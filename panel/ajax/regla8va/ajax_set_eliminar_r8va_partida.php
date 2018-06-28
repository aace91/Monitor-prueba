<?php
include_once('./../../../checklogin.php');
include('./../../../connect_r8va.php');
include('./../../../connect_casa.php');
include('./../../../db.php');

/*$host_ibase_casa = '192.168.1.107:E:\CASAWIN\CSAAIWIN\Datos\CASA.GDB'; 
$username_ibase_casa='SYSDBA';
$password_ibase_casa='masterkey';*/

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['id_fraccion_hist']) && !empty($_POST['id_fraccion_hist'])) {		
		
		$id_fraccion_hist = trim($_POST['id_fraccion_hist']);

		//***********************************************************//
		$id_usuario = $id;
		$fecha_registro =  date("Y-m-d H:i:s");		
		//***********************************************************//
		
		$consulta = "SELECT f.fraccion,fh.num_refe, fh.cons_fact, fh.cons_par, f.numero_permiso
					FROM fracciones_historico fh
						INNER JOIN fracciones f ON
							fh.id_fraccion = f.id_fraccion
					WHERE fh.id_fraccion_hist = ".$id_fraccion_hist;
		
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar informacion de la partida.';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "No se encontro informacion de la fraccion a reemplazar.";
			$respuesta['Error'] = ''; 
			exit(json_encode($respuesta));
		}
		$fraccion = ''; $num_refe = '';$cons_fact = ''; $cons_par = '';
		while($row = mysqli_fetch_array($query)){
			$fraccion = $row['fraccion'];
			$num_refe = $row['num_refe'];
			$cons_fact = $row['cons_fact'];
			$cons_par = $row['cons_par'];
			$numero_permiso = $row['numero_permiso'];
		}
		/*****************************************************************************/
		//ELIMINAR fraccion del sistema WEB
		/*****************************************************************************/
		$consulta = "DELETE FROM  fracciones_historico WHERE id_fraccion_hist = ".$id_fraccion_hist;
		mysqli_autocommit($cmysqli_s8va,FALSE);
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al eliminar los datos de la fraccion en el sistema WEB. [MySQL]';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			mysqli_rollback($cmysqli_s8va);
			exit(json_encode($respuesta));
		}
		/*****************************************************************************/
		//Agregar Movimiento del Usuario - Historico de Movimientos
		/*****************************************************************************/
		$consulta = "INSERT INTO historico_usuarios (id_usuario,fecha_movimiento,accion,fraccion_anterior,
													fraccion_nueva, num_refe, cons_fact, cons_par, permiso_eliminado,
													id_fraccion_hist_eliminada) 
										VALUES (	".$id_usuario.",
													'".$fecha_registro."',
													'Revertir Regla 8va de la Partida',
													'98020007',
													'".$fraccion."',
													'".$num_refe."',
													".$cons_fact.",
													".$cons_par.",
													'".$numero_permiso."',
													".$id_fraccion_hist.")";

		mysqli_autocommit($cmysqli_s8va,FALSE);
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al guardar el historico de movimientos del usuario. [MySQL]';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			mysqli_rollback($cmysqli_s8va);
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
			exit(json_encode($respuesta));
		}
		/*****************************************************************************/
		//Actualizar la fraccion en la BD de CASA
		/*****************************************************************************/
		$qCasaPar = "UPDATE SAAIO_FACPAR SET FRACCION = '".$fraccion."'
					WHERE NUM_REFE = '".$num_refe."' AND CONS_FACT = ".$cons_fact." AND CONS_PART = ".$cons_par;
		error_log($qCasaPar);
		$trans=ibase_trans("IBASE_WRITE",$dbh);
		$sth = ibase_query($trans, $qCasaPar);
		ibase_commit($trans);
		if (!$sth) { 
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al actualizar la informacion de la partida.[DB.CASA]";
			$respuesta['Error'] = ' ['.ibase_errmsg().']'.$qCasaPar;
			mysqli_rollback($cmysqli_s8va);
			exit(json_encode($respuesta));
		}		/*****************************************************************************/
		//ELIMINAR Permiso R8va
		/*****************************************************************************/
		$qCasaPer = "DELETE FROM  SAAIO_PERPAR 
						WHERE NUM_REFE = '".$num_refe."' AND 
								CONS_FACT = ".$cons_fact." AND 
								CONS_PART = ".$cons_par." AND
								CVE_PERM = 'C1' AND
								NUM_PERM = '".$numero_permiso."' AND 
								PER_IDEN = '1'";
		error_log($qCasaPer);
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
			$qCasaUd = "UPDATE SAAIO_FACPAR SET FRACCION = '98020007' WHERE NUM_REFE = '".$num_refe."' AND CONS_FACT = ".$cons_fact." AND CONS_PART = ".$cons_par;
			$transUd=ibase_trans("IBASE_WRITE",$dbh);
			$sthUp = ibase_query($transUd, $qCasaUd);
			ibase_commit($transUd);
			error_log('Error al eliminar el pedimento:'.$qCasaUd);
			if (!$sthUp) {
				$respuesta['Mensaje'] = "Error al eliminar el permiso.[DB.CASA].<br> 
										***IMPORTANTE***: La partida se quedo con la fraccion ".$fraccion." y NO se ELIMINO el permiso, favor de aplicar manualmente o informar al equipo de sistemas. <br>
										[NUM_REFE:".$num_refe."][CONS_FACT:".$cons_fact."][CONS_PAR:".$cons_par."]";
			}
			/*****************************************************************************/
			mysqli_rollback($cmysqli_s8va);
			ibase_close($dbh);
			exit(json_encode($respuesta));
		}
		mysqli_commit($cmysqli_s8va);
		
		$respuesta['Codigo'] = 1;
		$respuesta['Mensaje'] = 'La regla 8va se elimino correctamente a la partida!!.';
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
	ibase_close($dbh);
}

