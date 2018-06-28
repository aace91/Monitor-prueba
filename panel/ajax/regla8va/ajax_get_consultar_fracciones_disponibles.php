<?php
include_once('./../../../checklogin.php');
include('./../../../connect_r8va.php');


if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['permiso']) && !empty($_POST['permiso'])) {		
		
		$permiso = trim($_POST['permiso']);
		$fraccion = trim($_POST['fraccion']);
		$aFracciones = array();

		$consulta = "SELECT f.id_fraccion,f.fraccion,f.descripcion,(f.cantidad - IFNULL(SUM(fh.cantidad),0)) AS saldo_cantidad, (f.valor - IFNULL(SUM(fh.valor),0)) AS saldo_valor,
								f.fecha_vencimiento
							FROM fracciones f
								LEFT JOIN fracciones_historico fh ON
									f.id_fraccion = fh.id_fraccion
							WHERE f.fraccion = ".$fraccion." AND f.eliminado = '0' AND f.fecha_vencimiento >= CURDATE()
							GROUP BY f.id_fraccion";
		
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar informacion de la fracciones.';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) != 0){
			while($row = mysqli_fetch_array($query)){
				$aFraccion = array(
					"id_fraccion" =>  $row['id_fraccion'],
					"fraccion" =>  $row['fraccion'],
					"descripcion" =>  $row['descripcion'],
					"saldo_cantidad" =>  $row['saldo_cantidad'],
					"saldo_valor" =>  $row['saldo_valor'],
					"fecha_vencimiento" => date( 'd/m/Y', strtotime($row['fecha_vencimiento']))
				);
				array_push($aFracciones,$aFraccion);
			}
			$respuesta['Codigo'] = '1';
			$respuesta['aFracciones'] = $aFracciones;
		}else{
			$respuesta['Codigo'] = '-1';
			$respuesta['Mensaje'] = "No se encontraron fracciones disponibles.";
			$respuesta['Error'] = '';
		}
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

