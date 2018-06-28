<?php
include_once('./../../../checklogin.php');
include('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');

$sPathFilesPermisosImpo = $dir_archivos_web."monitor\\permisosImpo\\";

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['sAction']) && !empty($_POST['sAction'])) {
		$respuesta['Codigo'] = 1;	
		
		$sAction = $_POST['sAction'];
		$sIdPermiso = $_POST['sIdPermiso']; 
		$sNumeroPermiso = $_POST['sNumeroPermiso']; 
		$nCliente = $_POST['nCliente'];
		$sVigenciaIni = $_POST['sVigenciaIni'];
		$sVigenciaFin = $_POST['sVigenciaFin'];
		$bBorrarPermiso = $_POST['bBorrarPermiso']; 
		$aFracciones = json_decode($_POST['aFracciones']);
		$oFiles = $_FILES;
		
		/*************************************************/
				
		/*************************************************/
		
		$consulta = "SELECT numero_permiso
					 FROM bodega.permisos_pedimentos_impo
					 WHERE numero_permiso='".$sNumeroPermiso."'";
		
		if ($sAction == 'Editar') {
			$consulta .= " AND id_permiso <> ".$sIdPermiso;
		}		
		
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar numero de permiso. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			while($row = mysqli_fetch_object($query)){
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='El numero de permiso '.$sNumeroPermiso.' ya se encuentra dado de alta';
				$respuesta['Error'] = '';
				break;
			}
		}		
		
		mysqli_query($cmysqli, "BEGIN");
		
		if ($respuesta['Codigo'] == 1) {
			switch ($sAction) {
				case "Nuevo":
					$consulta = "INSERT INTO bodega.permisos_pedimentos_impo
										(numero_permiso,
										id_cliente,
										fecha_vigencia_ini,
										fecha_vigencia_fin)
								 VALUES ('".$sNumeroPermiso."',
										 '".$nCliente."',
										 '".$sVigenciaIni."',
										 '".$sVigenciaFin."')";
					
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al insertar el pemiso. Por favor contacte al administrador del sistema.'; 
						$respuesta['Error'] = ' ['.$error.']';
					} else {
						$sIdPermiso = mysqli_insert_id($cmysqli);
					}
					break;
				case "Editar":
					$consulta = "UPDATE bodega.permisos_pedimentos_impo
								 SET numero_permiso='".$sNumeroPermiso."',
								     id_cliente='".$nCliente."',
									 fecha_vigencia_ini='".$sVigenciaIni."',
									 fecha_vigencia_fin='".$sVigenciaFin."'
								 WHERE id_permiso=".$sIdPermiso;
					
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al actualizar el pemiso. Por favor contacte al administrador del sistema.'; 
						$respuesta['Error'] = ' ['.$error.']';
					}
					break;
			}
		}
		
		if ($respuesta['Codigo'] == 1) {
			$consulta = "DELETE FROM bodega.permisos_pedimentos_impo_det
						 WHERE id_permiso=".$sIdPermiso;
						 
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al guardar las fracciones. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}
			
			if ($respuesta['Codigo'] == 1) {
				foreach ($aFracciones as &$fraccion) {
					//error_log($fraccion->nombre);
					$consulta = "INSERT INTO bodega.permisos_pedimentos_impo_det (  
									 id_permiso
									,nombre
									,cantidad
									,cantidad_delbravo
									,unidad
									,fraccion
									,usuario_registro
								 ) VALUES (
									 ".$sIdPermiso."
									,'".scanear_string($fraccion->nombre)."'
									,".$fraccion->cantidad."
									,".$fraccion->cantidad_delbravo."
									,'".$fraccion->unidad."'
									,'".$fraccion->fraccion."'
									,".$id.")";
					
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al insertar facturas. Por favor contacte al administrador del sistema.'; 
						$respuesta['Error'] = ' ['.$error.']';
						
						break; 
					}
				}
			}
		}
		
		if ($respuesta['Codigo'] == 1) {
			if (isset($oFiles['oPdf'])) {
				$ext = explode('.', basename($oFiles['oPdf']['name']));
				$sFileName = scanear_string($sNumeroPermiso). "_doc." . array_pop($ext);
				$sTarget = $sPathFilesPermisosImpo . $sFileName;
				
				if(move_uploaded_file($oFiles['oPdf']['tmp_name'], $sTarget)) {
					$consulta = "UPDATE bodega.permisos_pedimentos_impo
								 SET archivo_permiso='".$sFileName."'
								 WHERE numero_permiso='".$sNumeroPermiso."'";
								 
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al actualizar permiso. Por favor contacte al administrador del sistema.'; 
						$respuesta['Error'] = ' ['.$error.']';
					}
				} else {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al guardar agregar el permiso.'; 
					$respuesta['Error'] = '';
				}
			} else {
				if ($bBorrarPermiso == 'SI') {
					//error_log('Num permiso: '.$sNumeroPermiso);
					$sFileName = scanear_string($sNumeroPermiso). "_doc.pdf";
					$sTarget = $sPathFilesPermisosImpo . $sFileName;
					if (file_exists($sTarget)) {
						unlink($sTarget);
						$consulta = "UPDATE bodega.permisos_pedimentos_impo
									 SET archivo_permiso=NULL
									 WHERE numero_permiso='".$sNumeroPermiso."'";
									 
						$query = mysqli_query($cmysqli, $consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al actualizar permiso. Por favor contacte al administrador del sistema.'; 
							$respuesta['Error'] = ' ['.$error.']';
						}
					}
				}
			}
		}
		
		if ($respuesta['Codigo'] == 1) { 
			$respuesta['Mensaje']='El permiso se ha guardado correctamente!';
			mysqli_query($cmysqli, "COMMIT");
		} else {
			mysqli_query($cmysqli, "ROLLBACK");
		}
	}else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}

/**
 * Reemplaza todos los caracteres especiales o extraño
 *
 * @param $string
 *  string la cadena a sanear
 *
 * @return $string
 *  string saneada
 */
function scanear_string($string) {
 
    $string = trim($string);
 
    //Esta parte se encarga de eliminar cualquier caracter extraño
    $string = str_replace(
        array("\\", "¨", "º", "~",
             "@", "|", "\"",
             "·", "$", "%", "&", "/",
             "'", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", "<"),
        '',
        $string
    );
 
 
    return $string;
}