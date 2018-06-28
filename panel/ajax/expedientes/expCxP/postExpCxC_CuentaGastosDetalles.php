<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{	
	$sIdEmpresa = $_POST['sIdEmpresa'];
	$sCuentaGastos = $_POST['sCuentaGastos'];
	$sReferencia = $_POST['sReferencia'];
	
	$aCuentaGastos = explode("-", $sCuentaGastos);
	$strTIPO_MOV = $aCuentaGastos[0];
	$strNO_BANCO = $aCuentaGastos[1];
	$strNO_MOV = $aCuentaGastos[2];
		
	if ($sFecha == '') {
		$sFecha = 'IS NULL';
	} else {
		$sFecha = "='".$sFecha."'";
	}
	
	// DB table to use
	$table = 'expedientes';	
	$primaryKey = 'cuenta_gastos';    
    $columns = array(	
		array('db' => 'cuenta_gastos', 'dt' => 'cuenta_gastos' ),
		array('db' => 'referencia', 'dt' => 'referencia' ),
		array('db' => 'pedimento', 'dt' => 'pedimento' ),
		array('db' => 'comentarios', 'dt' => 'comentarios' ),
        array( 'db' => 'fecha_cc_facturacion',   'dt' => 'fecha_cc_facturacion', 'formatter' => function( $d, $row ) {
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

	$baseSql = "SELECT CONCAT(tipo_mov,'-',no_banco,'-',no_mov) AS cuenta_gastos, referencia, pedimento, comentarios, fecha_cc_facturacion
				FROM expedientes.seguimiento_pedime
				WHERE id_empresa=".$sIdEmpresa." AND
				      tipo_mov='".$strTIPO_MOV."' AND
				      no_banco='".$strNO_BANCO."' AND
				      no_mov='".$strNO_MOV."' AND
				      referencia='".$sReferencia."' AND
				      fecha_cc_entrega IS NOT NULL";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







