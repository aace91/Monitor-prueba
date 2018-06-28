<?php
include_once('./../../../checklogin.php');
include('./../../../connect_r8va.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['id_fraccion']) && !empty($_POST['id_fraccion'])) {		
		
		$id_fraccion = trim($_POST['id_fraccion']);
		$descripcion = trim($_POST['descripcion']);
		$fraccion = trim($_POST['fraccion']);
		$cantidad = trim($_POST['cantidad']);
		$valor = trim($_POST['valor']);
		//$fecha_vencimiento = trim($_POST['fecha_vencimiento']);
		$fecha_vencimiento = date_format(date_create_from_format('d/m/Y',$_POST['fecha_vencimiento']),'Y-m-d H:i:s');
		$numero_permiso = trim($_POST['numero_permiso']);
	
		//***********************************************************//
		$id_usuario = $id;
		$fecha_registro =  date("Y-m-d H:i:s");		
		//***********************************************************//
		
		$consulta = "UPDATE fracciones SET 	descripcion = '".$descripcion."',
											fraccion = '".$fraccion."',
											cantidad = ".$cantidad.",
											valor = ".$valor.",
											fecha_vencimiento = '".$fecha_vencimiento."',
											fecha_ult_act = '".$fecha_registro."',
											usuario_ult_act = '".$id_usuario."'
						WHERE id_fraccion = ".$id_fraccion;
		
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al actualizar la informacion del registro.';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		$respuesta['Codigo'] = 1;
		$respuesta['Mensaje'] = 'El registro se ha actualizado correctamente!!.';
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

