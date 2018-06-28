<?php
include_once('./../../../checklogin.php');
include('./../../../connect_casa.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {  
		$respuesta['Codigo']=1;
		$referencia = $_POST['referencia'];
		
		$consulta = "SELECT a.NUM_REFE,a.ADU_DESP,a.PAT_AGEN,a.NUM_PEDI,
							f.NUM_FACT,b.CONS_FACT,b.CONS_PART,b.NUM_PART,b.FRACCION,b.DES_MERC
						FROM SAAIO_PEDIME a
							INNER JOIN SAAIO_FACTUR f ON
								a.NUM_REFE = f.NUM_REFE 
							INNER JOIN SAAIO_FACPAR b ON
								a.NUM_REFE = b.NUM_REFE AND
								f.CONS_FACT = b.CONS_FACT
						WHERE a.NUM_REFE = '".$referencia."'";
						
		$resped = odbc_exec ($odbccasa, $consulta);
		if ($resped == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar la informacion de los pedimentos. [DB.CASA.'.$consulta.']'.odbc_error();
			exit(json_encode($respuesta));
		}
		$aPartidas = array();
		while(odbc_fetch_row($resped)){
			
			$respuesta['NUM_REFE'] = odbc_result($resped,"NUM_REFE");
			$respuesta['ADU_DESP'] = odbc_result($resped,"ADU_DESP");
			$respuesta['PAT_AGEN'] = odbc_result($resped,"PAT_AGEN");
			$respuesta['NUM_PEDI'] = odbc_result($resped,"NUM_PEDI");
			
			$NUM_FACT = odbc_result($resped,"NUM_FACT");
			$CONS_FACT = odbc_result($resped,"CONS_FACT");
			$CONS_PART = odbc_result($resped,"CONS_PART");
			$NUM_PART = odbc_result($resped,"NUM_PART");
			$FRACCION = odbc_result($resped,"FRACCION");
			$DES_MERC = odbc_result($resped,"DES_MERC");
			
			$aPAR = array($NUM_FACT,$CONS_PART,$NUM_PART,$FRACCION,$DES_MERC);
			
			array_push($aPartidas,$aPAR);
		}
		if(count($aPartidas) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'No existe informacion para la referencia seleccionada. '.$consulta;
			exit(json_encode($respuesta));
		}
		$respuesta['aPartidas'] = $aPartidas;
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
