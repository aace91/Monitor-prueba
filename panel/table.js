$.fn.dataTable.Buttons.swfPath = './../swf/flashExport.swf';
refactual='';
$(document).ready(function() {
	var table= $('#example').dataTable( {
		"order": [[ 4, 'dsc' ]],
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "./post.php",
			"type": "POST",
			"data": function ( d ) {
				d.fechaini = $('#fechaini1').val();
				d.fechafin = $('#fechafin1').val();
				d.cliente = $('#selecliente').val();
				d.ejecutivo = $('#selejecutivo').val();
			}
		},
		"columns": [
			{ "data": "referencia",
				"mRender": function (data, type, row) {
					var link = data;
					if (row.estatus_comentario){
						link += '&nbsp;<a href="javascript:void(0);" onclick="consultacomref('+"'"+row.referencia+"'"+');return false;"><i class="fa fa-comment" aria-hidden="true"></i></a>';
					}
					return link;
				}
			},
			{ "data": "documentacion",
			    "className": "text-center",
				"mRender": function (data, type, row) {
					link='';
					if (!data){
						link='';
					}else{
						link='<a data-toggle="tooltip" data-placement="bottom" title="Entrada" href="'+data+'" target="_blank"><i class="fa fa-sign-in" aria-hidden="true"></i></a>';
					}
					/*if (!row.iddoc){
						link=link+'';
					}else{
						if (row.tipodoc=='1'){
							link=link+' <a href="javascript:void(0);" onclick="javascript:window.open(\'descargadoc.php?iddoc='+row.iddoc+'\',\'_blank\');"><i class="fa fa-sign-out" aria-hidden="true"></i></a>';
						}
					}*/
					if (row.estatus_doc != 'null') {
						link += ((link != '')? '&nbsp;&nbsp;&nbsp;&nbsp;' : '') + ' <a data-toggle="tooltip" data-placement="bottom" title="' + row.estatus_doc + '" href="javascript:void(0);" onclick="fcn_validacion_docs('+"'"+row.referencia+"'"+');"><i class="fa fa-file" aria-hidden="true" style="color:#' + ((row.estatus_doc == 'aprobado')? '3c763d' : '777') + '"></i></a>';
					}
					return link;
				}
			},
			{ "data": "bultos" },
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
			{ 
				"data": "fechaentrada",
				"className": "text-center",
				"mRender": function (data, type, row) {
					if (type === 'export') {
						return data;
					} else {
						if (data == '' || data == null) {
							return '<a href="#" data-toggle="tooltip" data-placement="bottom" title="Entrada virtual"><i class="fa fa-exclamation-triangle" aria-hidden="true" style="color:#f0ad4e;"></i></a> ' + row.fechavirtual;
						} else {
							return data;
						}
					}
				}
			},
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
							var sIcon = '';
							switch (row.remision_doc) {
								case 'pendiente': sIcon = '<i class="fa fa-clock-o" aria-hidden="true"></i>'; break;
								case 'aprobado': sIcon = '<i class="fa fa-check-circle" aria-hidden="true" style="color:#3c763d;"></i>'; break;
								case 'rechazado': sIcon = '<i class="fa fa-times-circle" aria-hidden="true" style="color:#a94442;"></i>'; break;
							}
							return '<a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="' + row.remision_doc + '"  onclick="consultaremesa('+row.remesa+','+'\''+row.pedimento+'\','+row.remision+',\''+row.referencia+'\');return false;">'+row.remesa+' ' + sIcon + '</a>';
						}
					}
				}				
			},
			{ "data": "ejecutivo", 
				"width": "140px"
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
			{ "data": "linea" , 
				"width": "200px" },
			{
				"data": "po"
			},
			{
				"data": "subguias"
			},
			{ "data": "nom_fac_master" , 
				"width": "100px",
				"mRender": function (data,type,row) {
					if (data==''||!data){
						return '';
					}else{
						return ' <a href="descargadoc.php?iddoc='+row.id_fac_master+'" target="_blank">'+data+'</a>';
					}
				}
			},
			{ "data": "id_car_nom" , 
				"width": "100px",
				"mRender": function (data,type,row) {
					if (data==''||!data){
						if(row.fec_sol_nom==''||!row.fec_sol_nom){
							return '<center><div id="solicitanom'+row.referencia+'"><a href="javascript:void(0);" onclick="solicitadoc('+"'"+row.referencia+"'"+',1);return false;">Solicitar</a></div></center>';
						}else{
							return '<center>Solicitada</center>';
						}
					}else{
						return '<center><a href="descargadoc.php?iddoc='+data+'" target="_blank">Ver</a></center>';
					}
				}
			},
			{ "data": "id_cer_ori" , 
				"width": "100px",
				"mRender": function (data,type,row) {
					if (data==''||!data){
						if(row.fec_sol_cer_ori==''||!row.fec_sol_cer_ori){
							return '<center><div id="solicitacerori'+row.referencia+'"><a href="javascript:void(0);" onclick="solicitadoc('+"'"+row.referencia+"'"+',2);return false;">Solicitar</a></div></center>';
						}else{
							return '<center>Solicitado</center>';
						}
					}else{
						return '<center><a href="descargadoc.php?iddoc='+data+'" target="_blank">Ver</a></center>';
					}
				}
			},
			{ "data": "id_estatus_factura" , 
				"width": "100px",
				"mRender": function (data,type,row) {
					if (data=='0' || !data){
						return '<center><div id="estatusfac'+row.referencia+'">ESTATUS PENDIENTE <a href="javascript:void(0);" onclick="estatusfac('+"'"+row.referencia+"'"+');return false;"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a></div></center>';
					}else{
						return '<center><div id="estatusfac'+row.referencia+'">'+row.estatus_factura+' <a href="javascript:void(0);" onclick="estatusfac('+"'"+row.referencia+"'"+');return false;"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a></div></center>';
					}
				}
			},
			{ "align": 'Center',
				"data": "referencia",
				"mRender": function (data,type,row) {
					if (data==''||!data){
						return '';
					}else{
						refactual=data;
						var sAlert = ((row.estatus_comentario) ? '<span class="glyphicon glyphicon-comment" aria-hidden="true" style="color: #ec971f;"></span></i>' : '')
						return '<center><a href="javascript:void(0);" onclick="consultacomref('+"'"+data+"'"+');return false;">Ver ' + sAlert + '</a></center>';
					}
				}
			}
		],
		"aoColumnDefs": [
			{
				"aTargets":[0], // You actual column with the string 'America'
				"fnCreatedCell": function(nTd, sData, oData, iRow, iCol)
				{
					if(oData.estatus=='2')
					{
						$(nTd).css('background-color', '#FF9999'); // You can use hex code as well
					}
				},                   
			},
			{
				"aTargets":[7], // You actual column with the string 'America'
				"fnCreatedCell": function(nTd, sData, oData, iRow, iCol)
				{
					if(oData.tienefactura=='NO'&&(!oData.iddoc||oData.tipodoc!='1')&&!oData.revision&&!oData.remision&&!oData.asignada)
					{
						var fec=moment(oData.fechaentrada+" "+oData.horaentrada,"DD-MM-YYYY hh:mm:ss a");
						var ahora=moment();
						var horas=ahora.diff(fec,'hours',true);
						if (horas<=4){
							$(nTd).css('background-color', '#FFFF99'); // You can use hex code as well
						}else{
							if (horas<=12){
								$(nTd).css('background-color', '#FF9999'); // You can use hex code as well
							}else{
								$(nTd).css('background-color', '#FF99FF'); // You can use hex code as well
							}
						}
					}
				},                   
			},
			{
				"aTargets":[9], // You actual column with the string 'America'
				"fnCreatedCell": function(nTd, sData, oData, iRow, iCol)
				{
					if((oData.tienefactura=='SI'||(oData.iddoc&&oData.tipodoc=='1')||oData.asignada)){
						if(!oData.revision&&!oData.remision)
						{
							if (!oData.asignada){
								$(nTd).css('background-color', '#FFFF99'); // You can use hex code as well
							}else{
								var fec=moment(oData.fechaasig+" "+oData.horaasig,"DD-MM-YYYY hh:mm:ss a");
								var ahora=moment();
								var horas=ahora.diff(fec,'hours',true);
								if (horas<=4){
									$(nTd).css('background-color', '#FFFF99'); // You can use hex code as well
								}else{
									if (horas<=12){
										$(nTd).css('background-color', '#FF9999'); // You can use hex code as well
									}else{
										$(nTd).css('background-color', '#FF99FF'); // You can use hex code as well
									}
								}
							}
						}
					}
				},                   
			},
			{
				"aTargets":[13], // You actual column with the string 'America'
				"fnCreatedCell": function(nTd, sData, oData, iRow, iCol)
				{
					//if((oData.tienefactura=='SI'||(oData.iddoc&&oData.tipodoc=='1'))){
						if(oData.revision){
							if(!oData.remision)
							{
								$(nTd).css('background-color', '#FFFF99'); // You can use hex code as well
							}
						}
					//}
				},                   
			},
			{
				"aTargets":[16], // You actual column with the string 'America'
				"fnCreatedCell": function(nTd, sData, oData, iRow, iCol)
				{
					//if((oData.tienefactura=='SI'&&(oData.iddoc&&oData.tipodoc=='1'))){
						if(oData.revision){
							if(oData.remision)
							{
								if(oData.pedimento=='-')
								{
									$(nTd).css('background-color', '#FFFF99'); // You can use hex code as well
								}
							}
						}
					//}
				},                   
			}
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
		//"scrollY":  '300px',
		aLengthMenu: [
			[25, 50, 100, 200, -1],
			[25, 50, 100, 200, "All"]
		],
		iDisplayLength: 100,
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
	
	$('#example').DataTable().on('draw', function () {
		$('[data-toggle="tooltip"]').tooltip(); 
	});
} );

function estatusfac(referencia){
	$('#modificaefac').modal({
		show: true
	});
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{action:"consultaefac", referencia: referencia},
		beforeSend: function () {
			$("#mensajeefac").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="36" width="36">Cargando espere...</div>');
			$('#selefac').prop( "disabled", true );
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			if (respuesta.codigo=='1'){
				$("#mensajeefac").html('');
				$('#selefac').val(respuesta.id_estatus_factura);
				$('#efac_ref').val(referencia);
				$('#selefac').prop( "disabled", false );
				$('#btnguardarefac').prop( "disabled", false );
			}else{
				$("#mensajeefac").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			$("#mensajeefac").html('<div class="alert alert-danger alert-dismissible" role="alert">'+xhr.responseText+'</div>');
		}
	});
	$("#mensajeefac").html('');
}

function guardaefac(){
	id_estatus_factura=$('#selefac').val();
	referencia=$('#efac_ref').val();
	$('#btnguardarefac').prop( "disabled", true );
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{action:"guardaefac", referencia: referencia, id_estatus_factura: id_estatus_factura},
		beforeSend: function () {
			$("#mensajeefac").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="36" width="36">Guardando espere...</div>');
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			if (respuesta.codigo=='1'){
				$("#mensajeefac").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
				/*var table = $('#example').DataTable();
				table.ajax.reload(null,false);*/
				$("#estatusfac"+referencia).html('<center><div id="estatusfac'+referencia+'">'+$('#selefac option:selected').text()+' <a href="javascript:void(0);" onclick="estatusfac('+"'"+referencia+"'"+');return false;"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a></div></center>');
			}else{
				$("#mensajeefac").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
				$('#btnguardarefac').prop( "disabled", false );
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			$("#mensajeefac").html('<div class="alert alert-danger alert-dismissible" role="alert">'+xhr.responseText+'</div>');
			$('#btnguardarefac').prop( "disabled", false );
		}
	});
}

function consultacomref(referencia){
	if(referencia==''){
		referencia=refactual;
	}
	$('html, body').animate({
					scrollTop: $("#Detalle").offset().top
				}, 1000);
	if(referencia==''){
		$("#Detalle").html("<br><center><p>Debe seleccionar una referencia</p></center>");
		return;
	}
	obj1 =  $("#tabpedimento");
	obj2 =  $("#tabremesa");
	obj3 =  $("#tabcomentarios");
	obj1.removeAttr('class','active');
	obj2.removeAttr('class','active');
	obj3.attr("class","active");
	$('html, body').animate({
					scrollTop: $("#Detalle").offset().top
				}, 1000);
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{action: 'consulta_comentarios', referencia: referencia},
		beforeSend: function () {
				$("#Detalle").html('<br><center><p><img src="../images/cargando.gif" height="36" width="36">Consultando, espere por favor...</p></center>');
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			if (respuesta.codigo=='1'){
				$("#Detalle").html(respuesta.html);
			}else{
				$("#Detalle").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
			}
		}
	});
}

function grabacomref(){
	referencia = document.getElementById("referencia").value;
	comentarios = document.getElementById("comentarios").value;
	estatus = document.getElementById("estatus").value;
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{action:'grabacomref', referencia: referencia, comentarios: comentarios, estatus:estatus},
		beforeSend: function () {
			$("#Detalle").html('<br><center><div class="alert alert-info" role="alert"><img src="../images/cargando.gif" height="36" width="36">Guardando, espere por favor...</div></center>');
		},
		success:  function (response) {
			respuesta=JSON.parse(response);
			if (respuesta.codigo==1){
				$("#Detalle").html('<br><center><div class="alert alert-success">'+respuesta.mensaje+'</div></center>');
				var table = $('#example').DataTable();
				table.ajax.reload(null,false);
			}else{
				$("#Detalle").html('<br><center><div class="alert alert-danger">'+respuesta.mensaje+'</div></center>');
			}
		}
	});
}

/**********************************************************************************/
/* VALIDACION DE DOCUMENTOS */
/**********************************************************************************/

var __sIdDoc; //variable global

function fcn_validacion_docs(referencia) {
	fcn_docs_ops('browser');
	
	if(referencia != ''){
		refactual = referencia;
	}
	
	$('#modal_documentos').modal({ show: true, backdrop: 'static', keyboard: false });

	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{action: 'consulta_documentos', referencia: refactual},
		beforeSend: function () {
			$("#itable_mdl_docs tbody").empty();
			$("#idiv_mdl_docs_mensaje").html('<br><center><p><img src="../images/cargando.gif" height="36" width="36">Consultando, espere por favor...</p></center>');
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			
			$("#idiv_mdl_docs_mensaje").empty();
			if (respuesta.codigo=='1'){
				$("#itable_mdl_docs tbody").html(respuesta.sDocumentos);
			}else{
				$("#idiv_mdl_docs_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
			}
		}
	});
}

function fcn_docs_ver(id_doc, tipo, status) {
	fcn_docs_ops('aprobar');
	__sIdDoc = id_doc;
	
	$('#ispan_mdl_docs_aprobar_documento').html(tipo);
	
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{action: 'consulta_documento_pdf', id_doc: __sIdDoc },
		beforeSend: function () {
			$('#pdfViewer_pdf_archivo').hide();
			$('.cls_aprobar_btn').hide();
			$("#idiv_mdl_docs_mensaje").html('<br><center><p><img src="../images/cargando.gif" height="36" width="36">Consultando, espere por favor...</p></center>');
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			
			$("#idiv_mdl_docs_mensaje").empty();
			if (respuesta.codigo=='1'){
				if (status != 'Aprobado' && status != 'Rechazado') {
					$('.cls_aprobar_btn').show();
				}

				var pdfjsframe_remped = document.getElementById('pdfViewer_pdf_archivo');
				
				var pdfData = base64ToUint8Array(respuesta.pdfbase64);
				pdfjsframe_remped.contentWindow.PDFViewerApplication.open(pdfData);
				$('#pdfViewer_pdf_archivo').show();

				/*var sHtml = '<iframe src="../bower_components/pdfjs/web/viewer.html?file=https://www.delbravoweb.com/archivos/monitor/' + respuesta.link + '" width="100%" height="700" frameborder="0" wmode="Opaque"  allowtransparency="yes" scrolling="no" style="position:relative;" ></iframe>';
				$('#iembed_pdf_archivo').html(sHtml);*/
			}else{
				$("#iembed_pdf_archivo").html('<div class="alert alert-danger">'+respuesta.mensaje+'</div>');
			}
		},
		error: function(data){
			$("#iembed_pdf_archivo").html('<div class="alert alert-danger">Error contacte al administrador</div>');
		}
	});
}

function fcn_docs_ops(sOpt) { 
	switch(sOpt) {
		case 'browser':
			$('#idiv_mdl_docs_table').show();
			$('#idiv_mdl_docs_aprobar').hide();
			$('#idiv_mdl_docs_rechazar').hide();
			break;
			
		case 'aprobar':
			$('#idiv_mdl_docs_table').hide();
			$('#idiv_mdl_docs_aprobar').show();
			$('#idiv_mdl_docs_rechazar').hide();
			break;
			
		case 'rechazar':
			$('#idiv_mdl_docs_table').hide();
			$('#idiv_mdl_docs_aprobar').hide();
			$('#idiv_mdl_docs_rechazar').show();
			
			$('#itxt_mdl_docs_observaciones').val('');
			$('#idiv_mdl_docs_rechazar_btns').show();
			break;
	}
}

function fcn_validacion_aprobar() {
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{
			action: 'guardar_documento_info', 
			referencia: refactual, 
			id_doc: __sIdDoc, 
			task: 'aprobar',
			id_estatus_documento: 0,
			txt_estatus_documento: '',
			observaciones: '',
			doc_tipo: ''
		},
		beforeSend: function () {
			$('.cls_aprobar_btn').hide();
			$("#idiv_mdl_docs_mensaje").html('<br><center><p><img src="../images/cargando.gif" height="36" width="36">Aprobando, espere por favor...</p></center>');
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			$("#idiv_mdl_docs_mensaje").empty();
			if (respuesta.codigo=='1'){
				fcn_docs_ops('browser');
				$("#itable_mdl_docs tbody").html(respuesta.sDocumentos);
				$("#idiv_mdl_docs_mensaje").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
			}else{
				$('.cls_aprobar_btn').show();
				$("#idiv_mdl_docs_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
			}
		}
	});
}

function fcn_validacion_rechazar() {
	var sIdEstatusDoc = $('#isel_mdl_docs_razon').val();
	var sTxtEstatusDoc = $('#isel_mdl_docs_razon option:selected').data('descripcion_us');
	var sObservaciones = $('#itxt_mdl_docs_observaciones').val().toUpperCase();
	if (!sObservaciones.trim()) { 
		$("#idiv_mdl_docs_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Debe agregar una observaci&oacute;n!!!</div>');
		return;
	}
		
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data: {
			action: 'guardar_documento_info', 
			referencia: refactual, 
			id_doc: __sIdDoc,
			task: 'rechazar',
			id_estatus_documento: sIdEstatusDoc,
			txt_estatus_documento: sTxtEstatusDoc,
			obs: sObservaciones,
			doc_tipo: $('#ispan_mdl_docs_aprobar_documento').html()
		},
		beforeSend: function () {
			$('#idiv_mdl_docs_rechazar_btns').hide();
			$("#idiv_mdl_docs_mensaje").html('<br><center><p><img src="../images/cargando.gif" height="36" width="36">Rechazando, espere por favor...</p></center>');
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			$("#idiv_mdl_docs_mensaje").empty();
			if (respuesta.codigo=='1'){
				fcn_docs_ops('browser');
				$("#itable_mdl_docs tbody").html(respuesta.sDocumentos);
				$("#idiv_mdl_docs_mensaje").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
			}else{
				$('#idiv_mdl_docs_rechazar_btns').show();
				$("#idiv_mdl_docs_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
			}
		}
	});
}