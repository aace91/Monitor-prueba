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

var oTableSalidasGrid = null;
var oTableSalidasFacturaGrid = null;

var __IdSalida;
var __IdCliente;
var __sCaja;
var __IdLogistica;
				
var __nTimerSecondsSync = 300; //Representado en segundos
var __nCountSync = __nTimerSecondsSync;
var __oTimerSync;

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
		$.fn.dataTable.ext.errMode = 'none';
		
		/**********************************/
		
		var oTouchSpinProp = {
			verticalbuttons: true,
			min: 0,
			max: 1000000000,
			step: 1,
            decimals: 0
		}
		$("#itxt_asig_bultos").TouchSpin(oTouchSpinProp);

		$('#isel_clientes_expo').select2({
			theme: "bootstrap",
			width: "off",
			placeholder: "Selecciona un Cliente"
		});

		$('#isel_logistica').select2({
			theme: "bootstrap",
			width: "off",
			placeholder: "Selecciona una opción"
		});
						
		fcn_cargar_grid_salidas();
		
		__oTimerSync = setTimeout(function () { ajax_set_insert_sync(); }, (__nTimerSecondsSync * 1000));
		setInterval(function(){ 
			__nCountSync -= 1;
			if (__nCountSync <= 0) {
				__nCountSync = 0;
			}
			
			$('#ispan_sync_message').html('<i class="fa fa-clock-o" aria-hidden="true"></i> Sincronizando en ' + __nCountSync + ' Segundos');
		}, 1000);		
		
		setInterval(function(){ 
			__nCountRefresGrid -= 1;
			if (__nCountRefresGrid <= 0) {
				fcn_cargar_grid_salidas(); 
			}
			
			$('#ispan_refresh_message').html('<i class="fa fa-clock-o" aria-hidden="true"></i> Actualizando Informaci&oacute;n de grid en ' + __nCountRefresGrid + ' Segundos');
		}, 1000);
		
		/* ..:: Configuramos el FileInput ::.. */
		$("#ifile_documentos").fileinput({
			uploadUrl: "ajax/exposSalidas/ajax_upload_files.php", // server upload action
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
		
		$('#modalloadconfig, #modalmessagebox_ok, #modalmessagebox_error, #modalconfirm, #kvFileinputModal').on('hidden.bs.modal', function (e) {
			var oModalsOpen = $('.in');
			if (oModalsOpen.length > 0 ) {
				$('body').addClass('modal-open');
			}
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

function fcn_cargar_grid_salidas(){
	try {
		if (oTableSalidasGrid == null) {
			var div_refresh_name = 'div_dtsalidas_refresh';
			var div_table_name = 'dtsalidas';
					
			// $('#' + div_table_name + ' tfoot th').each( function () {
				// var title = $(this).text();
				// $(this).html( '<input type="text" placeholder="Buscar '+title+'" />' );
			// } );
			
			oTableSalidasGrid = $('#' + div_table_name);
			
			oTableSalidasGrid.removeAttr('width').DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/exposSalidas/postSalidas.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
			            d.sIdCliente = $('#isel_clientes_expo').val();
					},
					'beforeSend': function (request) {
						hide_message();
					},
					"error": handleAjaxError
				},
				scrollY: "400px",
				scrollX:        true,
				fixedColumns: {
					leftColumns: 2
				},
				columns: [ 
					{ data: "salidanumero", className: "def_app_center"},	
					{ data: "caja", className: "def_app_center"},	
					{ data: "facturas", className: "def_app_center"},
					{ data: "fecha", className: "def_app_center"},
					{ 
						data: "logistica", 
						className: "def_app_center",
						render: function ( data, type, row ) {
							if (data == "" || data == null) {
								return '<a class="editor_dtsalidas_logistica"><i class="fa fa-hand-pointer-o" aria-hidden="true"></i> Asignar</a>';
							} else {
								return '<a class="editor_dtsalidas_logistica"><i class="fa fa-hand-pointer-o" aria-hidden="true"></i> Editar</a> ' + data;
							}
						}
					},
					{ 
						data: "bultos", 
						className: "def_app_center",
						render: function ( data, type, row ) {
							if (data == "" || data == null) {
								return '<a class="editor_dtsalidas_bultos"><i class="fa fa-plus" aria-hidden="true"></i> Agregar</a>';
							} else {
								return data + ' <a class="editor_dtsalidas_bultos"><i class="fa fa-pencil" aria-hidden="true"></i> Editar</a> ';
							}
						}
					},
					{ data: "estatus", className: "def_app_center"},
					{ data: "fecha_aprobado", className: "def_app_center"},
					{
						data: "bcomentario",
						className: "def_app_center",
						render: function ( data, type, row ) {
							if (data == "" || data == null) {
								return '';
							} else {
								return '<a class="editor_dtsalidas_comentarios"><img src="../images/comentarios.gif" width="15" height="15" border="0" /> Ver</a>';
							}
						}
					},
					{
						data: null,
						className: "def_app_center",
						defaultContent: '<a class="editor_dtsalidas_documentos"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Ver</a>'
					},
					{
						data: null,
						className: "def_app_center",
						render: function ( data, type, row ) {
							if (data.fecha_aprobado == "" || data.fecha_aprobado == null) {
								return '<a class="editor_dtsalidas_enviar"><i class="fa fa-paper-plane" aria-hidden="true"></i> Enviar</a>';
							} else {
								return '';								
							}
						}
					},
					{
						data: null,
						className: "def_app_center",
						defaultContent: '<a class="editor_dtsalidas_detalles"><i class="fa fa-eye" aria-hidden="true"></i> Ver Facturas</a>'
					}
				],
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
				buttons: [
					/*{
						extend: 'copy',
						text: '<i class="fa fa-files-o" aria-hidden="true"></i> Copiar',
						exportOptions: {
							columns: [ 0, 1, 2 ]
						}
					},
					{
						extend: 'excelHtml5',
						exportOptions: {
							columns: [ 0, 1, 2 ]
						}
					},*/
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
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_salidas_data();"');
			$("div." + div_refresh_name).html(sButton);
				
			oTableSalidasGrid.on('click', 'a.editor_dtsalidas_logistica', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableSalidasGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__IdSalida = oData.salidanumero;
					__IdCliente	= oData.id_cliente;
					__sCaja = oData.caja;
					__IdLogistica = oData.idlogistica;

					fcn_salida_logistica();
				} catch (err) {		
					var strMensaje = 'editor_dtsalidas_logistica() :: ' + err.message;
					show_error(strMensaje);
				}  
			} );

			oTableSalidasGrid.on('click', 'a.editor_dtsalidas_bultos', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableSalidasGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__IdSalida = oData.salidanumero;
					__IdCliente	= oData.id_cliente;
					__sCaja = oData.caja;
					__IdLogistica = oData.idlogistica;

					var nBultos = ((oData.bultos == "" || oData.bultos == null)? 0 : oData.bultos);
					fcn_salida_bultos(nBultos);
				} catch (err) {		
					var strMensaje = 'editor_dtsalidas_bultos() :: ' + err.message;
					show_error(strMensaje);
				}  
			} );
						
			oTableSalidasGrid.on('click', 'a.editor_dtsalidas_comentarios', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableSalidasGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__IdSalida = oData.salidanumero;
					__IdCliente	= oData.id_cliente;
					__sCaja = oData.caja;
					__IdLogistica = oData.idlogistica;
					
					fcn_salida_comentarios();
				} catch (err) {		
					var strMensaje = 'editor_dtsalidas_comentarios() :: ' + err.message;
					show_error(strMensaje);
				}  
			} );
			
			oTableSalidasGrid.on('click', 'a.editor_dtsalidas_detalles', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableSalidasGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__IdSalida = oData.salidanumero;
					__IdCliente	= oData.id_cliente;
					__sCaja = oData.caja;
					__IdLogistica = oData.idlogistica;
					
					fcn_salida_facturas();
				} catch (err) {		
					var strMensaje = 'editor_dtsalidas_detalles() :: ' + err.message;
					show_error(strMensaje);
				}  
			} );
			
			oTableSalidasGrid.on('click', 'a.editor_dtsalidas_documentos', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableSalidasGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__IdSalida = oData.salidanumero;
					__IdCliente	= oData.id_cliente;
					__sCaja = oData.caja;
					__IdLogistica = oData.idlogistica;
					
					fcn_salida_documentos();
				} catch (err) {		
					var strMensaje = 'editor_dtsalidas_documentos() :: ' + err.message;
					show_error(strMensaje);
				}  
			} );

			oTableSalidasGrid.on('click', 'a.editor_dtsalidas_enviar', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableSalidasGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__IdSalida = oData.salidanumero;
					__IdCliente	= oData.id_cliente;
					__sCaja = oData.caja;
					__IdLogistica = oData.idlogistica;
					
					fcn_salida_enviar_correo();
				} catch (err) {		
					var strMensaje = 'editor_dtsalidas_detalles() :: ' + err.message;
					show_error(strMensaje);
				}  
			} );
			
			
			oTableSalidasGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message);
			} );
		} else {
			ajax_get_grid_salidas_data();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_salidas() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_cargar_grid_salidas_facturas(){
	try {
		if (oTableSalidasFacturaGrid == null) {
			var div_refresh_name = 'div_dtsalidas_facturas_refresh';
			var div_table_name = 'dtsalidas_facturas';
						
			oTableSalidasFacturaGrid = $('#' + div_table_name);
			
			oTableSalidasFacturaGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/exposSalidas/postSalidasFacturas.php",
					"type": "POST",
					"data": function ( d ) {
			            d.sIdSalida = __IdSalida;
					},
					"error": handleAjaxError
				},
				columns: [ 
					{ data: "FACTURA_NUMERO", className: "def_app_center"},	
					{ data: "VALOR_FACTURA", className: "def_app_center"},
					{ data: "REFERENCIA", className: "def_app_center"},
					{ data: "PEDIMENTO", className: "def_app_center"}
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
						title: '<h2>Lista de Facturas</h2>',
						exportOptions: {
							columns: [ 0, 1, 2, 3 ]
						}
					}
				]
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_salidas_facturas();"');
			$("div." + div_refresh_name).html(sButton);
			
			oTableSalidasFacturaGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message);
			} );
		} else {
			ajax_get_grid_salidas_facturas_data();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_salidas_facturas() :: ' + err.message;
		show_modal_error(strMensaje);
    }
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

/* ..:: Mostramos la modal para asignar la logistica ::.. */
function fcn_salida_logistica() {
	try {		
		$('#isel_logistica').val('').trigger('change');
		$('#modal_asign_logistica').modal({ show: true });
		ajax_get_logisticas();
	} catch (err) {
		var strMensaje = 'fcn_salida_logistica() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: Mostramos la modal para asignar los bultos ::.. */
function fcn_salida_bultos(nBultos) {
	try {		
		$('#modal_asign_bultos').modal({ show: true });
		$("#itxt_asig_bultos").val(nBultos);
	} catch (err) {
		var strMensaje = 'fcn_salida_bultos() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: validamos antes de guardar los bultos ::.. */
function fcn_guardar_bultos() {
	var nBultos = $('#itxt_asig_bultos').val();
	if (nBultos == '' || nBultos <= 0) {
		show_modal_error('Debes ingresar un numero de bulto.');
		return;
	}

	var strTitle = 'Guardar Bultos';
	var strQuestion = 'Desea guardar la cantidad [' + nBultos + '] de bultos?';
	var oFunctionOk = function () { ajax_set_update_bultos(); };
	var oFunctionCancel = null;
	show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
}

/* ..:: Mostramos la modal de los comentarios ::.. */
function fcn_salida_comentarios() {
	try {		
		$('#idiv_timeline').empty();
		$('#modal_timeline').modal({ show: true });
		ajax_get_comentarios();
	} catch (err) {
		var strMensaje = 'fcn_salida_comentarios() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: Mostramos las facturas de la salida ::.. */
function fcn_salida_facturas() {
	try {
		$('#modal_salidas_facturas').modal({ show: true });
		fcn_cargar_grid_salidas_facturas();
	} catch (err) {
		var strMensaje = 'fcn_salida_facturas() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: Mostramos los documentos de la salida ::.. */
function fcn_salida_documentos() {
	try {
		var aPreview = new Array(); 
		var aPreviewConfig = new Array(); 
		fcn_fill_file_input(aPreview, aPreviewConfig);
		
		$('#modal_subir_docs').modal({ show: true });
		ajax_get_archivos();
	} catch (err) {
		var strMensaje = 'fcn_salida_documentos() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/* ..:: enviamos el correo ::.. */
function fcn_salida_enviar_correo() {
	try {
		$('#isel_enviar_correo_a').val('');
		$('#itxt_enviar_correo_observaciones').val('');
		
		$('#modal_enviar_correo').modal({ show: true });
	} catch (err) {
		var strMensaje = 'fcn_salida_documentos() :: ' + err.message;
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
			uploadUrl: "ajax/exposSalidas/ajax_upload_files.php", // server upload action
			uploadAsync: false,
			uploadExtraData: function() {
				return {
					sIdSalida: __IdSalida,
					sIdCliente: __IdCliente,
					sCaja: __sCaja,
					sIdLogistica: __IdLogistica
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
			},
			allowedFileExtensions: ["jpg", "png", "gif", "pdf"]
			/*,
			uploadExtraData: {
				img_key: "1000",
				img_keywords: "happy, places",
			}*/
		});
	} catch (err) {
		var strMensaje = 'fcn_fill_file_input() :: ' + err.message;
		show_message_error(strMensaje);
    }
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/* ..:: DATATABLES ::.. */
/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_salidas_data() {
	try {	
		__nCountRefresGrid = __nTimerSecondsRefresGrid;
		
		var table = oTableSalidasGrid.DataTable();
		table.ajax.reload(null, false);	
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_salidas_data() :: ' + err.message;
		show_modal_error(strMensaje, false);
    }  
}

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_salidas_facturas_data() {
	try {	
		var table = oTableSalidasFacturaGrid.DataTable();
		table.ajax.reload();		
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_salidas_facturas_data() :: ' + err.message;
		show_modal_error(strMensaje, false);
    }  
}

/* ..:: LOGISTICA ::.. */
function ajax_get_logisticas() {
	try {		
		var oData = {			
			sIdSalida: __IdSalida,
			sIdCliente: __IdCliente
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidas/ajax_get_logisticas.php',
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
						$('#isel_logistica').html(respuesta.sLogisticas);
						$('#isel_logistica').select2({
							theme: "bootstrap",
							width: "off",
							placeholder: "Selecciona una opción"
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
		var strMensaje = 'ajax_get_logisticas() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

function ajax_set_update_logistica() {
	try {
		var sIdLogistica = $('#isel_logistica').val();
		if (sIdLogistica == '') {
			show_modal_error('Debes seleccionar una logistica.');
			return;
		}

		var oData = {			
			sIdSalida: __IdSalida,
			sIdLogistica: sIdLogistica
		};

		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidas/ajax_set_update_logistica.php',		
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Asignando logistica, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						show_modal_ok(respuesta.Mensaje);
						$('#modal_asign_logistica').modal('hide');
						fcn_cargar_grid_salidas();
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_message_error(strMensaje);
					}
					
					__nCountSync = __nTimerSecondsSync;
					__oTimerSync = setTimeout(function () { ajax_set_insert_sync(); }, (__nTimerSecondsSync * 1000));
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
		var strMensaje = 'ajax_set_update_logistica() :: ' + err.message;
		show_message_error(strMensaje);
    }    
}

function ajax_set_update_bultos() {
	try {
		var nBultos = $('#itxt_asig_bultos').val();
		if (nBultos == '') {
			show_modal_error('Debes ingresar un numero de bulto.');
			return;
		}

		var oData = {			
			sIdSalida: __IdSalida,
			nBultos: nBultos
		};

		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidas/ajax_set_update_bultos.php',		
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Asignando logistica, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						show_modal_ok(respuesta.Mensaje);
						$('#modal_asign_bultos').modal('hide');
						fcn_cargar_grid_salidas();
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_message_error(strMensaje);
					}
					
					__nCountSync = __nTimerSecondsSync;
					__oTimerSync = setTimeout(function () { ajax_set_insert_sync(); }, (__nTimerSecondsSync * 1000));
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
		var strMensaje = 'ajax_set_update_bultos() :: ' + err.message;
		show_message_error(strMensaje);
    }    
}

function ajax_set_update_enviar_correo() {
	try {
		var sCorreo = $('#isel_enviar_correo_a').val();
		if (sCorreo == '') {
			show_modal_error('Debes seleccionar un correo.');
			return;
		}

		var sObservaciones = $('#itxt_enviar_correo_observaciones').val();

		var oData = {			
			sIdSalida: __IdSalida,
			sIdCliente: __IdCliente,
			sCaja: __sCaja,
			sIdLogistica: __IdLogistica,
			sCorreo: sCorreo,
			sObservaciones: sObservaciones
		};

		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidas/ajax_set_update_enviar_correo.php',		
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Enviando correo, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						show_modal_ok(respuesta.Mensaje);
						$('#modal_enviar_correo').modal('hide');
						fcn_cargar_grid_salidas();
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
					
					__nCountSync = __nTimerSecondsSync;
					__oTimerSync = setTimeout(function () { ajax_set_insert_sync(); }, (__nTimerSecondsSync * 1000));
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
		var strMensaje = 'ajax_set_update_enviar_correo() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: ARCHIVOS ::.. */
function ajax_get_archivos() {
	try {		
		var oData = {			
			sIdSalida: __IdSalida
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidas/ajax_get_archivos.php',
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

/* ..:: COMENTARIOS ::.. */
function ajax_get_comentarios() {
	try {	
		$('#itxt_timeline_comentario').val('');
		
		var oData = {			
			sIdSalida: __IdSalida
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidas/ajax_get_comentarios.php',
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
								var oTlHeading = $('<div/>', {'class':'timeline-heading'}).append('<h4>' + value.ct + '</h4>');
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
		var strMensaje = 'ajax_get_comentarios() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: SYNC ::.. */
function ajax_set_insert_sync() {
	try {	
		hide_message();
		
		$.ajax({
            type: "POST",
            url: 'ajax/exposSalidas/ajax_set_insert_sync.php',
			timeout: 60000,
		
            beforeSend: function (dataMessage) {
				clearTimeout(__oTimerSync);
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
						show_message_error(strMensaje);
					}
					
					__nCountSync = __nTimerSecondsSync;
					__oTimerSync = setTimeout(function () { ajax_set_insert_sync(); }, (__nTimerSecondsSync * 1000));
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
		var strMensaje = 'ajax_set_insert_sync() :: ' + err.message;
		show_message_error(strMensaje);
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