 <?php
include_once('./../../../checklogin.php');
include('./../../../bower_components/PHPExcel/Classes/PHPExcel/IOFactory.php');
 if($loggedIn == false){ 
	echo '500';
}else{
	if (isset($_FILES) && !empty($_FILES)) {
		$files = $_FILES;
		//$respuesta['HTML'] = $files["xlsLayout"]["tmp_name"];
		
		$respuesta['Codigo'] = '1';
		$respuesta['HTML']='<div class="row"><div class="col-md-12"><table class="table table-bordered table-striped table-condensed cf" width="100%">';
		$objPHPExcel = PHPExcel_IOFactory::load($files["xlsLayout"]["tmp_name"]);
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet)
		{
			$highestRow = $worksheet->getHighestRow();
			$respuesta['HTML'].='<thead class="cf bg-blue">
									<tr>
										<th>NUM_PARTE (Alfanum&eacute;rico 25)</th>
										<th>FRACCI&Oacute;N (Num&eacute;rico 8)</th>
										<th>FRACCI&Oacute;N_10 (Num&eacute;rico 10)</th>
										<th>DESCRIPCI&Oacute;N (Alfanum&eacute;rico 200)</th>
										<th>UNIDAD_MEDIDA (Alfanum&eacute;rico 20)</th>
									</tr>
								</thead>';
			$Clasificaciones = array();
			for ($row=2; $row <= $highestRow; $row++){
				
				$errNumParte = false;$errFraccion = false;$errFraccion10 = false;
				$errDescripcion = false;$errUM = false;
				
				$NUM_PARTE = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
				$FRACCION = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
				$FRACCION10 = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
				$DESCRIPCION = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
				$UM = $worksheet->getCellByColumnAndRow(4, $row)->getValue();			
				//Numero Parte
				if (!isset($NUM_PARTE) || empty($NUM_PARTE)) {
					$errNumParte = true;$respuesta['Codigo'] = '2';
				}else{ 
					if (strlen(trim($NUM_PARTE)) > 25){$errNumParte = true;$respuesta['Codigo'] = '2';} 
				}
				//Fraccion
				if (!isset($FRACCION) || empty($FRACCION)) {
					$errFraccion = true;$respuesta['Codigo'] = '2';
				}else{ 
					if (strlen(trim($FRACCION)) != 8){$errFraccion = true;$respuesta['Codigo'] = '2';} 
				}
				//Fraccion10
				if (strlen(trim($FRACCION10)) > 10){$errFraccion10 = true;$respuesta['Codigo'] = '2';}
				//Descripcion
				if (!isset($DESCRIPCION) || empty($DESCRIPCION)) {
					$errDescripcion = true; $respuesta['Codigo'] = '2';
				}else{ 
					if (strlen(trim($DESCRIPCION)) > 200){$errDescripcion = true; $respuesta['Codigo'] = '2';} 
				}
				//UM
				if (!isset($UM) || empty($UM)) {
					$UM = 'PZAS';
				}else{ 
					if (strlen(trim($UM)) > 20){
						$errUM = true;$respuesta['Codigo'] = '2';
					}
				}
				if($respuesta['Codigo'] == '1'){
					$RowCaja = array($NUM_PARTE,$FRACCION,$FRACCION10,$DESCRIPCION,$UM);
					array_push($Clasificaciones,$RowCaja);
					$_SESSION['aClasificaciones'] = $Clasificaciones;
				}else{
					$_SESSION['aClasificaciones'] = array();
				}					
				$respuesta['HTML'].="<tr>";
				$respuesta['HTML'] .= '<td'.($errNumParte?'class="alert alert-danger"':'').'>'.$NUM_PARTE.'</td>';
				$respuesta['HTML'] .= '<td '.($errFraccion?'class="alert alert-danger"':'').'>'.$FRACCION.'</td>';
				$respuesta['HTML'] .= '<td '.($errFraccion10?'class="alert alert-danger"':'').'>'.$FRACCION10.'</td>';
				$respuesta['HTML'] .= '<td '.($errDescripcion?'class="alert alert-danger"':'').'>'.$DESCRIPCION.'</td>';
				$respuesta['HTML'] .= '<td '.($errUM?'class="alert alert-danger"':'').'>'.$UM.'</td>';
				$respuesta['HTML'] .= "</tr>";
			}
			break;
		}
		$respuesta['HTML'] .= '</table></div></div>';
		
		
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']=(534-1);
		$respuesta['Error']= '';
	}
	// echo $html;  
	 echo json_encode($respuesta);  
}
 
 

 ?>  