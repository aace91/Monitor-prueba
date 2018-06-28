<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../url_archivos.php');

if($loggedIn == false){
	echo '500';
} else {
	
	$aCruces  = array();
	
	$consulta = "SELECT ce.id_cruce,ce.numcliente,cli.cnombre as cliente,ce.fecha_registro,ce.numlinea,lt.Nombre as nom_lineat,ce.aduana,ce.tiposalida,ce.caja,
						if(ce.notransfer IS NULL, '',ce.notransfer) as notransfer,if(tr.nombretransfer IS NULL, '',tr.nombretransfer) as nombretransfer,
						ce.caat,ce.scac,ce.observaciones,ce.po_number,
						if(ce.noentrega IS NULL, '',ce.noentrega) as noentrega,
						if(ent.nombreentrega IS NULL, '',ent.nombreentrega) as nombreentrega,
						ce.direntrega,ce.indicaciones,
						IF(ce.numcliente_consolidar IS NULL, '', ce.numcliente_consolidar) as numcliente_consolidar,
						GROUP_CONCAT(fe.SALIDA_NUMERO) as salida
				FROM bodega.cruces_expo ce
					INNER JOIN cruces_expo_detalle cd ON
						ce.id_cruce = cd.id_cruce
					INNER JOIN bodega.lineast lt ON
						ce.numlinea = lt.numlinea
					LEFT JOIN bodega.transfers_expo tr ON
						ce.notransfer = tr.notransfer
					LEFT JOIN bodega.entregas_expo ent ON
						ce.noentrega = ent.numeroentrega
					INNER JOIN bodega.cltes_expo cli ON
						ce.numcliente=cli.gcliente
					LEFT JOIN facturas_expo fe ON 
						cd.referencia = fe.REFERENCIA AND
						cd.cons_fact = fe.CONS_FACT_PED
					GROUP BY ce.id_cruce
				HAVING salida IS NULL";

	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar la informacion del cruce. Por favor contacte el administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
	}else{
		while ($row = mysqli_fetch_array($query)){
			$aCruce = array (
				"id_cruce" => $row['id_cruce'],
				"cliente" => $row['cliente'],
				"fecha_registro" => date( 'd/m/Y', strtotime($row['fecha_registro'])),
				"nom_lineat" => $row['nom_lineat'],
				"aduana" => $row['aduana'],
				"caja" => $row['caja'],
				"nombretransfer" => $row['nombretransfer'],
				"po_number" => $row['po_number'],
				"nombreentrega" => $row['nombreentrega']
			);
			array_push($aCruces,$aCruce);
		}
		$respuesta['Codigo'] = 1;
		$respuesta['aCruces'] = $aCruces;
	}
	
	echo json_encode($respuesta);
}
