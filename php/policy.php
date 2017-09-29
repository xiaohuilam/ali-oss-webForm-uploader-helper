<?php
$ossPolicyBuilder = new OssPolicyBuilder;
$ossPolicyBuilder->build();

class OssPolicyBuilder
{
    private $id = '';             // 阿里云的 Access Key ID
    private $key = '';            // 阿里云的 Access Key Secret
    private $host = '';           // OSS的外网访问地址
    private $callbackUrl = '';    // OSS服务器执行回调时发送请求的目标地址

    private $expire = 30;         // Policy的有效时间
    private $dir = 'test-dir/';   // 用户上传文件存放的目录

    public function build()
    {
        $id = $this->id;
        $key = $this->key;
        $host = $this->host;
        $expire = $this->expire;
        $dir = $this->dir;
        $callbackUrl = $this->callbackUrl;
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
        echo json_encode($response);
    }
    private function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z"; 
    }
}