<?php
include_once('../checklogin.php');
if($loggedIn == false){ header("Location: login.php"); }
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Equipo vacio</title>

    <!-- Bootstrap Core CSS -->
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- DataTables CSS -->
    <link href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
	
	<!-- Datepicker -->
    <link href="../bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css">

	

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
	<div class="container">
		<?php include('nav.php');?>
		<!-- Main component for a primary marketing message or call to action -->
		<div class="jumbotron"> 
			<div class="row">
				<form class="form-inline">
					<div class="form-group">
						<label><i class="fa fa-filter"></i> Cliente:</label>
						<select class="form-control" id="selcliente">
							<option value=0 selected>TODOS</option>
							<?php
							include("../connect_dbsql.php");
							$sql="SELECT
								Cliente_id,Nom
							FROM
								clientes
							ORDER BY
								Nom";
							$consulta = mysqli_query($cmysqli,$sql);
							if (!$consulta) {
								echo "<option>Error al consultar los clientes".mysqli_error($cmysqli)."</option>";
							}
							while($row = $consulta->fetch_object()){
								$nombre = $row->Nom;
								$id_cliente = $row->Cliente_id;
								echo '<option value="'.$id_cliente.'">'.$nombre.'</option>';
							}
							?>
						</select>
					</div>
					<div class="form-group">
						<label><i class="fa fa-filter"></i> Linea:</label>
						<select class="form-control" id="sellinea">
							<option value='0' selected>TODOS</option>
							<?php
							$sql="SELECT
								clave,Nombre
							FROM
								consolidadoras_salidas
							ORDER BY
								Nombre";
							$consulta = mysqli_query($cmysqli,$sql);
							if (!$consulta) {
								echo "<option>Error al consultar las lineas".mysqli_error($cmysqli)."</option>";
							}
							while($row = $consulta->fetch_object()){
								$nombre = $row->Nombre;
								$id_linea = $row->clave;
								echo '<option value="'.$nombre.'">'.$nombre.'</option>';
							}
							?>
						</select>
					</div>
					<div class="form-group">
						<label><i class="fa fa-filter"></i> Fecha entrada inicial:</label>
						<div class="input-append date" id="fechaini" data-date-format="mm/dd/yyyy" >
							<input type="text" id="fechaini1" class="form-control" value="" readonly>
							<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
						</div>
					</div>
					<div class="form-group">
						<label><i class="fa fa-filter"></i> Fecha entrada inicial:</label>
						<div class="input-append date" id="fechafin" data-date-format="mm/dd/yyyy">
							<input type="text" id="fechafin1" class="form-control" value="" readonly>
							<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
						</div>
					</div>
				</form>
			</div>
			<br>
			<div class="table-responsive">
				<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="dtvacio">
					<thead>
						<tr>
							<th>ID</th>
							<th>Fecha</th>
							<th>Hora</th>
							<th>Cliente</th>
							<th>Tipo equipo</th>
							<th># Equipo</th>
							<th>Linea</th>
							<th>Fotos</th>
						</tr>
					</thead>
				</table>
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

    <script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]-->
	


    <!-- Datepicker JavaScript -->
	<script src="../bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
	

	
	<script src="../js/equipov.js?1.00"></script>
</body>

</html>
