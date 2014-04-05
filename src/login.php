<?php

require_once("include/Utilities.php");

OpenDB();

$username = NULL; 
$password = NULL;
		
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) { 
  	$username = $_SERVER['PHP_AUTH_USER']; 
    $password = md5($_SERVER['PHP_AUTH_PW']);

	// most other servers 
} elseif (isset($_SERVER['HTTP_AUTHENTICATION'])) {
      if (strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']),'basic')===0)  
         list($username,$password) = explode(':',md5(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
}

$email=$username;
	
if(is_null($email) || is_null($password)) {
    header('WWW-Authenticate: Basic realm="My Realm"'); 
	header('X-PHP-Response-Code: 401', true, 401);
	echo('{"Error":"2", "Message":"Authentication failed"}');
	CloseDB();
	die();
}else{
	
	$date = date('Y/m/d H:i:s');
	$token = $email.$date;
	$token = base64_encode($token);
		
	$query = "SELECT email,password FROM Users WHERE email = '$email' AND password = '$password'";
	$result = PostSQL($query);
	
	if($result && mysql_num_rows($result)>0){
		$query = "UPDATE  Users SET  token =  '$token' WHERE  email = '$email'";
		$result = PostSQL($query);
		echo('{"token":"'.$token.'"}');
	}else{
		header('X-PHP-Response-Code: 401', true, 401);
		echo('{"Error":"2", "Message":"Authentication failed"}');
	}	
}

CloseDB();

?>