<?php

function getRequested($name, $default = null){
	if($name == 'cmd') $default = 'index';
	return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $default);
}

function getRequestParameter($name, $default = null){return getRequested($name, $default);}
function setRequestParameter($name, $value, $method = 'all'){
	if($method == 'all' || $method == 'get') $_GET[$name] = $value;
	if($method == 'all' || $method == 'post') $_POST[$name] = $value;
}

function redirect($uri = '', $method = 'location', $http_response_code = 302){
	global $config;
	if(!preg_match('#^https?://#i', $uri)){
		$uri = BASE_URL . ltrim($uri, '/');
	}
	switch($method){
		case 'refresh'	: header("Refresh:0;url=".$uri);
			break;
		default			: header("Location: ".$uri, TRUE, $http_response_code);
			break;
	} exit;
}

function isAjaxPost() {return (isPost() && isAjaxRequest()) ? true : false;}
function isAjaxRequest() {return (isXMLHTTPRequest()) ? true : false;}
function isXMLHTTPRequest() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;}

function isPost() {return (strtolower(getRequestMethod()) == 'post') ? true : false;}
function isGet() {return (strtolower(getRequestMethod()) == 'get') ? true : false;}
function getRequestMethod() {return $_SERVER['REQUEST_METHOD'] ? $_SERVER['REQUEST_METHOD'] : 'GET';}

function isConsole() {return PHP_SAPI == 'cli' ? true : false;}