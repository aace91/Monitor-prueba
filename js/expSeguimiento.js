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
var appName = 'Seguimiento';
var strSessionMessage = 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
var sGifLoader = '<img src="../images/cargando.gif" height="16" width="16"/>';

var __nIdRegistro;

var oTableBusquedaGrid = null;

var __sTipoUsuario;
var __sEmailUsuario;

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
		__sEmailUsuario = $('#itxt_email_usuario').val();
		
		fcn_cargar_grid_busqueda();
		fcn_sel_buscar_change();
		
		$('#isel_buscar').selectize({
			sortField: 'text'
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

function fcn_cargar_grid_busqueda() {
	try {
		if (oTableBusquedaGrid == null) {
			var div_refresh_name = 'div_seguimiento_refresh';
			var div_table_name = 'dtseguimiento';
			
			oTableBusquedaGrid = $('#' + div_table_name);
			
			oTableBusquedaGrid.DataTable({
				order: [[0, 'asc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/expedientes/expSeguimiento/postExpSeguimiento_Buscar.php",
					"type": "POST",
					"data": function ( d ) {
						var sBuscarPor = $('#isel_buscar').val();
						var sTexto = $('#itxt_buscar').val();
						if (sBuscarPor == '' || sTexto == '') {
							sBuscarPor = '-1';
						}
						
						d.sBuscarPor = sBuscarPor;
						d.sTexto = sTexto;
					}
				},				
				columnDefs: [
					{
						"targets": [ 4 ],
						"visible": false
					}
				],
				columns: [ 
					{ data: "referencia", className: "def_app_center"},	
					{ data: "pedimento", className: "def_app_center"},
					{ data: "cuenta_gastos", className: "def_app_center"},
					{ data: "trafico", className: "def_app_center"},
					{
						data:  "comentarios",
						render: function ( data, type, row ) {
							if (data == "") {
								return '<a href="#" class="editor_editar_comentario"><i class="fa fa-comment-o" aria-hidden="true"></i> Agregar Comentario</a>';
							} else {
								return '<a href="#" class="editor_editar_comentario"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Editar</a> ' + data;
							}
						},
						className: "def_app_left"
					},
					{ data: "status", className: "def_app_center"}
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
				dom: "<'row'<'col-xs-6'l><'col-sm-6'f>>" +
					 "<'row'<'col-xs-8'B><'col-xs-4'<'" + div_refresh_name + "'>>>" +
					 "<'row'<'col-xs-12'tr>>" +
					 "<'row'<'col-xs-5'i><'col-xs-7'p>>",
				buttons: [
					{
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Seguimiento de Pedimentos</h2>'
					}
				]
			} );	
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_busqueda_data();"');
			$("div." + div_refresh_name).html(sButton);
			
			oTableBusquedaGrid.on('click', 'a.editor_editar_comentario', function (e) {
				try {		
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = oTableBusquedaGrid.DataTable();
					var oData = table.row(current_row).data();			
					
					__nIdRegistro = oData.id_registro;
					fcn_mostrar_comentarios(oData.comentarios);
				} catch (err) {		
					var strMensaje = 'editor_recive_pedimento_click() :: ' + err.message;
					show_label_error(strMensaje);
				}  
			} );
		} else {
			ajax_get_grid_busqueda_data();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_busqueda() :: ' + err.message;
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

function fcn_sel_buscar_change() {
	try {
		var sValue = $('#isel_buscar').val();
		$('#itxt_buscar').attr('disabled', false);
		$('#ibtn_buscar').attr('disabled', false);
		$('#itxt_buscar').unmask();
		$('#itxt_buscar').val('');
		
		var table = oTableBusquedaGrid.DataTable();
		var column = table.column(4);
		if (__sEmailUsuario == 'jcdelacruz@delbravo.com' || __sEmailUsuario == 'alma.sanchez@delbravo.com' || __sEmailUsuario == 'candido@delbravo.com') {
			column.visible(true);
			fcn_cargar_grid_busqueda();
		} else {
			column.visible(false);
		}		
		
		switch(sValue) {
			case '':
				$('#itxt_buscar').attr('disabled', true);
				$('#ibtn_buscar').attr('disabled', true);
				break;
			case 'referencia':
				$('#itxt_buscar').attr('maxlength','20'); 
				break;
			case 'pedimento':
				$('#itxt_buscar').attr('maxlength','30');
				
				break;
			case 'cuenta_gastos':
				$('#itxt_buscar').attr('maxlength','30');
				$.mask.definitions['h'] = "[ireIRE]";
				$('#itxt_buscar').mask("h-9-9999?9999");
				break;
			
		}
	} catch (err) {		
		var strMensaje = 'fcn_sel_buscar_change() :: ' + err.message;
		show_label_error(strMensaje, false);
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

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/* ..:: DATATABLES ::.. */

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_busqueda_data() {
	try {	
		var table = oTableBusquedaGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_busqueda_data() :: ' + err.message;
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
						//__NumeroCaja = $('#itxt_modal_select_empresa_caja').val();
						
						// var sHtml = '';
						// sHtml += '<div class="alert alert-success" style="margin-bottom:0px;">';
						// sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '</span>';
						// sHtml += '</div>';
						
						// var $MsjCtrl = $('#idiv_modal_add_caja_mensaje');
						// $MsjCtrl.html(sHtml);
						// $MsjCtrl.fadeIn();
						
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
												
						sHtml += '<div class="alert alert-success" style="margin-bottom:0px;">';
						sHtml += '    <span style="color:#FFF;">' + respuesta.Mensaje + '!</span>';
						sHtml += '</div>';
												
						$MsjCtrl.html(sHtml);
						$MsjCtrl.fadeIn();
						
						setTimeout(function () {
							$MsjCtrl.fadeOut();
						},4000);
					}else{
						sHtml += '<div class="alert alert-error" style="margin-bottom:0px;">';
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

/* ..:: COMENTARIOS ::.. */
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
						ajax_get_grid_busqueda_data();
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















