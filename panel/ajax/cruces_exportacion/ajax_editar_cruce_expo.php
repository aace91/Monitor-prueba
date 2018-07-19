<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../connect_casa.php');
require('./../../../url_archivos.php');
require('enviar_notificacion_cruces.php');
if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['id_cruce']) && !empty($_POST['id_cruce'])) {
		$respuesta['Codigo']=1;
		
		$id_cruce = $_POST['id_cruce'];
		$id_lineat = $_POST['id_lineat'];
		$aduana = $_POST['aduana'];
		$id_transfer = $_POST['id_transfer'];
		
		$caat = $_POST['caat'];
		$scac = $_POST['scac'];
		
		$po_number = $_POST['po_number'];
		
		$id_entregar = $_POST['id_entregar'];
		$nom_entregar = $_POST['nom_entregar'];
		$dir_entregar = $_POST['dir_entregar'];
		$indicaciones = $_POST['indicaciones'];
		$observaciones = $_POST['observaciones'];
		
		$aCliConsolidar = json_decode($_POST['aCliConsolidar']);
		$fecharegistro =  date("Y-m-d H:i:s");
		
		$consulta = "UPDATE bodega.cruces_expo SET 
												numlinea = ".$id_lineat.",
												aduana = '".$aduana."',
												notransfer = ".$id_transfer.",
												caat = '".$caat."',
												scac = '".$scac."',
												po_number = '".$po_number."',
												noentrega = ".$id_entregar.",
												nombreentrega = '".$nom_entregar."',
												direntrega = '".$dir_entregar."',
												indicaciones = '".$indicaciones."',
												observaciones = '".$observaciones."',";
		/*if($consolidar == '1'){
			$consulta .= "						numcliente_consolidar = '".$numcliente."',";
		}else{
			$consulta .= "						numcliente_consolidar = NULL,";
		}*/
		$consulta .= "							id_usuario_ult_mod = ".$id.",
												fecha_ult_mod = '".$fecharegistro."'
						WHERE id_cruce = ".$id_cruce;
		mysqli_query($cmysqli,"BEGIN");
		$query = mysqli_query($cmysqli,$consulta); 
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al actualizar la informacion del cruce.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
			mysqli_query($cmysqli,"ROLLBACK");
			mysqli_query($cmysqli,"COMMIT");
			exit(json_encode($respuesta));
		}
		$consulta = "DELETE FROM cruces_expo_clientes_consolidar WHERE id_cruce = ".$id_cruce;
		
		$query = mysqli_query($cmysqli,$consulta); 
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al actualizar la informacion del cruce.'; 
			$respuesta['Error'] = ' ['.$error.']'.$consulta;
			mysqli_query($cmysqli,"ROLLBACK");
			mysqli_query($cmysqli,"COMMIT");
			exit(json_encode($respuesta));
		}
		for($i=0; $i < count($aCliConsolidar); $i++ ){
			$consulta = "INSERT INTO cruces_expo_clientes_consolidar (id_cruce,numcliente)
					     VALUES (".$id_cruce.",'".$aCliConsolidar[$i]->id_cliente."')";
			
			$query = mysqli_query($cmysqli,$consulta); 
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']='Error al actualizar la informacion del cruce. [clientes consolidar]['.$aCliConsolidar[$i]->cliente.']'; 
				$respuesta['Error'] = ' ['.$error.']'.$consulta;
				mysqli_query($cmysqli,"ROLLBACK");
				mysqli_query($cmysqli,"COMMIT");
				exit(json_encode($respuesta));
			}
		}
		mysqli_query($cmysqli,"COMMIT");
		$res = enviar_notificacion_nuevo_cruce_email($id_cruce,'Editar','Datos del Encabezado');
		if($res['Codigo'] != 1){
			$respuesta['Mensaje'] .=  $res['Error'];
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}