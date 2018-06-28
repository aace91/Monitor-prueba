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
		$sNumeroCaja = $_POST['sNumeroCaja'];
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$consulta = "SELECT id_caja
					 FROM expedientes.cajas
					 WHERE id_caja=".$sNumeroCaja." AND
					       id_empresa=".$sIdEmpresa;
		
		$query = mysqli_query($cmysqli_exp, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar el Numero de Caja. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			$num_rows = mysqli_num_rows($query);
			
			if ($num_rows <= 0) {
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "No existe la caja [ ".$sNumeroCaja." ] para la empresa seleccionada, favor de ingresar una diferente.";
				$respuesta['Error'] = '';
			}
		}
		
		if ($respuesta['Codigo']==1) {
			$respuesta['Mensaje']='Caja [ ".$sNumeroCaja." ] Ok.';
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}
