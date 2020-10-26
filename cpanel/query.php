<?php
require_once("util.php");

function query($uri)
{
	$ADDRESS = "http://127.0.0.1:8080/";
	
	return explode("\n\r", file_get_contents($ADDRESS . urlencode($uri)))[0];
}

function getlog($uid, $count = 5)
{
	$file = "../system/units/$uid/server.log";

	$lines = "";
	
	$f = rfile($file, $count);
	
	for($i = 0; $i < $count; $i++) $lines .= $f[$i];
	
	unset($f);
	
	return $lines;
}


$GLOBAL_PORT = 19132;
$USER_ID = "u" . $GLOBAL_PORT;

$act = $_GET['act'];
$data = 0;

if($act[0] == "\"") {
	echo query("unit $USER_ID $act");
	exit(0);
}

if(isset(explode(";", $act)[1]))
	$data = explode(";", $act)[1];

$act = explode(";", $act)[0];

switch($act)
{
	case "memory":
		echo query("unit $USER_ID memory");
	break;
	
	case "cpu":
		echo query("unit $USER_ID cpu");
	break;
	
	case "cache":
		echo query("unit $USER_ID cache");
	break;
	
	case "start":
		echo query("unit $USER_ID start");
	break;
	
	case "stop":
		query("unit $USER_ID stop");
		
		echo query("unit $USER_ID kill");
	break;
	
	case "kill":
		echo query("unit $USER_ID kill");
	break;
	
	case "srvlog":
		echo getlog($USER_ID, $data);
	break;
}



?>