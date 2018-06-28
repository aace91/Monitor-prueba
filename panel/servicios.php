<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
?>
<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Relación de Servicios</title>
    <!-- Bootstrap core CSS -->
    <link href="./../bootstrap/css/bootstrap.min.css" rel="stylesheet">	
	<link href="../datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">	
    <!-- Custom styles for this template -->
    <link href="./../bootstrap/css/navbar.css" rel="stylesheet">

    <script type="text/javascript" src="./../bootstrap/js/jquery.js"></script>
	<script src="./../bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../DataTables-1.10.9/media/js/jquery.dataTables.min.js"></script>
    <script src="../datepicker/js/bootstrap-datepicker.js"></script>
	<style>
		.modal {font-size:18px;}
		*:fullscreen
		*:-ms-fullscreen,
		*:-webkit-full-screen,
		*:-moz-full-screen {
		   overflow: auto !important;
		}
    </style>
  </head>
  <body class="skin-blue sidebar-collapse fixed">	
    <div class="container">
	  <?php include('nav.php');?>
      <div class="content-wrapper">
        <section class="content">
			<div class="row"><div class="col-lg-12">&nbsp;</div></div>
			<div class="row">
				<div class="col-md-12">
				  <div class="box box-info" id="panel_servicios">
					<div class="box-header with-border text-center">
					  <h3 class="box-title"><i class="fa fa-list" aria-hidden="true"></i> Relaci&oacute;n de servicios prestados</h3>
					</div>
					<div class="box-body">
						<div class="row">
							<div class="col-md-12">
								<div id="mensaje_drivers_guardar">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-4 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Referencia</div>
										<input id="txt_referencia" type="text" class="form-control" onKeyUp="toUpper(this)" maxlength="10">
									</div>
								</div>
							</div>
							<div class="col-xs-4 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Remision/Salida</div>
										<input id="txt_remision" type="text" class="form-control" onKeyUp="toUpper(this)" maxlength="6">
									</div>
								</div>
							</div>
							<div class="col-xs-4 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Pedimento</div>
										<input id="txt_pedimento" type="text" class="form-control" onKeyUp="toUpper(this)" maxlength="7">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Cliente</div>
										<input id="txt_cliente" type="text" class="form-control" onKeyUp="toUpper(this)" maxlength="250">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="input-group"> 
										<div class="input-group-addon"><strong>COBROS</strong></div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-3">
								<div class="form-group">									
									<label class="switch switch-green">
									  <input id="chk_cruce" type="checkbox" class="switch-input" >
									  <span class="switch-label" data-on="✓" data-off="X"></span>
									  <span class="switch-handle"></span>
									</label>&nbsp;
									<label class="control-label">Cruce</label>
								</div>
							</div>
							<div class="col-xs-9 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones</div>
										<textarea class="form-control" rows="2" id="txt_obs_cruce" disabled></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-3">
								<div class="form-group">									
									<label class="switch switch-green">
									  <input id="chk_flete" type="checkbox" class="switch-input" >
									  <span class="switch-label" data-on="✓" data-off="X"></span>
									  <span class="switch-handle"></span>
									</label>&nbsp;
									<label class="control-label">Flete</label>
								</div>
							</div>
							<div class="col-xs-9 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones</div>
										<textarea class="form-control" rows="2" id="txt_obs_flete" disabled></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-3">
								<div class="form-group">									
									<label class="switch switch-green">
									  <input id="chk_demoras" type="checkbox" class="switch-input" >
									  <span class="switch-label" data-on="✓" data-off="X"></span>
									  <span class="switch-handle"></span>
									</label>&nbsp;
									<label class="control-label">Demoras</label>
								</div>
							</div>
							<div class="col-xs-9 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones</div>
										<textarea class="form-control" rows="2" id="txt_obs_demoras" disabled></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-3">
								<div class="form-group">									
									<label class="switch switch-green">
									  <input id="chk_maniobras" type="checkbox" class="switch-input" >
									  <span class="switch-label" data-on="✓" data-off="X"></span>
									  <span class="switch-handle"></span>
									</label>&nbsp;
									<label class="control-label">Maniobras</label>
								</div>
							</div>
							<div class="col-xs-9 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones</div>
										<textarea class="form-control" rows="2" id="txt_obs_maniobras" disabled></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-3">
								<div class="form-group">									
									<label class="switch switch-green">
									  <input id="chk_inspeccion" type="checkbox" class="switch-input" >
									  <span class="switch-label" data-on="✓" data-off="X"></span>
									  <span class="switch-handle"></span>
									</label>&nbsp;
									<label class="control-label">Inspeccion</label>
								</div>
							</div>
							<div class="col-xs-9 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones</div>
										<textarea class="form-control" rows="2" id="txt_obs_inspeccion" disabled></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-3">
								<div class="form-group">									
									<label class="switch switch-green">
									  <input id="chk_cove" type="checkbox" class="switch-input" >
									  <span class="switch-label" data-on="✓" data-off="X"></span>
									  <span class="switch-handle"></span>
									</label>&nbsp;
									<label class="control-label">COVE</label>
								</div>
							</div>
							<div class="col-xs-9 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones</div>
										<textarea class="form-control" rows="2" id="txt_obs_cove" disabled></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-3">
								<div class="form-group">									
									<label class="switch switch-green">
									  <input id="chk_otros" type="checkbox" class="switch-input" >
									  <span class="switch-label" data-on="✓" data-off="X"></span>
									  <span class="switch-handle"></span>
									</label>&nbsp;
									<label class="control-label">Otros</label>
								</div>
							</div>
							<div class="col-xs-9 text-left">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-addon">Observaciones</div>
										<textarea class="form-control" rows="2" id="txt_obs_otros" disabled></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-5 col-md-3">
								<div class="form-group">									
									<label class="switch switch-green">
									  <input id="chk_srv_extra" type="checkbox" class="switch-input" >
									  <span class="switch-label" data-on="✓" data-off="X"></span>
									  <span class="switch-handle"></span>
									</label>&nbsp;
									<label class="control-label">Servicio Extraordinario</label>
								</div>
							</div>
							<div class="col-xs-7 col-md-9">
								<div class="form-group">
									<div class="input-group date date-time">
										<input  id="dtp_fecha_ext" type="text" class="form-control" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 text-center">
								<div class="form-group">
									<p>En el caso de flete deben anexar orden de servicio proporcionada por Sergio V&aacute;zquez. 
									<strong>Esta hoja debe estar inmediatamente atr&aacute;s del pedimento, as&iacute; como las salidas de los transfer.</strong> 
									Sin esta hoja no se recibir&aacute; el expediente.</p>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
								<div id="mensaje_relacion_servicios"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="box-footer">
						<div class="row">
							<div class="col-xs-12 text-right">
								<button id="btn_cancelar_estado" type="button" class="btn btn-default" onclick="cancelar_relacion_servicios()">Cancelar</button>
								&nbsp;
								<button id="btn_guardar_estado" type="button" class="btn btn-success" onclick="guarda_relacion_servicios()" >Guardar</button>
							</div>
						</div>
					</div>
				  </div>
				</div>
			</div>			
        </section>
      </div> 
	  <?php include('foot.php');?>
    </div>
	
    <!--script type="text/javascript" language="javascript" src="js/release.js"></script-->	
	<script type="text/javascript" language="javascript" >
		 
		var bNuevo = true;
		var sIdServicio = '';
		
		$(document).ready(function () {
			$('#dtp_fecha_ext').datepicker({
				format:'yyyy-mm-dd'
				});
			inicializa_controles_chk();
			
			$('#txt_referencia').focus();
			
			$("#txt_referencia").focusout(function() {if($.trim($("#txt_referencia").val()) != '')consultar_relacion_servicios();});
		});
		
		function toUpper(control) {
			if (/[a-z]/.test(control.value)) {
				control.value = control.value.toUpperCase();
			}
		}
		
		function inicializa_controles_chk(){
			$('#chk_cruce').click (function (){if ($(this)[0].checked){$("#txt_obs_cruce").prop('disabled', false);$("#txt_obs_cruce").focus();}else{$("#txt_obs_cruce").val('');$("#txt_obs_cruce").prop('disabled', true);}});
			$('#chk_flete').click (function (){if ($(this)[0].checked){$("#txt_obs_flete").prop('disabled', false);$("#txt_obs_flete").focus();}else{$("#txt_obs_flete").val('');$("#txt_obs_flete").prop('disabled', true);}});
			$('#chk_demoras').click (function (){if ($(this)[0].checked){$("#txt_obs_demoras").prop('disabled', false);$("#txt_obs_demoras").focus();}else{$("#txt_obs_demoras").val('');$("#txt_obs_demoras").prop('disabled', true);}});
			$('#chk_maniobras').click (function (){if ($(this)[0].checked){$("#txt_obs_maniobras").prop('disabled', false);$("#txt_obs_maniobras").focus();}else{$("#txt_obs_maniobras").val('');$("#txt_obs_maniobras").prop('disabled', true);}});
			$('#chk_inspeccion').click (function (){if ($(this)[0].checked){$("#txt_obs_inspeccion").prop('disabled', false);$("#txt_obs_inspeccion").focus();}else{$("#txt_obs_inspeccion").val('');$("#txt_obs_inspeccion").prop('disabled', true);}});
			$('#chk_cove').click (function (){if ($(this)[0].checked){$("#txt_obs_cove").prop('disabled', false);$("#txt_obs_cove").focus();}else{$("#txt_obs_cove").val('');$("#txt_obs_cove").prop('disabled', true);}});
			$('#chk_otros').click (function (){if ($(this)[0].checked){$("#txt_obs_otros").prop('disabled', false);$("#txt_obs_otros").focus();}else{$("#txt_obs_otros").val('');$("#txt_obs_otros").prop('disabled', true);}});
			//$('#chk_srv_extra').click (function (){if ($(this)[0].checked){$("#calendar").prop('disabled', false);$("#calendar").focus();}else{$("#calendar").val('');$("#calendar").prop('disabled', true);}});
		}
		
		function guarda_relacion_servicios(){
			if(bNuevo)
				ajax_guardar_servicios();
			else
				ajax_editar_servicios();
		}
		
		function consultar_relacion_servicios(){
			var sIdReferencia = $('#txt_referencia').val();
			
			$.ajax({
			url:   'ajax/relacion_servicios/ajax_consultar_relacion_servicios.php',
			type:  'post',
			data: {referencia:sIdReferencia},
			beforeSend: function () { 
				$("#mensaje_relacion_servicios").html('<div class="success alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> Consultando información, espere un momento por favor...</div>');
			},
			success:  function (response) {
				if (response != '500'){
					respuesta = JSON.parse(response);
					$('#modalloadconfig').modal('hide');
					if (respuesta.Codigo == '1'){
						$('#mensaje_relacion_servicios').html('');
						if(respuesta.nrows != '0'){
							bNuevo = false;							
							
							sIdServicio = respuesta.id_relacion;
							$('#txt_remision').val(respuesta.remision);
							$('#txt_pedimento').val(respuesta.pedimento);
							$('#txt_obs_cruce').val(respuesta.cruce_observaciones);
							$('#txt_cliente').val(respuesta.cliente);
							$('#txt_obs_flete').val(respuesta.flete_observaciones);
							$('#txt_obs_demoras').val(respuesta.demoras_observaciones);
							$('#txt_obs_maniobras').val(respuesta.maniobras_observaciones);
							$('#txt_obs_inspeccion').val(respuesta.inspeccion_observaciones);
							$('#txt_obs_cove').val(respuesta.cove_observaciones);
							$('#txt_obs_otros').val(respuesta.otros_observaciones);
							$('#dtp_fecha_ext').val(respuesta.fecha_servicio_extraordinario);
							
							$('#chk_cruce').prop( 'checked', (respuesta.cruce == '1' ? true : false) );
							$('#chk_flete').prop( 'checked', (respuesta.flete == '1' ? true : false) );
							$('#chk_demoras').prop( 'checked', (respuesta.demoras == '1' ? true : false) );
							$('#chk_maniobras').prop( 'checked', (respuesta.maniobras == '1' ? true : false) );
							$('#chk_inspeccion').prop( 'checked', (respuesta.inspeccion == '1' ? true : false) );
							$('#chk_cove').prop( 'checked', (respuesta.cove == '1' ? true : false) );
							$('#chk_otros').prop( 'checked', (respuesta.otros == '1' ? true : false) );
							$('#chk_srv_extra').prop( 'checked', (respuesta.servicio_extraordinario == '1' ? true : false) );
							
							$('#txt_obs_cruce').prop( 'disabled', (respuesta.cruce == '0' ? true : false) );
							$('#txt_obs_flete').prop( 'disabled', (respuesta.flete == '0' ? true : false) );
							$('#txt_obs_demoras').prop( 'disabled', (respuesta.demoras == '0' ? true : false) );
							$('#txt_obs_maniobras').prop( 'disabled', (respuesta.maniobras == '0' ? true : false) );
							$('#txt_obs_inspeccion').prop( 'disabled', (respuesta.inspeccion == '0' ? true : false) );
							$('#txt_obs_cove').prop( 'disabled', (respuesta.cove == '0' ? true : false) );
							$('#txt_obs_otros').prop( 'disabled', (respuesta.otros == '0' ? true : false) );
						
						}else{
							bNuevo = true;
							$('#txt_pedimento').val(respuesta.pedimento);
							$('#txt_cliente').val(respuesta.cliente);
							$('#txt_remision').val('');
						}						
					}else{
						$('#mensaje_relacion_servicios').html('<div class="success alert-danger alert-dismissible" role="alert">Error al consultar la referencia.['+respuesta.Mensaje+']</div>');
					}
				}else{
					$('#mensaje_relacion_servicios').html('<div class="success alert-danger alert-dismissible" role="alert">La sesión del usuario ha terminado.</div>');
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}
			},
			error: function(a,b){
				$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">'+a.status+' ['+a.statusText+']</div>');
			}
		});
		}
		
		function ajax_editar_servicios(){
			
			var sReferencia = $.trim($('#txt_referencia').val());
			var sRemision = $.trim($('#txt_remision').val());
			var sPedimento = $.trim($('#txt_pedimento').val());
			var sCliente= $.trim($('#txt_cliente').val());
			
			if (sReferencia.length < 8) { 
				$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">Es necesario que verifique el n&uacute;mero de referencia.</div>');
				
				$('#txt_referencia').focus(); return; 
			}
			/*if (sRemision == '') {
				$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">Es necesario que agregue el n&uacute;mero de remisi&oacute;n.</div>');
				$('#txt_remision').focus(); return; 
			}*/
			if (sPedimento == '') {
				$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">Es necesario que agregue el n&uacute;mero de pedimento.</div>');				
				$('#txt_pedimento').focus(); return; 
			}
			if (sCliente == '') {
				$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">Es necesario que agregue el nombre del cliente.</div>');				
				$('#txt_cliente').focus(); return; 
			}
			
			var sCruce = ($('#chk_cruce').is(':checked')?'1':'0');
			var sFlete = ($('#chk_flete').is(':checked')?'1':'0');
			var sDemoras = ($('#chk_demoras').is(':checked')?'1':'0');
			var sManiobras = ($('#chk_maniobras').is(':checked')?'1':'0');
			var sInspeccion = ($('#chk_inspeccion').is(':checked')?'1':'0');
			var sCove = ($('#chk_cove').is(':checked')?'1':'0');
			var sSrv_Ext = ($('#chk_srv_extra').is(':checked')?'1':'0');
			var sOtros = ($('#chk_otros').is(':checked')?'1':'0');
			
			var sCruce_Obs = $.trim($('#txt_obs_cruce').val());
			var sFlete_Obs = $.trim($('#txt_obs_flete').val());
			var sDemoras_Obs = $.trim($('#txt_obs_demoras').val());
			var sManiobras_Obs = $.trim($('#txt_obs_maniobras').val());
			var sInspeccion_Obs = $.trim($('#txt_obs_inspeccion').val());
			var sCove_Obs = $.trim($('#txt_obs_cove').val());
			var sOtros_Obs = $.trim($('#txt_obs_otros').val());
			var sSrv_Fecha = $('#dtp_fecha_ext').val() + ' 00:00:00';

			$.ajax({
				url:   'ajax/relacion_servicios/ajax_editar_relacion_servicios.php',
				type:  'post',
				data: {
					id_relacion: sIdServicio,
					referencia : sReferencia,
					remision : sRemision,
					pedimento : sPedimento,
					cliente : sCliente,
					cruce : sCruce,
					flete : sFlete,
					demoras : sDemoras,
					maniobras : sManiobras,
					inspeccion : sInspeccion,
					cove : sCove,
					srv_ext : sSrv_Ext,
					otros : sOtros,
					obs_cruce : sCruce_Obs,
					obs_flete : sFlete_Obs,
					obs_demoras : sDemoras_Obs,
					obs_maniobras : sManiobras_Obs,
					obs_inspeccion : sInspeccion_Obs,
					obs_cove : sCove_Obs,
					fecha_srv_ext : sSrv_Fecha,
					obs_otros : sOtros_Obs
				},
				beforeSend: function () { 
					$("#mensaje_relacion_servicios").html('<div class="success alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/>Guardando Informaci&oacute;n. Espere un momento por favor...</div>');
				},
				success:  function (response) {
					if (response != '500'){
						respuesta = JSON.parse(response);
						$('#modalloadconfig').modal('hide');
						if (respuesta.Codigo == '1'){
							$("#mensaje_relacion_servicios").html('<div class="success alert-success alert-dismissible" role="alert">' + respuesta.Mensaje + '</div>');
							window.location.href = "http://delbravoweb.tk/monitor/panel/showrelacionservicios.php?id="+sIdServicio;
							cancelar_relacion_servicios();
							
							$('#sel_placas_trailer').focus();
						}else{
							$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">' + respuesta.Mensaje + '</div>');
						}
					}else{
						$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">La sesi&oacute;n del usuario de ha terminado, es necesario que vuelva a iniciar.</div>');
						setTimeout(function () {window.location('../logout.php');},4000);
					}				
				},
				error: function(a,b){
					$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">' + a.status+' [' + a.statusText + ']' + '</div>');
				}
			});
		}
		
		function ajax_guardar_servicios(){
			var sReferencia = $.trim($('#txt_referencia').val());
			var sRemision = $.trim($('#txt_remision').val());
			var sPedimento = $.trim($('#txt_pedimento').val());
			var sCliente = $.trim($('#txt_cliente').val());
			
			if (sReferencia.length < 8) { 
				$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">Es necesario que verifique el n&uacute;mero de referencia.</div>');
				
				$('#txt_referencia').focus(); return; 
			}
			/*if (sRemision == '') {
				$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">Es necesario que agregue el n&uacute;mero de remisi&oacute;n.</div>');
				$('#txt_remision').focus(); return; 
			}*/
			if (sPedimento == '') {
				$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">Es necesario que agregue el n&uacute;mero de pedimento.</div>');				
				$('#txt_pedimento').focus(); return; 
			}
			if (sCliente == '') {
				$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">Es necesario que agregue el nombre del cliente.</div>');				
				$('#txt_cliente').focus(); return; 
			}
			
			var sCruce = ($('#chk_cruce').is(':checked')?'1':'0');
			var sFlete = ($('#chk_flete').is(':checked')?'1':'0');
			var sDemoras = ($('#chk_demoras').is(':checked')?'1':'0');
			var sManiobras = ($('#chk_maniobras').is(':checked')?'1':'0');
			var sInspeccion = ($('#chk_inspeccion').is(':checked')?'1':'0');
			var sCove = ($('#chk_cove').is(':checked')?'1':'0');
			var sSrv_Ext = ($('#chk_srv_extra').is(':checked')?'1':'0');
			var sOtros = ($('#chk_otros').is(':checked')?'1':'0');
			
			var sCruce_Obs = $.trim($('#txt_obs_cruce').val());
			var sFlete_Obs = $.trim($('#txt_obs_flete').val());
			var sDemoras_Obs = $.trim($('#txt_obs_demoras').val());
			var sManiobras_Obs = $.trim($('#txt_obs_maniobras').val());
			var sInspeccion_Obs = $.trim($('#txt_obs_inspeccion').val());
			var sCove_Obs = $.trim($('#txt_obs_cove').val());
			var sOtros_Obs = $.trim($('#txt_obs_otros').val());
			var sSrv_Fecha = $('#dtp_fecha_ext').val() + ' 00:00:00';

			$.ajax({
				url:   'ajax/relacion_servicios/ajax_agregar_relacion_servicios.php',
				type:  'post',
				data: {
					referencia : sReferencia,
					remision : sRemision,
					pedimento : sPedimento,
					cliente : sCliente,
					cruce : sCruce,
					flete : sFlete,
					demoras : sDemoras,
					maniobras : sManiobras,
					inspeccion : sInspeccion,
					cove : sCove,
					srv_ext : sSrv_Ext,
					otros : sOtros,
					obs_cruce : sCruce_Obs,
					obs_flete : sFlete_Obs,
					obs_demoras : sDemoras_Obs,
					obs_maniobras : sManiobras_Obs,
					obs_inspeccion : sInspeccion_Obs,
					obs_cove : sCove_Obs,
					fecha_srv_ext : sSrv_Fecha,
					obs_otros : sOtros_Obs
				},
				beforeSend: function () { 
					$("#mensaje_relacion_servicios").html('<div class="success alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/>Guardando Informaci&oacute;n. Espere un momento por favor...</div>');
				},
				success:  function (response) {
					if (response != '500'){
						respuesta = JSON.parse(response);
						$('#modalloadconfig').modal('hide');
						if (respuesta.Codigo == '1'){
							$("#mensaje_relacion_servicios").html('<div class="success alert-success alert-dismissible" role="alert">' + respuesta.Mensaje + '</div>');
							window.location.href = "http://www.delbravoweb.tk/monitor/panel/showrelacionservicios.php?id="+respuesta.id_relacion ;
							cancelar_relacion_servicios();
							
							$('#sel_placas_trailer').focus();
						}else{
							$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">' + respuesta.Mensaje + '</div>');
						}
					}else{
						$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">La sesi&oacute;n del usuario de ha terminado, es necesario que vuelva a iniciar.</div>');
						setTimeout(function () {window.location('../logout.php');},4000);
					}				
				},
				error: function(a,b){
					$("#mensaje_relacion_servicios").html('<div class="success alert-danger alert-dismissible" role="alert">' + a.status+' [' + a.statusText + ']' + '</div>');
				}
			});			
		}
		
		function cancelar_relacion_servicios(){
			
			$('#txt_referencia').val('');
			$('#txt_remision').val('');
			$('#txt_pedimento').val('');
			$('#txt_cliente').val('');
			$('#txt_obs_cruce').val('');
			$('#txt_obs_flete').val('');
			$('#txt_obs_demoras').val('');
			$('#txt_obs_maniobras').val('');
			$('#txt_obs_inspeccion').val('');
			$('#txt_obs_cove').val('');
			$('#txt_obs_otros').val('');
			$('#dtp_fecha_ext').val('');
			$('#chk_cruce').prop( 'checked', false );
			$('#chk_flete').prop( 'checked', false );
			$('#chk_demoras').prop( 'checked', false );
			$('#chk_maniobras').prop( 'checked', false );
			$('#chk_inspeccion').prop( 'checked', false );
			$('#chk_cove').prop( 'checked', false );
			$('#chk_otros').prop( 'checked', false );
			$('#chk_srv_extra').prop( 'checked', false );
			
			$('#txt_obs_cruce').prop( 'disabled', true );
			$('#txt_obs_flete').prop( 'disabled', true );
			$('#txt_obs_demoras').prop( 'disabled', true );
			$('#txt_obs_maniobras').prop( 'disabled', true );
			$('#txt_obs_inspeccion').prop( 'disabled', true );
			$('#txt_obs_cove').prop( 'disabled', true );
			$('#txt_obs_otros').prop( 'disabled', true );
			
			$('#txt_referencia').focus();
		}
	</script>
  </body>
</html>
