<?php
    function consultar_controles_facturas_select($idcliexpo){
        require('./../../../url_archivos.php');
		require('./../../../connect_dbsql.php');
		require('./../../../connect_casa.php');
        
        $respuesta['Codigo']=1;
        $respuesta['aPermisosAutomaticos'] = array();
        $respuesta['aPermisosAdhesion'] = array();
        $respuesta['aCertificados'] = array();
        
        $consulta="	SELECT id_permiso_adhesion, numero_permiso, 
                                IF(archivo_permiso IS NULL, '',CONCAT('".$URL_archivos_permisos."',archivo_permiso)) as archivo_permiso
                    FROM bodega.permisos_adhesion
                    WHERE	id_cliente = '".$idcliexpo."' AND fecha_vigencia_ini <= NOW() AND fecha_vigencia_fin >= NOW()
                    ORDER BY numero_permiso";
                            
        $query = mysqli_query($cmysqli, $consulta);
        if (!$query) {
            $error=mysqli_error($cmysqli);
            $respuesta['Codigo']=-1;
            $respuesta['Mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
            //return $response;
        }else{
            while($row = mysqli_fetch_object($query)){
                $id=$row->id_permiso_adhesion;
                $nombre=  $row->numero_permiso;
                $archivo_permiso=  $row->archivo_permiso;
                array_push($respuesta['aPermisosAdhesion'] ,array('id'=>$id,'text'=>$nombre,'url'=>$archivo_permiso));
            }
    
            $consulta="	SELECT id_permiso, numero_permiso,aviso_adhesion,
                                IF(archivo_permiso IS NULL, '',CONCAT('".$URL_archivos_permisos."',archivo_permiso)) as archivo_permiso
                        FROM bodega.permisos_pedimentos
                        WHERE	id_cliente = '".$idcliexpo."' AND fecha_vigencia_ini <= NOW() AND fecha_vigencia_fin >= NOW()
                        ORDER BY numero_permiso";
    
            $query = mysqli_query($cmysqli, $consulta);
            if (!$query) {
                $error=mysqli_error($cmysqli);
                $respuesta['Codigo']=-1;
                $respuesta['Mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
                //return $response;
            }else{
                while($row = mysqli_fetch_object($query)){
                    $id=$row->id_permiso; 
                    $permiso = $row->numero_permiso;
                    $aviso_adhesion = $row->aviso_adhesion;
                    $val_utilizado = fcn_valor_utilizado_permiso_auto($row->numero_permiso);
                    $nombre= $permiso.' / '.$val_utilizado;
                    $archivo_permiso=  $row->archivo_permiso;
                    array_push($respuesta['aPermisosAutomaticos'],array('id'=>$id,'text'=>$nombre,'url'=>$archivo_permiso,'aviso_adhesion'=>$aviso_adhesion));
                }
        
                $consulta="	SELECT id_certificado, descripcion_mercancia,
                                    IF(descripcion_mercancia IS NULL, '',CONCAT('".$URL_archivos_certificados_origen."',archivo_certificado)) as archivo_certificado
                            FROM bodega.certificados_origen
                            WHERE	id_cliente = '".$idcliexpo."' AND fecha_vigencia_ini <= NOW() AND fecha_vigencia_fin >= NOW()
                            ORDER BY descripcion_mercancia";
        
                $query = mysqli_query($cmysqli, $consulta);
                if (!$query) {
                    $error=mysqli_error($cmysqli);
                    $respuesta['Codigo']=-1;
                    $respuesta['Mensaje']='Error en la consulta: ' .$consulta.' , error:'.$error ;
                }else{
                    while($row = mysqli_fetch_object($query)){
                        $id=$row->id_certificado;
                        $nombre= $row->descripcion_mercancia;
                        $archivo_certificado=  $row->archivo_certificado;
                        array_push($respuesta['aCertificados'],array('id'=>$id,'text'=>$nombre,'url'=>$archivo_certificado));
                    }
                }
            }
        }
        return $respuesta;
    }

    function fcn_valor_utilizado_permiso_auto($npermiso){
        require('./../../../connect_casa.php');
		//global $odbccasa;
		global $cmysqli;global $idcliexpo;
        
        $Valor_Dlls = 0;$Cantidad_kgs = 0;
        $qCasa = "SELECT b.NUM_PERM, SUM(b.VAL_CDLL) AS VAL_DLLS, SUM(b.CAN_TARI) AS CAN_TARI
                    FROM (  SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI
                                FROM SAAIO_PERPAR a
                                    INNER JOIN SAAIO_PEDIME c ON
                                        a.NUM_REFE = c.NUM_REFE
                                WHERE a.NUM_PERM = '".$npermiso."' AND c.FIR_PAGO IS NULL
                            UNION ALL
                            SELECT a.NUM_PERM, a.VAL_CDLL, a.CAN_TARI													
                            FROM SAAIO_PERMIS a
                                INNER JOIN SAAIO_PEDIME c ON
                                        a.NUM_REFE = c.NUM_REFE
                            WHERE a.NUM_PERM = '".$npermiso."' AND c.FIR_PAGO IS NOT NULL) b
                    GROUP BY (b.NUM_PERM)";
    
        $resped = odbc_exec ($odbccasa, $qCasa);
        if ($resped == false){
            $mensaje = "Error al consultar el valor dolares del permiso utilizado en pedimentos. BD.CASA.".odbc_error();
            return $mensaje;
        }else{
            while(odbc_fetch_row($resped)){
                $Valor_Dlls = odbc_result($resped,"VAL_DLLS");
                $Cantidad_kgs = odbc_result($resped,"CAN_TARI");
            }
        }
        $consulta ="SELECT p.valor_dlls_delbravo,p.cantidad_delbravo
                    FROM permisos_pedimentos p
                    WHERE p.numero_permiso = '".$npermiso."'";
                    
        $query = mysqli_query($cmysqli, $consulta);
        if (!$query) {
            $error=mysqli_error($cmysqli);
            return 'Error en la consulta: ' .$consulta.' , error:'.$error ;
        }
        while($row = mysqli_fetch_object($query)){
            $valor_dlls_delbravo = $row->valor_dlls_delbravo;
            $cantidad_delbravo =  $row->cantidad_delbravo;
        }
        $saldo_dlls = ($valor_dlls_delbravo - $Valor_Dlls);
        $saldo_kgs = ($cantidad_delbravo - $Cantidad_kgs);
        return '$'.number_format ($saldo_dlls,2). ' / '.number_format ($saldo_kgs,0);
    }
?>