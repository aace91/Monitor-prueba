<?php
	
	include('../../connect_dbsql.php');
	include('../../connect_casa.php');
	include('generar_archivos_coves_pdf.php');
	
	$referencia = $_GET['referencia'];
	
	if (!isset($_GET['referencia']) || trim($referencia) == '') {
		exit("Error al recibir los datos de entrada");
	}
	
	$respuesta = generar_archivos_pdf_cove($referencia);
	
	if($respuesta['Codigo'] != 1){
		exit($respuesta['Mensaje']);
	}
	$aCOVES = $respuesta['aCOVES'];
	
	if (count($aCOVES) > 0){
		$zipname = $referencia.'_'.'COVES.zip';
		$zip = new ZipArchive;
		$zip->open($zipname, ZipArchive::CREATE);
		foreach ($aCOVES as $cove) {
		  $zip->addFile($cove);
		}
		$zip->close();
		
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename='.$zipname);
		header('Content-Length: ' . filesize($zipname));
		readfile($zipname);
		
		foreach ($aCOVES as $cove) {
		  unlink($cove);
		}
		unlink($zipname);
	}
	exit();
	
	
	
?>