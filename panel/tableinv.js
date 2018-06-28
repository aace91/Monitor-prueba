$(document).ready(function() {
	var table= $('#inventario').dataTable( {
		"order": [[ 1, 'asc' ]],
		"processing": true,
		"serverSide": true,
		"aoSearchCols": [
		  null,
		  null,
		  null,
		  null,
		  null,
		  null,
		  null,
		  { "sSearch": function (){
				var myselect = document.getElementById("selcliente");
				if (myselect.options[myselect.selectedIndex].value!="")
				{
					return myselect.options[myselect.selectedIndex].value;
				}else{
					return '-';
				}
			}
		  },
		],
		"ajax": {
			"url": "./postinv.php",
			"type": "POST",
			"data": function ( d ) {
				d.tipo_doc = $('#seltpo').val();
				d.showconfac=$("#showconfac").is(':checked');
			}
		},
		"deferLoading": 0,
		"columns": [
			{ "data": "referencia" },
			{ "data": "fechaentrada" },
			{ "data": "horaentrada" },
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
			},
			{ "data": "cliente" }
		],
		"columnDefs": [
            {
				"targets": [ 7 ],
                "visible": false,
				className: 'none'
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
		dom: 'T<"clear">frtlip',
        tableTools: {
            "sRowSelect": "multi",
            "aButtons": [
				 {
                    "sExtends": "select_all",
                    "sButtonText": "seleccionar todo"
                },
                {
                    "sExtends": "select_none",
                    "sButtonText": "quitar selección"
                }
			]
        }
	} );
	$('.dataTable').dataTable().fnFilterOnReturn();
	$('select').on('change', function (e) {
		compruebacheck();
		var table = $('#inventario').DataTable();
		table.ajax.reload();
	});
	$('#divchkfac').on('change', function (e) {
		if ($('#seltpo').val()==1){
			var table = $('#inventario').DataTable();
			table.ajax.reload();
		}
	});
	compruebacheck();
} );

function compruebacheck(){
	if($('#seltpo').val()==1){
		$( "#divchkfac" ).removeClass( "hide" );
	}else{
		$( "#divchkfac" ).addClass( "hide" );
	}
}