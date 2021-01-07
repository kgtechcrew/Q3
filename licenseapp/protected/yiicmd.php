<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/../../framework/yii.php';
$config=dirname(__FILE__).'/config/console.php';

require_once($yii);

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
 
Yii::createConsoleApplication($config)->run();
