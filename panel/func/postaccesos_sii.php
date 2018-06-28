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

Editor::inst( $db, 'accesos' )
	->fields(
		Field::inst( 'accesos.id_acceso as id_acceso' ),
		Field::inst( 'accesos.usuario as usuario' ),
		Field::inst( 'accesos.password as password' ),
		Field::inst( 'accesos.admin as admin' ),
		Field::inst( 'accesos.cliente_id as cliente_impo' )
			->options( Options::inst()
				->table( 'clientes' )
				->value( 'cliente_id' )
				->label( 'nom' )
				->where( function ($q) {
					$q->where( 'status', NULL);
					$q->or_where( 'status', '', '=' );
				})
			)
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return 0;
				}
				return $val;
			}),
		Field::inst( 'accesos.cliente_id_nb as cliente_nb' )
			->options( Options::inst()
				->table( 'clientes_nb' )
				->value( 'cliente_id' )
				->label( 'nom' )
			)
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return 0;
				}
				return $val;
			}),
		Field::inst( 'accesos.id_ped as cliente_ped' )
			->options( Options::inst()
				->table( 'casa.ctrac_client' )
				->value( 'cve_imp' )
				->label( 'nom_imp' )
				->where( function ($q) {
					$q->where( 'baj_imp', NULL );
				})
			)
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return '0';
				}
				return $val;
			}),
		Field::inst( 'accesos.id_expo as cliente_expo' )
			->options( Options::inst()
				->table( 'cltes_expo' )
				->value( 'gcliente' )
				->label( 'cnombre' )
			)
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return '0';
				}
				return $val;
			}),
		Field::inst( 'accesos.id_cont as cliente_gab' )
			->options( Options::inst()
				->table( 'contagab.aacte' )
				->value( 'no_cte' )
				->label( 'nombre' )
			)
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return 0;
				}
				return $val;
			}),
		Field::inst( 'accesos.id_cont_sab as cliente_sab' )
			->options( Options::inst()
				->table( 'contasab.aacte' )
				->value( 'no_cte' )
				->label( 'nombre' )
			)
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return 0;
				}
				return $val;
			}),
		Field::inst( 'accesos.id_quickbooks as cliente_qb_delbravo' )
			->options( Options::inst()
				->table( 'qbdelbravo_sync.customer' )
				->value( 'ListID' )
				->label( 'Name' )
				->where( function ($q) {
					$q->where( 'IsActive', 'true', '=' );
				})
			)
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return NULL;
				}
				return $val;
			}),
		Field::inst( 'accesos.id_quickbooks_benavides as cliente_qb_benavides' )
			->options( Options::inst()
				->table( 'qbbenavides_sync.customer' )
				->value( 'ListID' )
				->label( 'Name' )
				->where( function ($q) {
					$q->where( 'IsActive', 'true', '=' );
				})
			)
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return NULL;
				}
				return $val;
			}),
		Field::inst( 'accesos.clasificaciones as clasifica_mod' ),
		Field::inst( 'accesos.clasifica_read as clasifica_read' ),
		Field::inst( 'p.ultima_visita as ultima_visita' )
			->getFormatter( Format::dateSqlToFormat( 'd/m/Y' ) ),
		Field::inst( 'cli_impo.nom as cli_impo_nom' ),
		Field::inst( 'cli_nb.nom as cli_nb_nom' ),
		Field::inst( 'cli_ped.nom_imp as cli_ped_nom' ),
		Field::inst( 'cli_expo.cnombre as cli_expo_nom' ),
		Field::inst( 'cli_gab.nombre as cli_gab_nom' ),
		Field::inst( 'cli_sab.nombre as cli_sab_nom' ),
		Field::inst( 'cli_qb.name as cli_qb_nom' ),
		Field::inst( 'cli_qb_bac.name as cli_qb_bac_nom' )
	)
	->leftJoin( 'piwik.ultima_visita_sii as p', 'accesos.usuario', '=', 'p.user_id' )
	->leftJoin( 'clientes as cli_impo', 'accesos.cliente_id', '=', 'cli_impo.cliente_id' )
	->leftJoin( 'clientes_nb as cli_nb', 'accesos.cliente_id', '=', 'cli_nb.cliente_id' )
	->leftJoin( 'casa.ctrac_client as cli_ped', 'accesos.id_ped', '=', 'cli_ped.cve_imp' )
	->leftJoin( 'cltes_expo as cli_expo', 'accesos.id_expo', '=', 'cli_expo.gcliente' )
	->leftJoin( 'contagab.aacte as cli_gab', 'accesos.id_cont', '=', 'cli_gab.no_cte' )
	->leftJoin( 'contasab.aacte as cli_sab', 'accesos.id_cont_sab', '=', 'cli_sab.no_cte' )
	->leftJoin( 'qbdelbravo_sync.customer as cli_qb', 'accesos.id_quickbooks', '=', 'cli_qb.ListID' )
	->leftJoin( 'qbbenavides_sync.customer as cli_qb_bac', 'accesos.id_quickbooks_benavides', '=', 'cli_qb_bac.ListID' )
	->pkey('accesos.id_acceso')
	->debug(true)
	->process( $_POST )
	->json();
