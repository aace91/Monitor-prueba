<?php
	error_reporting( E_ALL ); 
	include_once('./../checklogin.php');
	$file=$_GET['file'];
	if($file==NULL or $file==''){
		echo 'No se recibio la información completa';
		exit();
	}
	if($loggedIn == false){ header("Location: ./../login.php"); }
	if (headers_sent()) {
		echo 'HTTP header already sent';
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length: ".filesize($file));
		header("Content-Disposition: inline; filename=\"Plantilla General Avanzada 5 ".date ("dmYHis.", filemtime($file))."xlsx\"");
		readfile($file);
		//unlink($file);
		exit;
	}
?>