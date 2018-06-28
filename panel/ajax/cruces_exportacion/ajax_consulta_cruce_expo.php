<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../url_archivos.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_cruce']) && !empty($_POST['id_cruce'])) {  
		$respuesta['Codigo']=1;
		$id_cruce = $_POST['id_cruce'];
		
		$consulta = "SELECT ce.numcliente,cli.cnombre as cliente,ce.fecha_registro,ce.numlinea,lt.Nombre as nom_lineat,ce.aduana,
							if(ce.notransfer IS NULL, '',ce.notransfer) as notransfer,if(tr.nombretransfer IS NULL, '',tr.nombretransfer) as nombretransfer,
							ce.caat,ce.scac,ce.observaciones,ce.po_number,
							if(ce.noentrega IS NULL, '',ce.noentrega) as noentrega,
							if(ent.nombreentrega IS NULL, '',ent.nombreentrega) as nombreentrega,
							ce.direntrega,ce.indicaciones,
							IF(ce.numcliente_consolidar IS NULL, '', ce.numcliente_consolidar) as numcliente_consolidar,
							clc.cnombre as cliente_consolidar
					 FROM bodega.cruces_expo ce
						INNER JOIN bodega.lineast lt ON
							ce.numlinea = lt.numlinea
						LEFT JOIN bodega.transfers_expo tr ON
							ce.notransfer = tr.notransfer
						LEFT JOIN bodega.entregas_expo ent ON
							ce.noentrega = ent.numeroentrega
						INNER JOIN bodega.cltes_expo cli ON
							ce.numcliente=cli.gcliente
						LEFT JOIN cltes_expo clc ON
							ce.numcliente_consolidar = clc.gcliente
					 WHERE id_cruce = ".$id_cruce;
	
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar la informacion del cruce. Por favor contacte el administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		}else{
			while ($row = mysqli_fetch_array($query)){
				$respuesta['numcliente'] = $row['numcliente'];
				$respuesta['cliente'] = $row['cliente'];
				$respuesta['fecha_registro'] = date( 'd/m/Y', strtotime($row['fecha_registro']));
				$respuesta['numlinea'] = $row['numlinea'];
				$respuesta['nom_lineat'] = $row['nom_lineat'];
				$respuesta['aduana'] = $row['aduana'];
				$respuesta['notransfer'] = $row['notransfer'];
				$respuesta['nombretransfer'] = $row['nombretransfer'];
				$respuesta['caat'] = $row['caat'];
				$respuesta['scac'] = $row['scac'];
				$respuesta['po_number'] = $row['po_number'];
				$respuesta['noentrega'] = $row['noentrega'];
				$respuesta['nombreentrega'] = $row['nombreentrega'];
				$respuesta['direntrega'] = $row['direntrega'];
				$respuesta['indicaciones'] = $row['indicaciones'];
				$respuesta['observaciones'] = $row['observaciones'];
				$respuesta['numcliente_consolidar'] = $row['numcliente_consolidar'];
				$respuesta['cliente_consolidar'] = $row['cliente_consolidar'];
			}
			$consulta = "SELECT cc.numcliente, cli.cnombre as cliente
						FROM bodega.cruces_expo_clientes_consolidar cc
							INNER JOIN bodega.cltes_expo cli ON
								cc.numcliente = cli.gcliente
						WHERE cc.id_cruce = ".$id_cruce;
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar la informacion de los clientes consolidados. Por favor contacte el administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}else{
				$aCliConsolidar = array();
				$nItem = 0;
				while ($row = mysqli_fetch_array($query)){
					$nItem +=1;
					$aCliente = array(
						'consecutivo' => $nItem,
						'id_cliente' => $row['numcliente'],
						'cliente' => $row['cliente']);
					array_push($aCliConsolidar,$aCliente);
				}
				$respuesta['aCliConsolidar'] = $aCliConsolidar;
				$respuesta['Mensaje'] = '';
				include('consultar_facturas.php');
			}
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
