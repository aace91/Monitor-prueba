<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{	
	// DB table to use
	$table = 'expedientes';
	$primaryKey = 'cuenta_gastos';    
    $columns = array(	
		array('db' => 'cuenta_gastos', 'dt' => 'cuenta_gastos' ),
		array('db' => 'referencia', 'dt' => 'referencia' ),
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

	/*$baseSql = "SELECT CONCAT(tipo_mov,'-',no_banco,'-',no_mov) AS cuenta_gastos, referencia, fecha_cc_entrega
				FROM expedientes.seguimiento_pedime
				WHERE pedimento IS NOT NULL AND fecha_cc_entrega='".$sFecha."'
				GROUP BY cuenta_gastos, referencia, fecha_cc_entrega";*/
				
	$baseSql = "SELECT CONCAT(tipo_mov,'-',no_banco,'-',no_mov) AS cuenta_gastos, referencia, fecha_cc_entrega
				FROM expedientes.seguimiento_pedime
				WHERE pedimento IS NOT NULL AND fecha_cc_entrega IS NOT NULL
				GROUP BY cuenta_gastos, referencia, fecha_cc_entrega";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







