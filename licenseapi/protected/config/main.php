<?php

require_once 'appconfig.php';
return array(
    'basePath'          => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name'              => 'LICENSE API',
    'defaultController' => 'license',
    'preload'           => array('log'),
    'language'          => 'en',
    'import'            => array(
        'application.models.*',
        'application.components.*',
        'application.controllers.*',
    ),
    'modules'           => array(
        'prototype',
        'gii' => array(
            'class'    => 'system.gii.GiiModule',
            'password' => 'kgisl',
        ),
    ),
    'components'        => array(
        'user'       => array(
            'allowAutoLogin' => false,
        ),
        'session'    => array(
            'sessionName' => md5("LICENSEAPI"),
            'class'       => 'CDbHttpSession',
            'timeout'     => 1800, //(3600 * 8) 1 hour * 8 
        ),
        'JWT'        => array(
            'class' => 'ext.JWT.JWT',
        ),
        'phpseclib'  => array(
            'class' => 'ext.phpseclib.PhpSecLib'
        ),
        'db'         => array(
            'connectionString' => 'mysql:host=' . DB_IP . ';dbname=' . DB_NAME,
            'username'         => DB_USERNAME,
            'password'         => DB_PASSWORD,
            'tablePrefix'      => 'tbl_',
            'emulatePrepare'   => true,
        ),
        'urlManager' => array(
            'urlFormat'      => 'path',
            'showScriptName' => false,
            'caseSensitive'  => true,
            'rules'          => array(
                'post/<id:\d+>/<title:.*?>'     => 'post/view',
                'posts/<tag:.*?>'               => 'post/index',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),
        'log'        => array(
            'class'  => 'CLogRouter',
            'routes' => array(
                array(
                    'class'   => 'CFileLogRoute',
                    'levels'  => LICENSEAPI_LOG_LEVEL,
                    'logPath' => LICENSEAPI_LOG_PATH,
                    'logFile' => "LICENSEAPI_" . date('m-d-Y') . '.log',
                ),
            ),
        ),
    ),
    'params'            => require(dirname(__FILE__) . '/params.php'),
);
