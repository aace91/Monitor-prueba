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

var oPrincipalGeneralGrid = null;
var oSecundarioDetalleGrid = null;

var __sReferencia = '';
var __sIdPlantilla = '';
var __sIdDetalle = '';

var __bReferenciaValida = false;
var __oPanelPrincipal = $('#idiv_panel_principal');
var __oPanelSecundario = $('#idiv_panel_secundario');

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
		
		$("#ifile_modal_subir_excel_archivo").fileinput('clear');
		$("#ifile_modal_subir_excel_archivo").fileinput('refresh', {
			showUpload: false,
			uploadClass: "btn btn-success",
			uploadLabel: "Subir",
			uploadUrl: function () { alert(); },
			//browseClass: "btn btn-success",
			browseLabel: "Buscar ...",
			//browseIcon: "<i class=\"glyphicon glyphicon-camera\"></i> ",
			removeClass: "btn btn-danger",
			removeLabel: "Eliminar",
			removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i> ",
			showPreview: false,
			allowedFileExtensions: ['xls', 'xlsx']
		});

		$('#modalloadconfig, #modalmessagebox_ok, #modalmessagebox_error, #modalconfirm, #modal_info').on('hidden.bs.modal', function (e) {
			var oModalsOpen = $('.in');
			if (oModalsOpen.length > 0 ) {
				$('body').addClass('modal-open');
			}
		});

		$('#itxt_modal_subir_excel_referencia').focusout(function() { fcn_modal_subir_excel_verificar_pedimento(); });

		$('#itxt_modal_edit_reg_fecha').datepicker({
			format: 'dd/mm/yyyy',
			language: "es",
			autoclose: true
		});

		var oTouchSpinProp = {
			verticalbuttons: true,
			min: 0,
			max: 1000000000,
			step: 0.1,
            decimals: 2
		}
		$("#itxt_modal_edit_reg_monto").TouchSpin(oTouchSpinProp);
		$("#itxt_modal_edit_reg_precio_partida").TouchSpin(oTouchSpinProp);
		$("#itxt_modal_edit_reg_cantidad_umc").TouchSpin(oTouchSpinProp);
		$("#itxt_modal_edit_reg_cantidad_umt").TouchSpin(oTouchSpinProp);

		fcn_cargar_grid_principal_general();		
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

function fcn_cargar_grid_principal_general() {
	try {
		if (oPrincipalGeneralGrid == null) {
			var div_refresh_name = 'div_dt_principal_general_refresh';
			var div_table_name = 'dt_principal_general';
			
			oPrincipalGeneralGrid = $('#' + div_table_name);
			
			oPrincipalGeneralGrid.DataTable({
				order: [[1, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/exposPlantilla/postPlantillaGeneral.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
						__sReferencia = $('#itxt_principal_referencia').val();
			            d.sReferencia = __sReferencia;
					},
					"beforeSend": function (request) {
						$('#idiv_principal_agregar_emb').hide();
						//hide_message();
					},
					"complete": function (request) {
						if(oPrincipalGeneralGrid.DataTable().data().length > 0) {
							$('#idiv_principal_agregar_emb').show();
							$('[data-toggle="tooltip"]').tooltip(); 
						}
					},
					"error": handleAjaxError
				},
				columns: [ 
					{ data: "id_embarque", className: "def_app_center"},
					{ data: "fecha", className: "def_app_center"},
					{ data: "registros", className: "def_app_center"},
					{
						data: null,
						className: "def_app_center",
						render: function ( data, type, row ) {
							var sHtml = '';
							sHtml += '<a class="btn btn-default btn-xs editor_dt_principal_general_exportar" data-toggle="tooltip" title="Generar Layout"><i class="fa fa-file-excel-o" aria-hidden="true"></i></a>';
							sHtml += '&nbsp;&nbsp;';

							sHtml += '<a class="btn btn-default btn-xs editor_dt_principal_general_eliminar" data-toggle="tooltip" title="Eliminar Embarque"><i class="fa fa-trash" aria-hidden="true"></i></a>';
							sHtml += '&nbsp;&nbsp;';

							sHtml += '<a class="btn btn-default btn-xs editor_dt_principal_general_editar" data-toggle="tooltip" title="Editar Embarque"><i class="fa fa-pencil" aria-hidden="true"></i></a>';

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
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Lista de salidas</h2>',
						exportOptions: {
							columns: [ 0, 1, 2 ]
						}
					}
				]
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_principal_general();"');
			$("div." + div_refresh_name).html(sButton);
				
			oPrincipalGeneralGrid.on('click', 'a.editor_dt_principal_general_exportar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oPrincipalGeneralGrid);

					__sIdPlantilla = oData.id_plantilla;	

					fcn_descargar_plantilla5();
					/*__IdSalida = oData.salidanumero;
					__IdCliente	= oData.id_cliente;
					__sCaja = oData.caja;
					__IdLogistica = oData.idlogistica;

					fcn_salida_logistica();*/
				} catch (err) {		
					var strMensaje = 'editor_dt_principal_general_exportar() :: ' + err.message;
					show_message_error(strMensaje);
				}  
			} );

			oPrincipalGeneralGrid.on('click', 'a.editor_dt_principal_general_eliminar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oPrincipalGeneralGrid);

					__sIdPlantilla = oData.id_plantilla;					
					var sIdEmbaque = oData.id_embarque;

					var strTitle = 'Eliminar Embarque';
					var strQuestion = 'Desea Eliminar el Embarque [' + sIdEmbaque + ']?';
					var oFunctionOk = function () { ajax_set_eliminar_embarque(); };
					var oFunctionCancel = null;
					show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
				} catch (err) {		
					var strMensaje = 'editor_dt_principal_general_eliminar() :: ' + err.message;
					show_message_error(strMensaje);
				}  
			} );

			oPrincipalGeneralGrid.on('click', 'a.editor_dt_principal_general_editar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oPrincipalGeneralGrid);

					__sIdPlantilla = oData.id_plantilla;
					fcn_principal_detalles();
				} catch (err) {		
					var strMensaje = 'editor_dt_principal_general_editar() :: ' + err.message;
					show_message_error(strMensaje);
				}  
			} );

			oPrincipalGeneralGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message);
			} );
		} else {
			var table = oPrincipalGeneralGrid.DataTable();
			table.ajax.reload(null, false);	
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_principal_general() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_cargar_grid_secundario_detalle() {
	try {
		if (oSecundarioDetalleGrid == null) {
			var div_refresh_name = 'div_dt_secundario_detalles_refresh';
			var div_table_name = 'dt_secundario_detalles';
			
			oSecundarioDetalleGrid = $('#' + div_table_name);
			
			oSecundarioDetalleGrid.removeAttr('width').DataTable({
				order: [[12, 'asc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/exposPlantilla/postPlantillaDetalles.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
			            d.sIdPlantilla = __sIdPlantilla;
					},
					"error": handleAjaxError
				},
				//scrollY: "400px",
				scrollX:true,
				fixedColumns: {
					leftColumns: 1
				},
				columns: [ 
					{
						data: null,
						className: "def_app_center",
						defaultContent: '<a class="btn btn-default btn-xs editor_dt_principal_general_editar"><i class="fa fa-pencil" aria-hidden="true"></i></a>'
					},
					{ data: "id_proveedor", className: "def_app_center"},
					{ data: "no_factura", className: "def_app_center"},
					{ data: "fecha_factura", className: "def_app_center"},
					{ data: "monto_factura", className: "def_app_center"},
					{ data: "moneda", className: "def_app_center"},
					{ data: "incoterm", className: "def_app_center"},
					{ data: "subdivision", className: "def_app_center"},
					{ data: "certificado", className: "def_app_center"},
					{ data: "no_parte", className: "def_app_center"},
					{ data: "origen", className: "def_app_center"},
					{ data: "vendedor", className: "def_app_center"},
					{ data: "fraccion", className: "def_app_center"},
					{ data: "descripcion" },
					{ data: "precio_partida", className: "def_app_center"},
					{ data: "umc", className: "def_app_center"},
					{ data: "cantidad_umc", className: "def_app_center"},
					{ data: "cantidad_umt", className: "def_app_center"},
					{ data: "preferencia", className: "def_app_center"},
					{ data: "marca", className: "def_app_center"},
					{ data: "modelo", className: "def_app_center"},
					{ data: "submodelo", className: "def_app_center"},
					{ data: "serie", className: "def_app_center"},
					{ data: "descripcion_cove" }
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
					{
						extend: 'excelHtml5',
						text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel',
						exportOptions: {
							columns: [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23 ]
						}
					}
				]
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_secundario_detalle();"');
			$("div." + div_refresh_name).html(sButton);
				
			oSecundarioDetalleGrid.on('click', 'a.editor_dt_principal_general_editar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oSecundarioDetalleGrid);

					__sIdDetalle = oData.id_detalle;
					fcn_modal_show_edit_reg(oData);
				} catch (err) {		
					var strMensaje = 'editor_dt_principal_general_editar() :: ' + err.message;
					show_message_error(strMensaje);
				}  
			} );

			oSecundarioDetalleGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message);
			} );
		} else {
			var table = oSecundarioDetalleGrid.DataTable();
			table.ajax.reload(null, false);	
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_secundario_detalle() :: ' + err.message;
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
		show_message_error('El servidor tardó demasiado en enviar los datos');
	} else {
		show_message_error('Se ha producido un error en el servidor. Por favor espera.');
		
		setTimeout(function(){ hide_message(); }, 5000);
	}
}

/* ..:: Capturamos los errores ::.. */
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

/*===============================*/
/* SUBIR ARCHIVO */
/*===============================*/

/* ..:: mostramos modal para subir archivo ::.. */
function fcn_modal_show_subir_excel(pAccion) {
	try {	
		__bReferenciaValida = false;

		$('#idiv_modal_subir_excel_mensaje').hide();
		$('#ifile_modal_subir_excel_archivo').fileinput('clear');	
		$('#ifile_modal_subir_excel_archivo').fileinput('refresh');
		
		$('#itxt_modal_subir_excel_referencia').val('');
		$('#ifile_modal_subir_excel_archivo').val('');
		$('#ilabel_modal_subir_excel_verify').empty();
		$('#ilabel_modal_subir_excel_clave_ped').empty();
		
		if (pAccion == 'agregar') {
			__bReferenciaValida = true;
			$('#itxt_modal_subir_excel_referencia').val(__sReferencia);
			$('#itxt_modal_subir_excel_referencia').attr('disabled', true);
		} else {
			$('#itxt_modal_subir_excel_referencia').attr('disabled', false);
		}

		$('#modal_subir_excel').modal({
			show: true,
			backdrop: 'static',
			keyboard: false
		});
	} catch (err) {
		var strMensaje = 'fcn_modal_show_subir_excel() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/* ..:: Validar Pedimento ::.. */
function fcn_modal_subir_excel_verificar_pedimento() {
	try {		
		ajax_get_verificar_referencia();
	} catch (err) {
		var strMensaje = 'fcn_modal_subir_excel_verificar_pedimento() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/* ..:: Subir archivo ::.. */
function fcn_modal_subir_excel_upload() {
	try {		
		var sHtml = '';
		$('#idiv_modal_subir_excel_mensaje').html(sHtml).hide();

		if (__bReferenciaValida) {
			var oXls = document.getElementById('ifile_modal_subir_excel_archivo');
			if (oXls.files[0]){
				var sReferencia = $('#itxt_modal_subir_excel_referencia').val().toUpperCase();
				ajax_set_archivo_excel(sReferencia, oXls.files[0]);
			} else {
				sHtml =	fcn_get_message_error('Debes seleccionar un archivo');
			}
		} else {
			sHtml =	fcn_get_message_error('Referencia Invalida');
		}
			
		if (sHtml != '') {
			$('#idiv_modal_subir_excel_mensaje').html(sHtml).show();
		}
	} catch (err) {
		var strMensaje = 'fcn_modal_subir_excel_upload() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}


/*===============================*/
/* PRINCIPAL */
/*===============================*/

/* ..:: Ingresamos al panel secundario ::.. */
function fcn_principal_detalles() {
	try {		
		$('#itxt_secundario_referencia').val(__sReferencia);

		__oPanelSecundario.show();
		__oPanelPrincipal.hide();

		fcn_cargar_grid_secundario_detalle();
	} catch (err) {
		var strMensaje = 'fcn_secundario_regresar() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*===============================*/
/* SECUNDARIO */
/*===============================*/

/* ..:: Regresar al panel principal ::.. */
function fcn_secundario_regresar() {
	try {		
		__oPanelSecundario.hide();
		__oPanelPrincipal.show();
	} catch (err) {
		var strMensaje = 'fcn_secundario_regresar() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*===============================*/
/* SECUNDARIO - EDITAR REGISTRO */
/*===============================*/
function fcn_modal_show_edit_reg(oData) {
	try {	
		$('#itxt_modal_edit_reg_fecha').datepicker('clearDates');
		$("#itxt_modal_edit_reg_fecha").datepicker("update", oData.fecha_factura);

		$('#itxt_modal_edit_reg_cve_prov').val(oData.id_proveedor);
		$('#itxt_modal_edit_reg_factura').val(oData.no_factura);
		//$('#itxt_modal_edit_reg_fecha').val(oData.fecha_factura);
		$('#itxt_modal_edit_reg_monto').val(oData.monto_factura);
		$('#itxt_modal_edit_reg_moneda').val(oData.moneda);
		$('#itxt_modal_edit_reg_incoterm').val(oData.incoterm);
		$('#itxt_modal_edit_reg_subdivision').val(oData.subdivision);
		$('#itxt_modal_edit_reg_certificado').val(oData.certificado);
		$('#itxt_modal_edit_reg_no_parte').val(oData.no_parte);
		$('#itxt_modal_edit_reg_origen').val(oData.origen);
		$('#itxt_modal_edit_reg_vendedor').val(oData.vendedor);
		$('#itxt_modal_edit_reg_fraccion').val(oData.fraccion);
		$('#itxt_modal_edit_reg_descripcion').val(oData.descripcion);
		$('#itxt_modal_edit_reg_precio_partida').val(oData.precio_partida);
		$('#itxt_modal_edit_reg_umc').val(oData.umc);
		$('#itxt_modal_edit_reg_cantidad_umc').val(oData.cantidad_umc);
		$('#itxt_modal_edit_reg_cantidad_umt').val(oData.cantidad_umt);
		$('#itxt_modal_edit_reg_preferencia').val(oData.preferencia);
		$('#itxt_modal_edit_reg_marca').val(oData.marca);
		$('#itxt_modal_edit_reg_modelo').val(oData.modelo);
		$('#itxt_modal_edit_reg_submodelo').val(oData.submodelo);
		$('#itxt_modal_edit_reg_serie').val(oData.serie);
		$('#itxt_modal_edit_reg_descripcion_cove').val(oData.descripcion_cove);

		$('#modal_edit_reg').modal({
			show: true,
			backdrop: 'static',
			keyboard: false
		});
	} catch (err) {
		var strMensaje = 'fcn_modal_show_edit_reg() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/*===============================*/
/* SUBIR ARCHIVO */
/*===============================*/

/* ..:: Verificamos que la referencia exista en casa y en mysql ::.. */
function ajax_get_verificar_referencia() {
	try {	
		__bReferenciaValida = false;

		var sReferencia = $('#itxt_modal_subir_excel_referencia').val().toUpperCase();
		if (sReferencia.trim()) {
			var oData = {			
				sReferencia: sReferencia
			};
			
			$.ajax({
	            type: "POST",
	            url: 'ajax/exposPlantilla/ajax_get_verificar_referencia.php',
				data: oData,
				timeout: 30000,
				
	            beforeSend: function (dataMessage) {
	            	$('#ilabel_modal_subir_excel_verify').html('<span style="color:#31708f;"><i class="fa fa-refresh fa-spin fa-fw"></i> Verificando</span>');
	            	$('#ilabel_modal_subir_excel_clave_ped').empty();
	            },
	            success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);

						var $oVerifyLabel = $('#ilabel_modal_subir_excel_verify');
						var $oClavePedLabel = $('#ilabel_modal_subir_excel_clave_ped');

						$oVerifyLabel.empty();
						$oClavePedLabel.empty();
						if (respuesta.Codigo == '1'){						
							if (respuesta.bExisteCasa == true) {
								__bReferenciaValida = true;
								if (respuesta.bExisteMysql == true) { 
									$oVerifyLabel.html('<span style="color:#8a6d3b;"><i class="fa fa-exclamation-circle"></i> Se agrega como nuevo Embarque</span>');
								} else {
									$oVerifyLabel.html('<span style="color:#3c763d;"><i class="fa fa-check-circle"></i> Valido</span>');
								}
								$oClavePedLabel.html('<span style="color:#337ab7;">CVE PED: '+respuesta.sClavePedimento+'</span>');
							} else {
								$oVerifyLabel.html('<span style="color:#a94442;"><i class="fa fa-times-circle"></i> No existe en CASA</span>');							
							}
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
		}
    } catch (err) {
		var strMensaje = 'ajax_get_verificar_referencia() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/* ..:: Subimos el archivo de excel ::.. */
function ajax_set_archivo_excel(sReferencia, oXls) {
	try {
		__sReferencia = sReferencia;

		var oData = new FormData();	
		oData.append('sReferencia', sReferencia);
		oData.append('oXls', oXls);

		$.ajax({
            type: "POST",
            url: 'ajax/exposPlantilla/ajax_set_archivo_excel.php',
            data: oData,
			contentType: false,
			cache: false,
			processData:false,
			timeout: 0,
			xhr: function() {
				$('#modal_info').modal({
					show: true,
					backdrop: 'static',
					keyboard: false
				});
				
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt){
				  if (evt.lengthComputable) {
					var percent = evt.loaded / evt.total * 100;
					if(percent > 89) 
						percent = 90;
					var sMen = '<div class="progress progress-striped active">';
					sMen += '		<div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
					sMen += '			<span>'+percent+'% Completed, Procesando Archivo, espere un momento por favor...</span>';
					sMen += '		</div>';
					sMen += '	</div>';
					$('#idiv_modal_info_mensaje').html(sMen);
				  }
				}, false);
				return xhr;
			},
            beforeSend: function (dataMessage) {
				// $('#idiv_armar_pal_aceptar').hide();
				// $('#ibtn_armar_pal_subir').attr('disabled', true);
            },
            success:  function (response) {
            	$('#modal_info').modal('hide');		

				if (response != '500'){
					var respuesta = JSON.parse(response);
					
					if (respuesta.Codigo == '1'){
						$('#modal_subir_excel').modal('hide');

						$('#itxt_principal_referencia').val(__sReferencia);
						fcn_cargar_grid_principal_general();

						setTimeout(function () {
							show_modal_ok('Archivo procesado correctamente: Total de registros guardados [' + respuesta.nTotalRegistros + '], <strong>Clave Pedimento: ' + respuesta.sClavePedimento + '</strong>');							
						},500);
					} else {
						$('#modal_info').modal('hide');	
						
						var strMensaje = respuesta.Mensaje;
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
				
				$('#modal_info').modal('hide');	
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_archivo_excel() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/*===============================*/
/* SECUNDARIO */
/*===============================*/

/* ..:: Eliminamos el embarque ::.. */
function ajax_set_eliminar_embarque() {
	try {	
		var oData = {	
			sIdPlantilla: __sIdPlantilla
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/exposPlantilla/ajax_set_eliminar_embarque.php',
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
						//$('#modal_edit_reg').modal('hide');

						show_modal_ok(respuesta.Mensaje);
						fcn_cargar_grid_principal_general();
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
		var strMensaje = 'ajax_set_editar_registro() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*===============================*/
/* SECUNDARIO - EDITAR REGISTRO */
/*===============================*/

/* ..:: Modificar registro ::.. */
function ajax_set_editar_registro() {
	try {	
		var sCveProv = $('#itxt_modal_edit_reg_cve_prov').val();
		var sFactura = $('#itxt_modal_edit_reg_factura').val();
		var sFecha = $('#itxt_modal_edit_reg_fecha').val();
		var sMonto = $('#itxt_modal_edit_reg_monto').val();
		var sMoneda = $('#itxt_modal_edit_reg_moneda').val();
		var sIncoterm = $('#itxt_modal_edit_reg_incoterm').val();
		var sSubdivision = $('#itxt_modal_edit_reg_subdivision').val();
		var sCertificado = $('#itxt_modal_edit_reg_certificado').val();
		var sNoParte = $('#itxt_modal_edit_reg_no_parte').val();
		var sOrigen = $('#itxt_modal_edit_reg_origen').val();
		var sVendedor = $('#itxt_modal_edit_reg_vendedor').val();
		var sFraccion = $('#itxt_modal_edit_reg_fraccion').val();
		var sDescripcion = $('#itxt_modal_edit_reg_descripcion').val();
		var sPrecioPartida = $('#itxt_modal_edit_reg_precio_partida').val();
		var sUMC = $('#itxt_modal_edit_reg_umc').val();
		var sCantUMC = $('#itxt_modal_edit_reg_cantidad_umc').val();
		var sCantUMT = $('#itxt_modal_edit_reg_cantidad_umt').val();
		var sPreferencia = $('#itxt_modal_edit_reg_preferencia').val();
		var sMarca = $('#itxt_modal_edit_reg_marca').val();
		var sModelo = $('#itxt_modal_edit_reg_modelo').val();
		var sSubmodelo = $('#itxt_modal_edit_reg_submodelo').val();
		var sSerie = $('#itxt_modal_edit_reg_serie').val();
		var sDescripcionCove = $('#itxt_modal_edit_reg_descripcion_cove').val();

		var oData = {	
			sIdDetalle: __sIdDetalle,		
			sCveProv: sCveProv,	
			sFactura: sFactura,	
			sFecha: sFecha,	
			sMonto: sMonto,	
			sMoneda: sMoneda,	
			sIncoterm: sIncoterm,	
			sSubdivision: sSubdivision,	
			sCertificado: sCertificado,	
			sNoParte: sNoParte,	
			sOrigen: sOrigen,	
			sVendedor: sVendedor,	
			sFraccion: sFraccion,	
			sDescripcion: sDescripcion,	
			sPrecioPartida: sPrecioPartida,	
			sUMC: sUMC,	
			sCantUMC: sCantUMC,	
			sCantUMT: sCantUMT,
			sPreferencia: sPreferencia,	
			sMarca: sMarca,	
			sModelo: sModelo,	
			sSubmodelo: sSubmodelo,	
			sSerie: sSerie,	
			sDescripcionCove: sDescripcionCove
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/exposPlantilla/ajax_set_editar_registro.php',
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
						$('#modal_edit_reg').modal('hide');

						show_modal_ok(respuesta.Mensaje);
						fcn_cargar_grid_secundario_detalle();
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
		var strMensaje = 'ajax_set_editar_registro() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*********************************************************************************************************************************
** DOWNLOAD FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

function fcn_descargar_plantilla5() {
	var data = { 
		sIdPlantilla: __sIdPlantilla
	};

	var oForm = document.createElement("form");
	oForm.target = 'data';
	oForm.method = 'POST'; // or "post" if appropriate
	oForm.action = 'ajax/exposPlantilla/dwn_plantilla5.php';

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

function fcn_get_message_error(sMensaje) {
	var sHtml = '<div class="alert alert-danger" style="margin-bottom:0px;">';
	sHtml +=	'	<strong>Error!</strong> ' + sMensaje;
	sHtml +=    '</div>';

	return sHtml;
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
		var sHtml = '<div class="alert alert-info" style="margin-top: 8px; margin-bottom: 8px;">';
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
		var sHtml = '<div class="alert alert-success" style="margin-top: 8px; margin-bottom: 8px;">';
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
		var sHtml = '<div class="alert alert-danger" style="margin-top: 8px; margin-bottom: 8px;">';
		sHtml +=	'	<strong>Error!</strong> ' + sMensaje;
		sHtml +=    '</div>';
		
		$('#idiv_message').html(sHtml);
		$('#idiv_message').show();
	}
}