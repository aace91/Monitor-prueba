<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
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
$table = 'saaio_pedime';

// Table's primary key
$primaryKey = 'referencia';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
	array( 'db' => 'referencia',     'dt' => 'referencia' ),
	array( 'db' => 'importador',     'dt' => 'importador' ),
	array( 'db' => 'orden_compra',     'dt' => 'orden_compra' ),
	array( 'db' => 'proveedor',     'dt' => 'proveedor' ),
	array( 'db' => 'facturas',     'dt' => 'facturas' ),
	array( 'db' => 'valor',     'dt' => 'valor' ),
	array( 'db' => 'origen',     'dt' => 'origen' ),
	array( 'db' => 'incoterms',     'dt' => 'incoterms' ),
	array( 'db' => 'comentarios',     'dt' => 'comentarios' ),
	array( 'db' => 'status',     'dt' => 'status' ),
	array( 'db' => 'linea_ame',     'dt' => 'linea_ame' ),
	array( 'db' => 'bol',     'dt' => 'bol' ),
	array( 'db' => 'no_bultos',     'dt' => 'no_bultos' ),
	array( 'db' => 'peso',     'dt' => 'peso' ),
	array( 'db' => 'pedimento',     'dt' => 'pedimento' ),
	array( 'db' => 'linea_mex',     'dt' => 'linea_mex' ),
	array( 'db' => 'no_unidad',     'dt' => 'no_unidad' ),
	array( 'db' => 'fec_orig',     'dt' => 'fec_orig' ),
	array( 'db' => 'fec_adu',     'dt' => 'fec_adu' ),
	array( 'db' => 'fec_info',     'dt' => 'fec_info' ),
	array( 'db' => 'fec_desp',     'dt' => 'fec_desp' ),
	array( 'db' => 'fec_entre',     'dt' => 'fec_entre' ),
	array( 'db' => 'remision',     'dt' => 'remision' ),
	array( 'db' => 'destino',     'dt' => 'destino' )
);

// SQL server connection information
$sql_details = array(
	'user' => $mysqluser,
	'pass' => $mysqlpass,
	'db'   => $mysqldb,
	'host' => $mysqlserver
);


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
$cliente=$_POST['cliente'];
if ($_POST['fechaini']!='')
	$fechaini=substr($_POST['fechaini'],6,4).substr($_POST['fechaini'],3,2).substr($_POST['fechaini'],0,2);
if ($_POST['fechafin']!='')
	$fechafin=substr($_POST['fechafin'],6,4).substr($_POST['fechafin'],3,2).substr($_POST['fechafin'],0,2);
if($fechaini!='' and $fechafin!=''){
	$fechas.=" and date_format(ped.fec_pago,'%Y%m%d')>='".$fechaini."' and date_format(ped.fec_pago,'%Y%m%d')<='".$fechafin."' ";
}else{
	if($fechaini!=''){
		$fechas.=" and date_format(ped.fec_pago,'%Y%m%d')>='".$fechaini."' ";
	}elseif($fechafin!=''){
		$fechas.=" and date_format(ped.fec_pago,'%Y%m%d')<='".$fechafin."' ";
	}
}
// Main query to actually get the data
$baseSql = "
	SELECT
		ped.NUM_REFE AS Referencia,
		cli.NOM_IMP AS Importador,
		ped.aut_obse orden_compra,
		pro.proNom AS Proveedor,
		GROUP_CONCAT(fac.NUM_FACT2 SEPARATOR ', ') AS Facturas,
		sum(fac.VAL_DLLS) AS Valor,
		frac.pai_orig AS Origen,
		GROUP_CONCAT(fac.ICO_FACT SEPARATOR ', ') AS Incoterms,
		'' AS Comentarios,
		'' AS STATUS,
		fle.flenombre AS linea_ame,
		bod.bodbno AS BOL,
		ped.CAN_BULT AS no_bultos,
		ped.pes_brut AS peso,
		ped.NUM_PEDI AS Pedimento,
		'' as destino,
		remg.linea linea_mex,
		cont.num_cont no_unidad,
		DATE_FORMAT(bod.bodfecha, '%d/%m/%Y') AS fec_orig,
		DATE_FORMAT(ped.FEC_PAGO, '%d/%m/%Y') AS fec_adu,
		DATE_FORMAT(ped.FEC_PAGO, '%d/%m/%Y') AS fec_info,
		DATE_FORMAT(ped.FEC_PAGO, '%d/%m/%Y') AS fec_desp,
		'' AS fec_entre,
		remd.remision AS Remision
	FROM
		casa.saaio_pedime AS ped
	LEFT JOIN casa.ctrac_client AS cli ON ped.cve_impo = cli.cve_imp
	LEFT JOIN casa.saaio_factur AS fac ON ped.num_refe = fac.num_refe
	LEFT JOIN casa.saaio_fracci AS frac ON ped.num_refe = frac.num_refe
	LEFT JOIN casa.saaio_conten AS cont ON ped.num_refe = cont.num_refe
	LEFT JOIN bodega.remisiondet AS remd ON CONCAT(
		ped.PAT_AGEN,
		'-',
		ped.NUM_PEDI
	) = remd.pedimento
	LEFT JOIN bodega.remisiongral AS remg ON remd.remision = remg.remision
	LEFT JOIN bodega.tblbod AS bod ON remd.referencia = bod.bodreferencia
	LEFT JOIN bodega.procli AS pro ON bod.bodprocli = pro.proveedor_id
	LEFT JOIN bodega.tblflet AS fle ON bod.bodfle = fle.fleclave
	WHERE
		ped.CVE_IMPO = '$cliente'
	AND ped.FIR_PAGO IS NOT NULL
	$fechas
	GROUP BY
		fac.num_refe";

require( 'ssp.class.php' );

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);

