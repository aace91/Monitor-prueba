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
	DataTables\Editor\Options,
	DataTables\Editor\Upload,
	DataTables\Editor\Validate,
	DataTables\Editor\ValidateOptions;

// Build our Editor instance and process the data coming from _POST


Editor::inst( $db, 'correos' )
	->fields(
		Field::inst( 'correos.row as id_correo' )
			->set('false'),
		Field::inst( 'correos.no_cte as id_cliente' ),
		Field::inst( 'correos.correo as correo' )
			->validator( Validate::email(
				ValidateOptions::inst()
					->allowEmpty( false )
					->optional( false )
				)
			),
		Field::inst( 'correos.id_tpo_correo as tpo' )
			->options( Options::inst()
				->table( 'tpo_correo' )
				->value( 'id_tpo_correo' )
				->label( 'descripcion' )
				->order('id_tpo_correo')
			)
			->validator( Validate::dbValues(
				ValidateOptions::inst()
					->allowEmpty( false )
					->optional( false )
				) 
			),
		Field::inst( 'tpo_correo.descripcion as tpo_desc' )
			->set('false')
	)
	->pkey('correos.row')
	->leftJoin( 'tpo_correo', 'correos.id_tpo_correo', '=', 'tpo_correo.id_tpo_correo' )
	->where( function ( $q) {
		if(isset($_POST['id_cliente']))
			$q->where('correos.no_cte',$_POST['id_cliente'],'=');
	} )
	->on( 'preCreate', function ( $editor, $values ) {
		$editor
            ->field( 'id_cliente' )
            ->setValue($_POST['id_cliente']);
	})
	->process( $_POST )
	->json();
