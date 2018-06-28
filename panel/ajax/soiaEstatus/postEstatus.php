<?php
include_once('./../../../checklogin.php');
require('./../../../connect_exp.php');
require('./../../../connect_casa.php');

if ($loggedIn == false){
	$msg='Code [500]';
	echo json_encode( array( 
		"error" => $msg
	) );
	exit(0);
} else { 
	//***********************************************************//
		
	$sBusqueda = $_POST['sBusqueda'];
	$sTexto = $_POST['sTexto'];
	
	$sReferenciaCASA = '';
	$sClienteCASA = '';
	//***********************************************************//
	
	$baseSql = '';
	if ($sBusqueda == 'pedimento') {
		if($sTexto != ''){
			$aPedimento = explode("-", $sTexto);
			if(count($aPedimento) >= 3) {
				$baseSql = "a.pedimento='".$aPedimento[2]."' AND a.patente='".$aPedimento[1]."' AND a.aduana='".$aPedimento[0]."'";
				$sReferenciaCASA = get_referencia_casa();
			} else {
				$baseSql = "a.pedimento='".$sTexto."'";
			}
		} else {
			$baseSql = "a.pedimento='-1'";
		}
	} else {
		if($sTexto != ''){
			$baseSql = "a.num_refe='".$sTexto."'";
			$sReferenciaCASA = $sTexto;
		} else {
			$baseSql = "a.num_refe='-1'";
		}
	}
	
	if ($sReferenciaCASA != '') {
		$sClienteCASA = get_cliente_casa();
	}
	
	//***********************************************************//
	
	// DB table to use
	$table = 'soia_situacion_pedime';

	// Table's primary key
	$primaryKey = 'id_sit_pedime';

	// Array of database columns which should be read and sent back to DataTables.
	// The `db` parameter represents the column name in the database, while the `dt`
	// parameter represents the DataTables column identifier. In this case object
	// parameter names

	$columns = array(	
		array('db' => 'id_sit_pedime',  'dt' => 'id_sit_pedime' ),
		array('db' => 'pedimento',  'dt' => 'pedimento' ),
		array('db' => 'num_refe',  'dt' => 'num_refe' ),
		array('db' => 'remesa', 'dt' => 'remesa' ),
		array('db' => 'estado_actual', 'dt' => 'estado_actual' ),
		array('db' => 'evento', 'dt' => 'evento' )
	);

	$sql_details = array(
		'user' => $mysqluser_exp,
        'pass' => $mysqlpass_exp,
        'db'   => $mysqldb_exp,
        'host' => $mysqlserver_exp
	);

	$baseSql = "SELECT a.id_sit_pedime, a.pedimento, a.num_refe, a.secuencia, a.factura AS remesa,
					   (SELECT y.descripcion
						FROM casa.soia_eventos AS z INNER JOIN
						     casa.soia_estados AS y ON z.id_estado = y.id_estado
						WHERE z.id_sit_pedime=a.id_sit_pedime
						ORDER BY z.id_evento DESC
						LIMIT 1) AS estado_actual,
					   (SELECT CONCAT(z.id_estado_detalle, '-', y.descripcion, '-' ,DATE_FORMAT(z.fecha, '%d/%m/%Y %H:%i'))
						FROM casa.soia_eventos AS z INNER JOIN
						     casa.soia_estados AS y ON z.id_estado_detalle = y.id_estado
						WHERE z.id_sit_pedime=a.id_sit_pedime AND 
							  z.id_estado_detalle IN (310, 510, 320, 520)
						ORDER BY z.fecha DESC
						LIMIT 1) AS evento
				FROM casa.soia_situacion_pedime AS a
			    WHERE ".$baseSql."
				ORDER BY a.pedimento, a.num_refe, CAST(a.factura as unsigned) ASC";
				
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP
	 * server-side, there is no need to edit below this line.
	 */

	require('../../ssp.class.php');
	
	$aDtTable = SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns );
	$aDtTable['sClienteCASA'] = $sClienteCASA;
	
	echo json_encode($aDtTable);
	/*echo json_encode(
		SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
	);*/
}

function get_referencia_casa() {
	global $aPedimento, $cmysqli_exp;
	$Referencia = '';
	
	$consulta="SELECT num_refe
			   FROM casa.soia_situacion_pedime 
			   WHERE aduana='".$aPedimento[0]."' AND
			         patente='".$aPedimento[1]."' AND
					 pedimento='".$aPedimento[2]."'
			   LIMIT 1";
	
	$query = mysqli_query($cmysqli_exp, $consulta);
	
	if (!$query) {
		$error=mysqli_error($cmysqli_exp);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar la referencia del pedimento ['.$aPedimento[2].'].'; 
		$respuesta['Error'] = ' ['.$error.']';	
	} else {
		while ($row = mysqli_fetch_array($query)){ 
			$Referencia = $row['num_refe'];
			break;
		}
	}
	
	return $Referencia;
}

function get_cliente_casa() {
	global $sReferenciaCASA, $odbccasa;
	$Cliente = '';
	
	$consulta = "SELECT NOM_IMP
				 FROM SAAIO_PEDIME a INNER JOIN 
				 	  CTRAC_CLIENT b ON b.CVE_IMP = a.CVE_IMPO
				 WHERE a.NUM_REFE='".$sReferenciaCASA."'";
	
	$query = odbc_exec ($odbccasa, $consulta);
	if ($query==false){ 
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "Error al consultar la Referencia [".$sReferenciaCASA."] en el sistema CASA.";
		$respuesta['Error'] = ' ['.$query.']';
	} else {
		while(odbc_fetch_row($query)){ 
			$Cliente = odbc_result($query,"NOM_IMP");
			break;
		}
	}
	
	return $Cliente;
}