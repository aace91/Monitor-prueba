<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['Id_Solicitud']) && !empty($_POST['Id_Solicitud'])) {  
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$Id_Solicitud = $_POST['Id_Solicitud'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		$aComentarios = array();
		
		//***********************************************************//
		
		$consulta = "SELECT a.de,
							a.comentario,
							a.fecha
					 FROM tiempo_extra_comentarios AS a
					 WHERE a.id_solicitud=".$Id_Solicitud."
					 ORDER BY a.fecha";
		
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los comentarios. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			while ($row = mysqli_fetch_array($query)){
				$aRow = array(
							'ct' =>  $row["de"],
							'cmt' => $row["comentario"],
							'dt' =>  date( 'M-d-Y H:i a', strtotime($row["fecha"]))
						);
				
				array_push($aComentarios, $aRow);
			}
		}		
		
		if ($respuesta['Codigo'] == 1) {
			$consulta = "UPDATE tiempo_extra
						 SET ejecutivo_ultima_vista='".$fecha_registro."'
						 WHERE id_solicitud=".$Id_Solicitud;

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al actualizar ultima vista. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}
		}
		
		$respuesta['aComentarios']=$aComentarios;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}