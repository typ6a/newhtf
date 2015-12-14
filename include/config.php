<?php

set_time_limit(600);

require_once('debug.php');
require_once('const.php');
require_once(BASE_DIR . 'classes/KDGLoader.php');

$config = array();

$config['db_user'] = DB_USERNAME;
$config['db_password'] = DB_PASSWORD;
$config['db_host'] = DB_SERVER;
$config['db_name'] = DB_DATABASE;

KDGLoader::loadLibraryClass('KDGGeneral');
KDGLoader::loadLibraryClass('KDGSession');

KDGLoader::loadLibraryClass('KDGModel');
KDGLoader::loadLibraryClass('Encoding');
KDGLoader::loadLibraryClass('KDGMemory');
KDGLoader::loadLibraryClass('ISDString');
KDGLoader::loadLibraryClass('Inflector');
KDGLoader::loadLibraryClass('KDGEntity');
KDGLoader::loadLibraryClass('KDGParser');
KDGLoader::loadLibraryClass('KDGCrawler');
KDGLoader::loadLibraryClass('KDGDatabase');
KDGLoader::loadLibraryClass('KDGInflector');

if(isConsole() && isset($argv) && $argv){
	foreach($argv as $val){
		if(stristr($val, '=')){
			$parts = explode('=', $val);
			if(isset($parts[0]) && isset($parts[1])){
				setRequestParameter($parts[0], $parts[1]);
			}
		}
	}
}