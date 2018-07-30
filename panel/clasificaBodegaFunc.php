<?php
include_once('./../checklogin.php');
include('./../connect_dbsql.php');
include('./../url_archivos.php');

require dirname(__DIR__) . '\vendor\autoload.php';
use PHPJasper\PHPJasper;

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'pruebas/') !== false)? true : false);

if($loggedIn == false){
	$Mensaje= "<a href='login.php'>Su sesión expiro favor de ingresar nuevamente</a>";
	$response['Codigo'] = -1;
    $response['Mensaje'] = $Mensaje;
    echo json_encode($response);
}
if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
		/*case 'buscatipo' : $respuesta = buscatipo((isset($_POST['q']) ? $_POST['q'] : ""));
			echo json_encode($respuesta);
			break;
			
		case 'guardartipo' : $respuesta = guardartipo(strtoupper($_POST['nombre_tipo']));
			echo json_encode($respuesta);
			break;*/

		case 'subir_documento' : $respuesta = fcn_subir_documento();
			echo json_encode($respuesta);
			break;

		case 'consultar_documentos' : $respuesta = fcn_consultar_documentos();
			echo json_encode($respuesta);
			break;
		
		case 'eliminar_documento' : $respuesta = fcn_eliminar_documento();
			echo json_encode($respuesta);
			break;
			
		case 'activar_principal' : $respuesta = fcn_activar_principal();
			echo json_encode($respuesta);
			break;
			
		case 'procesar_excel_layout' : $respuesta = fcn_procesar_excel_layout();
			echo json_encode($respuesta);
			break;

		case 'guardar_layout' : $respuesta = fcn_guardar_layout();
			echo json_encode($respuesta);
			break;

		case 'generar_ficha' : $respuesta = fcn_generar_ficha_clasificacion();
			echo json_encode($respuesta);
			break;
	}
}else{
	echo 'No se ha definido el nombre de la accion ';
}

/*************************************************************************************************/
/* METODOS                                                                                       */
/*************************************************************************************************/

/*function buscatipo($buscar){
	include('../connect_dbsql.php');
	if(!mysqli_get_connection_stats($cmysqli)){
		$response['Codigo']=-1;
		$response['Mensaje']='Conexion muerta' ;
		return $response;
	}
	$response['items']=array();
	if ($buscar!=''){
		$consulta="SELECT id_equipo,tipo_equipo from tipoequipo where tipo_equipo like '%$buscar%' limit 10";
		$query = $cmysqli->query($consulta);
		if (!$query) {
			$error=$cmysqli->error;
			$response['Codigo']=-1;
			$response['Mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
			return $response;
		}
		while($row = $query->fetch_object()){
			$id=$row->tipo_equipo;
			$nombre=$row->tipo_equipo;
			array_push($response['items'],array('id'=>$id,'text'=>$nombre));
		}
	}
	mysqli_close($cmysqli);
	return $response;
}*/

/*function guardartipo($nombre_tipo){
	include ('../connect_dbsql.php');
	$mdbFilename =$rutaequipomdb;
	$conn_access = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=$mdbFilename", '', '');
	if ($conn_access==false){
		$response['Codigo']=-1;
		$response['Mensaje']="Error al conectarse a la base de datos equipo.mdb".$ruta;
		return($response);
	}
	if(trim($nombre_tipo) ==''){
		$response['Codigo']=-1;
		$response['Mensaje']='El nombre del tipo no puede estar vació ';
		return($response);
	}
	$consultaa="INSERT INTO tipoequipo (tipo_equipo) values('$nombre_tipo')";
	$result = odbc_exec ($conn_access, $consultaa);
	if (odbc_num_rows($result)==-1){
		$response['Codigo']=-1;
		$response['Mensaje']="Error en consulta, error:".odbc_errormsg ($conn_access).", ".$consultaa;
		return($response);
	}
	odbc_close($conn_access);
	$consultaa="SELECT max(id_equipo) as clavem from tipoequipo";
	$result = odbc_exec ($conn_access, $consultaa);
	if ($result==false){
		$response['Codigo']=-1;
		$response['Mensaje']="Error en consulta, error:".odbc_errormsg ($conn_access).", ".$consultaa;
		return($response);
	}
	while ($fila = odbc_fetch_object($result)){
        $id=$fila->clavem;
    }
	$consultam="INSERT INTO tipoequipo (id_equipo,tipo_equipo) values($id,'$nombre_tipo')";
	$query = mysqli_query($cmysqli,$consultam);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['Codigo']=-1;
		$response['Mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		return $response;
	}
	$response['Codigo']=1;
	$response['Mensaje']='El tipo se guardo con exito';
	return $response;
	mysqli_close($cmysqli);
}*/

function fcn_consultar_documentos(){
	global $_POST, $bDebug, $cmysqli, $dir_archivos_web, $URL_archivos_web;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_POST['nIdClasificacion']) && !empty($_POST['nIdClasificacion'])) {
			$nIdClasificacion = $_POST['nIdClasificacion'];
				
			//***********************************************************//
			
			$aPreview = array();
			$aPreviewConfig = array();
			$aPreviewThumbTags = array();
			$sFolder = $dir_archivos_web . '\\monitor\\clasificaBodega\\' . $nIdClasificacion;

			//***********************************************************//	

			$consulta = "SELECT id_registro, nombre, principal
						 FROM bodega.clasificaciones_docs
						 WHERE id_clasificacion=".$nIdClasificacion;

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar los documentos. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else { 
				while ($row = mysqli_fetch_array($query)){
					$sFilePath = $dir_archivos_web . 'monitor\\clasificaBodega\\' . $nIdClasificacion . '\\' .$row["nombre"];
					$sType = get_file_type($sFilePath);
					
					$sPreview = $URL_archivos_web. 'monitor/clasificaBodega/' .$nIdClasificacion. '/' .$row["nombre"];
					$oPreviewConfig = array(
										'type' => $sType,
										'caption' => $row["nombre"],
										'size' => filesize($sFilePath),
										'width' => '120px',
										'url' => 'clasificaBodegaFunc.php?action=eliminar_documento',
										'key' => $row["id_registro"] . '^' . $nIdClasificacion . '^' . $row["nombre"],
										'showDrag' => false,
										'url_download' => $URL_archivos_web . '/monitor/clasificaBodega/' . $nIdClasificacion . '/' . $row["nombre"],
										'nombre_archivo' => $row["nombre"]
									);
					$oPreviewThumbTags = array(
						'{TAG_VALUE}' => (($row["principal"] == 1)? '<span class="label label-primary">Principal</span>' : ''),
						'{TAG_BTN_PRINCIPAL}' => (($sType == 'image' && $row["principal"] == 0)? '<button type="button" class="btn btn-xs btn-default" title="Activar como principal" onclick="fcn_activar_principal('.$row["id_registro"].');"><i class="glyphicon glyphicon-thumbs-up text-success"></i></button>' : '')
					);
					
					array_push($aPreview, $sPreview);
					array_push($aPreviewConfig, $oPreviewConfig);
					array_push($aPreviewThumbTags, $oPreviewThumbTags);
				}
			}		
			
			$respuesta['aPreview']=$aPreview;
			$respuesta['aPreviewConfig']=$aPreviewConfig;
			$respuesta['aPreviewThumbTags']=$aPreviewThumbTags;
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_consultar_documentos().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

function fcn_subir_documento(){
	global $_POST, $_FILES, $bDebug, $cmysqli, $dir_archivos_web, $URL_archivos_web;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_POST['nIdClasificacion']) && !empty($_POST['nIdClasificacion'])) {
			$nIdClasificacion = $_POST['nIdClasificacion'];
			$oFiles = $_FILES['ifile_documentos'];		
			
			//***********************************************************//
			
			$oFileNames = $oFiles['name'];
			$carpeta = $dir_archivos_web . '\\monitor\\clasificaBodega\\' . $nIdClasificacion;
			if (!file_exists($carpeta)) {
				mkdir($carpeta, 0777, true);
			}
			
			//***********************************************************//

			for($i=0; $i < count($oFileNames); $i++){
				$ext = explode('.', basename($oFileNames[$i]));
				
				$sFileName = md5(uniqid()). "." . array_pop($ext);
				$target = $carpeta . DIRECTORY_SEPARATOR . $sFileName;
				if(move_uploaded_file($oFiles['tmp_name'][$i], $target)) {
					$consulta = "INSERT INTO bodega.clasificaciones_docs (id_clasificacion, nombre)
								 VALUES (".$nIdClasificacion.", '".$sFileName."')";
								 
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
					$respuesta['Mensaje']='No se pudo copiar el documento';
					break;
				}
			}

			$respuesta = fcn_consultar_documentos();
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_subir_documento().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

function fcn_eliminar_documento(){
	global $_POST, $bDebug, $cmysqli, $dir_archivos_web, $URL_archivos_web;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_POST['key']) && !empty($_POST['key'])) { 
			$sKey = $_POST['key'];

			//***********************************************************//
			
			$aDelete = explode("^", $sKey);
			$nIdRegistro = $aDelete[0];
			$sFolder = $aDelete[1];
			$sNombreArchivo = $aDelete[2];
			
			$consulta="DELETE FROM bodega.clasificaciones_docs
					   WHERE id_registro=".$nIdRegistro;
								
			$query = mysqli_query($cmysqli, $consulta);		
			if ($query==false){
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje'] = 'Error al eliminar archivo'.$consulta;
				$respuesta['Error']=' ['.$error.']';
			} else {
				$sFilePath = $dir_archivos_web . 'monitor\\clasificaBodega\\' . $sFolder . '\\' .$sNombreArchivo;
				unlink($sFilePath);
			}
			
			$respuesta['Key']=$sKey;
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_eliminar_documento().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

function fcn_activar_principal(){
	global $_POST, $bDebug, $cmysqli;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_POST['nIdClasificacion']) && !empty($_POST['nIdClasificacion'])) {
			$nIdClasificacion = $_POST['nIdClasificacion'];
			$nIdRegistro = $_POST['nIdRegistro'];
			
			//***********************************************************//

			$consulta = "UPDATE bodega.clasificaciones_docs
						 SET principal=NULL
						 WHERE id_clasificacion=".$nIdClasificacion;
						
			$query = mysqli_query($cmysqli, $consulta);		
			if ($query==false){
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje'] = 'Error al activar como principal la imagen seleccionada'.$consulta;
				$respuesta['Error']=' ['.$error.']';
			}

			if ($respuesta['Codigo'] == 1) {
				$consulta = "UPDATE bodega.clasificaciones_docs
							 SET principal=1
							 WHERE id_registro=".$nIdRegistro;
							
				$query = mysqli_query($cmysqli, $consulta);		
				if ($query==false){
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje'] = 'Error al activar como principal la imagen seleccionada';
					$respuesta['Error']=' ['.$error.']';
				}
			}

			if ($respuesta['Codigo'] == 1) { 
				$respuesta = fcn_consultar_documentos();
			}
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_activar_principal().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

function fcn_procesar_excel_layout(){
	global $_POST, $_FILES, $bDebug, $cmysqli;
	
	include('./../bower_components/PHPExcel/Classes/PHPExcel/IOFactory.php');
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_FILES) && !empty($_FILES)) {
			$respuesta['HTML']='<div class="row"><div class="col-md-12"><table class="table table-bordered table-striped table-condensed cf" width="100%">';
			$objPHPExcel = PHPExcel_IOFactory::load($_FILES["xlsLayout"]["tmp_name"]);
			foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
				$highestRow = $worksheet->getHighestRow();
				$respuesta['HTML'].='<thead class="cf bg-blue">
										<tr>
											<th>NUM_PARTE (Alfanum&eacute;rico 25)</th>
											<th>FRACCI&Oacute;N (Num&eacute;rico 8)</th>
											<th>FRACCI&Oacute;N_10 (Num&eacute;rico 10)</th>
											<th>DESCRIPCI&Oacute;N (Alfanum&eacute;rico 200)</th>
											<th>UNIDAD_MEDIDA (Alfanum&eacute;rico 20)</th>
											<th>FUNDAMENTO_LEGAL (Alfanum&eacute;rico 500)</th>
										</tr>
									</thead>';

				$Clasificaciones = array();
				for ($row=2; $row <= $highestRow; $row++) {					
					$errNumParte = false;$errFraccion = false;$errFraccion10 = false;
					$errDescripcion = false;$errUM = false; $errFundamento = false;
					
					$NUM_PARTE = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
					$FRACCION = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
					$FRACCION10 = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
					$DESCRIPCION = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					$UM = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
					$FUNDAMENTO = $worksheet->getCellByColumnAndRow(5, $row)->getValue();	
					//Numero Parte
					if (!isset($NUM_PARTE) || empty($NUM_PARTE)) {
						$errNumParte = true;$respuesta['Codigo'] = '2';
					}else{ 
						if (strlen(trim($NUM_PARTE)) > 25){$errNumParte = true;$respuesta['Codigo'] = '2';} 
					}
					//Fraccion
					if (!isset($FRACCION) || empty($FRACCION)) {
						$errFraccion = true;$respuesta['Codigo'] = '2';
					}else{ 
						if (strlen(trim($FRACCION)) != 8){$errFraccion = true;$respuesta['Codigo'] = '2';} 
					}
					//Fraccion10
					if (strlen(trim($FRACCION10)) > 10){$errFraccion10 = true;$respuesta['Codigo'] = '2';}
					//Descripcion
					if (!isset($DESCRIPCION) || empty($DESCRIPCION)) {
						$errDescripcion = true; $respuesta['Codigo'] = '2';
					}else{ 
						if (strlen(trim($DESCRIPCION)) > 200){$errDescripcion = true; $respuesta['Codigo'] = '2';} 
					}
					//UM
					if (!isset($UM) || empty($UM)) {
						$UM = 'PZAS';
					}else{ 
						if (strlen(trim($UM)) > 20){
							$errUM = true;$respuesta['Codigo'] = '2';
						}
					}
					//Fundamento Legal
					if (strlen(trim($FUNDAMENTO)) > 500){$errFundamento = true;$respuesta['Codigo'] = '2';}
	
					if($respuesta['Codigo'] == '1'){
						$RowCaja = array($NUM_PARTE,$FRACCION,$FRACCION10,$DESCRIPCION,$UM, $FUNDAMENTO);
						array_push($Clasificaciones,$RowCaja);
						$_SESSION['aClasificaciones'] = $Clasificaciones;
					}else{
						$_SESSION['aClasificaciones'] = array();
					}					
					$respuesta['HTML'].="<tr>";
					$respuesta['HTML'] .= '<td'.($errNumParte?'class="alert alert-danger"':'').'>'.$NUM_PARTE.'</td>';
					$respuesta['HTML'] .= '<td '.($errFraccion?'class="alert alert-danger"':'').'>'.$FRACCION.'</td>';
					$respuesta['HTML'] .= '<td '.($errFraccion10?'class="alert alert-danger"':'').'>'.$FRACCION10.'</td>';
					$respuesta['HTML'] .= '<td '.($errDescripcion?'class="alert alert-danger"':'').'>'.$DESCRIPCION.'</td>';
					$respuesta['HTML'] .= '<td '.($errUM?'class="alert alert-danger"':'').'>'.$UM.'</td>';
					$respuesta['HTML'] .= '<td '.($errFundamento?'class="alert alert-danger"':'').'>'.$FUNDAMENTO.'</td>';
					$respuesta['HTML'] .= "</tr>";
				}
				break;
			}
			$respuesta['HTML'] .= '</table></div></div>';
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']=(534-1);
			$respuesta['Error']= '';
		}
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_activar_principal().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

function fcn_guardar_layout(){
	global $_POST, $bDebug, $cmysqli;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_POST['id_cliente']) && !empty($_POST['id_cliente'])) {
			$id_cliente = $_POST['id_cliente'];
			$id_proveedor = $_POST['id_proveedor'];
			$aClasificaciones = $_SESSION['aClasificaciones'];

			//***********************************************************//

			if(count($aClasificaciones) > 0){
				for($i = 0; $i < count($aClasificaciones); $i++){		
					$NUM_PARTE = $aClasificaciones[$i][0];
					$FRACCION = $aClasificaciones[$i][1];
					$FRACCION10 = $aClasificaciones[$i][2];
					$DESCRIPCION = $aClasificaciones[$i][3];
					$UM = $aClasificaciones[$i][4];
					$FUNDAMENTO = $aClasificaciones[$i][5];
					
					$consulta = "SELECT id 
								 FROM clasificaciones
								 WHERE noparte='".$NUM_PARTE."' AND 
									   cliente_id=".$id_cliente." AND 
									   proveedor_id=".$id_proveedor;
											
					$respuesta = web_service_query($consulta,'SELECT');
					if($respuesta['Codigo'] != 1){ break; }
					$aRows = json_decode($respuesta['Adicional1'], true);	
					if(count($aRows) == 0){
						$fechan = new DateTime();
						$fecha=$fechan->format("m/d/Y");
						$fecham=$fechan->format("Y-m-d 00:00:00");
						$hora=$fechan->format("g:i:s A");
						//INSERTAR ACCESS
						$consultaa="INSERT INTO clasificaciones (noparte,origen,fraccion,fraccion2,descripcion,
																 proveedor_id,cliente_id,medida,usuario,fecha,hora,clasificado, fundamento_legal)
									VALUES ('$NUM_PARTE','','$FRACCION','$FRACCION10','$DESCRIPCION',$id_proveedor,
											$id_cliente,'$UM','$username','$fecha','$hora','X', '".$FUNDAMENTO."')";
									
						$respuesta = web_service_query($consultaa,'INSERT');
						if($respuesta['Codigo'] != 1){
							break;
						}
						//CONSULTAR ID AGREGADO EN ACCESS
						$consulta = "SELECT id 
									FROM clasificaciones
									WHERE noparte='".$NUM_PARTE."' AND 
											cliente_id=".$id_cliente." AND 
											proveedor_id=".$id_proveedor;
											
						$respuesta = web_service_query($consulta,'SELECT');
						//error_log(json_encode($respuesta));
						if($respuesta['Codigo'] != 1){
							break;
						}
						$consecutivo = 0;
						$aRows = json_decode($respuesta['Adicional1'], true);
						for($i = 0; $i<count($aRows); $i++){
							$consecutivo = $aRows[$i]['id'] + 1;
						}
						if($consecutivo == 0){
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = 'Error al consultar consecutivo access.';
							$respuesta['Error'] = 'Error: consulta['.$consulta.'] respuesta[resultado:'.$respuesta['Adicional1'].']';
							$consulta = "DELETE FROM clasificaciones WHERE noparte='".$NUM_PARTE."' AND cliente_id=".$id_cliente." AND proveedor_id=".$id_proveedor;
							$res = web_service_query($consulta,'UPDATE');
							break;
						}
						//INSERTAR MY SQL
						$consulta = "INSERT INTO clasificaciones (id,noparte,origen,fraccion,fraccion2,descripcion,proveedor_id,
														cliente_id,medida,usuario,fecha,hora,clasificado, fundamento_legal) 
											VALUES (	'$consecutivo','$NUM_PARTE','','$FRACCION','$FRACCION10','$DESCRIPCION',
														'$id_proveedor','$id_cliente','$UM','$username','$fecham','$hora','X','".$FUNDAMENTO."')";
														
						$consultam=" INSERT INTO bodegareplica.clasificaciones (id,noparte,origen,fraccion,fraccion2,descripcion,
																		proveedor_id,cliente_id,medida,usuario,fecha,hora,fundamento_legal) 
																VALUES ($consecutivo,'$NUM_PARTE','','$FRACCION','$FRACCION10','$DESCRIPCION',
																	$id_proveedor,$id_cliente,'$UM','$username','$fecham','$hora','X','".$FUNDAMENTO."')";
						mysqli_query($cmysqli,"BEGIN");
						//Replica
						$query = mysqli_query($cmysqli,$consultam);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = 'Error al actualizar la clasificacion.';
							$respuesta['Error'] = '[REPLICA:Error :: item: '.($i+1).' |NumParte:'.$NUM_PARTE.'|'.$error.']'.$consultam;
							$consulta = "DELETE FROM clasificaciones WHERE noparte='".$NUM_PARTE."' AND cliente_id=".$id_cliente." AND proveedor_id=".$id_proveedor;
							$res = web_service_query($consulta,'UPDATE');
							mysqli_query($cmysqli,"ROLLBACK");
							break;
						}
						//Normal
						$query = mysqli_query($cmysqli,$consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = 'Error al guardar la clasificacion.';
							$respuesta['Error'] = '[BODEGA:Error :: item: '.($i+1).' |NumParte:'.$NUM_PARTE.'|'.$error.']'.$consulta;
							$consulta = "DELETE FROM clasificaciones WHERE noparte='".$NUM_PARTE."' AND cliente_id=".$id_cliente." AND proveedor_id=".$id_proveedor;
							$res = web_service_query($consulta,'UPDATE');
							mysqli_query($cmysqli,"ROLLBACK");
							break;
						}
						mysqli_query($cmysqli,"COMMIT");
					}else{
						
						$fechan = new DateTime();
						$fecha=$fechan->format("m/d/Y");
						$fecham=$fechan->format("Y-m-d 00:00:00");
						$hora=$fechan->format("g:i:s A");
						//Access
						$consultaa="
							UPDATE clasificaciones
							SET fecha= '$fecha',
								hora= '$hora' ,
								usuario= '$username' ,
								fraccion= '$FRACCION',
								fraccion2 = '$FRACCION10',
								descripcion= '$DESCRIPCION' ,
								medida= '$UM',
								clasificado='X'
							WHERE noparte='".$NUM_PARTE."' AND 
											cliente_id=".$id_cliente." AND 
											proveedor_id=".$id_proveedor;
											
						$respuesta = web_service_query($consultaa,'UPDATE');
						if($respuesta['Codigo'] != 1){
							break;
						}
						//MYSQL
						$consulta = "UPDATE clasificaciones SET 
														fraccion = '".$FRACCION."',
														fraccion2 = '".$FRACCION10."',
														descripcion = '".$DESCRIPCION."',
														medida = '".$UM."',
														usuario = '".$username."',
														clasificado = 'X'
									WHERE noparte='".$NUM_PARTE."' AND 
											cliente_id='".$id_cliente."' AND 
											proveedor_id='".$id_proveedor."'";
						
						$consultam="
							UPDATE bodegareplica.clasificaciones
							SET fecha = '$fecham',
								hora = '$hora' ,
								usuario = '$username' ,
								fraccion = '$FRACCION' ,
								fraccion2 = '$FRACCION10',
								descripcion= '$DESCRIPCION' ,
								medida= '$UM',
								clasificado='X'
							WHERE noparte='".$NUM_PARTE."' AND 
											cliente_id='".$id_cliente."' AND 
											proveedor_id='".$id_proveedor."'";
						mysqli_query($cmysqli,"BEGIN");
						$query = mysqli_query($cmysqli,$consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = 'Error al actualizar la clasificacion.';
							$respuesta['Error'] = '[Error :: item: '.($i+1).' |NumParte:'.$NUM_PARTE.'|'.$error.']'.$consulta;
							mysqli_query($cmysqli,"ROLLBACK");
							break;
						}
						$query = mysqli_query($cmysqli,$consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = 'Error al actualizar la clasificacion.';
							$respuesta['Error'] = '[Error :: item: '.($i+1).' |NumParte:'.$NUM_PARTE.'|'.$error.']'.$consulta;
							mysqli_query($cmysqli,"ROLLBACK");	
							break;
						}
						mysqli_query($cmysqli,"COMMIT");
					}
				}
			} else {
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'No existen registros para actualizar.';
				$respuesta['Error'] = '';
			}
		}else{
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error en los parametros de entrada.';
			$respuesta['Error'] = '';
		}
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_activar_principal().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

function fcn_generar_ficha_clasificacion(){
	global $_GET, $bDebug, $mysqlserver, $mysqldb, $mysqluser, $mysqlpass;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_GET['id']) && !empty($_GET['id'])) {
			$nIdClasificacion = $_GET['id'];
			
			$parametros = array('idClasificacion' => $nIdClasificacion);
			//***********************************************************//
			
			$file_rpt='rpttmp_'.time();
			$input = __DIR__ . "/ajax/clasificaciones/rptFichaClasificacion.jasper";  
			$output = __DIR__ . "/ajax/clasificaciones/$file_rpt";  
			$options = [ 
				'format' => ['pdf'],
				'params' => $parametros,
				'db_connection' => [
					'driver' => 'generic',
					'host' => $mysqlserver,
					'port' => '3306',
					'database' => $mysqldb,
					'username' => $mysqluser,
					'password' => $mysqlpass,
					'jdbc_driver' => 'com.mysql.jdbc.Driver',
					'jdbc_url' => "jdbc:mysql://$mysqlserver/$mysqldb"
				]	
			];

			//error_log(json_encode($options));
			$jasper = new PHPJasper;

			try {
				$jasper->process(
					$input,
					$output,
					$options
				)->execute(); //Reemplazar execute por output para obtener linea de comando en caso de querer hacer debug 
			}catch (Exception $e) {
				echo  $e->getMessage(), "\n";
			}

			if (headers_sent()) {
				echo 'HTTP header already sent';
			} else {
				header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
				header("Content-Type: application/pdf");
				header("Content-Transfer-Encoding: Binary");
				header("Content-Length: ".filesize($output.".pdf"));
				header("Content-Disposition: inline; filename=\"prueba.pdf\"");
				header('Accept-Ranges: bytes');
				readfile($output.".pdf");
				unlink($output.".pdf");
				exit;
			}
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_consultar_documentos().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
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

function web_service_query($consulta, $accion){
	global $URL_ws_webtools;
	$result['Codigo'] = 1;
	
	$client = new nusoap_client("$URL_ws_webtools/webtools/ws_mdb/ws_mdb.php?wsdl","wsdl");
	$err = $client->getError();
	if ($err) {
		$result['Codigo'] = -1;
		$result['Mensaje'] = "Constructor error:". $err ;
		$result['Error'] = '';
	}
	$param = array('usuario' => 'admin','password' => 'r0117c','consulta' => $consulta,'tipo' => $accion,'bd' => 'revisiones');
	$result = $client->call('ws_mdb', $param);
	$err = $client->getError();
	if ($err) {
		$result['Codigo'] = -1;
		$result['Mensaje'] = "Constructor error:". $err ;
		$result['Error'] = '';
		
	}
	if($result['Codigo']!=1){
		$result['Codigo'] = -1;
		$result['Mensaje'] = "Error del WS: ".$result['Mensaje'].". Consulta: ".$consulta;
		$result['Error'] = '';
	}
	return $result;
}