<?php
include_once('./../../../checklogin.php');
require('./../../../connect_gabdata.php');
require('./../../../url_archivos.php');
require('./../../../bower_components/TCPDF/tcpdf.php');

if($loggedIn == false){
	echo '500';
} else {
	$respuesta['Codigo'] = 1;

	if (isset($_POST['idclicont']) && !empty($_POST['idclicont'])) {
		$idclicont = $_POST['idclicont'];

		/***************************************************************************/

		$sNombreCliente = '';
		$sDireccionCliente = '';
		$sEstadoCliente = '';

		//$idclicont = 1113;
		/***************************************************************************/

		/* ..:: Consulta ::.. */
		$consulta = "SELECT a.nombre,
			                a.direccion1,
		                    CONCAT(TRIM(a.ciudad), ', ',TRIM(a.estado), ' C.P.:', TRIM(a.codigo)) AS estado
		             FROM contagab.aacte AS a
		             WHERE a.no_cte=".$idclicont;

		$query = mysqli_query($cmysqli_sab07,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_sab07);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar el nombre del cliente. Por favor contacte al administrador del sistema.';
			$respuesta['Error'] = ' ['.$error.']';

			exit(json_encode($respuesta));
		} else {
			while($row = mysqli_fetch_array($query)){
				$sNombreCliente = $row['nombre'];
				$sDireccionCliente = $row['direccion1'];
				$sEstadoCliente = $row['estado'];
			}
		}

		/*SELECT fac.fecha AS fecha_order,
							 DATE_FORMAT(fac.fecha, '%d/%m/%Y') AS fecha,
							 fac.trafico AS trafico,
							 fac.pedimento AS pedimento,
							 fac.tipo_mov AS tipo_mov,
							 fac.no_banco AS no_banco,
							 fac.no_mov AS no_mov,
							 '' AS td,
							 sum(IF (facd.c_a = '+', facd.monto, 0)) AS cargo,
							 sum(IF (facd.c_a = '-', facd.monto, 0)) AS abono,
							 DATEDIFF(NOW(), fac.fecha) AS dias,
							 1 AS tabla // Anticipos
					FROM contagab.aacgmex AS fac LEFT JOIN
							 contagab.asientocontable AS facd ON fac.trafico = facd.referencia AND
																		 facd.cuenta = 108 AND
																	 facd.sub_cta = ".$idclicont." // Id del cliente
					WHERE fac.no_cte = ".$idclicont." // Id del cliente
							 AND fac.cancelada = 0
					GROUP BY fac.no_mov, fac.no_banco,fac.tipo_mov
					HAVING round(cargo - abono, 2) != 0*/

		/* ..:: Datos Reporte ::.. */
		$consulta ="SELECT
						IFNULL(b.cancelada, 0) AS cancelada,
						IFNULL(b.no_cte, c.no_cte) AS no_cte,
						IFNULL(b.fecha, c.fecha) AS fecha_order,
						DATE_FORMAT(
							IFNULL(b.fecha, c.fecha),
							'%d/%m/%Y'
						) AS fecha,
						IFNULL(b.trafico, c.trafico) AS trafico,
						IFNULL(b.pedimento, c.pedimento) AS pedimento,
						IFNULL(b.tipo_mov, c.tipo_mov) AS tipo_mov,
						IFNULL(b.no_banco, c.no_banco) AS no_banco,
						IFNULL(b.no_mov, c.no_mov) AS no_mov,
						'' AS td,
						sum(IF(a.c_a = '+', a.monto, 0)) AS cargo,
						sum(IF(a.c_a = '-', a.monto, 0)) AS abono,
						round(
							sum(concat(a.c_a, a.monto)),
							2
						) AS saldotrafico,
						DATEDIFF(
							NOW(),
							IFNULL(b.fecha, c.fecha)
						) AS dias,
						1 AS tabla /* Anticipos */
					FROM
						contagab.asientocontable AS a
					LEFT JOIN contagab.aacgmex AS b ON a.referencia = b.trafico
					AND a.tipo_mov != 'R'
					LEFT JOIN contagab.notaremision_dbf AS c ON a.referencia = c.trafico
					AND a.tipo_mov != 'I'
					WHERE
						a.cuenta = 108 and a.sub_cta=$idclicont
					GROUP BY
						a.referencia,
						a.sub_cta
					HAVING
						saldotrafico != 0
						AND no_cte = $idclicont
						AND cancelada = 0
					/*ANTICIPOS PENDIENTES*/
					UNION
						SELECT
						 	 0 as cancelada,
							 0 as no_cte,
							 t1.fecha,
					     DATE_FORMAT(t1.fecha, '%d/%m/%Y') AS fecha,
						   t1.referencia,
						   t1.concepto,
						   t1.tipo_mov,
						   t1.no_banco,
						   t1.no_mov,
						   t3.monto AS totald,
						   t1.monto AS cargo,
						   concat(t1.c_a, t1.monto) AS abono,
							 '' as saldotrafico,
						   DATEDIFF(NOW(), t1.fecha) AS dias,
						   2 AS tabla /* Anticipos */
					FROM contagab.asientocontable AS t1 RIGHT JOIN
						 (SELECT t02.referencia,
								 sum(concat(t02.c_a, t02.monto)) AS dif
						  FROM contagab.asientocontable AS t02
						  WHERE t02.cuenta = 208 AND
								t02.sub_cta = ".$idclicont." /* Id del cliente*/
								AND t02.monto <> 0
						  GROUP BY t02.referencia
						  HAVING round(dif, 2) != 0) AS t2 ON t1.referencia = t2.referencia LEFT JOIN
						  contagab.asientocontable AS t3 ON t1.tipo_mov = t3.tipo_mov AND
								                            t1.no_banco = t3.no_banco AND
											                t1.no_mov = t3.no_mov AND
											                t3.cuenta = 103
					WHERE t1.cuenta = 208 AND
						  t1.sub_cta = ".$idclicont." /* Id del cliente*/
					      AND t1.monto <> 0
					ORDER BY tabla, fecha_order";

		$query = mysqli_query($cmysqli_sab07,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_sab07);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar el estado de cuenta. Por favor contacte al administrador del sistema.';
			$respuesta['Error'] = ' ['.$error.']';

			exit(json_encode($respuesta));
		}

		// ****************************************************************************************************************** //
		// Extend the TCPDF class to create custom Header and Footer

		class MYPDF extends TCPDF {
			//Page header
			public function Header() {
				global $idclicont, $sNombreCliente, $sDireccionCliente, $sEstadoCliente;
				$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

				$this->SetFont('helvetica', 'B', 16);
				$this->SetY(8);
				$this->Cell(0, 0, 'Estado de Cuenta', 0, 0, 'C');

				$this->Ln(6);
				$this->SetX(115);
				$this->SetFont('helvetica', '', 11);
				$this->Cell(0, 0, 'NUEVO LAREDO, TAMS., '.date('d').' DE '.strtoupper($meses[date('n')-1]).' DE '.date('Y'), 0, 0, 'R');

				$this->SetY(9);
				$this->SetFont('helvetica', '', 10);
				$this->Cell(0, 0, 'Pagina: '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, 0, 'R');

				$this->Ln(17);
				$this->SetFont('helvetica', '', 11);
				$this->SetX(25);
				$this->Cell(0, 0, '<'.$idclicont.'> '.$sNombreCliente, 0, 0, 'L');
				$this->Ln();
				$this->SetX(25);
				$this->Cell(0, 0, $sDireccionCliente, 0, 0, 'L');
				$this->Ln();
				$this->SetX(25);
				$this->Cell(0, 0, $sEstadoCliente, 0, 0, 'L');
				$this->Ln();
				$this->SetX(25);
				$this->Cell(0, 0, 'Atención:', 0, 0, 'L');
				$this->Ln();
				$this->Ln();
				$this->Cell(0, 0, 'ESTADO DE CUENTA AL: '.date('m/d/Y'), 0, 0, 'R');

				$this->Ln();
				$this->SetFont('helvetica', 'B', 10);
				$this->SetX(9);
				$this->Cell(18, 0, 'FECHA', 1, 0, 'C');
				$this->Cell(25, 0, 'REFERENCIA', 1, 0, 'C');
				$this->Cell(25, 0, 'PEDIMENTO', 1, 0, 'C');
				$this->Cell(25, 0, 'CTA GASTOS', 1, 0, 'C');
				$this->Cell(25, 0, ' ', 0, 0, 'C');
				$this->Cell(25, 0, 'CARGO', 1, 0, 'C');
				$this->Cell(25, 0, 'ABONO', 1, 0, 'C');
				$this->Cell(25, 0, 'IMPORTE', 1, 0, 'C');

				$this->Ln(5);
				$this->SetX(102);
				$this->SetFont('helvetica', '', 9);
				$this->Cell(70, 0, 'SALDO INICIAL: ', 0, 0, 'R');
				$this->SetFont('helvetica', 'B', 9);
				$this->Cell(29, 0, number_format(0, 2), 0, 0, 'R');

				// Logo
				$image_file = '../../../images/logo.png';
				$this->Image($image_file, 8, 5, 22, 22, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			}

			// Page footer
			public function Footer() {
				/*$this->SetY(-15);
				$this->SetFont('helvetica', 'I', 8);
				$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');*/
			}
		}

		$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Del Bravo Forwarding, Inc.');
		$pdf->SetTitle('Estado de cuenta');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->SetTopMargin(64);
		//$pdf->SetAutoPageBreak(true, 10);

		// ****************************************************************************************************************** //
		// Pintamos reporte
		$nFontMax = 10;
		$nFontMin = 9;

		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', $nFontMin);
		$border=0;

		$nRangoActual = 0;
		$nRango = 0;
		$nTotalRango = 0;
		$nTotalGeneral = 0;
		$nTotalCargo = 0;
		$nTotalAbono = 0;
		$sUltimoTipoMov = '';
		while($row = mysqli_fetch_object($query)){
			$nRango = obtener_rango($row->dias);
			if ($nRangoActual == 0) {
				$nRangoActual = $nRango;
			}

			$sUltimoTipoMov = (($row->tabla == 1)? 'I': 'D');
			if($sUltimoTipoMov == 'I') {
				if ($nRangoActual != $nRango) {
					pintar_rango($nRangoActual, $nTotalRango);
					$nRangoActual = $nRango;
					$nTotalRango = 0;
				}
			} else {
				if ($nRangoActual > 0) {
					if ($nRangoActual != $nRango) {
						pintar_rango($nRangoActual, $nTotalRango);
						$nRangoActual = -1;
						$nTotalRango = 0;
					}

					$pdf->Ln();
					$pdf->SetX(110);
					$pdf->SetFont('helvetica', 'B', $nFontMin);
					$pdf->Cell(60, 0, 'TOTAL CUENTAS DE GASTOS ', 0, 0, 'R');
					$pdf->Cell(32, 0, number_format($nTotalGeneral, 2), 0, 0, 'R');
					$pdf->Ln();
					$pdf->Ln();

					$pdf->SetFont('helvetica', '', $nFontMin);
					$pdf->Cell(0, 0, 'ANTICIPOS PENDIENTES POR APLICAR', 0, 0, 'C');
					$pdf->Ln();

					$pdf->SetX(87);
					$pdf->SetFont('helvetica', 'B', $nFontMin);
					$pdf->Cell(40, 0, 'TOTAL DEPOSITO', 0, 0, 'R');
					$pdf->Ln();

					$pdf->SetFont('helvetica', '', $nFontMin);

					$nTotalRango = 0;
					$nTotalGeneral = 0;
				}
			}

			$pdf->SetX(9);
			$pdf->Cell(18, 0, $row->fecha, $border, 0, 'C');
			$pdf->Cell(25, 0, trim($row->trafico), $border, 0, 'L');
			if ($sUltimoTipoMov == 'D') {
				$pdf->Cell(25, 0, get_pedimento(trim($row->trafico)), $border, 0, 'C');
				$pdf->Cell(25, 0, $row->tipo_mov.'-'.$row->no_banco.'-'.$row->no_mov, $border, 0, 'C');

				$nAbono = $row->abono;
				if ($nAbono >= 0) {
					$pdf->Cell(25, 0, ' ', $border, 0, 'R');
					$pdf->Cell(25, 0, number_format($row->cargo, 2), $border, 0, 'R');
					$pdf->Cell(25, 0, ' ', $border, 0, 'R');
					$pdf->Cell(25, 0, number_format($row->abono, 2), $border, 0, 'R');

					$nTotalCargo += $row->cargo;
				} else {
					$pdf->Cell(25, 0, number_format($row->td, 2), $border, 0, 'R');
					$pdf->Cell(25, 0, ' ', $border, 0, 'R');
					$pdf->Cell(25, 0, number_format($row->cargo, 2), $border, 0, 'R');
					$pdf->Cell(25, 0, number_format($row->abono, 2), $border, 0, 'R');

					$nTotalAbono += $row->cargo;
				}

				$nTotalGeneral += $row->abono;
			} else {
				$pdf->Cell(25, 0, trim($row->pedimento), $border, 0, 'C');
				$pdf->Cell(25, 0, $row->tipo_mov.'-  '.$row->no_mov, $border, 0, 'C');
				$pdf->Cell(25, 0, ' ', $border, 0, 'R');
				$pdf->Cell(25, 0, number_format($row->cargo, 2), $border, 0, 'R');
				$pdf->Cell(25, 0, number_format($row->abono, 2), $border, 0, 'R');
				$pdf->Cell(25, 0, number_format(($row->cargo - $row->abono), 2), $border, 0, 'R');

				$nTotalRango += $row->cargo - $row->abono;
				$nTotalGeneral += $row->cargo - $row->abono;
				$nTotalCargo += $row->cargo;
				$nTotalAbono += $row->abono;
			}
			$pdf->Ln();
		}

		//error_log('nRangoActual: '.$nRangoActual);
		//error_log('nRango: '.$nRango);
		if ($nRangoActual > 0) {
			//if ($nRangoActual != $nRango) {
				pintar_rango($nRangoActual, $nTotalRango);
			//}

			$pdf->Ln();
			$pdf->SetX(110);
			$pdf->SetFont('helvetica', 'B', $nFontMin);
			$pdf->Cell(60, 0, 'TOTAL CUENTAS DE GASTOS ', 0, 0, 'R');
			$pdf->Cell(32, 0, number_format($nTotalGeneral, 2), 0, 0, 'R');
			$pdf->SetFont('helvetica', '', $nFontMin);
		}

		//error_log('$sUltimoTipoMov ' . $sUltimoTipoMov);
		//error_log('$nTotalGeneral ' . $nTotalGeneral);
		if ($sUltimoTipoMov == 'D') {
			$pdf->Ln();
			$pdf->SetX(110);
			$pdf->SetFont('helvetica', 'B', $nFontMin);
			$pdf->Cell(60, 0, 'TOTAL ANTICIPOS POR APLICAR ', 0, 0, 'R');
			$pdf->Cell(32, 0,'$'.number_format($nTotalGeneral, 2), 0, 0, 'R');
			$pdf->Ln();
		}

		$pdf->Ln();
		$pdf->SetX(102);
		$pdf->SetFont('helvetica', '', $nFontMin);
		$pdf->Cell(25, 0, 'TOTALES ', 'T', 0, 'R');
		$pdf->Cell(25, 0, number_format($nTotalCargo, 2), 'T', 0, 'R');
		$pdf->Cell(25, 0, number_format($nTotalAbono, 2), 'T', 0, 'R');
		$pdf->Cell(25, 0, ' ', 'T', 0, 'R');
		$pdf->Ln();
		$pdf->Ln();

		$pdf->SetX(102);
		$pdf->SetFont('helvetica', 'B', $nFontMax);
		$pdf->Cell(70, 0, 'SALDO FINAL: ', 0, 0, 'R');
		$pdf->Cell(30, 0, '$'.number_format(($nTotalCargo - $nTotalAbono), 2), 0, 0, 'R');

		// Logo
		$pdf->Ln();
		$pdf->Ln();
		$image_file = '../../../images/datoscuentamex.png';
		$pdf->Image($image_file, 8, '', 195, 52, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		// ****************************************************************************************************************** //
		// Guardando archivo

		$sPathFiles = $dir_archivos_web."sii\\estadodecuentamex\\";
		$sFileName = uniqid().'.pdf';
		//$pdf->Output($sPathFiles.$sFileName, 'F');
		//$pdfbase64 = base64_encode($pdf->Output('EstadoCuenta.pdf', 'E'));
		$pdfbase64 = base64_encode($pdf->Output('EstadoCuenta.pdf', 'S'));

		$respuesta['codigo']='1';
		$respuesta['mensaje']="Reporte generado con exito";
		//$respuesta['link']='estadodecuentamex/'.$sFileName;
		$respuesta['data']=$pdfbase64;

		eliminar_archivos_viejos($sPathFiles);
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}

	echo json_encode($respuesta);
}

// ****************************************************************************************************************** //
// FUNCIONES
// ****************************************************************************************************************** //

function obtener_rango($dias){
	if ($dias >= 90) {
		return 91;
	} else if ($dias>= 60) {
		return 90;
	} else if ($dias >= 30) {
		return 60;
	} else {
		return 30;
	}
}

function pintar_rango($rango, $nTotalRango){
	global $pdf, $border;

	$pdf->SetX(110);
	$pdf->SetFillColor(255, 255, 0);
	if ($rango == 91) {
		$pdf->Cell(60, 0, 'SALDO MAS DE 90 DIAS: ', $border, 0, 'R', 1);
	} else if ($rango == 90) {
		$pdf->Cell(60, 0, 'SALDO 90 DIAS: ', $border, 0, 'R', 1);
	} else if ($rango == 60) {
		$pdf->Cell(60, 0, 'SALDO 60 DIAS: ', $border, 0, 'R', 1);
	} else {
		$pdf->Cell(60, 0, 'SALDO 30 DIAS: ', $border, 0, 'R', 1);
	}
	$pdf->Cell(32, 0, number_format($nTotalRango, 2), 'T', 0, 'R', 1);
	$pdf->Ln();
}

function get_pedimento($sTrafico) {
	global $cmysqli_sab07;

	if ($sTrafico != '') {
		$consulta = "SELECT concepto
					 FROM sab07web.todistra
					 WHERE trafico='".$sTrafico."'
					 GROUP BY trafico";

		$query = mysqli_query($cmysqli_sab07,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_sab07);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar el pedimento. Por favor contacte al administrador del sistema.';
			$respuesta['Error'] = ' ['.$error.']';

			exit(json_encode($respuesta));
		} else {
			while($row = mysqli_fetch_array($query)){
				return trim($row['concepto']);
				break;
			}
		}
	} else {

	}
}

function eliminar_archivos_viejos($path){
	$fileSystemIterator = new FilesystemIterator($path);
	$now = time();
	foreach ($fileSystemIterator as $file) {
		//3600 segundos que equivale a 1 hora
		if ($now - $file->getCTime() >= 3600) {
			unlink($path . DIRECTORY_SEPARATOR . $file->getFilename());
		}
	}
}
