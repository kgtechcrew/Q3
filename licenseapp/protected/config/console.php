<?php
$env_portal_type = 'LIVE';
$hostname        = isset($_SERVER['HOSTNAME']) ? $_SERVER['HOSTNAME'] : gethostname();
$path            = dirname(__FILE__);
if (stripos($hostname, 'GSS') !== false)
{
    $env_portal_type = 'DEV';
}
elseif (stripos($hostname, 'pharos') !== false && stripos($path, 'offshore-test'))
{
    $env_portal_type = 'OFFSHORE-WIN-TEST';
}
else if ($hostname == 'SV008-071060')
{
    $env_portal_type = 'UAT-CHARLOTTE';
}
else if ($hostname == 'SV008-070040')
{
    $env_portal_type = 'BETA-CHARLOTTE';
}
else
{
    $env_portal_type = 'LIVE';
}
/** Application Mode * */
define('APPLICTION_MODE', 'HFF');
defined('ENV_MODE') || define('ENV_MODE', $env_portal_type);

require_once $env_portal_type . '-appconfig.php';

return array(
    'basePath'   => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name'       => 'Mobile API',
    'preload'    => array('log'),
    'language'   => 'en',
    'import'     => array(
        'application.models.*',
        'application.components.*',
        'application.controllers.*',
        'application.extensions.phpmailer',
        'application.extensions.phpmailer.*',
        'application.extensions.phpmailer.JPhpMailer',
        'application.extensions.sftp.*',
        'application.extensions.ftp.*',
        'application.models.PaymentApi.*',
        'application.models.PaymentApi.genesis.*',
        'application.models.vantiv.*',
        /* MongoDB Config. */
        'ext.YiiMongoDbSuite.*',
    ),
    'components' => array(
        'user'    => array(
            'allowAutoLogin' => false,
        ),
        'session' => array(
            'sessionName' => md5("WEBSVC" . ENV_MODE . "_APPLICATION"),
            'class'       => 'CDbHttpSession',
            'timeout'     => 1800, //(3600 * 8) 1 hour * 8 
        ),
        'ftp'     => array(
            'class'       => 'application.extensions.ftp.EFtpComponent',
            'host'        => FTP_IP,
            'port'        => 21,
            'username'    => FTP_USERNAME,
            'password'    => FTP_PASSWORD,
            'ssl'         => false,
            'timeout'     => 90,
            'autoConnect' => true,
        ),
        'sftp'    => array(
            'class'    => 'application.extensions.sftp.SftpComponent',
            'host'     => SFTP_IP,
            'port'     => 22,
            'username' => SFTP_USERNAME,
            'password' => SFTP_PASSWORD,
        ),
        'JWT'     => array(
            'class' => 'ext.JWT.JWT',
        ),
        'db'      => array(
            'connectionString' => 'mysql:host=' . DB_IP . ';dbname=' . DB_NAME,
            'username'         => DB_USERNAME,
            'password'         => DB_PASSWORD,
            'tablePrefix'      => 'tbl_',
            'emulatePrepare'   => true,
        ),
        'payment' => array(
            'connectionString' => 'mysql:host=' . DB_IP . ';dbname=' . PAYMENT_DB_NAME,
            'username'         => DB_USERNAME,
            'password'         => DB_PASSWORD,
            'tablePrefix'      => 'tbl_',
            'emulatePrepare'   => true,
            'class'            => 'CDbConnection',
        ),
        'mongodb'       => array(
            'class'            => 'EMongoDB',
            'connectionString' => MONGODB_CONN_STRING,
            'dbName'           => MONGO_DB_NAME,
            'fsyncFlag'        => true,
            'safeFlag'         => true,
            'useCursor'        => false
        ),
        'errorHandler' => array(
            'class' => 'HFFErrorHandler',
        ),
        'urlManager' => array(
            'urlFormat'      => 'path',
            'showScriptName' => false,
            'caseSensitive'  => true,
            'rules'          => array(
                'post/<id:\d+>/<title:.*?>'           => 'post/view',
                'posts/<tag:.*?>'                     => 'post/index',
                '<controller:\w+>/<action:\w+>'       => '<controller>/<action>',
                'Mobile/v2/auth/login'                => 'mobilev2/login',
                'Mobile/v2/auth/logout'               => 'mobilev2/logout',
                'Mobile/v2/auth/passwordresetrequest' => 'mobilev2/passwordresetrequest',
                'Mobile/v2/auth/passwordreset'        => 'mobilev2/passwordreset',
                'Mobile/v2/auth/passwordpolicy'       => 'mobilev2/passwordpolicy',
                'Mobile/v2/signup'                    => 'mobilev2/signup'
            ),
        ),
        'log'        => array(
            'class'  => 'CLogRouter',
            'routes' => array(
                array(
                    'class'   => 'CFileLogRoute',
                    'levels'  => WEBSVC_LOG_LEVEL,
                    'logPath' => WEBSVC_LOG_PATH,
                    'logFile' => "HFFAPI_" . ENV_MODE . "_" . date('m-d-Y') . '.log',
                ),
            ),
        ),
    ),
    'params'     => require(dirname(__FILE__) . '/params.php'),
);
