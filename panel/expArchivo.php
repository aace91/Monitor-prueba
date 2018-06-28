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
	
    <title>Archivo</title>

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
	
	<!--TouchSpin-->
    <link rel="stylesheet" href="../plugins/touchspin/jquery.bootstrap-touchspin.css">
		
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
		
		.uppercase
		{
			text-transform:uppercase;
		}
		
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
	</style>
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
													<th class="def_app_center" style="width:15px;"></th>
												</tr>
											</thead>											
										</table>
									</div>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Impresora Etiquetas:</div>
										<select class="form-control" id="isel_modal_select_empresa_impresora"></select>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_select_empresa_ultima_caja"></div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_select_empresa_mensaje" style="display:none;"></div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Numero de Caja:</div>
										<input id="itxt_modal_select_empresa_caja" class="form-control" type="text" maxlength="60">
										<span class="input-group-btn">
											<button id="ibtn_modal_select_empresa_continuar" type="button" class="btn btn-success" onclick="ajax_get_verificar_caja();"><i class="fa fa-check-circle"></i> Continuar</button>
										</span>
									</div>
								</div>
							</div>
						</div>
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
													<th class="def_app_center">COMENTARIOS</th>
													<th class="def_app_center">FECHA FACTURA</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th class="def_app_center">CUENTA GASTOS</th>
													<th class="def_app_center">TRAFICO</th>
													<th class="def_app_center">PEDIMENTO</th>
													<th class="def_app_center">COMENTARIOS</th>
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
		
		<!-- MODAL AGREGAR CAJA -->
		<div id="modal_add_caja" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Agregar Caja</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12 col-md-12 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones:</div>
										<input id="itxt_modal_add_caja_observaciones" class="form-control" type="text" onKeyUp="toUpper(this)" maxlength="250">
									</div>									
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_add_caja_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_modal_add_caja_aceptar" type="button" class="btn btn-success" onClick="javascript:fcn_modal_add_caja();"><i class="fa fa-plus"></i> Agregar Caja</button>	
						<button id="ibtn_modal_add_caja_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL GENERAR RELACION CAJA -->
		<div id="modal_relacion_caja" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Generar Relacion de Caja</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Numero de Caja:</div>
										<input id="itxt_modal_relacion_caja_caja" class="form-control" type="text" maxlength="5">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_relacion_caja_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_modal_relacion_caja_aceptar" type="button" class="btn btn-success" onClick="javascript:fcn_exportar_relacion_caja();"><i class="fa fa-file-pdf-o"></i> Generar Relacion de Caja</button>	
						<button id="ibtn_modal_relacion_caja_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL IMPRIMIR ETIQUETA CAJA -->
		<div id="modal_etiqueta_caja" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Generar Etiqueta de Caja</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Numero de Caja:</div>
										<input id="itxt_modal_etiqueta_caja_caja" class="form-control" type="text" maxlength="5">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_etiqueta_caja_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_modal_etiqueta_caja_aceptar" type="button" class="btn btn-success" onClick="javascript:fcn_exportar_etiqueta_caja();"><i class="fa fa-print"></i> Generar Relacion de Caja</button>	
						<button id="ibtn_modal_etiqueta_caja_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODIFICAR CUENTA (CASO CUENTA CANCELADA) -->
		<div id="modal_cancelar_cuenta" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Cancelar Cuenta</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Cuenta Cancelada:</div>
										<input id="itxt_modal_cancelar_cuenta_ctacancelada" class="form-control text-uppercase" type="text" maxlength="14">
									</div>
								</div>
							</div>
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Cuenta que reemplaza:</div>
										<input id="itxt_modal_cancelar_cuenta_ctanueva" class="form-control text-uppercase" type="text" maxlength="14">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_cancelar_cuenta_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_modal_cancelar_cuenta_aceptar" type="button" class="btn btn-success" onClick="javascript:ajax_set_upd_cancelar_cta();"><i class="fa fa-exchange"></i> Cancelar y Reemplazar Cuenta</button>	
						<button id="ibtn_modal_cancelar_cuenta_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<!-- REIMPRIMIR ETIQUETA DE CUENTA DE GASTOS -->
		<div id="modal_imprimir_cuenta" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Reimprimir Etiqueta Cuenta</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Cuenta:</div>
										<input id="itxt_modal_imprimir_cuenta_cuenta" class="form-control text-uppercase" type="text" maxlength="14">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_imprimir_cuenta_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_modal_imprimir_cuenta_aceptar" type="button" class="btn btn-success" onClick="javascript:ajax_get_datos_cuenta();"><i class="fa fa-print"></i> Imprimir Etiqueta</button>	
						<button id="ibtn_modal_imprimir_cuenta_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<div class="jumbotron" style="padding-top: 10px; padding-bottom: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Archivo</strong> <small></small>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-8">
							<h5 id="istr_trabajando_empresa" class="text-danger"></h5>							
							<h5 id="istr_trabajando_Caja" class="text-danger"></h5>
						</div>
						<div class="col-xs-4">
							<div class="dropdown pull-right">
								<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
									<i class="fa fa-cog" aria-hidden="true"></i> Opciones
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
									<li><a onclick="fcn_mostrar_empresas();" href="#"><i class="fa fa-exchange" aria-hidden="true"></i> Cambiar Empresa y Caja</a></li>
									<li><a onclick="fcn_mostrar_modal_add_caja();" href="#"><i class="fa fa-plus" aria-hidden="true"></i> Agregar Nueva Caja</a></li>
									<li><a onclick="fcn_mostrar_modal_relacion_caja();" href="#"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Generar Relacion de Caja</a></li>
									<li><a onclick="fcn_mostrar_modal_etiqueta_caja();" href="#"><i class="fa fa-print" aria-hidden="true"></i> Imprimir Etiqueta de Caja</a></li>
									<li><a onclick="fcn_mostrar_modal_imprimir_cuenta_etiqueta();" href="#"><i class="fa fa-print" aria-hidden="true"></i> Reimprimir Etiqueta de Cuenta</a></li>
									<li><a onclick="fcn_mostrar_modal_cancelar_cuenta();" href="#"><i class="fa fa-ban" aria-hidden="true"></i> Cancelar Cuenta</a></li>
								</ul>
							</div>						
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dtpendientes" class="table table-striped table-bordered" width="100%">
										<thead>
											<tr>
												<th class="def_app_center">CUENTA GASTOS</th>
												<th class="def_app_center">TRAFICO</th>
												<th class="def_app_center">DETALLES</th>
												<th class="def_app_center">ETIQUETA</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th class="def_app_center">CUENTA GASTOS</th>
												<th class="def_app_center">TRAFICO</th>
												<th class="def_app_center">DETALLES</th>
												<th class="def_app_center">ETIQUETA</th>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
					</div>
						
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<button id="ibtn_sync" type="button" class="btn btn-default" onClick="javascript:ajax_set_upd_aplicar_digitalizacion('button');"><i class="fa fa-refresh" aria-hidden="true"></i> Sincronizar Documentos</button>
							&nbsp;<span id="ispan_sync_digitalizacion_message"></span>
						</div>
						<!--<div class="col-xs-12 col-md-6">
							<button id="ibtn_archivar" type="button" class="btn btn-default" onClick="javascript:fcn_mostrar_modal_archivar();"><i class="fa fa-archive" aria-hidden="true"></i> Archivar Cuentas</button>
						</div>-->
						<div class="col-xs-12 col-md-6">
							<span id="ispan_sync_message" class="pull-right" style="padding: 7px;"></span>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-12 text-left">
							<h5>
								<i class="fa fa-archive" aria-hidden="true"></i> <strong>Archivar Cuentas</strong>
							</h5>
						</div>
						
						<div class="col-xs-12 col-md-12">
							<div id="idiv_archivar_mensaje" style="display:none;"></div>
						</div>
						
						<div class="col-xs-12 col-md-12 text-left">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Cuenta Gastos:</div>
									<input id="itxt_archivar_cuenta_cuenta" class="form-control text-uppercase" type="text" maxlength="14">
									<span class="input-group-btn">
										<button id="ibtn_archivar_cuenta_agregar" type="button" class="btn btn-info" onclick="fcn_archivar();"><i class="fa fa-plus"></i> Archivar Cuenta</button>
									</span>
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
	
	<!--TouchSpin-->
	<script  type="text/javascript" language="javascript" src="../plugins/touchspin/jquery.bootstrap-touchspin.js"></script>
		
	<!--MaskedInput-->
	<script  type="text/javascript" language="javascript" src="../plugins/maskedinput/jquery.maskedinput.min.js"></script>
	
	<!-- boostrapselect JavaScript -->
	<!--<script src="../bower_components/bootstrap-select/js/bootstrap-select.js"></script>-->
	
	<!-- ETIQUETAS DYMO -->
	<script src="../js/DYMO.Label.Framework.2.0.2.js" type="text/javascript" charset="UTF-8"></script>

	<script src="../js/expArchivo.js?v=2018.01.05.1600"></script>
	<script src="../js/expArchivoLabels.js?v=2017.12.27.1600"></script>
</body>

</html>
