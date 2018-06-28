<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['id_librop']) && !empty($_POST['id_librop'])) {		
		
		$id_librop = $_POST['id_librop'];
		$referencia = $_POST['referencia'];
		$fecha =  $_POST['fecha'];
		$id_cliente = $_POST['id_cliente'];
		$cliente = $_POST['cliente'];
		$operacion = $_POST['operacion'];
		$cve_pedimento = $_POST['cve_pedimento'];
		$descripcion = $_POST['descripcion'];
		$observaciones = $_POST['observaciones'];
		
		//***********************************************************//
		$fecha_update =  date("Y-m-d H:i:s");		
		//***********************************************************//
		
		$consulta = " UPDATE librop_libro SET 
									referencia = '".$referencia."',
									id_cliente = '".$id_cliente."',
									cliente = '".$cliente."',
									tipo_operacion = '".$operacion."',
									clave_pedimento = '".$cve_pedimento."',
									descripcion_mercancia = '".$descripcion."',
									observaciones = '".$observaciones."',
									fecha_pedimento = '".$fecha."',
									fecha_ult_act = '".$fecha_update."',
									usuario_ult_act = ".$id."
						WHERE id_librop = ".$id_librop;
		
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al actualizar la informacion del pedimento.';
			$respuesta['Error'] = ' [UPDATE librop_libro]['.$error.']';
		}else{
			$respuesta['Codigo'] = 1;
		}
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos del pedimento.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

