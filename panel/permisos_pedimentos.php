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
	
    <title>Permisos - Monitor De Referencias</title>

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
		
		.verde_btns {background-color:#30AB53;color:#FFF;}
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

		<!-- MODAL AGREGAR PERMISO -->
		<div id="modal_nvo_permiso" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-file-o" aria-hidden="true"></i> Aviso Autom&aacute;tico</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">N&uacute;mero de permiso</span>
										<input id="txt_modal_numero_permiso" class="form-control text-uppercase" type="text" maxlength="20">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Cliente</span>
										<select class="form-control" id="sel_modal_cliente">
											<?php
												require('./../connect_dbsql.php');
												$consulta="SELECT f_numcli,nombre FROM geocel_clientes_expo ORDER BY nombre";
												$query = mysqli_query($cmysqli,$consulta);
												if (!$query) {
													$error=mysqli_error($cmysqli);
													$respuesta['Codigo']=-1;
													$respuesta['Mensaje']= 'Error al consultar los clientes en la base de datos. [bodega.geocel_clientes_expo] ['.$error.']['.$consulta.']';
												} else {
													echo '<option value="0">[Seleccionar Cliente]</option>';
													while($row = mysqli_fetch_array($query)){
														echo '<option value="'.$row['f_numcli'].'" >'.$row['nombre'].'</option>';
													}
												}
											?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Vigencia del:</span>
										<input type="text" id="txt_modal_vig_fechaini" class="form-control" value="" placeholder="dd/mm/yyyy">
										<span class="input-group-addon">al:</span>
										<input type="text" id="txt_modal_vig_fechafin" class="form-control" value="" placeholder="dd/mm/yyyy">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-6">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Valor total del permiso:</span>
										<span class="input-group-addon">$</span>
										<input id="txt_modal_valor_dlls" class="form-control decimal-2-places text-right" type="text" maxlength="14">
										<span class="input-group-addon text-left">dlls</span>
									</div>
								</div>
							</div>
							<div class="col-xs-6">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Cantidad total del permiso:</span>
										<input id="txt_modal_cantidad" class="form-control integer text-right" type="text" maxlength="11">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-6">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Valor a usar en Del Bravo:</span>
										<span class="input-group-addon">$</span>
										<input id="txt_modal_valor_dlls_delbravo" class="form-control decimal-2-places text-right" type="text" maxlength="14">
										<span class="input-group-addon text-left">dlls</span>
									</div>
								</div>
							</div>
							<div class="col-xs-6">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Cantidad a usar en Del Bravo:</span>
										<input id="txt_modal_cantidad_delbravo" class="form-control integer text-right" type="text" maxlength="11">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="ver_archivo_pdf_aviso" style="display:none;">
									
								</div>
								<div id="subir_archivo_aviso" style="display:none;">
									<div class="form-group">
										<label class="control-label">Documento del Aviso Autom&aacute;tico (pdf)</label>
										<input id="upload_aviso_auto" type="file" class="file-loading" data-show-upload="false" data-allowed-file-extensions='["pdf"]'>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<input type="checkbox" id="chb_aviso_adhesion" name="aviso_adhesion" value="coding">
								<label for="coding">Debe existir aviso de adhesi&oacute;n para el uso de este permiso.</label>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div id="idiv_modal_guardar_permiso"></div>
							</div>
						</div>
					</div>					
					<div class="modal-footer">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group pull-right">	
										<button id="ibtn_modal_guardar_permiso" type="button" class="btn btn-success" onClick="javascript:guardar_aviso_automatico();"><i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar</button>
									</div>	
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- MODAL VER ESTADO DEL PEMISO -->
		<div id="modal_estado" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" style="z-index:9999;">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span class="glyphicon glyphicon-list-alt"></span> Estado Del Permiso</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="row">
										<div class="col-md-12">
											<div class="form-group text-center">
												<h4>Número de Permiso: <strong><span id="lbl_numero_permiso"></span></strong></h4>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group text-left">
												<h4>Cliente: <strong><span id="lbl_cliente"></span></strong></h4>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group text-right">
												<h5>Vigencia del: <strong><span id="lbl_fecha_inicial"></span></strong> al <strong><span id="lbl_fecha_final"></span></strong></h5>
											</div>
										</div>
									</div><div class="row"><div class="col-md-12">&nbsp;</div></div>
									<div class="row">
										<div class="col-md-6">
											<div class="panel panel-info">
												<div class="panel-heading">
													<h4><strong><span id="lbl_fecha_inicial">Totales del permiso</span></strong></h4>
												</div>
												<div class="panel-body">
													<div class="row">
														<div class="col-md-12">
															<div class="form-group text-center">
																<h5>Total en dolares: <strong><span id="lbl_valor_total_permiso"></span></strong></h5>
															</div>
														</div>
														<div class="col-md-12">
															<div class="form-group text-center">
																<h5>Total en kilos: <strong><span id="lbl_cantidad_total_permiso"></span></strong></h5>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="panel panel-info">
												<div class="panel-heading">
													<h4><strong><span id="lbl_fecha_inicial">Totales asignados a Del Bravo</span></strong></h4>
												</div>
												<div class="panel-body">
													<div class="row">
														<div class="col-md-12">
															<div class="form-group text-center">
																<h5>Total en dolares: <strong><span id="lbl_valor_total_delbravo"></span></strong></h5>
															</div>
														</div>
														<div class="col-md-12">
															<div class="form-group text-center">
																<h5>Total en kilos: <strong><span id="lbl_cantidad_total_delbravo"></span></strong></h5>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div id="panel_saldo_delbravo" class="panel panel-danger">
												<div class="panel-heading">
													<h4><strong><span id="lbl_fecha_inicial">Saldo del permiso en Del Bravo</span></strong></h4>
												</div>
												<div class="panel-body">
													<div class="row">
														<div class="col-md-6">
															<div class="form-group text-center">
																<h4>Total en dolares: <strong><span id="lbl_valor_saldo"></span></strong></h4>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group text-center">
																<h4>Total en kilos: <strong><span id="lbl_cantidad_saldo"></span></strong></h4>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row"><div class="col-md-12">&nbsp;</div></div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group text-left">
												<h5><strong>Desglose de uso de permiso asignado a Del Bravo</strong></h5>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group text-left">
												<div class="table-responsive">
													<table id="rpt_referencias" class="table table-condensed" cellspacing="0" width="100%">
														<thead style="background-color:#3071AA; color:#FFF;">
															<tr>
																<th>Pedimento</th>
																<th>Referencia</th>
																<th>Importe en dlls. utilizado</th>
																<th>Saldo en dlls.</th>
																<th>Importe en kgs. utilizado</th>
																<th>Saldo en kgs.</th>
															</tr>
														</thead>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="row"><div class="col-md-12">&nbsp;</div></div>
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-info">
											  <strong>NOTA IMPORTANTE:</strong>  LA INFORMACION CONTENIDA EN ESTE REPORTE SOLAMENTE ES VALIDA SI DEL BRAVO HA HECHO USO EXCLUSIVO DE ESTE AVISO.
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
		
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<h2><strong><i class="fa fa-file-powerpoint-o" aria-hidden="true"></i> Permisos de Exportaci&oacute;n</strong></h2> Se enviar&aacute; diariamente un estado del permiso y su utilizaci&oacute;n en cada pedimento.
				</div>

				<div class="col-xs-12">
					<div id="idiv_message" style="display:none;"></div>
				</div>

				<div id="idiv_panel_principal" class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<div id="idiv_principal_message"></div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12">
							<div class="form-group pull-right">
								<button id="ibtn_nuevo" type="button" class="btn btn-success" onClick="javascript:agregar_permiso_pedimento();"><i class="fa fa-file-o" aria-hidden="true"></i> Nuevo Permiso</button>
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
												<th>#</th>
												<th>Número Permiso</th>
												<th style="width:170px;">Vigencia</th>
												<th>Cliente</th>
												<th>Total Permiso Dólares</th>
												<th>Total Permiso Kilos</th>
												<th>Valor Dólares Autorizado</th>
												<th>Valor Kilos Autorizado</th>
												<th>Valor Dólares Utilizado</th>
												<th>Valor Kilos Utilizado</th>
												<th>Saldo Dólares Del Bravo</th>
												<th>Saldo Kilos Del Bravo</th>
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

	<script src="../js/permisos_pedimentos.js?v=2017.11.13.1706"></script>
</body>

</html>
