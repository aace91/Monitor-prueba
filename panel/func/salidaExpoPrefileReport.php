<?php
include ('../../connect_dbsql.php');
require_once '../../bower_components/PHPMailer/PHPMailerAutoload.php';

$__bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);
$__strEmailAlerta = 'jcdelacruz@delbravo.com';

$consulta="SELECT a.salidanumero, a.fecha, a.caja,
				  GROUP_CONCAT(DISTINCT b.FACTURA_NUMERO SEPARATOR ', ') AS FACTURAS,
				  a.lineatransp, 
				  GROUP_CONCAT(DISTINCT b.PEDIMENTO SEPARATOR ', ') AS PEDIMENTOS,
				  a.usuario, d.usuEmail,
				  GROUP_CONCAT(DISTINCT c.email SEPARATOR ',') AS email,
				  GROUP_CONCAT(DISTINCT CONCAT(b.TIPOSALIDA, ': ' , b.CAJA) SEPARATOR ', ') AS CAJAS
		   FROM bodega.salidas_expo AS a INNER JOIN
				bodega.facturas_expo AS b ON b.SALIDA_NUMERO=a.salidanumero INNER JOIN
				bodega.contactos_expo AS c ON c.id_catalogo=b.NOAAA AND
											  c.tipo_catalogo='AAA' INNER JOIN 
				bodega.tblusua AS d ON d.usunombre=a.usuario
		   WHERE b.NOAAA=58 AND
			     b.PREFILE_ID IS NULL AND
				 a.salidanumero>=140771 AND 
				 a.salidanumero NOT IN ('134627')
		   GROUP BY a.salidanumero
		   ORDER BY a.salidanumero DESC";
		   
$query = mysqli_query($cmysqli, $consulta);
if (!$query) {
	$respuesta['Codigo']=-1;
	$respuesta['Mensaje']='Error al consultar la lista de salidas pendientes.'; 
	$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
	
	enviamail('SALIDAEXPO: salidaExpoPrefileReport','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
	exit();
} else {
	$sHtmlTitle = '<tr style="background-color: #4472C4; color: #FFF; font-size:16px;">
					  <td align="center" style="border: solid 1px #ccc; width:70px;">Salida</td>
					  <td align="center" style="border: solid 1px #ccc; width:90px;">Fecha</td>
					  <td align="center" style="border: solid 1px #ccc; width:120px;">Ejecutivo</td>
					  <td align="center" style="border: solid 1px #ccc; width:90px;">Plataforma</td>
					  <td align="center" style="border: solid 1px #ccc; width:90px;">Factura(s)</td>
					  <td align="center" style="border: solid 1px #ccc;">Transportista</td>
					  <td align="center" style="border: solid 1px #ccc; width:96px;">Pedimento(s)</td>
					  <td align="center" style="border: solid 1px #ccc; width:100px;"></td>
				  </tr>';
	$sHtmlBody = '';
	$sTrStyle = '';
	$aEmailsSend = array();
	
	while($row = mysqli_fetch_object($query)){
		$salidanumero = $row->salidanumero;
		$fecha = $row->fecha;
		$caja = ((is_null($row->caja)? $row->CAJAS : $row->caja));
		$FACTURAS = $row->FACTURAS;
		$lineatransp = $row->lineatransp;
		$PEDIMENTOS = $row->PEDIMENTOS;
		$usuario = $row->usuario;
		$usuEmail = $row->usuEmail;
		$email = $row->email . ',' . $usuEmail;
		
		$aEmails = explode(',', $email);
		foreach ($aEmails as &$sEmail) {
			if ($aEmails != '') {
				if (!in_array($sEmail, $aEmailsSend)) {
					array_push($aEmailsSend, $sEmail);
				}
			}
		}
		
		if ($sTrStyle == '') {
			$sTrStyle = 'style="background-color: #f2f2f2;"';
		} else {
			$sTrStyle = '';
		}
		
		$sHtmlBody .= '<tr '.$sTrStyle.'>
						  <td align="center" style="border: solid 1px #ddd;">'.$salidanumero.'</td>
		                  <td align="center" style="border: solid 1px #ddd;">'.date_format(new DateTime($fecha),"d/m/Y").'</td>
						  <td align="center" style="border: solid 1px #ddd;">'.$usuario.'</td>
		                  <td align="center" style="border: solid 1px #ddd;">'.$caja.'</td>
		                  <td align="center" style="border: solid 1px #ddd;">'.$FACTURAS.'</td>
		                  <td style="border: solid 1px #ddd;">'.$lineatransp.'</td>
		                  <td style="border: solid 1px #ddd;">'.$PEDIMENTOS.'</td>
						  <td align="center" style="border: solid 1px #ddd;">
							<a href="https://www.delbravoweb.com/'.(($__bDebug)? 'monitorpruebas' : 'monitor').'/panel/salidaExpo.php?id='.$salidanumero.'">'.utf8_decode('Asignar Prefile').'</a>
						  </td>
		              </tr>';
	}
	
	if ($sHtmlBody != '') {
		$sHtmlBody = $sHtmlTitle.$sHtmlBody;
		fcn_envia_notificacion_prefile($sHtmlBody, $aEmailsSend);
	}
}

echo '<br>Proceso Finalizado...';

/**************************************************************************************************/
/* ENVIO DE CORREOS */
/**************************************************************************************************/

function fcn_envia_notificacion_prefile($sHtmlBody, $aEmailsSend) {
	global $__bDebug;
	
	$adjuntos=array();
	$bcc=array();
	$to=$aEmailsSend;
		
	$asunto='Reporte de Exportaciones pendientes de Prefile';
	
	$sTitulo = 'Exportaciones pendientes de Prefile';
	
	$sHTML = '
	<table style="border: solid 1px #bbbccc; width: 900px;" cellspacing="0" cellpadding="0">
		<tbody>
			<tr style="background-color: #0073b7; color: #fff;">
				<td style="background-color: #fff;" width="100px">
					<img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" />
				</td>
				<td width="10">&nbsp;</td>
				<td align="center"
					><h1>Grupo Aduanero Del Bravo.</h1>
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
									<h2 style="color:red;">'.utf8_decode($sTitulo).'</h2>
								</td>
							</tr>
							<tr>
								<td colspan="3" style="background-color: #fcf8e3;">
									<div style="border:1px solid #faebcc; color: #8a6d3b; padding: 15px;">
										<strong>Alerta!</strong> '.utf8_decode('Esta es una lista de exportaciones a las cuales no se les pudo asignar el Prefile automáticamente, favor de atender a la brevedad.').' 
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="8">
									<table width="100%" cellspacing="0" cellpadding="0">
										'.$sHtmlBody.'
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3">
									<p>Este e-mail fue generado automaticamente, no conteste a el, si tiene alguna duda por favor comuniquese con su ejecutivo de Cuenta. Atte. Grupo Aduanero del Bravo S.A..</p>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>';
	
	if ($__bDebug) {
		$bcc=array();
		$to=array();
	} else {
		array_push($bcc,'enrique@delbravo.com');
	}
	array_push($bcc,'jcdelacruz@delbravo.com');
	
	$correo=enviamail($asunto,$sHTML,$to,$bcc,'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',$adjuntos);
	
	return $correo;
}

function enviamail($asunto,$mensaje,$to,$bcc,$mailserver,$portmailserver,$sender,$pass,$ruta_logo,$adjuntos){
	$mail = new PHPMailer();
	//Luego tenemos que iniciar la validación por SMTP:
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->Host = $mailserver; // SMTP a utilizar. Por ej. smtp.elserver.com
	$mail->Username = $sender; // Correo completo a utilizar
	$mail->Password = $pass; // Contraseña
	$mail->Port = $portmailserver; // Puerto a utilizar
	//Con estas pocas líneas iniciamos una conexión con el SMTP. Lo que ahora deberíamos hacer, es configurar el mensaje a enviar, el //From, etc.
	$mail->From = $sender; // Desde donde enviamos (Para mostrar)
	$mail->FromName = "Salidas de Exportacion Grupo Aduanero del Bravo";
	if($ruta_logo!=''){
		$mail->AddAttachment($ruta_logo, 'logo.png'); 
	}
	for($x=0;$x<count($adjuntos);$x++){
		$mail->AddAttachment($adjuntos[$x],$adjuntos[$x]); 
	}
	//Estas dos líneas, cumplirían la función de encabezado (En mail() usado de esta forma: “From: Nombre <correo@dominio.com>”) de //correo.
	if (count($to)>0){
		foreach($to as $t){
			// Esta es la dirección a donde enviamos
			$mail->AddAddress($t);
		}
	}
	if (count($bcc)>0){
		foreach($bcc as $b){
			// Esta es la dirección a donde enviamos
			$mail->AddBcc($b);
		}
	}
	$mail->IsHTML(true); // El correo se envía como HTML
	$mail->Subject = $asunto; // Este es el titulo del email.
	$mail->Body = $mensaje; // Mensaje a enviar
	$exito = $mail->Send(); // Envía el correo.

	//También podríamos agregar simples verificaciones para saber si se envió:
	if($exito){
		$respuesta['Codigo']=1;
		$respuesta['Mensaje']='El correo fue enviado correctamente.';
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']=$mail->ErrorInfo;
	}
	return $respuesta;
}