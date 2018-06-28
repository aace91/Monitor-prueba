<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
include('./../connect_gabdata.php');
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

    <title>Listado de Cuentas de Gastos</title>

	<!-- Bootstrap core CSS -->
    <link href="./../bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
	
	<!-- DataTables CSS -->
    <link href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet">
	
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

    
</head>
<body>
	<div class="container">
		<?php include('nav.php');?>
		
		<!-- MODAL CONFIRM -->
		<div id="modalenviar_cuenta_email" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Enviar PDF email</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<p>Selección de archivos para enviar</p>
									<label class="checkbox-inline" style="margin-left: 10px;">
										<input type="checkbox" value="pedimento">Pedimento
									</label>
									<label class="checkbox-inline">
										<input type="checkbox" value="hc">Hoja de Calculo
									</label>
									<label class="checkbox-inline">
										<input type="checkbox" value="mv">Manifestaci&oacute;n de Valor
									</label>
									<label class="checkbox-inline">
										<input type="checkbox" value="anexos">anexos
									</label>
									<label class="checkbox-inline">
										<input type="checkbox" value="xml" disabled="disabled">XML
									</label>
								</div>
							</div>
							<div class="col-xs-12">
								<hr style="margin-top: 0px; margin-bottom: 0px;"/>
								<div class="form-group">
									<div class="radio">
										<label><input type="radio" name="optradio_email" value="unico">Pdf unico</label>
									</div>
									<div class="radio">
										<label><input type="radio" name="optradio_email" value="separado">Pdf Separados</label>
									</div>
								</div>
							</div>
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Correos:</div>
										<input id="itxt_mdl_enviar_cuenta_email_correos" class="form-control" type="text" placeholder="Lista de Correos separados por ';'" maxlength="500"/>
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-md-12">
								<div class="form-group">
									<label class="control-label"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Seleccione Archivo(s)</label>
									<input id="ifile_documentos" name="ifile_documentos[]" type="file" class="file-loading" multiple/>
								</div>				
							</div>
							<div class="col-xs-12" id="idiv_mdl_enviar_cuenta_email_msj" style="display:none;"></div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="ibtn_mdl_enviar_cuenta_email_cancelar" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span> Cancelar</button>
						<button id="ibtn_mdl_enviar_cuenta_email_enviar" type="button" class="btn btn-success" onClick="ajax_enviar_cuenta_email();"><span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span> Enviar</button>
					</div>
				</div>
			</div>
		</div>
		
		<div id="modalEnvio1" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Enviar Cuentas x Correo</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<label class="checkbox-inline" style="margin-left: 10px;">
										<input type="checkbox" id="mdlEnvio1OmiteClientes">Omitir correos del cliente
									</label>
								</div>
							</div>
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">CC:</div>
										<input id="mdlEnvio1Correos" class="form-control" type="text" placeholder="Lista de Correos separados por ';'" maxlength="500"/>
									</div>
								</div>
							</div>
							<div class="col-xs-12" id="mdlEnvioDivMsg1" style="display:none;"></div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="mdlEnvio1Cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span> Cancelar</button>
						<button id="mdlEnvio1Enviar" type="button" class="btn btn-success" onClick="ajax_envio1_cuentas_sel();"><span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span> Enviar</button>
					</div>
				</div>
			</div>
		</div>
		
		<div id="modalEnvioListPen" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Enviar Listado de Cuentas Pendientes</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Para:</div>
										<input id="mdlEnvioListPenCorreos" class="form-control" type="text" placeholder="Lista de Correos separados por ';'" maxlength="500"/>
									</div>
								</div>
							</div>
							<div class="col-xs-12" id="mdlEnvioListPenDivMsg1" style="display:none;"></div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="mdlEnvioListPenCancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span> Cancelar</button>
						<button id="mdlEnvioListPenEnviar" type="button" class="btn btn-success" onClick="ajax_enviar_list_pendientes();"><span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span> Enviar</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Main component for a primary marketing message or call to action -->
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<h2>Cuentas de gastos <small>Listado</small></h2>
				</div>
				<div id="div_panel_principal" class="panel-body">
					<div class="row">
						<div class="col-xs-12" id="idiv_mensaje" style="display:none;"></div>
					
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
												echo '<option value="'.$row['no_cte'].'">'.$row['nombre'].'|'.$row['no_cte'].'</option>';
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
										<option value="PEN">Pendientes</option>
										<option value="PAG">Pagadas</option>
										<option value="T" selected>Todas</option>
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
					<div class="table-responsive">
						<table id="ctamex" class="table" cellspacing="0" width="100%">
							<thead style="background-color:#3071AA; color:#FFF;">
								<tr>
									<th></th>
									<th></th>
									<th>#Cuenta</th>
									<th>Estatus de envio</th>
									<th>Trafico/Referencia</th>
									<th>Fecha</th>
									<th>XML</th>
									<th>PDF</th>
									<th>Aduana</th>
									<th>Pedimentos</th>
									<th>Edocuments</th>
									<th>Anexos</th>
									<th>Cta. Ame</th>
									<th>Enviar</th>
									<th>Saldo pendiente</th>
									<th>Pedimento master</th>
									<th>Pedimentos 2</th>
									<th>Estatus Pago</th>
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
	<script src="../bower_components/datatables.net-select/js/dataTables.select.min.js"></script>
	
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
	<script type="text/javascript" src="../js/listctamex.js?v=2018.01.01.1000"></script>
</body>
</html>