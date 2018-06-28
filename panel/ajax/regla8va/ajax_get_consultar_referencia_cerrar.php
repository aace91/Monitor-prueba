<?php
include_once('./../../../checklogin.php');
include('./../../../connect_casa.php');
include('./../../../connect_r8va.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {

		$referencia = trim($_POST['referencia']);
		$aPartidas = array();  $aPermisos = array();

		//***********************************************************//
		$id_usuario = $id;
		$fecha_registro =  date("Y-m-d H:i:s");	
		//***********************************************************//
		//Revisar si la referencia ya se encuentra cerrada
		$consulta = "SELECT * FROM referencias WHERE num_refe = '".$referencia."'";
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar informacion de la referencia.[MySQL].';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) > 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "La referencia que desea consultar ya se encuentra como cerrada. Favor de revisar con el administrador del sistema.";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		/********************************************************************************************************
		 * PERMISOS USADOS EN LA REFERENCIA
		 ****************************************************************************************************** */
		$qCasa= "SELECT a.NUM_PERM
					FROM SAAIO_PERPAR a
					WHERE a.NUM_REFE = '".$referencia."'
					GROUP BY a.NUM_PERM";

		$resCasa = odbc_exec ($odbccasa, $qCasa);
		if ($resCasa == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar los permisos utilizados en la referencia. [BD.CASA].";
			$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
			exit(json_encode($respuesta));
		}
		if(odbc_num_rows($resCasa) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "No se encontraron permisos utilizados en la referencia. [BD.CASA].";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		$nItem = 0;$permisos = '';
		while(odbc_fetch_row($resCasa)){
			$NUM_PERM = odbc_result($resCasa,"NUM_PERM");
			if($nItem != 0){ $permisos .= ',';}
			$permisos .= "'".$NUM_PERM."'";
			$nItem += 1;
		}
		
		/********************************************************************************************************
		 * CONSULTAR POSIBLES PERMISOS APLICADOS
		 ****************************************************************************************************** */
		$consulta = "SELECT numero_permiso as permiso
						FROM fracciones 
						WHERE numero_permiso in (".$permisos.")
						GROUP BY numero_permiso
						ORDER BY numero_permiso";
						error_log($consulta);
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar los posibles permisos aplicados.[MySQL].';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "No se encontro ningun permiso en el sistema web que se pudiera aplicar a las partidas.";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		$permisos = '';$nItem = 0;
		while($row = mysqli_fetch_array($query)){
			$permiso = $row['permiso'];
			if($nItem != 0){ $permisos .= ',';}
			$permisos .= "'".$permiso."'";
			array_push($aPermisos,$permiso);
			$nItem += 1;
		}
		/********************************************************************************************************
		 * CONSULTAR PARTIDAS CON LAS REGLA 8va
		 ****************************************************************************************************** */
		
		$qCasa = "SELECT p.NUM_REFE,p.CONS_FACT,p.CONS_PART,f.NUM_FACT,prv.NOM_PRO,p.FRACCION as FRACCION_CASA,f.NUM_REM,
							CASE WHEN ped.FIR_REME IS NULL THEN '' ELSE ped.FIR_REME END as FIR_REME,
							CASE WHEN ped.FIR_PAGO IS NULL THEN '' ELSE ped.FIR_PAGO END as FIR_PAGO,
							CASE WHEN prm.NUM_PERM IS NULL THEN '' ELSE prm.NUM_PERM END as NUM_PERM,p.DES_MERC,p.CAN_FACT,p.MON_FACT,
							CASE WHEN p.CAS_TLCS IS NULL THEN '' ELSE p.CAS_TLCS END as CAS_TLCS
					FROM SAAIO_FACPAR p
						INNER JOIN SAAIO_PEDIME ped ON
                            p.NUM_REFE = ped.NUM_REFE
						INNER JOIN SAAIO_FACTUR f ON
							p.NUM_REFE = f.NUM_REFE AND
							p.CONS_FACT = f.CONS_FACT
						INNER JOIN CTRAC_PROVED prv ON
							f.CVE_PROV = prv.CVE_PRO
						INNER JOIN SAAIO_PERPAR prm ON
							p.NUM_REFE = prm.NUM_REFE AND
							p.CONS_FACT = prm.CONS_FACT AND 
							p.CONS_PART = prm.CONS_PART AND 
							prm.NUM_PERM IN (".$permisos.")
					WHERE p.NUM_REFE = '".$referencia."' ";
		error_log($qCasa);
		$resCasa = odbc_exec ($odbccasa, $qCasa);
		if ($resCasa == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar la informacion de la referencia. [BD.CASA].";
			$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
			exit(json_encode($respuesta));
		}
		if(odbc_num_rows($resCasa) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "No se encontraron partidas con la regla 8va aplicada para la referencia. [BD.CASA].";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		while(odbc_fetch_row($resCasa)){	
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

			$consulta = "SELECT IFNULL(fh.id_fraccion_hist,'') as id_fraccion_hist,fh.id_fraccion,fh.cantidad,fh.valor,
								f.descripcion,f.fraccion as fraccion_anterior,f.numero_permiso,IFNULL(fh.fecha_registro,'')as fecha_aplicacion
						FROM fracciones_historico fh 
						INNER JOIN fracciones f ON
							fh.id_fraccion = f.id_fraccion
							WHERE fh.num_refe = '".$referencia."' AND fh.cons_fact = '".$sCONS_FACT."' AND fh.cons_par = '".$sCONS_PART."'";

			$query = mysqli_query($cmysqli_s8va, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_s8va);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al verificar partidas con regla 8va aplicada manualmente.';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				exit(json_encode($respuesta));
			}
			$id_fraccion_hist = '';
			while($row = mysqli_fetch_array($query)){
				$id_fraccion_hist = $row['id_fraccion_hist'];
			}
			if($id_fraccion_hist == '' && $sNUM_PERM != ''){
				//Se aplico la Regla 8va Manualmente
				$aPartida = array(
					//Datos CASA
					"consecutivo_factura" => utf8_encode($sCONS_FACT),
					"consecutivo_partida" => utf8_encode($sCONS_PART),
					"numero_factura" => utf8_encode($sNUM_FACT),
					"numero_remesa" => utf8_encode($sNUM_REM),
					"nombre_proveedor" => utf8_encode($sNOM_PRO),
					"fraccion" => utf8_encode($sFRACCION_CASA),
					"descripcion" => utf8_encode($sDESC_CASA),
					"permiso" => utf8_encode($sNUM_PERM),//Si exite se aplico la regla --- Si existe y no en id_fraccion_hist se aplico sin el sistema web
					"cantidad" => utf8_encode($sCANT_FACT),
					"valor" => utf8_encode($sMON_FACT),
					"tlcs" => utf8_encode($sCAS_TLCS)
				);
				array_push($aPartidas,$aPartida);
			}
		}
		if(count($aPartidas) == 0){
			//Se cierra la referencia si no tiene partidas con la regla 8va aplicada manualmente
			/*****************************************************************************/
			//Insertar Referencia en Cerradas
			/*****************************************************************************/
			$consulta = "INSERT INTO referencias (num_refe,fecha_cierre,id_usuario_cierre)
										VALUES ('".$referencia."',
												'".$fecha_registro."',
												'".$id_usuario."')";
												
			$query = mysqli_query($cmysqli_s8va, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_s8va);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al guardar la referencia. [MySQL]';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				exit(json_encode($respuesta));
			}
			$respuesta['Mensaje'] = 'La referencia se ha cerrado correctamente!!.';
		}else{
			
		}
		$respuesta['Codigo'] = 1;
		$respuesta['aPartidas'] = $aPartidas;
		$respuesta['aPermisos'] = $aPermisos;
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	exit(json_encode($respuesta));
}

