<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');
if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['file_name']) && !empty($_POST['file_name'])) {
		$respuesta['Codigo']=1;
		$file_name = $_POST['file_name'];
		$tipo_archivo = $_POST['tipo_archivo'];
		
		switch($tipo_archivo){
			case 'packing':
				$sConsulta = " UPDATE cruces_expo_detalle SET
									archivo_packinglist = NULL 
								WHERE archivo_packinglist like '%".$file_name."%'";
				break;
			case 'certificado':
				$sConsulta = " UPDATE cruces_expo_detalle SET
									archivo_cert_origen = NULL 
								WHERE archivo_cert_origen like '%".$file_name."%'";
				break;
			case 'ticketbas':
				$sConsulta = " UPDATE cruces_expo_detalle SET
									archivo_ticketbascula = NULL 
								WHERE archivo_ticketbascula like '%".$file_name."%'";
				break;
		}
		$query = mysqli_query($cmysqli,$sConsulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al eliminar el documento. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
			exit(json_encode($respuesta));
		}
		unlink($dir_archivos_facturas.$file_name);
		$respuesta['Mensaje']='El documento se ha eliminado correctamente!!';	
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}