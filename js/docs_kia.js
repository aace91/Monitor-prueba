	/* **************************************************************************
		VARIABLES
	************************************************************************** */
	var strSessionMessage = 'La sesión del usuario ha finalizado, es necesario iniciar nuevamente.';
	var sGifLoader = '<img src="../images/cargando.gif" height="16" width="16"/>';

	var appName = 'Monitor';
	var oTablePedimentosGrid = null;
	var sReferencia = '';
	
	var aDetalle = new Array();
	var aRemesas = new Array();

	/* **************************************************************************
		FUNCIONES
	************************************************************************** */
	$(document).ready(function () {
		fcn_cargar_grid_pedimentos();
		fcn_inicializa_controles();
	});
	
	function fcn_inicializa_controles(){
		$("#btn_enviar_documentos_pedimento" ).click(function() {ajax_enviar_documentos_cliente();});
		$("#btn_generar_descargar_expediente" ).click(function() {ajax_generar_descargar_expediente_pedimento();});
		
		$('.modal').on('hidden.bs.modal', function (e) { regresar_focus_modal_open();});
		$('.modal').on('shown.bs.modal', function (e) { regresar_focus_modal_open();});
	}
	
	function regresar_focus_modal_open(){
		var oModalsOpen = $('.in');
		if (oModalsOpen.length > 0 ) {$('body').addClass('modal-open');}
	}
	
	function fcn_cargar_grid_pedimentos(pOpt) {
		try {
			if (oTablePedimentosGrid == null) {
				var div_refresh_name = 'div_dtreferencias_refresh';
				
				oTablePedimentosGrid = $('#dtkia').DataTable({
					order: [[0, 'desc']],
					processing: true,
					serverSide: true,
					ajax: {
						"url": "ajax/docs_kia/postPedimentos.php",
						"type": "POST",
						"timeout": 20000,
						"error": handleAjaxError,
						"data": function ( d ) {
							//d.sFiltro = $('#isel_filtro_referencias').val();
							d.search.value = d.search.value.toUpperCase();
						},
					},
					columns: [ 
						{ data: "NUM_REFE",  className: "def_app_center"},
						{ data: "OPERACION", className: "def_app_center"},
						{ data: "ADU_DESP",  className: "def_app_center"},
						{ data: "PAT_AGEN",  className: "def_app_center"},
						{ data: "NUM_PEDI",  className: "def_app_center"},
						{ data: "NUM_PEDI",  className: "def_app_center"},
						{ 
							data: "FEC_ENTR", 
							className: "def_app_center",
							render: function (data) {
								if (data != '' && data != null) { 
									var date = new Date(data);
									var month = date.getMonth() + 1;
									return date.getDate()  + "/" + (month.toString().length > 1 ? month : "0" + month) + "/" + date.getFullYear();
								} else {
									return data;
								}					        
							}
						},
						{ 
							data: "FEC_PAGO", 
							className: "def_app_center",
							render: function (data) {
								if (data != '' && data != null) { 
									var date = new Date(data);
									var month = date.getMonth() + 1;
									return date.getDate()  + "/" + (month.toString().length > 1 ? month : "0" + month) + "/" + date.getFullYear();
								} else {
									return data;
								}					        
							}
						},
						{ 
							data: "NUM_REFE",  
							className: "def_app_center",
							render: function (data) {
								var aBtn = '';
								//aBtn += '<a href="javascript:void(0);" onclick="ajax_consultar_datos_pedimento(\'' + data + '\');return false;" style="padding-left:.5em;" title="">[ <i class="fa fa-envelope" aria-hidden="true"></i> Enviar Documentaci&oacute;n ]</a>';
								aBtn += '<a href="javascript:void(0);" onclick="ajax_consultar_descargar_expediente(\'' + data + '\');return false;" style="padding-left:.5em;" title="">[ <i class="fa fa-download" aria-hidden="true"></i> Descargar Expediente ]</a>';
								return aBtn;
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
					dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
						 "<'row'<'col-xs-8'B><'col-xs-4'<'" + div_refresh_name + "'>>>" +
						 "<'row'<'col-sm-12'tr>>" +
						 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
					buttons: []
				});
			} else {
				pOpt = ((pOpt == null || pOpt == undefined)? false : true);

				var table = oTablePedimentosGrid.DataTable();
				table.ajax.reload(null, pOpt);
			}
		} catch (err) {		
			var strMensaje = 'fcn_cargar_grid_pedimentos() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	/*********************************************************************************************************************************
	** AJAX                                                                                                           **
	*********************************************************************************************************************************/
	
	function ajax_consultar_datos_pedimento(pReferencia){
		try {	
			sReferencia	= pReferencia;
			var oData = {referencia: sReferencia};
			$.ajax({
				type: "POST",
				url: 'ajax/docs_kia/ajax_consulta_datos_pedimento.php',
				data: oData,
				timeout: 30000,				
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor...');
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);
						if (respuesta.Codigo == '1'){
							
							$('#txt_referencia_mdl').val(respuesta.NUM_REFE);
							$('#txt_aduana_mdl').val(respuesta.ADU_DESP);
							$('#txt_patente_mdl').val(respuesta.PAT_AGEN);
							$('#txt_pedimento_mdl').val(respuesta.NUM_PEDI);
							
							aDetalle = respuesta.aPartidas;
							
							inicializa_tabla_detalle_facturas();
							
							$('#modaldatos').modal({ show: true });
							
						}else{
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
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_consultar_datos_pedimento() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function inicializa_tabla_detalle_facturas(){
		var otable = convert_array_table();
		$('#dtdetalle').DataTable({
			data: otable,
			"destroy": true,
			responsive: true,
			aLengthMenu: [
				[5, 10, 50, 100, -1],
				[5, 10, 50, 100, "All"]
			],
			iDisplayLength: 5
		});
		
		setTimeout(function () {$('#dtdetalle').DataTable().columns.adjust().responsive.recalc();},300);
	}
	
	function convert_array_table(){
		var oReturn = new Array();
		for(i = 0; i < aDetalle.length ; i++){
			oPush = [ 
					aDetalle[i][0],
					aDetalle[i][1],
					aDetalle[i][2],
					aDetalle[i][3],
					aDetalle[i][4]
				]
			oReturn.push(oPush);
		}
		return oReturn
	}
	
	function ajax_enviar_documentos_cliente(){
		try {
			var oData = {
				numero_parte: $('#sel_numero_parte').val(),
				referencia: sReferencia
			};
			$.ajax({
				type: "POST",
				url: 'ajax/docs_kia/ajax_enviar_documentos_pedimentos.php',
				data: oData,
				timeout: 30000,				
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor...');
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);
						if (respuesta.Codigo == '1'){
							$('#modaldatos').modal('hide');
							
							$('#idiv_panel_principal_mensaje').html('<div class="alert alert-success">'+respuesta.Mensaje+'</div>');
							
							$('#txt_referencia_mdl').val('');
							$('#txt_aduana_mdl').val('');
							$('#txt_patente_mdl').val('');
							$('#txt_pedimento_mdl').val('');
							
							aDetalle = Array();
							inicializa_tabla_detalle_facturas();
							
							setTimeout(function(){$('#idiv_panel_principal_mensaje').html('');},4000);
							
						}else{
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
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_consultar_datos_pedimento() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function ajax_consultar_descargar_expediente(pReferencia){
		try {	
			sReferencia	= pReferencia;
			var oData = {referencia: sReferencia};
			$.ajax({
				type: "POST",
				url: 'ajax/docs_kia/ajax_consulta_descargar_expediente.php',
				data: oData,
				timeout: 30000,				
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor...');
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);
						if (respuesta.Codigo == '1'){
							aRemesas = respuesta.aRemesas;
							if(aRemesas.length > 0){
								var sHTML = '';var sIds = '';
								$('#lbl_titulo_pedimeto_kia').html(respuesta.Pedimento);
								
								isIE = detectIE();
								for(i=0; i<aRemesas.length; i++){
									sHTML += '<div class="col-md-12">';
									sHTML += '	<div class="form-group">';
									sHTML += '		<label class="control-label">Remesa '+(i+1)+' (pdf)</label>';
									sHTML += '		<input id="upload_remesa_'+(i+1)+'" type="file" class="file-loading" data-show-upload="false" data-allowed-file-extensions=\'["pdf"]\'>';
									sHTML += '	</div>';
									sHTML += '</div>';
									if(i>0) sIds += ',';
									sIds += '#upload_remesa_'+(i+1)+'';
								}
								$('#subir_remesas_pedimento_kia').html(sHTML);
								$(sIds).fileinput({
									previewFileType: "any",
									browseClass: "btn btn-primary",
									browseLabel: " Examinar...",
									browseIcon: "<span class=\"glyphicon glyphicon-folder-open\"></span>",
									removeClass: "btn btn-danger",
									removeLabel: "Eliminar",
									removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i>",
									allowedFileExtensions: ["pdf"],
									showPreview: (isIE ? false : true)
								});
								
								$('#modalexpediente').modal({ show: true });
							}else{
								var strMensaje = "No existen remesas para esta referencia en CASA.";
								show_modal_error(strMensaje);
							}
							
						}else{
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
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_consultar_descargar_expediente() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function ajax_generar_descargar_expediente_pedimento(){
		
		var odata = new FormData();
		for(i=0; i<aRemesas.length; i++){
			var fRemesa = document.getElementById('upload_remesa_'+(i+1)).files[0];
			if(!fRemesa){
				var strMensaje = 'Es necesario agregarar el archivo de la Remesa '+(i+1)+'.';
				$('#mensaje_mdl_descargar_expediente').html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				return false;
			}
		}
		
		for(i=0; i<aRemesas.length; i++){
			var fRemesa = document.getElementById('upload_remesa_'+(i+1)).files[0];
			odata.append('f_remesa_'+((i+1)),fRemesa);
		}
		odata.append('referencia',sReferencia);
		odata.append('nRemesas',aRemesas.length);
		odata.append('numero_parte',$('#sel_numero_parte_exp').val());
		
		$.ajax({
			url:   'ajax/docs_kia/ajax_generar_descargar_expediente_kia.php',
			type:  'post',
			data:	odata,
			contentType: false,
			cache: false,
			processData:false,
			xhr: function()
			{
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt){
				  if (evt.lengthComputable) {
					var percent = (evt.loaded / evt.total * 100).toFixed(0);
					if(percent > 98) percent = 99;
					var sMen = '<div class="progress progress-striped active">';
					sMen += '		<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
					sMen += '			<span>'+percent+'% Completado</span>';
					sMen += '		</div>';
					sMen += '	</div>';
					$('#mensaje_mdl_descargar_expediente').html(sMen);
				  }
				}, false);
				return xhr;
			},
			beforeSend: function () {
				var strMensaje = 'Guardando información, espere un momento por favor...';
				$("#mensaje_mdl_descargar_expediente").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
			},
			success:  function (response) {
				if(response != '500'){
					respuesta = JSON.parse(response);
					if (respuesta.Codigo=='1'){
						$("#mensaje_mdl_descargar_expediente").html('');
						$('#modalexpediente').modal('hide');
						window.open("ajax/docs_kia/descargar_archivo_zip_expediente.php?nom="+respuesta.NombreArchivoZip, '_blank');
					}else{
						var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
						$("#mensaje_mdl_descargar_expediente").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
					}
				}else{
					var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
					$("#mensaje_mdl_descargar_expediente").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
					setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
				}
			},
			error: function(a,b){
				$("#mensaje_mdl_descargar_expediente").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
			},
			timeout: 20000
		});
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
	 
	 /* ..:: Ocultamos mensajes ::.. */
	 function hide_message() {
		$('#idiv_message').hide();
	 }
	 
	/* ..:: Funcion que muestra el mensaje de informacion ::.. */
	function show_message_info(sMensaje) {
		if (sMensaje == null || sMensaje == undefined || sMensaje == '') {
			$('#idiv_message').hide();
		} else {
			var sHtml = '<div class="alert alert-info">';
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
			var sHtml = '<div class="alert alert-success">';
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
			var sHtml = '<div class="alert alert-danger">';
			sHtml +=	'	<strong>Error!</strong> ' + sMensaje;
			sHtml +=    '</div>';
			
			$('#idiv_message').html(sHtml);
			$('#idiv_message').show();
		}
	}

	/* ///////////////////////////////////////////
	DETECTAR EL EXPLORADOR
	//////////////////////////////////////////// */

	function detectIE() {
	  var ua = window.navigator.userAgent;

	  // Test values; Uncomment to check result …

	  // IE 10
	  // ua = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)';
	  
	  // IE 11
	  // ua = 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko';
	  
	  var msie = ua.indexOf('MSIE ');
	  if (msie > 0) {
		// IE 10 or older => return version number
		return true;//parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
	  }

	  var trident = ua.indexOf('Trident/');
	  if (trident > 0) {
		// IE 11 => return version number
		return true;
	  }

	  // other browser
	  return false;
	}

