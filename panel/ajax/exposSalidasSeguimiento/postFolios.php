<?php
include_once('./../../../checklogin.php');


if ($loggedIn == false){
	$msg='Code [500]';
	echo json_encode( array( 
		"error" => $msg
	) );
	exit(0);
} else { 
	//***********************************************************//
		
	$sIdCliente = $_POST['sIdCliente'];
	
	//***********************************************************//
	
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
	$table = 'expos_seguimiento';

	// Table's primary key
	$primaryKey = 'id_folio';

	// Array of database columns which should be read and sent back to DataTables.
	// The `db` parameter represents the column name in the database, while the `dt`
	// parameter represents the DataTables column identifier. In this case object
	// parameter names

	$columns = array(	
		array('db' => 'id_folio',  'dt' => 'id_folio' ),
		array('db' => 'estatus',  'dt' => 'estatus' ),
		array('db' => 'id_cliente',  'dt' => 'id_cliente' ),
		array('db' => 'caja', 'dt' => 'caja' ),
		array('db' => 'consignatario', 'dt' => 'consignatario' ),
		array('db' => 'logistica', 'dt' => 'logistica' ),
		array('db' => 'nombre_logistica', 'dt' => 'nombre_logistica' ),
		array('db' => 'linea_transportista', 'dt' => 'linea_transportista' ),
		array('db' => 'linea_transportista_nombre', 'dt' => 'linea_transportista_nombre' ),
		array('db' => 'guia', 'dt' => 'guia' ),
		array('db' => 'fecha_salida_creada', 'dt' => 'fecha_salida_creada', 'formatter' => function( $d, $row ) {
				if ($d==''){
					return '';
				}else{				
					return date( 'd/m/Y H:i:s', strtotime($d)); 
				}               
		}),
		array('db' => 'fecha_aprobado', 'dt' => 'fecha_aprobado', 'formatter' => function( $d, $row ) {
				if ($d==''){
					return '';
				}else{				
					return date( 'd/m/Y H:i:s', strtotime($d)); 
				}               
		}),
		array('db' => 'fecha_alta', 'dt' => 'fecha_alta', 'formatter' => function( $d, $row ) {
				if ($d==''){
					return '';
				}else{				
					return date( 'd/m/Y H:i:s', strtotime($d)); 
				}               
		}),
		array('db' => 'facturas', 'dt' => 'facturas' ),
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
		array('db' => 'ejecutivo_ultima_vista', 'dt' => 'ejecutivo_ultima_vista' ),
		array('db' => 'fecha_ultimo_comentario', 'dt' => 'bnuevo_comentario', 'formatter' => function( $d, $row ) {
				if ($d=='' || is_null($d)){
					return false;
				}else{	
					if (strtotime($d) > strtotime($row['ejecutivo_ultima_vista'])) {
						return true;
					} else {
						return false;
					}
				}               
		})
	);

	$sql_details = array(
		'user' => $mysqluser,
		'pass' => $mysqlpass,
		'db'   => $mysqldb,
		'host' => $mysqlserver
	);

	$baseSql = "SELECT a.id_folio
				      ,a.id_cliente
				      ,a.caja
				      ,a.consignatario
				      ,a.logistica
				      ,b.nombre AS nombre_logistica
				      ,a.linea_transportista
				      ,c.nombre AS linea_transportista_nombre
				      ,a.guia
				      ,a.fecha_salida_creada
				      ,a.fecha_aprobado AS fecha_aprobado
				      ,a.fecha_alta
					  ,(SELECT GROUP_CONCAT(d.factura SEPARATOR \", \")
				        FROM bodega.expos_seguimiento_facturas AS d
				        WHERE d.id_folio = a.id_folio
				        GROUP BY d.id_folio) AS facturas
				      ,(SELECT c.descripcion
						FROM bodega.expos_seguimiento_historico_sts AS c
						WHERE c.id_folio = a.id_folio
						ORDER BY c.id_historico DESC
						LIMIT 1) AS estatus
					  ,(SELECT COUNT(*)
				        FROM bodega.expos_seguimiento_comments AS e
				        WHERE e.id_folio = a.id_folio) AS comentarios
					  ,a.ejecutivo_ultima_vista
					  ,(SELECT f.fecha
				        FROM bodega.expos_seguimiento_comments AS f
				        WHERE f.id_folio = a.id_folio
                        ORDER BY fecha DESC
                        LIMIT 1) AS fecha_ultimo_comentario
				FROM bodega.expos_seguimiento AS a INNER JOIN
                     bodega.expos_seguimiento_logisticas AS b ON b.logistica = a.logistica LEFT JOIN
                     bodega.expos_seguimiento_lineas_trans AS c ON c.id_linea = a.linea_transportista
				WHERE a.id_cliente='".$sIdCliente."'";
						
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP
	 * server-side, there is no need to edit below this line.
	 */

	require('../../ssp.class.php');

	echo json_encode(
		SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
	);
}