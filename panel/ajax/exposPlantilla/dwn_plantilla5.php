<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('./../../../connect_casa.php');

require_once './../../../bower_components/PHPExcel/Classes/PHPExcel.php';

if ($loggedIn == false){
	echo 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
} else{
	
	$aData = json_decode($_POST['aData']);
	$sIdPlantilla = $aData->sIdPlantilla;
	
	/******************************************************/

	$consulta = "SELECT
					CONCAT(pg.referencia,  ' - ', pg.id_embarque) AS referencia,
					pd.id_proveedor AS proveedor,
					pd.no_factura AS no_factura,
					date_format(
						pd.fecha_factura,
						'%d/%m/%Y'
					) AS fecha_factura,
					pd.monto_factura AS monto_factura,
					pd.moneda AS moneda,
					pd.incoterm AS incoterm,
					pd.subdivision AS subdivision,
					pd.certificado AS cer_origen,
					pd.no_parte AS numero_parte,
					pd.origen AS pais_origen,
					pd.vendedor AS pais_vendedor,
					pd.fraccion AS fraccion,
					pd.descripcion AS descripcion,
					pd.precio_partida AS precio_partida,
					pd.umc AS umc,
					pd.cantidad_umc AS cantidad_umc,
					pd.cantidad_umt AS cantidad_umt,
					pd.preferencia AS preferencia_arancelaria,
					pd.marca AS marca,
					pd.modelo AS modelo,
					pd.submodelo AS submodelo,
					pd.serie AS serie,
					0 AS pesokgs,
					'' AS umt,
					fra_restric.fraccion as fra_restric
				FROM bodega.expos_plantilla_gral AS pg LEFT JOIN
			       bodega.expos_plantilla_detalle AS pd ON pg.id_plantilla = pd.id_plantilla 
				LEFT JOIN bodega.fracciones_restric as fra_restric on pd.fraccion=fra_restric.fraccion and sector is not null
				LEFT JOIN bodega.fracciones_restric as fra_restric_h on pd.fraccion=fra_restric_h.fraccion and fra_restric_h.horario=1
				WHERE pg.id_plantilla=".$sIdPlantilla."
				ORDER BY pd.no_factura asc";

	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar detalles de plantilla. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
		$respuesta['Consulta'] = ' ['.$consulta.']';
		$response=json_encode($respuesta);
		exit($response);
	} 

	$strReportName = 'Plantilla General Avanzada 5';
	$strReportSheetGrafica = 'Grafica';
	$objPHPExcel = new PHPExcel();
	$objWorksheet = $objPHPExcel->getActiveSheet();
	$objPHPExcel->getProperties()->setCreator("Departamento de Sistemas")
								 ->setLastModifiedBy("Departamento de Sistemas")
								 ->setTitle($strReportName);


	$objPHPExcel->setActiveSheetIndex(0)
	            ->setCellValue('A1', 'Proveedor')
	            ->setCellValue('B1', 'No. Factura')
				->setCellValue('C1', 'Fecha factura')
				->setCellValue('D1', 'Monto factura')
				->setCellValue('E1', 'Moneda')
				->setCellValue('F1', 'Incoterm')
				->setCellValue('G1', 'Subdivision')
				->setCellValue('H1', 'Certificado Origen')
				->setCellValue('I1', 'Numero parte')
				->setCellValue('J1', 'Pais Origen')
				->setCellValue('K1', 'Pais Vendedor')
				->setCellValue('L1', 'Fraccion')
				->setCellValue('M1', 'Descripcion')
				->setCellValue('N1', 'Precio partida')
				->setCellValue('O1', 'UMC')
				->setCellValue('P1', 'Cantidad UMC (Cantidad factura)')
				->setCellValue('Q1', 'Cantidad UMT (Cantidad fisica)')
				->setCellValue('R1', 'Preferencia arancelaria')
				->setCellValue('S1', 'Marca')
				->setCellValue('T1', 'Modelo')
				->setCellValue('U1', 'Submodelo')
				->setCellValue('V1', 'Serie')
				->setCellValue('W1', 'Descripcion COVE')
				->setCellValue('X1', 'Referencia - Embarque')
				->setCellValue('Y1', 'No. Factura')
				->setCellValue('Z1', 'Suma Partidas');
	$objPHPExcel->getActiveSheet()->setTitle($strReportName);
	$oSheetFracciones = new PHPExcel_Worksheet($objPHPExcel, 'Fracciones');
	$objPHPExcel->addSheet($oSheetFracciones);
	$objPHPExcel->setActiveSheetIndex(1)
	            ->setCellValue('A1', 'Consecutivo Factura')
	            ->setCellValue('B1', 'Fracción')
				->setCellValue('C1', 'Descripción mercancía')
				->setCellValue('D1', 'País de origen')
				->setCellValue('E1', 'País vendedor')
				->setCellValue('F1', 'Unidad de la tarifa')
				->setCellValue('G1', 'Cantidad de la tarifa')
				->setCellValue('H1', 'Unidad según factura')
				->setCellValue('I1', 'Cantidad según factura')
				->setCellValue('J1', 'Clave indentificador 1')
				->setCellValue('K1', 'Complemento 1, Identificador 1')
				->setCellValue('L1', 'Complemento 2, Identificador 1')
				->setCellValue('M1', 'Clave identificador 2')
				->setCellValue('N1', 'Complemento 1, Identificador 2')
				->setCellValue('O1', 'Complemento 2, Identificador 2')
				->setCellValue('P1', 'Porcentaje del IVA')
				->setCellValue('Q1', 'Moneda')
				->setCellValue('R1', 'Valor Factura')
				->setCellValue('S1', 'Observaciones Partida');

	$renglon=1;
	$facturas=array();
	$cons_fact=1;
	$SumaPrecioPartida = 0;
	$NoFacturaActual = '';
	$NoFacturaAnterior = '';
	$MontoFacturaActual = 0;
	$MontoFacturaAnterior = 0;
	while($row = mysqli_fetch_object($query)){ 
		$renglon++;

		$NoFacturaActual = $row->no_factura;
		$MontoFacturaActual = round($row->monto_factura, 2);
		$precio_partida = round($row->precio_partida,2);

	//	error_log($row->no_factura);
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$renglon, $row->proveedor)
			->setCellValue('B'.$renglon, $NoFacturaActual)
			->setCellValue('C'.$renglon, $row->fecha_factura)
			->setCellValue('D'.$renglon, round($row->monto_factura, 2))
			->setCellValue('E'.$renglon, $row->moneda)
			->setCellValue('F'.$renglon, $row->incoterm)
			->setCellValue('G'.$renglon, $row->subdivision)
			->setCellValue('H'.$renglon, $row->cer_origen)
			->setCellValue('I'.$renglon, $row->numero_parte)
			->setCellValue('J'.$renglon, $row->pais_origen)
			->setCellValue('K'.$renglon, $row->pais_vendedor)
			->setCellValue('L'.$renglon, $row->fraccion)
			->setCellValue('M'.$renglon, $row->descripcion)
			->setCellValue('N'.$renglon, $precio_partida)
			->setCellValue('O'.$renglon, $row->umc)
			->setCellValue('P'.$renglon, $row->cantidad_umc)
			->setCellValue('Q'.$renglon, $row->cantidad_umt)
			->setCellValue('R'.$renglon, $row->preferencia_arancelaria)
			->setCellValue('S'.$renglon, $row->marca)
			->setCellValue('T'.$renglon, $row->modelo)
			->setCellValue('U'.$renglon, $row->submodelo)
			->setCellValue('V'.$renglon, $row->serie)
			->setCellValue('W'.$renglon, $row->descripcion)
			->setCellValue('X'.$renglon, $row->referencia);


		if ($NoFacturaAnterior == $NoFacturaActual) {
			$SumaPrecioPartida += $precio_partida;
		} else {
			//$objPHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.($renglon - 1), "=D".($renglon - 1));
			if($MontoFacturaAnterior != $SumaPrecioPartida){
				
				$DifMonto = $MontoFacturaAnterior-$SumaPrecioPartida;
				$PrecioActual = $objPHPExcel->setActiveSheetIndex(0)
					->getCell('N'.($renglon-1))->getValue();
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('N'.($renglon-1), $PrecioActual+$DifMonto);
				$SumaPrecioPartida=$SumaPrecioPartida+$DifMonto;
			}
			if ($NoFacturaAnterior != '') {
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.($renglon - 1), $NoFacturaAnterior);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.($renglon - 1), $SumaPrecioPartida);	
			}

			$SumaPrecioPartida = $precio_partida;
			$NoFacturaAnterior = $NoFacturaActual;
			$MontoFacturaAnterior = $MontoFacturaActual;
		}

		/***************************************************************************************************************/

		$objPHPExcel->getActiveSheet()
	        ->getComment('B'.$renglon)
	        ->getText()->createTextRun($row->referencia);
		$objPHPExcel->getActiveSheet()
	        ->getComment('Q'.$renglon)
	        ->getText()->createTextRun("EQUIVALENCIA CANTIDAD FISICA EN UMT " . $row->umt . ",\r\n PESO KGS: ". $row->pesokgs . "\r\n");
		$objPHPExcel->getActiveSheet()
	        ->getComment('Q'.$renglon)
			->setHeight("150px");
		if($row->fra_restric!=NULL){
			$objPHPExcel->getActiveSheet()->getStyle('L'.$renglon)->applyFromArray(
					array(
						'fill' 	=> array(
						'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
						'color'		=> array('argb' => 'FFFF3333')
						),
					)
				);
			$objPHPExcel->getActiveSheet()
	        ->getComment('L'.$renglon)
	        ->getText()->createTextRun('Fracción en anexo 10 sector 14 o 15');
		}
		$encontro_fac=false;
		foreach ($facturas as $factura) {
			if($factura[0]==$row->referencia and $factura[1]==$row->no_factura){
				$cons_factp=$factura[2];
				$encontro_fac=true;
				break;
			}
		}
		if($encontro_fac==false){
			array_push($facturas,array($row->referencia,$row->no_factura,$cons_fact));
			$cons_factp=$cons_fact;
			$cons_fact++;
		}
		$objPHPExcel->setActiveSheetIndex(1)
			->setCellValue('A'.$renglon, $cons_factp)
			->setCellValue('B'.$renglon, "='Plantilla General Avanzada 5'!L".$renglon)
			->setCellValue('C'.$renglon, "='Plantilla General Avanzada 5'!M".$renglon)
			->setCellValue('D'.$renglon, "='Plantilla General Avanzada 5'!J".$renglon)
			->setCellValue('E'.$renglon, "='Plantilla General Avanzada 5'!K".$renglon)
			->setCellValue('F'.$renglon, $row->umt)
			->setCellValue('G'.$renglon, "='Plantilla General Avanzada 5'!Q".$renglon)
			->setCellValue('H'.$renglon, "='Plantilla General Avanzada 5'!O".$renglon)
			->setCellValue('I'.$renglon, "='Plantilla General Avanzada 5'!P".$renglon)
			->setCellValue('J'.$renglon, "MA")
			->setCellValue('K'.$renglon, "")
			->setCellValue('L'.$renglon, "")
			->setCellValue('M'.$renglon, "")
			->setCellValue('N'.$renglon, "")
			->setCellValue('O'.$renglon, "")
			->setCellValue('P'.$renglon, "16")
			->setCellValue('Q'.$renglon, "='Plantilla General Avanzada 5'!E".$renglon)
			->setCellValue('R'.$renglon, "='Plantilla General Avanzada 5'!N".$renglon)
			->setCellValue('S'.$renglon, "='Plantilla General Avanzada 5'!I".$renglon);
	}
	
	if($MontoFacturaAnterior != $SumaPrecioPartida){	
		$DifMonto = $MontoFacturaAnterior-$SumaPrecioPartida;
		$PrecioActual = $objPHPExcel->setActiveSheetIndex(0)
			->getCell('N'.($renglon-1))->getValue();
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('N'.($renglon-1), $PrecioActual+$DifMonto);
		$SumaPrecioPartida=$SumaPrecioPartida+$DifMonto;
	}
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('Y'.$renglon, $NoFacturaAnterior);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('Z'.$renglon, $SumaPrecioPartida);

	$objPHPExcel->setActiveSheetIndex(0);
	foreach(range('A','Z') as $columnID) {
	    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
	        ->setAutoSize(true);
	}
	$objPHPExcel->setActiveSheetIndex(1);
	$objPHPExcel->getActiveSheet()->getColumnDimension();
	foreach(range('A','S') as $columnID) {
	    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
	        ->setAutoSize(true);
	}
	$objPHPExcel->getActiveSheet()->getColumnDimension();

	/************************************************/
	/* GUARDANDO Y DESCARGANDO EXCEL */
	/************************************************/
	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$fec1= new DateTime();
	$fec2= date_format($fec1, 'd-m-Y');
	$fec3= date_format($fec1, 'd/m/Y h:i a');
	$nfile=$strReportName.'.xlsx';
	$objWriter->setIncludeCharts(TRUE);
	$objWriter->save($nfile);
	
	if (headers_sent()) {
		echo 'HTTP header already sent';
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		//header("Content-Type: application/pdf");
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length: ".filesize($nfile));
		header("Content-Disposition: attachment; filename=\"".$nfile."\"");
		header('Accept-Ranges: bytes');
		@readfile($nfile);
		unlink($nfile);
		exit;
	}

	$post_data = array(
	  'sFechaInicio' => $sFechaInicio,
	  'sFechaFin' => $sFechaFin,
	  'sTipo' => $sTipo
	);
	
	echo json_encode ($post_data);
}

/*********************************************************/
/* ..:: GRAFICA DE EJECUTIVOS ::.. */
/*********************************************************/
function generar_grafica_ejecutivos() {
	try {
		global $sFechaInicio, $sFechaFin, $strReportSheetGrafica, $objPHPExcel, $objWorksheet, $odbccasa;

		$sGraficaTitulo = 'Errores Detactados Por Ejecutivo';

		$renglon=1;
		$objPHPExcel->getActiveSheet()
					->setTitle($strReportSheetGrafica)
					->setCellValue('A'.$renglon, $sGraficaTitulo);

		$renglon++;
		$objPHPExcel->getActiveSheet()
					->setCellValue('A'.$renglon, 'Ejecutivo')
					->setCellValue('B'.$renglon, 'Errores');

		$consulta = "
			SELECT a.USUARIO, b.NOMBRE, COUNT(*) AS ERRORES
			FROM GAB_GLOSA a LEFT JOIN
			     SISSEG_USUARI b ON a.USUARIO = b.LOGIN
			WHERE (a.FECHA_ALTA >= '".$sFechaInicio." 00:00:00' AND a.FECHA_ALTA <= '".$sFechaFin." 23:59:59') AND 
      			  a.NUM_REFE = (SELECT FIRST 1 c.NUM_REFE
                                FROM GAB_GLOSA_DET c
                                WHERE c.NUM_REFE = a.NUM_REFE)
			GROUP BY a.USUARIO, b.NOMBRE
			ORDER BY ERRORES DESC";	

		$query = odbc_exec ($odbccasa, $consulta);
		if ($query!=false){ 

			$_MaxTotal = 0;
			$nTotRows = odbc_num_rows($query);
			if ($nTotRows > 0) { 
				while(odbc_fetch_row($query)){ 
					$_total = odbc_result($query,"ERRORES");

					$renglon++;	
					$objPHPExcel->getActiveSheet()
								->setCellValue('A'.$renglon, utf8_encode(odbc_result($query,"NOMBRE")))
								->setCellValue('B'.$renglon, $_total);

					if ($_total > $_MaxTotal) {
						$_MaxTotal = $_total;
					}
				}

				if ($_MaxTotal > 10) {
					$_UltimoDigito = substr($_MaxTotal, -1);
					if ($_UltimoDigito > 0) {
						$_UltimoDigito = 10 - $_UltimoDigito;
					}
					$_MaxTotal += $_UltimoDigito;
				}			

				/****************************************/

				$dataSeriesLabels = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$B$2', NULL, 1),	//  Errores
				);

				$xAxisTickValues = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$A$3:$A$'.$renglon, NULL, 4),	
				);

				$dataSeriesValues = array(
					new PHPExcel_Chart_DataSeriesValues('Number', $strReportSheetGrafica.'!$B$3:$B$'.$renglon, NULL, 4),
				);

				$series = new PHPExcel_Chart_DataSeries(
					PHPExcel_Chart_DataSeries::TYPE_BARCHART,		// plotType
					PHPExcel_Chart_DataSeries::GROUPING_STANDARD,	// plotGrouping
					range(0, count($dataSeriesValues)-1),			// plotOrder
					$dataSeriesLabels,								// plotLabel
					$xAxisTickValues,								// plotCategory
					$dataSeriesValues								// plotValues
				);
				//	Set additional dataseries parameters
				//		Make it a vertical column rather than a horizontal bar graph
				$series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);
				//	Set the series in the plot area
				$plotArea = new PHPExcel_Chart_PlotArea(NULL, array($series));
				//	Set the chart legend
				$legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_BOTTOM, NULL, false);
				$title = new PHPExcel_Chart_Title($sGraficaTitulo);
				$yAxisLabel = new PHPExcel_Chart_Title('Errores');
				//	Create the chart
				$axis =  new PHPExcel_Chart_Axis();
				$axis->setAxisOptionsProperties('nextTo', null, null, null, null, null, null, $_MaxTotal);
				$chart = new PHPExcel_Chart(
					'chart1',		// name
					$title,			// title
					$legend,		// legend
					$plotArea,		// plotArea
					true,			// plotVisibleOnly
					0,				// displayBlanksAs
					NULL,			// xAxisLabel
					$yAxisLabel,		// yAxisLabel
					$axis
				);

				//	Set the position where the chart should appear in the worksheet
				$chart->setTopLeftPosition('D2');
				$chart->setBottomRightPosition('W28');
				//	Add the chart to the worksheet
				$objPHPExcel->getActiveSheet()->addChart($chart);

				foreach(range('A','B') as $columnID) {
				    $objPHPExcel->getActiveSheet()
				    			->getColumnDimension($columnID)
				                ->setAutoSize(true);
				}
			}

		} else {
			echo "Error al generar reporte Ejecutivos ".$consulta;
			exit();
		}
	} catch (Exception $e) {
		
	}
}

/*********************************************************/
/* ..:: GRAFICA DE CLIENTES ::.. */
/*********************************************************/
function generar_grafica_clientes() {
	try {
		global $sFechaInicio, $sFechaFin, $strReportSheetGrafica, $objPHPExcel, $objWorksheet, $odbccasa;

		$sGraficaTitulo = 'Errores Detactados Por Cliente';
		$renglon=1;
		$objPHPExcel->getActiveSheet()
					->setTitle($strReportSheetGrafica)
					->setCellValue('A'.$renglon, $sGraficaTitulo);

		$renglon++;
		$objPHPExcel->getActiveSheet()
					->setCellValue('A'.$renglon, 'Cliente')
					->setCellValue('B'.$renglon, 'Errores');

		$consulta = "
			SELECT b.CVE_IMPO, c.NOM_IMP AS CLIENTE, COUNT(*) AS ERRORES
			FROM GAB_GLOSA a LEFT JOIN
			     SAAIO_PEDIME b ON a.NUM_REFE = b.NUM_REFE LEFT JOIN
			     CTRAC_CLIENT c ON b.CVE_IMPO = c.CVE_IMP
			WHERE (a.FECHA_ALTA >= '".$sFechaInicio." 00:00:00' AND a.FECHA_ALTA <= '".$sFechaFin." 23:59:59') AND 
      			  a.NUM_REFE = (SELECT FIRST 1 c.NUM_REFE
                                FROM GAB_GLOSA_DET c
                                WHERE c.NUM_REFE = a.NUM_REFE)
			GROUP BY b.CVE_IMPO, CLIENTE
			ORDER BY ERRORES DESC";	

		$query = odbc_exec ($odbccasa, $consulta);
		if ($query!=false){ 

			$_MaxTotal = 0;
			$nTotRows = odbc_num_rows($query);
			if ($nTotRows > 0) { 
				while(odbc_fetch_row($query)){ 
					$_total = odbc_result($query,"ERRORES");

					$renglon++;	
					$objPHPExcel->getActiveSheet()
								->setCellValue('A'.$renglon, utf8_encode(odbc_result($query,"CLIENTE")))
								->setCellValue('B'.$renglon, $_total);

					if ($_total > $_MaxTotal) {
						$_MaxTotal = $_total;
					}
				}

				if ($_MaxTotal > 10) {
					$_UltimoDigito = substr($_MaxTotal, -1);
					if ($_UltimoDigito > 0) {
						$_UltimoDigito = 10 - $_UltimoDigito;
					}
					$_MaxTotal += $_UltimoDigito;
				}

				/****************************************/

				$dataSeriesLabels = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$B$2', NULL, 1),	//  Errores
				);

				$xAxisTickValues = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$A$3:$A$'.$renglon, NULL, 4),	
				);

				$dataSeriesValues = array(
					new PHPExcel_Chart_DataSeriesValues('Number', $strReportSheetGrafica.'!$B$3:$B$'.$renglon, NULL, 4),
				);

				$series = new PHPExcel_Chart_DataSeries(
					PHPExcel_Chart_DataSeries::TYPE_BARCHART,		// plotType
					PHPExcel_Chart_DataSeries::GROUPING_STANDARD,	// plotGrouping
					range(0, count($dataSeriesValues)-1),			// plotOrder
					$dataSeriesLabels,								// plotLabel
					$xAxisTickValues,								// plotCategory
					$dataSeriesValues								// plotValues
				);
				//	Set additional dataseries parameters
				//		Make it a vertical column rather than a horizontal bar graph
				$series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);
				//	Set the series in the plot area
				$plotArea = new PHPExcel_Chart_PlotArea(NULL, array($series));
				//	Set the chart legend
				$legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_BOTTOM, NULL, false);
				$title = new PHPExcel_Chart_Title($sGraficaTitulo);
				$yAxisLabel = new PHPExcel_Chart_Title('Errores');
				//	Create the chart
				$axis =  new PHPExcel_Chart_Axis();
				$axis->setAxisOptionsProperties('nextTo', null, null, null, null, null, null, $_MaxTotal);
				$chart = new PHPExcel_Chart(
					'chart1',		// name
					$title,			// title
					$legend,		// legend
					$plotArea,		// plotArea
					true,			// plotVisibleOnly
					0,				// displayBlanksAs
					NULL,			// xAxisLabel
					$yAxisLabel,		// yAxisLabel
					$axis
				);

				//	Set the position where the chart should appear in the worksheet
				$chart->setTopLeftPosition('D2');
				$chart->setBottomRightPosition('W28');
				//	Add the chart to the worksheet
				$objPHPExcel->getActiveSheet()->addChart($chart);

				foreach(range('A','B') as $columnID) {
				    $objPHPExcel->getActiveSheet()
				    			->getColumnDimension($columnID)
				                ->setAutoSize(true);
				}
			}

		} else {
			echo "Error al generar reporte Clientes ".$consulta;
			exit();
		}
	} catch (Exception $e) {
		
	}
}

/*********************************************************/
/* ..:: GRAFICA DE REGIMEN ::.. */
/*********************************************************/
function generar_grafica_regimen() {
	try {
		global $sFechaInicio, $sFechaFin, $strReportSheetGrafica, $objPHPExcel, $objWorksheet, $odbccasa;

		$sGraficaTitulo = 'Errores Detactados Por Regimen';

		$renglon=1;
		$objPHPExcel->getActiveSheet()
					->setTitle($strReportSheetGrafica)
					->setCellValue('A'.$renglon, $sGraficaTitulo);

		$renglon++;
		$objPHPExcel->getActiveSheet()
					->setCellValue('A'.$renglon, 'Regimen')
					->setCellValue('B'.$renglon, 'Errores');

		$consulta = "
			SELECT b.CVE_PEDI, COUNT(*) AS ERRORES
			FROM GAB_GLOSA a LEFT JOIN
			     SAAIO_PEDIME b ON a.NUM_REFE = b.NUM_REFE LEFT JOIN
     			 CTRAC_CLIENT c ON b.CVE_IMPO = c.CVE_IMP
			WHERE (a.FECHA_ALTA >= '".$sFechaInicio." 00:00:00' AND a.FECHA_ALTA <= '".$sFechaFin." 23:59:59') AND 
      			  a.NUM_REFE = (SELECT FIRST 1 c.NUM_REFE
                                FROM GAB_GLOSA_DET c
                                WHERE c.NUM_REFE = a.NUM_REFE)
			GROUP BY b.CVE_PEDI
			ORDER BY ERRORES DESC";	

		$query = odbc_exec ($odbccasa, $consulta);
		if ($query!=false){ 

			$_MaxTotal = 0;
			$nTotRows = odbc_num_rows($query);
			if ($nTotRows > 0) { 
				while(odbc_fetch_row($query)){ 
					$_total = odbc_result($query,"ERRORES");

					$renglon++;	
					$objPHPExcel->getActiveSheet()
								->setCellValue('A'.$renglon, odbc_result($query,"CVE_PEDI"))
								->setCellValue('B'.$renglon, $_total);
				
					if ($_total > $_MaxTotal) {
						$_MaxTotal = $_total;
					}
				}

				if ($_MaxTotal > 10) {
					$_UltimoDigito = substr($_MaxTotal, -1);
					if ($_UltimoDigito > 0) {
						$_UltimoDigito = 10 - $_UltimoDigito;
					}
					$_MaxTotal += $_UltimoDigito;
				}

				/****************************************/

				$dataSeriesLabels = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$B$2', NULL, 1),	//  Errores
				);

				$xAxisTickValues = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$A$3:$A$'.$renglon, NULL, 4),	
				);

				$dataSeriesValues = array(
					new PHPExcel_Chart_DataSeriesValues('Number', $strReportSheetGrafica.'!$B$3:$B$'.$renglon, NULL, 4),
				);

				$series = new PHPExcel_Chart_DataSeries(
					PHPExcel_Chart_DataSeries::TYPE_BARCHART,		// plotType
					PHPExcel_Chart_DataSeries::GROUPING_STANDARD,	// plotGrouping
					range(0, count($dataSeriesValues)-1),			// plotOrder
					$dataSeriesLabels,								// plotLabel
					$xAxisTickValues,								// plotCategory
					$dataSeriesValues								// plotValues
				);
				//	Set additional dataseries parameters
				//		Make it a vertical column rather than a horizontal bar graph
				$series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);
				//	Set the series in the plot area
				$plotArea = new PHPExcel_Chart_PlotArea(NULL, array($series));
				//	Set the chart legend
				$legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_BOTTOM, NULL, false);
				$title = new PHPExcel_Chart_Title($sGraficaTitulo);
				$yAxisLabel = new PHPExcel_Chart_Title('Errores');
				//	Create the chart
				$axis =  new PHPExcel_Chart_Axis();
				$axis->setAxisOptionsProperties('nextTo', null, null, null, null, null, null, $_MaxTotal);
				$chart = new PHPExcel_Chart(
					'chart1',		// name
					$title,			// title
					$legend,		// legend
					$plotArea,		// plotArea
					true,			// plotVisibleOnly
					0,				// displayBlanksAs
					NULL,			// xAxisLabel
					$yAxisLabel,		// yAxisLabel
					$axis
				);

				//	Set the position where the chart should appear in the worksheet
				$chart->setTopLeftPosition('D2');
				$chart->setBottomRightPosition('W28');
				//	Add the chart to the worksheet
				$objPHPExcel->getActiveSheet()->addChart($chart);

				foreach(range('A','B') as $columnID) {
				    $objPHPExcel->getActiveSheet()
				    			->getColumnDimension($columnID)
				                ->setAutoSize(true);
				}
			}

		} else {
			echo "Error al generar reporte Regimen ".$consulta;
			exit();
		}
	} catch (Exception $e) {
		
	}
}

/*********************************************************/
/* ..:: GRAFICA DE OPERACION (IMPO/EXPO) ::.. */
/*********************************************************/
function generar_grafica_impo_expo() {
	try {
		global $sFechaInicio, $sFechaFin, $strReportSheetGrafica, $objPHPExcel, $objWorksheet, $odbccasa;

		$sGraficaTitulo = 'Errores Detactados Por Operación';

		$renglon=1;
		$objPHPExcel->getActiveSheet()
					->setTitle($strReportSheetGrafica)
					->setCellValue('A'.$renglon, $sGraficaTitulo);

		$renglon++;
		$objPHPExcel->getActiveSheet()
					->setCellValue('A'.$renglon, 'Tipo Operación')
					->setCellValue('B'.$renglon, 'Errores');

		$consulta = "
			SELECT CASE b.IMP_EXPO 
				       WHEN 1 THEN 'IMPORTACIÓN' 
				       WHEN 2 THEN 'EXPORTACIÓN' 
				       ELSE 'NA' 
				   END AS IMP_EXPO,
			       COUNT(*) AS ERRORES
			FROM GAB_GLOSA a LEFT JOIN
			     SAAIO_PEDIME b ON a.NUM_REFE = b.NUM_REFE LEFT JOIN
     			 CTRAC_CLIENT c ON b.CVE_IMPO = c.CVE_IMP
			WHERE (a.FECHA_ALTA >= '".$sFechaInicio." 00:00:00' AND a.FECHA_ALTA <= '".$sFechaFin." 23:59:59') AND 
      			  a.NUM_REFE = (SELECT FIRST 1 c.NUM_REFE
                                FROM GAB_GLOSA_DET c
                                WHERE c.NUM_REFE = a.NUM_REFE)
			GROUP BY IMP_EXPO
			ORDER BY ERRORES DESC";	

		$query = odbc_exec ($odbccasa, $consulta);
		if ($query!=false){ 

			$_MaxTotal = 0;
			$nTotRows = odbc_num_rows($query);
			if ($nTotRows > 0) { 
				while(odbc_fetch_row($query)){ 
					$_total = odbc_result($query,"ERRORES");

					$renglon++;	
					$objPHPExcel->getActiveSheet()
								->setCellValue('A'.$renglon, odbc_result($query,"IMP_EXPO"))
								->setCellValue('B'.$renglon, $_total);

					if ($_total > $_MaxTotal) {
						$_MaxTotal = $_total;
					}
				}

				if ($_MaxTotal > 10) {
					$_UltimoDigito = substr($_MaxTotal, -1);
					if ($_UltimoDigito > 0) {
						$_UltimoDigito = 10 - $_UltimoDigito;
					}
					$_MaxTotal += $_UltimoDigito;
				}

				/****************************************/

				$dataSeriesLabels = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$B$2', NULL, 1),	//  Errores
				);

				$xAxisTickValues = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$A$3:$A$'.$renglon, NULL, 4),	
				);

				$dataSeriesValues = array(
					new PHPExcel_Chart_DataSeriesValues('Number', $strReportSheetGrafica.'!$B$3:$B$'.$renglon, NULL, 4),
				);

				$series = new PHPExcel_Chart_DataSeries(
					PHPExcel_Chart_DataSeries::TYPE_BARCHART,		// plotType
					PHPExcel_Chart_DataSeries::GROUPING_STANDARD,	// plotGrouping
					range(0, count($dataSeriesValues)-1),			// plotOrder
					$dataSeriesLabels,								// plotLabel
					$xAxisTickValues,								// plotCategory
					$dataSeriesValues								// plotValues
				);
				//	Set additional dataseries parameters
				//		Make it a vertical column rather than a horizontal bar graph
				$series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);
				//	Set the series in the plot area
				$plotArea = new PHPExcel_Chart_PlotArea(NULL, array($series));
				//	Set the chart legend
				$legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_BOTTOM, NULL, false);
				$title = new PHPExcel_Chart_Title($sGraficaTitulo);
				$yAxisLabel = new PHPExcel_Chart_Title('Errores');
				//	Create the chart
				$axis =  new PHPExcel_Chart_Axis();
				$axis->setAxisOptionsProperties('nextTo', null, null, null, null, null, null, $_MaxTotal);
				$chart = new PHPExcel_Chart(
					'chart1',		// name
					$title,			// title
					$legend,		// legend
					$plotArea,		// plotArea
					true,			// plotVisibleOnly
					0,				// displayBlanksAs
					NULL,			// xAxisLabel
					$yAxisLabel,		// yAxisLabel
					$axis
				);

				//	Set the position where the chart should appear in the worksheet
				$chart->setTopLeftPosition('D2');
				$chart->setBottomRightPosition('W28');
				//	Add the chart to the worksheet
				$objPHPExcel->getActiveSheet()->addChart($chart);

				foreach(range('A','B') as $columnID) {
				    $objPHPExcel->getActiveSheet()
				    			->getColumnDimension($columnID)
				                ->setAutoSize(true);
				}
			}

		} else {
			echo "Error al generar reporte Ejecutivos ".$consulta;
			exit();
		}
	} catch (Exception $e) {
		
	}
}

/*********************************************************/
/* ..:: GRAFICA POR PROBLEMA ::.. */
/*********************************************************/
function generar_grafica_problemas() {
	try {
		global $sFechaInicio, $sFechaFin, $strReportSheetGrafica, $objPHPExcel, $objWorksheet, $odbccasa;

		$sGraficaTitulo = 'Errores Detactados Por Problema';

		$renglon=1;
		$objPHPExcel->getActiveSheet()
					->setTitle($strReportSheetGrafica)
					->setCellValue('A'.$renglon, $sGraficaTitulo);

		$renglon++;
		$objPHPExcel->getActiveSheet()
					->setCellValue('A'.$renglon, 'Problema')
					->setCellValue('B'.$renglon, 'Errores');

		$consulta = "
			SELECT a.ID_PROBLEMA, c.PROBLEMA, COUNT(*) AS ERRORES
			FROM GAB_GLOSA_DET a INNER JOIN
			     GAB_GLOSA b ON a.NUM_REFE = b.NUM_REFE INNER JOIN
			     GAB_GLOSA_CAT_PROBLEMAS c ON a.ID_PROBLEMA = c.ID_PROBLEMA
			WHERE (a.FECHA_ALTA >= '".$sFechaInicio." 00:00:00' AND a.FECHA_ALTA <= '".$sFechaFin." 23:59:59') AND 
      			  a.NUM_REFE = (SELECT FIRST 1 c.NUM_REFE
                                FROM GAB_GLOSA_DET c
                                WHERE c.NUM_REFE = a.NUM_REFE)
			GROUP BY a.ID_PROBLEMA, c.PROBLEMA
			ORDER BY ERRORES DESC";	

		$query = odbc_exec ($odbccasa, $consulta);
		if ($query!=false){ 

			$_MaxTotal = 0;
			$nTotRows = odbc_num_rows($query);
			if ($nTotRows > 0) { 
				while(odbc_fetch_row($query)){ 
					$_total = odbc_result($query,"ERRORES");

					$renglon++;	
					$objPHPExcel->getActiveSheet()
								->setCellValue('A'.$renglon, utf8_encode(odbc_result($query,"PROBLEMA")))
								->setCellValue('B'.$renglon, $_total);

					if ($_total > $_MaxTotal) {
						$_MaxTotal = $_total;
					}
				}

				if ($_MaxTotal > 10) {
					$_UltimoDigito = substr($_MaxTotal, -1);
					if ($_UltimoDigito > 0) {
						$_UltimoDigito = 10 - $_UltimoDigito;
					}
					$_MaxTotal += $_UltimoDigito;
				}

				/****************************************/

				$dataSeriesLabels = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$B$2', NULL, 1),	//  Errores
				);

				$xAxisTickValues = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$A$3:$A$'.$renglon, NULL, 4),	
				);

				$dataSeriesValues = array(
					new PHPExcel_Chart_DataSeriesValues('Number', $strReportSheetGrafica.'!$B$3:$B$'.$renglon, NULL, 4),
				);

				$series = new PHPExcel_Chart_DataSeries(
					PHPExcel_Chart_DataSeries::TYPE_BARCHART,		// plotType
					PHPExcel_Chart_DataSeries::GROUPING_STANDARD,	// plotGrouping
					range(0, count($dataSeriesValues)-1),			// plotOrder
					$dataSeriesLabels,								// plotLabel
					$xAxisTickValues,								// plotCategory
					$dataSeriesValues								// plotValues
				);
				//	Set additional dataseries parameters
				//		Make it a vertical column rather than a horizontal bar graph
				$series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);
				//	Set the series in the plot area
				$plotArea = new PHPExcel_Chart_PlotArea(NULL, array($series));
				//	Set the chart legend
				$legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_BOTTOM, NULL, false);
				$title = new PHPExcel_Chart_Title($sGraficaTitulo);
				$yAxisLabel = new PHPExcel_Chart_Title('Errores');
				//	Create the chart
				$axis =  new PHPExcel_Chart_Axis();
				$axis->setAxisOptionsProperties('nextTo', null, null, null, null, null, null, $_MaxTotal);
				$chart = new PHPExcel_Chart(
					'chart1',		// name
					$title,			// title
					$legend,		// legend
					$plotArea,		// plotArea
					true,			// plotVisibleOnly
					0,				// displayBlanksAs
					NULL,			// xAxisLabel
					$yAxisLabel,		// yAxisLabel
					$axis
				);

				//	Set the position where the chart should appear in the worksheet
				$chart->setTopLeftPosition('D2');
				$chart->setBottomRightPosition('W28');
				//	Add the chart to the worksheet
				$objPHPExcel->getActiveSheet()->addChart($chart);

				foreach(range('A','B') as $columnID) {
				    $objPHPExcel->getActiveSheet()
				    			->getColumnDimension($columnID)
				                ->setAutoSize(true);
				}
			}
			
		} else {
			echo "Error al generar reporte Ejecutivos ".$consulta;
			exit();
		}
	} catch (Exception $e) {
		
	}
}