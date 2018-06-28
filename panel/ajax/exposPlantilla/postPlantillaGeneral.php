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
		
	$sReferencia = $_POST['sReferencia'];
	
	//***********************************************************//
		
	// DB table to use
	$table = 'expos_plantilla_gral';
	$primaryKey = 'id_plantilla';    
    $columns = array(	
		array('db' => 'id_plantilla', 'dt' => 'id_plantilla' ),
		array('db' => 'id_embarque', 'dt' => 'id_embarque' ),
		array('db' => 'referencia', 'dt' => 'referencia' ),
		array('db' => 'registros', 'dt' => 'registros' ),
		array('db' => 'fecha_alta', 'dt' => 'fecha', 'formatter' => function( $d, $row ) {
			if ($d==''){
				return '';
			}else{				
				return date( 'd/m/Y H:i:s', strtotime($d)); 
			}               
		})
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb,
        'host' => $mysqlserver
    );

	$baseSql = "SELECT a.id_plantilla, a.id_embarque, 'NA' AS referencia, a.fecha_alta, 
				      (SELECT COUNT(*)
				       FROM bodega.expos_plantilla_detalle AS b
							 WHERE b.id_plantilla = a.id_plantilla
				      ) AS registros
				FROM bodega.expos_plantilla_gral AS a
				WHERE a.referencia='".$sReferencia."' AND
				      a.fecha_del IS NULL";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







