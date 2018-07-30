<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include("./../../../bower_components/nusoap/src/nusoap.php");
set_time_limit (180);

if ($loggedIn == false){
	echo '500';
}else{
	if (isset($_POST['id_cliente']) && !empty($_POST['id_cliente'])) {
		$respuesta['Codigo'] = 1;
		$id_cliente = $_POST['id_cliente'];
		$id_proveedor = $_POST['id_proveedor'];
		$aClasificaciones = $_SESSION['aClasificaciones'];
		if(count($aClasificaciones) > 0){
			for($i = 0; $i < count($aClasificaciones); $i++){		
				$NUM_PARTE = $aClasificaciones[$i][0];
				$FRACCION = $aClasificaciones[$i][1];
				$FRACCION10 = $aClasificaciones[$i][2];
				$DESCRIPCION = $aClasificaciones[$i][3];
				$UM = $aClasificaciones[$i][4];
				$FUNDAMENTO = $aClasificaciones[$i][5];
				
				$consulta = "SELECT id 
							 FROM clasificaciones
							 WHERE noparte='".$NUM_PARTE."' AND 
								   cliente_id=".$id_cliente." AND 
								   proveedor_id=".$id_proveedor;
										
				$respuesta = web_service_query($consulta,'SELECT');
				if($respuesta['Codigo'] != 1){break;}
				$aRows = json_decode($respuesta['Adicional1'], true);	
				if(count($aRows) == 0){
					$fechan = new DateTime();
					$fecha=$fechan->format("m/d/Y");
					$fecham=$fechan->format("Y-m-d 00:00:00");
					$hora=$fechan->format("g:i:s A");
					//INSERTAR ACCESS
					$consultaa="INSERT INTO clasificaciones (noparte,origen,fraccion,fraccion2,descripcion,
											                 proveedor_id,cliente_id,medida,usuario,fecha,hora,clasificado, fundamento_legal)
								VALUES ('$NUM_PARTE','','$FRACCION','$FRACCION10','$DESCRIPCION',$id_proveedor,
										$id_cliente,'$UM','$username','$fecha','$hora','X', '".$FUNDAMENTO."')";
								
					$respuesta = web_service_query($consultaa,'INSERT');
					if($respuesta['Codigo'] != 1){
						break;
					}
					//CONSULTAR ID AGREGADO EN ACCESS
					$consulta = "SELECT id 
								FROM clasificaciones
								WHERE noparte='".$NUM_PARTE."' AND 
										cliente_id=".$id_cliente." AND 
										proveedor_id=".$id_proveedor;
										
					$respuesta = web_service_query($consulta,'SELECT');
					//error_log(json_encode($respuesta));
					if($respuesta['Codigo'] != 1){
						break;
					}
					$consecutivo = 0;
					$aRows = json_decode($respuesta['Adicional1'], true);
					for($i = 0; $i<count($aRows); $i++){
						$consecutivo = $aRows[$i]['id'] + 1;
					}
					if($consecutivo == 0){
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = 'Error al consultar consecutivo access.';
						$respuesta['Error'] = 'Error: consulta['.$consulta.'] respuesta[resultado:'.$respuesta['Adicional1'].']';
						$consulta = "DELETE FROM clasificaciones WHERE noparte='".$NUM_PARTE."' AND cliente_id=".$id_cliente." AND proveedor_id=".$id_proveedor;
						$res = web_service_query($consulta,'UPDATE');
						break;
					}
					//INSERTAR MY SQL
					$consulta = "INSERT INTO clasificaciones (id,noparte,origen,fraccion,fraccion2,descripcion,proveedor_id,
													cliente_id,medida,usuario,fecha,hora,clasificado, fundamento_legal) 
										VALUES (	'$consecutivo','$NUM_PARTE','','$FRACCION','$FRACCION10','$DESCRIPCION',
													'$id_proveedor','$id_cliente','$UM','$username','$fecham','$hora','X','".$FUNDAMENTO."')";
													
					$consultam=" INSERT INTO bodegareplica.clasificaciones (id,noparte,origen,fraccion,fraccion2,descripcion,
																	proveedor_id,cliente_id,medida,usuario,fecha,hora,fundamento_legal) 
															VALUES ($consecutivo,'$NUM_PARTE','','$FRACCION','$FRACCION10','$DESCRIPCION',
																$id_proveedor,$id_cliente,'$UM','$username','$fecham','$hora','X','".$FUNDAMENTO."')";
					mysqli_query($cmysqli,"BEGIN");
					//Replica
					$query = mysqli_query($cmysqli,$consultam);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = 'Error al actualizar la clasificacion.';
						$respuesta['Error'] = '[REPLICA:Error :: item: '.($i+1).' |NumParte:'.$NUM_PARTE.'|'.$error.']'.$consultam;
						$consulta = "DELETE FROM clasificaciones WHERE noparte='".$NUM_PARTE."' AND cliente_id=".$id_cliente." AND proveedor_id=".$id_proveedor;
						$res = web_service_query($consulta,'UPDATE');
						mysqli_query($cmysqli,"ROLLBACK");
						break;
					}
					//Normal
					$query = mysqli_query($cmysqli,$consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = 'Error al guardar la clasificacion.';
						$respuesta['Error'] = '[BODEGA:Error :: item: '.($i+1).' |NumParte:'.$NUM_PARTE.'|'.$error.']'.$consulta;
						$consulta = "DELETE FROM clasificaciones WHERE noparte='".$NUM_PARTE."' AND cliente_id=".$id_cliente." AND proveedor_id=".$id_proveedor;
						$res = web_service_query($consulta,'UPDATE');
						mysqli_query($cmysqli,"ROLLBACK");
						break;
					}
					mysqli_query($cmysqli,"COMMIT");
				}else{
					
					$fechan = new DateTime();
					$fecha=$fechan->format("m/d/Y");
					$fecham=$fechan->format("Y-m-d 00:00:00");
					$hora=$fechan->format("g:i:s A");
					//Access
					$consultaa="
						UPDATE clasificaciones
						SET fecha= '$fecha',
							hora= '$hora' ,
							usuario= '$username' ,
							fraccion= '$FRACCION',
							fraccion2 = '$FRACCION10',
							descripcion= '$DESCRIPCION' ,
							medida= '$UM',
							clasificado='X'
						WHERE noparte='".$NUM_PARTE."' AND 
										cliente_id=".$id_cliente." AND 
										proveedor_id=".$id_proveedor;
										
					$respuesta = web_service_query($consultaa,'UPDATE');
					if($respuesta['Codigo'] != 1){
						break;
					}
					//MYSQL
					$consulta = "UPDATE clasificaciones SET 
													fraccion = '".$FRACCION."',
													fraccion2 = '".$FRACCION10."',
													descripcion = '".$DESCRIPCION."',
													medida = '".$UM."',
													usuario = '".$username."',
													clasificado = 'X'
								WHERE noparte='".$NUM_PARTE."' AND 
										cliente_id='".$id_cliente."' AND 
										proveedor_id='".$id_proveedor."'";
					
					$consultam="
						UPDATE bodegareplica.clasificaciones
						SET fecha = '$fecham',
							hora = '$hora' ,
							usuario = '$username' ,
							fraccion = '$FRACCION' ,
							fraccion2 = '$FRACCION10',
							descripcion= '$DESCRIPCION' ,
							medida= '$UM',
							clasificado='X'
						WHERE noparte='".$NUM_PARTE."' AND 
										cliente_id='".$id_cliente."' AND 
										proveedor_id='".$id_proveedor."'";
					mysqli_query($cmysqli,"BEGIN");
					$query = mysqli_query($cmysqli,$consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = 'Error al actualizar la clasificacion.';
						$respuesta['Error'] = '[Error :: item: '.($i+1).' |NumParte:'.$NUM_PARTE.'|'.$error.']'.$consulta;
						mysqli_query($cmysqli,"ROLLBACK");
						break;
					}
					$query = mysqli_query($cmysqli,$consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = 'Error al actualizar la clasificacion.';
						$respuesta['Error'] = '[Error :: item: '.($i+1).' |NumParte:'.$NUM_PARTE.'|'.$error.']'.$consulta;
						mysqli_query($cmysqli,"ROLLBACK");	
						break;
					}
					mysqli_query($cmysqli,"COMMIT");
				}
			}
		}else{
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'No existen registros para actualizar.';
			$respuesta['Error'] = '';
		}
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = 'Error en los parametros de entrada.';
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);  
}

function web_service_query($consulta,$accion){
	include('./../../../url_archivos.php');
	$result['Codigo'] = 1;
	
	$client = new nusoap_client("$URL_ws_webtools/webtools/ws_mdb/ws_mdb.php?wsdl","wsdl");
	$err = $client->getError();
	if ($err) {
		$result['Codigo'] = -1;
		$result['Mensaje'] = "Constructor error:". $err ;
		$result['Error'] = '';
	}
	$param = array('usuario' => 'admin','password' => 'r0117c','consulta' => $consulta,'tipo' => $accion,'bd' => 'revisiones');
	$result = $client->call('ws_mdb', $param);
	$err = $client->getError();
	if ($err) {
		$result['Codigo'] = -1;
		$result['Mensaje'] = "Constructor error:". $err ;
		$result['Error'] = '';
		
	}
	if($result['Codigo']!=1){
		$result['Codigo'] = -1;
		$result['Mensaje'] = "Error del WS: ".$result['Mensaje'].". Consulta: ".$consulta;
		$result['Error'] = '';
	}
	return $result;
}


