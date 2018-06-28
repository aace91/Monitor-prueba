var table_detsalidaExpo;
var oTableFacturasGrid = null;
var oTableFacturasDocsGrid = null;

var __aAppData;
var __oTrDtDelete; //Para borrar elementos de la tabla
var __oTrDtInfo; //Para la informacion del datatable

var __sTipoPedimento = ''; //Para la referencia actual que se consulto
var __oDataNewRow = null; //Para guardar el row antes de consultar si existe documento (caso consolidado)
var __aDataDelRow = new Array();
var __bDataNewRow = false;

var aFileConfig = {
	showUpload: false,
	uploadClass: "btn btn-success",
	uploadLabel: "Subir",
	uploadUrl: function () { alert(); },
	//browseClass: "btn btn-success",
	browseLabel: "Buscar ...",
	//browseIcon: "<i class=\"glyphicon glyphicon-camera\"></i> ",
	removeClass: "btn btn-danger",
	removeLabel: "Eliminar",
	removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i> ",
	showPreview: false,
	allowedFileExtensions: ['pdf']
};

var __aSelFacturas = new Array();

var __sURLDocsExpo = 'http://delbravoweb.com/documentos_expo/salidaExpo';

$(document).ready(function() {
	$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	
	__aAppData = $('#itxt_data').data('app_data');
	
	if(__aAppData.Codigo == '-1') {
		show_modal_error(__aAppData.Mensaje);
		$('#ibtn_guardar_salida').hide();
		return;
	}
	
	__aAppData = $('#itxt_data').data('app_data').aAppData;
	
	/********************************************************************/
	
	$("#isel_lineast").select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "Selecciona una Linea Transportista",
		ajax: {
			url: "./func/salidaExpoFunc.php",
			type: "POST",
			dataType: 'json',
			delay: 250,
			timeout: 10000,
			data: function (params) {
				return {
					q: params.term, // search term
					action: 'buscalineat'
				};
			},
			processResults: function (data, page) {
			  // parse the results into the format expected by Select2.
			  // since we are using custom formatting functions we do not need to
			  // alter the remote JSON data
			  return {
				results: data.items
			  };
			},
			cache: true,
			minimumInputLength: 1
		}
	}).on("select2:select", function (e) { 
		$('#ibtn_agregar_lineat button').focus(); 
	}).on("select2:close", function (e) {
		$('#ibtn_agregar_lineat button').focus();
	});
	
	$('#isel_aa_ame').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "Selecciona una Agencia Aduanal Americana",
		ajax: {
			url: "./func/salidaExpoFunc.php",
			type: "POST",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term, // search term
					action: 'buscaaaa'
				};
			},
			processResults: function (data, page) {
			  // parse the results into the format expected by Select2.
			  // since we are using custom formatting functions we do not need to
			  // alter the remote JSON data
			  return {
				results: data.items
			  };
			},
			cache: true,
			minimumInputLength: 1
		}
	}).on("select2:select", function (e) { 
		$('#ibtn_agregar_aa_ame button').focus(); 
	}).on("select2:close", function (e) {
		$('#ibtn_agregar_aa_ame button').focus(); 
	});
	
	$('#isel_transfer').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "Selecciona un Transfer",
		ajax: {
			url: "./func/salidaExpoFunc.php",
			type: "POST",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term, // search term
					action: 'buscatransfers_expo'
				};
			},
			processResults: function (data, page) {
			  // parse the results into the format expected by Select2.
			  // since we are using custom formatting functions we do not need to
			  // alter the remote JSON data
			  return {
				results: data.items
			  };
			},
			cache: true,
			minimumInputLength: 1
		}
	}).on("select2:select", function (e) { 
		$('#ibtn_agregar_transfer button').focus(); 
	}).on("select2:close", function (e) { 
		$('#ibtn_agregar_transfer button').focus(); 
	});
	
	$('#isel_entregar_en').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "Selecciona un Lugar de Entrega",
		ajax: {
			url: "./func/salidaExpoFunc.php",
			type: "POST",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term, // search term
					action: 'buscaentregas_expo'
				};
			},
			processResults: function (data, page) {
			  // parse the results into the format expected by Select2.
			  // since we are using custom formatting functions we do not need to
			  // alter the remote JSON data
			  return {
				results: data.items
			  };
			},
			cache: true,
			minimumInputLength: 1
		}
	}).on("select2:select", function(e) { 
	   $('#itxt_entregar_en_direccion').val($(e.currentTarget).find("option:selected").data().data.dir);
	   $('#ibtn_agregar_entregas button').focus();
	}).on("select2:close", function (e) { 
		$('#ibtn_agregar_entregas button').focus(); 
	});
	
	$('#isel_cliente').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "Selecciona un Cliente",
		ajax: {
			url: "./func/salidaExpoFunc.php",
			type: "POST",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					q: params.term, // search term
					action: 'buscacltes_expo'
				};
			},
			processResults: function (data, page) {
			  // parse the results into the format expected by Select2.
			  // since we are using custom formatting functions we do not need to
			  // alter the remote JSON data
			  return {
				results: data.items
			  };
			},
			cache: true,
			minimumInputLength: 1
		}
	});
	
	$('#isel_tipo_salida').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "Seleccione una Opción"
	}).on("select2:select", function (e) { 
		$('#itxt_tipo_salida_caja').focus(); 
	}).on("select2:close", function (e) { 
		$('#itxt_tipo_salida_caja').focus(); 
	});

	$('#isel_edit_tipo_salida').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "Seleccione una Opción"
	});
	
	$('#isel_aduana').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "Seleccione una Opción"
	}).on("select2:select", function (e) { 
		$('#isel_transfer').select2('open');
	}).on("select2:close", function (e) { 
		$('#isel_transfer').focus(); 
	});
	
	$('#isel_factura').select2({
		theme: "bootstrap",
		width: "off",
		placeholder: "Seleccione una Opción"/*,
		tags: true,
		tokenSeparators: [',', ' ']*/
	}).on("select2:select", function (e) { 
		$('#ibtn_agregar_factura').focus(); 
	}).on("select2:close", function (e) { 
		$('#ibtn_agregar_factura').focus(); 
	});
	
	//new Date(year, month, day, hours, minutes, seconds, milliseconds)
	$('#itxt_fecha').datetimepicker({
		date: new Date()
	});
	$('#itxt_hora_entrega').datetimepicker({ format: 'LT' });
	
	$('#itxt_cruces_en_salida').TouchSpin({
		initval: 0,
		min: 1,
		max: 1000000000,
		step: 1
	});
	
	$("#isel_cliente").prop("disabled", true);
	
	$('#itxt_referencia').on("keypress", function(e) {
		if (e.keyCode == 13) {
			ajax_get_referencia_casa();
		}
	});
	
	$("#ifile_relacion_docs").fileinput('clear');
	$("#ifile_relacion_docs").fileinput('refresh', aFileConfig);
	
	$("#ifile_NOA").fileinput('clear');
	$("#ifile_NOA").fileinput('refresh', aFileConfig);
	
	$("#ifile_solicitud_retiro").fileinput('clear');
	$("#ifile_solicitud_retiro").fileinput('refresh', aFileConfig);
	
	fcn_inicializar_factura_ifiles();
	
	$('#modal_add_pedsimp').on('shown.bs.modal', function (e) {
		$("#ifile_add_pedsimp").fileinput('clear');
		$("#ifile_add_pedsimp").fileinput('refresh', {
			showUpload: false,
			uploadClass: "btn btn-success",
			uploadLabel: "Subir",
			uploadUrl: function () { alert(); },
			//browseClass: "btn btn-success",
			browseLabel: "Buscar ...",
			//browseIcon: "<i class=\"glyphicon glyphicon-camera\"></i> ",
			removeClass: "btn btn-danger",
			removeLabel: "Eliminar",
			removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i> ",
			showPreview: false,
			allowedFileExtensions: ['pdf']
		});
		
		show_custom_function_ok('', 'modal_add_pedsimp_mensaje');
		$('#modal_add_pedsimp_btn_ok').prop('disabled', false);
	});
	
	$('#modal_add_pednormal').on('shown.bs.modal', function (e) {
		$("#ifile_add_pednormal").fileinput('clear');
		$("#ifile_add_pednormal").fileinput('refresh', {
			showUpload: false,
			uploadClass: "btn btn-success",
			uploadLabel: "Subir",
			uploadUrl: function () { alert(); },
			//browseClass: "btn btn-success",
			browseLabel: "Buscar ...",
			//browseIcon: "<i class=\"glyphicon glyphicon-camera\"></i> ",
			removeClass: "btn btn-danger",
			removeLabel: "Eliminar",
			removeIcon: "<i class=\"glyphicon glyphicon-trash\"></i> ",
			showPreview: false,
			allowedFileExtensions: ['pdf']
		});
		
		show_custom_function_ok('', 'modal_add_pednormal_mensaje');
		$('#modal_add_pednormal_btn_ok').prop('disabled', false);
	});

	$('#modal_asig_doc').on('shown.bs.modal', function (e) {
		ajax_get_documentos();
		
		var aData = oTableFacturasGrid.dataTable().fnGetData();
		fcn_cargar_grid_facturas_docs(aData);
	});
	
	$('#itxt_prefile_entry_number').mask("***-9999999-9");
	$('#itxt_edit_prefile_entry_number').mask("***-9999999-9");
	
	if (__aAppData.sTask == 'insertar') {
		$('#ibtn_imprimir_salida').hide();
		var aData = new Array();
		fcn_cargar_grid_facturas(aData);
		$('#isel_lineast').select2('open');
		if(__aAppData.bCruce == '1'){
			fcn_set_info_salida();
			fcn_cargar_grid_facturas(__aAppData.aSalidaData.aFacturas);
		}
	} else {
		$('#ibtn_imprimir_salida').show();
		fcn_set_info_salida();
		fcn_cargar_grid_facturas(__aAppData.aSalidaData.aFacturas);
		$('#itxt_fecha :input').attr('disabled', true);
		$('#isel_lineast').focus();
	}
});

function fcn_set_info_salida() {
	try {
		var aData = __aAppData.aSalidaData;
		if(__aAppData.bCruce != '1'){
			$('#itxt_numero_salida').val(__aAppData.nSalidaNumero);
			$('#itxt_fecha').data("DateTimePicker").date(new Date(Date.parse(aData.fecha)));
		}
		$('#ickb_ferrocarril').prop('checked', ((aData.ferrocarril == 'X')? true : false));
		//$('#isel_tipo_salida').val(aData.tiposalida).trigger('change');
		//$('#itxt_tipo_salida_caja').val(aData.caja);
		$('#isel_aduana').val(aData.aduana).trigger('change');
		
		$('#itxt_cruces_en_salida').val(aData.cruces);
		$('#ickb_urgente').prop('checked', ((aData.urgente == 'SI')? true : false));
		$('#ickb_leyenda_trans').prop('checked', ((aData.leyenda == 'X')? true : false));
		if (aData.horaentrega != '') {
			$('#itxt_hora_entrega').data("DateTimePicker").date(new Date(Date.parse('1988-01-25 ' + aData.horaentrega)));
		}
		$('#itxt_recibio').val(aData.recibio);
		$('#itxt_numero_viaje').val(aData.viaje);
		$('#itxt_indicaciones').val(aData.indicaciones);
		$('#itxt_observaciones').val(aData.observaciones);
		
		fcn_set_select2_data('isel_lineast', aData.nolineatransp, aData.lineatransp);
		//fcn_set_select2_data('isel_aa_ame', aData.noaaa, aData.nombreaaa);
		fcn_set_select2_data('isel_transfer', aData.notransfer, aData.nombretransfer);
		//fcn_set_select2_data('isel_entregar_en', aData.noentrega, aData.Nombreentrega);
		$('#isel_entregar_en').select2("trigger", "select", {
			data: {
				id: aData.noentrega,
				text: aData.Nombreentrega,
				dir: aData.direntrega
			}
		});
		
		if (aData.relacion_docs_name != '') {
			$('#igpo_relacion_docs').hide();
			$('#igpo_relacion_docs_btn').show();
		}
		
		if (aData.notificacion_arribo_name != '') {
			$('#igpo_NOA').hide();
			$('#igpo_NOA_btn').show();
		}
		
		if (aData.solicitud_retiro_name != '') {
			$('#igpo_solicitud_retiro').hide();
			$('#igpo_solicitud_retiro_btn').show();
		}
	} catch (err) {		
		var strMensaje = 'fcn_set_info_salida() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_set_select2_data(oCtrl, nId, sText) {
	try {
		$('#' + oCtrl).select2("trigger", "select", {
			data: {
				id: nId,
				text: sText
			}
		});		
	} catch (err) {		
		var strMensaje = 'fcn_set_select2_data() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_inicializar_factura_ifiles() {
	try {
		$("#ifile_packlist").fileinput('clear');
		$("#ifile_packlist").fileinput('refresh', aFileConfig);
		
		$("#ifile_cerOrigen").fileinput('clear');
		$("#ifile_cerOrigen").fileinput('refresh', aFileConfig);
		
		$("#ifile_ticketbas").fileinput('clear');
		$("#ifile_ticketbas").fileinput('refresh', aFileConfig);
		
		$("#ifile_prefile").fileinput('clear');
		$("#ifile_prefile").fileinput('refresh', aFileConfig);
	} catch (err) {		
		var strMensaje = 'fcn_inicializar_factura_ifiles() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_inicializar_factura_ifiles_edit() {
	try {
		$("#ifile_edit_packlist").fileinput('clear');
		$("#ifile_edit_packlist").fileinput('refresh', aFileConfig);
		
		$("#ifile_edit_cerOrigen").fileinput('clear');
		$("#ifile_edit_cerOrigen").fileinput('refresh', aFileConfig);
		
		$("#ifile_edit_ticketbas").fileinput('clear');
		$("#ifile_edit_ticketbas").fileinput('refresh', aFileConfig);
		
		$("#ifile_edit_prefile").fileinput('clear');
		$("#ifile_edit_prefile").fileinput('refresh', aFileConfig);
	} catch (err) {		
		var strMensaje = 'fcn_inicializar_factura_ifiles() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

/*********************************************************************************************************************************
** FILL AND CREATE GRIDS FUNCTIONS                                                                                              **
*********************************************************************************************************************************/

function fcn_cargar_grid_facturas(aData) {
	try {
		if (oTableFacturasGrid == null) {
			var oDivDisplayErrors = 'idiv_mensaje';
			var div_table_name = 'dtdetsalidaExpo';
			var div_refresh_name = div_table_name + '_refresh';			
			
			oTableFacturasGrid = $('#' + div_table_name);
			
			oTableFacturasGrid.DataTable({
				//order: [[0, 'desc']],
				processing: false,
				serverSide: false,
				bServerSide: false,
				columnDefs: [
					{ targets: [1, 2, 3, 4, 5, 6, 7], orderable: false }
				],
				data: aData,
				columns: [ 
					{ data: "referencia" },
					{ data: "cliente" },
					{ data: "patente" },
					{ data: "pedimento" },
					{ data: "factura" },
					{
						data: "factura",
						className: "text-center",
						render: function ( data, type, row ) {
							if (type == 'display') { 
								var sHtml = '';
								
								if (row.caja == '') {
									row.caja = __aAppData.aSalidaData.caja
									row.tiposalida = __aAppData.aSalidaData.tiposalida;
								}
								
								if (!(row.caja == '')) {
									sHtml += row.tiposalida + ' - ' + row.caja;
								}
								
								return sHtml;
							}
							return data;
						}
					},
					{ data: "aaa" },					
					{
						data: null,
						className: "text-center",
						render: function ( data, type, row ) {
							if (type == 'display') { 
								var sHtml = '';
								sHtml += '<a class="btn btn-primary btn-xs editor_' + div_table_name + '_editar"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
								sHtml += '&nbsp;&nbsp;&nbsp;&nbsp;';
								sHtml += '<a class="btn btn-danger btn-xs editor_' + div_table_name + '_eliminar"><i class="fa fa-trash" aria-hidden="true"></i></a>';
								
								return sHtml;
							}
							return data;
						}
					}
				],
				responsive: true,
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
					},
					select: {
						rows: {
							_: "",
							0: "",                    
							1: "1 fila seleccionada"
						}
					}
				},
				bPaginate: false,
				bLengthChange: false,
				bFilter: false,
				buttons: []
			});
			
			oTableFacturasGrid.on('click', 'a.editor_' + div_table_name + '_eliminar', function (e) {
				try {		
					var oData = fcn_get_row_data($(this), oTableFacturasGrid);
					
					__oTrDtDelete = $(this).parents('tr');
					if (__oTrDtDelete.hasClass('child')) {//Check if the current row is a child row
						__oTrDtDelete = __oTrDtDelete.prev();//If it is, then point to the row before it (its 'parent')
					}
					
					var strTitle = 'Eliminar Factura';
					var strQuestion = 'Desea eliminar la factura [' + oData.factura + ']' + ((__aAppData.sTask == 'insertar')? '' : ', Este cambio sera permanente!!!');
					var oFunctionOk = function () { 
						var oData = oTableFacturasGrid.DataTable().row(__oTrDtDelete).data();	
						
						if (oData.num_rem_ped != '' && oData.num_rem_ped != null) {
							__aDataDelRow.push({ 
								referencia: oData.referencia, 
								factura: oData.factura, 
								num_rem_ped: oData.num_rem_ped,
								cons_fac_ped: oData.cons_fac_ped,
								packing_list_name: (($.type(oData.packing_list_name) == 'object')? '' : oData.packing_list_name),
								certificado_origen_name: (($.type(oData.certificado_origen_name) == 'object')? '' : oData.certificado_origen_name),
								ticket_bascula_name: (($.type(oData.ticket_bascula_name) == 'object')? '' : oData.ticket_bascula_name)
							});

							ajax_set_eliminar_factura();
						} else {
							oTableFacturasGrid.DataTable().row(__oTrDtDelete).remove().draw();
						}						
					};
					var oFunctionCancel = null;
					show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_eliminar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );
			
			oTableFacturasGrid.on('click', 'a.editor_' + div_table_name + '_editar', function (e) {
				try {		
					__oDataNewRow = fcn_get_row_data($(this), oTableFacturasGrid);
					fcn_inicializar_factura_ifiles_edit();
					
					$("#isel_edit_tipo_salida").select2("destroy");

					$("#isel_edit_tipo_salida").find('option[value=CAJA]').attr('disabled', 'disabled');
					$("#isel_edit_tipo_salida").find('option[value=PLATAFORMA]').attr('disabled', 'disabled');
					if ($('#isel_edit_tipo_salida').find('option[value="' + __oDataNewRow.tiposalida + '"]').is(':disabled')) {
						$('#isel_edit_tipo_salida').find('option[value="' + __oDataNewRow.tiposalida + '"]').removeAttr('disabled');
					}

					$('#isel_edit_tipo_salida').select2({
						theme: "bootstrap",
						width: "off",
						placeholder: "Seleccione una Opción"
					});

					$('#isel_edit_tipo_salida').val(__oDataNewRow.tiposalida).trigger('change');
					$('#itxt_edit_tipo_salida_caja').val(__oDataNewRow.caja);
					
					if (__oDataNewRow.packing_list_name != '') {
						$('#igpo_edit_packlist_docs').hide();
						$('#igpo_edit_packlist_docs_btn').show();
					} else {
						$('#igpo_edit_packlist_docs').show();
						$('#igpo_edit_packlist_docs_btn').hide();
					}
					
					if (__oDataNewRow.certificado_origen_name != '') {
						$('#igpo_edit_cerOrigen_docs').hide();
						$('#igpo_edit_cerOrigen_docs_btn').show();
					} else {
						$('#igpo_edit_cerOrigen_docs').show();
						$('#igpo_edit_cerOrigen_docs_btn').hide();
					}
					
					if (__oDataNewRow.ticket_bascula_name != '') {
						$('#igpo_edit_ticketbas_docs').hide();
						$('#igpo_edit_ticketbas_docs_btn').show();
					} else {
						$('#igpo_edit_ticketbas_docs').show();
						$('#igpo_edit_ticketbas_docs_btn').hide();
					}
					
					if (__oDataNewRow.prefile_name != '') {
						$('#igpo_edit_prefile_docs').hide();
						$('#igpo_edit_prefile_docs_btn').show();
						$('#igpo_edit_prefile_docs_select').hide();
					} else {
						$('#igpo_edit_prefile_docs').show();
						$('#igpo_edit_prefile_docs_btn').hide();
						$('#igpo_edit_prefile_docs_select').hide();
					}
					
					$('#itxt_edit_prefile_entry_number').val(__oDataNewRow.prefile_entry_number);
		
					$('#modal_edit_fac_doc').modal({ show: true });
				} catch (err) {		
					var strMensaje = 'a.editor_' + div_table_name + '_editar() :: ' + err.message;
					show_modal_error(strMensaje);
				}  
			} );

			oTableFacturasGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			oTableFacturasGrid.DataTable().clear().draw();
			if (aData.length > 0) {
				oTableFacturasGrid.dataTable().fnAddData(aData);	
			}
			setTimeout(function(){ oTableFacturasGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_facturas() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_cargar_grid_facturas_docs(aData) {
	try {
		if (oTableFacturasDocsGrid == null) {
			var oDivDisplayErrors = 'idiv_mdl_asig_doc_mensaje';
			var div_table_name = 'dtFacturasDocs';
			var div_refresh_name = div_table_name + '_refresh';			
			
			oTableFacturasDocsGrid = $('#' + div_table_name);
			
			oTableFacturasDocsGrid.DataTable({
				order: [[1, 'desc']],
				processing: false,
				serverSide: false,
				bServerSide: false,
				columnDefs: [
					{ targets: [0, 3, 4, 5, 6], orderable: false }
				],
				data: aData,
				columns: [ 
					{
						data: null,
						className: "text-center",
						render: function ( data, type, row ) {
							if (type == 'display') { 
								var sHtml = '';
								if (data != '') {
									sHtml += '<input type="checkbox" class="editor_' + div_table_name + '_seleccionar">'; 
								}
								return sHtml;
							}
							return data;
						}
					},
					{ data: "referencia" },
					{ data: "factura" },
					{
						data: "packing_list_id",
						className: "text-center",
						render: function ( data, type, row ) {
							if (type == 'display') { 
								var sHtml = '';
								if (data != '') {
									//sHtml += '<button class="btn btn-default btn-xs"><i class="fa fa-check-circle text-success" aria-hidden="true"></i></button>&nbsp;&nbsp;'; 
									sHtml += '<button class="btn btn-info btn-xs" onclick="fcn_ver_documento(\'' + row.packing_list_name + '\')"><i class="fa fa-eye" aria-hidden="true"></i></button>&nbsp;&nbsp;'; 
									sHtml += '<button class="btn btn-danger btn-xs editor_' + div_table_name + '_pkl_eliminar"><i class="fa fa-trash" aria-hidden="true"></i></button>'; 
								}
								//sHtml += '<a class="btn btn-danger btn-xs editor_' + div_table_name + '_eliminar"><i class="fa fa-trash" aria-hidden="true"></i></a>';
								
								return sHtml;
							}
							return data;
						}
					},
					{
						data: "certificado_origen_id",
						className: "text-center",
						render: function ( data, type, row ) {
							if (type == 'display') { 
								var sHtml = '';
								if (data != '') {
									//sHtml += '<button class="btn btn-default btn-xs"><i class="fa fa-check-circle text-success" aria-hidden="true"></i></button>&nbsp;&nbsp;'; 
									sHtml += '<button class="btn btn-info btn-xs" onclick="fcn_ver_documento(\'' + row.certificado_origen_name + '\')"><i class="fa fa-eye" aria-hidden="true"></i></button>&nbsp;&nbsp;'; 
									sHtml += '<button class="btn btn-danger btn-xs editor_' + div_table_name + '_cod_eliminar"><i class="fa fa-trash" aria-hidden="true"></i></button>'; 
								}
								return sHtml;
							}
							return data;
						}
					},
					{
						data: "ticket_bascula_id",
						className: "text-center",
						render: function ( data, type, row ) {
							if (type == 'display') { 
								var sHtml = '';
								if (data != '') {
									//sHtml += '<button class="btn btn-default btn-xs"><i class="fa fa-check-circle text-success" aria-hidden="true"></i></button>&nbsp;&nbsp;'; 
									sHtml += '<button class="btn btn-info btn-xs" onclick="fcn_ver_documento(\'' + row.ticket_bascula_name + '\')"><i class="fa fa-eye" aria-hidden="true"></i></button>&nbsp;&nbsp;'; 
									sHtml += '<button class="btn btn-danger btn-xs editor_' + div_table_name + '_tdb_eliminar"><i class="fa fa-trash" aria-hidden="true"></i></button>'; 
								}
								return sHtml;
							}
							return data;
						}
					},
					{
						data: "prefile_id",
						className: "text-center",
						render: function ( data, type, row ) {
							if (type == 'display') { 
								var sHtml = '';
								if (data != '') {
									//sHtml += '<button class="btn btn-default btn-xs"><i class="fa fa-check-circle text-success" aria-hidden="true"></i></button>&nbsp;&nbsp;'; 
									sHtml += '<button class="btn btn-info btn-xs" onclick="fcn_ver_documento(\'' + row.prefile_name + '\')"><i class="fa fa-eye" aria-hidden="true"></i></button>&nbsp;&nbsp;'; 
									sHtml += '<button class="btn btn-danger btn-xs editor_' + div_table_name + '_pre_eliminar"><i class="fa fa-trash" aria-hidden="true"></i></button>'; 
								}
								return sHtml;
							}
							return data;
						}
					}
				],
				responsive: true,
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
					},
					select: {
						rows: {
							_: "",
							0: "",                    
							1: "1 fila seleccionada"
						}
					}
				},
				bPaginate: false,
				bLengthChange: false,
				bFilter: false,
				buttons: []
			});
			
			oTableFacturasDocsGrid.on('click', 'input.editor_' + div_table_name + '_seleccionar', function (e) {
				var oData = fcn_get_row_data($(this), oTableFacturasGrid);

				var sSelected = oData.referencia + '|' + oData.factura
				var index = $.inArray(sSelected, __aSelFacturas);
				if (index === -1) {
					__aSelFacturas.push(sSelected);
				}else{
					__aSelFacturas.splice(index, 1);
				}
				$(this).closest('tr').toggleClass('selected');
			});
			
			oTableFacturasDocsGrid.on('click', 'button.editor_' + div_table_name + '_pkl_eliminar', function (e) {
				var oData = fcn_get_row_data($(this), oTableFacturasDocsGrid);
				
				__oTrDtInfo = $(this).parents('tr');
				if (__oTrDtInfo.hasClass('child')) {//Check if the current row is a child row
					__oTrDtInfo = __oTrDtInfo.prev();//If it is, then point to the row before it (its 'parent')
				}
				
				var strTitle = 'Eliminar Packing List';	
				var strQuestion = 'Desea eliminar el documento de la factura [' + oData.factura + ']';
				var oFunctionOk = function () { 
					var oDataRow = oTableFacturasDocsGrid.DataTable().row(__oTrDtInfo).data();	
					
					oDataRow.packing_list_id = '';
					oDataRow.packing_list_name = '';
					oTableFacturasDocsGrid.DataTable().row(__oTrDtInfo).data(oDataRow).draw();
					
					var aData = oTableFacturasDocsGrid.dataTable().fnGetData();
					fcn_cargar_grid_facturas(aData);
				};
				var oFunctionCancel = null;
				show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
			});
			
			oTableFacturasDocsGrid.on('click', 'button.editor_' + div_table_name + '_cod_eliminar', function (e) {
				var oData = fcn_get_row_data($(this), oTableFacturasDocsGrid);
				
				__oTrDtInfo = $(this).parents('tr');
				if (__oTrDtInfo.hasClass('child')) {//Check if the current row is a child row
					__oTrDtInfo = __oTrDtInfo.prev();//If it is, then point to the row before it (its 'parent')
				}
				
				var strTitle = 'Eliminar Certificado de Origen';	
				var strQuestion = 'Desea eliminar el documento de la factura [' + oData.factura + ']';
				var oFunctionOk = function () { 
					var oDataRow = oTableFacturasDocsGrid.DataTable().row(__oTrDtInfo).data();	
					
					oDataRow.certificado_origen_id = '';
					oDataRow.certificado_origen_name = '';
					oTableFacturasDocsGrid.DataTable().row(__oTrDtInfo).data(oDataRow).draw();
					
					var aData = oTableFacturasDocsGrid.dataTable().fnGetData();
					fcn_cargar_grid_facturas(aData);
				};
				var oFunctionCancel = null;
				show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
			});
			
			oTableFacturasDocsGrid.on('click', 'button.editor_' + div_table_name + '_tdb_eliminar', function (e) {
				var oData = fcn_get_row_data($(this), oTableFacturasDocsGrid);
				
				__oTrDtInfo = $(this).parents('tr');
				if (__oTrDtInfo.hasClass('child')) {//Check if the current row is a child row
					__oTrDtInfo = __oTrDtInfo.prev();//If it is, then point to the row before it (its 'parent')
				}
				
				var strTitle = 'Eliminar Ticket de Bascula';	
				var strQuestion = 'Desea eliminar el documento de la factura [' + oData.factura + ']';
				var oFunctionOk = function () { 
					var oDataRow = oTableFacturasDocsGrid.DataTable().row(__oTrDtInfo).data();	
					
					oDataRow.ticket_bascula_id = '';
					oDataRow.ticket_bascula_name = '';
					oTableFacturasDocsGrid.DataTable().row(__oTrDtInfo).data(oDataRow).draw();
					
					var aData = oTableFacturasDocsGrid.dataTable().fnGetData();
					fcn_cargar_grid_facturas(aData);
				};
				var oFunctionCancel = null;
				show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
			});
			
			oTableFacturasDocsGrid.on('click', 'button.editor_' + div_table_name + '_pre_eliminar', function (e) {
				var oData = fcn_get_row_data($(this), oTableFacturasDocsGrid);
				
				__oTrDtInfo = $(this).parents('tr');
				if (__oTrDtInfo.hasClass('child')) {//Check if the current row is a child row
					__oTrDtInfo = __oTrDtInfo.prev();//If it is, then point to the row before it (its 'parent')
				}
				
				var strTitle = 'Eliminar Prefile';	
				var strQuestion = 'Desea eliminar el documento de la factura [' + oData.factura + ']';
				var oFunctionOk = function () { 
					var oDataRow = oTableFacturasDocsGrid.DataTable().row(__oTrDtInfo).data();	
					
					oDataRow.prefile_id = '';
					oDataRow.prefile_name = '';
					oTableFacturasDocsGrid.DataTable().row(__oTrDtInfo).data(oDataRow).draw();
					
					var aData = oTableFacturasDocsGrid.dataTable().fnGetData();
					fcn_cargar_grid_facturas(aData);
				};
				var oFunctionCancel = null;
				show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel);
			});
			
			oTableFacturasDocsGrid.on( 'error.dt', function ( e, settings, techNote, message ) {
				on_grid_error(e, settings, techNote, message, oDivDisplayErrors);
			} );
		} else {
			oTableFacturasDocsGrid.DataTable().clear().draw();
			if (aData.length > 0) {
				oTableFacturasDocsGrid.dataTable().fnAddData(aData);	
			}
			setTimeout(function(){ oTableFacturasDocsGrid.DataTable().columns.adjust().responsive.recalc(); }, 500);
		}
	} catch (err) {		
		var strMensaje = 'fcn_cargar_grid_facturas() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

/* ..:: Obtenemos los datos del row ::.. */
function fcn_get_row_data($this, oGrid) {
	var current_row = $this.parents('tr');//Get the current row
	if (current_row.hasClass('child')) {//Check if the current row is a child row
		current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
	}

	var oData = oGrid.DataTable().row(current_row).data();	

	return oData;
}

/*********************************************************************************************************************************
** CONTROLS FUNCTIONS                                                                                                           **
*********************************************************************************************************************************/

function fcn_get_date(oCtrl) {
	try {
		var date = oCtrl.data('DateTimePicker').date();
		var day = ((date.date() > 9)? date.date() : '0' + date.date());
		var month = (((date.month() + 1) > 9)? (date.month() + 1) : '0' + (date.month() + 1));
		var year = date.year();
		var hours = date.hours();
		var minutes = date.minutes();
		
		var sDate = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':00';
		return sDate;
	} catch (err) {		
		var strMensaje = 'fcn_get_date() :: ' + err.message;
		//show_modal_error(strMensaje);
    }
}

function fcn_get_hour(oCtrl) {
	try {
		var date = oCtrl.data('DateTimePicker').date();
		if (date != null && date != undefined) {
			var hours = date.hours();
			var minutes = date.minutes();
			var ampm = hours >= 12 ? 'PM' : 'AM';
			hours = hours % 12;
			hours = hours ? hours : 12; // the hour '0' should be '12'
			minutes = minutes < 10 ? '0'+minutes : minutes;
	  
			var sDate = hours + ':' + minutes + ':00 ' + ampm;
			return sDate;
		} else {
			return '';
		}
	} catch (err) {		
		var strMensaje = 'fcn_get_hour() :: ' + err.message;
		//show_modal_error(strMensaje);
    }
}

function fcn_agregar_lineat(){
	editor_lineasExpo
	.title( 'Agregar transportista' )
	.buttons( 'Guardar' )
	.create();
}

function fcn_agregar_aa_ame(){
	editor_aaaExpo
	.title( 'Agregar Agente Aduanal Americano' )
	.buttons( 'Guardar' )
	.create();
}

function fcn_agregar_transfer(){
	editor_transfersExpo
	.title( 'Agregar Transfer' )
	.buttons( 'Guardar' )
	.create();
}

function fcn_agregar_entregas(){
	editor_entregasExpo
	.title( 'Agregar Lugar de Entrega' )
	.buttons( 'Guardar' )
	.create();
}
		
function fcn_display_referencia_loading(pOpt) {
	try {
		if (pOpt) {
			$('#ibtn_buscar_referencia').hide();
			$('#ibtn_buscar_referencia_loading').show();
		} else {
			$('#ibtn_buscar_referencia').show();
			$('#ibtn_buscar_referencia_loading').hide();
		}
	} catch (err) {		
		var strMensaje = 'fcn_display_referencia_loading() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_agregar_factura() {
	try {		
		show_custom_function_error('', 'idiv_message');
		__oDataNewRow = null;
		__bDataNewRow = true;
		
		var sReferencia = $('#itxt_referencia').val().toUpperCase();
		if (!sReferencia.trim()) { 
			show_custom_function_error('Debe ingresar una Referencia!', 'idiv_message');
			return;
		}
		
		var sCliente = $('#isel_cliente').val();
		sCliente = ((sCliente == null)? '' : sCliente);
		if (!sCliente.trim()) { 
			show_custom_function_error('Debe consultar la Referencia', 'idiv_message');
			return;
		}
	
		var sPatente = $('#itxt_patente').val().toUpperCase();
		if (!sPatente.trim()) { 
			show_custom_function_error('Debe ingresar una Patente!', 'idiv_message');
			return;
		}
		
		var sPedimento = $('#itxt_pedimento').val().toUpperCase();
		if (!sPedimento.trim()) { 
			show_custom_function_error('Debe ingresar un Pedimento!', 'idiv_message');
			return;
		}
		
		var sAAA = $('#isel_aa_ame').val();
		sAAA = ((sAAA == null)? '' : sAAA);
		if (!sAAA.trim()) { 
			show_custom_function_error('Debe seleccionar un Agente Aduanal Americano', 'idiv_message');
			return;
		}
		
		/************************************************************************/
		
		var sUuid = '';
		var sNumRemPed = '';
		var sConsFacPed = $('#isel_factura').val();
		sConsFacPed = ((sConsFacPed == null)? '' : sConsFacPed);
		if (!sConsFacPed.trim()) { 
			show_custom_function_error('Debe ingresar una Factura!', 'idiv_message');
			return;
		} else {
			sUuid = $('#isel_factura').select2('data')[0].uuid;
			sNumRemPed = $('#isel_factura').select2('data')[0].rem;
		}
		
		var sFactura = $('#isel_factura').select2('data')[0].text;
		var bExisteFactura = false;
		var table = oTableFacturasGrid.DataTable();
		$.each(table.rows().nodes(), function(index, item) {
			if (table.row(index).data().referencia == sReferencia && 
			    table.row(index).data().factura == sFactura) {
				bExisteFactura = true;
				return;
			}
		});
		if (bExisteFactura) { 
			show_custom_function_error('Ya existe la factura [ ' + sFactura + ' ]!', 'idiv_message');
			return;
		}
		
		/************************************************************************/
		
		var sTipoSalida = (($('#isel_tipo_salida').val() == null)? '' : $('#isel_tipo_salida').val());
		var sTipoSalidaCaja = $('#itxt_tipo_salida_caja').val().toUpperCase();
		
		if (!sTipoSalida.trim()) { 
			show_custom_function_error('Debe seleccionar un tipo de salida', 'idiv_message');
			return;
		}
		
		if (!sTipoSalidaCaja.trim()) { 
			show_custom_function_error('Debe ingresar un numero de ' + sTipoSalida.toLowerCase(), 'idiv_message');
			return;
		}
		
		/************************************************************************/
		
		__oDataNewRow = {
			clienteid: sCliente,
			cliente: $('#isel_cliente').select2('data')[0].text,
			referencia: sReferencia,
			patente: sPatente,
			pedimento: sPedimento,
			aaaid: sAAA,
			aaa: $('#isel_aa_ame').select2('data')[0].text,
			factura: sFactura,
			tiposalida: sTipoSalida,
			caja: sTipoSalidaCaja,
			uuid: sUuid,
			cons_fac_ped: sConsFacPed,
			num_rem_ped: sNumRemPed, 
			packing_list_id: '',
			packing_list_name: '',
			certificado_origen_id: '',
			certificado_origen_name: '',
			ticket_bascula_id: '',
			ticket_bascula_name: '',
			prefile_id: '',
			prefile_name: '',
			prefile_entry_number: ''
		};
		
		if (__sTipoPedimento == 'consolidado') {
			ajax_get_pedimento_simplificado();
		} else {
			ajax_get_pedimento_normal();
		}
	} catch (err) {		
		var strMensaje = 'fcn_agregar_factura() :: ' + err.message;
		show_modal_error(strMensaje);
    }
}

function fcn_eliminar_duplicados_select(oData) {
	try {
		if (oData.length > 0) {
			var table = oTableFacturasGrid.DataTable();
			$.each(table.rows().nodes(), function(index, item) {
				var result = $.grep(oData, function(e){ return e.text == table.row(index).data().factura; });
				if (result.length > 0) {
					oData.splice(result, 1);
				}
			});
		}
		
		$("#isel_factura").empty().trigger('change');
		$("#isel_factura").select2({
			data: oData,
			theme: "bootstrap",
			width: "off",
			placeholder: "Seleccione una Opción"/*,
			tags: true,
			tokenSeparators: [',', ' ']*/
		});
		$("#isel_factura").val('').trigger('change');
	} catch (err) {		
		var strMensaje = 'fcn_eliminar_duplicados_select() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_relacion_docs_opciones(pOpt) {
	try {
		switch(pOpt) {
			case 'ver':
				window.open(__sURLDocsExpo + '/' + __aAppData.aSalidaData.relacion_docs_name,'_blank');
				break;
			case 'nuevo':
				$('#igpo_relacion_docs').show();
				$('#igpo_relacion_docs_btn').hide();
				break;
		}
	} catch (err) {		
		var strMensaje = 'fcn_relacion_docs_opciones() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_prefile_opciones(pOpt) {
	try {
		switch(pOpt) {
			case 'ver':
				window.open(__sURLDocsExpo + '/' + __aAppData.aSalidaData.prefile_name,'_blank');
				break;
			case 'nuevo':
				$('#igpo_prefile').show();
				$('#igpo_prefile_btn').hide();
				break;
		}
	} catch (err) {		
		var strMensaje = 'fcn_prefile_opciones() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_NOA_opciones(pOpt) {
	try {
		switch(pOpt) {
			case 'ver':
				window.open(__sURLDocsExpo + '/' + __aAppData.aSalidaData.notificacion_arribo_name,'_blank');
				break;
			case 'nuevo':
				$('#igpo_NOA').show();
				$('#igpo_NOA_btn').hide();
				break;
		}
	} catch (err) {		
		var strMensaje = 'fcn_NOA_opciones() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_solicitud_retiro_opciones(pOpt) {
	try {
		switch(pOpt) {
			case 'ver':
				window.open(__sURLDocsExpo + '/' + __aAppData.aSalidaData.solicitud_retiro_name,'_blank');
				break;
			case 'nuevo':
				$('#igpo_solicitud_retiro').show();
				$('#igpo_solicitud_retiro_btn').hide();
				break;
		}
	} catch (err) {		
		var strMensaje = 'fcn_solicitud_retiro_opciones() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_mostrar_modal_pedimento_simplificado() {
	try {
		var sMensaje = 'Ref: ' + __oDataNewRow.referencia + ' :: Factura ' + __oDataNewRow.factura;
		$('#modal_add_pedsimp_title').html(sMensaje);
		$('#modal_add_pedsimp').modal({ show: true });
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_pedimento_simplificado() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_mostrar_modal_pedimento_normal() {
	try {
		var sMensaje = 'Ref: ' + __oDataNewRow.referencia;
		$('#modal_add_pednormal_title').html(sMensaje);
		$('#modal_add_pednormal').modal({ show: true });
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_pedimento_normal() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_sel_tipo_salida_change(pOpt) {
	switch(pOpt) {
		case 'principal':
			var sTipoSalida = $('#isel_tipo_salida').val();
			if (sTipoSalida != '') {
				sTipoSalida = $("#isel_tipo_salida option:selected").text().toLowerCase();
				sTipoSalida = sTipoSalida.charAt(0).toUpperCase() + sTipoSalida.slice(1);
			}
			$('#ispan_tipo_salida_caja').html(sTipoSalida);			
			break;
		case 'editar':
			var sTipoSalida = $('#isel_edit_tipo_salida').val().toLowerCase();
			if (sTipoSalida != '') {
				sTipoSalida = sTipoSalida.charAt(0).toUpperCase() + sTipoSalida.slice(1);
			}
			$('#ispan_edit_tipo_salida_caja').html(sTipoSalida);			
			break;
	}
}

/********************************************/
/* DOCUMENTOS */
/********************************************/

/* Todos los documentos */
function fcn_docs_facturas_options(pType, pOpt) {
	try {
		var sUrlDocument = '';
		
		switch(pType) {
			case 'packlist':
				sUrlDocument = __oDataNewRow.packing_list_name;
				break;
			
			case 'cerOrigen':
				sUrlDocument = __oDataNewRow.certificado_origen_name;
				break;
				
			case 'ticketbas':
				sUrlDocument = __oDataNewRow.ticket_bascula_name;
				break;
				
			case 'prefile':
				var sSelect = 'prefile';
				if ($('#modal_edit_fac_doc').is(':visible')) {
					sSelect = 'edit_prefile';
				}
			
				if ($('#igpo_' + sSelect + '_docs_select').is(':visible')) {
					var aDocsData = $('#isel_' + sSelect + '_documentos').select2('data');
					if (aDocsData.length > 0) {
						sUrlDocument = aDocsData[0].text;
					} else {
						show_modal_error('Debe seleccionar un documento de la lista de prefiles');
						return;
					}
				} else {
					sUrlDocument = __oDataNewRow.prefile_name;
				}
				break;
		}
		
		switch(pOpt) {
			case 'ver':
				window.open(__sURLDocsExpo + '/' + sUrlDocument,'_blank');
				break;
			case 'nuevo':
				$('#igpo_edit_' + pType + '_docs').show();
				$('#igpo_edit_' + pType + '_docs_btn').hide();
				break;
		}
	} catch (err) {		
		var strMensaje = 'fcn_docs_facturas_options() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_mdl_edit_fac_doc_mensaje');
    }
}

function fcn_docs_facturas_prefile_cancel() {
	try {
		var sSelectGroup = 'prefile';
		if ($('#modal_edit_fac_doc').is(':visible')) {
			sSelectGroup = 'edit_prefile';
		}
		
		$('#igpo_' + sSelectGroup + '_docs').show();
		$('#igpo_' + sSelectGroup + '_docs_btn').hide();
		$('#igpo_' + sSelectGroup + '_docs_select').hide();
	} catch (err) {		
		var strMensaje = 'fcn_docs_facturas_prefile_cancel() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_mdl_edit_fac_doc_mensaje');
    }
}

function fcn_docs_facturas_subir() {
	try {
		if ($('#igpo_edit_packlist_docs').is(':visible')) {
			__oDataNewRow.packing_list_id = '';
			__oDataNewRow.packing_list_name = '';
		}
		
		if ($('#igpo_edit_cerOrigen_docs').is(':visible')) {
			__oDataNewRow.certificado_origen_id = '';
			__oDataNewRow.certificado_origen_name = '';
		}
		
		if ($('#igpo_edit_ticketbas_docs').is(':visible')) {
			__oDataNewRow.ticket_bascula_id = '';
			__oDataNewRow.ticket_bascula_name = '';
		}
		
		if ($('#igpo_edit_prefile_docs').is(':visible')) {
			__oDataNewRow.prefile_id = '';
			__oDataNewRow.prefile_name = '';
		}
		
		ajax_subir_documentos();
	} catch (err) {		
		var strMensaje = 'fcn_docs_facturas_subir() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_mdl_edit_fac_doc_mensaje');
    }
}


/*DEPRECIADOS, VERIFICAR SI NO SE ESTAN USANDO ANTES DE CORRAR LAS FUNCIONES ATTE: JUAN CARLOS*/
function fcn_documentos_tipo_change() {
	try {
		var sDocumentoTipo = $('#isel_documento_tipo').val();
		
		switch(sDocumentoTipo) {
			case 'PRE':
				$('#ilbl_documento_referencia').html('Entry Number');
				$('#ilbl_documento_referencia').show();
				
				$('#itxt_documento_referencia').val('');
				$('#itxt_documento_referencia').show();
				
				$('#itxt_documento_referencia').mask("***-9999999-9");
				break;
			
			default:
				$('#ilbl_documento_referencia').hide();
				$('#itxt_documento_referencia').hide();
				
				$('#itxt_documento_referencia').unmask();
				$('#itxt_documento_referencia').val('');
		}
	} catch (err) {		
		var strMensaje = 'fcn_documentos_tipo_change() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_subir_documentos() {
	try {
		var oData = new FormData();
		var sDocumentoTipo = $('#isel_documento_tipo').val();
		var sDocumentoReferencia = $('#itxt_documento_referencia').val().toUpperCase();
		var nArchivos = 0;
		
		$.each($('#ifile_documento').fileinput('getFileStack'), function(i, file) {
			oData.append('oPdfFile-'+i, file);
			nArchivos += 1;
		});
		
		if (nArchivos == 0) {
			show_custom_function_error('Debe seleccionar un documento', 'idiv_message');
			return;
		}
		
		if (sDocumentoTipo == 'PRE') {
			if (sDocumentoReferencia == '') {
				show_custom_function_error('Debe ingresar el Entry number del prefile en el campo referencia', 'idiv_message');
				return;
			}			
		}
		
		oData.append('action', 'subir_documentos');
		oData.append('sDocumentoTipo', sDocumentoTipo);
		oData.append('sDocumentoReferencia', sDocumentoReferencia);
		oData.append('nUniqueId', __aAppData.nUniqueId);
		oData.append('nArchivos', nArchivos);
		
		ajax_subir_documentos(oData);
	} catch (err) {		
		var strMensaje = 'fcn_subir_documentos() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_mostrar_modal_asignar_documentos() {
	try {
		__aSelFacturas = new Array();
		$('#modal_asig_doc').modal({ show: true });
	} catch (err) {		
		var strMensaje = 'fcn_mostrar_modal_pedimento_normal() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_ver_documento(sName) {
	try {
		sName = ((sName == null || sName == undefined)? '': sName);
		
		if (sName == '') {
			var aDocsData = $('#isel_mdl_asig_doc_documentos').select2('data');
			if (aDocsData.length > 0) {
				window.open(__sURLDocsExpo + '/' + aDocsData[0].text,'_blank');
			}
		} else {
			window.open(__sURLDocsExpo + '/' + sName,'_blank');
		}
	} catch (err) {		
		var strMensaje = 'fcn_ver_documento() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

function fcn_asignar_documentos() {
	try {
		var aDocsData = $('#isel_mdl_asig_doc_documentos').select2('data');
		if (aDocsData.length) {
			if (__aSelFacturas.length > 0) {
				$.each(__aSelFacturas, function(index, item) {
					var sReferencia = item.split('|')[0];
					var sFactura = item.split('|')[1];
					
					var table = oTableFacturasGrid.DataTable();
					$.each(table.rows().nodes(), function(index, item) {
						var oDataRow = table.row(index).data();
						
						if (oDataRow.referencia == sReferencia && 
							oDataRow.factura == sFactura) {
							switch($('#isel_mdl_asig_doc_documento_tipo').val()) {
								case 'PKL':
									oDataRow.packing_list_id = aDocsData[0].id;
									break;
									
								case 'CDO':
									oDataRow.certificado_origen_id = aDocsData[0].id;
									break;
									
								case 'PRE':
									oDataRow.prefile_id = aDocsData[0].id;
									break;
									
								case 'TDB':
									oDataRow.ticket_bascula_id = aDocsData[0].id;
									break;
							}
							
							table.row(index).data(oDataRow).draw();
							return;
						}
					});
		
					//alert('id:' + aDocsData[0].id + ' Text:' + aDocsData[0].text);
				});
				
				__aSelFacturas = new Array();
				var aData = oTableFacturasGrid.dataTable().fnGetData();
				fcn_cargar_grid_facturas_docs(aData);
			} else {
				show_custom_function_error('Debe seleccionar por lo menos una factura', 'idiv_mdl_asig_doc_mensaje');
			}
		} else {
			show_custom_function_error('Debe seleccionar un documento valido', 'idiv_mdl_asig_doc_mensaje');
		}
	} catch (err) {		
		var strMensaje = 'fcn_asignar_documentos() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }
}

/**********************************************************************************************************************
    AJAX
********************************************************************************************************************* */

/* ..:: Obtenemos datos de la referencia ::.. */
function ajax_get_referencia_casa() {
	try {		
		show_custom_function_error('', 'idiv_message');
		
		var sReferencia = $('#itxt_referencia').val();
		
		if (!sReferencia.trim()) { 
			show_custom_function_error('Debe ingresar una referencia', 'idiv_message');
			return;
		}
		
		var oData = {			
			action: 'buscareferencia_casa',
			sReferencia: sReferencia.toUpperCase()
		};
		
		$.ajax({
            type: "POST",
            url: './func/salidaExpoFunc.php',
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				fcn_display_referencia_loading(true);
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					fcn_display_referencia_loading(false);
					if (respuesta.Codigo == '1'){
						if (respuesta.bExisteCasa == false) {
							show_custom_function_error('La referencia [' + respuesta.sReferencia + '] no existe en CASA.', 'idiv_message');
							return;
						}
						
						if (respuesta.oClienteData.length > 0) {
							$('#isel_cliente').select2("trigger", "select", {
								data: respuesta.oClienteData[0]
							});
						}
						
						$("#isel_factura").empty().trigger('change');
						$("#isel_factura").select2({
							data: respuesta.aFacturas,
							theme: "bootstrap",
							width: "off",
							placeholder: "Seleccione una Opción"/*,
							tags: true,
							tokenSeparators: [',', ' ']*/
						});
						$("#isel_factura").val('').trigger('change');
						
						$('#itxt_patente').val(respuesta.sPatente);
						$('#itxt_pedimento').val(respuesta.sPedimento);
						__sTipoPedimento = respuesta.sTipoPedimento
						
						$('#itxt_factura').focus();
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_custom_function_error(strMensaje, 'idiv_message');
					}
				}else{
					show_custom_function_error(strSessionMessage, 'idiv_message');
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_custom_function_error(strMensaje, 'idiv_message');
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_referencia_casa() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_message');
    }    
}

/* ..:: Verificamos que exista el pedimento simplificado ::.. */
function ajax_get_pedimento_simplificado() {
	try {		
		show_custom_function_error('', 'idiv_message');
		
		var oData = {			
			action: 'buscapedimento_simplificado',
			sReferencia: __oDataNewRow.referencia, 
			sNumRemPed: __oDataNewRow.num_rem_ped, 
			sUuid: __oDataNewRow.uuid
		};
		
		$.ajax({
            type: "POST",
            url: './func/salidaExpoFunc.php',
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informacion, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						if (respuesta.bExistePedSimp == false) {
							fcn_mostrar_modal_pedimento_simplificado();
						} else {
							ajax_subir_documentos();
							//oTableFacturasGrid.DataTable().row.add(__oDataNewRow).draw(false);
						}
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				}else{
					show_modal_error(strSessionMessage);
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_pedimento_simplificado() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Verificamos que exista el pedimento simplificado ::.. */
function ajax_get_pedimento_normal() {
	try {		
		show_custom_function_error('', 'idiv_message');
		
		var oData = {			
			action: 'buscapedimento_normal',
			sReferencia: __oDataNewRow.referencia, 
			sNumRemPed: __oDataNewRow.num_rem_ped, 
			sUuid: __oDataNewRow.uuid
		};
		
		$.ajax({
            type: "POST",
            url: './func/salidaExpoFunc.php',
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Consultando informacion, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						if (respuesta.bExistePedNormal == false) {
							fcn_mostrar_modal_pedimento_normal();
						} else {
							ajax_subir_documentos();
							//oTableFacturasGrid.DataTable().row.add(__oDataNewRow).draw(false);
						}
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				}else{
					show_modal_error(strSessionMessage);
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_pedimento_normal() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: subimos el pedimento simplificado ::.. */
function ajax_set_pedimento_simplificado() {
	try {		
		show_custom_function_error('', 'modal_add_pedsimp_mensaje');
		
		var oData = new FormData();
		
		var oPdfPedSimp = document.getElementById('ifile_add_pedsimp');
		if (oPdfPedSimp.files[0]){
			oData.append('oPdfPedSimp', oPdfPedSimp.files[0]);
		} else {
			show_custom_function_error('Debes seleccionar un documento con Pedimento simplificado.', 'modal_add_pedsimp_mensaje');
			return;
		}
			
		oData.append('action', 'guardarpedimento_simplificado');
		oData.append('sReferencia', __oDataNewRow.referencia);
		oData.append('sNumRemPed', __oDataNewRow.num_rem_ped);
		
		$.ajax({
            type: "POST",
            url: './func/salidaExpoFunc.php',
			data: oData,
			contentType: false,
			cache: false,
			processData:false,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Procesando informacion, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						show_custom_function_ok(respuesta.Mensaje, 'modal_add_pedsimp_mensaje');
						$('#modal_add_pedsimp_btn_ok').prop('disabled', true);
						
						if (__bDataNewRow) { 
							ajax_subir_documentos();
							//oTableFacturasGrid.DataTable().row.add(__oDataNewRow).draw(false);
						}
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_custom_function_error(strMensaje, 'modal_add_pedsimp_mensaje');
					}
				}else{
					show_custom_function_error(strSessionMessage, 'modal_add_pedsimp_mensaje');
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_custom_function_error(strMensaje, 'modal_add_pedsimp_mensaje');
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_referencia_casa() :: ' + err.message;
		show_custom_function_error(strMensaje, 'modal_add_pedsimp_mensaje');
    }    
}

/* ..:: subimos el pedimento normal ::.. */
function ajax_set_pedimento_normal() {
	try {		
		show_custom_function_error('', 'modal_add_pednormal_mensaje');
		
		var oData = new FormData();
		
		var oPdfPedNormal = document.getElementById('ifile_add_pednormal');
		if (oPdfPedNormal.files[0]){
			oData.append('oPdfPedNormal', oPdfPedNormal.files[0]);
		} else {
			show_custom_function_error('Debes seleccionar un documento con la copia del Pedimento para el transportista.', 'modal_add_pednormal_mensaje');
			return;
		}
			
		oData.append('action', 'guardarpedimento_normal');
		oData.append('sReferencia', __oDataNewRow.referencia);
		
		$.ajax({
            type: "POST",
            url: './func/salidaExpoFunc.php',
			data: oData,
			contentType: false,
			cache: false,
			processData:false,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Procesando informacion, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						show_custom_function_ok(respuesta.Mensaje, 'modal_add_pednormal_mensaje');
						$('#modal_add_pednormal_btn_ok').prop('disabled', true);
						
						if (__bDataNewRow) { 
							ajax_subir_documentos();
							//oTableFacturasGrid.DataTable().row.add(__oDataNewRow).draw(false);
						}
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_custom_function_error(strMensaje, 'modal_add_pednormal_mensaje');
					}
				}else{
					show_custom_function_error(strSessionMessage, 'modal_add_pednormal_mensaje');
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_custom_function_error(strMensaje, 'modal_add_pednormal_mensaje');
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_referencia_casa() :: ' + err.message;
		show_custom_function_error(strMensaje, 'modal_add_pednormal_mensaje');
    }    
}

/* ..:: Creamos la salida ::.. */
function ajax_set_salida() {
	try {		
		show_custom_function_error('', 'idiv_message');
		
		var oData = new FormData();
		
		var sFecha = fcn_get_date($('#itxt_fecha'));
		var sFerrocarril = (($("#ickb_ferrocarril").is(':checked'))? 'X': '');
		//var sTipoSalida = $('#isel_tipo_salida').val();
		//var sTipoSalidaCaja = $('#itxt_tipo_salida_caja').val().toUpperCase();
		var sEntregarEnDir = $('#itxt_entregar_en_direccion').val().toUpperCase();
		var sCrucesEnSalida = $('#itxt_cruces_en_salida').val();
		var sUrgente = (($("#ickb_urgente").is(':checked'))? 'SI': 'NO');
		var sLeyenda = (($("#ickb_leyenda_trans").is(':checked'))? 'X': '');	
		var sHoraEntrada = fcn_get_hour($('#itxt_hora_entrega'));
		var sRecibio = $('#itxt_recibio').val().toUpperCase();
		var sNumeroViaje = $('#itxt_numero_viaje').val().toUpperCase();
		var sIndicaciones = $('#itxt_indicaciones').val().toUpperCase();
		var sObservaciones = $('#itxt_observaciones').val().toUpperCase();
		
		var sLineaTranspId = (($('#isel_lineast').val() == null)? '' : $('#isel_lineast').val());
		var sLineaTranspName = '';
		var sAduanaId = (($('#isel_aduana').val() == null)? '' : $('#isel_aduana').val());
		var sTransferId = (($('#isel_transfer').val() == null)? '' : $('#isel_transfer').val());
		var sTransferName = '';
		var sEntregarEnId = (($('#isel_entregar_en').val() == null)? '' : $('#isel_entregar_en').val());
		var sEntregarEnName = '';
		
		if (!sLineaTranspId.trim()) { 
			show_custom_function_error('Debe seleccionar una linea transportista', 'idiv_message');
			return;
		} else {
			sLineaTranspName = $('#isel_lineast').select2('data')[0].text;
		}
		
		if (!sAduanaId.trim()) { 
			show_custom_function_error('Debe seleccionar una Aduana', 'idiv_message');
			return;
		}
		
		if (!sTransferId.trim()) { 
			show_custom_function_error('Debe seleccionar un Transfer', 'idiv_message');
			return;
		} else {
			sTransferName = $('#isel_transfer').select2('data')[0].text;
		}
		
		if (!sEntregarEnId.trim()) { 
			show_custom_function_error('Debe seleccionar un lugar de entrega', 'idiv_message');
			return;
		} else {
			sEntregarEnName = $('#isel_entregar_en').select2('data')[0].text;
		}
		
		/*=====================================================*/
		/* Documentos */
		if ($('#igpo_relacion_docs').is(':visible')) {
			var oPdfRelDocs = document.getElementById('ifile_relacion_docs');
			if (oPdfRelDocs.files[0]){
				oData.append('oPdfRelDocs', oPdfRelDocs.files[0]);
			} else {
				show_custom_function_error('Debes ingresar una relación de documentos.', 'idiv_message');
				return;
			}
		}
		
		if ($('#igpo_NOA').is(':visible')) { 
			var oPdfNOA = document.getElementById('ifile_NOA');
			if (oPdfNOA.files[0]){
				oData.append('oPdfNOA', oPdfNOA.files[0]);
				oData.append('bBorrarNOA', 'NO');
			} else {
				oData.append('bBorrarNOA', 'SI');
			}
		} else {
			oData.append('bBorrarNOA', 'NO');
		}
		
		if ($('#igpo_solicitud_retiro').is(':visible')) { 
			var oPdfSolRet = document.getElementById('ifile_solicitud_retiro');
			if (oPdfSolRet.files[0]){
				oData.append('oPdfSolRet', oPdfSolRet.files[0]);
				oData.append('bBorrarSolRet', 'NO');
			} else {
				oData.append('bBorrarSolRet', 'SI');
			}
		} else {
			oData.append('bBorrarSolRet', 'NO');
		}
		/*=====================================================*/
		
		var table = oTableFacturasGrid.DataTable();
		if (table.data().count() == 0) {
			show_custom_function_error('Debe ingresar por lo menos una factura', 'idiv_message');
			return;
		}

		var aFacturas = [];
		$.each(table.rows().nodes(), function(index, item) {
			var oDataFact = table.row(index).data();
			aFacturas.push(oDataFact);
		});
				
		oData.append('action', ((__aAppData.sTask == 'insertar')? 'insertar_salida' : 'editar_salida'));
		oData.append('nSalidaNumero', __aAppData.nSalidaNumero);
		oData.append('sFecha', sFecha);
		oData.append('sFerrocarril', sFerrocarril);
		//oData.append('sTipoSalida', sTipoSalida);
		//oData.append('sTipoSalidaCaja', sTipoSalidaCaja);
		oData.append('sEntregarEnDir', sEntregarEnDir);
		oData.append('sCrucesEnSalida', sCrucesEnSalida);
		oData.append('sUrgente', sUrgente);
		oData.append('sLeyenda', sLeyenda);
		oData.append('sHoraEntrada', sHoraEntrada);
		oData.append('sRecibio', sRecibio);
		oData.append('sNumeroViaje', sNumeroViaje);
		oData.append('sIndicaciones', sIndicaciones);
		oData.append('sObservaciones', sObservaciones);
		oData.append('sLineaTranspId', sLineaTranspId);
		oData.append('sLineaTranspName', sLineaTranspName);
		oData.append('sAduanaId', sAduanaId);
		oData.append('sTransferId', sTransferId);
		oData.append('sTransferName', sTransferName);
		oData.append('sEntregarEnId', sEntregarEnId);
		oData.append('sEntregarEnName', sEntregarEnName);
		oData.append('aFacturas', JSON.stringify(aFacturas));
		oData.append('aDataDelRow', JSON.stringify(__aDataDelRow));
		
		$.ajax({
            type: "POST",
            url: './func/salidaExpoFunc.php',
			data: oData,
			contentType: false,
			cache: false,
			processData:false,
			
            beforeSend: function (dataMessage) {
				show_load_config(true, ((__aAppData.sTask == 'insertar')? 'Creando' : 'Actualizando ') + ' salida, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						var oFunctionOk = function () { 
							__aAppData.nSalidaNumero = respuesta.nSalidaNumero;
							fcn_imprimir_salida(__aAppData.sTask);
							
							setTimeout(function () { window.close(); }, 2000);
						};
						
						$('#ibtn_guardar_salida').hide();
						show_modal_ok(respuesta.Mensaje, oFunctionOk);
					} else if (respuesta.Codigo == '100') {
						__bDataNewRow = false;
						__oDataNewRow = {
							referencia: respuesta.sReferencia,
							num_rem_ped: respuesta.sNumRemPed,
							factura: respuesta.sFactura
						}
		
						fcn_mostrar_modal_pedimento_simplificado();
					} else if (respuesta.Codigo == '101') {
						__bDataNewRow = false;
						__oDataNewRow = {
							referencia: respuesta.sReferencia,
							num_rem_ped: respuesta.sNumRemPed,
							factura: respuesta.sFactura
						}
		
						fcn_mostrar_modal_pedimento_normal();
					} else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_custom_function_error(strMensaje, 'idiv_message');
					}
				}else{
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_salida() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Eliminamos factura ::.. */
function ajax_set_eliminar_factura() {
	try {		
		show_custom_function_error('', 'idiv_message');
		
		var oData = new FormData();
		oData.append('action', 'eliminar_factura');		
		oData.append('nSalidaNumero', ((__aAppData.sTask == 'insertar')? '-1' : __aAppData.nSalidaNumero));		
		oData.append('aDataDelRow', JSON.stringify(__aDataDelRow));

		//return;
		$.ajax({
            type: "POST",
            url: './func/salidaExpoFunc.php',
			data: oData,
			contentType: false,
			cache: false,
			processData:false,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'Eliminando Factura, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					__aDataDelRow = new Array();
					if (respuesta.Codigo == '1'){
						oTableFacturasGrid.DataTable().row(__oTrDtDelete).remove().draw();
						show_custom_function_ok(respuesta.Mensaje, 'idiv_message');
					} else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_custom_function_error(strMensaje, 'idiv_message');
					}
				}else{
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_set_salida() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/********************************************/
/* DOCUMENTOS */
/********************************************/

/* ..:: Subimos los documentos ::.. */
function ajax_subir_documentos() {
	try {		
		var oPdfPackingList;
		var oPdfCerOrigen;
		var oPdfTicketBascula;
		var oPdfPrefile;		
		var sTxtEntryNumber = 'itxt_prefile_entry_number';
		var sDivMensaje = 'idiv_message';
		var sSelTipoSalida = 'isel_tipo_salida';
		var sTxtTipoSalidaCaja = 'itxt_tipo_salida_caja';
		
		if ($('#modal_edit_fac_doc').is(':visible')) {
			oPdfPackingList = document.getElementById('ifile_edit_packlist').files[0];
			oPdfCerOrigen = document.getElementById('ifile_edit_cerOrigen').files[0];
			oPdfTicketBascula = document.getElementById('ifile_edit_ticketbas').files[0];
			oPdfPrefile = document.getElementById('ifile_edit_prefile').files[0];
			
			sTxtEntryNumber = 'itxt_edit_prefile_entry_number';
			sDivMensaje = 'idiv_mdl_edit_fac_doc_mensaje';
			
			sSelTipoSalida = 'isel_edit_tipo_salida';
			sTxtTipoSalidaCaja = 'itxt_edit_tipo_salida_caja';
		} else {
			oPdfPackingList = document.getElementById('ifile_packlist').files[0];
			oPdfCerOrigen = document.getElementById('ifile_cerOrigen').files[0];
			oPdfTicketBascula = document.getElementById('ifile_ticketbas').files[0];
			oPdfPrefile = document.getElementById('ifile_prefile').files[0];
		}
		
		show_custom_function_error('', sDivMensaje);
		
		/************************************************************/
		
		var sTipoSalida = (($('#' + sSelTipoSalida).val() == null)? '' : $('#' + sSelTipoSalida).val());
		var sTipoSalidaCaja = $('#' + sTxtTipoSalidaCaja).val().toUpperCase();
		if (!sTipoSalida.trim()) { 
			show_custom_function_error('Debe seleccionar un tipo de salida', sDivMensaje);
			return;
		}
		
		if (!sTipoSalidaCaja.trim()) { 
			show_custom_function_error('Debe ingresar un numero de ' + sTipoSalida.toLowerCase(), sDivMensaje);
			return;
		}
		
		__oDataNewRow.tiposalida = sTipoSalida;
		__oDataNewRow.caja = sTipoSalidaCaja;
		
		/************************************************************/
		
		var oData = new FormData();
		
		if ($.type(oPdfPackingList) == 'object') {
			oData.append('PackingList', oPdfPackingList);
		}
		if ($.type(oPdfCerOrigen)  == 'object') {
			oData.append('CerOrigen',oPdfCerOrigen);
		}
		if ($.type(oPdfTicketBascula) == 'object') {
			oData.append('TicketBascula', oPdfTicketBascula);
		}
		if ($.type(oPdfPrefile) == 'object') {
			var sEntryNumber = $('#' + sTxtEntryNumber).val().toUpperCase();
			if (sEntryNumber == '') {
				show_custom_function_error('Debe ingresar el Entry number del prefile', sDivMensaje);
				return;
			}
			
			oData.append('sDocumentoReferencia', sEntryNumber);
			oData.append('Prefile', oPdfPrefile);
		} else {
			var sSelect = 'prefile';
			if ($('#modal_edit_fac_doc').is(':visible')) {
				sSelect = 'edit_prefile';
			}
			
			if ($('#igpo_' + sSelect + '_docs_select').is(':visible')) {
				var sEntryNumber = $('#' + sTxtEntryNumber).val().toUpperCase();
				if (sEntryNumber == '') {
					show_custom_function_error('Debe ingresar el Entry number del prefile', sDivMensaje);
					return;
				}
				
				var aDocsData = $('#isel_' + sSelect + '_documentos').select2('data');
				if (aDocsData.length > 0) {
					__oDataNewRow.prefile_id = aDocsData[0].id;
					__oDataNewRow.prefile_name = aDocsData[0].text;
					__oDataNewRow.prefile_entry_number = sEntryNumber;
				} else {
					show_custom_function_error('Debe seleccionar un documento de la lista de prefiles', sDivMensaje);
					return;
				}
			}
		}
				
		oData.append('action', 'subir_documentos');
		oData.append('sTask', __aAppData.sTask);
		oData.append('nUniqueId', __aAppData.nUniqueId);
					
		$.ajax({
            type: "POST",
            url: './func/salidaExpoFunc.php',
			data: oData,
			contentType: false,
			cache: false,
			processData:false,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				setTimeout(function () {
					show_load_config(true, 'Subiendo informaci&oacute;n, espere un momento por favor...');
				},500);
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					
					setTimeout(function () {
						show_load_config(false);
					},500);
					
					if (respuesta.Codigo == '1'){
						if (respuesta.pkl != '') {
							__oDataNewRow.packing_list_id = respuesta.pkl;
							__oDataNewRow.packing_list_name = respuesta.pkl_name;
						}
						
						if (respuesta.cdo != '') {
							__oDataNewRow.certificado_origen_id = respuesta.cdo;
							__oDataNewRow.certificado_origen_name = respuesta.cdo_name;
						}
						
						if (respuesta.tdb != '') {
							__oDataNewRow.ticket_bascula_id = respuesta.tdb;
							__oDataNewRow.ticket_bascula_name = respuesta.tdb_name;
						}
						
						if (respuesta.pre != '') {
							__oDataNewRow.prefile_id = respuesta.pre;
							__oDataNewRow.prefile_name = respuesta.pre_name;
							__oDataNewRow.prefile_entry_number = respuesta.pre_entry;
						}
						
						if ($('#modal_edit_fac_doc').is(':visible')) {							
							var table = oTableFacturasGrid.DataTable();
							$.each(table.rows().nodes(), function(index, item) {
								var oDataRow = table.row(index).data();
								
								if (oDataRow.referencia == __oDataNewRow.referencia && 
									oDataRow.factura == __oDataNewRow.factura) {
									
									table.row(index).data(oDataRow).draw();
									return;
								}
							});
							
							$('#modal_edit_fac_doc').modal('hide');
						} else {
							oTableFacturasGrid.DataTable().row.add(__oDataNewRow).draw(false);
						}
						
						$('#itxt_prefile_entry_number').val('');
						fcn_inicializar_factura_ifiles();
					} else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				}else{
					show_modal_error(strSessionMessage);					
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_subir_documentos() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: buscar prefiles con el entry number seleccionado ::.. */
function ajax_get_documentos_entry_number() {
	try {		
		var sEntryNumber = $('#itxt_prefile_entry_number').val().trim();
		var sDivMensaje = 'idiv_message';
		if ($('#modal_edit_fac_doc').is(':visible')) { 
			sEntryNumber = $('#itxt_edit_prefile_entry_number').val().trim();
			sDivMensaje = 'idiv_mdl_edit_fac_doc_mensaje';
		}
		
		if (sEntryNumber == '') {
			show_custom_function_error('Debe ingresar el Entry number', sDivMensaje);
			return;
		}
		
		var oData = {			
			action: 'buscar_documentos_entry_number',
			sEntryNumber: sEntryNumber,
			nUniqueId: __aAppData.nUniqueId
		};
		
		$.ajax({
            type: "POST",
            url: './func/salidaExpoFunc.php',
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'consultando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						var sSelect = 'isel_prefile_documentos';
						var sSelectGroup = 'prefile';
						if ($('#modal_edit_fac_doc').is(':visible')) { 
							sSelect = 'isel_edit_prefile_documentos';
							sSelectGroup = 'edit_prefile';
						}
						
						$('#igpo_' + sSelectGroup + '_docs').hide();
						$('#igpo_' + sSelectGroup + '_docs_btn').hide();
						$('#igpo_' + sSelectGroup + '_docs_select').show();
						
						/*************************************************/
						
						$("#" + sSelect).empty().trigger('change');
						$("#" + sSelect).select2({
							data: respuesta.aDocumentos,
							theme: "bootstrap",
							width: "off",
							placeholder: "Seleccione un documento"
						});
						$("#" + sSelect).val('').trigger('change');
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_modal_error(strMensaje);
					}
				}else{
					show_modal_error(strSessionMessage);
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_modal_error(strMensaje);
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_documentos() :: ' + err.message;
		show_modal_error(strMensaje);
    }    
}

/* ..:: Obtenemos documentos ::.. */
function ajax_get_documentos() {
	try {		
		show_custom_function_error('', 'idiv_mdl_asig_doc_mensaje');
		
		var sDocumentoTipo = $('#isel_mdl_asig_doc_documento_tipo').val();
		
		var oData = {			
			action: 'buscar_documentos',
			sDocumentoTipo: sDocumentoTipo,
			nUniqueId: __aAppData.nUniqueId,
			nSalidaNumero: __aAppData.nSalidaNumero
		};
		
		$.ajax({
            type: "POST",
            url: './func/salidaExpoFunc.php',
			data: oData,
			timeout: 30000,

            beforeSend: function (dataMessage) {
				show_load_config(true, 'consultando informaci&oacute;n, espere un momento por favor...');
            },
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					show_load_config(false);
					if (respuesta.Codigo == '1'){
						$("#isel_mdl_asig_doc_documentos").empty().trigger('change');
						$("#isel_mdl_asig_doc_documentos").select2({
							data: respuesta.aDocumentos,
							theme: "bootstrap",
							width: "off",
							placeholder: "Seleccione un documento"
						});
						$("#isel_mdl_asig_doc_documentos").val('').trigger('change');
					}else{
						var strMensaje = respuesta.Mensaje + respuesta.Error;
						show_custom_function_error(strMensaje, 'idiv_mdl_asig_doc_mensaje');
					}
				}else{
					show_custom_function_error(strSessionMessage, 'idiv_mdl_asig_doc_mensaje');
					setTimeout(function () {window.location.replace('../logout.php');},4000);
				}				
			},
			error: function(a,b){
				var strMensaje = a.status+' [' + a.statusText + ']';
				show_custom_function_error(strMensaje, 'idiv_mdl_asig_doc_mensaje');
			}
        });
    } catch (err) {
		var strMensaje = 'ajax_get_documentos() :: ' + err.message;
		show_custom_function_error(strMensaje, 'idiv_mdl_asig_doc_mensaje');
    }    
}

/*********************************************************************************************************************************
** DOWNLOAD FUNCTIONS                                                                                                           **
**********************************************************************************************************************************/

function fcn_imprimir_salida(sTask) {
	if (sTask == null || sTask == undefined) {
		sTask = '';
	}
		
	var oForm = document.createElement("form");
	oForm.target = 'data';
	oForm.method = 'GET'; // or "post" if appropriate
	oForm.action = 'showCarta_Instrucciones.php';

	var oInput = document.createElement("input");
	oInput.type = "text";
	oInput.name = "solicitud";
	oInput.value = __aAppData.nSalidaNumero;
	oForm.appendChild(oInput);
	
	if (sTask != '') {
		var oInput = document.createElement("input");
		oInput.type = "text";
		oInput.name = "env";
		oInput.value = sTask;
		oForm.appendChild(oInput);
	}

	document.body.appendChild(oForm);
	oForm.submit();
	$(oForm).remove();
}

/*********************************************************************************************************************************
** MESSAJE FUNCTIONS                                                                                                            **
*********************************************************************************************************************************/

/* ..:: Funcion que muestra el mensaje del loading  ::.. */
function show_load_config(bShow, sMensaje) {
	if (bShow) {
		if (sMensaje == null || sMensaje == undefined) {
			sMensaje = 'Consultando, espere un momento por favor...';
		}
		
		if ($('#modalloadconfig').is(':visible') == false) {
			$('#modalloadconfig').modal({ show: true, backdrop: 'static', keyboard: false });
		}
		$("#modalloadconfig_mensaje").html('<img src="../images/cargando.gif" height="16" width="16"/> ' + sMensaje);
	} else {
		setTimeout(function () {
			$('#modalloadconfig').modal('hide');
		},500);
	}
}

/* ..:: Funcion que muestra el mensaje de ok ::.. */
function show_modal_ok(sMensaje, oFunctionOk) {
	if (sMensaje == null || sMensaje == undefined) {
		sMensaje = '';
	}
	
    $('#modalmessagebox_ok_titulo').html('Exportaciones Salidas');
	$('#modalmessagebox_ok_mensaje').html('<i class="fa fa-check"></i> ' + sMensaje);

	if (oFunctionOk == null || oFunctionOk == undefined) {
		$('#modalmessagebox_btn_ok').off('click');
	} else {
		$('#modalmessagebox_btn_ok').on( "click", function() {
			setTimeout(function () {
				oFunctionOk();
			},500);
		} );
	}
	setTimeout(function () {
		$('#modalmessagebox_ok').modal({ show: true });
	},500);
}

/* ..:: Funcion que muestra el mensaje de error (lblError) ::.. */
function show_modal_error(sMensaje) {
	if (sMensaje == null || sMensaje == undefined) {
		sMensaje = '';
	}
	
	show_load_config(false);
	
    $('#modalmessagebox_error_span').html("ERROR :: Exportaciones Salidas");
	$('#modalmessagebox_error_mensaje').html('<i class="fa fa-ban"></i> ' + sMensaje);
	setTimeout(function () {
		$('#modalmessagebox_error').modal({ show: true });
	},500);
}

//Ejemplo oFunctionOk: se debe pasar una funcion de la siguiente manera "function () { Aqui la funcion o codigo a ejecutar }"
//Ejemplo oFunctionCancel: se debe pasar una funcion de la siguiente manera "function () { Aqui la funcion o codigo a ejecutar }"
function show_confirm(strTitle, strQuestion, oFunctionOk, oFunctionCancel) {
	if (strTitle == '') {
		strTitle = appName;
	}
	$('#modalconfirm_title').html(strTitle);
	$('#modalconfirm_mensaje').html('<i class="fa fa-exclamation-triangle"></i> ' + strQuestion);
	
	//Eliminamos evento click
	$('#modalconfirm_btn_ok').off( "click");
	$('#modalconfirm_btn_cancel').off( "click");
	
	//Reasignamos evento click Boton OK
	$('#modalconfirm_btn_ok').on( "click", function() {
		$('#modalconfirm').modal('hide');
		setTimeout(function () {
			oFunctionOk();
		},500);
	} );
	
	//Reasignamos evento click Boton Cancel
	if (oFunctionCancel == null || oFunctionCancel == undefined) {
		$('#modalconfirm_btn_cancel').on( "click", function() {
			$('#modalconfirm').modal('hide');
		});
	} else {
		$('#modalconfirm_btn_cancel').on( "click", function() {
			oFunctionCancel();
			$('#modalconfirm').modal('hide');
		});
	}	
		
	$('#modalconfirm').modal({ show: true });
}

/*******************************************/
/* MENSAJES PERSONALIZADOS */
/*******************************************/

/* ..:: Funcion muestra mensajes de error ::.. */
function show_custom_function_error(sMensaje, oDivDisplay, sStyle) {
	sMensaje = ((sMensaje == null || sMensaje == undefined)? '': sMensaje);
	oDivDisplay = ((oDivDisplay == null || oDivDisplay == undefined)? '': oDivDisplay);
	sStyle = ((sStyle == null || sStyle == undefined)? '': sStyle);

	if (oDivDisplay != '') {
		if (sMensaje != '') {
			var sHtml = '<div class="alert alert-danger" style="' + sStyle + '">';
			sHtml +=	'	 <strong>Error!</strong> ' + sMensaje;
			sHtml +=    '</div>';	
			
			$('#' + oDivDisplay).html(sHtml).show();
		} else {
			$('#' + oDivDisplay).hide();
		}
	} else {		
		show_modal_error('No se proporciono contenedor para el mensaje!');
	}
}

/* ..:: Funcion muestra mensajes de ok ::.. */
function show_custom_function_ok(sMensaje, oDivDisplay, sStyle) {
	sMensaje = ((sMensaje == null || sMensaje == undefined)? '': sMensaje);
	oDivDisplay = ((oDivDisplay == null || oDivDisplay == undefined)? '': oDivDisplay);
	sStyle = ((sStyle == null || sStyle == undefined)? '': sStyle);

	if (oDivDisplay != '') {
		if (sMensaje != '') {
			var sHtml = '<div class="alert alert-success" style="' + sStyle + '">';
			sHtml +=	'	 <strong>Exito!</strong> ' + sMensaje;
			sHtml +=    '</div>';	
			
			$('#' + oDivDisplay).html(sHtml).show();
		} else {
			$('#' + oDivDisplay).hide();
		}
	} else {		
		show_modal_error('No se proporciono contenedor para el mensaje!');
	}
}

/* ..:: Funcion muestra mensajes de info ::.. */
function show_custom_function_info(sMensaje, oDivDisplay, sStyle) {
	sMensaje = ((sMensaje == null || sMensaje == undefined)? '': sMensaje);
	oDivDisplay = ((oDivDisplay == null || oDivDisplay == undefined)? '': oDivDisplay);
	sStyle = ((sStyle == null || sStyle == undefined)? '': sStyle);

	if (oDivDisplay != '') {
		if (sMensaje != '') {
			var sHtml = '<div class="alert alert-info" style="' + sStyle + '">';
			sHtml +=	'	 <strong>Info!</strong> ' + sMensaje;
			sHtml +=    '</div>';	
			
			$('#' + oDivDisplay).html(sHtml).show();
		} else {
			$('#' + oDivDisplay).hide();
		}
	} else {		
		show_modal_error('No se proporciono contenedor para el mensaje!');
	}
}