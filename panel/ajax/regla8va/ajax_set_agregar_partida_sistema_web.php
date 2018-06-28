<?php
include_once('./../../../checklogin.php');
include('./../../../connect_casa.php');
include('./../../../connect_r8va.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {		
		
		$referencia = trim($_POST['referencia']);
		$cons_fact = trim($_POST['cons_fact']);
		$cons_par = trim($_POST['cons_par']);
		$id_fraccion = trim($_POST['id_fraccion']);
		//***********************************************************//
		$id_usuario = $id;
		$fecha_registro =  date("Y-m-d H:i:s");		
		//***********************************************************//

		//********************************************************************************************************//
		//Consultar informacion de Cantidad y Valor de la referencia en el sistema de pedimentos
		//********************************************************************************************************//
		$qCasa = "SELECT a.NUM_REFE, a.CONS_FACT, a.CONS_PART,a.FRACCION, a.DES_MERC, a.CAN_FACT, a.MON_FACT
				FROM SAAIO_FACPAR a
				WHERE a.NUM_REFE = '".$referencia."' AND a.CONS_FACT = ".$cons_fact." AND a.CONS_PART = ".$cons_par;

		$resCasa = odbc_exec ($odbccasa, $qCasa);
		if ($resCasa == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar la informacion de la referencia.[Cantidad,Valor] [BD.CASA].";
			$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
			exit(json_encode($respuesta));
		}
		if(odbc_num_rows($resCasa) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "No se encontraro informacion de la partida. [BD.CASA].";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		while(odbc_fetch_row($resCasa)){
			$sFRACCION = odbc_result($resCasa,"FRACCION");
			$sDES_MERC = odbc_result($resCasa,"DES_MERC");
			$sCAN_FACT = odbc_result($resCasa,"CAN_FACT");
			$sMON_FACT = odbc_result($resCasa,"MON_FACT");
		}
		//********************************************************************************************************//
		//Revisar si la fraccion original se encuentra disponible
		//********************************************************************************************************//
		$consulta = "SELECT f.id_fraccion,f.fraccion,f.descripcion,(f.cantidad - IFNULL(SUM(fh.cantidad),0)) AS saldo_cantidad, (f.valor - IFNULL(SUM(fh.valor),0)) AS saldo_valor
						FROM fracciones f
							LEFT JOIN fracciones_historico fh ON
								f.id_fraccion = fh.id_fraccion
						WHERE f.id_fraccion = '".trim($id_fraccion)."'
						GROUP BY f.id_fraccion";
	
		//$consulta = "SELECT * FROM  fracciones WHERE fraccion = '".$fraccion."' AND eliminado = '0'  AND fecha_vencimiento >= CURDATE()";
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar informacion de la fraccion.';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar la informacion de la fraccion seleccionada.';
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		while($row = mysqli_fetch_array($query)){
			//$id_fraccion = $row['id_fraccion'];
			$saldo_cantidad = $row['saldo_cantidad'];
			$saldo_valor = $row['saldo_valor'];
		}
		//********************************************************************************************************//
		//Revisar si el saldo de 
		//********************************************************************************************************//
		if(($saldo_cantidad - $sCAN_FACT) < 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "El saldo en la cantidad de la fraccion es insuficiente.";
			$respuesta['Error'] = '['.$fraccion .'] ['.$sDES_MERC.'].'; 
			exit(json_encode($respuesta));
		}
		/*if(($saldo_valor - $sMON_FACT) < 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "El saldo en el valor de la fraccion es insuficiente.";
			$respuesta['Error'] = '['.$fraccion .'] ['.$sDES_MERC.'].'; 
			exit(json_encode($respuesta));
		}*/
		/*****************************************************************************/
		//Insertar informacion de la partida en el sistema WEB
		/*****************************************************************************/
		$consulta = "INSERT INTO fracciones_historico (id_fraccion,num_refe,cons_fact,cons_par,cantidad,valor,fecha_registro,usuario_registro)
									VALUES (".$id_fraccion.",
											'".$referencia."',
											".$cons_fact.",
											".$cons_par.",
											".$sCAN_FACT.",
											".$sMON_FACT.",
											'".$fecha_registro."',
											'".$id_usuario."')";
											
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al guardar la informacion de la partida en la base de datos. [MySQL]';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		$respuesta['Codigo'] = 1;
		$respuesta['Mensaje'] = 'La informaci&oacute;n de la partida se ha guardado correctamente!!.';
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

