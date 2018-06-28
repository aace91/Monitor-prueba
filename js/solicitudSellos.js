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

var oTableReferenciasGrid;

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
		fcn_cargar_grid_referencias();
		
		// $('#modal_add_referencia').modal({
			// show: true
		// });
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

function fcn_cargar_grid_referencias(){
	oTableReferenciasGrid = $('#dtreferencias');
		
    oTableReferenciasGrid.DataTable({
		"order": [[2, 'desc']],
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/solicitudSellos/postSolicitudSellos.php",
            "type": "POST"
        },
        "columns": [ 
            { "data": "referencia", "className": "def_app_left"},				
            { "data": "caja", "className": "def_app_left"},
            { "data": "fecha_solicitud", "className": "def_app_center"},
            { "data": "fecha_atendido", "className": "def_app_center"},
			{ "data": "picture",
				"className": "def_app_center",
				"mRender": function (data, type, row) {
					link='';
					if (!data){
						link='';
					}else{
						link='<a href="' + data + '" target="_blank">Ver Foto</a>';
					}
					return link;
				}
			}
        ],
        responsive: {
			details: {
				renderer: function ( api, rowIdx ) {
					var data = api.cells( rowIdx, ':hidden' ).eq(0).map( function ( cell ) {
                        var header = $( api.column( cell.column ).header() );
						var strClassName = header[0].className;
						
						if (strClassName.toLowerCase().indexOf("def_app_hide_column") >= 0) {
							return '';
						} else {	
							if (header.text() == 'FOTO') {
								return 	'<li>' +
											'<span class="dtr-title">' + header.text() + ':</span>' +
											'<span class="dtr-data"><a href="' + api.cell( cell ).data() + '" target="_blank"> Ver Foto</a></span>' + 
										'</li>';
							} else {
								if (api.cell( cell ).data() != null) {
									return 	'<li>' +
												'<span class="dtr-title">' + header.text() + ':</span>' +
												'<span class="dtr-data"> ' + api.cell( cell ).data() + '</span>' + 
											'</li>';							
								} else {
									return '';
								}
								
							}							
						}
                    } ).toArray().join('');
 
                    return data ?
                        $('<ul/>').append( data ) :
                        false;
				}
			}
		},
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
			}
		}
    }).columns.adjust().draw();
	
	setInterval( function () {
		ajax_get_grid_data();
	}, 60000);
}
	
/*********************************************************************************************************************************
** CONTROLS FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

/* ..:: Funcion para solicitar el sello ::.. */
function fcn_solicitar_sello() {
	$("#ibtn_modal_solicitar_sello").prop('disabled', false);
	$("#ibtn_modal_cancel").html('<i class="fa fa-ban"></i> Cancelar');
	
	$('#idiv_modal_mensaje').hide();
	fcn_clean_ctrls();
	$('#modal_add_referencia').modal({
		show: true
	});
}

/* ..:: Funcion para limpiar los controles ::.. */
function fcn_clean_ctrls() {
	$('#itxt_modal_referencia').val('');
	$('#itxt_modal_caja').val('');
}

function toUpper(control) {
	if (/[a-z]/.test(control.value)) {
		control.value = control.value.toUpperCase();
	}
}	

/* *********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/* ..:: Consulta de manera interna el objeto del grid ::.. */
function ajax_get_grid_data() {
	try {	
		var table = oTableReferenciasGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_data() :: ' + err.message;
		show_label_error(strMensaje, false);
    }  
}

/* ..:: Consultamos el catalogo de proveedores ::.. */
function ajax_set_solicitar_sello() {
	try {	
		var sReferencia = $('#itxt_modal_referencia').val();
		var sCaja = $('#itxt_modal_caja').val();
		
		if (!sReferencia.trim()) {
			show_label_error('Favor de capturar el campo Numero de Referencia.');
			return;	
		}
		
		if (!sCaja.trim()) {
			show_label_error('Favor de capturar el campo Numero de Caja.');
			return;	
		}
	
		var oData = {			
			sReferencia: sReferencia,
			sCaja: sCaja
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/solicitudSellos/ajax_set_solicitar_sello.php',
            data: oData,

            beforeSend: function (dataMessage) {
				var sMensaje = '<i class="fa fa-refresh fa-spin" aria-hidden="true"></i> enviando solicitud, espere un momento por favor...'
				show_label_info(sMensaje, true);
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_label_info('', false);
					
					if (respuesta.Codigo == '1'){
						ajax_get_grid_data();
						$("#ibtn_modal_solicitar_sello").prop('disabled', true);
						$("#ibtn_modal_cancel").html('<i class="fa fa-ban"></i> Salir');
						show_label_ok(respuesta.Mensaje, false);						
					}else{
						var strMensaje = respuesta.Mensaje;
						show_label_error(strMensaje, false);
					}
				}else{
					show_label_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				show_label_info('', false);
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_label_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_solicitar_sello() :: ' + err.message;
		show_label_error(strMensaje);
    }    
}

/*********************************************************************************************************************************
** DOWNLOAD FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

/*********************************************************************************************************************************
** MESSAJE FUNCTIONS                                                                                                            **
*********************************************************************************************************************************/

/* ..:: Funcion que muestra el mensaje de ok ::.. */
function show_label_ok(sMensaje, bHide = true) {
    var sHtml = '';
	sHtml += '<div class="alert alert-success">';
	sHtml += '    <strong>Correcto!</strong> ' + sMensaje;
	sHtml += '</div>';		
	
	$('#idiv_modal_mensaje').html(sHtml);
	$('#idiv_modal_mensaje').fadeIn();
	
	if (bHide) {
		setTimeout(function () {
			//$('#idiv_modal_mensaje').empty();
			$('#idiv_modal_mensaje').fadeOut();
		},5000);
	}
}

/* ..:: Funcion que muestra el mensaje de info ::.. */
function show_label_info(sMensaje, bShow = false) {
	if (bShow) {
		var sHtml = '';
		sHtml += '<div class="alert alert-info">';
		sHtml += '     ' + sMensaje;
		sHtml += '</div>';		
		
		$('#idiv_modal_mensaje').html(sHtml);
		$('#idiv_modal_mensaje').fadeIn();
	} else {
		$('#idiv_modal_mensaje').hide();
	}
}

/* ..:: Funcion que muestra el mensaje de error ::.. */
function show_label_error(sMensaje, bHide = true) {
	var sHtml = '';
	sHtml += '<div class="alert alert-danger">';
	sHtml += '    <strong>Error!</strong> ' + sMensaje;
	sHtml += '</div>';		
	
	$('#idiv_modal_mensaje').html(sHtml);
	$('#idiv_modal_mensaje').fadeIn();
	
	if (bHide) {
		setTimeout(function () {
			//$('#idiv_modal_mensaje').empty();
			$('#idiv_modal_mensaje').fadeOut();
		},5000);
	}
}

//Ejemplo oFunctionOk: se debe pasar una funcion de la siguiente manera "function () { Aqui la funcion o codigo a ejecutar }"
//Ejemplo oFunctionCancel: se debe pasar una funcion de la siguiente manera "function () { Aqui la funcion o codigo a ejecutar }"
function showConfirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel) {
	if (strTitle == '') {
		strTitle = appName;
	}
	$('#span_confirm_titulo_messagebox').html(strTitle);
	$('#mensaje_confirm_messagebox').html(strQuestion);
	
	//Eliminamos evento click
	$('#btn_confirm_ok').off( "click");
	$('#btn_confirm_cancel').off( "click");
	
	//Reasignamos evento click
	$('#btn_confirm_ok').on( "click", function() {
		oFunctionOk();
		$('#modalconfirm').modal('hide');
	} );
	$('#btn_confirm_cancel').on( "click", oFunctionCancel);
		
	$('#modalconfirm').modal({ show: true });
}















