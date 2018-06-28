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
		$aCuentas = json_decode($_POST['aCuentas']);
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		$respuesta['nRowsUpdate'] = 0;
		$respuesta['nRowsInsert'] = 0;
		$respuesta['nRowsPuertosInsert'] = 0;
		$respuesta['nRowsPuertosInsertCuentas'] = '';
	
		//***********************************************************//
						
		for ($i=0; $i < count($aCuentas); $i++) {
			$aData = explode("-", $aCuentas[$i]);
			$strTIPO_MOV = '';
			$strNO_BANCO = '';
			$strNO_MOV = '';
			
			if (count($aData) == 3) {
				// Cuenta de Gastos
				$strTIPO_MOV = $aData[0];
				$strNO_BANCO = $aData[1];
				$strNO_MOV = $aData[2];
				
				$respuesta = fcn_process_registro($respuesta, $sIdEmpresa, $strTIPO_MOV, $strNO_BANCO, $strNO_MOV, $fecha_registro, $cmysqli_exp, $sRutaDatos);
			} else {
				// Folio Fiscal UUID
				$UUID = $aCuentas[$i];
				
				$consulta = "SELECT 
							    TIPO_MOV
							   ,NO_BANCO
							   ,NO_MOV
							 FROM ".fcn_get_tabla($sRutaDatos, 'aacgmex')."
							 WHERE UUID='".$UUID."'";	

				$query = mysqli_query($cmysqli_exp, $consulta);
				while($row = mysqli_fetch_array($query)){
					$strTIPO_MOV = $row['TIPO_MOV'];
					$strNO_BANCO = $row['NO_BANCO'];
					$strNO_MOV = $row['NO_MOV'];
					
					$respuesta = fcn_process_registro($respuesta, $sIdEmpresa, $strTIPO_MOV, $strNO_BANCO, $strNO_MOV, $fecha_registro, $cmysqli_exp, $sRutaDatos);
				}
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

function fcn_process_registro($respuesta, $sIdEmpresa, $strTIPO_MOV, $strNO_BANCO, $strNO_MOV, $fecha_registro, $cmysqli_exp, $sRutaDatos) {
	$respuesta['Codigo']=1;	
	
	$nRowsUpdate = (int)$respuesta['nRowsUpdate'];
	$nRowsInsert = (int)$respuesta['nRowsInsert'];
	$nRowsPuertosInsert = (int)$respuesta['nRowsPuertosInsert'];
	$nRowsPuertosInsertCuentas = $respuesta['nRowsPuertosInsertCuentas'];

	/* Eliminar registros que no tienen pedimento 
	   (no pongo en el where por fecha_cc_facturacion por que puede que uno de esos 
	   fue corregido y por lo tanto debe ser eliminado) */
	$consulta = "DELETE FROM expedientes.seguimiento_pedime
				 WHERE id_empresa=".$sIdEmpresa." AND
				       tipo_mov='".$strTIPO_MOV."' AND
					   no_banco='".$strNO_BANCO."' AND
					   no_mov='".$strNO_MOV."' AND
					   pedimento IS NULL AND
					   fecha_cc_entrega IS NULL";	

	$query2 = mysqli_query($cmysqli_exp, $consulta);
	if (!$query2) {
		$error=mysqli_error($cmysqli_exp);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al eliminar facturas sin pedimento. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
	} else {
		/* Grabando Datos */
		if ($strTIPO_MOV == 'I') {
			$consulta = "SELECT PEDIMENTO, TRAFICO, ADUANA, PATENTE
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
					$strPEDIMENTO = trim($row_aacgmex['PEDIMENTO']);
					$strADUANA = trim($row_aacgmex['ADUANA']);
					$strPATENTE = trim($row_aacgmex['PATENTE']);
					
					if ($strPEDIMENTO != '') {
						//$respuesta['Mensaje'].=' :: P-T['.$rs_aacgmex->Fields("PEDIMENTO")->value.'-'.$strTRAFICO.']';
						$consulta = "SELECT COUNT(*) AS total
									 FROM expedientes.seguimiento_pedime
									 WHERE pedimento='".$strPEDIMENTO."' AND 
										   aduana='".$strADUANA."' AND
										   patente='".$strPATENTE."'";

						$query_existe = mysqli_query($cmysqli_exp, $consulta);
						if (!$query_existe) {
							$error=mysqli_error($cmysqli_exp);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al consultar existencia de pedimento en cuenta. Por favor contacte al administrador del sistema.'; 
							$respuesta['Error'] = ' ['.$error.']';
						} else {
							$nTotal = 0;
							while($row_existe = mysqli_fetch_array($query_existe)){ 
								$nTotal = $row_existe['total'];
								break;
							}
							
							if ($nTotal > 0) {
								/**********************************************************************************************/
								/**********************************************************************************************/
								$respuesta['Verify'] = 'Piensa que es Normal';
								
								//Se hace Update por que existe en nuestro sistema
								$consulta = "UPDATE expedientes.seguimiento_pedime 
											 SET id_empresa=".$sIdEmpresa."
												,tipo_mov='".$strTIPO_MOV."'
												,no_banco='".$strNO_BANCO."'
												,no_mov='".$strNO_MOV."'
												,referencia='".$strTRAFICO."'
												,fecha_cc_facturacion='".$fecha_registro."'
												,fecha_cc_entrega='".$fecha_registro."'
											 WHERE id_empresa IS NULL AND 
												   pedimento='".$strPEDIMENTO."' AND 
												   aduana='".$strADUANA."' AND
												   patente='".$strPATENTE."' AND
												   fecha_cc_recepcion IS NOT NULL AND
												   fecha_cc_facturacion IS NULL";	
								
								$respuesta['Consulta'] = $consulta;
								$query2 = mysqli_query($cmysqli_exp, $consulta);
								if (!$query2) {
									$error=mysqli_error($cmysqli_exp);
									$respuesta['Codigo']=-1;
									$respuesta['Mensaje']='Error al Facturar. Por favor contacte al administrador del sistema.'; 
									$respuesta['Error'] = ' ['.$error.']';
									break;
								} else {
									$nRowsUpdate += mysqli_affected_rows($cmysqli_exp);
									
									$consulta = "SELECT c001numped AS PEDIMENTO, ADUANA, PATENTE
												 FROM ".fcn_get_tabla($sRutaDatos, 'factura_consolidado')."
												 WHERE c001refmas='".$strTRAFICO."'";
									
									$query_pedimentos = mysqli_query($cmysqli_exp, $consulta);	
									while($row_pedimentos = mysqli_fetch_array($query_pedimentos)){
										$strPEDIMENTO = $row_pedimentos['PEDIMENTO'];
										$strADUANA =  $row_pedimentos['ADUANA'];
										$strPATENTE = $row_pedimentos['PATENTE'];
										
										//$respuesta['Mensaje'].=' :: Pedimentos->P-T['.$strPEDIMENTO.'-'.$strTRAFICO.']';
										
										$consulta = "UPDATE expedientes.seguimiento_pedime 
													 SET id_empresa=".$sIdEmpresa."
														,tipo_mov='".$strTIPO_MOV."'
														,no_banco='".$strNO_BANCO."'
														,no_mov='".$strNO_MOV."'
														,referencia='".$strTRAFICO."'
														,fecha_cc_facturacion='".$fecha_registro."'
														,fecha_cc_entrega='".$fecha_registro."'
													 WHERE id_empresa IS NULL AND 
														   pedimento='".$strPEDIMENTO."' AND 
														   aduana='".$strADUANA."' AND
														   patente='".$strPATENTE."' AND
														   fecha_cc_recepcion IS NOT NULL AND 
														   fecha_cc_facturacion IS NULL";	
							
										$query2 = mysqli_query($cmysqli_exp, $consulta);
										if (!$query2) {
											$error=mysqli_error($cmysqli_exp);
											$respuesta['Codigo']=-1;
											$respuesta['Mensaje']='Error al Facturar. Por favor contacte al administrador del sistema.'; 
											$respuesta['Error'] = ' ['.$error.']'.$consulta;
											break;
										} else {
											$nRowsUpdate += mysqli_affected_rows($cmysqli_exp);
										}
									}
								}
							} else {
								/**********************************************************************************************/
								/**********************************************************************************************/
								//hago insert por que es una cuenta sin pedimento (Caso de puertos maritimos u otros puertos)
								
								$respuesta['Verify'] = 'Piensa que es Puerto';
								
								/* Verificar que no exista en casa para no tomar un pedimento que no escaneo laura(por error) como un pedimento de puerto */
								$bExisteCasa = fcn_existe_en_casa($strADUANA, $strPATENTE, $strPEDIMENTO);
								
								if ($bExisteCasa == false && $respuesta['Codigo'] == 1) {
									$consulta = "INSERT INTO expedientes.seguimiento_pedime 
												(id_empresa, tipo_mov, no_banco, no_mov, referencia, aduana, patente, pedimento, comentarios, 
												 fecha_recepcion_captura, fecha_recepcion_entrega, fecha_cc_recepcion, fecha_cc_facturacion, fecha_cc_entrega)
												VALUES
												(".$sIdEmpresa.", 
												'".$strTIPO_MOV."',
												'".$strNO_BANCO."',
												'".$strNO_MOV."',
												'".$strTRAFICO."',
												'".$strADUANA."',
												'".$strPATENTE."',
												'".$strPEDIMENTO."',
												'PUERTOS ADUANA: ".$strADUANA." PATENTE: ".$strPATENTE."',
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
										$nRowsPuertosInsert += mysqli_affected_rows($cmysqli_exp);
										
										if ($nRowsPuertosInsertCuentas != '') {
											$nRowsPuertosInsertCuentas .= ', ';
										}
										$nRowsPuertosInsertCuentas .= $strNO_MOV;
										
										/*****************/
										
										$consulta = "SELECT c001numped AS PEDIMENTO, ADUANA, PATENTE
													 FROM ".fcn_get_tabla($sRutaDatos, 'factura_consolidado')."
													  WHERE c001refmas='".$strTRAFICO."'";
										
										$query_pedimentos = mysqli_query($cmysqli_exp, $consulta);
										while($row_pedimentos = mysqli_fetch_array($query_pedimentos)){
											$strPEDIMENTO = $row_pedimentos['PEDIMENTO'];
											$strADUANA =  $row_pedimentos['ADUANA'];
											$strPATENTE = $row_pedimentos['PATENTE'];
											
											$consulta = "SELECT COUNT(*) AS total
														 FROM expedientes.seguimiento_pedime
														 WHERE pedimento='".$strPEDIMENTO."' AND 
															   aduana='".$strADUANA."' AND
															   patente='".$strPATENTE."'";

											$query_existe = mysqli_query($cmysqli_exp, $consulta);
											if (!$query_existe) {
												$error=mysqli_error($cmysqli_exp);
												$respuesta['Codigo']=-1;
												$respuesta['Mensaje']='Error al consultar existencia de pedimento en cuenta. Por favor contacte al administrador del sistema.'; 
												$respuesta['Error'] = ' ['.$error.']';
											} else {
												$nTotal = 0;
												while($row_existe = mysqli_fetch_array($query_existe)){ 
													$nTotal = $row_existe['total'];
													break;
												}
												
												if ($nTotal == 0) {
													$consulta = "INSERT INTO expedientes.seguimiento_pedime 
																(id_empresa, tipo_mov, no_banco, no_mov, referencia, aduana, patente, pedimento, comentarios, 
																 fecha_recepcion_captura, fecha_recepcion_entrega, fecha_cc_recepcion, fecha_cc_facturacion, fecha_cc_entrega)
																VALUES
																(".$sIdEmpresa.", 
																'".$strTIPO_MOV."',
																'".$strNO_BANCO."',
																'".$strNO_MOV."',
																'".$strTRAFICO."',
																'".$strADUANA."',
																'".$strPATENTE."',
																'".$strPEDIMENTO."',
																'PUERTOS ADUANA: ".$strADUANA." PATENTE: ".$strPATENTE."',
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
														$respuesta['Mensaje']='Error al insertar registro de Factura. Por favor contacte al administrador del sistema.'; 
														$respuesta['Error'] = ' ['.$error.']'.$consulta;
														break;
													} else {
														$nRowsInsert += mysqli_affected_rows($cmysqli_exp);
														$nRowsPuertosInsert += mysqli_affected_rows($cmysqli_exp);
														
														if ($nRowsPuertosInsertCuentas != '') {
															$nRowsPuertosInsertCuentas .= ', ';
														}
														$nRowsPuertosInsertCuentas .= $strNO_MOV;
													}
												}
											}
										}
									}
								} else {
									$respuesta['ExisteCasa'] = 'Existe en CASA no inserta nada';
								}
							}
						}
					} else {
						//Como borramos todos los que no tengan pedimento siempre y cuando no tengan fecha_cc_entrega,
						//es necesario insertarlos de nuevo
						$consulta = "INSERT INTO expedientes.seguimiento_pedime 
									(id_empresa, tipo_mov, no_banco, no_mov, referencia, fecha_cc_facturacion, fecha_cc_entrega)
									VALUES
									(".$sIdEmpresa.", 
									'".$strTIPO_MOV."',
									'".$strNO_BANCO."',
									'".$strNO_MOV."',
									'".$strTRAFICO."',
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
			}			
		} else {
			// Antes que revisara la rectificacion en access, el problema es que las rectificaciones no se estaban sincronizando bien en mysql
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
					$strPEDIMENTO = $row_notaremi['PEDIMENTO'];
					$strADUANA = $row_notaremi['ADUANA'];
					
					if ($strPEDIMENTO != '') {
						//$respuesta['Mensaje'].=' :: P-T['.$strPEDIMENTO.'-'.$strTRAFICO.']';
						
						$consulta = "UPDATE expedientes.seguimiento_pedime 
									 SET id_empresa=".$sIdEmpresa."
										,tipo_mov='".$strTIPO_MOV."'
										,no_banco='".$strNO_BANCO."'
										,no_mov='".$strNO_MOV."'
										,referencia='".$strTRAFICO."'
										,fecha_cc_facturacion='".$fecha_registro."'
										,fecha_cc_entrega='".$fecha_registro."'
									 WHERE id_empresa IS NULL AND
									       pedimento='".$strPEDIMENTO."' AND 
										   aduana='".$strADUANA."' AND
										   fecha_cc_recepcion IS NOT NULL AND 
										   fecha_cc_facturacion IS NULL";	
				
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

/* Verificar pedimento en casa */
function fcn_existe_en_casa($sAduana, $sPatente, $sPedimento) {
	global $odbccasa, $respuesta;
	
	$bReturn = false;
	
	$consulta = "SELECT a.NUM_REFE, a.IMP_EXPO, a.ADU_ENTR
				 FROM SAAIO_PEDIME a
				 WHERE a.PAT_AGEN='".$sPatente."' AND
					   a.NUM_PEDI='".$sPedimento."' AND
					   a.ADU_DESP='".$sAduana."'";
	$query = odbc_exec ($odbccasa, $consulta);
	if ($query==false){ 
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "Error al consultar el Pedimento [".$sPedimento."] en el sistema CASA.";
		$respuesta['Error'] = ' ['.$query.']';
	} else {
		if(odbc_num_rows($query)<=0){ 
			$bReturn =  false;
		} else {
			$bReturn =  true;
		}
	}
	
	return $bReturn;
}