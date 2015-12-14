<?php 

ini_set('display_errors',1);
error_reporting(E_ALL);

function pprint($val, $return = false, $console = false){
	if(!$return){
		if(PHP_SAPI == 'cli') $console = true;
		if(!$console) echo "<div style=\"text-align: left;\"><pre style=".'"font-family: courier new; font-size: 12px;color:#333;"'.">\n ";
		print_r($val);
		if($console) echo "\n";
		if(!$console) echo "\n</pre></div>\n";
	} else return print_r($val, $return);
}

function pc($val, $flag = 0, $return = false) {
	pprint($val, $return, true);
	if ($flag) exit('ok');
}

function p($val, $flag = 0, $return = false) {
	pprint($val, $return);
	if ($flag) exit('ok');
}

function pre($val, $flag = 0, $return = false){
	p($val, $flag, $return);
}

function pcolorize($str){
	return preg_replace(array(
		'/=>\n\040+/', 
		'/(\040*\w+\((\d+|\w+)\)(#\d+)?[\040+]?)/',
		'/(\040*"\w+")/'
	), array(
		' => ', 
		'<span style="color:#f00">$1</span>',
		'<span style="color:#00f">$1</span>'
	), $str);
}

function pv($val, $flag = 0) {
	ob_start();
	//var_dump($val);
	$str = pcolorize(ob_get_contents());
	ob_flush();
	ob_end_clean();
	p($str, $flag);
}

function pgc($object, $stop = 1){
	p(get_class($object), $stop);
}
