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

    <title>Reporte Coves y Contenedores</title>

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

		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<i class="fa fa-list" aria-hidden="true"></i><strong> Reporte Coves y Contenedores</strong>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-12">
							<div id="idiv_mensaje"></div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-5">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon">Referencia</span>
									<input id="itxt_referencia" class="form-control" type="text" maxlength="13"/>
									<span class="input-group-btn">
										<button type="button" class="btn btn-primary" onClick="javascript:ajax_get_covecontenedores();"><i class="fa fa-search" aria-hidden="true"></i> Buscar</button>	
									</span>
								</div>
							</div>
						</div>
					</div>
										
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div class='label label-primary' style='width: 100% !important; margin-bottom: 5px; display:block; font-size:100%;'>Listado de Coves</div>
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dt_coves" class="table table-striped table-bordered" width="100%">
										<thead>
											<tr>
												<th class="text-center">FACTURA</th>
												<th class="text-center">EDOCUMENT</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th class="text-center">FACTURA</th>
												<th class="text-center">EDOCUMENT</th>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class='label label-primary' style='width: 100% !important; margin-bottom: 5px; display:block; font-size:100%;'>Listado de Contenedores</div>
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dt_contenedores" class="table table-striped table-bordered" width="100%">
										<thead>
											<tr>
												<th class="text-center">CONTENEDOR</th>
												<th class="text-center">TIPO</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th class="text-center">CONTENEDOR</th>
												<th class="text-center">TIPO</th>
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
	
	<script src="../js/rpt_covescontenedores.js?v=2018.07.24.1544"></script>
</body>

</html>
