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

var oTableMateriasPrimasGrid = null;
var _nRegistrosGrabados = 0;

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
		
		fcn_cargar_grid_materias_primas();
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

function fcn_cargar_grid_materias_primas() {
	try {
		if (oTableMateriasPrimasGrid == null) {	
			var div_refresh_name = 'div_dtmaterias_primas_refresh';
			var div_table_name = 'dtmaterias_primas';
			
			$('#dtmaterias_primas tfoot th').each( function () {
				var title = $(this).text();
				$(this).html( '<input type="text" placeholder="Buscar '+title+'" />' );
			} );
						
			oTableMateriasPrimasGrid = $('#' + div_table_name);
			
			oTableMateriasPrimasGrid.DataTable({
				order: [[0, 'asc']],
				processing: true,
				serverSide: true,
				ajax: {
					"url": "ajax/sterisCatMatPrimas/postCatMaterisPrimas.php",
					"type": "POST"
				},
				scrollX: true,
				columns: [ 
					{ data: "strMaterial", className: "def_app_center"},	
					{ data: "strTipo", className: "def_app_center"},	
					{ data: "strNombre", className: "def_app_center"},	
					{ data: "strNombreIng", className: "def_app_center"},	
					{ data: "strUnidad", className: "def_app_center"},	
					{ data: "intFraccion", className: "def_app_center"},	
					{ data: "strPaisOrigen", className: "def_app_center"}
				],		
				//responsive: true,
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
						extend: 'print',
						className: 'pull-left',
						text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
						title: '<h2>Cat&aacute;logo Materis Primas</h2>'
					}
				]
			} );	
			
			var sButton = fcn_create_button_datatable(div_table_name, '<i class="fa fa-refresh" aria-hidden="true"></i> Recargar', 'onClick="javascript:ajax_get_grid_materias_primas_data();"');
			$("div." + div_refresh_name).html(sButton);
			
			// Apply the search
			oTableMateriasPrimasGrid.DataTable().columns().every( function () {
				var that = this;
		 
				$( 'input', this.footer() ).on( 'keyup change', function () {
					if ( that.search() !== this.value ) {
						that
							.search( this.value )
							.draw();
					}
				} );
			} );
		} else {
			ajax_get_grid_materias_primas_data();
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_materias_primas() :: ' + err.message;
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

/* ..:: Mostramos modal subir excel ::.. */
function fcn_modal_subir_excel() {
	try {
		$('#ifile_modal_subir_excel_archivo').fileinput('clear');	
		$('#ifile_modal_subir_excel_archivo').fileinput('refresh');
		
		$('#idiv_modal_info_mensaje_registros').empty();
		$('#idiv_panel_principal_mensaje').empty();
		
		$('#modal_subir_excel').modal({
			show: true,
			backdrop: 'static',
			keyboard: false
		});
    } catch (err) {		
		var strMensaje = 'fcn_modal_subir_excel() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

/* ..:: Subir Excel ::.. */
function fcn_modal_subir_excel_archivo() {
	try {
		$('#idiv_modal_info_mensaje_registros').empty();
		$('#idiv_panel_principal_mensaje').empty();
		
		_nRegistrosGrabados = 0;
		ajax_set_archivo_excel(true, 2);
    } catch (err) {		
		var strMensaje = 'fcn_modal_subir_excel_archivo() :: ' + err.message;
		show_label_error(strMensaje);
    }
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/* ..:: DATATABLES ::.. */

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_materias_primas_data() {
	try {	
		var table = oTableMateriasPrimasGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_folios_data() :: ' + err.message;
		show_label_error(strMensaje);
    }  
}

/* ..:: Subimos el archivo de excel ::.. */
function ajax_set_archivo_excel(bPrimeraVez, nRegActual) {
	try {
		var oData = new FormData();	
		
		if (bPrimeraVez) {
			var oXls = document.getElementById('ifile_modal_subir_excel_archivo');
			if (oXls.files[0]){
				oData.append('oXls', oXls.files[0]);
			} else {
				show_label_error('Debes seleccionar un archivo.');
				return;
			}
		}
		
		oData.append('bPrimeraVez', bPrimeraVez);
		oData.append('nRegActual', nRegActual);
		
		$.ajax({
            type: "POST",
            url: 'ajax/sterisCatMatPrimas/ajax_set_archivo_excel.php',
            data: oData,
			contentType: false,
			cache: false,
			processData:false,
			timeout: 0,
			xhr: function()
			{
				if (bPrimeraVez) { 
					$('#modal_info').modal({
						show: true,
						backdrop: 'static',
						keyboard: false
					});
				}
				
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
				if (response != '500'){
					var respuesta = JSON.parse(response);
					
					if (respuesta.Codigo == '1'){
						if (respuesta.nRegActual < respuesta.nTotalRegistros) { //respuesta.nTotalRegistros
							_nRegistrosGrabados += respuesta.nRegFor;
							var sHtml = '';
							sHtml += '<div class="alert alert-info" style="margin-bottom:0px;">';
							sHtml += '    <strong>Info!</strong> Registros guardados [' + _nRegistrosGrabados + ' de ' + respuesta.nTotalRegistros + '], favor de esperar. <i class="fa fa-spinner fa-pulse pull-right"></i>';
							sHtml += '</div>';		
							
							$('#idiv_modal_info_mensaje_registros').html(sHtml);
							
							ajax_set_archivo_excel(false, respuesta.nRegActual);
						} else { 
							$('#modal_info').modal('hide');							
							$('#modal_subir_excel').modal('hide');
							
							setTimeout(function () {
								show_label_ok('Cat&aacute;logo de materias primas procesado correctamente: Total de registros guardados [' + respuesta.nTotalRegistros + ']');							
							},1000);
							// var sHtml = '';
							// sHtml += '<div class="alert alert-success" style="margin-bottom:5px;">';
							// sHtml += '    <strong>&Eacute;xito!</strong> Cat&aacute;logo de materias primas procesado correctamente: Total de registros guardados [' + respuesta.nTotalRegistros + ']';
							// sHtml += '</div>';		
							
							// $('#idiv_panel_principal_mensaje').html(sHtml);
						}
					}else{
						$('#modal_info').modal('hide');	
						
						var strMensaje = respuesta.Mensaje;
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
				
				$('#modal_info').modal('hide');	
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_archivo_excel() :: ' + err.message;
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















