<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('enviar_notificacion_email.php');

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);

if($loggedIn == false){
	return '500';
} else{
	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {		
		$respuesta['Codigo'] = 1;

		$referencia = $_POST['referencia'];
		$sTarea = $_POST['sTarea'];
		$idSolicitud = $_POST['idSolicitud'];
		$motivo = $_POST['motivo'];
		
		/***************************************************************************/
		
		$fecha_registro =  date("Y-m-d H:i:s");
		
		/***************************************************************************/
		
		if ($sTarea == 'Nuevo') {
			$consulta = "INSERT INTO tiempo_extra 
						 (referencia
						 ,motivo
						 ,usuario_tipo
						 ,solicito_ejecutivo
						 ,usuario_id
						 ,fecha_registro
						 ,fecha_autorizo_ejecutivo)
						 VALUES
						 ('".$referencia."'
						 ,'".$motivo."'
						 ,'1'
						 ,1
						 ,".$id."
						 ,'".$fecha_registro."'
						 ,'".$fecha_registro."')";									
			
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al insertar solicitud de servicio prioritario. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}else{
				$id_Solicitud = mysqli_insert_id($cmysqli);
				
				$RespEmail = enviar_correo_nueva_solicitud($id_Solicitud, $pdo_mysql_sconn, $pdo_accss_sconn, $mysqluser, $mysqlpass, $bDebug, 'Ejecutivo');
				if($RespEmail['Codigo'] != 1){	
					$respuesta['Codigo'] = 1;
					$respuesta['Mensaje'] = 'La solicitud de Servicio Prioritario fue agregada correctamente. Error al enviar notificación por correo electrónico. ['.$RespEmail['Error'].']';
				}else{
					$respuesta['Codigo'] = 1;
					$respuesta['Mensaje'] = 'La solicitud de Servicio Prioritario fue agregada correctamente.';
				}
			}
		} else {
			$consulta = "UPDATE tiempo_extra
						 SET referencia='".$referencia."'
						 WHERE id_solicitud=". $idSolicitud;
						 
			$query = mysqli_query($cmysqli, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al actualizar la referencia. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}
		}
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = '404 :: No se recibieron datos de entrada.';
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}