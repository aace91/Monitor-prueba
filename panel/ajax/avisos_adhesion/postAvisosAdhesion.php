<?php
include_once('./../../../checklogin.php');
include('./../../../url_archivos.php');

if($loggedIn == false){ 
	echo json_encode( array("error" => 'La sesion del usuario ha finalizado. Por favor, inicie nuevamente.'));
	exit();
}
/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

// DB table to use
$table = 'permisos_adhesion';

// Table's primary key
$primaryKey = 'id_permiso_adhesion';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
	array( 'db' => 'id_permiso_adhesion',     'dt' => 'id_permiso_adhesion' ),
	array( 'db' => 'numero_permiso',     'dt' => 'numero_permiso' ),
	array( 'db' => 'vigencia',     'dt' => 'vigencia' ),
	array( 'db' => 'cliente',     'dt' => 'cliente' ),
	array( 'db' => 'documento',     'dt' => 'documento' )
);

// SQL server connection information
$sql_details = array(
	'user' => $mysqluser,
	'pass' => $mysqlpass,
	'db'   => $mysqldb,
	'host' => $mysqlserver
);

// Main query to actually get the data
$baseSql = "
			SELECT p.id_permiso_adhesion, p.numero_permiso,c.nombre as cliente, 
					CONCAT(DATE_FORMAT(p.fecha_vigencia_ini,\"%d/%m/%Y\"),\" a \",DATE_FORMAT(p.fecha_vigencia_fin,\"%d/%m/%Y\")) AS vigencia,
					CONCAT('".$URL_archivos_permisos."',p.archivo_permiso) as documento
			FROM permisos_adhesion p
				LEFT JOIN bodega.geocel_clientes_expo c ON
					p.id_cliente = c.f_numcli";

require( '../../ssp.class.php' );
echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);







