<?php
include_once("../../checklogin.php");
if($loggedIn == false){
	$error_msg="Se ha perdido la sesion favor de iniciarla de nuevo";
	exit(json_encode(array("error" => $error_msg)));
}

/*
 * Example PHP implementation used for the index.html example
 */

// DataTables PHP library
include( "../../editor/php/DataTables.php" );

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

Editor::inst( $db, 'lineast' )
	->fields(
		Field::inst( 'lineast.numlinea as id_linea' ),
		Field::inst( 'lineast.Nombre as nombre_linea' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			})
			->validator( 'Validate::required' )
	)
	->pkey('lineast.numlinea')
	//->leftJoin( 'cltes_expo as cli', 'cli.gcliente', '=', 'ent.numcliente' )
	->where( function ( $q) {
		$q->where('lineast.habilitado','1','=');
	} )
	->on( 'preCreate', function ( $editor, $values) {
		$consecutivo = $editor->db()
			->select( 'lineast', 'max(numlinea) as consecutivo' )
			->fetch();
		$siguiente=$consecutivo['consecutivo']+1;
		$editor
            ->field( 'id_linea' )
            ->setValue($siguiente);
    } )
	->on( 'preRemove', function ( $editor,$id_row, $values) {
       
    } )
	->process( $_POST )
	->json();
