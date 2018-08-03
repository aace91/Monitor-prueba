<?php
include_once('./../../../checklogin.php');
include('./../../../connect_casa.php');
if ($loggedIn == false){
	echo '500';
} else{
	//Parametros de entrada
	//$sFiltro = $_POST['sFiltro'];
	//Definicion de variables
    $sIndexColumn = "NUM_REFE";
    $sTable = "SAAIO_PEDIME";
 
    $aColumns = array();
	$aColumnsWhere = array(); //Para las opciones de filtrado
    $aColumnsOrder = array(); //Para las opciones de ordenado
    //COLUMNAS
	array_push( $aColumns, 'NUM_REFE' );
	array_push( $aColumns, 'OPERACION' );
	array_push( $aColumns, 'ADU_DESP' );
	array_push( $aColumns, 'PAT_AGEN' );
	array_push( $aColumns, 'NUM_PEDI' );
	array_push( $aColumns, 'NUM_PEDI' );
	array_push( $aColumns, 'FEC_ENTR' );
	array_push( $aColumns, 'FEC_PAGO' );
	
	array_push( $aColumnsWhere, 'NUM_REFE' );
	array_push( $aColumnsWhere, 'OPERACION' );
	array_push( $aColumnsWhere, 'ADU_DESP' );
	array_push( $aColumnsWhere, 'PAT_AGEN' );
	array_push( $aColumnsWhere, 'NUM_PEDI' );

    array_push( $aColumnsOrder, 'NUM_REFE' );
    array_push( $aColumnsOrder, 'OPERACION' );
    array_push( $aColumnsOrder, 'ADU_DESP' );
    array_push( $aColumnsOrder, 'PAT_AGEN' );
    array_push( $aColumnsOrder, 'NUM_PEDI' );
	
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
	                  NUM_REFE, 
					  CASE WHEN IMP_EXPO = 1 THEN 'IMPO' ELSE 'EXPO' END OPERACION, 
					  ADU_DESP, PAT_AGEN, NUM_PEDI, 
					  FEC_ENTR, CVE_PEDI, FEC_PAGO ";
	
	$sFrom = "FROM SAAIO_PEDIME ";
	
	$sAuxWhere = "WHERE CVE_IMPO = 'KIA' AND FIR_PAGO IS NOT NULL ";
	
	$sQuery = $sSelect . " " . $sFrom . " " . $sAuxWhere . " " . $sOrder;
    //error_log($sQuery);
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
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            //error_log($aColumns[$i]);
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
	
    echo json_encode( $output );
}







