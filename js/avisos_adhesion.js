	/* 
	* Application: Monitor Del Bravo
	* 
	* 
	* 
	* Copyright (c) 2017 DEL BRAVO. - all right reserved
	*/

	/*********************************************************************************************************************************
	** GLOBALS DEFINITION SECTION                                                                                                   **
	*********************************************************************************************************************************/

	/* ..:: App Vars ::.. */
	var appName = 'Monitor';
	var strSessionMessage = 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
	var sGifLoader = '<img src="../images/cargando.gif" height="16" width="16"/>';
	var oPrincipalGeneralGrid = null;
	var bSubirDoc = false;
	var sIdPermiso = '';
	var sAction = '';
	
	/*********************************************************************************************************************************
	** BEGIN APPLICATION                                                                                                            **
	*********************************************************************************************************************************/

	$(document).ready(function () {
		//Esta funcion se dispara cuando se terminan de cargar todos los elementos de la pagina { .js, .css, images, etc. }
		application_load();
	});

	/*********************************************************************************************************************************
	** END APPLICATION                                                                                                              **
	*********************************************************************************************************************************/

	function session_close() {
		document.location = 'pagina_despedida.aspx';
	}

	/*********************************************************************************************************************************
	** EXTERNAL APPLICATIONS                                                                                                        **
	*********************************************************************************************************************************/

	/*********************************************************************************************************************************
	** WINDOW RESIZE                                                                                                                **
	*********************************************************************************************************************************/

	function onWinResize() {
		if (document.body.parentNode.clientHeight < 710) 
			$('.dataTables_scrollBody').height('100px');		
		else 			                                 
			$('.dataTables_scrollBody').height((document.body.parentNode.clientHeight - 550) + 'px');
	}

	/*********************************************************************************************************************************
	** Application LOAD { Load Preliminar Data by Ajax }                                                                            **
	*********************************************************************************************************************************/

	function application_load() {
		application_form();
		application_run();
	}

	/*********************************************************************************************************************************
	** APPLICATION FORM { Generate HTML Content }                                                                                   **
	*********************************************************************************************************************************/

	function application_form() {
		$(window).resize(function() { onWinResize(); }); onWinResize();
	}

	/*********************************************************************************************************************************
	** APPLICATION RUN                                                                                                              **
	*********************************************************************************************************************************/

	function application_run() {
		try {
			$.fn.dataTable.ext.errMode = 'none';
			
			$('#modal_nvo_permiso').on('hidden.bs.modal', function (e) {
				var oModalsOpen = $('.in');
				if (oModalsOpen.length > 0 ) {
					$('body').addClass('modal-open');
				}
			});

			//itxt_modal_subir_excel_referencia').focusout(function() { fcn_modal_subir_excel_verificar_pedimento(); });

			$('#txt_modal_vig_fechaini, #txt_modal_vig_fechafin').datepicker({
				format: 'dd/mm/yyyy',
				language: "es",
				autoclose: true
			});
			
			
			var oTouchSpinProp = {
				verticalbuttons: true,
				min: 0,
				max: 1000000000,
				step: 0.1,
				decimals: 2
			}
			fcn_cargar_grid_principal_general();
				
			$("#upload_aviso_auto").fileinput({
				previewFileType: "any",
				browseClass: "btn btn-primary",
				browseLabel: " Examinar...",
				browseIcon: "<span class=\"glyphicon glyphicon-folder-open\"></span>",
				removeClass: "btn btn-danger",
				removeLabel: "Eliminar",
				removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i>",
				allowedFileExtensions: ["pdf"]
			});
			
		} catch (err) {		
			var strMensaje = 'application_run() :: ' + err.message;
			show_modal_error(strMensaje);
		}

	}

	function agregar_permiso_pedimento() {
		try {
			sAction = 'Nuevo';
			bSubirDoc = true;
			$("#ver_archivo_pdf_aviso").hide();
			$("#subir_archivo_aviso").show();
			
			$('#txt_modal_numero_permiso').val('');
			$('#sel_modal_cliente').val('0');
			$('#txt_modal_vig_fechaini').val('');
			$('#txt_modal_vig_fechafin').val('');
			$('#idiv_modal_guardar_permiso').html('');
			$("#upload_aviso_auto").fileinput('clear');
			$('#modal_nvo_permiso').modal({show: true,backdrop: 'static',keyboard: false});
		} catch (err) {
			var strMensaje = 'fcn_modal_show_subir_excel() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function validar_controles_permiso_nuevo(){
		if($('#txt_modal_numero_permiso').val().trim() == ''){
			var strMensaje = 'Es necesario que agregué el número de permiso.';
			$('#idiv_modal_guardar_permiso').html('<div class="alert alert-danger"><strong>Error:</strong> '+strMensaje+'</div>');
			$('#txt_modal_numero_permiso').focus();
			return false;
		}
		if($('#sel_modal_cliente').val().trim() == '0'){
			var strMensaje = 'En necesario seleccionar el cliente.';
			$('#idiv_modal_guardar_permiso').html('<div class="alert alert-danger"><strong>Error:</strong> '+strMensaje+'</div>');
			$('#sel_modal_cliente').focus();
			return false;
		}
		if($('#txt_modal_vig_fechaini').val().trim() == ''){
			var strMensaje = 'En necesario seleccionar la fecha inicial de vigencia.';
			$('#idiv_modal_guardar_permiso').html('<div class="alert alert-danger"><strong>Error:</strong> '+strMensaje+'</div>');
			$('#txt_modal_vig_fechaini').focus();
			return false;
		}
		if($('#txt_modal_vig_fechafin').val().trim() == ''){
			var strMensaje = 'En necesario seleccionar la fecha final de vigencia.';
			$('#idiv_modal_guardar_permiso').html('<div class="alert alert-danger"><strong>Error:</strong> '+strMensaje+'</div>');
			$('#txt_modal_vig_fechafin').focus();
			return false;
		}
		
		if(bSubirDoc){
			var ppermiso = document.getElementById('upload_aviso_auto').files[0];
		
			if(!ppermiso){
				var strMensaje = 'Es necesario agregarar el documento del aviso de adhesion en formato PDF.';
				$('#idiv_modal_guardar_permiso').html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				return false;
			}
		}
		return true;
	}
	
	function guardar_aviso_automatico(){
		switch(sAction){
			case 'Nuevo':
				ajax_guardar_aviso_automatico();
				break;
			case 'Editar':
				ajax_editar_aviso_automatico();
				break;
		}
	}
	
	function ajax_editar_aviso_automatico(){
		if(!validar_controles_permiso_nuevo()){
			return false;
		}		
		try{	
			var oData = new FormData();
			
			oData.append('id_permiso', sIdPermiso);
			oData.append('numero_permiso', $('#txt_modal_numero_permiso').val().trim().toUpperCase());
			oData.append('id_cliente', $('#sel_modal_cliente').val().trim());
			var fecha_ini = $('#txt_modal_vig_fechaini').val().split('/');
			var fecha_fin = $('#txt_modal_vig_fechafin').val().split('/');
			oData.append('fecha_ini', fecha_ini[2] + '-' + fecha_ini[1] + '-' + fecha_ini[0] + ' 00:00:00');
			oData.append('fecha_fin', fecha_fin[2] + '-' + fecha_fin[1] + '-' + fecha_fin[0] + ' 00:00:00');
			if(bSubirDoc){
				oData.append('documento_permiso',document.getElementById('upload_aviso_auto').files[0]);
			}
			oData.append('bSubirDoc',bSubirDoc);
			
			$.ajax({
				type: "POST",
				url: 'ajax/avisos_adhesion/ajax_editar_permiso_adhesion.php',
				data: oData,
				contentType: false,
				cache: false,
				processData:false,
				timeout: 0,
				xhr: function() {
					$('#modal_info').modal({show: true,backdrop: 'static',keyboard: false});
					
					var xhr = new window.XMLHttpRequest();
					xhr.upload.addEventListener("progress", function(evt){
					  if (evt.lengthComputable) {
						var percent = evt.loaded / evt.total * 100;
						if(percent > 89) 
							percent = 90;
						var sMen = '<div class="progress progress-striped active">';
						sMen += '		<div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
						sMen += '			<span>'+percent+'% Completado, Procesando Archivo, espere un momento por favor...</span>';
						sMen += '		</div>';
						sMen += '	</div>';
						$('#idiv_modal_info_mensaje').html(sMen);
					  }
					}, false);
					return xhr;
				},
				beforeSend: function (dataMessage) {
					
				},
				success:  function (response) {
					$('#modal_info').modal('hide');		

					if (response != '500'){
						var respuesta = JSON.parse(response);
						
						if (respuesta.Codigo == '1'){
							$('#modal_nvo_permiso').modal('hide');
							
							fcn_cargar_grid_principal_general();

							setTimeout(function () {
								show_modal_ok(respuesta.Mensaje);							
							},500);
						} else {
							var strMensaje = respuesta.Mensaje + respuesta.Error;
							show_modal_error(strMensaje);
						}
					}else{
						show_modal_error(strSessionMessage);					
						setTimeout(function () {window.location.replace('../logout.php');},4000);
					}				
				},
				error: function(a,b){
					var strMensaje = a.status+' [' + a.statusText + ']';
					show_modal_error(strMensaje);
					
					$('#modal_info').modal('hide');	
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_set_archivo_excel() :: ' + err.message;
			show_modal_error(strMensaje);
		}  
	}
	
	function ajax_guardar_aviso_automatico(){
		if(!validar_controles_permiso_nuevo()){
			return false;
		}		
		try{	
			var oData = new FormData();	
			oData.append('numero_permiso', $('#txt_modal_numero_permiso').val().trim().toUpperCase());
			oData.append('id_cliente', $('#sel_modal_cliente').val().trim());
			var fecha_ini = $('#txt_modal_vig_fechaini').val().split('/');
			var fecha_fin = $('#txt_modal_vig_fechafin').val().split('/');
			oData.append('fecha_ini', fecha_ini[2] + '-' + fecha_ini[1] + '-' + fecha_ini[0] + ' 00:00:00');
			oData.append('fecha_fin', fecha_fin[2] + '-' + fecha_fin[1] + '-' + fecha_fin[0] + ' 00:00:00');
			
			oData.append('documento_permiso',document.getElementById('upload_aviso_auto').files[0]);
			
			$.ajax({
				type: "POST",
				url: 'ajax/avisos_adhesion/ajax_guardar_permiso_adhesion.php',
				data: oData,
				contentType: false,
				cache: false,
				processData:false,
				timeout: 0,
				xhr: function() {
					$('#modal_info').modal({show: true,backdrop: 'static',keyboard: false});
					
					var xhr = new window.XMLHttpRequest();
					xhr.upload.addEventListener("progress", function(evt){
					  if (evt.lengthComputable) {
						var percent = evt.loaded / evt.total * 100;
						if(percent > 89) 
							percent = 90;
						var sMen = '<div class="progress progress-striped active">';
						sMen += '		<div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
						sMen += '			<span>'+percent+'% Completado, Procesando Archivo, espere un momento por favor...</span>';
						sMen += '		</div>';
						sMen += '	</div>';
						$('#idiv_modal_info_mensaje').html(sMen);
					  }
					}, false);
					return xhr;
				},
				beforeSend: function (dataMessage) {
					
				},
				success:  function (response) {
					$('#modal_info').modal('hide');		

					if (response != '500'){
						var respuesta = JSON.parse(response);
						
						if (respuesta.Codigo == '1'){
							$('#modal_nvo_permiso').modal('hide');
							
							fcn_cargar_grid_principal_general();

							setTimeout(function () {
								show_modal_ok(respuesta.Mensaje);							
							},500);
						} else {
							var strMensaje = respuesta.Mensaje + respuesta.Error;
							show_modal_error(strMensaje);
						}
					}else{
						show_modal_error(strSessionMessage);					
						setTimeout(function () {window.location.replace('../logout.php');},4000);
					}				
				},
				error: function(a,b){
					var strMensaje = a.status+' [' + a.statusText + ']';
					show_modal_error(strMensaje);
					
					$('#modal_info').modal('hide');	
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_set_archivo_excel() :: ' + err.message;
			show_modal_error(strMensaje);
		}  
	}
	
	function fcn_cargar_grid_principal_general() {
		try {
			if (oPrincipalGeneralGrid == null) {
				
				oPrincipalGeneralGrid = $('#dt_principal_general').DataTable({
					//order: [[1, 'desc']],
					processing: true,
					serverSide: true,
					ajax: {
						"url": "ajax/avisos_adhesion/postAvisosAdhesion.php",
						"type": "POST",
						"timeout": 20000,
						"error": handleAjaxError
					},
					columns: [ 
						{ "data": "id_permiso_adhesion" },
						{ "data": "numero_permiso" },
						{ "data": "vigencia" },
						{ "data": "cliente" },
						{ "data": "id_permiso_adhesion",
							"mRender": function (data, type, row) {
									var sBtnAction = '';
									sBtnAction += '<a href="javascript:void(0);" onclick="ajax_consulta_aviso_adhesion(\''+data+'\');return false;" style="padding-left:.5em;" title="">[<i class="fa fa-pencil" aria-hidden="true"></i> Editar]</a>';
									sBtnAction += '<a href="'+row.documento+'" target="_blank" style="padding-left:.5em;" title="">[<i class="fa fa-eye" aria-hidden="true"></i> Ver Documento]</a>';
									return sBtnAction;
								} 
						}
					],
					responsive: true,
					aLengthMenu: [
						[10, 25, 50, 100, 200, -1],
						[10, 25, 50, 100, 200, "All"]
					],
					iDisplayLength: 10,
					language: {
						sProcessing: '<img src="../images/cargando.gif" height="18" width="18"> Cargando, espera un momento por favor...',
						lengthMenu: "Mostrar _MENU_ registros por p&aacute;gina",
						search:         "Buscar:",
						info: "Mostrando p&aacute;gina _PAGE_ de _PAGES_ p&aacute;ginas de _TOTAL_ registros",
						zeroRecords:    "No se encontraron registros",
						infoEmpty:      "Mostrando 0 a 0 de 0 registros",
						infoFiltered:   "(filtrado de un total de _MAX_ registros)",
						paginate: {
							first:      "Primero",
							last:       "&Uacute;ltimo",
							next:       "Siguiente",
							previous:   "Anterior"
						},
						select: {
							rows: {
								_: "",
								0: "",                    
								1: "1 fila seleccionada"
							}
						}
					},
					buttons: [
						{
							extend: 'print',
							className: 'pull-left',
							text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
							title: '<h2>Lista de salidas</h2>',
							exportOptions: {
								columns: [ 0, 1, 2 ]
							}
						}
					]
				});
			} else {
				oPrincipalGeneralGrid.ajax.reload(null, false);	
			}
		} catch (err) {		
			var strMensaje = 'fcn_cargar_grid_principal_general() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function ajax_consulta_aviso_adhesion(IdPermiso){
		sIdPermiso = IdPermiso;
		
		$.ajax({
			type: "POST",
			url: 'ajax/avisos_adhesion/ajax_consultar_permiso_adhesion.php',
			data: {id_permiso: IdPermiso},
			beforeSend: function (dataMessage) {
				$("#idiv_principal_message").html('<div class="alert alert-info">'+sGifLoader + ' Cargando informaci&oacute;n, espere un momento por favor...</div>');
			},
			success:  function (response) {
				$("#idiv_principal_message").html('');
				if (response != '500'){
					var respuesta = JSON.parse(response);
					if (respuesta.Codigo == '1'){
						
						$('#txt_modal_numero_permiso').val(respuesta.permiso);
						$('#sel_modal_cliente').val(respuesta.id_cliente);
						$('#txt_modal_vig_fechaini').val(respuesta.fecha_ini);
						$('#txt_modal_vig_fechafin').val(respuesta.fecha_fin);
						
						$("#ver_archivo_pdf_aviso").html('<div class="alert alert-info"><a href="'+respuesta.documento+'" target="_blank"><strong>[<i class="fa fa-eye" aria-hidden="true"></i> Ver Documento]</a> <a class="pull-right" href="javascript:void(0);" onclick="subir_archivo_aviso_mostrar();return false;">[<i class="fa fa-upload" aria-hidden="true"></i> Subir Documento]</a></strong></div>');
						$("#ver_archivo_pdf_aviso").show();
						$("#subir_archivo_aviso").hide();
						$("#upload_aviso_auto").fileinput('clear');
						bSubirDoc = false;
						sAction = 'Editar';
						
						$('#modal_nvo_permiso').modal({show: true,backdrop: 'static',keyboard: false});
						
					} else {
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				}else{
					alert(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				$("#idiv_principal_message").html('');
				var strMensaje = a.status+' [' + a.statusText + ']';
				alert(strMensaje);
			}
		});
	}
		
	function subir_archivo_aviso_mostrar(){
		bSubirDoc =  true;
		$("#ver_archivo_pdf_aviso").html('');
		$("#upload_aviso_auto").fileinput('clear');
		$("#ver_archivo_pdf_aviso").hide();
		$("#subir_archivo_aviso").show();
	}

	
	
	
	/* ..:: Capturamos los errores ::.. */
function handleAjaxError( xhr, textStatus, error ) {
	if ( textStatus === 'timeout' ) {
		show_message_error('El servidor tardó demasiado en enviar los datos');
	} else {
		show_message_error('Se ha producido un error en el servidor. Por favor espera.');
		
		setTimeout(function(){ hide_message(); }, 5000);
	}
}

/* ..:: Capturamos los errores ::.. */
function on_grid_error(e, settings, techNote, message) {
	var bExist = message.includes("Code [500]");
	if(bExist) {
		show_message_error(strSessionMessage);					
		setTimeout(function () {window.location.replace('../logout.php');},4000);
	} else {
		show_message_error('Ha ocurrido un error: ' + message);
		setTimeout(function(){ hide_message(); }, 5000);
	}
}


/*********************************************************************************************************************************
** MESSAJE FUNCTIONS                                                                                                            **
*********************************************************************************************************************************/
/* ..:: Funcion que muestra el mensaje del loading  ::.. */
function show_load_config(bShow, sMensaje) {
	if (bShow) {
		if (sMensaje == null || sMensaje == undefined) {
			sMensaje = 'Consultando, espere un momento por favor...';
		}
		
		$('#modalloadconfig').modal({ show: true, backdrop: 'static', keyboard: false });
		$("#modalloadconfig_mensaje").html(sGifLoader + ' ' + sMensaje);
	} else {
		$('#modalloadconfig').modal('hide');
	}
}

/* ..:: Funcion que muestra el mensaje de ok ::.. */
function show_modal_ok(sMensaje) {
	if (sMensaje == null || sMensaje == undefined) {
		sMensaje = '';
	}
	
    $('#modalmessagebox_ok_titulo').html(appName);
	$('#modalmessagebox_ok_mensaje').html('<i class="fa fa-check"></i> ' + sMensaje);						
	setTimeout(function () {
		$('#modalmessagebox_ok').modal({ show: true });
	},500);
}

/* ..:: Funcion que muestra el mensaje de error (lblError) ::.. */
function show_modal_error(sMensaje) {
	if (sMensaje == null || sMensaje == undefined) {
		sMensaje = '';
	}
	
	show_load_config(false);
	
    $('#modalmessagebox_error_span').html("ERROR :: " + appName);
	$('#modalmessagebox_error_mensaje').html('<i class="fa fa-ban"></i> ' + sMensaje);
	setTimeout(function () {
		$('#modalmessagebox_error').modal({ show: true });
	},500);
}

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

/********/

function fcn_get_message_error(sMensaje) {
	var sHtml = '<div class="alert alert-danger" style="margin-bottom:0px;">';
	sHtml +=	'	<strong>Error!</strong> ' + sMensaje;
	sHtml +=    '</div>';

	return sHtml;
}

/********/
 
 /* ..:: Ocultamos mensajes ::.. */
 function hide_message() {
	$('#idiv_message').hide();
 }
 
/* ..:: Funcion que muestra el mensaje de informacion ::.. */
function show_message_info(sMensaje) {
	if (sMensaje == null || sMensaje == undefined || sMensaje == '') {
		$('#idiv_message').hide();
	} else {
		var sHtml = '<div class="alert alert-info" style="margin-top: 8px; margin-bottom: 8px;">';
		sHtml +=	'	<strong>Info!</strong> ' + sMensaje;
		sHtml +=    '</div>';
		
		$('#idiv_message').html(sHtml);
		$('#idiv_message').show();
	}
}

/* ..:: Funcion que muestra el mensaje de ok ::.. */
function show_message_ok(sMensaje) {
	if (sMensaje == null || sMensaje == undefined || sMensaje == '') {
		$('#idiv_message').hide();
	} else {
		var sHtml = '<div class="alert alert-success" style="margin-top: 8px; margin-bottom: 8px;">';
		sHtml +=	'	<strong>Exito!</strong> ' + sMensaje;
		sHtml +=    '</div>';
		
		$('#idiv_message').html(sHtml);
		$('#idiv_message').show();
	}
}

/* ..:: Funcion que muestra el mensaje de error ::.. */
function show_message_error(sMensaje) {
	if (sMensaje == null || sMensaje == undefined || sMensaje == '') {
		$('#idiv_message').hide();
	} else {
		var sHtml = '<div class="alert alert-danger" style="margin-top: 8px; margin-bottom: 8px;">';
		sHtml +=	'	<strong>Error!</strong> ' + sMensaje;
		sHtml +=    '</div>';
		
		$('#idiv_message').html(sHtml);
		$('#idiv_message').show();
	}
}