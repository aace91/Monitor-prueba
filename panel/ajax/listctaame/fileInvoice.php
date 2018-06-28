<?php

include_once('./../../../checklogin.php');
include('./../connect_dbsql.php');
	
if ($loggedIn == false){
	$respuesta['Mensaje']='La sesión del usuario ha caducado, por favor acceda de nuevo.';
	$respuesta['Error']= ' ['.$error.']';
	echo json_encode($respuesta);
} else {
	$respuesta['Codigo'] = 1;
	if(!isset($_REQUEST['id'])){	
		exit('No se recibieron datos');
	}
	$sTxnID = $_REQUEST['id'];
	
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
	header("Location: https://www.delbravoapps.com/ws_sii/documentos.php?id_acceso=$id_ac_ws&token=$token_ws&tipo_perfil=3&metodo=ver_documentos_ctaame_delbravo&txnid=$sTxnID");
}