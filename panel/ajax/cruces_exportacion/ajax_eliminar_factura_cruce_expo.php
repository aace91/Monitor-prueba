<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../url_archivos.php');
require('enviar_notificacion_cruces.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_factura']) && !empty($_POST['id_factura'])) {
		$respuesta['Codigo']=1;
		$id_factura = $_POST['id_factura'];
		$id_cruce = $_POST['id_cruce'];
		
		$consulta = "DELETE FROM cruces_expo_detalle WHERE id_detalle_cruce = ".$id_factura;
		mysqli_query($cmysqli,"BEGIN");
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al eliminar la factura. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
			mysqli_query($cmysqli,"ROLLBACK");
			mysqli_query($cmysqli,"COMMIT");
			exit(json_encode($respuesta));
		}
		$consulta = "DELETE FROM cruces_expo_permisos WHERE id_detalle_cruce = ".$id_cruce;
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al eliminar los permisos de la factura. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
			mysqli_query($cmysqli,"ROLLBACK");
			mysqli_query($cmysqli,"COMMIT");
			exit(json_encode($respuesta));
		}
		mysqli_query($cmysqli,"COMMIT");
		include('consultar_facturas.php');
		$respuesta['Mensaje']='La factura se ha eliminado correctamente!!';
		$res = enviar_notificacion_nuevo_cruce_email($id_cruce,'Editar','Elimino Factura ID:'.$id_factura);
		if($res['Codigo'] != 1){
			$respuesta['Mensaje'] .=  $res['Error'];
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}