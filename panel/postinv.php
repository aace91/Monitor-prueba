<?php
set_time_limit(30);
include_once('./../checklogin.php');
include('./../connect_dbsql.php');
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
			if($d==NULL)
				return '';
			else
				return date( 'd/m/Y', strtotime($d));
		} ),
	array( 'db' => 'fechavirtual',   'dt' => 'fechavirtual', 'formatter' => function( $d, $row ) {
			if($d==NULL)
				return '';
			else
				return date( 'd/m/Y', strtotime($d));
		} ),
	array( 'db' => 'proveedor',     'dt' => 'proveedor' ),
	array( 'db' => 'descripcion',     'dt' => 'descripcion' ),
	array( 'db' => 'foto1',     'dt' => 'foto1' ),
	array( 'db' => 'foto2',     'dt' => 'foto2' ),
	array( 'db' => 'foto3',     'dt' => 'foto3' ),
	array( 'db' => 'foto4',     'dt' => 'foto4' ),
	array( 'db' => 'foto5',     'dt' => 'foto5' ),
	array( 'db' => 'documentacion',     'dt' => 'documentacion' ),
	array( 'db' => 'po',     'dt' => 'po' )
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


$id_cliente=$_REQUEST['cliente'];
$cinventario = '
			AND
			IF (
				bod.bodsalida IS NULL,
				sald.referencia IS NULL,
				bod.bodsalida IS NULL
			)
				';
$consulta_set_id = 
	"SELECT
		id_tpo
	FROM
		docs_tipos
	WHERE
		`set`=1";
$query = mysqli_query($cmysqli,$consulta_set_id);
if(!$query){
	exit(json_encode(array("error" => "Error al consultar tipo de documetnos")));
}
while($row = mysqli_fetch_array($query)){
	$id_set=$row['id_tpo'];
}
// Main query to actually get the data
$baseSql = "SELECT
				bod.bodReferencia AS referencia,
				bodfecha AS fechaentrada,
				bod.fechavirtual as fechavirtual,
				bod.bodhora AS horaentrada,
				bod.boddescmer as descripcion,
				bod.bodnopedido as po,
				pro.proNom as proveedor,
				bod.bodfoto1 as foto1,
				bod.bodfoto2 as foto2,
				bod.bodfoto3 as foto3,
				bod.bodfoto4 as foto4,
				bod.bodfoto5 as foto5,
				bod.weblinkp as documentacion
			FROM
				tblbod AS bod
			LEFT JOIN procli AS pro ON bod.bodprocli = pro.proveedor_id
			LEFT JOIN detalle_salidas AS sald ON bod.bodReferencia = sald.REFERENCIA
			WHERE
				bod.bodcli=".$id_cliente.$cinventario."
			AND NOT EXISTS (
				SELECT 
					docs_refe.referencia, 
					docs.id_tpo 
				FROM 
					docs_refe 
				LEFT JOIN docs ON docs_refe.id_doc = docs.id_doc 
				WHERE 
					invalido IS NULL
				" . ($_POST['tipo_doc'] != $id_set ? "AND (docs.id_tpo = ". $_POST['tipo_doc']." OR docs.id_tpo = $id_set)" : "").
				"AND referencia=bod.bodreferencia
			)";

require( 'ssp.class.php' );

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);

