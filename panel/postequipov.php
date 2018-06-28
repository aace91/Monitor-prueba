<?php
include('../connect_dbsql.php');
/*
 * Example PHP implementation used for the index.html example
 */

// DataTables PHP library
include( "../editor/php/DataTables.php" );

// Alias Editor classes so they are easy to use
use
	DataTables\Editor,
	DataTables\Editor\Field,
	DataTables\Editor\Format,
	DataTables\Editor\Join,
	DataTables\Editor\MJoin,
	DataTables\Editor\Upload,
	DataTables\Editor\Validate;

// Build our Editor instance and process the data coming from _POST
Editor::inst( $db, 'equipoentrada' )
	->fields(
		Field::inst( 'equipoentrada.id' ),
		Field::inst( 'equipoentrada.hora' ),
		Field::inst( 'equipoentrada.fecha' )
			->getFormatter(function ( $val, $data, $opts ) {
				return date( 'Y-m-d', strtotime( $val ) );
			}),
		Field::inst( 'equipoentrada.nombre_cliente' ),
		Field::inst( 'equipoentrada.no_cliente' ),
		Field::inst( 'equipoentrada.tipo_equipo' ),
		Field::inst( 'equipoentrada.linea' ),
		Field::inst( 'equipoentrada.no_equipo' ),
		Field::inst( 'equipoentrada.id_foto_no_equipo' ),
		Field::inst( 'equipoentrada.id_foto_placas' ),
		Field::inst( 'equipoentrada.id_foto_marca' ),
		Field::inst( 'equipoentrada.id_foto_modelo' ),
		Field::inst( 'equipoentrada.observaciones' ),
		Field::inst( 'equipoentrada.usuario' )
	)
	->pkey('equipoentrada.id')
	->where( function ( $q) {
		if($_POST['id_cliente']!=0)
			$q->where('equipoentrada.no_cliente',$_POST['id_cliente'],'=');
		if($_POST['linea']!='0')
			$q->where('equipoentrada.linea',$_POST['linea'],'=');
		if($_POST['fechaini']!=''){
			$fec=date_create_from_format('m/d/Y', $_POST['fechaini']);
			$q->where('equipoentrada.fecha',$fec->format("Y-m-d"),'>=');
		}
		if($_POST['fechafin']!=''){
			$fec=date_create_from_format('m/d/Y', $_POST['fechafin']);
			$q->where('equipoentrada.fecha',$fec->format("Y-m-d"),'<=');
		}
	} )
	->leftJoin( 'clientes', 'clientes.cliente_id', '=', 'equipoentrada.no_cliente' )
	->leftJoin( 'tipoequipo', 'tipoequipo.tipo_equipo', '=', 'equipoentrada.tipo_equipo' )
	->leftJoin( 'consolidadoras_salidas', 'equipoentrada.linea','=','consolidadoras_salidas.Nombre' )
	->process( $_POST )
	->json();
