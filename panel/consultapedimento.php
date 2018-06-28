<?php
include_once('./../checklogin.php');
include('./../connect_casa.php');

if($loggedIn == false){ header("Location: ./../login.php"); }
$pedimentopat=$_POST['pedimento'];
$aduana=substr($pedimentopat,0,3);
$numpedi=substr($pedimentopat,-7);
$patente=substr($pedimentopat,4,4);
$query = "SELECT num_refe,imp_expo,adu_desp,extract(day from FEC_ENTR)||'/'||extract(month from FEC_ENTR)||'/'||extract(year from FEC_ENTR) as fec_entr,cve_pedi,fir_reme,val_dlls,val_come,extract(day from fec_pago)||'/'||extract(month from fec_pago)||'/'||extract(year from fec_pago) as fec_pago,fir_pago FROM SAAIO_PEDIME where adu_desp='".$aduana."' and num_pedi='".$numpedi."' and pat_agen='".$patente."'";
$result = odbc_exec ($odbccasa, $query);
if ($result!=false){
	if(odbc_num_rows($result)<=0){
		echo "<br><center><p>El pedimento no se encuentra en la base de datos de pedimentos ".$query."<p><center>";
		exit;
	}
	else{
		//echo odbc_result_all($result,"border=1");
		$respuesta='<div class="panel panel-default"><div class="panel-heading">Datos generales</div>';
		$respuesta.= '<div class="table-responsive"><table class="table"><tr><th>Pedimento</th><th>Operacion</th><th>Tipo</th><th>Aduana</th><th>Fecha entrada</th><th>Regimen</th><th>Valor Dlls.</th><th>Valor Comercial</th><th>Fecha pago</th><th>Firma pago</th></tr>';
		$firpago='';
		while(odbc_fetch_row($result)){
			$respuesta.='<tr>';
			$respuesta.='<td>'.$pedimentopat.'</td>';
			$referencia=odbc_result($result,"num_refe");
			$operacion = (odbc_result($result,"imp_expo")==1 ? "IMPO" : "EXPO");
			$respuesta.='<td>'.$operacion.'</td>';
			$tipo = (odbc_result($result,"fir_reme")=='' ? "DEFINITIVO" : "CONSOLIDADO");
			$respuesta.='<td>'.$tipo.'</td>';
			$aduana=odbc_result($result,"adu_desp");
			$respuesta.='<td>'.$aduana.'</td>';
			$respuesta.='<td>'.odbc_result($result,"fec_entr").'</td>';
			$respuesta.='<td>'.odbc_result($result,"cve_pedi").'</td>';
			$respuesta.='<td>'.odbc_result($result,"val_dlls").'</td>';
			$respuesta.='<td>'.odbc_result($result,"val_come").'</td>';
			$respuesta.='<td>'.odbc_result($result,"fec_pago").'</td>';
			$firpago=odbc_result($result,"fir_pago");
			$respuesta.='<td>'.odbc_result($result,"fir_pago").'</td>';
			$respuesta.='</tr>';
		}
		$respuesta.='</table></div>';
		$respuesta.='<div class="panel-heading">Partidas</div>';
		$query = "SELECT a.NUM_REFE, a.NUM_PART, a.FRACCION, a.DES_MERC, a.VAL_NORF, a.CAN_FACT
			FROM SAAIO_FRACCI a
			where a.NUM_REFE='".$referencia."'";
		$result = odbc_exec ($odbccasa, $query);
		if ($result!=false){
			$respuesta.= '<div class="table-responsive"><table class="table"><tr><th>#</th><th>Fraccion</th><th>Descripcion</th><th>Valor Aduana</th><th>Cantidad</th><th colspan="3"><center>Impuestos</center></th></tr>';
			while(odbc_fetch_row($result)){
				$respuesta.='<tr>';
				$partida=odbc_result($result,"NUM_PART");
				$respuesta.='<td>'.$partida.'</td>';
				$respuesta.='<td>'.odbc_result($result,"FRACCION").'</td>';
				$respuesta.='<td>'.odbc_result($result,"DES_MERC").'</td>';
				$respuesta.='<td>'.odbc_result($result,"VAL_NORF").'</td>';
				$respuesta.='<td>'.odbc_result($result,"CAN_FACT").'</td>';
				$respuesta.='<td>';
				$queryi = "SELECT a.CVE_IMPU,a.VAL_TASA,a.TOT_IMPU
					FROM SAAIO_CONTFRA a 
					where a.NUM_REFE='".$referencia."' AND a.NUM_PART='".$partida."'";
				$resulti = odbc_exec ($odbccasa, $queryi);
				if ($resulti!=false){
					$respuesta.= '<table class="table"><tr><th>Impuesto</th><th>Tasa</th><th>Importe</th></tr>';
					while(odbc_fetch_row($resulti)){
						$respuesta.='<tr>';
						if (odbc_result($resulti,"CVE_IMPU")==6)
							$impuesto =  "IGI";
						elseif(odbc_result($resulti,"CVE_IMPU")==3)
							$impuesto =  "IVA";
						else
							$impuesto =  odbc_result($resulti,"CVE_IMPU");
						$respuesta.='<td>'.$impuesto.'</td>';
						$respuesta.='<td>'.odbc_result($resulti,"VAL_TASA").'</td>';
						$respuesta.='<td>'.odbc_result($resulti,"TOT_IMPU").'</td>';
						$respuesta.='</tr>';
					}
					$respuesta.='</table>';
				}
				$respuesta.='</td>';
				$respuesta.='</tr>';
			}
			$respuesta.='</table></div>';
		}
		$respuesta.='<div class="panel-heading">Archivos</div>';
		//if ($firpago!=''){
			$respuesta.= '<div class="table-responsive"><table class="table"><tr><th>Pedimento</th><th>Pedimento XML(*)</th><th>Reporte COVEs</th><th>COVEs XMLs(*)</th><th>Acuses COVEs</th><th>Reporte Edocuments</th><th>HC</th><th>HC Anexo</th><th>MV</th></tr>';
			$linkp = (file_exists('D:\pedimentos2009\\'.$referencia.'.pdf') ? "<a href='http://www.delbravoweb.com:8091/pedimentos2009/".$referencia.".pdf' target=_blank ><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span></a>" : "No disponible");
			$linkpsim = (file_exists('D:\pedimentos2009\\'.$referencia.'_SIMP.pdf') ? "<a href='http://www.delbravoweb.com:8091/pedimentos2009/".$referencia."_SIMP.pdf' target=_blank ><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span></a>" : "No disponible");
			$linkhc = (file_exists('D:\pedimentos2009\hcmv\\'. str_replace('-','_',$referencia).'_Hojascalculo.pdf') ? "<a href='http://www.delbravoweb.com:8091/pedimentos2009/hcmv/".str_replace('-','_',$referencia)."_Hojascalculo.pdf' target=_blank ><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span></a>" : "No disponible");
			$linkahc = (file_exists('D:\pedimentos2009\hcmv\\'. str_replace('-','_',$referencia).'_Anexohojacalculo.pdf') ? "<a href='http://www.delbravoweb.com:8091/pedimentos2009/hcmv/".str_replace('-','_',$referencia)."_Anexohojacalculo.pdf' target=_blank ><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span></a>" : "No disponible");
			$linkmv = (file_exists('D:\pedimentos2009\hcmv\\'. str_replace('-','_',$referencia).'_Manifestacion.pdf') ? "<a href='http://www.delbravoweb.com:8091/pedimentos2009/hcmv/".str_replace('-','_',$referencia)."_Manifestacion.pdf' target=_blank ><span class='glyphicon glyphicon-eye-open' aria-hidden='true'></span></a>" : "No disponible");
			$respuesta.='<tr>';
			$respuesta.='<td><center>'.$linkp.'</center></td>';
			$respuesta.='<td id="pedxml"></td>';
			$respuesta.='<td><center><a href="javascript:void(0);" onclick="javascript:window.open(\'reportecove.php?ref='.$referencia.'\',\'_blank\');"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a></center></td>';
			$respuesta.='<td id="covexml"></td>';
			$respuesta.='<td><center><a href="javascript:void(0);" onclick="javascript:window.open(\'acusescove.php?ref='.$referencia.'\',\'_blank\');"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></a></center></td>';
			$respuesta.='<td><center><a href="javascript:void(0);" onclick="javascript:window.open(\'reporteedocument.php?ref='.$referencia.'\',\'_blank\');"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a></center></td>';
			$respuesta.='<td><center>'.$linkhc.'</center></td>';
			$respuesta.='<td><center>'.$linkahc.'</center></td>';
			$respuesta.='<td><center>'.$linkmv.'</center></td>';
			$respuesta.='</tr>';
			$respuesta.='</table></div>';
		/*}else{
			$respuesta.="<center><p>Para visualizar esta información se requiere que el pedimento este pagado <p></center>";
		}*/
		$respuesta.='<div class="panel-heading">Estatus(*)</div><div id="estatusped"></div>';
		//if ($firpago!=''){
			/*$respuesta.='Fecha de validacion:<br>';
			$respuesta.='Prevalidador:<br>';
			$respuesta.= '<table class="table"><tr><th>Operacion</th><th>Resultado</th></tr>';
			$respuesta.='</table>';*/
			//$respuesta.='<a href="javascript:void(0);" onclick="javascript:window.open(\'estatus.php?ped='.$numpedi.'&aduana='.$aduana.'&pat='.$patente.'\',\'_blank\');"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a>';
		/*}else{
			$respuesta.="<center><p>Para visualizar esta información se requiere que el pedimento este pagado <p></center>";
		}*/
		$respuesta.='</div>';
		$respuesta.='<div class="alert alert-warning" role="alert">(*)Consultas realizadas directamente a los servidores de ventanilla unica</div>';
		echo $respuesta;
	}
}
else{
	echo "<br><center><p>Error al realizar la consulta a la base de datos de pedimentos: ".$query." <p></center>";
	exit;
}
?>