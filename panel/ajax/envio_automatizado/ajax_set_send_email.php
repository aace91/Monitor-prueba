<?php
include_once('./../../../checklogin.php');
include('./../../../connect_dbsql.php');
require_once './../../../bower_components/PHPMailer/PHPMailerAutoload.php';

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);

if (isset($_POST['sAsunto']) && !empty($_POST['sAsunto'])) {
	$respuesta['Codigo'] = 1;	
		
	//***********************************************************//
	
	$sAsunto = utf8_decode($_POST['sAsunto']);
    $sMensaje = utf8_decode($_POST['sMensaje']);
	$sEmail = utf8_decode($_POST['sEmail']);
	$files = $_FILES;
	
	//***********************************************************//
	
	$fecha_registro =  date("Y-m-d H:i:s");
	$adjuntos=array();
		
	//***********************************************************//
	
	if(count($files) > 0) {
		foreach($files as $file){
			$target = sys_get_temp_dir() .'\\'. $file['name'];
			
			if(move_uploaded_file($file['tmp_name'], $target)) {
				array_push($adjuntos, array('dir' => $target, 'name' => $file['name']));
			}
		}
	}
	
	$fec1= new DateTime();
	$fec3= date_format($fec1, 'd/m/Y h:i a');
	
	$bcc=array();
	$to=array();	
	array_push($to,$sEmail);
	envia_notificacion($adjuntos, $fec3, $sAsunto, $sMensaje, $bcc, $to);
	
	if ($bDebug) {
		$respuesta['Debug'] = $bDebug;
	}

	$respuesta['Mensaje'] = 'Correo enviado correctamente.';
	$respuesta['sEmail'] = $sEmail;
} else{
	$respuesta['Codigo']=-1;
	$respuesta['Mensaje'] = 'No se recibieron datos';
}

echo json_encode($respuesta);

function envia_notificacion($adjuntos, $fec_gen, $sAsunto, $Mensaje, $bcc, $to){
	global $bDebug;

	$sMensaje='
	<!DOCTYPE html>
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf8_encode" />
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
					<!--table width="600" cellpadding="0" cellspacing="0" border="0">
					   <tbody>
						  <tr>
							 <td id="itd_titulo_alerta_color"width="15" bgcolor="#4E7EC1"></td>
							 <td id="itd_titulo_alerta_color2" width="500" align="left" valign="middle" bgcolor="#4E7EC1" style="font-family:Arial, Helvetica, sans-serif;font-size:20px;color:#ffffff;">
								<strong>'.$sAsunto.'</strong>
							 </td>
							 <td>
								<img src="http://www.delbravo.com/es/wp-content/uploads/2015/11/delbravo-logo.png" width="85" height="85" border="0" style="display:block;">
							 </td>
						  </tr>
					   </tbody>
					</table>
					<div class="navigation_menu">				
						<img src="http://delbravoweb.com/admin_clientes/images/email_top_barra.png" width="100%" height="20px" border="0" style="display:block;">
					</div-->

					<table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFFF">
						<tbody>
							<tr>
								<td width="15">&nbsp;</td>
								<td id="itd_detalles_notificacion" width="534" valign="top" align="left" style="font-family:Arial, Helvetica, sans-serif;color:#626262;font-size:14px;">
									'.$Mensaje.'
								</td>
								<td width="6" valign="top"></td>
								<td width="30" valign="top" align="center"></td>
								<td width="15"></td>
							</tr>
						</tbody>
					</table>
				</center>
				<!--br/>
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
				</center-->
			</body>
		</html>';
	

	$sMensaje = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml" xmlns="http://www.w3.org/1999/xhtml"><head>
	<!-- NAME: ANNOUNCE -->
	<!--[if gte mso 15]>
	<xml>
		<o:OfficeDocumentSettings>
		<o:AllowPNG/>
		<o:PixelsPerInch>96</o:PixelsPerInch>
		</o:OfficeDocumentSettings>
	</xml>
	<![endif]-->
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Feliz Navidad (Prueba)</title>

<style type="text/css">
	p{
		margin:10px 0;
		padding:0;
	}
	table{
		border-collapse:collapse;
	}
	h1,h2,h3,h4,h5,h6{
		display:block;
		margin:0;
		padding:0;
	}
	img,a img{
		border:0;
		height:auto;
		outline:none;
		text-decoration:none;
	}
	body,#bodyTable,#bodyCell{
		height:100%;
		margin:0;
		padding:0;
		width:100%;
	}
	.mcnPreviewText{
		display:none !important;
	}
	#outlook a{
		padding:0;
	}
	img{
		-ms-interpolation-mode:bicubic;
	}
	table{
		mso-table-lspace:0pt;
		mso-table-rspace:0pt;
	}
	.ReadMsgBody{
		width:100%;
	}
	.ExternalClass{
		width:100%;
	}
	p,a,li,td,blockquote{
		mso-line-height-rule:exactly;
	}
	a[href^=tel],a[href^=sms]{
		color:inherit;
		cursor:default;
		text-decoration:none;
	}
	p,a,li,td,body,table,blockquote{
		-ms-text-size-adjust:100%;
		-webkit-text-size-adjust:100%;
	}
	.ExternalClass,.ExternalClass p,.ExternalClass td,.ExternalClass div,.ExternalClass span,.ExternalClass font{
		line-height:100%;
	}
	a[x-apple-data-detectors]{
		color:inherit !important;
		text-decoration:none !important;
		font-size:inherit !important;
		font-family:inherit !important;
		font-weight:inherit !important;
		line-height:inherit !important;
	}
	.templateContainer{
		max-width:600px !important;
	}
	a.mcnButton{
		display:block;
	}
	.mcnImage{
		vertical-align:bottom;
	}
	.mcnTextContent{
		word-break:break-word;
	}
	.mcnTextContent img{
		height:auto !important;
	}
	.mcnDividerBlock{
		table-layout:fixed !important;
	}
	h1{
		color:#222222;
		font-family:Helvetica;
		font-size:40px;
		font-style:normal;
		font-weight:bold;
		line-height:150%;
		letter-spacing:normal;
		text-align:center;
	}
	h2{
		color:#222222;
		font-family:Helvetica;
		font-size:34px;
		font-style:normal;
		font-weight:bold;
		line-height:150%;
		letter-spacing:normal;
		text-align:left;
	}
	h3{
		color:#444444;
		font-family:Helvetica;
		font-size:22px;
		font-style:normal;
		font-weight:bold;
		line-height:150%;
		letter-spacing:normal;
		text-align:left;
	}
	h4{
		color:#999999;
		font-family:Georgia;
		font-size:20px;
		font-style:italic;
		font-weight:normal;
		line-height:125%;
		letter-spacing:normal;
		text-align:center;
	}
	#templateHeader{
		background-color:#F7F7F7;
		background-image:none;
		background-repeat:no-repeat;
		background-position:center;
		background-size:cover;
		border-top:0;
		border-bottom:0;
		padding-top:54px;
		padding-bottom:54px;
	}
	.headerContainer{
		background-color:transparent;
		background-image:none;
		background-repeat:no-repeat;
		background-position:center;
		background-size:cover;
		border-top:0;
		border-bottom:0;
		padding-top:0;
		padding-bottom:0;
	}
	.headerContainer .mcnTextContent,.headerContainer .mcnTextContent p{
		color:#808080;
		font-family:Helvetica;
		font-size:16px;
		line-height:150%;
		text-align:left;
	}
	.headerContainer .mcnTextContent a,.headerContainer .mcnTextContent p a{
		color:#00ADD8;
		font-weight:normal;
		text-decoration:underline;
	}
	#templateBody{
		background-color:#FFFFFF;
		background-image:none;
		background-repeat:no-repeat;
		background-position:center;
		background-size:cover;
		border-top:0;
		border-bottom:0;
		padding-top:36px;
		padding-bottom:54px;
	}
	.bodyContainer{
		background-color:transparent;
		background-image:none;
		background-repeat:no-repeat;
		background-position:center;
		background-size:cover;
		border-top:0;
		border-bottom:0;
		padding-top:0;
		padding-bottom:0;
	}
	.bodyContainer .mcnTextContent,.bodyContainer .mcnTextContent p{
		color:#808080;
		font-family:Helvetica;
		font-size:16px;
		line-height:150%;
		text-align:left;
	}
	.bodyContainer .mcnTextContent a,.bodyContainer .mcnTextContent p a{
		color:#00ADD8;
		font-weight:normal;
		text-decoration:underline;
	}
	#templateFooter{
		background-color:#333333;
		background-image:none;
		background-repeat:no-repeat;
		background-position:center;
		background-size:cover;
		border-top:0;
		border-bottom:0;
		padding-top:45px;
		padding-bottom:63px;
	}
	.footerContainer{
		background-color:transparent;
		background-image:none;
		background-repeat:no-repeat;
		background-position:center;
		background-size:cover;
		border-top:0;
		border-bottom:0;
		padding-top:0;
		padding-bottom:0;
	}
	.footerContainer .mcnTextContent,.footerContainer .mcnTextContent p{
		color:#FFFFFF;
		font-family:Helvetica;
		font-size:12px;
		line-height:150%;
		text-align:center;
	}
	.footerContainer .mcnTextContent a,.footerContainer .mcnTextContent p a{
		color:#FFFFFF;
		font-weight:normal;
		text-decoration:underline;
	}
@media only screen and (min-width:768px){
	.templateContainer{
		width:600px !important;
	}

}	@media only screen and (max-width: 480px){
	body,table,td,p,a,li,blockquote{
		-webkit-text-size-adjust:none !important;
	}

}	@media only screen and (max-width: 480px){
	body{
		width:100% !important;
		min-width:100% !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnImage{
		width:100% !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnCartContainer,.mcnCaptionTopContent,.mcnRecContentContainer,.mcnCaptionBottomContent,.mcnTextContentContainer,.mcnBoxedTextContentContainer,.mcnImageGroupContentContainer,.mcnCaptionLeftTextContentContainer,.mcnCaptionRightTextContentContainer,.mcnCaptionLeftImageContentContainer,.mcnCaptionRightImageContentContainer,.mcnImageCardLeftTextContentContainer,.mcnImageCardRightTextContentContainer,.mcnImageCardLeftImageContentContainer,.mcnImageCardRightImageContentContainer{
		max-width:100% !important;
		width:100% !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnBoxedTextContentContainer{
		min-width:100% !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnImageGroupContent{
		padding:9px !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnCaptionLeftContentOuter .mcnTextContent,.mcnCaptionRightContentOuter .mcnTextContent{
		padding-top:9px !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnImageCardTopImageContent,.mcnCaptionBottomContent:last-child .mcnCaptionBottomImageContent,.mcnCaptionBlockInner .mcnCaptionTopContent:last-child .mcnTextContent{
		padding-top:18px !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnImageCardBottomImageContent{
		padding-bottom:9px !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnImageGroupBlockInner{
		padding-top:0 !important;
		padding-bottom:0 !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnImageGroupBlockOuter{
		padding-top:9px !important;
		padding-bottom:9px !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnTextContent,.mcnBoxedTextContentColumn{
		padding-right:18px !important;
		padding-left:18px !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnImageCardLeftImageContent,.mcnImageCardRightImageContent{
		padding-right:18px !important;
		padding-bottom:0 !important;
		padding-left:18px !important;
	}

}	@media only screen and (max-width: 480px){
	.mcpreview-image-uploader{
		display:none !important;
		width:100% !important;
	}

}	@media only screen and (max-width: 480px){
	h1{
		font-size:30px !important;
		line-height:125% !important;
	}

}	@media only screen and (max-width: 480px){
	h2{
		font-size:26px !important;
		line-height:125% !important;
	}

}	@media only screen and (max-width: 480px){
	h3{
		font-size:20px !important;
		line-height:150% !important;
	}

}	@media only screen and (max-width: 480px){
	h4{
		font-size:18px !important;
		line-height:150% !important;
	}

}	@media only screen and (max-width: 480px){
	.mcnBoxedTextContentContainer .mcnTextContent,.mcnBoxedTextContentContainer .mcnTextContent p{
		font-size:14px !important;
		line-height:150% !important;
	}

}	@media only screen and (max-width: 480px){
	.headerContainer .mcnTextContent,.headerContainer .mcnTextContent p{
		font-size:16px !important;
		line-height:150% !important;
	}

}	@media only screen and (max-width: 480px){
	.bodyContainer .mcnTextContent,.bodyContainer .mcnTextContent p{
		font-size:16px !important;
		line-height:150% !important;
	}

}	@media only screen and (max-width: 480px){
	.footerContainer .mcnTextContent,.footerContainer .mcnTextContent p{
		font-size:14px !important;
		line-height:150% !important;
	}

}</style></head>
<body style="margin: 0px; padding: 0px; width: 100%; height: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
	<!--
-->
	<center>
		<table width="100%" height="100%" align="center" id="bodyTable" style="margin: 0px; padding: 0px; width: 100%; height: 100%; border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
			<tbody><tr>
				<td align="center" id="bodyCell" valign="top" style="margin: 0px; padding: 0px; width: 100%; height: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;">
					<!-- BEGIN TEMPLATE // -->
					<table width="100%" style="border-collapse: collapse; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
						<tbody>
						<tr>
							<td align="center" id="templateBody" valign="top" style="background: no-repeat center / cover rgb(255, 255, 255); padding-top: 36px; padding-bottom: 0px; border-top-color: currentColor; border-bottom-color: currentColor; border-top-width: 0px; border-bottom-width: 0px; border-top-style: none; border-bottom-style: none; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;" data-template-container="">
								<!--[if (gte mso 9)|(IE)]>
								<table align="center" border="0" cellspacing="0" cellpadding="0" width="600" style="width:600px;">
								<tr>
								<td align="center" valign="top" width="600" style="width:600px;">
								<![endif]-->
								<table width="100%" align="center" class="templateContainer" style="border-collapse: collapse; max-width: 600px !important; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
									<tbody><tr>
										<td class="bodyContainer" valign="top" style="background: no-repeat center / cover; padding-top: 0px; padding-bottom: 0px; border-top-color: currentColor; border-bottom-color: currentColor; border-top-width: 0px; border-bottom-width: 0px; border-top-style: none; border-bottom-style: none; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;"><table width="100%" class="mcnTextBlock" style="border-collapse: collapse; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
<tbody class="mcnTextBlockOuter">
	<tr>
		<td class="mcnTextBlockInner" valign="top" style="padding-top: 9px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;">
			  <!--[if mso]>
			<table align="left" border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%;">
			<tr>
			<![endif]-->
		
			<!--[if mso]>
			<td valign="top" width="600" style="width:600px;">
			<![endif]-->
			<table width="100%" align="left" class="mcnTextContentContainer" style="border-collapse: collapse; min-width: 100%; max-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
				<tbody><tr>

					<td class="mcnTextContent" valign="top" style="padding: 0px 18px 9px; text-align: left; color: rgb(128, 128, 128); line-height: 150%; font-family: Helvetica; font-size: 16px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;">

						<h1 style="margin: 0px; padding: 0px; text-align: center; color: rgb(34, 34, 34); line-height: 150%; letter-spacing: normal; font-family: Helvetica; font-size: 40px; font-style: normal; font-weight: bold; display: block;">!Feliz navidad!</h1>

					</td>
				</tr>
			</tbody></table>
			<!--[if mso]>
			</td>
			<![endif]-->

			<!--[if mso]>
			</tr>
			</table>
			<![endif]-->
		</td>
	</tr>
</tbody>
</table><table width="100%" class="mcnImageBlock" style="border-collapse: collapse; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
<tbody class="mcnImageBlockOuter">
		<tr>
			<td class="mcnImageBlockInner" valign="top" style="padding: 9px; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;">
				<table width="100%" align="left" class="mcnImageContentContainer" style="border-collapse: collapse; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
					<tbody><tr>
						<td class="mcnImageContent" valign="top" style="padding: 0px 9px; text-align: center; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;">


									<img width="564" align="middle" class="mcnImage" style="border: 0px currentColor; border-image: none; height: auto; padding-bottom: 0px; text-decoration: none; vertical-align: bottom; display: inline !important; -ms-interpolation-mode: bicubic; max-width: 800px;" alt="" src="https://gallery.mailchimp.com/a19ce3fc60be4d064f64a049d/images/d41b3f83-5687-4000-b73b-908df23e3a97.png">


						</td>
					</tr>
				</tbody></table>
			</td>
		</tr>
</tbody>
</table><table width="100%" class="mcnDividerBlock" style="border-collapse: collapse; table-layout: fixed !important; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
<tbody class="mcnDividerBlockOuter">
	<tr>
		<td class="mcnDividerBlockInner" style="padding: 27px 18px 0px; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;">
			<table width="100%" class="mcnDividerContent" style="border-collapse: collapse; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
				<tbody><tr>
					<td style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;">
						<span></span>
					</td>
				</tr>
			</tbody></table>
<!--
			<td class="mcnDividerBlockInner" style="padding: 18px;">
			<hr class="mcnDividerContent" style="border-bottom-color:none; border-left-color:none; border-right-color:none; border-bottom-width:0; border-left-width:0; border-right-width:0; margin-top:0; margin-right:0; margin-bottom:0; margin-left:0;" />
-->
		</td>
	</tr>
</tbody>
</table><table width="100%" class="mcnDividerBlock" style="border-collapse: collapse; table-layout: fixed !important; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
<tbody class="mcnDividerBlockOuter">
	<tr>
		<td class="mcnDividerBlockInner" style="padding: 9px 18px 0px; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;">
			<table width="100%" class="mcnDividerContent" style="border-collapse: collapse; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" border="0" cellspacing="0" cellpadding="0">
				<tbody><tr>
					<td style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; mso-line-height-rule: exactly;">
						<span></span>
					</td>
				</tr>
			</tbody></table>
<!--
			<td class="mcnDividerBlockInner" style="padding: 18px;">
			<hr class="mcnDividerContent" style="border-bottom-color:none; border-left-color:none; border-right-color:none; border-bottom-width:0; border-left-width:0; border-right-width:0; margin-top:0; margin-right:0; margin-bottom:0; margin-left:0;" />
-->
		</td>
	</tr>
</tbody>
</table></td>
									</tr>
								</tbody></table>
								<!--[if (gte mso 9)|(IE)]>
								</td>
								</tr>
								</table>
								<![endif]-->
							</td>
						</tr>
						
					</tbody></table>
					<!-- // END TEMPLATE -->
				</td>
			</tr>
		</tbody></table>
	</center>
<img width="1" height="1" src="https://delbravo.us17.list-manage.com/track/open.php?u=a19ce3fc60be4d064f64a049d&amp;id=2d8c43b36e&amp;e=0660294465">
</body></html>';

	/*if ($bDebug) {
		$bcc=array();
		$to=array();
		
		array_push($to,'jcdelacruz@delbravo.com');
	}*/
	
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
	}
	return $respuesta;
}