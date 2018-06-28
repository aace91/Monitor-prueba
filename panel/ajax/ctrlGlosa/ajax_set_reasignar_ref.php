<?php
include_once('./../../../checklogin.php');

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
		$sNuevaRefefencia = $_POST['sNuevaRefefencia'];  

		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
			
		//***********************************************************//

					
		$dbh = ibase_connect($host, $username, $password);
		if ($dbh == false) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al conectarte a la base de datos de CASA ";
			$respuesta['Error'] = '['.ibase_errmsg().']';
		} else {
			$consulta = "UPDATE GAB_GLOSA
			             SET NUM_REFE='".$sNuevaRefefencia."'
			             WHERE NUM_REFE='".$sIdReferencia."'";

			$trans=ibase_trans("IBASE_WRITE",$dbh);
			$sth = ibase_query($trans, $consulta);
			ibase_commit($trans);

			if (!$trans) { 
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al actualizar la referencia [".$sProblema."] en el sistema CASA.";
				$respuesta['Error'] = ' ['.ibase_errmsg().']'.$consulta;
			} else {
				$consulta = "UPDATE GAB_GLOSA_DET
							 SET NUM_REFE='".$sNuevaRefefencia."'
							 WHERE NUM_REFE='".$sIdReferencia."'";

				$trans=ibase_trans("IBASE_WRITE",$dbh);
				$sth = ibase_query($trans, $consulta);
				ibase_commit($trans);

				if (!$trans) { 
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "Error al actualizar la referencia [".$sProblema."] en el sistema CASA.";
					$respuesta['Error'] = ' ['.ibase_errmsg().']'.$consulta;
				} else {
					$respuesta['Mensaje']='Referencia actualizada correctamente!';
				}
			}
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	
	echo json_encode($respuesta);
}