<?php
	require_once('./../../../../checklogin.php');
	require('./../../../../plugins/fpdf/fpdf.php');
	
	$loggedIn = true;
	if ($loggedIn == false){
		echo 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
	} else {
		require('./../../../../connect_exp.php');
		
		if(isset($_POST['sNumeroCaja'])){			
			$sNumeroCaja = $_POST['sNumeroCaja'];
			
			/**************************************/
			
			$nHeight = 6; //alto de los Cell
			$nNumRow = 0;
			
			$sTitle = utf8_decode('Relación Caja: '.$sNumeroCaja);
			
			/**************************************/
			
			$consulta = "SELECT no_mov, referencia
						 FROM expedientes.expedientes
						 WHERE id_caja=".$sNumeroCaja."
						 ORDER BY no_mov";
			
			$query = mysqli_query($cmysqli_exp, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_exp);
				echo 'Error al consultar informacion de la caja. Por favor contacte al administrador del sistema.'. ' ['.$error.']';
			} else {
				$pdf = new FPDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();	
				$pdf->SetTitle($sTitle);
				
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(0,$nHeight, $sTitle, 0, 0, 'C');
				$pdf->Ln($nHeight + 2);
			
				$pdf->SetFont('Arial','B',10);
				$pdf->Cell(15,$nHeight,'', 1, 0, 'C');
				$pdf->Cell(40,$nHeight,'CUENTA DE GASTOS', 1, 0, 'C');
				$pdf->Cell(30,$nHeight,'TRAFICO', 1, 0, 'C');
				$pdf->Ln($nHeight);
					
				while($row = mysqli_fetch_array($query)){
					$nNumRow++;
					$sCuenta = $row['no_mov'];
					$sTrafico = $row['referencia'];
										
					$pdf->SetFont('Arial','',8);
					$pdf->Cell(15,$nHeight,$nNumRow, 1, 0, 'C');
					$pdf->Cell(40,$nHeight,$sCuenta, 1, 0, 'C');
					$pdf->Cell(30,$nHeight,$sTrafico, 1, 0, 'C');
					$pdf->Ln($nHeight);
				}
				
				$pdf->Output();
			}			
		} 
	}
?>