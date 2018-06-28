$(document).ready(function() {
	var table= $('#rpt_facmexame').dataTable( {
		"order": [[ 1, 'dsc' ]],
		"processing": true,
		"serverSide": true,
		"deferLoading": 0,
		"ajax": {
			"url": "./postrpt_facmexame.php",
			"type": "POST",
			"data": function ( d ) {
				/*d.fechaini = $('#fechaini1').val();
				d.fechafin = $('#fechafin1').val();
				d.cliente = $('#selcliente').val();*/
				d.patente = $('#patente').val();
				d.aduana = $('#aduana').val();
				d.pedimento = $('#pedimento').val();
			}
		},
		"columns": [
			{ "data": "factura" },
			{ "data": "fecha_factura" },
			{ "data": "trafico" },
			{ "data": "ctas_americanas" },
			{ "data": "pedimento" },
			{ "data": "cve_pedimento" },
			{ "data": "no_cta_gastos" },
			{ "data": "fecha_cta_gastos" },
			{ "data": "cargos_mexicanos" }
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
		"lengthMenu": [ [-1], [ "All"] ],
		responsive: false,
		"language": {
			"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36">Consultando información...',
			"sLengthMenu":     "Mostrar _MENU_ registros",
			"sZeroRecords":    "No se encontraron resultados",
			"sEmptyTable":     "Sin datos",
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
	} )
	.on('xhr.dt', function ( e, settings, json, xhr ) {
		if(!json.data[0]){
			return;
		}
		total_facturas=json.data[0].total_facturas;
        total_registros=json.recordsTotal;
		diferiencia=total_facturas-total_registros;
		if(total_facturas!=total_registros){
			alert('El total de facturas difiere al total de facturas del pedimento, es necesario capturar manualmente: '+diferiencia.toString()+' factura(s)');
		}
    } );
	/*var fechaini = $('#fechaini').datepicker({
		todayHighlight:true,
		autoclose: true,
		clearBtn: true
	}).data('datepicker');
	var fechafin = $('#fechafin').datepicker({
		todayHighlight:true,
		autoclose: true,
		clearBtn: true
	}).data('datepicker');*/
} );

function consultar(){
	patente = $('#patente').val();
	aduana = $('#aduana').val();
	pedimento = $('#pedimento').val();
	if(patente=='' || aduana=='' || pedimento==''){
		alert("Debe de introducir los todos los datos del pedimento (Patente, Aduana, Pedimento)");
		$('#patente').focus();
		return;
	}
	var table = $('#rpt_facmexame').DataTable();
	table.ajax.reload(null, true);
}