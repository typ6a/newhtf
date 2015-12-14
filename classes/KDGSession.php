<?php
 
class KDGSession {
	
	public static function set($name, $value){$_SESSION[$name] = $value;}
	public static function get($name, $default = false){return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;}
	public static function remove($name){if(isset($_SESSION[$name])) unset($_SESSION[$name]);}
	public static function has($name){return isset($_SESSION[$name]) ? true : false;}
	
}

session_start();