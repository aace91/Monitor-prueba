<?php
include_once('./../../../checklogin.php');
include('./../../../url_archivos.php');
include('./../../../bower_components/PHPExcel/Classes/PHPExcel.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['facturas_numparte']) && !empty($_POST['facturas_numparte'])) {  
		$respuesta['Codigo']=1;
		$aFacNumParte = json_decode($_POST['facturas_numparte']);
		$bNumFac_UUID = $_POST['numfact_uui'];
		$nombre_reporte = "Plantilla General Avanzada 5";

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Departamento de Sistemas")
									 ->setLastModifiedBy("Departamento de Sistemas")
									 ->setTitle($nombre_reporte);
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', 'Proveedor')
					->setCellValue('B1', 'No. Factura')
					->setCellValue('C1', 'Fecha factura')
					->setCellValue('D1', 'Monto factura')
					->setCellValue('E1', 'Moneda')
					->setCellValue('F1', 'Incoterm')
					->setCellValue('G1', 'Subdivision')
					->setCellValue('H1', 'Certificado Origen')
					->setCellValue('I1', 'Numero parte')
					->setCellValue('J1', 'Pais Origen')
					->setCellValue('K1', 'Pais Vendedor')
					->setCellValue('L1', 'Fraccion')
					->setCellValue('M1', 'Descripcion')
					->setCellValue('N1', 'Precio partida')
					->setCellValue('O1', 'UMC')
					->setCellValue('P1', 'Cantidad UMC (Cantidad factura)')
					->setCellValue('Q1', 'Cantidad UMT (Cantidad fisica)')
					->setCellValue('R1', 'Preferencia arancelaria')
					->setCellValue('S1', 'Marca')
					->setCellValue('T1', 'Modelo')
					->setCellValue('U1', 'Submodelo')
					->setCellValue('V1', 'Serie')
					->setCellValue('W1', 'Descripcion COVE')
					->setCellValue('X1', 'Referencia');
		$objPHPExcel->getActiveSheet()->setTitle($nombre_reporte);
		$oSheetFracciones = new PHPExcel_Worksheet($objPHPExcel, 'Fracciones');
		$objPHPExcel->addSheet($oSheetFracciones);
		$objPHPExcel->setActiveSheetIndex(1)
					->setCellValue('A1', 'Consecutivo Factura')
					->setCellValue('B1', 'Fracción')
					->setCellValue('C1', 'Descripción mercancía')
					->setCellValue('D1', 'País de origen')
					->setCellValue('E1', 'País vendedor')
					->setCellValue('F1', 'Unidad de la tarifa')
					->setCellValue('G1', 'Cantidad de la tarifa')
					->setCellValue('H1', 'Unidad según factura')
					->setCellValue('I1', 'Cantidad según factura')
					->setCellValue('J1', 'Clave indentificador 1')
					->setCellValue('K1', 'Complemento 1, Identificador 1')
					->setCellValue('L1', 'Complemento 2, Identificador 1')
					->setCellValue('M1', 'Clave identificador 2')
					->setCellValue('N1', 'Complemento 1, Identificador 2')
					->setCellValue('O1', 'Complemento 2, Identificador 2')
					->setCellValue('P1', 'Porcentaje del IVA')
					->setCellValue('Q1', 'Moneda')
					->setCellValue('R1', 'Valor Factura')
					->setCellValue('S1', 'Observaciones Partida');
		$renglon=1;
		$facturas=array();
		$cons_fact=1;
		//while($row = $query->fetch_object()){ $aFacNumParte
		for($i = 0; $i<count($aFacNumParte); $i++){
			$renglon++;
			$NumFact  = ($bNumFac_UUID == '1' ? $aFacNumParte[$i]->UUID : $aFacNumParte[$i]->numero_factura);
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$renglon, $aFacNumParte[$i]->id_proveedor)
				->setCellValue('B'.$renglon, $NumFact)
				->setCellValue('C'.$renglon, $aFacNumParte[$i]->fecha_factura)
				->setCellValue('D'.$renglon, $aFacNumParte[$i]->monto_factura)
				->setCellValue('E'.$renglon, $aFacNumParte[$i]->moneda)
				->setCellValue('F'.$renglon, $aFacNumParte[$i]->incoterm)
				->setCellValue('G'.$renglon, $aFacNumParte[$i]->subdivision)
				->setCellValue('H'.$renglon, $aFacNumParte[$i]->certificado_origen)
				->setCellValue('I'.$renglon, $aFacNumParte[$i]->numero_parte)
				->setCellValue('J'.$renglon, $aFacNumParte[$i]->pais_origen)
				->setCellValue('K'.$renglon, $aFacNumParte[$i]->pais_vendedor)
				->setCellValue('L'.$renglon, $aFacNumParte[$i]->fraccion)
				->setCellValue('M'.$renglon, $aFacNumParte[$i]->descripcion)
				->setCellValue('N'.$renglon, $aFacNumParte[$i]->precio_partida)
				->setCellValue('O'.$renglon, $aFacNumParte[$i]->UMC)
				->setCellValue('P'.$renglon, $aFacNumParte[$i]->cantidad_UMC)
				->setCellValue('Q'.$renglon, $aFacNumParte[$i]->cantidad_UMT)
				->setCellValue('R'.$renglon, $aFacNumParte[$i]->preferencia_arancelaria)
				->setCellValue('S'.$renglon, $aFacNumParte[$i]->marca)
				->setCellValue('T'.$renglon, $aFacNumParte[$i]->modelo)
				->setCellValue('U'.$renglon, $aFacNumParte[$i]->submodelo)
				->setCellValue('V'.$renglon, $aFacNumParte[$i]->serie)
				->setCellValue('W'.$renglon, $aFacNumParte[$i]->descripcion_cove)
				->setCellValue('X'.$renglon, $aFacNumParte[$i]->referencia);
			/*$objPHPExcel->getActiveSheet()
				->getComment('B'.$renglon)
				->getText()->createTextRun($row->referencia);
			$objPHPExcel->getActiveSheet()
				->getComment('Q'.$renglon)
				->getText()->createTextRun("EQUIVALENCIA CANTIDAD FISICA EN UMT " . $row->umt . ",\r\n PESO KGS: ". $row->pesokgs . "\r\n");
			$objPHPExcel->getActiveSheet()
				->getComment('Q'.$renglon)
				->setHeight("150px");
			*/
			$encontro_fac=false;
			foreach ($facturas as $factura) {
				if($factura[0]==$aFacNumParte[$i]->referencia and $factura[1]==$aFacNumParte[$i]->numero_factura){
					$cons_factp=$factura[2];
					$encontro_fac=true;
					break;
				}
			}
			if($encontro_fac==false){
				array_push($facturas,array($aFacNumParte[$i]->referencia,$aFacNumParte[$i]->numero_factura,$cons_fact));
				$cons_factp=$cons_fact;
				$cons_fact++;
			}
			$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('A'.$renglon, $cons_factp)
				->setCellValue('B'.$renglon, "='Plantilla General Avanzada 5'!L".$renglon)
				->setCellValue('C'.$renglon, "='Plantilla General Avanzada 5'!M".$renglon)
				->setCellValue('D'.$renglon, "='Plantilla General Avanzada 5'!J".$renglon)
				->setCellValue('E'.$renglon, "='Plantilla General Avanzada 5'!K".$renglon)
				->setCellValue('F'.$renglon, $aFacNumParte[$i]->UMC)
				->setCellValue('G'.$renglon, "='Plantilla General Avanzada 5'!Q".$renglon)
				->setCellValue('H'.$renglon, "='Plantilla General Avanzada 5'!O".$renglon)
				->setCellValue('I'.$renglon, "='Plantilla General Avanzada 5'!P".$renglon)
				->setCellValue('J'.$renglon, "MA")
				->setCellValue('K'.$renglon, "")
				->setCellValue('L'.$renglon, "")
				->setCellValue('M'.$renglon, "")
				->setCellValue('N'.$renglon, "")
				->setCellValue('O'.$renglon, "")
				->setCellValue('P'.$renglon, "16")
				->setCellValue('Q'.$renglon, "='Plantilla General Avanzada 5'!E".$renglon)
				->setCellValue('R'.$renglon, "='Plantilla General Avanzada 5'!N".$renglon)
				->setCellValue('S'.$renglon, "='Plantilla General Avanzada 5'!I".$renglon);
		}
		$objPHPExcel->setActiveSheetIndex(0);
		foreach(range('A','X') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->getColumnDimension();
		foreach(range('A','S') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}
		$objPHPExcel->getActiveSheet()->getColumnDimension();
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$tmpName = tempnam(sys_get_temp_dir(), 'data');
		$nfile="$tmpName.xlsx";
		$objWriter->save($nfile);
		$respuesta['Codigo']='1';
		//$respuesta['Mensaje']="Plantilla generada con exito";
		$respuesta['Mensaje']="<a href='descarga_plantilla.php?file=$nfile' target='_blank'><span class='glyphicon glyphicon-floppy-save' aria-hidden='true'></span>Descargar</a>";
		//$response=json_encode($respuesta);
		//exit($response);
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
