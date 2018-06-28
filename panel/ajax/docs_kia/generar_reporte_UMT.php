<?php
	require_once './../../../bower_components/PHPExcel/Classes/PHPExcel.php';
	function crear_archivo_excel_UMT($Referencia,$NumeroParte,$nPedimento){
		//CONEXION BD
		global $odbccasa;
		//QUERY
		$consulta = " SELECT a.PAT_AGEN,a.NUM_PEDI,a.ADU_DESP, f.NUM_FACT,fr.FRACCION,
							fr.NUM_PART,p.CAN_FACT,p.UNI_FACT,p.CAN_TARI,p.UNI_TARI,p.DES_MERC,pv.CVE_PROC,fr.PAI_ORIG
						FROM SAAIO_PEDIME a
							INNER JOIN SAAIO_FACTUR f ON
								a.NUM_REFE = f.NUM_REFE
							INNER JOIN CTRAC_PROVED pv ON
								f.CVE_PROV = pv.CVE_PRO
							INNER JOIN SAAIO_FACPAR p ON 
								f.NUM_REFE = p.NUM_REFE and
								f.CONS_FACT = p.CONS_FACT
							LEFT JOIN SAAIO_PARCONS pf ON
								p.NUM_REFE = pf.NUM_REFE and
								p.CONS_FACT = pf.CONS_FACT and
								p.CONS_PART = pf.CONS_PART
							LEFT JOIN SAAIO_FRACCI fr ON
								pf.NUM_REFE = fr.NUM_REFE and
								PF.CONS_FRA = fr.NUM_PART
						WHERE a.NUM_REFE = '".$Referencia."'";
					
		$resped = odbc_exec ($odbccasa, $consulta);
		if ($resped == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'EXCEL PECA :: Se genero un error al consultar la informacion de pedimentos. KIA - Valdez&WoodWard [monitor -> utilerias -> documentos_kia]'.odbc_error();
			$respuesta['Error'] = '';
			enviar_notificacion_error_reporte($respuesta['Mensaje'],$consulta);
			return $respuesta;
		}
		//EXCEL
		$objPHPExcel = new PHPExcel();
		$objWorksheet = $objPHPExcel->getActiveSheet();
		$objPHPExcel->getProperties()->setCreator("Departamento de Sistemas")
						->setLastModifiedBy("Departamento de Sistemas")
						->setTitle('KIA UMT');
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', 'Patente')
					->setCellValue('B1', 'Pedimento')
					->setCellValue('C1', 'Aduana')
					->setCellValue('D1', 'Factura')
					->setCellValue('E1', 'No. Parte')
					->setCellValue('F1', 'Fracción')
					->setCellValue('G1', 'Secuencia')
					->setCellValue('H1', 'CantUMC')
					->setCellValue('I1', 'UMC')
					->setCellValue('J1', 'Cantidad Tarifa')
					->setCellValue('K1', 'UMT')
					->setCellValue('L1', 'Factor UMC UMT')
					->setCellValue('M1', 'Descripción')
					->setCellValue('N1', 'Tipo')
					->setCellValue('O1', 'Clave Proveedor')
					->setCellValue('P1', 'Pais');
		$renglon = 1;
		while(odbc_fetch_row($resped)){
			$Patente = odbc_result($resped,"PAT_AGEN");
			$Pedimento = odbc_result($resped,"NUM_PEDI");
			$Aduana = odbc_result($resped,"ADU_DESP");
			$Factura = odbc_result($resped,"NUM_FACT");
			$No_Parte = $NumeroParte;
			$Fraccion = odbc_result($resped,"FRACCION");
			$Secuencia = odbc_result($resped,"NUM_PART");
			$CantUMC = odbc_result($resped,"CAN_FACT");
			$UMC = odbc_result($resped,"UNI_FACT");
			$Cantidad_Tarifa = odbc_result($resped,"CAN_TARI");
			$UMT = odbc_result($resped,"UNI_TARI");
			$Factor_UMC_UMT = '0';//Todos los ejemplos vienen asi, en el pediemnto no existe un campo como tal.
			$Descripcion = odbc_result($resped,"DES_MERC");
			$Tipo = 'MP';//******************** SOLAMENTE PARA EL PROVERDOR ANDE
			$Clave_Proveedor = odbc_result($resped,"CVE_PROC");
			$Pais = odbc_result($resped,"PAI_ORIG");
			
			$renglon++;
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$renglon, $Patente)
				->setCellValue('B'.$renglon, $Pedimento)
				->setCellValue('C'.$renglon, $Aduana)
				->setCellValue('D'.$renglon, $Factura)
				->setCellValue('E'.$renglon, $No_Parte)
				->setCellValue('F'.$renglon, $Fraccion)
				->setCellValue('G'.$renglon, $Secuencia)
				->setCellValue('H'.$renglon, $CantUMC)
				->setCellValue('I'.$renglon, $UMC)
				->setCellValue('J'.$renglon, $Cantidad_Tarifa)
				->setCellValue('K'.$renglon, $UMT)
				->setCellValue('L'.$renglon, $Factor_UMC_UMT)
				->setCellValue('M'.$renglon, $Descripcion)
				->setCellValue('N'.$renglon, $Tipo)
				->setCellValue('O'.$renglon, $Clave_Proveedor)
				->setCellValue('P'.$renglon, $Pais);
		}
		foreach(range('A','P') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}
		$objPHPExcel->getActiveSheet()->getColumnDimension();
		$objPHPExcel->getActiveSheet()->setTitle('Sheet1');	//No se agrega nombre a hoja para este reporte
		$objPHPExcel->setActiveSheetIndex(0);
		try{
			
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$nfile = 'documentos/'.$nPedimento.'.xlsx';
			$objWriter->setIncludeCharts(TRUE);
			$objWriter->save($nfile);
			
			$respuesta['Codigo'] = 1;	
			$respuesta ['NomExcelUMT'] = $nfile;			
			return $respuesta;
			
		} catch (Exception $e) {
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'EXCEL :: Error al genererar el archivo Excel UMT. [Referencia: '.$Referencia.']['.$e->getMessage().'][monitor -> utilerias -> documentos_kia]';
			$respuesta['Error'] = '';
			enviar_notificacion_error_reporte($respuesta['Mensaje'],'');
			return $respuesta;
		}
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
	