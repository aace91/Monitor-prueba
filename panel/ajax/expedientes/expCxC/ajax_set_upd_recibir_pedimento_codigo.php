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
		$sPedimento = $_POST['sPedimento'];
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$consulta = "SELECT id_registro
					 FROM expedientes.seguimiento_pedime
				     WHERE fecha_recepcion_entrega IS NOT NULL AND 
					       fecha_cc_recepcion IS NULL AND 
						   pedimento='".$sPedimento."'";
		
		$query = mysqli_query($cmysqli_exp, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar el Pedimento ['.$sPedimento.'].'; 
			$respuesta['Error'] = ' ['.$error.']';	
		} else {
			$num_rows = mysqli_num_rows($query);
			
			if ($num_rows == 0) {
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "El pedimento [".$sPedimento."] no se encuentra o ya a sido capturado.";
				$respuesta['Error'] = '';
			} else {
				$sIdRegistro = '';	
				while($row = mysqli_fetch_array($query)){					
					$sIdRegistro = $row['id_registro'];
					break;
				}				
				
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
				}
				
				if($respuesta['Codigo'] == 1 ){
					$respuesta['Mensaje']='Pedimento recibido correctamente!';
				} else {
				}
			}
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}

