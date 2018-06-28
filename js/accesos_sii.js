var editor_accesos_sii, table_accesos_sii;
$(document).ready(function() {
	editor_accesos_sii = new $.fn.dataTable.Editor( {
        "ajax": {
			"url": "func/postaccesos_sii.php",
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
				label: "Es admin:",
				name:  "admin",
				type:  "checkbox",
				options: [
					{ label: "Si", value: 1 }
				],
				separator: '',
				unselectedValue: 0
			},
			{
				label:  "Cliente impo:",
                name: "cliente_impo",
				type:  "select2",
				opts: {
					allowClear: true,
					placeholder:  "Seleccione un cliente"
				}
			},
			{
				label:  "Cliente NB:",
                name: "cliente_nb",
				type:  "select2",
				opts: {
					allowClear: true,
					placeholder:  "Seleccione un cliente"
				}
			},
			{
				label:  "Cliente Ped:",
                name: "cliente_ped",
				type:  "select2",
				opts: {
					allowClear: true,
					placeholder:  "Seleccione un cliente"
				}
			},
			{
				label:  "Cliente expo:",
                name: "cliente_expo",
				type:  "select2",
				opts: {
					allowClear: true,
					placeholder:  "Seleccione un cliente"
				}
			},
			{
				label:  "Cliente GAB:",
                name: "cliente_gab",
				type:  "select2",
				opts: {
					allowClear: true,
					placeholder:  "Seleccione un cliente"
				}
			},
			{
				label:  "Cliente SAB:",
                name: "cliente_sab",
				type:  "select2",
				opts: {
					allowClear: true,
					placeholder:  "Seleccione un cliente"
				}
			},
			{
				label:  "Cliente QB Del Bravo:",
                name: "cliente_qb_delbravo",
				type:  "select2",
				opts: {
					allowClear: true,
					placeholder:  "Seleccione un cliente"
				}
			},
			{
				label:  "Cliente QB Benavides:",
                name: "cliente_qb_benavides",
				type:  "select2",
				opts: {
					allowClear: true,
					placeholder:  "Seleccione un cliente"
				}
			},
			{
				label: "Editar clasificaciones:",
				name:  "clasifica_mod",
				type:  "checkbox",
				options: [
					{ label: "Si", value: 1 }
				],
				separator: '',
				unselectedValue: 0
			},
			{
				label: "Ver clasificaciones:",
				name:  "clasifica_read",
				type:  "checkbox",
				options: [
					{ label: "Si", value: 1 }
				],
				separator: '',
				unselectedValue: 0
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
		"searchDelay": 2500,
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "func/postaccesos_sii.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		"columns": [
			{ "data": "id_acceso" },
			{ "data": "usuario" },
			{ "data": "ultima_visita" },
			{ "data": "admin",
				"render": function ( data, type, row, meta ) {
					if (data == 1)
						return 'Si';
					else
						return 'No';
				}
			},
			{ "data": "cli_impo_nom",
				"render": function ( data, type, row, meta ) {
					if (row.cliente_mpo == 0)
						return '-';
					else
						return data;
				}
			},
			{ "data": "cli_nb_nom" ,
				"render": function ( data, type, row, meta ) {
					if (row.cliente_nb == 0)
						return '-';
					else
						return data;
				}
			},
			{ "data": "cli_ped_nom",
				"render": function ( data, type, row, meta ) {
					if (row.cliente_ped == '0')
						return '-';
					else
						return data;
				} 
			},
			{ "data": "cli_expo_nom",
				"render": function ( data, type, row, meta ) {
					if (row.cliente_expo == '0')
						return '-';
					else
						return data;
				} 
			},
			{ "data": "cli_gab_nom",
				"render": function ( data, type, row, meta ) {
					if (row.cliente_gab == 0)
						return '-';
					else
						return data;
				} 
			},
			{ "data": "cli_sab_nom",
				"render": function ( data, type, row, meta ) {
					if (row.cliente_sab == 0)
						return '-';
					else
						return data;
				}
			},
			{ "data": "cli_qb_nom",
				"render": function ( data, type, row, meta ) {
					if (!row.cliente_qb_delbravo)
						return '-';
					else
						return data;
				} 
			},
			{ "data": "cli_qb_bac_nom",
				"render": function ( data, type, row, meta ) {
					if (!row.cliente_qb_benavides)
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
	
	$('#dtaccesos_filter input').unbind();
	$('#dtaccesos_filter input').bind('keyup', function(e) {
		if(e.keyCode == 13) {
			$('#dtaccesos').dataTable().fnFilter(this.value);   
		}
	});
	
	$.fn.modal.Constructor.prototype.enforceFocus = function() {};
});