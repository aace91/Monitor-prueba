<?php
include('./../../../connect_dbsql.php');
include('./../../../connect_casa.php');
include('./../../../bower_components/PHPMailer/PHPMailerAutoload.php');

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);
$__nCantidadDelbravo = 0;
	
//Aumentar TimeOut de la conexion
ini_set('mysql.connect_timeout', 300);
ini_set('default_socket_timeout', 300);

/***********************************************************************/

$respuesta['Codigo'] = 1;

$sReportName = 'Reporte Diario Permisos de Importación Utilizados';
$aToError = array('jcdelacruz@delbravo.com');
$sUrlPHP = '[monitor/panel/ajax/aviso_automatico_permisos_impo/notificacion_estado_permisos.php]';

$fechaSRV = date('j.m.Y');
//$fechaSRV = '28.02.2018';

/***********************************************************************/

//Obtenemos los permisos usados sin importar si es consolidado o normal
$sCasaQuery = "SELECT a.NUM_PERM, c.NOM_IMP
			   FROM SAAIO_PERMIS a INNER JOIN
				    SAAIO_PEDIME b ON b.NUM_REFE = a.NUM_REFE INNER JOIN 
                    CTRAC_CLIENT c ON c.CVE_IMP=b.CVE_IMPO
			   WHERE b.IMP_EXPO=1 AND
                     a.CVE_PERM NOT IN ('N1', 'N6', 'IR', 'NM', 'A1', 'T9') AND
				     b.FIR_PAGO IS NOT NULL AND 
					 b.FEC_PAGO='".$fechaSRV.", 00:00:00.000'
			   GROUP BY a.NUM_PERM, c.NOM_IMP
			   UNION
			   SELECT a.NUM_PERM, c.NOM_IMP
			   FROM SAAIO_PERPAR a INNER JOIN
				    SAAIO_PEDIME b ON b.NUM_REFE = a.NUM_REFE INNER JOIN 
					CTRAC_CLIENT c ON c.CVE_IMP=b.CVE_IMPO
			   WHERE b.FEC_ENTR >= '01.01.2018, 00:00:00.000' AND 
				     b.IMP_EXPO=1 AND
                     a.CVE_PERM NOT IN ('N1', 'N6', 'IR', 'NM', 'A1', 'T9') AND
					 b.FIR_PAGO IS NULL
			   GROUP BY a.NUM_PERM, c.NOM_IMP";
		   
$resp = odbc_exec ($odbccasa, $sCasaQuery) or die(odbc_error());
if ($resp == false){
	$respuesta['Codigo'] = -1;
	
	$mensaje = $sReportName . " :: Error al consultar referencias en el sistema CASA.".odbc_error();
	$asunto = "Error al enviar reporte de permisos ".$sUrlPHP;
	enviamail($asunto, $mensaje, $aToError, array());
} else {
	while(odbc_fetch_row($resp)){
		$respuesta['Codigo'] = 1;
		$sNumPermiso = odbc_result($resp,"NUM_PERM");
		//$sNumPermiso = '173300115A0569';
		$sIdCliente = 0;
		$sNombreCliente = odbc_result($resp,"NOM_IMP");
		$dtFechaIni = '';
		$dtFechaFin = '';
		$sHtmlDetalle = '';
		
		//Buscamos permiso en MYSQL
		$consulta = "SELECT a.numero_permiso, b.f_numcli, b.nombre, a.fecha_vigencia_ini, a.fecha_vigencia_fin
					 FROM bodega.permisos_pedimentos_impo AS a INNER JOIN
						  bodega.geocel_clientes AS b on b.f_numcli=a.id_cliente
					 WHERE a.numero_permiso='".$sNumPermiso."'";
		
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$respuesta['Codigo'] = -1;
	
			$error=mysqli_error($cmysqli);
			$mensaje = $sReportName . " :: Error al consultar permiso en MYSQL. ".$error;
			$asunto = "Error al enviar reporte de permisos ".$sUrlPHP;
			enviamail($asunto, $mensaje, $aToError, array());
			break;
		} else {
			if(mysqli_num_rows($query) > 0){
				echo '</br>Existe Permiso ['.$sNumPermiso.']';
				while($row = mysqli_fetch_object($query)){
					$sIdCliente = $row->f_numcli;
					$sNombreCliente = $row->nombre;
					$dtFechaIni = $row->fecha_vigencia_ini;
					$dtFechaFin = $row->fecha_vigencia_fin;						
					break;
				}
			} else {
				echo '</br>Error Permiso ['.$sNumPermiso.'] ';
				$respuesta['Codigo'] = -1;				
				//error_log('notificacion_estado_permisos.php :: notificar que no esta capturado el permiso a usuarios del CASA');
				fcn_enviar_notificacion_permiso($sNumPermiso, $sIdCliente, $sNombreCliente);
			}
		}
		
		/**********************************************/
		//Obtenemos las fracciones y generamos el html
		if ($respuesta['Codigo'] == 1) {
			$sHtmlDetalle = fcn_get_fracciones($sNumPermiso, $sIdCliente, $sNombreCliente);
		}
		
		if ($sHtmlDetalle != '') {
			fcn_enviar_notificacion('en_dia', $sNumPermiso, $sHtmlDetalle, $dtFechaIni, $dtFechaFin, $sIdCliente, $sNombreCliente);
		}
		
		echo '</br>----------------------------------------';
	}
}

//*********************************************************************************************************************************************
//ENVIAR AVISO DE PERMISOS PROXIMOS A VENCERCE (7 DIAS)
//*********************************************************************************************************************************************
$consulta = "SELECT p.numero_permiso, p.id_cliente, gc.nombre, p.fecha_vigencia_ini, p.fecha_vigencia_fin
			 FROM permisos_pedimentos_impo p INNER JOIN
				  geocel_clientes gc ON gc.f_numcli=p.id_cliente
			 WHERE NOW() >= ADDDATE(DATE_FORMAT(p.fecha_vigencia_fin,'%Y-%m-%d 00:00:00'), INTERVAL -7 DAY) AND 
                   NOW() <= p.fecha_vigencia_fin";
			 
$query = mysqli_query($cmysqli, $consulta);
if (!$query) {
	$error=mysqli_error($cmysqli);
	$mensaje = $sReportName . " :: Error al consultar permisos proximos a vencer en MYSQL. ".$error;
	$asunto = "Error al enviar reporte de permisos ".$sUrlPHP;
	enviamail($asunto, $mensaje, $aToError, array());
} else {
	while($row = mysqli_fetch_array($query)){
		$respuesta['Codigo'] = 1;
		
		$__nCantidadDelbravo = 0;
		
		$sNumPermiso = $row['numero_permiso'];
		$sIdCliente = $row['id_cliente'];
		$sNombreCliente = $row['nombre'];
		$dtFechaIni = $row['fecha_vigencia_ini'];
		$dtFechaFin = $row['fecha_vigencia_fin'];
		$sHtmlDetalle = '';
		
		/**********************************************/
		//Obtenemos las fracciones y generamos el html
		if ($respuesta['Codigo'] == 1) {
			$sHtmlDetalle = fcn_get_fracciones($sNumPermiso, $sIdCliente, $sNombreCliente);
		}
		
		//if ($__nCantidadDelbravo > 0) {
			echo '</br>Error Permiso Proximo a Vencer ['.$sNumPermiso.'] ';
			fcn_enviar_notificacion('por_vencer', $sNumPermiso, $sHtmlDetalle, $dtFechaIni, $dtFechaFin, $sIdCliente, $sNombreCliente);
		//}
		
		echo '</br>----------------------------------------';
	}
}
echo '<br>Proceso Terminado...';

function fcn_get_fracciones($sNumPermiso, $sIdCliente, $sNombreCliente) {
	global $odbccasa, $cmysqli, $aToError, $sUrlPHP, $__nCantidadDelbravo;		
	
	$sHtmlDetalle = '';
	$sFraccionesIgnorar = "'98020007', '98020015'";
	
	$sCasaQuery = "SELECT a.NUM_PERM, b.FRACCION
				   FROM SAAIO_PERPAR a INNER JOIN 
					    SAAIO_FACPAR b ON b.NUM_REFE=a.NUM_REFE AND
									      b.CONS_FACT=a.CONS_FACT AND 
										  b.CONS_PART=a.CONS_PART INNER JOIN 
						SAAIO_PEDIME c ON c.NUM_REFE=a.NUM_REFE                 
				   WHERE a.NUM_PERM='".$sNumPermiso."' AND
					     b.FRACCION NOT IN (".$sFraccionesIgnorar.") AND
						 c.FIR_PAGO IS NULL 
				   GROUP BY a.NUM_PERM, b.FRACCION
				   UNION
				   SELECT a.NUM_PERM, b.FRACCION AS FRACCION
				   FROM SAAIO_PERMIS a INNER JOIN 
						SAAIO_FRACCI b ON b.NUM_REFE=a.NUM_REFE AND 
									      b.NUM_PART=a.NUM_PART INNER JOIN 
						SAAIO_PEDIME c ON c.NUM_REFE=a.NUM_REFE  
				   WHERE a.NUM_PERM='".$sNumPermiso."' AND 
					     b.FRACCION NOT IN (".$sFraccionesIgnorar.") AND 
						 c.FIR_PAGO IS NOT NULL 
				   GROUP BY a.NUM_PERM, b.FRACCION";
		
	$respFrac = odbc_exec ($odbccasa, $sCasaQuery) or die(odbc_error());
	if ($respFrac == false){
		$respuesta['Codigo'] = -1;

		$mensaje = $sReportName . " :: Error al consultar fracciones en el sistema CASA.".odbc_error();
		$asunto = "Error al enviar reporte de permisos ".$sUrlPHP;
		enviamail($asunto, $mensaje, $aToError, array());
	} else {
		echo '</br>Buscando Fracciones de Permiso ['.$sNumPermiso.']';
		while(odbc_fetch_row($respFrac)){
			$sFraccion = odbc_result($respFrac,"FRACCION");
			echo '</br>Fraccion ['.$sFraccion.']';
			//$sFraccion = '1234567';
			$sCantidad = '';
			$sCantidadDelbravo = '';
			$nUnidad = 0;
			
			//Buscamos fraccion en MYSQL
			$consulta = "SELECT b.fraccion, b.cantidad, b.cantidad_delbravo, b.unidad
						 FROM bodega.permisos_pedimentos_impo AS a INNER JOIN
							  bodega.permisos_pedimentos_impo_det AS b on b.id_permiso=a.id_permiso
						 WHERE a.numero_permiso='".$sNumPermiso."' AND 
							   b.fraccion='".$sFraccion."'";
			
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$respuesta['Codigo'] = -1;
		
				$error=mysqli_error($cmysqli);
				$mensaje = $sReportName . " :: Error al consultar las fracciones en MYSQL. ".$error;
				$asunto = "Error al enviar reporte de permisos ".$sUrlPHP;
				enviamail($asunto, $mensaje, $aToError, array());
				break;
			} else {
				if(mysqli_num_rows($query) == 0){
					//error_log('notificacion_estado_permisos.php :: notificar que no esta capturada la fraccion a ejecutivos de geocel_clientes');
					fcn_enviar_notificacion_fraccion($sNumPermiso, $sIdCliente, $sNombreCliente, $sFraccion);
				} else {
					while($row = mysqli_fetch_object($query)){
						$sCantidad = $row->cantidad;
						$sCantidadDelbravo = $row->cantidad_delbravo;
						$nUnidad = $row->unidad;
						break;
					}
				}
			}
			
			if ($sCantidad != '') {
				$sHtmlDetalle .= fcn_get_detalle_fraccion($sNumPermiso, $sFraccion, $sHtmlDetalle, $sCantidad, $sCantidadDelbravo, $nUnidad);
			}
			
			$__nCantidadDelbravo = $sCantidadDelbravo;
		}
	}
	
	return $sHtmlDetalle;
}

/* Obtenemos correos electronicos */
function fcn_get_emails_geocel_clientes($sIdCliente) {
	global $cmysqli, $aToError, $sUrlPHP;
	
	$respuesta['Codigo'] = 1;
	
	$bcc=array();
	$to=array();
			
	$consulta = "SELECT to1, to2, to3, to4, to5, to6, to7, to8, to9, to10, 
					    cc1, cc2, cc3, cc4, cc5, cc6, cc7, cc8, cc9, cc10
				 FROM geocel_clientes
				 WHERE f_numcli=". $sIdCliente;
	
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$respuesta['Codigo'] = -1;

		$error=mysqli_error($cmysqli);
		$mensaje = $sReportName . " :: Error al consultar los correos electronicos en MYSQL. ".$error;
		$asunto = "Error al enviar reporte de permisos ".$sUrlPHP;
		enviamail($asunto, $mensaje, $aToError, array());
	} else {
		while($row = mysqli_fetch_object($query)){
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
			break;
		}
	}
	
	$respuesta['bcc'] = $bcc;
	$respuesta['to'] = $to;
	
	return $respuesta;
}

function get_email_usuarios_casa($sNumPermiso) {
	global $odbccasa, $aToError, $sUrlPHP;
	
	$respuesta['Codigo'] = 1;
	
	$bcc=array();
	$to=array();
	
	$sCasaQuery = "SELECT b.CVE_IMPO, a.NUM_PERM, d.USU_MAIL
				   FROM SAAIO_PERMIS a INNER JOIN
					    SAAIO_PEDIME b ON b.NUM_REFE = a.NUM_REFE INNER JOIN 
						SAAIO_PROCES c ON c.NUM_REFE = a.NUM_REFE INNER JOIN 
						SISSEG_USUARI d ON d.LOGIN = c.USU_CIER 
				   WHERE b.IMP_EXPO=1 AND a.NUM_PERM='".$sNumPermiso."'
				   GROUP BY b.CVE_IMPO, a.NUM_PERM, d.USU_MAIL
				   UNION
				   SELECT b.CVE_IMPO, a.NUM_PERM, d.USU_MAIL
				   FROM SAAIO_PERPAR a INNER JOIN
					    SAAIO_PEDIME b ON b.NUM_REFE = a.NUM_REFE INNER JOIN 
						SAAIO_PROCES c ON c.NUM_REFE = a.NUM_REFE INNER JOIN 
						SISSEG_USUARI d ON d.LOGIN = c.USU_CIER
				   WHERE b.IMP_EXPO=1 AND a.NUM_PERM='".$sNumPermiso."'
				   GROUP BY b.CVE_IMPO, a.NUM_PERM, d.USU_MAIL";
	
	$respDetalles = odbc_exec ($odbccasa, $sCasaQuery) or die(odbc_error());
	if ($respDetalles == false){
		$respuesta['Codigo'] = -1;

		$mensaje = $sReportName . " :: Error al consultar correos electronicos en el sistema CASA.".odbc_error();
		$asunto = "Error al enviar reporte de permisos ".$sUrlPHP;
		enviamail($asunto, $mensaje, $aToError);
	} else {
		while(odbc_fetch_row($respDetalles)){
			$sEmail = (is_null(odbc_result($respDetalles,"USU_MAIL"))? 0: odbc_result($respDetalles,"USU_MAIL"));
			
			if ($sEmail != '') {
				$bExist = array_search($sEmail, $bcc);					
				if ($bExist === false) { 
					array_push($bcc, $sEmail);
				}
			}			
		}
	}
	
	$respuesta['bcc'] = $bcc;
	$respuesta['to'] = $to;
	
	return $respuesta;
}

/**********************************************************************************/
/* HTML Diseños */
/**********************************************************************************/
function fcn_get_detalle_fraccion($sNumPermiso, $sFraccion, $sHtmlDetalle, $sCantidad, $sCantidadDelbravo, $nUnidad) {
	global $odbccasa, $sReportName, $sUrlPHP, $aToError;
	
	$sCasaQuery = "SELECT a.NUM_REFE, c.NUM_PEDI, a.NUM_PERM, b.CONS_FACT, b.FRACCION, SUM(b.CAN_FACT) AS CANTIDAD, b.UNI_FACT, c.NUM_REFEO
				   FROM SAAIO_PERPAR a INNER JOIN 
				        SAAIO_FACPAR b ON b.NUM_REFE=a.NUM_REFE AND
					  			     b.CONS_FACT=a.CONS_FACT AND 
								     b.CONS_PART=a.CONS_PART INNER JOIN 
				        SAAIO_PEDIME c ON c.NUM_REFE=a.NUM_REFE 
                   WHERE a.NUM_PERM='".$sNumPermiso."' AND
					     b.FRACCION='".$sFraccion."' AND 
						 c.FIR_PAGO IS NULL			
				   GROUP BY a.NUM_REFE, c.NUM_PEDI, a.NUM_PERM, b.CONS_FACT, b.FRACCION, b.UNI_FACT, c.NUM_REFEO
				   UNION
				   SELECT a.NUM_REFE, c.NUM_PEDI, a.NUM_PERM, b.NUM_PART AS CONS_FACT, b.FRACCION, SUM(b.CAN_FACT) AS CANTIDAD, b.UNI_FACT, c.NUM_REFEO
				   FROM SAAIO_PERMIS a INNER JOIN 
					    SAAIO_FRACCI b ON b.NUM_REFE=a.NUM_REFE AND 
										  b.NUM_PART=a.NUM_PART INNER JOIN 
						SAAIO_PEDIME c ON c.NUM_REFE=a.NUM_REFE
				   WHERE a.NUM_PERM='".$sNumPermiso."' AND
						 b.FRACCION='".$sFraccion."' AND
						 c.FIR_PAGO IS NOT NULL
				   GROUP BY a.NUM_REFE, c.NUM_PEDI, a.NUM_PERM, b.NUM_PART, b.FRACCION, b.UNI_FACT, c.NUM_REFEO";
	
	$respDetalles = odbc_exec ($odbccasa, $sCasaQuery) or die(odbc_error());
	$respDetallesRefeo = odbc_exec ($odbccasa, $sCasaQuery) or die(odbc_error());
	if ($respDetalles == false){
		$respuesta['Codigo'] = -1;

		$mensaje = $sReportName . " :: Error al consultar fracciones en el sistema CASA.".odbc_error();
		$asunto = "Error al enviar reporte de permisos ".$sUrlPHP;
		enviamail($asunto, $mensaje, $aToError);
	} else {
		/* Hay que filtrar los pedimentos originales en el caso de las rectificaciones */
		$aNUM_REFEO = array();
		while(odbc_fetch_row($respDetallesRefeo)){
			if (!is_null(odbc_result($respDetallesRefeo,"NUM_REFEO"))) {
				$sReferencia = odbc_result($respDetallesRefeo,"NUM_REFEO");
				if (!in_array($sReferencia, $aNUM_REFEO)) {
					array_push($aNUM_REFEO, $sReferencia);
				}
			}
		}
		
		/* Continuamos con el proceso normal*/
		$sHtmlBody = '<tr>
						  <td align="center" style="border: solid 1px #333;">Referencia</td>
		                  <td align="center" style="border: solid 1px #333; width:90px;">Partida</td>
		                  <td align="center" style="border: solid 1px #333; width:90px;">Utilizado</td>
		                  <td align="center" style="border: solid 1px #333; width:90px;">Saldo</td>
		              </tr>
					  <tr>
						  <td colspan="3" align="right" style="border: solid 1px #333;"><strong>Saldo Inicial&nbsp;<strong></td>
		                  <td align="right" style="border: solid 1px #333;"><strong>'.$sCantidadDelbravo.'&nbsp;<strong></td>
		              </tr>';
		
		while(odbc_fetch_row($respDetalles)){
			$sReferencia = odbc_result($respDetalles,"NUM_REFE");
			if (!in_array($sReferencia, $aNUM_REFEO)) {
				$sTrStyle = '';
				$sDescAdicional = '';
				
				$sCantidad = (is_null(odbc_result($respDetalles,"CANTIDAD"))? 0: odbc_result($respDetalles,"CANTIDAD"));
				$nUnidadFactura = (is_null(odbc_result($respDetalles,"UNI_FACT"))? 0: odbc_result($respDetalles,"UNI_FACT"));
				
				echo '<br>UnidadFacturaCASA: '. $nUnidadFactura . ' :: UnidadMYSQL: ' . $nUnidad;
				
				if ($nUnidadFactura != $nUnidad) {
					$sCantidad = 0;
					$sTrStyle = 'style="background: #EFE480"';
					$sDescAdicional = ' (Unidad de medida diferente)';
				}
				$sCantidadDelbravo = $sCantidadDelbravo - $sCantidad;
				
				$sHtmlBody .= '<tr '.$sTrStyle.'>
								   <td align="center" style="border: solid 1px #333;">'.odbc_result($respDetalles,"NUM_REFE").$sDescAdicional.'</td>
								   <td align="center" style="border: solid 1px #333;">'.odbc_result($respDetalles,"CONS_FACT").'</td>
								   <td align="center" style="border: solid 1px #333;">'.$sCantidad.'</td>
								   <td align="right" style="border: solid 1px #333;">'.$sCantidadDelbravo.'&nbsp;</td>
							   </tr>';
			}
		}
		
		//NORMAL
		$sBGTitulo = '#4472C4';
		$sColorTitulo = '#FFF';
		
		if($sCantidadDelbravo <= 0){
			//ROJO se sobrepasaron las cantidades de los permisos
			$sColorTitulo = '#FF6E6E';
			$sColorTitulo = '#000';
		}	
		
		$sHtmlTitle = ' <tr style="background-color: '.$sBGTitulo.'; color: '.$sColorTitulo.'; font-size:16px;">
                            <td align="center" colspan="4" style="border: solid 1px #333;"><strong> Fracci&oacute;n '.$sFraccion.'</strong></td>
                        </tr>' . $sHtmlBody . '
						<tr>
							<td colspan="3" align="right" style="border: solid 1px #333;"><strong>Saldo Final&nbsp;<strong></td>
						    <td align="right" style="border: solid 1px #333;"><strong>'.$sCantidadDelbravo.'&nbsp;<strong></td>
						</tr>
						<tr><td colspan="4">&nbsp;</td></tr>';
		
		$sHtmlDetalle .= $sHtmlTitle;
	}
	
	return $sHtmlDetalle;
}

function fcn_enviar_notificacion($action, $sNumPermiso, $sHtmlDetalle, $dtFechaIni, $dtFechaFin, $sIdCliente, $sNombreCliente) {
	global $bDebug;
	
	$respuesta = fcn_get_emails_geocel_clientes($sIdCliente);
	
	$bcc = $respuesta['bcc'];
	$to = $respuesta['to'];
	
	$sHtml = '
		<table style="border: solid 1px #bbbccc; width: 700px;" cellspacing="0" cellpadding="0">
			<tbody>
				<tr style="background-color: #0073b7; color: #fff;">
					<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
					<td width="10px">&nbsp;</td>
					<td align="center">
						<h1>REPORTE DIARIO DE PERMISOS IMPORTACI&Oacute;N</h1>
					</td>
					<td width="10px">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="4">
						<table width="100%" cellspacing="0" cellpadding="0">
							<tr><td colspan="8">&nbsp;</td></tr>
							<tr><td colspan="8" align="center"><h2>N&uacute;mero de Permiso: '.$sNumPermiso.'</h2></td></tr>
							<tr><td colspan="8">&nbsp;</td></tr>
							<tr>
								<td colspan="8" align="right"><big>Vigencia del: <strong> '.date_format(new DateTime($dtFechaIni),"d/m/Y").' </strong> al: <strong>'.date_format(new DateTime($dtFechaFin),"d/m/Y").'</strong></big></td>
							</tr>
							<tr><td colspan="8">&nbsp;</td></tr>
							<tr>
								<td colspan="8" align="left"><h3>Cliente: '.$sNombreCliente.'</h3></td>
							</tr>
							<tr><td colspan="8">&nbsp;</td></tr>';
							
	if($action == 'por_vencer'){
		$sHtml .= '         <tr style="background: #FF7A7A">
								<td colspan="8" align="center"><h2>EL PERMISO ESTA PROXIMO A VENCER EL DIA '.date_format(new DateTime($dtFechaFin),"d/m/Y").'</h2></td>
							</tr>
							<tr><td colspan="8">&nbsp;</td></tr>';
	}
	
	$sHtml .= '             <!--tr>
								<td style="background: #E4FFF3;" colspan="4" align="center"><h3>Totales del permiso</h3></td>
								<td style="background: #E4FFF3;" colspan="4" align="center"><h3>Totales asignados a Del Bravo</h3></td>
							</tr>
	                        <tr>
								<td colspan="4" align="center"><big><strong> XXXXXXXXX</strong></big></td>
								<td colspan="4" align="center"><big><strong>XXXXXXXXX</strong></big></td>
							</tr>
							<tr><td colspan="8">&nbsp;</td></tr>
							<tr style="background-color: #6DCAFF; color: #000;" >
								<td colspan="8" align="center"><h3>Saldo del permiso en Del Bravo</h3></td>
							</tr>
							<tr style="background-color: #0073b7; color: #fff;"><td colspan="8">&nbsp;</td></tr>
							<tr style="background-color: #0073b7; color: #fff;">
								<td colspan="8" align="right"><big>Saldo Restante: <strong>XXXXXXXXX</strong></big></td>
							</tr>
							<tr style="background-color: #0073b7; color: #fff;"><td colspan="8">&nbsp;</td></tr>
							<tr><td colspan="8">&nbsp;</td></tr-->
							<tr>
								<td style="background: #E4FFF3;" colspan="8" align="left"><h4>Desglose de uso de permiso asignado a Del Bravo</h4></td>
							</tr>
							<tr><td colspan="8">&nbsp;</td></tr>
							<tr>
								<td colspan="8">
									<table width="100%" cellspacing="0" cellpadding="0">
										'.$sHtmlDetalle.'
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
		</table>';
	
	$asunto = 'Reporte diario de permisos. Cliente: '.$sNombreCliente.' | Permiso: '.$sNumPermiso;
	
	echo '</br>NOTIFICACION :: '.json_encode($to);
	echo '</br>NOTIFICACION :: '.json_encode($bcc);
	if ($bDebug) {
		$to=array();
		$bcc=array();
	}
	array_push($bcc,'jcdelacruz@delbravo.com');
	
	enviamail($asunto, $sHtml, $to, $bcc);
}

function fcn_enviar_notificacion_fraccion($sNumPermiso, $sIdCliente, $sNombreCliente, $sFraccion) { 
	global $bDebug;
	
	$respuesta = fcn_get_emails_geocel_clientes($sIdCliente);
	
	$bcc = array();
	$to = $respuesta['bcc'];
	
	$sHtml = '
		<table style="border: solid 1px #bbbccc; width: 700px;" cellspacing="0" cellpadding="0">
			<tbody>
				<tr style="background-color: #0073b7; color: #fff;">
					<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
					<td width="10">&nbsp;</td>
					<td align="center">
						<h1>LA FRACCI&Oacute;N NO EXISTE EN PERMISOS IMPORTACI&Oacute;N</h1>
					</td>
					<td width="10px">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="4">
						<table width="100%" cellspacing="0" cellpadding="0">
							<tbody>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2" align="left"><h3>N&uacute;mero de Permiso: '.$sNumPermiso.'</h3></td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2" align="left"><h3>Fracci&oacute;n: '.$sFraccion.'</h3></td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2" align="left"><h3>Cliente: '.$sNombreCliente.'</h3></td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr style="background: #EFE480">
									<td colspan="2" align="center"><h3>Es necesario agrega la informaci&oacute;n del permiso en el <a href="https://delbravoweb.com/monitor">[Monitor Del Bravo/Permisos.]</a> Para notificar el estado actual del mismo.</h3></td>
								</tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr style="background-color: #0073b7; color: #fff;"><td colspan="2">&nbsp;</td></tr>
								<tr>
									<td colspan="2">
									<p>Este correo fue enviado de forma autom&aacute;tica y no es necesario responder al mismo. &iexcl;Muchas Gracias!.</p>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>';
		
	$asunto = utf8_decode('Fracción inexistente en el sistema. Cliente: '.$sNombreCliente.' | Permiso: '.$sNumPermiso);
	
	echo '</br>FRACCION NO EXISTE :: '.json_encode($to);
	if ($bDebug) {
		$to=array();
		$bcc=array();
	} else {
		array_push($bcc,'abisaicruz@delbravo.com');
	}
	array_push($bcc,'jcdelacruz@delbravo.com');
	
	enviamail($asunto, $sHtml, $to, $bcc);
}

function fcn_enviar_notificacion_permiso($sNumPermiso, $sIdCliente, $sNombreCliente) { 
	global $bDebug;

	$respuesta = get_email_usuarios_casa($sNumPermiso);
	
	$bcc = array();
	$to = $respuesta['bcc'];
	
	$sHtml = '
		<table style="border: solid 1px #bbbccc; width: 700px;" cellspacing="0" cellpadding="0">
			<tbody>
				<tr style="background-color: #0073b7; color: #fff;">
					<td style="background-color: #fff;" width="100px"><img src="https://www.delbravoapps.com/webtoolspruebas/images/logo.png" alt="Del Bravo" width="90" height="90" /></td>
					<td width="10">&nbsp;</td>
					<td align="center">
						<h1>EL PERMISO DE IMPORTACI&Oacute;N NO EXISTE</h1>
					</td>
					<td width="10px">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="4">
						<table width="100%" cellspacing="0" cellpadding="0">
							<tbody>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2" align="left"><h3>N&uacute;mero de Permiso: '.$sNumPermiso.'</h3></td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2" align="left"><h3>Cliente: '.$sNombreCliente.'</h3></td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr style="background: #EFE480">
									<td colspan="2" align="center"><h3>Es necesario agrega la informaci&oacute;n del permiso en el <a href="https://delbravoweb.com/monitor">[Monitor Del Bravo/Permisos.]</a> Para notificar el estado actual del mismo.</h3></td>
								</tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr style="background-color: #0073b7; color: #fff;"><td colspan="2">&nbsp;</td></tr>
								<tr>
									<td colspan="2">
									<p>Este correo fue enviado de forma autom&aacute;tica y no es necesario responder al mismo. &iexcl;Muchas Gracias!.</p>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>';
		
	$asunto = utf8_decode('Permiso inexistente en el sistema. Cliente: '.$sNombreCliente.' | Permiso: '.$sNumPermiso);
	
	echo '</br>PERMISO NO EXISTE :: '.json_encode($to);
	if ($bDebug) {
		$to=array();
		$bcc=array();
	} else {
		array_push($bcc,'abisaicruz@delbravo.com');
	}
	array_push($bcc,'jcdelacruz@delbravo.com');
	
	enviamail($asunto, $sHtml, $to, $bcc);
}

/**********************************************************************************/
/* Notificaciones */
/**********************************************************************************/

function enviamail($asunto, $mensaje, $to, $bcc){
	global $sReportName;
	
	$mailserver = 'mail.delbravo.com';
	$portmailserver = '587';
	$sender = 'avisosautomaticos@delbravo.com';
	$pass = 'aviaut01';
	
	$mail = new PHPMailer();
	//Luego tenemos que iniciar la validación por SMTP:
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	//$mail->SMTPSecure = "tls";
	$mail->Host = $mailserver; // SMTP a utilizar. Por ej. smtp.elserver.com
	$mail->Username = $sender; // Correo completo a utilizar
	$mail->Password = $pass; // Contraseña
	$mail->Port = $portmailserver; // Puerto a utilizar
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
	
	$mail->IsHTML(true); // El correo se envía como HTML
	$mail->Subject = $asunto; // Este es el titulo del email.
	$mail->Body = $mensaje; // Mensaje a enviar
	$exito = $mail->Send(); // Envía el correo.

	//También podríamos agregar simples verificaciones para saber si se envió:
	if(!$exito){
		error_log($sReportName.' :: Error al enviar el correo electronico. ['.$mail->ErrorInfo.']');
	}
	return true;
}