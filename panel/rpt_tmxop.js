$(document).ready(function() {
	var table= $('#rpt_tmxop').dataTable( {
		"order": [[ 1, 'dsc' ]],
		"processing": true,
		"serverSide": true,
		"deferLoading": 0,
		"ajax": {
			"url": "./postrpt_tmxop.php",
			"type": "POST",
			"data": function ( d ) {
				d.fechaini = $('#fechaini1').val();
				d.fechafin = $('#fechafin1').val();
				d.cliente = $('#selcliente').val();
			}
		},
		"columns": [
			{ "data": "referencia" },
			{ "data": "importador" },
			{ "data": "orden_compra" },
			{ "data": "proveedor" },
			{ "data": "facturas" },
			{ "data": "valor" },
			{ "data": "origen" },
			{ "data": "incoterms" },
			{ "data": "comentarios" },
			{ "data": "status" },
			{ "data": "linea_ame"},
			{ "data": "bol"},
			{ "data": "no_bultos"},
			{ "data": "peso"},
			{ "data": "pedimento"},
			{ "data": "destino"},
			{ "data": "linea_mex"},
			{ "data": "no_unidad"},
			{ "data": "fec_orig"},
			{ "data": "fec_adu"},
			{ "data": "fec_info"},
			{ "data": "fec_desp"},
			{ "data": "fec_entre"},
			{ "data": "remision"}
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
	var fechaini = $('#fechaini').datepicker({
		todayHighlight:true,
		autoclose: true,
		clearBtn: true
	}).data('datepicker');
	var fechafin = $('#fechafin').datepicker({
		todayHighlight:true,
		autoclose: true,
		clearBtn: true
	}).data('datepicker');
} );

function consultar(){
	var table = $('#rpt_tmxop').DataTable();
	table.ajax.reload(null, true);
}