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

var __sEmailUsuario;

var oTableReferenciasGrid = null;
var oTableAddReferenciaGrid = null;
var oTableCatProblemasGrid = null;
var oTableReportesGrid = null;

var __sIdProblema;
var __sIdReferencia;
var __sIdUsuario;
var __sListo; //Para saber si esta listo (Cerrado)

var __sOperacion; //Si esta grabando o editando

/* Reportes */
var __aReporteData; //Almacena los datos de los selects
var __bReporteGenerar = false;
var __sTipo = '';
var __sTipoOpt2 = '';
var __sFechaInicio = '';
var __sFechaFin = '';

var __aRegimen = [
	{ id: 'A1', text: 'A1' }, 
	{ id: 'A3', text: 'A3' }, 
	{ id: 'A4', text: 'A4' }, 
	{ id: 'AD', text: 'AD' }, 
	{ id: 'AF', text: 'AF' }, 
	{ id: 'AJ', text: 'AJ' }, 
	{ id: 'BA', text: 'BA' }, 
	{ id: 'BF', text: 'BF' }, 
	{ id: 'BH', text: 'BH' }, 
	{ id: 'BM', text: 'BM' }, 
	{ id: 'BO', text: 'BO' }, 
	{ id: 'C1', text: 'C1' }, 
	{ id: 'CT', text: 'CT' }, 
	{ id: 'D1', text: 'D1' }, 
	{ id: 'F4', text: 'F4' }, 
	{ id: 'F5', text: 'F5' }, 
	{ id: 'H1', text: 'H1' }, 
	{ id: 'H3', text: 'H3' }, 
	{ id: 'H8', text: 'H8' }, 
	{ id: 'I1', text: 'I1' }, 
	{ id: 'IN', text: 'IN' }, 
	{ id: 'J2', text: 'J2' }, 
	{ id: 'K1', text: 'K1' }, 
	{ id: 'P1', text: 'P1' }, 
	{ id: 'RT', text: 'RT' }, 
	{ id: 'V1', text: 'V1' }, 
	{ id: 'V5', text: 'V5' }
];

/* Observaciones */
//Para eliminar la observacion por si roberto se equivoca
var __sObservIdProblema; 
var __sObservFecha;
var __aEmailsAutorizados = ['jcdelacruz@delbravo.com', 'roberto.gonzalez@delbravo.com'];

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
		
		__sEmailUsuario = $('#itxt_email_usuario').val();

		$('#idtp_reportes_fecha').datepicker({
			format: 'mm/dd/yyyy',
			language: "es",
			autoclose: true
		});

		$('#isel_add_problema_ejecutivo').select2({
			theme: "bootstrap",
			width: "off",
			placeholder: "Selecciona un Ejecutivo"
		});

		$('#isel_add_problema_lista').select2({
			theme: "bootstrap",
			width: "off",
			placeholder: "Selecciona un Problema"
		});

		fcn_cargar_grid_referencias();
		
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

function fcn_cargar_grid_referencias(pOpt) {
	try {
		if (oTableReferenciasGrid == null) {
			var div_refresh_name = 'div_dtreferencias_refresh';
			var div_table_name = 'dtreferencias';
			
			oTableReferenciasGrid = $('#' + div_table_name);
			
			oTableReferenciasGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/ctrlGlosa/postReferencias.php",
					"type": "POST",
					"timeout": 20000,
					"error": handleAjaxError,
					"data": function ( d ) {
			            d.sFiltro = $('#isel_filtro_referencias').val();
			            d.search.value = d.search.value.toUpperCase();
					},
				},
				columns: [ 
					{   data: "NUM_REFE", 
					    className: "text-center",
						render: function (data) {
							if (data == '' || data == null) { 
								return '<a class="editor_' + div_table_name + '_reasignar"><i class="fa fa fa-exchange" aria-hidden="true"></i> Reasignar</a>';
							} else {
								return data;
							}					        
					    }
					},
					{ 
						data: "FECHA_ALTA", 
						className: "text-center",
						render: function (data) {
							if (data != '' && data != null) { 
								var date = new Date(data);
						        var month = date.getMonth() + 1;
						        return date.getDate()  + "/" + (month.length > 1 ? month : "0" + month) + "/" + date.getFullYear() + " " + date.getHours() + ":" + date.getMinutes();
							} else {
								return data;
							}					        
					    }
					},
					{ data: "EJECUTIVO"},
					{ data: "CLIENTE"},
					{
						data: 'OBSERVACIONES',
						className: "text-center",
						render: function ( data, type, row ) {
							if (row.LISTO != '' && row.LISTO != null) {
								return '<a class="editor_' + div_table_name + '_comentarios">(' + data + ') <i class="fa fa-eye" aria-hidden="true"></i> Ver</a>';
							} else {
								if (data > 0) {
									return '<a class="editor_' + div_table_name + '_comentarios">(' + data + ') <i class="fa fa-plus" aria-hidden="true"></i> Agregar</a>';
								} else {
									return '<a class="editor_' + div_table_name + '_comentarios"><i class="fa fa-plus" aria-hidden="true"></i> Agregar</a>';
								}
							}							
						}
					},
					{
						data: 'LISTO',
						className: "text-center",
						render: function ( data, type, row ) {
							if (data != '' && data != null) {
								return '<span style="color:#449d44;"><i class="fa fa-check-circle" aria-hidden="true"> Cerrado</i></span>';
							} else {
								return '<a class="editor_' + div_table_name + '_listo" style="color:#d9534f;"><i class="fa fa-times-circle" aria-hidden="true"></i> Cerrar</a>';
							}
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
				buttons: [
					 {
		                text: '<i class="fa fa-plus" aria-hidden="true"></i> Agregar Referencia',
		                action: function ( e, dt, node, config ) {
		                    fcn_add_referencia_show();
		                }
		            },
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Lista de salidas</h2>',
						exportOptions: {
							columns: [ 0, 1, 2, 3 ]
						}
					}
				]
			});
			
			$('#dtreferencias_filter input[type=search]').addClass('text-uppercase');

			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_referencias();"');
			$("div." + div_refresh_name).html(sButton);
			
			oTableReferenciasGrid.on('click', 'a.editor_' + div_table_name + '_reasignar', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableReferenciasGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__sIdReferencia = oData.REFGLOSA;
					
					fcn_reasignar_show();
				} catch (err) {		
					var strMensaje = 'editor_' + div_table_name + '_reasignar() :: ' + err.message;
					show_error(strMensaje);
				}  
			} );
			
			oTableReferenciasGrid.on('click', 'a.editor_' + div_table_name + '_comentarios', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableReferenciasGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__sIdReferencia = oData.NUM_REFE;
					__sIdUsuario = oData.USUARIO;
					__sListo = oData.LISTO;

					if (__sIdReferencia == '' || __sIdReferencia == null) {
						show_modal_error('La referencia se borro o no existe, favor de reasignar a una referencia valida.');
						return;
					}
					
					if (__sIdUsuario == null || __sIdUsuario == undefined) {
						__sIdUsuario = '';
					}

					if (__sListo == '' || __sListo == null) { 
						__sListo = false;
					} else {
						__sListo = true;
					}

					fcn_problemas_show();
				} catch (err) {		
					var strMensaje = 'editor_' + div_table_name + '_comentarios() :: ' + err.message;
					show_error(strMensaje);
				}  
			} );

			oTableReferenciasGrid.on('click', 'a.editor_' + div_table_name + '_listo', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableReferenciasGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__sIdReferencia = oData.NUM_REFE;
					__sIdUsuario = oData.USUARIO;

					if (__sIdReferencia == '' || __sIdReferencia == null) {
						show_modal_error('La referencia se borro o no existe, favor de reasignar a una referencia valida.');
						return;
					}
					
					if (__sIdUsuario == null || __sIdUsuario == undefined) {
						__sIdUsuario = '';
					}

					if (__sIdUsuario == '') {
						fcn_listo_show();
					} else {
						var strTitle = 'Cerrar Referencia';
						var strQuestion = 'Desea cerrar la referencia [' + __sIdReferencia + ']?';
						var oFunctionOk = function () { ajax_set_update_cerrar_referencia(); };
						var oFunctionCancel = null;
						show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
					}
				} catch (err) {		
					var strMensaje = 'editor_' + div_table_name + '_listo() :: ' + err.message;
					show_error(strMensaje);
				}  
			} );
			
			oTableReferenciasGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message);
			} );
		} else {
			pOpt = ((pOpt == null || pOpt == undefined)? false : true);

			var table = oTableReferenciasGrid.DataTable();
			table.ajax.reload(null, pOpt);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_referencias() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_cargar_grid_add_referencia(pOpt) {
	try {
		if (oTableAddReferenciaGrid == null) {
			var div_table_name = 'dt_add_referencia';
			var div_refresh_name = 'div_' + div_table_name + '_refresh';
			
			oTableAddReferenciaGrid = $('#' + div_table_name);
			
			oTableAddReferenciaGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: false,
				ajax: {
					"url": "ajax/ctrlGlosa/postFindReferencia.php",
					"type": "POST",
					"timeout": 20000,
					"error": handleAjaxError,
					"data": function ( d ) {
			            d.sReferencia = $('#itxt_add_referencia_referencia').val();
					},
				},
				columns: [ 
					{ data: "NUM_REFE", className: "text-center"},	
					{ data: "FECHA_ALTA", className: "text-center"},
					{ data: "EJECUTIVO"},
					{ data: "CLIENTE"},
					{
						data: 'LISTO',
						className: "text-center",
						render: function ( data, type, row ) {
							if (type === 'display') {
								if (data != '' && data != null) {
									return '<span style="color:#449d44;"><i class="fa fa-check-circle" aria-hidden="true"> Cerrado</i></span>';
								} else {
									if (row.FECHA_ALTA != '' && row.FECHA_ALTA != null) {
										return '<span style="color:#449d44;"><i class="fa fa-check-circle" aria-hidden="true"> Pendiente</i></span>';
									} else {
										return '<a class="editor_' + div_table_name + '_agregar" style="color:#d9534f;"><i class="fa fa-plus" aria-hidden="true"></i> Agregar</a>';	
									}
								}	
							}	
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
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Lista de salidas</h2>',
						exportOptions: {
							columns: [ 0, 1, 2, 3 ]
						}
					}
				]
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_add_referencia();"');
			$("div." + div_refresh_name).html(sButton);
				
			oTableAddReferenciaGrid.on('click', 'a.editor_' + div_table_name + '_agregar', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableAddReferenciaGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__sIdReferencia = oData.NUM_REFE;
					__sIdUsuario = oData.USUARIO;
					__sListo = oData.LISTO;

					if (__sIdUsuario == null || __sIdUsuario == undefined) {
						__sIdUsuario = '';
					}

					if (__sListo == '' || __sListo == null) { 
						__sListo = false;
					} else {
						__sListo = true;
					}

					fcn_problemas_show();
				} catch (err) {		
					var strMensaje = 'editor_' + div_table_name + '_comentarios() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oTableAddReferenciaGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message);
			} );
		} else {
			pOpt = ((pOpt == null || pOpt == undefined)? false : true);

			var table = oTableAddReferenciaGrid.DataTable();
			table.ajax.reload(null, pOpt);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_add_referencia() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_cargar_grid_cat_problemas() {
	try {
		if (oTableCatProblemasGrid == null) {
			var div_refresh_name = 'div_dtcat_problemas_refresh';
			var div_table_name = 'dtcat_problemas';
			
			oTableCatProblemasGrid = $('#' + div_table_name);
			
			oTableCatProblemasGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: false,
				ajax: {
					"url": "ajax/ctrlGlosa/postProblemas.php",
					"type": "POST",
					"timeout": 20000,
					"error": handleAjaxError
				},
				columns: [ 
					{ data: "PROBLEMA"},	
					{ data: "FECHA_ALTA", className: "text-center"},
					{
						data: null,
						className: "text-center",
						defaultContent: '<a class="editor_' + div_table_name + '_editar"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>'
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
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Catalogo de Problemas</h2>',
						exportOptions: {
							columns: [ 0, 1 ]
						}
					}
				]
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_cat_problemas();"');
			$("div." + div_refresh_name).html(sButton);
				
			oTableCatProblemasGrid.on('click', 'a.editor_' + div_table_name + '_editar', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableCatProblemasGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__sIdProblema = oData.ID_PROBLEMA;
					__sOperacion = 'edit';

					fcn_cat_problemas_add();
					$('#itxt_cat_problemas_problema').val(oData.PROBLEMA);
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_error(strMensaje);
				}  
			} );
			
			oTableCatProblemasGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message);
			} );
		} else {
			var table = oTableCatProblemasGrid.DataTable();
			table.ajax.reload();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_cat_problemas() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_cargar_grid_reportes() {
	try {
		if (oTableReportesGrid == null) {
			var div_refresh_name = 'div_dtreportes_refresh';
			var div_table_name = 'dtreportes';
			
			oTableReportesGrid = $('#' + div_table_name);
			
			oTableReportesGrid.removeAttr('width').DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: false,
				ajax: {
					"url": "ajax/ctrlGlosa/postReportes.php",
					"type": "POST",
					"timeout": 20000,
					"error": handleAjaxError,
					"data": function ( d ) {
						d.bReporteGenerar = __bReporteGenerar;
						d.sFechaInicio = __sFechaInicio;
						d.sFechaFin = __sFechaFin;
			            d.sTipo = __sTipo;
			            d.sTipoOpt2 = __sTipoOpt2;
					},
				},
				columnDefs: [
					{ className: "dt-head-center", "targets": [1,5,6,7] },
				    { className: "dt-body-left", "targets": [1,5,6,7] }
			    ],
				columns: [ 	
					{ data: "FECHA", className: "text-center"},
					{ data: "EJECUTIVO"},	
					{ data: "OPERACION", className: "text-center"},
					{ data: "ADUANA", className: "text-center"},
					{ data: "PEDIMENTO", className: "text-center"},
					{ data: "CLIENTE"},
					{ data: "PROVEEDOR"},
					{ 
						data: "ERROR",
						render: function ( data, type, row ) {
							if (data != '' && data != null) {
								return data;
							} else {
								return 'OK';
							}
						}
					}
				],
				responsive: false,
				scrollX:        true,
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
				buttons: [
					{
						extend: 'excel',
						text: 'Exportar Excel'
					}
				]
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_reportes();"');
			$("div." + div_refresh_name).html(sButton);
				
			oTableReportesGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message);
			} );
		} else {
			var table = oTableReportesGrid.DataTable();
			table.ajax.reload();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_reportes() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/* ..:: Creamos botones para el datatables ::.. */
function fcn_create_button_datatable(sAriaControls, sBtnTxt, oFunction = '') {
	var sHtml = '';
	
	sHtml += '<a href="#" class="btn btn-default buttons-selected-single pull-right"';
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
		show_message_error('El servidor tardó demasiado en enviar los datos');
	} else {
		show_message_error('Se ha producido un error en el servidor. Por favor espera.');
		
		setTimeout(function(){ hide_message(); }, 5000);
	}
}

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
** CONTROLS FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

function fcn_filtro_referencias() {
	fcn_cargar_grid_referencias(true);
}

/*********************************/
/* ..:: BROWSER ::.. */
/*********************************/

/* ..:: Mostramos la modal para seleccionar un ejecutivo ::.. */
function fcn_listo_show() {
	try {		
		$('#modal_listo').modal({ show: true });
		ajax_get_ejecutivos_list();
	} catch (err) {
		var strMensaje = 'fcn_listo_show() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_listo_cerrar() {
	try {		
		var sIdUsuario = $('#isel_listo_ejecutivo').val();
		if (sIdUsuario == '') {
			show_modal_error('Debes seleccionar un ejecutivo.');
			return;
		}

		__sIdUsuario = sIdUsuario;
		ajax_set_update_cerrar_referencia();
	} catch (err) {
		var strMensaje = 'fcn_listo_show() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*********************************/
/* ..:: BUSCAR Y AGREGAR ::.. */
/*********************************/
function fcn_add_referencia_show() {
	try {		
		$('#itxt_add_referencia_referencia').val('');
		$('#modal_add_referencia').modal({ show: true });
		fcn_cargar_grid_add_referencia();
	} catch (err) {
		var strMensaje = 'fcn_add_referencia_show() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*********************************/
/* ..:: CATALOGO DE PROBLEMAS ::.. */
/*********************************/
/* ..:: Mostramos la modal para configurar catalogo de problemas ::.. */
function fcn_cat_problemas_show() {
	try {		
		fcn_cat_problemas_cancel();

		$('#modal_cat_problemas').modal({ show: true });
		fcn_cargar_grid_cat_problemas();
	} catch (err) {
		var strMensaje = 'fcn_cat_problemas_show() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: Desabilitamos controles para agregar un problema ::.. */
function fcn_cat_problemas_add() {
	try {		
		$('#itxt_cat_problemas_problema').val('');
		$('#itxt_cat_problemas_problema').prop('disabled', false);
		$('#ibtn_cat_problemas_add').hide();
		$('#ibtn_cat_problemas_save').show();
		$('#ibtn_cat_problemas_cancel').show();
	} catch (err) {
		var strMensaje = 'fcn_cat_problemas_add() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: Guardamos el problema ::.. */
function fcn_cat_problemas_save() {
	try {
		if (__sOperacion == 'edit') {
			ajax_set_update_problema();
		} else {
			ajax_set_insert_problema();
		}
	} catch (err) {
		var strMensaje = 'fcn_cat_problemas_save() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: cancelamos la alta de un problema ::.. */
function fcn_cat_problemas_cancel() {
	try {
		__sOperacion = '';
		$('#itxt_cat_problemas_problema').val('');
		$('#itxt_cat_problemas_problema').prop('disabled', true);
		$('#ibtn_cat_problemas_add').show();
		$('#ibtn_cat_problemas_save').hide();
		$('#ibtn_cat_problemas_cancel').hide();
	} catch (err) {
		var strMensaje = 'fcn_cat_problemas_cancel() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/*********************************/
/* ..:: REPORTES ::.. */
/*********************************/
function fcn_reportes_show() {
	try {		
		__bReporteGenerar = false;
		__sTipo = '';
		__sTipoOpt2 = '';
		__sFechaInicio = '';
		__sFechaFin = '';

		fcn_reportes_sel_tipo();

		$('#idtp_reportes_fecha [name="start"]').datepicker('clearDates');
		$('#idtp_reportes_fecha [name="end"]').datepicker('clearDates');

		$('#modal_reportes').modal({ show: true });
		ajax_get_reportes_tipo();
		fcn_cargar_grid_reportes();
	} catch (err) {
		var strMensaje = 'fcn_reportes_show() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_reportes_sel_filtro() {
	try {		
		var sValue = $('#isel_reportes_filtro').val();

		switch(sValue) {
			case 'fecha':
				$('#isel_reportes_tipo').prop('disabled', false);
				$('#isel_reportes_tipo_opt2').prop('disabled', false);

				var sHtml = '<option value="general" selected="true">General</option>';
				sHtml += '<option value="ejecutivo">Ejecutivo</option>';
				sHtml += '<option value="cliente">Cliente</option>';
				sHtml += '<option value="regimen">Regimen</option>';
				sHtml += '<option value="impo_expo">Operaci&oacute;n</option>';
				sHtml += '<option value="problema">Problema</option>';

				$('#isel_reportes_tipo').html(sHtml);

				$('#idiv_reportes_dtreportes').show();
				__bReporteGenerar = false;
				fcn_cargar_grid_reportes();

				fcn_reportes_sel_tipo();
				break;

			case 'grafica':

				__bReporteGenerar = false;
				fcn_cargar_grid_reportes();

				var sHtml = '<option value="ejecutivo" selected="true">Ejecutivo</option>';
				sHtml += '<option value="cliente">Cliente</option>';
				sHtml += '<option value="regimen">Regimen</option>';
				sHtml += '<option value="impo_expo">Impo/Expo</option>';
				sHtml += '<option value="problema">Problema</option>';

				$('#isel_reportes_tipo').html(sHtml);

				$('#isel_reportes_tipo_opt2').empty();
				$('#idiv_reportes_tipo_opt2').empty();
				$('#isel_reportes_tipo_opt2').prop('disabled', true);
				$('#isel_reportes_tipo_opt2').select2({
					theme: "bootstrap",
					width: "off",
					placeholder: "",
					data: []
				});

				$('#idiv_reportes_dtreportes').hide();
				break;
		}
	} catch (err) {
		var strMensaje = 'fcn_reportes_sel_filtro() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_reportes_sel_tipo() {
	try {		
		var sFiltro = $('#isel_reportes_filtro').val();
		var sValue = $('#isel_reportes_tipo').val();
		$('#isel_reportes_tipo_opt2').empty();

		if (sFiltro == 'fecha') {
			$('#isel_reportes_tipo_opt2').prop('disabled', false);
			switch(sValue) {
				case 'general':
					$('#idiv_reportes_tipo_opt2').empty();
					$('#isel_reportes_tipo_opt2').prop('disabled', true);
					$('#isel_reportes_tipo_opt2').select2({
						theme: "bootstrap",
						width: "off",
						placeholder: "",
						data: []
					});
					break;

				case 'ejecutivo':
					$('#idiv_reportes_tipo_opt2').html('Ejecutivo');
					$('#isel_reportes_tipo_opt2').select2({
						theme: "bootstrap",
						width: "off",
						placeholder: "Selecciona un Ejecutivo",
						data: __aReporteData.aEjecutivos
					});
					break;

				case 'cliente':
					$('#idiv_reportes_tipo_opt2').html('Cliente');
					$('#isel_reportes_tipo_opt2').select2({
						theme: "bootstrap",
						width: "off",
						placeholder: "Selecciona un Cliente",
						data: __aReporteData.aClientes
					});

					break;

				case 'regimen':
					$('#idiv_reportes_tipo_opt2').html('Regimen');
					$('#isel_reportes_tipo_opt2').select2({
						theme: "bootstrap",
						width: "off",
						placeholder: "Selecciona un Cliente",
						data: __aRegimen
					});
					break;

				case 'impo_expo':
					$('#idiv_reportes_tipo_opt2').html('Operacion');
					$('#isel_reportes_tipo_opt2').select2({
						theme: "bootstrap",
						width: "off",
						placeholder: "Selecciona una Operaci&oacute;n",
						data: [{ id: '1', text: 'IMPORTACIÓN' }, { id: '2', text: 'EXPORTACIÓN' }]
					});
					break;

				case 'problema':
					$('#idiv_reportes_tipo_opt2').html('Problema');
					$('#isel_reportes_tipo_opt2').select2({
						theme: "bootstrap",
						width: "off",
						placeholder: "Selecciona un Problema",
						data: __aReporteData.aProblemas
					});
					break;
			}
		} else {
			$('#isel_reportes_tipo_opt2').prop('disabled', true);
		}
	} catch (err) {
		var strMensaje = 'fcn_reportes_sel_filtro() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_reportes_generar() {
	try {	
		__bReporteGenerar = false;
		__sTipo = $('#isel_reportes_tipo').val();
		__sTipoOpt2 = $('#isel_reportes_tipo_opt2').val();
		__sFechaInicio = $('#idtp_reportes_fecha [name="start"]').val();
		__sFechaFin = $('#idtp_reportes_fecha [name="end"]').val();
		
		if (__sFechaInicio == '' || __sFechaFin == '') {
			show_modal_error('Debes capturar el rango de fechas.');
			return;
		}

		var sValue = $('#isel_reportes_filtro').val();
		if (sValue == 'fecha') {
			__bReporteGenerar = true;
			fcn_cargar_grid_reportes();
		} else {
			fcn_reportes_descargar_grafica();
		}
	} catch (err) {
		var strMensaje = 'fcn_reportes_generar() :: ' + err.message;
		show_modal_error(strMensaje);
    }	
}

/*********************************/
/* ..:: REASIGNAR REF ::.. */
/*********************************/
function fcn_reasignar_show() {
	try {
		$('#itxt_reasignar_ref_anterior').val(__sIdReferencia);
		$('#itxt_reasignar_ref_nueva').val('');

		$('#modal_reasignar').modal({ show: true });
	} catch (err) {
		var strMensaje = 'fcn_reasignar_show() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/*********************************/
/* ..:: AGREGAR UN PROBLEMA ::.. */
/*********************************/
function fcn_problemas_show() {
	try {
		if (__sListo) {
			//$('#idiv_add_problema_ejecutivo').hide();
			$('#idiv_add_problema_lista').hide();
			$('#idiv_add_problema_observacion').hide();
			$('#idiv_add_problema_footer').hide();			
		} else {			
			//$('#idiv_add_problema_ejecutivo').show();
			$('#idiv_add_problema_lista').show();
			$('#idiv_add_problema_observacion').show();
			$('#idiv_add_problema_footer').show();
		}

		$('#idiv_add_problema_mensaje').empty();
		fcn_problemas_cancel();

		$('#modal_add_problema').modal({ show: true });
		ajax_get_problemas_list();
	} catch (err) {
		var strMensaje = 'fcn_problemas_show() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: Capturamos nuevo problema ::.. */
function fcn_problemas_add() {
	try {
		$('#idiv_add_problema_mensaje').empty();

		if (__sIdUsuario == '') { 
			$('#isel_add_problema_ejecutivo').prop('disabled', false);
		}
		$('#isel_add_problema_lista').prop('disabled', false);
		$('#itxt_add_problema_observacion').prop('disabled', false);
		$('#ibtn_add_problema_lita_cancel').show();
		$('#ibtn_add_problema_lita_save').show();
		$('#ibtn_add_problema_lita_new').hide();
	} catch (err) {
		var strMensaje = 'fcn_problemas_add() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: Guardamos el problema y su observacion ::.. */
function fcn_problemas_save() {
	try {
		ajax_set_insert_problema_observ();
	} catch (err) {
		var strMensaje = 'fcn_cat_problemas_save() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: cancelamos la alta de un problema ::.. */
function fcn_problemas_cancel() {
	try {
		$('#isel_add_problema_ejecutivo').val(__sIdUsuario).trigger("change");
		$('#isel_add_problema_ejecutivo').prop('disabled', true);

		$('#isel_add_problema_lista').val('').trigger("change");
		$('#isel_add_problema_lista').prop('disabled', true);
		$('#itxt_add_problema_observacion').val('');
		$('#itxt_add_problema_observacion').prop('disabled', true);
		$('#ibtn_add_problema_lita_cancel').hide();
		$('#ibtn_add_problema_lita_save').hide();
		$('#ibtn_add_problema_lita_new').show();
	} catch (err) {
		var strMensaje = 'fcn_problemas_cancel() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: eliminamos un comentario ::.. */
function fcn_problemas_del_comentario(id, fecha) {
	__sObservIdProblema = id;
	__sObservFecha = fecha;

	//alert(__sObservIdProblema + ' ' + __sObservFecha);
	var strTitle = 'Eliminar Observaci&oacute;n';
	var strQuestion = 'Desea eliminar la observaci&oacute;n?';
	var oFunctionOk = function () { ajax_set_delete_problema_observ(); };
	var oFunctionCancel = null;
	show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/* ..:: BROWSER ::.. */
function ajax_get_ejecutivos_list() {
	try {	
		var oData = {			
			sIdReferencia: __sIdReferencia
		};

		$.ajax({
            type: "POST",
            url: 'ajax/ctrlGlosa/ajax_get_problemas_list.php',
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
						$('#isel_listo_ejecutivo').html(respuesta.sEjecutivosList);
						$('#isel_listo_ejecutivo').select2({
							theme: "bootstrap",
							width: "off",
							placeholder: "Selecciona un ejecutivo"
						});
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
		var strMensaje = 'ajax_get_ejecutivos_list() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

function ajax_set_update_cerrar_referencia() {
	try {
		var oData = {			
			sIdReferencia: __sIdReferencia,
			sIdUsuario: __sIdUsuario
		};

		$.ajax({
            type: "POST",
            url: 'ajax/ctrlGlosa/ajax_set_update_cerrar_referencia.php',		
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Guardando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						show_modal_ok(respuesta.Mensaje);

						fcn_cargar_grid_referencias();

						$('#modal_listo').modal('hide'); //Para casos en los que muestro la modal para solicitar al ejecutivo (cuando no tiene comentarios).
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
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
		var strMensaje = 'ajax_set_insert_problema() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: CATALOGO DE PROBLEMAS ::.. */
function ajax_set_insert_problema() {
	try {
		var sProblema = $('#itxt_cat_problemas_problema').val().trim().toUpperCase();
		if (sProblema == '') {
			show_modal_error('Debes capturar un problema.');
			return;
		}

		var oData = {			
			sProblema: sProblema
		};

		$.ajax({
            type: "POST",
            url: 'ajax/ctrlGlosa/ajax_set_insert_problema.php',		
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Guardando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						show_modal_ok(respuesta.Mensaje);

						fcn_cat_problemas_cancel();
						fcn_cargar_grid_cat_problemas();
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_message_error(strMensaje);
					}
				} else {
					show_message_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_message_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_insert_problema() :: ' + err.message;
		show_message_error(strMensaje);
    }    
}

function ajax_set_update_problema() {
	try {
		var sProblema = $('#itxt_cat_problemas_problema').val().trim().toUpperCase();
		if (sProblema == '') {
			show_modal_error('Debes capturar un problema.');
			return;
		}

		var oData = {			
			sProblema: sProblema,
			sIdProblema: __sIdProblema
		};

		$.ajax({
            type: "POST",
            url: 'ajax/ctrlGlosa/ajax_set_update_problema.php',		
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Guardando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						show_modal_ok(respuesta.Mensaje);

						fcn_cat_problemas_cancel();
						fcn_cargar_grid_cat_problemas();
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_message_error(strMensaje);
					}
				} else {
					show_message_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_message_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_insert_problema() :: ' + err.message;
		show_message_error(strMensaje);
    }    
}

/* ..:: AGREGAR PROBLEMAS ::.. */
function ajax_get_problemas_list() {
	try {	
		var oData = {			
			sIdReferencia: __sIdReferencia
		};

		$.ajax({
            type: "POST",
            url: 'ajax/ctrlGlosa/ajax_get_problemas_list.php',
			data: oData,
			timeout: 30000,
			
            beforeSend: function (dataMessage) {
            	$('#idiv_timeline').empty();
				show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#isel_add_problema_ejecutivo').html(respuesta.sEjecutivosList);
						$('#isel_add_problema_ejecutivo').select2({
							theme: "bootstrap",
							width: "off",
							placeholder: "Selecciona una opción"
						});

						$('#isel_add_problema_ejecutivo').val(__sIdUsuario).trigger("change");

						$('#isel_add_problema_lista').html(respuesta.sProblemasList);
						$('#isel_add_problema_lista').select2({
							theme: "bootstrap",
							width: "off",
							placeholder: "Selecciona una opción"
						});

						/* Timeline */
						if (respuesta.aComentarios.length > 0) {
							var bInverted = false;
							var oTlUl = $('<ul/>', {'class':'timeline'});
							$.each(respuesta.aComentarios, function (index, value) {
								var oTlHeading = $('<div/>', {'class':'timeline-heading'}).append('<h4>' + value.ct + '</h4>');
								var oTlbody = $('<div/>', {'class':'timeline-body'}).append('<strong style="font-size: 12px; padding: 0px 15px 0px 15px;">' + value.pb + '</strong><p style="font-size: 12px; padding-top:0px;">' + value.cmt + '</p>');
								
								/************************/
								var sTLFooter = '';
								if (__sListo) {
									sTLFooter = '<p class="text-left" style="width: 10%; display: inline-block;">&nbsp;</p>';
									sTLFooter += '<p class="text-right" style="width: 90%; display: inline-block;">' + value.dt + '</p>';						
								} else {
									var nWidth = '10';
									var sLink = '&nbsp;';
									if ($.inArray(__sEmailUsuario, __aEmailsAutorizados) >= 0) {
										nWidth = 30;
										sLink = '<a style="color: #d9534f; text-decoration: none;" href="#" onclick="javascript:fcn_problemas_del_comentario(\'' + value.ipb + '\', \'' + value.fa + '\');"><i class="fa fa-ban" aria-hidden="true"></i> Eliminar</a>';
									}
									sTLFooter = '<p class="text-left" style="width: ' + nWidth + '%; display: inline-block;">' + sLink + '</p>';
									sTLFooter += '<p class="text-right" style="width: ' + (100 - nWidth) + '%; display: inline-block;">' + value.dt + '</p>';
								}
								var oTlFooter = $('<div/>', {'class':'timeline-footer'}).append(sTLFooter);
								/************************/

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

							if (__sListo) {
								var nTimeLineHeight = $('#idiv_timeline').height();
								if (nTimeLineHeight > 400) {
									$('#style-4').height('400px');
								} else {
									$('#style-4').height(nTimeLineHeight + 'px');
								}
							} else {
								$('#style-4').height('200px');
							}	
						} else {
							$('#style-4').height('0px');
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
		var strMensaje = 'ajax_get_problemas_list() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

function ajax_set_insert_problema_observ() {
	try {
		var sIdUsuario = $('#isel_add_problema_ejecutivo').val();
		if (sIdUsuario == '') {
			show_modal_error('Debes seleccionar un ejecutivo.');
			return;
		}

		var sIdProblema = $('#isel_add_problema_lista').val();
		if (sIdProblema == '') {
			show_modal_error('Debes seleccionar un problema.');
			return;
		}

		var sObservacion = $('#itxt_add_problema_observacion').val().trim().toUpperCase();
		if (sObservacion == '') {
			show_modal_error('Debes capturar una observacion.');
			return;
		}

		var oData = {
			sIdReferencia: __sIdReferencia,			
			sIdProblema: sIdProblema,
			sObservacion: sObservacion,
			sIdUsuario: sIdUsuario
		};

		$.ajax({
            type: "POST",
            url: 'ajax/ctrlGlosa/ajax_set_insert_problema_observ.php',		
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Guardando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						if (__sIdUsuario == '') {
							__sIdUsuario = $('#isel_add_problema_ejecutivo').val();	
						}

						var sHtml = '<div class="alert alert-success" style="margin-bottom: 0px; margin-top: 15px;">';
						sHtml +=	'	<strong>Exito!</strong> ' + respuesta.Mensaje;
						sHtml +=    '</div>';
						
						$('#idiv_add_problema_mensaje').html(sHtml);
						$('#idiv_add_problema_mensaje').show();

						fcn_problemas_cancel();
						fcn_cargar_grid_referencias();
						setTimeout(function () { ajax_get_problemas_list(); },500);						
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_message_error(strMensaje);
					}
				} else {
					show_message_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_message_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_insert_problema_observ() :: ' + err.message;
		show_message_error(strMensaje);
    }    
}

function ajax_set_delete_problema_observ() {
	try {
		var oData = {
			sIdReferencia: __sIdReferencia,			
			sObservIdProblema: __sObservIdProblema,
			sObservFecha: __sObservFecha
		};

		$.ajax({
            type: "POST",
            url: 'ajax/ctrlGlosa/ajax_set_delete_problema_observ.php',		
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Guardando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						if (__sIdUsuario == '') {
							__sIdUsuario = $('#isel_add_problema_ejecutivo').val();	
						}

						var sHtml = '<div class="alert alert-success" style="margin-bottom: 0px; margin-top: 15px;">';
						sHtml +=	'	<strong>Exito!</strong> ' + respuesta.Mensaje;
						sHtml +=    '</div>';
						
						$('#idiv_add_problema_mensaje').html(sHtml);
						$('#idiv_add_problema_mensaje').show();

						fcn_problemas_cancel();
						fcn_cargar_grid_referencias();
						setTimeout(function () { ajax_get_problemas_list(); },500);						
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_message_error(strMensaje);
					}
				} else {
					show_message_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_message_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_delete_problema_observ() :: ' + err.message;
		show_message_error(strMensaje);
    }    
}

/* ..:: REPORTES ::.. */
function ajax_get_reportes_tipo() {
	try {	
		var oData = {			
			sIdReferencia: __sIdReferencia
		};

		$.ajax({
            type: "POST",
            url: 'ajax/ctrlGlosa/ajax_get_reportes_tipo.php',
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
						__aReporteData = respuesta.aReporteData;

						/*$('#isel_reportes_tipo_opt2').select2({
							theme: "bootstrap",
							width: "off",
							placeholder: "Selecciona un Ejecutivo",
							data: __aReporteData.aClientes
						});*/

						/*$('#isel_add_problema_ejecutivo').html(respuesta.sEjecutivosList);
						$('#isel_add_problema_ejecutivo').select2({
							theme: "bootstrap",
							width: "off",
							placeholder: "Selecciona una opción"
						});

						$('#isel_add_problema_ejecutivo').val(__sIdUsuario).trigger("change");

						$('#isel_add_problema_lista').html(respuesta.sProblemasList);
						$('#isel_add_problema_lista').select2({
							theme: "bootstrap",
							width: "off",
							placeholder: "Selecciona una opción"
						});*/
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
		var strMensaje = 'ajax_get_problemas_list() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: REASIGNAR ::.. */
function ajax_set_reasignar_ref() {
	try {	
		var sNuevaRefefencia = $('#itxt_reasignar_ref_nueva').val();
		if (sNuevaRefefencia == '') {
			show_modal_error('Debes ingresar una Referencia.');
			return;
		}
	
		var oData = {			
			sIdReferencia: __sIdReferencia,
			sNuevaRefefencia: sNuevaRefefencia
		};

		$.ajax({
            type: "POST",
            url: 'ajax/ctrlGlosa/ajax_set_reasignar_ref.php',
			data: oData,
			timeout: 30000,
			
            beforeSend: function (dataMessage) {
            	show_load_config(true, 'Actualizando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#modal_reasignar').modal('hide');
						show_modal_ok(respuesta.Mensaje);					
						fcn_cargar_grid_referencias();
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
		var strMensaje = 'ajax_set_reasignar_ref() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/*********************************************************************************************************************************
** DOWNLOAD FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

function fcn_reportes_descargar_grafica() {
	var data = { 
		sFechaInicio: __sFechaInicio,
		sFechaFin: __sFechaFin,
		sTipo: __sTipo
	};

	var oForm = document.createElement("form");
	oForm.target = 'data';
	oForm.method = 'POST'; // or "post" if appropriate
	oForm.action = 'ajax/ctrlGlosa/dwn_generar_grafica.php';

	var oInput = document.createElement("input");
	oInput.type = "text";
	oInput.name = "aData";
	oInput.value = JSON.stringify(data);
	oForm.appendChild(oInput);

	document.body.appendChild(oForm);
	oForm.submit();

	oForm.remove();
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