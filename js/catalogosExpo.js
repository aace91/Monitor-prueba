var editor_lineasExpo, table_lineasExpo;
var editor_aaaExpo, table_aaaExpo;
var editor_transfersExpo, table_transfersExpo;
var editor_entregasExpo, table_entregasExpo;
var editor_clientesExpo, table_clientesExpo;

var id_catalogo, tipo_catalogo, table_contactosExpo;

$(document).ready(function() {

	var div_table = '';

/*----- Inicia Catalogo lineas-------------*/

	div_table = get_div_table('#dtlineasExpo');

	editor_lineasExpo = new $.fn.dataTable.Editor( {
        "ajax": {
			"url": "func/postlineasExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		formOptions: {
			main: {
				focus: 1
			}
		},
		table: div_table,
        fields: [ {
				label: "ID:",
				name: "id_linea",
				attr:  {
					placeholder:  "Numero de linea",
					readonly: "true"
				}
			},
			{
				label: "Nombre:",
				name: "nombre_linea",
				attr:  {
					placeholder:  "Nombre de linea",
					'class': "text-uppercase"
				}
			}
        ],
		i18n: {
            create: {
                button: '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Nueva',
                title:   'Nueva linea',
                submit:  'Guardar'
            },
            edit: {
                button: '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar',
                title:   'Modificar linea',
                submit: 'Modificar'
            },
            remove: {
                button: '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Eliminar',
                title:   'Eliminar linea',
                submit:  'Eliminar',
                confirm: {
                    _: 'Esta seguro que desea eliminar las lineas?',
                    1: 'Esta seguro que desea eliminar la linea?'
                }
            },
            error: {
                system: 'Error desconocido contacte al administrador'
            }
        }
    } );

	table_lineasExpo =$('#dtlineasExpo').DataTable({
		"processing": true,
		"serverSide": true,
		"dom": 'Bflrtip',
		"order": [[ 1, 'asc' ]],
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "func/postlineasExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		"columns": [
			{ "data": "id_linea" },
			{ "data": "nombre_linea" }
		],
		buttons:[
			{ extend: "create", editor: editor_lineasExpo, className: 'btn btn-primary' },
			{ extend: "edit",   editor: editor_lineasExpo, className: 'btn btn-primary' },
			{ extend: "remove", editor: editor_lineasExpo, className: 'btn btn-primary' },
			{
				extend: 'edit',
				text: '<span class="glyphicon glyphicon glyphicon-send" aria-hidden="true"></span> Contactos',
				action: function ( e, dt, node, config ) {
					var sIdCatalogo = dt.row({ selected: true }).data().id_linea;
					show_mdl_contactos_expo('LTR', sIdCatalogo);
				},
				className: 'btn btn-primary'
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

/*-----Fin Catalogo lineas-------------*/

/*----- Inicia Catalogo AAA-------------*/

	div_table = get_div_table('#dtaaaExpo');

	editor_aaaExpo = new $.fn.dataTable.Editor( {
        "ajax": {
			"url": "func/postaaaExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		formOptions: {
			main: {
				focus: 1
			}
		},
        table: div_table,
        fields: [ {
				label: "ID:",
				name: "id_aa",
				attr:  {
					placeholder:  "Numero de AA",
					readonly: "true"
				}
			},
			{
				label: "Nombre:",
				name: "nombre_aa",
				attr:  {
					placeholder:  "Nombre de AA",
					'class': "text-uppercase"
				}
			},
			{
				label: "Telefono:",
				name: "telefono_aa",
				attr:  {
					placeholder:  "Telefono de AA"
				}
			}
        ],
		i18n: {
            create: {
                button: '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Nueva',
                title:   'Nueva AA',
                submit:  'Guardar'
            },
            edit: {
                button: '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar',
                title:   'Modificar AA',
                submit: 'Modificar'
            },
            remove: {
                button: '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Eliminar',
                title:   'Eliminar AA',
                submit:  'Eliminar',
                confirm: {
                    _: 'Esta seguro que desea eliminar las AA`s?',
                    1: 'Esta seguro que desea eliminar la AA?'
                }
            },
            error: {
                system: 'Error desconocido contacte al administrador'
            }
        }
    } );

	table_aaaExpo =$('#dtaaaExpo').DataTable({
		"processing": true,
		"serverSide": true,
		"dom": 'Bflrtip',
		"order": [[ 1, 'asc' ]],
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "func/postaaaExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		"columns": [
			{ "data": "id_aa" },
			{ "data": "nombre_aa" },
			{ "data": "telefono_aa" }
		],
		buttons:[
			{ extend: "create", editor: editor_aaaExpo, className: 'btn btn-primary' },
			{ extend: "edit",   editor: editor_aaaExpo, className: 'btn btn-primary' },
			{ extend: "remove", editor: editor_aaaExpo, className: 'btn btn-primary' },
			{
				extend: 'edit',
				text: '<span class="glyphicon glyphicon glyphicon-send" aria-hidden="true"></span> Contactos',
				action: function ( e, dt, node, config ) {
					var sIdCatalogo = dt.row({ selected: true }).data().id_aa;
					show_mdl_contactos_expo('AAA', sIdCatalogo);
				},
				className: 'btn btn-primary'
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

/*-----Fin Catalogo AAA-------------*/

/*----- Inicia Catalogo transfers-------------*/

	div_table = get_div_table('#dttransfersExpo');

	editor_transfersExpo = new $.fn.dataTable.Editor( {
        "ajax": {
			"url": "func/posttransfersExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		formOptions: {
			main: {
				focus: 1
			}
		},
        table: div_table,
        fields: [ {
				label: "ID:",
				name: "id_transfer",
				attr:  {
					placeholder:  "Numero de transfer",
					readonly: "true"
				}
			},
			{
				label: "Nombre:",
				name: "nombre_transfer",
				attr:  {
					placeholder:  "Nombre de transfer",
					'class': "text-uppercase"
				}
			},
			{
				label: "CAAT:",
				name: "caat",
				attr:  {
					placeholder:  "CAAT",
					'class': "text-uppercase"
				}
			},
			{
				label: "SCAC:",
				name: "scac",
				attr:  {
					placeholder:  "SCAC",
					'class': "text-uppercase"
				}
			}
        ],
		i18n: {
            create: {
                button: '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Nuevo',
                title:   'Nuevo transfer',
                submit:  'Guardar'
            },
            edit: {
                button: '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar',
                title:   'Modificar transfer',
                submit: 'Modificar'
            },
            remove: {
                button: '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Eliminar',
                title:   'Eliminar transfer',
                submit:  'Eliminar',
                confirm: {
                    _: 'Esta seguro que desea eliminar las lineas?',
                    1: 'Esta seguro que desea eliminar la linea?'
                }
            },
            error: {
                system: 'Error desconocido contacte al administrador'
            }
        }
    } );

	table_transfersExpo =$('#dttransfersExpo').DataTable({
		"processing": true,
		"serverSide": true,
		"dom": 'Bflrtip',
		"order": [[ 1, 'asc' ]],
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "func/posttransfersExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		"columns": [
			{ "data": "id_transfer" },
			{ "data": "nombre_transfer" },
			{ "data": "caat" },
			{ "data": "scac" }
		],
		buttons:[
			{ extend: "create", editor: editor_transfersExpo, className: 'btn btn-primary' },
			{ extend: "edit",   editor: editor_transfersExpo, className: 'btn btn-primary' },
			{ extend: "remove", editor:editor_transfersExpo, className: 'btn btn-primary' },
			{
				extend: 'edit',
				text: '<span class="glyphicon glyphicon glyphicon-send" aria-hidden="true"></span> Contactos',
				action: function ( e, dt, node, config ) {
					var sIdCatalogo = dt.row({ selected: true }).data().id_transfer;
					show_mdl_contactos_expo('TRA', sIdCatalogo);
				},
				className: 'btn btn-primary'
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

/*-----Fin Catalogo transfer-------------*/

/*----- Inicia Catalogo entregas-------------*/

	div_table = get_div_table('#dtentregasExpo');

	editor_entregasExpo = new $.fn.dataTable.Editor( {
        "ajax": {
			"url": "func/postentregasExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		formOptions: {
			main: {
				focus: 1
			}
		},
        table: div_table,
        fields: [ {
				label: "ID:",
				name: "id_entrega",
				attr:  {
					placeholder:  "Numero de entrega",
					readonly: "true"
				}
			},
			{
				label: "Nombre:",
				name: "nombre_entrega",
				attr:  {
					placeholder:  "Nombre de entrega",
					'class': "text-uppercase"
				}
			},
			{
				label: "Dirección:",
				name: "direccion_entrega",
				attr:  {
					placeholder:  "Dirección de entrega",
					'class': "text-uppercase"
				}
			}
        ],
		i18n: {
            create: {
                button: '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Nueva',
                title:   'Nueva punto entrega',
                submit:  'Guardar'
            },
            edit: {
                button: '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar',
                title:   'Modificar punto entrega',
                submit: 'Modificar'
            },
            remove: {
                button: '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Eliminar',
                title:   'Eliminar punto entrega',
                submit:  'Eliminar',
                confirm: {
                    _: 'Esta seguro que desea eliminar los puntos entregas?',
                    1: 'Esta seguro que desea eliminar el punto de entrega?'
                }
            },
            error: {
                system: 'Error desconocido contacte al administrador'
            }
        }
    } );

	table_entregasExpo =$('#dtentregasExpo').DataTable({
		"processing": true,
		"serverSide": true,
		"dom": 'Bflrtip',
		"order": [[ 1, 'asc' ]],
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "func/postentregasExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		"columns": [
			{ "data": "id_entrega" },
			{ "data": "nombre_entrega" },
			{ "data": "direccion_entrega" }
		],
		buttons:[
			{ extend: "create", editor: editor_entregasExpo, className: 'btn btn-primary' },
			{ extend: "edit",   editor: editor_entregasExpo, className: 'btn btn-primary' },
			{ extend: "remove", editor:editor_entregasExpo, className: 'btn btn-primary' }
		],
		select: {
            style:    'os',
        },
		responsive: true,
		"language": {
			"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36"> Cargando espere...',
		}
	});

/*-----Fin Catalogo transfer-------------*/

	$.fn.modal.Constructor.prototype.enforceFocus = function() {};
});

/*----- Inicia Catalogo clientes-------------*/

	div_table = get_div_table('#dtclientesExpo');

	editor_clientesExpo = new $.fn.dataTable.Editor( {
        "ajax": {
			"url": "func/postclientesExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		formOptions: {
			main: {
				focus: 0
			}
		},
		table: div_table,
        fields: [ {
				label: "ID:",
				name: "id_cliente",
				attr:  {
					placeholder:  "Id de Cliente",
					'class': "text-uppercase"
				}
			},
			{
				label: "Nombre:",
				name: "nombre_cliente",
				attr:  {
					placeholder:  "Nombre del Cliente",
					'class': "text-uppercase"
				}
			},
			{
				label: "RFC:",
				name: "rfc",
				attr:  {
					placeholder:  "RFC",
					'class': "text-uppercase"
				}
			},
			{
				label: "Ejecutivo principal:",
				name: "ejecutivo_id",
				type: "select2",
				opts: {
		       "placeholder": "Seleccione un ejecutivo",
		       "allowClear": true
				 }
			},
			{
					label: "Ejecutivos adicionales:",
					name: "tblusua[].Usuario_id",
					type: "checkbox"
			}
        ],
		i18n: {
            create: {
                button: '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Nueva',
                title:   'Nuevo cliente',
                submit:  'Guardar'
            },
            edit: {
                button: '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar',
                title:   'Modificar cliente',
                submit: 'Modificar'
            },
            remove: {
                button: '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Eliminar',
                title:   'Eliminar cliente',
                submit:  'Eliminar',
                confirm: {
                    _: 'Esta seguro que desea eliminar los cliente?',
                    1: 'Esta seguro que desea eliminar el cliente?'
                }
            },
            error: {
                system: 'Error desconocido contacte al administrador'
            }
        }
    } );

	table_clientesExpo =$('#dtclientesExpo').DataTable({
		"processing": true,
		"serverSide": true,
		"dom": 'Bflrtip',
		"order": [[ 1, 'asc' ]],
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "func/postclientesExpo.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		"columns": [
			{ "data": "id_cliente" },
			{ "data": "nombre_cliente" },
			{ "data": "rfc" }
		],
		buttons:[
			{ extend: "create", editor: editor_clientesExpo, className: 'btn btn-primary' },
			{ extend: "edit",   editor: editor_clientesExpo, className: 'btn btn-primary' },
			{ extend: "remove", editor: editor_clientesExpo, className: 'btn btn-primary' },
			{
				extend: 'edit',
				text: '<span class="glyphicon glyphicon glyphicon-send" aria-hidden="true"></span> Contactos',
				action: function ( e, dt, node, config ) {
					var sIdCatalogo = dt.row({ selected: true }).data().id_cliente;
					show_mdl_contactos_expo('CLI', sIdCatalogo);
				},
				className: 'btn btn-primary'
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

/*-----Fin Catalogo clientes-------------*/

/*----- Funciones Extras -------------*/

function get_div_table(sSelector) {
	if ($(sSelector).length){
		return sSelector;
	}else{
		return '';
	}
}

/* ..:: Validamos el correo electronico ::.. */
function fcn_validate_email(email) {
	var email_regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i;
	if(!email_regex.test(email)) {
		return false;
	} else{
		return true;
	}
}

/**************************************/
/* Catalogo Contactos Expo */
/**************************************/
function show_mdl_contactos_expo(sCatalogo, sIdCatalogo) {
	tipo_catalogo = sCatalogo;
	id_catalogo = sIdCatalogo;
	mdl_contactos_expo_func('cancel');

	switch(tipo_catalogo) {
		case 'CLI':
			$('#idiv_mdl_contactos_expo_tipo').show();
			break;

		default:
			$('#idiv_mdl_contactos_expo_tipo').hide();
	}

	$('#modal_contactos_expo').modal({ show: true, backdrop: 'static', keyboard: false });
	cargar_grid_contactos_expo();
}

function mdl_contactos_expo_func(pOpt) {
	try {
		$('#idiv_mdl_contactos_expo_msj').empty();
		switch(pOpt) {
			case 'cancel':
				$('#itxt_mdl_contactos_expo_correo').val('');
				$('#itxt_mdl_contactos_expo_correo').prop('disabled', true);
				$('#itxt_mdl_contactos_expo_nombre').val('');
				$('#itxt_mdl_contactos_expo_nombre').prop('disabled', true);
				$('#ibtn_mdl_contactos_expo_add').show();
				$('#ibtn_mdl_contactos_expo_save').hide();
				$('#ibtn_mdl_contactos_expo_cancel').hide();
				break;
			case 'add':
				$('#itxt_mdl_contactos_expo_correo').prop('disabled', false);
				$('#itxt_mdl_contactos_expo_nombre').prop('disabled', false);
				$('#ibtn_mdl_contactos_expo_add').hide();
				$('#ibtn_mdl_contactos_expo_save').show();
				$('#ibtn_mdl_contactos_expo_cancel').show();
				break;
			case 'save':
				var sEmail = $.trim($('#itxt_mdl_contactos_expo_correo').val());
				var sNombre = $.trim($('#itxt_mdl_contactos_expo_nombre').val());

				if (sEmail == '') {
					$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> Correo electr&oacute;nico obligatorio.</div>');
					return;
				} else if (fcn_validate_email(sEmail) == false) {
					$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> Formato de correo electr&oacute;nico incorrecto.</div>');
					return;
				}

				if (sNombre == '') {
					$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> El nombre es obligatorio.</div>');
					return;
				}

				var oData = {
					action: 'insertar_contacto',
					tipo_catalogo: tipo_catalogo,
					tipo_contacto: ((tipo_catalogo == 'CLI')? $('#isel_mdl_contactos_expo_tipo').val() : '-1'),
					id_catalogo: id_catalogo,
					sEmail: sEmail,
					sNombre: sNombre
				};


				$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-info"><img src="../images/cargando.gif" height="16" width="16"> Procesando espere...</div>');
				$.post( "func/contactosExpoFunc.php", oData, function(response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						$('#idiv_mdl_contactos_expo_msj').empty();
						if (respuesta.Codigo == '1'){
							mdl_contactos_expo_func('cancel');
							cargar_grid_contactos_expo();
							$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-success"><strong>Exito!</strong> ' + respuesta.Mensaje + '</div>');
						}else{
							var strMensaje = respuesta.Mensaje + respuesta.Error;
							$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> ' + strMensaje + '</div>');
						}
					}else{
						$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> La sesión del usuario ha caducado, por favor acceda de nuevo.</div>');
						setTimeout(function () {window.location.replace('../logout.php');},4000);
					}
				}).fail( function (xhr, textStatus, errorThrown) {
					var strMensaje = xhr.status+' [' + xhr.statusText + ']';
					$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> ' + strMensaje + '</div>');
				});
				break;
		}
	} catch (err) {
		var strMensaje = 'fcn_cat_problemas_cancel() :: ' + err.message;
		$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> ' + strMensaje + '</div>');
    }
}

function cargar_grid_contactos_expo() {
	try {
		if (table_contactosExpo == null) {
			var div_table_name = 'dtcontactos_expo';
			var div_refresh_name = div_table_name + '_refresh';

			table_contactosExpo = $('#' + div_table_name);
			table_contactosExpo.DataTable({
				order: [[0, 'desc']],
				processing: true,
				serverSide: false,
				ajax: {
					"url": "func/postcontactosExpo.php",
					"type": "POST",
					"timeout": 20000,
					"data": function ( d ) {
			            d.tipo_catalogo = tipo_catalogo;
						d.tipo_contacto = ((tipo_catalogo == 'CLI')? $('#isel_mdl_contactos_expo_tipo').val() : '-1');
						d.id_catalogo = id_catalogo;
					}
				},
				columns: [
					{ data: "email"},
					{ data: "nombre"},
					{
						data: null,
						className: "text-center",
						defaultContent: '<a class="btn btn-danger btn-xs editor_' + div_table_name + '_eliminar"><i class="fa fa-trash" aria-hidden="true"></i></a>'
					}
				],
				responsive: true,
				aLengthMenu: [
					[10, 25, 50, 100, 200, -1],
					[10, 25, 50, 100, 200, "All"]
				],
				iDisplayLength: 10,
				language: {
					sProcessing: '<img src="../images/cargando.gif" height="36" width="36"> Cargando espere...',
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
				buttons: []
			});

			table_contactosExpo.on('click', 'a.editor_' + div_table_name + '_eliminar', function (e) {
				try {
					var current_row = $(this).parents('tr');//Get the current row
					if (current_row.hasClass('child')) {//Check if the current row is a child row
						current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
					}
					var table = table_contactosExpo.DataTable();
					var oData = table.row(current_row).data();

					var id_contacto = oData.id_contacto;
					var email = oData.email;
					var mensaje = confirm("¿Eliminar el contacto " + email + "?");
					if (mensaje) {
						var oData = {
							action: 'eliminar_contacto',
							id_contacto: id_contacto
						};

						$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-info"><img src="../images/cargando.gif" height="16" width="16"> Procesando espere...</div>');
						$.post( "func/contactosExpoFunc.php", oData, function(response) {
							if (response != '500'){
								var respuesta = JSON.parse(response);
								$('#idiv_mdl_contactos_expo_msj').empty();
								if (respuesta.Codigo == '1'){
									mdl_contactos_expo_func('cancel');
									cargar_grid_contactos_expo();
									$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-success"><strong>Exito!</strong> ' + respuesta.Mensaje + '</div>');
								}else{
									var strMensaje = respuesta.Mensaje + respuesta.Error;
									$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> ' + strMensaje + '</div>');
								}
							}else{
								$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> La sesión del usuario ha caducado, por favor acceda de nuevo.</div>');
								setTimeout(function () {window.location.replace('../logout.php');},4000);
							}
						}).fail( function (xhr, textStatus, errorThrown) {
							var strMensaje = xhr.status+' [' + xhr.statusText + ']';
							$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> ' + strMensaje + '</div>');
						});
					}
				} catch (err) {
					var strMensaje = 'a.editor_' + div_table_name + '_eliminar() :: ' + err.message;
					show_error(strMensaje);
				}
			} );
		} else {
			table_contactosExpo.DataTable().ajax.reload();
		}
	} catch (err) {
		var strMensaje = 'cargar_grid_contactos_expo() :: ' + err.message;
		$('#idiv_mdl_contactos_expo_msj').html('<div class="alert alert-danger"><strong>Error!</strong> ' + strMensaje + '</div>');
    }
}
