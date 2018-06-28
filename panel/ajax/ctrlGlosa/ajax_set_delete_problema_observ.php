<?php
include_once('./../../../checklogin.php');

$sUserInsert = $username;

$host = '192.168.1.107:E:\CASAWIN\CSAAIWIN\Datos\CASA.GDB'; 
$username='SYSDBA';
$password='masterkey';

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sIdReferencia']) && !empty($_POST['sIdReferencia'])) { 
		$respuesta['Codigo'] = 1;

		//***********************************************************//
	
		$sIdReferencia = $_POST['sIdReferencia'];
		$sObservIdProblema = $_POST['sObservIdProblema'];
		$sObservFecha = $_POST['sObservFecha'];

		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//

		$dbh = ibase_connect($host, $username, $password);
		if ($dbh == false) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al conectarte a la base de datos de CASA ";
			$respuesta['Error'] = '['.ibase_errmsg().']';
		}

		if ($respuesta['Codigo'] == 1) { 
			$consulta = "DELETE FROM GAB_GLOSA_DET
						 WHERE NUM_REFE='".$sIdReferencia."' AND 
						       ID_PROBLEMA=".$sObservIdProblema." AND
						       FECHA_ALTA >= '".$sObservFecha.".000' AND FECHA_ALTA <= '".$sObservFecha.".999'";

			$trans=ibase_trans("IBASE_WRITE",$dbh);
			$sth = ibase_query($trans, $consulta);
			ibase_commit($trans);

			if (!$trans) { 
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al eliminar la observaci&oacute;n en la tabla GAB_GLOSA_DET del sistema CASA.";
				$respuesta['Error'] = ' ['.ibase_errmsg().']';
			} else {
				$respuesta['Mensaje']='Se elimino la observaci&oacute;n correctamente!';
			}
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	
	echo json_encode($respuesta);
}