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
	
	$consulta = "SELECT fecha_recepcion_entrega, COUNT(*) as total
				 FROM seguimiento_pedime
				 WHERE fecha_recepcion_captura IS NOT NULL AND
					   fecha_recepcion_entrega IS NOT NULL
				 GROUP BY fecha_recepcion_entrega
				 ORDER BY fecha_recepcion_entrega DESC";	

	$query = mysqli_query($cmysqli_exp, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli_exp);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar la lista de relaciones. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
	} else { 
		while($row = mysqli_fetch_array($query)){
			$strFechas .= '<option value="'.$row['fecha_recepcion_entrega'].'">'.$row['fecha_recepcion_entrega'].' - Numero de Registros ('.$row['total'].')</option>';			
		}
	}	

	$respuesta['Fechas'] = $strFechas;
	echo json_encode($respuesta);
}

