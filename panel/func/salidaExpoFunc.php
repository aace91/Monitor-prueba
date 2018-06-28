<?php
include_once("../../checklogin.php");
include("../../connect_dbsql.php");

$sPathFilesExpo = "\\\\192.168.1.126\\documentos_expo\\salidaExpo";
$sPathFilesPed2009 = "\\\\192.168.1.126\\pedimentos2009";
$sPathFilesCruces = "\\\\192.168.1.126\\documentos_expo\\cruces";

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);

if($loggedIn == false){
	$error_msg="Se ha perdido la sesion favor de iniciarla de nuevo";
	exit(json_encode(array("error" => $error_msg)));
} else {
	if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
		$action = $_REQUEST['action'];
		switch ($action) {
			case 'buscalineat' : $respuesta = fcn_buscalineat((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
				
			case 'buscaaaa' : $respuesta = fcn_buscaaaa((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
				
			case 'buscatransfers_expo' : $respuesta = fcn_buscatransfers_expo((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
				
			case 'buscaentregas_expo' : $respuesta = fcn_buscaentregas_expo((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
				
			case 'buscacltes_expo' : $respuesta = fcn_buscacltes_expo((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
				
			case 'buscareferencia_casa' : $respuesta = fcn_buscareferencia_casa();
				echo json_encode($respuesta);
				break;
				
			case 'buscapedimento_simplificado' : $respuesta = fcn_buscapedimento_simplificado();
				echo json_encode($respuesta);
				break;
				
			case 'buscapedimento_normal' : $respuesta = fcn_buscapedimento_normal();
				echo json_encode($respuesta);
				break;
			
			case 'guardarpedimento_simplificado' : $respuesta = fcn_guardarpedimento_simplificado();
				echo json_encode($respuesta);
				break;
				
			case 'guardarpedimento_normal' : $respuesta = fcn_guardarpedimento_normal();
				echo json_encode($respuesta);
				break;
				
            case 'insertar_salida' : $respuesta = fcn_insertar_salida();
				echo json_encode($respuesta);
				break;	

			case 'editar_salida' : $respuesta = fcn_editar_salida();
				echo json_encode($respuesta);
				break;

			case 'eliminar_factura' : $respuesta = fcn_eliminar_factura();
				echo json_encode($respuesta);
				break;
			
			case 'subir_documentos' : $respuesta = fcn_subir_documentos2();
				echo json_encode($respuesta);
				break;
			
			case 'buscar_documentos_entry_number' : $respuesta = fcn_buscar_documentos_entry_number();
				echo json_encode($respuesta);
				break;
				
			case 'buscar_documentos' : $respuesta = fcn_buscar_documentos();
				echo json_encode($respuesta);
				break;
		}
	}
}

/*************************************************************************************************/
/* FUNCIONES                                                                                     */
/*************************************************************************************************/

/* SELECT2 */
function fcn_buscalineat($buscar){
	global $cmysqli;
	
	$response['items']=array();
	if ($buscar!=''){
		$consulta="SELECT numlinea, nombre
		           FROM bodega.lineast
				   WHERE nombre LIKE '%".$buscar."%' AND 
				         habilitado=1
				   ORDER BY nombre
				   LIMIT 10";
				   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$response['codigo']=-1;
			$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
			return $response;
		}
		while($row = mysqli_fetch_object($query)){
			$id=$row->numlinea; 
			$nombre=$row->nombre; 
			array_push($response['items'],array('id'=>$id,'text'=>$nombre));
		} 
	}
	mysqli_close($cmysqli);
	return $response;
}

function fcn_buscaaaa($buscar){
	global $cmysqli;
	
	$response['items']=array();
	if ($buscar!=''){
		$consulta="SELECT numeroaa, nombreaa
				   FROM bodega.aaa
				   WHERE nombreaa LIKE '%".$buscar."%' AND 
				         habilitado=1
				   ORDER BY nombreaa
				   LIMIT 10";
				   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$response['codigo']=-1;
			$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
			return $response;
		}
		while($row = mysqli_fetch_object($query)){
			$id=$row->numeroaa; 
			$nombre=$row->nombreaa; 
			array_push($response['items'],array('id'=>$id,'text'=>$nombre));
		} 
	}
	mysqli_close($cmysqli);
	return $response;
}

function fcn_buscatransfers_expo($buscar){
	global $cmysqli;
	
	$response['items']=array();
	if ($buscar!=''){
		$consulta="SELECT notransfer, nombretransfer
				   FROM bodega.transfers_expo
				   WHERE nombretransfer LIKE '%".$buscar."%' AND
				         habilitado=1
				   ORDER BY nombretransfer
				   LIMIT 10";
				   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$response['codigo']=-1;
			$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
			return $response;
		}
		while($row = mysqli_fetch_object($query)){
			$id=$row->notransfer; 
			$nombre=$row->nombretransfer; 
			array_push($response['items'],array('id'=>$id,'text'=>$nombre));
		} 
	}
	mysqli_close($cmysqli);
	return $response;
}

function fcn_buscaentregas_expo($buscar){
	global $cmysqli;
	
	$response['items']=array();
	if ($buscar!=''){
		$consulta="SELECT numeroentrega, nombreentrega, direccion
				   FROM bodega.entregas_expo
				   WHERE nombreentrega LIKE '%".$buscar."%' AND
				         habilitado=1
				   ORDER BY nombreentrega
				   LIMIT 10";
				   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$response['codigo']=-1;
			$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
			return $response;
		}
		while($row = mysqli_fetch_object($query)){
			$id=$row->numeroentrega; 
			$nombre=$row->nombreentrega; 
			$direccion=$row->direccion; 
			array_push($response['items'],array('id'=>$id,'text'=>$nombre, 'dir'=>$direccion));
		} 
	}
	mysqli_close($cmysqli);
	return $response;
}

function fcn_buscacltes_expo($buscar){
	global $cmysqli;
	
	$response['items']=array();
	if ($buscar!=''){
		$consulta="SELECT gcliente, cnombre
				   FROM bodega.cltes_expo
				   WHERE cnombre LIKE '%".$buscar."%'
				   ORDER BY cnombre
				   LIMIT 10";
				   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$response['codigo']=-1;
			$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
			return $response;
		}
		while($row = mysqli_fetch_object($query)){
			$id=$row->gcliente; 
			$nombre=$row->cnombre; 
			array_push($response['items'],array('id'=>$id,'text'=>$nombre));
		} 
	}
	mysqli_close($cmysqli);
	return $response;
}

/**********************************************/

function fcn_buscareferencia_casa(){
	include ('../../connect_casa.php');
	
	global $_POST, $cmysqli;
	
	if (isset($_POST['sReferencia']) && !empty($_POST['sReferencia'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sReferencia = strtoupper ($_POST['sReferencia']);
		
		//***********************************************************//
		
		$bExisteCasa = false;
		$sPedimento = '';
		$sPatente = '';
		$sTipoPedimento = 'normal';
		$oClienteData = array();
		$aFacturas = array();
		
		//***********************************************************//
		
		/* ..:: Consultamos referencia en casa ::.. */
		$consulta = "SELECT a.NUM_PEDI, a.PAT_AGEN, a.FIR_REME
					 FROM SAAIO_PEDIME a
					 WHERE a.NUM_REFE='".$sReferencia."' AND
					       a.IMP_EXPO=2";
					 
		$query = odbc_exec($odbccasa, $consulta);
		if ($query==false){ 
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar la Referencia [".$sReferencia."] en el sistema CASA.";
			$respuesta['Error'] = ' ['.$query.']';
		} else {
			if(odbc_num_rows($query)<=0){ 
				/*$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "La Referencia [".$sReferencia."] no se encuentra en el sistema CASA.";
				$respuesta['Error'] = '';*/
				$bExisteCasa = false;
			} else { 
				while(odbc_fetch_row($query)){ 
					$sPedimento = odbc_result($query,"NUM_PEDI");
					$sPatente = odbc_result($query,"PAT_AGEN");
					$sTipoPedimento = (is_null(odbc_result($query,"FIR_REME"))? 'normal': 'consolidado');
					$bExisteCasa = true;
					break;
				}
				
				if ($bExisteCasa) { 
					/* ..:: Consultamos Facturas ::.. */	
					$consulta = "SELECT a.NUM_REFE, a.CONS_FACT, a.NUM_FACT, a.NUM_FACT2, a.NUM_REM
								 FROM SAAIO_FACTUR a
								 WHERE a.NUM_REFE='".$sReferencia."'
								 ORDER BY a.CONS_FACT";
								 
					$query = odbc_exec($odbccasa, $consulta);
					if ($query==false){ 
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = "Error al consultar las facturas de la Referencia [".$sReferencia."] en el sistema CASA.";
						$respuesta['Error'] = ' ['.$query.']';
					} else {
						while(odbc_fetch_row($query)){ 
							try {
								 $aRow = array(
									'id' => odbc_result($query,"CONS_FACT"),
									'text' => odbc_result($query,"NUM_FACT"),
									'uuid' => str_replace('­','-',odbc_result($query,"NUM_FACT2")),
									'rem' => ((is_null(odbc_result($query,"NUM_REM")))? '' : odbc_result($query,"NUM_REM"))
								);
								array_push($aFacturas, $aRow);
							} catch(Exception $e) {
								$respuesta['Codigo'] = -1;
								$respuesta['Mensaje'] = "Error al generar lista de facturas de la Referencia [".$sReferencia."] en el sistema CASA.";
								$respuesta['Error'] = ' ['.$e->getMessage().']';
							}
						}
					}
					
					if($respuesta['Codigo'] == 1) { 
						$consulta="SELECT CONS_FACT_PED
								   FROM bodega.facturas_expo
								   WHERE REFERENCIA='".$sReferencia."'";
						
						$query = mysqli_query($cmysqli, $consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al consultar las facturas de la Referencia ['.$sReferencia.'].'; 
							$respuesta['Error'] = ' ['.$error.']';	
						} else {
							while($row = mysqli_fetch_object($query)){
								$sExist = array_search($row->CONS_FACT_PED, array_column($aFacturas, 'id'));
								if (false !== $sExist) { 
									array_splice($aFacturas, $sExist, 1);
								}
							} 
						}
					}
				}
			}
		}
		
		/* ..:: Consultamos Cliente ::.. */
		if($respuesta['Codigo'] == 1) { 
			$consulta="SELECT b.gcliente, b.cnombre
					   FROM bodega.entradas_expo AS a INNER JOIN
							bodega.cltes_expo AS b ON b.gcliente = a.numcliente
					   WHERE a.referencia='".$sReferencia."'";
			
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar el Pedimento ['.$strPedimento.'].'; 
				$respuesta['Error'] = ' ['.$error.']';	
			} else {
				while($row = mysqli_fetch_object($query)){
					$id=$row->gcliente; 
					$nombre=$row->cnombre;
					array_push($oClienteData,array('id'=>$id,'text'=>$nombre));
				} 
			}
		}		
		
		$respuesta['sReferencia']=$sReferencia;
		$respuesta['bExisteCasa']=$bExisteCasa;
		$respuesta['sPedimento']=$sPedimento;
		$respuesta['sPatente']=$sPatente;
		$respuesta['sTipoPedimento']=$sTipoPedimento;
		$respuesta['oClienteData']=$oClienteData;
		$respuesta['aFacturas']=$aFacturas;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}	
	mysqli_close($cmysqli);
	return $respuesta;
}

function fcn_buscapedimento_simplificado(){	
	global $_POST, $sPathFilesPed2009, $cmysqli;
	
	if (isset($_POST['sReferencia']) && !empty($_POST['sReferencia'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sReferencia = strtoupper ($_POST['sReferencia']);
		$sNumRemPed = $_POST['sNumRemPed'];
		$sUuid = $_POST['sUuid'];
		
		//***********************************************************//
		
		$bExistePedSimp = false;
		
		//***********************************************************//
		
		/*$consulta = "SELECT *
					 FROM cruces_expo_detalle
					 WHERE uuid='".$sUuid."' AND
						   cons_fact IS NULL";
						   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar detalle en cruces.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$num_rows = mysqli_num_rows($query);
			if ($num_rows > 0) {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Esta Factura no se puede procesar por que esta ligada a un cruce pendiente.'; 
				$respuesta['Error'] = '';
			}
		}*/
		
		//***********************************************************//
		
		if ($respuesta['Codigo'] == 1) {
			$sFilePath = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sReferencia.'-'.$sNumRemPed.'.pdf';
			if (file_exists($sFilePath)) {
				$bExistePedSimp = true;
			}
		}
		
		$respuesta['bExistePedSimp']=$bExistePedSimp;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}	
	return $respuesta;
}

function fcn_buscapedimento_normal(){
	global $_POST, $sPathFilesPed2009, $cmysqli;
	
	if (isset($_POST['sReferencia']) && !empty($_POST['sReferencia'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sReferencia = strtoupper ($_POST['sReferencia']);
		$sUuid = $_POST['sUuid'];
		
		//***********************************************************//
		
		$bExistePedNormal = false;
		
		//***********************************************************//
		
		/*$consulta = "SELECT *
					 FROM cruces_expo_detalle
					 WHERE uuid='".$sUuid."' AND
						   cons_fact IS NULL";
					 
		//$respuesta['Consulta'] = $consulta;
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar detalle en cruces.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$num_rows = mysqli_num_rows($query);
			if ($num_rows > 0) {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Esta Factura no se puede procesar por que esta ligada a un cruce pendiente.'; 
				$respuesta['Error'] = '';
			}
		}*/
		
		//***********************************************************//
		
		if ($respuesta['Codigo'] == 1) {
			$sFilePath = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sReferencia.'-transportista.pdf';
			if (file_exists($sFilePath)) {
				$bExistePedNormal = true;
			}
		}
		
		$respuesta['bExistePedNormal']=$bExistePedNormal;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}	
	return $respuesta;
}

function fcn_guardarpedimento_simplificado(){
	global $_POST, $sPathFilesPed2009;
	
	if (isset($_POST['sReferencia']) && !empty($_POST['sReferencia'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sReferencia = strtoupper ($_POST['sReferencia']);
		$sNumRemPed = $_POST['sNumRemPed'];
		$oFiles = $_FILES;
		
		//***********************************************************//
		
		if (isset($oFiles['oPdfPedSimp'])) {
			$ext = explode('.', basename($oFiles['oPdfPedSimp']['name']));
			$sFileName = $sReferencia.'-'.$sNumRemPed .'.'. array_pop($ext);
			$target = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sFileName;
			
			if(move_uploaded_file($oFiles['oPdfPedSimp']['tmp_name'], $target)) {
				$respuesta['Mensaje']='Pedimento simplificado guardado correctamente';
			} else {
				$respuesta['Codigo']=-1;		
				$respuesta['Mensaje']='Error al guardar Pedimento simplificado, intente de nuevo';
				$respuesta['Error']='';
			}
		} else {
			$respuesta['Codigo']=-1;		
			$respuesta['Mensaje']='No se recibio documento de Pedimento simplificado';
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	return $respuesta;
}

function fcn_guardarpedimento_normal(){
	global $_POST, $sPathFilesPed2009;
	
	if (isset($_POST['sReferencia']) && !empty($_POST['sReferencia'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sReferencia = strtoupper ($_POST['sReferencia']);
		$oFiles = $_FILES;
		
		//***********************************************************//
		
		if (isset($oFiles['oPdfPedNormal'])) {
			$ext = explode('.', basename($oFiles['oPdfPedNormal']['name']));
			$sFileName = $sReferencia.'-transportista.'. array_pop($ext);
			$target = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sFileName;
			
			if(move_uploaded_file($oFiles['oPdfPedNormal']['tmp_name'], $target)) {
				$respuesta['Mensaje']='Pedimento guardado correctamente';
			} else {
				$respuesta['Codigo']=-1;		
				$respuesta['Mensaje']='Error al guardar Pedimento, intente de nuevo';
				$respuesta['Error']='';
			}
		} else {
			$respuesta['Codigo']=-1;		
			$respuesta['Mensaje']='No se recibio documento de Pedimento';
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	return $respuesta;
}

function fcn_insertar_salida(){
	global $_POST, $username, $cmysqli;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['sFecha']) && !empty($_POST['sFecha'])) {
		$sFecha = $_POST['sFecha'];
		$sFerrocarril = $_POST['sFerrocarril'];
		//$sTipoSalida = $_POST['sTipoSalida'];
		//$sTipoSalidaCaja = $_POST['sTipoSalidaCaja'];
		$sEntregarEnDir = $_POST['sEntregarEnDir'];
		$sCrucesEnSalida = $_POST['sCrucesEnSalida'];
		$sUrgente = $_POST['sUrgente'];
		$sLeyenda = $_POST['sLeyenda'];
		$sHoraEntrada = $_POST['sHoraEntrada'];
		$sRecibio = $_POST['sRecibio'];
		$sNumeroViaje = $_POST['sNumeroViaje'];
		$sIndicaciones = $_POST['sIndicaciones'];
		$sObservaciones = $_POST['sObservaciones'];
		$sLineaTranspId = $_POST['sLineaTranspId'];
		$sLineaTranspName = $_POST['sLineaTranspName'];
		$sAduanaId = $_POST['sAduanaId'];
		$sTransferId = $_POST['sTransferId'];
		$sTransferName = $_POST['sTransferName'];
		$sEntregarEnId = $_POST['sEntregarEnId'];
		$sEntregarEnName = $_POST['sEntregarEnName'];
		$aFacturas = json_decode($_POST['aFacturas']);
		$aDataDelRow = json_decode($_POST['aDataDelRow']);
		$bBorrarNOA = $_POST['bBorrarNOA'];
		$bBorrarSolRet = $_POST['bBorrarSolRet'];
		$oFiles = $_FILES;
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		$nSalidaNumero = 0;
		//***********************************************************//
		
		$respuesta = fcn_buscapedimento_simplificado_facturas($aFacturas);
		
		if ($respuesta['Codigo'] == 1) {
			$nSalidaNumero = fcn_insertar_salida_lasid($respuesta);
		}
		
		if ($respuesta['Codigo'] == 1) {
			mysqli_query($cmysqli, 'BEGIN');
			
			$consulta = "INSERT INTO bodega.salidas_expo (  
			                 salidanumero
						    ,fecha
						    ,lineatransp
							,aduana
							,notransfer
							,nombretransfer
							,noentrega
							,Nombreentrega
							,direntrega
							,cruces
							,usuario
							,urgente
							,horaentrega
							,recibio
							,indicaciones
							,observaciones
							,ferrocarril
							,viaje
							,bultos
							,peso
							,leyenda
							,nolineatransp
						 )
						 VALUES (
							 ".$nSalidaNumero."
							,'".$sFecha."'
							,'".$sLineaTranspName."'
							,'".$sAduanaId."'
							,".$sTransferId."
							,'".$sTransferName."'
							,".$sEntregarEnId."
							,'".$sEntregarEnName."'
							,'".$sEntregarEnDir."'
							,".$sCrucesEnSalida."
							,'".$username."'
							,'".$sUrgente."'
							,'".$sHoraEntrada."'
							,'".$sRecibio."'
							,'".$sIndicaciones."'
							,'".$sObservaciones."'
							,'".$sFerrocarril."'
							,'".$sNumeroViaje."'
							,0
							,0
							,".(($sLeyenda == '')? "NULL": "'".$sLeyenda."'")."
							,".$sLineaTranspId."
						 )";
						 
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al crear la Salida.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else {
				foreach ($aFacturas as &$factura) {
					$prefile_id = $factura->prefile_id;
					if ($prefile_id == '') {
						$respPrefile = fcn_buscar_prefile_factura($nSalidaNumero, $factura->caja, $factura->factura);
						if ($respPrefile['Codigo'] == 1) {
							$prefile_id = $respPrefile['id_documento'];
						}
					}
					
					$consulta = "INSERT INTO bodega.facturas_expo (  
									 FACTURA_NUMERO
									,TIPOSALIDA
									,CAJA
									,NUMCLIENTE
									,VALOR_FACTURA
									,REFERENCIA
									,PEDIMENTO
									,SALIDA_NUMERO
									,ID_REGISTRO
									,CONS_FACT_PED
									,NUM_REM_PED
									,UUID
									,PATENTE
									,NOAAA
									,PACKING_LIST_ID
									,CERTIFICADO_ORIGEN_ID
									,TICKET_BASCULA_ID
									,PREFILE_ID
								 )
								 VALUES (
									 '".$factura->factura."'
									,'".$factura->tiposalida."'
									,'".$factura->caja."'
									,'".$factura->clienteid."'
									,0
									,'".$factura->referencia."'
									,".$factura->pedimento."
									,".$nSalidaNumero."
									,(
										SELECT (b.ID_REGISTRO + 1) AS ID_REGISTRO
										FROM bodega.facturas_expo AS b
										ORDER BY b.ID_REGISTRO DESC
										LIMIT 1
									)
									,".(($factura->cons_fac_ped == '')? 'NULL': $factura->cons_fac_ped)."
									,".(($factura->num_rem_ped == '')? 'NULL': $factura->num_rem_ped)."
									,'".$factura->uuid."'
									,'".$factura->patente."'
									,".(($factura->aaaid == '')? 'NULL': $factura->aaaid)."
									,".(($factura->packing_list_id == '')? 'NULL': "'".$factura->packing_list_id."'")."
									,".(($factura->certificado_origen_id == '')? 'NULL': "'".$factura->certificado_origen_id."'")."
									,".(($factura->ticket_bascula_id == '')? 'NULL': "'".$factura->ticket_bascula_id."'")."
									,".(($prefile_id == '')? 'NULL': "'".$prefile_id."'")."
								 )";
									   
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al insertar facturas. '.$consulta; 
						$respuesta['Error'] = ' ['.$error.']';
						
						break; 
					}/* else {
						$respuesta = fcn_salida_archivos_factura($oFiles, $cmysqli, $nSalidaNumero, $factura); //Guardamos archivos de la factura
						
						if ($respuesta['Codigo'] != 1) { 
							break;
						}
					}*/	
				}
			}
			
			/* ..:: Guardamos archivos ::.. */
			if ($respuesta['Codigo'] == 1) {
				$respuesta = fcn_salida_archivos($oFiles, $cmysqli, $nSalidaNumero, $bBorrarNOA, $bBorrarSolRet);
			}
		
			/* ..:: Eliminamos archivos de pedimento simplificado ::.. */
			if ($respuesta['Codigo'] == 1) {
				$respuesta = fcn_salida_eliminar_pedimentos_simp($aDataDelRow, $cmysqli);
			}
		
			if ($respuesta['Codigo'] == 1) { 
				mysqli_query($cmysqli, "COMMIT");
				$respuesta['Mensaje']='Salida ['.$nSalidaNumero.'] creada correctamente!!!';
				
				include ('salidaExpoCartaInstrucciones.php');
				$respuesta2 = fcn_enviar_notificacion_salida($nSalidaNumero, true);
				if ($respuesta2['Codigo'] != 1) { 
					$respuesta['Mensaje'] .= ' :: No se envio la notificacion al cliente '.$respuesta2['Error'];
				} 
			} else {
				mysqli_query($cmysqli, "ROLLBACK");
			}
		}
		
		$respuesta['nSalidaNumero']=$nSalidaNumero;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}	
	mysqli_close($cmysqli);
	return $respuesta;
}

function fcn_insertar_salida_lasid(&$respuesta) {
	global $cmysqli;
	
	$nSalidaNumero = 0;
	
	$consulta = "SELECT (salidanumero + 1) AS salida
				 FROM bodega.salidas_expo
				 ORDER BY salidanumero DESC
				 LIMIT 1";
				 
	$query = mysqli_query($cmysqli, $consulta);
				
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al crear nuevo id.'; 
		$respuesta['Error'] = ' ['.$error.']';
	} else {
		while($row = mysqli_fetch_object($query)){
			$nSalidaNumero=$row->salida; 
			break;
		}
	}
	
	return $nSalidaNumero;
}

function fcn_editar_salida(){	
	global $_POST, $username, $cmysqli;
	
	$respuesta['Codigo']=1;
	
	if (isset($_POST['nSalidaNumero']) && !empty($_POST['nSalidaNumero'])) {
		$nSalidaNumero = $_POST['nSalidaNumero'];
		//$sFecha = $_POST['sFecha'];
		$sFerrocarril = $_POST['sFerrocarril'];
		//$sTipoSalida = $_POST['sTipoSalida'];
		//$sTipoSalidaCaja = $_POST['sTipoSalidaCaja'];
		$sEntregarEnDir = $_POST['sEntregarEnDir'];
		$sCrucesEnSalida = $_POST['sCrucesEnSalida'];
		$sUrgente = $_POST['sUrgente'];
		$sLeyenda = $_POST['sLeyenda'];
		$sHoraEntrada = $_POST['sHoraEntrada'];
		$sRecibio = $_POST['sRecibio'];
		$sNumeroViaje = $_POST['sNumeroViaje'];
		$sIndicaciones = $_POST['sIndicaciones'];
		$sObservaciones = $_POST['sObservaciones'];
		$sLineaTranspId = $_POST['sLineaTranspId'];
		$sLineaTranspName = $_POST['sLineaTranspName'];
		$sAduanaId = $_POST['sAduanaId'];
		$sTransferId = $_POST['sTransferId'];
		$sTransferName = $_POST['sTransferName'];
		$sEntregarEnId = $_POST['sEntregarEnId'];
		$sEntregarEnName = $_POST['sEntregarEnName'];
		$aFacturas = json_decode($_POST['aFacturas']);
		$aDataDelRow = json_decode($_POST['aDataDelRow']);
		$bBorrarNOA = $_POST['bBorrarNOA'];
		$bBorrarSolRet = $_POST['bBorrarSolRet'];
		$oFiles = $_FILES;
		
		/*include ('salidaExpoCartaInstrucciones.php');
		$respuesta = fcn_enviar_notificacion_salida($nSalidaNumero, true);
		
		$respuesta['nSalidaNumero']=$nSalidaNumero;
		return $respuesta;
		exit();*/
		
		//***********************************************************//
		
		mysqli_query($cmysqli, 'BEGIN');
		
		$respuesta = fcn_buscapedimento_simplificado_facturas($aFacturas);
		
		//usuario='".$username."',
		if ($respuesta['Codigo'] == 1) {		
			$consulta = "UPDATE bodega.salidas_expo
						 SET lineatransp='".$sLineaTranspName."',
							 aduana='".$sAduanaId."',
							 notransfer=".$sTransferId.",
							 nombretransfer='".$sTransferName."',
							 noentrega=".$sEntregarEnId.",
							 Nombreentrega='".$sEntregarEnName."',
							 direntrega='".$sEntregarEnDir."',
							 cruces=".$sCrucesEnSalida.",
							 urgente='".$sUrgente."',
							 horaentrega='".$sHoraEntrada."',
							 recibio='".$sRecibio."',
							 indicaciones='".$sIndicaciones."',
							 observaciones='".$sObservaciones."',
							 ferrocarril='".$sFerrocarril."',
							 viaje='".$sNumeroViaje."',
							 ".(($sLeyenda == '')? "leyenda=NULL": "leyenda='".$sLeyenda."'").",
							 nolineatransp=".$sLineaTranspId."
						 WHERE salidanumero=".$nSalidaNumero;
						 
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al actualizar la Salida.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else {
				$consulta = "DELETE FROM bodega.facturas_expo
							 WHERE SALIDA_NUMERO=".$nSalidaNumero;
							 
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al eliminar facturas de la salida ['.$nSalidaNumero.'].'; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					foreach ($aFacturas as &$factura) {
						$prefile_id = $factura->prefile_id;
						if ($prefile_id == '') {
							$respPrefile = fcn_buscar_prefile_factura($nSalidaNumero, $factura->caja, $factura->factura);
							if ($respPrefile['Codigo'] == 1) {
								$prefile_id = $respPrefile['id_documento'];
							}
						}
					
						$consulta = "INSERT INTO bodega.facturas_expo (  
										 FACTURA_NUMERO
										,TIPOSALIDA
										,CAJA
										,NUMCLIENTE
										,VALOR_FACTURA
										,REFERENCIA
										,PEDIMENTO
										,SALIDA_NUMERO
										,ID_REGISTRO
										,CONS_FACT_PED
										,NUM_REM_PED
										,UUID
										,PATENTE
										,NOAAA
										,PACKING_LIST_ID
										,CERTIFICADO_ORIGEN_ID
										,TICKET_BASCULA_ID
										,PREFILE_ID
									 )
									 VALUES (
										 '".$factura->factura."'
										,'".$factura->tiposalida."'
										,'".$factura->caja."'
										,'".$factura->clienteid."'
										,0
										,'".$factura->referencia."'
										,".$factura->pedimento."
										,".$nSalidaNumero."
										,(
											SELECT (b.ID_REGISTRO + 1) AS ID_REGISTRO
											FROM bodega.facturas_expo AS b
											ORDER BY b.ID_REGISTRO DESC
											LIMIT 1
										)
										,".(($factura->cons_fac_ped == '')? 'NULL': $factura->cons_fac_ped)."
										,".(($factura->num_rem_ped == '')? 'NULL': $factura->num_rem_ped)."
										,'".$factura->uuid."'
										,'".$factura->patente."'
										,".(($factura->aaaid == '')? 'NULL': $factura->aaaid)."
										,".(($factura->packing_list_id == '')? 'NULL': "'".$factura->packing_list_id."'")."
										,".(($factura->certificado_origen_id == '')? 'NULL': "'".$factura->certificado_origen_id."'")."
										,".(($factura->ticket_bascula_id == '')? 'NULL': "'".$factura->ticket_bascula_id."'")."
										,".(($prefile_id == '')? 'NULL': "'".$prefile_id."'")."
									 )";
									 
						$query = mysqli_query($cmysqli, $consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al insertar facturas. '.$consulta; 
							$respuesta['Error'] = ' ['.$error.']';
							
							break; 
						} /*else {
							$respuesta = fcn_salida_archivos_factura($oFiles, $cmysqli, $nSalidaNumero, $factura); //Guardamos archivos de la factura
							
							if ($respuesta['Codigo'] != 1) { 
								break;
							}
						}*/	
					}
				}
			}
		}
		
		/* ..:: Guardamos archivos ::.. */
		if ($respuesta['Codigo'] == 1) {
			$respuesta = fcn_salida_archivos($oFiles, $cmysqli, $nSalidaNumero, $bBorrarNOA, $bBorrarSolRet);
		}
		
		/* ..:: Eliminamos archivos de pedimento simplificado ::.. */
		if ($respuesta['Codigo'] == 1) {
			$respuesta = fcn_salida_eliminar_pedimentos_simp($aDataDelRow, $cmysqli);
		}
		
		if ($respuesta['Codigo'] == 1) { 
			mysqli_query($cmysqli, "COMMIT");
			$respuesta['Mensaje']='Salida ['.$nSalidaNumero.'] actualizada correctamente!!!';
			
			include ('salidaExpoCartaInstrucciones.php');
			$respuesta2 = fcn_enviar_notificacion_salida($nSalidaNumero, true);
			if ($respuesta2['Codigo'] != 1) { 
				$respuesta['Mensaje'] .= ' :: No se envio la notificacion al cliente '.$respuesta2['Error'];
			} 
		} else {
			mysqli_query($cmysqli, "ROLLBACK");
		}		
		
		$respuesta['nSalidaNumero']=$nSalidaNumero;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}	
	mysqli_close($cmysqli);
	return $respuesta;
}

function fcn_eliminar_factura() {
	global $_POST, $username, $cmysqli;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['nSalidaNumero']) && !empty($_POST['nSalidaNumero'])) { 
		$nSalidaNumero = $_POST['nSalidaNumero'];
		$aDataDelRow = json_decode($_POST['aDataDelRow']);

		if ($nSalidaNumero != '-1') {
			foreach ($aDataDelRow as &$factura) { 
				$consulta = "DELETE FROM bodega.facturas_expo
							 WHERE SALIDA_NUMERO=".$nSalidaNumero." AND
								   REFERENCIA='".$factura->referencia."' AND
								   CONS_FACT_PED=".$factura->cons_fac_ped;

				$query = mysqli_query($cmysqli, $consulta);
				 if (!$query) {
					  $error=mysqli_error($cmysqli);
					  $respuesta['Codigo']=-1;
					  $respuesta['Mensaje']='Error al eliminar la factura de la salida ['.$nSalidaNumero.'].'; 
					  $respuesta['Error'] = ' ['.$error.']';
				 }
			}
		}
		
		/* ..:: Eliminamos archivos de pedimento simplificado ::.. */
		if ($respuesta['Codigo'] == 1) {
			$respuesta = fcn_salida_eliminar_pedimentos_simp($aDataDelRow, $cmysqli);			
			$respuesta['Mensaje']='Factura borrada correctamente';
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}	

	return $respuesta;
}

/* DEPRESIADO */
function fcn_subir_documentos(){
	global $_POST, $username, $sPathFilesExpo, $cmysqli;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['sDocumentoTipo']) && !empty($_POST['sDocumentoTipo'])) {
		$sDocumentoTipo = $_POST['sDocumentoTipo'];
		$sDocumentoReferencia = $_POST['sDocumentoReferencia'];
		$nUniqueId = $_POST['nUniqueId'];
		$nArchivos = $_POST['nArchivos'];
		$oFiles = $_FILES;
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		$id_documento = 0;
		
		//***********************************************************//
		
		/* ..:: Insertar Registro de documento ::.. */
		mysqli_query($cmysqli, 'BEGIN');
			
		$consulta = "INSERT INTO bodega.documentos_expo (  
						 tipo
						,referencia
						,uniqueid
					 ) VALUES (
						 '".$sDocumentoTipo."'
						,".(($sDocumentoReferencia == '')? "NULL": "'".$sDocumentoReferencia."'")."
						,'".$nUniqueId."'
					 )";
					 
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al guardar archivo.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$id_documento = mysqli_insert_id($cmysqli);
		}
		
		/* ..:: Archivo(s) ::.. */
		if ($respuesta['Codigo'] == 1) {
			$sFileName = '';
			if ($nArchivos > 1) { 
				$aArchivos=array();
				
				foreach($oFiles as $file){
					$ext = explode('.', basename($file['name']));
					$ext = array_reverse($ext);
					$sName = array_pop($ext);
					$sName = str_replace(" ", "_", $sName);
					
					if ($sFileName == '') {
						$sFileName = $sName.'_'.$sDocumentoTipo.'_'.$id_documento.'.pdf';
					}
					$target = sys_get_temp_dir() .'\\'. $sName = str_replace(" ", "_", $file['name']);
					
					if(move_uploaded_file($file['tmp_name'], $target)) {
						array_push($aArchivos, $target);
					}
				}
				
				$target = $sPathFilesExpo . DIRECTORY_SEPARATOR . $sFileName;
				
				$sArchivos = '';
				foreach ($aArchivos as &$archivo) { 
					$sArchivos .= $archivo . ' ';
				}
				
				if ($sArchivos != '') {
					$sComando = '"C:\Program Files\gs\gs9.23\bin\gswin64" -dBATCH -dNOPAUSE -q -dSAFER -sDEVICE=pdfwrite -sOutputFile='.$target.' '.$sArchivos;
					$output = shell_exec($sComando);		
					if ($output != '') {
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al guardar archivo.'; 
						$respuesta['Error'] = ' ['.$output.']';
					}
				}
			} else {
				foreach($oFiles as $file){ 
					$ext = explode('.', basename($file['name']));
					$ext = array_reverse($ext);
					$sName = array_pop($ext);
					$sName = str_replace(" ", "_", $sName);
					
					$sFileName = $sName.'_'.$sDocumentoTipo.'_'.$id_documento.'.pdf';
					$target = $sPathFilesExpo . DIRECTORY_SEPARATOR . $sFileName;
					$respuesta['target']=$target;
					
					if(move_uploaded_file($file['tmp_name'], $target)) {
						array_push($aArchivos, $target);
					}
				}
			}
			
			if ($respuesta['Codigo'] == 1) {
				$consulta = "UPDATE bodega.documentos_expo
							 SET nombre_archivo='".$sFileName."'
							 WHERE id_documento=".$id_documento;
									 
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al editar nombre del archivo guardado.'; 
					$respuesta['Error'] = ' ['.$error.']';
				}
			}
		}
		
		if ($respuesta['Codigo'] == 1) { 
			mysqli_query($cmysqli, "COMMIT");
			$respuesta['Mensaje']='Archivo guardado correctamente!!!';
		} else {
			mysqli_query($cmysqli, "ROLLBACK");
		}
		
		$respuesta['id_documento']=$id_documento;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}	
	mysqli_close($cmysqli);
	return $respuesta;
}

/***********/

function fcn_subir_documentos2(){
	global $_POST, $username, $sPathFilesExpo;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['sTask']) && !empty($_POST['sTask'])) {
		$sTask = $_POST['sTask'];
		$nUniqueId = $_POST['nUniqueId'];
		$oFiles = $_FILES;
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		$id_documento = 0;
		
		$pkl = $pkl_name = '';
		$cdo = $cdo_name = '';
		$tdb = $tdb_name = '';
		$pre = $pre_name = $pre_entry = '';
		
		//***********************************************************//
		
		//Packing List
		if (isset($oFiles['PackingList'])) {
			$respuesta = fcn_insertar_documento('PackingList', 'PKL', '', $nUniqueId, $oFiles);
			
			if ($respuesta['Codigo'] == 1) {
				$pkl = $respuesta['id_documento'];
				$pkl_name = $respuesta['sFileName'];
			}
		}
		
		//Certificado de Origen
		if (isset($oFiles['CerOrigen'])) {
			$respuesta = fcn_insertar_documento('CerOrigen', 'CDO', '', $nUniqueId, $oFiles);
			
			if ($respuesta['Codigo'] == 1) {
				$cdo = $respuesta['id_documento'];
				$cdo_name = $respuesta['sFileName'];
			}
		}
		
		//Ticket de Bascula
		if (isset($oFiles['TicketBascula'])) {
			$respuesta = fcn_insertar_documento('TicketBascula', 'TDB', '', $nUniqueId, $oFiles);
			
			if ($respuesta['Codigo'] == 1) {
				$tdb = $respuesta['id_documento'];
				$tdb_name = $respuesta['sFileName'];
			}
		}
		
		//Prefile
		if (isset($oFiles['Prefile'])) {
			$sDocumentoReferencia = $_POST['sDocumentoReferencia'];
			$respuesta = fcn_insertar_documento('Prefile', 'PRE', $sDocumentoReferencia, $nUniqueId, $oFiles);
			
			if ($respuesta['Codigo'] == 1) {
				$pre = $respuesta['id_documento'];
				$pre_name = $respuesta['sFileName'];
				$pre_entry = $sDocumentoReferencia;
			}
		}
	}
	
	$respuesta['pkl'] = $pkl;
	$respuesta['pkl_name'] = $pkl_name;
	
	$respuesta['cdo'] = $cdo;
	$respuesta['cdo_name'] = $cdo_name;
	
	$respuesta['tdb'] = $tdb;
	$respuesta['tdb_name'] = $tdb_name;
	
	$respuesta['pre'] = $pre;
	$respuesta['pre_name'] = $pre_name;
	$respuesta['pre_entry'] = $pre_entry;
	
	return $respuesta;
}

function fcn_insertar_documento($sNombreTipo, $sDocumentoTipo, $sDocumentoReferencia, $nUniqueId, $oFiles) {
	include('../../connect_dbsql.php');
	
	global $sPathFilesExpo;
	
	$respuesta['Codigo']=1;
	
	//***********************************************************//
		
	$fecha_registro =  date("Y-m-d H:i:s");
	$id_documento = 0;
	$sFileName = '';
	
	//***********************************************************//
		
	/* ..:: Insertar Registro de documento ::.. */
	mysqli_query($cmysqli, 'BEGIN');
	
	$consulta = "INSERT INTO bodega.documentos_expo (  
					 tipo
					,referencia
					,uniqueid
				 ) VALUES (
					 '".$sDocumentoTipo."'
					,".(($sDocumentoReferencia == '')? "NULL": "'".$sDocumentoReferencia."'")."
					,'".$nUniqueId."'
				 )";
				 
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al guardar archivo.'; 
		$respuesta['Error'] = ' ['.$error.']';
	} else {
		$id_documento = mysqli_insert_id($cmysqli);
	}
	
	/* ..:: Archivo(s) ::.. */
	if ($respuesta['Codigo'] == 1) {
		$ext = explode('.', basename($oFiles[$sNombreTipo]['name']));
		$ext = array_reverse($ext);
		$sName = array_pop($ext);
		$sName = str_replace(" ", "_", $sName);
				
		$sFileName = $sName.'_'.$sDocumentoTipo.'_'.$id_documento.'.pdf';
		$target = $sPathFilesExpo . DIRECTORY_SEPARATOR . $sFileName;
				
		if(move_uploaded_file($oFiles[$sNombreTipo]['tmp_name'], $target)) {
			$consulta = "UPDATE bodega.documentos_expo
						 SET nombre_archivo='".$sFileName."'
						 WHERE id_documento=".$id_documento;
								 
			$query = mysqli_query($cmysqli, $consulta);						
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al editar nombre del archivo guardado.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}
		}
	}
		
	if ($respuesta['Codigo'] == 1) { 
		mysqli_query($cmysqli, "COMMIT");
		$respuesta['Mensaje']='Archivo guardado correctamente!!!';
	} else {
		mysqli_query($cmysqli, "ROLLBACK");
	}
	
	$respuesta['id_documento']=$id_documento;
	$respuesta['sFileName']=$sFileName;
	
	mysqli_close($cmysqli);
	return $respuesta;
}

function fcn_buscar_documentos_entry_number(){
	global $_POST, $cmysqli;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['sEntryNumber']) && !empty($_POST['sEntryNumber'])) {
		$sEntryNumber = $_POST['sEntryNumber'];
		$nUniqueId = $_POST['nUniqueId'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		$aDocumentos = array();
		
		//***********************************************************//
		
		$consulta = "SELECT IF(a.id_doc_master IS NULL, a.id_documento, a.id_doc_master) AS id_documento, a.nombre_archivo
					 FROM bodega.documentos_expo AS a
					 WHERE a.tipo='PRE' AND
						   a.referencia='".$sEntryNumber."'/* AND 
						   a.id_documento NOT IN (SELECT b.PREFILE_ID
												  FROM bodega.facturas_expo AS b
												  WHERE b.PREFILE_ID IS NOT NULL)*/";
		
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar documentos.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			while($row = mysqli_fetch_object($query)){
				$id=$row->id_documento; 
				$nombre=$row->nombre_archivo;
				array_push($aDocumentos,array('id'=>$id,'text'=>$nombre));
			} 
		}
		
		$respuesta['aDocumentos']=$aDocumentos;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error'] = '';
	}	
	mysqli_close($cmysqli);
	return $respuesta;
}

function fcn_buscar_documentos(){
	global $_POST, $cmysqli;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['sDocumentoTipo']) && !empty($_POST['sDocumentoTipo'])) {
		$sDocumentoTipo = $_POST['sDocumentoTipo'];
		$nUniqueId = $_POST['nUniqueId'];
		$nSalidaNumero = $_POST['nSalidaNumero'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		$aDocumentos = array();
		
		//***********************************************************//
		
		$consulta = "SELECT a.id_documento, a.nombre_archivo
					 FROM bodega.documentos_expo AS a LEFT JOIN
 	                      bodega.facturas_expo AS b ON a.id_documento=b.PACKING_LIST_ID LEFT JOIN
						  bodega.facturas_expo AS c ON a.id_documento=c.CERTIFICADO_ORIGEN_ID LEFT JOIN
						  bodega.facturas_expo AS d ON a.id_documento=d.TICKET_BASCULA_ID LEFT JOIN
						  bodega.facturas_expo AS e ON a.id_documento=e.PREFILE_ID
					 WHERE a.tipo='".$sDocumentoTipo."' AND 
						  (a.uniqueid='".$nUniqueId."' OR
						   b.SALIDA_NUMERO=".$nSalidaNumero." OR
						   c.SALIDA_NUMERO=".$nSalidaNumero." OR
						   d.SALIDA_NUMERO=".$nSalidaNumero." OR
						   e.SALIDA_NUMERO=".$nSalidaNumero.")
					 GROUP BY id_documento";
		
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar documentos.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			while($row = mysqli_fetch_object($query)){
				$id=$row->id_documento; 
				$nombre=$row->nombre_archivo;
				array_push($aDocumentos,array('id'=>$id,'text'=>$nombre));
			} 
		}
		
		$respuesta['aDocumentos']=$aDocumentos;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}	
	mysqli_close($cmysqli);
	return $respuesta;
}

function fcn_buscar_prefile_factura($nSalidaNumero, $sCaja, $sFactura){
	global $cmysqli;
	
	$respuesta['Codigo']=1;
	$id_documento = '';
	
	$consulta="SELECT a.id_documento
			   FROM bodega.documentos_expo AS a
			   WHERE a.caja='".$sCaja."' AND 
					 a.factura REGEXP '\\\^".$sFactura."\\\^' AND 
					 a.id_documento NOT IN (SELECT IF(b.SALIDA_NUMERO = ".$nSalidaNumero.", 0, b.PREFILE_ID)
											FROM bodega.facturas_expo AS b
											WHERE b.PREFILE_ID IS NOT NULL)
			   ORDER BY a.fecha_creacion DESC
			   LIMIT 1";
	
	$queryDocs = mysqli_query($cmysqli, $consulta);
	if (!$queryDocs) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='SALIDAEXPO salidaExpoFunc :: Error al consultar el prefile de la factura ['.$sFactura.'], Salida ['.$nSalidaNumero.'].'; 
		$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
	} else {
		while($rowDocs = mysqli_fetch_object($queryDocs)){ 
			$id_documento = $rowDocs->id_documento;
			break;
		}
	}
	
	$respuesta['id_documento']=$id_documento;
	return $respuesta;
}

/* ..:: (DEPRESIADO) guardamos los archivos por factura ::.. */
/*function fcn_salida_archivos_factura($oFiles, $cmysqli, $nSalidaNumero, $factura) {
	global $sPathFilesCruces;
	
	$respuesta['Codigo']=1;
	$sReferencia = $factura->referencia;
	$sFactura = $factura->factura;
	
	//Packing List
	if ($respuesta['Codigo'] == 1) {
		$sFileObject = $sReferencia . '_' . $sFactura . '_PackingList';
		if (isset($oFiles[$sFileObject])) {
			$ext = explode('.', basename($oFiles[$sFileObject]['name']));
			$sFileName = $nSalidaNumero . "_PackList_" . $sReferencia . '_'. $sFactura .'.'. array_pop($ext);
			$target = $sPathFilesCruces . DIRECTORY_SEPARATOR . $sFileName;
			
			if(move_uploaded_file($oFiles[$sFileObject]['tmp_name'], $target)) {
				$consulta = "UPDATE bodega.facturas_expo
							 SET PACKING_LIST_NAME='".$sFileName."'
							 WHERE SALIDA_NUMERO=".$nSalidaNumero." AND
								   REFERENCIA='".$sReferencia."' AND
								   FACTURA_NUMERO='".$sFactura."'";
							 
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al editar nombre packing list.'; 
					$respuesta['Error'] = ' ['.$error.']';
				}
			}
		}
	}
		
	//Certificado de Origen
	if ($respuesta['Codigo'] == 1) {
		$sFileObject = $sReferencia . '_' . $sFactura . '_CerOrigen';
		if (isset($oFiles[$sFileObject])) {
			$ext = explode('.', basename($oFiles[$sFileObject]['name']));
			$sFileName = $nSalidaNumero . "_CerOri_" . $sReferencia . '_'. $sFactura .'.'. array_pop($ext);
			$target = $sPathFilesCruces . DIRECTORY_SEPARATOR . $sFileName;
			
			if(move_uploaded_file($oFiles[$sFileObject]['tmp_name'], $target)) {
				$consulta = "UPDATE bodega.facturas_expo
							 SET CERTIFICADO_ORIGEN_NAME='".$sFileName."'
							 WHERE SALIDA_NUMERO=".$nSalidaNumero." AND
								   REFERENCIA='".$sReferencia."' AND
								   FACTURA_NUMERO='".$sFactura."'";
							 
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al editar nombre certificado de origen.'; 
					$respuesta['Error'] = ' ['.$error.']';
				}
			}
		}
	}
		
		
	//Ticket de Bascula
	if ($respuesta['Codigo'] == 1) {
		$sFileObject = $sReferencia . '_' . $sFactura . '_TicketBascula';
		if (isset($oFiles[$sFileObject])) {
			$ext = explode('.', basename($oFiles[$sFileObject]['name']));
			$sFileName = $nSalidaNumero . "_TicketBasc_" . $sReferencia . '_'. $sFactura .'.'. array_pop($ext);
			$target = $sPathFilesCruces . DIRECTORY_SEPARATOR . $sFileName;
			
			if(move_uploaded_file($oFiles[$sFileObject]['tmp_name'], $target)) {
				$consulta = "UPDATE bodega.facturas_expo
							 SET TICKET_BASCULA_NAME='".$sFileName."'
							 WHERE SALIDA_NUMERO=".$nSalidaNumero." AND
								   REFERENCIA='".$sReferencia."' AND
								   FACTURA_NUMERO='".$sFactura."'";
							 
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al editar nombre ticket de bascula.'; 
					$respuesta['Error'] = ' ['.$error.']';
				}
			}
		}
	}
	
	return $respuesta;
}*/

/* ..:: Funcion para verificar si las facturas a guardar tienen pedimento simplificado (Insertar o Editar Salida) ::.. */
function fcn_buscapedimento_simplificado_facturas($aFacturas) {
	include ('../../connect_casa.php');
	
	global $sPathFilesPed2009;
	
	$respuesta['Codigo']=1;
	
	foreach ($aFacturas as &$factura) {
		$sReferencia = strtoupper($factura->referencia);
		$sNumRemPed = $factura->num_rem_ped;
		$sFactura = $factura->factura;
		$sTipoPedimento = 'normal';
		
		//error_log('SALIDAEXPO sReferencia:'.$sReferencia.' :: sNumRemPed:'.$sNumRemPed.' :: sFactura:'.$sFactura);
		//if ($sNumRemPed != '') {
			/* ..:: Consultamos referencia en casa ::.. */
			$consulta = "SELECT a.NUM_PEDI, a.PAT_AGEN, a.FIR_REME
						 FROM SAAIO_PEDIME a
						 WHERE a.NUM_REFE='".$sReferencia."'";
			
			$query = odbc_exec($odbccasa, $consulta);
			if ($query==false){ 
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al consultar la Referencia [".$sReferencia."] en el sistema CASA.";
				$respuesta['Error'] = ' ['.$query.']';
			} else {
				if(odbc_num_rows($query)<=0){ 
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "La referencia [".$sReferencia."] no existe en el sistema CASA.";
					$respuesta['Error'] = ' ['.$query.']';
				} else { 
					while(odbc_fetch_row($query)){ 
						$sTipoPedimento = (is_null(odbc_result($query,"FIR_REME"))? 'normal': 'consolidado');
						break;
					}
				}
			}
			
			//error_log('SALIDAEXPO respuesta '.json_encode($respuesta));
			//error_log('SALIDAEXPO sTipoPedimento '.$sTipoPedimento);
			if ($respuesta['Codigo'] == 1) {
				if ($sTipoPedimento == 'consolidado') {
					$sFilePath = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sReferencia.'-'.$sNumRemPed.'.pdf';
					if (file_exists($sFilePath) == false) {
						$respuesta['Codigo']=100;
						$respuesta['sReferencia']=$sReferencia;
						$respuesta['sNumRemPed']=$sNumRemPed;
						$respuesta['sFactura']=$sFactura;
					}
				} else { //normal
					$sFilePath = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sReferencia.'-transportista.pdf';
					//error_log('SALIDAEXPO sFilePath '.$sFilePath);
					if (file_exists($sFilePath) == false) {
						$respuesta['Codigo']=101;
						$respuesta['sReferencia']=$sReferencia;
						$respuesta['sNumRemPed']=$sNumRemPed;
						$respuesta['sFactura']=$sFactura;
					}
				}
			}
		//}
	}
	
	return $respuesta;
}

/* ..:: Para guardar relacion de documentos y prefile ::.. */
function fcn_salida_archivos($oFiles, $cmysqli, $nSalidaNumero, $bBorrarNOA, $bBorrarSolRet){ 
	global $sPathFilesExpo;
	
	$respuesta['Codigo']=1;
	
	//Relacion de documentos
	if (isset($oFiles['oPdfRelDocs'])) {
		$ext = explode('.', basename($oFiles['oPdfRelDocs']['name']));
		$sFileName = $nSalidaNumero. "_reldocs." . array_pop($ext);
		$target = $sPathFilesExpo . DIRECTORY_SEPARATOR . $sFileName;
		
		if(move_uploaded_file($oFiles['oPdfRelDocs']['tmp_name'], $target)) {
			$consulta = "UPDATE bodega.salidas_expo
						 SET relacion_docs_name='".$sFileName."'
						 WHERE salidanumero=".$nSalidaNumero;
						 
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al editar nombre relación de documentos.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}
		}
	}
	
	//Notificacion de arribo (NOA)
	if (isset($oFiles['oPdfNOA'])) {
		$ext = explode('.', basename($oFiles['oPdfNOA']['name']));
		$sFileName = $nSalidaNumero. "_noa." . array_pop($ext);
		$target = $sPathFilesExpo . DIRECTORY_SEPARATOR . $sFileName;
		
		if(move_uploaded_file($oFiles['oPdfNOA']['tmp_name'], $target)) {
			$consulta = "UPDATE bodega.salidas_expo
						 SET notificacion_arribo_name='".$sFileName."'
						 WHERE salidanumero=".$nSalidaNumero;
						 
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al editar nombre NOA.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}
		}
	} else {
		if ($bBorrarNOA == 'SI') {
			$sFileName = $nSalidaNumero. "_noa.pdf";
			$target = $sPathFilesExpo . DIRECTORY_SEPARATOR . $sFileName;
			if (file_exists($target)) {
				unlink($target);
				$consulta = "UPDATE bodega.salidas_expo
							 SET notificacion_arribo_name=NULL
							 WHERE salidanumero=".$nSalidaNumero;
							 
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al editar nombre NOA.'; 
					$respuesta['Error'] = ' ['.$error.']';
				}
			}
		}
	}
	
	//Solicitud de Retiro
	if (isset($oFiles['oPdfSolRet'])) {
		$ext = explode('.', basename($oFiles['oPdfSolRet']['name']));
		$sFileName = $nSalidaNumero. "_solicitud_retiro." . array_pop($ext);
		$target = $sPathFilesExpo . DIRECTORY_SEPARATOR . $sFileName;
		
		if(move_uploaded_file($oFiles['oPdfSolRet']['tmp_name'], $target)) {
			$consulta = "UPDATE bodega.salidas_expo
						 SET solicitud_retiro_name='".$sFileName."'
						 WHERE salidanumero=".$nSalidaNumero;
						 
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al editar nombre NOA.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}
		}
	} else {
		if ($bBorrarSolRet == 'SI') {
			$sFileName = $nSalidaNumero. "_solicitud_retiro.pdf";
			$target = $sPathFilesExpo . DIRECTORY_SEPARATOR . $sFileName;
			if (file_exists($target)) {
				unlink($target);
				$consulta = "UPDATE bodega.salidas_expo
							 SET solicitud_retiro_name=NULL
							 WHERE salidanumero=".$nSalidaNumero;
							 
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al editar nombre Solicitud Retiro.'; 
					$respuesta['Error'] = ' ['.$error.']';
				}
			}
		}
	}
	
	return $respuesta;
}

/* ..:: Para eliminar Pedimentos simplificados ::.. */
function fcn_salida_eliminar_pedimentos_simp($aDataDelRow, $cmysqli){ 
	global $sPathFilesPed2009, $sPathFilesCruces;
	
	$respuesta['Codigo']=1;
	
	/* ..:: Borramos pedimento simplificado ::.. */
	/* EDITADO EL DIA 21 DICIEMBRE 2017 */
	/*foreach ($aDataDelRow as &$factura) {
		$consulta = "SELECT a.SALIDA_NUMERO
					 FROM bodega.facturas_expo AS a
					 WHERE a.REFERENCIA='".$factura->referencia."' AND
						   a.NUM_REM_PED=".$factura->num_rem_ped;
						   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar factura para eliminar'; 
			$respuesta['Error'] = ' ['.$error.']';
			
			break; 
		} else {
			$num_rows = mysqli_num_rows($query);
			if ($num_rows == 0) {
				$sFilePath = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $factura->referencia.'-'.$factura->num_rem_ped.'.pdf';
				if (file_exists($sFilePath)) {
					unlink($sFilePath);
				}
			}
		}
	}*/
	
	/* ..:: Borramos archivos de la factura ::.. */
	if ($respuesta['Codigo'] == 1) { 
		foreach ($aDataDelRow as &$factura) {
			//Simplificado caso de ser Consolidado de lo contrario seria 
			$sFilePath = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $factura->referencia.'-'.$factura->num_rem_ped.'.pdf';
			if (file_exists($sFilePath)) {
				unlink($sFilePath);
			}

			//PackingList
			/*if ($factura->packing_list_name != '') {
				$sFilePath = $sPathFilesCruces . DIRECTORY_SEPARATOR . $factura->packing_list_name ;
				if (file_exists($sFilePath)) {
					unlink($sFilePath);
				}
			}*/
			
			//Certificado de Origen
			/*if ($factura->certificado_origen_name != '') {
				$sFilePath = $sPathFilesCruces . DIRECTORY_SEPARATOR . $factura->certificado_origen_name ;
				if (file_exists($sFilePath)) {
					unlink($sFilePath);
				}
			}*/
			
			//Ticket Bascula
			/*if ($factura->ticket_bascula_name != '') {
				$sFilePath = $sPathFilesCruces . DIRECTORY_SEPARATOR . $factura->ticket_bascula_name ;
				if (file_exists($sFilePath)) {
					unlink($sFilePath);
				}
			}*/
		}
	}
	
	return $respuesta;
}