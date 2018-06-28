<?php
include_once('./../../../checklogin.php');

$link_base = "http://$ip_out2:$port_out2/webtools/upload/solicitudSellos/";

if ($loggedIn == false){
	echo '500';
} else{
	
    $primaryKey = 'id_registro';
    $table='';
    
    $columns = array(	
		array('db' => 'referencia', 'dt' => 'referencia' ),
        array('db' => 'caja', 'dt' => 'caja' ),
        array( 'db' => 'fecha_solicitud',   'dt' => 'fecha_solicitud', 'formatter' => function( $d, $row ) {
                $row = '';
                return date( 'd/m/Y H:i:s', strtotime($d));                
        }),
        array( 'db' => 'fecha_atendido',   'dt' => 'fecha_atendido', 'formatter' => function( $d, $row ) {
                $row = '';
				if ($d === NULL) {
					return '';
				} else {
					return date( 'd/m/Y H:i:s', strtotime($d));
				}
        }),		
		array('db' => 'picture', 'dt' => 'picture' )
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb,
        'host' => $mysqlserver
    );
    
    $baseSql = "SELECT *, IF (fecha_atendido IS NULL, NULL, (CONCAT('".$link_base."Pic1_', id_registro, '.jpg'))) AS picture
				FROM solicitud_sellos";	

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







