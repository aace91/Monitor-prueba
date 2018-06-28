<?php
include_once("../checklogin.php");
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
	DataTables\Editor\Upload,
	DataTables\Editor\Validate;

// Build our Editor instance and process the data coming from _POST

Editor::inst( $db, 'precaptura_detalle' )
	->fields(
		Field::inst( 'precaptura_detalle.id_precaptura as id_precaptura' ),
		Field::inst( 'precaptura_detalle.id_proveedor as id_proveedor' ),
		Field::inst( 'precaptura_detalle.id_detalle as id_detalle' )
			->set( false ),
		Field::inst( 'precaptura_detalle.no_factura as no_factura' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),
		Field::inst( 'precaptura_detalle.fecha_factura as fecha_factura' )
			->getFormatter(function ( $val, $data, $opts ) {
				if($val==NULL){
					return '';
				}else{
					return date( 'd/m/Y', strtotime( $val ) );
				}
			})
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return null;
				}else{
					$val = DateTime::createFromFormat('d/m/Y', $val);
					return $val->format('Y-m-d');
				}
			}),
		Field::inst( 'precaptura_detalle.monto_factura as monto_factura' )
			->validator( 'Validate::numeric' ),
		Field::inst( 'precaptura_detalle.moneda as moneda' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.incoterm as incoterm' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),
		Field::inst( 'precaptura_detalle.subdivision as subdivision' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),
		Field::inst( 'precaptura_detalle.certificado as certificado' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.no_parte as no_parte' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.origen as origen' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.vendedor as vendedor' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.fraccion as fraccion' ),	
		Field::inst( 'precaptura_detalle.descripcion as descripcion' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.precio_partida as precio_partida' ),	
		Field::inst( 'precaptura_detalle.umc as umc' ),	
		Field::inst( 'precaptura_detalle.cantidad_umc as cantidad_umc' ),	
		Field::inst( 'precaptura_detalle.cantidad_umt as cantidad_umt' ),	
		Field::inst( 'precaptura_detalle.preferencia as preferencia' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.marca as marca' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.modelo as modelo' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.submodelo as submodelo' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.serie as serie' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),	
		Field::inst( 'precaptura_detalle.descripcion_cove as descripcion_cove' )
			->setFormatter(function ( $val, $data, $opts ) {
				return strtoupper($val);
			}),
		Field::inst( 'fra_restric.fraccion as fra_restric' )
			->set( false )
	)
	//->leftJoin( 'casa.ctrac_proved as prov', 'pd.id_proveedor', '=', 'casa.ctrac_proved.cve_pro' )
	->leftJoin( 'fracciones_restric as fra_restric', 'precaptura_detalle.fraccion', '=', 'fra_restric.fraccion' )
	->pkey('id_detalle')
	->on( 'preCreate', function ( $editor, $values ) {
		global $id;
		/*$fecha = new DateTime();
		$hora = $fecha->format("g:i:s A");*/
		include ('../db.php');
		$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
		if ($cmysqli->connect_error) {
			$error_msg= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
			exit(json_encode(array("error" => $error_msg)));
		}
		$consulta="
			SELECT 
				id_proveedor
			FROM 
				precaptura_detalle
			WHERE
				id_precaptura=".$_POST['id_precaptura']."
			LIMIT 1
		";
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$error_msg= 'Error en la consulta: ' .$consulta.' , error:'.$error ;
			mysqli_close($cmysqli);
			exit(json_encode(array("error" => $error_msg)));
		}
		while($row = $query->fetch_object()){
			$id_proveedor=$row->id_proveedor; 
		}
		mysqli_close($cmysqli);
		$editor
            ->field( 'id_precaptura' )
            ->setValue($_POST['id_precaptura']);
		$editor
            ->field( 'id_proveedor' )
            ->setValue($id_proveedor);
		/*$editor
            ->field( 'equipoentrada.hora' )
            ->setValue($hora);*/
	})
	->on( 'postEdit', function ( $editor, $id_detalle, $values, $row ) {
		global $id;
		$fecha = new DateTime();
		$fecha_mod= $fecha->format("Y-m-d G:i:s");
		include ('../db.php');
		$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
		if ($cmysqli->connect_error) {
			$error_msg= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
			exit(json_encode(array("error" => $error_msg)));
		}
		$consulta="
			UPDATE precaptura_gral
			SET fecha_mod='$fecha_mod',id_usuario_mod=$id
			WHERE
				id_precaptura=".$_POST['id_precaptura']."
		";
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$error_msg= 'Error en la consulta: ' .$consulta.' , error:'.$error ;
			mysqli_close($cmysqli);
			exit(json_encode(array("error" => $error_msg)));
		}
		mysqli_close($cmysqli);
	})
	->on( 'postRemove', function ( $editor, $id_detalle, $values) {
		global $id;
		$fecha = new DateTime();
		$fecha_mod= $fecha->format("Y-m-d G:i:s");
		include ('../db.php');
		$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
		if ($cmysqli->connect_error) {
			$error_msg= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
			exit(json_encode(array("error" => $error_msg)));
		}
		$consulta="
			UPDATE precaptura_gral
			SET fecha_mod='$fecha_mod',id_usuario_mod=$id
			WHERE
				id_precaptura=".$_POST['id_precaptura']."
		";
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$error_msg= 'Error en la consulta: ' .$consulta.' , error:'.$error ;
			mysqli_close($cmysqli);
			exit(json_encode(array("error" => $error_msg)));
		}
		mysqli_close($cmysqli);
	})
	->where( function ( $q) {
		$q->where('precaptura_detalle.id_precaptura',$_POST['id_precaptura'],'=');
	} )
	->process( $_POST )
	->json();
