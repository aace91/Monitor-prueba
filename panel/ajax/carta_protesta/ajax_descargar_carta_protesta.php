<?php
	require('../../../plugins/tcpdf/tcpdf.php');
	include_once('./../../../checklogin.php');
	if ($loggedIn == false){
		echo '500';
	}else{
		if (isset($_POST['fecha']) && !empty($_POST['fecha'])) {
			
			$fecha = $_POST['fecha'];
			$aduana = $_POST['aduana'];
			$patente = $_POST['patente'];
			error_log('Patente:'.$patente);
			$html_encabezado = $_POST['texto_encabezado'];
			$gastos = $_POST['gastos'];
			$fletes = $_POST['fletes'];
			$seguros = $_POST['seguros'];
			$otros = $_POST['otros'];
			$html_proveedores = $_POST['texto_proveedores'];
			
			$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			
			// set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('Grupo Aduanero Del Bravo');
			$pdf->SetTitle('Carta_Protesta');
			$pdf->SetSubject('Carta_Protesta');
			$pdf->SetKeywords('Carta_Protesta');
			
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(true);
			
			$pdf->AddPage();
			
			$pdf->SetFont('helvetica', ' ', 9);
			$sHTML = '
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td align="justify" width="100%"><b>ASUNTO:</b> Se declara bajo protesta de decir verdad, que las enmendaduras o anotaciones que alteran los datos originales, así como los datos y requisitos que faltan en la factura comercial y que se declaran en la presente, son verdaderos y correctos.</td>
				</tr>
			</table>';
			$pdf->writeHTML($sHTML, false, false, true, false, '');
			$pdf->Cell(0,0, ' ', 0, 1, 'C',0,'',0);
			$sHTML = '
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td align="right" width="100%"><b>Nuevo Laredo, Tamaulipas, a '.$fecha.'</b></td>
				</tr>
			</table>';
			$pdf->writeHTML($sHTML, false, false, true, false, '');
			$pdf->Cell(0,10, ' ', 0, 1, 'C',0,'',0);
			$sHTML = '
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td align="left" width="100%"><b>C. ADMINISTRADOR DE LA ADUANA</b></td>
				</tr>
				<tr>
					<td align="left" width="100%"><b>FRONTERIZA DE '.$aduana.'</b></td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td align="left" width="100%">&nbsp;&nbsp;&nbsp;<b>PRESENTE.</b></td>
				</tr>				
			</table>';			
			$pdf->writeHTML($sHTML, false, false, true, false, '');
			$pdf->Cell(0,8, ' ', 0, 1, 'C',0,'',0);
			$pdf->writeHTML($html_encabezado, false, false, true, false, '');
			$pdf->Cell(0,0, ' ', 0, 1, 'C',0,'',0);
			$sHTML = '
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td align="left" width="100%"><b>I.- DEL IMPORTE DE LOS CARGOS NO COMPRENDIDOS EN EL PRECIO PAGADO POR LAS MERCANCÍAS CONSIGNADO EN LA REFERIDA FACTURA, Y QUE FORMAN PARTE DEL VALOR DE TRANSACCIÓN DE LAS MISMAS:</b></td>
				</tr>			
			</table>';			
			$pdf->writeHTML($sHTML, false, false, true, false, '');
			$pdf->Cell(0,0, ' ', 0, 1, 'C',0,'',0);
			$sHTML = '
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td align="right" width="20px"><b>&nbsp;</b></td>
					<td align="left" width="380px"><b>A.- GASTOS CONEXOS, TALES COMO MANEJO, CARGA Y DESCARGA.</b></td>
					<td align="right" width="20px"><b>$</b></td>
					<td align="right" width="70px"><b>'.$gastos.'</b></td>
				</tr>
				<tr>
					<td align="right" width="20px"><b>&nbsp;</b></td>
					<td align="left" width="380px"><b>B.- FLETES.</b></td>
					<td align="right" width="20px"><b>$</b></td>
					<td align="right" width="70px"><b>'.$fletes.'</b></td>
				</tr>
				<tr>
					<td align="right" width="20px"><b>&nbsp;</b></td>
					<td align="left" width="380px"><b>C.- SEGUROS.</b></td>
					<td align="right" width="20px"><b>$</b></td>
					<td align="right" width="70px"><b>'.$seguros.'</b></td>
				</tr>
				<tr>
					<td align="right" width="20px"><b>&nbsp;</b></td>
					<td align="left" width="380px"><b>D.- OTROS.</b></td>
					<td align="right" width="20px"><b>$</b></td>
					<td align="right" width="70px"><b>'.$otros.'</b></td>
				</tr>
			</table>';			
			$pdf->writeHTML($sHTML, false, false, true, false, '');
			$pdf->Cell(0,0, ' ', 0, 1, 'C',0,'',0);
			$sHTML = '
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td align="left" width="100%"><b>II.- DE LOS DEMAS DATOS Y REQUISITOS QUE DEBE REUNIR LA FACTURA Y SE OMITIERON:</b></td>
				</tr>			
			</table>';			
			$pdf->writeHTML($sHTML, false, false, true, false, '');
			$pdf->writeHTML($html_proveedores, false, false, true, false, '');
			$sHTML = '
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td align="left" width="100%"><b>SE DECLARA EN LA PRESENTE EL DOMICILIO CORRECTO DE LOS PROVEEDORES.</b></td>
				</tr>			
			</table>';			
			$pdf->writeHTML($sHTML, false, false, true, false, '');
			$pdf->Cell(0,0, ' ', 0, 1, 'C',0,'',0);
			$sHTML = '
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td align="center" width="100%"><b>PROTESTO LO NECESARIO</b></td>
				</tr>			
			</table>';			
			$pdf->writeHTML($sHTML, false, false, true, false, '');
			$y = $pdf->GetY();
			switch($patente){
				case '3483' :
					$pdf->Image('firmas/firma_estandia.png',90,$y,25,25);
					$pdf->Cell(0,10, ' ', 0, 1, 'C',0,'',0);
					$sHTML = '
					<table cellspacing="0" cellpadding="0">
						<tr>
							<td align="center" width="100%"><b>_________________________</b></td>
						</tr>			
					</table>';			
					$pdf->writeHTML($sHTML, false, false, true, false, '');
					$sHTML = '
					<table cellspacing="0" cellpadding="0">
						<tr>
							<td align="center" width="100%"><b>AGENTE ADUANAL</b></td>
						</tr>			
					</table>';			
					$pdf->writeHTML($sHTML, false, false, true, false, '');
					$sHTML = '
					<table cellspacing="0" cellpadding="0">
						<tr>
							<td align="center" width="100%"><b>Manuel José Estandia Fernández</b></td>
						</tr>			
					</table>';			
					$pdf->writeHTML($sHTML, false, false, true, false, '');
					$sHTML = '
					<table cellspacing="0" cellpadding="0">
						<tr>
							<td align="center" width="100%"><b>EAFM620803BVA</b></td>
						</tr>			
					</table>';			
					$pdf->writeHTML($sHTML, false, false, true, false, '');
					break;
				case '1664' :
					$pdf->Image('firmas/firma_hugo.png',77,$y,60,25);
					break;
			}
			
			$pdf->Output('Carta_Protesta.pdf', 'I');
		}else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			echo json_encode($respuesta);
		}		
	}
	