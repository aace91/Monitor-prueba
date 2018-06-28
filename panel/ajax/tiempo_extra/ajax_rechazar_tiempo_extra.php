<?php
$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);

$loggedIn = true;
$bLinkCorreo = true;
$id_solicitud = '';
$observaciones = '';
$observaciones_user = '';
$App = 'Ejecutivo';

if (isset($_GET['isd']) && !empty($_GET['isd'])) {
	$id_solicitud = $_GET['isd'];
	$observaciones = $_GET['ob'];
	$Tipo = $_GET['tp'];
	if ($Tipo == 'E') {
		$App = 'Ejecutivo';
	} else if($Tipo == 'B') {
		$App = 'Bodega';
	} else {
		$App = 'Cliente';
	}

	if ($Tipo == 'C') {		
		$observaciones_user = $_GET['usr'];		
	} else {
		$observaciones_user = $App;
	}
} else {
	include_once('./../../../checklogin.php');
	$bLinkCorreo = false;
}

require('./../../../connect_dbsql.php');
include('enviar_notificacion_email.php');


if($loggedIn == false){
	echo '500';
} else {	
	$respuesta['Codigo'] = 1;

	$fecha_registro =  date("Y-m-d H:i:s");

	if ($bLinkCorreo == false) {
		if (isset($_POST['id_solicitud']) && !empty($_POST['id_solicitud'])) {
			$id_solicitud = $_POST['id_solicitud'];
			$observaciones = $_POST['observaciones'];
			$observaciones_user = $username;
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
		}
	}

	if ($respuesta['Codigo'] == 1) {
		$consulta = "UPDATE tiempo_extra 
					 SET fecha_rechazo='".$fecha_registro."',
					    ".get_columna_actualizar_visto($App)."='".$fecha_registro."'
					 WHERE id_solicitud = ".$id_solicitud;

		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al guardar autorizacion. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
		} else { 
			$consulta = "INSERT INTO tiempo_extra_comentarios
						 (id_solicitud, de, comentario, fecha)
						 VALUES (
						 ".$id_solicitud.",
						 '".strtoupper($observaciones_user)."',
						 'SOLICITUD RECHAZADA: ".scanear_string($observaciones)."',
						 '".$fecha_registro."')";

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al insertar comentario. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else { 
				$respuesta = enviar_correo_notificacion($id_solicitud, $pdo_mysql_sconn, $pdo_accss_sconn, $mysqluser, $mysqlpass, $bDebug, $App);
				if($respuesta['Codigo'] != 1){	
					$respuesta['Codigo'] = 1;
					$respuesta['Mensaje'] = 'Solicitud de servicio prioritario se rechazó correctamente. Error al enviar notificación al cliente. ['.$respuesta['Error'].']';
				} else {
					$respuesta['Mensaje'] = 'Solicitud de servicio prioritario se rechazó correctamente.';
				}
			}
		}
	}

	if ($bLinkCorreo == false) { 
		echo json_encode($respuesta);
	} else {
		if ($respuesta['Codigo'] == 1) { 
			exit(get_html_ok_description($respuesta['Mensaje'] . ' Referencia: '. $respuesta['Referencia']));
		} else {
			exit(get_html_error_description($respuesta['Mensaje']));
		}
	}


	/*if (isset($_POST['id_solicitud']) && !empty($_POST['id_solicitud'])) {  
		$respuesta['Codigo'] = 1;	
		
		$id_solicitud = $_POST['id_solicitud'];
		$observaciones = $_POST['observaciones'];

		$fecha_registro =  date("Y-m-d H:i:s");
		
		$consulta = "UPDATE tiempo_extra 
					 SET fecha_rechazo='".$fecha_registro."',
						 bodega_ultima_vista='".$fecha_registro."'
 					 WHERE id_solicitud = ".$id_solicitud;
		
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al guardar autorizacion. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
		} else { 
			$consulta = "INSERT INTO tiempo_extra_comentarios
						 (id_solicitud, de, comentario, fecha)
						 VALUES (
						 ".$id_solicitud.",
						 '".strtoupper($nomcli)."',
						 'SOLICITUD RECHAZADA: ".scanear_string($observaciones)."',
						 '".$fecha_registro."')";

			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al insertar comentario. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else { 
				$RespEmail = enviar_correo_notificacion($id_solicitud, $pdo_mysql_sconn, $pdo_accss_sconn, $mysqluser, $mysqlpass, $bDebug, 'Ejecutivo');
				if($RespEmail['Codigo'] != 1){	
					$respuesta['Codigo'] = 1;
					$respuesta['Mensaje'] = 'Solicitud de servicio prioritario rechazado por Ejecutivo. Error al enviar notificación al cliente. ['.$RespEmail['Error'].']';
				}else{
					$respuesta['Mensaje'] = 'Solicitud de servicio prioritario rechazado por Ejecutivo.';
				}
			}
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);*/
}

function get_columna_actualizar_visto($App) {
	if ($App == 'Ejecutivo') {
		return 'ejecutivo_ultima_vista';
	} else if($App == 'Bodega') {
		return 'bodega_ultima_vista';
	} else {
		return 'cliente_ultima_vista';
	}
}

/**
 * Reemplaza todos los caracteres especiales o extraño
 *
 * @param $string
 *  string la cadena a sanear
 *
 * @return $string
 *  string saneada
 */
function scanear_string($string) {
 
    $string = trim($string);
 
    //Esta parte se encarga de eliminar cualquier caracter extraño
    $string = str_replace(
        array("\\", "¨", "º", "~",
             "@", "|", "\"",
             "·", "$", "%", "&", "/",
             "'", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", "<"),
        '',
        $string
    );
 
 
    return $string;
}