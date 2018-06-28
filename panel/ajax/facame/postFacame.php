<?php
include_once('./../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	require('./../../../connect_dbsql.php');
	
	$buscar_por = $_POST['buscar_por'];
	$texto_buscar = $_POST['texto_buscar'];
	
	// DB table to use
	$table = 'invoice';
	$primaryKey = 'idinvoice';
    
    $columns = array(	
		array('db' => 'cuenta_americana', 'dt' => 'cuenta_americana' ),
        array('db' => 'Subtotal', 'dt' => 'Subtotal' ),
		array('db' => 'cuenta_americana', 'dt' => 'refnumber'),
        array('db' => 'idinvoice', 'dt' => 'idinvoice', 'formatter' => function($val, $data) {
				if ($val==''){
					return '';
				}else{
					$link_base = "getinvoice.php?invoice=";
					$key = "Encripta Del Bravo Links";
					//$factura=rawurlencode( base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $val, MCRYPT_MODE_CBC, md5(md5($key)))));
					$factura = rawurlencode(openssl_encrypt($val, 'bf-ecb', $key, true));
					return $link_base . $factura;
				}              
        })
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb,
        'host' => $mysqlserver
    );
    
    // $baseSql = "SELECT c.qbsql_id, c.RefNumber as cuenta_americana, c.Subtotal, c.refnumber, c.refnumber as idinvoice
				// FROM qbdelbravo.qb_invoice AS c
				// WHERE c.fob = '3483-6909016'";	

	$baseSql = "";
			
	if ($buscar_por == 'pedimento') {
		if($texto_buscar!=''){
			$baseSql = "SELECT a.RefNumber as cuenta_americana, txnid as idinvoice, a.Subtotal
				        FROM qbdelbravo_sync.invoice AS a
						WHERE a.fob = '".$texto_buscar."'";
		} else {
			$baseSql.= "SELECT a.RefNumber as cuenta_americana, txnid as idinvoice, a.Subtotal
				        FROM qbdelbravo_sync.invoice AS a
						WHERE a.fob = '-1'";
		}
	} else {
		if($texto_buscar!=''){
			$baseSql = "SELECT b.fob, b.RefNumber as cuenta_americana, txnid as idinvoice, b.Subtotal, a.Description
						FROM qbdelbravo_sync.invoicelinedetail as a
						LEFT JOIN qbdelbravo_sync.invoice as b on a.idkey=b.TxnID
						WHERE a.Description REGEXP '".$texto_buscar."[^-]|".$texto_buscar."$'";
		} else {
			$baseSql.= "SELECT a.RefNumber as cuenta_americana, txnid as idinvoice, a.Subtotal
				        FROM qbdelbravo_sync.invoice AS a
						WHERE a.fob = '-1'";
		}
	}
	
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







