<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['catalogo']) && !empty($_POST['catalogo'])) {  
				
		$Catalogo = $_POST['catalogo'];
		$Accion = $_POST['accion'];
		
		switch($Catalogo){
			case 'lineat':
				$respuesta = nueva_linea_transportista($_POST['nombre']);
				break;
			case 'transfer':
				if($Accion == 'agregar'){
					$respuesta = nuevo_tranfer($_POST['nombre'],$_POST['caat'],$_POST['scac']);
				}else{
					$respuesta = editar_tranfer($_POST['id'],$_POST['nombre'],$_POST['caat'],$_POST['scac']);
				}
				break;
			default:
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al recibir datos de entrada. [Catalogo]';
		}
		
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}

function nueva_linea_transportista($Nombre){
	global $cmysqli;
	$respuesta['Codigo'] = 1;
	$consulta = "INSERT INTO bodega.lineast
					SELECT (numlinea + 1),'".$Nombre."'
					FROM bodega.lineast
					ORDER BY numlinea desc
					LIMIT 1";
	
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al insertar linea fletera. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
		return $respuesta;
	}
	return $respuesta;
}

function nuevo_tranfer($Nombre,$CAAT,$SCAC){
	global $cmysqli;
	$respuesta['Codigo'] = 1;
	$consulta = "INSERT INTO bodega.transfers_expo
					SELECT (notransfer + 1),'".$Nombre."','".$CAAT."','".$SCAC."'
					FROM bodega.transfers_expo
					ORDER BY notransfer desc
					LIMIT 1";
	
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al insertar transfer. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
		return $respuesta;
	}
	return $respuesta;
}

function editar_tranfer($Id,$Nombre,$CAAT,$SCAC){
	global $cmysqli;
	$respuesta['Codigo'] = 1;
	$consulta = "UPDATE bodega.transfers_expo SET 
								nombretransfer = '".$Nombre."',
								caat = '".$CAAT."',
								scac = '".$SCAC."'
					WHERE notransfer = ".$Id;
	
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al insertar transfer. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
		return $respuesta;
	}
	return $respuesta;
}



function consultar_catalogo_lineas_transportistas(){
	global $cmysqli;
	$respuesta['Codigo']=1;
	//Lineas Fleteras
	$consulta = "SELECT numlinea, nombre
					 FROM bodega.lineast
					 ORDER BY nombre";
	
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar el catalogo de lineas fleteras. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
		return $respuesta;
	}
	$respuesta['aLineas'] = '<option value="0">[SELECCIONAR]</option>';
	while ($row = mysqli_fetch_array($query)){
		$respuesta['aLineas'] .= '<option value="'.$row['numlinea'].'">'.$row['nombre'].'</option>';
	}
	//Transfers
	$consulta = "SELECT notransfer, nombretransfer
				 FROM bodega.transfers_expo
				 ORDER BY nombretransfer";
	
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error = mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar el catalogo de transfers. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
		return $respuesta;
	}
	$respuesta['aTransfers'] = '<option value="0">[SELECCIONAR]</option>';
	while ($row = mysqli_fetch_array($query)){
		$respuesta['aTransfers'] .= '<option value="'.$row['notransfer'].'">'.$row['nombretransfer'].'</option>';
	}
	//Entregar En
	$consulta = "SELECT numeroentrega, nombreentrega
				 FROM bodega.entregas_expo
				 ORDER BY nombreentrega";
	
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar el catalogo de entregas. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
		return $respuesta;
	}
	$respuesta['aEntregar'] = '<option value="0">[SELECCIONAR]</option>';
	while ($row = mysqli_fetch_array($query)){
		$respuesta['aEntregar'] .= '<option value="'.$row['numeroentrega'].'">'.$row['nombreentrega'].'</option>';
	}
	//Agente Aduanal Americano
	$consulta = "SELECT numeroaa, nombreaa
				FROM bodega.aaa
				ORDER BY nombreaa";
	
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar el catalogo de agentes aduanales americanos. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
		return $respuesta;
	}
	$respuesta['aAAA'] = '<option value="0">[SELECCIONAR]</option>';
	while ($row = mysqli_fetch_array($query)){
		$respuesta['aAAA'] .= '<option value="'.$row['numeroaa'].'">'.$row['nombreaa'].'</option>';
	}
	
	return $respuesta;
}