<?php
include_once('./../../../checklogin.php');
include('./../../../url_archivos.php');
include('./../../../connect_casa.php');

if($loggedIn == false){ 
	echo json_encode( array("error" => 'La sesion del usuario ha finalizado. Por favor, inicie nuevamente.'));
	exit();
}
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
$table = 'permisos_pedimentos';

// Table's primary key
$primaryKey = 'id_permiso';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
	array( 'db' => 'id_permiso',     'dt' => 'id_permiso' ),
	array( 'db' => 'numero_permiso',     'dt' => 'numero_permiso' ),
	array( 'db' => 'vigencia',     'dt' => 'vigencia' ),
	array( 'db' => 'cliente',     'dt' => 'cliente' ),
	array( 'db' => 'valor_dlls_total',     'dt' => 'valor_dlls_total', 'formatter' => function( $d, $row ) {return '$'.number_format ($d,2);}),
	array( 'db' => 'cantidad_total',     'dt' => 'cantidad_total', 'formatter' => function( $d, $row ) {return number_format ($d,0);} ),
	array( 'db' => 'valor_dlls_delbravo',     'dt' => 'valor_dlls_delbravo' , 'formatter' => function( $d, $row ) {return '$'.number_format ($d,2);}),
	array( 'db' => 'cantidad_delbravo',     'dt' => 'cantidad_delbravo', 'formatter' => function( $d, $row ) {return number_format ($d,0);}),
	array( 'db' => 'numero_permiso',     'dt' => 'valor_utilizado_delbravo', 'formatter' => function( $d, $row ) {
		global $odbccasa;
		$Valor_Dlls = 0;
		$qCasa = "SELECT b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
					FROM (  SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
								FROM SAAIO_PERPAR a
									INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
								WHERE a.NUM_PERM = '".$d."' AND c.FIR_PAGO IS NULL
							UNION ALL
							SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI													
							FROM SAAIO_PERMIS a
								INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
							WHERE a.NUM_PERM = '".$d."' AND c.FIR_PAGO IS NOT NULL) b
					GROUP BY (b.NUM_PERM)";
	
		$resped = odbc_exec ($odbccasa, $qCasa);
		if ($resped == false){
			$mensaje = "Error al consultar el valor dolares del permiso utilizado en pedimentos. BD.CASA.".odbc_error();
			echo json_encode( array("error" => $mensaje));
			exit(0);
		}else{
			while(odbc_fetch_row($resped)){
				$Valor_Dlls = odbc_result($resped,"VAL_DLLS");
			}
		}
		return '$'.number_format ($Valor_Dlls,2);
	} ),
	array( 'db' => 'numero_permiso',     'dt' => 'cantidad_utilizada_delbravo', 'formatter' => function( $d, $row ) {
		global $odbccasa;
		$Cantidad_Tarifa = 0;
		$qCasa = "SELECT b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
					FROM (  SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
								FROM SAAIO_PERPAR a
									INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
								WHERE a.NUM_PERM = '".$d."' AND c.FIR_PAGO IS NULL
							UNION ALL
							SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI													
							FROM SAAIO_PERMIS a
								INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
							WHERE a.NUM_PERM = '".$d."' AND c.FIR_PAGO IS NOT NULL) b
					GROUP BY (b.NUM_PERM)";
	
		$resped = odbc_exec ($odbccasa, $qCasa);
		if ($resped == false){
			$mensaje = "Error al consultar el valor kilos del permiso utilizado en pedimentos. BD.CASA.".odbc_error();
			echo json_encode( array("error" => $mensaje));
			exit(0);
		}else{
			while(odbc_fetch_row($resped)){
				$Cantidad_Tarifa = odbc_result($resped,"CAN_TARI");
			}
		}
		return number_format ($Cantidad_Tarifa,0);
	} ),
	array( 'db' => 'numero_permiso',     'dt' => 'saldo_dolares_delbravo', 'formatter' => function( $d, $row ) {
		global $odbccasa;
		$Valor_Dlls = 0;
		$qCasa = "SELECT b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
					FROM (  SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
								FROM SAAIO_PERPAR a
									INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
								WHERE a.NUM_PERM = '".$d."' AND c.FIR_PAGO IS NULL
							UNION ALL
							SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI													
							FROM SAAIO_PERMIS a
								INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
							WHERE a.NUM_PERM = '".$d."' AND c.FIR_PAGO IS NOT NULL) b
					GROUP BY (b.NUM_PERM)";
	
		$resped = odbc_exec ($odbccasa, $qCasa);
		if ($resped == false){
			$mensaje = "Error al consultar el valor dolares del permiso utilizado en pedimentos. BD.CASA.".odbc_error();
			echo json_encode( array("error" => $mensaje));
			exit(0);
		}else{
			while(odbc_fetch_row($resped)){
				$Valor_Dlls = odbc_result($resped,"VAL_DLLS");
			}
		}
		$saldo_dlls = ($row['valor_dlls_delbravo'] - $Valor_Dlls);
		return '$'.number_format ($saldo_dlls,2);
	}),
	array( 'db' => 'numero_permiso',     'dt' => 'saldo_kilos_delbravo', 'formatter' => function( $d, $row ) {
		global $odbccasa;
		$Cantidad_Tarifa = 0;
		$qCasa = "SELECT b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
					FROM (  SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
								FROM SAAIO_PERPAR a
									INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
								WHERE a.NUM_PERM = '".$d."' AND c.FIR_PAGO IS NULL
							UNION ALL
							SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI													
							FROM SAAIO_PERMIS a
								INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
							WHERE a.NUM_PERM = '".$d."' AND c.FIR_PAGO IS NOT NULL) b
					GROUP BY (b.NUM_PERM)";
	
		$resped = odbc_exec ($odbccasa, $qCasa);
		if ($resped == false){
			$mensaje = "Error al consultar el valor kilos del permiso utilizado en pedimentos. BD.CASA.".odbc_error();
			echo json_encode( array("error" => $mensaje));
			exit(0);
		}else{
			while(odbc_fetch_row($resped)){
				$Cantidad_Tarifa = odbc_result($resped,"CAN_TARI");
			}
		}
		$saldo = $row['cantidad_delbravo'] - $Cantidad_Tarifa;
		return number_format ($saldo,0);
	}),
	array( 'db' => 'documento',     'dt' => 'documento' )
);

// SQL server connection information
$sql_details = array(
	'user' => $mysqluser,
	'pass' => $mysqlpass,
	'db'   => $mysqldb,
	'host' => $mysqlserver
);

// Main query to actually get the data
$baseSql = "
			SELECT p.id_permiso, p.numero_permiso,c.nombre as cliente, 
					CONCAT(DATE_FORMAT(p.fecha_vigencia_ini,\"%d/%m/%Y\"),\" a \",DATE_FORMAT(p.fecha_vigencia_fin,\"%d/%m/%Y\")) AS vigencia,
					p.valor_dlls_total,p.cantidad_total,
					p.valor_dlls_delbravo,p.cantidad_delbravo,
					if(p.archivo_permiso IS NULL, '', CONCAT('".$URL_archivos_permisos."',p.archivo_permiso)) as documento
			FROM permisos_pedimentos p
				LEFT JOIN bodega.geocel_clientes_expo c ON
					p.id_cliente = c.f_numcli";

require( '../../ssp.class.php' );
echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);







