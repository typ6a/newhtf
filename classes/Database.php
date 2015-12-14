<?php

$gdblink = null;

class Database {

	public $link = null;

	/* Connecting, selecting database */
	function __construct($user, $password, $host, $dbname){
		global $gdblink;
		if($gdblink){
			$this->link = $gdblink;
		}else{
			$this->link = $gdblink = mysqli_connect($host, $user, $password) or die("Could not connect : " . mysqli_error($this->link));
			mysqli_select_db($this->link, $dbname) or die("Could not select database");
		}
		if($this->link){
			mysqli_query($this->link, "SET NAMES utf8");
		}
	}

	/* Executing SQL query */
	function execute($query){
		#print $query . "<br />";
		$result = mysqli_query($this->link, $query) or die("Query failed : " . mysqli_error($this->link));
		return $result;
	}
	
	/* Getting last inserted ID */
	function get_insert_id(){
		return mysqli_insert_id($this->link);
	}

	/* Closing connection */
	function close(){
		mysqli_close($this->link);
	}

	/* Check is db connected */
	function get_connected(){
		return !is_null($this->link) && !$this->link == 0;
	}

	/* XZ - to remove */
	function make_sql_date($date){
		if(is_null ($date)){
			return null;
		}
		if(!is_int ($date)){
			trigger_error ("Invalid date ($date) passed to make_sql_date", E_USER_ERROR);
			return false;
		} return date ("Y-m-d H:i:s", $date);
	}

	/* XZ - to remove */
	function make_sql_value($value){
		switch(gettype ($value)){
			case "boolean":
				return ($value) ? (1) : (0);
				break;
			case "integer":
			case "double":
				return $value;
				break;
			case "string":
				return "'" . addslashes($value) . "'";
				break;
			case "NULL":
				return "null";
				break;
			default:
				trigger_error ("make_sql_value doesn't know how to handle type " . gettype ($value), "E_USER_ERROR");
		} return $value;
	}
}