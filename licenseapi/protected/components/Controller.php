<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
    public $connection;
    public $renewed_token;

    /*
     * Entry Script of the Request
     */
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

   /*
    * Token Validation - token sent in the header is validated & verified here
    * whichin turn allows for the successful login
    */
    public function validateToken()
    {
        $user_id      = NULL;
        $payload_guid = NULL;
        
        /** retrieving all the headers **/
        $headers = getallheaders();
        
        if (isset($headers['Authorization']))
        {
            $jwt_header     = explode(' ', $headers['Authorization']);
            $jwt_auth_token = isset($jwt_header[1]) ? trim($jwt_header[1]) : '';

            /**
             * Decoding the jwtAuthToken sent in the request header (if token is expired unauthorized exception is thrown) 
             * Token Expiration Check
             **/
            try
            {
                $payload = Yii::app()->JWT->decode($jwt_auth_token);
            }
            catch (Exception $ex)
            {
                $ex_msg = $ex->getMessage();
                $this->storeRequestLog($ex_msg);
                $this->_sendResponse(401);
            }

            /** Checking whether the guarantor has successfully logged out with the payload GUID * */
            $payload_guid = $payload->jti;
            $user_id      = $payload->profiles[0]->userid;

            $logged_out = $this->checkSuccessfulLogout($user_id, $payload_guid);
            if ($logged_out == 1)
            {
                $this->storeRequestLog('USER HAS BEEN ALREADY LOGGED OUT FROM THE APPLICATION.');
                $this->_sendResponse(401);
            }
            else
            {
                
                $token_model            = new Token();
                $token_model->userid    = $user_id;
                $token_model->useremail = $payload->profiles[0]->email;
                $this->renewed_token    = $token_model->generateToken($payload_guid);
                
                return array('userid' => $user_id, 'guid' => $payload_guid);
            }
        }
        else
        {
            $this->_sendResponse(401);
        }
    }

    /**
     * 
     * @param type $ex_msg
     * storing the request logs 
     */
    public function storeRequestLog($ex_msg = '')
    {
        $headers           = CJSON::encode(getallheaders());
        $file_get_contents = file_get_contents('php://input');
        if(!empty($file_get_contents))
        {
            if(is_array($file_get_contents))
            {
                $input_details = CJSON::encode($file_get_contents);
            }
            else
            {
                $input_details = $file_get_contents;
            }
        }
        else
        {
            $input_details = '';
        }
        $sql           = "insert into lal_license_api_log(header_details,input_details,comments) values(:header_details,:input_details,:comments)";
        $command       = Yii::app()->db->createCommand($sql);
        $command->bindParam(":header_details", $headers);
        $command->bindParam(":input_details", $input_details);
        $command->bindParam(":comments", $ex_msg);
        $command->execute();
    }

    /**
     * 
     * @param type $user_id
     * @param type $payload_guid
     * @return int
     * checking whether the guid is already present in the blacklist table and confirming the logout
     */
    public function checkSuccessfulLogout($user_id, $payload_guid)
    {
        $logged_out = 0;
        $sql        = "select json_token_identifier from jbt_jti_blacklist_table where user_id = '" . $user_id . "'";
        $guids      = $this->connection->createCommand($sql)->queryAll();
        $ver_guids  = array();
        foreach ($guids as $key => $value)
        {
            $ver_guids[] = trim($value['json_token_identifier']);
        }
        if (in_array(trim($payload_guid), $ver_guids))
        {
            $logged_out = 1;
        }
        return $logged_out;
    }

    /** Assigning the DB connection to global variable * */
    public function getDbConnection()
    {
        $this->connection = Yii::app()->db;
    }

    /* 
     * Functions which doesn't need the authentication & authorization is mentioned here.
     * 
     */
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

    
    /*
     * Global Function used for returning the response body 
     */
    public function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        $status_header = 'HTTP/1.1 ' . $status . ' ' . Controller::_getStatusCodeMessage($status);
        header($status_header);
        header('Content-type: ' . $content_type);
        if($status == 200)
        {
            header('LoginToken: ' . $this->renewed_token);
        }
        
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

            // servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
            // this should be templatized in a real-world solution
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

}
