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
var oTableRecibirPedimentosGrid = null;
var oTableFacturarGrid = null;

var oTablePendEntregarGrid = null;
var oTablePendEntregarCuentasGrid = null;
var oTablePendEntregarDetallesGrid = null;

var oTableEntregadosGrid = null;

var __nIdRegistro = 0;

var __nEnters = 0;
var __sPatente = '';
var __sPedimento = '';
var __sClavePed = '';
var __sRFC = '';

var __EmpresaId;
var __EmpresaNombre;
var __EmpresaRutaDatos;
var __EmpresaRutaCasa;

var __strFolioFiscal;
var __strCuentaGastos;
var __strReferencia;
var __strFechaEntrega;

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
		$('[data-toggle="tooltip"]').tooltip();
		$('#itxt_modal_facturar_cuenta_banco, #itxt_modal_servicios_prestados_banco, #itxt_modal_adicionales_banco, #itxt_modal_cuenta_gastos_banco').numeric();	
		$('#itxt_modal_facturar_cuenta_no_mov, #itxt_modal_servicios_prestados_no_mov, #itxt_modal_adicionales_no_mov, #itxt_modal_cuenta_gastos_no_mov').numeric();
		$('#itxt_modal_servicios_prestados_pedimento').numeric();

		$('#itxt_modal_adicionales_aduana, #itxt_modal_adicionales_patente, #itxt_modal_adicionales_pedimento').numeric();
		
		
		//$('#itxt_modal_edit_comentario_click_comentarios').val().toUpperCase();
		//$('#itxt_modal_edit_comentario_click_comentarios').addClass('uppercase');
		
		fcn_mostrar_empresas();

		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var target = $(e.target).attr("href") // activated tab
			
			switch(target) {
				case '#idiv_pendientes':
					ajax_get_grid_pendientes_data();
					break;
					
				case '#idiv_pend_entregar':
					fcn_cargar_grid_pendentregar();
					break;
					
				case '#idiv_entregados':
					fcn_cargar_grid_entregados();
					//ajax_get_entregados_date_data();
					break;	
			}
		});
		
		$('#itxt_modal_recive_pedimento').on("keypress", function(e) {
			if (e.keyCode == 13) {
				__nEnters += 1;
				
				if (__nEnters >= 9) {
					__nEnters = 0;
					$('#itxt_modal_recive_pedimento').attr('disabled', true);
					setTimeout(function(){ 
						var aCode = $('#itxt_modal_recive_pedimento').val().split("\n");
						__sPatente = aCode[0].trim();
						__sPedimento = aCode[1].trim();
						__sClavePed = aCode[2].trim();
						__sRFC = aCode[3].trim();
						
						ajax_set_upd_recibir_pedimento_codigo(__sPedimento);
						//ajax_set_add_pedimento_codigo();
						//alert(__sPatente + ' ' + __sPedimento + ' ' + __sClavePed + ' ' + __sRFC);
					}, 500);					
				}
			}
		});
		
		$('#itxt_modal_facturar_uuid_uuid').on("keypress", function(e) {
			if (e.keyCode == 13) {
				fcn_modal_facturar_agregar_folio_fiscal();
			}
		});
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
	oTableEmpresasGrid = $('#dtempresas');
		
    oTableEmpresasGrid.DataTable({
		order: [[0, 'desc']],
        processing: true,
        serverSide: true,
        ajax: {
            "url": "ajax/expedientes/expCxC/postExpCxC_Empresas.php",
            "type": "POST"
        },
        columns: [ 
            { "data": "nombre", "className": "def_app_left"},		
            {
                data: null,
                className: "def_app_center",
                defaultContent: '<a href="#" class="editor_empresa_select"><i class="fa fa-check-circle" aria-hidden="true"></i> Seleccionar</a>'
            }
        ],
		//select: true,
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
	
	oTableEmpresasGrid.on('click', 'a.editor_empresa_select', function (e) {
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
			
			fcn_mostrar_panel();
		} catch (err) {		
			var strMensaje = 'editor_empresa_click() :: ' + err.message;
			show_label_error(strMensaje);
		}  
    } );
}

function fcn_cargar_grid_pendientes() {
	try {
		if (oTablePendientesGrid == null) {	
			oTablePendientesGrid = $('#dtpendientes');
			
			oTablePendientesGrid.DataTable({
				order: [[0, 'asc']],
				processing: true,
        		serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expCxC/postExpCxC_Pendientes.php",
					"type": "POST",
					"data": function ( d ) {
			            d.sIdEmpresa = __EmpresaId;
					}
				},
				columns: [ 
					{ "data": "referencia_saaio", "className": "def_app_left"},
					{ "data": "pedimento", "className": "def_app_left"},		
					{
						data:  "comentarios",
						render: function ( data, type, row ) {
							if (data == "") {
								return '<a href="#" class="editor_pendientes_editar_comentario"><i class="fa fa-comment-o" aria-hidden="true"></i> Agregar Comentario</a>';
							} else {
								return '<a href="#" class="editor_pendientes_editar_comentario"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Editar</a> ' + data;
							}
						},
						className: "def_app_left"
					}
				],	
				select: true,	
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
						title: '<h2>Pedimentos Pendientes</h2>'
					}
				]
			} );	
			
			var sButton = fcn_create_button_datatable('dtpendientes', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_pendientes_data();"');
			$("div.div_pendientes_refresh").html(sButton);
			
			oTablePendientesGrid.on('click', 'a.editor_pendientes_editar_comentario', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTablePendientesGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__nIdRegistro = oData.id_registro;
					fcn_mostrar_comentarios(oData.comentarios);
				} catch (err) {		
					var strMensaje = 'editor_recive_pedimento_click() :: ' + err.message;
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

function fcn_cargar_grid_recibir_pedimentos() {
	try {
		if (oTableRecibirPedimentosGrid == null) {			
			oTableRecibirPedimentosGrid = $('#dtrec_ped_pendientes');
			
			oTableRecibirPedimentosGrid.DataTable({
				order: [[1, 'asc']],
				processing: true,
        		serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expCxC/postExpCxC_RecivePedime.php",
					"type": "POST"
				},
				columns: [ 
					{
						data:  null,
						render: function ( data, type, row ) {
							return '<center><a href="#" class="editor_recive_pedimento"><i class="fa fa-check-circle" aria-hidden="true"></i> Recibir</a></center>';
						},
						className: "def_app_center"
					},
					{ "data": "referencia_saaio", "className": "def_app_left"},
					{ "data": "pedimento", "className": "def_app_left"},		
					{ "data": "comentarios", "className": "def_app_left"}
				],		
				responsive: true,
				scrollY:        "200px",
				scrollCollapse: true,
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
					 "<'row'<'col-sm-6'<'div_recibir_todos'>><'col-sm-6'>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>"
			} );
			
			oTableRecibirPedimentosGrid.on('click', 'a.editor_recive_pedimento', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableRecibirPedimentosGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					var sIdRegistro = oData.id_registro;
					var aRegistros = new Array();
					aRegistros.push(sIdRegistro);
					
					ajax_set_upd_recibir_pedimento_click(aRegistros);
				} catch (err) {		
					var strMensaje = 'editor_recive_pedimento_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
			
			var sButton = fcn_create_button_datatable('dtrec_ped_pendientes', '<i class="fa fa-check-square-o" aria-hidden="true"></i> Recibir Todos', 'onClick="javascript:fcn_modal_recibir_pedimentos_todos();"', 'pull-left');
			$("div.div_recibir_todos").html(sButton);
		} else {
			ajax_get_grid_recibir_pedimentos_data();
		}	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_recibir_pedimentos() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_facturar() {
	try {
		if (oTableFacturarGrid == null) {
			oTableFacturarGrid = $('#dtfacturar');
			oTableFacturarGrid.DataTable({
				scrollY:        "200px",
				scrollCollapse: true,
				bFilter: false, //Quitamos el filtro
				paging: false,  //Quitamos las paginas
			});
			
			$('#dtfacturar tbody').on( 'click', 'a.editor_modal_facturar_registro_delete', function () {
				oTableFacturarGrid.DataTable().row( $(this).parents('tr') ).remove().draw();
			} );
		} else {
			oTableFacturarGrid.DataTable().clear().draw();
		}
		
		setTimeout(function(){ oTableFacturarGrid.DataTable().columns.adjust().draw(); }, 250);	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_facturar() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_pendentregar() {
	try {
		if (oTablePendEntregarGrid == null) {
			oTablePendEntregarGrid = $('#dtpendentregar');
			
			oTablePendEntregarGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expCxC/postExpCxC_PendientesEntregar.php",
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
						defaultContent: '<a href="#" class="editor_pendentregar_detalles"><i class="fa fa-eye" aria-hidden="true"></i> Ver Detalles</a>'
					}					
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
					 "<'row'<'col-sm-8'B><'col-sm-4'<'div_pend_entregar_refresh'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'print',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Cuentas Pendientes por Entregar</h2>',
						exportOptions: {
							columns: [ 0, 1 ]
						}
					}
				]
			});
			
			var sButton = fcn_create_button_datatable('dtpendentregar', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_pendentregar_data();"');
			$("div.div_pend_entregar_refresh").html(sButton);
			
			oTablePendEntregarGrid.on('click', 'a.editor_pendentregar_detalles', function (e) {
				try {				
					fcn_mostrar_modal_cuenta_gastos_detalles(this, oTablePendEntregarGrid);
				} catch (err) {		
					var strMensaje = 'editor_edit_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			ajax_get_grid_pendentregar_data();
		}	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pendentregar() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_entregar_cuentas() {
	try {
		if (oTablePendEntregarCuentasGrid == null) {
			oTablePendEntregarCuentasGrid = $('#dtentregar_cuenta_gastos');
			
			oTablePendEntregarCuentasGrid.DataTable({
				order: [[1, 'asc']],
				processing: true,
				ajax: {
					"url": "ajax/expedientes/expCxC/postExpCxC_PendientesEntregar.php",
					"type": "POST",
					"data": function ( d ) {
			            d.sIdEmpresa = __EmpresaId;
					}
				},				
				columnDefs: [
					{
						'targets': 0,
						'checkboxes': {
							'selectRow': true
						}
					}
				],		
				columns: [ 
					{
						data: null
					},
					{ data: "cuenta_gastos", className: "def_app_center"},	
					{ data: "referencia", className: "def_app_center"},
					{
						data: null,
						className: "def_app_center",
						defaultContent: '<a href="#" class="editor_entregar_cuentas_detalles"><i class="fa fa-eye" aria-hidden="true"></i> Ver Detalles</a>'
					}
				],		
				/*select: {
					style: 'multi'
				},*/
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
					 "<'row'<'col-sm-2'>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>"
			} );
			
			oTablePendEntregarCuentasGrid.on('click', 'a.editor_entregar_cuentas_detalles', function (e) {
				try {				
					fcn_mostrar_modal_cuenta_gastos_detalles(this, oTablePendEntregarCuentasGrid);
				} catch (err) {		
					var strMensaje = 'editor_edit_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			oTablePendEntregarCuentasGrid.DataTable().column(0).checkboxes.deselect();
			oTablePendEntregarCuentasGrid.DataTable().clear().draw();
			ajax_get_grid_entregar_cuentas_data();
		}	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_entregar_cuentas() :: ' + err.message;
		show_label_error(strMensaje);
    }	
}

function fcn_cargar_grid_cuenta_gastos_detalles() {
	try {
		if (oTablePendEntregarDetallesGrid == null) {
			oTablePendEntregarDetallesGrid = $('#dtcuenta_gastos_detalles');
			
			oTablePendEntregarDetallesGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expCxC/postExpCxC_PendientesEntregarDetalles.php",
					"type": "POST",
					"data": function ( d ) {
			            d.sIdEmpresa = __EmpresaId;
						d.sCuentaGastos = __strCuentaGastos;
						d.sReferencia = __strReferencia;
						d.sFecha = __strFechaEntrega;
					}
				},
				columns: [ 
					{ data: "cuenta_gastos", className: "def_app_center"},	
					{ data: "referencia", className: "def_app_center"},	
					{ data: "pedimento", className: "def_app_center"},
					{ data: "fecha_cc_facturacion", className: "def_app_center"}				
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
			ajax_get_grid_cuenta_gastos_detalles_data();
		}	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_cuenta_gastos_detalles() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_entregados() {
	try {
		if (oTableEntregadosGrid == null) {
			oTableEntregadosGrid = $('#dtentregados');
			
			oTableEntregadosGrid.DataTable({
				order: [[2, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expCxC/postExpCxC_Entregados.php",
					"type": "POST"/*,
					"data": function ( d ) {
						d.sFecha = $('#isel_entregados_fechas').val();
					}*/
				},
				columns: [ 
					{ "data": "cuenta_gastos", "className": "def_app_center"},
					{ "data": "referencia", "className": "def_app_center"},			
					{ "data": "fecha_cc_entrega", "className": "def_app_center"}  ,
					{
						data: null,
						className: "def_app_center",
						defaultContent: '<a href="#" class="editor_entregados_detalles"><i class="fa fa-eye" aria-hidden="true"></i> Ver Detalles</a>'
					}					
				],
				select: true,
				responsive: true,
				aLengthMenu: [
					[10, 25, 50, 100, 200, -1],
					[10, 25, 50, 100, 200, "All"]
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
					 "<'row'<'col-xs-8'B><'col-xs-4'<'div_entregados_refresh'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Cuentas Entregadas</h2>',
						exportOptions: {
							columns: [ 0, 1, 2 ]
						}
					}
				]
			});
			
			var sButton = fcn_create_button_datatable('dtentregados', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_entregados_data();"');
			$("div.div_entregados_refresh").html(sButton);
			
			oTableEntregadosGrid.on('click', 'a.editor_entregados_detalles', function (e) {
				try {				
				    var dtFecha = $('#isel_entregados_fechas').val();
					fcn_mostrar_modal_cuenta_gastos_detalles(this, oTableEntregadosGrid, dtFecha);
				} catch (err) {		
					var strMensaje = 'editor_edit_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			ajax_get_grid_entregados_data();
		}	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pendentregar() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

/* ..:: Creamos botones para el datatables ::.. */
function fcn_create_button_datatable(sAriaControls, sBtnTxt, oFunction = '', sClass = 'pull-right') {
	var sHtml = '';
	
	sHtml += '<a class="btn btn-default buttons-selected-single ' + sClass + '"';
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
	
	if (oTableEmpresasGrid == null) {
		fcn_cargar_grid_empresas();
	} else {
		ajax_get_grid_empresas_data();
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

/* ..:: Mostramos el panel despues de seleccionar una empresa ::.. */
function fcn_mostrar_panel() {
	$('#modal_select_empresa').modal('hide');
	//$('#idiv_panel_principal').show();
	
	//var sHtml = '<a href="#"><i class="fa fa-exchange" aria-hidden="true"></i> Cambiar</a>';
	var sHtml = '';
	sHtml += '<i class="fa fa-industry" aria-hidden="true"></i>' + ' ';
	sHtml += '<strong>EMPRESA SELECCIONADA:</strong>' + ' [' + __EmpresaNombre + ']'+ ' ';
	sHtml += '<button type="button" class="btn btn-warning btn-xs" onclick="fcn_mostrar_empresas();"><i class="fa fa-exchange" aria-hidden="true"></i> Cambiar</button>';
	$('#istr_trabajando_empresa').html(sHtml);	
	
	$('#ili_pendientes').removeClass( "active" );
	$('#ili_pend_entregar').removeClass( "active" );
	$('#ili_entregados').removeClass( "active" );
	$('#idiv_pendientes').removeClass( "active" );
	$('#idiv_pend_entregar').removeClass( "active" );
	$('#idiv_entregados').removeClass( "active" );
	
	$('#ili_pendientes').addClass( "active" );
	$('#idiv_pendientes').addClass( "active" );
	
	$('#isel_entregados_fechas').html('');
	
	fcn_cargar_grid_pendientes();
}

/* ..:: Mostramos la modal para seleccionar los pedimentos que va a recibir ::.. */
function fcn_mostrar_modal_recibir_pedimentos() {
	__nEnters = 0;
	$('#itxt_modal_recive_pedimento').val('');
	
	$('#itxt_modal_recive_pedimento').attr('disabled', false);
	setTimeout(function(){ 
		$("#itxt_modal_recive_pedimento").focus();
	}, 500);
	
	$('#modal_recibir_pedimentos').modal({
		show: true
	});
	
	fcn_cargar_grid_recibir_pedimentos();
}

/* ..:: Recibir todos ::.. */
function fcn_modal_recibir_pedimentos_todos() {
	try {
		var aRegistros = new Array();					
		var table = oTableRecibirPedimentosGrid.DataTable();
		
		jQuery.each(table.rows().nodes(), function(index, item) {
			var oRow_IdRegistro = table.row(index).data().id_registro;
			aRegistros.push(oRow_IdRegistro);			
		});
		
		ajax_set_upd_recibir_pedimento_click(aRegistros);
	} catch (err) {		
		var strMensaje = 'fcn_modal_recibir_pedimentos_todos() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}


/* ..:: Mostramos la modal para facturar (Con folio fiscal o cuenta de gastos) ::.. */
function fcn_mostrar_modal_facturar() {
	try {	
		$('#modal_facturar').modal({ show: true });
		$('#itxt_modal_facturar_uuid_uuid').val('');
		$('#itxt_modal_facturar_cuenta_banco').val('');
		$('#itxt_modal_facturar_cuenta_no_mov').val('');
		$('#ibtn_modal_facturar_aceptar').show();
		$('#idiv_modal_facturar_mensaje').hide();
		
		setTimeout(function(){ 
			$("#itxt_modal_facturar_uuid_uuid").focus();
		}, 500);
	
		// $.mask.definitions['h'] = "[ireIRE]";
		// $('#itxt_modal_facturar_cuenta_cuenta').mask("h-9-99999");
		
		fcn_cargar_grid_facturar();		
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_facturar() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Funcion para obtener el folio fiscal por Pistola Codigo de Barras o Enter ::.. */
function fcn_modal_facturar_agregar_folio_fiscal() {
	try {
		$('#itxt_modal_facturar_uuid_uuid').attr('disabled', true);
				
		var aCode = $('#itxt_modal_facturar_uuid_uuid').val();
		aCode = aCode.toUpperCase();
		aCode = aCode.replace(/\¿/g, "=");
		aCode = aCode.replace(/\'/g, "-");

		if(aCode.indexOf('ID=') != -1){
			var start_pos = aCode.indexOf('ID=') + 3;
			var end_pos = aCode.length;
			if(aCode.indexOf('FE=') != -1){
				end_pos = aCode.indexOf('FE=') - 1;
			}
			__strFolioFiscal = aCode.substring(start_pos, end_pos);
		} else {
			__strFolioFiscal = aCode;
		}
		
		$('#itxt_modal_facturar_uuid_uuid').val('');
						
		fcn_modal_facturar_agregar_cuenta('folio_fiscal');
		
		$('#itxt_modal_facturar_uuid_uuid').attr('disabled', false);
		$("#itxt_modal_facturar_uuid_uuid").focus();
	} catch (err) {		
		var strMensaje = 'fcn_modal_facturar_agregar_folio_fiscal() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Agregamos la cuenta de gastos o UUID al grid ::.. */
function fcn_modal_facturar_agregar_cuenta(pOpt) {
	try {	
		var sHtml = '';
		var strValor;
		
		switch (pOpt) {
			case 'folio_fiscal':
				var aFolioFiscal = __strFolioFiscal.split('-');
				if (aFolioFiscal.length != 5) {
					sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
					sHtml += '    <span style="color:#FFF;">Folio Fiscal incorrecto!</span>';
					sHtml += '</div>';
				}
				
				strValor = __strFolioFiscal;
				break;
			case 'cuenta_gastos':
				
				var sTipoMov = $('#isel_modal_facturar_cuenta_tipo_mov').val();
				var sBanco = $('#itxt_modal_facturar_cuenta_banco').val().trim();
				var sNoMov = $('#itxt_modal_facturar_cuenta_no_mov').val().trim();
				
				if (sBanco == '' || sNoMov == '') {
					sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
					sHtml += '    <span style="color:#FFF;">Numero de cuenta invalido!</span>';
					sHtml += '</div>';
				}
				
				strValor = sTipoMov + '-' + sBanco + '-' + sNoMov; //strValor = $('#itxt_modal_facturar_cuenta_cuenta').val();
				
				$('#itxt_modal_facturar_cuenta_banco').val('');
				$('#itxt_modal_facturar_cuenta_no_mov').val('');
				break;
		}
		
		if (sHtml == '') {
			strValor = strValor.toUpperCase();
			if (strValor.trim()) {
				var bExisteValor = false;
				var table = oTableFacturarGrid.DataTable();
				jQuery.each(table.rows().nodes(), function(index, item) {
					var dataTableValor = table.row(index).data()[0];
					if (dataTableValor === strValor) {
						bExisteValor = true;
						return false;
					}
				});
				
				if (!bExisteValor) {
					oTableFacturarGrid.DataTable().row.add([ 
						strValor,
						'<center><a href="#" class="editor_modal_facturar_registro_delete"><i class="fa fa-check-circle" aria-hidden="true"></i> Eliminar</a></center>'
					]).draw(false);
				} else {			
					sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
					sHtml += '    <span style="color:#FFF;">Folio Fiscal o Cuenta de gastos repetida!</span>';
					sHtml += '</div>';
				}		
			} else {
				sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
				sHtml += '    <span style="color:#FFF;">Folio Fiscal o Cuenta de gastos invalido!</span>';
				sHtml += '</div>';
			}
		}			
		
		//Mostramos mensaje de error
		if (sHtml.trim()) {
			var $MsjCtrl = $('#idiv_modal_facturar_mensaje');
			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			
			setTimeout(function () {
				$MsjCtrl.fadeOut();
			},5000);	
		}
		
		$('#itxt_modal_facturar_uuid_uuid').val('');
		$('#itxt_modal_facturar_cuenta_cuenta').val('');
	} catch (err) {		
		var strMensaje = 'fcn_modal_facturar_agregar_cuenta() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Verificamos que haya datos en el grid para facturar ::.. */
function fcn_modal_facturar() {
	try {	
		if (oTableFacturarGrid.DataTable().row().length > 0) {
			var aCuentas = new Array();
			var table = oTableFacturarGrid.DataTable();
			jQuery.each(table.rows().nodes(), function(index, item) {
				aCuentas.push(table.row(index).data()[0]);
			});
			
			ajax_set_upd_facturar(aCuentas);
		} else {
			var sHtml = '';
			sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
			sHtml += '    <span style="color:#FFF;">Debe agregar por lo menos un Folio Fiscal o Cuenta de Gastos!</span>';
			sHtml += '</div>';
			
			var $MsjCtrl = $('#idiv_modal_facturar_mensaje');
			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			
			setTimeout(function () {
				$MsjCtrl.fadeOut();
			},5000);	
		}
	} catch (err) {		
		var strMensaje = 'fcn_modal_facturar() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Mostramos la modal para facturar (Con folio fiscal o cuenta de gastos) ::.. */
function fcn_mostrar_modal_entregar_cuenta_gastos() {
	try {	
		$('#modal_entregar_cuenta_gastos').modal({ show: true });
		
		fcn_cargar_grid_entregar_cuentas();
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_facturar() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/* ..:: Obtenemos los pedimentos seleccionados ::.. */
function fcn_entregar_cuenta_gastos_elementos_seleccionados() {
	var rows_selected = oTablePendEntregarCuentasGrid.DataTable().column(0).checkboxes.selected();
	var aSelected = new Array();
	$.each(rows_selected, function(index, rowId){
		aSelected.push({
			cuenta_gastos: rowId.cuenta_gastos,
			referencia: rowId.referencia
		});
    });
	
	if (aSelected.length > 0) {
		ajax_set_upd_entregar_cuenta_gastos(aSelected);
	} else {
		var sHtml = '';
		sHtml += '<div class="alert alert-danger">';
		sHtml += '    <span style="color:#FFF;">Debe seleccionar por lo menos una Cuenta de Gastos.</span>';
		sHtml += '</div>';
		
		if (sHtml.trim()) {
			var $MsjCtrl = $('#idiv_entregar_cuenta_gastos_mensaje');
			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			
			setTimeout(function () {
				$MsjCtrl.fadeOut();
			},4000);	
		}
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
		
		__strCuentaGastos = oData.cuenta_gastos;
		__strReferencia = oData.referencia;
		__strFechaEntrega = dtFecha;
					
		$('#modal_cuenta_gastos_detalles').modal({ show: true });
		
		fcn_cargar_grid_cuenta_gastos_detalles();
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_cuenta_gastos_detalles() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/***********************************************************************/
/* ..:: SERVICIOS PRESTADOS ::.. */

/* ..:: Mostramos la modal para servicios prestados ::.. */
function fcn_mostrar_modal_servicios_prestados() {
	try {	
		$('#modal_servicios_prestados').modal({ show: true });
		
		$('#ibtn_servicios_prestados_agregar').show();
		$('#idiv_servicios_prestados_mensaje').empty().hide();
		$('#itxt_modal_servicios_prestados_pedimento').val('');
		$('#itxt_modal_servicios_prestados_cve_ped').val('');
		$('#itxt_modal_servicios_prestados_operacion').val('1');
		$('#itxt_modal_servicios_prestados_comentarios').val('')
		$('#isel_modal_servicios_prestados_tipo_mov').val('I');
		$('#itxt_modal_servicios_prestados_banco').val('');
		$('#itxt_modal_servicios_prestados_no_mov').val('');
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_servicios_prestados() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/***********************************************************************/
/* ..:: CUENTAS ADICIONALES ::.. */

/* ..:: Mostramos la modal para cuentas adicionales ::.. */
function fcn_mostrar_modal_adicionales() {
	try {	
		$('#modal_adicionales').modal({ show: true });
		
		$('#ibtn_adicionales_agregar').show();
		$('#idiv_adicionales_mensaje').empty().hide();
		$('#itxt_modal_adicionales_aduana').val('');
		$('#itxt_modal_adicionales_patente').val('');
		$('#itxt_modal_adicionales_pedimento').val('');
		$('#isel_modal_adicionales_tipo_mov').val('I');
		$('#itxt_modal_adicionales_banco').val('');
		$('#itxt_modal_adicionales_no_mov').val('');
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_adicionales() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
}

/***********************************************************************/
/* ..:: CUENTAS DE GASTOS (sin pedimento) ::.. */

/* ..:: Mostramos la modal para cuentas gastos sin pedimento ::.. */
function fcn_mostrar_modal_cuenta_gastos() {
	try {	
		$('#modal_cuenta_gastos').modal({ show: true });
		
		$('#ibtn_cuenta_gastos_agregar').show();
		$('#idiv_cuenta_gastos_mensaje').empty().hide();
		
		$('#isel_modal_cuenta_gastos_tipo_mov').val('I');
		$('#itxt_modal_cuenta_gastos_banco').val('');
		$('#itxt_modal_cuenta_gastos_no_mov').val('');
		$('#itxt_modal_cuenta_gastos_comentarios').val('');
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_cuenta_gastos() :: ' + err.message;
		show_label_error(strMensaje, false);
    } 
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
		var table = oTablePendientesGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_pendientes_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_recibir_pedimentos_data() {
	try {	
		var table = oTableRecibirPedimentosGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_recibir_pedimentos_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_pendentregar_data() {
	try {	
		var table = oTablePendEntregarGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_pendentregar_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_entregar_cuentas_data() {
	try {	
		var table = oTablePendEntregarCuentasGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_entregar_cuentas_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_entregados_data() {
	try {	
		var table = oTableEntregadosGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_entregados_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_cuenta_gastos_detalles_data() {
	try {	
		var table = oTablePendEntregarDetallesGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_cuenta_gastos_detalles_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}


/* ..:: PENDIENTES DE FACTURAR ::.. */
/* ..:: Recibimos pedimentos, agregamos la fecha de recepcion opcion click ::.. */
function ajax_set_upd_recibir_pedimento_click(aRegistros) {
	try {		
		var oData = {	
			sIdEmpresa: __EmpresaId,		
			aRegistros: JSON.stringify(aRegistros)
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxC/ajax_set_upd_recibir_pedimento_click.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Actualizando pedimentos, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						ajax_get_grid_pendientes_data();
						fcn_cargar_grid_recibir_pedimentos();
						
						var sHtml = '';
						sHtml += '<div class="alert alert-success" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '</span>';
						sHtml += '</div>';
						
						if (sHtml.trim()) {
							var $MsjCtrl = $('#idiv_modal_recibir_pedime_mensaje');
							$MsjCtrl.html(sHtml);
							$MsjCtrl.fadeIn();
							
							setTimeout(function () {
								$MsjCtrl.fadeOut();
							},4000);	
						}
						$('#itxt_modal_recive_pedimento').focus();
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						
						var sHtml = '';
						sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + strMensaje + '</span>';
						sHtml += '</div>';
						
						if (sHtml.trim()) {
							var $MsjCtrl = $('#idiv_modal_recibir_pedime_mensaje');
							$MsjCtrl.html(sHtml);
							$MsjCtrl.fadeIn();
							
							setTimeout(function () {
								$MsjCtrl.fadeOut();
							},4000);	
						}
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
		var strMensaje = 'ajax_set_upd_recibir_pedimento_click() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: Recibimos pedimentos, agregamos la fecha de recepcion opcion codigo ::.. */
function ajax_set_upd_recibir_pedimento_codigo(sPedimento) {
	try {		
		var oData = {	
			sIdEmpresa: __EmpresaId,		
			sPedimento: sPedimento
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxC/ajax_set_upd_recibir_pedimento_codigo.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Actualizando pedimentos, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);					
					$('#itxt_modal_recive_pedimento').attr('disabled', false);
					$('#itxt_modal_recive_pedimento').val('');
					$('#itxt_modal_recive_pedimento').focus();
					if (respuesta.Codigo == '1'){
						ajax_get_grid_pendientes_data();
						fcn_cargar_grid_recibir_pedimentos();
						
						var sHtml = '';
						sHtml += '<div class="alert alert-success" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '</span>';
						sHtml += '</div>';
						
						if (sHtml.trim()) {
							var $MsjCtrl = $('#idiv_modal_recibir_pedime_mensaje');
							$MsjCtrl.html(sHtml);
							$MsjCtrl.fadeIn();
							
							setTimeout(function () {
								$MsjCtrl.fadeOut();
							},4000);	
						}
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						
						var sHtml = '';
						sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + strMensaje + '</span>';
						sHtml += '</div>';
						
						if (sHtml.trim()) {
							var $MsjCtrl = $('#idiv_modal_recibir_pedime_mensaje');
							$MsjCtrl.html(sHtml);
							$MsjCtrl.fadeIn();
							
							setTimeout(function () {
								$MsjCtrl.fadeOut();
							},4000);	
						}
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
		var strMensaje = 'ajax_set_upd_recibir_pedimento_click() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: Agregamos la fecha_cc_facturacion, estos pasan a pendientes por entregar ::.. */
function ajax_set_upd_facturar(aCuentas) {
	try {		
		var oData = {			
			sIdEmpresa: __EmpresaId,
			sRutaDatos: __EmpresaRutaDatos,
			aCuentas: JSON.stringify(aCuentas)
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxC/ajax_set_upd_facturar.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Asignando cuenta de gastos, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						ajax_get_grid_pendientes_data();
						$('#ibtn_modal_facturar_aceptar').hide();
						
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
						
						/*if (respuesta.nRowsUpdate > 0) {
							setTimeout(function () {
								$('#ili_pendientes').removeClass( "active" );
								$('#idiv_pendientes').removeClass( "active" );
								
								$('#ili_entregados').addClass( "active" );
								$('#idiv_entregados').addClass( "active" );
			
								$('#modal_facturar').modal('hide');
								setTimeout(function () { ajax_get_entregados_date_data(respuesta.Fecha); },500);
							}, 1500);
						} else {
							setTimeout(function () {
								$('#modal_facturar').modal('hide');
							}, 2500);
						}*/
						
						if (respuesta.nRowsPuertosInsert > 0) { 
							setTimeout(function () { 
								var strMensaje = 'Las siguientes cuentas de gastos pertenecen a puertos: [' + respuesta.nRowsPuertosInsertCuentas + ']';
								show_label_ok(strMensaje);
							},1000);							
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
		var strMensaje = 'ajax_set_upd_facturar() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: Editar comentario ::.. */
function ajax_set_upd_comentario() {
	try {		
		var sComentarios = $('#itxt_modal_edit_comentario_click_comentarios').val().toUpperCase();
	
		// if (!sComentarios.trim()) {
			// var sHtml = '';
			// sHtml += '<div class="alert alert-danger">';
			// sHtml += '    <span style="color:#FFF;">Debes ingresar un comentario!</span>';
			// sHtml += '</div>';
			
			// if (sHtml.trim()) {
				// var $MsjCtrl = $('#idiv_modal_edit_comentario_click_mensaje');
				// $MsjCtrl.html(sHtml);
				// $MsjCtrl.fadeIn();
				
				// setTimeout(function () {
					// $MsjCtrl.fadeOut();
				// },4000);	
			// }
			// return;
		// }
		
		var oData = {			
			nIdRegistro: __nIdRegistro,
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
						ajax_get_grid_pendientes_data();
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


/* ..:: PENDIENTES POR ENTREGAR ::.. */
function ajax_set_upd_entregar_cuenta_gastos(aSelected) {
	try {		
		var oData = {	
			sIdEmpresa: __EmpresaId,		
			aSelected: JSON.stringify(aSelected)
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxC/ajax_set_upd_entregar_cuenta_gastos.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Entregando cuentas, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						//alert(respuesta.aSelected);
						$('#ili_pendientes').removeClass( "active" );
						$('#ili_pend_entregar').removeClass( "active" );
						$('#idiv_pendientes').removeClass( "active" );
						$('#idiv_pend_entregar').removeClass( "active" );
						
						$('#ili_entregados').addClass( "active" );
						$('#idiv_entregados').addClass( "active" );
	
						$('#modal_entregar_cuenta_gastos').modal('hide');
						setTimeout(function () { ajax_get_entregados_date_data(respuesta.Fecha); },500);						
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
		var strMensaje = 'ajax_set_upd_entregar_cuenta_gastos() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}


/* ..:: PEDIMENTOS ENTREGADOS ::.. */
function ajax_get_entregados_date_data(dtFecha = '') {
	try {	
		var oData = {	
			sIdEmpresa: __EmpresaId
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxC/ajax_get_entregados_date_data.php',
			data: oData,
		
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
						}
						fcn_cargar_grid_entregados();
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

/* ..:: SERVICIOS PRESTADOS ::.. */
/* ..:: Agregamos un nuevo servicio prestado ::.. */
function ajax_set_servicio_prestado() {
	try {		
		$MsjCtrl = $('#idiv_servicios_prestados_mensaje');
		$MsjCtrl.empty().hide();

		var sPedimento = $('#itxt_modal_servicios_prestados_pedimento').val();
		var sCvePedimento = $('#itxt_modal_servicios_prestados_cve_ped').val().toUpperCase();
		var sOperacion = $('#itxt_modal_servicios_prestados_operacion').val();
		var sComentarios = $('#itxt_modal_servicios_prestados_comentarios').val().toUpperCase();
		var sTipoMov = $('#isel_modal_servicios_prestados_tipo_mov').val();
		var sNoBanco = $('#itxt_modal_servicios_prestados_banco').val();
		var sNoMov = $('#itxt_modal_servicios_prestados_no_mov').val();

		if (!sPedimento.trim()) {
			var sHtml = '';
			sHtml += '<div class="alert alert-danger">';
			sHtml += '    <span style="color:#FFF;">Debes ingresar un pedimento!</span>';
			sHtml += '</div>';

			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			return;
		}

		if (!sCvePedimento.trim()) {
			var sHtml = '';
			sHtml += '<div class="alert alert-danger">';
			sHtml += '    <span style="color:#FFF;">Debes ingresar la clave del pedimento!</span>';
			sHtml += '</div>';

			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			return;
		}

		if (!sNoBanco.trim() || !sNoMov.trim()) {
			var sHtml = '';
			sHtml += '<div class="alert alert-danger">';
			sHtml += '    <span style="color:#FFF;">Debes ingresar un numero de banco y un numero de movimiento!</span>';
			sHtml += '</div>';

			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			return;
		}

		/********************************************************/

		var oData = {			
			sIdEmpresa: __EmpresaId,
			sRutaDatos: __EmpresaRutaDatos,
			sPedimento: sPedimento,
			sCvePedimento: sCvePedimento,
			sOperacion: sOperacion,
			sTipoMov: sTipoMov,
			sNoBanco: sNoBanco,
			sNoMov: sNoMov,
			sComentarios: sComentarios
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxC/ajax_set_servicio_prestado.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Asignando cuenta de gastos, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#ibtn_servicios_prestados_agregar').hide();
						
						var sMensaje = respuesta.Mensaje + '. Registros actualizados [' + respuesta.nRowsUpdate + ']';				
						if (respuesta.nRowsInsert) {
							sMensaje += ' Registros Insertados [' + respuesta.nRowsInsert + '].';
						}							
						
						var sHtml = '';
						sHtml += '<div class="alert alert-success" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + sMensaje + '!</span>';
						sHtml += '</div>';
						
						var $MsjCtrl = $('#idiv_servicios_prestados_mensaje');
						$MsjCtrl.html(sHtml);
						$MsjCtrl.fadeIn();
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
		var strMensaje = 'ajax_set_servicio_prestado() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: CUENTA ADICIONAL ::.. */
/* ..:: Agregamos una nueva cuenta adicional ::.. */
function ajax_set_adicional() {
	try {		
		$MsjCtrl = $('#idiv_adicionales_mensaje');
		$MsjCtrl.empty().hide();

		var sAduana = $('#itxt_modal_adicionales_aduana').val();
		var sPatente = $('#itxt_modal_adicionales_patente').val();
		var sPedimento = $('#itxt_modal_adicionales_pedimento').val();
		var sComentarios = $('#itxt_modal_adicionales_comentarios').val().toUpperCase();
		var sTipoMov = $('#isel_modal_adicionales_tipo_mov').val();
		var sNoBanco = $('#itxt_modal_adicionales_banco').val();
		var sNoMov = $('#itxt_modal_adicionales_no_mov').val();

		if (!sAduana.trim() || !sPatente.trim() || !sPedimento.trim()) {
			var sHtml = '';
			sHtml += '<div class="alert alert-danger">';
			sHtml += '    <span style="color:#FFF;">Debes ingresar una aduana, patente y pedimento!</span>';
			sHtml += '</div>';

			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			return;
		}

		if (!sNoBanco.trim() || !sNoMov.trim()) {
			var sHtml = '';
			sHtml += '<div class="alert alert-danger">';
			sHtml += '    <span style="color:#FFF;">Debes ingresar un numero de banco y un numero de movimiento!</span>';
			sHtml += '</div>';

			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			return;
		}

		/********************************************************/

		var oData = {			
			sIdEmpresa: __EmpresaId,
			sRutaDatos: __EmpresaRutaDatos,
			sAduana: sAduana,
			sPatente: sPatente,
			sPedimento: sPedimento,
			sTipoMov: sTipoMov,
			sNoBanco: sNoBanco,
			sNoMov: sNoMov,
			sComentarios: sComentarios
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxC/ajax_set_adicional.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Asignando cuenta de gastos, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#ibtn_adicionales_agregar').hide();
						
						var sMensaje = respuesta.Mensaje + '. Registros actualizados [' + respuesta.nRowsUpdate + ']';				
						if (respuesta.nRowsInsert) {
							sMensaje += ' Registros Insertados [' + respuesta.nRowsInsert + '].';
						}							
						
						var sHtml = '';
						sHtml += '<div class="alert alert-success" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + sMensaje + '!</span>';
						sHtml += '</div>';
						
						var $MsjCtrl = $('#idiv_adicionales_mensaje');
						$MsjCtrl.html(sHtml);
						$MsjCtrl.fadeIn();
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
		var strMensaje = 'ajax_set_adicional() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: CUENTA GASTOS (sin pedimento) ::.. */
/* ..:: Agregamos un nuevo servicio prestado ::.. */
function ajax_set_cuenta_gastos() {
	try {		
		$MsjCtrl = $('#idiv_cuenta_gastos_mensaje');
		$MsjCtrl.empty().hide();

		
		var sTipoMov = $('#isel_modal_cuenta_gastos_tipo_mov').val();
		var sNoBanco = $('#itxt_modal_cuenta_gastos_banco').val();
		var sNoMov = $('#itxt_modal_cuenta_gastos_no_mov').val();
		var sComentarios = $('#itxt_modal_cuenta_gastos_comentarios').val().toUpperCase();

		if (!sNoBanco.trim() || !sNoMov.trim()) {
			var sHtml = '';
			sHtml += '<div class="alert alert-danger">';
			sHtml += '    <span style="color:#FFF;">Debes ingresar un numero de banco y un numero de movimiento!</span>';
			sHtml += '</div>';

			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			return;
		}
		
		if (!sComentarios.trim()) {
			var sHtml = '';
			sHtml += '<div class="alert alert-danger">';
			sHtml += '    <span style="color:#FFF;">Debes ingresar un comentario!</span>';
			sHtml += '</div>';

			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			return;
		}

		/********************************************************/

		var oData = {			
			sIdEmpresa: __EmpresaId,
			sRutaDatos: __EmpresaRutaDatos,
			sTipoMov: sTipoMov,
			sNoBanco: sNoBanco,
			sNoMov: sNoMov,
			sComentarios: sComentarios
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expCxC/ajax_set_cuenta_gastos_sin_ped.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Asignando cuenta de gastos, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#ibtn_cuenta_gastos_agregar').hide();
						
						var sMensaje = ''
						if (respuesta.nRowsInsert) {
							sMensaje = respuesta.Mensaje + '. Registros Insertados [' + respuesta.nRowsInsert + '].';
						}							
						
						var sHtml = '';
						sHtml += '<div class="alert alert-success" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + sMensaje + '!</span>';
						sHtml += '</div>';
						
						var $MsjCtrl = $('#idiv_cuenta_gastos_mensaje');
						$MsjCtrl.html(sHtml);
						$MsjCtrl.fadeIn();
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
		var strMensaje = 'ajax_set_cuenta_gastos() :: ' + err.message;
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