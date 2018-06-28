<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	$respuesta['Codigo'] = 1;
	
	//***********************************************************//

	$fecha_registro =  date("Y-m-d H:i:s");

	
	/********************************************/
	/* ..:: Base de datos casa ::.. */
	$mysqlserver_casa="192.168.1.107:3309";
	$mysqldb_casa="casa";
	$mysqluser_casa="root";
	$mysqlpass_casa="Marianar0117c";

	//$cmysql_casa = mysql_connect($mysqlserver_casa,$mysqluser_casa,$mysqlpass_casa);
	//mysql_select_db($mysqldb_casa) or die("Error al conectarse a la base de datos de casa");
	$cmysqli_casa = mysqli_connect($mysqlserver_casa,$mysqluser_casa,$mysqlpass_casa,$mysqldb_casa);
	if ($cmysqli_casa->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de casa: ".$cmysqli_casa->connect_error;
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = $mensaje;
		error_log(json_encode($respuesta));
	}
	
	//***********************************************************//
	
	/*****************************************************/
	/* PASO 1 - BUSCAR FACTURAS EN ACCEOSS */
	/*****************************************************/
	if ($respuesta['Codigo'] == 1) {
		$consulta = "SELECT a.id_folio, a.referencia, a.aduana, a.patente, a.pedimento, b.id_registro, b.factura
					 FROM bodega.expos_seguimiento AS a INNER JOIN
						  bodega.expos_seguimiento_facturas AS b ON b.id_folio = a.id_folio
					 WHERE a.fecha_linea_transportista IS NOT NULL AND
						   a.fecha_salida_creada IS NULL AND 
						   b.fecha_verificado IS NULL
					 ORDER BY a.id_folio";

		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los folios. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			while ($row = mysqli_fetch_array($query)){
				$id_registro = $row["id_registro"];
				$factura = $row["factura"];
				
				$conn_access = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=$rutaexposmdb", '', '');
				if ($conn_access==false){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error al conectarse a la base de datos de Expos.mdb ".odbc_errormsg ($conn_access);
					$respuesta['Error'] = '';
				} else {
					$consulta="SELECT salidas.salidanumero,
									  salidas.fecha,
									  salidas.hora,
									  salidas.caja,
									  salidas.nocliente,
									  facturas.REFERENCIA,
									  facturas.PEDIMENTO,
									  facturas.FACTURA_NUMERO
							   FROM salidas INNER JOIN 
									facturas ON facturas.SALIDA_NUMERO = salidas.salidanumero
							   WHERE salidas.nocliente='PISA' AND
									 salidas.salidanumero >= 118069 AND
									 facturas.FACTURA_NUMERO='".$factura."'";
					$query_facturas = odbc_exec($conn_access, $consulta);
					if ($query_facturas==false){
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']="Error en consultar la lista de salidas error:".odbc_errormsg ($conn_access);
						$respuesta['Error']='';
					} else {
						while ($ofactura = odbc_fetch_array($query_facturas)){ 
							/* ..:: Actualizamos en Mysql ::.. */
							$consulta="UPDATE bodega.expos_seguimiento_facturas
									   SET fecha_verificado='".$fecha_registro."',
										   referencia='".$ofactura['REFERENCIA']."',
										   id_salida=".$ofactura['salidanumero']."
									   WHERE id_registro=".$id_registro;
										
							$query_mysql = mysqli_query($cmysqli,$consulta);		
							if ($query_mysql==false){
								$error=mysqli_error($cmysqli);
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje'] = 'Error al actualizar la factura ['.$factura.']';
								$respuesta['Error']=' ['.$error.']';
							}
							
							break;
						}
					}
				}
			}
		}
	}
	
	/*****************************************************/
	/* PASO 2 - BUSCAR (ADU, PAT, PED)EN CASA */
	/*****************************************************/
	if ($respuesta['Codigo'] == 1) { 
		$consulta = "SELECT a.id_folio, facturas.referencia, facturas.facturas_fecha, facturas.id_salida
					 FROM bodega.expos_seguimiento AS a INNER JOIN
						 (SELECT b.id_folio,
								 SUM(IF(b.fecha_verificado IS NULL,1,0)) as facturas_fecha,
								 b.referencia,
								 b.id_salida
						  FROM bodega.expos_seguimiento_facturas AS b
						  GROUP BY b.id_folio) AS facturas ON facturas.id_folio = a.id_folio																				
					 WHERE a.fecha_linea_transportista IS NOT NULL AND
						   a.fecha_salida_creada IS NULL AND
						   facturas.facturas_fecha = 0";

		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los folios. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			while ($row = mysqli_fetch_array($query)){ 
				$id_folio = $row["id_folio"];
				$referencia = $row["referencia"];
				$salida = $row["id_salida"];
				
				$baseSql = "SELECT a.NUM_REFE, a.ADU_ENTR, a.PAT_AGEN, a.NUM_PEDI
							FROM SAAIO_PEDIME a
							WHERE a.NUM_REFE = '".$referencia."'";
				
				$result = odbc_exec ($odbccasa, $baseSql);
				if ($result!=false){ 
					while(odbc_fetch_row($result)){ 
						$ADU_ENTR= odbc_result($result,"ADU_ENTR");
						$PAT_AGEN= odbc_result($result,"PAT_AGEN");
						$NUM_PEDI= odbc_result($result,"NUM_PEDI");
					
						$consulta="UPDATE bodega.expos_seguimiento
								   SET referencia='".$referencia."',
									   aduana='".$ADU_ENTR."',
									   patente='".$PAT_AGEN."',
									   pedimento='".$NUM_PEDI."',
									   id_salida=".$salida.",
									   fecha_salida_creada='".$fecha_registro."'
								   WHERE id_folio=".$id_folio;
									
						$query_mysql = mysqli_query($cmysqli,$consulta);		
						if ($query_mysql==false){
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje'] = 'Error al actualizar el folio ['.$id_folio.']';
							$respuesta['Error']=' ['.$error.']';
						} else {
							$consulta = "INSERT INTO bodega.expos_seguimiento_historico_sts
										 (id_folio, descripcion, fecha)
										 VALUES (
										 ".$id_folio.",
										 'Aprobación Pendiente',
										 '".$fecha_registro."')";

							$query_historico = mysqli_query($cmysqli,$consulta);
							if (!$query_historico) {
								$error=mysqli_error($cmysqli);
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']='Error al insertar el estatus. Por favor contacte al administrador del sistema.'; 
								$respuesta['Error'] = ' ['.$error.']';
							}
						}
					}
				}
			}
		}
	}
	
	/*****************************************************/
	/* PASO 3 - VERIFICAR ESTATUS DE SOIA */
	/*****************************************************/
	/*if ($respuesta['Codigo'] == 1) {
		$consulta = "SELECT a.id_folio, a.referencia
					 FROM bodega.expos_seguimiento AS a 
					 WHERE a.fecha_salida_creada IS NOT NULL AND
						   a.fecha_desaduanado IS NULL";

		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los folios para consulta al SOIA.NET. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$aHistorico = [];
			
			while ($row = mysqli_fetch_array($query)){
				$id_folio = $row["id_folio"];
				$referencia = $row["referencia"];
				
				$consulta = "SELECT a.descripcion
							 FROM bodega.expos_seguimiento_historico_sts AS a
							 WHERE a.id_folio=".$id_folio;

				$query_historico = mysqli_query($cmysqli,$consulta);
				if (!$query_historico) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al consultar el historico para consulta al SOIA.NET. Por favor contacte al administrador del sistema.'; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					while ($oHistorico = mysqli_fetch_array($query_historico)){ 
						array_push($aHistorico, $oHistorico['descripcion']);
					}
				}
				
				//**********************************************
			
				$consulta = "SELECT b.id_estado_detalle, b.fecha
							 FROM casa.soia_situacion_pedime AS a INNER JOIN
								  casa.soia_eventos AS b ON b.id_sit_pedime = a.id_sit_pedime
							 WHERE a.num_refe='".$referencia."' AND
								   b.id_estado_detalle IN (310, 510, 700, 705, 710, 720, 730, 750, 760)
							 GROUP BY b.id_estado_detalle
							 ORDER BY b.id_evento";

				$query_soia = mysqli_query($cmysqli_casa,$consulta);
				if (!$query_soia) {
					$error=mysqli_error($cmysqli_casa);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al consultar estatus del SOIA.NET. Por favor contacte al administrador del sistema.'.$consulta; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					while ($oSoia = mysqli_fetch_array($query)){ 
						$fecha = $oSoia['fecha'];
						$bInsertar = false;
						$sDescripcion = '';
						
						if($oSoia['id_estado_detalle'] == 310 || $oSoia['id_estado_detalle'] == 510) {
							if (!in_array("Rojo en Aduana", $aHistorico)) { 
								$bInsertar = true;
								$sDescripcion = 'Rojo en Aduana';
							}
						} else {
							if (!in_array("Desaduanado", $aHistorico)) { 
								$bInsertar = true;
								$sDescripcion = 'Desaduanado';
							}
						}
						
						if ($bInsertar == true) {
							$consulta = "INSERT INTO bodega.expos_seguimiento_historico_sts
										 (id_folio, descripcion, fecha)
										 VALUES (
										 ".$id_folio.",
										 '".$sDescripcion."',
										 '".$fecha."')";

							$query_historico = mysqli_query($cmysqli,$consulta);
							if (!$query_historico) {
								$error=mysqli_error($cmysqli);
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']='Error al insertar el estatus. Por favor contacte al administrador del sistema.'; 
								$respuesta['Error'] = ' ['.$error.']';
							}
						}
					}
				}
			}
			
			$respuesta['aHistorico'] = $aHistorico;
		}
	}*/
	
	echo json_encode($respuesta);
}