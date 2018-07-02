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

Editor::inst( $db, 'entradas_expo' )
	->fields(
		Field::inst( 'entradas_expo.referencia_original AS referencia_original' ),
		Field::inst( 'entradas_expo.referencia as referencia' ),
		Field::inst( 'entradas_expo.fecha as fecha_alta' ),
		//Field::inst( 'entradas_expo.hora as hora' ),
		Field::inst( 'entradas_expo.numcliente as id_cliente' )
			->options( Options::inst()
				->table( 'cltes_expo' )
				->value( 'gcliente' )
				->label( 'cnombre' )
			)
			->validator( 'Validate::dbValues' ),
		Field::inst( 'entradas_expo.nombrecliente as cliente' ),
		Field::inst( 'entradas_expo.usuario as usuario' )
	)
	->pkey('entradas_expo.referencia')
	//->leftJoin( 'cltes_expo as cli', 'cli.gcliente', '=', 'ent.numcliente' )
	->where( function ( $q) {
		
	} )
	->on( 'preCreate', function ( $editor, $values) {
		global $id;
        $fecha = date_create();
		$cliente = $editor->db()
			->select( 'cltes_expo', 'cnombre', array('gcliente' => $values['id_cliente'])  )
			->fetch();
		$nom_cliente=$cliente['cnombre'];
		$editor
            ->field( 'cliente' )
            ->setValue($nom_cliente);
		$editor
            ->field( 'fecha_alta' )
            ->setValue($fecha->format("Y-m-d G:i:s"));
		$editor
            ->field( 'usuario' )
            ->setValue($id);
		/*$editor
            ->field( 'hora' )
            ->setValue($fecha->format("g:i:s A"));*/
		
		//Caso de rectificacion
		if ($values['referencia_original'] != '') {
			//global $odbccasa;
			include('../../connect_casa.php');
			
			$host = '192.168.1.107:E:\CASAWIN\CSAAIWIN\Datos\CASA.GDB'; 
			$username='SYSDBA'; 
			$password='masterkey';		
			$dbh = ibase_connect($host, $username, $password);
			
			$ref_original =  strtoupper($values['referencia_original']);
			$ref_rectificacion = substr($ref_original, 2, strlen($ref_original) - 1);
			$consulta = "SELECT (SELECT COUNT(b.NUM_REFE) + 1 AS RECTIFICACION
								 FROM SAAIO_PEDIME b
								 WHERE b.NUM_REFE LIKE '".$ref_rectificacion."-%' AND 
									   b.TIP_PEDI='R1' AND b.IMP_EXPO = 2)
						 FROM SAAIO_PEDIME a
						 WHERE a.NUM_REFE = '".$ref_original."'";
						 
			$query = odbc_exec($odbccasa, $consulta);
			if ($query==false){ 
				$error_msg="No se pudo consultar la referencia ".$ref_original;
				exit(json_encode(array("error" => $error_msg)));
			} else {
				if(odbc_num_rows($query)<=0){ 
					$error_msg="La referencia ".$ref_original." no existe en CASA.";
					exit(json_encode(array("error" => $error_msg)));
				} else {
					while(odbc_fetch_row($query)){ 
						$ref_rectificacion = $ref_rectificacion.'-R'.odbc_result($query,"RECTIFICACION");
					
						$editor
							->field( 'referencia_original' )
							->setValue($ref_original);						
						$editor
							->field( 'referencia' )
							->setValue($ref_rectificacion);
						break;
					}
				}
			}
		} else {
			//Caso normal
			$consecutivo = $editor->db()
				->select( 'consecutivos_expo', 'consecutivo' )
				->fetch();
			$editor->db()
				->query( 'update', 'consecutivos_expo' )
				->set( 'consecutivo', $consecutivo['consecutivo']+1 )
				->exec();
			
			$editor
				->field( 'referencia' )
				->setValue('GA18'.str_pad($consecutivo['consecutivo']+1,5,0,STR_PAD_LEFT));
		}
    } )
	->on( 'preEdit', function ( $editor, $id, $values) {
		
		include('../../connect_casa.php');
		
		$referencia =  strtoupper($values['referencia']);
		$consulta = "SELECT * FROM SAAIO_PEDIME WHERE NUM_REFE = '".$referencia."'";
		$query = odbc_exec($odbccasa, $consulta);
		if ($query==false){ 
			$error_msg="No se pudo consultar la referencia ".$referencia;
			exit(json_encode(array("error" => $error_msg)));
		} else {
			if(odbc_num_rows($query) > 0){
				$error_msg="No se puede modificar la informacion de la referencia porque existe en Sistemas CASA.";
				exit(json_encode(array("error" => $error_msg)));
			}
		}
		
		$cliente = $editor->db()
			->select( 'cltes_expo', 'cnombre', array('gcliente' => $values['id_cliente'])  )
			->fetch();
		$nom_cliente=$cliente['cnombre'];
		if($nom_cliente==''){
			$error_msg='Error al obtener el nombre del cliente';
			exit(json_encode(array("error" => $error_msg)));
		}
		$editor
            ->field( 'cliente' )
            ->setValue($nom_cliente);
    } )
	->on( 'preRemove', function ( $editor,$id_row, $values) {
        include('../../db.php');
		$conn_casa = new PDO($pdo_casa_cnn, $pdo_casa_usu, $pdo_casa_psw);
		$conn_casa->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$consulta = "
			SELECT 
				NUM_REFE
			FROM
				SAAIO_PEDIME
			WHERE 
				NUM_REFE='$id_row'
		";
		$resp = $conn_casa->query($consulta)->fetchAll();
		$conn_casa=NULL;
		if(count($resp) > 0){
			$error_msg="No se puede eliminar esta entrada por que contiene datos en el sistema de pedimentos";
			exit(json_encode(array("error" => $error_msg)));
		}
    } )
	->process( $_POST )
	->json();
