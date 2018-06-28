<?php
include_once('./../checklogin.php');
include('./../connect_dbsql.php');
if($loggedIn == false){ 
	$msg='Su sesión ha expirado favor de ingresar de nuevo';
	echo json_encode( array( 
			"error" => $msg
		) );
	exit(0);
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
$table = 'tblbod';

// Table's primary key
$primaryKey = 'referencia';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case object
// parameter names
$columns = array(
	array( 'db' => 'referencia',  'dt' => 'referencia' ),
	array( 'db' => 'cliente',     'dt' => 'cliente' ),
	array( 'db' => 'fechaentrada',   'dt' => 'fechaentrada', 'formatter' => function( $d, $row ) {
			return (($d!=NULL and $d!='')? date( 'd/m/Y', strtotime($d)) :  '');
		} ),
	array( 'db' => 'fechavirtual',   'dt' => 'fechavirtual', 'formatter' => function( $d, $row ) {
			return date( 'd/m/Y', strtotime($d));
		} ),
	array( 'db' => 'horaentrada',     'dt' => 'horaentrada' ),
	array( 'db' => 'tienefactura',     'dt' => 'tienefactura','formatter' => function( $d, $row ) {
			if ($d=='SI'||$row['iddoc']==NULL){
				return $d;
			}else{
				return 'SI';
			}
	}),
	array( 'db' => 'iddoc',     'dt' => 'iddoc' ),
	array( 'db' => 'tipodoc',     'dt' => 'tipodoc' ),
	array( 'db' => 'fechadoc',  'dt' => 'fechadoc' ),
	array( 'db' => 'fechafactura',   'dt' => 'fechafactura', 'formatter' => function( $d, $row ) {
			if ($row['iddoc']==NULL){
				if ($d!=Null){
					return date( 'd/m/Y', strtotime($d));
				}else{
					return '';
				}
			}else{
				$datef=new DateTime($row['fechadoc']);
				return $datef->format('d/m/Y');
			}
		} ),
	array( 'db' => 'asignada',     'dt' => 'asignada' ),
	array( 'db' => 'horaasig',     'dt' => 'horaasig' ),
	array( 'db' => 'revision',     'dt' => 'revision' ),
	array( 'db' => 'fechaasig',   'dt' => 'fechaasig', 'formatter' => function( $d, $row ) {
			if ($d!=Null){
				return date( 'd/m/Y', strtotime($d));
			}else{
				return '';
			}
		} ),
	array( 'db' => 'fecharevision',   'dt' => 'fecharevision', 'formatter' => function( $d, $row ) {
			if ($d!=Null){
				return date( 'd/m/Y', strtotime($d));
			}else{
				return '';
			}
		} ),
	array( 'db' => 'horarevision',     'dt' => 'horarevision' ),
	array( 'db' => 'facrev',     'dt' => 'facrev' ),
	array( 'db' => 'remision',     'dt' => 'remision' ),
	array( 'db' => 'fecharemision',   'dt' => 'fecharemision', 'formatter' => function( $d, $row ) {
			if ($d!=Null){
				return date( 'd/m/Y', strtotime($d));
			}else{
				return '';
			}
		} ),
	array( 'db' => 'horaremision',     'dt' => 'horaremision' ),
	array( 'db' => 'tipo_pedimento',     'dt' => 'tipo_pedimento' ),
	array( 'db' => 'aduana',     'dt' => 'aduana' ),
	array( 'db' => 'pedimento',   'dt' => 'pedimento', 'formatter' => function( $d, $row ) {
			if ($row['aduana']=='COLOMBIA'){
				$adu='800';
			}elseif($row['aduana']=='LAREDO'){
				$adu='240';
			}else{
				$adu=$row['aduana'];
			}
			return $adu.'-'.$d;
		} ),
	array( 'db' => 'remesa',     'dt' => 'remesa' ),
	array( 'db' => 'salida',     'dt' => 'salida' ),
	array( 'db' => 'fechasalida',     'dt' => 'fechasalida' ),
	array( 'db' => 'horasalida',     'dt' => 'horasalida' ),
	array( 'db' => 'foto1',     'dt' => 'foto1' ),
	array( 'db' => 'foto2',     'dt' => 'foto2' ),
	array( 'db' => 'foto3',     'dt' => 'foto3' ),
	array( 'db' => 'foto4',     'dt' => 'foto4' ),
	array( 'db' => 'foto5',     'dt' => 'foto5' ),
	array( 'db' => 'fotosadicionales',     'dt' => 'fotosadicionales', 'formatter' => function( $d, $row ) {
		if($d!=NULL)
			$fotos = explode(",", $d);
		else
			$fotos = array();
		$links='';
		foreach ($fotos as $consecutivo) {
			//$archivofoto="\\\\192.168.2.33\dbdata\bodega\FotosAdicionalesWeb\\".$row['referencia']."_adicional_".$consecutivo.".jpg";
			$links.=' <a href="getimageadicional.php?referencia='.$row['referencia'].'&id='.$consecutivo.'" target=_blank>'.$consecutivo.'a</a> ';
		}
		return $links;
	}),
	array( 'db' => 'documentacion',     'dt' => 'documentacion' ),
	array( 'db' => 'ejecutivo',     'dt' => 'ejecutivo' ),
	array( 'db' => 'fechaentrada',   'dt' => 'diasenbodega', 'formatter' => function( $d, $row ) {
			if ($d!=Null){
				$datetime1 = new DateTime(date('Y-m-d h:i:s A',strtotime(date('Y-m-d',strtotime($row['fechaentrada'])).' '.$row['horaentrada'])));
				$datetime2 = new DateTime();
				$interval = $datetime1->diff($datetime2);
				return $interval->format('%a días, %h horas, %I minutos');
				//return date('Y-m-d h:i:s A',strtotime(date('Y-m-d',strtotime($row['fechaentrada'])).' '.$row['horaentrada']));
			}else{
				return '';
			}
	} ),
	array( 'db' => 'estatus',   'dt' => 'estatus', 'formatter' => function( $d, $row ) {
			if ($d==Null){
				return '1';
			}else{
				return $d;
			}
	} ),
	array( 'db' => 'estatus',   'dt' => 'estatus_comentario', 'formatter' => function( $d, $row ) {
			if ($d==Null){
				return false;
			}else{
				return true;
			}
	} ),
	array( 'db' => 'caja',  'dt' => 'caja' ),
	array( 'db' => 'bodbno',     'dt' => 'guia' ),
	array( 'db' => 'linea',     'dt' => 'linea' ),
	array( 'db' => 'id_fac_master',     'dt' => 'id_fac_master' ),
	array( 'db' => 'nom_fac_master',     'dt' => 'nom_fac_master' ),
	array( 'db' => 'id_car_nom',     'dt' => 'id_car_nom' ),
	array( 'db' => 'id_cer_ori',     'dt' => 'id_cer_ori' ),
	array( 'db' => 'fec_sol_nom',     'dt' => 'fec_sol_nom', 'formatter' => function( $d, $row ) {
		/*if ($d!=Null){
			$fecha= new DateTime($d);
			
		}else{
			return '';
		}*/
		return $d;
	} ),
	array( 'db' => 'fec_sol_cer_ori',     'dt' => 'fec_sol_cer_ori' ),
	array( 'db' => 'id_estatus_factura',     'dt' => 'id_estatus_factura' ),
	array( 'db' => 'estatus_factura',     'dt' => 'estatus_factura' ),
	array( 'db' => 'bultos',     'dt' => 'bultos' ),
	array( 'db' => 'po',     'dt' => 'po' ),
	array( 'db' => 'estatus_doc',     'dt' => 'estatus_doc' ),
	array( 'db' => 'remision_doc',     'dt' => 'remision_doc', 'formatter' => function( $d, $row ) {
		return (($d==Null)? '' : $d);
	} )
);

// SQL server connection information
$sql_details = array(
	'user' => $mysqluser,
	'pass' => $mysqlpass,
	'db'   => $mysqldb,
	'host' => $mysqlserver
);
$fechaini='';
$fechafin='';
$fechas = '';

if(isset($_POST['fechaini'])){
	if ($_POST['fechaini']!='')
		$fechaini=substr($_POST['fechaini'],6,4).substr($_POST['fechaini'],3,2).substr($_POST['fechaini'],0,2);
}
if(isset($_POST['fechaini'])){
	if ($_POST['fechafin']!='')
		$fechafin=substr($_POST['fechafin'],6,4).substr($_POST['fechafin'],3,2).substr($_POST['fechafin'],0,2);
}

$fechas=" (bod.bodfecha>='20140101' OR bod.bodfecha IS NULL)";
if($fechaini!='' and $fechafin!=''){
	$fechas.=" AND bod.bodfecha>='".$fechaini."' AND bod.bodfecha<='".$fechafin."'";
}else{
	if($fechaini!=''){
		$fechas.=" AND bod.bodfecha>='".$fechaini."'";
	}elseif($fechafin!=''){
		$fechas.=" AND bod.bodfecha<='".$fechafin."'";
	}
}
if ($_POST['cliente']!=''){
	$fcliente=' AND bod.bodcli='.$_POST['cliente'];
}else{
	$fcliente='';
}
$cinventario='AND IF (bod.bodsalida IS NULL,sald.referencia IS NULL,bod.bodsalida IS NULL)';
$baseSql = "SELECT
				bod.bodReferencia AS referencia,
				bodfecha AS fechaentrada,
				bod.bodhora AS horaentrada,
				bod.bodcaja AS caja,
				bod.bodbno as bodbno,
				bod.bodbultos as bultos,
				bod.bodnopedido as po,
				cli.nom as cliente,
				bod.BODSHIPPING AS tienefactura,
				bod.fechafactura AS fechafactura,
				rev.id_revision AS revision,
				rev.fecha AS fecharevision,
				rev.hora AS horarevision,
				rev.factura as facrev,
				remg.remision,
				remg.fecha AS fecharemision,
				remg.hora AS horaremision,
				remg.aduana as aduana,
				remd.tipo_pedimento,
				remd.pedimento,
				remd.partida as remesa,
				salg.relacion AS salida,
				salg.fecha AS fechasalida,
				salg.hora AS horasalida,
				bod.bodfoto1 as foto1,
				bod.bodfoto2 as foto2,
				bod.bodfoto3 as foto3,
				bod.bodfoto4 as foto4,
				bod.bodfoto5 as foto5,
				bod.weblinkp as documentacion,
				bod.fechavirtual,
				cli.ejecutivo,
				docr.id_doc as iddoc,
				doc.id_tpo as tipodoc,
				doc.fecha as fechadoc,
				asig.fecha as fechaasig,
				asig.hora as horaasig,
				asig.asignada as asignada,
				com.estatus,
				lineas.flenombre as linea,
				doc_fm.id_doc as id_fac_master,
				doc_con_fm.nombre as nom_fac_master,
				doc_nom.id_doc as id_car_nom,
				doc_co.id_doc as id_cer_ori,
				com.req_car_nom as fec_sol_nom,
				com.req_cer_ori as fec_sol_cer_ori,
				bod.id_estatus_factura as id_estatus_factura,
				efac.descripcion as estatus_factura,
			   (SELECT CASE
			   			  WHEN COUNT(DISTINCT docs_tipos.id_tpo) = 0 THEN 'null'
						  WHEN COUNT(DISTINCT docs_tipos.id_tpo) = SUM(IF(estatus_documentos.ok = 1, 1, 0)) THEN 'aprobado'
						  ELSE 'pendiente'
					   END 
				FROM bodega.docs_refe INNER JOIN 
				     bodega.docs_tipos ON docs_tipos.id_tpo=docs_refe.id_tpo INNER JOIN
					 bodega.docs ON docs.id_doc=docs_refe.id_doc AND
								    docs.id_tpo=docs_refe.id_tpo INNER JOIN
					 bodega.estatus_documentos ON estatus_documentos.id_estatus_documento=docs.id_estatus_documento
				WHERE docs_refe.referencia=bod.bodReferencia  AND
					 (docs_tipos.ver_proveedor=1 OR 
					  docs_tipos.solicitud_previa=1)) AS estatus_doc,
			   (SELECT CASE
				 		  WHEN fec_aprov IS NOT NULL THEN 'aprobado'
						  WHEN fec_rechazo IS NOT NULL THEN 'rechazado'
						  ELSE 'pendiente'
						END
				FROM bodega.remeaprov
				WHERE remision=remg.remision AND
					  pedimento=remd.pedimento AND 
					  partida=remd.partida
				ORDER BY fec_subida DESC
				LIMIT 1) AS remision_doc,
				( SELECT GROUP_CONCAT( consecutivo ) FROM fotos WHERE referencia = bod.bodReferencia ) AS fotosadicionales
			FROM
				tblbod AS bod
			LEFT JOIN clientes AS cli ON bod.bodcli = cli.Cliente_id
			LEFT JOIN revision_general AS rev ON bod.bodReferencia = rev.referencia
			LEFT JOIN remisiondet AS remd ON bod.bodReferencia = remd.referencia
			LEFT JOIN remisiongral AS remg ON remd.remision = remg.remision
			LEFT JOIN detalle_salidas AS sald ON bod.bodReferencia = sald.REFERENCIA
			LEFT JOIN datos_generales_salidas AS salg ON sald.RELACION = salg.relacion
			LEFT JOIN docs_refe as docr on bod.bodReferencia=docr.referencia and docr.id_tpo=1
			LEFT JOIN docs as doc on docr.id_doc=doc.id_doc
			LEFT JOIN docs_refe as doc_fm on bod.bodReferencia=doc_fm.referencia and doc_fm.id_tpo=2
			LEFT JOIN docs_contenido as doc_con_fm on doc_fm.id_doc=doc_con_fm.id_doc
			LEFT JOIN docs_refe as doc_nom on bod.bodReferencia=doc_nom.referencia and doc_nom.id_tpo=3
			LEFT JOIN docs_refe as doc_co on bod.bodReferencia=doc_co.referencia and doc_co.id_tpo=4
			LEFT JOIN asignaciones as asig on bod.bodReferencia=asig.referencia
			LEFT JOIN tblbodcom as com on bod.bodReferencia=com.referencia
			LEFT JOIN tblflet AS lineas ON bod.bodfle=lineas.fleclave
			LEFT JOIN estatus_factura AS efac ON bod.id_estatus_factura=efac.id_estatus_factura
			WHERE
			 ".$fechas.$cinventario.$fcliente;

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'ssp.class.php' );
echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);

