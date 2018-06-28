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

		<title>Monitor - Priority service</title>

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
		
		<!-- Custom styles for this template -->
		<link href="../bootstrap/css/navbar.css" rel="stylesheet">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
		
		<link href="timeline.css" rel="stylesheet"/>
		<style>
			textarea {
				resize: none;
			}
			
			.btn-app {
			  border-radius: 3px;
			  position: relative;
			  padding: 15px 5px;
			  margin: 0 0 10px 10px;
			  min-width: 80px;
			  height: 60px;
			  text-align: center;
			  font-size: 12px;
			}
			.btn-app > .fa,
			.btn-app > .glyphicon,
			.btn-app > .ion {
			  font-size: 20px;
			  display: block;
			}
			.btn-app:hover {
			  border-color: #aaa;
			}
			.btn-app:active,
			.btn-app:focus {
			  -webkit-box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
			  -moz-box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
			  box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
			}
			.btn-app > .badge {
			  position: absolute;
			  top: -3px;
			  right: -10px;
			  font-size: 10px;
			  font-weight: 400;
			}

			/*Para boton refrescar de grid*/
			div.dt-buttons {
				float:right;
				margin-bottom:6px;
			}
		</style>
	</head>

	<body>
		<div class="container">
			<?php include('nav.php'); ?>
			<input type="hidden" id="itxt_data" data-app_data="<?php 
				$sReferencia = '';
				if ((isset($_GET['ref']) && !empty($_GET['ref']))) { 
					$sReferencia = $_GET['ref'];
				}
				
				$aAppData = array(
					'sReferencia' => $sReferencia
				);
					
				$respuesta['aAppData']=$aAppData;
				echo htmlspecialchars(json_encode($respuesta), ENT_QUOTES, 'UTF-8');
			?>"/>

			<!-- MODAL CONFIRM -->
			<div id="modalconfirm" class="modal fade" style="z-index:9999;" data-backdrop="static">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header" style="border-top:4px solid #f39c12;">
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

			<!-- MODAL TIEMPO EXTRA-->
			<div class="modal" id="modal_tiempoext" data-backdrop="static">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header" style="border-top:4px solid #00BFEE;">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 id="ih4_tiempoext_titulo" class="modal-title"><i class="fa fa-clock-o" aria-hidden="true"></i> Solicitar priority service</h4>
						</div>
						<div class="modal-body">
							<div class="row" id="idiv_referencia_anterior" style="display:none;">
								<div class="col-md-12">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">Referencia Anterior</div>
											<input type="text" class="form-control text-uppercase" value="" id="txt_referencia_anterior" maxlength="10" disabled="disabled">
										</div>
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">Referencia</div>
											<input type="text" class="form-control text-uppercase" value="" id="txt_referencia" maxlength="10">
											<span class="input-group-btn">
												<button id="btn_buscar_referencia" type="button" class="btn btn-info"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">Cliente</div>
											<input type="text" class="form-control text-uppercase" value="" id="txt_cliente" disabled>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">Linea Fletera</div>
											<input type="text" class="form-control text-uppercase" value="" id="txt_linea_fletera" disabled>
										</div>
									</div>
								</div>
							</div>
							<div class="row" id="idiv_motivo">
								<div class="col-md-12">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">Motivo</div>
											<textarea class="form-control text-uppercase" rows="2" id="txt_motivo" disabled></textarea>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div id="mensaje_tiempoext_modal"></div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button id="btn_guardar_tiempoext" type="button" class="btn btn-success" disabled><i class="fa fa-floppy-o"></i> Guardar</button>
						</div>
					</div>
				</div>
			</div>

			<!-- ACEPTAR O RECHAZAR TIEMPO EXTRA -->
			<div class="modal" id="modal_rechazar" data-backdrop="static">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header" style="border-top:4px solid #FF5A5A;">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title"><span class="glyphicon glyphicon-ban-circle"></span> Rechazar solicitud de servicio prioritario</h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">Comentarios</div>
											<textarea class="form-control text-uppercase" rows="4" id="txt_mdl_rec_tiempoext_observaciones"></textarea>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div id="div_mdl_rec_tiempoext_mensaje"></div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button id="btn_mdl_rec_tiempoext_guardar" type="button" class="btn btn-success"><i class="fa fa-floppy-o"></i> Guardar</button>
						</div>
					</div>
				</div>
			</div>
			
			<!-- MODAL TIMELINE -->
			<div id="modal_timeline" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header" style="border-top:4px solid #00BFEE;">
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
									<div id="idiv_timeline_mensaje"></div>
								</div>
							</div>
							<div id="idiv_timeline_row_comentario" class="row">
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
						<strong> Servicio Prioritario</strong> <small></small>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-12">
								<div id="mensaje_tiempo_extra"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 text-right">
								<button class="btn btn-app btn-success" id="btn_nuevo_tiempoext">
									<i class="fa fa-clock-o" aria-hidden="true"></i> Solicitar priority service
								</button>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="tbl_tiemposext" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th>#</th>
													<th>Referencia</th>
													<th>Motivo</th>
													<th>Fecha Alta</th>
													<th>Cliente</th>
													<th>Linea Entrego</th>								
													<th width="150px">ESTADO</th>
													<th>Aprobaci&oacute;n Bodega</th>
													<th>Aprobaci&oacute;n Cliente</th>
													<th>Aprobaci&oacute;n Ejecutivo</th>
													<th>Comentarios</th>
													<th>Usuario Solicito</th>
													<th>Acciones</th>
													<th>Opt</th>
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

		<script src="../js/tiempo_extra.js?201801101100"></script>
	</body>
</html>
