<?php
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
$table = '';

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
            return $d;
        }
    ),
	array( 'db' => 'referencia',     'dt' => 'referencia' ),
	array( 'db' => 'fecha_entrada',   'dt' => 'fecha_entrada', 'formatter' => function( $d, $row ) {
		return date( 'd/m/Y', strtotime($d));
	} ),
	array( 'db' => 'proveedor',     'dt' => 'proveedor' ),
	array( 'db' => 'descripcion',     'dt' => 'descripcion' ),
	array( 'db' => 'precaptura',     'dt' => 'precaptura' ),
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
$cliente=$_POST['cliente'];
$sRefs = trim($_POST['referencias']);
// Main query to actually get the data
$baseSql = "
	SELECT
		tbod.bodreferencia as referencia,
		tbod.bodfecha as fecha_entrada,
		pro.proNom  as proveedor,
		tbod.boddescmer as descripcion,
		pg.referencia as precaptura
	FROM
		bodega.tblbod AS tbod
	LEFT JOIN bodega.procli AS pro ON tbod.bodprocli = pro.proveedor_id
	LEFT JOIN bodega.detalle_salidas AS sald ON tbod.bodReferencia = sald.REFERENCIA
	LEFT JOIN detalle_revision as revd ON tbod.bodReferencia=revd.referencia
	LEFT JOIN bodega.precaptura_gral as pg on tbod.bodreferencia=pg.referencia
	WHERE
		IF (tbod.bodsalida IS NULL,sald.referencia IS NULL,tbod.bodsalida IS NULL)
	AND tbod.bodcli=$cliente AND revd.referencia IS NOT NULL
	".($sRefs!="''"?" AND tbod.bodReferencia in ($sRefs)":"")."
	GROUP BY
		revd.referencia";

require( 'ssp.class.php' );

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);

