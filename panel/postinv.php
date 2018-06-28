<?php
set_time_limit(30);
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
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
$table = 'tblbod';

// Table's primary key
$primaryKey = 'referencia';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
	array(
        'db' => 'referencia',
        'dt' => 'DT_RowId',
        'formatter' => function( $d, $row ) {
            // Technically a DOM id cannot start with an integer, so we prefix
            // a string. This can also be useful if you have multiple tables
            // to ensure that the id is unique with a different prefix
            return 'row_'.$d;
        }
    ),
	array( 'db' => 'referencia',  'dt' => 'referencia' ),
	array( 'db' => 'fechaentrada',   'dt' => 'fechaentrada', 'formatter' => function( $d, $row ) {
			return date( 'd/m/Y', strtotime($d));
		} ),
	array( 'db' => 'horaentrada',     'dt' => 'horaentrada' ),
	array( 'db' => 'proveedor',     'dt' => 'proveedor' ),
	array( 'db' => 'descripcion',     'dt' => 'descripcion' ),
	array( 'db' => 'foto1',     'dt' => 'foto1' ),
	array( 'db' => 'foto2',     'dt' => 'foto2' ),
	array( 'db' => 'foto3',     'dt' => 'foto3' ),
	array( 'db' => 'foto4',     'dt' => 'foto4' ),
	array( 'db' => 'foto5',     'dt' => 'foto5' ),
	array( 'db' => 'documentacion',     'dt' => 'documentacion' ),
	array( 'db' => 'cliente',     'dt' => 'cliente' )
);

// SQL server connection information
$sql_details = array(
	'user' => $mysqluser,
	'pass' => $mysqlpass,
	'db'   => $mysqldb,
	'host' => $mysqlserver
);


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'ssp.class.inv.php' );

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);

