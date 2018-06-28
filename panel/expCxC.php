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
	
    <title>Expedientes Cuentas por Cobrar</title>

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
		/*#idiv_panel_principal { display:none; }*/
		
		.def_app_right{
		  text-align: right;
		}

		.def_app_left{
		  text-align: left;
		}

		.def_app_center{
		  text-align: center !important;
		}
		
		.def_app_button_default_datatable { color:#000 !important; }
		
		/******************************************************/
		/* Clases para modales */
		/******************************************************/
		.modal { overflow: auto !important; }
			
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
		
		.uppercase
		{
			text-transform:uppercase;
		}
	</style>
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php');?>
		
		<!-- MODAL LOAD CONFIGURACION -->
		<div id="modalloadconfig" class="modal in" data-backdrop="static" style="z-index:9999;">
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
		<div id="modalconfirm" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<!--button type="button" class="close" data-dismiss="modal">&times;</button-->
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
		
		<!-- MODAL SELECCIONAR EMPRESA -->
		<div id="modal_select_empresa" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<!--button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button-->
						<h4 class="modal-title">Seleccionar Empresa Para Trabajar</h4>
					</div>
					<div class="modal-body">								
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dtempresas" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="def_app_center">EMPRESA NOMBRE</th>
													<th class="def_app_center"></th>
												</tr>
											</thead>											
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL CAPTURAR PEDIMENTOS -LINK CLICK- -->
		<div id="modal_edit_comentario_click" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Editar Comentario</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12 col-md-12 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Comentarios:</div>
										<textarea id="itxt_modal_edit_comentario_click_comentarios" class="form-control uppercase" rows="5" style="resize: none;"></textarea>
										<!--<input id="itxt_modal_edit_comentario_click_comentarios" class="form-control" type="text" onKeyUp="toUpper(this)" maxlength="1000">-->
									</div>
								</div>					
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_edit_comentario_click_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_modal_edit_comentario_click_agregar" type="button" class="btn btn-success" onClick="javascript:ajax_set_upd_comentario();"><i class="fa fa-floppy-o"></i> Guardar Comentario</button>	
						<button id="ibtn_modal_edit_comentario_click_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL RECIBIR PEDIMENTOS -->
		<div id="modal_recibir_pedimentos" class="modal in" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Recibir Pedimentos</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12 col-md-12 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">C&oacute;digo de Barras:</div>
										<textarea class="form-control" style="resize:none; overflow:hidden;" id="itxt_modal_recive_pedimento" rows="1"></textarea>
									</div>									
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dtrec_ped_pendientes" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="def_app_center">RECIBIR</th>
													<th class="def_app_center">REFERENCIA</th>
													<th class="def_app_center">PEDIMENTO</th>
													<th class="def_app_center">COMENTARIOS</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th class="def_app_center">RECIBIR</th>
													<th class="def_app_center">REFERENCIA</th>
													<th class="def_app_center">PEDIMENTO</th>
													<th class="def_app_center">COMENTARIOS</th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_recibir_pedime_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<!--<button id="ibtn_modal_rec_pedime_aceptar" type="button" class="btn btn-success" onClick="javascript:ajax_set_upd_recibir_pedimento();"><i class="fa fa-check-circle"></i> Recibir Pedimentos</button>-->
						<button id="ibtn_modal_rec_pedime_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL FACTURAR -->
		<div id="modal_facturar" class="modal fade" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Asignar cuenta de gastos</h4>
					</div>
					<div class="modal-body">	
						<ul class="nav nav-tabs">
							<li id="ili_modal_facturar_uuid" class="active"><a href="#idiv_modal_facturar_uuid" data-toggle="tab">Folio Fiscal</a></li>
							<li id="ili_modal_facturar_cuenta"><a href="#idiv_modal_facturar_cuenta" data-toggle="tab">Cuenta de Gastos</a></li>
						</ul>
						
						<div class="tab-content clearfix">
							<div class="tab-pane active" id="idiv_modal_facturar_uuid">
								<br>
								<div class="row" >
									<div class="col-xs-12 col-md-12 text-left">
										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Folio Fiscal:</div>
												<!--<textarea class="form-control" style="resize:none; overflow:hidden;" id="itxt_modal_facturar_uuid_uuid" rows="1"></textarea>-->
												<input id="itxt_modal_facturar_uuid_uuid" class="form-control" type="text" maxlength="200">
												<span class="input-group-btn">
													<button id="ibtn_modal_facturar_cuenta_agregar" type="button" class="btn btn-info" onclick="fcn_modal_facturar_agregar_folio_fiscal();"><i class="fa fa-plus"></i> Agregar Folio</button>
												</span>
											</div>									
										</div>
									</div>
								</div>
							</div>
							
							<div class="tab-pane" id="idiv_modal_facturar_cuenta">
								<br>
								<div class="row">
									<div class="col-xs-6 col-md-4 text-left">
										<div class="form-group">
											<label>Tipo Movimiento</label>
											<select id="isel_modal_facturar_cuenta_tipo_mov" class="form-control">
												<option value="I" selected>Factura</option>
												<option value="R">Remisi&oacute;n</option>
											</select>								
										</div>
									</div>
									<div class="col-xs-6 col-md-2 text-left">
										<div class="form-group">
											<label>Banco</label>
											<input id="itxt_modal_facturar_cuenta_banco" class="form-control" style="text-align:center;" type="text" maxlength="2">								
										</div>
									</div>
									<div class="col-xs-6 col-md-4 text-left">
										<div class="form-group">
											<label>Movimiento</label>
											<input id="itxt_modal_facturar_cuenta_no_mov" class="form-control" style="text-align:center;" type="text" maxlength="8">							
										</div>
									</div>
									<div class="col-xs-6 col-md-2 text-left">
										<div class="form-group">
											<label>&nbsp;</label></br>
											<button id="ibtn_modal_facturar_cuenta_agregar" type="button" class="btn btn-info" data-toggle="tooltip" data-placement="top" title="Agregar Cuenta" onclick="fcn_modal_facturar_agregar_cuenta('cuenta_gastos');"><i class="fa fa-plus"></i></button>							
										</div>
									</div>
								</div>
								<!--
								<div class="row">
									<div class="col-xs-12 col-md-12 text-left">
										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Cuenta Gastos:</div>
												<input id="itxt_modal_facturar_cuenta_cuenta" class="form-control" type="text" maxlength="60">
												<span class="input-group-btn">
													<button id="ibtn_modal_facturar_cuenta_agregar2" type="button" class="btn btn-info" onclick="fcn_modal_facturar_agregar_cuenta('cuenta_gastos');"><i class="fa fa-plus"></i> Agregar Cuenta</button>
												</span>
											</div>									
										</div>
									</div>
								</div>-->
							</div>							
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dtfacturar" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="def_app_center">FOLIO FISCAL/CUENTA DE GASTOS</th>
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
								<div id="idiv_modal_facturar_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_modal_facturar_aceptar" type="button" class="btn btn-success" onClick="javascript:fcn_modal_facturar();"><i class="fa fa-check-circle"></i> Asignar cuenta de gastos</button>	
						<button id="ibtn_modal_facturar_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL ENTREGAR CUENTAS DE GASTOS -->
		<div id="modal_entregar_cuenta_gastos" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Entregar Cuentas</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dtentregar_cuenta_gastos" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="def_app_center" style="width:15px;"></th>
													<th class="def_app_center">CUENTA GASTOS</th>
													<th class="def_app_center">TRAFICO</th>
													<th class="def_app_center">DETALLES</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th class="def_app_center" style="width:15px;"></th>
													<th class="def_app_center">CUENTA GASTOS</th>
													<th class="def_app_center">TRAFICO</th>
													<th class="def_app_center">DETALLES</th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div id="idiv_entregar_cuenta_gastos_mensaje" style="display:none;"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_entregar_cuenta_gastos_entregar" type="button" class="btn btn-success" onClick="javascript:fcn_entregar_cuenta_gastos_elementos_seleccionados();"><i class="fa fa-check-circle"></i> Entregar Cuentas</button>	
						<button id="ibtn_entregar_cuenta_gastos_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
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
													<th class="def_app_center">CUENTA GASTOS</th>
													<th class="def_app_center">TRAFICO</th>
													<th class="def_app_center">PEDIMENTO</th>
													<th class="def_app_center">FECHA FACTURA</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th class="def_app_center">CUENTA GASTOS</th>
													<th class="def_app_center">TRAFICO</th>
													<th class="def_app_center">PEDIMENTO</th>
													<th class="def_app_center">FECHA FACTURA</th>
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
		
		<!-- MODAL SERVICIOS PRESTADOS -->
		<div id="modal_servicios_prestados" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Nuevo Servicio Prestado</h4>
					</div>
					<div class="modal-body" style="padding-bottom: 0px;">	
						<!--div class="row" >
							<div class="col-xs-12 col-md-12 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Folio Fiscal:</div>
										<input id="itxt_modal_servicios_prestados_uuid_uuid" class="form-control" type="text" maxlength="200">
										<span class="input-group-btn">
											<button id="ibtn_modal_servicios_prestados_agregar" type="button" class="btn btn-info" onclick="fcn_modal_facturar_agregar_folio_fiscal();"><i class="fa fa-plus"></i> Agregar Folio</button>
										</span>
									</div>									
								</div>
							</div>
						</div-->

						<div class="row" >
							<div class="col-xs-12 col-sm-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Pedimento:</div>
										<input id="itxt_modal_servicios_prestados_pedimento" class="form-control" type="text" maxlength="30" />
									</div>									
								</div>
							</div>
							<div class="col-xs-12 col-sm-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Clave Pedimento:</div>
										<input id="itxt_modal_servicios_prestados_cve_ped" class="form-control text-uppercase" type="text" maxlength="2" />
									</div>									
								</div>
							</div>
							<div class="col-xs-12 col-sm-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Operaci&oacute;n:</div>
											<select id="itxt_modal_servicios_prestados_operacion" class="form-control">
											<option value="1" selected>Importaci&oacute;n</option>
											<option value="2">Exportaci&oacute;n</option>
										</select>	
									</div>									
								</div>
							</div>
						</div>

						<div class="row" >
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Comentarios:</div>
										<input id="itxt_modal_servicios_prestados_comentarios" class="form-control text-uppercase" type="text" maxlength="1000" />
									</div>									
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-6 col-md-5 text-left">
								<div class="form-group">
									<label>Tipo Movimiento</label>
									<select id="isel_modal_servicios_prestados_tipo_mov" class="form-control">
										<option value="I" selected>Factura</option>
										<option value="R">Remisi&oacute;n</option>
									</select>								
								</div>
							</div>
							<div class="col-xs-6 col-md-2 text-left">
								<div class="form-group">
									<label>Banco</label>
									<input id="itxt_modal_servicios_prestados_banco" class="form-control" style="text-align:center;" type="text" maxlength="2">								
								</div>
							</div>
							<div class="col-xs-6 col-md-5 text-left">
								<div class="form-group">
									<label>Movimiento</label>
									<input id="itxt_modal_servicios_prestados_no_mov" class="form-control" style="text-align:center;" type="text" maxlength="8">							
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_servicios_prestados_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_servicios_prestados_agregar" type="button" class="btn btn-success" onClick="javascript:ajax_set_servicio_prestado();"><i class="fa fa-check-circle"></i> Agregar</button>	
						<button id="ibtn_servicios_prestados_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>
					</div>
				</div>
			</div>
		</div>

		<!-- MODAL CUENTA ADICIONAL -->
		<div id="modal_adicionales" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Nueva Cuenta Complementaria <small>(adicional)</small></h4>
					</div>
					<div class="modal-body" style="padding-bottom: 0px;">	
						<!--div class="row" >
							<div class="col-xs-12 col-md-12 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Folio Fiscal:</div>
										<input id="itxt_modal_adicionales_uuid_uuid" class="form-control" type="text" maxlength="200">
										<span class="input-group-btn">
											<button id="ibtn_modal_adicionales_agregar" type="button" class="btn btn-info" onclick="fcn_modal_facturar_agregar_folio_fiscal();"><i class="fa fa-plus"></i> Agregar Folio</button>
										</span>
									</div>									
								</div>
							</div>
						</div-->

						<div class="row" >
							<div class="col-xs-12 col-sm-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Aduana:</div>
										<input id="itxt_modal_adicionales_aduana" class="form-control" type="text" maxlength="3" />
									</div>									
								</div>
							</div>
							<div class="col-xs-12 col-sm-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Patente:</div>
										<input id="itxt_modal_adicionales_patente" class="form-control" type="text" maxlength="4" />
									</div>									
								</div>
							</div>
							<div class="col-xs-12 col-sm-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Pedimento:</div>
										<input id="itxt_modal_adicionales_pedimento" class="form-control" type="text" maxlength="30" />
									</div>									
								</div>
							</div>
						</div>

						<div class="row" >
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Comentarios:</div>
										<input id="itxt_modal_adicionales_comentarios" class="form-control text-uppercase" type="text" maxlength="1000" />
									</div>									
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-6 col-md-5 text-left">
								<div class="form-group">
									<label>Tipo Movimiento</label>
									<select id="isel_modal_adicionales_tipo_mov" class="form-control">
										<option value="I" selected>Factura</option>
										<option value="R">Remisi&oacute;n</option>
									</select>								
								</div>
							</div>
							<div class="col-xs-6 col-md-2 text-left">
								<div class="form-group">
									<label>Banco</label>
									<input id="itxt_modal_adicionales_banco" class="form-control" style="text-align:center;" type="text" maxlength="2">								
								</div>
							</div>
							<div class="col-xs-6 col-md-5 text-left">
								<div class="form-group">
									<label>Movimiento</label>
									<input id="itxt_modal_adicionales_no_mov" class="form-control" style="text-align:center;" type="text" maxlength="8">							
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_adicionales_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_adicionales_agregar" type="button" class="btn btn-success" onClick="javascript:ajax_set_adicional();"><i class="fa fa-check-circle"></i> Agregar</button>	
						<button id="ibtn_adicionales_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>
					</div>
				</div>
			</div>
		</div>

		<!-- MODAL CUENTA GASTOS SIN PEDIMENTO -->
		<div id="modal_cuenta_gastos" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Cuenta de Gastos <small>(sin pedimento)</small></h4>
					</div>
					<div class="modal-body" style="padding-bottom: 0px;">	
						<div class="row">
							<div class="col-xs-6 col-md-5 text-left">
								<div class="form-group">
									<label>Tipo Movimiento</label>
									<select id="isel_modal_cuenta_gastos_tipo_mov" class="form-control">
										<option value="I" selected>Factura</option>
										<option value="R">Remisi&oacute;n</option>
									</select>								
								</div>
							</div>
							<div class="col-xs-6 col-md-2 text-left">
								<div class="form-group">
									<label>Banco</label>
									<input id="itxt_modal_cuenta_gastos_banco" class="form-control" style="text-align:center;" type="text" maxlength="2">								
								</div>
							</div>
							<div class="col-xs-6 col-md-5 text-left">
								<div class="form-group">
									<label>Movimiento</label>
									<input id="itxt_modal_cuenta_gastos_no_mov" class="form-control" style="text-align:center;" type="text" maxlength="8">							
								</div>
							</div>
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Comentarios:</div>
										<input id="itxt_modal_cuenta_gastos_comentarios" class="form-control text-uppercase" type="text" maxlength="1000" />
									</div>									
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_cuenta_gastos_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_cuenta_gastos_agregar" type="button" class="btn btn-success" onClick="javascript:ajax_set_cuenta_gastos();"><i class="fa fa-check-circle"></i> Agregar</button>	
						<button id="ibtn_cuenta_gastos_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Cuentas por Cobrar</strong> <small></small>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-xs-8">
							<h5 id="istr_trabajando_empresa" class="text-danger"></h5>
						</div>
						<div class="col-sm-4">
								<div class="dropdown pull-right">
									<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										<i class="fa fa-cog" aria-hidden="true"></i> Opciones
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
										<li><a onclick="fcn_mostrar_modal_servicios_prestados();" href="#"><i class="fa fa-plus" aria-hidden="true"></i> Servicio Prestado <small>(otras patentes)</small></a></li>
										<li><a onclick="fcn_mostrar_modal_adicionales();" href="#"><i class="fa fa-plus" aria-hidden="true"></i> Cuenta Complementaria <small>(adicional)</small></a></li>
										<li><a onclick="fcn_mostrar_modal_cuenta_gastos();" href="#"><i class="fa fa-plus" aria-hidden="true"></i> Cuenta de Gastos <small>(sin pedimento)</small></a></li>
									</ul>
								</div>						
							</div>
					</div>

					<ul class="nav nav-tabs">
						<li id="ili_pendientes" class="active"><a href="#idiv_pendientes" data-toggle="tab">Pendientes Por Facturar</a></li>
						<li id="ili_pend_entregar" style="display:none;"><a href="#idiv_pend_entregar" data-toggle="tab">Pendientes Por Entregar</a></li>
						<li id="ili_entregados"><a href="#idiv_entregados" data-toggle="tab">Facturados</a></li>
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
														<th class="def_app_center">REFERENCIA</th>
														<th class="def_app_center">PEDIMENTO</th>
														<th class="def_app_center">COMENTARIOS</th>
														<!--th class="def_app_center">FECHA RECIBIDO</th-->
													</tr>
												</thead>
												<tfoot>
													<tr>
														<th class="def_app_center">REFERENCIA</th>
														<th class="def_app_center">PEDIMENTO</th>
														<th class="def_app_center">COMENTARIOS</th>
														<!--th class="def_app_center">FECHA RECIBIDO</th-->
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
										<!--button id="ibtn_recibir_pedime" type="button" class="btn btn-info" onClick="javascript:fcn_mostrar_modal_recibir_pedimentos();"><i class="fa fa-file-text" aria-hidden="true"></i> Recibir Pedimentos</button-->
										<button id="ibtn_facturar" type="button" class="btn btn-default" onClick="javascript:fcn_mostrar_modal_facturar();"><i class="fa fa-check-circle" aria-hidden="true"></i> Asignar cuenta de gastos</button>
									</div>
								</div>
							</div>
						</div>
						
						<div class="tab-pane" id="idiv_pend_entregar">
							<br>
							<div class="row">
								<div class="col-xs-12">
									<div class="dataTable_wrapper">
										<div class="table-responsive" style="overflow:hidden;">
											<table id="dtpendentregar" class="table table-striped table-bordered" width="100%">
												<thead>
													<tr>
														<th class="def_app_center">CUENTA GASTOS</th>
														<th class="def_app_center">TRAFICO</th>
														<th class="def_app_center">DETALLES</th>
													</tr>
												</thead>
												<tfoot>
													<tr>
														<th class="def_app_center">CUENTA GASTOS</th>
														<th class="def_app_center">TRAFICO</th>
														<th class="def_app_center">DETALLES</th>
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
										<button id="ibtn_pedentregar_entregar" type="button" class="btn btn-info" onClick="javascript:fcn_mostrar_modal_entregar_cuenta_gastos();"><i class="fa fa-check-circle" aria-hidden="true"></i> Entregar Cuentas</button>								
									</div>
								</div>
							</div>
						</div>
						
						<div class="tab-pane" id="idiv_entregados">
							<br>
							<!--div class="row">
								<div class="col-xs-12 col-md-12">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">Fecha Entrega:</div>
											<select class="form-control" id="isel_entregados_fechas" onChange="fcn_cargar_grid_entregados();"></select>
										</div>							
									</div>
								</div>
							</div-->
							<div class="row">
								<div class="col-xs-12">
									<div class="dataTable_wrapper">
										<div class="table-responsive" style="overflow:hidden;">
											<table id="dtentregados" class="table table-striped table-bordered" width="100%">
												<thead>
													<tr>
														<th class="def_app_center">CUENTA GASTOS</th>
														<th class="def_app_center">TRAFICO</th>
														<th class="def_app_center">FECHA ENTREGA</th>
														<th class="def_app_center">DETALLES</th>
													</tr>
												</thead>
												<tfoot>
													<tr>
														<th class="def_app_center">CUENTA GASTOS</th>
														<th class="def_app_center">TRAFICO</th>
														<th class="def_app_center">FECHA ENTREGA</th>
														<th class="def_app_center">DETALLES</th>
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
	<script src="../bower_components/bootstrap-select/js/bootstrap-select.js"></script>
	
	<!--Numeric TextBox-->
	<script  type="text/javascript" language="javascript" src="../plugins/numeric/jquery.numeric.min.js"></script>
	
	<!--MaskedInput
	<script  type="text/javascript" language="javascript" src="../plugins/maskedinput/jquery.maskedinput.min.js"></script>-->
		
	<script src="../js/expCxC.js?v=2017.12.28.1500"></script>
</body>

</html>
