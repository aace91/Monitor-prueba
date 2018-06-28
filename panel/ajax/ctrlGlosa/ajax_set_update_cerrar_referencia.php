<?php
include_once('./../../../checklogin.php');
require('./../../../connect_casa.php');

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
		$sIdUsuario = $_POST['sIdUsuario'];

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
			$consulta = "SELECT FIRST 1 COUNT(a.NUM_REFE) AS NREF
						 FROM GAB_GLOSA a
						 WHERE a.NUM_REFE='".$sIdReferencia."'";
			
			$result = odbc_exec($odbccasa, $consulta);
			if ($result == false){ 
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']="Error al consultar a la tabla GAB_GLOSA del sistema CASA ";
				$respuesta['Error'] = '['.odbc_error($odbccasa).']';
			} else { 
				$nTotRows = 0;
				while(odbc_fetch_row($result)) { 
					$nTotRows = odbc_result($result,"NREF");
				}

				if ($nTotRows > 0) { 
					$consulta = "UPDATE GAB_GLOSA
					         	 SET LISTO=CURRENT_TIMESTAMP
					         	 WHERE NUM_REFE='".$sIdReferencia."'";

					$trans=ibase_trans("IBASE_WRITE",$dbh);
					$sth = ibase_query($trans, $consulta);
					ibase_commit($trans);

					if (!$trans) { 
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = "Error al cerrar la referencia [".$sIdReferencia."] en el sistema CASA.";
						$respuesta['Error'] = ' ['.ibase_errmsg().']'.$consulta;
					} else {
						$respuesta['Mensaje']='Se cerro la referencia correctamente!';
					}
				} else {
					$consulta = "INSERT INTO GAB_GLOSA (NUM_REFE, USUARIO, FECHA_ALTA, LISTO)
					             VALUES ('".$sIdReferencia."',
					                     '".$sIdUsuario."',
					                      CURRENT_TIMESTAMP,
					                      CURRENT_TIMESTAMP)";

					$trans=ibase_trans("IBASE_WRITE",$dbh);
					$sth = ibase_query($trans, $consulta);
					ibase_commit($trans);

					if (!$trans) { 
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = "Error al cerrar la referencia [".$sIdReferencia."] en la tabla GAB_GLOSA del sistema CASA.";
						$respuesta['Error'] = ' ['.ibase_errmsg().']';
					} else {
						$respuesta['Mensaje']='Se cerro la referencia correctamente!';
					}
				}
			}
		}

		/*if ($dbh == false) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al conectarte a la base de datos de CASA ";
			$respuesta['Error'] = '['.ibase_errmsg().']';
		} else {
			$consulta = "UPDATE GAB_GLOSA
			         	 SET LISTO=CURRENT_TIMESTAMP
			         	 WHERE NUM_REFE='".$sIdReferencia."'";

			$trans=ibase_trans("IBASE_WRITE",$dbh);
			$sth = ibase_query($trans, $consulta);
			ibase_commit($trans);

			if (!$trans) { 
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al cerrar la referencia [".$sIdReferencia."] en el sistema CASA.";
				$respuesta['Error'] = ' ['.ibase_errmsg().']'.$consulta;
			} else {
				$respuesta['Mensaje']='Se cerro la referencia correctamente!';
			}
		}*/
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	
	echo json_encode($respuesta);
}