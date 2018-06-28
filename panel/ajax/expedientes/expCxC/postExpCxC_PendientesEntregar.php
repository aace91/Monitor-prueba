<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	$sIdEmpresa = $_POST['sIdEmpresa'];
	
	// DB table to use
	$table = 'expedientes';
	$primaryKey = 'cuenta_gastos';    
    $columns = array(	
		array('db' => 'cuenta_gastos', 'dt' => 'cuenta_gastos' ),
		array('db' => 'referencia', 'dt' => 'referencia' )
    );

    $sql_details = array(
        'user' => $mysqluser_exp,
        'pass' => $mysqlpass_exp,
        'db'   => $mysqldb_exp,
        'host' => $mysqlserver_exp
    );

	$baseSql = "SELECT CONCAT(tipo_mov,'-',no_banco,'-',no_mov) AS cuenta_gastos, referencia
				FROM expedientes.seguimiento_pedime
				WHERE id_empresa=".$sIdEmpresa." AND
				      fecha_cc_recepcion IS NOT NULL AND
					  fecha_cc_facturacion IS NOT NULL AND
					  fecha_cc_entrega IS NULL
				GROUP BY cuenta_gastos, referencia";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







