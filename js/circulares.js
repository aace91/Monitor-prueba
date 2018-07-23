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

/* ..:: App Vars ::.. */
var appName = 'Monitor';
var strSessionMessage = 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
var sGifLoader = '<img src="../images/cargando.gif" height="16" width="16"/>';

var oCircularesGrid = null;
var oListasCGrid = null;
var oListasCorreosGrid = null;
var oCirculareListasGrid = null;

var __nIdCircular;
var __sTask;

var __nIdListaCorreos;
var __sTaskListaCorreos;

var __nTotalPages = 0;
var __oTrDtDelete;

var oLanguage = {
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
}

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
		
		$(document).on('hidden.bs.modal', '.modal', function (event) { 
			setTimeout(function(){ 
				var oModalsOpen = $('.modal.in');
				if (oModalsOpen.length > 0 ) {
					$('body').addClass('modal-open');
				} else {
					$('body').removeClass('modal-open');
				}
			}, 700);	
		});

		$('#idiv_mensaje_html').summernote({
			toolbar: [
				['style', ['style']],
				['fontsize', ['fontsize']],
				['font', ['bold', 'italic', 'underline', 'clear']],
				['fontname', ['fontname']],
				['color', ['color']],
				['para', ['ul', 'ol', 'paragraph']],
				['table', ['table']],
				['height', ['height']],
				['insert', ['picture', 'link', 'video', 'hr']],
				['misc', ['fullscreen', 'undo', 'redo', 'help']]
			],
			tabsize: 2,
			height: 400,
			lang: 'es-ES',
			//focus: true,
			placeholder: 'Escriba su mensaje...',
		});
		$('#idiv_mensaje_html').summernote('fontSize', 14);

		$("#isel_lista_correos").select2({
			theme: "bootstrap",
			width: "off",
			placeholder: "Seleccione una Opción"
		});
		
		/* ..:: Configuramos el FileInput ::.. */
		$("#ifile_documentos").fileinput({
			uploadUrl: "ajax/circulares/circularesFunc.php?action=upload_files", // server upload action
			uploadAsync: false
		}).on('fileuploaded', function(event, params) {
			console.log('fileuploaded');
		}).on('filebatchuploadsuccess', function(event, data, previewId, index) {
			setTimeout(function(){ 
				var respuesta = data.jqXHR.responseJSON;
				fcn_fill_file_input(respuesta.aPreview, respuesta.aPreviewConfig);
			}, 500);
		}).on("filepredelete", function(jqXHR) {
			var abort = true;
			if (confirm('Desea eliminar el archivo seleccionado?')) {
				abort = false;
			}
			return abort; // you can also send any data/object that you can receive on `filecustomerror` event
		}).on('filedeleted', function(event, key, jqXHR, data) {
			console.log('filedeleted');
		}).on("filebatchselected", function(event, files) {
			$("#ifile_documentos").fileinput("upload");
		});


		fcn_cargar_grid_circulares();
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

function fcn_cargar_grid_circulares(bReloadPaging) {
	try {
		if (oCircularesGrid == null) {
			var oDivDisplayErrors = 'idiv_bwsr_mensaje';
			var div_table_name = 'dt_circulares';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oCircularesGrid = $('#' + div_table_name);
			
			oCircularesGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				columnDefs: [
					{ targets: 3, orderable: false }
				],
				ajax: {
					"url": "ajax/circulares/circularesFunc.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
						d.action = 'table_circulares';
					},
					"error": handleAjaxError
				},
				columns: [ 
					{ "data": "fecha", "className": "text-center" },
					{ "data": "asunto" },
					{ "data": "tipo" },
					{   "data": null,
						"className": "text-center",
						"mRender": function (data, type, row) {
								var sHtml = '';
								sHtml += '<a class="btn btn-primary btn-xs editor_' + div_table_name + '_editar def_app_btn_tbl_margin"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
								sHtml += '<a class="btn btn-primary btn-xs editor_' + div_table_name + '_copy def_app_btn_tbl_margin"><i class="fa fa-copy" aria-hidden="true"></i></a>';
								sHtml += '<a class="btn btn-danger btn-xs editor_' + div_table_name + '_eliminar def_app_btn_tbl_margin"><i class="fa fa-trash" aria-hidden="true"></i></a>';
								return sHtml;
							} 
					}
				],
				responsive: true,
				aLengthMenu: [
					[10, 25, 50, 100, 200, -1],
					[10, 25, 50, 100, 200, "All"]
				],
				iDisplayLength: 10,
				language: oLanguage,
				dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
					 "<'row'<'col-xs-8'B><'col-xs-4'<'" + div_refresh_name + "'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						text: '<i class="fa fa-plus" aria-hidden="true"></i> Nuevo Circular',
						className: 'btn btn-success',
		                action: function ( e, dt, node, config ) {
							__sTask = 'nuevo';
							__nIdCircular = '';
							
							$('#isel_mdl_tipo_circular').val('interno');
							$('#itxt_mdl_circular_asunto').val('');
							
							$('#modal_circular').modal({ show: true, backdrop: 'static', keyboard: false });
		            	}
					},
					{
						text: '<i class="fa fa-list-ul" aria-hidden="true"></i> Listas de Correos',
						className: 'btn btn-primary',
		                action: function ( e, dt, node, config ) {
							fcn_listas_correos_ops('show');
		            	}
		            }
				]
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_circulares(true);"');
			$("div." + div_refresh_name).html(sButton);
			
			oCircularesGrid.on('click', 'a.editor_' + div_table_name + '_editar', function (e) {
				try {		
					__sTask = 'editar';

					var oData = fcn_get_row_data($(this), oCircularesGrid);
					__nIdCircular = oData.id_circular;
					
					fcn_inicializar_circular();
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );

			oCircularesGrid.on('click', 'a.editor_' + div_table_name + '_copy', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oCircularesGrid);
					__nIdCircular = oData.id_circular;
					
					var strTitle = 'Copiar Circular';
					var strQuestion = 'Desea copiar el circular con asunto: ' + oData.asunto;
					var oFunctionOk = function () { 
						ajax_copy_del_circular('copy');
					};
					var oFunctionCancel = null;
					show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );

			oCircularesGrid.on('click', 'a.editor_' + div_table_name + '_eliminar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oCircularesGrid);
					__nIdCircular = oData.id_circular;
					
					var strTitle = 'Eliminar Circular';
					var strQuestion = 'Desea eliminar el circular con asunto: ' + oData.asunto;
					var oFunctionOk = function () { 
						ajax_copy_del_circular('delete');
					};
					var oFunctionCancel = null;
					show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oCircularesGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);

			var table = oCircularesGrid.DataTable();
			table.search('').ajax.reload(null, bReloadPaging);
			setTimeout(function(){ oCircularesGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_circulares() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

function fcn_cargar_grid_listas(bReloadPaging) {
	try {
		if (oListasCGrid == null) {
			var oDivDisplayErrors = 'idiv_listasc_mensaje';
			var div_table_name = 'dt_listas';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oListasCGrid = $('#' + div_table_name);
			
			oListasCGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				columnDefs: [
					{ targets: 3, orderable: false }
				],
				ajax: {
					"url": "ajax/circulares/circularesFunc.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
						d.action = 'table_listas_correos';
					},
					"error": handleAjaxError
				},
				columns: [ 
					{ "data": "fecha", "className": "text-center" },
					{ "data": "nombre" },
					{ "data": "descripcion" },
					{   "data": null,
						"className": "text-center",
						"mRender": function (data, type, row) {
								var sHtml = '';
								sHtml += '<a class="btn btn-primary btn-xs editor_' + div_table_name + '_editar def_app_btn_tbl_margin"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
								sHtml += '<a class="btn btn-danger btn-xs editor_' + div_table_name + '_eliminar def_app_btn_tbl_margin"><i class="fa fa-trash" aria-hidden="true"></i></a>';
								return sHtml;
							} 
					}
				],
				responsive: true,
				aLengthMenu: [
					[10, 25, 50, 100, 200, -1],
					[10, 25, 50, 100, 200, "All"]
				],
				iDisplayLength: 10,
				language: oLanguage,
				dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
					 "<'row'<'col-xs-8'B><'col-xs-4'<'" + div_refresh_name + "'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						text: '<i class="fa fa-plus" aria-hidden="true"></i> Nueva Lista Correos',
						className: 'btn btn-success',
		                action: function ( e, dt, node, config ) {
							__nIdListaCorreos = '';
							fcn_listas_correos_ops('show_lista_correos');
		            	}
					}
				]
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_listas(true);"');
			$("div." + div_refresh_name).html(sButton);
			
			oListasCGrid.on('click', 'a.editor_' + div_table_name + '_editar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oListasCGrid);
					
					__nIdListaCorreos = oData.id_lista;
					fcn_listas_correos_ops('show_lista_correos');
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );

			oListasCGrid.on('click', 'a.editor_' + div_table_name + '_eliminar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oListasCGrid);
					__nIdListaCorreos = oData.id_lista;
					
					var strTitle = 'Eliminar Lista de Correos';
					var strQuestion = 'Desea eliminar la lista de correos: ' + oData.nombre;
					var oFunctionOk = function () { 
						ajax_del_lista_correos();
					};
					var oFunctionCancel = null;
					show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oListasCGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);

			var table = oListasCGrid.DataTable();
			table.search('').ajax.reload(null, bReloadPaging);
			setTimeout(function(){ oListasCGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_listas() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

function fcn_cargar_grid_lista_correos(aData) {
	try {
		if (oListasCorreosGrid == null) {
			var oDivDisplayErrors = 'idiv_lista_correo_mensaje';
			var div_table_name = 'dt_lista_correos';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oListasCorreosGrid = $('#' + div_table_name);
			
			oListasCorreosGrid.DataTable({
				order: [[0, 'desc']],
				processing: false,
				serverSide: false,
				columnDefs: [
					{ targets: 2, orderable: false }
				],
				data: aData,
				columns: [ 
					{ "data": "correo" },
					{ "data": "nombre" },
					{   "data": null,
						"className": "text-center",
						"mRender": function (data, type, row) {
								var sHtml = '';
								sHtml += '<a class="btn btn-danger btn-xs editor_' + div_table_name + '_eliminar def_app_btn_tbl_margin"><i class="fa fa-trash" aria-hidden="true"></i></a>';
								return sHtml;
							} 
					}
				],
				responsive: true,
				aLengthMenu: [
					[10, 25, 50, 100, 200, -1],
					[10, 25, 50, 100, 200, "All"]
				],
				iDisplayLength: 10,
				language: oLanguage,
				dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: []
			});
			
			oListasCorreosGrid.on('click', 'a.editor_' + div_table_name + '_eliminar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oListasCorreosGrid);
					
					__oTrDtDelete = $(this).parents('tr');

					var strTitle = 'Eliminar Correo electrónico';
					var strQuestion = 'Desea eliminar el correo: ' + oData.correo;
					var oFunctionOk = function () { 
						oListasCorreosGrid.DataTable().row(__oTrDtDelete).remove().draw();
					};
					var oFunctionCancel = null;
					show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oListasCorreosGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			oListasCorreosGrid.DataTable().clear().draw();
			if (aData.length > 0) {
				oListasCorreosGrid.dataTable().fnAddData(aData);	
			}
			setTimeout(function(){ oListasCorreosGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_lista_correos() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

function fcn_cargar_grid_circular_listas(aData) {
	try {
		if (oCirculareListasGrid == null) {
			var oDivDisplayErrors = 'idiv_mensaje_errors';
			var div_table_name = 'dt_circular_listas';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oCirculareListasGrid = $('#' + div_table_name);
			
			oCirculareListasGrid.DataTable({
				order: [[0, 'desc']],
				processing: false,
				serverSide: false,
				columnDefs: [
					{ targets: 2, orderable: false }
				],
				data: aData,
				columns: [ 
					{ "data": "nombre" },
					{ "data": "descripcion" },
					{   "data": null,
						"className": "text-center",
						"mRender": function (data, type, row) {
								var sHtml = '';
								sHtml += '<a class="btn btn-primary btn-xs editor_' + div_table_name + '_ver def_app_btn_tbl_margin"><i class="fa fa-eye" aria-hidden="true"></i></a>';
								sHtml += '<a class="btn btn-danger btn-xs editor_' + div_table_name + '_eliminar def_app_btn_tbl_margin"><i class="fa fa-trash" aria-hidden="true"></i></a>';
								return sHtml;
							} 
					}
				],
				responsive: true,
				aLengthMenu: [
					[10, 25, 50, 100, 200, -1],
					[10, 25, 50, 100, 200, "All"]
				],
				iDisplayLength: 10,
				language: oLanguage,
				dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: []
			});
			
			oCirculareListasGrid.on('click', 'a.editor_' + div_table_name + '_ver', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oCirculareListasGrid);

					__nIdListaCorreos = oData.id_lista;
					fcn_listas_correos_ops('show_lista_correos');
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_ver() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );

			oCirculareListasGrid.on('click', 'a.editor_' + div_table_name + '_eliminar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oCirculareListasGrid);
					
					__oTrDtDelete = $(this).parents('tr');

					var strTitle = 'Eliminar Lista de Correos';
					var strQuestion = 'Desea eliminar la lista de correos: ' + oData.nombre;
					var oFunctionOk = function () { 
						oCirculareListasGrid.DataTable().row(__oTrDtDelete).remove().draw();
					};
					var oFunctionCancel = null;
					show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_eliminar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );

			oCirculareListasGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			oCirculareListasGrid.DataTable().clear().draw();
			if (aData.length > 0) {
				oCirculareListasGrid.dataTable().fnAddData(aData);	
			}
			setTimeout(function(){ oCirculareListasGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_lista_correos() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

/**************************************/

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
		show_modal_error('Se ha producido un error en el servidor. Por favor espera.');
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
		show_custom_function_error(sMensaje, oDivDisplay, 'margin: 0px;');
	}
}

/*********************************************************************************************************************************
** CONTROLS FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

/* ..:: Creamos el objeto FileInput y lo llenamos de datos ::.. */
function fcn_fill_file_input(aPreview, aPreviewConfig) {
	try {
		$("#ifile_documentos").fileinput('destroy');
		$("#ifile_documentos").fileinput({
			language: 'es',
			uploadUrl: "ajax/circulares/circularesFunc.php?action=upload_files", // server upload action
			uploadAsync: false,
			uploadExtraData: function() {
				return {
					nIdCircular: __nIdCircular
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
				showUpload: false,
				showDelete: true
			},
			previewFileIconSettings: { // configure your icon file extensions
				'doc': '<i class="fa fa-file-word-o text-primary"></i>',
				'xls': '<i class="fa fa-file-excel-o text-success"></i>',
				'ppt': '<i class="fa fa-file-powerpoint-o text-danger"></i>',
				'pdf': '<i class="fa fa-file-pdf-o text-danger"></i>',
				'zip': '<i class="fa fa-file-archive-o text-muted"></i>',
				'htm': '<i class="fa fa-file-code-o text-info"></i>',
				'txt': '<i class="fa fa-file-text-o text-info"></i>',
				'mov': '<i class="fa fa-file-movie-o text-warning"></i>',
				'mp3': '<i class="fa fa-file-audio-o text-warning"></i>',
				// note for these file types below no extension determination logic 
				// has been configured (the keys itself will be used as extensions)
				'jpg': '<i class="fa fa-file-photo-o text-danger"></i>', 
				'gif': '<i class="fa fa-file-photo-o text-warning"></i>', 
				'png': '<i class="fa fa-file-photo-o text-primary"></i>'    
			},
			previewFileExtSettings: { // configure the logic for determining icon file extensions
				'doc': function(ext) {
					return ext.match(/(doc|docx)$/i);
				},
				'xls': function(ext) {
					return ext.match(/(xls|xlsx)$/i);
				},
				'ppt': function(ext) {
					return ext.match(/(ppt|pptx)$/i);
				},
				'zip': function(ext) {
					return ext.match(/(zip|rar|tar|gzip|gz|7z)$/i);
				},
				'htm': function(ext) {
					return ext.match(/(htm|html)$/i);
				},
				'txt': function(ext) {
					return ext.match(/(txt|ini|csv|java|php|js|css)$/i);
				},
				'mov': function(ext) {
					return ext.match(/(avi|mpg|mkv|mov|mp4|3gp|webm|wmv)$/i);
				},
				'mp3': function(ext) {
					return ext.match(/(mp3|wav)$/i);
				},
			}	
		});
		
		if (aPreviewConfig.length > 0) {
			$.each(aPreviewConfig, function (index, value) {
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
			});
		}

		$('div.close.fileinput-remove').hide();
		$('div.btn.btn-primary.btn-file').prop('disabled', false);
		$('div.btn.btn-primary.btn-file').removeAttr("disabled");
	} catch (err) {
		var strMensaje = 'fcn_fill_file_input() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_inicializar_circular() {
	try {
		$('#idiv_mensaje').empty().hide();

		$('#idiv_panel_principal').hide();
		$('#idiv_panel_secundario').show();
		$('#idiv_panel_listas_correos').hide();
		
		$('#isel_tipo_circular').val('interno');
		$('#itxt_asunto_circular').val('');
		$('#idiv_mensaje_html').summernote('code', '');
		$('#itxt_correos_adicionales').val('');
		
		var aPreview = new Array(); 
		var aPreviewConfig = new Array(); 
		fcn_fill_file_input(aPreview, aPreviewConfig);
		fcn_cargar_grid_circular_listas([]);

		ajax_get_circular();
    } catch (err) {		
		var strMensaje = 'fcn_inicializar_circular() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_regresar_principal() {
	try {
		$('#idiv_panel_principal').show();
		$('#idiv_panel_secundario').hide();
		$('#idiv_panel_listas_correos').hide();

		fcn_cargar_grid_circulares();
    } catch (err) {		
		var strMensaje = 'fcn_regresar_principal() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_get_checkbox_value(sCtrlCkb) {
	try {
		if($('#' + sCtrlCkb).is(':checked')) {
			return '1';
		} else {
			return '0';
		}
    } catch (err) {		
		var strMensaje = 'fcn_get_checkbox_value() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_guardar_circular(sCtrlCkb) {
	try {
		var strTitle = 'Guardar y enviar';
		var strQuestion = 'Al aceptar se guardar&aacute; el circular y posteriormente se enviara por correo electr&oacute;nico a los diferentes destinatarios, usted est&aacute; de acuerdo?';
		var oFunctionOk = function () { 
			ajax_set_circular('editar-enviar');
		};
		var oFunctionCancel = null;
		show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
    } catch (err) {		
		var strMensaje = 'fcn_get_checkbox_value() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_envio_pendiente(pTotalPaginas) {
	try {
		var strHtml = '';
		strHtml += '<div class="alert alert-warning">';
		strHtml += '   <strong>Alerta!</strong> Tiene pendiente el env&iacute;o de <span class="badge" style="background-color: #337ab7">' + pTotalPaginas + '</span> paginas, desea continuar con el reenv&iacute;o?';
		strHtml += '   <button type="button" class="btn btn-success btn-sm" onclick="fcn_envio_pendiente_reenviar('+ pTotalPaginas +');">';
		strHtml += '      <i class="fa fa-paper-plane"></i> Si';
		strHtml += '   </button>';
		strHtml += '   <button type="button" class="btn btn-danger btn-sm" style="margin-left: 15px;" onclick="fcn_envio_pendiente_cancelar();"><i class="fa fa-times"></i> No</button>';
		strHtml += '</div>';
		
		$('#idiv_mensaje').html(strHtml).show();
    } catch (err) {		
		var strMensaje = 'fcn_envio_pendiente() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_envio_pendiente_reenviar(pTotalPaginas) {
	$('#idiv_mensaje').empty().hide();

	__nTotalPages = pTotalPaginas;

	ajax_send_email(pTotalPaginas);
}

function fcn_envio_pendiente_cancelar() {
	$('#idiv_mensaje').empty().hide();
}

function fcn_circular_agregar_lista() {
	try {
		var oSelLista = $('#isel_lista_correos');
		var nIdLista = ((oSelLista.val() == null)? '' : oSelLista.val());
		if (!nIdLista.trim()) { 
			show_modal_error('Debe seleccionar una lista de correos!');
			return;
		}

		if (fcn_buscar_en_grid(nIdLista, oCirculareListasGrid, 'id_lista') == true) {
			show_modal_error('Esta lista de correos ya se encuentra seleccionada!!!');
			return false;
		}

		oCirculareListasGrid.DataTable().row.add({
			id_lista: nIdLista,
			nombre: oSelLista.select2('data')[0].text,
			descripcion: oSelLista.select2('data')[0].descripcion
		}).draw(false);

		oSelLista.val('').trigger('change');
    } catch (err) {		
		var strMensaje = 'fcn_circular_agregar_lista() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_circular_ver_lista() {
	try {
		var oSelLista = $('#isel_lista_correos');
		var nIdLista = ((oSelLista.val() == null)? '' : oSelLista.val());
		if (nIdLista.trim()) { 
			__nIdListaCorreos = nIdLista;
			fcn_listas_correos_ops('show_lista_correos');	
		}
    } catch (err) {		
		var strMensaje = 'fcn_circular_ver_lista() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/***************************************************/
/* ..:: LISTAS DE CORREOS ::.. */
/***************************************************/

function fcn_listas_correos_ops(pOpt) {
	try {
		switch (pOpt) {
			case 'show':
				$('#idiv_panel_principal').hide();
				$('#idiv_panel_secundario').hide();
				$('#idiv_panel_listas_correos').show();

				fcn_cargar_grid_listas();
				break;
		
			case 'show_lista_correos':
				show_custom_function_ok('', 'idiv_lista_correo_mensaje');
				$('#modal_lista_correos .modal-title').html('<i class="fa fa-envelope" aria-hidden="true"></i> Nueva Lista');
				
				$('#itxt_mdl_lista_correo_nombre_lista, #itxt_mdl_lista_correo_descripcion').val('');
				$('#itxt_mdl_lista_correo_correo, #itxt_mdl_lista_correo_nombre').val('');
				
				$('#itxt_mdl_lista_correo_nombre_lista, #itxt_mdl_lista_correo_descripcion').prop('disabled', false);
				$('#ibtn_mdl_lista_correo_guardar').prop('disabled', false);
				$('#modal_lista_correos').modal({ show: true, backdrop: 'static', keyboard: false });

				fcn_cargar_grid_lista_correos(new Array());				
				if (__nIdListaCorreos != '') {
					setTimeout(function () { ajax_get_lista_correos(); }, 750);

					$('#modal_lista_correos .modal-title').html('<i class="fa fa-envelope" aria-hidden="true"></i> Editar Lista de Correos');
					if ($('#idiv_panel_secundario').is(":visible")) {
						$('#modal_lista_correos .modal-title').html('<i class="fa fa-eye" aria-hidden="true"></i> Ver Lista de Correos');
						$('#itxt_mdl_lista_correo_nombre_lista, #itxt_mdl_lista_correo_descripcion').prop('disabled', true);
					}
				}			
				break;

			case 'add_correo':
				var sEmail = fcn_validate_email('itxt_mdl_lista_correo_correo');
				var sNombre = $('#itxt_mdl_lista_correo_nombre').val().trim();
				
				if (sEmail == '') {
					show_modal_error('Debe ingresar un correo electrónico v&aacute;lido!');
					return false;
				}

				if (sNombre == '') {
					show_modal_error('Debe agregar un nombre!!!');
					return false;
				}

				if (fcn_buscar_en_grid(sEmail, oListasCorreosGrid, 'correo') == true) {
					show_modal_error('El correo electrónico ya se encuentra en la lista!!!');
					return false;
				}

				oListasCorreosGrid.DataTable().row.add({
					correo: sEmail,
					nombre: sNombre
				}).draw(false);

				$('#itxt_mdl_lista_correo_correo').val('');
				$('#itxt_mdl_lista_correo_nombre').val('');
				break;
		}
    } catch (err) {		
		var strMensaje = 'fcn_listas_correos_ops() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/***************************************************/
/* ..:: GENERALES ::.. */
/***************************************************/

/* ..:: Validamos el correo electrónico ::.. */
function fcn_validate_email(id) {
	var email_regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i;
	if(!email_regex.test($("#"+id).val())) {
		return '';
	} else{
		return $("#"+id).val();
	}
}

/* ..:: buscamos en el grid ::.. */
function fcn_buscar_en_grid(sValor, oGrid, sColumnGrid) {
	var oTable = oGrid.DataTable();
	var bReturn = false;
	
	//oTable.data().each(function (value, index) {
	$.each(oTable.data(), function (index, value) {
		if (value[sColumnGrid] == sValor) {
			bReturn = true;
		}
	});
	
	return bReturn;
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/* ..:: obtenemos datos del circular ::.. */
function ajax_get_circular() {
	try {	
		var oData = {	
			action: 'consultar_circular',
			nIdCircular: __nIdCircular
		};

		$.ajax({
			type: "POST",
			url: 'ajax/circulares/circularesFunc.php',
			data: oData,
			timeout: 30000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$("#itxt_circular_sender").val(respuesta.sSender);
						$("#itxt_circular_fromname").val(respuesta.sFromName);
						$("#isel_tipo_circular").val(respuesta.sTipo);
						$("#itxt_asunto_circular").val(respuesta.sAsunto);
						$('#idiv_mensaje_html').summernote('code', respuesta.sMensaje);
						$("#itxt_correos_adicionales").val(respuesta.sCorreosAdicionales);
						$("#ickb_enviar_clientes_impo").prop('checked', ((respuesta.nEnviarClientesImpo == '1')? true : false));
						$("#ickb_enviar_clientes_expo").prop('checked', ((respuesta.nEnviarClientesExpo == '1')? true : false));
						$("#ickb_enviar_clientes_nb").prop('checked', ((respuesta.nEnviarClientesNB == '1')? true : false));
						$("#ickb_enviar_ejecutivos_impo").prop('checked', ((respuesta.nEnviarEjecutivosImpo == '1')? true : false));
						$("#ickb_enviar_ejecutivos_expo").prop('checked', ((respuesta.nEnviarEjecutivosExpo == '1')? true : false));
						$("#ickb_enviar_ejecutivos_nb").prop('checked', ((respuesta.nEnviarEjecutivosNB == '1')? true : false));

						$("#isel_lista_correos").empty().select2({
							data: respuesta.aListasSelect,
							theme: "bootstrap",
							width: "off",
							placeholder: "Seleccione una Opción"
						}).val('').trigger('change');

						fcn_fill_file_input(respuesta.aPreview, respuesta.aPreviewConfig);
						fcn_cargar_grid_circular_listas(respuesta.aListas);

						if (respuesta.nTotalPaginas > 0) {
							fcn_envio_pendiente(respuesta.nTotalPaginas);
						}
					} else {
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				}else{
					show_load_config(false);
					
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				show_load_config(false);
				
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
		});
    } catch (err) {
		show_load_config(false);
		
		var strMensaje = 'ajax_get_circular() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Guardamos el circular ::.. */
function ajax_set_circular(pTask) {
	try {
		fcn_envio_pendiente_cancelar(); //por si mostramos una alerta de tipo envio pendiente, lo ocultamos

		__sTask = ((pTask == null || pTask == undefined)? __sTask : pTask);

		__nTotalPages = 0;

		var sSender = '';
		var sFromName = '';
		var sAsunto = '';
		var sTipo = '';
		var sMensaje = '';
		var sCorreosAdicionales = '';
		var nEnviarClientesImpo = '0';
		var nEnviarClientesExpo = '0';
		var nEnviarClientesNB = '0';
		var nEnviarEjecutivosImpo = '0';
		var nEnviarEjecutivosExpo = '0';
		var nEnviarEjecutivosNB = '0';
		var aListas = new Array();
		
		if ($('#modal_circular').is(':visible')) { 
			sAsunto = $('#itxt_mdl_circular_asunto').val().trim();
			sTipo = $('#isel_mdl_tipo_circular').val();
		} else {
			sSender = fcn_validate_email('itxt_circular_sender');
			sFromName = $('#itxt_circular_fromname').val().trim();
			sAsunto = $('#itxt_asunto_circular').val().trim();
			sTipo = $('#isel_tipo_circular').val();
			sMensaje = $('#idiv_mensaje_html').summernote('code');
			sCorreosAdicionales = $('#itxt_correos_adicionales').val().trim();
			nEnviarClientesImpo = fcn_get_checkbox_value('ickb_enviar_clientes_impo');
			nEnviarClientesExpo = fcn_get_checkbox_value('ickb_enviar_clientes_expo');
			nEnviarClientesNB = fcn_get_checkbox_value('ickb_enviar_clientes_nb');
			nEnviarEjecutivosImpo = fcn_get_checkbox_value('ickb_enviar_ejecutivos_impo');
			nEnviarEjecutivosExpo = fcn_get_checkbox_value('ickb_enviar_ejecutivos_expo');
			nEnviarEjecutivosNB = fcn_get_checkbox_value('ickb_enviar_ejecutivos_nb');
			
			if (sSender == '') { show_modal_error('Debe ingresar un correo electrónico v&aacute;lido!'); return false; }
			if (sFromName == '') { show_modal_error('Debe agregar un nombre de remitente!!!'); return false; }
			if (sMensaje == '') { show_modal_error('Debe agregar un mensaje!!!'); return false; }

			oCirculareListasGrid.DataTable().data().each(function (value, index) {
				aListas.push({ id_lista: value.id_lista });
			});
		}
		
		if (sAsunto == '') {
			show_modal_error('Debe agregar un asunto!!!');
			return false;
		}
		
		var oData = {	
			action: 'guadar_circular',
			nIdCircular: __nIdCircular,
			sTask: __sTask,
			sSender: sSender,
			sFromName: sFromName,
			sAsunto: sAsunto,
			sTipo: sTipo,
			sMensaje: sMensaje,
			sCorreosAdicionales: sCorreosAdicionales,
			nEnviarClientesImpo: nEnviarClientesImpo,
			nEnviarClientesExpo: nEnviarClientesExpo,
			nEnviarClientesNB: nEnviarClientesNB,
			nEnviarEjecutivosImpo: nEnviarEjecutivosImpo,
			nEnviarEjecutivosExpo: nEnviarEjecutivosExpo,
			nEnviarEjecutivosNB: nEnviarEjecutivosNB,
			aListas: JSON.stringify(aListas)
		};

		$.ajax({
			type: "POST",
			url: 'ajax/circulares/circularesFunc.php',
			data: oData,
			timeout: 30000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Guardando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						switch(__sTask) {
							case 'nuevo':
								__nIdCircular = respuesta.nIdCircular;

								fcn_inicializar_circular();
								
								$('#isel_tipo_circular').val(respuesta.sTipo);
								$('#itxt_asunto_circular').val(respuesta.sAsunto);
								
								$('#modal_circular').modal('hide');
								__sTask = 'editar';
								break;
								
							case 'editar':
								 fcn_regresar_principal();
								break;
								
							case 'editar-enviar':
								if (respuesta.nTotalPaginas > 0) {
									__nTotalPages = respuesta.nTotalPaginas;
									setTimeout(function () { ajax_send_email(respuesta.nTotalPaginas); }, 500);									
								} else {
									fcn_regresar_principal();
								}
								break;
						}
					} else {
						show_load_config(false);
						
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				}else{
					show_load_config(false);
					
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				show_load_config(false);
				
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
		});
    } catch (err) {
		show_load_config(false);
		
		var strMensaje = 'ajax_set_circular() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Eliminar o Copiar el circular ::.. */
function ajax_copy_del_circular(pTarea) {
	try {
		var oData = {	
			action: ((pTarea == 'copy')? 'copiar_circular': 'eliminar_circular'),
			nIdCircular: __nIdCircular
		};

		$.ajax({
			type: "POST",
			url: 'ajax/circulares/circularesFunc.php',
			data: oData,
			timeout: 30000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Guardando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						if (pTarea == 'copy') {
							__nIdCircular = respuesta.nIdCircular;
							
							fcn_inicializar_circular();
						} else {
							show_modal_ok(respuesta.Mensaje);
							fcn_regresar_principal();
						}
					} else {
						show_load_config(false);
						
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				}else{
					show_load_config(false);
					
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				show_load_config(false);
				
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
		});
    } catch (err) {
		show_load_config(false);
		
		var strMensaje = 'ajax_copy_del_circular() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Eliminar o Copiar el circular ::.. */
function ajax_send_email(pTotalPaginas) {
	try {
		var oData = {	
			action: 'enviar_email',
			nIdCircular: __nIdCircular
		};

		$.ajax({
			type: "POST",
			url: 'ajax/circulares/circularesFunc.php',
			data: oData,
			timeout: 40000,
			
			beforeSend: function (dataMessage) {
				var percent = parseInt((__nTotalPages - pTotalPaginas) * 100 / __nTotalPages);
				var sHtml = 'Enviando Correo, espere un momento por favor...<br>';
				sHtml += '   <div class="progress progress-striped active">';
				sHtml += '		<div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
				sHtml += '			<span>'+percent+'% Completado</span>';
				sHtml += '		</div>';
				sHtml += '	 </div>';

				show_load_config(true, sHtml);
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					if (respuesta.Codigo == '1'){
						if (respuesta.nTotalPaginas > 0) {
							ajax_send_email(respuesta.nTotalPaginas);
						} else {
							show_load_config(false);
							setTimeout(function () { 
								show_modal_ok('Mensaje enviado a todos los destinatatios!!!');
								fcn_regresar_principal();
							}, 500);		
						}
					} else {
						show_load_config(false);
						
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);

						setTimeout(function () { fcn_inicializar_circular(); }, 500);
					}
				}else{
					show_load_config(false);
					
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				show_load_config(false);
				
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);

				setTimeout(function () { fcn_inicializar_circular(); }, 500);
			}
		});
    } catch (err) {
		show_load_config(false);
		
		var strMensaje = 'ajax_send_email() :: ' + err.message;
		show_modal_error(strMensaje);

		setTimeout(function () { fcn_inicializar_circular(); }, 500);
    }    
}

/***************************************************/
/* ..:: LISTAS DE CORREOS ::.. */
/***************************************************/

/* ..:: consultamos los datos de la lista de correos ::.. */
function ajax_get_lista_correos() {
	try {	
		var oData = {	
			action: 'consultar_lista_correos',
			nIdListaCorreos: __nIdListaCorreos
		};

		$.ajax({
			type: "POST",
			url: 'ajax/circulares/circularesFunc.php',
			data: oData,
			timeout: 30000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#itxt_mdl_lista_correo_nombre_lista').val(respuesta.sNombre);
						$('#itxt_mdl_lista_correo_descripcion').val(respuesta.sDescripcion);

						fcn_cargar_grid_lista_correos(respuesta.aCorreos);
					} else {
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				}else{
					show_load_config(false);
					
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				show_load_config(false);
				
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
		});
    } catch (err) {
		show_load_config(false);
		
		var strMensaje = 'ajax_get_lista_correos() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Guardamos la lista de correos ::.. */
function ajax_set_lista_correos() {
	try {
		show_custom_function_error('', 'idiv_lista_correo_mensaje');
		var sNombre = $('#itxt_mdl_lista_correo_nombre_lista').val().trim();
		var sDescripcion = $('#itxt_mdl_lista_correo_descripcion').val().trim();
		var aCorreos = new Array();

		if (sNombre == '') {
			show_modal_error('Debe ingresar un nombre a la lista de correos!');
			return false;
		}

		var oTable = oListasCorreosGrid.DataTable();
		if (oTable.data().count() == 0) {
			show_modal_error('Debe ingresar por lo menos un correo electrónico!');
			return false;
		}

		oTable.data().each(function (value, index) {
			aCorreos.push(value);
		});
		
		var oData = {	
			action: 'guardar_lista_correos',
			sNombre: sNombre,
			sDescripcion: sDescripcion,
			nIdListaCorreos: __nIdListaCorreos,
			aCorreos: JSON.stringify(aCorreos)
		};

		$.ajax({
			type: "POST",
			url: 'ajax/circulares/circularesFunc.php',
			data: oData,
			timeout: 30000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Guardando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				show_load_config(false);
				if (response != '500'){
					var respuesta = JSON.parse(response);					
					if (respuesta.Codigo == '1'){
						$('#ibtn_mdl_lista_correo_guardar').prop('disabled', true);
						show_custom_function_ok(respuesta.Mensaje, 'idiv_lista_correo_mensaje', 'margin: 0px;');
						fcn_cargar_grid_listas();
					} else {
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				} else {					
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				show_load_config(false);
				
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
		});
    } catch (err) {
		show_load_config(false);
		
		var strMensaje = 'ajax_set_lista_correos() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Eliminar la lista de correos ::.. */
function ajax_del_lista_correos() {
	try {
		var oData = {	
			action: 'eliminar_lista_correos',
			nIdListaCorreos: __nIdListaCorreos
		};

		$.ajax({
			type: "POST",
			url: 'ajax/circulares/circularesFunc.php',
			data: oData,
			timeout: 30000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Actualizando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				show_load_config(false);
				if (response != '500'){
					var respuesta = JSON.parse(response);					
					if (respuesta.Codigo == '1'){
						show_modal_ok(respuesta.Mensaje);
						fcn_cargar_grid_listas();
					} else {
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				} else {					
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				show_load_config(false);
				
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
		});
    } catch (err) {
		show_load_config(false);
		
		var strMensaje = 'ajax_del_lista_correos() :: ' + err.message;
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
function show_load_config_progressbar(bShow, sMensaje, nProgress) {
	if (bShow) {
		if (sMensaje == null || sMensaje == undefined) {
			sMensaje = 'Consultando, espere un momento por favor...';
		}
		
		var sHtml = '';
		sHtml += '<div class="progress">';
		sHtml += '    <div class="progress-bar progress-bar-success progress-bar-striped active"';
		sHtml += '         role="progressbar"';
		sHtml += '         aria-valuenow="' + nProgress + '"';
		sHtml += '         aria-valuemin="0"';
		sHtml += '         aria-valuemax="100"';
		sHtml += '         style="width:' + nProgress + '%">';
		sHtml += sMensaje;
		sHtml += '    </div>';
		sHtml += '</div>';
		
		if ($('#modalloadconfig').hasClass('in') == false) {
			$('#modalloadconfig').modal({ show: true, backdrop: 'static', keyboard: false });
		}		
		$("#modalloadconfig_mensaje").html(sHtml);
	} else {
		setTimeout(function () {
			$('#modalloadconfig').modal('hide');
		},500);
	}
}

/* ..:: Funcion que muestra el mensaje del loading  ::.. */
function show_load_config(bShow, sMensaje) {
	if (bShow) {
		if (sMensaje == null || sMensaje == undefined) {
			sMensaje = 'Consultando, espere un momento por favor...';
		}
		
		$('#modalloadconfig').modal({ show: true, backdrop: 'static', keyboard: false });
		$("#modalloadconfig_mensaje").html(sGifLoader + ' ' + sMensaje);
	} else {
		setTimeout(function () {
			$('#modalloadconfig').modal('hide');
		},500);
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

/* ..:: Funcion muestra mensajes de ok ::.. */
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