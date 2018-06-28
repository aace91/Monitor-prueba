<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sIdSalida']) && !empty($_POST['sIdSalida'])) {  
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$sIdSalida = $_POST['sIdSalida'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		$aPreview = $aPreviewConfig = [];
		$sPathFiles = "D:\\archivos_web\\monitor\\exposSalidas";
		$sUrlFiles = "http://delbravoweb.com/archivos/monitor/exposSalidas";
		
		//***********************************************************//
		
		$consulta = "SELECT nombre_archivo
					 FROM bodega.expos_salidas_files
					 WHERE id_salida=".$sIdSalida;
		
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los documentos. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			while ($row = mysqli_fetch_array($query)){
				$sFilePath = $sPathFiles. '\\' .$sIdSalida. '\\' .$row["nombre_archivo"];
				
				$sPreview = $sUrlFiles. '/' .$sIdSalida. '/' .$row["nombre_archivo"];
				//$sPreview = '<img src="'.$sPreview.'" class="kv-preview-data krajee-init-preview file-preview-image">';
				$sPreviewConfig = array(
									'type' => get_file_type($sFilePath),
									'caption' => $row["nombre_archivo"],
									'size' => filesize($sFilePath),
									'width' => '120px',
									'url' => 'ajax/exposSalidas/ajax_delete_files.php',
									'key' => $sIdSalida . '^' . $row["nombre_archivo"],
									'showDrag' => false
								);
				
				array_push($aPreview, $sPreview);
				array_push($aPreviewConfig, $sPreviewConfig);
			}
		}		
		
		$respuesta['aPreview']=$aPreview;
		$respuesta['aPreviewConfig']=$aPreviewConfig;
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}

function get_file_type($sPath) {
	$ext = explode('.', basename($sPath));
	
	if (preg_match("/\.(gif|png|jpe?g)$/i", $sPath)) {
		return 'image';
	} else if(preg_match("/\.(pdf)$/i", $sPath)) {
		return 'pdf';
	} else if(preg_match("/\.(htm|html)$/i", $sPath)) {
		return 'html';
	} else if(preg_match("/\.(xml|javascript)$/i", $sPath)) {
		return 'text';
	} else {
		return 'other';
	}
	// switch(array_pop($ext)) {
		// case 'pdf':
			// return 'pdf';
			// break;
			
		// case 'jpg':
		// case 'png':
		// case 'gif':
			// return 'image';
			// break;
	// }
}
