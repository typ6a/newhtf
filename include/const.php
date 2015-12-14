<?php

if(isset($_SERVER['windir']) || (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'newhtf.localhost') || (isset($_SERVER['USERNAME']) && $_SERVER['USERNAME'] == 'KDG')){
	define('BASE_URL', 'http://newhtf.localhost/');
	define('BASE_DIR', 'd:/workspace/newhtf/');
	define('IS_LOCALHOST', true);
	define('IS_SERVER', false);
}else{
	define('BASE_URL', 'http://newhtf.kapver.net/');
	define('BASE_DIR', '/home4/brembul/newhtf/');
	define('IS_LOCALHOST', false);
	define('IS_SERVER', true);
}

define('EXT', '.php');

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'projects');
define('DB_DATABASE', 'newhtf');
define('DB_TYPE', 'mysql');