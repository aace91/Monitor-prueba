<?php
include ('db.php');

$cmysqli_sab07 = mysqli_connect($mysqlserver_sab07,$mysqluser_sab07,$mysqlpass_sab07,$mysqldb_sab07);
if ($cmysqli_sab07->connect_error) {
	$mensaje= "Error al conectarse a la base de datos de expedientes: ".$cmysqli_sab07->connect_error;
	$response['codigo'] = -1;
	$response['mensaje'] = $mensaje;
	error_log(json_encode($response));
	die(json_encode($response));
}
?>