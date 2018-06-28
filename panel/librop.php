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

    <title>Libro de Pedimentos</title>

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
	
	<!--link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet"/-->
	
    <!-- Custom styles for this template -->
    <link href="../bootstrap/css/navbar.css" rel="stylesheet">
	
	<!-- selectize CSS-->
	<link href="../bower_components/selectize/dist/css/selectize.css" rel="stylesheet">
	<link href="../bower_components/selectize/dist/css/selectize.bootstrap3.css" rel="stylesheet">
	
	<!-- datepicker -->
	<link href="../datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
    
	
	<link rel="icon" type="image/ico" href="../favicon.ico" />
	
	<style>
		#isec_reporte { display: none; }
	
	
		.def_app_right{
		  text-align: right;
		}

		.def_app_left{
		  text-align: left;
		}

		.def_app_center{
		  text-align: center !important;
		}
	</style>
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php');?>
		
		<!-- NUMERO DE PEDIMENTO -->
		<div id="modal_mostrar_pedimento" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Pedimento Asignado</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12">
								<div id="idiv_mensaje_pedimento_generado"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success" data-dismiss="modal"> Ok </button>
					</div>
				</div>
			</div>
		</div>
		<!-- MODAL NUEVO PEDIMENTO -->
		<div id="modal_add_pedimento" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-file-text-o" aria-hidden="true"></i> <span id="lbl_modal_titulo_pedimento"></span></h4>
					</div>
					<div class="modal-body">								
						<div class="row">
							<div class="col-xs-6 col-md-2 text-left">
								<div class="form-group">
									<label>Año:</label>
									<select id="isel_pedi_mod_anio" class="form-control select2">
										<option value="2018">2018</option>
										<option value="2017" selected>2017</option>
										<option value="2016">2016</option>
									</select>
								</div>
							</div>
							<div class="col-xs-6 col-md-4 text-left">
								<div class="form-group">
									<label>Aduana:</label>
									<select id="isel_pedi_mod_aduana" class="form-control select2">
										<option value="1" selected>800 - COLOMBIA NUEVO LEON, MX.</option>
										<option value="2">240 - NUEVO LAREDO TAMAULIPAS, MX.</option>
									</select>
								</div>					
							</div>
							<div class="col-xs-12 col-md-6 text-left">
								<div class="form-group">
									<label>Patente:</label>
									<!--select id="isel_pedi_mod_patente" class="form-control select2">
										<option value="1664" selected>1664 - HUGO NISHIYAMA DE LA GARZA</option>
										<option value="3483">3483 - MANUEL JOSE ESTANDIA FERNANDEZ</option>
									</select-->
									<select id="isel_pedi_mod_patente" class="form-control select2">
										<?php
											include('./../connect_dbsql.php');
											$consulta = "SELECT patente,nombre
														 FROM librop_patentes
														 ORDER BY patente";				
											$query = mysqli_query($cmysqli,$consulta);
											while($row = mysqli_fetch_array($query)){
												echo '<option value="'.$row['patente'].'">'.$row['patente'].' - '.$row['nombre'].'</option>';
											}
										?>
									</select>
								</div>					
							</div>
							
							<div class="col-xs-6 col-md-6 text-left">
								<div class="form-group">
									<label>Referencia:</label>
									<input id="itxt_pedi_mod_referencia" class="form-control" type="text" onKeyUp="toUpper(this)" maxlength="10">
								</div>
							</div>
							<div class="col-xs-6 col-md-6 text-left">
								<div class="form-group">
									<label>Fecha (dd/mm/yyyy):</label>
									<div class="input-append date input-group" id="idt_pedi_mod_fecha" data-date-format="dd/mm/yyyy">									
										<input type="text" id="fecha_pedimento" class="form-control" value="" readonly>
										<span class="input-group-addon">
											<span class="add-on"><i class="glyphicon glyphicon-calendar"></i></span>
										</span>
									</div>
								</div>					
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 text-left">
								<div class="form-group">
									<label>Cliente:</label>
									<select id="isel_pedi_mod_cliente" class="form-control">
									<?php
										require('./../connect_casa.php');
										
										$sQuery = "SELECT CVE_IMP,NOM_IMP FROM ctrac_client ORDER BY NOM_IMP";
										$result = odbc_exec ($odbccasa, $sQuery) or die(odbc_error());
										if ($result!=false){
											$sHTML = '<option value="0">[SELECCIONAR CLIENTE]</option>';
											while(odbc_fetch_row($result)){
												$sHTML .= '<option value="'.odbc_result($result,"CVE_IMP").'">'. odbc_result($result,"NOM_IMP"). '</option>';
											}		
										}else{
											$sHTML .= '<option value="0">'.odbc_error().'</option>';
										}
										echo $sHTML;
									?>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 col-md-5 text-left">
								<div class="form-group">
									<label>Operación:</label>
									<select id="isel_pedi_mod_operacion" class="form-control select2">
										<option value="1" selected>IMPORTACIÓN</option>
										<option value="2">EXPORTACIÓN</option>
									</select>
								</div>
							</div>
							<div class="col-xs-12 col-md-2 text-left">
								<div class="form-group">
									<label>Cve Pedimento:</label>
									<input id="itxt_pedi_mod_cve_pedimento" class="form-control" type="text" onKeyUp="toUpper(this)" maxlength="2">
								</div>					
							</div>
							<div class="col-xs-12 col-md-5 text-left">
								<div class="form-group">
									<label>Usuario:</label>
									<?php
										include_once('./../checklogin.php');
										echo '<input class="form-control" value="'.$username.'" type="text" onKeyUp="toUpper(this)"  disabled>';
									?>
								</div>					
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 text-left">
								<div class="form-group">
									<label>Descripción Mercancía:</label>
									<input id="itxt_pedi_mod_desc_mercancia" class="form-control" type="text" onKeyUp="toUpper(this)" maxlength="255">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 text-left">
								<div class="form-group">
									<label>Observaciones:</label>
									<textarea class="form-control" rows="4" id="itxt_pedi_mod_observaciones"  onKeyUp="toUpper(this)"></textarea>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div id="idiv_modal_mensaje_pedimento"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="row">
							<div class="col-xs-12 col-md-12">
								<div class="form-group">
									<button type="button" class="btn btn-primary" onClick="javascript:guardar_nuevo_pedimentos();"><i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	
		<!-- MODAL NUEVO RANGO -->
		<div id="modal_add_rango" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><i class="fa fa-arrows-v" aria-hidden="true"></i> Generar nuevo rango</h4>
					</div>
					<div class="modal-body">								
						<div class="row">
							<div class="col-xs-6 col-md-2 text-left">
								<div class="form-group">
									<label>Año:</label>
									<select id="isel_rango_mod_anio" class="form-control select2" >
										<option value="2018">2018</option>
										<option value="2017" selected>2017</option>
										<option value="2018">2016</option>
									</select>
								</div>
							</div>
							<div class="col-xs-6 col-md-4 text-left">
								<div class="form-group">
									<label>Aduana:</label>
									<select id="isel_rango_mod_aduana" class="form-control select2">
										<option value="1" selected>800 - COLOMBIA NUEVO LEON, MX.</option>
										<option value="2">240 - NUEVO LAREDO TAMAULIPAS, MX.</option>
									</select>
								</div>					
							</div>
							<div class="col-xs-12 col-md-6 text-left">
								<div class="form-group">
									<label>Patente:</label>
									<!--select id="isel_rango_mod_patente" class="form-control select2">
										<option value="1664" selected>1664 - HUGO NISHIYAMA DE LA GARZA</option>
										<option value="3483">3483 - MANUEL JOSE ESTANDIA FERNANDEZ</option>
									</select-->
									<select  id="isel_rango_mod_patente" class="form-control select2">
										<?php
											include('./../connect_dbsql.php');
											$consulta = "SELECT patente,nombre
														 FROM librop_patentes
														 ORDER BY patente";				
											$query = mysqli_query($cmysqli,$consulta);
											while($row = mysqli_fetch_array($query)){
												echo '<option value="'.$row['patente'].'">'.$row['patente'].' - '.$row['nombre'].'</option>';
											}
										?>
									</select>
								</div>					
							</div>
						</div>
						<div class="row">
							<div class="col-xs-6 text-left">
								<div class="form-group">
									<label>Pedimento Incial:</label>
									<input id="itxt_rango_mod_ped_ini" class="form-control" type="text" maxlength="7">
								</div>
							</div>
							<div class="col-xs-6 text-left">
								<div class="form-group">
									<label>Pedimento Final:</label>
									<input id="itxt_rango_mod_ped_fin" class="form-control" type="text" maxlength="7">
								</div>
							</div>
						</div>						
						<div class="row">
							<div class="col-xs-12 text-left">
								<div class="form-group">
									<label>Observaciones:</label>
									<textarea class="form-control" rows="4" id="itxt_rango_mod_observaciones"  onKeyUp="toUpper(this)"></textarea>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div id="idiv_rango_mod_mensaje" ></div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="row">
							<div class="col-xs-12 col-md-12">
								<div class="form-group">
									<button type="button" class="btn btn-primary" onClick="javascript:ajax_set_nuevo_rango();"><i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar</button>
									<!--button id="ibtn_modal_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Cancelar</button-->					
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	
		
		<!--div class="jumbotron"-->
		<div class="row">	
			<div class="col-xs-12">
				<ul class="nav nav-tabs" role="tablist" id="librop_tabs">
					<li class="active"><a href="#pedimentos" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-book" aria-hidden="true"></i> Libro de Pedimentos</a></li>
					<li role="presentation"><a href="#rangos" aria-controls="settings" role="tab" data-toggle="tab"><i class="fa fa-arrows-v" aria-hidden="true"></i> Rangos</a></li>
				</ul>
				<div class="tab-content">
					<div id="pedimentos" class="tab-pane active">
						<div class="panel panel-default">
							<div class="panel-heading">&nbsp;</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-12 col-md-12">
										<div class="form-group text-right">
											<button id="ibtn_nuevo_pedimento" type="button" class="btn btn-primary" onClick="javascript:fcn_nuevo_pedimento_open();"><i class="fa fa-plus" aria-hidden="true"></i> Nuevo Número de Pedimento</button>					
										</div>
									</div>
								</div>
								<div class="row"><div class="col-xs-12 col-md-12">&nbsp;</div></div>
								<div class="row"><div class="col-xs-12 col-md-12"><div id="mensaje_librop_editar"></div></div></div>
								<div class="row">
									<div class="col-xs-12">
										<div class="dataTable_wrapper">
											<div class="table-responsive" style="overflow:hidden;">
												<table id="dtpedimentos" class="table table-striped table-bordered" width="100%">
													<thead>
														<tr>
															<th class="def_app_center">#</th>
															<th class="def_app_center">ACCIONES</th>
															<th class="def_app_center">ADUANA</th>
															<th class="def_app_center">PATENTE</th>
															<th class="def_app_center">PEDIMENTO</th>
															<th class="def_app_center">REFERENCIA</th>
															<th class="def_app_center">AÑO</th>
															<th class="def_app_center">CLIENTE</th>
															<th class="def_app_center">OPERACIÓN</th>
															<th class="def_app_center">CVE. PEDIMENTO</th>
															<th class="def_app_center">DESCRIPCIÓN MERCANCÍA</th>
															<th class="def_app_center">OBSERVACIONES</th>
														</tr>
													</thead>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="panel-footer text-right">
								<small>Monitor de Referencias &reg; 2017 &copy; Grupo Aduanero Del Bravo S.A. de C.V.</small>
							</div>
						</div>
					</div>
					<div id="rangos" class="tab-pane">
						<div class="panel panel-default">
							<div class="panel-heading">&nbsp;</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-12 col-md-12">
										<div class="form-group text-right">
											<button id="ibtn_nuevo_rango" type="button" class="btn btn-primary" onClick="javascript:fcn_nuevo_rango_open();"><i class="fa fa-plus" aria-hidden="true"></i> Nuevo Rango</button>					
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-12">
										<div class="dataTable_wrapper">
											<div class="table-responsive" style="overflow:hidden;">
												<table id="dtrangos" class="table table-striped table-bordered" width="100%">
													<thead>
														<tr>
															<th class="def_app_center">#</th>
															<th class="def_app_center">ADUANA</th>
															<th class="def_app_center">PATENTE</th>
															<th class="def_app_center">AÑO</th>
															<th class="def_app_center">PEDIMENTO INICIAL</th>
															<th class="def_app_center">PEDIMENTO FINAL</th>
															<th class="def_app_center">FECHA</th>
														</tr>
													</thead> 
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="panel-footer text-right">
								<small>Monitor de Referencias &reg; 2017 &copy; Grupo Aduanero Del Bravo S.A. de C.V.</small>
							</div>
						</div>
					</div>
				</div>				
			</div>
		</div>
		<!--/div-->
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
	<!-- datepicker -->
    <script src="../datepicker/js/bootstrap-datepicker.js"></script>
	
	<script src="../js/librop.js?2017.09.21.1154"></script>
</body>

</html>
