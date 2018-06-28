<?php
require_once '../bower_components/PHPExcel/Classes/PHPExcel.php';
include ('../connect_dbsql.php');

//$id_cliente=1233;
$referencias=$_REQUEST['referencias'];
$referenciasb=array();
foreach($referencias as $referencia){
	array_push($referenciasb,"'".$referencia."'");
}
$respuesta['codigo']=-1;
$respuesta['mensaje']='Error desconocido contacte al administrador';
$respuesta['link']=$referenciasb;
$ref_rev=array();
$ref_pre=array();
foreach($referenciasb as $x){
	$consulta="SELECT referencia FROM precaptura_gral WHERE referencia=$x";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['mensaje']="Error 1 al generar plantilla, contacte al administrador. Error al realizar la consulta: ".$error;
		$respuesta['consulta']=$consulta;
		$response=json_encode($respuesta);
		exit($response);
	}
	if($query->num_rows==1){
		array_push($ref_pre,$x);
	}else{
		array_push($ref_rev,$x);
	}
}
$cref_rev="tbod.bodreferencia=".implode(" or tbod.bodreferencia=",$ref_rev);
$orderby_rev="tbod.bodreferencia=".implode("DESC, tbod.bodreferencia=",$referenciasb);
$orderby_rev.=", revd.factura, revd.regid";
$cref_pre="pg.referencia=".implode(" or tbod.bodreferencia=",$ref_pre);
$orderby_pre="referencia=".implode("DESC, referencia=",$referenciasb);
$orderby_pre.=", no_factura";
$nombre_reporte="Plantilla General Avanzada 5";

$consulta1="
	SELECT
		tbod.bodreferencia AS referencia,
		pro.proNom AS proveedor,
		revd.factura AS no_factura,
		'' AS fecha_factura,
		revdt.monto_factura AS monto_factura,
		'' AS moneda,
		revg.incoterm AS incoterm,
		'' AS subdivision,
		'' AS cer_origen,
		revd.noparte AS numero_parte,
		revd.origen AS pais_origen,
		'' AS pais_vendedor,
		revd.fraccion AS fraccion,
		revd.descripcion AS descripcion,
		revd.valor AS precio_partida,
		ump.num_uni AS umc,
		revd.cantidadfac AS cantidad_umc,
		(
			revd.cantidadfis * ump.fac_equi
		) AS cantidad_umt,
		'' AS preferencia_arancelaria,
		revd.marca AS marca,
		revd.modelo AS modelo,
		'' AS submodelo,
		revd.serie AS serie,
		revd.pesokgs AS pesokgs,
		ump.UNI_EQUI AS umt,
		fra_restric.fraccion as fra_restric,
		fra_restric_h.fraccion as fra_restric_h
	FROM
		bodega.tblbod AS tbod
	LEFT JOIN bodega.detalle_revision AS revd ON tbod.bodReferencia = revd.referencia
	LEFT JOIN bodega.revision_general AS revg ON revd.referencia = revg.referencia
	AND revd.factura = revg.factura
	LEFT JOIN bodega.unidadesmedida AS um ON revd.medida = um.um
	LEFT JOIN casa.ctarc_unidad AS ump ON um.cve_pedimento = ump.num_uni
	LEFT JOIN (
		SELECT
			sum(valor) AS monto_factura,
			referencia,
			factura
		FROM
			bodega.detalle_revision
		GROUP BY
			referencia,
			factura
	) AS revdt ON revd.referencia = revdt.referencia
	AND revd.factura = revdt.factura
	LEFT JOIN bodega.procli AS pro ON tbod.bodprocli = pro.proveedor_id
	LEFT JOIN bodega.fracciones_restric as fra_restric on revd.fraccion=fra_restric.fraccion and sector is not null
	LEFT JOIN bodega.fracciones_restric as fra_restric_h on revd.fraccion=fra_restric_h.fraccion and fra_restric_h.horario=1
	WHERE
		$cref_rev
";

$consulta2="
	SELECT
		pg.referencia AS referencia,
		pd.id_proveedor AS proveedor,
		pd.no_factura AS no_factura,
		date_format(
			pd.fecha_factura,
			'%d/%m/%Y'
		) AS fecha_factura,
		pd.monto_factura AS monto_factura,
		pd.moneda AS moneda,
		pd.incoterm AS incoterm,
		pd.subdivision AS subdivision,
		pd.certificado AS cer_origen,
		pd.no_parte AS numero_parte,
		pd.origen AS pais_origen,
		pd.vendedor AS pais_vendedor,
		pd.fraccion AS fraccion,
		pd.descripcion AS descripcion,
		pd.precio_partida AS precio_partida,
		pd.umc AS umc,
		pd.cantidad_umc AS cantidad_umc,
		pd.cantidad_umt AS cantidad_umt,
		pd.preferencia AS preferencia_arancelaria,
		pd.marca AS marca,
		pd.modelo AS modelo,
		pd.submodelo AS submodelo,
		pd.serie AS serie,
		0 AS pesokgs,
		'' AS umt,
		fra_restric.fraccion as fra_restric,
		fra_restric_h.fraccion as fra_restric_h
	FROM
		bodega.tblbod AS tbod
	LEFT JOIN bodega.precaptura_gral AS pg ON tbod.bodReferencia = pg.referencia
	LEFT JOIN bodega.precaptura_detalle AS pd ON pg.id_precaptura = pd.id_precaptura
	LEFT JOIN bodega.fracciones_restric as fra_restric on pd.fraccion=fra_restric.fraccion and sector is not null
	LEFT JOIN bodega.fracciones_restric as fra_restric_h on pd.fraccion=fra_restric_h.fraccion and fra_restric_h.horario=1
	WHERE
		$cref_pre
";

if(count($ref_rev)>0 and count($ref_pre)>0){
	$consulta = "
		$consulta1
		UNION ALL
		$consulta2	
		ORDER BY
			$orderby_pre";
}elseif(count($ref_rev)>0){
	$consulta="
		$consulta1
	ORDER BY
		$orderby_rev";
}elseif(count($ref_pre)>0){
	$consulta="
		$consulta2
	ORDER BY
		$orderby_pre";
}

$query = mysqli_query($cmysqli,$consulta);
if (!$query) {
	$error=mysqli_error($cmysqli);
	$respuesta['mensaje']="Error al generar plantilla, contacte al administrador. Error al realizar la consulta: ".$error;
	$respuesta['consulta']=$consulta;
	$response=json_encode($respuesta);
	exit($response);
}
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
while($row = $query->fetch_object()){ 
	$renglon++;
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$renglon, $row->proveedor)
		->setCellValue('B'.$renglon, $row->no_factura)
		->setCellValue('C'.$renglon, $row->fecha_factura)
		->setCellValue('D'.$renglon, $row->monto_factura)
		->setCellValue('E'.$renglon, $row->moneda)
		->setCellValue('F'.$renglon, $row->incoterm)
		->setCellValue('G'.$renglon, $row->subdivision)
		->setCellValue('H'.$renglon, $row->cer_origen)
		->setCellValue('I'.$renglon, $row->numero_parte)
		->setCellValue('J'.$renglon, $row->pais_origen)
		->setCellValue('K'.$renglon, $row->pais_vendedor)
		->setCellValue('L'.$renglon, $row->fraccion)
		->setCellValue('M'.$renglon, $row->descripcion)
		->setCellValue('N'.$renglon, $row->precio_partida)
		->setCellValue('O'.$renglon, $row->umc)
		->setCellValue('P'.$renglon, $row->cantidad_umc)
		->setCellValue('Q'.$renglon, $row->cantidad_umt)
		->setCellValue('R'.$renglon, $row->preferencia_arancelaria)
		->setCellValue('S'.$renglon, $row->marca)
		->setCellValue('T'.$renglon, $row->modelo)
		->setCellValue('U'.$renglon, $row->submodelo)
		->setCellValue('V'.$renglon, $row->serie)
		->setCellValue('W'.$renglon, $row->descripcion)
		->setCellValue('X'.$renglon, $row->referencia);
	$objPHPExcel->getActiveSheet()
        ->getComment('B'.$renglon)
        ->getText()->createTextRun($row->referencia);
	$objPHPExcel->getActiveSheet()
        ->getComment('Q'.$renglon)
        ->getText()->createTextRun("EQUIVALENCIA CANTIDAD FISICA EN UMT " . $row->umt . ",\r\n PESO KGS: ". $row->pesokgs . "\r\n");
	$objPHPExcel->getActiveSheet()
        ->getComment('Q'.$renglon)
		->setHeight("150px");
	if($row->fra_restric!=NULL){
		$objPHPExcel->getActiveSheet()->getStyle('L'.$renglon)->applyFromArray(
				array(
					'fill' 	=> array(
					'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
					'color'		=> array('argb' => 'FFFF3333')
					),
				)
			);
		$objPHPExcel->getActiveSheet()
        ->getComment('L'.$renglon)
        ->getText()->createTextRun('Fracción en anexo 10 sector 14 o 15');
	}
	if($row->fra_restric_h!=NULL){
		$objPHPExcel->getActiveSheet()->getStyle('L'.$renglon)->applyFromArray(
				array(
					/*'fill' 	=> array(
						'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
						'color'		=> array('argb' => 'FFFF3333')
					),*/
					'borders' => array(
								'top'	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color'=> array('argb' => 'EF7F1A00')),
								'right'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color'=> array('argb' => 'EF7F1A00')),
								'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color'=> array('argb' => 'EF7F1A00')),
								'left'		=> array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color'=> array('argb' => 'EF7F1A00'))
					)
				)
			);
		$objPHPExcel->getActiveSheet()
        ->getComment('L'.$renglon)
        ->getText()->createTextRun('Fracción con restricción de horario');
	}
	$encontro_fac=false;
	foreach ($facturas as $factura) {
		if($factura[0]==$row->referencia and $factura[1]==$row->no_factura){
			$cons_factp=$factura[2];
			$encontro_fac=true;
			break;
		}
	}
	if($encontro_fac==false){
		array_push($facturas,array($row->referencia,$row->no_factura,$cons_fact));
		$cons_factp=$cons_fact;
		$cons_fact++;
	}
	$objPHPExcel->setActiveSheetIndex(1)
		->setCellValue('A'.$renglon, $cons_factp)
		->setCellValue('B'.$renglon, "='Plantilla General Avanzada 5'!L".$renglon)
		->setCellValue('C'.$renglon, "='Plantilla General Avanzada 5'!M".$renglon)
		->setCellValue('D'.$renglon, "='Plantilla General Avanzada 5'!J".$renglon)
		->setCellValue('E'.$renglon, "='Plantilla General Avanzada 5'!K".$renglon)
		->setCellValue('F'.$renglon, $row->umt)
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
$respuesta['codigo']='1';
$respuesta['mensaje']="Plantilla generada con exito";
$respuesta['link']="<a href='descarga_plantilla.php?file=$nfile' target='_blank'><span class='glyphicon glyphicon-floppy-save' aria-hidden='true'></span>Descargar</a>";
$respuesta['consulta']=$consulta;
$response=json_encode($respuesta);
exit($response);
