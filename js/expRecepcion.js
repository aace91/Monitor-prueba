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

var oTableRectifPendientes = null;
var oTablePendientesGrid = null;
var oTablePendEntregarGrid = null;
var oTableEntregadosGrid = null;
var oTableEntregarPedimentosGrid = null;

var __nIdRegistroComentario;

var __nEnters = 0;
var __sPatente = '';
var __sPedimento = '';
var __sClavePed = '';
var __sRFC = '';
var __sFirElec = '';

var __oLinkPendientes;
var __oPendientesData = null;
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
    	__oPendientesData = $('#itxt_pendientes_data').data('pendientes');
    	__oLinkPendientes = $('#ilink_pendientes');
    	setInterval(function(){ 
    		if (__oLinkPendientes.hasClass('def_app_link_color_black')) {
    			__oLinkPendientes.removeClass('def_app_link_color_black').addClass('def_app_link_color_red');
    		} else {
    			__oLinkPendientes.removeClass('def_app_link_color_red').addClass('def_app_link_color_black');
    		}
    	}, 1000);
    	fcn_pendientes_link_alert_show();

    	/*************************************************************/

		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var target = $(e.target).attr("href") // activated tab
			
			switch(target) {
				case '#idiv_pendientes':
					ajax_get_grid_pendientes_data();
					break;
					
				case '#idiv_pendentregar':
					fcn_cargar_grid_pendentregar();
					break;
					
				case '#idiv_entregados':
					ajax_get_entregados_date_data();
					break;	
			}
		});

		$('#itxt_modal_ped_cod_pedimento').on("keypress", function(e) {
			if (e.keyCode == 13) {
				__nEnters += 1;
				
				if (__nEnters >= 9) {
					__nEnters = 0;
					$('#itxt_modal_ped_cod_pedimento').attr('disabled', true);
					setTimeout(function(){ 
						var aCode = $('#itxt_modal_ped_cod_pedimento').val().split("\n");
						__sPatente = aCode[0].trim();
						__sPedimento = aCode[1].trim();
						__sClavePed = aCode[2].trim();
						__sRFC = aCode[3].trim();
						__sFirElec = aCode[5].trim();
						
						__sClavePed = __sClavePed.toUpperCase();
						__sRFC = __sRFC.toUpperCase();
						__sFirElec = __sFirElec.toUpperCase();
						//alert(__sPatente + '-' + __sPedimento + '-' + __sClavePed);
						ajax_set_add_pedimento_codigo();
					}, 500);					
				}
			}
		});

		fcn_cargar_grid_pendientes();	
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

function fcn_cargar_grid_rectif_pendiente(){
	try {
		var aData = __oPendientesData.aDatos
		
		if (oTableRectifPendientes == null) {	
			var div_refresh_name = 'div_dtrectif_pedimentos_refresh';
			var div_table_name = 'dtrectif_pedimentos';
						
			oTableRectifPendientes = $('#' + div_table_name);
		
			oTableRectifPendientes.DataTable({
				responsive: true,
				data: aData,
				columns: [ 
					{ data: "NUM_REFE", className: "def_app_center"},
					{ data: "PEDIMENTO", className: "def_app_center"}
				],
				bFilter: false, //Quitamos el filtro
				paging: false,  //Quitamos las paginas
				aLengthMenu: [
					[10, 25, 50, 100, 200, -1],
					[10, 25, 50, 100, 200, "All"]
				],
				iDisplayLength: 10,
				language: {
					sProcessing: '<img src="images/cargando.gif" height="18" width="18"> Cargando, espera un momento por favor...',
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
					 //"<'row'<'col-xs-8'B><'col-xs-4'<'div_pendientes_refresh'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: []
			});
		} else {
			oTableRectifPendientes.DataTable().clear().draw();
			oTableRectifPendientes.dataTable().fnAddData(aData);
			//setTimeout(function(){ oTableArmarPalRemisionGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_rectif_pendiente() :: ' + err.message;
		show_error(strMensaje);
    }
}

function fcn_cargar_grid_pendientes() {
	oTablePendientesGrid = $('#dtpendientes');
	
	oTablePendientesGrid.DataTable({
		order: [[0, 'asc']],
		processing: true,
		ajax: 'ajax/expedientes/expRecepcion/postExpRecepcionPendientes.php',
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
				title: '<h2>Pedimentos Pendientes por Recibir</h2>',
				exportOptions: {
					columns: [ 0, 1, 2 ]
				}
			}
		]
	} );	
	
	var sButton = fcn_create_button_datatable('dtpendientes', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_pendientes_data();"');
	$("div.div_pendientes_refresh").html(sButton);
	
	oTablePendientesGrid.on('click', 'a.editor_pendientes_pedime', function (e) {
		try {		
			var current_row = $(this).parents('tr');//Get the current row
			if (current_row.hasClass('child')) {//Check if the current row is a child row
				current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
			}
			var table = oTablePendientesGrid.DataTable();
			var oData = table.row(current_row).data();			
			
			var sReferencia = oData[0];
			fcn_add_pedimento_click(sReferencia);
		} catch (err) {		
			var strMensaje = 'editor_edit_click() :: ' + err.message;
			show_label_error(strMensaje);
		}  
    } );
}

function fcn_cargar_grid_pendentregar(){
	try {
		if (oTablePendEntregarGrid == null) {
			oTablePendEntregarGrid = $('#dtpendentregar');
			
			oTablePendEntregarGrid.DataTable({
				order: [[1, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expRecepcion/postExpRecepcionPendientesEntregar.php",
					"type": "POST"
				},
				columns: [ 
					{ "data": "referencia_saaio", "className": "def_app_center"},		
					{ "data": "pedimento", "className": "def_app_center"},								
					{
						data:  "comentarios",
						render: function ( data, type, row ) {
							if (data == "") {
								return '<a href="#" class="editor_pendentregar_editar_comentario"><i class="fa fa-comment-o" aria-hidden="true"></i> Agregar Comentario</a>';
							} else {
								return '<a href="#" class="editor_pendentregar_editar_comentario"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Editar</a> ' + data;
							}
						},
						className: "def_app_left"
					},
					{ "data": "fecha_recepcion_captura", "className": "def_app_center"}    
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
					 "<'row'<'col-xs-8'B><'col-xs-4'<'div_pend_entregar_refresh'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Pedimentos Pendientes por Entregar</h2>'
					}
				]
			});
			
			var sButton = fcn_create_button_datatable('dtpendentregar', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_pendentregar_data();"');
			$("div.div_pend_entregar_refresh").html(sButton);
			
			oTablePendEntregarGrid.on('click', 'a.editor_pendentregar_editar_comentario', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTablePendEntregarGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__nIdRegistroComentario = oData.id_registro;
					fcn_mostrar_comentarios(oData.comentarios);
				} catch (err) {		
					var strMensaje = 'editor_recive_pedimento_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
			
			// $('#dtfolios tbody').on('click', 'tr', function () { 
				// var table = oTablePendEntregarGrid.DataTable();
				
				// table.buttons(0).text('<i class="fa fa-pencil" aria-hidden="true"></i> Editar');
				// if ( table.rows( { selected: true } ).indexes().length === 0 ) {
					// __strFechaEntrega = '';
				// } else { 
					// __strFechaEntrega = table.row({selected: true}).data().fecha_recepcion_entrega;
						
					// if (__strFechaEntrega != '') {
						// table.buttons(0).text('<i class="fa fa-eye" aria-hidden="true"></i> Ver');
						// table.buttons(1).disable();
					// }
				// }
			// });
		} else {
			ajax_get_grid_pendentregar_data();
		}	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pendentregar() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

function fcn_cargar_grid_entregar_pedime() {
	try {
		if (oTableEntregarPedimentosGrid == null) {
			oTableEntregarPedimentosGrid = $('#dtentregar_pedimentos');
			
			oTableEntregarPedimentosGrid.DataTable({
				order: [[1, 'asc']],
				processing: true,
				ajax: {
					"url": "ajax/expedientes/expRecepcion/postExpRecepcionPendientesEntregar.php",
					"type": "POST"
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
					{ "data": "referencia_saaio", "className": "def_app_center"},		
					{ "data": "pedimento", "className": "def_app_center"},								
					{ "data": "comentarios", "className": "def_app_center"}
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

		} else {
			oTableEntregarPedimentosGrid.DataTable().column(0).checkboxes.deselect();
			ajax_get_grid_entregar_pedime_data();
		}	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pendentregar() :: ' + err.message;
		show_label_error(strMensaje);
    }	
}

function fcn_cargar_grid_entregados(){
	try {
		if (oTableEntregadosGrid == null) {
			oTableEntregadosGrid = $('#dtentregados');
			
			oTableEntregadosGrid.DataTable({
				order: [[1, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expRecepcion/postExpRecepcionEntregados.php",
					"type": "POST",
					"data": function ( d ) {
						d.sFecha = $('#isel_entregados_fechas').val();
					}
				},
				columns: [ 
					{ "data": "referencia_saaio", "className": "def_app_center"},		
					{ "data": "pedimento", "className": "def_app_center"},				
					{ "data": "comentarios", "className": "def_app_center"},				
					{ "data": "fecha_recepcion_captura", "className": "def_app_center"},				
					{ "data": "fecha_recepcion_entrega", "className": "def_app_center"}  
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
					 "<'row'<'col-xs-8'B><'col-xs-4'<'div_entregados_refresh'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Pedimentos Entregados</h2>',// + $("#isel_entregados_fechas").val() + '</h2>',
						exportOptions: {
							columns: [ 1, 2, 4 ]
						}
					}
				]
			});
			
			// var sButton = fcn_create_button_datatable('dtentregados', '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_pendentregar_data();"');
			// $("div.div_entregados_refresh").html(sButton);
		} else {
			ajax_get_grid_entregados_data();
		}	
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pendentregar() :: ' + err.message;
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

/* ..:: Funcion para mostrar o no la alerta de rectificaciones pendientes ::.. */
function fcn_pendientes_link_alert_show() {
	if (__oPendientesData.aDatos.length > 0) {
		__oLinkPendientes.show();
	} else {
		__oLinkPendientes.hide();
	}
}

/* ..:: Mostramos la modal de rectificaciones pendientes ::.. */
function fcn_pendientes_rectif_show() {
	$('#modal_rectif_pendientes').modal({
		show: true
	});
	fcn_cargar_grid_rectif_pendiente();
}

/* ..:: Verificamos el arreglo de Rectificaciones ::.. */
function fcn_pendientes_rectif_verify(aData) {
	__oPendientesData.aDatos = aData;
	fcn_pendientes_link_alert_show();
}

function fcn_crear_timeout() {
	__oLinkPendientes.hide();
}

/*******************************************************************************************/

/* ..:: Mostramos la modal de captura de pedimento ::.. */
function fcn_add_pedimento() {
	__nEnters = 0;
	
	fcn_modal_pedimento_clear_ctrls();
	$('#ili_modal_add_ped_codigo').removeClass( "active" );
	$('#ili_modal_add_ped_referencia').removeClass( "active" );
	$('#idiv_modal_add_ped_codigo').removeClass( "active" );
	$('#idiv_modal_add_ped_referencia').removeClass( "active" );
	
	$('#ili_modal_add_ped_codigo').addClass( "active" );
	$('#idiv_modal_add_ped_codigo').addClass( "active" );
	
	$('#itxt_modal_ped_cod_pedimento').attr('disabled', false);
	$("#itxt_modal_ped_cod_pedimento").focus();
	
	setTimeout(function(){ 
		$("#itxt_modal_ped_cod_pedimento").focus();
	}, 1000);
					
	$('#modal_add_pedimentos').modal({
		show: true
	});
}

function fcn_close_modal_pedimento() { 
	ajax_get_grid_pendientes_data();
}

/* ..:: Limpiamos los controles de la ventana modal ::.. */
function fcn_modal_pedimento_clear_ctrls() {
	$('#isel_modal_ped_cod_comentarios').val('');
	$('#itxt_modal_ped_cod_comentarios_otros').val('');
	$('#itxt_modal_ped_cod_pedimento').val('');
	$('#itxt_modal_ped_ref_comentarios').val('');
	$('#itxt_modal_ped_ref_referencia').val('');
}

/* ..:: Mostramos la modal de captura el comentario ::.. */
function fcn_add_pedimento_click(sReferencia) {
	fcn_modal_pedimento_click_clear_ctrls();
	
	$("#itxt_modal_ped_click_comentarios").focus();
	$("#itxt_modal_ped_click_referencia").val(sReferencia);
	
	setTimeout(function(){ 
		$("#itxt_modal_ped_click_comentarios").focus();
	}, 1000);
					
	$('#modal_add_pedimentos_click').modal({
		show: true
	});
}

/* ..:: Limpiamos los controles de la ventana modal de comentario ::.. */
function fcn_modal_pedimento_click_clear_ctrls() {
	$('#itxt_modal_ped_click_comentarios').val('');
	$('#itxt_modal_ped_click_referencia').val('');
}

/* ..:: Mostramos la modal para entregar pedimentos ::.. */
function fcn_modal_mostrar_entregar_pedime() {
	$('#modal_entregar_pedimentos').modal({
		show: true
	});
	fcn_cargar_grid_entregar_pedime();
}

/* ..:: Obtenemos los pedimentos seleccionados ::.. */
function fcn_entregar_pedime_elementos_seleccionados() {
	var rows_selected = oTableEntregarPedimentosGrid.DataTable().column(0).checkboxes.selected();
	var aSelected = new Array();
	$.each(rows_selected, function(index, rowId){
		aSelected.push(rowId.id_registro);
    });
	
	if (aSelected.length > 0) {
		ajax_set_upd_entregar_pedime(aSelected);
	} else {
		var sHtml = '';
		sHtml += '<div class="alert alert-danger">';
		sHtml += '    <span style="color:#FFF;">Debe seleccionar por lo menos un Pedimento.</span>';
		sHtml += '</div>';
		
		if (sHtml.trim()) {
			var $MsjCtrl = $('#idiv_modal_entregar_pedime_click_mensaje');
			$MsjCtrl.html(sHtml);
			$MsjCtrl.fadeIn();
			
			setTimeout(function () {
				$MsjCtrl.fadeOut();
			},4000);	
		}
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

/* ..:: Funcion para mostrar el boton de imprimir relacion ::.. */
/*function fcn_show_constrols_pedime() {
	var table = oTablePedimentosFolioGrid.DataTable();
	
	$('#ili_pendientes').removeClass( "active" );
	$('#ili_pedimentos').removeClass( "active" );
	$('#idiv_pendientes').removeClass( "active" );
	$('#idiv_pedimentos').removeClass( "active" );
	
	if (__strFechaEntrega == '') {
		$('#ili_pendientes').show();
		$('#ili_pendientes').addClass( "active" );
		$('#idiv_pendientes').addClass( "active" );
	} else {
		$('#ili_pendientes').hide();
		
		$('#ili_pedimentos').addClass( "active" );
		$('#idiv_pedimentos').addClass( "active" );
	}
			
	if (__strFechaEntrega == '') {
		table.buttons(1).disable();
	} else {		
		table.buttons(1).enable();
	}
	
	if (__strFechaEntrega == '') { 				
		$('#ibtn_generar_relacion').show();	
	} else {
		$('#ibtn_generar_relacion').hide();
	}
}*/

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/* ..:: DATATABLES ::.. */
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
function ajax_get_grid_entregar_pedime_data() {
	try {	
		var table = oTableEntregarPedimentosGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_entregar_pedime_data() :: ' + err.message;
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

/* PEDIMENTOS PENDIENTES DE ESCANEAR (CASO RECTIFICACIONES) */


/* ..:: PEDIMENTOS POR RECIBIR ::.. */
/* ..:: Agregamos pedimento y eliminamos de los pendientes ::.. */
function ajax_set_add_pedimento_codigo() {
	try {		
		var sComentarios = $('#isel_modal_ped_cod_comentarios').val();
		var sComentariosOtros = $('#itxt_modal_ped_cod_comentarios_otros').val();
		
		sComentarios = sComentarios + ' ' + sComentariosOtros;
		sComentarios = sComentarios.trim();
		
		var oData = {			
			sPatente: __sPatente,
			sPedimento: __sPedimento,
			sClavePed: __sClavePed,
			sRFC: __sRFC,
			sFirElec: __sFirElec,
			sComentarios: sComentarios
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expRecepcion/ajax_set_add_pedimento_codigo.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Agregando pedimento, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						fcn_add_pedimento();
						//ajax_get_grid_pendientes_data();
						
						var sHtml = '';
						sHtml += '<div class="alert alert-success">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '</span>';
						sHtml += '</div>';
						
						if (sHtml.trim()) {
							var $MsjCtrl = $('#idiv_modal_ped_cod_mensaje');
							$MsjCtrl.html(sHtml);
							$MsjCtrl.show();
							
							setTimeout(function () {
								$MsjCtrl.hide();
							},4000);	
						}
						
						fcn_pendientes_rectif_verify(respuesta.aDatos);
					}else{
						fcn_add_pedimento();
						
						var sHtml = '';
						sHtml += '<div class="alert alert-danger">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + respuesta.Error + '</span>';
						sHtml += '</div>';
						
						if (sHtml.trim()) {
							var $MsjCtrl = $('#idiv_modal_ped_cod_mensaje');
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
		var strMensaje = 'ajax_set_add_pedimento_codigo() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

function ajax_set_add_pedimento_referencia() {
	try {		
		var sComentarios = $('#itxt_modal_ped_ref_comentarios').val();
		var sReferencia = $('#itxt_modal_ped_ref_referencia').val();
	
		if (!sReferencia.trim()) {
			var sHtml = '';
			sHtml += '<div class="alert alert-danger">';
			sHtml += '    <span style="color:#FFF;">Debes ingresar una referencia!</span>';
			sHtml += '</div>';
			
			if (sHtml.trim()) {
				var $MsjCtrl = $('#idiv_modal_ped_ref_mensaje');
				$MsjCtrl.html(sHtml);
				$MsjCtrl.fadeIn();
				
				setTimeout(function () {
					$MsjCtrl.fadeOut();
				},4000);	
			}
			return;
		}
		
		var oData = {			
			sReferencia: sReferencia,
			sComentarios: sComentarios
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expRecepcion/ajax_set_add_pedimento_referencia.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Agregando pedimento, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						fcn_add_pedimento();
						ajax_get_grid_pendientes_data();
						
						var sHtml = '';
						sHtml += '<div class="alert alert-success">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '</span>';
						sHtml += '</div>';
						
						if (sHtml.trim()) {
							var $MsjCtrl = $('#idiv_modal_ped_ref_mensaje');
							$MsjCtrl.html(sHtml);
							$MsjCtrl.fadeIn();
							
							setTimeout(function () {
								$MsjCtrl.fadeOut();
							},4000);	
						}

						fcn_pendientes_rectif_verify(respuesta.aDatos);
					}else{
						fcn_add_pedimento();
						
						var sHtml = '';
						sHtml += '<div class="alert alert-danger">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + respuesta.Error + '</span>';
						sHtml += '</div>';
						
						if (sHtml.trim()) {
							var $MsjCtrl = $('#idiv_modal_ped_ref_mensaje');
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
		var strMensaje = 'ajax_set_add_pedimento_referencia() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

function ajax_set_add_pedimento_click() {
	try {		
		var sComentarios = $('#itxt_modal_ped_click_comentarios').val();
		var sReferencia = $('#itxt_modal_ped_click_referencia').val();
	
		if (!sReferencia.trim()) {
			var sHtml = '';
			sHtml += '<div class="alert alert-danger">';
			sHtml += '    <span style="color:#FFF;">Debes ingresar una referencia!</span>';
			sHtml += '</div>';
			
			if (sHtml.trim()) {
				var $MsjCtrl = $('#idiv_modal_ped_click_mensaje');
				$MsjCtrl.html(sHtml);
				$MsjCtrl.fadeIn();
				
				setTimeout(function () {
					$MsjCtrl.fadeOut();
				},4000);	
			}
			return;
		}
		
		var oData = {			
			sReferencia: sReferencia,
			sComentarios: sComentarios
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expRecepcion/ajax_set_add_pedimento_referencia.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Agregando pedimento, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						//fcn_add_pedimento_click();
						ajax_get_grid_pendientes_data();
						
						var sHtml = '';
						sHtml += '<div class="alert alert-success">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '</span>';
						sHtml += '</div>';
						
						if (sHtml.trim()) {
							var $MsjCtrl = $('#idiv_modal_ped_click_mensaje');
							$MsjCtrl.html(sHtml);
							$MsjCtrl.fadeIn();
							
							setTimeout(function () {
								$MsjCtrl.fadeOut();
								$('#modal_add_pedimentos_click').modal('hide');
							},1000);	
						}

						fcn_pendientes_rectif_verify(respuesta.aDatos);
					} else {
						//fcn_add_pedimento_click();
						
						var sHtml = '';
						sHtml += '<div class="alert alert-danger">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + respuesta.Error + '</span>';
						sHtml += '</div>';
						
						if (sHtml.trim()) {
							var $MsjCtrl = $('#idiv_modal_ped_click_mensaje');
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
		var strMensaje = 'ajax_set_add_pedimento_click() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}


/* ..:: PEDIMENTOS POR ENTREGAR ::.. */
function ajax_set_upd_entregar_pedime(aSelected) {
	try {		
		var oData = {			
			aSelected: JSON.stringify(aSelected)
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expRecepcion/ajax_set_upd_entregar_pedime.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Entregando pedimentos, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#ili_pedentregar').removeClass( "active" );
						$('#idiv_pendentregar').removeClass( "active" );
						
						$('#ili_entregados').addClass( "active" );
						$('#idiv_entregados').addClass( "active" );
	
						$('#modal_entregar_pedimentos').modal('hide');
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
		var strMensaje = 'ajax_set_upd_entregar_pedime() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: Editar comentario ::.. */
function ajax_set_upd_comentario() {
	try {		
		var sComentarios = $('#itxt_modal_edit_comentario_click_comentarios').val().toUpperCase();
	
		if (!sComentarios.trim()) {
			var sHtml = '';
			sHtml += '<div class="alert alert-danger">';
			sHtml += '    <span style="color:#FFF;">Debes ingresar un comentario!</span>';
			sHtml += '</div>';
			
			if (sHtml.trim()) {
				var $MsjCtrl = $('#idiv_modal_edit_comentario_click_mensaje');
				$MsjCtrl.html(sHtml);
				$MsjCtrl.fadeIn();
				
				setTimeout(function () {
					$MsjCtrl.fadeOut();
				},4000);	
			}
			return;
		}
		
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
						ajax_get_grid_pendentregar_data();
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

/* ..:: PEDIMENTOS ENTREGADOS ::.. */
function ajax_get_entregados_date_data(dtFecha = '') {
	try {		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expRecepcion/ajax_get_entregados_date_data.php',
		
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

/*******************************************************************************************/

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















