<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../url_archivos.php');
require('enviar_notificacion_cruces.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_detalle_cruce']) && !empty($_POST['id_detalle_cruce'])) {
		$respuesta['Codigo']=1;
		$id_detalle_cruce = $_POST['id_detalle_cruce'];
		$cons_fact = $_POST['cons_fact'];
		
		$consulta = "UPDATE cruces_expo_detalle SET cons_fact = ".$cons_fact." WHERE id_detalle_cruce = ".$id_detalle_cruce;
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al vincular la factura de CASA con la de Cruces.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
		}
		mysqli_close($cmysqli);
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}