<?php
include_once('./../checklogin.php');
if($loggedIn == false){ 
	$msg='Su sesión ha expirado favor de ingresar de nuevo';
	echo json_encode( array( 
			"error" => $msg
		) );
	exit(0);
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
$table = 'tblbod';

// Table's primary key
$primaryKey = 'referencia';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
	array( 'db' => 'referencia',  'dt' => 'referencia' ),
	array( 'db' => 'fechaentrada',     'dt' => 'fechaentrada' ),
	array( 'db' => 'cliente',     'dt' => 'cliente' ),
	array( 'db' => 'revision',     'dt' => 'revision' , 'formatter' => function( $d, $row ) {
		if ($d == Null){
			return '';
		}
		return $d;
	}),
	array( 'db' => 'fecharevision',     'dt' => 'fecharevision' ),
	array( 'db' => 'facrev',     'dt' => 'facrev' , 'formatter' => function( $d, $row ) {
		if ($d == Null){
			return '';
		}
		return $d;
	}),
	array( 'db' => 'doc_entrada',     'dt' => 'doc_entrada')
);

// SQL server connection information
$sql_details = array(
	'user' => $mysqluser,
	'pass' => $mysqlpass,
	'db'   => $mysqldb,
	'host' => $mysqlserver
);

/*if ($_POST['cliente']!=''){
	$fcliente=' and bod.bodcli='.$_POST['cliente'];
}else{
	$fcliente='';
}*/

$sRefs = $_POST['referencias'];

$baseSql = "SELECT
				bod.bodReferencia AS referencia,
				bodfecha AS fechaentrada,
				cli.nom as cliente,
				GROUP_CONCAT(rev.id_revision) AS revision,
				GROUP_CONCAT(CONCAT(rev.fecha,' ',rev.hora)) AS fecharevision,
				GROUP_CONCAT(rev.factura) as facrev,
				bod.weblinkp as doc_entrada
			FROM
					tblbod AS bod
				LEFT JOIN clientes AS cli ON bod.bodcli = cli.Cliente_id
				LEFT JOIN revision_general AS rev ON bod.bodReferencia = rev.referencia
				LEFT JOIN remisiondet AS remd ON bod.bodReferencia = remd.referencia
			
			WHERE  bod.bodReferencia in ($sRefs)
			GROUP BY bod.bodReferencia";
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'ssp.class.php' );

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);

