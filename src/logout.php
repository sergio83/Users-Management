<?php

require_once("include/Utilities.php");



$token = isset($_GET["token"])?$_GET["token"]:""; 

if(!isset($token)){
	header('X-PHP-Response-Code: 401', true, 401);
	echo('{"Error":"2", "Message":"Authentication failed"}');
	die();
}


OpenDB();

$query = "UPDATE  Users SET  token =  '' WHERE  token = '$token'";

$result = PostSQL($query);
echo('{"Result":"OK"}');
		
CloseDB();
?>