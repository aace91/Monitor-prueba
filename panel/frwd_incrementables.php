<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Monitor - Incrementables</title>

    <!-- Bootstrap Core CSS -->
	<link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	 
	<!-- Custom Fonts -->
	<link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

	<!-- DataTables CSS -->
    <link href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-fixedcolumns-bs/css/fixedColumns.bootstrap.min.css" rel="stylesheet">
	<!--link href="../editor/css/editor.bootstrap.min.css" rel="stylesheet">
	<link href="../editor/css/editor.selectize.css" rel="stylesheet"-->	
	<!--link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet"/-->
	
	<!-- Custom styles for this template -->
	<link href="../bootstrap/css/navbar.css" rel="stylesheet">

	<!-- Select2 CSS -->
	<link href="../bower_components/select2/dist/css/select2.min.css" rel="stylesheet" />
	<link href="../bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css" rel="stylesheet" />
	
	<!-- Switch -->
	<link href="../bower_components/switch/css/style_switch.css" rel="stylesheet" type="text/css">
	
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

	<style>
		a:hover { cursor:pointer; }

		div.note-editable p { font-size: 14px !important; }
		ul.dropdown-style li a p { font-size: 14px !important; }
	</style>
</head>

<body>
    <div class="container">

        <?php require('nav.php'); ?>
		
		<input type="hidden" id="itxt_data" data-app_data="<?php
			$aAppData = array(
				'sId' => $id
			);
				
			$respuesta['aAppData']=$aAppData;
			echo htmlspecialchars(json_encode($respuesta), ENT_QUOTES, 'UTF-8');
		?>"/>
		
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
		
		<!-- MODAL CREAR CRUCE -->
		<div id="modal_nuevo_cruce" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-file-o" aria-hidden="true"></i> Nuevo Cruce</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Cliente</span>
										<select id="isel_nvo_cruce_clientes" class="form-control">
											<?php
												include('./../connect_dbsql.php');

												$consulta = "SELECT bodCli.Cliente_id, bodCli.Nom
															 FROM facturacion.clientes AS facCli INNER JOIN
																  bodega.clientes AS bodCli ON bodCli.Cliente_id=facCli.id_cliente
															 ORDER BY bodCli.Nom";
												
												$query = mysqli_query($cmysqli, $consulta);
												
												if (!$query) {
													$error=mysqli_error($cmysqli);
													echo '<option value="">Error al cargar catalodo de transportes: '.$error.'</option>';
												} else {
													echo '<option value=""></option>';
													while($row = mysqli_fetch_array($query)){
														echo '<option value="'.$row['Cliente_id'].'">'.$row['Nom'].'</option>';												
													}
												}
											?>
										</select>
										<span class="input-group-btn">
											<button id="ibtn_nvo_cruce_select_client" class="btn btn-success" type="button" onclick="fcn_crear_cruce_select_client();">
												<i class="fa fa-check" aria-hidden="true"></i>
											</button>
										</span>
									</div>
								</div>
							</div>

							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Remisión</span>
										<input id="itxt_mdl_nvo_cruce_remision" class="form-control integer" type="text" maxlength="11" disabled="disabled">
										<span class="input-group-btn">
											<button id="ibtn_nvo_cruce_add_remision" class="btn btn-primary" type="button" onclick="ajax_get_agregar_remision();" disabled="disabled">
												<i class="fa fa-plus" aria-hidden="true"></i>
											</button>
										</span>
									</div>
								</div>
							</div>

							<div class="col-xs-12">
								<div class="dataTable_wrapper">
									<div class="table-responsive" style="overflow:hidden;">
										<table id="dt_remisiones" class="table table-striped table-bordered" width="100%">
											<thead>
												<tr>
													<th>Remisión</th>
													<th style="width:50px;"></th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>

							<div class="col-xs-12 col-md-9">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">Tipo de Transporte</span>
										<select id="isel_mdl_nvo_cruce_tipo_transporte" class="form-control" onchange="fcn_change_tipo_transporte();" disabled="disabled">
											<?php
												include('./../connect_dbsql.php');
												
												$consulta = "SELECT id_transporte, nombre
															 FROM facturacion.inc_tipo_transporte
															 ORDER BY orden";
												
												$query = mysqli_query($cmysqli, $consulta);
												
												if (!$query) {
													$error=mysqli_error($cmysqli);
													echo '<option value="">Error al cargar catalodo de transportes: '.$error.'</option>';
												} else {
													echo '<option value="">Seleccione un tipo</option>';
													while($row = mysqli_fetch_array($query)){
														echo '<option value="'.$row['id_transporte'].'">'.$row['nombre'].'</option>';												
													}
												}
											?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-md-3">
								<div class="form-group">
									<div class="input-group pull-right">
										<label class="checkbox-inline"><input id="ickb_mdl_nvo_cruce_hazmat" type="checkbox" value="">Hazmat</label>
									</div>
								</div>
							</div>

							<div id="idiv_honorarios" class="col-xs-12">
								<div class="form-group">
									<label><i class="fa fa-check-circle" aria-hidden="true"></i> Selección de Honorarios</label>
									<div id="idiv_honorarios_radios"></div>
								</div>	
							</div>

							<div class="col-xs-12">
								<div id="idiv_mdl_nvo_cruce_mensaje"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="row">
							<div class="col-xs-12">
								<div class="form-group">
									<div class="input-group pull-right">	
										<button id="ibtn_mdl_nvo_cruce_crear_cruce" type="button" class="btn btn-success" onClick="javascript:ajax_set_crear_cruce();"><i class="fa fa-plus-circle" aria-hidden="true"></i> Crear Cruce</button>
									</div>	
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Incrementables</strong>			
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-12">
							<div id="idiv_bwsr_mensaje"></div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12">
							<div class="dataTable_wrapper">
								<div class="table-responsive" style="overflow:hidden;">
									<table id="dt_incrementables" class="table table-striped table-bordered" width="100%">
										<thead>
											<tr>
												<th style="width:30px;">ID CRUCE</th>
												<th style="width:120px;">Fecha</th>
												<th>Cliente</th>
												<th style="width:40px;">Remisiones</th>
												<th>Ejecutivo</th>
												<th style="width:40px;">Total</th>
												<th style="width:100px;"></th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th style="width:30px;">ID CRUCE</th>
												<th style="width:120px;">Fecha</th>
												<th>Cliente</th>
												<th style="width:40px;">Remisiones</th>
												<th>Ejecutivo</th>
												<th style="width:40px;">Total</th>
												<th style="width:100px;"></th>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div id="idiv_panel_secundario" class="panel panel-default" style="margin-bottom: 0px; display:none;">
				<div class="panel-heading">
					<strong> Incrementables</strong>				
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-lg-12">
							<div id="idiv_mensaje"></div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-lg-12">
							<div id="idiv_mensaje_inc_conceptos" style="display:none;">
								<div class="alert alert-warning" style="margin-bottom: 5px;">
									<strong>Atenci&oacute;n!</strong> Este es un calculo aproximado, se pueden aplicar los siguientes incrementables durante el proceso de carga en bodega <a href="#idiv_inc_conceptos" class="alert-link" data-toggle="collapse">Ver conceptos</a>.
								</div>
								<div id="idiv_inc_conceptos" class="collapse alert alert-info">
									Lorem ipsum dolor text....
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12 col-sm-6">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon">Tipo Transporte</span>
									<input id="itxt_cruce_trans_tipo" class="form-control" type="text" disabled="disabled">
								</div>
							</div>
						</div>
					</div>
					<!-- /.row -->

					<div class="row" id="idiv_cruce_remisiones"></div>
					<!-- /.row -->

					<div class="row" id="idiv_cruce_ltl"></div>

					<div class="row">
						<div class="col-lg-12">
							<div id="idiv_mensaje_errors"></div>
						</div>
					</div>
				</div>
					
				<div class="panel-footer">
					<button type="button" class="btn btn-danger" onclick="fcn_close_detalles();"><i class="fa fa-ban"></i> Salir</button>
					<button id="ibtn_recalcular_incrementables" type="button" class="btn btn-primary pull-right" onclick="ajax_get_recalcular_incrementable();" data-toggle="tooltip" title="Esta opci&oacute;n de debe usar cuando se agrege o elimine una referencia de la remisi&oacute;n, este proceso realiza nuevamente el calculo de incrementables!"><i class="fa fa-calculator"></i> Recalcular Incrementables</button>
				</div>
			</div>
		</div>
    </div>
    <!-- /#wrapper -->

	<script src="../bower_components/json3/lib/json3.min.js"></script>
	
	<!--script src="../bower_components/wysihtml5/dist/wysihtml5-0.3.0.js"></script-->
	
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
	<script src="../bower_components/datatables.net-fixedcolumns/js/dataTables.fixedColumns.min.js"></script>
	<!--script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	<script src="../editor/js/editor.selectize.js"></script-->

	<script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]-->
	
	<!-- Select2 -->
    <script type="text/javascript" src="../bower_components/select2/dist/js/select2.min.js"></script>
	<script type="text/javascript" src="../bower_components/select2/dist/js/i18n/es.js"></script>

	<!--Numeric-->
	<script  type="text/javascript" language="javascript" src="../plugins/numeric/jquery.numeric.js"></script>

	<script src="../js/frwd_incrementables.js?v=2018.06.29.1340"></script>
</body>

</html>
