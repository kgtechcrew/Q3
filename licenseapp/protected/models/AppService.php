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
        if ($status == 'S')
        {
            Yii::app()->session['token']  = $result['key'];
            Yii::app()->session['userid'] = $result['userid'];
        }

        if (is_numeric($result['userid']))
        {
            $this->userDeviceService($result);
        }

        return $status;
    }

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
        $userdevice_info['flag']           = $result['status'];
        $userdevice_info['isexceeded']     = $result['isexceeded'];
        $userdevice_info['deviceexceeded'] = $result['deviceexceeded'];
        $url                               = USER_DEVICE_API;
        $token                             = Yii::app()->session['token'];
        $status                            = Controller::apiService($userdevice_info, $url, $token);
        Yii::app()->session['loginid']     = $status['loginid'];
    }

}
