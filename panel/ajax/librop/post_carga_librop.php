<?php
include_once('./../../../checklogin.php');

//$link_base = "http://$ip_out2:$port_out2/webtools/upload/solicitudSellos/";

if ($loggedIn == false){
	echo '500';
} else{
	require('./../../../connect_dbsql.php');
	
	$primaryKey = 'id_librop';
    
    $columns = array(	
		array('db' => 'id_librop', 'dt' => 'id_librop' ),
		array('db' => 'pedimento', 'dt' => 'pedimento' ),
		array('db' => 'referencia', 'dt' => 'referencia' ),
		array('db' => 'patente', 'dt' => 'patente' ),
		array('db' => 'aduana', 'dt' => 'aduana' ),
		array('db' => 'anio', 'dt' => 'anio' ),
		array('db' => 'cliente', 'dt' => 'cliente' ),
		array('db' => 'tipo_operacion', 'dt' => 'tipo_operacion' ),
		array('db' => 'clave_pedimento', 'dt' => 'clave_pedimento' ),
		array('db' => 'descripcion_mercancia', 'dt' => 'descripcion_mercancia' ),
        array('db' => 'observaciones', 'dt' => 'observaciones' )
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb,
        'host' => $mysqlserver
    );
    
    $baseSql = "SELECT  lp.id_librop,
						lp.pedimento,
						lp.referencia,
						CONCAT(pt.patente,'-',pt.nombre) as patente,
						CONCAT(ad.numero,'-',ad.nombre) as aduana,
						lp.anio,
						lp.cliente,
						IF(lp.tipo_operacion = '1','Importacón','Exportación') as tipo_operacion,
						lp.clave_pedimento,
						lp.descripcion_mercancia,
						lp.observaciones
				FROM librop_libro lp 
					INNER JOIN librop_patentes pt ON
						lp.patente = pt.patente
					INNER JOIN librop_aduanas ad ON
						lp.id_aduana = ad.id_aduana";	

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../ssp.class.php');
	$table = '';
    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







