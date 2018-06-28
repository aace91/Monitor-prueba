<?php
include_once('./../../../checklogin.php');
include('./../../../connect_casa.php');
include('./../../../connect_r8va.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {

		$referencia = trim($_POST['referencia']);
		$aPartidas = array();
		
		/********************************************************************************************************
		 * CONSULTAR PARTIDAS DE LA REFERENCIA EN BD CASA - CIERRE
		 ****************************************************************************************************** */

		$qCasa = "SELECT a.NUM_PART,a.FRACCION,a.DES_MERC,a.TIP_MONE,a.VAL_NORF, a.CAN_TARI,umt.DES_UNI as UMT,a.VAL_COMF,a.CAN_FACT, umc.DES_UNI as UMC,a.PAI_ORIG,a.PAI_VEND,a.CAS_TLCS, 
							CASE WHEN p.NUM_PERM IS NULL THEN '' ELSE p.NUM_PERM END AS NUM_PERM, 
							ped.FIR_REME,ped.FIR_PAGO,ped.FIR_ELEC, 
							CASE WHEN r.partidas IS NULL THEN 0 ELSE r.partidas END AS CNT_PAR
					FROM SAAIO_FRACCI a 
					INNER JOIN SAAIO_PEDIME ped ON a.NUM_REFE = ped.NUM_REFE 
					INNER JOIN CTARC_UNIDAD umt ON a.UNI_TARI = umt.NUM_UNI 
					INNER JOIN CTARC_UNIDAD umc ON a.UNI_FACT = umc.NUM_UNI 
					LEFT JOIN SAAIO_PERMIS p ON a.NUM_REFE = p.NUM_REFE and a.NUM_PART = p.NUM_PART 
					LEFT JOIN (
								SELECT pc.NUM_REFE,pc.CONS_FRA, COUNT(*) as partidas 
								FROM SAAIO_PARCONS pc 
								GROUP BY pc.NUM_REFE,pc.CONS_FRA
								) r ON
						a.NUM_REFE = r.NUM_REFE AND 
						a.NUM_PART = r.CONS_FRA
					WHERE a.NUM_REFE = '".$referencia."'";
		
		$resCasa = odbc_exec ($odbccasa, $qCasa);
		if ($resCasa == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar la informacion de la referencia. [BD.CASA].";
			$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
			exit(json_encode($respuesta));
		}
		if(odbc_num_rows($resCasa) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "No se encontraron partidas a nivel pedimento de la referencia.[Partidas-Pedimento][SAAIO_FRACCI][BD.CASA].";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		while(odbc_fetch_row($resCasa)){
			$NUM_PART = utf8_encode(odbc_result($resCasa,"NUM_PART"));
			$FRACCION = utf8_encode(odbc_result($resCasa,"FRACCION"));
			$DES_MERC = utf8_encode(odbc_result($resCasa,"DES_MERC"));
			$TIP_MONE = utf8_encode(odbc_result($resCasa,"TIP_MONE"));
			$VAL_COMF = utf8_encode(odbc_result($resCasa,"VAL_COMF"));
			$CAN_TARI = utf8_encode(odbc_result($resCasa,"CAN_TARI"));
			$UMT = utf8_encode(odbc_result($resCasa,"UMT"));
			$VAL_NORF = utf8_encode(odbc_result($resCasa,"VAL_NORF"));
			$CAN_FACT = utf8_encode(odbc_result($resCasa,"CAN_FACT"));
			$UMC = utf8_encode(odbc_result($resCasa,"UMC"));
			$PAI_ORIG = utf8_encode(odbc_result($resCasa,"PAI_ORIG"));
			$PAI_VEND = utf8_encode(odbc_result($resCasa,"PAI_VEND"));
			$NUM_PERM = utf8_encode(odbc_result($resCasa,"NUM_PERM"));
			$CAS_TLCS = utf8_encode(odbc_result($resCasa,"CAS_TLCS"));
			//Saber si es consolidado y si no esta pagado
			$sFIR_REME = utf8_encode(odbc_result($resCasa,"FIR_REME"));
			$sFIR_PAGO = utf8_encode(odbc_result($resCasa,"FIR_PAGO"));
			$FIR_ELEC = utf8_encode(odbc_result($resCasa,"FIR_ELEC"));
			$CNT_PAR = utf8_encode(odbc_result($resCasa,"CNT_PAR"));
			
			/*if(trim($sFIR_PAGO) != ''){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'La referencia que desea consultar, ya cuenta con firma de pago.';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				exit(json_encode($respuesta));
			}
			if(trim($FIR_ELEC) != ''){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'La referencia que desea consultar, ya cuenta con firma electr&oacute;nica.';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				exit(json_encode($respuesta));
			}*/
			
			$RegApli = '0'; $PartApli = '0'; $FracciApli = '0';
			//Revisar si el permiso 
			if($NUM_PERM != ''){
				$consulta = "SELECT fraccion FROM fracciones WHERE numero_permiso='".$NUM_PERM."'";
				
				$query = mysqli_query($cmysqli_s8va, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli_s8va);
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Error al consultar permisos en catalogo de fracciones.[MySQL]';
					$respuesta['Error'] = '['.$error.']['.$consulta.']';
					exit(json_encode($respuesta));
				}
				if(mysqli_num_rows($query) > 0){
					$RegApli = '1';
				}
			}else{
				//Consultar si la partida aplica para la regla 8va en la tabala de steris_r8va.fracciones
				$consulta = "SELECT fraccion,descripcion 
							FROM fracciones 
							WHERE fraccion = '".$FRACCION."'  AND eliminado = '0' AND fecha_vencimiento >= CURDATE() ";
				
				$query = mysqli_query($cmysqli_s8va, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli_s8va);
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Error al consultar fracciones disponibles para la regla 8va.';
					$respuesta['Error'] = '['.$error.']['.$consulta.']';
					exit(json_encode($respuesta));
				}
				if(mysqli_num_rows($query) > 0){
					$bExistDesc = false;$FracciApli = '1';
					while($row = mysqli_fetch_array($query)){
						if(trim($row['descripcion']) == trim($DES_MERC)){
							$bExistDesc = true;
							break;
						}
					}
					if(!$bExistDesc){$PartApli = '1';}
				}
			}
			/*Verificar si se aplico la R 8va desde el sistema Web*/
			$RegApliWeb = '0';
			if($RegApli){//Si se aplico la R8va ver si fue desde el sistema web
				if($CNT_PAR > 0){
					//Seleccionar partidas de la fraccion
					$qCasa = "SELECT a.CONS_FACT,a.CONS_PART
								FROM SAAIO_PARCONS a 
								WHERE a.NUM_REFE = '".$referencia."'  AND a.CONS_FRA = ".$NUM_PART;
								
					$resParCons = odbc_exec ($odbccasa, $qCasa);
					if ($resParCons == false){
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = "Error al consultar la informacion de las partidas. [BD.CASA].";
						$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
						exit(json_encode($respuesta));
					}
					if(odbc_num_rows($resParCons) > 0){
						$RegApliWeb = '1';
						while(odbc_fetch_row($resParCons)){
							$CONS_FACT_PARCON = utf8_encode(odbc_result($resParCons,"CONS_FACT"));
							$CONS_PART_PARCON = utf8_encode(odbc_result($resParCons,"CONS_PART"));
							//Revisar que todas las partidas de la fraccion se les haya aplicado la R8va desde el sistema Web*/
							$consulta = "SELECT *
										FROM fracciones_historico 
										WHERE num_refe='".$referencia."' AND cons_fact = ".$CONS_FACT_PARCON.' AND cons_par = '.$CONS_PART_PARCON;
							
							$query = mysqli_query($cmysqli_s8va, $consulta);
							if (!$query) {
								$error=mysqli_error($cmysqli_s8va);
								$respuesta['Codigo'] = -1;
								$respuesta['Mensaje'] = 'Error al consultar fracciones disponibles para la regla 8va.';
								$respuesta['Error'] = '['.$error.']['.$consulta.']';
								exit(json_encode($respuesta));
							}
							if(mysqli_num_rows($query) == 0){
								$RegApliWeb = '0';
								break;
							}
						}
					}
				}
			}
			
			$aPartida = array(
				"numero_partida" => $NUM_PART,
				"fraccion" => $FRACCION,
				"descripcion" => $DES_MERC,
				"tipo_moneda" => $TIP_MONE,
				"valor_aduana" => $VAL_NORF,
				"cantidad_tarifa" => $CAN_TARI,
				"umt" => $UMT,
				"valor_comercial" => $VAL_COMF,
				"cantidad_factura" => $CAN_FACT,
				"umc" => $UMC,
				"pais_origen" => $PAI_ORIG,
				"pais_vendedor" => $PAI_VEND,
				"numero_permiso" => $NUM_PERM,
				"tlc" => $CAS_TLCS,
				"regla_aplicada" => $RegApli,
				"aplica_partida" => $PartApli,
				"aplica_fraccion" => $FracciApli,
				"cantidad_partidas" => $CNT_PAR,
				"seaplico_r8va_web" => $RegApliWeb
			);
			
			array_push($aPartidas,$aPartida);
		}
		$respuesta['Codigo'] = 1;
		$respuesta['aPartidas'] = $aPartidas;
		
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	exit(json_encode($respuesta));
}

