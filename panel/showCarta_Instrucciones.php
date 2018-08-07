<?php
	require_once '../bower_components/PHPMailer/PHPMailerAutoload.php';
	//require('../plugins/FPDF/fpdf.php');
	//require('../plugins/FPDI/fpdi.php');

	$sPathFilesExpo = "\\\\192.168.1.126\\documentos_expo\\salidaExpo";
	$sPathFilesPed2009 = "\\\\192.168.1.126\\pedimentos2009";
	$sPathFilesPermisos = "\\\\192.168.1.126\\permisos";
	
	$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);
	
	/**********************************************************************/

	if (!isset($_GET['solicitud'])) {
		exit(get_html_error_description("No se recibio el numero de salida"));
	} else if ($_GET['solicitud'] == '') {
		exit(get_html_error_description("No se recibio el numero de salida"));
	}

	$solicitud=$_GET['solicitud'];
	$type='none';
	$usuario='none';
	$enviar='none';
	
	//error_log('JC SALIDA '.$solicitud.' QUERY '.$_SERVER['REQUEST_URI']);
	
	if (isset($_GET['type'])) { $type=$_GET['type']; }	
	if (isset($_GET['usr'])) { $usuario=strtoupper($_GET['usr']); }	
	if (isset($_GET['env'])) { 
		if ($_GET['env'] == 'editar') {
			$enviar='enviar';
		}
	}
	
	$my_report = '';
	$selection_formula = '';
	$sCaja = '';
	$sLineaT = '';
	$sReportada = '';
	$sReportadaAA = '';
	$sFacturas = '';
	$sPedimentos = '';
	$sObservaciones = '';
	$sRelacionDocsName = '';
	
	$adjuntos=array();
	$aEmailsListaEnviados=array();
	$aEnvioClientes=array();
	$aEnvioLineaTrans=array();
	$aEnvioTransfer=array();
	
	$sFile;
	
	/*******************************************************************/

	include ('../connect_dbsql.php');

	//Caso descarga LINEA TRANSPORTISTA Y TRANSFER
	if ($usuario != 'none') {
		fcn_set_fecha_descarga();
	}
	
	/*******************************************************************/
	
	$consulta="SELECT a.nocliente, a.nombrecliente, a.caja, a.lineatransp, a.observaciones, a.reportada, a.reportada_aa,
	                  relacion_docs_name
			   FROM bodega.salidas_expo AS a
			   WHERE a.salidanumero=".$solicitud;
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al definir que formato de impresion usar '. mysqli_error($cmysqli) .'.'));		
	}
	while($row = mysqli_fetch_object($query)){
		$sCaja = $row->caja;
		$sLineaT = $row->lineatransp;
		$sObservaciones = ((is_null($row->observaciones))? '': $row->observaciones);
		$sReportada = ((is_null($row->reportada))? 'N' : $row->reportada);
		$sReportadaAA = ((is_null($row->reportada_aa))? 'N' : $row->reportada_aa);
		$sRelacionDocsName = ((is_null($row->relacion_docs_name))? '': $row->relacion_docs_name);
		
		if (is_null($row->nocliente)) {
			$my_report = __DIR__ . "\\Carta_Instrucciones_old_feb2018.rpt";
			if (is_null($row->caja)) {
				$my_report =  __DIR__ . "\\Carta_Instrucciones.rpt";
			}
			
			$selection_formula = '{salidas_expo.salidanumero} = '.$solicitud;
			
			fcn_get_email_notificacion_new();
		} else {
			$my_report =  __DIR__ . "\\Carta_Instrucciones_old.rpt";
			$selection_formula = '{salidas.salidanumero} = '.$solicitud;
			
			fcn_get_email_notificacion_old($row->nocliente, $row->nombrecliente);
		}
		break;
	} 

	/************************************************/
	
	$consulta="SELECT GROUP_CONCAT(a.FACTURA_NUMERO SEPARATOR ', ') AS facturas,
				      (SELECT GROUP_CONCAT(b.PEDIMENTO SEPARATOR ', ') 
					   FROM bodega.facturas_expo AS b
					   WHERE b.SALIDA_NUMERO=a.SALIDA_NUMERO
					   GROUP BY SALIDA_NUMERO) AS pedimentos 
			   FROM bodega.facturas_expo AS a
			   WHERE a.SALIDA_NUMERO=".$solicitud."
			   GROUP BY a.SALIDA_NUMERO;";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al definir que formato de impresion usar '. mysqli_error($cmysqli) .'.'));
	}
	while($row = mysqli_fetch_object($query)){
		$sFacturas = $row->facturas;
		$sPedimentos = $row->pedimentos;		
		break;
	} 
	
	/*******************************************************************/

	//$selection_formula = '{salidas.salidanumero} = '.$solicitud;
	$COM_Object = 'CrystalRuntime.Application'; 
	try {
		$crapp  = new COM ($COM_Object) or die("Unable to Create Object"); 
	} catch (com_exception $e) {
		exit($e->getMessage());
	}

	$my_pdf = "expo_file_".$solicitud.".pdf";//tempnam(".\\", "CR").".pdf";

	try
	{
		$creport = $crapp->OpenReport($my_report, 1);	
		//$creport->Database->LogOnServer("p2sodbc.dll", "bodegamysql_cliente", $mysqldb, $mysqluser, $mysqlpass);
		$creport->EnableParameterPrompting = 0; 
		$creport->FormulaSyntax = 0;  
		$creport->RecordSelectionFormula=$selection_formula;  
		$creport->DiscardSavedData;
		$creport->ReadRecords();
		$creport->ExportOptions->DiskFileName=$my_pdf;
		$creport->ExportOptions->FormatType=31;
		$creport->ExportOptions->DestinationType=1;
		$creport->Export(false);
		$creport = null;
		$crapp = null;
		
		/****************************************************************************************/
		
		if ($type === 'file') {
			$sFile = fcn_get_file_send();
		} /*else {
			if ($sReportada != 'S' || $enviar == 'enviar') {
				// ..:: Revisamos si hay que notificar al agente aduanal ::..
				$sFacturasPendientes = fcn_verificar_envio_notificacion_aaa();
				if ($sFacturasPendientes != '') {
					if ($sReportadaAA != 'S') {
						$renvio = fcn_envia_notificacion_prefile($sFacturasPendientes);
					
						if ($renvio['codigo'] == 1) {
							$consulta="UPDATE bodega.salidas_expo
									   SET reportada_aa='S'
									   WHERE salidanumero=".$solicitud;
									   
							$query = mysqli_query($cmysqli, $consulta);
							if (!$query) {
								exit(get_html_error_description('Error al marcar como reportada aa por correo electronico '. mysqli_error($cmysqli) .'.'));
							}
						} else {
							exit(get_html_error_description(json_encode($renvio['codigo'])));
						}
					} else {
						error_log('JC SALIDA '.$solicitud.' YA FUE REPORTADA A AGENTE ADUANAL AMERICANO');
					}
				} else {
					$sFile = fcn_get_file_send();
					array_push($adjuntos, $sFile);
					
					//ENVIO DE CORREOS					
					$renvio['codigo'] = 1;
					foreach($aEnvioClientes as $row){
						error_log('JC CORREOS SALIDA '.$solicitud.' CLIENTES TO: '.json_encode($row['to']));
						error_log('JC CORREOS SALIDA '.$solicitud.' CLIENTES BCC: '.json_encode($row['bcc']));
						$renvio=envia_rpt_clientes($adjuntos, $row['cliente'], $row['to'], $row['bcc']);
					}
					
					foreach($aEnvioLineaTrans as $row){
						error_log('JC CORREOS SALIDA '.$solicitud.' LINEATRANS TO: '.json_encode($row['to']));
						$renvio=envia_rpt_lineatrans_transfer($adjuntos, $row['cliente'], $row['to'], $row['bcc'], 'LTR');
					}
					
					foreach($aEnvioTransfer as $row){
						error_log('JC CORREOS SALIDA '.$solicitud.' TRANSFER TO: '.json_encode($row['to']));
						$renvio=envia_rpt_lineatrans_transfer($adjuntos, $row['cliente'], $row['to'], $row['bcc'], 'TRA');
					}
					
					//ACTUALIZAR SALIDA COMO REPORTADA
					unlink($sFile); //Eliminamos archivo
					if ($renvio['codigo'] == 1 && $sReportada != 'S') {
						$consulta="UPDATE bodega.salidas_expo
								   SET reportada='S'
								   WHERE salidanumero=".$solicitud;
								   
						$query = mysqli_query($cmysqli, $consulta);
						if (!$query) {
							exit(get_html_error_description('Error al marcar como reportada por correo electronico '. mysqli_error($cmysqli) .'.'));
						}
					}
				}
			}
		}*/
	} 
	catch(com_exception $error){
		exit($error->getMessage());
	}

	if (headers_sent()) {
		echo 'HTTP header already sent';
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header("Content-Type: application/pdf");
		header("Content-Transfer-Encoding: Binary");
			
		if ($type === 'file') { 
			header("Content-Length: ".filesize($sFile));
			header("Content-Disposition: inline; filename=\"".rtrim($solicitud).".pdf\"");
			header('Accept-Ranges: bytes');
			@readfile($sFile);
			unlink($sFile);
			unlink($my_pdf);
		} else {
			header("Content-Length: ".filesize($my_pdf));
			header("Content-Disposition: inline; filename=\"".rtrim($solicitud).".pdf\"");
			header('Accept-Ranges: bytes');
			@readfile($my_pdf);
			unlink($my_pdf);
		}
		
		exit;
	}
	
/* Actualizar fecha de descarga */
function fcn_set_fecha_descarga() {
	global $cmysqli, $solicitud, $usuario;
	$fecha_registro =  date("Y-m-d H:i:s");
	
	$consulta="UPDATE bodega.salidas_expo
			   SET ".(($usuario == 'LTR')? 'descarga_lineatrans' : 'descarga_transfer')."='".$fecha_registro."'
			   WHERE salidanumero=".$solicitud;
	
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al obtener archivo de descarga.'));
	}
}

/* Verificar si se debe subir el prefile siempre y cuando sea benavides and company */
function fcn_verificar_envio_notificacion_aaa() {
	global $cmysqli, $solicitud;
	
	$sFacturasPendientes = '';
	
	$consulta="SELECT GROUP_CONCAT(FACTURA_NUMERO SEPARATOR ', ') AS facturas
			   FROM bodega.facturas_expo
			   WHERE SALIDA_NUMERO=".$solicitud." AND 
				     NOAAA=58 AND
					 PREFILE_ID IS NULL";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al consultar existencia en facturas '. mysqli_error($cmysqli) .'.'));
	} else {
		while($row = mysqli_fetch_object($query)){
			$sFacturasPendientes = $row->facturas;
			break;
		} 
	}
	
	return $sFacturasPendientes;
}

/* (DEPRESIADO) Verificamos si las facturas fueron subidas en la interfaz de cruces */
function fcn_get_enviar_correo_x_cruces() {
	global $cmysqli, $solicitud;
	
	$bEnviar = false;
	
	$consulta="SELECT COUNT(*) AS total
			   FROM bodega.facturas_expo AS a INNER JOIN
				    bodega.cruces_expo_detalle AS b ON b.uuid=a.UUID
			   WHERE a.SALIDA_NUMERO=".$solicitud;
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al consultar existencia en cruces '. mysqli_error($cmysqli) .'.'));
	} else {
		while($row = mysqli_fetch_object($query)){
			if ($row->total > 0) { 
				$bEnviar = true; 
			}
			
			break;
		} 
	}
	
	return $bEnviar;
}

/* Generamos un solo archivo pdf */
function fcn_get_file_send() {
	global $solicitud, $cmysqli, $my_pdf, $sPathFilesExpo, $sPathFilesPed2009, $sPathFilesPermisos;
	
	$aArchivos=array();
	
	/************************************************/
	/* 1 - Carta de instrucciones
	   2 - Relacion de documentos
       3 - Prefile                                  
	   4 - Notificacion de Arribo (NOA)          
	   5 - Pedimento o Remesas 
  	   6 - Avisos (Permisos)
       7 - Solicitud de Retiro	                    */
	/************************************************/
	
	/************************************************/
	/* 1 - Carta de instrucciones */
	/************************************************/
	array_push($aArchivos, $my_pdf);
				
	/************************************************/
	/* 2 - Relacion de documentos */
	/************************************************/
	$sFileName = $sPathFilesExpo . DIRECTORY_SEPARATOR . $solicitud . "_reldocs.pdf";
	if (file_exists($sFileName)) { 
		array_push($aArchivos, $sFileName);
	}
	
	/************************************************/
	/* 3 - Prefile */
	/************************************************/
	$consulta="SELECT a.PREFILE_ID, b.nombre_archivo, IF(b.id_doc_master IS NULL, b.id_documento, b.id_doc_master) AS id_documento_if
			   FROM bodega.facturas_expo AS a INNER JOIN
				    bodega.documentos_expo AS b ON b.id_documento=a.PREFILE_ID
			   WHERE SALIDA_NUMERO=".$solicitud."
               GROUP BY id_documento_if";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al consultar remesas de salida '. mysqli_error($cmysqli) .'.'));
	}
	
	while($row = mysqli_fetch_object($query)){		
		$sFileName = $sPathFilesExpo . DIRECTORY_SEPARATOR . $row->nombre_archivo;
		if (file_exists($sFileName)) { 
			array_push($aArchivos, $sFileName);
		}
	}
	
	/*$sFileName = $sPathFilesExpo . DIRECTORY_SEPARATOR . $solicitud . "_prefile.pdf";
	if (file_exists($sFileName)) { 
		array_push($aArchivos, $sFileName);
	}*/
	
	/************************************************/
	/* 4 - Notificacion de Arribo (NOA) */
	/************************************************/
	$sFileName = $sPathFilesExpo . DIRECTORY_SEPARATOR . $solicitud . "_noa.pdf";
	if (file_exists($sFileName)) { 
		array_push($aArchivos, $sFileName);
	}
	
	/************************************************/
	/* 5 - Pedimento o Remesas */
	/************************************************/	
	$consulta="SELECT a.REFERENCIA, a.NUM_REM_PED, GROUP_CONCAT(DISTINCT b.nombre_archivo) AS TICKET_BASCULA_NAME
  			   FROM bodega.facturas_expo AS a LEFT JOIN 
					bodega.documentos_expo AS b ON b.id_documento=a.TICKET_BASCULA_ID
			   WHERE a.SALIDA_NUMERO=".$solicitud."
			   GROUP BY a.REFERENCIA, a.NUM_REM_PED
			   ORDER BY a.REFERENCIA, a.NUM_REM_PED";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al consultar remesas de salida '. mysqli_error($cmysqli) .'.'));
	}
	
	$sRefActual = '';
	while($row = mysqli_fetch_object($query)){
		$sReferencia = $row->REFERENCIA;
		$sNumRemPed = $row->NUM_REM_PED;
		$sTipoPedimento = fcn_tipo_pedimento($sReferencia);
		$aTickets = explode(',', $row->TICKET_BASCULA_NAME);
		
		if ($sRefActual != $sReferencia) {
			$sRefActual = $sReferencia;
			
			if ($sTipoPedimento == 'normal') { 
				$sFileName = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sReferencia . "-transportista.pdf";
				if (file_exists($sFileName)) { 
					array_push($aArchivos, $sFileName);
				}
			} else { //Pedimento consolidado
				$sFileName = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sReferencia . "-" . $sNumRemPed . ".pdf";
				if (file_exists($sFileName)) { 
					array_push($aArchivos, $sFileName);
				}
			}
		} else {
			if ($sTipoPedimento == 'consolidado') { 
				$sFileName = $sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sReferencia . "-" . $sNumRemPed . ".pdf";
				if (file_exists($sFileName)) { 
					array_push($aArchivos, $sFileName);
				}
			}
		}
		
		if (count($aTickets) > 0) {
			foreach ($aTickets as &$ticket) { 
				if ($ticket != '') {
					$sFileName = $sPathFilesExpo . DIRECTORY_SEPARATOR . $ticket;
					if (file_exists($sFileName)) { 
						array_push($aArchivos, $sFileName);
					}
					/*$aTicketData = explode('/', $ticket);
					$ticket = end($aTicketData);
					$sFileName = $sPathFilesCruces . DIRECTORY_SEPARATOR . $ticket;
					if (file_exists($sFileName)) { 
						array_push($aArchivos, $sFileName);
					}*/
				}
			}
		}
	}
	
	/************************************************/
	/* 6 - Avisos (Permisos) */
	/************************************************/
	
	$consulta="SELECT REFERENCIA, NUM_REM_PED, CONS_FACT_PED
			   FROM bodega.facturas_expo
			   WHERE SALIDA_NUMERO=".$solicitud."
			   ORDER BY REFERENCIA, NUM_REM_PED";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al consultar remesas de salida '. mysqli_error($cmysqli) .'.'));
	}
	
	while($row = mysqli_fetch_object($query)){
		$sReferencia = $row->REFERENCIA;
		$sConsFactPed = $row->CONS_FACT_PED;
		
		fcn_permiso_nombre_archivo($sReferencia, $sConsFactPed, $aArchivos);
	}
	
	/************************************************/
	/* 7 - Solicitud de Retiro */
	/************************************************/
	$sFileName = $sPathFilesExpo . DIRECTORY_SEPARATOR . $solicitud . "_solicitud_retiro.pdf";
	if (file_exists($sFileName)) { 
		array_push($aArchivos, $sFileName);
	}
	
	/************************************************/
	/* Se arma archivo */
	/************************************************/
	
	$sFile = "salidaexportacion_".$solicitud.".pdf";
	$sArchivos = '';
	foreach ($aArchivos as &$archivo) { 
		$sArchivos .= $archivo . ' ';
	}
	
	if ($sArchivos != '') {
		$sComando = '"C:\Program Files\gs\gs9.23\bin\gswin64" -dBATCH -dNOPAUSE -q -dSAFER -sDEVICE=pdfwrite -sOutputFile='.$sFile.' '.$sArchivos;
		$output = shell_exec($sComando);
		if ($output != '') {
			exit(get_html_error_description($output));
		}
	}
	
	//error_log($sFile);
	return $sFile;
}
	
/* ..:: consultamos si es Consolidado o Normal ::.. */
function fcn_tipo_pedimento($sReferencia) {
	include ('../connect_casa.php');
	
	$sTipoPedimento = 'normal';
	
	/* ..:: Consultamos referencia en casa ::.. */
	$consulta = "SELECT a.NUM_PEDI, a.PAT_AGEN, a.FIR_REME
				 FROM SAAIO_PEDIME a
				 WHERE a.NUM_REFE='".$sReferencia."'";
					 
	$query = odbc_exec($odbccasa, $consulta);
	if ($query==false){ 
		exit(get_html_error_description("Error al consultar el tipo de pedimento de la Referencia [".$sReferencia."] en el sistema CASA.."));
	} else {
		while(odbc_fetch_row($query)){ 
			$sTipoPedimento = (is_null(odbc_result($query,"FIR_REME"))? 'normal': 'consolidado');
			break;
		}
	}
	
	return $sTipoPedimento;
}

/* ..:: obtenemos el nombre del archivo del permiso ::.. */
function fcn_permiso_nombre_archivo($sReferencia, $sConsFactPed, &$aArchivos) {
	include ('../connect_casa.php');
	global $cmysqli,$sPathFilesPermisos;
	
	$consulta = '';
	$sNumPerm = '';
	$sTipoPedimento = fcn_tipo_pedimento($sReferencia);
	
	if ($sTipoPedimento == 'consolidado') { 
		/* ..:: Consultamos referencia en casa ::.. */
		$consulta = "SELECT a.NUM_PERM
					 FROM SAAIO_PERPAR a
					 WHERE a.NUM_REFE='".$sReferencia."' AND
						   a.CONS_FACT=".$sConsFactPed;
	} else { // Pedimento normal
		/* ..:: Consultamos referencia en casa ::.. */
		$consulta = "SELECT a.NUM_PERM
					 FROM SAAIO_PERMIS a
					 WHERE a.NUM_REFE='".$sReferencia."'";
	}	
	
	$query = odbc_exec($odbccasa, $consulta);
	if ($query==false){ 
		exit(get_html_error_description("Error al consultar el numero de permiso de la Referencia [".$sReferencia."] en el sistema CASA.."));
	} else {
		while(odbc_fetch_row($query)){ 
			$sNumPerm = (is_null(odbc_result($query,"NUM_PERM"))? '': odbc_result($query,"NUM_PERM"));
			
			$consulta="SELECT archivo_permiso
					   FROM bodega.permisos_pedimentos
					   WHERE numero_permiso='".$sNumPerm."'";
					   
			$query_mysql = mysqli_query($cmysqli, $consulta);
			if (!$query_mysql) {
				exit(get_html_error_description('Error al consultar permisos de pedimentos en mysql '. mysqli_error($cmysqli) .'.'));
			}
			
			while($row = mysqli_fetch_object($query_mysql)){
				$sFileName = $row->archivo_permiso;
				if ($sFileName != '') {
					$sFileName = $sPathFilesPermisos . DIRECTORY_SEPARATOR . $sFileName;
					if (file_exists($sFileName)) { 
						array_push($aArchivos, $sFileName);
					}
				}
			}
		}
	}
}
	
function fcn_get_email_notificacion_new() {
	global $aEmailsListaEnviados, $aEnvioClientes, $aEnvioLineaTrans, $aEnvioTransfer, $solicitud, $cmysqli;
	
	$bcc=array();
	$to=array();
	$sClientes = '';
		
	//NIVEL FACTURA
	$consulta="SELECT a.NUMCLIENTE, b.cnombre, c.email, c.tipo_contacto
               FROM bodega.facturas_expo AS a INNER JOIN
                    bodega.cltes_expo AS b ON b.gcliente=a.NUMCLIENTE LEFT JOIN
		            bodega.contactos_expo AS c ON (c.id_catalogo=a.NUMCLIENTE AND
								                   c.tipo_catalogo='CLI') OR 
                                                  (c.id_catalogo=a.NOAAA AND
												   c.tipo_catalogo='AAA')
			   WHERE a.SALIDA_NUMERO=".$solicitud;
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al consultar emails nuevo esquema '. mysqli_error($cmysqli) .'.'));
	} else {
		while($row = mysqli_fetch_object($query)){
			if (strpos($sClientes, $row->cnombre) === false) {
				if ($sClientes != '') { $sClientes .= ', '; }
				$sClientes .= $row->cnombre;
			}

			if ($row->tipo_contacto == 'EJE') { 
				fcn_set_email_array($row->email, $bcc); 
			} else {
				fcn_set_email_array($row->email, $to); //CLIENTES, AAA, TRANS, ETC ETC
			}
		} 
	}
	
	array_push($aEnvioClientes, array('cliente'=>$sClientes,'to'=>$to,'bcc'=>$bcc));
	
	$bcc=array();
	$to=array();
	
	//NIVEL ENCABEZADO LINEA TRANSPRTISTA
	$consulta="SELECT c.email
			   FROM bodega.salidas_expo AS a LEFT JOIN
				    bodega.contactos_expo AS c ON c.id_catalogo=a.nolineatransp AND
												  c.tipo_catalogo='LTR'
				WHERE a.salidanumero=".$solicitud;
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al consultar emails nuevo esquema encabezado'. mysqli_error($cmysqli) .'.'));
	} else {
		while($row = mysqli_fetch_object($query)){
			fcn_set_email_array($row->email, $to); //TRANS, ETC ETC
		}
	}
	
	if (count($to) > 0) {
		array_push($aEnvioLineaTrans, array('cliente'=>$sClientes,'to'=>$to,'bcc'=>$bcc));
	}
	
	$bcc=array();
	$to=array();
	
	$consulta="SELECT c.email
			   FROM bodega.salidas_expo AS a LEFT JOIN
				    bodega.contactos_expo AS c ON c.id_catalogo=a.notransfer AND
												  c.tipo_catalogo='TRA'
				WHERE a.salidanumero=".$solicitud;
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al consultar emails nuevo esquema encabezado'. mysqli_error($cmysqli) .'.'));
	} else {
		while($row = mysqli_fetch_object($query)){
			fcn_set_email_array($row->email, $to); //TRANS, ETC ETC
		}
	}
	
	if (count($to) > 0) {
		array_push($aEnvioTransfer, array('cliente'=>$sClientes,'to'=>$to,'bcc'=>$bcc));
	}
}

function fcn_set_email_array($sEmail, &$array) {
	global $aEmailsListaEnviados;
	
	if (is_null($sEmail) == false && $sEmail != '') { 
		$sEmail = strtolower($sEmail);
	
		$bExist = array_search($sEmail, array_column($aEmailsListaEnviados, 'email'));
		if (false === $bExist) {
			array_push($aEmailsListaEnviados, array('email' => $sEmail));			
			array_push($array, $sEmail);
		}
	}	
}

function fcn_get_email_notificacion_old($nocliente, $nombrecliente) {
	global $aEnvioClientes, $solicitud, $cmysqli;
	
	$consulta="SELECT to1, to2, to3, to4, to5, to6, to7, to8, to9, to10, 
				      cc1, cc2, cc3, cc4, cc5, cc6, cc7, cc8, cc9, cc10
			   FROM bodega.geocel_clientes_expo
			   WHERE f_numcli='".$nocliente."'";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit('Error al consultar emails viejo esquema '. mysqli_error($cmysqli) .'.');		
	} else {
		while($row = mysqli_fetch_object($query)){
			$bcc=array();
			$to=array();
	
			if (is_null($row->to1) == false && $row->to1 != '') { array_push($to, $row->to1); }
			if (is_null($row->to2) == false && $row->to2 != '') { array_push($to, $row->to2); }
			if (is_null($row->to3) == false && $row->to3 != '') { array_push($to, $row->to3); }
			if (is_null($row->to4) == false && $row->to4 != '') { array_push($to, $row->to4); }
			if (is_null($row->to5) == false && $row->to5 != '') { array_push($to, $row->to5); }
			if (is_null($row->to6) == false && $row->to6 != '') { array_push($to, $row->to6); }
			if (is_null($row->to7) == false && $row->to7 != '') { array_push($to, $row->to7); }
			if (is_null($row->to8) == false && $row->to8 != '') { array_push($to, $row->to8); }
			if (is_null($row->to9) == false && $row->to9 != '') { array_push($to, $row->to9); }
			if (is_null($row->to10) == false && $row->to10 != '') { array_push($to, $row->to10); }
			if (is_null($row->cc1) == false && $row->cc1 != '') { array_push($bcc, $row->cc1); }
			if (is_null($row->cc2) == false && $row->cc2 != '') { array_push($bcc, $row->cc2); }
			if (is_null($row->cc3) == false && $row->cc3 != '') { array_push($bcc, $row->cc3); }
			if (is_null($row->cc4) == false && $row->cc4 != '') { array_push($bcc, $row->cc4); }
			if (is_null($row->cc5) == false && $row->cc5 != '') { array_push($bcc, $row->cc5); }
			if (is_null($row->cc6) == false && $row->cc6 != '') { array_push($bcc, $row->cc6); }
			if (is_null($row->cc7) == false && $row->cc7 != '') { array_push($bcc, $row->cc7); }
			if (is_null($row->cc8) == false && $row->cc8 != '') { array_push($bcc, $row->cc8); }
			if (is_null($row->cc9) == false && $row->cc9 != '') { array_push($bcc, $row->cc9); }
			if (is_null($row->cc10) == false && $row->cc10 != '') { array_push($bcc, $row->cc10); }
			
			array_push($aEnvioClientes, array('cliente'=>$nombrecliente,'to'=>$to,'bcc'=>$bcc));			
			break;
		} 
	}
}

function fcn_envia_notificacion_prefile($sFacturasPendientes) {
	global $bDebug, $solicitud, $aEmailsListaEnviados, $cmysqli, $sCaja, $sLineaT, $sPedimentos;
	
	$aEmailsListaEnviados=array();
	$adjuntos=array();
	$bcc=array();
	$to=array();
		
	$consulta="SELECT a.NUMCLIENTE, b.email, b.tipo_contacto
			   FROM bodega.facturas_expo AS a INNER JOIN
				    bodega.contactos_expo AS b ON (b.id_catalogo=a.NUMCLIENTE AND
												   b.tipo_catalogo='CLI' AND b.tipo_contacto = 'EJE') OR
												  (b.id_catalogo=a.NOAAA AND
												   b.tipo_catalogo='AAA')
			   WHERE a.SALIDA_NUMERO=".$solicitud."
			   GROUP BY b.email";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		exit(get_html_error_description('Error al consultar emails de ejecutivos '. mysqli_error($cmysqli) .'.'));
	} else {
		while($row = mysqli_fetch_object($query)){
			if ($row->tipo_contacto == 'EJE') { 
				fcn_set_email_array($row->email, $bcc); 
			} else {
				fcn_set_email_array($row->email, $to); //CLIENTES, AAA, TRANS, ETC ETC
			}
		}
	}
	
	//error_log('JC CORREOS SALIDA PREFILE '.$solicitud.' CLIENTES TO: '.json_encode($to));
	//error_log('JC CORREOS SALIDA PREFILE '.$solicitud.' CLIENTES BCC: '.json_encode($bcc));
	
	$asunto='Falta archivo Prefile en salida: '.$solicitud;
	/*$mensaje='
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>Inventario x No. parte</title>
			<style type="text/css">
				body {
					margin: 0; padding: 0; min-width: 100%!important;
				}.content {
					width: 100%; max-width: 600px;
				} 
				table {
					border-collapse: collapse;
					width: 100%;
				}

				th, td {
					text-align: left;
					padding: 8px;
				}

				tr:nth-child(even){
					background-color: #f2f2f2
				}

				th {
					background-color: #4CAF50;
					color: white;
				}
			</style>
		</head>
		<body yahoo>
			<img src="cid:logo.png" alt="Logo Del Bravo" width="103" height="100" /><br>
			Este e-mail fue generado automaticamente, no conteste a el, si tiene alguna <br>
			duda por favor comuniquese con su ejecutivo de Cuenta. Atte. Grupo Aduanero del Bravo S.A.<br><br>
			<strong style="color:red;">Falta archivo Prefile en salida: '.$solicitud.'</strong><br><br>
			Factura(s): '.$sFacturasPendientes.'<br>
			Plataforma: '.$sCaja.'<br>
			Transportista: '.$sLineaT .'<br>
			Pedimento(s): '.$sPedimentos.'<br>
			<p>Este es un E-mail automatizado favor de no responder a el. Gracias !</p>
		</body>
	</html>';*/
	
	$asunto='Falta archivo Prefile en salida: '.$solicitud;
	$sTitulo = 'Falta archivo Prefile en salida';
	
	$sHTML = '
	<table style="border: solid 1px #bbbccc; width: 700px;" cellspacing="0" cellpadding="0">
		<tbody>
			<tr style="background-color: #0073b7; color: #fff;">
				<td style="background-color: #fff;" width="100px">
					<img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" />
				</td>
				<td width="10">&nbsp;</td>
				<td align="center"
					><h1>Grupo Aduanero Del Bravo.</h1>
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
									<h2 style="color:red;">'.utf8_decode($sTitulo).'</h2>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td align="left">
									<big>
										<strong>Numero de Salida:</strong>
										<strong>
											<span style="text-decoration: underline;">'.$solicitud.'</span>
										</strong>
									</big>
								</td>
								<td align="left">&nbsp;</td>
								<td align="left">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Factura(s): </strong> '.$sFacturasPendientes.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Plataforma: </strong> '.$sCaja.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Transportista: </strong> '.$sLineaT.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Pedimento(s): </strong> '.$sPedimentos.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3">
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
	} else {
		array_push($bcc,'enrique@delbravo.com');
	}
	array_push($bcc,'jcdelacruz@delbravo.com');
	
	$correo=enviamail($asunto,$sHTML,$to,array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',$adjuntos);
	$correo=enviamail($asunto,$sHTML,$bcc,array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',$adjuntos);
	
	return $correo;
}

function envia_rpt_clientes($adjuntos, $cliente, $to, $bcc){
	global $bDebug, $sCaja, $sLineaT, $sFacturas, $sPedimentos, $sObservaciones, $sReportada, $solicitud;
	
	$asunto=utf8_decode((($sReportada == 'S')? 'Modificación Salida de Exportación' : 'Salida de Exportación Automatica').', Cliente(s): '.$cliente);
	$sTitulo = (($sReportada == 'S')? 'Modificación Salida de Exportación' : 'Salida de Exportación Automatica');
	
	$sHTML = '
	<table style="border: solid 1px #bbbccc; width: 700px;" cellspacing="0" cellpadding="0">
		<tbody>
			<tr style="background-color: #0073b7; color: #fff;">
				<td style="background-color: #fff;" width="100px">
					<img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" />
				</td>
				<td width="10">&nbsp;</td>
				<td align="center"
					><h1>Grupo Aduanero Del Bravo.</h1>
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
									<h2 '.(($sReportada == 'S')? 'style="color: #E38F00;"' : '').'>'.utf8_decode($sTitulo).'</h2>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td align="left">
									<big>
										<strong>Numero de Salida:</strong>
										<strong>
											<span style="text-decoration: underline;">'.$solicitud.'</span>
										</strong>
									</big>
								</td>
								<td align="left">&nbsp;</td>
								<td align="left">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Factura(s): </strong> '.$sFacturas.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Plataforma: </strong> '.$sCaja.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Transportista: </strong> '.$sLineaT.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Pedimento(s): </strong> '.$sPedimentos.'
								</td>
							</tr>';
	if($sObservaciones != '') {
		$sHTML .= '			<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Observaciones: </strong> '.$sObservaciones.'
								</td>
							</tr>';
	}
	$sHTML .= '				<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #E4FFF3;" colspan="3">
									<strong>
										Estimado Cliente.
										<br>
										'.utf8_decode('Una copia de la documentación de exportación se encuentra ubicada en nuestro Sistema integral de información.').' <a href="https://www.delbravoweb.com/sii/login.php">'.utf8_decode('Click Aquí para ir a la pagina.').'</a>
										<br>
										'.utf8_decode('Si no cuenta con un acceso, favor de solicitarlo con su ejecutivo o enviando un correo a enrique@delbravo.com').'
									</strong>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3">
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
	} else {
		array_push($bcc,'enrique@delbravo.com');
	}
	
	array_push($bcc,'jcdelacruz@delbravo.com');
	
	$correo=enviamail($asunto,$sHTML,$to,array('jcdelacruz@delbravo.com'),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',$adjuntos);
	$correo=enviamail($asunto,$sHTML,$bcc,array(),'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',$adjuntos);
	return $correo;
}

function envia_rpt_lineatrans_transfer($adjuntos, $cliente, $to, $bcc, $tipo){
	global $bDebug, $sCaja, $sLineaT, $sFacturas, $sPedimentos, $sObservaciones, $sReportada, $solicitud;
	
	$asunto=utf8_decode((($sReportada == 'S')? 'Modificación Salida de Exportación' : 'Salida de Exportación Automatica').', Cliente(s): '.$cliente);
	$sTitulo = (($sReportada == 'S')? 'Modificación Salida de Exportación' : 'Salida de Exportación Automatica');
	
	$sHTML = '
	<table style="border: solid 1px #bbbccc; width: 700px;" cellspacing="0" cellpadding="0">
		<tbody>
			<tr style="background-color: #0073b7; color: #fff;">
				<td style="background-color: #fff;" width="100px">
					<img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" />
				</td>
				<td width="10">&nbsp;</td>
				<td align="center"
					><h1>Grupo Aduanero Del Bravo.</h1>
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
									<h2 '.(($sReportada == 'S')? 'style="color: #E38F00;"' : '').'>'.utf8_decode($sTitulo).'</h2>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td align="left">
									<big>
										<strong>Numero de Salida:</strong>
										<strong>
											<span style="text-decoration: underline;">'.$solicitud.'</span>
										</strong>
									</big>
								</td>
								<td align="left">&nbsp;</td>
								<td align="left">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Factura(s): </strong> '.$sFacturas.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Plataforma: </strong> '.$sCaja.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Transportista: </strong> '.$sLineaT.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Pedimento(s): </strong> '.$sPedimentos.'
								</td>
							</tr>';
	if($sObservaciones != '') {
		$sHTML .= '			<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Observaciones: </strong> '.$sObservaciones.'
								</td>
							</tr>';
	}
	$sHTML .= '				<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #E4FFF3;" colspan="3">
									<strong>
										'.utf8_decode('Para descargar la documentación de la salida ').' <a href="https://www.delbravoweb.com/'.(($bDebug)? 'monitorpruebas' : 'monitor').'/panel/showCarta_Instrucciones.php?solicitud='.$solicitud.'&type=file&usr='.$tipo.'">'.utf8_decode('Click Aquí.').'</a>
									</strong>
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3">
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
	
	$adjuntos=array();
	$correo=enviamail($asunto,$sHTML,$to,$bcc,'mail.delbravo.com','25','salidasexpo@delbravo.com','salexp01','',$adjuntos);
	return $correo;
}

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
	$mail->FromName = "Salidas de Exportacion Grupo Aduanero del Bravo";
	if($ruta_logo!=''){
		$mail->AddAttachment($ruta_logo, 'logo.png'); 
	}
	for($x=0;$x<count($adjuntos);$x++){
		$mail->AddAttachment($adjuntos[$x],$adjuntos[$x]); 
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

function get_html_error_description($sMensaje) {
	$sHtml = '
		<!DOCTYPE html>
		<html>
			<head>
				<style>
					.alert {
						padding: 20px;
						background-color: #f44336;
						color: white;
					}
				</style>
			</head>
			<body>

				<h2>Delbravo</h2>

				<div class="alert">
					<strong>Error!</strong> '.$sMensaje.'
					<br/><br/>
					<strong>Favor de notificarlo al departamento de sistemas</strong>
				</div>

			</body>
		</html>
	';
	
	return $sHtml;
}