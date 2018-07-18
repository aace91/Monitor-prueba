<?php
include_once('./../../../checklogin.php');
include('./../../../connect_casa.php');
include('./../../../url_archivos.php');
if($loggedIn == false){ 
	echo json_encode( array("error" => 'La sesion del usuario ha finalizado. Por favor, inicie nuevamente.'));
	exit();
}
//Revisa archivos creados temporalmente y los elimina
	eliminar_archivos_viejos();
/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

// DB table to use
$table = 'cruces_expo';

// Table's primary key
$primaryKey = 'id_cruce';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
	array('db' => 'id_cruce',
			'dt' => 'DT_RowId',
			'formatter' => function( $d, $row ) {
				return $d;
        } ),
	array( 'db' => 'numcliente',     'dt' => 'numcliente' ),
	array( 'db' => 'fecha_registro',     'dt' => 'fecha_registro'),
	array( 'db' => 'cliente',     'dt' => 'cliente' ),
	array( 'db' => 'linea_tans',     'dt' => 'linea_tans' ),
	array( 'db' => 'aduana',     'dt' => 'aduana' , 'formatter' => function( $d, $row ) {
		switch($d){
			case '800':
				$d = '800 - COLOMBIA, NL. MEXICO.';
				break;
			case '240':
				$d = '240 - NUEVO LAREDO, TAMPS. MEXICO.';
				break;
		}
		return $d;
	}),
	array( 'db' => 'po_number',     'dt' => 'po_number' ),
	array( 'db' => 'tiposalida',     'dt' => 'tiposalida' ),
	array( 'db' => 'caja',     'dt' => 'caja' ),
	array( 'db' => 'transfer',     'dt' => 'transfer' ),
	array( 'db' => 'entrega',     'dt' => 'entrega' ),
	array( 'db' => 'facturas',     'dt' => 'facturas' ),
	array( 'db' => 'habilitado_editar',     'dt' => 'habilitado_editar' ),
	array( 'db' => 'referencias',     'dt' => 'referencias'),
	array( 'db' => 'referencias',     'dt' => 'pedimento' , 'formatter' => function( $d, $row ) {
		$sPed = '';
		if($d != ''){
			$aReferencias = explode(',',$d);
			for($i = 0; $i<count($aReferencias); $i++){
				global $odbccasa;
				
				$qCasa = "SELECT a.ADU_DESP, a.PAT_AGEN, a.NUM_PEDI
							FROM SAAIO_PEDIME a
							WHERE a.NUM_REFE='".$aReferencias[$i]."'
							GROUP BY a.ADU_DESP, a.PAT_AGEN, a.NUM_PEDI";
				$resped = odbc_exec ($odbccasa, $qCasa);
				if ($resped == false){
					$mensaje = "Error al consultar pedimento de las referencias. DataTableCol:Pedimento/BD.CASA.".odbc_error().$qCasa ;
					echo json_encode( array("error" => $mensaje));
					exit(0);
				}else{
					$nItem = 0;
					while(odbc_fetch_row($resped)){
						if($nItem != 0){$sPed .= '|';}						
						$Aduana = odbc_result($resped,"ADU_DESP");
						$Patente = odbc_result($resped,"PAT_AGEN");
						$Pedimento = odbc_result($resped,"NUM_PEDI");
						
						$sPed .= $Aduana.'-'.$Patente.'-'.$Pedimento;
					}
				}
			}
			return $sPed;
		}else{
			return $sPed;
		}
	}),
	array( 'db' => 'estado',     'dt' => 'estado'),
	array( 'db' => 'salidas',     'dt' => 'salidas' )
);

// SQL server connection information
$sql_details = array(
	'user' => $mysqluser,
	'pass' => $mysqlpass,
	'db'   => $mysqldb,
	'host' => $mysqlserver,
	'dns_casa' => $dsn,
	'user_ldo' => $mysqluser_sab07,
	'pass_ldo' => $mysqlpass_sab07,
	'db_ldo'   => $mysqldb_sab07,
	'host_ldo' => $mysqlserver_sab07
);

$cliente = $_POST['cliente'];
$estado = $_POST['estado'];

$sSql = "";
if($cliente != 'todo'){
	$sSql = " WHERE c.numcliente = '".$cliente."'";
}
$sSqlHav = '';
if($estado != 'todo'){
	$sSqlHav = " HAVING GROUP_CONCAT(DISTINCT fe.SALIDA_NUMERO)IS ".($estado == 'prc' || $estado == 'cum' ? 'NOT' : '')." NULL";
}

// Main query to actually get the data
$baseSql = "SELECT c.id_cruce,DATE_FORMAT(c.fecha_registro, '%d/%m/%Y') as fecha_registro,lt.Nombre as linea_tans,c.aduana,
				IFNULL(c.tiposalida,GROUP_CONCAT(DISTINCT cd.tiposalida)) as tiposalida,
				IFNULL(c.caja,GROUP_CONCAT(DISTINCT cd.caja)) as caja,
				te.nombretransfer as transfer,
				c.numcliente,ce.cnombre as cliente,
				CONCAT(c.nombreentrega,'-',c.direntrega) as entrega,
				c.habilitado_editar,c.po_number,
				GROUP_CONCAT(DISTINCT cd.numero_factura) as facturas,
				IF( GROUP_CONCAT(DISTINCT fe.SALIDA_NUMERO) IS NULL, '', GROUP_CONCAT(DISTINCT fe.SALIDA_NUMERO)) as salidas,
				IF( GROUP_CONCAT(DISTINCT fe.SALIDA_NUMERO) IS NULL, '', GROUP_CONCAT(DISTINCT CONCAT(cd.referencia,'|',cd.cons_fact))) as estado,
				IFNULL(GROUP_CONCAT(DISTINCT cd.referencia),'') as referencias
			FROM cruces_expo c
				INNER JOIN lineast lt ON
					c.numlinea = lt.numlinea
				LEFT JOIN transfers_expo te ON
					c.notransfer = te.notransfer
				LEFT JOIN cruces_expo_detalle cd ON
					c.id_cruce = cd.id_cruce
				INNER JOIN cltes_expo ce ON
					c.numcliente = ce.gcliente
				LEFT JOIN facturas_expo fe ON
					cd.referencia = fe.REFERENCIA AND
					cd.cons_fact = fe.CONS_FACT_PED
			".$sSql."
			GROUP BY c.id_cruce ".$sSqlHav;
//error_log($baseSql);
require( '../../ssp.class.cruces.php' );
echo json_encode(SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns ));

/* ********************************************************************************************
	FUNCION QUE LIMPIA DIRECTORIO DE ARCHIVOS TEMPORALES
******************************************************************************************** */
function eliminar_archivos_viejos(){
	global $dir_archivos_temp_cruces;
	if (is_dir($dir_archivos_temp_cruces))
	{
		$fileSystemIterator = new FilesystemIterator($dir_archivos_temp_cruces);
		$now = time();
		foreach ($fileSystemIterator as $file) {
			//3600 segundos que equivale a 1 hora
			if ($now - $file->getCTime() >= 3600) {
				if(strpos($dir_archivos_temp_cruces.$file->getFilename(),'.pdf')){
					unlink($dir_archivos_temp_cruces.$file->getFilename());
				}
			}
		}
	}
}



