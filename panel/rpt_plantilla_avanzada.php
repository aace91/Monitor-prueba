<?php
include_once('./../checklogin.php');
include('./../connect_dbsql.php');
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

    <title>Plantilla avanzada COVE</title>

	<!-- Bootstrap core CSS -->
    <link href="./../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	
	
	<link href="./../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet"/>
	<!--link href="./../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet"/-->
	<link href="./../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet"/>
	<link href="./../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet"/>
	
	<link href="../editor/css/editor.bootstrap.min.css" rel="stylesheet">
	
	<link href="./../bower_components/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet"/>
	
	
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
	<script type="text/javascript" src="./../bower_components/jquery/dist/jquery.min.js"></script>
	<script src="./../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
	
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net/js/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>	
	<!--script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-responsive/js/dataTables.responsive.min.js"></script-->
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-buttons/js/buttons.html5.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-buttons/js/buttons.flash.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-buttons/js/buttons.colVis.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-select/js/dataTables.select.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bower_components/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
	
	<script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	
	<script type="text/javascript" language="javascript" src="./../bower_components/jszip/dist/jszip.min.js"></script>
	
	<script type="text/javascript" language="javascript" src="./../bower_components/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	
	<script type="text/javascript" language="javascript" src="./../bower_components/moment/min/moment.min.js"></script>
	
	<!--[if (gte IE 9) | (!IE)]><!-->  
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]--> 
	
	<style>
		body.modal-open {
			position: fixed;
		}
	</style>
	
	
	<script type="text/javascript" language="javascript" src="rpt_plantilla_avanzada.js?301120171820" class="init"></script>
</head>
<body>
	<div id="modal_elimina_precaptura" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="precaptura" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="titulo_precaptura">Eliminar Precaptura</h4>
				</div>
				<div class="modal-body">	
					<h4 id="eliminar_ref"></h2>
					<div id="eliminando"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button id="btneliminar_precaptura" type="button" class="btn btn-danger"> Eliminar</button>					
				</div>
			</div>
		</div>
	</div>
	<div id="modal_precaptura" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="precaptura" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="titulo_precaptura">Precaptura</h4>
				</div>
				<div class="modal-body">	
					<div id="guardando"></div>
					<row>
						<form class="form-horizontal" role="form">
							<div class="form-group">
								<label class="control-label col-sm-2" for="referencia">Referencia:</label>
								<div class="col-sm-10">
									<p class="form-control-static" id="referencia_precaptura"></p>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2" for="proveedor">Proveedor revisión:</label>
								<div class="col-sm-10">
									<p class="form-control-static" id="proveedor_precaptura"></p>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2" for="proveedor">Proveedor plantilla:</label>
								<div class="col-sm-8">
									<select class="form-control" id="selprovpre">
										<option value=""></option>
										<?php
										
										$sql="SELECT
											cve_pro,nom_pro,dir_pro,noe_pro,tax_pro
										FROM
											casa.ctrac_proved
										WHERE
											fec_baja is null
										ORDER BY
											nom_pro";
										$consulta = mysqli_query($cmysqli,$sql);
										if (!$consulta) {
											echo "<option>Error al consultar los proveedores ".mysqli_error($cmysqli)."</option>";
										}
										while($row = $consulta->fetch_object()){
											$nom_pro = $row->nom_pro;
											$cve_pro = $row->cve_pro;
											$dir_pro = $row->dir_pro.' '.$row->dir_pro;
											$tax_pro = $row->tax_pro;
											echo '<option value="'.$cve_pro.'">'.$nom_pro.' || '.$cve_pro.' || '.$dir_pro.' || '.$tax_pro.'</option>';
										}
										?>
									</select>
								</div>
							</div>
						</form>
					</row>
						<div class="table-responsive">
							<table id="dtprecaptura" class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th></th>
										<th>Cve&nbsp;Prov</th>
										<th>Numero&nbsp;Factura</th>
										<th>Fecha&nbsp;Factura</th>
										<th>Monto&nbsp;Fac</th>
										<th>Moneda</th>
										<th>Incoterm</th>
										<th>Subdivisión</th>
										<th>Cer&nbsp;Ori</th>
										<th>No&nbsp;Parte&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Origen</th>
										<th>Vendedor</th>
										<th>Fraccion&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Descripción&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Precio&nbsp;partida</th>
										<th>UMC</th>
										<th>Cant&nbsp;UMC(factura)</th>
										<th>Cant&nbsp;UMT(fisica)</th>
										<th>Preferencia</th>
										<th>Marca&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Modelo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Submodelo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>No&nbsp;parte/Serie&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Descricpión_COVE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th></th>
										<th>Cve&nbsp;Prov</th>
										<th>Numero&nbsp;Factura</th>
										<th>Fecha&nbsp;Factura</th>
										<th>Monto&nbsp;Fac</th>
										<th>Moneda</th>
										<th>Incoterm</th>
										<th>Subdivisión</th>
										<th>Cer&nbsp;Ori</th>
										<th>No&nbsp;Parte&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Origen</th>
										<th>Vendedor</th>
										<th>Fraccion&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Descripción&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Precio&nbsp;partida</th>
										<th>UMC</th>
										<th>Cant&nbsp;UMC(factura)</th>
										<th>Cant&nbsp;UMT(fisica)</th>
										<th>Preferencia</th>
										<th>Marca&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Modelo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Submodelo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>No&nbsp;parte/Serie&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
										<th>Descricpión_COVE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
									</tr>
								</tfoot>
							</table>
						</div>
					<h3>Leyenda de colores</h3>
					<table class="table table-condensed">
						<tr>
							<td class="danger"></td>
							<td>Fracción en anexo 10 sector 14 o 15 o con restricción de horario</td>
						</tr>
					</table>
				</div>
				<div class="modal-footer">
					<button id="btn_cancel1" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>					
				</div>
			</div>
		</div>
	</div>
	<div class="container">
	<?php include('nav.php');?>
		<!-- Main component for a primary marketing message or call to action -->
		<div class="jumbotron"> 
			<h2>Reportes <small>Generar plantilla avanzada COVE</small></h2>
			<br>
			<p>Seleccione el cliente y las referencias a exportar y presione Generar Layout</p>
			<br>
			<div class="row">
				<div class="col-md-12 text-center">
					<form class="form-inline" role="form">
						<div class="form-group">
							<label>Cliente</label>
							<select class="selectpicker clientes" data-style="btn-primary" id="selcliente" data-live-search="true">
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
			<div class="row">
				<div id="cargando"></div>
			</div>
			<br>
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12">
					<div class="form-group">
						<label for="referencias">Filtrar referencias (una por renglón):</label>
						<textarea class="form-control" rows="5" id="text_referencias"></textarea>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="table-responsive">
					Referencias en inventario que ya cuenta con revisión:
					<table id="plantilla_avanzada" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>Referencia</th>
								<th>Fecha entrada</th>
								<th>Proveedor</th>
								<th>Descripción</th>
								<th>Precaptura</th>
							</tr>
						</thead>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 text-center">
					<button type="button" class="btn btn-primary" id="btngenerar">Generar Layout</button> <button type="button" class="btn btn-primary" id="btnlimpiar">Limpiar selección</button>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-md-12 text-center">
					<div id="archivo"></div>
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
</body>
</html>