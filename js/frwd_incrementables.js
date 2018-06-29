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

var __aAppData;

var oCrucesRemGrid = null;
var oRemisionCruceGrid = null;

var __nIdCruce;
var __sRecalcularIncMsg = '';

var __sIdEjecutivo;
var __sIdCliente;
var __nReferencias;
var __nFechaCajaEntrada;
var __sTipoSalida;

var __oTrDtDelete;
var __nReferenciasDelete; //Para saber cuantas referencias voy a eliminar
var __nFechaCajaEntradaDelete; //Para saber entradas si efectivamente es consolidada o no
 

var __sDebug = ((window.location.pathname.indexOf("pruebas/") >= 0)? true : false);

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
		
		__aAppData = $('#itxt_data').data('app_data').aAppData;
		
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

		$('#modal_nuevo_cruce').on('shown.bs.modal', function (e) {
			$("#isel_nvo_cruce_clientes").select2({
				theme: "bootstrap",
				width: "off",
				placeholder: "Seleccione un Cliente",
			});

			fcn_cargar_grid_remision_cruce(new Array());
		});	

		$(".integer").numeric(false, function() { alert("Solo enteros"); this.value = ""; this.focus(); });

		$('[data-toggle="tooltip"]').tooltip();  
		fcn_cargar_grid_incrementables();
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

function fcn_cargar_grid_incrementables(bReloadPaging) {
	try {
		show_custom_function_error('', 'idiv_bwsr_mensaje');
		if (oCrucesRemGrid == null) {
			var oDivDisplayErrors = 'idiv_bwsr_mensaje';
			var div_table_name = 'dt_incrementables';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oCrucesRemGrid = $('#' + div_table_name);
			
			oCrucesRemGrid.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: true,
				columnDefs: [
					{ targets: [5, 6], orderable: false }
				],
				ajax: {
					"url": "ajax/facturacion/frwd_incrementables/incrementablesFunc.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
			            d.action = 'table_incrementables';
					},
					"error": handleAjaxError
				},
				columns: [ 
					{ "data": "id_cruce", "className": "text-center" },
					{ "data": "fecha_alta", "className": "text-center" },
					{ "data": "nombre_cliente" },
					{ "data": "remisiones" },
					{ "data": "ejecutivo" },
					{ "data": "total" },
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
				buttons: [
					{
						text: '<i class="fa fa-plus" aria-hidden="true"></i> Crear Cruce',
						className: 'btn-success',
		                action: function ( e, dt, node, config ) {
							fcn_show_crear_cruce();
		            	}
		            }
				]
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_incrementables(true);"');
			$("div." + div_refresh_name).html(sButton);
			
			oCrucesRemGrid.on('click', 'a.editor_' + div_table_name + '_ver', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oCrucesRemGrid);
					__nIdCruce = oData.id_cruce;

					fcn_show_detalles();
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oCrucesRemGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);

			var table = oCrucesRemGrid.DataTable();
			table.search('').ajax.reload(null, bReloadPaging);
			setTimeout(function(){ oCrucesRemGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_incrementables() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

function fcn_cargar_grid_remision_cruce(aData) {
	try {
		show_custom_function_error('', 'idiv_mdl_nvo_cruce_mensaje');
		if (oRemisionCruceGrid == null) {
			var oDivDisplayErrors = 'idiv_mdl_nvo_cruce_mensaje';
			var div_table_name = 'dt_remisiones';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oRemisionCruceGrid = $('#' + div_table_name);
			
			oRemisionCruceGrid.DataTable({
				order: [[0, 'desc']],
				processing: false,
				serverSide: false,
				columnDefs: [
					{ targets: [1], orderable: false }
				],
				data: aData,
				columns: [ 
					{ "data": "remision", "className": "text-center" },
					{   "data": null,
						"className": "text-center",
						"mRender": function (data, type, row) {
							return '<a class="btn btn-danger btn-xs editor_' + div_table_name + '_eliminar"><i class="fa fa-trash" aria-hidden="true"></i></a>';
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
				"bPaginate": false,
				"bLengthChange": false,
				"bFilter": false,
				"bInfo": false,				
				buttons: []
			});
			
			oRemisionCruceGrid.on('click', 'a.editor_' + div_table_name + '_eliminar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oRemisionCruceGrid);
					
					__oTrDtDelete = $(this).parents('tr');
					__nReferenciasDelete = oData.referencias;
					__nFechaCajaEntradaDelete = oData.nfechacajaentrada;

					var strTitle = 'Eliminar Remisión';
					var strQuestion = 'Desea eliminar el la remisión ' + oData.remision + "?";
					var oFunctionOk = function () { 
						oRemisionCruceGrid.DataTable().row(__oTrDtDelete).remove().draw();

						__nReferencias -= __nReferenciasDelete;
						__nFechaCajaEntrada -= __nFechaCajaEntradaDelete;
						fcn_change_tipo_transporte();
					};
					var oFunctionCancel = null;
					show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_eliminar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oRemisionCruceGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			oRemisionCruceGrid.DataTable().clear().draw();
			if (aData.length > 0) {
				oRemisionCruceGrid.dataTable().fnAddData(aData);	
			}
			setTimeout(function(){ oRemisionCruceGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_remision_cruce() :: ' + err.message;
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

/********************************************
** CRUCES                                  **
********************************************/

function fcn_show_crear_cruce() {
	try {
		__sIdEjecutivo = '';
		__sIdCliente = '';
		__nReferencias = 0;
		__nFechaCajaEntrada = 0;

		$('#idiv_honorarios').hide();

		$('#isel_nvo_cruce_clientes').prop('disabled', false);
		$('#ibtn_nvo_cruce_select_client').prop('disabled', false);
		$('#itxt_mdl_nvo_cruce_remision').prop('disabled', true);
		$('#ibtn_nvo_cruce_add_remision').prop('disabled', true);
		$('#isel_mdl_nvo_cruce_tipo_transporte').prop('disabled', true);
		$('#ickb_mdl_nvo_cruce_hazmat').prop('disabled', true);
		$('#ibtn_mdl_nvo_cruce_crear_cruce').prop('disabled', true);

		$('#isel_nvo_cruce_clientes').val('').trigger('change');
		$('#itxt_mdl_nvo_cruce_remision').val('');
		$('#isel_mdl_nvo_cruce_tipo_transporte').val('');
		$('#ickb_mdl_nvo_cruce_hazmat').prop('checked', false);

		$('#modal_nuevo_cruce').modal({ show: true, backdrop: 'static', keyboard: false });		
    } catch (err) {		
		var strMensaje = 'fcn_show_crear_cruce() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_crear_cruce_select_client() {
	try {
		if ($('#isel_nvo_cruce_clientes').val() == '') {
			show_custom_function_error('Debe seleccionar un cliente!!!', 'idiv_mdl_nvo_cruce_mensaje');
		} else {
			__sIdCliente = $('#isel_nvo_cruce_clientes').val();

			ajax_get_tarifas_honorarios();
		}
    } catch (err) {		
		var strMensaje = 'fcn_crear_cruce_select_client() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_change_tipo_transporte() {
	try {
		$('#idiv_honorarios').hide();

		if (oRemisionCruceGrid.DataTable().data().count() > 0) {
			var sSelect = $.trim($('#isel_mdl_nvo_cruce_tipo_transporte').val());
			if (sSelect != '') {
				if (sSelect == 'T' || sSelect == 'P') {
					if (__nReferencias > 1) {
						__sTipoSalida = 'CONSOLIDADA';
					} else {
						__sTipoSalida = 'DIRECTA';
					}

					//Caso de B&G FOODS
					if (__sTipoSalida == 'CONSOLIDADA') {
						if (__nFechaCajaEntrada <= 1) {
							__sTipoSalida = 'DIRECTA';
						}
					}
				} else if (sSelect == '3' || sSelect == 'C') {
					__sTipoSalida = 'CONSOLIDADA';
				}

				if (__sTipoSalida == 'DIRECTA' && sSelect != 'H') {
					$('#idiv_honorarios').show();
				}
			}
		}
	} catch (err) {
		var strMensaje = 'fcn_change_tipo_transporte() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

function fcn_generar_honorarios(aHonorarios) {
	try {	
		var sHonorarios = '';
		$.each(aHonorarios, function (key, val) {
			sHonorarios += '<div class="col-xs-12 col-sm-6">';
			sHonorarios += '    <label class="radio-inline">';
			sHonorarios += '		<input type="radio" data-options=\'' + JSON.stringify(val) + '\' name="ophonorarios" ' + val.checked + '> ' + val.titulo + ' <small>' + val.descripcion + '</small>';
			sHonorarios += '    </label>';
			sHonorarios += '</div>';
		});

		if (aHonorarios.length > 0) {
			var sHTML = '';
			sHTML += '<div class="row">';
			sHTML += sHonorarios;
			sHTML += '</div>';

			$('#idiv_honorarios_radios').html(sHTML);

			var nRadioCheked = $('input[name=ophonorarios]:checked', '#idiv_honorarios_radios').val();
			if (typeof nRadioCheked === "undefined") {
				$("input:radio[name=ophonorarios]:first").attr('checked', true);
			}
		}		
	} catch (err) {
		var strMensaje = 'fcn_generar_honorarios() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

/********************************************
** DETALLE INCREMENTABLE                   **
********************************************/

function fcn_show_detalles() {
	try {
		$('#idiv_mensaje').empty().hide();

		$('#idiv_panel_principal').hide();
		$('#idiv_panel_secundario').show();

		$('#itxt_cruce_cliente').val('');
		$('#itxt_cruce_trans_tipo').val('');

		$('#idiv_cruce_remisiones').empty();
		$('#idiv_cruce_ltl').empty();

		$('#ibtn_recalcular_incrementables').hide();
				
		$('#idiv_mensaje_inc_conceptos').hide();
		$('#idiv_inc_conceptos').removeClass('in');
		
		ajax_get_incrementable();
    } catch (err) {		
		var strMensaje = 'fcn_show_detalles() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_close_detalles() {
	try {
		__sRecalcularIncMsg = '';

		$('#idiv_mensaje').empty().hide();

		$('#idiv_panel_principal').show();
		$('#idiv_panel_secundario').hide();

		fcn_cargar_grid_incrementables();
    } catch (err) {		
		var strMensaje = 'fcn_close_detalles() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_pinta_detalles(aPedimentos) {
	try {
		var dTotal = 0.00;
		var sHtmlTabla = '';
		$.each(aPedimentos, function (index, value) {
			sHtmlTabla += '' +
			'<tr>' +
			'    <td>' + value.pedimento + '</td>' +
			'    <td align="right">$ ' + value.total + '</td>' +
			'</tr>';

			dTotal += parseFloat(value.total);
		});

		if (sHtmlTabla != '') {
			sHtmlTabla += '' +
			'<tr>' +
			'    <td align="right" style="color:red;"><strong>Total:</strong></td>' +
			'    <td align="right" style="color:red;"><strong>$ ' + dTotal.toFixed(2) + '</strong></td>' +
			'</tr>';

			sHtmlTabla = '' +
			'<div class="col-lg-12">' +
			'	<table class="table table-bordered">' +
			'		<thead>' +
			'			<tr>' +
			'				<th>Pedimento</th>' +
			'				<th class="text-right" style="width:100px;">Total</th>' +
			'			</tr>' +
			'		</thead>' +
			'		<tbody>' + sHtmlTabla + '</tbody>' +
			'	</table>' +
			'</div>';

			$('#idiv_cruce_remisiones').append(sHtmlTabla);
		}
    } catch (err) {		
		var strMensaje = 'fcn_pinta_detalles() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_pinta_detalles_depresiado(aRemisionesData, aLTL) {
	try {
		var dTotal = 0.00;
		$.each(aRemisionesData, function (index, value) {
			var sRemision = value.remision;
			var sHtmlTabla = '';
			
			$.each(value.detalles, function (index, value) {
				sHtmlTabla += '' +
				'<tr>' +
				'    <td>' + value.titulo + '</td>' +
				'    <td>' + value.referencia + '</td>' +
				'    <td align="center">' + value.cantidad + '</td>' +
				'    <td align="right">$ ' + value.tarifa + '</td>' +
				'    <td align="right">$ ' + value.total + '</td>' +
				'</tr>';

				dTotal += parseFloat(value.total);
			});

			if (sHtmlTabla != '') {
				sHtmlTabla = '' +
				'<div class="col-lg-12">' +
				'	<h4>Remision ' + sRemision + '</h4>' +
				'	<hr style="margin-top: 0px; margin-bottom: 8px; border-color: #ddd;">' + 
				'	<table class="table table-bordered">' +
				'		<thead>' +
				'			<tr>' +
				'				<th>Concepto</th>' +
				'				<th>Referencia</th>' +
				'				<th class="text-center" style="width:100px;">Cantidad</th>' +
				'				<th class="text-right" style="width:100px;">Tarifa</th>' +
				'				<th class="text-right" style="width:100px;">Total</th>' +
				'			</tr>' +
				'		</thead>' +
				'		<tbody>' + sHtmlTabla + '</tbody>' +
				'	</table>' +
				'</div>';

				$('#idiv_cruce_remisiones').append(sHtmlTabla);
			}
		});

		var sHtmlTabla = '';
		$.each(aLTL, function (index, value) {
			sHtmlTabla += '' +
			'<tr>' +
			'    <td>' + value.referencia + '</td>' +
			'    <td align="center">' + value.cantidad + '</td>' +
			'    <td align="right">$ ' + value.tarifa + '</td>' +
			'    <td align="right">$ ' + value.total + '</td>' +
			'</tr>';

			dTotal += parseFloat(value.total);
		});

		if (sHtmlTabla != '') {
			sHtmlTabla = '' +
			'<div class="col-lg-12">' +
			'	<h4>Tarifa LTL</h4>' +
			'	<hr style="margin-top: 0px; margin-bottom: 8px; border-color: #ddd;">' + 
			'	<table class="table table-bordered">' +
			'		<thead>' +
			'			<tr>' +
			'				<th>Referencia</th>' +
			'				<th class="text-center" style="width:100px;">Cantidad</th>' +
			'				<th class="text-right" style="width:100px;">Tarifa</th>' +
			'				<th class="text-right" style="width:100px;">Total</th>' +
			'			</tr>' +
			'		</thead>' +
			'		<tbody>' + sHtmlTabla + '</tbody>' +
			'	</table>' +
			'</div>';

			$('#idiv_cruce_ltl').append(sHtmlTabla);
		}

		sHtmlTabla = '' +
		'<div class="col-lg-12 text-right">' +
		'	<h4 style="color:red;"><strong>Total: $ ' + dTotal.toFixed(2) + '</strong></h4>' +
		'</div>';

		$('#idiv_cruce_ltl').append(sHtmlTabla);
    } catch (err) {		
		var strMensaje = 'fcn_pinta_detalles() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*********************************************************************************************************************************
** AJAX                                                                                                                         **
*********************************************************************************************************************************/

/********************************************
** CRUCES                                  **
********************************************/

/* ..:: Consultamos honorarios del cliente ::.. */
function ajax_get_tarifas_honorarios() {
	try {	
		var oData = {	
			action: 'consultar_tarifas_honorarios',
			sIdCliente: __sIdCliente
		};

		$.ajax({
			type: "POST",
			url: 'ajax/facturacion/frwd_incrementables/incrementablesFunc.php',
			data: oData,
			timeout: 180000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						__sIdEjecutivo = respuesta.sIdEjecutivo;

						$('#isel_nvo_cruce_clientes').prop('disabled', true);
						$('#ibtn_nvo_cruce_select_client').prop('disabled', true);
						$('#itxt_mdl_nvo_cruce_remision').prop('disabled', false);
						$('#ibtn_nvo_cruce_add_remision').prop('disabled', false);
						$('#isel_mdl_nvo_cruce_tipo_transporte').prop('disabled', false);
						$('#ickb_mdl_nvo_cruce_hazmat').prop('disabled', false);
						$('#ibtn_mdl_nvo_cruce_crear_cruce').prop('disabled', false);

						fcn_generar_honorarios(respuesta.aHonorarios);
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
		
		var strMensaje = 'ajax_get_tarifas_honorarios() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Agregamos la remision ::.. */
function ajax_get_agregar_remision() {
	try {	
		var sRemision = $.trim($('#itxt_mdl_nvo_cruce_remision').val());
		if (sRemision == '') {
			show_custom_function_error('Debe ingresar una Remisión!!!', 'idiv_mdl_nvo_cruce_mensaje');
			return false;
		}

		var bRepetida = false;
		oRemisionCruceGrid.DataTable().data().each(function (value, index) {
			if (value.remision == sRemision) {
				bRepetida = true;
				return false;
			}
		});

		if (bRepetida) {
			show_custom_function_error('La remision ' + sRemision + ' ya existe en la lista!!!', 'idiv_mdl_nvo_cruce_mensaje');
			return false;
		}

		var oData = {	
			action: 'consultar_remision',
			sRemision: sRemision,
			sIdCliente: __sIdCliente,
			nReferencias: __nReferencias,
			nFechaCajaEntrada: __nFechaCajaEntrada			
		};

		$.ajax({
			type: "POST",
			url: 'ajax/facturacion/frwd_incrementables/incrementablesFunc.php',
			data: oData,
			timeout: 180000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						__nReferencias = respuesta.nReferencias;
						__nFechaCajaEntrada = respuesta.nFechaCajaEntrada;

						var oRow = { 
							remision: sRemision,
							bCobrar: respuesta.bCobrar,
							referencias: respuesta.aReferenciasRemision,							
							nfechacajaentrada: respuesta.nFechaCajaEntrada
						}
						oRemisionCruceGrid.DataTable().row.add(oRow).draw(false);

						$('#itxt_mdl_nvo_cruce_remision').val('');

						fcn_change_tipo_transporte();
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
		
		var strMensaje = 'ajax_get_agregar_remision() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Creamos el cruce ::.. */
function ajax_set_crear_cruce() {
	try {	
		fcn_change_tipo_transporte();
		
		var aRemisionesSel = new Array();
		oRemisionCruceGrid.DataTable().data().each(function (value, index) {
			aRemisionesSel.push(value.remision);
		});

		if (aRemisionesSel.length == 0) { 
			show_custom_function_error('Debe ingresar por lo menos una Remisión!!!', 'idiv_mdl_nvo_cruce_mensaje');
			return false;
		}

		var sTipoTrans = $('#isel_mdl_nvo_cruce_tipo_transporte').val();

		if (sTipoTrans == '') {
			show_custom_function_error('Debe seleccionar un tipo de transporte!!!', 'idiv_mdl_nvo_cruce_mensaje');
			return false;
		}

		var bHazmat = (($('#ickb_mdl_nvo_cruce_hazmat').is(':checked'))? 1 : 0);

		/*************************************************************/

		var aHonorarios = new Array();
		if (__sTipoSalida == 'DIRECTA') {
			$.each($('input:radio[name=ophonorarios]:checked'), function (key, val) {
				aHonorarios.push($(val).data('options'));
			});
		}

		var oData = {	
			action: 'crear_cruce',
			sId: __aAppData.sId,
			sIdCliente: __sIdCliente,
			sTipoSalida: __sTipoSalida,
			sTipoTrans: sTipoTrans,
			bHazmat: bHazmat,
			aRemisiones: JSON.stringify(aRemisionesSel),
			aHonorarios: JSON.stringify(aHonorarios)
		};

		$.ajax({
			type: "POST",
			url: 'https://www.delbravoapps.com/webtools' + ((__sDebug)? 'pruebas' : '') + '/ajax/facturacion/frwd_incrementables/incrementablesFunc.php',
			data: oData,
			timeout: 180000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						__nIdCruce = respuesta.nIdCruce;
						$('#modal_nuevo_cruce').modal('hide');

						__sRecalcularIncMsg = 'Cruce ' + __nIdCruce + 'Creado correctamente!!!';
						setTimeout(function () { fcn_show_detalles(); },700);
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
		
		var strMensaje = 'ajax_set_crear_cruce() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/********************************************
** DETALLE INCREMENTABLE                   **
********************************************/

/* ..:: Consultamos el incrementable ::.. */
function ajax_get_incrementable() {
	try {	
		var oData = {	
			action: 'consultar_incrementable',
			nIdCruce: __nIdCruce
		};

		$.ajax({
			type: "POST",
			url: 'ajax/facturacion/frwd_incrementables/incrementablesFunc.php',
			data: oData,
			timeout: 180000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$('#itxt_cruce_cliente').val(respuesta.aCruceData.sClienteNombre);
						$('#itxt_cruce_trans_tipo').val(respuesta.aCruceData.sTransTipo);

						$('#ibtn_recalcular_incrementables').show();

						if (__sRecalcularIncMsg != '') {
							show_modal_ok(__sRecalcularIncMsg);
							__sRecalcularIncMsg = '';
						}
						fcn_pinta_detalles(respuesta.aCruceData.aPedimentos);
						
						if (respuesta.aCruceData.sTarifasIncSinAsignar != '') {
							$('#idiv_mensaje_inc_conceptos').show();
							$('#idiv_inc_conceptos').html(respuesta.aCruceData.sTarifasIncSinAsignar);
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
		
		var strMensaje = 'ajax_get_incrementable() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Consultamos el incrementable ::.. */
function ajax_get_recalcular_incrementable() {
	try {	
		var oData = {	
			action: 'recalcular_incrementable',
			nIdCruce: __nIdCruce
		};

		$.ajax({
			type: "POST",
			url: 'https://www.delbravoapps.com/webtools' + ((__sDebug)? 'pruebas' : '') + '/ajax/facturacion/frwd_incrementables/incrementablesFunc.php',
			data: oData,
			timeout: 180000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informaci&oacute;n, espere un momento por favor... ');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						__sRecalcularIncMsg = respuesta.Mensaje;
						setTimeout(function () {fcn_show_detalles();}, 750);
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
		
		var strMensaje = 'ajax_get_recalcular_incrementable() :: ' + err.message;
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