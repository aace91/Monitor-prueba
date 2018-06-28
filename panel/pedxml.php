<?php
	//consultarPedimentoCompleto("MECC470130952", "9p8I+atJEPNuPYmIIzgkawoPToGg0ntx0Zouywn/ar6xuRThUk/AzgbYECn2vGi5", "800", "3232", "4003063") ;
	include_once('./../checklogin.php');
	if($loggedIn == false){
		header("Location: ./../login.php"); 
	}
	if (!isset($_POST['pedimento']) && !isset($_POST['referencia'])) {
		exit("Error 0");
	}
	if (!isset($_POST['referencia'])){
		$pedimentopat=$_POST['pedimento'];
		$TcAduana=substr($pedimentopat,0,3);
		$TcPedimento=substr($pedimentopat,-7);
		$TcPatente=substr($pedimentopat,4,4);
		$query = "SELECT fir_pago FROM SAAIO_PEDIME where num_pedi='".$TcPedimento."' and pat_agen='".$TcPatente."' and adu_desp='".$TcAduana."'";
	}else{
		$referencia=$_POST['referencia'];
		$query = "SELECT fir_pago,adu_desp,num_pedi,pat_agen FROM SAAIO_PEDIME where num_refe='".$referencia."'";
	}
	$result = odbc_exec ($odbccasa, $query);
	if ($result!=false){
		if(odbc_num_rows($result)<=0){
			exit ("Error 01");
		}else{
			while(odbc_fetch_row($result)){
				$firpago=odbc_result($result,"fir_pago");
				if (!isset($_POST['pedimento'])){
					$TcAduana=odbc_result($result,"adu_desp");
					$TcPedimento=odbc_result($result,"num_pedi");
					$TcPatente=odbc_result($result,"pat_agen");
				}
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
	//$xmlcove->save('peticionpedcompleto.xml');
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
	  //echo 'Error al procesar la peticion, cierre esta ventana e intentelo de nuevo, detalle del error: '.$ex;
	  echo 'Error 1';
	  exit;
	}
	//echo $result;
	$respuesta = new DOMDocument();
	$respuesta->preserveWhiteSpace = false;
	$respuesta->formatOutput = true;
	$respuesta->loadXML($result);
	$nodes=$respuesta->getElementsByTagName('Fault') ;
	$nodes2=$respuesta->getElementsByTagName('error') ;
	//$respuesta->save('respuestapedcompleto.xml');
	if ($nodes->length<>0) {
		//echo "Error al realizar la conexi�n a Ventanilla Unica intentelo de nuevo si el problema persiste contacte al administrador</br>";
		echo 'Error 2';
		foreach( $nodes as $error )
		{
				$errores = $error->getElementsByTagName( "faultstring" );
				$error = $errores->item(0)->nodeValue;
				//echo "Error de Ventanilla Unica: ".$error.$TcUser;
				echo 'Error 3';
		}			
		exit;
	}
	else{
		if ($nodes2->length<>0) {
			foreach( $nodes2 as $error )
			{
				$errores = $error->getElementsByTagName( "mensaje" );
				$error = $errores->item(0)->nodeValue;
				//echo "Error de ventanilla unica: ".$error;
				echo 'Error 4: '.$error;
			}
			exit;
		}
		else{
			$respuesta->save($rutaxml);
			if (filesize($rutaxml)<2048){
				//echo "Error al descargar informacion de ventanilla unica intente de nuevo".$result;
				echo 'Error 5';
				unlink($rutaxml);
				exit;
			}
			echo '<center><a href="descargapedxml.php?nxml='.$nxml.'"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></a></center>';
		}
	}
?>