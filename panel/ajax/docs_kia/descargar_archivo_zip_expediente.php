<?php
	$NomArchivo = $_GET['nom'];
	header("Content-type: application/zip");
	header("Content-Disposition: attachment; filename=documentos/".$NomArchivo.".zip");
	header("Content-Transfer-Encoding: binary");
	readfile("documentos/".$NomArchivo.".zip");
	unlink("documentos/".$NomArchivo.".zip");
	exit(0);
?>