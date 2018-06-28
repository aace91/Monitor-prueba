<?php

include_once('./../../../../checklogin.php');

$sTableName = 'expedientes.expedientes';

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
		
	if (isset($_POST['sIdEmpresa']) && !empty($_POST['sIdEmpresa'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sIdEmpresa = $_POST['sIdEmpresa'];
		$sCuentaCancelada = $_POST['sCuentaCancelada'];
		$sCuentaNueva = $_POST['sCuentaNueva'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$aCuentaCancelada = explode("-", $sCuentaCancelada);
		$aCuentaNueva = explode("-", $sCuentaNueva);
		
		$sCanceladaTIPO_MOV = $aCuentaCancelada[0];
		$sCanceladaNO_BANCO = $aCuentaCancelada[1];
		$sCanceladaNO_MOV = $aCuentaCancelada[2];
		
		$sNuevaTIPO_MOV = $aCuentaNueva[0];
		$sNuevaNO_BANCO = $aCuentaNueva[1];
		$sNuevaNO_MOV = $aCuentaNueva[2];
		
		mysqli_query($cmysqli_exp, 'BEGIN');
		
		$consulta = "UPDATE expedientes.seguimiento_pedime 
					 SET tipo_mov='".$sNuevaTIPO_MOV."',
					     no_banco='".$sNuevaNO_BANCO."',
						 no_mov='".$sNuevaNO_MOV."'
					 WHERE id_empresa=".$sIdEmpresa." AND 
						   tipo_mov='".$sCanceladaTIPO_MOV."' AND
						   no_banco='".$sCanceladaNO_BANCO."' AND
						   no_mov='".$sCanceladaNO_MOV."'";
		
		$query = mysqli_query($cmysqli_exp, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al actualizar cuenta cancelada seccion - Seguimiento. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$consulta = "UPDATE expedientes.expedientes 
						 SET tipo_mov='".$sNuevaTIPO_MOV."',
							 no_banco='".$sNuevaNO_BANCO."',
							 no_mov='".$sNuevaNO_MOV."'
						 WHERE id_empresa=".$sIdEmpresa." AND 
							   tipo_mov='".$sCanceladaTIPO_MOV."' AND
							   no_banco='".$sCanceladaNO_BANCO."' AND
							   no_mov='".$sCanceladaNO_MOV."'";
			
			$query = mysqli_query($cmysqli_exp, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_exp);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al actualizar cuenta cancelada seccion - Expedinetes. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}	
		}
		
		if ($respuesta['Codigo']==1) {
			mysqli_query($cmysqli_exp, "COMMIT");
			$respuesta['Mensaje']='Cuenta cancelada y reemplazada correctamente';
		} else {
			mysqli_query($cmysqli_exp, "ROLLBACK");
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}