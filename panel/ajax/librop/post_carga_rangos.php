<?php
include_once('./../../../checklogin.php');

//$link_base = "http://$ip_out2:$port_out2/webtools/upload/solicitudSellos/";

if ($loggedIn == false){
	echo '500';
} else{
	
	$primaryKey = 'id_rango';
    
    $columns = array(	
		array('db' => 'id_rango', 'dt' => 'id_rango' ),
		array('db' => 'patente', 'dt' => 'patente' ),
		array('db' => 'aduana', 'dt' => 'aduana' ),
		array('db' => 'anio', 'dt' => 'anio' ),
		array('db' => 'pedimento_inicial', 'dt' => 'pedimento_inicial' ),
		array('db' => 'pedimento_final', 'dt' => 'pedimento_final' ),
        array('db' => 'fecha_registro', 'dt' => 'fecha_registro' )
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb,
        'host' => $mysqlserver
    );
    
    $baseSql = "SELECT r.id_rango,CONCAT(ad.numero,'-',ad.nombre) AS aduana,
							CONCAT(pt.patente,'-',pt.nombre) AS patente,r.anio,
							r.pedimento_inicial,r.pedimento_final,r.fecha_registro
				FROM librop_rangos r
					INNER JOIN librop_aduanas ad ON
						r.id_aduana = ad.id_aduana
					INNER JOIN librop_patentes pt ON
						r.patente = pt.patente";	

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







