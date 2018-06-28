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

    <title>Solicitud de Sellos</title>

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
		#isec_reporte { display: none; }
	
	
		.def_app_right{
		  text-align: right;
		}

		.def_app_left{
		  text-align: left;
		}

		.def_app_center{
		  text-align: center !important;
		}
	</style>
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php');?>
		
		<div id="modal_add_referencia" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Solicitar nuevo sello</h4>
					</div>
					<div class="modal-body">								
						<div class="row">
							<div class="col-xs-12 col-md-6 text-left">
								<div class="form-group">
									<label>Numero de Referencia:</label>
									<input id="itxt_modal_referencia" class="form-control" type="text" onKeyUp="toUpper(this)" maxlength="15">
								</div>
							</div>
							<div class="col-xs-12 col-md-6 text-left">
								<div class="form-group">
									<label>Numero de Caja:</label>
									<input id="itxt_modal_caja" class="form-control" type="text" onKeyUp="toUpper(this)" maxlength="20">
								</div>					
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div id="idiv_modal_mensaje" style="display:none;"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="row">
							<div class="col-xs-12 col-md-12">
								<div class="form-group">
									<button id="ibtn_modal_solicitar_sello" type="button" class="btn btn-success" onClick="javascript:ajax_set_solicitar_sello();"><i class="fa fa-check"></i> Solicitar Sello</button>	
									<button id="ibtn_modal_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Cancelar</button>					
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	
		<!-- Main component for a primary marketing message or call to action -->
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<i class="fa fa-list" aria-hidden="true"></i><strong> Lista de Sellos</strong> <small></small>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-md-12">
							<div class="form-group">
								<button id="ibtn_solicitar_sello" type="button" class="btn btn-default" onClick="javascript:fcn_solicitar_sello();">Solicitar Sello</button>					
							</div>
						</div>
					</div>
										
					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dtreferencias" class="table table-striped table-bordered" width="100%">
										<thead>
											<tr>
												<th class="def_app_center">REFERENCIA</th>
												<th class="def_app_center">CAJA</th>
												<th class="def_app_center">FECHA SOLICITUD</th>
												<th class="def_app_center">FECHA ATENDIDO</th>
												<th class="def_app_center">FOTO</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th class="def_app_center">REFERENCIA</th>
												<th class="def_app_center">CAJA</th>
												<th class="def_app_center">FECHA SOLICITUD</th>
												<th class="def_app_center">FECHA ATENDIDO</th>
												<th class="def_app_center">FOTO</th>
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
	
	<script src="../js/solicitudSellos.js?1.01"></script>
</body>

</html>
