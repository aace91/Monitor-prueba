<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
	require('./../../../../connect_casa.php');
	
	if (isset($_POST['sIdEmpresa']) && !empty($_POST['sIdEmpresa'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sIdEmpresa = $_POST['sIdEmpresa'];
		$sRutaDatos = $_POST['sRutaDatos'];
		$sAduana = $_POST['sAduana'];
		$sPatente = $_POST['sPatente'];
		$sPedimento = $_POST['sPedimento'];
		$sComentarios = $_POST['sComentarios'];
		$sTipoMov = $_POST['sTipoMov'];
		$sNoBanco = $_POST['sNoBanco'];
		$sNoMov = $_POST['sNoMov'];
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		$respuesta['nRowsUpdate'] = 0;
		$respuesta['nRowsInsert'] = 0;
		
		//***********************************************************//
		
		$consulta = "SELECT a.NUM_REFE, a.CVE_PEDI, a.IMP_EXPO
					 FROM SAAIO_PEDIME a
					 WHERE a.ADU_ENTR='".$sAduana."' AND
						   a.PAT_AGEN='".$sPatente."' AND
						   a.NUM_PEDI='".$sPedimento."'";

		$result = odbc_exec ($odbccasa, $consulta);
		if ($result == false) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar pedimento. Por favor contacte al administrador del sistema.'.$consulta; 
			$respuesta['Error'] = '';
		} else {
			$nRows = odbc_num_rows($result);
			if ($nRows > 0) {
				while(odbc_fetch_row($result)){ 
					$referencia_saaio = trim(odbc_result($result,"NUM_REFE"));
					$clave_pedimento = trim(odbc_result($result,"CVE_PEDI"));
					$impo_expo = trim(odbc_result($result,"IMP_EXPO"));

					$consulta = "SELECT referencia_saaio, clave_pedimento, impo_expo
								 FROM expedientes.seguimiento_pedime
								 WHERE id_empresa=".$sIdEmpresa." AND
									   tipo_mov='".$sTipoMov."' AND
									   no_banco=".$sNoBanco." AND
									   no_mov=".$sNoMov;
									   
					$query = mysqli_query($cmysqli_exp, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli_exp);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al verificar cuenta de gastos. Por favor contacte al administrador del sistema.'; 
						$respuesta['Error'] = ' ['.$error.']';
					} else {
						$nRows = mysqli_num_rows($query);
						if ($nRows > 0) { 
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='La cuenta de gastos ' . $sNoMov . ' ya ha sido facturada.'; 
							$respuesta['Error'] = '';
						} else {
							$respuesta = fcn_process_registro($respuesta, $sIdEmpresa, $sTipoMov, $sNoBanco, $sNoMov, $fecha_registro, $cmysqli_exp, $sRutaDatos, $referencia_saaio, $clave_pedimento, $impo_expo);
						}
					}			   
					
					break;
				}
			} else {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='No existe pedimento ['.$sPedimento.'] en el sistema.'; 
				$respuesta['Error'] = '';
			}
		}
		
		if ($respuesta['Codigo']==1) {
			$respuesta['Mensaje']='Se ha Facturado correctamente';
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	
	echo json_encode($respuesta);
}

function fcn_process_registro($respuesta, $sIdEmpresa, $strTIPO_MOV, $strNO_BANCO, $strNO_MOV, $fecha_registro, $cmysqli_exp, $sRutaDatos, $referencia_saaio, $clave_pedimento, $impo_expo) {
	$respuesta['Codigo']=1;	

	global $sAduana, $sPatente, $sPedimento, $sComentarios;

	$nRowsUpdate = (int)$respuesta['nRowsUpdate'];
	$nRowsInsert = (int)$respuesta['nRowsInsert'];
	
	/* Grabando Datos */
	if ($strTIPO_MOV == 'I') {
		$consulta = "SELECT FACTURA, TRAFICO, ADUANA, PATENTE
					 FROM ".fcn_get_tabla($sRutaDatos, 'aacgmex')."
					 WHERE TIPO_MOV='".$strTIPO_MOV."' AND
						   NO_BANCO=".$strNO_BANCO." AND
						   NO_MOV=".$strNO_MOV;

		$query_aacgmex = mysqli_query($cmysqli_exp, $consulta);
		if (!$query_aacgmex) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar aacgmex. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			while($row_aacgmex = mysqli_fetch_array($query_aacgmex)){ 
				$strTRAFICO = trim($row_aacgmex['TRAFICO']);
				//$strPEDIMENTO = trim($row_aacgmex['PEDIMENTO']);
				$strADUANA = trim($row_aacgmex['ADUANA']);
				$strPATENTE = trim($row_aacgmex['PATENTE']);

				$sComentarios = (($sComentarios == '')? 'ADICIONAL: '.$strADUANA.' PATENTE: '.$strPATENTE.' PEDIMENTO:'.$sPedimento : $sComentarios);
				$consulta = "INSERT INTO expedientes.seguimiento_pedime 
							(id_empresa, tipo_mov, no_banco, no_mov, referencia, complemento, referencia_saaio, aduana, patente, pedimento, clave_pedimento, impo_expo, comentarios,
							 fecha_recepcion_captura, fecha_recepcion_entrega, fecha_cc_recepcion, fecha_cc_facturacion, fecha_cc_entrega)
							VALUES
							(".$sIdEmpresa.", 
							'".$strTIPO_MOV."',
							'".$strNO_BANCO."',
							'".$strNO_MOV."',
							'".$strTRAFICO."',
							1,
							'".$referencia_saaio."',
							'".$sAduana."',
							'".$sPatente."',
							'".$sPedimento."',
							'".$clave_pedimento."',
							".$impo_expo.",
							'".$sComentarios."',
							'".$fecha_registro."',
							'".$fecha_registro."',
							'".$fecha_registro."',
							'".$fecha_registro."',
							'".$fecha_registro."'
							)";	

				$query2 = mysqli_query($cmysqli_exp, $consulta);
				if (!$query2) {
					$error=mysqli_error($cmysqli_exp);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al insertar Factura. Por favor contacte al administrador del sistema.'.$sComentarios; 
					$respuesta['Error'] = ' ['.$error.']'.$consulta;
					break;
				} else {
					$nRowsInsert += mysqli_affected_rows($cmysqli_exp);
				}
			}					
		}			
	} else {
		/*$conn_notaremi = new COM("ADODB.Connection");
		$conn_notaremi->Open("Provider=vfpoledb.1; Data Source=\\".$sRutaDatos."\\notaremision.DBF; Collating Sequence=GENERAL");

		$consulta = "SELECT PEDIMENTO, TRAFICO, ADUANA, PATENTE
					 FROM notaremision
					 WHERE TIPO_MOV='".$strTIPO_MOV."' AND
						   NO_BANCO=".$strNO_BANCO." AND
						   NO_MOV=".$strNO_MOV;
		$rs_notaremi = $conn_notaremi->Execute($consulta);
		
		while (!$rs_notaremi->EOF) {
			$strTRAFICO = $rs_notaremi->Fields("TRAFICO")->value;
			//$strPEDIMENTO = $rs_notaremi->Fields("PEDIMENTO")->value;
			$strADUANA = trim($rs_notaremi->Fields("ADUANA")->value);
			$strPATENTE = trim($rs_notaremi->Fields("PATENTE")->value);
			
			$rs_notaremi->MoveNext();
			
			$consulta = "INSERT INTO expedientes.seguimiento_pedime 
							(id_empresa, tipo_mov, no_banco, no_mov, referencia, complemento, referencia_saaio, aduana, patente, pedimento, clave_pedimento, impo_expo, comentarios,
							 fecha_recepcion_captura, fecha_recepcion_entrega, fecha_cc_recepcion, fecha_cc_facturacion, fecha_cc_entrega)
							VALUES
							(".$sIdEmpresa.", 
							'".$strTIPO_MOV."',
							'".$strNO_BANCO."',
							'".$strNO_MOV."',
							'".$strTRAFICO."',
							1,
							'".$referencia_saaio."',
							'".$sAduana."',
							'".$sPatente."',
							'".$sPedimento."',
							'".$clave_pedimento."',
							".$impo_expo.",
							'".$sComentarios."',
							'".$fecha_registro."',
							'".$fecha_registro."',
							'".$fecha_registro."',
							'".$fecha_registro."',
							'".$fecha_registro."'
							)";	
	
			$query2 = mysqli_query($cmysqli_exp, $consulta);
			if (!$query2) {
				$error=mysqli_error($cmysqli_exp);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al Facturar. Por favor contacte al administrador del sistema.'.$consulta; 
				$respuesta['Error'] = ' ['.$error.']';
			} else {
				$nRowsUpdate += mysqli_affected_rows($cmysqli_exp);
			}
			
			break;
		}*/
		
		$consulta = "SELECT PEDIMENTO, TRAFICO, ADUANA, PATENTE
					 FROM ".fcn_get_tabla($sRutaDatos, 'notaremision', '_dbf')."
					 WHERE NO_MOV=".$strNO_MOV;
		
		$query_notaremi = mysqli_query($cmysqli_exp, $consulta);
		if (!$query_notaremi) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar aacgmex. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			while($row_notaremi = mysqli_fetch_array($query_notaremi)){
				$strTRAFICO = $row_notaremi['TRAFICO'];
				//$strPEDIMENTO = $row_notaremi['PEDIMENTO'];
				$strADUANA = $row_notaremi['ADUANA'];
				$strPATENTE = $row_notaremi['PATENTE'];
				
				$consulta = "INSERT INTO expedientes.seguimiento_pedime 
							 (id_empresa, tipo_mov, no_banco, no_mov, referencia, complemento, referencia_saaio, aduana, patente, pedimento, clave_pedimento, impo_expo, comentarios,
							  fecha_recepcion_captura, fecha_recepcion_entrega, fecha_cc_recepcion, fecha_cc_facturacion, fecha_cc_entrega)
							 VALUES
							 (".$sIdEmpresa.", 
							 '".$strTIPO_MOV."',
							 '".$strNO_BANCO."',
							 '".$strNO_MOV."',
							 '".$strTRAFICO."',
							 1,
							 '".$referencia_saaio."',
							 '".$sAduana."',
							 '".$sPatente."',
							 '".$sPedimento."',
							 '".$clave_pedimento."',
							 ".$impo_expo.",
							 '".$sComentarios."',
							 '".$fecha_registro."',
							 '".$fecha_registro."',
							 '".$fecha_registro."',
							 '".$fecha_registro."',
							 '".$fecha_registro."'
							 )";	
		
				$query2 = mysqli_query($cmysqli_exp, $consulta);
				if (!$query2) {
					$error=mysqli_error($cmysqli_exp);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al Facturar. Por favor contacte al administrador del sistema.'; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					$nRowsUpdate += mysqli_affected_rows($cmysqli_exp);
				}
			}
		}
	}
	
	$respuesta['nRowsUpdate'] = $nRowsUpdate;
	$respuesta['nRowsInsert'] = $nRowsInsert;
	return $respuesta;
}

function fcn_get_tabla($sRutaDatos, $tabla, $subtabla = '') {
	
	if (strpos($sRutaDatos, 'sab07') !== false) {
		return 'contagab.'.$tabla.$subtabla;
	} else if (strpos($sRutaDatos, 'sab10') !== false) {
		return 'contasab.'.$tabla.$subtabla;
	} else {
		return '';
	}
	
}