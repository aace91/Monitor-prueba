<?php
include_once("../../checklogin.php");

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);

if($loggedIn == false){
	$error_msg="Se ha perdido la sesion favor de iniciarla de nuevo";
    exit(json_encode(array("error" => $error_msg)));
}
$respuesta = fcn_buscacliente((isset($_POST['q']) ? $_POST['q'] : ""));
echo json_encode($respuesta);

function fcn_buscacliente($buscar){
	include('../../connect_gabdata.php');

	$response['items']=array();
	if ($buscar!=''){

		$consulta="SELECT CVE_IMP, NOM_IMP
		           FROM casa.ctrac_client
				   WHERE NOM_IMP LIKE '%".$buscar."%'
				   ORDER BY NOM_IMP
				   LIMIT 10";
				   
		$query = mysqli_query($cmysqli_sab07, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_sab07);
			$response['codigo']=-1;
			$response['mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
			return $response;
		}
		while($row = mysqli_fetch_object($query)){
			$id=$row->CVE_IMP; 
			$nombre=$row->NOM_IMP; 
			array_push($response['items'],array('id'=>$id,'text'=>$nombre));
		} 
	}
	mysqli_close($cmysqli_sab07);
	return $response;
}


