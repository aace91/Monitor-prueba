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

var oTableEmpresasGrid = null;
var oTablePendientesGrid = null;
var oTableCuentaGastosDetallesGrid = null;
var oTableArchivoGrid = null;

var __EmpresaId;
var __EmpresaNombre;
var __EmpresaRutaDatos;
var __EmpresaRutaCasa;
var __NumeroCaja;

var __strCuentaGastos;
var __strReferencia;

var __nIdRegistroComentario;
				
var __nTimerSecondsDigitalizacion = 300; //Representado en segundos
var __nCountDigitalizacion = __nTimerSecondsDigitalizacion;
var __oTimerDigitalizacion;

var __bRefreshGrid = false;
var __nTimerSecondsRefresGrid = 30; //Representado en segundos
var __nCountRefresGrid = __nTimerSecondsRefresGrid; //Contador de segundos, cuando sea mayor a nTimerSecondsRefreshGrid se reinicia

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
		$("#itxt_modal_select_empresa_caja").TouchSpin({
			initval: 1,
			min: 1,
			max: 1000000000
		});	
		
		$("#itxt_modal_relacion_caja_caja").TouchSpin({
			initval: 1,
			min: 1,
			max: 1000000000
		});	
		
		$("#itxt_modal_etiqueta_caja_caja").TouchSpin({
			initval: 1,
			min: 1,
			max: 1000000000
		});	
				
		fcn_mostrar_empresas();
		
		$('#itxt_archivar_cuenta_cuenta').on("keypress", function(e) {
			if(e.keyCode == 16 || e.keyCode == 17 || e.keyCode == 74) {
				console.log('keypress: ' + e.keyCode);
				e.preventDefault();
				e.stopPropagation();
				return;
			}
			
			if (e.keyCode == 13) {
				fcn_archivar();
			}
		});
		
		$('#itxt_archivar_cuenta_cuenta').on("keydown", function(e) {
			if(e.keyCode == 16 || e.keyCode == 17 || e.keyCode == 74) {
				console.log('keydown: ' + e.keyCode);		

				if (e.keyCode != 16 && e.keyCode != 17) {
					//console.log(String.fromCharCode(e.keyCode));
					$(this).val($(this).val() + String.fromCharCode(e.keyCode));
				}
				e.preventDefault();
				e.stopPropagation();
				return;
			}
		});
		
		$('#itxt_archivar_cuenta_cuenta').on("keyup", function(e) {
			if(e.keyCode == 16 || e.keyCode == 17) {
				console.log('keyup: ' + e.keyCode);				
				e.preventDefault();
				e.stopPropagation();
				return;
			}
		});
		
		/*$('#itxt_archivar_cuenta_cuenta').on("keypress", function(e) {
			if (e.keyCode == 13) {
				fcn_archivar();
			}
		});*/
		
		fcn_loadPrinters();

		__oTimerDigitalizacion = setTimeout(function () { ajax_set_upd_aplicar_digitalizacion('timer'); }, (__nTimerSecondsDigitalizacion * 1000));
		setInterval(function(){ 
			__nCountDigitalizacion -= 1;
			if (__nCountDigitalizacion <= 0) {
				__nCountDigitalizacion = 0;
			}
			
			$('#ispan_sync_digitalizacion_message').html('<i class="fa fa-clock-o" aria-hidden="true"></i> Sincronizando en ' + __nCountDigitalizacion + ' Segundos');
		}, 1000);		
		
		setInterval(function(){ 
			__nCountRefresGrid -= 1;
			if (__nCountRefresGrid <= 0) {
				fcn_refrescar_grids(); 
			}
			
			$('#ispan_sync_message').html('<i class="fa fa-clock-o" aria-hidden="true"></i> Actualizando Informaci&oacute;n de grid en ' + __nCountRefresGrid + ' Segundos');
		}, 1000);
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

function fcn_cargar_grid_empresas() {
	try {
		if (oTableEmpresasGrid == null) {
			oTableEmpresasGrid = $('#dtempresas');
				
			oTableEmpresasGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expArchivo/postExpArchivo_Empresas.php",
					"type": "POST"
				},
				columns: [ 
					{ "data": "nombre", "className": "def_app_left"},		
					{
						data: null,
						className: "def_app_center",
						render: function ( data, type, row ) {
							if ( type === 'display' ) {
								if (row.id_empresa == __EmpresaId) { 
									return '<input type="checkbox" class="editor-active" checked="checked">';
								} else {
									return '<input type="checkbox" class="editor-active">';
								}
							}
							return data;
						}
					}
				],
				responsive: true,
				bFilter: false, //Quitamos el filtro
				paging: false,  //Quitamos las paginas
				aLengthMenu: [
					[25, 50, 100, 200, -1],
					[25, 50, 100, 200, "All"]
				],
				iDisplayLength: 25,
				language: {
					sProcessing: '<img src="../images/cargando.gif" height="36" width="36"> Cargando, espera un momento por favor...',
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
			});
			
			oTableEmpresasGrid.on( 'change', 'input.editor-active', function () {
				try {				
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					
					var table = oTableEmpresasGrid.DataTable();
					var oData = table.row(current_row).data();				
					
					__EmpresaId = oData.id_empresa;
					__EmpresaNombre = oData.nombre;
					__EmpresaRutaDatos = oData.rutadatos;
					__EmpresaRutaCasa = oData.rutacasa;
					
					$(':checkbox', table.rows().nodes()).prop('checked', false);
					$(':checkbox', table.row(current_row).nodes()).prop('checked', true);

					ajax_get_ultima_caja();
					//fcn_mostrar_panel();					
					// var bExisteValor = false;
					// jQuery.each(table.rows().nodes(), function(index, item) {
						// var oRow_EmpresaId = table.row(index).data().id_empresa;
						// if (oRow_EmpresaId === __EmpresaId) {
							// $(':checkbox', table.rows(index).nodes()).prop('checked', true);
						// } else {
							// $(':checkbox', table.rows(index).nodes()).prop('checked', false);
						// }					
					// });
				} catch (err) {		
					var strMensaje = 'editor_empresa_change() :: ' + err.message;
					show_label_error(strMensaje);
				}
			} );
		} else {
			ajax_get_grid_empresas_data();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_empresas() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_pendientes() {
	try {
		if (oTablePendientesGrid == null) {	
			oTablePendientesGrid = $('#dtpendientes');
			
			oTablePendientesGrid.DataTable({
				order: [[0, 'asc']],
				processing: true,
				serverSide: false,
				ajax: {
					"url": "ajax/expedientes/expArchivo/postExpArchivo_Pendientes.php",
					"type": "POST",
					"data": function ( d ) {
			            d.sIdEmpresa = __EmpresaId;
					}
				},
				columns: [ 
					{ data: "cuenta_gastos", className: "def_app_center"},	
					{ data: "referencia", className: "def_app_center"},
					{
						data: null,
						className: "def_app_center",
						defaultContent: '<a href="#" class="editor_pendientes_detalles"><i class="fa fa-eye" aria-hidden="true"></i> Ver Detalles</a>'
					},
					{
						data: null,
						className: "def_app_center",
						defaultContent: '<a href="#" class="editor_pendientes_etiqueta"><i class="fa fa-barcode" aria-hidden="true"></i></a>'
					}	
				],		
				responsive: true,
				aLengthMenu: [
					[10, 50, 100, 200, -1],
					[10, 50, 100, 200, "All"]
				],
				iDisplayLength: 10,
				language: {
					sProcessing: '<img src="../images/cargando.gif" height="36" width="36"> Cargando, espera un momento por favor...',
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
					 "<'row'<'col-xs-8'B><'col-xs-4'<'div_pendientes_refresh'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Pendientes por Archivar</h2>'
					},
					{
						text: '<i class="fa fa-barcode" aria-hidden="true"></i> Imprimir Todas Etiquetas',
						action: function ( e, dt, node, config ) {
							fcn_imprimir_todas_etiquetas();
		            	}
					}
				]
			} );	
			
			var sButton = fcn_create_button_datatable('dtpendientes', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_pendientes_data();"');
			$("div.div_pendientes_refresh").html(sButton);
			
			oTablePendientesGrid.on('click', 'a.editor_pendientes_detalles', function (e) {
				try {				
					fcn_mostrar_modal_cuenta_gastos_detalles(this, oTablePendientesGrid);
				} catch (err) {		
					var strMensaje = 'editor_pendientes_detalles_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );

			oTablePendientesGrid.on('click', 'a.editor_pendientes_etiqueta', function (e) {
				try {			
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					
					var table = oTablePendientesGrid.DataTable();
					var oData = table.row(current_row).data();		

					var oLabelInfo = {
						cliente: oData.cliente_nombre, 
						impo_expo: oData.impo_expo, 
						pedimento: oData.pedimento, 
						referencia: oData.referencia, 
						cuenta: oData.no_mov, 
						codebar: oData.cuenta_gastos
					};
					var aLabelInfo = new Array();
					aLabelInfo.push(oLabelInfo);

					fcn_imprimir_etiqueta_expediente(aLabelInfo);
				} catch (err) {		
					var strMensaje = 'editor_pendientes_etiqueta_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			ajax_get_grid_pendientes_data();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pendientes() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_cuenta_gastos_detalles() {
	try {
		if (oTableCuentaGastosDetallesGrid == null) {
			oTableCuentaGastosDetallesGrid = $('#dtcuenta_gastos_detalles');
			
			oTableCuentaGastosDetallesGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expArchivo/postExpArchivo_CuentaGastosDetalles.php",
					"type": "POST",
					"data": function ( d ) {
			            d.sIdEmpresa = __EmpresaId;
						d.sCuentaGastos = __strCuentaGastos;
						d.sReferencia = __strReferencia;
					}
				},
				columns: [ 
					{ data: "cuenta_gastos", className: "def_app_center"},	
					{ data: "referencia", className: "def_app_center"},	
					{ data: "pedimento", className: "def_app_center"},
					{
						data:  "comentarios",
						render: function ( data, type, row ) {
							if (data == "") {
								return '<a href="#" class="editor_detalles_editar_comentario"><i class="fa fa-comment-o" aria-hidden="true"></i> Agregar Comentario</a>';
							} else {
								return '<a href="#" class="editor_detalles_editar_comentario"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Editar</a> ' + data;
							}
						},
						className: "def_app_left"
					},
					{ data: "fecha_cc_facturacion", className: "def_app_center"}				
				],
				//select: true,
				responsive: true,
				aLengthMenu: [
					[25, 50, 100, 200, -1],
					[25, 50, 100, 200, "All"]
				],
				iDisplayLength: 25,
				language: {
					sProcessing: '<img src="../images/cargando.gif" height="36" width="36"> Cargando, espera un momento por favor...',
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
					 "<'row'<'col-sm-8'B>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'print',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Detalles de la Cuenta: ' + __strCuentaGastos + '</h2>'
					}
				]
			});	

			oTableCuentaGastosDetallesGrid.on('click', 'a.editor_detalles_editar_comentario', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableCuentaGastosDetallesGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__nIdRegistroComentario = oData.id_registro;
					fcn_mostrar_comentarios(oData.comentarios);
				} catch (err) {		
					var strMensaje = 'editor_recive_pedimento_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			ajax_get_grid_cuenta_gastos_detalles_data();
		}	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_cuenta_gastos_detalles() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_archivar() {
	try {
		if (oTableArchivoGrid == null) {
			oTableArchivoGrid = $('#dtarchivar');
			oTableArchivoGrid.DataTable({
				scrollY:        "200px",
				scrollCollapse: true,
				bFilter: false, //Quitamos el filtro
				paging: false,  //Quitamos las paginas
			});
			
			oTableArchivoGrid.find('tbody').on( 'click', 'a.editor_modal_archivo_registro_delete', function () {
				oTableArchivoGrid.DataTable().row( $(this).parents('tr') ).remove().draw();
			} );
		} else {
			oTableArchivoGrid.DataTable().clear().draw();
		}
		
		setTimeout(function(){ oTableArchivoGrid.DataTable().columns.adjust().draw(); }, 250);	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_archivar() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

/* ..:: Creamos botones para el datatables ::.. */
function fcn_create_button_datatable(sAriaControls, sBtnTxt, oFunction = '') {
	var sHtml = '';
	
	sHtml += '<a class="btn btn-default buttons-selected-single pull-right"';
	sHtml += '    tabindex="0"';
	sHtml += '    aria-controls="' + sAriaControls + '"';
	sHtml += ' ' + oFunction;
	sHtml += '    href="#">';
	sHtml += '    <span>'+ sBtnTxt +'</span>';
	sHtml += '</a>';
	
	return sHtml;
}

/*********************************************************************************************************************************
** CONTROLS FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

function toUpper(control) {
	if (/[a-z]/.test(control.value)) {
		control.value = control.value.toUpperCase();
	}
}	

/* ..:: Obligamos a seleccionar una empresa ::.. */
function fcn_mostrar_empresas() {
	//$('#idiv_panel_principal').hide();
	$('#modal_select_empresa').modal({
		show: true,
		backdrop: 'static',
		keyboard: false
	});
	
	__bRefreshGrid = false;
	fcn_cargar_grid_empresas();
}

/* ..:: Mostramos el panel despues de seleccionar una empresa ::.. */
function fcn_mostrar_panel() {
	try {
		$('#modal_select_empresa').modal('hide');
		//$('#idiv_panel_principal').show();
		
		var sHtml = '';
		sHtml += '<i class="fa fa-industry" aria-hidden="true"></i>' + ' ';
		sHtml += '<strong>EMPRESA SELECCIONADA:</strong>' + ' [' + __EmpresaNombre + ']'+ '  ';
		//sHtml += '<button type="button" class="btn btn-warning btn-xs" onclick="fcn_mostrar_empresas();"><i class="fa fa-exchange" aria-hidden="true"></i> Cambiar</button>';
		$('#istr_trabajando_empresa').html(sHtml);
		
		sHtml = '';
		sHtml += '<i class="fa fa-archive" aria-hidden="true"></i>' + ' ';
		sHtml += '<strong>CAJA SELECCIONADA:</strong>' + ' [ ' + __NumeroCaja + ' ]'+ ' ';
		$('#istr_trabajando_Caja').html(sHtml);
			
		setTimeout(function(){ 
			$("#itxt_archivar_cuenta_cuenta").focus();
		}, 500);
	
		$.mask.definitions['h'] = "[ireIRE]";
		$('#itxt_archivar_cuenta_cuenta').mask("h-9-9999?9999");
		
        __nCountDigitalizacion = __nTimerSecondsDigitalizacion;
		__nCountRefresGrid = __nTimerSecondsRefresGrid;
		__bRefreshGrid = true;
		fcn_cargar_grid_pendientes();
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_panel() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

/* ..:: Mostramos la modal para agregar caja ::.. */
function fcn_mostrar_modal_add_caja() {
	try {	
		$('#modal_add_caja').modal({ show: true });
		$('#itxt_modal_add_caja_observaciones').val('');
		$('#ibtn_modal_add_caja_aceptar').show();
		$('#idiv_modal_add_caja_mensaje').hide();
		
		setTimeout(function(){ 
			$("#itxt_modal_add_caja_observaciones").focus();
		}, 500);
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_add_caja() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Mostramos la modal para Relacion de Caja ::.. */
function fcn_mostrar_modal_relacion_caja() {
	try {	
		$('#modal_relacion_caja').modal({ show: true });
		$('#idiv_modal_relacion_caja_mensaje').hide();
		
		setTimeout(function(){ 
			$("#itxt_modal_relacion_caja_caja").focus();
		}, 500);
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_relacion_caja() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Mostramos la modal para Etiquetas de Caja ::.. */
function fcn_mostrar_modal_etiqueta_caja() {
	try {	
		$('#modal_etiqueta_caja').modal({ show: true });
		$('#idiv_modal_etiqueta_caja_mensaje').hide();
		
		setTimeout(function(){ 
			$("#itxt_modal_etiqueta_caja_caja").focus();
		}, 500);
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_etiqueta_caja() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Mostramos la modal para Etiquetas de Caja ::.. */
function fcn_mostrar_modal_cancelar_cuenta() {
	try {	
		$('#modal_cancelar_cuenta').modal({ show: true });
		$('#idiv_modal_cancelar_cuenta_mensaje').hide();
		
		$('#itxt_modal_cancelar_cuenta_ctacancelada').val('');
		$('#itxt_modal_cancelar_cuenta_ctanueva').val('');
		
		$('#itxt_modal_cancelar_cuenta_ctacancelada').mask("h-9-9999?9999");
		$('#itxt_modal_cancelar_cuenta_ctanueva').mask("h-9-9999?9999");
		setTimeout(function(){ 
			$("#itxt_modal_cancelar_cuenta_ctacancelada").focus();
		}, 500);
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_cancelar_cuenta() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Mostramos la modal para imprimir etiqueta de cuenta ::.. */
function fcn_mostrar_modal_imprimir_cuenta_etiqueta() {
	try {	
		$('#modal_imprimir_cuenta').modal({ show: true });
		$('#idiv_modal_imprimir_cuenta_mensaje').hide();
		
		$('#itxt_modal_imprimir_cuenta_cuenta').val('');		
		$('#itxt_modal_imprimir_cuenta_cuenta').mask("h-9-9999?9999");
		setTimeout(function(){ 
			$("#itxt_modal_cancelar_cuenta_ctacancelada").focus();
		}, 500);
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_imprimir_cuenta_etiqueta() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}


/* ..:: Agregamos la nueva caja ::.. */
function fcn_modal_add_caja() {
	ajax_set_add_caja();
}

/* ..:: Mostramos la modal para mostrar los detalles de la cuenta de gastos ::.. */
function fcn_mostrar_modal_cuenta_gastos_detalles(oThis, oTable, dtFecha = '') {
	try {	
		var current_row = $(oThis).parents('tr');//Get the current row
		if (current_row.hasClass('child')) {//Check if the current row is a child row
			current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
		}
		
		var table = oTable.DataTable();
		var oData = table.row(current_row).data();			
		
		__EmpresaId = oData.id_empresa;
		__strCuentaGastos = oData.cuenta_gastos;
		__strReferencia = oData.referencia;
					
		$('#modal_cuenta_gastos_detalles').modal({ show: true });
		
		fcn_cargar_grid_cuenta_gastos_detalles();
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_cuenta_gastos_detalles() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Verificamos y archivamos la cuenta de gastos ::.. */
function fcn_archivar() {
	try {			
		var strValor = $('#itxt_archivar_cuenta_cuenta').val();
		strValor = strValor.toUpperCase().trim();
		
		var sHtml = '';
		if (strValor.trim()) {
			var aCuentas = new Array();
			aCuentas.push(strValor);
			
			var oDiv = $('#idiv_archivar_mensaje');
			var bContinue = true;
			
			if (oDiv.is(':visible')) {
				if (oDiv.find('div').hasClass("alert-error") || oDiv.find('div').hasClass("alert-warning")) {
					show_label_error('Favor de atender y cerrar la alerta, dar clic boton X de alerta');
					bContinue = false;
				}
			}
			
			if (bContinue) {
				ajax_set_add_archivar(aCuentas);
			}
		} else {
			sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
			sHtml += '    <span style="color:#FFF;">Cuenta de gastos invalida!</span>';
			sHtml += '</div>';
		}
		
		//Mostramos mensaje de error
		if (sHtml.trim()) {
			var $MsjCtrl = $('#idiv_archivar_mensaje');
			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			
			setTimeout(function () {
				$MsjCtrl.fadeOut();
			},5000);	
		}
		
		$('#itxt_archivar_cuenta_cuenta').val('');
		$("#itxt_archivar_cuenta_cuenta").focus();
	} catch (err) {		
		var strMensaje = 'fcn_archivar() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Refrescamos grids ::.. */
function fcn_refrescar_grids() {
	if (__bRefreshGrid) {
		fcn_cargar_grid_pendientes();
	} else {
		__nCountRefresGrid = __nTimerSecondsRefresGrid;
	}
}

/* ..:: Mostramos modal para editar los comentarios ::.. */
function fcn_mostrar_comentarios(sComentario) {
	$('#modal_edit_comentario_click').modal({
		show: true,
		backdrop: 'static',
		keyboard: false
	});
	$('#idiv_modal_edit_comentario_click_mensaje').hide();
	$('#ibtn_modal_edit_comentario_click_agregar').show();
	
	$('#itxt_modal_edit_comentario_click_comentarios').val('');
	$('#itxt_modal_edit_comentario_click_comentarios').val(sComentario);
}


/* ..:: Generamos el array para imprimir ::.. */
function fcn_imprimir_todas_etiquetas() {
	try {			
		var aLabelInfo = new Array();
		var table = oTablePendientesGrid.DataTable();
		jQuery.each(table.rows().nodes(), function(index, item) {
			var oLabelInfo = {
				cliente: table.row(index).data().cliente_nombre, 
				impo_expo: table.row(index).data().impo_expo, 
				pedimento: table.row(index).data().pedimento, 
				referencia: table.row(index).data().referencia, 
				cuenta: table.row(index).data().no_mov, 
				codebar: table.row(index).data().cuenta_gastos
			};
			aLabelInfo.push(oLabelInfo);
		});
	
		fcn_imprimir_etiqueta_expediente(aLabelInfo);
	} catch (err) {		
		var strMensaje = 'fcn_imprimir_todas_etiquetas() :: ' + err.message;
		show_label_error(strMensaje, false);
    }
}

/**************************************************/
/* IMPRIMIR ETIQUETAS */
/**************************************************/

function fcn_loadPrinters(){
	printersSelect = document.getElementById('isel_modal_select_empresa_impresora');
	printers = dymo.label.framework.getLabelWriterPrinters();
	if (printers.length == 0)
	{
		//alert("No hay impresoras Dymo instaladas, contacte al administrador");
		return;
	}

	for (var i = 0; i < printers.length; i++)
	{
		var printer = printers[i];

		var printerName = printer.name;

		var option = document.createElement('option');
		option.value = printerName;
		option.appendChild(document.createTextNode(printerName));

		printersSelect.append(option);
	}
}

function fcn_imprimir_etiqueta_expediente(aLabelInfo){
	var ini = 0;var fin = 0;
	barcodeLabel = dymo.label.framework.openLabelXml(getBarcodeLabelXmlEtiquetaExpediente());				
	labelSet = new dymo.label.framework.LabelSetBuilder();

	$.each( aLabelInfo, function(key, value) {
		var record = labelSet.addRecord();
		record.setText("txt_cliente", value.cliente);
		record.setText("txt_empo_expo", value.impo_expo);
		record.setText("txt_pedimento", value.pedimento);
		record.setText("txt_referencia", value.referencia);
		record.setText("txt_cuenta", value.cuenta);
		record.setText("txt_codebar", value.codebar);
	});
		
	if (!barcodeLabel){
		var strMensaje = 'Load label before printing'
		show_label_error(strMensaje, false);
		return;
	}
	if (!labelSet){
		var strMensaje = 'Label data is not loaded'
		show_label_error(strMensaje, false);
		return;
	}
	if (!$('#isel_modal_select_empresa_impresora').val()){
		var strMensaje = 'Select printer DYMO.'
		show_label_error(strMensaje, false);
		return;
	}
	barcodeLabel.print($('#isel_modal_select_empresa_impresora').val(),'',labelSet);
}

function fcn_imprimir_etiqueta_caja(aLabelInfo){
	var ini = 0;var fin = 0;
	barcodeLabel = dymo.label.framework.openLabelXml(getBarcodeLabelXmlEtiquetaCaja());				
	labelSet = new dymo.label.framework.LabelSetBuilder();

	$.each( aLabelInfo, function(key, value) {
		var record = labelSet.addRecord();
		record.setText("txt_caja", value.caja);
		record.setText("txt_codebar", value.codebar);
	});
		
	if (!barcodeLabel){
		var strMensaje = 'Load label before printing'
		show_label_error(strMensaje, false);
		return;
	}
	if (!labelSet){
		var strMensaje = 'Label data is not loaded'
		show_label_error(strMensaje, false);
		return;
	}
	if (!$('#isel_modal_select_empresa_impresora').val()){
		var strMensaje = 'Select printer DYMO.'
		show_label_error(strMensaje, false);
		return;
	}
	barcodeLabel.print($('#isel_modal_select_empresa_impresora').val(),'',labelSet);
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/* ..:: DATATABLES ::.. */
/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_empresas_data() {
	try {	
		var table = oTableEmpresasGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_folios_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_pendientes_data() {
	try {	
		__nCountRefresGrid = __nTimerSecondsRefresGrid;
		
		var table = oTablePendientesGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_pendientes_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_digitalizados_data() {
	try {	
		var table = oTableDigitalizadosGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_digitalizados_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_cuenta_gastos_detalles_data() {
	try {	
		var table = oTableCuentaGastosDetallesGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_cuenta_gastos_detalles_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}


/* ..:: EMPRESA/CAJA ::.. */
function ajax_get_verificar_caja() {
	try {		
		__NumeroCaja = $('#itxt_modal_select_empresa_caja').val();
		
		var sHtml = '';
		var $MsjCtrl = $('#idiv_modal_select_empresa_mensaje');
		
		if (__EmpresaId == '' || __EmpresaId == undefined) {
			sHtml += '<div class="alert alert-error">';
			sHtml += '    <span style="color:#FFF;">Debe seleccionar una Empresa!</span>';
			sHtml += '</div>';
		}
		
		if (sHtml == '') {
			if (__NumeroCaja == 0) {
				sHtml += '<div class="alert alert-error">';
				sHtml += '    <span style="color:#FFF;">Debe ingresar una Caja valida!</span>';
				sHtml += '</div>';
			}
		}
		
		if (sHtml != '') {
			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			
			setTimeout(function () {
				$MsjCtrl.fadeOut();
			},5000);
			
			return;
		}
		
		/************************************************************************/
		
		var oData = {			
			sIdEmpresa: __EmpresaId,
			sNumeroCaja: __NumeroCaja
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expArchivo/ajax_get_verificar_caja.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Verificando existencia de caja, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						fcn_mostrar_panel();
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_label_error(strMensaje);
					}
				}else{
					show_label_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_label_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_verificar_caja() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

function ajax_set_add_caja() {
	try {		
		var oData = {			
			sIdEmpresa: __EmpresaId,
			sObservaciones: $('#itxt_modal_add_caja_observaciones').val().trim()
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expArchivo/ajax_set_add_caja.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Agregando Caja, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){						
						var oFunctionOk = function () { 
							__NumeroCaja = respuesta.idCaja; 
							fcn_mostrar_panel();
						};
						var oFunctionCancel = function () { $('#modalconfirm').modal('hide'); };
						var strMensaje = respuesta.Mensaje + '\nDesea trabajar con la nueva caja?';
						show_confirm('Nueva Caja', strMensaje, oFunctionOk, oFunctionCancel);
									
						$('#ibtn_modal_add_caja_aceptar').hide();
						$('#modal_add_caja').modal('hide');
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_label_error(strMensaje);
					}
				}else{
					show_label_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_label_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_add_caja() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: Consultamos la ultima caja ::.. */
function ajax_get_ultima_caja() {
	try {	
		var oData = {			
			sIdEmpresa: __EmpresaId
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expArchivo/ajax_get_ultima_caja.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando caja, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						var sHtml = '<p class="text-info" style="margin-bottom:0px;"><i class="fa fa-archive" aria-hidden="true"></i> &Uacute;ltima Caja en Sistema: [ <strong>' + respuesta.Caja + '</strong> ]</p>';
						$('#idiv_modal_select_empresa_ultima_caja').html(sHtml);
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_label_error(strMensaje);
					}
				}else{
					show_label_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_label_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_ultima_caja() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: ARCHIVAR ::.. */
/* ..:: Agregamos la fecha_archivo_archivado, estos pasan a pendientes por entregar ::.. */
function ajax_set_add_archivar(aCuentas) {
	try {		
		var oData = {			
			sIdEmpresa: __EmpresaId,
			sNumeroCaja: __NumeroCaja,
			aCuentas: JSON.stringify(aCuentas)
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expArchivo/ajax_set_add_archivar.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Archivando, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					
					var $MsjCtrl = $('#idiv_archivar_mensaje');
					var sHtml = '';
					if (respuesta.Codigo == '1'){
						ajax_get_grid_pendientes_data();
						//$('#ibtn_modal_archivar_aceptar').hide();
												
						sHtml += '<div class="alert alert-success" style="margin-bottom:5px;">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '!</span>';
						sHtml += '</div>';
												
						$MsjCtrl.html(sHtml);
						$MsjCtrl.fadeIn();
						
						setTimeout(function () {
							$MsjCtrl.fadeOut();
						},4000);
					} else if (respuesta.Codigo == '100') {
						sHtml += '<div class="alert alert-warning" style="margin-bottom:5px;">';
						sHtml += '    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>';
						sHtml += '    <strong>Advertencia!</strong> <span>' + respuesta.Mensaje + '!</span>';
						sHtml += '</div>';

						$MsjCtrl.html(sHtml);
						$MsjCtrl.fadeIn();
					} else {
						sHtml += '<div class="alert alert-error" style="margin-bottom:5px;">';
						sHtml += '    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '!</span>';
						sHtml += '</div>';

						$MsjCtrl.html(sHtml);
						$MsjCtrl.fadeIn();
					}
				}else{
					show_label_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_label_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_upd_facturar() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: DIGITALIZACION ::.. */
function ajax_set_upd_aplicar_digitalizacion(pControlCall) {
	try {	
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxP/ajax_set_upd_aplicar_digitalizacion.php',
		
            beforeSend: function (dataMessage) {
				clearTimeout(__oTimerDigitalizacion);
				$('#ibtn_sync').html('<i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Sincronizando');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#ibtn_sync').html('<i class="fa fa-refresh" aria-hidden="true"></i> Sincronizar Documentos');
						
						__nCountDigitalizacion = __nTimerSecondsDigitalizacion;
						__oTimerDigitalizacion = setTimeout(function () { ajax_set_upd_aplicar_digitalizacion('timer'); }, (__nTimerSecondsDigitalizacion * 1000));
						fcn_refrescar_grids();
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_label_error(strMensaje);
					}
				}else{
					show_label_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_label_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_archivos_prueba() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: DETALLES ::.. */
/* ..:: Editar comentario ::.. */
function ajax_set_upd_comentario() {
	try {		
		var sComentarios = $('#itxt_modal_edit_comentario_click_comentarios').val().toUpperCase();
	
		var oData = {			
			nIdRegistro: __nIdRegistroComentario,
			sComentarios: sComentarios
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxC/ajax_set_upd_comentario.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Actualizando comentario, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					
					var sHtml = '';
					var $MsjCtrl = $('#idiv_modal_edit_comentario_click_mensaje');
					if (respuesta.Codigo == '1'){
						fcn_cargar_grid_cuenta_gastos_detalles();
						$('#ibtn_modal_edit_comentario_click_agregar').hide();
												
						sHtml += '<div class="alert alert-success" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '!</span>';
						sHtml += '</div>';
						
						$MsjCtrl.html(sHtml);
						$MsjCtrl.fadeIn();
						
						setTimeout(function () {
							$MsjCtrl.fadeOut();
						},4000);
					} else {
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						
						sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + strMensaje + '</span>';
						sHtml += '</div>';
						
						$MsjCtrl.html(sHtml);
						$MsjCtrl.fadeIn();
						
						setTimeout(function () {
							$MsjCtrl.fadeOut();
						},4000);
					}
				}else{
					show_label_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_label_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_upd_comentario() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: CANCELAR CUENTA ::.. */
/* ..:: Cancelamos la cuenta y la reemplazamos por la que se indica ::.. */
function ajax_set_upd_cancelar_cta() {
	try {		
		$('#idiv_modal_cancelar_cuenta_mensaje').hide();
		var sHtml = '';
		
		var strCuentaCancelada = $('#itxt_modal_cancelar_cuenta_ctacancelada').val().toUpperCase().trim();
		var strCuentaNueva = $('#itxt_modal_cancelar_cuenta_ctanueva').val().toUpperCase().trim();
		
		if (strCuentaCancelada.trim() == '') { 
			sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
			sHtml += '    <span style="color:#FFF;">Cuenta cancelada invalida!</span>';
			sHtml += '</div>';
			
			$('#idiv_modal_cancelar_cuenta_mensaje').html(sHtml).show();
			return;
		} 
		
		if (strCuentaNueva.trim() == '') { 
			sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
			sHtml += '    <span style="color:#FFF;">Cuenta de reemplazo invalida!</span>';
			sHtml += '</div>';
			
			$('#idiv_modal_cancelar_cuenta_mensaje').html(sHtml).show();
			return;
		} 
	
		var oData = {	
			sIdEmpresa: __EmpresaId,		
			sCuentaCancelada: strCuentaCancelada,
			sCuentaNueva: strCuentaNueva
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expArchivo/ajax_set_upd_cancelar_cta.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Archivando, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					
					var $MsjCtrl = $('#idiv_modal_cancelar_cuenta_mensaje');
					var sHtml = '';
					if (respuesta.Codigo == '1'){
						ajax_get_grid_pendientes_data();
						
						sHtml += '<div class="alert alert-success" style="margin-bottom:5px;">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '!</span>';
						sHtml += '</div>';
						
						$MsjCtrl.html(sHtml).show();
					} else {
						sHtml += '<div class="alert alert-error" style="margin-bottom:5px;">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '!</span>';
						sHtml += '</div>';

						$MsjCtrl.html(sHtml).show();
					}
				}else{
					show_label_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_label_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_upd_cancelar_cta() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: EMPRESA/CAJA ::.. */
function ajax_get_datos_cuenta() {
	try {		
		$('#idiv_modal_imprimir_cuenta_mensaje').hide();
		var sHtml = '';
		
		var strCuenta = $('#itxt_modal_imprimir_cuenta_cuenta').val().toUpperCase().trim();
		if (strCuenta.trim() == '') { 
			sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
			sHtml += '    <span style="color:#FFF;">Cuenta invalida!</span>';
			sHtml += '</div>';
			
			$('#idiv_modal_imprimir_cuenta_mensaje').html(sHtml).show();
			return;
		} 
		
		var oData = {	
			sIdEmpresa: __EmpresaId,		
			strCuenta: strCuenta
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expArchivo/ajax_get_datos_cuenta.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					
					var $MsjCtrl = $('#idiv_modal_imprimir_cuenta_mensaje');
					var sHtml = '';
					if (respuesta.Codigo == '1'){
						var aLabelInfo = new Array();
						var oLabelInfo = {
							cliente: respuesta.cliente_nombre, 
							impo_expo: respuesta.impo_expo, 
							pedimento: respuesta.pedimento, 
							referencia: respuesta.referencia, 
							cuenta: respuesta.no_mov, 
							codebar: respuesta.cuenta_gastos
						};
						aLabelInfo.push(oLabelInfo);
						
						fcn_imprimir_etiqueta_expediente(aLabelInfo);
					} else {
						sHtml += '<div class="alert alert-error" style="margin-bottom:5px;">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '!</span>';
						sHtml += '</div>';

						$MsjCtrl.html(sHtml).show();
					}
				}else{
					show_label_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_label_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_upd_cancelar_cta() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/*********************************************************************************************************************************
** DOWNLOAD FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

function fcn_exportar_relacion_caja() {
	var sNumeroCaja = $('#itxt_modal_relacion_caja_caja').val();
	
	var sHtml = '';
	var $MsjCtrl = $('#idiv_modal_relacion_caja_mensaje');
		
	if (sNumeroCaja == 0) {
		sHtml += '<div class="alert alert-error">';
		sHtml += '    <span style="color:#FFF;">Debe ingresar una Caja valida!</span>';
		sHtml += '</div>';
	} else {
		var oForm = document.createElement("form");
		oForm.target = 'data';
		oForm.method = 'POST'; // or "post" if appropriate
		oForm.action = 'ajax/expedientes/expArchivo/exportar_relacion_caja.php';

		var oInput = document.createElement("input");
		oInput.type = "text";
		oInput.name = "sNumeroCaja";
		oInput.value = sNumeroCaja;
		oForm.appendChild(oInput);
		
		document.body.appendChild(oForm);
		oForm.submit();
		$(oForm).remove();
	}
}

function fcn_exportar_etiqueta_caja() {
	var sNumeroCaja = $('#itxt_modal_etiqueta_caja_caja').val();
	
	var sHtml = '';
	var $MsjCtrl = $('#idiv_modal_etiqueta_caja_mensaje');
		
	if (sNumeroCaja == 0) {
		sHtml += '<div class="alert alert-error">';
		sHtml += '    <span style="color:#FFF;">Debe ingresar una Caja valida!</span>';
		sHtml += '</div>';
	} else {
		/*var oForm = document.createElement("form");
		oForm.target = 'data';
		oForm.method = 'POST'; // or "post" if appropriate
		oForm.action = 'ajax/expedientes/expArchivo/exportar_etiqueta_caja.php';

		var oInput = document.createElement("input");
		oInput.type = "text";
		oInput.name = "sNumeroCaja";
		oInput.value = sNumeroCaja;
		oForm.appendChild(oInput);
		
		document.body.appendChild(oForm);
		oForm.submit();
		$(oForm).remove();*/

		var oLabelInfo = {
			caja: 'Caja #' + sNumeroCaja, 
			codebar: sNumeroCaja
		};
		var aLabelInfo = new Array();
		aLabelInfo.push(oLabelInfo);

		fcn_imprimir_etiqueta_caja(aLabelInfo);
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
function show_label_ok(sMensaje) {
	if (sMensaje == null || sMensaje == undefined) {
		sMensaje = '';
	}
	
    $('#modalmessagebox_ok_titulo').html(appName);
	$('#modalmessagebox_ok_mensaje').html(sMensaje);						
	setTimeout(function () {
		$('#modalmessagebox_ok').modal({ show: true });
	},500);
}

/* ..:: Funcion que muestra el mensaje de error (lblError) ::.. */
function show_label_error(sMensaje) {
	if (sMensaje == null || sMensaje == undefined) {
		sMensaje = '';
	}
	
	show_load_config(false);
	
    $('#modalmessagebox_error_span').html("ERROR :: " + appName);
	$('#modalmessagebox_error_mensaje').html(sMensaje);
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
	$('#modalconfirm_mensaje').html(strQuestion);
	
	//Eliminamos evento click
	$('#modalconfirm_btn_ok').off( "click");
	$('#modalconfirm_btn_cancel').off( "click");
	
	//Reasignamos evento click Boton OK
	$('#modalconfirm_btn_ok').on( "click", function() {
		oFunctionOk();
		$('#modalconfirm').modal('hide');
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