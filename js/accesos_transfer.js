var editor_accesos_sii, table_accesos_sii;
$(document).ready(function() {
	editor_accesos_sii = new $.fn.dataTable.Editor( {
        "ajax": {
			"url": "func/postaccesos_transfer.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
        table: "#dtaccesos",
        fields: [ 
			{
				label: "Usuario:",
				name: "usuario",
				attr:  {
					placeholder:  "Usuario SII"
				}
			},
			{
				label: "Password:",
				name: "password",
				attr:  {
					placeholder:  "Password SII"
				}
			},
			{
				label:  "Transfer:",
                name: "notransfer",
				type:  "select2",
				opts: {
					allowClear: true,
					placeholder:  "Seleccione un transfer"
				}
			}
        ],
		i18n: {
            create: {
                button: '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Nuevo',
                title:   'Nuevo acceso',
                submit:  'Guardar'
            },
            edit: {
                button: '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar',
                title:   'Modificar acceso',
                submit: 'Modificar'
            },
            remove: {
                button: '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Eliminar',
                title:   'Eliminar acceso',
                submit:  'Eliminar',
                confirm: {
                    _: 'Esta seguro que desea eliminar los accesos?',
                    1: 'Esta seguro que desea eliminar el acceso?'
                }
            },
            error: {
                system: 'Error desconocido contacte al administrador'
            }
        }
    } );
	
	var table_accesos_sii =$('#dtaccesos').DataTable({
		"processing": true,
		"serverSide": true,
		"dom": 'Bflrtip',
		"order": [[ 1, 'asc' ]],
		"searchDelay": 650,
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "func/postaccesos_transfer.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		"columns": [
			{ "data": "id_acceso" },
			{ "data": "usuario" },
			{ "data": "transfer_nom",
				"render": function ( data, type, row, meta ) {
					if (row.cliente_mpo == 0)
						return '-';
					else
						return data;
				}
			}
		],
		buttons:[ 
			{ extend: "create", editor: editor_accesos_sii, className: 'btn btn-primary' },
			{ extend: "edit",   editor: editor_accesos_sii, className: 'btn btn-primary' },
			{ extend: "remove", editor: editor_accesos_sii, className: 'btn btn-primary' }
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