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

// DB table to use
$table = '';

// Table's primary key
$primaryKey = 'id_solicitud';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
	//array( 'db' => 'id', 'dt' => 'id'),
	array( 'db' => 'id_solicitud',     'dt' => 'id_solicitud' ),
	array( 'db' => 'referencia',     'dt' => 'referencia' ),
	array( 'db' => 'motivo',     'dt' => 'motivo' ),
	array( 'db' => 'fecha_registro',   'dt' => 'fecha_registro', 'formatter' => function( $d, $row ) {
		if ($d === NULL) {
			return '';
		} else {
			return date( 'm/d/Y H:i', strtotime($d));
		}
	}),
	array( 'db' => 'cliente',     'dt' => 'cliente' ),	
	array( 'db' => 'linea_entrego',     'dt' => 'linea_entrego' ),
	array( 'db' => 'estatus',     'dt' => 'estatus' ),
	array( 'db' => 'fecha_autorizo_bodega',  'dt' => 'fecha_autorizo_bodega' , 'formatter' => function( $d, $row ) {
		if ($d === NULL) {
			return '';
		} else {
			return date( 'm/d/Y H:i', strtotime($d));
		}
	}),	
	array( 'db' => 'fecha_autorizo_cliente',  'dt' => 'fecha_autorizo_cliente' , 'formatter' => function( $d, $row ) {
		if ($d === NULL) {
			return '';
		} else {
			return date( 'm/d/Y H:i', strtotime($d));
		}
	}),
	array( 'db' => 'fecha_autorizo_ejecutivo',  'dt' => 'fecha_autorizo_ejecutivo' , 'formatter' => function( $d, $row ) {
		if ($d === NULL) {
			return '';
		} else {
			return date( 'm/d/Y H:i', strtotime($d));
		}
	}),
	array( 'db' => 'fecha_rechazo',  'dt' => 'fecha_rechazo' , 'formatter' => function( $d, $row ) {
		if ($d === NULL) {
			return '';
		} else {
			return date( 'm/d/Y H:i', strtotime($d));
		}
	}),
	array( 'db' => 'observaciones',     'dt' => 'observaciones' ),
	array( 'db' => 'usuario_registro',     'dt' => 'usuario_registro' ),
	array('db' => 'comentarios', 'dt' => 'bcomentario', 'formatter' => function( $d, $row ) {
		if ($d==''){
			return false;
		}else{	
			if ($d > 0) {
				return true;
			} else {
				return false;
			}		
		}               
	}),
	array('db' => 'ultima_vista', 'dt' => 'ultima_vista' ),
	array('db' => 'fecha_ultimo_comentario', 'dt' => 'bnuevo_comentario', 'formatter' => function( $d, $row ) {
		if ($d=='' || is_null($d)){
			return false;
		}else{	
			if (strtotime($d) > strtotime($row['ultima_vista'])) {
				return true;
			} else {
				return false;
			}
		}               
	})
);

// SQL server connection information
$sql_details = array(
	'user' => $mysqluser,
	'pass' => $mysqlpass,
	'db'   => $mysqldb,
	'host' => $mysqlserver
);
			
$baseSql = "SELECT te.id_solicitud,
				   te.referencia,
				   te.motivo,
				   te.fecha_registro,
				   cli.Nom as cliente,
				   fle.fleNombre as linea_entrego,
				   CASE 
				      WHEN te.fecha_autorizo_cliente IS NULL AND te.fecha_rechazo IS NULL THEN 'PENDIENTE'
					  WHEN te.fecha_rechazo IS NOT NULL THEN 'RECHAZADO'
					  WHEN te.fecha_autorizo_cliente IS NOT NULL AND te.fecha_autorizo_bodega IS NOT NULL AND te.fecha_autorizo_ejecutivo IS NOT NULL THEN 'AUTORIZADO'
					  ELSE 'PENDIENTE'
				   END AS estatus,
				   te.observaciones,
				   IF(te.usuario_tipo = '1', u.usunombre, cli.Nom) as usuario_registro,
				   te.fecha_autorizo_bodega, 
				   te.fecha_autorizo_cliente, 
				   te.fecha_autorizo_ejecutivo,
				   te.fecha_rechazo,
				   (SELECT COUNT(*)
					FROM tiempo_extra_comentarios AS tec
					WHERE tec.id_solicitud = te.id_solicitud) AS comentarios,
				   te.ejecutivo_ultima_vista AS ultima_vista,
				   (SELECT tec.fecha
					FROM tiempo_extra_comentarios AS tec
					WHERE tec.id_solicitud = te.id_solicitud
					ORDER BY fecha DESC
					LIMIT 1) AS fecha_ultimo_comentario
			FROM tiempo_extra te INNER JOIN 
				 tblbod bod ON te.referencia = bod.bodReferencia INNER JOIN
				 tblflet fle ON bod.bodfle = fle.fleClave INNER JOIN
				 clientes cli ON bod.bodcli = cli.Cliente_id INNER JOIN
				 tblusua u ON te.usuario_id = u.Usuario_id";

require('../../ssp.class.php');

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);

