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
	
    <title>Control de Glosa</title>

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
	<link href="../editor/css/editor.selectize.css" rel="stylesheet">
	
	<!-- Custom styles for this template -->
    <link href="../bootstrap/css/navbar.css" rel="stylesheet">
	
	<!-- Select2 CSS-->
	<link href="../bower_components/select2/dist/css/select2.min.css" rel="stylesheet">
	<link href="../bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css" rel="stylesheet">

	<!-- Bootstrap Datepicker -->
	<link href="../bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">

	<!-- CSS Propios - TimeLine -->
	<link href="timeline.css" rel="stylesheet"/>

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	<style>
		/*#idiv_panel_principal { display:none; }*/
		
		a:hover {
			cursor:pointer;
		}

		/***************************************/
		/*CLASES EXTRA PARA SCROLLBAR*/
		/***************************************/
		#style-4::-webkit-scrollbar-track
		{
			-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
			background-color: #F5F5F5;
		}

		#style-4::-webkit-scrollbar
		{
			width: 8px;
			background-color: #F5F5F5;
		}

		#style-4::-webkit-scrollbar-thumb
		{
			background-color: #000000;
			border: 2px solid #555555;
		}
	</style>
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php');?>		
		<input type="hidden" id="itxt_email_usuario" value="<?php global $usuemail; echo $usuemail ?>"/>

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
	
		<!-- MODAL CATALOGO DE PROBLEMAS -->
		<div id="modal_cat_problemas" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-exclamation" aria-hidden="true"></i> Cat&aacute;logo de Problemas</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div id="id_prueba" class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Problema</div>
										<input id="itxt_cat_problemas_problema" type="text" maxlength="100" class="form-control text-uppercase">
										<span id="ibtn_cat_problemas_add" class="input-group-btn">
											<button class="btn btn-success" type="button" onClick="fcn_cat_problemas_add();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;"><i class="fa fa-plus" aria-hidden="true"></i></button>
										</span>
										<span id="ibtn_cat_problemas_save" class="input-group-btn">
											<button class="btn btn-success" type="button" onClick="fcn_cat_problemas_save();" style="border-radius: 0px !important;"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
										</span>

										<span id="ibtn_cat_problemas_cancel" class="input-group-btn">
											<button class="btn btn-danger" type="button" onClick="fcn_cat_problemas_cancel();"><i class="fa fa-ban" aria-hidden="true"></i></button>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dtcat_problemas" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="text-center">PROBLEMA</th>
													<th class="text-center" style="width: 120px;">FECHA</th>
													<th class="text-center">EDITAR</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th class="text-center">PROBLEMA</th>
													<th class="text-center" style="width: 120px;">FECHA</th>
													<th class="text-center">EDITAR</th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--div class="modal-footer">
						<button id="ibtn_modal_cuenta_gastos_detalles_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>	
					</div-->
				</div>
			</div>
		</div>
		
		<!-- MODAL REPORTES -->
		<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-file-text" aria-hidden="true"></i> Reportes</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Filtro</div>
										<select id="isel_reportes_filtro" class="form-control" onchange="fcn_reportes_sel_filtro();">
											<option value="fecha" selected="true">Por Fecha</option>
											<option value="grafica">Grafica</option>
										</select>
										<!--div class="input-group-btn">
											<button class="btn btn-info" type="button" onClick="fcn_cargar_grid_referencias();"><i class="fa fa-search" aria-hidden="true"></i></button>
										</div-->
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<div id="idtp_reportes_fecha" class="input-daterange input-group">
										<span class="input-group-addon">Fecha</span>
									    <input type="text" class="form-control" name="start" />
									    <span class="input-group-addon">a</span>
									    <input type="text" class="form-control" name="end" />
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-12 col-sm-5">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Tipo</div>
										<select id="isel_reportes_tipo" class="form-control" onchange="fcn_reportes_sel_tipo();">
											<option value="general" selected="true">General</option>
											<option value="ejecutivo">Ejecutivo</option>
											<option value="cliente">Cliente</option>
											<option value="regimen">Regimen</option>
											<option value="impo_expo">Operaci&oacute;n</option>
											<option value="problema">Problema</option>
										</select>
									</div>
								</div>
							</div>

							<div class="col-xs-12 col-sm-5">
								<div class="form-group">
									<div class="input-group">
										<div id="idiv_reportes_tipo_opt2" class="input-group-addon">Tipo</div>
										<select id="isel_reportes_tipo_opt2" class="form-control"></select>
									</div>
								</div>
							</div>

							<div class="col-xs-12 col-sm-2">
								<div class="form-group pull-right">
									<div class="input-group ">
										<button type="button" class="btn btn-info pull-right" onClick="fcn_reportes_generar();"><i class="fa fa-cogs"></i> Generar</button>		
									</div>
								</div>
							</div>
						</div>

						<div class="row" id="idiv_reportes_dtreportes">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dtreportes" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="text-center" style="width: 120px;">FECHA</th>
													<th class="text-center" style="width: 200px;">EJECUTIVO</th>
													<th class="text-center" style="width: 60px;">T. OP.</th>
													<th class="text-center">ADUANA</th>
													<th class="text-center">PEDIMENTO</th>
													<th class="text-center" style="width: 250px;">CLIENTE</th>
													<th class="text-center" style="width: 200px;">PROVEEDOR</th>
													<th class="text-center" style="width: 250px;">ERROR</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th class="text-center" style="width: 120px;">FECHA</th>
													<th class="text-center" style="width: 200px;">EJECUTIVO</th>
													<th class="text-center" style="width: 60px;">T. OP.</th>
													<th class="text-center">ADUANA</th>
													<th class="text-center">PEDIMENTO</th>
													<th class="text-center" style="width: 250px;">CLIENTE</th>
													<th class="text-center" style="width: 200px;">PROVEEDOR</th>
													<th class="text-center" style="width: 250px;">ERROR</th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--div class="modal-footer">
						<button id="ibtn_modal_cuenta_gastos_detalles_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>	
					</div-->
				</div>
			</div>
		</div>

		<!-- MODAL REASIGNAR REFERENCIA -->
		<div id="modal_reasignar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-exchange" aria-hidden="true"></i> Reasignar Referencia</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia Anterior</div>
										<input id="itxt_reasignar_ref_anterior" type="text" class="form-control" disabled="disabled"/>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Nueva Referencia</div>
										<input id="itxt_reasignar_ref_nueva" type="text" class="form-control" />
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer" id="idiv_reasignar_ref_footer">
						<button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Cancelar</button>
						<button type="button" class="btn btn-success pull-right" onClick="ajax_set_reasignar_ref();"><i class="fa fa-floppy-o"></i> Guardar</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL BUSCAR REFERENCIA -->
		<div id="modal_add_referencia" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Agregar Referencia</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia</div>
										<input id="itxt_add_referencia_referencia" type="text" class="form-control" />
										<!--select id="isel_add_referencia_filtro" class="form-control">
											<option value="referencia" selected="true">Referencia</option>
										</select-->
										<span class="input-group-btn">
									    	<button class="btn btn-info" type="button" onClick="fcn_cargar_grid_add_referencia();"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
									    </span>
									</div>
								</div>
							</div>
						</div>

						<div class="row" id="idiv_reportes_dtreportes">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dt_add_referencia" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="text-center" style="width: 80px;">REFERENCIA</th>
													<th class="text-center" style="width: 100px;">FECHA</th>
													<th class="text-center" style="width: 130px;">EJECUTIVO</th>
													<th class="text-center">CLIENTE</th>
													<th class="text-center" style="width: 70px;">ESTATUS</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th class="text-center" style="width: 80px;">REFERENCIA</th>
													<th class="text-center" style="width: 100px;">FECHA</th>
													<th class="text-center" style="width: 130px;">EJECUTIVO</th>
													<th class="text-center">CLIENTE</th>
													<th class="text-center" style="width: 70px;">ESTATUS</th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--div class="modal-footer">
						<button id="ibtn_modal_cuenta_gastos_detalles_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>	
					</div-->
				</div>
			</div>
		</div>

		<!-- MODAL AGREGAR PROBLEMAS -->
		<div id="modal_add_problema" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-exclamation" aria-hidden="true"></i> Capturar Problema</h4>
					</div>
					<div class="modal-body">
						<div id="style-4" class="row" style="height: 200px; overflow-y: scroll; margin-bottom: 10px;">
							<div id="idiv_timeline" class="col-xs-12">
								<!--ul class="timeline">	
									<li>
										<div class="timeline-badge">
										  <a><i class="fa fa-circle" id=""></i></a>
										</div>
										<div class="timeline-panel">
											<div class="timeline-heading">
												<h4>Timeline Event</h4>
											</div>
											<div class="timeline-body">
												<p>Invitamus me testatur sed quod non dum animae tuae lacrimis ut libertatem deum rogus aegritudinis causet. Dicens hoc contra serpentibus isto.</p>
											</div>
											<div class="timeline-footer">
												<p class="text-right">Feb-21-2014</p>
											</div>
										</div>
									</li>
									<li class="timeline-inverted">
										<div class="timeline-badge">
											<a><i class="fa fa-circle invert" id=""></i></a>
										</div>
										<div class="timeline-panel">
											<div class="timeline-heading">
												<h4>Timeline Event</h4>
											</div>
											<div class="timeline-body">
												<p>Stranguillione in deinde cepit roseo commendavit patris super color est se sed. Virginis plus plorantes abscederem assignato ipsum ait regem Ardalio nos filiae Hellenicus mihi cum. Theophilo litore in lucem in modo invenit quasi nomen magni ergo est se est Apollonius, habet clementiae venit ad nomine sed dominum depressit filia navem.</p>
											</div>
											<div class="timeline-footer">
												<p class="text-right">Feb-23-2014</p>
											</div>
										</div>
									</li>
									<li class="clearfix no-float"></li>
								</ul-->
							</div>
						</div>

						<div class="row" id="idiv_add_problema_ejecutivo">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Ejecutivo</div>
										<select id="isel_add_problema_ejecutivo" class="form-control"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row" id="idiv_add_problema_lista">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Problema</div>
										<select id="isel_add_problema_lista" class="form-control"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row" id="idiv_add_problema_observacion">
							<div class="col-xs-12">
								<div class="input-group">
									<div class="input-group-addon">Obsercaciones</div>
									<textarea id="itxt_add_problema_observacion" cols="30" rows="2" class="form-control text-uppercase" style="resize: none;"></textarea>
								</div>
							</div>
						</div>

						<div class="row">
							<div id="idiv_add_problema_mensaje" class="col-xs-12"></div>
						</div>
					</div>
					<div class="modal-footer" id="idiv_add_problema_footer">
						<!--button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button-->
						<button id="ibtn_add_problema_lita_cancel" type="button" class="btn btn-danger pull-right" onClick="fcn_problemas_cancel();" style="margin-left: 10px;"><i class="fa fa-ban"></i> Cancelar</button>
						<button id="ibtn_add_problema_lita_save" type="button" class="btn btn-success pull-right" onClick="fcn_problemas_save();"><i class="fa fa-floppy-o"></i> Guardar</button>
						<button id="ibtn_add_problema_lita_new" type="button" class="btn btn-success pull-right" onClick="fcn_problemas_add();"><i class="fa fa-plus"></i> Nuevo</button>
					</div>
				</div>
			</div>
		</div>

		<!-- MODAL CERRAR PROBLEMA -->
		<div id="modal_listo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-exclamation" aria-hidden="true"></i> Capturar Problema</h4>
					</div>
					<div class="modal-body">
						<div class="row" id="idiv_listo_ejecutivo">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Ejecutivo</div>
										<select id="isel_listo_ejecutivo" class="form-control"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div id="idiv_listo_mensaje" class="col-xs-12"></div>
						</div>
					</div>
					<div class="modal-footer" id="idiv_listo_footer">
						<button id="ibtn_listo_cerrar" type="button" class="btn btn-success pull-right" onClick="fcn_listo_cerrar();"><i class="fa fa-floppy-o"></i> Guardar</button>
						<!--button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button-->
						<!--button id="ibtn_add_problema_lita_cancel" type="button" class="btn btn-danger pull-right" onClick="fcn_problemas_cancel();" style="margin-left: 10px;"><i class="fa fa-ban"></i> Cancelar</button>
						<button id="ibtn_add_problema_lita_save" type="button" class="btn btn-success pull-right" onClick="fcn_problemas_save();"><i class="fa fa-floppy-o"></i> Guardar</button>
						<button id="ibtn_add_problema_lita_new" type="button" class="btn btn-success pull-right" onClick="fcn_problemas_add();"><i class="fa fa-plus"></i> Nuevo</button-->
					</div>
				</div>
			</div>
		</div>
		
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Control de Glosa</strong> <small></small>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-6">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Filtro</div>
									<select id="isel_filtro_referencias" class="form-control" onchange="fcn_filtro_referencias();">
										<option value="pendientes" selected="true">Pendientes</option>
										<option value="todo">Todo</option>
										<option value="listos">Cerrados</option>
									</select>
								</div>
							</div>							
						</div>
						<div class="col-xs-12 col-sm-6">
							<div class="form-group pull-right">
								<div class="dropdown pull-right">
									<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										<i class="fa fa-cog" aria-hidden="true"></i> Opciones
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
										<li><a onclick="fcn_cat_problemas_show();" href="#"><i class="fa fa-exclamation" aria-hidden="true"></i>  Cat&aacute;logo de Problemas</a></li>
										<li><a onclick="fcn_reportes_show();" href="#"><i class="fa fa-file-text" aria-hidden="true"></i> Reportes</a></li>
									</ul>
								</div>	
							</div>												
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dtreferencias" class="table table-striped table-bordered" width="100%">
										<thead>
											<tr>
												<th class="text-center" style="width: 80px;">REFERENCIA</th>
												<th class="text-center" style="width: 100px;">FECHA</th>
												<th class="text-center" style="width: 130px;">EJECUTIVO</th>
												<th class="text-center">CLIENTE</th>
												<th class="text-center" style="width: 80px;">PROBLEMA</th>
												<th class="text-center">CERRADO</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th class="text-center" style="width: 80px;">REFERENCIA</th>
												<th class="text-center" style="width: 100px;">FECHA</th>
												<th class="text-center" style="width: 130px;">EJECUTIVO</th>
												<th class="text-center">CLIENTE</th>
												<th class="text-center" style="width: 80px;">PROBLEMA</th>
												<th class="text-center">CERRADO</th>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
    </div> <!-- /container -->

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
	<script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	<script src="../editor/js/editor.selectize.js"></script>
	
    <script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]-->
		
	<!-- Select2 JavaScript -->
	<script src="../bower_components/select2/dist/js/select2.min.js"></script>
	<script src="../bower_components/select2/dist/js/i18n/es.js"></script>

	<!-- Bootstrap Datepicker -->
	<script src="../bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
	<script src="../bower_components/bootstrap-datepicker/dist/locales/bootstrap-datepicker.es.min.js"></script>

	<script src="../js/ctrlGlosa.js?v=2017.11.29.2000"></script>
</body>

</html>