<?php

$timeValue = 0;
$memoryValue = 0;
$resTimeValue = 0;

function getDiffMemory($pointNumber = 1, $blockSep = '') {
	global $memoryValue;
	$curr = memory_get_usage();
	if($memoryValue == 0){
		$res = 0;
	}else{
		$res = $curr - $memoryValue;
	}
	$memoryValue = $curr;
	if(isConsole()){
		echo $memoryValue . "\t" . round($memoryValue/1024/1024, 2) . "MB\t[" . $pointNumber . "]\t" . '(' . $res . ')' . "\n";
	}else{
		echo "<br/>" . $memoryValue . " " . number_format(round($memoryValue/1024/1024, 2), 2, '.', ',') . "MB [" . $pointNumber . "] " . '(' . $res . ')' . "<br/>";
	}
	if(!empty($blockSep)){
		echo $blockSep . "\n";
	}
}

function getDiffTime ($pointNumber = 'point', $getTotalDiff = false, $exit = false) {
	global $timeValue, $resTimeValue;
	//$curr = time();
	$curr = microtime(1);
	if($timeValue == 0){
		$res = 0;
	}else{
		$res = $curr - $timeValue;
	}
	$resTimeValue += $res;
	$timeValue = $curr;
	
	if(isConsole()){
		echo "-- " . date('H:i:s', $timeValue) . " -> " . number_format($res, 4) . " -> [" . $pointNumber . "] \n";
		if($getTotalDiff == true) echo '-- TOTAL TIME DIFF: ' . number_format($resTimeValue, 4) . " --\n";
	}else{
		echo " <br/>" . date('H:i:s', $timeValue) . " -> " . number_format($res, 4) . " -> [" . $pointNumber . "] <br/>";
		if($getTotalDiff == true) echo 'TOTAL TIME DIFF: ' . number_format($resTimeValue, 4) . '<br/>';
	}
	if($exit) exit;
}

function getDiffTimeAndMemory($pointNumber) {
	getDiffMemory($pointNumber);
	getDiffTime($pointNumber);
	echo '['.$pointNumber.'] ------------------------- <br/>';
}