<?php
include_once('./../checklogin.php');
include('./../connect_dbsql.php');
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
    <title>Cruces - Monitor De Referencias</title>
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
	<!-- Select2 CSS-->
	<link href="../bower_components/select2/dist/css/select2.min.css" rel="stylesheet">
	<link href="../bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css" rel="stylesheet">
	<!-- FileInput -->
	<link href="../bower_components/bootstrap-fileinput-4.2.3/css/fileinput.min.css" rel="stylesheet"/>
	<!-- Bootstrap Datepicker -->
	<link href="../bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
	<!-- Switch -->
	<link href="../bower_components/switch/css/style_switch.css" rel="stylesheet" type="text/css">
	<!--TouchSpin-->
    <link rel="stylesheet" href="../plugins/touchspin/jquery.bootstrap-touchspin.css">
	
	<style>
		#idiv_panel_principal { display: block; }
		#idiv_panel_secundario { display: none; }
		#idiv_principal_agregar_emb { display: none; }
		.def_app_right{text-align: right;}
		.def_app_left{text-align: left;}
		.def_app_center{text-align: center !important;}
		.def_app_button_default_datatable { color:#000 !important; }
		a:hover {cursor:pointer;}
		
		.verde_btns {background-color:#30AB53;color:#FFF;}
		.col-cfdi-fac{ width:350px !important; }
		.bg-cruce {background-color:#FAB46F;color:#333;}
		.bg-cruce-body {background-color:#FDF4E4;}
		
		.bg-factura {background-color:#6AB0D6;color:#FFF;}
		.bg-factura-body {background-color:#EDF8FE;}
		
		.bg_impdoc {background-color:#5cb85c;color:#FFF;}
		.bg_impdoc_body {background-color:#D8FCDB;}

		.bg-plantilla {background-color:#4D90DF;color:#FFF;}
		.bg-plantilla-body {background-color:#F4F9FF;}
		
		div.dataTables_processing { z-index: 1; } 

		.table {background-color:#FFF;}
		
		.tb_pointer{cursor:pointer;}
		
	</style>
</head>

<body id="body">
	<div class="container">
		<!-- MODAL AGREGAR CRUCE DE EXPORTACION -->
		<div id="modal_estado" class="modal">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header bg-cruce">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span class="glyphicon glyphicon-road"></span> Cruce De Exportaci&oacute;n</h4>
					</div>
					<div class="modal-body bg-cruce-body">
						<div class="row">
							<div class="col-md-8">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Cliente</div>
										<input id="txt_cliente_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Fecha</div>
										<input id="txt_fecha_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-8">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Linea Transportista</div>
										<select id="sel_linea_transportista" class="form-control select2" style="width: 100%;"></select>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Aduana</div>
										<select id="sel_aduana" class="form-control select2" style="width: 100%;">	
											<option value="">[SELECCIONAR]</option>
											<option value="240">240 - NUEVO LAREDO, TAMPS.</option>
											<option value="800">800 - COLOMBIA, NUEVO LEON.</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Transfer</div>
										<select id="sel_transfer" class="form-control select2" style="width: 100%;">	
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">CAAT</div>
										<input id="txt_caat_transfer" type="text" class="form-control text-uppercase" maxlength="10">
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">SCAC</div>
										<input id="txt_scac_transfer" type="text" class="form-control text-uppercase" maxlength="10">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">PO Number</div>
										<input id="txt_po_number" type="text" class="form-control text-uppercase" maxlength="10">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Entregar En</div>
										<select id="sel_lugares_entrega" class="form-control select2" style="width: 100%;"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Direcci&oacute;n</div>
										<input id="txt_direccion_entrega" type="text" class="form-control text-uppercase" disabled>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Indicaciones</div>
										<input id="txt_indicaciones" type="text" maxlength="255" class="form-control text-uppercase">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<label class="switch switch-green">
									  <input id="chk_consolidar_cruce" type="checkbox" class="switch-input">
									  <span class="switch-label" data-on="Si" data-off="No"></span>
									  <span class="switch-handle"></span>
									</label>&nbsp;&nbsp;<label>Consolidar Cruce</label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Consolidar con</div>
										<select id="sel_consolidar" class="form-control select2" style="width: 100%;" disabled></select>
										<span class="input-group-btn">
											<button type="button" class="btn btn-primary" OnClick="javascript:agregar_cliente_consolidar(); return false;"><span class="glyphicon glyphicon-plus"></span> Agregar</button>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
		  							<div id="mensaje_cliente_consolidar_cruce"></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group text-left">
									<div class="table-responsive">
										<table id="tbl_cliconsolidar" class="table table-condensed" cellspacing="0" width="100%">
											<thead style="background-color:#3071AA; color:#FFF;">
												<tr>
													<th>#</th>
													<th>Nombre Cliente</th>
													<th>Acciones</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones</div>
										<textarea class="form-control text-uppercase" rows="3" id="txt_observaciones_cruce"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group text-right">
									<button id="btn_nueva_factura_cruce" type="button" class="btn btn-primary" onclick="agregar_factura_cruce();"><span class="glyphicon glyphicon-plus"></span> Nueva Factura</button>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group text-left">
									<div class="table-responsive">
										<table id="rpt_facturas" class="table table-condensed" cellspacing="0" width="100%">
											<thead style="background-color:#3071AA; color:#FFF;">
												<tr>
													<th>N&uacute;mero_Contenedor</th>
													<th>N&uacute;mero_Factura</th>
													<th>UUID</th>
													<th>Fecha_Factura</th>
													<th>Agente_Aduanal_Americano</th>
													<th>Referencia</th>
													<th>Regimen</th>
													<th>Atados</th>
													<th>Peso Kgs.</th>
													<th>Peso Lbs.</th>
													<th>Factura</th>
													<th>CFDI</th>
													<th>Anexo Factura</th>
													<th>Packing_List</th>
													<th>Certificado_Origen</th>
													<th>Ticket Bascula</th>
													<th>Contactos AAA</th>
													<th>Pedimento</th>
													<th>Acciones</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_modal_cruces"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer bg-cruce">
						<!--button id="modalconfirm_btn_cancel" type="button" class="btn btn-danger pull-left"><span class="glyphicon glyphicon-ban-circle"></span> Cancelar</button-->
						<button id="btn_guardar_cruce" onclick="guardar_cruce_expo();" type="button" class="btn btn-success"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar</button>
					</div>
				</div>
			</div>
		</div>
		<!-- MODAL MESSAAGEBOX ERROR -->
		<div id="modalmessagebox_error" class="modal fade modal-danger" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span style="color:#a94442;" id="modalmessagebox_error_titulo"> </span></h4>
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
		<!-- MODAL Diferencias -->
		<div id="modalmessagebox_dif" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span style="color:#a94442;" id="modalmessagebox_error_titulo">Error :: Datos inconsistente o sin informaci&oacute;n</span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div id="modalmessagebox_dif_mensaje"></div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-info" data-dismiss="modal">OK</button>
					</div>
				</div>
			</div>
		</div>
		<!-- Agregar Factura -->
		<div id="modal_factura" class="modal modal-danger">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header bg-factura">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span class="glyphicon glyphicon-file"></span> Nueva Factura</h4>
					</div>
					<div class="modal-body bg-factura-body">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia</div>
										<input id="txt_buscar_referencia_mdl" type="text" class="form-control text-uppercase" placeholder="" maxlength="10">
										<span class="input-group-btn">
											<button id="btn_seleccionar_referencia_factura" onclick="ajax_seleccionar_referencia_factura('mdl_factura'); return false;" type="button" class="btn btn-info"><i class="fa fa-check" aria-hidden="true"></i> Seleccionar</button>
											<button id="btn_generar_referencia_factura" onclick="generar_nueva_referencia_expo('factura'); return false;" type="button" class="btn btn-primary"><i class="fa fa-cogs" aria-hidden="true"></i> Generar Nueva</button>
											<button id="btn_cancelar_referencia_factura" onclick="cancelar_referencia_factura(); return false;" type="button" class="btn btn-danger" style="display:none;"><i class="fa fa-ban" aria-hidden="true"></i> Cancelar</button>
										</span>
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<div id="mensaje_consultar_referencia_factura"></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Pedimento</div>
										<input type="text" id="txt_pedimento_referencia_mdl" class="form-control" value="" placeholder="" disabled>
										<span class="input-group-btn">
											<button id="btn_generar_pedimento" onclick="generar_nuevo_pedimento_referencia(); return false;" type="button" class="btn btn-primary"><i class="fa fa-cogs" aria-hidden="true"></i> Generar Pedimento</button>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Tipo de Salida</div>
										<select id="sel_tipo_salida" class="form-control select2" style="width: 100%;" onchange="mostrar_etiquete_contenedor(this.value);">	
											<?php
												
												$consulta = "SELECT titulo, visible
															FROM bodega.tipo_salida_expo
															WHERE visible = 1
															ORDER BY titulo";
																
												$query = mysqli_query($cmysqli,$consulta);							
												if (!$query) {
													$error=mysqli_error($cmysqli);
													echo '<option value="">'.$error.'</option>';
												} else {
													echo '<option value="">[SELECCIONAR]</option>';
													while ($row = mysqli_fetch_array($query)){
														echo '<option value="'.$row['titulo'].'">'.$row['titulo'].'</option>';
													}
												}
											?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon"><span id="lbl_contenedor"></span></div>
										<input id="txt_numero_caja" type="text" maxlength="20" class="form-control text-uppercase">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">R&eacute;gimen del Pedimento</div>
										<select id="sel_regimen" class="form-control select2" style="width: 100%;"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label class="control-label">CFDI (xml)</label>
									<input id="upload_cfdi" type="file" class="file-loading" data-show-upload="false" data-allowed-file-extensions='["xml"]'>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_modal_procesar_cfdi"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">N&uacute;mero Factura</div>
										<input id="txt_numero_factura_mdl" type="text" class="form-control text-uppercase" placeholder=""  maxlength="20" disabled>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<!--input type="text" id="txt_fecha_factura_mdl" class="form-control" value="" placeholder="" disabled-->
									<!--div class="col-md-12"-->
										<div class="input-group">
											<div class="input-group-addon">Fecha Factura</div>
											<div class="date date-time">
												<input id="txt_fecha_factura_mdl" type="text" class="form-control" readonly/>
												<!--span class="input-group-addon">
													<span class="glyphicon glyphicon-calendar"></span>
												</span-->
											</div>
										</div>
									<!--/div-->
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">UUID</div>
										<input id="txt_numero_uuid_mdl" type="text" class="form-control text-uppercase" maxlength="45" placeholder="" disabled>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Atados</div>
										<input id="txt_numero_atados" type="text" class="form-control text-right integer" placeholder="">
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Peso Factura</div>
										<input type="text" id="txt_peso_factura" class="form-control text-right decimal-3-places" value="" placeholder="">
										<div class="input-group-addon">kgs</div>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Peso</div>
										<input type="text" id="txt_peso_factura_lbs" class="form-control text-right decimal-3-places" value="" placeholder="">
										<div class="input-group-addon">lbs</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Agente Aduanal Americano</div>
										<select id="sel_agente_americano" class="form-control select2" style="width: 100%;"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="panel panel-info">
										<div class="panel-heading">
											<h5><span class="glyphicon glyphicon-file"></span> Avisos Autom&aacute;ticos</h5>	
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-5">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Aviso de Adhesi&oacute;n</div>
															<select id="sel_aviso_adhesion" class="form-control select2" style="width: 100%;" onchange="seleccionar_permiso_adhesion_archivo(); return false;">	
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-5">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Aviso Autom&aacute;tico</div>
															<select id="sel_permisos" class="form-control select2" style="width: 100%;" onchange="seleccionar_aviso_automatico_archivo(); return false;"></select>
														</div>
													</div>
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<button id="btn_tipo_caja" onclick="guardar_nuevo_permiso();" type="button" class="btn btn-primary" ><span class="glyphicon glyphicon-plus"></span> Agregar</button>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<div id="mensaje_modal_fac_permisos"></div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6 text-center">
													<div id="descargar_aviso_adhesion"></div>
												</div>
												<div class="col-md-6 text-center">
													<div id="descargar_aviso_automatico"></div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<div class="form-group text-left">
														<div class="table-responsive">
															<table id="tbl_permisos" class="table table-condensed" cellspacing="0" width="100%">
																<thead style="background-color:#3071AA; color:#FFF;">
																	<tr>
																		<th>Aviso Autom&aacute;tico</th>
																		<th>Aviso Adhesi&oacute;n</th>
																		<th>Acciones</th>
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
						<div class="row">
							<div id="div_fileupload_factura_pdf">
								<div class="col-md-12">
									<div class="form-group">
										<label class="control-label">Factura (pdf)</label>
										<input id="upload_factura" type="file" class="file-loading" data-show-upload="false" data-allowed-file-extensions='["pdf"]'>
									</div>
								</div>
							</div>
							<div id="div_descargar_factura_pdf">
								<div class="col-md-12">
									<div class="panel panel-info">
										<div class="panel-heading">
											<h5><span class="glyphicon glyphicon-file"></span> Factura</h5>	
										</div>
										<div class="panel-body">
											<div class="col-xs-6 text-center">
												<div id="link_descargar_factura_pdf"></div>
											</div>
											<div class="col-xs-6 text-center">
												<a href="javascript:void(0);" onclick="mostrar_fileupload_factura('factura'); return false;" class="btn btn-primary" title="Subir Factura">
												<span class="glyphicon glyphicon-cloud-upload"></span>  <span class="hidden-sm hidden-md hidden-xs">Subir Factura</span><span class="hidden-lg hidden-xs">Subir</span>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div id="div_fileupload_anexo_factura_pdf">
								<div class="col-md-12">
									<div class="form-group">
										<label class="control-label">Anexo Factura (pdf,xls,xlsx)</label>
										<input id="upload_anexo_factura" type="file" class="file-loading" data-show-upload="false" data-allowed-file-extensions='["pdf","xls","xlsx"]'>
									</div>
								</div>
							</div>
							<div id="div_descargar_anexo_factura_pdf">
								<div class="col-md-12">
									<div class="panel panel-info">
										<div class="panel-heading">
											<h5><span class="glyphicon glyphicon-file"></span> Anexo Factura</h5>	
										</div>
										<div class="panel-body">
											<div class="col-xs-4 text-center">
												<div id="link_descargar_anexo_factura_pdf"></div>
											</div>
											<div class="col-xs-4 text-center">
												<div id="link_eliminar_anexo_factura_pdf"></div>
											</div>
											<div class="col-xs-4 text-center">
												<a href="javascript:void(0);" onclick="mostrar_fileupload_factura('anexo_factura'); return false;" class="btn btn-primary" title="Subir Anexo Factura">
												<span class="glyphicon glyphicon-cloud-upload"></span>  <span class="hidden-sm hidden-md hidden-xs">Subir Anexo Factura</span><span class="hidden-lg hidden-xs">Subir</span>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div id="div_fileupload_packlist_pdf">
								<div class="col-md-12">
									<div class="form-group">
										<label class="control-label">Packing List/Carta Manifiesto (pdf)</label>
										<input id="upload_packing" type="file" class="file-loading" data-show-upload="false" data-allowed-file-extensions='["pdf"]'>
									</div>
								</div>
							</div>
							<div id="div_descargar_packlist_pdf">
								<div class="col-md-12">
									<div class="panel panel-info">
										<div class="panel-heading">
											<h5><span class="glyphicon glyphicon-file"></span> Packing List</h5>	
										</div>
										<div class="panel-body">
											<div class="col-xs-4 text-center">
												<div id="link_descargar_packlist_pdf"></div>
											</div>
											<div class="col-xs-4 text-center">
												<div id="link_eliminar_packlist_pdf"></div>
											</div>
											<div class="col-xs-4 text-center">
												<a href="javascript:void(0);" onclick="mostrar_fileupload_factura('packing'); return false;" class="btn btn-primary" title="Subir Packing List">
												<span class="glyphicon glyphicon-cloud-upload"></span>  <span class="hidden-sm hidden-md hidden-xs">Subir Packing List</span><span class="hidden-lg hidden-xs">Subir</span>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div id="div_fileupload_certori_select">
								<div class="col-md-12">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">Certificado Origen</div>
											<select id="sel_cert_origen" class="form-control" style="width: 100%;" onchange="seleccionar_certificados_origen_archivo(); return false;"></select>
											<span class="input-group-btn">
												<button id="btn_subir_archivo_certificado" type="button" class="btn btn-primary" OnClick="javascript:subir_certificado_origen_factura(); return false;"><span class="glyphicon glyphicon-cloud-upload"></span> Subir Certificado</button>
											</span>
										</div>
									</div>
								</div>
								<div class="col-md-12 text-center">
									<div id="descargar_certificado_origen"></div>
								</div>
							</div>
							<div id="div_fileupload_certori_pdf">
								<div class="col-md-12">
									<div class="form-group">
										<label class="control-label">Certificado Origen (pdf)</label>
										<input id="upload_certificado" type="file" class="file-loading" data-show-upload="false" data-allowed-file-extensions='["pdf"]'>
									</div>
								</div>
							</div>
							<div id="div_descargar_certorit_pdf">
								<div class="col-md-12">
									<div class="panel panel-info">
										<div class="panel-heading">
											<h5><span class="glyphicon glyphicon-file"></span> Certificado Origen</h5>	
										</div>
										<div class="panel-body">
											<div class="col-xs-4 text-center">
												<div id="link_descargar_certori_pdf"></div>
											</div>
											<div class="col-xs-4 text-center">
												<div id="link_eliminar_certori_pdf"></div>
											</div>
											<div class="col-xs-4 text-center">
												<a href="javascript:void(0);" onclick="mostrar_fileupload_factura('certificado'); return false;" class="btn btn-primary" title="Subir Certificado Origen">
												<span class="glyphicon glyphicon-cloud-upload"></span>  <span class="hidden-sm hidden-md hidden-xs">Subir Certificado Origen</span><span class="hidden-lg hidden-xs">Subir</span>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div id="div_fileupload_tickbas_pdf">
								<div class="col-md-12">
									<div class="form-group">
										<label class="control-label">Ticket de bascula (pdf)</label>
										<input id="upload_ticket_bascula" type="file" class="file-loading" data-show-upload="false" data-allowed-file-extensions='["pdf"]'>
									</div>
								</div>
							</div>
							<div id="div_descargar_tickbas_pdf">
								<div class="col-md-12">
									<div class="panel panel-info">
										<div class="panel-heading">
											<h5><span class="glyphicon glyphicon-file"></span> Ticket Bascula</h5>	
										</div>
										<div class="panel-body">
											<div class="col-xs-4 text-center">
												<div id="link_descargar_tickbas_pdf"></div>
											</div>
											<div class="col-xs-4 text-center">
												<div id="link_eliminar_tickbas_pdf"></div>
											</div>
											<div class="col-xs-4 text-center">
												<a href="javascript:void(0);" onclick="mostrar_fileupload_factura('ticketbas'); return false;" class="btn btn-primary" title="Subir Ticket Bascula">
													<span class="glyphicon glyphicon-cloud-upload"></span>  <span class="hidden-sm hidden-md hidden-xs">Subir Ticket Bascula</span><span class="hidden-lg hidden-xs">Subir</span>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_modal_factura"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer bg-factura">
						<button id="btn_guardar_factura_mdl" onclick="guardar_factura(); return false;" type="button" class="btn btn-success"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar</button>
					</div>
				</div>
			</div>
		</div>
		<!-- MODAL CONFIRM -->
		<div id="modalconfirm" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span class="glyphicon glyphicon-trash"></span> Eliminar Factura</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-warning" style="margin-bottom:0px;">
									Esta seguro que desea eliminar permanentemente la informaci&oacute;n de la factura?.
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button data-dismiss="modal" type="button" class="btn btn-danger pull-left"><i class="fa fa-ban"></i> NO</button>
						<button onclick="eliminar_factura_aceptar();" type="button" class="btn btn-success"><i class="fa fa-check"></i> SI</button>
					</div>
				</div>
			</div>
		</div>
		<!-- MODAL CONFIRM CRUCE-->
		<div id="modalconfirm_cruce" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span class="glyphicon glyphicon-trash"></span> Eliminar Cruce</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-warning" style="margin-bottom:0px;">
									Esta seguro que desea eliminar la informaci&oacute;n del cruce?.
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button data-dismiss="modal" type="button" class="btn btn-danger pull-left"><i class="fa fa-ban"></i> NO</button>
						<button onclick="ajax_eliminar_cruce_expo();" type="button" class="btn btn-success"><i class="fa fa-check"></i> SI</button>
					</div>
				</div>
			</div>
		</div>
		<!-- MODAL ESTADO SOIA-->
		<div id="modalestados" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span class="glyphicon glyphicon-flag"></span> Estados en Aduana</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div id="modal_estado_detalle_soia"></div>
								</div>							
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- MODAL CONFIRM PERMISO-->
		<div id="modalconfirm_permiso" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span class="glyphicon glyphicon-trash"></span> Eliminar Permiso</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="alert alert-warning" style="margin-bottom:0px;">
									Esta seguro que desea eliminar permanentemente este permiso?.
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button data-dismiss="modal" type="button" class="btn btn-danger pull-left"><i class="fa fa-ban"></i> NO</button>
						<button onclick="eliminar_permiso_aceptar();" type="button" class="btn btn-success"><i class="fa fa-check"></i> SI</button>
					</div>
				</div>
			</div>
		</div>
		<!-- SUBIR PEDIMENTO -->
		<div id="modalsubirpedimento" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Subir Pedimento Simplificado</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia</div>
										<input id="txt_numero_referencia_upped" type="text" class="form-control text-uppercase" placeholder="" disabled>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Remesa</div>
										<input id="txt_numero_remesa_upped" type="text" class="form-control text-uppercase" placeholder="" disabled>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label class="control-label">Pedimento Simplificado (pdf)</label>
									<input id="upload_pedimento" type="file" class="file-loading" data-show-upload="false" data-allowed-file-extensions='["pdf"]'>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_mdl_subir_pedimento"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button onclick="ajax_guardar_documento_pedimento();" type="button" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar</button>
					</div>
				</div>
			</div>
		</div>
		<!-- IMPRIMIR DOCUMENTACION CRUCE-->
		<div id="modal_PDF_Imprimir" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header bg_impdoc">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Unificar Documentaci&oacute;n en PDF</h4>
					</div>
					<div class="modal-body bg-impdoc-body">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="table-responsive">
										<table id="tbl_facimpr" class="table table-condensed" cellspacing="0" width="100%">
											<thead style="background-color:#3071AA; color:#FFF;">
												<tr>
													<th class="def_app_center">Numero_Factura</th>
													<th class="def_app_center"><i class="fa fa-print" aria-hidden="true"></i> Copias_Factura</th>
													<th>Factura</th>
													<th class="def_app_center"><i class="fa fa-print" aria-hidden="true"></i> Copias_CFDI</th>
													<th>CFDI</th>
													<th class="def_app_center"><i class="fa fa-print" aria-hidden="true"></i> Copias_Pcaking</th>
													<th>Packing_List</th>
													<th class="def_app_center"><i class="fa fa-print" aria-hidden="true"></i> Copias_Certificado</th>
													<th>Certificado_Origen</th>
													<th class="def_app_center"><i class="fa fa-print" aria-hidden="true"></i> Copias_Ticket</th>
													<th>Ticket_Bascula</th>
													<th class="def_app_center"><i class="fa fa-print" aria-hidden="true"></i> Copias_Aviso</th>
													<th>Aviso_Automatico</th>
													<th class="def_app_center"><i class="fa fa-print" aria-hidden="true"></i> Copias_Adhesion</th>
													<th>Aviso_Adhesion</th>
													<th>Acciones</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div id="div_copias_documentos" class="row" style="display:none;">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="panel panel-info">
										<div class="panel-heading">
											<h5><i class="fa fa-print" aria-hidden="true"></i> Copias de cada documento [Factura: <span id="lbl_titulo_copias_factura"></span>]</h5>	
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-xs-8">
													<div class="form-group text-center" style="border-bottom:1px solid #333;">
														<label>ARCHIVO</label>
													</div>
												</div>
												<div class="col-xs-4">
													<div class="form-group text-center" style="border-bottom:1px solid #AAA;">
														<label>COPIAS</label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-8">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Factura</div>
															<input id="txt_numero_factura_impr_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
														</div>
													</div>
												</div>
												<div class="col-xs-4">
													<div class="form-group">
														<input id="txt_numero_factura_impr_mdl_copias" type="text" class="form-control text-center integer" placeholder="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-8">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">CFDI</div>
															<input id="txt_cfdi_impr_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
														</div>
													</div>
												</div>
												<div class="col-xs-4">
													<div class="form-group">
														<input id="txt_cfdi_impr_mdl_copias" type="text" class="form-control text-center integer" placeholder="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-8">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Packing List</div>
															<input id="txt_packing_impr_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
														</div>
													</div>
												</div>
												<div class="col-xs-4">
													<div class="form-group">
														<input id="txt_packing_impr_mdl_copias" type="text" class="form-control text-center integer" placeholder="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-8">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Certificado Origen</div>
															<input id="txt_certificado_impr_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
														</div>
													</div>
												</div>
												<div class="col-xs-4">
													<div class="form-group">
														<input id="txt_certificado_impr_mdl_copias" type="text" class="form-control text-center integer" placeholder="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-8">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Ticket Bascula</div>
															<input id="txt_ticket_impr_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
														</div>
													</div>
												</div>
												<div class="col-xs-4">
													<div class="form-group">
														<input id="txt_ticket_impr_mdl_copias" type="text" class="form-control text-center integer" placeholder="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-8">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Aviso Autom&aacute;tico</div>
															<input id="txt_permiso_impr_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
														</div>
													</div>
												</div>
												<div class="col-xs-4">
													<div class="form-group">
														<input id="txt_permiso_impr_mdl_copias" type="text" class="form-control text-center integer" placeholder="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-8">
													<div class="form-group">
														<div class="input-group">
															<div class="input-group-addon">Aviso Adhesi&oacute;n</div>
															<input id="txt_adhesion_impr_mdl" type="text" class="form-control text-uppercase" placeholder="" disabled>
														</div>
													</div>
												</div>
												<div class="col-xs-4">
													<div class="form-group">
														<input id="txt_adhesion_impr_mdl_copias" type="text" class="form-control text-center integer" placeholder="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-12">
													<div id="mensaje_guardar_copias_impresion"></div>
												</div>
											</div>
											<div class="row">
												<div class="col-xs-6 text-left">
													<button onclick="cancelar_copias_documentos_factura();" type="button" class="btn btn-danger"><i class="fa fa-ban" aria-hidden="true"></i> Cancelar</button>
												</div>
												<div class="col-xs-6 text-right">
													<button onclick="guardar_copias_documentos_factura();" type="button" class="btn btn-primary"><i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar</button>
												</div>
											</div>
											
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_mdl_impresion_documentos"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer bg_impdoc">
						<button id="btn_guardar_cruce" onclick="generar_pdf_unico_impresion_cruce();" type="button" class="btn btn-info"><i class="fa fa-cogs" aria-hidden="true"></i> Generar PDF</button>
					</div>
				</div>
			</div>
		</div>
		<!-- ASIGNAR REFERENCIAS -->
		<div id="modalasigreferencias" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header" style="border-top:solid 5px #A8FB9C;">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-list" aria-hidden="true"></i> Asignar Referencias</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia</div>
										<input id="txt_referencia_asigreffac_mdl" type="text" class="form-control text-uppercase" placeholder="" maxlength="10" />
										<span class="input-group-btn">
											<button id="btn_asignar_referencia_sel" onclick="ajax_asignar_referencia_factura('sel'); return false;" type="button" class="btn btn-info"><i class="fa fa-check" aria-hidden="true"></i> Asignar Seleccionadas</button>
											<button id="btn_asignar_referencia_tod" onclick="ajax_asignar_referencia_factura('todo'); return false;" type="button" class="btn btn-primary"><i class="fa fa-list-ul" aria-hidden="true"></i> Asignar Todas</button>
											<button id="btn_generar_referencia_factura" onclick="generar_nueva_referencia_expo('asifac'); return false;" type="button" class="btn btn-primary"><i class="fa fa-cogs" aria-hidden="true"></i> Generar Referencia</button>
										</span>
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<div id="mensaje_asigreffac_modal"></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Pedimento</div>
										<input type="text" id="txt_pedimento_asigreffac_mdl" class="form-control" value="" placeholder="" disabled>
										<span class="input-group-btn">
											<button id="btn_generar_pedimento_asigref" onclick="generar_nuevo_pedimento_referencia(); return false;" type="button" class="btn btn-primary"><i class="fa fa-cogs" aria-hidden="true"></i> Generar Pedimento</button>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group text-right">
									<label><span id="lbl_facturas_sel"></span> Seleccionadas</label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group text-left">
									<div class="table-responsive">
										<table id="table_facasigref" class="table table-condensed table-hover" cellspacing="0" width="100%">
											<thead style="background-color:#3071AA; color:#FFF;">
												<tr>
													<th>#</th>
													<th>N&uacute;mero_Factura</th>
													<th>Referencia</th>
													<th>UUID</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_mdl_generar_pedimento_ref"></div>
							</div>
						</div>
					</div>
					<!--div class="modal-footer">
						<button onclick="ajax_generar_nuevo_pedimento_ref();" type="button" class="btn btn-success"><i class="fa fa-cogs" aria-hidden="true"></i> Generar Pedimento</button>
					</div-->
				</div>
			</div>
		</div>
		<!-- GENERAR PEDIMENTO -->
		<div id="modalgenpedimento" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header" style="border-top:solid 5px #A8FB9C;">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-file-text-o" aria-hidden="true"></i> Generar N&uacute;mero de Pedimento</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-6 col-md-4 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Año</div>
										<select id="sel_pedimento_mod_anio" class="form-control select2">
										</select>
									</div>
								</div>
							</div>
							<div class="col-xs-6 col-md-8 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Patente</div>
										<select id="sel_pedimento_mod_patente" class="form-control select2">
											<option value="1664" selected>1664 - HUGO NISHIYAMA DE LA GARZA</option>
											<option value="3483">3483 - MANUEL JOSE ESTANDIA FERNANDEZ</option>
										</select>
									</div>
								</div>					
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Cliente</div>
										<select id="sel_pedimento_mod_cliente_casa" class="form-control select2">
											<?php
												include('../connect_casa.php');
												$qCasa = "SELECT a.CVE_IMP, a.NOM_IMP 
															FROM CTRAC_CLIENT a
															ORDER BY a.NOM_IMP ";
												$resped = odbc_exec ($odbccasa, $qCasa);
												if ($resped == false){
													echo '<option value="">Error al consultar los clientes. BD.CASA.'.odbc_error().'</option>';
												}else{
													while(odbc_fetch_row($resped)){
														echo '<option value="'.odbc_result($resped,"CVE_IMP").'">'.utf8_decode(odbc_result($resped,"NOM_IMP")).'</option>';
													}
												}
											?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">R&eacute;gimen</div>
										<select id="sel_pedimento_mod_regimen" class="form-control select2" style="width: 100%;"></select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Descripci&oacute;n de la Mercanc&iacute;a</div>
										<input id="txt_pedimento_mod_desc_merc" type="text" class="form-control text-uppercase" placeholder="" maxlength="200">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_mdl_generar_pedimento_ref"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button onclick="ajax_generar_nuevo_pedimento_ref();" type="button" class="btn btn-success"><i class="fa fa-cogs" aria-hidden="true"></i> Generar Pedimento</button>
					</div>
				</div>
			</div>
		</div>
		<!-- MODAL CONFIRMAR GENERAR NUEVA REFERENCIA -->
		<div id="modalconfirm_genreferencia" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="border-top:solid 5px #E56060;">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> La referencia no existe.</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-2 text-center">
								<i class="fa fa-question-circle-o fa-4x" aria-hidden="true"></i>
							</div>
							<div class="col-xs-10">
								<div class="alert alert-warning" style="margin-bottom:0px;">
									La referencia que desea vincular no existe, desea generar una nueva referencia de exportaci&oacute;n?.
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button data-dismiss="modal" type="button" class="btn btn-danger pull-left"><i class="fa fa-ban"></i> NO</button>
						<button onclick="generar_nueva_referencia_expo();" type="button" class="btn btn-success"><i class="fa fa-check"></i> SI</button>
					</div>
				</div>
			</div>
		</div>
		<!-- GENERAR REFERENCIA -->
		<div id="modalgenreferencia" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header" style="border-top:solid 5px #8CD58F;">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-cogs" aria-hidden="true"></i> Generar Nueva Referencia de Exportaci&oacute;n</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-4">
								<div class="form-group">
									<label class="switch switch-green">
									  <input id="chk_rectificacion_genref" type="checkbox" class="switch-input">
									  <span class="switch-label" data-on="Si" data-off="No"></span>
									  <span class="switch-handle"></span>
									</label>&nbsp;&nbsp;<label>Rectificacion</label>
								</div>
							</div>
						</div>
						<div id="div_referencia_rectificacion_genrefexpo" class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia Original</div>
										<input type="text" id="txt_referencia_anterior" class="form-control" value="" placeholder="En caso de rectificación ingrese la referencia original" maxlength="10">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_mdl_generar_ref_expo"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button onclick="ajax_generar_nueva_referencia_expo();" type="button" class="btn btn-success"><i class="fa fa-cogs" aria-hidden="true"></i> Generar Referencia</button>
					</div>
				</div>
			</div>
		</div>
		<!-- Asignar Facturas CASA -->
		<div id="modal_asigfac_casa" class="modal modal-danger">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header bg-factura">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-link" aria-hidden="true"></i> Vincular Facturas con Sistema CASA</h4>
					</div>
					<div class="modal-body bg-factura-body">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div id="div_facturas_vincular"></div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<div id="mensaje_mld_asignar_facturas_casa"></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id=""></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Seleccionar Factura CASA -->
		<div id="modal_selfaccasa" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header" style="border-top:solid 5px #8CD58F;">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-cogs" aria-hidden="true"></i> Vincular Factura CASA</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia</div>
										<input id="txt_mdl_referencia_selfac_casa" type="text" class="form-control text-uppercase" placeholder="" disabled>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Factura Cruce</div>
										<input id="txt_mdl_factura_selfac_casa" type="text" class="form-control text-uppercase" placeholder="" disabled>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Factura Casa</div>
										<select id="sel_facturas_casa" class="form-control select2" style="width: 100%;">
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_mdl_selfac_casa"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button onclick="ajax_asignar_factura_casa();" type="button" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar</button>
					</div>
				</div>
			</div>
		</div>
		<!-- MODAL CONFIRM SEGUIR CON SALIDA-->
		<div id="modalconfirmsalida" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Aviso Informativo</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div id="mensaje_confirmar_salida" class="alert alert-warning" style="margin-bottom:0px;">
									
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button data-dismiss="modal" type="button" class="btn btn-danger pull-left"><i class="fa fa-ban"></i> NO</button>
						<button onclick="continuar_salidas_confirmacion();" type="button" class="btn btn-success"><i class="fa fa-check"></i> SI</button>
					</div>
				</div>
			</div>
		</div>
		<!-- MODAL PARTIDAS CRUCE PLANTILLA 5 -->
		<div id="modal_plantilla" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header bg-plantilla">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Plantilla General Avanzada 5</h4>
					</div>
					<div class="modal-body bg-plantilla-body">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group text-left">
									<div class="input-group">
										<div class="input-group-addon">Destinatario</div>
										<select id="sel_proveedor_plantilla" class="form-control select2" style="width: 100%;">
										<?php
											//global $odbccasa;
											$qCasa = "SELECT a.CVE_PRO, a.NOM_PRO
														FROM CTRAC_DESTIN a
														ORDER BY  a.NOM_PRO";

											$resped = odbc_exec ($odbccasa, $qCasa);
											if ($resped == false){
												echo '<option value="">'.odbc_error().'</option>';
											}else{
												echo '<option value="">[SELECCIONAR DESTINATARIO]</option>';
												while(odbc_fetch_row($resped)){					
													$id_proveedor = odbc_result($resped,"CVE_PRO");
													$nombre = odbc_result($resped,"NOM_PRO");
													echo '<option value="'.$id_proveedor.'">'.$nombre.'</option>';
												}
											}
										?>
										</select>
										<!--span class="input-group-btn">
											<button onclick="asignar_proveedor_factura('todo'); return false;" type="button" class="btn btn-primary"><i class="fa fa-list-ul" aria-hidden="true"></i> Asignar Todas</button>										
										</span-->
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div id="div_facturas_plantilla_cruces"></div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group text-left">
									<div class="input-group">
										<div class="input-group-addon">Receptor</div>
										<textarea class="form-control" id="txt_receptor_factura_plantilla" name="" rows="3" readonly></textarea>
										<!--label><span id="lbl_receptor_factura_plantilla"></span></label-->
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label class="switch switch-green">
									<input id="chk_usar_uuid_plantilla" type="checkbox" class="switch-input">
									<span class="switch-label" data-on="SI" data-off="NO"></span>
									<span class="switch-handle"></span>
									</label>&nbsp;&nbsp;<label>Utilizar UUID como <strong>N&uacute;mero De Factura</strong></label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_modal_plantilla_5"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group text-left">
									<div class="table-responsive">
										<table id="tbl_plantilla" class="table table-condensed table-hover" cellspacing="0" width="100%"  style="background-color:#FFF;">
											<thead style="background-color:#3071AA; color:#FFF;">
												<tr>
													<th>Proveedor</th>
													<th>No. Factura</th>
													<th>Fecha factura</th>
													<th>Monto factura</th>
													<th>Moneda</th>
													<th>Incoterm</th>
													<th>Subdivision</th>
													<th>Certificado Origen</th>
													<th>Numero parte</th>
													<th>Pais Origen	</th>
													<th>Pais Vendedor</th>
													<th>Fraccion</th>
													<th>Descripcion</th>
													<th>Precio partida</th>
													<th>UMC</th>
													<th>Cantidad UMC (Cantidad factura)</th>
													<th>Cantidad UMT (Cantidad fisica)</th>
													<th>Preferencia arancelaria</th>
													<th>Marca</th>
													<th>Modelo</th>
													<th>Submodelo</th>
													<th>Serie</th>
													<th>Descripcion COVE</th>
													<th>Referencia</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 text-center">
								<div id="mensaje_modal_generar_plantilla_5"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button onclick="ajax_generar_plantilla_5();" type="button" class="btn btn-success"><i class="fa fa-cogs" aria-hidden="true"></i> Generar Plantilla</button>
					</div>
				</div>
			</div>
		</div>
		<!-- MODAL SELECCIONAR CRUCES PLANTILLA AVANZADA 5 -->
		<div id="modal_cruces_plantilla" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header bg-cruce">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-check-square"></i> Seleccionar Cruces</h4>
					</div>
					<div class="modal-body bg-cruce-body">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group text-left">
									<!--div class="table-responsive"-->
									<!--div class="dataTable_wrapper"-->
										<table id="rpt_cruces_plantilla" class="table table-hover tb_pointer" cellspacing="0" >
											<thead style="background-color:#3071AA; color:#FFF;">
												<tr>
													<th>Id</th>
													<th>Cliente</th>
													<th>Aduana</th>
													<th>Numero_Caja</th>
													<th>Fecha_Registro</th>
													<th>Linea_Transportista</th>
													<th>Transfer</th>
													<th>PO_Number</th>
													<th>Entregar</th>
												</tr>
											</thead>
										</table>
									<!--/div-->
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_mdl_cruces_pantilla"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer bg-cruce">
						<!--button id="modalconfirm_btn_cancel" type="button" class="btn btn-danger pull-left"><span class="glyphicon glyphicon-ban-circle"></span> Cancelar</button-->
						<button id="btn_guardar_cruce" onclick="seleccionar_cruces_plantilla_avanzada_5();" type="button" class="btn btn-success"><i class="fa fa-check"></i> Seleccionar</button>
					</div>
				</div>
			</div>
		</div>
		
		<?php include('nav.php');?>
		
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<h2><strong><i class="fa fa-road" aria-hidden="true"></i> Cruces de Exportaci&oacute;n.</strong></h2> 
				</div>
				<div id="idiv_panel_principal" class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<div id="mensaje_cruces"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 text-center">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Cliente</div>
									<select class="form-control text-uppercase" onchange="actualiza_grid_cruces()" id="selecliente">
										<option value="todo">TODOS</option>
										<?php
											global $cmysqli;
											$consulta="SELECT cnombre,gcliente FROM cltes_expo order by cnombre";
											$query = mysqli_query($cmysqli,$consulta);
											$number = mysqli_num_rows($query);
											if($number > 1){
												while($row = mysqli_fetch_array($query)){
													echo '<option value="'.$row['gcliente'].'">'.$row['cnombre'].'</option>';
												}
											}
										?>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6 text-center">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Estado</div>
									<select class="form-control text-uppercase" data-style="btn-primary" onchange="actualiza_grid_cruces()" id="selestado">
										<option value="todo">TODOS</option>
										<option value="pnd" selected>PENDIENTES</option>
										<option value="prc">PROCESADOS</option>
										<option value="cum">CUMPLIDOS</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="tbl_cruces" class="table table-bordered table-hover" width="100%">
										<thead>
											<tr>
												<th style="width:50px">#</th>
												<th>Fecha</th>
												<th style="width:350px">Cliente</th>
												<th style="width:350px">Aduana</th>
												<th>Facturas</th>
												<th>Estado_Aduana_MX</th>
												<th>Estado_Documentaci&oacute;n</th>
												<th>PO_Number</th>
												<th>Linea_Transportista</th>
												<th>Tipo_Salida</th>
												<th>Contenedor</th>
												<th>Transfer</th>
												<th>Entregar</th>
												<th>Pedimentos</th>
												<th>Salidas</th>
												<th>Acciones</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div class="row"><div class="col-xs-12">&nbsp;</div></div>
					<div class="row">
						<div class="col-xs-1">
							<label class="label label-warning"><i class="glyphicon glyphicon-edit"></i></label>
						</div>
						<div class="col-xs-11">
							<strong>EN PROCESO DE CAPTURA: </strong> En este estado se encuentran todos aquellos cruces que se están capturando actualmente en el sistema de pedimentos.
						</div>
					</div>
					<div class="row"><div class="col-xs-12">&nbsp;</div></div>
					<div class="row">
						<div class="col-xs-1">
							<label class="label label-success"><i class="glyphicon glyphicon-ok"></i></label>
						</div>
						<div class="col-xs-11">
							<strong>PROCESADO: </strong> Todos los cruces con este estado, ya se encuentran listos con la documentación para ser despachados en aduana. En caso de que estos ya se encuentren despachados, se habilitara información de la aduana mexicana.
						</div>
					</div>
					<div class="row"><div class="col-xs-12">&nbsp;</div></div>
					<div class="row">
						<div class="col-xs-1">
							<div style="background-color:#E87F7F; color:#FFF; width:20px;">&nbsp;</div>
						</div>
						<div class="col-xs-11">
							<strong>PENDIENTES: </strong> Son todos aquellos cruces que no cuenten con una salida de Del Bravo.
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
	<script src="../bower_components/datatables.net-fixedcolumns/js/dataTables.fixedColumns.min.js"></script>
	
	<!--script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	<script src="../editor/js/editor.selectize.js"></script-->
    <script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]-->
	<!-- boostrapselect JavaScript -->
	<!--<script src="../bower_components/bootstrap-select/js/bootstrap-select.js"></script>-->
	<!-- Select2 JavaScript -->
	<script src="../bower_components/select2/dist/js/select2.min.js"></script>
	<script src="../bower_components/select2/dist/js/i18n/es.js"></script>
	<!-- Fileinput JS -->
	<!--script type="text/javascript" language="javascript" src="http://plugins.krajee.com/assets/24b9d388/js/plugins/purify.min.js"></script-->
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/fileinput.min.js"></script>
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/locales/es.js"></script>
	<!-- Bootstrap Datepicker -->
	<script src="../bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
	<script src="../bower_components/bootstrap-datepicker/dist/locales/bootstrap-datepicker.es.min.js"></script>
	<!--TouchSpin-->
	<script  type="text/javascript" language="javascript" src="../plugins/touchspin/jquery.bootstrap-touchspin.js"></script>
	<!--Numeric-->
	<script  type="text/javascript" language="javascript" src="../plugins/numeric/jquery.numeric.js"></script>
	<!--JS principal-->
	<script src="../js/cruces_exportacion.js?v=2018.07.19.1000"></script>
</body>

</html>
