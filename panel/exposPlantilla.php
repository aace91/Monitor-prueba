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
	
    <title>Exportaciones - Salidas</title>

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

	<!--TouchSpin-->
    <link rel="stylesheet" href="../plugins/touchspin/jquery.bootstrap-touchspin.css">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	<style>
		#idiv_panel_principal { display: block; }
		#idiv_panel_secundario { display: none; }
		#idiv_principal_agregar_emb { display: none; }
	
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
		
		a:hover {
			cursor:pointer;
		}
	</style>
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php');?>
		
		<!-- MODAL INFO -->
		<div id="modal_info" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-body">	
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_info_mensaje"></div>
							</div>
							<div class="col-md-12">
								<div id="idiv_modal_info_mensaje_registros"></div>
							</div>
						</div>
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

		<!-- MODAL SUBIR EXCEL -->
		<div id="modal_subir_excel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Procesar Documento</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Referencia</span>
										<input id="itxt_modal_subir_excel_referencia" class="form-control text-uppercase" type="text" maxlength="15">
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<label id="ilabel_modal_subir_excel_verify" class="control-label" style="padding-top: 6px; padding-bottom: 6px;"></label>
									<label id="ilabel_modal_subir_excel_clave_ped" class="control-label pull-right" style="padding-top: 6px; padding-bottom: 6px;"></label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<label class="control-label"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Seleccione Archivo Excel</label>
									<input id="ifile_modal_subir_excel_archivo" type="file" class="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div id="idiv_modal_subir_excel_mensaje" style="display:none;"></div>
							</div>
						</div>
					</div>
					
					<div class="modal-footer">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group pull-right">	
										<button id="ibtn_modal_subir_excel_subir" type="button" class="btn btn-info" onClick="javascript:fcn_modal_subir_excel_upload();"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Subir</button>
									</div>	
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- MODAL EDITAR REGISTRO -->
		<div id="modal_edit_reg" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Editar Registro</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Clave Proveedor:</div>
										<input id="itxt_modal_edit_reg_cve_prov" class="form-control text-uppercase" type="text" maxlength="6" disabled="disabled">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Factura:</div>
										<input id="itxt_modal_edit_reg_factura" class="form-control text-uppercase" type="text" maxlength="15">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Fecha:</div>
										<input id="itxt_modal_edit_reg_fecha" class="form-control text-uppercase" type="text">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Monto:</div>
										<input id="itxt_modal_edit_reg_monto" class="form-control" type="text" maxlength="26">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Modena:</div>
										<input id="itxt_modal_edit_reg_moneda" class="form-control text-uppercase" type="text" maxlength="3">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Incoterm:</div>
										<input id="itxt_modal_edit_reg_incoterm" class="form-control text-uppercase" type="text" maxlength="3">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Subdivisi&oacute;n:</div>
										<input id="itxt_modal_edit_reg_subdivision" class="form-control text-uppercase" type="text" maxlength="1">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Cer Origen:</div>
										<input id="itxt_modal_edit_reg_certificado" class="form-control text-uppercase" type="text" maxlength="1">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">No Parte:</div>
										<input id="itxt_modal_edit_reg_no_parte" class="form-control text-uppercase" type="text" maxlength="50">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Origen:</div>
										<input id="itxt_modal_edit_reg_origen" class="form-control text-uppercase" type="text" maxlength="3">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Vendedor:</div>
										<input id="itxt_modal_edit_reg_vendedor" class="form-control text-uppercase" type="text" maxlength="3">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Fracci&oacute;n:</div>
										<input id="itxt_modal_edit_reg_fraccion" class="form-control text-uppercase" type="text" maxlength="8">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-12 col-md-8">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Descripci&oacute;n:</div>
										<input id="itxt_modal_edit_reg_descripcion" class="form-control text-uppercase" type="text" maxlength="250">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Precio Partida:</div>
										<input id="itxt_modal_edit_reg_precio_partida" class="form-control" type="text" maxlength="26">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">UMC:</div>
										<input id="itxt_modal_edit_reg_umc" class="form-control text-uppercase" type="text" maxlength="2">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">UMC (Factura):</div>
										<input id="itxt_modal_edit_reg_cantidad_umc" class="form-control" type="text" maxlength="26">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">UMT (F&iacute;sica):</div>
										<input id="itxt_modal_edit_reg_cantidad_umt" class="form-control" type="text" maxlength="26">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Preferencia:</div>
										<input id="itxt_modal_edit_reg_preferencia" class="form-control text-uppercase" type="text" maxlength="2">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Marca:</div>
										<input id="itxt_modal_edit_reg_marca" class="form-control text-uppercase" type="text" maxlength="100">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Modelo:</div>
										<input id="itxt_modal_edit_reg_modelo" class="form-control text-uppercase" type="text" maxlength="50">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Submodelo:</div>
										<input id="itxt_modal_edit_reg_submodelo" class="form-control text-uppercase" type="text" maxlength="50">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-6 col-md-4">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Serie:</div>
										<input id="itxt_modal_edit_reg_serie" class="form-control text-uppercase" type="text" maxlength="50">
									</div>	
								</div>				
							</div>
							<div class="col-xs-12 col-sm-12 col-md-8">
								<div class="form-group">
									<div class="input-group">	
										<div class="input-group-addon">Descrici&oacute;n Cove:</div>
										<input id="itxt_modal_edit_reg_descripcion_cove" class="form-control text-uppercase" type="text" maxlength="250">
									</div>	
								</div>				
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>
						<button type="button" class="btn btn-success pull-right" onClick="ajax_set_editar_registro();"><i class="fa fa-check-circle"></i> Guardar</button>
					</div>
				</div>
			</div>
		</div>


		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Embarques</strong> <small></small>
				</div>

				<div class="col-xs-12">
					<div id="idiv_message" style="display:none;"></div>
				</div>

				<div id="idiv_panel_principal" class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<div id="idiv_principal_message" style="display:none;"></div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12 col-sm-8 col-md-6">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon">Referencia:</span>
									<input id="itxt_principal_referencia" class="form-control text-uppercase" type="text" maxlength="15">
									<span class="input-group-btn">
										<button id="ibtn_buscar" type="button" class="btn btn-info" onclick="fcn_cargar_grid_principal_general();"><i class="fa fa-search"></i> Buscar</button>
									</span>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-4 col-md-6">
							<div class="form-group pull-right">
								<button id="ibtn_nuevo" type="button" class="btn btn-success" onClick="javascript:fcn_modal_show_subir_excel();"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Nuevo Documento</button>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dt_principal_general" class="table table-striped table-bordered" width="99.8%">
										<thead>
											<tr>
												<th class="def_app_center" style="width:120px;">EMBARQUE</th>
												<th class="def_app_center" style="width:120px;">FECHA</th>
												<th class="def_app_center" style="width:120px;">REGISTROS</th>
												<th class="def_app_center">OPCIONES</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th class="def_app_center" style="width:120px;">EMBARQUE</th>
												<th class="def_app_center" style="width:120px;">FECHA</th>
												<th class="def_app_center" style="width:120px;">REGISTROS</th>
												<th class="def_app_center">OPCIONES</th>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
					</div>
						
					<div id="idiv_principal_agregar_emb" class="row">
						<div class="col-xs-12">
							<button id="ibtn_principal_agregar_emb" type="button" class="btn btn-success" onClick="javascript:fcn_modal_show_subir_excel('agregar');"><i class="fa fa-plus" aria-hidden="true"></i> Agregar Embarque</button>
						</div>
					</div>
				</div>

				<div id="idiv_panel_secundario" class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<div id="idiv_secundario_message" style="display:none;"></div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-9 col-sm-6 col-md-4">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon">Referencia:</span>
									<input id="itxt_secundario_referencia" class="form-control" type="text" maxlength="7" disabled="disabled">
								</div>
							</div>
						</div>
						<div class="col-xs-3 col-sm-6 col-md-8">
							<div class="form-group pull-right">
								<button type="button" class="btn btn-info" onClick="javascript:fcn_secundario_regresar();"><i class="fa fa-reply" aria-hidden="true"></i> Atr&aacute;s</button>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dt_secundario_detalles" class="table table-striped table-bordered" width="99.8%">
										<thead>
											<tr>
												<th class="def_app_center" style="width:20px;"></th>
												<th class="def_app_center" style="width:50px;">CVE PROV</th>
												<th class="def_app_center" style="width:80px;"># FACTURA</th>
												<th class="def_app_center" style="width:120px;">FECHA FACTURA</th>
												<th class="def_app_center" style="width:80px;">MONTO FACTURA</th>
												<th class="def_app_center" style="width:80px;">MONEDA</th>
												<th class="def_app_center" style="width:80px;">INCOTERM</th>
												<th class="def_app_center" style="width:80px;">SUBDIVISI&Oacute;N</th>
												<th class="def_app_center" style="width:80px;">CER ORI</th>
												<th class="def_app_center" style="width:80px;">NO PARTE</th>
												<th class="def_app_center" style="width:80px;">ORIGEN</th>
												<th class="def_app_center" style="width:80px;">VENDEDOR</th>
												<th class="def_app_center" style="width:80px;">FRACCI&Oacute;N</th>
												<th class="def_app_center" style="width:250px;">DESCRIPCI&Oacute;N</th>
												<th class="def_app_center" style="width:80px;">PRECIO PARTIDA</th>
												<th class="def_app_center" style="width:80px;">UMC</th>
												<th class="def_app_center" style="width:80px;">CANT UMC(FACTURA)</th>
												<th class="def_app_center" style="width:80px;">CANT UMT(F&Iacute;SICA)</th>
												<th class="def_app_center" style="width:80px;">PREFERENCIA</th>
												<th class="def_app_center" style="width:80px;">MARCA</th>
												<th class="def_app_center" style="width:80px;">MODELO</th>
												<th class="def_app_center" style="width:80px;">SUBMODELO</th>
												<th class="def_app_center" style="width:80px;">SERIE</th>
												<th class="def_app_center" style="width:250px;">DESCRIPCION COVE</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th class="def_app_center" style="width:20px;"></th>
												<th class="def_app_center" style="width:50px;">CVE PROV</th>
												<th class="def_app_center" style="width:80px;"># FACTURA</th>
												<th class="def_app_center" style="width:120px;">FECHA FACTURA</th>
												<th class="def_app_center" style="width:80px;">MONTO FACTURA</th>
												<th class="def_app_center" style="width:80px;">MONEDA</th>
												<th class="def_app_center" style="width:80px;">INCOTERM</th>
												<th class="def_app_center" style="width:80px;">SUBDIVISI&Oacute;N</th>
												<th class="def_app_center" style="width:80px;">CER ORI</th>
												<th class="def_app_center" style="width:80px;">NO PARTE</th>
												<th class="def_app_center" style="width:80px;">ORIGEN</th>
												<th class="def_app_center" style="width:80px;">VENDEDOR</th>
												<th class="def_app_center" style="width:80px;">FRACCI&Oacute;N</th>
												<th class="def_app_center" style="width:250px;">DESCRIPCI&Oacute;N</th>
												<th class="def_app_center" style="width:80px;">PRECIO PARTIDA</th>
												<th class="def_app_center" style="width:80px;">UMC</th>
												<th class="def_app_center" style="width:80px;">CANT UMC(FACTURA)</th>
												<th class="def_app_center" style="width:80px;">CANT UMT(F&Iacute;SICA)</th>
												<th class="def_app_center" style="width:80px;">PREFERENCIA</th>
												<th class="def_app_center" style="width:80px;">MARCA</th>
												<th class="def_app_center" style="width:80px;">MODELO</th>
												<th class="def_app_center" style="width:80px;">SUBMODELO</th>
												<th class="def_app_center" style="width:80px;">SERIE</th>
												<th class="def_app_center" style="width:250px;">DESCRIPCION COVE</th>
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

	<script src="../js/exposPlantilla.js?v=2017.04.21.1700"></script>
</body>

</html>
