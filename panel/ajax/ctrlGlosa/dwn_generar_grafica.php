<?php
include_once('./../../../checklogin.php');
require_once './../../../bower_components/PHPExcel/Classes/PHPExcel.php';

if ($loggedIn == false){
	echo 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
} else{
	require('./../../../connect_casa.php');
	
	$aData = json_decode($_POST['aData']);
	$sFechaInicio = $aData->sFechaInicio;
	$sFechaFin = $aData->sFechaFin;
	$sTipo = $aData->sTipo;

	$sFechaInicio = date( 'd.m.Y', strtotime($sFechaInicio));
	$sFechaFin = date( 'd.m.Y', strtotime($sFechaFin));

	/******************************************************/

	$strReportName = '';
	$strReportSheetGrafica = 'Grafica';
	$objPHPExcel = new PHPExcel();
	$objWorksheet = $objPHPExcel->getActiveSheet();
	$objPHPExcel->getProperties()->setCreator("Departamento de Sistemas")
								 ->setLastModifiedBy("Departamento de Sistemas");

	switch ($sTipo) {
		case 'ejecutivo':
			$strReportName = 'Grafica Ejecutivos '.$sFechaInicio.'-'.$sFechaFin;
			generar_grafica_ejecutivos();
			break;
		
		case 'cliente':
			$strReportName = 'Grafica Clientes '.$sFechaInicio.'-'.$sFechaFin;
			generar_grafica_clientes();
			break;

		case 'regimen':
			$strReportName = 'Grafica Regimen '.$sFechaInicio.'-'.$sFechaFin;
			generar_grafica_regimen();
			break;

		case 'impo_expo':
			$strReportName = 'Grafica Tipo Operación '.$sFechaInicio.'-'.$sFechaFin;
			generar_grafica_impo_expo();
			break;

		case 'problema':
			$strReportName = 'Grafica de Problemas '.$sFechaInicio.'-'.$sFechaFin;
			generar_grafica_problemas();
			break;

		default:				
			break;
	}

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
					->setCellValue('B'.$renglon, 'Total')
					->setCellValue('C'.$renglon, 'Errores');

		$consulta = "
			SELECT a.USUARIO, b.NOMBRE, COUNT(*) AS TOTAL,
				  (SELECT COUNT(*) AS ERRORES
					FROM GAB_GLOSA g LEFT JOIN
						 SISSEG_USUARI h ON g.USUARIO = h.LOGIN
					WHERE  g.USUARIO = a.USUARIO AND (g.FECHA_ALTA >= '".$sFechaInicio." 00:00:00' AND g.FECHA_ALTA <= '".$sFechaFin." 23:59:59') AND 
						   g.NUM_REFE = (SELECT FIRST 1 c.NUM_REFE
										FROM GAB_GLOSA_DET c
										WHERE c.NUM_REFE = g.NUM_REFE AND c.ID_PROBLEMA <> 12287)
					GROUP BY g.USUARIO, h.NOMBRE
					ORDER BY ERRORES DESC)
			FROM GAB_GLOSA a LEFT JOIN
			     SISSEG_USUARI b ON a.USUARIO = b.LOGIN
			WHERE (a.FECHA_ALTA >= '".$sFechaInicio." 00:00:00' AND a.FECHA_ALTA <= '".$sFechaFin." 23:59:59') AND 
      			  a.NUM_REFE = (SELECT FIRST 1 c.NUM_REFE
                                FROM GAB_GLOSA_DET c
                                WHERE c.NUM_REFE = a.NUM_REFE)
			GROUP BY a.USUARIO, b.NOMBRE
			ORDER BY TOTAL DESC";	
			
		$query = odbc_exec ($odbccasa, $consulta);
		if ($query!=false){ 

			$_MaxTotal = 0;
			$nTotRows = odbc_num_rows($query);
			if ($nTotRows > 0) { 
				while(odbc_fetch_row($query)){ 
					$_total = odbc_result($query,"TOTAL");
					$_errores = ((odbc_result($query,"ERRORES") === NULL)? 0: odbc_result($query,"ERRORES"));

					$renglon++;	
					$objPHPExcel->getActiveSheet()
								->setCellValue('A'.$renglon, utf8_encode(odbc_result($query,"NOMBRE")))
								->setCellValue('B'.$renglon, $_total)
								->setCellValue('C'.$renglon, $_errores);

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
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$B$2', NULL, 1),	//  Total
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$C$2', NULL, 1),	//  Errores
				);

				$xAxisTickValues = array(
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$A$3:$A$'.$renglon, NULL, 4),
					new PHPExcel_Chart_DataSeriesValues('String', $strReportSheetGrafica.'!$A$3:$A$'.$renglon, NULL, 4),	
				);

				$dataSeriesValues = array(
					new PHPExcel_Chart_DataSeriesValues('Number', $strReportSheetGrafica.'!$B$3:$B$'.$renglon, NULL, 4),
					new PHPExcel_Chart_DataSeriesValues('Number', $strReportSheetGrafica.'!$C$3:$C$'.$renglon, NULL, 4),
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