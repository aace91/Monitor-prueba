<?php
include_once('./../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
}else{	
	if (isset($_POST['anio']) && !empty($_POST['anio'])) {		
		require('./../../../connect_dbsql.php');
		$anio = $_POST['anio'];
		$id_aduana = $_POST['id_aduana'];
		if (!isset($_POST['id_aduana']) || empty($_POST['id_aduana'])) {
			$aduana = $_POST['aduana'];
			$consulta = " SELECT id_aduana FROM librop_aduanas WHERE numero = '".$aduana."'";
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$error=mysqli_error($cmysqli);
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Error al consultar el numero de aduana. [Aduana:'.$aduana.']';
				$respuesta['Error'] = $error;
				echo json_encode($respuesta);
			}else{
				if(mysqli_num_rows($query) > 0){
					while($row = mysqli_fetch_array($query)){
						$id_aduana = $row['id_aduana'];
					}
				}else{
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Error, la aduana no existe. [Aduana:'.$aduana.']';
					$respuesta['Error'] = '';
					echo json_encode($respuesta);
				}
			}
		}
		$patente = $_POST['patente'];
		$referencia = $_POST['referencia'];
		$fecha = $_POST['fecha'];
		$id_cliente = $_POST['id_cliente'];
		$cliente = $_POST['cliente'];
		$operacion = $_POST['operacion'];
		$cve_pedimento = $_POST['cve_pedimento'];
		$descripcion = $_POST['descripcion'];
		$observaciones = $_POST['observaciones'];
		
		//***********************************************************//
		$fecha_registro =  date("Y-m-d H:i:s");		
		//***********************************************************//
		
		$consulta = " SELECT id_rango,consecutivo,pedimento_final 
						FROM librop_rangos
						WHERE patente='".$patente."' AND id_aduana = ".$id_aduana." AND anio = '".$anio."'";
		
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al consultar rangos disponibles.';
			$respuesta['Error'] = ' [librop_rangos]['.$error.']';
		}else{
			$nRows = mysqli_num_rows($query);
			if($nRows > 0){
				$nPedimento = '';
				while($row = mysqli_fetch_array($query)){
					$nIdRango = $row['id_rango'];
					$nConsecutivo = intval($row['consecutivo']);
					$nPedFinal = intval($row['pedimento_final']);
					
					if(($nConsecutivo + 1) <=  $nPedFinal){
						$nCons = ($nConsecutivo + 1);
						$consulta = " UPDATE librop_rangos SET
											consecutivo = ".$nCons."
										WHERE id_rango = ".$nIdRango;
						
						$query = mysqli_query($cmysqli,$consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = 'Error al generar número de pedimento.';
							$respuesta['Error'] = ' [UPDATE librop_rangos]['.$error.']';
							break;
						}else{
							$nPedimento = $nConsecutivo;
							break;
						}
					}
				}
				if($nPedimento != ''){
					$consulta = " INSERT INTO librop_libro (pedimento,referencia,patente,id_aduana,anio,id_cliente,cliente,tipo_operacion,
															clave_pedimento,descripcion_mercancia,observaciones,fecha_pedimento,fecha_registro,usuario_registro)
										VALUES (
												'".$nPedimento."',
												'".$referencia."',
												'".$patente."',
												".$id_aduana.",
												'".$anio."',
												'".$id_cliente."',
												'".str_replace("'"," ",$cliente)."',
												'".$operacion."',
												'".$cve_pedimento."',
												'".$descripcion."',
												'".$observaciones."',
												'".$fecha."',
												'".$fecha_registro."',
												".$id.")";
					
					$query = mysqli_query($cmysqli,$consulta);
					if (!$query) {
						$error=mysqli_error($cmysqli);
						$respuesta['Codigo'] = -1;
						$respuesta['Mensaje'] = 'Error al generar número de pedimento.';
						$respuesta['Error'] = ' [INSERT librop_libro]['.$error.']';
					}else{
						$respuesta['Codigo'] = 1;
						$respuesta['Pedimento'] = $nPedimento;
					}
				}else{
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Sin rangos validos. Por favor, verifíquelo con su administrador.';
					$respuesta['Error'] = '';
				}
			}else{
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'No existe un rango para esta selección. Por favor, verifíquelo con su administrador.';
				$respuesta['Error'] = '';
			}			
		}
		








		/*
		$consulta = "INSERT INTO librop_rangos (consecutivo,patente,id_aduana,anio,pedimento_inicial,pedimento_final,fecha_registro,usuario_registro)
						VALUES(	".$pedimento_ini.",
								'".$patente."',
								".$id_aduana.",
								'".$anio."',
								".$pedimento_ini.",
								".$pedimento_fin.",
								'".$fecha_registro."',
								".$id .")";
		
		$query = mysqli_query($cmysqli,$consulta);
		
		if (!$query){
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo'] = '-1';
			$respuesta['Mensaje']='Error al insertar el rango de los pedimentos.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else {
			$respuesta['Codigo'] = '1';
		}*/
		
	}else{
		$respuesta['Codigo'] = '-1';
		$respuesta['Mensaje'] = "458 : Error al recibir los datos del pedimento.";
		$respuesta['Error'] = '';
	}
	echo json_encode($respuesta);
}

