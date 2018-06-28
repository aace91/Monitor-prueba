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

Editor::inst( $db, 'cltes_expo' )
	->fields(
		Field::inst( 'cltes_expo.gcliente as id_cliente' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			})
			->validator( 'Validate::required' ),
		Field::inst( 'cltes_expo.cnombre as nombre_cliente' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper(html_entity_decode($val));
			})
			->validator( 'Validate::required' ),
		Field::inst( 'cltes_expo.rfc as rfc' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			})
			->validator( 'Validate::required' ),
		Field::inst( 'cltes_expo.ejecutivo_id as ejecutivo_id' )
				->options( Options::inst()
	        ->table( 'tblusua' )
	        ->value( 'Usuario_id' )
	        ->label( 'usunombre' )
	        ->where( function ($q) {
	            $q->where( 'usunivel', 'E', '=' );
							$q->where( 'usupasswd', '%BAJA%', 'NOT LIKE' );
	        })
				)
				->setFormatter(function ( $val, $data, $opts ) {
					if($val==''){
						$val=NULL;
					}
					return $val;
				})
	)
	->join(
		Mjoin::inst( 'tblusua' )
			->link( 'cltes_expo.gcliente', 'expos_clte_ejecutivo.gcliente' )
			->link( 'tblusua.Usuario_id', 'expos_clte_ejecutivo.Usuario_id' )
			->fields(
				Field::inst( 'Usuario_id' )
				->options( Options::inst()
					->table( 'tblusua' )
					->value( 'Usuario_id' )
					->label( 'usunombre' )
					->where( function ($q) {
							$q->where( 'usunivel', 'E', '=' );
							$q->where( 'usupasswd', '%BAJA%', 'NOT LIKE' );
					})
				)
			)
	)
	->pkey('cltes_expo.gcliente')
	//->leftJoin( 'cltes_expo as cli', 'cli.gcliente', '=', 'ent.numcliente' )
	->where( function ( $q) {
		$q->where('cltes_expo.habilitado','1','=');
	} )
	->on( 'preCreate', function ( $editor, $values) {
		/*$consecutivo = $editor->db()
			->select( 'lineast', 'max(numlinea) as consecutivo' )
			->fetch();
		$siguiente=$consecutivo['consecutivo']+1;*/
		/*$editor
            ->field( 'id_linea' )
            ->setValue($siguiente);*/
    } )
	->on( 'preEdit', function ( $editor, $id, $values) {} )
	->on( 'preRemove', function ( $editor,$id_row, $values) {} )
	->process( $_POST )
	->json();
