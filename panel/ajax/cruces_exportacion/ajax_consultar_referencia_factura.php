<?php
include_once('./../../../checklogin.php');
require('./../../../connect_casa.php');
require('./../../../connect_dbsql.php');
include('./../../../url_archivos.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {  
		$respuesta['Codigo']=1;
		$referencia = $_POST['referencia'];
		
		$consulta = " SELECT ee.referencia,IFNULL(l.clave_pedimento,'') as regimen,
							CONCAT(ad.numero,'-',l.patente,'-',l.pedimento) as pedimento
						FROM entradas_expo ee
							LEFT JOIN librop_libro l ON
								ee.referencia = l.referencia
							LEFT JOIN librop_aduanas ad ON
								l.id_aduana = ad.id_aduana
						WHERE ee.referencia = '$referencia'";
	
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar la informacion del cruce. Por favor contacte el administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		}else{
			if(mysqli_num_rows($query) > 0){
				$respuesta['bExiste'] = '1';
				while ($row = mysqli_fetch_array($query)){
					//Consultar Pedimento de la referencia Libro Pedimento WEB
					$respuesta['Regimen'] = $row['regimen'];
					$respuesta['Pedimento'] = $row['pedimento'];
					//Consultar Regimen Del Pedimento
					$qCasa = "SELECT (b.CVE_DOC || '-' || b.DES_DOC) as DES_DOC
								FROM CTARC_DOCUME b
								WHERE b.CVE_DOC='".$respuesta['Regimen']."'";
				
					$resped = odbc_exec ($odbccasa, $qCasa);
					if ($resped == false){
						$respuesta['Regimen'] = '';
					}else{
						while(odbc_fetch_row($resped)){
							$respuesta['Regimen_Nom'] = odbc_result($resped,"DES_DOC");
						}
					}
				}
			}else{
				$respuesta['bExiste'] = '0';
			}
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
