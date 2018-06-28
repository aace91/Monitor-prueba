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

    <title>Expedientes Cuentas por Pagar</title>

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
	
	<!--link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet"/-->
	
    <!-- Custom styles for this template -->
    <link href="../bootstrap/css/navbar.css" rel="stylesheet">
	
	<!-- selectize CSS-->
	<link href="../bower_components/selectize/dist/css/selectize.css" rel="stylesheet">
	<link href="../bower_components/selectize/dist/css/selectize.bootstrap3.css" rel="stylesheet">
	
	<!--style>
		body.modal-open {
			position: fixed;
		}
	</style-->

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	<style>
		#idiv_panel_principal { display:block; }
			
		.def_app_button_default_datatable { color:#000 !important; }
		
		/******************************************************/
		/* Clases para modales */
		/******************************************************/
		.modal-success .modal-header,
		.modal-success .modal-footer {
			border-color: #00733e;
		}
		.modal-danger .modal-header,
		.modal-danger .modal-footer {
			border-color: #c23321;
		}
		
		.modal-primary .modal-body,
		.modal-primary .modal-header,
		.modal-primary .modal-footer,
		.modal-warning .modal-body,
		.modal-warning .modal-header,
		.modal-warning .modal-footer,
		.modal-info .modal-body,
		.modal-info .modal-header,
		.modal-info .modal-footer,
		.modal-success .modal-body,
		.modal-success .modal-header,
		.modal-success .modal-footer,
		.modal-danger .modal-body,
		.modal-danger .modal-header,
		.modal-danger .modal-footer {
			color: #fff !important;
		}
		
		.bg-green,
		.callout.callout-success,
		.alert-success,
		.label-success,
		.modal-success .modal-body {
			background-color: #00a65a !important;
		}
		
		.bg-red,
		.callout.callout-danger,
		.alert-danger,
		.alert-error,
		.label-danger,
		.modal-danger .modal-body {
			background-color: #dd4b39 !important;
		}
		
		.bg-green-active,
		.modal-success .modal-header,
		.modal-success .modal-footer {
			background-color: #008d4c !important;
		}
		
		.bg-red-active,
		.modal-danger .modal-header,
		.modal-danger .modal-footer {
			background-color: #d33724 !important;
		}
		
		/******************************************/
		/* DATATABLES */
		/******************************************/
		.printer table{
			counter-reset: rowNumber;
		}
		 
		.printer tr {
			counter-increment: rowNumber;
		}
		 
		.printer tr td:first-child::before {
			content: counter(rowNumber);
			min-width: 1em;
			margin-right: 0.5em;
		}
	</style>
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php'); ?>
		
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
		<div id="modalmessagebox_ok" class="modal fade modal-success" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span id ="modalmessagebox_ok_titulo"> </span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-3 text-center">
								<i class="fa fa-check fa-3x"></i>
							</div>
							<div class="col-xs-9" style="color:#FFF;">
								<div id="modalmessagebox_ok_mensaje"></div>
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
		<div id="modalmessagebox_error" class="modal fade modal-danger" style="z-index:9999;">
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal">&times;</button>
		                <h4 class="modal-title"><span id ="modalmessagebox_error_span"> </span></h4>
		            </div>
		            <div class="modal-body">
		                <div class="row">
		                    <div class="col-xs-3 text-center">
		                        <i class="fa fa-ban fa-3x"></i>
		                    </div>
		                    <div class="col-xs-9" style="color:#FFF;">
		                        <div id="modalmessagebox_error_mensaje"></div>
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
		<div id="modalconfirm" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span id ="modalconfirm_title"> </span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-2 text-center">
								<i class="fa fa-exclamation-triangle fa-3x"></i>
							</div>
							<div class="col-xs-10">
								<div id="modalconfirm_mensaje"></div>
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
		
		<!-- MODAL VER DETALLES -->
		<div id="modal_cuenta_gastos_detalles" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Detalles</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dtcuenta_gastos_detalles" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="text-center">CUENTA GASTOS</th>
													<th class="text-center">TRAFICO</th>
													<th class="text-center">PEDIMENTO</th>
													<th class="text-center">FECHA FACTURA</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th class="text-center">CUENTA GASTOS</th>
													<th class="text-center">TRAFICO</th>
													<th class="text-center">PEDIMENTO</th>
													<th class="text-center">FECHA FACTURA</th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_modal_cuenta_gastos_detalles_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL ENTREGAR CUENTAS -->
		<div id="modal_entregar" class="modal fade" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Entregar Cuentas</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="dropdown pull-right">
									<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										<i class="fa fa-cog" aria-hidden="true"></i> Historial
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
										<li><a onclick="fcn_mostrar_cookies_grid('aAjaxTraficos1');" href="#"><i class="fa fa-list-ol" aria-hidden="true"></i> Guardado 1</a></li>
										<li><a onclick="fcn_mostrar_cookies_grid('aAjaxTraficos2');" href="#"><i class="fa fa-list-ol" aria-hidden="true"></i> Guardado 2</a></li>
										<li><a onclick="fcn_mostrar_cookies_grid('aAjaxTraficos3');" href="#"><i class="fa fa-list-ol" aria-hidden="true"></i> Guardado 3</a></li>
									</ul>
								</div>						
							</div>
						</div>
						<br>
					
						<div class="row" >
							<div class="col-xs-12 col-md-12 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Trafico:</div>
										<input id="itxt_modal_entregar_trafico" class="form-control" type="text" maxlength="200">
										<span class="input-group-btn">
											<button id="ibtn_modal_entregar_agregar" type="button" class="btn btn-info" onclick="fcn_modal_entregar_agregar_trafico();"><i class="fa fa-plus"></i> Agregar</button>
										</span>
									</div>									
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dttraficos" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="def_app_center">TRAFICO</th>
													<th class="def_app_center" style="width:60px;"></th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_entregar_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_modal_entregar_aceptar" type="button" class="btn btn-success" onClick="javascript:ajax_set_upd_entregar_cuentas();"><i class="fa fa-check-circle"></i> Entregar Cuentas</button>	
						<button id="ibtn_modal_entregar_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Cuentas por Pagar</strong> <small></small>
				</div>
				<div class="panel-body">
					<!--<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<select class="selectpicker form-control" data-live-search="true" style="display:none;">
									<option>Hot Dog, Fries and a Soda</option>
									<option>Burger, Shake and a Smile</option>
									<option>Sugar, Spice and all things nice</option>
									<option>ajjajaja Spice and all things nice</option>
								</select>
							</div>							
						</div>
					</div>-->
					<ul class="nav nav-tabs">
						<li id="ili_pendientes" class="active"><a href="#idiv_pendientes" data-toggle="tab">Pendientes Por Digitalizar</a></li>
						<!--li id="ili_digitalizados"><a href="#idiv_digitalizados" data-toggle="tab">Digitalizados</a></li-->
						<li id="ili_pendientes_ent"><a href="#idiv_pendientes_ent" data-toggle="tab">Pendientes Por Entregar</a></li>
						<li id="ili_entregados"><a href="#idiv_entregados" data-toggle="tab">Entregados</a></li>
					</ul>
					
					<div class="tab-content clearfix">
						<div class="tab-pane active" id="idiv_pendientes">
							<br>
							<div class="row">
								<div class="col-xs-12">
									<div class="dataTable_wrapper">
										<div class="table-responsive" style="overflow:hidden;">
											<table id="dtpendientes" class="table table-striped table-bordered" width="100%">
												<thead>
													<tr>
														<th class="text-center">CUENTA GASTOS</th>
														<th class="text-center">TRAFICO</th>
														<th class="text-center">FACTURA</th>
														<th class="text-center">DETALLES</th>
													</tr>
												</thead>
												<tfoot>
													<tr>
														<th class="text-center">CUENTA GASTOS</th>
														<th class="text-center">TRAFICO</th>
														<th class="text-center">FACTURA</th>
														<th class="text-center">DETALLES</th>
													</tr>
												</tfoot>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<!--div class="tab-pane" id="idiv_digitalizados">
							<br>
							<div class="row">
								<div class="col-xs-12">
									<div class="dataTable_wrapper">
										<div class="table-responsive" style="overflow:hidden;">
											<table id="dtdigitalizados" class="table table-striped table-bordered" width="100%">
												<thead>
													<tr>
														<th class="text-center">CUENTA GASTOS</th>
														<th class="text-center">TRAFICO</th>
														<th class="text-center">FACTURA</th>
														<th class="text-center">DETALLES</th>
													</tr>
												</thead>
												<tfoot>
													<tr>
														<th class="text-center">CUENTA GASTOS</th>
														<th class="text-center">TRAFICO</th>
														<th class="text-center">FACTURA</th>
														<th class="text-center">DETALLES</th>
													</tr>
												</tfoot>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div-->
						
						<div class="tab-pane" id="idiv_pendientes_ent">
							<br>
							<div class="row">
								<div class="col-xs-12">
									<div class="dataTable_wrapper">
										<div class="table-responsive" style="overflow:hidden;">
											<table id="dtpendientes_ent" class="table table-striped table-bordered" width="100%">
												<thead>
													<tr>
														<th class="text-center">CUENTA GASTOS</th>
														<th class="text-center">TRAFICO</th>
														<th class="text-center">FACTURA</th>
														<th class="text-center">DETALLES</th>
													</tr>
												</thead>
												<tfoot>
													<tr>
														<th class="text-center">CUENTA GASTOS</th>
														<th class="text-center">TRAFICO</th>
														<th class="text-center">FACTURA</th>
														<th class="text-center">DETALLES</th>
													</tr>
												</tfoot>
											</table>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-12 col-md-12">
									<div class="form-group">
										<div>
											<button id="ibtn_pendientes_ent_entregar" type="button" class="btn btn-info" onclick="javascript:fcn_mostrar_modal_entregar_cuentas();"><i class="fa fa-check-circle" aria-hidden="true"></i> Entregar Cuentas</button>					
										</div>								
									</div>
								</div>
							</div>
						</div>
						
						<div class="tab-pane" id="idiv_entregados">
							<br>
							<div class="row">
								<div class="col-xs-12 col-md-12">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">Fecha Entrega:</div>
											<select class="form-control" id="isel_entregados_fechas" onChange="fcn_cargar_grid_entregados(true);"></select>
										</div>							
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-12">
									<div class="dataTable_wrapper">
										<div class="table-responsive" style="overflow:hidden;">
											<table id="dtentregados" class="table table-striped table-bordered" width="100%">
												<thead>
													<tr>
														<th class="text-center" width="30px"></th>
														<th class="text-center">CUENTA GASTOS</th>
														<th class="text-center">TRAFICO</th>
														<th class="text-center">FECHA</th>
														<th class="text-center">FACTURA</th>
														<th class="text-center">DETALLES</th>
													</tr>
												</thead>
												<tfoot>
													<tr>
														<th class="text-center" width="30px"></th>
														<th class="text-center">CUENTA GASTOS</th>
														<th class="text-center">TRAFICO</th>
														<th class="text-center">FECHA</th>
														<th class="text-center">FACTURA</th>
														<th class="text-center">DETALLES</th>
													</tr>
												</tfoot>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<button id="ibtn_sync" type="button" class="btn btn-default" onClick="javascript:ajax_set_upd_aplicar_digitalizacion('button');"><i class="fa fa-refresh" aria-hidden="true"></i> Sincronizar Documentos</button>
							&nbsp;<span id="ispan_sync_digitalizacion_message"></span>
						</div>
						<div class="col-xs-12 col-md-6">
							<span id="ispan_sync_message" class="pull-right" style="padding: 7px;"></span>
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
	<script src="../bower_components/datatables.net-checkboxes/js/dataTables.checkboxes.min.js"></script>
	<script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	<script src="../editor/js/editor.selectize.js"></script>
	
    <script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]-->
	
	<!-- selectize JavaScript -->
	<script src="../bower_components/selectize/dist/js/standalone/selectize.js"></script>
	
	<!-- boostrapselect JavaScript -->
	<!--<script src="../bower_components/bootstrap-select/js/bootstrap-select.js"></script>-->
		
	<script src="../js/expCxP.js?v=2017.12.01.1240"></script>
</body>

</html>
