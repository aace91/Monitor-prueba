<?php

function shutdown() 
{ 
    $a=error_get_last(); 
    if($a==null){   
        //error_log("No errors checklogin"); 
	}else 
        error_log(json_encode($a)); 
} 
register_shutdown_function('shutdown'); 

$loggedIn = "";
$status = session_status();
if($status == PHP_SESSION_NONE){
	session_start();
}

$id = '';
$username = '';
$uniqid = '';
$usunivel = '';
$usuemail = '';

if(isset($_SESSION['id'])){
	$id = $_SESSION['id'];
	$username = $_SESSION['username'];
	$uniqid = $_SESSION['uid'];
	$usunivel = $_SESSION['usunivel'];
	$usuemail = $_SESSION['usuemail'];
	//$clienteinv = $_SESSION['clienteinv'];
	if($uniqid == sha1($id)){
		include('connect_gabsql1.php');
		$queryl="SELECT `Usuario_id` FROM `tblusua` WHERE `Usuario_id` = '$id' AND `usuEmail` = '$usuemail' LIMIT 1";
		$loginCheck = mysqli_query($cmysqli_gab1,$queryl) or die(mysql_error());
		$numberLog = mysqli_num_rows($loginCheck);
		mysqli_close($cmysqli_gab1);
		if($numberLog == 1){
			$loggedIn = true;	
		} else {
			$loggedIn = false;
			session_destroy();
			header("Location: ../login.php");
		}
	}else{
		$loggedIn = false;
		session_destroy();
		header("Location: ../login.php");
	}
} else {
	$loggedIn = false;
}
?>