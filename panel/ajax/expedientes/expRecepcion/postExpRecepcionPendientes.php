<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	require('./../../../../connect_casa.php');
	
	$baseSql = "SELECT EXTRACT (YEAR FROM a.FEC_ENTR) FECHA, a.NUM_REFE, a.CVE_IMPO, a.IMP_EXPO, a.TIP_PEDI, a.PAT_AGEN,
			                    a.NUM_PEDI, a.ADU_ENTR, a.FIR_REME, a.FEC_ENTR, a.FIR_PAGO, a.FEC_PAGO
				FROM SAAIO_PEDIME a
				WHERE a.FIR_ELEC <> 'DESISTIO' AND
				     (a.FEC_ENTR >= '01.01.2017' AND a.FIR_PAGO is not null) AND 
				      a.NUM_REFE NOT IN (SELECT b.NUM_REFE
					                     FROM GAB_EXPEDIENTES b)
				ORDER BY a.NUM_REFE asc";
	$result = odbc_exec ($odbccasa, $baseSql);
	$datos = array();
	if ($result!=false){ 
		while(odbc_fetch_row($result)){ 
			$aRow = array(
				odbc_result($result,"NUM_REFE"),
				odbc_result($result,"NUM_PEDI"),
				odbc_result($result,"FEC_PAGO"),
				'<center><a href="#" class="editor_pendientes_pedime"><i class="fa fa-check-circle" aria-hidden="true"></i> Recibir</a></center>'
			);
			array_push($datos, $aRow);
		}
	}
	
	$post_data = array(
	  'data' => $datos
	);
	
	echo json_encode ($post_data);
}







