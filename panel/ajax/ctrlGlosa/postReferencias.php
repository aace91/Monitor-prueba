<?php
include_once('./../../../checklogin.php');

if ($loggedIn == false){
	echo '500';
} else{
	require('./../../../connect_casa.php');
	
	$sFiltro = $_POST['sFiltro'];

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Easy set variables
     */
     
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "a.NUM_REFE";
     
    /* DB table to use */
    $sTable = "SAAIO_PEDIME";
 
    /* Database connection information 
    $gaSql['user']       = "";
    $gaSql['password']   = "";
    $gaSql['dsn']        = "cnxpedimentos";
    $gaSql['database']   = "";*/
 
    /*
    * Columns
    * If you don't want all of the columns displayed you need to hardcode $aColumns array with your elements.
    * If not this will grab all the columns associated with $sTable
    */
    $aColumns = array();
	$aColumnsWhere = array(); //Para las opciones de filtrado
    $aColumnsOrder = array(); //Para las opciones de ordenado
     
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP server-side, there is
     * no need to edit below this line
     */
     
    /*
     * ODBC connection
     
    $gaSql['link'] =  odbc_connect( $gaSql['dsn'], $gaSql['user'], $gaSql['password']  ) or
        die( "Connection failed: " . odbc_error() );
	if (!$gaSql['link']){
		exit("Ocurrio un error tratando de conectarse con el origen de datos de pedimentos".odbc_error());
	}*/
	
	array_push( $aColumns, 'NUM_REFE' );
	array_push( $aColumns, 'REFGLOSA' );
	array_push( $aColumns, 'FECHA_ALTA' );
	array_push( $aColumns, 'OBSERVACIONES' );
	array_push( $aColumns, 'USUARIO' );
	array_push( $aColumns, 'EJECUTIVO' );
	array_push( $aColumns, 'CLIENTE' );
	array_push( $aColumns, 'LISTO' );
	
	array_push( $aColumnsWhere, 'a.NUM_REFE' );
	array_push( $aColumnsWhere, 'b.FECHA_ALTA' );
	array_push( $aColumnsWhere, 'b.USUARIO' );
	array_push( $aColumnsWhere, 'd.NOMBRE' );
	array_push( $aColumnsWhere, 'e.NOM_IMP' );

    array_push( $aColumnsOrder, 'NUM_REFE' );
    array_push( $aColumnsOrder, 'FECHA_ALTA' );
    array_push( $aColumnsOrder, 'EJECUTIVO' );
    array_push( $aColumnsOrder, 'CLIENTE' );
    array_push( $aColumnsOrder, 'OBSERVACIONES' );
    array_push( $aColumnsOrder, 'LISTO' );
	
    /*
     * Paging
     * How rows are limited depends on which database you're using. This will need to be altered depending on your database.
     */
 
    $sLimit = "";

    if (intval( $_POST['length'] ) > 0) {
        $sLimit = "FIRST " . intval( $_POST['length'] )." SKIP " . intval( $_POST['start'] ) ;
    }
     
    /*
     * Ordering
     */
 
    $sOrder = "";
    if ( isset( $_POST['order'] ) )
    {
        $sOrder = "ORDER BY  ";
        for ( $i=0 ; $i<count($_POST['order']) ; $i++ )
        {
			$columnIdx = intval($_POST['order'][$i]['column']);
			$requestColumn = $_POST['columns'][$columnIdx];
            if ( $requestColumn['orderable'] == "true" )
            {
                $sOrder .= $aColumnsOrder[ $columnIdx ]."
                    ".($_POST['order'][$i]['dir']==='asc' ? 'asc' : 'desc') .", ";
            }
        }
         
        $sOrder = substr_replace( $sOrder, "", -2 );
        if ( $sOrder == "ORDER BY" )
        {
            $sOrder = "";
        }
    }
     
     
    /*
     * Filtering
     * Unchanged: This is standard SQL
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     */
	
    /*$sWhere = " WHERE (fir_elec is not null or fir_reme is not null) and cve_impo='".$idcliped."'".$fechas;
	$sWhere1 = " WHERE (fir_elec is not null or fir_reme is not null) and cve_impo='".$idcliped."'".$fechas;*/
	$sWhere = '';
    if ( isset($_POST['search']) && $_POST['search']['value'] != "" )
    {
        $sWhere .= " AND(";
        for ( $i=0 ; $i<count($aColumnsWhere) ; $i++ )
        {
            $sWhere .= $aColumnsWhere[$i]." LIKE '%".addslashes( $_POST['search']['value'] )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= ')';
    }
     
    /* Individual column filtering */
     
    for ( $i=0 ; $i<count($aColumnsWhere) ; $i++ )
    {
        if ( isset($_POST['searchable'.$i]) && $_POST['searchable'.$i] == "true" && $_POST['search_'.$i] != '' )
        {
            $sWhere .= " AND ";
            $sWhere .= $aColumnsWhere[$i]." LIKE '%".addslashes($_POST['search_'.$i])."%' ";
        }
    }
     
     
    /*
     * SQL queries
     * Get data to display
     * Different implementations of SQL use different ways to limit a data set. This would need to be altered depending on your database.
     */
		
	$sSelect = "SELECT " . $sLimit . " 
	                   a.NUM_REFE, b.NUM_REFE AS REFGLOSA, b.FECHA_ALTA, b.USUARIO, d.NOMBRE AS EJECUTIVO, a.CVE_IMPO, e.NOM_IMP AS CLIENTE, b.LISTO,
					   (SELECT COUNT(c.NUM_REFE)
					    FROM GAB_GLOSA_DET c
					    WHERE c.NUM_REFE = a.NUM_REFE) AS OBSERVACIONES";
	
	$sFrom = "FROM GAB_GLOSA b LEFT join
				   SAAIO_PEDIME a ON a.NUM_REFE = b.NUM_REFE LEFT JOIN
    			   SISSEG_USUARI d ON b.USUARIO = d.LOGIN LEFT JOIN
     			   CTRAC_CLIENT e ON a.CVE_IMPO = e.CVE_IMP";
	
	$sAuxWhere = "";
	
    switch ($sFiltro) {
        case 'pendientes':
            $sAuxWhere .= "WHERE b.LISTO IS NULL ".$sWhere;
            break;

        case 'listos':
            $sAuxWhere .= "WHERE b.LISTO IS NOT NULL ".$sWhere;
            break;

        case 'todo':
           $sAuxWhere .= "WHERE a.NUM_REFE IS NOT NULL ".$sWhere;
            break;

        default:
            $sAuxWhere .= "WHERE a.NUM_REFE IS NOT NULL ".$sWhere;
            break;
    }
	
	$sQuery = $sSelect . " " . $sFrom . " " . $sAuxWhere . " " . $sOrder;
	$rResult = odbc_exec($odbccasa,$sQuery) or die("$sQuery: " . odbc_error());
    
    /* Data set length after filtering */
    /* odbc_num_rows isn't supported by all ODBC drivers, so just run a count */
    /* This shouldn't need to be changed */
    //$sQueryCnt = "SELECT count(*) as counter FROM $sTable $sWhere";
	$sQueryCnt = "SELECT count(*) as counter " . $sFrom . " " . $sAuxWhere;
	$result = odbc_exec ($odbccasa, $sQueryCnt) or die(odbc_error());
    if ($result!=false){
		while(odbc_fetch_row($result)){
			$iFilteredTotal = odbc_result($result,"counter");
		}		
	}else{
		exit(odbc_error());
	}    
    
    /* Total data set length */
    /* odbc_num_rows isn't supported by all ODBC drivers, so just run a count */
    /* This shouldn't need to be changed */

	//$sQuery = "SELECT COUNT(".$sIndexColumn.") as counter FROM  $sTable $sWhere";
	$sQuery = "SELECT COUNT(".$sIndexColumn.") as counter " . $sFrom . " " . $sAuxWhere;
    
	$result = odbc_exec ($odbccasa, $sQuery) or die(odbc_error());
    if ($result!=false){
		while(odbc_fetch_row($result)){
			$iTotal = odbc_result($result,"counter");
		}		
	}else{
		exit(odbc_error());
	}
     
    /*
     * Output
     * Unchanged
     */
	$output = array(
		"draw"            => intval( $_POST['draw'] ),
		"recordsTotal"    => intval( $iTotal ),
		"recordsFiltered" => intval( $iFilteredTotal ),
		"data"            => array(),
		"query"			  => $sQuery
	);

    while(odbc_fetch_row($rResult)){
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
            if ( $aColumns[$i] == "version" )
            {
                /* Special output formatting for 'version' column */
                $row[$aColumns[$i]] = (odbc_result($rResult,$aColumns[$i])=="0") ? '-' : odbc_result($rResult,$aColumns[$i]);
            }
            else if ( $aColumns[$i] != ' ' )
            {
                /* General output */
                $row[$aColumns[$i]] = utf8_encode(odbc_result($rResult,$aColumns[$i]));
            }
		}
		$output['data'][] = $row;
	}	
    
    /*while(odbc_fetch_row($rResult)){	
		$row = array();
		for ( $i=0, $ien=count($aColumns) ; $i<$ien ; $i++ ) {
			$row = array();
			$row[ $aColumns[$i] ] = odbc_result($rResult,$aColumns[$i]);
			$output['data'][] = $row;
		}
	}*/
         
    echo json_encode( $output );
}







