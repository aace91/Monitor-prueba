<?php
include_once('./../../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	$sBuscarPor = $_POST['sBuscarPor'];
	$sTexto = $_POST['sTexto'];
	
	// DB table to use
	$table = 'seguimiento_pedime';
	$primaryKey = 'id_registro';    
    $columns = array(	
		array('db' => 'id_registro', 'dt' => 'id_registro' ),
		array('db' => 'referencia_saaio', 'dt' => 'referencia' ),
		array('db' => 'pedimento', 'dt' => 'pedimento' ),
		array('db' => 'cuenta_gastos', 'dt' => 'cuenta_gastos' ),
		array('db' => 'trafico', 'dt' => 'trafico' ),
		array('db' => 'caja', 'dt' => 'caja' ),
		array('db' => 'comentarios', 'dt' => 'comentarios' ),
		array('db' => 'status_pedime', 'dt' => 'status', 'formatter' => function( $val, $row ) {
                if ($val==''){
					return '';
				}else{			
					if ($val == 'Archivado') {
						return $val . ' (Caja: '.$row[5].')'; 
					} else {						
						return $val; 
					}
				}               
        })
    );

    $sql_details = array(
        'user' => $mysqluser_exp,
        'pass' => $mysqlpass_exp,
        'db'   => $mysqldb_exp,
        'host' => $mysqlserver_exp
    );

	$baseSql = "SELECT a.id_registro
	                  ,a.referencia_saaio
					  ,a.pedimento
					  ,CONCAT(a.tipo_mov,'-',a.no_banco,'-',a.no_mov) AS cuenta_gastos
					  ,a.referencia AS trafico
					  ,CASE WHEN a.fecha_archivo_archivado IS NOT NULL THEN 'Archivado'
							WHEN a.fecha_cp_entrega IS NOT NULL THEN 'Pendiente por Archivar'
							WHEN a.fecha_cp_digitalizado IS NOT NULL THEN 'Digitalizado (Pendiente por Entregar)'
							WHEN a.fecha_cc_entrega IS NOT NULL THEN 'Cuentas por Pagar (Digitalizando)'
							WHEN a.fecha_recepcion_entrega IS NOT NULL THEN 'Cuentas por Cobrar (Facturando)'
							WHEN a.fecha_recepcion_captura IS NOT NULL THEN 'Pedimento Pagado'
					   END AS status_pedime
					  ,(SELECT b.id_caja
						FROM expedientes.expedientes b
						WHERE b.tipo_mov = a.tipo_mov AND 
							  b.no_banco = a.no_banco AND 
							  b.no_mov = a.no_mov AND
							  b.referencia = a.referencia) AS caja
					  ,a.comentarios
				FROM expedientes.seguimiento_pedime a";
	
	switch ($sBuscarPor) {
		case '-1':
			$baseSql .= " WHERE id_registro=-1";
			break;
		case 'referencia':
			$baseSql .= " WHERE referencia_saaio='".$sTexto."'";
			break;
		case 'pedimento':
			$baseSql .= " WHERE pedimento='".$sTexto."'";
			break;
		case 'cuenta_gastos':
			$baseSql .= " WHERE CONCAT(tipo_mov,'-',no_banco,'-',no_mov)='".$sTexto."'";
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







