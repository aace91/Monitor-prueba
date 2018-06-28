var  table_listCorreos, editor_listCorreos;
$(document).ready(function() {
	
	editor_listCorreos = new $.fn.dataTable.Editor( {
        "ajax": {
			"url": "ajax/listcorreos/postlistcorreos_edit.php",
			"type": "POST",
			"data": function ( d ) {
				d.id_cliente = $('#id_cliente').val();
			}
		},
		formOptions: {
			main: {
				focus: 1
			}
		},
        table: "#dtlistcorreos",
        fields: [ 
			{
				label: "ID Cliente:",
				name: "id_cliente",
				attr:  {
					placeholder:  ""
				}
			},
			{
				label: "Correo:",
				name: "correo",
				attr:  {
					placeholder:  "Escriba el correo"
				}
			},
			{
				label:  "Tipo:",
                name: "tpo",
				type:  "select2",
				opts: {
					placeholder: "Seleccione un tipo",
					allowClear: false
				}
            }
        ],
		i18n: {
            create: {
                button: '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Nuevo',
                title:   'Nuevo correo',
                submit:  'Guardar'
            },
            edit: {
                button: '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar',
                title:   'Modificar corrreo',
                submit: 'Modificar'
            },
            remove: {
                button: '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Eliminar',
                title:   'Eliminar correo',
                submit:  'Eliminar',
                confirm: {
                    _: 'Esta seguro que desea eliminar los correos?',
                    1: 'Esta seguro que desea eliminar el correo?'
                }
            },
            error: {
                system: 'Error desconocido contacte al administrador'
            }
        }
    } );

	table_listCorreos =$('#dtlistcorreos').DataTable({
		"processing": true,
		"serverSide": true,
		"dom": 'Bflrtip',
		"order": [[ 0,'asc' ]],
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "ajax/listcorreos/postlistcorreos_edit.php",
			"type": "POST",
			"data": function ( d ) {
				d.id_cliente = $('#id_cliente').val();
			}
		},
		"columns": [
			{ "data": "correo"},
			{ "data": "tpo_desc"}
		],
		buttons:[ 
			{ extend: "create",  editor: editor_listCorreos },
			{ extend: "edit",  editor: editor_listCorreos },
			{ extend: "remove",  editor: editor_listCorreos },
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
			}
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