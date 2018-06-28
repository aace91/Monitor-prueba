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
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$consulta = "SELECT id_caja
					 FROM expedientes.cajas
					 WHERE id_empresa=".$sIdEmpresa."
					 ORDER BY id_caja DESC
					 LIMIT 1";
		
		$query = mysqli_query($cmysqli_exp, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar el Numero de Caja. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			$num_rows = mysqli_num_rows($query);
			
			if ($num_rows > 0) {
				while($row = mysqli_fetch_array($query)){
					$respuesta['Caja'] = $row['id_caja'];
				}
			} else {
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "No existen la cajas para la empresa seleccionada, favor de seleccionar una empresa diferente.";
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
