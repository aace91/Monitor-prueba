<?php
	include_once('./../../../../checklogin.php');
	require('./../../../../plugins/fpdf/fpdf.php');
	
	$loggedIn = true;
	if ($loggedIn == false){
		echo 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
	} else {
		//echo $_POST['aSelectedData'];
		if(isset($_POST['aSelected'])){			
			//$aSelected = array("7", "8", "9", "10", "11", "12", "13");
			$aSelected = json_decode($_POST['aSelected']);
			$sTitle = utf8_decode($_POST['sTitle']);
			$sTipoRel = $_POST['sTipoRel'];
			
			$pdf = new FPDF();
			$pdf->AliasNbPages();
			$pdf->AddPage();	
			$pdf->SetTitle($sTitle);
			
			/**************************************/
			/* Variables de control para PDF      */
			$Height = 6;
			/**************************************/
			
			$pdf->SetFont('Arial','B',12);
			$pdf->Cell(0,$Height, $sTitle, 0, 0, 'C');
			$pdf->Ln($Height + 2);
			
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(40,$Height,'CUENTA DE GASTOS', 1, 0, 'C');
			$pdf->Cell(30,$Height,'PEDIMENTO', 1, 0, 'C');
			$pdf->Cell(15,$Height,'CAJA', 1, 0, 'C');
			
			if ($sTipoRel == 'MV_HC') {
				$pdf->Cell(30,$Height,'MANIF VALOR', 1, 0, 'C');
				$pdf->Cell(30,$Height,'HOJA CALC', 1, 0, 'C');
			} else {
				// DS_LIB
				$pdf->Cell(30,$Height,'DES LIBRE', 1, 0, 'C');
			}
			
			$pdf->Ln($Height);
				
			for ($i=0; $i < count($aSelected); $i++) {
				$sNumCuenta = $aSelected[$i]->cuenta_gastos;
				$sPedimento = $aSelected[$i]->pedimento;
				$sCaja = $aSelected[$i]->caja;
				
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(40,$Height,$sNumCuenta, 1, 0, 'C');
				$pdf->Cell(30,$Height,$sPedimento, 1, 0, 'C');
				$pdf->Cell(15,$Height,$sCaja, 1, 0, 'C');
				
				if ($sTipoRel == 'MV_HC') {
					$sMV = $aSelected[$i]->mv;
					$sHC = $aSelected[$i]->hc;
					
					if ($sMV != '') {
						$sMV = 'OK';
					}
					
					if ($sHC != '') {
						$sHC = 'OK';
					}
					
					$pdf->Cell(30,$Height,$sMV, 1, 0, 'C');
					$pdf->Cell(30,$Height,$sHC, 1, 0, 'C');
				} else {
					// DS_LIB
					$sDS = $aSelected[$i]->ds;
					
					if ($sDS != '') {
						$sDS = 'OK';
					}
					
					$pdf->Cell(30,$Height,$sDS, 1, 0, 'C');
				}
			
				$pdf->Ln($Height);
			}
						
			$pdf->Output();
		} 
	}
?>