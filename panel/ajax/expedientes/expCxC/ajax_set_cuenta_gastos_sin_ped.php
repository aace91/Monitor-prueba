<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
	
	if (isset($_POST['sIdEmpresa']) && !empty($_POST['sIdEmpresa'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sIdEmpresa = $_POST['sIdEmpresa'];
		$sRutaDatos = $_POST['sRutaDatos'];
		$sTipoMov = $_POST['sTipoMov'];
		$sNoBanco = $_POST['sNoBanco'];
		$sNoMov = $_POST['sNoMov'];
		$sComentarios = $_POST['sComentarios'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		$nRowsInsert = 0;
		
		//***********************************************************//
		
		$consulta = "SELECT referencia_saaio, clave_pedimento, impo_expo
					 FROM expedientes.seguimiento_pedime
					 WHERE tipo_mov='".$sTipoMov."' AND
						   no_banco=".$sNoBanco." AND
						   no_mov=".$sNoMov;
						   
		$query = mysqli_query($cmysqli_exp, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al verificar cuenta de gastos. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$nRows = mysqli_num_rows($query);
			if ($nRows > 0) { 
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='La cuenta de gastos ' . $sNoMov . ' ya ha sido facturada.'; 
				$respuesta['Error'] = '';
			}
		}
		
		if ($respuesta['Codigo'] == 1) {
			$consulta = "SELECT trafico
						 FROM ".fcn_get_tabla($sRutaDatos, 'aacgmex')."
						 WHERE tipo_mov='".$sTipoMov."' AND
							   no_banco=".$sNoBanco." AND
							   no_mov=".$sNoMov;
						
			$query_aacgmex = mysqli_query($cmysqli_exp, $consulta);
			if (!$query_aacgmex) {
				$error=mysqli_error($cmysqli_exp);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al verificar cuenta de gastos. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else {
				$nRows = mysqli_num_rows($query_aacgmex);
				if ($nRows == 0) { 
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='La cuenta de gastos ' . $sNoMov . ' no existe en el sistema de contabilidad.'; 
					$respuesta['Error'] = '';
				} else {
					while($row_aacgmex = mysqli_fetch_array($query_aacgmex)){ 
						$strTRAFICO = trim($row_aacgmex['trafico']);
						
						$consulta = "INSERT INTO expedientes.seguimiento_pedime 
									(id_empresa, tipo_mov, no_banco, no_mov, referencia, comentarios,
 									 fecha_cc_recepcion, fecha_cc_facturacion, fecha_cc_entrega)
									VALUES
									(".$sIdEmpresa.", 
									'".$sTipoMov."',
									'".$sNoBanco."',
									'".$sNoMov."',
									'".$strTRAFICO."',
									'".$sComentarios."',
									'".$fecha_registro."',
									'".$fecha_registro."',
									'".$fecha_registro."'
									)";	

						$query2 = mysqli_query($cmysqli_exp, $consulta);
						if (!$query2) {
							$error=mysqli_error($cmysqli_exp);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al insertar Factura. Por favor contacte al administrador del sistema.'.$sComentarios; 
							$respuesta['Error'] = ' ['.$error.']'.$consulta;
							break;
						} else {
							$nRowsInsert += mysqli_affected_rows($cmysqli_exp);
						}
					
						break;
					}
				}
			}
		}
		
		if ($respuesta['Codigo']==1) {
			$respuesta['Mensaje']='Se agrego la cuenta ' . $sNoMov . ' correctamente';
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	
	$respuesta['nRowsInsert']=$nRowsInsert;
	echo json_encode($respuesta);
}

function fcn_get_tabla($sRutaDatos, $tabla) {
	
	if (strpos($sRutaDatos, 'sab07') !== false) {
		return 'contagab.'.$tabla;
	} else if (strpos($sRutaDatos, 'sab10') !== false) {
		return 'contasab.'.$tabla;
	} else {
		return '';
	}
	
}