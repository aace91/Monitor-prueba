<?php
include_once('./../../../checklogin.php');

$host = '192.168.1.107:E:\CASAWIN\CSAAIWIN\Datos\CASA.GDB'; 
$username='SYSDBA';
$password='masterkey';

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sProblema']) && !empty($_POST['sProblema'])) { 
		$respuesta['Codigo'] = 1;

		//***********************************************************//
	
		$sProblema = $_POST['sProblema'];

		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
			
		//***********************************************************//

					
		$dbh = ibase_connect($host, $username, $password);
		if ($dbh == false) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al conectarte a la base de datos de CASA ";
			$respuesta['Error'] = '['.ibase_errmsg().']';
		} else {
			$consulta = "INSERT INTO GAB_GLOSA_CAT_PROBLEMAS (ID_PROBLEMA, PROBLEMA, FECHA_ALTA) VALUES (
						    (SELECT FIRST 1 (a.ID_PROBLEMA + 1) AS ID
						     FROM GAB_GLOSA_CAT_PROBLEMAS a
							 ORDER BY a.ID_PROBLEMA DESC), '".$sProblema."', CURRENT_TIMESTAMP)";

			$trans=ibase_trans("IBASE_WRITE",$dbh);
			$sth = ibase_query($trans, $consulta);
			ibase_commit($trans);

			if (!$trans) { 
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al grabar el Problema [".$sProblema."] en el sistema CASA.";
				$respuesta['Error'] = ' ['.ibase_errmsg().']'.$consulta;
			} else {
				$respuesta['Mensaje']='Se grabo la informaci&oacute;n correctamente!';
			}
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	
	echo json_encode($respuesta);
}