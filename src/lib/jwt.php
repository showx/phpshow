<?php
/**
 * jwt(json web token)
 * 往config里获取key
 * 前端jwt校验(https://jwt.io/)
 * Author:show
 */

namespace phpshow\lib;


class jwt
{
    public $leeway = 0;
    /**
     * 加密
     */
    public function encode($payload,$key=''):string
    {
        if(empty($key))
        {
            $key = \phpshow\lib\config::get("signkey")['privatekey'];
            $key = $this->privatekey($key);
        }
        //分三段
        //header.payload.signature[ ha256(b64(header).b64(payload),secret) ]
        //header
        $header = array('typ' => 'JWT', 'alg' => "HS256");
        //payload
        $segments = array();
        $segments[] = $this->DataEncode(json_encode($header));
        $segments[] = $this->DataEncode(json_encode($payload));
        $signing_input = implode('.', $segments);
        //加密
        $signature = $this->sign($signing_input, $key);
        $segments[] = $this->DataEncode($signature);
        $segments_str = implode('.', $segments);
        return $segments_str;
    }

    /**
     * jwt解密
     * iss 签发人
     * exp 过期时间
     * sub 主题
     * aud 受众
     * nbf 生效时间
     * iat 签发时间
     * jti 编号
     * 自定义：使用nbf和exp就ok
     */
    public function decode($jwt, $key = '')
    {
        if(empty($key))
        {
            $key = \phpshow\lib\config::get("signkey")['publickey'];
            $key = $this->publickey($key);
        }
        $timestamp = time();
        if (empty($key)) {
            return false;
        }
        //一定是三个下标的数组
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            return false;
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = json_decode($this->DataDecode($headb64),false, 512, JSON_BIGINT_AS_STRING))) {
            return false;
        }
        if (null === $payload = json_decode($this->DataDecode($bodyb64), false, 512, JSON_BIGINT_AS_STRING)) {
            return false;
        }
        if (false === ($sig = $this->DataDecode($cryptob64))) {
            return false;
        }
        if (!$this->verify("$headb64.$bodyb64", $sig, $key, $header->alg)) {
            return false;
        }
        //验证nbf
        if (isset($payload->nbf)) {
            if($payload->nbf > ($timestamp + $this->leeway))
            {
                return false;
            }
        }
        //验证iat
        if (isset($payload->iat)) {
            if($payload->iat > ($timestamp + $this->leeway))
            {
                return false;
            }
        }
        //验证exp
        if (isset($payload->exp)) {
            if(($timestamp - $this->leeway) >= $payload->exp)
            {
                return false;
            }
        }else{
            //一定要加过期时间,exp必填项
            return false;
        }
        return $payload;
    }
    /**
     * 验证加密数据
     */
    private static function verify($msg, $signature, $key)
    {
        $success = openssl_verify($msg, $signature, $key,"SHA256");
        if ($success === 1) {
            return true;
        } elseif ($success === 0) {
            return false;
        }
    }
    /**
     * 加密方式，使用openssl sha256即可
     */
    public function sign($msg, $key)
    {
        $signature = '';
        $success = openssl_sign($msg, $signature, $key, "SHA256");
        if (!$success) {
            return false;
        } else {
            return $signature;
        }
    }

    /**
     * base64安全加密
     */
    public function DataEncode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * base64安全解密
     */
    public function DataDecode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
    /**
     * 获取openssl私钥
     */
    public function privatekey($privateKey)
    {
        $privateKey = chunk_split($privateKey,64,"\n");
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".$privateKey."-----END RSA PRIVATE KEY-----\n";
        $privateKey = openssl_get_privatekey($privateKey);
        return $privateKey;
    }
    /**
     * 获取openssl公钥
     */
    public function publickey($publicKey)
    {
        $publicKey = chunk_split($publicKey,64,"\n");
        $publicKey = "-----BEGIN PUBLIC KEY-----\n".$publicKey."-----END PUBLIC KEY-----\n";
        $publicKey = openssl_get_publickey($publicKey);
        return $publicKey;
    }

}