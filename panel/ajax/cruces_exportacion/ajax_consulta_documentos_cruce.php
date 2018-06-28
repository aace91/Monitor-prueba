<?php 
	include_once('./../../../checklogin.php');
	require('./../../../connect_dbsql.php');
	include('./../../../url_archivos.php');

	if($loggedIn == false){
		echo '500';
	} else {
		if (isset($_POST['id_cruce']) && !empty($_POST['id_cruce'])) {  
			$respuesta['Codigo']=1;
			$id_cruce = $_POST['id_cruce'];
			
			//Consultar facturas xml para mostrar impresion
			$consulta = "SELECT ced.id_detalle_cruce,ced.regimen,ced.numero_factura,ced.archivo_factura,ced.archivo_cfdi,
								IFNULL(ced.archivo_cert_origen,'') AS archivo_cert_origen,
								IFNULL(ced.archivo_packinglist,'') AS archivo_packinglist,
								IFNULL(ced.archivo_ticketbascula,'') AS  archivo_ticketbascula,
								IF(cp.id_permiso IS NULL, '' ,pp.archivo_permiso) AS nombre_permiso,
								IF(cp.id_permiso IS NULL, '' ,CONCAT('http://www.delbravoweb.com/docs_permisos/',pp.archivo_permiso)) AS archivo_permiso,
								IF(cp.id_permiso_adhesion IS NULL, '' ,pa.archivo_permiso) AS nombre_permiso_adhesion,
								IF(cp.id_permiso_adhesion IS NULL, '' ,CONCAT('http://www.delbravoweb.com/docs_permisos/',pa.archivo_permiso)) AS archivo_permiso_adhesion
							FROM cruces_expo_detalle ced
								LEFT JOIN cruces_expo_permisos cp ON
									ced.id_detalle_cruce = cp.id_detalle_cruce
								LEFT JOIN permisos_pedimentos pp ON
									cp.id_permiso = pp.id_permiso
								LEFT JOIN permisos_adhesion pa ON
									cp.id_permiso_adhesion = pa.id_permiso_adhesion
							WHERE ced.id_cruce = ".$id_cruce."
							GROUP BY ced.id_detalle_cruce";
			
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar la informacion del cruce. Por favor contacte el administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}else{
				$aFacturas = array();
				while ($row = mysqli_fetch_array($query)){
					$Factura['id_detalle_cruce'] = $row['id_detalle_cruce'];
					$Factura['numero_factura'] = $row['numero_factura'];
					$Factura['archivo_factura'] = $row['archivo_factura'];
					$Factura['copias_factura'] = '1';
					$Factura['archivo_cfdi'] = $row['archivo_cfdi'];
					$Factura['copias_cfdi'] = ($row['regimen'] == 'A1' ? '1' :'0');
					$Factura['archivo_cert_origen'] = $row['archivo_cert_origen'];
					$Factura['copias_cert_origen'] = ($row['archivo_cert_origen'] == '' ? '0' : '1');
					$Factura['archivo_packinglist'] = $row['archivo_packinglist'];
					$Factura['copias_packinglist'] = ($row['archivo_packinglist'] == '' ? '0' : '1');
					$Factura['archivo_ticketbascula'] = $row['archivo_ticketbascula'];
					$Factura['copias_ticketbascula'] = ($row['archivo_ticketbascula'] == '' ? '0' : '1');
					$Factura['nombre_permiso'] = $row['nombre_permiso'];
					$Factura['archivo_permiso'] = $row['archivo_permiso'];
					$Factura['copias_permiso'] = ($row['archivo_permiso'] == '' ? '0' : '1');
					$Factura['nombre_permiso_adhesion'] = $row['nombre_permiso_adhesion'];
					$Factura['archivo_permiso_adhesion'] = $row['archivo_permiso_adhesion'];
					//Copias en 0 porque no se digitaliza
					$Factura['copias_permiso_adhesion'] = '0';
					array_push($aFacturas,$Factura);
				}
				$respuesta['aFacturas'] = $aFacturas;
			}
		}else{
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
		}
		echo json_encode($respuesta);
	}
?>