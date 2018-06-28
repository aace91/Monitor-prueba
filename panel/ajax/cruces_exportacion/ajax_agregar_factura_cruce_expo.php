<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../url_archivos.php');
require('enviar_notificacion_cruces.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_cruce']) && !empty($_POST['id_cruce'])) {
		$respuesta['Codigo']=1;
		$id_cruce = $_POST['id_cruce'];
		$tipo_salida = $_POST['tipo_salida'];
		$numero_caja = $_POST['numero_caja'];
		$numero_factura = $_POST['numero_factura'];
		$uuid = $_POST['uuid'];
		$fecha = $_POST['fecha'];
		$referencia = $_POST['referencia'];
		$regimen = $_POST['regimen'];
		$atados = $_POST['atados'];
		$peso_kgs = $_POST['peso_kgs'];
		$peso_lbs = $_POST['peso_lbs'];
		$aaa = $_POST['aaa'];
		$permisos = json_decode($_POST['permisos']);
		$fecharegistro =  date("Y-m-d H:i:s");
		$files = $_FILES;
		
		//Guardar Archivos Facturas
		//ARCHIVO FACTURA PDF
		if(isset($files["f_factura"])) {
			if($files["f_factura"]["error"] == 0) {	
			
				$pdfFact = $files["f_factura"]["tmp_name"];				
				$ext = pathinfo($files["f_factura"]["name"], PATHINFO_EXTENSION);
				
				$NomPdfFact = 'C'.$id_cruce.'_FACTURA_'.$numero_factura.'_'.date("YmdHis").".".$ext;
				
				if(!isset($pdfFact)) {
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["f_factura"]["name"].'] excede el máximo permitido.';
					$respuesta['Error'] = '';
					exit(json_encode($respuesta));
				}else{
					if(!move_uploaded_file($pdfFact, $dir_archivos_facturas.$NomPdfFact)){
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["f_factura"]["name"].']['.$dir_archivos_facturas.$NomPdfFact.'] en el servidor.';
						$respuesta['Error'] = '';
						exit(json_encode($respuesta));
					}
				}
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error en el archivo de la factura ['.$files["f_factura"]["name"].']';
				$respuesta['Error'] = '';
				exit(json_encode($respuesta));
			}
		}else{
			$respuesta['Codigo'] = '-1';
			$respuesta['Mensaje'] = 'Error al recibir el archivo de la factura '.$numero_factura.'.';
			$respuesta['Error'] = '';
			exit(json_encode($respuesta));
		}
		//ANEXO FACTURA
		if($_POST['bAnexoFact'] == '1'){
			if(isset($files["f_anexofact"])) {
				if($files["f_anexofact"]["error"] == 0) {	
				
					$pdfAnexoFact = $files["f_anexofact"]["tmp_name"];				
					$ext = pathinfo($files["f_anexofact"]["name"], PATHINFO_EXTENSION);
					
					$NomAnexoFact = 'C'.$id_cruce.'_AnexoFact_'.$numero_factura.'_'.date("YmdHis").".".$ext;
					
					if(!isset($pdfAnexoFact)) {
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["f_anexofact"]["name"].'] excede el máximo permitido.';
						$respuesta['Error'] = '';
						exit(json_encode($respuesta));
					}else{
						if(!move_uploaded_file($pdfAnexoFact, $dir_archivos_facturas.$NomAnexoFact)){
							$respuesta['Codigo'] = '-1';
							$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["f_anexofact"]["name"].'] en el servidor.';
							$respuesta['Error'] = '';
							exit(json_encode($respuesta));
						}
					}
				}else{
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error en el archivo ['.$files["f_anexofact"]["name"].']';
					$respuesta['Error'] = '';
					exit(json_encode($respuesta));
				}
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error al recibir el archivo del anexo de la factura '.$numero_factura.'.';
				$respuesta['Error'] = '';
				exit(json_encode($respuesta));
			}
		}
		//CFDI - XML
		if($_POST['bCfdi'] == '1'){
			if(isset($files["f_cfdi"])) {
				if($files["f_cfdi"]["error"] == 0) {	
				
					$xmlDFDI = $files["f_cfdi"]["tmp_name"];				
					$ext = pathinfo($files["f_cfdi"]["name"], PATHINFO_EXTENSION);
					
					$NomXMLCFDI = 'C'.$id_cruce.'_CFDI_'.$numero_factura.'_'.date("YmdHis").".".$ext;
					
					if(!isset($xmlDFDI)) {
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["f_cfdi"]["name"].'] excede el máximo permitido.';
						$respuesta['Error'] = '';
						exit(json_encode($respuesta));
					}else{
						if(!move_uploaded_file($xmlDFDI, $dir_archivos_facturas.$NomXMLCFDI)){
							$respuesta['Codigo'] = '-1';
							$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["f_cfdi"]["name"].'] en el servidor.';
							$respuesta['Error'] = '';
							exit(json_encode($respuesta));
						}
					}
				}else{
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error en el archivo ['.$files["f_cfdi"]["name"].']';
					$respuesta['Error'] = '';
					exit(json_encode($respuesta));
				}
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error al recibir el archivo cfdi de la factura '.$numero_factura.'.';
				$respuesta['Error'] = '';
				exit(json_encode($respuesta));
			}
		}
		//CERTIFICADO DE ORIGEN
		if($_POST['bCerOri'] == '1'){
			if(isset($files["f_cerori"])) {
				if($files["f_cerori"]["error"] == 0) {	
				
					$pdfCerOri = $files["f_cerori"]["tmp_name"];				
					$ext = pathinfo($files["f_cerori"]["name"], PATHINFO_EXTENSION);
					
					$NomCerOri = 'C'.$id_cruce.'_CerOri_'.$numero_factura.'_'.date("YmdHis").".".$ext;
					
					if(!isset($pdfCerOri)) {
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["f_cerori"]["name"].'] excede el máximo permitido.';
						$respuesta['Error'] = '';
						exit(json_encode($respuesta));
					}else{
						if(!move_uploaded_file($pdfCerOri, $dir_archivos_facturas.$NomCerOri)){
							$respuesta['Codigo'] = '-1';
							$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["f_cerori"]["name"].'] en el servidor.';
							$respuesta['Error'] = '';
							exit(json_encode($respuesta));
						}
					}
				}else{
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error en el archivo ['.$files["f_cerori"]["name"].']';
					$respuesta['Error'] = '';
					exit(json_encode($respuesta));
				}
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error al recibir el archivo del certificado de origen de la factura '.$numero_factura.'.';
				$respuesta['Error'] = '';
				exit(json_encode($respuesta));
			}
		}
		//PckingList
		if($_POST['bPackList'] == '1'){
			if(isset($files["f_plist"])) {
				if($files["f_plist"]["error"] == 0) {	
				
					$pdfPackList = $files["f_plist"]["tmp_name"];				
					$ext = pathinfo($files["f_plist"]["name"], PATHINFO_EXTENSION);
					
					$NomPackList = 'C'.$id_cruce.'_PackList_'.$numero_factura.'_'.date("YmdHis").".".$ext;
					
					if(!isset($pdfPackList)) {
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["f_plist"]["name"].'] excede el máximo permitido.';
						$respuesta['Error'] = '';
						exit(json_encode($respuesta));
					}else{
						if(!move_uploaded_file($pdfPackList, $dir_archivos_facturas.$NomPackList)){
							$respuesta['Codigo'] = '-1';
							$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["f_plist"]["name"].'] en el servidor.';
							$respuesta['Error'] = '';
							exit(json_encode($respuesta));
						}
					}
				}else{
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error en el archivo ['.$files["f_plist"]["name"].']';
					$respuesta['Error'] = '';
					exit(json_encode($respuesta));
				}
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error al recibir el archivo packing list de la factura '.$numero_factura.'.';
				$respuesta['Error'] = '';
				exit(json_encode($respuesta));
			}
		}
		//Ticket de Bascula
		if($_POST['bTicketBascula'] == '1'){
			if(isset($files["f_ticketbascula"])) {
				if($files["f_ticketbascula"]["error"] == 0) {	
				
					$pdfTicketBascula = $files["f_ticketbascula"]["tmp_name"];				
					$ext = pathinfo($files["f_ticketbascula"]["name"], PATHINFO_EXTENSION);
					
					$NomTicketBas = 'C'.$id_cruce.'_ticketbas_'.$numero_factura.'_'.date("YmdHis").".".$ext;
					
					if(!isset($pdfTicketBascula)) {
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["f_ticketbascula"]["name"].'] excede el máximo permitido.';
						$respuesta['Error'] = '';
						exit(json_encode($respuesta));
					}else{
						if(!move_uploaded_file($pdfTicketBascula, $dir_archivos_facturas.$NomTicketBas)){
							$respuesta['Codigo'] = '-1';
							$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["f_ticketbascula"]["name"].'] en el servidor.';
							$respuesta['Error'] = '';
							exit(json_encode($respuesta));
						}
					}
				}else{
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error en el archivo ['.$files["f_ticketbascula"]["name"].']';
					$respuesta['Error'] = '';
					exit(json_encode($respuesta));
				}
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error al recibir el archivo ticket de bascula de la factura '.$numero_factura.'.';
				$respuesta['Error'] = '';
				exit(json_encode($respuesta));
			}
		}
		//Guardar Informacion Facturas		
		$fecha_factura = date_format(DateTime::createFromFormat('d/m/Y H:i:s', $fecha),'Y-m-d H:i:s');
		
		$consulta = "INSERT INTO bodega.cruces_expo_detalle (id_cruce,tiposalida,caja,numero_factura,uuid,fecha_factura,
															referencia,regimen,atados,peso_factura_kgs,peso_factura_lbs,
															noaaa,archivo_factura";
		if($_POST['bCfdi'] == '1')
			$consulta .= " 									,archivo_cfdi";
		if($_POST['bAnexoFact'] == '1')
			$consulta .= " 									,archivo_anexo_factura";
		if($_POST['id_certificado'] != '')
			$consulta .= " 									,id_certificado";
		if($_POST['bCerOri'] == '1')
			$consulta .= " 									,archivo_cert_origen";
		if($_POST['bPackList'] == '1')
			$consulta .= " 									,archivo_packinglist";
		if($_POST['bTicketBascula'] == '1')
			$consulta .= " 									,archivo_ticketbascula";
		$consulta .= "										)
							VALUES (".$id_cruce.",
									'".$tipo_salida."',
									'".$numero_caja."',
									'".$numero_factura."',
									'".$uuid."',
									'".$fecha_factura."',
									'".$referencia."',
									'".$regimen."',
									".$atados.",
									".$peso_kgs.",
									".$peso_lbs .",
									".$aaa.",
									'".$URL_archivos_facturas.$NomPdfFact."'";
		if($_POST['bCfdi'] == '1')
			$consulta .= " 			,'".$URL_archivos_facturas.$NomXMLCFDI."'";
		if($_POST['bAnexoFact'] == '1')
			$consulta .= " 			,'".$URL_archivos_facturas.$NomAnexoFact."'";
		if($_POST['id_certificado'] != '')
			$consulta .= " 			,".$_POST['id_certificado'];
		if($_POST['bCerOri'] == '1')
			$consulta .= " 			,'".$URL_archivos_facturas.$NomCerOri."'";
		if($_POST['bPackList'] == '1')
			$consulta .= " 			,'".$URL_archivos_facturas.$NomPackList."'";
		if($_POST['bTicketBascula'] == '1')
			$consulta .= " 			,'".$URL_archivos_facturas.$NomTicketBas."'";
		$consulta .= "				)";
		
		mysqli_query($cmysqli,"BEGIN");
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al insert la informacion de la factura '.$numero_factura.'.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
			mysqli_query($cmysqli,"ROLLBACK");
			mysqli_query($cmysqli,"COMMIT");
			exit(json_encode($respuesta));
		}
		$id_detalle_cruce = mysqli_insert_id($cmysqli);
		for($r=0; $r < count($permisos); $r++ ){
			
			$consulta = "INSERT INTO bodega.cruces_expo_permisos (
									id_detalle_cruce, 
									id_permiso";
			if($permisos[$r]->aviso_adhesion != '')
				$consulta .= "		,id_permiso_adhesion";
			$consulta .= "			)
							VALUES (
									'".$id_detalle_cruce."',
									'".$permisos[$r]->aviso_aut."'";
			if($permisos[$r]->aviso_adhesion != '')
				$consulta .= "		,'".$permisos[$r]->aviso_adhesion."'";
			$consulta .= "			)";
			
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al insertar la informacion de los permisos. ['.$permisos[$r]->aviso_adhesion.']['.$permisos[$r]->aviso_aut.']'; 
				$respuesta['Error'] = ' ['.$error.']'.$consulta;
				mysqli_query($cmysqli,"ROLLBACK");
				mysqli_query($cmysqli,"COMMIT");
				exit(json_encode($respuesta));
			}
		}
		mysqli_query($cmysqli,"COMMIT");
		include('consultar_facturas.php');
		$respuesta['Mensaje']='La factura se ha eliminado correctamente!!';
		$res = enviar_notificacion_nuevo_cruce_email($id_cruce,'Editar','Agrego Factura '.$numero_factura);
		if($res['Codigo'] != 1){
			$respuesta['Mensaje'] .=  $res['Error'];
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}