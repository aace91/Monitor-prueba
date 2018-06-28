<?php
	require_once('./../../../../checklogin.php');
	require('./../../../../plugins/fpdf/fpdf.php');
	require('./../../../../bower_components/TCPDF/tcpdf.php');
	
	$loggedIn = true;
	if ($loggedIn == false){
		echo 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
	} else {
		//echo $_POST['aSelectedData'];
		if(isset($_POST['sNumeroCaja'])){			
			$sNumeroCaja = $_POST['sNumeroCaja'];
			
			/**************************************/
			
			$nHeight = 6; //alto de los Cell
			
			$sTitle = utf8_decode('Etiqueta Caja: '.$sNumeroCaja);
			
			// create new PDF document
			$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

			// set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('Del Bravo');
			$pdf->SetTitle('Etiqueta Caja '.$sNumeroCaja);
			$pdf->SetSubject('Etiqueta Caja '.$sNumeroCaja);
			$pdf->SetKeywords('Etiqueta Caja '.$sNumeroCaja);

			// remove default header/footer
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// set font
			$pdf->SetFont('helvetica', 'B', 40);

			// add a page
			$pdf->AddPage();

			// print a message
			$pdf->Cell(0,$nHeight, 'GRUPO ADUANERO DEL BRAVO SA DE CV', 0, 0, 'C');

			// set style for barcode
			$style = array(
				'border' => 0,
				'vpadding' => 'auto',
				'hpadding' => 'auto',
				'fgcolor' => array(0,0,0),
				'bgcolor' => false, //array(255,255,255)
				'module_width' => 5, // width of a single module in points
				'module_height' => 3 // height of a single module in points
			);
			
			$pdf->write2DBarcode($sNumeroCaja, 'PDF417', 14, 30, 0, 126, $style, 'N');
			
			$pdf->Ln(24);
			$pdf->SetFont('helvetica', 'B', 70);
			$pdf->Cell(0,0, 'CAJA #'.$sNumeroCaja, 0, 0, 'C');
			
			//Close and output PDF document
			$pdf->Output('Etiqueta Caja '.$sNumeroCaja.'.pdf', 'I');
		} 
	}
?>