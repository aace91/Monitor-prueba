<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sIdFolio']) && !empty($_POST['sIdFolio'])) {  
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$sIdFolio = $_POST['sIdFolio'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		$aComentarios = [];
		
		//***********************************************************//
		
		$consulta = "SELECT b.Nom AS nombre_cliente,
		                    c.nombre AS nombre_logistica,
							d.usunombre AS nombre_ejecutivo,
							e.nombre AS nombre_consignatario,
						    a.comentario,
							a.fecha
					 FROM bodega.expos_seguimiento_comments AS a LEFT JOIN  
					      bodega.clientes AS b ON a.id_cliente = b.Cliente_id LEFT JOIN
						  bodega.accesoslogist AS c ON c.id_logistica = a.logistica LEFT JOIN
                          bodega.tblusua AS d ON d.Usuario_id = a.ejecutivo LEFT JOIN
                          bodega.accesosconsign AS e ON e.id_consignatario = a.consignatario
					 WHERE a.id_folio=".$sIdFolio."
					 ORDER BY a.fecha";
		
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los comentarios. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			while ($row = mysqli_fetch_array($query)){
				$sNombre = '';
				if (is_null($row["nombre_cliente"]) == false) {
					$sNombre = $row["nombre_cliente"];
				}
				
				if (is_null($row["nombre_logistica"]) == false) {
					$sNombre = $row["nombre_logistica"];
				}
				
				if (is_null($row["nombre_ejecutivo"]) == false) {
					$sNombre = $row["nombre_ejecutivo"];
				}

				if (is_null($row["nombre_consignatario"]) == false) {
					$sNombre = $row["nombre_consignatario"];
				}
				
				$aRow = array(
							'ct' =>  $sNombre,
							'cmt' => $row["comentario"],
							'dt' =>  date( 'M-d-Y H:i a', strtotime($row["fecha"]))
						);
				
				array_push($aComentarios, $aRow);
			}
		}		
		
		if ($respuesta['Codigo'] == 1) {
			$consulta = "UPDATE bodega.expos_seguimiento
						 SET ejecutivo_ultima_vista='".$fecha_registro."'
						 WHERE id_folio=".$sIdFolio;

			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al actualizar ultima vista. Por favor contacte al administrador del sistema.'.$consulta; 
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