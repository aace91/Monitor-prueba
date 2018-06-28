<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['Id_Solicitud']) && !empty($_POST['Id_Solicitud'])) {  
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$Id_Solicitud = $_POST['Id_Solicitud'];
		$sComentario = $_POST['sComentario'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		$sComentario = scanear_string($sComentario);
		
		//***********************************************************//
		$consulta = "INSERT INTO tiempo_extra_comentarios
					 (id_solicitud, de, comentario, fecha)
					 VALUES (
					 ".$Id_Solicitud.",
					 '".$username."',
					 '".scanear_string($sComentario)."',
					 '".$fecha_registro."'
					 )";

		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al insertar comentario. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			$respuesta['Mensaje']='Comentario se agreg&oacute; correctamente!';
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}

/**
 * Reemplaza todos los caracteres especiales o extraño
 *
 * @param $string
 *  string la cadena a sanear
 *
 * @return $string
 *  string saneada
 */
function scanear_string($string) {
 
    $string = trim($string);
 
    //Esta parte se encarga de eliminar cualquier caracter extraño
    $string = str_replace(
        array("\\", "¨", "º", "~",
             "@", "|", "\"",
             "·", "$", "%", "&", "/",
             "'", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", "<"),
        '',
        $string
    );
 
 
    return $string;
}