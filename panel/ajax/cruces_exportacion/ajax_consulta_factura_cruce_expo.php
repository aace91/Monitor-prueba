<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');
include('consultar_controles_factura.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_detalle_cruce']) && !empty($_POST['id_detalle_cruce'])) {  
		$respuesta['Codigo']=1;
		$id_detalle_cruce = $_POST['id_detalle_cruce'];
		$id_cliente = $_POST['id_cliente'];
		
		$consulta = " SELECT cd.tiposalida,cd.caja,cd.numero_factura,cd.uuid,cd.fecha_factura,IFNULL(cd.referencia,'') as referencia,
							cd.regimen,r.des_doc as regimen_nom,cd.atados,cd.peso_factura_kgs,cd.peso_factura_lbs,
							cd.noaaa,aaa.nombreaa,cd.archivo_factura,cd.archivo_cfdi,
							IF(cd.id_certificado IS NULL, '', cd.id_certificado) as id_certificado,
							IF(cd.archivo_cert_origen, '', cd.archivo_cert_origen) as archivo_cert_origen,
							IF(cd.archivo_packinglist, '', cd.archivo_packinglist) as archivo_packinglist,
							IF(cd.archivo_ticketbascula, '', cd.archivo_ticketbascula) as archivo_ticketbascula
						FROM cruces_expo_detalle cd
							LEFT JOIN casa.ctarc_docume r ON
								cd.regimen = r.cve_doc
							INNER JOIN aaa ON
								cd.noaaa = aaa.numeroaa
						WHERE cd.id_detalle_cruce = ".$id_detalle_cruce;
	
		$query =  mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar la informacion del cruce. Por favor contacte el administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		}else{
			$respuesta = consultar_controles_facturas_select($id_cliente);
			while ($row = mysqli_fetch_array($query)){				
				$fFactura = '<a href="'.$row['archivo_factura'].'" target="_blank" class="btn btn-info" title="Descargar Factura"><span class="glyphicon glyphicon-download"></span> <span class="hidden-sm hidden-md hidden-xs">Descargar Factura</span><span class="hidden-lg hidden-xs">Descargar</span></a>';
				$fCFDI = '<a href="'.$row['archivo_cfdi'].'" target="_blank" class="btn btn-info" title="Descargar CFDI"><span class="glyphicon glyphicon-download"></span> Descargar CFDI</a>';
				$fArchCer = ($row['archivo_cert_origen'] == '' ? '' : '<a href="'.$row['archivo_cert_origen'].'" target="_blank" class="btn btn-info" title="Descargar Certificado Origen"><span class="glyphicon glyphicon-download"></span><span class="hidden-sm hidden-md hidden-xs">Descargar Certificado Origen</span><span class="hidden-lg hidden-xs">Descargar</span></a>');
				$fPackList = ($row['archivo_packinglist'] == '' ? '' : '<a href="'.$row['archivo_packinglist'].'" target="_blank" class="btn btn-info" title="Descargar Packing List"><span class="glyphicon glyphicon-download"></span> <span class="hidden-sm hidden-md hidden-xs">Descargar Packing List</span><span class="hidden-lg hidden-xs">Descargar</span></a>');
				$fTickBasc = ($row['archivo_ticketbascula'] == '' ? '' : '<a href="'.$row['archivo_ticketbascula'].'" target="_blank" class="btn btn-info" title="Descargar Ticket Bascula"><span class="glyphicon glyphicon-download"></span> <span class="hidden-sm hidden-md hidden-xs">Descargar Ticket Bascula</span><span class="hidden-lg hidden-xs">Descargar</span></a>');
				
				$respuesta['tipo_salida'] = $row['tiposalida'];
				$respuesta['numero_caja'] = $row['caja'];
				$respuesta['numero_factura'] = $row['numero_factura'];
				$respuesta['uuid'] = $row['uuid'];
				$respuesta['fecha_factura'] = $row['fecha_factura'];
				$respuesta['regimen'] = $row['regimen'];
				$respuesta['referencia'] = $row['referencia'];
				$respuesta['regimen_nom'] = $row['regimen'].'-'.$row['regimen_nom'];
				$respuesta['atados'] = $row['atados'];
				$respuesta['peso_factura_kgs'] = $row['peso_factura_kgs'];
				$respuesta['peso_factura_lbs'] = $row['peso_factura_lbs'];
				$respuesta['noaaa'] = $row['noaaa'];
				$respuesta['nombreaa'] = $row['nombreaa'];
				$respuesta['archivo_factura'] = $fFactura;
				$respuesta['archivo_cfdi'] = $fCFDI;
				$respuesta['id_certificado'] = $row['id_certificado'];
				$respuesta['archivo_cert_origen'] = $fArchCer;
				$respuesta['nombre_cert_origen'] = array_pop(explode('/',$row['archivo_cert_origen']));
				$respuesta['archivo_packinglist'] = $fPackList;
				$respuesta['nombre_packinglist'] = array_pop(explode('/',$row['archivo_packinglist']));
				$respuesta['archivo_ticketbascula'] = $fTickBasc;
				$respuesta['nombre_ticketbascula'] = array_pop(explode('/',$row['archivo_ticketbascula']));
				
				$consulta = "SELECT CONCAT(ad.numero,'-',l.patente,'-',l.pedimento) as pedimento
								FROM librop_libro l
									INNER JOIN librop_aduanas ad ON
										l.id_aduana = ad.id_aduana
								WHERE referencia = '".$respuesta['referencia']."'";
				
				$query =  mysqli_query($cmysqli,$consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al consultar la informacion del pedimento.'; 
					$respuesta['Error'] = ' ['.$error.']';
				}else{
					while ($row = mysqli_fetch_array($query)){
						$respuesta['pedimento'] = $row['pedimento'];
					}
				}
				
			}
			include('consultar_permisos_cruces.php');
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
