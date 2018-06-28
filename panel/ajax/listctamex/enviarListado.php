<?php
include("./../../../bower_components/nusoap/src/nusoap.php");
$client = new nusoap_client('http://www.delbravoweb.com/monitor/ws/server.php?wsdl','wsdl');
$err = $client->getError();
if ($err) {
	$respuesta['Codigo'] = -1;
	$respuesta['Mensaje'] = "Error al comunicarse con el WS";
}else{
	$param = array(
		'usuario' => 'admin',
		'password' => 'r0117c',
		'correos' => $_REQUEST['correos']
	);
	$result = $client->call('enviarlistado', $param);
	$respuesta['Codigo'] = $result['Codigo'];
	$respuesta['Mensaje'] = $result['Mensaje'];
}
echo json_encode($respuesta);
?>