<?php
include_once('./../checklogin.php');
include('./../connect_dbsql.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
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

    <title>Listado de Cuentas de Gastos Americana</title>

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
	
	<!-- Javascript Propio -->
	<script type="text/javascript" language="javascript" class="init">
		$(document).ready(function() {
			var table= $('#ctaame').dataTable( {
				"order": [ [3, 'dsc'] ],
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "./ajax/listctaame/postctaame.php",
					"type": "POST",
					"data": function ( d ) {
						d.fechaini = $('#fechaini1').val();
						d.fechafin = $('#fechafin1').val();
						d.estatus_pago = $('#selestatus').val();
						d.idquickbooks = (($('#clientecontabilidad').val() == '')? '-1' : $('#clientecontabilidad').val());
					}
				},
				"columns": [
					{ 	data: "cuenta" },
					{ 	
						data: "estatus",
						className: "text-center",
						render: function ( data, type, row ) { 
							var sHtml = '';
							if (type == 'display') {
								if (data == 'true') {
									sHtml += '<span class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true" title="Pagada"></span>';
								} else {
									sHtml += '<span class="glyphicon glyphicon-warning-sign text-warning" aria-hidden="true" title="Pendiente"></span>';
								}
							}
							return sHtml;
						}
					},
					{ 	data: "referencia" },
					{ 	data: "fecha" },
					{ 	data: "Trailer" },
					{ 	data: "FOB" },
					{   data: null,
						className: "text-center",
						render: function ( data, type, row ) { 
							var sHtml = '';
							if (type == 'display') {
								sHtml += '<a href="ajax/listctaame/fileInvoice.php?id='+ row.TxnID +'" target="_blank">';
								sHtml += '	<span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>';
								sHtml += '</a>';
							}
							return sHtml;
						}
					}
				],
				"buttons": [
					{
						extend: 'copyHtml5',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'excelHtml5',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'csvHtml5',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'pdfHtml5',
						orientation: 'landscape',
						pageSize: 'LEGAL',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'print',
						exportOptions: {
							columns: ':visible'
						}
					}
				],
				"dom": '<"top"r>fBt<"bottom"lpi><"clear">',
				"sScrollX": '100%',
				responsive: true,
				"language": {
					"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36">Consultando información...',
					"sLengthMenu":     "Mostrar _MENU_ registros",
					"sZeroRecords":    "No se encontraron resultados",
					"sEmptyTable":     "Ningún dato disponible en esta tabla",
					"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
					"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
					"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
					"sInfoPostFix":    "",
					"sSearch":         "Buscar:",
					"sUrl":            "",
					"sInfoThousands":  ",",
					"sLoadingRecords": "Cargando...",
					"oPaginate": {
						"sFirst":    "Primero",
						"sLast":     "Último",
						"sNext":     "Siguiente",
						"sPrevious": "Anterior"
					},
					"oAria": {
						"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
						"sSortDescending": ": Activar para ordenar la columna de manera descendente"
					}
				}
			} );
			
			var fechaini = $('#fechaini').datepicker({
				todayHighlight:true,
				autoclose: true,
				clearBtn: true
			}).data('datepicker');
			
			var fechafin = $('#fechafin').datepicker({
				todayHighlight:true,
				autoclose: true,
				clearBtn: true
			}).data('datepicker');
			
			$("#clientecontabilidad").select2({
				theme: "bootstrap",
				width: "off",
				placeholder: "Seleccione un Cliente",
			});	
		} );
	
		/* ..:: Capturamos los errores ::.. */
		function handleAjaxError( xhr, textStatus, error ) {
			if ( textStatus === 'timeout' ) {
				show_message_error('El servidor tardó demasiado en enviar los datos');
			} else {
				show_message_error('Se ha producido un error en el servidor. Por favor espera.');
				
				setTimeout(function(){ hide_message(); }, 5000);
			}
		}

		function on_grid_error(e, settings, techNote, message) {
			var bExist = message.includes("Code [500]");
			if(bExist) {
				show_custom_function_error(strSessionMessage, 'idiv_error_mensaje');					
				setTimeout(function () {window.location.replace('../logout.php');},4000);
			} else {
				show_custom_function_error('Ha ocurrido un error: ' + message, 'idiv_error_mensaje');
				setTimeout(function(){ show_custom_function_error('', 'idiv_error_mensaje'); }, 5000);
			}
		}
		
		function filtrafecha(){
			var table = $('#ctaame').DataTable();
			table.ajax.reload(null, true);
		}
		
		/* ..:: Funcion muestra mensajes de error ::.. */
		function show_custom_function_error(sMensaje, oDivDisplay, sStyle) {
			sMensaje = ((sMensaje == null || sMensaje == undefined)? '': sMensaje);
			oDivDisplay = ((oDivDisplay == null || oDivDisplay == undefined)? '': oDivDisplay);
			sStyle = ((sStyle == null || sStyle == undefined)? '': sStyle);

			if (oDivDisplay != '') {
				if (sMensaje != '') {
					var sHtml = '<div class="alert alert-danger" style="' + sStyle + '">';
					sHtml +=	'	 <strong>Error!</strong> ' + sMensaje;
					sHtml +=    '</div>';	
					
					$('#' + oDivDisplay).html(sHtml).show();
				} else {
					$('#' + oDivDisplay).hide();
				}
			} else {		
				show_modal_error('No se proporciono contenedor para el mensaje!');
			}
		}
	</script>
</head>
<body>
	<div class="container">
		<?php include('nav.php');?>
		
		<!-- Main component for a primary marketing message or call to action -->
		<div class="jumbotron"> 
			<h2>Cuentas de gastos Americana <small>Listado</small></h2>
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
									$consulta = "SELECT b.ListID, b.`Name`
												 FROM bodega.accesos AS a INNER JOIN
													  qbdelbravo_sync.customer AS b ON b.ListID=a.id_quickbooks
												 WHERE a.id_quickbooks IS NOT NULL
												 GROUP BY b.ListID, b.`Name`
												 ORDER BY b.`Name`";
															
									$query = mysqli_query($cmysqli,$consulta);
									echo '<option value="" selected></option>';
									while($row = mysqli_fetch_array($query)){
										echo '<option value="'.$row['ListID'].'">'.$row['Name'].'</option>';
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
								<option value="false">Pendientes</option>
								<option value="true">Pagadas</option>
								<option value="none" selected>Todos</option>
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
			<table id="ctaame" class="display responsive no-wrap" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th style="width:100px;">#Cuenta</th>
						<th>Estatus</th>
						<th style="width:120px;">Trafico/Referencia</th>
						<th>Fecha (DD/MM/YYYY)</th>
						<th>Trailer #</th>
						<th>Pedimento</th>
						<th>Factura</th>
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