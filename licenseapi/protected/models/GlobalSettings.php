<?php

class GlobalSettings extends CFormModel
{

    /*
     * fetches the active users currently loggedin to the system / application.
     */
    public function concurrentUserCount()
    {
        $sql = "SELECT COUNT(*) FROM (
                    SELECT COUNT(*),user_id FROM license_his_user_log 
                    WHERE login_status = 'S'  AND logout_status IS NULL
                    GROUP BY user_id HAVING COUNT(*) >= 1) license_table";

        $count = Yii::app()->db->createCommand($sql)->queryScalar();
        return $count;
    }

    /*
     * used to fetch the global concurrent users count who can able to login into the system simultaneously 
     */
    public function usersAllowedCount()
    {
        $sql          = "SELECT global_value FROM lgt_license_global_table where global_key = 'concurrent_users'";
        $allowd_count = Yii::app()->db->createCommand($sql)->queryScalar();
        return $allowd_count;
    }

    /*
     * Validating the concurrent users in the system
     * If the new user tries to login while maximum count of concurrent users are also loggedin
     * then the system will restrict the user to login.
     */
    public function isConcurrentUsersExceeded()
    {
        $concurrent_user_count = $this->concurrentUserCount();
        $users_allowed_count   = $this->usersAllowedCount();
        $user_waiting_count    = 1;
        if (($concurrent_user_count + $user_waiting_count) > $users_allowed_count)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /*
     * fetching the user's active multiple logged in devices count
     */
    public function checkUserDevicesCount($userid)
    {
        $sql = "SELECT COUNT(*) FROM license_his_user_log WHERE user_id = ".$userid." AND login_status = 'S' AND logout_status IS NULL";
        $count = Yii::app()->db->createCommand($sql)->queryScalar();
        return $count;
    }
    
    /*
     * Used to fetch the global allowed devices count for a particular user
     */
    public function devicesAllowedCount()
    {
        $sql                   = "SELECT global_value FROM lgt_license_global_table where global_key = 'allowed_devices'";
        $devices_allowed_count = Yii::app()->db->createCommand($sql)->queryScalar();
        return $devices_allowed_count;
    }

    /*
     * Validating the user's active devices login count and if it 
     * exceeds the predefined count in the global table
     * it will be restricted
     */
    public function isUsersAllowedDevicesExceeded($userid)
    {
        $users_active_dev_count  = $this->checkUserDevicesCount($userid);
        $devices_allowed_count   = $this->devicesAllowedCount();
        $user_waiting_count      = 1;
        if (($users_active_dev_count + $user_waiting_count) > $devices_allowed_count)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /*
     * Used to fetch the current timestamp of mysql
     */
    public function fetchDBTime()
    {
        $sql      = "select now()";
        $datetime = Yii::app()->db->createCommand($sql)->queryScalar();
        return $datetime;
    }
    
    /*
     * This function is used for returning the response for all the license related request actions.
     * LCE    --------> Login Count Exceeded
     * ADE    --------> Allowed Devices Exceeded
     * LS     --------> Login Successfull
     * VPWD   --------> Is Valid Password
     * VUNAME --------> Is valid Username
     */
    public function formatResponseMessages($mode, $token = array())
    {
        $controller = new Controller(1);
        $token = empty($token)? new Token() : $token;
        $result = array();
        switch($mode)
        {
            case "LCE":
                $result['status']             = 'F';
                $result['key']                = NULL;
                $result['userid']             = $token->userid;
                $result['isexceeded']         = 'Y';
                $result['deviceexceeded']     = 'N';
                $result['message']            = Yii::t('ui','LCE');
                $controller->_sendResponse(200, $result, "Content-Type: application/json");
                break;
            case "ADE":
                $result['status']              = 'F';
                $result['key']                 = NULL;
                $result['userid']              = $token->userid;
                $result['isexceeded']          = 'N';
                $result['deviceexceeded']      = 'Y';
                $result['message']             = Yii::t('ui','ADE');
                $controller->_sendResponse(200, $result, "Content-Type: application/json");
                break;
            case "LS":
                $result['status']              = 'S';
                $result['key']                 = $token->generated_token;
                $result['userid']              = $token->userid;
                $result['isexceeded']          = 'N';
                $result['deviceexceeded']      = 'N';
                $result['message']             = Yii::t('ui','LS');
                $controller->_sendResponse(200, $result, "Content-Type: application/json");
                break;
            case "VPWD":
                $result['status']              = 'F';
                $result['key']                 =  NULL;
                $result['userid']              = $token->userid;
                $result['isexceeded']          = 'N';
                $result['deviceexceeded']      = 'N';
                $result['message']             = Yii::t('ui','VPWD');
                $controller->_sendResponse(200, $result, "Content-Type: application/json");
                break;
            case "VUNAME":
                $result['status']              = 'F';
                $result['key']                 = NULL;
                $result['userid']              = NULL;
                $result['isexceeded']          = 'N';
                $result['deviceexceeded']      = 'N';
                $result['message']             = Yii::t('ui','VUNAME');
                $controller->_sendResponse(200, $result, "Content-Type: application/json");
                break;
            default:
                $result['status']              = NULL;
                $result['key']                 = NULL;
                $result['userid']              = NULL;
                $result['isexceeded']          = NULL;
                $result['deviceexceeded']      = NULL;
                $result['message']             = NULL;
                $controller->_sendResponse(200, $result, "Content-Type: application/json");
                break;   
        }
    }

}
