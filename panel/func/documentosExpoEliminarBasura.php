<?php
include ('../../connect_dbsql.php');
$sPathFilesExpo = "\\\\192.168.1.126\\documentos_expo\\salidaExpo";

$response['codigo']=1;

$consulta="SELECT a.id_documento, a.tipo, a.nombre_archivo, a.fecha_creacion
		   FROM bodega.documentos_expo AS a
		   WHERE a.tipo='PKL' AND 
		         a.fecha_creacion < ADDDATE(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'), INTERVAL - 7 DAY) AND
				 a.id_documento NOT IN (SELECT b.PACKING_LIST_ID
										FROM bodega.facturas_expo AS b
										WHERE b.PACKING_LIST_ID IS NOT NULL)
		   ORDER BY a.fecha_creacion";
		   
$query = mysqli_query($cmysqli, $consulta);
if (!$query) {
	$error=mysqli_error($cmysqli);
	$response['codigo']=-1;
	$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
} else {
	while($row = mysqli_fetch_object($query)){
		$consulta="DELETE FROM bodega.documentos_expo
				   WHERE id_documento=".$row->id_documento;
				   
		$query_del = mysqli_query($cmysqli, $consulta);
		if (!$query_del) {
			$error=mysqli_error($cmysqli);
			$response['codigo']=-1;
			$response['mensaje']='Error al eliminar duplicados: ' .$consulta.' , error:'.$error ;
		} else {
			$sFilePath = $sPathFilesExpo . DIRECTORY_SEPARATOR . $row->nombre_archivo;
			if (file_exists($sFilePath)) {
				unlink($sFilePath);
			}
		}
	}
}

if ($response['codigo']=1) {
	$consulta="SELECT a.id_documento, a.tipo, a.nombre_archivo, a.fecha_creacion
			   FROM bodega.documentos_expo AS a
			   WHERE a.tipo='CDO' AND 
			         a.fecha_creacion < ADDDATE(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'), INTERVAL - 7 DAY) AND
					 a.id_documento NOT IN (SELECT b.CERTIFICADO_ORIGEN_ID
											FROM bodega.facturas_expo AS b
											WHERE b.CERTIFICADO_ORIGEN_ID IS NOT NULL)
			   ORDER BY a.fecha_creacion";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
	} else {
		while($row = mysqli_fetch_object($query)){
			$consulta="DELETE FROM bodega.documentos_expo
					   WHERE id_documento=".$row->id_documento;
					   
			$query_del = mysqli_query($cmysqli, $consulta);
			if (!$query_del) {
				$error=mysqli_error($cmysqli);
				$response['codigo']=-1;
				$response['mensaje']='Error al eliminar duplicados: ' .$consulta.' , error:'.$error ;
			} else {
				$sFilePath = $sPathFilesExpo . DIRECTORY_SEPARATOR . $row->nombre_archivo;
				if (file_exists($sFilePath)) {
					unlink($sFilePath);
				}
			}
		}
	}
}

if ($response['codigo']=1) {
	$consulta="SELECT a.id_documento, a.tipo, a.nombre_archivo, a.fecha_creacion
			   FROM bodega.documentos_expo AS a
			   WHERE a.tipo='TDB' AND 
			         a.fecha_creacion < ADDDATE(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'), INTERVAL - 7 DAY) AND
					 a.id_documento NOT IN (SELECT b.TICKET_BASCULA_ID
											FROM bodega.facturas_expo AS b
											WHERE b.TICKET_BASCULA_ID IS NOT NULL)
			   ORDER BY a.fecha_creacion";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
	} else {
		while($row = mysqli_fetch_object($query)){
			$consulta="DELETE FROM bodega.documentos_expo
					   WHERE id_documento=".$row->id_documento;
					   
			$query_del = mysqli_query($cmysqli, $consulta);
			if (!$query_del) {
				$error=mysqli_error($cmysqli);
				$response['codigo']=-1;
				$response['mensaje']='Error al eliminar duplicados: ' .$consulta.' , error:'.$error ;
			} else {
				$sFilePath = $sPathFilesExpo . DIRECTORY_SEPARATOR . $row->nombre_archivo;
				if (file_exists($sFilePath)) {
					unlink($sFilePath);
				}
			}
		}
	}
}

if ($response['codigo']=1) {
	$consulta="SELECT a.id_documento, a.tipo, a.nombre_archivo, a.id_doc_master, a.caja, a.factura, a.fecha_creacion
			   FROM bodega.documentos_expo AS a
			   WHERE a.tipo='PRE' AND 
				     a.fecha_creacion < ADDDATE(DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00'), INTERVAL - 7 DAY) AND
					 a.id_documento NOT IN (SELECT b.PREFILE_ID
											FROM bodega.facturas_expo AS b
											WHERE b.PREFILE_ID IS NOT NULL)
			   ORDER BY a.fecha_creacion";
			   
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
	} else {
		while($row = mysqli_fetch_object($query)){
			$consulta="DELETE FROM bodega.documentos_expo
					   WHERE id_documento=".$row->id_documento;
					   
			$query_del = mysqli_query($cmysqli, $consulta);
			if (!$query_del) {
				$error=mysqli_error($cmysqli);
				$response['codigo']=-1;
				$response['mensaje']='Error al eliminar duplicados: ' .$consulta.' , error:'.$error ;
			} else {
				$sFilePath = $sPathFilesExpo . DIRECTORY_SEPARATOR . $row->nombre_archivo;
				if (file_exists($sFilePath)) {
					unlink($sFilePath);
				}
			}
		}
	}
}

mysqli_close($cmysqli);

echo json_encode($response);