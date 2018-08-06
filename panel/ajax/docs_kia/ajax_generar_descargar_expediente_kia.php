<?php
include_once('./../../../checklogin.php');
include('./../../../connect_dbsql.php');
include('./../../../connect_casa.php');
include('./../../formato_cove/generar_archivos_coves_pdf.php');
include('generar_archivo_xml.php');
include('generar_reporte_UMT.php');
	
if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {  
		$respuesta['Codigo']=1;
		$referencia = $_POST['referencia'];
		$nRemesas = $_POST['nRemesas'];
		$numero_parte = $_POST['numero_parte'];
		$files = $_FILES; $ADU_DESP='';$Patente='';
		
		//Consultar_Numero_Pedimento
		$consulta = "SELECT substring(a.ADU_DESP from 1 for 2) as CLAVE_ADUANA, a.PAT_AGEN, a.NUM_PEDI,a.FEC_PAGO,a.ADU_DESP
						FROM SAAIO_PEDIME a
						WHERE a.NUM_REFE = '".$referencia."'";
		$resped = odbc_exec ($odbccasa, $consulta);
		if ($resped == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar el numero de remesas. [DB.CASA.'.$consulta.']';
			$respuesta['Error'] = odbc_error();
			exit(json_encode($respuesta));
		}
		$respuesta['nRemesas'] = array();
		$nPedimento = '';
		while(odbc_fetch_row($resped)){
			$ADU_DESP = odbc_result($resped,"ADU_DESP");
			$Aduana = odbc_result($resped,"CLAVE_ADUANA");
			$Patente = odbc_result($resped,"PAT_AGEN");
			$Pedimento = odbc_result($resped,"NUM_PEDI");
			$Anio = date('y',strtotime(odbc_result($resped,"FEC_PAGO")));
			$nPedimento = $Anio.' '.$Aduana.' '.$Patente.' '.$Pedimento;
		}
		if($nPedimento == ''){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al generar el numero de pedimento.';
			$respuesta['Error'] = '['.$consulta.']';
			exit(json_encode($respuesta));
		}
		//Pedimento
		$aArchivos = array();
		if(file_exists("D:\\pedimentos2009\\".$referencia.".pdf")){
			$sPedFile = 'documentos/'.$nPedimento.'.pdf';
			if(!copy("D:\\pedimentos2009\\".$referencia.".pdf",$sPedFile)){
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error al guardar el archivo [ "D:\\pedimentos2009\\'.$referencia.'.pdf"]['.$sPedFile.'] en el servidor.';
				$respuesta['Error'] = '';
				exit(json_encode($respuesta));
			}
			array_push($aArchivos,$sPedFile);
		}else{
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje'] = 'El archivo del pedimento pagado no existe para la referencia '.$referencia.'.[P:\]';
			$respuesta['Error'] = '';
		}
		//REPORTE UMT
		$resUMT = crear_archivo_excel_UMT($referencia,$numero_parte,$nPedimento);
		if($resUMT['Codigo'] == 1){
			array_push($aArchivos,$resUMT['NomExcelUMT']);
		}else{
			eliminar_archivos_creados_expediente($aArchivos);
			$respuesta = $resUMT;
			exit(json_encode($respuesta));
		}
		//REPORTE PECA
		$resPECA = crear_archivo_excel_PECA($referencia,$nPedimento);
		if($resPECA['Codigo'] == 1){
			array_push($aArchivos,$resPECA['NomExcelPECA']);
		}else{
			eliminar_archivos_creados_expediente($aArchivos);
			$respuesta = $resPECA;
			exit(json_encode($respuesta));
		}
		//function crear_archivo_excel_PECA($Referencia,$nPedimento){
		//REPORTE XML
		$Resp = crear_archivo_xml_kia($referencia, $numero_parte);
		if($Resp['Codigo'] == 1){
			array_push($aArchivos,$Resp['NomXML']);
		}else{
			eliminar_archivos_creados_expediente($aArchivos);
			$respuesta = $Resp;
			exit(json_encode($respuesta));
		}
		//Remesas Moduladas
		for($i=0; $i<$nRemesas; $i++){
			if(isset($files['f_remesa_'.($i+1)])) {
				if($files['f_remesa_'.($i+1)]["error"] == 0) {
					$Remesa = $files['f_remesa_'.($i+1)]["tmp_name"];				
					$ext = pathinfo($files['f_remesa_'.($i+1)]["name"], PATHINFO_EXTENSION);
					$NomRemesa = 'documentos/'.$nPedimento.'_'.($i+1).".".$ext;
					if(!isset($Remesa)) {
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'El tamaño del archivo de la remesa '.($i+1).' excede el máximo permitido.';
						$respuesta['Error'] = '';
						eliminar_archivos_creados_expediente($aArchivos);
						exit(json_encode($respuesta));
					}else{
						if(!move_uploaded_file($Remesa,$NomRemesa)){
							$respuesta['Codigo'] = '-1';
							$respuesta['Mensaje'] = 'Error al guardar el archivo de la remesa '.($i+1).' en el servidor.';
							$respuesta['Error'] = '';
							eliminar_archivos_creados_expediente($aArchivos);
							exit(json_encode($respuesta));
						}else{
							array_push($aArchivos,$NomRemesa);
						}
					}
				}else{
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error en el archivo de la remesa '.($i+1);
					$respuesta['Error'] = '';
					eliminar_archivos_creados_expediente($aArchivos);
					exit(json_encode($respuesta));
				}
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error al recibir el archivo de la remesa '.($i+1).'.';
				$respuesta['Error'] = '';
				eliminar_archivos_creados_expediente($aArchivos);
				exit(json_encode($respuesta));
			}
		}
		//FACTURAS - PL - BL [Todo lo que se digitaliza, si no se degitaliza algun docuemnto faltara en el expediente.]
		$sql="
			SELECT  a.COM_IDEN,a.NOM_ARCH,b.fec_pago,b.num_refeo
			FROM SAAIO_IDEPED a
			LEFT JOIN SAAIO_PEDIME b on a.num_refe=b.num_refe
			WHERE a.CVE_IDEN='ED' AND a.NUM_REFE='".$referencia."'";
		$resultsql=odbc_exec($odbccasa,$sql);
		$error=odbc_errormsg(); 
		if ($error!=''){
			echo $error;
			exit;
		}
		$edocs=array();
		while(odbc_fetch_row($resultsql)){
			$fec_pago=odbc_result($resultsql,"FEC_PAGO");
			$nom_arch=rtrim(odbc_result($resultsql,"NOM_ARCH"));
			$edocument=rtrim(odbc_result($resultsql,"COM_IDEN"));
			$numrefeo=rtrim(odbc_result($resultsql,"NUM_REFEO"));
			$nedoc=$edocument.'.pdf';
			$rutaxml="\\\\192.168.1.107\\gabdata\\CASAWIN\\cove\\ventanilla\\edocumentpdf\\".($numrefeo!='' ? $numrefeo : $referencia)."\\".$nedoc;
			$rutaExpIDE = 'documentos/'.$nom_arch;
			if(file_exists($rutaxml)){
				if(!copy($rutaxml,$rutaExpIDE)){
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error al guardar el archivo digitalizado '.$nom_arch.' del identificador '.$edocument.' en el servidor.';
					$respuesta['Error'] = '';
					eliminar_archivos_creados_expediente($aArchivos);
					exit(json_encode($respuesta));
				}
				array_push($aArchivos,$rutaExpIDE);
			}else{
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje'] = 'El archivo del edocument '.$edocument.' no existe en el directorio.['.$rutaxml.']';
				$respuesta['Error'] = '';
				eliminar_archivos_creados_expediente($aArchivos);
				exit(json_encode($respuesta));
			}
		}
		//COVES
		$resCOVEpdf = generar_archivos_pdf_cove($referencia,'expediente');
		if($resCOVEpdf['Codigo'] != 1){
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje'] = $resCOVEpdf['Mensaje'];
			$respuesta['Error'] = '';
			eliminar_archivos_creados_expediente($aArchivos);
			exit(json_encode($respuesta));
		}
		$aCOVES = $resCOVEpdf['aCOVES'];
		//Guardar en el directorio del expediente con el formato requerido por KIA
		if (count($aCOVES) > 0){
			//Guardar ruta de los archivos PDF generados para eliminar en caso de error o al finalizar proceso
			foreach ($aCOVES as $cove) {
				array_push($aArchivos,$cove);
			}
			$consulta = "SELECT a.NUM_REFE, a.E_DOCUMENT, f.NUM_FACT
							FROM SAAIO_COVE a
								INNER JOIN SAAIO_FACTUR f ON
									a.NUM_REFE = f.NUM_REFE and
									a.CONS_FACT = F.CONS_FACT
							WHERE a.NUM_REFE = '".$referencia."'";
			
			$result = odbc_exec ($odbccasa, $consulta);
			if (!$result){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al consultar las facturas de CASA. ".odbc_error($odbccasa);
				$respuesta['Error'] = '';
				eliminar_archivos_creados_expediente($aArchivos);
				return $respuesta;
			}
			while(odbc_fetch_row($result)){
				$COVE = odbc_result($result,"E_DOCUMENT");
				$NUM_FACT = odbc_result($result,"NUM_FACT");
				
				$nomOriginal = $COVE.'.pdf';
				$nomExpediente = 'documentos/VA_'.$NUM_FACT.'.pdf';
				
				if(file_exists($nomOriginal)){
					if(!copy($nomOriginal,$nomExpediente)){
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'Error al guardar el archivo PDF del COVE '.$COVE.' en el directorio del expediente.';
						$respuesta['Error'] = '';
						eliminar_archivos_creados_expediente($aArchivos);
						exit(json_encode($respuesta));
					}
					array_push($aArchivos,$nomExpediente);//Revisar que guarde los PDF de los coves 
					
				}else{
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje'] = 'El archivo PDF del COVE '.$COVE.' no se genero.';
					$respuesta['Error'] = '';
					eliminar_archivos_creados_expediente($aArchivos);
					exit(json_encode($respuesta));
				}
			}
		}
		//Archivo Validacion
		$consulta = "SELECT FIRST 1 a.ID_ARCH, a.NUM_REFE, m.NOM_ARCH, m.DAT_ARCH
						FROM SAAIO_ARCHMD a
							INNER JOIN SAAIO_ARCHM m ON
								a.ID_ARCH = m.ID_ARCH
						WHERE a.NUM_REFE = '".$referencia."'
						ORDER BY a.ID_ARCH DESC";
			
		$result = odbc_exec ($odbccasa, $consulta);
		if (!$result){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar el archivo de validacion en CASA. ".odbc_error($odbccasa);
			$respuesta['Error'] = '';
			eliminar_archivos_creados_expediente($aArchivos);
			return $respuesta;
		}
		while(odbc_fetch_row($result)){
			$NOM_ARCH = odbc_result($result,"NOM_ARCH");
			$DAT_ARCH = odbc_result($result,"DAT_ARCH");
			//$NOM_ARCH_RESP = str_replace($NOM_ARCH, "E", "a");
			$NOM_ARCH_RESP = explode('.',$NOM_ARCH)[0].'.err';
			
			if($ADU_DESP == '240'){
				$ADU_DESP = 'laredo';
			}else{
				$ADU_DESP = 'colombia';
			}
			$sRutaArchivos = "\\\\192.168.1.107\\gabdata\\CASAWIN\\".$ADU_DESP.$Patente.'\\';
			//Archivo Envio
			$sNomArchVal = 'documentos/'.$NOM_ARCH;
			if(file_exists($sRutaArchivos.$NOM_ARCH)){
				if(!copy($sRutaArchivos.$NOM_ARCH,$sNomArchVal)){
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error al guardar el archivo de validacion en el expediente.'.$NOM_ARCH;
					$respuesta['Error'] = '';
					eliminar_archivos_creados_expediente($aArchivos);
					exit(json_encode($respuesta));
				}
				array_push($aArchivos,$sNomArchVal);
				
			}else{
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje'] = 'No existe el archivo de validacion del pedimento.'.$sRutaArchivos.$NOM_ARCH;
				$respuesta['Error'] = '';
				eliminar_archivos_creados_expediente($aArchivos);
				exit(json_encode($respuesta));
			}
			//Archivo Respuesta
			$sNomArchVal = 'documentos/'.$NOM_ARCH_RESP;
			if(file_exists($sRutaArchivos.$NOM_ARCH_RESP)){
				if(!copy($sRutaArchivos.$NOM_ARCH_RESP,$sNomArchVal)){
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error al guardar el archivo de validacion en el expediente.'.$NOM_ARCH_RESP;
					$respuesta['Error'] = '';
					eliminar_archivos_creados_expediente($aArchivos);
					exit(json_encode($respuesta));
				}
				array_push($aArchivos,$sNomArchVal);
				
			}else{
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje'] = 'No existe el archivo de validacion del pedimento.'.$sRutaArchivos.$NOM_ARCH_RESP;
				$respuesta['Error'] = '';
				eliminar_archivos_creados_expediente($aArchivos);
				exit(json_encode($respuesta));
			}
		}
		//Archivo PAGO
		$consulta = "SELECT FIRST 1 a.NUM_REFE, a.FEC_CREA, a.NOM_ARCH, a.CVE_CNTA, a.CVE_BANC, a.CVE_AUT
						FROM SAAIO_ARCHPAGO a
						WHERE a.NUM_REFE = '".$referencia."'
						ORDER BY a.FEC_CREA DESC";
			
		$result = odbc_exec ($odbccasa, $consulta);
		if (!$result){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar el archivo de pago en CASA. ".odbc_error($odbccasa);
			$respuesta['Error'] = '';
			eliminar_archivos_creados_expediente($aArchivos);
			return $respuesta;
		}
		while(odbc_fetch_row($result)){
			$NOM_ARCH = odbc_result($result,"NOM_ARCH");
			$NOM_ARCH_RESP = str_replace("E", "a", $NOM_ARCH);
			
			if($ADU_DESP == '240'){
				$ADU_DESP = 'laredo';
			}else{
				$ADU_DESP = 'colombia';
			}
			$sRutaArchivos = "\\\\192.168.1.107\\gabdata\\CASAWIN\\".$ADU_DESP.$Patente.'\\';
			//Archivo Envio
			$sNomArchVal = 'documentos/'.$NOM_ARCH;
			if(file_exists($sRutaArchivos.$NOM_ARCH)){
				if(!copy($sRutaArchivos.$NOM_ARCH,$sNomArchVal)){
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error al guardar el archivo de pago en el expediente.'.$NOM_ARCH;
					$respuesta['Error'] = '';
					eliminar_archivos_creados_expediente($aArchivos);
					exit(json_encode($respuesta));
				}
				array_push($aArchivos,$sNomArchVal);
				
			}else{
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje'] = 'No existe el archivo de pago del pedimento.'.$sRutaArchivos.$NOM_ARCH;
				$respuesta['Error'] = '';
				eliminar_archivos_creados_expediente($aArchivos);
				exit(json_encode($respuesta));
			}
			//Archivo Respuesta
			$sNomArchVal = 'documentos/'.$NOM_ARCH_RESP;
			if(file_exists($sRutaArchivos.$NOM_ARCH_RESP)){
				if(!copy($sRutaArchivos.$NOM_ARCH_RESP,$sNomArchVal)){
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error al guardar el archivo de pago en el expediente.'.$NOM_ARCH_RESP;
					$respuesta['Error'] = '';
					eliminar_archivos_creados_expediente($aArchivos);
					exit(json_encode($respuesta));
				}
				array_push($aArchivos,$sNomArchVal);
				
			}else{
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje'] = 'No existe el archivo de pago del pedimento.'.$sRutaArchivos.$NOM_ARCH_RESP;
				$respuesta['Error'] = '';
				eliminar_archivos_creados_expediente($aArchivos);
				exit(json_encode($respuesta));
			}
		}
		//GENERAR EL ZIP PARA DESCARGAR
		$noZIP = preg_replace('/ +/', '-', $nPedimento);
		$nombreArchivo = 'documentos/'.$noZIP.'.zip';
		$zip = new ZipArchive();
		$zip->open($nombreArchivo, ZipArchive::OVERWRITE);
		foreach( $aArchivos as $file) { 
			$pos = strpos($file, 'COVE');
			if ($pos === false) {
				$zip->addFile($file);
			}
		}
		$zip->close();
		eliminar_archivos_creados_expediente($aArchivos);
		$respuesta['Codigo']=1;
		$respuesta['NombreArchivoZip']=$noZIP;
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

function eliminar_archivos_creados_expediente($pFiles){
	for($i=0; $i<count($pFiles); $i++){
		unlink($pFiles[$i]);
	}
}
