<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
$_SESSION['inventario']=true;
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

    <title>Reporte Facturacion Mexicana y Americana con facturas de pedimento</title>

	<!-- Bootstrap core CSS -->
    <link href="./../bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="./../DataTables-1.10.9/media/css/jquery.dataTables.css" rel="stylesheet"/>
	<link href="./../bootstrap/css/dataTables.responsive.css" rel="stylesheet"/>
	<link href="./../Buttons-1.0.3/css/buttons.bootstrap.min.css" rel="stylesheet"/>
	
	<link href="../datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
	
	
    <!-- Custom styles for this template -->
    <link href="./../bootstrap/css/navbar.css" rel="stylesheet">
	<style type="text/css">
		div.dataTables_processing { z-index: 1; }
	</style>

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <!--script src="./bootstrap/js/ie-emulation-modes-warning.js"></script-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	<script type="text/javascript" src="./../bootstrap/js/jquery.js"></script>
	<script src="./../bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../DataTables-1.10.9/media/js/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/dataTables.responsive.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../Buttons-1.0.3/js/dataTables.buttons.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../Buttons-1.0.3/js/buttons.bootstrap.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../Buttons-1.0.3/js/buttons.html5.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../Buttons-1.0.3/js/buttons.print.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../Buttons-1.0.3/js/buttons.flash.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../Buttons-1.0.3/js/buttons.colVis.min.js"></script>
	
	<script type="text/javascript" language="javascript" src="./../jzip/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->  
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]--> 
	
	<!-- datepicker -->
    <script src="../datepicker/js/bootstrap-datepicker.js"></script>
	
	<script type="text/javascript" language="javascript" src="rpt_facmexame.js?1.02" class="init"></script>
</head>
<body>
	<div class="container">
 <?php include('nav.php');?>
      <!-- Main component for a primary marketing message or call to action -->
      <div class="jumbotron"> 
	  <h2>Reportes <small>Facturacion Mexicana y Americana con facturas de pedimento</small></h2>
	  <div class="row">
			<div class="col-md-12 text-center">
				<form class="form-inline" role="form">
					  	<div class="form-group">
							<label>Patente:</label>
							<input type="text" class="form-control" id="patente" placeholder="Patente">
						</div>
						<div class="form-group">
							<label>Aduana:</label>
							<input type="text" class="form-control" id="aduana" placeholder="Aduana">
						</div>
						<div class="form-group">
							<label>Pedimento:</label>
							<input type="text" class="form-control" id="pedimento" placeholder="Pedimento">
						</div>
						<button type="button" class="btn btn-primary" onclick="consultar()">Consultar</button>
					  <!--div class="form-group">
						<label>&nbsp;</label>
						<div class="input-append date" data-date-format="dd/mm/yyyy">
							<button type="button" class="btn btn-primary" onclick="consultar()">Consultar</button>
						</div>
					  </div-->
				</form>
			</div>
		</div>
		<br>
		<div id="cargando"></div>
		<div class="table-responsive">
		<table id="rpt_facmexame" class="table table-striped table-condensed" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>FACTURA</th>
					<th>FECHAS FACTURAS</th>
					<th>TRAFICO</th>
					<th>CTAS AMERICANAS</th>
					<th>PEDIMENTO</th>
					<th>CVE PEDIMENTO</th>
					<th>NO CTA GASTOS</th>
					<th>FECHA CTA GASTOS</th>
					<th>CARGOS MEXICANOS</th>
				</tr>
			</thead>
		</table>
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
</body>
</html>