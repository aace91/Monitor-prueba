<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
$_SESSION['inventario']=false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="login pedimento">
    <meta name="author" content="Abisai Cruz">
    <!--link rel="icon" href="../../favicon.ico"-->

    <title>Listado de Correos</title>

	<!-- Bootstrap core CSS -->
    <link href="./../bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
	
	<!-- DataTables CSS -->
    <link href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet">

	

    <!-- Custom styles for this template -->
    <link href="./../bootstrap/css/navbar.css" rel="stylesheet"/>
	
	<!-- Select2 CSS -->
	<link href="../bower_components/select2/dist/css/select2.min.css" rel="stylesheet" />
	<link href="../bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css" rel="stylesheet" />
	

	<style type="text/css">
		div.dataTables_processing { z-index: 1; }
	</style>

  
</head>
<body>
	<div class="container">
		<?php include('nav.php');?>
		
		
		<!-- Main component for a primary marketing message or call to action -->
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<h2>Correos envio <small>Listado</small></h2>
				</div>
				<div id="div_panel_principal" class="panel-body">
					<br>
					<div class="table-responsive">
					<table id="dtlistcorreos" class="table">
						<thead style="background-color:#3071AA; color:#FFF;">
							<tr>
								<th>#Cliente</th>
								<th>Acciones</th>
								<th>Nombre</th>
								<th>Correos</th>
							</tr>
						</thead>
					</table>
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
	
	  <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <!--script src="./bootstrap/js/ie-emulation-modes-warning.js"></script-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	<!-- jQuery -->
	<!--[if lt IE 9]>
	  <script src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js' type='text/javascript'/>
	<![endif]-->

	<!--[if (gte IE 9) | (!IE)]><!-->  
	   <script src="./../bootstrap/js/jquery.js"></script>
	<!--<![endif]--> 
	<script src="./../bootstrap/js/bootstrap.min.js"></script>
	
	<!-- DataTables JavaScript -->
    <script src="../bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
	<script src="../bower_components/datatables.net-responsive/js/dataTables.responsive.js"></script>
	<script src="../bower_components/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script src="../bower_components/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
	<script src="../bower_components/datatables.net-buttons/js/buttons.colVis.min.js"></script>
    <script src="../bower_components/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="../bower_components/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script src="../bower_components/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
	
	<script type="text/javascript" language="javascript" src="./../jzip/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->  
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]--> 
	
	<!-- datepicker -->
    <script src="../datepicker/js/bootstrap-datepicker.js"></script>
	
	<script type="text/javascript" language="javascript" src="moment.js"></script>
	
	<!-- Select2 -->
    <script type="text/javascript" src="../bower_components/select2/dist/js/select2.min.js"></script>
	<script type="text/javascript" src="../bower_components/select2/dist/js/i18n/es.js"></script>
	
	<!-- Fileinput JS -->
	<!--script type="text/javascript" language="javascript" src="http://plugins.krajee.com/assets/24b9d388/js/plugins/purify.min.js"></script-->
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/fileinput.min.js"></script>
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/locales/es.js"></script>
	
	<!-- Javascript Propio -->
	<script type="text/javascript" src="../js/listcorreos.js?v=2017.12.29.1000"></script>
</body>
</html>