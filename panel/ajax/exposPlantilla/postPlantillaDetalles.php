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
		
	$sIdPlantilla = $_POST['sIdPlantilla'];
	
	//***********************************************************//
		
	// DB table to use
	$table = 'expos_plantilla_gral';
	$primaryKey = 'id_detalle';    
    $columns = array(	
    	array('db' => 'id_detalle', 'dt' => 'id_detalle' ),
		array('db' => 'id_proveedor', 'dt' => 'id_proveedor' ),
		array('db' => 'no_factura', 'dt' => 'no_factura' ),
		array('db' => 'fecha_factura', 'dt' => 'fecha_factura', 'formatter' => function( $d, $row ) {
			if ($d==''){
				return '';
			}else{				
				return date( 'd/m/Y', strtotime($d)); 
			}               
		}),
		array('db' => 'monto_factura', 'dt' => 'monto_factura', 'formatter' => function( $d, $row ) {
			return (($d=='')? '0.00' : number_format((float)$d, 2, '.', ''));
		}),
		array('db' => 'moneda', 'dt' => 'moneda' ),
		array('db' => 'incoterm', 'dt' => 'incoterm' ),
		array('db' => 'subdivision', 'dt' => 'subdivision' ),
		array('db' => 'certificado', 'dt' => 'certificado' ),
		array('db' => 'no_parte', 'dt' => 'no_parte' ),
		array('db' => 'origen', 'dt' => 'origen' ),
		array('db' => 'vendedor', 'dt' => 'vendedor' ),
		array('db' => 'fraccion', 'dt' => 'fraccion' ),
		array('db' => 'descripcion', 'dt' => 'descripcion' ),
		array('db' => 'precio_partida', 'dt' => 'precio_partida', 'formatter' => function( $d, $row ) {
			return (($d=='')? '0.00' : number_format((float)$d, 2, '.', ''));
		}),
		array('db' => 'umc', 'dt' => 'umc' ),
		array('db' => 'cantidad_umc', 'dt' => 'cantidad_umc', 'formatter' => function( $d, $row ) {
			return (($d=='')? '0.00' : number_format((float)$d, 2, '.', ''));
		}),
		array('db' => 'cantidad_umt', 'dt' => 'cantidad_umt', 'formatter' => function( $d, $row ) {
			return (($d=='')? '0.00' : number_format((float)$d, 2, '.', ''));
		}),
		array('db' => 'preferencia', 'dt' => 'preferencia' ),
		array('db' => 'marca', 'dt' => 'marca' ),
		array('db' => 'modelo', 'dt' => 'modelo' ),
		array('db' => 'submodelo', 'dt' => 'submodelo' ),
		array('db' => 'serie', 'dt' => 'serie' ),
		array('db' => 'descripcion_cove', 'dt' => 'descripcion_cove' )
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb,
        'host' => $mysqlserver
    );

	$baseSql = "SELECT a.id_detalle, a.id_proveedor, a.no_factura, a.fecha_factura,
	                   a.monto_factura, a.moneda, a.incoterm, a.subdivision, a.certificado, a.no_parte, a.origen,
	                   a.vendedor, a.fraccion, a.descripcion, a.precio_partida, a.umc, a.cantidad_umc, a.cantidad_umt, 
	                   a.preferencia, a.marca, a.modelo, a.submodelo, a.serie, a.descripcion_cove
				FROM bodega.expos_plantilla_detalle AS a
				WHERE a.id_plantilla=".$sIdPlantilla;
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







