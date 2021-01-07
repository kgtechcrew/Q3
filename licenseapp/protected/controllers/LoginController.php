<?php

/**
 * Description of login process.
 *
 * @author srinivasan.k
 */
class LoginController extends Controller
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
        try
        {
            //$this->agsParams('user');
        }
        catch (Exception $e)
        {
//            if (DISPLAY_MAINTENANCE_SCREEN)
//            {
//                $this->redirect(Yii::app()->baseUrl . "/Maintenance.php");
//            }
//            else
//            {
//                Yii::app()->user->setState("error", $e);
//                $this->ShowError();
//                exit;
//            }
        }
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
            $result = $model->loginService($loginForm);
            if ($result == 'success')
            {
                echo $result;
            }
        }
        else
        {
            $this->render('login', array());
        }
    }

    public function actionDashabord()
    {
        $this->render('dashabord', array());
    }

}
