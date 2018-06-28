<?php
include_once('./../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	require('./../../../connect_casa.php');
	
	$baseSql = "SELECT a.ID_PROBLEMA, a.PROBLEMA, a.FECHA_ALTA
				FROM GAB_GLOSA_CAT_PROBLEMAS a
				WHERE a.ELIMINADO IS NULL OR a.ELIMINADO = 0";
	
	$result = odbc_exec($odbccasa, $baseSql);
	$datos = array();
	if ($result!=false){ 
		while(odbc_fetch_row($result)){ 
			$sFecha = date( 'd/m/Y H:i:s', strtotime(odbc_result($result,"FECHA_ALTA")));

			$aRow = array(
				'ID_PROBLEMA'=> odbc_result($result,"ID_PROBLEMA"),
				'PROBLEMA'=> odbc_result($result,"PROBLEMA"),
				'FECHA_ALTA'=> $sFecha
			);
			 
			array_push($datos, $aRow);
		}
	}
	
	$post_data = array(
	  'data' => $datos
	);
	
	echo json_encode ($post_data);
}