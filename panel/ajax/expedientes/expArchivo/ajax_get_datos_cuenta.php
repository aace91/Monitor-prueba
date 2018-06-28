<?php
include_once('./../../../../checklogin.php');

$sTableName = 'expedientes.expedientes';

if ($loggedIn == false){
	echo '500';
}else{	
	require('./../../../../connect_exp.php');
		
	if (isset($_POST['sIdEmpresa']) && !empty($_POST['sIdEmpresa'])) {
		$respuesta['Codigo']=1;		
		
		//***********************************************************//
		
		$sIdEmpresa = $_POST['sIdEmpresa'];
		$strCuenta = $_POST['strCuenta'];
		
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		$strCuenta = explode("-", $strCuenta);
		
		$sTIPO_MOV = $strCuenta[0];
		$sNO_BANCO = $strCuenta[1];
		$sNO_MOV = $strCuenta[2];
		
		$consulta = "SELECT a.id_empresa, CONCAT(a.tipo_mov,'-',a.no_banco,'-',a.no_mov) AS cuenta_gastos,
						    b.trafico AS referencia, IF(a.impo_expo = 1, 'IMPORTACION', 'EXPORTACION') AS impo_expo,
						    b.pedimento, b.no_mov, c.nombre AS cliente_nombre
					 FROM expedientes.seguimiento_pedime a INNER JOIN
						  ".(($sIdEmpresa == '1'? 'contagab': 'contasab')).".aacgmex AS b ON a.tipo_mov = b.tipo_mov AND
																                             a.no_banco = b.no_banco AND
																                             a.no_mov = b.no_mov INNER JOIN 
						  ".(($sIdEmpresa == '1'? 'contagab': 'contasab')).".aacte AS c ON c.no_cte = b.no_cte
					 WHERE a.id_empresa=".$sIdEmpresa."  AND
						   a.tipo_mov='".$sTIPO_MOV."' AND
						   a.no_banco=".$sNO_BANCO." AND
						   a.no_mov=".$sNO_MOV;
		
		//error_log($consulta);
		$query = mysqli_query($cmysqli_exp, $consulta);	
		if (!$query) {
			$error=mysqli_error($cmysqli_exp);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar los datos de la cuenta solicitada. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$num_rows = mysqli_num_rows($query);
			
			if ($num_rows > 0) {
				while($row = mysqli_fetch_array($query)){
					$respuesta['cuenta_gastos'] = $row['cuenta_gastos'];
					$respuesta['referencia'] = $row['referencia'];
					$respuesta['impo_expo'] = $row['impo_expo'];
					$respuesta['pedimento'] = (($row['pedimento'] == NULL)? '' : $row['pedimento']);
					$respuesta['no_mov'] = $row['no_mov'];
					$respuesta['cliente_nombre'] = $row['cliente_nombre'];
					
					break;
				}
				$respuesta['Mensaje']='Datos consultados correctamente!!!';
			} else {
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "No existe la cuenta solicitada, favor de ingresar una diferente.";
				$respuesta['Error'] = '';
			}
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}