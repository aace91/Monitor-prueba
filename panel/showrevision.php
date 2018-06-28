<?php
include_once('./../checklogin.php');
include('./../connect_dbsql.php');
if (!isset($_GET['app'])) {
	if($loggedIn == false){
		header("Location: ./../login.php"); 
	}
}

if (!isset($_GET['id'])) {
	exit("No se recibio el numero de revision");
}
if (!isset($_GET['referencia'])) {
	exit("No se recibio la referencia");
}
if (!isset($_GET['factura'])) {
	exit("No se recibio el numero de factura");
}
$revision=$_GET['id'];
$referencia=$_GET['referencia'];
$factura=$_GET['factura'];
$querym="SELECT manual,referencia from revision_general where id_revision=$revision";
$consultam= mysqli_query($cmysqli,$querym) or die("<br><center><p>Error al consultar el tipo de revision ".$querym."<p></center>");
$nrows = mysqli_num_rows($consultam);
if($nrows > 0){
	while($row = mysqli_fetch_array($consultam)){
		$manual=$row['manual'];
		$referencia=$row['referencia'];
	}
}else{
	exit("La revisión $revision no existe en la base de datos");
}
if($manual=='S'){
	$ruta = "\\\\192.168.2.33\\dbdata\\revman\\".$referencia.".pdf";
	header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
	header("Content-Type: application/pdf");
	header("Content-Transfer-Encoding: Binary");
	header("Content-Length: ".filesize($ruta));
	header("Content-Disposition: inline; filename=\"Revision_".rtrim($revision).".pdf\"");
	readfile($ruta);
	//echo $contenido;
	exit;
}else{
	$selection_formula = "{revision_general.referencia} = '".$referencia."' and {revision_general.factura} ='".$factura."'";
	$COM_Object = 'CrystalRuntime.Application'; 
	try {
		$crapp  = new COM ($COM_Object) or die("Unable to Create Object"); 
	} catch (com_exception $e) {
		exit($e->getMessage());
	}
	$my_report = __DIR__ . DIRECTORY_SEPARATOR ."revision.rpt";
	$my_pdf = tempnam(".\\", "CR");;
	try
	{
		$creport = $crapp->OpenReport($my_report, 1);	
		$creport->Database->LogOnServer("p2sodbc.dll", "bodegamysql", $mysqldb, $mysqluser, $mysqlpass);
		$creport->EnableParameterPrompting = 0; 
		$creport->FormulaSyntax = 0;  
		$creport->RecordSelectionFormula=$selection_formula;  
		$creport->DiscardSavedData;
		$creport->ReadRecords();
		$creport->ExportOptions->DiskFileName=$my_pdf;
		$creport->ExportOptions->FormatType=31;
		$creport->ExportOptions->DestinationType=1;
		$creport->Export(false);
		$creport = null;
		$crapp = null;
	//    $ObjectFactory = null;
	} 
	catch(com_exception $error){
		exit($error->getMessage());
	}
	if (headers_sent()) {
		echo 'HTTP header already sent';
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header("Content-Type: application/pdf");
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length: ".filesize($my_pdf));
		header("Content-Disposition: inline; filename=\"Revision_".rtrim($revision).".pdf\"");
		header('Accept-Ranges: bytes');
		@readfile($my_pdf);
		unlink($my_pdf);
		exit;
	}
}