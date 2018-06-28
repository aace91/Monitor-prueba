<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{	
	$sFiltro = $_POST['sFiltro'];
	//$sTexto = $_POST['sTexto'];
	
	$table = 'expedientes';
	$primaryKey = 'id_registro';    
    $columns = array(	
		array('db' => 'id_registro', 'dt' => 'id_registro' ),
		array('db' => 'cuenta_gastos', 'dt' => 'cuenta_gastos' ),
		array('db' => 'referencia_saaio', 'dt' => 'referencia_saaio' ),
		array('db' => 'pedimento', 'dt' => 'pedimento' ),
		array('db' => 'comentarios', 'dt' => 'comentarios' ),
		array('db' => 'fecha_archivo_mv', 'dt' => 'fecha_archivo_mv', 'formatter' => function( $val, $row ) {
                if ($val==''){
					return '';
				}else{			
					return $val; 
				}               
        }),
		array('db' => 'fecha_archivo_hc', 'dt' => 'fecha_archivo_hc', 'formatter' => function( $val, $row ) {
                if ($val==''){
					return '';
				}else{			
					return $val; 
				}               
        }),
		array('db' => 'caja', 'dt' => 'caja', 'formatter' => function( $val, $row ) {
                if ($val==''){
					return '';
				}else{			
					return $val; 
				}               
        })
    );

    $sql_details = array(
        'user' => $mysqluser_exp,
        'pass' => $mysqlpass_exp,
        'db'   => $mysqldb_exp,
        'host' => $mysqlserver_exp
    );

	$baseSql = "SELECT a.id_registro,
			           CONCAT(a.tipo_mov,'-',a.no_banco,'-',a.no_mov) AS cuenta_gastos, 
					   a.referencia_saaio, 
					   a.pedimento,
					   a.comentarios,
					   a.fecha_archivo_mv,
                       a.fecha_archivo_hc,
					   b.id_caja AS caja
				FROM expedientes.seguimiento_pedime as a LEFT JOIN 
				     expedientes.expedientes b ON b.tipo_mov = a.tipo_mov AND 
												  b.no_banco = a.no_banco AND 
												  b.no_mov = a.no_mov AND
												  b.referencia = a.referencia";
	
	switch ($sFiltro) {
		case 'pendientes':
			$baseSql .= " WHERE a.impo_expo = 1 AND
			                    a.fecha_archivo_archivado IS NOT NULL AND 
							   (a.fecha_archivo_mv IS NULL AND a.fecha_archivo_hc IS NULL)";
			break;
		case 'firmados':
			$baseSql .= " WHERE a.impo_expo = 1 AND
			                    a.fecha_archivo_archivado IS NOT NULL AND 
                               (a.fecha_archivo_mv IS NOT NULL OR a.fecha_archivo_hc IS NOT NULL)";
			break;
	}	
				
				
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







