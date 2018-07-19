<?php

include('./../../../bower_components/PHPMailer/PHPMailerAutoload.php');

function enviar_notificacion_nuevo_cruce_email($idCruce,$action,$seccion){
	global $cmysqli;global $URL_archivos_permisos;
	$consulta = "SELECT ce.id_cruce,cli.cnombre as cliente,l.Nombre as linea_t,ce.aduana,tr.nombretransfer,
						ce.caat,ce.scac,ent.nombreentrega,ce.direntrega,ce.indicaciones,ce.numcliente,ce.po_number,
						IFNULL(GROUP_CONCAT(clice.cnombre SEPARATOR ' <br> '),'') as clientes_consolidar,
					    ce.observaciones
				 FROM cruces_expo	ce INNER JOIN
					  cltes_expo cli ON ce.numcliente = cli.gcliente LEFT JOIN 
					  lineast l ON ce.numlinea = l.numlinea LEFT JOIN
					  transfers_expo tr ON ce.notransfer = tr.notransfer LEFT JOIN
					  entregas_expo ent ON ce.noentrega = ent.numeroentrega LEFT JOIN
					  cruces_expo_clientes_consolidar cc ON ce.id_cruce = cc.id_cruce LEFT JOIN
					  cltes_expo clice ON cc.numcliente = clice.gcliente
				 WHERE ce.id_cruce = ".$idCruce;

	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo'] = -1;
		$respuesta['Error']='La notificacion NO fue enviada. Body.HTML.EMail.MySQL.cruces_expo['.$error.']';
		return $respuesta;
	}
	if(mysqli_num_rows($query) > 0){
		while($row = mysqli_fetch_array($query)){
			$id_cruce = $row['id_cruce'];
			$cliente = $row['cliente'];
			$linea_t = $row['linea_t'];
			$aduana = $row['aduana'];
			$nombretransfer = $row['nombretransfer'];
			$caat = $row['caat'];
			$scac = $row['scac'];
			$po_number = $row['po_number'];
			$nombreentrega = $row['nombreentrega'];
			$direntrega = $row['direntrega'];
			$indicaciones = $row['indicaciones'];
			$observaciones = $row['observaciones'];
			$numcliente = $row['numcliente'];
			$clientes_consolidar = $row['clientes_consolidar'];
		}
		$sHTML = '<table style="border: solid 1px #bbbccc; width: 1100px;" cellspacing="0" cellpadding="0">
					<tbody>
						<tr style="background-color: #0073b7; color: #fff;">
							<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
						<td width="10">&nbsp;</td>
							<td align="center"><h1>Grupo Aduanero Del Bravo, S.A. de C.V.</h1></td>
							<td width="10px">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4">
							<table width="100%" cellspacing="0" cellpadding="0">
								<tbody>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr><td style="background: #E4FFF3;" colspan="3" align="center"><h2>'.($action == 'Nuevo' ? 'Nuevo Cruce de Exportaci&oacute;n' : 'Modificaci&oacute;n de Cruce ['.$seccion.']').'</h2></td></tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr><td colspan="3" align="right"><strong>Fecha:</strong> '.date("d/m/Y H:i:s").'</td></tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr><td colspan="3" align="left"><big>Cruce: <strong><span style="text-decoration: underline;">'.$id_cruce.'</span></strong></big></td></tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr><td colspan="3" align="left"><big>Cliente: <strong><span style="text-decoration: underline;">'.$cliente.'</span></strong></big></td></tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr><td colspan="3">Linea Transportista: <strong>'.$linea_t.'</strong></td></tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr>
									<td colspan="3"><big>Aduana: <strong><span style="text-decoration: underline;">'.$aduana.'</span></strong><big></big></big></td>
								</tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr><td colspan="3">Transfer: <strong>'.$nombretransfer.'</strong></td></tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr>
									<td>CAAT: <strong>'.$caat.'</strong></td>
									<td>&nbsp;</td>
									<td>SCAC: <strong>'.$scac.'</strong></td>
								</tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr><td colspan="3">PO Number: <strong>'.$po_number.'</strong></td></tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr><td colspan="3">Entregar en: <strong>'.$nombreentrega.'</strong></td></tr>
								<tr><td colspan="3">Direcci&oacute;n: <strong>'.$direntrega.'</strong></td></tr>
								<tr><td colspan="3">Indicaciones: <strong>'.$indicaciones.'</strong></td></tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr><td style="background-color: #bbbccc; border: 1px solid #bbbccc;" colspan="3" align="center"><big><strong>Observaciones</strong></big></td></tr>
								<tr><td style="border:1px solid #bbbccc;" colspan="3" align="left">'.($observaciones == '' ? '' : $observaciones).'</td></tr>
								<tr><td colspan="3">&nbsp;</td></tr>';
		if($clientes_consolidar != ''){
			$sHTML .= '			<tr><td style="background-color: #E4FFF3; border: 1px solid #bbbccc;" colspan="3" align="center"><big><strong>CRUCE CONSOLIDADO CON</strong></big></td></tr>
								<tr><td style="border: 1px solid #bbbccc;" colspan="3" align="center"><big><strong>'.$clientes_consolidar.'</strong></big></td></tr>
								<tr><td colspan="3">&nbsp;</td></tr>';
		}
		$sHTML .= '				<tr><td style="background-color: #0073b7; border: 1px solid #bbbccc; color: #fff;" colspan="3" align="center"><strong>FACTURAS</strong></td></tr>
								<tr>
									<td colspan="3">
										<table cellspacing="0" cellpadding="0">
										<thead>
											<tr style="background: #bbb; color: #000;">
												<td style="border:1px solid #AEAEAE;"><strong>Contenedor</strong></td>
												<td style="border:1px solid #AEAEAE;"><strong>Numero Factura</strong></td>
												<td style="border:1px solid #AEAEAE;"><strong>UUID</strong></td>
												<td style="border:1px solid #AEAEAE;"><strong>Fecha Factura</strong></td>
												<td style="border:1px solid #AEAEAE;"><strong>R&eacute;gimen</strong></td>
												<td style="border:1px solid #AEAEAE;"><strong>Atados</strong></td>
												<td style="border:1px solid #AEAEAE;"><strong>Peso kgs.</strong></td>
												<td style="border:1px solid #AEAEAE;"><strong>Peso lbs.</strong></td>
												<td style="border:1px solid #AEAEAE;"><strong>Agente Aduanal Americano</strong></td>
												<td style="border:1px solid #AEAEAE;"><strong>Avisos Autom&aacute;ticos</strong></td>
												<td style="border:1px solid #AEAEAE;"><strong>Avisos Adhesion</strong></td>
												<td align="center" style="border:1px solid #AEAEAE;"><strong>Factura</strong></td>
												<td align="center" style="border:1px solid #AEAEAE;"><strong>CFDI</strong></td>
												<td align="center" style="border:1px solid #AEAEAE;"><strong>Anexo Factura</strong></td>
												<td align="center" style="border:1px solid #AEAEAE;"><strong>Packing</strong></td>
												<td align="center" style="border:1px solid #AEAEAE;"><strong>Certificado de Origen</strong></td>
											</tr>
										<thead>
										<tbody>';
			
		$consulta = " SELECT  ced.tiposalida,ced.caja,ced.id_detalle_cruce,ced.numero_factura,ced.uuid,ced.fecha_factura,aaa.nombreaa,
								ced.regimen,ced.atados,ced.peso_factura_kgs,ced.peso_factura_lbs,
								GROUP_CONCAT(DISTINCT CONCAT('<a href=\"','".$URL_archivos_permisos."',pp.archivo_permiso,'\">',pp.numero_permiso,'</a>')) as avisos_automaticos,
								GROUP_CONCAT(DISTINCT CONCAT('<a href=\"','".$URL_archivos_permisos."',pad.archivo_permiso,'\">',pad.numero_permiso,'</a>')) as avisos_adhesion,
								ced.archivo_factura,
								IFNULL(ced.archivo_cfdi, '') as archivo_cfdi,
								IFNULL(ced.archivo_anexo_factura, '') as archivo_anexo_factura,
								IF(ced.archivo_cert_origen IS NULL, IF(cer.id_certificado IS NULL, '', CONCAT('".$URL_archivos_certificados_origen."',cer.archivo_certificado)),ced.archivo_cert_origen) as archivo_cert_origen, 
								IFNULL(ced.archivo_packinglist, '') as archivo_packinglist
						FROM cruces_expo_detalle ced
							INNER JOIN aaa ON
								ced.noaaa = aaa.numeroaa
							LEFT JOIN cruces_expo_permisos cep ON
								ced.id_detalle_cruce = cep.id_detalle_cruce
							LEFT JOIN permisos_pedimentos pp ON
								cep.id_permiso = pp.id_permiso
							LEFT JOIN permisos_adhesion pad ON
								cep.id_permiso_adhesion = pad.id_permiso_adhesion
							LEFT JOIN certificados_origen cer ON
								ced.id_certificado = cer.id_certificado
						WHERE ced.id_cruce = ".$id_cruce."
						GROUP BY ced.id_detalle_cruce";
						
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo'] = -1;
			$respuesta['Error']='Problemas al enviar notificacion. Body.HTML.Facturas['.$error.']';
			return $respuesta;
		}
		while($row = mysqli_fetch_array($query)){
			$sHTML .= '							<tr>
													<td style="border:1px solid #AEAEAE;">'.$row['tiposalida'].':'.$row['caja'].'</td>
													<td style="border:1px solid #AEAEAE;">'.$row['numero_factura'].'</td>
													<td style="border:1px solid #AEAEAE;">'.$row['uuid'].'</td>
													<td style="border:1px solid #AEAEAE;">'.$row['fecha_factura'].'</td>
													<td style="border:1px solid #AEAEAE;">'.$row['regimen'].'</td>
													<td style="border:1px solid #AEAEAE;">'.$row['atados'].'</td>
													<td style="border:1px solid #AEAEAE;">'.$row['peso_factura_kgs'].'</td>
													<td style="border:1px solid #AEAEAE;">'.$row['peso_factura_lbs'].'</td>
													<td style="border:1px solid #AEAEAE;">'.$row['nombreaa'].'</td>
													<td style="border:1px solid #AEAEAE;">'.$row['avisos_automaticos'].'</td>
													<td style="border:1px solid #AEAEAE;">'.$row['avisos_adhesion'].'</td>
													<td align="center" style="border:1px solid #AEAEAE;">&nbsp;<a href="'.$row['archivo_factura'].'">[PDF]</a>&nbsp;</td>
													<td align="center" style="border:1px solid #AEAEAE;">&nbsp;'.($row['archivo_cfdi'] == '' ? '' : '<a href="'.$row['archivo_cfdi'].'">[XML]</a>&nbsp;<a href="https://www.delbravoweb.com/sii/admin/ajax/cruces/descargar_cfdi_pdf.php?icd='.$row['id_detalle_cruce'].'">[PDF]&nbsp;</a>').'</td>
													<td align="center" style="border:1px solid #AEAEAE;">&nbsp;'.($row['archivo_anexo_factura'] == '' ? '' : '<a href="'.$row['archivo_anexo_factura'].'">[Anexo]</a>').'&nbsp;</td>
													<td align="center" style="border:1px solid #AEAEAE;">&nbsp;'.($row['archivo_packinglist'] == '' ? '' : '<a href="'.$row['archivo_packinglist'].'">[PDF]</a>').'&nbsp;</td>
													<td align="center" style="border:1px solid #AEAEAE;">&nbsp;'.($row['archivo_cert_origen'] == '' ? '' : '<a href="'.$row['archivo_cert_origen'].'">[PDF]</a>').'&nbsp;</td>
												</tr>';
		}
		$sHTML .= '						</tbody>
										</table>
									</td>
								</tr>
								<tr><td colspan="3">&nbsp;</td></tr>
								<tr>
									<td colspan="3">Este correo electronico fue enviado de forma automatica y no es necesario responder al mismo. Muchas Gracias!</td>
								</tr>
								</tbody>
							</table>
							</td>
						</tr>
					</tbody>
			</table>';
		$asunto = ($action == 'Nuevo' ? 'Nuevo Cruce de Exportacion. Cliente:'.$cliente.' Cruce: '.$id_cruce : 'Modificacion de Cruce. Cliente:'.$cliente.' Cruce: '.$id_cruce);
		$mensaje = $sHTML;
		
		$consulta = "SELECT GROUP_CONCAT(DISTINCT ctcli.email) as contactos_cliente,
						    GROUP_CONCAT(DISTINCT ctaaa.email) as contactos_aaa,
						    '' as contactos_lineat,
						    '' as contactos_transfer
				     FROM cruces_expo c LEFT JOIN
					      contactos_expo ctcli ON c.numcliente = ctcli.id_catalogo AND
						                          ctcli.tipo_catalogo = 'CLI' INNER JOIN
                          cruces_expo_detalle cd ON c.id_cruce = cd.id_cruce LEFT JOIN
						  contactos_expo ctaaa ON cd.noaaa = ctaaa.id_catalogo AND
						                          ctaaa.tipo_catalogo = 'AAA'
				     WHERE c.id_cruce = $id_cruce 
				     GROUP BY c.id_cruce";
					
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo'] = 1;
			$respuesta['Error'] = 'Problemas al enviar notificacion. Destinatarios['.$error.']' ;
			return $respuesta;
		}else{
			$to = array();
			while($row = mysqli_fetch_array($query)){
				if(trim($row['contactos_cliente']) != ''){
					$aCont = explode(',',$row['contactos_cliente']);
					$to = array_merge($to,$aCont);
				}
				if(trim($row['contactos_aaa']) != ''){
					$aCont = explode(',',$row['contactos_aaa']);
					$to = array_merge($to,$aCont);
				}
				if(trim($row['contactos_lineat']) != ''){
					$aCont = explode(',',$row['contactos_lineat']);
					$to = array_merge($to,$aCont);
				}
				if(trim($row['contactos_transfer']) != ''){
					$aCont = explode(',',$row['contactos_transfer']);
					$to = array_merge($to,$aCont);
				}
			}

			$to = array();
			$to = array('jcdelacruz@delbravo.com');

			$RespEmail = enviamail($asunto,$mensaje,$to);
			if($RespEmail['Codigo'] != 1){
				$respuesta['Codigo'] = -1;
				$respuesta['Error'] = 'Problemas al enviar la notificacion. ['.$RespEmail['Mensaje'].']';
			}else{
				$respuesta['Codigo'] = 1;
			}
		}
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = 'Problemas para enviar notificacion. [cruce:'.$idCruce.']';
	}
	return $respuesta;
}

function enviamail($asunto,$mensaje,$to){
	
	$mailserver = 'mail.delbravo.com';
	$portmailserver = '25';
	$sender = 'cruces_expo@delbravo.com';
	//$pass = 'aviaut01';
	
	$mail = new PHPMailer();
	//Luego tenemos que iniciar la validación por SMTP:
	$mail->IsSMTP();
	//$mail->SMTPAuth = true;
	$mail->SMTPAuth = false;
	//$mail->SMTPSecure = "tls";
	$mail->Host = $mailserver; // SMTP a utilizar. Por ej. smtp.elserver.com
	$mail->Username = $sender; // Correo completo a utilizar
	//$mail->Password = $pass; // Contraseña
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