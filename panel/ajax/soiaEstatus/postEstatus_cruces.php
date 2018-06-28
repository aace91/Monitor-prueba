<?php
include_once('./../../../checklogin.php');
include('./../../../connect_exp.php');

if ($loggedIn == false){
	$msg='Code [500]';
	echo json_encode( array( 
		"error" => $msg
	) );
	exit(0);
} else { 
	
	$sPedimento = '';$sAduana = '';	$sPatente = ''; $sRemesa = '';
	$table = 'soia_situacion_pedime';
	$primaryKey = 'id_sit_pedime';
	$pedimento = $_POST['pedimento'];
	$id_cruce = $_POST['id_cruce'];
	
	$aPedimento = explode("-", $pedimento);
	
	if(count($aPedimento) == 3) {
		$sPedimento = $aPedimento[2];
		$sAduana = $aPedimento[0];
		$sPatente = $aPedimento[1];
		$bConsulta = true;
	}else{
		if(count($aPedimento) == 4) {
			$sPedimento = $aPedimento[2];
			$sAduana = $aPedimento[0];
			$sPatente = $aPedimento[1];
			$sRemesa = $aPedimento[3];
			$bConsulta = true;
		}else{
			$bConsulta = false;
		}
	}
	
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
	
	if($bConsulta){
		$where = "a.pedimento='".$aPedimento[2]."' AND a.patente='".$aPedimento[1]."' AND a.aduana='".$aPedimento[0]."'";
		if(count($aPedimento) == 4) {
			$where .= "   AND a.factura = '".$aPedimento[3]."'";
		}
	}else{
		$where = "a.pedimento='-1'";
	}
		
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
			    WHERE ".$where ."
				ORDER BY a.pedimento, a.num_refe, CAST(a.factura as unsigned) ASC";
		
			
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP   *
	 * server-side, there is no need to edit below this line.                    *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	require('../../ssp.class.php');
	
	$aDtTable = SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns );
	//$aDtTable['sClienteCASA'] = $sClienteCASA;
	
	echo json_encode($aDtTable);
	/*echo json_encode(
		SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
	);*/
}
