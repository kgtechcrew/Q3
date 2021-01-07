<?php

/**
 * JSON Web Tokens (JWT) Class
 *
 * @author Nabi KaramAliZadeh <nabikaz@gmail.com>
 * @link www.nabi.ir
 * @project https://github.com/NabiKAZ/yii-jwt
 * @since 2017-03-09
 * @version 1.0
 * @license GNU General Public License v3
 * @copyright 2017 Nabi Karamalizadeh
 *
 */
class JWT
{
    public $key;

    public function init()
    {
        require dirname(__FILE__) . '/src/JWT.php';
        require dirname(__FILE__) . '/src/BeforeValidException.php';
        require dirname(__FILE__) . '/src/ExpiredException.php';
        require dirname(__FILE__) . '/src/SignatureInvalidException.php';
        
        $this->key = trim(JWT_SECRET_KEY);
    }

    public function encode($payload)
    {
        return \Firebase\JWT\JWT::encode($payload, $this->key,'HS256');
    }

    public function decode($msg)
    {
        return \Firebase\JWT\JWT::decode($msg, $this->key, array('HS256'));
    }
    
    public function logoutDecode($msg)
    {
       return \Firebase\JWT\JWT::logoutDecode($msg, $this->key, array('HS256'));
    }
    
    public function encodePlainText($payload)
    {
        $this->key = trim(JWT_SECRET_KEY);
        return \Firebase\JWT\JWT::encode($payload, $this->key,'HS256');
    }
    
    public function decodeDecryptedText($msg)
    {
       $this->key = trim(JWT_SECRET_KEY);
       return \Firebase\JWT\JWT::decode($msg, $this->key, array('HS256'));
    }
    
}