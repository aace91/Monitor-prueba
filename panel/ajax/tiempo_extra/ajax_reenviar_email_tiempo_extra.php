<?php

include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('enviar_notificacion_email.php');

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);

if($loggedIn == false){
	return '500';
} else{
	$respuesta['Codigo'] = 1;

	if (isset($_POST['id_solicitud']) && !empty($_POST['id_solicitud'])) {		
		$id_solicitud = $_POST['id_solicitud'];
		
		$RespEmail = enviar_correo_nueva_solicitud($id_solicitud, $pdo_mysql_sconn, $pdo_accss_sconn, $mysqluser, $mysqlpass, $bDebug, 'Ejecutivo');
		if($RespEmail['Codigo'] != 1){	
			$respuesta['Codigo'] = 1;
			$respuesta['Mensaje'] = 'La solicitud de Servicio Prioritario fue enviada correctamente. Error al enviar notificación por correo electrónico. ['.$RespEmail['Error'].']';
		}else{
			$respuesta['Codigo'] = 1;
			$respuesta['Mensaje'] = 'La solicitud de Servicio Prioritario fue enviada correctamente.';
		}
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = '404 :: No se recibieron datos de entrada.';
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}