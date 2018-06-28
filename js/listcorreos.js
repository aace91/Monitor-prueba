var  table_listCorreos;
$(document).ready(function() {

	var table_listCorreos =$('#dtlistcorreos').DataTable({
		"processing": true,
		"serverSide": true,
		"dom": 'Bflrtip',
		"order": [[ 2, 'asc' ]],
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "ajax/listcorreos/postlistcorreos.php",
			"type": "POST",
			"data": function ( d ) {
			}
		},
		"columns": [
			{ "data": "id_cliente",
				"width": "10%"
			},
			{ "data": "id_cliente" ,
				"render" :  function ( data, type, row, meta ) {
					return '<center><a href="editcorreos.php?nom_cliente='+row.nombre+'&id_cliente='+data+'" target="_blank"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Editar</a></center>';
					
				},
				"width": "10%"
			},
			{ "data": "nombre",
				"width": "80%"
			},
			{ "data": "correos"
			}
		],
		buttons:[ 
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