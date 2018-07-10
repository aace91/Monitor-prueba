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
		<meta name="description" content="login monitor">
		<meta name="author" content="Abisai Cruz">
		<!--link rel="icon" href="../../favicon.ico"-->

		<title>Monitor</title>

		<!-- Bootstrap core CSS -->
		<link href="./../bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<!--link href="./../bootstrap/css/jquery.dataTables.css" rel="stylesheet"/-->
		<link href="./../bootstrap/css/buttons.bootstrap.min.css" rel="stylesheet"/>
		<link href="./../bootstrap/css/dataTables.bootstrap.css" rel="stylesheet"/>
		<!--link href="./../bootstrap/css/dataTables.tableTools.min.css" rel="stylesheet"/>
		<link href="./datatablestools.css" rel="stylesheet"/-->
		<link href="./../bootstrap/css/fixedColumns.dataTables.min.css" rel="stylesheet"/>
		
		<!-- Custom Fonts -->
		<link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

		<link href="../datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
		
		<link href="./../bootstrap/css/bootstrap-select.min.css" rel="stylesheet"/>
		
		<!-- Custom styles for this template -->
		<link href="./../bootstrap/css/navbar.css" rel="stylesheet">
		
		<!-- FileInput -->
		<link href="../bower_components/bootstrap-fileinput-4.2.3/css/fileinput.min.css" rel="stylesheet"/>

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

		<!-- Fileinput JS -->
		<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/fileinput.js"></script>
		<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/locales/es.js"></script>
		
		<script type="text/javascript" language="javascript" class="init" src="table.js?2018.07.04.1326"></script>
		<script type="text/javascript" language="javascript" src="operaciones.js?2018.06.22.1348"></script>
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
		<!--Modal Remesa o Pedimento Documentos -->
		<div id="modal_remped_documentos" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-file-o" aria-hidden="true"></i> Documentos</h4>
					</div>
					<div class="modal-body">
						<div id="idiv_mdl_remped_docs_detalle" class="row" style="display:none;">
							<div id="idiv_mdl_remped_docs_ver_documento" class="col-xs-12" style="display:block;">
								<ol class="breadcrumb">
									<li class="breadcrumb-item active">Documento: <strong><span></span></strong></li>
									<button type="button" class="btn btn-primary btn-xs pull-right" onclick="fcn_remped_docs_ops('browser');"><i class="fa fa-reply" aria-hidden="true"></i> Regresar</button>
								</ol>
							</div>
							<div class="col-xs-12 cls_remped_detalle"></div>
							<div id="idiv_mdl_remped_docs_obs" class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones</div>
										<input id="itxt_mdl_remped_docs_obs" type="text" maxlength="500" class="form-control text-uppercase" disabled="disabled">
									</div>
								</div>
							</div>
						</div>
						
						<div id="idiv_mdl_remped_docs_table" class="row" style="display:block;">
							<div class="col-xs-12">
								<div class="form-group">
									<button type="button" class="btn btn-primary" onclick="fcn_remped_docs_ops('subir_archivos');"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Subir Archivos</button>
								</div>
							</div>
							<div class="col-xs-12">
								<table id="itable_remped_docs" class="table table-bordered table-striped">
									<thead>
									<tr>
										<th style="text-align: center;">Tipo</th>
										<th style="text-align: center;">Nombre</th>
										<th style="text-align: center;">Fecha Documento</th>
										<th style="text-align: center;">Estatus</th>
										<th></th>
									</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
						</div>

						<div id="idiv_mdl_remped_docs_upload" class="row" style="display:none;">
							<div class="col-xs-12">
								<ol class="breadcrumb">
									<li class="breadcrumb-item active">Subir documentos</strong></li>
									<button type="button" class="btn btn-primary btn-xs pull-right" onclick="fcn_remped_docs_ops('browser');"><i class="fa fa-reply" aria-hidden="true"></i> Regresar</button>
								</ol>
							</div>
							<div class="col-xs-12 col-md-12">
								<div class="form-group">
									<label class="control-label"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Seleccione Archivo(s)</label>
									<input id="ifile_remped_docs" type="file" name="ifile_remped_docs[]" multiple>
								</div>				
							</div>
							<div class="col-xs-12">
								<div class="form-group">
									<button id="ibtn_reped_docs_subir" type="button" class="btn btn-success" style="float: right;" onclick="fcn_remped_docs_subir();"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Subir Documentos</button>
								</div>	
							</div>
						</div>
						
						<div id="idiv_mdl_remped_docs_ver" class="row" style="display:none;">
							<div id='iembed_mdl_remped_docs_pdf_archivo' class="col-md-12">
								<!--canvas id="the-canvas"></canvas-->
								<iframe id="pdfViewer_mdl_remped_docs_pdf_archivo" src="./../bower_components/pdfjs/web/viewer.html" style="width: 100%; height: 700px; display: none;" allowfullscreen="" webkitallowfullscreen=""></iframe>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div id="idiv_remped_docs_mensaje"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger pull-left" data-dismiss="modal" aria-label="Close"><i class="fa fa-ban" aria-hidden="true"></i> Salir</button>
					</div>
				</div>
			</div>
		</div>

		<!--Modal Documentos -->
		<div id="modal_documentos" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-file-o" aria-hidden="true"></i> Documentos</h4>
					</div>
					<div class="modal-body">
						<div id="idiv_mdl_docs_table" class="row" style="display:block;">
							<div class="col-xs-12">
								<table id="itable_mdl_docs" class="table table-bordered table-striped">
									<thead>
									<tr>
										<th style="text-align: center;">Tipo</th>
										<th style="text-align: center;">Nombre</th>
										<th style="text-align: center;">Estatus</th>
										<th></th>
									</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
						</div>
						
						<div id="idiv_mdl_docs_aprobar" class="row" style="display:none;">
							<div class="col-xs-12">
								<ol class="breadcrumb">
									<li class="breadcrumb-item active">Documento: <strong><span id="ispan_mdl_docs_aprobar_documento"></span></strong></li>
								</ol>
							</div>
							
							<div class="col-xs-12">
								<div class="form-group">
									<button type="button" class="btn btn-primary" onclick="fcn_docs_ops('browser');"><i class="fa fa-reply" aria-hidden="true"></i> Regresar</button>
									<button type="button" class="btn btn-danger cls_aprobar_btn" style="margin-left: 40px; float: right;" onclick="fcn_docs_ops('rechazar');"><i class="fa fa-times-circle" aria-hidden="true"></i> Rechazar</button>
									<button type="button" class="btn btn-success cls_aprobar_btn" style="float: right;" onclick="fcn_validacion_aprobar();"><i class="fa fa-check-circle" aria-hidden="true"></i> Aprobar</button>
								</div>	
							</div>
							
							<div id='iembed_pdf_archivo' class="col-md-12">
								<!--canvas id="the-canvas"></canvas-->
								<iframe id="pdfViewer_pdf_archivo" src="./../bower_components/pdfjs/web/viewer.html" style="width: 100%; height: 700px; display: none;" allowfullscreen="" webkitallowfullscreen=""></iframe>
							</div>
						</div>
						
						<div id="idiv_mdl_docs_rechazar" class="row" style="display:none;">
							<div class="col-xs-12">
								<div class="form-group">
									<button type="button" class="btn btn-primary" onclick="fcn_docs_ops('aprobar');"><i class="fa fa-reply" aria-hidden="true"></i> Regresar</button>
								</div>	
							</div>
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Raz&oacute;n de Rechazo</div>
										<select id="isel_mdl_docs_razon" class="form-control">
											<?php
												$consulta="SELECT id_estatus_documento, descripcion_es, descripcion_us
														FROM estatus_documentos
														WHERE ok = 0 AND 
																pendiente=0";
												$query = mysqli_query($cmysqli,$consulta);
												$number = mysqli_num_rows($query);
												if($number > 1){
													while($row = mysqli_fetch_array($query)){
														echo '<option value="'.$row['id_estatus_documento'].'" data-descripcion_us="'.$row['descripcion_us'].'">'.$row['descripcion_es'].'</option>';
													}
												}
											?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones</div>
										<input id="itxt_mdl_docs_observaciones" type="text" maxlength="500" class="form-control text-uppercase">
									</div>
								</div>
							</div>
							
							<div class="col-xs-12">
								<div id="idiv_mdl_docs_rechazar_btns" class="form-group text-right">
									<button type="button" class="btn btn-danger" style="margin-left: 40px;" onclick="fcn_validacion_rechazar();"><i class="fa fa-times-circle" aria-hidden="true"></i> Rechazar</button>
								</div>	
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<div id="idiv_mdl_docs_mensaje"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close"><i class="fa fa-ban" aria-hidden="true"></i> Salir</button>
					</div>
				</div>
			</div>
		</div>
		
		<!--Modal Windows-->
		<div class="modal fade" id="modificaefac" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Modifica estatus factura</h4>
					</div>
					<div class="modal-body">
						<form>
							<div class="form-group">
								<label>Estatus factura</label>
									<select data-tabindex="1" id="selefac" class="form-control">
									<!--option value="0" selected="selected">ESTATUS PENDIENTE</option-->
									<?php
										$consulta="SELECT id_estatus_factura,descripcion FROM `estatus_factura` order by descripcion";
										$query = mysqli_query($cmysqli,$consulta);
										$number = mysqli_num_rows($query);
										if($number > 1){
											while($row = mysqli_fetch_array($query)){
												echo '<option value="'.$row['id_estatus_factura'].'">'.$row['descripcion'].'</option>';
											}
										}
									?>
								</select>
							</div>
							<input type="hidden" id="efac_ref" value="">
						</form>
					</div>
					<div class="modal-footer">
						<div id="mensajeefac"></div>
						<button type="button" class="btn btn-primary" id="btnguardarefac" onclick="guardaefac();return false;" data-tabindex="4">Guardar</button>
					</div>
				</div>
			</div>
		</div>

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
									<label>Fecha incial:</label>
									<div class="input-append date" id="fechaini" data-date-format="dd/mm/yyyy">
									<input type="text" id="fechaini1" class="form-control" value="" readonly>
									<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
									</div>
							</div>
							<div class="form-group">
									<label>Fecha final:</label>
									<div class="input-append date" id="fechafin" data-date-format="dd/mm/yyyy">
									<input type="text" id="fechafin1" class="form-control" value="" readonly>
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
							<th>Bultos</th>
							<th>Fotos Entrada</th>
							<th>Fotos Adicionales</th>
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
							<th>Tiempo en bodega</th>
							<th>Caja</th>
							<th>Guía</th>
							<th>Linea</th>
							<th>PO</th>
							<th>Subguias</th>
							<th>Fac. Master</th>
							<th>NOM</th>
							<th>Cer. Origen</th>
							<th>Estatus Factura</th>
							<th>Ver comentarios/estatus</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th>Referencia</th>
							<th>Documentación</th>
							<th>Bultos</th>
							<th>Fotos Entrada</th>
							<th>Fotos Adicionales</th>
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
							<th>Tiempo en bodega</th>
							<th>Caja</th>
							<th>Guía</th>
							<th>Linea</th>
							<th>PO</th>
							<th>Fac. Master</th>
							<th>NOM</th>
							<th>Cer. Origen</th>
							<th>Estatus Factura</th>
							<th>Ver comentarios/estatus</th>
						</tr>
					</tfoot>
				</table>
			</div>
		
			<strong>Leyenda referencia</strong>
			<br><br>
			<div id="leyendas">
				<table>
				<tr>
					<td class="col-md-1" bgcolor="#FF9999"></td>
					<td>&nbsp;&nbsp;</td>
					<td><strong>Despacho Urgente</strong></td>
				<tr>
				</table>
			</div>
			<br>

			<strong>Leyenda general</strong>
			<br><br>
			<div id="leyendas">
				<table>
				<tr>
					<td class="col-md-1" bgcolor="#FFFF99"></td>
					<td>&nbsp;&nbsp;</td>
					<td><strong>Menos de 4 Horas</strong></td>
					<td>&nbsp;&nbsp;</td>
					<td class="col-md-1" bgcolor="#FF9999"></td>
					<td>&nbsp;&nbsp;</td>
					<td><strong>Mas de 4 Horas y menos de 12 Horas</strong></td>
					<td>&nbsp;&nbsp;</td>
					<td class="col-md-1" bgcolor="#FF99FF"></td>
					<td>&nbsp;&nbsp;</td>
					<td><strong>Mas de 12 Horas</strong></td>
				<tr>
				</table>
			</div>
			<br>

			<ul class="nav nav-tabs">
			<li id="tabpedimento" role="presentation" class="active"><a href="javascript:void(0);" onclick="consultapedimento(pedimentog);return false;">Datos Pedimento</a></li>
			<!--li id="tabremesa" role="presentation"><a href="javascript:void(0);" onclick="consultaremesa('','');return false;">Datos Remesa</a></li-->
			<li id="tabcomentarios" role="presentation"><a href="javascript:void(0);" onclick="consultacomref('','');return false;" >Comentarios</a></li>
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