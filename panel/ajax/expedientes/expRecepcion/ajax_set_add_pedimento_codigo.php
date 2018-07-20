<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_casa.php');
	require('./../../../../connect_exp.php');
	require('./../../../../connect_dbsql.php');
		
	$host = '192.168.1.107:E:\CASAWIN\CSAAIWIN\Datos\CASA.GDB'; 
	$username='SYSDBA'; 
	$password='masterkey';					
	$dbh = ibase_connect($host, $username, $password);
					
	if (isset($_POST['sPatente']) && !empty($_POST['sPatente'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sPatente = $_POST['sPatente'];
		$sPedimento = $_POST['sPedimento'];
		$sClavePed = $_POST['sClavePed'];
		$sRFC = $_POST['sRFC'];
		$sFirElec = $_POST['sFirElec'];
		$sComentarios = $_POST['sComentarios'];
					
		//***********************************************************//

		$fecha_captura =  date("Y-m-d H:i:s");
		$fecha_registro_saaio =  date("d.m.y, H:i:s");
		
		//***********************************************************//
		
		$datos = Array();
		
		$strReferencia;
		$strImpoExpo;
		$strAduana;
		$strTipPedi;
				
		$consulta = "SELECT a.NUM_REFE, a.IMP_EXPO, a.ADU_ENTR, a.TIP_PEDI
					 FROM SAAIO_PEDIME a
					 WHERE a.PAT_AGEN='".$sPatente."' AND
						   a.NUM_PEDI='".$sPedimento."' AND
						   a.CVE_PEDI='".$sClavePed."' AND
						   a.FIR_ELEC='".$sFirElec."'";
		$query = odbc_exec ($odbccasa, $consulta);
		if ($query==false){ 
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar el Pedimento [".$sPedimento."] en el sistema CASA.";
			$respuesta['Error'] = ' ['.$query.']';
		} else {
			if(odbc_num_rows($query)<=0){ 
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "El pedimento [".$sPedimento."] no se encuentra en el sistema CASA.";
				$respuesta['Error'] = '';
			} else {
				if (odbc_num_rows($query) == 1) {
					while(odbc_fetch_row($query)){ 
						$strReferencia = odbc_result($query,"NUM_REFE");
						$strImpoExpo = odbc_result($query,"IMP_EXPO");
						$strAduana = odbc_result($query,"ADU_ENTR");
						$strTipPedi = odbc_result($query,"TIP_PEDI");
					}
				} else {
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "El pedimento [".$sPedimento."] se encuentra duplicado en el sistema CASA.";
					$respuesta['Error'] = '';
				}
			}	
		}

		//Si es un pedimento de exportación se debe verificar si todas las facturas del pedimento estan en una salida
		if ($respuesta['Codigo']== 1 && $strImpoExpo == 2 && $strTipPedi != 'R1') {
			$consulta = "SELECT a.NUM_REFE, a.CONS_FACT, a.NUM_FACT, a.NUM_FACT2
						 FROM SAAIO_FACTUR a
						 WHERE a.NUM_REFE='".$strReferencia."'
						 ORDER BY a.CONS_FACT";
			
			$query = odbc_exec ($odbccasa, $consulta);
			if ($query==false){ 
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al consultar facturas del [".$sPedimento."] en el sistema CASA.";
				$respuesta['Error'] = ' ['.$query.']';
			} else {
				if(odbc_num_rows($query)<=0){ 
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "El pedimento [".$sPedimento."] no tiene facturas en el sistema CASA.";
					$respuesta['Error'] = '';
				} else {
					while(odbc_fetch_row($query)){ 
						$strCONS_FACT = odbc_result($query,"CONS_FACT");
						$strNUM_FACT = odbc_result($query,"NUM_FACT");
						
						$consulta="SELECT REFERENCIA, PEDIMENTO
								   FROM bodega.facturas_expo
								   WHERE FACTURA_NUMERO='".$strNUM_FACT."' AND
										 CONS_FACT_PED=".$strCONS_FACT." AND
										 REFERENCIA='".$strReferencia."'";
											
						$query = mysqli_query($cmysqli, $consulta);			
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al consultar la factura ['.$strNUM_FACT.'] en salidas de exportación.'; 
							$respuesta['Error'] = ' ['.$error.']';	
						} else {
							if (mysqli_num_rows($query) == 0) {
								$respuesta['Codigo'] = -1;
								$respuesta['Mensaje'] = "La factura [".$strNUM_FACT."] no existe en una salida de exportación, favor de notificar al ejecutivo.";
								$respuesta['Error'] = '';
								break;
							}
						}
					}
				}
			}
		}

		//continuamos con el proceso normal
		if ($respuesta['Codigo']==1) {
			$consulta="SELECT pedimento
					   FROM expedientes.seguimiento_pedime 
					   WHERE referencia_saaio='".$strReferencia."' AND
							 pedimento='".$sPedimento."' AND 
							 aduana='".$strAduana."' AND 
							 complemento IS NULL";
			
			$query = mysqli_query($cmysqli_exp, $consulta);			
			if (!$query) {
				$error=mysqli_error($cmysqli_exp);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar el Pedimento ['.$sPedimento.'].'; 
				$respuesta['Error'] = ' ['.$error.']';	
			} else {
				$num_rows = mysqli_num_rows($query);
				
				if ($num_rows > 0) {
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "El pedimento [".$sPedimento."] ya esta dado de alta, favor de ingresar uno diferente.";
					$respuesta['Error'] = '';
				} else { 
					$tr=ibase_trans("IBASE_WRITE",$dbh);					
					$consulta = "INSERT INTO GAB_EXPEDIENTES (NUM_REFE, FEC_ALTA) VALUES ('".$strReferencia."', '".$fecha_registro_saaio."')";
					$sth = ibase_query($tr, $consulta);
					ibase_commit($tr);
				
					if (!$tr) { 
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = "Error al grabar el Pedimento [".$sPedimento." - ".$strReferencia."] en el sistema CASA.";
						$respuesta['Error'] = ' ['.$query.'] '.$consulta;
					} else {
						$consulta = "INSERT INTO expedientes.seguimiento_pedime
										(referencia_saaio, pedimento, clave_pedimento, impo_expo, aduana, patente, comentarios, fecha_recepcion_captura)
									 VALUES 
									 	('".$strReferencia."'
										,'".$sPedimento."'
										,'".$sClavePed."'
										,'".$strImpoExpo."'
										,'".$strAduana."'
										,'".$sPatente."'
										,'".$sComentarios."'
										,'".$fecha_captura."')";
										
						mysqli_query($cmysqli_exp, "BEGIN");
						$query = mysqli_query($cmysqli_exp, $consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli_exp);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al agregar Pedimento ['.$sPedimento.'].'; 
							$respuesta['Error'] = ' ['.$error.']';	
							
							mysqli_query($cmysqli_exp, "ROLLBACK");
						} else {
							$consulta = "UPDATE expedientes.seguimiento_pedime_pend
										 SET pendiente=1
										 WHERE referencia_saaio='".$strReferencia."'";

							$query = mysqli_query($cmysqli_exp, $consulta);
							if (!$query) {
								$error=mysqli_error($cmysqli_exp);
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']='Error al actualizar la Rectificacion Pedimento ['.$sPedimento.'].'; 
								$respuesta['Error'] = ' ['.$error.']';	
								
								mysqli_query($cmysqli_exp, "ROLLBACK");
							} else {
								$respuesta['Mensaje']='Pedimento agregado correctamente!';
							}
						}	
						mysqli_query($cmysqli_exp, "COMMIT");
					} 
				}
			}
		}

		if ($respuesta['Codigo']==1) {
			$consulta = "SELECT a.NUM_REFE, a.ADU_ENTR, a.PAT_AGEN, a.NUM_PEDI
						 FROM SAAIO_PEDIME a
						 WHERE a.NUM_REFEO='".$strReferencia."'";
			$query = odbc_exec ($odbccasa, $consulta);
			if ($query==false){ 
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al consultar rectificaciones del Pedimento [".$sPedimento."] en el sistema CASA.";
				$respuesta['Error'] = ' ['.$query.']';
			} else {
				while(odbc_fetch_row($query)){ 
					$strReferencia = odbc_result($query,"NUM_REFE");
					$strAduana = odbc_result($query,"ADU_ENTR");
					$strPatente = odbc_result($query,"PAT_AGEN");
					$strPedimento = odbc_result($query,"NUM_PEDI");
					
					$consulta = "SELECT referencia_saaio
								 FROM expedientes.seguimiento_pedime
								 WHERE referencia_saaio='".$strReferencia."'";

					$query2 = mysqli_query($cmysqli_exp, $consulta);	
					if ($query2==false){
						$error=mysqli_error($cmysqli_exp);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje'] = 'Error en consultar referencia en seguimiento_pedime'.$consulta;
						$respuesta['Error']=' ['.$error.']';
					} else {
						$num_rows = mysqli_num_rows($query2);
						if ($num_rows == 0) {
							$consulta = "INSERT INTO expedientes.seguimiento_pedime_pend
											(referencia_saaio, aduana, patente, pedimento, pendiente)
										 VALUES 
											('".$strReferencia."'
											,'".$strAduana."'
											,'".$strPatente."'
											,'".$strPedimento."'
											,0)";
										 
							$query = mysqli_query($cmysqli_exp, $consulta);	
							if (!$query) {
								$error=mysqli_error($cmysqli_exp);
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']='Error al agregar Rectificacion de Pedimento ['.$sPedimento.'].'; 
								$respuesta['Error'] = ' ['.$error.']';	
							} 
						}
					}
				}
			}
		}

		if ($respuesta['Codigo']==1) {
			$consulta = "SELECT referencia_saaio, pedimento
						 FROM expedientes.seguimiento_pedime_pend
						 WHERE pendiente IS NULL OR pendiente = 0";
			
			$query = mysqli_query($cmysqli_exp, $consulta);
			if ($query==false){
				$error=mysqli_error($cmysqli_exp);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje'] = 'Error en consultar los pedimentos pendientes'.$consulta;
				$respuesta['Error']=' ['.$error.']';
			} else {
				while($row = mysqli_fetch_array($query)){
					$aRow = array(
						'NUM_REFE'=> $row["referencia_saaio"],
						'PEDIMENTO'=> $row["pedimento"]
					);
					
					array_push($datos, $aRow);
				}
			}
		}

		$respuesta['aDatos']=$datos;
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}

