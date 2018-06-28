<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sIdSalida']) && !empty($_POST['sIdSalida'])) {  
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$sIdSalida = $_POST['sIdSalida'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		$aComentarios = [];
		
		//***********************************************************//
		
		$consulta = "SELECT b.Nom, a.comentario, a.fecha
                     FROM bodega.expos_salidas_coments AS a LEFT JOIN  
                          bodega.clientes AS b ON a.id_cliente = b.Cliente_id
					 WHERE a.id_salida=".$sIdSalida."
					 ORDER BY a.fecha";
		
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los comentarios. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			while ($row = mysqli_fetch_array($query)){
				$aRow = array(
							'ct' =>  $row["Nom"],
							'cmt' => $row["comentario"],
							'dt' =>  date( 'M-d-Y H:i a', strtotime($row["fecha"]))
						);
				
				array_push($aComentarios, $aRow);
			}
		}		
		
		$respuesta['aComentarios']=$aComentarios;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}