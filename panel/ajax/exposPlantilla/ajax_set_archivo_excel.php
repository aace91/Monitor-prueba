<?php
include_once('./../../../checklogin.php');
include('./../../../connect_casa.php');
require('./../../../connect_dbsql.php');

include('./../../../bower_components/PHPExcel/Classes/PHPExcel/IOFactory.php');

if ($loggedIn == false){
	echo '500';
}else{		
	if (isset($_POST['sReferencia']) && !empty($_POST['sReferencia'])) {
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$sReferencia = $_POST['sReferencia'];   
		
		//***********************************************************//
		
		$datetime1 = new DateTime("now");
		
		$fecha_registro = date("Y-m-d H:i:s");
		
		$nIdPlantilla = 0;
		$nIdEmbarque = 1;
		$sPedimento = '';
		$nTotalRegistros = 0;
		$aPaises = Array();
		$aUnidades = Array();
		$sClavePedimento = '';

		//***********************************************************//

		$sFacturasDumplicadas = get_facturas_duplicadas($sReferencia, $_FILES);

		mysqli_query($cmysqli, "BEGIN");
		if ($respuesta['Codigo'] == 1) {
			$consulta = "SELECT (id_embarque + 1) AS id_embarque
						 FROM bodega.expos_plantilla_gral
						 WHERE referencia='".$sReferencia."'
						 ORDER BY id_embarque DESC
					     LIMIT 1";

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar pedimento en plantilla general. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else { 
				$nNumRows = mysqli_num_rows($query);

				if ($nNumRows > 0) {
					while($row = mysqli_fetch_array($query)){ 
						$nIdEmbarque = $row['id_embarque'];
						break;
					}
				}

				$sPedimento = get_referencia_casa($sReferencia); //Tambien actualizo la clave del pedimento A1, RT etc,etc.

				if ($respuesta['Codigo'] == 1) { 
					$consulta = "INSERT INTO bodega.expos_plantilla_gral
								 (pedimento
								 ,referencia
								 ,id_embarque
								 ,fecha_alta
								 ,id_usuario_alta)
								 VALUES
								 ('".$sPedimento."'
								 ,'".$sReferencia."'
								 ,".$nIdEmbarque."
								 ,'".$fecha_registro."'
								 ,".$id.")";
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al insertar registro del embarque. Por favor contacte al administrador del sistema.'.$consulta; 
						$respuesta['Error'] = ' ['.$error.']';

						mysqli_query($cmysqli, "ROLLBACK");
					} else {
						$nIdPlantilla = mysqli_insert_id($cmysqli);
					}
				}			
			}
		}

		if ($respuesta['Codigo'] == 1) {
			$objPHPExcel = PHPExcel_IOFactory::load($_FILES["oXls"]["tmp_name"]);
		
			$datetime2 = new DateTime("now");
			$interval = date_diff($datetime1, $datetime2);
			$respuesta['Lectura'] = $interval->format("%H:%I:%S");
						
			$datetime1 = new DateTime("now");
			
			foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
				
				$highestRow = $worksheet->getHighestRow();
				//$nTotalRegistros = $highestRow - 1;
				
				//mysqli_query($cmysqli, "BEGIN");
				for ($row=2; $row<=$highestRow; $row++) {
					$sClave = $worksheet->getCellByColumnAndRow(34, $row)->getValue();

					if ($sClave == $sClavePedimento) {
						$sTipoCambio = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
						$sTipoTasa = $worksheet->getCellByColumnAndRow(33, $row)->getValue();
						$sMarca = $worksheet->getCellByColumnAndRow(39, $row)->getValue();
						$sModelo = $worksheet->getCellByColumnAndRow(40, $row)->getValue();
						$sSerie = $worksheet->getCellByColumnAndRow(41, $row)->getValue();
						$sSerie = str_replace(' ', '', $sSerie);
						$sModelo = str_replace("'", "''", $sModelo);

						$no_factura = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$fecha_factura = get_fecha($worksheet->getCellByColumnAndRow(2, $row));
						$monto_factura = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
						$modena = 'MXN';
						$incoterm = $worksheet->getCellByColumnAndRow(37, $row)->getValue();
						$subdivision = 'N';
						$certificado = 'N';//(($sTipoTasa == '1')? 'N': 'S');
						$no_parte = $worksheet->getCellByColumnAndRow(24, $row)->getValue();
						$origen = get_pais($worksheet->getCellByColumnAndRow(10, $row));
						$vendedor = $origen;
						$fraccion = $worksheet->getCellByColumnAndRow(28, $row)->getValue();
						$descripcion = scanear_string($worksheet->getCellByColumnAndRow(25, $row)->getValue());
						$precio_partida = $worksheet->getCellByColumnAndRow(30, $row)->getValue();
						$umc = get_umc($worksheet->getCellByColumnAndRow(27, $row), $respuesta);
						$cantidad_umc = $worksheet->getCellByColumnAndRow(26, $row)->getValue();
						$cantidad_umt = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
						$preferencia = (($sTipoTasa == '2')? 'TL': 'N');
						$marca = (($sMarca == '')? 'S/M': $sMarca);
						$modelo = (($sModelo == '')? 'N/A': $sModelo);
						$submodelo = 'N/A';
						$serie = (($sSerie == '')? '': $sSerie.'/'). $no_parte;
						$descripcion_cove = $descripcion;

						if ($no_factura == '') {
							break;
						}

						if ($sClave == 'RT') {
							//$proporcion = round( $precio_partida / $monto_factura,4);
							$monto_factura = (double)$monto_factura / (double)$sTipoCambio;
							
							$modena = 'USD';
							$precio_partida = (double)$precio_partida / (double)$sTipoCambio;
							//$precio_partida = round($monto_factura,2) * round($proporcion,2) ;
							//error_log($precio_partida.'='.round($monto_factura,2).'*'.round($proporcion,2));
						}
						
						if ($respuesta['Codigo'] == 1) {
							$consulta = "INSERT INTO bodega.expos_plantilla_detalle
										 (id_plantilla
										 ,id_embarque
										 ,id_proveedor
										 ,no_factura
										 ,fecha_factura
										 ,monto_factura
										 ,moneda
										 ,incoterm
										 ,subdivision
										 ,certificado
										 ,no_parte
										 ,origen
										 ,vendedor
										 ,fraccion
										 ,descripcion
										 ,precio_partida
										 ,umc
										 ,cantidad_umc
										 ,cantidad_umt
										 ,preferencia
										 ,marca
										 ,modelo
										 ,submodelo
										 ,serie
										 ,descripcion_cove)
										 VALUES 
										 (".$nIdPlantilla."
										 ,".$nIdEmbarque."
										 ,'STCORP'
										 ,'".$no_factura."'
										 ,".(($fecha_factura == 'NULL')? $fecha_factura : "'".$fecha_factura."'")."
										 ,".$monto_factura."
										 ,'".$modena."'
										 ,'".$incoterm."'
										 ,'".$subdivision."'
										 ,'".$certificado."'
										 ,'".$no_parte."'
										 ,'".$origen."'
										 ,'".$vendedor."'
										 ,'".$fraccion."'
										 ,'".$descripcion."'
										 ,".$precio_partida."
										 ,'".$umc."'
										 ,".$cantidad_umc."
										 ,".$cantidad_umt."
										 ,'".$preferencia."'
										 ,'".$marca."'
										 ,'".$modelo."'
										 ,'".$submodelo."'
										 ,'".$serie."'
										 ,'".$descripcion_cove."')";
							
							$query = mysqli_query($cmysqli, $consulta);
							if (!$query) {
								$error=mysqli_error($cmysqli);
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']='Error al insertar registro en detalle. Por favor contacte al administrador del sistema.'.$consulta; 
								$respuesta['Error'] = ' ['.$error.']';
								
								mysqli_query($cmysqli, "ROLLBACK");
								break;
							}
						} else {
							break;
						}

						$nTotalRegistros += 1;
					}
				}
				
				break;
			}
		}

		if ($respuesta['Codigo'] == 1) {
			$consulta = "SELECT no_factura, cantidad_umt, COUNT(*) AS total_facturas
						 FROM bodega.expos_plantilla_detalle
						 WHERE id_plantilla=".$nIdPlantilla."
						 GROUP BY no_factura";

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar peso del embarque. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else { 
				while($row = mysqli_fetch_array($query)){
					$nNoFactura = $row['no_factura'];
					$nCantidadUMT = $row['cantidad_umt'];
					$nTotalFacturas = $row['total_facturas'];

					$nNewUMT = $nCantidadUMT / $nTotalFacturas;

					$consulta = "UPDATE bodega.expos_plantilla_detalle
								 SET cantidad_umt=".$nNewUMT."
								 WHERE id_plantilla=".$nIdPlantilla." AND
								       no_factura='".$nNoFactura."'";

					$query2 = mysqli_query($cmysqli, $consulta);
					if (!$query2) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al actualizar el peso de la factura ['.$nNoFactura.'] en la plantilla ['.$nIdPlantilla.']. Por favor contacte al administrador del sistema.'; 
						$respuesta['Error'] = ' ['.$error.']';

						mysqli_query($cmysqli, "ROLLBACK");
						break;
					}
				}
			}
		}

		if ($respuesta['Codigo']==1) {
			if ($nTotalRegistros > 0) {
				mysqli_query($cmysqli, "COMMIT");
				$respuesta['Mensaje']='Se han guardado todos los registros correctamente.';
			} else {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='El documento no contiene informaci&oacute;n para la referencia ['.$sReferencia.'], la clave del pedimento no corresponte.';
				$respuesta['Error'] = '';
				mysqli_query($cmysqli, "ROLLBACK");
			}
		}
		
		$respuesta['nTotalRegistros'] = $nTotalRegistros;
		$respuesta['sClavePedimento'] = $sClavePedimento;
		
		$datetime2 = new DateTime("now");
		$interval = date_diff($datetime1, $datetime2);
		
		$respuesta['Termino'] = $interval->format("%H:%I:%S");
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}

/* ..:: Obtenemos facturas duplicadas ::.. */
function get_facturas_duplicadas($sReferencia, $oFILES) {
	global $cmysqli, $respuesta;
	
	$aFacturasDuplicadas = Array();

	$consulta = "SELECT DISTINCT(a.no_factura) AS no_factura
				 FROM bodega.expos_plantilla_detalle AS a LEFT JOIN 
				      bodega.expos_plantilla_gral AS b ON b.id_plantilla = a.id_plantilla
				 WHERE b.referencia='".$sReferencia."' AND b.fecha_del IS NULL";
					
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar facturas. Por favor contacte al administrador del sistema.'.$consulta; 
		$respuesta['Error'] = ' ['.$error.']';
	} else {
		$aFacturas = Array();
		while($row = mysqli_fetch_array($query)){
			array_push($aFacturas, $row["no_factura"]);
		}

		if(count($aFacturas) > 0) {
			$objPHPExcel = PHPExcel_IOFactory::load($oFILES["oXls"]["tmp_name"]);
			foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {				
				$highestRow = $worksheet->getHighestRow();
				
				for ($row=2; $row<=$highestRow; $row++) {
					$no_factura = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
					
					if (in_array($no_factura, $aFacturas)) {
						if (!in_array($no_factura, $aFacturasDuplicadas)) { 
							array_push($aFacturasDuplicadas, $no_factura);
						}
					}
				}
				break;
			}
		}		
	}

	if(count($aFacturasDuplicadas) > 0) {
		$sReturnFacturas = implode(", ", $aFacturasDuplicadas);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Las siguientes facturas ya se encuentran dadas de alta, no se pueden subir facturas duplicadas. Facturas duplicadas: '.$sReturnFacturas; 
		$respuesta['Error'] = ' ['.$error.']';
	}

	return $aFacturasDuplicadas;
}

/* ..:: Obtener una fecha ::.. */
function get_fecha($oCell) {
	$sReturnFecha = 'NULL';

	if(PHPExcel_Shared_Date::isDateTime($oCell)){
		$FECHA = PHPExcel_Shared_Date::ExcelToPHPObject($oCell->getValue());
		$sReturnFecha = $FECHA->format("Y-m-d");
		$sReturnFecha = (string)$sReturnFecha;
	} else {
		$FECHA = $oCell->getValue();
		if ($FECHA != '') {
			$time = strtotime(str_replace('/', '-', $FECHA));;
			$sReturnFecha = date('Y-m-d',$time);
		}
	}

	return $sReturnFecha;
}

/* ..:: Consultamos la referencia del CASA ::.. */
function get_referencia_casa($sReferencia) {
	global $odbccasa, $respuesta, $sClavePedimento;

	$sReturnPedimento = '';
	$consulta = "SELECT a.NUM_REFE, a.NUM_PEDI, a.CVE_PEDI
					 FROM SAAIO_PEDIME a 
					 WHERE a.NUM_REFE='".$sReferencia."'";
		
	$result = odbc_exec($odbccasa, $consulta);
	if ($result==false){ 
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "Error al consultar informaci&oacute;n en sistema CASA. Por favor contacte al administrador del sistema.";
		$respuesta['Error'] = ' ['.odbc_error().']';
	} else {
		while (odbc_fetch_row($result)) { 
			$sReturnPedimento = odbc_result($result,"NUM_PEDI");
			$sClavePedimento = odbc_result($result,"CVE_PEDI");
			break;
		}
	}

	return $sReturnPedimento;
}

/* ..:: Consultamos la unidad de medida ::.. */
function get_umc($oCell) {
	global $odbccasa, $respuesta, $aUnidades;

	$sReturnData = '';
	$sCellValue = $oCell->getValue();

	$sExist = array_search($sCellValue, array_column($aUnidades, 'ABR_UNI'));

	if (false !== $sExist) { 
		$sReturnData = $aUnidades[$sExist]['NUM_UNI'];
		//error_log('Obtengo de Array');
	} else {
		//error_log('Consulta en CASA');
		$consulta = "SELECT a.NUM_UNI
					 FROM CTARC_UNIDAD a
					 WHERE a.ABR_UNI='".$sCellValue."'";
			
		$result = odbc_exec($odbccasa, $consulta);
		if ($result==false){ 
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar informaci&oacute;n clave unidad de medida en sistema CASA. Por favor contacte al administrador del sistema.";
			$respuesta['Error'] = ' ['.odbc_error().']';
		} else {
			while (odbc_fetch_row($result)) { 
				$sReturnData = odbc_result($result,"NUM_UNI");			
				array_push($aUnidades, array('ABR_UNI' => $sCellValue, 'NUM_UNI' => $sReturnData));
				break;
			}
		}
	}


	/*$consulta = "SELECT cve_pedimento
				 FROM bodega.unidadesmedida
				 WHERE um LIKE '%".$oCell->getValue()."%'";
					
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar unidad de medida. Por favor contacte al administrador del sistema.'.$consulta; 
		$respuesta['Error'] = ' ['.$error.']';
		break;
	} else {
		while($row = mysqli_fetch_array($query)){
			$sReturnData = $row["cve_pedimento"];
			break;
		}
	}*/

	return $sReturnData;
}

/* ..:: Obtenemos la clave del pais ::.. */
function get_pais($oCell) {
	global $odbccasa, $respuesta, $aPaises;

	$sReturnData = '';
	$sCellValue = $oCell->getValue();

	$sExist = array_search($sCellValue, array_column($aPaises, 'CVE_PAI2'));
	//error_log($sExist);
	if (false !== $sExist) {
		$sReturnData = $aPaises[$sExist]['CVE_PAI'];
	} else {
		//error_log('Consulta');
		$consulta = "SELECT a.CVE_PAI
					 FROM CTARC_PAISES a
					 WHERE a.CVE_PAI2='".$sCellValue."'";
			
		$result = odbc_exec($odbccasa, $consulta);
		if ($result==false){ 
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar informaci&oacute;n clave pais en sistema CASA. Por favor contacte al administrador del sistema.";
			$respuesta['Error'] = ' ['.odbc_error().']';
		} else {
			while (odbc_fetch_row($result)) { 
				$sReturnData = odbc_result($result,"CVE_PAI");			
				array_push($aPaises, array('CVE_PAI2' => $sCellValue, 'CVE_PAI' => $sReturnData));
				break;
			}
		}
	}

	return $sReturnData;
}

/**
 * Reemplaza todos los acentos por sus equivalentes sin ellos
 *
 * @param $string
 *  string la cadena a sanear
 *
 * @return $string
 *  string saneada
 */
function scanear_string($string) {
 
    $string = trim($string);
 
    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );
 
    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );
 
    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );
 
    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );
 
    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );
 
    $string = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C',),
        $string
    );
 
    //Esta parte se encarga de eliminar cualquier caracter extraño
    $string = str_replace(
        array("\\", "¨", "º", "-", "~",
             "#", "@", "|", "!", "\"",
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", "<", ";", ",", ":",
             "."),
        '',
        $string
    );
 
 
    return $string;
}
