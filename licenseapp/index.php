<?php

/** Debug Enable "true / false" * */
defined('YII_DEBUG') || define('YII_DEBUG', true);

/** Trace level "1 / 2 / 3" * */
defined('YII_TRACE_LEVEL') || define('YII_TRACE_LEVEL', 3);

$yii    = dirname(__FILE__) . '/../../framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';

require_once($yii);
Yii::createWebApplication($config)->run();

