<?php
include_once('checklogin.php');
if($loggedIn == false){
	$mensaje= "<a href='login.php'>Su sesión expiro favor de ingresar nuevamente</a>";
	$response['codigo'] = -1;
    $response['mensaje'] = $mensaje;
    echo json_encode($response);
}
if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
		case 'buscatipo' : $respuesta = buscatipo((isset($_POST['q']) ? $_POST['q'] : ""));
			echo json_encode($respuesta);
            break;
		case 'guardartipo' : $respuesta = guardartipo(strtoupper($_POST['nombre_tipo']));
			echo json_encode($respuesta);
            break;
	}
}else{
	echo 'No se ha definido el nombre de la accion ';
}

function buscatipo($buscar){
	include('connect_dbsql.php');
	if(!mysqli_get_connection_stats($cmysqli)){
		$response['codigo']=-1;
		$response['mensaje']='Conexion muerta' ;
		return $response;
	}
	$response['items']=array();
	if ($buscar!=''){
		$consulta="SELECT id_equipo,tipo_equipo from tipoequipo where tipo_equipo like '%$buscar%' limit 10";
		$query = $cmysqli->query($consulta);
		if (!$query) {
			$error=$cmysqli->error;
			$response['codigo']=-1;
			$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
			return $response;
		}
		while($row = $query->fetch_object()){
			$id=$row->tipo_equipo;
			$nombre=$row->tipo_equipo;
			array_push($response['items'],array('id'=>$id,'text'=>$nombre));
		}
	}
	mysqli_close($cmysqli);
	return $response;
}

function guardartipo($nombre_tipo){
	include ('db.php');
	$mdbFilename =$rutaequipomdb;
	$conn_access = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=$mdbFilename", '', '');
	if ($conn_access==false){
		$response['codigo']=-1;
		$response['mensaje']="Error al conectarse a la base de datos equipo.mdb".$ruta;
		return($response);
	}
	if(trim($nombre_tipo) ==''){
		$response['codigo']=-1;
		$response['mensaje']='El nombre del tipo no puede estar vació ';
		return($response);
	}
	$consultaa="INSERT INTO tipoequipo (tipo_equipo) values('$nombre_tipo')";
	$result = odbc_exec ($conn_access, $consultaa);
	if (odbc_num_rows($result)==-1){
		$response['codigo']=-1;
		$response['mensaje']="Error en consulta, error:".odbc_errormsg ($conn_access).", ".$consultaa;
		return($response);
	}
	odbc_close($conn_access);
	$consultaa="SELECT max(id_equipo) as clavem from tipoequipo";
	$result = odbc_exec ($conn_access, $consultaa);
	if ($result==false){
		$response['codigo']=-1;
		$response['mensaje']="Error en consulta, error:".odbc_errormsg ($conn_access).", ".$consultaa;
		return($response);
	}
	while ($fila = odbc_fetch_object($result)){
        $id=$fila->clavem;
    }
	$consultam="INSERT INTO tipoequipo (id_equipo,tipo_equipo) values($id,'$nombre_tipo')";
	$query = mysqli_query($cmysqli,$consultam);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$response['codigo']=-1;
		$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
		return $response;
	}
	$response['codigo']=1;
	$response['mensaje']='El tipo se guardo con exito';
	return $response;
	mysqli_close($cmysqli);
}
