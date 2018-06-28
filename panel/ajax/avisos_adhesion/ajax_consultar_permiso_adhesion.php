<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');

if ($loggedIn == false){
	echo '500';
}else{
	error_log('Error:'.$_POST['id_permiso']);
	if (isset($_POST['id_permiso']) && !empty($_POST['id_permiso'])) {
		$respuesta['Codigo'] = 1;	
		
		$id_permiso = $_POST['id_permiso'];
		
		$consulta = "SELECT p.numero_permiso,p.fecha_vigencia_ini,p.fecha_vigencia_fin,p.id_cliente,p.archivo_permiso
							FROM permisos_adhesion p
							WHERE p.id_permiso_adhesion = ".$id_permiso;
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']= 'Error al consultar permiso en base de datos. [bodega.permisos] ['.$error.']['.$consulta.']';
		} else {
			if(mysqli_num_rows($query) > 0){
				while($row = mysqli_fetch_array($query)){
					$respuesta['id_cliente'] = $row['id_cliente'];
					$respuesta['permiso'] = $row['numero_permiso'];
					$respuesta['fecha_ini'] = date_format(new DateTime($row['fecha_vigencia_ini']),"d/m/Y");
					$respuesta['fecha_fin'] = date_format(new DateTime($row['fecha_vigencia_fin']),"d/m/Y");
					$respuesta['documento'] = $URL_archivos_permisos.$row['archivo_permiso'];
				}
			}else{
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar el permiso. [id_permiso o cliente NO EXISTE]';
			}
		}
	}else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
