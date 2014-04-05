<?php

require_once("include/Utilities.php");

OpenDB();

//---------------------------------------------------------------------------------
function getUsers(){
	
	$token = isset($_GET["token"])?urldecode($_GET["token"]):"";
	
	if(!isValidToken($token)){
		header('X-PHP-Response-Code: 401', true, 401);
		echo('{"Error":"2", "Message":"Authentication failed"}');
	}else{
	
		$query = $query = "SELECT firstName,lastName,email,birthday,gender FROM `Users` WHERE NOT token = '".$token."'";
		$result = PostSQL($query);
		
		if($result && mysql_num_rows($result)!=0){
			$users = array();
			$i=0;
			while($row = mysql_fetch_array($result)){
				$users[$i] = cleanProfile($row);
				$i++;
			}
			
			echo('{"users":'.json_encode($users).'}');
		}else echo('{"users":[]}');	
	}	
}
//---------------------------------------------------------------------------------

$http_method = $_SERVER['REQUEST_METHOD'];

if($http_method=="GET"){
	getUsers();
}

CloseDB();

?>