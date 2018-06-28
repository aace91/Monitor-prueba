<?php
	include_once('./../../../checklogin.php');
	include('./../../../connect_casa.php');
	include('generar_archivo_xml.php');
	include('generar_reporte_UMT.php');
	require_once './../../../bower_components/PHPExcel/Classes/PHPExcel.php';
	require_once './../../../bower_components/PHPMailer/PHPMailerAutoload.php';
		
	if($loggedIn == false){
		echo '500';
	} else {
		if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {  
			$respuesta['Codigo']=1;
			$referencia = $_POST['referencia'];
			$numero_parte = $_POST['numero_parte'];
			
			$Resp = crear_archivo_xml_kia($referencia, $numero_parte);
			if($Resp['Codigo'] == 1){
				$RutaXML = $Resp['NomXML'];
				//Crear Archivo EXCEL PECA
				$resPECA = crear_archivo_excel_PECA($referencia,$Resp['NomPed']);
				if($resPECA['Codigo'] == 1){
					$RutaExcelPECA = $resPECA['NomExcelPECA'];
					//Archivo UMT
					$resUMT = crear_archivo_excel_UMT($referencia,$numero_parte,$Resp['NomPed']);
					if($resUMT['Codigo'] == 1){
						$RutaExcelUMT = $resUMT['NomExcelUMT'];
						$respuesta = enviar_email_documentos_pedimento($referencia,$RutaXML,$RutaExcelPECA,$RutaExcelUMT);						
					}else{
						$respuesta = $resUMT;
					}
				}else{
					$respuesta = $resPECA;
				}
			}else{
				$respuesta = $Resp;
			}
		}else{
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
		}
		echo json_encode($respuesta);
	}

	function crear_archivo_excel_PECA($Referencia,$nPedimento){
		//CONEXION BD
		global $odbccasa;
		//Query
		$consulta = "SELECT a.PAT_AGEN, a.NUM_PEDI, a.ADU_DESP, a.CVE_PEDI, a.FEC_PAGO, CASE WHEN a.IMP_EXPO = '1' THEN 'IMPO' ELSE 'EXPO' END AS IMPO_EXPO,
							a.VAL_COME,
							(SELECT CASE WHEN SUM(TOT_IMPU) IS NULL THEN 0 ELSE SUM ( CASE WHEN TOT_IMPU IS NULL THEN 0 ELSE ROUND(TOT_IMPU) END ) END
								FROM SAAIO_CONTFRA
								WHERE NUM_REFE = a.NUM_REFE AND CVE_IMPU = 3
								GROUP BY NUM_REFE) AS IVA_IMP,
							(SELECT FPA_IMPU FROM SAAIO_CONTFRA WHERE NUM_REFE = a.NUM_REFE AND CVE_IMPU = 3 GROUP BY FPA_IMPU) AS FP_IVA,
							(SELECT CASE WHEN SUM(TOT_IMPU) IS NULL THEN 0 ELSE SUM ( CASE WHEN TOT_IMPU IS NULL THEN 0 ELSE ROUND(TOT_IMPU) END ) END
								FROM SAAIO_CONTFRA
								 WHERE NUM_REFE = a.NUM_REFE AND CVE_IMPU = 6
								 GROUP BY NUM_REFE) AS IGI_IMP,
							(SELECT FPA_IMPU FROM SAAIO_CONTFRA WHERE NUM_REFE = a.NUM_REFE AND CVE_IMPU = 6 GROUP BY FPA_IMPU) AS FP_IGI,
							CASE WHEN d.VAL_TASA IS NULL THEN 0 ELSE d.TOT_IMPU END AS DTA_IMP,
							d.FPA_IMPU AS FP_DTA,
							CASE WHEN a.FEC_PAGO > '2014-03-01 00:00:00' THEN (p.VAL_TASA - 20) ELSE 0 END AS PRV_IMP,
							o.OTROS,
							CASE WHEN a.FEC_PAGO > '2014-03-01 00:00:00' THEN (p.TOT_IMPU-(p.VAL_TASA - 20)) ELSE 0 END AS CNT_IMP,
							p.TOT_IMPU,
							cb.CTA_CENT,
							cb.CVE_BAN
					FROM SAAIO_PEDIME a
						LEFT JOIN SAAIO_CONTPED d ON
							 a.NUM_REFE = d.NUM_REFE AND 
							 d.CVE_IMPU = 1
						LEFT JOIN SAAIO_CONTPED AS p ON
										 a.NUM_REFE = p.NUM_REFE AND 
										 p.CVE_IMPU = 15
						LEFT JOIN (SELECT ROUND(SUM(
											CASE WHEN IMP_INCR  IS NULL THEN 0 ELSE 
												CASE WHEN MON_INCR = 'USD' THEN IMP_INCR * TIP_CAMB 
													ELSE 
														CASE WHEN MON_INCR = 'MXP' THEN IMP_INCR ELSE (IMP_INCR * EQU_DLLS) * TIP_CAMB END
												END
											END)) AS OTROS,NUM_REFE
									FROM VSAAIO_INCREM 
									WHERE CVE_INCR IN (4,5,6,7,8,9)
									GROUP BY NUM_REFE) o ON
							a.NUM_REFE = o.NUM_REFE
						INNER JOIN (SELECT FIRST 1 *
									FROM SAAIO_ARCHPAGO
									WHERE NUM_REFE = '".$Referencia."'
									ORDER BY FEC_CREA DESC) ap ON
							a.NUM_REFE = ap.NUM_REFE
						INNER JOIN SAAIC_CTABAN cb ON
							ap.CVE_CNTA = cb.CVE_CTA
					WHERE a.NUM_REFE = '".$Referencia."'";
					
		$resped = odbc_exec ($odbccasa, $consulta);
		if ($resped == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'EXCEL PECA :: Se genero un error al consultar la informacion de pedimentos. KIA - Valdez&WoodWard [monitor -> utilerias -> documentos_kia]'.odbc_error();
			$respuesta['Error'] = '';
			enviar_notificacion_error_reporte($respuesta['Mensaje'],$consulta);
			return $respuesta;
		}
		while(odbc_fetch_row($resped)){
			
			$customBroker = "WOODWARD";
			$patente = odbc_result($resped,"PAT_AGEN");
			$pedimento = odbc_result($resped,"NUM_PEDI");
			$customNo = odbc_result($resped,"ADU_DESP");
			$pedimentoCode = odbc_result($resped,"CVE_PEDI");
			$FEC_PAGO = odbc_result($resped,"FEC_PAGO");
			$pecaPayDate = date('d/m/Y',strtotime($FEC_PAGO));
			$impoExpo = odbc_result($resped,"IMPO_EXPO");
			$customsValue = odbc_result($resped,"VAL_COME");
			$FP_IVA = odbc_result($resped,"FP_IVA");
			if($FP_IVA == '6' || $FP_IVA == '21'){
				$vatFiscalCredit = odbc_result($resped,"IVA_IMP");
				$vatPaid = '0';
			}else{
				$vatFiscalCredit = '0';
				$vatPaid = odbc_result($resped,"IVA_IMP");
			}
			$FP_IGI = odbc_result($resped,"FP_IGI");
			if($FP_IGI == '6' || $FP_IGI == '21'){
				$igiPendign = odbc_result($resped,"IGI_IMP");
				$igiPaid = '0';
			}else{
				$igiPendign = '0';
				$igiPaid = odbc_result($resped,"IGI_IMP");
			}
			$FP_DTA = odbc_result($resped,"FP_DTA");
			if($FP_DTA == '6' || $FP_DTA == '21'){
				$dtaPending = odbc_result($resped,"DTA_IMP");
				$dtaPaid = '0';
			}else{
				$dtaPending = '0';
				$dtaPaid = odbc_result($resped,"DTA_IMP");
			}
			$prevPaid = odbc_result($resped,"PRV_IMP");
			$others = odbc_result($resped,"OTROS");
			$cntrPaid = odbc_result($resped,"CNT_IMP");
			$totalPaid = odbc_result($resped,"TOT_IMPU");
			$bankNo = odbc_result($resped,"CVE_BAN");
			switch($bankNo){
				case '05':
					$bankNo = 'BANAMEX';
					break;
				case '08':
					$bankNo = 'BANORTE';
					break;
				case '11':
					$bankNo = 'BANCOMER';
					break;
				default:
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'EXCEL PECA :: Es necesario que sistemas agregue el banco con clave ['.$bankNo.'] en la linea 427-SWITCH CASE . KIA - Valdez&WoodWard [monitor -> utilerias -> documentos_kia]'.odbc_error();
					$respuesta['Error'] = '';
					enviar_notificacion_error_reporte($respuesta['Mensaje'],$consulta);
					return $respuesta;
			}
			$accountId= odbc_result($resped,"CTA_CENT");
			$status = 'PAID'; // Se toma PAID ya que todos los pedimentos son pagados. Se asusme que se refiere a el estado del pedimento.
			$kmmld = '';// No se identifico a que se refiere esta columna (Pendiente respuesta Valdez&WoodWard) 
			$pedimentoType = 'MP - Material Productivo';// EN CASO DE USARSE OTRO PROVEEDOR ACTUALIAR VALIDACION (MP para el proveedor ANDE (BP))
			
			//EXCEL
			$objPHPExcel = new PHPExcel();
			$objWorksheet = $objPHPExcel->getActiveSheet();
			$objPHPExcel->getProperties()->setCreator("DEL BRAVO")
							->setLastModifiedBy("DEL BRAVO")
							->setTitle('KIA PECA');
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A1', 'customBroker')
						->setCellValue('B1', 'patente')
						->setCellValue('C1', 'pedimento')
						->setCellValue('D1', 'customNo')
						->setCellValue('E1', 'pedimentoCode')
						->setCellValue('F1', 'pecaPayDate')
						->setCellValue('G1', 'impoExpo')
						->setCellValue('H1', 'customsValue')
						->setCellValue('I1', 'vatFiscalCredit')
						->setCellValue('J1', 'vatPaid')
						->setCellValue('K1', 'igiPaid')
						->setCellValue('L1', 'igiPendign')
						->setCellValue('M1', 'dtaPaid')
						->setCellValue('N1', 'dtaPending')
						->setCellValue('O1', 'prevPaid')
						->setCellValue('P1', 'others')
						->setCellValue('Q1', 'cntrPaid')
						->setCellValue('R1', 'totalPaid')
						->setCellValue('S1', 'bankNo')
						->setCellValue('T1', 'accountId')
						->setCellValue('U1', 'status')
						->setCellValue('V1', 'kmmld')
						->setCellValue('W1', 'pedimentoType');
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A2', $customBroker)
						->setCellValue('B2', $patente)
						->setCellValue('C2', $pedimento)
						->setCellValue('D2', $customNo)
						->setCellValue('E2', $pedimentoCode)
						->setCellValue('F2', $pecaPayDate)
						->setCellValue('G2', $impoExpo)
						->setCellValue('H2', $customsValue)
						->setCellValue('I2', $vatFiscalCredit)
						->setCellValue('J2', $vatPaid)
						->setCellValue('K2', $igiPaid)
						->setCellValue('L2', $igiPendign)
						->setCellValue('M2', $dtaPaid)
						->setCellValue('N2', $dtaPending)
						->setCellValue('O2', $prevPaid)
						->setCellValue('P2', $others)
						->setCellValue('Q2', $cntrPaid)
						->setCellValue('R2', $totalPaid)
						->setCellValue('S2', $bankNo)
						->setCellValue('T2', $accountId)
						->setCellValue('U2', $status)
						->setCellValue('V2', $kmmld)
						->setCellValue('W2', $pedimentoType);
						
			foreach(range('A','W') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}
			/*$objPHPExcel->getActiveSheet()
						->getStyle('D2:D'.$renglon)
						->getAlignment()
						->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$objPHPExcel->getActiveSheet()
						->getStyle('E2:J'.$renglon)
						->getAlignment()
						->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);*/
			$objPHPExcel->getActiveSheet()->getColumnDimension();
			$objPHPExcel->getActiveSheet()->setTitle('Sheet1');	//No se agrega nombre a hoja para este reporte
			$objPHPExcel->setActiveSheetIndex(0);
			try{
				
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				$nfile = 'documentos/'.$nPedimento.'_PECA.xlsx';
				$objWriter->setIncludeCharts(TRUE);
				$objWriter->save($nfile);
				
				$respuesta['Codigo'] = 1;	
				$respuesta ['NomExcelPECA'] = $nfile;
				return $respuesta;
				
			} catch (Exception $e) {
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'EXCEL :: Error al genererar el archivo Excel PECA. [Referencia: '.$Referencia.']['.$e->getMessage().'][monitor -> utilerias -> documentos_kia]';
				$respuesta['Error'] = '';
				enviar_notificacion_error_reporte($respuesta['Mensaje'],'');
				return $respuesta;
			}
		}
	}
		
	function enviar_notificacion_error_reporte($Body,$Query){
		$mensaje = '<table style="border: solid 1px #bbbccc; width: 900px;" cellspacing="0" cellpadding="0">
					<tbody>
						<tr style="background-color:#d9534f; color:#fff;">
							<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoweb.com/monitor/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
							<td width="10px">&nbsp;</td>
							<td align="center">
								<h1>REPORTE DIARIO KIA - VALDEZ&WOODWARD</h1>
							</td>
							<td width="10px">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4">
								<table width="100%" cellspacing="0" cellpadding="3">
									<tbody>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr><td colspan="8" align="left"><h3>'.$Body.'</h3></td></tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr><td colspan="8" align="center" style="border: 1px solid #999; background-color:#EEE;"><strong>DETALLE</strong></td></tr>
										<tr><td colspan="8" align="left" style="border: 1px solid #999;">'.$Query.'</td></tr>
										<tr><td colspan="8">&nbsp;</td></tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>';
		$asunto = "ERROR :: Reporte Diario KIA - VALDEZ&WOODWARD :: Error al generar reporte diario.";
		$to = array();$adjuntos = array();
		array_push($to,'marco@delbravo.com');
		$RespEmail = enviamail($asunto,$mensaje,$to,$adjuntos);
	}

	function enviar_email_documentos_pedimento($referencia,$RutaXML,$RutaExcelPECA,$RutaExcelUMT){
		//Armar HTML notificacion DOCUMENTOS
		$sHTML = '<table style="border: solid 1px #bbbccc; width: 900px;" cellspacing="0" cellpadding="0">
					<tbody>
						<tr style="background-color: #0073b7; color: #fff;">
							<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoweb.com/monitor/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
							<td width="10px">&nbsp;</td>
							<td align="center">
								<h1>Grupo Aduanero Del Bravo</h1>
							</td>
							<td width="10px">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4">
								<table width="100%" cellspacing="0" cellpadding="0">
									<tbody>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr>
											<td colspan="8" align="left"><h3>Cliente: KIA MOTORS MEXICO S.A. DE C.V.</h3></td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>
										<tr>
											<td colspan="8" align="left"><h3>Referencia: '.$referencia.'</h3></td>
										</tr>
										<tr><td colspan="8">&nbsp;</td></tr>										
										<tr>
											<td colspan="8">
											<p>Este correo fue enviado de forma autom&aacute;tica y no es necesario responder al mismo. &iexcl;Muchas Gracias!.</p>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>';
		
		
		$asunto = 'Documentacion de Pedimentos. KIA MOTORS MEXICO S.A. DE C.V. | Referencia: '.$referencia;
		$mensaje = $sHTML;
		$to = array(); $adjuntos = array();
		array_push($to,'luis.perez@delbravo.com');
		
		array_push($adjuntos,$RutaXML);
		array_push($adjuntos,$RutaExcelPECA);
		array_push($adjuntos,$RutaExcelUMT);
		
		$respuesta = enviamail($asunto,$mensaje,$to,$adjuntos);
		
		unlink($RutaXML);
		unlink($RutaExcelPECA);
		unlink($RutaExcelUMT);
		
		return $respuesta;
	}
	
	function enviamail($asunto,$mensaje,$to,$adjuntos){
		$mail = new PHPMailer();
		//Luego tenemos que iniciar la validación por SMTP:
		$mail->IsSMTP();$mail->SMTPAuth = true;
		$mail->Host = 'mail.delbravo.com';
		$mail->Username = 'avisosautomaticos@delbravo.com';
		$mail->Password = 'aviaut01';$mail->Port = '25';
		$mail->From = 'avisosautomaticos@delbravo.com'; // Desde donde enviamos (Para mostrar)
		$mail->FromName = 'Avisos Automaticos Del Bravo';
		
		//$mail->AddAttachment('../images/logo.png', 'logo.png'); 
		
		for($x=0;$x<count($adjuntos);$x++){
			$Nombre = explode('/',$adjuntos[$x]);
			$mail->AddAttachment($adjuntos[$x],$Nombre[1]);
		}
		//Estas dos líneas, cumplirían la función de encabezado (En mail() usado de esta forma: “From: Nombre <correo@dominio.com>”) de //correo.
		if (count($to)>0){
			foreach($to as $t){
				// Esta es la dirección a donde enviamos
				$mail->AddAddress($t);
			}
		}
		$mail->AddBcc('marco@delbravo.com');
		
		$mail->IsHTML(true); // El correo se envía como HTML
		$mail->Subject = $asunto; // Este es el titulo del email.
		$mail->Body = $mensaje; // Mensaje a enviar
		$exito = $mail->Send(); // Envía el correo.

		//También podríamos agregar simples verificaciones para saber si se envió:
		if($exito){
			$respuesta['Codigo']=1;
			$respuesta['Mensaje']='La documentacion fue enviada correctamente!.';
		}else{
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']=$mail->ErrorInfo;
		}
		return $respuesta;
	}
