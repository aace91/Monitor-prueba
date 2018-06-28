<?php
include_once('./../../../checklogin.php');
require('./../../../connect_exp.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['sIdSitPedime']) && !empty($_POST['sIdSitPedime'])) {  
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$sIdSitPedime = $_POST['sIdSitPedime'];
		
		//***********************************************************//
		
		$sTable = '';
		
		//***********************************************************//
		
		$consulta = "
			SELECT a.id_estado, b.descripcion, a.id_estado_detalle, 
				   (SELECT z.descripcion
					FROM casa.soia_estados AS z
					WHERE z.id_estado = a.id_estado_detalle
					LIMIT 1) AS detalle, 
				   a.fecha
			FROM casa.soia_eventos AS a INNER JOIN
				 casa.soia_estados AS b ON b.id_estado = a.id_estado
			WHERE a.id_sit_pedime=".$sIdSitPedime."   
			GROUP BY a.id_estado, a.id_estado_detalle
			ORDER BY a.id_evento";

		//$respuesta['consulta']=$consulta;
		$query = mysqli_query($cmysqli_exp, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['codigo']=-1;
			$respuesta['mensaje']='Error al consultar detalles del estatus: ' .$error;
		} else {
			while($row = mysqli_fetch_array($query)){
				$nEstadoDet = $row['id_estado_detalle'];
				$sColumna = $row['detalle'];
				
				if ($nEstadoDet == 310 || $nEstadoDet == 510){
					$sColumna = '<span class="label label-danger">'.$sColumna.'</span>';
				}
				
				if ($nEstadoDet == 320 || $nEstadoDet == 520){ 
					$sColumna = '<span class="label label-success">'.$sColumna.'</span>';
				}
				
				$sTable .= '
					<tr>
						<td>'.$row['descripcion'].'</td>
						<td>'.$sColumna.'</td>
						<td>'.date('m/d/Y H:i:s', strtotime($row['fecha'])).'</td>
					</tr>';
			}
			
			$sTable = '
				<div class="col-xs-12">
					<table class="table table-bordered table-striped table-hover">
						<thead>
							<tr>
								<th>Situaci&oacute;n</th>
								<th>Detalle De La Situaci&oacute;n</th>
								<th>Fecha</th>
							</tr>
						</thead>
						<tbody>
							'.$sTable.'
						</tbody>
					</table>
				</div>';
		}

		$respuesta['sTable']=$sTable;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);	
}
?>