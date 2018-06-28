<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../connect_casa.php');
require('./../../../url_archivos.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['cruces']) && !empty($_POST['cruces'])) {
		$respuesta['Codigo']=1;
		$aFacturas = array();
		$aIdCruces = json_decode($_POST['cruces']);
		$fecharegistro =  date("Y-m-d H:i:s");
		$strCruces = '';
		for($i = 0; $i<count($aIdCruces); $i++){
			$strCruces .= ($i > 0 ? ',' : '')."'".$aIdCruces[$i]."'";
		}
		$consulta = "SELECT ce.id_cruce,
							ce.numcliente,
							cli.cnombre,
							ce.numlinea,
							lt.Nombre as lineat,
							IFNULL(ce.caja,GROUP_CONCAT(DISTINCT ced.caja)) AS caja,
							ce.aduana,
							ce.notransfer,
							te.nombretransfer,
							ce.noentrega,
							ce.nombreentrega,
							ce.direntrega,
							ce.indicaciones,
							ce.observaciones,
							ce.caat,
							ce.scac,
							if(ce.numcliente_consolidar IS NULL, '', ce.numcliente_consolidar) as numcliente_consolidar,
							if(clic.cnombre IS NULL, '', clic.cnombre) as cliente_consolidar
					FROM cruces_expo ce
						INNER JOIN cruces_expo_detalle ced ON
							ce.id_cruce = ced.id_cruce
						INNER JOIN cltes_expo cli ON
							ce.numcliente = cli.gcliente
						INNER JOIN lineast lt ON
							ce.numlinea = lt.numlinea
						LEFT JOIN transfers_expo te ON
							ce.notransfer = te.notransfer
						LEFT JOIN cltes_expo clic ON
							ce.numcliente_consolidar = clic.gcliente
					WHERE ce.id_cruce IN ($strCruces)
					GROUP BY ce.id_cruce";
		
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar la informacion de los cruces.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) > 0){
			$nitem = 0; 
			$blineat = false;$baduana = false;$btiposal = false;$bcaja = false;
			$btransfer = false;$bcaat = false;$bscac = false;$bentregar = false;
			$bconsolidar= false;
			
			$tdlineat = '';$tdaduana = '';$tdtiposal = '';$tdcaja = '';
			$tdtransfer = '';$tdcaat = '';$tdscac = '';$tdentregar = '';
			$cli_consolidar = ''; $observaciones_cruces='';
			
			while ($row = mysqli_fetch_array($query)){
				if($nitem == 0){
					
					$cruce = $row['id_cruce'];$numcliente = $row['numcliente'];
					$cnombre = $row['cnombre'];$numlinea = $row['numlinea'];
					$lineat = $row['lineat'];$caja = $row['caja'];
					$aduana = $row['aduana'];$notransfer = $row['notransfer'];
					$nombretransfer = $row['nombretransfer'];$noentrega = $row['noentrega'];
					$nombreentrega = $row['nombreentrega'];$indicaciones = $row['indicaciones'];
					//$tiposalida = $row['tiposalida'];
					$caat = $row['caat'];$scac = $row['scac'];
					$direntrega = $row['direntrega'];
					
					$mTabla[$nitem]['lineat'] = $row['lineat'];
					$mTabla[$nitem]['caja'] = $row['caja'];
					$mTabla[$nitem]['aduana'] = $row['aduana'];
					$mTabla[$nitem]['nombretransfer'] = $row['nombretransfer'];
					$mTabla[$nitem]['nombreentrega'] = $row['nombreentrega'];
					//$mTabla[$nitem]['tiposalida'] = $row['tiposalida'];
					$mTabla[$nitem]['caat'] = $row['caat'];
					$mTabla[$nitem]['scac'] = $row['scac'];
					
					$mTabla[$nitem]['cruce'] = $row['id_cruce'];
					$mTabla[$nitem]['lineat'] = $row['lineat'];
					if(trim($row['numlinea']) == ''){
						$respuesta['Codigo']=-1;$blineat = true;
						$mTabla[$nitem]['lineat'] = '[VACIO]';
					}
					$mTabla[$nitem]['aduana'] = $row['aduana'];
					if(trim($row['aduana']) == ''){
						$respuesta['Codigo']=-1;$baduana = true;
						$mTabla[$nitem]['aduana'] = '[VACIO]';
					}
					/*$mTabla[$nitem]['tiposalida'] = $row['tiposalida'];
					if(trim($row['tiposalida']) == ''){
						$respuesta['Codigo']=-1;$btiposal = true;
						$mTabla[$nitem]['tiposalida'] = '[VACIO]';
					}*/
					$mTabla[$nitem]['caja'] = $row['caja'];
					if(trim($row['caja']) == ''){
						$respuesta['Codigo']=-1;$bcaja = true;
						$mTabla[$nitem]['caja'] = '[VACIO]';
					}
					$mTabla[$nitem]['nombretransfer'] = $row['nombretransfer'];
					if(trim($row['notransfer']) == ''){
						$respuesta['Codigo']=-1;$btransfer = true;
						$mTabla[$nitem]['nombretransfer'] = '[VACIO]';
					}
					$mTabla[$nitem]['caat'] = $row['caat'];
					if(trim($row['caat']) == ''){
						$respuesta['Codigo']=-1;$bcaat = true;
						$mTabla[$nitem]['caat'] = '[VACIO]';
					}
					$mTabla[$nitem]['scac'] = $row['scac'];
					if(trim($row['scac']) == ''){
						$respuesta['Codigo']=-1;$bscac = true;
						$mTabla[$nitem]['scac'] = '[VACIO]';
					}
					$mTabla[$nitem]['nombreentrega'] = $row['nombreentrega'];
					if(trim($row['noentrega']) == ''){
						$respuesta['Codigo']=-1;$bentregar = true;
						$mTabla[$nitem]['nombreentrega'] = '[VACIO]';
					}
					$mTabla[$nitem]['cli_consolidar'] = $row['cliente_consolidar'];
					if($row['numcliente_consolidar'] != ''){
						$cli_consolidar = $row['numcliente_consolidar'];
					}else{
						$cli_consolidar = $numcliente;
					}
					$nitem += 1;
				}else{
					$mTabla[$nitem]['cruce'] = $row['id_cruce'];
					$mTabla[$nitem]['lineat'] = $row['lineat'];
					if(trim($numlinea) != trim($row['numlinea']) || trim($row['numlinea']) == ''){
						$respuesta['Codigo']=-1;$blineat = true;
						if(trim($row['numlinea']) == ''){
							$mTabla[$nitem]['lineat'] = '[VACIO]';
						}
					}
					$mTabla[$nitem]['aduana'] = $row['aduana'];
					if(trim($aduana) != trim($row['aduana']) || trim($row['aduana']) == ''){
						$respuesta['Codigo']=-1;$baduana = true;
						if(trim($row['aduana']) == ''){
							$mTabla[$nitem]['aduana'] = '[VACIO]';
						}
					}
					/*$mTabla[$nitem]['tiposalida'] = $row['tiposalida'];
					if(trim($tiposalida) != trim($row['tiposalida']) || trim($row['tiposalida']) == ''){
						$respuesta['Codigo']=-1;$btiposal = true;
						if(trim($row['tiposalida']) == ''){
							$mTabla[$nitem]['tiposalida'] = '[VACIO]';
						}
					}*/
					$mTabla[$nitem]['caja'] = $row['caja'];
					if(trim($caja) != trim($row['caja']) || trim($row['caja']) == ''){
						$respuesta['Codigo']=-1;$bcaja = true;
						if(trim($row['caja']) == ''){
							$mTabla[$nitem]['caja'] = '[VACIO]';
						}
					}
					$mTabla[$nitem]['nombretransfer'] = $row['nombretransfer'];
					if(trim($notransfer) != trim($row['notransfer']) || trim($row['notransfer']) == ''){
						$respuesta['Codigo']=-1;$btransfer = true;
						if(trim($row['notransfer']) == ''){
							$mTabla[$nitem]['nombretransfer'] = '[VACIO]';
						}
					}
					$mTabla[$nitem]['caat'] = $row['caat'];
					if(trim($caat) != trim($row['caat']) || trim($row['caat']) == ''){
						$respuesta['Codigo']=-1;$bcaat = true;
						if(trim($row['caat']) == ''){
							$mTabla[$nitem]['caat'] = '[VACIO]';
						}
					}
					$mTabla[$nitem]['scac'] = $row['scac'];
					if(trim($scac) != trim($row['scac']) || trim($row['scac']) == ''){
						$respuesta['Codigo']=-1;$bscac = true;
						if(trim($row['scac']) == ''){
							$mTabla[$nitem]['scac'] = '[VACIO]';
						}
					}
					$mTabla[$nitem]['nombreentrega'] = $row['nombreentrega'];
					if(trim($noentrega) != trim($row['noentrega']) || trim($row['noentrega']) == ''){
						$respuesta['Codigo']=-1;$bentregar = true;
						if(trim($row['noentrega']) == ''){
							$mTabla[$nitem]['nombreentrega'] = '[VACIO]';
						}
					}
					$mTabla[$nitem]['cli_consolidar'] = $row['cliente_consolidar'];
					//Pueden tener diferentes clientes a consilidar o no tener y se debe poder generar salida
					//siempre y cuando los otros datos coincidan
					/*if($row['numcliente_consolidar'] == ''){
						if($cli_consolidar != $row['numcliente']){
							$respuesta['Codigo']=-1;$bconsolidar = true;
						}
					}else{
						if($cli_consolidar != $row['numcliente_consolidar']){
							$respuesta['Codigo']=-1;$bconsolidar = true;
						}
					}*/
					$nitem += 1;
				}
				$observaciones_cruces .= ' '.$row['observaciones'];
			}
			if($respuesta['Codigo'] != 1){
				$sHTML = '';
				for($i = 0 ; $i<count($mTabla); $i++){
					$sHTML .= '	<tr>
									<th>'.$mTabla[$i]['cruce'].'</th>
									'.($blineat ? '<th>'.$mTabla[$i]['lineat'].'</th>' : '').'
									'.($baduana ? '<th>'.$mTabla[$i]['aduana'].'</th>' : '').'
									'.($bcaja ? '<th>'.$mTabla[$i]['caja'].'</th>' : '').'
									'.($btransfer ? '<th>'.$mTabla[$i]['nombretransfer'].'</th>' : '').'
									'.($bcaat ? '<th>'.$mTabla[$i]['caat'].'</th>' : '').'
									'.($bscac ? '<th>'.$mTabla[$i]['scac'].'</th>' : '').'
									'.($bentregar ? '<th>'.$mTabla[$i]['nombreentrega'].'</th>' : '').'									
								</tr>';
				}	//'.($bconsolidar ? '<th>'.$mTabla[$i]['cli_consolidar'].'</th>' : '').'
				$sHTML = '
					<div class="col-xs-12">
						<h4>Existen diferencias en los siguientes campos:</h4>
					</div>
					<div class="col-xs-12" style="overflow:hidden;">
						<table class="table table-bordered table-striped" width="100%">
							<thead>
								<tr>
									<th>Cruce</th>
									'.($blineat ? '<th>Linea Transportista</th>' : '').'
									'.($baduana ? '<th>Aduana</th>' : '').'
									'.($bcaja ? '<th>N&uacute;mero Caja</th>' : '').'
									'.($btransfer ? '<th>Transfer</th>' : '').'
									'.($bcaat ? '<th>CAAT</th>' : '').'
									'.($bscac ? '<th>SCAC</th>' : '').'
									'.($bentregar ? '<th>ENTREGAR</th>' : '').'									
								</tr>
							</thead>
							<tbody>
								'.$sHTML.'
							</tbody>
						</table>
					</div>';//'.($bconsolidar ? '<th>CLIENTE A CONSOLIDAR</th>' : '').'
					
				$respuesta['Codigo'] = 2;
				$respuesta['Mensaje'] = $sHTML;
				$respuesta['Error'] = '';
				exit(json_encode($respuesta));
			}
			
			//Verificar si las facturas existen en casa // Si ya se vinculo a una factura se ignora. [cons_fact IS NULL]
			$consulta = "SELECT id_cruce, id_detalle_cruce, uuid, IFNULL(referencia, '') as referencia, numero_factura
				FROM cruces_expo_detalle ced
				WHERE id_cruce in ($strCruces) AND cons_fact IS NULL";
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al verificar las facturas en CASA. [MySQL].'; 
				$respuesta['Error'] = ' ['.$error.']'.$consulta;
				exit(json_encode($respuesta));
			}
			$aFacNoCasa = array();
			while ($row = mysqli_fetch_array($query)){
				if($row['referencia'] == ''){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Es necesario que todas las facturas del cruce '.$row['id_cruce'].' tengan referencia asignada.'; 
					$respuesta['Error'] = ' ['.$error.']'.$consulta;
					exit(json_encode($respuesta));
				}
				$referencia = $row['referencia'];
				$uuid = $row['uuid'];
				$numero_factura = $row['numero_factura'];
				//Buscar factura en CASA
				//global $odbccasa;
				$Pnts = '';
				$qCasa = "SELECT p.ADU_DESP,p.PAT_AGEN,p.NUM_PEDI,p.NUM_REFE,f.CONS_FACT,f.NUM_FACT,f.NUM_FACT2,f.NUM_REM, 
								CASE WHEN p.FIR_REME IS NULL THEN '' ELSE p.FIR_REME END AS FIR_REME
							FROM SAAIO_FACTUR f
								INNER JOIN SAAIO_PEDIME p ON
									p.NUM_REFE = f.NUM_REFE 
							WHERE p.NUM_REFE = '".$referencia."' AND UPPER(f.NUM_FACT2) = '".strtoupper($uuid)."'";
				
				$resped = odbc_exec ($odbccasa, $qCasa);
				if ($resped == false){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error al verificar factura en CASA [".$referencia." UUID: ".$uuid."]. BD.CASA.";
					$respuesta['Error'] = ' ['.odbc_error().']'.$qCasa;
					exit(json_encode($respuesta));
				}
				if(odbc_num_rows($resped) == 0 ){
					//Si la factura no existe en CASA agregarla para que ejecutivo la asigne
					$Fac = array(
								"id_cruce" => $row['id_cruce'],
								"id_detalle_cruce" => $row['id_detalle_cruce'],
								"referencia" => $referencia,
								"numero_factura" => $numero_factura
								);
					array_push($aFacNoCasa,$Fac);			
				}else{
					while(odbc_fetch_row($resped)){
						$referencia = odbc_result($resped,"NUM_REFE");
						$cons_fact = odbc_result($resped,"CONS_FACT");
						$num_fact = odbc_result($resped,"NUM_FACT");
						
						$con = "SELECT id_detalle_cruce
								FROM cruces_expo_detalle ced
								WHERE referencia = '".$referencia."' AND cons_fact = ".$cons_fact;
								
						$q = mysqli_query($cmysqli,$con);
						if (!$q) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al verificar si la factura encontrada en CASA, existe en el sistema de CRUCES.[$referencia / $cons_fact / $num_fact] [MySQL].'; 
							$respuesta['Error'] = ' ['.$error.']'.$con;
							exit(json_encode($respuesta));
						}
						if(mysqli_num_rows($q) == 0){
							//No existe en casa, Asignarla a factura de cruce
							$con = "UPDATE cruces_expo_detalle SET 
																referencia = '".$referencia."',
																cons_fact = ".$cons_fact. ' 
										WHERE id_detalle_cruce = '.$row['id_detalle_cruce'];
							
							$q = mysqli_query($cmysqli,$con);
							if (!$q) {
								$error=mysqli_error($cmysqli);
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']='Error al actualizar los datos de la factura $numero_factura del cruce '.$row['id_cruce'].' [MySQL].'; 
								$respuesta['Error'] = ' ['.$error.']'.$con;
								exit(json_encode($respuesta));
							}
						}else{
							//Si la factura ya existe, Mostrar opciones para que el ejecutivo ligue
							$Fac = array(
								"id_cruce" => $row['id_cruce'],
								"id_detalle_cruce" => $row['id_detalle_cruce'],
								"referencia" => $referencia,
								"numero_factura" => $numero_factura
								);
							array_push($aFacNoCasa,$Fac);
						}
					}
				}
			}
			//Regresar facturas que no se pueden vincular
			if(count($aFacNoCasa) > 0 ){
				$respuesta['Codigo'] = 4;
				$respuesta['Mensaje']="Las siguientes facturas no se vincularon con casa, favor de vincular manualmente.";
				$respuesta['Error'] = '';
				$respuesta['aFacNoCasa'] = $aFacNoCasa;
				exit(json_encode($respuesta));
			}
			//Ver si existe un cruce consolidado analizar los pendientes que no exista otro con la misma caja linea transportista.
			//AVISAR QUE CRUCES CON LA MISMA CAJA Y LINEA FLETERA QUE NO HAN SIDO PROCESADOS
			$consulta = "SELECT ced.id_cruce
							FROM cruces_expo_detalle ced
								INNER JOIN cruces_expo ce ON
									ced.id_cruce = ce.id_cruce
								INNER JOIN (SELECT numlinea, caja
														FROM cruces_expo 
														WHERE numcliente_consolidar IS NOT NULL AND id_cruce IN ($strCruces)) ca ON
									ce.caja = ca.caja AND
									ce.numlinea = ca.numlinea
								LEFT JOIN facturas_expo fe ON
									ced.referencia = fe.REFERENCIA AND 
									ced.cons_fact = fe.CONS_FACT_PED
							WHERE fe.SALIDA_NUMERO IS NULL AND ced.id_cruce NOT IN ($strCruces)
							GROUP BY ced.id_cruce";
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al verificar si existen cruces con las mismas caracteristicas.'; 
				$respuesta['Error'] = ' ['.$error.']'.$consulta;
				exit(json_encode($respuesta));
			}
			if(mysqli_num_rows($query) > 0){
				$respuesta['Codigo']=3;
				$respuesta['Mensaje']='Los siguientes cruces cuentan con las mismas caracteristicas que el(los) selccionado(s).</br>';
				while ($row = mysqli_fetch_array($query)){
					$respuesta['Mensaje'] = ' [Cruce: '.$row['id_cruce'].']</br>';
				}
				$respuesta['Mensaje'] = 'Desea continuar con la salida?';
			}
			// Si es un solo cruce y es consolidado, Avisar que es consolidado y solo se selecciono uno
			if(count($aIdCruces) == 1){
				if($cli_consolidar != $numcliente){
					//error_log('Cli_Consolidad:'.$cli_consolidar);
					$respuesta['Codigo']=3;
					$respuesta['Mensaje']='Solamente se ha seleccionado un cruce y es consolidado. Desea continuar con la salida?';
				}
			}
			$consulta = "SELECT ce.tiposalida,ce.caja,ce.numero_factura,ce.uuid,ce.fecha_factura,ce.noaaa,aaa.nombreaa,
								ce.referencia,ce.cons_fact,c.numcliente,cli.cnombre,
								IFNULL(ce.archivo_cert_origen,'') as archivo_cert_origen, 
								IFNULL(ce.archivo_packinglist,'') AS archivo_packinglist,
								IFNULL(ce.archivo_ticketbascula,'') AS archivo_ticketbascula
						FROM cruces_expo_detalle ce
							INNER JOIN cruces_expo c ON
								ce.id_cruce = c.id_cruce
							INNER JOIN cltes_expo cli ON
								c.numcliente = cli.gcliente
							INNER JOIN aaa ON
								ce.noaaa = aaa.numeroaa
						WHERE ce.id_cruce IN ($strCruces)";
		
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar la informacion de las facturas en los cruces.'; 
				$respuesta['Error'] = ' ['.$error.']'.$consulta;
				exit(json_encode($respuesta));
			}
			//Traer de CASA referencia,pedimento,cons_fact_ped,patente
			while ($row = mysqli_fetch_array($query)){
				
				$numcliente = trim($row['numcliente']);
				$cnombre = trim($row['cnombre']);
				
				$referencia = trim($row['referencia']);
				$cons_fact = trim($row['cons_fact']);
				
				$tipo_salida = trim($row['tiposalida']);
				$caja = trim($row['caja']);
				$numero_factura = trim($row['numero_factura']);
				$uuid = trim($row['uuid']);
				$fecha_factura = trim($row['fecha_factura']);
				$noaaa = trim($row['noaaa']);
				$nombreaa = trim($row['nombreaa']);
				
				$archivo_cert_origen = trim($row['archivo_cert_origen']);
				$archivo_packinglist = trim($row['archivo_packinglist']);
				$archivo_ticketbascula = trim($row['archivo_ticketbascula']);
				
				//global $odbccasa;
				$Pnts = '';
				$qCasa = "SELECT p.ADU_DESP,p.PAT_AGEN,p.NUM_PEDI,p.NUM_REFE,f.CONS_FACT,f.NUM_FACT,f.NUM_FACT2,f.NUM_REM, 
								CASE WHEN p.FIR_REME IS NULL THEN '' ELSE p.FIR_REME END AS FIR_REME
							FROM SAAIO_FACTUR f
								INNER JOIN SAAIO_PEDIME p ON
									p.NUM_REFE = f.NUM_REFE 
							WHERE p.NUM_REFE = '".$referencia."' AND f.CONS_FACT = ".$cons_fact;
				
				$resped = odbc_exec ($odbccasa, $qCasa);
				if ($resped === false){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error al consultar los datos del pedimento en la factura [".$numero_factura."]. BD.CASA.";
					$respuesta['Error'] = ' ['.odbc_error().']'.$qCasa;
					exit(json_encode($respuesta));
				}
				$nItem = 0;
				while(odbc_fetch_row($resped)){
					$referencia = odbc_result($resped,"NUM_REFE");
					$aduana = odbc_result($resped,"ADU_DESP");
					$patente = odbc_result($resped,"PAT_AGEN");
					$pedimento = odbc_result($resped,"NUM_PEDI");
					$cons_fact = odbc_result($resped,"CONS_FACT");
					$num_remesa = odbc_result($resped,"NUM_REM");
					$fir_remesa = odbc_result($resped,"FIR_REME");
					$nItem += 1;
				}
				if($nItem == 0){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="La factura $numero_factura no existe en CASA. BD.CASA. [$uuid]";
					$respuesta['Error'] = '';
					exit(json_encode($respuesta));
				}					
				$Factura = array(
							'clienteid' => $numcliente,
							'cliente' => $cnombre,
							'referencia' => $referencia,
							'patente' => $patente,
							'pedimento' => $pedimento,
							'aaaid' => $noaaa,
							'aaa' => $nombreaa,
							'factura' => $numero_factura,
							'tiposalida' => $tipo_salida,
							'caja' => $caja,
							'uuid' => $uuid,
							'cons_fac_ped' => $cons_fact,
							'num_rem_ped' => ((is_null($num_remesa))? '': $num_remesa),
							'packing_list_id' => '',
							'packing_list_name' => $archivo_packinglist,
							'certificado_origen_id' => '',
							'certificado_origen_name' => $archivo_cert_origen,
							'ticket_bascula_id' => '',
							'ticket_bascula_name' => $archivo_ticketbascula,
							'prefile_id' => '',
                            'prefile_name' => '',
							'prefile_entry_number' => '',
							'prefile_real_entry_number' => ''
						);
				array_push($aFacturas,$Factura);		
				//Verificar documentacion de la factura.CASA
				$Ped = '';
				$qCasa = "SELECT f.NUM_REFE,CASE WHEN f.NUM_REM IS NULL THEN '' ELSE f.NUM_REM END as NUMREM,
								CASE WHEN p.FIR_REME IS NULL THEN '' ELSE p.FIR_REME END FIR_REME
							FROM SAAIO_FACTUR f
								INNER JOIN SAAIO_PEDIME p ON
									f.NUM_REFE = p.NUM_REFE
							WHERE f.NUM_REFE = '".$referencia."' AND f.CONS_FACT = ".$cons_fact;
				
				$resped = odbc_exec ($odbccasa, $qCasa);
				if ($resped == false){
					$respuesta['Mensaje']= "Error al consultar la inforamcion de la factura para verificar su documentacion. BD.CASA.".odbc_error();
					$respuesta['Error'] = '';
					exit(json_encode($respuesta));
				}else{
					while(odbc_fetch_row($resped)){
						$Referencia = odbc_result($resped,"NUM_REFE");
						$NumRemesa = (odbc_result($resped,"FIR_REME") == '' ? '' : odbc_result($resped,"NUMREM"));
						if(!file_exists ($dir_archivos_pedimentos.$Referencia.($NumRemesa == '' ? '' : '-'.$NumRemesa).'.pdf')){
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']="Es necesario subir el pedimento simplificado de la referencia ".$Referencia.($NumRemesa == '' ? '' : ' remesa '.$NumRemesa).'.';
							$respuesta['Error']='<a class="btn btn-info btn-xs" href="javascript:void(0);" onclick="subir_pedimento_simplificado(\''.$Referencia.'\',\''.$NumRemesa.'\');return false;" style="padding-left:.5em;" title=""><i class="fa fa-upload" aria-hidden="true"></i> Subir Pedimento</a>';
							exit(json_encode($respuesta));
						}
					}
				}
				//************************************************************************
			}
			if(count($Factura) == 0){
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']="No existen facturas para los cruces [$strCruces]";
				$respuesta['Error']='';
				exit(json_encode($respuesta));
			}
			
			$aSalidaData = array(
							//'salidanumero' => $row->salidanumero,
							//'fecha' => $row->fecha,
							'lineatransp' => $lineat,
							//'caja' => $caja, ** Nivel Factura **
							'aduana' => $aduana,
							'notransfer' => $notransfer,
							'nombretransfer' => $nombretransfer,
							'noentrega' => $noentrega,
							'Nombreentrega' => $nombreentrega,
							'direntrega' => $direntrega,
							//'tiposalida' => $tiposalida, ** Nivel Factura **
							'cruces' => '1',
							'usuario' => $username,
							'urgente' => 'NO',
							'horaentrega' => '',
							'recibio' => '',
							'indicaciones' => $indicaciones,
							'observaciones' => $observaciones_cruces,
							'ferrocarril' => '',
							'viaje' => '',
							'leyenda' => '',
							'nolineatransp' => $numlinea,
							'relacion_docs_name' => '',
							//'prefile_name' => '',
							//'prefile_obligatorio' => '',
							'notificacion_arribo_name' => '',
							'solicitud_retiro_name' => '',
							//'entry_number' => '',
							'aFacturas' => $aFacturas
						);
						
			$_SESSION['aSalidaData'] = $aSalidaData;
			
		}else{
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No existe informacion para los cruces '.$strCruces;
			$respuesta['Error']='';
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
		$respuesta['Error']='';
	}
	exit(json_encode($respuesta));
}