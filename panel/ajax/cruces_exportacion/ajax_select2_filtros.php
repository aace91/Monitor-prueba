<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../connect_casa.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
		$action = $_REQUEST['action'];
		$numcliente = $_REQUEST['num_cliente'];
		switch ($action) {
			case 'busca_linea_fletera' : $respuesta = fcn_buscalineat((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
			case 'busca_transfers' : $respuesta = fcn_buscatransfers_expo((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
			case 'busca_entregas_expo' : $respuesta = fcn_buscaentregas_expo((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
			case 'busca_aaa' : $respuesta = fcn_buscaaaa((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;	
			case 'busca_avisos_adhesion': $respuesta = fcn_busca_avisos_adhesion((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;	
			case 'busca_permisos': $respuesta = fcn_busca_permisos_pedimentos((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
			case 'busca_clientes': $respuesta = fcn_busca_clientes_consolidar((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
			case 'busca_regimen': $respuesta = fcn_busca_regimen_casa((isset($_POST['q']) ? $_POST['q'] : ""));
				echo json_encode($respuesta);
				break;
		}
	}else{
		$response['Codigo']=-1;
		$response['Mensaje']='Error al recibir los datos.';
	}
}

/*************************************************************************************************/
/* FUNCIONES                                                                                     */
/*************************************************************************************************/

/* SELECT2 */
function fcn_buscalineat($buscar){
	global $cmysqli;
	
	$response['items']=array();
	$consulta="		SELECT lt.numlinea, lt.nombre, GROUP_CONCAT( DISTINCT ce.email SEPARATOR '; ') as contactos_expo,
							IF(f.frecuencia IS NULL, 0 , f.frecuencia) as frecuencia
						FROM bodega.lineast lt
							LEFT JOIN bodega.contactos_expo ce ON
								lt.numlinea = ce.id_catalogo AND
								ce.tipo_catalogo = 'LTR'
							LEFT JOIN (SELECT numlinea,count(*) as frecuencia
													FROM bodega.cruces_expo 
													GROUP BY numlinea) f ON
								lt.numlinea = f.numlinea";
	if ($buscar!='')
		$consulta.="	WHERE lt.nombre LIKE '%".$buscar."%' AND lt.habilitado = '1'";
	$consulta.="		GROUP BY lt.numlinea
						ORDER BY frecuencia DESC, lt.nombre
						LIMIT 10";
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		exit(json_encode(array("error" => $mensaje)));
	}
	$aItemsFr = array();$aItemsNrm = array();
	while($row = mysqli_fetch_object($query)){
		if($row->frecuencia > 0){
			$id=$row->numlinea; 
			$nombre=$row->nombre; 
			$contactos_expo=$row->contactos_expo; 
			array_push($aItemsFr,array('id'=>$id,'text'=>$nombre,'contactos_expo'=>$contactos_expo));
		}else{
			$id=$row->numlinea; 
			$nombre=$row->nombre; 
			$contactos_expo=$row->contactos_expo; 
			array_push($aItemsNrm,array('id'=>$id,'text'=>$nombre,'contactos_expo'=>$contactos_expo));
		}
	}
	array_push($response['items'],array('text'=>'Frecuentes','children'=>$aItemsFr));
	array_push($response['items'],array('text'=>'Todos','children'=>$aItemsNrm));
	
	mysqli_close($cmysqli);
	return $response;
}

function fcn_buscatransfers_expo($buscar){
	global $cmysqli;
	
	$response['items']=array();
	$consulta="SELECT tr.notransfer, tr.nombretransfer, tr.caat, tr.scac,
						GROUP_CONCAT(DISTINCT ce.email SEPARATOR '; ') as contactos_tranfer,
						IF(f.frecuencia IS NULL, 0 , f.frecuencia) as frecuencia
				   FROM bodega.transfers_expo tr
							LEFT JOIN bodega.contactos_expo ce ON
								tr.notransfer = ce.id_catalogo AND
								ce.tipo_catalogo = 'TRA'
							LEFT JOIN (SELECT notransfer,count(*) as frecuencia
													FROM bodega.cruces_expo 
													GROUP BY notransfer) f ON
								tr.notransfer = f.notransfer";
	if ($buscar!='')
		$consulta.="	WHERE tr.nombretransfer LIKE '%".$buscar."%' AND tr.habilitado = '1'";
	$consulta.="	GROUP BY tr.notransfer
					ORDER BY frecuencia DESC,tr.nombretransfer
					LIMIT 10";//Tabla f utilizada para mostrar primero las lineas transportistas con mas frecuencia
				   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		return $response;
	}
	$aItemsFr = array();$aItemsNrm = array();
	while($row = mysqli_fetch_object($query)){
		if($row->frecuencia > 0){
			$id=$row->notransfer; 
			$nombre=$row->nombretransfer;
			$caat=$row->caat; 
			$scac=$row->scac;
			$contactos_tranfer=$row->contactos_tranfer;
			array_push($aItemsFr,array('id'=>$id,'text'=>$nombre,'caat'=>$caat,'scac'=>$scac,'contactos_tranfer'=>$contactos_tranfer));
		}else{
			$id=$row->notransfer; 
			$nombre=$row->nombretransfer;
			$caat=$row->caat; 
			$scac=$row->scac;
			$contactos_tranfer=$row->contactos_tranfer;
			array_push($aItemsNrm,array('id'=>$id,'text'=>$nombre,'caat'=>$caat,'scac'=>$scac,'contactos_tranfer'=>$contactos_tranfer));
		}
	}
	array_push($response['items'],array('text'=>'Frecuentes','children'=>$aItemsFr));
	array_push($response['items'],array('text'=>'Todos','children'=>$aItemsNrm));
	
	mysqli_close($cmysqli);
	return $response;
}

function fcn_buscaentregas_expo($buscar){
	global $cmysqli;
	$response['items']=array();
	
	$consulta="SELECT numeroentrega, nombreentrega, direccion,
						IF(f.frecuencia IS NULL, 0 , f.frecuencia) as frecuencia
				   FROM bodega.entregas_expo ex
						LEFT JOIN (SELECT noentrega,count(*) as frecuencia
													FROM bodega.cruces_expo
													GROUP BY noentrega) f ON
							ex.numeroentrega = f.noentrega";
	if ($buscar!='')
		$consulta.="	WHERE nombreentrega LIKE '%".$buscar."%'";
	$consulta.="	ORDER BY frecuencia DESC,nombreentrega
				   LIMIT 10";
	
	$query = mysqli_query( $cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		return $response;
	}
	$aItemsFr = array();$aItemsNrm = array();
	while($row = mysqli_fetch_object($query)){
		if($row->frecuencia > 0){
			$id=$row->numeroentrega; 
			$nombre=$row->nombreentrega; 
			$direccion=$row->direccion; 
			array_push($aItemsFr,array('id'=>$id,'text'=>$nombre, 'dir'=>$direccion));
		}else{
			$id=$row->numeroentrega; 
			$nombre=$row->nombreentrega; 
			$direccion=$row->direccion; 
			array_push($aItemsNrm,array('id'=>$id,'text'=>$nombre, 'dir'=>$direccion));
		}
	}
	array_push($response['items'],array('text'=>'Frecuentes','children'=>$aItemsFr));
	array_push($response['items'],array('text'=>'Todos','children'=>$aItemsNrm));
	
	mysqli_close($cmysqli);
	return $response;
}

function fcn_buscaaaa($buscar){
	global $cmysqli;	
	$response['items']=array();
	
	$consulta="		SELECT aaa.numeroaa, aaa.nombreaa,
						GROUP_CONCAT(DISTINCT ce.email SEPARATOR '; ') as contactos_aaa,
						IF(f.frecuencia IS NULL, 0 , f.frecuencia) as frecuencia
					FROM bodega.aaa
						LEFT JOIN bodega.contactos_expo ce ON
							aaa.numeroaa = ce.id_catalogo AND
							ce.tipo_catalogo = 'AAA'
						LEFT JOIN (SELECT noaaa,count(*) as frecuencia
										FROM bodega.cruces_expo_detalle ced
											INNER JOIN bodega.cruces_expo ce ON
												ced.id_cruce = ce.id_cruce
										GROUP BY noaaa) f ON
								aaa.numeroaa = f.noaaa";
	if ($buscar!='')
		$consulta.=" WHERE aaa.nombreaa LIKE '%".$buscar."%' AND aaa.habilitado = '1'";
	$consulta.="	GROUP BY aaa.numeroaa
					ORDER BY f.frecuencia DESC,aaa.nombreaa
					LIMIT 10";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		return $response;
	}
	$aItemsFr = array();$aItemsNrm = array();
	while($row = mysqli_fetch_object($query)){
		if($row->frecuencia > 0){
			$id=$row->numeroaa; 
			$nombre=$row->nombreaa; 
			$contactos_aaa=$row->contactos_aaa;
			array_push($aItemsFr,array('id'=>$id,'text'=>$nombre,'contactos_aaa'=>$contactos_aaa));
		}else{
			$id=$row->numeroaa; 
			$nombre=$row->nombreaa; 
			$contactos_aaa=$row->contactos_aaa;
			array_push($aItemsNrm,array('id'=>$id,'text'=>$nombre,'contactos_aaa'=>$contactos_aaa));
		}
	}
	array_push($response['items'],array('text'=>'Frecuentes','children'=>$aItemsFr));
	array_push($response['items'],array('text'=>'Todos','children'=>$aItemsNrm));
	
	mysqli_close($cmysqli);
	return $response;
}

function fcn_busca_avisos_adhesion($buscar){
	require('./../../../url_archivos.php');
	global $cmysqli;
	global $numcliente;
	
	$response['items']=array();
	
	$consulta="			SELECT id_permiso_adhesion, numero_permiso, CONCAT('".$URL_archivos_permisos."',archivo_permiso) as archivo_permiso
						FROM bodega.permisos_adhesion
						WHERE	id_cliente = '".$numcliente."' AND fecha_vigencia_ini <= NOW() AND fecha_vigencia_fin >= NOW()";
	if ($buscar!=''){
		$consulta.=" 		AND numero_permiso LIKE '%".$buscar."%'";
	}
	$consulta.=" 
						ORDER BY numero_permiso
						LIMIT 10";
						
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		return $response;
	}
	while($row = mysqli_fetch_object($query)){
		$id=$row->id_permiso_adhesion;
		$nombre=  $row->numero_permiso;
		$archivo_permiso=  $row->archivo_permiso;
		array_push($response['items'],array('id'=>$id,'text'=>$nombre,'url'=>$archivo_permiso));
	} 
		
	mysqli_close($cmysqli);
	return $response;
}

function fcn_busca_permisos_pedimentos($buscar){
	require('./../../../url_archivos.php');
	global $cmysqli;
	global $numcliente;
	
	$response['items']=array();
	
	$consulta="			SELECT id_permiso, numero_permiso,CONCAT('".$URL_archivos_permisos."',archivo_permiso) as archivo_permiso,
								aviso_adhesion
						FROM bodega.permisos_pedimentos
						WHERE	id_cliente = '".$numcliente."' AND fecha_vigencia_ini <= NOW() AND fecha_vigencia_fin >= NOW()";
	if ($buscar!=''){
		$consulta.=" 		AND numero_permiso LIKE '%".$buscar."%'";
	}
	$consulta.=" 
						ORDER BY numero_permiso
						LIMIT 10";
						
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		return $response;
	}
	while($row = mysqli_fetch_object($query)){
		$id=$row->id_permiso; 
		$permiso = $row->numero_permiso;
		$aviso_adhesion = $row->aviso_adhesion;
		$val_utilizado = fcn_valor_utilizado_permiso_auto($row->numero_permiso);
		$nombre= $permiso.' / '.$val_utilizado;
		$archivo_permiso=  $row->archivo_permiso;
		array_push($response['items'],array('id'=>$id,'text'=>$nombre,'url'=>$archivo_permiso,'aviso_adhesion'=>$aviso_adhesion));
	} 
		
	mysqli_close($cmysqli);
	return $response;
}

function fcn_valor_utilizado_permiso_auto($npermiso){
	global $odbccasa;
	global $cmysqli;
	
	$Valor_Dlls = 0;$Cantidad_kgs = 0;
	$qCasa = "SELECT b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
				FROM (  SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
							FROM SAAIO_PERPAR a
								INNER JOIN SAAIO_PEDIME c ON
									a.NUM_REFE = c.NUM_REFE
							WHERE a.NUM_PERM = '".$npermiso."' AND c.FIR_PAGO IS NULL
						UNION ALL
						SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI													
						FROM SAAIO_PERMIS a
							INNER JOIN SAAIO_PEDIME c ON
									a.NUM_REFE = c.NUM_REFE
						WHERE a.NUM_PERM = '".$npermiso."' AND c.FIR_PAGO IS NOT NULL) b
				GROUP BY (b.NUM_PERM)";

	$resped = odbc_exec ($odbccasa, $qCasa);
	if ($resped == false){
		$mensaje = "Error al consultar el valor dolares del permiso utilizado en pedimentos. BD.CASA.".odbc_error();
		return $mensaje;
	}else{
		while(odbc_fetch_row($resped)){
			$Valor_Dlls = odbc_result($resped,"VAL_DLLS");
			$Cantidad_kgs = odbc_result($resped,"CAN_TARI");
		}
	}
	$consulta ="SELECT p.valor_dlls_delbravo,p.cantidad_delbravo
				FROM permisos_pedimentos p
				WHERE p.numero_permiso = '".$npermiso."'";
				
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		return 'Error en la consulta: ' .$consulta.' , error:'.$error ;
	}
	while($row = mysqli_fetch_object($query)){
		$valor_dlls_delbravo = $row->valor_dlls_delbravo;
		$cantidad_delbravo =  $row->cantidad_delbravo;
	}
	$saldo_dlls = ($valor_dlls_delbravo - $Valor_Dlls);
	$saldo_kgs = ($cantidad_delbravo - $Cantidad_kgs);
	return '$'.number_format ($saldo_dlls,2). ' / '.number_format ($saldo_kgs,0);
}

function fcn_busca_clientes_consolidar($buscar){
	global $cmysqli;
	
	$response['items']=array();
	
	$consulta="SELECT gcliente, cnombre, IF(f.frecuencia IS NULL, 0 , f.frecuencia) as frecuencia
				FROM bodega.cltes_expo cli
					LEFT JOIN (SELECT numcliente_consolidar,count(*) as frecuencia
								FROM bodega.cruces_expo_detalle ced
									INNER JOIN bodega.cruces_expo ce ON
										ced.id_cruce = ce.id_cruce
								GROUP BY numcliente_consolidar) f ON
						gcliente = f.numcliente_consolidar";
	if ($buscar!='')
		$consulta.="	WHERE cnombre LIKE '%".$buscar."%'";
	$consulta.="	ORDER BY f.frecuencia DESC,cnombre
				   LIMIT 10";
	
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		return $response;
	}	
	$aItemsFr = array();$aItemsNrm = array();
	while($row = mysqli_fetch_object($query)){
		if($row->frecuencia > 0){
			$id=$row->gcliente; 
			$nombre=$row->cnombre;
			array_push($aItemsFr,array('id'=>$id,'text'=>$nombre));
		}else{
			$id=$row->gcliente; 
			$nombre=$row->cnombre;
			array_push($aItemsNrm,array('id'=>$id,'text'=>$nombre));
		}
	}
	array_push($response['items'],array('text'=>'Frecuentes','children'=>$aItemsFr));
	array_push($response['items'],array('text'=>'Todos','children'=>$aItemsNrm));
	
	
	mysqli_close($cmysqli);
	return $response;
}

function fcn_busca_regimen_casa($buscar){
	global $odbccasa;
	$response['items']=array();
	
	$qCasa = "SELECT FIRST 10 CVE_DOC,DES_DOC
				FROM (SELECT a.CVE_DOC, (a.CVE_DOC || '-' || a.DES_DOC) as DES_DOC FROM CTARC_DOCUME a)";
	if ($buscar!='')
		$qCasa.="	WHERE DES_DOC LIKE '".strtoupper($buscar)."%'";
	$qCasa.=" 	ORDER BY DES_DOC
				";
	$resped = odbc_exec ($odbccasa, $qCasa);
	if ($resped == false){
		$id = -1;
		$descipcion = "Error al consultar el catalogo de regimenes. BD.CASA.".odbc_error();
		array_push($response['items'],array('id'=>$id,'text'=> $descipcion));
	}else{
		while(odbc_fetch_row($resped)){
			$id = utf8_encode(odbc_result($resped,"CVE_DOC"));
			$descipcion = utf8_encode(odbc_result($resped,"DES_DOC"));
			array_push($response['items'],array('id'=>$id,'text'=> $descipcion));
		}
	}	
	return $response;
}

