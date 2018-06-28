<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sIdPlantilla']) && !empty($_POST['sIdPlantilla'])) { 
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$sIdPlantilla = $_POST['sIdPlantilla'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");

		//***********************************************************//
		
		$consulta = "UPDATE expos_plantilla_gral
					 SET fecha_del='".$fecha_registro."'
						,id_usuario_del=".$id."
					 WHERE id_plantilla=".$sIdPlantilla;
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al eliminar Embarque. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			$respuesta['Mensaje']='Registro eliminado correctamente.';
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}

	echo json_encode($respuesta);
}

function get_column_value($oValue, $sType = '') {
	if(is_null($oValue)) { 
		return 'null';
	} else {
		switch ($sType) {
			case 'string':
				return "'".$oValue."'";
				break;
				
			case 'numeric':
				return $oValue;
				break;   
				
			default:
				return "'".$oValue."'";
		}
	}
}
