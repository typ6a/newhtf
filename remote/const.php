<?php



if(isset($_SERVER['windir']) || (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'alexandar.localhost') || (isset($_SERVER['USERNAME']) && $_SERVER['USERNAME'] == 'KDG')){
	define('BASE_URL', 'http://alexandar.localhost/');
	define('BASE_DIR', 'd:/workspace/alexandar/');
	define('IS_LOCALHOST', true);
	define('IS_SERVER', false);
}else{
	define('BASE_URL', 'http://alexandar.kapver.net/');
	define('BASE_DIR', '/home4/brembul/alexandar/');
	define('IS_LOCALHOST', false);
	define('IS_SERVER', true);
}

define('EXT', '.php');

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'brembul_xbox');
define('DB_PASSWORD', 'brembulxbox');
define('DB_DATABASE', 'brembul_alexandar');
define('DB_TYPE', 'mysql');