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

var oTablePendientesGrid = null;
var oTableDigitalizadosGrid = null;
var oTablePendientesEntGrid = null;
var oTableEntregadosGrid = null;
var oTableTraficosGrid =  null;
var oTableCuentaGastosDetallesGrid = null;

var __nTimerSecondsDigitalizacion = 300; //Representado en segundos
var __nCountDigitalizacion = __nTimerSecondsDigitalizacion;
var __oTimerDigitalizacion;

var __nTimerSecondsRefresGrid = 30; //Representado en segundos
var __nCountRefresGrid = __nTimerSecondsRefresGrid; //Contador de segundos, cuando sea mayor a nTimerSecondsRefreshGrid se reinicia

var __EmpresaId;
var __strCuentaGastos;
var __strReferencia;
		
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
		fcn_cargar_grid_pendientes();
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var target = $(e.target).attr("href") // activated tab
			
			switch(target) {
				case '#idiv_pendientes':
					fcn_cargar_grid_pendientes();
					break;
					
				case '#idiv_digitalizados':
					fcn_cargar_grid_digitalizados();
					break;
				
				case '#idiv_pendientes_ent':
					fcn_cargar_grid_pendientes_ent();
					break;	
				
				case '#idiv_entregados':
					ajax_get_entregados_date_data();
					//fcn_cargar_grid_entregados();
					break;	
			}
		});
		
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
		
		$('#itxt_modal_entregar_trafico').on("keypress", function(e) {
			if(e.keyCode == 16 || e.keyCode == 17 || e.keyCode == 74) {
				console.log('keypress: ' + e.keyCode);
				e.preventDefault();
				e.stopPropagation();
				return;
			}
			
			if (e.keyCode == 13) {
				fcn_modal_entregar_agregar_trafico();
			}
		});
		
		$('#itxt_modal_entregar_trafico').on("keydown", function(e) {
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
		
		$('#itxt_modal_entregar_trafico').on("keyup", function(e) {
			if(e.keyCode == 16 || e.keyCode == 17) {
				console.log('keyup: ' + e.keyCode);				
				e.preventDefault();
				e.stopPropagation();
				return;
			}
		});
		
		/*$(document).on('keydown', function(e) {
			if(e.keyCode == 16 || e.keyCode == 17) {
				console.log('keypress: ' + e.keyCode);
				//return false;
				e.preventDefault();
				e.stopPropagation();
				return;
			}
		});*/
		
		/*$(document).on('keydown', function(e) {
			if(e.keyCode == 16 || e.keyCode == 17) {
				console.log('keydown: ' + e.keyCode);
				//return false;
				e.preventDefault();
				e.stopPropagation();
				return;
			} else {
				console.log('keydown else: ' + e.keyCode);
			}
		});*/
		
		/*$(document).on('keyup', function(e) {
			if(e.keyCode == 16 || e.keyCode == 17) {
				console.log('keypress: ' + e.keyCode);
				//return false;
				e.preventDefault();
				e.stopPropagation();
				return;
			}
		});*/
  
		fcn_crear_cookies();
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

function fcn_cargar_grid_pendientes(bReloadPaging) {
	try {
		if (oTablePendientesGrid == null) {	
			oTablePendientesGrid = $('#dtpendientes');
			
			oTablePendientesGrid.DataTable({
				columnDefs: [ {
					searchable: false,
					orderable: false,
					targets: [2, 3]
				}],
				order: [[0, 'asc']],
				processing: true,			
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expCxP/postExpCxP_Pendientes.php",
					"type": "POST"
				},
				columns: [ 
					{ data: "cuenta_gastos", className: "text-center"},	
					{ data: "referencia", className: "text-center"},
					{ data: "refnumber",
						"className": "text-center",
						"mRender": function (data, type, row) {
							if(data!=null && data!=''){
								return '<center><a href="'+ row.idinvoice +'" target="_blank">'+data+' <i class="fa fa-download fa-3"></i></a></center>';
							} else {
								return '<center>No Disponible</center>';
							}
						}
					},
					{
						data: null,
						className: "text-center",
						defaultContent: '<a href="#" class="editor_pendientes_detalles"><i class="fa fa-eye" aria-hidden="true"></i> Ver Detalles</a>'
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
						title: '<h2>Pendientes por Digitalizar</h2>',
						exportOptions: {
							columns: [ 0, 1 ]
						}
					}
				]
			} );	
			
			var sButton = fcn_create_button_datatable('dtpendientes', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_pendientes(true);"');
			$("div.div_pendientes_refresh").html(sButton);
			
			oTablePendientesGrid.on('click', 'a.editor_pendientes_detalles', function (e) {
				try {				
					fcn_mostrar_modal_cuenta_gastos_detalles(this, oTablePendientesGrid);
				} catch (err) {		
					var strMensaje = 'editor_pendientes_detalles_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			__nCountRefresGrid = __nTimerSecondsRefresGrid;
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);
			
			var table = oTablePendientesGrid.DataTable();
			if (bReloadPaging) {
				table.search('').ajax.reload(null, bReloadPaging);
			} else {
				table.ajax.reload(null, bReloadPaging);
			}
			setTimeout(function(){ oTablePendientesGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pendientes() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_digitalizados() {
	try {
		if (oTableDigitalizadosGrid == null) {	
			oTableDigitalizadosGrid = $('#dtdigitalizados');
			
			oTableDigitalizadosGrid.DataTable({
				order: [[0, 'asc']],
				processing: true,			
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expCxP/postExpCxP_Digitalizados.php",
					"type": "POST"
				},
				columns: [ 
					{ data: "cuenta_gastos", className: "text-center"},	
					{ data: "referencia", className: "text-center"},
					{ data: "refnumber",
						"className": "text-center",
						"mRender": function (data, type, row) {
							if(data!=null && data!=''){
								return '<center><a href="'+ row.idinvoice +'" target="_blank">'+data+' <i class="fa fa-download fa-3"></i></a></center>';
							} else {
								return '<center>No Disponible</center>';
							}
						}
					},
					{
						data: null,
						className: "text-center",
						defaultContent: '<a href="#" class="editor_digitalizados_detalles"><i class="fa fa-eye" aria-hidden="true"></i> Ver Detalles</a>'
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
					 "<'row'<'col-xs-8'B><'col-xs-4'<'div_digitalizados_refresh'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Cuentas Digitalizadas</h2>'
					}
				]
			} );	
			
			var sButton = fcn_create_button_datatable('dtdigitalizados', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_digitalizados_data();"');
			$("div.div_digitalizados_refresh").html(sButton);
			
			oTableDigitalizadosGrid.on('click', 'a.editor_digitalizados_detalles', function (e) {
				try {				
					fcn_mostrar_modal_cuenta_gastos_detalles(this, oTableDigitalizadosGrid);
				} catch (err) {		
					var strMensaje = 'editor_digitalizados_detalles_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			__nCountRefresGrid = __nTimerSecondsRefresGrid;
		
			var table = oTableDigitalizadosGrid.DataTable();
			table.ajax.reload();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_digitalizados() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_pendientes_ent(bReloadPaging) {
	try {
		if (oTablePendientesEntGrid == null) {	
			oTablePendientesEntGrid = $('#dtpendientes_ent');
			
			oTablePendientesEntGrid.DataTable({
				columnDefs: [ {
					searchable: false,
					orderable: false,
					targets: [2, 3]
				}],
				order: [[0, 'asc']],
				processing: true,			
				serverSide: false,
				ajax: {
					"url": "ajax/expedientes/expCxP/postExpCxP_PendientesEntregar.php",
					"type": "POST"
				},
				columns: [ 
					{ data: "cuenta_gastos", className: "text-center"},	
					{ data: "referencia", className: "text-center"},
					{ data: "refnumber",
						"className": "text-center",
						"mRender": function (data, type, row) {
							if(data!=null && data!=''){
								return '<center><a href="'+ row.idinvoice +'" target="_blank">'+data+' <i class="fa fa-download fa-3"></i></a></center>';
							} else {
								return '<center>No Disponible</center>';
							}
						}
					},
					{
						data: null,
						className: "text-center",
						defaultContent: '<a href="#" class="editor_pendientes_ent_detalles"><i class="fa fa-eye" aria-hidden="true"></i> Ver Detalles</a>'
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
					 "<'row'<'col-xs-8'B><'col-xs-4'<'div_digitalizados_refresh'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Cuentas Digitalizadas</h2>',
						exportOptions: {
							columns: [ 0, 1 ]
						}
					}
				]
			} );	
			
			var sButton = fcn_create_button_datatable('dtpendientes_ent', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_pendientes_ent(true);"');
			$("div.div_digitalizados_refresh").html(sButton);
			
			oTablePendientesEntGrid.on('click', 'a.editor_pendientes_ent_detalles', function (e) {
				try {				
					fcn_mostrar_modal_cuenta_gastos_detalles(this, oTablePendientesEntGrid);
				} catch (err) {		
					var strMensaje = 'editor_pendientes_ent_detalles_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			__nCountRefresGrid = __nTimerSecondsRefresGrid;
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);
			
			var table = oTablePendientesEntGrid.DataTable();
			if (bReloadPaging) {
				table.search('').ajax.reload(null, bReloadPaging);
			} else {
				table.ajax.reload(null, bReloadPaging);
			}
			setTimeout(function(){ oTablePendientesEntGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pendientes_ent() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_entregados(bReloadPaging) {
	try {
		if (oTableEntregadosGrid == null) {	
			oTableEntregadosGrid = $('#dtentregados');
			
			oTableEntregadosGrid.DataTable({
				columnDefs: [ {
					searchable: false,
					orderable: false,
					targets: [0, 3, 4, 5]
				}],
				order: [[1, 'asc']],
				processing: true,			
				serverSide: false,
				ajax: {
					"url": "ajax/expedientes/expCxP/postExpCxP_Entregados.php",
					"type": "POST",
					"data": function ( d ) {
						d.sFecha = $('#isel_entregados_fechas').val();
					}
				},
				columns: [ 
					{ data: null, defaultContent: '' },
					{ data: "cuenta_gastos", className: "text-center"},	
					{ data: "referencia", className: "text-center"},
					{ data: "fecha_entrega", className: "text-center"},
					{ data: "refnumber",
						"className": "text-center",
						"mRender": function (data, type, row) {
							if(data!=null && data!=''){
								return '<center><a href="'+ row.idinvoice +'" target="_blank">'+data+' <i class="fa fa-download fa-3"></i></a></center>';
							} else {
								return '<center>No Disponible</center>';
							}
						}
					},
					{
						data: null,
						className: "text-center",
						defaultContent: '<a href="#" class="editor_entregados_detalles"><i class="fa fa-eye" aria-hidden="true"></i> Ver Detalles</a>'
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
					 "<'row'<'col-xs-8'B><'col-xs-4'<'div_digitalizados_refresh'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Cuentas Entregadas</h2>',
						exportOptions: {
							columns: [ 0, 1, 2, 3 ]
						},
						customize: function ( win ) {
							$(win.document.body).find('table').addClass('printer');
						}
					}
				]
			} );	
			
			var sButton = fcn_create_button_datatable('dtpendientes_ent', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_entregados(true);"');
			$("div.div_digitalizados_refresh").html(sButton);
			
			oTableEntregadosGrid.on( 'order.dt search.dt', function () {
				oTableEntregadosGrid.DataTable().column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
					cell.innerHTML = i+1;
					oTableEntregadosGrid.DataTable().cell(cell).invalidate('dom');
				} )
			});
			
			oTableEntregadosGrid.on('click', 'a.editor_entregados_detalles', function (e) {
				try {				
					fcn_mostrar_modal_cuenta_gastos_detalles(this, oTableEntregadosGrid);
				} catch (err) {		
					var strMensaje = 'editor_entregados_detalles_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			__nCountRefresGrid = __nTimerSecondsRefresGrid;
		
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);
			
			var table = oTableEntregadosGrid.DataTable();
			if (bReloadPaging) {
				table.search('').ajax.reload(null, bReloadPaging);
			} else {
				table.ajax.reload(null, bReloadPaging);
			}
			setTimeout(function(){ oTableEntregadosGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_entregados() :: ' + err.message;
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
					"url": "ajax/expedientes/expCxP/postExpCxC_CuentaGastosDetalles.php",
					"type": "POST",
					"data": function ( d ) {
			            d.sIdEmpresa = __EmpresaId;
						d.sCuentaGastos = __strCuentaGastos;
						d.sReferencia = __strReferencia;
					}
				},
				columns: [ 
					{ data: "cuenta_gastos", className: "text-center"},	
					{ data: "referencia", className: "text-center"},	
					{ data: "pedimento", className: "text-center"},
					{ data: "fecha_cc_facturacion", className: "text-center"}				
				],
				select: true,
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
		} else {
			var table = oTableCuentaGastosDetallesGrid.DataTable();
			table.ajax.reload();
		}	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_cuenta_gastos_detalles() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_traficos() {
	try {
		if (oTableTraficosGrid == null) {
			oTableTraficosGrid = $('#dttraficos');
			oTableTraficosGrid.DataTable({
				scrollY:        "200px",
				scrollCollapse: true,
				bFilter: false, //Quitamos el filtro
				paging: false,  //Quitamos las paginas
			});
			
			$('#dttraficos tbody').on( 'click', 'a.editor_modal_entregar_trafico_delete', function () {
				oTableTraficosGrid.DataTable().row( $(this).parents('tr') ).remove().draw();
			} );
		} else {
			oTableTraficosGrid.DataTable().clear().draw();
		}
		
		setTimeout(function(){ oTableTraficosGrid.DataTable().columns.adjust().draw(); }, 250);	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_facturar() :: ' + err.message;
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

/* ..:: Refrescamos grids ::.. */
function fcn_refrescar_grids() {
	if ($('#idiv_pendientes').hasClass('active')) {
		fcn_cargar_grid_pendientes();
	} else if ($('#idiv_pendientes_ent').hasClass('active')) {
		fcn_cargar_grid_pendientes_ent();
	} else {
		__nCountRefresGrid = __nTimerSecondsRefresGrid;
	}
}

/* ..:: Mostramos la modal para entregar cuentas ::.. */
function fcn_mostrar_modal_entregar_cuentas() {
	try {	
		$('#modal_entregar').modal({ show: true });
		$('#itxt_modal_entregar_trafico').val('');
		$('#ibtn_modal_entregar_aceptar').show();
		$('#idiv_modal_entregar_mensaje').hide();
		
		setTimeout(function(){ 
			$("#itxt_modal_entregar_trafico").focus();
		}, 500);
	
		fcn_cargar_grid_traficos();		
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_entregar_cuentas() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Funcion para obtener el trafico por Pistola Codigo de Barras o Enter ::.. */
function fcn_modal_entregar_agregar_trafico() {
	try {
		$('#itxt_modal_entregar_trafico').attr('disabled', true);
				
		var aCode = $('#itxt_modal_entregar_trafico').val();
		aCode = aCode.toUpperCase();
		aCode = aCode.replace(/\¿/g, "=");
		aCode = aCode.replace(/\'/g, "-");

		$('#itxt_modal_entregar_trafico').val('');
			
		var sHtml = '';
		strValor = aCode.toUpperCase();
		if (strValor.trim()) {
			var bExisteValor = false;
			var table = oTableTraficosGrid.DataTable();
			jQuery.each(table.rows().nodes(), function(index, item) {
				var dataTableValor = table.row(index).data()[0];
				if (dataTableValor === strValor) {
					bExisteValor = true;
					return false;
				}
			});
			
			if (!bExisteValor) {
				bExisteValor = false;
				var table = oTablePendientesEntGrid.DataTable();
				jQuery.each(table.rows().nodes(), function(index, item) {
					var dataTableValor = table.row(index).data().referencia;
					if (dataTableValor === strValor) {
						bExisteValor = true;
						return false;
					}
				});
				
				if (bExisteValor) { 
					oTableTraficosGrid.DataTable().row.add([ 
						strValor,
						'<center><a href="#" class="editor_modal_entregar_trafico_delete"><i class="fa fa-check-circle" aria-hidden="true"></i> Eliminar</a></center>'
					]).draw(false);
				} else {
					sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
					sHtml += '    <span style="color:#FFF;">El Trafico no esta digitalizado!</span>';
					sHtml += '</div>';
				}
			} else {			
				sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
				sHtml += '    <span style="color:#FFF;">Trafico repetido!</span>';
				sHtml += '</div>';
			}		
		} else {
			sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
			sHtml += '    <span style="color:#FFF;">Trafico invalido!</span>';
			sHtml += '</div>';
		}
		
		//Mostramos mensaje de error
		if (sHtml.trim()) {
			var $MsjCtrl = $('#idiv_modal_entregar_mensaje');
			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			
			setTimeout(function () {
				$MsjCtrl.fadeOut();
			},5000);	
		}
		
		$('#itxt_modal_entregar_trafico').attr('disabled', false);
		$("#itxt_modal_entregar_trafico").focus();
	} catch (err) {		
		var strMensaje = 'fcn_modal_entregar_agregar_trafico() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Funcion para manejar las cookies ::.. */
function fcn_crear_cookies() {
	try {
		if (fcn_comprobarCookie('aAjaxTraficos1') == false) {
			fcn_crearCookie('aAjaxTraficos1', 'none', 2);
		}
		
		if (fcn_comprobarCookie('aAjaxTraficos2') == false) {
			fcn_crearCookie('aAjaxTraficos2', 'none', 2);
		}
		
		if (fcn_comprobarCookie('aAjaxTraficos3') == false) {
			fcn_crearCookie('aAjaxTraficos3', 'none', 2);
		}
	} catch (err) {		
		var strMensaje = 'fcn_crear_cookies() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Funcion para guardar nuevo registro en cookies ::.. */
function fcn_guardar_cookies(aTraficos) {
	try {
		fcn_crear_cookies();
		
		fcn_crearCookie('aAjaxTraficos3', fcn_obtenerCookie('aAjaxTraficos2'), 2);
		fcn_crearCookie('aAjaxTraficos2', fcn_obtenerCookie('aAjaxTraficos1'), 2);
		fcn_crearCookie('aAjaxTraficos1', JSON.stringify(aTraficos), 2);
	} catch (err) {		
		var strMensaje = 'fcn_guardar_cookies() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Funcion para obtener cookies y mostrarlos en grid ::.. */
function fcn_mostrar_cookies_grid(clave) {
	try {
		fcn_crear_cookies();
		aValue = fcn_obtenerCookie(clave);
		
		if (aValue != 'none') {
			aValue = JSON.parse(aValue);
			if ($.isArray(aValue)) {
				$.each(aValue, function( key, value ) {
					var bExisteValor = false;
					var table = oTableTraficosGrid.DataTable();
					jQuery.each(table.rows().nodes(), function(index, item) {
						var dataTableValor = table.row(index).data()[0];
						if (dataTableValor === value) {
							bExisteValor = true;
							return false;
						}
					});
			
					if (!bExisteValor) { 
						oTableTraficosGrid.DataTable().row.add([ 
							value,
							'<center><a href="#" class="editor_modal_entregar_trafico_delete"><i class="fa fa-check-circle" aria-hidden="true"></i> Eliminar</a></center>'
						]).draw(false);
					}
				});
			}
		}
	} catch (err) {		
		var strMensaje = 'fcn_guardar_cookies() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/************************************/
/* COOKIES */
/************************************/
function fcn_crearCookie(clave, valor, diasexpiracion) {
    var d = new Date();
    d.setTime(d.getTime() + (diasexpiracion*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = clave + "=" + valor + "; " + expires;
}

function fcn_obtenerCookie(clave) {
    var name = clave + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

function fcn_comprobarCookie(clave) {
    var clave = fcn_obtenerCookie(clave);
    if (clave!="") {
        // La cookie existe.
		return true;
    }else{
        // La cookie no existe.
		return false;
    }
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

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

/* ..:: ENTREGAR CUENTAS ::.. */
/* ..:: Agregamos la fecha_cp_entrega, estos pasan a entregados ::.. */
function ajax_set_upd_entregar_cuentas() {
	try {		
		if (oTableTraficosGrid.DataTable().row().length == 0) {
			var sHtml = '';
			sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
			sHtml += '    <span style="color:#FFF;">Debe agregar por lo menos un Folio Fiscal o Cuenta de Gastos!</span>';
			sHtml += '</div>';
			
			var $MsjCtrl = $('#idiv_modal_entregar_mensaje');
			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			
			setTimeout(function () {
				$MsjCtrl.fadeOut();
			},5000);	
		}
		
		var aTraficos = new Array();
		var table = oTableTraficosGrid.DataTable();
		jQuery.each(table.rows().nodes(), function(index, item) {
			aTraficos.push(table.row(index).data()[0]);
		});
	
		var oData = {
			aTraficos: JSON.stringify(aTraficos)
		};
		
		fcn_guardar_cookies(aTraficos);
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxP/ajax_set_upd_entregar_cuentas.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Entregando cuentas, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#ibtn_modal_entregar_aceptar').hide();
						//show_label_ok(respuesta.Mensaje);
						
						$('#ili_pendientes_ent').removeClass( "active" );
						$('#idiv_pendientes_ent').removeClass( "active" );
						
						$('#ili_entregados').addClass( "active" );
						$('#idiv_entregados').addClass( "active" );
	
						$('#modal_entregar').modal('hide');
						setTimeout(function () { ajax_get_entregados_date_data(respuesta.Fecha); },500);	
						
						/*ajax_get_grid_pendientes_data();
						
						var sMensaje = respuesta.Mensaje + '. Registros actualizados [' + respuesta.nRowsUpdate + ']';				
						if (respuesta.nRowsInsert) {
							sMensaje += ' Registros Insertados [' + respuesta.nRowsInsert + '].';
						}							
						
						var sHtml = '';
						sHtml += '<div class="alert alert-success" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + sMensaje + '!</span>';
						sHtml += '</div>';
						
						var $MsjCtrl = $('#idiv_modal_facturar_mensaje');
						$MsjCtrl.html(sHtml);
						$MsjCtrl.fadeIn();
						
						if (respuesta.nRowsPuertosInsert > 0) { 
							setTimeout(function () { 
								var strMensaje = 'Las siguientes cuentas de gastos pertenecen a puertos: [' + respuesta.nRowsPuertosInsertCuentas + ']';
								show_label_ok(strMensaje);
							},1000);							
						}*/
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
		var strMensaje = 'ajax_set_upd_facturar() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: CUENTAS ENTREGADAS ::.. */
function ajax_get_entregados_date_data(dtFecha = '') {
	try {		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxP/ajax_get_entregados_date_data.php',
		
            beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#isel_entregados_fechas').html(respuesta.Fechas);	
						
						if (respuesta.Fechas != '') {
							if (dtFecha == '') {
								$("#isel_entregados_fechas").val($("#isel_entregados_fechas option:first").val());
							} else {
								$("#isel_entregados_fechas").val(dtFecha);
							}
							
							fcn_cargar_grid_entregados();
						}
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
		var strMensaje = 'ajax_get_entregados_date_data() :: ' + err.message;
		show_label_error(strMensaje);
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