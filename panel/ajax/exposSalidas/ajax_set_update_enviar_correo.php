<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require_once './../../../bower_components/PHPMailer/PHPMailerAutoload.php';
include('clsCorreos.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sIdSalida']) && !empty($_POST['sIdSalida'])) {  
		$respuesta['Codigo'] = 1;	
	
		//***********************************************************//
		
		$sIdSalida = $_POST['sIdSalida'];
		$sIdCliente = $_POST['sIdCliente'];  
		$sCaja = $_POST['sCaja'];  
		$sIdLogistica = $_POST['sIdLogistica'];  
		$sCorreo = $_POST['sCorreo'];
		$sObservaciones = $_POST['sObservaciones'];

		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");

		$sPathFiles = "D:\\archivos_web\\monitor\\exposSalidas";
		$adjuntos=array();

		//***********************************************************//
		
		$consulta="UPDATE bodega.expos_salidas
				   SET fecha_aprobado='".$fecha_registro."',
				       observaciones='".$sObservaciones."'
				   WHERE id_salida=".$sIdSalida;
							   
		$query = mysqli_query($cmysqli,$consulta);		
		if ($query==false){
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje'] = 'Error al actualizar la fecha de aprobado en expos_salidas';
			$respuesta['Error']=' ['.$error.']';
		} else {
			$respuesta['Mensaje'] = 'Correo enviado correctamente.';

			$consulta="SELECT nombre_archivo
					   FROM bodega.expos_salidas_files
                       WHERE id_salida=".$sIdSalida." AND nombre_archivo LIKE '%.pdf'";

            $query = mysqli_query($cmysqli,$consulta);
            if ($query==false){
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje'] = 'Error al consultar los archivos adjuntos';
				$respuesta['Error']=' ['.$error.']';
			} else {
				while($row = mysqli_fetch_array($query)){ 
					$sFile = $sPathFiles . "\\" . $sIdSalida . "\\" . $row['nombre_archivo'];
					array_push($adjuntos,$sFile);
				}
			} 
		}

		/* ..:: Enviar correo ::.. */
		if($respuesta['Codigo'] == '1') {
			$sCorreos = new CCorreo;
			$sCorreos->GenerarEmails($sIdCliente, '-1', $cmysqli);
			if ($sCorreos->bError) {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']=$sCorreos->sMensaje; 
				$respuesta['Error'] = ' ['.$sCorreos->sError.']';
			} else {
				//$sCorreos->to = get_email($sCorreo, $sCorreos->to);

				$respuesta['aTo']=$sCorreos->to;
				$respuesta['aBcc']=$sCorreos->bcc;
				//$respuesta['Mensaje']=$sCorreos->sMensaje; 
				
				
				$fec1= new DateTime();
				$fec3= date_format($fec1, 'd/m/Y h:i a');
				envia_notificacion($adjuntos, $fec3, $fecha_registro, $sCorreos->bcc, $sCorreos->to);
			}
		}		
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}

	echo json_encode($respuesta);
}

function envia_notificacion($adjuntos,$fec_gen, $fecha_registro, $bcc, $to){
	global $sIdSalida, $sCaja, $sObservaciones;
	
	$sAsunto = 'Documentos de Caja '.$sCaja;

	$sMensaje='
	<!DOCTYPE html>
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title>delbravo</title>
			</head>
			
			<style>
				p {margin-top:0px; margin-bottom:5px;}
				p strong { color:#000; }
			</style>
			<body>
				<center>
					<table width="600" cellpadding="0" cellspacing="0" border="0">
					   <tbody>
						  <tr>
							 <td width="15" bgcolor="#4E7EC1"></td>
							 <td width="500" align="left" valign="middle" bgcolor="#4E7EC1" style="font-family:Arial, Helvetica, sans-serif;font-size:20px;color:#ffffff;">
								<strong>'.$sAsunto.'</strong>
							 </td>
							 <td>
								<img src="http://www.delbravo.com/es/wp-content/uploads/2015/11/delbravo-logo.png" width="85" height="85" border="0" style="display:block;">
							 </td>
						  </tr>
					   </tbody>
					</table>
					<div style="background-position: top;
								width: 600px;
								position: relative;">
						<img src="http://delbravoweb.com/admin_clientes/images/email_top_barra.png" width="100%" height="20" border="0" style="display:block;">
					</div>

					<table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF">
						<tbody>
							<tr>
								<td width="15">&nbsp;</td>
								<td width="534" valign="top" align="left" style="font-family:Arial, Helvetica, sans-serif;color:#626262;font-size:14px;">
									Un ejecutivo a subido documentaci&oacute;n:
									
									<hr/>
									<p><strong>Numero de caja:</strong> '.$sCaja.'</p>
									<p><strong>Observaciones:</strong> '.$sObservaciones.'</p>
									<p><strong>Fecha y hora de subida:</strong> '.$fec_gen.'</p>
									<p>Para verificar los archivos ingresar <a style="color:#3333d7;" href="http://delbravoweb.com/sii/logistica/exposSalidasEnviar.php?cj='.$sCaja.'&sld='.$sIdSalida.'">aqu&iacute;</a></p>
								</td>
								<td width="6" valign="top"></td>
								<td width="30" valign="top" align="center"></td>
								<td width="15"></td>
							</tr>
						</tbody>
					</table>
				</center>		   
				<br/>
				<center>
					<table align="center" border="0" width="600" cellpadding="0" cellspacing="0" style="background: #0055a0 none repeat scroll 0% 0%; color:#FFF;">
						<tbody>
							<tr>
								<td>
									<img src="http://delbravoweb.com/admin_clientes/images/email_bottom_barra.png" border="0" style="left: 0; 
																																			width:100%;
																																			right: 0;
																																			height: 20px;
																																			z-index: 99;
																																			background-size: 100% 100%;
																																			margin-top: 0px;
																																			max-width:600px;">
									<font size="1" face="arial, helvetica, sans-serif">
										<p style="margin-left:5px;">Derechos reservados. Copyright 2017 Grupo Aduanero Del Bravo.</p>
										<p style="margin-left:5px;">Este correo se ha generado de forma automatica, favor de no responder sobre el.</p>
										<p style="margin-left:5px;">Visite nuestro Web Site <a href="www.delbravo.com" style="color:#FFF;">www.delbravo.com</a></p>
									</font>
									<br>
								</td>
							</tr>
						</tbody>
					</table>
				</center>
			</body>
		</html>';

	$bcc=array();
	$to=array();
	array_push($to,'jcdelacruz@delbravo.com');
	$correo=enviamail($sAsunto,$sMensaje,$to,$bcc,'mail.delbravo.com','25','avisosautomaticos@delbravo.com','aviaut01','images/logo.png',$adjuntos);
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
	$mail->FromName = $sender;
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
		$respuesta['codigo']=1;
		$respuesta['mensaje']='El correo fue enviado correctamente.';
	}else{
		$respuesta['codigo']=-1;
		$respuesta['mensaje']=$mail->ErrorInfo;
	}
	return $respuesta;
}