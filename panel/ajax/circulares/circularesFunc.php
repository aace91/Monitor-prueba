<?php
include_once("./../../../checklogin.php");
include('./../../../url_archivos.php');
include('./../../../bower_components/PHPMailer/PHPMailerAutoload.php');

$sPathFiles = $dir_archivos_web."monitor\\circulares";
$sUrlFiles = "https://www.delbravoweb.com/archivos/monitor/circulares";
$nMaxRowPerPage = 30;

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);

if($loggedIn == false){
	$error_msg="Se ha perdido la sesion favor de iniciarla de nuevo";
	exit('500');
	//exit(json_encode(array("error" => $error_msg)));
} else {
	if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
		$action = $_REQUEST['action'];
		switch ($action) {
			case 'guadar_circular' : $respuesta = fcn_guadar_circular();
				echo json_encode($respuesta);
				break;
				
			case 'consultar_circular' : $respuesta = fcn_consultar_circular();
				echo json_encode($respuesta);
				break;

			case 'eliminar_circular' : $respuesta = fcn_eliminar_circular();
				echo json_encode($respuesta);
				break;

			case 'copiar_circular' : $respuesta = fcn_copiar_circular();
				echo json_encode($respuesta);
				break;

			case 'consultar_archivos' : $respuesta = fcn_consultar_archivos();
				echo json_encode($respuesta);
				break;
			
			case 'upload_files' : $respuesta = fcn_upload_files();
				echo json_encode($respuesta);
				break;

			case 'delete_file' : $respuesta = fcn_delete_file();
				echo json_encode($respuesta);
				break;

			case 'enviar_email' : $respuesta = fcn_enviar_email();
				echo json_encode($respuesta);
				break;
		}
	}
}

/*************************************************************************************************/
/* METODOS                                                                                       */
/*************************************************************************************************/

function fcn_guadar_circular(){
	include ('./../../../connect_dbsql.php');
	
	global $_POST, $bDebug;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['sAsunto']) && !empty($_POST['sAsunto'])) {
		$nIdCircular = $_POST['nIdCircular'];
		$sTask = $_POST['sTask'];
		$sSender = $_POST['sSender'];
		$sFromName = $_POST['sFromName'];
		$sAsunto = $_POST['sAsunto'];
		$sTipo = $_POST['sTipo'];
		$sMensaje = $_POST['sMensaje'];
		$sCorreosAdicionales = $_POST['sCorreosAdicionales'];
		$nEnviarClientesImpo = $_POST['nEnviarClientesImpo'];
		$nEnviarClientesExpo = $_POST['nEnviarClientesExpo'];
		$nEnviarClientesNB = $_POST['nEnviarClientesNB'];
		$nEnviarEjecutivosImpo = $_POST['nEnviarEjecutivosImpo'];
		$nEnviarEjecutivosExpo = $_POST['nEnviarEjecutivosExpo'];
		$nEnviarEjecutivosNB = $_POST['nEnviarEjecutivosNB'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		
		//error_log('ENTRANDO');
		$sMensaje = eliminar_comentarios_html($sMensaje);
		//error_log('SIN COMENTARIOS '.$sMensaje);

		$sHtmlCode = htmlentities(htmlspecialchars($sMensaje));
		$nTotalPaginas = 0;

		//error_log($sHtmlCode);
		
		//***********************************************************//
		
		switch ($sTask) {
			case 'nuevo':
				$consulta = "INSERT INTO bodega.circulares
							    (tipo
								,asunto
								,mensaje
								,correos_adicionales
								,enviar_clientes_impo
								,enviar_clientes_expo
								,enviar_clientes_nb
								,enviar_ejecutivos_impo
								,enviar_ejecutivos_expo
								,enviar_ejecutivos_nb
								,fecha_enviado)
							 VALUES 
							    ('".$sTipo."'
								,'".$sAsunto."'
								,'".$sHtmlCode."'
								,'".$sCorreosAdicionales."'
								,".$nEnviarClientesImpo."
								,".$nEnviarClientesExpo."
								,".$nEnviarClientesNB."
								,".$nEnviarEjecutivosImpo."
								,".$nEnviarEjecutivosExpo."
								,".$nEnviarEjecutivosNB."
								,NULL)";
				
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al insertar circular. Por favor contacte al administrador del sistema.'; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					$nIdCircular = mysqli_insert_id($cmysqli);
				}
				break;
				
			case 'editar':
			case 'editar-enviar':
				$consulta = "UPDATE bodega.circulares
							 SET tipo='".$sTipo."',
								 remitente='".$sSender."',
								 remitente_nombre='".$sFromName."',
								 asunto='".$sAsunto."',
								 mensaje='".$sHtmlCode."',
								 correos_adicionales='".$sCorreosAdicionales."',
								 enviar_clientes_impo=".$nEnviarClientesImpo.",
								 enviar_clientes_expo=".$nEnviarClientesExpo.",
								 enviar_clientes_nb=".$nEnviarClientesNB.",
								 enviar_ejecutivos_impo=".$nEnviarEjecutivosImpo.",
								 enviar_ejecutivos_expo=".$nEnviarEjecutivosExpo.",								 
								 enviar_ejecutivos_nb=".$nEnviarEjecutivosNB.",
								 fecha_enviado=IF(fecha_enviado IS NOT NULL, fecha_enviado, ".(($sTask == 'editar')? "NULL" : "'".$fecha_registro."'").")
							 WHERE id_circular=".$nIdCircular;
				
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al actualizar circular. Por favor contacte al administrador del sistema.'.$consulta; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					if ($sTask == 'editar-enviar') {
						$respPaginacion = fcn_generar_paginas_correos();
						if ($respPaginacion['Codigo'] == '1') { 
							$nTotalPaginas = count($respPaginacion['aSendEmails']);
						} else {
							$respuesta = $respPaginacion;
						}
					}
				}
				break;
		}
		
		$respuesta['sTipo']=$sTipo;
		$respuesta['nIdCircular']=$nIdCircular;
		$respuesta['sAsunto']=$sAsunto;
		$respuesta['nTotalPaginas']=$nTotalPaginas;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}	
	return $respuesta;
}

function fcn_consultar_circular(){
	include ('./../../../connect_dbsql.php');
	
	global $_POST, $bDebug;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['nIdCircular']) && !empty($_POST['nIdCircular'])) {
		$nIdCircular = $_POST['nIdCircular'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		
		$sTipo = '';
		$sSender = '';
		$sFromName = '';
		$sAsunto = '';
		$sMensaje = '';
		$sCorreosAdicionales = '';
		$nEnviarClientesImpo = '';
		$nEnviarClientesExpo = '';
		$nEnviarClientesNB = '';
		$nEnviarEjecutivosImpo = '';
		$nEnviarEjecutivosExpo = '';
		$nEnviarEjecutivosNB = '';
		$aPreview = array();
		$aPreviewConfig = array();
		$nTotalPaginas = 0;

		//***********************************************************//
		
		$consulta = "SELECT tipo, remitente, remitente_nombre, asunto, mensaje, correos_adicionales,
							enviar_clientes_impo, enviar_clientes_expo,
							enviar_clientes_nb,
							enviar_ejecutivos_impo, enviar_ejecutivos_expo,
							enviar_ejecutivos_nb,
							paginacion_array
					 FROM bodega.circulares
					 WHERE id_circular=".$nIdCircular;

		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar el circular. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			while($row = mysqli_fetch_array($query)){
				$sTipo = $row['tipo'];
				$sSender = $row['remitente'];
				$sFromName = $row['remitente_nombre'];
				$sAsunto = $row['asunto'];
				$sMensaje = html_entity_decode(htmlspecialchars_decode($row['mensaje']));
				$sCorreosAdicionales =((is_null($row['correos_adicionales']))? '' : $row['correos_adicionales']);
				$nEnviarClientesImpo = ((is_null($row['enviar_clientes_impo']))? 0 : $row['enviar_clientes_impo']);
				$nEnviarClientesExpo = ((is_null($row['enviar_clientes_expo']))? 0 : $row['enviar_clientes_expo']);
				$nEnviarClientesNB = ((is_null($row['enviar_clientes_nb']))? 0 : $row['enviar_clientes_nb']);
				$nEnviarEjecutivosImpo = ((is_null($row['enviar_ejecutivos_impo']))? 0 : $row['enviar_ejecutivos_impo']);
				$nEnviarEjecutivosExpo = ((is_null($row['enviar_ejecutivos_expo']))? 0 : $row['enviar_ejecutivos_expo']);
				$nEnviarEjecutivosNB = ((is_null($row['enviar_ejecutivos_nb']))? 0 : $row['enviar_ejecutivos_nb']);

				if (!is_null($row['paginacion_array'])) {
					$aSendEmails = json_decode($row['paginacion_array']);
					$nTotalPaginas = count($aSendEmails);
				}
				
				break;
			}
		}
		
		if ($respuesta['Codigo'] == '1') {
			$aArchivos = fcn_consultar_archivos();

			if ($aArchivos['Codigo'] == '1') {
				$aPreview = $aArchivos['aPreview'];
				$aPreviewConfig = $aArchivos['aPreviewConfig'];
			} else {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']=$aArchivos['Mensaje']; 
				$respuesta['Error'] = $aArchivos['Error'];;
			}
		}

		$respuesta['sTipo']=$sTipo;
		$respuesta['sSender']=$sSender;
		$respuesta['sFromName']=$sFromName;
		$respuesta['sAsunto']=$sAsunto;
		$respuesta['sMensaje']=$sMensaje;
		$respuesta['sCorreosAdicionales']=$sCorreosAdicionales;
		$respuesta['nEnviarClientesImpo']=$nEnviarClientesImpo;
		$respuesta['nEnviarClientesExpo']=$nEnviarClientesExpo;
		$respuesta['nEnviarClientesNB']=$nEnviarClientesNB;
		$respuesta['nEnviarEjecutivosImpo']=$nEnviarEjecutivosImpo;
		$respuesta['nEnviarEjecutivosExpo']=$nEnviarEjecutivosExpo;
		$respuesta['nEnviarEjecutivosNB']=$nEnviarEjecutivosNB;
		$respuesta['aPreview']=$aPreview;
		$respuesta['aPreviewConfig']=$aPreviewConfig;
		$respuesta['nTotalPaginas']=$nTotalPaginas;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}	
	return $respuesta;
}

function fcn_eliminar_circular(){
	include ('./../../../connect_dbsql.php');
	
	global $_POST, $bDebug;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['nIdCircular']) && !empty($_POST['nIdCircular'])) {
		$nIdCircular = $_POST['nIdCircular'];
		
		//***********************************************************//
		
		$consulta = "UPDATE bodega.circulares
					 SET fecha_eliminado=NOW()
					 WHERE id_circular=".$nIdCircular;

		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al eliminar el circular. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$respuesta['Mensaje']='Circular eliminado correctamente!!!';
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}	
	return $respuesta;
}

function fcn_copiar_circular(){
	include ('./../../../connect_dbsql.php');
	
	global $_POST, $bDebug, $sPathFiles;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['nIdCircular']) && !empty($_POST['nIdCircular'])) {
		$nIdCircular = $_POST['nIdCircular'];
		
		//***********************************************************//

		$nNewIdCircular = 0;

		//***********************************************************//
		
		$consulta = "INSERT INTO bodega.circulares
		                (tipo, asunto, mensaje, correos_adicionales, 
						 enviar_clientes_impo, enviar_clientes_expo,
						 enviar_clientes_nb,
						 enviar_ejecutivos_impo, enviar_ejecutivos_expo,
						 enviar_ejecutivos_nb,
						 fecha_insertado, fecha_enviado)
					 SELECT tipo, asunto, mensaje, correos_adicionales, 
						    enviar_clientes_impo, enviar_clientes_expo,
							enviar_clientes_nb,
						    enviar_ejecutivos_impo, enviar_ejecutivos_expo,
							enviar_ejecutivos_nb,
							NOW(), NULL
					 FROM bodega.circulares
					 WHERE id_circular=".$nIdCircular;

		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al copiar el circular. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$nNewIdCircular = mysqli_insert_id($cmysqli);
		}

		if ($respuesta['Codigo'] == '1') {
			$consulta = "INSERT INTO bodega.circulares_docs
						    (id_circular, nombre, fecha)
						 SELECT '".$nNewIdCircular."', nombre, NOW()
						 FROM bodega.circulares_docs
						 WHERE id_circular=".$nIdCircular;

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al copiar documentos de circular. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}
		}

		if ($respuesta['Codigo'] == '1') {
			$src = $sPathFiles . DIRECTORY_SEPARATOR . $nIdCircular;
			$dst = $sPathFiles . DIRECTORY_SEPARATOR . $nNewIdCircular;

			fcn_recurse_copy($src, $dst);
		}

		$respuesta['nIdCircular']=$nNewIdCircular;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}	
	return $respuesta;
}

function fcn_consultar_archivos(){
	include ('./../../../connect_dbsql.php');
	
	global $_POST, $bDebug, $sPathFiles, $sUrlFiles;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['nIdCircular']) && !empty($_POST['nIdCircular'])) {
		$nIdCircular = $_POST['nIdCircular'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		
		$aPreview = array();
		$aPreviewConfig = array();
		
		//***********************************************************//
		
		$consulta = "SELECT id_registro, nombre
					 FROM bodega.circulares_docs
					 WHERE id_circular=".$nIdCircular;

		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los archivos adjuntos. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			while ($row = mysqli_fetch_array($query)){
				$sFilePath = $sPathFiles. '\\' .$nIdCircular. '\\' .$row["nombre"];
				
				$sPreview = $sUrlFiles. '/' .$nIdCircular. '/' .$row["nombre"];
				$sPreviewConfig = array(
									'type' => get_file_type($sFilePath),
									'caption' => $row["nombre"],
									'size' => filesize($sFilePath),
									'width' => '120px',
									'url' => 'ajax/circulares/circularesFunc.php?action=delete_file',
									'key' => $row["id_registro"] . '^' . $nIdCircular . '^' . $row["nombre"],
									'showDrag' => false,
									'url_download' => $sUrlFiles . '/' . $nIdCircular . '/' . $row["nombre"],
									'nombre_archivo' => $row["nombre"]
								);
				
				array_push($aPreview, $sPreview);
				array_push($aPreviewConfig, $sPreviewConfig);
			}
		}
		
		$respuesta['aPreview']=$aPreview;
		$respuesta['aPreviewConfig']=$aPreviewConfig;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}	
	return $respuesta;
}

function fcn_upload_files(){
	include ('./../../../connect_dbsql.php');
	
	global $_POST, $bDebug, $sPathFiles, $sUrlFiles;
	
	$respuesta['Codigo']=1;
	if (isset($_FILES['ifile_documentos']) && !empty($_FILES['ifile_documentos'])) { 
		$nIdCircular = $_POST['nIdCircular'];
		$oFiles = $_FILES['ifile_documentos'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		$oFileNames = $oFiles['name'];
		
		$aPreview = array();
		$aPreviewConfig = array();

		//***********************************************************//
		
		$sCarpeta = $sPathFiles . DIRECTORY_SEPARATOR . $nIdCircular;
		if (!file_exists($sCarpeta)) {
			mkdir($sCarpeta, 0777, true);
		}

		for($i=0; $i < count($oFileNames); $i++){
			$aFile = explode('.', basename($oFileNames[$i]));
			$sExt = array_pop($aFile);
			$aFile = array_reverse($aFile);
			$sFileName = array_pop($aFile);

			$sTarget = $sCarpeta . DIRECTORY_SEPARATOR . $sFileName . '.' . $sExt;
			$nConsecutivo = 0;	
			$sTempFileName = $sFileName;
			do {
				$nConsecutivo += 1;
				if (file_exists($sTarget)) {
					$sTempFileName =  $sFileName . '_(' . $nConsecutivo . ')';
					$sTarget = $sCarpeta . DIRECTORY_SEPARATOR . $sTempFileName . $sExt;
				} else {
					break;
				}
			} while (true);
			$sFileName = $sTempFileName;

			$respuesta['Target']=$sTarget;
			if(move_uploaded_file($oFiles['tmp_name'][$i], $sTarget)) {
				$consulta = "INSERT INTO bodega.circulares_docs
								(id_circular
								,nombre)
							VALUES 
								(".$nIdCircular."
								,'".$sFileName . '.' . $sExt."')";

				$query = mysqli_query($cmysqli, $consulta);		
				if ($query==false){
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje'] = 'Error en insertar documentos'.$consulta;
					$respuesta['Error']=' ['.$error.']';
					
					break;
				}
			} else {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='No se cargo el documento ['.$sFileName . '.' . $sExt.'], contacte al administrador';
				
				$success = false;
				break;
			}
		}

		if ($respuesta['Codigo'] == '1') {
			$aArchivos = fcn_consultar_archivos();

			if ($aArchivos['Codigo'] == '1') {
				$aPreview = $aArchivos['aPreview'];
				$aPreviewConfig = $aArchivos['aPreviewConfig'];
			} else {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']=$aArchivos['Mensaje']; 
				$respuesta['Error'] = $aArchivos['Error'];;
			}
		}

		$respuesta['aPreview']=$aPreview;
		$respuesta['aPreviewConfig']=$aPreviewConfig;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}	
	return $respuesta;
}

function fcn_delete_file(){
	include ('./../../../connect_dbsql.php');
	
	global $_POST, $bDebug, $sPathFiles;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['key']) && !empty($_POST['key'])) { 
		$sKey = $_POST['key'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");

		$aDelete = explode("^", $sKey);
		$nIdRegistro = $aDelete[0];
		$sFolder = $aDelete[1];
		$sNombreArchivo = $aDelete[2];

		$_POST['nIdCircular'] = $sFolder;
		
		$aPreview = array();
		$aPreviewConfig = array();
		
		//***********************************************************//

		$consulta="DELETE FROM bodega.circulares_docs
				   WHERE id_registro=".$nIdRegistro;
							
		$query = mysqli_query($cmysqli, $consulta);		
		if ($query==false){
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje'] = 'Error al eliminar archivo'.$consulta;
			$respuesta['Error']=' ['.$error.']';
		} else {
			$sFilePath = $sPathFiles. '\\' .$sFolder. '\\' .$sNombreArchivo;
			if (file_exists($sFilePath)) {
				unlink($sFilePath);
			}
		}

		if ($respuesta['Codigo'] == '1') {
			$aArchivos = fcn_consultar_archivos();

			if ($aArchivos['Codigo'] == '1') {
				$aPreview = $aArchivos['aPreview'];
				$aPreviewConfig = $aArchivos['aPreviewConfig'];
			} else {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']=$aArchivos['Mensaje']; 
				$respuesta['Error'] = $aArchivos['Error'];;
			}
		}
			
		$respuesta['Key']=$sKey;
		$respuesta['aPreview']=$aPreview;
		$respuesta['aPreviewConfig']=$aPreviewConfig;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}	
	return $respuesta;
}

function fcn_enviar_email(){
	include ('./../../../connect_dbsql.php');
	
	global $_POST, $bDebug, $sPathFiles;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['nIdCircular']) && !empty($_POST['nIdCircular'])) {
		$nIdCircular = $_POST['nIdCircular'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");

		$sSender = '';
		$sFromName = '';
		$sAsunto = '';
		$sMensaje = '';
		$aSendEmails = array();
		$nTotalPaginas = 0;

		//***********************************************************//
		
		$consulta = "SELECT remitente, remitente_nombre, asunto, mensaje, paginacion_array
					 FROM bodega.circulares
					 WHERE id_circular=".$nIdCircular;

		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar el mensaje del circular. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			while($row = mysqli_fetch_array($query)){
				$sSender = $row['remitente'];
				$sFromName = $row['remitente_nombre'];
				$sAsunto = $row['asunto'];
				$sMensaje = html_entity_decode(htmlspecialchars_decode($row['mensaje']));
				$aSendEmails = json_decode($row['paginacion_array']);

				break;
			}
		}
		
		if ($respuesta['Codigo'] == '1') {
			if (count($aSendEmails) > 0) {
				$aTo = array();
				$aBcc = array_shift($aSendEmails);
				$aAdjuntos=array();

				/********************************/
				//Guardamos en el log
				
				$consulta = "INSERT INTO bodega.circulares_log
							    (id_circular
								,correos)
							 VALUES 
							    ('".$nIdCircular."'
								,'".json_encode($aBcc)."')";
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al guardar en el log de sucesos. Por favor contacte al administrador del sistema.'; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					/********************************/
				
					if ($bDebug) {
						error_log('Se borra aBcc');
						$aBcc = array();
						array_push($aBcc,'jcdelacruz@delbravo.com');
					}
					
					/********************************/

					$sPath = $sPathFiles. '\\' .$nIdCircular;
					if(file_exists($sPath)){
						$fileSystemIterator = new FilesystemIterator($sPath); 
						foreach ($fileSystemIterator as $file) {
							$sTarget = $sPath . DIRECTORY_SEPARATOR . $file->getFilename();
							array_push($aAdjuntos, array('dir' => $sTarget, 'name' => $file->getFilename()));
						}
					}
					/********************************/

					$sHTML = '
						<!DOCTYPE html>
						<html>
							<head>
								<meta http-equiv="Content-Type" content="text/html; charset=utf8_encode" />
								<title>delbravo</title>
							</head>
				
							<body>'.$sMensaje.'</body>
						</html>';

					$bEnviado = enviamail($sAsunto, $sHTML, $aTo, $aBcc, $aAdjuntos, $sSender, $sFromName);
					if ($bEnviado) {
						$consulta = "UPDATE bodega.circulares
									 SET paginacion_array=".((count($aSendEmails)> 0)? "'".json_encode($aSendEmails)."'" : "NULL")."
									 WHERE id_circular=".$nIdCircular;
				
						$query = mysqli_query($cmysqli, $consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al actualizar la lista de correos del circular. Por favor contacte al administrador del sistema.'; 
							$respuesta['Error'] = ' ['.$error.']';
						} else {
							$nTotalPaginas = count($aSendEmails);		
						}
					}
				}
			}
		}

		$respuesta['nTotalPaginas']=$nTotalPaginas;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}	
	return $respuesta;
}

/*************************************************************************************************/
/* FUNCIONES                                                                                     */
/*************************************************************************************************/

function get_file_type($sPath) {
	$ext = explode('.', basename($sPath));
	
	if (preg_match("/\.(gif|png|jpe?g)$/i", $sPath)) {
		return 'image';
	} else if(preg_match("/\.(pdf)$/i", $sPath)) {
		return 'pdf';
	} else if(preg_match("/\.(htm|html)$/i", $sPath)) {
		return 'html';
	} else if(preg_match("/\.(xml|javascript)$/i", $sPath)) {
		return 'text';
	} else {
		return 'other';
	}
}

/* Copiar Folder a otro Folder */
function fcn_recurse_copy($src, $dst) {
	if (!file_exists($dst)) {
		mkdir($dst, 0777, true);
	}

	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				fcn_recurse_copy($src . '/' . $file,$dst . '/' . $file);
			} else {
				copy($src . '/' . $file,$dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}

/* Obtenemos los correos */ 
function fcn_generar_paginas_correos() {
	include ('./../../../connect_dbsql.php');

	global $_POST, $bDebug, $nMaxRowPerPage;

	$respuesta['Codigo']=1;

	//***********************************************************//

	$nIdCircular = $_POST['nIdCircular'];
	$sCorreosAdicionales = $_POST['sCorreosAdicionales'];
	$nEnviarClientesImpo = $_POST['nEnviarClientesImpo'];
	$nEnviarClientesExpo = $_POST['nEnviarClientesExpo'];
	$nEnviarClientesNB = $_POST['nEnviarClientesNB'];
	$nEnviarEjecutivosImpo = $_POST['nEnviarEjecutivosImpo'];
	$nEnviarEjecutivosExpo = $_POST['nEnviarEjecutivosExpo'];
	$nEnviarEjecutivosNB = $_POST['nEnviarEjecutivosNB'];

	$aSendEmails = array();
	$aCorreosErroneos = array();
	
	//***********************************************************//

	$sFrom = "";
	
	/* ..:: Correos adicionales ::.. */
	if ($sCorreosAdicionales != '') {
		$aCorreosAdicionales = explode(';', $sCorreosAdicionales);
		foreach($aCorreosAdicionales as $email){ 
			$sFrom .= (($sFrom == '')? '' : ' UNION ALL ');
			$sFrom .= "SELECT '".trim($email)."' AS correos, 'NA' AS cliente_id, 'Manual' AS nombre, 'NA' AS tabla";
		}
		/*$aPagina = array();
		foreach($aCorreosAdicionales as $email){
			array_push($aPagina, trim($email));
		}
	
		if (count($aPagina) > 0) {
			array_push($aSendEmails, $aPagina);
		}*/
	}
	
	//***********************************************************//
	
	/* ..:: Clientes ::.. */	
	if ($nEnviarClientesImpo == '1') {
		$sFrom .= (($sFrom == '')? '' : ' UNION ALL ');

		$sFrom .= "SELECT to1 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT to2 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT to3 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT to4 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT to5 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT to6 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT to7 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT to8 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT to9 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT to10 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes ";
	}

	if ($nEnviarClientesExpo == '1') {
		$sFrom .= (($sFrom == '')? '' : ' UNION ALL ');

		$sFrom .= "SELECT email AS correos, id_catalogo AS f_numcli, 'NA' AS nombre, 'bodega.contactos_expo.Clientes' AS tabla FROM bodega.contactos_expo WHERE tipo_catalogo='CLI' AND tipo_contacto='CLI' ";
	}

	if ($nEnviarClientesNB == '1') {
		$sFrom .= (($sFrom == '')? '' : ' UNION ALL ');

		$sFrom .= "SELECT to1 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT to2 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT to3 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT to4 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT to5 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT to6 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT to7 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT to8 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT to9 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT to10 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla FROM bodega.geocel_clientes_nb ";
	}

	/* ..:: Ejecutivos ::.. */	
	if ($nEnviarEjecutivosImpo == '1') {
		$sFrom .= (($sFrom == '')? '' : ' UNION ALL ');

		$sFrom .= "SELECT cc1 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT cc2 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT cc3 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT cc4 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT cc5 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT cc6 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT cc7 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT cc8 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT cc9 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes UNION ALL
				   SELECT cc10 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes' AS tabla FROM bodega.geocel_clientes ";
	}

	if ($nEnviarEjecutivosExpo == '1') {
		$sFrom .= (($sFrom == '')? '' : ' UNION ALL ');

		$sFrom .= "SELECT email AS correos, id_catalogo AS f_numcli, 'NA' AS nombre, 'bodega.contactos_expo.Ejecutivos' AS tabla FROM bodega.contactos_expo WHERE tipo_catalogo='CLI' AND tipo_contacto='EJE' ";
	}

	if ($nEnviarEjecutivosNB == '1') {
		$sFrom .= (($sFrom == '')? '' : ' UNION ALL ');

		$sFrom .= "SELECT cc1 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla  FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT cc2 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla  FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT cc3 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla  FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT cc4 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla  FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT cc5 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla  FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT cc6 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla  FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT cc7 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla  FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT cc8 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla  FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT cc9 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla  FROM bodega.geocel_clientes_nb UNION ALL
				   SELECT cc10 AS correos, f_numcli AS cliente_id, nombre, 'bodega.geocel_clientes_nb' AS tabla  FROM bodega.geocel_clientes_nb ";
	}

	if ($sFrom != '') {
		$consulta = "SELECT DISTINCT correos, cliente_id, nombre, tabla
					 FROM (".$sFrom.") AS tblCorreos
					 WHERE correos IS NOT NULL AND correos <> ''";
		
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar la lista de correos. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$nRowCount = 0;
			$aPagina = array();
		
			while($row = mysqli_fetch_array($query)){
				$aCorreos = explode(',', trim($row['correos']));
				
				foreach($aCorreos as $email){ 
					$email = trim($email);
				    if ($email != '') {
						if (is_valid_email($email)) {
							if (!in_array_r($email, $aSendEmails)) {
								if (!in_array_r($email, $aPagina)) {
									$nRowCount += 1;
									array_push($aPagina, $email);

									if ($nRowCount >= $nMaxRowPerPage) {
										array_push($aSendEmails, $aPagina);

										$nRowCount = 0;
										$aPagina = array();
									}
								}
							}
						} else {
							array_push($aCorreosErroneos, array('email' => $email,
							                                    'id_cliente' => $row['cliente_id'], 
																'nombre' => $row['nombre'],
																'tabla' => $row['tabla']));
						}
					}
				}
			}

			if ($nRowCount > 0) {
				array_push($aSendEmails, $aPagina);
			}
		}
	}

	if ($respuesta['Codigo'] == '1') {
		$consulta = "UPDATE bodega.circulares
				     SET paginacion_array='".json_encode($aSendEmails)."'
					 WHERE id_circular=".$nIdCircular;

		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al actualizar la lista de correos del circular. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		}
	}
	
	if ($respuesta['Codigo'] == '1') {
		if (count($aCorreosErroneos) > 0) {
			$bEnviado = enviamail('Emails incorrectos en el proceso de Circulares', 'Emails incorrectos en el proceso de Circulares <br> '.json_encode($aCorreosErroneos), array('errores.sistemas@delbravo.com'), array(), array(), 'errores.sistemas@delbravo.com', 'Avisos Sistemas');
		}
	}

	$respuesta['aSendEmails']=$aSendEmails;
	return $respuesta;
}

/* Elimina los comentarios del HTML */
function eliminar_comentarios_html($html = '') {
	try {
		do {
			$nPosInicial = strpos($html, '<!--');
			
			if ($nPosInicial !== false) {
				$nPosFinal = strpos($html, '-->');
				
				$sSubstring = substr($html, $nPosInicial, $nPosFinal + 3);
				$html = substr_replace($html,"", $nPosInicial, ($nPosFinal - $nPosInicial) + 3);
			} else {
				break;
			}
		} while (true);
	
		return preg_replace('/<!--(.|\s)*?-->/', '', $html);
	} catch(Exception $e) {
		error_log($e->getMessage());
	}
}

function is_valid_email($str) {
  return (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
}

function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

/**********************************************************************************/
/* NOTIFICACIONES EMAIL                                                           */
/**********************************************************************************/

function enviamail($asunto, $mensaje, $to, $bcc, $adjuntos, $sender, $fromname){
	global $sReportName;
	
	$mailserver = 'mail.delbravo.com';
	$portmailserver = '587';
	
	$mail = new PHPMailer();
	//Luego tenemos que iniciar la validación por SMTP:
	$mail->IsSMTP();
	$mail->SMTPAuth = false;
	//$mail->SMTPSecure = "tls";
	$mail->Host = $mailserver; // SMTP a utilizar. Por ej. smtp.elserver.com
	$mail->Username = $sender; // Correo completo a utilizar
	$mail->Port = $portmailserver; // Puerto a utilizar
	//Con estas pocas líneas iniciamos una conexión con el SMTP. Lo que ahora deberíamos hacer, es configurar el mensaje a enviar, el //From, etc.
	$mail->From = $sender; // Desde donde enviamos (Para mostrar)
	$mail->FromName = $fromname;
	$mail->CharSet = 'UTF-8';

	for($x=0;$x<count($adjuntos);$x++){
		$mail->AddAttachment($adjuntos[$x]['dir'],$adjuntos[$x]['name']);
	}

	//Estas dos líneas, cumplirían la función de encabezado (En mail() usado de esta forma: “From: Nombre <correo@dominio.com>”) de //correo.
	if (count($to)>0){
		foreach($to as $t){
			// Esta es la dirección a donde enviamos
			$mail->AddAddress($t);
		}
	}
	
	if (count($bcc)>0){
		foreach($bcc as $b){
			// Esta es la dirección a donde enviamos
			$mail->AddBcc($b);
		}
	}
	
	$mail->IsHTML(true); // El correo se envía como HTML
	$mail->Subject = $asunto; // Este es el titulo del email.
	$mail->Body = $mensaje; // Mensaje a enviar
	$exito = $mail->Send(); // Envía el correo.

	//También podríamos agregar simples verificaciones para saber si se envió:
	if(!$exito){
		error_log($sReportName.' :: Error al enviar el correo electronico. ['.$mail->ErrorInfo.']');
	}
	return true;
}