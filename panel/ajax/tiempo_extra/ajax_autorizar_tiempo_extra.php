<?php
$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);

$loggedIn = true;
$bLinkCorreo = true;
$id_solicitud = '';
$App = 'Ejecutivo';

if (isset($_GET['isd']) && !empty($_GET['isd'])) {
	$id_solicitud = $_GET['isd'];
	$Tipo = $_GET['tp'];
	if ($Tipo == 'E') {
		$App = 'Ejecutivo';
	} else if($Tipo == 'B') {
		$App = 'Bodega';
	} else {
		$App = 'Cliente';
	}
} else {
	include_once('./../../../checklogin.php');
	$bLinkCorreo = false;
}

require('./../../../connect_dbsql.php');
include('enviar_notificacion_email.php');

if($loggedIn == false){
	exit('500');
} else {
	$respuesta['Codigo'] = 1;

	$fecha_registro =  date("Y-m-d H:i:s");

	if ($bLinkCorreo == false) {
		if (isset($_POST['id_solicitud']) && !empty($_POST['id_solicitud'])) {
			$id_solicitud = $_POST['id_solicitud'];
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
		}
	}

	if ($respuesta['Codigo'] == 1) { 
		$consulta = "SELECT fecha_autorizo_bodega, fecha_autorizo_cliente, fecha_autorizo_ejecutivo, fecha_rechazo
					 FROM bodega.tiempo_extra
					 WHERE id_solicitud = ".$id_solicitud;
					 
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar autorizaciones. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
		} else { 
			while($row = mysqli_fetch_object($query)){
				$fecha_autorizo_bodega = (is_null($row->fecha_autorizo_bodega)? '' : $row->fecha_autorizo_bodega);
				$fecha_autorizo_cliente = (is_null($row->fecha_autorizo_cliente)? '' : $row->fecha_autorizo_cliente);
				$fecha_autorizo_ejecutivo = (is_null($row->fecha_autorizo_ejecutivo)? '' : $row->fecha_autorizo_ejecutivo);
				$fecha_rechazo =(is_null($row->fecha_rechazo)? '' : $row->fecha_rechazo);
				
				echo $fecha_rechazo;
				if ($fecha_rechazo == '') {
					switch ($App) {
						case 'Bodega':
							if ($fecha_autorizo_bodega != '') {
								$respuesta['Codigo'] = 100;
								$respuesta['Mensaje'] = 'La solicitud ya ha sido aprobada por Bodega, no se realizaron cambios.';
							}
							break;
							
						case 'Cliente':
							if ($fecha_autorizo_cliente != '') {
								$respuesta['Codigo'] = 100;
								$respuesta['Mensaje'] = 'La solicitud ya ha sido aprobada por el Cliente, no se realizaron cambios.';
							}
							break;
							
						case 'Ejecutivo':
							if ($fecha_autorizo_ejecutivo != '') {
								$respuesta['Codigo'] = 100;
								$respuesta['Mensaje'] = 'La solicitud ya ha sido aprobada por el Ejecutivo, no se realizaron cambios.';
							}
							break;
							
						default:
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = 'Tipo de aplicación incorrecta.';
					}
				} else {
					$respuesta['Codigo'] = 100;
					$respuesta['Mensaje'] = 'Solicitud previamente rechazada, no se realizaron cambios.';
				}
				break;
			} 
		}
	}
	
	if ($respuesta['Codigo'] == 1) {
		$consulta = "UPDATE tiempo_extra
					 SET ".get_columna_actualizar($App)." = '".$fecha_registro."'
					 WHERE id_solicitud = ".$id_solicitud;

		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al guardar autorizacion. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
		} else { 
			$respuesta = enviar_correo_notificacion($id_solicitud, $pdo_mysql_sconn, $pdo_accss_sconn, $mysqluser, $mysqlpass, $bDebug, $App);
			if($respuesta['Codigo'] != 1){	
				$respuesta['Codigo'] = 1;
				$respuesta['Mensaje'] = 'Solicitud de servicio prioritario se autorizó correctamente. Error al enviar notificación al cliente. ['.$respuesta['Error'].']';
			} else {
				$respuesta['Codigo'] = 1;
				$respuesta['Mensaje'] = 'Solicitud de servicio prioritario se autorizó correctamente.';
			}
		}
	}

	if ($bLinkCorreo == false) { 
		echo json_encode($respuesta);
	} else {
		if ($respuesta['Codigo'] == 1) { 
			exit(get_html_ok_description($respuesta['Mensaje'] . ' Referencia: '. $respuesta['Referencia']));
		} else if ($respuesta['Codigo'] == 100) { 
			exit(get_html_warning_description($respuesta['Mensaje']));
		} else {
			exit(get_html_error_description($respuesta['Mensaje']));
		}
	}

	/*if (isset($_POST['id_solicitud']) && !empty($_POST['id_solicitud'])) {  
		$respuesta['Codigo'] = 1;
		
		$id_solicitud = $_POST['id_solicitud'];
		$fecha_registro =  date("Y-m-d H:i:s");
		
		$consulta = "UPDATE tiempo_extra 
					 SET fecha_autorizo_ejecutivo = '".$fecha_registro."'
					 WHERE id_solicitud = ".$id_solicitud;
		
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al guardar autorizacion. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
		} else { 
			//$respuesta['Mensaje'] = 'Solicitud de servicio prioritario se autorizó correctamente.';
		
			$RespEmail = enviar_correo_notificacion($id_solicitud, $pdo_mysql_sconn, $pdo_accss_sconn, $mysqluser, $mysqlpass, $bDebug, 'Ejecutivo');
			if($RespEmail['Codigo'] != 1){	
				$respuesta['Codigo'] = 1;
				$respuesta['Mensaje'] = 'Solicitud de servicio prioritario se autorizó correctamente. Error al enviar notificación al cliente. ['.$RespEmail['Error'].']';
			}else{
				$respuesta['Codigo'] = 1;
				$respuesta['Mensaje'] = 'Solicitud de servicio prioritario se autorizó correctamente.';
			}
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);*/
}

function get_columna_actualizar($App) {
	if ($App == 'Ejecutivo') {
		return 'fecha_autorizo_ejecutivo';
	} else if($App == 'Bodega') {
		return 'fecha_autorizo_bodega';
	} else {
		return 'fecha_autorizo_cliente';
	}
}