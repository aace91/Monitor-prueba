<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['key']) && !empty($_POST['key'])) { 
		$respuesta['Codigo']=1;		
		$respuesta['Error']='';
		
		//***********************************************************//
		
		// get the files posted
		$sKey = $_POST['key'];
		
		//***********************************************************//
		
		$sPathFiles = "D:\\archivos_web\\monitor\\exposSalidas";
		
		//***********************************************************//
		
		$aDelete = explode("^", $sKey);
		$sIdSalida = $aDelete[0];
		$sNombreArchivo = $aDelete[1];
		
		$consulta="DELETE FROM bodega.expos_salidas_files
                   WHERE id_salida=".$sIdSalida." AND
				         nombre_archivo='".$sNombreArchivo."'";
							   
		$query = mysqli_query($cmysqli,$consulta);		
		if ($query==false){
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje'] = 'Error al eliminar archivo'.$consulta;
			$respuesta['Error']=' ['.$error.']';
		} else {
			$sFilePath = $sPathFiles. '\\' .$sIdSalida. '\\' .$sNombreArchivo;
			unlink($sFilePath);
		}
				
		$respuesta['Key']=$sKey;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}

