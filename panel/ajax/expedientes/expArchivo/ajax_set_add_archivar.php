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
		$sNumeroCaja = $_POST['sNumeroCaja'];
		$aCuentas = json_decode($_POST['aCuentas']);
					
		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");
		
		//***********************************************************//
		
		mysqli_query($cmysqli_exp, "BEGIN");
		
		for ($i=0; $i < count($aCuentas); $i++) {
			$aData = explode("-", $aCuentas[$i]);
			$strTIPO_MOV = $aData[0];
			$strNO_BANCO = $aData[1];
			$strNO_MOV = $aData[2];
			
			$consulta = "SELECT referencia
						 FROM expedientes.seguimiento_pedime
						 WHERE id_empresa=".$sIdEmpresa." AND
							   tipo_mov='".$strTIPO_MOV."' AND
						       no_banco='".$strNO_BANCO."' AND
							   no_mov='".$strNO_MOV."' AND
							   fecha_cp_entrega IS NOT NULL AND
							   fecha_archivo_archivado IS NULL 
						 GROUP BY referencia";
			
			$query = mysqli_query($cmysqli_exp, $consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli_exp);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al consultar las cuentas de gastos. Por favor contacte al administrador del sistema.'; 
				$respuesta['Error'] = ' ['.$error.']';
			} else {
				$num_rows = mysqli_num_rows($query);
				
				if ($num_rows <= 0) {
					$consulta = "SELECT a.pedimento
		                               ,a.fecha_archivo_archivado
			                           ,CASE WHEN a.fecha_archivo_archivado IS NOT NULL THEN 'Archivado'
				 	                         WHEN a.fecha_cp_entrega IS NOT NULL THEN 'Pendiente por Archivar'
											 WHEN a.fecha_cp_digitalizado IS NOT NULL THEN 'Digitalizado (Pendiente por Entregar)'
						                     WHEN a.fecha_cc_entrega IS NOT NULL THEN 'Cuentas por Pagar (Digitalizando)'
						                     WHEN a.fecha_recepcion_entrega IS NOT NULL THEN 'Cuentas por Cobrar (Facturando)'
						                     WHEN a.fecha_recepcion_captura IS NOT NULL THEN 'Pedimento Pagado'
			                            END AS status_pedime
			                           ,(SELECT b.id_caja
				                         FROM expedientes.expedientes b
				                         WHERE b.tipo_mov = a.tipo_mov AND 
						                       b.no_banco = a.no_banco AND 
						                       b.no_mov = a.no_mov AND
						                       b.referencia = a.referencia) AS caja
                                 FROM expedientes.seguimiento_pedime a
								 WHERE id_empresa=".$sIdEmpresa." AND
									   tipo_mov='".$strTIPO_MOV."' AND
									   no_banco='".$strNO_BANCO."' AND
									   no_mov='".$strNO_MOV."'";
					
					$query = mysqli_query($cmysqli_exp, $consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli_exp);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al consultar las cuentas de gastos seccion de revision. Por favor contacte al administrador del sistema.'; 
						$respuesta['Error'] = ' ['.$error.']';
					} else {
						$num_rows = mysqli_num_rows($query);
						
						if ($num_rows > 0) { 
							while($row = mysqli_fetch_array($query)){
								$respuesta['Codigo']=100;
								$respuesta['Mensaje']='Cuenta [ '.$aCuentas[$i].' ] '.$row['status_pedime']; 
								$respuesta['Error'] = '';
								
								if(!is_null($row['caja'])) {
									$respuesta['Mensaje'].= ' (Caja '.$row['caja'].') fecha [ '.$row['fecha_archivo_archivado'].' ]';
								}
								break;
							}
						} else {
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='No se encontro registro de la cuenta [ '.$aCuentas[$i].' ]'; 
							$respuesta['Error'] = '';
						}
					}
				} else {
					while($row = mysqli_fetch_array($query)){
						$strTRAFICO = $row['referencia'];
						
						$consulta = "UPDATE expedientes.seguimiento_pedime 
									 SET fecha_archivo_archivado='".$fecha_registro."'
									 WHERE id_empresa=".$sIdEmpresa." AND 
										   tipo_mov='".$strTIPO_MOV."' AND
										   no_banco='".$strNO_BANCO."' AND
										   no_mov='".$strNO_MOV."' AND
										   referencia='".$strTRAFICO."' AND
										   fecha_archivo_archivado IS NULL";
						
						$query_upd = mysqli_query($cmysqli_exp, $consulta);
						if (!$query_upd) {
							$error=mysqli_error($cmysqli_exp);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al actualizar fecha archivado. Por favor contacte al administrador del sistema.'; 
							$respuesta['Error'] = ' ['.$error.']';
							mysqli_query($cmysqli_exp, "ROLLBACK");
							break;
						} else {
							$consulta = "INSERT INTO ".$sTableName."
										(id_empresa, id_caja, tipo_mov, no_banco, no_mov, referencia, id_estatus, id_responsable)
										 VALUES
										 (".$sIdEmpresa.", 
										'".$sNumeroCaja."',
										'".$strTIPO_MOV."',
										'".$strNO_BANCO."',
										'".$strNO_MOV."',
										'".$strTRAFICO."',
										'1',
										'1'
										)";	
					
							$query_insert = mysqli_query($cmysqli_exp, $consulta);
							if (!$query_insert) {
								$error=mysqli_error($cmysqli_exp);
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']='Error al insertar Expediente. Por favor contacte al administrador del sistema.'; 
								$respuesta['Error'] = ' ['.$error.']';
								
								mysqli_query($cmysqli_exp, "ROLLBACK");
								break;
							}			
						}
					}
				}
			}
		}
		
		if ($respuesta['Codigo']==1) {
			mysqli_query($cmysqli_exp, "COMMIT");
			$respuesta['Mensaje']='Se han archivado las cuentas correctamente.';
		}
	} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
	}	
	echo json_encode($respuesta);
}