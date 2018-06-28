<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
include('./../connect_gabdata.php');
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
$link_edocuments='';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
    array( 'db' => 'no_banco',  'dt' => 'no_banco' ),
	array( 'db' => 'no_mov',  'dt' => 'no_mov'),
  array( 'db' => 'trafico',  'dt' => 'trafico' ),
	array( 'db'=> 'tipo_mov',   'dt' => 'tipo_mov', 'formatter' => function( $d,$row ) {
		global $cmysqli_sab07;
    $querym="SELECT concepto2 as concepto FROM contagab.aacgmex_detalle WHERE referencia='".$row['trafico']."' and tipo=1 and clave=12";
    //error_log($querym);
		$consultam= mysqli_query($cmysqli_sab07, $querym) or die(json_encode(array(error=>"Error al consultar las cuentas americanas ".$querym.", error:".mysqli_error($cmysqli_sab07))));
		$nrows = mysqli_num_rows($consultam);
		$ctaame= NULL;
		if($nrows > 0){
			$ctaame= NULL;
			while($rowc = mysqli_fetch_array($consultam)){
				//$linkcta = (file_exists('D:\ctaame\Inv_'.$rowc['concepto'].'.pdf') ? '<a href="dctaame.php?npdf=Inv_'.$rowc['concepto'].'.pdf" target="_blank" >'.$rowc['concepto'].'</a> ' : "<span title='PDF no diponible'>".$rowc['concepto']."</span> " );
				//$ctaame.=$linkcta;
				$ctaame = '<a href="dctaame.php?npdf='.$rowc['concepto'].'" target="_blank" >'.$rowc['concepto'].'</a>';
			}
		}
		if ($ctaame!=NULL){
			return $ctaame;
		}else{
			return '';
		}
	}),
	array( 'db' => 'deuda',   'dt' => 'estatus', 'formatter' => function( $d, $row ) {
			if ($d<1){
				$estatus=' <span class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true" title="Pagada"></span>';
			}else{
				$estatus=' <span class="glyphicon glyphicon-warning-sign text-warning" aria-hidden="true" title="Pendiente"></span>';
			}
			return $estatus;
		} ),
	array( 'db' => 'envio_cliente',   'dt' => 'envio_cliente', 'formatter' => function( $d, $row ) {
			if ($d!=NULL){
				$estatus='<center><span class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true" title="Enviada: '.$d.'"></span></center>';
			}else{
				$estatus='<center><span class="glyphicon glyphicon-warning-sign text-warning" aria-hidden="true" title="Pendiente"></span></center>';
			}
			return $estatus;
		} ),
	array( 'db' => 'deuda',  'dt' => 'deuda',  'formatter' => function( $d, $row ) {
		return number_format($d,2);
	}),
	array( 'db' => 'fecha',   'dt' => 'fecha', 'formatter' => function( $d, $row ) {
			return date( 'd/m/Y', strtotime($d));
		} ),
	array( 'db' => 'pedimento',     'dt' => 'pedimento' ),
	array( 'db' => 'aduana',     'dt' => 'aduana' ),
	array( 'db' => 'patente',     'dt' => 'patente' ),
	array( 'db' => 'referencias',     'dt' => 'referencias' ),
	array( 'db' => 'trafico',  'dt' => 'anexos' , 'formatter' => function( $d, $row ) {
		include('./../url_archivos.php');
		
		$trafico=str_replace('/','_',rtrim($d));
		if (file_exists($dir_archivos_cfd.'anexos\\'.$trafico.'.pdf')){
			return '<center><a href="'.$URL_archivos_anexos.$trafico.'.pdf" target=_blank><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a></center>';
		}
		else{
			$pos = strrpos($trafico, "-");
			if ($pos==false){
				$ar1=substr($trafico,0,2).'-'.substr($trafico,2);
			}
			else{
				$ar1=str_replace('-','',$trafico);
			}
			if (file_exists($dir_archivos_cfd.'anexos\\'.$ar1.'.pdf')){
				return '<a href="'.$URL_archivos_anexos.rtrim($ar1).'.pdf" target=_blank>Ver</a> ';
			}
			else{
				return '<center><span class="glyphicon glyphicon-minus" aria-hidden="true" title="No disponible"></span></center>';
			}
		}
	}),
	array( 'db' => 'pedimentos',     'dt' => 'pedimentos1' ),
	array( 'db' => 'pedimentos',   'dt' => 'pedimentos', 'formatter' => function( $d, $row ) {
		include('./../url_archivos.php');
		global $odbccasa,$link_edocuments;
		$ped1='';
		if ($row['pedimento']!=''){
				$pQuery = "SELECT num_refe FROM SAAIO_PEDIME where adu_desp='".$row['aduana']."' and num_pedi='".rtrim($row['pedimento'])."' and pat_agen='".$row['patente']."'";
				$pResult = odbc_exec ($odbccasa, $pQuery) or die("$pQuery: " . odbc_error());
				if ($pResult!=false){
						if(odbc_num_rows($pResult)>0){
								while(odbc_fetch_row($pResult)){
										$ped1='<a href='.$URL_archivos_pedimentos.odbc_result($pResult,"num_refe").'.pdf target=_blank>'.$row['pedimento'].'</a> ';
										$link_edocuments='<a href=descargazipedoc.php?referencia='.odbc_result($pResult,"num_refe").' target=_blank>'.$row['pedimento'].'</a> ';
								}
						}
				}
		}
		if ($d!=''){
				$apedimentos = explode(", ", $d);
				$areferencias = explode(", ", $row['referencias']);
				$links='';
				for ($ped = 0; $ped<count($apedimentos); $ped++) {
						$links.='<a href='.$URL_archivos_pedimentos.$areferencias[$ped].'.pdf target=_blank>'.$apedimentos[$ped].'</a> ';
						$link_edocuments.='<a href=descargazipedoc.php?referencia='.$areferencias[$ped].' target=_blank>'.$apedimentos[$ped].'</a> ';
				}
				$pedimentos=$ped1.' '.$links;
		}else{
				$pedimentos=$ped1;
		}
		return $pedimentos;
	} ),
	array( 'db' => 'pedimentos',   'dt' => 'edocuments', 'formatter' => function( $d, $row ) {
		global $link_edocuments;
		return $link_edocuments;
	} )
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
	$fechas.=" and fac.fecha>='".$fechaini."' and fac.fecha<='".$fechafin."'";
}else{
	if($fechaini!=''){
		$fechas.=" and fac.fecha>='".$fechaini."'";
	}elseif($fechafin!=''){
		$fechas.=" and fac.fecha<='".$fechafin."'";
	}
}
$having='';
if ($_POST['estatus_pago']=='PAG'){
	$having=" HAVING deuda<1";
}
if ($_POST['estatus_pago']=='PEN'){
	$having=" HAVING deuda>1";
}

$idclicont = $_POST['idclicont'];

$baseSql = "SELECT fac.tipo_mov as tipo_mov,
				   fac.no_banco as no_banco,
				   fac.no_mov as no_mov,
				   fac.fecha as fecha,
				   fac.trafico as trafico,
				   fac.cancelada as cancelada,
				   fac.pedimento as pedimento,
				   fac.aduana as aduana,
				   fac.patente as patente,
				   ped.pedimentos AS pedimentos,
					ped.referencias AS referencias,
				   sum(concat(facd.c_a, facd.monto)) AS deuda,
				   fecha_enviocliente as envio_cliente
		    FROM contagab.aacgmex AS fac
			LEFT JOIN (
					SELECT
						ped0.c001refmas,
						GROUP_CONCAT(
							ped0.c001numped SEPARATOR ', '
						) AS pedimentos,
						GROUP_CONCAT(
							ped0.c001refmas SEPARATOR ', '
						) AS referencias
					FROM
						contagab.factura_consolidado AS ped0
					GROUP BY
						ped0.c001refmas
				) AS ped ON fac.trafico = ped.c001refmas
			LEFT JOIN contagab.asientocontable AS facd ON fac.trafico = facd.referencia AND
 				                                     facd.cuenta = 108  AND
				                                     facd.sub_cta = $idclicont
			WHERE fac.no_cte = '".$idclicont."'".$fechas." AND
			      fac.cancelada=0
      AND fac.firmada=1
			GROUP BY fac.no_mov, fac.no_banco, fac.tipo_mov, ped.c001refmas ".
			$having;

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'ssp.class.php' );

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);
