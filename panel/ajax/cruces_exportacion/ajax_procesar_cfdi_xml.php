<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_FILES) && !empty($_FILES)) {
		$xml = new DOMDocument();
		$ok = $xml->load($_FILES["xmlCFDI"]["tmp_name"]);
		if (!$ok) {
			//$xmlerr=display_xml_errors(); 
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al leer el archivo XML []. Por favor, contacte el administrador del sistema.";
			exit(json_encode($respuesta));
		}
		$texto = $xml->saveXML();
		if (strpos($texto,"cfdi:Comprobante")!==FALSE) {
			$tipo="cfdi";
		} elseif (strpos($texto,"<Comprobante")!==FALSE) {
			$tipo="cfd";
		} elseif (strpos($texto,"retenciones:Retenciones")!==FALSE) {
			$tipo="retenciones";
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Tipo de XML no identificado ....";
			exit(json_encode($respuesta));
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
		$serie = utf8_decode($root->getAttribute("serie"));
		if (!isset($serie) || empty($serie)) {
			$serie = utf8_decode($root->getAttribute("Serie"));
		}
		$fechaxml = $root->getAttribute("fecha");
		if (!isset($fechaxml) || empty($fechaxml)) {
			$fechaxml = $root->getAttribute("Fecha");
		}
		$folio = $root->getAttribute('folio');
		if (!isset($folio) || empty($folio)) {
			$folio = $root->getAttribute("Folio");
		}
		$respuesta['serie'] = $serie;
		$respuesta['fecha'] = date('d/m/Y H:i:s',strtotime($fechaxml));
		$respuesta['folio'] = $folio;
		
		$TFD = $root->getElementsByTagName('TimbreFiscalDigital')->item(0);
		if ($TFD!=null) {
			$respuesta['uuid'] = $TFD->getAttribute("UUID");
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="El timbre fiscal del cfdi es incorrecto..";
			exit(json_encode($respuesta));
		}
		
		
		
		$respuesta['Codigo']=1;		
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}