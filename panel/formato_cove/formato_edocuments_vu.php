<?php
	
	include('../../connect_dbsql.php');
	include('../../connect_casa.php');
	require('tcpdf/tcpdf.php');
	require('tcpdf/Output.php');
	include('generar_archivos_edocuments_pdf.php');
	
	$referencia = $_GET['referencia'];
	
	if (!isset($_GET['referencia']) || trim($referencia) == '') {
		exit("Error al recibir los datos de entrada");
	}
	
	$respuesta = generar_archivos_pdf_edocuments($referencia,'seccPedimentos');
	
	if($respuesta['Codigo'] != 1){
		foreach ($aeDocuments as $edocument) {
		  unlink($edocument);
		}
		exit($respuesta['Mensaje']);
	}
	$aeDocuments = $respuesta['aeDocuments'];
	
	if (count($aeDocuments) > 0){
		$zipname = $referencia.'_'.'eDocuments.zip';
		$zip = new ZipArchive;
		$zip->open($zipname, ZipArchive::CREATE);
		foreach ($aeDocuments as $edocument) {
		  $zip->addFile($edocument);
		}
		$zip->close();
		
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename='.$zipname);
		header('Content-Length: ' . filesize($zipname));
		readfile($zipname);
		
		foreach ($aeDocuments as $edocument) {
		  unlink($edocument);
		}
		unlink($zipname);
	}
	exit();
	
	
	
?>