<?php
include_once('./../../../checklogin.php');
require('./../../../connect_casa.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sIdReferencia']) && !empty($_POST['sIdReferencia'])) { 
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
	
		$sIdReferencia = $_POST['sIdReferencia'];  


		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$aComentarios = [];
		$sProblemasList = '<option value=""></option>';
		$consulta = "SELECT a.ID_PROBLEMA, a.PROBLEMA
					 FROM GAB_GLOSA_CAT_PROBLEMAS a
					 WHERE a.ELIMINADO IS NULL OR a.ELIMINADO = 0";
		
		$result = odbc_exec($odbccasa, $consulta);
		if ($result!=false){ 
			while(odbc_fetch_row($result)){ 
				$sProblemasList .= '<option value="'.odbc_result($result,"ID_PROBLEMA").'">'.odbc_result($result,"PROBLEMA").'</option>';
			}
		}

		/*************************************************/
		
		$sEjecutivosList = '<option value=""></option>';
		$consulta = "SELECT a.LOGIN, a.NOMBRE
					 FROM SISSEG_USUARI a
					 WHERE a.GRUPO IS NOT NULL";
		
		$result = odbc_exec($odbccasa, $consulta);
		if ($result!=false){ 
			while(odbc_fetch_row($result)){ 
				$sEjecutivosList .= '<option value="'.odbc_result($result,"LOGIN").'">'.odbc_result($result,"NOMBRE").'</option>';
			}
		}
		
		/*************************************************/
		
		$consulta = "SELECT a.ID_PROBLEMA, b.PROBLEMA, a.FECHA_ALTA, a.OBSERVACION, a.NOMBRE_OBSERV
					 FROM GAB_GLOSA_DET a INNER JOIN
					      GAB_GLOSA_CAT_PROBLEMAS b ON a.ID_PROBLEMA = b.ID_PROBLEMA
					 WHERE a.NUM_REFE='".$sIdReferencia."'";
		
		$result = odbc_exec($odbccasa, $consulta);
		if ($result!=false){ 
			while(odbc_fetch_row($result)){ 
				$aRow = array(
							'ct' =>  odbc_result($result,"NOMBRE_OBSERV"),
							'cmt' => odbc_result($result,"OBSERVACION"),
							'dt' =>  date( 'M-d-Y H:i a', strtotime(odbc_result($result,"FECHA_ALTA"))),
							'pb' => odbc_result($result,"PROBLEMA"),
							'ipb' => odbc_result($result,"ID_PROBLEMA"),
							'fa' => date( 'd.m.Y, H:i:s', strtotime(odbc_result($result,"FECHA_ALTA")))
						);
				
				array_push($aComentarios, $aRow);
			}
		}

		$respuesta['aComentarios']=$aComentarios;
		$respuesta['sEjecutivosList']=$sEjecutivosList;
		$respuesta['sProblemasList']=$sProblemasList;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	
	echo json_encode($respuesta);
}