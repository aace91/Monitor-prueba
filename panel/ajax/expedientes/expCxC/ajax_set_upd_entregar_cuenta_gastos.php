<?php

include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
	
	if (isset($_POST['sIdEmpresa']) && !empty($_POST['sIdEmpresa'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sIdEmpresa = json_decode($_POST['sIdEmpresa']);
		$aSelected = json_decode($_POST['aSelected']);
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		mysqli_query($cmysqli_exp, "BEGIN");
		for ($i=0; $i < count($aSelected); $i++) {
			// $sChekeo .= $aSelected[$i]->cuenta_gastos;
			// $sChekeo .= ' - ';
			// $sChekeo .= $aSelected[$i]->referencia;
			// $sChekeo .= ' || ';
			$sReferencia = $aSelected[$i]->referencia;
			$aCuentaGastos = explode("-", $aSelected[$i]->cuenta_gastos);
			$strTIPO_MOV = $aCuentaGastos[0];
			$strNO_BANCO = $aCuentaGastos[1];
			$strNO_MOV = $aCuentaGastos[2];
			
			$consulta = "UPDATE expedientes.seguimiento_pedime 
						 SET fecha_cc_entrega='".$fecha_registro."'
						 WHERE id_empresa=".$sIdEmpresa." AND
							   tipo_mov='".$strTIPO_MOV."' AND
							   no_banco='".$strNO_BANCO."' AND
							   no_mov='".$strNO_MOV."' AND
							   referencia='".$sReferencia."' AND
							   fecha_cc_facturacion IS NOT NULL";		
			
			$query = mysqli_query($cmysqli_exp, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_exp);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al actualizar las Cuentas de Gastos. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
				break;
			}
		}
		
		if($respuesta['Codigo'] == 1 ){
			$respuesta['Mensaje']='Pedimentos entregados correctamente!';
			$respuesta['Fecha']=$fecha_registro;
			mysqli_query($cmysqli_exp,"COMMIT");
		} else {
			mysqli_query($cmysqli_exp,"ROLLBACK");
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}

