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

var oTableFacturasGrid;

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
		fcn_cargar_grid_facturas();
		
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

function fcn_cargar_grid_facturas(){
	oTableFacturasGrid = $('#dtfacturas');
		
    oTableFacturasGrid.DataTable({
		order: [[0, 'asc']],
        processing: true,
        serverSide: true,
        ajax: {
            "url": "ajax/facame/postfacame.php",
            "type": "POST",
			"data": function ( d ) {
				d.buscar_por = $('#isel_buscar').val();
				d.texto_buscar = $('#itxt_numero_pedimento').val();
			}
        },
        columns: [ 
            { "data": "cuenta_americana", "className": "def_app_left"},				
            { "data": "Subtotal", "className": "def_app_left"},
            { "data": "refnumber",
				"className": "def_app_center",
				"mRender": function (data, type, row) {
					if(data!=null && data!=''){
						return '<center><a href="'+ row.idinvoice +'" target="_blank">'+data+' <i class="fa fa-download fa-3"></i></a></center>';
					} else {
						return '<center>No Disponible</center>';
					}
				}
			}
        ],
        responsive: true,
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
		},
		dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
			 "<'row'<'col-sm-12'B>>" +
			 "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
		buttons: [
			{
				extend: 'copy',
				text: 'Copiar'
			},
			{
				extend: 'excel',
				text: 'Exportar Excel'
			},
			{
				extend: 'pdf',
				text: 'Exportar pdf'
			},
			{
				extend: 'print',
				text: 'Imprimir'
			}
		]
    });//.columns.adjust().draw();	
}
	
/*********************************************************************************************************************************
** CONTROLS FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

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
		var table = oTableFacturasGrid.DataTable();
		table.ajax.reload();
	} catch (err) {		
		var strMensaje = 'ajax_get_grid_data() :: ' + err.message;
		show_label_error(strMensaje, false);
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















