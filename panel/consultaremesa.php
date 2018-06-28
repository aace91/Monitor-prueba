<?php
include_once('../checklogin.php');
include_once('../connect_dbsql.php');
include_once('../url_archivos.php');

if($loggedIn == false){
	$respuesta['codigo']=-1;	
	$respuesta['mensaje']='Su sesión expiro favor de <a href="../login.php">ingresar</a> nuevamente'; 
    exit(json_encode($respuesta));
}

include('../bower_components/phpmailer/PHPMailerAutoload.php');
$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'pruebas/') !== false)? true : false);

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
		case 'consultaremesa' : $respuesta = consultaremesa();
			echo json_encode($respuesta);
			break;
			
		case 'subir_documentos' : $respuesta = subir_documentos();
			echo json_encode($respuesta);
	}
}

/*************************************************************************************************/
/* METODOS                                                                                       */
/*************************************************************************************************/

function consultaremesa() {
	global $_POST, $bDebug, $cmysqli;
	include('./../connect_casa.php');
	
	$respuesta['codigo']=1;	
	try {
		if (isset($_POST['remesa']) && !empty($_POST['remesa'])) {
			$remesa = $_POST['remesa'];
			$remision = $_POST['remision'];
			$pedimentopat = $_POST['pedimento'];
			$aduana = substr($pedimentopat, 0, 3);
			$numpedi = substr($pedimentopat, -7);
			$patente = substr($pedimentopat, 4, 4);
			
			/************************************************/

			$pedimento = $patente.'-'.$numpedi;
			$aduana_text = (($aduana == 800)? 'COLOMBIA' : 'LAREDO');
			$sHtmlResp = '';
			$fec_aprov = '';
			$fec_rechazo = '';
			$obs = '';
			$referencia = '';
			$tablaprov = '';
			$cove = '';
			$cons_fact = '';
			$sDocumentos = '';
			
			/************************************************/

			if ($remesa == 0){
				$respuesta['codigo']=-1;
				$respuesta['mensaje']='El numero de remesa es invalido'; 
				$respuesta['error'] = '';
			}

			if (rtrim($numpedi)==''||rtrim($patente)==''){
				$respuesta['codigo']=-1;
				$respuesta['mensaje']='El numero de pedimento es invalido'; 
				$respuesta['error'] = '';
				return $respuesta;
			}

			$query = "SELECT num_refe, imp_expo 
			          FROM SAAIO_PEDIME 
					  WHERE num_pedi='".$numpedi."' and 
							pat_agen='".$patente."' and 
							adu_desp='".$aduana."'";
			
			$result = odbc_exec ($odbccasa, $query);
			if ($result != false){
				$referencia=odbc_result($result,"num_refe");
				$tablaprov = (odbc_result($result,"imp_expo")==1 ? "CTRAC_PROVED" : "CTRAC_DESTIN");
			} else {
				$respuesta['codigo']=-1;
				$respuesta['mensaje']='Error al realizar la consulta a la base de datos de pedimentos: '.$query; 
				$respuesta['error'] = '';
				return $respuesta;
			}
			
			$query = "SELECT b.num_fact,extract(day from b.fec_fact)||'/'||extract(month from b.fec_fact)||'/'||extract(year from b.fec_fact) as fec_fact,c.nom_pro,b.val_dlls,b.num_pedi,b.cons_fact,d.e_document 
					  FROM SAAIO_FACTUR b LEFT JOIN 
						   ".$tablaprov." c ON b.cve_prov=c.cve_pro left join 
						   SAAIO_COVE d on b.num_refe=d.num_refe and b.cons_fact=d.cons_fact 
					  WHERE b.num_refe='".$referencia."' and b.num_rem='".$remesa."'";

			$result = odbc_exec ($odbccasa, $query);
			if ($result!=false){
				if(odbc_num_rows($result)<=0){
					$respuesta['codigo']=-1;
					$respuesta['mensaje']='La remesa no se encuentra en la base de datos de pedimentos '.$query; 
					$respuesta['error'] = '';
					return $respuesta;
				} else {
					while(odbc_fetch_row($result)){
						$cons_fact=odbc_result($result,"cons_fact");
						$cove=odbc_result($result,"e_document");

						$sHtmlResp.='
						<div class="panel panel-default">
							<div class="panel-heading">Encabezado de factura</div>
							<table class="table">
								<tr>
									<th>Factura</th>
									<th>Fecha</th>
									<th>Proveedor</th>
									<th>Valor Dlls</th>
									<th>Cantidad</th>
									<th>COVE</th>
								</tr>
								<tr>
									<td>'.odbc_result($result,"num_fact").'</td>
									<td>'.odbc_result($result,"fec_fact").'</td>
									<td>'.odbc_result($result,"nom_pro").'</td>
									<td>'.odbc_result($result,"val_dlls").'</td>
									<td>'.odbc_result($result,"num_pedi").'</td>
									<td>'.odbc_result($result,"e_document").'</td>
								</tr>
							</table>';
					}
				}
			} else{
				$respuesta['codigo']=-1;
				$respuesta['mensaje']='Error al realizar la consulta a la base de datos de pedimentos: '.$query; 
				$respuesta['error'] = '';
				return $respuesta;
			}

			$query = "SELECT a.cons_part,a.can_fact,b.des_uni,a.des_merc,a.num_part,a.fraccion,a.mon_fact,a.tip_mone 
			          FROM SAAIO_FACPAR a LEFT JOIN 
					       ctarc_unidad b on a.uni_fact=b.num_uni 
					  WHERE a.num_refe='".$referencia."' and a.cons_fact='".$cons_fact."'";
			$result = odbc_exec ($odbccasa, $query);
			if ($result!=false){
				if(odbc_num_rows($result)<=0){
					$respuesta['codigo']=-1;
					$respuesta['mensaje']='La remesa no se encuentra en la base de datos de pedimentos '.$query;
					$respuesta['error'] = '';
					return $respuesta;
				} else {
					while(odbc_fetch_row($result)){
						$sHtmlResp.='
							<div class="panel-heading">Detalle de factura</div>
							<table class="table">
								<tr>
									<th>Cantidad</th>
									<th>Unidad de Medida</th>
									<th>Descripcion</th>
									<th>Numero Parte</th>
									<th>Fracción</th>
									<th>Precio</th>
									<th>Moneda</th>
								</tr>
								<tr>
									<td>'.odbc_result($result,"can_fact").'</td>
									<td>'.odbc_result($result,"des_uni").'</td>
									<td>'.odbc_result($result,"des_merc").'</td>
									<td>'.odbc_result($result,"num_part").'</td>
									<td>'.odbc_result($result,"fraccion").'</td>
									<td>'.odbc_result($result,"mon_fact").'</td>
									<td>'.odbc_result($result,"tip_mone").'</td>
								</tr>
							</table>';
					}
				}
			} else {
				$respuesta['codigo']=-1;
				$respuesta['mensaje']='Error al realizar la consulta a la base de datos de pedimentos: '.$query.'';
				$respuesta['error'] = '';
				return $respuesta;
			}

			$sHtmlResp.='
			</div>';

			/************************************************/

			$consulta="SELECT remeaprov.id_doc, remeaprov.fec_subida, remeaprov.fec_aprov, remeaprov.fec_rechazo, remeaprov.obs,
			                  docstpo.id_tpo, docstpo.descripcion, docs_contenido.nombre
	                   FROM bodega.remeaprov INNER JOIN
		                    bodega.remisiongral AS bodRem ON bodRem.remision=remeaprov.remision LEFT JOIN
		                    bodega.docs ON docs.id_doc=remeaprov.id_doc LEFT JOIN
		                    bodega.docs_tipos AS docstpo ON docstpo.id_tpo=docs.id_tpo INNER JOIN
					        bodega.docs_contenido ON docs_contenido.id_doc=remeaprov.id_doc
                       WHERE remeaprov.remision=".$remision." AND
					         remeaprov.pedimento='".$pedimento."' AND
						     bodRem.aduana='".$aduana_text."' AND
						     remeaprov.partida=".$remesa."
					   ORDER BY remeaprov.fec_subida DESC";

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$response['codigo']=-1;
				$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
				return $response;
			}

			while($row = $query->fetch_object()){
				$sStatus = '';
				$sHtmlLabel = '';

				if ($row->fec_rechazo != NULL) {
					$fec_rechazo = date( 'd/m/Y g:i A', strtotime($row->fec_rechazo));
					
					$sStatus = 'Rechazado';
					$sHtmlLabel = '<span class="label label-danger">'.$sStatus.'</span>';
				} else if ($row->fec_aprov != NULL) {
					$fec_aprov = date( 'd/m/Y g:i A', strtotime($row->fec_aprov));

					$sStatus = 'Aprobado';
					$sHtmlLabel = '<span class="label label-success">'.$sStatus.' '.$fec_aprov.'</span>';
				} else {
					$sStatus = 'Pendiente';
					$sHtmlLabel = '<span class="label label-default">'.$sStatus.'</span>';
				}
				
				$sDocumentos .= '
				<tr>
					<td>'.$row->descripcion.'</td>
					<td>'.$row->nombre.'</td>
					<td align="center">'. date( 'd/m/Y g:i A', strtotime($row->fec_subida)).'</td>
					<td align="center">'.$sHtmlLabel.'</td>
					<td align="center"><a href="javascript:void(0);" onclick="fcn_remped_docs_ver('.$row->id_doc.',\''.$row->descripcion.'\',\''.$sStatus.'\',\''.$row->obs.'\'); return false;"><i class="fa fa-eye" aria-hidden="true"></i> Ver</a></td>
				</tr>'; 
			}

			if ($sDocumentos == '') {
				$consulta="SELECT remeaprov.fec_aprov, remeaprov.obs
						   FROM bodega.remeaprov 
						   WHERE remeaprov.remision=".$remision." AND
								 remeaprov.pedimento='".$pedimento."' AND
								 remeaprov.partida=".$remesa." AND
								 remeaprov.fec_aprov IS NOT NULL";
				
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$response['codigo']=-1;
					$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
					return $response;
				}

				while($row = $query->fetch_object()){
					$fec_aprov =  date( 'd/m/Y g:i A', strtotime($row->fec_aprov));
					break;
				}
			}

			if ($sDocumentos == '' && $fec_aprov == '') {
				$consulta="SELECT remeaprov.fec_rechazo, remeaprov.obs
						   FROM bodega.remeaprov 
						   WHERE remeaprov.remision=".$remision." AND
								 remeaprov.pedimento='".$pedimento."' AND
								 remeaprov.partida=".$remesa." AND
								 remeaprov.fec_rechazo IS NOT NULL";
				
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$response['codigo']=-1;
					$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
					return $response;
				}

				while($row = $query->fetch_object()){
					$fec_rechazo =  date( 'd/m/Y g:i A', strtotime($row->fec_rechazo));
					$obs = ((is_null($row->obs))? '' : $row->obs);
					break;
				}
			}

			/************************************************/

			$respuesta['sDocumentos']=$sDocumentos;
			$respuesta['sHtmlResp']=$sHtmlResp;
			$respuesta['fec_aprov']=$fec_aprov;
			$respuesta['fec_rechazo']=$fec_rechazo;
			$respuesta['obs']=$obs;
			return $respuesta;
		} else {
			$respuesta['codigo']=-1;
			$respuesta['mensaje']='No se recibieron datos';
			$respuesta['error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['codigo']=-1;
		$respuesta['mensaje']='Error consultaremesa().'; 
		$respuesta['error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

function subir_documentos() {
	global $_POST, $bDebug, $cmysqli, $id, $dir_archivos_web;
	include('./../connect_casa.php');
	
	$respuesta['codigo']=1;	
	$sPathFiles = $dir_archivos_web."monitor\\inventario\\";

	try {
		if (isset($_POST['remesa']) && !empty($_POST['remesa'])) {
			$remesa = $_POST['remesa'];
			$remision = $_POST['remision'];
			$pedimentopat = $_POST['pedimento'];
			$referencia = $_POST['referencia'];
			$files = $_FILES;

			/************************************************/

			$aduana = substr($pedimentopat, 0, 3);
			$numpedi = substr($pedimentopat, -7);
			$patente = substr($pedimentopat, 4, 4);

			$pedimento = $patente.'-'.$numpedi;
			$sReferenciasEmail = '';
			
			$aArchivos=array();
			$sFile = '';
			$sFileNameContenido = $remision.'_'.$pedimentopat.'_'.$remesa.'.pdf';

			/************************************************/

			if(count($files) > 0) {
				foreach($files as $file){
					$ext = explode('.', basename($file['name']));
					$target = get_file_name($sPathFiles, array_pop($ext), 'upload');
					
					if(move_uploaded_file($file['tmp_name'], $target)) {
						array_push($aArchivos, $target);
					} else {
						$respuesta['codigo']=-1;
						$respuesta['mensaje']='Error al subir los archivos, intente nuevamente'; 
						$respuesta['error'] = '';
					}
				}
			}

			if ($respuesta['codigo'] == 1) {
				$sFile = get_file_name($sPathFiles, 'pdf', 'unificado');
				$sArchivos = '';
				foreach ($aArchivos as &$archivo) { 
					$sArchivos .= $archivo . ' ';
				}
				
				if ($sArchivos != '') {
					$sComando = '"C:\Program Files\gs\gs9.23\bin\gswin64" -dBATCH -dNOPAUSE -q -dSAFER -sDEVICE=pdfwrite -sOutputFile='.$sFile.' '.$sArchivos;
					$output = shell_exec($sComando);
					if ($output != '') {
						$respuesta['codigo']=-1;
						$respuesta['mensaje']=$output; 
						$respuesta['error'] = $output;
					}
				}
			}

			if ($respuesta['codigo'] == 1) { 
				$iddoc = 0;
				$doccon=base64_encode(file_get_contents($sFile));
				$docnom=$sFileNameContenido;

				/************************/

				mysqli_query($cmysqli, "BEGIN");

				$consulta = "INSERT INTO docs (id_tpo,fecha,cliente_id) VALUES('10', NOW(), '".$id."')";
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['mensaje']='Error al agregar registro en docs. Por favor contacte al administrador del sistema.'.$consulta; 
					$respuesta['error'] = ' ['.$error.']';
				} else {
					$iddoc = mysqli_insert_id($cmysqli);
				}

				$respuesta['iddoc']=$iddoc;

				if ($respuesta['codigo'] == 1) { 
					$consulta = "INSERT INTO docs_contenido (id_doc,contenido,nombre) VALUES('".$iddoc."','".$doccon."','".$docnom."')";
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['codigo']=-1;
						$respuesta['mensaje']='Error al agregar registro en docs_contenido. Por favor contacte al administrador del sistema.'.$consulta; 
						$respuesta['error'] = ' ['.$error.']';
					}
				}

				if ($respuesta['codigo'] == 1) { 
					$consulta = "SELECT referencia
								 FROM bodega.remisiondet
								 WHERE remision=".$remision." AND
									   pedimento='".$pedimento."' AND
									   partida= ".$remesa;
									   
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['codigo']=-1;
						$respuesta['mensaje']='Error al agregar registro en remeaprov. Por favor contacte al administrador del sistema.'.$consulta; 
						$respuesta['error'] = ' ['.$error.']';
					} else {
						while($row = $query->fetch_object()){
							$sReferenciasEmail .= (($sReferenciasEmail == '')? '' : ', ');
							$sReferenciasEmail .= $row->referencia;

							$consulta = "INSERT INTO docs_refe (id_doc,referencia,id_tpo) VALUES('".$iddoc."','".$row->referencia."','10')";
							$query_insert = mysqli_query($cmysqli, $consulta);
							if (!$query_insert) {
								$error=mysqli_error($cmysqli);
								$respuesta['codigo']=-1;
								$respuesta['mensaje']='Error al agregar registro en docs_refe. Por favor contacte al administrador del sistema.'.$consulta; 
								$respuesta['error'] = ' ['.$error.']';
								break;
							}
						}
					}
				}

				//Rechazando remisiones anteriores
				if ($respuesta['codigo'] == 1) { 
					$consulta = "UPDATE bodega.remeaprov
								 SET fec_rechazo=NOW(),
									 obs='Invalidado por Ejecutivo'
								 WHERE remision=".$remision." AND
									   pedimento='".$pedimento."' AND
									   remeaprov.partida=".$remesa." AND 
									   remeaprov.fec_aprov IS NULL AND
									   remeaprov.fec_rechazo IS NULL";
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['codigo']=-1;
						$respuesta['mensaje']='Error al rechazar registro en remeaprov. Por favor contacte al administrador del sistema.'.$consulta; 
						$respuesta['error'] = ' ['.$error.']';
					}
				}

				if ($respuesta['codigo'] == 1) { 
					$consulta = "INSERT INTO remeaprov (remision, pedimento, partida, fec_subida, id_doc) 
					             VALUES(".$remision.", '".$pedimento."', ".$remesa.", NOW(), ".$iddoc.")";
					$query = mysqli_query($cmysqli, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['codigo']=-1;
						$respuesta['mensaje']='Error al agregar registro en remeaprov. Por favor contacte al administrador del sistema.'.$consulta; 
						$respuesta['error'] = ' ['.$error.']';
					}
				}

				if ($respuesta['codigo'] == 1) { 
					mysqli_query($cmysqli, "COMMIT");
				} else {
					mysqli_query($cmysqli, "ROLLBACK");
				}
			}

			//Enviar correo (Pendiente)
			if ($respuesta['codigo'] == 1) { 
				$adjuntos= array();
				$bcc = array();
				$to = array();

				array_push($adjuntos, array('dir' => $sFile, 'name' => $sFileNameContenido));

				$consulta = "SELECT CONCAT(IFNULL(to1, ''), ',', IFNULL(to2, ''), ',', IFNULL(to3, ''), ',', IFNULL(to4, ''), ',', IFNULL(to5, ''), ',',
										   IFNULL(to6, ''), ',', IFNULL(to7, ''), ',', IFNULL(to8, ''), ',', IFNULL(to9, ''), ',', IFNULL(to10, ''), ',',
									       IFNULL(cc1, ''), ',', IFNULL(cc2, ''), ',', IFNULL(cc3, ''), ',', IFNULL(cc4, ''), ',', IFNULL(cc5, ''), ',',
										   IFNULL(cc6, ''), ',', IFNULL(cc7, ''), ',', IFNULL(cc8, ''), ',', IFNULL(cc9, ''), ',', IFNULL(cc10, '')) AS emails
							 FROM bodega.geocel_clientes AS bodGeoC INNER JOIN 
							      bodega.tblbod ON tblbod.bodcli=bodGeoC.f_numcli
							 WHERE tblbod.bodReferencia='".$referencia."'";

				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['mensaje']='Error al consultar correos. Por favor contacte al administrador del sistema.'.$consulta; 
					$respuesta['error'] = ' ['.$error.']';
				} else {
					while($row = $query->fetch_object()){
						$to = fcn_emails_array($row->emails);
						break;
					}
				}

				/*********************************************************/
				
				$asunto = 'Nuevo documento de remesa, Pedimento: '.$pedimento.' - Referencia: '.$referencia;
				$sInfo = '';
				if (strlen($sReferenciasEmail) >= 15) {
					$sInfo = '
					<tr>
						<td colspan="3" style="padding: 15px; margin-bottom: 0px; border: 1px solid transparent; border-radius: 4px; color: #31708f; background-color: #d9edf7; border-color: #bce8f1;">
							<div style="padding: 15px;">
								<strong>Info!</strong> Cuando se apruebe la remesa de una de las referencias de la lista las demás referencias quedaran aprobadas.
							</div>
						</td>
					</tr>';
				}
			
				$sHTML = '
				<table style="border: solid 1px #bbbccc; width: 800px;" cellspacing="0" cellpadding="0">
					<tbody>
						<tr style="background-color: #0073b7; color: #fff;">
							<td style="background-color: #fff;" width="100px">
								<img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" />
							</td>
							<td width="10">&nbsp;</td>
							<td align="center">
								<h1>Del Bravo</h1>
							</td>
							<td width="10px">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4">
								<table width="100%" cellspacing="0" cellpadding="0">
									<tbody>
										<tr>
											<td colspan="3">&nbsp;</td>
										</tr>
										<tr>
											<td colspan="3" align="center">
												<h2 style="color:green;">Nuevo documento de remesa</h2>
											</td>
										</tr>
										<tr>
											<td colspan="3" style="padding: 15px;">
												<div style="padding-left: 15px;">
													<strong>Pedimento:</strong> '.$pedimento.' 
													<br/>
													<strong>N. Remesa:</strong> '.$remesa.' 
													<br/>
													<strong>Referencia(s):</strong> '.$sReferenciasEmail.'    
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="3" style="padding: 15px; margin-bottom: 0px; border: 1px solid transparent; border-radius: 4px; color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6;">
												<div style="padding: 15px;">
													<strong>Atención!</strong> Se ha generado un a nueva remesa, para aprobar o rechazar es necesario que ingrese a nuestro sistema integral.  
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="3" style="padding: 15px;">
												<div style="padding: 15px;">
													Para atender haga <a href="https://www.delbravoweb.com/sii/index.php">Click Aquí</a>
													<!--br/>
													Para video de ayuda <a href="https://www.delbravoweb.com/archivos/videos/aprobar_remesa_ayuda.mp4">Click Aquí</a-->
												</div>
											</td>
										</tr>
										'.$sInfo.'
										<tr>
											<td colspan="3" style="padding: 15px;">
												<p>Este e-mail fue generado automaticamente, no conteste a el, si tiene alguna duda por favor comuniquese con su ejecutivo de Cuenta. Atte. Grupo Aduanero del Bravo S.A..</p>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>';
				
				if ($bDebug) {
					$bcc=array();
					$to=array();
				}

				array_push($bcc,'jcdelacruz@delbravo.com');
				//array_push($bcc,'carlos7999@hotmail.com');
				//array_push($bcc,'abisaicruz@delbravo.com');
			
				$respuesta=enviamail($asunto,$sHTML,$to,$bcc,'mail.delbravo.com','25','avisosautomaticos@delbravo.com','aviaut01','',$adjuntos);
			}

			if ($respuesta['codigo'] == 1) {
				$respuesta = consultaremesa();
				$respuesta['mensaje'] = (($respuesta['codigo'] == 1)? 'Archivos procesados correctamente!!!' : $respuesta['mensaje'].'. '.'Los archivos se guardaron correctamente!!!');
				
			}
		} else {
			$respuesta['codigo']=-1;
			$respuesta['mensaje']='No se recibieron datos';
			$respuesta['error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['codigo']=-1;
		$respuesta['mensaje']='Error consultaremesa().'; 
		$respuesta['error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

/*************************************************************************************************/
/* FUNCIONES                                                                                     */
/*************************************************************************************************/

function get_file_name($sPathFiles, $ext, $extranombre) {
	$bFileExist = false;
	$target = '';
	do {
		$sFileName = md5(uniqid()). "_" . $extranombre . "." . $ext;
		$target = $sPathFiles . DIRECTORY_SEPARATOR . $sFileName;

		if (file_exists($target)) { 
			$bFileExist = true;
		} else {
			$bFileExist = false;
		}
	} while ($bFileExist == true);

	return $target;
}

function fcn_emails_array($aData) { 
	$aEmails=array();

	$aEmail = explode(",",$aData);

	foreach ($aEmail as $email) {
		if(is_null($email) == false && $email != '') {
			array_push($aEmails, $email);
		}
	}

	return $aEmails;
}

/*************************************************************************************************/
/* CORREOS                                                                                       */
/*************************************************************************************************/

function enviamail($asunto,$mensaje,$to,$bcc,$mailserver,$portmailserver,$sender,$pass,$ruta_logo,$adjuntos){
	$mail = new PHPMailer();
	//Luego tenemos que iniciar la validación por SMTP:
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->Host = $mailserver; // SMTP a utilizar. Por ej. smtp.elserver.com
	$mail->Username = $sender; // Correo completo a utilizar
	$mail->Password = $pass; // Contraseña
	$mail->Port = $portmailserver; // Puerto a utilizar
	//Con estas pocas líneas iniciamos una conexión con el SMTP. Lo que ahora deberíamos hacer, es configurar el mensaje a enviar, el //From, etc.
	$mail->From = $sender; // Desde donde enviamos (Para mostrar)
	$mail->FromName = "Del Bravo";
	$mail->CharSet = 'UTF-8';

	if($ruta_logo!=''){
		$mail->AddAttachment($ruta_logo, 'logo.png'); 
	}

	for($x=0;$x<count($adjuntos);$x++){
		$mail->AddAttachment($adjuntos[$x]['dir'],$adjuntos[$x]['name']);
	}
	//Estas dos líneas, cumplirían la función de encabezado (En mail() usado de esta forma: “From: Nombre <correo@dominio.com>”) de //correo.
	if (count($to)>0){
		foreach($to as $t){
			// Esta es la dirección a donde enviamos
			$mail->AddAddress($t);
		}
	}
	if (count($bcc)>0){
		foreach($bcc as $b){
			// Esta es la dirección a donde enviamos
			$mail->AddBcc($b);
		}
	}
	
	$mail->IsHTML(true); // El correo se envía como HTML
	$mail->Subject = $asunto; // Este es el titulo del email.
	$mail->Body = $mensaje; // Mensaje a enviar
	$exito = $mail->Send(); // Envía el correo.

	//También podríamos agregar simples verificaciones para saber si se envió:
	if($exito){
		$respuesta['codigo']=1;
		$respuesta['mensaje']='El correo fue enviado correctamente.';
	}else{
		$respuesta['codigo']=-1;
		$respuesta['mensaje']=$mail->ErrorInfo;
	}
	return $respuesta;
}
