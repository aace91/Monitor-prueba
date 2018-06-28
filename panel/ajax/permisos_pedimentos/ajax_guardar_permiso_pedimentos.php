<?php
include_once('./../../../checklogin.php');
include('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');

if ($loggedIn == false){
	echo '500';
}else{	
	
	if (isset($_POST['numero_permiso']) && !empty($_POST['numero_permiso'])) {
		$respuesta['Codigo'] = 1;	
		
		$numero_permiso = $_POST['numero_permiso'];
		$id_cliente = $_POST['id_cliente']; 
		$fecha_ini = $_POST['fecha_ini'];
		$fecha_fin = $_POST['fecha_fin'];
		$valor_total_permiso = $_POST['valor_total']; 
		$cantidad_total_permiso = $_POST['cantidad_total']; 
		$valor_delbravo = $_POST['valor_delbravo'];
		$aviso_adhesion = $_POST['aviso_adhesion'];
		$cantidad_delbravo = $_POST['cantidad_delbravo']; 
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
			
		if($respuesta['Codigo'] == 1){
			$fecha_registro = date("Y-m-d H:i:s");

			$consulta = "INSERT INTO bodega.permisos_pedimentos
									 (numero_permiso,
									 id_cliente,
									 fecha_vigencia_ini,
									 fecha_vigencia_fin,
									 valor_dlls_total,
									 cantidad_total,
									 valor_dlls_delbravo,
									 cantidad_delbravo,
									 archivo_permiso,
									 aviso_adhesion,
									 usuario_registro,
									 fecha_registro)
							VALUES ('".$numero_permiso."',
									'".$id_cliente."',
									'".$fecha_ini."',
									'".$fecha_fin."',
									".$valor_total_permiso.",
									".$cantidad_total_permiso.",
									".$valor_delbravo.",
									".$cantidad_delbravo.",
									'".$NomPDF."',
									'".$aviso_adhesion."',
									".$id.",
									'".$fecha_registro."')";

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al insertar el pemiso. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']['.$consulta.']';
			} else {
				$respuesta['Codigo']=1;
				$respuesta['Mensaje']='El permiso se ha guardado correctamente!';
			}
		}
	}else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
