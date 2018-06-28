<?php
	$consulta = "SELECT cd.id_detalle_cruce,cd.tiposalida,cd.caja,cd.numero_factura,cd.uuid,cd.fecha_factura,aaa.nombreaa, 
						cd.referencia,IFNULL(cd.cons_fact,'') as cons_fact,cd.regimen, cd.atados, cd.peso_factura_kgs,peso_factura_lbs,
						cd.archivo_factura,
						IFNULL(cd.archivo_cfdi,'') as archivo_cfdi,
						IFNULL(cd.archivo_anexo_factura,'') as archivo_anexo_factura,
						IF(cd.archivo_cert_origen IS NULL, IF(cd.id_certificado IS NULL, '', CONCAT('".$URL_archivos_certificados_origen."',cer.archivo_certificado)),'') as archivo_cert_origen,
						IFNULL(cd.archivo_packinglist,'') AS archivo_packinglist,
						IFNULL(cd.archivo_ticketbascula,'') AS archivo_ticketbascula,
						GROUP_CONCAT(DISTINCT con.email SEPARATOR '; ') as contactos_aaa
					FROM bodega.cruces_expo_detalle cd
						INNER JOIN bodega.aaa ON 
							cd.noaaa = aaa.numeroaa
						LEFT JOIN contactos_expo con ON
							cd.noaaa = con.id_catalogo AND
							con.tipo_catalogo = 'AAA'
						LEFT JOIN certificados_origen cer ON
							cd.id_certificado = cer.id_certificado
					WHERE id_cruce = ".$id_cruce."
					GROUP BY cd.id_detalle_cruce";
	
	$query = mysqli_query($cmysqli,$consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['Mensaje']='Problema al consultar la tabla de las facturas.'; 
		$respuesta['Error'] = ' ['.$error.']'.$consulta;
		exit(json_encode($respuesta));
	}else{
		$aFacturas = array();
		while ($rowfac = mysqli_fetch_array($query)){
			$id_detalle_cruce = $rowfac['id_detalle_cruce'];
			$tiposalida = $rowfac['tiposalida'];
			$caja = $rowfac['caja'];
			$cons_fact = $rowfac['cons_fact'];
			$fac = $rowfac['numero_factura'];
			$uuid = $rowfac['uuid'];
			$fecfac = $rowfac['fecha_factura'];
			$referencia = $rowfac['referencia'];
			$regimen = $rowfac['regimen'];
			$atados = $rowfac['atados'];
			$peso_factura_kgs = $rowfac['peso_factura_kgs'];
			$peso_factura_lbs = $rowfac['peso_factura_lbs'];
			$aaa = $rowfac['nombreaa'];
			$afac = $rowfac['archivo_factura'];
			$cfdi = $rowfac['archivo_cfdi'];
			$cfdi = ($cfdi == '' ? '' : array('name' => '<a class="btn btn-info btn-xs" href="'.$cfdi.'" target="_blank"><span class="glyphicon glyphicon-eye-open"></span> Ver Archivo</a>&nbsp;&nbsp;
										                 <a href="https://www.delbravoweb.com/sii/admin/ajax/cruces/descargar_cfdi_pdf.php?icd='.$id_detalle_cruce.'" target="_blank" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-download"></span> Descargar PDF</a>'));
			$aanexo = $rowfac['archivo_anexo_factura'];
			$aanexo = ($aanexo == '' ? '' : array('name' => '<a class="btn btn-info btn-xs" href="'.$aanexo.'" target="_blank"><span class="glyphicon glyphicon-eye-open"></span> Ver Archivo</a>'));
			$acor = $rowfac['archivo_cert_origen'];
			$acor = ($acor == '' ? '' : array('name' => '<a href="'.$acor.'" class="btn btn-info btn-xs" target="_blank"><span class="glyphicon glyphicon-download"></span> Descargar PDF</a>'));
			$apack = $rowfac['archivo_packinglist'];
			$apack = ($apack == '' ? '' : array('name' => '<a href="'.$apack.'" class="btn btn-info btn-xs" target="_blank"><span class="glyphicon glyphicon-download"></span> Descargar PDF</a>'));
			$cont_aaa = $rowfac['contactos_aaa'];
			$atckbas = $rowfac['archivo_ticketbascula'];
			$atckbas = ($atckbas == '' ? '' : array('name' => '<a href="'.$atckbas.'" class="btn btn-info btn-xs" target="_blank"><span class="glyphicon glyphicon-download"></span> Descargar PDF</a>'));
			include('consultar_permisos_cruces.php');
			$permisos = $aPermisos;
			
			//Verificar documentacion de la factura.CASA
			require('./../../../connect_casa.php');
			$Ped = '';
			$qCasa = "SELECT a.ADU_DESP, a.PAT_AGEN, a.NUM_PEDI,f.NUM_REM,
								CASE WHEN a.FIR_REME IS NULL THEN '' ELSE a.FIR_REME END AS FIR_REME
							FROM SAAIO_PEDIME a
								LEFT JOIN SAAIO_FACTUR f ON
									a.NUM_REFE = f.NUM_REFE
							WHERE a.NUM_REFE='".$referencia."' ".(trim($cons_fact) == '' ? '' :"AND f.CONS_FACT = ".$cons_fact)."
							GROUP BY a.ADU_DESP, a.PAT_AGEN, a.NUM_PEDI,a.FIR_REME,f.NUM_REM";
			$resped = odbc_exec ($odbccasa, $qCasa);
			if ($resped == false){
				$mensaje = "Error al consultar pedimento de la factura '".$fac."'. BD.CASA.".odbc_error();
				echo json_encode( array("error" => $mensaje));
				exit(0);
			}else{
				$nItem = 0;
				while(odbc_fetch_row($resped)){
					$Aduana = odbc_result($resped,"ADU_DESP");
					$Patente = odbc_result($resped,"PAT_AGEN");
					$Pedimento = odbc_result($resped,"NUM_PEDI");
					$NumRemesa = odbc_result($resped,"NUM_REM");
					$Fir_Refe = odbc_result($resped,"FIR_REME");
					$Ped = $Aduana.'-'.$Patente.'-'.$Pedimento.($Fir_Refe == '' && trim($cons_fact) != '' ? '' : '-'.$NumRemesa);
					if(trim($cons_fact) != ''){
						$Ped = '<a class="btn btn-info btn-xs" href="javascript:void(0);" onclick="show_tabla_soia_estados(\''.$id_cruce.'\',\''.$Ped.'\');return false;" style="padding-left:.5em;" title="">'.$Ped.'</a>';
						if(file_exists ($dir_archivos_pedimentos.$referencia.($Fir_Refe == '' ? '' : '-'.$NumRemesa).'.pdf')){
							$Ped .= '&nbsp;<a target="_blank" class="btn btn-info btn-xs" href="http://www.delbravoweb.com:8091/pedimentos2009/'.$referencia.($NumRemesa == '' ? '' : '-'.$NumRemesa).'.pdf" style="padding-left:.5em;" title=""><i class="fa fa-download" aria-hidden="true"></i> Descargar Pedimento</a>';
						}else{
							$Ped .= '&nbsp;<a class="btn btn-info btn-xs" href="javascript:void(0);" onclick="subir_pedimento_simplificado(\''.$referencia.'\',\''.$NumRemesa.'\');return false;" style="padding-left:.5em;" title=""><i class="fa fa-upload" aria-hidden="true"></i> Subir Pedimento</a>';
						}
					}
				}
			}
			//Armar el array de la tabla
			$aFac = array(
				'id' => $id_detalle_cruce,
				'eliminar' => '<a href="javascript:void(0);" class="btn btn-warning btn-xs" onclick="eliminar_factura_editar_cruce(\''.$id_detalle_cruce.'\'); return false;"><span class="glyphicon glyphicon-trash"></span> Eliminar<a>',
				'editar' => '<a href="javascript:void(0);" class="btn btn-primary btn-xs" onclick="ajax_consulta_factura_editar(\''.$id_detalle_cruce.'\'); return false;"><span class="glyphicon glyphicon-pencil"></span> Editar<a>',
				'tipo_salida' => $tiposalida,
				'numero_caja' => $caja,
				'numero_factura' => $fac,
				'uuid' => $uuid,
				'fecha' => $fecfac,
				'regimen'=> $regimen,
				'referencia' => $referencia,
				'atados'=> $atados,
				'peso_kgs'=> $peso_factura_kgs,
				'peso_lbs'=> $peso_factura_lbs,
				'aaa_nom' => $aaa,
				'permisos' => $permisos,
				'pdf' => array('name' => '<a href="'.$afac.'" target="_blank" class="btn btn-info btn-xs"><span class="glyphicon glyphicon-download"></span> Descargar PDF</a>'),
				'xml' => $cfdi,
				'anexofact' => $aanexo,
				'certificado' => $acor,						
				'plist' => $apack,
				'pedimento' => $Ped,
				'ticketbas' => $atckbas,
				'cont_aaa' => $cont_aaa
				);
			array_push($aFacturas,$aFac);
		}
		$respuesta['aFacturas'] = $aFacturas;
	}
?>