<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['id_librop']) && !empty($_POST['id_librop'])) {
		$id_librop = $_POST['id_librop'];
		
		$consulta = "SELECT * FROM librop_libro WHERE id_librop = '".$id_librop."'";
		
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error = mysqli_error($cmysqli);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar la informacion del pedimento. ['.$error.']';
		}else{
			$respuesta['Codigo'] = 1;
			$respuesta['nrows'] = mysqli_num_rows($query);
			while($row = mysqli_fetch_array($query)){
				$respuesta['fecha_pedimento'] = $row['fecha_pedimento'];
				$respuesta['referencia'] = $row['referencia'];
				$respuesta['patente'] = $row['patente'];
				$respuesta['id_aduana'] = $row['id_aduana'];
				$respuesta['anio'] = $row['anio'];
				$respuesta['id_cliente'] = $row['id_cliente'];
				$respuesta['tipo_operacion'] = $row['tipo_operacion'];
				$respuesta['clave_pedimento'] = $row['clave_pedimento'];
				$respuesta['descripcion_mercancia'] = $row['descripcion_mercancia'];	
				$respuesta['observaciones'] = $row['observaciones'];
			}
		}			
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "458 : Error al recibir los datos de la relacion.";
	}
	echo json_encode($respuesta);
}