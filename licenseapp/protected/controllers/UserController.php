<?php

/**
 * Description of login process.
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

    public function init()
    {
        
    }

    /**
     * This function is used to login the portal
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

    public function actionDashabord()
    {
        $model     = new AppService();
        $dashboard = $model->dashboardService();
        $this->render('dashabord', array('dashboard' => $dashboard));
    }

    public function actionTrackLoginUser()
    {
        $model      = new AppService();
        $login_user = $model->trackLoginUserService();
        $this->render('track', array('login_user' => $login_user));
    }

    public function actionLogout()
    {
        $model = new AppService();
        $model->logoutService();
        $this->render('login');
    }

}