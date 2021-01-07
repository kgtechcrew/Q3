<?php

/**
 * Description of Token
 *
 * @author dineshkumar.devaraj
 */
class Token
{
    
    public $useremail       = NULL;
    public $userid          = NULL;
    public $generated_token = NULL;
    
    public function generateToken($guid = NULL)
    {
        $iat   = time();
        $exp   = $iat + 1 * 60 * 10;
        
        /** Generating the guid **/
        $guid  = empty($guid)?$this->generateGuid():$guid;
        
        $user_token              = array();
        $user_token[0]['email']  = $this->useremail;
        $user_token[0]['userid'] = $this->userid;
        
        $token = array(
            "iat"      => $iat,
            "exp"      => $exp,
            "jti"      => $guid,
            "profiles" => $user_token,
        );
        
        $jwt = Yii::app()->JWT->encode($token);
        return $jwt;
    }

    /** Generating a GUID for the JWT payload * */
    public function generateGuid()
    {
        if (function_exists('com_create_guid'))
        {
            return trim(com_create_guid(), '{}');
        }
        else
        {
            mt_srand((double) microtime() * 10000); 
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); 
            $uuid   = chr(123)
                    . substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12)
                    . chr(125); 
            return trim($uuid, '{}');
        }
    }
}
