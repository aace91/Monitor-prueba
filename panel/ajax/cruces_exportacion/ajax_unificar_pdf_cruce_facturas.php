<?php
include_once('./../../../checklogin.php');
if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['aCopias']) && !empty($_POST['aCopias'])) {
		$_SESSION['aCopias'] = json_decode($_POST['aCopias'],true);
		$respuesta['Codigo']=1;
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos error de CHARLY';
		$respuesta['Error']='';
	}
	exit(json_encode($respuesta));
}