 /* * - * - * - * - * - * - * - - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - *
*	CRUCES EXPORTACION
* - * - * - * - * - * - * - - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * */
var aFacturas = new Array();
var aFacImp = new Array();
var aPermisos = new Array();
var aParPlant5 = new Array();
var aFacReceptores = new Array();
var aPermisosAdhesion = new Array(); var aPermisosAutomaticos = new Array(); var aCertificados = new Array();
var nFac = 0; var nPerm = 0;
var gAccion = ''; var gCatalogo = ''; var sIdCruce = ''; var sIdFactura = ''; var sIdCliente = ''; var sAduana = '';
var ActFactura = ''; var sPedimento = ''; var sIdSitePedimento  = ''; var nItem = 0;
var selected = []; var sel_facturas = []; var sel_parplantilla = []; var sReferencia = ''; var sRemesa = ''; var isIE = true; var bDocCFDI = false;
var selected_plantilla5 = [];
var bDocFact = false; var bDocPacList = false; var bDocCerOri = false; var bDocTickBasc = false;
var sTipoArchElim = ''; var sFileNameEli = ''; var sActFactura = ''; var bAviso_Adhesion = false;var bCertSel = true;
var gebRefExpo = '';var aCliCons = new Array();
//Asignar Facturas Cruces
var aFacCASA = new Array(); 
var IdDetCruce = ''; var nItemArrFac = ''; var sNumFact = '';
//Plantilla Anzada Cruce
var table_plantilla = null; var table = null; var table_asigfac = null; 

//Cruces Plantilla Anzada
var aCrucesPlantilla = Array();
var table_cruces_plantilla5 = null;

$(document).ready(function() {
	inicializar_controles();
	inicializar_tabla_cruces();
	inicializa_tabla_soia_detalle();
	inicializar_select_2();
	$(window).resize(function() { onWinResize(); });
} );

function onWinResize(){
	//setTimeout(function () {$('#tbl_cruces').DataTable().columns.adjust().responsive.recalc();},500);
	//setTimeout(function () {$('#rpt_facturas').DataTable().columns.adjust().responsive.recalc();},500);
}

function inicializar_controles(){
	isIE = detectIE();//Detectar si es Internet Explorer
	$("#upload_pedimento, #upload_factura,#upload_packing,#upload_certificado,#upload_ticket_bascula").fileinput({
		previewFileType: "any",
		browseClass: "btn btn-primary",
		browseLabel: " Examinar...",
		browseIcon: "<span class=\"glyphicon glyphicon-folder-open\"></span>",
		removeClass: "btn btn-danger",
		removeLabel: "Eliminar",
		removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i>",
		allowedFileExtensions: ["pdf"],
		showPreview: (isIE ? false : true)
	});
	$("#upload_anexo_factura").fileinput({
		showPreview: (isIE ? false : true),
		previewFileType: "any",
		browseClass: "btn btn-primary",
		browseLabel: " Examinar...",
		browseIcon: "<span class=\"glyphicon glyphicon-folder-open\"></span>",
		removeClass: "btn btn-danger",
		removeLabel: "Eliminar",
		removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i>",
		allowedFileExtensions: ["pdf","xls","xlsx"]
	});
	$("#upload_cfdi").fileinput({
			previewFileType: "any",
			browseClass: "btn btn-primary",
			browseLabel: " Examinar...",
			browseIcon: "<span class=\"glyphicon glyphicon-folder-open\"></span>",
			removeClass: "btn btn-danger",
			removeLabel: "Eliminar",
			removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i>",
			allowedFileExtensions: ["xml"],
			showPreview: false
		});
	$('#upload_cfdi').on('fileloaded', function(event, numFiles, label) {
			bDocCFDI = true;
			ajax_procesar_archivo_cfdi();
		});
		
	$('.modal').on('hidden.bs.modal', function (e) { regresar_focus_modal_open();});
		
	$('.modal').on('shown.bs.modal', function (e) { 
		regresar_focus_modal_open();
		inicializar_select_2();
	});
	
	$('#chk_consolidar_cruce').click(function (){
		if ($(this)[0].checked){
			$("#sel_consolidar").prop('disabled', false);
			$("#sel_consolidar").empty().trigger('change');
		}else{
			$("#sel_consolidar").empty().trigger('change');
			$("#sel_consolidar").prop('disabled', true);
			aCliCons= new Array();
			inicializar_tabla_clientes_consolidar();
		}
	});
	$('#chk_rectificacion_genref').click(function (){
		if ($(this)[0].checked){
			$("#div_referencia_rectificacion_genrefexpo").show();
		}else{
			$("#div_referencia_rectificacion_genrefexpo").hide();
		}
	});
	
	$(".integer").numeric(false, function() { alert("Solamente se permiten numeros enteros."); this.value = ""; this.focus(); });
	$(".decimal-3-places").numeric({ decimalPlaces: 3 });
	
	$('#txt_peso_factura').focusout(function(){
		if($('#txt_peso_factura').val() != ''){
			var peso_kgs = eval($('#txt_peso_factura').val());
			var peso_lbs = (peso_kgs / 0.4536);
			$('#txt_peso_factura_lbs').val(peso_lbs.toFixed(3));
		}else{
			$('#txt_peso_factura').val('0');
			$('#txt_peso_factura_lbs').val('0');
		}
	});
	
	$('#txt_peso_factura_lbs').focusout(function(){
		if($('#txt_peso_factura_lbs').val() != ''){
			var peso_lbs = eval($('#txt_peso_factura_lbs').val());
			var peso_kgs = (peso_lbs * 0.4536);
			$('#txt_peso_factura').val(peso_kgs.toFixed(3));
		}else{
			$('#txt_peso_factura').val('0');
			$('#txt_peso_factura_lbs').val('0');
		}
	});

	//Mostrar años pedimentos
	var anio = (new Date).getFullYear();
	for(i = (anio - 1); i < (anio + 5); i++){
		$('#sel_pedimento_mod_anio').append($("<option></option>").attr("value",i).text(i));
	}
	$('#sel_pedimento_mod_anio').val(anio);

	
	$('#chk_usar_uuid_plantilla').click(function (){
		//if ($(this)[0].checked){
		inicializar_table_parfac_plantilla_5()
	});
	
	$('#txt_fecha_factura_mdl').datepicker({
		todayHighlight:true,
		autoclose: true,
		clearBtn: true,
		format:'dd/mm/yyyy 00:00:00',
		setDate: new Date()
	}).data('datepicker');
	/*$('.date-time').datepicker({format:'dd/mm/yyyy 00:00:00'});
	$(".date-time").datepicker('setDate', new Date());*/
}

function inicializar_select_2(){
	
	$('#selecliente').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "[SELECCIONAR]"
	}).on("select2:select", function (e) { 
		$('#selestado').focus(); 
	}).on("select2:close", function (e) { 
		$('#selestado').focus(); 
	});
	
	$('#selestado').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "[SELECCIONAR]"
	}).on("select2:select", function (e) { 
		$('#sel_tipo_salida').focus(); 
	}).on("select2:close", function (e) { 
		$('#sel_tipo_salida').focus(); 
	});
	
	$('#sel_linea_transportista').select2(get_options_select('busca_linea_fletera')).on("select2:select", function (e) { 
		$('#sel_aduana').focus(); 
	}).on("select2:close", function (e) { 
		$('#sel_aduana').focus(); 
	});
	
	$('#sel_aduana').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "[SELECCIONAR]"
	}).on("select2:select", function (e) { 
		//$('#sel_tipo_salida').focus(); 
	}).on("select2:close", function (e) { 
		//$('#sel_tipo_salida').focus(); 
	});
	
	$('#sel_consolidar').select2(get_options_select('busca_clientes')).on("select2:select", function (e) { 
		//$('#sel_aduana').focus(); 
	}).on("select2:close", function (e) { 
		//$('#sel_aduana').focus(); 
	});
	
	$('#sel_tipo_salida').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "[SELECCIONAR]"
	}).on("select2:select", function (e) { 
		//$('#txt_numero_caja').focus(); 
	}).on("select2:close", function (e) { 
		//$('#txt_numero_caja').focus(); 
	});
	
	$('#sel_transfer').select2(get_options_select('busca_transfers')).on("select2:select", function (e) { 
		
		$('#txt_caat_transfer').val(e.params.data.caat);
		$('#txt_scac_transfer').val(e.params.data.scac);
		
		$('#txt_caat_transfer').focus(); 
	}).on("select2:close", function (e) { 
		$('#txt_caat_transfer').focus(); 
	});
	
	$('#sel_lugares_entrega').select2(get_options_select('busca_entregas_expo')).on("select2:select", function(e) { 
		//$('#txt_direccion_entrega').val($(e.currentTarget).find("option:selected").data().data.dir);
		$('#txt_direccion_entrega').val(e.params.data.dir);
		$('#txt_indicaciones').focus(); 
	}).on("select2:close", function (e) { 
		$('#txt_indicaciones').focus(); 
	});
	
	$('#sel_agente_americano').select2(get_options_select_facturas('busca_aaa')).on("select2:select", function (e) { 
		//$('#sel_lugares_entrega').focus(); 
	}).on("select2:close", function (e) { 
		//$('#sel_lugares_entrega').focus(); 
	});
	
	$('#sel_regimen').select2(get_options_select_facturas('busca_regimen')).on("select2:select", function (e) { 
		if(e.params.data.id == 'A1'){
			$("#txt_numero_factura_mdl").prop('disabled',true);
			$("#txt_fecha_factura_mdl").prop('disabled',true);
			$("#txt_numero_uuid_mdl").prop('disabled',true);
		}else{
			$("#txt_numero_factura_mdl").prop('disabled',false);
			$("#txt_fecha_factura_mdl").prop('disabled',false);
			$("#txt_numero_uuid_mdl").prop('disabled',false);
		}
	}).on("select2:close", function (e) { 
		//$('#sel_lugares_entrega').focus(); 
	});
	
	$('#sel_pedimento_mod_regimen').select2(get_options_select_gen_pedimento('busca_regimen')).on("select2:select", function (e) { 
		//$('#sel_lugares_entrega').focus(); 
	}).on("select2:close", function (e) { 
		//$('#sel_lugares_entrega').focus(); 
	});
	
	$('#sel_pedimento_mod_cliente_casa').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "[SELECCIONAR]",
		dropdownParent: $('#modalgenpedimento')
	}).on("select2:select", function (e) { 
		$('#selestado').focus(); 
	}).on("select2:close", function (e) { 
		$('#selestado').focus(); 
	});

	$('#sel_proveedor_plantilla').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "[SELECCIONAR]",
		dropdownParent: $('#modal_plantilla')
	}).on("select2:select", function (e) { 
		//$('#selestado').focus(); 
	}).on("select2:close", function (e) { 
		//$('#selestado').focus(); 
	});
}

function regresar_focus_modal_open(){
	var oModalsOpen = $('.in');
	if (oModalsOpen.length > 0 ) {$('body').addClass('modal-open');}
}

function inicializar_tabla_cruces(){
	table = $('#tbl_cruces').DataTable( {
		"order": [[ 0, 'desc' ]],
		"searchDelay": 3000,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "ajax/cruces_exportacion/postCrucesExpo.php",
			"type": "POST",
			"data": function ( d ) { 
				d.cliente = $('#selecliente').val();
				d.estado = $('#selestado').val();
			},
			"error": function (a,b){
				alert(a.statusText);
			}
		},
		"columns": [
			{ "data": "DT_RowId" },
			{ "data": "fecha_registro" },
			{ "data": "cliente" },
			{ "data": "aduana" },
			{ "data": "facturas" },
			{ "data": "estado" ,
				"mRender": function (data, type, row) {
					var sBtns = '';
					if(data != '' && data != null){
						var aEstado = data.split('|');						
						if (aEstado[1] != '' && aEstado[1] != null) {
							var aEvento = aEstado[1].split("-");
							if (aEvento.length >= 3) {
								if (aEvento[0] == 310 || aEvento[0] == 510){
									sBtns = '<a href="javascript:void(0);" onclick="consultar_detalle_estado_soia(\''+aEstado[0]+'\');return false;" style="padding-left:.5em;" title=""><span class="label label-danger">' + /*aEvento[1] + ' ' + */aEvento[2] + '</span></a>';
								} else if (aEvento[0] == 320 || aEvento[0] == 520) {
									sBtns = '<a href="javascript:void(0);" onclick="consultar_detalle_estado_soia(\''+aEstado[0]+'\');return false;" style="padding-left:.5em;" title=""><span class="label label-success">' + /*aEvento[1] + ' ' + */aEvento[2] + '</span></a>';
								}
							}
							
						}
					}
					return sBtns;
				}
			},
			{ "data": "DT_RowId",
				"mRender": function (data, type, row) {
						var sBtnAction = '';
						if(row.salidas == ''){
							if(row.referencias != ''){
								sBtnAction += '<span class="label label-warning" ><i class="glyphicon glyphicon-edit"></i></span>';
							}
						}else{
							sBtnAction += '<span class="label label-success" ><i class="fa fa-check" aria-hidden="true"></i></span>';
						}
						return sBtnAction;
					}
			},
			{ "data": "po_number" },
			{ "data": "linea_tans" },
			{ "data": "tiposalida" },
			{ "data": "caja" },
			{ "data": "transfer" },
			{ "data": "entrega" },
			{ "data": "pedimento"/*,
				"mRender": function (data, type, row) {
					var sBtns = '';
					if(data!=''){
						var aPedimentos = data.split(',');
						for(i=0; i<aPedimentos.length; i++ ){
							if(i != 0){sBtns += ' ';}
							sBtns += aPedimentos[i].split('|')[0];
						}
					}
					return sBtns;
				}*/},
			{ "data": "salidas" ,
				"mRender": function (data, type, row) {
					var sBtns = '';
					if(data!=''){
						var aSalidas = data.split(',');
						for(i=0; i<aSalidas.length; i++ ){
							sBtns += '<a href="https://www.delbravoweb.tk/monitor/panel/showCarta_Instrucciones.php?solicitud='+aSalidas[i]+'&type=file" style="padding-left:.5em;" title="" target="_blank">'+aSalidas[i]+'</a>';
						}
					}
					return sBtns;
				}
			},
			{ "data": "DT_RowId",
				"mRender": function (data, type, row) {
					var sBtnAction = '';
					if(row.salidas == ''){
						var PedCapCasa = 'No';
						if(row.pedimento != ''){
							var aPedimentos = row.pedimento.split(',');
							for(i=0; i < aPedimentos.length; i++ ){
								if(aPedimentos[i].split('|')[1] == 'Si'){//Si una factura se se encuentra capturada el cruce se toma como capturada.
									PedCapCasa = 'Si';
									break;
								}
							}
						}
						if(PedCapCasa == 'No'){
							sBtnAction += '<a href="javascript:void(0);" onclick="ajax_consulta_cuce_expo(\''+data+'\',\'Editar\');return false;" style="padding-left:.5em;" title="">[ <span class="glyphicon glyphicon-pencil"></span> Editar ]</a>';
							sBtnAction += '<a href="javascript:void(0);" onclick="eliminar_cruce_expo(\''+data+'\');return false;" style="padding-left:.5em;" title="">[ <span class="glyphicon glyphicon-trash"></span> Eliminar ]</a>';
						}else{
							//sBtnAction += '<span class="label label-warning" ><i class="glyphicon glyphicon-edit"></i> EN PROCESO DE CAPTURA</span>&nbsp;&nbsp;&nbsp;';
							sBtnAction += '<a href="javascript:void(0);" onclick="ajax_consulta_cuce_expo(\''+data+'\',\'ver\');return false;" style="padding-left:.5em;" title="">[ <i class="fa fa-eye" aria-hidden="true"></i> Ver Informaci&oacute;n ]</a>';
							sBtnAction += '<a href="javascript:void(0);" onclick="ajax_consulta_cuce_expo(\''+data+'\',\'editar_encabezado\');return false;" style="padding-left:.5em;" title="">[ <span class="glyphicon glyphicon-pencil"></span> Editar Encabezado ]</a>';
							if(row.habilitado_editar == '0'){
								sBtnAction += '<a href="javascript:void(0);" onclick="ajax_habilitar_editar_cuce_expo(\''+data+'\');return false;" style="padding-left:.5em;" title="Editar"> [<span class="glyphicon glyphicon-ok"></span> Habilitar Para Editar En Cliente ]</a>';
							}else{
								sBtnAction += '<a href="javascript:void(0);" onclick="ajax_consulta_cuce_expo(\''+data+'\',\'Editar\');return false;" style="padding-left:.5em;" title="">[ <span class="glyphicon glyphicon-pencil"></span> Editar ]</a>';
							}
						}
						//*  * * * * * * * * * * * * * * * * * * * * * * * * * ASIGNAR REFERENCIAS * * * * * * * * * * * * * * * * * * * * * * * * *
						sBtnAction += '<a href="javascript:void(0);" onclick="ajax_asignar_referencias_factura(\''+data+'\');return false;" style="padding-left:.5em;" title="">[ <i class="fa fa-check-square-o" aria-hidden="true"></i> Asignar Referencias ]</a>';
						/*<div class="col-xs-6">
								<div class="form-group text-left">
									<button id="btn_asignar_referencias" type="button" class="btn btn-primary" onclick="asignar_referencias_factura();"><i class="fa fa-list" aria-hidden="true"></i> Asignar Referencias</button>
								</div>
							</div>*/
							sBtnAction += '<a href="javascript:void(0);" onclick="ajax_consultar_plantilla_avanzada_5(\''+data+'\');return false;" style="padding-left:.5em;" title="">[ <i class="fa fa-table" aria-hidden="true"></i> Plantilla Avanzada 5 ]</a>';
					}else{
						//sBtnAction += '<span class="label label-success" ><i class="fa fa-check" aria-hidden="true"></i> PROCESADO</span>&nbsp;&nbsp;&nbsp;';
						sBtnAction += '<a href="javascript:void(0);" onclick="ajax_consulta_cuce_expo(\''+data+'\',\'ver\');return false;" style="padding-left:.5em;" title=""> [<i class="fa fa-eye" aria-hidden="true"></i> Ver Informaci&oacute;n ]</a>';
					}
					sBtnAction += '<a href="javascript:void(0);" onclick="ajax_consultar_documentacion_cruce(\''+data+'\');return false;" style="padding-left:.5em;" title=""> [<i class="fa fa-file-pdf-o" aria-hidden="true"></i> Unificar PDF\'s ]</a>';
					
					return sBtnAction;
				}
			}
		],
		"buttons": [
			{
				extend: 'colvis',
				text: '<i class="fa fa-columns" aria-hidden="true"></i> Visualizar columnas',
				className: 'btn-primary'
			},
			/*{
				extend: 'copyHtml5',
				exportOptions: {
                    columns: ':visible'
                }
			},*/
			{
				text: '<i class="fa fa-table" aria-hidden="true"></i> Excel',
				extend: 'excelHtml5',
				exportOptions: {
                    columns: ':visible'
                },
				className: 'btn-info'
			},
			{
				text: '<i class="fa fa-table" aria-hidden="true"></i> CSV',
				extend: 'csvHtml5',
				exportOptions: {
                    columns: ':visible'
                },
				className: 'btn-info'
			},
			{
				text: '<i class="fa fa-file" aria-hidden="true"></i> PDF',
				extend: 'pdfHtml5',
				orientation: 'landscape',
				pageSize: 'LEGAL',
				exportOptions: {
                    columns: ':visible'
                },
				className: 'btn-info'
			},
			{
				text: '<i class="fa fa-print" aria-hidden="true"></i> Imprimir',
				extend: 'print',
				exportOptions: {
                    columns: ':visible'
                },
				className: 'btn-info'
			},
			{
				text: '<i class="fa fa-table" aria-hidden="true"></i> Plantilla Avanzada 5',
				action: function ( e, dt, node, config ) {
					ajax_consultar_cruces_plantilla_avanzada();
				},
				className: 'btn-info'
			},
			{
				text: '<i class="fa fa-sign-out" aria-hidden="true"></i> Generar Salida',
				action: function ( e, dt, node, config ) {
					crear_nueva_salida_cruces();
				},
				className: 'btn-success'
			}
		],
		"dom": "<rf<Bt><pl>i>",
		"lengthMenu": [ [5,10, 25, 50, -1], [5,10, 25, 50, "All"] ],
		iDisplayLength: 25,
		responsive: true,
		"sScrollX": '200%',
		"language": {
			"sProcessing":     '<div style="background="><img src="../images/cargando.gif" height="36" width="36">Consultando información...</div>',
			"sLengthMenu":     "Mostrar _MENU_ registros",
			"sZeroRecords":    "No se encontraron resultados",
			"sEmptyTable":     "No hay datos",
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
		"fnRowCallback": function( nRow, aData ) {
			var $nRow = $(nRow);
			if(aData.salidas == ''){
				if ( $.inArray(aData.DT_RowId, selected) !== -1 ) {
					$nRow.addClass('selected');
				}else{
					$nRow.css({"background-color":"#E87F7F"});
					$nRow.css({"color":"#FFF"});
				}
			}
			return nRow;
		}
	} );
	
	$('#tbl_cruces tbody').on('click', 'td',function(e){		
		var current_row = $(this).parents('tr');
		if (current_row.hasClass('child')) {//Check if the current row is a child row
			current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
		}
		
		var cell_clicked    = table.cell(this).index();
		var oRow  = table.row(current_row).data();
		if (typeof(cell_clicked) != 'undefined') {
			if(cell_clicked.column != 0 && cell_clicked.column != 5 && cell_clicked.column != 9 && cell_clicked.column != 10 && cell_clicked.column != 11){
				var id = oRow.DT_RowId;
				var referencias = oRow.referencias;
				var salidas = oRow.salidas;
				var index = $.inArray(id, selected);
				if(salidas == ''){
					if(referencias != ''){
						if(id != ''){
							if ( index === -1 ) {
								selected.push( oRow.DT_RowId );
								$(this).closest('tr').css({"background-color":"#0088cc"});
								$(this).closest('tr').css({"color":"#FFF"});
							}else{
								selected.splice( index, 1 );
								$(this).closest('tr').css({"background-color":"#E87F7F"});
								$(this).closest('tr').css({"color":"#FFF"});
							}
							$('#lbl_total_seleccionados').html(selected.length);
						}
					}else{
						$('#modalmessagebox_error_titulo').html('Error :: Sistema CASA sin informacion');
						$('#modalmessagebox_error_mensaje').html('Por favor, verifique que las facturas existan en el Sistema CASA.');
						$('#modalmessagebox_error').modal({show: true,backdrop: 'static',keyboard: false});
					}
				}else{
					$('#modalmessagebox_error_titulo').html('Error :: Existe en salida');
					$('#modalmessagebox_error_mensaje').html('El cruce que desea seleccionar ya se encuentra en la salida ' + salidas + '.');
					$('#modalmessagebox_error').modal({show: true,backdrop: 'static',keyboard: false});
				}
			}
		}
	});
	
	$('#tbl_cruces_filter input').unbind();
	$('#tbl_cruces_filter input').bind('keyup', function(e) {
		if(e.keyCode == 13) {
			$('#tbl_cruces').dataTable().fnFilter(this.value);   
		}
	}); 
	
	setTimeout(function () {
		$('#tbl_cruces').DataTable().columns.adjust().responsive.recalc();
	},3000);
}

function actualiza_grid_cruces(){
	selected = [];
	table = $('#tbl_cruces').DataTable();
	table.ajax.reload();

	setTimeout(function () {
		//$('#tbl_cruces').DataTable().columns.adjust().responsive.recalc();
	},1000);
}

function get_fecha_actual(){
	var date = new Date();
	
	var years = date.getFullYear();
	var months = (date.getMonth()+1); 
	var days = date.getDate(); 
	/*var hours = date.getHours();
	var minutes = date.getMinutes();
	var seconds = date.getSeconds();*/
	
	months = months < 10 ? '0' + months : months;
	days = days < 10 ? '0' + days : days;
	return days + '/' + months + '/' + years;
}

function get_fecha_actual_YYYmmddHHiiSS(){
	var date = new Date();
	
	var years = date.getFullYear();
	var months = (date.getMonth()+1); 
	var days = date.getDate(); 
	var hours = date.getHours();
	var minutes = date.getMinutes();
	var seconds = date.getSeconds();
	
	months = months < 10 ? '0' + months : months;
	days = days < 10 ? '0' + days : days;
	hours = hours < 10 ? '0' + hours : hours;
	minutes = minutes < 10 ? '0' + minutes : minutes;
	seconds = seconds < 10 ? '0' + seconds : seconds;
	
	return years + '-' + months + '-' + days + ' ' + hours + ':' + minutes + ':' + seconds;
}

function mostrar_fileupload_factura(pOpt){
	switch(pOpt){
		case 'factura':
			bDocFact = true;
			$("#upload_factura").fileinput('clear');
			$('#div_fileupload_factura_pdf').show();
			$('#div_descargar_factura_pdf').hide();
			break;
		case 'anexo_factura':
			bDocAnexoFact = true;
			$("#upload_anexo_factura").fileinput('clear');
			$('#div_fileupload_anexo_factura_pdf').show();
			$('#div_descargar_anexo_factura_pdf').hide();
			break;
		case 'packing':
			bDocPacList = true;
			$("#upload_packing").fileinput('clear');
			$('#div_fileupload_packlist_pdf').show();
			$('#div_descargar_packlist_pdf').hide();
			break;
		case 'certificado':
			bDocCerOri = true;
			$("#upload_certificado").fileinput('clear');
			$('#div_fileupload_certori_pdf').show();
			$('#div_descargar_certorit_pdf').hide();
			$('#div_fileupload_certori_select').hide();
			break;
		case 'ticketbas':
			bDocTickBasc = true;
			$("#upload_ticket_bascula").fileinput('clear');
			$('#div_fileupload_tickbas_pdf').show();
			$('#div_descargar_tickbas_pdf').hide();
			break;
	}
}

function link_eliminar_docuemento(sFileName,sTipoArchivo){
	sTipoArchElim = sTipoArchivo;
	sFileNameEli = sFileName;
	var sBtnAction ='<a href="javascript:void(0);" class="btn btn-warning" onclick="eliminar_documento_factura();return false;" style="padding-left:.5em" title="Eliminar">\
						<i class="glyphicon glyphicon-trash"></i> <span class="hidden-sm hidden-md hidden-xs">Eliminar Documento</span><span class="hidden-lg hidden-xs">Eliminar</span>\
					</a>';
	return sBtnAction;
}

/* ///////////////////////////////////////////
	CLIENTES CONSOLIDAR
//////////////////////////////////////////// */

function agregar_cliente_consolidar(){
	if($('#sel_consolidar').val() == '' || $('#sel_consolidar').val() == null){
		var sMensaje = 'Es necesario seleccionar el cliente que desea agregar.';
		$('#mensaje_cliente_consolidar_cruce').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		return;
	}
	var newItem = {
		consecutivo: (aCliCons.length+1),
		id_cliente: $('#sel_consolidar').val(),
		cliente : $("#sel_consolidar option:selected").text()
	};
	
	aCliCons.push(newItem);
	inicializar_tabla_clientes_consolidar();
	$("#sel_consolidar").empty().trigger('change');
}

function tabla_eliminar_cliente_consolidar_action(id){
	var sBtnAction ='<a href="javascript:void(0);" onclick="eliminar_cliente_consolidar(\''+id+'\');return false;" style="padding-left:.5em" title="Eliminar"><i class="glyphicon glyphicon-trash"></i> Eliminar</a>&nbsp;';
	return sBtnAction;
}

function inicializar_tabla_clientes_consolidar(){
	var otable = convert_array_table_cliente_consolidar();
	$('#tbl_cliconsolidar').DataTable({
		data: otable,
		"destroy": true,
		responsive: true,
		aLengthMenu: [
			[5, 10, 50, 100, -1],
			[5, 10, 50, 100, "All"]
		],
		iDisplayLength: 5
	});
	
	setTimeout(function () {$('#tbl_cliconsolidar').DataTable().columns.adjust().responsive.recalc();},300);
}

function convert_array_table_cliente_consolidar(){
	//aCliCons
	var oReturn = new Array();
	for(i = 0; i < aCliCons.length ; i++){
		oPush = [ 
			(i+1),
			aCliCons[i].cliente,
			tabla_eliminar_cliente_consolidar_action(i)
		];
		oReturn.push(oPush);
	}
	return oReturn
}

function eliminar_cliente_consolidar(id){
	aCliCons.splice( id, 1 );
	inicializar_tabla_clientes_consolidar();
}

function inicializa_controles_cruce(){
	
	$("#txt_cliente_mdl").val('');
	$("#txt_fecha_mdl").val('');
	$('#txt_caat_transfer').val('');
	$('#txt_scac_transfer').val('');
	$('#txt_po_number').val('');
	$('#txt_direccion_entrega').val('');
	$('#txt_indicaciones').val('');
	$('#txt_observaciones_cruce').val('');
	$("#sel_linea_transportista").empty().trigger('change');
	$("#sel_aduana").val('').trigger('change');
	$('#chk_consolidar_cruce').prop('checked', false );
	$("#sel_consolidar").empty().trigger('change');
	$("#sel_consolidar").prop('disabled', true);
	$("#sel_transfer").empty().trigger('change');
	$("#sel_lugares_entrega").empty().trigger('change');
	$('#chk_consolidar_cruce').prop('checked', false );
	$("#sel_consolidar").empty().trigger('change');
	$("#sel_consolidar").prop('disabled', true);
	$("#mensaje_cruces").html('');
	$("#mensaje_modal_cruces").html('');
	aCliCons= new Array();
	inicializar_tabla_clientes_consolidar();
	//Modal Facturas
	$('#txt_numero_caja').val('');
	$("#sel_tipo_salida").val('').trigger('change');
	$("#sel_agente_americano").empty().trigger('change');
	
	aFacturas = new Array();
	inicializa_controles_facturas();
}

/* ///////////////////////////////////////////
	FACTURAS
//////////////////////////////////////////// */

function agregar_factura_cruce(){
	gebRefExpo = 'factura';
	inicializa_controles_facturas();
	ajax_consulta_controles_factura();
	/*sActFactura = 'Nueva';
	$('#modal_factura').modal({show: true,backdrop: 'static',keyboard: false});*/
}

function inicializa_tabla_facturas(){
	var otable = convert_array_table();
	$('#rpt_facturas').DataTable({
		data: otable,
		"destroy": true,
		responsive: true,
		aLengthMenu: [
			[5, 10, 50, 100, -1],
			[5, 10, 50, 100, "All"]
		],
		iDisplayLength: 5
	});
	
	setTimeout(function () {$('#rpt_facturas').DataTable().columns.adjust().responsive.recalc();},300);
}

function convert_array_table(){
	var oReturn = new Array();
	for(i = 0; i < aFacturas.length ; i++){
		var pcfdi = (aFacturas[i].xml != '' ? aFacturas[i].xml.name : '');
		var panexofac = (aFacturas[i].anexofact != '' ? aFacturas[i].anexofact.name : '');
		var plist = (aFacturas[i].plist != '' ? aFacturas[i].plist.name : '');
		var certificado = (aFacturas[i].certificado != '' ? aFacturas[i].certificado.name : '');
		var eliminar = (gAccion == 'Editar' ? aFacturas[i].eliminar : '');
		var editar = (gAccion == 'Editar' ? aFacturas[i].editar : '');
		var ticketbas = (aFacturas[i].ticketbas != '' ? aFacturas[i].ticketbas.name : '');
		
		oPush = [ 
				aFacturas[i].numero_caja,
				aFacturas[i].numero_factura,
				aFacturas[i].uuid,
				aFacturas[i].fecha,
				aFacturas[i].aaa_nom,
				aFacturas[i].referencia,
				aFacturas[i].regimen,
				aFacturas[i].atados,
				aFacturas[i].peso_kgs,
				aFacturas[i].peso_lbs,
				aFacturas[i].pdf.name,
				pcfdi,
				panexofac,
				plist,
				certificado,
				ticketbas,
				aFacturas[i].cont_aaa,
				aFacturas[i].pedimento,
				editar + '&nbsp;' + eliminar
			]
		oReturn.push(oPush);
	}
	return oReturn;
}

function valida_controles_factura_cruce(){
	if($('#sel_tipo_salida').val() == '' || $('#sel_tipo_salida').val() == null){
		var sMensaje = 'Es necesario seleccionar el tipo de salida(tipo contenedor).';
		$('#mensaje_modal_factura').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#sel_tipo_salida').focus();
		return false;
	}
	if($('#txt_numero_caja').val().trim() == ''){
		var sMensaje = 'Es necesario agregar el numero de contenedor.';
		$('#mensaje_modal_factura').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#txt_numero_caja').focus();
		return false;
	}
	if($('#txt_numero_factura_mdl').val().trim() == ''){
		var sMensaje = 'Es necesario agregar el numero de factura.';
		$('#mensaje_modal_factura').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#txt_numero_factura_mdl').focus();
		return false;
	}
	if($('#txt_fecha_factura_mdl').val().trim() == ''){
		var sMensaje = 'Es necesario agregar la fecha de la factura.';
		$('#mensaje_modal_factura').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#txt_fecha_factura_mdl').focus();
		return false;
	}
	if($('#sel_regimen').val() == 'A1'){
		if($('#txt_numero_uuid_mdl').val().trim() == ''){
			var sMensaje = 'Es necesario agregar el UUID de la factura.';
			$('#mensaje_modal_factura').html('<div class="alert alert-danger">'+sMensaje+'</div>');
			$('#txt_numero_uuid_mdl').focus();
			return false;
		}
	}
	if($('#sel_regimen').val() == '' || $('#sel_regimen').val() == null){
		var sMensaje = 'Es necesario seleccionar el regimen del pedimento donde se agregara la factura.';
		$('#mensaje_modal_factura').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#sel_regimen').focus();
		return false;
	}
	if($('#txt_peso_factura').val() == '' || $('#txt_peso_factura_lbs').val() == ''){
		var sMensaje = 'Es necesario capturar el peso.';
		$('#mensaje_modal_factura').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#txt_peso_factura').focus();
		return false;
	}
	if($('#sel_agente_americano').val() == '' || $('#sel_agente_americano').val() == null){
		var sMensaje = 'Es necesario seleccionar el agente aduanal americano.';
		$('#mensaje_modal_factura').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#sel_agente_americano').focus();
		return false;
	}
	if($('#sel_aviso_adhesion').val() != '' || $('#sel_permisos').val() != ''){
		var sMensaje = 'Es necesario presionar el boton [ + Agregar] para que los avisos se vinculen a la factura.';
		$('#mensaje_modal_factura').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		return false;
	}
	return true;
}

function guardar_factura(){
	if(sActFactura == 'Nueva'){
		ajax_agregar_factura_editar_cruce();
	}else{
		ajax_guardar_factura_editar();
	}
}

function inicializa_controles_facturas(){
	
	$('#txt_buscar_referencia_mdl').val('');
	$('#txt_pedimento_referencia_mdl').val('');
	
	$('#txt_numero_factura_mdl').val('');
	$('#txt_fecha_factura_mdl').val('');
	$('#txt_numero_uuid_mdl').val('');
	
	$("#sel_regimen").empty().trigger('change');
	$('#txt_numero_atados').val('');
	$('#txt_peso_factura').val('');
	$('#txt_peso_factura_lbs').val('');
	
	aPermisos = new Array();
	inicializa_controles_permisos()
	inicializa_tabla_permisos();
	
	//Documento Factura
	bDocFact = true;
	$("#upload_factura").fileinput('clear');
	$('#div_fileupload_factura_pdf').show();
	$('#div_descargar_factura_pdf').hide();
	//Documento Anexo FACTURA
	bDocAnexoFact = true;
	$("#upload_anexo_factura").fileinput('clear');
	$('#div_fileupload_anexo_factura_pdf').show();
	$('#div_descargar_anexo_factura_pdf').hide();
	//Documento Packing List
	bDocPacList = true;
	$("#upload_packing").fileinput('clear');
	$('#div_fileupload_packlist_pdf').show();
	$('#div_descargar_packlist_pdf').hide();
	//Documento Certificado Origen
	bDocCerOri = true;
	$("#upload_certificado").fileinput('clear');
	$('#div_fileupload_certori_pdf').hide();
	$('#div_descargar_certorit_pdf').hide();
	$('#div_fileupload_certori_select').show();
	//Documento Ticket Bascula
	bDocTickBasc = true;
	$("#upload_ticket_bascula").fileinput('clear');
	$('#div_fileupload_tickbas_pdf').show();
	$('#div_descargar_tickbas_pdf').hide();
	
	$('#descargar_aviso_adhesion').html('');
	$('#descargar_aviso_automatico').html('');
	
	$("#upload_factura,#upload_anexo_factura,#upload_packing,#upload_certificado,#upload_cfdi,#upload_ticket_bascula").fileinput('clear');
	$('#sel_cert_origen').val('');
	$('#descargar_certificado_origen').html('');

	$('#mensaje_modal_factura').html('');
	deshabilitar_controles_factura_referencia(true);
	$('#txt_buscar_referencia_mdl').focus();
}

function tabla_eliminar_factura_actions(id){
	var sBtnAction ='<a href="javascript:void(0);" class="btn btn-info btn-xs" onclick="eliminar_factura(\''+id+'\');return false;" style="padding-left:.5em" title="Eliminar"><i class="glyphicon glyphicon-trash"></i> Eliminar</a>&nbsp;';
	return sBtnAction;		
}

function eliminar_factura(item) {
	if(aFacturas.length > 1){
		nFac = item;
		$('#modalconfirm').modal({show: true,backdrop: 'static',keyboard: false});
	}else{
		var sMensaje = 'La factura no puede ser eliminada ya que el cruce quedaria sin informacion.';
		$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+sMensaje+'</div>');
	}
}

function eliminar_factura_aceptar(){
	$('#modalconfirm').modal('hide');
	ajax_eliminar_factura_cruce();
}

function seleccionar_permiso_adhesion_archivo(){
	var id_permiso = $('#sel_aviso_adhesion').val();
	for(i=0; i<aPermisosAdhesion.length; i++){
		if(aPermisosAdhesion[i].id == id_permiso){
			$('#descargar_aviso_adhesion').html('<div class="alert alert-info"><strong><a href="'+aPermisosAdhesion[i].url+'" target="_blank"><i class="glyphicon glyphicon-eye-open" ></i> Ver Documento '+aPermisosAdhesion[i].text+'</a></strong</div>');
			break;
		}
		$('#descargar_aviso_adhesion').html('');
	}
}

function seleccionar_aviso_automatico_archivo(){
	var id_permiso = $('#sel_permisos').val();
	for(i=0; i<aPermisosAutomaticos.length; i++){
		if(aPermisosAutomaticos[i].id == id_permiso){
			$('#descargar_aviso_automatico').html('<div class="alert alert-info"><strong><a href="'+aPermisosAutomaticos[i].url+'" target="_blank"><i class="glyphicon glyphicon-eye-open" ></i> Ver Documento '+aPermisosAutomaticos[i].text+'</a></strong</div>');
			break;
		}
		$('#descargar_aviso_automatico').html('');
	}
}

function seleccionar_certificados_origen_archivo(){
	var id_certificado = $('#sel_cert_origen').val();
	for(i=0; i<aCertificados.length; i++){
		if(aCertificados[i].id == id_certificado){
			$('#descargar_certificado_origen').html('<div class="alert alert-info"><strong><a href="'+aCertificados[i].url+'" target="_blank"><i class="glyphicon glyphicon-eye-open" ></i> Ver Documento '+aCertificados[i].text+'</a></strong</div>');
			break;
		}
		$('#descargar_certificado_origen').html('');
	}
}

//  *-*--*-*-*-*-*-*-* AJAX *-*--*-*-*-*-*-*-*

function ajax_consulta_controles_factura(){
	$.ajax({
		type: "POST",
		url: 'ajax/cruces_exportacion/ajax_consulta_controles_facturas.php',
		data: {id_cliente: sIdCliente},
		timeout: 120000,
		beforeSend: function (dataMessage) {
			$('modal_factura').animate({scrollTop: $("#mensaje_modal_cruces").offset().top}, 2000);
			$("#mensaje_modal_cruces").html('<div class="alert alert-info"><img src="../images/cargando.gif" height="16" width="16"/> Cargando informaci&oacute;n, espere un momento por favor...</div>');
		},
		success:  function (response) {
			$("#mensaje_modal_cruces").html('');
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					//Avisos Automaticos
					var sHtmlPerAut = '<option value="">[SELECCIONAR]</option>';
					aPermisosAutomaticos = respuesta.aPermisosAutomaticos;
					for(i=0; i<respuesta.aPermisosAutomaticos.length; i++){
						sHtmlPerAut += '<option value="'+respuesta.aPermisosAutomaticos[i].id+'">'+respuesta.aPermisosAutomaticos[i].text+'</option>';
					}
					$("#sel_permisos").html(sHtmlPerAut);
					//aPermisosAdhesion
					aPermisosAdhesion = respuesta.aPermisosAdhesion;
					sHtmlPerAut = '<option value="">[SELECCIONAR]</option>';
					for(i=0; i<respuesta.aPermisosAdhesion.length; i++){
						sHtmlPerAut += '<option value="'+respuesta.aPermisosAdhesion[i].id+'">'+respuesta.aPermisosAdhesion[i].text+'</option>';
					}
					$("#sel_aviso_adhesion").html(sHtmlPerAut);
					//Certificados Origen
					sHtmlPerAut = '<option value="">[SELECCIONAR]</option>';
					aCertificados = respuesta.aCertificados;
					aCertificados = respuesta.aCertificados;
					for(i=0; i<respuesta.aCertificados.length; i++){
						sHtmlPerAut += '<option value="'+respuesta.aCertificados[i].id+'">'+respuesta.aCertificados[i].text+'</option>';
					}
					$("#sel_cert_origen").html(sHtmlPerAut);

					sActFactura = 'Nueva';
					$('#modal_factura').modal({show: true,backdrop: 'static',keyboard: false});
				}else{
					var strMensaje = respuesta.Mensaje + respuesta.Error;
					$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+strMensaje+'</div>');
				}
			}else{
				alert('La sesion del usuario ha finalizado, es necesario iniciar nuevamente.');					
				setTimeout(function () {window.location.replace('../logout.php');},4000);
			}				
		},
		error: function(a,b){
			$("#mensaje_modal_cruces").html('');
			var strMensaje = a.status+' [' + a.statusText + ']';
			alert(strMensaje);
		}
	});
}

function subir_certificado_origen_factura(){
	$('#div_fileupload_certori_select').hide();
	$("#upload_certificado").fileinput('clear');
	$('#div_fileupload_certori_pdf').show();
	bCertSel = false;
}

function ajax_agregar_factura_editar_cruce(){
	if(!valida_controles_factura_cruce()){
		return false;
	}
	var pfactura = document.getElementById('upload_factura').files[0];
	var panexo_factura = document.getElementById('upload_anexo_factura').files[0];
	var parchivoxml = document.getElementById('upload_cfdi').files[0];
	var pcertificado = ''; var pid_certificado = '';
	
	if(bCertSel){
		pid_certificado = $('#sel_cert_origen').val();
	}else{
		pcertificado = document.getElementById('upload_certificado').files[0];
	}
	var ppackinglist = document.getElementById('upload_packing').files[0];
	var pticketbascula = document.getElementById('upload_ticket_bascula').files[0];
	
	if(!pfactura){
		var strMensaje = 'Es necesario agregarar el archivo de la factura en formato PDF.';
		$('#mensaje_modal_factura').html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
		return false;
	}
	if($('#sel_regimen').val() == 'A1'){
		if(!parchivoxml){
			var strMensaje = 'Es necesario agregarar el archivo del CFDI en formato XML.';
			$('#mensaje_modal_factura').html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
			return false;
		}
	}
	
	if(!parchivoxml){ parchivoxml = ''; }
	if(!panexo_factura){ panexo_factura = ''; }
	if(!pcertificado){ pcertificado = ''; }
	if(!ppackinglist){ ppackinglist = ''; }
	if(!pticketbascula){ pticketbascula = ''; }
	
	var ptipo_salida = $("#sel_tipo_salida").val();
	var pnumero_caja = $("#txt_numero_caja").val().trim().toUpperCase();
	var pnum_factura = $("#txt_numero_factura_mdl").val().trim().toUpperCase();
	var puuid = $("#txt_numero_uuid_mdl").val().trim().toUpperCase();
	var pfecha_factura = $("#txt_fecha_factura_mdl").val().trim().toUpperCase();
	var paaa = $('#sel_agente_americano').val();
	var paaa_nom = $("#sel_agente_americano option:selected").text();
	var preferencia = $("#txt_buscar_referencia_mdl").val().trim().toUpperCase();
	var pregimen = $('#sel_regimen').val();
	var patados = ($('#txt_numero_atados').val().trim() == '' ? '0' : $('#txt_numero_atados').val().trim());
	var ppeso_kgs = ($('#txt_peso_factura').val().trim() == '' ? '0' : $('#txt_peso_factura').val().trim());
	var ppeso_lbs = ($('#txt_peso_factura_lbs').val().trim() == '' ? '0' : $('#txt_peso_factura_lbs').val().trim());
	
	var odata = new FormData();
	odata.append('id_cruce',sIdCruce);
	odata.append('tipo_salida',ptipo_salida);
	odata.append('numero_caja',pnumero_caja);
	odata.append('numero_factura',pnum_factura);
	odata.append('uuid', puuid);
	odata.append('fecha',pfecha_factura);
	odata.append('referencia',preferencia);
	odata.append('regimen',pregimen);
	odata.append('atados',patados);
	odata.append('peso_kgs',ppeso_kgs);
	odata.append('peso_lbs',ppeso_lbs);
	odata.append('aaa', paaa);
	odata.append('permisos', JSON.stringify(aPermisos));
	odata.append('f_factura',pfactura);
	odata.append('bAnexoFact',(panexo_factura != '' ? '1' : '0'));
	odata.append('f_anexofact',panexo_factura);
	odata.append('id_certificado',pid_certificado);
	odata.append('bCfdi',(parchivoxml != '' ? '1' : '0'));
	odata.append('f_cfdi',parchivoxml);
	odata.append('bCerOri',(pcertificado != '' ? '1' : '0'));
	odata.append('f_cerori',pcertificado);
	odata.append('bPackList',(ppackinglist != '' ? '1' : '0'));
	odata.append('f_plist',ppackinglist);
	odata.append('bTicketBascula',(pticketbascula != '' ? '1' : '0'));
	odata.append('f_ticketbascula',pticketbascula);
	
	$.ajax({
		url:   'ajax/cruces_exportacion/ajax_agregar_factura_cruce_expo.php',
		type:  'post',
		data:	odata,
		timeout: 300000,
		contentType: false,
		cache: false,
		processData:false,
		xhr: function()
		{
			var xhr = new window.XMLHttpRequest();
			xhr.upload.addEventListener("progress", function(evt){
			  if (evt.lengthComputable) {
				var percent = (evt.loaded / evt.total * 100).toFixed(0);
				if(percent > 98) percent = 99;
				var sMen = '<div class="progress progress-striped active">';
				sMen += '		<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
				sMen += '			<span>'+percent+'% Completado</span>';
				sMen += '		</div>';
				sMen += '	</div>';
				$('#mensaje_modal_factura').html(sMen);
			  }
			}, false);
			return xhr;
		},
		beforeSend: function () {
			var strMensaje = 'Guardando información, espere un momento por favor...';
			$("#mensaje_modal_factura").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if(response != '500'){
				respuesta = JSON.parse(response);
				if (respuesta.Codigo=='1'){
					$("#mensaje_modal_factura").html('');
					var strMensaje = 'La factura se ha guardado correctamente!!.';
					$("#mensaje_modal_cruces").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					
					aFacturas = respuesta.aFacturas;
					inicializa_tabla_facturas();
					$('#modal_factura').modal('hide');
					
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_modal_factura").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_modal_factura").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_modal_factura").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
	
}

function eliminar_factura_editar_cruce(IdFactura){
	if(aFacturas.length > 1){
		sIdFactura = IdFactura;
		$('#modalconfirm').modal({show: true,backdrop: 'static',keyboard: false});
	}else{
		var sMensaje = 'La factura no puede ser eliminada ya que el cruce quedaría sin información.';
		$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+sMensaje+'</div>');
	}
}

function ajax_eliminar_factura_cruce(){
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_eliminar_factura_cruce_expo.php',
		data: {
			id_factura: sIdFactura,
			id_cruce: sIdCruce
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Eliminando información, espere un momento por favor...';
			$("#mensaje_modal_cruces").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				
				if (respuesta.Codigo == '1'){
					var strMensaje = respuesta.Mensaje;
					$("#mensaje_modal_cruces").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					
					aFacturas = respuesta.aFacturas;
					inicializa_tabla_facturas();
					
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_modal_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_modal_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_modal_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

function ajax_consulta_factura_editar(pIdFac){
	sIdFactura = pIdFac; sActFactura = 'Editar';
	inicializa_controles_facturas();
	$.ajax({
		type: "POST",
		timeout: 120000,
		url: 'ajax/cruces_exportacion/ajax_consulta_factura_cruce_expo.php',
		data: {
			id_detalle_cruce: sIdFactura,
			id_cliente: sIdCliente
		},
		beforeSend: function (dataMessage) {
			$('modal_factura').animate({scrollTop: $("#mensaje_modal_cruces").offset().top}, 2000);
			$("#mensaje_modal_cruces").html('<div class="alert alert-info"><img src="../images/cargando.gif" height="16" width="16"/> Cargando informaci&oacute;n, espere un momento por favor...</div>');
		},
		success:  function (response) {
			$("#mensaje_modal_cruces").html('');
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					if(respuesta.referencia != ''){
						deshabilitar_controles_factura_referencia(false);
					}else{
						deshabilitar_controles_factura_referencia(true);
					}					
					$('#txt_buscar_referencia_mdl').val(respuesta.referencia);
					$('#txt_pedimento_referencia_mdl').val(respuesta.pedimento);
					
					$('#sel_tipo_salida').select2("trigger", "select", {
						data: { id: respuesta.tipo_salida, text: respuesta.tipo_salida }
					});
					$('#txt_numero_caja').val(respuesta.numero_caja);
					
					$('#txt_numero_factura_mdl').val(respuesta.numero_factura);
					$('#txt_fecha_factura_mdl').val(respuesta.fecha_factura);
					$('#txt_numero_uuid_mdl').val(respuesta.uuid);
					$('#sel_regimen').select2("trigger", "select", {
						data: {
						   id: respuesta.regimen,
						   text: respuesta.regimen_nom
						}
					});
					$('#txt_numero_atados').val(respuesta.atados);
					$('#txt_peso_factura').val(respuesta.peso_factura_kgs);
					$('#txt_peso_factura_lbs').val(respuesta.peso_factura_lbs);
					$('#sel_agente_americano').select2("trigger", "select", {
						data: {
						   id: respuesta.noaaa,
						   text: respuesta.nombreaa
						}
					});

					var sHtmlPerAut = '<option value="">[SELECCIONAR]</option>';
					aPermisosAutomaticos = respuesta.aPermisosAutomaticos;
					for(i=0; i<respuesta.aPermisosAutomaticos.length; i++){
						sHtmlPerAut += '<option value="'+respuesta.aPermisosAutomaticos[i].id+'">'+respuesta.aPermisosAutomaticos[i].text+'</option>';
					}
					$("#sel_permisos").html(sHtmlPerAut);
					//aPermisosAdhesion
					sHtmlPerAut = '<option value="">[SELECCIONAR]</option>';
					aPermisosAdhesion = respuesta.aPermisosAdhesion;
					for(i=0; i<aPermisosAdhesion.length; i++){
						sHtmlPerAut += '<option value="'+aPermisosAdhesion[i].id+'">'+respuesta.aPermisosAdhesion[i].text+'</option>';
					}
					$("#sel_aviso_adhesion").html(sHtmlPerAut);
					//Certificados Origen
					sHtmlPerAut = '<option value="">[SELECCIONAR]</option>';
					aCertificados = respuesta.aCertificados;
					for(i=0; i<respuesta.aCertificados.length; i++){
						sHtmlPerAut += '<option value="'+respuesta.aCertificados[i].id+'">'+respuesta.aCertificados[i].text+'</option>';
					}
					$("#sel_cert_origen").html(sHtmlPerAut);
					
					aPermisos = respuesta.aPermisos;
					inicializa_tabla_permisos();
					$('#descargar_aviso_adhesion').html('');
					$('#descargar_aviso_automatico').html('');
					$("#upload_cfdi").fileinput('clear');
					$("#upload_anexo_factura").fileinput('clear');
					$("#upload_packing").fileinput('clear');
					$("upload_certificado").fileinput('clear');
					$("#upload_ticket_bascula").fileinput('clear');
					
					//Factura
					bDocCFDI = false;
					bDocFact = false;
					$('#div_fileupload_factura_pdf').hide();
					$('#div_descargar_factura_pdf').show();
					$('#link_descargar_factura_pdf').html(respuesta.archivo_factura);
					//PackingList
					if(respuesta.archivo_packinglist != ''){
						bDocPacList = false;
						$('#div_fileupload_packlist_pdf').hide();
						$('#div_descargar_packlist_pdf').show();
						$('#link_descargar_packlist_pdf').html(respuesta.archivo_packinglist);
						var link_eliminar = link_eliminar_docuemento(respuesta.nombre_packinglist,'packing');
						$('#link_eliminar_packlist_pdf').html(link_eliminar);
					}
					//Certificado Origen
					if(respuesta.archivo_cert_origen != ''){
						bDocCerOri = false;
						$('#div_fileupload_certori_pdf').hide();
						$('#div_fileupload_certori_select').hide();
						$('#div_descargar_certorit_pdf').show();
						$('#link_descargar_certori_pdf').html(respuesta.archivo_cert_origen);
						var link_eliminar = link_eliminar_docuemento(respuesta.nombre_cert_origen,'certificado');
						$('#link_eliminar_certori_pdf').html(link_eliminar);
					}else{
						if(respuesta.id_certificado != ''){
							$('#div_fileupload_packlist_pdf').hide();
							$('#div_descargar_packlist_pdf').hide();
							$('#div_fileupload_certori_select').show();
							$('#sel_cert_origen').val(respuesta.id_certificado);
						}
					}
					//Ticket Bascula
					if(respuesta.archivo_ticketbascula != ''){
						bDocTickBasc = false;
						$('#div_fileupload_tickbas_pdf').hide();
						$('#div_descargar_tickbas_pdf').show();
						$('#link_descargar_tickbas_pdf').html(respuesta.archivo_ticketbascula);
						var link_eliminar = link_eliminar_docuemento(respuesta.nombre_ticketbascula,'ticketbas');
						$('#link_eliminar_tickbas_pdf').html(link_eliminar);
					}
					gebRefExpo = 'factura';
					ActFactura = 'Editar';					
					$('#modal_factura').modal({show: true,backdrop: 'static',keyboard: false});
				}else{
					var strMensaje = respuesta.Mensaje + respuesta.Error;
					$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+strMensaje+'</div>');
				}
			}else{
				alert('La sesion del usuario ha finalizado, es necesario iniciar nuevamente.');					
				setTimeout(function () {window.location.replace('../logout.php');},4000);
			}				
		},
		error: function(a,b){
			$("#mensaje_modal_cruces").html('');
			var strMensaje = a.status+' [' + a.statusText + ']';
			alert(strMensaje);
		}
	});
}

function eliminar_documento_factura(){
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_eliminar_documento_cruce_expo.php',
		data: {
			file_name: sFileNameEli,
			tipo_archivo: sTipoArchElim
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Eliminando archivo, espere un momento por favor...';
			$("#mensaje_modal_factura").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					var strMensaje = respuesta.Mensaje;
					$("#mensaje_modal_factura").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					switch(sTipoArchElim){
						case 'anexo_factura':
							bDocAnexoFact = true;
							$("#upload_anexo_factura").fileinput('clear');
							$('#div_fileupload_anexo_factura_pdf').show();
							$('#div_descargar_anexo_factura_pdf').hide();
							break;
						case 'packing':
							bDocPacList = true;
							$("#upload_packing").fileinput('clear');
							$('#div_fileupload_packlist_pdf').show();
							$('#div_descargar_packlist_pdf').hide();
							break;
						case 'certificado':
							bDocCerOri = true;
							$("#upload_certificado").fileinput('clear');
							$('#div_fileupload_certori_pdf').show();
							$('#div_descargar_certorit_pdf').hide();
							$('#div_fileupload_certori_select').hide();
							break;
						case 'ticketbas':
							bDocTickBasc = true;
							$("#upload_ticket_bascula").fileinput('clear');
							$('#div_fileupload_tickbas_pdf').show();
							$('#div_descargar_tickbas_pdf').hide();
							break;
					}
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_modal_factura").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_modal_factura").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_modal_factura").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

function ajax_guardar_factura_editar(){
	if(!valida_controles_factura_cruce()){
		return false;
	}
	var odata = new FormData();
	var pfactura = document.getElementById('upload_factura').files[0];
	var panexofactura = document.getElementById('upload_anexo_factura').files[0];
	var parchivoxml = document.getElementById('upload_cfdi').files[0];
	var pcertificado = ''; var pid_certificado = '';
	if(bCertSel){
		pid_certificado = $('#sel_cert_origen').val();
	}else{
		pcertificado = document.getElementById('upload_certificado').files[0];
	}
	var ppackinglist = document.getElementById('upload_packing').files[0];
	var pticketbascula = document.getElementById('upload_ticket_bascula').files[0];
	
	if(bDocCFDI){
		if(!parchivoxml && $('#sel_regimen').val() == 'A1'){
			var strMensaje = 'Es necesario agregarar el archivo del CFDI en formato XML.';
			$('#mensaje_modal_factura').html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
			return false;
		}
		//odata.append('f_cfdi',parchivoxml);
	}
	//odata.append('bdoccfdi',(bDocCFDI ? '1' : '0'));
	if(bDocFact){
		if(!pfactura){
			var strMensaje = 'Es necesario agregarar el archivo de la factura en formato PDF.';
			$('#mensaje_modal_factura').html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
			return false;
		}
	}
	odata.append('bdocfact',(bDocFact ? '1' : '0'));
	
	if(!parchivoxml){ parchivoxml = ''; }
	if(!panexofactura){ panexofactura = '';	}
	if(!pcertificado){ pcertificado = ''; }
	if(!ppackinglist){ ppackinglist = ''; }
	if(!pticketbascula){ pticketbascula = ''; }
	
	var ptipo_salida = $("#sel_tipo_salida").val().trim().toUpperCase();
	var pnumero_caja = $("#txt_numero_caja").val().trim().toUpperCase();
	var pnum_factura = $("#txt_numero_factura_mdl").val().trim().toUpperCase();
	var puuid = $("#txt_numero_uuid_mdl").val().trim().toUpperCase();
	var pfecha_factura = $("#txt_fecha_factura_mdl").val().trim().toUpperCase();
	var paaa = $('#sel_agente_americano').val();
	var paaa_nom = $("#sel_agente_americano option:selected").text();
	
	var pregimen = $('#sel_regimen').val();
	var preferencia = $('#txt_buscar_referencia_mdl').val().trim().toUpperCase();;
	var patados = ($('#txt_numero_atados').val().trim() == '' ? '0' : $('#txt_numero_atados').val().trim());
	var ppeso_kgs = ($('#txt_peso_factura').val().trim() == '' ? '0' : $('#txt_peso_factura').val().trim());
	var ppeso_lbs = ($('#txt_peso_factura_lbs').val().trim() == '' ? '0' : $('#txt_peso_factura_lbs').val().trim());
	
	odata.append('id_cruce',sIdCruce);
	odata.append('id_detalle_cruce',sIdFactura);
	odata.append('tipo_salida',ptipo_salida);
	odata.append('numero_caja',pnumero_caja);
	odata.append('numero_factura',pnum_factura);
	odata.append('uuid', puuid);
	odata.append('fecha',pfecha_factura);
	odata.append('regimen',pregimen);
	odata.append('referencia',preferencia);
	odata.append('atados',patados);
	odata.append('peso_kgs',ppeso_kgs);
	odata.append('peso_lbs',ppeso_lbs);
	odata.append('aaa', paaa);
	odata.append('f_factura',pfactura);
	odata.append('bdoccfdi',(bDocCFDI ? '1' : '0'));
	odata.append('f_cfdi',parchivoxml);
	odata.append('bAnexoFact',(panexofactura != '' ? '1' : '0'));
	odata.append('f_anexofact',panexofactura);
	odata.append('id_certificado',pid_certificado);
	odata.append('bCerOri',(pcertificado != '' ? '1' : '0'));
	odata.append('f_cerori',pcertificado);
	odata.append('bPackList',(ppackinglist != '' ? '1' : '0'));
	odata.append('f_plist',ppackinglist);
	odata.append('bTicketBascula',(pticketbascula != '' ? '1' : '0'));
	odata.append('f_ticketbascula',pticketbascula);
	
	$.ajax({
		url:   'ajax/cruces_exportacion/ajax_editar_factura_cruce_expo.php',
		type:  'post',
		data:	odata,
		timeout: 300000,
		contentType: false,
		cache: false,
		processData:false,
		xhr: function()
		{
			var xhr = new window.XMLHttpRequest();
			xhr.upload.addEventListener("progress", function(evt){
			  if (evt.lengthComputable) {
				var percent = (evt.loaded / evt.total * 100).toFixed(0);
				if(percent > 98) percent = 99;
				var sMen = '<div class="progress progress-striped active">';
				sMen += '		<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
				sMen += '			<span>'+percent+'% Completado</span>';
				sMen += '		</div>';
				sMen += '	</div>';
				$('#mensaje_modal_factura').html(sMen);
			  }
			}, false);
			return xhr;
		},
		beforeSend: function () {
			var strMensaje = 'Guardando información, espere un momento por favor...';
			$("#mensaje_modal_factura").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if(response != '500'){
				respuesta = JSON.parse(response);
				if (respuesta.Codigo=='1'){
					$("#mensaje_modal_factura").html('');
					var strMensaje =  respuesta.Mensaje + respuesta.Error;
					$("#mensaje_modal_factura").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					
					aFacturas = respuesta.aFacturas;
					inicializa_tabla_facturas();
					$('#modal_factura').modal('hide');
					
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_modal_factura").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_modal_factura").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_modal_factura").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

function ajax_seleccionar_referencia_factura(){
	if($("#txt_buscar_referencia_mdl").val().trim() == ''){
		$("#mensaje_consultar_referencia_factura").html('<div class="alert alert-danger">Es necesario agregar la referencia de la factura.</div>');
		$("#txt_buscar_referencia_mdl").focus();
		return;
	}
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_consultar_referencia_factura.php',
		data: {
			referencia: $("#txt_buscar_referencia_mdl").val().trim().toUpperCase()
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Consultando información, espere un momento por favor...';
			$("#mensaje_consultar_referencia_factura").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					$("#mensaje_consultar_referencia_factura").html('');
					if(respuesta.bExiste == '1'){
						deshabilitar_controles_factura_referencia(false);
						if(respuesta.Regimen != ''){
							$('#txt_pedimento_referencia_mdl').val(respuesta.Pedimento);
							$('#sel_regimen').select2("trigger", "select", {
								data: {
								   id: respuesta.Regimen,
								   text: respuesta.Regimen_Nom
								}
							});
							$("#btn_generar_pedimento").prop('disabled',true);
							if($("#sel_regimen").val() == 'A1'){
								$("#txt_numero_factura_mdl").prop('disabled',true);
								$("#txt_fecha_factura_mdl").prop('disabled',true);
								$("#txt_numero_uuid_mdl").prop('disabled',true);
							}else{
								$("#txt_numero_factura_mdl").prop('disabled',false);
								$("#txt_fecha_factura_mdl").prop('disabled',false);
								$("#txt_numero_uuid_mdl").prop('disabled',false);
							}
						}else{
							$("#btn_generar_pedimento").prop('disabled',false);
						}
					}else{
						//Preguntar si se desea generar la referencia.
						$('#modalconfirm_genreferencia').modal({show: true,backdrop: 'static',keyboard: false});
					}
				}else{
					var strMensaje = respuesta.Mensaje;
					$("#mensaje_consultar_referencia_factura").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_consultar_referencia_factura").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_consultar_referencia_factura").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

function generar_nueva_referencia_expo(){
	$('#modalconfirm_genreferencia').modal('hide');
	$("#txt_referencia_anterior").val('');
	$("#div_referencia_rectificacion_genrefexpo").hide();
	setTimeout(function(){$('#modalgenreferencia').modal({show: true,backdrop: 'static',keyboard: false});},500);
}

function deshabilitar_controles_factura_referencia(pOpt){
	$("#txt_buscar_referencia_mdl").prop('disabled',!pOpt);
	
	$("#sel_tipo_salida").prop('disabled',pOpt);
	$("#txt_numero_caja").prop('disabled',pOpt);
	
	if(pOpt){
		$("#btn_seleccionar_referencia_factura").show();
		$("#btn_generar_referencia_factura").show();
		$("#btn_cancelar_referencia_factura").hide();
	}else{
		$("#btn_seleccionar_referencia_factura").hide();
		$("#btn_generar_referencia_factura").hide();
		$("#btn_cancelar_referencia_factura").show();
	}
	$("#sel_regimen").prop('disabled',pOpt);
	
	$("#btn_generar_pedimento").prop('disabled',pOpt);
	
	$('#upload_cfdi').fileinput((pOpt ? 'disable' : 'enable'));
	
	if($("#sel_regimen").val() == 'A1'){
		$("#txt_numero_factura_mdl").prop('disabled',true);
		$("#txt_fecha_factura_mdl").prop('disabled',true);
		$("#txt_numero_uuid_mdl").prop('disabled',true);
	}else{
		$("#txt_numero_factura_mdl").prop('disabled',pOpt);
		$("#txt_fecha_factura_mdl").prop('disabled',pOpt);
		$("#txt_numero_uuid_mdl").prop('disabled',pOpt);
	}
	
	$("#txt_numero_atados").prop('disabled',pOpt);
	$("#txt_peso_factura").prop('disabled',pOpt);
	$("#txt_peso_factura_lbs").prop('disabled',pOpt);
	$("#sel_agente_americano").prop('disabled',pOpt);
	
	$("#sel_aviso_adhesion").prop('disabled',pOpt);
	$("#sel_permisos").prop('disabled',pOpt);
	$("#btn_tipo_caja").prop('disabled',pOpt);
	
	$('#upload_factura').fileinput((pOpt ? 'disable' : 'enable'));
	$('#upload_anexo_factura').fileinput((pOpt ? 'disable' : 'enable'));
	$('#upload_packing').fileinput((pOpt ? 'disable' : 'enable'));
	$('#upload_certificado').fileinput((pOpt ? 'disable' : 'enable'));
	$('#sel_cert_origen').prop('disabled',pOpt);
	$('#btn_subir_archivo_certificado').prop('disabled',pOpt);
	$('#upload_ticket_bascula').fileinput((pOpt ? 'disable' : 'enable'));
	
	
	$("#btn_guardar_factura_mdl").prop('disabled',pOpt);
}

function ajax_generar_nueva_referencia_expo(){
	var sRef = '';
	if($('#chk_rectificacion_genref').prop('checked')){
		if($("#txt_referencia_anterior").val().trim() == ''){
			var sMensaje = 'Es necesario agregar la referencia original de la rectificacion.';
			$("#mensaje_mdl_generar_ref_expo").html('<div class="alert alert-danger alert-dismissible" role="alert">'+sMensaje+'</div>');
			return false;
		}else{
			sRef = $("#txt_referencia_anterior").val().toUpperCase();
		}
	}
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_generar_referencia_expo.php',
		data: {
			referencia: sRef,
			cliente: sIdCliente
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Consultando información, espere un momento por favor...';
			$("#mensaje_mdl_generar_ref_expo").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					$("#mensaje_mdl_generar_ref_expo").html('');
					if(gebRefExpo == 'factura'){
						$("#txt_buscar_referencia_mdl").val(respuesta.Referencia);
						deshabilitar_controles_factura_referencia(false);
					}else{
						$("#txt_referencia_asigreffac_mdl").val(respuesta.Referencia);
						$("#mensaje_asigreffac_modal").html('<div class="alert alert-info alert-dismissible" role="alert">Se genero la referencia <strong>'+respuesta.Referencia+'</strong>!</div>');
					}
					$('#modalgenreferencia').modal('hide');
				}else{
					var strMensaje = respuesta.Mensaje;
					$("#mensaje_mdl_generar_ref_expo").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_mdl_generar_ref_expo").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_mdl_generar_ref_expo").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

function cancelar_referencia_factura(){
	if(sActFactura == 'Nueva'){
		inicializa_controles_facturas();
	}else{
		deshabilitar_controles_factura_referencia(true);
	}
}

//GENERAR Pedimento

function generar_nuevo_pedimento_referencia(){
	var anio = (new Date).getFullYear();
	$('#sel_pedimento_mod_anio').val(anio);
	$('#sel_pedimento_mod_patente').val('1664');
	$("#sel_pedimento_mod_regimen").empty().trigger('change');
	$("#sel_pedimento_mod_cliente_casa").val('');
	$("#txt_pedimento_mod_desc_merc").val('');
	if($('#sel_aduana').val() == '' || $('#sel_aduana').val() == null){
		var strMensaje = 'Es necesario seleccionar  la aduana del cruce para poder generar un n&uacute;mero de pedimento.';
		$('#modalmessagebox_error_titulo').html('Error :: Seleccionar Cruces');
		$('#modalmessagebox_error_mensaje').html(strMensaje);
		$('#modalmessagebox_error').modal({show: true,backdrop: 'static',keyboard: false});
		return false;
	}
	if($('#sel_regimen').val() != '' && $('#sel_regimen').val() != null){
		$('#sel_pedimento_mod_regimen').select2("trigger", "select", {
							data: {
							   id: $('#sel_regimen').val(),
							   text: $("#sel_regimen option:selected").text()
							}
						});
	}
	$('#modalgenpedimento').modal({show: true,backdrop: 'static',keyboard: false});
}

function ajax_generar_nuevo_pedimento_ref(){
	if($('#sel_pedimento_mod_cliente_casa').val() == '' || $('#sel_pedimento_mod_cliente_casa').val() == null){
		var strMensaje = 'Es necesario seleccionar el cliente.';
		$("#mensaje_mdl_generar_pedimento_ref").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
		return false;
	}
	if($('#sel_aduana').val() == '' || $('#sel_aduana').val() == null){
		var strMensaje = 'Es necesario seleccionar el r&eacute;gimen del pedimento.';
		$("#mensaje_mdl_generar_pedimento_ref").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
		return false;
	}
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/librop/ajax_set_nuevo_pedimento.php',
		data: {
			anio: $("#sel_pedimento_mod_anio").val(),
			aduana: $("#sel_aduana").val(),
			patente: $("#sel_pedimento_mod_patente").val(),
			referencia: (gebRefExpo == 'factura' ? $("#txt_buscar_referencia_mdl").val().trim() : $("#txt_referencia_asigreffac_mdl").val().trim()),
			fecha: get_fecha_actual(),
			cve_pedimento: $("#sel_pedimento_mod_regimen").val().trim(),
			descripcion: $("#txt_pedimento_mod_desc_merc").val().trim().toUpperCase(),
			id_cliente: $("#sel_pedimento_mod_cliente_casa").val(),
			cliente: $("#sel_pedimento_mod_cliente_casa option:selected").text(),
			operacion: 2,
			observaciones: ''//EXPORTACION
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Consultando información, espere un momento por favor...';
			$("#mensaje_mdl_generar_pedimento_ref").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					$("#mensaje_mdl_generar_pedimento_ref").html('');
					
					var sAdu = $("#sel_aduana").val();
					var sPat = $("#sel_pedimento_mod_patente").val();
					var sPed = respuesta.Pedimento;
					if(gebRefExpo == 'factura'){
						$("#txt_pedimento_referencia_mdl").val(sAdu + '-' + sPat + '-' + sPed);
						$('#sel_regimen').select2("trigger", "select", {
								data: {
								   id: $('#sel_pedimento_mod_regimen').val(),
								   text: $("#sel_pedimento_mod_regimen option:selected").text()
								}
							});
						$("#btn_generar_pedimento").prop('disabled',true);
					}else{
						$("#txt_pedimento_asigreffac_mdl").val(sAdu + '-' + sPat + '-' + sPed);
						$("#btn_generar_pedimento_asigref").prop('disabled',true);
					}
					$('#modalgenpedimento').modal('hide');
				}else{
					var strMensaje = respuesta.Mensaje;
					$("#mensaje_mdl_generar_pedimento_ref").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_mdl_generar_pedimento_ref").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_mdl_generar_pedimento_ref").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

/* ///////////////////////////////////////////
	AGREGAR PERMISOS
//////////////////////////////////////////// */

function inicializa_tabla_permisos(){
	var otable = convert_array_table_permisos();
	$('#tbl_permisos').DataTable({
		data: otable,
		"destroy": true,
		responsive: true,
		aLengthMenu: [
			[5, 10, 50, 100, -1],
			[5, 10, 50, 100, "All"]
		],
		iDisplayLength: 5
	});
	
	setTimeout(function () {$('#tbl_permisos').DataTable().columns.adjust().responsive.recalc();},300);
}

function convert_array_table_permisos(){
	var oReturn = new Array();
	for(i = 0; i < aPermisos.length ; i++){
		oPush = [ 
				aPermisos[i].num_aut,
				aPermisos[i].num_adhesion,
				aPermisos[i].eliminar
			]
		oReturn.push(oPush);
	}
	return oReturn
}

function guardar_nuevo_permiso(){
	if(bAviso_Adhesion){
		if($('#sel_aviso_adhesion').val() == '' || $('#sel_aviso_adhesion').val() == null){
			var sMensaje = 'Es necesario seleccionar el aviso de adhesion.';
			$('#mensaje_modal_fac_permisos').html('<div class="alert alert-danger">'+sMensaje+'</div>');
			$('#sel_aviso_adhesion').focus();
			setTimeout(function(){$('#mensaje_modal_fac_permisos').html('');},2000);
			return false;
		}
	}
	if($('#sel_permisos').val() == '' || $('#sel_permisos').val() == null){
		var sMensaje = 'Es necesario seleccionar el aviso autom&aacute;tico.';
		$('#mensaje_modal_fac_permisos').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#sel_permisos').focus();
		setTimeout(function(){$('#mensaje_modal_fac_permisos').html('');},2000);
		return false;
	}
	var paviso_adhesion = '';
	if($('#sel_aviso_adhesion').val() != '' && $('#sel_aviso_adhesion').val() != null){
		paviso_adhesion = $('#sel_aviso_adhesion').val();
	}
	var paviso_automatico = $('#sel_permisos').val();
	
	var pnum_adhesion = '';
	if($('#sel_aviso_adhesion').val() != '' && $('#sel_aviso_adhesion').val() != null){
		pnum_adhesion = $("#sel_aviso_adhesion option:selected").text();
	}
	var pnum_automatico = $("#sel_permisos option:selected").text().split('/')[0];
	
	for(i=0; i<aPermisos.length; i++){
		if(paviso_automatico == aPermisos[i].aviso_aut){
			var sMensaje = 'El aviso ya se encuentra dado de alta.';
			$('#mensaje_modal_fac_permisos').html('<div class="alert alert-danger">'+sMensaje+'</div>');
			inicializa_controles_permisos();
			setTimeout(function(){$('#mensaje_modal_fac_permisos').html('');},2000);
			return false;
		}
	}
	var newItem = {
		aviso_adhesion: paviso_adhesion,
		aviso_aut: paviso_automatico,
		num_adhesion: pnum_adhesion,
		num_aut: pnum_automatico,
		eliminar : tabla_eliminar_permisos_actions(aPermisos.length)
	};
	
	aPermisos.push(newItem);
	
	inicializa_tabla_permisos();
	inicializa_controles_permisos();
}

function inicializa_controles_permisos(){
	/*$("#sel_aviso_adhesion").empty().trigger('change');
	$("#sel_permisos").empty().trigger('change');*/

	$("#sel_aviso_adhesion").val('');
	$("#sel_permisos").val('');
	
	$("#descargar_aviso_adhesion").html('');
	$("#descargar_aviso_automatico").html('');
	
	$('#mensaje_modal_fac_permisos').html('');
}

function tabla_eliminar_permisos_actions(id){
	var sBtnAction ='<a href="javascript:void(0);" onclick="eliminar_permiso(\''+id+'\');return false;" style="padding-left:.5em" title="Eliminar"><i class="glyphicon glyphicon-trash"></i> Eliminar</a>&nbsp;';
	return sBtnAction;
}

function eliminar_permiso(item) {
	nPerm = item;
	$('#modalconfirm_permiso').modal({show: true,backdrop: 'static',keyboard: false});
}

function eliminar_permiso_aceptar(){
	$('#modalconfirm_permiso').modal('hide');
	ajax_eliminar_permiso_cruce();
}

function ajax_eliminar_permiso_cruce(){
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_eliminar_permiso_cruce_expo.php',
		data: {
			id_permiso: nPerm,
			id_detalle_cruce: sIdFactura
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Eliminando información, espere un momento por favor...';
			$("#mensaje_modal_fac_permisos").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				
				if (respuesta.Codigo == '1'){
					var strMensaje = respuesta.Mensaje;
					$("#mensaje_modal_fac_permisos").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					
					aPermisos = respuesta.aPermisos;
					inicializa_tabla_permisos();
					
					setTimeout(function(){$("#mensaje_modal_fac_permisos").html('');},4000);
					
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_modal_fac_permisos").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_modal_fac_permisos").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_modal_fac_permisos").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

/* ///////////////////////////////////////////
	AJAX
//////////////////////////////////////////// */

function valida_controles_cruce_expo(){
	if($('#sel_linea_transportista').val() == '' || $('#sel_linea_transportista').val() == null){
		var sMensaje = 'Es necesario seleccionar la linea de cruce.';
		$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#sel_linea_transportista').focus();
		return false;
	}
	if($('#sel_aduana').val() == '' || $('#sel_aduana').val() == null){
		var sMensaje = 'Es necesario seleccionar la aduana.';
		$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#sel_aduana').focus();
		return false;
	}
	if($('#sel_transfer').val() == '' || $('#sel_transfer').val() == null){
		var sMensaje = 'Es necesario seleccionar el transfer.';
		$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#sel_transfer').focus();
		return false;
	}
	if($('#sel_lugares_entrega').val() == '' || $('#sel_lugares_entrega').val() == null){
		var sMensaje = 'Es necesario seleccionar el lugar de entrega.';
		$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#sel_lugares_entrega').focus();
		return false;
	}
	if($('#chk_consolidar_cruce').prop('checked')){
		if($('#sel_consolidar').val() != null){
			var sMensaje = 'Es necesario presionar el boton [+ Agregar] para seleccionar los clientes a consolidar.';
			$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+sMensaje+'</div>');
			return false;
		}
		if(aCliCons.length == 0){
			var sMensaje = 'Es necesario agregar minimo un cliente para consolidar el cruce.';
			$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+sMensaje+'</div>');
			$('#sel_consolidar').focus();
			return false;
		}
	}
	if(aFacturas.length == 0){
		var sMensaje = 'Es necesario agregar la(s) factura(s).';
		$('#mensaje_modal_cruces').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		return false;
	}
	return true;
}

function guardar_cruce_expo(){
	ajax_editar_cruce_expo();
}

function ajax_eliminar_cruce_expo(){
	$('#modalconfirm_cruce').modal('hide');
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_eliminar_cruce_expo.php',
		data: {id_cruce: sIdCruce},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Eliminando información, espere un momento por favor...';
			$("#mensaje_cruces").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				
				if (respuesta.Codigo == '1'){
					
					var strMensaje = 'El cruce se ha eliminado correctamente!!.';
					$("#mensaje_cruces").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					
					var table = $('#tbl_cruces').DataTable();
					table.ajax.reload();
					
					
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

function ajax_procesar_archivo_cfdi(){
	var xmlCFDI = document.getElementById('upload_cfdi');
		
	if (!xmlCFDI.files[0]){
		var strMensaje = 'El archivo CFDI seleccionado es incorrecto.';
		$("#mensaje_modal_procesar_cfdi").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
		$("#upload_cfdi").fileinput('clear');
		return;
	}
	
	var odata = new FormData();
	odata.append('xmlCFDI', xmlCFDI.files[0]);
	
	$.ajax({
		url: "ajax/cruces_exportacion/ajax_procesar_cfdi_xml.php",
		type: "POST",
		data: odata,
		timeout: 300000,
		contentType: false,
		cache: false,
		processData:false,
		xhr: function()
		{
			var xhr = new window.XMLHttpRequest();
			xhr.upload.addEventListener("progress", function(evt){
			  if (evt.lengthComputable) {
				var percent = evt.loaded / evt.total * 100;
				percent = percent.toFixed(0);
				if(percent > 99) percent = 99;
				var sMen = '<div class="progress progress-striped active">';
				sMen += '		<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
				sMen += '			<span>'+percent+'% completado</span>';
				sMen += '		</div>';
				sMen += '	</div>';
				$('#mensaje_modal_procesar_cfdi').html(sMen);
			  }
			}, false);
			return xhr;
		},
		beforeSend: function(){
			$("#mensaje_modal_procesar_cfdi").html('<div class="alert alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> Procesando xml, espere un momento por favor...</div>');
		},
		success: function(response)
		{
			if(response != '500'){
				respuesta = JSON.parse(response);				
				if(respuesta.Codigo == '1'){
					$("#mensaje_modal_procesar_cfdi").html('');
					varFac = respuesta.serie.toString().trim() + respuesta.folio.toString().trim();
					if(varFac == ''){
						if(respuesta.uuid.length > 20){
							varFac = respuesta.uuid.substring(0, 20);
						}else{
							varFac = respuesta.uuid
						}
					}
					$("#txt_numero_factura_mdl").val(varFac);
					$("#txt_numero_uuid_mdl").val(respuesta.uuid);
					$("#txt_fecha_factura_mdl").val(respuesta.fecha);
				}else{
					var strMensaje = respuesta.Mensaje+respuesta.Error;
					$("#mensaje_modal_procesar_cfdi").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
				}				
			}else{
				$("#mensaje_modal_procesar_cfdi").html('<div class="alert alert-danger alert-dismissible" role="alert">'+aEtiquetas[204-1].strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("logout.php");},6000);
			}
		},
		error: function(a,b){
			alert(a.status + ' [' + a.statusText + ']');
		}
	});
}

/* ////// EDITAR /////// */

function ajax_consulta_cuce_expo(IdCruce,Action){
	gAccion = Action;//Editar / editar / encabezado/ ver
	sIdCruce = IdCruce;
	inicializa_controles_cruce();
	$.ajax({
		type: "POST",
		url: 'ajax/cruces_exportacion/ajax_consulta_cruce_expo.php',
		data: {id_cruce: sIdCruce},
		timeout: 120000,
		beforeSend: function (dataMessage) {
			$('html, body').animate({scrollTop: $("#mensaje_cruces").offset().top}, 2000);
			$("#mensaje_cruces").html('<div class="alert alert-info"><img src="../images/cargando.gif" height="16" width="16"/> Cargando informaci&oacute;n, espere un momento por favor...</div>');
		},
		success:  function (response) {
			$("#mensaje_cruces").html('');
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					sIdCliente = respuesta.numcliente;
					$("#txt_cliente_mdl").val(respuesta.cliente);
					$("#txt_fecha_mdl").val(respuesta.fecha_registro);
					if(respuesta.numlinea != ''){
						$('#sel_linea_transportista').select2("trigger", "select", {
							data: {
							   id: respuesta.numlinea,
							   text: respuesta.nom_lineat
							}
						});
					}
					$("#sel_aduana").val(respuesta.aduana).trigger('change');
					//$("#sel_tipo_salida").val(respuesta.tiposalida).trigger('change');
					//$('#txt_numero_caja').val(respuesta.caja);

					if(respuesta.notransfer){ 
						$('#sel_transfer').select2("trigger", "select", {
							data: {
								id: respuesta.notransfer,
								text: respuesta.nombretransfer
							}
						});
					}
					$("#txt_caat_transfer").val(respuesta.caat);
					$("#txt_scac_transfer").val(respuesta.scac);
					$("#txt_po_number").val(respuesta.po_number);
					if(respuesta.noentrega){
						$('#sel_lugares_entrega').select2("trigger", "select", {
							data: {
							   id: respuesta.noentrega,
							   text: respuesta.nombreentrega
							}
						});
					}
					$("#txt_direccion_entrega").val(respuesta.direntrega);
					$("#txt_indicaciones").val(respuesta.indicaciones);
					$("#txt_observaciones_cruce").val(respuesta.observaciones);
					
					if(respuesta.aCliConsolidar.length > 0){
						$("#sel_consolidar").empty().trigger('change');
						$('#chk_consolidar_cruce').prop('checked',true);
						aCliCons = respuesta.aCliConsolidar;
						inicializar_tabla_clientes_consolidar();
					}else{
						$("#sel_consolidar").empty().trigger('change');
						$('#chk_consolidar_cruce').prop('checked',false);
					}
					
					switch(gAccion){
						case 'Editar':
							deshabilita_contoles_cruce(false);
							break;
						case 'ver':
							deshabilita_contoles_cruce(true);
							break;
						case 'editar_encabezado':
							deshabilita_contoles_encabezado();
							break;
					}
					
					aFacturas = respuesta.aFacturas;					
					inicializa_tabla_facturas();
					$('#modal_estado').modal({show: true,backdrop: 'static',keyboard: false});
					
				}else{
					var strMensaje = respuesta.Mensaje + respuesta.Error;
					$('#modalmessagebox_error_titulo').html('Error :: Seleccionar Cruces');
					$('#modalmessagebox_error_mensaje').html(strMensaje);
					$('#modalmessagebox_error').modal({show: true,backdrop: 'static',keyboard: false});
				}
			}else{
				alert('La sesion del usuario ha finalizado, es necesario iniciar nuevamente.');					
				setTimeout(function () {window.location.replace('../logout.php');},4000);
			}				
		},
		error: function(a,b){
			$("#mensaje_cruces").html('');
			var strMensaje = a.status+' [' + a.statusText + ']';
			alert(strMensaje);
		}
	});
}

function deshabilita_contoles_cruce(bVal){
	$("#sel_linea_transportista").prop('disabled', bVal);
	$("#sel_aduana").prop('disabled', bVal);
	$("#sel_tipo_salida").prop('disabled', bVal);
	$("#txt_numero_caja").prop('disabled', bVal);
	$("#sel_transfer").prop('disabled', bVal);
	$("#txt_caat_transfer").prop('disabled', bVal);
	$("#txt_scac_transfer").prop('disabled', bVal);
	$("#txt_po_number").prop('disabled', bVal);
	$("#sel_lugares_entrega").prop('disabled', bVal);
	$("#txt_direccion_entrega").prop('disabled', bVal);
	$("#txt_indicaciones").prop('disabled', bVal);
	
	$("#txt_observaciones_cruce").prop('disabled', bVal);
	
	$('#chk_consolidar_cruce').prop('disabled', bVal);
	if($('#chk_consolidar_cruce').prop('checked') && bVal == false){
		$('#sel_consolidar').prop('disabled', false);
	}else{
		$('#sel_consolidar').prop('disabled', true);
	}
	$("#btn_nueva_factura_cruce").prop('disabled', bVal);
	$("#btn_guardar_cruce").prop('disabled', bVal);
}

function deshabilita_contoles_encabezado(){
	$("#sel_linea_transportista").prop('disabled', false);
	$("#sel_aduana").prop('disabled', false);
	$("#sel_tipo_salida").prop('disabled', false);
	$("#txt_numero_caja").prop('disabled', false);
	$("#sel_transfer").prop('disabled', false);
	$("#txt_caat_transfer").prop('disabled', false);
	$("#txt_scac_transfer").prop('disabled', false);
	$("#txt_po_number").prop('disabled', false);
	$("#sel_lugares_entrega").prop('disabled', false);
	$("#txt_direccion_entrega").prop('disabled', false);
	$("#txt_indicaciones").prop('disabled', false);
	
	$('#chk_consolidar_cruce').prop('disabled', false);
	if($('#chk_consolidar_cruce').prop('checked')){
		$('#sel_consolidar').prop('disabled', false);
	}else{
		$('#sel_consolidar').prop('disabled', true);
	}
	
	$("#btn_nueva_factura_cruce").prop('disabled', true);
	$("#btn_guardar_cruce").prop('disabled', false);
}

function ajax_editar_cruce_expo(){
	
	if(!valida_controles_cruce_expo()){
		return;
	}
	var bConsolidar = $('#chk_consolidar_cruce').prop('checked');
	var odata = {
		id_cruce: sIdCruce,
		id_lineat: $("#sel_linea_transportista").val(),
		aduana:  $("#sel_aduana").val(),
		tipo_salida: $("#sel_tipo_salida").val(),
		numero_caja: $("#txt_numero_caja").val().trim().toUpperCase(),
		id_transfer: $("#sel_transfer").val(),
		caat: $("#txt_caat_transfer").val().trim().toUpperCase(),
		scac: $("#txt_scac_transfer").val().trim().toUpperCase(),
		po_number: $("#txt_po_number").val().trim().toUpperCase(),
		id_entregar: $("#sel_lugares_entrega").val(),
		nom_entregar: $("#sel_lugares_entrega option:selected").text().trim().toUpperCase(),
		dir_entregar: $("#txt_direccion_entrega").val().trim().toUpperCase(),
		indicaciones: $("#txt_indicaciones").val().trim().toUpperCase(),
		observaciones: $("#txt_observaciones_cruce").val().trim().toUpperCase(),
		aCliConsolidar: JSON.stringify(aCliCons)
	};
	$.ajax({
		url:   'ajax/cruces_exportacion/ajax_editar_cruce_expo.php',
		type:  'post',
		data:	odata,
		timeout: 120000,
		beforeSend: function () {
			var strMensaje = 'Guardando información, espere un momento por favor...';
			$("#mensaje_modal_cruces").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if(response != '500'){
				respuesta = JSON.parse(response);
				if (respuesta.Codigo=='1'){
					$("#mensaje_modal_cruces").html('');
					var strMensaje = 'El cruce se ha actualizado correctamente!!.';
					$("#mensaje_cruces").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					inicializa_controles_cruce();
					
					var table = $('#tbl_cruces').DataTable();
					table.ajax.reload();
					
					$('#modal_estado').modal('hide');
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_modal_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_modal_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_modal_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

function ajax_habilitar_editar_cuce_expo(pId){
	$.ajax({
		type: "POST",
		timeout: 20000,
		url:   'ajax/cruces_exportacion/ajax_habilitar_editar_cruce_expo.php',
		data: {
			id_cruce: pId
		},
		beforeSend: function (dataMessage) {
			$('html, body').animate({scrollTop: $("#mensaje_cruces").offset().top}, 2000);
			var strMensaje = 'Habilitando cruce, espere un momento por favor...';
			$("#mensaje_cruces").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				
				if (respuesta.Codigo == '1'){
					var strMensaje = respuesta.Mensaje;
					$("#mensaje_cruces").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					var table = $('#tbl_cruces').DataTable();
					table.ajax.reload();
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

//ELIMNAR CRUCE

function eliminar_cruce_expo(id_cruce){
	sIdCruce = id_cruce;
	$('#modalconfirm_cruce').modal({show: true,backdrop: 'static',keyboard: false});
}

function get_options_select(select_option){
	var oData = {
		theme: "bootstrap",
		width: "off",
		placeholder: "[SELECCIONAR]",
		ajax: {
			url: 'ajax/cruces_exportacion/ajax_select2_filtros.php',
			type: "POST",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term,
					action: select_option,
					num_cliente: sIdCliente
				};
			},
			processResults: function (data, page) {
			  return {
				results: data.items
			  };
			},
			cache: false,
			minimumInputLength: 1
		},
		dropdownParent: $('#modal_estado')//Se debe seleccionar la modal a la que pertence para que funcione en FIREFOX
	};
	return oData;
}

function get_options_select_facturas(select_option){
	var oData = {
		theme: "bootstrap",
		width: "off",
		placeholder: "[SELECCIONAR]",
		ajax: {
			url: 'ajax/cruces_exportacion/ajax_select2_filtros.php',
			type: "POST",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term,
					action: select_option,
					num_cliente: sIdCliente
				};
			},
			processResults: function (data, page) {
			  return {
				results: data.items
			  };
			},
			cache: false,
			minimumInputLength: 1
		},
		dropdownParent: $('#modal_factura')//Se debe seleccionar la modal a la que pertence para que funcione en FIREFOX
	};
	return oData;
}

function get_options_select_gen_pedimento(select_option){
	var oData = {
		theme: "bootstrap",
		width: "off",
		placeholder: "[SELECCIONAR]",
		ajax: {
			url: 'ajax/cruces_exportacion/ajax_select2_filtros.php',
			type: "POST",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term,
					action: select_option,
					num_cliente: sIdCliente
				};
			},
			processResults: function (data, page) {
			  return {
				results: data.items
			  };
			},
			cache: false,
			minimumInputLength: 1
		},
		dropdownParent: $('#modalgenpedimento')//Se debe seleccionar la modal a la que pertence para que funcione en FIREFOX
	};
	return oData;
}

/*function get_options_select(select_option){
	var oData = {
		theme: "bootstrap",
		width: "off",
		placeholder: "[SELECCIONAR]",
		ajax: {
			url: 'ajax/cruces_exportacion/ajax_select2_filtros.php',
			type: "POST",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term,
					action: select_option
				};
			},
			processResults: function (data, page) {
			  return {
				results: data.items
			  };
			},
			cache: false,
			minimumInputLength: 1
		}
	};
	return oData;
}*/

function mostrar_etiquete_contenedor(pOpt){
	switch(pOpt){
		case '':
			$('#lbl_contenedor').html('');
			break;
		case 'CAJA':
			$('#lbl_contenedor').html('Numero Caja');
			break;
		case 'PLATAFORMA':
			$('#lbl_contenedor').html('Numero Plataforma');
			break;
		case 'PLACAS':
			$('#lbl_contenedor').html('Numero Placas');
			break;
		case 'FURGON':
			$('#lbl_contenedor').html('Numero Furgon');
			break;
		case 'GONDOLA':
			$('#lbl_contenedor').html('Numero Gondola');
			break;
		case 'CARRO':
			$('#lbl_contenedor').html('Numero Carro');
			break;
	}
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *		UNIFICAR DOCUMENTOS del Cruce en un solo PDF			*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

function ajax_consultar_documentacion_cruce(IdCruce){
	sIdCruce = IdCruce;
	var oData = {id_cruce: sIdCruce};
	$.ajax({
		type: "POST",
		timeout: 120000,
		url: 'ajax/cruces_exportacion/ajax_consulta_documentos_cruce.php',
		data: oData,
		beforeSend: function (dataMessage) {
			$('html, body').animate({scrollTop: $("#mensaje_cruces").offset().top}, 2000);
			$("#mensaje_cruces").html('<div class="alert alert-info"><img src="../images/cargando.gif" height="16" width="16"/> Cargando informaci&oacute;n, espere un momento por favor...</div>');
		},
		success:  function (response) {
			$("#mensaje_cruces").html('');
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					$('#div_copias_documentos').hide();
					aFacImp = respuesta.aFacturas;					
					inicializa_tabla_facturas_impresion();
					$('#modal_PDF_Imprimir').modal({show: true,backdrop: 'static',keyboard: false});
				}else{
					var strMensaje = respuesta.Mensaje + respuesta.Error;
					$('#modalmessagebox_error_titulo').html('Error :: Seleccionar Cruces');
					$('#modalmessagebox_error_mensaje').html(strMensaje);
					$('#modalmessagebox_error').modal({show: true,backdrop: 'static',keyboard: false});
				}
			}else{
				alert('La sesion del usuario ha finalizado, es necesario iniciar nuevamente.');					
				setTimeout(function () {window.location.replace('../logout.php');},4000);
			}				
		},
		error: function(a,b){
			$("#mensaje_cruces").html('');
			var strMensaje = a.status+' [' + a.statusText + ']';
			alert(strMensaje);
		}
	});
}

function inicializa_tabla_facturas_impresion(){
	var otable = convert_array_table_impresion();
	$('#tbl_facimpr').DataTable({
		data: otable,
		"destroy": true,
		responsive: true,
		aLengthMenu: [
			[5, 10, 50, 100, -1],
			[5, 10, 50, 100, "All"]
		],
		iDisplayLength: 5
	});
	
	setTimeout(function () {$('#tbl_facimpr').DataTable().columns.adjust().responsive.recalc();},300);
}

function convert_array_table_impresion(){
	var oReturn = new Array();
	for(i = 0; i < aFacImp.length ; i++){		
		var archFactura = crear_link_ver_archivo(aFacImp[i].archivo_factura.split('/')[aFacImp[i].archivo_factura.split('/').length-1],aFacImp[i].archivo_factura);
		var archCFDI = crear_link_ver_archivo(aFacImp[i].archivo_cfdi.split('/')[aFacImp[i].archivo_cfdi.split('/').length-1],'https://www.delbravoweb.com/sii/admin/ajax/cruces/descargar_cfdi_pdf.php?icd='+aFacImp[i].id_detalle_cruce+'&sxml=1');
		var archPacking = crear_link_ver_archivo(aFacImp[i].archivo_packinglist.split('/')[aFacImp[i].archivo_packinglist.split('/').length-1],aFacImp[i].archivo_packinglist);
		var archCert = crear_link_ver_archivo(aFacImp[i].archivo_cert_origen.split('/')[aFacImp[i].archivo_cert_origen.split('/').length-1],aFacImp[i].archivo_cert_origen);
		var archTicket = crear_link_ver_archivo(aFacImp[i].archivo_ticketbascula.split('/')[aFacImp[i].archivo_ticketbascula.split('/').length-1],aFacImp[i].archivo_ticketbascula);
		var archAviso = crear_link_ver_archivo(aFacImp[i].archivo_permiso.split('/')[aFacImp[i].archivo_permiso.split('/').length-1],aFacImp[i].archivo_permiso);
		var archAdhesion = crear_link_ver_archivo(aFacImp[i].archivo_permiso_adhesion.split('/')[aFacImp[i].archivo_permiso_adhesion.split('/').length-1],aFacImp[i].archivo_permiso_adhesion);
		
		oPush = [ 
				aFacImp[i].numero_factura,
				aFacImp[i].copias_factura,
				archFactura,
				aFacImp[i].copias_cfdi,
				archCFDI,
				aFacImp[i].copias_packinglist,
				archPacking,
				aFacImp[i].copias_cert_origen,
				archCert,
				aFacImp[i].copias_ticketbascula,
				archTicket,
				aFacImp[i].copias_permiso,
				archAviso,
				aFacImp[i].copias_permiso_adhesion,
				archAdhesion,
				crear_link_seleccionar_factura(i)
			];
		oReturn.push(oPush);
	}
	return oReturn
}

function crear_link_seleccionar_factura(pItem){
	return '<a href="javascript:void(0);" onclick="mostrar_copias_archivos_impresion(\''+pItem+'\');return false;" style="padding-left:.5em;" title="">[<i class="fa fa-pencil" aria-hidden="true"></i> Modificar Copias]</a>';
}

function crear_link_ver_archivo(texto,url){
	return '<a href="' + url + '" target="_blank" style="padding-left:.5em;" title="">' + texto + '</a>'; 
}

function mostrar_copias_archivos_impresion(pItem){
	nItem = pItem;
	$('#lbl_titulo_copias_factura').html(aFacImp[nItem].numero_factura)
	$('#txt_numero_factura_impr_mdl').val(aFacImp[nItem].archivo_factura.split('/')[aFacImp[nItem].archivo_factura.split('/').length-1]);
	$('#txt_numero_factura_impr_mdl_copias').val(aFacImp[nItem].copias_factura);
	$('#txt_cfdi_impr_mdl').val(aFacImp[nItem].archivo_cfdi.split('/')[aFacImp[nItem].archivo_cfdi.split('/').length-1]);
	$('#txt_cfdi_impr_mdl_copias').val(aFacImp[nItem].copias_cfdi);
	$('#txt_packing_impr_mdl').val(aFacImp[nItem].archivo_packinglist.split('/')[aFacImp[nItem].archivo_packinglist.split('/').length-1]);
	$('#txt_packing_impr_mdl_copias').val(aFacImp[nItem].copias_packinglist);
	$('#txt_packing_impr_mdl_copias').prop('disabled', (aFacImp[nItem].archivo_packinglist.trim() == '' ? true : false));
	$('#txt_certificado_impr_mdl').val(aFacImp[nItem].archivo_cert_origen.split('/')[aFacImp[nItem].archivo_cert_origen.split('/').length-1]);
	$('#txt_certificado_impr_mdl_copias').val(aFacImp[nItem].copias_cert_origen);
	$('#txt_certificado_impr_mdl_copias').prop('disabled', (aFacImp[nItem].archivo_cert_origen.trim() == '' ? true : false));
	$('#txt_ticket_impr_mdl').val(aFacImp[nItem].archivo_ticketbascula.split('/')[aFacImp[nItem].archivo_ticketbascula.split('/').length-1]);
	$('#txt_ticket_impr_mdl_copias').val(aFacImp[nItem].copias_ticketbascula);
	$('#txt_ticket_impr_mdl_copias').prop('disabled', (aFacImp[nItem].archivo_ticketbascula.trim() == '' ? true : false));
	$('#txt_permiso_impr_mdl').val(aFacImp[nItem].archivo_permiso.split('/')[aFacImp[nItem].archivo_permiso.split('/').length-1]);
	$('#txt_permiso_impr_mdl_copias').val(aFacImp[nItem].copias_permiso);
	$('#txt_permiso_impr_mdl_copias').prop('disabled', (aFacImp[nItem].archivo_permiso.trim() == '' ? true : false));
	$('#txt_adhesion_impr_mdl').val(aFacImp[nItem].archivo_permiso_adhesion.split('/')[aFacImp[nItem].archivo_permiso_adhesion.split('/').length-1]);
	$('#txt_adhesion_impr_mdl_copias').val(aFacImp[nItem].copias_permiso_adhesion);
	$('#txt_adhesion_impr_mdl_copias').prop('disabled', (aFacImp[nItem].archivo_permiso_adhesion.trim() == '' ? true : false));
	$('#div_copias_documentos').show("slow");
	$('#modal_PDF_Imprimir').animate({scrollTop: $("#div_copias_documentos").offset().top}, 2000);
}

function guardar_copias_documentos_factura(){
	
	if($('#txt_numero_factura_impr_mdl_copias').val().trim() == ''){
		$('#mensaje_guardar_copias_impresion').html('<div class="alert alert-danger">Es necesario agregar el n&uacute;mero de copias de la factura.</div>');
		$('#txt_numero_factura_impr_mdl_copias').focus();
		return;
	}
	if($('#txt_cfdi_impr_mdl_copias').val().trim() == ''){
		$('#mensaje_guardar_copias_impresion').html('<div class="alert alert-danger">Es necesario agregar el n&uacute;mero de copias del CFDI.</div>');
		$('#txt_cfdi_impr_mdl_copias').focus();
		return;
	}
	if($('#txt_packing_impr_mdl_copias').val().trim() == ''){
		$('#mensaje_guardar_copias_impresion').html('<div class="alert alert-danger">Es necesario agregar el n&uacute;mero de copias del packing list.</div>');
		$('#txt_packing_impr_mdl_copias').focus();
		return;
	}
	if($('#txt_certificado_impr_mdl_copias').val().trim() == ''){
		$('#mensaje_guardar_copias_impresion').html('<div class="alert alert-danger">Es necesario agregar el n&uacute;mero de copias del certificado de origen.</div>');
		$('#txt_certificado_impr_mdl_copias').focus();
		return;
	}
	if($('#txt_ticket_impr_mdl_copias').val().trim() == ''){
		$('#mensaje_guardar_copias_impresion').html('<div class="alert alert-danger">Es necesario agregar el n&uacute;mero de copias del ticket.</div>');
		$('#txt_ticket_impr_mdl_copias').focus();
		return;
	}
	if($('#txt_permiso_impr_mdl_copias').val().trim() == ''){
		$('#mensaje_guardar_copias_impresion').html('<div class="alert alert-danger">Es necesario agregar el n&uacute;mero de copias del aviso autom&aacute;tico.</div>');
		$('#txt_permiso_impr_mdl_copias').focus();
		return;
	}
	if($('#txt_adhesion_impr_mdl_copias').val().trim() == ''){
		$('#mensaje_guardar_copias_impresion').html('<div class="alert alert-danger">Es necesario agregar el n&uacute;mero de copias del aviso de adhesi&oacute;n.</div>');
		$('#txt_adhesion_impr_mdl_copias').focus();
		return;
	}
	
	aFacImp[nItem].copias_factura = $('#txt_numero_factura_impr_mdl_copias').val();
	aFacImp[nItem].copias_cfdi = $('#txt_cfdi_impr_mdl_copias').val();
	aFacImp[nItem].copias_packinglist = $('#txt_packing_impr_mdl_copias').val();
	aFacImp[nItem].copias_cert_origen = $('#txt_certificado_impr_mdl_copias').val();
	aFacImp[nItem].copias_ticketbascula = $('#txt_ticket_impr_mdl_copias').val();
	aFacImp[nItem].copias_permiso = $('#txt_permiso_impr_mdl_copias').val();
	aFacImp[nItem].copias_permiso_adhesion = $('#txt_adhesion_impr_mdl_copias').val();
	
	inicializa_tabla_facturas_impresion();
	
	$('#div_copias_documentos').hide("slow");
}

function cancelar_copias_documentos_factura(){
	$('#div_copias_documentos').hide("slow");
}

function generar_pdf_unico_impresion_cruce(){
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_unificar_pdf_cruce_facturas.php',
		data: {aCopias: JSON.stringify(aFacImp)},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Eliminando información, espere un momento por favor...';
			$("#mensaje_mdl_impresion_documentos").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					$("#mensaje_mdl_impresion_documentos").html('');
					$('#modal_PDF_Imprimir').modal('hide');
					window.open('https://www.delbravoweb.tk/monitor/panel/ajax/cruces_exportacion/unificar_pdf_cruce_facturas.php', '_blank');					
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_mdl_impresion_documentos").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_mdl_impresion_documentos").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_mdl_impresion_documentos").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *		ASIGNAR REFERENCIAS A FACTURAS			*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

function inicializar_table_facturas_asigref(){
	var otable = convert_array_table_asigreffac();
	table_asigfac = $('#table_facasigref').DataTable({
		data: otable,
		"destroy": true,
		responsive: true,
		aLengthMenu: [
			[-1],
			["All"]
		],
		iDisplayLength: -1
	});
	
	$('#table_facasigref tbody').on('click', 'td',function(e){		
		var current_row = $(this).parents('tr');
		if (current_row.hasClass('child')) {//Check if the current row is a child row
			current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
		}
		
		var cell_clicked    = table_asigfac.cell(this).index();
		var oRow  = table_asigfac.row(current_row).data();
		if (typeof(cell_clicked) != 'undefined') {
			if(cell_clicked.column != 0){
				var id = oRow[0];//id_factura
				var index = $.inArray(id, sel_facturas);
				if ( index === -1 ) {
					sel_facturas.push( id );
					/*$(this).closest('tr').css({"background-color":"#0088cc"});
					$(this).closest('tr').css({"color":"#FFF"});*/
				}else{
					sel_facturas.splice( index, 1 );
					/*$(this).closest('tr').css({"background-color":"#FFF"});
					$(this).closest('tr').css({"color":"#000"});*/
				}
				$('#lbl_facturas_sel').html(sel_facturas.length);
				$(this).closest('tr').toggleClass('selected');
			}
		}
	});
	
	setTimeout(function () {$('#table_facasigref').DataTable().columns.adjust().responsive.recalc();},300);
}

function convert_array_table_asigreffac(){
	var oReturn = new Array();
	for(i = 0; i < aFacturas.length ; i++){
		
		oPush = [
				aFacturas[i].id,
				aFacturas[i].numero_factura,
				aFacturas[i].referencia,
				aFacturas[i].uuid
			]
		oReturn.push(oPush);
	}
	return oReturn
}
 
function asignar_referencias_factura(){
	gebRefExpo = 'asig_ref_fac';
	selected = [];
	
	$("#txt_referencia_asigreffac_mdl").val('');
	$("#txt_pedimento_asigreffac_mdl").val('');
	
	$("#btn_generar_pedimento_asigref").prop('disabled',true);
	$('#lbl_facturas_sel').html(selected.length);
	inicializar_table_facturas_asigref();
	$('#modalasigreferencias').modal({show: true,backdrop: 'static',keyboard: false});
}
 
function ajax_asignar_referencia_factura(pOpt){
	if($("#txt_referencia_asigreffac_mdl").val().trim() == ''){
		var sMensaje = 'Es necesario agregar la referencia.';
		$('#mensaje_asigreffac_modal').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		$('#txt_referencia_asigreffac_mdl').focus();
		return false;
	}
	if(sel_facturas.length == 0 && pOpt == 'sel'){
		var sMensaje = 'Es necesario seleccionar las referencias a las que desea asignar la referencia.';
		$('#mensaje_asigreffac_modal').html('<div class="alert alert-danger">'+sMensaje+'</div>');
		return false;
	}
	$("#btn_generar_pedimento_asigref").prop('disabled',true);
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_asignar_referencia_facturas.php',
		data: {
			referencia: $("#txt_referencia_asigreffac_mdl").val().toUpperCase().trim(),
			opcion: pOpt, //Todas las facturas o las seleccionadas
			id_cruce: sIdCruce,
			seleccionadas: JSON.stringify(sel_facturas)
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Procesando información, espere un momento por favor...';
			$("#mensaje_asigreffac_modal").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					if(respuesta.pedimento == ''){
						$("#btn_generar_pedimento_asigref").prop('disabled',false);
					}
					$("#txt_pedimento_asigreffac_mdl").val(respuesta.pedimento);
					
					var strMensaje = 'La referencia se asigno correctamente a la(s) factura(s)!!.';
					$("#mensaje_asigreffac_modal").html('<div class="alert alert-success alert-dismissible" role="alert">'+strMensaje+'</div>');
					
					sel_facturas = [];
					$('#lbl_facturas_sel').html(sel_facturas.length);
					aFacturas = respuesta.aFacturas;
					inicializa_tabla_facturas();
					inicializar_table_facturas_asigref();
					
					actualiza_grid_cruces();
					
					setTimeout(function(){$("#mensaje_asigreffac_modal").html('');},3000);
				}else{
					if (respuesta.Codigo == '2'){
						
						$('#modalconfirm_genreferencia').modal({show: true,backdrop: 'static',keyboard: false});
					}else{
						var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
						$("#mensaje_asigreffac_modal").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
					}
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_asigreffac_modal").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_asigreffac_modal").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}
 
function ajax_asignar_referencias_factura(idCruce){
	sIdCruce = idCruce;
	$.ajax({
		type: "POST",
		timeout: 120000,
		url: 'ajax/cruces_exportacion/ajax_consulta_cruce_expo.php',
		data: {
			id_cruce: sIdCruce
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Procesando información, espere un momento por favor...';
			$("#mensaje_cruces").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if (respuesta.Codigo == '1'){
					$("#mensaje_cruces").html('');
					sIdCliente = respuesta.numcliente;
					sAduana = respuesta.aduana;
					aFacturas = respuesta.aFacturas;
					gebRefExpo = 'asig_ref_fac';
					selected = [];
					$("#txt_referencia_asigreffac_mdl").val('');
					$("#txt_pedimento_asigreffac_mdl").val('');
					$("#btn_generar_pedimento_asigref").prop('disabled',true);
					$('#lbl_facturas_sel').html(selected.length);
					inicializar_table_facturas_asigref();
					$('#modalasigreferencias').modal({show: true,backdrop: 'static',keyboard: false});
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
					
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *					ESTADOS SOIA							   *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

function show_tabla_soia_estados(id_cruce,pedimento){
	
	/*$('#txt_pedimento_soia').val(pedimento);
	sPedimento = pedimento;
	sIdCruce = id_cruce;
	
	$("#modal_estado_detalle_soia").html('');
	
	var table = $('#dtestatus').DataTable();
	table.ajax.reload(null, true);
	
	$('#modalestados').modal({show: true,backdrop: 'static',keyboard: false});
	*/
}

function inicializa_tabla_soia_detalle(){
	/*$('#dtestatus').DataTable({
		bSort: false,
		processing: true,
		serverSide: true,
		ajax: {
			"url": "ajax/soiaEstatus/postEstatus_cruces.php",
			"type": "POST",
			"timeout": 20000,
			"data": function ( d ) {
				d.pedimento = sPedimento;
				d.id_cruce = sIdCruce;
			},
			"error": function (a,b){
						alert(a.statusText);
					}
		},
		columns: [ 
			{ data: "pedimento"},	
			{ data: "num_refe"},
			{ data: "remesa"},
			{ data: "estado_actual"},
			{ 
				data: "evento",
				className: "text-center",
				render: function ( data, type, row ) { 
					if (type == 'display') {
						var sHtml = '';
						if (data != '' && data != null) {
							var aEvento = data.split("-");
							if (aEvento.length >= 3) {
								if (aEvento[0] == 310 || aEvento[0] == 510){
									sHtml = '<span class="label label-danger">' + aEvento[1] + ' ' + aEvento[2] + '</span>';
								} else if (aEvento[0] == 320 || aEvento[0] == 520) {
									sHtml = '<span class="label label-success">' + aEvento[1] + ' ' + aEvento[2] + '</span>';
								}
							}
							
						}
						
						return sHtml;
					} else {
						return data;
					}
				}
			},
			{
				data: null,
				className: "text-center",
				render: function ( data, type, row ) { 
					if (type == 'display') {
						var sHtml = '';
						sHtml += '<a href="javascript:void(0);" onclick="consultar_detalle_estado_soia(\''+row.id_sit_pedime+'\'); return false;">';
						sHtml += '   <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>';
						sHtml += '</a>';
						
						return sHtml;
					} else {
						return data;
					}
				}
			}
		],
		responsive: false,
		aLengthMenu: [
			[10, 25, 50, 100, 200, -1],
			[10, 25, 50, 100, 200, "All"]
		],
		iDisplayLength: -1,
		language: {
			sProcessing: '<img src="../images/cargando.gif" height="18" width="18"> Cargando, espera un momento por favor...',
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
			}
		}
	});*/
}

function consultar_detalle_estado_soia(id_sit_pedime){
	sIdSitePedimento = id_sit_pedime;
	$('#modalestados').modal({show: true,backdrop: 'static',keyboard: false});
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/soiaEstatus/ajax_get_detalles.php',
		data: {
			sIdSitPedime:sIdSitePedimento
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Consultando información, espere un momento por favor...';
			$("#modal_estado_detalle_soia").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				
				if (respuesta.Codigo == '1'){
					
					$("#modal_estado_detalle_soia").html(respuesta.sTable);
				}else{
					var strMensaje = respuesta.Mensaje;
					$("#modal_estado_detalle_soia").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#modal_estado_detalle_soia").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#modal_estado_detalle_soia").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *					CREAR SALIDA CRUCES						   *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */ 

 function crear_nueva_salida_cruces(){
	if(selected.length == 0){
		$('#modalmessagebox_error_titulo').html('Error :: Seleccionar Cruces');
		$('#modalmessagebox_error_mensaje').html('Es necesario seleccionar los cruces que formaran la salida.');
		$('#modalmessagebox_error').modal({show: true,backdrop: 'static',keyboard: false});
		return;
	}
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_crear_salida_cruces.php',
		data: {
			cruces: JSON.stringify(selected)
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Procesando información, espere un momento por favor...';
			$("#mensaje_cruces").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				$("#mensaje_cruces").html('');
				var respuesta = JSON.parse(response);
				switch(eval(respuesta.Codigo)){
					case 1:
						$("#mensaje_cruces").html('');
						selected = [];
						window.open("salidaExpo.php?cruces=1",'_blank');
						actualiza_grid_cruces();
						break;
					case 2:
						$('#modalmessagebox_dif_mensaje').html(respuesta.Mensaje);
						$('#modalmessagebox_dif').modal({show: true,backdrop: 'static',keyboard: false});
						break;
					case 3:
						$('#mensaje_confirmar_salida').html(respuesta.Mensaje);
						$('#modalconfirmsalida').modal({show: true,backdrop: 'static',keyboard: false});
						break
					case 4:
						//aFacNoCasa
						aFacCASA = respuesta.aFacNoCasa;
						mostrar_facturas_sin_vinculacion_casa();
						break;
					default:
						var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
						$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
						$('html, body').animate({scrollTop: $("#mensaje_cruces").offset().top}, 500);
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
 }
 
 function mostrar_facturas_sin_vinculacion_casa(){
	var sHTML = '<table class="table" width="100%" style="background:#FFF;">';
	sHTML += '		<thead style="background-color:#3071AA; color:#FFF;">';
	sHTML += '			<tr>';
	sHTML += '				<th>Cruce</th>';
	sHTML += '				<th>Numero_Factura</th>';
	sHTML += '				<th>Referencia</th>';
	sHTML += '				<th>Acciones</th>';
	sHTML += '			</tr>';
	sHTML += '		</thead>';
	sHTML += '		<tbody>';
	for(i=0; i<aFacCASA.length; i++){
		sHTML += '		<tr>';
		sHTML += '			<td>' + aFacCASA[i].id_cruce + '</td>';
		sHTML += '			<td>' + aFacCASA[i].numero_factura + '</td>';
		sHTML += '			<td>' + aFacCASA[i].referencia + '</td>';
		sHTML += '			<td><a href="javascript:void(0);" onclick="ajax_consultar_facturas_casa_asignar(\''+aFacCASA[i].id_detalle_cruce+'\',\''+aFacCASA[i].referencia+'\',\''+i+'\',\''+aFacCASA[i].numero_factura+'\');return false;" ><i class="fa fa-link" aria-hidden="true"></i> Vincular Factura CASA</a></td>';//Parametros Id_Detalle_Cruce,Referencia(traer facturas que no estan en cruces),i (eliminar del array FacCASA y repintar)
		sHTML += '		</tr>';
	}
	sHTML += '		</tbody>';
	sHTML += '</table>';
	$('#div_facturas_vincular').html(sHTML);
	$('#modal_asigfac_casa').modal({show: true,backdrop: 'static',keyboard: false});
 }
 
 function ajax_consultar_facturas_casa_asignar(pIdDetalle,pReferencia,pItemArrFac,pNumFact){
	IdDetCruce = pIdDetalle;nItemArrFac = pItemArrFac;
	sReferencia = pReferencia;sNumFact = pNumFact;
	
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_consultar_facturas_casa_nocruces.php',
		data: {
			referencia: sReferencia
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Procesando información, espere un momento por favor...';
			$("#mensaje_mld_asignar_facturas_casa").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if(respuesta.Codigo == '1'){
					$("#mensaje_mld_asignar_facturas_casa").html('');
					//Mostrar modal con referencias de CASA
					$("#txt_mdl_referencia_selfac_casa").val(sReferencia);
					$("#txt_mdl_factura_selfac_casa").val(sNumFact);
					$("#sel_facturas_casa").html(respuesta.optsFacturas);
					$('#modal_selfaccasa').modal({show: true,backdrop: 'static',keyboard: false});
					
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_mld_asignar_facturas_casa").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
					$('html, body').animate({scrollTop: $("#mensaje_cruces").offset().top}, 500);
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_mld_asignar_facturas_casa").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_mld_asignar_facturas_casa").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
 }
 
 function ajax_asignar_factura_casa(){
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_asignar_factura_casa_cruce.php',
		data: {
			id_detalle_cruce: IdDetCruce,
			cons_fact: $("#sel_facturas_casa").val()
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Procesando información, espere un momento por favor...';
			$("#mensaje_mdl_selfac_casa").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if(respuesta.Codigo == '1'){
					$("#mensaje_mdl_selfac_casa").html('');
					//Mostrar modal con referencias de CASA
					
					aFacCASA.splice(nItemArrFac,1);
					if(aFacCASA.length == 0){
						$('#modal_asigfac_casa').modal('hide');
					}else{
						mostrar_facturas_sin_vinculacion_casa();
					}
					$('#modal_selfaccasa').modal('hide');
					
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_mdl_selfac_casa").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
					$('html, body').animate({scrollTop: $("#mensaje_cruces").offset().top}, 500);
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_mdl_selfac_casa").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_mdl_selfac_casa").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
 }
 
 function continuar_salidas_confirmacion(){
	$("#mensaje_cruces").html('');
	selected = [];
	window.open("salidaExpo.php?cruces=1",'_blank');
	$('#modalconfirmsalida').modal('hide');
	actualiza_grid_cruces();
 }
 
 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *		GENERAR PLANTILLA AVANZADA 5 VARIOS CRUCES			   *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */ 
 
function ajax_consultar_cruces_plantilla_avanzada(){
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_consultar_cruces_plantilla_avanzada.php',
		beforeSend: function (dataMessage) {
			var strMensaje = 'Consultando información, espere un momento por favor...';
			$("#mensaje_cruces").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if(respuesta.Codigo == '1'){
					$("#mensaje_cruces").html('');
					aCrucesPlantilla = respuesta.aCruces;
					inicializa_tabla_cruces_plantilla_avanzada5();
					$('#modal_cruces_plantilla').modal({show: true,backdrop: 'static',keyboard: false});
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
					$('html, body').animate({scrollTop: $("#mensaje_cruces").offset().top}, 500);
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}
 
function inicializa_tabla_cruces_plantilla_avanzada5(){
	var otable = convert_array_table_cruces_plantilla_vanzada5();
	table_cruces_plantilla5 = $('#rpt_cruces_plantilla').DataTable({
		"order": [[ 0, 'desc' ]],
		data: otable,
		"destroy": true,
		//responsive: true,
		aLengthMenu: [
			[5, 10, 50, 100, -1],
			[5, 10, 50, 100, "All"]
		],
		iDisplayLength: 10,
		"autoWidth": true,
		//"jQueryUI": true,
		//"scrollCollapse": true,
		"scrollX": true,
		"sScrollXInner": "200%",
		"fnRowCallback": function( nRow, aData ) {
			var $nRow = $(nRow);
			if(aData[0] == ''){
				if ( $.inArray(aData[0], selected_plantilla5) !== -1 ) {
					$nRow.addClass('selected');
				}else{
					$nRow.css({"background-color":"#0088cc"});
					$nRow.css({"color":"#FFF"});
				}
			}
			return nRow;
		}
	});
	
	$('#rpt_cruces_plantilla tbody').on('click', 'td',function(e){		
		var current_row = $(this).parents('tr');
		if (current_row.hasClass('child')) {//Check if the current row is a child row
			current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
		}
		
		var cell_clicked = table_cruces_plantilla5.cell(this).index();
		var oRow  = table_cruces_plantilla5.row(current_row).data();
		if (typeof(cell_clicked) != 'undefined') {
			//if(cell_clicked.column != 0 && cell_clicked.column != 5 && cell_clicked.column != 9 && cell_clicked.column != 10 && cell_clicked.column != 11){
			var id = oRow[0];
			if(id != ''){
				var index = $.inArray(id, selected_plantilla5);
				if ( index === -1 ) {
					selected_plantilla5.push( oRow[0] );
					$(this).closest('tr').css({"background-color":"#0088cc"});
					$(this).closest('tr').css({"color":"#FFF"});
				}else{
					selected_plantilla5.splice( index, 1 );
					$(this).closest('tr').css({"background-color":"#FFF"});
					$(this).closest('tr').css({"color":"#000"});
				}
				//$('#lbl_total_seleccionados').html(selected_plantilla5.length);
			}
		}
	});
	
	setTimeout(function () {$('#rpt_cruces_plantilla').DataTable().columns.adjust().responsive.recalc();},1000);
}

function convert_array_table_cruces_plantilla_vanzada5(){
	var oReturn = new Array();
	for(i = 0; i < aCrucesPlantilla.length ; i++){
		oPush = [ 
				aCrucesPlantilla[i].id_cruce,
				aCrucesPlantilla[i].cliente,
				aCrucesPlantilla[i].aduana,
				aCrucesPlantilla[i].caja,
				aCrucesPlantilla[i].fecha_registro,
				aCrucesPlantilla[i].nom_lineat,
				aCrucesPlantilla[i].nombretransfer,
				aCrucesPlantilla[i].po_number,
				aCrucesPlantilla[i].nombreentrega
			]
		oReturn.push(oPush);
	}
	return oReturn;
}
 
function seleccionar_cruces_plantilla_avanzada_5(){
	$('#modal_cruces_plantilla').modal('hide');
	aCrucesPlantilla = new Array();
	inicializa_tabla_cruces_plantilla_avanzada5();
	ajax_consultar_plantilla_avanzada_5(selected_plantilla5);
}
 
 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *			SUBIR PEDIMENTO SIMPLIFICADO					    *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */ 
 
function inicializa_controles_subir_pedimento(){
	$("#txt_numero_referencia_upped").val('');
	$("#txt_numero_remesa_upped").val('');
	$("#upload_pedimento").fileinput('clear');
}
 
function subir_pedimento_simplificado(pReferencia,pRemesa){
	inicializa_controles_subir_pedimento();
	$("#txt_numero_referencia_upped").val(pReferencia);
	$("#txt_numero_remesa_upped").val(pRemesa);
	sReferencia = pReferencia;
	sRemesa = pRemesa;
	$('#modalsubirpedimento').modal({show: true,backdrop: 'static',keyboard: false});
}

function ajax_guardar_documento_pedimento(){
	var pdfPedimento = document.getElementById('upload_pedimento');
	if (sReferencia == ''){
		var strMensaje = 'La referencia no es correcta.';
		$("#mensaje_mdl_subir_pedimento").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
		return;
	}
	if (!pdfPedimento.files[0]){
		var strMensaje = 'El archivo PDF del pedimento es incorrecto.';
		$("#mensaje_mdl_subir_pedimento").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
		$("#upload_pedimento").fileinput('clear');
		return;
	}
	var sFileName = sReferencia + (sRemesa == '' ? '' : '-' + sRemesa);
	var odata = new FormData();
	odata.append('id_cruce', sIdCruce);
	odata.append('nombre', sFileName);
	odata.append('pdfPedimento', pdfPedimento.files[0]);
	
	$.ajax({
		url: "ajax/cruces_exportacion/ajax_subir_pedimento.php",
		type: "POST",
		data: odata,
		timeout: 300000,
		contentType: false,
		cache: false,
		processData:false,
		xhr: function()
		{
			var xhr = new window.XMLHttpRequest();
			xhr.upload.addEventListener("progress", function(evt){
			  if (evt.lengthComputable) {
				var percent = evt.loaded / evt.total * 100;
				percent = percent.toFixed(0);
				if(percent > 99) percent = 99;
				var sMen = '<div class="progress progress-striped active">';
				sMen += '		<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
				sMen += '			<span>'+percent+'% completado</span>';
				sMen += '		</div>';
				sMen += '	</div>';
				$('#mensaje_modal_procesar_cfdi').html(sMen);
			  }
			}, false);
			return xhr;
		},
		beforeSend: function(){
			$("#mensaje_mdl_subir_pedimento").html('<div class="alert alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> Procesando archivo, espere un momento por favor...</div>');
		},
		success: function(response)
		{
			if(response != '500'){
				respuesta = JSON.parse(response);				
				if(respuesta.Codigo == '1'){
					$("#mensaje_mdl_subir_pedimento").html('');
					if(respuesta.aFacturas.length > 0){
						aFacturas = respuesta.aFacturas;
						inicializa_tabla_facturas();
					}
					$("#mensaje_cruces").html('<div class="alert alert-success alert-dismissible" role="alert">El documento del pedimento se ha guardado correctamente!.</div>');
					setTimeout(function () {$("#mensaje_cruces").html('');},2000);
					$('#modalsubirpedimento').modal('hide');
					inicializa_controles_subir_pedimento();
				}else{
					var strMensaje = respuesta.Mensaje+respuesta.Error;
					$("#mensaje_mdl_subir_pedimento").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');
				}				
			}else{
				$("#mensaje_mdl_subir_pedimento").html('<div class="alert alert-danger alert-dismissible" role="alert">'+aEtiquetas[204-1].strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("logout.php");},6000);
			}
		},
		error: function(a,b){
			alert(a.status + ' [' + a.statusText + ']');
		}
	});
}
 
 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *			PLANTILLA AVANZADA 5 EXPORTACION				   *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */ 
 
function ajax_consultar_plantilla_avanzada_5(pIdCruce){
	sIdCruce = pIdCruce;	
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_consultar_cruce_plantilla_5.php',
		data: {
			id_cruce: JSON.stringify(sIdCruce)
			
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Procesando información, espere un momento por favor...';
			$("#mensaje_cruces").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if(respuesta.Codigo == '1'){
					$("#mensaje_cruces").html('');
					$( "#chk_usar_uuid_plantilla" ).prop( "checked", true );
					aParPlant5 = respuesta.aFacPart;
					aFacReceptores = respuesta.aFacReceptores;
					//var selfacplantilla = [];
					$("#sel_proveedor_plantilla").val('');
					$("#mensaje_modal_plantilla_5").html('');
					var sHtml = '';
					sHtml += '<div class="col-md-12">';
					sHtml += '	<div class="form-group text-left">';
					sHtml += '		<div class="input-group">';
					sHtml += '			<div class="input-group-addon">Facturas</div>';
					sHtml += '			<select id="sel_facturas_plantilla" class="form-control select2" style="width: 100%;" onchange="mostrar_receptor_factura_plantilla(); return false;">';
					sHtml += '				<option value="todas">TODAS</option>';
					for(i=0; i<aFacReceptores.length; i++){
						sHtml += '			<option value="'+aFacReceptores[i].numero_factura+'">'+aFacReceptores[i].numero_factura+'</option>';
					}
					sHtml += '			</select>';
					sHtml += '			<span class="input-group-btn">';
					sHtml += '				<button onclick="asignar_proveedor_factura(); return false;" type="button" class="btn btn-primary"><i class="fa fa-check-square-o" aria-hidden="true"></i> Asignar Destinatario</button>';
					sHtml += '			</span>';
					sHtml += '		</div>';
					sHtml += '	</div>';
					sHtml += '</div>';
					$("#div_facturas_plantilla_cruces").html(sHtml);
					mostrar_receptor_factura_plantilla();
					sel_parplantilla = [];
					inicializar_table_parfac_plantilla_5();
					$('#modal_plantilla').modal({show: true,backdrop: 'static',keyboard: false});
				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
					$('html, body').animate({scrollTop: $("#mensaje_cruces").offset().top}, 500);
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
				setTimeout(function () {window.parent.location.replace("../logout.php");},3000);
			}
		},
		error: function(a,b){
			$("#mensaje_cruces").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
} 

function inicializar_table_parfac_plantilla_5(){
	var otable = convert_array_table_parfac_plantilla_5();
	table_plantilla = $('#tbl_plantilla').DataTable({
		data: otable, 
		"destroy": true,
		responsive: true,
		aLengthMenu: [
			[5, 10, 50, 100, -1],
			[5, 10, 50, 100, "All"]
		],
		iDisplayLength: 10/*,
		/*"dom": "<rf<Bt><pl>i>",
		"buttons": [
			{
				extend: 'excelHtml5',
				text: '<i class="fa fa-download" aria-hidden="true"></i> Descargar Excel',
				className: 'verde_btns'
			},
		]*/
	});
	
	setTimeout(function () {$('#tbl_plantilla').DataTable().columns.adjust().responsive.recalc();},300);
}

function convert_array_table_parfac_plantilla_5(){
	var oReturn = new Array();
	for(i = 0; i < aParPlant5.length ; i++){
		var nFactura = ($('#chk_usar_uuid_plantilla').is(':checked') ? aParPlant5[i].UUID : aParPlant5[i].numero_factura);
		oPush = [
			aParPlant5[i].id_proveedor,
			nFactura,
			aParPlant5[i].fecha_factura,
			aParPlant5[i].monto_factura,
			aParPlant5[i].moneda,
			aParPlant5[i].incoterm,
			aParPlant5[i].subdivision,
			aParPlant5[i].certificado_origen,
			aParPlant5[i].numero_parte,
			aParPlant5[i].pais_origen,
			aParPlant5[i].pais_vendedor,
			aParPlant5[i].fraccion,
			aParPlant5[i].descripcion,
			aParPlant5[i].precio_partida,
			aParPlant5[i].UMC,
			aParPlant5[i].cantidad_UMC,
			aParPlant5[i].cantidad_UMT,
			aParPlant5[i].preferencia_arancelaria,
			aParPlant5[i].marca,
			aParPlant5[i].modelo,
			aParPlant5[i].submodelo,
			aParPlant5[i].serie,
			aParPlant5[i].descripcion_cove,
			aParPlant5[i].referencia
			]
		oReturn.push(oPush);
	}
	return oReturn;
}

function asignar_proveedor_factura(){
	if($("#sel_proveedor_plantilla").val() == ''){
		var sMensaje = "Es necesario seleccionar el destinatario de la factura.";
		$("#mensaje_modal_plantilla_5").html('<div class="alert alert-danger alert-dismissible" role="alert">'+sMensaje+'</div>');
	}
	for(i=0; i<aParPlant5.length; i++){
		if($("#sel_facturas_plantilla").val() != 'todas'){
			if($("#sel_facturas_plantilla").val().trim() == aParPlant5[i].numero_factura.trim()){
				aParPlant5[i].id_proveedor = $("#sel_proveedor_plantilla").val();
			}
		}else{
			aParPlant5[i].id_proveedor = $("#sel_proveedor_plantilla").val();
		}
		
	}
	inicializar_table_parfac_plantilla_5();
}

function mostrar_receptor_factura_plantilla(){
	if($("#sel_facturas_plantilla").val() != 'todas'){
		var nitem = $("#sel_facturas_plantilla").val();
		for(i=0; i<aFacReceptores.length; i++){
			if($("#sel_facturas_plantilla").val() == aFacReceptores[i].numero_factura){
				$("#txt_receptor_factura_plantilla").html(aFacReceptores[i].receptor);
				break;
			}
		}
		$("#txt_receptor_factura_plantilla").attr('rows', '1');
	}else{
		var sReceptores = '';
		for(i=0; i<aFacReceptores.length; i++){
			sReceptores += 'Factura:'+aFacReceptores[i].numero_factura+' | Receptor:'+aFacReceptores[i].receptor+'\n';
		}
		$("#txt_receptor_factura_plantilla").attr('rows', aFacReceptores.length);
		$("#txt_receptor_factura_plantilla").html(sReceptores);
	}
	
}

function ajax_generar_plantilla_5(){
	$.ajax({
		type: "POST",
		timeout: 120000,
		url:   'ajax/cruces_exportacion/ajax_generar_plantilla_5.php',
		data: {
			facturas_numparte: JSON.stringify(aParPlant5),
			numfact_uui: ($('#chk_usar_uuid_plantilla').prop('checked') ? '1' : '0')
		},
		beforeSend: function (dataMessage) {
			var strMensaje = 'Generando plantilla, espere un momento por favor...';
			$("#mensaje_modal_generar_plantilla_5").html('<div class="alert alert-info alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> '+strMensaje+'</div>');
		},
		success:  function (response) {
			if (response != '500'){
				var respuesta = JSON.parse(response);
				if(respuesta.Codigo == '1'){
					$("#mensaje_modal_generar_plantilla_5").html(respuesta.Mensaje);





				}else{
					var strMensaje = respuesta.Mensaje + ' ' + respuesta.Error;
					$("#mensaje_modal_generar_plantilla_5").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+strMensaje+'</div>');
				}
			}else{
				var strMensaje = 'Su sesión de usuario ha finalizado. Inicie sesión nuevamente.';
				$("#mensaje_modal_generar_plantilla_5").html('<div class="alert alert-danger alert-dismissible" role="alert">'+strMensaje+'</div>');				
			}
		},
		error: function(a,b){
			$("#mensaje_modal_generar_plantilla_5").html('<div class="alert alert-danger alert-dismissible" role="alert">ERROR AJAX: '+a.status + ' [' + a.statusText + ']'+'</div>');
		}
	});
}

 /* ///////////////////////////////////////////
	DETECTAR EL EXPLORADOR
//////////////////////////////////////////// */

function detectIE() {
  var ua = window.navigator.userAgent;

  // Test values; Uncomment to check result …

  // IE 10
  // ua = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)';
  
  // IE 11
  // ua = 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko';
  
  var msie = ua.indexOf('MSIE ');
  if (msie > 0) {
    // IE 10 or older => return version number
    return true;//parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
  }

  var trident = ua.indexOf('Trident/');
  if (trident > 0) {
    // IE 11 => return version number
    return true;
  }

  // other browser
  return false;
}
