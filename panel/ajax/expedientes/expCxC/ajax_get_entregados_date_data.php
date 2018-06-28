<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
	
	if (isset($_POST['sIdEmpresa']) && !empty($_POST['sIdEmpresa'])) {
	
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sIdEmpresa = json_decode($_POST['sIdEmpresa']);
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$consulta = "SELECT fecha_cc_entrega
					 FROM expedientes.seguimiento_pedime
					 WHERE id_empresa=".$sIdEmpresa." AND
						   fecha_cc_facturacion IS NOT NULL AND
						   fecha_cc_entrega IS NOT NULL
					 GROUP BY fecha_cc_entrega
					 ORDER BY fecha_cc_entrega DESC";	

		$query = mysqli_query($cmysqli_exp, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar la lista de relaciones. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			$respuesta['Fechas'] = '';
			while($row = mysqli_fetch_array($query)){
				$respuesta['Fechas'] .= '<option value="'.$row['fecha_cc_entrega'].'">'.$row['fecha_cc_entrega'].'</option>';			
			}
		}	
	}
	echo json_encode($respuesta);
}

