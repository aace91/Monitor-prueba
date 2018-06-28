<?php
include_once("../../../checklogin.php");
if($loggedIn == false){
	$error_msg="Se ha perdido la sesion favor de iniciarla de nuevo";
	exit(json_encode(array("error" => $error_msg)));
}

/*
 * Example PHP implementation used for the index.html example
 */

// DataTables PHP library
$sql_details = array(
	"type" => "Mysql",  // Database type: "Mysql", "Postgres", "Sqlite" or "Sqlserver"
	"user" => $mysqluser_sab07,       // Database user name
	"pass" => $mysqlpass_sab07,       // Database password
	"host" => $mysqlserver2_sab07,       // Database host
	"port" => $mysqlport_sab07,       // Database connection port (can be left empty for default)
	"db"   => $mysqldb_sab07,       // Database name
	"dsn"  => ""        // PHP DSN extra information. Set as `charset=utf8` if you are using MySQL
);

include( "../../../editor/php/DataTables.php" );

// Alias Editor classes so they are easy to use
use
	DataTables\Editor,
	DataTables\Editor\Field,
	DataTables\Editor\Format,
	DataTables\Editor\Join,
	DataTables\Editor\Mjoin,
	DataTables\Editor\Options,
	DataTables\Editor\Upload,
	DataTables\Editor\Validate;

// Build our Editor instance and process the data coming from _POST


Editor::inst( $db, 'aacte as b' )
	->fields(
		Field::inst( 'trim(b.no_cte) as id_cliente' ),
		Field::inst( 'b.nombre as nombre' ),
		Field::inst( 'a.correos as correos' )
	)
	->pkey('b.no_cte')
	->leftJoin( 'list_correos as a', 'b.no_cte', '=', 'a.no_cte' )
	->where( function ( $q) {
		
	} )
	->process( $_POST )
	->json();
