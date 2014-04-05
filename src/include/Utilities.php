<?php

include "config.php";

/********* DB Access functions *********/

function OpenDB()
{
	session_cache_expire(120);
	session_cache_limiter('nocache');
	session_start();

    $link = @mysql_connect(db_server, db_user, db_password);
    $_SESSION['db_link']=$link;
    @mysql_select_db(db_database);
    return $link;
}

function CloseDB()
{
    mysql_close ($_SESSION['db_link']); // ok ya termine de leer los datos
}

function GetSQL($query)
{
    $result = mysql_query($query,$_SESSION['db_link']);
    while($row = @mysql_fetch_assoc ($result))
        $ret[] = $row;

    return $ret;
}
function GetSQLFirstValue($query)
{
    $result = mysql_query($query, $_SESSION['db_link']);
    return mysql_result($result, 0,0);
}

function PostSQL($query)
{
    $result = mysql_query($query,$_SESSION['db_link']);
  
    return $result;
}
function GetAllTableData($table)
{
    return GetSql("select * from $table");
}
function GetTableDataByWhere($table, $where)
{
    return GetSql("select * from $table where $where");
}
/*
function printheader() {
	echo '<div id="headertitle">Crash Reports</div>';	
}
*/
function printheader() {
	echo '<div id="headertitle">Crash Reports</div>';
	echo '<a href="crashReports.php">Crashes</a> | <a href="users.php">Users</a>';
}

function printfooter() {
	echo "Copyright © 2012 Creative Coefficient. All rights reserved.";
}


//------------------------------------------------------------------------------
function isValidToken($token){

	if(!isset($token) || $token=="")
		return false;
		
	$query = "SELECT * FROM `Users` WHERE token = '".$token."'";
	$result = PostSQL($query);

	if(mysql_num_rows($result)==0){
		return false;
	}
	
	return true;
}
//------------------------------------------------------------------------------
function isValidBirthday($birthday){
	
	if(!isset($birthday) || $birthday=="")
		return false;
		
	$date = date( 'Y-m-d', strtotime( $birthday) );
		
	$pp = new DateTime($date);
	$today = new DateTime("now", new DateTimeZone('America/Argentina/Buenos_Aires'));
	$interval = $today->diff($pp);
	
	$days=$interval->days;
		
	if($days<365*18){
		return false;
	}
		
	return true;
}
//---------------------------------------------------------------------------------
function isValidGender($gender){

	if(!isset($gender) || $gender=="" || ($gender!="male" && $gender!="female")){
		return false;
	}
	return true;
}
//---------------------------------------------------------------------------------
function cleanProfile($profile){
	$array = array();
	
	if(isset($profile["firstName"])){
		$array["firstName"]=$profile["firstName"];
	}

	if(isset($profile["lastName"])){
		$array["lastName"]=$profile["lastName"];
	}
	
	if(isset($profile["email"])){
		$array["email"]=$profile["email"];	
	}
	
	if(isset($profile["password"])){
		$array["password"]=$profile["password"];		
	}
	
	if(isset($profile["gender"])){
		$array["gender"]=$profile["gender"];			
	}	
	
	if(isset($profile["birthday"])){
		$array["birthday"]=$profile["birthday"];				
	}	
	
	if(isset($profile["image"])){
		$array["image"]=$profile["image"];				
	}	
	
	return $array;
}
//---------------------------------------------------------------------------------
?>