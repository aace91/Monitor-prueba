<?php
	include('./../../../connect_dbsql.php');
	include('./../../../connect_casa.php');
	include('./../../../bower_components/PHPMailer/PHPMailerAutoload.php');
	//$fecha_registro = date("Y-m-d H:i:s");
		
	$aPermisos = array();
	$aPermisos_CASA = array();
	$consulta = "SELECT id_registro_factura FROM bodega.permisos_pedimentos_cfg ";
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$mensaje = 'Reporte Diario Permisos Utilizados :: Error al consultar ultima factura procesada. ['.$error.']['.$consulta.']';
		$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
		$to = array('marco@delbravo.com');
		enviamail($asunto,$mensaje,$to);
		exit();
	}
	if(mysqli_num_rows($query) == 0){
		$error=mysqli_error($cmysqli);
		$mensaje = 'Reporte Diario Permisos Utilizados :: No se encontraron registros en la tabla bodega.permisos_pedimentos_cfg. ['.$consulta.']';
		$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
		$to = array('marco@delbravo.com');
		enviamail($asunto,$mensaje,$to);
		exit();
	}
	while($row = mysqli_fetch_array($query)){
		$UltFactura = $row['id_registro_factura'];
	}
	$consulta = "SELECT f.ID_REGISTRO,f.FACTURA_NUMERO,f.REFERENCIA,f.PEDIMENTO, f.NUMCLIENTE,c.cnombre
				FROM bodega.facturas_expo f
					INNER JOIN bodega.cltes_expo c ON
						f.NUMCLIENTE = c.gcliente
				WHERE f.ID_REGISTRO >= ".$UltFactura;
	
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$mensaje = "Reporte Diario Permisos Utilizados :: Error al consultar facturas pendientes. [".$error."] [".$consulta.']';
		$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
		$to = array('marco@delbravo.com');
		enviamail($asunto,$mensaje,$to);
		exit();
	}
	if(mysqli_num_rows($query) == 0){
		$error=mysqli_error($cmysqli);
		$mensaje = 'Reporte Diario Permisos Utilizados :: No se encontraron facturas pendientes para enviar notificaciones.['.$consulta.']';
		$asunto = "Sin facturas pendientes para enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
		$to = array('marco@delbravo.com');
		enviamail($asunto,$mensaje,$to);
		exit();
	}
	while($row = mysqli_fetch_array($query)){
		$Id_Registro = $row['ID_REGISTRO'];
		$sFactura = $row['FACTURA_NUMERO'];
		$sReferencia = $row['REFERENCIA'];
		$sPedimento = $row['PEDIMENTO'];
		$sNoCliente = $row['NUMCLIENTE'];
		$sNombreCliente = $row['cnombre'];
		
		//Saber si es un pedimento con cierre o remesa
		$sQuery = "SELECT CASE WHEN a.FIR_PAGO IS NULL THEN '' ELSE a.FIR_PAGO END AS firma_pago
					FROM SAAIO_PEDIME a
					WHERE a.NUM_REFE = '".$sReferencia."'
					ORDER BY a.NUM_REFE DESC";
		$resp = odbc_exec ($odbccasa, $sQuery) or die(odbc_error());
		if ($resp == false){
			$mensaje = "Reporte Diario Permisos Utilizados :: Error al consultar el pedimento en el sistema CASA.".odbc_error();
			$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
			$to = array('marco@delbravo.com');
			enviamail($asunto,$mensaje,$to);
		}else{
			if(odbc_num_rows($resp) == 0){
				$mensaje = "Reporte Diario Permisos Utilizados :: No se encontro la referencia en el sistema CASA.[Referencia:".$sReferencia."]";
				$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
				$to = array('marco@delbravo.com');
				enviamail($asunto,$mensaje,$to);
			}else{
				while(odbc_fetch_row($resp)){
					$FirmaPago = odbc_result($resp,"firma_pago");
					//Procesar Remesas Si no es Cierre
					if(trim($FirmaPago) == ''){
						$ConsPedi = "SELECT a.NUM_REFE,a.CONS_FACT, a.NUM_PERM, b.NUM_FACT
									FROM SAAIO_PERPAR a 
										INNER JOIN SAAIO_FACTUR b ON
											a.NUM_REFE = b.NUM_REFE AND 
											a.CONS_FACT = b.CONS_FACT
									WHERE a.NUM_REFE = '".trim($sReferencia)."' AND b.NUM_FACT = '".trim($sFactura)."'
									GROUP BY a.NUM_REFE,a.CONS_FACT, a.NUM_PERM, b.NUM_FACT
									ORDER BY a.NUM_REFE DESC";
						$resped = odbc_exec ($odbccasa, $ConsPedi) or die(odbc_error());
						if ($resped == false){
							$mensaje = "Reporte Diario Permisos Utilizados :: Error al permisos en el sistema CASA.".odbc_error();
							$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
							$to = array('marco@delbravo.com');
							enviamail($asunto,$mensaje,$to);
						}else{
							while(odbc_fetch_row($resped)){
								$Permiso = odbc_result($resped,"NUM_PERM");
								$aPermiso = array ($sReferencia,$Permiso,$sNoCliente,$sFactura,$sNombreCliente);
								array_push($aPermisos,$aPermiso);
							}
						}						
					}else{
						//Procesar permisos del cierre de pedimento
						$ConsPedi = "SELECT a.NUM_PERM
										FROM SAAIO_PERMIS a
										WHERE a.NUM_REFE = '".trim($sReferencia)."' 
										GROUP BY a.NUM_PERM";
										
						$resped = odbc_exec ($odbccasa, $ConsPedi) or die(odbc_error());
						if ($resped == false){
							$mensaje = "Reporte Diario Permisos Utilizados :: Error al permisos en el sistema CASA.".odbc_error();
							$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
							$to = array('marco@delbravo.com');
							enviamail($asunto,$mensaje,$to);
						}else{
							while(odbc_fetch_row($resped)){
								$Permiso = odbc_result($resped,"NUM_PERM");
								$aPermiso = array ($sReferencia,$Permiso,$sNoCliente,$sFactura,$sNombreCliente);
								array_push($aPermisos,$aPermiso);
							}
						}
					}
				}
			}
		}
		$UltFactura = $Id_Registro;
	}
	if(count($aPermisos) > 0){		
		for($i = 0; $i < count($aPermisos); $i++){
			$consulta = "SELECT p.numero_permiso,p.fecha_vigencia_ini,p.fecha_vigencia_fin,p.valor_dlls_total,
								p.cantidad_total,p.valor_dlls_delbravo,p.cantidad_delbravo,p.id_cliente,gc.nombre as cliente
							FROM permisos_pedimentos p
								LEFT JOIN geocel_clientes_expo gc ON
									p.id_cliente = gc.f_numcli
							WHERE numero_permiso = '".$aPermisos[$i][1]."'";
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$mensaje = 'Reporte Diario Permisos Utilizados :: Error al consultar pemiso en base de datos. ['.$error.']['.$consulta.']';
				$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
				$to = array('marco@delbravo.com');
				enviamail($asunto,$mensaje,$to);
			} else {
				if(mysqli_num_rows($query) > 0){
					while($row = mysqli_fetch_array($query)){
						$numero_permiso_mysql = $row['numero_permiso'];
						$fecha_vigencia_ini_mysql = $row['fecha_vigencia_ini'];
						$fecha_vigencia_fin_mysql = $row['fecha_vigencia_fin'];
						
						$valor_dlls_total_mysql = $row['valor_dlls_total'];
						$cantidad_total_mysql = $row['cantidad_total'];
						$valor_dlls_delbravo_mysql = $row['valor_dlls_delbravo'];
						$cantidad_delbravo_mysql = $row['cantidad_delbravo'];
						
						
						$id_cliente_mysql = $row['id_cliente'];
						$id_cliente_nom_mysql = $row['cliente'];
						
						//if($aPermisos[$i][2] == 'remesa'){
						$qCasa = "SELECT b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
									FROM (  SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
												FROM SAAIO_PERPAR a
													INNER JOIN SAAIO_PEDIME c ON
														a.NUM_REFE = c.NUM_REFE
												WHERE a.NUM_PERM = '".$aPermisos[$i][1]."' AND c.FIR_PAGO IS NULL AND a.NUM_REFE NOT IN (SELECT NUM_REFEO FROM SAAIO_PEDIME WHERE NUM_REFEO IS NOT NULL)
											UNION ALL
											SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI													
											FROM SAAIO_PERMIS a
                                                INNER JOIN SAAIO_PEDIME c ON
														a.NUM_REFE = c.NUM_REFE
											WHERE a.NUM_PERM = '".$aPermisos[$i][1]."' AND c.FIR_PAGO IS NOT NULL AND a.NUM_REFE NOT IN (SELECT NUM_REFEO FROM SAAIO_PEDIME WHERE NUM_REFEO IS NOT NULL)) b
									GROUP BY (b.NUM_PERM)";
					
						$resped = odbc_exec ($odbccasa, $qCasa) or die(odbc_error());
						if ($resped == false){
							$mensaje = "Reporte Diario Permisos Utilizados :: Error al consultar valores del permiso BD.CASA.".odbc_error();
							$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
							$to = array('marco@delbravo.com');
							enviamail($asunto,$mensaje,$to);
						}else{
							while(odbc_fetch_row($resped)){
								$Permiso = odbc_result($resped,"NUM_PERM");
								$Valor_Dlls = odbc_result($resped,"VAL_DLLS");
								$Cantidad_Tarifa = odbc_result($resped,"CAN_TARI");
								
								//OBTENER REPORTE DE REFERENCIAS Y CANTIDADES UTILIZADAS EN EL PERMISO								
								$aReferencias = referencias_en_permiso_CASA($aPermisos[$i][1]);
								$resp = enviar_notificacion_permiso_email('en_dia',$Permiso,$Valor_Dlls,$Cantidad_Tarifa,$fecha_vigencia_ini_mysql,$fecha_vigencia_fin_mysql,$valor_dlls_total_mysql,$cantidad_total_mysql,$valor_dlls_delbravo_mysql,$cantidad_delbravo_mysql,$aPermisos[$i][2],$id_cliente_nom_mysql,$aReferencias);
							}
						}
					}
				}else{
					/*$qCasa = "SELECT FIRST 1 b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
								FROM (SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
										FROM SAAIO_PERPAR a
											INNER JOIN SAAIO_PEDIME c ON
												a.NUM_REFE = c.NUM_REFE
										WHERE a.NUM_PERM = '".$aPermisos[$i][1]."' AND c.FIR_PAGO IS NULL
										UNION ALL
										SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
										FROM SAAIO_PERMIS a
											INNER JOIN SAAIO_PEDIME c ON
												a.NUM_REFE = c.NUM_REFE
										WHERE a.NUM_PERM = '".$aPermisos[$i][1]."' AND c.FIR_PAGO IS NOT NULL) b
								GROUP BY (b.NUM_PERM)";
				
					$resped = odbc_exec ($odbccasa, $qCasa) or die(odbc_error());
					if ($resped == false){
						$mensaje = "Reporte Diario Permisos Utilizados :: Error al consultar valores del permiso BD.CASA.".odbc_error();
						$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
						$to = array('marco@delbravo.com');
						enviamail($asunto,$mensaje,$to);
					}else{
						while(odbc_fetch_row($resped)){*/
							//Si el permiso no existe en el sistema, enviar notificacion para darlo de alta
							$resp = enviar_notificacion_permiso_email_noexiste($aPermisos[$i][0],$aPermisos[$i][1],$aPermisos[$i][2],$aPermisos[$i][4],$aPermisos[$i][3]);
						/*}
					}*/
				}
				
			}
		}
		//Actualizar ultimo registro de factura para el siguiente reporte
		$consulta = "UPDATE bodega.permisos_pedimentos_cfg SET id_registro_factura = '".$UltFactura."'";
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$mensaje = 'Reporte Diario Permisos Utilizados :: Error al actualizar ultimo registro leido de factura. ['.$error.']['.$consulta.']';
			$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
			$to = array('marco@delbravo.com');
			enviamail($asunto,$mensaje,$to);
		}
	}
	//odbc_close($conn_access_nb);
	
	//*********************************************************************************************************************************************
	//ENVIAR AVISO DE PERMISOS PROXIMOS A VENCERCE (7 DIAS)
	//*********************************************************************************************************************************************
	$consulta = "SELECT  p.numero_permiso,p.id_cliente,gc.nombre as cliente, p.fecha_vigencia_ini, p.fecha_vigencia_fin, p.valor_dlls_total,
								p.cantidad_total,p.valor_dlls_delbravo,p.cantidad_delbravo
				FROM permisos_pedimentos p
					INNER JOIN geocel_clientes_expo gc ON 
						p.id_cliente = gc.f_numcli
				WHERE p.fecha_vigencia_fin = ADDDATE(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'), INTERVAL 7 DAY)";
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$mensaje = 'Reporte Diario Permisos Utilizados :: Error al consultar pemiso en base de datos.[Permisos por vencer en 7 Dias] ['.$error.']['.$consulta.']';
		$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
		$to = array('marco@delbravo.com');
		enviamail($asunto,$mensaje,$to);
	} else {
		while($row = mysqli_fetch_array($query)){
			$numero_permiso_mysql = $row['numero_permiso'];
			$fecha_vigencia_ini_mysql = $row['fecha_vigencia_ini'];
			$fecha_vigencia_fin_mysql = $row['fecha_vigencia_fin'];
			
			$valor_dlls_total_mysql = $row['valor_dlls_total'];
			$cantidad_total_mysql = $row['cantidad_total'];
			$valor_dlls_delbravo_mysql = $row['valor_dlls_delbravo'];
			$cantidad_delbravo_mysql = $row['cantidad_delbravo'];
			
			$id_cliente_mysql = $row['id_cliente'];
			$id_cliente_nom_mysql = $row['cliente'];
			
			//if($aPermisos[$i][2] == 'remesa'){
			$qCasa = "SELECT b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
						FROM (SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
								FROM SAAIO_PERPAR a
									INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
								WHERE a.NUM_PERM = '".$numero_permiso_mysql."' AND c.FIR_PAGO IS NULL
								UNION ALL
								SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
								FROM SAAIO_PERMIS a
									INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
								WHERE a.NUM_PERM = '".$numero_permiso_mysql."' AND c.FIR_PAGO IS NOT NULL) b
						GROUP BY (b.NUM_PERM)";
		
			$resped = odbc_exec ($odbccasa, $qCasa) or die(odbc_error());
			if ($resped == false){
				$mensaje = "Reporte Diario Permisos Utilizados :: Error al consultar valores del permiso BD.CASA.".odbc_error();
				$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
				$to = array('marco@delbravo.com');
				enviamail($asunto,$mensaje,$to);
			}else{
				while(odbc_fetch_row($resped)){
					$Permiso = odbc_result($resped,"NUM_PERM");
					$Valor_Dlls = odbc_result($resped,"VAL_DLLS");
					$Cantidad_Tarifa = odbc_result($resped,"CAN_TARI");
					
					$aReferencias = referencias_en_permiso_CASA($numero_permiso_mysql);
					
					$resp = enviar_notificacion_permiso_email('por_vencer',$Permiso,$Valor_Dlls,$Cantidad_Tarifa,$fecha_vigencia_ini_mysql,$fecha_vigencia_fin_mysql,$valor_dlls_total_mysql,$cantidad_total_mysql,$valor_dlls_delbravo_mysql,$cantidad_delbravo_mysql,$id_cliente_mysql,$id_cliente_nom_mysql,$aReferencias );
				}
			}
		}
	}
	
	function enviar_notificacion_permiso_email($action,$Permiso,$Valor_Casa,$Cantidad_Casa,$Fechaini,$Fechafin,$valor_dlls_total,$cantidad_total,$valor_dlls_delbravo,$cantidad_delbravo,$Id_Cliente,$Nom_Cliente,$Referencias){
		global $cmysqli;
		
		$sHTML = '<table style="border: solid 1px #bbbccc; width: 900px;" cellspacing="0" cellpadding="0">
					<tbody>
						<tr style="background-color: #0073b7; color: #fff;">
							<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
							<td width="10px">&nbsp;</td>
							<td align="center">
								<h1>REPORTE DIARIO DE PERMISOS</h1>
							</td>
							<td width="10px">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4">
								<table width="100%" cellspacing="0" cellpadding="0">
									<tbody>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr><td colspan="8" align="center"><h2>N&uacute;mero de Permiso: '.$Permiso.'</h2></td></tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr>
											<td colspan="8" align="right"><big>Vigencia del: <strong> '.date_format(new DateTime($Fechaini),"d/m/Y").' </strong> al: <strong>'.date_format(new DateTime($Fechafin),"d/m/Y").'</strong></big></td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr>
											<td colspan="8" align="left"><h3>Cliente: '.$Nom_Cliente.'</h3></td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>';
		if($action == 'por_vencer'){
			$sHTML .= '					<tr style="background: #FF7A7A">
											<td colspan="8" align="center"><h2>EL PERMISO ESTA PROXIMO A VENCER EL DIA '.date_format(new DateTime($Fechafin),"d/m/Y").'</h2></td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>';
		}
		$sHTML .= '						<tr>
											<td style="background: #E4FFF3;" colspan="8" align="center"><h3>Totales del permiso</h3></td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr>
											<td colspan="4" align="center"><big>Total en dolares: <strong> $'.number_format ($valor_dlls_total,2).'</strong></big></td>
											<td colspan="4" align="center"><big>Total en kilos: <strong>'.number_format ($cantidad_total,0).'</strong></big></td
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr>
											<td style="background: #E4FFF3;" colspan="8" align="center"><h3>Totales asignados a Del Bravo</h3></td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr>
											<td colspan="4" align="center"><big>Total en dolares: <strong> $'.number_format ($valor_dlls_delbravo,2).'</strong></big></td>
											<td colspan="4" align="center"><big>Total en kilos: <strong>'.number_format ($cantidad_delbravo,0).'</strong></big></td
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>';
		if(($valor_dlls_delbravo - $Valor_Casa) < 0 || ($cantidad_delbravo-$Cantidad_Casa) < 0){
			//ROJO se sobrepasaron las cantidades de los permisos
			$sColorTitulo = '#FF6E6E';
			$sColorBody = '#B80015';
		}else{
			//NORAL
			//Con cantidades estables
			$sColorTitulo = '#6DCAFF';
			$sColorBody = '#0073b7';
		}							
		$sHTML .= '						<tr><td colspan="8">&nbsp;</td></tr>
										<tr style="background-color: '.$sColorTitulo.'; color: #000;" >
											<td colspan="8" align="center"><h3>Saldo del permiso en Del Bravo</h3></td>
										</tr>
										<tr style="background-color: '.$sColorBody.'; color: #fff;"><td colspan="8">&nbsp;</td></tr>
										<tr style="background-color: '.$sColorBody.'; color: #fff;">
											<td colspan="4" align="center"><big>Saldo en dolares: <strong> $'.number_format (($valor_dlls_delbravo - $Valor_Casa),2).'</strong></big></td>
											<td colspan="4" align="center"><big>Saldo en kilos: <strong>'.number_format (($cantidad_delbravo-$Cantidad_Casa),0).'</strong></big></td>
										</tr>
										<tr style="background-color: '.$sColorBody.'; color: #fff;"><td colspan="8">&nbsp;</td></tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr>
											<td style="background: #E4FFF3;" colspan="8" align="left"><h4>Desglose de uso de permiso asignado a Del Bravo</h4></td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>';
		if(count($Referencias) > 0){
			$sHTML .= '					<tr>
											<td align="center" style="background-color: #0073b7; color: #fff; border: solid 1px #333;" ><strong>Pedimento<strong></td>
											<td align="center" style="background-color: #0073b7; color: #fff; border: solid 1px #333;" ><strong>Referencia<strong></td>
											<td align="center" style="background-color: #0073b7; color: #fff; border: solid 1px #333;" ><strong>Importe en dlls. utilizado<strong></td>
											<td align="center" style="background-color: #0073b7; color: #fff; border: solid 1px #333;" ><strong>Saldo en dlls.<strong></td>
											<td align="center" style="background-color: #0073b7; color: #fff; border: solid 1px #333;" ><strong>Importe en kg. utilizado<strong></td>
											<td align="center" style="background-color: #0073b7; color: #fff; border: solid 1px #333;" ><strong>Saldo en kilos<strong></td>
										</tr>
										<tr>
											<td align="left"  colspan="2"><strong>Saldo inicial<strong></td>
											<td align="right"><strong>&nbsp;<strong></td>
											<td align="right" style="background-color: #0073b7; color: #fff; border: solid 1px #333;" ><strong>$'.number_format ($valor_dlls_delbravo,2).'<strong></td>
											<td align="right"><strong>&nbsp;<strong></td>
											<td align="right" style="background-color: #0073b7; color: #fff; border: solid 1px #333;" ><strong>'.number_format ($cantidad_delbravo,0).'<strong></td>
										</tr>
										';
			$SalVal = $valor_dlls_delbravo;
			$SalCant = $cantidad_delbravo;
			for($i = 0; $i<count($Referencias); $i++){
				if($Referencias[$i][2] == 0){
					$ValRef = $Referencias[$i][4];
				}else{
					$ValRef = $Referencias[$i][2];
				}
				if($Referencias[$i][3] == 0){
					$CantRef = $Referencias[$i][5];
				}else{
					$CantRef = $Referencias[$i][3];
				}
				$SalVal = $SalVal - $ValRef;
				$SalCant = $SalCant - $CantRef;
				$sHTML .= '				<tr>
											<td align="center" style="border: solid 1px #333;">'.$Referencias[$i][1].'</td>
											<td align="center" style="border: solid 1px #333;">'.$Referencias[$i][0].'</td>
											<td align="right" style="border: solid 1px #333;">$'.number_format($ValRef,2).'</td>
											<td align="right" style="border: solid 1px #333;">$'.number_format($SalVal,2).'</td>
											<td align="right" style="border: solid 1px #333;">'.number_format($CantRef,0).'</td>	
											<td align="right" style="border: solid 1px #333;">'.number_format($SalCant,0).'</td>
										</tr>';
			}
		}
		$sHTML .= '						<tr>
											<td align="right"  colspan="2"><strong>Suma:<strong></td>
											<td align="right" style="background-color: #0073b7; color: #fff; border: solid 1px #333;" ><strong>$'.number_format ($Valor_Casa,2).'<strong></td>
											<td align="right"><strong>&nbsp;<strong></td>
											<td align="right" style="background-color: #0073b7; color: #fff; border: solid 1px #333;" ><strong>'.number_format ($Cantidad_Casa,0).'<strong></td>
											<td align="right"><strong>&nbsp;<strong></td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr>
											<td style="background: #E4FFF3;" colspan="8" align="left"><strong>NOTA IMPORTANTE:</strong> LA INFORMACION CONTENIDA EN ESTE REPORTE SOLAMENTE ES VALIDA SI DEL BRAVO HA HECHO USO EXCLUSIVO DE ESTE AVISO.</td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>										
										<tr>
											<td colspan="8">
											<p>Este correo fue enviado de forma autom&aacute;tica y no es necesario responder al mismo. &iexcl;Muchas Gracias!.</p>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>';
		//Si hay diferencias en Saldos Detalle(REFERENCIAS) y el Saldo Total (SUMA)
		if( number_format (($valor_dlls_delbravo - $Valor_Casa),2)  != number_format($SalVal,2) || number_format (($cantidad_delbravo-$Cantidad_Casa),0) != number_format($SalCant,0)){
				
			$saldos['sal_gen_val'] = number_format (($valor_dlls_delbravo - $Valor_Casa),2);
			$saldos['sal_gen_cnt'] = number_format (($cantidad_delbravo-$Cantidad_Casa),0);
			$saldos['sal_det_val'] = number_format($SalVal,2);
			$saldos['sal_det_cnt'] = number_format($SalCant,0);
			
			enviar_aviso_diferencias_saldos ($Nom_Cliente,$Permiso,$saldos);
		}
		
		$asunto = 'Reporte diario de permisos. Cliente: '.$Nom_Cliente.' | Permiso: '.$Permiso.'';
		$mensaje = $sHTML;
		
		/*$consulta = "SELECT to1,to2,to3,to4,to5,to6,to7,to8,to9,to10,
							cc1,cc2,cc3,cc4,cc5,cc6,cc7,cc8,cc9,cc10
					FROM geocel_clientes_expo
					WHERE f_numcli = '". $Id_Cliente."'";*/
					
		$consulta = "SELECT email 
						FROM contactos_expo
						WHERE id_catalogo = '". $Id_Cliente."' AND tipo_catalogo = 'CLI'";
						
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$mensaje = 'Reporte Diario Permisos Utilizados :: Error al consultar dentinatarios del aviso. ['.$error.']['.$consulta.']';
			$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
			$to = array('marco@delbravo.com');
			enviamail($asunto,$mensaje,$to);
		} else {
			if(mysqli_num_rows($query) > 0){
				$to = array();
				while($row = mysqli_fetch_array($query)){
					array_push($to,$row['email']);
					/*for ($i = 1; $i <= 10; $i++) {
						$correo=$row['to'.$i];
						if($correo!='' or $correo!=NULL){
							array_push($to,$correo);
						}
					}
					for ($i = 1; $i <= 10; $i++) {
						$correo=$row['cc'.$i];
						if($correo!='' or $correo!=NULL){
							array_push($to,$correo);
						}
					}*/
				}
				$RespEmail = enviamail($asunto,$mensaje,$to);
				
				
				
			}else{
				enviar_aviso_cliente_noexiste($Id_Cliente,$Referencias[0][0],$Permiso);
			}
		}
	}
	
	function enviar_notificacion_permiso_email_noexiste($Referencia,$Permiso,$Id_Cliente,$Cliente,$Numero_Factura){
		global $cmysqli;
		$sHTML = '<table style="border: solid 1px #bbbccc; width: 700px;" cellspacing="0" cellpadding="0">
					<tbody>
						<tr style="background-color: #0073b7; color: #fff;">
							<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
							<td width="10">&nbsp;</td>
							<td align="center">
								<h1>EL PERMISO NO EXISTE</h1>
							</td>
							<td width="10px">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4">
								<table width="100%" cellspacing="0" cellpadding="0">
									<tbody>
										<tr><td colspan="2">&nbsp;</td></tr>
										<tr><td colspan="2" align="left"><h3>N&uacute;mero de Permiso: '.$Permiso.'</h3></td></tr>
										<tr><td colspan="2">&nbsp;</td></tr>
										<tr><td colspan="2" align="left"><h3>Referencia: '.$Referencia.'</h3></td></tr>
										<tr><td colspan="2">&nbsp;</td></tr>
										<tr><td colspan="2" align="left"><h3>Cliente: '.$Cliente.'</h3></td></tr>
										<tr><td colspan="2">&nbsp;</td></tr>
										<tr><td colspan="2" align="left"><h3>N&uacute;mero de Factura: '.$Numero_Factura.'</h3></td></tr>
										<tr><td colspan="2">&nbsp;</td></tr>
										<tr style="background: #EFE480">
											<td colspan="2" align="center"><h3>Es necesario agrega la informaci&oacute;n del permiso en el <a href="https://delbravoweb.com/monitor">[Monitor Del Bravo/Permisos.]</a> Para notificar el estado actual del mismo.</h3></td>
										</tr>
										<tr><td colspan="2">&nbsp;</td></tr>
										<tr style="background-color: #0073b7; color: #fff;"><td colspan="2">&nbsp;</td></tr>
										<tr>
											<td colspan="2">
											<p>Este correo fue enviado de forma autom&aacute;tica y no es necesario responder al mismo. &iexcl;Muchas Gracias!.</p>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>';
				
		$asunto = 'El Permiso No Existe en el Monitor. Permiso: '.$Permiso.'';
		$mensaje = $sHTML;
		
		/*$consulta = "SELECT cc1,cc2,cc3,cc4,cc5,cc6,cc7,cc8,cc9,cc10
					FROM geocel_clientes_expo
					WHERE f_numcli = '". $Id_Cliente."'";*/
					
		$consulta = "SELECT email 
						FROM contactos_expo
						WHERE id_catalogo = '". $Id_Cliente."' AND tipo_catalogo = 'CLI' AND tipo_contacto='EJE'";
					
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$mensaje = 'Reporte Diario Permisos Utilizados :: Error al consultar dentinatarios del aviso. [No existe el permiso en el monitor]['.$error.']['.$consulta.']';
			$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
			$to = array('marco@delbravo.com');
			enviamail($asunto,$mensaje,$to);
		} else {
			if(mysqli_num_rows($query) > 0){
				$to = array();
				while($row = mysqli_fetch_array($query)){
					array_push($to,$row['email']);
					/*for ($i = 1; $i <= 10; $i++) {
						$correo=$row['to'.$i];
						if($correo!='' or $correo!=NULL){
							array_push($to,$correo);
						}
					}
					for ($i = 1; $i <= 10; $i++) {
						$correo=$row['cc'.$i];
						if($correo!='' or $correo!=NULL){
							array_push($to,$correo);
						}
					}*/
				}
				$RespEmail = enviamail($asunto,$mensaje,$to);
			}else{
				enviar_aviso_cliente_noexiste($Id_Cliente,$Referencia,$Permiso);
			}
		}
	}
	
	function enviamail($asunto,$mensaje,$to){
	
		$mailserver = 'mail.delbravo.com';
		$portmailserver = '587';
		$sender = 'avisosautomaticos@delbravo.com';
		$pass = 'aviaut01';
		
		$mail = new PHPMailer();
		//Luego tenemos que iniciar la validación por SMTP:
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		//$mail->SMTPSecure = "tls";
		$mail->Host = $mailserver; // SMTP a utilizar. Por ej. smtp.elserver.com
		$mail->Username = $sender; // Correo completo a utilizar
		$mail->Password = $pass; // Contraseña
		$mail->Port = $portmailserver; // Puerto a utilizar
		//Con estas pocas líneas iniciamos una conexión con el SMTP. Lo que ahora deberíamos hacer, es configurar el mensaje a enviar, el //From, etc.
		$mail->From = $sender; // Desde donde enviamos (Para mostrar)
		$mail->FromName = $sender;

		//Estas dos líneas, cumplirían la función de encabezado (En mail() usado de esta forma: “From: Nombre <correo@dominio.com>”) de //correo.
		/*if (count($to)>0){
			foreach($to as $t){
				// Esta es la dirección a donde enviamos
				$mail->AddAddress($t);
			}
		}*/
		//if (count($bcc)>0){
			//foreach($bcc as $b){
				// Esta es la dirección a donde enviamos
		$mail->AddBcc('marco@delbravo.com');
		//$mail->AddBcc('abisaicruz@delbravo.com');
		
		$mail->IsHTML(true); // El correo se envía como HTML
		$mail->Subject = $asunto; // Este es el titulo del email.
		$mail->Body = $mensaje; // Mensaje a enviar
		$exito = $mail->Send(); // Envía el correo.

		//También podríamos agregar simples verificaciones para saber si se envió:
		if(!$exito){
			error_log('Reporte Diario Permisos Utilizados :: Error al enviar el correo electronico. ['.$mail->ErrorInfo.']');
		}
		return true;
	}
	
	function referencias_en_permiso_CASA($nPermiso){		
		include('./../../../connect_dbsql.php');
		include('./../../../connect_casa.php');
		
		/*Relacion de rermisos en cada cierre de pedimento en comparativo con el total en las remesas*/
		$aReferencias = array();
		
		//Hacer un arreglo donde se muestre el comparativo total de los pedimentos y las remesas. 
		
		//TODO
		/*$qCasa = "SELECT a.NUM_REFE AS REF_A,p.NUM_PEDI,a.NUM_PERM AS PER_A, SUM(a.VAL_CDLL) AS VAL_A, SUM(a.CAN_TARI) AS CAN_A,
							CASE WHEN SUM(b.VAL_CDLL) IS NULL THEN 0 ELSE SUM(b.VAL_CDLL) END AS VAL_B, 
							CASE WHEN SUM(b.CAN_TARI) IS NULL THEN 0 ELSE SUM(b.CAN_TARI) END AS CAN_B													
					FROM SAAIO_PERMIS a
						INNER JOIN SAAIO_PEDIME p ON
							a.NUM_REFE = p.NUM_REFE
						LEFT JOIN SAAIO_PERPAR b ON
							a.NUM_REFE = b.NUM_REFE AND
							b.NUM_PERM = '".$nPermiso."'
					WHERE a.NUM_PERM = '".$nPermiso."'
					GROUP BY a.NUM_REFE,p.NUM_PEDI,a.NUM_PERM
					ORDER BY a.NUM_REFE";*/
					
		$qCasa = "SELECT a.NUM_REFE AS REF_A,p.NUM_PEDI,a.NUM_PERM AS PER_A, SUM(a.VAL_CDLL) AS VAL_A, SUM(a.CAN_TARI) AS CAN_A
					FROM SAAIO_PERMIS a
						INNER JOIN SAAIO_PEDIME p ON
							a.NUM_REFE = p.NUM_REFE
                    WHERE a.NUM_PERM = '".$nPermiso."' AND p.FIR_PAGO IS NOT NULL AND a.NUM_REFE NOT IN (SELECT NUM_REFEO FROM SAAIO_PEDIME WHERE NUM_REFEO IS NOT NULL)
					GROUP BY a.NUM_REFE,p.NUM_PEDI,a.NUM_PERM
					ORDER BY a.NUM_REFE";
		
		$resped = odbc_exec ($odbccasa, $qCasa) or die(odbc_error());
		if ($resped == false){
			$mensaje = "Reporte Diario Permisos Utilizados :: Error al consultar valores del permiso BD.CASA.".odbc_error();
			$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
			$to = array('marco@delbravo.com');
			enviamail($asunto,$mensaje,$to);
		}else{
			while(odbc_fetch_row($resped)){
				$REF_CIE = odbc_result($resped,"REF_A");
				$PED_CIE = odbc_result($resped,"NUM_PEDI");
				
				$VAL_CIE = odbc_result($resped,"VAL_A");
				$CAN_CIE = odbc_result($resped,"CAN_A");
				
				/*$VAL_PAR = odbc_result($resped,"VAL_B");
				$CAN_PAR = odbc_result($resped,"CAN_B");*/
				
				$Referencia = array($REF_CIE,$PED_CIE,$VAL_CIE,$CAN_CIE);//,$VAL_PAR,$CAN_PAR);
				array_push($aReferencias,$Referencia);
				
			}
		}
		//PERMISOS PARTIDAS QUE NO ESTEN EN PERMISOS PEDIMENTO PAGADO
		/*$qCasa = "SELECT a.NUM_REFE AS REF_A,p.NUM_PEDI,a.NUM_PERM AS PER_A, SUM(a.VAL_CDLL) AS VAL_A, SUM(a.CAN_TARI) AS CAN_A,
							CASE WHEN SUM(b.VAL_CDLL) IS NULL THEN 0 ELSE SUM(b.VAL_CDLL) END AS VAL_B,
							CASE WHEN SUM(b.CAN_TARI) IS NULL THEN 0 ELSE SUM(b.CAN_TARI) END AS CAN_B
					FROM SAAIO_PERPAR a
						INNER JOIN SAAIO_PEDIME p ON
							a.NUM_REFE = p.NUM_REFE
						LEFT JOIN SAAIO_PERMIS b ON
							a.NUM_REFE = b.NUM_REFE AND
							b.NUM_PERM = '".$nPermiso."'
					WHERE a.NUM_PERM = '".$nPermiso."'
					GROUP BY a.NUM_REFE,p.NUM_PEDI,a.NUM_PERM
					ORDER BY a.NUM_REFE";*/
					
		$qCasa = "SELECT a.NUM_REFE AS REF_A,p.NUM_PEDI,a.NUM_PERM AS PER_A, SUM(a.VAL_CDLL) AS VAL_A, SUM(a.CAN_TARI) AS CAN_A
					FROM SAAIO_PERPAR a
						INNER JOIN SAAIO_PEDIME p ON
							a.NUM_REFE = p.NUM_REFE
					WHERE a.NUM_PERM = '".$nPermiso."' AND p.FIR_PAGO IS NULL  AND a.NUM_REFE NOT IN (SELECT NUM_REFEO FROM SAAIO_PEDIME WHERE NUM_REFEO IS NOT NULL)
					GROUP BY a.NUM_REFE,p.NUM_PEDI,a.NUM_PERM
					ORDER BY a.NUM_REFE";
	
		$resped = odbc_exec ($odbccasa, $qCasa) or die(odbc_error());
		if ($resped == false){
			$mensaje = "Reporte Diario Permisos Utilizados :: Error al consultar valores del permiso BD.CASA.".odbc_error();
			$asunto = "Error al enviar reporte de permisos [monitor/panel/ajax/aviso_automatico_permisos/enviar_reporte_diario_permisos.php]";
			$to = array('marco@delbravo.com');
			enviamail($asunto,$mensaje,$to);
		}else{
			while(odbc_fetch_row($resped)){
				//Referencia y permiso B porque siempre trae dato
				$REF_PAR = odbc_result($resped,"REF_A");
				$PED_PAR = odbc_result($resped,"NUM_PEDI");
				
				$VAL_PAR = odbc_result($resped,"VAL_A");
				$CAN_PAR = odbc_result($resped,"CAN_A");
				
				/*$VAL_CIE = odbc_result($resped,"VAL_B");
				$CAN_CIE = odbc_result($resped,"CAN_B");*/
				$Referencia = array($REF_PAR,$PED_PAR,$VAL_PAR,$CAN_PAR);//($REF_PAR,$PED_PAR,$VAL_CIE,$CAN_CIE,$VAL_PAR,$CAN_PAR);
				if(COUNT($aReferencias) > 0){
					$bexist = false;
					for($i=0; $i<count($aReferencias); $i++){
						if($Referencia[0] == $aReferencias[$i][0]){
							$bexist = true;
							break;
						}
					}
					if(!$bexist){
						array_push($aReferencias,$Referencia);
					}
				}else{
					array_push($aReferencias,$Referencia);
				}											
			}
		}
		
		return $aReferencias;
	}
	
	function enviar_aviso_cliente_noexiste($sCliente,$sReferencia,$sPermiso){
		$mensaje = '<table style="border: solid 1px #bbbccc; width: 900px;" cellspacing="0" cellpadding="0">
					<tbody>
						<tr style="background-color: #EFE480; color: #fff;">
							<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
							<td width="10px">&nbsp;</td>
							<td align="center">
								<h1>REPORTE DIARIO DE PERMISOS</h1>
							</td>
							<td width="10px">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4">
								<table width="100%" cellspacing="0" cellpadding="0">
									<tbody>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr><td colspan="8" align="center"><h3>El cliente con el id:'.$sCliente.' no existe en la tabla de Geocel_CLientes y no es posible enviarle notificaciones del permiso '.$sPermiso.'. [Referencia:'.$sReferencia.']</h3></td></tr>
										<tr><td colspan="8">&nbsp;</td></tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>';
		$asunto = "Avios Diario de Permisos :: El cliente no existe en geocel_clientes.";
		$to = array();
		array_push($to,'marco@delbravo.com');
		array_push($to,'abisaicruz@delbravo.com');
		$RespEmail = enviamail($asunto,$mensaje,$to);
	}
	
	function enviar_aviso_diferencias_saldos($sCliente,$sPermiso,$saldos){
		$mensaje = '<table style="border: solid 1px #bbbccc; width: 900px;" cellspacing="0" cellpadding="0">
					<tbody>
						<tr style="background-color: #CA3E3E; color: #fff;">
							<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
							<td width="10px">&nbsp;</td>
							<td align="center">
								<h1>DIFERENCIAS EN SALDOS</h1>
							</td>
							<td width="10px">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4">
								<table width="100%" cellspacing="0" cellpadding="0">
									<tbody>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr><td colspan="8" align="left"><h3>Cliente: '.$sCliente.'</h3></td></tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr><td colspan="8" align="left"><h3>Permiso: '.$sPermiso.'</h3></td></tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr><td colspan="8" align="left"><h5>Es necesario revisar a detalle los valores del permiso '.$sPermiso.', existen diferencias en el saldo total y el saldo a detalle de las referencias.</h5></td></tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr>
											<td colspan="2" align="left"><h3>&nbsp</h3></td>
											<td colspan="3" align="center"><h3>VALOR</h3></td>
											<td colspan="3" align="center"><h3>CANTIDAD</h3></td>
										</tr>
										<tr>
											<td colspan="2" align="right"><h3>Saldo Total</h3></td>
											<td colspan="3" align="left"><h3>&nbsp;&nbsp;'.$saldos['sal_gen_val'].'</h3></td>
											<td colspan="3" align="left"><h3>&nbsp;&nbsp;'.$saldos['sal_gen_cnt'].'</h3></td>
										</tr>
										<tr>
											<td colspan="2" align="right"><h3>Saldo Detalle</h3></td>
											<td colspan="3" align="center"><h3>'.$saldos['sal_det_val'].'</h3></td>
											<td colspan="3" align="center"><h3>'.$saldos['sal_det_cnt'].'</h3></td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>';
							
		$asunto = "Avios Diario de Permisos :: Diferencia en saldos del permiso ".$sPermiso;
		$to = array();
		array_push($to,'marco@delbravo.com');
		array_push($to,'abisaicruz@delbravo.com');
		$RespEmail = enviamail($asunto,$mensaje,$to);
	}
	
	
	