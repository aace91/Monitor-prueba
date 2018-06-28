<?php
include_once('./../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	require('./../../../connect_casa.php');
	
	$bReporteGenerar = $_POST['bReporteGenerar'];
	$sFechaInicio = $_POST['sFechaInicio'];
	$sFechaFin = $_POST['sFechaFin'];
	$sTipo = $_POST['sTipo'];
	$sTipoOpt2 = $_POST['sTipoOpt2'];

	$sFechaInicio = date( 'd.m.Y', strtotime($sFechaInicio));
	$sFechaFin = date( 'd.m.Y', strtotime($sFechaFin));

	$bReporteGenerar = ($bReporteGenerar === 'true'? true: false);
	/******************************************************/

	$datos = array();
	$bEntro =  false;
	if ($bReporteGenerar) {
		$bEntro = true;
		$baseSql = "SELECT a.NUM_REFE,
					       b.FECHA_ALTA,
					       CASE a.IMP_EXPO
					           WHEN 1 THEN 'IMP'
					           WHEN 2 THEN 'EXP'
					           ELSE 'NA'
					       END || '/' || a.CVE_PEDI AS IMP_EXPO,
					       a.ADU_ENTR,
					       a.NUM_PEDI,
					       a.CVE_IMPO, e.NOM_IMP AS CLIENTE,
					       CASE a.IMP_EXPO
					           WHEN 1 THEN (SELECT FIRST 1 z.NOM_PRO
					                        FROM SAAIO_FACTUR c LEFT JOIN
					                             CTRAC_PROVED z ON c.CVE_PROV = z.CVE_PRO
					                        WHERE c.NUM_REFE = a.NUM_REFE)
					           WHEN 2 THEN (SELECT FIRST 1 z.NOM_PRO
					                        FROM SAAIO_FACTUR c LEFT JOIN
					                             CTRAC_DESTIN z ON c.CVE_PROV = z.CVE_PRO
					                        WHERE c.NUM_REFE = a.NUM_REFE)
					           ELSE 'NA'
					       END AS NOM_PRO,
					       /*(SELECT FIRST 1 z.NOM_PRO
					        FROM SAAIO_FACTUR c LEFT JOIN
					             CTRAC_PROVED z ON c.CVE_PROV = z.CVE_PRO
					        WHERE c.NUM_REFE = a.NUM_REFE),*/
					       b.USUARIO, d.NOMBRE AS EJECUTIVO,
					       (SELECT LIST('<strong>' || x.PROBLEMA || ':</strong> ' || y.OBSERVACION, ', <br>')
					        FROM GAB_GLOSA_DET y LEFT JOIN
					             GAB_GLOSA_CAT_PROBLEMAS x ON y.ID_PROBLEMA = x.ID_PROBLEMA
					        WHERE y.NUM_REFE = a.NUM_REFE) AS OBSERVACIONES
					FROM GAB_GLOSA b LEFT JOIN
					     SAAIO_PEDIME a ON b.NUM_REFE = a.NUM_REFE LEFT JOIN
					     SISSEG_USUARI d ON b.USUARIO = d.LOGIN LEFT JOIN
					     CTRAC_CLIENT e ON a.CVE_IMPO = e.CVE_IMP
					WHERE (b.FECHA_ALTA >= '".$sFechaInicio." 00:00:00' AND b.FECHA_ALTA <= '".$sFechaFin." 23:59:59')";

		switch ($sTipo) {
			case 'ejecutivo':
				$baseSql .= "AND b.USUARIO = '".$sTipoOpt2."'";
				break;
			
			case 'cliente':
				$baseSql .= "AND a.CVE_IMPO = '".$sTipoOpt2."'";
				break;

			case 'regimen':
				$baseSql .= "AND a.CVE_PEDI = '".$sTipoOpt2."'";
				break;

			case 'impo_expo':
				$baseSql .= "AND a.IMP_EXPO = '".$sTipoOpt2."'";
				break;

			case 'problema':
				$baseSql .= "AND b.NUM_REFE IN (SELECT v.NUM_REFE
						                        FROM GAB_GLOSA_DET v LEFT JOIN
						                             GAB_GLOSA_CAT_PROBLEMAS w ON v.ID_PROBLEMA = w.ID_PROBLEMA
						                        WHERE w.ID_PROBLEMA = ".$sTipoOpt2.")";
				break;

			default:				
				break;
		}


		$result = odbc_exec ($odbccasa, $baseSql);
		if ($result!=false){ 
			while(odbc_fetch_row($result)){ 
				if (is_null(odbc_result($result,"FECHA_ALTA"))) {
					$sFecha = '';
				} else {
					$sFecha = date( 'd-m-Y', strtotime(odbc_result($result,"FECHA_ALTA")));	
				}

				$aRow = array(
					'FECHA'=> $sFecha,
					'EJECUTIVO'=> odbc_result($result,"EJECUTIVO"),
					'OPERACION'=> odbc_result($result,"IMP_EXPO"),
					'ADUANA'=> odbc_result($result,"ADU_ENTR"),
					'PEDIMENTO'=> odbc_result($result,"NUM_PEDI"),
					'CLIENTE' => utf8_encode(odbc_result($result,"CLIENTE")),
					'PROVEEDOR' => utf8_encode(odbc_result($result,"NOM_PRO")),
					'ERROR' => utf8_encode(odbc_result($result,"OBSERVACIONES"))
				);
				
				array_push($datos, $aRow);
			}
		}
	}
	
	$post_data = array(
	  'data' => $datos,
	  'entro' => $bEntro,
	  'bandera' => $bReporteGenerar
	);
	
	echo json_encode ($post_data);
}