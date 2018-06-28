<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('./../../../connect_casa.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['cliente']) && !empty($_POST['cliente'])) {
		$respuesta['Codigo']=1;
		$referencia = $_POST['referencia'];
		$cliente = $_POST['cliente'];
		
		//Caso de rectificacion
		if ($referencia != '') {
			//global $odbccasa;
			
			/*$host = '192.168.1.107:E:\CASAWIN\CSAAIWIN\Datos\CASA.GDB'; 
			$username='SYSDBA'; 
			$password='masterkey';		
			$dbh = ibase_connect($host, $username, $password);*/
			
			$ref_original =  strtoupper($referencia);
			$ref_rectificacion = substr($ref_original, 2, strlen($ref_original) - 1);
			$consulta = "SELECT (SELECT COUNT(b.NUM_REFE) + 1 AS RECTIFICACION
								 FROM SAAIO_PEDIME b
								 WHERE b.NUM_REFE LIKE '".$ref_rectificacion."-%' AND 
									   b.TIP_PEDI='R1')
						 FROM SAAIO_PEDIME a
						 WHERE a.NUM_REFE = '".$ref_original."'";
						 
			$query = odbc_exec($odbccasa, $consulta);
			if ($query==false){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "No se pudo consultar la referencia ".$ref_original;
				$respuesta['Error'] = "";
			} else {
				if(odbc_num_rows($query)<=0){ 
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "La referencia ".$ref_original." no existe en CASA.";
					$respuesta['Error'] = "";
				} else {
					while(odbc_fetch_row($query)){ 
						$ref_rectificacion = $ref_rectificacion.'-R'.odbc_result($query,"RECTIFICACION");
						$fecharegistro =  date("Y-m-d H:i:s");
						$consulta = "INSERT INTO entradas_expo (referencia,referencia_original,fecha,numcliente,NombreCliente) 
											VALUES ('".$ref_rectificacion."','".$ref_original."','".$fecharegistro."','".$cliente."',(SELECT cnombre FROM cltes_expo WHERE gcliente = '".$cliente."'))";
						$query= mysqli_query($cmysqli,$consulta);
				
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = "Error al generar referencia de exportacion. [Referencia Original:".$ref_original.']'.$consulta;
							$respuesta['Error'] = $error;
						}else{
							$respuesta['Codigo'] = 1;
							$respuesta['Referencia'] = $ref_rectificacion;
						}
					}
				}
			}
		} else {
			//Caso normal
			$consulta = "SELECT consecutivo FROM consecutivos_expo";
			$query= mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al generar consecutivo de la referencia. ".$consulta;
				$respuesta['Error'] = $error;
			}else{
				if(mysqli_num_rows($query) > 0){
					while ($row = mysqli_fetch_array($query)){
						$nvoCons = ($row['consecutivo']+1);
						$consulta = "UPDATE consecutivos_expo SET consecutivo = '".$nvoCons."'";
						$query= mysqli_query($cmysqli,$consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = "Error al generar consecutivo de la referencia. ".$consulta;
							$respuesta['Error'] = $error;
						}else{
							$nvaRef = 'GA'.date("y").str_pad($nvoCons,5,0,STR_PAD_LEFT);
							
							$fecharegistro =  date("Y-m-d H:i:s");
							$consulta = "INSERT INTO entradas_expo (referencia,fecha,numcliente,NombreCliente) 
												VALUES ('".$nvaRef."','".$fecharegistro."','".$cliente."',(SELECT cnombre FROM cltes_expo WHERE gcliente = '".$cliente."'))";
							$query= mysqli_query($cmysqli,$consulta);
							if (!$query) {
								$error=mysqli_error($cmysqli);
								$respuesta['Codigo'] = -1;
								$respuesta['Mensaje'] = "Error al generar referencia de exportacion. ".$consulta;
								$respuesta['Error'] = $error;
							}else{
								$respuesta['Codigo'] = 1;
								$respuesta['Referencia'] = $nvaRef;
							}
						}
					}
				}else{
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "No existe un consecutivo para las referencias en consecutivos_expo.";
					$respuesta['Error'] = "";
				}
			}
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error']='';
	}
	exit(json_encode($respuesta));
}