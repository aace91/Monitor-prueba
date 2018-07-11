<?php
include_once("../../checklogin.php");
if($loggedIn == false){
	$error_msg="Se ha perdido la sesion favor de iniciarla de nuevo";
	exit(json_encode(array("error" => $error_msg)));
}

/*
 * Example PHP implementation used for the index.html example
 */

/*// DataTables PHP library
include( "../../editor/php/DataTables.php" );

// Alias Editor classes so they are easy to use
use
	DataTables\Editor,
	DataTables\Editor\Field,
	DataTables\Editor\Format,
	DataTables\Editor\Join,
	DataTables\Editor\Mjoin,
	DataTables\Editor\Options,
	DataTables\Editor\Upload,
	DataTables\Editor\Validate;

// Build our Editor instance and process the data coming from _POST

Editor::inst( $db, 'salidas_expo' )
	->fields(
		Field::inst( 'salidas_expo.salidanumero as no_salida' ),
		Field::inst( 'salidas_expo.fecha as fecha_alta' ),
		Field::inst( 'salidas_expo.lineatransp as linea' ),
		Field::inst( 'salidas_expo.tiposalida as tipo_salida' ),
		Field::inst( 'salidas_expo.caja as notipo_salida' )//,
		//Field::inst( 'facturas_expo.FACTURA_NUMERO as facturas' )
	)
	//->leftJoin( 'facturas_expo', 'facturas_expo.SALIDA_NUMERO', '=', 'salidas_expo.salidanumero' )
	->pkey('salidas_expo.salidanumero')
	->where( function ( $q) {
		
	} )
	->process( $_POST )
	->json();*/
	
	$sel_status = $_POST['sel_status'];
	
	// DB table to use
	$table = 'salidas_expo';
	$primaryKey = 'no_salida';    
    $columns = array(	
		array('db' => 'no_salida', 'dt' => 'no_salida' ),
		array('db' => 'fecha_alta', 'dt' => 'fecha_alta', 'formatter' => function( $d, $row ) {
			if ($d==''){
				return '';
			}else{				
				return date( 'd/m/Y H:i', strtotime($d)); 
			}               
		}),
		array('db' => 'linea', 'dt' => 'linea' ),
		array('db' => 'tipo_salida', 'dt' => 'tipo_salida' ),
		array('db' => 'cajas', 'dt' => 'cajas', 'formatter' => function( $d, $row ) {
			if ($row['tipo_salida']==''){
				return $row['cajas'];
			}else{				
				return $d . ': ' . $row['notipo_salida'];
			}               
		}),
		array('db' => 'notipo_salida', 'dt' => 'notipo_salida' ),
		array('db' => 'referencias', 'dt' => 'referencias' ),
		array('db' => 'facturas', 'dt' => 'facturas' ),
		array('db' => 'ejecutivo', 'dt' => 'ejecutivo' ),
		array('db' => 'estatus', 'dt' => 'estatus' )
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb,
        'host' => $mysqlserver
    );
	
	$sWhere = '';
	/*if ($sel_status == '0') {
		$sWhere = "WHERE reportada IS NULL AND prefile_name IS NULL AND 
		                 (prefile_obligatorio = 1 AND
						 (SELECT COUNT(*)
                          FROM bodega.facturas_expo AS a INNER JOIN
                               bodega.cruces_expo_detalle AS b ON b.uuid=a.UUID
					      WHERE a.SALIDA_NUMERO=salidas_expo.salidanumero) > 0)";
	} else if ($sel_status == '1') {
		$sWhere = "WHERE reportada = 'S' OR prefile_obligatorio = 0";
	}*/
	if ($sel_status == '0') {
		$sWhere = "WHERE salidas_expo.reportada IS NULL AND 
						 facturas_expo.NOAAA = 58 AND
						 facturas_expo.PREFILE_ID IS NULL AND 
						 facturas_expo.SALIDA_NUMERO >= 140771 /*AND
						 (SELECT COUNT(*)
						  FROM bodega.facturas_expo AS a INNER JOIN
						 	   bodega.cruces_expo_detalle AS b ON b.uuid=a.UUID
						  WHERE a.SALIDA_NUMERO=salidas_expo.salidanumero) > 0*/";
	} else if ($sel_status == '1') {
		$sWhere = "WHERE reportada = 'S'";
	}

	$baseSql = "SELECT salidas_expo.salidanumero as no_salida,
					   salidas_expo.fecha as fecha_alta,
					   salidas_expo.lineatransp as linea,
					   salidas_expo.tiposalida as tipo_salida,
					   salidas_expo.caja as notipo_salida,
					   salidas_expo.usuario AS ejecutivo,
					   GROUP_CONCAT(DISTINCT CONCAT(facturas_expo.TIPOSALIDA, ': ' , facturas_expo.CAJA) SEPARATOR ', ') AS cajas,
					   GROUP_CONCAT(DISTINCT facturas_expo.REFERENCIA ORDER BY facturas_expo.REFERENCIA ASC SEPARATOR ', ') as referencias,
					   GROUP_CONCAT(DISTINCT facturas_expo.FACTURA_NUMERO ORDER BY facturas_expo.FACTURA_NUMERO ASC SEPARATOR ', ') as facturas,
				       IF(salidas_expo.reportada IS NULL AND 
                          facturas_expo.NOAAA = 58 AND
                          facturas_expo.PREFILE_ID IS NULL /*AND 
                          (SELECT COUNT(*)
                           FROM bodega.facturas_expo AS a INNER JOIN
                                bodega.cruces_expo_detalle AS b ON b.uuid=a.UUID 
                           WHERE a.SALIDA_NUMERO=salidas_expo.salidanumero) > 0*/, 'pendiente', 'cumplida') AS estatus
				FROM bodega.salidas_expo AS salidas_expo LEFT JOIN
					 bodega.facturas_expo AS facturas_expo ON facturas_expo.SALIDA_NUMERO=salidas_expo.salidanumero
				".$sWhere."
				GROUP BY salidas_expo.salidanumero
				ORDER BY salidas_expo.salidanumero DESC";
				
	//error_log($baseSql);
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

	require('../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
