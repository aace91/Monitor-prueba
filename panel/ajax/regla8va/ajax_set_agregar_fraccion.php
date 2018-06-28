<?php
include_once('./../../../checklogin.php');
include('./../../../connect_r8va.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['descripcion']) && !empty($_POST['descripcion'])) {		
		
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
		$consulta = "SELECT * FROM  fracciones WHERE descripcion = '".$descripcion."' AND fraccion = '".$fraccion."' AND numero_permiso = '".$numero_permiso."' AND eliminado = '0'";
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar informacion.';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) == 0){
			//INSERT
			$consulta = "INSERT INTO fracciones (descripcion,fraccion,cantidad,valor,fecha_vencimiento,numero_permiso,fecha_registro,usuario_registro)
									VALUES (	'".$descripcion."',
												'".$fraccion."',
												".$cantidad.",
												".$valor.",
												'".$fecha_vencimiento."',
												'".$numero_permiso."',
												'".$fecha_registro."',
												'".$id_usuario."')";

			$query = mysqli_query($cmysqli_s8va, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_s8va);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al insertar la informacion del registro.';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				exit(json_encode($respuesta));
			}
			$respuesta['Codigo'] = 1;
			$respuesta['Mensaje'] = 'El registro se ha dado de alta correctamente!!.';
		}else{
			//UPDATE
			$consulta = "UPDATE fracciones SET 	cantidad = ".$cantidad.",
												valor = ".$valor.",
												fecha_vencimiento = '".$fecha_vencimiento."',
												fecha_ult_act = '".$fecha_registro."',
												usuario_ult_act = '".$id_usuario."'
							WHERE descripcion = '".$descripcion."' AND fraccion = '".$fraccion."' AND numero_permiso = '".$numero_permiso."'";
			
			$query = mysqli_query($cmysqli_s8va, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_s8va);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al actualizar la informacion del registro.[Registro Existente]';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				exit(json_encode($respuesta));
			}

			$respuesta['Codigo'] = 1;
			$respuesta['Mensaje'] = 'El registro ya existia y se ha actualizado correctamente!!.';
		}
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

