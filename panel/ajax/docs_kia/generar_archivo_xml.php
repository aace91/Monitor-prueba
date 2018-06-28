<?php
	function crear_archivo_xml_kia($Referencia, $NumeroParte){
		//CONEXION BD
		global $odbccasa;
		$nPedimento = '';
		//FIREBIRD - ENCABEZADO
		$consulta = "SELECT p.NUM_REFE,p.NUM_PEDI,p.FEC_PAGO,p.FEC_ENTR,p.TIP_CAMB,
						CASE WHEN p.CVE_PEDI = 'V1' THEN
								'C'/*'C-Temp. Ind. Virtual'*/
							WHEN p.REG_ADUA = 'IMD' THEN
								'A'/*'A-Def.'*/
							WHEN p.REG_ADUA = 'DFI' THEN
								'D'/*'D-Deposito Fiscal'*/
							ELSE
								'B'/*'B-Temp. Dir.'*/
						END AS TIPO,
						p.CVE_PEDI,p.PAT_AGEN,
						substring(p.ADU_DESP from 1 for 2) as CLAVE_ADUANA,
						substring(p.ADU_DESP from 3 for 3) as CLAVE_SECCION,
						ROUND(CASE WHEN s.SEGUROS IS NULL THEN 0 ELSE  p.VAL_COME END) as VAL_SEGUROS,
						ROUND(CASE WHEN s.SEGUROS IS NULL THEN 0 ELSE s.SEGUROS END) AS SEGUROS,
						ROUND(CASE WHEN f.FLETES IS NULL THEN 0 ELSE f.FLETES END) AS FLETES,
						ROUND(CASE WHEN e.EMBALAJES IS NULL THEN 0 ELSE e.EMBALAJES END) AS EMBALAJES,
						ROUND(CASE WHEN o.OTROS IS NULL THEN 0 ELSE o.OTROS END) AS OTROS,
						CASE WHEN DTA.VAL_TASA IS NULL THEN 0 ELSE DTA.TOT_IMPU END AS DTA_IMP,
						CASE WHEN p.FEC_PAGO > '2014-03-01 00:00:00' THEN (PRV.VAL_TASA - 20) ELSE 0 END AS PRV_IMP,
						CASE WHEN p.FEC_PAGO > '2014-03-01 00:00:00' THEN (PRV.TOT_IMPU-(PRV.VAL_TASA - 20)) ELSE 0 END AS CNT_IMP,
						LIST(DISTINCT pr.CVE_PROC, ', ') AS CVE_PRV_CLI
			FROM saaio_pedime p
				INNER JOIN saaio_factur fac ON
					p.NUM_REFE = fac.NUM_REFE
				INNER JOIN ctrac_proved pr ON
					fac.CVE_PROV = pr.CVE_PRO
				LEFT JOIN (SELECT CASE WHEN IMP_INCR  IS NULL THEN 0 ELSE 
												CASE WHEN MON_INCR = 'USD' THEN
													IMP_INCR * TIP_CAMB
												ELSE
													CASE WHEN MON_INCR = 'MXP' THEN 
														IMP_INCR
													ELSE						
														(IMP_INCR * EQU_DLLS) * TIP_CAMB
													END
												END
											END AS SEGUROS,NUM_REFE
									FROM vsaaio_increm
									WHERE CVE_INCR = 2) s ON
											 p.NUM_REFE = s.NUM_REFE
				LEFT JOIN (SELECT CASE WHEN IMP_INCR  IS NULL THEN 0 ELSE 
												CASE WHEN MON_INCR = 'USD' THEN
													IMP_INCR * TIP_CAMB
												ELSE
													CASE WHEN MON_INCR = 'MXP' THEN 
														IMP_INCR
													ELSE						
														ROUND(((IMP_INCR * EQU_DLLS) * TIP_CAMB),0)
													END
												END
											END AS FLETES,NUM_REFE
									FROM vsaaio_increm
									WHERE CVE_INCR = 1) f ON
											 p.NUM_REFE = f.NUM_REFE
				LEFT JOIN (SELECT CASE WHEN IMP_INCR  IS NULL THEN 0 ELSE 
												CASE WHEN MON_INCR = 'USD' THEN
													ROUND((IMP_INCR * TIP_CAMB),0)
												ELSE
													CASE WHEN MON_INCR = 'MXP' THEN 
														IMP_INCR
													ELSE						
														ROUND(((IMP_INCR * EQU_DLLS) * TIP_CAMB),0)
													END
												END
											END AS EMBALAJES,NUM_REFE
									FROM vsaaio_increm
									WHERE CVE_INCR = 3) e ON
											 p.NUM_REFE = e.NUM_REFE
				LEFT JOIN (SELECT SUM(CASE WHEN IMP_INCR  IS NULL THEN 0 ELSE 
												CASE WHEN MON_INCR = 'USD' THEN
													IMP_INCR * TIP_CAMB
												ELSE
													CASE WHEN MON_INCR = 'MXP' THEN 
														IMP_INCR
													ELSE						
														(IMP_INCR * EQU_DLLS) * TIP_CAMB
													END
												END
											END) AS OTROS,NUM_REFE
									FROM vsaaio_increm
									WHERE CVE_INCR NOT IN (1,2,3)
									GROUP BY NUM_REFE) o ON
											 p.NUM_REFE = o.NUM_REFE
				LEFT JOIN saaio_contped AS DTA ON
								 p.NUM_REFE = DTA.NUM_REFE AND 
								 DTA.CVE_IMPU = 1
				LEFT JOIN saaio_contped AS PRV ON
								 p.NUM_REFE = PRV.NUM_REFE AND 
								 PRV.CVE_IMPU = 15
			WHERE p.NUM_REFE = '".$Referencia."'
			GROUP BY p.NUM_REFE,p.NUM_PEDI,p.FEC_PAGO,p.FEC_ENTR,p.TIP_CAMB,TIPO,p.CVE_PEDI,p.PAT_AGEN,
						CLAVE_ADUANA,CLAVE_SECCION,VAL_SEGUROS,SEGUROS,FLETES,EMBALAJES,OTROS,DTA_IMP,
						PRV_IMP,CNT_IMP";
		
		$resped = odbc_exec ($odbccasa, $consulta);
		if ($resped == false){
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'XML :: Se genero un error al consultar la informacion de pedimentos. KIA - Valdez&WoodWard [monitor -> utilerias -> documentos_kia]'.odbc_error();
			$respuesta['Error'] = '';
			enviar_notificacion_error_reporte($respuesta['Mensaje'],$consulta);
			return $respuesta;
		}
		try {
			$xmlInfo = new DOMDocument('1.0', 'UTF-8');
			$xmlInfo->xmlStandalone = true;
			$nPrincipal = $xmlInfo->createElement("Importaciones");
			
			while(odbc_fetch_row($resped)){
			
				$NUM_REFE = odbc_result($resped,"NUM_REFE");
				$CVE_PRV_CLI = odbc_result($resped,"CVE_PRV_CLI");
				$DTA_IMP = odbc_result($resped,"DTA_IMP");
				$CNT_IMP = odbc_result($resped,"CNT_IMP");
				$PRV_IMP = odbc_result($resped,"PRV_IMP");
				$OTROS = odbc_result($resped,"OTROS");
				$EMBALAJES = odbc_result($resped,"EMBALAJES");
				$FLETES = odbc_result($resped,"FLETES");
				$SEGUROS = odbc_result($resped,"SEGUROS");
				$VAL_SEGUROS = odbc_result($resped,"VAL_SEGUROS");
				$CLAVE_SECCION = odbc_result($resped,"CLAVE_SECCION");
				$CLAVE_ADUANA = odbc_result($resped,"CLAVE_ADUANA");
				$PAT_AGEN = odbc_result($resped,"PAT_AGEN");
				$CVE_PEDI = odbc_result($resped,"CVE_PEDI");
				$TIPO = odbc_result($resped,"TIPO");
				$TIP_CAMB = odbc_result($resped,"TIP_CAMB");
				$FEC_ENTR = odbc_result($resped,"FEC_ENTR");
				$FEC_PAGO = odbc_result($resped,"FEC_PAGO");
				$CLAVE_ADUANA = odbc_result($resped,"CLAVE_ADUANA");
				$PAT_AGEN = odbc_result($resped,"PAT_AGEN");
				$NUM_PEDI = odbc_result($resped,"NUM_PEDI");
				
				if(trim($CVE_PRV_CLI) != 'ANDE'){
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'ERROR :: El proveedor de la factura es diferente de [BP PRODUCTS NORTH AMERICA INC.]. 
												Es necesario agregar validacion para la descripcion en el XML del nuevo proveedor. 
												Referencia ['.$NUM_REFE.'] KIA - Valdez&WoodWard.
												/***** VERIFICAR COLUMNA pedimentoType DEL EXCEL PECA *****/';
					$respuesta['Error'] = '';
					enviar_notificacion_error_reporte($respuesta['Mensaje'],'');
					return $respuesta;
				}
				$Importacion = $xmlInfo->createElement("importacion");
				$Anio = date('y',strtotime($FEC_PAGO));
				$nPedimento = $Anio.' '.$CLAVE_ADUANA.' '.$PAT_AGEN.' '.$NUM_PEDI;
				$Importacion->setAttribute("pedimento", $nPedimento);
				$Importacion->setAttribute("fecha_pago", date('d/m/Y',strtotime($FEC_PAGO)));
				$Importacion->setAttribute("fecha_entrada", date('d/m/Y',strtotime($FEC_ENTR)));
				$Importacion->setAttribute("tipo_cambio", $TIP_CAMB);
				$Importacion->setAttribute("tipo", $TIPO);
				$Importacion->setAttribute("clave_pedimento", $CVE_PEDI);
				$Importacion->setAttribute("patente", $PAT_AGEN);
				$Importacion->setAttribute("clave_aduana", $CLAVE_ADUANA);
				$Importacion->setAttribute("clave_seccion", $CLAVE_SECCION);
				$Importacion->setAttribute("valseguros", $VAL_SEGUROS);
				$Importacion->setAttribute("seguros", $SEGUROS);
				$Importacion->setAttribute("fletes", $FLETES);
				$Importacion->setAttribute("embalajes", $EMBALAJES);
				$Importacion->setAttribute("otrosincremen", $OTROS);
				$Importacion->setAttribute("previo", $PRV_IMP);
				$Importacion->setAttribute("cnt", $CNT_IMP);
				$Importacion->setAttribute("dta", $DTA_IMP);
				$Importacion->setAttribute("adicional3", "");
				$Importacion->setAttribute("descripcion", "MP");//MP para el proveedor ANDE
				
				$consultaf = "SELECT f.CONS_FACT,f.NUM_FACT, f.FEC_FACT,p.TIP_CAMB,prv.CVE_PROC,f.ICO_FACT,f.MON_FACT,f.EQU_DLLS,cv.E_DOCUMENT
								FROM saaio_factur f 
									INNER JOIN saaio_pedime p ON
										f.NUM_REFE = p.NUM_REFE
									INNER JOIN ctrac_proved prv ON
										f.CVE_PROV = prv.CVE_PRO
									INNER JOIN saaio_cove cv ON
										f.NUM_REFE = cv.NUM_REFE AND
										f.CONS_FACT = cv.CONS_FACT
								WHERE f.NUM_REFE = '".$NUM_REFE."'";
				
				$resfac = odbc_exec ($odbccasa, $consultaf);
				if ($resfac == false){
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'ERROR :: Se genero un error al consultar la informacion de las facturas. KIA - Valdez&WoodWard ['.$NUM_REFE.']'.odbc_error();
					$respuesta['Error'] = '';
					enviar_notificacion_error_reporte($respuesta['Mensaje'],$consultaf);
					return $respuesta;
				}
				while(odbc_fetch_row($resfac)){
					$CONS_FACT = odbc_result($resfac,"CONS_FACT");
					$NUM_FACT = odbc_result($resfac,"NUM_FACT");
					$FEC_FACT = odbc_result($resfac,"FEC_FACT");
					$TIP_CAMB = odbc_result($resfac,"TIP_CAMB");
					$CVE_PROC = odbc_result($resfac,"CVE_PROC");
					$ICO_FACT = odbc_result($resfac,"ICO_FACT");
					$MON_FACT = odbc_result($resfac,"MON_FACT");
					$EQU_DLLS = odbc_result($resfac,"EQU_DLLS");
					$E_DOCUMENT = odbc_result($resfac,"E_DOCUMENT");
					
					$Factura = $xmlInfo->createElement("compra");
					$Factura->setAttribute("factura", $NUM_FACT);
					$Factura->setAttribute("fecha", date('d/m/Y',strtotime($FEC_FACT)));
					$Factura->setAttribute("tipo_cambio", $TIP_CAMB);
					$Factura->setAttribute("clave_proveedor", $CVE_PROC);
					$Factura->setAttribute("clave_incoterm", $ICO_FACT);
					$Factura->setAttribute("clave_moneda", $MON_FACT);
					$Factura->setAttribute("factor_moneda", $EQU_DLLS);
					$Factura->setAttribute("edocument", $E_DOCUMENT);
					$Factura->setAttribute("adicional3", '');
					
					$consultap = "SELECT  pf.CAN_FACT,pf.UNI_FACT,pf.MON_FACT,pf.PORC_IVA,p.CVE_VALO,
											pf.FRACCION,pf.PAI_ORIG,pf.ADVAL,p.FPA_IVA1,f.PES_BRUT,
											CASE WHEN ide.CVE_PERM = 'AF' THEN 'IV' ELSE 'I'END AS CVE_CAT
									FROM SAAIO_FACTUR f
										INNER JOIN SAAIO_PARCONS a ON
											f.NUM_REFE = a.NUM_REFE and
											f.CONS_FACT = a.CONS_FACT
										INNER JOIN SAAIO_FRACCI p ON
											a.NUM_REFE = p.NUM_REFE and
											a.CONS_PART = p.NUM_PART
										LEFT JOIN SAAIO_CONTFRA iva ON
											a.NUM_REFE = iva.NUM_REFE AND
											a.CONS_PART = iva.NUM_PART AND
											iva.CVE_IMPU = 3
										LEFT JOIN SAAIO_CONTFRA igi ON
											a.NUM_REFE = igi.NUM_REFE AND
											a.CONS_PART = igi.NUM_PART AND
											igi.CVE_IMPU = 6
										LEFT JOIN SAAIO_CONTFRA ieps ON
											a.NUM_REFE = ieps.NUM_REFE AND
											a.CONS_PART = ieps.NUM_PART AND
											ieps.CVE_IMPU = 5
										INNER JOIN SAAIO_FACPAR pf ON
											f.NUM_REFE = pf.NUM_REFE AND
											f.CONS_FACT = pf.CONS_FACT
										LEFT JOIN SAAIO_IDEFRA ide ON
											p.NUM_REFE = ide.NUM_REFE AND
											p.NUM_PART = ide.NUM_PART AND
											ide.CVE_PERM = 'AF'
									WHERE a.NUM_REFE = '".$NUM_REFE."' AND f.CONS_FACT = ".$CONS_FACT;
					$respar = odbc_exec ($odbccasa, $consultap);
					if ($respar == false){
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = 'ERROR :: Se genero un error al consultar la informacion de las facturas. KIA - Valdez&WoodWard [Referencia:'.$NUM_REFE.' Factura:'.$CONS_FACT.'][monitor -> utilerias -> documentos_kia]'.odbc_error();
						$respuesta['Error'] = '';
						enviar_notificacion_error_reporte($respuesta['Mensaje'],$consultap);
						return $respuesta;
					}
					while(odbc_fetch_row($respar)){
						
						$CAN_FACT = odbc_result($respar,"CAN_FACT");
						$UNI_FACT = odbc_result($respar,"UNI_FACT");
						$MON_FACT = odbc_result($respar,"MON_FACT");
						$PORC_IVA = odbc_result($respar,"PORC_IVA");
						$FRACCION = odbc_result($respar,"FRACCION");
						$PAI_ORIG = odbc_result($respar,"PAI_ORIG");
						$ADVAL = odbc_result($respar,"ADVAL");
						$FPA_IVA1 = odbc_result($respar,"FPA_IVA1");
						$PES_BRUT = odbc_result($respar,"PES_BRUT");
						$CVE_CAT = odbc_result($respar,"CVE_CAT");
						$CVE_VALO = odbc_result($respar,"CVE_VALO");
						
						$Material = $xmlInfo->createElement("material");
						$Material->setAttribute("clave_material", $NumeroParte);
						$Material->setAttribute("cantidad", $CAN_FACT);
						$Material->setAttribute("clave_unidad", $UNI_FACT);
						$Material->setAttribute("valor_factura", $MON_FACT);
						$Material->setAttribute("valorusd", $MON_FACT);
						$Material->setAttribute("clave_categoria", $CVE_CAT);
						$Material->setAttribute("clave_fraccion", $FRACCION);
						$Material->setAttribute("clave_pais", $PAI_ORIG);
						$Material->setAttribute("clave_tratado", '');//Enviar siempre vacio para GASOLINA
						$Material->setAttribute("clave_ppse", '');//Enviar siempre vacio para GASOLINA
						$Material->setAttribute("clave_regla8va", '');//Enviar siempre vacio para GASOLINA
						$Material->setAttribute("advalorem", $ADVAL);
						$Material->setAttribute("fpiva", $FPA_IVA1);
						$Material->setAttribute("clave_formapago", $FPA_IVA1); /******************  CONFIRMAR QUE ES LA MISMA FORMA DE PAGO QUE EL IVA  **************************/
						$Material->setAttribute("clave_metodo", $CVE_VALO); 
						$Material->setAttribute("iva", $PORC_IVA);
						$Material->setAttribute("clave_almacen", '');//Enviar siempre vacio para GASOLINA
						$Material->setAttribute("peso_gross", $PES_BRUT);
						$Material->setAttribute("serie", '');//Enviar siempre vacio para GASOLINA
						
						$Factura->appendChild($Material);
					}
					$Importacion->appendChild($Factura);
				}
				$nPrincipal->appendChild($Importacion);
			}
			$xmlInfo->appendChild($nPrincipal);
			$NomXML = 'documentos/'.$nPedimento.'.xml';
			$xmlInfo->save($NomXML);
		} catch (Exception $e) {
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al genererar el archivo XML. [Referencia: '.$Referencia.']['.$e->getMessage().'][monitor -> utilerias -> documentos_kia]';
			$respuesta['Error'] = '';
			enviar_notificacion_error_reporte($respuesta['Mensaje'],$consultaf);
			return $respuesta;
		}
		$respuesta['Codigo'] = 1;	
		$respuesta ['NomXML'] = $NomXML;
		$respuesta ['NomPed'] = $nPedimento;
		return $respuesta;
	}