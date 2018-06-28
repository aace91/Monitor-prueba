<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	$respuesta['Codigo'] = 1;	
	
	//***********************************************************//
	
	//$sIdPaleta = $_POST['sIdPaleta'];  

	//***********************************************************//

	$fecha_registro =  date("Y-m-d H:i:s");
		
	//***********************************************************//
	
	$mdbFilenameExpos ='\\\\192.168.1.107\gabdata\bodega\Expos.mdb';
	$conn_access = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=$mdbFilenameExpos", '', '');			
	if ($conn_access==false){
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']="Failed connect to database Expos.mdb ".odbc_errormsg ($conn_access);
		$respuesta['Error'] = '';
	} else {
		$consulta="SELECT salidanumero,
						  fecha,
						  hora,
						  caja,
						  nocliente
				   FROM salidas
				   WHERE nocliente='PISA' AND
						 salidanumero >= 118069 AND
						 sync=false;";
		$query = odbc_exec($conn_access, $consulta);
		if ($query==false){
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error en consultar la lista de salidas error:".odbc_errormsg ($conn_access);
			$respuesta['Error']='';
		} else {
			while ($row = odbc_fetch_array($query)){ 
				$salidanumero = $row['salidanumero'];
				$fecha = substr($row['fecha'], 0, 10) . ' ' . $row['hora'];
				$caja = $row['caja'];
				$nocliente = $row['nocliente'];
				
				$fecha = date("Y-m-d H:i:s", strtotime($fecha));
				/*****************************/
			
				$consulta2="SELECT FACTURA_NUMERO, 
								   VALOR_FACTURA, 
								   FECHA_FACTURA, 
								   REFERENCIA, 
								   PEDIMENTO, 
								   PROVEEDOR,
								   ID_PROVEEDOR,
								   NO_PARTES
							FROM facturas
							WHERE SALIDA_NUMERO = ".$salidanumero.";";
							
				$query2 = odbc_exec($conn_access, $consulta2);						
				if ($query2==false){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error en consultar la lista de facturas [".$salidanumero."] error:".odbc_errormsg ($conn_access).", ".$consulta2;
					$respuesta['Error']='';
					
					break;
				} else {
					/* ..:: Insertamos salida ::.. */
					mysqli_query("BEGIN");
					
					$consulta_mysql="INSERT INTO bodega.expos_salidas
								(id_salida, id_cliente, caja, fecha)
								VALUES(".$salidanumero.",
									   '".$nocliente."',
									   '".$caja."',
									   '".$fecha."')";
								
					$query_mysql = mysqli_query($cmysqli,$consulta_mysql);		
					if ($query_mysql==false){
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje'] = 'Error en insertar salida en expos_salidas';
						$respuesta['Error']=' ['.$error.']';
						
						mysqli_query("ROLLBACK");
						break;
					}
					
					/* ..:: Insertamos facturas ::.. */
					while ($row2 = odbc_fetch_array($query2)){  
						$consulta_mysql="INSERT INTO bodega.expos_salidas_facturas
									(FACTURA_NUMERO,
									 VALOR_FACTURA,
									 FECHA_FACTURA, 
									 REFERENCIA, 
									 PEDIMENTO,
									 SALIDA_NUMERO,
									 PROVEEDOR,
									 ID_PROVEEDOR,
									 NO_PARTES)
									VALUES('".$row2['FACTURA_NUMERO']."',
										   ".$row2['VALOR_FACTURA'].",
										   ".get_column_value($row2['FECHA_FACTURA']).",
										   '".$row2['REFERENCIA']."',
										   ".$row2['PEDIMENTO'].",
										   ".$salidanumero.",
										   ".get_column_value($row2['PROVEEDOR']).",
										   ".get_column_value($row2['ID_PROVEEDOR'], 'numeric').",
										   ".get_column_value($row2['NO_PARTES']).")";
									
						$query_mysql = mysqli_query($cmysqli,$consulta_mysql);		
						if ($query_mysql==false){
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje'] = 'Error en insertar factura en expos_salidas_facturas'.$consulta_mysql;
							$respuesta['Error']=' ['.$error.']';
							
							mysqli_query("ROLLBACK");
							break;
						}					
					}
					
					if ($respuesta['Codigo'] != '1') {
						break;
					} else {
						$consulta3="UPDATE salidas SET sync=true WHERE salidanumero=".$salidanumero;
						$query3 = odbc_exec ($conn_access, $consulta3);
						if ($query==false){
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']="Error al actualizar sync en Expos.mdb error:".odbc_errormsg ($conn_access).", ".$consulta3;
							$respuesta['Error']='';
							
							mysqli_query("ROLLBACK");
							break;
						} else {
							mysqli_query("COMMIT");
						}
					}
				}
			}
		}
	}
	
	echo json_encode($respuesta);
}

function get_column_value($oValue, $sType = '') {
	if(is_null($oValue)) { 
		return 'null';
	} else {
		switch ($sType) {
			case 'string':
				return "'".$oValue."'";
				break;
				
			case 'numeric':
				return $oValue;
				break;   
				
			default:
				return "'".$oValue."'";
		}
	}
}
