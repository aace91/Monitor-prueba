<?php
	require_once("../bower_components/nusoap/src/nusoap.php");
	include('../bower_components/PHPMailer/PHPMailerAutoload.php');
    //crear server
    $server = new soap_server;
    // initialize WSDL con nombre de la funcion
    $server->configureWSDL( 'enviarcuentas' , 'urn:enviarcuentas' );

    $server->wsdl->schemaTargetNamespace = 'urn:enviarcuentas';

    $server->wsdl->addComplexType(
        'RespuestaObject',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'Codigo' => array('name'=>'Codigo','type'=>'xsd:int'),
			'Mensaje' => array('name'=>'Mensaje','type'=>'xsd:string')
        )
    );


    $server->register(
        'enviarcuentas',
        array(
            'usuario' => 'xsd:string',
            'password' => 'xsd:string',
			'cuentas' => 'xsd:string',
			'correos' => 'xsd:string',
			'omite_cliente' => 'xsd:string'
            ),
        array('return'=>'tns:RespuestaObject'),
        'enviarcuentaswsdl',
        'urn:enviarcuentaswsdl',
        'rpc',
        'encoded',
        'Envia cuentas');

	$server->register(
        'forzarpendientes',
        array(
            'usuario' => 'xsd:string',
            'password' => 'xsd:string'
            ),
        array('return'=>'tns:RespuestaObject'),
        'forzarpendienteswsdl',
        'urn:forzarpendienteswsdl',
        'rpc',
        'encoded',
        'Forzar envio de cuentas pendientes');

	$server->register(
        'enviarlistado',
        array(
            'usuario' => 'xsd:string',
            'password' => 'xsd:string',
			'correos' => 'xsd:string'
            ),
        array('return'=>'tns:RespuestaObject'),
        'enviarlistadowsdl',
        'urn:enviarlistadowsdl',
        'rpc',
        'encoded',
        'Enviar listado de cuentas pendientes');

	function enviarlistado($usuario,$password,$correos){
		include('../db.php');
		include('../url_archivos.php');
		$respuesta=array(3);
		$respuesta['Codigo']=0;
		$respuesta['Mensaje']="Error interno, contacte al administrador";
		$db = mysqli_connect($mysqlserver2_sab07,$mysqluser_sab07,$mysqlpass_sab07,$mysqldb_sab07,$mysqlport_sab07);
		if (!$db) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al conectarse a la bd";
			return $respuesta;
		}
		$query="
			Select
				a.tipo_mov as tipomov,
				a.no_mov as no_mov,
				a.no_cte as no_cte,
				a.trafico as trafico,
				DATE_FORMAT(a.fecha,'%d/%m/%Y') as fecha,
				b.nombre as ncliente,
				count(c.correo) as tcorreos,
				b.refidentica as ridentica
			from
				aacgmex as a
			inner join aacte as b on a.no_cte=b.no_cte
			left join correos as c on a.no_cte=c.no_cte
			where
				a.fecha_enviocliente is null
			and b.enviarfac=1
			and a.fecha < CURDATE()
			and DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= a.fecha
			and cancelada=0
			and a.firmada=1
			GROUP BY
				a.no_mov
			order by
				a.fecha,
				a.no_cte";
		$result = mysqli_query($db,$query);
		if(!$result){
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al realizar consulta de pedimentos: ".$db->error;
			return $respuesta;
		}
		$nfilas = $result->num_rows;
		if ($nfilas>0){
			$bcorreo='<html><HEAD></HEAD><body>A continuaci&oacute;n se listan las cuentas de gastos que estan pendientes de enviar de los ultimos 30 dias<br><br>';
			$bcorreo=$bcorreo.'<table border="1" cellspacing="0" cellpadding="0" bordercolor="0B1248" width="100%"><tr><td nowrap align="center" bgcolor="0D76C7">Factura</td><td nowrap align="center" bgcolor="0D76C7">Trafico</td><td nowrap align="center" bgcolor="0D76C7">Fecha</td><td nowrap align="center" bgcolor="0D76C7"># Cliente</td><td nowrap align="center" bgcolor="0D76C7">Nombre Cliente</td><td nowrap align="center" bgcolor="0D76C7">Anexos</td><td nowrap align="center" bgcolor="0D76C7">Correo</td></tr>';
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$trafico=rtrim($row['trafico']);
				$trafico=str_replace('/','_',$trafico);
				$bcorreo=$bcorreo.'<tr>';
				if (file_exists($dir_archivos_anexos_gab.$trafico.'.pdf')==false){
					if ($row["ridentica"]==0){
						$pos=strpos($trafico, '-');
						if ($pos === false) {
							$trafico2=substr($trafico,0,2).'-'.substr($trafico,2);
						}
						else{
							$trafico2=str_replace('-','',$trafico);
						}
						if (file_exists($dir_archivos_anexos_gab.$trafico2.'.pdf')){
							$anexos='SI';
						}
						else{
							$anexos='<font color="red">FALTAN ANEXOS</font> ';
						}
					}
				}
				else{
					$anexos='SI';
				}
				if ($row["tcorreos"]<1){
					$correo='<font color="red">NINGUN CORREO REGISTRADO</font>';
				}
				else{
					$correo='SI';
				}
				$nomov=$row["no_mov"];
				$bcorreo=$bcorreo.'<td><center>'.$nomov.'</center></td>';
				$bcorreo=$bcorreo.'<td><center>'.$trafico.'</center></td>';
				$bcorreo=$bcorreo.'<td><center>'.$row["fecha"].'</center></td>';
				$bcorreo=$bcorreo.'<td><center>'.$row["no_cte"].'</center></td>';
				$bcorreo=$bcorreo.'<td><center>'.mb_convert_encoding($row["ncliente"], "UTF-8", "auto").'</center></td>';
				$bcorreo=$bcorreo.'<td><center>'.$anexos.'</center></td>';
				$bcorreo=$bcorreo.'<td><center>'.$correo.'</center></td>';
				$bcorreo=$bcorreo.'</tr>';
			}
			$bcorreo=$bcorreo.'</table><br><br>Este es un aviso generado automaticamente, favor de no responder a el. </body></html>';
			$para=explode(";",$correos);
			$cc=array();
			$bcc=array("abisaicruz@delbravo.com");
			$titulo='Listado de cuentas pendientes por enviar';
			$mail = new PHPMailer();
			try {
				$mail->IsSMTP();
				$mail->SMTPAuth = true;
				$mail->CharSet = 'UTF-8';
				//$mail->SMTPSecure = "tls";
				$mail->Host = "mail.delbravo.com"; // SMTP a utilizar. Por ej. smtp.elserver.com
				$mail->Username = "facturacionelectronica@delbravo.com"; // Correo completo a utilizar
				$mail->Password = "facelec01"; // Contraseña
				$mail->Port = "587"; // Puerto a utilizar
				//Con estas pocas líneas iniciamos una conexión con el SMTP. Lo que ahora deberíamos hacer, es configurar el mensaje a enviar, el //From, etc.
				$mail->From = "facturacionelectronica@delbravo.com"; // Desde donde enviamos (Para mostrar)
				$mail->FromName = "facturacionelectronica@delbravo.com";
				//Estas dos líneas, cumplirían la función de encabezado (En mail() usado de esta forma: “From: Nombre <correo@dominio.com>”) de //correo.
				if (count($para)>0){
					foreach($para as $correo){
						$mail->addAddress($correo);
					}
				}
				if (count($cc)>0){
					foreach($cc as $correo){
						$mail->addCC($correo);
					}
				}
				if (count($bcc)>0){
					foreach($bcc as $correo){
						$mail->addBCC($correo);
					}
				}
				$mail->IsHTML(true); // El correo se envía como HTML
				$mail->Subject = $titulo; // Este es el titulo del email.
				$mail->Body = $bcorreo; // Mensaje a enviar
				$exito = $mail->Send(); // Envía el correo.
				if(!$exito){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Mailer Error: ' . $mail->ErrorInfo;
					return $respuesta;
				}
			} catch (Exception $e) {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Mailer Error: ' . $mail->ErrorInfo;
				return $respuesta;
			}
			$respuesta['Codigo']=1;
			$respuesta['Mensaje']='Correo enviado';
			return $respuesta;
			/*$rn = "\r\n";
			// Para enviar un correo HTML mail, la cabecera Content-type debe fijarse
			$cabeceras  = 'MIME-Version: 1.0' . $rn;
			$cabeceras .= 'Content-type: text/html; charset=utf-8' . $rn;
			// Cabeceras adicionales
			//$cabeceras .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . $rn;
			$cabeceras .= 'From: facturacionelectronica@delbravo.com' . $rn;
			// Mail it
			if ($para!=''){
				mail($para, $titulo, $bcorreo, $cabeceras);
				$respuesta['Codigo']=1;
				$respuesta['Mensaje']='Correo Enviado';
				return $respuesta;
			}
			else{
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='CORREO NO ENVIADO, el campo del destinatario esta vacio';
				return $respuesta;
			}*/
		}
	}

	function forzarpendientes($usuario,$password){
		include('../db.php');
		include('../url_archivos.php');
		$respuesta=array(3);
		$respuesta['Codigo']=0;
		$respuesta['Mensaje']="Error interno, contacte al administrador";
		$db = mysqli_connect($mysqlserver2_sab07,$mysqluser_sab07,$mysqlpass_sab07,$mysqldb_sab07,$mysqlport_sab07);
		if (!$db) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al conectarse a la bd";
			return $respuesta;
		}
		$query='
			SELECT
				a.tipo_mov AS tipomov,
				a.no_mov AS no_mov,
				a.no_cte AS no_cte,
				a.trafico as trafico,
				c.buzonfiscal as buzon
			FROM
				aacgmex AS a
			LEFT JOIN (
				SELECT
					no_cte,
					count(correo) AS total_correos
				FROM
					correos
				GROUP BY
					no_cte
			) AS b ON a.no_cte = b.no_cte
			LEFT JOIN
				aacte as c on a.no_cte=c.no_cte
			WHERE
				a.fecha_enviocliente IS NULL
			AND a.cancelada=0
			AND a.firmada=1
			AND b.total_correos > 0
			ORDER BY
				a.no_cte,
				a.fecha';
		$result = mysqli_query($db,$query);
		$cuentas = array();
		$cliente_actual = NULL;
		$total_cuentas = $result->num_rows;
		$cuentas_enviadas = 0;
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			if (file_exists($dir_archivos_anexos_gab.$row['trafico'].'.pdf')==false){
				continue;
			}
			$cuentas_enviadas++;
			if ($cliente_actual == NULL){
				$cliente_actual = $row['no_cte'];
			}
			if ($cliente_actual==$row['no_cte']){
				array_push($cuentas,' '.$row['no_mov']);
				if($row['buzon'] == 1){
					$envio = enviarcuentas('admin','r0117c',json_encode($cuentas),'','false', 'true');
					if($envio['Codigo']==-1){
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']=$envio['Mensaje'];
						return $respuesta;
					}
					$cuentas = array();
					$cliente_actual = NULL;
				}
			}else{
				$envio = enviarcuentas('admin','r0117c',json_encode($cuentas),'','false');
				if($envio['Codigo']==-1){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']=$envio['Mensaje'];
					return $respuesta;
				}
				$cuentas = array();
				array_push($cuentas,' '.$row['no_mov']);
				$cliente_actual = $row['no_cte'];
			}
		}
		if(count($cuentas)>0){
			$envio = enviarcuentas('admin','r0117c',json_encode($cuentas),'','false');
			if($envio['Codigo']==-1){
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']=$envio['Mensaje'];
				return $respuesta;
			}
		}
		$respuesta['Codigo']=1;
		$respuesta['Mensaje']=$cuentas_enviadas.' cuentas enviadas con exito';
		return $respuesta;
	}

    //Metodo de envio cuentas especificas
    function enviarcuentas($usuario,$password,$cuentas,$correos,$omite_cliente,$buzon = 'false')
    {
		include('../db.php');
		include('../url_archivos.php');

		$respuesta=array(3);
		$respuesta['Codigo']=0;
		$respuesta['Mensaje']="Error interno, contacte al administrador";
		//$respuesta=array('CodigoRespuesta'=>1,'Mensaje'=>'Hola'.$cuentas);
		$ip1="www.delbravoweb.com";
		$ip="$ip1:8092";
		$query='';
		$bcorreo='<html><HEAD></HEAD><body>A continuaci&oacute;n se lista las cuentas de gastos que se han generado recientemente <br><br>';
		$bcorreo=$bcorreo.'<table border="1" cellspacing="0" cellpadding="0" bordercolor="0B1248" width="100%"><tr><td nowrap align="center" bgcolor="0D76C7">Factura</td><td nowrap align="center" bgcolor="0D76C7">Trafico</td><td nowrap align="center" bgcolor="0D76C7">Fecha</td><td nowrap align="center" bgcolor="0D76C7">XML</td><td nowrap align="center" bgcolor="0D76C7">PDF</td><td nowrap align="center" bgcolor="0D76C7">Pedimentos</td><td nowrap align="center" bgcolor="0D76C7">PDFs COVEs</td><td nowrap align="center" bgcolor="0D76C7">Edocuments</td><td nowrap align="center" bgcolor="0D76C7">HC</td><td nowrap align="center" bgcolor="0D76C7">Anexo HC</td><td nowrap align="center" bgcolor="0D76C7">MV</td><td nowrap align="center" bgcolor="0D76C7">Anexos</td></tr>';
		$vuelta=0;
		$enviadas=array();
		$cuentasenv="";
		$cuentas=json_decode($cuentas);
		foreach( $cuentas as $x=>$y) {
			$tipomov=(substr($y,0,1)=='R' ? "R" : "I");
			$nomov=substr($y,1,strlen($y)-1);
			$db = mysqli_connect($mysqlserver2_sab07,$mysqluser_sab07,$mysqlpass_sab07,$mysqldb_sab07,$mysqlport_sab07);
			if (!$db) {
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']="Error al conectarse a la bd";
				return $respuesta;
			}
			if ($tipomov=='I'){
				$query='
					Select
						a.tipo_mov as tipomov,
						a.no_mov as no_mov,
						a.no_cte as no_cte,
						a.trafico as trafico,
						DATE_FORMAT(a.fecha,"%d/%m/%Y") as fecha,
						b.nombre as ncliente,
						b.refidentica as ridentica,
						a.aduana as aduana,
						a.pedimento as pedimento,
						a.patente as patente
					from
						aacgmex as a
					left join aacte as b on a.no_cte=b.no_cte
					where
						a.no_banco=1
					and	a.no_mov='.$nomov.'
					and a.tipo_mov="'.$tipomov.'"
					order by
						a.no_cte,
						a.fecha';
			}else{
				//Consulta de remisiones
			}
			$result = mysqli_query($db,$query);
			$row = $result->fetch_array(MYSQLI_ASSOC);
			$nomcliente=htmlspecialchars ($row['ncliente']);
			$numcliente=$row['no_cte'];
			$bcorreo=$bcorreo.'<tr><td><center>'.$row['no_mov'].'</center></td>';
			$bcorreo=$bcorreo.'<td><center>'.$row['trafico'].'</center></td>';
			$bcorreo=$bcorreo.'<td><center>'.$row['fecha'].'</center></td>';
			if ($row['tipomov']=='R'){
				$bcorreo=$bcorreo.'<td><center>Remisi&oacute;n</center></td>';
				$bcorreo=$bcorreo.'<td><center>Remisi&oacute;n</center></td>';
			}
			else{
				$bcorreo=$bcorreo.'<td><center><a href="http://'.$ip1.'/sii/admin/dxml_mail.php?nxml='.rtrim($row['no_mov']).'.xml">Descargar</a></center></td>';
				$bcorreo=$bcorreo.'<td><center><a href="http://'.$ip1.'/sii/admin/dpdf_mail.php?npdf='.rtrim($row['no_mov']).'.pdf">Descargar</a></center></td>';
			}
			if ($row['aduana']=='240' or $row['aduana']=='800'){
				$dsn = "cnxpedimentos";
				//debe ser de sistema no de usuario
				$usuarioc = "";
				$clavec="";
				//realizamos la conexion mediante odbc
				$odbccasa=odbc_connect($dsn, $usuarioc, $clavec);
				if (!$odbccasa){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error al conectarse a la base de datos de pedimentos [CASA].";
					return $respuesta;
				}
				$pQuery = "SELECT num_refe FROM SAAIO_PEDIME where adu_desp='".$row['aduana']."' and num_pedi='".rtrim($row['pedimento'])."' and pat_agen='".$row['patente']."'";
				$pResult = odbc_exec ($odbccasa, $pQuery);
				if ($pResult==false){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error al realizar la consulta del pedimento de CASA.";
					return $respuesta;
				}
				if(odbc_num_rows($pResult)>0){
					while(odbc_fetch_row($pResult)){
						$ref_ped_encabezado=odbc_result($pResult,"num_refe");
					}
				}
			}
			$ped_encabezado=$row['pedimento'];
			$aduana_encabezado=$row['aduana'];
			$nomov=rtrim($row['no_mov']);
			$trafico=rtrim($row['trafico']);
			$trafico1=rtrim($row['trafico']);
			$trafico=str_replace('/','_',$trafico);
			if ($row['tipomov']=='I'){
				$query = "Select c001numref as num_refe, c001numped as num_pedi from factura_consolidado where c001refmas='".$trafico1."'";
			}
			$result = mysqli_query($db,$query);
			if(!$result){
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']="Error al realizar consulta de pedimentos: ".$db->error;
				return $respuesta;
			}
			$bcorreo=$bcorreo.'<td><center>';
			if($aduana_encabezado=='240' or $aduana_encabezado=='800'){
				$numrefe=rtrim($ref_ped_encabezado);
				$ped_link=$ped_encabezado;
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/pedimentos/'.$numrefe.'.pdf">'.rtrim($ped_link).'</a> ';
			}
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$numrefe=rtrim($row['num_refe']);
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/pedimentos/'.$numrefe.'.pdf">'.rtrim($row['num_pedi']).'</a> ';
			}
			$bcorreo=$bcorreo.'</center></td>';
			mysqli_data_seek($result, 0);
			$bcorreo=$bcorreo.'<td><center>';
			if($aduana_encabezado=='240' or $aduana_encabezado=='800'){
				$numrefe=rtrim($ref_ped_encabezado);
				$ped_link=$ped_encabezado;
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/sii/admin/formato_cove/formato_cove_vu.php?referencia='.$numrefe.'">'.rtrim($ped_link).'</a> ';
			}
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$numrefe=rtrim($row['num_refe']);
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/sii/admin/formato_cove/formato_cove_vu.php?referencia='.$numrefe.'">'.rtrim($row['num_pedi']).'</a> ';
			}
			$bcorreo=$bcorreo.'</center></td>';
			mysqli_data_seek($result, 0);
			$bcorreo=$bcorreo.'<td><center>';
			if($aduana_encabezado=='240' or $aduana_encabezado=='800'){
				$numrefe=rtrim($ref_ped_encabezado);
				$ped_link=$ped_encabezado;
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/sii/admin/descargazipedoc.php?referencia='.$numrefe.'">'.rtrim($ped_link).'</a> ';
			}
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$numrefe=rtrim($row['num_refe']);
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/sii/admin/descargazipedoc.php?referencia='.$numrefe.'">'.rtrim($row['num_pedi']).'</a> ';
			}
			$bcorreo=$bcorreo.'</center></td>';
			mysqli_data_seek($result, 0);
			$bcorreo=$bcorreo.'<td><center>';
			if($aduana_encabezado=='240' or $aduana_encabezado=='800'){
				$numrefe=rtrim(str_replace('-','_',$ref_ped_encabezado));
				$ped_link=$ped_encabezado;
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/pedimentos/HCMV/'.$numrefe.'_Hojascalculo.pdf">'.rtrim($ped_link).'</a> ';
			}
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$numrefe=rtrim(str_replace('-','_',$row['num_refe']));
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/pedimentos/HCMV/'.$numrefe.'_Hojascalculo.pdf">'.rtrim($row['num_pedi']).'</a> ';
			}
			$bcorreo=$bcorreo.'</center></td>';
			mysqli_data_seek($result, 0);
			$bcorreo=$bcorreo.'<td><center>';
			if($aduana_encabezado=='240' or $aduana_encabezado=='800'){
				$numrefe=rtrim(str_replace('-','_',$ref_ped_encabezado));
				$ped_link=$ped_encabezado;
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/pedimentos/HCMV/'.$numrefe.'_Anexohojacalculo.pdf">'.rtrim($ped_link).'</a> ';
			}
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$numrefe=rtrim(str_replace('-','_',$row['num_refe']));
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/pedimentos/HCMV/'.$numrefe.'_Anexohojacalculo.pdf">'.rtrim($row['num_pedi']).'</a> ';
			}
			$bcorreo=$bcorreo.'</center></td>';
			mysqli_data_seek($result, 0);
			$bcorreo=$bcorreo.'<td><center>';
			if($aduana_encabezado=='240' or $aduana_encabezado=='800'){
				$numrefe=rtrim(str_replace('-','_',$ref_ped_encabezado));
				$ped_link=$ped_encabezado;
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/pedimentos/HCMV/'.$numrefe.'_Manifestacion.pdf">'.rtrim($ped_link).'</a> ';
			}
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$numrefe=rtrim(str_replace('-','_',$row['num_refe']));
				$bcorreo=$bcorreo.'<a href="http://'.$ip1.'/pedimentos/HCMV/'.$numrefe.'_Manifestacion.pdf">'.rtrim($row['num_pedi']).'</a> ';
			}
			$bcorreo=$bcorreo.'</center></td>';
			if (file_exists($dir_archivos_anexos_gab.$trafico.'.pdf')){
				$bcorreo=$bcorreo.'<td><center><a href="http://'.$ip1.'/anexos/'.$trafico.'.pdf">Descargar</a></center></td></tr>';
				if ($omite_cliente=='false'){
					array_push($enviadas,array($tipomov,$nomov));
				}
				$cuentasenv=$cuentasenv.' '.$nomov;
			}
			else
			{
				if ($row["ridentica"]==0){
					$pos=strpos($trafico, '-');
					if ($pos === false) {
						$ar1=substr($trafico,0,2).'-'.substr($trafico,2);
					}
					else{
						$ar1=str_replace('-','',$trafico);
					}
					if (file_exists($dir_archivos_anexos_gab.$ar1.'.pdf')){
						$bcorreo=$bcorreo.'<td><center><a href="http://'.$ip1.'/anexos/'.$ar1.'.pdf">Descargar</a></center></td></tr>';
						if ($omite_cliente=='false'){
							array_push($enviadas,array($tipomov,$nomov));
						}
						$cuentasenv=$cuentasenv.','.$nomov;
					}
					else{
						$bcorreo=$bcorreo.'<td><center><a href="http://'.$ip1.'/anexos/'.$trafico.'.pdf">Descargar</a></center></td></tr>';
						$cuentasenv=$cuentasenv.','.$nomov;
					}
				}
			}
		}
		$bcorreo=$bcorreo.'</table><br><br>Conozca nuestro <a href="https://www.delbravoweb.com/sii"/>Sistema Integral de Informacion (SII)</a> en donde puede consultar todo lo relacionado con sus embarques en caso de no contar con usuario y password favor de solicitarlo a Abisai Cruz (abisaicruz@delbravo.com).<br><br>Este es un aviso generado automaticamente, favor de no responder a el. </body></html>';
		$titulo = 'Envio de facturas de Grupo Del Bravo, Cliente: '.$nomcliente;
		$para = '';
		if ($correos=='')
		{
			$cc = '';
		}
		else
		{
			if ($omite_cliente=='true'){
				$para = rtrim($correos);
			}
			else{
				$cc = rtrim($correos);
			}
		}
		$bcc = 'abisaicruz@delbravo.com';
		$contp=0;
		$contcc=0;
		$contbcc=0;
		if ($omite_cliente=='false'){
			$query = "
				Select
					correo,
					id_tpo_correo
				from
					correos
				where no_cte='".$numcliente."'";
			$result = mysqli_query($db,$query);
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				switch($row['id_tpo_correo']){
					case 1:
						$contp++;
						if (($contp==1) && ($correos=='')){
							$para = $row['correo'];
						}
						else{
							$para .= ';'.$row['correo'];
						}
						break;
					case 2:
						$contcc++;
						if (($contcc==1) && ($correos=='')){
							$cc = $row['correo'];
						}
						else{
							$cc .= ';'.$row['correo'];
						}
						break;
					case 3:
						$contbcc++;
						if (($contbcc==1) && $bcc==''){
							$bcc = $row['correo'];
						}
						else{
							$bcc .= ';'.$row['correo'];
						}
						break;
				}
			}
		}

		$para = explode(";", $para);
		$cc = explode(";", $cc);
		$bcc = explode(";", $bcc);
		$mail = new PHPMailer();
		try {
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			//$mail->SMTPSecure = "tls";
			$mail->Host = "mail.delbravo.com"; // SMTP a utilizar. Por ej. smtp.elserver.com
			$mail->Username = "facturacionelectronica@delbravo.com"; // Correo completo a utilizar
			$mail->Password = "facelec01"; // Contraseña
			$mail->Port = "587"; // Puerto a utilizar
			//Con estas pocas líneas iniciamos una conexión con el SMTP. Lo que ahora deberíamos hacer, es configurar el mensaje a enviar, el //From, etc.
			$mail->From = "facturacionelectronica@delbravo.com"; // Desde donde enviamos (Para mostrar)
			$mail->FromName = "facturacionelectronica@delbravo.com";
			//Estas dos líneas, cumplirían la función de encabezado (En mail() usado de esta forma: “From: Nombre <correo@dominio.com>”) de //correo.
			if (count($para)>0){
				foreach($para as $correo){
					$mail->addAddress($correo);
				}
			}
			if (count($cc)>0){
				foreach($cc as $correo){
					$mail->addCC($correo);
				}
			}
			if (count($bcc)>0){
				foreach($bcc as $correo){
					$mail->addBCC($correo);
				}
			}
			$mail->IsHTML(true); // El correo se envía como HTML
			$mail->Subject = $titulo; // Este es el titulo del email.
			$mail->Body = $bcorreo; // Mensaje a enviar
			if ($buzon == 'true' and $tipomov != 'R'){
				$mail->addAttachment('\\\\192.168.1.107\\gabdata\\avanza\\gab\\xml\\' . $nomov . '.xml', $nomov . '.xml');
				$mail->addAttachment('\\\\192.168.1.107\\gabdata\\avanza\\gab\\pdf\\' . $nomov . '.pdf', $nomov . '.pdf');
			}
			$exito = $mail->Send(); // Envía el correo.
			if(!$exito){
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Mailer Error: ' . $mail->ErrorInfo;
				return $respuesta;
			}
		} catch (Exception $e) {
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Mailer Error: ' . $mail->ErrorInfo;
			return $respuesta;
		}
		
		if (($para!='')||($cc!='')){
			foreach( $enviadas as $x=>$y) {
				$query = "
					update
						aacgmex
					set
						fecha_enviocliente='".date('Y-m-d H:i:s')."'
					where
						no_mov='".$y[1]."'
					and no_banco=1
					and tipo_mov='".$y[0]."'
					and fecha_enviocliente is null";
				$result = mysqli_query($db,$query);
				if (!$result){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al actualizar la fecha de envio de la cuenta: '.$db->error;
					return $respuesta;
				}
			}
			$respuesta['Codigo']=1;
			$respuesta['Mensaje']='Correo Enviado cuentas: '.$cuentasenv;
		}else{
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='CORREO NO ENVIADO, lo destinarios To o Cc no pueden estar vacios';
		}
		/*//echo "Para: ".$para;
		//echo "CC: ".$cc;
		//echo "Bcc: ".$bcc;
		// message
		$rn = "\r\n";
		// Para enviar un correo HTML mail, la cabecera Content-type debe fijarse
		$cabeceras  = 'MIME-Version: 1.0' . $rn;
		$cabeceras .= 'Content-type: text/html; charset=utf-8' . $rn;

		// Cabeceras adicionales
		//$cabeceras .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . $rn;
		$cabeceras .= 'From: facturacionelectronica@delbravo.com' . $rn;
		if ($cc != '') {
			$cabeceras .= 'Cc: '. $cc . $rn;
		}
		if ($bcc != '') {
			$cabeceras .= 'Bcc: '. $bcc . $rn;
		}
		if ($buzon == 1 and $tipomov != 'R'){
			aadd(aFiles,'\\\\192.168.1.107\\avanza\\gab\\xml\\' + $nomov + '.xml')
			aadd(aFiles,'\\\\192.168.1.107\\avanza\\gab\\pdf\\' + $nomov + '.pdf')
		}
		// Mail it
		if (($para=='')&&($cc=='')&&($bcc=='')){
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='CORREO NO ENVIADO, no se recibieron direcciones de correo';
		}
		else{
			if (($para!='')||($cc!='')){
				mail($para, $titulo, $bcorreo, $cabeceras);
				foreach( $enviadas as $x=>$y) {
					$query = "
						update
							aacgmex
						set
							fecha_enviocliente='".date('Y-m-d H:i:s')."'
						where
							no_mov='".$y[1]."'
						and no_banco=1
						and tipo_mov='".$y[0]."'
						and fecha_enviocliente is null";
					error_log($query);
					$result = mysqli_query($db,$query);
					if (!$result){
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al actualizar la fecha de envio de la cuenta: '.$db->error;
						return $respuesta;
					}
				}
				$respuesta['Codigo']=1;
				$respuesta['Mensaje']='Correo Enviado cuentas: '.$cuentasenv;
			}
			else{
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='CORREO NO ENVIADO, lo destinarios To o Cc no pueden estar vacios';
			}
		}*/
		
		return $respuesta;
	}

	// create HTTP listener
    if ( !isset( $HTTP_RAW_POST_DATA ) ) $HTTP_RAW_POST_DATA =file_get_contents( 'php://input' );
		$server->service($HTTP_RAW_POST_DATA);
    exit();
?>
