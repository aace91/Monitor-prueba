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

    <title>Circulares</title>

    <!-- Bootstrap Core CSS -->
	<link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	 
	<!-- Custom Fonts -->
	<link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

	<!-- DataTables CSS -->
    <link href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-fixedcolumns-bs/css/fixedColumns.bootstrap.min.css" rel="stylesheet">
	<!--link href="../editor/css/editor.bootstrap.min.css" rel="stylesheet">
	<link href="../editor/css/editor.selectize.css" rel="stylesheet"-->	
	<!--link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet"/-->
	
	<!-- Custom styles for this template -->
	<link href="../bootstrap/css/navbar.css" rel="stylesheet">

	<!-- FileInput -->
	<link href="../bower_components/bootstrap-fileinput-4.2.3/css/fileinput.min.css" rel="stylesheet"/>

    <!-- Bootstrap summernote -->
	<link href="../bower_components/summernote/dist/summernote.css" rel="stylesheet"/>
	
	<!-- Switch -->
	<link href="../bower_components/switch/css/style_switch.css" rel="stylesheet" type="text/css">
	
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

	<style>
		a:hover { cursor:pointer; }

		div.note-editable p { font-size: 14px !important; }
		ul.dropdown-style li a p { font-size: 14px !important; }
	</style>
</head>

<body>
    <div class="container">

        <?php require('nav.php'); ?>
		
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
						<button type="button" class="btn btn-info" data-dismiss="modal">OK</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MESSAGE BOX ERROR-->
		<div id="modalmessagebox_error" class="modal fade modal-danger" style="z-index:9999;">
			<div class="modal-dialog modal-lg">
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
		
		<!-- MODAL AGREGAR PERMISO -->
		<div id="modal_circular" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-file-o" aria-hidden="true"></i> Nuevo Circular</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Asunto</span>
										<input id="itxt_mdl_circular_asunto" class="form-control" type="text" maxlength="255">
									</div>
								</div>
							</div>
							<div class="col-xs-12">
								<div class="form-group" style="margin-bottom: 0px;">
									<div class="input-group">
										<div class="input-group-addon">Tipo</div>
										<select class="form-control" id="isel_mdl_tipo_circular">
											<option value="interno" selected>Interno</option>
											<option value="externo">Externo</option>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group pull-right">	
										<button id="ibtn_mdl_guardar_circular" type="button" class="btn btn-success" onClick="javascript:ajax_set_circular();"><i class="fa fa-plus-circle" aria-hidden="true"></i> Crear</button>
									</div>	
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Envio de Circulares</strong>			
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-12">
							<div id="idiv_bwsr_mensaje"></div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dt_circulares" class="table table-striped table-bordered" width="100%">
										<thead>
											<tr>
												<th style="width:120px;">Fecha</th>
												<th>Asunto</th>
												<th style="width:40px;">Tipo</th>
												<th style="width:100px;"></th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>Fecha</th>
												<th>Asunto</th>
												<th>Tipo</th>
												<th></th>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div id="idiv_panel_secundario" class="panel panel-default" style="margin-bottom: 0px; display:none;">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div class="form-group" style="margin-bottom: 0px; padding-top: 8px;">
								<strong> Envio de Circulares</strong>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class="form-group" style="margin-bottom: 0px;">
								<div class="input-group">
									<div class="input-group-addon">Tipo</div>
									<select class="form-control" id="isel_tipo_circular">
										<option value="interno" selected>Interno</option>
										<option value="externo">Externo</option>
									</select>
								</div>
							</div>
						</div>
					</div>					
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-12">
							<div id="idiv_mensaje"></div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12 col-sm-6">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon">Email Remitente</span>
									<input id="itxt_circular_sender" class="form-control" type="text" maxlength="50">
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-6">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon">Nombre Remitente</span>
									<input id="itxt_circular_fromname" class="form-control" type="text" maxlength="75">
								</div>
							</div>
						</div>
						<div class="col-lg-12">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Asunto</div>
									<input class="form-control" id="itxt_asunto_circular" maxlength="255"/>
								</div>
							</div>
						</div>
					</div>
					<!-- /.row -->

					<div class="row">
						<div class="col-lg-12">
							<div class="panel panel-default">
								<div class="panel-heading">Adjuntos</div>
								<!-- /.panel-heading -->
								<div class="panel-body">
									<div class="row">
										<div class="col-xs-12">
											<input id="ifile_documentos" name="ifile_documentos[]" type="file" class="file-loading" multiple/>
										</div>
									</div>
								</div>
								<!-- /.panel-body -->
							</div>
							<!-- /.panel -->
						</div>
						<!-- /.col-lg-12 -->
						
						<div class="col-lg-12">
							<div class="panel panel-default" style="margin-bottom: 0px;">
								<div class="panel-heading">
									Correo Personalizado
								</div>
								<!-- /.panel-heading -->
								<div class="panel-body" style="padding-bottom: 0px;">
									<div class="form-group">
										<label for="comment">Mensaje:</label>
										<div id="idiv_mensaje_html"></div>
									</div>
									
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">Correos Adicionales <small>(Lista de Correos separados por ';')</small></div>
											<input class="form-control" id="itxt_correos_adicionales" placeholder="Lista de Correos separados por ';'" value="">
										</div>
									</div>

									<div class="row">
										<div class="col-xs-12 col-md-6">
											<div class="panel panel-default">
												<div class="panel-heading">Clientes</div>
												<div class="panel-body">
													<div class="row">
														<div class="col-xs-12">
															<div class="form-group">
																<label class="switch switch-green">
																<input id="ickb_enviar_clientes_impo" type="checkbox" class="switch-input">
																<span class="switch-label" data-on="Si" data-off="No"></span>
																<span class="switch-handle"></span>
																</label>&nbsp;&nbsp;<label>Todos los Clientes Importaci&oacute;n</label>
															</div>
														</div>
														<div class="col-xs-12">
															<div class="form-group">
																<label class="switch switch-green">
																<input id="ickb_enviar_clientes_expo" type="checkbox" class="switch-input">
																<span class="switch-label" data-on="Si" data-off="No"></span>
																<span class="switch-handle"></span>
																</label>&nbsp;&nbsp;<label>Todos los Clientes Exportaci&oacute;n</label>
															</div>
														</div>
														<div class="col-xs-12">
															<div class="form-group" style="margin-bottom: 0px;">
																<label class="switch switch-green">
																<input id="ickb_enviar_clientes_nb" type="checkbox" class="switch-input">
																<span class="switch-label" data-on="Si" data-off="No"></span>
																<span class="switch-handle"></span>
																</label>&nbsp;&nbsp;<label>Todos los Clientes NorthBound</label>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-xs-12 col-md-6">
											<div class="panel panel-default">
												<div class="panel-heading">Ejecutivos</div>
												<div class="panel-body">
													<div class="row">
														<div class="col-xs-12">
															<div class="form-group">
																<label class="switch switch-green">
																<input id="ickb_enviar_ejecutivos_impo" type="checkbox" class="switch-input">
																<span class="switch-label" data-on="Si" data-off="No"></span>
																<span class="switch-handle"></span>
																</label>&nbsp;&nbsp;<label>Todos los Ejecutivos Importaci&oacute;n</label>
															</div>
														</div>
														<div class="col-xs-12">
															<div class="form-group">
																<label class="switch switch-green">
																<input id="ickb_enviar_ejecutivos_expo" type="checkbox" class="switch-input">
																<span class="switch-label" data-on="Si" data-off="No"></span>
																<span class="switch-handle"></span>
																</label>&nbsp;&nbsp;<label>Todos los Ejecutivos Exportaci&oacute;n</label>
															</div>
														</div>
														<div class="col-xs-12">
															<div class="form-group" style="margin-bottom: 0px;">
																<label class="switch switch-green">
																<input id="ickb_enviar_ejecutivos_nb" type="checkbox" class="switch-input">
																<span class="switch-label" data-on="Si" data-off="No"></span>
																<span class="switch-handle"></span>
																</label>&nbsp;&nbsp;<label>Todos los Ejecutivos NorthBound</label>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- /.panel-body -->
							</div>
						</div>
						<!-- /.col-lg-12 -->
					</div>
					<!-- /.row -->

					<div class="row">
						<div class="col-lg-12">
							<div id="idiv_mensaje_errors"></div>
						</div>
					</div>
				</div>
					
				<div class="panel-footer">
					<button type="button" class="btn btn-danger" onclick="fcn_regresar_principal();"><i class="fa fa-ban"></i> Salir</button>
					<div class="btn-group pull-right">
						<button class="btn btn-success" type="button" onclick="fcn_guardar_circular();">
							<i class="fa fa-paper-plane"></i> Guardar y Enviar
						</button>
						<button type="button" class="btn btn-default dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
							<li>
								<a onclick="ajax_set_circular();"><i class="fa fa-save"></i> Guardar</a>
							</li>
						</ul>
					</div>

					<!--button type="button" class="btn btn-success pull-right" onclick="fcn_send_emails();"><i class="fa fa-paper-plane"></i> Enviar</button-->
				</div>
			</div>
		</div>
    </div>
    <!-- /#wrapper -->

	<script src="../bower_components/json3/lib/json3.min.js"></script>
	
	<!--script src="../bower_components/wysihtml5/dist/wysihtml5-0.3.0.js"></script-->
	
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
	<script src="../bower_components/datatables.net-fixedcolumns/js/dataTables.fixedColumns.min.js"></script>
	<!--script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	<script src="../editor/js/editor.selectize.js"></script-->

	<script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]-->
	
	<!-- Fileinput JS -->
	<!--script type="text/javascript" language="javascript" src="http://plugins.krajee.com/assets/24b9d388/js/plugins/purify.min.js"></script-->
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/fileinput.min.js"></script>
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/locales/es.js"></script>

	<!-- Bootstrap summernote -->
	<script src="../bower_components/summernote/dist/summernote.min.js"></script>
	<script src="../bower_components/summernote/dist/lang/summernote-es-ES.min.js"></script>


	<script src="../js/circulares.js?v=2018.05.15.1730"></script>
</body>

</html>
