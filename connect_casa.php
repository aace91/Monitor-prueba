<?php
include ('db.php');

$dsn = "cnxpedimentos";
//debe ser de sistema no de usuario
$usuarioc = "";
$clavec="";
//realizamos la conexion mediante odbc
$odbccasa=odbc_connect($dsn, $usuarioc, $clavec);
if (!$odbccasa){
	exit(json_encode (array("Codigo"=>-1, "Mensaje"=>"Error al conectarse a la base de datos de pedimentos [CASA]")));
}

?>