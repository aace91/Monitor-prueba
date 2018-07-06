<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');

if ($loggedIn == false){
	echo '500';
}else{	
	
	if (isset($_POST['descripcion']) && !empty($_POST['descripcion'])) {
		$respuesta['Codigo'] = 1;	
		
		$descripcion = $_POST['descripcion'];
		$id_cliente = $_POST['id_cliente']; 
		$fecha_ini = $_POST['fecha_ini'];
		$fecha_fin = $_POST['fecha_fin'];
		$files = $_FILES;
			
		if($files["documento_certificado"]["error"] == 0) {	
				
			$pdfFile = $files["documento_certificado"]["tmp_name"];				
			$ext = pathinfo($files["documento_certificado"]["name"], PATHINFO_EXTENSION);
			
			$NomPDF = 'CerOri_'.$id_cliente.'_'.date("YmdHis").".".$ext;
			
			if(!isset($pdfFile)) {
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'El tamaño del archivo excede del máximo permitido.';
				$respuesta['Error'] = '';
			}else{
				if(!move_uploaded_file($pdfFile, $dir_archivos_certificados_origen.$NomPDF)){
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Error al guardar el archivo en el servidor.';
					$respuesta['Error'] = '';
				}
			}
		}else{
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error en el archivo.';
			$respuesta['Error'] = '';
		}
			
		if($respuesta['Codigo'] == 1){
			$fecha_registro = date("Y-m-d H:i:s");

			$consulta = "INSERT INTO bodega.certificados_origen
									 (id_cliente,
									 descripcion_mercancia,
									 fecha_vigencia_ini,
									 fecha_vigencia_fin,
									 archivo_certificado,
									 usuario_registro,
									 fecha_registro)
							VALUES ('".$id_cliente."',
									'".$descripcion."',
									'".$fecha_ini."',
									'".$fecha_fin."',
									'".$NomPDF."',
									".$id.",
									'".$fecha_registro."')";

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al insertar la informacion del certificado de origen. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']['.$consulta.']';
			} else {
				$respuesta['Codigo']=1;
				$respuesta['Mensaje']='la informacion del certificado de origen se ha guardado correctamente!';
			}
		}
	}else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
