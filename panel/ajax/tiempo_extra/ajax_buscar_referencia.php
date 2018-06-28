<?php
include_once('./../../../checklogin.php');
require('./../../../db.php');
include('./../../../bower_components/nusoap/src/nusoap.php');
include('./../../../url_archivos.php');

if($loggedIn == false){
	return '500';
} else{
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {
		
		$referencia = $_POST['referencia'];
		try {
			
            /*$conn_acc = new PDO($pdo_accss_sconn);
			$conn_acc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
			
			$consulta = "
					SELECT Clientes.Nom as cliente, tblFlet.fleNombre as linea_fletera
					FROM  tblFlet INNER JOIN (Clientes INNER JOIN tblBod  ON Clientes.Cliente_id = tblBod.bodcli) ON 
						  tblFlet.fleClave = tblBod.bodfle
					WHERE tblBod.bodReferencia = '".$referencia."'";
					
			$resp = $conn_acc->query($consulta)->fetchAll();
			
			if(count($resp) > 0){
				foreach ($resp as $row) {
					$respuesta['Codigo'] = 1;
					$respuesta['cliente'] = $row['cliente'];
					$respuesta['linea_fletera'] = $row['linea_fletera'];
				}
				
				$cnn_mysql= new PDO($pdo_mysql_sconn, $mysqluser, $mysqlpass);
				$cnn_mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				$consulta = "SELECT * FROM tiempo_extra WHERE referencia = '".$referencia."'";
				$query = $cnn_mysql->query($consulta);
				
				if(count($query->fetchAll()) > 0){
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "La referencia ".$referencia." ya cuenta con una solicitud de Servicio Prioritario."; 
					$respuesta['Error'] = '';
				}
				$cnn_mysql = null;
			}else{
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "La referencia no existe en la base de datos."; 
				$respuesta['Error'] = '';
			}
			$conn_acc = null;*/

			$consulta_mdb = "SELECT Clientes.Nom as cliente, tblFlet.fleNombre as linea_fletera
							 FROM  tblFlet INNER JOIN (Clientes INNER JOIN tblBod  ON Clientes.Cliente_id = tblBod.bodcli) ON 
								   tblFlet.fleClave = tblBod.bodfle
							 WHERE tblBod.bodReferencia = '".$referencia."'";

			$client = new nusoap_client($URL_ws_webtools."/webtools/ws_mdb/ws_mdb.php?wsdl","wsdl");
			$err = $client->getError();
			if ($err) {
				throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.");
			}

			$param = array(
				'usuario' => 'admin',
				'password' => 'r0117c',
				'consulta' => $consulta_mdb,
				'tipo' => 'SELECT',
				'bd' => 'bodega');
			
			$result = $client->call('ws_mdb', $param);
			$err = $client->getError();
			if ($err) {
				throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.". $err);
			}

			if($result['Codigo']==1){
				$bodegadet=json_decode($result['Adicional1']);
				if (count($bodegadet) > 0) {
					foreach($bodegadet as $row_bod){
						$respuesta['Codigo'] = 1;
						$respuesta['cliente'] = $row_bod->cliente;
						$respuesta['linea_fletera'] = $row_bod->linea_fletera;
						break;
					}
				} else {
					$sReferenciasIncorrectas .= (($sReferenciasIncorrectas == '')? '' : $sReferenciasIncorrectas.'<br>');
					$sReferenciasIncorrectas .= 'La referencia <strong>'.$sReferencia.'</strong> no tiene detalle de bultos configurado, favor de comunicarse con personal de bodega';
				}
			}


		} catch (PDOException $e) {
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "¡Error en la consulta!: "; 
			$respuesta['Error'] = ' ['.$e->getMessage().']';
		}
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = '404 :: No se recibieron datos de entrada.';
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}



/* ..:: Verificamos que la referencia tenga el detalle de bultos correcto ::.. */
function fcn_get_referencias_incorrectas($aReferenciasRemision) { 
	global $bDebug, $cmysqli, $URL_ws_webtools;

	$sReferenciasIncorrectas = '';

	/******************************************/

	foreach ($aReferenciasRemision as $row) {
		$sReferencia = $row['referencia'];
		
		$consulta_mdb = "SELECT tipBultos.Tipo, tipBultos.id_facturacion, detBultos.cantidad
						 FROM detallebultos AS detBultos INNER JOIN  
							  tipobultos AS tipBultos ON tipBultos.Clave=detBultos.clavebulto
						 WHERE detBultos.referencia='".$sReferencia."'";

		$client = new nusoap_client($URL_ws_webtools."/webtools/ws_mdb/ws_mdb.php?wsdl","wsdl");
		$err = $client->getError();
		if ($err) {
			throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.");
		}

		$param = array(
			'usuario' => 'admin',
			'password' => 'r0117c',
			'consulta' => $consulta_mdb,
			'tipo' => 'SELECT',
			'bd' => 'bodega');
		
		$result = $client->call('ws_mdb', $param);
		$err = $client->getError();
		if ($err) {
			throw new Exception("Error al consultar información de bodega. Por favor contacte al administrador del sistema.". $err);
		}

		if($result['Codigo']==1){
			$bodegadet=json_decode($result['Adicional1']);
			if (count($bodegadet) > 0) {
				foreach($bodegadet as $row_bod){
					$sTipo = $row_bod->Tipo;
					$id_facturacion = $row_bod->id_facturacion;
					$sCantidad = intval($row_bod->cantidad);
	
					if (is_null($id_facturacion) || $id_facturacion == '') {
						$sReferenciasIncorrectas .= (($sReferenciasIncorrectas == '')? '' : $sReferenciasIncorrectas.'<br>');
						$sReferenciasIncorrectas .= 'La referencia <strong>'.$sReferencia.'</strong> tiene un tipo de bulto no permitido <strong>Cantidad: '.$sCantidad.'</strong>, tipo de bulto: <strong>'.$sTipo.'</strong>, favor de comunicarse con personal de bodega';
					}
				}
			} else {
				$sReferenciasIncorrectas .= (($sReferenciasIncorrectas == '')? '' : $sReferenciasIncorrectas.'<br>');
				$sReferenciasIncorrectas .= 'La referencia <strong>'.$sReferencia.'</strong> no tiene detalle de bultos configurado, favor de comunicarse con personal de bodega';
			}
		}
	}

	return $sReferenciasIncorrectas;
}

