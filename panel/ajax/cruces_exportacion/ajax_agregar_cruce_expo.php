<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../url_archivos.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_lineat']) && !empty($_POST['id_lineat'])) {
		$respuesta['Codigo']=1;
		$id_lineat = $_POST['id_lineat'];
		$aduana = $_POST['aduana'];
		$tipo_salida = $_POST['tipo_salida'];
		$numero_caja = $_POST['numero_caja'];
		$id_transfer = $_POST['id_transfer'];
		
		$caat = $_POST['caat'];
		$scac = $_POST['scac'];
		
		$id_entregar = $_POST['id_entregar'];
		$nom_entregar = $_POST['nom_entregar'];
		$dir_entregar = $_POST['dir_entregar'];
		$indicaciones = $_POST['indicaciones'];
		
		$aFacturas = json_decode($_POST['aFacturas']);
		$fecharegistro =  date("Y-m-d H:i:s");
		$files = $_FILES;
		
		$consulta = "INSERT INTO bodega.cruces_expo (numcliente,numlinea,aduana,tiposalida,caja,notransfer,caat,scac,noentrega,
													nombreentrega,direntrega,indicaciones,id_usuario_registro,fecha_registro)
										VALUES ('".$idcliexpo."',
												".$id_lineat.",
												'".$aduana."',
												'".$tipo_salida."',
												'".$numero_caja."',
												".$id_transfer.",
												'".$caat."',
												'".$scac."',
												".$id_entregar.",
												'".$nom_entregar."',
												'".$dir_entregar."',
												'".$indicaciones."',
												".$id.",
												'".$fecharegistro."')";
		
		mysqli_query($cmysqli,"BEGIN");
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al insert la informacion del cruce.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
			mysqli_query($cmysqli,"ROLLBACK");
			mysqli_query($cmysqli,"COMMIT");
			exit(json_encode($respuesta));
		}
		$id_cruce = mysqli_insert_id($cmysqli);
		for($i=0; $i < count($aFacturas); $i++ ){
			//Guardar Archivos Facturas
			//ARCHIVO FACTURA PDF
			if(isset($files["f_factura_".$i])) {
				if($files["f_factura_".$i]["error"] == 0) {	
				
					$pdfFact = $files["f_factura_".$i]["tmp_name"];				
					$ext = pathinfo($files["f_factura_".$i]["name"], PATHINFO_EXTENSION);
					
					$NomPdfFact = 'C'.$id_cruce.'_FACTURA_'.$aFacturas[$i]->numero_factura.'_'.date("YmdHis").".".$ext;
					
					if(!isset($pdfFact)) {
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["f_factura_".$i]["name"].'] excede el máximo permitido.';
						$respuesta['Error'] = '';
						mysqli_query($cmysqli,"ROLLBACK");
						mysqli_query($cmysqli,"COMMIT");
						exit(json_encode($respuesta));
					}else{
						if(!move_uploaded_file($pdfFact, $dir_archivos_facturas.$NomPdfFact)){
							$respuesta['Codigo'] = '-1';
							$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["f_factura_".$i]["name"].'] en el servidor.';
							$respuesta['Error'] = '';
							exit(json_encode($respuesta));
						}
					}
				}else{
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error en el archivo de la factura ['.$files["f_factura_".$i]["name"].']';
					$respuesta['Error'] = '';
					mysqli_query($cmysqli,"ROLLBACK");
					mysqli_query($cmysqli,"COMMIT");
					exit(json_encode($respuesta));
				}
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error al recibir el archivo de la factura '.$aFacturas[$i]->numero_factura.'.';
				$respuesta['Error'] = '';
				mysqli_query($cmysqli,"ROLLBACK");
				mysqli_query($cmysqli,"COMMIT");
				exit(json_encode($respuesta));
			}
			//CFDI - XML
			if(isset($files["f_cfdi_".$i])) {
				if($files["f_cfdi_".$i]["error"] == 0) {	
				
					$xmlDFDI = $files["f_cfdi_".$i]["tmp_name"];				
					$ext = pathinfo($files["f_cfdi_".$i]["name"], PATHINFO_EXTENSION);
					
					$NomXMLCFDI = 'C'.$id_cruce.'_CFDI_'.$aFacturas[$i]->numero_factura.'_'.date("YmdHis").".".$ext;
					
					if(!isset($xmlDFDI)) {
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["f_cfdi_".$i]["name"].'] excede el máximo permitido.';
						$respuesta['Error'] = '';
						mysqli_query($cmysqli,"ROLLBACK");
						mysqli_query($cmysqli,"COMMIT");
						exit(json_encode($respuesta));
					}else{
						if(!move_uploaded_file($xmlDFDI, $dir_archivos_facturas.$NomXMLCFDI)){
							$respuesta['Codigo'] = '-1';
							$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["f_cfdi_".$i]["name"].'] en el servidor.';
							$respuesta['Error'] = '';
							exit(json_encode($respuesta));
						}
					}
				}else{
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error en el archivo ['.$files["f_cfdi_".$i]["name"].']';
					$respuesta['Error'] = '';
					mysqli_query($cmysqli,"ROLLBACK");
					mysqli_query($cmysqli,"COMMIT");
					exit(json_encode($respuesta));
				}
			}else{
				$respuesta['Codigo'] = '-1';
				$respuesta['Mensaje'] = 'Error al recibir el archivo cfdi de la factura '.$aFacturas[$i]->numero_factura.'.';
				$respuesta['Error'] = '';
				mysqli_query($cmysqli,"ROLLBACK");
				mysqli_query($cmysqli,"COMMIT");
				exit(json_encode($respuesta));
			}
			//CERTIFICADO DE ORIGEN
			if($_POST['bCerOri'.$i] == '1'){
				if(isset($files["f_cerori_".$i])) {
					if($files["f_cerori_".$i]["error"] == 0) {	
					
						$pdfCerOri = $files["f_cerori_".$i]["tmp_name"];				
						$ext = pathinfo($files["f_cerori_".$i]["name"], PATHINFO_EXTENSION);
						
						$NomCerOri = 'C'.$id_cruce.'_CerOri_'.$aFacturas[$i]->numero_factura.'_'.date("YmdHis").".".$ext;
						
						if(!isset($pdfCerOri)) {
							$respuesta['Codigo'] = '-1';
							$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["f_cerori_".$i]["name"].'] excede el máximo permitido.';
							$respuesta['Error'] = '';
							mysqli_query($cmysqli,"ROLLBACK");
							mysqli_query($cmysqli,"COMMIT");
							exit(json_encode($respuesta));
						}else{
							if(!move_uploaded_file($pdfCerOri, $dir_archivos_facturas.$NomCerOri)){
								$respuesta['Codigo'] = '-1';
								$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["f_cerori_".$i]["name"].'] en el servidor.';
								$respuesta['Error'] = '';
								exit(json_encode($respuesta));
							}
						}
					}else{
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'Error en el archivo ['.$files["f_cerori_".$i]["name"].']';
						$respuesta['Error'] = '';
						mysqli_query($cmysqli,"ROLLBACK");
						mysqli_query($cmysqli,"COMMIT");
						exit(json_encode($respuesta));
					}
				}else{
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error al recibir el archivo del certificado de origen de la factura '.$aFacturas[$i]->numero_factura.'.';
					$respuesta['Error'] = '';
					mysqli_query($cmysqli,"ROLLBACK");
					mysqli_query($cmysqli,"COMMIT");
					exit(json_encode($respuesta));
				}
			}
			//PckingList
			if($_POST['bPackList'.$i] == '1'){
				if(isset($files["f_plist_".$i])) {
					if($files["f_plist_".$i]["error"] == 0) {	
					
						$pdfPackList = $files["f_plist_".$i]["tmp_name"];				
						$ext = pathinfo($files["f_plist_".$i]["name"], PATHINFO_EXTENSION);
						
						$NomPackList = 'C'.$id_cruce.'_PackList_'.$aFacturas[$i]->numero_factura.'_'.date("YmdHis").".".$ext;
						
						if(!isset($pdfPackList)) {
							$respuesta['Codigo'] = '-1';
							$respuesta['Mensaje'] = 'El tamaño del archivo [ '.$files["f_plist_".$i]["name"].'] excede el máximo permitido.';
							$respuesta['Error'] = '';
							mysqli_query($cmysqli,"ROLLBACK");
							mysqli_query($cmysqli,"COMMIT");
							exit(json_encode($respuesta));
						}else{
							if(!move_uploaded_file($pdfPackList, $dir_archivos_facturas.$NomPackList)){
								$respuesta['Codigo'] = '-1';
								$respuesta['Mensaje'] = 'Error al guardar el archivo [ '.$files["f_plist_".$i]["name"].'] en el servidor.';
								$respuesta['Error'] = '';
								exit(json_encode($respuesta));
							}
						}
					}else{
						$respuesta['Codigo'] = '-1';
						$respuesta['Mensaje'] = 'Error en el archivo ['.$files["f_plist_".$i]["name"].']';
						$respuesta['Error'] = '';
						mysqli_query($cmysqli,"ROLLBACK");
						mysqli_query($cmysqli,"COMMIT");
						exit(json_encode($respuesta));
					}
				}else{
					$respuesta['Codigo'] = '-1';
					$respuesta['Mensaje'] = 'Error al recibir el archivo packing list de la factura '.$aFacturas[$i]->numero_factura.'.';
					$respuesta['Error'] = '';
					mysqli_query($cmysqli,"ROLLBACK");
					mysqli_query($cmysqli,"COMMIT");
					exit(json_encode($respuesta));
				}
			}
			//Guardar Informacion Facturas
			

			$fecha_factura = date_format(DateTime::createFromFormat('d/m/Y H:i:s', $aFacturas[$i]->fecha),'Y-m-d H:i:s');
			
			$consulta = "INSERT INTO bodega.cruces_expo_detalle (id_cruce,numero_factura,uuid,fecha_factura,noaaa,archivo_factura,archivo_cfdi";
			if($aFacturas[$i]->aviso_adhesion != '')
				$consulta .= "									,id_permiso_adhesion";
			if($aFacturas[$i]->aviso_aut != '')
				$consulta .= "									,id_permiso";
			if($_POST['bCerOri'.$i] == '1')
				$consulta .= " 									,archivo_cert_origen";
			if($_POST['bPackList'.$i] == '1')
				$consulta .= " 									,archivo_packinglist";
			
			$consulta .= "										)
								VALUES (".$id_cruce.",
										'".$aFacturas[$i]->numero_factura ."',
										'".$aFacturas[$i]->uuid ."',
										'".$fecha_factura ."',
										".$aFacturas[$i]->aaa .",
										'".$URL_archivos_facturas.$NomPdfFact."',
										'".$URL_archivos_facturas.$NomXMLCFDI."'";
			if($aFacturas[$i]->aviso_adhesion != '')
				$consulta .= "			,".$aFacturas[$i]->aviso_adhesion;
			if($aFacturas[$i]->aviso_aut != '')
				$consulta .= "			,".$aFacturas[$i]->aviso_aut;
			if($_POST['bCerOri'.$i] == '1')
				$consulta .= " 			,'".$URL_archivos_facturas.$NomCerOri."'";
			if($_POST['bPackList'.$i] == '1')
				$consulta .= " 			,'".$URL_archivos_facturas.$NomPackList."'";
			$consulta .= "				)";
			
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al insert la informacion de la factura '.$aFacturas[$i]->numero_factura.'.'; 
				$respuesta['Error'] = ' ['.$error.']'.$consulta;
				mysqli_query($cmysqli,"ROLLBACK");
				mysqli_query($cmysqli,"COMMIT");
				exit(json_encode($respuesta));
			}
		}
		mysqli_query($cmysqli,"COMMIT");
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}