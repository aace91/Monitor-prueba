<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{	
	$sIdEmpresa = $_POST['sIdEmpresa'];
	
	// DB table to use
	$table = 'seguimiento_pedime';
	$primaryKey = 'id_empresa';    
    $columns = array(	
		array('db' => 'id_empresa', 'dt' => 'id_empresa' ),
		array('db' => 'cuenta_gastos', 'dt' => 'cuenta_gastos' ),
		array('db' => 'referencia', 'dt' => 'referencia' ),
		array('db' => 'impo_expo', 'dt' => 'impo_expo' ),
		array('db' => 'pedimento', 'dt' => 'pedimento', 'formatter' => function( $d, $row ) {
			return (($d=='')? '' : $d);
        }),
		array('db' => 'no_mov', 'dt' => 'no_mov' ),
		array('db' => 'cliente_nombre', 'dt' => 'cliente_nombre' )
    );

    $sql_details = array(
        'user' => $mysqluser_exp,
        'pass' => $mysqlpass_exp,
        'db'   => $mysqldb_exp,
        'host' => $mysqlserver_exp
    );

	$baseSql = "SELECT a.id_empresa, CONCAT(a.tipo_mov,'-',a.no_banco,'-',a.no_mov) AS cuenta_gastos,
                       b.trafico AS referencia, IF(a.impo_expo = 1, 'IMPORTACION', 'EXPORTACION') AS impo_expo,
                       b.pedimento, b.no_mov, c.nombre AS cliente_nombre
                FROM expedientes.seguimiento_pedime a INNER JOIN
                     ".(($sIdEmpresa == '1'? 'contagab': 'contasab')).".aacgmex AS b ON a.tipo_mov = b.tipo_mov AND
                                              a.no_banco = b.no_banco AND
                                              a.no_mov = b.no_mov INNER JOIN 
                     ".(($sIdEmpresa == '1'? 'contagab': 'contasab')).".aacte AS c ON c.no_cte = b.no_cte
                WHERE a.id_empresa=".$sIdEmpresa." AND
                      a.fecha_cp_entrega IS NOT NULL AND
                      a.fecha_archivo_archivado IS NULL 
                GROUP BY cuenta_gastos, referencia
				UNION
                SELECT a.id_empresa, CONCAT(a.tipo_mov,'-',a.no_banco,'-',a.no_mov) AS cuenta_gastos,
                       b.trafico AS referencia, IF(a.impo_expo = 1, 'IMPORTACION', 'EXPORTACION') AS impo_expo,
                       b.pedimento, b.no_mov, c.nombre AS cliente_nombre
                FROM expedientes.seguimiento_pedime a INNER JOIN 
                     ".(($sIdEmpresa == '1'? 'contagab': 'contasab')).".notaremision_dbf AS b ON a.tipo_mov = b.tipo_mov AND
                                              a.no_banco = b.no_banco AND
                                              a.no_mov = b.no_mov INNER JOIN 
                     contagab.aacte AS c ON c.no_cte = b.no_cte 
                WHERE a.id_empresa=1 AND
                      a.fecha_cp_entrega IS NOT NULL AND
                      a.fecha_archivo_archivado IS NULL 
                GROUP BY cuenta_gastos, referencia";
					
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require('../../../ssp.class.php');

    $_POST['draw']=1;
    $_POST['columns']=[];
    
    echo json_encode(
            SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
    );
}







