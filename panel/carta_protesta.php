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
	
    <title>Carta Protesta - Monitor De Referencias</title>

    <!-- Bootstrap Core CSS -->
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- DataTables CSS -->
    <!--link href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-fixedcolumns-bs/css/fixedColumns.bootstrap.min.css" rel="stylesheet"-->
	
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
	
	<!--TextEditor-->
	<link rel="stylesheet" href="../plugins/sceditor/minified/themes/square.min.css" type="text/css" />

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
		
		<div id="contanier_jum" class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<h2><strong><i class="fa fa-file-text-o" aria-hidden="true"></i> Carta Protesta</strong></h2>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<div id="idiv_principal_message" style="display:none;"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon">Referencia</div>
									<input id="txt_referenica_cartaprotesta" type="text" maxlength="10" class="form-control text-uppercase">
									<span id="ibtn_cat_problemas_add" class="input-group-btn">
										<button class="btn btn-info" type="button" onClick="ajax_buscar_referencia_carta();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
									</span>
								</div>
							</div>
						</div>
					</div>
					<div class="row"><div class="col-xs-12">&nbsp;</div></div>
					<div class="row">
						<div class="col-xs-12">
							<p style="font-family:arial; font-size:12px;"><strong>ASUNTO:</strong> Se declara bajo protesta de decir verdad, que las enmendaduras o anotaciones que alteran los datos originales, as&iacute; como los datos y requisitos que faltan en la factura comercial y que se declaran en la presente, son verdaderos y correctos.</p>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 text-right">
							<p style="font-family:arial; font-size:12px;"><strong>Nuevo Laredo, Tamaulipas, a <span id="lbl_fecha_documento"></span></strong></p>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 text-left">
							<p style="font-family:arial; font-size:12px;"><strong>C. ADMINISTRADOR DE LA ADUANA</br>FRONTERIZA DE <span id="lbl_aduana"></span></br></br>PRESENTE.</strong></p>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<textarea id="edit_inicial" class="form-control text-uppercase" placeholder="" rows="11"></textarea>
							</div>
						</div>
					</div>
					<div class="row"><div class="col-xs-12">&nbsp;</div></div>
					<div class="row">
						<div class="col-xs-12 text-left">
							<p style="font-family:arial; font-size:12px;"><strong>I.- DEL IMPORTE DE LOS CARGOS NO COMPRENDIDOS EN EL PRECIO PAGADO POR LAS MERCANCÍAS CONSIGNADO EN LA REFERIDA FACTURA, Y QUE FORMAN PARTE DEL VALOR DE TRANSACCIÓN DE LAS MISMAS:</strong></p>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<table width="100%" class="" style="font-size:12px;">
								<tbody>
									<tr style="border-bottom: 1px solid #EEE; height:12px; font-weight:bold; height:25px;">
										<td>&nbsp;&nbsp;&nbsp;A.- GASTOS CONEXOS, TALES COMO MANEJO, CARGA Y DESCARGA.</td>
										<td>&nbsp;</td>
										<td width="300px">
											<div class="input-group">
												<div class="input-group-addon">$</div>
												<input id="txt_gastos" type="text" class="form-control text-uppercase text-right">
												<div class="input-group-addon">DLLS.</div>
											</div>
										</td>
									</tr>
									<tr style="border-bottom: 1px solid #EEE; height:12px; font-weight:bold; height:25px;">
										<td>&nbsp;&nbsp;&nbsp;B.- FLETES.</td>
										<td>&nbsp;</td>
										<td>
											<div class="input-group">
												<div class="input-group-addon">$</div>
												<input id="txt_fletes" type="text" class="form-control text-uppercase text-right">
												<div class="input-group-addon">DLLS.</div>
											</div>
										</td>
									</tr>
									<tr style="border-bottom: 1px solid #EEE; height:12px; font-weight:bold; height:25px;">
										<td>&nbsp;&nbsp;&nbsp;C.- SEGUROS.</td>
										<td>&nbsp;</td>
										<td>
											<div class="input-group">
												<div class="input-group-addon">$</div>
												<input id="txt_seguros" type="text" class="form-control text-uppercase text-right">
												<div class="input-group-addon">DLLS.</div>
											</div>
										</td>
									</tr>
									<tr style="border-bottom: 1px solid #EEE; height:12px; font-weight:bold; height:25px;">
										<td>&nbsp;&nbsp;&nbsp;D.- OTROS.</td>
										<td>&nbsp;</td>
										<td>
											<div class="input-group">
												<div class="input-group-addon">$</div>
												<input id="txt_otros" type="text" class="form-control text-uppercase text-right">
												<div class="input-group-addon">DLLS.</div>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="row"><div class="col-xs-12">&nbsp;</div></div>
					<div class="row">
						<div class="col-xs-12 text-left">
							<p style="font-family:arial; font-size:12px;"><strong>II.- DE LOS DEMAS DATOS Y REQUISITOS QUE DEBE REUNIR LA FACTURA Y SE OMITIERON:</strong></p>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<textarea id="edit_prov" class="form-control text-uppercase" placeholder="" rows="21"></textarea>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 text-left">
							<p style="font-family:arial; font-size:12px;"><strong>SE DECLARA EN LA PRESENTE EL DOMICILIO CORRECTO DE LOS PROVEEDORES.</strong></p>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 text-center">
							<p style="font-family:arial; font-size:12px;"><strong>PROTESTO LO NECESARIO</strong></p>
						</div>
					</div>
					<div id="div_agente_aduanal">
					</div>
					<div class="row"><div class="col-xs-12">&nbsp;</div></div>
					<div class="row">
						<div class="col-xs-12 text-right">
							<button class="btn btn-default" type="button" onClick="ajax_buscar_referencia();" style="border-top-right-radius: 0px !important; border-bottom-right-radius: 0px !important;"><i class="fa fa-ban" aria-hidden="true"></i> Cancelar</button>
							<button class="btn btn-success" type="button" onClick="ajax_descargar_carta_protesta();" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;"><i class="fa fa-download" aria-hidden="true"></i> Descargar</button>
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
	<!-- Text Editor -->
	<script src="../plugins/sceditor/minified/jquery.sceditor.bbcode.min.js"></script>
	
	<script src="../js/carta_protesta.js?v=2018.01.03.1856"></script>
</body>

</html>
