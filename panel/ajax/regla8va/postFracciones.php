<?php
include_once('./../../../checklogin.php');
if ($loggedIn == false){
	echo '500';
} else{
	
	$primaryKey = 'id_fraccion';
	$table = 'fracciones';
	
    $columns = array(	
		array('db' => 'id_fraccion', 'dt' => 'id_fraccion' ),
		array('db' => 'descripcion', 'dt' => 'descripcion' ),
		array('db' => 'fraccion', 'dt' => 'fraccion' ),
		array('db' => 'cantidad', 'dt' => 'cantidad' ),
		array('db' => 'valor', 'dt' => 'valor' ),
		array('db' => 'fecha_vencimiento', 'dt' => 'fecha_vencimiento', 'formatter' => function( $d, $row ) {
			return date( 'd/m/Y', strtotime($d));
		}),
		array('db' => 'numero_permiso', 'dt' => 'numero_permiso' ),
		array('db' => 'vencida', 'dt' => 'vencida' ),
		array('db' => 'cantidad_saldo', 'dt' => 'cantidad_saldo' ),
		array('db' => 'valor_saldo', 'dt' => 'valor_saldo' ),
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
    
    $baseSql = "SELECT f.id_fraccion,f.descripcion,f.fraccion,f.cantidad,f.valor,f.fecha_vencimiento,f.numero_permiso,
						IF(f.fecha_vencimiento <= CURDATE(), 'S', 'N') as vencida,
						(f.cantidad - IF(SUM(fh.cantidad) IS NULL, 0, SUM(fh.cantidad))) AS cantidad_saldo,
						(f.valor - IF(SUM(fh.valor) IS NULL, 0, SUM(fh.valor))) AS valor_saldo,
						f.fecha_registro,u.usunombre as usuario_registro						
				FROM fracciones f
					LEFT JOIN bodega.tblusua u ON
						f.usuario_registro = u.Usuario_id
					LEFT JOIN fracciones_historico fh ON
						f.id_fraccion = fh.id_fraccion
				WHERE f.eliminado = '0'
				GROUP BY f.id_fraccion";	

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../ssp.class.php');
    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







