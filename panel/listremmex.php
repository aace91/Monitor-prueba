<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
include('./../connect_gabdata.php');
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

    <title>Listado de Remisiones Mexicanas</title>

	<!-- Bootstrap core CSS -->
    <link href="./../bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
	<link href="./../DataTables-1.10.9/media/css/jquery.dataTables.css" rel="stylesheet"/>
	<link href="./../bootstrap/css/dataTables.responsive.css" rel="stylesheet"/>
	
	<link href="./../Buttons-1.0.3/css/buttons.bootstrap.min.css" rel="stylesheet"/>
	
	<link href="../datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <!-- Custom styles for this template -->
    <link href="./../bootstrap/css/navbar.css" rel="stylesheet"/>
	
	<!-- Select2 CSS -->
	<link href="../bower_components/select2/dist/css/select2.min.css" rel="stylesheet" />
	<link href="../bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css" rel="stylesheet" />
	
	<!-- FileInput -->
	<link href="../bower_components/bootstrap-fileinput-4.2.3/css/fileinput.min.css" rel="stylesheet"/>
	
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
	<!-- jQuery -->
	<!--[if lt IE 9]>
	  <script src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js' type='text/javascript'/>
	<![endif]-->

	<!--[if (gte IE 9) | (!IE)]><!-->  
	   <script src="./../bootstrap/js/jquery.js"></script>
	<!--<![endif]--> 
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
	
	<script type="text/javascript" language="javascript" src="moment.js"></script>
	
	<!-- Select2 -->
    <script type="text/javascript" src="../bower_components/select2/dist/js/select2.min.js"></script>
	<script type="text/javascript" src="../bower_components/select2/dist/js/i18n/es.js"></script>
	
	<!-- Fileinput JS -->
	<!--script type="text/javascript" language="javascript" src="http://plugins.krajee.com/assets/24b9d388/js/plugins/purify.min.js"></script-->
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/fileinput.min.js"></script>
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/locales/es.js"></script>
	
	<!-- Javascript Propio -->
	<script type="text/javascript" src="../js/listremmex.js?v=2017.12.28.1000"></script>
</head>
<body>
	<div class="container">
		<?php include('nav.php');?>
		
		<!-- Main component for a primary marketing message or call to action -->
		<div class="jumbotron"> 
			<h2>Remisiones GAB <small>Listado</small></h2>
			<div class="row">
				<div class="col-xs-12">
					<div class="form-group">
						<div id="idiv_error_mensaje"></div>
					</div>
				</div>
				<div class="col-xs-12 col-md-6">
					<div class="form-group" style="text-align: left;">
						<label>Cliente:</label>
						<div class="input-append">
							<select class="form-control" id="clientecontabilidad" onchange="filtrafecha();">
								<?php
									include('./../connect_gabdata.php');

									$consulta = "SELECT no_cte, nombre
												 FROM contagab.aacte
												 WHERE nombre<>'' AND nombre IS NOT NULL
												 ORDER BY nombre";
															
									$query = mysqli_query($cmysqli_sab07, $consulta);
									echo '<option value="" selected></option>';
									while($row = mysqli_fetch_array($query)){
										echo '<option value="'.$row['no_cte'].'">'.$row['nombre'].'</option>';
									}
								?>
							</select>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-md-6">
					<div class="form-group">
						<label>Estatus Pago:</label>
						<div class="input-append">
							<select class="form-control" id="selestatus" onchange="filtrafecha();">
								<option value="0">Vigente</option>
								<option value="1">Cancelada</option>
								<option value="-1">Todas</option>
							</select>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-md-4">
					<div class="form-group form-inline">
						<label>Fecha incial:</label>
						<div class="input-append date" id="fechaini" data-date-format="dd/mm/yyyy">
							<input type="text" id="fechaini1" class="form-control" value="" readonly>
							<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-md-4">
					<div class="form-group form-inline">
						<label>Fecha final:</label>
						<div class="input-append date" id="fechafin" data-date-format="dd/mm/yyyy">
							<input type="text" id="fechafin1" class="form-control" value="" readonly>
							<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-md-4">
					<div class="form-group">
						<label class="hidden-xs hidden-sm">&nbsp;</label>
						<div class="input-append">
							<button type="button" class="btn btn-primary" onclick="filtrafecha()">Filtrar</button>
						</div>
					</div>
				</div>
			</div>
			<br>
			<table id="ctamex" class="display responsive no-wrap" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>#Remision</th>
						<th>Estatus</th>
						<th>Trafico/Referencia</th>
						<th>Fecha</th>
						<th>Subtotal</th>
						<th>Pedimento</th>
						<th>Anexos</th>
					</tr>
				</thead>
			</table>
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