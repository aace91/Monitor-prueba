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

var oTablePedimentosGrid;
var oTableDetalleIncGrid;
var oTableDetalleCruceGrid;

var __sPedimento;
var __sIdCruce;

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
		
		$("#isel_cliente").select2({
			theme: "bootstrap",
			width: "off",
			placeholder: "Seleccione un Cliente",
		});
	
		fcn_cargar_grid_pedimentos();
    } catch (err) {		
		var strMensaje = 'application_run() :: ' + err.message;
		show_label_error(strMensaje);
    }

    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    /*++ Buttons Events                                                                                                             ++*/
    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
}

/*********************************************************************************************************************************
** FILL AND CREATE GRIDS FUNCTIONS                                                                                              **
*********************************************************************************************************************************/

function fcn_cargar_grid_pedimentos(bReloadPaging) {
	try {
		show_custom_function_error('', 'idiv_mensaje');
		if (oTablePedimentosGrid == null) {
			var oDivDisplayErrors = 'idiv_mensaje';
			var div_table_name = 'dt_pedimentos';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oTablePedimentosGrid = $('#' + div_table_name);
			
			oTablePedimentosGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				columnDefs: [
					{ targets: [2], orderable: false }
				],
				ajax: {
					"url": "ajax/facturacion/rpt_incrementablesxpedime/rptIncrementPedimeFunc.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
			            d.action = 'table_pedimentos';
			            d.id_cliente = (!($('#isel_cliente').val().trim())? -1 : $('#isel_cliente').val());
					},
					"error": handleAjaxError
				},
				columns: [ 
					{ "data": "pedimento" },
					{ "data": "numero_cruces", "className": "text-center" },
					{   "data": null,
						"className": "text-center",
						"mRender": function (data, type, row) {
							var sHtml = '';
							sHtml += '<a class="btn btn-primary btn-xs editor_' + div_table_name + '_ver"><i class="fa fa-eye" aria-hidden="true"></i></a>';
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
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_pedimentos(true);"');
			$("div." + div_refresh_name).html(sButton);
			
			oTablePedimentosGrid.on('click', 'a.editor_' + div_table_name + '_ver', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oTablePedimentosGrid);
					
					__sPedimento = oData.pedimento;
					
					fcn_show_detalles();
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oTablePedimentosGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);

			var table = oTablePedimentosGrid.DataTable();
			table.search('').ajax.reload(null, bReloadPaging);
			setTimeout(function(){ oTablePedimentosGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pedimentos() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

function fcn_cargar_grid_detalle_incrementable(bReloadPaging) {
	try {
		show_custom_function_error('', 'idiv_mensaje');
		if (oTableDetalleIncGrid == null) {
			var oDivDisplayErrors = 'idiv_mensaje';
			var div_table_name = 'dt_detalle_inc';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oTableDetalleIncGrid = $('#' + div_table_name);
			
			oTableDetalleIncGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				columnDefs: [
					{ targets: [3, 4], orderable: false }
				],
				ajax: {
					"url": "ajax/facturacion/rpt_incrementablesxpedime/rptIncrementPedimeFunc.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
			            d.action = 'table_detalle_incrementable';
						d.id_cliente = (!($('#isel_cliente').val().trim())? -1 : $('#isel_cliente').val());
			            d.pedimento = __sPedimento;
					},
					"error": handleAjaxError
				},
				columns: [ 
					{ "data": "id_cruce" },
					{ "data": "fecha_salida", "className": "text-center" },
					{ "data": "caja", "className": "text-center" },
					{ "data": "total", "className": "text-center" },
					{   "data": null,
						"className": "text-center",
						"mRender": function (data, type, row) {
							var sHtml = '';
							sHtml += '<a class="btn btn-primary btn-xs editor_' + div_table_name + '_ver"><i class="fa fa-eye" aria-hidden="true"></i></a>';
							return sHtml;
						} 
					}
				],
				responsive: true,
				aLengthMenu: [
					[10, 25, 50, 100, 200, -1],
					[10, 25, 50, 100, 200, "All"]
				],
				iDisplayLength: -1,
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
				dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				"bPaginate": false,
				"bLengthChange": false,
				"bInfo": false,	
				buttons: [
					{
						extend: 'excelHtml5',
						text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Exportar Excel',
						exportOptions: {
							columns: ':visible'
						}
					}
				],
				footerCallback: function ( row, data, start, end, display ) {
					var api = this.api(), data;
		 
					// Remove the formatting to get integer data for summation
					var intVal = function ( i ) {
						return typeof i === 'string' ?
							i.replace(/[\$,]/g, '')*1 :
							typeof i === 'number' ?
								i : 0;
					};
		 
					// Total over all pages
					total = api
						.column(3)
						.data()
						.reduce( function (a, b) {
							return intVal(a) + intVal(b);
						}, 0);
		 
					// Total over this page
					pageTotal = api
						.column(3, { page: 'current'} )
						.data()
						.reduce( function (a, b) {
							return intVal(a) + intVal(b);
						}, 0);
		 
					// Update footer
					$( api.column(3).footer() ).html(
						//'$'+pageTotal +' ( $'+ total +' total)'
						'$ ' + (total).toFixed(2)
					);
				}
			});
			
			oTableDetalleIncGrid.on('click', 'a.editor_' + div_table_name + '_ver', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oTableDetalleIncGrid);
					
					__sIdCruce = oData.id_cruce;
					
					fcn_show_detalle_cruce();
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );

			oTableDetalleIncGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);

			var table = oTableDetalleIncGrid.DataTable();
			table.search('').ajax.reload(null, bReloadPaging);
			setTimeout(function(){ oTableDetalleIncGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_detalle_incrementable() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

function fcn_cargar_grid_detalle_cruce(bReloadPaging) {
	try {
		show_custom_function_error('', 'idiv_mdl_det_cruce_mensaje');
		if (oTableDetalleCruceGrid == null) {
			var oDivDisplayErrors = 'idiv_mdl_det_cruce_mensaje';
			var div_table_name = 'dt_detalle_cruce';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oTableDetalleCruceGrid = $('#' + div_table_name);
			
			oTableDetalleCruceGrid.DataTable({
				order: [[3, 'desc']],
				processing: true,
				serverSide: true,
				columnDefs: [
					{ targets: [0, 1, 2, 5, 6, 7], orderable: false }
				],
				ajax: {
					"url": "ajax/facturacion/rpt_incrementablesxpedime/rptIncrementPedimeFunc.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
			            d.action = 'table_detalle_cruce';
						d.id_cruce = __sIdCruce;
			            d.pedimento = __sPedimento;
					},
					"error": handleAjaxError
				},
				columns: [ 
					{ "data": "id_cruce", "className": "text-center" },
					{ "data": "pedimento", "className": "text-center" },
					{ "data": "tc", "className": "text-right" },
					{ "data": "no_partes" },
					{ "data": "titulo" },
					{ "data": "cantidad", "className": "text-right" },
					{ "data": "tarifa", "className": "text-right" },
					{ "data": "total", "className": "text-right" }
				],
				responsive: true,
				aLengthMenu: [
					[10, 25, 50, 100, 200, -1],
					[10, 25, 50, 100, 200, "All"]
				],
				iDisplayLength: -1,
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
				dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				"bPaginate": false,
				"bLengthChange": false,
				"bInfo": false,	
				buttons: [
					{
						extend: 'excelHtml5',
						text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Exportar Excel',
						exportOptions: {
							columns: ':visible'
						}
					}
				],
				footerCallback: function ( row, data, start, end, display ) {
					var api = this.api(), data;
					var columna_suma = 7;
		 
					// Remove the formatting to get integer data for summation
					var intVal = function ( i ) {
						return typeof i === 'string' ?
							i.replace(/[\$,]/g, '')*1 :
							typeof i === 'number' ?
								i : 0;
					};
		 
					// Total over all pages
					total = api
						.column(columna_suma)
						.data()
						.reduce( function (a, b) {
							return intVal(a) + intVal(b);
						}, 0);
		 
					// Total over this page
					pageTotal = api
						.column(columna_suma, { page: 'current'} )
						.data()
						.reduce( function (a, b) {
							return intVal(a) + intVal(b);
						}, 0);
		 
					// Update footer
					$( api.column(columna_suma).footer() ).html(
						//'$'+pageTotal +' ( $'+ total +' total)'
						'$ ' + (total).toFixed(2)
					);
				}
			});
			
			oTableDetalleCruceGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);

			var table = oTableDetalleCruceGrid.DataTable();
			table.search('').ajax.reload(null, bReloadPaging);
			setTimeout(function(){ oTableDetalleCruceGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_detalle_cruce() :: ' + err.message;
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
	var bExist = message.includes("500");
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

function fcn_show_detalles() {
	try {	
		$('#idiv_principal').hide();
		$('#idiv_detalle').show();
		
		$('#ispan_pedimento_actual').html(__sPedimento);		
		
		fcn_cargar_grid_detalle_incrementable();
	} catch (err) {		
		var strMensaje = 'fcn_show_detalles() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

function fcn_close_detalles() {
	try {	
		$('#idiv_principal').show();
		$('#idiv_detalle').hide();
	} catch (err) {		
		var strMensaje = 'fcn_close_detalles() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

function fcn_show_detalle_cruce() {
	try {	
		$('#modal_detalle_cruce').modal({ show: true });
		$('#ispan_det_cruce_title').html('<strong>' + __sIdCruce + '</strong>');
		
		fcn_cargar_grid_detalle_cruce();
	} catch (err) {		
		var strMensaje = 'fcn_show_detalles() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* *********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

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