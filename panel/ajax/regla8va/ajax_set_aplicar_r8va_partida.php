<?php
include_once('./../../../checklogin.php');
require('./../../../connect_casa.php');
require('./../../../connect_r8va.php');
if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {		
		
		$referencia = trim($_POST['referencia']);
		$cons_fact = trim($_POST['cons_fact']);
		$cons_par = trim($_POST['cons_par']);
		$permiso = trim($_POST['permiso']);
		$id_fraccion = trim($_POST['id_fraccion']);

		//***********************************************************//
		$id_usuario = $id;
		$fecha_registro =  date("Y-m-d H:i:s");		
		//***********************************************************//
		$qCasa = "SELECT a.NUM_REFE, a.CONS_FACT, a.CONS_PART,a.FRACCION, a.DES_MERC, a.CAN_FACT, a.MON_FACT
					FROM SAAIO_FACPAR a
					WHERE a.NUM_REFE = '".$referencia."' AND a.CONS_FACT = ".$cons_fact." AND a.CONS_PART = ".$cons_par;
		
		$resCasa = odbc_exec ($odbccasa, $qCasa);
		if ($resCasa == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar la informacion de la referencia. [BD.CASA].";
			$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
			exit(json_encode($respuesta));
		}
		if(odbc_num_rows($resCasa) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "No se encontraron partidas capturadas para la referencia. [BD.CASA].";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		while(odbc_fetch_row($resCasa)){
			$sFRACCION = odbc_result($resCasa,"FRACCION");
			$sDES_MERC = odbc_result($resCasa,"DES_MERC");
			$sCAN_FACT = odbc_result($resCasa,"CAN_FACT");
			$sMON_FACT = odbc_result($resCasa,"MON_FACT");

			if($id_fraccion == ''){
				$consulta = "SELECT f.id_fraccion,f.fraccion,f.descripcion,(f.cantidad - IFNULL(SUM(fh.cantidad),0)) AS saldo_cantidad, (f.valor - IFNULL(SUM(fh.valor),0)) AS saldo_valor
							FROM fracciones f
								LEFT JOIN fracciones_historico fh ON
									f.id_fraccion = fh.id_fraccion
							WHERE f.fraccion = '".trim($sFRACCION)."' AND f.descripcion = '".trim($sDES_MERC)."' AND numero_permiso = '".$permiso."'
							GROUP BY f.id_fraccion";
			}else{
				$consulta = "SELECT f.id_fraccion,f.fraccion,f.descripcion,(f.cantidad - IFNULL(SUM(fh.cantidad),0)) AS saldo_cantidad, (f.valor - IFNULL(SUM(fh.valor),0)) AS saldo_valor
								FROM fracciones f
									LEFT JOIN fracciones_historico fh ON
										f.id_fraccion = fh.id_fraccion
								WHERE f.id_fraccion = ".$id_fraccion."
								GROUP BY f.id_fraccion";
			}
			$query = mysqli_query($cmysqli_s8va, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_s8va);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al consultar fracciones disponibles para la regla 8va.';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				exit(json_encode($respuesta));
			}
			if(mysqli_num_rows($query) == 0){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "No se encontro una fraccion una fraccion disponible para la partida.";
				$respuesta['Error'] = 'Favor de agregar la fraccion ['.$sFRACCION .'] con la descripcion ['.$sDES_MERC.'] en las fracciones disponibles.'; 
				exit(json_encode($respuesta));
			}
			//mysqli_autocommit($cmysqli_s8va,FALSE);
			error_log($consulta);
			mysqli_query($cmysqli_s8va,"BEGIN");
			while($row = mysqli_fetch_array($query)){
				$id_fraccion = $row['id_fraccion'];
				$fraccion = $row['fraccion'];
				$saldo_cantidad = $row['saldo_cantidad'];
				$saldo_valor = $row['saldo_valor'];

				if(($saldo_cantidad - $sCAN_FACT) < 0){
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "El saldo en la cantidad es insuficiente.";
					$respuesta['Error'] = '['.$sFRACCION .'] ['.$sDES_MERC.'].'; 
					exit(json_encode($respuesta));
				}

				/*if(($saldo_valor - $sMON_FACT) < 0){
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "El saldo en el valor es insuficiente.";
					$respuesta['Error'] = '['.$sFRACCION .'] ['.$sDES_MERC.'].'; 
					exit(json_encode($respuesta));
				}*/
				/*****************************************************************************/
				//Inserta la fraccion en el historico de cambio
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
				$frquery = mysqli_query($cmysqli_s8va, $consulta);
				if (!$frquery) {
					$error=mysqli_error($cmysqli_s8va);
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Error al guardar el cambio en la base de datos. [MySQL]';
					$respuesta['Error'] = '['.$error.']['.$consulta.']';
					//mysqli_rollback($cmysqli_s8va);
					mysqli_query($cmysqli_s8va,"ROLLBACK");
					exit(json_encode($respuesta));
				}
				/*****************************************************************************/
				//Open Conexion BD de CASA
				/*****************************************************************************/
				$dbh = ibase_connect($host_ibase_casa, $username_ibase_casa, $password_ibase_casa);
				if ($dbh == false) {
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error al conectarte a la base de datos.[DB.CASA][".$host_ibase_casa.']['.$username_ibase_casa.']['.$password_ibase_casa."]";
					$respuesta['Error'] = '['.ibase_errmsg().']';
					ibase_close($dbh);
					//mysqli_rollback($cmysqli_s8va);
					mysqli_query($cmysqli_s8va,"ROLLBACK");
					exit(json_encode($respuesta));
				}
				/*****************************************************************************/
				//Actualizar la fraccion en la BD de CASA
				/*****************************************************************************/
				$qCasaPar = "UPDATE SAAIO_FACPAR SET FRACCION = '98020007'
							WHERE NUM_REFE = '".$referencia."' AND CONS_FACT = ".$cons_fact." AND CONS_PART = ".$cons_par;
				
				$trans=ibase_trans("IBASE_WRITE",$dbh);
				$sth = ibase_query($trans, $qCasaPar);
				ibase_commit($trans);
				if (!$sth) { 
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "Error al actualizar la informacion de la partida.[DB.CASA]";
					$respuesta['Error'] = ' ['.ibase_errmsg().']'.$qCasaPar;
					//mysqli_rollback($cmysqli_s8va);
					mysqli_query($cmysqli_s8va,"ROLLBACK");
					exit(json_encode($respuesta));
				}
				/*****************************************************************************/
				//Insertar Permiso R8va
				/*****************************************************************************/
				$qCasaPer = "INSERT INTO SAAIO_PERPAR (NUM_REFE, CONS_FACT, CONS_PART, CVE_PERM, NUM_PERM, PER_IDEN)
								VALUES ('".$referencia."',".$cons_fact.",".$cons_par.",'C1','".$permiso."','1')";

				$transpar = ibase_trans("IBASE_WRITE",$dbh);
				$sthpar = ibase_query($transpar, $qCasaPer);
				ibase_commit($transpar);
				if (!$sthpar) {
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "Error al insertar la informacion del permiso.[DB.CASA]";
					$respuesta['Error'] = ' ['.ibase_errmsg().']'.$qCasaPer;
					/*****************************************************************************/
					//Regresar la fraccion a la partida
					/*****************************************************************************/
					$qCasaUd = "UPDATE SAAIO_FACPAR SET FRACCION = '".$fraccion."' WHERE NUM_REFE = '".$referencia."' AND CONS_FACT = ".$cons_fact." AND CONS_PART = ".$cons_par;
					$transUd=ibase_trans("IBASE_WRITE",$dbh);
					$sthUp = ibase_query($transUd, $qCasaUd);
					ibase_commit($transUd);
					if (!$sthUp) {
						$respuesta['Mensaje'] = "Error al insertar permiso.[DB.CASA].<br> 
												***IMPORTANTE***: La partida se quedo con la fraccion 98020007 y NO se aplico el permiso, favor de aplicar manualmente o informar al equipo de sistemas. <br>
												[NUM_REFE:".$referencia."][CONS_FACT:".$cons_fact."][CONS_PAR:".$cons_par."]";
					}
					/*****************************************************************************/
					ibase_close($dbh);
					//mysqli_rollback($cmysqli_s8va);
					mysqli_query($cmysqli_s8va,"ROLLBACK");
					exit(json_encode($respuesta));
				}
				/*****************************************************************************/
				ibase_close($dbh);
			}
			//mysqli_commit($cmysqli_s8va);
			mysqli_query($cmysqli_s8va,"COMMIT");
		}
		$respuesta['Codigo'] = 1;
		$respuesta['Mensaje'] = 'La regla 8va se aplicó correctamente a la partida!!.';
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
	ibase_close($dbh);
}

