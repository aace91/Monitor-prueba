var id_precaptura=0, editor_precaptura, table_precaptura;
var selected = [];
$(document).ready(function() {
	
	
	var table= $('#plantilla_avanzada').dataTable( {
		"order": [[ 1, 'asc' ]],
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "./postrpt_plantilla_avanzada.php",
			"type": "POST",
			"data": function ( d ) {
				var text_referencias = $('#text_referencias').val().trim();
				var sRefs = '';
				// split into rows
				ref_Rows = text_referencias.split("\n");
				for(i=0; i<ref_Rows.length; i++){
					//if(ref_Rows[i].trim() != ''){
					sRefs += (i == 0 ? '' : ',') + "'" + ref_Rows[i].trim() + "'";
					//}
				}
				d.cliente = $('#selcliente').val();
				d.referencias = sRefs;
			}
		},
		"rowCallback": function( row, data ) {
            if ( $.inArray(data.DT_RowId, selected) !== -1 ) {
                $(row).addClass('selected');
            }
        },
		"columns": [
			{ "data": "referencia" },
			{ "data": "fecha_entrada" },
			{ "data": "proveedor" },
			{ "data": "descripcion" },
			{ "data": "referencia",
				"mRender": function (data, type, row) {
					if(row.precaptura==null||row.precaptura==''){
						precaptura=' <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> ';
					}else{
						precaptura=' <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> <a href="javascript:void(0);" onclick="abre_elimina_precaptura('+"'"+data+"'"+');return false;"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a> ';
					}
					return '<center>'+precaptura+'<a href="javascript:void(0);" onclick="abre_precaptura('+"'"+data+"'"+');return false;"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a></center>'
				}
			}
		],
		"buttons": [
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
			{
                text: 'Sel. Ref. precapturadas',
                action: function ( e, dt, node, config ) {
					var table = $('#plantilla_avanzada').DataTable();
					table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
						var data = this.data();
							table.rows( rowIdx ).nodes().to$().toggleClass( 'selected',false );
					} );
					selected=[];
					table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
						var data = this.data();
						if(data.precaptura){
							table.rows( rowIdx ).nodes().to$().toggleClass( 'selected' );
							hace_seleccion(data.referencia);
						}
					} );
                }
			},
			{
                text: 'Sel. todo',
                action: function ( e, dt, node, config ) {
					var table = $('#plantilla_avanzada').DataTable();
					table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
						var data = this.data();
							table.rows( rowIdx ).nodes().to$().toggleClass( 'selected',false );
					} );
					selected=[];
					table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
						var data = this.data();
							table.rows( rowIdx ).nodes().to$().toggleClass( 'selected' );
							hace_seleccion(data.referencia);
					} );
                }
			}
		],
		"dom": "<rf<Bt>lpi>",
		"sScrollX": '100%',
		"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
		responsive: false,
		"language": {
			"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36">Consultando información...',
			"sLengthMenu":     "Mostrar _MENU_ registros",
			"sZeroRecords":    "No se encontraron resultados",
			"sEmptyTable":     "No existe inventario en bodega",
			"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
			"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
			"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
			"sInfoPostFix":    "",
			"sSearch":         "Buscar:",
			"sUrl":            "",
			"sInfoThousands":  ",",
			"sLoadingRecords": "Cargando...",
			"oPaginate": {
				"sFirst":    "Primero",
				"sLast":     "Último",
				"sNext":     "Siguiente",
				"sPrevious": "Anterior"
			},
			"oAria": {
				"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
				"sSortDescending": ": Activar para ordenar la columna de manera descendente"
			}
		}
	} );
	$( "#selcliente" ).change(function() {
		var table = $('#plantilla_avanzada').DataTable();
		table.ajax.reload(null, true);
		selected = [];
	});
	
 
    $('#plantilla_avanzada tbody').on('click', 'tr', function (e) {
		if($(e.target).is(':last-child')==false){
			var id = this.id;
			$(this).toggleClass('selected');
			hace_seleccion(id);
		}
    } );
	
	$( "#btngenerar" ).click(function() {
		if (selected.length<=0){
			alert("No ha seleccionado ninguna referencia para exportar");
			return;
		}
		$.ajax({
			url:   'gen_plantillaa5.php',
			type:  'post',
			data:	{ referencias: selected},
			beforeSend: function () {
				$("#archivo").html('<center><img src="../images/cargando.gif" height="16" width="16">Generando</center>');
			},
			success:  function (response) {
				respuesta = JSON.parse(response);
				if (respuesta.codigo=='1'){
					$("#archivo").html(respuesta.link);
				}else{
					$("#archivo").html('<div class="alert alert-danger">'+respuesta.mensaje+'</div>');
				}
			},
			error: function(data){
				$("#archivo").html('<div class="alert alert-danger">Error contacte al administrador</div>');
			}
		});
	});
	
	$( "#btnlimpiar" ).click(function() {
		var table = $('#plantilla_avanzada').DataTable();
		table.ajax.reload(null, true);
		selected = [];
	});
	
	
	$('#modal_precaptura').on('shown.bs.modal', function () {
		table_precaptura.ajax.reload(null, true);
	});
	
	var table_precaptura=$('#dtprecaptura').DataTable({
		"order": [[1, 'desc']],
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "./postprecaptura.php",
			"type": "POST",
			"data": function ( d ) {
				d.id_precaptura = id_precaptura;
			}
		},
		"columns": [
			{
                "data": null,
                "defaultContent": "",
                "className": "select-checkbox",
                "orderable": false
            },
			{ "data": "id_proveedor",
				"orderable": false
			},
			{ "data": "no_factura"},
			{ "data": "fecha_factura" },
			{ "data": "monto_factura" },
			{ "data": "moneda"},
			{ "data": "incoterm" },
			{ "data": "subdivision" },
			{ "data": "certificado" },
			{ "data": "no_parte"},
			{ "data": "origen" },
			{ "data": "vendedor" },
			{ "data": "fraccion" },
			{ "data": "descripcion" },
			{ "data": "precio_partida" },
			{ "data": "umc" },
			{ "data": "cantidad_umc" },
			{ "data": "cantidad_umt" },
			{ "data": "preferencia" },
			{ "data": "marca" },
			{ "data": "modelo" },
			{ "data": "submodelo" },
			{ "data": "serie"} ,
			{ "data": "descripcion_cove" }
		],
		"rowCallback": function( row, data ) {
            if ( data.fra_restric!=null ) {
                $(row).addClass('danger');
            }
        },
		rowId: 'id_detalle',
		responsive: false,
		select: {
            style:    'os',
            selector: 'td:first-child'
        },
		keys: {
			columns: ':not(:first-child)',
			keys: [ 13 ]
		},
		"autoWidth": false,
		"sScrollX": true ,
		"language": {
			"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36">Cargando...',
		}
	});
	
	editor_precaptura = new $.fn.dataTable.Editor( {
		"ajax": {
			"url": "./postprecaptura.php",
			"type": "POST",
			"data": function ( d ) {
				d.id_precaptura = id_precaptura;
			}
		},
        table: "#dtprecaptura",
        fields: [ 
			{ 
				"label": "Cve Proveedor",
				"name": "id_proveedor", 
				"type": "readonly"
			},
			{ 
				"label": "No Fac",
				"name": "no_factura", 
				attr:  {
					maxlength: 15,
					class: 'text-uppercase'
				}
			},
			{ 
				"label": "Fec Fac",
				"name": "fecha_factura",
				"type": "datetime",
				"format": "DD/MM/YYYY"				
			},
			{ 
				"label": "Monto factura",
				"name": "monto_factura", 
				attr:  {
					maxlength: 20
				} 
			},
			{ 
				"label": "Moneda",
				"name": "moneda", 
				attr:  {
					maxlength: 3,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Incoterm",
				"name": "incoterm", 
				attr:  {
					maxlength: 3,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Subdivision",
				"name": "subdivision", 
				attr:  {
					maxlength: 1,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Certificado",
				"name": "certificado", 
				attr:  {
					maxlength: 1,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "No Parte",
				"name": "no_parte", 
				attr:  {
					maxlength: 50,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Origen",
				"name": "origen", 
				attr:  {
					maxlength: 3,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Vendedor",
				"name": "vendedor", 
				attr:  {
					maxlength: 3,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Fraccion",
				"name": "fraccion", 
				attr:  {
					maxlength: 8
				} 
			},
			{ 
				"label": "Descripcion",
				"name": "descripcion", 
				attr:  {
					maxlength: 250,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Precio partida",
				"name": "precio_partida", 
				attr:  {
					maxlength: 20,
				} 
			},
			{ 
				"label": "UMC",
				"name": "umc", 
				attr:  {
					maxlength: 2
				} 
			},
			{ 
				"label": "Cantidad UMC",
				"name": "cantidad_umc", 
				attr:  {
					maxlength: 20
				} 
			},
			{ 
				"label": "Cantidad UMT",
				"name": "cantidad_umt", 
				attr:  {
					maxlength: 20,
				} 
			},
			{ 
				"label": "Preferencia",
				"name": "preferencia", 
				attr:  {
					maxlength: 2,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Marca",
				"name": "marca", 
				attr:  {
					maxlength: 100,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Modelo",
				"name": "modelo", 
				attr:  {
					maxlength: 50,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Submodelo",
				"name": "submodelo", 
				attr:  {
					maxlength: 50,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Serie",
				"name": "serie", 
				attr:  {
					maxlength: 50,
					class: 'text-uppercase'
				} 
			},
			{ 
				"label": "Descripcion COVE",
				"name": "descripcion_cove", 
				attr:  {
					maxlength: 250,
					class: 'text-uppercase'
				} 
			}
        ],
        formOptions: {
            inline: {
                onBlur: 'submit'
            }
        },
		i18n: {
            create: {
                button: "Agregar",
                title:  "Agregar renglon",
                submit: "Agregar"
            },
            edit: {
                button: "Editar renglon",
                title:  "Editar renglon",
                submit: "Guardar"
            },
            remove: {
                button: "Eliminar",
                title:  "Eliminar renglon",
                submit: "Eliminar",
                confirm: {
                    _: "Esta seguro que desea eliminar %d renglones?",
                    1: "Esta seguro que desea eliminar 1 renglon?"
                }
            },
            error: {
                system: "Error desconocido, contacte al administrador"
            },
            datetime: {
                previous: 'Anterior',
                next:     'Siguiente',
                months:   [ 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre' ],
                weekdays: [ 'Dom', 'Lun', 'Mar', 'Mir', 'Jue', 'Vie', 'Sab' ]
            }
        }
    } );	
	$('#dtprecaptura').on( 'click', 'tbody td:not(:first-child)', function (e) {
       editor_precaptura.inline( this );
    } );
	
 
    // Inline editing on tab focus
    table_precaptura.on( 'key-focus', function ( e, datatable, cell ) {
        editor_precaptura.inline( cell.index() );
    } );
	
	var openVals;
    editor_precaptura
        .on( 'open', function () {
            // Store the values of the fields on open
            openVals = JSON.stringify( editor_precaptura.get() );
        } )
        .on( 'preBlur', function ( e ) {
            // On close, check if the values have changed and ask for closing confirmation if they have
            if ( openVals !== JSON.stringify( editor_precaptura.get() ) ) {
                return confirm( 'Tien cambios sin guardar desea salir?' );
            }
        } );
	
	new $.fn.dataTable.Buttons( table_precaptura, [
        {
			text:"Asignar proveedor",
			action: function ( e, dt, node, config ) {
                asigna_proveedor();
            } 
		},
		{ 
			extend: "edit", 
			editor: editor_precaptura,
			text: "Editar renglon"
		},
		{
			text:"Agregar renglon",
			action: function ( e, dt, node, config ) {
                agregar_renglon();
            } 
		},
		{
                extend: "selectedSingle",
                text: 'Copiar renglon',
                action: function ( e, dt, node, config ) {
                    // Place the selected row into edit mode (but hidden),
                    // then get the values for all fields in the form
                    var values = editor_precaptura.edit(
                            table_precaptura.row( { selected: true } ).index(),
                            false
                        )
                        .val();
 
                    // Create a new entry (discarding the previous edit) and
                    // set the values from the read values
                    editor_precaptura
                        .create( {
                            title: 'Copiar renglon',
                            buttons: 'Guardar'
                        } )
                        .set( values );
                }
        },
		/*{ 
			extend: "create", 
			editor: editor_precaptura,
			text: "Agregar renglon"
		},*/
		{ 
			extend: "remove", 
			editor: editor_precaptura,
			text: "Eliminar renglon"
		}
    ] );
    table_precaptura.buttons().container()
        .appendTo( $('.col-sm-6:eq(0)', table_precaptura.table().container() ) );
		
	$(document).on('hidden.bs.modal', function (event) {
	  if ($('.modal:visible').length) {
		$('body').addClass('modal-open');
	  }
	});
	
	$('#text_referencias').bind('input propertychange', function() {
		var table = $('#plantilla_avanzada').DataTable();
		table.ajax.reload(null, false);
	});
} );

function hace_seleccion(id){
	var index = $.inArray(id, selected);
	if ( index === -1 ) {
		selected.push( id );
	} else {
		selected.splice( index, 1 );
	}
	if(selected.length>1){
		fila="filas seleccionadas";
	}else{
		fila="fila seleccionada";
	}
	if($('#select-items_plantilla_avanzada').length == 0) {
		$("#plantilla_avanzada_info").append('<span class="select-item" id="select-items_plantilla_avanzada">' + selected.length.toString() + ' '+fila+'</span>');
	}else{
		if(selected.length>0){
			$("#select-items_plantilla_avanzada").html(selected.length.toString() +' '+fila);
		}else{
			$("#select-items_plantilla_avanzada").html('');
		}
	}
}

function abre_elimina_precaptura(referencia){
	$( "#btneliminar_precaptura" ).click(function() {
		eliminar_precaptura(referencia);
	});
	$("#eliminar_ref").html('Esta seguro que desea eliminar la precaptura de la referencia ' +referencia+'?');
	$('#modal_elimina_precaptura').modal({
		show: true,
		backdrop: 'static',
		keyboard: false
	});
}

function eliminar_precaptura(referencia){
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{
			action: 'eliminar_precaptura',
			referencia: referencia
		},
		beforeSend: function () {
			$("#eliminando").html('<div class="alert alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> Eliminando, espere un momento...</div>');
		},
		success:  function (response) {
			$("#eliminando").html('');
			if(response) {
				respuesta = JSON.parse(response);
				if (respuesta.codigo=='1'){
					var table = $('#plantilla_avanzada').DataTable();
					table.ajax.reload(null, false);
					$('#modal_elimina_precaptura').modal('hide');
				}else{
					alert(respuesta.mensaje);
				}
			}else{
				alert("Error desonocido al eliminar la precaptura");
			}
		},
		error: function(a,b){
			alert(a.status + ' [' + a.statusText + ']');
		}
	});
}


function agregar_renglon(){
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{
			action: 'agregar_renglon',
			id_precaptura: id_precaptura
		},
		beforeSend: function () {
			$('html, body').animate({ scrollTop: $('#cargando').offset().top }, 'slow');
			$("#guardando").html('<div class="alert alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> Guardando, espere un momento...</div>');
		},
		success:  function (response) {
			$("#guardando").html('');
			if(response) {
				respuesta = JSON.parse(response);
				if (respuesta.codigo=='1'){
					var table = $('#dtprecaptura').DataTable();
					table.ajax.reload(null, true);
				}else{
					alert(respuesta.mensaje);
				}
			}else{
				alert("Error desonocido al consultar la subcaptura");
			}
		},
		error: function(a,b){
			alert(a.status + ' [' + a.statusText + ']');
		}
	});
}

function asigna_proveedor(){
	cve_pro=$("#selprovpre").val();
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{
			action: 'asignaproveedor',
			id_precaptura: id_precaptura,
			cve_pro: cve_pro
		},
		beforeSend: function () {
			$('html, body').animate({ scrollTop: $('#cargando').offset().top }, 'slow');
			$("#guardando").html('<div class="alert alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> Guardando, espere un momento...</div>');
		},
		success:  function (response) {
			$("#guardando").html('');
			if(response) {
				respuesta = JSON.parse(response);
				if (respuesta.codigo=='1'){
					var table = $('#dtprecaptura').DataTable();
					table.ajax.reload(null, true);
				}else{
					alert(respuesta.mensaje);
				}
			}else{
				alert("Error desonocido al consultar la subcaptura");
			}
		},
		error: function(a,b){
			alert(a.status + ' [' + a.statusText + ']');
		}
	});
}

function abre_precaptura(referencia){
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{
			action: 'abreprecaptura',
			referencia: referencia
		},
		beforeSend: function () {
			$('html, body').animate({ scrollTop: $('#cargando').offset().top }, 'slow');
			$("#cargando").html('<div class="alert alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> Consultando, espere un momento...</div>');
		},
		success:  function (response) {
			$("#cargando").html('');
			if(response) {
				respuesta = JSON.parse(response);
				if (respuesta.codigo=='1'){
					$('#modal_precaptura').modal({
						show: true,
						backdrop: 'static',
						keyboard: false
					});
					id_precaptura=respuesta.id_precaptura;
					proveedor=respuesta.proveedor;
					$("#selprovpre").val("");
					$("#referencia_precaptura").html(referencia);
					$("#proveedor_precaptura").html(proveedor);
					var table = $('#plantilla_avanzada').DataTable();
					table.ajax.reload(null, false);
				}else{
					alert(respuesta.mensaje);
				}
			}else{
				alert("Error desonocido al consultar la subcaptura");
			}
		},
		error: function(a,b){
			alert(a.status + ' [' + a.statusText + ']');
		}
	});
}