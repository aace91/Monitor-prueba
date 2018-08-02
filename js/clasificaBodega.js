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

var appName = 'Monitor';
var strSessionMessage = 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
var sGifLoader = '<img src="../images/cargando.gif" height="16" width="16"/>';

var editor, table;

var __nIdClasificacion;

var oLanguage = {
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
}

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
		//$.fn.dataTable.ext.errMode = 'none';
		
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

		$('#selcliente, #selproveedor').selectize({
			sortField: 'text'
		});

		$( "#selproveedor" ).change(function() { table.ajax.reload(null, true); });
		$( "#selcliente" ).change(function() { table.ajax.reload(null, true); });

		fcn_cargar_grid_clasificaciones();
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

function fcn_cargar_grid_clasificaciones() {
	try {
		editor = new $.fn.dataTable.Editor( {
			"ajax": {
				"url": "./postClasificaBodega.php",
				"type": "POST",
				"data": function ( d ) {
					d.id_cliente = $('#selcliente').val();
					d.id_proveedor = $('#selproveedor').val();
				}
			},
			formOptions: {
				main: {
					focus: 1,
					onReturn: null
				}
			},
			table: "#dtclasificaciones",
			display: "bootstrap",
			fields: [ 
				{
					label: "ID:",
					name: "clasificaciones.id",
					attr:  {
						'disabled': "disable"
					}
				},
				{
					label: "# Parte:",
					name: "clasificaciones.noparte",
					attr:  {
						maxlength: 25,
						placeholder: 'Numero de parte',
						'class': "text-uppercase",
						'disabled': "disable"
					}
				},
				{
					label: "Origen:",
					name: "clasificaciones.origen",
					type: "selectize"
				},
				{
					label: "Fracción:",
					name: "clasificaciones.fraccion",
					attr:  {
						maxlength: 8,
						placeholder: 'Fracción',
						'class': "text-uppercase"
					}
				},
				{
					label: "Fracción 2:",
					name: "clasificaciones.fraccion2",
					attr:  {
						maxlength: 10,
						placeholder: 'Fracción 10 digitos',
						'class': "text-uppercase"
					}
				},
				{
					label: "Descripción:",
					name: "clasificaciones.descripcion",
					attr:  {
						maxlength: 200,
						placeholder: 'Descripción',
						'class': "text-uppercase"
					}
				},
				{
					label: "Descripción Ingles:",
					name: "clasificaciones.descripcion_ing",
					attr:  {
						maxlength: 200,
						placeholder: 'Descripción Ingles',
						'class': "text-uppercase"
					}
				},
				{
					label: "Medida:",
					name: "clasificaciones.medida",
					type: "selectize"
				},
				{
					label: "Regla 8va(Fracción):",
					name: "clasificaciones.fraccionR8va",
					attr:  {
						maxlength: 8,
						placeholder: 'Fracción',
						'class': "text-uppercase"
					}
				},
				{
					label: "Material:",
					name: "clasificaciones.material",
					attr:  {
						maxlength: 200,
						placeholder: 'Material',
						'class': "text-uppercase"
					}
				},
				{
					label: "Fundamento Legal:",
					name: "clasificaciones.fundamento_legal",
					type:   "textarea",
					attr:  {
						placeholder: 'Fundamento Legal',
						'class': "text-uppercase def_app_textarea_height",
					}
				},
				{
					label: "Restricciones:",
					name: "clasificaciones_catalogo[].id",
					type: "checkbox"
				},
				{
					label: "Proveedor:",
					name: "clasificaciones.proveedor_id",
					type: "selectize",
					attr:  {
						'disabled': "disable"
					}
				},
				{
					label: "Cliente:",
					name: "clasificaciones.cliente_id",
					type: "selectize",
					attr:  {
						'disabled': "disable"
					}
				},
				{
					label: "Usuario:",
					name: "clasificaciones.usuario",
					type: "readonly"
				},{
					label: "Fecha:",
					name: "clasificaciones.fecha",
					type: "date",
					type: "readonly"
				},{
					label: "Hora:",
					name: "clasificaciones.hora",
					type: "readonly"
				},
				{
					label: "Referencias:",
					name: "refxclasifica.referencias",
					type: "readonly"
				}
			],
			i18n: {
				create: {
					button: '<i class="fa fa-plus" aria-hidden="true"></i> Nuevo',
					title:  "Nueva clasificación",
					submit: "Guardar"
				},
				edit: {
					button: '<i class="fa fa-pencil" aria-hidden="true"></i> Modificar',
					title:  "Modificar clasificación",
					submit: "Modificar"
				},
				error: {
					system: "Ha ocurrido un error contacte al administrador del sistema"
				}
			}
		} );
		
		table =$('#dtclasificaciones').DataTable({
			order: [[1, 'desc'],[2, 'desc']],
			columnDefs: [
				{ "searchable": false, "targets": [12, 13] },
				{ "orderable": false, "targets": [12, 13] }
			],
			lengthMenu: [[10, 25, 50, 100, 1000, -1], [10, 25, 50, 100, 1000, "All"]],
			processing: true,
			serverSide: true,
			ajax: {
				"url": "./postClasificaBodega.php",
				"type": "POST",
				"data": function ( d ) {
					d.id_cliente = $('#selcliente').val();
					d.id_proveedor = $('#selproveedor').val();
				}
			},
			columns: [
				{ "data": "clasificaciones.id" },
				{ "data": "clasificaciones.noparte" },
				{ "data": "clasificaciones.origen" },
				{ "data": "clasificaciones.fraccion" },
				{ "data": "clasificaciones.descripcion" },
				{ "data": "clasificaciones.descripcion_ing" },
				{ "data": "clasificaciones.medida" },
				{ "data": "procli.proNom" },
				{ "data": "clientes.Nom" },
				{ 
					"data": "clasificaciones.clasificado",
					"className": "text-center",
					"mRender": function (data, type, row) {
						if (data == 'X') {
							return '<i class="fa fa-check-circle" aria-hidden="true" style="color: #398439"></i>';
						} else {
							return data;
						}
					}
				},
				{ "data": "clasificaciones.material" },
				{ "data": "clasificaciones.fundamento_legal" },
				{ 
					"data": null,
					"className": "text-center",
					"mRender": function (data, type, row) {
						return '<a class="btn btn-primary btn-xs editor_dtclasificaciones_subir"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Documentos</a>';
					}
				},
				{ 
					"data": null,
					"className": "text-center",
					"mRender": function (data, type, row) {
						return '<a class="btn btn-primary btn-xs editor_dtclasificaciones_ficha"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Generar</a>';
					}
				},
				{ "data": "clasificaciones.usuario" },
				{ "data": "clasificaciones.fecha" },
				{ "data": "clasificaciones.hora" }								
			],
			select: {
				style:    'os',
			},
			responsive: true,
			language: oLanguage,
			dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
				 "<'row'<'col-xs-12'B>>" +
				 "<'row'<'col-sm-12'tr>>" +
				 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
			buttons: []
		});

		// Display the buttons
		new $.fn.dataTable.Buttons( table, [
			{
				extend: 'colvis',
				text: 'Visualizar columnas'
			},
			/*{
				extend: 'copyHtml5',
				exportOptions: {
					columns: ':visible'
				}
			},*/
			{
				extend: 'excelHtml5',
				exportOptions: {
					columns: ':visible'
				}
			},
			/*{
				extend: 'csvHtml5',
				exportOptions: {
					columns: ':visible'
				}
			},*/
			{
				extend: 'pdfHtml5',
				orientation: 'landscape',
				pageSize: 'LEGAL',
				exportOptions: {
					columns: ':visible'
				}
			},
			/*{
				extend: 'print',
				exportOptions: {
					columns: ':visible'
				}
			},*/
			{ extend: "create",  editor: editor, className: 'btn-success' },
			{ extend: "edit",  editor: editor, className: 'btn-primary' },
			{
				text: '<i class="fa fa-upload" aria-hidden="true"></i> Subir Layout',
				className: 'btn-primary',
				action: function ( e, dt, node, config ) {
					abrir_modal_subir_layout_cajas();
				}
			}
		] );

		table.buttons().container().appendTo( $('.dt-buttons.btn-group', table.table().container() ) );
			
		table.on('click', 'a.editor_dtclasificaciones_subir', function (e) {
			try {		
				var oData = fcn_get_row_data($(this), table);
				__nIdClasificacion = oData.clasificaciones.id;
				
				$('#modal_documentos').modal({ show: true, backdrop: 'static', keyboard: false });

				fcn_fill_file_input(new Array(), new Array(), new Array());
				ajax_consultar_documentos();
			} catch (err) {		
				var strMensaje = 'a.editor_dtclasificaciones_subir() :: ' + err.message;
				show_modal_error(strMensaje);
			}  
		} );

		table.on('click', 'a.editor_dtclasificaciones_ficha', function (e) {
			try {		
				var oData = fcn_get_row_data($(this), table);
				__nIdClasificacion = oData.clasificaciones.id;

				window.open("clasificaBodegaFunc.php?action=generar_ficha&id=" + __nIdClasificacion,'_blank');
			} catch (err) {		
				var strMensaje = 'a.editor_dtclasificaciones_ficha() :: ' + err.message;
				show_modal_error(strMensaje);
			}  
		} );

		editor.on('open', function ( e, mode, action ){
			if(action=='create'){
				cliente=$('#selcliente').val();
				proveedor=$('#selproveedor').val();
				editor.enable( ['clasificaciones.noparte', 'clasificaciones.proveedor_id', 'clasificaciones.cliente_id'] );
				if(proveedor!=0){
					editor.field('clasificaciones.proveedor_id').set(proveedor);
				}
				editor.field('clasificaciones.cliente_id').set(cliente);
			} else {
				editor.field( 'clasificaciones.origen' ).focus();
			}
		});
	
		/*editor.on('preSubmit', function ( e, json, data ) {
			$( "div.DTE_Processing_Indicator" ).html('<center><img src="./../images/cargando.gif" height="16" width="16">Guardando...</center>');
		});*/
		
		/*editor.on('postSubmit', function ( e, json, data, action) {
			$( "div.DTE_Processing_Indicator" ).html('');
		});*/
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_clasificaciones() :: ' + err.message;
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

	var oData = oGrid.row(current_row).data();	

	return oData;
}

/*********************************************************************************************************************************
** CONTROLS FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

function abrir_modal_subir_layout_cajas(){	
	if ($('#selproveedor').val() == 0){
		alert('Para utilizar esta herramienta es necesario seleccionar un proveedor.');
		return;
	}
	
	$("#archivo_xls_layout").fileinput('clear');
	$("#archivo_xls_layout").fileinput('refresh',{
				browseClass: "btn btn-primary",
				browseLabel: "Explorar...",
				browseIcon: "<i class=\"glyphicon glyphicon-open-file\"></i> ",
				showRemove: true,
				removeClass: "btn btn-danger",
				removeLabel: "Eliminar",
				removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i> ",
				showUpload: false,
				showPreview: true,
				allowedFileExtensions: ["xls","xlsx"]
			});
	$('#archivo_xls_layout').on('fileselect', function(event, numFiles, label) {
		ajax_procesar_archivo_excel_layout_clasificaciones();
	});
	$('#archivo_xls_layout').on('fileclear', function(event, numFiles, label) {
		$("#mensaje_subir_layout").html('');
		$("#mensaje_mod_subir").html('');
		$('#btn_guardar_layout').prop('disabled',true);
	});
	
	$('#btn_guardar_layout').prop('disabled',true);
	
	$('#txt_mdl_cliente').val($("#selcliente option:selected").text());
	$('#txt_mdl_proveedor').val($("#selproveedor option:selected").text());
	
	$('#modalupload').modal({show: true});
}

/* ..:: Creamos el objeto FileInput y lo llenamos de datos ::.. */
function fcn_fill_file_input(aPreview, aPreviewConfig, aPreviewThumbTags) {
	try {
		var oInputData = {
			language: "es",
			uploadUrl: "clasificaBodegaFunc.php",
			uploadAsync: false,
			uploadExtraData: function() {
				return {
					action: 'subir_documento',
					nIdClasificacion: __nIdClasificacion
				};
			},
			showRemove: false,
			minFileCount: 1,
			maxFileCount: 40,
			resizeImage: true,
			overwriteInitial: false,
			initialPreview: aPreview,
			initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup
			initialPreviewFileType: 'image', // image is the default and can be overridden in config below
			initialPreviewConfig: aPreviewConfig,
			previewSettings: {
				image: {width: "200px", height: "160px"}
			},
			layoutTemplates: {
				actions: '<div class="file-upload-indicator" title="{indicatorTitle}">{indicator}</div>\n' +
						'{drag}\n' +
						'<div class="file-actions">{TAG_VALUE}\n' +
						'    <div class="file-footer-buttons">\n' +
						'        {TAG_BTN_PRINCIPAL} {upload} {delete} {zoom} {other}' +
						'    </div>\n' +
						'    <div class="clearfix"></div>\n' +
						'</div>'
			},
			previewThumbTags: {
				'{TAG_VALUE}': '',
				'{TAG_BTN_PRINCIPAL}': ''
			},
			initialPreviewThumbTags: aPreviewThumbTags,
			fileActionSettings: {
				showUpload: false
			}
		};

		if (!$("#ifile_documentos").data('fileinput')) {
			$("#ifile_documentos").fileinput(
				oInputData
			).on('fileuploaded', function(event, data, previewId, index) {
				setTimeout(function () { fcn_fill_file_input(data.response.aPreview, data.response.aPreviewConfig, data.response.aPreviewThumbTags); }, 750);
			}).on('filebatchuploadsuccess', function(event, data) {
				setTimeout(function () { fcn_fill_file_input(data.response.aPreview, data.response.aPreviewConfig, data.response.aPreviewThumbTags); }, 750);
			}).on("filepredelete", function(jqXHR) {
				var abort = true;
				if (confirm('Desea eliminar el documento seleccionado?')) {
					abort = false;
				}
				return abort; // you can also send any data/object that you can receive on `filecustomerror` event
			});
		} else {
			$('#ifile_documentos').fileinput('destroy');
			$("#ifile_documentos").fileinput(oInputData);
		}
	} catch (err) {
		show_custom_function_error('fcn_fill_file_input(): ' + err.message, 'idiv_documentos_mensaje');
    }
}

function fcn_activar_principal(nIdRegistro) {
	try {
		var strTitle = 'Activar como principal';
		var strQuestion = 'Al activar como principal esta imagen se mostrara en el documento de ficha de clasificación, desea continuar?';
		var oFunctionOk = function () { 
			ajax_activar_principal(nIdRegistro);
		};
		var oFunctionCancel = null;
		show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
	} catch (err) {
		show_custom_function_error('fcn_activar_principal(): ' + err.message, 'idiv_documentos_mensaje');
    }
}

/* ..:: Creamos el objeto FileInput y lo llenamos de datos ::.. */
function fcn_prueba_fill_file_input(aPreview, aPreviewConfig) {
	try {
		var bShowDelete = ($('#ibtn_factura_guardar_factura').is(":visible")? true : false);
		$("#ifile_documentos").fileinput('destroy');
		//$("#ifile_documentos").fileinput('clear');
		$("#ifile_documentos").fileinput({
			language: ((__aAppData.Idioma == 0)? '': 'es'),
			uploadUrl: "ajax/pendientes/ajax_upload_files.php", // server upload action
			uploadAsync: false,
			uploadExtraData: function() {
				return {
					sTarea: __sTarea,
					nUniqId: __nUniqId,
					nIdFactura: __nIdFactura
				};
			},
			showRemove: false,
			minFileCount: 1,
			maxFileCount: 5,
			resizeImage: true,
			overwriteInitial: false,
			initialPreview: aPreview,
			initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup
			initialPreviewFileType: 'image', // image is the default and can be overridden in config below
			initialPreviewConfig: aPreviewConfig,
			previewSettings: {
				image: {width: "200px", height: "160px"}
			},
			fileActionSettings: {
				showUpload: false,
				showDelete: bShowDelete
			}
		});
		
		/*if (aPreviewConfig.length > 0) {
			$.each(aPreviewConfig, function (index, value) {
				var oElemento = $('.file-footer-caption[title="' + value.nombre_archivo + '"]').parent().find('.file-actions')[0];
				var oActions = $(oElemento).find('.file-footer-buttons');

				var oButton = $('<button/>', {
					html: '<i class="fa fa-download" aria-hidden="true"></i>',
			        title: 'Descargar',
			        class: 'btn btn-xs btn-default',
			        type: 'button',
			        click: function (event) { 
			        	event.preventDefault();
			        	window.open(value.url_download);
			        }
			    });

			    oActions.prepend(oButton);
			});
		}*/

		$('div.close.fileinput-remove').hide();
		
		/*if ($('#ibtn_factura_guardar_factura').is(":visible") == false) {
			$('div.input-group.file-caption-main').hide();		
		}*/
								
	} catch (err) {
		var strMensaje = 'fcn_fill_file_input() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/*********************************************************************************************************************************
** AJAX                                                                                                                         **
*********************************************************************************************************************************/

function ajax_procesar_archivo_excel_layout_clasificaciones(){
	var xlsLayout = document.getElementById('archivo_xls_layout');
		
	if (!xlsLayout.files[0]){
		$("#mensaje_mod_subir_cajas").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Es necesario seleccionar un archivo para procesar la informacion.</div>');
		$('html, body').animate({scrollTop: $("#mensaje_mod_subir_cajas").offset().top }, 1000);
		$("#archivo_xls_layout").fileinput('clear');
		return;
	}
	
	var odata = new FormData();
	odata.append('action', 'procesar_excel_layout');
	odata.append('xlsLayout', xlsLayout.files[0]);
	
	$.ajax({
		url: "clasificaBodegaFunc.php",
		type: "POST",
		data: odata,
		contentType: false,
		cache: false,
		processData:false,
		xhr: function()
		{
			var xhr = new window.XMLHttpRequest();
			xhr.upload.addEventListener("progress", function(evt){
			  if (evt.lengthComputable) {
				var percent = evt.loaded / evt.total * 100;
				percent = percent.toFixed(2);
				if(percent > 99) percent = 99;
				var sMen = '<div class="progress progress-striped active">';
				sMen += '		<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
				sMen += '			<span>'+percent+'% completado</span>';
				sMen += '		</div>';
				sMen += '	</div>';
				$('#mensaje_mod_subir').html(sMen);
			  }
			}, false);
			return xhr;
		},
		beforeSend: function(){
			$("#mensaje_mod_subir").html('<div class="alert alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> Validando informacion del archivo, espere un momento por favor...</div>');
		},
		success: function(response)
		{
			if(response.toString().trim() != '500'){
				respuesta = JSON.parse(response);				
				switch (respuesta.Codigo){
					case '1' :
						$("#mensaje_mod_subir").html('');							
						$("#mensaje_subir_layout").html(respuesta.HTML);
						$('#btn_guardar_layout').prop('disabled',false);
						break
					case '2' :
						$("#mensaje_mod_subir").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Es necesario verificar las celdas en rojo para procesar correctamente el archivo.</div>');
						$("#mensaje_subir_layout").html(respuesta.HTML);
						$('#btn_guardar_layout').prop('disabled',true);
						break;
					case '-1' :
						$("#mensaje_mod_subir").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+aEtiquetas[respuesta.Mensaje].strMensaje + respuesta.Error+'</div>');
						$('#btn_guardar_layout').prop('disabled',true);
						break;					
				}					
			}else{
				$("#mensaje_mod_subir").html('<div class="alert alert-danger alert-dismissible" role="alert">La sesión del usuario ha finalizado. Es necesario iniciar nuevamente.</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},4000);
			}
		},
		error: function(a,b){
			alert(a.status + ' [' + a.statusText + ']');
		}
	});
}

function guardar_facturas_embarques(){
	$.ajax({
		url:   'ajax/clasificaciones/ajax_guardar_layout.php',
		type:  'post',
		data: {
			action: 'guardar_layout',
			id_cliente: $("#selcliente").val(),
			id_proveedor: $("#selproveedor").val()
		},
		beforeSend: function () {
			$("#mensaje_mod_subir").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><img src="../images/cargando.gif" height="16" width="16"/> Procesando clasificaciones, espere un momento por favor...</div>');
		},
		success:  function (response) {
			if(response != '500'){
				respuesta = JSON.parse(response);
				if(respuesta.Codigo == '1'){
					$("#mensaje_mod_subir").html('');
					$("#mensaje_clasificaciones").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Las clasificaciones se procesaron correctamente!.</div>');
					table.ajax.reload();
					$('#modalupload').modal('hide');
					setTimeout(function () { $("#mensaje_clasificaciones").html(''); },3000);
				}else{
					$("#mensaje_mod_subir").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + respuesta.Mensaje + respuesta.Error+'</div>');
				}
			}else{
				$("#mensaje_mod_subir").html('<div class="alert alert-danger alert-dismissible" role="alert">La sesión del usuario ha finalizado. Es necesario iniciar nuevamente.</div>');				
				setTimeout(function () {window.parent.location.replace("logout.php");},6000);
			}
		},
		error: function(a,b){
			alert(a.status + ' [' + a.statusText + ']');
		}
	});
}

function ajax_consultar_documentos(){
	try {
		$.ajax({
			url:   'clasificaBodegaFunc.php',
			type:  'post',
			data:	{
				action: 'consultar_documentos',
				nIdClasificacion: __nIdClasificacion
			},
			beforeSend: function () {
				show_load_config(true, 'Cargando información espere, espere un momento por favor...');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						fcn_fill_file_input(respuesta.aPreview, respuesta.aPreviewConfig, respuesta.aPreviewThumbTags);
					} else {
						var strMensaje = respuesta.Mensaje;
						show_modal_error(strMensaje);
					}
				} else {
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('logout.php');},4000);
				}
			},
			error: function(){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
		});
	} catch (err) {
		var strMensaje = 'ajax_consultar_documentos() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function ajax_activar_principal(nIdRegistro){
	try {
		$.ajax({
			url:   'clasificaBodegaFunc.php',
			type:  'post',
			data:	{
				action: 'activar_principal',
				nIdClasificacion: __nIdClasificacion,
				nIdRegistro: nIdRegistro
			},
			beforeSend: function () {
				show_load_config(true, 'Cargando información espere, espere un momento por favor...');
			},
			success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						fcn_fill_file_input(respuesta.aPreview, respuesta.aPreviewConfig, respuesta.aPreviewThumbTags);
					} else {
						var strMensaje = respuesta.Mensaje;
						show_modal_error(strMensaje);
					}
				} else {
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('logout.php');},4000);
				}
			},
			error: function(){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
		});
	} catch (err) {
		var strMensaje = 'ajax_consultar_documentos() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

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