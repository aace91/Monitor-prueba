<?php
include_once('./../../../checklogin.php');
require('./../../../connect_casa.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sReferencia']) && !empty($_POST['sReferencia'])) { 
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
	
		$sReferencia = $_POST['sReferencia']; 

		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		$bExisteMysql = false;
		$bExisteCasa = false;
		$sClavePedimento = '';

		//***********************************************************//
		
		$consulta = "SELECT a.NUM_REFE, a.CVE_PEDI
					 FROM SAAIO_PEDIME a 
					 WHERE a.NUM_REFE='".$sReferencia."' AND 
					       a.IMP_EXPO = '2' AND
					       a.CVE_IMPO = 'STERIS'";
		
		$result = odbc_exec($odbccasa, $consulta);
		if ($result==false){ 
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar informaci&oacute;n en sistema CASA. Por favor contacte al administrador del sistema.";
			$respuesta['Error'] = ' ['.odbc_error().']';
		} else {
			while (odbc_fetch_row($result)) { 
				$bExisteCasa = true;
				$sClavePedimento = odbc_result($result,"CVE_PEDI");
				break;
			}
		}

		if ($respuesta['Codigo'] == 1) {
			$consulta = "SELECT referencia
						 FROM bodega.expos_plantilla_gral
						 WHERE referencia='".$sReferencia."' AND fecha_del IS NULL";

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar la referencia en plantilla general. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else { 
				while($row = mysqli_fetch_array($query)){
					$bExisteMysql = true;
					break;
				}
			}
		}

		$respuesta['bExisteCasa']=$bExisteCasa;
		$respuesta['bExisteMysql']=$bExisteMysql;
		$respuesta['sClavePedimento']=$sClavePedimento;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	
	echo json_encode($respuesta);
}