<?php
include_once('./../../../checklogin.php');

if ($loggedIn == false){
	$msg='Code [500]';
	echo json_encode( array( 
		"error" => $msg
	) );
	exit(0);
} else {
	
	//***********************************************************//
		
	$sIdCliente = $_POST['sIdCliente'];
	
	//***********************************************************//
		
	// DB table to use
	$table = 'expos_salidas';
	$primaryKey = 'id_salida';    
    $columns = array(	
		array('db' => 'id_salida', 'dt' => 'salidanumero' ),
		array('db' => 'id_cliente', 'dt' => 'id_cliente' ),
		array('db' => 'caja', 'dt' => 'caja' ),
		array('db' => 'bultos', 'dt' => 'bultos' ),
		array('db' => 'facturas', 'dt' => 'facturas' ),
		array('db' => 'estatus', 'dt' => 'estatus' ),
		array('db' => 'documentado', 'dt' => 'documentado' ),
		array('db' => 'adicional', 'dt' => 'adicional' ),
		array('db' => 'idlogistica', 'dt' => 'idlogistica' ),
		array('db' => 'logistica', 'dt' => 'logistica' ),
		array('db' => 'fecha', 'dt' => 'fecha', 'formatter' => function( $d, $row ) {
			if ($d==''){
				return '';
			}else{				
				return date( 'd/m/Y H:i:s', strtotime($d)); 
			}               
		}),
		array('db' => 'fecha_aprobado', 'dt' => 'fecha_aprobado', 'formatter' => function( $d, $row ) {
				if ($d==''){
					return '';
				}else{				
					return date( 'd/m/Y H:i:s', strtotime($d)); 
				}               
		}),
		array('db' => 'bcomentario', 'dt' => 'bcomentario' )
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb,
        'host' => $mysqlserver
    );

	$baseSql = "SELECT a.id_salida, a.id_cliente, a.caja, a.bultos, a.fecha, a.documentado, a.adicional, a.fecha_aprobado, b.id_registro AS bcomentario,
				       (SELECT GROUP_CONCAT(c.FACTURA_NUMERO SEPARATOR \", \")
				        FROM bodega.expos_salidas_facturas AS c
                        WHERE c.SALIDA_NUMERO = a.id_salida
			            GROUP BY c.SALIDA_NUMERO) AS facturas,
					   (CASE 
							WHEN a.fecha_aprobado IS NOT NULL THEN 'Aprobado'
							WHEN a.adicional IS NOT NULL THEN 'Imagenes'
							WHEN a.documentado IS NOT NULL THEN 'Documentado'
							ELSE ''
						END) AS estatus, 
					   (SELECT c.nombre
		                FROM bodega.expos_salidas_logisticas AS c
		                WHERE c.logistica = a.logistica
		                LIMIT 1) AS logistica, a.logistica AS idlogistica
				FROM bodega.expos_salidas AS a LEFT JOIN
					 bodega.expos_salidas_coments AS b ON 
					 a.id_salida = b.id_salida
				WHERE a.id_cliente='".$sIdCliente."'
				GROUP BY a.id_salida";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







