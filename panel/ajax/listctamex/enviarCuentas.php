<?php
include_once('./../../../checklogin.php');

if($loggedIn == false){
	echo '500';
} else {	
	include("./../../../bower_components/nusoap/src/nusoap.php");
	$client = new nusoap_client('http://www.delbravoweb.tk/monitor/ws/server.php?wsdl','wsdl');
	$err = $client->getError();
	if ($err) {
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "Error al comunicarse con el WS";
	}else{
		$param = array(
			'usuario' => 'admin',
			'password' => 'r0117c',
			'cuentas' => json_encode($_POST['cuentas']),
			'correos' => $_POST['correos'],
			'omite_cliente' => $_POST['omite_cliente']
		);
		$result = $client->call('enviarcuentas', $param);
		$respuesta['Codigo'] = $result['Codigo'];
		$respuesta['Mensaje'] = $result['Mensaje'];
	}
	echo json_encode($respuesta);
}

?>