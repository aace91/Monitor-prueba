<?php
	$zipname = $_GET['link'];
	error_log($zipname);
	if (isset($zipname) && !empty($zipname)) {
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename='.$zipname);
		header('Content-Length: ' . filesize($zipname));
		readfile($zipname);
		unlink($zipname);
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "458 : Error al recibir los datos de entrada.".$_GET['link'];
	}
	echo json_encode($respuesta);