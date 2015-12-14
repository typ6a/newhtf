<?php

class Crawler {

	protected static $cookiesFile	=  'cookies.txt';
	protected static $authTokenFile	= 'auth-token.txt';
	protected static $authDataFile	= 'auth-data.txt';
	
	protected static $authToken		= null;
	protected static $authData		= array();
    
    protected $response = null;

	//protected static $response_info	= array();
	
	public function __construct(){
		set_time_limit(0);
		$this->startBuffering();
		$this->execute();
	}
	
	public function __destruct(){
		$this->endBuffering();	
	}
	
	public static function makeRequest($url, $data = array(), $login = false, $useCookies = true){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.6 Safari/537.11');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		//curl_setopt($ch, CURLOPT_PROXY, '94.231.182.6');
		//curl_setopt($ch, CURLOPT_PROXYPORT, '8080');
		if($data){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			if($login){
				curl_setopt($ch, CURLOPT_COOKIEJAR, self::getCookiesFile());
			}
		}
		if($useCookies){
			curl_setopt($ch, CURLOPT_COOKIEFILE, self::getCookiesFile());
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		//self::$response_info = curl_getinfo($ch);
        $result = curl_exec($ch);
		curl_close($ch);
		return $result;
    }
	
	public function setPage($page){$this->page = $page;}
	public function getPage(){return $this->page;}
	public function hasPage(){return !is_null($this->page) ? true : false;}
	
	protected function startBuffering(){
		@ob_start();
		@ob_end_flush();
		if(!isConsole()){
			echo '
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
			<html>
			<head><meta http-equiv="Content-Script-Type" content="text/javascript" /></head>
			<body>';
		}
	}

	protected function sendBuffered($content = null){
		if($content) echo $content.(isConsole() ? "\n" : '</br>');
		@ob_flush();
		@flush();
	}

	protected function endBuffering(){
		$this->sendBuffered();
		if(!isConsole()){
			echo '</body></html>';
		}
	}

	protected function isLogedIn(){
		if((time() - filemtime(self::getCookiesFile())) < 86400){
			return true;
		} return false;
	}
	
	protected function execute(){
		if(!file_exists(self::getCookiesFile()) || !file_exists(self::getAuthTokenFile()) || !$this->isLogedIn()){
			self::executeLogin();
		}else{
			self::$authToken = self::getFromFile(self::getAuthTokenFile());
		}
	}
	
	public static function getLoginToken(){
		$data = array();
		$response = self::makeRequest('http://www.goodreads.com/user/sign_in', array('field'=>'value'), true);
		preg_match('/<input name="authenticity_token" type="hidden" value="(.+)" \/>/', $response, $matches);
		if(isset($matches[1])){
			$data['token'] = $matches[1];
		}
		$matches = array();
		preg_match("/<input name='n' type='hidden' value='(.+)'>/", $response, $matches);
		if(isset($matches[1])){
			$data['n'] = $matches[1];
		}
		return $data;
	}
	
	public static function executeLogin(){
		$loginFormData = self::getLoginToken();
		//p($loginFormData,1);
		$url = 'https://www.goodreads.com/user/sign_in';
		$res = self::makeRequest($url, array(
			'n' => $loginFormData['n'],
			'next' => 'Sign in',
			'utf8' => '✓',
			'authenticity_token' => $loginFormData['token'],
			'user[email]' => SERVICE_USERNAME,
			'user[password]' => SERVICE_PASSWORD,
		), true);
		preg_match('/.+content="(.+)" name="csrf-token".+/', $res, $matches);
		if(KDGModel::isINE($matches, 1)){
			self::$authToken = $matches[1];
			self::saveToFile(self::getAuthTokenFile(), self::$authToken);
		}
	}
	
	public static function saveToFile($path, $string = null){
		$fh = fopen($path, 'w');
		fwrite($fh, $string);
		fclose($fh);
	}
	
	public static function getFromFile($path){
		return file_get_contents($path);
	}
	
	public static function getCookiesFile(){
		return BASE_DIR . self::$cookiesFile;
	}
	
	public static function getAuthTokenFile(){
		return BASE_DIR . self::$authTokenFile;
	}

}