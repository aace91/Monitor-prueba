<?php
include_once("../../checklogin.php");
if($loggedIn == false){
	$error_msg="Se ha perdido la sesion favor de iniciarla de nuevo";
	exit(json_encode(array("error" => $error_msg)));
}

/******************************************************************/

$tipo_catalogo = $_POST['tipo_catalogo'];
$tipo_contacto = $_POST['tipo_contacto'];
$id_catalogo = $_POST['id_catalogo'];

/******************************************************************/

// DB table to use
$table = 'contactos_expo';

// Table's primary key
$primaryKey = 'id_contacto';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names

$columns = array(	
	array('db' => 'id_contacto',  'dt' => 'id_contacto' ),
	array('db' => 'email',  'dt' => 'email' ),
	array('db' => 'nombre',  'dt' => 'nombre' )
);

$sql_details = array(
	'user' => $mysqluser,
	'pass' => $mysqlpass,
	'db'   => $mysqldb,
	'host' => $mysqlserver
);

$baseSql = "SELECT id_contacto, email, nombre
			FROM bodega.contactos_expo
			WHERE id_catalogo='".$id_catalogo."' AND
			      tipo_catalogo='".$tipo_catalogo."'";

switch ($tipo_catalogo) {
    case 'CLI':
        $baseSql .= " AND tipo_contacto='".$tipo_contacto."'";
        break;
}
					
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require('../ssp.class.php');

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);