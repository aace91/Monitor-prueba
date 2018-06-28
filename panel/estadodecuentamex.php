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
    <meta name="author" content="jcdelacruz">
    <!--link rel="icon" href="../../favicon.ico"-->

    <title>Estado de cuenta GAB</title>

	<!-- Bootstrap core CSS -->
    <link href="./../bootstrap/css/bootstrap.min.css" rel="stylesheet"/>

	<!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

	<!-- Custom styles for this template -->
	<link href="./../bootstrap/css/navbar.css" rel="stylesheet">

	<!-- Select2 CSS -->
	<link href="../bower_components/select2/dist/css/select2.min.css" rel="stylesheet" />
	<link href="../bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css" rel="stylesheet" />

	<style type="text/css">
		div.dataTables_processing { z-index: 1; }
	</style>

</head>
<body>
	<div class="container">
        <?php include('nav.php');?>
		<!-- Main component for a primary marketing message or call to action -->
		<div class="jumbotron" style="padding: 10px; margin: 0px;">
			<div class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<h2>Contabilidad <small>Estado de cuenta GAB</small></h2>
				</div>
				<div id="div_panel_principal" class="panel-body">
					<div class="row">
						<div class="col-xs-12" id="idiv_mensaje" style="display:none;"></div>

						<div class="col-xs-12 col-md-6">
							<div class="form-group" style="text-align: left;">
								<label>Cliente:</label>
								<div class="input-append">
									<select class="form-control" id="clientecontabilidad">
										<?php
											$consulta = "SELECT no_cte, nombre
														 FROM contagab.aacte
														 WHERE nombre<>'' AND nombre IS NOT NULL
														 ORDER BY nombre";

											$query = mysqli_query($cmysqli_sab07,$consulta);
											if(isset($_REQUEST['id_cliente'])==false){
												echo '<option value="" selected></option>
												';
											}else{
												echo '<option value=""></option>
												';
											}
											while($row = mysqli_fetch_array($query)){
												$cte_sel='';
												if(isset($_REQUEST['id_cliente'])){
													if($_REQUEST['id_cliente']==$row['no_cte']){
														$cte_sel=' selected';
													}
												}
												echo '<option value="'.$row['no_cte'].'"' . $cte_sel .'>'.trim($row['nombre']).'</option>
												';
											}
										?>
									</select>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class="form-group">
								<label class="hidden-xs hidden-sm">&nbsp;</label>
								<div class="input-append">
									<button type="button" class="btn btn-primary" onclick="fcn_generar_estado_cuenta()"><i class="fa fa-cogs" aria-hidden="true"></i> Generar Estado de Cuenta</button>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div id='iembed_pdf_archivo' class="col-md-12 text-center">
							<!--canvas id="the-canvas"></canvas-->
							<iframe id="pdfViewer" src="./../bower_components/pdfjs/web/viewer.html" style="width: 100%; height: 700px; display: none;" allowfullscreen="" webkitallowfullscreen=""></iframe>
						</div>
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

	<script type="text/javascript" language="javascript" src="./../jzip/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]-->

	<script type="text/javascript" language="javascript" src="moment.js"></script>

	<!-- Select2 -->
    <script type="text/javascript" src="../bower_components/select2/dist/js/select2.min.js"></script>
	<script type="text/javascript" src="../bower_components/select2/dist/js/i18n/es.js"></script>

	<!-- Fileinput JS -->
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/fileinput.min.js"></script>
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput-4.2.3/js/locales/es.js"></script>

	<script type="text/javascript" language="javascript">
		var pdfjsframe = document.getElementById('pdfViewer');
		$(document).ready(function() {
			$("#clientecontabilidad").select2({
				theme: "bootstrap",
				width: "off",
				placeholder: "Seleccione un Cliente",
			});

			if($('#clientecontabilidad').val() != ''){
				console.log($('#clientecontabilidad').val());
				fcn_generar_estado_cuenta();
			}
		} );

		function fcn_generar_estado_cuenta() {
			var idclicont = (($('#clientecontabilidad').val() == '')? '-1' : $('#clientecontabilidad').val());

			if (idclicont == '-1') {
				show_custom_function_error('Debe seleccionar un cliente', 'idiv_mensaje');
				return;
			}

			var oData = {
				idclicont: idclicont
			};


			$.ajax({
				url:   'ajax/estadodecuentamex/postestadodecuentamex.php',
				type:  'post',
				data: oData,
				beforeSend: function () {
					$('#pdfViewer').hide();
					show_custom_function_info('<img src="../images/cargando.gif" height="16" width="16"> Generando estado de cuenta', 'idiv_mensaje');
				},
				success:  function (response) {
					respuesta = JSON.parse(response);
					show_custom_function_info('', 'idiv_mensaje');
					if (respuesta.codigo=='1'){
						var pdfData = base64ToUint8Array(respuesta.data);
						pdfjsframe.contentWindow.PDFViewerApplication.open(pdfData);
						$('#pdfViewer').show();
					}else{
						$("#iembed_pdf_archivo").html('<div class="alert alert-danger">'+respuesta.mensaje+'</div>');
					}
				},
				error: function(data){
					$("#iembed_pdf_archivo").html('<div class="alert alert-danger">Error contacte al administrador</div>');
				}
			});
		}

		function base64ToUint8Array(base64) {
			var raw = atob(base64);
			var uint8Array = new Uint8Array(raw.length);
			for (var i = 0; i < raw.length; i++) {
				uint8Array[i] = raw.charCodeAt(i);
			}
			return uint8Array;
		}

		/*********************************************************************************************************************************
		** MESSAJE FUNCTIONS                                                                                                            **
		*********************************************************************************************************************************/

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

		/* ..:: Funcion muestra mensajes de info ::.. */
		function show_custom_function_info(sMensaje, oDivDisplay, sStyle) {
			sMensaje = ((sMensaje == null || sMensaje == undefined)? '': sMensaje);
			oDivDisplay = ((oDivDisplay == null || oDivDisplay == undefined)? '': oDivDisplay);
			sStyle = ((sStyle == null || sStyle == undefined)? '': sStyle);

			if (oDivDisplay != '') {
				if (sMensaje != '') {
					var sHtml = '<div class="alert alert-info" style="' + sStyle + '">';
					sHtml +=	'	 <strong>Info!</strong> ' + sMensaje;
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
</body>
</html>
