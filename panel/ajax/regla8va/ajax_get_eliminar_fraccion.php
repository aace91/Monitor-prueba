<?php
include_once('./../../../checklogin.php');
include('./../../../connect_r8va.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['id_fraccion']) && !empty($_POST['id_fraccion'])) {		
		
		$id_fraccion = trim($_POST['id_fraccion']);
	
		//***********************************************************//
		$id_usuario = $id;
		$fecha_eliminado =  date("Y-m-d H:i:s");		
		//***********************************************************//
		
		$consulta = "UPDATE fracciones SET 	eliminado = '1',
											fecha_eliminado = '".$fecha_eliminado."',
											usuario_eliminado = '".$id_usuario."'
						WHERE id_fraccion = ".$id_fraccion;
		
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al eliminar el registro.';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		$respuesta['Codigo'] = 1;
		$respuesta['Mensaje'] = 'El registro se ha eliminado correctamente!!.';
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

