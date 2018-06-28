<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	// DB table to use
	$table = 'expedientes';
	$primaryKey = 'id_registro';    
    $columns = array(	
		array('db' => 'id_registro', 'dt' => 'id_registro' ),
		array('db' => 'referencia_saaio', 'dt' => 'referencia_saaio' ),
		array('db' => 'pedimento', 'dt' => 'pedimento' ),
		array('db' => 'comentarios', 'dt' => 'comentarios' )
    );

    $sql_details = array(
        'user' => $mysqluser_exp,
        'pass' => $mysqlpass_exp,
        'db'   => $mysqldb_exp,
        'host' => $mysqlserver_exp
    );

	$baseSql = "SELECT id_registro, referencia_saaio, pedimento, comentarios, fecha_cc_recepcion, fecha_cc_entrega
				FROM expedientes.seguimiento_pedime
				WHERE fecha_recepcion_entrega IS NOT NULL AND fecha_cc_recepcion IS NULL";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







