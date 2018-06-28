<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
	
	if (isset($_POST['aSelected']) && !empty($_POST['aSelected'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$aSelected = json_decode($_POST['aSelected']);
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		mysqli_query($cmysqli_exp, "BEGIN");
		for ($i=0; $i < count($aSelected); $i++) {			
			$consulta = "UPDATE expedientes.seguimiento_pedime 
						 SET fecha_recepcion_entrega='".$fecha_registro."'
						    ,fecha_cc_recepcion='".$fecha_registro."'
						 WHERE id_registro=".$aSelected[$i];				
			
			$query = mysqli_query($cmysqli_exp, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_exp);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al actualizar los Pedimentos. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
				break;
			}
		}
		
		if($respuesta['Codigo'] == 1 ){
			$respuesta['Mensaje']='Pedimentos entregados correctamente!';
			$respuesta['Fecha']=$fecha_registro;

			mysqli_query($cmysqli_exp, "COMMIT");
		} else {
			mysqli_query($cmysqli_exp, "ROLLBACK");
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}

