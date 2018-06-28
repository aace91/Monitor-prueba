$.fn.dataTable.Buttons.swfPath = './../swf/flashExport.swf';
$(document).ready(function() {
	var table= $('#example').dataTable( {
		"order": [[ 4, 'dsc' ]],
		"processing": true,
		"serverSide": true,
		"aoSearchCols": [
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  {},
		  { "sSearch": function (){
				var myselect = document.getElementById("selejecutivo");
				if (myselect.options[myselect.selectedIndex].value!="")
				{
					return myselect.options[myselect.selectedIndex].value;
				}else{
					return '';
				}
			}
		  }
		],
		"ajax": {
			"url": "./posthistorial.php",
			"type": "POST",
			"data": function ( d ) {
				d.fechaini = $('#fechaini1').val();
				d.fechafin = $('#fechafin1').val();
				d.fechasalini = $('#fechasalini1').val();
				d.fechasalfin = $('#fechasalfin1').val();
				d.cliente = $('#selecliente').val();
			}
		},
		"columns": [
			{ "data": "referencia" },
			{ "data": "documentacion",
				"mRender": function (data, type, row) {
					link='';
					if (!data){
						link='';
					}else{
						link='<a href="'+data+'" target="_blank">Entrada</a>';
					}
					if (!row.iddoc){
						link=link+'';
					}else{
						if (row.tipodoc=='1'){
							link=link+' <a href="javascript:void(0);" onclick="javascript:window.open(\'descargadoc.php?iddoc='+row.iddoc+'\',\'_blank\');">Factura</a>';
						}
					}
					return link;
				}
			},
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
					if (!row.fotosadicionales){
						link=link+'';
					}else{
						link=link+row.fotosadicionales;
					}
					return link;
				}				
			},
			{ "data": "cliente" ,
				"width": "300px" 
			},
			{ "data": "fechaentrada"},
			{ "data": "horaentrada" },
			{ "data": "tienefactura" },
			{ "data": "fechafactura"},
			{ "data": "revision" ,
				"mRender": function (data, type, row) {
					info='';
					if (!data||data==''){
						if (!row.asignada||row.asignada==''){
							info='';
						}else{
							info='Asignada: '+row.asignada+', '+row.fechaasig+', '+row.horaasig;
						}
					}else{
						link='';
						if (!data){
							link='';
						}else{
							link=link+'<center><a href="javascript:void(0);" onclick="javascript:window.open(\'showrevision.php?id='+data+'&referencia='+row.referencia+'&factura='+row.facrev+'\',\'_blank\');">'+data+'</a></center>';
						}
						info=link;
					}
					return info;
				},
				"width": "180px"
			},
			{ "data": "fecharevision" },
			{ "data": "horarevision" },
			{ "data": "facrev" },
			{ "data": "remision",
				"mRender": function (data, type, row) {
					link='';
					if (!data){
						link='';
					}else{
						link=link+'<center><a href="javascript:void(0);" onclick="javascript:window.open(\'showremision.php?id='+data+'\',\'_blank\');">'+data+'</a></center>';
					}
					return link;
				}
			},
			{ "data": "fecharemision" },
			{ "data": "horaremision" },
			{ "data": "tipo_pedimento" },
			{ "data": "pedimento",
				"mRender": function (data, type, full) {
					if (data==null){
						return '-';
					}
					else{
						return '<a href="javascript:void(0);" onclick="consultapedimento('+"'"+data+"'"+');return false;">'+data+'</a>';
					}
				}
			},
			{ "align": 'Center',
				"data": "remesa",
				"mRender": function (data, type, row) {
					if (data==null){
						return '-';
					}else{
						if (row.tipo_pedimento=='DEFINITIVO'){
							return 'Ped. Norm.';
						}else{
							return '<a href="javascript:void(0);" onclick="consultaremesa('+row.remesa+','+"'"+row.pedimento+"',"+row.remision+');return false;">'+row.remesa+'</a>';
						}
					}
				}				
			},
			{ "data": "ejecutivo", 
				"width": "140px"
			},
			{ "data": "fechasalida" },
			{ "data": "horasalida",
				"width": "80px"
			},
			{ "data": "diasenbodega", 
				"width": "180px" 
			},
			{ "data": "caja", 
				"width": "100px"
			},
			{ "data": "guia", 
				"width": "140px",
				"mRender": function (data,type,row) {
					if (data==''||!data){
						return '';
					}else{
						return ' <a href="https://track.aftership.com/'+data+'" target="_blank">'+data+'</a>';
					}
				}
			},
			{ "data": "linea" }
		],
		"buttons": [
			{
				extend: 'colvis',
				text: 'Columnas'
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
		"scrollY":  '300px',
		"fixedColumns": {
			"leftColumns": 2
		},
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
		}
	} );
	/*var tt = new $.fn.dataTable.TableTools( table );
    $( tt.fnContainer() ).insertBefore('div.dataTables_wrapper');*/
	/*$('.dataTable').dataTable().fnFilterOnReturn();*/
	$('#example_filter input').unbind();
	$('#example_filter input').bind('keyup', function(e) {
		if(e.keyCode == 13) {
			table.fnFilter(this.value);   
		}
	}); 
	$('#fechaini, #fechafin').keyup( function(e) {
		if(e.keyCode == 13){
			var table = $('#example').DataTable();
			table.draw();
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
	var fechafin = $('#fechasalini').datepicker({
		todayHighlight:true,
		autoclose: true,
		clearBtn: true
	}).data('datepicker');
	var fechafin = $('#fechasalfin').datepicker({
		todayHighlight:true,
		autoclose: true,
		clearBtn: true
	}).data('datepicker');
} );