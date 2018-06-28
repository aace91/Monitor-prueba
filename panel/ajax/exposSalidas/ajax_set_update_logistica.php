<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	$respuesta['Codigo'] = 1;	
	
	//***********************************************************//
	
	$sIdSalida = $_POST['sIdSalida'];
	$sIdLogistica = $_POST['sIdLogistica'];  

	//***********************************************************//

	$fecha_registro =  date("Y-m-d H:i:s");
		
	//***********************************************************//
	
	$consulta="UPDATE bodega.expos_salidas
			   SET logistica='".$sIdLogistica."'
			   WHERE id_salida=".$sIdSalida;
						   
	$query = mysqli_query($cmysqli,$consulta);		
	if ($query==false){
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje'] = 'Error al actualizar factura en expos_salidas_facturas'.$consulta;
		$respuesta['Error']=' ['.$error.']';
	} else {
		$respuesta['Mensaje'] = 'Logistica asignada correctamente.';
	}

	echo json_encode($respuesta);
}