<?php
include_once('./../checklogin.php');
if($loggedIn == false){
	header("Location: ./../login.php"); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Monitor - Salida exportacion</title>

	<!-- Bootstrap core CSS -->
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	
	<!-- DataTables CSS -->
    <link href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet">
	<link href="../editor/css/editor.bootstrap.min.css" rel="stylesheet">
	
	<!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	
	<!-- FileInput -->
	<link href="../bower_components/bootstrap-fileinput-4.2.3/css/fileinput.min.css" rel="stylesheet"/>
	
	<!-- Select2 CSS -->
	<link href="../bower_components/select2/dist/css/select2.min.css" rel="stylesheet" />
	<link href="../bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css" rel="stylesheet" />
	
	<!-- Bootstrap Datepicker -->
	<link href="../bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
	
    <!-- Custom styles for this template -->
    <link href="../bootstrap/css/navbar.css" rel="stylesheet">
	
	<style>
		a:hover { cursor:pointer; }
	</style>

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <!--script src="./bootstrap/js/ie-emulation-modes-warning.js"></script-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
</head>
<body>
	<div class="container">
		<?php include('nav.php');?>
		
		<input type="hidden" id="itxt_data" data-app_data="<?php 
			include('./../connect_dbsql.php');

			$respuesta['Codigo']=1;
			
			$sTask = 'insertar';
			$aSalidaData = array();
			$nSalidaNumero = 0;
			$bCruce = '-1';
			$nUniqueId = uniqid($id.'_', true); /*'151_5a677aefb5f25'*/
				
			if ((isset($_GET['id']) && !empty($_GET['id']))) {
				$sTask = 'editar';
				$nSalidaNumero = $_GET['id'];
				
				$consulta = "SELECT salidanumero, fecha, lineatransp, caja, aduana,
									notransfer, nombretransfer, noentrega, Nombreentrega, direntrega,
									tiposalida, cruces, usuario, urgente, horaentrega, recibio, indicaciones, observaciones,
									ferrocarril, viaje, leyenda, nolineatransp, relacion_docs_name,
									notificacion_arribo_name, solicitud_retiro_name
							 FROM bodega.salidas_expo
							 WHERE salidanumero=".$nSalidaNumero;
							 
				$query = mysqli_query($cmysqli, $consulta);							
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al consultar informacion de la salida.'; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					while($row = mysqli_fetch_object($query)){
						$aFacturas = array();
						$consulta = "SELECT a.FACTURA_NUMERO, a.TIPOSALIDA, a.CAJA, a.NUMCLIENTE, b.cnombre, a.VALOR_FACTURA, a.REFERENCIA,
											a.PEDIMENTO, a.SALIDA_NUMERO, a.CONS_FACT_PED, NUM_REM_PED, a.UUID, a.PATENTE, a.NOAAA, 
											a.PACKING_LIST_ID,
										    d.nombre_archivo AS PACKING_LIST_NAME,
											a.CERTIFICADO_ORIGEN_ID,
										    e.nombre_archivo AS CERTIFICADO_ORIGEN_NAME,
											a.TICKET_BASCULA_ID,
										    f.nombre_archivo AS TICKET_BASCULA_NAME,
											a.PREFILE_ID,
											g.nombre_archivo AS PREFILE_NAME,
											g.referencia AS PREFILE_ENTRY_NUMBER,
										    c.nombreaa
									 FROM bodega.facturas_expo AS a LEFT JOIN 
									      bodega.cltes_expo AS b ON b.gcliente = a.NUMCLIENTE LEFT JOIN
										  bodega.aaa AS c ON c.numeroaa=a.NOAAA LEFT JOIN
										  bodega.documentos_expo AS d ON d.id_documento=a.PACKING_LIST_ID LEFT JOIN
										  bodega.documentos_expo AS e ON e.id_documento=a.CERTIFICADO_ORIGEN_ID LEFT JOIN
										  bodega.documentos_expo AS f ON f.id_documento=a.TICKET_BASCULA_ID LEFT JOIN
										  bodega.documentos_expo AS g ON g.id_documento=a.PREFILE_ID
									 WHERE a.SALIDA_NUMERO=".$nSalidaNumero;
									 
						$query_facturas = mysqli_query($cmysqli, $consulta);
						if (!$query_facturas) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al consultar facturas de la salida.'; 
							$respuesta['Error'] = ' ['.$error.']';
						} else {
							while($row_fact = mysqli_fetch_object($query_facturas)){ 
								$aRow = array(
									'clienteid' => ((is_null($row_fact->NUMCLIENTE))? '': $row_fact->NUMCLIENTE),
									'cliente' => ((is_null($row_fact->cnombre))? '': $row_fact->cnombre),
									'referencia' => $row_fact->REFERENCIA,
									'patente' => ((is_null($row_fact->PATENTE))? '': $row_fact->PATENTE),
									'pedimento' => $row_fact->PEDIMENTO,
									'aaaid' => ((is_null($row_fact->NOAAA))? '': $row_fact->NOAAA),
									'aaa' => ((is_null($row_fact->nombreaa))? '': $row_fact->nombreaa),
									'factura' => $row_fact->FACTURA_NUMERO,
									'tiposalida' => ((is_null($row_fact->TIPOSALIDA))? '': $row_fact->TIPOSALIDA),
									'caja' => ((is_null($row_fact->CAJA))? '': $row_fact->CAJA),
									'uuid' => $row_fact->UUID,
									'cons_fac_ped' => $row_fact->CONS_FACT_PED,
									'num_rem_ped' => ((is_null($row_fact->NUM_REM_PED))? '': $row_fact->NUM_REM_PED),
									'packing_list_id' => ((is_null($row_fact->PACKING_LIST_ID))? '': $row_fact->PACKING_LIST_ID),
									'packing_list_name' => ((is_null($row_fact->PACKING_LIST_NAME))? '': $row_fact->PACKING_LIST_NAME),
									'certificado_origen_id' => ((is_null($row_fact->CERTIFICADO_ORIGEN_ID))? '': $row_fact->CERTIFICADO_ORIGEN_ID),
									'certificado_origen_name' => ((is_null($row_fact->CERTIFICADO_ORIGEN_NAME))? '': $row_fact->CERTIFICADO_ORIGEN_NAME),
									'ticket_bascula_id' => ((is_null($row_fact->TICKET_BASCULA_ID))? '': $row_fact->TICKET_BASCULA_ID),
									'ticket_bascula_name' => ((is_null($row_fact->TICKET_BASCULA_NAME))? '': $row_fact->TICKET_BASCULA_NAME),
									'prefile_id' => ((is_null($row_fact->PREFILE_ID))? '': $row_fact->PREFILE_ID),
									'prefile_name' => ((is_null($row_fact->PREFILE_NAME))? '': $row_fact->PREFILE_NAME),
									'prefile_entry_number' => ((is_null($row_fact->PREFILE_ENTRY_NUMBER))? '': $row_fact->PREFILE_ENTRY_NUMBER)
								);
								
								array_push($aFacturas, $aRow);
							}
						}
						
						$aSalidaData = array(
							'salidanumero' => $row->salidanumero,
							'fecha' => $row->fecha,
							'lineatransp' => $row->lineatransp,
							'caja' => $row->caja,
							'aduana' => $row->aduana,
							'notransfer' => $row->notransfer,
							'nombretransfer' => $row->nombretransfer,
							'noentrega' => $row->noentrega,
							'Nombreentrega' => $row->Nombreentrega,
							'direntrega' => $row->direntrega,
							'tiposalida' => $row->tiposalida,
							'cruces' => $row->cruces,
							'usuario' => $row->usuario,
							'urgente' => $row->urgente,
							'horaentrega' => $row->horaentrega,
							'recibio' => $row->recibio,
							'indicaciones' => $row->indicaciones,
							'observaciones' => $row->observaciones,
							'ferrocarril' => $row->ferrocarril,
							'viaje' => $row->viaje,
							'leyenda' => $row->leyenda,
							'nolineatransp' => $row->nolineatransp,
							'relacion_docs_name' => ((is_null($row->relacion_docs_name))? '': $row->relacion_docs_name),
							'notificacion_arribo_name' => ((is_null($row->notificacion_arribo_name))? '': $row->notificacion_arribo_name),
							'solicitud_retiro_name' => ((is_null($row->solicitud_retiro_name))? '': $row->solicitud_retiro_name),
							'aFacturas' => $aFacturas
						);
				
						break;
					}
				}
			}
			
			if ((isset($_GET['cruces']) && !empty($_GET['cruces']))) {
				//$_SESSION['aSalidaData'] = $aSalidaData;
				
				$bCruce = '1';
				$aSalidaData = $_SESSION['aSalidaData'];
				/*$aSalidaData['aFacturas'][0]['packing_list_name'] = 'http://www.delbravoweb.com/documentos_expo/cruces/C1108_PackList_3314_20180123135009.pdf';
				$aSalidaData['aFacturas'][0]['certificado_origen_name'] = 'http://www.delbravoweb.com/documentos_expo/cruces/C1108_CerOri_3314_20180123135009.pdf';
				$aSalidaData['aFacturas'][0]['ticket_bascula_name'] = 'http://www.delbravoweb.com/documentos_expo/cruces/C1108_ticketbas_3314_20180123135009.pdf';*/
				
				$sPathFilesCruces = "\\\\192.168.1.126\\documentos_expo\\cruces";
				for ($x = 0; $x <= count($aSalidaData['aFacturas']) - 1; $x++) {
					$sCrucesName = $aSalidaData['aFacturas'][$x]['packing_list_name'];
					
					if ($sCrucesName != '') {
						$aFileData = explode('/', $sCrucesName);
						$sCrucesName = end($aFileData);
						$sPathCrucesName = $sPathFilesCruces . DIRECTORY_SEPARATOR . $sCrucesName;
						
						if (file_exists($sPathCrucesName)) { 
							$respuesta = fcn_copiar_documentos_cruces($cmysqli, 'PKL', $nUniqueId, $sPathCrucesName, $sCrucesName);
							if ($respuesta['Codigo'] == 1) { 
								$aSalidaData['aFacturas'][$x]['packing_list_id']=$respuesta['id_documento'];
								$aSalidaData['aFacturas'][$x]['packing_list_name']=$respuesta['sFileName'];
							} else {
								break;
							}
						}
					}
					
					if ($respuesta['Codigo'] == 1) { 
						$sCrucesName = $aSalidaData['aFacturas'][$x]['certificado_origen_name'];
						
						if ($sCrucesName != '') {
							$aFileData = explode('/', $sCrucesName);
							$sCrucesName = end($aFileData);
							$sPathCrucesName = $sPathFilesCruces . DIRECTORY_SEPARATOR . $sCrucesName;
							
							if (file_exists($sPathCrucesName)) { 
								$respuesta = fcn_copiar_documentos_cruces($cmysqli, 'CDO', $nUniqueId, $sPathCrucesName, $sCrucesName);
								if ($respuesta['Codigo'] == 1) { 
									$aSalidaData['aFacturas'][$x]['certificado_origen_id']=$respuesta['id_documento'];
									$aSalidaData['aFacturas'][$x]['certificado_origen_name']=$respuesta['sFileName'];
								}
							}
						}
					} else {
						break;
					}
					
					if ($respuesta['Codigo'] == 1) { 
						$sCrucesName = $aSalidaData['aFacturas'][$x]['ticket_bascula_name'];
						
						if ($sCrucesName != '') {
							$aFileData = explode('/', $sCrucesName);
							$sCrucesName = end($aFileData);
							$sPathCrucesName = $sPathFilesCruces . DIRECTORY_SEPARATOR . $sCrucesName;
							
							if (file_exists($sPathCrucesName)) { 
								$respuesta = fcn_copiar_documentos_cruces($cmysqli, 'TDB', $nUniqueId, $sPathCrucesName, $sCrucesName);
								if ($respuesta['Codigo'] == 1) { 
									$aSalidaData['aFacturas'][$x]['ticket_bascula_id']=$respuesta['id_documento'];
									$aSalidaData['aFacturas'][$x]['ticket_bascula_name']=$respuesta['sFileName'];
								}
							}
						}
					} else {
						break;
					}
				}				
			}
			
			$aAppData = array(
				'sTask' => $sTask,
				'bCruce' => $bCruce,
				'aSalidaData' => $aSalidaData,
				'nSalidaNumero' => $nSalidaNumero, 
				'nUniqueId' => $nUniqueId
			);
				
			$respuesta['aAppData']=$aAppData;
			echo htmlspecialchars(json_encode($respuesta), ENT_QUOTES, 'UTF-8');
			
			function fcn_copiar_documentos_cruces($cmysqli, $sDocumentoTipo, $nUniqueId, $sPathCrucesName, $sCrucesName){
				$sPathFilesExpo = "\\\\192.168.1.126\\documentos_expo\\salidaExpo";
				$respuesta['Codigo']=1;
				
				$id_documento = 0;
				$sFileName = '';
				
				mysqli_query($cmysqli, 'BEGIN');
				$consulta = "INSERT INTO bodega.documentos_expo (  
								 tipo
								,uniqueid
							 ) VALUES (
								 '".$sDocumentoTipo."'
								,'".$nUniqueId."'
							 )";
							 
				$query = mysqli_query($cmysqli, $consulta);
				if (!$query) {
					$error=mysqli_error($cmysqli);
					$respuesta['Codigo']=-1;
					$respuesta['Mensaje']='Error al guardar archivo.'; 
					$respuesta['Error'] = ' ['.$error.']';
				} else {
					$id_documento = mysqli_insert_id($cmysqli);
				}
				
				/* ..:: Archivo(s) ::.. */
				if ($respuesta['Codigo'] == 1) {
					$ext = explode('.', $sCrucesName);
					$ext = array_reverse($ext);
					$sName = array_pop($ext);
					$sFileName = $sName.'_'.$sDocumentoTipo.'_'.$id_documento.'.pdf';
					$target = $sPathFilesExpo . DIRECTORY_SEPARATOR . $sFileName;
					
					if (!copy($sPathCrucesName, $target)) {
						$respuesta['Codigo']=-1;
						$respuesta['Mensaje']='No se copio el archivo.'; 
						$respuesta['Error'] = '';
					}
					
					if ($respuesta['Codigo'] == 1) {
						$consulta = "UPDATE bodega.documentos_expo
									 SET nombre_archivo='".$sFileName."'
									 WHERE id_documento=".$id_documento;
											 
						$query = mysqli_query($cmysqli, $consulta);
						if (!$query) {
							$error=mysqli_error($cmysqli);
							$respuesta['Codigo']=-1;
							$respuesta['Mensaje']='Error al editar nombre del archivo guardado.'; 
							$respuesta['Error'] = ' ['.$error.']';
						}
					}
				}
				
				if ($respuesta['Codigo'] == 1) { 
					mysqli_query($cmysqli, "COMMIT");
					$respuesta['Mensaje']='Archivo guardado correctamente!!!';
				} else {
					mysqli_query($cmysqli, "ROLLBACK");
				}
				
				$respuesta['id_documento']=$id_documento;
				$respuesta['sFileName']= $sFileName;
				return $respuesta;
			}
		?>"/>
		
		<!-- MODAL LOAD CONFIGURACION -->
		<div id="modalloadconfig" class="modal" style="z-index:9999;">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-body">
						<div id="modalloadconfig_mensaje"></div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MESSAGE BOX OK -->
		<div id="modalmessagebox_ok" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span style="color:#3c763d;" id="modalmessagebox_ok_titulo"> </span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-success" style="margin-bottom:0px;">
									<div id="modalmessagebox_ok_mensaje"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="modalmessagebox_btn_ok" type="button" class="btn btn-info" data-dismiss="modal">OK</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MESSAGE BOX ERROR-->
		<div id="modalmessagebox_error" class="modal fade modal-danger" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span style="color:#a94442;" id="modalmessagebox_error_span"> </span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-danger" style="margin-bottom:0px;">
									<div id="modalmessagebox_error_mensaje"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-info" data-dismiss="modal">OK</button>
					</div>
				</div>
			</div>
		</div>
	
		<!-- MODAL CONFIRM -->
		<div id="modalconfirm" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<!--button type="button" class="close" data-dismiss="modal">&times;</button-->
						<h4 class="modal-title"><span id="modalconfirm_title"> </span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-warning" style="margin-bottom:0px;">
									<div id="modalconfirm_mensaje"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="modalconfirm_btn_cancel" type="button" class="btn btn-danger pull-left"><i class="fa fa-ban"></i> Cancelar</button>
						<button id="modalconfirm_btn_ok" type="button" class="btn btn-success"><i class="fa fa-check"></i> Aceptar</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL SUBIR PEDIMENTO SIMPLIFICADO -->
		<div id="modal_add_pedsimp" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span id="modal_add_pedsimp_title"> </span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-info">
									<strong>Info!</strong> Favor de ingresar el pdf del Pedimento simplificado.
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Pedimento Simplificado</label>
									<input id="ifile_add_pedsimp" type="file" class="file" accept="application/pdf"/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div id="modal_add_pedsimp_mensaje"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>
						<button id="modal_add_pedsimp_btn_ok" type="button" class="btn btn-success" onclick="ajax_set_pedimento_simplificado();"><i class="fa fa-check"></i> Guardar</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL SUBIR PEDIMENTO NORMAL -->
		<div id="modal_add_pednormal" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span id="modal_add_pednormal_title"> </span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-info">
									<strong>Info!</strong> Favor de ingresar el pdf de la copia del pedimento para el Transpotista.
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Copia Pedimento Transportista</label>
									<input id="ifile_add_pednormal" type="file" class="file" accept="application/pdf"/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div id="modal_add_pednormal_mensaje"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>
						<button id="modal_add_pednormal_btn_ok" type="button" class="btn btn-success" onclick="ajax_set_pedimento_normal();"><i class="fa fa-check"></i> Guardar</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL ASIGNAR DOCUMENTO -->
		<div id="modal_asig_doc" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Asignar Documento(s)</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-info">
									<strong>Info!</strong> Favor de seleccionar un tipo para que se desplieguen los documentos
								</div>
							</div>
							<div class="col-xs-12 col-sm-3">
								<div class="alert alert-info" style="padding:5px;">
									<strong>PK</strong> Packing List
								</div>	
							</div>
							<div class="col-xs-12 col-sm-3">
								<div class="alert alert-info" style="padding:5px;">
									<strong>CD</strong> Certificado de Origen
								</div>
							</div>
							<div class="col-xs-12 col-sm-3">
								<div class="alert alert-info" style="padding:5px;">
									<strong>TB</strong> Ticket de Bascula
								</div>
							</div>
							<div class="col-xs-12 col-sm-3">
								<div class="alert alert-info" style="padding:5px;">
									<strong>PF</strong> Prefile
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12 col-md-3">
								<div class="form-group">
									<label>Tipo</label>
									<select id="isel_mdl_asig_doc_documento_tipo" class="form-control" onchange="ajax_get_documentos();">
										<option value="PKL" selected>Packing List</option>
										<option value="CDO">Certificado de Origen</option>
										<option value="PRE">Prefile</option>
										<option value="TDB">Ticket de Bascula</option>
									</select>
								</div>
							</div>
							
							<div class="col-xs-12 col-md-8">
								<div class="form-group">
									<label>Documento(s)</label>
									<select id="isel_mdl_asig_doc_documentos" class="form-control"></select>
								</div>
							</div>
							
							<div class="col-xs-12 col-md-1">
								<div class="form-group">
									<label class="control-label hidden-xs hidden-sm">&nbsp;</label>
									<br class="hidden-xs hidden-sm"/>
									<button type="button" class="btn btn-info pull-right" onClick="fcn_ver_documento();"><i class="fa fa-eye" aria-hidden="true"></i></button>
								</div>
							</div>
						</div>
						<div class="row">				
							<div class="col-xs-12">
								<div class="table-responsive">
									<table id="dtFacturasDocs" class="table table-striped table-bordered" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th></th>
												<th>Referencia</th>
												<th>Factura</th>
												<th>PK</th>
												<th>CD</th>
												<th>TB</th>
												<th>PF</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div id="idiv_mdl_asig_doc_mensaje"></div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-warning" style="margin:0px;">
									<strong>Alerta!</strong> Los cambios se aplicar&aacute;n hasta que se guarde la salida
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>
						<button type="button" class="btn btn-success" onclick="fcn_asignar_documentos();"><i class="fa fa-check"></i> Asignar Documento</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL EDITAR DATOS Y DOCUMENTO(S) -->
		<div id="modal_edit_fac_doc" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Editar Factura</h4>
					</div>
					<div class="modal-body">
						<h4>Tipo de Salida</h4>
						<hr style="margin-top: 0px; margin-bottom: 8px; border-color: #ddd;"/>
						<div class="row">
							<div class="col-xs-12 col-md-6">
								<div class="form-group">
									<label>Tipo de Salida</label>
									<select id="isel_edit_tipo_salida" class="form-control" tabindex="68" onchange="fcn_sel_tipo_salida_change('editar');">
									<?php
										$consulta = "SELECT titulo, visible
													 FROM bodega.tipo_salida_expo
													 ORDER BY titulo";
														
										$query = mysqli_query($cmysqli, $consulta);
										if (!$query) {
											$error=mysqli_error($cmysqli);
											echo '<option value="">'.$error.'</option>';
										} else {
											while($row = mysqli_fetch_object($query)){
												$sDisabled = (($row->visible == 1)? '' : 'disabled="disabled"');
												echo '<option value="'.$row->titulo.'" '.$sDisabled.'>'.$row->titulo.'</option>';
											}
										}
									?>
									</select>
								</div>
							</div>
							<div class="col-xs-12 col-md-6">
								<div class="form-group">
									<label class="hidden-xs hidden-sm">Numero <span id="ispan_edit_tipo_salida_caja"></span></label>
									<input id="itxt_edit_tipo_salida_caja" type="text" class="form-control text-uppercase" tabindex="69"/>
								</div>
							</div>
						</div>
						
						<h4>Documentos</h4>
						<hr style="margin-top: 0px; margin-bottom: 8px; border-color: #ddd;"/>
						<div class="row">
							<div class="col-xs-12 col-md-6">
								<div id="igpo_edit_packlist_docs" class="form-group" style="display:block;">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Packing List</label>
									<input id="ifile_edit_packlist" type="file" class="file" accept="application/pdf"/>
								</div>
								<div id="igpo_edit_packlist_docs_btn" class="form-group" style="display:none;">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Packing List</label>
									<br>
									<button type="button" class="btn btn-primary" onclick="fcn_docs_facturas_options('packlist', 'ver');"><i class="fa fa-eye" aria-hidden="true"></i> Ver</button>
									<button type="button" class="btn btn-danger pull-right" onclick="fcn_docs_facturas_options('packlist', 'nuevo');"><i class="fa fa-trash" aria-hidden="true"></i> Eliminar</button>
								</div>
							</div>
							<div class="col-xs-12 col-md-6">
								<div id="igpo_edit_cerOrigen_docs" class="form-group" style="display:block;">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Certificado de Origen</label>
									<input id="ifile_edit_cerOrigen" type="file" class="file" accept="application/pdf"/>
								</div>
								<div id="igpo_edit_cerOrigen_docs_btn" class="form-group" style="display:none;">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Certificado de Origen</label>
									<br>
									<button type="button" class="btn btn-primary" onclick="fcn_docs_facturas_options('cerOrigen', 'ver');"><i class="fa fa-eye" aria-hidden="true"></i> Ver</button>
									<button type="button" class="btn btn-danger pull-right" onclick="fcn_docs_facturas_options('cerOrigen', 'nuevo');"><i class="fa fa-trash" aria-hidden="true"></i> Eliminar</button>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12 col-md-6">
								<div id="igpo_edit_prefile_docs" class="form-group" style="display:block;">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Prefile</label>
									<input id="ifile_edit_prefile" type="file" class="file" accept="application/pdf"/>
								</div>
								<div id="igpo_edit_prefile_docs_btn" class="form-group" style="display:none;">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Prefile</label>
									<br>
									<button type="button" class="btn btn-primary" onclick="fcn_docs_facturas_options('prefile', 'ver');"><i class="fa fa-eye" aria-hidden="true"></i> Ver</button>
									<button type="button" class="btn btn-danger pull-right" onclick="fcn_docs_facturas_options('prefile', 'nuevo');"><i class="fa fa-trash" aria-hidden="true"></i> Eliminar</button>
								</div>
								<div id="igpo_edit_prefile_docs_select" class="form-group" style="display:none;">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Prefile Documentos</label>
									<div class="input-group">
										<select id="isel_edit_prefile_documentos" class="form-control"></select>
										<span class="input-group-btn">
											<button class="btn btn-info" type="button" onClick="fcn_docs_facturas_options('prefile', 'ver');" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="51">
												<i class="fa fa-eye" aria-hidden="true"></i>
											</button>
										</span>
										<span class="input-group-btn">
											<button class="btn btn-danger" type="button" onClick="fcn_docs_facturas_prefile_cancel();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="51">
												<i class="fa fa-ban"></i>
											</button>
										</span>
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-md-6">
								<div class="form-group">
									<label>Entry Number</label>
									<div class="input-group">
										<input id="itxt_edit_prefile_entry_number" type="text" class="form-control text-uppercase" maxlength="13"/>
										<span class="input-group-btn">
											<button class="btn btn-info" type="button" onClick="ajax_get_documentos_entry_number();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="51">
												<i class="fa fa-search" aria-hidden="true"></i> Buscar
											</button>
										</span>
									</div>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12 col-md-6">
								<div id="igpo_edit_ticketbas_docs" class="form-group" style="display:block;">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Ticket de Bascula</label>
									<input id="ifile_edit_ticketbas" type="file" class="file" accept="application/pdf"/>
								</div>
								<div id="igpo_edit_ticketbas_docs_btn" class="form-group" style="display:none;">
									<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Ticket de Bascula</label>
									<br>
									<button type="button" class="btn btn-primary" onclick="fcn_docs_facturas_options('ticketbas', 'ver');"><i class="fa fa-eye" aria-hidden="true"></i> Ver</button>
									<button type="button" class="btn btn-danger pull-right" onclick="fcn_docs_facturas_options('ticketbas', 'nuevo');"><i class="fa fa-trash" aria-hidden="true"></i> Eliminar</button>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div id="idiv_mdl_edit_fac_doc_mensaje"></div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-warning" style="margin:0px;">
									<strong>Alerta!</strong> Los cambios se aplicar&aacute;n hasta que se guarde la salida
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>
						<button type="button" class="btn btn-success" onclick="fcn_docs_facturas_subir();"><i class="fa fa-check"></i> Actualizar Factura</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<h2><strong><i class="fa fa-truck fa-flip-horizontal" aria-hidden="true"></i> Salida de Exportaci&oacute;n</strong></h2> 
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<label>Fecha y Hora</label>
								<div id="itxt_fecha" class='input-group date' data-format="dd/MM/yyyy HH:mm:ss PP">
									<input type='text' class="form-control"/>
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-4">
							<div class="form-group" style="text-align: center;">
								<label class="hidden-xs hidden-sm" style="width:100%;">&nbsp;</label>
								Ferrocarril&nbsp;&nbsp;<input id="ickb_ferrocarril" class="form-check-input" type="checkbox" tabindex="1">
							</div>
						</div>
						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<label>Salida #</label>
								<input id="itxt_numero_salida" type="text" class="form-control text-center" value="" disabled="disabled"/>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label>L. Transp</label>
								<div class="input-group">
									<select id="isel_lineast" class="form-control" tabindex="5"></select>
									<span id="ibtn_agregar_lineat" class="input-group-btn">
										<button class="btn btn-success" type="button" onclick="fcn_agregar_lineat();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="6">
											<i class="fa fa-plus" aria-hidden="true"></i>
										</button>
									</span>
								</div>
							</div>
						</div>
					</div>
					
					<div class="row">
						<!--div class="col-xs-12 col-md-4">
							<div class="form-group">
								<label>Tipo de Salida</label>
								<select id="isel_tipo_salida" class="form-control" tabindex="10">
									<option value=""></option>
									<option value="PLACAS">PLACAS</option>
									<option value="CAJA">CAJA</option>
									<option value="PLATAFORMA">PLATAFORMA</option>
									<option value="FURGON">FURGON</option>
									<option value="GONDOLA">GONDOLA</option>
									<option value="CARRO">CARRO</option>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<label class="hidden-xs hidden-sm">&nbsp;</label>
								<input id="itxt_tipo_salida_caja" type="text" class="form-control text-uppercase" tabindex="15"/>
							</div>
						</div-->
						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<label>Aduana</label>
								<select id="isel_aduana" class="form-control" tabindex="20">
									<option value=""></option>
									<option value="240">240 - Laredo</option>
									<option value="800">800 - Colombia</option>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-8">
							<div class="form-group">
								<label>Transfer</label>
								<div class="input-group">
									<select id="isel_transfer" class="form-control" tabindex="21"></select>
									<span id="ibtn_agregar_transfer" class="input-group-btn" tabindex="22">
										<button class="btn btn-success" type="button" onclick="fcn_agregar_transfer();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="26">
											<i class="fa fa-plus" aria-hidden="true"></i>
										</button>
									</span>
								</div>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label>Entregar En</label>
								<div class="input-group">
									<select id="isel_entregar_en" class="form-control" tabindex="30"></select>
									<span id="ibtn_agregar_entregas" class="input-group-btn">
										<button class="btn btn-success" type="button" onclick="fcn_agregar_entregas();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="31">
											<i class="fa fa-plus" aria-hidden="true"></i>
										</button>
									</span>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label class="hidden-xs hidden-sm">Direcci&oacute;n</label>
								<input id="itxt_entregar_en_direccion" type="text" class="form-control text-uppercase" disabled="disabled"/>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<label>Numero de Cruces en Salida</label>
								<input id="itxt_cruces_en_salida" type="text" class="form-control text-center" value="0" tabindex="35"/>
							</div>
						</div>
						<div class="col-xs-12 col-sm-6 col-md-4">
							<div class="form-group">
								<label class="hidden-xs hidden-sm" style="width:100%;">&nbsp;</label>
								Marcar Como Urgente?&nbsp;&nbsp;<input id="ickb_urgente" class="form-check-input" type="checkbox" tabindex="36"/>
							</div>
						</div>
						<div class="col-xs-12 col-sm-6 col-md-4">
							<div class="form-group">
								<label class="hidden-xs hidden-sm" style="width:100%;">&nbsp;</label>
								Leyenda Transportista&nbsp;&nbsp;<input id="ickb_leyenda_trans" class="form-check-input" type="checkbox" tabindex="37"/>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-3">
							<div class="form-group">
								<label>Hora de Entrega</label>
								<div id="itxt_hora_entrega" class='input-group date' tabindex="40">
									<input type='text' class="form-control" tabindex="41"/>
									<span class="input-group-addon">
										 <span class="glyphicon glyphicon-time"></span>
									</span>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label>Recibio</label>
								<input id="itxt_recibio" type="text" class="form-control text-uppercase" tabindex="42"/>
							</div>
						</div>
						<div class="col-xs-12 col-md-3">
							<div class="form-group">
								<label>Viaje No</label>
								<input id="itxt_numero_viaje" type="text" class="form-control text-uppercase" tabindex="43"/>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label>Indicaciones</label>
								<input id="itxt_indicaciones" type="text" class="form-control text-uppercase" maxlength="255" tabindex="45"/>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label>Observaciones (Se envian en correo electr&oacute;nico)</label>
								<input id="itxt_observaciones" type="text" class="form-control text-uppercase" maxlength="500" tabindex="46"/>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div id="igpo_relacion_docs" class="form-group" style="display:block;">
								<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Relaci&oacute;n de Documentos</label>
								<input id="ifile_relacion_docs" type="file" class="file" accept="application/pdf"/>
							</div>
							<div id="igpo_relacion_docs_btn" class="form-group" style="display:none;">
								<label><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Relaci&oacute;n de Documentos</label>
								<br>
								<button id="ibtn_relacion_docs_ver" type="button" class="btn btn-primary" onclick="fcn_relacion_docs_opciones('ver');"><i class="fa fa-eye" aria-hidden="true"></i> Ver</button>
								<button id="ibtn_relacion_docs_nuevo" type="button" class="btn btn-danger pull-right" onclick="fcn_relacion_docs_opciones('nuevo');"><i class="fa fa-trash" aria-hidden="true"></i> Eliminar</button>
							</div>
						</div>
						<div class="col-xs-12 col-md-6"></div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div id="igpo_NOA" class="form-group">
								<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Notificaci&oacute;n de Arribo (NOA)</label>
								<input id="ifile_NOA" type="file" class="file" accept="application/pdf"/>
							</div>
							<div id="igpo_NOA_btn" class="form-group" style="display:none;">
								<label><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Notificaci&oacute;n de Arribo (NOA)</label>
								<br>
								<button id="ibtn_NOA_ver" type="button" class="btn btn-primary" onclick="fcn_NOA_opciones('ver');"><i class="fa fa-eye" aria-hidden="true"></i> Ver</button>
								<button id="ibtn_NOA_nuevo" type="button" class="btn btn-danger pull-right" onclick="fcn_NOA_opciones('nuevo');"><i class="fa fa-trash" aria-hidden="true"></i> Eliminar</button>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div id="igpo_solicitud_retiro" class="form-group">
								<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Solicitud de Retiro</label>
								<input id="ifile_solicitud_retiro" type="file" class="file" accept="application/pdf"/>
							</div>
							<div id="igpo_solicitud_retiro_btn" class="form-group" style="display:none;">
								<label><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Solicitud de Retiro</label>
								<br>
								<button id="ibtn_solicitud_retiro_ver" type="button" class="btn btn-primary" onclick="fcn_solicitud_retiro_opciones('ver');"><i class="fa fa-eye" aria-hidden="true"></i> Ver</button>
								<button id="ibtn_solicitud_retiro_nuevo" type="button" class="btn btn-danger pull-right" onclick="fcn_solicitud_retiro_opciones('nuevo');"><i class="fa fa-trash" aria-hidden="true"></i> Eliminar</button>
							</div>
						</div>
					</div>
					
					<h4><i class="fa fa-file-o" aria-hidden="true"></i> Facturas</h4>
					
					<hr style="margin-top: 0px; margin-bottom: 8px; border-color: #ddd;"/>
					
					<div class="row">
						<div class="col-xs-12 col-md-3">
							<div class="form-group">
								<label>Referencia</label>
								<div class="input-group">
									<input id="itxt_referencia" type="text" class="form-control text-uppercase" maxlength="15" tabindex="50"/>
									<span id="ibtn_buscar_referencia" class="input-group-btn">
										<button class="btn btn-info" type="button" onClick="ajax_get_referencia_casa();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="51">
											<i class="fa fa-search" aria-hidden="true"></i>
										</button>
									</span>
									<span id="ibtn_buscar_referencia_loading" class="input-group-btn" style="display:none;">
										<button type="button" class="btn btn-info" tabindex="51"><i class="fa fa-spinner fa-pulse"></i></button>
									</span>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<label>Cliente</label>
								<select id="isel_cliente" class="form-control" tabindex="52"></select>
							</div>
						</div>
						
						<div class="col-xs-12 col-md-5">
							<div class="form-group">
								<label>Agencia Aduanal Americana</label>
								<div class="input-group">
									<select id="isel_aa_ame" class="form-control" tabindex="53"></select>
									<span id="ibtn_agregar_aa_ame" class="input-group-btn">
										<button class="btn btn-success" type="button" onclick="fcn_agregar_aa_ame();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="61">
											<i class="fa fa-plus" aria-hidden="true"></i>
										</button>
									</span>
								</div>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<label>Patente</label>
								<input id="itxt_patente" type="text" class="form-control text-uppercase" maxlength="4" tabindex="60" disabled="disabled"/>
							</div>
						</div>
						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<label>Pedimento</label>
								<input id="itxt_pedimento" type="text" class="form-control text-uppercase" maxlength="30" tabindex="61" disabled="disabled"/>
							</div>
						</div>
						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<label>Factura</label>
								<div class="input-group">
									<select id="isel_factura" class="form-control" tabindex="62"></select>
								</div>
								<!--input id="itxt_factura" type="text" class="form-control text-uppercase" maxlength="30"/-->
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label>Tipo de Salida</label>
								<select id="isel_tipo_salida" class="form-control" tabindex="65" onchange="fcn_sel_tipo_salida_change('principal');">
									<?php
										$consulta = "SELECT titulo, visible
													 FROM bodega.tipo_salida_expo
													 WHERE visible=1
													 ORDER BY titulo";
														
										$query = mysqli_query($cmysqli, $consulta);
										if (!$query) {
											$error=mysqli_error($cmysqli);
											echo '<option value="">'.$error.'</option>';
										} else {
											echo '<option value=""></option>';
											while($row = mysqli_fetch_object($query)){
												echo '<option value="'.$row->titulo.'">'.$row->titulo.'</option>';
											}
										}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label class="hidden-xs hidden-sm">Numero <span id="ispan_tipo_salida_caja"></span></label>
								<input id="itxt_tipo_salida_caja" type="text" class="form-control text-uppercase" tabindex="66"/>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Packing List</label>
								<input id="ifile_packlist" type="file" class="file" accept="application/pdf"/>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Certificado de Origen</label>
								<input id="ifile_cerOrigen" type="file" class="file" accept="application/pdf"/>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div id="igpo_prefile_docs" class="form-group" style="display:block;">
								<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Prefile</label>
								<input id="ifile_prefile" type="file" class="file" accept="application/pdf"/>
							</div>
							<div id="igpo_prefile_docs_btn" class="form-group" style="display:none;"></div>
							<div id="igpo_prefile_docs_select" class="form-group" style="display:none;">
								<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Prefile Documentos</label>
								<div class="input-group">
									<select id="isel_prefile_documentos" class="form-control"></select>
									<span class="input-group-btn">
										<button class="btn btn-info" type="button" onClick="fcn_docs_facturas_options('prefile', 'ver');" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="70">
											<i class="fa fa-eye" aria-hidden="true"></i>
										</button>
									</span>
									<span class="input-group-btn">
										<button class="btn btn-danger" type="button" onClick="fcn_docs_facturas_prefile_cancel();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="71">
											<i class="fa fa-ban"></i>
										</button>
									</span>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label>Entry Number</label>
								<div class="input-group">
									<input id="itxt_prefile_entry_number" type="text" class="form-control text-uppercase" maxlength="13"/>
									<span class="input-group-btn">
										<button class="btn btn-info" type="button" onClick="ajax_get_documentos_entry_number();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;" tabindex="75">
											<i class="fa fa-search" aria-hidden="true"></i> Buscar
										</button>
									</span>
								</div>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Ticket de Bascula</label>
								<input id="ifile_ticketbas" type="file" class="file" accept="application/pdf"/>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">		
							<div class="form-group">
								<label class="control-label hidden-xs hidden-sm">&nbsp;</label>
								<br class="hidden-xs hidden-sm"/>
								<button id="ibtn_agregar_factura" class="btn btn-success pull-right" type="button" onClick="fcn_agregar_factura();" tabindex="80">
									<i class="fa fa-plus" aria-hidden="true"></i> Agregar Factura
								</button>
							</div>
						</div>
					</div>
					
					<div class="row">				
						<div class="col-xs-12">
							<div class="table-responsive">
								<table id="dtdetsalidaExpo" class="table table-striped table-bordered" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Referencia</th>
											<th>Cliente</th>
											<th>Patente</th>									
											<th>Pedimento</th>
											<th>Factura</th>
											<th>Tipo-Caja</th>
											<th>AAA</th>
											<th width="60px"></th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
					
					<!--h4><i class="fa fa-cloud-upload" aria-hidden="true"></i> Documentos</h4>
					
					<hr style="margin-top: 0px; margin-bottom: 8px; border-color: #ddd;"/>
					
					<div class="row">	
						<div class="col-xs-12 col-md-3">
							<div class="form-group">
								<label>Tipo</label>
								<select id="isel_documento_tipo" class="form-control" onchange="fcn_documentos_tipo_change();">
									<option value="PKL" selected>Packing List</option>
									<option value="CDO">Certificado de Origen</option>
									<option value="PRE">Prefile</option>
									<option value="TDB">Ticket de Bascula</option>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div id="igpo_prefile" class="form-group">
								<label class="control-label"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Documento(s) <small>(Varios archivos generan uno solo)</small></label>
								<input id="ifile_documento" type="file" class="file" multiple accept="application/pdf"/>
							</div>
						</div>
						<div class="col-xs-12 col-md-2">
							<div class="form-group">
								<label id="ilbl_documento_referencia"></label>
								<input id="itxt_documento_referencia" type="text" class="form-control text-uppercase" maxlength="255"/>
							</div>
						</div>
						<div class="col-xs-12 col-md-1">
							<div class="form-group">
								<label class="control-label hidden-xs hidden-sm">&nbsp;</label>
								<br class="hidden-xs hidden-sm"/>
								<button type="button" class="btn btn-info pull-right" onClick="fcn_subir_documentos();"><i class="fa fa-cloud-upload" aria-hidden="true"></i></button>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<button type="button" class="btn btn-primary" onClick="fcn_mostrar_modal_asignar_documentos();"><i class="fa fa-hand-pointer-o" aria-hidden="true"></i> Asignar Archivo a Factura</button>
							</div>
						</div>
					</div-->
					
					<div class="row">
						<div class="col-xs-12">
							<div id="idiv_message" style="display:none;"></div>
						</div>
					</div>
				</div>
				
				<div class="panel-footer">
					<div class="row">
						<div class="col-xs-12">
							<button id="ibtn_imprimir_salida" type="button" class="btn btn-primary" style="display:none;" onClick="fcn_imprimir_salida();"><i class="fa fa-print" aria-hidden="true"></i> Imprimir Salida</button>
							<button id="ibtn_guardar_salida" type="button" class="btn btn-success pull-right" onClick="ajax_set_salida();" tabindex="85"><i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar Salida</button>
						</div>
					</div>
				</div>
			</div>
		</div>
    </div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!--script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="./bootstrap/js/bootstrap.min.js"></script-->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--script src="../../assets/js/ie10-viewport-bug-workaround.js"></script-->
	<?php include('foot.php');?>
	
	<script type="text/javascript" src="../bower_components/jquery/dist/jquery.js"></script>
	
	<script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
	
	<script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->  
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]--> 
	
	<!-- DataTables JavaScript -->
    <script src="../bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
	<script src="../bower_components/datatables.net-responsive/js/dataTables.responsive.js"></script>
	<script src="../bower_components/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script src="../bower_components/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
	<script src="../bower_components/datatables.net-select/js/dataTables.select.min.js"></script>
	<script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	<script src="../js/editor.select2.js"></script>
	
	<!-- Fileinput JS -->
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/fileinput.min.js"></script>
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/locales/es.js"></script>
	
	<!-- Select2 -->
    <script src="../bower_components/select2/dist/js/select2.min.js"></script>
	<script src="../bower_components/select2/dist/js/i18n/es.js"></script>
	
	<!-- Moment -->
	<script src="../bower_components/moment/min/moment.min.js" type="text/javascript" ></script>
	
	<!-- Bootstrap Datepicker -->
	<script src="../bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js" type="text/javascript" ></script>
	
	<!--TouchSpin-->
	<script type="text/javascript" language="javascript" src="../bower_components/touchspin/jquery.bootstrap-touchspin.js"></script>
	
	<!--MaskedInput-->
	<script  type="text/javascript" language="javascript" src="../plugins/maskedinput/jquery.maskedinput.min.js"></script>
	
	<script src="../js/salidaExpo.js?160420181131"></script>
	<script src="../js/catalogosExpo.js?130420181600"></script>
</body>
</html>