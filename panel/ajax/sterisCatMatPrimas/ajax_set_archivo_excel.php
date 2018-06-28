<?php
include_once('./../../../checklogin.php');
include('./../../../connect_dbsql.php');

include('./../../../bower_components/PHPExcel/Classes/PHPExcel/IOFactory.php');
$url_excel = '\\\\192.168.1.126\inetpub\wwwroot\monitorpruebas\panel\ajax\sterisCatMatPrimas\\';

if ($loggedIn == false){
	echo '500';
}else{		
	if (isset($_POST['bPrimeraVez']) && !empty($_POST['bPrimeraVez'])) {
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$bPrimeraVez = $_POST['bPrimeraVez'];   
		$nRegActual = $_POST['nRegActual'];
		
		$respuesta['bPrimeraVez'] = $bPrimeraVez;		
		$bPrimeraVez = ($bPrimeraVez === 'true');
		
		//***********************************************************//
		
		$datetime1 = new DateTime("now");
		
		$fecha_registro = date("Y-m-d H:i:s");
					
		//***********************************************************//
		
		$destination = $url_excel . 'sterisCatMatPrimas.tmp';	
		$nRegInsert = 0;
		$nRegFor = 0;
		$nTotalRegistros = 0;
		$sInserted = '';
		
		if ($bPrimeraVez) {	
			unlink($destination);
			
			$filename = $_FILES["oXls"]["tmp_name"];			
			move_uploaded_file($filename, $destination);	
		}
		
		if ($respuesta['Codigo'] == 1) {
			$objPHPExcel = PHPExcel_IOFactory::load($destination);
		
			$datetime2 = new DateTime("now");
			$interval = date_diff($datetime1, $datetime2);
			$respuesta['Lectura'] = $interval->format("%H:%I:%S");
						
			$datetime1 = new DateTime("now");
			
			foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
				
				if ($bPrimeraVez) {
					$consulta = "TRUNCATE TABLE bodega.steris_catalogo_materis_primas";
					
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al eliminar registro catalogo. Por favor contacte al administrador del sistema.'; 
						$respuesta['Error'] = ' ['.$error.']';
						
						break;
					}
				}
				
				$highestRow = $worksheet->getHighestRow();
				$nTotalRegistros = $highestRow - 1;
				
				mysqli_query($cmysqli, "BEGIN");
				for ($row=$nRegActual; $row<=$highestRow; $row++) {
					$intEmpresa = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
					$strMaterial = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
					$strTipo = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
					$strNombre = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					$strNombreIng = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
					$intUnidad = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
					$strUnidad = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
					$intFraccion = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
					$strHTS = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
					$strGrupo = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
					$strPaisOrigen = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
					$dblPesoKgs = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
										
					$dblPcioMats = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
					$dblPcioVA = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
					$strAFNumSerie = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
					$strAFNumParte = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
					$strAFMarca = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
					$strAFModelo = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
					$dblTasaImpto = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
					$strTipoTasa = $worksheet->getCellByColumnAndRow(19, $row)->getValue();
					
					if (!is_numeric($dblPesoKgs)) { $dblPesoKgs = '0'; }
					if (!is_numeric($dblPcioMats)) { $dblPcioMats = '0'; }					
					if (!is_numeric($dblPcioVA)) { $dblPcioVA = '0'; }
					if (!is_numeric($dblTasaImpto)) { $dblTasaImpto = '0'; }
					
					$datFechaMod = '';
					if(PHPExcel_Shared_Date::isDateTime($worksheet->getCellByColumnAndRow(20, $row))){
						$FECHA = PHPExcel_Shared_Date::ExcelToPHPObject($worksheet->getCellByColumnAndRow(20, $row)->getValue());
						$datFechaMod = $FECHA->format("Y-m-d H:i:s");
						$datFechaMod = (string)$datFechaMod;
					} else {
						$FECHA = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
						if ($FECHA != '') {
							$time = strtotime($FECHA);
							$datFechaMod = date('Y-m-d H:i:s',$time);
						}
					}				
					$ACTIVOFIJO = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
					$sInserted .= $ACTIVOFIJO;
					if ($ACTIVOFIJO === 'FALSE') {
						$ACTIVOFIJO = 0;
					} else {
						$ACTIVOFIJO = 1;
					}
					
					$ACTIVOFIJO = 0;
					// $FECHAPXM = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
					// $PLAZOPXM = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
					
					/* Reemplazando caracteres no validos */
					$strNombre = scanear_string($strNombre);
					$strNombreIng = scanear_string($strNombreIng);
						
					$consulta = "INSERT INTO bodega.steris_catalogo_materis_primas
								 (intEmpresa
								 ,strMaterial
								 ,strTipo
								 ,strNombre
								 ,strNombreIng
								 ,intUnidad
								 ,strUnidad
								 ,intFraccion
								 ,strHTS
								 ,strGrupo
								 ,strPaisOrigen
								 ,dblPesoKgs
								 ,dblPcioMats
								 ,dblPcioVA
								 ,strAFNumSerie
								 ,strAFNumParte
								 ,strAFMarca
								 ,strAFModelo
								 ,dblTasaImpto
								 ,strTipoTasa";
					if ($datFechaMod != '') {
						$consulta .= ",datFechaMod";
					}
					$consulta .= ",ACTIVOFIJO)
								 VALUES 
								 (".$intEmpresa."
								 ,'".$strMaterial."'
								 ,'".$strTipo."'
								 ,'".$strNombre."'
								 ,'".$strNombreIng."'
								 ,".$intUnidad."
								 ,'".$strUnidad."'
								 ,".$intFraccion."
								 ,'".$strHTS."'
								 ,'".$strGrupo."'
								 ,'".$strPaisOrigen."'
								 ,".$dblPesoKgs."
								 ,".$dblPcioMats."
								 ,".$dblPcioVA."
								 ,'".$strAFNumSerie."'
								 ,'".$strAFNumParte."'
								 ,'".$strAFMarca."'
								 ,'".$strAFModelo."'
								 ,".$dblTasaImpto."
								 ,'".$strTipoTasa."'";
					if ($datFechaMod != '') {
						$consulta .= ",'" . $datFechaMod . "'";
					}
					$consulta .= ",".$ACTIVOFIJO.")";
					
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al insertar registro catalogo. Por favor contacte al administrador del sistema.'.$consulta; 
						$respuesta['Error'] = ' ['.$error.']';
						$respuesta['Consulta'] = ' ['.$consulta.']';
						
						$respuesta['bRollback'] = 'True';
						mysqli_query($cmysqli, "ROLLBACK");
						break;
					} else {
						$nRegInsert++;
					}
					
					$nRegFor++;
					
					if ($nRegFor >= 500) {
						break;
					}
				}
				
				if ($respuesta['Codigo']==1) {
					mysqli_query($cmysqli,  "COMMIT");
					$respuesta['Mensaje']='Se han guardado todos los registros correctamente.';
				}
				break;
			}
			
			$respuesta['nRegActual'] = ($nRegActual + $nRegFor);
			$respuesta['nTotalRegistros'] = $nTotalRegistros;
			$respuesta['sInserted'] = $sInserted;
			
			$respuesta['nRegInsert'] = $nRegInsert;
			$respuesta['nRegFor'] = $nRegFor;
			
			$datetime2 = new DateTime("now");
			$interval = date_diff($datetime1, $datetime2);
			
			$respuesta['Termino'] = $interval->format("%H:%I:%S");
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
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
