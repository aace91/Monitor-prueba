<?php
if (headers_sent()) {
	echo 'HTTP header already sent';
} else {
	$referencia=$_GET["referencia"];
	$id=$_GET["id"];
	$file=$referencia."_adicional_$id.jpg";
	$ruta = "\\\\192.168.2.33\dbdata\bodega\FotosAdicionalesweb\\$file";
	header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
	header("Content-Type: image/jpg");
	header("Content-Transfer-Encoding: Binary");
	header("Content-Length: ".filesize($ruta));
	header("Content-Disposition: inline; filename=\"".basename($ruta)."\"");
	readfile($ruta);
	//echo $contenido;
	exit;
}