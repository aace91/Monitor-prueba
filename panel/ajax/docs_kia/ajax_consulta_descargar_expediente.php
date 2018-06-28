<?php
include_once('./../../../checklogin.php');
include('./../../../connect_casa.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {  
		$respuesta['Codigo']=1;
		$referencia = $_POST['referencia'];
		
		$qCasa = "SELECT a.NUM_REM,b.ADU_DESP, b.PAT_AGEN, b.NUM_PEDI
							FROM SAAIO_FACTUR a
								INNER JOIN SAAIO_PEDIME b ON
									a.NUM_REFE = b.NUM_REFE 
							WHERE a.NUM_REFE = '$referencia'
							GROUP BY a.NUM_REM,b.ADU_DESP, b.PAT_AGEN, b.NUM_PEDI";
		$resped = odbc_exec ($odbccasa, $qCasa);
		if ($resped == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar remesas del pedimento'".$fac."'. BD.CASA.";
			$respuesta['Error'] = odbc_error();
		}else{
			$aRemesas = array(); $nItem = 0;
			while(odbc_fetch_row($resped)){
				if($nItem == 0){
					$Aduana = odbc_result($resped,"ADU_DESP");
					$Patente = odbc_result($resped,"PAT_AGEN");
					$Pedimento = odbc_result($resped,"NUM_PEDI");
					$respuesta['Pedimento'] = $Aduana.'-'.$Patente.'-'.$Pedimento;
				}
				array_push($aRemesas,odbc_result($resped,"NUM_REM"));
			}
			$respuesta['aRemesas'] = $aRemesas;
		}
		
		
		
		/*if(file_exists ( "\\\\192.168.1.126\\Pedimentos2009\\".$referencia.".pdf")){
			//$respuesta['Pedimento'] = '<a href="http://www.delbravoweb.com/pedimentos/'.$referencia.'.pdf">PEDIMETO</a>';
			$respuesta['Pedimento'] = '<div class="alert alert-success">OK</div>';
		}else{
			$respuesta['Codigo']=-1;
			$respuesta['Pedimento'] = '<div class="alert alert-danger">NO EXISTE!</div>';
		}
		$respuesta['ReporteXML'] = '<div class="alert alert-success">OK</div>';
		$respuesta['ReporteUMT'] = '<div class="alert alert-success">OK</div>';
		
		$consulta = "SELECT NUM_REFE,NUM_REM
						FROM SAAIO_FACTUR
						WHERE NUM_REFE = '".$referencia."'
						GROUP BY NUM_REM
						ORDER BY NUM_REM DESC";
		$resped = odbc_exec ($odbccasa, $consulta);
		if ($resped == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar el numero de remesas. [DB.CASA.'.$consulta.']';
			$respuesta['Error'] = odbc_error();
			exit(json_encode($respuesta));
		}
		$respuesta['nRemesas'] = array();
		while(odbc_fetch_row($resped)){
			array_push($respuesta['nRemesas'],odbc_result($resped,"NUM_REM"));
		}
		//Facturas / BL
		$sql="
			SELECT  a.COM_IDEN,a.NOM_ARCH,b.fec_pago,b.num_refeo
			FROM SAAIO_IDEPED a
			LEFT JOIN SAAIO_PEDIME b on a.num_refe=b.num_refe
			WHERE a.CVE_IDEN='ED' AND a.NUM_REFE='".$referencia."'";
		$resultsql=odbc_exec($odbccasa,$sql);
		$error=odbc_errormsg(); 
		if ($error!=''){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar la informacion de los archivos digitalzados.[eDocuments/Facturas,BL]. [DB.CASA.'.$consulta.']';
			$respuesta['Error'] = $error;
			exit(json_encode($respuesta));
		}
		$edocs=array();
		while(odbc_fetch_row($resultsql)){
			$fec_pago=odbc_result($resultsql,"FEC_PAGO");
			$nom_arch=rtrim(odbc_result($resultsql,"NOM_ARCH"));
			$edocument=rtrim(odbc_result($resultsql,"COM_IDEN"));
			$numrefeo=rtrim(odbc_result($resultsql,"NUM_REFEO"));
			$nedoc=$edocument.'.pdf';
			//$rutaxml="\\\\192.168.1.107\\gabdata\\CASAWIN\\cove\\ventanilla\\edocumentpdf\\".($numrefeo!='' ? $numrefeo : $referencia)."\\".$nedoc;
			//array_push($edocs,array($rutaxml,$nom_arch));
			array_push($edocs,$nom_arch);
		}
		if(count($edocs)<=0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Este pedimento no cuenta con documentos digitalizados (FACTURAS/BL)'.$sql;
			exit();
		}
		$respuesta['Digitalizados'] = '<div class="alert alert-success">OK</div>';
		$respuesta['Documentos'] = $edocs;
		$respuesta['COVES'] = '<div class="alert alert-success">OK</div>';*/
		/*********************************************************************************/
	/*$nombreArchivo = $referencia."_EDOCUMENTS_PDFs";
	$zip = new ZipArchive();
	$zip->open($nombreArchivo, ZipArchive::OVERWRITE);
	foreach( $edocs as $x=>$y) { 
		$zip->addFile($y[0],$y[1]);
	}
	$zip->close();
	header("Content-type: application/zip");
	header("Content-Disposition: attachment; filename=$nombreArchivo.zip");
	header("Content-Transfer-Encoding: binary");
	readfile($nombreArchivo);
	unlink($nombreArchivo);*/
		
		/*$consulta = "SELECT a.NUM_REFE,a.ADU_DESP,a.PAT_AGEN,a.NUM_PEDI,
							f.NUM_FACT,b.CONS_FACT,b.CONS_PART,b.NUM_PART,b.FRACCION,b.DES_MERC
						FROM SAAIO_PEDIME a
							INNER JOIN SAAIO_FACTUR f ON
								a.NUM_REFE = f.NUM_REFE 
							INNER JOIN SAAIO_FACPAR b ON
								a.NUM_REFE = b.NUM_REFE AND
								f.CONS_FACT = b.CONS_FACT
						WHERE a.NUM_REFE = '".$referencia."'";
						
		$resped = odbc_exec ($odbccasa, $consulta);
		if ($resped == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar la informacion de los pedimentos. [DB.CASA.'.$consulta.']'.odbc_error();
			exit(json_encode($respuesta));
		}
		$aPartidas = array();
		while(odbc_fetch_row($resped)){
			
			$respuesta['NUM_REFE'] = odbc_result($resped,"NUM_REFE");*/
			
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}
