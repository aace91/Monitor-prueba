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
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		$aPreview = $aPreviewConfig = [];
		$sPathFiles = "D:\\archivos_web\\monitor\\exposSeguimiento";
		$sUrlFiles = "https://www.delbravoweb.com/archivos/monitor/exposSeguimiento";
		
		//***********************************************************//
		
		$consulta = "SELECT nombre_archivo
					 FROM bodega.expos_seguimiento_files
					 WHERE id_folio=".$sIdFolio;
		
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los documentos. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			while ($row = mysqli_fetch_array($query)){
				$sFilePath = $sPathFiles. '\\' .$sIdFolio. '\\' .$row["nombre_archivo"];
				
				$sPreview = $sUrlFiles. '/' .$sIdFolio. '/' .$row["nombre_archivo"];
				//$sPreview = '<img src="'.$sPreview.'" class="kv-preview-data krajee-init-preview file-preview-image">';
				$sPreviewConfig = array(
									'type' => get_file_type($sFilePath),
									'caption' => $row["nombre_archivo"],
									'size' => filesize($sFilePath),
									'width' => '120px',
									'url' => 'ajax/exposSalidasSeguimiento/ajax_delete_files.php',
									'key' => $sIdFolio . '^' . $row["nombre_archivo"],
									'showDrag' => false,
									'url_download' => $sUrlFiles . '/' . $sIdFolio . '/' . $row["nombre_archivo"],
									'nombre_archivo' => $row["nombre_archivo"]
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
