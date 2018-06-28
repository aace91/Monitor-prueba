<?php
include_once('../checklogin.php');
include('./../connect_dbsql.php');

if($loggedIn == false){ header("Location: ../login.php"); }
if (!isset($_GET['invoice'])) {
	exit("It has not received the reference");
}
$invoice=$_GET['invoice'];
$key = "Encripta Del Bravo Links";
//$invoice=rawurldecode( rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(rawurldecode($invoice)), MCRYPT_MODE_CBC, md5(md5($key))), "\0"));
$invoice = rawurldecode(rtrim(openssl_decrypt(rawurldecode($invoice), 'bf-ecb', $key, true)));

$consulta="
SELECT 
	usupasswd as password 
FROM 
	`tblusua` 
WHERE 
	tblusua.usuario_id = '$id'";

$query = mysqli_query($cmysqli, $consulta);
$number = mysqli_num_rows($query);

if($number == 1){
	while($row = mysqli_fetch_array($query)){
		$pass_cli = $row['password'];
	}
}
$URL = "https://www.delbravoapps.com/ws_sii/login.php?usuario=$usuemail&password=$pass_cli&tipo_perfil=3";
$data_login = json_decode(file_get_contents($URL));
if($data_login->codigo!=1){
	exit("Error al entrar al ws de bodega. ".$data_login->mensaje);
}
$perfil=$data_login->perfil;
$id_ac_ws=$perfil->id_acceso;
$token_ws=$perfil->token;
header("Location: https://www.delbravoapps.com/ws_sii/documentos.php?id_acceso=$id_ac_ws&token=$token_ws&tipo_perfil=3&metodo=ver_documentos_ctaame_delbravo&txnid=$invoice");
