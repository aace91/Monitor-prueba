<?php
include_once('./../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	require('./../../../connect_casa.php');
	
	$sReferencia = $_POST['sReferencia'];

	/********************************************************/

	$datos = array();

	if ($sReferencia != "") {
		$baseSql = "SELECT a.NUM_REFE, b.FECHA_ALTA, b.USUARIO, d.NOMBRE AS EJECUTIVO, a.CVE_IMPO, e.NOM_IMP, b.LISTO,
					       (SELECT COUNT(c.NUM_REFE)
					        FROM GAB_GLOSA_DET c
					        WHERE c.NUM_REFE = a.NUM_REFE) AS OBSERVACIONES
					FROM SAAIO_PEDIME a LEFT join
					     GAB_GLOSA b ON a.NUM_REFE = b.NUM_REFE LEFT JOIN
						 SISSEG_USUARI d ON b.USUARIO = d.LOGIN LEFT JOIN
	 			 		 CTRAC_CLIENT e ON a.CVE_IMPO = e.CVE_IMP
					WHERE a.NUM_REFE LIKE '%".$sReferencia."%'";
		
		$result = odbc_exec ($odbccasa, $baseSql);
		
		if ($result!=false){ 
			while(odbc_fetch_row($result)){ 
				if (is_null(odbc_result($result,"FECHA_ALTA"))) {
					$sFecha = '';
				} else {
					$sFecha = date( 'd/m/Y H:i', strtotime(odbc_result($result,"FECHA_ALTA")));	
				}

				$aRow = array(
					'NUM_REFE'=> odbc_result($result,"NUM_REFE"),
					'FECHA_ALTA'=> $sFecha,
					'OBSERVACIONES'=> utf8_encode(odbc_result($result,"OBSERVACIONES")),
					'USUARIO'=> utf8_encode(odbc_result($result,"USUARIO")),
					'EJECUTIVO' => utf8_encode(odbc_result($result,"EJECUTIVO")),
					'CLIENTE' => utf8_encode(odbc_result($result,"NOM_IMP")),
					'LISTO' => utf8_encode(odbc_result($result,"LISTO"))
				);
				
				array_push($datos, $aRow);
			}
		}
	}
	
	$post_data = array(
	  'data' => $datos
	);
	
	echo json_encode ($post_data);
}







