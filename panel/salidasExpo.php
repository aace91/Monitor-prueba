<?php
include_once('./../checklogin.php');
if($loggedIn == false){
	header("Location: ./../login.php"); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Monitor - Salidas exportacion</title>

	<!-- Bootstrap core CSS -->
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	
	<!-- DataTables CSS -->
    <link href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet">
	<link href="../editor/css/editor.bootstrap.min.css" rel="stylesheet">
	
	<!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	
	<!-- Select2 CSS -->
	<link rel="stylesheet" href="../bower_components/select2/dist/css/select2.min.css" />
	
    <!-- Custom styles for this template -->
    <link href="../bootstrap/css/navbar.css" rel="stylesheet">
	

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <!--script src="./bootstrap/js/ie-emulation-modes-warning.js"></script-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	<style>
		table.dataTable tbody tr.selected {
			background-color: #0088cc !important;
		}
	</style>
</head>
<body>
	<div class="container">
		<?php include('nav.php');?>
		<!-- Main component for a primary marketing message or call to action -->
		<div class="jumbotron" style="padding: 10px; margin: 0px;">
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<h2><strong><i class="fa fa-list" aria-hidden="true"></i> Salidas de Exportación</strong></h2> 
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label>Estatus:</label>
								<div class="input-append">
									<select class="form-control" id="sel_status" onchange="fcn_sel_status_change();">
										<option value="0" selected>Pendientes</option>
										<option value="1">Cumplidas</option>
										<option value="-1">Todas</option>
									</select>
								</div>
							</div>
						</div>
					</div>
				
					<div class="row">
						<div class="col-xs-12">
							<div class="table-responsive">
								<table id="dtsalidasExpo" class="table table-striped table-bordered" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>No.</th>
											<th>Fecha alta</th>
											<th>Linea</th>
											<th>Tipo-Caja</th>
											<th>Referencias</th>
											<th>Facturas</th>
											<th>Ejecutivo</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
    </div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!--script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="./bootstrap/js/bootstrap.min.js"></script-->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--script src="../../assets/js/ie10-viewport-bug-workaround.js"></script-->
	<?php include('foot.php');?>
	
	<script type="text/javascript" src="../bower_components/jquery/dist/jquery.js"></script>
	
	<script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
	
	
	
	<script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->  
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]--> 
	
	<!-- DataTables JavaScript -->
    <script src="../bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
	<script src="../bower_components/datatables.net-responsive/js/dataTables.responsive.js"></script>
	<script src="../bower_components/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script src="../bower_components/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
	<script src="../bower_components/datatables.net-select/js/dataTables.select.min.js"></script>
	<script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	<script src="../js/editor.select2.js"></script>
	
	<!-- select2 -->
    <script src="../bower_components/select2/dist/js/select2.min.js"></script>
	
	<script src="../js/salidasExpo.js?210220181800"></script>
</body>
</html>