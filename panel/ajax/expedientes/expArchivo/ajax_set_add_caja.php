<?php

include_once('./../../../../checklogin.php');

$sTableName = 'expedientes.cajas';

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
		
	if (isset($_POST['sIdEmpresa']) && !empty($_POST['sIdEmpresa'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sIdEmpresa = $_POST['sIdEmpresa'];
		$sObservaciones = $_POST['sObservaciones'];
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$consulta = "INSERT INTO ".$sTableName."
					    (ubicacion, estatus, observaciones, id_empresa, fecha_estatus)
					 VALUES 
					    (1
						,1
						,'".$sObservaciones."'
						,".$sIdEmpresa."
						,'".$fecha_registro."')";
		
		$query = mysqli_query($cmysqli_exp, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al agregar nueva caja, Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';	
		} else {
			$idCaja = mysqli_insert_id($cmysqli_exp);

			$respuesta['idCaja'] = $idCaja;
			$respuesta['Mensaje']='Caja agregada correctamente! Caja creada: [ '.$idCaja.' ]';
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}