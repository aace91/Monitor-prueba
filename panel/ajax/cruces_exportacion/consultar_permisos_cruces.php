<?php 
	$consulta = "SELECT cd.id_detalle_cruce,p.id_detalle_cruce_permiso,
						pp.numero_permiso as permiso_auto,
						p.id_permiso,
						pa.numero_permiso as permiso_adhesion,
						p.id_permiso_adhesion
						FROM cruces_expo_detalle cd
							INNER JOIN cruces_expo_permisos p ON
								cd.id_detalle_cruce = p.id_detalle_cruce
							INNER JOIN bodega.permisos_pedimentos pp ON
								p.id_permiso = pp.id_permiso
							LEFT JOIN bodega.permisos_adhesion pa ON
								p.id_permiso_adhesion = pa.id_permiso_adhesion
						WHERE cd.id_detalle_cruce =  ".$id_detalle_cruce;
	$queryper = mysqli_query($cmysqli,$consulta);
	if (!$queryper) {
		$error=mysqli_error($cmysqli);
		$respuesta['Mensaje'] .= ' Ocurrio un problema al actualizar la informacion de los cruces actuales. ['.$error.']';
		exit(json_encode($respuesta));
	}else{
		$aPermisos = array();
		while ($rowper = mysqli_fetch_array($queryper)){
			$aPermiso = array(
				'eliminar' => '<a href="javascript:void(0);" onclick="eliminar_permiso(\''.$rowper['id_detalle_cruce_permiso'].'\'); return false;"><span class="glyphicon glyphicon-trash"></span> Eliminar<a>',
				'aviso_adhesion' => $rowper['id_permiso_adhesion'],
				'aviso_aut' => $rowper['id_permiso'],
				'num_adhesion' => $rowper['permiso_adhesion'],
				'num_aut' => $rowper['permiso_auto']);
			array_push($aPermisos,$aPermiso);
		}
		$respuesta['aPermisos'] = $aPermisos;
	}
?>