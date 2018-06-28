<?php
include_once('./../../../checklogin.php');
include('./../../../connect_r8va.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['id_fraccion']) && !empty($_POST['id_fraccion'])) {		
		
		$id_fraccion = trim($_POST['id_fraccion']);

		$consulta = "SELECT id_fraccion,descripcion,fraccion,cantidad,valor,fecha_vencimiento,numero_permiso 
						FROM  fracciones 
						WHERE id_fraccion = ".$id_fraccion;
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar informacion.';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) != 0){
			while($row = mysqli_fetch_array($query)){
				$respuesta['Codigo'] = '1';
				$respuesta['descripcion'] = $row['descripcion'];
				$respuesta['fraccion'] = $row['fraccion'];
				$respuesta['cantidad'] = $row['cantidad'];
				$respuesta['valor'] = $row['valor'];
				$respuesta['fecha_vencimiento'] = $row['fecha_vencimiento'];
				$respuesta['numero_permiso'] = $row['numero_permiso'];
			}
		}else{
			$respuesta['Codigo'] = '-1';
			$respuesta['Mensaje'] = "No se encontro informacion de la fraccion que se desea editar. Favor de contactar el administrador del sistema.";
			$respuesta['Error'] = '';
		}
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

