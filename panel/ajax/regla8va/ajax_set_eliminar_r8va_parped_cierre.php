<?php
include_once('./../../../checklogin.php');
include('./../../../connect_r8va.php');
include('./../../../connect_casa.php');
include('./../../../db.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {		
		
		$referencia = trim($_POST['referencia']);
		$numero_partida = trim($_POST['numero_partida']);
		
		//***********************************************************//
		$id_usuario = $id;
		$fecha_registro =  date("Y-m-d H:i:s");		
		//***********************************************************//
		$fraccion_original = ''; $aPartidasFac = array();
		//Revisar si el se aplico la regla 8va desde la partida del pedimento
		$consulta = "SELECT f.fraccion,f.numero_permiso,fh.id_fraccion_hist,fh.num_refe, fh.cons_fact, fh.cons_par
					FROM fracciones_historico fh
						INNER JOIN fracciones f ON
							fh.id_fraccion = f.id_fraccion
					WHERE fh.num_refe = '".$referencia."' AND fh.num_par = ".$numero_partida;
		
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar la informacion de las partidas en el sistema WEB.';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) > 0){
			//Se aplico Regla 8va en la partida del pedimento(Cierre).
			while($row = mysqli_fetch_array($query)){
				
				$id_fraccion_hist = $row['id_fraccion_hist'];
				$fraccion = $row['fraccion'];
				$numero_permiso = $row['numero_permiso'];
				$num_refe = $row['num_refe'];
				$cons_fact = $row['cons_fact'];
				$cons_par = $row['cons_par'];
				
				$Partida = array(
					"id_fraccion_hist" => $id_fraccion_hist,
					"fraccion" => $fraccion,
					"numero_permiso" => $numero_permiso,
					"num_refe" => $num_refe,
					"cons_fact" => $cons_fact,
					"cons_par" => $cons_par,
				);
				
				array_push($aPartidasFac,$Partida);
			}
		}else{
			//Revisar si se aplico Regla desde el pedimento consolidado(Abierto)
			//Revisar las partidas de la fraccion en casa para ver si existen en el sistema web
			//SAAIO_FRACCI(CASA) -> SAAIO_PARCONS(CASA) -> fracciones_historico(WEB)
			$qCasa = "SELECT a.CONS_FACT, a.CONS_PART
					FROM SAAIO_PARCONS a
					WHERE a.NUM_REFE = '".$referencia."' AND a.CONS_FRA = ".$numero_partida;
			$resCasa = odbc_exec ($odbccasa, $qCasa);
			if ($resCasa == false){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al consultar las facturas de la partida. [BD.CASA].";
				$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
				exit(json_encode($respuesta));
			}
			if(odbc_num_rows($resCasa) == 0){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "No se encontraron partidas de factura vinculadas a la partida del pedimento. [BD.CASA].";
				$respuesta['Error'] = '';
				exit(json_encode($respuesta));
			}
			$nitem = 0; $sWhere = '';
			while(odbc_fetch_row($resCasa)){
				$sCONS_FACT = odbc_result($resCasa,"CONS_FACT");
				$sCONS_PART = odbc_result($resCasa,"CONS_PART");
				
				$consulta = "SELECT f.fraccion,f.numero_permiso,fh.id_fraccion_hist,fh.num_refe, fh.cons_fact, fh.cons_par
							FROM fracciones_historico fh
								INNER JOIN fracciones f ON
									fh.id_fraccion = f.id_fraccion
							WHERE fh.num_refe = '".$referencia."' AND fh.cons_fact = ".$sCONS_FACT." AND fh.cons_par=".$sCONS_PART;
				//error_log($consulta);
				$query = mysqli_query($cmysqli_s8va, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli_s8va);
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Error al consultar la informacion de las partidas en el sistema WEB.[Partida-Factura]';
					$respuesta['Error'] = '['.$error.']['.$consulta.']';
					exit(json_encode($respuesta));
				}
				while($row = mysqli_fetch_array($query)){
					
					$id_fraccion_hist = $row['id_fraccion_hist'];
					$fraccion = $row['fraccion'];
					$numero_permiso = $row['numero_permiso'];
					$num_refe = $row['num_refe'];
					$cons_fact = $row['cons_fact'];
					$cons_par = $row['cons_par'];
					
					$Partida = array(
						"id_fraccion_hist" => $id_fraccion_hist,
						"fraccion" => $fraccion,
						"numero_permiso" => $numero_permiso,
						"num_refe" => $num_refe,
						"cons_fact" => $cons_fact,
						"cons_par" => $cons_par,
					);
					
					array_push($aPartidasFac,$Partida);
				}
			}
		}
		if(count($aPartidasFac) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'No se encontraron partidas-facturas en el sistema WEB. Por favor contacte el administrador del sistema.';
			$respuesta['Error'] = '[Opcion1: casa.saaio_fracci -> casa.saaio_parcons -> web.fracciones_historico] [Opcion2: casa.saaio_fracci -> web.fracciones_historico.num_par]';
			exit(json_encode($respuesta));
		}
		//Eliminar R8va WEB
		mysqli_autocommit($cmysqli_s8va,FALSE);
		for($i = 0; $i<count($aPartidasFac); $i++){
			$consulta = "DELETE FROM fracciones_historico WHERE id_fraccion_hist = ".$aPartidasFac[$i]['id_fraccion_hist'];
			
			$query = mysqli_query($cmysqli_s8va, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_s8va);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al eliminar partidas en el sistema web. [MySQL]';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				mysqli_rollback($cmysqli_s8va);
				exit(json_encode($respuesta));
			}
		}
		//Eliminar Regla Partida del Pedimento
		/*****************************************************************************/
		//Open Conexion BD de CASA
		/*****************************************************************************/
		$dbh = ibase_connect($host_ibase_casa, $username_ibase_casa, $password_ibase_casa);
		if ($dbh == false) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al conectarte a la base de datos.[DB.CASA][".$host_ibase_casa.']['.$username_ibase_casa.']['.$password_ibase_casa."]";
			$respuesta['Error'] = '['.ibase_errmsg().']';
			ibase_close($dbh);
			mysqli_rollback($cmysqli_s8va);
			exit(json_encode($respuesta));
		}
		/*****************************************************************************/
		//Actualizar la fraccion de la partida nivel pedimento en la BD de CASA
		/*****************************************************************************/
		$qCasaPar = "UPDATE SAAIO_FRACCI SET FRACCION = '".$aPartidasFac[0]['fraccion']."'
						WHERE NUM_REFE = '".$referencia."' AND NUM_PART = ".$numero_partida;
		
		$trans=ibase_trans("IBASE_WRITE",$dbh);
		$sth = ibase_query($trans, $qCasaPar);
		ibase_commit($trans);
		if (!$sth) { 
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al actualizar la informacion de la partida.[DB.CASA]";
			$respuesta['Error'] = ' ['.ibase_errmsg().']'.$qCasaPar;
			mysqli_rollback($cmysqli_s8va);
			exit(json_encode($respuesta));
		}
		/*****************************************************************************/
		//Insertar Permiso R8va NIVEL Partida Pedimento
		/*****************************************************************************/
		$qCasaPer = "DELETE FROM SAAIO_PERMIS WHERE NUM_REFE = '".$referencia."' AND NUM_PART = ".$numero_partida." AND NUM_PERM = '".$aPartidasFac[0]['numero_permiso']."'";

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
			$qCasaUd = "UPDATE SAAIO_FRACCI SET FRACCION = '98020007' WHERE NUM_REFE = '".$referencia."' AND NUM_PART = ".$numero_partida;
			$transUd=ibase_trans("IBASE_WRITE",$dbh);
			$sthUp = ibase_query($transUd, $qCasaUd);
			ibase_commit($transUd);
			if (!$sthUp) {
				$respuesta['Mensaje'] = "Error al eliminar permiso.[DB.CASA].<br> 
										***IMPORTANTE***: La partida del pedimento se actualizo con FRACCION ORIGINAL y NO de elimino con el PERMISO [R8va], favor de revisar [Fraccion-Permiso] en la partida directamente en el sistema CASA. <br>
										[NUM_REFE:".$referencia."][NUM_PART:".$numero_partida."]";
			}
			/*****************************************************************************/
			ibase_close($dbh);
			mysqli_rollback($cmysqli_s8va);
			exit(json_encode($respuesta));
		}
		mysqli_commit($cmysqli_s8va);
		/*for($i = 0; $i<count($aPartidasFac); $i++){
			//No se actualizaran las partidas del pedimento a nivel factura porque no es necesario afectar esos campos
			//Ya se eliminaron los saldos en el sistema WEB y se elimino la R8va de la partida a nivel pedimento
		}*/
		$respuesta['Codigo'] = 1;
		$respuesta['Mensaje'] = 'La regla 8va se elimino correctamente a la partida!!.';
		$respuesta['fraccion'] = $aPartidasFac[0]['fraccion'];
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
	ibase_close($dbh);
}

