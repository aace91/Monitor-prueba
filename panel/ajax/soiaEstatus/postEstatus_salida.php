<?php
require('./../../../connect_exp.php');
include('./../../../connect_dbsql.php');

if ($loggedIn == false){
	$msg='Code [500]';
	echo json_encode( array( 
		"error" => $msg
	) );
	exit(0);
} else { 
	
	$sPedimento = '';$sAduana = '';	$sPatente = '';
	$table = 'soia_situacion_pedime';
	$primaryKey = 'id_sit_pedime';
	//***********************************************************//
	
	$pedimento = $_POST['pedimento'];
	$numsalida = $_POST['numsalida'];
	
	$aPedimento = explode("-", $pedimento);
	
	if(count($aPedimento) >= 3) {
		$sPedimento = $aPedimento[2];
		$sAduana = $aPedimento[0];
		$sPatente = $aPedimento[1];
		$bConsulta = true;
	}else{
		$bConsulta = false;
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
		'user' => $mysqluser5,
        'pass' => $mysqlpass5,
        'db'   => $mysqldb5,
        'host' => $mysqlserver5
	);
	if($bConsulta){
		$UUID = '';
	
		$consulta = "SELECT GROUP_CONCAT(DISTINCT CONCAT('\'', fe.UUID,'\'')) AS UUID
					 FROM salidas_expo se INNER JOIN 
					      facturas_expo fe ON se.salidanumero = fe.SALIDA_NUMERO
					WHERE se.aduana = '$sAduana' AND fe.PATENTE = '$sPatente' AND fe.PEDIMENTO = '$sPedimento' AND se.salidanumero = $numsalida
					GROUP BY se.salidanumero";
		
		
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$mensaje = "Error al consultar la informacion del pedimento.".$error;
			echo json_encode( array("error" => $mensaje));
			exit(0);
		}else{
			while ($row = mysqli_fetch_array($query)){
				$UUID = $row['UUID'];
			}
		}
		$remesas = '';
		if($UUID != ''){
			$consulta = "SELECT GROUP_CONCAT( DISTINCT NUM_REM) as remesas
					 FROM casa.saaio_factur 
					 WHERE NUM_FACT2 IN (".$UUID.")
					 GROUP BY NUM_REFE";
		
			$query = mysqli_query($cmysqlsab10, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqlsab10);
				$mensaje = "Error al consultar la remesa de la factura.".$error;
				echo json_encode( array("error" => $mensaje));
				exit(0);
			}else{
				while ($row = mysqli_fetch_array($query)){
					$remesas = $row['remesas'];
				}
			}
		}
		if($remesas == ''){
			$where = "a.pedimento='".$aPedimento[2]."' AND a.patente='".$aPedimento[1]."' AND a.aduana='".$aPedimento[0]."' AND a.factura = ''";
		}else{
			$where = "a.pedimento='".$aPedimento[2]."' AND a.patente='".$aPedimento[1]."' AND a.aduana='".$aPedimento[0]."'  AND a.factura in (".$remesas.")";
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
				
	/*$baseSql = "SELECT a.id_sit_pedime, a.pedimento, a.num_refe, a.secuencia, a.factura AS remesa, 
					   a.id_estado, b.descripcion AS estado_actual, a.fecha,
					   (SELECT CONCAT(z.id_estado_detalle, '-', y.descripcion, '-' ,DATE_FORMAT(z.fecha, '%d/%m/%Y %H:%i'))
						FROM casa.soia_eventos AS z INNER JOIN
							 casa.soia_estados AS y ON z.id_estado_detalle = y.id_estado
						WHERE z.id_sit_pedime=a.id_sit_pedime AND 
							  z.id_estado_detalle IN (310, 510, 320, 520)
						ORDER BY z.fecha DESC
						LIMIT 1) AS evento
				FROM casa.soia_situacion_pedime AS a INNER JOIN
				     casa.soia_estados AS b ON b.id_estado = a.id_estado
			    WHERE ".$where ."
				ORDER BY a.pedimento, a.num_refe, CAST(a.factura as unsigned) ASC";*/
	
				
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP
	 * server-side, there is no need to edit below this line.
	 */

	require('../../ssp.class.php');
	
	$aDtTable = SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns );
	//$aDtTable['sClienteCASA'] = $sClienteCASA;
	
	echo json_encode($aDtTable);
	/*echo json_encode(
		SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
	);*/
}
