<?php
include_once('./../checklogin.php');
if($loggedIn == false){
	header("Location: ./../login.php"); 
}
if (!isset($_GET['id'])) {
	exit("No se recibio el numero de remisión");
}
$remision=$_GET['id'];
$selection_formula = "{RemisionGral.remision} = ".$remision;
$COM_Object = 'CrystalRuntime.Application'; 
try {
	$crapp  = new COM ($COM_Object) or die("Unable to Create Object"); 
} catch (com_exception $e) {
	echo $e->getMessage();
}
$my_report = __DIR__ . DIRECTORY_SEPARATOR ."remision.rpt";
$my_pdf = tempnam("", "CR");;
try
{
	$creport = $crapp->OpenReport($my_report, 1);	
	$creport->Database->LogOnServer("p2sodbc.dll", "bodegamysql", $mysqldb, $mysqluser, $mysqlpass);
	//$creport->Database->LogOnServer("p2sodbc.dll", "RemisionesMySQL", $mysqldb, $mysqluser, $mysqlpass);
	//$creport->LogonInfo( 'dsn=Remisiones;uid="";pwd="";dsq=');
	//$creport->Database->Tables(1)->SetLogOnInfo("RemisionesMySQL", "RemisionesMySQL", "root", "Marianar0117c");
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
	echo $error->getMessage();
}
	if (headers_sent()) {
		echo 'HTTP header already sent';
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header("Content-Type: application/pdf");
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length: ".filesize($my_pdf));
		header("Content-Disposition: attachment; filename=\"Remision_".rtrim($remision).".pdf\"");
		header('Accept-Ranges: bytes');
		@readfile($my_pdf);
		unlink($my_pdf);
		exit;
	}