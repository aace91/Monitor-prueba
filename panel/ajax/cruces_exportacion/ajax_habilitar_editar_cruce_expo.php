<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_cruce']) && !empty($_POST['id_cruce'])) {
		$respuesta['Codigo']=1;
		$id_cruce = $_POST['id_cruce'];
		
		$consulta = "UPDATE cruces_expo SET habilitado_editar = '1' WHERE id_cruce = ".$id_cruce;
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al habilitar el cruce. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
			exit(json_encode($respuesta));
		}
		$respuesta['Mensaje']='El cruce se habilito correctamente para editar!!.';
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}