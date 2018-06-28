<?php
include_once('./../../../../checklogin.php');
if($loggedIn == false){ header("Location: ../../../../login.php"); }
if (!isset($_GET['invoice'])) {
	exit("It has not received the reference");
}
$invoice=$_GET['invoice'];
$empresa=$_GET['mp'];
$tipo=$_GET['tipo'];
$key = "Encripta Del Bravo Links";
//$invoice=rawurldecode( rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(rawurldecode($invoice)), MCRYPT_MODE_CBC, md5(md5($key))), "\0"));
$invoice = rawurldecode(rtrim(openssl_decrypt(rawurldecode($invoice), 'bf-ecb', $key, true)));

$ruta = '';
if ($empresa == '1') {
	$ruta="\\\\192.168.1.107\\gabdata\\Avanza\\gab\\pdf\\".$tipo.$invoice.".pdf";
} else if ($empresa == '2') {
	$ruta="\\\\192.168.1.107\\gabdata\\Avanza\\sab\\pdf\\".$tipo.$invoice.".pdf";
} else {
	$ruta="\\\\192.168.1.107\\gabdata\\Avanza\\gab\\pdf\\".$tipo.$invoice.".pdf";
}

if (file_exists($ruta)){
	if (headers_sent()) {
		echo 'HTTP header already sent';
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header("Content-Type: application/pdf");
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length: ".filesize($ruta));
		header("Content-Disposition: attachment; filename=\"".$invoice.".pdf\"");
		header('Accept-Ranges: bytes');
		// check for IE only headers
		if (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) || (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false)) {
		  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		  header('Pragma: public');
		} else {
		  header('Pragma: no-cache');
		}
		@readfile($ruta);
		exit;
	}
}else{
	exit("La factura no existe, por favor contacte al administrador ".$ruta);
}
