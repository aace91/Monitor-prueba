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

var oTableMvHc = null;
var oTableDsLib = null;

var __sFiltroMvHc;
var __nIdRegistroMvHc;
var __sCuentaGastosMvHc;
var __sReferenciaMvHc;
var __sPedimentoMvHc;
var __sMvHc_MvOk;
var __sMvHc_HcOk;

var __sFiltroDsLib;
var __nIdRegistroDsLib;
var __sCuentaGastosDsLib;
var __sReferenciaDsLib;
var __sPedimentoDsLib;

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
		$('#idiv_mv_hc_imprimir_relacion').css('display', 'none');
		$('#idiv_ds_lib_imprimir_relacion').css('display', 'none');
		
		__sFiltroMvHc = $('input[name="optradio_mv_hc"]:checked').val();
		__sFiltroDsLib = $('input[name="optradio_ds_lib"]:checked').val();
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var target = $(e.target).attr("href") // activated tab
			
			switch(target) {
				case '#idiv_mv_hc':
					fcn_cargar_grid_pendientes_mv_hc();
					break;
					
				case '#idiv_desaduanamiento_libre':
					fcn_cargar_grid_pendientes_ds_lib();
					break;
					
			}
		});
		
		$('input[type=radio][name=optradio_mv_hc]').change(function() {
			//alert(this.value);
			var table = oTableMvHc.DataTable();
			
			__sFiltroMvHc = this.value;
			switch(__sFiltroMvHc) {
				case 'pendientes':
					$('#idiv_mv_hc_imprimir_relacion').css('display', 'none');
					
					var column = table.column(0);
					column.visible(false);
					column = table.column(7);
					column.visible(true);
		
					fcn_cargar_grid_pendientes_mv_hc();
					break;
					
				case 'firmados':
					$('#idiv_mv_hc_imprimir_relacion').css('display', 'block');
					
					var column = table.column(0);
					column.visible(true);
					column = table.column(7);
					column.visible(false);
					
					fcn_cargar_grid_pendientes_mv_hc();
					break;
				
			}
		});		
		
		$('input[type=radio][name=optradio_ds_lib]').change(function() {
			//alert(this.value);
			var table = oTableDsLib.DataTable();
			
			__sFiltroDsLib = this.value;
			switch(__sFiltroDsLib) {
				case 'pendientes':
					$('#idiv_ds_lib_imprimir_relacion').css('display', 'none');
					
					var column = table.column(0);
					column.visible(false);
					column = table.column(6);
					column.visible(true);
					
					fcn_cargar_grid_pendientes_ds_lib();
					break;
					
				case 'firmados':
					$('#idiv_ds_lib_imprimir_relacion').css('display', 'block');
					
					var column = table.column(0);
					column.visible(true);
					column = table.column(6);
					column.visible(false);
					
					fcn_cargar_grid_pendientes_ds_lib();
					break;
				
			}
		});
		
		fcn_cargar_grid_pendientes_mv_hc();
		fcn_cargar_grid_pendientes_ds_lib();
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

/* ..:: Grid Manifestacion Valor y Hoja de Calculo ::.. */
function fcn_cargar_grid_pendientes_mv_hc() {
	try {
		if (oTableMvHc == null) {	
			var div_refresh_name = 'div_pendientes_mv_hc_refresh';
			var div_table_name = 'dtpendientes_mv_hc';
						
			oTableMvHc = $('#' + div_table_name);
						
			oTableMvHc.DataTable({
				order: [[1, 'asc'], [5, 'asc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expPapeleriaFirmada/postExpPapeleriaFirmada_MvHc.php",
					"type": "POST",
					"data": function ( d ) {
						d.sFiltro = $('input[name="optradio_mv_hc"]:checked').val();
					}
				},				
				columnDefs: [
					{
						'targets': 0,
						'checkboxes': {
							'selectRow': true
						}
					},
					{
						"targets": [ 0 ],
						"visible": false
					}
				],
				columns: [ 
					{
						data: null, 
						className: "def_app_center"
					},
					{ data: "cuenta_gastos", className: "def_app_center"},	
					{ data: "referencia_saaio", className: "def_app_center"},
					{ data: "pedimento", className: "def_app_center"},
					{ data: "fecha_archivo_mv", className: "def_app_center"},
					{ data: "fecha_archivo_hc", className: "def_app_center"},
					{ data: "caja", className: "def_app_center"},
					{
						data: null,
						className: "def_app_center",
						render: function ( data, type, row ) {
							if(__sFiltroMvHc == 'pendientes') {
								return '<a href="#" class="editor_pendientes_mv_hc_editar"><i class="fa fa-pencil" aria-hidden="true"></i> Editar</a>';
							} else {
								return '';
							}					
						}
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
					 "<'row'<'col-xs-8'B><'col-xs-4'<'" + div_refresh_name + "'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'excelHtml5',
						className: 'pull-left',
						text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Exportar Excel',
						title: 'Manifestación Valor y Hoja Calculo',
						exportOptions: {
							columns: [ 1, 2, 3, 4, 5, 6 ],
                            stripHtml: true
						}
					}
				]
			} );	
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_pendientes_mv_hc_data();"');
			$("div." + div_refresh_name).html(sButton);
			
			oTableMvHc.on('click', 'a.editor_pendientes_mv_hc_editar', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableMvHc.DataTable();
					var oData = table.row(current_row).data();			
			
					__nIdRegistroMvHc = oData.id_registro;
					__sCuentaGastosMvHc = oData.cuenta_gastos;
					__sReferenciaMvHc = oData.referencia_saaio;
					__sPedimentoMvHc = oData.pedimento;
					
					__sMvHc_MvOk = oData.fecha_archivo_mv;					
					__sMvHc_HcOk = oData.fecha_archivo_hc;

					//alert(__nIdRegistroMvHc + ' ' + __sCuentaGastosMvHc + ' ' + __sReferenciaMvHc + ' ' + __sPedimentoMvHc);
					fcn_recibir_mv_hc();
				} catch (err) {		
					var strMensaje = 'editor_pendientes_mv_hc_editar_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			oTableMvHc.DataTable().column(0).checkboxes.deselect();
			oTableMvHc.DataTable().clear().draw();
			//ajax_get_grid_pendientes_mv_hc_data();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pendientes_mv_hc() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

/* ..:: Grid Desaduanamiento Libre ::.. */
function fcn_cargar_grid_pendientes_ds_lib() {
	try {
		if (oTableDsLib == null) {	
			var div_refresh_name = 'div_pendientes_ds_lib_refresh';
			var div_table_name = 'dtpendientes_ds_lib';
						
			oTableDsLib = $('#' + div_table_name);
						
			oTableDsLib.DataTable({
				order: [[1, 'asc'], [5, 'asc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expPapeleriaFirmada/postExpPapeleriaFirmada_DsLib.php",
					"type": "POST",
					"data": function ( d ) {
						d.sFiltro = $('input[name="optradio_ds_lib"]:checked').val();
					}
				},				
				columnDefs: [
					{
						'targets': 0,
						'checkboxes': {
							'selectRow': true
						}
					},
					{
						"targets": [ 0 ],
						"visible": false
					}
				],
				columns: [ 
					{
						data: null, 
						className: "def_app_center"
					},
					{ data: "cuenta_gastos", className: "def_app_center"},	
					{ data: "referencia_saaio", className: "def_app_center"},
					{ data: "pedimento", className: "def_app_center"},
					{ data: "fecha_archivo_desaduanamiento", className: "def_app_center"},
					{ data: "caja", className: "def_app_center"},
					{
						data: null,
						className: "def_app_center",
						render: function ( data, type, row ) {
							if(__sFiltroDsLib == 'pendientes') {
								return '<a href="#" class="editor_pendientes_ds_lib_editar"><i class="fa fa-check" aria-hidden="true"></i> Confirm</a>';
							} else {
								return '';
							}					
						}
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
					 "<'row'<'col-xs-8'B><'col-xs-4'<'" + div_refresh_name + "'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: [
					{
						extend: 'excelHtml5',
						className: 'pull-left',
						text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Exportar Excel',
						title: 'Desaduanamiento Libre',
						exportOptions: {
							columns: [ 1, 2, 3, 4, 5 ],
                            stripHtml: true
						}
					}
				]
			} );	
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_pendientes_ds_lib_data();"');
			$("div." + div_refresh_name).html(sButton);
			
			oTableDsLib.on('click', 'a.editor_pendientes_ds_lib_editar', function (e) {
				try {			
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableDsLib.DataTable();
					var oData = table.row(current_row).data();			
			
					__nIdRegistroDsLib = oData.id_registro;
					__sCuentaGastosDsLib = oData.cuenta_gastos;
					__sReferenciaDsLib = oData.referencia_saaio;
					__sPedimentoDsLib = oData.pedimento;
					
					ajax_set_guardar_ds_lib();
				} catch (err) {		
					var strMensaje = 'editor_pendientes_ds_lib_editar_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			oTableDsLib.DataTable().column(0).checkboxes.deselect();
			oTableDsLib.DataTable().clear().draw();
			//ajax_get_grid_pendientes_ds_lib_data();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_pendientes_mv_hc() :: ' + err.message;
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

function fcn_recibir_mv_hc() {
	try {
		$('#ih4_editar_mv_hc_title').html('Documentos Manifestaci&oacute;n Valor y Hoja de Calculo [ ' + __sCuentaGastosMvHc + ' ]');
		
		// $('#itxt_modal_editar_mv_hc_referencia').val(__sReferenciaMvHc);
		// $('#itxt_modal_editar_mv_hc_pedimento').val(__sPedimentoMvHc);
		
		$('#idiv_modal_editar_mv_hc_mensaje').empty();
		$('#idiv_modal_editar_mv_hc_mensaje').hide();
				
		$('#ibtn_modal_editar_mv_hc_aceptar').attr('disabled', false);
		$('#ickb_modal_editar_mv_hc_manifestacion_valor').prop( "checked", true );
		$('#ickb_modal_editar_mv_hc_hoja_calculo').prop( "checked", true );
		
		$('#ickb_modal_editar_mv_hc_manifestacion_valor').attr("disabled", false);
		$('#ickb_modal_editar_mv_hc_hoja_calculo').attr("disabled", false);
		if (__sMvHc_MvOk != '') {
			$('#ickb_modal_editar_mv_hc_manifestacion_valor').attr("disabled", true);
		}
		if (__sMvHc_HcOk != '') {
			$('#ickb_modal_editar_mv_hc_hoja_calculo').attr("disabled", true);
		}
		
		$('#modal_editar_mv_hc').modal({
			show: true,
			backdrop: 'static',
			keyboard: false
		});
	} catch (err) {		
		var strMensaje = 'fcn_recibir_mv_hc() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

/* ..:: Obtenemos las cuentas seleccionadas ::.. */
function fcn_imprimir_relacion_mv_hc() {
	var rows_selected = oTableMvHc.DataTable().column(0).checkboxes.selected();
	var aSelected = new Array();
	$.each(rows_selected, function(index, rowId){
		aSelected.push({
			cuenta_gastos: rowId.cuenta_gastos,
			pedimento: rowId.pedimento,
			caja: rowId.caja,
			mv: rowId.fecha_archivo_mv,
			hc: rowId.fecha_archivo_hc
		});
    });
	
	if (aSelected.length > 0) {
		fcn_imprimir_relacion(aSelected, 'Relación Movimiento Valor Y Hoja de Cálculo', 'MV_HC');
	} else {
		show_label_error('Debe seleccionar por lo menos un registro.');
	}
}

/* ..:: Obtenemos las cuentas seleccionadas ::.. */
function fcn_imprimir_relacion_ds_lib() {
	var rows_selected = oTableDsLib.DataTable().column(0).checkboxes.selected();
	var aSelected = new Array();
	$.each(rows_selected, function(index, rowId){
		aSelected.push({
			cuenta_gastos: rowId.cuenta_gastos,
			pedimento: rowId.pedimento,
			caja: rowId.caja,
			ds: rowId.fecha_archivo_desaduanamiento
		});
    });
	
	if (aSelected.length > 0) {
		fcn_imprimir_relacion(aSelected, 'Relación Desaduanamiento Libre', 'DS_LIB');
	} else {
		show_label_error('Debe seleccionar por lo menos un registro.');
	}
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/* ..:: DATATABLES ::.. */

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_pendientes_mv_hc_data() {
	try {	
		var table = oTableMvHc.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_pendientes_mv_hc_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_pendientes_ds_lib_data() {
	try {	
		var table = oTableDsLib.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_pendientes_ds_lib_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}


/* ..:: TAB MANIFESTACION VALOR Y HOJA DE CALCULO ::.. */
function ajax_set_guardar_hv_hc() {
	try {		
		var sHtml = '';
		var $MsjCtrl = $('#idiv_modal_editar_mv_hc_mensaje');
		
		var bCkbMv = false;
		var bCkbHc = false;
		
		if ($('#ickb_modal_editar_mv_hc_manifestacion_valor').is(':checked')) {
			bCkbMv = true;
		}
		
		if ($('#ickb_modal_editar_mv_hc_hoja_calculo').is(':checked')) {
			bCkbHc = true;
		}
	
		if (bCkbMv == false && bCkbHc == false) {
			sHtml += '<div class="alert alert-danger" style="margin-bottom:0px !important;">';
			sHtml += '    <span style="color:#FFF;">Debe seleccionar por lo menos un documento!</span>';
			sHtml += '</div>'
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
			nIdRegistro: __nIdRegistroMvHc,
			bCkbMv: bCkbMv,
			bCkbHc: bCkbHc,
			sCuentaGastosMvHc: __sCuentaGastosMvHc,
			sPedimentoMvHc: __sPedimentoMvHc
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expPapeleriaFirmada/ajax_set_guardar_hv_hc.php',
			data: oData,

            beforeSend: function (dataMessage) {
				$('#idiv_modal_editar_mv_hc_mensaje').empty();
				$('#idiv_modal_editar_mv_hc_mensaje').hide();
				show_load_config(true, 'Guardando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						var sHtml = '';
						
						sHtml += '<div class="alert alert-success" style="margin-bottom:0px !important;">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '</span>';
						sHtml += '</div>'
						
						$('#idiv_modal_editar_mv_hc_mensaje').html(sHtml);
						$('#idiv_modal_editar_mv_hc_mensaje').fadeIn();
						
						$('#ibtn_modal_editar_mv_hc_aceptar').attr('disabled', true);
						
						ajax_get_grid_pendientes_mv_hc_data();
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
		var strMensaje = 'ajax_set_guardar_hv_hc() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/* ..:: TAB DESADUANAMIENTO LIBRE ::.. */
function ajax_set_guardar_ds_lib() {
	try {		
		var oData = {			
			nIdRegistro: __nIdRegistroDsLib,
			sCuentaGastosDsLib: __sCuentaGastosDsLib,
			sPedimentoDsLib: __sPedimentoDsLib
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/expedientes/expPapeleriaFirmada/ajax_set_guardar_ds_lib.php',
			data: oData,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Guardando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						var sHtml = '';
						
						sHtml += '<div class="alert alert-success">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '</span>';
						sHtml += '</div>'
						
						$('#idiv_desaduanamiento_libre_mensaje').html(sHtml);
						$('#idiv_desaduanamiento_libre_mensaje').fadeIn();
						
						setTimeout(function () {
							$('#idiv_desaduanamiento_libre_mensaje').fadeOut();
						},5000);
						
						ajax_get_grid_pendientes_ds_lib_data();
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
		var strMensaje = 'ajax_set_guardar_hv_hc() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/*********************************************************************************************************************************
** DOWNLOAD FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

function fcn_imprimir_relacion(aSelected, sTitulo, sTipoRel) {
	//var data = { aSelected: JSON.stringify(aSelected) };
	
	var oForm = document.createElement("form");
	oForm.target = 'data';
	oForm.method = 'POST'; // or "post" if appropriate
	oForm.action = 'ajax/expedientes/expPapeleriaFirmada/exportar_relacion.php';

	var oInput = document.createElement("input");
	oInput.type = "text";
	oInput.name = "aSelected";
	oInput.value = JSON.stringify(aSelected);
	oForm.appendChild(oInput);
	
	var oInput = document.createElement("input");
	oInput.type = "text";
	oInput.name = "sTitle";
	oInput.value = sTitulo;
	oForm.appendChild(oInput);
	
	var oInput = document.createElement("input");
	oInput.type = "text";
	oInput.name = "sTipoRel";
	oInput.value = sTipoRel;
	oForm.appendChild(oInput);

	document.body.appendChild(oForm);
	oForm.submit();
	$(oForm).remove();
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















