<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sIdFolio']) && !empty($_POST['sIdFolio'])) {  
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$sIdFolio = $_POST['sIdFolio'];
		$sComentario = $_POST['sComentario'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		$sComentario = scanear_string($sComentario);
		
		//***********************************************************//
		$consulta = "INSERT INTO bodega.expos_seguimiento_comments
					 (id_folio, ejecutivo, comentario, fecha)
					 VALUES (
					 ".$sIdFolio.",
					 ".$id.",
					 '".$sComentario."',
					 '".$fecha_registro."'
					 )";

		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al insertar comentario. Por favor contacte al administrador del sistema.'.$consulta; 
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
        array("\\", "¨", "º", "-", "~",
             "#", "@", "|", "!", "\"",
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", "<", ";", ",", ":",
             "."),
        '',
        $string
    );
 
 
    return $string;
}