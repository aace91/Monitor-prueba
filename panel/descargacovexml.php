<?php
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');
	include_once('./../checklogin.php');
	if($loggedIn == false){ header("Location: ./../login.php"); }
	/*if (!isset($_GET['ref']){
	 exit();
	}*/
	if (!isset($_POST['pedimento'])) {
		exit("Error 0");
	}
	$pedimentopat=$_POST['pedimento'];
	$aduana=substr($pedimentopat,0,3);
	$pedimento=substr($pedimentopat,-7);
	$patente=substr($pedimentopat,4,4);
	if ($patente=='3232'){
		$TcUser="MECC470130952";
		$TcPass="9p8I+atJEPNuPYmIIzgkawoPToGg0ntx0Zouywn/ar6xuRThUk/AzgbYECn2vGi5";
	}elseif($patente=='3483'){
		$TcUser="EAFM620803BVA";
		$TcPass="PD6tyHXPvmbqoCniTWN8wiVSCXfdEgMo7VR4xPlNAPNoxBqc3puaghlDKaPOzbY2";
	}
	//consulta y ciclo de todos los coves
	$sql="SELECT a.FOL_COVE,a.E_DOCUMENT,a.NUM_CER,b.NUM_REFE FROM SAAIO_COVE a left join SAAIO_PEDIME b on a.NUM_REFE=b.NUM_REFE WHERE b.NUM_PEDI='".$pedimento."' AND b.PAT_AGEN='".$patente."' and b.ADU_DESP='".$aduana."'";
	$resultsql=odbc_exec($odbccasa,$sql);
	$error=odbc_errormsg(); 
	if ($error!=''){
		echo $error;
		exit;
	}
	//$xmls=array();
	$hayerrores=false;
	$cont=0;
	while(odbc_fetch_row($resultsql)){
		$cont++;
		$referencia=odbc_result($resultsql,"NUM_REFE");
		$operacion=rtrim(odbc_result($resultsql,"FOL_COVE"));
		$edocument=rtrim(odbc_result($resultsql,"E_DOCUMENT"));
		$cadena_original='|'.$TcUser.'|'.$edocument.'|';
		$nxml=$operacion."_".$edocument.'.xml';
		$rutaxml="d:\coves\\".$nxml;
		if (file_exists($rutaxml)){
			//array_push($xmls,$rutaxml);
			continue;
		}
		if ($patente=='3232'){
			$file="MECC470130952_20140725_193012.key.pem";      // Ruta al archivo key.pem
			$filecer="00001000000304767281.cer";
		}elseif($patente=='3483'){
			$file="eafm620803bva_1106202329.key.pem";
			$filecer="00001000000103839716.cer";
		}
		$pkeyid = openssl_get_privatekey(file_get_contents($file));
		openssl_sign($cadena_original, $crypttext, $pkeyid, OPENSSL_ALGO_SHA1);
		openssl_free_key($pkeyid);
		$sello = base64_encode($crypttext);
		$handle = fopen($filecer, "r");
		if ($handle) {
			$certhex = base64_encode(fread($handle,filesize($filecer)));
			fclose($handle);
		}
		$xmlcove = new DOMDocument('1.0', 'UTF-8');
		$nSobre  = $xmlcove->createElement("SOAP-ENV:Envelope");
		$nSobre-> setAttribute("xmlns:SOAP-ENV", "http://schemas.xmlsoap.org/soap/envelope/");
		$nSobre-> setAttribute("xmlns:ns1", "http://www.ventanillaunica.gob.mx/cove/ws/oxml/");
		$nSobre->setAttribute("xmlns:ns2","http://www.ventanillaunica.gob.mx/ConsultarEdocument/");
		$nSobreEncabezado = $xmlcove->createElement("SOAP-ENV:Header");
		$nSeguridad =$xmlcove->createElement("wsse:Security");
		$nSeguridad  -> setAttribute("SOAP-ENV:mustUnderstand", "1");
		$nSeguridad  -> setAttribute("xmlns:wsse", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");
		$nUsernameToken =$xmlcove->createElement("wsse:UsernameToken");
		$nUsername =$xmlcove->createElement("wsse:Username",trim($TcUser));
		$nPassword =$xmlcove->createElement("wsse:Password",trim($TcPass));
		$nPassword-> setAttribute("Type", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText");
		$nBody = $xmlcove->createElement("SOAP-ENV:Body");
		$nConsultarCove= $xmlcove->createElement("ns2:ConsultarEdocumentRequest");
		$nPeticion = $xmlcove->createElement("ns2:request");
		$nFirmaElectronica = $xmlcove->createElement("ns2:firmaElectronica");
		$nCertificado = $xmlcove->createElement("ns1:certificado",trim($certhex));
		$nCadena = $xmlcove->createElement("ns1:cadenaOriginal", trim($cadena_original));
		$nFirma = $xmlcove->createElement("ns1:firma", trim($sello));
		$nCriterioBusqueda = $xmlcove->createElement("ns2:criterioBusqueda");
		$nEdocument = $xmlcove->createElement("ns2:eDocument",trim($edocument));
		//$nAdenda = $xmlcove->createElement("ns2:numeroAdenda",'0');
		$nUsernameToken->appendChild($nUsername);
		$nUsernameToken->appendChild($nPassword);
		$nSeguridad->appendChild($nUsernameToken);  
		$nSobreEncabezado->appendChild($nSeguridad);
		$nSobre->appendChild($nSobreEncabezado);
		$nFirmaElectronica->appendChild($nCertificado);
		$nFirmaElectronica->appendChild($nCadena);
		$nFirmaElectronica->appendChild($nFirma);
		$nPeticion->appendChild($nFirmaElectronica);
		$nCriterioBusqueda->appendChild($nEdocument);
		//$nCriterioBusqueda->appendChild($nAdenda);
		$nPeticion->appendChild($nCriterioBusqueda);
		$nConsultarCove->appendChild($nPeticion);
		$nBody->appendChild($nConsultarCove);
		$nSobre->appendChild($nBody);
		$xmlcove->appendChild($nSobre);
		//echo $xmlcove->saveXML();
		//echo '<textarea>'.$xmlcove->saveXML().'</textarea>';
		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 
				"https://www.ventanillaunica.gob.mx:443/ventanilla/ConsultarEdocumentService?wsdl");
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
			//echo 'Error al procesar la peticion, cierre esta ventana e intente de nuevo, detalle del error: '.$ex;
			$msgerror='Error al procesar la peticion, cierre esta ventana e intente de nuevo, detalle del error: '.$ex;
			$hayerrores=true;
			break;
		}
		if ($result!=NULL){
			$respuesta = new DOMDocument();
			$respuesta->preserveWhiteSpace = false;
			$respuesta->formatOutput = true;
			$respuesta->loadXML($result);
			$nodes=$respuesta->getElementsByTagName('Fault') ;
			$nodes2=$respuesta->getElementsByTagName('error') ;
			if ($nodes->length<>0) {
				//echo "Error al realizar la conexi�n a ventanilla unica, intente de nuevo, si el problema persiste contacte al administrador";
				$msgerror="Error al realizar la conexi�n a ventanilla unica, intente de nuevo, si el problema persiste contacte al administrador";
				//echo '<textarea>'.$respuesta->saveXML().'</textarea>';
				$hayerrores=true;
				break;
			}
			else{
				if ($nodes2->length<>0) {
					foreach( $nodes2 as $error )
					{
						$error1 = $error->item(0)->nodeValue;
						$msgerror.= $error1;
					}
					$hayerrores=true;
					break;
				}
				else{
					$respuesta->save($rutaxml);
					//array_push($xmls,$rutaxml);
				}
			}
		}
		else{
			//echo "Error al obtener la informacion de ventanilla unica, intente de nuevo, si el problema persiste contacte al administrador";
			$msgerror="Error al obtener la informacion de ventanilla unica, intente de nuevo, si el problema persiste contacte al administrador";
			$hayerrores=true;
			break;
		}
	}
	if ($cont>0){
		if ($hayerrores==false){
			echo '<center><a href="descargazipcovexml.php?ref='.$referencia.'" target=_blank><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></a></center>';
		}
		else{
			echo "Error";
		}
	}
	else{
		echo "Sin COVEs";
	}
?>