<?php
	error_reporting( E_ALL ); 
	$iddoc=$_GET['iddoc'];
	include_once('./../checklogin.php');
	include('./../connect_dbsql.php');
	if($loggedIn == false){ header("Location: ./../login.php"); }
	
	$consulta = mysqli_query($cmysqli, "SELECT contenido,nombre from docs_contenido where id_doc=".$iddoc);
	if (!$consulta) {
		echo 'Error al consultar el documento'.mysqli_error($cmysqli);
		exit;
	}
	while($row = mysqli_fetch_array($consulta)){
		$nombreArchivo = $row['nombre'];
		$contenido = base64_decode($row['contenido']);
		//$archivo=fread ($contenido);
	}
	if (headers_sent()) {
		echo 'HTTP header already sent';
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header("Content-Type: application/pdf");
		header("Content-Transfer-Encoding: Binary");
		//header("Content-Length: ".filesize($nombreArchivo));
		header("Content-Disposition: inline; filename=\"".$nombreArchivo."\"");
		//readfile($archivo);
		echo $contenido;
		exit;
	}
	//unlink($nombreArchivo);
?>