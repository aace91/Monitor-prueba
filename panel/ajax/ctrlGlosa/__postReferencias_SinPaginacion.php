<?php
include_once('./../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	require('./../../../connect_casa.php');
	
	$sFiltro = $_POST['sFiltro'];

	$baseSql = '';
	if ($sFiltro == 'sin_pagar') {
		$baseSql = "SELECT a.NUM_REFE, b.FECHA_ALTA, b.USUARIO, d.NOMBRE AS EJECUTIVO, a.CVE_IMPO, e.NOM_IMP,
					       (SELECT COUNT(c.NUM_REFE)
					        FROM GAB_GLOSA_DET c
					        WHERE c.NUM_REFE = a.NUM_REFE) AS OBSERVACIONES
					FROM SAAIO_PEDIME a LEFT join
					     GAB_GLOSA b ON a.NUM_REFE = b.NUM_REFE LEFT JOIN
    					 SISSEG_USUARI d ON b.USUARIO = d.LOGIN LEFT JOIN
     			 		 CTRAC_CLIENT e ON a.CVE_IMPO = e.CVE_IMP
					WHERE (a.FEC_ENTR >= '01.03.2017' AND a.NUM_PEDI is not null
					      AND (a.FIR_REME is not null OR a.FIR_PAGO is not null))";

					/*WHERE (a.FEC_ENTR >= '01.03.2017' AND a.CVE_PEDI<>'V1' AND a.NUM_PEDI is not null
					      AND (a.TIP_PEDI <> 'R1' OR a.TIP_PEDI is null)
					      AND (a.FIR_REME is not null OR a.FIR_PAGO is not null))*/
	} else {
		$baseSql = "SELECT a.NUM_REFE, b.FECHA_ALTA, b.USUARIO, d.NOMBRE AS EJECUTIVO, a.CVE_IMPO, e.NOM_IMP,
					       (SELECT COUNT(c.NUM_REFE)
					        FROM GAB_GLOSA_DET c
					        WHERE c.NUM_REFE = a.NUM_REFE) AS OBSERVACIONES
					FROM SAAIO_PEDIME a LEFT join
					     GAB_GLOSA b ON a.NUM_REFE = b.NUM_REFE LEFT JOIN
     					 SISSEG_USUARI d ON b.USUARIO = d.LOGIN LEFT JOIN
     					 CTRAC_CLIENT e ON a.CVE_IMPO = e.CVE_IMP
					WHERE (a.FEC_ENTR >= '01.03.2017' AND a.NUM_PEDI is not null)";

					/*WHERE (a.FEC_ENTR >= '01.03.2017' AND a.CVE_PEDI<>'V1' AND a.NUM_PEDI is not null
					      AND (a.TIP_PEDI <> 'R1' OR a.TIP_PEDI is null))";*/
	}
	

	$result = odbc_exec ($odbccasa, $baseSql);
	$datos = array();
	if ($result!=false){ 
		while(odbc_fetch_row($result)){ 
			if (is_null(odbc_result($result,"FECHA_ALTA"))) {
				$sFecha = '';
			} else {
				$sFecha = date( 'd/m/Y H:i:s', strtotime(odbc_result($result,"FECHA_ALTA")));	
			}

			$aRow = array(
				'NUM_REFE'=> odbc_result($result,"NUM_REFE"),
				'FECHA_ALTA'=> $sFecha,
				'OBSERVACIONES'=> odbc_result($result,"OBSERVACIONES"),
				'USUARIO'=> odbc_result($result,"USUARIO"),
				'EJECUTIVO' => odbc_result($result,"EJECUTIVO"),
				'CLIENTE' => odbc_result($result,"NOM_IMP")
			);
			
			array_push($datos, $aRow);
		}
	}
	
	$post_data = array(
	  'data' => $datos
	);
	
	echo json_encode ($post_data);
}







