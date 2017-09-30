<?php

namespace App\Services;

class AliOssService
{
    private $accessKey = '';
    private $accessSecret = '';
    private $ossDomain = '';
    private $getPolicyFrom = '';
    private $callback = '';
    private $expire = 0;
    private $authorization = '';
    private $pubKeyUrl = '';
    private $isVerified = false;
    public function __construct()
    {
        foreach(config('aliOss') as $key => $val)
            $this->$key = $val;
    }
    public function policy($dir = '')
    {
        $id = $this->accessKey;
        $key = $this->accessSecret;
        $host = $this->ossDomain;
        $expire = $this->expire;
        $callbackUrl = $this->callback;
        $now = time();
        $end = $now + $expire;
        $expiration = $this->gmt_iso8601($end);
        $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
        $conditions[] = $condition;
        $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
        $conditions[] = $start;
        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));
        $callback_param = array
        (
            'callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded"
        );
        $callback_string = json_encode($callback_param);
        $base64_callback_body = base64_encode($callback_string);
        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['dir'] = $dir;
        $response['callback'] = $base64_callback_body;
        return response()->json($response);
    }
    public function callback(\Closure $callback)
    {
        try{
            $this->authorization = base64_decode($_SERVER['HTTP_AUTHORIZATION']);
            $this->pubKeyUrl = base64_decode($_SERVER['HTTP_X_OSS_PUB_KEY_URL']);
        } catch (\Exception $e)
        {
            return response('Forbidden', 403);
        }
        $this->verify();
        $callback($this->getFileInfo());
        return $this->response();
    }
    public function verify()
    {
        $authorization = $this->authorization;
        $pubKeyUrl = $this->pubKeyUrl;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pubKeyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $pubKey = curl_exec($ch);
        if(!$pubKey)
            exit();
        $body = file_get_contents('php://input');
        $authStr = '';
        $path = $_SERVER['REQUEST_URI'];
        $pos = strpos($path, '?');
        if ($pos === false)
            $authStr = urldecode($path)."\n".$body;
        else
            $authStr = urldecode(substr($path, 0, $pos)).substr($path, $pos, strlen($path) - $pos)."\n".$body;
        if(openssl_verify($authStr, $authorization, $pubKey, OPENSSL_ALGO_MD5) == 1)
            $this->isVerified = true;
        else
            return response('Forbidden', 403);
    }
    public function response()
    {
        if($this->isVerified)
            return response()->json(['Status' => 'Ok']);
        else
            return response('Forbidden', 403);
    }
    public function getFileInfo()
    {
        $rawData = file_get_contents('php://input');
        $rawData = explode('&', $rawData);
        $fileInfo = [];
        foreach($rawData as $r)
        {
            $r = explode('=', $r);
            $fileInfo[$r[0]] = $r[1];
        }
        $fileInfo['filename'] = urldecode($fileInfo['filename']);
        $fileInfo['mimeType'] = urldecode($fileInfo['mimeType']);
        return $fileInfo;
    }
    private function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $myDatetime = new \DateTime($dtStr);
        $expiration = $myDatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }
}