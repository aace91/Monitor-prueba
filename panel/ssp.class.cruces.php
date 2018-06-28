<?php
 //include('verificar_estado_aduanamx_referencias.php');
// if($loggedIn == false){ header("Location: ./../login.php"); }
/*
 * Helper functions for building a DataTables server-side processing SQL query
 *
 * The static functions in this class are just helper functions to help build
 * the SQL used in the DataTables demo server-side processing scripts. These
 * functions obviously do not represent all that can be done with server-side
 * processing, they are intentionally simple to show how it works. More complex
 * server-side processing operations will likely require a custom script.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */


// REMOVE THIS BLOCK - used for DataTables test environment only!
$file = $_SERVER['DOCUMENT_ROOT'].'/datatables/mysql.php';
if ( is_file( $file ) ) {
	include( $file );
}


class SSP {
	/**
	 * Create the data output array for the DataTables rows
	 *
	 *  @param  array $columns Column information array
	 *  @param  array $data    Data from the SQL get
	 *  @return array          Formatted data in a row based format
	 */
	static function data_output ( $sql_details, $request, $columns, $data )
	{
		$out = array();

		for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
			/*Revisar Estados Aduana Antes de Agregar el Registro al Array de Regreso*/
			$resEdo = self::verificar_estado_aduanamx_referencias($sql_details,$data[$i]['estado']);
			if(($request['estado'] == 'cum' && trim($resEdo) != '') ||
				($request['estado'] == 'prc' && trim($resEdo) == '') || 
				($request['estado'] != 'cum' && $request['estado'] != 'prc')){
				for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
					$column = $columns[$j];
					// Is there a formatter?
					if ( isset( $column['formatter'] ) ) {
						$row[ $column['dt'] ] = $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
					}
					else {
						$row[ $column['dt'] ] = $data[$i][ $columns[$j]['db'] ];
					}
				}
				//error_log(json_encode($row));
				$row['estado'] = $resEdo;
				$out[] = $row;
			}
		}

		return $out;
	}


	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL limit clause
	 */
	static function limit ( $request, $columns )
	{
		$limit = '';

		if ( isset($request['start']) && $request['length'] != -1 ) {
			$limit = "LIMIT ".intval($request['start']).", ".intval($request['length']);
		}

		return $limit;
	}


	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL order by clause
	 */
	static function order ( $request, $columns )
	{
		$order = '';

		if ( isset($request['order']) && count($request['order']) ) {
			$orderBy = array();
			$dtColumns = self::pluck( $columns, 'dt' );

			for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
				// Convert the column index into the column data property
				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];

				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['orderable'] == 'true' ) {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = '`'.$column['db'].'` '.$dir;
				}
			}

			$order = 'ORDER BY '.implode(', ', $orderBy);
		}

		return $order;
	}


	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @param  array $bindings Array of values for PDO bindings, used in the
	 *    sql_exec() function
	 *  @return string SQL where clause
	 */
	static function filter ( $request, $columns, &$bindings )
	{
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = self::pluck( $columns, 'dt' );

		if ( isset($request['search']) && $request['search']['value'] != '' ) {
			$str = $request['search']['value'];

			for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['searchable'] == 'true' ) {
					$binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
					$globalSearch[] = "`".$column['db']."` LIKE ".$binding;
				}
			}
		}

		// Individual column filtering
		for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $request['columns'][$i];
			$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $columns[ $columnIdx ];

			$str = $requestColumn['search']['value'];

			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
				$columnSearch[] = "`".$column['db']."` LIKE ".$binding;
			}
		}

		// Combine the filters into a single string
		$where = '';

		if ( count( $globalSearch ) ) {
			$where = '('.implode(' OR ', $globalSearch).')';
		}

		if ( count( $columnSearch ) ) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where .' AND '. implode(' AND ', $columnSearch);
		}

		if ( $where !== '' ) {
			$where = 'WHERE '.$where;
		}

		return $where;
	}


	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $sql_details SQL connection details - see sql_connect()
	 *  @param  string $table SQL table to query
	 *  @param  string $primaryKey Primary key of the table
	 *  @param  array $columns Column information array
	 *  @return array          Server-side processing response array
	 */
	static function simple ( $request, $sql_details, $table, $primaryKey, $columns )
	{
		global $baseSql;
		$bindings = array();
		$db = self::sql_connect( $sql_details );

		// Build the SQL query string from the request
		$limit = self::limit( $request, $columns );
		$order = self::order( $request, $columns );
		$where = self::filter( $request, $columns, $bindings );

		$data = self::sql_exec( $db, $bindings,
			"SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", self::pluck($columns, 'db'))."`
			 FROM ($baseSql) as consulta
			 $where
			 $order
			 $limit"
		);

		// Data set length after filtering
		$resFilterLength = self::sql_exec( $db,
			"SELECT FOUND_ROWS()"
		);
		$recordsFiltered = $resFilterLength[0][0];

		// Total data set length
		$resTotalLength = self::sql_exec( $db,
			"SELECT COUNT(`{$primaryKey}`)
			 FROM   ($baseSql) as consulta"
		);
		$recordsTotal = $resTotalLength[0][0];


		/*
		 * Output
		 */
		return array(
			"draw"            => intval( $request['draw'] ),
			"recordsTotal"    => intval( $recordsTotal ),
			"recordsFiltered" => intval( $recordsFiltered ),
			"data"            => self::data_output( $sql_details,$request,$columns, $data ),
			"consulta"		  => $baseSql
		);
	}


	/**
	 * Connect to the database
	 *
	 * @param  array $sql_details SQL server connection details array, with the
	 *   properties:
	 *     * host - host name
	 *     * db   - database name
	 *     * user - user name
	 *     * pass - user password
	 * @return resource Database connection handle
	 */
	static function sql_connect ( $sql_details )
	{
		try {
			$db = @new PDO(
				"mysql:host={$sql_details['host']};dbname={$sql_details['db']}",
				$sql_details['user'],
				$sql_details['pass'],
				array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION )
			);
		}
		catch (PDOException $e) {
			self::fatal(
				"An error occurred while connecting to the database. ".
				"The error reported by the server was: ".$e->getMessage()
			);
		}

		return $db;
	}


	/**
	 * Execute an SQL query on the database
	 *
	 * @param  resource $db  Database handler
	 * @param  array    $bindings Array of PDO binding values from bind() to be
	 *   used for safely escaping strings. Note that this can be given as the
	 *   SQL query string if no bindings are required.
	 * @param  string   $sql SQL query to execute.
	 * @return array         Result from the query (all rows)
	 */
	static function sql_exec ( $db, $bindings, $sql=null )
	{
		// Argument shifting
		if ( $sql === null ) {
			$sql = $bindings;
		}

		$stmt = $db->prepare( $sql );
		//echo $sql;

		// Bind parameters
		if ( is_array( $bindings ) ) {
			for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) {
				$binding = $bindings[$i];
				$stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
			}
		}

		// Execute
		try {
			$stmt->execute();
		}
		catch (PDOException $e) {
			self::fatal( "An SQL error occurred: ".$e->getMessage() );
		}

		// Return all
		return $stmt->fetchAll();
	}


	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Internal methods
	 */

	/**
	 * Throw a fatal error.
	 *
	 * This writes out an error message in a JSON string which DataTables will
	 * see and show to the user in the browser.
	 *
	 * @param  string $msg Message to send to the client
	 */
	static function fatal ( $msg )
	{
		echo json_encode( array( 
			"error" => $msg
		) );

		exit(0);
	}

	/**
	 * Create a PDO binding key which can be used for escaping variables safely
	 * when executing a query with sql_exec()
	 *
	 * @param  array &$a    Array of bindings
	 * @param  *      $val  Value to bind
	 * @param  int    $type PDO field type
	 * @return string       Bound key to be used in the SQL where this parameter
	 *   would be used.
	 */
	static function bind ( &$a, $val, $type )
	{
		$key = ':binding_'.count( $a );

		$a[] = array(
			'key' => $key,
			'val' => $val,
			'type' => $type
		);

		return $key;
	}


	/**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	static function pluck ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
			$out[] = $a[$i][$prop];
		}

		return $out;
	}
	/**
	 *
	 *
	 *@Parm string $sReferencias;
	 *@return string Estados
	*/
	static function verificar_estado_aduanamx_referencias($sql_details, $sReferencias){
		if($sReferencias != ''){// Si existe salida se obtienen las referencias en la columna [estado]
			$aReferencias = explode(',',$sReferencias);
			for($i = 0; $i<count($aReferencias); $i++){
				//$odbccasa = $sql_details['odbccasa'];
				$odbccasa=odbc_connect($sql_details['dns_casa'], '', '');
				if (!$odbccasa){
					self::fatal("Error al conectarse a la base de datos de pedimentos [CASA].[ssp.class.cruces.php]");
				}
				$aDetCruce = explode('|',$aReferencias[$i]);
				$qCasa = "SELECT a.ADU_DESP, a.PAT_AGEN, a.NUM_PEDI,f.NUM_REM,
								CASE WHEN a.FIR_REME IS NULL THEN '' ELSE a.FIR_REME END AS FIR_REME
							FROM SAAIO_PEDIME a
								INNER JOIN SAAIO_FACTUR f ON
									a.NUM_REFE = f.NUM_REFE AND 
									f.CONS_FACT = ".$aDetCruce[1]."
							WHERE a.NUM_REFE='".$aDetCruce[0]."'
							GROUP BY a.ADU_DESP, a.PAT_AGEN, a.NUM_PEDI,a.FIR_REME,f.NUM_REM";
				
				$resped = odbc_exec ($odbccasa, $qCasa);
				if ($resped == false){
					self::fatal("Error al consultar pedimento de las referencias. BD.CASA.".odbc_error().$qCasa);
				}else{
					$sWhere = ''; $nItem = 0;
					while(odbc_fetch_row($resped)){
						if($nItem != 0){$sWhere .= ' OR ';}						
						$Aduana = odbc_result($resped,"ADU_DESP");
						$Patente = odbc_result($resped,"PAT_AGEN");
						$Pedimento = odbc_result($resped,"NUM_PEDI");
						$Remesa = odbc_result($resped,"NUM_REM");
						$Firma_Reme = odbc_result($resped,"FIR_REME");
						
						if($Firma_Reme == ''){
							$sWhere = "(a.pedimento='".$Pedimento."' AND a.patente='".$Patente."' AND a.aduana='".$Aduana."' AND a.factura = '')";
						}else{
							$sWhere = "(a.pedimento='".$Pedimento."' AND a.patente='".$Patente."' AND a.aduana='".$Aduana."'  AND a.factura = '".$Remesa."')";
						}
					}
					if($sWhere != ''){
						
						$sReturn = '';
						$mysqlserver = $sql_details['host_ldo']; $mysqluser = $sql_details['user_ldo'];
						$mysqlpass = $sql_details['pass_ldo']; $mysqldb = $sql_details['db_ldo'];
						
						$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
						if ($cmysqli->connect_error) {
							self::fatal("Error al conectarse a la base de datos de nuevo laredo. ".$cmysqli_sab07->connect_error);
						}
						
						$baseSql = "SELECT a.id_sit_pedime,
								(SELECT CONCAT(z.id_estado_detalle, '-', y.descripcion, '-' ,DATE_FORMAT(z.fecha, '%d/%m/%Y %H:%i'))
									FROM casa.soia_eventos AS z INNER JOIN
										 casa.soia_estados AS y ON z.id_estado_detalle = y.id_estado
									WHERE z.id_sit_pedime=a.id_sit_pedime AND 
										  z.id_estado_detalle IN (310, 510, 320, 520)
									ORDER BY z.fecha DESC
									LIMIT 1) AS evento
						FROM casa.soia_situacion_pedime AS a INNER JOIN
							 casa.soia_estados AS b ON b.id_estado = a.id_estado
						WHERE ".$sWhere ."
						ORDER BY a.pedimento, a.num_refe, CAST(a.factura as unsigned) ASC";
						
						$query = mysqli_query($cmysqli,$baseSql);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							mysqli_close($cmysqli);
							self::fatal('Error al consultar estados del SOIA.['.$error.']');
						}
						
						while($row = mysqli_fetch_array($query)){
							if(trim($row['evento']) != '') {
								$sReturn = $row['id_sit_pedime'].'|'.$row['evento'];
							}
						}				
						return $sReturn;
					}
				}
			}
			return '';//Sin estado para las referencias
		}else{
			return $sReferencias;
		}
		return '';
	}

}

