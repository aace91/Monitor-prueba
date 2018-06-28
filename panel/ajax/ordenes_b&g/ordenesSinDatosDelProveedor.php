<?php
include ('./../../../connect_dbsql.php');
require_once './../../../bower_components/PHPMailer/PHPMailerAutoload.php';

$__bDebug = ((strpos($_SERVER['REQUEST_URI'], 'pruebas/') !== false)? true : false);
$__strEmailAlerta = 'jcdelacruz@delbravo.com';

$consulta="SELECT GROUP_CONCAT( bodOrd.po SEPARATOR ', ' ) AS po,
				  bodOrd.proveedor,
				 (SELECT CONCAT(IFNULL( cc1, '' ), ',',
							    IFNULL( cc2, '' ), ',',
								IFNULL( cc3, '' ), ',',
								IFNULL( cc4, '' ), ',',
								IFNULL( cc5, '' ), ',',
								IFNULL( cc6, '' ), ',',
								IFNULL( cc7, '' ), ',',
								IFNULL( cc8, '' ), ',',
								IFNULL( cc9, '' ), ',',
								IFNULL( cc10, '' )) 
				  FROM geocel_clientes 
				  WHERE f_numcli = 1359) AS ejecutivos_email,
				 (SELECT GROUP_CONCAT(email) 
				  FROM contactos_proveedores
				  WHERE id_catalogo = bodOrd.proveedor 
				  GROUP BY id_catalogo) AS prov_email
		   FROM bodega.`ordenes_b&g` AS bodOrd INNER JOIN 
			    bodega.tblbod ON tblbod.bodReferencia = bodOrd.referencia 
		   WHERE tblbod.bodsalida IS NULL AND 
				 tblbod.PORLLEGAR = 1 AND 
				 NOW( ) >= bodOrd.fecha_envio AND
				 NOT EXISTS (SELECT docs_refe.referencia 
				             FROM docs_refe LEFT JOIN 
							      docs ON docs_refe.id_doc = docs.id_doc 
							 WHERE invalido IS NULL AND 
								   referencia = tblbod.bodreferencia)
		   GROUP BY bodOrd.proveedor";
		   
$query = mysqli_query($cmysqli, $consulta);
if (!$query) {
	$respuesta['Codigo']=-1;
	$respuesta['Mensaje']='Error al consultar la lista de proveedores pendientes de documentos.'; 
	$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
	
	enviamail('ORDENESBG: ordenesSinDatosDelProveedor','ORDENESBG: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','avisosautomaticos@delbravo.com','aviaut01','',array());
	exit();
} else {
	while($row = mysqli_fetch_object($query)){
		fcn_envia_notificacion($row);
	}
}

echo '<br>Proceso Finalizado...';

/**************************************************************************************************/
/* FUNCIONES */
/**************************************************************************************************/

function fcn_emails_array($aData) { 
	$aEmails=array();

	$aEmail = explode(",",$aData);

	foreach ($aEmail as $email) {
		if(is_null($email) == false && $email != '') {
			array_push($aEmails, $email);
		}
	}

	return $aEmails;
}

/**************************************************************************************************/
/* ENVIO DE CORREOS */
/**************************************************************************************************/

function fcn_envia_notificacion($row) {
	global $__bDebug, $cmysqli;
	
	$sPo = $row->po;
	$nProveedor = $row->proveedor;
	
	/***************************************/

	$adjuntos=array();
	$bcc = array();
	$cc = fcn_emails_array($row->ejecutivos_email);
	$to = fcn_emails_array($row->prov_email);

	/***************************************/
		
	$asunto = 'Waiting for documents';
	
	$sTable = '';
	$consulta="SELECT bodOrd.po, IF(DATEDIFF(NOW(), bodOrd.fecha_envio) >= 1, '24 hours without attention', '') AS dias,
					  bodOrd.referencia, bodDocTpo.id_tpo, bodDocTpo.descripcion
			   FROM bodega.`ordenes_b&g` AS bodOrd INNER JOIN
					bodega.tblbod ON tblbod.bodReferencia=bodOrd.referencia LEFT JOIN 
					bodega.docs_refe AS bodDocsRef ON bodDocsRef.referencia=tblbod.bodReferencia LEFT JOIN
					bodega.docs AS bodDocs ON bodDocs.id_doc=bodDocsRef.id_doc LEFT JOIN
					bodega.docs_tipos AS bodDocTpo ON bodDocTpo.id_tpo=bodDocsRef.id_tpo
			   WHERE bodOrd.proveedor=".$nProveedor." AND
					  tblbod.bodsalida IS NULL AND 
					  tblbod.PORLLEGAR=1 AND
					  NOW() >= bodOrd.fecha_envio AND
					  NOT EXISTS (SELECT docs_refe.referencia 
                                  FROM docs_refe LEFT JOIN 
                                       docs ON docs_refe.id_doc = docs.id_doc 
                                  WHERE invalido IS NULL AND 
                                        referencia = tblbod.bodreferencia)";
			
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar la lista de documentos pendientes.'.$consulta; 
		$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
		
		enviamail('ORDENESBG: ordenesSinDatosDelProveedor','ORDENESBG: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','avisosautomaticos@delbravo.com','aviaut01','',array());
		exit();
	} else {
		while($row_query = mysqli_fetch_object($query)){
			$sTable .= '
			<tr style="background-color: #f2f2f2;">
				<td align="center" style="border: solid 1px #ddd;">'.$row_query->po.'</td>
				<td align="center" style="border: solid 1px #ddd;">'.$row_query->referencia.'</td>
				<td align="center" style="border: solid 1px #ddd;">'.((is_null($row_query->descripcion))? '' : $row_query->descripcion).'</td>
				<td align="center" style="border: solid 1px #ddd; '.(($row_query->dias != '')? 'color:red;' : '').'" >'.$row_query->dias.'</td>
			</tr>';
		}
	}

	if ($sTable != '') {
		$sTable = '
		<tr>
			<td colspan="8" style="padding: 15px;">
				<table width="100%" cellspacing="0" cellpadding="0">
					<tr style="background-color: #4472C4; color: #FFF; font-size:16px;">
						<td align="center" style="border: solid 1px #ccc; width:150px;">PO</td>
						<td align="center" style="border: solid 1px #ccc; width:120px;">Reference</td>
						<td align="center" style="border: solid 1px #ccc;">Required documents</td>
						<td align="center" style="border: solid 1px #ccc;">More 24 hours</td>
					</tr>
					'.$sTable.'
				</table>
			</td>
		</tr>';
	}

	/***************************************/

	$sHTML = '
	<table style="border: solid 1px #bbbccc; width: 800px;" cellspacing="0" cellpadding="0">
		<tbody>
			<tr style="background-color: #0073b7; color: #fff;">
				<td style="background-color: #fff;" width="100px">
					<img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" />
				</td>
				<td width="10">&nbsp;</td>
				<td align="center">
					<h1>&nbsp;&nbsp;&nbsp;Del Bravo&nbsp;&nbsp;&nbsp;</h1>
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
									<h2>'.utf8_decode($asunto).'</h2>
								</td>
							</tr>
							<tr>
								<td colspan="3" style="padding: 15px;">
									<div style="padding: 15px;">
										<strong>Attention!</strong> You are informed that we are waiting to receive information from the following POs. 
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="3" style="background-color: #d9edf7;">
									<div style="border:1px solid #bce8f1; color: #31708f; background-color: #d9edf7; padding: 15px;">
										<strong>PO\'s:</strong> '.utf8_decode($sPo).'
									</div>
								</td>
							</tr>
							'.$sTable.'
							<tr>
								<td colspan="3" style="padding: 15px;">
									<div style="padding: 15px;">
										To answer this request, <a href="https://www.delbravoweb.com/sii/index.php"><strong>Click here</strong></a>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="3" style="padding: 15px;">
									<p>This e-mail was generated automatically, do not answer to it, if you have any questions please contact your Account executive. Atte. Del Bravo S.A ..</p>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>';
	
	if ($__bDebug) {
		$cc=array();
		$to=array();
	}
	
	array_push($bcc,'jcdelacruz@delbravo.com');
	
	$correo=enviamail($asunto,$sHTML,$to,$cc,$bcc,'mail.delbravo.com','25','avisosautomaticos@delbravo.com','aviaut01','',$adjuntos);
	
	return $correo;
}

function enviamail($asunto,$mensaje,$to,$cc,$bcc,$mailserver,$portmailserver,$sender,$pass,$ruta_logo,$adjuntos){
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
	$mail->FromName = "Del Bravo";
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
	if (count($cc)>0){
		foreach($cc as $b){
			// Esta es la dirección a donde enviamos
			$mail->AddCC($b);
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