<?php
include_once('./../checklogin.php');
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
Editor::inst( $db, 'clasificaciones' )
	->fields(
		Field::inst( 'clasificaciones.id' ),
		Field::inst( 'clasificaciones.noparte' )
			->setFormatter(function ( $val, $data, $opts ) {
				if ($val==''){
					return null;
				}
				return strtoupper($val);
			})
			->validator( 'Validate::required' ),
		Field::inst( 'clasificaciones.origen' )
			->options( function () use ( $db ) {
                // Use `selectDistinct` to get the full list of names from the
                // database and then concatenate the first and last names
                $userList = $db->select( 'casa.ctarc_paises', 'cve_pai',null, null );
                $out = array();
				$out[] = array(
                        "value" => '',
                        "label" => ''
                    );
                while ( $row = $userList->fetch() ) {
                    $out[] = array(
                        "value" => $row['cve_pai'],
                        "label" => $row['cve_pai']
                    );
                }
 
                return $out;
            } )
			->setFormatter(function ( $val, $data, $opts ) {
				return (($val=='')? null : $val);
			}),
		Field::inst( 'clasificaciones.fraccion' )
			->setFormatter(function ( $val, $data, $opts ) {
				return (($val=='')? null : strtoupper($val));
			})
			->validator( 'Validate::required' ),
		Field::inst( 'clasificaciones.fraccion2' )
			->setFormatter(function ( $val, $data, $opts ) {
				return (($val=='')? null : strtoupper($val));
			}),
		Field::inst( 'clasificaciones.descripcion' )
			->setFormatter(function ( $val, $data, $opts ) {
				return (($val=='')? null : strtoupper($val));
			})
			->validator( 'Validate::required' ),
		Field::inst( 'clasificaciones.descripcion_ing' )
			->setFormatter(function ( $val, $data, $opts ) {
				return (($val=='')? null : strtoupper($val));
			})
			->validator( 'Validate::required' ),
		Field::inst( 'clasificaciones.medida' )
            ->options( 'unidadesmedida', 'um', 'um' )
			->validator( 'Validate::dbValues' , array('required'=>true)),
		Field::inst( 'clasificaciones.proveedor_id' )
            ->options( 'procli', 'proveedor_id', 'proNom' )
			->validator( 'Validate::dbValues' , array('required'=>true)),
		Field::inst( 'clasificaciones.cliente_id' )
            ->options( 'clientes', 'cliente_id', 'Nom' )
			->validator( 'Validate::dbValues' , array('required'=>true)),
		Field::inst( 'clasificaciones.usuario' ),
		Field::inst( 'clasificaciones.hora' ),
		Field::inst( 'clasificaciones.fecha' )
			->getFormatter(function ( $val, $data, $opts ) {
				return date( 'd/m/Y', strtotime( $val ) );
			}),
		Field::inst( 'clasificaciones.clasificado' ),
		Field::inst( 'clasificaciones.material' )
			->setFormatter(function ( $val, $data, $opts ) {
				return (($val=='')? null : strtoupper($val));
			}),
		Field::inst( 'clasificaciones.fundamento_legal' )
			->setFormatter(function ( $val, $data, $opts ) {
				return (($val=='')? null : strtoupper($val));
			}),
		Field::inst( 'clasificaciones.fraccionR8va' )
			->setFormatter(function ( $val, $data, $opts ) {
				return (($val=='')? null : strtoupper($val));
			}),
		Field::inst( 'clientes.Nom' ),
		Field::inst( 'procli.proNom' ),
		Field::inst( 'refxclasifica.referencias' )
			->set( false )
	)
	->pkey('clasificaciones.id')
	->on( 'preCreate', function ( $editor, $values ) {
		global $username,$ip_out2,$port_out2;

		include("../connect_dbsql.php");
		include("../bower_components/nusoap/src/nusoap.php");
		
		$clasificaciones=$values['clasificaciones'];

		$consultaa = "SELECT max(id) as id_clasificacion FROM clasificaciones";
		$client = new nusoap_client("http://$ip_out2:$port_out2/webtools/ws_mdb/ws_mdb.php?wsdl","wsdl");
		$err = $client->getError();
		if ($err) {
			$error_msg="Constructor error:". $err ;
			exit(json_encode(array("error" => $error_msg)));
		}
		//$client->debug();
		$param = array('usuario' => 'admin',
		'password' => 'r0117c',
		'consulta' => $consultaa,
		'tipo' => 'SELECT',
		'bd' => 'revisiones');
		$result = $client->call('ws_mdb', $param);
		$err = $client->getError();
		if ($err) {
			$error_msg="Constructor error:". $err ;
			exit(json_encode(array("error" => $error_msg)));
		}
		if($result['Codigo']!=1){
			$error_msg="Error del WS: ".$result['Mensaje'];
			exit(json_encode(array("error" => $error_msg)));
		}
		$filas=json_decode($result['Adicional1'], true);
		$fila=$filas[0];
		$id_new=$fila['id_clasificacion']+1;
		
		$editor
            ->field( 'clasificaciones.id' )
            ->setValue($id_new);
		$fechan = new DateTime();
		$editor
            ->field( 'clasificaciones.fecha' )
            ->setValue($fechan->format("Y-m-d"));
		$editor
            ->field( 'clasificaciones.hora' )
            ->setValue($fechan->format("g:i:s A"));
		$editor
            ->field( 'clasificaciones.usuario' )
            ->setValue($username);
		$editor
            ->field( 'clasificaciones.clasificado' )
			->setValue('X');
			
		$id_clasificacion=$id_new;
		$noparte=strtoupper($clasificaciones['noparte']); 
		$origen=$clasificaciones['origen'];
		$fraccion=strtoupper($clasificaciones['fraccion']);
		$fraccion2=strtoupper($clasificaciones['fraccion2']);
		$descripcion=strtoupper($clasificaciones['descripcion']);
		$descripcion_ing=strtoupper($clasificaciones['descripcion_ing']);
		$medida=$clasificaciones['medida'];
		$proveedor_id=$clasificaciones['proveedor_id'];
		$cliente_id=$clasificaciones['cliente_id'];
		$usuario=$username;
		$fecha='#'.$fechan->format("Y-m-d").'#';
		$fecham=$fechan->format("Ymd");
		$hora=$fechan->format("g:i:s A");
		$clasificado='X';
		$fraccionR8va=($clasificaciones['fraccionR8va']=='' ? "'".strtoupper($clasificaciones['fraccionR8va'])."'" : 'NULL');
		$material=strtoupper($clasificaciones['material']);
		$fundamento_legal=strtoupper($clasificaciones['fundamento_legal']);

		if ($id_clasificacion==''){
			$error_msg="El ID no se genero de manera correcta";
			exit(json_encode(array("error" => $error_msg)));
		}
		$errores=array();
		if ($noparte==''){
			array_push($errores,array("name"=>"clasificaciones.noparte","status"=>"Este campo es requerido"));
		}
		if ($fraccion==''){
			array_push($errores,array("name"=>"clasificaciones.fraccion","status"=>"Este campo es requerido"));
		}
		if ($descripcion==''){
			array_push($errores,array("name"=>"clasificaciones.descripcion","status"=>"Este campo es requerido"));
		}
		if ($medida==''){
			array_push($errores,array("name"=>"clasificaciones.medida","status"=>"Este campo es requerido"));
		}
		if ($proveedor_id==''){
			array_push($errores,array("name"=>"clasificaciones.proveedor_id","status"=>"Este campo es requerido"));
		}
		if ($cliente_id==''){
			array_push($errores,array("name"=>"clasificaciones.cliente_id","status"=>"Este campo es requerido"));
		}
		if(count($errores)>0){
			$respuesta=array("fieldErrors"=>$errores,"data"=>array());
			exit(json_encode($respuesta));
		}
		
		/*****************************************************/

		$consultaa="
			INSERT INTO clasificaciones
			(
				id,
				noparte,
				origen,
				fraccion,
				fraccion2,
				descripcion,
				descripcion_ing,
				proveedor_id,
				cliente_id,
				medida,
				usuario,
				fecha,
				hora,
				clasificado,
				fraccionR8va,
				material,
				fundamento_legal
			)
			
			VALUES 
			(
				$id_clasificacion,
				'$noparte',
				'$origen',
				'$fraccion',
				'$fraccion2',
				'$descripcion',
				'$descripcion_ing',
				$proveedor_id,
				$cliente_id,
				'$medida',
				'$usuario',
				$fecha,
				'$hora',
				'$clasificado',
				$fraccionR8va,
				'$material',
				'". utf8_decode($fundamento_legal) ."'
			)
		";
		$consultam="
			INSERT INTO bodegareplica.clasificaciones
			(
				id,
				noparte,
				origen,
				fraccion,
				fraccion2,
				descripcion,
				descripcion_ing,
				proveedor_id,
				cliente_id,
				medida,
				usuario,
				fecha,
				hora,
				clasificado,
				fraccionR8va,
				material,
				fundamento_legal
			)
			
			VALUES 
			(
				$id_clasificacion,
				'$noparte',
				'$origen',
				'$fraccion',
				'$fraccion2',
				'$descripcion',
				'$descripcion_ing',
				$proveedor_id,
				$cliente_id,
				'$medida',
				'$usuario',
				'$fecham',
				'$hora',
				'$clasificado',
				$fraccionR8va,
				'$material',
				'$fundamento_legal'
			)
		";

		/*****************************************************/

		$client = new nusoap_client("http://$ip_out2:$port_out2/webtools/ws_mdb/ws_mdb.php?wsdl","wsdl");
		$err = $client->getError();
		if ($err) {
			$error_msg="Constructor error:". $err ;
			exit(json_encode(array("error" => $error_msg)));
		}
		//$client->debug();
		$param = array('usuario' => 'admin',
		'password' => 'r0117c',
		'consulta' => $consultaa,
		'tipo' => 'INSERT',
		'bd' => 'revisiones');
		$result = $client->call('ws_mdb', $param);
		$err = $client->getError();
		if ($err) {
			$error_msg="Constructor error:". $err ;
			exit(json_encode(array("error" => $error_msg)));
		}
		if($result['Codigo']!=1){
			$error_msg="Error del WS: ".$result['Mensaje'].". Consulta: ".$consultaa;
			exit(json_encode(array("error" => $error_msg)));
		}

		/*****************************************************/

		$query = mysqli_query($cmysqli,$consultam);
		if (!$query) {
			$error_msg='Error en la consulta: '.mysqli_error($cmysqli);
			exit(json_encode(array("error" => $error_msg,"consulta" =>$consultam )));
		}
	})
	->on( 'preEdit', function ( $editor, $id, $values ) {
		global $username,$ip_out2,$port_out2;
		
		include("../bower_components/nusoap/src/nusoap.php");
		include("../connect_dbsql.php");
		
		$clasificaciones=$values['clasificaciones'];
		$fechan = new DateTime();

		$editor
			->field( 'clasificaciones.fecha' )
			->setValue($fechan->format("Y-m-d"));
		$editor
			->field( 'clasificaciones.hora' )
			->setValue($fechan->format("g:i:s A"));
		$editor
			->field( 'clasificaciones.usuario' )
			->setValue($username);
		$editor
			->field( 'clasificaciones.clasificado' )
			->setValue('X');

		$id_clasificacion=$clasificaciones['id'];
		$origen=$clasificaciones['origen'];
		$fraccion=strtoupper($clasificaciones['fraccion']);
		$fraccion2=strtoupper($clasificaciones['fraccion2']);
		$descripcion=strtoupper($clasificaciones['descripcion']);
		$descripcion_ing=strtoupper($clasificaciones['descripcion_ing']);
		$medida=$clasificaciones['medida'];
		$usuario=$username;
		$fecha='#'.$fechan->format("Y-m-d").'#';
		$fecham=$fechan->format("Ymd");
		$hora=$fechan->format("g:i:s A");
		$clasificado='X';
		$fraccionR8va=($clasificaciones['fraccionR8va']=='' ? "'".strtoupper($clasificaciones['fraccionR8va'])."'" : 'NULL');
		$material=strtoupper($clasificaciones['material']);
		$fundamento_legal=strtoupper($clasificaciones['fundamento_legal']);
		$errores=array();

		if ($fraccion==''){
			array_push($errores,array("name"=>"clasificaciones.fraccion","status"=>"Este campo es requerido"));
		}
		if ($descripcion==''){
			array_push($errores,array("name"=>"clasificaciones.descripcion","status"=>"Este campo es requerido"));
		}
		if ($medida==''){
			array_push($errores,array("name"=>"clasificaciones.medida","status"=>"Este campo es requerido"));
		}
		if(count($errores)>0){
			$respuesta=array("fieldErrors"=>$errores,"data"=>array());
			exit(json_encode($respuesta));
		}

		/*****************************************************/

		$consultaa = "UPDATE clasificaciones
			          SET fecha= $fecha,
				          hora= '$hora',
				          usuario= '$usuario',
				          origen= '$origen',
				          fraccion= '$fraccion',
						  fraccion2= '$fraccion2',
				          descripcion= '$descripcion',
						  descripcion_ing= '$descripcion_ing',
				          medida= '$medida',
				          clasificado='$clasificado',
				          fraccionR8va=$fraccionR8va,
						  material= '$material',
						  fundamento_legal='". utf8_decode($fundamento_legal) ."'
					  WHERE id=".$id_clasificacion;
					  
		$consultam = "UPDATE bodegareplica.clasificaciones
			          SET fecha= '$fecham',
				          hora= '$hora',
				          usuario= '$usuario',
				          origen= '$origen',
				          fraccion= '$fraccion',
				          fraccion2= '$fraccion2',
				          descripcion= '$descripcion',
				          descripcion_ing= '$descripcion_ing',
				          medida= '$medida',
				          clasificado='$clasificado',
				          fraccionR8va=$fraccionR8va,
				          material= '$material',
				          fundamento_legal= '$fundamento_legal'
					 WHERE id=".$id_clasificacion;
		
		/*****************************************************/

		$client = new nusoap_client("http://$ip_out2:$port_out2/webtools/ws_mdb/ws_mdb.php?wsdl","wsdl");
		$err = $client->getError();
		if ($err) {
			$error_msg="Constructor error:". $err ;
			exit(json_encode(array("error" => $error_msg)));
		}
		
		$param = array('usuario' => 'admin',
		'password' => 'r0117c',
		'consulta' => $consultaa,
		'tipo' => 'UPDATE',
		'bd' => 'revisiones');
		$result = $client->call('ws_mdb', $param);
		$err = $client->getError();
		if ($err) {
			$error_msg="Constructor error:". $err ;
			exit(json_encode(array("error" => $error_msg)));
		}
		if($result['Codigo']!=1){
			$error_msg="Error del WS: ".$result['Mensaje'].". Consulta: ".$consultaa;
			exit(json_encode(array("error" => $error_msg)));
		}

		/*****************************************************/

		$query = mysqli_query($cmysqli,$consultam);
		if (!$query) {
			$error_msg='Error en la consulta: '.mysqli_error($cmysqli);
			exit(json_encode(array("error" => $error_msg,"consulta" =>$consultam )));
		}
	})
	->where( function ( $q) {
		if($_POST['id_cliente']!=0)
			$q->where('clasificaciones.cliente_id',$_POST['id_cliente'],'=');
		if($_POST['id_proveedor']!='0')
			$q->where('clasificaciones.proveedor_id',$_POST['id_proveedor'],'=');
	} )
	->leftJoin( 'clientes', 'clientes.cliente_id', '=', 'clasificaciones.cliente_id' )
	->leftJoin( 'procli', 'procli.proveedor_id', '=', 'clasificaciones.proveedor_id' )
	->leftJoin( 'refxclasifica', 'refxclasifica.idclasif', '=', 'clasificaciones.id' )
	->join(
		Mjoin::inst( 'clasificaciones_catalogo' )
			->link( 'clasificaciones.id', 'clasificaciones_restricciones.id_clasificacion' )
			->link( 'clasificaciones_catalogo.id', 'clasificaciones_restricciones.id_restriccion' )
			->order( 'descripcion asc' )
			->fields(
				Field::inst( 'id' )
					->options( 'clasificaciones_catalogo', 'id', 'descripcion' ),
				Field::inst( 'descripcion' )
			)
	)
	->process( $_POST )
	->json();
