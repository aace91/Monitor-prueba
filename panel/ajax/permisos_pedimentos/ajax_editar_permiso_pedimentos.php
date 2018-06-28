<?php
include_once('./../../../checklogin.php');
include('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');

if ($loggedIn == false){
	echo '500';
}else{	
	
	if (isset($_POST['id_permiso']) && !empty($_POST['id_permiso'])) {
		$respuesta['Codigo'] = 1;	
		$id_permiso = $_POST['id_permiso'];
		$numero_permiso = $_POST['numero_permiso'];
		$id_cliente = $_POST['id_cliente']; 
		$fecha_ini = $_POST['fecha_ini'];
		$fecha_fin = $_POST['fecha_fin'];
		$valor_total_permiso = $_POST['valor_total']; 
		$cantidad_total_permiso = $_POST['cantidad_total']; 
		$valor_delbravo = $_POST['valor_delbravo']; 
		$cantidad_delbravo = $_POST['cantidad_delbravo'];
		
		$aviso_adhesion = $_POST['aviso_adhesion'];
		
		$bSubirDoc = $_POST['bSubirDoc'];
		if($bSubirDoc == 'true'){
			$files = $_FILES;
			if($files["documento_permiso"]["error"] == 0) {	
					
				$pdfFile = $files["documento_permiso"]["tmp_name"];				
				$ext = pathinfo($files["documento_permiso"]["name"], PATHINFO_EXTENSION);
				
				$NomPDF = 'Permiso_'.$numero_permiso.'_'.date("YmdHis").".".$ext;
				
				if(!isset($pdfFile)) {
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'El tamaño del archivo excede del máximo permitido.';
					$respuesta['Error'] = '';
				}else{
					if(!move_uploaded_file($pdfFile, $dir_archivos_permisos.$NomPDF)){
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
		}
		
		if($respuesta['Codigo'] == 1){
			$fecha_registro = date("Y-m-d H:i:s");

			$consulta = "UPDATE bodega.permisos_pedimentos SET
							numero_permiso = '".$numero_permiso."',
							id_cliente = '".$id_cliente."',
							fecha_vigencia_ini = '".$fecha_ini."',
							fecha_vigencia_fin = '".$fecha_fin."',
							valor_dlls_total = ".$valor_total_permiso.",
							cantidad_total = ".$cantidad_total_permiso.",
							valor_dlls_delbravo = ".$valor_delbravo.",
							cantidad_delbravo = ".$cantidad_delbravo.",
							aviso_adhesion = ".$aviso_adhesion.",";
			if($bSubirDoc == 'true'){				
				$consulta .= "archivo_permiso = '".$NomPDF."',";
			}
			$consulta .= "	usuario_registro = ".$id.",
							fecha_registro = '".$fecha_registro."'
					WHERE id_permiso = ".$id_permiso;

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al actualizar la informacion. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']['.$consulta.']';
			} else {
				$respuesta['Codigo']=1;
				$respuesta['Mensaje']='La informacion del permiso se ha actualizado correctamente!';
			}
		}
	}else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
