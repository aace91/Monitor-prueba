<?php
include ('db.php');
$cmysqli_s8va = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb_sterisr8va);
if (!$cmysqli_s8va) {
	$mensaje= "Error al conectarse a la base de datos de [Regla 8va STERIS]: ".mysqli_connect_error();
	$response['codigo'] = -1;
	$response['mensaje'] = $mensaje;
	error_log(json_encode($response));
	die(json_encode($response));
}

?>