<?php
include('./../../../bower_components/PHPMailer/PHPMailerAutoload.php');

/* Solo casos de solicitud de sercivio prioritario */
function enviar_correo_nueva_solicitud($IdSolicitud, $pdo_mysql_sconn, $pdo_accss_sconn, $mysqluser, $mysqlpass, $bDebug, $App) {
	try {
		$cnn_mysql= new PDO($pdo_mysql_sconn, $mysqluser, $mysqlpass);
		$cnn_mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$consulta = "SELECT te.id_solicitud,te.referencia,te.motivo,te.fecha_registro,cli.Nom as cliente,
			                fle.fleNombre as linea_entrego,
	                        IF(te.usuario_tipo = '1', u.usunombre, cli.Nom) as usuario_solicito,
                            IF(te.usuario_tipo = '1', u.usuEmail, NULL) as usuario_email,
		   					bod.bodcli
					 FROM tiempo_extra te INNER JOIN 
						  tblbod bod ON te.referencia = bod.bodReferencia INNER JOIN
						  tblflet fle ON bod.bodfle = fle.fleClave INNER JOIN
						  clientes cli ON bod.bodcli = cli.Cliente_id INNER JOIN
						  tblusua u ON te.usuario_id = u.Usuario_id
					 WHERE te.id_solicitud = ".$IdSolicitud;
		
		$query = $cnn_mysql->query($consulta)->fetchAll();
		if(count($query) > 0){
			foreach ($query as $row) {
				$Referencia = $row['referencia'];
				$Motivo = $row['motivo'];
				$Fecha = date_create($row['fecha_registro']);
				$idCliente = $row['bodcli'];
				$Cliente = $row['cliente'];
				$LineaEntrego = $row['linea_entrego'];
				$Usuario = $row['usuario_solicito'];
				$UsuarioEmail = $row['usuario_email'];

				$respuesta['Referencia'] = $Referencia;
			}

			//$conn_acc = new PDO($pdo_accss_sconn);
			$conn_acc = new PDO("odbc:bodegamysql", "", "");
			$conn_acc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
			
			$consulta = "	SELECT to1,to2,to3,to4,to5,to6,to7,to8,to9,to10,
								   cc1,cc2,cc3,cc4,cc5,cc6,cc7,cc8,cc9,cc10
							FROM geocel_clientes
							WHERE f_numcli = ". $idCliente;

			$resp = $conn_acc->query($consulta)->fetchAll();
			if(count($resp) > 0){
				$to_Client = array();
				$to_Ejecut = array();	
				$to_Bodega = array();
				
				/*Si lo crearon en bodega usamos avisosbodega@delbravo.com*/
				/*if (is_null($UsuarioEmail) == false) {
					array_push($to_Ejecut, $UsuarioEmail);
				}*/
				
				foreach ($resp as $row) {
					for ($i = 1; $i <= 10; $i++) {
						$correo = $row['to'.$i];
						if($correo != '' or $correo != NULL){
							array_push($to_Client, $correo);
						}
						$correo=$row['cc'.$i];
						if($correo!='' or $correo!=NULL){
							if ($correo != 'avisosbodega@delbravo.com') {
								array_push($to_Ejecut, $correo);
							}
						}
					}
				}
				
				if ($bDebug) {
					error_log('DEBUG[Servicio Prioritario] Clientes Emails: '. json_encode($to_Client));
					error_log('DEBUG[Servicio Prioritario] Ejecutivo Emails: '. json_encode($to_Ejecut));
					$to_Client = array();
					$to_Ejecut = array();	
					array_push($to_Bodega, 'jcdelacruz@delbravo.com');
					array_push($to_Client, 'jcdelacruz@delbravo.com');
					array_push($to_Ejecut, 'jcdelacruz@delbravo.com');
				} else {
					array_push($to_Bodega, 'avisosbodega@delbravo.com');
				}

				$asunto = 'Solicitud De Servicio Prioritario. [Del Bravo Forwarding] ['.$Referencia.']['.$Cliente.']';
				$sHTML_Client = formato_correo_nueva_solicitud('Client', $IdSolicitud, $Referencia, $Motivo, $Fecha, $LineaEntrego, $Cliente, $Usuario, $bDebug, $App);
				$sHTML_Ejecut = formato_correo_nueva_solicitud('Ejecut', $IdSolicitud, $Referencia, $Motivo, $Fecha, $LineaEntrego, $Cliente, $Usuario, $bDebug, $App);
				$sHTML_Bodega = formato_correo_nueva_solicitud('Bodega', $IdSolicitud, $Referencia, $Motivo, $Fecha, $LineaEntrego, $Cliente, $Usuario, $bDebug, $App);
			
				$RespEmail = enviamail($asunto, $sHTML_Client, $to_Client);
				if($RespEmail['Codigo'] != 1){
					$respuesta['Codigo'] = -1;
					$respuesta['Error'] = $RespEmail['Mensaje'];
				} else {
					$RespEmail = enviamail($asunto, $sHTML_Ejecut, $to_Ejecut);

					if($RespEmail['Codigo'] != 1){
						$respuesta['Codigo'] = -1;
						$respuesta['Error'] = $RespEmail['Mensaje'];
					} else {
						$RespEmail = enviamail($asunto, $sHTML_Bodega, $to_Bodega);

						if($RespEmail['Codigo'] != 1){
							$respuesta['Codigo'] = -1;
							$respuesta['Error'] = $RespEmail['Mensaje'];
						} else {
							$respuesta['Codigo'] = 1;
							$respuesta['Mensaje'] = 'La solicitud de servicio prioritario, fue enviada correctamente por correo electrónico.';
						}
					}
				}
			} else {
				$respuesta['Codigo'] = -1;
				$respuesta['Error'] = 'El cliente no cuenta con destinatario para su notificacion.[Id:'.$idCliente.']';
			}
		}else{
			$respuesta['Codigo'] = -1;
			$respuesta['Error']='Error al enviar correo al cliente.[No se enconto informacion de la solicitud '.$IdSolicitud.']';
		}
		$cnn_mysql = null;

	}  catch (PDOException $e) {
		$respuesta['Codigo'] = -1;
		$respuesta['Error'] = 'Error al enviar notificacion por correo. ['.$e->getMessage().']';
	}
	return $respuesta;
}

/* Solo para casos rechazan o autorizan una solicitud */
/* $App = Ejecutivo|Cliente|Bodega para formatear el mensaje  */
function enviar_correo_notificacion($IdSolicitud, $pdo_mysql_sconn, $pdo_accss_sconn, $mysqluser, $mysqlpass, $bDebug, $App) { 
	try {
		$respuesta['Codigo'] = 1;

		$cnn_mysql= new PDO($pdo_mysql_sconn, $mysqluser, $mysqlpass);
		$cnn_mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$consulta = "SELECT te.id_solicitud,te.referencia,te.motivo,te.fecha_registro,cli.Nom as cliente,
							fle.fleNombre as linea_entrego,
							IF(te.usuario_tipo = '1', u.usunombre, cli.Nom) as usuario_solicito,
							IF(te.usuario_tipo = '1', u.usuEmail, NULL) as usuario_email,
							bod.bodcli,
							te.fecha_autorizo_bodega,
							te.fecha_autorizo_cliente,
							te.fecha_autorizo_ejecutivo,
							te.fecha_rechazo
					FROM tiempo_extra te INNER JOIN 
						tblbod bod ON te.referencia = bod.bodReferencia INNER JOIN
						tblflet fle ON bod.bodfle = fle.fleClave INNER JOIN
						clientes cli ON bod.bodcli = cli.Cliente_id INNER JOIN
						tblusua u ON te.usuario_id = u.Usuario_id
					WHERE te.id_solicitud = ".$IdSolicitud;

		$query = $cnn_mysql->query($consulta)->fetchAll();
		if(count($query) > 0){
			foreach ($query as $row) {
				$Referencia = $row['referencia'];
				$Motivo = $row['motivo'];
				$Fecha = date_create($row['fecha_registro']);
				$idCliente = $row['bodcli'];
				$Cliente = $row['cliente'];
				$LineaEntrego = $row['linea_entrego'];
				$Usuario = $row['usuario_solicito'];
				$UsuarioEmail = $row['usuario_email'];

				$FechaAutorizadoBodega = ((is_null($row['fecha_autorizo_bodega'])? '': date_create($row['fecha_autorizo_bodega'])));
				$FechaAutorizadoCliente = ((is_null($row['fecha_autorizo_cliente'])? '': date_create($row['fecha_autorizo_cliente'])));
				$FechaAutorizadoEjecutivo = ((is_null($row['fecha_autorizo_ejecutivo'])? '': date_create($row['fecha_autorizo_ejecutivo'])));
				$FechaRechazo = ((is_null($row['fecha_rechazo'])? '': date_create($row['fecha_rechazo'])));

				$respuesta['Referencia'] = $Referencia;
			}

			//$conn_acc = new PDO($pdo_accss_sconn);
			$conn_acc = new PDO("odbc:bodegamysql", "", "");
			$conn_acc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
			
			$consulta = "	SELECT to1,to2,to3,to4,to5,to6,to7,to8,to9,to10,
								   cc1,cc2,cc3,cc4,cc5,cc6,cc7,cc8,cc9,cc10
							FROM geocel_clientes
							WHERE f_numcli = ". $idCliente;

			$resp = $conn_acc->query($consulta)->fetchAll();
			if(count($resp) > 0){
				$to_Client = array();
				$to_Ejecut = array();	
				$to_Bodega = array();
				if ($bDebug) {
					array_push($to_Bodega, 'jcdelacruz@delbravo.com');
				} else {
					array_push($to_Bodega, 'avisosbodega@delbravo.com');
				}

				/*Si lo crearon en bodega usamos avisosbodega@delbravo.com*/
				/*if (is_null($UsuarioEmail) == false) {
					array_push($to_Ejecut, $UsuarioEmail);
				}*/
				
				foreach ($resp as $row) {
					for ($i = 1; $i <= 10; $i++) {
						$correo = $row['to'.$i];
						if($correo != '' or $correo != NULL){
							array_push($to_Client, $correo);
						}
						$correo=$row['cc'.$i];
						if($correo!='' or $correo!=NULL){
							if ($correo != 'avisosbodega@delbravo.com') {
								array_push($to_Ejecut, $correo);
							}
						}
					}
				}
		
				$asunto = 'Solicitud De Servicio Prioritario '.($FechaRechazo == '' ? 'Autorizado' : 'Rechazado').'. [Del Bravo] ['.$Referencia.']['.$Cliente.']';
				$sHTML_Client = formato_correo_notificacion('Client', $IdSolicitud, $Referencia, $Motivo, $Fecha, $LineaEntrego, $Cliente, $Usuario, $bDebug, $App, $FechaAutorizadoBodega, $FechaAutorizadoCliente, $FechaAutorizadoEjecutivo, $FechaRechazo);
				$sHTML_Ejecut = formato_correo_notificacion('Ejecut', $IdSolicitud, $Referencia, $Motivo, $Fecha, $LineaEntrego, $Cliente, $Usuario, $bDebug, $App, $FechaAutorizadoBodega, $FechaAutorizadoCliente, $FechaAutorizadoEjecutivo, $FechaRechazo);
				$sHTML_Bodega = formato_correo_notificacion('Bodega', $IdSolicitud, $Referencia, $Motivo, $Fecha, $LineaEntrego, $Cliente, $Usuario, $bDebug, $App, $FechaAutorizadoBodega, $FechaAutorizadoCliente, $FechaAutorizadoEjecutivo, $FechaRechazo);
			
				$RespEmail = enviamail($asunto, $sHTML_Client, $to_Client);
				if($RespEmail['Codigo'] != 1){
					$respuesta['Codigo'] = -1;
					$respuesta['Error'] = $RespEmail['Mensaje'];
				} else {
					$RespEmail = enviamail($asunto, $sHTML_Ejecut, $to_Ejecut);

					if($RespEmail['Codigo'] != 1){
						$respuesta['Codigo'] = -1;
						$respuesta['Error'] = $RespEmail['Mensaje'];
					} else {
						$RespEmail = enviamail($asunto, $sHTML_Bodega, $to_Bodega);

						if($RespEmail['Codigo'] != 1){
							$respuesta['Codigo'] = -1;
							$respuesta['Error'] = $RespEmail['Mensaje'];
						} else {
							$respuesta['Codigo'] = 1;
							$respuesta['Mensaje'] = 'La solicitud de servicio prioritario, fue enviada correctamente por correo electrónico.';
						}
					}
				}
			} else {
				$respuesta['Codigo'] = -1;
				$respuesta['Error'] = 'El cliente no cuenta con destinatario para su notificacion.[Id:'.$idCliente.']';
			}
		}
	}  catch (PDOException $e) {
		$respuesta['Codigo'] = -1;
		$respuesta['Error'] = 'Error al enviar notificacion por correo. ['.$e->getMessage().']';
	}
	return $respuesta;
}

/*****************************************************************************************/
/* FORMATOS EMAIL */
/*****************************************************************************************/

function formato_correo_nueva_solicitud($Tipo, $IdSolicitud, $Referencia, $Motivo, $Fecha, $LineaEntrego, $Cliente, $Usuario, $bDebug, $App) {
	$sHTML = '
	<table style="border: solid 1px #bbbccc; width: 700px;" cellspacing="0" cellpadding="0">
		<tbody>
			<tr style="background-color: #0073b7; color: #fff;">
				<td style="background-color: #fff;" width="100px">
					<img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" />
				</td>
				<td width="10">&nbsp;</td>
				<td align="center"
					><h1>Del Bravo Forwarding Inc.</h1>
				</td>
				<td width="10px">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="4">
					<table width="100%" cellspacing="0" cellpadding="0">
						<tbody>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3" align="center">
									<h2>Solicitud De Servicio Prioritario</h2>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td align="left">
									<big>
										<strong>Referencia:</strong>
										<strong>
											<span style="text-decoration: underline;">'.$Referencia.'</span>
										</strong>
									</big>
								</td>
								<td align="left">&nbsp;</td>
								<td align="left">
									<strong>Fecha:</strong> '.date_format($Fecha, 'd/m/y').'
								</td>
							</tr>
							<tr>
								<td align="left" colspan="3">
									<big>
										<strong>Cliente:</strong>
										<strong>
											<span style="text-decoration: underline;">'.$Cliente.'</span>
										</strong>
									</big>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF; border: 1px solid #bbbccc;" colspan="3" align="center">
									<strong>MOTIVO</strong>
								</td>
							</tr>
							<tr>
								<td style="border: 1px solid #bbbccc;" colspan="3">'.$Motivo.'</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Solicit&oacute;:</strong> '.$Usuario.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Linea que entreg&oacute;:</strong> '.$LineaEntrego.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #E4FFF3;" colspan="3" align="center">
									<h3>';

	if($Tipo == 'Ejecut'){
		if ($App != 'Ejecutivo') {
			$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/ajax/tiempo_extra/ajax_autorizar_tiempo_extra.php?isd='.$IdSolicitud.'&tp=E" style="color: #3c763d;">[Autorizar]</a>';
			$sHTML .= '				   		<br/><br/>';
			$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/tiempos_extra_rechazar.php?isd='.$IdSolicitud.'&tp=E&usr='.$Cliente.'" style="color: #ff5a5a;">[Rechazar Solicitud]</a>';
		}
	} else if($Tipo == 'Bodega'){ 
		if ($App != 'Bodega') { 
			$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/ajax/tiempo_extra/ajax_autorizar_tiempo_extra.php?isd='.$IdSolicitud.'&tp=B" style="color: #3c763d;">[Autorizar]</a>';
			$sHTML .= '				   		<br/><br/>';
			$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/tiempos_extra_rechazar.php?isd='.$IdSolicitud.'&tp=B&usr='.$Cliente.'" style="color: #ff5a5a;">[Rechazar Solicitud]</a>';
		}
	} else { //Client
		if ($App != 'Cliente') { 
			$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/ajax/tiempo_extra/ajax_autorizar_tiempo_extra.php?isd='.$IdSolicitud.'&tp=C" style="color: #3c763d;">[Autorizar]</a>';
			$sHTML .= '				   		<br/><br/>';
			$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/tiempos_extra_rechazar.php?isd='.$IdSolicitud.'&tp=C&usr='.$Cliente.'" style="color: #ff5a5a;">[Rechazar Solicitud]</a>';
		}
	}
	$sHTML .=						'</h3>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3">
									<p>Es necesario responder a la brevedad a esta solicitud, todo esto para brindarle un servicio mucho m&aacute;s r&aacute;pido y de mejor calidad. Muchas gracias y quedamos a la espera de su pronta respuesta en el link antes mencionado.</p>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>';

	return $sHTML;
}

function formato_correo_notificacion($Tipo, $IdSolicitud, $Referencia, $Motivo, $Fecha, $LineaEntrego, $Cliente, $Usuario, $bDebug, $App, $FechaAutorizadoBodega, $FechaAutorizadoCliente, $FechaAutorizadoEjecutivo, $FechaRechazo) {
	$sHTML = '
	<table style="border: solid 1px #bbbccc; width: 700px;" cellspacing="0" cellpadding="0">
		<tbody>
			<tr style="background-color: #0073b7; color: #fff;">
				<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
				<td width="10">&nbsp;</td>
				<td align="center"><h1>Del Bravo Forwarding Inc.</h1></td>
				<td width="10px">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="4">
					<table width="100%" cellspacing="0" cellpadding="0">
						<tbody>
							<tr><td colspan="3">&nbsp;</td></tr>
							<tr>';
	if($FechaRechazo == ''){
		if ($FechaAutorizadoBodega != '' && $FechaAutorizadoCliente != '' && $FechaAutorizadoEjecutivo != '') {
			$sHTML .= '				<td colspan="3" align="center" style="background-color:#5FFF9C; color:#333;"><h2>Servicio Prioritario AUTORIZADO</h2></td>';			
		} else {
			$sHTML .= '				<td colspan="3" align="center" style="background-color:#FFBF3B; color:#333;"><h2>Servicio Prioritario PENDIENTE</h2></td>';		
		}
	} else {
		$sHTML .= '				<td colspan="3" align="center" style="background-color:#FF9091; color:#333;"><h2>Servicio Prioritario RECHAZADO</h2></td>';
	}
	$sHTML .= '				</tr>
							<tr><td colspan="3">&nbsp;</td></tr>
							<tr>
								<td align="left"><big><strong>Referencia:</strong> <strong><span style="text-decoration: underline;">'.$Referencia.'</span></strong></big></td>
								<td align="left">&nbsp;</td>
								<td align="left"><big><strong>Fecha:</strong> '.date_format($Fecha, 'd/m/Y').'<big></td>
							</tr>
							<tr>
								<td align="left" colspan="3"><big><strong>Cliente:</strong><strong><span style="text-decoration: underline;">'.$Cliente.'</span></strong></big></td>
							</tr>';

	if($FechaRechazo == ''){
		$sHTML .= '			<tr><td colspan="3">&nbsp;</td></tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<big>
										<strong>Fecha Autorizaci&oacute;n:</strong> ';
		if ($App == 'Bodega') {
			$sHTML .= date_format($FechaAutorizadoBodega,'d/m/Y H:i:s');
		} else if ($App == 'Cliente') {
			$sHTML .= date_format($FechaAutorizadoCliente,'d/m/Y H:i:s');
		} else {
			$sHTML .= date_format($FechaAutorizadoEjecutivo,'d/m/Y H:i:s');
		}
		$sHTML .= '					<big>
								</td>
							</tr>';
	}

	$sHTML .= '				<tr><td colspan="3">&nbsp;</td></tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3"><big><strong>Solicit&oacute;:</strong> '.$Usuario.'<big></td>
							</tr>
							<tr><td colspan="3">&nbsp;</td></tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3"><strong>Linea que entreg&oacute;:</strong> '.$LineaEntrego.'</td>
							</tr>';
	if ($FechaAutorizadoBodega != '') {
		$sHTML .= '			<tr><td colspan="3">&nbsp;</td></tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3"><strong>Autorizado por Bodega:</strong> <strong style="color: #3c763d;">'.date_format($FechaAutorizadoBodega,'d/m/Y H:i:s').'</strong></td>
							</tr>';
	} else {
		$sHTML .= '			<tr><td colspan="3">&nbsp;</td></tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3"><strong>Autorizado por Bodega:</strong> '.(($FechaRechazo != '' && $App == 'Bodega')? '<strong style="color: #ff5a5a;">RECHAZADO</strong>' : '<strong style="color: #E38F00;">PENDIENTE</strong>').'</td>
							</tr>';
	}

	if ($FechaAutorizadoCliente != '') {
		$sHTML .= '			<tr><td colspan="3">&nbsp;</td></tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3"><strong>Autorizado por Cliente:</strong> <strong style="color: #3c763d;">'.date_format($FechaAutorizadoCliente,'d/m/Y H:i:s').'</strong></td>
							</tr>';
	} else {
		$sHTML .= '			<tr><td colspan="3">&nbsp;</td></tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3"><strong>Autorizado por Cliente:</strong> '.(($FechaRechazo != '' && $App == 'Cliente')? '<strong style="color: #ff5a5a;">RECHAZADO</strong>' : '<strong style="color: #E38F00;">PENDIENTE</strong>').'</td>
							</tr>';
	}
		
	if ($FechaAutorizadoEjecutivo != '') {
		$sHTML .= '			<tr><td colspan="3">&nbsp;</td></tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3"><strong>Autorizado por Ejecutivo:</strong> <strong style="color: #3c763d;">'.date_format($FechaAutorizadoEjecutivo,'d/m/Y H:i:s').'</strong></td>
							</tr>';
	} else {
		$sHTML .= '			<tr><td colspan="3">&nbsp;</td></tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3"><strong>Autorizado por Ejecutivo:</strong> '.(($FechaRechazo != '' && $App == 'Ejecutivo')? '<strong style="color: #ff5a5a;">RECHAZADO</strong>' : '<strong style="color: #E38F00;">PENDIENTE</strong>').'</td>
							</tr>';
	}

	$sHTML .= '				<tr>
								<td style="background: #E4FFF3;" colspan="3" align="center">
									<h3>';

	if ($FechaRechazo == '') { 
		if($Tipo == 'Ejecut'){
			if ($FechaAutorizadoEjecutivo == '') {
				$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/ajax/tiempo_extra/ajax_autorizar_tiempo_extra.php?isd='.$IdSolicitud.'&tp=E" style="color: #3c763d;">[Autorizar]</a>';
				$sHTML .= '				   		<br/><br/>';
				$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/tiempos_extra_rechazar.php?isd='.$IdSolicitud.'&tp=E&usr='.$Cliente.'" style="color: #ff5a5a;">[Rechazar Solicitud]</a>';
			}
		} else if($Tipo == 'Bodega'){ 
			if ($FechaAutorizadoBodega == '') {
				$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/ajax/tiempo_extra/ajax_autorizar_tiempo_extra.php?isd='.$IdSolicitud.'&tp=B" style="color: #3c763d;">[Autorizar]</a>';
				$sHTML .= '				   		<br/><br/>';
				$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/tiempos_extra_rechazar.php?isd='.$IdSolicitud.'&tp=B&usr='.$Cliente.'" style="color: #ff5a5a;">[Rechazar Solicitud]</a>';
			}
		} else { //Client
			if ($FechaAutorizadoCliente == '') {
				$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/ajax/tiempo_extra/ajax_autorizar_tiempo_extra.php?isd='.$IdSolicitud.'&tp=C" style="color: #3c763d;">[Autorizar]</a>';
				$sHTML .= '				   		<br/><br/>';
				$sHTML .= '				   		<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/tiempos_extra_rechazar.php?isd='.$IdSolicitud.'&tp=C&usr='.$Cliente.'" style="color: #ff5a5a;">[Rechazar Solicitud]</a>';
			}
		}
	}

	/*if($Tipo == 'Ejecut'){
		$sHTML .= '				   		<a href="https://www.delbravoweb.com/monitor/panel/tiempos_extra.php?ref='.$Referencia.'">[Autorizar/Rechazar Solicitud]</a>';
	} else if($Tipo == 'Bodega'){ 
		$sHTML .= '				   		<a href="https://www.delbravoapps.com/webtools/tiempos_extra.php?ref='.$Referencia.'">[Autorizar/Rechazar Solicitud]</a>';
	} else {
		$sHTML .= '						<a href="https://www.delbravoweb.com/sii/admin/tiempos_extra.php?ref='.$Referencia.'">[Autorizar/Rechazar Solicitud]</a>';
	}*/

	$sHTML .= '						</h3>
								</td>
							</tr>
							<tr>
								<td colspan="3">
								<p>Es necesario responder a la brevedad a esta solicitud, todo esto para brindarle un servicio mucho m&aacute;s r&aacute;pido y de mejor calidad. Muchas gracias y quedamos a la espera de su pronta respuesta en el link antes mencionado.</p>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>';

	return $sHTML;
}

/*****************************************************************************************/
/* ENVIAR CORREO */
/*****************************************************************************************/

function enviamail($asunto, $mensaje, $to){
	
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
	if (count($to)>0){
		foreach($to as $t){
			// Esta es la dirección a donde enviamos
			$mail->AddAddress($t);
		}
	}
	//if (count($bcc)>0){
		//foreach($bcc as $b){
			// Esta es la dirección a donde enviamos
	$mail->AddBcc('marco@delbravo.com');
	
	$mail->IsHTML(true); // El correo se envía como HTML
	$mail->Subject = $asunto; // Este es el titulo del email.
	$mail->Body = $mensaje; // Mensaje a enviar
	$exito = $mail->Send(); // Envía el correo.

	//También podríamos agregar simples verificaciones para saber si se envió:
	if($exito){
		$respuesta['Codigo']=1;
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']=$mail->ErrorInfo;
	}
	return $respuesta;
}

/*****************************************************************************************/
/* HTML ERRORS */
/*****************************************************************************************/
function get_html_error_description($sMensaje) {
	$sHtml = '
		<!DOCTYPE html>
		<html>
			<head>
				<style>
					.alert {
						padding: 20px;
						background-color: #f44336;
						color: white;
					}
				</style>
			</head>
			<body>
				<br/><br/>
				<center>
					<table width="700" cellpadding="0" cellspacing="0" border="0">
						<tbody>
							<tr>
								<td id="itd_titulo_alerta_color"width="15" bgcolor="#C62828"></td>
								<td id="itd_titulo_alerta_color2" width="600" align="left" valign="middle" bgcolor="#C62828" style="font-family:Arial, Helvetica, sans-serif;font-size:20px;color:#ffffff;">
									<strong id="istrong_titulo_alerta">'.utf8_decode($sMensaje).'</strong>
								</td>
								<td>
									<img src="http://www.delbravo.com/es/wp-content/uploads/2015/11/delbravo-logo.png" width="85" height="85" border="0" style="display:block;">
								</td>
							</tr>
						</tbody>
					</table>
				</center>
			</body>
		</html>
	';
	
	return $sHtml;
}

function get_html_warning_description($sMensaje) {
	$sHtml = '
		<!DOCTYPE html>
		<html>
			<head>
				<style>
					.alert {
						padding: 20px;
						background-color: #f44336;
						color: white;
					}
				</style>
			</head>
			<body>
				<br/><br/>
				<center>
					<table width="700" cellpadding="0" cellspacing="0" border="0">
						<tbody>
							<tr>
								<td id="itd_titulo_alerta_color"width="15" bgcolor="#e1b105"></td>
								<td id="itd_titulo_alerta_color2" width="600" align="left" valign="middle" bgcolor="#e1b105" style="font-family:Arial, Helvetica, sans-serif;font-size:20px;color:#ffffff;">
									<strong id="istrong_titulo_alerta">'.utf8_decode($sMensaje).'</strong>
								</td>
								<td>
									<img src="http://www.delbravo.com/es/wp-content/uploads/2015/11/delbravo-logo.png" width="85" height="85" border="0" style="display:block;">
								</td>
							</tr>
						</tbody>
					</table>
				</center>
			</body>
		</html>
	';
	
	return $sHtml;
}

function get_html_ok_description($sMensaje) {
	$sHtml = '
		<!DOCTYPE html>
		<html>
			<head>
				<style>
					.alert {
						padding: 15px;
						margin-bottom: 20px;
						border: 1px solid transparent;
						border-radius: 4px;
						color: #3c763d;
						background-color: #dff0d8;
						border-color: #d6e9c6;
					}
				</style>
			</head>
			<body>
				<br/><br/>
				<center>
					<table width="700" cellpadding="0" cellspacing="0" border="0">
						<tbody>
							<tr>
								<td id="itd_titulo_alerta_color"width="15" bgcolor="#2E7D32"></td>
								<td id="itd_titulo_alerta_color2" width="600" align="left" valign="middle" bgcolor="#2E7D32" style="font-family:Arial, Helvetica, sans-serif;font-size:20px;color:#ffffff;">
									<strong id="istrong_titulo_alerta">'.utf8_decode($sMensaje).'</strong>
								</td>
								<td>
									<img src="http://www.delbravo.com/es/wp-content/uploads/2015/11/delbravo-logo.png" width="85" height="85" border="0" style="display:block;">
								</td>
							</tr>
						</tbody>
					</table>
				</center>
			</body>
		</html>
	';
	
	return $sHtml;
}
