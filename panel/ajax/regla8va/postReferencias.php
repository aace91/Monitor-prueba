<?php
include_once('./../../../checklogin.php');
if ($loggedIn == false){
	echo '500';
} else{
	
	$primaryKey = 'id_referencia';
	$table = 'referencias';
	
    $columns = array(	
		array('db' => 'id_referencia', 'dt' => 'id_referencia' ),
		array('db' => 'num_refe', 'dt' => 'num_refe' ),
		array('db' => 'num_refe', 'dt' => 'cliente', 'formatter' => function( $d, $row ) {
			return 'STERIS MEXICO S DE RL DE CV';
		} ),
		array('db' => 'fecha_cierre', 'dt' => 'fecha_cierre', 'formatter' => function( $d, $row ) {
			return date( 'd/m/Y H:i:s', strtotime($d));
		}),
        array('db' => 'usuario_cierre', 'dt' => 'usuario_cierre' )
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb_sterisr8va,
        'host' => $mysqlserver
    );
    
    $baseSql = "SELECT r.id_referencia, r.num_refe, r.fecha_cierre, u.usunombre as usuario_cierre
				FROM referencias r
					INNER JOIN bodega.tblusua u ON
						r.id_usuario_cierre = u.Usuario_id";	

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../ssp.class.php');
    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







