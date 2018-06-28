<?php
	
	function generar_archivos_pdf_edocuments($referencia,$tipo_consulta){
		global $odbccasa;
		$respuesta['Codigo'] = 1;
		$respuesta['Mensaje'] = "";
		$isRelacionFac = false;
		$directorio = "\\\\192.168.1.107\\gabdata\\CASAWIN\\cove\\ventanilla\\edocument\\".$referencia."\\";
		if(!file_exists($directorio)){
			$query = "SELECT num_refeo FROM SAAIO_PEDIME where num_refe='".$referencia."'";
			$result = odbc_exec ($odbccasa, $query);
			if (!$result){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Generar PDF eDocuments :: Error al consultar la referencia. [".odbc_error($odbccasa).']['.$query.']'."\r\n";
				return $respuesta;
			}
			while(odbc_fetch_row($result)){
				$refo=odbc_result($result,"num_refeo");
			}
			$directorio = "\\\\192.168.1.107\\gabdata\\CASAWIN\\cove\\ventanilla\\edocument\\".$refo."\\";
			if(!file_exists($directorio)){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Generar PDF eDocuments :: La referencia [".$referencia.",".$refo."] no existe en el directorio de los eDocuments."."\r\n";
				return $respuesta;
			}
		}
		$respuesta['aeDocuments'] = array(); $aComprobantes = array();
		$dir_edocuments  = scandir($directorio);
		for($i=0; $i<count($dir_edocuments); $i++){
			$bErrFile = false;
			//Numero Operacion Digitalizacion
			if(strpos(trim($dir_edocuments[$i]),'_DigitalResult.xml') !== false) {
				$numeroOperacion = '';$eDocument = '';$TextoXML = '';
				//Leer solamente el texto XML ya que el archivo DigitalResult.xml cuenta con texto extra 
				//y no compatible con la estructura con el new DOMDocument();
				$sXML = file_get_contents($directorio.$dir_edocuments[$i],false);
				$aXML = explode("\n", $sXML);
				for($f=0;$f<($aXML);$f++){
					if (strpos($aXML[$f], "<?xml") !== false) {
						$TextoXML = trim($aXML[$f]);//.$aXML[$i];
						break;
					}
				}
				if($TextoXML != ""){
					$xmlResp = new DOMDocument();
					$xmlResp->preserveWhiteSpace = false;
					$xmlResp->loadXML($TextoXML);
					
					if($xmlResp->getElementsByTagName("tieneError")->item(0)->nodeValue == 'false'){
						//Obtener numero de operacion
						$numeroOperacion = $xmlResp->getElementsByTagName("numeroOperacion")->item(0)->nodeValue;
						$aNomDigPet = explode('_',$dir_edocuments[$i]);
						//Obtener la respuesta del Numero de Operacion obtenido
						for($j=0; $j<count($dir_edocuments); $j++){
							if($dir_edocuments[$j] == $aNomDigPet[0].'_ConsultaResult.xml') {
								//Leer respuesta de la consulta del Numero de Operacion
								$TextoXMLConRes = '';
								$sXMLConRes = file_get_contents($directorio.$dir_edocuments[$j],false);
								$aXMLConRes = explode("\n", $sXMLConRes);
								for($f;$f<($aXMLConRes);$f++){
									if (strpos($aXMLConRes[$f], "<?xml") !== false) {
										$TextoXMLConRes = trim($aXMLConRes[$f]);
										break;
									}
								}
								if($TextoXMLConRes != ""){
									//tenemos el XML en texto de la respuesta de la consulta
									$xmlRespCons = new DOMDocument();
									$xmlRespCons->preserveWhiteSpace = false;
									$xmlRespCons->loadXML($TextoXMLConRes);
									
									if($xmlRespCons->getElementsByTagName("tieneError")->item(0)->nodeValue == 'false'){
										
										$dtCreado = $xmlRespCons->getElementsByTagName("Created")->item(0)->nodeValue;
										$eDocument = $xmlRespCons->getElementsByTagName("eDocument")->item(0)->nodeValue;
										$numeroDeTramite = $xmlRespCons->getElementsByTagName("numeroDeTramite")->item(0)->nodeValue;
										$cadenaOriginal = base64_encode(trim($xmlRespCons->getElementsByTagName("cadenaOriginal")->item(0)->nodeValue));
										
										//Leer la peticion de digitalizacion para obtener los datos de envio
										for($k=0; $k<count($dir_edocuments); $k++){
											//error_log($dir_edocuments[$j].' = '.$aNomDigPet[0].'_Digital.xml');
											if($dir_edocuments[$k] == $aNomDigPet[0].'_Digital.xml') {
												
												//error_log('Documento Digital...');
												$xmlDig = new DOMDocument();
												$xmlDig->preserveWhiteSpace = false;
												$xmlDig->load($directorio.$dir_edocuments[$k]);
												
												$TipoDocumento = tipos_documento_edocuments_ventanilla($xmlDig->getElementsByTagName("idTipoDocumento")->item(0)->nodeValue);
												$SelloDigitalSol = $xmlDig->getElementsByTagName("firma")->item(0)->nodeValue;
												$nombreDocumento = $xmlDig->getElementsByTagName("nombreDocumento")->item(0)->nodeValue.'.pdf';
												$RFC = $xmlDig->getElementsByTagName("Username")->item(0)->nodeValue;
												$rfcConsulta = $xmlDig->getElementsByTagName("rfcConsulta")->item(0)->nodeValue;
												$NomRFC = usuario_generacion_edocument($RFC);
												
												//Consultar el AA o Cliente que genero el edocument\\
												
												
												//GENERAR EL PDF DEL EDOCUMENT
												$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);				
												$pdf->SetCreator(PDF_CREATOR);
												$pdf->SetAuthor('Grupo Aduanero Del Bravo');
												$pdf->SetTitle($eDocument);
												$pdf->SetSubject($eDocument);
												$pdf->SetKeywords($eDocument);
												$pdf->setPrintHeader(true);
												$pdf->setPrintFooter(true);
												$pdf->SetHeaderData(PDF_HEADER_LOGO, 15, '', '');
												$pdf->AddPage();
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);				
												$pdf->SetFont('helvetica', '', 14);
												$pdf->SetColor('text',0,77,153);
												$pdf->Cell(0,10, 'ACUSE DIGITALIZACIÓN DE DOCUMENTOS' , 0, 1, 'C',0,'',0);
												$pdf->SetColor('text',0,0,0);
												$pdf->SetFont('helvetica', '', 10);
												$pdf->Cell(100,6, 'Folio de la solicitud:' , 0, 0, 'R',0,'',0);
												$pdf->Cell(0,6, $numeroDeTramite , 0, 1, 'C',0,'',0);
												$pdf->Cell(0,0, '' , 0, 1, 'R',0,'',0);
												$pdf->Cell(23,6, 'Estimado(a):' , 0, 0, 'L',0,'',0);
												$pdf->Cell(0,6, $NomRFC , 0, 1, 'L',0,'',0);
												$pdf->Cell(10,6, 'RFC:' , 0, 0, 'L',0,'',0);
												$pdf->Cell(0,6, $RFC , 0, 1, 'L',0,'',0);
												$pdf->Cell(0,0, '' , 0, 1, 'R',0,'',0);
												$date = date_create_from_format('Y-m-d H:i:s',str_replace('Z','',str_replace('T',' ',$dtCreado)));
												$sHTML = '
												<table cellspacing="0" cellpadding="2">
													<tr>
														<td>Siendo las '.date('H:m', $date->getTimestamp()).' del '.date('d/m/Y', $date->getTimestamp()).', se tiene por recibida y atendida su solicitud de registro de Documentos Digitalizados presentado a través de la ventanilla unica.</td>
													</tr>
												</table>';
												$pdf->writeHTML($sHTML, false, false, true, false, '');
												$pdf->Cell(0,0, '' , 0, 1, 'R',0,'',0);
												$pdf->SetFont('helvetica', 'B', 10);
												$pdf->Cell(0,0, 'Los datos de cada documento son los siguientes:' , 0, 1, 'C',0,'',0);
												$pdf->Cell(0,0, '' , 0, 1, 'R',0,'',0);
												$sHTML = '
												<table border="1" cellspacing="0" cellpadding="2">
													<tr>
														<td width="210">Operación</td>
														<td width="310">Registro de documentos digitalizados</td>
													</tr>
												</table>';
												$pdf->writeHTML($sHTML, false, false, true, false, '');
												$pdf->SetFont('helvetica', '', 10);
												$sHTML = '
												<table border="1" cellspacing="0" cellpadding="2">
													<tr>
														<td width="210">Número e_document</td>
														<td width="310">'.$eDocument.'</td>
													</tr>
													<tr>
														<td width="210">Tipo de documento</td>
														<td width="310">'.$TipoDocumento.'</td>
													</tr>
													<tr>
														<td width="210">Nombre del documento</td>
														<td width="310">'.$nombreDocumento.'</td>
													</tr>
													<tr>
														<td width="210">Fecha de registro(En la que se dio de alta el registro de documentos digitalizados)</td>
														<td width="310">'.date('d', $date->getTimestamp()).' de '.nombre_mes(date('m', $date->getTimestamp())).' del '.date('Y', $date->getTimestamp()).'</td>
													</tr>
													<tr>
														<td width="210">Cadena Original</td>
														<td width="310">'.$cadenaOriginal.'</td>
													</tr>
													<tr>
														<td width="210">Sello digital del solicitante(del documento)</td>
														<td width="310">'.$SelloDigitalSol.'</td>
													</tr>
													<tr>
														<td width="210">Sello digital de la ventanilla &uacute;nica</td>
														<td width="310">&nbsp;<br><br></td>
													</tr>
													<tr>
														<td width="210">Leyenda</td>
														<td width="310">Tiene 90 d&iacute;as a partir de esta fecha para utilizar su documento digitalizado, si en ese tiempo no lo utiliza, ser&aacute; dado de baja del sistema.</td>
													</tr>
												</table>';
												$pdf->writeHTML($sHTML, false, false, true, false, '');
												$pdf->Cell(0,23, '' , 0, 1, 'R',0,'',0);
												$style = array('width' => 0.5, 'color' => array(0, 0, 0));
												$pdf->Line(10, 230, 200, 230, $style);
												$sHTML = '
												<table cellspacing="0" cellpadding="2">
													<tr>
														<td>Los datos personales suministrados a través de las solicitudes, promociones, trámites, consultas y pagos, hechos por medios 
															electrónicos e impresos, serán protegidos, incorporados y tratados en el sistema de datos personales de la
															“Ventanilla Digital” acorde con la Ley Federal de Transparencia y Acceso a la Información Pública Gubernamental y las
															demás disposiciones legales aplicables; y podrán ser transmitidos a las autoridades competentes en materia de comercio
															exterior, al propio titular de la información, o a terceros, en este último caso siempre que las disposiciones aplicables
															contemplen dichas transferencia.</td>
													</tr>
												</table>';
												$pdf->writeHTML($sHTML, false, false, true, false, '');
												if($tipo_consulta == 'expediente'){
													$pdf->Output('expediente_docs/'.$referencia.'/'.$eDocument.'.pdf', 'F');
													array_push($respuesta['aeDocuments'],'expediente_docs/'.$referencia.'/'.$eDocument.'.pdf');
												}else{
													$pdf->Output($eDocument.'.pdf', 'F');
													array_push($respuesta['aeDocuments'],$eDocument.'.pdf');
												}
											}
										}
									}else{
										$respuesta['Codigo'] = -1;
										$respuesta['Mensaje'] .= "Generar PDF eDocuments :: Error en la respuesta de ventanilla unica al generar el eDocument.[".json_encode($xmlRespCons)."][".$dir_edocuments[$i].']'."\r\n";
										$bErrFile = true;
									}
								}
							}
						}
					}else{
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] .= "Generar PDF eDocuments :: Error en ventanilla unica al digitalizar el documento.[".$dir_edocuments[$i].']'."\r\n";
						$bErrFile = true;
					}
				}
			}
		}
		return $respuesta;
	}
	
	function tipos_documento_edocuments_ventanilla($id_tipo_doc){
		switch($id_tipo_doc){
			case '168' :  return 'Calca o fotografía digital del NIV del vehículo';
			case '169' :  return 'Aviso';
			case '170' :  return 'Factura';
			case '171' :  return 'Documento con el que se acredite la propiedad de la mercancía';
			case '172' :  return 'Contratos';
			case '176' :  return 'Documentación relacionada con la garantía otorgada en términos de los artículos 84-A y 86-A de la L.A';
			case '177' :  return 'Identificación Oficial';
			case '179' :  return 'Comprobante de domicilio';
			case '184' :  return 'Documento que ampara el avaluó de las mercancías';
			case '185' :  return 'Documentos de adjudicación judicial de las mercancías';
			case '187' :  return 'Solicitud de retiro de mercancías que causaron abandono';
			case '189' :  return 'Actas';
			case '192' :  return 'Escritos';
			case '420' :  return 'Certificado de peso o volumen';
			case '421' :  return 'Comprobante de la importación temporal de la embarcación debidamente formalizado';
			case '422' :  return 'Comprobante expedido por donataria';
			case '423' :  return 'Consulta en la que conste que  el vehículo no se encuentra reportado como robado, siniestrado, restringido o prohibido para su circulación en el país de procedencia';
			case '424' :  return 'Clave Única del Registro de Población';
			case '425' :  return 'Declaración de internación o extracción de cantidades en efectivo y/o documentos por cobrar';
			case '426' :  return 'Declaración de operaciones que no confieren origen en países no parte de acuerdo al TLCI';
			case '427' :  return 'Declaración en la que se señalen los motivos por los que efectúa la devolución  de mercancías en los términos. de la regla 3.8.4';
			case '428' :  return 'Documentación con información que permita la identificación, análisis y control en términos del artículo 36 de la L.A';
			case '429' :  return 'Documentación que acredite que acepta y subsana la irregularidad';
			case '430' :  return 'Documentación que ampare la importación temporal del vehículo de que se trate';
			case '431' :  return 'Documentación que compruebe que la adquisición de las mercancías fue efectuada cuando se contaba con autorización para operar bajo un Programa IMMEX';
			case '433' :  return 'Documento con  base en el cual se determine  la procedencia y  el origen de las mercancías';
			case '434' :  return 'Documento con que se acredite el reintegro del IVA, en caso de que el contribuyente hubiere obtenido la devolución, o efectuado el acreditamiento de los saldos a favor declarados con motivo de la exportación';
			case '435' :  return 'Documentos previstos en la regla 8.7., fracciones I a IV de la Resolución del TLCAN';
			case '436' :  return 'El Documento que compruebe el cumplimiento de las regulaciones y restricciones  no arancelarias';
			case '437' :  return 'formato denominado “Relaciyn de documentos”';
			case '438' :  return 'Guía aérea, conocimiento de embarque o carta de porte';
			case '439' :  return 'Hoja con los datos de la matrícula y nombre del barco, el lugar donde se localiza y se indique que la mercancía se encuentra almacenada en los depósitos para combustible del barco para su propio consumo';
			case '440' :  return 'Manifiesto de carga';
			case '441' :  return 'Oficios emitidos por autoridad';
			case '442' :  return 'Pedimentos';
			case '443' :  return 'Programa IMMEX';
			case '444' :  return 'Relación de candados';
			case '445' :  return 'Relación de certificados de origen';
			default : return '';
		}
	}
	
	function usuario_generacion_edocument($RFC){
		switch($RFC){
			case 'NIGH670524EE3' : return 'HUGO NISHIYAMA DE LA GARZA';
			case 'EAFM620803BVA' : return 'MANUEL JOSE ESTANDIA FERNANDEZ';
		}
		global $odbccasa;
		$query = "SELECT NOM_IMP FROM CTRAC_CLIENT WHERE RFC_IMP='".$RFC."'";
		$result = odbc_exec ($odbccasa, $query);
		if (!$result){
			return " ";
		}
		while(odbc_fetch_row($result)){
			return odbc_result($result,"NOM_IMP");
		}
		return '';
		
	}

	function nombre_mes($nMes){
		switch($nMes){
			case '01' : return 'enero';
			case '02' : return 'febrero';
			case '03' : return 'marzo';
			case '04' : return 'abril';
			case '05' : return 'mayo';
			case '06' : return 'junio';
			case '07' : return 'julio';
			case '08' : return 'agosto';
			case '09' : return 'septiembre';
			case '10' : return 'octubre';
			case '11' : return 'nomviembre';
			case '12' : return 'diciembre';
		}
	}
	?>