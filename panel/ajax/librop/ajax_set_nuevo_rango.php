<?php

include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['anio']) && !empty($_POST['anio'])) {		
		
		$anio = $_POST['anio'];
		$id_aduana = $_POST['id_aduana'];
		$patente = $_POST['patente'];
		$pedimento_ini = $_POST['pedimento_ini'];
		$pedimento_fin = $_POST['pedimento_fin'];
		$observaciones = $_POST['observaciones'];
		
		//***********************************************************//
		$fecha_registro =  date("Y-m-d H:i:s");		
		//***********************************************************//
		
		$consulta = "INSERT INTO librop_rangos (consecutivo,patente,id_aduana,anio,pedimento_inicial,pedimento_final,fecha_registro,usuario_registro)
						VALUES(	".$pedimento_ini.",
								'".$patente."',
								".$id_aduana.",
								'".$anio."',
								".$pedimento_ini.",
								".$pedimento_fin.",
								'".$fecha_registro."',
								".$id .")";
		
		$query = mysqli_query($cmysqli,$consulta);
		
		if (!$query){
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo'] = '-1';
			$respuesta['Mensaje']='Error al insertar el rango de los pedimentos.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$respuesta['Codigo'] = '1';
			/*if(odbc_num_rows($result) <= 0){
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='No es posible localizar la referencia en la base de datos de pedimentos.'; 
				$respuesta['Error'] = ' ['.$error.']';	
				$bContinue  = false;
			}*/
		}
		
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos del rango.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

