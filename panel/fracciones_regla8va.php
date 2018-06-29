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

    <title>Regla 8va Partidas</title>

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
	
	<!-- datepicker -->
	<link href="../datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
    
	
	<link rel="icon" type="image/ico" href="../favicon.ico" />
	
	<style>
		#isec_reporte { display: none; }
		.def_app_right{text-align: right;}
		.def_app_left{text-align: left;}
		.def_app_center{text-align: center !important;}
		.font-size-18x {font-size:18px !important;}
		.icon-btn {cursor:pointer; text-decoration: none !important;}
	</style>
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php');?>
		
		<!-- MODAL PROCESAR REFERENCIA -->
		<div id="modal_procesar_ref" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><span id="lbl_titulo_procesar_referencia"></span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia</div>
										<input id="itxt_mdl_proref_referencia" class="form-control text-uppercase" type="text" maxlength="15">
										<span class="input-group-btn">
											<button id="ibtn_mdl_proref_buscar_ref" type="button" class="btn btn-primary" OnClick="javascript:fnc_ajax_buscar_referencia_procesar(); return false;"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div id="idiv_menseje_mdl_buscar_proref"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Filtrar por</div>
										<select id="sel_filtro_estado_parreferencia" class="form-control select2" style="width: 100%;" onchange="fnc_ajax_buscar_referencia_procesar();">
											<option value="0">TODOS LOS ESTATUS</option>
											<option value="pend">PENDIENTES</option>
											<option value="apli">TODAS APLICADAS R8va</option>
											<option value="apli_sis">APLICADAS R8va POR SISTEMA</option>
											<option value="apli_man">APLICADAS R8va MANUALMENTE</option>
											<option value="tlcs">APLICADAS TLC's</option>
											<option value="no_apli">NO APLICAN</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Remesa</div>
										<select id="sel_remesa_ref_seleccionada" class="form-control select2" style="width: 100%;" onchange="fnc_ajax_buscar_referencia_procesar();"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Permiso</div>
										<select id="sel_permisos_disponibles_ref" class="form-control select2" style="width: 100%;" onchange="fnc_ajax_buscar_referencia_procesar();"></select>
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-sm-6">
								<div class="form-group text-right">
									<div class="btn-group">
											<button id="ibtn_mdl_proref_cancelat" type="button" class="btn btn-warning" OnClick="javascript:fnc_limpiar_controles_procesar_referencia_mdl(); return false;"><i class="fa fa-ban" aria-hidden="true"></i> Cancelar</button>
											<button id="ibtn_mdl_proref_aplicar_todo" type="button" class="btn btn-primary" OnClick="javascript:fnc_aplicar_r8va_todo_array(0); return false;"><i class="fa fa-list-ul" aria-hidden="true"></i> Aplicar Permiso a Todas las Pendientes</button>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group text-left">
									<div class="table-responsive">
										<table id="dtpar_referencias" class="table table-condensed" cellspacing="0" width="100%">
											<thead style="background-color:#3071AA; color:#FFF;">
												<tr>
													<th>N&uacute;mero_Factura</th>
													<th>Acciones</th>
													<th>Estatus</th>
													<th>Proveedor</th>
													<th>Consecutivo</th>
													<th>Fracci&oacute;n_CASA</th>
													<th>Descripci&oacute;n</th>
													<th>N&uacute;mero_Permiso</th>
													<th>Cantidad</th>
													<th>Fracci&oacute;n_Anterior</th>
													<th>Fecha_Aplicaci&oacute;n</th>
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

		<!-- MODAL FRACCIONES -->
		<div id="modal_fraccion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><span id="lbl_titulo_agregar_fraccion"></span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<label>Descripci&oacute;n:</label>
									<input id="itxt_mdl_fraccion_descripcion" class="form-control text-uppercase" type="text" maxlength="255">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label>Fracci&oacute;n:</label>
									<input id="itxt_mdl_fraccion_fraccion" class="form-control text-uppercase integer" type="text" maxlength="10">
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label>Cantidad Total:</label>
									<input id="itxt_mdl_fraccion_cantidad" class="form-control text-uppercase integer" type="text" maxlength="10">
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label>Valor Total:</label>
									<input id="itxt_mdl_fraccion_valor" class="form-control text-uppercase decimal-2-places" type="text" maxlength="10">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6 text-left">
								<div class="form-group">
									<label>Fecha Vencimiento (dd/mm/yyyy):</label>
									<div class="input-append date input-group" id="idt_mdl_fecha_vence" data-date-format="dd/mm/yyyy">									
										<input type="text" id="idt_txt_fecha_vence" class="form-control" value="" readonly>
										<span class="input-group-addon">
											<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
										</span>
									</div>
								</div>					
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label>N&uacute;mero Permiso:</label>
									<input id="itxt_mdl_fraccion_numero_permiso" class="form-control text-uppercase" type="text" maxlength="30">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div id="idiv_menseje_mdl_fraccion"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
					<button type="button" class="btn btn-primary" onClick="javascript:fnc_guardar_fraccion();"><i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar</button>
					</div>
				</div>
			</div>
		</div>

		<!-- MODAL CERRAR REFERENCIA -->
		<div id="modal_referencia" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><span id="lbl_titulo_cerrar_referencia"></span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia</div>
										<input id="itxt_mdl_cerrar_ref_referencia" class="form-control text-uppercase" type="text" maxlength="15">
										<span class="input-group-btn">
											<button id="ibtn_mdl_cerrar_ref_buscar_ref" type="button" class="btn btn-success" OnClick="javascript:fnc_ajax_buscar_referencia_cerrar(); return false;"><i class="fa fa-check" aria-hidden="true"></i> Cerrar Referencia</button>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div id="idiv_menseje_mdl_buscar_cerrar_ref"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group text-right">
									<div class="btn-group">
											<button id="ibtn_mdl_cerrar_ref_cancelat" type="button" class="btn btn-warning" OnClick="javascript:fnc_limpiar_controles_cerrar_referencia_mdl(); return false;"><i class="fa fa-ban" aria-hidden="true"></i> Cancelar</button>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group text-left">
									<div class="table-responsive">
										<table id="dtpar_cerrar_ref" class="table table-condensed" cellspacing="0" width="100%">
											<thead style="background-color:#3071AA; color:#FFF;">
												<tr>
													<th>N&uacute;mero_Factura</th>
													<th>Acciones</th>
													<th>Remesa</th>
													<th>Proveedor</th>
													<th>Consecutivo</th>
													<th>Fracci&oacute;n</th>
													<th>Descripci&oacute;n</th>
													<th>N&uacute;mero_Permiso</th>
													<th>Cantidad</th>
													<th>Valor</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--div class="modal-footer">
						<button type="button" class="btn btn-success" onClick="javascript:fnc_cerrar_referencia();"><i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar</button>
					</div-->
				</div>
			</div>
		</div>

		<!-- MODAL AGREGAR INFORMACION DE LA PARTIDA -->
		<div id="modal_infopartida" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><span id="lbl_titulo_info_partida_web"></span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Permiso</div>
										<select id="sel_partida_info_permiso" class="form-control select2" style="width: 100%;" onchange="fnc_ajax_consultar_fracciones_info_partida('permiso');"></select>
										<!--input id="itxt_mdl_partida_info_fraccion_original" class="form-control text-uppercase" type="text" maxlength="10"-->
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Fracci&oacute;n</div>
										<select id="sel_partida_info_fraccion" class="form-control select2" style="width: 100%;" onchange="fnc_ajax_consultar_fracciones_info_partida('fracciones');"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Descripci&oacute;n</div>
										<select id="sel_partida_info_descripcion" class="form-control select2" style="width: 100%;"></select>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" onClick="javascript:fnc_ajax_agregar_partida_8va_sistema_web();"><i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar</button>
					</div>
				</div>
			</div>
		</div>

		<!-- MODAL APLICAR R8va A PARTIDA CON FRACCION APLICABLE -->
		<div id="modal_fraccion_apli_par" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><span id="lbl_titulo_aplicar_fraccion_partida"></span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="panel panel-info">
										<div class="panel-heading with-border">
											<h5><i class="fa fa-list-alt" aria-hidden="true"></i> Datos de la partida</h5>	
											<!--div class="box-tools pull-right">
												<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
											</div-->
										</div>
										<div class="panel-body no-padding">
											<div class="row">
												<div class="col-xs-12 col-md-4">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Factura</div>
															<input id="itxt_mdl_fraccdisp_factura" class="form-control text-uppercase" type="text" disabled>
														</div>
													</div>
												</div>
												<div class="col-xs-12 col-md-8">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Proveedor</div>
															<input id="itxt_mdl_fraccdisp_proveedor" class="form-control text-uppercase" type="text" disabled>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-12 col-md-4">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Fraccion</div>
															<input id="itxt_mdl_fraccdisp_fraccion" class="form-control text-uppercase" type="text" disabled>
														</div>
													</div>
												</div>
												<div class="col-xs-12 col-md-8">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Descripcion</div>
															<input id="itxt_mdl_fraccdisp_descripcion" class="form-control text-uppercase" type="text" disabled>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-6">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Cantidad</div>
															<input id="itxt_mdl_fraccdisp_cantidad" class="form-control text-uppercase" type="text" disabled>
														</div>
													</div>
												</div>
												<div class="col-xs-6">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Valor</div>
															<input id="itxt_mdl_fraccdisp_valor" class="form-control text-uppercase" type="text" disabled>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="panel panel-info">
										<div class="panel-heading with-border">
											<h5><i class="fa fa-list" aria-hidden="true"></i> Fracciones disponibles</h5>	
											<!--div class="box-tools pull-right">
												<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
											</div-->
										</div>
										<div class="panel-body no-padding">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group text-left">
														<div class="table-responsive">
															<table id="dtpar_fracciones_disp" class="table table-condensed" cellspacing="0" width="100%">
																<thead style="background-color:#3071AA; color:#FFF;">
																	<tr>
																		<th>#</th>
																		<th>Acciones</th>
																		<th>Fracci&oacute;n</th>
																		<th>Descripci&oacute;n</th>
																		<th>Saldo_Cantidad</th>
																		<th>Saldo_Valor</th>
																		<th>Fecha_Vence</th>
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
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- AGREGAR REGLA 8VA A FRACCION -->
		<div id="modalr8va_fracci" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-check-circle"></i> Aplicar regla 8va a partida del pedimento</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia:</div>
										<input id="itxt_mdl_fracci_aplir8va_referencia" class="form-control text-uppercase" type="text" disabled>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Partida:</div>
										<input id="itxt_mdl_fracci_aplir8va_partida" class="form-control text-uppercase" type="text" disabled>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Fracci&oacute;n:</div>
										<input id="itxt_mdl_fracci_aplir8va_fraccion" class="form-control text-uppercase" type="text" disabled>
									</div>
								</div>
							</div>
							<div class="col-sm-8">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Descripci&oacute;n:</div>
										<input id="itxt_mdl_fracci_aplir8va_descripcion" class="form-control text-uppercase" type="text" disabled>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Permiso</div>
										<select id="isel_mdl_fracci_aplir8va_permiso" class="form-control select2" style="width: 100%;" onchange="fnc_ajax_consultar_fracciones_info_parfac_fraccion('permiso');"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Fracci&oacute;n</div>
										<select id="isel_mdl_fracci_aplir8va_fraccion" class="form-control select2" style="width: 100%;" onchange="fnc_ajax_consultar_fracciones_info_parfac_fraccion('fracciones');"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Descripci&oacute;n</div>
										<select id="isel_mdl_fracci_aplir8va_descripcion" class="form-control select2" style="width: 100%;"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-info">
									<div class="panel-heading"><i class="fa fa-file-alt"></i> Partidas-Facturas</div>
									<div class="panel-body">
										<div class="row">
											<div class="col-md-12">
												<div class="form-group text-right">
												<button type="button" class="btn btn-primary" OnClick="javascript:fnc_agregar_parfac_fraccion_cierre(); return false;"><i class="fa fa-plus"></i> Agregar Partida</button>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group text-left">
													<div class="table-responsive">
														<table id="dtparfac_fraccion" class="table table-condensed" cellspacing="0" width="100%">
															<thead style="background-color:#3071AA; color:#FFF;">
																<tr>
																	<th>N&uacute;mero_Factura</th>
																	<th>Acciones</th>
																	<th>N&uacute;mero_Parte</th>
																	<th>Fracci&oacute;n</th>
																	<th>Descripci&oacute;n</th>
																	<th>Cantidad</th>
																	<th>Valor</th>
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
					</div>
					<div class="modal-footer">
						<button id="modal_btn_fracci_apli_r8va" type="button" class="btn btn-success" OnClick="javascript:fnc_ajax_aplicar_r8va_parped_cierre(); return false;"><i class="fa fa-save"></i> Aplicar Regla</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- PARTIDAS FACTURA DE LA FRACCION -->
		<div id="modalr8va_fracci_parfac_editar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-lg modal-info">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><span id="lbl_titulo_mdl_fraccion_parfac"></span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Factura</div>
										<input id="itxt_mdl_fracci_parfac_factura" class="form-control text-uppercase" type="text" disabled>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">N&uacute;mero Parte</div>
										<select id="isel_mdl_fracci_parfac_numparte" class="form-control select2" style="width: 100%;" onchange="fnc_seleccionar_numparte_parfac_fraccion(this.value); return false;"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Fracci&oacute;n</div>
										<input id="itxt_mdl_fracci_parfac_fraccion" class="form-control text-uppercase" type="text" disabled>
									</div>
								</div>
							</div>
							<div class="col-sm-8">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Descripci&oacute;n</div>
										<input id="itxt_mdl_fracci_parfac_descripcion" class="form-control text-uppercase" type="text" disabled>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Cantidad</div>
										<input id="itxt_mdl_fracci_parfac_cantidad" class="form-control text-uppercase integer" type="text" >
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Valor</div>
										<input id="itxt_mdl_fracci_parfac_valor" class="form-control text-uppercase decimal-2-places" type="text" >
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success" OnClick="javascript:fnc_guardar_parfac_fraccion_cierre(); return false;"><i class="fa fa-save"></i> Guardar</button>
					</div>
				</div>
			</div>
		</div>
		
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
		<div id="modalconfirm" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
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
		<!-- FRACCION ORIGINAL :: ELMINAR R8VA PARTIDA PEDIMENTO (FRACCI)-- >
		<div id="modalfracciori" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Fraccion Original</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<label>Fracci&oacute;n Original:</label>
									<input id="itxt_mdl_fraccion_original" class="form-control text-uppercase" type="text" maxlength="255">
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="modal_btn_eliminar_r8va" type="button" class="btn btn-warning" OnClick="javascript:fnc_ajax_eliminar_regla8va_parped_cierre_noweb(0); return false;"><i class="fa fa-trash"></i> Eliminar Regla</button>
					</div>
				</div>
			</div>
		</div-->
		
		<!--div class="jumbotron"-->
		<div class="row">	
			<div class="col-xs-12">
				<ul class="nav nav-tabs" role="tablist" id="librop_tabs">
					<li class="active"><a href="#partidas" role="tab" data-toggle="tab"><i class="fa fa-history" aria-hidden="true"></i> Hist&oacute;rico De Partidas Actualizadas</a></li>
					<li role="presentation"><a href="#fracciones" role="tab" data-toggle="tab"><i class="fa fa-list" aria-hidden="true"></i> Fracciones</a></li>
					<li role="presentation"><a href="#partidas_pedimento" role="tab" data-toggle="tab"><i class="fa fa-list-ol"></i> Partidas Del Pedimento</a></li>
					<li role="presentation"><a href="#referencias" role="tab" data-toggle="tab"><i class="fa fa-file-text-o" aria-hidden="true"></i> Referencias</a></li>
				</ul>
				<div class="tab-content">
					<div id="partidas" class="tab-pane active">
						<div class="panel panel-default">
							<div class="panel-heading">Historico de partidas donde se aplico la regla 8va.</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-12 col-md-12">
										<div class="form-group text-right">
											<button id="ibtn_procesar_referencia" type="button" class="btn btn-primary" onClick="javascript:fcn_procesar_referencia_r8va();"><i class="fa fa-cogs" aria-hidden="true"></i> Procesar Referencia</button>					
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-12">
										<div class="dataTable_wrapper">
											<div class="table-responsive">
												<table id="dtpartidas" class="table table-striped table-bordered" width="100%">
													<thead>
														<tr>
															<th class="def_app_center">#</th>
															<!--th class="def_app_center">Acciones</th-->
															<th class="def_app_center">Referencia</th>
															<th class="def_app_center">Factura</th>
															<th class="def_app_center">Proveedor</th>
															<th class="def_app_center">Numero_Partida</th>
															<th class="def_app_center">Fracci&oacute;n_Original</th>
															<th class="def_app_center">Descripci&oacute;n</th>
															<th class="def_app_center">N&uacute;mero_Permiso</th>
															<th class="def_app_center">Fecha_Registro</th>
															<th class="def_app_center">Usuario_Registro</th>
														</tr>
													</thead> 
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="panel-footer text-right">
								<small>Monitor de Referencias &reg; 2017 &copy; Grupo Aduanero Del Bravo S.A. de C.V.</small>
							</div>
						</div>
					</div>
					<div id="fracciones" class="tab-pane">
						<div class="panel panel-default">
							<div class="panel-heading">Catalogo de fracciones para la aplicacion de la regla 8va.</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-12 col-md-12">
										<div class="form-group text-right">
											<button id="ibtn_nueva_fraccion" type="button" class="btn btn-primary" onClick="javascript:fcn_agregar_fraccion_disponible();"><i class="fa fa-plus" aria-hidden="true"></i> Nueva</button>					
										</div>
									</div>
								</div>
								<div class="row"><div class="col-xs-12 col-md-12">&nbsp;</div></div>
								<div class="row"><div class="col-xs-12 col-md-12"><div id="mensaje_librop_editar"></div></div></div>
								<div class="row">
									<div class="col-xs-12">
										<div class="dataTable_wrapper">
											<div class="table-responsive">
												<table id="dtfracciones" class="table table-striped table-bordered" width="100%">
													<thead>
														<tr>
															<th class="def_app_center">#</th>
															<th class="def_app_center">Acciones</th>
															<th class="def_app_center">Descripci&oacute;n</th>
															<th class="def_app_center">Fracci&oacute;n</th>
															<th class="def_app_center">Cantidad</th>
															<th class="def_app_center">Saldo_Cantidad</th>
															<th class="def_app_center">Valor</th>
															<th class="def_app_center">Saldo_Valor</th>
															<th class="def_app_center">Vence</th>
															<th class="def_app_center">Regla_8va</th>
															<th class="def_app_center">Fecha_Registro</th>
															<th class="def_app_center">Usuario_Registro</th>
														</tr>
													</thead>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="panel-footer text-right">
								<small>Monitor de Referencias &reg; 2017 &copy; Grupo Aduanero Del Bravo S.A. de C.V.</small>
							</div>
						</div>
					</div>
					<div id="partidas_pedimento" class="tab-pane">
						<div class="panel panel-default">
							<div class="panel-heading">Aplicacion de regla 8va en partidas del pedimento marcado como cierre.</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-12">
										<div class="form-group">
											<div class="input-group">
												<div class="input-group-addon">Referencia</div>
												<input id="itxt_referencia_buscar_partidas_cierre" class="form-control text-uppercase" type="text" maxlength="15">
												<span class="input-group-btn">
													<button id="ibtn_mdl_parref_buscar_ref" type="button" class="btn btn-primary" OnClick="javascript:fnc_ajax_buscar_referencia_parped_cierre(); return false;"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
													<button id="ibtn_mdl_parref_cancelar_ref" type="button" class="btn btn-warning" OnClick="javascript:fnc_cancelar_referencia_cierre(); return false;"><i class="fa fa-ban" aria-hidden="true"></i> Cancelar</button>
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12">
										<div id="idiv_menseje_parpedimento_cierre"></div>
									</div>
								</div>
								<!--div class="row">
									<div class="col-xs-12">
										<div class="form-group text-right">
											<button id="ibtn_mdl_proref_buscar_ref" type="button" class="btn btn-info" OnClick="javascript:fnc_ajax_buscar_referencia_procesar(); return false;"><i class="fa fa-refresh" aria-hidden="true"></i> Actualizar</button>
										</div>
									</div>
								</div-->
								<div class="row">
									<div class="col-md-12">
										<div class="form-group text-left">
											<div class="table-responsive">
												<table id="dtpar_pedimentocierre" class="table table-condensed" cellspacing="0" width="100%">
													<thead>
														<tr>
															<th>N&uacute;mero_Partida</th>
															<th>Acciones</th>
															<th>Estado</th>
															<th>Fracci&oacute;n</th>
															<th>Descripci&oacute;n</th>
															<th>Tipo_Moneda</th>
															<th>Valor_Aduana</th>
															<th>Cantidad_Tarifa</th>
															<th>UMT</th>
															<th>Valor_Comercial</th>
															<th>Cantidad_Factura</th>
															<th>UMC</th>
															<th>Pa&iacute;s_Origen</th>
															<th>Pa&iacute;s_Vendedor</th>
															<th>N&uacute;mero_Permiso</th>
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
					<div id="referencias" class="tab-pane">
						<div class="panel panel-default">
							<div class="panel-heading">Cerrar referencia para analizar la correcta aplicacion de la regla 8va en cada partida.</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-12 col-md-12">
										<div class="form-group text-right">
											<button id="ibtn_cerrar_referencia" type="button" class="btn btn-success" onClick="javascript:fnc_show_modal_cerrar_referencia();"><i class="fa fa-list-alt" aria-hidden="true"></i> Cerrar Referencia</button>					
										</div>
									</div>
								</div>
								<div class="row"><div class="col-xs-12 col-md-12">&nbsp;</div></div>
								<div class="row"><div class="col-xs-12 col-md-12"><div id="mensaje_cerrar_referencia"></div></div></div>
								<div class="row">
									<div class="col-xs-12">
										<div class="dataTable_wrapper">
											<div class="table-responsive">
												<table id="dtreferencias" class="table table-striped table-bordered" width="100%">
													<thead>
														<tr>
															<th class="def_app_center">#</th>
															<th class="def_app_center">Referencia</th>
															<th class="def_app_center">Acciones</th>
															<th class="def_app_center">Cliente</th>
															<th class="def_app_center">Fecha_Cierre</th>
															<th class="def_app_center">Usuario_Cierre</th>
														</tr>
													</thead>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="panel-footer text-right">
								<small>Monitor de Referencias &reg; 2017 &copy; Grupo Aduanero Del Bravo S.A. de C.V.</small>
							</div>
						</div>
					</div>
				</div>				
			</div>
		</div>
		<!--/div-->
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
	
	<!-- selectize JavaScript -->
	<script src="../bower_components/selectize/dist/js/standalone/selectize.js"></script>
	
	<!-- boostrapselect JavaScript -->
	<script src="../bower_components/bootstrap-select/js/bootstrap-select.js"></script>
	<!-- datepicker -->
    <script src="../datepicker/js/bootstrap-datepicker.js"></script>
	<!--Numeric-->
	<script  type="text/javascript" language="javascript" src="../plugins/numeric/jquery.numeric.js"></script>

	<script src="../js/regla8va_steris.js?2018.03.22.1020"></script>
</body>

</html>
