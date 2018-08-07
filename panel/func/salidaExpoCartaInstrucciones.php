<?php
include ('../../connect_dbsql.php');
require_once '../../bower_components/PHPMailer/PHPMailerAutoload.php';

$__bDebug = true;

$__sPathFilesExpo = "";
$__sPathFilesPed2009 = "";
$__sPathFilesPermisos = "";

$__sSolicitud;
$__sCaja = '';
$__sLineaT = '';
$__sReportada = '';
$__sReportadaAA = '';
$__sFacturas = '';
$__sPedimentos = '';
$__sObservaciones = '';

$__aEmailsListaEnviados=array();
$__aEnvioClientes=array();
$__aEnvioLineaTrans=array();
$__aEnvioTransfer=array();
	
function fcn_enviar_notificacion_salida($solicitud, $bEnviar) {
	global $cmysqli, $__sSolicitud, $__sCaja, $__sLineaT, $__sReportada, $__sReportadaAA, $__sFacturas,
	       $__sPedimentos, $__sObservaciones, $__aEmailsListaEnviados, $__aEnvioClientes, $__aEnvioLineaTrans, 
		   $__aEnvioTransfer, $__bDebug, $__sPathFilesExpo, $__sPathFilesPed2009, $__sPathFilesPermisos;	
	
	$__bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);
	//$__bDebug = true;
	
	$__sPathFilesExpo = "\\\\192.168.1.126\\documentos_expo\\salidaExpo";
	$__sPathFilesPed2009 = "\\\\192.168.1.126\\pedimentos2009";
	$__sPathFilesPermisos = "\\\\192.168.1.126\\permisos";

	$__sSolicitud = $solicitud;
	$__sCaja = '';
	$__sLineaT = '';
	$__sReportada = '';
	$__sReportadaAA = '';
	$__sFacturas = '';
	$__sPedimentos = '';
	$__sObservaciones = '';

	$__aEmailsListaEnviados=array();
	$__aEnvioClientes=array();
	$__aEnvioLineaTrans=array();
	$__aEnvioTransfer=array();

	/********************************************************/
	
	$my_report = '';
	$selection_formula = '';
	
	$respuesta['Codigo']=1;
	
	/********************************************************/
	
	$consulta="SELECT a.nocliente, a.nombrecliente, a.caja, a.lineatransp, a.observaciones, a.reportada, a.reportada_aa,
	                  relacion_docs_name
			   FROM bodega.salidas_expo AS a
			   WHERE a.salidanumero=".$__sSolicitud;
	
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al definir que formato de impresion usar.'; 
		$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
	} else {
		while($row = mysqli_fetch_object($query)){
			$__sLineaT = $row->lineatransp;
			$__sObservaciones = ((is_null($row->observaciones))? '': $row->observaciones);
			$__sReportada = ((is_null($row->reportada))? 'N' : $row->reportada);
			$__sReportadaAA = ((is_null($row->reportada_aa))? 'N' : $row->reportada_aa);
			
			if (is_null($row->nocliente)) {
				$my_report = dirname(dirname(__FILE__)) . "\\Carta_Instrucciones.rpt";
				$selection_formula = '{salidas_expo.salidanumero} = '.$__sSolicitud;
				
				$respuesta = fcn_get_email_notificacion_new();
			} else {
				$bViejoEsquema = true;
				$my_report = dirname(dirname(__FILE__)) . "\\Carta_Instrucciones_old.rpt";
				$selection_formula = '{salidas.salidanumero} = '.$__sSolicitud;
				
				$respuesta = fcn_get_email_notificacion_old($row->nocliente, $row->nombrecliente);
			}
			break;
		}
	}
	
	/************************************************/
	
	if ($respuesta['Codigo'] == 1) {
		/*$consulta="SELECT GROUP_CONCAT(a.FACTURA_NUMERO SEPARATOR ', ') AS facturas,
						  (SELECT GROUP_CONCAT(b.PEDIMENTO SEPARATOR ', ') 
						   FROM bodega.facturas_expo AS b
						   WHERE b.SALIDA_NUMERO=a.SALIDA_NUMERO
						   GROUP BY SALIDA_NUMERO) AS pedimentos 
				   FROM bodega.facturas_expo AS a
				   WHERE a.SALIDA_NUMERO=".$__sSolicitud."
				   GROUP BY a.SALIDA_NUMERO;";*/
		$consulta="SELECT GROUP_CONCAT(a.FACTURA_NUMERO SEPARATOR ', ') AS facturas,
					      GROUP_CONCAT(DISTINCT a.PEDIMENTO SEPARATOR ', ') AS pedimentos, 
					      GROUP_CONCAT(DISTINCT CONCAT(a.TIPOSALIDA, ': ' , a.CAJA) SEPARATOR ', ') AS cajas
				   FROM bodega.facturas_expo AS a
				   WHERE a.SALIDA_NUMERO=".$__sSolicitud."
				   GROUP BY a.SALIDA_NUMERO;";

	   $query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al obtener datos de facturas y pedimentos.'; 
			$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
		} else {
			while($row = mysqli_fetch_object($query)){
				$__sFacturas = $row->facturas;
				$__sPedimentos = $row->pedimentos;
				$__sCaja = $row->cajas;
				break;
			} 
		}
	}
	
	if ($respuesta['Codigo'] == 1) {
		$COM_Object = 'CrystalRuntime.Application'; 
		$crapp = null;
		try {
			$crapp  = new COM ($COM_Object) or die("Unable to Create Object"); 
		} catch (com_exception $e) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']=''; 
			$respuesta['Error'] = ' ['.$e->getMessage().']';
		}
		
		if ($respuesta['Codigo'] == 1) {
			$sMyPdf = "expo_file_".$__sSolicitud.".pdf";
			
			try {
				$creport = $crapp->OpenReport($my_report, 1);	
				$creport->EnableParameterPrompting = 0; 
				$creport->FormulaSyntax = 0;  
				$creport->RecordSelectionFormula=$selection_formula;  
				$creport->DiscardSavedData;
				$creport->ReadRecords();
				$creport->ExportOptions->DiskFileName=$sMyPdf;
				$creport->ExportOptions->FormatType=31;
				$creport->ExportOptions->DestinationType=1;
				$creport->Export(false);
				$creport = null;
				$crapp = null;
				
				/****************************************************************************************/

				if ($__sReportada != 'S' || $bEnviar == true) {
					/* ..:: Revisamos si hay que notificar al agente aduanal ::.. */
					$__sFacturasPendientes = fcn_verificar_envio_notificacion_aaa();
					if ($__sFacturasPendientes != '') {
						//if ($__sReportadaAA != 'S') {
							$renvio = fcn_envia_notificacion_prefile($__sFacturasPendientes);
						
							if ($renvio['Codigo'] == 1) {
								$consulta="UPDATE bodega.salidas_expo
										   SET reportada_aa='S'
										   WHERE salidanumero=".$__sSolicitud;
										   
								$query = mysqli_query($cmysqli, $consulta);
								if (!$query) {
									$respuesta['Codigo']=-1;
									$respuesta['Mensaje']= 'Error al marcar como reportada aa por correo electronico '. mysqli_error($cmysqli); 
									$respuesta['Error'] = 'Error al marcar como reportada aa por correo electronico '. mysqli_error($cmysqli);
									//error_log('JC CORREOS SALIDA PREFILE: '.json_encode($renvio));
								}
							} else {
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']=$renvio['Mensaje']; 
								$respuesta['Error'] = '';
								//error_log('JC CORREOS SALIDA PREFILE: '.json_encode($renvio));
							}
						//} else {
						//	error_log('SALIDAEXPO :: SALIDA '.$__sSolicitud.' YA FUE REPORTADA A AGENTE ADUANAL AMERICANO');
						//}
					} else {
						$sFile = fcn_get_file_send($sMyPdf, $respuesta);
						
						//ENVIO DE CORREOS	
						if ($respuesta['Codigo'] == 1) {
							$aAdjuntos = array();
							
							array_push($aAdjuntos, $sFile);
							
							foreach($__aEnvioClientes as $row){
								//error_log('SALIDAEXPO :: CORREOS SALIDA '.$__sSolicitud.' CLIENTES TO: '.json_encode($row['to']));
								//error_log('SALIDAEXPO :: CORREOS SALIDA '.$__sSolicitud.' CLIENTES BCC: '.json_encode($row['bcc']));
								$respuesta = envia_rpt_clientes($aAdjuntos, $row['cliente'], $row['to'], $row['bcc']);
							}
							
							foreach($__aEnvioLineaTrans as $row){
								//error_log('SALIDAEXPO :: CORREOS SALIDA '.$__sSolicitud.' LINEATRANS TO: '.json_encode($row['to']));
								$respuesta = envia_rpt_lineatrans_transfer($row['cliente'], $row['to'], $row['bcc'], 'LTR');
							}
							
							foreach($__aEnvioTransfer as $row){
								//error_log('SALIDAEXPO :: CORREOS SALIDA '.$__sSolicitud.' TRANSFER TO: '.json_encode($row['to']));
								$respuesta=envia_rpt_lineatrans_transfer($row['cliente'], $row['to'], $row['bcc'], 'TRA');
							}							
							
							//ACTUALIZAR SALIDA COMO REPORTADA
							if ($respuesta['Codigo'] == 1 && $__sReportada != 'S') {
								$consulta="UPDATE bodega.salidas_expo
										   SET reportada='S'
										   WHERE salidanumero=".$__sSolicitud;
										   
								$query = mysqli_query($cmysqli, $consulta);
								if (!$query) {
									$respuesta['Codigo']=-1;
									$respuesta['Mensaje']='Error al marcar como reportada por correo electronico.'; 
									$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
								}
							}
						}
						
						if (file_exists($sFile)) { 
							unlink($sFile); //Eliminamos archivo
						}
					}
				}
			} catch(com_exception $error){
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']=''; 
				$respuesta['Error'] = ' ['.$error->getMessage().']';
			}
			
			if (file_exists($sMyPdf)) { 
				unlink($sMyPdf); //Eliminamos archivo
				/*@chmod($sMyPdf, 0777);
				@unlink($sMyPdf);*/
			}
		}
	}
	
	return $respuesta;
}

/* ..:: Correos del nuevo esquema::.. */
function fcn_get_email_notificacion_new() {
	global $__aEnvioClientes, $__aEnvioLineaTrans, $__aEnvioTransfer, $__sSolicitud, $cmysqli;
	
	$respuesta['Codigo']=1;
	
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
			   WHERE a.SALIDA_NUMERO=".$__sSolicitud;
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar emails nuevo esquema.'; 
		$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
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
		
		array_push($__aEnvioClientes, array('cliente'=>$sClientes,'to'=>$to,'bcc'=>$bcc));
	}
		
	if ($respuesta['Codigo'] == 1) {
		$bcc=array();
		$to=array();
	
		//NIVEL ENCABEZADO LINEA TRANSPRTISTA
		$consulta="SELECT c.email
				   FROM bodega.salidas_expo AS a LEFT JOIN
						bodega.contactos_expo AS c ON c.id_catalogo=a.nolineatransp AND
													  c.tipo_catalogo='LTR'
					WHERE a.salidanumero=".$__sSolicitud;
				   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar emails nuevo esquema encabezado.'; 
			$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
		} else {
			while($row = mysqli_fetch_object($query)){
				fcn_set_email_array($row->email, $to); //TRANS, ETC ETC
			}
		}
		
		if (count($to) > 0) {
			array_push($__aEnvioLineaTrans, array('cliente'=>$sClientes,'to'=>$to,'bcc'=>$bcc));
		}
	}
	
	if ($respuesta['Codigo'] == 1) { 
		$bcc=array();
		$to=array();
		
		$consulta="SELECT c.email
				   FROM bodega.salidas_expo AS a LEFT JOIN
						bodega.contactos_expo AS c ON c.id_catalogo=a.notransfer AND
													  c.tipo_catalogo='TRA'
					WHERE a.salidanumero=".$__sSolicitud;
				   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar emails nuevo esquema encabezado.'; 
			$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
		} else {
			while($row = mysqli_fetch_object($query)){
				fcn_set_email_array($row->email, $to); //TRANS, ETC ETC
			}
		}
		
		if (count($to) > 0) {
			array_push($__aEnvioTransfer, array('cliente'=>$sClientes,'to'=>$to,'bcc'=>$bcc));
		}
	}
	
	return $respuesta;
}

/* ..:: Quitamos duplicados::.. */
function fcn_set_email_array($sEmail, &$array) {
	global $__aEmailsListaEnviados;
	
	if (is_null($sEmail) == false && $sEmail != '') { 
		$sEmail = strtolower($sEmail);
	
		$bExist = array_search($sEmail, array_column($__aEmailsListaEnviados, 'email'));
		if (false === $bExist) {
			array_push($__aEmailsListaEnviados, array('email' => $sEmail));			
			array_push($array, $sEmail);
		}
	}	
}

/* ..:: Correos del viejo esquema::.. */
function fcn_get_email_notificacion_old($nocliente, $nombrecliente) {
	global $__aEnvioClientes, $__sSolicitud, $cmysqli;
	
	$respuesta['Codigo']=1;
	
	$consulta="SELECT to1, to2, to3, to4, to5, to6, to7, to8, to9, to10, 
				      cc1, cc2, cc3, cc4, cc5, cc6, cc7, cc8, cc9, cc10
			   FROM bodega.geocel_clientes_expo
			   WHERE f_numcli='".$nocliente."'";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar emails viejo esquema.'; 
		$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';	
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
			
			array_push($__aEnvioClientes, array('cliente'=>$nombrecliente,'to'=>$to,'bcc'=>$bcc));			
			break;
		} 
	}
	
	return $respuesta;
}

/* Verificar si se debe subir el prefile siempre y cuando sea benavides and company */
function fcn_verificar_envio_notificacion_aaa() {
	global $cmysqli, $__sSolicitud;
	
	$__sFacturasPendientes = '';
	
	$consulta="SELECT GROUP_CONCAT(FACTURA_NUMERO SEPARATOR ', ') AS facturas
			   FROM bodega.facturas_expo
			   WHERE SALIDA_NUMERO=".$__sSolicitud." AND 
				     NOAAA=58 AND
					 PREFILE_ID IS NULL";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		error_log('SALIDAEXPO ERROR :: Error al obtener facturas pendientes de prefile ['.mysqli_error($cmysqli).']');
	} else {
		while($row = mysqli_fetch_object($query)){
			$__sFacturasPendientes = $row->facturas;
			break;
		} 
	}
	
	return $__sFacturasPendientes;
}

/* Generamos un solo archivo pdf */
function fcn_get_file_send($sMyPdf, &$respuesta) {
	global $cmysqli, $__sSolicitud, $__sPathFilesExpo, $__sPathFilesPed2009, $__sPathFilesPermisos;
	
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
	array_push($aArchivos, $sMyPdf);
				
	/************************************************/
	/* 2 - Relacion de documentos */
	/************************************************/
	$sFileName = $__sPathFilesExpo . DIRECTORY_SEPARATOR . $__sSolicitud . "_reldocs.pdf";
	if (file_exists($sFileName)) { 
		array_push($aArchivos, $sFileName);
	}
	
	/************************************************/
	/* 3 - Prefile */
	/************************************************/
	/*$consulta="SELECT a.PREFILE_ID, b.nombre_archivo
			   FROM bodega.facturas_expo AS a INNER JOIN
				    bodega.documentos_expo AS b ON b.id_documento=a.PREFILE_ID
			   WHERE SALIDA_NUMERO=".$__sSolicitud."
			   GROUP BY a.PREFILE_ID";*/
	$consulta="SELECT a.PREFILE_ID, b.nombre_archivo, IF(b.id_doc_master IS NULL, b.id_documento, b.id_doc_master) AS id_documento_if
			   FROM bodega.facturas_expo AS a INNER JOIN
				    bodega.documentos_expo AS b ON b.id_documento=a.PREFILE_ID
			   WHERE SALIDA_NUMERO=".$__sSolicitud."
               GROUP BY id_documento_if";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar prefile'; 
		$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
	} else {
		while($row = mysqli_fetch_object($query)){		
			$sFileName = $__sPathFilesExpo . DIRECTORY_SEPARATOR . $row->nombre_archivo;
			if (file_exists($sFileName)) { 
				array_push($aArchivos, $sFileName);
			}
		}
	}
	
	/*$sFileName = $__sPathFilesExpo . DIRECTORY_SEPARATOR . $__sSolicitud . "_prefile.pdf";
	if (file_exists($sFileName)) { 
		array_push($aArchivos, $sFileName);
	}*/
	
	/************************************************/
	/* 4 - Notificacion de Arribo (NOA) */
	/************************************************/
	if ($respuesta['Codigo'] == 1) {
		$sFileName = $__sPathFilesExpo . DIRECTORY_SEPARATOR . $__sSolicitud . "_noa.pdf";
		if (file_exists($sFileName)) { 
			array_push($aArchivos, $sFileName);
		}
	}
	
	/************************************************/
	/* 5 - Pedimento o Remesas */
	/************************************************/	
	if ($respuesta['Codigo'] == 1) {
		$consulta="SELECT a.REFERENCIA, a.NUM_REM_PED, GROUP_CONCAT(DISTINCT b.nombre_archivo) AS TICKET_BASCULA_NAME
				   FROM bodega.facturas_expo AS a LEFT JOIN 
						bodega.documentos_expo AS b ON b.id_documento=a.TICKET_BASCULA_ID
				   WHERE a.SALIDA_NUMERO=".$__sSolicitud."
				   GROUP BY a.REFERENCIA, a.NUM_REM_PED
				   ORDER BY a.REFERENCIA, a.NUM_REM_PED";
				   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar remesas de salida'; 
			$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
		} else {
			$sRefActual = '';
			while($row = mysqli_fetch_object($query)){
				$sReferencia = $row->REFERENCIA;
				$sNumRemPed = $row->NUM_REM_PED;
				$sTipoPedimento = fcn_tipo_pedimento($sReferencia);
				$aTickets = explode(',', $row->TICKET_BASCULA_NAME);
				
				if ($sRefActual != $sReferencia) {
					$sRefActual = $sReferencia;
					
					if ($sTipoPedimento == 'normal') { 
						$sFileName = $__sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sReferencia . "-transportista.pdf";
						//error_log('Pedimento: '.$sTipoPedimento.' - '.$sFileName);
						if (file_exists($sFileName)) { 
							array_push($aArchivos, $sFileName);
						}
					} else { //Pedimento consolidado
						$sFileName = $__sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sReferencia . "-" . $sNumRemPed . ".pdf";
						//error_log('Pedimento: '.$sTipoPedimento.' - '.$sFileName);
						if (file_exists($sFileName)) { 
							array_push($aArchivos, $sFileName);
						}
					}
				} else {
					if ($sTipoPedimento == 'consolidado') { 
						$sFileName = $__sPathFilesPed2009 . DIRECTORY_SEPARATOR . $sReferencia . "-" . $sNumRemPed . ".pdf";
						if (file_exists($sFileName)) { 
							array_push($aArchivos, $sFileName);
						}
					}
				}
				
				if (count($aTickets) > 0) {
					foreach ($aTickets as &$ticket) { 
						if ($ticket != '') {
							$sFileName = $__sPathFilesExpo . DIRECTORY_SEPARATOR . $ticket;
							if (file_exists($sFileName)) { 
								array_push($aArchivos, $sFileName);
							}
						}
					}
				}
			}
		}
	}
	
	/************************************************/
	/* 6 - Avisos (Permisos) */
	/************************************************/
	if ($respuesta['Codigo'] == 1) {
		$consulta="SELECT REFERENCIA, NUM_REM_PED, CONS_FACT_PED
				   FROM bodega.facturas_expo
				   WHERE SALIDA_NUMERO=".$__sSolicitud."
				   ORDER BY REFERENCIA, NUM_REM_PED";
				   
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los permisos de salida'; 
			$respuesta['Error'] = ' ['.mysqli_error($cmysqli).']';
		} else {
			while($row = mysqli_fetch_object($query)){
				$sReferencia = $row->REFERENCIA;
				$sConsFactPed = $row->CONS_FACT_PED;
				
				fcn_permiso_nombre_archivo($sReferencia, $sConsFactPed, $aArchivos);
			}
		}
	}
	
	/************************************************/
	/* 7 - Solicitud de Retiro */
	/************************************************/
	$sFileName = $__sPathFilesExpo . DIRECTORY_SEPARATOR . $__sSolicitud . "_solicitud_retiro.pdf";
	if (file_exists($sFileName)) { 
		array_push($aArchivos, $sFileName);
	}
	
	/************************************************/
	/* Se arma archivo */
	/************************************************/
	
	$sFile = "salidaexportacion_".$__sSolicitud.".pdf";
	$sArchivos = '';
	foreach ($aArchivos as &$archivo) { 
		$sArchivos .= $archivo . ' ';
	}
	
	if ($sArchivos != '') {
		$sComando = '"C:\Program Files\gs\gs9.23\bin\gswin64" -dBATCH -dNOPAUSE -q -dSAFER -sDEVICE=pdfwrite -sOutputFile='.$sFile.' '.$sArchivos;

		$output = shell_exec($sComando);
		if ($output != '') {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']=$output; 
			$respuesta['Error'] = $output;
			//error_log('JC CORREOS SALIDA PREFILE: '.json_encode($respuesta));
			exit($respuesta);
		}
	}
	
	return $sFile;
}

/* ..:: consultamos si es Consolidado o Normal ::.. */
function fcn_tipo_pedimento($sReferencia) {
	include('../../connect_casa.php');
	
	$sTipoPedimento = 'normal';
	
	/* ..:: Consultamos referencia en casa ::.. */
	$consulta = "SELECT a.NUM_PEDI, a.PAT_AGEN, a.FIR_REME
				 FROM SAAIO_PEDIME a
				 WHERE a.NUM_REFE='".$sReferencia."'";
					 
	$query = odbc_exec($odbccasa, $consulta);
	if ($query==false){ 
		error_log('SALIDAEXPO ERROR :: Error al consultar el tipo de pedimento de la Referencia ['.$sReferencia.'] en el sistema CASA.');
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
	include('../../connect_casa.php');
	
	global $cmysqli, $__sPathFilesPermisos;
	
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
		error_log("SALIDAEXPO ERROR :: Error al consultar el numero de permiso de la Referencia [".$sReferencia."] en el sistema CASA.");
	} else {
		while(odbc_fetch_row($query)){ 
			$sNumPerm = (is_null(odbc_result($query,"NUM_PERM"))? '': odbc_result($query,"NUM_PERM"));
			
			$consulta="SELECT archivo_permiso
					   FROM bodega.permisos_pedimentos
					   WHERE numero_permiso='".$sNumPerm."'";
					   
			$query_mysql = mysqli_query($cmysqli, $consulta);
			if (!$query_mysql) {
				error_log('SALIDAEXPO ERROR :: Error al consultar permisos de pedimentos en mysql '. mysqli_error($cmysqli) .'.');
			}
			
			while($row = mysqli_fetch_object($query_mysql)){
				$sFileName = $row->archivo_permiso;
				if ($sFileName != '') {
					$sFileName = $__sPathFilesPermisos . DIRECTORY_SEPARATOR . $sFileName;
					if (file_exists($sFileName)) { 
						array_push($aArchivos, $sFileName);
					}
				}
			}
		}
	}
}

/**************************************************************************************************/
/* ENVIO DE CORREOS */
/**************************************************************************************************/
function fcn_envia_notificacion_prefile($sFacturasPendientes) {
	global $cmysqli, $__bDebug, $__sSolicitud, $__aEmailsListaEnviados, $__sCaja, $__sLineaT, $__sPedimentos;
	
	$__aEmailsListaEnviados=array();
	$adjuntos=array();
	$bcc=array();
	$to=array();
		
	$consulta="SELECT a.NUMCLIENTE, b.email, b.tipo_contacto
			   FROM bodega.facturas_expo AS a INNER JOIN
				    bodega.contactos_expo AS b ON (b.id_catalogo=a.NUMCLIENTE AND
												   b.tipo_catalogo='CLI' AND b.tipo_contacto = 'EJE') OR
												  (b.id_catalogo=a.NOAAA AND
												   b.tipo_catalogo='AAA')
			   WHERE a.SALIDA_NUMERO=".$__sSolicitud."
			   GROUP BY b.email";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar emails de ejecutivos '. mysqli_error($cmysqli); 
		$respuesta['Error'] = 'Error al consultar emails de ejecutivos '. mysqli_error($cmysqli);
		error_log('JC CORREOS SALIDA PREFILE: '.json_encode($respuesta));
		return $respuesta;
	} else {
		while($row = mysqli_fetch_object($query)){
			if ($row->tipo_contacto == 'EJE') { 
				fcn_set_email_array($row->email, $bcc); 
			} else {
				fcn_set_email_array($row->email, $to); //CLIENTES, AAA, TRANS, ETC ETC
			}
		}
	}
	
	//error_log('JC CORREOS SALIDA PREFILE '.$__sSolicitud.' CLIENTES TO: '.json_encode($to));
	//error_log('JC CORREOS SALIDA PREFILE '.$__sSolicitud.' CLIENTES BCC: '.json_encode($bcc));
	
	$asunto='Falta archivo Prefile en salida: '.$__sSolicitud;
	
	$asunto='Falta archivo Prefile en salida: '.$__sSolicitud;
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
											<span style="text-decoration: underline;">'.$__sSolicitud.'</span>
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
									<strong>Plataforma: </strong> '.$__sCaja.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Transportista: </strong> '.$__sLineaT.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Pedimento(s): </strong> '.$__sPedimentos.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #E4FFF3;" colspan="3">
									<strong>
										Estimado Agente Aduanal.
										<br>
										'.utf8_decode('Para editar la salida de ').' <a href="https://www.delbravoweb.com/'.(($__bDebug)? 'monitorpruebas' : 'monitor').'/panel/salidaExpo.php?id='.$__sSolicitud.'">'.utf8_decode('Click Aquí.').'</a>
										<br>
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
	
	if ($__bDebug) {
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
	global $__bDebug, $__sCaja, $__sLineaT, $__sFacturas, $__sPedimentos, $__sObservaciones, $__sReportada, $__sSolicitud;
	
	$asunto=utf8_decode((($__sReportada == 'S')? 'Modificación Salida de Exportación' : 'Salida de Exportación Automatica').', Cliente(s): '.$cliente);
	$sTitulo = (($__sReportada == 'S')? 'Modificación Salida de Exportación' : 'Salida de Exportación Automatica');
	
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
									<h2 '.(($__sReportada == 'S')? 'style="color: #E38F00;"' : '').'>'.utf8_decode($sTitulo).'</h2>
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
											<span style="text-decoration: underline;">'.$__sSolicitud.'</span>
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
									<strong>Factura(s): </strong> '.$__sFacturas.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Plataforma(s): </strong> '.$__sCaja.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Transportista: </strong> '.$__sLineaT.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Pedimento(s): </strong> '.$__sPedimentos.'
								</td>
							</tr>';
	if($__sObservaciones != '') {
		$sHTML .= '			<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Observaciones: </strong> '.$__sObservaciones.'
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
	
	if ($__bDebug) {
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

function envia_rpt_lineatrans_transfer($cliente, $to, $bcc, $tipo){
	global $__bDebug, $__sCaja, $__sLineaT, $__sFacturas, $__sPedimentos, $__sObservaciones, $__sReportada, $__sSolicitud;
	
	$asunto=utf8_decode((($__sReportada == 'S')? 'Modificación Salida de Exportación' : 'Salida de Exportación Automatica').', Cliente(s): '.$cliente);
	$sTitulo = (($__sReportada == 'S')? 'Modificación Salida de Exportación' : 'Salida de Exportación Automatica');
	
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
									<h2 '.(($__sReportada == 'S')? 'style="color: #E38F00;"' : '').'>'.utf8_decode($sTitulo).'</h2>
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
											<span style="text-decoration: underline;">'.$__sSolicitud.'</span>
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
									<strong>Factura(s): </strong> '.$__sFacturas.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Plataforma(s): </strong> '.$__sCaja.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Transportista: </strong> '.$__sLineaT.'
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Pedimento(s): </strong> '.$__sPedimentos.'
								</td>
							</tr>';
	if($__sObservaciones != '') {
		$sHTML .= '			<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #EFEFEF;" colspan="3">
									<strong>Observaciones: </strong> '.$__sObservaciones.'
								</td>
							</tr>';
	}
	$sHTML .= '				<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td style="background: #E4FFF3;" colspan="3">
									<strong>
										'.utf8_decode('Para descargar la documentación de la salida ').' <a href="https://www.delbravoweb.com/'.(($__bDebug)? 'monitorpruebas' : 'monitor').'/panel/showCarta_Instrucciones.php?solicitud='.$__sSolicitud.'&type=file&usr='.$tipo.'">'.utf8_decode('Click Aquí.').'</a>
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
	
	if ($__bDebug) {
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
		$respuesta['Codigo']=1;
		$respuesta['Mensaje']='El correo fue enviado correctamente.';
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']=$mail->ErrorInfo;
	}
	return $respuesta;
}