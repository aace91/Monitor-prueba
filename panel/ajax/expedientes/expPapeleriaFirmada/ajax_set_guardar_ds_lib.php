<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
	
	if (isset($_POST['nIdRegistro']) && !empty($_POST['nIdRegistro'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$nIdRegistro = $_POST['nIdRegistro'];
		$sCuentaGastosDsLib = $_POST['sCuentaGastosDsLib'];
		$sPedimentoDsLib = $_POST['sPedimentoDsLib'];
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		mysqli_query($cmysqli_exp, "BEGIN");
		$consulta = "UPDATE expedientes.seguimiento_pedime 
					 SET fecha_archivo_desaduanamiento='".$fecha_registro."'
					 WHERE id_registro=".$nIdRegistro;
		
		$query = mysqli_query($cmysqli_exp, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al actualizar la cuenta ['.$sCuentaGastosMvHc.'] con el pedimento ['.$sPedimentoMvHc.'].'; 
			$respuesta['Error'] = ' ['.$error.']';
		}
		
		if($respuesta['Codigo'] == 1 ){
			$respuesta['Mensaje']='Documento capturados correctamente!';
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

