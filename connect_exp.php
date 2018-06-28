<?php
include('db.php');

$cmysqli_exp = mysqli_connect($mysqlserver_exp,$mysqluser_exp,$mysqlpass_exp,$mysqldb_exp);
if ($cmysqli_exp->connect_error) {
	$mensaje= "Error al conectarse a la base de datos de expedientes: ".$cmysqli_exp->connect_error;
	$response['codigo'] = -1;
	$response['mensaje'] = $mensaje;
	error_log(json_encode($response));
	die(json_encode($response));
}
?>