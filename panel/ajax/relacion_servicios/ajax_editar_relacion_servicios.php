<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['id_relacion']) && !empty($_POST['id_relacion'])) {
		
		$id_relacion = $_POST['id_relacion'];
		$referencia = $_POST['referencia'];
		$remision = $_POST['remision'];
		$pedimento = $_POST['pedimento'];
		$cliente = $_POST['cliente'];
		$cruce = $_POST['cruce'];
		$flete = $_POST['flete'];
		$demoras = $_POST['demoras'];
		$maniobras = $_POST['maniobras'];
		$inspeccion = $_POST['inspeccion'];
		$cove = $_POST['cove'];
		$srv_ext = $_POST['srv_ext'];
		$otros = $_POST['otros'];
		$obs_cruce = $_POST['obs_cruce'];
		$obs_flete = $_POST['obs_flete'];
		$obs_demoras = $_POST['obs_demoras'];
		$obs_maniobras = $_POST['obs_maniobras'];
		$obs_inspeccion = $_POST['obs_inspeccion'];
		$obs_cove = $_POST['obs_cove'];
		
		$fecha_srv_ext = $_POST['fecha_srv_ext'];
		$obs_otros = $_POST['obs_otros'];
		
		$consulta = "UPDATE servicios SET   referencia='".$referencia."',
											remision='".$remision."',
											pedimento='".$pedimento."',
											cliente='".$cliente."',
											cruce='".$cruce."',
											cruce_observaciones='".$obs_cruce."',
											flete='".$flete."',
											flete_observaciones='".$obs_flete."',
											demoras='".$demoras."',
											demoras_observaciones='".$obs_demoras."',
											maniobras='".$maniobras."',
											maniobras_observaciones='".$obs_maniobras."',
											inspeccion='".$inspeccion."',
											inspeccion_observaciones='".$obs_inspeccion."',
											cove='".$cove."',
											cove_observaciones='".$obs_cove."',
											servicio_extraordinario='".$srv_ext."',";
		if($srv_ext == '1')
			$consulta .= "					fecha_servicio_extraordinario='".$fecha_srv_ext."',";
		$consulta .= "						otros='".$otros."',
											otros_observaciones='".$obs_otros."'
					WHERE id_relacion=".$id_relacion;
		
		$query = mysqli_query($cmysqli,$consulta);
		
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al guardar la relacion de servicios. ['.$error.']';
		}else{
			$respuesta['Codigo']=1;
			$respuesta['Mensaje']='La relacion de servicios de ha guardado correctamente!.';
			$respuesta['id_relacion'] = mysqli_insert_id($cmysqli);
		}			
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "458 : Error al recibir los datos de la relacion.";
	}
	echo json_encode($respuesta);
}