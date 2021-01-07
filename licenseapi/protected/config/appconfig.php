<?php

/** license api log, temp paths and log levels **/

defined('LICENSEAPI_LOG_PATH') || define('LICENSEAPI_LOG_PATH', sys_get_temp_dir());
defined('LICENSEAPI_TEMP_PATH') || define('LICENSEAPI_TEMP_PATH', sys_get_temp_dir());
defined('LICENSEAPI_LOG_LEVEL') || define('LICENSEAPI_LOG_LEVEL', 'error, warning');

/** DB Configurations * */
defined('DB_IP') || define('DB_IP', '127.0.0.1');
defined('DB_NAME') || define('DB_NAME', 'test');
defined('DB_USERNAME') || define('DB_USERNAME', 'root');
defined('DB_PASSWORD') || define('DB_PASSWORD', '');

/** DB Error Notification Mode * */
defined('DB_ERROR_NOTIFICATION') || define('DB_ERROR_NOTIFICATION', '1');

/** JWT SECRET KEY **/
defined('JWT_SECRET_KEY') || define('JWT_SECRET_KEY', 'ADEFG====');




