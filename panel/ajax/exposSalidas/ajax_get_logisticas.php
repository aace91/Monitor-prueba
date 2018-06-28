<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sIdSalida']) && !empty($_POST['sIdSalida'])) {  
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$sIdSalida = $_POST['sIdSalida'];
		$sIdCliente = $_POST['sIdCliente'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$sLogisticas = '';
		$consulta = "SELECT logistica, nombre
					 FROM bodega.expos_salidas_logisticas
					 WHERE id_cliente_expo='".$sIdCliente."'
					 ORDER BY nombre";
		
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar las logisticas. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			$sLogisticas .= '<option value=""></option>';
			while($row = mysqli_fetch_array($query)){
				$sLogisticas .= '<option value="'.$row['logistica'].'">'.$row['nombre'].'</option>';
			}
		}

		$respuesta['sLogisticas']=$sLogisticas;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}