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
		$bCkbMv = ($_POST['bCkbMv'] === 'true');
		$bCkbHc = ($_POST['bCkbHc'] === 'true');
		$sCuentaGastosMvHc = $_POST['sCuentaGastosMvHc'];
		$sPedimentoMvHc = $_POST['sPedimentoMvHc'];
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$consulta = "SELECT a.fecha_archivo_mv, a.fecha_archivo_hc
					 FROM expedientes.seguimiento_pedime AS a
					 WHERE id_registro=".$nIdRegistro;
					 
		$query = mysqli_query($cmysqli_exp, $consulta);
		if ($query==false){ 
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje'] = "Error al consultar la cuenta [".$sCuentaGastosMvHc."] con el pedimento [".$sPedimentoMvHc."]";
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			while($row = mysqli_fetch_array($query)){					
				$fecha_archivo_mv = $row['fecha_archivo_mv'];	
				$fecha_archivo_hc = $row['fecha_archivo_hc'];
				
				$consulta_aux = '';		
				if ($bCkbMv === true) {
					if(is_null($fecha_archivo_mv)) {
						$consulta_aux .= "fecha_archivo_mv='".$fecha_registro."'";
					}
				}
				
				if ($bCkbHc === true) {
					if(is_null($fecha_archivo_hc)) {
						if ($consulta_aux != '') { 
							$consulta_aux .= ",";
						}
						$consulta_aux .= "fecha_archivo_hc='".$fecha_registro."'";
					}
				}
				
				$consulta = "UPDATE expedientes.seguimiento_pedime 
							 SET ".$consulta_aux."
							 WHERE id_registro=".$nIdRegistro;
				
				$query = mysqli_query($cmysqli_exp, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli_exp);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al actualizar la cuenta ['.$sCuentaGastosMvHc.'] con el pedimento ['.$sPedimentoMvHc.'].'; 
					$respuesta['Error'] = ' ['.$error.']';
				}
				
				if($respuesta['Codigo'] == 1 ){
					$respuesta['Mensaje']='Documentos capturados correctamente!';
				}
				
				break;
			}	
		}	
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}

