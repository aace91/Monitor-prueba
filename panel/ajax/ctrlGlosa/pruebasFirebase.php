<?php
	$post = json_decode(file_get_contents("php://input"), true);
	error_log('PBA' . json_encode($post));
	//error_log('PBA' . json_encode($_POST["sToken"]));
	
	$respuesta['Codigo']=1;
	$respuesta['Mensaje']='Tengo Datos '.$post['sToken'];
	
	exit(json_encode($respuesta));
