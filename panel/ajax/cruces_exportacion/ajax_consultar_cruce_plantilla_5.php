<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../url_archivos.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_cruce']) && !empty($_POST['id_cruce'])) {  
		$respuesta['Codigo']=1;
		$id_cruce = json_decode($_POST['id_cruce']);
		$sIds = '';
		if(is_array($id_cruce)){
			for($i=0; $i<count($id_cruce);$i++){
				$sIds .= ($i > 0 ? ',' :'').$id_cruce[$i];
			}
		}else{
			$sIds = $id_cruce;
		}
		
		$consulta = "SELECT id_cruce,archivo_cfdi,regimen
						FROM cruces_expo_detalle
						WHERE id_cruce IN (".$sIds.")";
		
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar la informacion de las facturas.'; 
			$respuesta['Error'] = ' ['.$error.']';
		}else{
			$aFacReceptores = array();
			$aFacPart = array();
			while ($row = mysqli_fetch_array($query)){
				//Datos de c/Factura del Cruce
				$aDirFac = explode('/',$row['archivo_cfdi']);
				$sDirCFDI = $dir_archivos_facturas . array_pop($aDirFac);
				$sRegimen = $row['regimen'];
				$xml = new DOMDocument();
				$ok = $xml->load($sDirCFDI);
				if (!$ok) {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error al leer el archivo XML [".$sDirCFDI."]. Por favor, contacte el administrador del sistema.";
					exit(json_encode($respuesta));
				}
				$texto = $xml->saveXML();
				if (strpos($texto,"cfdi:Comprobante")!==FALSE) {
					$tipo="cfdi";
				} elseif (strpos($texto,"<Comprobante")!==FALSE) {
					$tipo="cfd";
				} elseif (strpos($texto,"retenciones:Retenciones")!==FALSE) {
					$tipo="retenciones";
				} else {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Tipo de XML no identificado ....";
					exit(json_encode($respuesta));
				}
				if ($tipo=="retenciones") {
					$root = $xml->getElementsByTagName('Retenciones')->item(0);
					$Version = $root->getAttribute("Version");
				} else {
					$root = $xml->getElementsByTagName('Comprobante')->item(0);
					$Comprobante = $xml->getElementsByTagName('Comprobante')->item(0);
					$Version = $root->getAttribute("version");
					if ($Version==null) $Version = $root->getAttribute("Version");
				}
				//error_log('Version: '.$Version);
				$serie = utf8_decode($root->getAttribute("serie"));
				if (!isset($serie) || empty($serie)) {
					$serie = utf8_decode($root->getAttribute("Serie"));
				}
				$fechaxml = $root->getAttribute("fecha");
				if (!isset($fechaxml) || empty($fechaxml)) {
					$fechaxml = $root->getAttribute("Fecha");
				}
				$fechaxml = date('d/m/Y',strtotime($fechaxml));
				$folio = $root->getAttribute('folio');
				if (!isset($folio) || empty($folio)) {
					$folio = $root->getAttribute("Folio");
				}
				$numero_factura = $serie.$folio;
				$receptor = $root->getElementsByTagName('Receptor')->item(0);
				$receptor_nom = $receptor->getAttribute('nombre');
				if (!isset($receptor_nom) || empty($receptor_nom)) {
					$receptor_nom = $receptor->getAttribute("Nombre");
				}
				$aFac = array (
					'numero_factura' => $numero_factura,
					'receptor' => $receptor_nom
				);
				array_push($aFacReceptores,$aFac);
				$TFD = $root->getElementsByTagName('TimbreFiscalDigital')->item(0);
				$uuid = '';
				if ($TFD!=null) {
					$uuid = $TFD->getAttribute("UUID");
				} else {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="El timbre fiscal del cfdi es incorrecto..";
					exit(json_encode($respuesta));
				}
				$total_factura = $root->getAttribute('total');
				if (!isset($total_factura) || empty($total_factura)) {
					$total_factura = $root->getAttribute("Total");
				}
				$moneda = $root->getAttribute('moneda');
				if (!isset($moneda) || empty($moneda)) {
					$moneda = $root->getAttribute("Moneda");
				}
				$incoterm = ''; $subdivision = 'N'; $certificado_origen = 'N';

				if($Version == '3.3'){
					$ComExt = $root->getElementsByTagName('ComercioExterior')->item(0);
					//Incoterms
					if($sRegimen != 'RT'){
						try{
							$incoterm = $ComExt->getAttribute("Incoterm");
							if (!isset($incoterm) || empty($incoterm)) {
								$incoterm = $root->getAttribute("incoterm");
							}
						}catch (Exception $e){
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='La factura ['.$numero_factura.'] esta marcada como '.$sRegimen.' y no cuenta con el complemento correcto [Incoterm].[Cruce:'.$row['id_cruce'].']';
							$respuesta['Error']=$e->getMessage();
						}
					}
					//Subdivision
					/*$subdivision = $ComExt->getAttribute("Subdivision");
					if (!isset($subdivision) || empty($subdivision)) {
						$subdivision = $root->getAttribute("subdivision");
					}
					$subdivision = ($subdivision == '1' ? 'S' : 'N');
					//Certificado Origen
					$certificado_origen = $ComExt->getAttribute("CertificadoOrigen");
					if (!isset($certificado_origen) || empty($certificado_origen)) {
						$certificado_origen = $root->getAttribute("certificadoOrigen");
					}
					$certificado_origen = ($certificado_origen == '1' ? 'S' : 'N');*/
				}
				//Mercancias
				$aMercancias = array();
				if($Version == '3.3'){
					$Mercancias = $xml->getElementsByTagName('Mercancia');
					foreach ($Mercancias as $Mercancia) {
						$aMer['valor_dolares'] = $Mercancia->getAttribute("ValorDolares");
						$aMer['valor_unitario'] = $Mercancia->getAttribute("ValorUnitarioAduana");
						$aMer['cantidad_tarifa'] = $Mercancia->getAttribute("CantidadAduana");
						$aMer['fraccion'] = $Mercancia->getAttribute("FraccionArancelaria");
						$aMer['unidad_tarifa'] = $Mercancia->getAttribute("UnidadAduana");
						$aMer['numero_parte'] = $Mercancia->getAttribute("NoIdentificacion");
						array_push($aMercancias,$aMer);
					}
				}
				//Partidas Factura
				$aConceptos = array();
				$Conceptos = $xml->getElementsByTagName('Concepto');
				foreach ($Conceptos as $Concepto) {
					$numero_parte = $Concepto->getAttribute("NoIdentificacion");
					if (!isset($numero_parte) || empty($numero_parte)) {
						$numero_parte = $Concepto->getAttribute("noIdentificacion");
					}
					$pais_origen = 'USA';
					$pais_vendedor = 'USA';
					//fraccion
					$descripcion = $Concepto->getAttribute("Descripcion");
					if (!isset($descripcion) || empty($descripcion)) {
						$descripcion = $Concepto->getAttribute("descripcion");
					}
					//Precio_Partida
					$precio_partida = $Concepto->getAttribute("importe");
					if (!isset($precio_partida) || empty($precio_partida)) {
						$precio_partida = $Concepto->getAttribute("Importe");
					}
					//UMC
					$UMC = $Concepto->getAttribute("unidad");
					if (!isset($UMC) || empty($UMC)) {
						$UMC = $Concepto->getAttribute("Unidad");
					}
					//CantidadC
					$CantidadC = $Concepto->getAttribute("cantidad");
					if (!isset($CantidadC) || empty($CantidadC)) {
						$CantidadC = $Concepto->getAttribute("Cantidad");
					}
					//Buscar datos aduana mercancia si es version 3.3
					$valor_dolares = ''; $valor_unitario = ''; $cantidad_tarifa = '';
					$fraccion = ''; $unidad_tarifa = '';
					
					if(count($aMercancias) > 0){
						for($i = 0; $i<count($aMercancias); $i++){
							if($aMercancias[$i]['numero_parte'] == $numero_parte){
								$valor_dolares = $aMercancias[$i]['valor_dolares']; 
								$valor_unitario = $aMercancias[$i]['valor_unitario']; 
								$cantidad_tarifa = $aMercancias[$i]['cantidad_tarifa'];
								$fraccion = $aMercancias[$i]['fraccion']; 
								$unidad_tarifa = $aMercancias[$i]['unidad_tarifa']; 
								//$numero_parte = $Mercancias[$i]['numero_parte'];
								break;
							}
						}
					}
					$ParFac = array(
						"id_proveedor"=> '',
						"UUID" => strtoupper ($uuid),
						"numero_factura"=> strtoupper($numero_factura),
						"fecha_factura"=> $fechaxml,//dd/mm/YYYY
						"monto_factura"=> $total_factura,
						"moneda"=> $moneda,
						"incoterm"=> $incoterm,
						"subdivision"=> $subdivision,
						"certificado_origen"=> $certificado_origen,
						"numero_parte"=> $numero_parte,
						"pais_origen"=> $pais_origen,
						"pais_vendedor"=> $pais_vendedor,
						"fraccion"=> $fraccion,
						"descripcion"=> $descripcion,
						"precio_partida"=> $precio_partida,
						"UMC"=> $UMC,
						"cantidad_UMC"=> $CantidadC,
						"cantidad_UMT"=> $cantidad_tarifa,
						"preferencia_arancelaria"=> 'N',
						"marca"=> 'S/M',
						"modelo"=> 'S/M',
						"submodelo"=> 'N/A',
						"serie"=> 'S/N',
						"descripcion_cove"=> $descripcion,
						"referencia"=>$id_cruce
					);
					array_push($aFacPart,$ParFac);
				}
				/*$respuesta['serie'] = $serie;
				$respuesta['folio'] = $folio;
				$TFD = $root->getElementsByTagName('TimbreFiscalDigital')->item(0);
				if ($TFD!=null) {
					$respuesta['uuid'] = $TFD->getAttribute("UUID");
				} else {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="El timbre fiscal del cfdi es incorrecto..";
					exit(json_encode($respuesta));
				}*/
			}
			$respuesta['aFacPart'] = $aFacPart;
			$respuesta['aFacReceptores'] = $aFacReceptores;
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
