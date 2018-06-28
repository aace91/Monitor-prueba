<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
include('./../connect_casa.php');
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
$table = 'aacgmex';

// Table's primary key
$primaryKey = 'trafico';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
	array( 'db' => 'no_mov',  'dt' => 'no_mov' ),
	array( 'db' => 'cancelada',   'dt' => 'cancelada', 'formatter' => function( $d, $row ) {
			if ($d=='0'){
				$estatus='VIGENTE';
			}elseif($d=='1'){
				$estatus='CANCELADA';
			}
			return $estatus;
		} ),
	array( 'db' => 'trafico',  'dt' => 'trafico' ),
	array( 'db' => 'fecha',   'dt' => 'fecha', 'formatter' => function( $d, $row ) {
			return date( 'd/m/Y', strtotime($d));
		} ),
	array( 'db' => 'subtotal',     'dt' => 'subtotal' ),
	array( 'db' => 'aduana',     'dt' => 'aduana' ),
	array( 'db' => 'patente',     'dt' => 'patente' ),
	array( 'db' => 'trafico',  'dt' => 'anexos' , 'formatter' => function( $d, $row ) {
		include ('./../url_archivos.php');
		$trafico=str_replace('/','_',rtrim($d));
		if (file_exists($dir_server_web.'cfd\\anexos\\'.$trafico.'.pdf')){
			return '<a href="../../anexos/'.$trafico.'.pdf" target=_blank>Ver</a> ';
		}
		else{
			$pos = strrpos($trafico, "-");
			if ($pos==false){
				$ar1=substr($trafico,0,2).'-'.substr($trafico,2);
			}
			else{
				$ar1=str_replace('-','',$trafico);
			}
			if (file_exists('D:\cfd\anexos\\'.$ar1.'.pdf')){
				return '<a href="../../anexos/'.rtrim($ar1).'.pdf" target=_blank>Ver</a> ';
			}
			else{
				return 'No disponible';
			}
		} 
		
	}),
	array( 'db' => 'pedimento',   'dt' => 'pedimento', 'formatter' => function( $d, $row ) {
			include('./../url_archivos.php');
			global $odbccasa;
			$ped1='';
			if ($d!=''){
				$pQuery = "SELECT num_refe FROM SAAIO_PEDIME where adu_desp='".$row['aduana']."' and num_pedi='".rtrim($d)."' and pat_agen='".$row['patente']."'";
				$pResult = odbc_exec ($odbccasa, $pQuery) or die("$pQuery: " . odbc_error());
				if ($pResult!=false){
					if(odbc_num_rows($pResult)>0){
						while(odbc_fetch_row($pResult)){
							$ped1='<a href='.$URL_archivos_pedimentos.odbc_result($pResult,"num_refe").'.pdf target=_blank>'.rtrim($d).'</a> ';
						}
					}
				}
			}
			return $ped1;
		} ),
);

// SQL server connection information
$sql_details = array(
	'user' => $mysqluser_sab07,
	'pass' => $mysqlpass_sab07,
	'db'   => $mysqldb_sab07,
	'host' => $mysqlserver_sab07
);

$fechas='';
$fechaini='';
$fechafin='';
if ($_POST['fechaini']!='')
	$fechaini=substr($_POST['fechaini'],6,4).substr($_POST['fechaini'],3,2).substr($_POST['fechaini'],0,2);
if ($_POST['fechafin']!='')
	$fechafin=substr($_POST['fechafin'],6,4).substr($_POST['fechafin'],3,2).substr($_POST['fechafin'],0,2);
if($fechaini!='' and $fechafin!=''){
	$fechas.=" and rem.fecha>='".$fechaini."' and rem.fecha<='".$fechafin."'";
}else{
	if($fechaini!=''){
		$fechas.=" and rem.fecha>='".$fechaini."'";
	}elseif($fechafin!=''){
		$fechas.=" and rem.fecha<='".$fechafin."'";
	}
}
$having='';
if ($_POST['estatus_pago']!='-1'){
	$having=" HAVING cancelada=".$_POST['estatus_pago'];
}

$idclicont = $_POST['idclicont'];

$baseSql = "SELECT rem.no_mov as no_mov,
				   rem.fecha as fecha,
				   rem.subtotal as subtotal,
				   rem.trafico as trafico,
				   rem.cancelada as cancelada,
				   rem.pedimento as pedimento,
				   rem.aduana as aduana,
				   rem.patente as patente
			FROM contagab.notaremision_dbf AS rem
			WHERE rem.no_cte = '".$idclicont."'".$fechas.$having;
			
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

//require( 'ssp.classremmex.php' );
require( 'ssp.class.php' );

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);

