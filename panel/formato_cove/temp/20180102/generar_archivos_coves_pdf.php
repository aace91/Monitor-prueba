<?php
	require('tcpdf/tcpdf.php');
	require('tcpdf/Output.php');
	
	function generar_archivos_pdf_cove($referencia){
		global $odbccasa; global $cmysqli;
		$isRelacionFac = false;
		$directorio = "\\\\192.168.1.107\\gabdata\\CASAWIN\\cove\\ventanilla\\cove\\".$referencia."\\";
		if(!file_exists($directorio)){
			$query = "SELECT num_refeo FROM SAAIO_PEDIME where num_refe='".$referencia."'";
			$result = odbc_exec ($odbccasa, $query);
			if (!$result){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error en consulta: ".odbc_error($odbccasa);
				return $respuesta;
			}
			while(odbc_fetch_row($result)){
				$refo=odbc_result($result,"num_refeo");
			}
			$directorio = "\\\\192.168.1.107\\gabdata\\CASAWIN\\cove\\ventanilla\\cove\\".$refo."\\";
			if(!file_exists($directorio)){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Generar PDF COVES :: El directorio de las referencias [".$referencia.",".$refo."] no existe en directorio. [\\\\192.168.1.107\\gabdata\\CASAWIN\\cove\\ventanilla\\cove\\]";
				return $respuesta;
			}
		}
		$aCOVES = array(); $aComprobantes = array();
		$dir_coves  = scandir($directorio);
		for($i=0; $i<count($dir_coves); $i++){
			//echo 'Hora '.date("Y-m-d H:i:s").' en leer '.$dir_coves[$i].'</br>';
			if(strpos(trim($dir_coves[$i]),'_COVEReturn.xml') !== false) {
				$numeroOperacion = '';
				$COVE = '';
				//Consultamos el numero de operacion
				$xmlResp = new DOMDocument();
				$xmlResp->preserveWhiteSpace = false;
				$xmlResp->load($directorio.$dir_coves[$i]);
				$nodes =  $xmlResp->getElementsByTagName('solicitarRecibirCoveServicioResponse');
				if($nodes->length <= 0){
					//error_log('El Nodo solicitarRecibirCoveServicioResponse esta vacio o no existe!.');
					$nodes =  $xmlResp->getElementsByTagName('solicitarRecibirRelacionFacturasNoIAServicioResponse');
					$isRelacionFac = true;
				}
				//error_log(json_encode($nodes));
				//echo $nodes->length;
				foreach( $nodes as $node ){
					//error_log('Name:'.$node->nodeName);
					$numeroOperacion = $node->getElementsByTagName("numeroDeOperacion")->item(0)->nodeValue;
					if (trim($node->getElementsByTagName("mensajeInformativo")->item(0)->nodeValue) != 'La recepción del COVE fue exitosa.'){
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = "Generar PDF COVES :: Error de ventanilla para el COVE ".$dir_coves[$i].'['.trim($node->getElementsByTagName("mensajeInformativo")->item(0)->nodeValue).']';
						return $respuesta;
					}
					
				}
				if($numeroOperacion == ''){
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Generar PDF COVES :: El archivo ['.$directorio.$dir_coves[$i].'] no cuenta con un número de operación valido.';
					return $respuesta;
				}			
				//Consultamos el numero de COVE dependiendo del numero de operacion, esto porque se pueden tener varias peticiones para una sola factura
				for($j=0; $j<count($dir_coves); $j++){
					$xmlResp = '_'.$numeroOperacion.'_COVEConsultaResult.xml';
					if(strpos(trim($dir_coves[$j]),$xmlResp) !== false) {
						$xmlResp = new DOMDocument();
						$xmlResp->preserveWhiteSpace = false;
						$xmlResp->load($directorio.$dir_coves[$j]);
						$nodes =  $xmlResp->getElementsByTagName('respuestasOperaciones');
						foreach( $nodes as $node ){
							$error = $node->getElementsByTagName("contieneError")->item(0)->nodeValue;
							$COVE = $node->getElementsByTagName("eDocument")->item(0)->nodeValue;
							$Factura = $node->getElementsByTagName("numeroFacturaORelacionFacturas")->item(0)->nodeValue;
							$aComprobante = array($error,$COVE,$Factura);
							array_push($aComprobantes,$aComprobante);
							if($error == 'true'){
								$respuesta['Codigo'] = -1;
								$respuesta['Mensaje'] = "Generar PDF COVES :: El archivo XML del COVE contiene errores. NúmeroOperacón[".$numeroOperacion.']';
								return $respuesta;
							}
						}
					}
				}
				if(count($aComprobantes) == 0){
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Generar PDF COVE :: No se encontro el archivo de respuesta para el archivo ['.$directorio.$dir_coves[$i].']. NúmeroOperacón['.$numeroOperacion.'] Archivo['.$referencia.'_'.$numeroOperacion.'_COVEConsultaResult.xml'.']';
					return $respuesta;
				}
				
				$archActual = $dir_coves[$i];
				$ArchCOVE = str_replace('Return','',$archActual);
				
				$xmlCOVE= new DOMDocument();
				$xmlCOVE->preserveWhiteSpace = false;
				$xmlCOVE->load($directorio.$ArchCOVE);
				$nodes =  $xmlCOVE->getElementsByTagName('comprobantes');
				
				if ($nodes->length<>0) {
					// create new PDF document
					for($c = 0; $c < count($aComprobantes); $c++){//Varios Comprobantes en una misma Operacion
						//echo 'Hora '.date("Y-m-d H:i:s").' BUSCANDO_FACTURA'.$Factura .'</br>';
						foreach( $nodes as $node ){
							if($isRelacionFac){
								//numeroRelacionFacturas
								$Factura = $node->getElementsByTagName("numeroRelacionFacturas")->item(0)->nodeValue;
							}else{
								$Factura = $node->getElementsByTagName("numeroFacturaOriginal")->item(0)->nodeValue;
							}
							if($aComprobantes[$c][2] == $Factura){
								//echo 'Hora '.date("Y-m-d H:i:s").' FACTURA '.$Factura .'</br>';
								//echo 'Hora '.date("Y-m-d H:i:s").'Iniciando creacion del PDF '.$aComprobantes[$c][1];
								$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);				
								// set document information
								$pdf->SetCreator(PDF_CREATOR);
								$pdf->SetAuthor('Grupo Aduanero Del Bravo');
								$pdf->SetTitle($aComprobantes[$c][1]);
								$pdf->SetSubject($aComprobantes[$c][1]);
								$pdf->SetKeywords($aComprobantes[$c][1]);
								
								$pdf->setPrintHeader(true);
								$pdf->setPrintFooter(true);
								$pdf->SetHeaderData(PDF_HEADER_LOGO, 15, '', '');
								
								$pdf->AddPage();
								//$pdf->Image('img/header.png',5,7,195,35);
								$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);				
								$pdf->SetFont('helvetica', '', 14);
								$pdf->SetColor('text',0,77,153);
								$pdf->Cell(0,0, 'Información de Valor y de Comercialización' , 0, 1, 'C',0,'',0);
								
								$pdf->SetFont('helvetica', '', 12);
								$pdf->SetColor('text',0,77,153);
								$pdf->Cell(50,6, 'Datos del Acuse de Valor' , 0, 0, 'L',0,'',0);
								$pdf->SetFont('helvetica', '', 10);
								$pdf->SetColor('text',0,0,0);
								$pdf->Cell(0,6, $aComprobantes[$c][1] , 0, 1, 'L',0,'',0);
								//TOCE.IMP :: Importación
								//TOCE.EXP :: Exportación)
								if(trim($node->getElementsByTagName("tipoOperacion")->item(0)->nodeValue) == 'TOCE.IMP'){
									$TipoOperacion = 'Importación';
								}else{
									$TipoOperacion = 'Exportación';
								}
								$Patente = $node->getElementsByTagName("patenteAduanal")->item(0)->nodeValue;
								$FechaExpedicion = $node->getElementsByTagName("fechaExpedicion")->item(0)->nodeValue;
								$Observaciones = $node->getElementsByTagName("observaciones")->item(0)->nodeValue;
								$date = new DateTime($FechaExpedicion);
								$FechaExpedicion = $date->format('d/m/Y');
								Switch($node->getElementsByTagName("tipoFigura")->item(0)->nodeValue){
									case '1':
										$TipoFigura = 'Agente Aduanal';
										break;
									case '2':
										$TipoFigura = 'Apoderado Aduanal';
										break;
									case '3':
										$TipoFigura = 'Mandatario';
										break;
									case '4':
										$TipoFigura = 'Exportador';
										break;
									case '5':
										$TipoFigura = 'Importador';
										break;
								}
								$RFC_Consulta = $node->getElementsByTagName("rfcConsulta")->item(0)->nodeValue;
								
								$sHTML = '
								<table  border="1" cellspacing="0" cellpadding="2">
									<tr bgcolor="#cccccc">
										<td  colspan="2" ><strong>Tipo de operaci&oacute;n</strong></td>
										<td  colspan="2"><strong>Relaci&oacute;n de facturas</strong></td>
										<td  colspan="2"><strong>No. de factura</strong></td>
									</tr>
									<tr>
										<td  colspan="2">'.$TipoOperacion.'</td>
										<td  colspan="2">'.($isRelacionFac ? 'CON RELACIÓN DE FACTURAS' : 'SIN RELACIÓN DE FACTURAS').'</td>
										<td  colspan="2">'.$Factura.'</td>
									</tr>
									<tr bgcolor="#cccccc">
										<td  colspan="3" ><strong>Tipo de figura</strong></td>
										<td  colspan="3"><strong>Fecha Exp.</strong></td>
									</tr>
									<tr>
										<td  colspan="3">'.$TipoFigura.'</td>
										<td  colspan="3">'.$FechaExpedicion .'</td>
									</tr>
									<tr bgcolor="#cccccc">
										<td  colspan="6" ><strong>Observaciones</strong></td>
									</tr>
									<tr>
										<td  colspan="6">'.$Observaciones.'</td>
									</tr>
								</table>';
									
								$pdf->writeHTML($sHTML, false, false, true, false, '');
								$pdf->SetFont('helvetica', '', 12);
								$pdf->SetColor('text',0,77,153);
								$pdf->Cell(0,6, 'RFC con permisos de consulta' , 0, 1, 'L',0,'',0);
								
								$consulta="SELECT NOM_IMP FROM casa.ctrac_client WHERE RFC_IMP = '".$RFC_Consulta."'";	
								
								$query = mysqli_query($cmysqli,$consulta);
								if (!$query){
									$error = mysqli_error($cmysqli);
									$respuesta['Codigo'] = -1;
									$respuesta['Mensaje'] = "Generar PDF COVES :: Error al consultar el RFC de consulta en nuestra base de datos. ".$error;
									return $respuesta;
								}
								if(mysqli_num_rows($query) > 0){
									while($row = mysqli_fetch_array($query)){
										$RazonSocial = $row["NOM_IMP"];
									}
								}else{
									$respuesta['Codigo'] = -1;
									$respuesta['Mensaje'] = 'Generar PDF COVES :: El RFC de consulta no se encuentra en nuestro catálogo. RFC['.$RFC_Consulta.']';
									return $respuesta;
								}
								
								$pdf->SetColor('text',0,0,0);
								$pdf->SetFont('helvetica', '', 10);
								$sHTML = '
								<table  border="1" cellspacing="0" cellpadding="2">
									<tr bgcolor="#cccccc">
										<td  colspan="2" ><strong>RFC de consulta</strong></td>
										<td  colspan="4"><strong>Nombre o Razón Social</strong></td>
									</tr>
									<tr>
										<td  colspan="2">'.$RFC_Consulta.'</td>
										<td  colspan="4">'.$RazonSocial.'</td>
									</tr>
								</table>';
									
								$pdf->writeHTML($sHTML, false, false, true, false, '');
								
								$pdf->SetFont('helvetica', '', 12);
								$pdf->SetColor('text',0,77,153);
								$pdf->Cell(0,6, 'Número de patente aduanal' , 0, 1, 'L',0,'',0);
								
								$pdf->SetColor('text',0,0,0);
								$pdf->SetFont('helvetica', '', 10);
								$sHTML = '
								<table  border="1" cellspacing="0" cellpadding="2">
									<tr bgcolor="#cccccc">
										<td  colspan="6" ><strong>Número autorización aduanal</strong></td>
									</tr>
									<tr>
										<td  colspan="6">'.$Patente.'</td>
									</tr>
								</table>';
									
								$pdf->writeHTML($sHTML, false, false, true, false, '');
								
								if($isRelacionFac){
									//RELACION FACTURAS :: Es una relacion de facturas y se recorre el tag <facturas></facturas> para obtener el arreglo de cada una de ellas
									foreach( $node->getElementsByTagName("facturas") as $factura ){
										//numeroFactura proveedor *** No se usa en el formato PDF ***
										$NumFacturaProv = $factura->getElementsByTagName("numeroFactura")->item(0)->nodeValue;
										//Datos de la Factura
										if(trim($factura->getElementsByTagName("certificadoOrigen")->item(0)->nodeValue) == '0'){
											$CertificadoOrigen = 'No funge como certificado de origen';
										}else{
											$CertificadoOrigen = 'Si funge como certificado de origen';
										}
										if(trim($factura->getElementsByTagName("subdivision")->item(0)->nodeValue) == '0'){
											$Subdivision = 'Sin subdivisión';
										}else{
											$Subdivision = 'Con subdivisión';
										}
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$pdf->SetFont('helvetica', '', 12);
										$pdf->SetColor('text',0,77,153);
										$pdf->Cell(189,6, 'Datos de la factura' , 0, 1, 'L',0,'',0);
										$pdf->SetColor('text',0,0,0);
										$pdf->SetFont('helvetica', '', 10);
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr bgcolor="#cccccc">
												<td  colspan="2" ><strong>Subdivisión</strong></td>
												<td  colspan="2" ><strong>Certificado de origen</strong></td>
												<td  colspan="2" ><strong>No. de exportador autorizado</strong></td>
											</tr>
											<tr>
												<td  colspan="2">'.$Subdivision.'</td>
												<td  colspan="2" >'.$CertificadoOrigen .'</td>
												<td  colspan="2" >'.' '.'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										
										// ******************************************
										//	EMISOR
										//******************************************** 
										foreach( $factura->getElementsByTagName("emisor") as $emisor ){
											switch(trim($emisor->getElementsByTagName("tipoIdentificador")->item(0)->nodeValue)){
												case '0':
													$TipoID_emisor = 'TAX_ID';
													break;
												case '1':
													$TipoID_emisor = 'RFC';
													break;
												case '2':
													$TipoID_emisor = 'CURP';
													break;
												case '3':
													$TipoID_emisor = 'SIN TAX_ID';
													break;
											}
											$RFC_emisor  = $emisor->getElementsByTagName("identificacion")->item(0)->nodeValue;
											$Nombre_emisor = $emisor->getElementsByTagName("nombre")->item(0)->nodeValue;
											
											foreach( $emisor->getElementsByTagName("domicilio") as $domicilio ){
												$Calle_emisor = $domicilio->getElementsByTagName("calle")->item(0)->nodeValue;
												$Dir_emisor = split("COL.",$Calle_emisor);
												if(count($Dir_emisor )>1){
													$Calle_emisor = trim($Dir_emisor[0]);
													$Colonia_emisor = trim($Dir_emisor[1]);
												}
												$Dir_emisor = split("COL",$Calle_emisor);
												if(count($Dir_emisor )>1){
													$Calle_emisor = trim($Dir_emisor[0]);
													$Colonia_emisor = trim($Dir_emisor[1]);
												}
												$Dir_emisor = split("COLONIA.",$Calle_emisor);
												if(count($Dir_emisor )>1){
													$Calle_emisor = trim($Dir_emisor[0]);
													$Colonia_emisor = trim($Dir_emisor[1]);
												}
												$Dir_emisor = split("COLONIA",$Calle_emisor);
												if(count($Dir_emisor )>1){
													$Calle_emisor = trim($Dir_emisor[0]);
													$Colonia_emisor = trim($Dir_emisor[1]);
												}
												$Numext_emisor =  $domicilio->getElementsByTagName("numeroExterior")->item(0)->nodeValue;
												if (isset($domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue) && !empty($domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue)) {
													$Numint_emisor =  $domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue;
												}else{
													$Numint_emisor =  '';
												}
												if (isset($domicilio->getElementsByTagName("localidad")->item(0)->nodeValue) && !empty($domicilio->getElementsByTagName("localidad")->item(0)->nodeValue)) {
													$Localidad_emisor =  $domicilio->getElementsByTagName("localidad")->item(0)->nodeValue;
												}else{
													$Localidad_emisor =  '';
												}
												$Municipio_emisor =  $domicilio->getElementsByTagName("municipio")->item(0)->nodeValue;
												$Estado_emisor =  $domicilio->getElementsByTagName("entidadFederativa")->item(0)->nodeValue;
												$Pais_emisor =  $domicilio->getElementsByTagName("pais")->item(0)->nodeValue;
												$CP_emisor =  $domicilio->getElementsByTagName("codigoPostal")->item(0)->nodeValue;
											}
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$pdf->SetFont('helvetica', '', 12);
											$pdf->SetColor('text',0,77,153);
											$pdf->Cell(189,6, 'Datos generales del proveedor' , 0, 1, 'L',0,'',0);
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$pdf->SetColor('text',0,0,0);
											$pdf->SetFont('helvetica', ' ', 10);
											
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr bgcolor="#cccccc">
													<td  colspan="2" ><strong>Tipo de identificador</strong></td>
													<td  colspan="4" ><strong>Tax ID/Sin Tax ID/RFC/CURP</strong></td>
												</tr>
												<tr>
													<td  colspan="2">'.$TipoID_emisor.'</td>
													<td  colspan="4" >'.$RFC_emisor .'</td>
												</tr>
												<tr bgcolor="#cccccc">
													<td  colspan="2" ><strong>Nombre(s) o Razón Social</strong></td>
													<td  colspan="2" ><strong>Apellido paterno</strong></td>
													<td  colspan="2" ><strong>Apellido materno</strong></td>
												</tr>
												<tr>
													<td  colspan="2">'.$Nombre_emisor.'</td>
													<td  colspan="2" >'.' '.'</td>
													<td  colspan="2" >'.' '.'</td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$pdf->SetFont('helvetica', '', 12);
											$pdf->SetColor('text',0,77,153);
											$pdf->Cell(189,6, 'Domicilio del proveedor' , 0, 1, 'L',0,'',0);						
											$pdf->SetColor('text',0,0,0);
											$pdf->SetFont('helvetica', ' ', 10);
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">							
												<tr bgcolor="#cccccc">
													<td colspan="3"><strong>Calle</strong></td>
													<td colspan="1"><strong>No. exterior</strong></td>
													<td colspan="1"><strong>No. interior</strong></td>
													<td colspan="1"><strong>Código postal</strong></td>
												</tr>
												<tr>
													<td colspan="3">'.$Calle_emisor.'</td>
													<td colspan="1">'.$Numext_emisor.'</td>
													<td colspan="1">'.$Numint_emisor.'</td>
													<td colspan="1">'.$CP_emisor.'</td>
												</tr>
												<tr bgcolor="#cccccc">
													<td colspan="3" ><strong>Colonia</strong></td>
													<td colspan="3" ><strong>Localidad</strong></td>
												</tr>
												<tr>
													<td colspan="3">'.$Colonia_emisor.'</td>
													<td colspan="3" >'.$Localidad_emisor .'</td>
												</tr>							
												<tr bgcolor="#cccccc">
													<td colspan="3" ><strong>Entidad federativa</strong></td>
													<td colspan="3" ><strong>Municipio</strong></td>
												</tr>
												<tr>
													<td colspan="3">'.$Estado_emisor.'</td>
													<td colspan="3" >'.$Municipio_emisor .'</td>
												</tr>
												<tr bgcolor="#cccccc">
													<td colspan="6" ><strong>País</strong></td>
												</tr>
												<tr>
													<td colspan="6">'.$Pais_emisor.'</td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
										}
										
										// ******************************************
										//DESTINATARIO
										//******************************************** 
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$pdf->SetFont('helvetica', '', 12);
										$pdf->SetColor('text',0,77,153);
										$pdf->Cell(189,6, 'Datos generales del destinatario' , 0, 1, 'L',0,'',0);					
										$pdf->SetColor('text',0,0,0);
										$pdf->SetFont('helvetica', ' ', 10);
										
										foreach( $factura->getElementsByTagName("destinatario") as $destinatario ){
											switch(trim($destinatario->getElementsByTagName("tipoIdentificador")->item(0)->nodeValue)){
												case '0':
													$TipoID_destinatario = 'TAX_ID';
													break;
												case '1':
													$TipoID_destinatario = 'RFC';
													break;
												case '2':
													$TipoID_destinatario = 'CURP';
													break;
												case '3':
													$TipoID_destinatario = 'SIN TAX_ID';
													break;
											}
											$RFC_destinatario  = $destinatario->getElementsByTagName("identificacion")->item(0)->nodeValue;
											$Nombre_destinatario = $destinatario->getElementsByTagName("nombre")->item(0)->nodeValue;
											
											foreach( $destinatario->getElementsByTagName("domicilio") as $domicilio ){
												$Calle_destinatario = $domicilio->getElementsByTagName("calle")->item(0)->nodeValue;
												$Dir_destinatario = split("COL.",$Calle_destinatario);
												$Colonia_destinatario = '';
												if(count($Dir_destinatario )>1){
													$Calle_destinatario = trim($Dir_destinatario[0]);
													$Colonia_destinatario = trim($Dir_destinatario[1]);
												}
												$Dir_destinatario = split("COL",$Calle_destinatario);
												if(count($Dir_destinatario )>1){
													$Calle_destinatario= trim($Dir_destinatario[0]);
													$Colonia_destinatario = trim($Dir_destinatario[1]);
												}
												$Dir_destinatario = split("COLONIA.",$Calle_destinatario);
												if(count($Dir_destinatario )>1){
													$Calle_destinatario = trim($Dir_destinatario[0]);
													$Colonia_destinatario = trim($Dir_destinatario[1]);
												}
												$Dir_destinatario = split("COLONIA",$Calle_destinatario);
												if(count($Dir_destinatario )>1){
													$Calle_destinatario = trim($Dir_destinatario[0]);
													$Colonia_destinatario = trim($Dir_destinatario[1]);
												}
												$Numext_destinatario =  $domicilio->getElementsByTagName("numeroExterior")->item(0)->nodeValue;
												if (isset($domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue) && !empty($domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue)) {
													$Numint_destinatario =  $domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue;
												}else{
													$Numint_destinatario =  '';
												}
												if (isset($domicilio->getElementsByTagName("localidad")->item(0)->nodeValue) && !empty($domicilio->getElementsByTagName("localidad")->item(0)->nodeValue)) {
													$Localidad_destinatario =  $domicilio->getElementsByTagName("localidad")->item(0)->nodeValue;
												}else{
													$Localidad_destinatario =  '';
												}
												//$Numint_destinatario =  $domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue;
												//$Localidad_destinatario =  $domicilio->getElementsByTagName("localidad")->item(0)->nodeValue;
												
												$Municipio_destinatario =  $domicilio->getElementsByTagName("municipio")->item(0)->nodeValue;									
												$Estado_destinatario =  $domicilio->getElementsByTagName("entidadFederativa")->item(0)->nodeValue;
												$Pais_destinatario =  $domicilio->getElementsByTagName("pais")->item(0)->nodeValue;
												$CP_destinatario =  $domicilio->getElementsByTagName("codigoPostal")->item(0)->nodeValue;
											}
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr bgcolor="#cccccc">
													<td  colspan="2" ><strong>Tipo de identificador</strong></td>
													<td  colspan="4" ><strong>Tax ID/Sin Tax ID/RFC/CURP</strong></td>
												</tr>
												<tr>
													<td  colspan="2">'.$TipoID_destinatario.'</td>
													<td  colspan="4" >'.$RFC_destinatario .'</td>
												</tr>
												<tr bgcolor="#cccccc">
													<td  colspan="2" ><strong>Nombre(s) o Razón Social</strong></td>
													<td  colspan="2" ><strong>Apellido paterno</strong></td>
													<td  colspan="2" ><strong>Apellido materno</strong></td>
												</tr>
												<tr>
													<td  colspan="2">'.$Nombre_destinatario.'</td>
													<td  colspan="2" >'.' '.'</td>
													<td  colspan="2" >'.' '.'</td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}						
											$pdf->SetFont('helvetica', '', 12);
											$pdf->SetColor('text',0,77,153);
											$pdf->Cell(189,6, 'Domicilio del destinatario' , 0, 1, 'L',0,'',0);						
											$pdf->SetColor('text',0,0,0);
											$pdf->SetFont('helvetica', ' ', 10);
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">							
												<tr bgcolor="#cccccc">
													<td colspan="3"><strong>Calle</strong></td>
													<td colspan="1"><strong>No. exterior</strong></td>
													<td colspan="1"><strong>No. interior</strong></td>
													<td colspan="1"><strong>Código postal</strong></td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr>
													<td colspan="3">'.$Calle_destinatario.'</td>
													<td colspan="1">'.$Numext_destinatario.'</td>
													<td colspan="1">'.$Numint_destinatario.'</td>
													<td colspan="1">'.$CP_destinatario.'</td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr bgcolor="#cccccc">
													<td colspan="3" ><strong>Colonia</strong></td>
													<td colspan="3" ><strong>Localidad</strong></td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr>
													<td colspan="3">'.$Colonia_destinatario.'</td>
													<td colspan="3" >'.$Localidad_destinatario.'</td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr bgcolor="#cccccc">
													<td colspan="3" ><strong>Entidad federativa</strong></td>
													<td colspan="3" ><strong>Municipio</strong></td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr>
													<td colspan="3">'.$Estado_destinatario.'</td>
													<td colspan="3" >'.$Municipio_destinatario .'</td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr bgcolor="#cccccc">
													<td colspan="6" ><strong>País</strong></td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr>
													<td colspan="6">'.$Pais_destinatario.'</td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}						
										}
										
										// ******************************************
										//MERCANCIAS
										//******************************************** 
										foreach( $node->getElementsByTagName("mercancias") as $mercancia ){
											$Descripcion_Merc = $mercancia->getElementsByTagName("descripcionGenerica")->item(0)->nodeValue;
											$UM_Merc = get_descripcion_UM($mercancia->getElementsByTagName("claveUnidadMedida")->item(0)->nodeValue);
											//Bajar catalogo de ventanilla y guardad en BD para consulta
											$Cantidad_Merc = $mercancia->getElementsByTagName("cantidad")->item(0)->nodeValue;
											$TipoMoneda_Merc = get_descripcion_Moneda($mercancia->getElementsByTagName("tipoMoneda")->item(0)->nodeValue);
											//Bajar catalogo de ventanilla y guardad en BD para consulta
											$ValorUnitario_Merc = $mercancia->getElementsByTagName("valorUnitario")->item(0)->nodeValue;
											$ValorTotal_Merc = $mercancia->getElementsByTagName("valorTotal")->item(0)->nodeValue;
											$ValorDolares_Merc = $mercancia->getElementsByTagName("valorDolares")->item(0)->nodeValue;
											foreach( $mercancia->getElementsByTagName("descripcionesEspecificas") as $descripciones ){
												$Marca_Merc = $descripciones->getElementsByTagName("marca")->item(0)->nodeValue;
												$Modelo_Merc = $descripciones->getElementsByTagName("modelo")->item(0)->nodeValue;
												$SubModelo_Merc = $descripciones->getElementsByTagName("subModelo")->item(0)->nodeValue;
												$NumSerie_Merc = $descripciones->getElementsByTagName("numeroSerie")->item(0)->nodeValue;
											}
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$pdf->SetFont('helvetica', '', 12);
											$pdf->SetColor('text',0,77,153);
											$pdf->Cell(189,6, 'Datos de la mercancía' , 0, 1, 'L',0,'',0);						
											$pdf->SetColor('text',0,0,0);
											$pdf->SetFont('helvetica', ' ', 10);
											
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr bgcolor="#cccccc">
													<td  colspan="4" ><strong>Descripción genérica de la mercancía</strong></td>
													<td  colspan="2" ><strong>Clave UMC</strong></td>
													<td  colspan="2" ><strong>Cantidad UMC</strong></td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr>
													<td  colspan="4">'.$Descripcion_Merc.'</td>
													<td  colspan="2" >'.$UM_Merc .'</td>
													<td  colspan="2">'.$Cantidad_Merc.'</td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr bgcolor="#cccccc">
													<td  colspan="2" ><strong>Tipo Moneda</strong></td>
													<td  colspan="2" ><strong>Valor unitario</strong></td>
													<td  colspan="2" ><strong>Valor total</strong></td>
													<td  colspan="2" ><strong>Valor total en dólares</strong></td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr>
													<td  colspan="2">'.$TipoMoneda_Merc.'</td>
													<td  colspan="2" >'.$ValorUnitario_Merc.'</td>
													<td  colspan="2" >'.$ValorTotal_Merc.'</td>
													<td  colspan="2">'.$ValorDolares_Merc.'</td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											
											$pdf->SetFont('helvetica', '', 12);
											$pdf->SetColor('text',0,77,153);
											$pdf->Cell(189,6, 'Descripción de la mercancía' , 0, 1, 'L',0,'',0);						
											$pdf->SetColor('text',0,0,0);
											$pdf->SetFont('helvetica', ' ', 10);
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr bgcolor="#cccccc">
													<td  colspan="2" ><strong>Marca</strong></td>
													<td  colspan="2" ><strong>Modelo</strong></td>
													<td  colspan="2" ><strong>Submodelo</strong></td>
													<td  colspan="2" ><strong>No. serie</strong></td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}
											$sHTML = '
											<table  border="1" cellspacing="0" cellpadding="2">
												<tr>
													<td  colspan="2">'.$Marca_Merc.'</td>
													<td  colspan="2" >'.$Modelo_Merc.'</td>
													<td  colspan="2" >'.$SubModelo_Merc.'</td>
													<td  colspan="2">'.$NumSerie_Merc.'</td>
												</tr>
											</table>';
											$pdf->writeHTML($sHTML, false, false, true, false, '');
											$y = $pdf->GetY();
											if($y > 260){
												$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
											}						
										}
									}
								}else{
									//FACTURA SENCILLA :: Solo una factura y se toman los datos directo de cada tag, no incluye el tag <facturas></facturas>
									foreach( $node->getElementsByTagName("factura") as $factura ){
										if(trim($factura->getElementsByTagName("certificadoOrigen")->item(0)->nodeValue) == '0'){
											$CertificadoOrigen = 'No funge como certificado de origen';
										}else{
											$CertificadoOrigen = 'Si funge como certificado de origen';
										}
										if(trim($factura->getElementsByTagName("subdivision")->item(0)->nodeValue) == '0'){
											$Subdivision = 'Sin subdivisión';
										}else{
											$Subdivision = 'Con subdivisión';
										}
									}
									$pdf->SetFont('helvetica', '', 12);
									$pdf->SetColor('text',0,77,153);
									$pdf->Cell(189,6, 'Datos de la factura' , 0, 1, 'L',0,'',0);
									$pdf->SetColor('text',0,0,0);
									$pdf->SetFont('helvetica', '', 10);
									$sHTML = '
									<table  border="1" cellspacing="0" cellpadding="2">
										<tr bgcolor="#cccccc">
											<td  colspan="2" ><strong>Subdivisión</strong></td>
											<td  colspan="2" ><strong>Certificado de origen</strong></td>
											<td  colspan="2" ><strong>No. de exportador autorizado</strong></td>
										</tr>
										<tr>
											<td  colspan="2">'.$Subdivision.'</td>
											<td  colspan="2" >'.$CertificadoOrigen .'</td>
											<td  colspan="2" >'.' '.'</td>
										</tr>
									</table>';
									$pdf->writeHTML($sHTML, false, false, true, false, '');
									// ******************************************
									//	EMISOR
									//******************************************** 
									foreach( $node->getElementsByTagName("emisor") as $emisor ){
										switch(trim($emisor->getElementsByTagName("tipoIdentificador")->item(0)->nodeValue)){
											case '0':
												$TipoID_emisor = 'TAX_ID';
												break;
											case '1':
												$TipoID_emisor = 'RFC';
												break;
											case '2':
												$TipoID_emisor = 'CURP';
												break;
											case '3':
												$TipoID_emisor = 'SIN TAX_ID';
												break;
										}
										$RFC_emisor  = $emisor->getElementsByTagName("identificacion")->item(0)->nodeValue;
										$Nombre_emisor = $emisor->getElementsByTagName("nombre")->item(0)->nodeValue;
										
										foreach( $emisor->getElementsByTagName("domicilio") as $domicilio ){
											$Calle_emisor = $domicilio->getElementsByTagName("calle")->item(0)->nodeValue;
											$Dir_emisor = split("COL.",$Calle_emisor);
											if(count($Dir_emisor )>1){
												$Calle_emisor = trim($Dir_emisor[0]);
												$Colonia_emisor = trim($Dir_emisor[1]);
											}
											$Dir_emisor = split("COL",$Calle_emisor);
											if(count($Dir_emisor )>1){
												$Calle_emisor = trim($Dir_emisor[0]);
												$Colonia_emisor = trim($Dir_emisor[1]);
											}
											$Dir_emisor = split("COLONIA.",$Calle_emisor);
											if(count($Dir_emisor )>1){
												$Calle_emisor = trim($Dir_emisor[0]);
												$Colonia_emisor = trim($Dir_emisor[1]);
											}
											$Dir_emisor = split("COLONIA",$Calle_emisor);
											if(count($Dir_emisor )>1){
												$Calle_emisor = trim($Dir_emisor[0]);
												$Colonia_emisor = trim($Dir_emisor[1]);
											}
											$Numext_emisor =  $domicilio->getElementsByTagName("numeroExterior")->item(0)->nodeValue;
											if (isset($domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue) && !empty($domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue)) {
												$Numint_emisor =  $domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue;
											}else{
												$Numint_emisor =  '';
											}
											if (isset($domicilio->getElementsByTagName("localidad")->item(0)->nodeValue) && !empty($domicilio->getElementsByTagName("localidad")->item(0)->nodeValue)) {
												$Localidad_emisor =  $domicilio->getElementsByTagName("localidad")->item(0)->nodeValue;
											}else{
												$Localidad_emisor =  '';
											}
											$Municipio_emisor =  $domicilio->getElementsByTagName("municipio")->item(0)->nodeValue;
											$Estado_emisor =  $domicilio->getElementsByTagName("entidadFederativa")->item(0)->nodeValue;
											$Pais_emisor =  $domicilio->getElementsByTagName("pais")->item(0)->nodeValue;
											$CP_emisor =  $domicilio->getElementsByTagName("codigoPostal")->item(0)->nodeValue;
										}
										$pdf->SetFont('helvetica', '', 12);
										$pdf->SetColor('text',0,77,153);
										$pdf->Cell(189,6, 'Datos generales del proveedor' , 0, 1, 'L',0,'',0);
										
										$pdf->SetColor('text',0,0,0);
										$pdf->SetFont('helvetica', ' ', 10);
										
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr bgcolor="#cccccc">
												<td  colspan="2" ><strong>Tipo de identificador</strong></td>
												<td  colspan="4" ><strong>Tax ID/Sin Tax ID/RFC/CURP</strong></td>
											</tr>
											<tr>
												<td  colspan="2">'.$TipoID_emisor.'</td>
												<td  colspan="4" >'.$RFC_emisor .'</td>
											</tr>
											<tr bgcolor="#cccccc">
												<td  colspan="2" ><strong>Nombre(s) o Razón Social</strong></td>
												<td  colspan="2" ><strong>Apellido paterno</strong></td>
												<td  colspan="2" ><strong>Apellido materno</strong></td>
											</tr>
											<tr>
												<td  colspan="2">'.$Nombre_emisor.'</td>
												<td  colspan="2" >'.' '.'</td>
												<td  colspan="2" >'.' '.'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$pdf->SetFont('helvetica', '', 12);
										$pdf->SetColor('text',0,77,153);
										$pdf->Cell(189,6, 'Domicilio del proveedor' , 0, 1, 'L',0,'',0);						
										$pdf->SetColor('text',0,0,0);
										$pdf->SetFont('helvetica', ' ', 10);
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">							
											<tr bgcolor="#cccccc">
												<td colspan="3"><strong>Calle</strong></td>
												<td colspan="1"><strong>No. exterior</strong></td>
												<td colspan="1"><strong>No. interior</strong></td>
												<td colspan="1"><strong>Código postal</strong></td>
											</tr>
											<tr>
												<td colspan="3">'.$Calle_emisor.'</td>
												<td colspan="1">'.$Numext_emisor.'</td>
												<td colspan="1">'.$Numint_emisor.'</td>
												<td colspan="1">'.$CP_emisor.'</td>
											</tr>
											<tr bgcolor="#cccccc">
												<td colspan="3" ><strong>Colonia</strong></td>
												<td colspan="3" ><strong>Localidad</strong></td>
											</tr>
											<tr>
												<td colspan="3">'.$Colonia_emisor.'</td>
												<td colspan="3" >'.$Localidad_emisor .'</td>
											</tr>							
											<tr bgcolor="#cccccc">
												<td colspan="3" ><strong>Entidad federativa</strong></td>
												<td colspan="3" ><strong>Municipio</strong></td>
											</tr>
											<tr>
												<td colspan="3">'.$Estado_emisor.'</td>
												<td colspan="3" >'.$Municipio_emisor .'</td>
											</tr>
											<tr bgcolor="#cccccc">
												<td colspan="6" ><strong>País</strong></td>
											</tr>
											<tr>
												<td colspan="6">'.$Pais_emisor.'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
									}
									
									// ******************************************
									//DESTINATARIO
									//******************************************** 
									$pdf->SetFont('helvetica', '', 12);
									$pdf->SetColor('text',0,77,153);
									$pdf->Cell(189,6, 'Datos generales del destinatario' , 0, 1, 'L',0,'',0);					
									$pdf->SetColor('text',0,0,0);
									$pdf->SetFont('helvetica', ' ', 10);
									
									foreach( $node->getElementsByTagName("destinatario") as $destinatario ){
										switch(trim($destinatario->getElementsByTagName("tipoIdentificador")->item(0)->nodeValue)){
											case '0':
												$TipoID_destinatario = 'TAX_ID';
												break;
											case '1':
												$TipoID_destinatario = 'RFC';
												break;
											case '2':
												$TipoID_destinatario = 'CURP';
												break;
											case '3':
												$TipoID_destinatario = 'SIN TAX_ID';
												break;
										}
										$RFC_destinatario  = $destinatario->getElementsByTagName("identificacion")->item(0)->nodeValue;
										$Nombre_destinatario = $destinatario->getElementsByTagName("nombre")->item(0)->nodeValue;
										
										foreach( $destinatario->getElementsByTagName("domicilio") as $domicilio ){
											$Calle_destinatario = $domicilio->getElementsByTagName("calle")->item(0)->nodeValue;
											$Dir_destinatario = split("COL.",$Calle_destinatario);
											$Colonia_destinatario = '';
											if(count($Dir_destinatario )>1){
												$Calle_destinatario = trim($Dir_destinatario[0]);
												$Colonia_destinatario = trim($Dir_destinatario[1]);
											}
											$Dir_destinatario = split("COL",$Calle_destinatario);
											if(count($Dir_destinatario )>1){
												$Calle_destinatario= trim($Dir_destinatario[0]);
												$Colonia_destinatario = trim($Dir_destinatario[1]);
											}
											$Dir_destinatario = split("COLONIA.",$Calle_destinatario);
											if(count($Dir_destinatario )>1){
												$Calle_destinatario = trim($Dir_destinatario[0]);
												$Colonia_destinatario = trim($Dir_destinatario[1]);
											}
											$Dir_destinatario = split("COLONIA",$Calle_destinatario);
											if(count($Dir_destinatario )>1){
												$Calle_destinatario = trim($Dir_destinatario[0]);
												$Colonia_destinatario = trim($Dir_destinatario[1]);
											}
											$Numext_destinatario =  $domicilio->getElementsByTagName("numeroExterior")->item(0)->nodeValue;
											if (isset($domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue) && !empty($domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue)) {
												$Numint_destinatario =  $domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue;
											}else{
												$Numint_destinatario =  '';
											}
											if (isset($domicilio->getElementsByTagName("localidad")->item(0)->nodeValue) && !empty($domicilio->getElementsByTagName("localidad")->item(0)->nodeValue)) {
												$Localidad_destinatario =  $domicilio->getElementsByTagName("localidad")->item(0)->nodeValue;
											}else{
												$Localidad_destinatario =  '';
											}
											//$Numint_destinatario =  $domicilio->getElementsByTagName("numeroInterior")->item(0)->nodeValue;
											//$Localidad_destinatario =  $domicilio->getElementsByTagName("localidad")->item(0)->nodeValue;
											
											$Municipio_destinatario =  $domicilio->getElementsByTagName("municipio")->item(0)->nodeValue;									
											$Estado_destinatario =  $domicilio->getElementsByTagName("entidadFederativa")->item(0)->nodeValue;
											$Pais_destinatario =  $domicilio->getElementsByTagName("pais")->item(0)->nodeValue;
											$CP_destinatario =  $domicilio->getElementsByTagName("codigoPostal")->item(0)->nodeValue;
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr bgcolor="#cccccc">
												<td  colspan="2" ><strong>Tipo de identificador</strong></td>
												<td  colspan="4" ><strong>Tax ID/Sin Tax ID/RFC/CURP</strong></td>
											</tr>
											<tr>
												<td  colspan="2">'.$TipoID_destinatario.'</td>
												<td  colspan="4" >'.$RFC_destinatario .'</td>
											</tr>
											<tr bgcolor="#cccccc">
												<td  colspan="2" ><strong>Nombre(s) o Razón Social</strong></td>
												<td  colspan="2" ><strong>Apellido paterno</strong></td>
												<td  colspan="2" ><strong>Apellido materno</strong></td>
											</tr>
											<tr>
												<td  colspan="2">'.$Nombre_destinatario.'</td>
												<td  colspan="2" >'.' '.'</td>
												<td  colspan="2" >'.' '.'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}						
										$pdf->SetFont('helvetica', '', 12);
										$pdf->SetColor('text',0,77,153);
										$pdf->Cell(189,6, 'Domicilio del destinatario' , 0, 1, 'L',0,'',0);						
										$pdf->SetColor('text',0,0,0);
										$pdf->SetFont('helvetica', ' ', 10);
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">							
											<tr bgcolor="#cccccc">
												<td colspan="3"><strong>Calle</strong></td>
												<td colspan="1"><strong>No. exterior</strong></td>
												<td colspan="1"><strong>No. interior</strong></td>
												<td colspan="1"><strong>Código postal</strong></td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr>
												<td colspan="3">'.$Calle_destinatario.'</td>
												<td colspan="1">'.$Numext_destinatario.'</td>
												<td colspan="1">'.$Numint_destinatario.'</td>
												<td colspan="1">'.$CP_destinatario.'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr bgcolor="#cccccc">
												<td colspan="3" ><strong>Colonia</strong></td>
												<td colspan="3" ><strong>Localidad</strong></td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr>
												<td colspan="3">'.$Colonia_destinatario.'</td>
												<td colspan="3" >'.$Localidad_destinatario.'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr bgcolor="#cccccc">
												<td colspan="3" ><strong>Entidad federativa</strong></td>
												<td colspan="3" ><strong>Municipio</strong></td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr>
												<td colspan="3">'.$Estado_destinatario.'</td>
												<td colspan="3" >'.$Municipio_destinatario .'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr bgcolor="#cccccc">
												<td colspan="6" ><strong>País</strong></td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr>
												<td colspan="6">'.$Pais_destinatario.'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}						
									}
									
									// ******************************************
									//MERCANCIAS
									//******************************************** 
									foreach( $node->getElementsByTagName("mercancias") as $mercancia ){
										$Descripcion_Merc = $mercancia->getElementsByTagName("descripcionGenerica")->item(0)->nodeValue;
										$UM_Merc = get_descripcion_UM($mercancia->getElementsByTagName("claveUnidadMedida")->item(0)->nodeValue);
										//Bajar catalogo de ventanilla y guardad en BD para consulta
										$Cantidad_Merc = $mercancia->getElementsByTagName("cantidad")->item(0)->nodeValue;
										$TipoMoneda_Merc = get_descripcion_Moneda($mercancia->getElementsByTagName("tipoMoneda")->item(0)->nodeValue);
										//Bajar catalogo de ventanilla y guardad en BD para consulta
										$ValorUnitario_Merc = $mercancia->getElementsByTagName("valorUnitario")->item(0)->nodeValue;
										$ValorTotal_Merc = $mercancia->getElementsByTagName("valorTotal")->item(0)->nodeValue;
										$ValorDolares_Merc = $mercancia->getElementsByTagName("valorDolares")->item(0)->nodeValue;
										foreach( $mercancia->getElementsByTagName("descripcionesEspecificas") as $descripciones ){
											$Marca_Merc = $descripciones->getElementsByTagName("marca")->item(0)->nodeValue;
											$Modelo_Merc = $descripciones->getElementsByTagName("modelo")->item(0)->nodeValue;
											$SubModelo_Merc = $descripciones->getElementsByTagName("subModelo")->item(0)->nodeValue;
											$NumSerie_Merc = $descripciones->getElementsByTagName("numeroSerie")->item(0)->nodeValue;
										}
										
										$pdf->SetFont('helvetica', '', 12);
										$pdf->SetColor('text',0,77,153);
										$pdf->Cell(189,6, 'Datos de la mercancía' , 0, 1, 'L',0,'',0);						
										$pdf->SetColor('text',0,0,0);
										$pdf->SetFont('helvetica', ' ', 10);
										
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr bgcolor="#cccccc">
												<td  colspan="4" ><strong>Descripción genérica de la mercancía</strong></td>
												<td  colspan="2" ><strong>Clave UMC</strong></td>
												<td  colspan="2" ><strong>Cantidad UMC</strong></td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr>
												<td  colspan="4">'.$Descripcion_Merc.'</td>
												<td  colspan="2" >'.$UM_Merc .'</td>
												<td  colspan="2">'.$Cantidad_Merc.'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr bgcolor="#cccccc">
												<td  colspan="2" ><strong>Tipo Moneda</strong></td>
												<td  colspan="2" ><strong>Valor unitario</strong></td>
												<td  colspan="2" ><strong>Valor total</strong></td>
												<td  colspan="2" ><strong>Valor total en dólares</strong></td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr>
												<td  colspan="2">'.$TipoMoneda_Merc.'</td>
												<td  colspan="2" >'.$ValorUnitario_Merc.'</td>
												<td  colspan="2" >'.$ValorTotal_Merc.'</td>
												<td  colspan="2">'.$ValorDolares_Merc.'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										
										$pdf->SetFont('helvetica', '', 12);
										$pdf->SetColor('text',0,77,153);
										$pdf->Cell(189,6, 'Descripción de la mercancía' , 0, 1, 'L',0,'',0);						
										$pdf->SetColor('text',0,0,0);
										$pdf->SetFont('helvetica', ' ', 10);
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr bgcolor="#cccccc">
												<td  colspan="2" ><strong>Marca</strong></td>
												<td  colspan="2" ><strong>Modelo</strong></td>
												<td  colspan="2" ><strong>Submodelo</strong></td>
												<td  colspan="2" ><strong>No. serie</strong></td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}
										$sHTML = '
										<table  border="1" cellspacing="0" cellpadding="2">
											<tr>
												<td  colspan="2">'.$Marca_Merc.'</td>
												<td  colspan="2" >'.$Modelo_Merc.'</td>
												<td  colspan="2" >'.$SubModelo_Merc.'</td>
												<td  colspan="2">'.$NumSerie_Merc.'</td>
											</tr>
										</table>';
										$pdf->writeHTML($sHTML, false, false, true, false, '');
										$y = $pdf->GetY();
										if($y > 260){
											$pdf->Cell(0,35, '' , 0, 1, 'C',0,'',0);
										}						
									}
								}
								$pdf->Output($aComprobantes[$c][1].'.pdf', 'F');
								array_push($aCOVES,$aComprobantes[$c][1].'.pdf');
							}
						}
					}
				}else{
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Generar PDF COVE :: Error al leer la información del archivo ['.$directorio.$ArchCOVE.']';
					return $respuesta;
				}	
			}
		}
		$respuesta['Codigo'] = 1;
		$respuesta['aCOVES'] = $aCOVES;
		return $respuesta;
	}
	
	function get_descripcion_Moneda($MON){
		switch($MON){
			case 'AED': return 'Dirham de los Emiratos Árabes Unidos'; break;
			case 'AFN': return 'Afgani afgano'; break;
			case 'ALL': return 'Lek albanés'; break;
			case 'AMD': return 'Dram armenio'; break;
			case 'ANG': return 'Florín antillano neerlandés'; break;
			case 'AOA': return 'Kwanza angoleño'; break;
			case 'ARS': return 'Peso argentino'; break;
			case 'AUD': return 'Dólar australiano'; break;
			case 'AWG': return 'Florín arubeño'; break;
			case 'AZM': return 'Manat azerbaiyano'; break;
			case 'BAM': return 'Marco convertible de Bosnia-Herzegovina'; break;
			case 'BBD': return 'Dólar de Barbados'; break;
			case 'BDT': return 'Taka de Bangladesh'; break;
			case 'BGN': return 'Lev búlgaro'; break;
			case 'BHD': return 'Dinar bahreiní'; break;
			case 'BIF': return 'Franco burundés'; break;
			case 'BMD': return 'Dòlar de Bermuda'; break;
			case 'BND': return 'Dòlar de Brunéi'; break;
			case 'BOB': return 'Boliviano'; break;
			case 'BOV': return 'Mvdol boliviano (código de fondos)'; break;
			case 'BRL': return 'Real brasileño'; break;
			case 'BSD': return 'Dólar bahameño'; break;
			case 'BTN': return 'Ngultrum de Bután'; break;
			case 'BWP': return 'Pula de Botsuana'; break;
			case 'BYR': return 'Rublo bielorruso'; break;
			case 'BZD': return 'Dólar de Belice'; break;
			case 'CAD': return 'Dólar canadiense'; break;
			case 'CDF': return 'Franco congoleño'; break;
			case 'CHF': return 'Franco suizo'; break;
			case 'CLF': return 'Unidades de fomento chilenas (código de fondos)'; break;
			case 'CLP': return 'Peso chileno'; break;
			case 'CNY': return 'Yuan Renminbi de China'; break;
			case 'COP': return 'Peso colombiano'; break;
			case 'COU': return 'Unidad de valor real colombiana (añadida al COP)'; break;
			case 'CRC': return 'Colón costarricense'; break;
			case 'CSD': return 'Dinar serbio (Reemplazado por RSD el 25 de octubre de 2006)'; break;
			case 'CUP': return 'Peso cubano'; break;
			case 'CUC': return 'Peso cubano convertible'; break;
			case 'CVE': return 'Escudo caboverdiano'; break;
			case 'CYP': return 'Libra chipriota'; break;
			case 'CZK': return 'Koruna checa'; break;
			case 'DJF': return 'Franco yibutiano'; break;
			case 'DKK': return 'Corona danesa'; break;
			case 'DOP': return 'Peso dominicano'; break;
			case 'DZD': return 'Dinar algerino'; break;
			case 'EEK': return 'Corona estonia'; break;
			case 'EGP': return 'Libra egipcia'; break;
			case 'ERN': return 'Nakfa eritreo'; break;
			case 'ETB': return 'Birr etíope'; break;
			case 'EUR': return 'Euro'; break;
			case 'FJD': return 'Dólar fijiano'; break;
			case 'FKP': return 'Libra malvinense'; break;
			case 'GBP': return 'Libra esterlina (libra de Gran Bretaña)'; break;
			case 'GEL': return 'Lari georgiano'; break;
			case 'GHS': return 'Cedi ghanés'; break;
			case 'GIP': return 'Libra de Gibraltar'; break;
			case 'GMD': return 'Dalasi gambiano'; break;
			case 'GNF': return 'Franco guineano'; break;
			case 'GTQ': return 'Quetzal guatemalteco'; break;
			case 'GYD': return 'Dólar guyanés'; break;
			case 'HKD': return 'Dólar de Hong Kong'; break;
			case 'HNL': return 'Lempira hondureño'; break;
			case 'HRK': return 'Kuna croata'; break;
			case 'HTG': return 'Gourde haitiano'; break;
			case 'HUF': return 'Forint húngaro'; break;
			case 'IDR': return 'Rupiah indonesia'; break;
			case 'ILS': return 'Nuevo shéquel israelí'; break;
			case 'INR': return 'Rupia india'; break;
			case 'IQD': return 'Dinar iraquí'; break;
			case 'IRR': return 'Rial iraní'; break;
			case 'ISK': return 'Króna islandesa'; break;
			case 'JMD': return 'Dólar jamaicano'; break;
			case 'JOD': return 'Dinar jordano'; break;
			case 'JPY': return 'Yen japonés'; break;
			case 'KES': return 'Chelín keniata'; break;
			case 'KGS': return 'Som kirguís (de Kirguistán)'; break;
			case 'KHR': return 'Riel camboyano'; break;
			case 'KMF': return 'Franco comoriano (de Comoras)'; break;
			case 'KPW': return 'Won norcoreano'; break;
			case 'KRW': return 'Won surcoreano'; break;
			case 'KWD': return 'Dinar kuwaití'; break;
			case 'KYD': return 'Dólar caimano (de Islas Caimán)'; break;
			case 'KZT': return 'Tenge kazajo'; break;
			case 'LAK': return 'Kip lao'; break;
			case 'LBP': return 'Libra libanesa'; break;
			case 'LKR': return 'Rupia de Sri Lanka'; break;
			case 'LRD': return 'Dólar liberiano'; break;
			case 'LSL': return 'Loti lesotense'; break;
			case 'LTL': return 'Litas lituano'; break;
			case 'LVL': return 'Lat letón'; break;
			case 'LYD': return 'Dinar libio'; break;
			case 'MAD': return 'Dirham marroquí'; break;
			case 'MDL': return 'Leu moldavo'; break;
			case 'MGA': return 'Ariary malgache'; break;
			case 'MKD': return 'Denar macedonio'; break;
			case 'MMK': return 'Kyat birmano'; break;
			case 'MNT': return 'Tughrik mongol'; break;
			case 'MOP': return 'Pataca de Macao'; break;
			case 'MRO': return 'Ouguiya mauritana'; break;
			case 'MTL': return 'Lira maltesa'; break;
			case 'MUR': return 'Rupia mauricia'; break;
			case 'MVR': return 'Rufiyaa maldiva'; break;
			case 'MWK': return 'Kwacha malauí'; break;
			case 'MXN': return 'Peso mexicano'; break;
			case 'MXV': return 'Unidad de Inversión (UDI) mexicana (código de fondos)'; break;
			case 'MYR': return 'Ringgit malayo'; break;
			case 'MZN': return 'Metical mozambiqueño'; break;
			case 'NAD': return 'Dólar namibio'; break;
			case 'NGN': return 'Naira nigeriana'; break;
			case 'NIO': return 'Córdoba nicaragüense'; break;
			case 'NOK': return 'Corona noruega'; break;
			case 'NPR': return 'Rupia nepalesa'; break;
			case 'NZD': return 'Dólar neozelandés'; break;
			case 'OMR': return 'Rial omaní'; break;
			case 'PAB': return 'Balboa panameña'; break;
			case 'PEN': return 'Nuevo sol peruano'; break;
			case 'PGK': return 'Kina de Papúa Nueva Guinea'; break;
			case 'PHP': return 'Peso filipino'; break;
			case 'PKR': return 'Rupia pakistaní'; break;
			case 'PLN': return 'zloty polaco'; break;
			case 'PYG': return 'Guaraní paraguayo'; break;
			case 'QAR': return 'Rial qatarí'; break;
			case 'RON': return 'Leu rumano'; break;
			case 'RUB': return 'Rublo ruso'; break;
			case 'RWF': return 'Franco ruandés'; break;
			case 'SAR': return 'Riyal saudí'; break;
			case 'SBD': return 'Dólar de las Islas Salomón'; break;
			case 'SCR': return 'Rupia de Seychelles'; break;
			case 'SDG': return 'Dinar sudanés'; break;
			case 'SEK': return 'Corona sueca'; break;
			case 'SGD': return 'Dólar de Singapur'; break;
			case 'SHP': return 'Libra de Santa Helena'; break;
			case 'SKK': return 'Corona eslovaca'; break;
			case 'SLL': return 'Leone de Sierra Leona'; break;
			case 'SOS': return 'Chelín somalí'; break;
			case 'SRD': return 'Dólar surinamés'; break;
			case 'STD': return 'Dobra de Santo Tomé y Príncipe'; break;
			case 'SYP': return 'Libra siria'; break;
			case 'SZL': return 'Lilangeni suazi'; break;
			case 'THB': return 'Baht tailandés'; break;
			case 'TJS': return 'Somoni tayik (de Tayikistán)'; break;
			case 'TMT': return 'Manat turcomano'; break;
			case 'TND': return 'Dinar tunecino'; break;
			case 'TOP': return 'Pa\'anga tongano'; break;
			case 'TRY': return 'Lira turca'; break;
			case 'TTD': return 'Dólar de Trinidad y Tobago'; break;
			case 'TWD': return 'Dólar taiwanés'; break;
			case 'TZS': return 'Chelín tanzano'; break;
			case 'UAH': return 'Grivna ucraniana'; break;
			case 'UGX': return 'Chelín ugandés'; break;
			case 'USD': return 'Dólar estadounidense'; break;
			case 'USN': return 'Dólar estadounidense (Siguiente día) (código de fondos)'; break;
			case 'USS': return 'Dólar estadounidense (Mismo día) (código de fondos)'; break;
			case 'UYU': return 'Peso uruguayo'; break;
			case 'UZS': return 'Som uzbeko'; break;
			case 'VEF': return 'Bolívar fuerte venezolano'; break;
			case 'VND': return 'Dong vietnamita'; break;
			case 'VUV': return 'Vatu vanuatense'; break;
			case 'WST': return 'Tala samoana'; break;
			case 'XAF': return 'Franco CFA de África Central'; break;
			case 'XAG': return 'Onza de plata'; break;
			case 'XAU': return 'Onza de oro'; break;
			case 'XBA': return 'European Composite Unit (EURCO) (unidad del mercado de bonos)'; break;
			case 'XBB': return 'European Monetary Unit (E.M.U.-6) (unidad del mercado de bonos)'; break;
			case 'XBC': return 'European Unit of Account 9 (E.U.A.-9) (unidad del mercado de bonos)'; break;
			case 'XBD': return 'European Unit of Account 17 (E.U.A.-17) (unidad del mercado de bonos)'; break;
			case 'XCD': return 'Dólar del Caribe Oriental'; break;
			case 'XDR': return 'Derechos Especiales de Giro (FMI)'; break;
			case 'XFO': return 'Franco de oro (Special settlement currency)'; break;
			case 'XFU': return 'Franco UIC (Special settlement currency)'; break;
			case 'XOF': return 'Franco CFA de África Occidental'; break;
			case 'XPD': return 'Onza de paladio'; break;
			case 'XPF': return 'Franco CFP'; break;
			case 'XPT': return 'Onza de platino'; break;
			case 'XTS': return 'Reservado para pruebas'; break;
			case 'XXX': return 'Sin divisa'; break;
			case 'YER': return 'Rial yemení (de Yemen)'; break;
			case 'ZAR': return 'Rand sudafricano'; break;
			case 'ZMK': return 'Kwacha zambiano'; break;
			case 'ZWL': return 'Dólar zimbabuense'; break;
		}
	}
	
	function get_descripcion_UM($UM){
		switch($UM){
			case '5': return 'lift'; break;
			case '6': return 'small spray'; break;
			case '8': return 'heat lot'; break;
			case '10_1': return 'group'; break;
			case '11_1': return 'outfit'; break;
			case '13_1': return 'ration'; break;
			case '14_1': return 'shot'; break;
			case '15_1': return 'stick, military'; break;
			case '16_1': return 'hundred fifteen kg drum'; break;
			case '17_1': return 'hundred lb drum'; break;
			case '18_1': return 'fiftyfive gallon (US) drum'; break;
			case '19_1': return 'tank truck'; break;
			case '1A': return 'car mile'; break;
			case '1B': return 'car count'; break;
			case '1C': return 'locomotive count'; break;
			case '1D': return 'caboose count'; break;
			case '1E': return 'empy car'; break;
			case '1F': return 'train mile'; break;
			case '1G': return 'fuel usage gallon (US)'; break;
			case '1H': return 'caboose mile'; break;
			case '1I': return 'fixed rate'; break;
			case '1J': return 'ton mile'; break;
			case '1K': return 'locomotive mile'; break;
			case '1L': return 'total car count'; break;
			case '1M': return 'total car mile'; break;
			case '1X': return 'quarter mile'; break;
			case '20_1': return 'twenty foot container'; break;
			case '21_1': return 'forty foot container'; break;
			case '22': return 'decilitre per gram'; break;
			case '23': return 'gram per cubic centimetre'; break;
			case '24': return 'theoretical pound'; break;
			case '25': return 'gram per square centimetre'; break;
			case '26': return 'acual ton'; break;
			case '27': return 'theoretical ton'; break;
			case '28': return 'kilogram per square metre'; break;
			case '29': return 'pund per thousand square feet'; break;
			case '2A': return 'radian per second'; break;
			case '2B': return 'radian per second squared'; break;
			case '2C': return 'roentgen'; break;
			case '2I': return 'British thermal unit per hour'; break;
			case '2J': return 'cubic centimetre per second'; break;
			case '2K': return 'cubic foot per hour'; break;
			case '2L': return 'cubic foot per minute'; break;
			case '2M': return 'centimetre per second'; break;
			case '2N': return 'decibel'; break;
			case '2P': return 'kilobyte'; break;
			case '2Q': return 'kilobecquerel'; break;
			case '2R': return 'kilocurie'; break;
			case '2U': return 'megagram'; break;
			case '2V': return 'megagram per hour'; break;
			case '2W': return 'bin'; break;
			case '2X': return 'metre per minute'; break;
			case '2Y': return 'milliröntgen'; break;
			case '2Z': return 'millivolt'; break;
			case '30': return 'horse power dar per air dry metric ton'; break;
			case '31': return 'catch weight'; break;
			case '32': return 'kilogram per air dry metric ton'; break;
			case '33': return 'kilopascal square metres per gram'; break;
			case '34': return 'kilopascals per millimetre'; break;
			case '35': return 'millilitres per square centimetre second'; break;
			case '36': return 'cubic feet per minute per square foot'; break;
			case '37': return 'ounce per square foot'; break;
			case '38': return 'ounces per square foot per 0,01 inch'; break;
			case '3B': return 'megajoule'; break;
			case '3C': return 'manmonth'; break;
			case '3E': return 'pund per pund of product'; break;
			case '3G': return 'pound per piece of product'; break;
			case '3H': return 'kilogram per kilogram of product'; break;
			case '3I': return 'kilogram per piece of product'; break;
			case '40': return 'millilitre per second'; break;
			case '41': return 'millilitre per minute'; break;
			case '43': return 'super bulk bag'; break;
			case '44': return 'fivehundred kg bulk bag'; break;
			case '45': return 'threehundred kg bulk bag'; break;
			case '46': return 'fifty lb bulk bag'; break;
			case '47': return 'fifty lb bag'; break;
			case '48': return 'bulk car load'; break;
			case '4A': return 'bobbin'; break;
			case '4B': return 'cap'; break;
			case '4C': return 'centistokes'; break;
			case '4E': return 'twenty pack'; break;
			case '4G': return 'microlitre'; break;
			case '4H': return 'micrometre (micron)'; break;
			case '4K': return 'milliampere'; break;
			case '4L': return 'megabyte'; break;
			case '4M': return 'milligram per hour'; break;
			case '4N': return 'megabecquerel'; break;
			case '4O': return 'microfarad'; break;
			case '4P': return 'newton per metre'; break;
			case '4Q': return 'ounce inche'; break;
			case '4R': return 'ounce foot'; break;
			case '4T': return 'picofarad'; break;
			case '4U': return 'pund per hour'; break;
			case '4W': return 'ton (US) per hour'; break;
			case '4X': return 'kilolitre per hour'; break;
			case '53': return 'theoretical kilograms'; break;
			case '54': return 'theoretical tonne'; break;
			case '56': return 'sitas'; break;
			case '57': return 'mesh'; break;
			case '58': return 'net kilogram'; break;
			case '59': return 'part per million'; break;
			case '5A': return 'barrel (US) per minute'; break;
			case '5B': return 'batch'; break;
			case '5C': return 'gallon (US) per thousand'; break;
			case '5E': return 'MMSCF/day'; break;
			case '5F': return 'pounds per thousand'; break;
			case '5G': return 'pump'; break;
			case '5H': return 'stage'; break;
			case '5I': return 'standard cubic foot'; break;
			case '5J': return 'hydraulic horse power'; break;
			case '5K': return 'count per minute'; break;
			case '5P': return 'seismic level'; break;
			case '5Q': return 'seismic line'; break;
			case '60': return 'percen weight'; break;
			case '61': return 'part per billion (US)'; break;
			case '62': return 'percent per 1000 hour'; break;
			case '63': return 'failure rate in time'; break;
			case '64': return 'pound per square inch, gauge'; break;
			case '66': return 'oersted'; break;
			case '69': return 'test specific scale'; break;
			case '71': return 'volt ampere per pund'; break;
			case '72': return 'watt per pound'; break;
			case '73': return 'ampere tum per centimetre'; break;
			case '74': return 'millipascal'; break;
			case '76': return 'gauss'; break;
			case '77': return 'milli-inch'; break;
			case '78': return 'kilogauss'; break;
			case '80': return 'pounds per sauqre inche absoulte'; break;
			case '81': return 'henry'; break;
			case '84': return 'kilopound per square inch'; break;
			case '85': return 'foot pound-force'; break;
			case '87': return 'pound per cubic foot'; break;
			case '89': return 'poise'; break;
			case '90': return 'saybold universal second'; break;
			case '91': return 'stokes'; break;
			case '92': return 'calorie per cubic centimetre'; break;
			case '93': return 'calorie per gram'; break;
			case '94': return 'curl unit'; break;
			case '95': return 'twenty thousand gallon (US) tankcar'; break;
			case '96': return 'ten thousand gallon (US) tankcar'; break;
			case '97': return 'ten kg drum'; break;
			case '98': return 'fifteen kg drum'; break;
			case 'A1': return '15° C Calorie'; break;
			case 'A10': return 'ampere square metre per joule second'; break;
			case 'A11': return 'angstrom'; break;
			case 'A12': return 'astronomical unit'; break;
			case 'A13': return 'attojoule'; break;
			case 'A14': return 'barn'; break;
			case 'A15': return 'barn per electron volt'; break;
			case 'A16': return 'barn per steradian electronvolt'; break;
			case 'A17': return 'barn per steradian'; break;
			case 'A18': return 'becquerel per kilogram'; break;
			case 'A19': return 'becquerel per metre cubed'; break;
			case 'A2': return 'ampere per centimetre'; break;
			case 'A20': return 'British thermal unit per second square foot degree Rankin'; break;
			case 'A21': return 'British thermal unit per pound degree Rankin'; break;
			case 'A22': return 'British thermal unit per second foot degree Rankin'; break;
			case 'A23': return 'Brithis thermal unit per hour square foot degree Rankin'; break;
			case 'A24': return 'candela per square metre'; break;
			case 'A25': return 'cheval vapeur'; break;
			case 'A26': return 'coulomb metre'; break;
			case 'A27': return 'coulomb metre squared per volt'; break;
			case 'A28': return 'coulomb per cubic centimetre'; break;
			case 'A29': return 'coulomb per cubic metre'; break;
			case 'A3': return 'ampere per millimetre'; break;
			case 'A30': return 'coulomb per cubic millimetre'; break;
			case 'A31': return 'coulomb per kilogram second'; break;
			case 'A32': return 'coulomb per mole'; break;
			case 'A33': return 'coulomb per square centimetre'; break;
			case 'A34': return 'coulomb per square metre'; break;
			case 'A35': return 'coulomb per square millimetre'; break;
			case 'A36': return 'cubic centimetre per mole'; break;
			case 'A37': return 'cubic decimetre per mole'; break;
			case 'A38': return 'cubic metre per coulomb'; break;
			case 'A39': return 'cubic metre per kilogram'; break;
			case 'A4': return 'ampere per squre centrimetre'; break;
			case 'A40': return 'cubic metre per mole'; break;
			case 'A41': return 'ampere per square metre'; break;
			case 'A42': return 'curie per kilogram'; break;
			case 'A43': return 'deadweight tonnage'; break;
			case 'A44': return 'decalitre'; break;
			case 'A45': return 'decametre'; break;
			case 'A47': return 'decitex'; break;
			case 'A48': return 'degree Rankin'; break;
			case 'A49': return 'denier'; break;
			case 'A5': return 'ampere square metre'; break;
			case 'A50': return 'dyne second per cubic centimetre'; break;
			case 'A51': return 'dyne second per centimetre'; break;
			case 'A52': return 'dyne second per centimetre to the fifth power'; break;
			case 'A53': return 'electronvolt'; break;
			case 'A54': return 'electronvolt per metre'; break;
			case 'A55': return 'electronvolt square metre'; break;
			case 'A56': return 'electronvolt square metre per kilogram'; break;
			case 'A57': return 'erg'; break;
			case 'A58': return 'erg per centimetre'; break;
			case 'A59': return '8-part cloud cover'; break;
			case 'A6': return 'ampere per square metre kelvin squared'; break;
			case 'A60': return 'erg per cubic centimetre'; break;
			case 'A61': return 'erg per gram'; break;
			case 'A62': return 'erg per gram second'; break;
			case 'A63': return 'erg per second'; break;
			case 'A64': return 'erg per second square centimetre'; break;
			case 'A65': return 'erg per square centimetre second'; break;
			case 'A66': return 'erg square centimetre'; break;
			case 'A67': return 'erg sqtuare centimetre per gram'; break;
			case 'A68': return 'exajoule'; break;
			case 'A69': return 'farad per metre'; break;
			case 'A7': return 'ampere per square millimetre'; break;
			case 'A70': return 'femtojoule'; break;
			case 'A71': return 'femtometre'; break;
			case 'A73': return 'foot per second squared'; break;
			case 'A74': return 'foot pound-force per second'; break;
			case 'A75': return 'freight ton'; break;
			case 'A76': return 'gallon (US) per thousand'; break;
			case 'A77': return 'Gaussian CGS unit of displacement'; break;
			case 'A78': return 'Gaussian CGS unit of electric current'; break;
			case 'A79': return 'Gaussian CGS unit of electric charge'; break;
			case 'A8': return 'ampere second'; break;
			case 'A80': return 'Gaussian CGS unit of electric field strenght'; break;
			case 'A81': return 'Gaussian CGS unit of electric polarization'; break;
			case 'A82': return 'Gaussian CGS unit of electric potencial'; break;
			case 'A83': return 'Gaussian CGS unit of magnetization'; break;
			case 'A84': return 'gigacoulomb per cubic metre'; break;
			case 'A85': return 'gigaelectronvolt'; break;
			case 'A86': return 'gigahertz'; break;
			case 'A87': return 'gigaohm'; break;
			case 'A88': return 'gigaohm metre'; break;
			case 'A89': return 'gigapascal'; break;
			case 'A9': return 'rate'; break;
			case 'A90': return 'gigawatt'; break;
			case 'A91': return 'gon'; break;
			case 'A92': return 'grade'; break;
			case 'A93': return 'gram per cubic metre'; break;
			case 'A94': return 'gram per mole'; break;
			case 'A95': return 'gray'; break;
			case 'A96': return 'gray per second'; break;
			case 'A97': return 'hectopascal'; break;
			case 'A98': return 'henry per metre'; break;
			case 'A99': return 'bit'; break;
			case 'AA': return 'ball'; break;
			case 'AB': return 'bulk pack'; break;
			case 'ACR': return 'acre'; break;
			case 'ACT': return 'activity'; break;
			case 'AD': return 'byte'; break;
			case 'AE': return 'ampere per metre'; break;
			case 'AH': return 'additional minute'; break;
			case 'AI': return 'average minute per call'; break;
			case 'AJ': return 'cop'; break;
			case 'AK': return 'fathom'; break;
			case 'AL': return 'access line'; break;
			case 'AM': return 'ampoule'; break;
			case 'AMH': return 'apere hour'; break;
			case 'AMP': return 'ampere'; break;
			case 'ANN': return 'year'; break;
			case 'AP': return 'aluminum pound only'; break;
			case 'APZ': return 'troy ounce ot apothecary ounce'; break;
			case 'AQ': return 'anti-hemophilic factor (AHF) unit'; break;
			case 'AR': return 'suppository'; break;
			case 'ARE': return 'are'; break;
			case 'AS': return 'assortment'; break;
			case 'ASM': return 'alcoholic strenght by mass'; break;
			case 'ASU': return 'alcoholic strenght by volume'; break;
			case 'ATM': return 'standard atmosphere'; break;
			case 'ATT': return 'technical atmosphere'; break;
			case 'AV': return 'capsule'; break;
			case 'AW': return 'powder filled vial'; break;
			case 'AY': return 'assembly'; break;
			case 'AZ': return 'Brithis thermal unit per pound'; break;
			case 'B0': return 'Btu per cubic foot'; break;
			case 'B1': return 'barrel (US) per day'; break;
			case 'B10': return 'bit per second'; break;
			case 'B11': return 'joule per kilogram kelvin'; break;
			case 'B12': return 'joule per metre'; break;
			case 'B13': return 'joule per metre squared'; break;
			case 'B13_1': return 'joule per square metre'; break;
			case 'B14': return 'joule per metre to the fourth power'; break;
			case 'B15': return 'joule per mole'; break;
			case 'B16': return 'joule per mole kelvin'; break;
			case 'B17': return 'credit'; break;
			case 'B18': return 'joule second'; break;
			case 'B19': return 'digit'; break;
			case 'B2': return 'bunk'; break;
			case 'B20': return 'joule square metre per kilogram'; break;
			case 'B21': return 'kelvin per watt'; break;
			case 'B22': return 'kiloampere'; break;
			case 'B23': return 'kiloampere per square metre'; break;
			case 'B24': return 'kiloampere per metre'; break;
			case 'B25': return 'kilobecquerel per kilogram'; break;
			case 'B26': return 'kilocoulomb'; break;
			case 'B27': return 'kilocoulomb per cubic metre'; break;
			case 'B28': return 'kilocoulomb per square metre'; break;
			case 'B29': return 'kiloelectronvolt'; break;
			case 'B3': return 'battin pound'; break;
			case 'B30': return 'gidibit'; break;
			case 'B31': return 'kilogram metre per second'; break;
			case 'B32': return 'kilogram metre squared'; break;
			case 'B33': return 'kilogram metre squared per second'; break;
			case 'B34': return 'kilogram per cubic decimetre'; break;
			case 'B35': return 'kilogram per litre'; break;
			case 'B35_1': return 'kilogram per litre of product'; break;
			case 'B36': return 'thermochemical calorie per gram'; break;
			case 'B37': return 'kilogram-force'; break;
			case 'B38': return 'kilogram-force metre'; break;
			case 'B39': return 'kilogram-force metre per second'; break;
			case 'B4': return 'barrel,imperial'; break;
			case 'B40': return 'kilogram-force per square metre'; break;
			case 'B41': return 'kilojoule per kelvin'; break;
			case 'B42': return 'kilojoule per kilogram'; break;
			case 'B43': return 'kilojoule per kilogram kelvin'; break;
			case 'B44': return 'kilojoule per mole'; break;
			case 'B45': return 'kilomole'; break;
			case 'B46': return 'kilomole per cubic metre'; break;
			case 'B47': return 'kilonewton'; break;
			case 'B48': return 'kilonewton metre'; break;
			case 'B49': return 'kiloohm'; break;
			case 'B5': return 'billet'; break;
			case 'B50': return 'kiloohm metre'; break;
			case 'B51': return 'kilopond'; break;
			case 'B52': return 'kilosecond'; break;
			case 'B53': return 'kilosiemens'; break;
			case 'B54': return 'kilosiemens pe rmetre'; break;
			case 'B55': return 'kilovolt per metre'; break;
			case 'B56': return 'kiloweber per metre'; break;
			case 'B57': return 'light year'; break;
			case 'B58': return 'litre per mole'; break;
			case 'B59': return 'lumen hour'; break;
			case 'B6': return 'bunk'; break;
			case 'B60': return 'lumen per square metre'; break;
			case 'B61': return 'lumen per watt'; break;
			case 'B62': return 'lumen second'; break;
			case 'B63': return 'lux hour'; break;
			case 'B64': return 'lux second'; break;
			case 'B65': return 'maxwell'; break;
			case 'B66': return 'megaampere per square metre'; break;
			case 'B67': return 'megabecquerel per kilogram'; break;
			case 'B68': return 'gigabit'; break;
			case 'B69': return 'megacoulomb per cubic metre'; break;
			case 'B7': return 'cycle'; break;
			case 'B70': return 'megacoulomb per square metre'; break;
			case 'B71': return 'megaelectronvolt'; break;
			case 'B72': return 'megagram per cubic metre'; break;
			case 'B73': return 'meganewton'; break;
			case 'B74': return 'meganewton metre'; break;
			case 'B75': return 'megaohm'; break;
			case 'B76': return 'megaohm metre'; break;
			case 'B77': return 'megasiemens per metre'; break;
			case 'B78': return 'megavolt'; break;
			case 'B79': return 'megavolt per metre'; break;
			case 'B8': return 'joule per cubic metre'; break;
			case 'B80': return 'gigabit per second'; break;
			case 'B81': return 'reciprocal metre squared reciprocal second'; break;
			case 'B82': return 'inche per linear foot'; break;
			case 'B83': return 'metre to the fourth power'; break;
			case 'B84': return 'microampere'; break;
			case 'B85': return 'microbar'; break;
			case 'B86': return 'microcoulomb'; break;
			case 'B87': return 'microcoulomb per cubic metre'; break;
			case 'B88': return 'microcoulomb per square metre'; break;
			case 'B89': return 'microfarad per metre'; break;
			case 'B9': return 'battin pound'; break;
			case 'B90': return 'microhenry'; break;
			case 'B91': return 'microhenry per metre'; break;
			case 'B92': return 'micronewton'; break;
			case 'B93': return 'micronewton metre'; break;
			case 'B94': return 'miocroohm'; break;
			case 'B95': return 'microohm metre'; break;
			case 'B96': return 'micropascal'; break;
			case 'B97': return 'microradian'; break;
			case 'B98': return 'microsecond'; break;
			case 'B99': return 'microsiemens'; break;
			case 'BAR': return 'bar [unit of pressure]'; break;
			case 'BB': return 'base box'; break;
			case 'BD': return 'board'; break;
			case 'BE': return 'bundle'; break;
			case 'BFT': return 'board foot'; break;
			case 'BG': return 'bag'; break;
			case 'BH': return 'brush'; break;
			case 'BHP': return 'brake horse power'; break;
			case 'BIL_EUR': return 'billion (EUR)'; break;
			case 'BIL_US': return 'trillion (US)'; break;
			case 'BJ': return 'bucket'; break;
			case 'BK': return 'basket'; break;
			case 'BL': return 'bale'; break;
			case 'BLD': return 'dry barrel (S)'; break;
			case 'BLL': return 'barrel (US)'; break;
			case 'BO': return 'bottle'; break;
			case 'BP': return 'hundred board feet'; break;
			case 'BQL': return 'becquerel'; break;
			case 'BR': return 'bar [unit of packaging]'; break;
			case 'BT': return 'bolt'; break;
			case 'BTU': return 'British thermal unit'; break;
			case 'BUA': return 'bushel (US)'; break;
			case 'BUI': return 'bushel (UK)'; break;
			case 'BW': return 'base weight'; break;
			case 'BX': return 'box'; break;
			case 'BZ': return 'million BTUs'; break;
			case 'C0': return 'call'; break;
			case 'C1': return 'composite product pound (total weight)'; break;
			case 'C10': return 'millifarad'; break;
			case 'C10_1': return 'millivolt per metre'; break;
			case 'C11': return 'milligal'; break;
			case 'C12': return 'milligram per metre'; break;
			case 'C13': return 'milligray'; break;
			case 'C14': return 'millihenry'; break;
			case 'C15': return 'millijoule'; break;
			case 'C16': return 'millimetre per second'; break;
			case 'C17': return 'millimetre squared per second'; break;
			case 'C18': return 'millimole'; break;
			case 'C19': return 'mole per kilogram'; break;
			case 'C2': return 'carset'; break;
			case 'C20': return 'millnewton'; break;
			case 'C21': return 'kibibit'; break;
			case 'C22': return 'millinewton per metre'; break;
			case 'C23': return 'milliohm metre'; break;
			case 'C24': return 'millipascal second'; break;
			case 'C25': return 'milliradian'; break;
			case 'C26': return 'millisecond'; break;
			case 'C27': return 'millisiemens'; break;
			case 'C28': return 'millisievert'; break;
			case 'C29': return 'millitesla'; break;
			case 'C3': return 'microvolt per metre'; break;
			case 'C31': return 'milliwatt'; break;
			case 'C32': return 'milliwatt per square metre'; break;
			case 'C33': return 'milliweber'; break;
			case 'C34': return 'mole'; break;
			case 'C35': return 'mole per cubic decimetre'; break;
			case 'C36': return 'mole per cubic metre'; break;
			case 'C37': return 'kilobit'; break;
			case 'C38': return 'mole per litre'; break;
			case 'C39': return 'nanoampere'; break;
			case 'C4': return 'carload'; break;
			case 'C40': return 'nanocoulomb'; break;
			case 'C41': return 'nanofarad'; break;
			case 'C42': return 'nanofarad per metre'; break;
			case 'C43': return 'nanohenry'; break;
			case 'C44': return 'nanohenry per metre'; break;
			case 'C45': return 'nanometre'; break;
			case 'C46': return 'nanoohm metre'; break;
			case 'C47': return 'nanosecond'; break;
			case 'C48': return 'nanotesla'; break;
			case 'C49': return 'nanowatt'; break;
			case 'C5': return 'cost'; break;
			case 'C50': return 'neper'; break;
			case 'C51': return 'neper per second'; break;
			case 'C52': return 'picometre'; break;
			case 'C53': return 'newton metre second'; break;
			case 'C54': return 'newton metre squared kilogram squared'; break;
			case 'C55': return 'newton per square metre'; break;
			case 'C56': return 'newton per square millimetre'; break;
			case 'C57': return 'newton second'; break;
			case 'C58': return 'newton second per metre'; break;
			case 'C59': return 'octave'; break;
			case 'C6': return 'cell'; break;
			case 'C60': return 'ohm centimetre'; break;
			case 'C61': return 'ohm metre'; break;
			case 'C62': return 'one'; break;
			case 'C62_1': return 'piece'; break;
			case 'C62_2': return 'unit'; break;
			case 'C63': return 'parsec'; break;
			case 'C64': return 'pascal per kelvin'; break;
			case 'C65': return 'pascal per second'; break;
			case 'C66': return 'pascal second per cubic metre'; break;
			case 'C67': return 'pascal second per metre'; break;
			case 'C68': return 'petajoule'; break;
			case 'C69': return 'phon'; break;
			case 'C7': return 'centipoise'; break;
			case 'C70': return 'picoampere'; break;
			case 'C71': return 'picocoulomb'; break;
			case 'C72': return 'picofarad per metre'; break;
			case 'C73': return 'picohenry'; break;
			case 'C74': return 'kilobit per second'; break;
			case 'C75': return 'picowatt'; break;
			case 'C76': return 'picowatt per square metre'; break;
			case 'C77': return 'pund page'; break;
			case 'C78': return 'pund-force'; break;
			case 'C79': return 'kilovolt ampere hour'; break;
			case 'C8': return 'millicoulomb per kilogram'; break;
			case 'C80': return 'rad'; break;
			case 'C81': return 'radian'; break;
			case 'C82': return 'radian square metre per mole'; break;
			case 'C83': return 'radian square metre per kilogram'; break;
			case 'C84': return 'radian per metre'; break;
			case 'C85': return 'reciprocal angstrom'; break;
			case 'C86': return 'reciprocal cubic metre'; break;
			case 'C87': return 'reciprocal cubic metre per second'; break;
			case 'C88': return 'reciprocal electron volt per cubic metre'; break;
			case 'C89': return 'reciprocal henry'; break;
			case 'C9': return 'coil group'; break;
			case 'C90': return 'reciprocal joul eper cubic metre'; break;
			case 'C91': return 'reciprocal kelvin or kelvin to the power minus one'; break;
			case 'C92': return 'reciprocal metre'; break;
			case 'C93': return 'reciprocal metre squared'; break;
			case 'C93_1': return 'reciprocal squared metre'; break;
			case 'C94': return 'reciprocal minute'; break;
			case 'C95': return 'reciprocal mole'; break;
			case 'C96': return 'reciprocal pascal or pascal to the power minus one'; break;
			case 'C97': return 'reciprocal second'; break;
			case 'C98': return 'reciprocal second per cubic metre'; break;
			case 'C99': return 'reciprocal second per metre squared'; break;
			case 'CA': return 'can'; break;
			case 'CCT': return 'carrying capacity in metric ton'; break;
			case 'CDL': return 'candela'; break;
			case 'CEL': return 'degree Celsius'; break;
			case 'CEN': return 'hundred'; break;
			case 'CG': return 'card'; break;
			case 'CGM': return 'centigram'; break;
			case 'CH': return 'container'; break;
			case 'CJ': return 'cone'; break;
			case 'CK': return 'connector'; break;
			case 'CKG': return 'coulomb per kilogram'; break;
			case 'CL': return 'coil'; break;
			case 'CLF': return 'hundred leave'; break;
			case 'CLT': return 'centilitre'; break;
			case 'CMK': return 'square centimetre'; break;
			case 'CMQ': return 'cubic centrimetre'; break;
			case 'CMT': return 'centimetre'; break;
			case 'CNP': return 'hundred pack'; break;
			case 'CNT': return 'cental (UK)'; break;
			case 'CO': return 'carboy'; break;
			case 'COU': return 'coulomb'; break;
			case 'CQ': return 'cartridge'; break;
			case 'CR': return 'crate'; break;
			case 'CS': return 'case'; break;
			case 'CT': return 'carton'; break;
			case 'CTG': return 'content gram'; break;
			case 'CTM': return 'metric carat'; break;
			case 'CTN': return 'content ton (metric)'; break;
			case 'CU': return 'cup'; break;
			case 'CUR': return 'curie'; break;
			case 'CV': return 'cover'; break;
			case 'CWA': return 'hundred pounds (cwt)/hundred weight (US)'; break;
			case 'CWI': return 'hundred weight (UK)'; break;
			case 'CY': return 'cylinder'; break;
			case 'CZ': return 'combo'; break;
			case 'D03': return 'kilowatt hour per hour'; break;
			case 'D04': return 'lot [unit of weight]'; break;
			case 'D1': return 'reciprocal second per steradian'; break;
			case 'D10': return 'siemens per metre'; break;
			case 'D11': return 'mebibit'; break;
			case 'D12': return 'siemens square metre per mole'; break;
			case 'D13': return 'sievert'; break;
			case 'D14': return 'thousand linear yard'; break;
			case 'D15': return 'sone'; break;
			case 'D16': return 'square centimetre per erg'; break;
			case 'D17': return 'square centimetre per steradian erg'; break;
			case 'D18': return 'metre kelvin'; break;
			case 'D19': return 'square metre kelvin per watt'; break;
			case 'D2': return 'reciprocal second per steradian metre squared'; break;
			case 'D20': return 'square metre per joule'; break;
			case 'D21': return 'square metre per kilogram'; break;
			case 'D22': return 'square metre per mole'; break;
			case 'D23': return 'pen gram (protein)'; break;
			case 'D24': return 'square metre per steradian'; break;
			case 'D25': return 'square metre per steradian joule'; break;
			case 'D26': return 'square metre per volt second'; break;
			case 'D27': return 'steradian'; break;
			case 'D28': return 'syphon'; break;
			case 'D29': return 'terahertz'; break;
			case 'D30': return 'terajoule'; break;
			case 'D31': return 'terawatt'; break;
			case 'D32': return 'terawatt hour'; break;
			case 'D33': return 'tesla'; break;
			case 'D34': return 'tex'; break;
			case 'D35': return 'thermochemical calorie'; break;
			case 'D36': return 'megabit'; break;
			case 'D37': return 'thermochemical calorie per gram kelvin'; break;
			case 'D38': return 'thermochemical calorie per second centimetre kelvin'; break;
			case 'D39': return 'thermochemical calorie per second sqaure centimetre kelvin'; break;
			case 'D40': return 'thousand litre'; break;
			case 'D41': return 'tonne per cubic metre'; break;
			case 'D42': return 'tropical year'; break;
			case 'D43': return 'unified atomic mass unit'; break;
			case 'D44': return 'var'; break;
			case 'D45': return 'volt squared per kelvin squared'; break;
			case 'D46': return 'volt-ampere'; break;
			case 'D47': return 'volt per centimetre'; break;
			case 'D48': return 'volt per kelvin'; break;
			case 'D49': return 'milivolt per kelvin'; break;
			case 'D5': return 'kilogram per square centimetre'; break;
			case 'D50': return 'volt per metre'; break;
			case 'D51': return 'volt per millimetre'; break;
			case 'D52': return 'watt per kelvin'; break;
			case 'D53': return 'wat per metre kelvin'; break;
			case 'D54': return 'watt per square metre'; break;
			case 'D55': return 'watt per square metre kelvin'; break;
			case 'D56': return 'watt per square metre kelvin to the fourth power'; break;
			case 'D57': return 'watt per steradian'; break;
			case 'D58': return 'watt per steradian square metre'; break;
			case 'D59': return 'weber per metre'; break;
			case 'D6': return 'roentgen per second'; break;
			case 'D60': return 'weber per millimetre'; break;
			case 'D61': return 'minute [unit of angle]'; break;
			case 'D62': return 'second [unit of angle]'; break;
			case 'D63': return 'book'; break;
			case 'D64': return 'block'; break;
			case 'D65': return 'round'; break;
			case 'D66': return 'cassette'; break;
			case 'D67': return 'dollar per hour'; break;
			case 'D68': return 'number of words'; break;
			case 'D69': return 'inch to the fourth power'; break;
			case 'D7': return 'sandwich'; break;
			case 'D70': return 'International Table (IT) calorie)'; break;
			case 'D71': return 'International Table (IT) calorie per second centimetre kelvin'; break;
			case 'D72': return 'International Table (IT) calorie per second square centimetre kelvin'; break;
			case 'D73': return 'joule square metre per kilogram'; break;
			case 'D74': return 'kilogram per mole'; break;
			case 'D75': return 'International Table (IT) calorie per gram'; break;
			case 'D76': return 'International Table (IT) calorie per gram kelvin'; break;
			case 'D77': return 'megacoulomb'; break;
			case 'D78': return 'megajoule per second'; break;
			case 'D79': return 'beam'; break;
			case 'D8': return 'draize score'; break;
			case 'D80': return 'microwatt'; break;
			case 'D81': return 'microtesla'; break;
			case 'D82': return 'microvolt per metre'; break;
			case 'D83': return 'millinewton metre'; break;
			case 'D85': return 'microwatt per square metre'; break;
			case 'D86': return 'millicoulomb per kilogram'; break;
			case 'D87': return 'millimole per kilogram'; break;
			case 'D88': return 'millicoulomb per cubic metre'; break;
			case 'D89': return 'millicoulomb per square metre'; break;
			case 'D9': return 'dyne per square centimetre'; break;
			case 'D90': return 'cubic metre (net'; break;
			case 'D91': return 'rem'; break;
			case 'D92': return 'band'; break;
			case 'D93': return 'second per cubic metre'; break;
			case 'D94': return 'second per radian cubic metre'; break;
			case 'D95': return 'joule per gram'; break;
			case 'D96': return 'pound gross'; break;
			case 'D97': return 'pallet/unit load'; break;
			case 'D98': return 'mass pound'; break;
			case 'D99': return 'sleeve'; break;
			case 'DAA': return 'decare'; break;
			case 'DAD': return 'ten day'; break;
			case 'DAY': return 'day'; break;
			case 'DB': return 'dry pound'; break;
			case 'DC': return 'disk (disc)'; break;
			case 'DD': return 'degree Celsius hundred'; break;
			case 'DD_1': return 'degree [unit of angle]'; break;
			case 'DE': return 'deal'; break;
			case 'DEC': return 'decade'; break;
			case 'DG': return 'decigram'; break;
			case 'DI': return 'dispense'; break;
			case 'DJ': return 'decagram'; break;
			case 'DKW': return 'kilogram drained net weight'; break;
			case 'DLT': return 'decilitre'; break;
			case 'DMK': return 'square decimetre'; break;
			case 'DMO': return 'standard kilolitre'; break;
			case 'DMQ': return 'cubic decimetre per mole'; break;
			case 'DMT': return 'decimetre'; break;
			case 'DN': return 'decinewton metre'; break;
			case 'DPC': return 'dozen piece'; break;
			case 'DPR': return 'dozen pair'; break;
			case 'DPT': return 'displacement tonnage'; break;
			case 'DQ': return 'data record'; break;
			case 'DR': return 'drum'; break;
			case 'DRA': return 'dram (US)'; break;
			case 'DRI': return 'dram (UK)'; break;
			case 'DRL': return 'dozen roll'; break;
			case 'DRM': return 'drachm (UK)'; break;
			case 'DS': return 'display'; break;
			case 'DT': return 'dry ton'; break;
			case 'DTN': return 'centner, metric 100kg'; break;
			case 'DTN_1': return 'decitonne'; break;
			case 'DTN_2': return 'quintal, metric 100kg'; break;
			case 'DU': return 'dyne'; break;
			case 'DWT': return 'pennyweight'; break;
			case 'DX': return 'dyne per centimetre'; break;
			case 'DY': return 'directory book'; break;
			case 'DZN': return 'dozen'; break;
			case 'DZP': return 'dozen pack'; break;
			case 'E01': return 'newton per square centimetre'; break;
			case 'E07': return 'megawatt hour per hour'; break;
			case 'E08': return 'megawatt per hertz'; break;
			case 'E09': return 'milliampere hour'; break;
			case 'E10': return 'degree days'; break;
			case 'E11': return 'gigacalorie'; break;
			case 'E12': return 'mille'; break;
			case 'E14': return 'kilocalorie (IT)'; break;
			case 'E15': return 'kilocalorie (TH) per hour'; break;
			case 'E16': return 'million BTU (IT) per hour'; break;
			case 'E17': return 'cubic foot per second'; break;
			case 'E18': return 'tonne per hour'; break;
			case 'E19': return 'ping'; break;
			case 'E2': return 'belt'; break;
			case 'E20': return 'megabit per second'; break;
			case 'E21': return 'shares'; break;
			case 'E22': return 'TEU'; break;
			case 'E23': return 'tyre'; break;
			case 'E25': return 'active unit'; break;
			case 'E27': return 'dose'; break;
			case 'E28': return 'air dry ton'; break;
			case 'E3': return 'trailer'; break;
			case 'E30': return 'strand'; break;
			case 'E31': return 'square metre per litre'; break;
			case 'E32': return 'litre per hour'; break;
			case 'E33': return 'foot per thousand'; break;
			case 'E34': return 'gigabyte'; break;
			case 'E35': return 'terabyte'; break;
			case 'E36': return 'petabyte'; break;
			case 'E37': return 'pixel'; break;
			case 'E38': return 'megapixel'; break;
			case 'E39': return 'dots per inch'; break;
			case 'E4': return 'gross kilogram'; break;
			case 'E40': return 'part per hundred thousand'; break;
			case 'E41': return 'kilogram force per square millimetre'; break;
			case 'E42': return 'kilogram force per square centimetre'; break;
			case 'E43': return 'joule per square centimetre'; break;
			case 'E44': return 'kilogram-force metre per square centimetre'; break;
			case 'E5': return 'metric long ton'; break;
			case 'EA': return 'each'; break;
			case 'EB': return 'electronica mail box'; break;
			case 'EC': return 'each per month'; break;
			case 'EP': return 'eleven pack'; break;
			case 'EQ': return 'equivalen gallon'; break;
			case 'EV': return 'envelope'; break;
			case 'F1': return 'thousand cubic feet per day'; break;
			case 'F9': return 'fibre per cubic centimetre of air'; break;
			case 'FAH': return 'degree Fahrenheit'; break;
			case 'FAR': return 'farad'; break;
			case 'FB': return 'field'; break;
			case 'FBM': return 'fibre metre'; break;
			case 'FC': return 'thousand cubic feet'; break;
			case 'FD': return 'million particle per cubic foot'; break;
			case 'FE': return 'track foot'; break;
			case 'FF': return 'hundred cubic metre'; break;
			case 'FG': return 'transdermal patch'; break;
			case 'FH': return 'micromole'; break;
			case 'FL': return 'flake ton'; break;
			case 'FM': return 'million cubic feet'; break;
			case 'FOT': return 'foot per thousand'; break;
			case 'FP': return 'pound per square foot'; break;
			case 'FR': return 'foot per minute'; break;
			case 'FS': return 'foot per second squared'; break;
			case 'FTK': return 'square foot'; break;
			case 'FTQ': return 'cubic foot'; break;
			case 'G2': return 'US gallon per minute'; break;
			case 'G3': return 'Imperial gallon per minute'; break;
			case 'G7': return 'microfiche sheet'; break;
			case 'GB': return 'gallon (US) per day'; break;
			case 'GBQ': return 'gigabecquerel'; break;
			case 'GC': return 'gram per 100 gram'; break;
			case 'GD': return 'gross barrel'; break;
			case 'GDW': return 'gram, dry weight'; break;
			case 'GE': return 'pound per gallon (US)'; break;
			case 'GF': return 'gram per metre (gram per 100 centimetres'; break;
			case 'GFI': return 'gram of fissile isotope'; break;
			case 'GGR': return 'great gross'; break;
			case 'GH': return 'half gallon'; break;
			case 'GIA': return 'gill'; break;
			case 'GIC': return 'gram, including container'; break;
			case 'GII': return 'gill (UK)'; break;
			case 'GIP': return 'gram, including inner packaging'; break;
			case 'GJ': return 'gram per millilitre'; break;
			case 'GK': return 'gram per kilogram'; break;
			case 'GL': return 'gram per litre'; break;
			case 'GLD': return 'dry gallon (S)'; break;
			case 'GLI': return 'gallon (UK)'; break;
			case 'GLL': return 'gallon (US)'; break;
			case 'GM': return 'gram per square metre'; break;
			case 'GN': return 'gross gallon'; break;
			case 'GO': return 'milligrams per square metre'; break;
			case 'GP': return 'milligrams per cubic metre'; break;
			case 'GQ': return 'microgram per cubic metre'; break;
			case 'GRM': return 'gram'; break;
			case 'GRN': return 'grain'; break;
			case 'GRO': return 'gross'; break;
			case 'GRT': return 'gross register ton'; break;
			case 'GT': return 'gross ton'; break;
			case 'GT_1': return 'metric gross ton'; break;
			case 'GV': return 'gigajoule'; break;
			case 'GW': return 'gallon per thousand cubic feet'; break;
			case 'GWH': return 'gigawatt hour'; break;
			case 'GY': return 'gross yard'; break;
			case 'GZ': return 'gage-system'; break;
			case 'H1': return 'half page- electronic'; break;
			case 'H2': return 'half litr'; break;
			case 'HA': return 'hank'; break;
			case 'HAR': return 'hecare'; break;
			case 'HBA': return 'hectobar'; break;
			case 'HBX': return 'hundred boxes'; break;
			case 'HC': return 'hundred count'; break;
			case 'HD': return 'half dozen'; break;
			case 'HDW': return 'hundred kilogram, dry wieight'; break;
			case 'HE': return 'hundredth of a carat'; break;
			case 'HF': return 'hundred feet'; break;
			case 'HGM': return 'hectogram'; break;
			case 'HH': return 'hundred cubic feet'; break;
			case 'HI': return 'hundred sheet'; break;
			case 'HIU': return 'hundred international unit'; break;
			case 'HJ': return 'metric horse power'; break;
			case 'HK': return 'hundred kilogram, dry wieight'; break;
			case 'HKM': return 'hundred kilogram, net mass'; break;
			case 'HL': return 'hundred feet (linear)'; break;
			case 'HLT': return 'hectolitre'; break;
			case 'HM': return 'mile per hour'; break;
			case 'HMQ': return 'million cubic metre'; break;
			case 'HMT': return 'hectometre'; break;
			case 'HN': return 'conventional millimetre of mercury'; break;
			case 'HO': return 'hundred troy ounce'; break;
			case 'HP': return 'conventional millimetre of water'; break;
			case 'HPA': return 'hectolitre of pure alcohol'; break;
			case 'HS': return 'hundred square feet'; break;
			case 'HT': return 'half hour'; break;
			case 'HTZ': return 'hertz'; break;
			case 'HUR': return 'hour'; break;
			case 'HY': return 'hundred yard'; break;
			case 'IA': return 'inche pound (pound inch)'; break;
			case 'IC': return 'count per inche'; break;
			case 'IE': return 'person'; break;
			case 'IF': return 'inches of water'; break;
			case 'II': return 'column inch'; break;
			case 'IL': return 'inche per minute'; break;
			case 'IM': return 'impression inch'; break;
			case 'INH': return 'inch'; break;
			case 'INK': return 'square inch'; break;
			case 'INQ': return 'cubic inch'; break;
			case 'INQ_1': return 'inch cubed'; break;
			case 'IP': return 'insurance policy'; break;
			case 'ISD': return 'international sugar degree'; break;
			case 'IT': return 'count per centimetre'; break;
			case 'IU': return 'inche per second'; break;
			case 'IU_1': return 'inch per second (vibration)'; break;
			case 'IV': return 'inch per second squared'; break;
			case 'IV': return 'inch per second squared (acceleration)'; break;
			case 'J2': return 'joule per kilogram'; break;
			case 'JB': return 'jumbo'; break;
			case 'JE': return 'joule per kelvin'; break;
			case 'JG': return 'jug'; break;
			case 'JK': return 'megajoule per kilogram'; break;
			case 'JM': return 'megajoule per cubic metre'; break;
			case 'JO': return 'joint'; break;
			case 'JOU': return 'joule'; break;
			case 'JPS': return 'hundred metre'; break;
			case 'JR': return 'jar'; break;
			case 'JWL': return 'number of jewels'; break;
			case 'K1': return 'kilowatt demand'; break;
			case 'K2': return 'kilovolt ampere reactive demand'; break;
			case 'K3': return 'kilovolt ampere reactive hour'; break;
			case 'K5': return 'kilovolt ampere (reactive)'; break;
			case 'K6': return 'kilolitre'; break;
			case 'KA': return 'cake'; break;
			case 'KB': return 'kilocharacter'; break;
			case 'KBA': return 'kilobar'; break;
			case 'KCC': return 'kilogram of choline chloride'; break;
			case 'KD': return 'kilogram decimal'; break;
			case 'KEL': return 'kelvin'; break;
			case 'KF': return 'kilopacket'; break;
			case 'KG': return 'keg'; break;
			case 'KGM': return 'kilogram'; break;
			case 'KGS': return 'kilogram per second'; break;
			case 'KHY': return 'kilogram of hydrogen peroxide'; break;
			case 'KHZ': return 'kilohertz'; break;
			case 'KI': return 'kilogram per millimetre widht'; break;
			case 'KIC': return 'kilogram, including container'; break;
			case 'KIP': return 'kilogram, including inner packaging'; break;
			case 'KJ': return 'kilosegment'; break;
			case 'KJO': return 'kilojoule'; break;
			case 'KL': return 'kilogram per metre'; break;
			case 'KLK': return 'lactic dry material percentage'; break;
			case 'KMA': return 'kilogram of methylamine'; break;
			case 'KMH': return 'kilometre per hour'; break;
			case 'KMK': return 'square kilometre'; break;
			case 'KMQ': return 'kilogram per cubic metre'; break;
			case 'KMT': return 'kilometre per hour'; break;
			case 'KNI': return 'kilogram of nitrogen'; break;
			case 'KNS': return 'kilogram named substance'; break;
			case 'KNT': return 'knot'; break;
			case 'KO': return 'milliequivalence caustic potash per gram of product'; break;
			case 'KPA': return 'kilopascal'; break;
			case 'KPH': return 'kilogram of potassium hydroxide (caustic potash)'; break;
			case 'KPO': return 'kilogram of potassium oxide'; break;
			case 'KPP': return 'kilogram of phosphorus pentoxide (phosphoric anhydride)'; break;
			case 'KR': return 'kiloroentgen'; break;
			case 'KS': return 'thousand pound per square inch'; break;
			case 'KSD': return 'kilogram of substance 90% dry'; break;
			case 'KSH': return 'kilogram of sodium hydroxide (caustic soda)'; break;
			case 'KT': return 'kit'; break;
			case 'KTM': return 'kilometre per hour'; break;
			case 'KTN': return 'kilotonne'; break;
			case 'KUR': return 'kilogram of uranium'; break;
			case 'KVA': return 'kilovolt-ampere'; break;
			case 'KVR': return 'kilovar'; break;
			case 'KVT': return 'kilovolt'; break;
			case 'KW': return 'kilograms per millimetre'; break;
			case 'KWH': return 'kilowatt hour'; break;
			case 'KWO': return 'kilogram of tungsten trioxide'; break;
			case 'KWT': return 'kilowatt'; break;
			case 'KX': return 'millilitre per kilogram'; break;
			case 'L2': return 'litre per minute'; break;
			case 'LA': return 'pound per cubic inch'; break;
			case 'LAC': return 'lactose excess percentage'; break;
			case 'LBR': return 'pound'; break;
			case 'LBR_1': return 'pound decimal'; break;
			case 'LBT': return 'troy pound (US)'; break;
			case 'LC': return 'linear centimetre'; break;
			case 'LD': return 'litre per day'; break;
			case 'LE': return 'lite'; break;
			case 'LEF': return 'leaf'; break;
			case 'LF': return 'linear foot'; break;
			case 'LH': return 'labour hour'; break;
			case 'LI': return 'linear inch'; break;
			case 'LJ': return 'large spray'; break;
			case 'LK': return 'link'; break;
			case 'LM': return 'linear metre'; break;
			case 'LN': return 'length'; break;
			case 'LO': return 'lot [unit of procurement]'; break;
			case 'LP': return 'liquid pound'; break;
			case 'LPA': return 'litre of pure alcohol'; break;
			case 'LR': return 'layer'; break;
			case 'LS': return 'lump sum'; break;
			case 'LTN': return 'ton (UK) or long ton (US)'; break;
			case 'LTR': return 'litre'; break;
			case 'LUB': return 'metric ton, lubricating oil'; break;
			case 'LUM': return 'lumen'; break;
			case 'LUX': return 'lux'; break;
			case 'LX': return 'linear yard per pound'; break;
			case 'LY': return 'linar yard'; break;
			case 'M1': return 'milligram per litre'; break;
			case 'M4': return 'monetary value'; break;
			case 'M5': return 'microcurie'; break;
			case 'M7': return 'micro-inch'; break;
			case 'M9': return 'million Btu per 1000 cubic feet'; break;
			case 'MA': return 'machine per unit'; break;
			case 'MAH': return 'megavolt ampere reactive hours'; break;
			case 'MAL': return 'mega litre'; break;
			case 'MAM': return 'megametre'; break;
			case 'MAR': return 'mevavolt ampere reactive'; break;
			case 'MAW': return 'megawatt'; break;
			case 'MBE': return 'thousand standard brick equivalent'; break;
			case 'MBF': return 'thousand board feet'; break;
			case 'MBR': return 'millibar'; break;
			case 'MC': return 'microgram'; break;
			case 'MCU': return 'millicurie'; break;
			case 'MD': return 'air dry metric tone'; break;
			case 'MF': return 'milligram per square foot per side'; break;
			case 'MGM': return 'milligram'; break;
			case 'MHZ': return 'megahertz'; break;
			case 'MIK': return 'square mile'; break;
			case 'MIL': return 'thousand'; break;
			case 'MIN': return 'minute [unit of time]'; break;
			case 'MIO': return 'million'; break;
			case 'MIU': return 'million international unit'; break;
			case 'MK': return 'milligram per square inch'; break;
			case 'MLD': return 'billion (US)'; break;
			case 'MLD_1': return 'milliard'; break;
			case 'MLT': return 'millilitre per kilogram'; break;
			case 'MMK': return 'square millimetre'; break;
			case 'MMQ': return 'cubic millimetre'; break;
			case 'MMT': return 'millimetre'; break;
			case 'MND': return 'kilogra, dry weight'; break;
			case 'MO': return 'magnetic tape'; break;
			case 'MON': return 'month'; break;
			case 'MPA': return 'megapascal'; break;
			case 'MQ': return 'thousand metre'; break;
			case 'MQH': return 'cunic metre per hour'; break;
			case 'MQS': return 'cubic metre per second'; break;
			case 'MSK': return 'metre per second squared'; break;
			case 'MT': return 'mat'; break;
			case 'MTK': return 'square metre'; break;
			case 'MTQ': return 'cubic metre'; break;
			case 'MTQ_1': return 'metre cubed'; break;
			case 'MTR': return 'metre'; break;
			case 'MTS': return 'metre per second'; break;
			case 'MV': return 'number of mults'; break;
			case 'MVA': return 'megavolt-ampere'; break;
			case 'MWH': return 'megawatt hour (1000kW.h)'; break;
			case 'N1': return 'pen calorie'; break;
			case 'N2': return 'number of lines'; break;
			case 'N3': return 'print point'; break;
			case 'NA': return 'milligram per kilogram'; break;
			case 'NAR': return 'number of articles'; break;
			case 'NB': return 'barge'; break;
			case 'NBB': return 'number of bobbins'; break;
			case 'NC': return 'car'; break;
			case 'NCL': return 'number of cells'; break;
			case 'ND': return 'net barrel'; break;
			case 'NE': return 'net litre'; break;
			case 'NEW': return 'newton per square centimetre'; break;
			case 'NF': return 'message'; break;
			case 'NG': return 'net gallon (US)'; break;
			case 'NH': return 'message hour'; break;
			case 'NI': return 'net imperial gallon'; break;
			case 'NIU': return 'number of international units'; break;
			case 'NJ': return 'number of screens'; break;
			case 'NL': return 'load'; break;
			case 'NMI': return 'nautical mile'; break;
			case 'NMP': return 'number of packs'; break;
			case 'NN': return 'train'; break;
			case 'NPL': return 'number of parcels'; break;
			case 'NPR': return 'number of pairs'; break;
			case 'NPT': return 'number of parts'; break;
			case 'NQ': return 'mho'; break;
			case 'NR': return 'micromho'; break;
			case 'NRL': return 'number of rolls'; break;
			case 'NT': return 'metric net ton'; break;
			case 'NT_1': return 'net ton'; break;
			case 'NTT': return 'net register ton'; break;
			case 'NV': return 'vehicle'; break;
			case 'NX': return 'part per thousand'; break;
			case 'NY': return 'pound per air dry metric ton'; break;
			case 'OA': return 'panel'; break;
			case 'ODE': return 'ozone depletion equivalent'; break;
			case 'OHM': return 'ohm'; break;
			case 'ON': return 'ounce per square yard'; break;
			case 'ONZ': return 'ounce per square yard'; break;
			case 'OP': return 'two pack'; break;
			case 'OT': return 'overtime hour'; break;
			case 'OZ': return 'ounce av'; break;
			case 'OZA': return 'fluid ounce (US)'; break;
			case 'OZI': return 'fluid ounce (UK)'; break;
			case 'P0': return 'page -electronic'; break;
			case 'P1': return 'percent'; break;
			case 'P2': return 'pound per foot'; break;
			case 'P3': return 'three pack'; break;
			case 'P4': return 'four pack'; break;
			case 'P5': return 'five pack'; break;
			case 'P6': return 'six pack'; break;
			case 'P7': return 'seven pack'; break;
			case 'P8': return 'eight pack'; break;
			case 'P9': return 'nine pack'; break;
			case 'PA': return 'packet'; break;
			case 'PAL': return 'pascal'; break;
			case 'PB': return 'pair inch'; break;
			case 'PD': return 'pad'; break;
			case 'PE': return 'pound equivalent'; break;
			case 'PF': return 'pallet (lift)'; break;
			case 'PFL': return 'proof litre'; break;
			case 'PG': return 'plate'; break;
			case 'PGL': return 'proof gallon'; break;
			case 'PI': return 'pitch'; break;
			case 'PK': return 'pack'; break;
			case 'PK_1': return 'package'; break;
			case 'PLA': return 'pail'; break;
			case 'PLA_1': return 'degree Plato'; break;
			case 'PM': return 'pound percentage'; break;
			case 'PN': return 'pound net'; break;
			case 'PO': return 'pound per inch of lengtt'; break;
			case 'PQ': return 'page per inch'; break;
			case 'PR': return 'pair inch'; break;
			case 'PS': return 'pound-force per square inch'; break;
			case 'PT': return 'pint (US)'; break;
			case 'PTD': return 'dry pint(US)'; break;
			case 'PTI': return 'pint (UK)'; break;
			case 'PTL': return 'liquid pint (US)'; break;
			case 'PU': return 'tray/tray pack'; break;
			case 'PV': return 'half pint (US)'; break;
			case 'PW': return 'pound per inch of width'; break;
			case 'PY': return 'peck dry (US)'; break;
			case 'PZ': return 'peck dry (UK)'; break;
			case 'Q3': return 'meal'; break;
			case 'QA': return 'page-facsimile'; break;
			case 'QAN': return 'quarter (of a year)'; break;
			case 'QB': return 'page-hardcopy'; break;
			case 'QD': return 'quarter dozen'; break;
			case 'QH': return 'quarter hour'; break;
			case 'QK': return 'quartes kilogram'; break;
			case 'QR': return 'quire'; break;
			case 'QT': return 'quart (US)'; break;
			case 'QTD': return 'dry quart (US)'; break;
			case 'QTI': return 'quart (UK)'; break;
			case 'QTL': return 'liquid quart (US)'; break;
			case 'QTR': return 'quarter (UK)'; break;
			case 'R1': return 'pica'; break;
			case 'R4': return 'calorie'; break;
			case 'R9': return 'thousand cubic metre'; break;
			case 'RA': return 'rack'; break;
			case 'RD': return 'rod'; break;
			case 'RG': return 'ring'; break;
			case 'RH': return 'running or operating hour'; break;
			case 'RK': return 'roll metric measure'; break;
			case 'RL': return 'reel'; break;
			case 'RM': return 'ream'; break;
			case 'RN': return 'ream metric measure'; break;
			case 'RO': return 'roll metric measure'; break;
			case 'RP': return 'pound per team'; break;
			case 'RPM': return 'revolutions per minute'; break;
			case 'RPS': return 'revolutions per second'; break;
			case 'RS': return 'reset'; break;
			case 'RT': return 'revenue ton mile'; break;
			case 'RU': return 'run'; break;
			case 'S3': return 'foot wquare per second'; break;
			case 'S3_1': return 'square foot per second'; break;
			case 'S4': return 'metre squared per second (square metres/seconds US)'; break;
			case 'S4_1': return 'square metre per second sixty fourths of an inch'; break;
			case 'S5': return 'sixty fourths of an inch'; break;
			case 'S6': return 'session'; break;
			case 'S7': return 'storage unit'; break;
			case 'S8': return 'standard advertising unit'; break;
			case 'SA': return 'sack'; break;
			case 'SAN': return 'half year (6 months)'; break;
			case 'SCO': return 'score'; break;
			case 'SCR': return 'scruple'; break;
			case 'SD': return 'solid pound'; break;
			case 'SE': return 'section'; break;
			case 'SEC': return 'second [unit of time]'; break;
			case 'SET': return 'set'; break;
			case 'SG': return 'segment'; break;
			case 'SHT': return 'shipping ton'; break;
			case 'SIE': return 'siemens'; break;
			case 'SK': return 'split tank truck'; break;
			case 'SL': return 'slipsheet'; break;
			case 'SMI': return 'mile (statute mile)'; break;
			case 'SN': return 'square rod'; break;
			case 'SO': return 'spool'; break;
			case 'SP': return 'shelf package'; break;
			case 'SQ': return 'square'; break;
			case 'SQR': return 'square, roofing'; break;
			case 'SR': return 'strip'; break;
			case 'SS': return 'sheet metric measure'; break;
			case 'SST': return 'short standard (7200 matches)'; break;
			case 'ST': return 'sheet'; break;
			case 'STI': return 'stone (UK)'; break;
			case 'STK': return 'stick, cigarette'; break;
			case 'STL': return 'standard litre'; break;
			case 'STN': return 'net ton (2000 lb)'; break;
			case 'STN_1': return 'ton (US) or short ton (UK/US)'; break;
			case 'SV': return 'skid'; break;
			case 'SW': return 'skein'; break;
			case 'SX': return 'shipment'; break;
			case 'T0': return 'telecommunication line service'; break;
			case 'T1': return 'thousand pound gross'; break;
			case 'T3': return 'thousand piece'; break;
			case 'T4': return 'thousand bag'; break;
			case 'T5': return 'thousand casing'; break;
			case 'T6': return 'thousand gallon (US)'; break;
			case 'T7': return 'thousand impression'; break;
			case 'T8': return 'thousand linear inch'; break;
			case 'TA': return 'tenth cubic foot'; break;
			case 'TAH': return 'kiloampere hour (thousand ampere hour'; break;
			case 'TC': return 'truckload'; break;
			case 'TD': return 'therm'; break;
			case 'TE': return 'tote'; break;
			case 'TF': return 'ten square yard'; break;
			case 'TI': return 'thousand square inch'; break;
			case 'TIC': return 'metric ton, including container'; break;
			case 'TIP': return 'metric ton, including inner packaging'; break;
			case 'TJ': return 'thousand square centimetre'; break;
			case 'TK': return 'tank, rectangular'; break;
			case 'TL': return 'thousand feet (linear)'; break;
			case 'TMS': return 'kilogram of imported meta, less offal'; break;
			case 'TN': return 'tin'; break;
			case 'TNE': return 'metric ton'; break;
			case 'TNE_1': return 'tonne (metric ton)'; break;
			case 'TP': return 'ten pack'; break;
			case 'TPR': return 'ten pair'; break;
			case 'TQ': return 'thousand feet'; break;
			case 'TQD': return 'thousand cubic metre per day'; break;
			case 'TR': return 'ten square feet'; break;
			case 'TRL': return 'trillion (EUR)'; break;
			case 'TS': return 'tonne of substance 90% dry'; break;
			case 'TSD': return 'thousand square feet'; break;
			case 'TSH': return 'ton of steam per hour'; break;
			case 'TT': return 'thousand linear metre'; break;
			case 'TU': return 'tube'; break;
			case 'TV': return 'thousand kilogram'; break;
			case 'TW': return 'thousand sheet'; break;
			case 'TY': return 'tank, cylindrical'; break;
			case 'U1': return 'tratment'; break;
			case 'U2': return 'tablet'; break;
			case 'UA': return 'torr'; break;
			case 'UB': return 'telecommunication line in service average'; break;
			case 'UC': return 'telecommunication port'; break;
			case 'UD': return 'tenth minute'; break;
			case 'UE': return 'tenth hour'; break;
			case 'UF': return 'usage per telecommunication line average'; break;
			case 'UH': return 'ten thousand yard'; break;
			case 'UM': return 'million un it'; break;
			case 'UN': return 'newton metre'; break;
			case 'VA': return 'volt ampere per kilogram'; break;
			case 'VI': return 'vial'; break;
			case 'VLT': return 'volt'; break;
			case 'VQ': return 'bulk'; break;
			case 'VS': return 'visit'; break;
			case 'W2': return 'wet kilo'; break;
			case 'W4': return 'two week'; break;
			case 'WA': return 'watt per kilogram'; break;
			case 'WB': return 'wet pound'; break;
			case 'WCD': return 'cord'; break;
			case 'WE': return 'wet ton'; break;
			case 'WE_1': return 'wrap'; break;
			case 'WEB': return 'weber'; break;
			case 'WEE': return 'week'; break;
			case 'WG': return 'wine gallon'; break;
			case 'WH': return 'wheel'; break;
			case 'WHR': return 'watt hour'; break;
			case 'WI': return 'weight per square inch'; break;
			case 'WM': return 'working month'; break;
			case 'WSD': return 'standard'; break;
			case 'WTT': return 'watt'; break;
			case 'WW': return 'millilitre of water'; break;
			case 'X1': return 'hchain'; break;
			case 'YDK': return 'squared yard'; break;
			case 'YDQ': return 'cubic yard'; break;
			case 'YL': return 'hundred linear yard'; break;
			case 'YRD': return 'yard'; break;
			case 'YT': return 'ten yard'; break;
			case 'Z1': return 'lift van'; break;
			case 'Z2': return 'chest'; break;
			case 'Z3': return 'cask'; break;
			case 'Z4': return 'hogshead'; break;
			case 'Z5': return 'lug'; break;
			case 'Z6': return 'conference point'; break;
			case 'Z8': return 'newspage agate line'; break;
			case 'ZP': return 'page'; break;
			case 'ZZ': return 'muatually defined'; break;
		}
	}
	
	
?>