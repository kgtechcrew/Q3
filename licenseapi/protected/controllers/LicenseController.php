<?php

class LicenseController extends Controller
{

    public $global_model = NULL;

    /* Global objects defined in the constructor */

    public function __construct()
    {

        $this->global_model = new GlobalSettings();
    }

    /*
     * Default Action For This API
     */

    public function actionIndex()
    {
        
    }

    /*
     * Login Authentication  ---> Basic Authentication is used here
     * Username --> Email ID
     * Password --> Password
     * Below are the validations checked & verified before allowing login 
     * Concurrent Users Check
     * Allowed Devices Check
     * Username Valid Check
     * Password Valid Check
     * If login is successfull then the JWT token will be sent as a response to the application.
     */

    public function actionLogin()
    {
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
        {
            $user_model = new User();
            $token      = new Token();

            $username = trim($_SERVER['PHP_AUTH_USER']);
            $password = trim($_SERVER['PHP_AUTH_PW']);

            $userdetails = User::model()->find(array('condition' => 'udt_email = "' . $username . '"'));
            $result      = array();
            if (!empty($userdetails))
            {
                $dbpassword = $userdetails->udt_password;
                $valid_pwd  = $user_model->validatePassword($dbpassword, $password);

                $token->useremail = $username;
                $token->userid    = $userdetails->udt_id;

                /* valid password check */
                if ($valid_pwd)
                {
                    /* concurrent users check */
                    $count_exceeded = $this->global_model->isConcurrentUsersExceeded();
                    if ($count_exceeded)
                    {
                        $this->global_model->formatResponseMessages("LCE", $token);
                    }
                    else
                    {
                        /* Devices count check */
                        $device_count_exceeded = $this->global_model->isUsersAllowedDevicesExceeded($token->userid);
                        if ($device_count_exceeded)
                        {
                            $this->global_model->formatResponseMessages("ADE", $token);
                        }
                        else
                        {
                            $token->generated_token = $token->generateToken();
                            $this->global_model->formatResponseMessages("LS", $token);
                        }
                    }
                }
                else
                {
                    $this->global_model->formatResponseMessages("VPWD", $token);
                }
            }
            else
            {
                $this->global_model->formatResponseMessages("VUNAME");
            }
        }
        else
        {
            $this->_sendResponse(500);
        }
    }

    /*
     * Storing the Device Information from where this API is triggered
     * Details stored here are,
     * IP address
     * Browser Information
     * Operating System
     * Device Type ( Desktop / Mobile )
     */

    public function actionStoreUserDeviceInfo()
    {
        $user_details = array();
        $json         = file_get_contents('php://input');
        $input_data   = CJSON::decode($json, true);

        if ($input_data['flag'] == 'S')
        {
            $user_details = $this->validateToken();
        }

        $userhis                  = new UserLicenseHistory();
        $userhis->user_id         = $user_details['userid'];
        $userhis->pat_login_time  = $this->global_model->fetchDBTime();
        $userhis->login_status    = $input_data['flag'];
        $userhis->pat_sys_ip      = $input_data['sysip'];
        $userhis->pat_sys_browser = $input_data['sysbrowser'];
        $userhis->pat_sys_os      = $input_data['sysos'];
        $userhis->pat_dev_type    = $input_data['devtype'];
        $userhis->pat_guid        = ($input_data['flag'] == 'S') ? $user_details['guid'] : '';
        $userhis->login_exceeded  = $input_data['isexceeded'];
        $userhis->device_exceeded = $input_data['deviceexceeded'];
        $userhis->save();

        $id = $userhis->id;
        if ($userhis->login_status == "S")
        {
            $result['status']  = 'S';
            $result['loginid'] = $id;
        }
        else
        {
            $result['status']  = 'F';
            $result['loginid'] = NULL;
        }
        $result['sysip']      = $input_data['sysip'];
        $result['sysbrowser'] = $input_data['sysbrowser'];
        $result['sysos']      = $input_data['sysos'];
        $result['devtype']    = $input_data['devtype'];
        $this->_sendResponse(200, $result, "Content-Type: application/json");
    }

    /*
     * Load Dashboard Details
     * Details fetched here are,
     * IP address
     * Browser Information
     * Operating System
     * Device Type ( Desktop / Mobile )
     */

    public function actionDashboard()
    {
        $user_details = array();
        $user_details = $this->validateToken();

        if (!empty($user_details))
        {
            $json           = file_get_contents('php://input');
            $input_data     = CJSON::decode($json, true);
            $loginid        = $input_data['loginid'];
            $user           = new User();
            $dashboard_info = $user->loadDashboardInfo($loginid);
            $this->_sendResponse(200, $dashboard_info, "Content-Type: application/json");
        }
        else
        {
            $this->_sendResponse(401);
        }
    }
    
    /*
     * Used to list all the users who have logged in currently in the application
     * Lists the details like users who have loggedin on multiple devices
     * Browser,IP,OS & Device Informations
     */

    public function actionTrackLoginUsers()
    {
        $user_details = $this->validateToken();
        if (!empty($user_details))
        {
            $user                       = new User();
            $user_login_details         = $user->trackLoginUsers();
            $this->_sendResponse(200, $user_login_details, "Content-Type: application/json");
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /*
     * Logout the application and storing the guid in the blacklist table
     * Inorder to avoid insecure subsequent requests after successful logout 
     */

    public function actionLogout()
    {
        $user_details = $this->validateToken();
        if (!empty($user_details))
        {
            $json       = file_get_contents('php://input');
            $input_data = CJSON::decode($json, true);

            $blacklist                        = new BlackList();
            $blacklist->user_id               = $user_details['userid'];
            $blacklist->json_token_identifier = $user_details['guid'];
            $blacklist->save();


            $loginid                      = $input_data['loginid'];
            $log_details                  = UserLicenseHistory::model()->findByPk($loginid);
            $log_details->logout_status   = 'Y';
            $log_details->pat_logout_time = $this->global_model->fetchDBTime();
            $log_details->pat_guid        = '';
            $log_details->update();


            $result['status']  = 'success';
            $result['message'] = "Logged Out Successfully";
            $this->_sendResponse(200, $result, "Content-Type: application/json");
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

}
