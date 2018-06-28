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
	
    <title>Salidas Seguimiento</title>

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
	<link href="../editor/css/editor.bootstrap.min.css" rel="stylesheet">
	<link href="../editor/css/editor.selectize.css" rel="stylesheet">
	
	<!--link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet"/-->
	
	<!-- Custom styles for this template -->
    <link href="../bootstrap/css/navbar.css" rel="stylesheet">
	
	<!-- Select2 CSS-->
	<link href="../bower_components/select2/dist/css/select2.min.css" rel="stylesheet">
	<link href="../bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css" rel="stylesheet">
	
	<!-- FileInput -->
	<link href="../bower_components/bootstrap-fileinput-4.2.3/css/fileinput.min.css" rel="stylesheet"/>
	
	<!--TouchSpin-->
    <link rel="stylesheet" href="../plugins/touchspin/jquery.bootstrap-touchspin.css">

	<!-- CSS Propios - TimeLine -->
	<link href="timeline.css" rel="stylesheet"/>
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
		
		a:hover {
			cursor:pointer;
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
		
		<!-- MODAL SUBIR DOCUMENTOS -->
		<div id="modal_subir_docs" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Subir Archivos</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div class="col-xs-12 col-md-12">
								<div class="form-group">
									<label class="control-label"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Seleccione Archivo(s)</label>
									<input id="ifile_documentos" name="ifile_documentos[]" type="file" class="file-loading" multiple=true/>
								</div>
								<!--div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Comentarios:</div>
										<textarea id="itxt_modal_edit_comentario_click_comentarios" class="form-control uppercase" rows="5" style="resize: none;"></textarea>
										
									</div>
								</div-->					
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
					</div>
				</div>
			</div>
		</div>
		
		<!-- MODAL TIMELINE -->
		<div id="modal_timeline" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Comentarios</h4>
					</div>
					<div class="modal-body">	
						<div class="row">
							<div id="idiv_timeline" class="col-xs-12">
								<ul class="timeline">	
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
								</ul>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div id="idiv_timeline_mensaje" style="display:none;"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group" style="margin-bottom: 5px;">
									<label for="comment"><i class="fa fa-comments" aria-hidden="true"></i> Comentario:</label>
									<textarea class="form-control text-uppercase" style="resize: none;" rows="2" id="itxt_timeline_comentario"></textarea>
								</div>
							</div>
							<div class="col-xs-12">
								<button type="button" class="btn btn-success pull-right" onClick="ajax_set_comentarios()"><i class="fa fa-plus"></i> Agregar Comentario</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Salidas</strong> <small></small>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<div id="idiv_mensaje" style="display:none;"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label class="control-label hidden-xs hidden-sm">&nbsp;</label>
								<div class="input-group">	
									<div class="input-group-addon">Cliente:</div>
									<div class="select2-wrapper">
										<select id="isel_clientes_expo" class="form-control" onChange="ajax_get_grid_salidas_data();">
											<?php
												require('./../../../connect_dbsql.php');
												$consulta = "SELECT b.gcliente, b.cnombre
															 FROM bodega.expos_seguimiento AS a INNER JOIN
																  bodega.cltes_expo AS b ON a.id_cliente = b.gcliente
															 GROUP BY b.gcliente, b.cnombre";
																						
												$query = mysqli_query($cmysqli,$consulta);
												$bFirstElement = true;
												echo '<option value=""></option>';
												while($row = mysqli_fetch_array($query)){
													if ($bFirstElement) {
														echo '<option selected value="'.$row['gcliente'].'">'.$row['cnombre'].'</option>';
														$bFirstElement = false;
													} else {
														echo '<option value="'.$row['gcliente'].'">'.$row['cnombre'].'</option>';	
													}													
												}
											?>							
										</select>
									</div>
								</div>	
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dtfolios" class="table table-striped table-bordered" width="99.8%">
										<thead>
											<tr>
												<th class="def_app_center">FOLIO</th>
												<th class="def_app_center" style="width:100px;">ESTATUS</th>
												<th class="def_app_center" style="width:120px;">CREADO</th>
												<th class="def_app_center">CAJA</th>
												<th class="def_app_center" style="width:120px;">FACTURA(S)</th>
												<th class="def_app_center" style="width:130px;">LOGISTICA</th>
												<th class="def_app_center" style="width:130px;">TRANSPORTISTA</th>
												<th class="def_app_center" style="width:120px;">APROBADO</th>
												<th class="def_app_center">COMENTARIOS</th>
												<th class="def_app_center">DOCUMENTOS</th>
												<!--th class="def_app_center" style="width:90px;">OPCIONES</th-->
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th class="def_app_center">FOLIO</th>
												<th class="def_app_center" style="width:100px;">ESTATUS</th>
												<th class="def_app_center" style="width:120px;">CREADO</th>
												<th class="def_app_center">CAJA</th>
												<th class="def_app_center" style="width:120px;">FACTURA(S)</th>
												<th class="def_app_center" style="width:130px;">LOGISTICA</th>
												<th class="def_app_center" style="width:130px;">TRANSPORTISTA</th>
												<th class="def_app_center" style="width:120px;">APROBADO</th>
												<th class="def_app_center">COMENTARIOS</th>
												<th class="def_app_center">DOCUMENTOS</th>
												<!--th class="def_app_center" style="width:90px;">OPCIONES</th-->
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
					</div>
						
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<button id="ibtn_sync" type="button" class="btn btn-default" onClick="javascript:ajax_set_update_sync();"><i class="fa fa-refresh" aria-hidden="true"></i> Sincronizar Documentos</button>
							&nbsp;<span id="ispan_sync_message"></span>
						</div>
						<div class="col-xs-12 col-md-6">
							<span id="ispan_refresh_message" class="pull-right" style="padding: 7px;"></span>
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
	<script src="../bower_components/datatables.net-fixedcolumns/js/dataTables.fixedColumns.min.js"></script>
	<script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	<script src="../editor/js/editor.selectize.js"></script>
	
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
	
	<!--TouchSpin-->
	<script  type="text/javascript" language="javascript" src="../plugins/touchspin/jquery.bootstrap-touchspin.js"></script>

	<script src="../js/exposSalidasSeguimiento.js?v=2017.05.29.0900"></script>
</body>

</html>
