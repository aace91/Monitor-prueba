<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
if ($loggedIn == false){
	echo '500';
}else{	
	
	if (isset($_POST['id_permiso']) && !empty($_POST['id_permiso'])) {
		$respuesta['Codigo'] = 1;	
		
		$id_permiso = $_POST['id_permiso'];
		
		$consulta = "SELECT p.numero_permiso,p.fecha_vigencia_ini,p.fecha_vigencia_fin,p.valor_dlls_total,c.nombre as cliente, 
								p.cantidad_total,p.valor_dlls_delbravo,p.cantidad_delbravo,p.id_cliente
							FROM permisos_pedimentos p
								LEFT JOIN bodega.geocel_clientes_expo c ON
									p.id_cliente = c.f_numcli
							WHERE p.id_permiso = ".$id_permiso;
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']= 'Error al consultar permiso en base de datos. [bodega.permisos] ['.$error.']['.$consulta.']';
		} else {
			if(mysqli_num_rows($query) > 0){
				while($row = mysqli_fetch_array($query)){
					$permiso = $row['numero_permiso'];
					$respuesta['cliente'] = $row['cliente'];
					$respuesta['permiso'] = $row['numero_permiso'];
					$respuesta['fecha_ini'] = date_format(new DateTime($row['fecha_vigencia_ini']),"d/m/Y");
					$respuesta['fecha_fin'] = date_format(new DateTime($row['fecha_vigencia_fin']),"d/m/Y");
					
					$valor_dlls_delbravo = $row['valor_dlls_delbravo'];
					$cantidad_delbravo = $row['cantidad_delbravo'];
					$respuesta['valor_dlls_total'] = '$ '.number_format($row['valor_dlls_total'],2);
					$respuesta['cantidad_total'] = number_format($row['cantidad_total'],0);
					$respuesta['valor_dlls_delbravo'] = '$ '.number_format($row['valor_dlls_delbravo'],2);
					$respuesta['cantidad_delbravo'] = number_format($row['cantidad_delbravo'],0);
				}
				$res = referencias_en_permiso_CASA($permiso,$valor_dlls_delbravo,$cantidad_delbravo);
				if($res['Codigo'] == 1){
					$respuesta['Codigo'] = 1;
					$respuesta['tabla'] = $res['aTabla'];
					$respuesta['valor_saldo_delbravo'] = $res['valor_saldo_delbravo'];
					$respuesta['cantidad_saldo_delbravo'] = $res['cantidad_saldo_delbravo'];
					$respuesta['color'] = $res['color'];
				}else{$respuesta = $res;}
			}else{
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar el permiso. [id_permiso o cliente NO EXISTE]';
			}
		}
	}else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}


	function referencias_en_permiso_CASA($nPermiso,$valor_total,$cantidad_total){
		//error_log('valor:'.$valor_total.' || cantidad:'.$cantidad_total);
		include('./../../../connect_casa.php');
		
		/*Relacion de rermisos en cada cierre de pedimento en comparativo con el total en las remesas*/
		$aReferencias = array();
		$aTabla = array();
		//Hacer un arreglo donde se muestre el comparativo total de los pedimentos y las remesas. 
		
		//TODO
		/*$qCasa = "SELECT a.NUM_REFE AS REF_A,p.NUM_PEDI,a.NUM_PERM AS PER_A, a.VAL_CDLL AS VAL_A, a.CAN_TARI AS CAN_A,
							CASE WHEN SUM(b.VAL_CDLL) IS NULL THEN 0 ELSE SUM(b.VAL_CDLL) END AS VAL_B, 
							CASE WHEN SUM(b.CAN_TARI) IS NULL THEN 0 ELSE SUM(b.CAN_TARI) END AS CAN_B													
					FROM SAAIO_PERMIS a
						INNER JOIN SAAIO_PEDIME p ON
							a.NUM_REFE = p.NUM_REFE
						LEFT JOIN SAAIO_PERPAR b ON
							a.NUM_REFE = b.NUM_REFE AND
							b.NUM_PERM = '".$nPermiso."'
					WHERE a.NUM_PERM = '".$nPermiso."'
					GROUP BY a.NUM_REFE,p.NUM_PEDI,a.NUM_PERM,a.VAL_CDLL,a.CAN_TARI
					ORDER BY a.NUM_REFE";*/
		$qCasa = "SELECT a.NUM_REFE AS REF_A,p.NUM_PEDI,a.NUM_PERM AS PER_A, SUM(a.VAL_CDLL) AS VAL_A, SUM(a.CAN_TARI) AS CAN_A
					FROM SAAIO_PERMIS a
						INNER JOIN SAAIO_PEDIME p ON
							a.NUM_REFE = p.NUM_REFE
                    WHERE a.NUM_PERM = '".$nPermiso."' AND p.FIR_PAGO IS NOT NULL AND a.NUM_REFE NOT IN (SELECT NUM_REFEO FROM SAAIO_PEDIME WHERE NUM_REFEO IS NOT NULL)
					GROUP BY a.NUM_REFE,p.NUM_PEDI,a.NUM_PERM
					ORDER BY a.NUM_REFE";
	
		$resped = odbc_exec ($odbccasa, $qCasa);
		if ($resped == false){
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al consultar valores del permiso BD.CASA.".odbc_error();
			return $respuesta;
		}else{
			while(odbc_fetch_row($resped)){
				$REF_CIE = odbc_result($resped,"REF_A");
				$PED_CIE = odbc_result($resped,"NUM_PEDI");
				
				$VAL_CIE = odbc_result($resped,"VAL_A");
				$CAN_CIE = odbc_result($resped,"CAN_A");
				
				/*$VAL_PAR = odbc_result($resped,"VAL_B");
				$CAN_PAR = odbc_result($resped,"CAN_B");*/
				
				//error_log("CIERRE: | ".$REF_CIE.' | '.$PER_CIE.' | '.$VAL_CIE.' | '.$CAN_CIE.' | '.$VAL_PAR.' | '.$CAN_PAR);
				
				$Referencia = array($REF_CIE,$PED_CIE,$VAL_CIE,$CAN_CIE);//,$VAL_PAR,$CAN_PAR);
				array_push($aReferencias,$Referencia);
				
			}
		}
		//PERMISOS PARTIDAS QUE NO ESTEN EN PERMISOS PEDIMENTO PAGADO
		/*$qCasa = "SELECT a.NUM_REFE AS REF_A,p.NUM_PEDI,a.NUM_PERM AS PER_A, SUM(a.VAL_CDLL) AS VAL_A, SUM(a.CAN_TARI) AS CAN_A,
							CASE WHEN b.VAL_CDLL IS NULL THEN 0 ELSE b.VAL_CDLL END AS VAL_B, 
							CASE WHEN b.CAN_TARI IS NULL THEN 0 ELSE b.CAN_TARI END AS CAN_B													
					FROM SAAIO_PERPAR a
						INNER JOIN SAAIO_PEDIME p ON
							a.NUM_REFE = p.NUM_REFE
						LEFT JOIN SAAIO_PERMIS b ON
							a.NUM_REFE = b.NUM_REFE AND
							b.NUM_PERM = '".$nPermiso."'
					WHERE a.NUM_PERM = '".$nPermiso."'
					GROUP BY a.NUM_REFE,p.NUM_PEDI,a.NUM_PERM,b.NUM_REFE,b.VAL_CDLL,b.CAN_TARI 
					ORDER BY a.NUM_REFE";*/
					
		$qCasa = "SELECT a.NUM_REFE AS REF_A,p.NUM_PEDI,a.NUM_PERM AS PER_A, SUM(a.VAL_CDLL) AS VAL_A, SUM(a.CAN_TARI) AS CAN_A
					FROM SAAIO_PERPAR a
						INNER JOIN SAAIO_PEDIME p ON
							a.NUM_REFE = p.NUM_REFE
					WHERE a.NUM_PERM = '".$nPermiso."' AND p.FIR_PAGO IS NULL  AND a.NUM_REFE NOT IN (SELECT NUM_REFEO FROM SAAIO_PEDIME WHERE NUM_REFEO IS NOT NULL)
					GROUP BY a.NUM_REFE,p.NUM_PEDI,a.NUM_PERM
					ORDER BY a.NUM_REFE";
	
		$resped = odbc_exec ($odbccasa, $qCasa);
		if ($resped == false){
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Reporte Diario Permisos Utilizados :: Error al consultar valores del permiso BD.CASA.".odbc_error();
			return $respuesta;
		}else{
			while(odbc_fetch_row($resped)){
				//Referencia y permiso B porque siempre trae dato
				$REF_PAR = odbc_result($resped,"REF_A");
				$PED_PAR = odbc_result($resped,"NUM_PEDI");
				
				$VAL_PAR = odbc_result($resped,"VAL_A");
				$CAN_PAR = odbc_result($resped,"CAN_A");
				
				/*$VAL_CIE = odbc_result($resped,"VAL_B");
				$CAN_CIE = odbc_result($resped,"CAN_B");*/
				$Referencia = array($REF_PAR,$PED_PAR,$VAL_PAR,$CAN_PAR);//($REF_PAR,$PED_PAR,$VAL_CIE,$CAN_CIE,$VAL_PAR,$CAN_PAR);
				if(COUNT($aReferencias) > 0){
					$bexist = false;
					for($i=0; $i<count($aReferencias); $i++){
						if($Referencia[0] == $aReferencias[$i][0]){
							$bexist = true;
							break;
						}
					}
					if(!$bexist){
						array_push($aReferencias,$Referencia);
					}
					/*if (!in_array($Referencia,$aReferencias)) {
						//error_log("No existe en el array aun agregandose");
						//error_log("REFERENCIA_CIERRRE: | ".$REF_PAR.' | '.$PER_PAR.' | '.$VAL_CIE.' | '.$CAN_CIE.' | '.$VAL_PAR.' | '.$CAN_PAR);
						array_push($aReferencias,$Referencia);
					}else{
						//error_log("Ya EXISTE no se agrego a array");
						//error_log("REFERENCIA_CIERRRE: | ".$REF_PAR.' | '.$PER_PAR.' | '.$VAL_CIE.' | '.$CAN_CIE.' | '.$VAL_PAR.' | '.$CAN_PAR);
					}*/
				}else{
					//error_log("NO HAY REFERENCIAS EN EL ARRAY aReferencias");
					//error_log("REFERENCIA_CIERRRE: | ".$REF_PAR.' | '.$PER_PAR.' | '.$VAL_CIE.' | '.$CAN_CIE.' | '.$VAL_PAR.' | '.$CAN_PAR);
					array_push($aReferencias,$Referencia);
				}											
			}
		}
		//Saldo Inicial
		$Row = array('Saldo_Inicial','','','$ '.number_format($valor_total,2),'',number_format($cantidad_total,0));
		array_push($aTabla,$Row);
		
		$saldo_valor = $valor_total;
		$saldo_kilos = $cantidad_total;
		
		for($i = 0; $i < count($aReferencias); $i++){
			
			$pedimento = $aReferencias[$i][1];
			$referencia = $aReferencias[$i][0];
			
			$valor_usado = ($aReferencias[$i][2] == 0 ? $aReferencias[$i][4] : $aReferencias[$i][2]);
			$kilos_usados =($aReferencias[$i][3] == 0 ? $aReferencias[$i][5] : $aReferencias[$i][3]);
			
			$saldo_valor = $saldo_valor - $valor_usado;
			$saldo_kilos = $saldo_kilos - $kilos_usados;
			
			$Row = array($pedimento,$referencia,'$ '.number_format($valor_usado,2),'$ '.number_format($saldo_valor,2),number_format($kilos_usados,0),number_format($saldo_kilos,0));
			array_push($aTabla,$Row);
		}
		
		//Total utilizado
		/*$qCasa = "SELECT b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
					FROM (  SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
								FROM SAAIO_PERPAR a
									INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
								WHERE a.NUM_PERM = '".$nPermiso."' AND c.FIR_PAGO IS NULL
							UNION ALL
							SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI													
							FROM SAAIO_PERMIS a
								INNER JOIN SAAIO_PEDIME c ON
										a.NUM_REFE = c.NUM_REFE
							WHERE a.NUM_PERM = '".$nPermiso."' AND c.FIR_PAGO IS NOT NULL) b
					GROUP BY (b.NUM_PERM)";*/
					
		$qCasa = "SELECT b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
									FROM (  SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
												FROM SAAIO_PERPAR a
													INNER JOIN SAAIO_PEDIME c ON
														a.NUM_REFE = c.NUM_REFE
												WHERE a.NUM_PERM = '".$nPermiso."' AND c.FIR_PAGO IS NULL AND a.NUM_REFE NOT IN (SELECT NUM_REFEO FROM SAAIO_PEDIME WHERE NUM_REFEO IS NOT NULL)
											UNION ALL
											SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI													
											FROM SAAIO_PERMIS a
                                                INNER JOIN SAAIO_PEDIME c ON
														a.NUM_REFE = c.NUM_REFE
											WHERE a.NUM_PERM = '".$nPermiso."' AND c.FIR_PAGO IS NOT NULL AND a.NUM_REFE NOT IN (SELECT NUM_REFEO FROM SAAIO_PEDIME WHERE NUM_REFEO IS NOT NULL)) b
									GROUP BY (b.NUM_PERM)";
	
		$resped = odbc_exec ($odbccasa, $qCasa) or die(odbc_error());
		if ($resped == false){
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']=  "Reporte Diario Permisos Utilizados :: Error al consultar valores del permiso BD.CASA.".odbc_error();
		}else{
			$nRows = 0;
			while(odbc_fetch_row($resped)){
				
				$valor_utilizado = odbc_result($resped,"VAL_DLLS");
				$kilos_utilizados = odbc_result($resped,"CAN_TARI");
				
				$Row = array('Suma','','$ '.number_format($valor_utilizado,2),'',number_format($kilos_utilizados,0),'');
				array_push($aTabla,$Row);
				
				$respuesta['Codigo']=1;
				$respuesta['aTabla'] = $aTabla;
				
				$respuesta['valor_saldo_delbravo'] = '$ '.number_format(($valor_total - $valor_utilizado),2);
				$respuesta['cantidad_saldo_delbravo'] = number_format(($cantidad_total - $kilos_utilizados),0);
				
				if($valor_utilizado < 0 || $kilos_utilizados < 0){
					$respuesta['color'] = 'panel-danger';
				}else{
					$respuesta['color'] = 'panel-info';
				}
				
				$nRows += 1;
			}
			if($nRows == 0){
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='No existe informacion para el permiso '.$nPermiso.'pedimentos. db.casa';
			}
		};
		return $respuesta;
	}

	