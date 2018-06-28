<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../url_archivos.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['nombre']) && !empty($_POST['nombre'])) {
		
		$id_cruce = $_POST['id_cruce'];
		$nombre = $_POST['nombre'];
		$aFacturas = array();
		$files = $_FILES;
		
		if(isset($files["pdfPedimento"])) {
			if($files["pdfPedimento"]["error"] == 0) {	
			
				$pdfFact = $files["pdfPedimento"]["tmp_name"];				
				if(!isset($pdfFact)) {
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["pdfPedimento"]["name"].'] excede el máximo permitido.';
					$respuesta['Error'] = '';
					exit(json_encode($respuesta));
				}else{
					if(!move_uploaded_file($pdfFact, $dir_archivos_pedimentos.$nombre.'.pdf')){
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["pdfPedimento"]["name"].'] en el servidor.';
						$respuesta['Error'] = '';
						exit(json_encode($respuesta));
					}
				}
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error en el archivo ['.$files["pdfPedimento"]["name"].']';
				$respuesta['Error'] = '';
				exit(json_encode($respuesta));
			}
		}else{
			$respuesta['Codigo'] = '-1';
			$respuesta['Mensaje'] = 'Error al recibir el archivo del pedimento.';
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		if($id_cruce != ''){			
			include('consultar_facturas.php');	
		}
		$respuesta['Codigo']=1;		
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}