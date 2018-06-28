<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	$sIdEmpresa = $_POST['sIdEmpresa'];
	
	// DB table to use
	$table = 'expedientes';
	$primaryKey = 'id_registro';    
    $columns = array(	
		array('db' => 'id_registro', 'dt' => 'id_registro' ),
		array('db' => 'referencia_saaio', 'dt' => 'referencia_saaio' ),
		array('db' => 'pedimento', 'dt' => 'pedimento' ),
		array('db' => 'comentarios', 'dt' => 'comentarios' ),
		array( 'db' => 'fecha_cc_recepcion',   'dt' => 'fecha_cc_recepcion', 'formatter' => function( $d, $row ) {
                if ($d==''){
					return '';
				}else{				
					return date( 'd/m/Y H:i:s', strtotime($d)); 
				}               
        }),
		array( 'db' => 'fecha_cc_entrega',   'dt' => 'fecha_cc_entrega', 'formatter' => function( $d, $row ) {
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

	$baseSql = "SELECT id_registro, referencia_saaio, pedimento, comentarios, fecha_cc_recepcion, fecha_cc_entrega
				FROM expedientes.seguimiento_pedime
				WHERE (id_empresa=".$sIdEmpresa." OR id_empresa IS NULL) AND
				      fecha_recepcion_entrega IS NOT NULL AND
					  fecha_cc_facturacion IS NULL";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../../ssp.class.php');

	echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







