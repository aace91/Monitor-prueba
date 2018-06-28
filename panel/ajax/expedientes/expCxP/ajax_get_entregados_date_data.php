<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
		
	$respuesta['Codigo']=1;		
				
	//***********************************************************//

	$fecha_registro =  date("Y-m-d H:i:s");
	$strFechas = '';

	//***********************************************************//
	
	$consulta = "SELECT fecha_cp_entrega, COUNT(DISTINCT referencia) AS total
				 FROM seguimiento_pedime
				 WHERE fecha_cp_entrega >= '2017-12-04'
				 GROUP BY fecha_cp_entrega
				 ORDER BY fecha_cp_entrega DESC";	

	$query = mysqli_query($cmysqli_exp, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli_exp);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar la lista de relaciones. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
	} else { 
		while($row = mysqli_fetch_array($query)){
			$strFechas .= '<option value="'.$row['fecha_cp_entrega'].'">'.$row['fecha_cp_entrega'].' - Numero de Registros ('.$row['total'].')</option>';			
		}
	}

	$respuesta['Fechas'] = $strFechas;
	echo json_encode($respuesta);
}

