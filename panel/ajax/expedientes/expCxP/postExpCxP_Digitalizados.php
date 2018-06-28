<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{	
	// DB table to use
	$table = 'expedientes';
	$primaryKey = 'id_empresa';    
    $columns = array(	
		array('db' => 'id_empresa', 'dt' => 'id_empresa' ),
		array('db' => 'cuenta_gastos', 'dt' => 'cuenta_gastos' ),
		array('db' => 'referencia', 'dt' => 'referencia' ),
		array('db' => 'no_mov', 'dt' => 'refnumber', 'formatter' => function($val, $data) {
				if ($val==''){
					return '';
				}else{
					$nombre_fichero = '';
					
					if ($data['id_empresa'] == '1') {
						$nombre_fichero = "\\\\192.168.1.92\\contabilidad\\sab07\\cfd\\PDFS\\" . $val . ".pdf";
					} else if ($data['id_empresa'] == '2') {
						$nombre_fichero = "\\\\192.168.1.92\\contabilidad\\sab10\\cfd\\pfds\\" . $val . ".pdf";
					} else {
						$nombre_fichero = "\\\\192.168.1.92\\contabilidad\\sab07\\cfd\\PDFS\\" . $val . ".pdf";
					}
					
					if (file_exists($nombre_fichero)) {
						return $val;
					} else {
						return '';
					}
				}              
        }),
        array('db' => 'no_mov', 'dt' => 'idinvoice', 'formatter' => function($val, $data) {
				if ($val==''){
					return '';
				}else{
					$sTipo = '';
					if (strpos($data['cuenta_gastos'], 'R-') !== false) {
					    $sTipo = 'R_';
					}

					$link_base = "ajax/expedientes/expCxP/getinvoice.php?invoice=";
					$key = "Encripta Del Bravo Links";
					//$factura=rawurlencode( base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $val, MCRYPT_MODE_CBC, md5(md5($key)))));
					$factura = rawurlencode(openssl_encrypt($val, 'bf-ecb', $key, true));
					return $link_base . $factura . "&mp=" . $data['id_empresa']. "&tipo=" . $sTipo;
				}              
        })
    );

    $sql_details = array(
        'user' => $mysqluser_exp,
        'pass' => $mysqlpass_exp,
        'db'   => $mysqldb_exp,
        'host' => $mysqlserver_exp
    );

	$baseSql = "SELECT id_empresa, CONCAT(tipo_mov,'-',no_banco,'-',no_mov) AS cuenta_gastos, referencia, no_mov
				FROM expedientes.seguimiento_pedime
				WHERE fecha_cp_digitalizado IS NOT NULL
				GROUP BY cuenta_gastos, referencia";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */
	$table = '';
    require('../../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







