<?php
set_time_limit(240);

include_once('./../../../checklogin.php');
include('./../../../connect_dbsql.php');
include('./../../../bower_components/nusoap/src/nusoap.php');
include("./../../../bower_components/PHPExcel/Classes/PHPExcel/IOFactory.php");
include('./../../../url_archivos.php');

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'pruebas/') !== false)? true : false);

if($loggedIn == false){
	$action = $_REQUEST['action'];
	switch ($action) {
		case 'table_entradas':
			exit(json_encode(array("error" => '500')));
			break;
		default:
			exit('500');
	}
} else {
	if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
		$action = $_REQUEST['action'];
		switch ($action) {
			case 'table_entradas' : $respuesta = fcn_table_entradas($_REQUEST['estatus']);
				echo json_encode($respuesta);
				break;
				
			case 'procesar_xls' : $respuesta = fcn_procesar_xls();
				echo json_encode($respuesta);
				break;

			case 'eliminar_referencia' : $respuesta = fcn_eliminar_referencia();
				echo json_encode($respuesta);
				break;
		}
	}
}

/*************************************************************************************************/
/* METODOS                                                                                       */
/*************************************************************************************************/

function fcn_table_entradas($estatusref){
	global $_POST, $bDebug, $baseSql, $mysqluser, $mysqlpass, $mysqldb, $mysqlserver, $id;
	
	$table = '`ordenes_b&g`';
	$primaryKey = 'id_reg';

	$columns = array(
		array( 'db' => 'id_reg',        'dt' => 'id_reg' ),
		array( 'db' => 'po', 'dt' =>    'po' ),
		array( 'db' => 'noparte',       'dt' => 'noparte' ),
		array( 'db' => 'descripcion',   'dt' => 'descripcion' ),
		array( 'db' => 'qty',           'dt' => 'qty' ),
		array( 'db' => 'unidad_medida', 'dt' => 'unidad_medida' ),
		array( 'db' => 'proveedor',     'dt' => 'proveedor' ),
		array( 'db' => 'fecha_envio',   'dt' => 'fecha_envio', 'formatter' => function( $d, $row ) {
			return (($d=='')? '' : date( 'd/m/Y', strtotime($d)));
		}),
		array( 'db' => 'fecha_entrega', 'dt' => 'fecha_entrega', 'formatter' => function( $d, $row ) {
			return (($d=='')? '' : date( 'd/m/Y', strtotime($d)));
		}),
		array( 'db' => 'referencia',    'dt' => 'referencia', 'formatter' => function( $d, $row ) {
			return (($d=='')? '' : (($d=='X')? 'Pendiente' : $d));
		}),
		array( 'db' => 'PORLLEGAR',     'dt' => 'PORLLEGAR' ),
		array( 'db' => 'flete',     'dt' => 'flete' ),
		array( 'db' => 'temperatura',    'dt' => 'temperatura', 'formatter' => function( $d, $row ) {
			return ( $d==NULL ? 'NO' :  'SI' );
		}),
	);

	$sql_details = array(
		'user' => $mysqluser,
		'pass' => $mysqlpass,
		'db'   => $mysqldb,
		'host' => $mysqlserver
	);

	$baseSql = "SELECT bodEnt.id_reg, bodEnt.po, bodEnt.noparte, bodEnt.descripcion, bodEnt.qty,
					   bodEnt.unidad_medida, bodProv.proNom AS proveedor, bodEnt.fecha_envio, bodEnt.fecha_entrega, 
					   bodEnt.referencia, tblbod.PORLLEGAR, bodEnt.flete as flete, bodEnt.temperatura as temperatura
				FROM bodega.`ordenes_b&g` AS bodEnt INNER JOIN
					 bodega.clasificaciones AS bodClasif ON bodClasif.noparte=bodEnt.noparte INNER JOIN
					 bodega.procli AS bodProv ON bodProv.proveedor_id=bodClasif.proveedor_id INNER JOIN
					 bodega.tblbod AS tblbod ON tblbod.bodReferencia=bodEnt.referencia
				WHERE bodEnt.fecha_eliminado IS NULL ".(($estatusref == 3)? '' : (($estatusref == 1)? 'AND tblbod.bodfecha IS NULL' : 'AND tblbod.bodfecha IS NOT NULL'));

	require('./../../ssp.class.php');
	return	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns );
}

function fcn_procesar_xls(){
	global $_POST, $bDebug, $cmysqli, $_FILES;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_FILES['oXls']) && !empty($_FILES['oXls'])) {
			$aData = array();
			
			$objPHPExcel = PHPExcel_IOFactory::load($_FILES["oXls"]["tmp_name"]);
			
			foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
				$highestRow = $worksheet->getHighestRow();
				
				for ($row=3; $row<=$highestRow; $row++) {
					$po = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
					$noparte = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
					/*$descripcion = $worksheet->getCellByColumnAndRow(2, $row)->getOldCalculatedValue();
					if (is_null($descripcion)) {
						$descripcion = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
					}*/
					
					$qty = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					$unidad_medida = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
					$proveedor = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
					$fecha_envio_mysql = fcn_get_fecha_mysql($worksheet->getCellByColumnAndRow(6, $row));
					$fecha_entrega_mysql = fcn_get_fecha_mysql($worksheet->getCellByColumnAndRow(7, $row));
					$fecha_envio_access = fcn_get_fecha_access($worksheet->getCellByColumnAndRow(6, $row));
					$fecha_entrega_access = fcn_get_fecha_access($worksheet->getCellByColumnAndRow(7, $row));
					$referencia = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
					$caja = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
					$flete = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
					$temperatura = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
					
					$boddesccaja = 'CAJA 53';
					$bodfle = '66';
					
					if (is_numeric($temperatura)) {
						$boddesccaja = 'REFRIGERADA';
						$bodfle = '808';
					} else {
						$temperatura = '';
					}
					
					if (is_numeric($flete) == false) {
						$flete = '0';
					}
					
					if (strtoupper($referencia) == 'X') {
						if (is_null($po) && is_null($noparte)) {
							break;
						}
						
						$descripcion = '';
						$proveedor = fcn_get_proveedor_clasif($noparte, $descripcion);
						$sTask = fcn_existe_po($po, $referencia);
						
						$oRow = array(
							'po' => $po,
							'noparte' => $noparte,
							'descripcion' => $descripcion,
							'qty' => $qty,
							'unidad_medida' => $unidad_medida,
							'proveedor' => $proveedor,
							'fecha_envio_mysql' => $fecha_envio_mysql,
							'fecha_entrega_mysql' => $fecha_entrega_mysql,
							'fecha_envio_access' => $fecha_envio_access,
							'fecha_entrega_access' => $fecha_entrega_access,
							'referencia' => $referencia,
							'temperatura' => $temperatura,
							'caja' => ((is_null($caja))? '' : $caja),
							'flete' => $flete,
							'boddesccaja' => $boddesccaja,
							'bodfle' => $bodfle,
							'task' => $sTask
						);

						array_push($aData, $oRow);
					}
				}
			}
			
			/*$respuesta['aData'] = $aData;
			exit(json_encode($respuesta));*/
			
			/* Insertamos informacion */
			if ($respuesta['Codigo'] == 1) {
				foreach ($aData as $oRow) {
					$fecha_registro_mysql =  date("Y-m-d");
					$fecha_registro_access =  date("m/d/Y");
					
					switch ($oRow['task']) {
						case "insert":
							$sConsecutivo = fcn_get_consecutivo();
							$sReferencia = 'BF-'.$sConsecutivo;
							
							$consulta = "INSERT INTO tblbod
											(bodReferencia, BODEMB, bodprocli, bodcli,
											 bodtipemb, bodfle, bodtipfle, bodpesolbs,
											 bodcaja, BodDescCaja, PORLLEGAR, packlist,
											 bodbultos, clasebultos, bodusuario, bodimpfle,
											 boddescmer, bodnopedido, BODFORIGEN, fechaVirtual, fechapaq)
										  VALUES 
											('".$sReferencia."'
											,'".explode("|", $oRow['proveedor'])[1]."'
											,".explode("|", $oRow['proveedor'])[0]."
											,1359
											,'COMPLETO'
											,".$oRow['bodfle']."
											,'PPD'
											,0
											,'".$oRow['caja']."'
											,'".$oRow['boddesccaja']."'
											,1
											,'NO'
											,1
											,'BULTOS'
											,1
											,0
											,'".$oRow['descripcion']."'
											,'".$oRow['po']."'
											,'".$oRow['fecha_envio_access']."'
											,'".$fecha_registro_access."'
											,'".$oRow['fecha_entrega_access']."');";
							
							$bResult = fcn_set_insupd_referencia($consulta, 'INSERT', 'bodega');				
							if ($bResult === true) {
								mysqli_query($cmysqli, "BEGIN");
								
								/********************************************************/
								
								$BOD_ID = fcn_get_bod_id_access($sReferencia);
								
								$consulta = "INSERT INTO bodegareplica.tblbod
												(BOD_ID, bodReferencia, BODEMB, bodprocli, bodcli,
												 bodtipemb, bodfle, bodtipfle, bodpesolbs,
												 bodcaja, BodDescCaja, PORLLEGAR, packlist,
												 bodbultos, clasebultos, bodusuario, bodimpfle,
												 boddescmer, bodnopedido, BODFORIGEN, fechaVirtual, fechapaq)
											  VALUES 
												(".$BOD_ID."
												,'".$sReferencia."'
												,'".explode("|", $oRow['proveedor'])[1]."'
												,".explode("|", $oRow['proveedor'])[0]."
												,1359
												,'COMPLETO'
												,".$oRow['bodfle']."
												,'PPD'
												,0
												,'".$oRow['caja']."'
												,'".$oRow['boddesccaja']."'
												,1
												,'NO'
												,1
												,'BULTOS'
												,1
												,0
												,'".$oRow['descripcion']."'
												,'".$oRow['po']."'
												,'".$oRow['fecha_envio_mysql']."'
												,'".$fecha_registro_mysql."'
												,'".$oRow['fecha_entrega_mysql']."')";
								
								$query_insert = mysqli_query($cmysqli, $consulta);
								if (!$query_insert) {
									$error=mysqli_error($cmysqli);
									$respuesta['Codigo']=-1;
									$respuesta['Mensaje']='Error al agregar registro en bodegareplica.tblbod. Por favor contacte al administrador del sistema.'.$consulta; 
									$respuesta['Error'] = ' ['.$error.']';
								} else {
									$consulta = str_replace("bodegareplica.tblbod","bodega.tblbod", $consulta);
									
									$query_insert = mysqli_query($cmysqli, $consulta);
									if (!$query_insert) {
										$error=mysqli_error($cmysqli);
										$respuesta['Codigo']=-1;
										$respuesta['Mensaje']='Error al agregar registro en bodega.tblbod. Por favor contacte al administrador del sistema.'.$consulta; 
										$respuesta['Error'] = ' ['.$error.']';
									} else {
										$consulta = "INSERT INTO bodega.`ordenes_b&g`
														(po
														,noparte
														,descripcion
														,qty
														,unidad_medida
														,proveedor
														,fecha_envio
														,fecha_entrega
														,temperatura
														,caja
														,flete
														,referencia)
													  VALUES 
														('".$oRow['po']."'
														,'".$oRow['noparte']."'
														,'".$oRow['descripcion']."'
														,".$oRow['qty']."
														,'".$oRow['unidad_medida']."'
														,".explode("|", $oRow['proveedor'])[0]."
														,'".$oRow['fecha_envio_mysql']."'
														,'".$oRow['fecha_entrega_mysql']."'
														,".(($oRow['temperatura'] == '')? 'NULL' : $oRow['temperatura'])."
														,'".$oRow['caja']."'
														,'".$oRow['flete']."'
														,'".$sReferencia."')";
										
										$query_insert = mysqli_query($cmysqli, $consulta);
										if (!$query_insert) {
											$error=mysqli_error($cmysqli);
											$respuesta['Codigo']=-1;
											$respuesta['Mensaje']='Error al agregar registro en entradas_bg. Por favor contacte al administrador del sistema.'.$consulta; 
											$respuesta['Error'] = ' ['.$error.']';
										}
									}
								}
				
								/********************************************************/
								
								if ($respuesta['Codigo'] == 1) { 
									mysqli_query($cmysqli, "COMMIT");
								} else {
									mysqli_query($cmysqli, "ROLLBACK");
								}
							} else {
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']=$bResult;
								$respuesta['Error'] = '';
							}
							
							$respuesta['sConsecutivo'] = $sConsecutivo;
							$respuesta['Consulta'] = $consulta;
							break;
						case "update":			 
							$consulta = "UPDATE tblbod
										 SET BODEMB='".explode("|", $oRow['proveedor'])[1]."',
										     bodprocli=".explode("|", $oRow['proveedor'])[0].",
											 bodcli=1359,
											 bodtipemb='COMPLETO',
											 bodfle=".$oRow['bodfle'].",
											 bodtipfle='PPD',
											 bodpesolbs=0,
											 bodcaja='".$oRow['caja']."',
											 BodDescCaja='".$oRow['boddesccaja']."',
											 PORLLEGAR=1,
											 packlist='NO',
											 bodbultos=1,
											 clasebultos='BULTOS',
											 bodimpfle=0,
											 boddescmer='".$oRow['descripcion']."',
										     BODFORIGEN='".$oRow['fecha_envio_access']."',
										     fechapaq='".$oRow['fecha_entrega_access']."'
										 WHERE bodnopedido='".$oRow['po']."' AND
										       bodReferencia='".$oRow['referencia']."';";
							
							$bResult = fcn_set_insupd_referencia($consulta, 'UPDATE', 'bodega');				
							if ($bResult === true) {
								mysqli_query($cmysqli, "BEGIN");
								
								/********************************************************/

								$consulta = "UPDATE bodegareplica.tblbod
										     SET BODEMB='".explode("|", $oRow['proveedor'])[1]."',
										         bodprocli=".explode("|", $oRow['proveedor'])[0].",
											     bodcli=1359,
											     bodtipemb='COMPLETO',
											     bodfle=".$oRow['bodfle'].",
											     bodtipfle='PPD',
											     bodpesolbs=0,
											     bodcaja='".$oRow['caja']."',
											     BodDescCaja='".$oRow['boddesccaja']."',
											     PORLLEGAR=1,
											     packlist='NO',
											     bodbultos=1,
											     clasebultos='BULTOS',
											     bodimpfle=0,
											     boddescmer='".$oRow['descripcion']."',
										         BODFORIGEN='".$oRow['fecha_envio_mysql']."',
										         fechapaq='".$oRow['fecha_entrega_mysql']."'
										     WHERE bodnopedido='".$oRow['po']."' AND
												   bodReferencia='".$oRow['referencia']."';";
												   
								$query = mysqli_query($cmysqli, $consulta);
								if (!$query) {
									$error=mysqli_error($cmysqli);
									$respuesta['Codigo']=-1;
									$respuesta['Mensaje']='Error al actualizar registro en bodegareplica.tblbod. Por favor contacte al administrador del sistema.'.$consulta; 
									$respuesta['Error'] = ' ['.$error.']';
								}

								if ($respuesta['Codigo'] == 1) {
									$consulta = str_replace("bodegareplica.tblbod","bodega.tblbod", $consulta);

									$query = mysqli_query($cmysqli, $consulta);
									if (!$query) {
										$error=mysqli_error($cmysqli);
										$respuesta['Codigo']=-1;
										$respuesta['Mensaje']='Error al actualizar registro en bodega.tblbod. Por favor contacte al administrador del sistema.'.$consulta; 
										$respuesta['Error'] = ' ['.$error.']';
									}
								}

								if ($respuesta['Codigo'] == 1) {
									$consulta = "UPDATE bodega.`ordenes_b&g`
												 SET po='".$oRow['po']."',
													 noparte='".$oRow['noparte']."',
													 descripcion='".$oRow['descripcion']."',
													 qty=".$oRow['qty'].",
													 unidad_medida='".$oRow['unidad_medida']."',
													 proveedor=".explode("|", $oRow['proveedor'])[0].",
													 fecha_envio='".$oRow['fecha_envio_mysql']."',
													 fecha_entrega='".$oRow['fecha_entrega_mysql']."',
													 temperatura=".(($oRow['temperatura'] == '')? 'NULL' : $oRow['temperatura']).",
													 caja='".$oRow['caja']."',
													 flete='".$oRow['flete']."'
												 WHERE referencia='".$oRow['referencia']."'";
												
									$query = mysqli_query($cmysqli, $consulta);
									if (!$query) {
										$error=mysqli_error($cmysqli);
										$respuesta['Codigo']=-1;
										$respuesta['Mensaje']='Error al actualizar registro en entradas_bg. Por favor contacte al administrador del sistema.'.$consulta; 
										$respuesta['Error'] = ' ['.$error.']';
									}
								}

								/********************************************************/
								
								if ($respuesta['Codigo'] == 1) { 
									mysqli_query($cmysqli, "COMMIT");
								} else {
									mysqli_query($cmysqli, "ROLLBACK");
								}
							} else {
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']=$bResult;
								$respuesta['Error'] = '';
							}
							break;
					}
					
					if ($respuesta['Codigo'] != 1) {
						break;
					}
				}
			}
			
			$respuesta['aData'] = $aData;
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']=''; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

function fcn_eliminar_referencia(){
	global $_POST, $bDebug, $cmysqli, $id;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_POST['sReferencia']) && !empty($_POST['sReferencia'])) {
			$sReferencia = $_POST['sReferencia'];
			$sObservaciones = $_POST['sObservaciones'];

			/********************************************************/
			fcn_es_virtual($sReferencia);

			if ($respuesta['Codigo'] == 1) {
				$consulta = "DELETE FROM tblbod WHERE bodReferencia = '".$sReferencia."'";
				$bResult = fcn_set_insupd_referencia($consulta, 'UPDATE', 'bodega');
				if ($bResult === false) {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']=$bResult;
					$respuesta['Error'] = '';
				}
			}

			if ($respuesta['Codigo'] == 1) {
				$consulta = "DELETE FROM revision_general WHERE referencia = '".$sReferencia."'";
				$bResult = fcn_set_insupd_referencia($consulta, 'UPDATE', 'revisiones');
				if ($bResult === false) {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']=$bResult;
					$respuesta['Error'] = '';
				}
			}

			if ($respuesta['Codigo'] == 1) {
				$consulta = "DELETE FROM detalle WHERE referencia = '".$sReferencia."'";
				$bResult = fcn_set_insupd_referencia($consulta, 'UPDATE', 'revisiones');
				if ($bResult === false) {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']=$bResult;
					$respuesta['Error'] = '';
				}
			}

			if ($respuesta['Codigo'] == 1) {
				mysqli_query($cmysqli, "BEGIN");

				$consulta = "DELETE FROM bodegareplica.tblbod WHERE bodReferencia = '".$sReferencia."'";			
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al eliminar registro en bodegareplica.tblbod. Por favor contacte al administrador del sistema.'.$consulta; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					$consulta = str_replace("bodegareplica.tblbod","bodega.tblbod", $consulta);
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al eliminar registro en bodega.tblbod. Por favor contacte al administrador del sistema.'.$consulta; 
						$respuesta['Error'] = ' ['.$error.']';
					}
				}

				if ($respuesta['Codigo'] == 1) { 
					$consulta = "UPDATE bodega.`ordenes_b&g`
								 SET fecha_eliminado=NOW(),
									 observaciones='".$sObservaciones."',
									 usuario_elimino=".$id."
								 WHERE referencia='".$sReferencia."'";

					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al actualizar fecha eliminado en ordenesBG. Por favor contacte al administrador del sistema.'.$consulta; 
						$respuesta['Error'] = ' ['.$error.']';
					}
				}
				
				if ($respuesta['Codigo'] == 1) { 
					mysqli_query($cmysqli, "COMMIT");
					$respuesta['Mensaje']='Referencia <strong>'.$sReferencia.'</strong> eliminada correctamente';
				} else {
					mysqli_query($cmysqli, "ROLLBACK");
				}
			}
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']=''; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}
	
/*************************************************************************************************/
/* FUNCIONES                                                                                     */
/*************************************************************************************************/

function fcn_get_fecha_mysql($oCell) {
	if(PHPExcel_Shared_Date::isDateTime($oCell)){
		$FECHA = PHPExcel_Shared_Date::ExcelToPHPObject($oCell->getValue());
		return $FECHA->format('Y-m-d');
	}else{
		return $oCell->getValue();
	}
}

function fcn_get_fecha_access($oCell) {
	if(PHPExcel_Shared_Date::isDateTime($oCell)){
		$FECHA = PHPExcel_Shared_Date::ExcelToPHPObject($oCell->getValue());
		return $FECHA->format('m/d/Y');
	}else{
		return $oCell->getValue();
	}
}

function fcn_get_proveedor_clasif($sNoPart, &$descripcion) {
	global $bDebug, $cmysqli;
	
	$consulta = "SELECT bodClas.id, bodClas.proveedor_id, bodProv.proNom, bodClas.descripcion
				 FROM bodega.clasificaciones AS bodClas INNER JOIN
					  bodega.procli AS bodProv ON bodProv.proveedor_id=bodClas.proveedor_id
				 WHERE bodClas.noparte='".$sNoPart."' AND 
                       bodClas.cliente_id=1359";

	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		throw new Exception("fcn_get_proveedor_clasif() ". $error);
	}
	
	if (mysqli_num_rows($query) == 0) {
		throw new Exception('El numero de parte <strong>'.$sNoPart.'</strong> no existe en clasificaciones, favor de atender.');
	} else {
		while($row = mysqli_fetch_array($query)){ 
			$descripcion = $row['descripcion'];
			return $row['proveedor_id'] . '|' . $row['proNom'];
		}
	}
}

function fcn_existe_po($sPO, &$bodReferencia) {
	global $bDebug, $cmysqli, $URL_ws_webtools;
	
	$consulta_mdb = "SELECT bodReferencia, PORLLEGAR
					 FROM tblbod
					 WHERE bodnopedido='".$sPO."'";

	$client = new nusoap_client($URL_ws_webtools."/webtools".(($bDebug)? "pruebas" : "")."/ws_mdb/ws_mdb.php?wsdl","wsdl");
	$err = $client->getError();
	if ($err) {
		throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.");
	}

	$param = array(
		'usuario' => 'admin',
		'password' => 'r0117c',
		'consulta' => $consulta_mdb,
		'tipo' => 'SELECT',
		'bd' => 'bodega');
	
	$result = $client->call('ws_mdb', $param);
	$err = $client->getError();
	if ($err) {
		throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.". $err);
	}

	if($result['Codigo']==1){
		$bodegadet=json_decode($result['Adicional1']);
		if (count($bodegadet) > 0) {
			foreach($bodegadet as $row_bod){
				$PORLLEGAR = $row_bod->PORLLEGAR;
				$bodReferencia = $row_bod->bodReferencia;

				if ($PORLLEGAR == 1 || $PORLLEGAR == true) {
					return 'update';
				} else {
					throw new Exception("El PO <strong>".$sPO."</strong> con referencia <strong>".$bodReferencia."</strong> no es virtual por lo que no se puede actualizar.");
				}
			}
		} else {
			return 'insert';
		}
	}
}

function fcn_es_virtual($bodReferencia) {
	global $bDebug, $cmysqli, $URL_ws_webtools;
	
	$consulta_mdb = "SELECT bodReferencia, bodnopedido, PORLLEGAR
					 FROM tblbod
					 WHERE bodReferencia='".$bodReferencia."'";

	$client = new nusoap_client($URL_ws_webtools."/webtools".(($bDebug)? "pruebas" : "")."/ws_mdb/ws_mdb.php?wsdl","wsdl");
	$err = $client->getError();
	if ($err) {
		throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.");
	}

	$param = array(
		'usuario' => 'admin',
		'password' => 'r0117c',
		'consulta' => $consulta_mdb,
		'tipo' => 'SELECT',
		'bd' => 'bodega');
	
	$result = $client->call('ws_mdb', $param);
	$err = $client->getError();
	if ($err) {
		throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.". $err);
	}

	if($result['Codigo']==1){
		$bodegadet=json_decode($result['Adicional1']);
		if (count($bodegadet) > 0) {
			foreach($bodegadet as $row_bod){
				$PORLLEGAR = $row_bod->PORLLEGAR;
				$sPO = $row_bod->bodnopedido;

				if ($PORLLEGAR == 0 || $PORLLEGAR == false) {
					throw new Exception("El PO <strong>".$sPO."</strong> con referencia <strong>".$bodReferencia."</strong> no es virtual.");
				}
				break;
			}
		} else {
			throw new Exception("No existe información de la referencia <strong>".$bodReferencia."</strong> en bodega.");
		}
	}
}

function fcn_get_bod_id_access($sReferencia) {
	global $bDebug, $cmysqli, $URL_ws_webtools;
	
	$consulta_mdb = "SELECT bodReferencia, BOD_ID
					 FROM tblbod
					 WHERE bodReferencia='".$sReferencia."'";

	$client = new nusoap_client($URL_ws_webtools."/webtools".(($bDebug)? "pruebas" : "")."/ws_mdb/ws_mdb.php?wsdl","wsdl");
	$err = $client->getError();
	if ($err) {
		throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.");
	}

	$param = array(
		'usuario' => 'admin',
		'password' => 'r0117c',
		'consulta' => $consulta_mdb,
		'tipo' => 'SELECT',
		'bd' => 'bodega');
	
	$result = $client->call('ws_mdb', $param);
	$err = $client->getError();
	if ($err) {
		throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.". $err);
	}

	if($result['Codigo']==1){
		$bodegadet=json_decode($result['Adicional1']);
		if (count($bodegadet) > 0) {
			foreach($bodegadet as $row_bod){
				return $row_bod->BOD_ID;
			}
		} else {
			throw new Exception('No se pudo obtener el campo BOD_ID de la referencia <strong>'.$sReferencia.'</strong>');
		}
	} else {
		throw new Exception($result['Mensaje']);
	}
}

function fcn_get_consecutivo() {
	global $bDebug, $cmysqli, $URL_ws_webtools;
	
	$client = new nusoap_client($URL_ws_webtools."/webtools".(($bDebug)? "pruebas" : "")."/ws_mdb/ws_mdb.php?wsdl","wsdl");
	$err = $client->getError();
	if ($err) {
		throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.");
	}

	$param = array(
		'usuario' => 'admin',
	);
	$result = $client->call('get_consecutivo_referencia_bodega');
	$err = $client->getError();
	if ($err) {
		throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.". $err);
	}

	if($result['Codigo']==1){
		return $result['Adicional1'];
	} else {
		throw new Exception($result['Mensaje']);
	}
}

function fcn_set_insupd_referencia($consulta_mdb, $tipo, $bd) {
	global $bDebug, $cmysqli, $URL_ws_webtools;

	$client = new nusoap_client($URL_ws_webtools."/webtools".(($bDebug)? "pruebas" : "")."/ws_mdb/ws_mdb.php?wsdl","wsdl");
	$err = $client->getError();
	if ($err) {
		throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.");
	}

	$param = array(
		'usuario' => 'admin',
		'password' => 'r0117c',
		'consulta' => $consulta_mdb,
		'tipo' => $tipo,
		'bd' => $bd);
	
	$result = $client->call('ws_mdb', $param);
	$err = $client->getError();
	if ($err) {
		throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.". $err);
	}

	if($result['Codigo']==1){
		return true;
	} else {
		return $result['Mensaje'];
	}
}
