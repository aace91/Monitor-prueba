<?php
include_once('./../../../checklogin.php');
include ('./../../../connect_casa.php');

$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'pruebas/') !== false)? true : false);

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) { 
	$action = $_REQUEST['action'];
	if($loggedIn == false){
		switch ($action) {
			default:
				exit('500');
		}
	} else {
		switch ($action) {
			case 'consultar_covecontenedores' : $respuesta = fcn_consultar_covecontenedores();
				echo json_encode($respuesta);
				break;
		}
	}
} else {
	$respuesta['Codigo']=-1;
	$respuesta['Mensaje']='No se recibio metodo!!!';
	$respuesta['Error'] = '';
	echo json_encode($respuesta);
}

/*************************************************************************************************/
/* METODOS                                                                                       */
/*************************************************************************************************/

function fcn_consultar_covecontenedores(){
	global $_POST, $bDebug, $odbccasa;
	
	$respuesta['Codigo']=1;
	try {
		if (isset($_POST['sReferencia']) && !empty($_POST['sReferencia'])) {
			$sReferencia = $_POST['sReferencia'];
			
			//***********************************************************//
			
			$aCoves = array();
			$aContenedores = array();
			$sTipoPedimento = '';
			
			//***********************************************************//

			$consulta = "SELECT b.NUM_FACT, a.E_DOCUMENT
						 FROM SAAIO_COVE a INNER JOIN 
							  SAAIO_FACTUR b ON b.NUM_REFE=a.NUM_REFE AND
											    b.CONS_FACT=a.CONS_FACT       
						 WHERE a.NUM_REFE='".$sReferencia."'";
			
			$query = odbc_exec($odbccasa, $consulta);
			if ($query==false){ 
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "Error al consultar los coves de la Referencia [".$sReferencia."] en el sistema CASA.";
				$respuesta['Error'] = ' ['.$query.']';
			} else {
				if(odbc_num_rows($query) <= 0){ 
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "La referencia [".$sReferencia."] no existe en el sistema CASA.";
					$respuesta['Error'] = '';
				} else { 
					while(odbc_fetch_row($query)){ 
						$sFactura = (is_null(odbc_result($query,"NUM_FACT"))? '': odbc_result($query,"NUM_FACT"));
						$sEdocument = (is_null(odbc_result($query,"E_DOCUMENT"))? '': odbc_result($query,"E_DOCUMENT"));

						array_push($aCoves, array('factura'=>$sFactura, 'cove'=>$sEdocument));
					}
				}
			}

			if ($respuesta['Codigo'] == 1) {
				$consulta = "SELECT a.NUM_PEDI, a.PAT_AGEN, a.FIR_REME
							 FROM SAAIO_PEDIME a
							 WHERE a.NUM_REFE='".$sReferencia."'";
				
				$query = odbc_exec($odbccasa, $consulta);
				if ($query==false){ 
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "Error al consultar la Referencia [".$sReferencia."] en el sistema CASA.";
					$respuesta['Error'] = '';
				} else {
					while(odbc_fetch_row($query)){ 
						$sTipoPedimento = (is_null(odbc_result($query,"FIR_REME"))? 'normal': 'consolidado');
						break;
					}
				}
			}
			
			if ($respuesta['Codigo'] == 1) {
				switch ($sTipoPedimento) {
					case 'normal':
						$consulta = "SELECT a.NUM_REFE, a.NUM_CONT, a.CVE_CONT, b.DESCRIP
								     FROM SAAIO_CONTEN a INNER JOIN 
									      CTARC_CATGRA b ON b.CVE_1='CTN' AND
													        b.CVE_2=a.CVE_CONT
								     WHERE a.NUM_REFE='".$sReferencia."'";
						break;
				
					case 'consolidado':
						$consulta = "SELECT a.NUM_REFE, b.NUM_CONT, b.CVE_CONT, c.DESCRIP
									 FROM SAAIO_FACTUR a INNER JOIN
										  SAAIO_FACCON b ON b.NUM_REFE=a.NUM_REFE AND
														    b.CONS_FACT = a.CONS_FACT INNER JOIN 
										  CTARC_CATGRA c ON c.CVE_1='CTN' AND
														    c.CVE_2=b.CVE_CONT
									 WHERE a.NUM_REFE='".$sReferencia."'";
						break;

					default:
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = "Tipo de pedimento no admintido.";
						$respuesta['Error'] = '';
						break;
				}

				$query = odbc_exec($odbccasa, $consulta);
				if ($query==false){ 
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "Error al consultar los contenedores de la Referencia [".$sReferencia."] en el sistema CASA.";
					$respuesta['Error'] = ' ['.$query.']';
				} else {
					if(odbc_num_rows($query) <= 0){ 
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = "La referencia [".$sReferencia."] no existe en el sistema CASA.";
						$respuesta['Error'] = '';
					} else { 
						while(odbc_fetch_row($query)){ 
							$sContenedor = (is_null(odbc_result($query,"NUM_CONT"))? '': odbc_result($query,"NUM_CONT"));
							$sDescripcion = (is_null(odbc_result($query,"DESCRIP"))? '': odbc_result($query,"DESCRIP"));
	
							array_push($aContenedores, array('contenedor'=>$sContenedor, 'tipo'=>$sDescripcion));
						}
					}
				}
				
			}

			$respuesta['aCoves'] = $aCoves;
			$respuesta['aContenedores'] = $aContenedores;
		} else {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='No se recibieron datos';
			$respuesta['Error'] = '';
		}	
	} catch(Exception $e) {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='Error fcn_consultar_remision().'; 
		$respuesta['Error'] = ' ['.$e->getMessage().']';
	}
	return $respuesta;
}

/*************************************************************************************************/
/* FUNCIONES                                                                                     */
/*************************************************************************************************/

function fcn_get_tipo_cobro($id_cliente) {
	global $bDebug, $cmysqli;
	
	$consulta = "SELECT tipo_cobro
				 FROM facturacion.clientes
				 WHERE id_cliente=".$id_cliente;

	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		throw new Exception("fcn_get_tipo_cobro() ". $error);
	}
	
	if (mysqli_num_rows($query) == 0) {
		throw new Exception('El cliente no existe en el sistema de facturacion.');
	} else {
		while($row = mysqli_fetch_array($query)){ 
			return $row['tipo_cobro'];
		}
	}
}