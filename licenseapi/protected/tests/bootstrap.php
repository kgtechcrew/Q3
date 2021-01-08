<?php

$yiit       = dirname(__FILE__) . '/../../../framework/yiit.php';
$app_config = dirname(__FILE__) . '/../config/test.php';

$_SERVER['SCRIPT_FILENAME'] = 'index-test.php';
$_SERVER['SCRIPT_NAME']     = '/index-test.php';
$_SERVER['REQUEST_URI']     = 'index-test.php';
$_SERVER['SERVER_NAME']     = 'localhost';

require_once($yiit);
require_once($app_config);
require_once(dirname(__FILE__) . '/WebTestCase.php');

Yii::createWebApplication($app_config);
