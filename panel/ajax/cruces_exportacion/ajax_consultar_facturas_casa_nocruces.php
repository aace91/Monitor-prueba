<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../connect_casa.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {  
		$respuesta['Codigo']=1;
		$referencia = $_POST['referencia'];
		
		$consulta = "SELECT GROUP_CONCAT(cons_fact) as consecutivos
						FROM cruces_expo_detalle
						WHERE referencia = '".$referencia."'
						GROUP BY referencia";
	
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar la informacion del cruce. Por favor contacte el administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		}else{
			$nItem = 0;
			while ($row = mysqli_fetch_array($query)){
				
				$qCasa = "SELECT f.CONS_FACT,f.NUM_FACT
							FROM SAAIO_FACTUR f
							WHERE f.NUM_REFE = '".$referencia."'".(trim($row['consecutivos']) != ''? " AND f.CONS_FACT NOT IN (".$row['consecutivos'].")" : '')."
							ORDER BY f.NUM_FACT";
				
				$resped = odbc_exec ($odbccasa, $qCasa);
				if ($resped == false){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error al consultar facturas disponibles en CASA. BD.CASA.";
					$respuesta['Error'] = ' ['.odbc_error().']'.$qCasa;
					exit(json_encode($respuesta));
				}
				if(odbc_num_rows($resped) == 0 ){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="No existen facturas disponibles en CASA para la referencia ".$referencia.". BD.CASA.";
					$respuesta['Error'] = '';// ['.odbc_error().']'.$qCasa;
					exit(json_encode($respuesta));
				}
				$respuesta['optsFacturas'] = '';
				while(odbc_fetch_row($resped)){
					$respuesta['optsFacturas'] .= '<option value = "'.odbc_result($resped,"CONS_FACT").'">'.odbc_result($resped,"NUM_FACT").'</option>';
				}
			}
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	echo json_encode($respuesta);
}
