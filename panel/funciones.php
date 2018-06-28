<?php
include_once('./../checklogin.php');
include('../phpmailer/PHPMailerAutoload.php');
if($loggedIn == false){ 
	$mensaje= "<a href='./../login.php'>Su sesión expiro favor de ingresar nuevamente</a>";
	$response['codigo'] = -1;
    $response['mensaje'] = $mensaje;
    echo json_encode($response);
}

if (isset($_POST['action']) && !empty($_POST['action'])) {
    $action = $_POST['action'];
    switch ($action) {
		case 'solicitadoc' : $respuesta = solicitanom($_POST['referencia'],$_POST['tipodoc']);
			echo json_encode($respuesta);
            break;
	}
}

function conn1(){
	include('../db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		error_log(json_encode($response));
		return $response;
	}
	$response['codigo'] = 1;
	$response['mensaje'] = 'Conexión exitosa';
	$response['conexion'] = $cmysqli;
	return $response;
}

function solicitanom($referencia,$tipodoc){
	$to=array();
	$bcc=array();
	$conecta=conn1();
	if($conecta['codigo']!=1){
		$response['codigo'] = -1;
		$response['mensaje'] = $conecta['mensaje'];
		return $response;
	}
	$cmysqli=$conecta['conexion'];
	if($tipodoc==1){
		$doc="req_car_nom";
		$m1="carta NOM";
		$m2="una ".$m1;
	}
	if($tipodoc==2){
		$doc="req_cer_ori";
		$m1="certificado de origen";
		$m2="un ".$m1;
	}
	$consulta="INSERT INTO tblbodcom (referencia, $doc)
		VALUES
			('$referencia', '".date("Y-m-d H:i:s")."') ON DUPLICATE KEY UPDATE $doc = '".date("Y-m-d H:i:s")."'";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['codigo']=-1;
		$respuesta['mensaje']='Error en consulta: ' .$error ;
		return $respuesta;
	}
	array_push($bcc,'abisaicruz@delbravo.com');
	$consulta="SELECT
		bod.boddescmer as descripcion,
		DATE_FORMAT(com.$doc, '%d/%m/%Y %h:%i %p') as fec_sol,
		g.to1,
		g.to2,
		g.to3,
		g.to4,
		g.to5,
		g.to6,
		g.to7,
		g.to8,
		g.to9,
		g.to10,
		g.cc1,
		g.cc2,
		g.cc3,
		g.cc4,
		g.cc5,
		g.cc6,
		g.cc7,
		g.cc8,
		g.cc9,
		g.cc10
	FROM
		tblbod as bod 
		left join tblbodcom as com
		on bod.bodreferencia=com.referencia
		left join geocel_clientes AS g 
		on bod.bodcli=g.f_numcli
	WHERE
		bod.bodreferencia = '$referencia'";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['codigo']=-1;
		$respuesta['mensaje']='Error en consulta: ' .$error ;
		return $respuesta;
	}
	while($row = $query->fetch_object()){ 
		for ($i = 1; $i <= 10; $i++) {
			$row2= get_object_vars($row);
			$gto=$row2['to'.$i];
			if($to!='' or $gto!=NULL){
				array_push($to,$gto);
			}
		}
		for ($i = 1; $i <= 10; $i++) {
			$row2= get_object_vars($row);
			$gcc=$row2['cc'.$i];
			if($to!='' or $gcc!=NULL){
				array_push($to,$gcc);
			}
		}
		$descripcion=$row->descripcion;
		$fec_sol=$row->fec_sol;
	}
	if(count($to)<=0){
		$respuesta['codigo']=1;
		$respuesta['mensaje']='No se envio el correo por que no hay ningun remitente registrado';
		return $respuesta;
	}
	mysqli_close($cmysqli);
	$asunto="Solicitud de $m1, $referencia";
	$mensaje='<img src="cid:logo.png" alt="Logo Del Bravo" width="103" height="100" /><br>';
	$mensaje.="<p>Se le notifica que el ejecutivo a solicitado $m2:</p>";
	$mensaje.='<strong>Referencia: </strong>'.$referencia.'<br>';
	$mensaje.='<strong>Descripcion mercancia: </strong>'.$descripcion.'<br>';
	$mensaje.='<strong>Fecha y hora de solicitud: </strong>'.$fec_sol.'<br>';

	$mensaje.='<p>Este correo se ha generado de forma automatica, favor de no responder sobre el.</p>';
	$correo=enviamail($asunto,$mensaje,$to,$bcc,'mail.delbravo.com','25','avisosautomaticos@delbravo.com','aviaut01','../images/logo.png');
	if ($correo['codigo']==-1){
		$respuesta['codigo']=-1;
		$respuesta['mensaje']='Error al enviar el correo de notificacion de entrada: '.$correo['mensaje'].count($to);
	}else{
		$respuesta['codigo']=1;
		$respuesta['mensaje']=$correo['mensaje'];
	}
	return $respuesta;
}

function enviamail($asunto,$mensaje,$to,$bcc,$mailserver,$portmailserver,$sender,$pass,$ruta_logo){
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