<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');
require('./../../../url_archivos.php');

if($loggedIn == false){
	echo '500';
} else {
	if (isset($_POST['referencia']) && !empty($_POST['referencia'])) {
		$respuesta['Codigo']=1;
		$referencia = $_POST['referencia'];
		$id_cruce = $_POST['id_cruce'];
		$opcion = $_POST['opcion'];
		$seleccion = json_decode($_POST['seleccionadas']);
		
		$consulta = " SELECT ee.referencia,
							IFNULL(CONCAT(ad.numero,'-',l.patente,'-',l.pedimento) ,'') as pedimento
						FROM entradas_expo ee
							LEFT JOIN librop_libro l ON
								ee.referencia = l.referencia
							LEFT JOIN librop_aduanas ad ON
								l.id_aduana = ad.id_aduana
						WHERE ee.referencia = '".$referencia."'";
	
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al consultar la informacion del cruce. Por favor contacte el administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		}else{
			if(mysqli_num_rows($query) > 0){
				while ($row = mysqli_fetch_array($query)){
					$respuesta['pedimento']=$row['pedimento'];
				}
				switch($opcion){
					case 'todo':
						$consulta = "UPDATE cruces_expo_detalle SET referencia = '".$referencia."' WHERE id_cruce = ".$id_cruce;
						$query = mysqli_query($cmysqli,$consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al actualizar la referencia en todas las facturas del cruce.'; 
							$respuesta['Error'] = ' ['.$error.']';
						}else{
							include('consultar_facturas.php');
						}
						break;
					case 'sel':
						mysqli_query($cmysqli,"BEGIN");
						for($i = 0; $i < count($seleccion); $i++){
							$consulta = "UPDATE cruces_expo_detalle SET referencia = '".$referencia."' WHERE id_detalle_cruce = ".$seleccion[$i];
							$query = mysqli_query($cmysqli,$consulta);
							if (!$query) {
								$error=mysqli_error($cmysqli);
								$respuesta['Codigo']=-1;
								$respuesta['Mensaje']='Error al actualizar la referencia de la factura [id:'.$seleccion[$i].'].'; 
								$respuesta['Error'] = ' ['.$error.']';
								mysqli_query($cmysqli,"ROLLBACK");
								break;
							}
						}
						if($respuesta['Codigo'] == 1){
							include('consultar_facturas.php');
						}
						mysqli_query($cmysqli,"COMMIT");
						break;
					default:
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='Error al recibir la opcion de actualizacion.';
						$respuesta['Error']='';
				}
			}else{
				$respuesta['Codigo']=2;
				$respuesta['Mensaje']='La referencia no existe.'; 
				$respuesta['Error'] = '';
			}
		}
	}else{
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}
	exit(json_encode($respuesta));
}