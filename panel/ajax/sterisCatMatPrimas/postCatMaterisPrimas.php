<?php
include_once('./../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	// DB table to use
	$table = 'bodega';

	$primaryKey = 'id_registro';    
    $columns = array(	
		array('db' => 'strMaterial', 'dt' => 'strMaterial' ),
		array('db' => 'strTipo', 'dt' => 'strTipo' ),
		array('db' => 'strNombre', 'dt' => 'strNombre' ),
		array('db' => 'strNombreIng', 'dt' => 'strNombreIng' ),
		array('db' => 'strUnidad', 'dt' => 'strUnidad' ),
		array('db' => 'intFraccion', 'dt' => 'intFraccion' ),
		array('db' => 'strPaisOrigen', 'dt' => 'strPaisOrigen' )
    );

    $sql_details = array(
        'user' => $mysqluser,
        'pass' => $mysqlpass,
        'db'   => $mysqldb,
        'host' => $mysqlserver
    );

	$baseSql = "SELECT id_registro, strMaterial, strTipo, strNombre, strNombreIng, strUnidad, intFraccion, strPaisOrigen
                FROM bodega.steris_catalogo_materis_primas";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('./../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







