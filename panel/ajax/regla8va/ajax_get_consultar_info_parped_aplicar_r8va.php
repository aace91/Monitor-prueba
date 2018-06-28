<?php
include_once('./../../../checklogin.php');
include('./../../../connect_r8va.php');
include('./../../../connect_casa.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {		
		
		$referencia = trim($_POST['referencia']);
		$numero_partida = trim($_POST['numero_partida']);
		$fraccion = trim($_POST['fraccion']);
		$descripcion = trim($_POST['descripcion']);
		$aPartidasFac = array();
		$aPermisos = array();
		/* *****************************************************************
		 * CONSULTAR FACTURAS-PARTIDAS DE LA FRACCION
		 ***************************************************************** */
		$qCasa = "	SELECT a.NUM_REFE, a.CONS_FACT, a.CONS_PART, f.NUM_FACT, p.NUM_PART, p.FRACCION, p.DES_MERC, p.CAN_FACT, p.MON_FACT 
					FROM SAAIO_PARCONS a
						INNER JOIN SAAIO_FACPAR p ON
							a.NUM_REFE = p.NUM_REFE AND
							a.CONS_FACT = p.CONS_FACT AND
							a.CONS_PART = p.CONS_PART
						INNER JOIN SAAIO_FACTUR f ON
							a.NUM_REFE = f.NUM_REFE AND
							a.CONS_FACT = f.CONS_FACT
					WHERE a.NUM_REFE = '".$referencia."' AND a.CONS_FRA = ".$numero_partida;
		
		$resCasa = odbc_exec ($odbccasa, $qCasa);
		if ($resCasa == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar la informacion de la referencia. [BD.CASA].";
			$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
			exit(json_encode($respuesta));
		}
		/*if(odbc_num_rows($resCasa) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "No se encontraron partidas capturadas para la referencia. [BD.CASA].";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}*/
		while(odbc_fetch_row($resCasa)){	
			$sCONS_FACT = utf8_encode(odbc_result($resCasa,"CONS_FACT"));
			$sCONS_PART = utf8_encode(odbc_result($resCasa,"CONS_PART"));
			$sNUM_FACT = utf8_encode(odbc_result($resCasa,"NUM_FACT"));
			$sNUM_PART = utf8_encode(odbc_result($resCasa,"NUM_PART"));
			$sFRACCION = utf8_encode(odbc_result($resCasa,"FRACCION"));
			$sDES_MERC = utf8_encode(odbc_result($resCasa,"DES_MERC"));
			$sCAN_FACT = utf8_encode(odbc_result($resCasa,"CAN_FACT"));
			$sMON_FACT = utf8_encode(odbc_result($resCasa,"MON_FACT"));
			
			$ParFac = array(
				"cons_fact" => $sCONS_FACT,
				"cons_part" => $sCONS_PART,
				"num_fact" => $sNUM_FACT,
				"num_part" => $sNUM_PART,
				"fraccion" => $sFRACCION,
				"des_merc" => $sDES_MERC,
				"can_fact" => $sCAN_FACT,
				"val_fact" => $sMON_FACT
			);
			array_push ($aPartidasFac,$ParFac);
		}
		
		$consulta = "SELECT numero_permiso
						FROM fracciones 
						WHERE fraccion = '".$fraccion."' AND eliminado = '0' AND fecha_vencimiento >= CURDATE()
						GROUP BY numero_permiso";
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar informacion.';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) != 0){
			while($row = mysqli_fetch_array($query)){
				array_push($aPermisos,$row['numero_permiso']);
			}
			$respuesta['Codigo'] = '1';
			$respuesta['aPermisos'] = $aPermisos;
			$respuesta['aPartidasFac'] = $aPartidasFac;
		}else{
			$respuesta['Codigo'] = '-1';
			$respuesta['Mensaje'] = "No se encontraron permisos disponibles en el catalogo del cliente. Favor de contactar el administrador del sistema.";
			$respuesta['Error'] = '';
		}
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

