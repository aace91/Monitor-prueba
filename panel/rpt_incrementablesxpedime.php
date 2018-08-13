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

    <title>Reporte Incrementables Por Pedimento</title>

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
	
	<!-- Custom styles for this template -->
	<link href="../bootstrap/css/navbar.css" rel="stylesheet">

	<!-- Select2 CSS -->
	<link href="../bower_components/select2/dist/css/select2.min.css" rel="stylesheet" />
	<link href="../bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css" rel="stylesheet" />
	
	<style>
		a:hover { cursor:pointer; }
	</style>
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php');?>

		<!-- MODAL LOAD CONFIGURACION -->
		<div id="modalloadconfig" class="modal" style="z-index:9999;">
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
			<div class="modal-dialog modal-lg">
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

		<!-- MODAL DETALLE CRUCE -->
		<div id="modal_detalle_cruce" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-list-o" aria-hidden="true"></i> Detalle cruce <span id="ispan_det_cruce_title"></span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dt_detalle_cruce" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="text-center">CRUCE</th>
													<th class="text-center">PEDIMENTO</th>
													<th class="text-center">TIPO CAMBIO</th>
													<th class="text-center">N PARTE</th>
													<th class="text-center">CONCEPTO</th>
													<th class="text-center">CANTIDAD</th>
													<th class="text-center">TARIFA</th>
													<th class="text-center">TOTAL</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th colspan="6" style="text-align:right !important;">TOTAL:</th>
													<th colspan="2" id="dtcruce_items_total"></th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>

							<div class="col-xs-12">
								<div id="idiv_mdl_det_cruce_mensaje"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group pull-right">	
										<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-ban" aria-hidden="true"></i> Salir</button>
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
					<i class="fa fa-list" aria-hidden="true"></i><strong> Reporte Incrementables Por Pedimento</strong> <small></small>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-12">
							<div id="idiv_mensaje"></div>
						</div>
					</div>
					
					<div id="idiv_principal" style="display:block;">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Cliente</div>
										<select class="form-control" id="isel_cliente" onChange="fcn_cargar_grid_pedimentos();">
											<?php
												include('./../connect_dbsql.php');

												$consulta = "SELECT bodCli.Cliente_id, bodCli.Nom
															 FROM facturacion.clientes AS facCli INNER JOIN 
																  bodega.clientes AS bodCli ON bodCli.Cliente_id=facCli.id_cliente
															 ORDER BY Nom";
																		
												$query = mysqli_query($cmysqli, $consulta);
												echo '<option value="" selected></option>';
												while($row = mysqli_fetch_array($query)){
													echo '<option value="'.$row['Cliente_id'].'">'.$row['Nom'].'</option>';
												}
											?>
										</select>
										<span class="input-group-btn">
											<button id="ibtn_consultar" type="button" class="btn btn-primary" onClick="javascript:fcn_cargar_grid_pedimentos();">Consultar</button>	
										</span>
									</div>
								</div>
							</div>
						</div>
											
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dt_pedimentos" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="text-center">NUMERO PEDIMENTO</th>
													<th class="text-center">CRUCES</th>
													<th class="text-center"></th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th class="text-center">NUMERO PEDIMENTO</th>
													<th class="text-center">CRUCES</th>
													<th class="text-center"></th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div id="idiv_detalle" style="display:none;">
						<div class="row">
							<div class="col-xs-12">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="#" onclick="fcn_close_detalles();">Lista de Pedimentos</a></li>
									<li class="breadcrumb-item active"><strong><span id="ispan_pedimento_actual"></span></strong></li>
									<button type="button" class="btn btn-primary btn-xs pull-right" onclick="fcn_close_detalles();"><i class="fa fa-reply" aria-hidden="true"></i> Regresar</button>
								</ol>
							</div>
						</div>
								
						<div class="row">
							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dt_detalle_inc" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th class="text-center">CRUCE</th>
													<th class="text-center">FECHA SALIDA</th>
													<th class="text-center">N. CAJA</th>
													<th class="text-center">INCREMENTABLE</th>
													<th class="text-center">FLETE</th>
													<th class="text-center"></th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th colspan="3" style="text-align:right !important;">TOTAL:</th>
													<th id="dtfactura_items_total"></th>
													<th></th>
													<th></th>
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
    </div> <!-- /container -->

	<script src="../bower_components/json3/lib/json3.min.js"></script>
	
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

	<script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]-->
	
	<!-- Select2 -->
    <script type="text/javascript" src="../bower_components/select2/dist/js/select2.min.js"></script>
	<script type="text/javascript" src="../bower_components/select2/dist/js/i18n/es.js"></script>

	<!--Numeric-->
	<script  type="text/javascript" language="javascript" src="../plugins/numeric/jquery.numeric.js"></script>
	
	<script src="../js/rpt_incrementablesxpedime.js?v=2018.08.13.0900"></script>
</body>

</html>
