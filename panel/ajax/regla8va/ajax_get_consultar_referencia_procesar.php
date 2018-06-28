<?php
include_once('./../../../checklogin.php');
include('./../../../connect_casa.php');
include('./../../../connect_r8va.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {

		$referencia = trim($_POST['referencia']);
		$estatus = trim($_POST['estatus']);
		$remesa = trim($_POST['remesa']);
		$permiso = trim($_POST['permiso']);
		$aPermisos = array();

		$aPartidas = array();
		$aRemesas = array();

		/********************************************************************************************************
		 * CONSULTAR PERMISOS
		 ****************************************************************************************************** */
		if($permiso == ''){
			$consulta = "SELECT numero_permiso
							FROM fracciones 
							WHERE eliminado = '0' AND fecha_vencimiento >= CURDATE()
							GROUP BY numero_permiso
							ORDER BY numero_permiso";

			$query = mysqli_query($cmysqli_s8va, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_s8va);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al verificar partidas con regla 8va aplicada.';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				exit(json_encode($respuesta));
			}
			if(mysqli_num_rows($query) == 0){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "No se encontraron permisos disponibles para aplicar. [MySQL].";
				$respuesta['Error'] = '[Seccion de fracciones sin registros disponibles]';
				exit(json_encode($respuesta));
			}
			$nItem = 0;
			while($row = mysqli_fetch_array($query)){
				if($nItem == 0){
					$permiso = $row['numero_permiso'];
					$nItem += 1;
				}
				array_push($aPermisos,$row['numero_permiso']);				
			}
		}
		/********************************************************************************************************
		 * CONSULTAR REMESAS REFERENCIAS
		 ****************************************************************************************************** */
		if($remesa == ''){
			$qCasa = "SELECT a.NUM_REM,p.FIR_REME
						FROM SAAIO_FACTUR a
							INNER JOIN SAAIO_PEDIME p ON
								a.NUM_REFE = p.NUM_REFE
						WHERE a.NUM_REFE = '".$referencia."'
						GROUP BY a.NUM_REM,p.FIR_REME
						ORDER BY a.NUM_REM";
			$resCasa = odbc_exec ($odbccasa, $qCasa);
			if ($resCasa == false){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al consultar remesas en la referencia. [BD.CASA].";
				$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
				exit(json_encode($respuesta));
			}
			while(odbc_fetch_row($resCasa)){	
				$NUM_REM = odbc_result($resCasa,"NUM_REM");
				$FIR_REME = odbc_result($resCasa,"FIR_REME");
				if($FIR_REME != ''){
					if (!in_array($NUM_REM, $aRemesas)){
						array_push($aRemesas,$NUM_REM);
					}
				}
			}
			if($aRemesas > 0 && $_POST['permiso'] == ''){
				$remesa = $aRemesas[count($aRemesas)-1];
			}
		}
		/********************************************************************************************************
		 * CONSULTAR PARTIDAS DE LA REFERENCIA EN BD CASA
		 ****************************************************************************************************** */

		$qCasa = "SELECT p.NUM_REFE,p.CONS_FACT,p.CONS_PART,f.NUM_FACT,prv.NOM_PRO,p.FRACCION as FRACCION_CASA,f.NUM_REM,
							CASE WHEN ped.FIR_REME IS NULL THEN '' ELSE ped.FIR_REME END as FIR_REME,
							CASE WHEN ped.FIR_PAGO IS NULL THEN '' ELSE ped.FIR_PAGO END as FIR_PAGO,
							CASE WHEN prm.FIR_ELEC IS NULL THEN '' ELSE prm.FIR_ELEC END as FIR_ELEC,
							CASE WHEN prm.NUM_PERM IS NULL THEN '' ELSE prm.NUM_PERM END as NUM_PERM,
							p.DES_MERC,p.CAN_FACT,p.MON_FACT,
							CASE WHEN p.CAS_TLCS IS NULL THEN '' ELSE p.CAS_TLCS END as CAS_TLCS
					FROM SAAIO_FACPAR p
						INNER JOIN SAAIO_PEDIME ped ON
                            p.NUM_REFE = ped.NUM_REFE
						INNER JOIN SAAIO_FACTUR f ON
							p.NUM_REFE = f.NUM_REFE AND
							p.CONS_FACT = f.CONS_FACT
						INNER JOIN CTRAC_PROVED prv ON
							f.CVE_PROV = prv.CVE_PRO
						LEFT JOIN SAAIO_PERPAR prm ON
							p.NUM_REFE = prm.NUM_REFE AND
							p.CONS_FACT = prm.CONS_FACT AND 
							p.CONS_PART = prm.CONS_PART AND 
							prm.NUM_PERM = '".$permiso."'
					WHERE p.NUM_REFE = '".$referencia."' ".($remesa != '' ? " AND f.NUM_REM =".$remesa : '');
		$resCasa = odbc_exec ($odbccasa, $qCasa);
		if ($resCasa == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar la informacion de la referencia. [BD.CASA].";
			$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
			exit(json_encode($respuesta));
		}
		if(odbc_num_rows($resCasa) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "No se encontraron partidas capturadas para la referencia. [BD.CASA].";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		while(odbc_fetch_row($resCasa)){	
			/* *****************************************************************************************
				Datos permanentes para aplicar la regla 8va a partidas de STERIS
				FRACCION: 98020007
			***************************************************************************************** */
			$sCONS_FACT = odbc_result($resCasa,"CONS_FACT");
			$sCONS_PART = odbc_result($resCasa,"CONS_PART");
			$sNUM_FACT = odbc_result($resCasa,"NUM_FACT");
			$sNOM_PRO = odbc_result($resCasa,"NOM_PRO");
			$sFRACCION_CASA = odbc_result($resCasa,"FRACCION_CASA");
			$sDESC_CASA = odbc_result($resCasa,"DES_MERC");
			$sNUM_PERM = odbc_result($resCasa,"NUM_PERM");
			$sCANT_FACT = odbc_result($resCasa,"CAN_FACT");
			$sMON_FACT = odbc_result($resCasa,"MON_FACT");
			$sNUM_REM = odbc_result($resCasa,"NUM_REM");
			$sCAS_TLCS = odbc_result($resCasa,"CAS_TLCS");
			//Saber si es consolidado y si no esta pagado
			$sFIR_REME = odbc_result($resCasa,"FIR_REME");
			$sFIR_PAGO = odbc_result($resCasa,"FIR_PAGO");
			$FIR_ELEC = odbc_result($resCasa,"FIR_ELEC");

			if(trim($sFIR_PAGO) != ''){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'La referencia que desea consultar, ya cuenta con firma de pago.';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
			}

			if(trim($FIR_ELEC) != ''){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'La referencia que desea consultar, ya cuenta con firma electr&oacute;nica.';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
			}

			$consulta = "SELECT IFNULL(fh.id_fraccion_hist,'') as id_fraccion_hist,fh.id_fraccion,fh.cantidad,fh.valor,
								f.descripcion,f.fraccion as fraccion_anterior,f.numero_permiso,IFNULL(fh.fecha_registro,'')as fecha_aplicacion,
								IFNULL(r.fecha_cierre,'') as cerrada
						FROM fracciones_historico fh 
							INNER JOIN fracciones f ON
								fh.id_fraccion = f.id_fraccion
							LEFT JOIN referencias r ON
								fh.num_refe = r.num_refe
						WHERE fh.num_refe = '".$referencia."' AND fh.cons_fact = '".$sCONS_FACT."' AND fh.cons_par = '".$sCONS_PART."'";

			$query = mysqli_query($cmysqli_s8va, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_s8va);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al verificar partidas con regla 8va aplicada.';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				exit(json_encode($respuesta));
			}

			$id_fraccion_hist = '';$id_fraccion = '';$descripcion = '';$aplica_partida = '1';//1-Si 0-No
			$cantidad = '';$valor = '';$fraccion_anterior = '';$fecha_aplicacion = '';$numero_permiso ='';$cerrada='';
			$aplica_fraccion = '0';

			while($row = mysqli_fetch_array($query)){
				$id_fraccion_hist = $row['id_fraccion_hist'];
				$id_fraccion = $row['id_fraccion'];
				$descripcion = $row['descripcion'];
				$cantidad = $row['cantidad'];
				$valor = $row['valor'];
				$fraccion_anterior = $row['fraccion_anterior'];
				$numero_permiso = $row['numero_permiso'];
				$fecha_aplicacion = $row['fecha_aplicacion'];
				$cerrada = $row['cerrada'];
			}
			if ($cerrada != '') {
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'La referencia que desea procesar ya fue cerrada. ';
				$respuesta['Error'] = date( 'd/m/Y H:i:s', strtotime($cerrada));
				exit(json_encode($respuesta));
			}
			//Consultar si la partida aplica para la regla 8va en la tabala de steris_r8va.fracciones
			if($id_fraccion_hist == '' && $sNUM_PERM == ''){
				$consulta = "SELECT descripcion 
							FROM fracciones 
							WHERE fraccion = '".$sFRACCION_CASA."' AND numero_permiso='".$permiso."' AND eliminado = '0' AND fecha_vencimiento >= CURDATE() ";
				
				$query = mysqli_query($cmysqli_s8va, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli_s8va);
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Error al consultar fracciones disponibles para la regla 8va.';
					$respuesta['Error'] = '['.$error.']['.$consulta.']';
					exit(json_encode($respuesta));
				}
				if(mysqli_num_rows($query) == 0){
					$aplica_partida = '0'; // No es fraccion/descripcion valida para aplicar r8va
				}else{
					// descripcion = '".$sDESC_CASA."'
					$bExistDesc = false;
					while($row = mysqli_fetch_array($query)){
						if(trim($row['descripcion']) == trim($sDESC_CASA)){
							$bExistDesc = true;
							break;
						}
					}
					if(!$bExistDesc){$aplica_fraccion = '1';}
				}
			}
			/*if($sFIR_REME != ''){
				if (!in_array($sNUM_REM, $aRemesas)){
					array_push($aRemesas,$sNUM_REM);
				}
			}*/
			$aPartida = array(
				//Datos CASA
				"consecutivo_factura" => utf8_encode($sCONS_FACT),
				"consecutivo_partida" => utf8_encode($sCONS_PART),
				"numero_factura" => utf8_encode($sNUM_FACT),
				"numero_remesa" => utf8_encode($sNUM_REM),
				"nombre_proveedor" => utf8_encode($sNOM_PRO),
				"fraccion_casa" => utf8_encode($sFRACCION_CASA),
				"descripcion_casa" => utf8_encode($sDESC_CASA),
				"permiso_casa" => utf8_encode($sNUM_PERM),//Si exite se aplico la regla --- Si existe y no en id_fraccion_hist se aplico sin el sistema web
				"cantidad_casa" => utf8_encode($sCANT_FACT),
				"valor_casa" => utf8_encode($sMON_FACT),
				"tlc_casa" => utf8_encode($sCAS_TLCS),
				//Datos partida sistema r8va
				"id_fraccion_hist" => $id_fraccion_hist,
				//"id_fraccion" => $id_fraccion,
				"descripcion_web" => $descripcion,
				"permiso_web" => $numero_permiso,
				"cantidad_web" => $cantidad,
				"valor_web" => $valor,
				"fraccion_anterior" => $fraccion_anterior,
				"fecha_aplicacion" => ($fecha_aplicacion != '' ? date( 'd/m/Y H:i:s', strtotime($fecha_aplicacion)) : ''),
				"aplica_regla" => $aplica_partida,
				"aplica_fraccion" => $aplica_fraccion
			);

			switch($estatus){
				case '0':
					array_push($aPartidas,$aPartida);
					break;
				case 'pend':
					if($id_fraccion_hist == '' && $aplica_partida == '1' && $sNUM_PERM == ''){
						array_push($aPartidas,$aPartida);
					}
					break;
				case 'apli':
					if($sNUM_PERM != ''){
						array_push($aPartidas,$aPartida);
					}
					break;
				case 'apli_sis':	
					if($id_fraccion_hist != ''){
						array_push($aPartidas,$aPartida);
					}
					break;
				case 'apli_man':	
					if($id_fraccion_hist == '' && $sNUM_PERM != ''){
						array_push($aPartidas,$aPartida);
					}
					break;
				case 'no_apli':	
					if($aplica_partida == '0'){
						array_push($aPartidas,$aPartida);
					}
					break;
				case 'tlcs':
					if($sCAS_TLCS != ''){
						array_push($aPartidas,$aPartida);
					}
					break;
			}
		}
		$respuesta['Codigo'] = 1;
		$respuesta['aPartidas'] = $aPartidas;
		$respuesta['remesa'] = $remesa;
		$respuesta['aRemesas'] = $aRemesas;
		$respuesta['permiso'] = $permiso;
		$respuesta['aPermisos'] = $aPermisos;

	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	exit(json_encode($respuesta));
}

