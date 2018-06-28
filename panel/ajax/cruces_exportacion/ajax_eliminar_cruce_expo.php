<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_cruce']) && !empty($_POST['id_cruce'])) {
		$respuesta['Codigo']=1;
		$id_cruce = $_POST['id_cruce'];
		
		$consulta = "DELETE FROM cruces_expo WHERE id_cruce = ".$id_cruce;
		mysqli_query($cmysqli,"BEGIN");
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al eliminar el cruce. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
			mysqli_query($cmysqli,"ROLLBACK");
			mysqli_query($cmysqli,"COMMIT");
			exit(json_encode($respuesta));
		}
		$consulta = "DELETE FROM cruces_expo_detalle WHERE id_cruce = ".$id_cruce;
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al eliminar las facturas del cruce. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
			mysqli_query($cmysqli,"ROLLBACK");
			mysqli_query($cmysqli,"COMMIT");
			exit(json_encode($respuesta));
		}
		$consulta = "DELETE FROM cruces_expo_permisos WHERE id_detalle_cruce in (SELECT id_detalle_cruce FROM cruces_expo_detalle WHERE id_cruce  = ".$id_cruce.")";
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al eliminar los permisos del cruce. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
			mysqli_query($cmysqli,"ROLLBACK");
			mysqli_query($cmysqli,"COMMIT");
			exit(json_encode($respuesta));
		}
		mysqli_query($cmysqli,"COMMIT");
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}