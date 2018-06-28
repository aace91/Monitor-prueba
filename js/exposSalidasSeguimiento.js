/* 
* Application: 
* Author: Ing. Juan Carlos de la Cruz
* email: carlos7999@hotmail.com
* 
* Copyright (c) 2016 DEL BRAVO. - all right reserved
*/

/*********************************************************************************************************************************
** GLOBALS DEFINITION SECTION                                                                                                   **
*********************************************************************************************************************************/

var fecha = [];

/* ..:: App Vars ::.. */
var appName = 'Monitor';
var strSessionMessage = 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
var sGifLoader = '<img src="../images/cargando.gif" height="16" width="16"/>';

var oTableFoliosGrid = null;

/* Variables Folio */
var __sIdFolio;

var __nTimerSecondsSync = 300; //Representado en segundos
var __nCountSync = __nTimerSecondsSync;
var __oTimerSync;

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

//funciones que llamen a otras paginas en otras ventanas

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

    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    /*++ INICIALIZATION FORM SECTION {Basic Structure}                                                                              ++*/
    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

    //Aquí creo el Html para agregarlo dentro del div principal, etc

    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    /*++ ENVIRONMENT SECTION                                                                                                        ++*/
    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

    $(window).resize(function() { onWinResize(); }); onWinResize();
}

/*********************************************************************************************************************************
** APPLICATION RUN                                                                                                              **
*********************************************************************************************************************************/

function application_run() {
    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    /*++ Fill Data Controls                                                                                                         ++*/
    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    try {
		$.fn.dataTable.ext.errMode = 'none';
		
		/**********************************/
		
		$('#isel_clientes_expo').select2({
			theme: "bootstrap",
			width: "off",
			placeholder: "Selecciona un Cliente"
		});
		
		/* ..:: Configuramos el FileInput ::.. */
		$("#ifile_documentos").fileinput({
			uploadUrl: "ajax/exposSalidasSeguimiento/ajax_upload_files.php", // server upload action
			uploadAsync: false
		}).on('fileuploaded', function(e, params) {
			ajax_get_archivos();
		}).on('filebatchuploadsuccess', function(event, data, previewId, index) {
			ajax_get_archivos();
			// var form = data.form, files = data.files, extra = data.extra,
				// response = data.response, reader = data.reader;
			// console.log('File batch upload success');
		}).on("filepredelete", function(jqXHR) {
			var abort = true;
			if (confirm("Desea eliminar el siguiente archivo?")) {
				abort = false;
			}
			return abort; // you can also send any data/object that you can receive on `filecustomerror` event
		}).on('filedeleted', function(event, key) {
			console.log('Key = ' + key);
		});
		
		$('div.modal').on('hidden.bs.modal', function (e) {
			setTimeout(function(){ 
				var oModalsOpen = $('.modal.in');
				if (oModalsOpen.length > 0 ) {
					$('body').addClass('modal-open');
				} else {
					$('body').removeClass('modal-open');
				}
			}, 700);			
		});
		
		__oTimerSync = setTimeout(function () { ajax_set_update_sync(); }, (__nTimerSecondsSync * 1000));
		setInterval(function(){ 
			__nCountSync -= 1;
			if (__nCountSync <= 0) {
				__nCountSync = 0;
			}
			
			$('#ispan_sync_message').html('<i class="fa fa-clock-o" aria-hidden="true"></i> Sincronizando en ' + __nCountSync + ' Segundos');
		}, 1000);	

		fcn_cargar_grid_folios();
    } catch (err) {		
		var strMensaje = 'application_run() :: ' + err.message;
		show_modal_error(strMensaje);
    }

    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    /*++ Buttons Events                                                                                                             ++*/
    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
}

/*********************************************************************************************************************************
** FILL AND CREATE GRIDS FUNCTIONS                                                                                              **
*********************************************************************************************************************************/

function fcn_cargar_grid_folios(){
	try {
		if (oTableFoliosGrid == null) {
			var oDivDisplayErrors = 'idiv_mensaje';
			var div_table_name = 'dtfolios';
			var div_refresh_name = div_table_name + '_refresh';	
			
			oTableFoliosGrid = $('#' + div_table_name);
			
			oTableFoliosGrid.removeAttr('width').DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/exposSalidasSeguimiento/postFolios.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
			            d.sIdCliente = $('#isel_clientes_expo').val();
					},
					'beforeSend': function (request) {
						show_custom_function_ok('', oDivDisplayErrors);
					},
					"error": handleAjaxError
				},
				scrollY: "400px",
				scrollX:        true,
				fixedColumns: {
					leftColumns: 2
				},
				columns: [ 
					{ data: "id_folio", className: "def_app_center"},	
					{ data: "estatus", className: "def_app_center"},
					{ data: "fecha_alta", className: "def_app_center"},
					{ data: "caja", className: "def_app_center"},
					{ data: "facturas", className: "def_app_center"},
					{ data: "nombre_logistica", className: "def_app_center"},
					{ data: "linea_transportista_nombre", className: "def_app_center"},
					{ data: "fecha_aprobado", className: "def_app_center" },
					{
						data: "bcomentario",
						className: "def_app_center",
						render: function ( data, type, row ) {
							var sHtml = '';
							if (type == 'display') { 
								if (data == "false" || data == false) {
									sHtml = '<a class="editor_dtfolios_comentarios"><i class="fa fa-comments" aria-hidden="true"></i> Agregar</a>';
								} else {
									sHtml = '<a class="editor_dtfolios_comentarios"><img src="../images/comentarios.gif" width="15" height="15" border="0" /> Ver</a>';sHtml = '<a class="editor_dtfolios_comentarios">';
									sHtml += '   <i class="fa fa-eye" aria-hidden="true"></i> Ver'
									if (row.bnuevo_comentario == 'true' || row.bnuevo_comentario == true) {
										sHtml += '&nbsp; <img src="../images/nuevo_comentario.gif" width="21" height="18" border="0" />';
									}
									sHtml += '</a>';
								}
							}							
							return sHtml;
						}
					},
					{
						data: null,
						className: "def_app_center",
						render: function ( data, type, row ) { 
							var sHtml = '';
							if (type == 'display') {
								sHtml = '<a class="editor_dtfolios_documentos"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Documentos</a>';	
							}
							return sHtml;
						}
					}/*,
					{
						data: null,
						className: "def_app_center",
						render: function ( data, type, row ) {
							var sHtml = '';
							if (type == 'display') {
								if (row.fecha_aprobado == null || row.fecha_aprobado == '') {
									sHtml = '<a class="editor_dtfolios_editar"><i class="fa fa-pencil" aria-hidden="true"></i> Editar</a>';
								}
							}
							return sHtml;
						}
					}*/
				],
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
					if ( aData.fecha_aprobado != '') {
						$('td', nRow).css('background-color', '#dff0d8');
					}
				},
				responsive: false,
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
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_folios();"');
			$("div." + div_refresh_name).html(sButton);
				
			oTableFoliosGrid.on('click', 'a.editor_' + div_table_name + '_comentarios', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oTableFoliosGrid);		
					
					__sIdFolio = oData.id_folio;

					fcn_folio_comentarios();
				} catch (err) {		
					var strMensaje = 'editor_' + div_table_name + '_comentarios() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oTableFoliosGrid.on('click', 'a.editor_' + div_table_name + '_documentos', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oTableFoliosGrid);		
					
					__sIdFolio = oData.id_folio;

					fcn_folio_documentos();
				} catch (err) {		
					var strMensaje = 'editor_' + div_table_name + '_documentos() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oTableFoliosGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			oTableFoliosGrid.DataTable().search('').ajax.reload(null, false);
			setTimeout(function(){ oTableFoliosGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_folios() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

//===============================================================\\
// FUNCIONES PARA LOS GRIDS
//===============================================================\\

/* ..:: Obtenemos los datos del row ::.. */
function fcn_get_row_data($this, oGrid) {
	var current_row = $this.parents('tr');//Get the current row
	if (current_row.hasClass('child')) {//Check if the current row is a child row
		current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
	}

	var oData = oGrid.DataTable().row(current_row).data();	

	return oData;
}

/* ..:: Creamos botones para el datatables ::.. */
function fcn_create_button_datatable(sAriaControls, sBtnTxt, oFunction = '') {
	var sHtml = '';
	
	sHtml += '<a class="btn btn-default buttons-selected-single pull-right"';
	sHtml += '    tabindex="0"';
	sHtml += '    aria-controls="' + sAriaControls + '"';
	sHtml += ' ' + oFunction;
	sHtml += '    >';
	sHtml += '    <span>'+ sBtnTxt +'</span>';
	sHtml += '</a>';
	
	return sHtml;
}

/* ..:: Capturamos los errores ::.. */
function handleAjaxError( xhr, textStatus, error ) {
	if ( textStatus === 'timeout' ) {
		show_modal_error('El servidor tardó demasiado en enviar los datos');
	} else {
		show_modal_error('Se ha producido un error en el servidor, error: [' + error + ']. Por favor espera.');
	}
}

/* ..:: Capturamos los errores ::.. */
function on_grid_error(e, settings, techNote, message, oDivDisplay) {
	var bExist = message.includes("Code [500]");

	if(bExist) {
		show_modal_error(strSessionMessage);					
		setTimeout(function () {window.location.replace('../logout.php');},4000);
	} else {
		var sMensaje = 'Ha ocurrido un error: ' + message;
		show_custom_function_error(sMensaje, oDivDisplay);
	}
}

/*********************************************************************************************************************************
** CONTROLS FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

/*****************************************/
/* COMENTARIOS */
/*****************************************/

/* ..:: Mostramos la modal de los comentarios ::.. */
function fcn_folio_comentarios() {
	try {		
		$('#idiv_timeline').empty();
		$('#modal_timeline').modal({ show: true });
		ajax_get_comentarios();
	} catch (err) {
		var strMensaje = 'fcn_folio_comentarios() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*****************************************/
/* DOCUMENTOS */
/*****************************************/

/* ..:: Mostramos los documentos de la salida ::.. */
function fcn_folio_documentos() {
	try {
		var aPreview = new Array(); 
		var aPreviewConfig = new Array(); 
		fcn_fill_file_input(aPreview, aPreviewConfig);
		
		$('#modal_subir_docs').modal({ show: true });
		ajax_get_archivos();
	} catch (err) {
		var strMensaje = 'fcn_folio_documentos() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: Creamos el objeto FileInput y lo llenamos de datos ::.. */
function fcn_fill_file_input(aPreview, aPreviewConfig) {
	try {
		$("#ifile_documentos").fileinput('destroy');
		//$("#ifile_documentos").fileinput('clear');
		$("#ifile_documentos").fileinput({
			language: "es",
			uploadUrl: "ajax/exposSalidasSeguimiento/ajax_upload_files.php", // server upload action
			uploadAsync: false,
			uploadExtraData: function() {
				return {
					sIdFolio: __sIdFolio
				};
			},
			showRemove: false,
			minFileCount: 1,
			maxFileCount: 5,
			resizeImage: true,
			overwriteInitial: false,
			initialPreview: aPreview,
			initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup
			initialPreviewFileType: 'image', // image is the default and can be overridden in config below
			initialPreviewConfig: aPreviewConfig,
			previewSettings: {
				image: {width: "200px", height: "160px"}
			},
			fileActionSettings: {
				showUpload: false
			}/*,
			allowedFileExtensions: ["jpg", "png", "gif", "pdf"]*/
			/*,
			uploadExtraData: {
				img_key: "1000",
				img_keywords: "happy, places",
			}*/
		});
		
		if (aPreviewConfig.length > 0) {
			$.each(aPreviewConfig, function (index, value) {
				//var oElemento = $('.file-preview-frame[title="' + value.nombre_archivo + '"]').find('.file-actions')[0];
				var oElemento = $('.file-footer-caption[title="' + value.nombre_archivo + '"]').parent().find('.file-actions')[0];
				var oActions = $(oElemento).find('.file-footer-buttons');

				var oButton = $('<button/>', {
					html: '<i class="fa fa-download" aria-hidden="true"></i>',
			        title: 'Descargar',
			        class: 'btn btn-xs btn-default',
			        type: 'button',
			        click: function (event) { 
			        	event.preventDefault();
			        	//document.getElementById('my_iframe').src = value.url_download;
			        	window.open(value.url_download);
			        	//window.location.href = value.url_download;
			        }
			    });

			    oActions.prepend(oButton);
				//oActions.prepend('<button type="button" class="btn btn-xs btn-default" title="Descargar" onClick="javascript:fcn_download_file(\'' + value.url_download + '\');"><i class="fa fa-download" aria-hidden="true"></i></button>')
			});
		}

		$('div.close.fileinput-remove').hide();
	} catch (err) {
		var strMensaje = 'fcn_fill_file_input() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/*****************************************/
/* COMENTARIOS */
/*****************************************/

/* ..:: Obtenemos los comentarios ::.. */
function ajax_get_comentarios() {
	try {	
		$('#itxt_timeline_comentario').val('');
		
		var oData = {			
			sIdFolio: __sIdFolio
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidasSeguimiento/ajax_get_comentarios.php',
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
						if (respuesta.aComentarios.length > 0) {
							var bInverted = false;
							var oTlUl = $('<ul/>', {'class':'timeline'});
							$.each(respuesta.aComentarios, function (index, value) {
								var oTlHeading = $('<div/>', {'class':'timeline-heading'}).append('<h5>' + value.ct + '</h5>');
								var oTlbody = $('<div/>', {'class':'timeline-body'}).append('<p>' + value.cmt + '</p>');
								var oTlFooter = $('<div/>', {'class':'timeline-footer'}).append('<p class="text-right">' + value.dt + '</p>');
								
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
						
						fcn_cargar_grid_folios();
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
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_comentarios() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Guardamos un comentario ::.. */
function ajax_set_comentarios() {
	try {		
		$('#idiv_timeline_mensaje').hide();
		
		var sComentario = $('#itxt_timeline_comentario').val().toUpperCase();
		if (!sComentario.trim()) { 
			show_custom_function_error('Debe ingresar un comentario!', 'idiv_timeline_mensaje', 'margin: 0px;');
			return;
		}
	
		var oData = {			
			sIdFolio: __sIdFolio,
			sComentario: sComentario
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidasSeguimiento/ajax_set_comentarios.php',
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Guardando comentario, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						show_custom_function_ok(respuesta.Mensaje, 'idiv_timeline_mensaje', 'margin: 0px;');
						setTimeout(function () {
							show_custom_function_ok('', 'idiv_timeline_mensaje');
						},4000);
						
						setTimeout(function () {
							ajax_get_comentarios();
						},1000);
						//fcn_cargar_grid_folios();
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
		var strMensaje = 'ajax_set_comentarios() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/*****************************************/
/* DOCUMENTOS */
/*****************************************/

/* ..:: Consultamos los archivos ::.. */
function ajax_get_archivos() {
	try {		
		var oData = {			
			sIdFolio: __sIdFolio
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidasSeguimiento/ajax_get_archivos.php',
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
						fcn_fill_file_input(respuesta.aPreview, respuesta.aPreviewConfig);
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
		var strMensaje = 'ajax_get_verificar_caja() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/*****************************************/
/* SYNC */
/*****************************************/
function ajax_set_update_sync() {
	try {	
		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidasSeguimiento/ajax_set_update_sync.php',
			timeout: 60000,
		
            beforeSend: function (dataMessage) {
				//clearTimeout(__oTimerSync);
				$('#ibtn_sync').html('<i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Sincronizando');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#ibtn_sync').html('<i class="fa fa-refresh" aria-hidden="true"></i> Sincronizar Documentos');
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
					fcn_cargar_grid_folios();
					
					__nCountSync = __nTimerSecondsSync;
					__oTimerSync = setTimeout(function () { ajax_set_update_sync(); }, (__nTimerSecondsSync * 1000));
				} else {
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
		var strMensaje = 'ajax_set_update_sync() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/*********************************************************************************************************************************
** DOWNLOAD FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

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

/*******************************************/
/* MENSAJES PERSONALIZADOS */
/*******************************************/

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

/* ..:: Funcion muestra mensajes de error ::.. */
function show_custom_function_ok(sMensaje, oDivDisplay, sStyle) {
	sMensaje = ((sMensaje == null || sMensaje == undefined)? '': sMensaje);
	oDivDisplay = ((oDivDisplay == null || oDivDisplay == undefined)? '': oDivDisplay);
	sStyle = ((sStyle == null || sStyle == undefined)? '': sStyle);

	if (oDivDisplay != '') {
		if (sMensaje != '') {
			var sHtml = '<div class="alert alert-success" style="' + sStyle + '">';
			sHtml +=	'	 <strong>Exito!</strong> ' + sMensaje;
			sHtml +=    '</div>';	
			
			$('#' + oDivDisplay).html(sHtml).show();
		} else {
			$('#' + oDivDisplay).hide();
		}
	} else {		
		show_modal_error('No se proporciono contenedor para el mensaje!');
	}
}