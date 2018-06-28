<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {		
		
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
		
		$consulta = "INSERT INTO servicios (
											referencia,
											remision,
											pedimento,
											cliente,
											cruce,
											cruce_observaciones,
											flete,
											flete_observaciones,
											demoras,
											demoras_observaciones,
											maniobras,
											maniobras_observaciones,
											inspeccion,
											inspeccion_observaciones,
											cove,
											cove_observaciones,
											servicio_extraordinario,";
		if($srv_ext == '1')
			$consulta .= "					fecha_servicio_extraordinario,";
		$consulta .= "						otros,
											otros_observaciones,
											id_usuario
										) VALUES (
											'".$referencia."',
											'".$remision."',
											'".$pedimento."',
											'".$cliente."',
											'".$cruce."',
											'".$obs_cruce."',
											'".$flete."',
											'".$obs_flete."',
											'".$demoras."',
											'".$obs_demoras."',
											'".$maniobras."',
											'".$obs_maniobras."',
											'".$inspeccion."',
											'".$obs_inspeccion."',
											'".$cove."',
											'".$obs_cove."',
											'".$srv_ext."',";
		if($srv_ext == '1')
			$consulta .= "					'".$fecha_srv_ext."',";
		$consulta .= "						'".$otros."',
											'".$obs_otros."',
											".$id.")";
		
		$query = mysqli_query($cmysqli,$consulta);
		
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al guardar la relacion de servicios. ['.$error.']';
		}else{
			$respuesta['Codigo']=1;
			$respuesta['Mensaje']='La relacion de servicios de ha guardado correctamente!.';
			$respuesta['id_relacion'] = mysqli_insert_id($cmysqli);;
		}			
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "458 : Error al recibir los datos de la relacion.";
	}
	echo json_encode($respuesta);
}