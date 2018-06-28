<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_permiso']) && !empty($_POST['id_permiso'])) {
		$respuesta['Codigo']=1;
		$id_permiso = $_POST['id_permiso'];
		$id_detalle_cruce = $_POST['id_detalle_cruce'];
		
		$consulta = "DELETE FROM cruces_expo_permisos WHERE id_detalle_cruce_permiso = ".$id_permiso;
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al eliminar el cruce. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
			exit(json_encode($respuesta));
		}
		$respuesta['Mensaje']='El cruce se ha eliminado correctamente!!';
		include('consultar_permisos_cruces.php');		
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}