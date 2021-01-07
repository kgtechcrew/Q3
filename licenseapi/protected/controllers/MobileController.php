<?php

/**
 * $Id: MobileController.php 49 2016-01-19 00:57:51Z mankar $
 *
 * EXCLUSIVE LICENSE
 * THE INFORMATION AND COMPUTER SOURCE CODE CONTAINED WITHIN THIS PROGRAM SCRIPT IS
 * THE EXCLUSIVE PROPERTY OF HEALTHFIRST FINANCIAL, LLC. USE MUST BE AUTHORIZED UNDER WRITTEN
 * LICENSE OBTAINED FROM HEALTHFIRST FINANCIAL, LLC. USE AT YOUR OWN RISK. NO WARANTY EITHER
 * EXPRESSED OR IMPLIED.
 *
 * UNAUTHORIZED USE, ALTERATION, COPYING, OR REDISTRIBUTION IS STRICTLY PROHIBITED.
 *
 * @copyright Copyright (c) 2015 HealthFirst Financial, LLC.
 *
 * @author KG Financial Software Pvt Ltd (www.kgfsl.com), Chris DeLess
 *
 */
class MobileController extends Controller
{

    /**
     * Action used for login authentication
     * 
     */
    public function actionLogin()
    {
        $json          = file_get_contents('php://input');
        $login_details = CJSON::decode($json, true);

        if (!empty($login_details))
        {
            $model    = new User();
            $username = $login_details['id'];
            $pin      = $login_details['pass'];
            $record   = $model->loginProcess($username, $pin);
            $device_type = '';
            $ime_no      = '';
            $os_type     = '';
            if (!empty($record))
            {
                $model->account = $record['pgt_cardnumber'];
                $model->pin     = $record['pgt_pin'];

                if ($model->authenticate($model->account, $model->pin))
                {
                    $cardno      = Yii::app()->user->account;
                    $model->processLogin($cardno, $ime_no, $device_type, $os_type, 1);

                    /** Log Requests For Login Function * */
                    $this->storeRequestLog('LOGGED IN SUCCESSFULLY FOR MOBILE APP V1');

                    $result = array('result' => true, 'message' => 'Logged in successfully');
                    header('Content-Type: application/json');
                    echo CJSON::encode($result);
                }
                else
                {
                    $this->_sendResponse(401);
                }
            }
            else
            {
                $this->_sendResponse(401);
            }
        }
        else
        {
            $this->_sendResponse(500);
        }
    }

    /**
     * Action for updating the login status
     * 
     */
    public function actionLogout()
    {
        $account_number = Yii::app()->user->account; //CJSON::decode($json,true);

        if (!empty($account_number))
        {
            $cardno = $account_number;
            $User   = new User();

            /** Log Requests For Logout Function * */
            $this->storeRequestLog('LOGGED OUT SUCCESSFULLY FOR MOBILE APP V1');

            $upd_status = $User->processLogout($cardno, 1);
            if ($upd_status == 1)
            {
                Yii::app()->session->clear();
                Yii::app()->session->destroy();
                Yii::app()->user->logout();
                $result = array('result' => true, 'message' => 'Logout Status Updated Successfully');
                header('Content-Type: application/json');
                echo CJSON::encode($result);
            }
            else
            {
                $this->_sendResponse(500);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action for getting Account Summary
     * 
     */
    public function actionGetAccountSummary()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $this->accountSummary($account_number, 'Account_Summary');
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action for getting Current Balance Due
     */
    public function actionGetCurrentBalDue()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $this->accountSummary($account_number, 'Current_Balance');
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action for getting Past Due Amount
     */
    public function actionGetPastDueAmount()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $this->accountSummary($account_number, 'Past_Due_Amt');
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action for getting Past Due Amount
     */
    public function actionGetPaymentAgmt()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $this->accountSummary($account_number, 'Payment_Arrg');
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * function for getting the account information
     * 
     */
    public function accountSummary($cardno = null, $actionType = null)
    {
        $AccountInfo    = new AccountInfo();
        $loan_id        = $AccountInfo->getLoanId($cardno);
        $fee_function   = $AccountInfo->getAccountInfo($loan_id);
        $p_princ_bal    = isset($fee_function['p_princ_bal']) ? $fee_function['p_princ_bal'] : 0;
        $p_late_fee     = isset($fee_function['p_late_fee']) ? $fee_function['p_late_fee'] : 0;
        $p_nsf_fee      = isset($fee_function['p_nsf_fee']) ? $fee_function['p_nsf_fee'] : 0;
        $p_ext_fee      = isset($fee_function['p_ext_fee']) ? $fee_function['p_ext_fee'] : 0;
        $p_int_recv     = isset($fee_function['p_int_recv']) ? $fee_function['p_int_recv'] : 0;

        $account_information = $AccountInfo->displayLedgerDetails('ACCOUNT', $actionType, $loan_id, $p_late_fee, $p_nsf_fee, $p_ext_fee, $p_princ_bal, $p_int_recv);

        if (!empty($account_information))
        {
            header('Content-Type: application/json');
            if ($actionType == "Account_Summary")
            {
                $result = array('result' => true);
                echo CJSON::encode(array_merge($result, $account_information));
                exit;
            }

            echo CJSON::encode($account_information);
            exit;
        }
        else
        {
            //TODO: This is a good place to throw a full stack dump and find out why it failed...it never should.                	
            $this->_sendResponse(500);
        }
    }

    /**
     * action for getting the account Details
     * 
     */
    public function actionGetAccountDetail()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $this->accountDetail($account_number, 'Account_Details');
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * function for getting the account funding details
     * 
     */
    public function accountDetail($cardno = null, $actionType = null)
    {
        $AccountInfo    = new AccountInfo();
        $loan_id        = $AccountInfo->getLoanId($cardno);
        $fee_function   = $AccountInfo->getAccountInfo($loan_id);
        $p_princ_bal    = isset($fee_function['p_princ_bal']) ? $fee_function['p_princ_bal'] : 0;
        $p_late_fee     = isset($fee_function['p_late_fee']) ? $fee_function['p_late_fee'] : 0;
        $p_nsf_fee      = isset($fee_function['p_nsf_fee']) ? $fee_function['p_nsf_fee'] : 0;
        $p_ext_fee      = isset($fee_function['p_ext_fee']) ? $fee_function['p_ext_fee'] : 0;
        $p_int_recv     = isset($fee_function['p_int_recv']) ? $fee_function['p_int_recv'] : 0;

        $account_information = $AccountInfo->displayFundingDetails('MOBILEALLCHARGE', $actionType, $loan_id, $p_late_fee, $p_nsf_fee, $p_ext_fee, $p_princ_bal, $p_int_recv);

        header('Content-Type: application/json');
        if (!empty($account_information))
        {
            $result = array('result' => true, 'remarks' => null, 'accounts' => $account_information);
            echo CJSON::encode($result);
            exit;
        }
        else
        {
            $result = array('result' => false, 'remarks' => 'No Account Details Found.');
            echo CJSON::encode($result);
            exit;
        }
    }

    /**
     * action for bank route number
     * 
     */
    public function actionGetBankNameFromRouteNumber()
    {
        $account_number = $this->validateToken(); //CJSON::decode($json,true);

        if (!empty($account_number))
        {
            $json              = file_get_contents('php://input');
            $bank_route_number = CJSON::decode($json, true);
            $bankno            = !empty($bank_route_number['bankRouteNumber']) ? $bank_route_number['bankRouteNumber'] : '';

            if (!empty($bankno))
            {
                $bank_details = Aba::model()->findAll(array(
                    'condition' => 'bkt_bank_route_no LIKE :keyword',
                    'order'     => 'bkt_bank_route_no',
                    'limit'     => 20,
                    'params'    => array(':keyword' => "%$bankno%")
                ));

                if (!empty($bank_details))
                {
                    $suggest = array();
                    foreach ($bank_details as $model)
                    {
                        $suggest = $model->bkt_bank_name; // return values from autocomplete
                    }

                    $result = array('status' => true, 'message' => $suggest);
                    header('Content-Type: application/json');
                    echo CJSON::encode($result);
                    exit;
                }
                else
                {
                    $this->_sendResponse(500, 'No bank details found');
                    exit;
                }
            }
            else
            {
                $this->_sendResponse(500);
                exit;
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action for view the statements     
     * 
     */
    public function actionGetStatementList()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $AccountInfo    = new AccountInfo();
            $loan_id        = $AccountInfo->getLoanId($account_number);
            $fee_function   = $AccountInfo->getAccountInfo($loan_id);
            $p_princ_bal    = isset($fee_function['p_princ_bal']) ? $fee_function['p_princ_bal'] : 0;
            $p_late_fee     = isset($fee_function['p_late_fee']) ? $fee_function['p_late_fee'] : 0;
            $p_nsf_fee      = isset($fee_function['p_nsf_fee']) ? $fee_function['p_nsf_fee'] : 0;
            $p_ext_fee      = isset($fee_function['p_ext_fee']) ? $fee_function['p_ext_fee'] : 0;
            $p_int_recv     = isset($fee_function['p_int_recv']) ? $fee_function['p_int_recv'] : 0;

            $statement_information = $AccountInfo->viewStatements('Statements', $loan_id, $p_late_fee, $p_nsf_fee, $p_ext_fee, $p_princ_bal, $p_int_recv);

            header('Content-Type: application/json');
            if (!empty($statement_information))
            {
                $result = array('result' => true, 'statements' => $statement_information);
                echo CJSON::encode($result);
                exit;
            }
            else
            {
                $result = array('result' => false, 'statements' => '');
                echo CJSON::encode($result);
                exit;
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action used for Zip code Check
     * 
     */
    public function actionGetZipCodeValid()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $json    = file_get_contents('php://input');
            $zipcode = CJSON::decode($json, true);

            if (isset($zipcode['ZipCode']) && !empty($zipcode['ZipCode']))
            {
                $model = new AccountInfo();
                header('Content-Type: application/json');
                $model->zipcode($zipcode);
            }
            else
            {
                $this->_sendResponse(500);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action used for Zip code Check
     * 
     */
    public function actionGetValidEmailaddr()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $json       = file_get_contents('php://input');
            $email_addr = CJSON::decode($json, true);

            if (isset($email_addr['emailAddr']) && !empty($email_addr['emailAddr']))
            {
                header('Content-Type: application/json');
                if (!preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/", $email_addr['emailAddr']))
                {
                    $result = array('status' => false, 'message' => 'Email address format is wrong');
                    echo CJSON::encode($result);
                }
                else
                {
                    $result = array('status' => true, 'message' => 'Valid Email Address');
                    echo CJSON::encode($result);
                }
            }
            else
            {
                $this->_sendResponse(200, 'Email Address cannot be empty');
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action used for Zip code Check
     * 
     */
    public function actionGetValidPhoneNum()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $json    = file_get_contents('php://input');
            $phoneno = CJSON::decode($json, true);

            if (isset($phoneno['PhoneNumber']) && !empty($phoneno['PhoneNumber']))
            {
                header('Content-Type: application/json');
                if (!preg_match("/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/", $phoneno['PhoneNumber']))
                {

                    $result = array('status' => false, 'message' => 'Not Valid Phone Number');
                    echo CJSON::encode($result);
                }
                else
                {
                    $result = array('status' => true, 'message' => 'Valid Phone Number');
                    echo CJSON::encode($result);
                }
            }
            else
            {
                $this->_sendResponse(200, 'Phone Number cannot be empty');
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action used for Getting State values
     * 
     */
    public function actionGetState()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $json  = file_get_contents('php://input');
            $state = CJSON::decode($json, true);

            if (!empty($state['state']))
            {
                $state_name   = !empty($state['state']) ? $state['state'] : '';
                $User         = new User();
                $stateDetails = $User->getState($state_name);
                if (!empty($stateDetails))
                {
                    $result = array('status' => 'S', 'remarks' => 'Success', 'values' => $stateDetails);
                    header('Content-Type: application/json');
                    echo CJSON::encode($result);
                }
                else
                {
                    $this->_sendResponse(501);
                }
            }
            else
            {
                $this->_sendResponse(500);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action used for viewing the payment history
     * 
     */
    public function actionGetPaymentHistory()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $model  = new AccountInfo();
            $result = $model->view_statement_list($account_number);
            if (!empty($result))
            {
                $result = array('result' => true, 'transactions' => $result);
                header('Content-Type: application/json');
                echo CJSON::encode($result);
            }
            else
            {
                $this->_sendResponse(200, 'No payments found');
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action used for Communicating wit HFF
     * 
     */
    public function actionSetContactHFF()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $json        = file_get_contents('php://input');
            $communicate = CJSON::decode($json, true);
            $com_err     = array();
            $i           = 0;
            if (!empty($communicate))
            {
                if (empty($communicate['Name']))
                {
                    $com_err['Name'] = 'Name cannot be empty';
                    $i               = $i + 1;
                }
                else
                {
                    if (!preg_match("/^[a-zA-Z ]*$/", $communicate['Name']))
                    {
                        $com_err['Name'] = 'Name is not Valid';
                        $i               = $i + 1;
                    }
                }
                if (empty($communicate['email']))
                {
                    $com_err['email'] = 'Email Address cannot be Empty';
                    $i                = $i + 1;
                }
                else
                {
                    if (!preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/", $communicate['email']))
                    {
                        $com_err['email'] = 'Email address is not Valid';
                        $i                = $i + 1;
                    }
                }
                if (empty($communicate['PhoneNumber']))
                {
                    $com_err['PhoneNumber'] = 'Phone Number cannot be empty';
                    $i                      = $i + 1;
                }
                else
                {
                    if (!preg_match("/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/", $communicate['PhoneNumber']))
                    {
                        $com_err['PhoneNumber'] = 'Phone number is not valid';
                        $i                      = $i + 1;
                    }
                }
                if (empty($communicate['Message']))
                {
                    $com_err['Message'] = 'Message cannot be empty';
                    $i                  = $i + 1;
                }

                if ($i == 0)
                {
                    $email_info = $this->Emailsend($communicate['Name'], $communicate['email'], $communicate['PhoneNumber'], $communicate['Message']);
                    if ($email_info == 0)
                    {
                        $this->_sendResponse(501);
                    }
                    else
                    {
                        //Success State.....
                        $result = array('status' => true, 'message' => 'Your request sent successfully');
                        header('Content-Type: application/json');
                        echo CJSON::encode($result);
                    }
                }
                else
                {
                    if (!empty($com_err))
                    {
                        $err    = implode(',', $com_err);
                        $result = array('status' => false, 'message' => $err);
                        header('Content-Type: application/json');
                        echo CJSON::encode($result);
                    }
                }
            }
            else
            {
                $this->_sendResponse(500);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Sending Email
     * 
     */
    public function EmailSend($name, $address, $phone, $message)
    {
        $connection     = Yii::app()->db;
        $sql            = "select ags.ags_var_value as 'FromMail', ags.ags_var_desc as 'Mname' from ags_app_global_setting ags where ags.ags_var_key='mob_app_from_email_id'";
        $fromMail       = $connection->createCommand($sql)->queryAll();
        $from_email_ids = $fromMail[0]['FromMail'];
        $from_name      = $fromMail[0]['Mname'];
        $sql            = "select ags.ags_var_value as 'ToMail' from ags_app_global_setting ags where ags.ags_var_key='mob_app_to_email_id'";
        $toMail         = $connection->createCommand($sql)->queryScalar();
        $to_email_ids   = explode(",", $toMail);

        $lMailer    = new HffMailer();
        $lMailer->SetSubject(date('m/d/Y') . ' AccessOne Contact Information - ** ' . ENV_MODE . ' **');

        $lMailer->SetFromAddress($from_email_ids, $from_name);
        $lMailer->AddAllToAddress($to_email_ids);

        $content = "<div style='font-family: verdana;font-size:11px;padding:5px;color:#000000;background-color: #FFFFFF;'>Team,</font><br/><br/>";
        $content .= "<dd>This email is to notify that, Patient " . $name . " sent a message from HFF mobile application. </dd><br/><br/>";
        $content .="<dd><b>Patient name :</b> " . $name . " <b>Address :</b> " . $address . " <b>Phone Number :</b> " . $phone . " <b><br/><dd>Message :</b> " . $message . "</dd><br/><br/>";
        $content .= "<font style='font-family: verdana;font-size:11px;padding:5px;color:#000000;background-color: #FFFFFF;'>Thank you<br/>";
        $lMailer->SetBody($content);

        if ($lMailer->SendWithoutDbConfirmation())
        {
            return 1;
        }
        else
        {
            return 1;
        }
    }

    /**
     * Action used for Downloading the statements
     * 
     */
    public function actionGetStatementPDF()
    {
        $json             = file_get_contents('php://input');
        $statementDetails = CJSON::decode($json, true);
        $AccountNumber    = $this->validateToken();
        if (!empty($statementDetails) && !empty($AccountNumber))
        {
            $billno           = $statementDetails['statementID'];
            $StatementDate    = date('Y-m-d');
            // $AccountNumber    = $this->validateToken();        
            $files            = new Files;
            $StatementDetails = $files->getDynamicReportDetails($billno, 'SMT', $StatementDate);

            if (isset($StatementDetails) && !empty($StatementDetails))
            {
                $filename    = $StatementDetails[0]['created_file_name'];
                $date        = $StatementDetails[0]['date_value_1'];
                $base_path   = $StatementDetails[0]['created_file_base_path'];
                $mig_flag    = $StatementDetails['flag'];
                $path        = $this->retrieveSFTP($filename, $date, $base_path, $mig_flag, 'STMT'); // Files get from FTP  
                $loanDetails = User::getLoanDetails($AccountNumber);
                $guarid      = $loanDetails[0]['lmt_guar_id'];
                $loanid      = $loanDetails[0]['lmt_loan_id'];
                if (!empty($StatementDate))
                {
                    AccountInfo::viewStatementComments($guarid, $loanid, $StatementDate);
                }

                $output = base64_encode(file_get_contents($path));
                header('Content-Type: text/plain');
                echo $output;
                @unlink($path);
                exit();
            }
            else
            {
                $this->_sendResponse(200, 'No Reports Found');
            }
        }
        else
        {
            $this->_sendResponse(500);
        }
    }

    /**
      Function to get  file from loacal or configurable path from SFTP server
     * */
    private function retrieveSFTP($filename, $date, $base_path, $mig_flag = 0, $letter_type = "STD")
    {
        if (stristr(PHP_OS, 'WIN') && $letter_type == "STMT")
        {
            $file = ''; 
            if ($date == null)
            {
                $path = $base_path; 
            }
            else
            {
                $path = $this->makePath($date, $base_path);
            }
            
            $file_path_with_size = $path.$filename;
            
            if(filesize($file_path_with_size) > 0)
            {
                $file = rename($file_path_with_size, sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename);
                return ($file === 0) ? '' : sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
            }
            else
            {
                echo 0;
                exit;
            }
        }
        else
        {
            $file = '';
            $sftp = Yii::app()->sftp;
            $sftp->connect();
            if ($date == null)
            {
                $path = $base_path; //ARCHIVE_PATH;
            }
            else
            {
                $path = $this->makePath($date, $base_path);
            }

            if ($path != '\\')
            {
                $sftp->chdir($path);
            }

            if ($sftp->getSize($filename) > 0)
            {
                $file = $sftp->getFile($filename, sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename);
                if ($mig_flag == 1)
                {
                    @unlink($base_path . $filename);
                }
                return ($file === 0) ? '' : sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
            }
            else
            {
                echo 0;
                exit;
            }
        }
    }

    /** Used for setting the notification firebase token * */
    public function actionSetNotificationToken()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            try
            {
                $group_id      = 0;
                $json          = file_get_contents('php://input');
                $token_details = CJSON::decode($json, true);
                $token         = $token_details['token'];
                $final_token   = trim($token);

                $loan_details = Loan::model()->find(array('condition' => 'lmt_card_no = "' . $account_number . '"'));
                $guar_id      = $loan_details->lmt_guar_id;


                if (Credentials::model()->exists('patient_guar_id = :patient_guar_id', array(":patient_guar_id" => $guar_id)))
                {
                    $guar_chk_credentials = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $guar_id . '"'));
                    $group_id             = $guar_chk_credentials->group_id;

                    if (!empty($guar_chk_credentials->patient_email) && !empty($guar_chk_credentials->patient_pwd))
                    {
                        $group_credentials = Credentials::model()->findAll(array('condition' => 'group_id = "' . $group_id . '"'));
                        foreach ($group_credentials as $key => $value)
                        {
                            $guar_credentials = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $value->patient_guar_id . '"'));
                            if (!empty($guar_credentials->patient_email) && !empty($guar_credentials->patient_pwd) && $guar_credentials->patient_signup_active_status == 'Y')
                            {
                                $guar_credentials->patient_firebase_token = $final_token;
                                $guar_credentials->update();
                            }
                        }

                        $response      = array('result' => 'true');
                        $json_response = json_encode($response);
                        /** storing the logs * */
                        $this->storeNotificationLogs($json, 'setNotificationToken', $json_response, 200, 'SUCCESS', $group_id);
                        header('Content-Type: application/json');
                        echo CJSON::encode($response);
                    }
                    else
                    {
                        /** storing the logs * */
                        $json_response = 'Guarantor has not started the signup / registration process.';
                        $this->storeNotificationLogs($json, 'setNotificationToken', $json_response, 401, 'FAILURE', $group_id);
                        $this->_sendResponse(401);
                    }
                }
                else
                {
                    /** storing the logs * */
                    $json_response = 'Guarantor has not started the signup / registration process.';
                    $this->storeNotificationLogs($json, 'setNotificationToken', $json_response, 401, 'FAILURE', $group_id);
                    $this->_sendResponse(401);
                }
            } catch (Exception $ex)
            {
                /** storing the logs * */
                $exception_message = $ex->getMessage();
                $this->storeNotificationLogs($json, 'setNotificationToken', $exception_message, 500, 'FAILURE', $group_id);
                $this->_sendResponse(500, $exception_message);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /** Setting the notification preference * */
    public function actionSetNotificationPreference()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            try
            {
                $group_id      = 0;
                $json          = file_get_contents('php://input');
                $input_details = CJSON::decode($json, true);

                $loan_details = Loan::model()->find(array('condition' => 'lmt_card_no = "' . $account_number . '"'));
                $guar_id      = $loan_details->lmt_guar_id;

                if (Credentials::model()->exists('patient_guar_id = :patient_guar_id', array(":patient_guar_id" => $guar_id)))
                {
                    $guar_chk_credentials = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $guar_id . '"'));
                    $group_id             = $guar_chk_credentials->group_id;
                    if (!empty($guar_chk_credentials->patient_email) && !empty($guar_chk_credentials->patient_pwd))
                    {

                        $group_credentials = Credentials::model()->findAll(array('condition' => 'group_id = "' . $group_id . '"'));

                        foreach ($group_credentials as $key => $value)
                        {
                            $guar_id         = $value->patient_guar_id;
                            $push_alert_type = 'PUSH';
                            EmailAlerts::model()->deleteAll("psd_id = '" . $guar_id . "' and push_alert_type = 'PUSH'");
                            foreach ($input_details as $key1 => $value1)
                            {
                                if ($value1['flag'] == "1" || $value1['flag'] == 1)
                                {
                                    $pref_type     = $value1['preferenceType'];
                                    $sql           = "select tmf_id from tmf_text_message_format where tmf_alert_type = '" . $pref_type . "' limit 1";
                                    $push_alert_id = $this->connection->createCommand($sql)->queryScalar();

                                    $model                  = new EmailAlerts();
                                    $model->psd_id          = $guar_id;
                                    $model->push_alert_id   = $push_alert_id;
                                    $model->push_alert_name = $pref_type;
                                    $model->push_alert_type = $push_alert_type;
                                    $model->save();
                                }
                            }
                        }


                        $response      = array('result' => 'true');
                        $json_response = json_encode($response);


                        /** storing the logs * */
                        $this->storeNotificationLogs($json, 'setNotificationPreference', $json_response, 200, 'SUCCESS', $group_id);
                        header('Content-Type: application/json');
                        echo CJSON::encode($response);
                        Yii::app()->end();
                    }
                    else
                    {
                        /** storing the logs * */
                        $json_response = 'Guarantor has not started the signup / registration process.';
                        $this->storeNotificationLogs($json, 'setNotificationPreference', $json_response, 401, 'FAILURE', $group_id);
                        $this->_sendResponse(401);
                    }
                }
                else
                {
                    /** storing the logs * */
                    $json_response = 'Guarantor has not started the signup / registration process.';
                    $this->storeNotificationLogs($json, 'setNotificationPreference', $json_response, 401, 'FAILURE', $group_id);
                    $this->_sendResponse(401);
                }
            } catch (Exception $ex)
            {
                /** storing the logs * */
                $exception_message = $ex->getMessage();
                $this->storeNotificationLogs($json, 'setNotificationPreference', $exception_message, 500, 'FAILURE', $group_id);
                $this->_sendResponse(500, $exception_message);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /** used for getting the notification preferences * */
    public function actionGetNotificationPreference()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            try
            {
                $group_id     = 0;
                $loan_details = Loan::model()->find(array('condition' => 'lmt_card_no = "' . $account_number . '"'));
                $guar_id      = $loan_details->lmt_guar_id;
                if (Credentials::model()->exists('patient_guar_id = :patient_guar_id', array(":patient_guar_id" => $guar_id)))
                {
                    $guar_chk_credentials = Credentials::model()->find(array('condition' => 'patient_guar_id = "' . $guar_id . '"'));
                    $group_id             = $guar_chk_credentials->group_id;

                    if (!empty($guar_chk_credentials->patient_email) && !empty($guar_chk_credentials->patient_pwd))
                    {
                        $email_alerts        = EmailAlerts::model()->findAll("psd_id = '" . $guar_id . "' and push_alert_type = 'PUSH'");
                        $push_alerts_details = array();
                        $preference_types    = array();
                        foreach ($email_alerts as $key => $value)
                        {
                            $preference_types[]                          = $value['push_alert_name'];
                            $push_alerts_details[$key]['preferenceType'] = $value['push_alert_name'];
                            $push_alerts_details[$key]['flag']           = 1;
                        }

                        $total_push_alert_details = array('ACCOUNT_CANCEL', 'APPROACH_PAST_DUE', 'DEMO_CHANGE', 'NEW_ACCOUNT_LOAD', 'PASSWORD_CHANGE', 'PAST_DUE_NOTICE', 'PAYMENT_CONFIRM', 'PAYMENT_POSTED');
                        $diff_preferences         = array_diff($total_push_alert_details, $preference_types);
                        $non_push_alerts_details  = array();
                        foreach ($diff_preferences as $key => $value)
                        {
                            $non_push_alerts_details[$key]['preferenceType'] = $value;
                            $non_push_alerts_details[$key]['flag']           = 0;
                        }

                        $notification_preferences = array_merge($push_alerts_details, $non_push_alerts_details);
                        $result                   = array('result' => true, 'preferences' => $notification_preferences);

                        /** storing the logs * */
                        $this->storeNotificationLogs('', 'getNotificationPreference', json_encode($result), 200, 'SUCCESS', $group_id);
                        echo CJSON::encode($result);
                        Yii::app()->end();
                    }
                    else
                    {
                        /** storing the logs * */
                        $json_response = 'Guarantor has not started the signup / registration process.';
                        $this->storeNotificationLogs('', 'getNotificationPreference', $json_response, 401, 'FAILURE', $group_id);
                        $this->_sendResponse(401);
                    }
                }
                else
                {
                    $json_response = 'Guarantor has not started the signup / registration process.';
                    $this->storeNotificationLogs('', 'getNotificationPreference', $json_response, 401, 'FAILURE', $group_id);
                    $this->_sendResponse(401);
                }
            } catch (Exception $ex)
            {
                /** storing the logs * */
                $exception_message = $ex->getMessage();
                $this->storeNotificationLogs('', 'getNotificationPreference', $exception_message, 500, 'FAILURE', $group_id);
                $this->_sendResponse(500, $exception_message);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /** Storing the notification logs  * */
    public function storeNotificationLogs($request, $method, $response, $status_code, $status, $group_id)
    {
        /** storing the logs * */
        $notification_logs             = new NotificationLog();
        $notification_logs->rawdata    = $request;
        $notification_logs->method     = $method;
        $notification_logs->response   = $response;
        $notification_logs->res_code   = $status_code;
        $notification_logs->res_status = $status;
        $notification_logs->group_id   = $group_id;
        $notification_logs->save();
    }

    /**
     * Action used for Making using Pay by card
     * 
     */
    public function actionSetPayByCCard()
    {
        $account_number = $this->validateToken();
        if (!empty($account_number))
        {
            $json         = file_get_contents('php://input');
            $pymt_details = CJSON::decode($json, true);

            if (!empty($pymt_details))
            {
                $this->actionCardMakePayment($pymt_details, $account_number);
            }
            else
            {
                $this->_sendResponse(500);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action used for making using pay by check
     * 
     */
    public function actionSetPayByCheck()
    {
        $account_number = $this->validateToken();

        if (!empty($account_number))
        {
            $json         = file_get_contents('php://input');
            $pymt_details = CJSON::decode($json, true);

            if (!empty($pymt_details))
            {
                $this->actionMakePayment($pymt_details, $account_number);
            }
            else
            {
                $this->_sendResponse(500);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * Action used for Making the payments
     * 
     */
    public function actionMakePayment($pymt_details = array(), $hf_acc_number)
    {
        /** Check present day * */
        $card_no  = $hf_acc_number;
        $this->paymentPresentDayCheck(date('Y-m-d'), $card_no);
        
        
        /** Check whether the account is in recourse queue **/
        $rec_queue_payment = $this->paymentRecourseRestriction($card_no);

        if (!empty($pymt_details))
        {
            $source      = $this->getSourceName();
            $bank_route  = isset($pymt_details['bankRouteNumber']) ? $pymt_details['bankRouteNumber'] : '';
            $bank_acc_no = isset($pymt_details['BankAccountNumber']) ? $pymt_details['BankAccountNumber'] : '';
            if (!empty($pymt_details['paymentAmt']))
            {
                $payment_amount             = preg_replace('/[,]/s', '', $pymt_details['paymentAmt']);
                $pymt_details['paymentAmt'] = number_format($payment_amount, 2, '.', '');
            }


            $card_number = $hf_acc_number;
            $guar_det    = $this->getGuarantorDetails($card_number);
            $lastname    = !empty($guar_det[0]['gmt_guar_lname']) ? $guar_det[0]['gmt_guar_lname'] : '';
            $firstname   = !empty($guar_det[0]['gmt_guar_fname']) ? $guar_det[0]['gmt_guar_fname'] : '';
            $guar_id     = !empty($guar_det[0]['id']) ? $guar_det[0]['id'] : '';

            //Get Bank Name Details
            $bank_name                  = $this->getBankName($bank_route);
            $bank_name                  = str_replace("'", "\'", $bank_name);
            $api_data["paymentType"]    = "CH";
            $api_data["account"]        = $card_number;
            $api_data["firstName"]      = $firstname;
            $api_data["lastname"]       = $lastname;
            $api_data["freqType"]       = 'O';
            $api_data["startDate"]      = date('Y-m-d');
            $api_data["endDate"]        = date('Y-m-d');
            $api_data["payment"]        = !empty($pymt_details['paymentAmt']) ? $pymt_details['paymentAmt'] : 0.00;
            $api_data["routingNumber"]  = $bank_route;
            $api_data["bankAccount"]    = $bank_acc_no;
            $api_data["bankName"]       = $bank_name;
            $api_data["enteredBy"]      = $guar_id;
            $api_data["cardNumber"]     = "";
            $api_data["cvvCode"]        = "";
            $api_data["expMonth"]       = "";
            $api_data["expYear"]        = "";
            $api_data["zipCode"]        = "";
            $api_data["payerFirstName"] = $firstname;
            $api_data["payerLastname"]  = $lastname;
            $api_data["importSource"]   = $source;
            $api_data["savePayment"]    = isset($pymt_details['savepayment']) ? $pymt_details['savepayment'] : '';
            $api_data["nickName"]       = isset($pymt_details['nickname']) ? $pymt_details['nickname'] : '';
            $api_data["useSavedPayment"]= isset($pymt_details['useSavedPayment']) ? $pymt_details['useSavedPayment'] : '';
            $api_data["savedPaymentId"] = isset($pymt_details['savedPaymentId']) ? $pymt_details['savedPaymentId'] : '';
            
            $pay_details                = Controller::savePaymentApi(json_encode($api_data));
            if (!empty($pay_details))
            {
                $sheduledId = $pay_details['payment']["ppdId"];
                $status     = $pay_details['payment']["status"];
                if ($status != "success")
                {
                    $error = '';
                    if (is_array($pay_details["payment"]["comments"]))
                    {
                        if (!empty($pay_details["payment"]["comments"]))
                        {
                            foreach ($pay_details["payment"]["comments"] as $err_key => $err_value)
                            {
                                if (is_array($err_value))
                                {
                                    foreach ($err_value as $err_svalue)
                                    {
                                        $error .= $err_svalue . "<br/>";
                                }
                                }
                                else
                            {
                                    $error .= $err_value . "<br/>";
                            }
                            }
                        }
                        else
                    {
                            $error .= Yii::t('ui', 'pymt_contact');
                        }
                    }
                    else
                    {
                        $err_value = explode(',', $pay_details["payment"]["comments"]);
                        if (!empty($err_value))
                        {
                            foreach ($err_value as $value)
                            {
                                $error .= $value . "<br/>";
                            }
                        }
                        else
                        {
                            $error .= Yii::t('ui', 'pymt_contact');
                        }
                    }
                    $result = array('status' => false, 'message' => $error);
                    header('Content-Type: application/json');
                    echo CJSON::encode($result);
                    exit;
                }
                $result = array('status' => true);

                /** LPS Table Entry * */
                $this->lpsPaymentEntery($pymt_details, $sheduledId, 'CH', $card_no);

                header('Content-Type: application/json');
                echo CJSON::encode(array_merge($result, array('confirmationNumber' => $sheduledId)));
            }
            else
            {
                $errmsg = Yii::t('ui', 'pymt_contact');
                $result = array('status' => false, 'message' => $errmsg);
                header('Content-Type: application/json');
                echo CJSON::encode($result);
                exit;
            }
        }
        else
        {
            $this->_sendResponse(500);
        }
    }

    public function actionCardMakePayment($pymt_details, $hf_acc_number)
    {
        /** Check present day * */
        $card_no = $hf_acc_number;
        $this->paymentPresentDayCheck(date('Y-m-d'), $card_no);

        /** Check credit card lock down status * */
        $this->creditCardLockDown($card_no);

        if (!empty($pymt_details))
        {
            if (!empty($pymt_details['paymentAmt']))
            {
                $payment_amount             = preg_replace('/[,]/s', '', $pymt_details['paymentAmt']);
                $pymt_details['paymentAmt'] = number_format($payment_amount, 2, '.', '');
            }
            
            if(isset($pymt_details['nameOnCard']))
            {
                $nameOnCard = !empty($pymt_details['nameOnCard']) ? $pymt_details['nameOnCard'] : '';
                $nameOnCard = explode(" ", $nameOnCard, 2);
                if (isset($nameOnCard[1]))
                {
                    $lastname  = !empty($nameOnCard[0]) ? $nameOnCard[0] : '';
                    $firstname = !empty($nameOnCard[1]) ? $nameOnCard[1] : '';
                }
                else
                {
                    $firstname = !empty($nameOnCard[0]) ? $nameOnCard[0] : '';
                    $lastname  = '';
                }
            }
            else
            {
                $firstname = "";
                $lastname  = "";
            }
            
            $card_number                = $hf_acc_number;
            $guar_det                   = $this->getGuarantorDetails($card_number);
            $guar_id                    = !empty($guar_det[0]['id']) ? $guar_det[0]['id'] : '';
            $guar_lastname              = !empty($guar_det[0]['gmt_guar_lname']) ? $guar_det[0]['gmt_guar_lname'] : '';
            $guar_firstname             = !empty($guar_det[0]['gmt_guar_fname']) ? $guar_det[0]['gmt_guar_fname'] : '';
            $api_data["paymentType"]    = "CC";
            $api_data["account"]        = $card_number;
            $api_data["firstName"]      = $guar_firstname;
            $api_data["lastname"]       = $guar_lastname;
            $api_data["freqType"]       = 'O';
            $api_data["startDate"]      = date('Y-m-d');
            $api_data["endDate"]        = date('Y-m-d');
            $api_data["payment"]        = !empty($pymt_details['paymentAmt']) ? $pymt_details['paymentAmt'] : 0.00;
            $api_data["routingNumber"]  = "";
            $api_data["bankAccount"]    = "";
            $api_data["bankName"]       = "";
            $api_data["enteredBy"]      = $guar_id;
            $api_data["cardNumber"]     = isset($pymt_details['cardNumber']) ? $pymt_details['cardNumber'] : '';
            $api_data["cvvCode"]        = isset($pymt_details['CVV']) ? $pymt_details['CVV'] : '';
            $api_data["expMonth"]       = isset($pymt_details['expiryMonth']) ? $pymt_details['expiryMonth'] : '';
            $api_data["expYear"]        = isset($pymt_details['expiryYear']) ? $pymt_details['expiryYear'] : '';
            $api_data["zipCode"]        = isset($pymt_details['zipCode']) ? $pymt_details['zipCode'] : '';
            $api_data["payerFirstName"] = $firstname;
            $api_data["payerLastname"]  = $lastname;
            $api_data["importSource"]   = "HFX_MOB";
            $api_data["savePayment"]    = isset($pymt_details['savepayment']) ? $pymt_details['savepayment'] : '';
            $api_data["nickName"]       = isset($pymt_details['nickname']) ? $pymt_details['nickname'] : '';
            $api_data["useSavedPayment"]= isset($pymt_details['useSavedPayment']) ? $pymt_details['useSavedPayment'] : '';
            $api_data["savedPaymentId"] = isset($pymt_details['savedPaymentId']) ? $pymt_details['savedPaymentId'] : '';
            
            $pay_details                = Controller::savePaymentApi(json_encode($api_data));
            if (!empty($pay_details))
            {
                $sheduledId = $pay_details["payment"]["ppdId"];
                $status     = isset($pay_details['payment']['status']) ? $pay_details['payment']['status'] : "";
                if ($status != "success")
                {
                    $error = '';
                    if (is_array($pay_details["payment"]["comments"]))
                    {
                        if (!empty($pay_details["payment"]["comments"]))
                        {
                            foreach ($pay_details["payment"]["comments"] as $err_key => $err_value)
                            {
                                if (is_array($err_value))
                                {
                                    foreach ($err_value as $err_svalue)
                                    {
                                        $error .= $err_svalue . "<br/>";
                                }
                                }
                                else
                            {
                                    $error .= $err_value . "<br/>";
                            }
                        }
                        }
                    else
                    {
                            $error .= Yii::t('ui', 'pymt_contact');
                        }
                    }
                    else
                    {
                        $err_value = explode(',', $pay_details["payment"]["comments"]);
                        if (!empty($err_value))
                        {
                            foreach ($err_value as $value)
                            {
                                $error .= $value . "<br/>";
                            }
                        }
                        else
                        {
                            $error .= Yii::t('ui', 'pymt_contact');
                        }
                    }
                    $result = array('status' => false, 'message' => $error);
                    header('Content-Type: application/json');
                    echo CJSON::encode($result);
                    exit;
                }
                $result = array('status' => true);
                /** LPS Table Entry * */
                $this->lpsPaymentEntery($pymt_details, $sheduledId, 'CC', $card_no);

                header('Content-Type: application/json');
                echo CJSON::encode(array_merge($result, array('message' => "Confirmation number: $sheduledId")));
                exit;
            }
            else
            {
                $errmsg .= Yii::t('ui', 'pymt_contact');
                $result = array('status' => false, 'message' => $errmsg);
                header('Content-Type: application/json');
                echo CJSON::encode($result);
                exit;
            }
        }
        else
        {
            $this->_sendResponse(500);
        }
    }

    public function displayPymtErrorMessages($err_det = array(), $field = '')
    {
        $column_names = array('payorZipCode' => 'Zip Code', 'paymentAmount' => 'Payment amount', 'cardNumber' => 'Card Number', 'expireYear' => 'Expiry Year');
        $message      = '';
        foreach ($err_det as $key => $value)
        {
            foreach ($value as $key1 => $value1)
            {
                if ($key1 == 'msg')
                {
                    $message .= ($value1 == 'Required') ? $this->displayReqPymtMessages($field) . ',' : $column_names[$field] . ' ' . $value1 . ',';
                }
            }
        }
        $message = rtrim($message, ',');
        return $message;
    }

    public function displayReqPymtMessages($column_name)
    {
        $err_msg = '';
        switch ($column_name)
        {
            case 'payorZipCode':
                $err_msg = 'Zip Code should be 5 or 6 characters long';
                break;
            case 'paymentAmount':
                $err_msg = 'Payment amount must be more than $0';
                break;
            case 'cardNumber':
                $err_msg = 'Card Number should be 16 characters';
                break;
            case 'expireYear':
                $err_msg = 'Expiry Year should be 4 characters';
                break;
            default:
                break;
        }
        return $err_msg;
    }

    /**
     * Action used for making payment error notifications
     * 
     */
    private function getMPSErrMessage($err)
    {
        $errmsg = array();
        foreach ($err as $key => $value)
        {
            foreach ($value as $key1 => $value1)
            {
                $errmsg[$key1] = $key . " " . $key1 . " " . $value1['msg'];
            }
        }
        return $errmsg;
    }

    /**
     * Action used for providing the alert mes
     * 
     */
    public function actionGetAlerts()
    {
        $json       = file_get_contents('php://input');
        $get_alerts = CJSON::decode($json, true);

        if (!empty($get_alerts))
        {
            $appver   = $get_alerts['appver'];
            $platform = $get_alerts['platform'];
            $version  = $get_alerts['platformversion'];

            if (!empty($appver) && !empty($platform) && !empty($version))
            {
                $sql      = "select nws.hff_portal_title as UpdateTitle, nws.hff_portal_description as UpdateDescription from hff_portal_news_updates nws where nws.hff_portal_news_active='Y'";
                $messages = Yii::app()->db->createCommand($sql)->queryAll();

                header('Content-Type: application/json');
                if (!empty($messages))
                {
                    $result = array('enable' => true, 'message' => $messages[0]['UpdateDescription']);
                    echo CJSON::encode($result);
                }
                else
                {
                    $result = array('enable' => true, 'message' => '');
                    echo CJSON::encode($result);
                }
            }
            else
            {
                $this->_sendResponse(500);
            }
        }
        else
        {
            $this->_sendResponse(501);
        }
    }

    public function getGuarantorDetails($card_no = NULL)
    {
        $guar_det = Yii::app()->db->createCommand()
                ->select('gmt.*')
                ->from('lmt_mst_loan lmt')
                ->join('gmt_mst_guar gmt', 'lmt.lmt_guar_id = gmt.id')
                ->where('lmt_card_no = :card_no', array(':card_no' => $card_no))
                ->queryAll();
        return $guar_det;
    }

    public function getBankName($bkt_bank_route_no = NULL)
    {
        $bank_name = Yii::app()->db->createCommand()
                ->select('bkt.bkt_bank_name')
                ->from('bkt_mst_bank bkt')
                ->where('bkt_bank_route_no = :bkt_bank_route_no', array(':bkt_bank_route_no' => $bkt_bank_route_no))
                ->queryScalar();
        return $bank_name;
    }

    public function paymentPresentDayCheck($present_date, $card_no)
    {
        ini_set('error_reporting', '~E_DEPRECATED');
        $connection        = Yii::app()->payment;
        $query             = "select count(*) as count from ppd_pat_pymt_det p where p.ppd_freq_type = 'O' and p.ppd_start_date = '" . $present_date . "' and p.ppd_account = '" . $card_no . "' and p.ppd_deleted is NULL";
        $command           = $connection->createCommand($query);
        $data              = $command->queryScalar();
        if ($data > 0)
        {
            $result = array('result' => false, 'message' => 'Please call AccessOne at 888-394-3133 for assistance');
            header('Content-Type: application/json');
            echo CJSON::encode($result);
            exit;
        }
    }

    /** After payment success make a Entry in lps table. * */
    public function lpsPaymentEntery($pymt_details, $sheduledId, $paymt_type, $card_no)
    {
        $connection          = Yii::app()->db;
        $loan                = Loan::model()->find(array('condition' => 'lmt_card_no = "' . $card_no . '"'));
        $guarantor           = Guarantor::model()->find(array('condition' => 'id = "' . $loan->lmt_guar_id . '"'));
        $mobile_userid_query = "select ags_var_value from ags_app_global_setting where ags_var_key = 'mobile_payment_user_id'";
        $user_id             = $connection->createCommand($mobile_userid_query)->queryScalar();
        $user_id             = !empty($user_id) ? $user_id : '';
        $bank_acc_num        = isset($pymt_details['BankAccountNumber']) ? $this->stringMasking($pymt_details['BankAccountNumber']) : null;
        $credit_card_num     = isset($pymt_details['cardNumber']) ? $this->stringMasking($pymt_details['cardNumber']) : '0';

        $lpspaymt                     = new lpsPayment();
        $lpspaymt->lps_clnt_id        = $loan->lmt_clnt_id;
        $lpspaymt->lps_guar_id        = $loan->lmt_guar_id;
        $lpspaymt->lps_loan_ref_id    = $loan->lmt_loan_id;
        $lpspaymt->lps_paymt_date     = date('Y-m-d');
        $lpspaymt->lps_txn_type       = 'RP';
        $lpspaymt->lps_paymt_mode     = $paymt_type;
        $lpspaymt->lps_paymt_amt      = isset($pymt_details['paymentAmt']) ? $pymt_details['paymentAmt'] : 0.00;
        $lpspaymt->lps_req_user       = '';
        $lpspaymt->lps_status         = 'P';
        $lpspaymt->lps_comments       = 'Mobile Payment';
        $lpspaymt->lps_fac_charg_id   = '0';
        $lpspaymt->lps_fname          = $guarantor->gmt_guar_fname;
        $lpspaymt->lps_lname          = $guarantor->gmt_guar_lname;
        $lpspaymt->lps_bnk_route_no   = isset($pymt_details['bankRouteNumber']) ? $pymt_details['bankRouteNumber'] : null;
        $lpspaymt->lps_bnk_acc_no     = $bank_acc_num;
        $lpspaymt->lps_bnk_name       = '';
        $lpspaymt->lps_card_number    = $credit_card_num;
        $lpspaymt->lps_card_exp_month = '0';
        $lpspaymt->lps_card_exp_year  = '0';
        $lpspaymt->lps_zipcode        = isset($pymt_details['zipCode']) ? $pymt_details['zipCode'] : '0';
        $lpspaymt->lps_paymt_flag     = 'O';
        $lpspaymt->lps_mps_posting_id = $sheduledId;
        $lpspaymt->lps_entry_date     = date('Y-m-d');
        $lpspaymt->lps_user_id        = $user_id;
        $lpspaymt->lps_event_id       = '';
        $lpspaymt->lps_email_event_id = '';

        if ($lpspaymt->save())
        {
            /** Comments Entery * */
            $last_four_digit        = isset($pymt_details['BankAccountNumber']) ? substr($pymt_details['BankAccountNumber'], -4, 4) : substr($pymt_details['cardNumber'], -4, 4);
            $check_card_append_stmt = isset($pymt_details['BankAccountNumber']) ? " Check ending in " . $last_four_digit : " Card ending in " . $last_four_digit;
            $comment                = "Mobile App Payment of $" . $pymt_details['paymentAmt'] . " Auth# " . $sheduledId . $check_card_append_stmt;
            $query                  = 'CALL `sp_create_comment_by_event`("' . $comment . '","' . $user_id . '","' . $loan->lmt_guar_id . '","' . $loan->lmt_loan_id . '","' . $loan->lmt_clnt_id . '",1418)';
            $command                = $connection->createCommand($query);
            $command->execute();
        }
    }

    /**
     * String Masking
     * @param type $val
     * @return null
     */
    public function stringMasking($val)
    {
        if (!empty($val))
        {
            return substr_replace($val, str_repeat("*", strlen($val) - 4), 0, strlen($val) - 4);
        }
        else
        {
            return null;
        }
    }

    /**
     * Credit card lock down.
     * @param type $card_num
     */
    public function creditCardLockDown($card_num)
    {
        $sql                   = "select fn_get_card_lockdown_status('CARD'," . $card_num . ",0,0,0,'Mobile App')";
        $credit_card_lock_down = Yii::app()->db->createCommand($sql)->queryScalar();

        if ($credit_card_lock_down[0] == 1)
        {
            $result = array('result' => false, 'message' => 'Please call AccessOne at 888-394-3133 for assistance');
            header('Content-Type: application/json');
            echo CJSON::encode($result);
            exit;
        }
    }

    /** Payment Recourse Queue Restriction * */
    public function paymentRecourseRestriction($card_no)
    {
        $connection    = Yii::app()->db;
        $rec_queue_qry = "select count(*) from evt_tran_event join lmt_mst_loan ON lmt_loan_id = evt_loan_id where lmt_card_no = '" . $card_no . "' and evt_event_type_id IN (1605,1304,1305,1306)";
        $rec_queue_chk = $connection->createCommand($rec_queue_qry)->queryScalar();
        if ($rec_queue_chk > 0)
        {
            $result = array('result' => false, 'message' => 'Please call AccessOne at 888-394-3133 for assistance');
            header('Content-Type: application/json');
            echo CJSON::encode($result);
            exit;
        }
        return 1;
    }

    public function getSourceName()
    {
        $source      = Yii::app()->payment->createCommand()
                ->select('isc.import_source_code')
                ->from('pay_import_source_config isc')
                ->where('import_from = :import_from AND import_mode = :import_mode', array(':import_from' => 'mobile_app', ':import_mode' => 'Payment Management'))
                ->queryRow();
        $source_name = $source['import_source_code'];
        return $source_name;
    }
    
    public function actionGetSavedPayments()
    {
        $account_number = $this->validateToken();
        
        if (!empty($account_number))
        {
            $model = new PpdSavePayments();
            $result = $model->fetchSavedPayments($account_number);
            header('Content-Type: application/json');
            echo CJSON::encode($result);
            exit;
        }
        else
        {
            $this->_sendResponse(401);
        }
    }
    
    
    public function actionSetPayBySavedCC()
    {
        $account_number = $this->validateToken();

        if (!empty($account_number))
        {
            $json         = file_get_contents('php://input');
            $pymt_details = CJSON::decode($json, true);

            if (!empty($pymt_details))
            {
                $pymt_details['paymentType'] = 'CC';
                $pymt_details["useSavedPayment"] = 'Y';
                $model = new PpdSavePayments();
                $paymentId = $model->getSavePaymentId($pymt_details,$account_number);
                $pymt_details["savedPaymentId"]  = $paymentId;
                $this->actionCardMakePayment($pymt_details, $account_number);
            }
            else
            {
                $this->_sendResponse(500);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }
    
    public function actionSetPayBySavedCH()
    {
        $account_number = $this->validateToken();

        if (!empty($account_number))
        {
            $json         = file_get_contents('php://input');
            $pymt_details = CJSON::decode($json, true);

            if (!empty($pymt_details))
            {
                $pymt_details['paymentType']     = 'CH';
                $pymt_details["useSavedPayment"] = 'Y';
                $model                           = new PpdSavePayments();
                $paymentId                       = $model->getSavePaymentId($pymt_details, $account_number);
                $pymt_details["savedPaymentId"]  = $paymentId;
                $this->actionMakePayment($pymt_details, $account_number);
            }
            else
            {
                $this->_sendResponse(500);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

}
