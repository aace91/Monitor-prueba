<?php
	$bDebug = ((strpos($_SERVER['REQUEST_URI'], 'monitorpruebas/') !== false)? true : false);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">

		<title>Monitor - Priority service</title>

		<!-- Bootstrap Core CSS -->
		<link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
		
		<!-- Custom Fonts -->
		<link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	
		<!-- Custom styles for this template -->
		<link href="../bootstrap/css/navbar.css" rel="stylesheet">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
		
		<link href="timeline.css" rel="stylesheet"/>
		<style>
			textarea {
				resize: none;
			}
			
			.btn-app {
			  border-radius: 3px;
			  position: relative;
			  padding: 15px 5px;
			  margin: 0 0 10px 10px;
			  min-width: 80px;
			  height: 60px;
			  text-align: center;
			  font-size: 12px;
			}
			.btn-app > .fa,
			.btn-app > .glyphicon,
			.btn-app > .ion {
			  font-size: 20px;
			  display: block;
			}
			.btn-app:hover {
			  border-color: #aaa;
			}
			.btn-app:active,
			.btn-app:focus {
			  -webkit-box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
			  -moz-box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
			  box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
			}
			.btn-app > .badge {
			  position: absolute;
			  top: -3px;
			  right: -10px;
			  font-size: 10px;
			  font-weight: 400;
			}

			/*Para boton refrescar de grid*/
			div.dt-buttons {
				float:right;
				margin-bottom:6px;
			}
		</style>		
	</head>

	<body>
		<input type="hidden" id="itxt_data" data-app_data="<?php 
			$sLink = '';
			if ((isset($_GET['isd']) && !empty($_GET['isd']))) { 
				$id_solicitud = $_GET['isd'];
				$Tipo = $_GET['tp'];
				$observaciones_user = $_GET['usr'];

				$sQueryString = 'isd='.$id_solicitud.'&tp='.$Tipo.'&usr='.$observaciones_user;
				$sLink = 'ajax/tiempo_extra/ajax_rechazar_tiempo_extra.php?'.$sQueryString;
			}
			
			$aAppData = array(
				'sLink' => $sLink
			);
				
			$respuesta['aAppData']=$aAppData;
			echo htmlspecialchars(json_encode($respuesta), ENT_QUOTES, 'UTF-8');
		?>"/>
		<div class="container">
			<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
				<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
					<div class="panel-heading">
						<span class="glyphicon glyphicon-ban-circle"></span> <strong>  Rechazar solicitud de servicio prioritario</strong> <small></small>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Comentarios</div>
										<textarea class="form-control text-uppercase" rows="4" id="txt_mdl_rec_tiempoext_observaciones"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="div_mdl_rec_tiempoext_mensaje"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<button id="btn_mdl_rec_tiempoext_guardar" type="button" class="btn btn-success pull-right"><i class="fa fa-floppy-o"></i> Guardar</button>
							</div>
						</div>
					</div>
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
	
		<script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
		<!--[if (gte IE 9) | (!IE)]><!-->
			<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
			<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
		<!--<![endif]-->

		<script type="text/javascript" language="javascript">
			var __aAppData;
			$(document).ready(function() {
				__aAppData = $('#itxt_data').data('app_data').aAppData;

				$('#btn_mdl_rec_tiempoext_guardar').click(function(event){ ajax_rechazar_tiempo_extra(); });	

				if (__aAppData.sLink == '') {
					$("#div_mdl_rec_tiempoext_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">No se recibieron datos</div>');
					$('#txt_mdl_rec_tiempoext_observaciones').closest("div").hide();
					$('#btn_mdl_rec_tiempoext_guardar').hide();
				}
			});

			function ajax_rechazar_tiempo_extra() {
				if($('#txt_mdl_rec_tiempoext_observaciones').val().trim() == ''){
					var strMensaje = 'Es necesario agregar una observación respecto al rechazo de esta solicitud, todo esto para ofrecerle un mejor servicio.';
					$("#div_mdl_rec_tiempoext_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
					return false;
				}

				window.parent.location.replace(__aAppData.sLink + '&ob=' + $('#txt_mdl_rec_tiempoext_observaciones').val().trim().toUpperCase());
			}
		</script>
	</body>
</html>
