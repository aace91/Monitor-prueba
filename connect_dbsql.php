<?php
include ('db.php');
$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
if (!$cmysqli) {
	$mensaje= "Error al conectarse a la base de datos de bodega: ".mysqli_connect_error($cmysqli);
	$response['codigo'] = -1;
	$response['mensaje'] = $mensaje;
	if (isset($consulta)){
		$response['consulta'] = $consulta;
	}
	error_log(json_encode($response));
	die(json_encode($response));
}

?>