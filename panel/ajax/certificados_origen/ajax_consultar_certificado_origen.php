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
		
		$consulta = "SELECT co.descripcion_mercancia,co.fecha_vigencia_ini,co.fecha_vigencia_fin,co.id_cliente,co.archivo_certificado
							FROM certificados_origen co
							WHERE co.id_certificado = ".$id_certificado;
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']= 'Error al consultar el certificado de origen en la base de datos. [bodega.certificados_origen] ['.$error.']['.$consulta.']';
		} else {
			if(mysqli_num_rows($query) > 0){
				while($row = mysqli_fetch_array($query)){
					$respuesta['id_cliente'] = $row['id_cliente'];
					$respuesta['descripcion_mercancia'] = $row['descripcion_mercancia'];
					$respuesta['fecha_ini'] = date_format(new DateTime($row['fecha_vigencia_ini']),"d/m/Y");
					$respuesta['fecha_fin'] = date_format(new DateTime($row['fecha_vigencia_fin']),"d/m/Y");
					$respuesta['documento'] = $URL_archivos_certificados_origen.$row['archivo_certificado'];
				}
			}else{
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar el  certificado de origen. [id_certificado o cliente NO EXISTE]';
			}
		}
	}else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
