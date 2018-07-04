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

    <title>Subir archivos</title>

	<!-- Bootstrap core CSS -->
    <link href="./../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

	<!-- Datatables CSS -->
	<link href="./../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet"/>
	<link href="./../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet"/>
	<link href="./../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet"/>
	<link href="./../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet"/>
	
	<!-- FileInput CSS -->
	<link href="./../bootstrap/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
    
	<!-- Custom styles for this template -->
    <link href="./../bootstrap/css/navbar.css" rel="stylesheet">

	<!-- Custom Fonts -->
	<link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

	<!-- Select2 CSS -->
	<link href="../bower_components/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css">

	

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <!--script src="./bootstrap/js/ie-emulation-modes-warning.js"></script-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
	<div class="container">
 <?php include('nav.php');?>
      <!-- Main component for a primary marketing message or call to action -->
      <div class="jumbotron" style="padding: 10px; margin: 0px;"> 
		<div class="panel panel-default" style="margin-bottom: 0px;">
		<div class="panel-heading"><h2>Subir documento <small></small></h2></div>
		<div class="panel-body">
				<div class="row">
					<div class="col-md-2"><label>Tipo de documento</label></div>
					<div class="col-md-4"> 
						<select class="form-control selectpicker" id="seltpo" style="width: 100%">
							<?php
								$consulta="SELECT 
									id_tpo,
									descripcion 
								FROM 
									`docs_tipos`
								where
									ver_proveedor=1 or `set`=1 or solicitud_previa=1  
								order by 
									descripcion";
								$query = mysqli_query($cmysqli,$consulta);
								$number = mysqli_num_rows($query);
								if($number >= 1){
									while($row = mysqli_fetch_array($query)){
										if($row['id_tpo']==1){
											echo '<option value="'.$row['id_tpo'].'" selected>'.$row['descripcion'].'</option>';
										}else{
											echo '<option value="'.$row['id_tpo'].'">'.$row['descripcion'].'</option>';
										}
									}
								}
							?>
						</select>
					</div>
					<div class="col-md-1"><label>Cliente</label></div>
					<div class="col-md-5">
						<select class="form-control selectpicker" style="width: 100%" id="selcliente"  onchange="cambiacliente()" >
							<?php
								$consulta="SELECT Nom,Cliente_id FROM `clientes` order by Nom";
								$query = mysqli_query($cmysqli,$consulta);
								$number = mysqli_num_rows($query);
								if($number >= 1){
									while($row = mysqli_fetch_array($query)){
										echo '<option value="'.$row['Cliente_id'].'">'.$row['Nom'].'</option>';
									}
								}
							?>
						</select>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-md-12">
						<label>A continuación se muestra la referencias sin el documento seleccionado o en un estado no válido, seleccione todas las referencias que ampara el documento a subir</label>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-md-12">
						<table id="inventario" class="table table-striped table-bordered" style="width:100%">
							<thead>
								<tr>
									<th>Referencia</th>
									<th>Fecha Entrada</th>
									<th>PO</th>
									<th>Proveedor</th>
									<th>Descripcion</th>
									<th>Fotos</th>
									<th>Documentación</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
							<tfoot>
								<tr>
									<th>Referencia</th>
									<th>Fecha Entrada</th>
									<th>PO</th>
									<th>Proveedor</th>
									<th>Descripcion</th>
									<th>Fotos</th>
									<th>Documentación</th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<label>Haga clic en examinar para seleccionar el archivo o arrastrelo al recuadro</label>
					</div>
					<div class="col-md-12">
						<input id="idejecutivo" type="hidden" value="<?php echo $id; ?>" >
					</div>
					<div class="col-md-12">
						<input id="documento" name="documento[]" type="file">
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div id="error" class="col-md-12"></div>
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
	<script type="text/javascript" src="./../bootstrap/js/jquery.js"></script>

	<script src="./../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-select/js/dataTables.select.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-buttons/js/buttons.colVis.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>

	<script src="./../bootstrap/js/fileinput.min.js" type="text/javascript"></script>

	<!-- Select2 JS-->
	<script type="text/javascript" language="javascript" src="./../bower_components/select2/dist/js/select2.min.js"></script>

	<script type="text/javascript" language="javascript" class="init" src="tableinv.js?2018.04.07.1616"></script>
</body>
</html>