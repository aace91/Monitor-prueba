<?php
include_once('./../../../checklogin.php');
require('./../../../url_archivos.php');
require('consultar_controles_factura.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_REQUEST['id_cliente']) && !empty($_REQUEST['id_cliente'])) {
		$id_cliente = $_REQUEST['id_cliente'];
		$respuesta = consultar_controles_facturas_select($id_cliente);
		exit(json_encode($respuesta));
	}else{
		$response['Codigo']=-1;
		$response['Mensaje']='Error al recibir los datos.';
	}
	
}



