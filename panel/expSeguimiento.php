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
	
    <title>Seguimiento</title>

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
		<input type="hidden" id="itxt_email_usuario" value="<?php echo $usuemail ?>"/>
		
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
		
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Seguimiento de Pedimentos</strong> <small></small>
				</div>
				<div class="panel-body">
					<div class="row">	
						<div class="col-xs-12 col-md-4">
							<select id="isel_buscar" class="form-control" onChange="fcn_sel_buscar_change();">
								<option value="">Seleccione una opci&oacute;n</option>
								<option value="referencia">Numero de Referencia</option>
								<option value="pedimento">Pedimento</option>
								<option value="cuenta_gastos">Cuenta de Gastos</option>
							</select>
						</div>
						<div class="col-xs-12 col-md-8">
							<div class="form-group">
								<div class="input-group">
									<input id="itxt_buscar" class="form-control" type="text" maxlength="14">
									<span class="input-group-btn">
										<button id="ibtn_buscar" type="button" class="btn btn-info" onclick="ajax_get_grid_busqueda_data();"><i class="fa fa-search"></i> Buscar</button>
									</span>
								</div>									
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dtseguimiento" class="table table-striped table-bordered" width="100%">
										<thead>
											<tr>
												<th class="def_app_center">REFERENCIA</th>
												<th class="def_app_center">PEDIMENTO</th>
												<th class="def_app_center" style="width:100px;">CUENTA</th>
												<th class="def_app_center">TRAFICO</th>
												<th class="def_app_center">COMENTARIOS</th>
												<th class="def_app_center">ESTATUS</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th class="def_app_center">REFERENCIA</th>
												<th class="def_app_center">PEDIMENTO</th>
												<th class="def_app_center">CUENTA</th>
												<th class="def_app_center">TRAFICO</th>
												<th class="def_app_center">COMENTARIOS</th>
												<th class="def_app_center">ESTATUS</th>
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
		
	<script src="../js/expSeguimiento.js?v=2017.01.23.1700"></script>
</body>

</html>
