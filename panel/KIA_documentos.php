<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="expires" content="0">
	
    <title>Enviar documentacion de pedimentos para el cliente KIA</title>

    <!-- Bootstrap Core CSS -->
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- DataTables CSS -->
    <link href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet">
	<link href="../editor/css/editor.bootstrap.min.css" rel="stylesheet">
	<!-- FileInput -->
	<link href="../bower_components/bootstrap-fileinput-4.2.3/css/fileinput.min.css" rel="stylesheet"/>
	
	<!-- Custom styles for this template -->
    <link href="../bootstrap/css/navbar.css" rel="stylesheet">
	
	<!--style>
		body.modal-open {
			position: fixed;
		}
	</style-->
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php');?>
		
		<!-- MODAL LOAD CONFIGURACION -->
		<div id="modalloadconfig" class="modal fade" style="z-index:9999;">
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
						<h4 class="modal-title"><span id ="modalmessagebox_ok_titulo"> </span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-success" style="margin-bottom:0px;">
									<div id="modalmessagebox_ok_mensaje"></div>
								</div>
							</div>
						</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-info" data-dismiss="modal" style="width:150px">OK</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MESSAGE BOX ERROR-->
		<div id="modalmessagebox_error" class="modal fade" style="z-index:9999;">
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal">&times;</button>
		                <h4 class="modal-title"><span id ="modalmessagebox_error_span"> </span></h4>
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
		                <button type="button" class="btn btn-info" data-dismiss="modal" style="width:150px">OK</button>
		            </div>
		        </div>
		    </div>
		</div>
		
		<!-- MODAL CONFIRM -->
		<div id="modaldatos" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-files-o" aria-hidden="true"></i> Enviar documentos de la referencia <span id="lbl_titulo_referencia"></span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia</div>
										<input id="txt_referencia_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Aduana</div>
										<input id="txt_aduana_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Patente</div>
										<input id="txt_patente_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Pedimento</div>
										<input id="txt_pedimento_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">NUMERO PARTE</div>
										<select class="form-control" id="sel_numero_parte">
											<option value="GB270-201703080001" selected>GB270-201703080001</option>
										</select>
										<!--span class="input-group-btn">
											<button id="btn_agregar_transportista" type="button" class="btn btn-info"><i class="fa fa-plus"></i> Nuevo</button>
										</span-->
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dtdetalle" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="def_app_center">NUMERO_FACTURA</th>
													<th class="def_app_center">PARTIDA</th>
													<th class="def_app_center">NUMERO_PARTE</th>
													<th class="def_app_center">FRACCION</th>
													<th class="def_app_center">DESCRIPCION</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="btn_enviar_documentos_pedimento" type="button" class="btn btn-success"><i class="fa fa-paper-plane-o" aria-hidden="true"></i> Enviar Documentaci&oacute;n</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL DESCARGAR EXPEDIENTE -->
		<div id="modalexpediente" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-download" aria-hidden="true"></i> Descargar Expediente [<span id="lbl_titulo_pedimeto_kia"></span>]</h4>
					</div>
					<div class="modal-body">
						<div class="row">
		                    <div class="col-xs-12">
								<label><strong>Favor de agregar los archivos PDF de las Remesas generadas en este pedimento.</strong></label>
		                    </div>
		                </div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">NUMERO PARTE</div>
										<select class="form-control" id="sel_numero_parte_exp">
											<option value="GB270-201703080001" selected>GB270-201703080001</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div id="subir_remesas_pedimento_kia"></div>
						</div>
						<div class="row">
		                    <div class="col-xs-12">
								<div id="mensaje_mdl_descargar_expediente"></div>
		                    </div>
		                </div>
					</div>
					<div class="modal-footer">
						<button id="btn_generar_descargar_expediente" type="button" class="btn btn-success"><i class="fa fa-cog" aria-hidden="true"></i> Generar/Descargar</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<h3><strong><i class="fa fa-files-o" aria-hidden="true"></i> KIA MOTORS<strong> <small>Enviar Documentacion de Pedimentos</small></h3>
				</div>
				<div class="panel-body">
					<div class="row" style="margin-bottom: 5px;">
						<div class="col-xs-12">	
							<div id="idiv_panel_principal_mensaje"></div>
						</div>
					</div>
					<!--div id="idiv_filtro" class="row">
						<div class="col-xs-6 col-md-6">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Filtrar por:</div>
									<select class="form-control" id="isel_buscar">
										<option value="pedimento" selected>Numero de Pedimento (Ejemplo: 800-3483-Pedimento)</option>
										<option value="referencia">Referencia</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-xs-6 col-md-6">
							<div class="form-group">
								<div class="input-group">
									<input id="itxt_busqueda" class="form-control text-uppercase" type="text" maxlength="19">
									<span class="input-group-btn">
										<button id="ibtn_consultar" type="button" class="btn btn-primary" onClick="javascript:fcn_cargar_grid_estatus();">Consultar</button>	
									</span>
								</div>
							</div>
						</div>
						<div class="col-xs-12">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Cliente:</div>
									<input id="itxt_cliente" class="form-control text-uppercase" type="text" disabled="disabled">
								</div>
							</div>
						</div>
					</div-->
					<div id="idiv_tabla" class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dtkia" class="table table-striped table-bordered" width="100%">
										<thead>
											<tr>
												
												<th class="def_app_center">REFERENCIA</th>
												<th class="def_app_center">OPERACION</th>
												<th class="def_app_center">ADUANA</th>
												<th clss="def_app_center">PATENTE</th>
												<th class="def_app_center">PEDIMENTO</th>
												<th class="def_app_center">CLAVE</th>
												<th class="def_app_center">FECHA_ENTRADA</th>
												<th class="def_app_center">FECHA_PAGO</th>
												<th class="def_app_center">ACCIONES</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div id="idiv_detalle_return" class="row" style="display:none;">
						<div class="col-xs-12">
							<div class="form-group">
								<button type="button" class="btn btn-primary btn-xs" onclick="fcn_detalles_regresar();">
									<span class="glyphicon glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Regresar
								</button>
							</div>							
						</div>
					</div>					
					<div id="idiv_detalle" class="row" style="display:none;">
					</div>
				</div>
			</div>
		</div>
    </div>

	<script src="../bower_components/json3/lib/json3.min.js"></script>

	<!--[if lt IE 9]>
		<script src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js' type='text/javascript'/>
	<![endif]-->

	<!--[if (gte IE 9) | (!IE)]><!-->
		<script src="../bower_components/jquery/dist/jquery.min.js"></script>
	<!--<![endif]-->

    <!-- Bootstrap Core JavaScript -->
    <script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="../bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
	<script src="../bower_components/datatables.net-responsive/js/dataTables.responsive.js"></script>
	<script src="../bower_components/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
	<script src="../bower_components/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../bower_components/datatables.net-buttons/js/buttons.colVis.min.js"></script>
    <script src="../bower_components/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="../bower_components/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script src="../bower_components/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
	<script src="../bower_components/datatables.net-select/js/dataTables.select.min.js"></script>
	<script src="../bower_components/datatables.net-checkboxes/js/dataTables.checkboxes.min.js"></script>
	<script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	
    <script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]-->
	<!-- Fileinput JS -->
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/fileinput.min.js"></script>
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/locales/es.js"></script>
	
	<script src="../js/docs_kia.js?2017.11.07.1221"></script>
</body>

</html>
