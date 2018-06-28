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

var oTableCuentasGrid = null;

var __sDataRow;

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
		
		var fechaini = $('#fechaini').datepicker({
			todayHighlight:true,
			autoclose: true,
			clearBtn: true
		}).data('datepicker');
		var fechafin = $('#fechafin').datepicker({
			todayHighlight:true,
			autoclose: true,
			clearBtn: true
		}).data('datepicker');
		
		$("#clientecontabilidad").select2({
			theme: "bootstrap",
			width: "off",
			placeholder: "Seleccione un Cliente",
		});	

		$("input[type=radio][name=optradio_email]").change(function() {
			if (this.value == 'unico') {
				$('#modalenviar_cuenta_email input[value=xml]').prop('checked', false);
				$('#modalenviar_cuenta_email input[value=xml]').prop('disabled', true);
			} else  {
				$('#modalenviar_cuenta_email input[value=xml]').prop('disabled', false);
			}
		});
		
		fcn_cargar_grid_ctamex();
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

function fcn_cargar_grid_ctamex(){
	try {
		if (oTableCuentasGrid == null) {
			var div_refresh_name = 'div_ctamex_refresh';
			var div_table_name = 'ctamex';
					
			oTableCuentasGrid = $('#' + div_table_name);
			oTableCuentasGrid.dataTable({
				"order": [ 6, 'dsc' ],
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "./postctamex.php",
					"type": "POST",
					"data": function ( d ) {
						d.fechaini = $('#fechaini1').val();
						d.fechafin = $('#fechafin1').val();
						d.estatus_pago = $('#selestatus').val();
						d.idclicont = (($('#clientecontabilidad').val() == '')? '-1' : $('#clientecontabilidad').val());
					}
				},
				"columns": [
					{   // Responsive control column
						data: null,
						defaultContent: '',
						className: 'control',
						orderable: false
					},
					{   // Checkbox select column
						data: null,
						defaultContent: '',
						className: 'select-checkbox',
						orderable: false
					},
					{ "data": "no_mov" },
					{ "data": "envio_cliente" },
					{ "data": "trafico" },
					{ "data": "fecha" },
					{ "data": "no_mov",
						className: "text-center",
						"mRender": function (data, type, full) {
							return '<a href="dxml.php?nxml='+data+'.xml" target="_blank"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></a>';
						}
					},
					{ "data": "no_mov",
						className: "text-center",
						"mRender": function (data, type, full) {
								return '<a href="dpdf.php?npdf='+data+'.pdf" target="_blank"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></a>';
						}
					},
					{ "data": "aduana",
						className: "text-center",
						"mRender": function (data, type, full) {
								return data;
						}
					},
					{ "data": "pedimentos",
						className: "text-center",
						"mRender": function (data, type, full) {
								return data;
						}
					},
					{ "data": "edocuments",
						className: "text-center",
						"mRender": function (data, type, full) {
								return data;
						}
					},
					{ "data": "anexos"},
					{ "data": "tipo_mov",
						className: "text-center",
						"mRender": function (data, type, full) {
								return data;
						}
					},
					{ "data": "no_mov",
						className: "text-center",
						"mRender": function (data, type, full) {
							return '<a class="editor_ctamex_enviar"><span class="glyphicon glyphicon-send" aria-hidden="true"></span></a>';
						}
					},
					{ "data": "deuda",
						"mRender": function (data, type, full) {
								return '$'+data;
						}
					},
					{ "data": "pedimentos1",
						"className": "never"
					},
					{ "data": "pedimento",
						"className": "never"
					},
					{ "data": "estatus" }
				],
				"buttons": [
					{
						extend: 'colvis',
						text: 'Visualizar columnas'
					},
					{
						extend: 'copyHtml5',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'excelHtml5',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'csvHtml5',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'pdfHtml5',
						orientation: 'landscape',
						pageSize: 'LEGAL',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'print',
						exportOptions: {
							columns: ':visible'
						}
					},
					{
						extend: 'selected',
						text: 'Enviar selección',
						action: function ( e, dt, node, config ) {
							$("#mdlEnvio1OmiteClientes").prop('checked', false);
							$("#mdlEnvio1Correos").val('');
							$("#mdlEnvioDivMsg1").css('display','none');
							$("#modalEnvio1").modal();
						}
					},
					{
						text: 'Forzar pendientes',
						action: function ( e, dt, node, config ) {
							ajax_forzar_pendientes();
						}
					},
					{
						text: 'Enviar listado pendientes',
						action: function ( e, dt, node, config ) {
							$("#mdlEnvioListPenCorreos").val('');
							$("#mdlEnvioListPenDivMsg1").css('display','none');
							$("#modalEnvioListPen").modal();
						}
					}
				],
				"dom": '<"top"r>fBt<"bottom"lpi><"clear">',
				"sScrollX": '100%',
				responsive: true,
				"language": {
					"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36">Consultando información...',
					"sLengthMenu":     "Mostrar _MENU_ registros",
					"sZeroRecords":    "No se encontraron resultados",
					"sEmptyTable":     "Ningún dato disponible en esta tabla",
					"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
					"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
					"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
					"sInfoPostFix":    "",
					"sSearch":         "Buscar:",
					"sUrl":            "",
					"sInfoThousands":  ",",
					"sLoadingRecords": "Cargando...",
					"oPaginate": {
						"sFirst":    "Primero",
						"sLast":     "Último",
						"sNext":     "Siguiente",
						"sPrevious": "Anterior"
					},
					"oAria": {
						"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
						"sSortDescending": ": Activar para ordenar la columna de manera descendente"
					}
				},
				select: {
					style:    'multi',
					selector: 'td.select-checkbox'
				}
			});
			
			oTableCuentasGrid.on('click', 'a.editor_ctamex_enviar', function (e) {
				try {		
					__sDataRow = fcn_get_row_data($(this), oTableCuentasGrid);
					
					if (__sDataRow.trafico == null || __sDataRow.trafico == undefined) { __sDataRow.trafico = ''; }					
					if (__sDataRow.referencias == null || __sDataRow.referencias == undefined) { __sDataRow.referencias = ''; }
					
					$("#modalenviar_cuenta_email").modal({ show: true });
					$('#modalenviar_cuenta_email input:checkbox').prop('checked', true);
					$("input[name=optradio_email][value=unico]").prop('checked', true);
					$('#modalenviar_cuenta_email input[value=xml]').prop('checked', false);
					$('#modalenviar_cuenta_email input[value=xml]').prop('disabled', true);
					
					show_custom_function_error('', 'idiv_mdl_enviar_cuenta_email_msj');
					
					fcn_fill_file_input();
				} catch (err) {		
					var strMensaje = 'a.editor_ctamex_enviar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
				
			oTableCuentasGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
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

/* ..:: Obtenemos los datos del row ::.. */
function fcn_get_row_data($this, oGrid) {
	var current_row = $this.parents('tr');//Get the current row
	if (current_row.hasClass('child')) {//Check if the current row is a child row
		current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
	}

	var oData = oGrid.DataTable().row(current_row).data();
	return oData;
}
		
/* ..:: Capturamos los errores ::.. */
function handleAjaxError( xhr, textStatus, error ) {
	if ( textStatus === 'timeout' ) {
		show_custom_function_error('El servidor tardó demasiado en enviar los datos', 'idiv_mensaje');	
	} else {
		show_custom_function_error('Se ha producido un error en el servidor. Por favor espera.', 'idiv_mensaje');	
		
		setTimeout(function(){ hide_message(); }, 5000);
	}
}

function on_grid_error(e, settings, techNote, message) {
	var bExist = message.includes("Code [500]");
	if(bExist) {
		show_custom_function_error(strSessionMessage, 'idiv_mensaje');					
		setTimeout(function () {window.location.replace('../logout.php');},4000);
	} else {
		show_custom_function_error('Ha ocurrido un error: ' + message, 'idiv_mensaje');
		setTimeout(function(){ hide_message(); }, 5000);
	}
}

/*********************************************************************************************************************************
** CONTROLS FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

function filtrafecha(){
	var table = $('#ctamex').DataTable();
	table.ajax.reload(null, true);
}

/* ..:: Validamos el correo electronico ::.. */
function fcn_validate_email(email) {
	var email_regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i;
	if(!email_regex.test(email)) {
		return false;
	} else{
		return true;
	}
}

/* ..:: Creamos el objeto FileInput y lo llenamos de datos ::.. */
function fcn_fill_file_input() {
	try {
		$("#ifile_documentos").fileinput('destroy');
		//$("#ifile_documentos").fileinput('clear');
		$("#ifile_documentos").fileinput({
			language: "es",
			uploadUrl: "N/A",
			uploadAsync: false,
			showRemove: false,
			showUpload: false,
			minFileCount: 1,
			fileActionSettings: {
				showUpload: false
			}
		});
	} catch (err) {
		var strMensaje = 'fcn_fill_file_input() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_mensaje');
    }
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

function ajax_enviar_list_pendientes(){
	correos = $('#mdlEnvioListPenCorreos').val();
	$.ajax({
		type: "POST",
		url: 'ajax/listctamex/enviarListado.php',
		data: {
			correos: correos
		},
		timeout: 30000,
		beforeSend: function (dataMessage) {
			show_custom_function_info('Enviando listado, espere un momento por favor...', 'mdlEnvioListPenDivMsg1', 'margin-bottom: 0px;');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				show_custom_function_info('', 'mdlEnvioListPenDivMsg1');
				if (respuesta.Codigo == '1'){
					show_custom_function_ok(respuesta.Mensaje, 'mdlEnvioListPenDivMsg1', 'margin-bottom: 0px;');
					//table.ajax.reload(null, true);
				}else{
					var strMensaje = respuesta.Mensaje;
					show_custom_function_error(strMensaje, 'mdlEnvioListPenDivMsg1', 'margin-bottom: 0px;');
				}	
			}else{
				show_custom_function_info('La sesión del usuario ha caducado, por favor acceda de nuevo.', 'mdlEnvioListPenDivMsg1', 'margin-bottom: 0px;');
				setTimeout(function () {window.location.replace('../logout.php');},4000);
			}	
			
		},
		error: function(a,b){
			var strMensaje = a.status+' [' + a.statusText + ']';
			show_custom_function_info(strMensaje, 'modalEnvioListPen', 'margin-bottom: 0px;');
		}
	});
}

function ajax_forzar_pendientes(){
	$.ajax({
		type: "POST",
		url: 'ajax/listctamex/forzarPendientes.php',
		data: {
			
		},
		timeout: 30000,
		beforeSend: function (dataMessage) {
			show_custom_function_info('Enviando correo(s), espere un momento por favor...', 'idiv_mensaje', 'margin-bottom: 0px;');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				show_custom_function_info('', 'idiv_mensaje');
				if (respuesta.Codigo == '1'){
					show_custom_function_ok(respuesta.Mensaje, 'idiv_mensaje', 'margin-bottom: 0px;');
					//table.ajax.reload(null, true);
				}else{
					var strMensaje = respuesta.Mensaje;
					show_custom_function_error(strMensaje, 'idiv_mensaje', 'margin-bottom: 0px;');
				}	
			}else{
				show_custom_function_info('La sesión del usuario ha caducado, por favor acceda de nuevo.', 'idiv_mensaje', 'margin-bottom: 0px;');
				setTimeout(function () {window.location.replace('../logout.php');},4000);
			}	
			
		},
		error: function(a,b){
			var strMensaje = a.status+' [' + a.statusText + ']';
			show_custom_function_info(strMensaje, 'idiv_mensaje', 'margin-bottom: 0px;');
		}
	});
}

function ajax_envio1_cuentas_sel(){
	var table = $('#ctamex').DataTable();
	var cuentas_sel = [], correos, omite_cliente;
	table.rows({ selected: true }).every( function ( rowIdx, tableLoop, rowLoop ) {
		var data = this.data();
		cuentas_sel.push(' '+data.no_mov);
	} );
	correos = $('#mdlEnvio1Correos').val();
	omite_cliente = $('#mdlEnvio1OmiteClientes').is(":checked");
	$.ajax({
		type: "POST",
		url: 'ajax/listctamex/enviarCuentas.php',
		data: {
			cuentas: cuentas_sel,
			correos: correos,
			omite_cliente: omite_cliente
		},
		timeout: 30000,
		beforeSend: function (dataMessage) {
			show_custom_function_info('Enviando correo(s), espere un momento por favor...', 'mdlEnvioDivMsg1', 'margin-bottom: 0px;');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				show_custom_function_info('', 'mdlEnvioDivMsg1');
				if (respuesta.Codigo == '1'){
					show_custom_function_ok(respuesta.Mensaje, 'mdlEnvioDivMsg1', 'margin-bottom: 0px;');
					//table.ajax.reload(null, true);
				}else{
					var strMensaje = respuesta.Mensaje;
					show_custom_function_error(strMensaje, 'mdlEnvioDivMsg1', 'margin-bottom: 0px;');
				}	
			}else{
				show_custom_function_info('La sesión del usuario ha caducado, por favor acceda de nuevo.', 'mdlEnvioDivMsg1', 'margin-bottom: 0px;');
				setTimeout(function () {window.location.replace('../logout.php');},4000);
			}	
			
		},
		error: function(a,b){
			var strMensaje = a.status+' [' + a.statusText + ']';
			show_custom_function_info(strMensaje, 'mdlEnvioDivMsg1', 'margin-bottom: 0px;');
		}
	});
}

/* ..:: Enviamos Email con archivos ::.. */
function ajax_enviar_cuenta_email() {
	try {		
		show_custom_function_error('', 'idiv_mdl_enviar_cuenta_email_msj');
	
		var aPedimento = new Array();
		var aArchivos = new Array();
		var sAnexos = '';
		var sXml = '';
		var aHC = new Array();
		var aMV = new Array();	
		var aEmails = new Array();
		var sTipoEnvio = $("input[name=optradio_email]:checked").val();
		var sEmails = $('#itxt_mdl_enviar_cuenta_email_correos').val();
		if (sEmails == '') {
			show_custom_function_error('Debe ingresar un correo electronico.', 'idiv_mdl_enviar_cuenta_email_msj', 'margin-bottom: 0px;');
			return;
		} else {
			var aEmails = sEmails.split(";");
			$.each(aEmails, function(index, item) {
				var sEmail = $.trim(item);
				if ($.inArray(sEmail, aEmails) == -1) {
					if (fcn_validate_email(sEmail)) {
						aEmails.push(sEmail);
					} else {
						show_custom_function_error('Correo electronico ' + sEmail + ' incorrecto.', 'idiv_mdl_enviar_cuenta_email_msj', 'margin-bottom: 0px;');
						return false;
					}
				}
			});
		}
		
		if (aEmails.length == 0) {
			show_custom_function_error('Debe ingresar un correo electronico.', 'idiv_mdl_enviar_cuenta_email_msj', 'margin-bottom: 0px;');
			return;
		}
					
		$('#modalenviar_cuenta_email input:checked').each(function() {
			switch($(this).val()) {
				case 'pedimento':
					var aReferencias = __sDataRow.trafico.split(",");
					$.each(aReferencias, function(index, item) {
						var sReferencia = $.trim(item);
						if (sReferencia != '') {
							if ($.inArray(sReferencia, aPedimento) == -1) {
								aPedimento.push(sReferencia);
							}
						}
					});	
					
					var aReferencias = __sDataRow.referencias.split(",");
					$.each(aReferencias, function(index, item) {
						var sReferencia = $.trim(item);
						if (sReferencia != '') {
							if ($.inArray(sReferencia, aPedimento) == -1) {
								aPedimento.push(sReferencia);
							}
						}
					});							
					break;
				
				case 'anexos':
					sAnexos = $.trim(__sDataRow.trafico);
					break;
					
				case 'hc':
					var aReferencias = __sDataRow.referencias.split(",");
					$.each(aReferencias, function(index, item) {
						var sReferencia = $.trim(item);
						if (sReferencia != '') {
							if ($.inArray(sReferencia, aHC) == -1) {
								aHC.push(sReferencia);
							}
						}
					});
					
					var aReferencias = __sDataRow.trafico.split(",");
					$.each(aReferencias, function(index, item) {
						var sReferencia = $.trim(item);
						if (sReferencia != '') {
							if ($.inArray(sReferencia, aHC) == -1) {
								aHC.push(sReferencia);
							}
						}
					});	
					break;
					
				case 'mv':
					var aReferencias = __sDataRow.referencias.split(",");
					$.each(aReferencias, function(index, item) {
						var sReferencia = $.trim(item);
						if (sReferencia != '') {
							if ($.inArray(sReferencia, aMV) == -1) {
								aMV.push(sReferencia);
							}
						}
					});
					
					var aReferencias = __sDataRow.trafico.split(",");
					$.each(aReferencias, function(index, item) {
						var sReferencia = $.trim(item);
						if (sReferencia != '') {
							if ($.inArray(sReferencia, aMV) == -1) {
								aMV.push(sReferencia);
							}
						}
					});	
					break;
					
				case 'xml':
					sXml = __sDataRow.no_mov;
					break;
			}
		});
		
		var oData = new FormData();	
		
		//var oDocs = $('#ifile_documentos').fileinput('getFileStack');
		$.each($('#ifile_documentos').fileinput('getFileStack'), function(i, file) {
			oData.append('file-'+i, file);
		});

	  
		/*$.each(oDocs, function(index, item) {
			//odata.append('img_entrada' + i, document.getElementById('upload_fotografias').files[i]);
			aArchivos.push(item);
		});*/
		
		oData.append('sNoMov', __sDataRow.no_mov);
		oData.append('sPatente', $.trim(__sDataRow.patente));
		oData.append('sPedimento', $.trim(__sDataRow.pedimento));
		oData.append('sTipoEnvio', sTipoEnvio);
		oData.append('sAnexos', sAnexos);
		oData.append('sXml', sXml);
		oData.append('aEmails', JSON.stringify(aEmails));
		oData.append('aPedimento', JSON.stringify(aPedimento));
		oData.append('aHC', JSON.stringify(aHC));
		oData.append('aMV', JSON.stringify(aMV));
		oData.append('sNoMov', __sDataRow.no_mov);
		//oData.append('aArchivos', aArchivos);
		
		/*var oData = {			
			sNoMov: __sDataRow.no_mov,
			sPatente: $.trim(__sDataRow.patente),
			sPedimento: $.trim(__sDataRow.pedimento),
			sTipoEnvio: sTipoEnvio,
			sAnexos: sAnexos,
			sXml: sXml,
			aEmails: JSON.stringify(aEmails),
			aPedimento: JSON.stringify(aPedimento),
			aHC: JSON.stringify(aHC),
			aMV: JSON.stringify(aMV),
			aArchivos: aArchivos
		};*/
		
		$.ajax({
			type: "POST",
			url: 'ajax/listctamex/enviarExpediente.php',
			data: oData,
			contentType: false,
			cache: false,
			processData:false,
			timeout: 30000,

			beforeSend: function (dataMessage) {
				show_custom_function_info('Enviando correo(s), espere un momento por favor...', 'idiv_mdl_enviar_cuenta_email_msj', 'margin-bottom: 0px;');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_custom_function_info('', 'idiv_mdl_enviar_cuenta_email_msj');
					if (respuesta.Codigo == '1'){
						show_custom_function_ok('Correo enviado correctamente.', 'idiv_mdl_enviar_cuenta_email_msj', 'margin-bottom: 0px;');
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_custom_function_info(strMensaje, 'idiv_mdl_enviar_cuenta_email_msj', 'margin-bottom: 0px;');
					}
				}else{
					show_custom_function_info('La sesión del usuario ha caducado, por favor acceda de nuevo.', 'idiv_mdl_enviar_cuenta_email_msj', 'margin-bottom: 0px;');
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_custom_function_info(strMensaje, 'idiv_mdl_enviar_cuenta_email_msj', 'margin-bottom: 0px;');
			}
		});
	} catch (err) {
		var strMensaje = 'ajax_enviar_cuenta_email() :: ' + err.message;
		show_custom_function_info(strMensaje, 'idiv_mdl_enviar_cuenta_email_msj', 'margin-bottom: 0px;');
	}    
}
		
/*********************************************************************************************************************************
** DOWNLOAD FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

/*********************************************************************************************************************************
** MESSAJE FUNCTIONS                                                                                                            **
*********************************************************************************************************************************/

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