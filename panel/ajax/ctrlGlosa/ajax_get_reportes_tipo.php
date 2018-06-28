<?php
include_once('./../../../checklogin.php');
require('./../../../connect_casa.php');

if($loggedIn == false){
	echo '500';
} else {	
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		$aEjecutivos = [];
		$aClientes = [];
		$aProblemas = [];

		//***********************************************************//

		$consulta = "SELECT a.LOGIN, a.NOMBRE
					 FROM SISSEG_USUARI a
					 WHERE a.GRUPO IS NOT NULL";
		
		$result = odbc_exec($odbccasa, $consulta);
		if ($result!=false){ 
			while(odbc_fetch_row($result)){ 
				$aRow = array(
							'id' =>  odbc_result($result,"LOGIN"),
							'text' => odbc_result($result,"NOMBRE")
						);
				
				array_push($aEjecutivos, $aRow);
			}
		}

		$consulta = "SELECT a.CVE_IMP, a.NOM_IMP
					 FROM CTRAC_CLIENT a";
		
		$result = odbc_exec($odbccasa, $consulta);
		if ($result!=false){ 
			while(odbc_fetch_row($result)){ 
				$aRow = array(
							'id' =>  odbc_result($result,"CVE_IMP"),
							'text' => utf8_encode(odbc_result($result,"NOM_IMP"))
						);
				
				array_push($aClientes, $aRow);
			}
		}

		$consulta = "SELECT a.ID_PROBLEMA, a.PROBLEMA
					 FROM GAB_GLOSA_CAT_PROBLEMAS a
					 WHERE a.ELIMINADO IS NULL OR a.ELIMINADO = 0";
		
		$result = odbc_exec($odbccasa, $consulta);
		if ($result!=false){ 
			while(odbc_fetch_row($result)){ 
				$aRow = array(
							'id' =>  odbc_result($result,"ID_PROBLEMA"),
							'text' => odbc_result($result,"PROBLEMA")
						);
				
				array_push($aProblemas, $aRow);
			}
		}

		$aReporteData = array(
							'aEjecutivos' => $aEjecutivos,
							'aClientes' => $aClientes,
							'aProblemas' => $aProblemas
						);

		$respuesta['aReporteData']=$aReporteData;
	
	echo json_encode($respuesta);
}