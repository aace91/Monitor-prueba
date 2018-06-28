<?php
include ('db.php');
$cmysqli_gab1 = mysqli_connect($mysqlserver_gab1,$mysqluser_gab1,$mysqlpass_gab1,$mysqldb_gab1);
if (!$cmysqli_gab1) {
	$mensaje= "Error al conectarse a la base de datos de bodega en GAB1: ".mysqli_connect_error($cmysqli_gab1);
	$response['codigo'] = -1;
	$response['mensaje'] = $mensaje;
	error_log(json_encode($response));
	die(json_encode($response));
}

?>