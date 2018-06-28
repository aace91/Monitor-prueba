<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');
if ($loggedIn == false){
	echo '500';
}else{	
	
	if (isset($_POST['id_permiso']) && !empty($_POST['id_permiso'])) {
		$respuesta['Codigo'] = 1;	
		
		$id_permiso = $_POST['id_permiso'];
		
		$consulta = "SELECT p.numero_permiso,p.fecha_vigencia_ini,p.fecha_vigencia_fin,p.valor_dlls_total,
								p.cantidad_total,p.valor_dlls_delbravo,p.cantidad_delbravo,p.id_cliente,p.aviso_adhesion,
								IF(p.archivo_permiso IS NULL, '',CONCAT('".$URL_archivos_permisos."',p.archivo_permiso)) archivo_permiso
							FROM permisos_pedimentos p
							WHERE p.id_permiso = ".$id_permiso;
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']= 'Error al consultar permiso en base de datos. [bodega.permisos] ['.$error.']['.$consulta.']';
		} else {
			if(mysqli_num_rows($query) > 0){
				while($row = mysqli_fetch_array($query)){
					$permiso = $row['numero_permiso'];
					$respuesta['id_cliente'] = $row['id_cliente'];
					$respuesta['permiso'] = $row['numero_permiso'];
					$respuesta['fecha_ini'] = date_format(new DateTime($row['fecha_vigencia_ini']),"d/m/Y");
					$respuesta['fecha_fin'] = date_format(new DateTime($row['fecha_vigencia_fin']),"d/m/Y");
					$respuesta['valor_dlls_total'] = $row['valor_dlls_total'];
					$respuesta['cantidad_total'] = $row['cantidad_total'];
					$respuesta['valor_dlls_delbravo'] = $row['valor_dlls_delbravo'];
					$respuesta['cantidad_delbravo'] = $row['cantidad_delbravo'];
					$respuesta['documento'] = $row['archivo_permiso'];
					$respuesta['aviso_adhesion'] = $row['aviso_adhesion'];
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


	