<?php
require('./../../../connect_dbsql.php');
include('./../../../bower_components/PHPMailer/PHPMailerAutoload.php');

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);
	
$respuesta['Codigo'] = 1;

$consulta = "SELECT te.id_solicitud, te.referencia, te.motivo, te.fecha_registro, cli.Nom as cliente,
					fle.fleNombre as linea_entrego,
					IF(te.usuario_tipo = '1', u.usunombre, cli.Nom) as usuario_solicito,
					IF(te.usuario_tipo = '1', u.usuEmail, NULL) as usuario_email,
					bod.bodcli
			 FROM bodega.tiempo_extra te INNER JOIN 
					bodega.tblbod bod ON te.referencia = bod.bodReferencia INNER JOIN
					bodega.tblflet fle ON bod.bodfle = fle.fleClave INNER JOIN
					bodega.clientes cli ON bod.bodcli = cli.Cliente_id INNER JOIN
					bodega.tblusua u ON te.usuario_id = u.Usuario_id
			 WHERE te.solicito_ejecutivo=1 AND
				   te.fecha_autorizo_cliente IS NULL AND
				   te.fecha_rechazo IS NULL";
			 
$query = mysqli_query($cmysqli, $consulta);
if (!$query) {
	$error=mysqli_error($cmysqli);
	$respuesta['Codigo']=-1;
	$respuesta['Mensaje']='Error al consultar los clientes con servicios prioritarios pendientes. Por favor contacte al administrador del sistema.'; 
	$respuesta['Error'] = ' ['.$error.']';
	
	error_log('enviar_notificacion_pendientes_autorizar.php :: '.json_encode($respuesta));
} else {
	while ($row = mysqli_fetch_array($query)){
		$IdSolicitud = $row['id_solicitud'];
		$Referencia = $row['referencia'];
		$Motivo = $row['motivo'];
		$Fecha = date_create($row['fecha_registro']);
		$idCliente = $row['bodcli'];
		$Cliente = $row['cliente'];
		$LineaEntrego = $row['linea_entrego'];
		$Usuario = $row['usuario_solicito'];
		$UsuarioEmail = $row['usuario_email'];
		
		$consulta = "SELECT to1,to2,to3,to4,to5,to6,to7,to8,to9,to10,
							cc1,cc2,cc3,cc4,cc5,cc6,cc7,cc8,cc9,cc10
					 FROM bodega.geocel_clientes
					 WHERE f_numcli = ". $idCliente;
		
		$query_correos = mysqli_query($cmysqli, $consulta);
		if (!$query_correos) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error consultar correos de clientes en geocel_clientes. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
			
			error_log('enviar_notificacion_pendientes_autorizar.php :: '.json_encode($respuesta));
		} else {
			$to_Client = array();
			
			while ($emails = mysqli_fetch_array($query_correos)){
				for ($i = 1; $i <= 10; $i++) {
					$correo = $emails['to'.$i];
					if($correo != '' or $correo != NULL){
						array_push($to_Client, $correo);
					}
				}
			}
			
			if (count($to_Client) > 0) {
				if ($bDebug) {
					error_log('DEBUG[Servicio Prioritario - Notificacion Pendiente] Clientes Emails: '. json_encode($to_Client));
					$to_Client = array();
					array_push($to_Client, 'jcdelacruz@delbravo.com');
				}
				
				$asunto = 'Servicio Prioritario Pendiente. [Del Bravo Forwarding] ['.$Referencia.']['.$Cliente.']';
				$sHTML_Client = formato_correo_nueva_solicitud($IdSolicitud, $Referencia, $Motivo, $Fecha, $LineaEntrego, $Cliente, $Usuario, $bDebug);
				
				$RespEmail = enviamail($asunto, $sHTML_Client, $to_Client);
				if($RespEmail['Codigo'] != 1){
					$respuesta['Codigo'] = -1;
					$respuesta['Error'] = $RespEmail['Mensaje'];
				}
			}
		}
	}
}

echo json_encode($respuesta);

/*****************************************************************************************/
/* FORMATOS EMAIL */
/*****************************************************************************************/

function formato_correo_nueva_solicitud($IdSolicitud, $Referencia, $Motivo, $Fecha, $LineaEntrego, $Cliente, $Usuario, $bDebug) {
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
									<h2>Servicio Prioritario pendiente de autorizar</h2>
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
								<td align="right">
									<strong>Fecha:</strong> '.date_format($Fecha, 'd/m/y').'
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
								<td style="background: #FCF8E3;" colspan="3">'
									.utf8_decode('Estimado Cliente, tiene una solicitud de servicio prioritario pendiente por autorizar, puede realizar la autorización dando clic en el botón "Autorizar" que se muestra a continuación.').
								'</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #E4FFF3;" colspan="3" align="center">
									<h3>
										<a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/ajax/tiempo_extra/ajax_autorizar_tiempo_extra.php?isd='.$IdSolicitud.'&tp=C" style="color: #3c763d;">[Autorizar]</a>
									</h3>
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