<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');

include './../../../bower_components/PHPMailer/PHPMailerAutoload.php';
require('./../../../plugins/FPDF/fpdf.php');
require('./../../../plugins/FPDI/fpdi.php');

class ConcatPdf extends FPDI {
    public $files = array();

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function concat()
    {
        foreach($this->files AS $file) {
			$pageCount = $this->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
				$tplIdx = $this->ImportPage($pageNo);
                $s = $this->getTemplatesize($tplIdx);
                $this->AddPage($s['w'] > $s['h'] ? 'L' : 'P', array($s['w'], $s['h']));
                $this->useTemplate($tplIdx);
            }
        }
    }
}

if($loggedIn == false){
	echo '500';
} else {	
	$respuesta['Codigo'] = 1;
	
	if (isset($_POST['sNoMov']) && !empty($_POST['sNoMov'])) { 
		$sNoMov = $_POST['sNoMov'];
		$sPatente = $_POST['sPatente'];
		$sPedimento = $_POST['sPedimento'];
		$sTipoEnvio = $_POST['sTipoEnvio'];
		$sAnexos = $_POST['sAnexos'];
		$sXml = $_POST['sXml'];
		$aEmails = json_decode($_POST['aEmails']);
		$aPedimento = json_decode($_POST['aPedimento']);
		$aHC = json_decode($_POST['aHC']);
		$aMV = json_decode($_POST['aMV']);
		$files = $_FILES;
		
		/******************************************************/
	
		$adjuntos=array();
		$aArchivos=array();
		
		if ($sNoMov != '') {
			$sFile = $dir_archivos_gabdata.'Avanza\\gab\\pdf\\'.$sNoMov.'.pdf';
			if (file_exists($sFile)){ 
				if ($sTipoEnvio == 'unico') {
					array_push($aArchivos, $sFile);
				} else {
					array_push($adjuntos, array('dir' => $sFile, 'name' => $sNoMov.'.pdf'));
				}
			}
		}
		
		if ($sXml != '') {
			$sFile = $dir_archivos_gabdata.'Avanza\\gab\\xml\\'.$sXml.'.xml';
			if (file_exists($sFile)){ 
				if ($sTipoEnvio == 'separado') {
					array_push($adjuntos, array('dir' => $sFile, 'name' => $sXml.'.xml'));
				}
			}
		}
		
		foreach($aPedimento as $pedimento){
			$sFile = $dir_archivos_pedimentos.$pedimento.'.pdf';
			if (file_exists($sFile)){ 
				if ($sTipoEnvio == 'unico') {
					array_push($aArchivos, $sFile);
				} else {
					array_push($adjuntos, array('dir' => $sFile, 'name' => $pedimento.'.pdf'));
				}
			}
		}
		
		if ($sAnexos != '') {
			$sFile = $dir_server_web.'cfd\\anexos\\'.$sAnexos.'.pdf';
			if (file_exists($sFile)){ 
				if ($sTipoEnvio == 'unico') {
					array_push($aArchivos, $sFile);
				} else {
					array_push($adjuntos, array('dir' => $sFile, 'name' => $sAnexos.'_anexos.pdf'));
				}
			}
		}
		
		foreach($aHC as $hc){
			$sFile = $dir_archivos_pedimentos.'HCMV\\'.str_replace("-", "_", $hc).'_Hojascalculo.pdf';
			if (file_exists($sFile)){ 
				if ($sTipoEnvio == 'unico') {
					array_push($aArchivos, $sFile);
				} else {
					array_push($adjuntos, array('dir' => $sFile, 'name' => str_replace("-", "_", $hc).'_Hojascalculo.pdf'));
				}
			}
		}
		
		foreach($aMV as $mv){
			$sFile = $dir_archivos_pedimentos.'HCMV\\'.str_replace("-", "_", $mv).'_Manifestacion.pdf';
			if (file_exists($sFile)){ 
				if ($sTipoEnvio == 'unico') {
					array_push($aArchivos, $sFile);
				} else {
					array_push($adjuntos, array('dir' => $sFile, 'name' => str_replace("-", "_", $mv).'_Manifestacion.pdf'));
				}
			}
		}
		
		/**********************************************************************************************/
		/* ..:: Archivos Extra ::.. */
		
		$sPathFiles = $dir_archivos_web."monitor\\listctamex\\temporales";
		if (!file_exists($sPathFiles)) {
			mkdir($sPathFiles, 0777, true);
		}
		
		eliminar_archivos_viejos($sPathFiles);
		
		if(count($files) > 0) {
			foreach($files as $file){
				$ext = explode('.', basename($file['name']));
				$sFileName = md5(uniqid()). "." . array_pop($ext);
				$target = $sPathFiles . DIRECTORY_SEPARATOR . $sFileName;
				
				if(move_uploaded_file($file['tmp_name'], $target)) {
					if ($sTipoEnvio == 'unico') {
						array_push($aArchivos, $target);
					} else {
						array_push($adjuntos, array('dir' => $target, 'name' => $file['name']));
					}
				}
			}
		}
		
		/**********************************************************************************************/
		
		if ($sTipoEnvio == 'unico') { 
			$pdf = new ConcatPdf();
			$pdf->setFiles($aArchivos);
			$pdf->concat();
			
			$sFile = $sPathFiles . DIRECTORY_SEPARATOR . $sPatente.'-'.$sPedimento.'.pdf';
			$pdf->Output($sFile,'F');
			array_push($adjuntos, array('dir' => $sFile, 'name' => $sPatente.'-'.$sPedimento.'.pdf'));
		}
		
		/**********************************************************************************************/
		
		$bcc=array();
		$to=array();
		
		foreach($aEmails as $email){
			array_push($to, $email);
		}
		
		$fec1= new DateTime();
		$fec3= date_format($fec1, 'd/m/Y h:i a');
		$nombre_reporte = 'Documentos de cuenta '.$sNoMov;
		$renvio=envia_rpt($adjuntos,$fec3,$nombre_reporte, $sPatente.'-'.$sPedimento, $bcc, $to);
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	
	echo json_encode($respuesta);
}

function eliminar_archivos_viejos($path){
	$fileSystemIterator = new FilesystemIterator($path);
	$now = time();
	foreach ($fileSystemIterator as $file) {
		//3600 segundos que equivale a 1 hora
		if ($now - $file->getCTime() >= 3600) {			
			unlink($path . DIRECTORY_SEPARATOR . $file->getFilename());
		}
	}
}

function envia_rpt($adjuntos,$fec_gen,$nombre_reporte, $sAsunto, $bcc, $to){
	$asunto=$sAsunto;
	$mensaje='
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>delbravo</title>
		  
			<style>
				.navigation_menu {
					background-position: top;
					width: 600px;
					position: relative;
				}
				
				p {margin-top:0px; margin-bottom:5px;}
				p strong { color:#000; }
			</style>
		</head>
		
		<body>
			<center>
				<table width="600" cellpadding="0" cellspacing="0" border="0">
				   <tbody>
					  <tr>
						 <td id="itd_titulo_alerta_color"width="15" bgcolor="#4E7EC1"></td>
						 <td id="itd_titulo_alerta_color2" width="500" align="left" valign="middle" bgcolor="#4E7EC1" style="font-family:Arial, Helvetica, sans-serif;font-size:20px;color:#ffffff;">
							<strong id="istrong_titulo_alerta">'. $nombre_reporte.'</strong>
						 </td>
						 <td>
							<img src="http://www.delbravo.com/es/wp-content/uploads/2015/11/delbravo-logo.png" width="85" height="85" border="0" style="display:block;">
						 </td>
					  </tr>
				   </tbody>
				</table>
				<div class="navigation_menu">				
					<img src="http://delbravoweb.com/admin_clientes/images/email_top_barra.png" width="100%" height="20px" border="0" style="display:block;">
				</div>

				<table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF">
					<tbody>
						<tr>
							<td width="15">&nbsp;</td>
							<td id="itd_detalles_notificacion" width="534" valign="top" align="left" style="font-family:Arial, Helvetica, sans-serif;color:#626262;font-size:14px;">
								</br>Adjunto a este correo encontrara el reporte de '. $nombre_reporte.'
								<hr/>
								<p>
									<strong>Fecha y hora de generacion: </strong>'.$fec_gen.'
								</p>
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
				<table align="center" border="0" width="600" cellpadding="0" cellspacing="0" cellspacing="0" style="max-width:600px; background: #0055a0 none repeat scroll 0% 0%; color:#FFF;">
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
	
	$correo=enviamail($asunto,$mensaje,$to,$bcc,'mail.delbravo.com','25','avisosautomaticos@delbravo.com','aviaut01','',$adjuntos);
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
		$mail->AddAttachment($adjuntos[$x]['dir'],$adjuntos[$x]['name']); 
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
		error_log(json_encode($respuesta));
	}
	return $respuesta;
}
?>