<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
	
	if (isset($_POST['aTraficos']) && !empty($_POST['aTraficos'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$aTraficos = json_decode($_POST['aTraficos']);
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		mysqli_query($cmysqli_exp, "BEGIN");
		for ($i=0; $i < count($aTraficos); $i++) {	
			$sTrafico = $aTraficos[$i];
			$consulta = "UPDATE expedientes.seguimiento_pedime 
						 SET fecha_cp_entrega='".$fecha_registro."'
						 WHERE referencia='".$sTrafico."' AND
						       fecha_cp_entrega IS NULL AND
							   fecha_archivo_archivado IS NULL";
			
			$query = mysqli_query($cmysqli_exp, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_exp);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al entregar el trafico '.$sTrafico.'. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
				break;
			}
		}
		
		if($respuesta['Codigo'] == 1 ){
			$respuesta['Mensaje']='Traficos entregados correctamente!';
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

