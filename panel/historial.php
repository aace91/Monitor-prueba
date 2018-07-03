<?php
include_once('./../checklogin.php');
include('./../connect_dbsql.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Abisai Cruz">
    <!--link rel="icon" href="../../favicon.ico"-->

    <title>Historico</title>

	<!-- Bootstrap core CSS -->
    <link href="./../bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<!--link href="./../bootstrap/css/jquery.dataTables.css" rel="stylesheet"/-->
	<link href="./../bootstrap/css/buttons.bootstrap.min.css" rel="stylesheet"/>
	<link href="./../bootstrap/css/dataTables.bootstrap.css" rel="stylesheet"/>
	<!--link href="./../bootstrap/css/dataTables.tableTools.min.css" rel="stylesheet"/>
	<link href="./datatablestools.css" rel="stylesheet"/-->
	<link href="./../bootstrap/css/fixedColumns.dataTables.min.css" rel="stylesheet"/>
	
	<link href="../datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
	
	<link href="./../bootstrap/css/bootstrap-select.min.css" rel="stylesheet"/>
	
    <!-- Custom styles for this template -->
    <link href="./../bootstrap/css/navbar.css" rel="stylesheet">

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
	<script type="text/javascript" language="javascript" src="./../DataTables-1.10.9/media/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../DataTables-1.10.9/media/js/dataTables.bootstrap.js"></script>
	<!--script type="text/javascript" language="javascript" src="./../bootstrap/js/dataTables.responsive.min.js"></script-->
	<!--script type="text/javascript" language="javascript" src="./../bootstrap/js/fnFilterOnReturn.js"></script-->
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/bootstrap-select.min.js"></script>
	<!--script type="text/javascript" language="javascript" src="./../bootstrap/js/dataTables.tableTools.min.js"></script-->
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/dataTables.buttons.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/buttons.bootstrap.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/buttons.html5.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/buttons.print.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/buttons.flash.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/buttons.colVis.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/dataTables.fixedColumns.min.js"></script>
	<!--script type="text/javascript" language="javascript" src="./../bootstrap/js/dataTables.bootstrap.js"></script-->
	
	<script type="text/javascript" language="javascript" src="./../jzip/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->  
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]--> 
	
	<!-- datepicker -->
    <script src="../datepicker/js/bootstrap-datepicker.js"></script>
	
	<script type="text/javascript" language="javascript" class="init" src="tablehistorico.js?2018.07.03.1226"></script>
	<script type="text/javascript" language="javascript" src="operaciones.js?1.0"></script>
	<script type="text/javascript" language="javascript" src="moment.js"></script>
	<script type="text/javascript" language="javascript">
		function cambiaejecutivo(){
			actualiza();
		}
		function actualiza(){
			var table = $('#example').DataTable();
			table.ajax.reload(null, false);
		}
	</script>
</head>
<body>
	<div class="container">
 <?php include('nav.php');?>
      <!-- Main component for a primary marketing message or call to action -->
      <div class="jumbotron"> 
		<div class="row">
			<div class="col-md-5 text-center">
				<form class="form-inline" role="form">
					<div class="form-group">
						<label>Ejecutivo</label>
						<select class="selectpicker ejecutivo" data-style="btn-primary" onchange="cambiaejecutivo()" id="selejecutivo">
							<option value="">TODOS</option>
							<?php
								$consulta="SELECT usunombre,Usuario_id FROM `tblusua` where usunivel='E' order by usunombre";
								$query = mysqli_query($cmysqli,$consulta);
								$number = mysqli_num_rows($query);
								if($number > 1){
									while($row = mysqli_fetch_array($query)){
										if ($row['Usuario_id']==$id){
											echo '<option selected value="'.$row['usunombre'].'">'.$row['usunombre'].'</option>';
										}else{
											echo '<option value="'.$row['usunombre'].'">'.$row['usunombre'].'</option>';
										}
									}
								}
							?>
						</select>
					</div>
				</form>
			</div>
			<div class="col-md-7 text-center">
				<form class="form-inline" role="form">
					<div class="form-group">
						<label>Cliente</label>
						<select class="selectpicker clientes" data-style="btn-primary" onchange="actualiza()" id="selecliente" data-live-search="true">
							<option value="">TODOS</option>
							<?php
								$consulta="SELECT nom,cliente_id FROM `clientes` order by nom";
								$query = mysqli_query($cmysqli,$consulta);
								$number = mysqli_num_rows($query);
								if($number > 1){
									while($row = mysqli_fetch_array($query)){
										echo '<option value="'.$row['cliente_id'].'">'.$row['nom'].'</option>';
									}
								}
							?>
						</select>
					</div>
				</form>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12 text-center">
				<form class="form-inline" role="form">
					  <div class="form-group">
							<label>Fecha entrada incial:</label>
							<div class="input-append date" id="fechaini" data-date-format="dd/mm/yyyy">
							<input type="text" id="fechaini1" class="form-control" value="" readonly>
							<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
							</div>
					  </div>
					  <div class="form-group">
							<label>Fecha entrada final:</label>
							<div class="input-append date" id="fechafin" data-date-format="dd/mm/yyyy">
							<input type="text" id="fechafin1" class="form-control" value="" readonly>
							<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
							</div>
					  </div>
					   <div class="form-group">
							<label>Fecha salida incial:</label>
							<div class="input-append date" id="fechasalini" data-date-format="dd/mm/yyyy">
							<input type="text" id="fechasalini1" class="form-control" value="" readonly>
							<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
							</div>
					  </div>
					  <div class="form-group">
							<label>Fecha salida final:</label>
							<div class="input-append date" id="fechasalfin" data-date-format="dd/mm/yyyy">
							<input type="text" id="fechasalfin1" class="form-control" value="" readonly>
							<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
							</div>
					  </div>
					  <div class="form-group">
						<label>&nbsp;</label>
						<div class="input-append date" data-date-format="dd/mm/yyyy">
							<button type="button" class="btn btn-primary" onclick="filtrafecha()">Filtrar fechas</button>
						</div>
					  </div>
				</form>
			</div>
		</div>
		<br>
		<div class="table-responsive">
		<table id="example" class="table table-striped table-bordered table-hover table-condensed" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>Referencia</th>
					<th>Documentación</th>
					<th>Fotos</th>
					<th>Cliente</th>
					<th>Fecha Entrada</th>
					<th>Hora Entrada</th>
					<th>Factura</th>
					<th>Fecha factura</th>
					<th>#Revisión</th>
					<th>Fecha revisión</th>
					<th>Hora revisión</th>
					<th>Factura revisión</th>
					<th>#Remisión</th>
					<th>Fecha remisión</th>
					<th>Hora remisión</th>
					<th>Tipo Ped.</th>
					<th>Pedimento</th>
					<th>Remesa</th>
					<th>Ejecutivo</th>
					<th>Fecha salida</th>
					<th>Hora salida</th>
					<th>Tiempo en bodega</th>
					<th>Caja</th>
					<th>Guía</th>
					<th>Linea</th>
					<th>PO</th>
					<th>Subguias</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Referencia</th>
					<th>Documentación</th>
					<th>Fotos</th>
					<th>Cliente</th>
					<th>Fecha Entrada</th>
					<th>Hora Entrada</th>
					<th>Factura</th>
					<th>Fecha factura</th>
					<th>#Revisión</th>
					<th>Fecha revisión</th>
					<th>Hora revisión</th>
					<th>Factura revisión</th>
					<th>#Remisión</th>
					<th>Fecha remisión</th>
					<th>Hora remisión</th>
					<th>Tipo Ped.</th>
					<th>Pedimento</th>
					<th>Remesa</th>
					<th>Ejecutivo</th>
					<th>Fecha salida</th>
					<th>Hora salida</th>
					<th>Tiempo en bodega</th>
					<th>Caja</th>
					<th>Guía</th>
					<th>Linea</th>
					<th>PO</th>
					<th>Subguias</th>
				</tr>
        </tfoot>
		</table>
		</div>
		<br>
		<ul class="nav nav-tabs">
		  <li id="tabpedimento" role="presentation" class="active"><a href="javascript:void(0);" onclick="consultapedimento(pedimentog);return false;">Datos Pedimento</a></li>
		  <li id="tabremesa" role="presentation"><a href="javascript:void(0);" onclick="consultaremesa('','');return false;">Datos Remesa</a></li>
		</ul>
		<div id="Detalle">
			<br>
			<center><p>Debe seleccionar un pedimento</p></center>
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