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
$table = '';

// Table's primary key
$primaryKey = 'factura';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
	array( 'db' => 'factura',     'dt' => 'factura' ),
	array( 'db' => 'fecha_factura',     'dt' => 'fecha_factura' ),
	array( 'db' => 'trafico',     'dt' => 'trafico' ),
	array( 'db' => 'ctas_americanas',     'dt' => 'ctas_americanas' ),
	array( 'db' => 'pedimento',     'dt' => 'pedimento' ),
	array( 'db' => 'cve_pedimento',     'dt' => 'cve_pedimento' ),
	array( 'db' => 'no_cta_gastos',     'dt' => 'no_cta_gastos' ),
	array( 'db' => 'fecha_cta_gastos',     'dt' => 'fecha_cta_gastos' ),
	array( 'db' => 'cargos_mexicanos',     'dt' => 'cargos_mexicanos' ),
	array( 'db' => 'total_facturas',     'dt' => 'total_facturas' )
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
$num_pedi=$_POST['pedimento'];
$aduana=$_POST['aduana'];
$patente=$_POST['patente'];
//$num_pedi='6908066';
/*$cliente=$_POST['cliente'];
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
}*/
// Main query to actually get the data
$baseSql = "
	SELECT
		fac_ped.NUM_FACT AS FACTURA,
		DATE_FORMAT(
			fac_ped.FEC_FACT,
			'%d/%m/%Y'
		) FECHA_FACTURA,
		remd.referencia AS TRAFICO,
		fac_ame.RefNumber as CTAS_AMERICANAS,
		remd.pedimento AS PEDIMENTO,
		ped.CVE_PEDI AS CVE_PEDIMENTO,
		fac_mex.no_mov AS NO_CTA_GASTOS,
		DATE_FORMAT(fac_mex.fecha, '%d/%m/%Y') AS FECHA_CTA_GASTOS,
		fac_mex.ttotal AS CARGOS_MEXICANOS,
		cf.tfac as TOTAL_FACTURAS
	FROM
		casa.saaio_pedime AS ped
	INNER JOIN casa.saaio_factur AS fac_ped ON ped.NUM_REFE = fac_ped.NUM_REFE
	LEFT JOIN bodega.remisiondet AS remd ON concat(
		ped.PAT_AGEN,
		'-',
		ped.NUM_PEDI
	) = remd.pedimento
	INNER JOIN revision_general AS revg ON remd.referencia = revg.referencia
	AND fac_ped.NUM_FACT = revg.factura
	LEFT JOIN sab07web.aacgmex AS fac_mex ON ped.ADU_DESP = fac_mex.aduana
	AND ped.PAT_AGEN = fac_mex.patente
	AND ped.NUM_PEDI = fac_mex.pedimento
	LEFT JOIN sab07web.pedimentos AS ped_fac ON ped.ADU_DESP = ped_fac.aduana
	AND ped.PAT_AGEN = ped_fac.patente
	AND ped.NUM_PEDI = ped_fac.pedimento
	LEFT JOIN qbdelbravo.qb_invoice_invoiceline AS fac_amed ON fac_amed.Descrip LIKE concat('%', remd.referencia, '%')
	LEFT JOIN qbdelbravo.qb_invoice AS fac_ame on fac_amed.Invoice_TxnID=fac_ame.TxnID
	LEFT JOIN (
		SELECT count(f.num_fact) as tfac,f.num_refe
		from casa.saaio_factur as f
		group by f.num_refe
	) as cf on ped.num_refe=cf.num_refe
	WHERE
		ped.NUM_PEDI = '$num_pedi' and ped.PAT_AGEN='$patente' and ped.ADU_DESP='$aduana'
	GROUP BY
		fac_ped.num_fact
	ORDER BY
		NUM_FACT";

require( 'ssp.class.php' );

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);

