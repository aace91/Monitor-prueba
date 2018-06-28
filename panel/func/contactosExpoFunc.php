<?php
include_once("../../checklogin.php");
include("../../connect_dbsql.php");

if($loggedIn == false){
	$error_msg="Se ha perdido la sesion favor de iniciarla de nuevo";
	exit(json_encode(array("error" => $error_msg)));
} else {
	if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
		$action = $_REQUEST['action'];
		switch ($action) {
			case 'insertar_contacto' : $respuesta = fcn_insertar_contacto();
				echo json_encode($respuesta);
				break;	

			case 'eliminar_contacto' : $respuesta = fcn_eliminar_contacto();
				echo json_encode($respuesta);
				break;
		}
	}
}

/*************************************************************************************************/
/* FUNCIONES                                                                                     */
/*************************************************************************************************/

function fcn_insertar_contacto(){
	global $cmysqli;
	global $_POST;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['tipo_catalogo']) && !empty($_POST['tipo_catalogo'])) {
		$tipo_catalogo = $_POST['tipo_catalogo'];
		$tipo_contacto = $_POST['tipo_contacto'];
		$id_catalogo = $_POST['id_catalogo'];
		$sEmail = $_POST['sEmail'];
		$sNombre = $_POST['sNombre'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		
		if ($respuesta['Codigo'] == 1) {
			mysqli_query($cmysqli,'START TRANSACTION');
			
			$consulta = "INSERT INTO bodega.contactos_expo (  
			                 id_catalogo
						    ,email
						    ,nombre
							,tipo_catalogo
							,tipo_contacto
						 )
						 VALUES (
							 '".$id_catalogo."'
							,'".$sEmail."'
							,'".$sNombre."'
							,'".$tipo_catalogo."'
							,".(($tipo_contacto == '-1')? 'NULL' : "'".$tipo_contacto."'")."
						 )";
						 
			$query = mysqli_query($cmysqli,$consulta);						
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al insertar contacto.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}
			
			if ($respuesta['Codigo'] == 1) { 
				mysqli_query($cmysqli,"COMMIT");
				$respuesta['Mensaje']='Contacto agregado correctamente!!!'; 
			} else {
				mysqli_query($cmysqli,"ROLLBACK");
			}
		}
		
		$respuesta['nSalidaNumero']=$nSalidaNumero;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}	
	mysqli_close($cmysqli);
	return $respuesta;
}

function fcn_eliminar_contacto(){
	global $cmysqli;
	global $_POST;
	
	$respuesta['Codigo']=1;
	if (isset($_POST['id_contacto']) && !empty($_POST['id_contacto'])) {
		$id_contacto = $_POST['id_contacto'];
		
		//***********************************************************//
		
		$fecha_registro =  date("Y-m-d H:i:s");
		
		if ($respuesta['Codigo'] == 1) {
			mysqli_query($cmysqli,'START TRANSACTION');
			
			$consulta = "DELETE FROM bodega.contactos_expo
						 WHERE id_contacto=".$id_contacto;
						 
			$query = mysqli_query($cmysqli,$consulta);						
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al eliminar contacto.'; 
				$respuesta['Error'] = ' ['.$error.']';
			}
			
			if ($respuesta['Codigo'] == 1) { 
				mysqli_query($cmysqli,"COMMIT");
				$respuesta['Mensaje']='Contacto eliminado correctamente!!!'; 
			} else {
				mysqli_query($cmysqli,"ROLLBACK");
			}
		}
		
		$respuesta['nSalidaNumero']=$nSalidaNumero;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}	
	mysqli_close($cmysqli);
	return $respuesta;
}