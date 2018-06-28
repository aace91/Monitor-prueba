<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	$respuesta['Codigo'] = 1;	
	
	//***********************************************************//
	
	$sIdSalida = $_POST['sIdSalida'];
	$nBultos = $_POST['nBultos'];  

	//***********************************************************//

	$fecha_registro =  date("Y-m-d H:i:s");
		
	//***********************************************************//
	
	$consulta="UPDATE bodega.expos_salidas
			   SET bultos=".$nBultos."
			   WHERE id_salida=".$sIdSalida;
						   
	$query = mysqli_query($cmysqli, $consulta);		
	if ($query==false){
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje'] = 'Error al actualizar factura en expos_salidas_facturas'.$consulta;
		$respuesta['Error']=' ['.$error.']';
	} else {
		$respuesta['Mensaje'] = 'Numero de bultos asignados correctamente.';
	}

	echo json_encode($respuesta);
}