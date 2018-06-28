<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{	
	$sFecha = $_POST['sFecha'];
	
	// DB table to use
	$table = 'seguimiento_pedime';
	$primaryKey = 'id_registro';    
    $columns = array(	
		array('db' => 'id_registro', 'dt' => 'id_registro' ),
		array('db' => 'referencia_saaio', 'dt' => 'referencia_saaio' ),
		array('db' => 'pedimento', 'dt' => 'pedimento' ),
		array('db' => 'comentarios', 'dt' => 'comentarios' ),
        array( 'db' => 'fecha_recepcion_captura',   'dt' => 'fecha_recepcion_captura', 'formatter' => function( $d, $row ) {
                if ($d==''){
					return '';
				}else{				
					return date( 'd/m/Y H:i:s', strtotime($d)); 
				}               
        }),
        array( 'db' => 'fecha_recepcion_entrega',   'dt' => 'fecha_recepcion_entrega', 'formatter' => function( $d, $row ) {
                if ($d==''){
					return '';
				}else{				
					return date( 'd/m/Y H:i:s', strtotime($d)); 
				}               
        })
    );

    $sql_details = array(
        'user' => $mysqluser_exp,
        'pass' => $mysqlpass_exp,
        'db'   => $mysqldb_exp,
        'host' => $mysqlserver_exp
    );

	$baseSql = "SELECT id_registro, referencia_saaio, pedimento, comentarios, fecha_recepcion_captura, fecha_recepcion_entrega
			    FROM expedientes.seguimiento_pedime
				WHERE fecha_recepcion_entrega='".$sFecha."'";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







