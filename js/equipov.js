var table;
$(document).ready(function() {
	table =$('#dtvacio').DataTable({
		"order": [[1, 'desc'],[2, 'desc']],
		"lengthMenu": [[10, 25, 50, 100, 1000, -1], [10, 25, 50, 100, 1000, "All"]],
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "./postequipov.php",
			"type": "POST",
			"data": function ( d ) {
				d.id_cliente = $('#selcliente').val();
				d.linea = $('#sellinea').val();
				d.fechaini = $('#fechaini1').val();
				d.fechafin = $('#fechafin1').val();
			}
		},
		"columns": [
			{ "data": "equipoentrada.id" },
			{ "data": "equipoentrada.fecha" },
			{ "data": "equipoentrada.hora" },
			{ "data": "equipoentrada.nombre_cliente" },
			{ "data": "equipoentrada.tipo_equipo" },
			{ "data": "equipoentrada.no_equipo" },
			{ "data": "equipoentrada.linea" },
			{ "data": "equipoentrada.id_foto_no_equipo" ,
				"mRender": function (data, type, row) {
					link='';
					if (!data){
						link='';
					}else{
						link='<center><a href="http://delbravoapps.com:8091/webtools/fotos_equipo/'+data+'.jpg" target="_blank">Equipo</a></center>';
					}
					if (!row.equipoentrada.id_foto_placas){
						link=link+'';
					}else{
						link=link+'<center><a href="http://delbravoapps.com:8091/webtools/fotos_equipo/'+row.equipoentrada.id_foto_placas+'.jpg" target="_blank">Placas</a></center>';
					}
					if (!row.equipoentrada.id_foto_marca){
						link=link+'';
					}else{
						link=link+'<center><a href="http://delbravoapps.com:8091/webtools/fotos_equipo/'+row.equipoentrada.id_foto_marca+'.jpg" target="_blank">Marca</a></center>';
					}
					if (!row.equipoentrada.id_foto_modelo){
						link=link+'';
					}else{
						link=link+'<center><a href="http://delbravoapps.com:8091/webtools/fotos_equipo/'+row.equipoentrada.id_foto_modelo+'.jpg" target="_blank">Modelo</a></center>';
					}
					return link;
				}
			}
			
		],
		select: {
            style:    'os',
        },
		responsive: true,
		"language": {
			"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36">Cargando...',
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
    ] );
    table.buttons().container()
        .appendTo( $('.col-sm-6:eq(0)', table.table().container() ) );


	var fechaini = $('#fechaini').datepicker({
		todayHighlight:true,
		autoclose: true,
		clearBtn: true
	}).on('changeDate', function(ev) {
		table.ajax.reload(null, true);
	}).data('datepicker');
	var fechafin = $('#fechafin').datepicker({
		todayHighlight:true,
		autoclose: true,
		clearBtn: true
	}).on('changeDate', function(ev) {
		table.ajax.reload(null, true);
	}).data('datepicker');
	$( "#sellinea" ).change(function() {
		table.ajax.reload(null, true);
	});
	$( "#selcliente" ).change(function() {
		table.ajax.reload(null, true);
	});
});