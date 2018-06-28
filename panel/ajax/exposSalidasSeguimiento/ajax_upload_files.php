<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require_once './../../../bower_components/PHPMailer/PHPMailerAutoload.php';
include('clsCorreos.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_FILES['ifile_documentos']) && !empty($_FILES['ifile_documentos'])) { 
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		// get the files posted
		$oFiles = $_FILES['ifile_documentos'];		
		$sIdFolio = empty($_POST['sIdFolio']) ? '' : $_POST['sIdFolio'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$sPathFiles = "D:\\archivos_web\\monitor\\exposSeguimiento";
		
		// a flag to see if everything is ok
		$success = null;
		// get file names
		$oFileNames = $oFiles['name'];
		
		//***********************************************************//
		
		$carpeta = $sPathFiles . DIRECTORY_SEPARATOR . $sIdFolio;
		if (!file_exists($carpeta)) {
			mkdir($carpeta, 0777, true);
		}

		// loop and process files
		for($i=0; $i < count($oFileNames); $i++){
			$ext = explode('.', basename($oFileNames[$i]));
			
			$sFileName = md5(uniqid()). "." . array_pop($ext);
			$target = $sPathFiles . DIRECTORY_SEPARATOR . $sIdFolio . DIRECTORY_SEPARATOR . $sFileName;
			$respuesta['Target']=$target;
			if(move_uploaded_file($oFiles['tmp_name'][$i], $target)) {
				$success = true;
				$paths[] = $target;
				
				$consulta="INSERT INTO bodega.expos_seguimiento_files
								(id_folio, nombre_archivo, fecha)
								VALUES(".$sIdFolio.",
									   '".$sFileName."',
									   '".$fecha_registro."')";
									   
				$query = mysqli_query($cmysqli,$consulta);		
				if ($query==false){
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje'] = 'Error en insertar factura en expos_salidas_facturas'.$consulta;
					$respuesta['Error']=' ['.$error.']';
					
					break;
				}
			} else {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='No se creo archivo, contacte al administrador';
				
				$success = false;
				break;
			}
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}

function envia_notificacion($adjuntos,$fec_gen,$sIdSalida, $fecha_registro, $bcc, $to){
	$sAsunto = 'Carga de archivos en salida '.$sIdSalida;
	
	global $sCaja;
	
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
						<img src="http://delbravoweb.com/admin_clientes/images/email_top_barra.png" width="100%" height="20px" border="0" style="display:block;">
					</div>

					<table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF">
						<tbody>
							<tr>
								<td width="15">&nbsp;</td>
								<td width="534" valign="top" align="left" style="font-family:Arial, Helvetica, sans-serif;color:#626262;font-size:14px;">
									Un ejecutivo a subido documentaci&oacute;n en la salida ['.$sIdSalida.']
									
									<hr/>
									<p><strong>Numero de caja:</strong> '.$sCaja.'</p>
									<p><strong>Fecha y hora de subida:</strong> '.$fec_gen.'</p>
									<p>Para verificar los archivos ingresar <a style="color:#3333d7;" href="http://delbravoweb.com/sii/logistica/exposSalidasViewer.php?dt='.$fecha_registro.'&sld='.$sIdSalida.'">aqu&iacute;</a></p>
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
	//array_push($to,'abisaicruz@delbravo.com');
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