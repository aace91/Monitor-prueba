<?php
include_once('./../../../checklogin.php');
require('./../../../db.php');

if ($loggedIn == false){
	$msg='Code [500]';
	echo json_encode( array( 
		"error" => $msg
	) );
	exit(0);
} else {
	
	$sIdSalida = $_POST['sIdSalida'];
	
	// DB table to use
	$table = 'expos_salidas_facturas';
	$primaryKey = 'SALIDA_NUMERO';    
    $columns = array(	
		array('db' => 'SALIDA_NUMERO', 'dt' => 'SALIDA_NUMERO' ),
		array('db' => 'FACTURA_NUMERO', 'dt' => 'FACTURA_NUMERO' ),
		array('db' => 'VALOR_FACTURA', 'dt' => 'VALOR_FACTURA' ),
		array('db' => 'REFERENCIA', 'dt' => 'REFERENCIA' ),
		array('db' => 'PEDIMENTO', 'dt' => 'PEDIMENTO' )
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb,
        'host' => $mysqlserver
    );

	$baseSql = "SELECT SALIDA_NUMERO, FACTURA_NUMERO, VALOR_FACTURA, REFERENCIA, PEDIMENTO
				FROM bodega.expos_salidas_facturas
				WHERE SALIDA_NUMERO=".$sIdSalida;
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







