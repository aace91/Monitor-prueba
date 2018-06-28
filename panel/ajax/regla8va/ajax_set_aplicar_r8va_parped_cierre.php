<?php
include_once('./../../../checklogin.php');
include('./../../../connect_casa.php');
include('./../../../connect_r8va.php');

$host = '192.168.1.107:E:\CASAWIN\CSAAIWIN\Datos\CASA.GDB'; 
$username='SYSDBA';
$password='masterkey';

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {		
		
		$referencia = trim($_POST['referencia']);
		$numero_partida = trim($_POST['numero_partida']);
		$id_fraccion = trim($_POST['id_fraccion']);
		$aPartidasFac = json_decode($_POST['aPartidasFac']);

		//***********************************************************//
		$id_usuario = $id;
		$fecha_registro =  date("Y-m-d H:i:s");		
		//***********************************************************//
		
		/*		*** VERIFICAR EL TOTAL DE CANTIDAD PARA REVISAR SALDO ***		*/
		$nTotCantidad = 0; $nTotValor = 0;
		for($i = 0; $i<count($aPartidasFac); $i++){
			$nTotCantidad += $aPartidasFac[$i]->can_fact;
			$nTotValor += $aPartidasFac[$i]->val_fact;
		}
		//Verificar que lo especificado de las partidas en las facturas,
		//cuadre con las cantidades de la partida del pedimento
		$qCasa = "SELECT a.CAN_FACT FROM SAAIO_FRACCI a WHERE a.NUM_REFE = '".$referencia."' AND a.NUM_PART = ".$numero_partida;
		$resCasa = odbc_exec ($odbccasa, $qCasa);
		if ($resCasa == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al consultar la informacion de la referencia. [BD.CASA].";
			$respuesta['Error'] = '['.odbc_error().']['.$qCasa.']';
			exit(json_encode($respuesta));
		}
		if(odbc_num_rows($resCasa) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "Error al obtener los datos de la partida ".$numero_partida.". [BD.CASA].";
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		while(odbc_fetch_row($resCasa)){
			$sCAN_FACT = odbc_result($resCasa,"CAN_FACT");
			if($nTotCantidad != $sCAN_FACT){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "La cantidad total de las partidas en las facturas es diferente a la cantidad de la partida ".$numero_partida.".";
				$respuesta['Error'] = 'Es necesario que ambas cantidades esten identicas para llevar el saldo correcto.';
				exit(json_encode($respuesta));
			}
		}
		//Verificar el saldo
		$consulta = "SELECT f.id_fraccion,f.numero_permiso,f.fraccion,f.descripcion,(f.cantidad - IFNULL(SUM(fh.cantidad),0)) AS saldo_cantidad, (f.valor - IFNULL(SUM(fh.valor),0)) AS saldo_valor
						FROM fracciones f
							LEFT JOIN fracciones_historico fh ON
								f.id_fraccion = fh.id_fraccion
						WHERE f.id_fraccion = ".$id_fraccion."
						GROUP BY f.id_fraccion";
		
		$query = mysqli_query($cmysqli_s8va, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli_s8va);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar el saldo de la fraccion.';
			$respuesta['Error'] = '['.$error.']['.$consulta.']';
			exit(json_encode($respuesta));
		}
		if(mysqli_num_rows($query) == 0){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "La fraccion que desea aplicar no existe en el catalogo del cliente.";
			$respuesta['Error'] = 'Por favor, contacte el administrador del sistema.'; 
			exit(json_encode($respuesta));
		}
		while($row = mysqli_fetch_array($query)){
			
			$fraccion = $row['fraccion'];
			$numero_permiso = $row['numero_permiso'];
			$saldo_cantidad = $row['saldo_cantidad'];
			$saldo_valor = $row['saldo_valor'];

			if(($saldo_cantidad - $sCAN_FACT) < 0){
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "El saldo en la cantidad es insuficiente.";
				$respuesta['Error'] = ''; 
				exit(json_encode($respuesta));
			}
		}
		/*****************************************************************************/
		//Inserta la fraccion en el historico de cambio
		/****************************************************************************/
		mysqli_autocommit($cmysqli_s8va,FALSE);
		for($i = 0; $i<count($aPartidasFac); $i++){
			$consulta = "INSERT INTO fracciones_historico (id_fraccion,num_refe,cons_fact,cons_par,cantidad,valor,num_par,fecha_registro,usuario_registro)
										VALUES (".$id_fraccion.",
												'".$referencia."',
												".$aPartidasFac[$i]->cons_fact.",
												".$aPartidasFac[$i]->cons_part.",
												".$aPartidasFac[$i]->can_fact.",
												".$aPartidasFac[$i]->val_fact.",
												".$numero_partida.",
												'".$fecha_registro."',
												'".$id_usuario."')";
												
			$query = mysqli_query($cmysqli_s8va, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_s8va);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al guardar la informacion de la Regla 8va en el sistema web. [MySQL]';
				$respuesta['Error'] = '['.$error.']['.$consulta.']';
				mysqli_rollback($cmysqli_s8va);
				exit(json_encode($respuesta));
			}
		}
		/*****************************************************************************/
		//Open Conexion BD de CASA
		/*****************************************************************************/
		$dbh = ibase_connect($host, $username, $password);
		if ($dbh == false) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al conectarte a la base de datos.[DB.CASA][".$host.']['.$username.']['.$password."]";
			$respuesta['Error'] = '['.ibase_errmsg().']';
			ibase_close($dbh);
			mysqli_rollback($cmysqli_s8va);
			exit(json_encode($respuesta));
		}
		/*****************************************************************************/
		//Actualizar la fraccion de la partida nivel pedimento en la BD de CASA
		/*****************************************************************************/
		$qCasaPar = "UPDATE SAAIO_FRACCI SET FRACCION = '98020007'
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
		$qCasaPer = "INSERT INTO SAAIO_PERMIS (NUM_REFE, NUM_PART, CVE_PERM, NUM_PERM, PER_IDEN)
						VALUES ('".$referencia."',".$numero_partida.",'C1','".$numero_permiso."','1')";

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
			$qCasaUd = "UPDATE SAAIO_FRACCI SET FRACCION = '".$fraccion."' WHERE NUM_REFE = '".$referencia."' AND NUM_PART = ".$numero_partida;
			$transUd=ibase_trans("IBASE_WRITE",$dbh);
			$sthUp = ibase_query($transUd, $qCasaUd);
			ibase_commit($transUd);
			if (!$sthUp) {
				$respuesta['Mensaje'] = "Error al insertar permiso.[DB.CASA].<br> 
										***IMPORTANTE***: La partida del pedimento se quedo con la fraccion 98020007 y NO se aplico el permiso, favor de aplicar manualmente o informar al equipo de sistemas. <br>
										[NUM_REFE:".$referencia."][NUM_PART:".$numero_partida."]";
			}
			/*****************************************************************************/
			ibase_close($dbh);
			mysqli_rollback($cmysqli_s8va);
			exit(json_encode($respuesta));
		}
		mysqli_commit($cmysqli_s8va);
		//for($i = 0; $i<count($aPartidasFac); $i++){
			//NOTA: Aqui se actualiza la informacion de las partidas a nivel fraccion, 
			//pero no es necesario ya que esa informacion no es reelevante en el calculo de saldos(WEB)
			//Solamente es necesario tener la informacion de las partidas-facturas y su cantidad-valor utilizado
			//en la fraccion.
		//}
		
		$respuesta['Codigo'] = 1;
		$respuesta['Mensaje'] = 'La regla 8va se aplicó correctamente a la partida del pedimento!!.';
		$respuesta['fraccion'] = '98020007';
		$respuesta['numero_permiso'] = $numero_permiso;
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
	ibase_close($dbh);
}

