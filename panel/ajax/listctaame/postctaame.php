<?php
include_once('./../../../checklogin.php');
if($loggedIn == false){ header("Location: ./../../../login.php"); }
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

/****************************************************************************/ 

$estatus_pago = $_POST['estatus_pago'];
$fechaini = $_POST['fechaini'];
$fechafin = $_POST['fechafin'];

/***************************/

$fechas = '';
$filtro = (($estatus_pago == 'none')? '': "AND a.isPaid='".$estatus_pago."'");

/****************************************************************************/ 
 
// DB table to use
$table = 'invoice';

// Table's primary key
$primaryKey = 'TxnID';
$link_edocuments='';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
			
$columns = array(
    array( 'db' => 'TxnID',  'dt' => 'TxnID' ),
	array( 'db' => 'cuenta',  'dt' => 'cuenta' ),
	array( 'db' => 'estatus',  'dt' => 'estatus' ),
	array( 'db' => 'referencia',  'dt' => 'referencia' ),
	array( 'db' => 'fecha',  'dt' => 'fecha', 'formatter' => function( $d, $row ) {
		return date( 'd/m/Y', strtotime($d));
	}),
	array( 'db' => 'Trailer',  'dt' => 'Trailer' ),
	array( 'db' => 'FOB',  'dt' => 'FOB' )
);

// SQL server connection information
$sql_details = array(
	'user' => $mysqluser,
	'pass' => $mysqlpass,
	'db'   => $mysqldb,
	'host' => $mysqlserver
);

$fechaini = (($fechaini != '')? substr($_POST['fechaini'],6,4).substr($_POST['fechaini'],3,2).substr($_POST['fechaini'],0,2) : '');
$fechafin = (($fechafin != '')? substr($_POST['fechafin'],6,4).substr($_POST['fechafin'],3,2).substr($_POST['fechafin'],0,2) : '');

if($fechaini != '' and $fechafin != ''){
	$fechas.=" AND a.TimeCreated >= '".$fechaini."' AND a.TimeCreated <= '".$fechafin."'";
}else{
	if($fechaini!=''){
		$fechas.=" AND a.TimeCreated >= '".$fechaini."'";
	} elseif ($fechafin!=''){
		$fechas.=" AND a.TimeCreated <= '".$fechafin."'";
	}
}

$idquickbooks = $_POST['idquickbooks'];
$baseSql = "SELECT a.TxnID,
			       a.RefNumber AS cuenta,
			       a.isPaid AS estatus,
			       a.PONumber AS referencia,
			       a.TimeCreated AS fecha, 
 				   a.FOB,
				   a.CustomField2 AS Trailer
			FROM qbdelbravo_sync.invoice AS a
			WHERE a.CustomerRef_ListID='".$idquickbooks."'
				  ".$filtro." ".$fechas;

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( './../../ssp.class.php' );

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);

