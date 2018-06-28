<?php
	include_once('./../checklogin.php');
	if($loggedIn == false){
		header("Location: ./../login.php"); 
	}
	if (!isset($_POST['pedimento'])) {
		exit("Error 0");
	}
	$pedimentopat=$_POST['pedimento'];
	$TcAduana=substr($pedimentopat,0,3);
	$TcPedimento=substr($pedimentopat,-7);
	$TcPatente=substr($pedimentopat,4,4);
	$query = "SELECT fir_pago FROM SAAIO_PEDIME where num_pedi='".$TcPedimento."' and pat_agen='".$TcPatente."' and adu_desp='".$TcAduana."'";
	$result = odbc_exec ($odbccasa, $query);
	if ($result!=false){
		if(odbc_num_rows($result)<=0){
			exit ("Error 01");
		}else{
			while(odbc_fetch_row($result)){
				$firpago=odbc_result($result,"fir_pago");
			}
		}
	}else{
		exit ("Error 02");
	}
	if($TcPatente=='3232'){ 
		$TcUser="MECC470130952";
		$TcPass="9p8I+atJEPNuPYmIIzgkawoPToGg0ntx0Zouywn/ar6xuRThUk/AzgbYECn2vGi5";
	}
	if($TcPatente=='3483'){ 
		$TcUser="EAFM620803BVA";
		$TcPass="PD6tyHXPvmbqoCniTWN8wiVSCXfdEgMo7VR4xPlNAPNoxBqc3puaghlDKaPOzbY2";
	}
	$nxml=substr($TcAduana,0,2).'_'.$TcPatente.'_'.$TcPedimento.'.xml';
	$rutaxml="d:\pedimentosxml\\".$nxml;
	if (file_exists($rutaxml)==false || $firpago==''){
		$xmlcove = new DOMDocument('1.0', 'UTF-8');
		$nSobre  = $xmlcove->createElement("soapenv:Envelope");
		$nSobre-> setAttribute("xmlns:soapenv", "http://schemas.xmlsoap.org/soap/envelope/");
		$nSobre-> setAttribute("xmlns:con", "http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpedimentocompleto");
		$nSobre->setAttribute("xmlns:com","http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes");
		
		$nSobreEncabezado = $xmlcove->createElement("soapenv:Header");
		
		$nSeguridad =$xmlcove->createElement("wsse:Security");
		$nSeguridad  -> setAttribute("soapenv:mustUnderstand", "1");
		$nSeguridad  -> setAttribute("xmlns:wsse", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");

		$nUsernameToken =$xmlcove->createElement("wsse:UsernameToken");

		$nUsername =$xmlcove->createElement("wsse:Username",trim($TcUser));
		$nPassword =$xmlcove->createElement("wsse:Password",trim($TcPass));
		$nPassword-> setAttribute("Type", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText");

		$nBody = $xmlcove->createElement("soapenv:Body");

		$nConsultarPedimentoCompleto = $xmlcove->createElement("con:consultarPedimentoCompletoPeticion");
		$nPeticion = $xmlcove->createElement("con:peticion");
		$nAduana = $xmlcove->createElement("com:aduana",trim($TcAduana));
		$nPatente = $xmlcove->createElement("com:patente", trim($TcPatente));
		$nPedimento = $xmlcove->createElement("com:pedimento", trim($TcPedimento));
		
		$nUsernameToken->appendChild($nUsername);
		$nUsernameToken->appendChild($nPassword);
		$nSeguridad->appendChild($nUsernameToken);  
		$nSobreEncabezado->appendChild($nSeguridad);
		$nSobre->appendChild($nSobreEncabezado);

		$nPeticion->appendChild($nAduana);
		$nPeticion->appendChild($nPatente);
		$nPeticion->appendChild($nPedimento);
		$nConsultarPedimentoCompleto->appendChild($nPeticion);
		$nBody->appendChild($nConsultarPedimentoCompleto);
		$nSobre->appendChild($nBody);
		$xmlcove->appendChild($nSobre);

		try{
		  $ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL, 
			  "https://www.ventanillaunica.gob.mx:443/ventanilla-ws-pedimentos/ConsultarPedimentoCompletoService?wsdl");
		  curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlcove->saveXML());
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		  curl_setopt($ch, CURLOPT_USERPWD, trim($TcUser).":".trim($TcPass));
		  curl_setopt($ch, CURLOPT_POST, true);
		  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		  curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		  
		  $result = curl_exec($ch);
		  curl_close($ch);
		} catch (Exception $ex){
		  echo '<center><p>Error al obtener el pedimento xml de ventanilla unica, intente de nuevo: '.$ex.' </center></p>';
		  exit;
		}
		//echo $result;
		$respuesta = new DOMDocument();
		$respuesta->preserveWhiteSpace = false;
		$respuesta->formatOutput = true;
		$respuesta->loadXML($result);
		
		$nodes=$respuesta->getElementsByTagName('Fault') ;
		$nodes2=$respuesta->getElementsByTagName('error') ;
		if ($nodes->length<>0) {
			echo "<center><p>Error al realizar la conexi�n a ventanilla unica intentelo de nuevo si el problema persiste contacte al administrador</center></p>";
			foreach( $nodes as $error )
			{
					$errores = $error->getElementsByTagName( "faultstring" );
					$error = $errores->item(0)->nodeValue;
					echo "<center><p>Error de Ventanilla Unica al consuultar pedimento completo: ".$error.' </center></p>';
			}			
			exit;
		}
		else{
			if ($nodes2->length<>0) {
				foreach( $nodes2 as $error )
				{
					$errores = $error->getElementsByTagName( "mensaje" );
					$error = $errores->item(0)->nodeValue;
					echo "Error de ventanilla unica al obterner el pedimento xml: ".$error.' </center></p>';
				}
				exit;
			}
			else{
				$respuesta->save($rutaxml);
				if (filesize($rutaxml)<2048){
					echo "<center><p>Error al obtener el pedimento xml de ventanilla unica intente de nuevo".$result. '</center></p>';
					unlink($rutaxml);
					exit;
				}
				$noperaciones = $respuesta->getElementsByTagName('numeroOperacion');
				foreach ($noperaciones as $noperacion) {
					consultaestatus($noperacion->nodeValue);
				}
			}
		}
	}
	else{
		$respuesta = new DOMDocument();
		$respuesta->preserveWhiteSpace = false;
		$respuesta->formatOutput = true;
		$respuesta->load($rutaxml);
		$noperaciones = $respuesta->getElementsByTagName('numeroOperacion');
		foreach ($noperaciones as $noperacion) {
			consultaestatus($noperacion->nodeValue);
		}
	}
	
	function consultaestatus($operacion){
		global $TcUser,$TcPass,$TcAduana,$TcPatente,$TcPedimento;
		$nxmle=substr($TcAduana,0,2).'_'.$TcPatente.'_'.$TcPedimento.'_estatus.xml';
		$rutaxmle="d:\pedimentosxml\\".$nxmle;
		if (!file_exists($rutaxmle)){
			$npedimento=$TcPatente."_".$TcAduana."_".$TcPedimento;
			$xmlcove = new DOMDocument('1.0', 'UTF-8');
			$nSobre  = $xmlcove->createElement("soapenv:Envelope");
			$nSobre-> setAttribute("xmlns:soapenv", "http://schemas.xmlsoap.org/soap/envelope/");
			$nSobre-> setAttribute("xmlns:con", "http://server.estadoPedimento.ws.pedimentos.www.ventanillaunica.gob.mx/");
			$nSobre->setAttribute("xmlns:com","http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes");
			
			$nSobreEncabezado = $xmlcove->createElement("soapenv:Header");
			
			$nSeguridad =$xmlcove->createElement("wsse:Security");
			$nSeguridad  -> setAttribute("soapenv:mustUnderstand", "1");
			$nSeguridad  -> setAttribute("xmlns:wsse", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");

			$nUsernameToken =$xmlcove->createElement("wsse:UsernameToken");

			$nUsername =$xmlcove->createElement("wsse:Username",trim($TcUser));
			$nPassword =$xmlcove->createElement("wsse:Password",trim($TcPass));
			$nPassword-> setAttribute("Type", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText");

			$nBody = $xmlcove->createElement("soapenv:Body");

			$nConsultarEstadoPedimento = $xmlcove->createElement("con:ConsultarEstadoPedimento");
			$nPeticion1 = $xmlcove->createElement("peticion");
			$nOperacion= $xmlcove->createElement("numeroOperacion",$operacion);
			$nPeticion2 = $xmlcove->createElement("peticion");
			$nAduana = $xmlcove->createElement("com:aduana",trim($TcAduana));
			$nPatente = $xmlcove->createElement("com:patente", trim($TcPatente));
			$nPedimento = $xmlcove->createElement("com:pedimento", trim($TcPedimento));
			
			$nUsernameToken->appendChild($nUsername);
			$nUsernameToken->appendChild($nPassword);
			$nSeguridad->appendChild($nUsernameToken);  
			$nSobreEncabezado->appendChild($nSeguridad);
			$nSobre->appendChild($nSobreEncabezado);

			$nPeticion2->appendChild($nAduana);
			$nPeticion2->appendChild($nPatente);
			$nPeticion2->appendChild($nPedimento);
			$nPeticion1->appendChild($nOperacion);
			$nPeticion1->appendChild($nPeticion2);
			$nConsultarEstadoPedimento->appendChild($nPeticion1);
			$nBody->appendChild($nConsultarEstadoPedimento);
			$nSobre->appendChild($nBody);
			$xmlcove->appendChild($nSobre);
			//echo $xmlcove->saveXML();
			try{
			  $ch = curl_init();
			  curl_setopt($ch, CURLOPT_URL, 
				  "https://www.ventanillaunica.gob.mx:443/webservice-pedimentos-HA/consultarEstadoPedimento?wsdl");
			  curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlcove->saveXML());
			  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			  curl_setopt($ch, CURLOPT_USERPWD, trim($TcUser).":".trim($TcPass));
			  curl_setopt($ch, CURLOPT_POST, true);
			  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			  curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
			  
			  $result = curl_exec($ch);
			  curl_close($ch);
			} catch (Exception $ex){
			  echo '<center><p>Error al obtener el estatus del pedimento de ventanilla unica intentelo de nuevo, detalle del error: '.$ex.'</center></p>';
			  exit;
			}
			//echo $result;
			$respuesta = new DOMDocument();
			$respuesta->preserveWhiteSpace = false;
			$respuesta->formatOutput = true;
			$respuesta->loadXML($result);
			$nodes=$respuesta->getElementsByTagName('Fault') ;
			$nodes2=$respuesta->getElementsByTagName('error') ;
			if ($nodes->length<>0) {
				echo '<center><p>Error al obtener el estatus del pedimento de ventanilla unica intentelo de nuevo, detalle del error: '.$ex.', si el problema persiste contacte al administrador</center></p>';
				foreach( $nodes as $error )
				{
						$errores = $error->getElementsByTagName( "faultstring" );
						$error = $errores->item(0)->nodeValue;
						echo '<center><p>Error al obtener el estatus del pedimento de ventanilla unica intentelo de nuevo, detalle del error: '.$error.'</center></p>';
				}			
				exit;
			}
			else{
				if ($nodes2->length<>0) {
					foreach( $nodes2 as $error )
					{
						$errores = $error->getElementsByTagName( "mensaje" );
						$error = $errores->item(0)->nodeValue;
						echo '<center><p>Error al obtener el estatus del pedimento de ventanilla unica intentelo de nuevo, detalle del error: '.$error.'</center></p>';
					}
					exit;
				}
				else{
					//header('Content-Type: text/html; charset=utf-8;');
					//$respuesta->save($rutaxmle);
					$nodes=$respuesta->getElementsByTagName('estadosPedimento') ;
					$rresult= '<div class="table-responsive"><table class="table"><tr><th>Fecha</th><th>Proceso</th><th>Secuencia</th><th>Resultado</th><th>Remesa</th><th>Cantidad</th><th>Valor</th></tr>';
					foreach( $nodes as $estados )
					{
						$descripcionestado = $estados->getElementsByTagName( "descripcionEstado" );
						$estado = $estados->getElementsByTagName( "estado" );
						$descripcionsubestado = $estados->getElementsByTagName( "descripcionSubEstado" );
						$subestado = $estados->getElementsByTagName( "subEstado" );
						$fechaestado = $estados->getElementsByTagName( "fecha" );
						$secuencia = $estados->getElementsByTagName( "secuencia" );
						$factura = $estados->getElementsByTagName( "factura" );
						$cantidad = $estados->getElementsByTagName( "cantidad" );
						$valor = $estados->getElementsByTagName( "valor" );
						$enc = mb_detect_encoding($descripcionsubestado->item(0)->nodeValue, "UTF-8,ISO-8859-1");
						$descsub=iconv($enc, "UTF-8", $descripcionsubestado->item(0)->nodeValue);
						$rresult.='<tr>';
						$rresult.='<td>'.date('d/m/Y h:i:sa',strtotime($fechaestado->item(0)->nodeValue)).'</td>';
						$rresult.='<td>'.$descripcionestado->item(0)->nodeValue.'</td>';
						$rresult.='<td>'.$secuencia->item(0)->nodeValue.'</td>';
						if ($estado->item(0)->nodeValue==3){
							if($subestado->item(0)->nodeValue==320){
								$rresult.='<td><span class="label label-success">'.$descsub.'</span></td>';
							}elseif($subestado->item(0)->nodeValue==310){
								$rresult.='<td><span class="label label-danger">'.$descsub.'</span></td>';
							}else{
								$rresult.='<td>'.$descsub.'</td>';
							}
						}else{
								$rresult.='<td>'.$descsub.'</td>';
						}
						$rresult.='<td>'.$factura->item(0)->nodeValue.'</td>';
						$rresult.='<td>'.$cantidad->item(0)->nodeValue.'</td>';
						$rresult.='<td>'.$valor->item(0)->nodeValue.'</td>';
						$rresult.='</tr>';
						if ($estado->item(0)->nodeValue==7&& $subestado->item(0)->nodeValue==730){
							$respuesta->save($rutaxmle);
						}
					}		
					$rresult.='</table></div>';
					echo $rresult;
					//echo '<textarea>'.$respuesta->saveXML().'</textarea>';
				}
			}
		}else{
			$respuesta = new DOMDocument();
			$respuesta->preserveWhiteSpace = false;
			$respuesta->formatOutput = true;
			$respuesta->load($rutaxmle);
			$nodes=$respuesta->getElementsByTagName('estadosPedimento') ;
			$rresult= '<div class="table-responsive"><table class="table"><tr><th>Fecha</th><th>Proceso</th><th>Secuencia</th><th>Resultado</th><th>Remesa</th><th>Cantidad</th><th>Valor</th></tr>';
			foreach( $nodes as $estados )
			{
				$descripcionestado = $estados->getElementsByTagName( "descripcionEstado" );
				$estado = $estados->getElementsByTagName( "estado" );
				$descripcionsubestado = $estados->getElementsByTagName( "descripcionSubEstado" );
				$subestado = $estados->getElementsByTagName( "subEstado" );
				$fechaestado = $estados->getElementsByTagName( "fecha" );
				$secuencia = $estados->getElementsByTagName( "secuencia" );
				$factura = $estados->getElementsByTagName( "factura" );
				$cantidad = $estados->getElementsByTagName( "cantidad" );
				$valor = $estados->getElementsByTagName( "valor" );
				$enc = mb_detect_encoding($descripcionsubestado->item(0)->nodeValue, "UTF-8,ISO-8859-1");
				$descsub=iconv($enc, "UTF-8", $descripcionsubestado->item(0)->nodeValue);
				$rresult.='<tr>';
				$rresult.='<td>'.date('d/m/Y h:i:sa',strtotime($fechaestado->item(0)->nodeValue)).'</td>';
				$rresult.='<td>'.$descripcionestado->item(0)->nodeValue.'</td>';
				$rresult.='<td>'.$secuencia->item(0)->nodeValue.'</td>';
				if ($estado->item(0)->nodeValue==3){
					if($subestado->item(0)->nodeValue==320){
						$rresult.='<td><span class="label label-success">'.$descsub.'</span></td>';
					}elseif($subestado->item(0)->nodeValue==310){
						$rresult.='<td><span class="label label-danger">'.$descsub.'</span></td>';
					}else{
						$rresult.='<td>'.$descsub.'</td>';
					}
				}else{
						$rresult.='<td>'.$descsub.'</td>';
				}
				$rresult.='<td>'.$factura->item(0)->nodeValue.'</td>';
				$rresult.='<td>'.$cantidad->item(0)->nodeValue.'</td>';
				$rresult.='<td>'.$valor->item(0)->nodeValue.'</td>';
				$rresult.='</tr>';
				if ($estado->item(0)->nodeValue==7&& $subestado->item(0)->nodeValue==730){
					$respuesta->save($rutaxmle);
				}
			}		
			$rresult.='</table></div>';
			echo $rresult;
		}
	}
?>