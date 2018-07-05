<?php
include_once('../checklogin.php');
if($loggedIn == false){
	$error_msg= "Su sesión expiro favor de <a href='login.php'>ingresar</a> nuevamente";
    exit(json_encode(array("error" => $error_msg)));
}

include('../bower_components/phpmailer/PHPMailerAutoload.php');
$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'pruebas/') !== false)? true : false);

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
		case 'guardaefac' : $respuesta = guardaefac($_REQUEST['referencia'],$_REQUEST['id_estatus_factura']);
			echo json_encode($respuesta);
            break;
		case 'consultaefac' : $respuesta = consultaefac($_REQUEST['referencia']);
			echo json_encode($respuesta);
            break;
		case 'consulta_comentarios' : $respuesta = consulta_comentarios($_REQUEST['referencia']);
			echo json_encode($respuesta);
            break;
		case 'grabacomref' : $respuesta = grabacomref($_POST['referencia'],$_POST['comentarios'],$_POST['estatus']);
            echo json_encode($respuesta);
            break;
		case 'abreprecaptura' : $respuesta = abreprecaptura($_POST['referencia']);
            echo json_encode($respuesta);
            break;
		case 'asignaproveedor' : $respuesta = asignaproveedor($_POST['cve_pro'],$_POST['id_precaptura']);
            echo json_encode($respuesta);
            break;
		case 'agregar_renglon' : $respuesta = agregar_renglon($_POST['id_precaptura']);
            echo json_encode($respuesta);
            break;
		case 'eliminar_precaptura' : $respuesta = eliminar_precaptura($_POST['referencia']);
            echo json_encode($respuesta);
			break;
		case 'consulta_documentos' : $respuesta = consulta_documentos($_POST['referencia']);
            echo json_encode($respuesta);
            break;
		case 'consulta_documento_pdf' : $respuesta = consulta_documento_pdf($_POST['id_doc']);
            echo json_encode($respuesta);
            break;
		case 'guardar_documento_info' : $respuesta = guardar_documento_info($_POST['id_doc'], $_POST['task'], $_POST['referencia'], $_POST['id_estatus_documento'], $_POST['txt_estatus_documento'], $_POST['obs'], $_POST['doc_tipo']);
            echo json_encode($respuesta);
            break;
	}
}

function guardar_documento_info($id_doc, $sTask, $referencia, $id_estatus_documento, $sEstatusDoc, $obs, $doc_tipo){
	include ('../db.php');
	global $bDebug;
	
	$response['codigo']=1;	
	
	/**************************************/
	
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	
	switch ($sTask) {
		case "aprobar":
			$consulta="SELECT id_estatus_documento
					   FROM bodega.estatus_documentos
					   WHERE ok=1";
					   
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$response['codigo']=-1;
				$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
				mysqli_close($cmysqli);
				return $response;
			}
			
			$sIdEstatusDoc;
			while($row = $query->fetch_object()){
				$sIdEstatusDoc = $row->id_estatus_documento;
				break;
			}
			
			$consulta="UPDATE bodega.docs
					   SET invalido=NULL,
					   	   aprovado=NOW(),
					       id_estatus_documento=".$sIdEstatusDoc."
					   WHERE id_doc=".$id_doc;
					   
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$response['codigo']=-1;
				$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
				mysqli_close($cmysqli);
				return $response;
			}
			
			$response = consulta_documentos($referencia);
			$response['mensaje']="Documento aprobado correctamente!!!";
			break;

		case "rechazar":
			$bodnopedido;
			$referencias;
			$bcc;
			$to;
			
			/*$consulta="SELECT tblbod.bodnopedido, 
							  (SELECT CONCAT(IFNULL(cc1, ''), ',', IFNULL(cc2, ''), ',', IFNULL(cc3, ''), ',', IFNULL(cc4, ''), ',', IFNULL(cc5, ''), ',',
								  	         IFNULL(cc6, ''), ',', IFNULL(cc7, ''), ',', IFNULL(cc8, ''), ',', IFNULL(cc9, ''), ',', IFNULL(cc10, ''))
							   FROM geocel_clientes
							   WHERE f_numcli=tblbod.bodcli) AS ejecutivos_email,
							  (SELECT GROUP_CONCAT(email)
							   FROM contactos_proveedores
							   WHERE id_catalogo=tblbod.bodprocli
							   GROUP BY id_catalogo) AS prov_email
					   FROM bodega.tblbod
					   WHERE tblbod.bodReferencia='".$referencia."'";*/
			$consulta = "SELECT GROUP_CONCAT(DISTINCT tblbod.bodnopedido SEPARATOR ', ') AS bodnopedido,
			                    GROUP_CONCAT(DISTINCT tblbod.bodReferencia SEPARATOR ', ') AS bodReferencia,
	                            GROUP_CONCAT(DISTINCT (SELECT CONCAT(IFNULL(cc1, ''), ',', IFNULL(cc2, ''), ',', IFNULL(cc3, ''), ',', IFNULL(cc4, ''), ',', IFNULL(cc5, ''), ',',
													                 IFNULL(cc6, ''), ',', IFNULL(cc7, ''), ',', IFNULL(cc8, ''), ',', IFNULL(cc9, ''), ',', IFNULL(cc10, ''))
								                       FROM geocel_clientes
								                       WHERE f_numcli=tblbod.bodcli)) AS ejecutivos_email,
		                       (SELECT GROUP_CONCAT(email)
			                    FROM contactos_proveedores
			                    WHERE id_catalogo=tblbod.bodprocli
			                    GROUP BY id_catalogo) AS prov_email
                         FROM bodega.docs_refe AS docsRef INNER JOIN
	                          bodega.tblbod ON tblbod.bodReferencia=docsRef.referencia
                         WHERE docsRef.id_doc=".$id_doc;
					   
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$response['codigo']=-1;
				$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
				mysqli_close($cmysqli);
				return $response;
			}
			
			while($row = $query->fetch_object()){
				$bodnopedido = $row->bodnopedido;
				$referencias = $row->bodReferencia;
				$to = fcn_emails_array($row->prov_email.','.$row->ejecutivos_email);
				break;
			}
			
			/*********************************************************************************************/
			
			$consulta="UPDATE bodega.docs
					   SET invalido=NOW(),
					       id_estatus_documento=".$id_estatus_documento.",
						   obs='".$obs."'
					   WHERE id_doc=".$id_doc;
					   
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$response['codigo']=-1;
				$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
				mysqli_close($cmysqli);
				return $response;
			}
			
			/*********************************************************************************************/
			
			$asunto = 'Rejected document - '.$referencia;
			
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
											<h2 style="color:red;">Rejected document</h2>
										</td>
									</tr>
									<tr>
										<td colspan="3" style="padding: 15px;">
											<div style="padding: 15px;">
												<strong>Attention!</strong> The following document was rejected 
											</div>
										</td>
									</tr>
									<tr>
										<td colspan="3" style="background-color: #f2dede;">
											<div style="border:1px solid #ebccd1; color: #a94442; background-color: #f2dede; padding-left: 15px; padding-right: 15px;">
												<p><strong>Referencia(s):</strong> '.$referencias.'</p>
												<p><strong>PO(s):</strong> '.$bodnopedido.'</p>		
												<p><strong>Document:</strong> '.$doc_tipo.'</p>
												<p><strong>Comments:</strong> '.$sEstatusDoc.', '.$obs.'</p>
											</div>
										</td>
									</tr>
									<tr>
										<td colspan="3" style="padding: 15px;">
											<div style="padding: 15px;">
												To answer this request, <a href="https://www.delbravoweb.com/sii/index.php">Click here</a>
											</div>
										</td>
									</tr>
									<tr>
										<td colspan="3" style="padding: 15px;">
											<p>This e-mail was generated automatically, do not answer to it, if you have any questions please contact your Account executive. Atte. Del Bravo S.A ..</p>
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
			
			$envio=enviamail($asunto,$sHTML,$to,$bcc, 'mail.delbravo.com','25','avisosautomaticos@delbravo.com','aviaut01',true,NULL);
			/*********************************************************************************************/
			
			$response = consulta_documentos($referencia);
			$response['mensaje']="Documento rechazado correctamente!!!";
			break;
	}
	
	return $response;
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

function consulta_documento_pdf($id_doc){
	include ('../db.php');
	include ('../url_archivos.php');
	
	$response['codigo']=1;
	$pdfbase64;

	$sPathFiles = $dir_archivos_web."monitor\\inventario\\";
	$nombreArchivo  = '';
	
	/**************************************/
	
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	$consulta="SELECT contenido, nombre
			   FROM bodega.docs_contenido
			   WHERE id_doc=".$id_doc;
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		mysqli_close($cmysqli);
		return $response;
	}
	
	while($row = $query->fetch_object()){
		$pdfbase64 = $row->contenido;

		//Depresiado por nuevo metodo de abisai
		/*$nombreArchivo = strtoupper(dechex(date("YmdHisu"))).'_'.$row->nombre;
		$fecha = date("YmdHisu");
		$nombreArchivo =strtoupper(dechex($fecha)).'_'.$row->nombre;

		if (!file_put_contents($sPathFiles.$nombreArchivo, base64_decode($row->contenido))){
			$response['codigo']=-1;
			$response['mensaje']='Error al escribir el archivo' ;
			return $response;
		}*/
	} 
	
	mysqli_close($cmysqli);
	
	$response['pdfbase64']=$pdfbase64;
	$response['link']='inventario/'.$nombreArchivo;
	$response['mensaje']="Consulta realizada correctamente";
	return $response;
}

function consulta_documentos($referencia){
	include ('../db.php');
	
	$response['codigo']=1;
	$sDocumentos = '';
	
	/**************************************/
	
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	$consulta="SELECT docs_refe.id_doc, docs_refe.id_tpo, docs_tipos.descripcion, docs_contenido.nombre,
	                  docs.invalido, estatus_documentos.ok, estatus_documentos.pendiente
			   FROM bodega.docs_refe INNER JOIN 
				    bodega.docs_tipos ON docs_tipos.id_tpo=docs_refe.id_tpo INNER JOIN
					bodega.docs ON docs.id_doc=docs_refe.id_doc AND
					 			   docs.id_tpo=docs_refe.id_tpo INNER JOIN
					bodega.docs_contenido ON docs_contenido.id_doc=docs_refe.id_doc INNER JOIN
                    bodega.estatus_documentos ON estatus_documentos.id_estatus_documento=docs.id_estatus_documento
			   WHERE docs_refe.referencia='".$referencia."' AND 
                     docs_refe.id_tpo NOT IN(10)
			   ORDER BY docs_refe.id_doc desc";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		mysqli_close($cmysqli);
		return $response;
	}
	while($row = $query->fetch_object()){
		$sStatus = '';
		$sHtmlLabel = '';
		if ($row->invalido != NULL) {
			$sStatus = 'Rechazado';
			$sHtmlLabel = '<span class="label label-danger">'.$sStatus.'</span>';
		} else if ($row->ok == 1) {
			$sStatus = 'Aprobado';
			$sHtmlLabel = '<span class="label label-success">'.$sStatus.'</span>';
		} else {
			$sStatus = 'Pendiente';
			$sHtmlLabel = '<span class="label label-default">'.$sStatus.'</span>';
		}
		
		$sDocumentos .= '
		<tr>
			<td>'.$row->descripcion.'</td>
			<td>'.$row->nombre.'</td>
			<td align="center">'.$sHtmlLabel.'</td>
			<td align="center"><a href="javascript:void(0);" onclick="fcn_docs_ver('.$row->id_doc.',\''.$row->descripcion.'\',\''.$sStatus.'\'); return false;"><i class="fa fa-eye" aria-hidden="true"></i> Ver</a></td>
		</tr>'; 
	} 
	
	mysqli_close($cmysqli);
	
	$response['sDocumentos']=$sDocumentos;
	$response['mensaje']="Consulta realizada correctamente";
	return $response;
}

function eliminar_precaptura($referencia){
	include ('../db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	$consulta="
		DELETE FROM precaptura_gral
		WHERE
			referencia='$referencia'
	";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		mysqli_close($cmysqli);
		return $response;
	}
	mysqli_close($cmysqli);
	$response['codigo']=1;
	$response['mensaje']="Consulta realizada correctamente";
	return $response;
}

function agregar_renglon($id_precaptura){
	global $id;
	include ('../db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	$fecha = new DateTime();
	$fecha_mod= $fecha->format("Y-m-d G:i:s");
	$consulta="
		SELECT 
			id_proveedor
		FROM 
			precaptura_detalle
		WHERE
			id_precaptura=$id_precaptura
		LIMIT 1
	";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		mysqli_close($cmysqli);
		return $response;
	}
	while($row = $query->fetch_object()){
		$id_proveedor=$row->id_proveedor; 
	}
	$consulta="
		INSERT INTO precaptura_detalle(
				id_precaptura,
				id_proveedor,
				no_factura)
		VALUES(
			$id_precaptura,
			'$id_proveedor',
			'NUEVO RENGLON')
	";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		mysqli_close($cmysqli);
		return $response;
	}
	$consulta="
		UPDATE precaptura_gral
		SET fecha_mod='$fecha_mod',id_usuario_mod=$id
		WHERE
			id_precaptura=$id_precaptura";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		return $response;
		mysqli_close($cmysqli);
	}
	mysqli_close($cmysqli);
	$response['codigo']=1;
	$response['mensaje']="Consulta realizada correctamente";
	return $response;
}

function asignaproveedor($cve_pro,$id_precaptura){
	include('../db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	global $id;
	$fecha = new DateTime();
	$fecha_mod= $fecha->format("Y-m-d G:i:s");
	$consulta="
		UPDATE precaptura_detalle
		SET
			id_proveedor='$cve_pro'
		WHERE
			id_precaptura=$id_precaptura
	";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		mysqli_close($cmysqli);
		return $response;
	}
	$consulta="
		UPDATE precaptura_gral
		SET fecha_mod='$fecha_mod',id_usuario_mod=$id
		WHERE
			id_precaptura=$id_precaptura";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		return $response;
		mysqli_close($cmysqli);
	}
	mysqli_close($cmysqli);
	$response['codigo']=1;
	$response['mensaje']="Consulta realizada correctamente";
	return $response;
}

function abreprecaptura($referencia){
	include('../db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	global $id;
	$consulta="
		SELECT 
			b.proNom as proveedor
		FROM 
			tblbod as a
		LEFT JOIN procli as b
		ON a.bodprocli=b.proveedor_id
		WHERE
			a.bodreferencia='$referencia'
	";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		mysqli_close($cmysqli);
		return $response;
	}
	while($row = $query->fetch_object()){
		$proveedor=$row->proveedor; 
	} 
	$consulta="
		SELECT 
			id_precaptura 
		FROM 
			precaptura_gral
		WHERE
			referencia='$referencia'
	";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		mysqli_close($cmysqli);
		return $response;
	}
	if (mysqli_num_rows($query)<=0){
		try {
			$cmysqli->autocommit(false);
			$consulta="
				INSERT INTO precaptura_gral (referencia, id_usuario_alta)
				VALUES
					('$referencia', $id)
			";
			$query=$cmysqli->query($consulta);
			if (!$query) {
				$error=$cmysqli->error;
				$response['codigo']=-1;
				$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
				mysqli_close($cmysqli);
				return $response;
				//throw new Exception($conn->error);
			}
			$id_precaptura=$cmysqli->insert_id;
			$consulta2="
				INSERT INTO precaptura_detalle (
					id_precaptura,
					no_factura,
					monto_factura,
					incoterm,
					subdivision,
					certificado,
					no_parte,
					origen,
					fraccion,
					descripcion,
					precio_partida,
					umc,
					cantidad_umc,
					cantidad_umt,
					preferencia,
					marca,
					modelo,
					serie,
					descripcion_cove
				) SELECT
					$id_precaptura,
					revd.factura AS no_factura,
					revdt.monto_factura,
					if(revg.incoterm=NULL OR revg.incoterm='','DAT',revg.incoterm)  AS incoterm,
					'N' as subdivision,
					'N' as certificado,
					revd.noparte AS numero_parte,
				    revd.origen,
					revd.fraccion,
					revd.descripcion,
					revd.valor AS precio_partida,
					ump.num_uni AS umc,
					revd.cantidadfac AS cantidad_umc,
					(
						revd.cantidadfis * ump.fac_equi
					) AS cantidad_umt,
					'N' as preferencia,
					revd.marca,
					revd.modelo,
					concat(revd.noparte,'/',revd.serie) as serie,
					revd.descripcion
				FROM
					bodega.tblbod AS tbod
				LEFT JOIN bodega.detalle_revision AS revd ON tbod.bodReferencia = revd.referencia
				LEFT JOIN bodega.revision_general AS revg ON revd.referencia = revg.referencia
				AND revd.factura = revg.factura
				LEFT JOIN bodega.unidadesmedida AS um ON revd.medida = um.um
				LEFT JOIN casa.ctarc_unidad AS ump ON um.cve_pedimento = ump.num_uni
				LEFT JOIN (
					SELECT
						sum(valor) AS monto_factura,
						referencia,
						factura
					FROM
						bodega.detalle_revision
					GROUP BY
						referencia,
						factura
				) AS revdt ON revd.referencia = revdt.referencia
				AND revd.factura = revdt.factura
				LEFT JOIN bodega.procli AS pro ON tbod.bodprocli = pro.proveedor_id
				LEFT JOIN bodega.detalle_salidas AS sald ON tbod.bodReferencia = sald.REFERENCIA
				WHERE
					tbod.bodReferencia = '$referencia'
			";
			$query=$cmysqli->query($consulta2);
			if (!$query) {
				$error=$cmysqli->error;
				$response['codigo']=-1;
				$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
				mysqli_close($cmysqli);
				return $response;
				//throw new Exception($conn->error);
			}
			$cmysqli->commit();
			$cmysqli->autocommit(true);
		} catch (Exception $e) {
			$cmysqli->rollback();
			$cmysqli->autocommit(true);
			$response['codigo']=-1;
			$response['mensaje']='Error en la consulta: ' .$e->getMessage() ;
			mysqli_close($cmysqli);
			return $response;
		}
		$response['codigo']=1;
		$response['mensaje']="Consulta realizada correctamente";
		$response['id_precaptura']=$id_precaptura;
		$response['proveedor']=$proveedor;
		mysqli_close($cmysqli);
		return $response;
	}else{
		while($row = $query->fetch_object()){
			$id_precaptura=$row->id_precaptura; 
		} 
		mysqli_close($cmysqli);
		$response['codigo']=1;
		$response['mensaje']="Consulta realizada correctamente";
		$response['id_precaptura']=$id_precaptura;
		$response['proveedor']=$proveedor;
		return $response;
	}
}

function guardaefac($referencia,$id_estatus_factura){
	include ('../db.php');
	include("../bower_components/nusoap/src/nusoap.php");
	/*$conn_access = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=$rutabodegamdb", '', '');
	if ($conn_access==false){
		$response['codigo']=-1;
		$response['mensaje']="Error al conectarse a la base de datos bodega.mdb";
		return($response);
	}*/
	
	$consulta="UPDATE tblbod SET id_estatus_factura=$id_estatus_factura where bodreferencia='$referencia'";
	
	/*
	$result = odbc_exec ($conn_access, $consulta);
	if (odbc_num_rows($result)==-1){
		$response['codigo']=-1;
		$response['mensaje']="Error en consulta, error:".odbc_errormsg ($conn_access).", ".$consulta;
		return $response;
	}
	odbc_close($conn_access);*/
	
	$client = new nusoap_client("http://$ip_out2:$port_out2/webtools/ws_mdb/ws_mdb.php?wsdl","wsdl");
	$err = $client->getError();
	if ($err) {
		$error_msg="Constructor error:". $err ;
		exit(json_encode(array("error" => $error_msg)));
	}
	//$client->debug();
	$param = array('usuario' => 'admin',
	'password' => 'r0117c',
	'consulta' => $consulta,
	'tipo' => 'UPDATE',
	'bd' => 'bodega');
	$result = $client->call('ws_mdb', $param);
	$err = $client->getError();
	if ($err) {
		$response['codigo']=-1;
		$response['mensaje']="Constructor error:". $err ;
		return $response;
	}
	if($result['Codigo']!=1){
		$response['codigo']=-1;
		$response['mensaje']="Error del WS: ".$result['Mensaje'];
		return $response;
	}
	
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	mysqli_select_db($cmysqli,"bodegareplica");
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta de bodegareplica: ' .$consulta.' , error:'.$error ;
		mysqli_close($cmysqli);
		return $response;
	}
	mysqli_select_db($cmysqli,"bodega");
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta de bodega: ' .$consulta.' , error:'.$error ;
		mysqli_close($cmysqli);
		return $response;
	}
	$response['codigo']=1;
	$response['mensaje']='Informacion guardada con exito';
	return $response;
	mysqli_close($cmysqli);
}

function consultaefac($referencia){
	include ('../db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	$response['codigo']=-1;
	$response['mensaje']='';
	$response['id_estatus_factura']=0;
	$consulta="SELECT id_estatus_factura from tblbod where bodreferencia='$referencia'";
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;\
		mysqli_close($cmysqli);
		return $response;
	}
	while($row = $query->fetch_object()){
		$id_estatus_factura=$row->id_estatus_factura; 
	} 
	mysqli_close($cmysqli);
	$response['codigo']=1;
	$response['mensaje']='Consulta realizada con exito';
	$response['id_estatus_factura']=$id_estatus_factura;
	return $response;
}

function buscalinea2($buscar){
	include ('db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	$response['items']=array();
	if ($buscar!=''){
		$consulta="SELECT clave,Nombre from consolidadoras_salidas where nombre like '%$buscar%' limit 10";
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$response['codigo']=-1;
			$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
			mysqli_close($cmysqli);
			return $response;
		}
		while($row = $query->fetch_object()){
			$id=$row->Nombre; 
			$nombre=$row->Nombre; 
			array_push($response['items'],array('value'=>$id,'label'=>$nombre));
		} 
	}
	mysqli_close($cmysqli);
	return $response;
}

function get_correos($no_cliente,$to,$cc){
    include('../db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
    $correos=array();
    $consulta="SELECT ";
    for ($i = 1; $i <= 10; $i++) {
        if($to==true){
            $consulta.=" to$i";
            if($i!=10 or $cc==true){
                $consulta.=", ";
            }
        }
        if($cc==true){
            $consulta.=" cc$i";
            if($i!=10){
                $consulta.=", ";
            }
        }
    }
    $consulta.="
        FROM
            geocel_clientes
        WHERE
            geocel_clientes.f_numcli = $no_cliente";
    $query = mysqli_query($cmysqli,$consulta);
    if (!$query) {
        $error=mysqli_error($cmysqli);
        $respuesta['codigo']=-1;
        $respuesta['mensaje']='Error en consulta de correos: ' .$error ;
        return $respuesta;
    }
    while($row = $query->fetch_object()){
        for ($i = 1; $i <= 10; $i++) {
            $row2= get_object_vars($row);
            if($to==true){
                $correo=$row2['to'.$i];
                if($correo!='' or $correo!=NULL){
                    array_push($correos,$correo);
                }
            }
            if($cc==true){
                $correo=$row2['cc'.$i];
                if($correo!='' or $correo!=NULL){
                    array_push($correos,$correo);
                }
            }
        }
    }
	$respuesta['codigo']=1;
	$respuesta['mensaje']="Consulta exitosa".$consulta;
	$respuesta['correos']=$correos;
    return $respuesta;
}

function enviamail($asunto,$mensaje,$to,$bcc,$mailserver,$portmailserver,$sender,$pass,$html,$auth){
	if($html==NULL){
		$html=true;
	}
	$mail = new PHPMailer();
	//Luego tenemos que iniciar la validación por SMTP:
	$mail->IsSMTP();
	$mail->Host = $mailserver; // SMTP a utilizar. Por ej. smtp.elserver.com
	$mail->Port = $portmailserver; // Puerto a utilizar
	$mail->Username = $sender; // Correo completo a utilizar
	$mail->Password = $pass; // Contraseña
	$mail->SMTPAuth = $auth;
	//Con estas pocas líneas iniciamos una conexión con el SMTP. Lo que ahora deberíamos hacer, es configurar el mensaje a enviar, el //From, etc.
	$mail->From = $sender; // Desde donde enviamos (Para mostrar)
	$mail->FromName = $sender;

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
	$mail->IsHTML($html); // El correo se envía como HTML
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

function reenvia_mail($id_equipov){
	include('connect_dbsql.php');
	global $ip_out,$port_out;
	$consulta="
		SELECT DATE_FORMAT(e.fecha,'%d/%m/%Y') as fecha,
			e.hora,
			e.no_cliente,
			c.nom as cliente,
			e.tipo_equipo,
			e.id_foto_no_equipo,
			e.id_foto_placas,
			e.id_foto_marca,
			e.id_foto_modelo,
			e.observaciones,
			e.linea,
			e.nombre_usuario as usuario
        FROM
            bodega.equipoentrada as e
			left join bodega.clientes as c on e.no_cliente=c.cliente_id
        WHERE
            e.id=$id_equipov";
    $query = mysqli_query($cmysqli,$consulta);
    if (!$query) {
        $error=mysqli_error($cmysqli);
        $respuesta['codigo']=-1;
        $respuesta['mensaje']='Error al consultar registro de equipo vacio: ' .$error ;
		$respuesta['consulta']=$consulta;
        return $respuesta;
    }
	if (mysqli_num_rows($query)<=0){
		$respuesta['codigo']=-1;
        $respuesta['mensaje']='No se pudo consultar el registro consulte al administrador ' ;
		$respuesta['consulta']=$consulta;
	}
    while($row = $query->fetch_object()){
		$fecha=$row->fecha;
		$hora=$row->hora;
		$no_cliente=$row->no_cliente;
		$nombre_cliente=$row->cliente;
		$tipo_equipo=$row->tipo_equipo;
		$id_foto_no_equipo=$row->cliente;
		$id_foto_placas=$row->cliente;
		$id_foto_marca=$row->cliente;
		$id_foto_modelo=$row->cliente;
		$observaciones=$row->cliente;
		$linea=$row->linea;
		$usuario=$row->usuario;
	}
	$link_base = "http://$ip_out:$port_out/webtools/fotos_equipo/";
	$to=get_correos($no_cliente,true,true);
	if($to['codigo']==-1){
		$respuesta['codigo']=-1;
        $respuesta['mensaje']="Error al consultar correos: ".$to['mensaje'] ;
		return $respuesta;
	}
	$bcc=array();
	//$to=array();
	$to=$to['correos'];
	array_push($to,"martin@delbravo.com");
	array_push($to,"trafico@delbravo.com");
	array_push($to,"cruces@delbravo.com");
	array_push($bcc,"abisaicruz@delbravo.com");
	$asunto="Equipo Vacio Recibido en Del Bravo **AVISO REENVIADO**";
	$mensaje="
		<p>Del Bravo Forwarding Reporta Equipo Vacio Recibido.
		Favor de no contestar a este E-mail ya que es un
		e-mail generado automaticamente por el sistema
		de Control de Equipo Vacio. Gracias !<p>

		Fecha: $fecha<br>
		Hora: $hora<br>
		Cliente: $nombre_cliente<br>
		Tipo Equipo: $tipo_equipo<br>
		Foto Num. Equipo: ".($id_foto_no_equipo!='NULL' ? '<a href="'.$link_base.$id_foto_no_equipo.'.jpg">Ver foto</a>':'')."<br>
		Foto Placas: ".($id_foto_placas!='NULL' ? '<a href="'.$link_base.$id_foto_placas.'.jpg">Ver foto</a>':'')."<br>
		Foto Marca: ".($id_foto_marca!='NULL' ? '<a href="'.$link_base.$id_foto_marca.'.jpg">Ver foto</a>':'')."<br>
		Foto Modelo: ".($id_foto_modelo!='NULL' ? '<a href="'.$link_base.$id_foto_modelo.'.jpg">Ver foto</a>':'')."<br>
		Observaciones: $observaciones<br>
		Entrego: $linea<br>
		Recibio Equipo: $usuario<br>
	";
	$envio=enviamail($asunto,$mensaje,$to,$bcc,'mail.delbravo.com','25','martin@delbravo.com','',true,NULL);
	/*if($envio['codigo']==-1){
		$respuesta['codigo']=-1;
		$respuesta['mensaje']="Error al enviar correo: ".$envio['mensaje'].var_dump($to);
		return $respuesta;
	}*/
	$respuesta['codigo']=1;
    $respuesta['mensaje']="Correo enviado";
	return $respuesta;
}

function consulta_comentarios($referencia){
	include('../db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	$respuesta['codigo']=-1;
	$respuesta['mensaje']='Error desconocido, contacte al administrador';
	$respuesta['html']='';
	$consulta="
		SELECT
			com.comentarios,
			com.estatus
        FROM
            tblbodcom as com
        WHERE
            com.referencia='$referencia'";
    $query = mysqli_query($cmysqli,$consulta);
    if (!$query) {
        $error=mysqli_error($cmysqli);
        $respuesta['codigo']=-1;
        $respuesta['mensaje']='Error al consultar los comentarios: ' .$error ;
		$respuesta['consulta']=$consulta;
		mysqli_close ($cmysqli);
        return $respuesta;
    }
	if (mysqli_num_rows($query)<=0){
		$comentarios='';
		$estatus='1';
	}
    while($row = $query->fetch_object()){
		$comentarios=$row->comentarios;
		$estatus=$row->estatus;
	}
	$response= ' 	<br><form role="form">
					<input type="hidden" id="referencia" value="'.$referencia.'">
					<div class="form-group">
						<label>'. $referencia .' comentarios:</label>
						<textarea class="form-control" rows="5" id="comentarios">'.$comentarios.'</textarea>
					</div>
					<div class="form-group">
						<label>Estatus</label>
						<select class="form-control" id="estatus">';
	$consulta="SELECT
		id_estatus,
		estatus
	FROM
		estatus";
	$query = mysqli_query($cmysqli,$consulta);
	 if (!$query) {
        $error=mysqli_error($cmysqli);
        $respuesta['codigo']=-1;
        $respuesta['mensaje']='Error al consultar los tipos de estatus: ' .$error ;
		$respuesta['consulta']=$consulta;
		mysqli_close ($cmysqli);
        return $respuesta;
    }
	while($row = $query->fetch_object()){
		if ($estatus==$row->id_estatus){
			$response.= '				<option value="'.$row->id_estatus.'" selected>'.$row->estatus.'</option>';
		}else{
			$response.= '				<option value="'.$row->id_estatus.'">'.$row->estatus.'</option>';
		}
	}						
	$response.=				'</select>
					</div>
					<button type="button" onclick="grabacomref()" class="btn btn-primary">Grabar</button>
				</form>';
	$respuesta['codigo']=1;
    $respuesta['mensaje']='Consulta realizada con exito' ;
	$respuesta['html']=$response;
	mysqli_close ($cmysqli);
	return $respuesta;
}

function grabacomref($referencia,$comentarios,$estatus){
	include('../db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	$consulta="
		INSERT INTO tblbodcom 
			(referencia,
			comentarios,
			estatus,
			fecha_com_estatus) 
		
		values
			('$referencia',
			'$comentarios',
			'$estatus',
			'".date('Y-m-d G:i:s')."') 
		ON DUPLICATE KEY UPDATE 
			comentarios='$comentarios',
			estatus='$estatus',
			fecha_com_estatus='".date('Y-m-d G:i:s')."'";
	$query = mysqli_query($cmysqli,$consulta);
    if (!$query) {
        $error=mysqli_error($cmysqli);
        $response['codigo']=-1;
        $response['mensaje']="Error al grabar lo comentarios y/o estatus de la referencia: $referencia " .$error ;
		$response['consulta']=$consulta;
		mysqli_close ($cmysqli);
        return $response;
    }
	$response['codigo']=1;
	$response['mensaje']='La informacion se ha guardado con exito';
	return $response;
}
