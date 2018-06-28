<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
	
	if (isset($_POST['nIdRegistro']) && !empty($_POST['nIdRegistro'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$nIdRegistro = $_POST['nIdRegistro'];
		$sComentarios = $_POST['sComentarios'];
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
						
		$consulta = "UPDATE expedientes.seguimiento_pedime 
					 SET comentarios='".$sComentarios."'
					 WHERE id_registro=".$nIdRegistro;	

		$query = mysqli_query($cmysqli_exp, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al grabar comentario. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} 
		
		if ($respuesta['Codigo']==1) {
			$respuesta['Mensaje']='Comentario Guardado.';
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	
	echo json_encode($respuesta);
}