<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }

include("../connect_dbsql.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Clasificaciones Bodega</title>

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
	
	<!-- Custom styles for this template -->
    <link href="../bootstrap/css/navbar.css" rel="stylesheet">
	
	<!-- selectize CSS-->
	<link href="../bower_components/selectize/dist/css/selectize.css" rel="stylesheet">
	<link href="../bower_components/selectize/dist/css/selectize.bootstrap3.css" rel="stylesheet">

	<!-- FileInput -->
	<link href="../bower_components/bootstrap-fileinput-4.2.3/css/fileinput.min.css" rel="stylesheet"/>
	
	<style>
		.bg-green { background-color: #5cb85c;color: #FFF;}
		.bg-green-body { background-color: #F6FDF6}
		.def_app_textarea_height { height:150px !important; }
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

		<!-- SUBIR CATALOGO CAJAS UPLOAD LAYOUT -->
		<div class="modal fade" id="modalupload" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header bg-green">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><i class="fa fa-upload" aria-hidden="true"></i> Subir Layout Clasificaciones</h4>
					</div>
					<div class="modal-body bg-green-body">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Cliente</div>
										<input type="text" class="form-control" value="" id="txt_mdl_cliente" disabled>
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Proveedor</div>
										<input type="text" class="form-control" value="" id="txt_mdl_proveedor" disabled>
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="control-label"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Seleccionar archivo de Excel</label>
									<input id="archivo_xls_layout" type="file" class="file">
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<div id="mensaje_subir_layout"></div>
								</div>
							</div>
							<div class="col-md-12">
								<div id="mensaje_mod_subir"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer bg-green">
						<button id="btn_guardar_layout" type="button" class="btn btn-primary" OnClick="javascript:guardar_facturas_embarques(); return false;"><i class="fa fa-cogs" aria-hidden="true"></i> Procesar Archivo</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- SUBIR DOCUMENTOS -->
		<div id="modal_documentos" class="modal fade" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-file-o" aria-hidden="true"></i> Documentos</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12 col-md-12">
								<div class="form-group">
									<label class="control-label"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Seleccione Archivo(s)</label>
									<input id="ifile_documentos" name="ifile_documentos[]" type="file" class="file-loading" multiple=true>
								</div>				
							</div>
							<!--div class="col-xs-12">
								<div class="form-group">
									<button id="ibtn_documentos_subir" type="button" class="btn btn-success" style="float: right;" onclick="alert();"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Subir Documentos</button>
								</div>	
							</div-->
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div id="idiv_documentos_mensaje"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger pull-left" data-dismiss="modal" aria-label="Close"><i class="fa fa-ban" aria-hidden="true"></i> Salir</button>
					</div>
				</div>
			</div>
		</div>

		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Clasificaciones Bodega</strong>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<div id="mensaje_clasificaciones"></div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label><i class="fa fa-filter"></i> Cliente:</label>
								<select class="form-control" id="selcliente">
									<?php
										$sql="SELECT Cliente_id,Nom
											  FROM clientes
											  ORDER BY Nom";
										$consulta = mysqli_query($cmysqli,$sql);
										if (!$consulta) {
											echo "<option>Error al consultar los clientes".mysqli_error($cmysqli)."</option>";
										}
										while($row = $consulta->fetch_object()){
											echo '<option value="'.$row->Cliente_id.'">'.$row->Nom.'</option>';
										}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label><i class="fa fa-filter"></i> Proveedor:</label>
								<select class="form-control" id="selproveedor">
									<option value=0>TODOS</option>
									<?php
										$sql="SELECT proveedor_id,proNom
											  FROM procli
											  ORDER BY proNom";
										$consulta = mysqli_query($cmysqli,$sql);
										if (!$consulta) {
											echo "<option>Error al consultar los proveedores".mysqli_error($cmysqli)."</option>";
										}
										while($row = $consulta->fetch_object()){
											echo '<option value="'.$row->proveedor_id.'">'.$row->proNom.'</option>';
										}
									?>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">							
									<table id="dtclasificaciones" class="table table-striped table-bordered" width="100%">
										<thead>
											<tr>
												<th>ID</th>
												<th>Numero parte</th>
												<th>Origen</th>
												<th>Fraccion</th>
												<th>Descripción</th>
												<th>Descripción Ingles</th>
												<th>Unidad de medida</th>
												<th>Proveedor</th>
												<th>Cliente</th>
												<th>Clasificado</th>
												<th>Material</th>
												<th>Fundamento Legal</th>
												<th>Documentos</th>
												<th>Ficha de Clasificación</th>
												<th>Usuario</th>
												<th>Fecha</th>
												<th>Hora</th>
											</tr>
										</thead>
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
	
	<!-- Fileinput JS -->
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/fileinput.min.js"></script>
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/locales/es.js"></script>
	
	<script src="../js/clasificaBodega.js?2017.10.31.1805"></script>
</body>

</html>
