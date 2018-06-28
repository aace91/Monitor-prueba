<?php
include_once('./../../../checklogin.php');
include('./../../../connect_dbsql.php');

$sURLPermisosImpo = "https://www.delbravoweb.com/archivos/monitor/permisosImpo/";

if ($loggedIn == false){
	echo '500';
}else{		
	if (isset($_POST['sIdPermiso']) && !empty($_POST['sIdPermiso'])) {
		$respuesta['Codigo'] = 1;	
		
		$sIdPermiso = $_POST['sIdPermiso'];
		
		//***********************************************************//
		
		$aFracciones = array();
		
		//***********************************************************//
		
		$consulta = "SELECT p.numero_permiso,
							p.id_cliente,
							p.fecha_vigencia_ini,
							p.fecha_vigencia_fin, 
							IF(p.archivo_permiso IS NULL, '',CONCAT('".$sURLPermisosImpo."',p.archivo_permiso)) archivo_permiso
					 FROM permisos_pedimentos_impo p
					 WHERE p.id_permiso = ".$sIdPermiso;
		
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar numero de permiso. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			if(mysqli_num_rows($query) > 0){
				while($row = mysqli_fetch_array($query)){
					$respuesta['numero_permiso'] = $row['numero_permiso'];
					$respuesta['id_cliente'] = $row['id_cliente'];
					$respuesta['fecha_ini'] = date_format(new DateTime($row['fecha_vigencia_ini']),"d/m/Y");
					$respuesta['fecha_fin'] = date_format(new DateTime($row['fecha_vigencia_fin']),"d/m/Y");
					$respuesta['documento'] = $row['archivo_permiso'];
					
					break;
				}
			} else {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar el permiso. [permiso o cliente NO EXISTE]';
				$respuesta['Error'] = '';
			}
		}
		
		if ($respuesta['Codigo'] == 1) {
			$consulta = "SELECT id_permiso, nombre, cantidad, cantidad_delbravo, unidad, fraccion
						 FROM bodega.permisos_pedimentos_impo_det
						 WHERE id_permiso=".$sIdPermiso;
			
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar las fracciones. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else {
				while($row = mysqli_fetch_object($query)){
					$aRow = array(
						'id_permiso' => ((is_null($row->id_permiso))? '': $row->id_permiso),
						'nombre' => ((is_null($row->nombre))? '': $row->nombre),
						'cantidad' => ((is_null($row->cantidad))? '': $row->cantidad),
						'cantidad_delbravo' => ((is_null($row->cantidad_delbravo))? '': $row->cantidad_delbravo),
						'unidad' => ((is_null($row->unidad))? '': get_unidad_medida($row->unidad)),
						'fraccion' => ((is_null($row->fraccion))? '': $row->fraccion)
					);
					
					array_push($aFracciones, $aRow);
				}
			}
		}
		
		$respuesta['aFracciones']=$aFracciones;
	}else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}

function get_unidad_medida($nUnidad) {
	include('./../../../connect_casa.php');
	
	$sUnidadDesc = '';
	
	$consulta="SELECT a.NUM_UNI, a.DES_UNI
			   FROM CTARC_UNIDAD a
			   WHERE a.NUM_UNI = ".$nUnidad;
			   
	$resp = odbc_exec ($odbccasa, $consulta) or die(odbc_error());
	if ($resp == false){
		$sUnidadDesc = odbc_error();
	} else {
		while(odbc_fetch_row($resp)){ 
			$sUnidadDesc = odbc_result($resp,"DES_UNI");
			break;
		}
	}
	
	return $sUnidadDesc;
}
	