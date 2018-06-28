var editor,table;
$(document).ready(function() {
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
        fields: [ {
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
                label: "Descripción:",
                name: "clasificaciones.descripcion",
				attr:  {
					maxlength: 200,
					placeholder: 'Descripción',
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
                button: "Nuevo",
                title:  "Nueva clasificación",
                submit: "Guardar"
            },
            edit: {
                button: "Modificar",
                title:  "Modificar clasificación",
                submit: "Modificar"
            },
            error: {
                system: "Ha ocurrido un error contacte al administrador del sistema"
            }
        }
    } );
	table =$('#dtclasificaciones').DataTable({
		"order": [[1, 'desc'],[2, 'desc']],
		"lengthMenu": [[10, 25, 50, 100, 1000, -1], [10, 25, 50, 100, 1000, "All"]],
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "./postClasificaBodega.php",
			"type": "POST",
			"data": function ( d ) {
				d.id_cliente = $('#selcliente').val();
				d.id_proveedor = $('#selproveedor').val();
			}
		},
		"columns": [
			{ "data": "clasificaciones.id" },
			{ "data": "clasificaciones.noparte" },
			{ "data": "clasificaciones.origen" },
			{ "data": "clasificaciones.fraccion" },
			{ "data": "clasificaciones.descripcion" },
			{ "data": "clasificaciones.medida" },
			{ "data": "procli.proNom" },
			{ "data": "clientes.Nom" },
			{ "data": "clasificaciones.clasificado" },
			{ "data": "clasificaciones.usuario" },
			{ "data": "clasificaciones.fecha" },
			{ "data": "clasificaciones.hora" }
		],
		select: {
            style:    'os',
        },
		responsive: true,
		"language": {
			"sProcessing":     '<img src="./../images/cargando.gif" height="36" width="36">Cargando...',
		}
	});
	// Display the buttons
    new $.fn.dataTable.Buttons( table, [
		{
			extend: 'colvis',
			text: 'Visualizar columnas'
		},
		{
			extend: 'copyHtml5',
			exportOptions: {
				columns: ':visible'
			}
		},
		{
			extend: 'excelHtml5',
			exportOptions: {
				columns: ':visible'
			}
		},
		{
			extend: 'csvHtml5',
			exportOptions: {
				columns: ':visible'
			}
		},
		{
			extend: 'pdfHtml5',
			orientation: 'landscape',
			pageSize: 'LEGAL',
			exportOptions: {
				columns: ':visible'
			}
		},
		{
			extend: 'print',
			exportOptions: {
				columns: ':visible'
			}
		},
        { extend: "create",  editor: editor },
		{ extend: "edit",  editor: editor },
		{
			text: 'Subir Layout',
			action: function ( e, dt, node, config ) {
				abrir_modal_subir_layout_cajas();
			},
			className: 'verde_btns'
		}
    ] );
    table.buttons().container()
        .appendTo( $('.col-sm-6:eq(0)', table.table().container() ) );
		
	editor.on('open', function ( e, mode, action ){
		if(action=='create'){
			cliente=$('#selcliente').val();
			proveedor=$('#selproveedor').val();
			editor.enable( ['clasificaciones.noparte', 'clasificaciones.proveedor_id', 'clasificaciones.cliente_id'] );
			if(proveedor!=0){
				editor.field('clasificaciones.proveedor_id').set(proveedor);
			}
			editor.field('clasificaciones.cliente_id').set(cliente);
		}else{
			editor.field( 'clasificaciones.origen' ).focus();
		}
	});

	editor.on('preSubmit', function ( e, json, data ) {
		$( "div.DTE_Processing_Indicator" ).html('<center><img src="./../images/cargando.gif" height="16" width="16">Guardando...</center>');
	});
	
	editor.on('postSubmit', function ( e, json, data, action) {
		$( "div.DTE_Processing_Indicator" ).html('');
	});

	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};

	$( "#selproveedor" ).change(function() {
		table.ajax.reload(null, true);
	});
	$( "#selcliente" ).change(function() {
		table.ajax.reload(null, true);
	});
	
	$('.modal').on('hidden.bs.modal', function (e) {
			var oModalsOpen = $('.in');
			if (oModalsOpen.length > 0 ) {$('body').addClass('modal-open');}
		});
		
	//abrir_modal_subir_layout_cajas();
});


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

function ajax_procesar_archivo_excel_layout_clasificaciones(){
	var xlsLayout = document.getElementById('archivo_xls_layout');
		
	if (!xlsLayout.files[0]){
		$("#mensaje_mod_subir_cajas").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Es necesario seleccionar un archivo para procesar la informacion.</div>');
		$('html, body').animate({scrollTop: $("#mensaje_mod_subir_cajas").offset().top }, 1000);
		$("#archivo_xls_layout").fileinput('clear');
		return;
	}
	
	var odata = new FormData();
	odata.append('xlsLayout', xlsLayout.files[0]);
	
	$.ajax({
		url: "ajax/clasificaciones/ajax_procesar_excel_layout.php",
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





