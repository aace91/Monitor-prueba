<?php

include_once('./../../../checklogin.php');
include('../../../phpmailer/PHPMailerAutoload.php');

function enviamail($asunto, $mensaje, $to, $bcc, $mailserver, $portmailserver, $sender, $pass, $ruta_logo){
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

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['sReferencia']) && !empty($_POST['sReferencia'])) {		
		require('./../../../connect_casa.php');
		require('./../../../connect_dbsql.php');
		
		$sReferencia = $_POST['sReferencia'];
		$sCaja = $_POST['sCaja'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		$bContinue = true;
		$bCommit = true;
		
		//***********************************************************//
		
		$query = "SELECT num_refe
		          FROM SAAIO_PEDIME
				  WHERE num_refe='".$sReferencia."'";
		
		$result = odbc_exec($odbccasa, $query);
		
		if (!$result){
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar la existencia de la referencia.'; 
			$respuesta['Error'] = ' ['.$error.']';	
			
			$bContinue  = false;
		} else {
			if(odbc_num_rows($result) <= 0){
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='No es posible localizar la referencia en la base de datos de pedimentos.'; 
				$respuesta['Error'] = ' ['.$error.']';	
				$bContinue  = false;
			}
		}
			
		//***********************************************************//
		
		if ($bContinue) {
			$consulta="SELECT referencia
					   FROM solicitud_sellos 
					   WHERE referencia = '".$sReferencia."'";	
				
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar la existencia de la referencia.'; 
				$respuesta['Error'] = ' ['.$error.']';	
				$bContinue  = false;
			} else {
				$num_rows = mysqli_num_rows($query);
				if ($num_rows > 0){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='La referencia que desea agregar ya existe, favor de ingresar una referencia diferente.'; 
					$respuesta['Error'] = ' ['.$error.']';	
					$bContinue  = false;
				}
			}
		}
		
		//***********************************************************//
		
		if ($bContinue) {
			$consulta = "INSERT INTO solicitud_sellos 
							   (referencia
							   ,caja
							   ,usuario_id_solicita
							   ,fecha_solicitud)
						 VALUES 
							   ('".$sReferencia."'
							   ,'".$sCaja."'
							   ,".$id."
							   ,'".$fecha_registro."')";
							   
			mysqli_query($cmysqli,"BEGIN");
			$query = mysqli_query($cmysqli, $consulta);		
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al guardar la solicitud del sello. ['.$error.']'.$consulta;
				
				$bCommit = false;
			}else{
				$to=array();
				$bcc=array();
				
				array_push($to, $_SESSION['usuemail']);
				//array_push($to, 'martin@delbravo.com');
				
				$asunto="Solicitud de Sello";
				$mensaje='<img src="cid:logo.png" alt="Logo Del Bravo" width="103" height="100" /><br>';
				$mensaje.="<p>Se le notifica que el ejecutivo $username a solicitado un Sello:</p>";
				$mensaje.='<strong>Referencia: </strong>'.$sReferencia.'<br>';
				$mensaje.='<strong>Caja: </strong>'.$sCaja.'<br>';
				$mensaje.='<strong>Fecha y hora de solicitud: </strong>'.$fecha_registro.'<br>';
				$mensaje.='<strong>Nota: </strong>Para atender la solicitud es necesario ingresar a la p&aacute;gina web de webtools<br>';

				$mensaje.='<p>Este correo se ha generado de forma automatica, favor de no responder sobre el.</p>';
				$correo=enviamail($asunto, $mensaje, $to, $bcc, 'mail.delbravo.com', '25', 'avisosautomaticos@delbravo.com', 'aviaut01', '../../../images/logo.png');
				
				if ($correo['codigo']==-1){
					$respuesta['codigo']=-1;
					$respuesta['mensaje']='Error al enviar el correo de notificacion de solicitud del sello: '.$correo['mensaje'].count($to);
					
					$bCommit = false;
				}else{
					$respuesta['Codigo']=1;
					$respuesta['Mensaje']='La solicitud del sello se ha enviado correctamente!.';
				}
			}
			
			if ($bCommit) {
				mysqli_query($cmysqli,"COMMIT");
			} else {
				mysqli_query($cmysqli,"ROLLBACK");
			}
		}
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "458 : Error al recibir los datos del sello.";
	}
	echo json_encode($respuesta);
}

