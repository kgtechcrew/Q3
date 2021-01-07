<?php

class JOSE_JWT {
    public $header = array(
        'typ' => 'JWT',
        'alg' => 'none'
    );
    public $claims = array();
    public $signature = '';
    public $raw;

    
    public function init() {
        
    }
    
    public function __construct($claims = array()) {
        $this->claims = $claims;
    }

    public function toString() {
        return implode('.', array(
            $this->compact((object) $this->header),
            $this->compact((object) $this->claims),
            $this->compact($this->signature)
        ));
    }

    public function __toString() {
        return $this->toString();
    }

    public function sign($private_key_or_secret, $algorithm = 'HS256') {
        $jws = $this->toJWS();
        $jws->sign($private_key_or_secret, $algorithm);
        return $jws;
    }

    public function verify($public_key_or_secret, $alg = null) {
        $jws = $this->toJWS();
        $jws->verify($public_key_or_secret, $alg);
        return $jws;
    }

    public function encrypt($public_key_or_secret, $algorithm = 'RSA1_5', $encryption_method = 'A128CBC-HS256') {
        $jwe = new JOSE_JWE($this);
        $jwe->encrypt($public_key_or_secret, $algorithm, $encryption_method);
        return $jwe;
    }

    public static function encode($claims) {
        return new self($claims);
    }

    public static function decode($jwt_string) {
        $segments = explode('.', $jwt_string);
        switch (count($segments)) {
            case 3:
                $jwt = new self();
                $jwt->raw = $jwt_string;
                $jwt->header = (array) $jwt->extract($segments[0]);
                $jwt->claims = (array) $jwt->extract($segments[1]);
                $jwt->signature      = $jwt->extract($segments[2], 'as_binary');
                return $jwt;
            case 5:
                $jwe = new JOSE_JWE($jwt_string);
                $jwe->auth_data  = $segments[0];
                $jwe->header     = (array) $jwe->extract($segments[0]);
                $jwe->jwe_encrypted_key  = $jwe->extract($segments[1], 'as_binary');
                $jwe->iv                 = $jwe->extract($segments[2], 'as_binary');
                $jwe->cipher_text        = $jwe->extract($segments[3], 'as_binary');
                $jwe->authentication_tag = $jwe->extract($segments[4], 'as_binary');
                return $jwe;
            default:
                throw new JOSE_Exception_InvalidFormat('JWT should have exact 3 or 5 segments');
        }
    }

    public function compact($segment) {
        if (is_object($segment)) {
            $stringified = str_replace("\/", "/", json_encode($segment));
        } else {
            $stringified = $segment;
        }
        if ($stringified === 'null' && $segment !== null) { // shouldn't happen, just for safe
            throw new JOSE_Exception_InvalidFormat('Compact seriarization failed');
        }
        return JOSE_URLSafeBase64::encode($stringified);
    }

    public function extract($segment, $as_binary = false) {
        $stringified = JOSE_URLSafeBase64::decode($segment);
        if ($as_binary) {
            $extracted = $stringified;
        } else {
            $extracted = json_decode($stringified);
            if ($stringified !== 'null' && $extracted === null) {
                throw new JOSE_Exception_InvalidFormat('Compact de-serialization failed');
            }
        }
        return $extracted;
    }

    public function toJWS() {
        return new JOSE_JWS($this);
    }
}
