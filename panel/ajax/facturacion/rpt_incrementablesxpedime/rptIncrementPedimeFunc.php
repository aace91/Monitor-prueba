<?php
include_once('./../../../../checklogin.php');
include('./../../../../connect_dbsql.php');

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'pruebas/') !== false)? true : false);

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) { 
	$action = $_REQUEST['action'];
	if($loggedIn == false){
		switch ($action) {
			case 'table_pedimentos':
			case 'table_detalle_incrementable':
			case 'table_detalle_cruce':
				exit(json_encode(array("error" => '500')));
				break;
			default:
				exit('500');
		}
	} else {
		switch ($action) {
			case 'table_pedimentos' : $respuesta = fcn_table_pedimentos();
				echo json_encode($respuesta);
				break;
			
			case 'table_detalle_incrementable' : $respuesta = fcn_table_detalle_incrementable();
				echo json_encode($respuesta);
				break;

			case 'table_detalle_cruce' : $respuesta = fcn_table_detalle_cruce();
				echo json_encode($respuesta);
				break;
		}
	}
} else {
	$respuesta['Codigo']=-1;
	$respuesta['Mensaje']='No se recibio metodo!!!';
	$respuesta['Error'] = '';
	echo json_encode($respuesta);
}

/*************************************************************************************************/
/* METODOS                                                                                       */
/*************************************************************************************************/

function fcn_table_pedimentos(){
	global $_POST, $bDebug, $baseSql, $mysqluser, $mysqlpass, $mysqldb, $mysqlserver, $id;
	
	$id_cliente = $_POST['id_cliente'];
	
	$table = 'remisiondet';
	$primaryKey = 'pedimento';

	$columns = array(
		array( 'db' => 'pedimento',     'dt' => 'pedimento' ),
		array( 'db' => 'numero_cruces', 'dt' => 'numero_cruces' )
	);

	$sql_details = array(
		'user' => $mysqluser,
		'pass' => $mysqlpass,
		'db'   => $mysqldb,
		'host' => $mysqlserver
	);

	$baseSql = "SELECT bodRem.pedimento, facRemDet.referencia, COUNT(DISTINCT facCruces.id_cruce) AS numero_cruces
				FROM facturacion.inc_remisiondet AS facRemDet INNER JOIN
					 bodega.remisiondet AS bodRem ON bodRem.remision=facRemDet.remision AND
													 bodRem.referencia=facRemDet.referencia INNER JOIN
					 facturacion.inc_remision AS facRem ON facRem.remision=facRemDet.remision INNER JOIN 
					 facturacion.inc_cruces_impo AS facCruces ON facCruces.id_cruce=facRem.id_cruce INNER JOIN
					 facturacion.clientes AS facCli ON facCli.id_inc_cliente=facCruces.id_inc_cliente
				WHERE facCli.id_cliente=".$id_cliente."
				GROUP BY bodRem.pedimento
				ORDER BY bodRem.pedimento";

	require('./../../../ssp.class.php');
	return	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns );
}

function fcn_table_detalle_incrementable(){
	global $_POST, $bDebug, $baseSql, $mysqluser, $mysqlpass, $mysqldb, $mysqlserver, $id;
	
	$id_cliente = $_POST['id_cliente'];
	$pedimento = $_POST['pedimento'];
	
	$table = 'remisiondet';
	$primaryKey = 'id_cruce';

	$columns = array(
		array( 'db' => 'id_cruce',     'dt' => 'id_cruce' ),
		array( 'db' => 'total',        'dt' => 'total' ),
		array( 'db' => 'caja',         'dt' => 'caja' ),
		array( 'db' => 'fecha_salida', 'dt' => 'fecha_salida')
	);

	$sql_details = array(
		'user' => $mysqluser,
		'pass' => $mysqlpass,
		'db'   => $mysqldb,
		'host' => $mysqlserver
	);

	$baseSql = "SELECT facRem.id_cruce, facRemDet.remision, bodRemDet.pedimento, facRemDet.referencia, 
					   facRemDet.id_tarifa, TRUNCATE(SUM(facRemDet.cantidad * facRemDet.tarifa), 2) AS total,
					   CONCAT(DATE_FORMAT(bodSalida.fecha, '%d/%m/%Y' ), ' ', bodSalida.hora) AS fecha_salida,
					   bodSalida.caja
				FROM facturacion.inc_remisiondet AS facRemDet INNER JOIN
					 bodega.remisiondet AS bodRemDet ON bodRemDet.remision=facRemDet.remision AND
														bodRemDet.referencia=facRemDet.referencia INNER JOIN
					 facturacion.inc_remision AS facRem ON facRem.remision=facRemDet.remision INNER JOIN 
					 facturacion.inc_cruces_impo AS facCruces ON facCruces.id_cruce=facRem.id_cruce INNER JOIN
					 facturacion.clientes AS facCli ON facCli.id_inc_cliente=facCruces.id_inc_cliente LEFT JOIN
					 facturacion.tarifas_sb AS facTarifa ON facTarifa.id_tarifa=facRemDet.id_tarifa  INNER JOIN
					 bodega.datos_generales_salidas AS bodSalida ON bodSalida.remision=facRemDet.remision
				WHERE facCli.id_cliente=".$id_cliente." AND
					  bodRemDet.pedimento='".$pedimento."' AND
			         (facTarifa.es_incrementable=1 OR facRemDet.tipo='LTL')
				GROUP BY facRem.id_cruce
				ORDER BY bodRemDet.pedimento";

	require('./../../../ssp.class.php');
	return	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns );
}

function fcn_table_detalle_cruce(){
	global $_POST, $bDebug, $baseSql, $mysqluser, $mysqlpass, $mysqldb, $mysqlserver, $id;
	
	$id_cruce = $_POST['id_cruce'];
	$pedimento = $_POST['pedimento'];
	
	$table = 'inc_remision';
	$primaryKey = 'remision';

	$columns = array(
		array( 'db' => 'remision',  'dt' => 'remision' ),
		array( 'db' => 'id_cruce',  'dt' => 'id_cruce' ),
		array( 'db' => 'pedimento', 'dt' => 'pedimento' ),
		array( 'db' => 'aduana',    'dt' => 'tc' , 'formatter' => function( $aduana, $row ) {
			if ($aduana == NULL || $aduana == 'ADUANA DESCONOCIDA'){
				return (($aduana == NULL)? '' : $aduana);
			} else {
				include('./../../../../connect_casa.php');
				
				$pedimentopat = $row['pedimento'];
			    $patente = substr($pedimentopat, 0, 4);
				$numpedi = substr($pedimentopat, -7);

				$query = "SELECT tip_camb
                          FROM SAAIO_PEDIME 
					      WHERE num_pedi='".$numpedi."' and 
							    pat_agen='".$patente."' and 
							    adu_desp='".$aduana."'";
			
				$result = odbc_exec ($odbccasa, $query);
				if ($result != false){
					return odbc_result($result,"tip_camb");					
				} else {
					return 'Error al realizar la consulta a la base de datos de pedimentos: '.$query;
				}
			}
		} ),
		array( 'db' => 'no_partes',    'dt' => 'no_partes'),
		array( 'db' => 'titulo',    'dt' => 'titulo' ),
		array( 'db' => 'cantidad',  'dt' => 'cantidad' ),
		array( 'db' => 'tarifa',    'dt' => 'tarifa'),
		array( 'db' => 'total',     'dt' => 'total')
	);

	$sql_details = array(
		'user' => $mysqluser,
		'pass' => $mysqlpass,
		'db'   => $mysqldb,
		'host' => $mysqlserver
	);

	$baseSql = "SELECT facRem.remision, facRem.id_cruce, bodremdet.pedimento, IF(facRemDet.tipo = 'LTL', 'Honorarios', facTar.titulo) AS titulo, 
					   SUM(facRemDet.cantidad) AS cantidad, TRUNCATE(facRemDet.tarifa, 2) AS tarifa, 
				       TRUNCATE((SUM(facRemDet.cantidad) * facRemDet.tarifa), 2) AS total,
					   facRemDet.tipo, 
				       (CASE 
					       WHEN bodRem.aduana = 'COLOMBIA' THEN 800
					       WHEN bodRem.aduana = 'LAREDO' THEN 240
					       ELSE 'ADUANA DESCONOCIDA'
				        END) AS aduana, 
				       IF(facRemDet.tipo = 'LTL', facRemDet.id_ltl, facRemDet.id_tarifa) AS id_tarifa,
					  (SELECT GROUP_CONCAT(DISTINCT tRevDet.noparte SEPARATOR ', ')
					   FROM bodega.remisiondet AS tRemDet INNER JOIN 
						    bodega.detalle_revision AS tRevDet ON tRevDet.referencia=tRemDet.referencia
					   WHERE remision=facRem.remision) AS no_partes
			    FROM facturacion.inc_remision AS facRem INNER JOIN
				     facturacion.inc_remisiondet AS facRemDet ON facRemDet.remision=facRem.remision INNER JOIN
				     bodega.remisiongral AS bodRem ON bodRem.remision=facRem.remision INNER JOIN
			      	 bodega.remisiondet AS bodRemDet ON bodRemDet.remision=facRem.remision AND 
													    bodRemDet.referencia=facRemDet.referencia LEFT JOIN
				     facturacion.tarifas_sb AS facTar ON facTar.id_tarifa=facRemDet.id_tarifa
				WHERE facRem.id_cruce=".$id_cruce." AND 
				     (facTar.es_incrementable=1 OR facRemDet.tipo='LTL') AND
					  bodRemDet.pedimento='".$pedimento."'
				GROUP BY facRemDet.tipo, id_tarifa
				UNION
				SELECT facRem.remision, facRem.id_cruce, bodRemDet.pedimento, 'Flete' AS titulo, 
					   '1' AS cantidad, 
					   TRUNCATE(bodOrdBG.flete, 2) AS tarifa,
					   TRUNCATE(bodOrdBG.flete, 2) AS total,
					   'FLETE' AS tipo, '' AS aduana, '' AS id_tarifa, '' AS no_partes
				FROM facturacion.inc_remision AS facRem INNER JOIN
					 bodega.remisiondet AS bodRemDet ON bodRemDet.remision=facRem.remision INNER JOIN
					 bodega.`ordenes_b&g` AS bodOrdBG ON bodOrdBG.referencia=bodRemDet.referencia INNER JOIN
					 bodega.tblbod ON tblbod.bodReferencia=bodOrdBG.referencia
				WHERE facRem.id_cruce=".$id_cruce." AND
					  bodRemDet.pedimento='".$pedimento."'
				GROUP BY tblbod.bodfecha, tblbod.bodcaja
				ORDER BY pedimento DESC";
				
				/*
	SELECT rem.remision, rem.id_cruce, bodremdet.pedimento, IF(tarifas.titulo IS NULL, 'Honorarios', tarifas.titulo) AS titulo, 
					   remdet.cantidad, TRUNCATE(remdet.tarifa, 2) AS tarifa, TRUNCATE((remdet.cantidad * remdet.tarifa), 2) AS total, 10 AS tc
				FROM facturacion.inc_remision AS rem INNER JOIN
					 facturacion.inc_remisiondet AS remdet ON remdet.remision=rem.remision INNER JOIN
					 bodega.remisiondet AS bodremdet ON bodremdet.remision=rem.remision AND 
					 bodremdet.referencia=remdet.referencia LEFT JOIN
					 facturacion.tarifas_sb AS tarifas ON tarifas.id_tarifa=remdet.id_tarifa
				WHERE rem.id_cruce=$id_cruce AND 
					 (tarifas.es_incrementable=1 OR remdet.tipo='LTL') AND
				      bodremdet.pedimento='".$pedimento."'
				ORDER BY bodremdet.pedimento DESC*/

	require('./../../../ssp.class.php');
	return	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns );
}

/*************************************************************************************************/
/* FUNCIONES                                                                                     */
/*************************************************************************************************/

function fcn_get_tipo_cobro($id_cliente) {
	global $bDebug, $cmysqli;
	
	$consulta = "SELECT tipo_cobro
				 FROM facturacion.clientes
				 WHERE id_cliente=".$id_cliente;

	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		throw new Exception("fcn_get_tipo_cobro() ". $error);
	}
	
	if (mysqli_num_rows($query) == 0) {
		throw new Exception('El cliente no existe en el sistema de facturacion.');
	} else {
		while($row = mysqli_fetch_array($query)){ 
			return $row['tipo_cobro'];
		}
	}
}