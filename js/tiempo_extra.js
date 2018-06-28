/*********************************************************************************************************************************
** VARIABLES GLOBALES                                                                                                           **
*********************************************************************************************************************************/

var Id_Solicitud = '';
var __sTarea = 'Nuevo';
var __aAppData;

/*********************************************************************************************************************************
** FUNCIONES                                                                                                                    **
*********************************************************************************************************************************/

$(document).ready(function() {
	__aAppData = $('#itxt_data').data('app_data').aAppData;

	$('#btn_mdl_rec_tiempoext_guardar').click(function(event){ ajax_rechazar_tiempo_extra(); });		
	
	$('#btn_nuevo_tiempoext').click(function(event){ nuevo_tiempo_extra(); });		
	$('#btn_guardar_tiempoext').click(function(event){ ajax_guardar_tiempo_extra(); });
	
	$('#btn_buscar_referencia').click(function(event){ ajax_buscar_referencia_cita(); });
	
	inciaializa_tabla_tiempo_extra();

	if (__aAppData.sReferencia != '') {
		var table = $('#tbl_tiemposext').DataTable();
		table.search(__aAppData.sReferencia).ajax.reload(null, true);
	}
});
	
/*********************************************************************************************************************************
** FILL AND CREATE GRIDS FUNCTIONS                                                                                              **
*********************************************************************************************************************************/

function inciaializa_tabla_tiempo_extra(){
	var table = $('#tbl_tiemposext').DataTable( {
		"order": [ [0, 'desc'] ],
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "ajax/tiempo_extra/post_tiempoext.php",
			"type": "POST",
			"error": function (a,b){
				if (a.responseText == '{500}'){
					var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
					$("#div_mensaje_entradas_mx").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
					setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
				}else{
					$("#div_mensaje_entradas_mx").html('<div class="alert alert-danger alert-dismissible" role="alert">DataTable: '+a.responseText+'</div>');
				}
			}
		},
		"columns": [
			{ "data": "id_solicitud"},
			{ "data": "referencia"},
			{ "data": "motivo"},
			{ "data": "fecha_registro", "className": "text-center"},
			{ "data": "cliente"},
			{ "data": "linea_entrego"},
			{ "data": "estatus" ,
			  "className": "text-center",
				"mRender": function (data, type, row) {
					var sBtnAction = data;
					if(data == 'PENDIENTE'){
						if (row.fecha_autorizo_ejecutivo == '') {
							sBtnAction = '<a href="javascript:void(0);" onclick="autorizar_tiempo_extra(\''+row.id_solicitud+'\', \''+row.referencia+'\');return false;" style="padding-left:.5em;" title="" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-ok-sign"></span> Autorizar</a>&nbsp;';
							sBtnAction += '<a href="javascript:void(0);" onclick="rechazar_tiempo_extra(\''+row.id_solicitud+'\');return false;" style="padding-left:.5em;" title="" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-remove-sign"></span> Rechazar</a>';
						}
					}
					
					switch(sBtnAction) {
						case 'PENDIENTE':
							sBtnAction = '<span class="label label-warning"><i class="glyphicon glyphicon-time"></i> ' + sBtnAction + '</span>';
							break;
						case 'RECHAZADO':
							sBtnAction = '<span class="label label-danger"><i class="glyphicon glyphicon-remove"></i> ' + sBtnAction + ' ' + row.fecha_rechazo + '</span>';
							break;
						case 'AUTORIZADO':
							sBtnAction = '<span class="label label-success"><i class="glyphicon glyphicon-ok"></i> ' + sBtnAction + '</span>';
							break;
					}

					return sBtnAction;
				}
			},
			{ "data": "fecha_autorizo_bodega",
			  "className": "text-center",
			  "mRender": function (data, type, row) {
					if(data != ''){
						return '<span class="label label-success"><i class="glyphicon glyphicon-ok"></i> ' + data + '</span>';
					} else {
						return '';
					}
				}
			},
			{ "data": "fecha_autorizo_cliente",
			  "className": "text-center",
			  "mRender": function (data, type, row) {
					if(data != ''){
						return '<span class="label label-success"><i class="glyphicon glyphicon-ok"></i> ' + data + '</span>';
					} else {
						return '';
					}
				}
			},
			{ "data": "fecha_autorizo_ejecutivo",
			  "className": "text-center",
			  "mRender": function (data, type, row) {
					if(data != ''){
						return '<span class="label label-success"><i class="glyphicon glyphicon-ok"></i> ' + data + '</span>';
					} else {
						return '';
					}
				}
			},
			{
				data: "bcomentario",
				className: "def_app_center",
				render: function ( data, type, row ) {
					if (type == 'display') { 
						var sHtml = '';
						if (data == "false" || data == false) {
							sHtml = '<a href="javascript:void(0);" onclick="fcn_show_modal_comentarios(\''+row.id_solicitud+'\');return false;" title="" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-comment"></i> Agregar</a>';
						} else {
							sHtml = '<a href="javascript:void(0);" onclick="fcn_show_modal_comentarios(\''+row.id_solicitud+'\');return false;" title="" class="btn btn-xs btn-primary">';
							sHtml += '   <i class="glyphicon glyphicon-eye-open"></i> Ver';
							if (row.bnuevo_comentario == 'true' || row.bnuevo_comentario == true) {
								sHtml += '&nbsp; <img src="../images/nuevo_comentario.gif" width="21" height="18" border="0" />';
							}
							sHtml += '</a>';
						}
						
						return sHtml;
					} else {
						return data;
					}
				}
			},
			{ "data": "usuario_registro"},
			{ "data": "id_solicitud",
				"mRender": function (data, type, row) {
					var sBtnAction = '';
					if(row.estatus == 'PENDIENTE'){
						sBtnAction += '<a href="javascript:void(0);" onclick="ajax_reeenviar_email(\''+row.id_solicitud+'\');return false;" style="padding-left:.5em;" title="" class="btn btn-primary btn-xs"><i class="fa fa-share" aria-hidden="true"></i> Reenviar Notificaci&oacute;n</a>';
					}
					return sBtnAction;
				}					
			},
			{ "data": "id_solicitud",
				"mRender": function (data, type, row) {
					var sBtnAction = '<a href="javascript:void(0);" onclick="fcn_cambiar_referencia(\''+row.id_solicitud+'\', \''+row.referencia+'\');return false;" style="padding-left:.5em;" title="" class="btn btn-primary btn-xs"><i class="fa fa-exchange" aria-hidden="true"></i> Cambiar Referencia</a>';
					return sBtnAction;
				}					
			}
		],
		responsive: true,
		aLengthMenu: [
			[25, 50, 100, 200, -1],
			[25, 50, 100, 200, "All"]
		],
		iDisplayLength: 50,
		"dom": "<rf<Bt>lpi>",
		"buttons": [
			{
				text: '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar',
				action: function ( e, dt, node, config ) {
					var table = $('#tbl_tiemposext').DataTable();
					table.ajax.reload(null, true);
				}
			}
			/*
			{
				extend: 'colvis',
				text: '<i class="fa fa-columns"></i> Ver Columnas',
				className: 'azul_btns'
			},
			{
				extend: 'excelHtml5',
				text: '<i class="fa fa-file-excel-o"></i> XLS',
				className: 'verde_btns'
			},
			{
				extend: 'excelHtml5',
				exportOptions: {
					columns: ':visible'
				},
				text: '<i class="fa fa-file-excel-o"></i> Columnas Visibles XLS',
				className: 'verde_btns'
			},
			{
				extend: 'csvHtml5',
				text: '<i class="fa fa-file-text-o"></i> CSV',
				className: 'verde_btns'
			},
			{
				extend: 'csvHtml5',
				exportOptions: {
					columns: ':visible'
				},
				text: '<i class="fa fa-file-text-o"></i> Columnas Visibles CSV',
				className: 'verde_btns'
			},
			{
				extend: 'pdfHtml5',
				orientation: 'landscape',
				pageSize: 'LEGAL',
				exportOptions: {
					columns: ':visible'
				},
				text: '<i class="fa fa-file-pdf-o"></i> Columnas Visibles PDF',
				className: 'verde_btns'
				
			},
			{
				extend: 'print',
				exportOptions: {
					columns: ':visible'
				},
				text: '<i class="fa fa-print"></i> Imprimir',
				className: 'verde_btns'
			}*/
		],
		"language": 
		{
			"sProcessing":     '<img src="../images/cargando.gif" height="18" width="18"> Cargando, espera un momento por favor...'
		}
	} );
	
	setTimeout(function () {$('#tbl_tiemposext').DataTable().columns.adjust().responsive.recalc();}, 2000);				
}

/*********************************************************************************************************************************
** CONTROLS FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

/*********************************/
/* ..:: BROWSER ::.. */
/*********************************/

function nuevo_tiempo_extra(){	
	Id_Solicitud = '';
	__sTarea = 'Nuevo';
 
	$("#ih4_tiempoext_titulo").html('<i class="fa fa-clock-o" aria-hidden="true"></i> Solicitar priority service');
	$("#idiv_referencia_anterior").hide();
	$("#idiv_motivo").show();
	
	$("#txt_referencia").val('');
	$("#txt_motivo").val('');
	
	
	$("#txt_cliente").val('');
	$("#txt_linea_fletera").val('');
	
	$( "#txt_referencia" ).prop( "disabled", false );
	$( "#btn_buscar_referencia" ).prop( "disabled", false );
	
	$( "#txt_motivo" ).prop( "disabled", true );
	$( "#btn_guardar_tiempoext" ).prop( "disabled", true );
	
	$("#mensaje_upload_modal").html('');
	$('#modal_tiempoext').modal({show: true});
	setTimeout(function(){$("#txt_referencia").focus();},500);
}

function autorizar_tiempo_extra(idSolicitud, idReferencia) {
	Id_Solicitud = idSolicitud;

	var strTitle = 'Autorizar Solicitud';
	var strQuestion = 'Desea autorizar la solicitud con referencia [' + idReferencia + ']?';
	var oFunctionOk = function () { ajax_autorizar_tiempo_extra(Id_Solicitud); };
	var oFunctionCancel = null;
	show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
}

function rechazar_tiempo_extra(idSolicitud){
	Id_Solicitud = idSolicitud;
	$('#txt_mdl_rec_tiempoext_observaciones').val('');
	$('#div_mdl_rec_tiempoext_mensaje').html('');
	$("#btn_mdl_rec_tiempoext_guardar").prop('disabled', false);
	$('#modal_rechazar').modal({show: true});
	setTimeout(function(){$('#txt_mdl_rec_tiempoext_observaciones').focus();},500);
}

function fcn_cambiar_referencia(idSolicitud, idReferencia) {
	nuevo_tiempo_extra();
	
	Id_Solicitud = idSolicitud;
	__sTarea = 'Editar';
	
	$("#ih4_tiempoext_titulo").html('<i class="fa fa-clock-o" aria-hidden="true"></i> Cambiar Referencia');
	
	$("#idiv_referencia_anterior").show();
	$("#idiv_motivo").hide();
	
	$("#txt_referencia_anterior").val(idReferencia);
}
	
/*********************************/
/* ..:: COMENTARIOS ::.. */
/*********************************/

/* ..:: Mostramos la modal de los comentarios ::.. */
function fcn_show_modal_comentarios(idSolicitud) {
	Id_Solicitud = idSolicitud;
	$('#idiv_timeline').empty();
	
	$('#modal_timeline').modal({ show: true });
	ajax_get_comentarios();
}
	
/*********************************************************************************************************************************
** AJAX                                                                                                                         **
*********************************************************************************************************************************/

function ajax_guardar_tiempo_extra(){		
	if($("#txt_referencia").val().trim() == ''){
		var strMensaje = 'Es necesario agregar el número de referencia a trabajar.';
		$("#mensaje_upload_modal").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
		$('#txt_referencia').focus();
		return false;
	}
	
	if (__sTarea == 'Nuevo') {
		if($("#txt_motivo").val().trim() == ''){
			var strMensaje = 'Es necesario agregar el motivo del tiempo extra.';
			$("#mensaje_upload_modal").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
			$('#txt_motivo').focus();
			return false;
		}
	}
	
	$.ajax({
		url:   'ajax/tiempo_extra/ajax_solicitar_tiempo_extra.php',
		type:  'post',
		data: {
			referencia: $("#txt_referencia").val().trim().toUpperCase(),
			sTarea: __sTarea,
			idSolicitud: Id_Solicitud,
			motivo: $("#txt_motivo").val().trim().toUpperCase()
		},
		beforeSend: function () {
			var strMensaje = 'Espere un momento por favor...';
			$("#mensaje_tiempoext_modal").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if(response != '500'){
				respuesta = JSON.parse(response);					
				if (respuesta.Codigo == '1'){
					$("#mensaje_tiempoext_modal").html('');
					
					var strMensaje = respuesta.Mensaje;
					$("#mensaje_tiempo_extra").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					$('html, body').animate({scrollTop: $("#mensaje_tiempo_extra").offset().top}, 1000);
					$('#modal_tiempoext').modal('hide');
					var table = $('#tbl_tiemposext').DataTable();
					table.ajax.reload();
				}else{
					var strMensaje = respuesta.Mensaje + '' + respuesta.Error;
					$("#mensaje_tiempoext_modal").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_tiempoext_modal").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function (data, textStatus, jqXHR) { 
			$("#mensaje_tiempoext_modal").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + data.status + ' [' + data.statusText + ']'+'</div>');
		}
	});
}

function ajax_buscar_referencia_cita(){
	$.ajax({
		url:   'ajax/tiempo_extra/ajax_buscar_referencia.php',
		type:  'post',
		data: {
			referencia: $("#txt_referencia").val().trim().toUpperCase()
		},
		beforeSend: function () {
			var strMensaje = 'Consultando información. Espere un momento por favor...';
			$("#mensaje_tiempoext_modal").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if(response != '500'){
				respuesta = JSON.parse(response);					
				if (respuesta.Codigo == '1'){
					$("#mensaje_tiempoext_modal").html('');
					
					$("#txt_cliente").val(respuesta.cliente);
					$("#txt_linea_fletera").val(respuesta.linea_fletera);
					
					$( "#txt_referencia" ).prop( "disabled", true );
					$( "#btn_buscar_referencia" ).prop( "disabled", true );
					
					$( "#txt_motivo" ).prop( "disabled", false );
					$( "#btn_guardar_tiempoext" ).prop( "disabled", false );
					
				}else{
					var strMensaje = respuesta.Mensaje + '' + respuesta.Error;
					$("#mensaje_tiempoext_modal").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_tiempoext_modal").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function (data, textStatus, jqXHR) { 
			$("#mensaje_tiempoext_modal").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + data.status + ' [' + data.statusText + ']'+'</div>');
		}
	});
}

function ajax_reeenviar_email(idSolicitud){
	$.ajax({
		url:   'ajax/tiempo_extra/ajax_reenviar_email_tiempo_extra.php',
		type:  'post',
		data: {
			id_solicitud: idSolicitud
		},
		beforeSend: function () {
			var strMensaje = 'Espere un momento por favor...';
			$("#mensaje_tiempo_extra").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if(response != '500'){
				respuesta = JSON.parse(response);					
				if (respuesta.Codigo == '1'){
					
					var strMensaje = respuesta.Mensaje;
					$("#mensaje_tiempo_extra").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					
				}else{
					var strMensaje = respuesta.Error;
					$("#mensaje_tiempo_extra").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_tiempo_extra").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function (data, textStatus, jqXHR) { 
			$("#mensaje_tiempo_extra").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + data.status + ' [' + data.statusText + ']'+'</div>');
		}
	});
}	

function ajax_autorizar_tiempo_extra(idSolicitud){
	$.ajax({
		url:   'ajax/tiempo_extra/ajax_autorizar_tiempo_extra.php',
		type:  'post',
		data: {
			id_solicitud: idSolicitud
		},
		beforeSend: function () {
			var strMensaje = 'Espere un momento por favor...';
			$("#mensaje_tiempo_extra").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if(response != '500'){
				respuesta = JSON.parse(response);					
				if (respuesta.Codigo == '1'){
					$("#mensaje_tiempo_extra").html('');
					
					var strMensaje = respuesta.Mensaje;
					$("#mensaje_tiempo_extra").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					var table = $('#tbl_tiemposext').DataTable();
					table.ajax.reload();
				}else{
					var strMensaje = respuesta.Mensaje + '' + respuesta.Error;
					$("#mensaje_tiempo_extra").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_tiempo_extra").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function (data, textStatus, jqXHR) { 
			$("#mensaje_tiempo_extra").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + data.status + ' [' + data.statusText + ']'+'</div>');
		}
	});
}

function ajax_rechazar_tiempo_extra(){
	if($('#txt_mdl_rec_tiempoext_observaciones').val().trim() == ''){
		var strMensaje = 'Es necesario agregar una observación respecto al rechazo de esta solicitud, todo esto para ofrecerle un mejor servicio.';
		$("#div_mdl_rec_tiempoext_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
		return false;
	}
	$.ajax({
		url:   'ajax/tiempo_extra/ajax_rechazar_tiempo_extra.php',
		type:  'post',
		data: {
			id_solicitud: Id_Solicitud,
			observaciones: $('#txt_mdl_rec_tiempoext_observaciones').val().trim().toUpperCase()
		},
		beforeSend: function () {
			$("#btn_mdl_rec_tiempoext_guardar").prop('disabled', true);
			var strMensaje = 'Espere un momento por favor...';
			$("#div_mdl_rec_tiempoext_mensaje").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if(response != '500'){
				respuesta = JSON.parse(response);					
				if (respuesta.Codigo == '1'){
					$("#div_mdl_rec_tiempoext_mensaje").html('');
					
					var strMensaje = respuesta.Mensaje;
					$("#div_mdl_rec_tiempoext_mensaje").html('<div class="alert alert-warning alert-dismissible" role="alert">'+strMensaje+'</div>');
					$('#modal_tiempoext').modal('hide');
					var table = $('#tbl_tiemposext').DataTable();
					table.ajax.reload();
				} else {
					$("#btn_mdl_rec_tiempoext_guardar").prop('disabled', false);
					var strMensaje = respuesta.Mensaje + '' + respuesta.Error;
					$("#div_mdl_rec_tiempoext_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#div_mdl_rec_tiempoext_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function (data, textStatus, jqXHR) { 
			$("#div_mdl_rec_tiempoext_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + data.status + ' [' + data.statusText + ']'+'</div>');
		}
	});
}

/* ..:: Obtenemos los comentarios ::.. */
function ajax_get_comentarios() {
	try {	
		$('#itxt_timeline_comentario').val('');
		
		var oData = {			
			Id_Solicitud: Id_Solicitud
		};
		
		$.ajax({
			type: "POST",
			url: 'ajax/tiempo_extra/ajax_get_comentarios.php',
			data: oData,
			timeout: 30000,

			beforeSend: function (dataMessage) {
				var strMensaje = 'Consultando informaci&oacute;n, espere un momento por favor...';
				$("#idiv_timeline_mensaje").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					$("#idiv_timeline_mensaje").empty();
					if (respuesta.Codigo == '1'){
						if (respuesta.aComentarios.length > 0) {
							var bInverted = false;
							var oTlUl = $('<ul/>', {'class':'timeline'});
							$.each(respuesta.aComentarios, function (index, value) {
								var oTlHeading = $('<div/>', {'class':'timeline-heading'}).append('<h5>' + value.ct + '</h5>');
								var oTlbody = $('<div/>', {'class':'timeline-body'}).append('<p>' + value.cmt + '</p>');
								if (/SOLICITUD RECHAZADA: /i.test(value.cmt)) {
									value.cmt = value.cmt.replace("SOLICITUD RECHAZADA: ", "");
									oTlbody = $('<div/>', {'class':'timeline-body'}).append('<p><strong style="color:#a94442;">SOLICITUD RECHAZADA: </strong>' + value.cmt + '</p>');
								}
								var oTlFooter = $('<div/>', {'class':'timeline-footer'}).append('<p class="text-right"><i class="fa fa-clock-o"></i> ' + value.dt + '</p>');
								
								var oTlPanel = $('<div/>', {'class':'timeline-panel'}).append(oTlHeading).append(oTlbody).append(oTlFooter);
								
								var oTlBadge;
								var oTlInverted;
								if (bInverted) {
									oTlBadge = $('<div/>', {'class':'timeline-badge'}).append('<a><i class="fa fa-circle invert"></i></a>');
									oTlInverted = $('<li/>', {'class':'timeline-inverted'});
									bInverted = false;
								} else {
									oTlBadge = $('<div/>', {'class':'timeline-badge'}).append('<a><i class="fa fa-circle" id=""></i></a>');
									oTlInverted = $('<li/>');
									bInverted = true;
								}
								
								oTlInverted.append(oTlBadge).append(oTlPanel);
								oTlUl.append(oTlInverted);
							});
							
							oTlUl.append('<li class="clearfix no-float"></li>');
							
							
							$('#idiv_timeline').empty();
							$('#idiv_timeline').append(oTlUl);
						}
						
						var table = $('#tbl_tiemposext').DataTable();
						table.ajax.reload(null, false);
					} else {
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						$("#idiv_timeline_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + strMensaje + '</div>');		
					}
				}else{
					var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
					$("#idiv_timeline_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + strMensaje + '</div>');						
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				$("#idiv_timeline_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + strMensaje + '</div>');
			}
		});
	} catch (err) {
		var strMensaje = 'ajax_get_comentarios() :: ' + err.message;
		$("#idiv_timeline_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + strMensaje + '</div>');
	}    
}

/* ..:: Guardamos un comentario ::.. */
function ajax_set_comentarios() {
	try {		
		$("#idiv_timeline_mensaje").empty();
		
		var sComentario = $('#itxt_timeline_comentario').val().toUpperCase();
		if (!sComentario.trim()) { 
			$("#idiv_timeline_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">Debe ingresar un comentario!</div>');
			return false;
		}
	
		var oData = {			
			Id_Solicitud: Id_Solicitud,
			sComentario: sComentario
		};
		
		$.ajax({
			type: "POST",
			url: 'ajax/tiempo_extra/ajax_set_comentarios.php',
			data: oData,
			timeout: 30000,

			beforeSend: function (dataMessage) {
				var strMensaje = 'Guardando comentario, espere un momento por favor...';
				$("#idiv_timeline_mensaje").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					if (respuesta.Codigo == '1'){
						$("#idiv_timeline_mensaje").html('<div class="alert alert-success alert-dismissible" role="alert">'+respuesta.Mensaje+'</div>');
						
						setTimeout(function () {
							$("#idiv_timeline_mensaje").empty();
						},4000);
						
						setTimeout(function () {
							ajax_get_comentarios();
						},1000);
					}else {
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						$("#idiv_timeline_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + strMensaje + '</div>');		
					}
				}else{
					var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
					$("#idiv_timeline_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + strMensaje + '</div>');						
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				$("#idiv_timeline_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + strMensaje + '</div>');
			}
		});
	} catch (err) {
		var strMensaje = 'ajax_get_comentarios() :: ' + err.message;
		$("#idiv_timeline_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: ' + strMensaje + '</div>');
	}    
}

/*********************************************************************************************************************************
** MESSAJE FUNCTIONS                                                                                                            **
*********************************************************************************************************************************/

//Ejemplo oFunctionOk: se debe pasar una funcion de la siguiente manera "function () { Aqui la funcion o codigo a ejecutar }"
//Ejemplo oFunctionCancel: se debe pasar una funcion de la siguiente manera "function () { Aqui la funcion o codigo a ejecutar }"
function show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel) {
	if (strTitle == '') {
		strTitle = appName;
	}
	$('#modalconfirm_title').html(strTitle);
	$('#modalconfirm_mensaje').html('<i class="fa fa-exclamation-triangle"></i> ' + strQuestion);
	
	//Eliminamos evento click
	$('#modalconfirm_btn_ok').off( "click");
	$('#modalconfirm_btn_cancel').off( "click");
	
	//Reasignamos evento click Boton OK
	$('#modalconfirm_btn_ok').on( "click", function() {
		$('#modalconfirm').modal('hide');
		setTimeout(function () {
			oFunctionOk();
		},500);
	} );
	
	//Reasignamos evento click Boton Cancel
	if (oFunctionCancel == null || oFunctionCancel == undefined) {
		$('#modalconfirm_btn_cancel').on( "click", function() {
			$('#modalconfirm').modal('hide');
		});
	} else {
		$('#modalconfirm_btn_cancel').on( "click", function() {
			oFunctionCancel();
			$('#modalconfirm').modal('hide');
		});
	}	
		
	$('#modalconfirm').modal({ show: true });
}