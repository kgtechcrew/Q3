<?php

/**
 * AppService class.
 */
class AppService extends CFormModel
{

    /**
     * Login service
     * @param type $loginForm
     * @return type
     */
    public function loginService($loginForm = array())
    {
        $result = Controller::loginApi($loginForm);
        $status = $result['status'];
        if ($status == 'success')
        {
            Yii::app()->session['token']  = $result['key'];
            Yii::app()->session['userid'] = $result['userid'];
        }
        if (is_numeric($result['userid']))
        {
            Yii::app()->session['userid'] = $result['userid'];
            $this->userDeviceService($result);
        }

        return $result;
    }

    /**
     * This function is used to send the 
     * @param type $result
     */
    public function userDeviceService($result = array())
    {
        $userdevice                        = new UserDevice();
        $phpSniff                          = new phpSniff();
        $sysbrowser                        = $userdevice->get_user_browser();
        $sysip                             = $phpSniff->_browser_info['ip'];
        $devtype                           = $userdevice->systemEnv();
        $sysos                             = $userdevice->getOS();
        $userdevice_info                   = array();
        $userdevice_info['userid']         = Yii::app()->session['userid'];
        $userdevice_info['sysip']          = $sysip;
        $userdevice_info['sysbrowser']     = $sysbrowser;
        $userdevice_info['sysos']          = $sysos;
        $userdevice_info['devtype']        = $devtype;
        $userdevice_info['flag']           = ($result['status'] == "success") ? 'S' : 'F';
        $userdevice_info['isexceeded']     = $result['isexceeded'];
        $userdevice_info['deviceexceeded'] = $result['deviceexceeded'];
        $url                               = USER_DEVICE_API;
        $token                             = !empty(Yii::app()->session['token']) ? Yii::app()->session['token'] : 'empty';
        $status                            = Controller::apiService($userdevice_info, $url, $token);
        Yii::app()->session['loginid']     = $status['loginid'];
    }

    public function dashboardService()
    {
        $json            = array();
        $token           = Yii::app()->session['token'];
        $json['loginid'] = Yii::app()->session['loginid'];
        $url             = DASHBOARD_API;
        $data            = Controller::apiService($json, $url, $token);
        return $data;
    }

    public function trackLoginUserService()
    {
        $json  = array();
        $token = Yii::app()->session['token'];
        $url   = TRACK_LOGIN_USER_API;
        $data  = Controller::apiService($json, $url, $token);
        return $data;
    }

    public function logoutService()
    {
        $json            = array();
        $url             = LOGOUT_API;
        $json['loginid'] = Yii::app()->session['loginid'];
        $token           = Yii::app()->session['token'];
        Controller::apiService($json, $url, $token);
        $this->destorySesssion();
    }

    public function destorySesssion()
    {
        Yii::app()->session->clear();
        Yii::app()->session->destroy();
    }

}
