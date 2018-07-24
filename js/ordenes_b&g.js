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

var oReferenciasGrid = null;
var __sReferencia;

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

		$("#ifile_xls").fileinput({
			language: "es",
			previewFileType: "any",
			browseClass: "btn btn-primary",
			browseLabel: " Examinar...",
			browseIcon: "<span class=\"glyphicon glyphicon-folder-open\"></span>",
			removeClass: "btn btn-danger",
			removeLabel: "Eliminar",
			removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i>",
			allowedFileExtensions: ["pdf"]
		});
		
		fcn_cargar_grid_entradas();
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

function fcn_cargar_grid_entradas(bReloadPaging) {
	try {
		show_custom_function_error('', 'idiv_bwsr_mensaje');
		if (oReferenciasGrid == null) {
			var oDivDisplayErrors = 'idiv_bwsr_mensaje';
			var div_table_name = 'dt_ordenes';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oReferenciasGrid = $('#' + div_table_name);
			
			oReferenciasGrid.DataTable({
				order: [[1, 'desc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/ordenes_b&g/ordenesB&GFunc.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
						d.action = 'table_entradas';
						d.estatus = $('#estatusref').val();
					},
					"error": handleAjaxError
				},
				columns: [
					{ "data": "po", "className": "text-center" },
					{ 
						"data": "referencia",
						"className": "text-center",
						"mRender": function (data, type, row) {
							if (row.PORLLEGAR == true || row.PORLLEGAR == 1) {
								return '<a href="#" data-toggle="tooltip" data-placement="bottom" title="Entrada virtual"><i class="fa fa-exclamation-triangle" aria-hidden="true" style="color:#f0ad4e;"></i></a><br/>' + data;
							} else {
								return data;
							}
						}
					},
					{ "data": "noparte", "className": "text-center" },
					{ "data": "descripcion" },
					{ "data": "qty" },
					{ "data": "unidad_medida" },
					{ "data": "proveedor" },
					{ "data": "fecha_envio" },
					{ "data": "fecha_entrega" },
					{ "data": "flete" },
					{ "data": "temperatura" },
					{ 
						"data": null,
						"className": "text-center",
						"mRender": function (data, type, row) {
							var sHtml = '';
							if (row.PORLLEGAR == true || row.PORLLEGAR == 1) {
								sHtml += '<a class="btn btn-danger btn-xs editor_' + div_table_name + '_eliminar"><i class="fa fa-trash" aria-hidden="true"></i></a>';
							}							
							return sHtml;
						}
					},
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
						text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Procesar Excel',
						className: 'btn-success',
		                action: function ( e, dt, node, config ) {
							fcn_show_subir_xls();
		            	}
		            }
				]
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_entradas(true);"');
			$("div." + div_refresh_name).html(sButton);
			
			oReferenciasGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );

			oReferenciasGrid.on('click', 'a.editor_' + div_table_name + '_eliminar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oReferenciasGrid);
					var sPO = oData.po;
					__sReferencia = oData.referencia;

					var strQuestion = 'Al realizar esta acción, toda la informacion de la referencia <strong>' + __sReferencia + '</strong> sera eliminada de bodega, ';
					strQuestion += 'desea eliminar la referencia <strong>' + __sReferencia + '</strong>?';
					$('#idiv_mdl_eliminar_mensaje').html(strQuestion);
					$('#itxt_mdl_eliminar_observacion').val('');

					$('#modal_eliminar').modal({ show: true });
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oReferenciasGrid.DataTable().on('draw', function () {
				$('[data-toggle="tooltip"]').tooltip(); 
			});

			/*$('#estatusref').on('change', function () {
				var table = oReferenciasGrid.DataTable();
				table.search('').ajax.reload(null, null);
			})*/
		} else {
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);

			var table = oReferenciasGrid.DataTable();
			table.search('').ajax.reload(null, bReloadPaging);
			setTimeout(function(){ oReferenciasGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_entradas() :: ' + err.message;
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

function fcn_show_subir_xls() {
	try {
		show_custom_function_error('', 'idiv_mdl_subir_xls_mensaje');
		
		$("#ifile_xls").fileinput('clear');
		$('#modal_subir_xls').modal({ show: true, backdrop: 'static', keyboard: false });		
    } catch (err) {		
		var strMensaje = 'fcn_show_subir_xls() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*********************************************************************************************************************************
** AJAX                                                                                                                         **
*********************************************************************************************************************************/

/* ..:: Subimos el archivo de excel ::.. */
function ajax_set_xls() {
	try {	
		show_custom_function_error('', 'idiv_mdl_subir_xls_mensaje');
	
		var oData = new FormData();
		oData.append('action', 'procesar_xls');
		
		var oXls = document.getElementById('ifile_xls');
		if (oXls.files[0]){
			oData.append('oXls', oXls.files[0]);
		} else {
			show_custom_function_error('Debe seleccionar un archivo de excel!!!', 'idiv_mdl_subir_xls_mensaje');
			return false;
		}

		/*************************************************************/

		$.ajax({
			type: "POST",
			url: 'ajax/ordenes_b&g/ordenesB&GFunc.php',
			data: oData,
			contentType: false,
			cache: false,
			processData:false,
			timeout: 0,
			xhr: function() {
				$('#modal_info').modal({show: true,backdrop: 'static',keyboard: false});
				
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt){
				  if (evt.lengthComputable) {
					var percent = evt.loaded / evt.total * 100;
					if(percent > 89) 
						percent = 90;
					var sMen = '<div class="progress progress-striped active">';
					sMen += '		<div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
					sMen += '			<span>'+percent+'% Completado, Procesando Archivo, espere un momento por favor...</span>';
					sMen += '		</div>';
					sMen += '	</div>';
					$('#idiv_modal_info_mensaje').html(sMen);
				  }
				}, false);
				return xhr;
			},
			beforeSend: function (dataMessage) { },
			success:  function (response) {
				$('#modal_info').modal('hide');		
				
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						show_modal_ok('Archivo procesado correctamente!!!');
						
						setTimeout(function () {
							$('#modal_subir_xls').modal('hide');
							fcn_cargar_grid_entradas();
						},1000);
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
		
		var strMensaje = 'ajax_set_xls() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Subimos el archivo de excel ::.. */
function ajax_set_eliminar_referencia() {
	try {	
		var sObservaciones = $('#itxt_mdl_eliminar_observacion').val().trim();
		if (sObservaciones == '') { show_modal_error('Debe agregar una observación!!!'); return false; }

		/*************************************************************/

		$.ajax({
			type: "POST",
			url: 'ajax/ordenes_b&g/ordenesB&GFunc.php',
			data: {
				'action': 'eliminar_referencia',
				'sReferencia': __sReferencia,
				'sObservaciones': sObservaciones
			},
			timeout: 30000,
			
			beforeSend: function (dataMessage) {
				show_load_config(true, 'Eliminando referencia, espere un momento por favor... ');
			},
			success:  function (response) {
				show_load_config(false);
				
				if (response != '500'){
					var respuesta = JSON.parse(response);
					if (respuesta.Codigo == '1'){
						$('#modal_eliminar').modal('hide');
						setTimeout(function () { show_modal_ok(respuesta.Mensaje); fcn_cargar_grid_entradas(); }, 750);
					} else {
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				} else {					
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
		
		var strMensaje = 'ajax_set_xls() :: ' + err.message;
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