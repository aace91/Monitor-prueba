/* 
* Application: Monitor Del Bravo
* 
* 
* 
* Copyright (c) 2017 DEL BRAVO. - all right reserved
*/

/*********************************************************************************************************************************
** GLOBALS DEFINITION SECTION                                                                                                   **
*********************************************************************************************************************************/

/* ..:: App Vars ::.. */
var appName = 'Monitor';
var strSessionMessage = 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
var sGifLoader = '<img src="../images/cargando.gif" height="16" width="16"/>';

var oPermisosImpoGrid = null;
var oPermisosImpoFraccionesGrid = null;
var __oTrDtDelete = null;

var __sIdPermiso = '';
var __sAction = '';
var __sPermisoNombre = '';

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
	$(window).resize(function() { onWinResize(); }); onWinResize();
}

/*********************************************************************************************************************************
** APPLICATION RUN                                                                                                              **
*********************************************************************************************************************************/

function application_run() {
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
	
		$('#isel_modal_cliente').select2({
			theme: "bootstrap",
			width: "off",
			placeholder: "Seleccione un Cliente"
		});
		
		$('#isel_modal_fraccion_unidad').select2({
			theme: "bootstrap",
			width: "off",
			placeholder: "Seleccione una unidad"
		});
			
		$('#itxt_modal_vig_fechaini, #itxt_modal_vig_fechafin').datepicker({
			format: 'dd/mm/yyyy',
			language: "es",
			autoclose: true
		});
		
		$("#ifile_permiso").fileinput({
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
		
		$('#modal_nvo_permiso').on('shown.bs.modal', function (e) {
			$('#isel_modal_cliente').focus(); 
		});
		
		$("#itxt_modal_fraccion").numeric();
		$("#itxt_modal_fraccion_cantidad").numeric({ decimalPlaces: 2 });
		$("#itxt_modal_fraccion_cantidad_db").numeric({ decimalPlaces: 2 });
		$('#itxt_modal_fraccion_cantidad').focusout(function() {
			var sCantidad = $('#itxt_modal_fraccion_cantidad').val().trim().toUpperCase();
			if (sCantidad != '') {
				$('#itxt_modal_fraccion_cantidad_db').val(sCantidad);
			}
		
			
		});
	
		fcn_cargar_grid_permisos_impo();
	} catch (err) {		
		var strMensaje = 'application_run() :: ' + err.message;
		show_modal_error(strMensaje);
	}

}

/*********************************************************************************************************************************
** FILL AND CREATE GRIDS FUNCTIONS                                                                                              **
*********************************************************************************************************************************/

function fcn_cargar_grid_permisos_impo(bReloadPaging) {
	try {
		if (oPermisosImpoGrid == null) {
			var oDivDisplayErrors = 'idiv_message';
			var div_table_name = 'dtpermisos_impo';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oPermisosImpoGrid = $('#' + div_table_name);
			
			oPermisosImpoGrid.DataTable({
				//order: [[1, 'desc']],
				processing: true,
				serverSide: true,
				columnDefs: [
					{ targets: 3, orderable: false }
				],
				ajax: {
					"url": "ajax/permisos_pedimentos_impo/postPermisos.php",
					"type": "POST",
					"timeout": 20000,
					"error": handleAjaxError
				},
				columns: [ 
					{ "data": "numero_permiso" },
					{ "data": "vigencia" },
					{ "data": "cliente" },
					{   "data": null,
						"className": "text-center",
						"mRender": function (data, type, row) {
								var sHtml = '';
								sHtml += '<a class="btn btn-primary btn-xs editor_' + div_table_name + '_editar"><i class="fa fa-pencil-square" aria-hidden="true"></i></a>';
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
				buttons: []
			});
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:fcn_cargar_grid_permisos_impo(true);"');
			$("div." + div_refresh_name).html(sButton);
			
			oPermisosImpoGrid.on('click', 'a.editor_' + div_table_name + '_editar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oPermisosImpoGrid);
					__sIdPermiso = oData.id_permiso;
					ajax_consulta_aviso_automatico();
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oPermisosImpoGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			bReloadPaging = ((bReloadPaging == null || bReloadPaging == undefined)? false: true);

			var table = oPermisosImpoGrid.DataTable();
			table.search('').ajax.reload(null, bReloadPaging);
			setTimeout(function(){ oPermisosImpoGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_permisos_impo() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

function fcn_cargar_grid_permisos_impo_fracciones(aData) {
	try {
		if (oPermisosImpoFraccionesGrid == null) {
			var oDivDisplayErrors = 'idiv_message';
			var div_table_name = 'dtfracciones';
			var div_refresh_name = div_table_name + '_refresh';		
			
			oPermisosImpoFraccionesGrid = $('#' + div_table_name);
			
			oPermisosImpoFraccionesGrid.DataTable({
				processing: false,
				serverSide: false,
				bServerSide: false,
				columnDefs: [
					{ targets: 4, orderable: false }
				],
				data: aData,
				columns: [ 
					{ "data": "fraccion" },
					{ "data": "nombre" },
					{ "data": "cantidad" },
					{ "data": "cantidad_delbravo" },
					{ "data": "unidad" },
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
				dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
					 "<'row'<'col-xs-8'B><'col-xs-4'<'" + div_refresh_name + "'>>>" +
					 "<'row'<'col-sm-12'tr>>" +
					 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
				buttons: []
			});
			
			oPermisosImpoFraccionesGrid.on('click', 'a.editor_' + div_table_name + '_eliminar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oPermisosImpoFraccionesGrid);
					
					__oTrDtDelete = $(this).parents('tr');
					if (__oTrDtDelete.hasClass('child')) {//Check if the current row is a child row
						__oTrDtDelete = __oTrDtDelete.prev();//If it is, then point to the row before it (its 'parent')
					}
					
					var strTitle = 'Eliminar Fracci&oacute;n';
					var strQuestion = 'Desea eliminar la fracci&oacute;n [' + oData.fraccion + ']!!!';
					var oFunctionOk = function () { 
						oPermisosImpoFraccionesGrid.DataTable().row(__oTrDtDelete).remove().draw();
					};
					var oFunctionCancel = null;
					show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_eliminar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oPermisosImpoFraccionesGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			oPermisosImpoFraccionesGrid.DataTable().clear().draw();
			if (aData.length > 0) {
				oPermisosImpoFraccionesGrid.dataTable().fnAddData(aData);	
			}
			setTimeout(function(){ oPermisosImpoFraccionesGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_permisos_impo_fracciones() :: ' + err.message;
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
	var bExist = message.includes("Code [500]");
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

/* ..:: Agregar permiso ::.. */
function fcn_agregar_permiso_pedimento() {
	try {
		__sAction = 'Nuevo';
		__bSubirDoc = true;
		
		$('#itxt_modal_numero_permiso').val('');
		$('#isel_modal_cliente').val('').trigger('change');
		$('#isel_modal_fraccion_unidad').val('').trigger('change');
		
		$('#itxt_modal_vig_fechaini').val('');
		$('#itxt_modal_vig_fechafin').val('');
		
		$("#idiv_ver_archivo_pdf_aviso").hide();
		$("#idiv_subir_archivo_aviso").show();
		$("#ifile_permiso").fileinput('clear');
		
		$('#idiv_modal_permiso_msj').html('');
		$('#igpo_permiso_docs').show();
		
		var aData = new Array();
		fcn_cargar_grid_permisos_impo_fracciones(aData);
		
		$('#modal_nvo_permiso').modal({show: true,backdrop: 'static',keyboard: false});
	} catch (err) {
		var strMensaje = 'fcn_agregar_permiso_pedimento() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

function fcn_limpiar_ctrl_fracciones() {
	try {
		$('#itxt_modal_fraccion_nombre').val('');
		$('#itxt_modal_fraccion').val('');
		$('#itxt_modal_fraccion_cantidad').val('');
		$('#isel_modal_fraccion_unidad').val('').trigger('change');
	} catch (err) {
		var strMensaje = 'fcn_limpiar_ctrl_fracciones() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

/* ..:: Para mostrar o no el div de subir documento ::.. */
function fcn_permiso_docs_opciones(pOpt) {
	try {
		switch(pOpt) {
			case 'ver':
				window.open(__sPermisoNombre,'_blank');
				break;
			case 'nuevo':
				$("#ifile_permiso").fileinput('clear');
				$('#igpo_permiso_docs').show();
				$('#igpo_permiso_docs_btn').hide();
				break;
		}
	} catch (err) {
		var strMensaje = 'fcn_permiso_docs_opciones() :: ' + err.message;
		show_modal_error(strMensaje);
	}
}

function fcn_agregar_fraccion() {
	try {		
		show_custom_function_error('', 'idiv_modal_permiso_msj');
		__oDataNewRow = null;
		
		var sNombre = $('#itxt_modal_fraccion_nombre').val().trim().toUpperCase();
		if (!sNombre) { 
			show_custom_function_error('Debe ingresar un nombre!', 'idiv_modal_permiso_msj');
			return;
		}
		
		var sFraccion = $('#itxt_modal_fraccion').val().trim().toUpperCase();
		if (!sFraccion) { 
			show_custom_function_error('Debe ingresar una fracci&oacute;n!', 'idiv_modal_permiso_msj');
			return;
		} else {
			if (sFraccion.length < 8) {
				show_custom_function_error('La longitud de la fraccion debe ser de 8 o mas caracteres!', 'idiv_modal_permiso_msj');
				return;
			}
		}
		
		var sCantidad = $('#itxt_modal_fraccion_cantidad').val().trim().toUpperCase();
		if (!sCantidad) { 
			show_custom_function_error('Debe ingresar una cantidad!', 'idiv_modal_permiso_msj');
			return;
		}
		
		var sCantidadDelBravo = $('#itxt_modal_fraccion_cantidad_db').val().trim().toUpperCase();
		if (!sCantidadDelBravo) { 
			show_custom_function_error('Debe ingresar una cantidad!', 'idiv_modal_permiso_msj');
			return;
		}
		
		var sUnidad = (($('#isel_modal_fraccion_unidad').val() == null)? '' : $('#isel_modal_fraccion_unidad').val());
		if (!sUnidad.trim()) { 
			show_custom_function_error('Debe seleccionar un unidad!', 'idiv_modal_permiso_msj', 'margin: 0px;');
			return;
		}
		
		var bExisteFraccion = false;
		var table = oPermisosImpoFraccionesGrid.DataTable();
		$.each(table.rows().nodes(), function(index, item) {
			if (table.row(index).data().fraccion == sFraccion) {
				bExisteFraccion = true;
				return;
			}
		});
		if (bExisteFraccion) { 
			show_custom_function_error('Ya existe la fraccion [ ' + sFraccion + ' ]!', 'idiv_modal_permiso_msj');
			return;
		}
		
		oRow = {
			id_permiso: '',
			nombre: sNombre,
			cantidad: sCantidad,
			cantidad_delbravo: sCantidadDelBravo,
			unidad: sUnidad,
			fraccion: sFraccion
		};
		
		oPermisosImpoFraccionesGrid.DataTable().row.add(oRow).draw(false);
		setTimeout(function(){ oPermisosImpoFraccionesGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		fcn_limpiar_ctrl_fracciones();
	} catch (err) {		
		var strMensaje = 'fcn_agregar_fraccion() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*********************************************************************************************************************************
** AJAX                                                                                                                         **
*********************************************************************************************************************************/

function ajax_guardar_permiso_pedimentos(){
	try{
		show_custom_function_error('', 'idiv_modal_permiso_msj');
		
		var oData = new FormData();
		
		var sNumeroPermiso = $('#itxt_modal_numero_permiso').val().toUpperCase().trim();
		var nCliente = (($('#isel_modal_cliente').val() == null)? '' : $('#isel_modal_cliente').val());
		var sVigenciaIni = $('#itxt_modal_vig_fechaini').val().trim();
		var sVigenciaFin = $('#itxt_modal_vig_fechafin').val().trim();
		
		if (!sNumeroPermiso) { 
			show_custom_function_error('Debe ingresar un numero de permiso!', 'idiv_modal_permiso_msj', 'margin: 0px;');
			return;
		}
		
		if (!nCliente.trim()) { 
			show_custom_function_error('Debe seleccionar un cliente!', 'idiv_modal_permiso_msj', 'margin: 0px;');
			return;
		}
		
		if(sVigenciaIni == ''){
			show_custom_function_error('En necesario seleccionar la fecha inicial de vigencia.', 'idiv_modal_permiso_msj', 'margin: 0px;');
			return;
		}
		
		if(sVigenciaFin == ''){
			show_custom_function_error('En necesario seleccionar la fecha final de vigencia.', 'idiv_modal_permiso_msj', 'margin: 0px;');
			return;
		}
		
		if ($('#igpo_permiso_docs').is(':visible')) { 
			var oPdf = document.getElementById('ifile_permiso');
			if (oPdf.files[0]){
				oData.append('oPdf', oPdf.files[0]);
				oData.append('bBorrarPermiso', 'NO');
			} else {
				oData.append('bBorrarPermiso', 'SI');
			}
		} else {
			oData.append('bBorrarPermiso', 'NO');
		}
		
		var aFracciones = oPermisosImpoFraccionesGrid.dataTable().fnGetData();
		
		aVigenciaIni = sVigenciaIni.split('/');
		aVigenciaFin = sVigenciaFin.split('/');
		
		sVigenciaIni = aVigenciaIni[2] + '-' + aVigenciaIni[1] + '-' + aVigenciaIni[0] + ' 00:00:00';
		sVigenciaFin = aVigenciaFin[2] + '-' + aVigenciaFin[1] + '-' + aVigenciaFin[0] + ' 00:00:00';
			
		oData.append('sAction', __sAction);
		oData.append('sIdPermiso', __sIdPermiso);
		oData.append('sNumeroPermiso', sNumeroPermiso);
		oData.append('nCliente', nCliente);
		oData.append('sVigenciaIni', sVigenciaIni);
		oData.append('sVigenciaFin', sVigenciaFin);
		oData.append('aFracciones', JSON.stringify(aFracciones));
		
		$.ajax({
			type: "POST",
			url: 'ajax/permisos_pedimentos_impo/ajax_guardar_permiso_pedimentos.php',
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
					
					if (respuesta.Codigo == '1'){
						$('#modal_nvo_permiso').modal('hide');
						fcn_cargar_grid_permisos_impo();
						setTimeout(function () {
							show_modal_ok(respuesta.Mensaje);							
						},500);
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
				
				$('#modal_info').modal('hide');	
			}
		});
	} catch (err) {
		var strMensaje = 'ajax_guardar_permiso_pedimentos() :: ' + err.message;
		show_modal_error(strMensaje);
	}  
}

function ajax_consulta_aviso_automatico(){
	try{
		$.ajax({
			type: "POST",
			url: 'ajax/permisos_pedimentos_impo/ajax_consultar_aviso_automatico.php',
			data: {
				sIdPermiso: __sIdPermiso
			},
			beforeSend: function (dataMessage) {
				$("#idiv_principal_message").html('<div class="alert alert-info">'+sGifLoader + ' Cargando informaci&oacute;n, espere un momento por favor...</div>');
			},
			success:  function (response) {
				$("#idiv_principal_message").html('');
				if (response != '500'){
					var respuesta = JSON.parse(response);
					if (respuesta.Codigo == '1'){
						__sAction = 'Editar';
						
						show_custom_function_error('', 'idiv_modal_permiso_msj');
						fcn_limpiar_ctrl_fracciones();
						
						$('#itxt_modal_numero_permiso').val(respuesta.numero_permiso);
						$('#isel_modal_cliente').val(respuesta.id_cliente).trigger('change');
						$('#itxt_modal_vig_fechaini').val(respuesta.fecha_ini);
						$('#itxt_modal_vig_fechafin').val(respuesta.fecha_fin);
						
						$("#ifile_permiso").fileinput('clear');
						if (respuesta.documento != '') {
							__sPermisoNombre = respuesta.documento;
							$('#igpo_permiso_docs').hide();
							$('#igpo_permiso_docs_btn').show();
						} else {
							$('#igpo_permiso_docs').show();
							$('#igpo_permiso_docs_btn').hide();
						}
						
						fcn_cargar_grid_permisos_impo_fracciones(respuesta.aFracciones);
						
						$('#modal_nvo_permiso').modal({show: true,backdrop: 'static',keyboard: false});
					} else {
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				}else{
					alert(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				$("#idiv_principal_message").html('');
				var strMensaje = a.status+' [' + a.statusText + ']';
				alert(strMensaje);
			}
		});
	} catch (err) {
		var strMensaje = 'ajax_consulta_aviso_automatico() :: ' + err.message;
		show_modal_error(strMensaje);
	} 
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

/* ..:: Funcion muestra mensajes de error ::.. */
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