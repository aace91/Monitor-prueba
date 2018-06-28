<?php
include_once('./../../../checklogin.php');
require('./../../../connect_casa.php');
include('./../../../connect_r8va.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {		
		
		$referencia = trim($_POST['referencia']);
		$num_fact = trim($_POST['num_fact']);

		$qCasa = "SELECT a.CONS_FACT, a.CONS_PART, a.NUM_PART, a.FRACCION, a.MON_FACT, a.CAN_FACT, a.DES_MERC
					FROM SAAIO_FACPAR a
						INNER JOIN SAAIO_FACTUR b ON
							a.NUM_REFE = b.NUM_REFE and
							a.CONS_FACT = b.CONS_FACT
					WHERE a.NUM_REFE = '".$referencia."' AND b.NUM_FACT = '".$num_fact."'";
		
		$resCasa = odbc_exec ($odbccasa, $qCasa);
		if ($resCasa == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar la informacion de la factura. [BD.CASA].";
			$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
			exit(json_encode($respuesta));
		}
		if(odbc_num_rows($resCasa) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "No existe informacion de la factura [".$num_fact."] en el sistema. [BD.CASA].";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		$aPartidas = array();
		while(odbc_fetch_row($resCasa)){	
			$sCONS_FACT = utf8_encode(odbc_result($resCasa,"CONS_FACT"));
			$sCONS_PART = utf8_encode(odbc_result($resCasa,"CONS_PART"));
			$sNUM_PART = utf8_encode(odbc_result($resCasa,"NUM_PART"));
			$sFRACCION = utf8_encode(odbc_result($resCasa,"FRACCION"));
			$sMON_FACT = utf8_encode(odbc_result($resCasa,"MON_FACT"));
			$sCAN_FACT = utf8_encode(odbc_result($resCasa,"CAN_FACT"));
			$sDES_MERC = utf8_encode(odbc_result($resCasa,"DES_MERC"));
			$Partida = array(
				"cons_fact" => $sCONS_FACT,
				"cons_part" => $sCONS_PART,
				"num_part" => $sNUM_PART,
				"fraccion" => $sFRACCION,
				"des_merc" => $sDES_MERC,
				"val_fact" => $sMON_FACT,
				"can_fact" => $sCAN_FACT
			);
			array_push($aPartidas, $Partida);
		}
		$respuesta['Codigo'] = '1';
		$respuesta['aPartidas'] = $aPartidas;
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

