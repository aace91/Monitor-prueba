<?php
require('./../../../connect_casa.php');
require('./../../../connect_dbsql.php');
include_once('./../../../checklogin.php');
if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {		
		
		$referencia = $_POST['referencia'];
		
		$consulta = "SELECT * FROM servicios WHERE referencia = '".$referencia."'";
		
		$query = mysqli_query($cmysqli,$consulta);
		
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar la referencia. ['.$error.']';
		}else{
			$respuesta['Codigo'] = 1;
			$respuesta['nrows'] = mysqli_num_rows($query);
			while($row = mysqli_fetch_array($query)){
				$respuesta['id_relacion'] = $row['id_relacion'];
				$respuesta['remision'] = $row['remision'];
				$respuesta['pedimento'] = $row['pedimento'];
				$respuesta['cliente'] = $row['cliente'];
				$respuesta['cruce'] = $row['cruce'];
				$respuesta['cruce_observaciones'] = $row['cruce_observaciones'];
				$respuesta['flete'] = $row['flete'];
				$respuesta['flete_observaciones'] = $row['flete_observaciones'];
				$respuesta['demoras'] = $row['demoras'];	
				$respuesta['demoras_observaciones'] = $row['demoras_observaciones'];	
				$respuesta['maniobras'] = $row['maniobras'];	
				$respuesta['maniobras_observaciones'] = $row['maniobras_observaciones'];	
				$respuesta['inspeccion'] = $row['inspeccion'];	
				$respuesta['inspeccion_observaciones'] = $row['inspeccion_observaciones'];	
				$respuesta['cove'] = $row['cove'];	
				$respuesta['cove_observaciones'] = $row['cove_observaciones'];	
				$respuesta['servicio_extraordinario'] = $row['servicio_extraordinario'];	
				$respuesta['fecha_servicio_extraordinario'] = $row['fecha_servicio_extraordinario'];	
				$respuesta['otros'] = $row['otros'];	
				$respuesta['otros_observaciones'] = $row['otros_observaciones'];	
			}
			if($respuesta['nrows'] == 0){
				//Regresar pedimento y cliente cuando es nueva
				
				$qCasa = "SELECT a.NUM_PEDI,c.NOM_IMP
							FROM SAAIO_PEDIME a
								INNER JOIN CTRAC_CLIENT c ON
									a.CVE_IMPO = c.CVE_IMP
							WHERE a.NUM_REFE = '".$referencia."'";
						
				$resped = odbc_exec ($odbccasa, $qCasa);
				if ($resped == false){
					$mensaje = "Error al consultar los datos de la referencia. BD.CASA.".odbc_error();
					echo json_encode( array("error" => $mensaje));
					exit(0);
				}else{
					while(odbc_fetch_row($resped)){
						$respuesta['pedimento'] = odbc_result($resped,"NUM_PEDI");
						$respuesta['cliente'] = odbc_result($resped,"NOM_IMP");
					}			
				}
			}
			
		}			
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "458 : Error al recibir los datos de la relacion.";
	}
	echo json_encode($respuesta);
}