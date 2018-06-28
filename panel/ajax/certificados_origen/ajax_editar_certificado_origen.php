<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');

if ($loggedIn == false){
	echo '500';
}else{	
	
	if (isset($_POST['id_certificado']) && !empty($_POST['id_certificado'])) {
		$respuesta['Codigo'] = 1;	
		
		$id_certificado = $_POST['id_certificado'];
		$descripcion = $_POST['descripcion'];
		$id_cliente = $_POST['id_cliente']; 
		$fecha_ini = $_POST['fecha_ini'];
		$fecha_fin = $_POST['fecha_fin'];
		$bSubirDoc = $_POST['bSubirDoc'];
		
		if($bSubirDoc == 'true'){
			$files = $_FILES;	
			if($files["documento_certificado"]["error"] == 0) {	
					
				$pdfFile = $files["documento_certificado"]["tmp_name"];				
				$ext = pathinfo($files["documento_certificado"]["name"], PATHINFO_EXTENSION);
				
				$NomPDF = 'CerOri_'.$id_cliente.'_'.date("YmdHis").".".$ext;
				
				if(!isset($pdfFile)) {
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'El tamaño del archivo excede del máximo permitido.';
					$respuesta['Error'] = '';
					break;
				}else{
					if(!move_uploaded_file($pdfFile, $dir_archivos_certificados_origen.$NomPDF)){
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = 'Error al guardar el archivo en el servidor.';
						$respuesta['Error'] = '';
						break;
					}else{
						error_log('Archivo Guardado:'.$dir_archivos_certificados_origen.$NomPDF);
					}
				}
			}else{
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error en el archivo.';
				$respuesta['Error'] = '';
				break;
			}
		}
		if($respuesta['Codigo'] == 1){
			$fecha_registro = date("Y-m-d H:i:s");

			$consulta = "UPDATE certificados_origen SET 
									descripcion_mercancia = '".$descripcion."',
									id_cliente = '".$id_cliente."',
									fecha_vigencia_ini = '".$fecha_ini."',
									fecha_vigencia_fin = '".$fecha_fin."',";
			if($bSubirDoc == 'true'){
				$consulta .= "		archivo_certificado = '".$NomPDF."',";
			}
			$consulta .= "			usuario_ult_act = ".$id.",
									fecha_ult_act = '".$fecha_registro."'
							WHERE id_certificado = ".$id_certificado;

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al actualizar la informacion. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']['.$consulta.']';
			} else {
				$respuesta['Codigo']=1;
				$respuesta['Mensaje']='El certificado de origen se ha guardado correctamente!';
			}
		}
	}else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
