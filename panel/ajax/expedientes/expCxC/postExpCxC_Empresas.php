<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{	
	// DB table to use
	$table = 'expedientes';
	$primaryKey = 'id_empresa';    
    $columns = array(	
		array('db' => 'id_empresa', 'dt' => 'id_empresa' ),
		array('db' => 'nombre', 'dt' => 'nombre' ),
		array('db' => 'rutadatos', 'dt' => 'rutadatos' ),
		array('db' => 'rutacasa', 'dt' => 'rutacasa' )
    );

    $sql_details = array(
        'user' => $mysqluser_exp,
        'pass' => $mysqlpass_exp,
        'db'   => $mysqldb_exp,
        'host' => $mysqlserver_exp
    );

	$baseSql = "SELECT *
		        FROM expedientes.empresas a";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../../ssp.class.php');

    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







