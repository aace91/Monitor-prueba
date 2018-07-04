var table;
$(document).ready(function() {
	table= $('#inventario').DataTable( {
		"order": [[ 1, 'asc' ]],
		"processing": true,
		"serverSide": false,
		"ajax": {
			"url": "./postinv.php",
			"type": "POST",
			"data": function ( d ) {
				d.tipo_doc = $('#seltpo').val();
				d.cliente = $('#selcliente').val();
			}
		},
		"initComplete": function () {
			table.buttons().container()
				.appendTo($('#inventario_wrapper .col-sm-6:eq(0)'));
		},
		"columns": [
			{ "data": "referencia" },
			{
				"data": "fechaentrada",
				"className": "text-center",
				"mRender": function (data, type, row) {
					if (type === 'export') {
						return data;
					} else {
						if (data == '' || data == null) {
							return '<a href="#" data-toggle="tooltip" data-placement="bottom" title="Virtual entry"><i class="fa fa-exclamation-triangle" aria-hidden="true" style="color:#f0ad4e;"></i></a> ' + row.fechavirtual;
						} else {
							return data;
						}
					}
				}
			},
			{ "data": "po" },
			{ "data": "proveedor" },
			{ "data": "descripcion" },
			{ "data": "foto1",
				"mRender": function (data,type,row) {
					var link='';
					if (!row.foto1){
						link=link+'';
					}else{
						link=link+'<a href="'+row.foto1+'" target="_blank">1</a>';
					}
					if (!row.foto2){
						link=link+'';
					}else{
						link=link+'  <a href="'+row.foto2+'" target="_blank">2</a>';
					}
					if (!row.foto3){
						link=link+'';
					}else{
						link=link+'  <a href="'+row.foto3+'" target="_blank">3</a>';
					}
					if (!row.foto4){
						link=link+'';
					}else{
						link=link+'  <a href="'+row.foto4+'" target="_blank">4</a>';
					}
					if (!row.foto5){
						link=link+'';
					}else{
						link=link+'  <a href="'+row.foto5+'" target="_blank">5</a>';
					}
					return link;
				}				
			},
			{ "data": "documentacion",
				"mRender": function (data, type, full) {
					if (!data){
						return '';
					}else{
						return '<a href="'+data+'" target="_blank">Ver</a>';
					}
				}
			}
		],
		responsive: true,
		"language": {
			"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36"/>Consultando información...',
			"sLengthMenu":     "Mostrar _MENU_ registros",
			"sZeroRecords":    "No se encontraron resultados",
			"sEmptyTable":     "Ningún dato disponible en esta tabla",
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
		},
		select: {
			style: 'multi'
		},
		lengthChange: false,
		buttons: [
			'pageLength',
			'selectAll',
			'selectNone',
			'colvis'
		]
	} );

	table.on('draw', function () {
		$('[data-toggle="tooltip"]').tooltip();
	});

	$('#seltpo').on('change', function (e) {
		compruebacheck();
		var table = $('#inventario').DataTable();
		table.ajax.reload();
	});
	$('.selectpicker').select2();

	$("#documento").fileinput({
		allowedFileExtensions: ["pdf"],
		elErrorContainer: "#error",
		browseLabel: "Browse",
		removeLabel: "Delete",
		uploadLabel: "Save",
		uploadClass: "btn btn-success",
		msgInvalidFileExtension: "Extension overrides for file {name}. Only {extensions} files are supported.",
		msgValidationError: "<span class='text-danger'><i class='glyphicon glyphicon-exclamation-sign'></i> Failed to select the file</span>",
		msgLoading: "Processing &hellip;",
		uploadUrl: "savedoc.php", // your upload server url
		uploadExtraData: function () {
			/*var oTable = $('#inventario').dataTable();
			var oTT = TableTools.fnGetInstance( 'inventario' );
			var aData = oTT.fnGetSelectedData();*/
			//console.log(table.rows( { selected: true } ).data().toArray());
			return {
				id_subio: $("#idejecutivo").val(),
				tipo: $("#seltpo").val(),
				referencias: JSON.stringify(table.rows({ selected: true }).data().toArray(), null, 2)
			};
		}
	});
	$('#documento').on('fileuploaded', function (event, data, previewId, index) {
		var table = $('#inventario').DataTable();
		table.ajax.reload();
	});
} );

function cambiacliente() {
	table.ajax.reload();
}
