<?php
	include('db.php');
	$cmysql_exp = mysql_connect($mysqlserver_exp,$mysqluser_exp,$mysqlpass_exp);
	mysql_select_db($mysqldb_exp) or die("Error al conectarse a la base de datos de expedientes");
	$cmysqli_exp = mysqli_connect($mysqlserver_exp,$mysqluser_exp,$mysqlpass_exp,$mysqldb_exp);
	if ($cmysqli_exp->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de expedientes: ".$cmysqli_exp->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		die(json_encode($response));
	}
	
	$cmysql_sab07 = mysql_connect($mysqlserver_sab07,$mysqluser_sab07,$mysqlpass_sab07);
	mysql_select_db($mysqldb_sab07) or die("Error al conectarse a la base de datos de expedientes");
	$cmysqli_sab07 = mysqli_connect($mysqlserver_sab07,$mysqluser_sab07,$mysqlpass_sab07,$mysqldb_sab07);
	if ($cmysqli_sab07->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de expedientes: ".$cmysqli_sab07->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		die(json_encode($response));
	}
	
	$cmysql = mysql_connect($mysqlserver,$mysqluser,$mysqlpass);
	mysql_select_db($mysqldb) or die("Error al conectarse a la base de datos de bodega");
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		die(json_encode($response));
	}
	$dsn = "cnxpedimentos";
	//debe ser de sistema no de usuario
	$usuarioc = "";
	$clavec="";
	//realizamos la conexion mediante odbc
	$odbccasa=odbc_connect($dsn, $usuarioc, $clavec);
	if (!$odbccasa){
		exit("<strong>Error al conectarse a la base de datos de pedimentos [CASA].</strong>");
	}

	//Steris R8va
	$mysqldb_sterisr8va = 'steris_regla8va';
	$cmysqli_s8va = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb_sterisr8va);
	if ($cmysqli_s8va->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de steris r8va ".$cmysqli_s8va->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		die(json_encode($response));
	}



?>