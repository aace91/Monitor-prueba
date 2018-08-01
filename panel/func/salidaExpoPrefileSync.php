<?php
include ('../../connect_dbsql.php');
include ('../../vendor/autoload.php');

include ('salidaExpoCartaInstrucciones.php'); //Para el envio de correos

$__sPathPrefile = '\\\\192.168.1.126\\documentos_expo\\prefiles_benavides\\'; 
$__sPathFilesExpo = "\\\\192.168.1.126\\documentos_expo\\salidaExpo";
$__strEmailAlerta = 'jcdelacruz@delbravo.com';

/********************************************************************************/

/****************************************************/
/* PROCESANDO ARCHIVOS */
/****************************************************/

$fileSystemIterator = new FilesystemIterator($__sPathPrefile);
$now = time();
foreach ($fileSystemIterator as $file) {
	$sFileName = $file->getFilename();	
	
	if (pathinfo($sFileName, PATHINFO_EXTENSION) == 'pdf') {
		$sTextOriginal = fcn_get_tex_pdf($sFileName);
		$sTextResiduo = $sTextOriginal;
		
		$sTextCaja = fcn_get_text_seccion($sTextResiduo, 'INWARD CARGO MANIFEST FOR VESSEL UNDER');
		
		$sCaja = fcn_get_data_regexp('/Trailer([\s|0-9a-zA-Z-_&]+)Foreign/', $sTextCaja);
		$sSCAC = fcn_get_data_regexp('/Carrier(\s*[A-Z]{4})/', $sTextCaja);
		$aEntryNumber = fcn_get_data_regexp_entrys('/([a-zA-Z]{3}-[0-9]{7}-[0-9]{1})/', $sTextResiduo);
		
		echo json_encode($aEntryNumber);
		echo '<br>';
		
		$respuesta = fcn_get_guardar_archivo_prefile($aEntryNumber, $sCaja, $sSCAC, $sFileName, $sTextResiduo);
	}
}

//exit('Fin');

/****************************************************/
/* BUSCANDO SALIDAS */
/****************************************************/

$consulta="SELECT a.salidanumero, a.fecha, b.caja, 
				  b.FACTURA_NUMERO
		   FROM bodega.salidas_expo AS a INNER JOIN
				bodega.facturas_expo AS b ON b.SALIDA_NUMERO=a.salidanumero
		   WHERE b.NOAAA=58 AND
				 b.PREFILE_ID IS NULL AND
				 a.fecha >= '2018-07-19' AND
				 a.salidanumero NOT IN ('134627')
		   ORDER BY a.salidanumero DESC";
		   
$query = mysqli_query($cmysqli, $consulta);
if (!$query) {
	$respuesta['Codigo']=-1;
	$respuesta['Mensaje']='Error al consultar la lista de salidas pendientes.'; 
	$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
	
	enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
} else {
	while($row = mysqli_fetch_object($query)){
		$salidanumero = $row->salidanumero;
		$caja = $row->caja;
		$sFactura = $row->FACTURA_NUMERO;
		$bEnviarEmail = false;
		
		$consulta="SELECT a.id_documento
				   FROM bodega.documentos_expo AS a
				   WHERE a.fecha_creacion > ADDDATE(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'), INTERVAL - 7 DAY) AND
						 a.caja='".$caja."' AND 
						 a.factura REGEXP '\\\^".$sFactura."\\\^' AND 
						 a.id_documento NOT IN (SELECT IF(b.SALIDA_NUMERO = ".$salidanumero.", 0, b.PREFILE_ID)
												FROM bodega.facturas_expo AS b
												WHERE b.PREFILE_ID IS NOT NULL)
				   ORDER BY a.fecha_creacion DESC
				   LIMIT 1";
		
		$queryDocs = mysqli_query($cmysqli, $consulta);
		if (!$queryDocs) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los documentos disponibles.'; 
			$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
			
			enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
			break;
		} else {
			while($rowDocs = mysqli_fetch_object($queryDocs)){ 
				$id_documento = $rowDocs->id_documento;
				
				$consulta="UPDATE bodega.facturas_expo
						   SET PREFILE_ID=".$id_documento."
						   WHERE SALIDA_NUMERO=".$salidanumero." AND
								 FACTURA_NUMERO='".$sFactura."'";
														
				$queryUpdate = mysqli_query($cmysqli, $consulta);
				if (!$queryUpdate) {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al actualizar factura con prefile.'; 
					$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
					
					enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
					break;
				} else {
					$bEnviarEmail = true;
					echo 'Salida ['.$salidanumero.'] :: Factura ['.$sFactura.'] :: Prefile Document ['.$id_documento.'] Actualizada.';
					echo '<br>';
				}
				break;
			}
		}
			
		/****************/
		//Intentamos enviar correo
		if ($bEnviarEmail) {
			$respuesta2 = fcn_enviar_notificacion_salida($salidanumero, true);
			if ($respuesta2['Codigo'] != 1) { 
				$respuesta['Mensaje'] .= 'Salida: '.$salidanumero.' :: No se envio la notificacion al cliente '.$respuesta2['Mensaje'].' :: Error:'.$respuesta2['Error'];
				enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
			} 
		}
	}
}

echo '<br>Proceso Finalizado...';

function fcn_get_guardar_archivo_prefile($aEntryNumber, $sCaja, $sSCAC, $sSourceFileName, &$sTextResiduo) {
	global $cmysqli, $__sPathPrefile, $__sPathFilesExpo, $__strEmailAlerta;
	
	$respuesta['Codigo']=1;
	
	/***************************************/
	
	$nUniqueId = uniqid('000_', true);
	
	$ext = explode('.', basename($sSourceFileName));
	$ext = array_reverse($ext);
	$sFileName = array_pop($ext);
	$sFileName = str_replace(" ", "_", $sFileName);
	
	/***************************************/
	$nidDocMaster = '';
	$aIdDocuments = array();
	
	foreach ($aEntryNumber as &$sEntryNumber) {
		$nIdDocumento = '';
		$sNombreArchivo = '';
		
		$sPAPS = $sSCAC;//.str_replace("-","",$sEntryNumber);		
		$sTextEntry = fcn_get_text_seccion($sTextResiduo, 'PREFILE ENTRY '.$sEntryNumber);
		
		$sFacturas = fcn_get_data_regexp('/INV#:\s*([0-9a-zA-Z,-_\s]+)\s*'.$sPAPS.'/', $sTextEntry);
		$sFacturas = fcn_get_facturas_format($sFacturas);
		
		$consulta="SELECT id_documento, id_doc_master, nombre_archivo
				   FROM bodega.documentos_expo AS a
				   WHERE a.referencia = '".$sEntryNumber."'";
				   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar el documentos_expo.'; 
			$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
			
			enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
			break;
		} else {
			$num_rows = mysqli_num_rows($query);
			if ($num_rows > 0) {
				while($row = mysqli_fetch_object($query)){
					$nIdDocumento = $row->id_documento;
					$nombre_archivo = $row->nombre_archivo;

					if (!is_null($row->id_doc_master)) {
						$nidDocMaster = $row->id_doc_master;
					}
					
					$ext = explode('.', basename($nombre_archivo));
					$ext = array_reverse($ext);
					$sName = array_pop($ext);
					
					//Renombramos y copiamos le archivo anterior para posteriormente copiar el nuevo
					$nombre_archivo = $sName.'_anterior.pdf';
					$sSourceFile = $__sPathFilesExpo.'\\'.$row->nombre_archivo;
					$sDestinationFile = $__sPathFilesExpo.'\\'.$nombre_archivo;
					if (!rename($sSourceFile, $sDestinationFile)) {
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al renombrar archivo existente ['.$sSourceFile.'] a ['.$sDestinationFile.'].'; 
						
						enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
					} else {
						echo '<br>Archivo '.$nombre_archivo.' copiado<br>';
					}
					
					$sNombreArchivo = $sFileName.'_PRE_'.$nIdDocumento.'.pdf';
					break;
				}
			} else {
				$consulta = "INSERT INTO bodega.documentos_expo (  
								 tipo
								,referencia
								,uniqueid
							 ) VALUES (
								 'PRE'
								,'".$sEntryNumber."'
								,'".$nUniqueId."'
							 )";
							 
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al guardar archivo en base de datos.'; 
					$respuesta['Error'] = ' ['.$error.']';
					
					enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
				} else {
					$nIdDocumento = mysqli_insert_id($cmysqli);
					
					$sNombreArchivo = $sFileName.'_PRE_'.$nIdDocumento.'.pdf';
				}
			}
			
			if ($respuesta['Codigo'] == 1) {
				if ($nidDocMaster == '') {
					$nidDocMaster = $nIdDocumento;
				}
				
				array_push($aIdDocuments, $nIdDocumento);
				
				/************************************/
				
				$sSourceFile = $__sPathPrefile.'\\'.$sSourceFileName;
				$sDestinationFile = $__sPathFilesExpo.'\\'.$sNombreArchivo;
				if (copy($sSourceFile, $sDestinationFile)) {
					$consulta = "UPDATE bodega.documentos_expo
								 SET nombre_archivo='".$sNombreArchivo."',
								     id_doc_master=".((count($aEntryNumber) > 1)? "'".$nidDocMaster."'": "NULL").",
								     caja='".$sCaja."',
									 factura='".$sFacturas."'
								 WHERE id_documento=".$nIdDocumento;
										 
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al editar nombre del archivo guardado.'; 
						$respuesta['Error'] = ' ['.$error.']';
						
						enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
					}
				} else {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al copiar archivo ['.$sSourceFile.'] a ['.$sDestinationFile.'].'; 
					
					enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
				}
			}
		}
	}
	
	/*Actualizamos los ids que se crearon por si falto asignar uno de documento master, 
	  en el caso de que suvieron de nuevo el mismo archivo, caso de arturo licona que agrego unas facturas y 
	  cambio el entry number*/
	$sIdNOTIN = '';
	if ($respuesta['Codigo'] == 1) { 
		if (count($aEntryNumber) > 1) {
			foreach ($aIdDocuments as &$sId) {
				if ($sIdNOTIN != '') {
					$sIdNOTIN .= ',';
				}
				
				$sIdNOTIN .= $sId;
				
				$consulta = "UPDATE bodega.documentos_expo
							 SET id_doc_master='".$nidDocMaster."'
							 WHERE id_documento=".$sId;
									 
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al editar el documento master del id ['.$sId.'] por ['.$nidDocMaster.'].'; 
					$respuesta['Error'] = ' ['.$error.']';
					
					enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
				}
			}
		}
	}
	
	/* Buscamos documentos master que se hayan quedado como basura */
	if ($respuesta['Codigo'] == 1) { 
		if (count($aEntryNumber) > 1 && $sIdNOTIN != '') {
			if ($nidDocMaster != '') {
				$consulta = "DELETE FROM bodega.documentos_expo
							 WHERE id_doc_master=".$nidDocMaster." AND 
								   id_documento NOT IN (".$sIdNOTIN.")";
								   
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al eliminar documentos basura.'.$consulta; 
					$respuesta['Error'] = ' ['.$error.']';
					
					enviamail('SALIDAEXPO: salidaExpoPrefileSync','SALIDAEXPO: '.json_encode($respuesta),array($__strEmailAlerta),array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',array());
				}
			}
		}
	}
	
	if ($respuesta['Codigo'] == 1) {
		$sSourceFile = $__sPathPrefile.'\\'.$sSourceFileName;
		@chmod( $sSourceFile, 0777 );
        @unlink( $sSourceFile );
	}
	
	return $respuesta;
}

/********************************************************************************/
/* ..:: Funciones ::.. */
/********************************************************************************/

function fcn_get_tex_pdf($sFile) {
	global $__sPathPrefile;
	
	$parser = new \Smalot\PdfParser\Parser();
	$pdf    = $parser->parseFile($__sPathPrefile.$sFile);
	
	return $pdf->getText();
}

function fcn_get_text_seccion(&$sTextResiduo, $sTextSeccion) {
	$nPosicion = strpos($sTextResiduo, $sTextSeccion) + strlen($sTextSeccion)-1;
	$sTextReturn = substr($sTextResiduo, 0, $nPosicion); 
	$sTextResiduo = substr($sTextResiduo, $nPosicion, (strlen($sTextResiduo) - 1));
	
	return $sTextReturn;
}

function fcn_get_data_regexp($sRegexp, $sText) {
	preg_match($sRegexp, $sText, $matches, PREG_OFFSET_CAPTURE);
	
	echo json_encode($matches);
	echo '<br>';
	
	return trim($matches[1][0]);
}

function fcn_get_data_regexp_entrys($sRegexp, $sText) {
	preg_match_all($sRegexp, $sText, $matches, PREG_PATTERN_ORDER);
	
	$aEntrys = array();
	foreach ($matches[1] as &$match) {
		if (!in_array($match, $aEntrys)) {
			array_push($aEntrys, $match);
		}
	}
	
	return $aEntrys;
}

function fcn_get_facturas_format($sFacturas) {
	$aFacturas = explode(",", $sFacturas);
	echo '<br>Facturas'.json_encode($aFacturas).'<br>';
	
	$sFacturas = '';
	foreach ($aFacturas as $sFact) {
		if ($sFacturas != '') {
			$sFacturas .= '|';
		}
		$sFacturas .= '^'.trim($sFact).'^';
		//echo 'Facturas'.$sFacturas;
	}
	
	return $sFacturas;
}
