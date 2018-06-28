<?php
	require_once("../bower_components/nusoap/src/nusoap.php");
    //crear server
    $server = new soap_server;
    // initialize WSDL con nombre de la funcion
    $server->configureWSDL( 'ws_casa' , 'urn:ws_casa' );
   
    $server->wsdl->schemaTargetNamespace = 'urn:ws_casa';
       
    $server->wsdl->addComplexType(
        'RespuestaObject',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'Codigo' => array('name'=>'Codigo','type'=>'xsd:int'),
			'Mensaje' => array('name'=>'Mensaje','type'=>'xsd:string'),
			'Adicional1' => array('name'=>'Adicional1','type'=>'xsd:string'),
			'Adicional2' => array('name'=>'Adicional2','type'=>'xsd:string')
        )
    );
   
    $server->wsdl->addComplexType(
        'Objectconsulta',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'usuario' => array('name'=>'usuario','type'=>'xsd:string'),
            'password' => array('name'=>'password','type'=>'xsd:string'),
			'consulta' => array('name'=>'consulta','type'=>'xsd:string'),
			'tipo' => array('name'=>'consulta','type'=>'xsd:string')
            )
       
    );
   
   
    $server->register(
        'ws_casa',
        array(
            'usuario' => 'xsd:string',
            'password' => 'xsd:string',
			'consulta' => 'xsd:string',
			'tipo' => 'xsd:string'
            ),
        array('return'=>'tns:RespuestaObject'),
        'ws_casawsdl',
        'urn:ws_casawsdl',
        'rpc',
        'encoded',
        'ConsultaRemotaAccess');
       
    //Metodo de consulta
    function ws_casa($usuario,$password,$consulta,$tipo)
    {
		include("../db.php");
		if ($usuario !='admin' or $password!='r0117c'){
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error de autentificación de WS al ejecutar la consulta en access';
			$respuesta['Adicional1']="";
			$respuesta['Adicional2']="";
			return $respuesta;
		}
		$dsn = "cnxpedimentos";
		//debe ser de sistema no de usuario
		$usuarioc = "";
		$clavec="";
		//realizamos la conexion mediante odbc
		$odbccasa=odbc_connect($dsn, $usuarioc, $clavec);
		if (!$odbccasa){
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']="Error al conectarse a la base de datos de pedimentos";
			$respuesta['Adicional1']="";
			$respuesta['Adicional2']="";
			return $respuesta;
		}
		switch ($tipo) {
		    case "UPDATE":
				$result = odbc_exec ($odbccasa, $consulta);
				if ($result==false){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error en consulta, error:".odbc_errormsg ($odbccasa);
					$respuesta['Adicional1']="";
					$respuesta['Adicional2']="";
					odbc_close($odbccasa);
					return $respuesta;
				}else{
					if (odbc_num_rows($result)==-1){
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']="Error en consulta, error:".odbc_errormsg ($odbccasa);
						$respuesta['Adicional1']="";
						$respuesta['Adicional2']="";
						odbc_close($odbccasa);
						return $respuesta;
					}else{
						odbc_close($odbccasa);
						$respuesta['Codigo']=1;
						$respuesta['Mensaje']='Consulta realizada con exito';
						$respuesta['Adicional1']="";
						$respuesta['Adicional2']="";	
						return $respuesta;
					}
				}
				break;
			case "SELECT":
				$result = odbc_exec ($odbccasa, $consulta);
				if ($result==false){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error en consulta, error:".odbc_errormsg ($odbccasa);
					$respuesta['Adicional1']="";
					$respuesta['Adicional2']="";
					odbc_close($odbccasa);
					return $respuesta;
				}else{
					$filas=array();
					while ($fila = odbc_fetch_array($result)){
						array_push($filas,$fila);
					}
					odbc_close($odbccasa);
					$respuesta['Codigo']=1;
					$respuesta['Mensaje']='Consulta realizada con exito';
					$respuesta['Adicional1']=json_encode($filas);
					$respuesta['Adicional2']="";	
					return $respuesta;
				}
				break;
			case "INSERT":
				$result = odbc_exec ($odbccasa, $consulta);
				if ($result==false){
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']="Error en consulta, error:".odbc_errormsg ($odbccasa);
					$respuesta['Adicional1']="";
					$respuesta['Adicional2']="";
					odbc_close($odbccasa);
					return $respuesta;
				}else{
					odbc_close($odbccasa);
					$respuesta['Codigo']=1;
					$respuesta['Mensaje']='Consulta realizada con exito';
					$respuesta['Adicional1']="";
					$respuesta['Adicional2']="";	
					return $respuesta;
				}
				break;
			default:
				$respuesta['Codigo']=-1;
				$respuesta['Mensaje']="No se recibio el tipo de consulta";
				$respuesta['Adicional1']="";
				$respuesta['Adicional2']="";
				return $respuesta;
		}
		
	}
	
	// create HTTP listener
    if ( !isset( $HTTP_RAW_POST_DATA ) ) $HTTP_RAW_POST_DATA =file_get_contents( 'php://input' );
	$server->service($HTTP_RAW_POST_DATA);
    exit();