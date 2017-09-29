<?php
$ossCallback = new OssCallback;
$ossCallback->verify();
$fileInfo = $ossCallback->getFileInfo();

// 做一些业务处理

$ossCallback->response();

class OssCallback
{
	private $authorization;
	private $pubKeyUrl;
	private $isVerified = false;

	public function __construct()
	{
		try
		{
			$this->authorization = base64_decode($_SERVER['HTTP_AUTHORIZATION']);
			$this->pubKeyUrl = base64_decode($_SERVER['HTTP_X_OSS_PUB_KEY_URL']);
		} catch (Exception $e)
		{
			header("http/1.1 403 Forbidden");
		}
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
    		header("http/1.1 403 Forbidden");
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
	public function response()
	{
		if($this->isVerified)
		{
			header("Content-Type: application/json");
			echo json_encode(['Status' => 'Ok']);
		}
		else
			header("http/1.1 403 Forbidden");
	}
}