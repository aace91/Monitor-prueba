<?php
set_time_limit(240);

include_once('./../../../../checklogin.php');
include('./../../../../connect_dbsql.php');
include('./../../../../url_archivos.php');
include('./../../../../bower_components/nusoap/src/nusoap.php');

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'pruebas/') !== false)? true : false);

if($loggedIn == false){
	exit('500');
} else {
	if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
		$action = $_REQUEST['action'];
		switch ($action) {
			case 'table_incrementables' : $respuesta = fcn_table_incrementables();
				echo json_encode($respuesta);
				break;

			case 'consultar_remision' : $respuesta = fcn_consultar_remision();
				echo json_encode($respuesta);
				break;
			
			case 'consultar_tarifas_honorarios' : $respuesta = fcn_consultar_tarifas_honorarios();
				echo json_encode($respuesta);
				break;

			case 'consultar_incrementable' : $respuesta = fcn_consultar_incrementable();
				echo json_encode($respuesta);
				break;
		}
	}
}

/*************************************************************************************************/
/* METODOS                                                                                       */
/*************************************************************************************************/

function fcn_table_incrementables(){
	global $_POST, $bDebug, $baseSql, $mysqluser, $mysqlpass, $mysqldb, $mysqlserver, $id;
	
	$table = 'inc_cruces_impo';
	$primaryKey = 'id_cruce';

	$columns = array(
		array( 'db' => 'id_cruce',       'dt' => 'id_cruce' ),
		array( 'db' => 'nombre_cliente', 'dt' => 'nombre_cliente' ),
		array( 'db' => 'remisiones',     'dt' => 'remisiones' ),
		array( 'db' => 'ejecutivo',      'dt' => 'ejecutivo' ),
		array( 'db' => 'total',          'dt' => 'total' ),
		array( 'db' => 'fecha_alta',     'dt' => 'fecha_alta', 'formatter' => function( $d, $row ) {
			if ($d==''){
				return '';
			}else{				
				return date( 'd/m/Y H:i A', strtotime($d)); 
			}               
		})
	);

	$sql_details = array(
		'user' => $mysqluser,
		'pass' => $mysqlpass,
		'db'   => $mysqldb,
		'host' => $mysqlserver
	);

	$baseSql = "SELECT cruces.id_cruce, cli.Nom AS nombre_cliente, cruces.fecha_alta,
                       GROUP_CONCAT(DISTINCT rem.remision) AS remisiones,
                       TRUNCATE(SUM(remdet.cantidad * remdet.tarifa), 2) AS total, bodUser.usunombre AS ejecutivo
                FROM facturacion.inc_cruces_impo AS cruces INNER JOIN
                     facturacion.inc_remision AS rem ON rem.id_cruce=cruces.id_cruce LEFT JOIN
                     facturacion.inc_remisiondet AS remdet ON remdet.remision=rem.remision LEFT JOIN
                     facturacion.tarifas_sb AS tarifas ON tarifas.id_tarifa=remdet.id_tarifa INNER JOIN
                     facturacion.clientes AS remcli ON remcli.id_inc_cliente=cruces.id_inc_cliente INNER JOIN
                     bodega.clientes AS cli ON cli.Cliente_id=remcli.id_cliente INNER JOIN 
                     bodega.tblusua AS bodUser ON bodUser.Usuario_id=cruces.usuario_creador
                WHERE cruces.fecha_salida IS NULL AND 
                      (tarifas.es_incrementable=1 OR remdet.tipo='LTL')
                GROUP BY cruces.id_cruce
                ORDER BY cruces.id_cruce DESC";

	require('./../../../ssp.class.php');
	return	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns );
}

function fcn_consultar_remision(){
	global $_POST, $bDebug, $cmysqli;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_POST['sRemision']) && !empty($_POST['sRemision'])) {
			$sRemision = $_POST['sRemision'];
			$sIdCliente = $_POST['sIdCliente'];
			$nReferencias = $_POST['nReferencias'];
			$nFechaCajaEntrada = $_POST['nFechaCajaEntrada'];
			
			//***********************************************************//
			
			$consulta = "SELECT id_cruce FROM facturacion.inc_remision WHERE remision=".$sRemision;
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar la remision. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else {
				while($row = mysqli_fetch_array($query)){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='La remision ['.$sRemision.'] ya se encuentra dada de alta en el cruce ['.$row['id_cruce'].']'; 
					$respuesta['Error'] = '';
				}
			}

			/**********************************************************************/

			$bCobrar = false;
			$aReferenciasRemision = array();

			if ($respuesta['Codigo'] == 1) {
				$bCobrar = fcn_get_cobrar_remision($sRemision);
				$aReferenciasRemision = fcn_get_referencias_remision($sRemision);
				if(count($aReferenciasRemision) > 0){ 
					$nReferencias += count($aReferenciasRemision);

					$sReferenciasIncorrectas = fcn_get_referencias_incorrectas($aReferenciasRemision);
					if ($sReferenciasIncorrectas == '') { 
						$sReferenciasIncorrectas = fcn_get_referencias_incorrectas_cliente($aReferenciasRemision, $sIdCliente);
						if ($sReferenciasIncorrectas != '') {
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Referencias Incorrectas</br>';
							$respuesta['Error'] = $sReferenciasIncorrectas;
						}
					} else {
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Referencias Incorrectas</br>';
						$respuesta['Error'] = $sReferenciasIncorrectas;
					}
				} else {
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "La remision [".$sRemision."] no cuenta con referencias."; 
					$respuesta['Error'] = '';
				}
			}

			if ($respuesta['Codigo'] == 1) {
				$nFechaCajaEntrada += fcn_get_conteo_fecha_caja_entrada($sRemision);
			}

			$respuesta['nReferencias'] = $nReferencias;
			$respuesta['nFechaCajaEntrada'] = $nFechaCajaEntrada;
			$respuesta['bCobrar'] = $bCobrar;
			$respuesta['aReferenciasRemision'] = count($aReferenciasRemision);
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_consultar_remision().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

function fcn_consultar_tarifas_honorarios(){
	global $_POST, $bDebug, $cmysqli, $id;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_POST['sIdCliente']) && !empty($_POST['sIdCliente'])) {
			$sIdCliente = $_POST['sIdCliente'];
			
			//***********************************************************//
			
			$aHonorarios = fcn_get_honorarios_tarifas($sIdCliente);

			$respuesta['sIdEjecutivo'] = $id;
			$respuesta['aHonorarios'] = $aHonorarios;
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_consultar_remision().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

function fcn_consultar_incrementable(){
	global $_POST, $bDebug, $cmysqli;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_POST['nIdCruce']) && !empty($_POST['nIdCruce'])) {
			$nIdCruce = $_POST['nIdCruce'];
			
			//***********************************************************//
			
			$fecha_registro = date("Y-m-d H:i:s");

			$aCruceData = array();
			$aPedimentos = array();

			$sClienteNombre = '';
			$sTransTipo = '';
			$sTipoSalida = '';
			$sTarifasIncSinAsignar = '';
			
			//***********************************************************//
			$consulta = "SELECT bodremdet.pedimento, TRUNCATE(SUM(remdet.cantidad * remdet.tarifa), 2) AS total
						 FROM facturacion.inc_remision AS rem INNER JOIN
							  facturacion.inc_remisiondet AS remdet ON remdet.remision=rem.remision INNER JOIN
							  bodega.remisiondet AS bodremdet ON bodremdet.remision=rem.remision AND 
							  bodremdet.referencia=remdet.referencia LEFT JOIN
							  facturacion.tarifas_sb AS tarifas ON tarifas.id_tarifa=remdet.id_tarifa     
						 WHERE rem.id_cruce=".$nIdCruce." AND 
							   (tarifas.es_incrementable=1 OR remdet.tipo='LTL')
						 GROUP BY bodremdet.pedimento
						 ORDER BY bodremdet.pedimento DESC";

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar incrementables. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else {
				while($row = mysqli_fetch_array($query)){
					$aRow = array(
						'pedimento' => $row["pedimento"],
						'total' => $row["total"]
					);

					array_push($aPedimentos, $aRow);
				}
			}

			if ($respuesta['Codigo'] == 1) {
				$consulta = "SELECT cruce.id_cruce, cli.Nom AS nombre_cliente,
									cruce.trans_tipo AS transtipo, trans.nombre AS trans_tipo, cruce.hazmat
							 FROM facturacion.inc_cruces_impo AS cruce INNER JOIN
								  facturacion.inc_tipo_transporte AS trans ON trans.id_transporte=cruce.trans_tipo INNER JOIN
								  facturacion.clientes faccli ON faccli.id_inc_cliente=cruce.id_inc_cliente INNER JOIN
								  bodega.clientes AS cli ON cli.Cliente_id=faccli.id_cliente
							 WHERE cruce.id_cruce=".$nIdCruce;
				
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al consultar datos del cruce. Por favor contacte al administrador del sistema.'; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					while($row = mysqli_fetch_array($query)){
						$sClienteNombre = $row["nombre_cliente"];
						$sTransTipo = $row["trans_tipo"] . (($row["hazmat"] == 1)? ' - Hazmat' : '');
						$sTipoSalida = fcn_get_tipo_salida($nIdCruce, $row["transtipo"]);
					}
				}
			}
			
			if ($respuesta['Codigo'] == 1) { 
				$sTarifasIncSinAsignar = fcn_get_tarifas_inc_sin_asignar($nIdCruce);
			}

			$aCruceData = array(
				'aPedimentos' => $aPedimentos,
				'sClienteNombre' => $sClienteNombre,
				'sTransTipo' => $sTransTipo,
				'sTipoSalida' => $sTipoSalida,
				'sTarifasIncSinAsignar' => $sTarifasIncSinAsignar
			);

			$respuesta['aCruceData']=$aCruceData;
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_consultar_incrementable().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

/*************************************************************************************************/
/* FUNCIONES                                                                                     */
/*************************************************************************************************/

/* ..:: Obtenemos las referencias de la remision ::.. */
function fcn_get_cobrar_remision($sRemision) { 
	global $bDebug, $cmysqli;

	include('./../../../../db.php');

	$bCobrar = false;

	/******************************************/

	//$pdo_accss_sconn_remisiones = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=".$rutaremisionesmdb.";Uid=; Pwd=;";
	//$conn_acc_rem = new PDO($pdo_accss_sconn_remisiones);
	$conn_acc_rem = new PDO("odbc:RemisionesMDB", "", "");
	$conn_acc_rem->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	

	$consulta = "SELECT cruza
				 FROM remisiongral
				 WHERE remision=".$sRemision;

	$resp_rem = $conn_acc_rem->query($consulta)->fetchAll();
	if(count($resp_rem) > 0){
		foreach ($resp_rem as $row) {
			$nCobrar = (($row['cruza'] == 1)? true : false);

			break;
		}
	} else {
		throw new Exception("La remision no existe.");
	}

	return $bCobrar;
}

/* ..:: Obtenemos las referencias de la remision ::.. */
function fcn_get_referencias_remision($sRemision) { 
	global $bDebug, $cmysqli;

	include('./../../../../db.php');

	/******************************************/

	//$pdo_accss_sconn_remisiones = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=".$rutaremisionesmdb.";Uid=; Pwd=;";
	//$conn_acc_rem = new PDO($pdo_accss_sconn_remisiones);
	$conn_acc_rem = new PDO("odbc:RemisionesMDB", "", "");
	$conn_acc_rem->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	

	$consulta = "SELECT referencia
				 FROM remisiondet
				 WHERE remision=".$sRemision;

	$resp_rem = $conn_acc_rem->query($consulta)->fetchAll();
	return $resp_rem;
}

/* ..:: Verificamos que la referencia tenga el detalle de bultos correcto ::.. */
function fcn_get_referencias_incorrectas($aReferenciasRemision) { 
	global $bDebug, $cmysqli, $URL_bodega;

	$sReferenciasIncorrectas = '';

	/******************************************/

	foreach ($aReferenciasRemision as $row) {
		$sReferencia = $row['referencia'];
		
		$consulta_mdb = "SELECT tipBultos.Tipo, tipBultos.id_facturacion, detBultos.cantidad
						 FROM detallebultos AS detBultos INNER JOIN  
							  tipobultos AS tipBultos ON tipBultos.Clave=detBultos.clavebulto
						 WHERE detBultos.referencia='".$sReferencia."'";

		$client = new nusoap_client($URL_bodega."webtools/ws_mdb/ws_mdb.php?wsdl","wsdl");
		$err = $client->getError();
		if ($err) {
			throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.");
		}

		$param = array(
			'usuario' => 'admin',
			'password' => 'r0117c',
			'consulta' => $consulta_mdb,
			'tipo' => 'SELECT',
			'bd' => 'bodega');
		
		$result = $client->call('ws_mdb', $param);
		$err = $client->getError();
		if ($err) {
			throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.". $err);
		}

		if($result['Codigo']==1){
			$bodegadet=json_decode($result['Adicional1']);
			if (count($bodegadet) > 0) {
				foreach($bodegadet as $row_bod){
					$sTipo = $row_bod->Tipo;
					$id_facturacion = $row_bod->id_facturacion;
					$sCantidad = intval($row_bod->cantidad);
	
					if (is_null($id_facturacion) || $id_facturacion == '') {
						$sReferenciasIncorrectas .= (($sReferenciasIncorrectas == '')? '' : $sReferenciasIncorrectas.'<br>');
						$sReferenciasIncorrectas .= 'La referencia <strong>'.$sReferencia.'</strong> tiene un tipo de bulto no permitido <strong>Cantidad: '.$sCantidad.'</strong>, tipo de bulto: <strong>'.$sTipo.'</strong>, favor de comunicarse con personal de bodega';
					}
				}
			} else {
				$sReferenciasIncorrectas .= (($sReferenciasIncorrectas == '')? '' : $sReferenciasIncorrectas.'<br>');
				$sReferenciasIncorrectas .= 'La referencia <strong>'.$sReferencia.'</strong> no tiene detalle de bultos configurado, favor de comunicarse con personal de bodega';
			}
		}
	}

	return $sReferenciasIncorrectas;
}

/* ..:: Verificamos que las referencias correspondan al mismo cliente ::.. */
function fcn_get_referencias_incorrectas_cliente($aReferenciasRemision, $sIdCliente) { 
	global $bDebug, $cmysqli, $URL_bodega;

	$sReferenciasIncorrectas = '';

	/******************************************/

	foreach ($aReferenciasRemision as $row) {
		$sReferencia = $row['referencia'];
		
		$consulta_mdb = "SELECT bodCli
						 FROM tblbod
						 WHERE bodReferencia='".$sReferencia."'";

		$client = new nusoap_client($URL_bodega."webtools/ws_mdb/ws_mdb.php?wsdl","wsdl");
		$err = $client->getError();
		if ($err) {
			throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.");
		}

		$param = array(
			'usuario' => 'admin',
			'password' => 'r0117c',
			'consulta' => $consulta_mdb,
			'tipo' => 'SELECT',
			'bd' => 'bodega');
		
		$result = $client->call('ws_mdb', $param);
		$err = $client->getError();
		if ($err) {
			throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.". $err);
		}

		if($result['Codigo']==1){
			$bodegadet=json_decode($result['Adicional1']);
			if (count($bodegadet) > 0) {
				foreach($bodegadet as $row_bod){
					$sBodCliente = $row_bod->bodCli;
	
					if ($sBodCliente != $sIdCliente) {
						$sReferenciasIncorrectas .= (($sReferenciasIncorrectas == '')? '' : $sReferenciasIncorrectas.'<br>');
						$sReferenciasIncorrectas .= "La referencia <strong>" .$sReferencia ."</strong> se encuentra asignada a un cliente diferente.";
					}
				}
			} else {
				$sReferenciasIncorrectas .= (($sReferenciasIncorrectas == '')? '' : $sReferenciasIncorrectas.'<br>');
				$sReferenciasIncorrectas .= "La referencia <strong>" .$sReferencia ."</strong> no se encuentra en el inventario.";
			}
		}
	}

	return $sReferenciasIncorrectas;
}

/* ..:: Obtenemos los datos de las tarifas de honorarios ::.. */
function fcn_get_honorarios_tarifas($sIdCliente) {
	global $cmysqli, $bDebug;

	$aHonorarios = array();

	$consulta = "SELECT tar.id_tarifa, tar.titulo, tar.descripcion, tar.tarifa
				 FROM facturacion.clientes AS facCli INNER JOIN
					  facturacion.tarifas_sb AS tar ON tar.id_inc_cliente=facCli.id_inc_cliente
				 WHERE facCli.id_cliente=".$sIdCliente." AND
					   tar.tipo_tarifa='H' AND 
					   tar.fecha_del IS NULL";
		
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		throw new Exception("fcn_get_honorarios_tarifas() ". $error);
	} else {
		while($row = mysqli_fetch_array($query)){ 
			$id_tarifa = $row["id_tarifa"];

			$bExist = array_search($id_tarifa, array_column($aHonorarios, 'id_tarifa'));
			if (false !== $bExist) {
				if ($row["checked"] == 'true') {
					$aHonorarios[$bExist]['checked'] = 'checked';
				}
			} else {
				$oHonorario = array(
					'remision' => '',
					'id_tarifa' => $id_tarifa,
					'titulo' => $row["titulo"],
					'descripcion' => ((is_null($row["descripcion"])? '' : '('.$row["descripcion"].')')),
					'tarifa' => $row["tarifa"], 
					'checked' => '',
					'referencia' => ''
				);

				array_push($aHonorarios, $oHonorario);
			}
		}
	}

	return $aHonorarios;
}

/* ..:: Obtenemos el tipo de Salida ::.. */
function fcn_get_tipo_salida($nIdCruce, $sTipoTransporte) {
	global $bDebug, $cmysqli;

	$sTipoSalida = '';

	/**************************************************************/

	$consulta = "SELECT COUNT(bodremdet.referencia) AS referencias
				 FROM facturacion.inc_remision AS rem INNER JOIN
		              bodega.remisiondet AS bodremdet ON bodremdet.remision=rem.remision INNER JOIN
		              bodega.tblbod AS bod ON bod.bodReferencia=bodremdet.referencia
				 WHERE rem.id_cruce=".$nIdCruce;

	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		throw new Exception("fcn_get_tipo_salida() ". $error);
	} else {
		$nReferencias = 0;
		while($row = mysqli_fetch_array($query)){ 
			$nReferencias = $row['referencias'];
			break;
		}

		if ($nReferencias > 0) {
			if ($sTipoTransporte == 'T' || $sTipoTransporte == 'P') {
				if ($nReferencias > 1) {
					$sTipoSalida = 'CONSOLIDADA';
				} else {
					$sTipoSalida = 'DIRECTA';
				}

				//Caso de B&G FOODS
				if ($sTipoSalida == 'CONSOLIDADA') {
					if (fcn_get_conteo_fecha_caja_entrada_cruce($nIdCruce) <= 1) {
						$sTipoSalida = 'DIRECTA';
					}
				}
			} else {
				$sTipoSalida = 'CONSOLIDADA';
			}
		} else {
			throw new Exception("No se encontraron referencias para el cruce [".$nIdCruce."]");
		}
	}
	
	return $sTipoSalida;
}

/* ..:: Obtenemos las tarifas de incrementables que no se asignaron para mostrarlos
        al ejecutivo ya que pueden ser agregados cuando esten cargando la 
        mercancia en bodega                                        		            ::.. */
function fcn_get_tarifas_inc_sin_asignar($nIdCruce) {
	global $bDebug, $cmysqli;

	$sTarifasIncSinAsignar = '';

	/**************************************************************/

	$consulta = "SELECT DISTINCT(tarifas.id_tarifa), tarifas.titulo, tarifas.descripcion
				 FROM facturacion.inc_cruces_impo AS cruce INNER JOIN
					  facturacion.inc_remision AS rem ON rem.id_cruce=cruce.id_cruce INNER JOIN
                      facturacion.inc_remisiondet AS remdet ON remdet.remision=rem.remision INNER JOIN
                      facturacion.tarifas_sb AS tarifas ON tarifas.id_inc_cliente=cruce.id_inc_cliente
                 WHERE cruce.id_cruce=".$nIdCruce." AND 
                       tarifas.id_tipo_transporte IS NULL AND
					  (tarifas.es_incrementable=1 OR remdet.tipo='LTL') AND
					   tarifas.id_tarifa NOT IN (SELECT c.id_tarifa
												 FROM facturacion.inc_cruces_impo AS a INNER JOIN
												      facturacion.inc_remision AS b ON b.id_cruce=a.id_cruce INNER JOIN
					                                  facturacion.inc_remisiondet AS c ON c.remision=b.remision
                                                 WHERE a.id_cruce=".$nIdCruce.")";

	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		throw new Exception("fcn_get_tarifas_inc_sin_asignar() ". $error);
	} else {
		while($row = mysqli_fetch_array($query)){ 
			$sTarifasIncSinAsignar .= (($sTarifasIncSinAsignar == '')? '' : '</br>');
			$sTarifasIncSinAsignar .= '<i class="fa fa-caret-right" aria-hidden="true"></i> <strong>'.$row['titulo'].'</strong>';
			
			if (!(is_null($row['descripcion']) || $row['descripcion'] == '')) {
				$sTarifasIncSinAsignar .= ' <small>('.$row['descripcion'].')</small>';
			}
		}
	}
	
	return $sTarifasIncSinAsignar;
}

/* Obtenemos el conteo de las referencias para saber si vinieron en una sola caja y asi convertirla en cruce DIRECTA caso B&G */
function fcn_get_conteo_fecha_caja_entrada($sRemision) {
	global $bDebug, $cmysqli;

	$nTotal = 0;

	/**************************************************************/

	$consulta = "SELECT tblbod.bodfecha, tblbod.bodcaja
				 FROM bodega.remisiondet AS bodRemDet INNER JOIN
					  bodega.tblbod ON tblbod.bodReferencia=bodRemDet.referencia
				 WHERE bodRemDet.remision=".$sRemision."
				 GROUP BY tblbod.bodfecha, tblbod.bodcaja";

	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		throw new Exception("fcn_get_conteo_fecha_caja_entrada() ". $error);
	} else {
		$nTotal = mysqli_num_rows($query);
	}
	
	return $nTotal;
}

/* Obtenemos el conteo de las referencias para saber si vinieron en una sola caja y asi convertirla en cruce DIRECTA caso B&G por cruce */
function fcn_get_conteo_fecha_caja_entrada_cruce($nIdCruce) {
	global $bDebug, $cmysqli;

	$nTotal = 0;

	/**************************************************************/

	$consulta = "SELECT tblbod.bodfecha, tblbod.bodcaja
				 FROM facturacion.inc_remision AS facRem INNER JOIN
					  bodega.remisiondet AS bodRemDet ON bodRemDet.remision=facRem.remision INNER JOIN
					  bodega.tblbod ON tblbod.bodReferencia=bodRemDet.referencia
				 WHERE facRem.id_cruce=".$nIdCruce."
				 GROUP BY tblbod.bodfecha, tblbod.bodcaja";

	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		throw new Exception("fcn_get_conteo_fecha_caja_entrada_cruce() ". $error);
	} else {
		$nTotal = mysqli_num_rows($query);
	}
	
	return $nTotal;
}