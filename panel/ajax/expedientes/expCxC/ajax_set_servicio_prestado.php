<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
	
	if (isset($_POST['sIdEmpresa']) && !empty($_POST['sIdEmpresa'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sIdEmpresa = $_POST['sIdEmpresa'];
		$sRutaDatos = $_POST['sRutaDatos'];
		$sPedimento = $_POST['sPedimento'];
		$sCvePedimento = $_POST['sCvePedimento'];
		$sOperacion = $_POST['sOperacion'];
		$sComentarios = $_POST['sComentarios'];
		$sTipoMov = $_POST['sTipoMov'];
		$sNoBanco = $_POST['sNoBanco'];
		$sNoMov = $_POST['sNoMov'];
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		$respuesta['nRowsUpdate'] = 0;
		$respuesta['nRowsInsert'] = 0;
		$respuesta['nRowsPuertosInsert'] = 0;
		$respuesta['nRowsPuertosInsertCuentas'] = '';
	
		//***********************************************************//
						
		$respuesta = fcn_process_registro($respuesta, $sIdEmpresa, $sTipoMov, $sNoBanco, $sNoMov, $fecha_registro, $cmysqli_exp, $sRutaDatos);

		if ($respuesta['Codigo']==1) {
			$respuesta['Mensaje']='Se ha Facturado correctamente';
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	
	echo json_encode($respuesta);
}

function fcn_process_registro($respuesta, $sIdEmpresa, $strTIPO_MOV, $strNO_BANCO, $strNO_MOV, $fecha_registro, $cmysqli_exp, $sRutaDatos) {
	$respuesta['Codigo']=1;	

	global $sPedimento, $sCvePedimento, $sOperacion, $sComentarios;

	$nRowsUpdate = (int)$respuesta['nRowsUpdate'];
	$nRowsInsert = (int)$respuesta['nRowsInsert'];
	$nRowsPuertosInsert = (int)$respuesta['nRowsPuertosInsert'];
	$nRowsPuertosInsertCuentas = $respuesta['nRowsPuertosInsertCuentas'];
	
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

				$sComentarios = (($sComentarios == '')? 'SERVICIOS PRESTADOS ADUANA: '.$strADUANA.' PATENTE: '.$strPATENTE.' PEDIMENTO:'.$sPedimento : $sComentarios);
				$consulta = "INSERT INTO expedientes.seguimiento_pedime 
							(id_empresa, tipo_mov, no_banco, no_mov, referencia, aduana, patente, pedimento, clave_pedimento, impo_expo, comentarios,
							 fecha_recepcion_captura, fecha_recepcion_entrega, fecha_cc_recepcion, fecha_cc_facturacion, fecha_cc_entrega)
							VALUES
							(".$sIdEmpresa.", 
							'".$strTIPO_MOV."',
							'".$strNO_BANCO."',
							'".$strNO_MOV."',
							'".$strTRAFICO."',
							'".$strADUANA."',
							'".$strPATENTE."',
							'".$sPedimento."',
							'".$sCvePedimento."',
							".$sOperacion.",
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
					$respuesta['Mensaje']='Error al insertar Factura. Por favor contacte al administrador del sistema.'; 
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
							(id_empresa, tipo_mov, no_banco, no_mov, referencia, aduana, patente, pedimento, comentarios, clave_pedimento, impo_expo, 
							 fecha_recepcion_captura, fecha_recepcion_entrega, fecha_cc_recepcion, fecha_cc_facturacion, fecha_cc_entrega)
							VALUES
							(".$sIdEmpresa.", 
							'".$strTIPO_MOV."',
							'".$strNO_BANCO."',
							'".$strNO_MOV."',
							'".$strTRAFICO."',
							'".$strADUANA."',
							'".$strPATENTE."',
							'".$sPedimento."',
							'".$sCvePedimento."',
							".$sOperacion.",
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
			
			break;
		}*/
		
		$consulta = "SELECT PEDIMENTO, TRAFICO, ADUANA, PATENTE
					 FROM ".fcn_get_tabla($sRutaDatos, 'notaremision', '_dbf')."
					  WHERE TIPO_MOV='".$strTIPO_MOV."' AND
							NO_BANCO=".$strNO_BANCO." AND
							NO_MOV=".$strNO_MOV;
		
		$query_notaremi = mysqli_query($cmysqli_exp, $consulta);
		if (!$query_notaremi) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar aacgmex. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			while($row_notaremi = mysqli_fetch_array($query_notaremi)){
				$strTRAFICO = $row_notaremi['TRAFICO'];
				$strADUANA = $row_notaremi['ADUANA'];
				$strPATENTE = $row_notaremi['PATENTE'];
				
				$consulta = "INSERT INTO expedientes.seguimiento_pedime 
							 (id_empresa, tipo_mov, no_banco, no_mov, referencia, aduana, patente, pedimento, clave_pedimento, impo_expo, comentarios,
							  fecha_recepcion_captura, fecha_recepcion_entrega, fecha_cc_recepcion, fecha_cc_facturacion, fecha_cc_entrega)
							 VALUES
							 (".$sIdEmpresa.", 
							 '".$strTIPO_MOV."',
							 '".$strNO_BANCO."',
							 '".$strNO_MOV."',
							 '".$strTRAFICO."',
							 '".$strADUANA."',
							 '".$strPATENTE."',
							 '".$sPedimento."',
							 '".$sCvePedimento."',
							 ".$sOperacion.",
							 '".$sComentarios."',
							 '".$fecha_registro."',
							 '".$fecha_registro."',
							 '".$fecha_registro."',
							 '".$fecha_registro."',
							 '".$fecha_registro."')";
			
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
	$respuesta['nRowsPuertosInsert'] = $nRowsPuertosInsert;
	$respuesta['nRowsPuertosInsertCuentas'] = $nRowsPuertosInsertCuentas;
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