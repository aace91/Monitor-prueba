var editor_entradasExpo, table_entradasExpo;
$(document).ready(function() {
	editor_entradasExpo = new $.fn.dataTable.Editor( {
        "ajax": {
			"url": "func/postentradasExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		focus: 2,
        table: "#dtentradasExpo",
        fields: [ 
			{
				label: "Referencia Original:",
				name: "referencia_original",
				attr:  {
					placeholder:  "En caso de Rectificación ingrese la referencia original"
				}
			},
			{
				label: "Referencia:",
				name: "referencia",
				attr:  {
					placeholder:  "Referencia",
					readonly: "true"
				}
			},
			{
				label: "Fecha alta:",
				name: "fecha_alta",
				attr:  {
					placeholder:  "Fecha alta",
					readonly: "true"
				}
			},
			{
				label:  "Cliente:",
                name: "id_cliente",
				type:  "select2",
				/*"opts": {
					"allowClear": false,
					ajax: {
						url: "func/funciones.php",
						type: "POST",
						dataType: 'json',
						delay: 250,
						data: function (params) {
							return {
								q: params.term, // search term
								action: 'buscaClienteCasa'
							};
						},
						processResults: function (data, page) {
						  // parse the results into the format expected by Select2.
						  // since we are using custom formatting functions we do not need to
						  // alter the remote JSON data
						  return {
							results: data.items
						  };
						},
						cache: true,
						minimumInputLength: 1
					}
				}*/
            }
        ],
		i18n: {
            create: {
                button: '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Nueva',
                title:   'Nueva entrada',
                submit:  'Guardar'
            },
            edit: {
                button: '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar',
                title:   'Modificar entrada',
                submit: 'Modificar'
            },
            remove: {
                button: '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Eliminar',
                title:   'Eliminar entrada',
                submit:  'Eliminar',
                confirm: {
                    _: 'Esta seguro que desea eliminar las entradas?',
                    1: 'Esta seguro que desea eliminar la entrada?'
                }
            },
            error: {
                system: 'Error desconocido contacte al administrador'
            }
        }
    } );
	
	editor_entradasExpo.on( 'initCreate', function ( e, json, data ) {
		editor_entradasExpo.enable('referencia_original');
	} );
	
	editor_entradasExpo.on( 'initEdit', function ( e, json, data ) {
		editor_entradasExpo.disable('referencia_original');
	} );
	
	editor_entradasExpo.on( 'postEdit', function ( e, json, data ) {
		show_modal_ok('Referencia actualizada [' + data.referencia + ']');
	} );
	
	editor_entradasExpo.on( 'postCreate', function ( e, json, data ) {
		show_modal_ok('Referencia Creada [' + data.referencia + ']');
	} );

	var table_entradasExpo =$('#dtentradasExpo').DataTable({
		"processing": true,
		"serverSide": true,
		"dom": 'Bflrtip',
		"order": [[ 1, 'dsc' ]],
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "func/postentradasExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		"columns": [
			{ "data": "referencia" },
			{ "data": "fecha_alta" },
			{ "data": "cliente" }
		],
		buttons:[ 
			{ extend: "create", editor: editor_entradasExpo, className: 'btn btn-primary' },
			{ extend: "edit",   editor: editor_entradasExpo, className: 'btn btn-primary' },
			{ extend: "remove", editor: editor_entradasExpo, className: 'btn btn-primary' }
		],
		select: {
            style:    'os',
        },
		responsive: true,
		"language": {
			"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36"> Cargando espere...',
		}
	});
	
	$.fn.modal.Constructor.prototype.enforceFocus = function() {};
});

/* ..:: Funcion que muestra el mensaje de ok notificando la referencia ::.. */
function show_modal_ok(sMensaje) {
	if (sMensaje == null || sMensaje == undefined) {
		sMensaje = '';
	}
	
    $('#modalmessagebox_ok_titulo').html('');
	$('#modalmessagebox_ok_mensaje').html(' <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ' + sMensaje);						
	setTimeout(function () {
		$('#modalmessagebox_ok').modal({ show: true });
	},500);
}