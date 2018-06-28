<?php
include_once('./../../../checklogin.php');
include('./../../../db.php');
if ($loggedIn == false){
	echo '500';
}else{
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {
		$respuesta['Codigo'] = 1;	
		
		$referencia = $_POST['referencia'];
		
		try {
			$conn_casa = new PDO($pdo_casa_cnn, $pdo_casa_usu, $pdo_casa_psw);
			$conn_casa->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$consulta = "SELECT ped.NUM_REFE,ped.ADU_DESP,ped.PAT_AGEN,
								CASE WHEN CARGA.IMP_INCR  IS NULL THEN  0  ELSE 
									CASE WHEN CARGA.MON_INCR = 'USD' THEN 
										CARGA.IMP_INCR /** CARGA.TIP_CAMB*/
									ELSE CASE WHEN CARGA.MON_INCR = 'MXP' THEN 
											CARGA.IMP_INCR / CARGA.TIP_CAMB
										ELSE 
											(CARGA.IMP_INCR * CARGA.EQU_DLLS) /** CARGA.TIP_CAMB*/
										END 
									END 
								END AS CARGA,
								CASE WHEN FLETES.IMP_INCR  IS NULL THEN 0 ELSE 
									CASE WHEN FLETES.MON_INCR = 'USD' THEN
										FLETES.IMP_INCR /** FLETES.TIP_CAMB*/
									ELSE
										CASE WHEN FLETES.MON_INCR = 'MXP' THEN 
											(FLETES.IMP_INCR / FLETES.TIP_CAMB)
										ELSE						
											(FLETES.IMP_INCR * FLETES.EQU_DLLS) /** FLETES.TIP_CAMB*/
										END
									END
								END AS FLETES,
								CASE WHEN SEGUROS.IMP_INCR  IS NULL THEN 0 ELSE 
									CASE WHEN SEGUROS.MON_INCR = 'USD' THEN
										SEGUROS.IMP_INCR /** SEGUROS.TIP_CAMB*/
									ELSE
										CASE WHEN SEGUROS.MON_INCR = 'MXP' THEN 
											(SEGUROS.IMP_INCR / SEGUROS.TIP_CAMB)
										ELSE						
											(SEGUROS.IMP_INCR * SEGUROS.EQU_DLLS) /** SEGUROS.TIP_CAMB*/
										END
									END
								END AS SEGUROS,
								CASE WHEN EMBA.IMP_INCR  IS NULL THEN 0 ELSE 
									CASE WHEN EMBA.MON_INCR = 'USD' THEN
										EMBA.IMP_INCR /** EMBA.TIP_CAMB*/
									ELSE
										CASE WHEN EMBA.MON_INCR = 'MXP' THEN 
											(EMBA.IMP_INCR / EMBA.TIP_CAMB)
										ELSE						
											(EMBA.IMP_INCR * EMBA.EQU_DLLS) /** EMBA.TIP_CAMB*/
										END
									END
								END AS EMBALAJES,
								(CASE WHEN REVER.IMP_INCR  IS NULL THEN 0 ELSE CASE WHEN REVER.MON_INCR = 'USD' THEN (REVER.IMP_INCR /** REVER.TIP_CAMB*/)
									ELSE CASE WHEN REVER.MON_INCR = 'MXP' THEN (REVER.IMP_INCR / REVER.TIP_CAMB) ELSE ((REVER.IMP_INCR * REVER.EQU_DLLS) /** REVER.TIP_CAMB*/) 
								END END END) AS REVERSI,
								(CASE WHEN REGALIAS.IMP_INCR  IS NULL THEN 0 ELSE CASE WHEN REGALIAS.MON_INCR = 'USD' THEN (REGALIAS.IMP_INCR /** REGALIAS.TIP_CAMB*/)
									ELSE CASE WHEN REGALIAS.MON_INCR = 'MXP' THEN (REGALIAS.IMP_INCR / REGALIAS.TIP_CAMB) ELSE ((REGALIAS.IMP_INCR * REGALIAS.EQU_DLLS) /** REGALIAS.TIP_CAMB*/) 
								END END END) AS REGALIAS,
								(CASE WHEN COMI.IMP_INCR  IS NULL THEN 0 ELSE CASE WHEN COMI.MON_INCR = 'USD' THEN (COMI.IMP_INCR /** COMI.TIP_CAMB*/)
									ELSE CASE WHEN COMI.MON_INCR = 'MXP' THEN (COMI.IMP_INCR / COMI.TIP_CAMB) ELSE ((COMI.IMP_INCR * COMI.EQU_DLLS) /** COMI.TIP_CAMB*/) 
								END END END) AS COMI,
								(CASE WHEN MATERIAL.IMP_INCR  IS NULL THEN 0 ELSE CASE WHEN MATERIAL.MON_INCR = 'USD' THEN (MATERIAL.IMP_INCR /** MATERIAL.TIP_CAMB*/)
									ELSE CASE WHEN MATERIAL.MON_INCR = 'MXP' THEN (MATERIAL.IMP_INCR / MATERIAL.TIP_CAMB) ELSE ((MATERIAL.IMP_INCR * MATERIAL.EQU_DLLS) /** MATERIAL.TIP_CAMB*/) 
								END END END) AS MATERIAL,
								(CASE WHEN TECNO.IMP_INCR  IS NULL THEN 0 ELSE CASE WHEN TECNO.MON_INCR = 'USD' THEN (TECNO.IMP_INCR /** TECNO.TIP_CAMB*/)
									ELSE CASE WHEN TECNO.MON_INCR = 'MXP' THEN (TECNO.IMP_INCR / TECNO.TIP_CAMB) ELSE ((TECNO.IMP_INCR * TECNO.EQU_DLLS) /** TECNO.TIP_CAMB*/)
								END END END) AS TECNO
					FROM saaio_pedime as ped
								LEFT JOIN vsaaio_increm FLETES ON
								   ped.NUM_REFE = FLETES.NUM_REFE AND
									 FLETES.CVE_INCR = 1
								LEFT JOIN vsaaio_increm SEGUROS ON
								   ped.NUM_REFE = SEGUROS.NUM_REFE AND
									 SEGUROS.CVE_INCR = 2
								LEFT JOIN vsaaio_increm EMBA ON
								   ped.NUM_REFE = EMBA.NUM_REFE AND
									 EMBA.CVE_INCR = 3
								LEFT JOIN vsaaio_increm CARGA ON
									 ped.NUM_REFE = CARGA.NUM_REFE AND
									 CARGA.CVE_INCR = 4
								LEFT JOIN vsaaio_increm REVER ON
									 ped.NUM_REFE = REVER.NUM_REFE AND
									 REVER.CVE_INCR = 5
								LEFT JOIN vsaaio_increm REGALIAS ON
									 ped.NUM_REFE = REGALIAS.NUM_REFE AND
									 REGALIAS.CVE_INCR = 6
								LEFT JOIN vsaaio_increm COMI ON
									 ped.NUM_REFE = COMI.NUM_REFE AND
									 COMI.CVE_INCR = 7
								LEFT JOIN vsaaio_increm MATERIAL ON
									 ped.NUM_REFE = MATERIAL.NUM_REFE AND
									 MATERIAL.CVE_INCR = 8
								LEFT JOIN vsaaio_increm TECNO ON
									 ped.NUM_REFE = TECNO.NUM_REFE AND
									 TECNO.CVE_INCR = 9
				WHERE ped.NUM_REFE = '".$referencia."'";
					
			$resp = $conn_casa->query($consulta)->fetchAll();
			
			if(count($resp) > 0){
				foreach ($resp as $row) {
					$respuesta['Codigo'] = 1;
					
					$respuesta['Aduana'] = utf8_encode ($row['ADU_DESP']);
					$respuesta['Patente'] = utf8_encode ($row['PAT_AGEN']);
					$respuesta['CARGA'] = utf8_encode ($row['CARGA']);
					$respuesta['FLETES'] = utf8_encode ($row['FLETES']);
					$respuesta['SEGUROS'] = utf8_encode ($row['SEGUROS']);
					$respuesta['EMBALAJES'] = utf8_encode ($row['EMBALAJES']);
					$respuesta['REVERSI'] = utf8_encode ($row['REVERSI']);
					$respuesta['REGALIAS'] = utf8_encode ($row['REGALIAS']);
					$respuesta['COMI'] = utf8_encode ($row['COMI']);
					$respuesta['MATERIAL'] = utf8_encode ($row['MATERIAL']);
					$respuesta['TECNO'] = utf8_encode ($row['TECNO']);
					
				}
				//Consultar datos de los proveedores facturas
				$consulta = "SELECT ped.NUM_REFE,
								cli.NOM_IMP AS NOM_CLI,
								('# ' || cli.NOE_IMP ||CASE WHEN cli.NOI_IMP IS NULL THEN '' ELSE ' INT' || cli.NOI_IMP END || ' ' || cli.DIR_IMP ) AS DIR_CLI,
								(cli.POB_IMP || ', '||est.NOM_EFED|| ', '||cli.PAI_IMP|| '. '||cli.CP_IMP) AS CD_CLI,
								('RFC. '||cli.RFC_IMP) AS RFC_CLI,
								
								CASE WHEN ped.IMP_EXPO = '1' THEN 
									pro.CVE_PRO
								ELSE 
									des.CVE_PRO
								END AS ID_PROV,
								
								CASE WHEN ped.IMP_EXPO = '1' THEN 
									pro.NOM_PRO
								ELSE 
									des.NOM_PRO
								END AS NOM_PROV,
								CASE WHEN ped.IMP_EXPO = '1' THEN 
									(pro.NOE_PRO ||CASE WHEN pro.NOI_PRO IS NULL THEN '' ELSE ' INT' || pro.NOI_PRO END || ' ' || pro.DIR_PRO)
								ELSE 
									(des.NOE_PRO ||CASE WHEN des.NOI_PRO IS NULL THEN '' ELSE ' INT' || des.NOI_PRO END || ' ' || des.DIR_PRO)
								END AS DIR_PROV,
								CASE WHEN ped.IMP_EXPO = '1' THEN 
									(pro.POB_PRO || ', '||est_prov.NOM_EFED|| ', '||pro.PAI_PRO|| '. '||pro.ZIP_PRO)
								ELSE 
									(des.POB_PRO || ', '||est_dest.NOM_EFED|| ', '||des.PAI_PRO|| '. '||des.ZIP_PRO)
								END AS CD_PROV,
								CASE WHEN ped.IMP_EXPO = '1' THEN 
									('TAX ID. '||pro.TAX_PRO) 
								ELSE 
									('TAX ID. '||des.TAX_PRO)
								END AS RFC_PROV
							FROM saaio_pedime as ped
								INNER JOIN CTRAC_CLIENT AS cli ON
									ped.CVE_IMPO = cli.CVE_IMP
								LEFT JOIN CTARC_ENTFED AS est ON
									cli.EFE_IMP = est.CVE_EFED AND
									cli.PAI_IMP = est.CVE_PAIS
								INNER JOIN saaio_factur AS fac ON
									 ped.NUM_REFE = fac.NUM_REFE
								LEFT JOIN ctrac_proved AS pro ON
									 fac.CVE_PROV = pro.CVE_PRO
								LEFT JOIN CTARC_ENTFED AS est_prov ON
									pro.EFE_PRO = est_prov.CVE_EFED AND
									pro.PAI_PRO = est_prov.CVE_PAIS
								LEFT JOIN ctrac_destin AS des ON
									 fac.CVE_PROV = des.CVE_PRO
								LEFT JOIN CTARC_ENTFED AS est_dest ON
									des.EFE_PRO = est_dest.CVE_EFED AND
									des.PAI_PRO = est_dest.CVE_PAIS
							WHERE ped.NUM_REFE = '".$referencia."'
							GROUP BY ped.NUM_REFE,NOM_CLI,DIR_CLI,CD_CLI,RFC_CLI,ID_PROV,NOM_PROV,DIR_PROV,CD_PROV,RFC_PROV
							ORDER BY NOM_PROV";
				//error_log($consulta);
				$respProv = $conn_casa->query($consulta)->fetchAll();
				//error_log('Trae consulta |'.count($respFac));
				if(count($respProv) > 0){
					$nItem = 0;
					$aProveedores = array();
					foreach ($respProv as $row) {
						if($nItem == 0 ){
							$respuesta['nom_cli'] = utf8_encode ($row['NOM_CLI']);
							$respuesta['dir_cli'] = utf8_encode ($row['DIR_CLI']);
							$respuesta['cd_cli'] = utf8_encode ($row['CD_CLI']);
							$respuesta['rfc_cli'] = utf8_encode ($row['RFC_CLI']);
							$nItem = $nItem + 1;
						}
						//Facturas del proveedor
						
						$cveProv = utf8_encode ($row['ID_PROV']);
						//error_log("ID_PROV:".$cveProv);
						
						$consulta = "SELECT distinct fac.NUM_FACT
									FROM saaio_pedime as ped
										INNER JOIN saaio_factur AS fac ON
											 ped.NUM_REFE = fac.NUM_REFE
									WHERE ped.NUM_REFE = '".$referencia."' AND fac.CVE_PROV = '".$cveProv."'";
						
						$respFac = $conn_casa->query($consulta)->fetchAll();
						
						$sFacturas = '';
						foreach ($respFac as $rowfac) {
							$sFacturas .= ($sFacturas != '' ? ',' : '' ).utf8_encode ($rowfac['NUM_FACT']);
							error_log(utf8_encode ('FACTURA:'.$rowfac['NUM_FACT']));
						}
						$Proveedor = array(utf8_encode ($row['NOM_PROV']),utf8_encode ($row['DIR_PROV']),utf8_encode ($row['CD_PROV']),utf8_encode ($row['RFC_PROV']),$sFacturas);//,utf8_encode ($row['ICO_FAC'])
						array_push($aProveedores,$Proveedor);
					}
					$respuesta['aProveedores'] =  $aProveedores;
					
					$consulta = "SELECT distinct fac.ICO_FACT
								FROM saaio_pedime as ped
									INNER JOIN saaio_factur AS fac ON
										 ped.NUM_REFE = fac.NUM_REFE
								WHERE ped.NUM_REFE = '".$referencia."'";
								
					$respICO = $conn_casa->query($consulta)->fetchAll();
					$aIcoterms = array();
					foreach ($respICO as $row) {
						$Icoterm = array(utf8_encode ($row['ICO_FACT']));
						array_push($aIcoterms,$Icoterm);
					}
					$respuesta['aIcoterms'] =  $aIcoterms;
					
				}else{
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = "La referencia no cuenta con facturas. [db.CASA]"; 
					$respuesta['Error'] = '';
				}
			}else{
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = "La referencia no existe en la base de datos. [db.CASA]"; 
				$respuesta['Error'] = '';
			}
			$conn_casa = null;
		} catch (PDOException $e) {
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = "¡Error en la consulta!: "; 
			$respuesta['Error'] = ' ['.$e->getMessage().']';
		}
	}else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}



	
	
	
	