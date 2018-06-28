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

Editor::inst( $db, 'accesostransfers' )
	->fields(
		Field::inst( 'accesostransfers.id_acceso as id_acceso' ),
		Field::inst( 'accesostransfers.usuario as usuario' ),
		Field::inst( 'accesostransfers.password as password' ),
		Field::inst( 'accesostransfers.notransfer as notransfer' )
			->options( Options::inst()
				->table( 'transfers_expo' )
				->value( 'notransfer' )
				->label( 'nombretransfer' )
				->where( function ($q) {
					$q->where( 'habilitado', '1');
				})
			)
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return 0;
				}
				return $val;
			}),
		Field::inst( 'transfers_expo.nombretransfer as transfer_nom' )
	)
	->leftJoin( 'transfers_expo as transfers_expo', 'accesostransfers.notransfer', '=', 'transfers_expo.notransfer' )
	->pkey('accesostransfers.id_acceso')
	->debug(true)
	->process( $_POST )
	->json();
