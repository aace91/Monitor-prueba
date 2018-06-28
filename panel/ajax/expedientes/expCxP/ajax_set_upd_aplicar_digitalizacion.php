<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	require('./../../../../connect_exp.php');
	
	$respuesta['Codigo']=1;		
	
	//***********************************************************//

	$fecha_registro =  date("Y-m-d H:i:s");
	
	//***********************************************************//
		
	$htEmpresas = array();
	
	/* ..:: Datos de las empresas ::.. */
	$consulta = "SELECT id_empresa, rutaanexos
				 FROM expedientes.empresas
				 WHERE rutaanexos IS NOT NULL";	

	$query = mysqli_query($cmysqli_exp, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli_exp);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar las Empresas. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
	} else { 
		while($row = mysqli_fetch_array($query)){
			$htEmpresas[$row['id_empresa']] = $row['rutaanexos'];	
		}
	}
		
	/* ..:: Datos de las cuentas y actualizacion ::.. */
	$consulta = "SELECT id_empresa, tipo_mov, no_banco, no_mov, referencia
				 FROM expedientes.seguimiento_pedime
				 WHERE fecha_cc_recepcion IS NOT NULL AND
					   fecha_cc_entrega IS NOT NULL AND
					   fecha_cp_digitalizado IS NULL
				 GROUP BY tipo_mov, no_banco, no_mov, referencia";	

	$query = mysqli_query($cmysqli_exp, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli_exp);
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error al consultar los Traficos. Por favor contacte al administrador del sistema.'; 
		$respuesta['Error'] = ' ['.$error.']';
	} else { 
	    $respuesta['total'] = mysqli_num_rows($query);
		while($row = mysqli_fetch_array($query)){
			$sIdEmpresa = $row['id_empresa'];
			$sTIPO_MOV = $row['tipo_mov'];
			$sNO_BANCO = $row['no_banco'];
			$sNO_MOV = $row['no_mov'];
			$sReferencia = trim($row['referencia']);
			$sRutaAnexos = $htEmpresas[$sIdEmpresa];
			
			$ruta=$sRutaAnexos.'\\'.$sReferencia.".pdf";
			if (file_exists($ruta)){ 
				$sFechaArchivo = date("Y-m-d H:i:s", filemtime($ruta));
				
				$consulta = "UPDATE expedientes.seguimiento_pedime 
							 SET fecha_cp_digitalizado='".$sFechaArchivo."'
							 WHERE id_empresa=".$sIdEmpresa." AND
								   tipo_mov='".$sTIPO_MOV."' AND
								   no_banco='".$sNO_BANCO."' AND
								   no_mov='".$sNO_MOV."' AND
								   referencia='".$sReferencia."' AND
								   fecha_cc_recepcion IS NOT NULL AND
								   fecha_cc_entrega IS NOT NULL AND
								   fecha_cp_digitalizado IS NULL";		
				
				$query_upd = mysqli_query($cmysqli_exp, $consulta);
				if (!$query_upd) {
					$error=mysqli_error($cmysqli_exp);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al actualizar las Cuentas de Gastos. Por favor contacte al administrador del sistema.'; 
					$respuesta['Error'] = ' ['.$error.']';
					break;
				}
			} 
		}
	}
		
	echo json_encode($respuesta);
}







