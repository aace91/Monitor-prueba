<?php
include_once('./../../../checklogin.php');
include('./../../../connect_r8va.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['permiso']) && !empty($_POST['permiso'])) {		
		
		$permiso = trim($_POST['permiso']);
		$fraccion = trim($_POST['fraccion']);
		$descripcion = trim($_POST['descripcion']);
		$aFracciones = array();$aDescripciones = array();

		if($fraccion == ''){
			//Fracciones
			$consulta = "SELECT fraccion
						FROM fracciones 
						WHERE numero_permiso = '".$permiso."'
						GROUP BY fraccion
						ORDER BY fraccion";
			
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
					array_push($aFracciones,$row['fraccion']);
				}
				$respuesta['Codigo'] = '1';
				$respuesta['aFracciones'] = $aFracciones;
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = "No se encontraron fracciones disponibles.";
				$respuesta['Error'] = '';
			}
		}else{
			//Descripciones
			$consulta = "SELECT id_fraccion,descripcion
							FROM fracciones 
							WHERE numero_permiso = '".$permiso."' AND fraccion= '".$fraccion."'
							ORDER BY descripcion";
			
			$query = mysqli_query($cmysqli_s8va, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_s8va);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al consultar fracciones disponibles.[Descripcion]';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				exit(json_encode($respuesta));
			}
			if(mysqli_num_rows($query) != 0){
				while($row = mysqli_fetch_array($query)){
					$Descripcion = array (
						"id_fraccion" => $row['id_fraccion'],
						"descripcion"=> $row['descripcion']
					);
					array_push($aDescripciones,$Descripcion );
				}
				$respuesta['Codigo'] = '1';
				$respuesta['aDescripciones'] = $aDescripciones;
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = "No se encontraron fracciones disponibles.[Descripciones]";
				$respuesta['Error'] = '';
			}
		}
			
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

