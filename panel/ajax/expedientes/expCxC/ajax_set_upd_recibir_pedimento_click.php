<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
		
	if (isset($_POST['sIdEmpresa']) && !empty($_POST['sIdEmpresa'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sIdEmpresa = $_POST['sIdEmpresa'];
		$aRegistros = json_decode($_POST['aRegistros']);
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		mysqli_query($cmysqli_exp, "BEGIN");
		for ($i=0; $i < count($aRegistros); $i++) {
			$sIdRegistro = $aRegistros[$i];
			
			$consulta = "UPDATE expedientes.seguimiento_pedime 
						 SET id_empresa='".$sIdEmpresa."'
							,fecha_cc_recepcion='".$fecha_registro."'
						 WHERE id_registro=".$sIdRegistro;				
			
			$query = mysqli_query($cmysqli_exp, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_exp);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al recibir el registro seleccionado. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
				break;
			}
		}
		
		if($respuesta['Codigo'] == 1 ){
			$respuesta['Mensaje']='Pedimentos recibidos correctamente!';
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

