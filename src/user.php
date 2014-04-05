<?php

require_once("include/Utilities.php");

OpenDB();

//---------------------------------------------------------------------------------
function createUser(){

	$username = NULL; 
    $password = NULL;
		
	if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) { 
    	$username = $_SERVER['PHP_AUTH_USER']; 
	    $password = md5($_SERVER['PHP_AUTH_PW']);

	// most other servers 
	} elseif (isset($_SERVER['HTTP_AUTHENTICATION'])) {
        if (strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']),'basic')===0)  
          list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
	}

	$email=$username;

	if(is_null($email) || is_null($password)) {
    	header('WWW-Authenticate: Basic realm="My Realm"'); 
	    header('X-PHP-Response-Code: 401', true, 401);
	    echo('{"Error":"2", "Message":"Authentication failed"}');
	    CloseDB();
	    die();
	}else{
		$firstName = isset($_POST["firstName"])?urldecode($_POST["firstName"]):"";
		$lastName = isset($_POST["lastName"])?urldecode($_POST["lastName"]):"";
		$gender = isset($_POST["gender"])?urldecode($_POST["gender"]):"";
		$birthday = isset($_POST["birthday"])?urldecode($_POST["birthday"]):"";			
	
		header("Content-type: application/json");
	
		if($firstName=="" || $lastName=="" || !isValidGender($gender) || !isValidBirthday($birthday)){
			header('X-PHP-Response-Code: 400', true, 400);
			echo('{"Error":"1", "Message":"Bad Request"}');
		
		}else{
			//$avatar = http_get_request_body();
    
    
		    $url = "";
    		if(isset($_FILES["image"])){
	    		$fileName = base64_encode($email).".png";
    			move_uploaded_file($_FILES["image"]["tmp_name"],"images/" .$fileName);
    			$url = image_folder.$fileName;
	    	}
	    				
		    $firstName = mysql_real_escape_string($firstName);
			$lastName = mysql_real_escape_string($lastName);
			$email = mysql_real_escape_string($email);
			$password = mysql_real_escape_string($password);
			$gender = mysql_real_escape_string($gender);
			$birthday = mysql_real_escape_string($birthday);
	
			$date = date('Y/m/d H:i:s');
			$token = $email.$date;
			$token = base64_encode($token);
		
			//stripslashes
	
			$query = "INSERT INTO Users (firstName,lastName,email,password,gender,birthday,token) VALUES ('$firstName','$lastName','$email','$password','$gender','$birthday','$token')";
	
			if(isset($url) && $url!="")
				$query = "INSERT INTO Users (firstName,lastName,email,password,gender,birthday,token,image) VALUES ('$firstName','$lastName','$email','$password','$gender','$birthday','$token','$url')";
	
			$result = PostSQL($query);
		
			if($result){
				header('X-PHP-Response-Code: 201', true, 201);
				echo('{"token":"'.$token.'"}');
			}else{
				header('X-PHP-Response-Code: 424', true, 424);
				echo('{"Error":"3", "Message":"User exist"}');
			}
		}

	}
}
//---------------------------------------------------------------------------------
function editUser(){

	$token = isset($_GET["token"])?urldecode($_GET["token"]):"";
	
	if(!isValidToken($token)){
		header('X-PHP-Response-Code: 401', true, 403);
		echo('{"Error":"2", "Message":"Authentication is required"}');
	}else{
	
    	$password = NULL;
		
		if (isset($_SERVER['PHP_AUTH_PW'])) { 
	  	  $password = md5($_SERVER['PHP_AUTH_PW']);

		// most other servers 
		} elseif (isset($_SERVER['HTTP_AUTHENTICATION'])) {
     	   if (strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']),'basic')===0)  
    	      list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		}

		if(is_null($password)) {
    		header('WWW-Authenticate: Basic realm="My Realm"'); 
		    header('X-PHP-Response-Code: 401', true, 401);
		    echo('{"Error":"2", "Message":"Authentication failed"}');
		    CloseDB();
		    die();
		}else{
		
			$url = "";
		    if(isset($_FILES["image"])){
			    $query = $query = "SELECT email FROM `Users` WHERE token = '".$token."'";
				$result = PostSQL($query);
				$row = mysql_fetch_array($result);
	    		$fileName = base64_encode($row["email"]).".png";
    			move_uploaded_file($_FILES["image"]["tmp_name"],"images/" .$fileName);
    			$url = image_folder.$fileName;
	    	}

			$putdata =file_get_contents('php://input');
			$output=array();
			parse_str($putdata,$output);

			$firstName = isset($output["firstName"])?urldecode($output["firstName"]):"";
			$lastName = isset($output["lastName"])?urldecode($output["lastName"]):"";
			$gender = isset($output["gender"])?urldecode($output["gender"]):"";
			$birthday = isset($output["birthday"])?urldecode($output["birthday"]):"";		

	 		$firstName = mysql_real_escape_string($firstName);
			$lastName = mysql_real_escape_string($lastName);

			$password = mysql_real_escape_string($password);
			$gender = mysql_real_escape_string($gender);
			$birthday = mysql_real_escape_string($birthday);
	
			if($firstName=="" || $lastName=="" || !isValidGender($gender) || !isValidBirthday($birthday)){
				header('X-PHP-Response-Code: 400', true, 400);
				echo('{"Error":"1", "Message":"Bad Request"}');	
				CloseDB();
				die();
			}

			$query = "UPDATE Users SET firstName = '$firstName', lastName = '$lastName', gender = '$gender',birthday = '$birthday',password = '$password' WHERE token = '$token'";
			
			if(isset($url) && $url!="")
				$query = "UPDATE Users SET firstName = '$firstName', lastName = '$lastName', gender = '$gender',birthday = '$birthday',password = '$password',image = '$url' WHERE token = '$token'";			
				
			$result = PostSQL($query);
		
			if($result){
				header('X-PHP-Response-Code: 201', true, 201);
				echo('{"Result":"OK"}');
			}else{
				header('X-PHP-Response-Code: 424', true, 424);
				echo('{"Error":"3", "Message":"User exist"}');
			}
		}
	}
}
//---------------------------------------------------------------------------------
function deleteUser(){
	$token = isset($_GET["token"])?urldecode($_GET["token"]):"";
	
	if(!isValidToken($token)){
		header('X-PHP-Response-Code: 401', true, 403);
		echo('{"Error":"2", "Message":"Authentication is required"}');
	}else{
		$query = "DELETE FROM Users WHERE token = '$token'";
		PostSQL($query);
		echo('{"Result":"OK"}');
	}
}
//---------------------------------------------------------------------------------
function getUsers(){
	
	$token = isset($_GET["token"])?urldecode($_GET["token"]):"";
	
	if(!isValidToken($token)){
		header('X-PHP-Response-Code: 401', true, 401);
		echo('{"Error":"2", "Message":"Authentication failed"}');
	}else{
	
		$query = $query = "SELECT firstName,lastName,email,birthday,gender,image FROM `Users` WHERE token = '".$token."'";
		$result = PostSQL($query);
		
		if($result && mysql_num_rows($result)!=0){
			$row = mysql_fetch_array($result);
			echo json_encode(cleanProfile($row));
		}else echo('{}');	
	}	
}
//---------------------------------------------------------------------------------

$http_method = $_SERVER['REQUEST_METHOD'];

if($http_method=="POST"){
	createUser();
}else if($http_method=="PUT"){
	editUser();
}else if($http_method=="DELETE"){
	deleteUser();
}else if($http_method=="GET"){
	getUsers();
}

CloseDB();

?>