<?php
include_once('./../../../checklogin.php');
require('./../../../connect_casa.php');
if ($loggedIn == false){
	echo '500';
} else{
	$primaryKey = 'id_fraccion_hist';
	$table = 'fracciones_historico';
	
    $columns = array(	
		array('db' => 'id_fraccion_hist', 'dt' => 'id_fraccion_hist' ),
		array('db' => 'num_refe', 'dt' => 'num_refe' ),
		array('db' => 'cons_fact', 'dt' => 'cons_fact' ),
		array('db' => 'cons_par', 'dt' => 'cons_par' ),
		//Factura
		array( 'db' => 'num_refe',     'dt' => 'factura' , 'formatter' => function( $d, $row ) {
			if($d != ''){
				global $odbccasa;
				$sNumFact = '';
				$sNUM_REFE = $d;
				$sCONS_FACT = $row['cons_fact'];
				$qCasa = "SELECT a.NUM_FACT
							FROM SAAIO_FACTUR a
							WHERE a.NUM_REFE = '".$sNUM_REFE."' AND a.CONS_FACT = ".$sCONS_FACT;
				$res = odbc_exec ($odbccasa, $qCasa);
				if ($res == false){
					$mensaje = "Error al consultar el numero de factura. [Referencia:".$sNUM_REFE." Consecutivo: ".$sCONS_FACT."][BD.CASA].".odbc_error().$qCasa ;
					echo json_encode( array("error" => $mensaje));
					exit(0);
				}else{
					while(odbc_fetch_row($res)){					
						$sNumFact = odbc_result($res,"NUM_FACT");
					}
				}
				return $sNumFact;
			}else{
				return $d;
			}
		}),
		//Proveedor
		array( 'db' => 'num_refe',     'dt' => 'proveedor' , 'formatter' => function( $d, $row ) {
			if($d != ''){
				global $odbccasa;
				$sNomProv = '';
				$sNUM_REFE = $d;
				$sCONS_FACT = $row['cons_fact'];
				$qCasa = "SELECT b.NOM_PRO
							FROM SAAIO_FACTUR a
								INNER JOIN CTRAC_PROVED b ON
									a.CVE_PROV = b.CVE_PRO
							WHERE a.NUM_REFE = '".$sNUM_REFE."' AND a.CONS_FACT = ".$sCONS_FACT;
				$res = odbc_exec ($odbccasa, $qCasa);
				if ($res == false){
					$mensaje = "Error al consultar el nombre del proveedor. [Referencia:".$sNUM_REFE." Consecutivo Factura: ".$sCONS_FACT."][BD.CASA].".odbc_error().$qCasa ;
					echo json_encode( array("error" => $mensaje));
					exit(0);
				}else{
					while(odbc_fetch_row($res)){					
						$sNomProv = odbc_result($res,"NOM_PRO");
					}
				}
				return $sNomProv;
			}else{
				return $d;
			}
		}),
		array('db' => 'cantidad', 'dt' => 'cantidad' ),
		array('db' => 'valor', 'dt' => 'valor' ),
		array('db' => 'fraccion_anterior', 'dt' => 'fraccion' ),
		array('db' => 'descripcion', 'dt' => 'descripcion' ),
		array('db' => 'numero_permiso', 'dt' => 'numero_permiso' ),
		array('db' => 'fecha_registro', 'dt' => 'fecha_registro', 'formatter' => function( $d, $row ) {
			return date( 'd/m/Y H:i:s', strtotime($d));
		}),
        array('db' => 'usuario_registro', 'dt' => 'usuario_registro' )
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb_sterisr8va,
        'host' => $mysqlserver
    );
    
    $baseSql = "SELECT fh.id_fraccion_hist,fh.num_refe, fh.cons_fact, fh.cons_par, fh.cantidad, fh.valor,
						f.fraccion as fraccion_anterior,f.descripcion,f.numero_permiso,
						fh.fecha_registro,u.usunombre as usuario_registro
				FROM fracciones_historico fh 
					INNER JOIN fracciones f ON
						fh.id_fraccion = f.id_fraccion
					LEFT JOIN bodega.tblusua u ON
						f.usuario_registro = u.Usuario_id
				";	

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../ssp.class.php');
    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







