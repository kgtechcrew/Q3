<?php

/**
 * Description : This controller is used for call the all license api services.
 *
 * @author srinivasan.k
 */
class UserController extends Controller
{

    public $layout = '//layouts/main';

    public function filters()
    {
        return array(
            'accessControl',
            'postOnly + delete',
        );
    }

    /** Initialize Process  * */
    public function init()
    {
        
    }

    /**
     * Login process
     * Basic Authorization username and password
     */
    public function actionLogin()
    {
        $request   = Yii::app()->request;
        $loginForm = $request->getParam('loginForm');
        $model     = new AppService();

        if (!empty($loginForm))
        {
            $result   = $model->loginService($loginForm);
            $response = array('status' => $result['status'], 'message' => $result['message']);
            echo CJSON::encode($response);
        }
        else
        {
            $this->render('login');
        }
    }

    /**
     * Dashboard process
     */
    public function actionDashboard()
    {
        $model     = new AppService();
        $dashboard = $model->dashboardService();
        $this->render('dashabord', array('dashboard' => $dashboard));
    }

    /**
     * Get Tracking Login User Information
     */
    public function actionTrackLoginUser()
    {
        $model      = new AppService();
        $login_user = $model->trackLoginUserService();
        $this->render('track', array('login_user' => $login_user));
    }

    /**
     * Logout Process
     */
    public function actionLogout()
    {
        $model = new AppService();
        $model->logoutService();
        $this->render('login');
    }

}
