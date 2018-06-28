<?php
include_once('./../../../checklogin.php');
require('./../../../url_archivos.php');
require('./../../../bower_components/TCPDF/tcpdf.php');
$XML_PDF = '';
if($loggedIn == false){
	echo '500::La sesion del usuario ha finalizado. Es necesario que inicie nuevamente.';
} else {
	if (isset($_SESSION['aCopias']) && !empty($_SESSION['aCopias'])) {
		$aCopias = $_SESSION['aCopias'];//json_decode($_POST['aCopias'],true);
		//error_log('ArchFac:'.$aCopias[0]["archivo_factura"]);
		$aFiles = array();
		for($i = 0; $i < count($aCopias); $i++){
			//Archivo Factura
			for($j = 0; $j<$aCopias[$i]["copias_factura"]; $j++){
				$aDirFac = explode('/',$aCopias[$i]["archivo_factura"]);
				$dirFile = array_pop($aDirFac);
				array_push($aFiles,$dir_archivos_facturas.$dirFile);
			}
			//Archivo Factura
			if($aCopias[$i]["archivo_cfdi"] != ''){
				for($j = 0; $j<$aCopias[$i]["copias_cfdi"]; $j++){
					$resFile = generar_pdf_cfdi(array_pop(explode('/',$aCopias[$i]["archivo_cfdi"])));
					if($resFile['Codigo'] == 1){
						$dirFile = $resFile['nomFile'];
						$XML_PDF = $resFile['nomFile'];
					}else{
						exit(json_encode($resFile));
					}
					array_push($aFiles,$dirFile);
				}
			}
			//Archivo Packing List
			if($aCopias[$i]["archivo_packinglist"] != ''){
				for($j = 0; $j<$aCopias[$i]["copias_packinglist"]; $j++){
					$dirFile = array_pop(explode('/',$aCopias[$i]["archivo_packinglist"]));
					array_push($aFiles,$dir_archivos_facturas.$dirFile);
				}
			}
			//Archivo Certificado Origen
			if($aCopias[$i]["archivo_cert_origen"] != ''){
				for($j = 0; $j<$aCopias[$i]["copias_cert_origen"]; $j++){
					$dirFile = array_pop(explode('/',$aCopias[$i]["archivo_cert_origen"]));
					array_push($aFiles,$dir_archivos_facturas.$dirFile);
				}
			}
			//Archivo Ticket Bascula
			if($aCopias[$i]["archivo_ticketbascula"] != ''){
				for($j = 0; $j<$aCopias[$i]["copias_ticketbascula"]; $j++){
					$dirFile = array_pop(explode('/',$aCopias[$i]["archivo_ticketbascula"]));
					array_push($aFiles,$dir_archivos_facturas.$dirFile);
				}
			}
			//Archivo Aviso Automatico
			if($aCopias[$i]["archivo_permiso"] != ''){
				for($j = 0; $j<$aCopias[$i]["copias_permiso"]; $j++){
					$dirFile = array_pop(explode('/',$aCopias[$i]["archivo_permiso"]));
					array_push($aFiles,$dir_archivos_permisos.$dirFile);
				}
			}
			//Archivo Aviso Automatico
			if($aCopias[$i]["archivo_permiso_adhesion"] != ''){
				for($j = 0; $j<$aCopias[$i]["copias_permiso_adhesion"]; $j++){
					$dirFile = array_pop(explode('/',$aCopias[$i]["archivo_permiso_adhesion"]));
					array_push($aFiles,$dir_archivos_permisos.$dirFile);
				}
			}
		}
		if(count($aCopias) > 0 && count($aFiles) > 0){
			//UNIFICAR PDF
			$sArchivos = '';;
			foreach ($aFiles as &$archivo) { 
				$sArchivos .= $archivo . ' ';
			}
			$sFile = "Documentos_Cruce_".date("YmdHis").".pdf";
			if ($sArchivos != '') {
				$sComando = '"C:\Program Files\gs\gs9.23\bin\gswin64" -dBATCH -dNOPAUSE -q -dSAFER -sDEVICE=pdfwrite -sOutputFile='.$sFile.' '.$sArchivos;
				$output = shell_exec($sComando);		
				if ($output != '') {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error en el proceso que unifica los archivos PDF.[C:\Program Files\gs\gs9.23\bin\gswin64]';
					$respuesta['Error']=$output;
					unlink($XML_PDF);
					unlink($sFile);
					exit(json_encode($respuesta));
				}
			}
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
			header("Content-Type: application/pdf");
			header("Content-Transfer-Encoding: Binary");
			header("Content-Length: ".filesize($sFile));
			header("Content-Disposition: inline; filename=\"".rtrim($sFile).".pdf\"");
			header('Accept-Ranges: bytes');
			@readfile($sFile);
			unlink($sFile);
			//ELIMINAR LOS PDF DE LOS CFDI
			/*foreach ($aFiles as &$archivo) { 
				$sArchivos .= $archivo . ' ';iff CFDI-
			}*/
		}else{
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se cuenta con informacion para procesar en los arreglos.';
			$respuesta['Error']='';
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error']='';
	}
	unlink($XML_PDF);
	exit(json_encode($respuesta));
}
/*           GENERAR XML Y ADJUNTAR TODOS LOS PDF PARA RETORNAR EL PDF UNIFICADO      */
function generar_pdf_cfdi($NomArchivo){
	global $dir_archivos_facturas;
	global $dir_archivos_temp_cruces;
	try{
		$xml = new DOMDocument();
		$ok = $xml->load($dir_archivos_facturas.$NomArchivo);
		if (!$ok) {
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al leer el archivo XML [CFDI]. Por favor, contacte el administrador del sistema.";
			$respuesta['Error'] = $e->getMessage();
			return $respuesta;
		}
		$texto = $xml->saveXML();
		if (strpos($texto,"cfdi:Comprobante")!==FALSE) {
			$tipo="cfdi";
		} elseif (strpos($texto,"<Comprobante")!==FALSE) {
			$tipo="cfd";
		} elseif (strpos($texto,"retenciones:Retenciones")!==FALSE) {
			$tipo="retenciones";
		} else {
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Tipo de XML no identificado .... ".$NomArchivo;
			$respuesta['Error'] = '';
			return $respuesta;
		}
		if ($tipo=="retenciones") {
			$root = $xml->getElementsByTagName('Retenciones')->item(0);
			$Version = $root->getAttribute("Version");
		} else {
			$root = $xml->getElementsByTagName('Comprobante')->item(0);
			$Comprobante = $xml->getElementsByTagName('Comprobante')->item(0);
			$version = $root->getAttribute("version");
			if ($version==null) $version = $root->getAttribute("Version");
		}
		//Obtener Serie y Folio del XML 
		$serie = utf8_decode($root->getAttribute("serie"));
		if (!isset($serie) || empty($serie)) {
			$serie = utf8_decode($root->getAttribute("Serie"));
		}
		$folio = $root->getAttribute('folio');
		if (!isset($folio) || empty($folio)) {
			$folio = $root->getAttribute("Folio");
		}
		//GENERAR PDF del XML
		$xml = simplexml_load_file($dir_archivos_facturas.$NomArchivo);
		$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Grupo Aduanero Del bravo');
		$pdf->SetTitle('CFDI-'.$serie.'-'.$folio);
		$pdf->SetSubject('CFDI-'.$serie.'-'.$folio);
		$pdf->SetKeywords('CFDI-'.$serie.'-'.$folio);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 9);
		$pdf->MultiCell(0, 0, ''.$xml->asXML(), 0, 'L');
		$NomCFDIPDF = $dir_archivos_temp_cruces.'CFDI-'.$serie.'-'.$folio.date("YmdHis").'.pdf';//$dir_archivos_facturas.'CFDI-'.$serie.'-'.$folio.date("YmdHis").'.pdf';
		$pdf->Output($NomCFDIPDF, 'F');
		$respuesta['Codigo'] = 1;
		$respuesta['nomFile'] = $NomCFDIPDF;
	}catch (Exception $e) {
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = 'Error al generar el archivo CFDI a PDF.';
		$respuesta['Error'] = $e->getMessage();
	}
	return $respuesta;
}
