<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{

    public $layout = 'column1';
    public $menu   = array();
    public $format = 'json';
    public $connection;

    public function init()
    {
        try
        {
            $this->getDbConnection();
            $this->_checkAuth();
        }
        catch (Exception $e)
        {
            $this->_sendResponse(500);
            Yii::app()->user->setState("error", $e);
            Yii::app()->end();
        }
    }

    /** Checking the request received is based on the Token / Session * */
    public function _checkAuth()
    {
        $headers = getallheaders();/** retrieving all the headers * */
        if (isset($headers['Authorization']))
        {
            return true;
        }
        else
        {
            $exception_count = $this->exceptionUrls();
            if ($exception_count == 0)
            {
                $this->_sendResponse(401);
                Yii::app()->end();
            }
            else
            {
                return true;
            }
        }
    }

    /** Assigning the DB connection to global variable * */
    public function getDbConnection()
    {
        $this->connection = Yii::app()->db;
    }

    public function exceptionUrls()
    {
        $exception_urls  = array(
            'license/login',
        );
        $exception_count = 0;
        foreach ($exception_urls as $key => $value)
        {
            if (Yii::app()->urlManager->parseUrl(Yii::app()->request) == $value || stripos(Yii::app()->urlManager->parseUrl(Yii::app()->request), $value) !== FALSE)
            {
                $exception_count++;
            }
        }
        return $exception_count;
    }

    public function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        $status_header = 'HTTP/1.1 ' . $status . ' ' . Controller::_getStatusCodeMessage($status);
        header($status_header);
        header('Content-type: ' . $content_type);

        if ($body != '')
        {
            if ($content_type == 'application/xml')
            {
                $root_element = Controller::getRootElement();
                $xml          = Array2XML::createXML($root_element, $body);
                $body         = $xml->saveXML();
                $this->storeRequestLog(CJSON::encode($body));
                echo $body;
            }
            else
            {
                $this->storeRequestLog(CJSON::encode($body));
                echo CJSON::encode($body);
            }
            exit;
        }
        else
        {
            $message = '';
            switch ($status)
            {

                case 401:
                    $message = 'Invalid user/session. You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
                default :
                    $message = '';
                    break;
            }

            $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                        <html>
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                                <title>' . $status . ' ' . Controller::_getStatusCodeMessage($status) . '</title>
                            </head>
                            <body>
                                <h1>' . Controller::_getStatusCodeMessage($status) . '</h1>
                                <p>' . $message . '</p>
                                <hr />
                            </body>
                        </html>';

            echo $body;
            exit;
        }
    }

    /**
     * Gets the message for a status code
     * @param mixed $status 
     * @access private
     * @return string
     */
    public static function _getStatusCodeMessage($status)
    {
        $codes = Array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    /**
     * This function used for call the api services
     * @param type $json_data
     * @return type
     */
    public static function apiService($data, $url)
    {
        $json_data      = json_encode($data);
        $server         = WEBSVCSERVER;
        $restClient     = new RESTClient();
        $base_64_encode = base64_encode(WEBSVCUSERNAME . ':' . WEBSVCPASSWORD);
        $authentication = mb_convert_encoding($base_64_encode, 'US-ASCII', 'UTF-8');
        $restClient->set_header('Content-Type', 'application/json');
        $restClient->set_header('Accept', 'application/json');
        $restClient->set_header("Authorization Bearer {$access_token}");
        $restClient->set_header("Authorization", "Basic " . $authentication);
        $restClient->ssl(FALSE);
        $response = $restClient->post($server . $url, $json_data, 'json');
        $status   = $restClient->status();
        if ($status == 200)
        {
            return CJSON::decode(CJSON::encode($response, true), true);
        }
        else
        {
            return array();
        }
    }

    /**
     * This function used for call the login api service
     * @param type $loginForm
     * @return type
     */
    public static function loginApi($loginForm = array())
    {
        $server         = WEBSVCSERVER;
        $url            = LOGIN_API;
        $restClient     = new RESTClient();
        $base_64_encode = base64_encode($loginForm['username'] . ':' . $loginForm['password']);
        $authentication = mb_convert_encoding($base_64_encode, 'US-ASCII', 'UTF-8');
        $restClient->set_header('Content-Type', 'application/json');
        $restClient->set_header('Accept', 'application/json');
        $restClient->set_header("Authorization", "Basic " . $authentication);
        $restClient->ssl(FALSE);
        $json_data      = array();
        $response       = $restClient->post($server . $url, $json_data, 'json');
        $status         = $restClient->status();
        if ($status == 200)
        {
            return CJSON::decode(CJSON::encode($response, true), true);
        }
        else
        {
            return array();
        }
    }

}
