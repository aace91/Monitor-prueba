/* 
* Copyright (c) 2018 DEL BRAVO. - all right reserved
*/
	var appName = 'Monitor :: Fracciones Regla 8va.';
	var strSessionMessage = 'La sesión del usuario ha terminado, por favor acceda de nuevo.';
	var sGifLoader = '<i class="fa fa-refresh fa-spin" aria-hidden="true"></i>&nbsp;';

	var oTableFracciones = null;
	var oTablePartidas = null;
	var oTableParReferencia = null;
	var oTableReferencias = null;

	var sIdFraccion = '';
	var sAccion = '';
	var sNumReferencia = '';
	var sNumReferenciaParCierre = '';
	var sNumReferenciaCerrar = '';
	var sPartidasRef = Array();
	var sPartidasRefCerrar = Array ();
	var aFraccionesDisp = Array();
	var aPartidasPedCierre = Array ();
	var sidFraccionhist = '';
	var bSelRem = true; //crear opciones del select remesa

	var sidDiv_BusRef = 'idiv_menseje_mdl_buscar_proref';
	var sidDiv_BusRef_Cerrar = 'idiv_menseje_mdl_buscar_cerrar_ref';
	var sIdDiv_BusRef_ParPed_Cierre = 'idiv_menseje_parpedimento_cierre';

	var sAplConsFact = '';
	var sAplConsPart = '';
	var sAplFraccion = '';
	var sAplDescripcion = '';
	var sAplCantidad = '';
	var sAplValor = '';

	var sTipoAplicacion = '';

	var sItemActual = 0;
	var sErroresAplR8vaArray = '';
	//var sIdFraccionHist = 0 ;

	var sIdFraccion = '';

	var sConsFactCRef = '';
	var sConsPartCRef = '';
	
	var nItemParPedCierre = 0;
	//var nParPedCierre = 0;
	var aParFacFracci = Array();
	var nItemParFacEdit = 0;
	var sActParFacFracci = '';
	
	var aParFacAddPar = Array();
	
	var sConsFacParNva = 0;
	var sConsParParNva = 0;
	
	/* ********************************************************************************************************************************
	** MAIN                                                                                                      **
	******************************************************************************************************************************* **/
	$(document).ready(function () {
		//Esta funcion se dispara cuando se terminan de cargar todos los elementos de la pagina { .js, .css, images, etc. }
		application_load();
	});

	function application_load() {
		application_run();
	}

	function application_run() {
		try {

			fcn_cargar_grid_fracciones();

			fcn_cargar_grid_partidas_referencias();

			fcn_cargar_grid_referencias();

			fnc_carga_grid_partidas_8va_manual_referencia();
			
			fnc_carga_grid_partidas_pedimento_cierre();
			
			$('#idt_mdl_fecha_vence').datepicker({
				todayHighlight:true,
				autoclose: true
				//clearBtn: true
			}).data('datepicker');

			$("#idt_mdl_fecha_vence").datepicker("setDate", new Date());

			//Clases para textbox numericos
			$(".integer").numeric(false, function() { alert("Solamente se permiten numeros enteros."); this.value = ""; this.focus(); });
			$(".decimal-2-places").numeric({ decimalPlaces: 2 });

			//Regresar Focus a MODALES
			$('.modal').on('hidden.bs.modal', function (e) {
				var oModalsOpen = $('.in');
				if (oModalsOpen.length > 0 ) {$('body').addClass('modal-open');}
			});
			
			$("#itxt_mdl_fracci_parfac_factura").focusout(
				function (){
					fnc_ajax_consultar_partidas_facturas_txtfact();
				}
			);
			//$('#modalr8va_fracci').modal({show: true});
		} catch (err) {		
			var strMensaje = 'application_run() :: ' + err.message;
			show_label_error(strMensaje);
		}
	}

	/* ********************************************************************************************************************************
	** FRACCIONES                                                                                         **
	******************************************************************************************************************************* **/
	/*Cargar Informacion del Grid*/
	function fcn_cargar_grid_fracciones(){
		try{
			if (oTableFracciones == null) {
				oTableFracciones = $('#dtfracciones');
				oTableFracciones.DataTable({
					"processing": true,
					"serverSide": true,
					ajax: {
						"url": "ajax/regla8va/postFracciones.php",
						"type": "POST",
						"timeout": 50000,
						"data": function ( d ) {
							d.sFiltro = $('#isel_filtrar_fracciones').val();
						},				
						"error": handleAjaxError
					},
					columns: [ 
						{ data: "id_fraccion", className: "text-center"},
						{ data: "id_fraccion", className: "text-center",
							render: function ( data, type, row ) {
								if(row.vencida != 'S'){
									var sHtml = '';
									sHtml += '<a class="btn_dt_fracciones_editar font-size-18x icon-btn" title="Editar">';
									sHtml += '	<i class="fa fa-pencil" aria-hidden="true"></i>';
									sHtml += '</a>&nbsp;&nbsp;';
									sHtml += '<a class="btn_dt_fracciones_eliminar font-size-18x icon-btn" title="Eliminar">';
									sHtml += '	<i class="fa fa-trash-o" aria-hidden="true"></i>';
									sHtml += '</a>';
									return sHtml;
								}else{
									return sHtml;
								}
							}	
						},
						{ data: "descripcion", className: "text-center"},
						{ data: "fraccion", className: "text-center"},
						{ data: "cantidad", className: "text-center"},
						{ data: "cantidad_saldo", className: "text-center"},
						{ data: "valor", className: "text-center"},
						{ data: "valor_saldo", className: "text-center"},
						{ data: "fecha_vencimiento", className: "text-center"},
						{ data: "numero_permiso", className: "text-center"},
						{ data: "fecha_registro", className: "text-center"},
						{ data: "usuario_registro", className: "text-center"}
					],
					responsive: true,
					aLengthMenu: [
						[25, 50, 100, 200, -1],
						[25, 50, 100, 200, "All"]
					],
					iDisplayLength: 50,
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
					dom: 
						"<'row'<'col-xs-8'B><'col-xs-4'f>>" +
						"<'row'<'col-sm-12'tr>>" +
						"<'row'<'col-sm-12'l>>" +
						"<'row'<'col-sm-5'i><'col-sm-7'p>>",
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
						}
					]
				});

				oTableFracciones.on('click', 'a.btn_dt_fracciones_editar', function (e) {
					try {		
						var oData = fcn_get_row_data($(this), oTableFracciones);		
						
						sIdFraccion = oData.id_fraccion;
						ajax_get_consulta_editar_fraccion();
					} catch (err) {		
						var strMensaje = 'editor_' + div_table_name + '_detalles() :: ' + err.message;
						show_modal_error(strMensaje);
					}  
				} );

				oTableFracciones.on('click', 'a.btn_dt_fracciones_eliminar', function (e) {
					try {		
						var oData = fcn_get_row_data($(this), oTableFracciones);		
						
						sIdFraccion = oData.id_fraccion;
						strQuestion = 'Esta seguro que desea eliminar la fraccion?.<br>Permiso: '+oData.numero_permiso+'<br>Fracci&oacute;n: '+oData.fraccion+'<br>Descripci&oacute;n: '+oData.descripcion;
						show_confirm('<i class="fa fa-trash-o" aria-hidden="true"></i> Eliminar Fraccion', strQuestion, function (){ajax_eliminar_fraccion();});
					
					} catch (err) {		
						var strMensaje = 'editor_' + div_table_name + '_detalles() :: ' + err.message;
						show_modal_error(strMensaje);
					}  
				} );
			}else {
				oTableFracciones.DataTable().search('').ajax.reload(null, false);
				setTimeout(function(){ oTableFracciones.DataTable().columns.adjust().responsive.recalc(); }, 500);
			}
		} catch (err) {
			var strMensaje = 'fcn_cargar_grid_fracciones :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	function fcn_agregar_fraccion_disponible(){
		sAccion = 'Nuevo';
		fnc_show_modal_fraccion_add_edit();
	}

	function fnc_limpiar_controles_fraccion_mdl(){
		$("#itxt_mdl_fraccion_descripcion").val('');
		$("#itxt_mdl_fraccion_fraccion").val('');
		$("#itxt_mdl_fraccion_cantidad").val('');
		$("#itxt_mdl_fraccion_valor").val('');
		$("#itxt_mdl_fraccion_numero_permiso").val('');
		$("#idt_mdl_fecha_vence").datepicker("setDate", new Date());
		show_custom_function_error('', 'idiv_menseje_mdl_fraccion');
	}

	function fnc_validar_controles_fraccion_mdl(){
		if($("#itxt_mdl_fraccion_descripcion").val().trim() == ''){
			show_custom_function_error('En necesario agregar la descripci&oacute;n de la mercanc&iacute;a.', 'idiv_menseje_mdl_fraccion'); 
			return false;
		}
		if($("#itxt_mdl_fraccion_fraccion").val().trim() == ''){
			show_custom_function_error('En necesario agregar el n&uacute;mero de fracci&oacute;n.', 'idiv_menseje_mdl_fraccion'); 
			return false;
		}
		if($("#itxt_mdl_fraccion_cantidad").val().trim() == ''){
			show_custom_function_error('En necesario agregar la cantidad total del permiso.', 'idiv_menseje_mdl_fraccion'); 
			return false;
		}
		if($("#itxt_mdl_fraccion_valor").val().trim() == ''){
			show_custom_function_error('En necesario agregar el valor total del permiso.', 'idiv_menseje_mdl_fraccion'); 
			return false;
		}
		if($("#itxt_mdl_fraccion_numero_permiso").val().trim() == ''){
			show_custom_function_error('En necesario agregar el numero del permiso.', 'idiv_menseje_mdl_fraccion'); 
			return false;
		}
		return true;
	}

	function fnc_guardar_fraccion(){
		switch(sAccion){
			case 'Nuevo':
				fnc_ajax_guardar_nueva_fraccion();
				break;
			case 'Editar':
				fnc_ajax_guardar_editar_fraccion();
				break;
		}
	}

	/* -----------------------------------------
			AJAX
	----------------------------------------- */
	function fnc_ajax_guardar_nueva_fraccion(){
		try{
			if(!fnc_validar_controles_fraccion_mdl()){ return false; }
			
			var oData = {
				descripcion: $('#itxt_mdl_fraccion_descripcion').val().trim().toUpperCase(),
				fraccion: $('#itxt_mdl_fraccion_fraccion').val().trim().toUpperCase(),
				cantidad: $('#itxt_mdl_fraccion_cantidad').val().trim().toUpperCase(),
				valor: $('#itxt_mdl_fraccion_valor').val().trim().toUpperCase(),
				fecha_vencimiento: $('#idt_txt_fecha_vence').val().trim().toUpperCase(),
				numero_permiso: $('#itxt_mdl_fraccion_numero_permiso').val().trim().toUpperCase()
			};
			
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_set_agregar_fraccion.php',
				data: oData,
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Generando fracci&oacute;n, espere un momento por favor...')
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);					
						if (respuesta.Codigo == '1'){
							fcn_cargar_grid_fracciones();
							$('#modal_fraccion').modal('hide');			
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'fnc_ajax_guardar_nueva_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	function ajax_get_consulta_editar_fraccion(){
		try{
			var oData = {id_fraccion: sIdFraccion};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_get_consultar_fraccion.php',
				data: oData,
				beforeSend: function (dataMessage) {
					show_load_config(true)
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);					
						if (respuesta.Codigo == '1'){
							$("#itxt_mdl_fraccion_descripcion").val(respuesta.descripcion);
							$("#itxt_mdl_fraccion_fraccion").val(respuesta.fraccion);
							$("#itxt_mdl_fraccion_cantidad").val(respuesta.cantidad);
							$("#itxt_mdl_fraccion_valor").val(respuesta.valor);
							$("#itxt_mdl_fraccion_numero_permiso").val(respuesta.numero_permiso);
							$("#idt_mdl_fecha_vence").datepicker("setDate", new Date(respuesta.fecha_vencimiento));

							sAccion = 'Editar';
							fnc_show_modal_fraccion_add_edit();		
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_get_consulta_editar_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	function fnc_ajax_guardar_editar_fraccion(){
		try{
			if(!fnc_validar_controles_fraccion_mdl()){ return false; }
			
			var oData = {
				id_fraccion: sIdFraccion,
				descripcion: $('#itxt_mdl_fraccion_descripcion').val().trim().toUpperCase(),
				fraccion: $('#itxt_mdl_fraccion_fraccion').val().trim().toUpperCase(),
				cantidad: $('#itxt_mdl_fraccion_cantidad').val().trim().toUpperCase(),
				valor: $('#itxt_mdl_fraccion_valor').val().trim().toUpperCase(),
				fecha_vencimiento: $('#idt_txt_fecha_vence').val().trim().toUpperCase(),
				numero_permiso: $('#itxt_mdl_fraccion_numero_permiso').val().trim().toUpperCase()
			};
			
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_set_editar_fraccion.php',
				data: oData,
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Guardando fracci&oacute;n, espere un momento por favor...')
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);					
						if (respuesta.Codigo == '1'){
							fcn_cargar_grid_fracciones();
							$('#modal_fraccion').modal('hide');			
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'fnc_ajax_guardar_nueva_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	function ajax_eliminar_fraccion(){
		try{
			var oData = {id_fraccion: sIdFraccion};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_get_eliminar_fraccion.php',
				data: oData,
				beforeSend: function (dataMessage) {
					show_load_config(true)
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);					
						if (respuesta.Codigo == '1'){
							fcn_cargar_grid_fracciones();
							$('#modal_fraccion').modal('hide');
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_get_consulta_editar_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	/* ********************************************************************************************************************************
	** HISTORICO PARTIDAS                                                                                                      **
	******************************************************************************************************************************* **/
	/*Cargar Informacion del Grid*/
	function fcn_cargar_grid_partidas_referencias(){
		try{
			if (oTablePartidas == null) {
				oTablePartidas = $('#dtpartidas');
				oTablePartidas.DataTable({
					//"order": [[2, 'desc']],
					"processing": true,
					"serverSide": true,
					ajax: {
						"url": "ajax/regla8va/postPartidas.php",
						"type": "POST",
						"timeout": 50000,
						"data": function ( d ) {
							//d.sFiltro = $('#isel_filtrar_fracciones').val();
						},
						"error": handleAjaxError
					},
					columns: [ 
						{ data: "id_fraccion_hist", className: "text-center"},
						/*{ data: "id_fraccion_hist", className: "text-center",
							render: function ( data, type, row ) {
								if(row.vencida != 'S'){
									var sHtml = '';
									sHtml += '<a class="btn_dt_fracciones_editar font-size-18x icon-btn" title="Editar">';
									sHtml += '	<i class="fa fa-pencil" aria-hidden="true"></i>';
									sHtml += '</a>&nbsp;&nbsp;';
									sHtml += '<a class="btn_dt_fracciones_eliminar font-size-18x icon-btn" title="Eliminar">';
									sHtml += '	<i class="fa fa-trash-o" aria-hidden="true"></i>';
									sHtml += '</a>';
									return sHtml;
								}else{
									return '';//sHtml;
								//}
							}	
						},*/
						{ data: "num_refe", className: "text-left"},
						{ data: "factura", className: "text-left"},
						{ data: "proveedor", className: "text-left"},
						{ data: "cons_par", className: "text-center"},
						{ data: "fraccion", className: "text-center"},
						{ data: "descripcion", className: "text-left"},
						{ data: "numero_permiso", className: "text-left"},
						{ data: "fecha_registro", className: "text-left"},
						{ data: "usuario_registro", className: "text-left"}
					],
					responsive: true,
					aLengthMenu: [
						[25, 50, 100, 200, -1],
						[25, 50, 100, 200, "All"]
					],
					iDisplayLength: 50,
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
					dom: 
						"<'row'<'col-xs-8'B><'col-xs-4'f>>" +
						"<'row'<'col-sm-12'tr>>" +
						"<'row'<'col-sm-12'l>>" +
						"<'row'<'col-sm-5'i><'col-sm-7'p>>",
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
						}
					]
				});

				/*oTablePartidas.on('click', 'a.btn_dt_fracciones_editar', function (e) {
					try {		
						var oData = fcn_get_row_data($(this), oTablePartidas);		
						
						sIdFraccion = oData.id_fraccion;
						ajax_get_consulta_editar_fraccion();
					} catch (err) {		
						var strMensaje = 'editor_' + div_table_name + '_detalles() :: ' + err.message;
						show_modal_error(strMensaje);
					}  
				} );

				oTablePartidas.on('click', 'a.btn_dt_fracciones_eliminar', function (e) {
					try {		
						var oData = fcn_get_row_data($(this), oTablePartidas);		
						
						sIdFraccion = oData.id_fraccion;
						strQuestion = 'Esta seguro que desea eliminar la fraccion?.<br>Permiso: '+oData.numero_permiso+'<br>Fracci&oacute;n: '+oData.fraccion+'<br>Descripci&oacute;n: '+oData.descripcion;
						show_confirm('<i class="fa fa-trash-o" aria-hidden="true"></i> Eliminar Fraccion', strQuestion, function (){ajax_eliminar_fraccion();});
					
					} catch (err) {		
						var strMensaje = 'editor_' + div_table_name + '_detalles() :: ' + err.message;
						show_modal_error(strMensaje);
					}  
				} );*/
			}else {
				oTablePartidas.DataTable().search('').ajax.reload(null, false);
				setTimeout(function(){ oTablePartidas.DataTable().columns.adjust().responsive.recalc(); }, 500);
			}
		} catch (err) {
			var strMensaje = 'fcn_cargar_grid_fracciones :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	function fcn_procesar_referencia_r8va(){
		//sAccion = 'Nuevo';
		fnc_show_modal_procesar_referencia_r8va();
	}

	function fnc_limpiar_controles_procesar_referencia_mdl(){
		$('#itxt_mdl_proref_referencia').val('');

		$('#sel_filtro_estado_parreferencia').val('0');
		bSelRem = true;
		$('#sel_remesa_ref_seleccionada').html('');

		$('#sel_permisos_disponibles_ref').html('');

		show_custom_function_error('', sidDiv_BusRef);
		sPartidasRef = Array();
		fnc_carga_grid_fracciones_referencia();
		fnc_deshabilitar_controles_procesarref(true);

		sItemActual = 0;
		sErroresAplR8vaArray = '';

		$('#itxt_mdl_proref_referencia').focus();

	}

	function fnc_carga_grid_fracciones_referencia(){
		try{
			//var otable = fnc_convert_array_table_par_referencia();
			oTableParReferencia = $('#dtpar_referencias');
			oTableParReferencia.DataTable({
				data: fnc_convert_array_table_par_referencia(),
				"destroy": true,
				responsive: true,
				aLengthMenu: [
					[5, 10, 50, 100, -1],
					[5, 10, 50, 100, "All"]
				],
				iDisplayLength: -1
			});
		} catch (err) {
			var strMensaje = 'fnc_carga_grid_fracciones_referencia :: ' + err.message;
			show_modal_error(strMensaje);
		}
		//setTimeout(function () {oTableParReferencia.columns.adjust().responsive.recalc();},300);
	}

	function fnc_convert_array_table_par_referencia(){
		var oReturn = new Array();
		for(i = 0; i < sPartidasRef.length ; i++){
			var oPush = [];
			if(sPartidasRef[i].id_fraccion_hist == ''){
				//Partida sin procesar
				oPush = [
					sPartidasRef[i].numero_factura,
					fnc_get_links_acciones_parref(i),
					fnc_get_estado_partida_regla_8va(i),//sPartidasRef[i].permiso_casa,sPartidasRef[i].aplica_regla,sPartidasRef[i].aplica_fraccion),
					sPartidasRef[i].nombre_proveedor,
					sPartidasRef[i].consecutivo_partida,
					sPartidasRef[i].fraccion_casa,
					sPartidasRef[i].descripcion_casa,
					sPartidasRef[i].permiso_casa,
					sPartidasRef[i].cantidad_casa,
					sPartidasRef[i].valor_casa,
					sPartidasRef[i].fraccion_anterior,
					sPartidasRef[i].fecha_aplicacion
				];
			}else{
				//Partida procesada por el sistema
				oPush = [
					sPartidasRef[i].numero_factura,
					fnc_get_links_acciones_parref(i),//,sPartidasRef[i].permiso_casa,sPartidasRef[i].aplica_regla,sPartidasRef[i].aplica_fraccion,sPartidasRef[i].id_fraccion_hist),
					fnc_get_estado_partida_regla_8va(i),//sPartidasRef[i].permiso_casa,sPartidasRef[i].aplica_regla,sPartidasRef[i].aplica_fraccion,sPartidasRef[i].id_fraccion_hist),
					sPartidasRef[i].nombre_proveedor,
					sPartidasRef[i].consecutivo_partida,
					sPartidasRef[i].fraccion_casa,
					sPartidasRef[i].descripcion_web,
					sPartidasRef[i].permiso_web,
					sPartidasRef[i].cantidad_web,
					sPartidasRef[i].valor_web,
					sPartidasRef[i].fraccion_anterior,
					sPartidasRef[i].fecha_aplicacion
				];
			}
			oReturn.push(oPush);
		}
		return oReturn
	}

	function fnc_get_links_acciones_parref(nItem){
		//,sPartidasRef[i].permiso_casa,sPartidasRef[i].aplica_regla,sPartidasRef[i].aplica_fraccion
		var nPermisoCasa = sPartidasRef[nItem].permiso_casa;
		var bAplicaRegla = sPartidasRef[nItem].aplica_regla;
		var bAplicaFraccion = sPartidasRef[nItem].aplica_fraccion;
		var idFraccionhist = sPartidasRef[nItem].id_fraccion_hist;
		var sTLCs = sPartidasRef[nItem].tlc_casa;
		var sLinks = ''; /*sidFraccionhist = idFraccionHist;*/
		//if(sidFraccionhist == null || sidFraccionhist == undefined){
		if(sTLCs == ''){
			if(idFraccionhist == ''){
				//No procesado por el sistema
				if(nPermisoCasa == ''){
					//Pendiente de aplicar
					if(bAplicaRegla == '1'){
						if(bAplicaFraccion == '1'){//Solo aplica fraccion
							sLinks = '<a href="javascript:void(0);" onclick="fnc_ajax_ver_fracciones_disponibles('+nItem+');return false;" style="padding-left:.5em" title="Aplicar regla 8va de fracciones disponibles" class="btn btn-xs btn-primary"><i class="fa fa-list" aria-hidden="true"></i> Ver Disponibles</a>&nbsp;';
						}else{
							sLinks = '<a href="javascript:void(0);" onclick="fnc_aplicar_r8va_partida_seleccionada('+nItem+');return false;" style="padding-left:.5em" title="Aplicar Regla 8va" class="btn btn-xs btn-primary"><i class="fa fa-check" aria-hidden="true"></i> Aplicar R8va</a>&nbsp;';
						}
					}
				}//else{//Regla8va Aplicada Manualmente
			}else{
				//Procesado por el sistema
				sLinks = '<a href="javascript:void(0);" onclick="fnc_eliminar_revertir_regla_8va('+idFraccionhist+');return false;" style="padding-left:.5em" title="Regresar informacion original a la partida." class="btn btn-xs btn-danger"><i class="fa fa-undo" aria-hidden="true"></i> Eliminar R8va</a>&nbsp;';
			}
		}
		return sLinks;
	}

	function fnc_get_estado_partida_regla_8va(nItem){//nPermisoCasa,bAplicaRegla,bAplicaFraccion,idFraccionHist){
		//sPartidasRef[i].permiso_casa,sPartidasRef[i].aplica_regla,sPartidasRef[i].aplica_fraccion,sPartidasRef[i].id_fraccion_hist
		var nPermisoCasa = sPartidasRef[nItem].permiso_casa;
		var bAplicaRegla = sPartidasRef[nItem].aplica_regla;
		var bAplicaFraccion = sPartidasRef[nItem].aplica_fraccion;
		var idFraccionHist = sPartidasRef[nItem].id_fraccion_hist;
		var sTLCs = sPartidasRef[nItem].tlc_casa;
		var sHtml = ''
		//if(idFraccionHist == null || idFraccionHist == undefined){
		if(sTLCs == ''){
			if(idFraccionHist == ''){
				//No procesado por el sistema
				if(nPermisoCasa == ''){
					if(bAplicaRegla == '1'){
						if(bAplicaFraccion == '1'){
							//Aplica solamente la fraccion.
							//sHtml += '<a href="javascript:void(0);" onclick="fnc_ver_descripciones_merc_disponibles('+nItem+');return false;" style="padding-left:.5em" title="Aplicar Regla 8va"
							sHtml += '	<span class="label label-info">';
							sHtml += '		<i class="fa fa-info-circle" aria-hidden="true"></i> Fracci&oacute;n Aplicable';
							sHtml += '	</span>';
						}else{
							//Pendiente de aplicar
							sHtml += '<span class="label label-warning">';
							sHtml += '	<i class="fa fa-clock-o" aria-hidden="true"></i> Pendiente';
							sHtml += '</span>';
						}
					}else{
						//No aplica la regla para la fraccion/descripcion
						sHtml += '<span class="label label-danger">';
						sHtml += '	<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> No Aplica';
						sHtml += '</span>';
					}
				}else{//Regla8va Aplicada Manualmente
					//Procesado por el sistema
					sHtml += '<span class="label label-success">';
					sHtml += '	<i class="fa fa-check-square-o" aria-hidden="true"></i> R8va (Manual)';
					sHtml += '</span>';
				}
			}else{
				//Procesado por el sistema
				sHtml += '<span class="label label-success">';
				sHtml += '	<i class="fa fa-check-circle" aria-hidden="true"></i> R8va Aplicada';
				sHtml += '</span>';
			}
		}else{
			sHtml += '<span class="label label-success">';
			sHtml += '	<i class="fa fa-file-text-o" aria-hidden="true"></i> TLC\'s Aplicado';
			sHtml += '</span>';
		}
		return sHtml;
	}

	function fnc_deshabilitar_controles_procesarref(bOpt){
		
		$('#itxt_mdl_proref_referencia').prop('disabled',!bOpt);
		$('#ibtn_mdl_proref_buscar_ref').prop('disabled',!bOpt);
		$('#sel_filtro_estado_parreferencia').prop('disabled',bOpt);
		$('#sel_remesa_ref_seleccionada').prop('disabled',bOpt);
		$('#sel_permisos_disponibles_ref').prop('disabled',bOpt);
		
		$('#ibtn_mdl_proref_cancelat').prop('disabled',bOpt);
		$('#ibtn_mdl_proref_aplicar_todo').prop('disabled',bOpt);

		//$('#ibtn_mdl_proref_aplicar_todo').prop('disabled',bOpt);
	}

	function fnc_aplicar_r8va_partida_seleccionada(nItem,IdFraccion){
		sTipoAplicacion = 'Simple';
		sAplConsFact = sPartidasRef[nItem].consecutivo_factura;
		sAplConsPart = sPartidasRef[nItem].consecutivo_partida;
		sAplFraccion = sPartidasRef[nItem].fraccion_casa;
		sAplDescripcion = sPartidasRef[nItem].descripcion_casa;
		sAplCantidad = sPartidasRef[nItem].cantidad_casa;
		sAplValor = sPartidasRef[nItem].valor_casa;

		if (IdFraccion == null || IdFraccion == undefined) {
			sIdFraccion = '';
		}else{
			sIdFraccion = IdFraccion;
		}

		fnc_ajax_aplicar_r8va_partida();
	}

	function fnc_aplicar_r8va_todo_array(nItem){
		//sItemActual = nItem;
		if(nItem == 0){	
			sErroresAplR8vaArray = ''; 
			sTipoAplicacion = 'Multiple';
		}
		for(i=nItem; i<sPartidasRef.length; i++){
			if(sPartidasRef[i].id_fraccion_hist == '' && sPartidasRef[i].permiso_casa == '' && sPartidasRef[i].aplica_regla == '1' && sPartidasRef[i].aplica_fraccion == '0'){
				sAplConsFact = sPartidasRef[i].consecutivo_factura;
				sAplConsPart = sPartidasRef[i].consecutivo_partida;
				sAplFraccion = sPartidasRef[i].fraccion_casa;
				sAplDescripcion = sPartidasRef[i].descripcion_casa;
				sAplCantidad = sPartidasRef[i].cantidad_casa;
				sAplValor = sPartidasRef[i].valor_casa;
				fnc_ajax_aplicar_r8va_partida();
				sItemActual = i + 1;
				break;
			}
		}
		if(nItem  == sPartidasRef.length){
			sItemActual = i;
			if(sErroresAplR8vaArray != ''){
				show_modal_error(sErroresAplR8vaArray);
				sErroresAplR8vaArray = '';
			}
		}
	}

	function fnc_eliminar_revertir_regla_8va(IdFraHist){
		sIdFraccionHist = IdFraHist;
		show_confirm('Eliminar Regla 8va', 'Esta seguro que desea eliminar la regla 8va a la partida?', function () { fnc_ajax_eliminar_regla8va_partida(); });
	}

	function fnc_carga_grid_fracciones_disponibles(){
		try{
			$('#dtpar_fracciones_disp').DataTable({
				data: fnc_convert_array_table_fracciones_disponibles(),
				"destroy": true,
				responsive: true,
				aLengthMenu: [
					[5, 10, 50, 100, -1],
					[5, 10, 50, 100, "All"]
				],
				iDisplayLength: 5
			});
		} catch (err) {
			var strMensaje = 'fnc_carga_grid_fracciones_disponibles :: ' + err.message;
			show_modal_error(strMensaje);
		}
		//setTimeout(function () {oTableParReferencia.columns.adjust().responsive.recalc();},300);
	}

	function fnc_convert_array_table_fracciones_disponibles(){
		var oReturn = new Array();
		for(i = 0; i < aFraccionesDisp.length ; i++){
			oPush = [
				aFraccionesDisp[i].id_fraccion,
				fnc_get_links_acciones_fraccionesdisp(aFraccionesDisp[i].id_fraccion),
				aFraccionesDisp[i].fraccion,
				aFraccionesDisp[i].descripcion,
				aFraccionesDisp[i].saldo_cantidad,
				aFraccionesDisp[i].saldo_valor,
				aFraccionesDisp[i].fecha_vencimiento
			];
			oReturn.push(oPush);
		}
		return oReturn
	}

	function fnc_get_links_acciones_fraccionesdisp(IdFraccion){
		return '<a href="javascript:void(0);" onclick="fnc_aplicar_r8va_partida_seleccionada('+sItemActual+','+IdFraccion+');return false;" style="padding-left:.5em" title="Aplicar Regla 8va" class="btn btn-xs btn-primary"><i class="fa fa-check" aria-hidden="true"></i> Aplicar R8va</a>&nbsp;';
	}
	/* -----------------------------------------
			AJAX
	----------------------------------------- */
	/* Buscar referencia a procesar con la regla 8va */
	function fnc_ajax_buscar_referencia_procesar(){
		try{
			//var DivDisplay = 'idiv_menseje_mdl_buscar_proref';
			if($('#itxt_mdl_proref_referencia').val().trim() == ''){
				show_custom_function_error('Es necesario agregar el n&uacute;mero de referencia que se desea procesar.', sidDiv_BusRef);
				return false;
			}
			//show_custom_function_error('',sidDiv_BusRef);
			sNumReferencia = $('#itxt_mdl_proref_referencia').val().toUpperCase().trim();
			var oData = {
				referencia: sNumReferencia,
				estatus: $('#sel_filtro_estado_parreferencia').val(),
				remesa: ($('#sel_remesa_ref_seleccionada').val() == null || $('#sel_remesa_ref_seleccionada').val() == undefined ? '': $('#sel_remesa_ref_seleccionada').val()),
				permiso: ($('#sel_permisos_disponibles_ref').val() == null || $('#sel_permisos_disponibles_ref').val() == undefined ? '': $('#sel_permisos_disponibles_ref').val())
			};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_get_consultar_referencia_procesar.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_custom_function_loading('Consultando informacion, espere un momento por favor...', sidDiv_BusRef);
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_custom_function_loading('', sidDiv_BusRef);					
						if (respuesta.Codigo == '1'){
							if(bSelRem){
								var sHtmlSelRem = '';
								for(i=0; i<respuesta.aRemesas.length; i++){
									sHtmlSelRem += '<option value="'+respuesta.aRemesas[i]+'" '+(respuesta.aRemesas[i] == respuesta.remesa ? 'selected' : '')+'>'+respuesta.aRemesas[i]+'</option>';
								}
								sHtmlSelRem += '<option value="">TODAS</option>';
								$('#sel_remesa_ref_seleccionada').html(sHtmlSelRem);
								bSelRem = false;
							}
							if(respuesta.aPermisos.length > 0){
								var sHtmlSelPer = '';
								for(i=0; i<respuesta.aPermisos.length; i++){
									sHtmlSelPer += '<option value="'+respuesta.aPermisos[i]+'" '+(respuesta.aPermisos[i] == respuesta.permiso ? 'selected' : '')+'>'+respuesta.aPermisos[i]+'</option>';
								}
								$('#sel_permisos_disponibles_ref').html(sHtmlSelPer);
							}
							sPartidasRef =  respuesta.aPartidas;
							fnc_carga_grid_fracciones_referencia();
							fnc_deshabilitar_controles_procesarref(false);
							//aRemesas	
						}else{
							show_custom_function_error(respuesta.Mensaje+'['+respuesta.Error+']', sidDiv_BusRef);
						}
					}else{
						show_custom_function_error(strSessionMessage, sidDiv_BusRef);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_custom_function_error(a.status+' [' + a.statusText + ']', sidDiv_BusRef);
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_get_consulta_editar_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	function fnc_ajax_aplicar_r8va_partida(){
		try{
			var oData  = {
				referencia: sNumReferencia,
				cons_fact: sAplConsFact,
				cons_par: sAplConsPart,
				permiso: $('#sel_permisos_disponibles_ref').val(),
				id_fraccion: sIdFraccion
			};
			$.ajax({
				//async: false,
				type: "POST",
				url: 'ajax/regla8va/ajax_set_aplicar_r8va_partida.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Aplicando Regla 8va a la partida [Fraccion:'+sAplFraccion+'][Descripcion:'+sAplDescripcion+'], espere un momento por favor...')
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);					
						if (respuesta.Codigo == '1'){
							if(sTipoAplicacion == 'Simple'){
								fnc_ajax_buscar_referencia_procesar();
								$('#modal_fraccion_apli_par').modal('hide');
							}else{
								fnc_aplicar_r8va_todo_array(sItemActual);
							}
						}else{
							if(sTipoAplicacion == 'Simple'){
								show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
							}else{
								sErroresAplR8vaArray += respuesta.Mensaje+'['+respuesta.Error+']<br>.';
								fnc_aplicar_r8va_todo_array(sItemActual);
							}
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'fnc_ajax_aplicar_r8va_partida() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	function fnc_ajax_eliminar_regla8va_partida(){
		try{
			var oData  = {id_fraccion_hist: sIdFraccionHist};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_set_eliminar_r8va_partida.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Eliminando Regla 8va a la partida, espere un momento por favor...')
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);					
						if (respuesta.Codigo == '1'){
							show_modal_ok(respuesta.Mensaje);
							fnc_ajax_buscar_referencia_procesar();
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'fnc_ajax_aplicar_r8va_partida() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	function fnc_ajax_ver_fracciones_disponibles(nItem){
		try{
			sItemActual = nItem;
			var oData  = {
				fraccion: sPartidasRef[sItemActual].fraccion_casa,
				permiso: $('#sel_permisos_disponibles_ref').val()
			};
			$.ajax({
				//async: false,
				type: "POST",
				url: 'ajax/regla8va/ajax_get_consultar_fracciones_disponibles.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_load_config(true)
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);					
						if (respuesta.Codigo == '1'){
							aFraccionesDisp = respuesta.aFracciones;

							$('#itxt_mdl_fraccdisp_factura').val(sPartidasRef[sItemActual].numero_factura);
							$('#itxt_mdl_fraccdisp_proveedor').val(sPartidasRef[sItemActual].nombre_proveedor);
							$('#itxt_mdl_fraccdisp_fraccion').val(sPartidasRef[sItemActual].fraccion_casa);
							$('#itxt_mdl_fraccdisp_descripcion').val(sPartidasRef[sItemActual].descripcion_casa);
							$('#itxt_mdl_fraccdisp_cantidad').val(sPartidasRef[sItemActual].cantidad_casa);
							$('#itxt_mdl_fraccdisp_valor').val(sPartidasRef[sItemActual].valor_casa);

							fnc_show_modal_fracciones_disponibles();
							fnc_carga_grid_fracciones_disponibles();
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'fnc_ajax_ver_fracciones_disponibles() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	/* ********************************************************************************************************************************
	** PARTIDAS PEDIMENTO *** CIERRE ***                                                                                         **
	******************************************************************************************************************************* **/
	
	function fnc_carga_grid_partidas_pedimento_cierre(){
		try{
			$('#dtpar_pedimentocierre').DataTable({
				data: fnc_convert_array_table_parpedimento_cierre(),
				"destroy": true,
				responsive: true,
				aLengthMenu: [
					[5, 10, 50, 100, -1],
					[5, 10, 50, 100, "All"]
				],
				iDisplayLength: -1
			});
		} catch (err) {
			var strMensaje = 'fnc_carga_grid_partidas_pedimento_cierre :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function fnc_convert_array_table_parpedimento_cierre(){
		var oReturn = new Array();
		for(i = 0; i < aPartidasPedCierre.length ; i++){
			oPush = [
				aPartidasPedCierre[i].numero_partida,
				fnc_get_links_acciones_parpedimento_cierre(i),
				fnc_get_estado_partida_parpedimento_cierre(i),
				aPartidasPedCierre[i].fraccion,
				aPartidasPedCierre[i].descripcion,
				aPartidasPedCierre[i].tipo_moneda,
				aPartidasPedCierre[i].valor_aduana,
				aPartidasPedCierre[i].cantidad_tarifa,
				aPartidasPedCierre[i].umt,
				aPartidasPedCierre[i].valor_comercial,
				aPartidasPedCierre[i].cantidad_factura,
				aPartidasPedCierre[i].umc,
				aPartidasPedCierre[i].pais_origen,
				aPartidasPedCierre[i].pais_vendedor,
				aPartidasPedCierre[i].numero_permiso
			];
			oReturn.push(oPush);
		}
		return oReturn;
	}
	
	function fnc_get_links_acciones_parpedimento_cierre(nItem){
		var sLinks = '';
		var sTLCs = aPartidasPedCierre[nItem].tlc;
		var bAplicaRegla = aPartidasPedCierre[nItem].aplica_partida;
		var bAplicaFraccion = aPartidasPedCierre[nItem].aplica_fraccion;
		var bReglaAplicada = aPartidasPedCierre[nItem].regla_aplicada;
		if(sTLCs == ''){
			if(bReglaAplicada == '1'){
				sLinks = '<a href="javascript:void(0);" onclick="fnc_eliminar_regla8va_parped_cierre('+nItem+');return false;" style="padding-left:.5em" title="Regresar informacion original a la partida." class="btn btn-xs btn-danger"><i class="fa fa-undo" aria-hidden="true"></i> Eliminar R8va</a>&nbsp;';
			}else{
				if(bAplicaRegla == '1' || bAplicaFraccion == '1'){
					sLinks = '<a href="javascript:void(0);" onclick="fnc_ajax_get_consultar_info_parped_aplicar_r8va('+nItem+');return false;" style="padding-left:.5em" title="Aplicar Regla 8va" class="btn btn-xs btn-primary"><i class="fa fa-check" aria-hidden="true"></i> Aplicar R8va</a>&nbsp;';
				}/*else{
					if(bAplicaFraccion == '1'){
						sLinks = '<a href="javascript:void(0);" onclick="fnc_ajax_ver_fracciones_disponibles('+nItem+');return false;" style="padding-left:.5em" title="Aplicar regla 8va de fracciones disponibles" class="btn btn-xs btn-primary"><i class="fa fa-list" aria-hidden="true"></i> Ver Disponibles</a>&nbsp;';
					}
				}*/
			}
		}
		return sLinks;
	}
	
	function fnc_get_estado_partida_parpedimento_cierre(nItem){
		var sHtml = '';
		var sTLCs = aPartidasPedCierre[i].tlc;
		var bAplicadaRegla = aPartidasPedCierre[i].regla_aplicada;
		var bAplicaFraccion = aPartidasPedCierre[nItem].aplica_fraccion;
		var bAplicaRegla = aPartidasPedCierre[nItem].aplica_partida;
		if(sTLCs == ''){
			if(bAplicadaRegla == '1'){
				//Procesado por el sistema
				sHtml += '<span class="label label-success">';
				sHtml += '	<i class="fa fa-check-circle" aria-hidden="true"></i> R8va Aplicada';
				sHtml += '</span>';
			}else{
				if(bAplicaFraccion == '1'){
					if(bAplicaRegla == '1'){
						//Pendiente de aplicar
						sHtml += '<span class="label label-warning">';
						sHtml += '	<i class="fa fa-clock-o" aria-hidden="true"></i> Pendiente';
						sHtml += '</span>';
					}else{
						//Aplica solamente la fraccion.
						//sHtml += '<a href="javascript:void(0);" onclick="fnc_ver_descripciones_merc_disponibles('+nItem+');return false;" style="padding-left:.5em" title="Aplicar Regla 8va"
						sHtml += '	<span class="label label-info">';
						sHtml += '		<i class="fa fa-info-circle" aria-hidden="true"></i> Fracci&oacute;n Aplicable';
						sHtml += '	</span>';
					}
				}else{
					//No aplica la regla para la fraccion/descripcion
					sHtml += '<span class="label label-danger">';
					sHtml += '	<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> No Aplica';
					sHtml += '</span>';
				}
			}
		}else{
			sHtml += '<span class="label label-success">';
			sHtml += '	<i class="fa fa-file-text-o" aria-hidden="true"></i> TLC\'s Aplicado';
			sHtml += '</span>';
		}
		return sHtml;
	}
	
	function fnc_inicializar_controles_mdl_aplir8va_parped_cierre(){
		$('#itxt_mdl_fracci_aplir8va_referencia').val('');
		$('#itxt_mdl_fracci_aplir8va_partida').val('');
		$('#itxt_mdl_fracci_aplir8va_fraccion').val('');
		$('#itxt_mdl_fracci_aplir8va_descripcion').val('');
		
		$('#isel_mdl_fracci_aplir8va_permiso').html('');
		$('#isel_mdl_fracci_aplir8va_fraccion').html('');
		$('#isel_mdl_fracci_aplir8va_descripcion').html('');
		
		aParFacFracci = Array();
		fnc_carga_grid_parfac_fraccion_cierre();
	}
	
	
	/*function fnc_eliminar_regla_8va_parped_cierre(nItem){
		nItemParPedCierre = nItem;
		if(aPartidasPedCierre[nItem].seaplico_r8va_web == '1'){
			//Se aplico la regla 8va desde el sistema WEB
			show_confirm('Eliminar Regla 8va', 'Esta seguro que desea eliminar la regla 8va de la partida en el cierre del pedimento?', function () { fnc_ajax_eliminar_regla8va_parped_cierre(); });
		}else{
			//if(eval(aPartidasPedCierre[nItem].cantidad_partidas) > 0){
			//No se aplico la Regla 8va desde el sistema Web 
			//Es necesario solicitar la fraccion original para eliminar la Regla
			//Aplicar esta fraccion a las partidas de las facturas
			fnc_show_modal_fraccion_original_eliminar_r8va();
			//}
		}
	}*/
		
	//PARTIDAS - FACTURA DE LA FRACCIONES
	function fnc_carga_grid_parfac_fraccion_cierre(){
		try{
			$('#dtparfac_fraccion').DataTable({
				data: fnc_convert_array_table_parfac_fraccion(),
				"destroy": true,
				responsive: true,
				aLengthMenu: [
					[5, 10, 50, 100, -1],
					[5, 10, 50, 100, "All"]
				],
				iDisplayLength: -1
			});
		} catch (err) {
			var strMensaje = 'fnc_carga_grid_parfac_fraccion_cierre :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function fnc_convert_array_table_parfac_fraccion(){
		var oReturn = new Array();
		for(i = 0; i < aParFacFracci.length ; i++){
			oPush = [
				aParFacFracci[i].num_fact,
				fnc_get_links_acciones_parfac_fraccion_cierre(i),
				aParFacFracci[i].num_part,
				aParFacFracci[i].fraccion,
				aParFacFracci[i].des_merc,
				aParFacFracci[i].can_fact,
				aParFacFracci[i].val_fact
			];
			oReturn.push(oPush);
		}
		return oReturn;
	}
	
	function fnc_get_links_acciones_parfac_fraccion_cierre(nitem){
		var sLinks = '';
		sLinks += '<a href="javascript:void(0);" onclick="fnc_editar_parfac_fraccion_cierre('+nitem+');return false;" style="padding-left:.5em" title="Editar" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;';
		sLinks += '<a href="javascript:void(0);" onclick="fnc_eliminar_parfac_fraccion_cierre('+nitem+');return false;" style="padding-left:.5em" title="Eliminar" class="btn btn-xs btn-warning"><i class="fa fa-trash"></i></a>';
		return sLinks;
	}
	
	function fnc_editar_parfac_fraccion_cierre(nitem){
		sActParFacFracci = 'Editar';
		nItemParFacEdit = nitem;
		
		$('#itxt_mdl_fracci_parfac_factura').prop("disabled",true);
		$('#isel_mdl_fracci_parfac_numparte').prop("disabled",true);
		
		$('#itxt_mdl_fracci_parfac_factura').val(aParFacFracci[nitem].num_fact);
		var sHtmlSel = '<option value="'+aParFacFracci[nitem].num_part+'" selected>'+aParFacFracci[nitem].num_part+'</option>';
		$('#isel_mdl_fracci_parfac_numparte').val(sHtmlSel);
		$('#itxt_mdl_fracci_parfac_fraccion').val(aParFacFracci[nitem].fraccion);
		$('#itxt_mdl_fracci_parfac_descripcion').val(aParFacFracci[nitem].des_merc);
		$('#itxt_mdl_fracci_parfac_cantidad').val(aParFacFracci[nitem].can_fact);
		$('#itxt_mdl_fracci_parfac_valor').val(aParFacFracci[nitem].val_fact);
		fnc_show_modal_editar_parfac_fraccion();
	}
	
	function fnc_guardar_editar_parfac_fraccion_cierre(){
		if($('#itxt_mdl_fracci_parfac_cantidad').val().trim() == ''){
			show_modal_error('Es necesario agregar la cantidad de la partida a la que se le desea aplicar la r8va.');
			return false;
		}
		if($('#itxt_mdl_fracci_parfac_valor').val().trim() == ''){
			show_modal_error('Es necesario agregar el valor de la partida al que se desea aplicar la r8va.');
			return false;
		}
		
		aParFacFracci[nItemParFacEdit].can_fact = $('#itxt_mdl_fracci_parfac_cantidad').val().trim();
		aParFacFracci[nItemParFacEdit].val_fact = $('#itxt_mdl_fracci_parfac_valor').val().trim();
	
		fnc_carga_grid_parfac_fraccion_cierre();
		
		$('#modalr8va_fracci_parfac_editar').modal('hide');
	}
	
	function fnc_eliminar_parfac_fraccion_cierre(nitem){
		nItemParFacEdit = nitem;
		show_confirm("Eliminar Partida De La Factura", "Esta seguro que desea eliminar la partida de la factura?", function (){fnc_eliminar_parfac_fraccion_cierre_si();});
	}
	
	function fnc_eliminar_parfac_fraccion_cierre_si(){
		aParFacFracci.splice(nItemParFacEdit, 1);
		fnc_carga_grid_parfac_fraccion_cierre();
	}
		
	function fnc_agregar_parfac_fraccion_cierre(){
		
		sActParFacFracci = 'Nuevo';
		
		fnc_inicializar_controles_parfac_fraccion(true);
		$('#itxt_mdl_fracci_parfac_factura').prop("disabled",false);
		$('#isel_mdl_fracci_parfac_numparte').prop("disabled",false);
		
		fnc_show_modal_editar_parfac_fraccion();
		
		setTimeout(function (){$('#itxt_mdl_fracci_parfac_factura').focus();},500);
	}
	
	function fnc_inicializar_controles_parfac_fraccion(pOpt){
		if(pOpt){
			$('#itxt_mdl_fracci_parfac_factura').val('');
		}
		$('#isel_mdl_fracci_parfac_numparte').html('');
		$('#itxt_mdl_fracci_parfac_fraccion').val('');
		$('#itxt_mdl_fracci_parfac_descripcion').val('');
		$('#itxt_mdl_fracci_parfac_cantidad').val('');
		$('#itxt_mdl_fracci_parfac_valor').val('');
	}
		
	function fnc_guardar_nuevo_parfac_fraccion_cierre(){
		if($('#itxt_mdl_fracci_parfac_cantidad').val().trim() == ''){
			show_modal_error('Es necesario agregar la cantidad de la partida a la que se le desea aplicar la r8va.');
			return false;
		}
		if($('#itxt_mdl_fracci_parfac_valor').val().trim() == ''){
			show_modal_error('Es necesario agregar el valor de la partida al que se desea aplicar la r8va.');
			return false;
		}
		for(i=0; i<aParFacFracci.length; i++){
			if(sConsFacParNva == aParFacFracci[i].cons_fact &&  sConsParParNva == aParFacFracci[i].cons_part){
				show_modal_error('La partida '+aParFacFracci[i].cons_part+' que desea agregar de la factura, ya existe!.');
				return false;
			}
		}
		var nitem = eval($('#isel_mdl_fracci_parfac_numparte').val().trim());
		var aPartida = {
			'cons_fact': sConsFacParNva,
			'cons_part': sConsParParNva,
			'num_fact': $('#itxt_mdl_fracci_parfac_factura').val().trim().toUpperCase(),
			'num_part': $('#isel_mdl_fracci_parfac_numparte option:selected').text().trim().toUpperCase(),
			'fraccion': $('#itxt_mdl_fracci_parfac_fraccion').val().trim().toUpperCase(),
			'des_merc': $('#itxt_mdl_fracci_parfac_descripcion').val().trim().toUpperCase(),
			'can_fact': $('#itxt_mdl_fracci_parfac_cantidad').val().trim().toUpperCase(),
			'val_fact': $('#itxt_mdl_fracci_parfac_valor').val().trim().toUpperCase()
		};
		aParFacFracci.push(aPartida);
		
		fnc_carga_grid_parfac_fraccion_cierre();
		$('#modalr8va_fracci_parfac_editar').modal('hide');
	}
	
	function fnc_guardar_parfac_fraccion_cierre(){
		if(sActParFacFracci == 'Nuevo'){
			fnc_guardar_nuevo_parfac_fraccion_cierre();
		}else{
			fnc_guardar_editar_parfac_fraccion_cierre();
		}
	}
	
	function fnc_seleccionar_numparte_parfac_fraccion(nitem){
		sConsFacParNva = aParFacAddPar[nitem].cons_fact;
		sConsParParNva = aParFacAddPar[nitem].cons_part;
		$('#itxt_mdl_fracci_parfac_fraccion').val(aParFacAddPar[nitem].fraccion);
		$('#itxt_mdl_fracci_parfac_descripcion').val(aParFacAddPar[nitem].des_merc);
		$('#itxt_mdl_fracci_parfac_cantidad').val(aParFacAddPar[nitem].can_fact);
		$('#itxt_mdl_fracci_parfac_valor').val(aParFacAddPar[nitem].val_fact);
	}
	
	//Eliminar R8va Partida Pedimento
	function fnc_eliminar_regla8va_parped_cierre(nParPed){
		nItemParPedCierre = nParPed;
		show_confirm("Eliminar Regla 8va Partida", "Esta seguro que desea eliminar la regla 8va de la partida?", function (){fnc_ajax_eliminar_regla8va_parped_cierre();});
	}
	/* -----------------------------------------
			AJAX
	----------------------------------------- */
	/* Buscar referencia cierre */
	function fnc_ajax_buscar_referencia_parped_cierre(){
		try{
			if($('#itxt_referencia_buscar_partidas_cierre').val().trim() == ''){
				show_custom_function_error('Es necesario agregar el n&uacute;mero de referencia que se desea procesar.', sidDiv_BusRef);
				return false;
			}
			
			sNumReferenciaParCierre = $('#itxt_referencia_buscar_partidas_cierre').val().toUpperCase().trim();
			var oData = {referencia: sNumReferenciaParCierre};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_get_consultar_partidas_pedimento_cierre.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_custom_function_loading('Consultando informacion, espere un momento por favor...', sIdDiv_BusRef_ParPed_Cierre);
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_custom_function_loading('', sIdDiv_BusRef_ParPed_Cierre);					
						if (respuesta.Codigo == '1'){
							aPartidasPedCierre =  respuesta.aPartidas;
							fnc_carga_grid_partidas_pedimento_cierre();
						}else{
							show_custom_function_error(respuesta.Mensaje+'['+respuesta.Error+']', sIdDiv_BusRef_ParPed_Cierre);
						}
					}else{
						show_custom_function_error(strSessionMessage, sIdDiv_BusRef_ParPed_Cierre);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_custom_function_error(a.status+' [' + a.statusText + ']', sIdDiv_BusRef_ParPed_Cierre);
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_get_consulta_editar_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function fnc_ajax_get_consultar_info_parped_aplicar_r8va(nItem){
		try{
			nItemParPedCierre = nItem;
			var oData = {
				referencia: sNumReferenciaParCierre,
				numero_partida: aPartidasPedCierre[nItemParPedCierre].numero_partida,
				fraccion: aPartidasPedCierre[nItemParPedCierre].fraccion,
				descripcion: aPartidasPedCierre[nItemParPedCierre].descripcion
			};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_get_consultar_info_parped_aplicar_r8va.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_load_config(true);
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);
						if (respuesta.Codigo == '1'){
							
							fnc_inicializar_controles_mdl_aplir8va_parped_cierre();
							
							$('#itxt_mdl_fracci_aplir8va_referencia').val(sNumReferenciaParCierre);
							$('#itxt_mdl_fracci_aplir8va_partida').val(aPartidasPedCierre[nItemParPedCierre].numero_partida);
							$('#itxt_mdl_fracci_aplir8va_fraccion').val(aPartidasPedCierre[nItemParPedCierre].fraccion);
							$('#itxt_mdl_fracci_aplir8va_descripcion').val(aPartidasPedCierre[nItemParPedCierre].descripcion);
							
							var sHtmlSelPerm = '';
							sHtmlSelPerm += '<option value="" selected>[SELECCIONAR PERMISO]</option>';
							for(i=0; i<respuesta.aPermisos.length; i++){
								sHtmlSelPerm += '<option value="'+respuesta.aPermisos[i]+'">'+respuesta.aPermisos[i]+'</option>';
							}
							$('#isel_mdl_fracci_aplir8va_permiso').html(sHtmlSelPerm);
							
							aParFacFracci = respuesta.aPartidasFac;
							fnc_carga_grid_parfac_fraccion_cierre();
							
							fnc_show_modal_aplicar_r8va_fraccion_pedimento();
							
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']', sidDiv_BusRef);
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_get_consulta_editar_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function fnc_ajax_consultar_fracciones_info_parfac_fraccion(pOpt){
		try{
			if(pOpt == 'permiso'){
				$('#isel_mdl_fracci_aplir8va_fraccion').html('');
				$('#isel_mdl_fracci_aplir8va_descripcion').html('');
				if($('#isel_mdl_fracci_aplir8va_permiso').val().trim() == ''){
					return false;
				}
			}
			if(pOpt == 'fracciones'){
				$('#isel_mdl_fracci_aplir8va_descripcion').html('');
				if($('#isel_mdl_fracci_aplir8va_fraccion').val().trim() == ''){
					return false;
				}
			}
			
			var oData = {
				permiso: $('#isel_mdl_fracci_aplir8va_permiso').val().trim(),
				fraccion:($('#isel_mdl_fracci_aplir8va_fraccion').val() == null || $('#isel_mdl_fracci_aplir8va_fraccion').val() == undefined ? '': $('#isel_mdl_fracci_aplir8va_fraccion').val()),
				descripcion:($('#isel_mdl_fracci_aplir8va_descripcion').val() == null || $('#isel_mdl_fracci_aplir8va_descripcion').val() == undefined ? '': $('#isel_mdl_fracci_aplir8va_descripcion').val()),
			};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_get_consultar_fracciones_disponibles_sel_cerrar.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Consultando informacion, espere un momento por favor...', sidDiv_BusRef_Cerrar);
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						setTimeout(function (){show_load_config(false);},300);			
						if (respuesta.Codigo == '1'){
							if($('#isel_mdl_fracci_aplir8va_fraccion').val() == null || $('#isel_mdl_fracci_aplir8va_fraccion').val() == undefined){
								var sHtmlSelRem = ''; var bSelected 
								sHtmlSelRem += '<option value="" selected>[SELECCIONAR FRACCIÓN]</option>';
								for(i=0; i<respuesta.aFracciones.length; i++){
									sHtmlSelRem += '<option value="'+respuesta.aFracciones[i]+'" '+($('#itxt_mdl_fracci_aplir8va_fraccion').val().trim() == respuesta.aFracciones[i] ? 'selected' : '')+'>'+respuesta.aFracciones[i]+'</option>';
								}
								$('#isel_mdl_fracci_aplir8va_fraccion').html(sHtmlSelRem);
								setTimeout(function (){fnc_ajax_consultar_fracciones_info_parfac_fraccion('fracciones');},1300);
							}else{
								var sHtmlSelRem = '';
								sHtmlSelRem += '<option value="" selected>[SELECCIONAR DESCRIPCIÓN]</option>';
								for(i=0; i<respuesta.aDescripciones.length; i++){
									sHtmlSelRem += '<option value="'+respuesta.aDescripciones[i].id_fraccion+'">'+respuesta.aDescripciones[i].descripcion+'</option>';
								}
								$('#isel_mdl_fracci_aplir8va_descripcion').html(sHtmlSelRem);
							}
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_get_consulta_editar_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	//Aplicar R8va
	function fnc_ajax_aplicar_r8va_parped_cierre(){
		try{
			if($('#isel_mdl_fracci_aplir8va_permiso').val().trim() == ''){
				show_modal_error('Es necesario seleccionar el permiso que se desea aplicar a la partida del pedimento.');
				return false;
			}
			if($('#isel_mdl_fracci_aplir8va_fraccion').val().trim() == ''){
				show_modal_error('Es necesario seleccionar la fraccion del permiso que desea aplicar a la partida del pedimento.');
				return false;
			}
			if($('#isel_mdl_fracci_aplir8va_descripcion').val().trim() == ''){
				show_modal_error('Es necesario seleccionar la descripcion de la fraccion que desea aplicar a la partida del pedimento.');
				return false;
			}
			if(aParFacFracci.length == 0){
				how_modal_error('Es necesario agregar las partidas de las facturas que forman la partida del pedimento.');
				return false;
			}
			var oData  = {
				referencia: sNumReferenciaParCierre,
				numero_partida: aPartidasPedCierre[nItemParPedCierre].numero_partida,
				id_fraccion: $('#isel_mdl_fracci_aplir8va_descripcion').val(),
				aPartidasFac: JSON.stringify(aParFacFracci)
			};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_set_aplicar_r8va_parped_cierre.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Guardando informaci&oacute;n de la partida, espere un momento por favor...');
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);					
						if (respuesta.Codigo == '1'){
							show_modal_ok(respuesta.Mensaje);
							//aPartidasPedCierre = Array();
							
							aPartidasPedCierre[nItemParPedCierre].fraccion = respuesta.fraccion;
							aPartidasPedCierre[nItemParPedCierre].numero_permiso = respuesta.numero_permiso;
							aPartidasPedCierre[nItemParPedCierre].regla_aplicada = '1';
							
							fnc_carga_grid_partidas_pedimento_cierre();
							//fnc_ajax_buscar_referencia_parped_cierre();
							$('#modalr8va_fracci').modal('hide');
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'fnc_ajax_aplicar_r8va_parped_cierre() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	//Partidas-FACTURA
	function fnc_ajax_consultar_partidas_facturas_txtfact(){
		try{
			fnc_inicializar_controles_parfac_fraccion(false);
			if($('#itxt_mdl_fracci_parfac_factura').val().trim() == ''){
				return false;
			}
			var oData = {
				referencia: sNumReferenciaParCierre,
				num_fact: $('#itxt_mdl_fracci_parfac_factura').val().trim().toUpperCase()
			};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_get_consultar_partidas_factura.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Consultando informacion, espere un momento por favor...', sidDiv_BusRef_Cerrar);
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);				
						if (respuesta.Codigo == '1'){
							var sHtmlSel = '';
							aParFacAddPar = respuesta.aPartidas;
							for(i=0; i< respuesta.aPartidas.length; i++){
								sHtmlSel += '<option value="'+i+'">'+respuesta.aPartidas[i].num_part+'</option>'
							}
							$('#isel_mdl_fracci_parfac_numparte').html(sHtmlSel);
						
							fnc_seleccionar_numparte_parfac_fraccion(0);
						}else{
							$('#itxt_mdl_fracci_parfac_factura').val('');
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_get_consulta_editar_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	//Eliminar R8va
	function fnc_ajax_eliminar_regla8va_parped_cierre(){
		try{
			var oData  = {
				referencia: sNumReferenciaParCierre,
				numero_partida: aPartidasPedCierre[nItemParPedCierre].numero_partida
			};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_set_eliminar_r8va_parped_cierre.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Eliminando Regla 8va a la partida, espere un momento por favor...')
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);					
						if (respuesta.Codigo == '1'){
							show_modal_ok(respuesta.Mensaje);
							aPartidasPedCierre[nItemParPedCierre].fraccion = respuesta.fraccion;
							aPartidasPedCierre[nItemParPedCierre].numero_permiso = '';
							aPartidasPedCierre[nItemParPedCierre].regla_aplicada = '0';
							//aPartidasPedCierre = Array();
							fnc_carga_grid_partidas_pedimento_cierre();
							//fnc_ajax_buscar_referencia_parped_cierre();
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'fnc_ajax_aplicar_r8va_partida() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	/* ********************************************************************************************************************************
	**  CERRAR REFERENCIAS                                                                                                      **
	******************************************************************************************************************************* **/
	function fcn_cargar_grid_referencias(){
		try{
			if (oTableReferencias == null) {
				oTableReferencias = $('#dtreferencias');
				oTableReferencias.DataTable({
					//"order": [[2, 'desc']],
					"processing": true,
					"serverSide": true,
					ajax: {
						"url": "ajax/regla8va/postReferencias.php",
						"type": "POST",
						"timeout": 50000,
						"data": function ( d ) {
							//d.sFiltro = $('#isel_filtrar_fracciones').val();
						},
						"error": handleAjaxError
					},
					columns: [ 
						{ data: "id_referencia", className: "text-center"},
						{ data: "num_refe", className: "text-left"},
						{ data: "num_refe", className: "text-left"},
						{ data: "cliente", className: "text-left"},
						{ data: "fecha_cierre", className: "text-left"},
						{ data: "usuario_cierre", className: "text-left"}
					],
					responsive: true,
					aLengthMenu: [
						[25, 50, 100, 200, -1],
						[25, 50, 100, 200, "All"]
					],
					iDisplayLength: 50,
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
					dom: 
						"<'row'<'col-xs-8'B><'col-xs-4'f>>" +
						"<'row'<'col-sm-12'tr>>" +
						"<'row'<'col-sm-12'l>>" +
						"<'row'<'col-sm-5'i><'col-sm-7'p>>",
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
						}
					]
				});
			}else {
				oTableReferencias.DataTable().search('').ajax.reload(null, false);
				setTimeout(function(){ oTableReferencias.DataTable().columns.adjust().responsive.recalc(); }, 500);
			}
		} catch (err) {
			var strMensaje = 'fcn_cargar_grid_referencias :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	function fnc_carga_grid_partidas_8va_manual_referencia(){
		try{
			//var otable = fnc_convert_array_table_par_referencia();
			//oTableParReferencia = $('#dtpar_cerrar_ref');
			$('#dtpar_cerrar_ref').DataTable({
				data: fnc_convert_array_table_par_cerrar_ref(),
				"destroy": true,
				responsive: true,
				aLengthMenu: [
					[5, 10, 50, 100, -1],
					[5, 10, 50, 100, "All"]
				],
				iDisplayLength: -1
			});
		} catch (err) {
			var strMensaje = 'fnc_carga_grid_partidas_8va_manual_referencia :: ' + err.message;
			show_modal_error(strMensaje);
		}
		//setTimeout(function () {oTableParReferencia.columns.adjust().responsive.recalc();},300);
	}

	function fnc_convert_array_table_par_cerrar_ref(){
		var oReturn = new Array();
		for(i = 0; i < sPartidasRefCerrar.length ; i++){
			var oPush = [];
			oPush = [
				sPartidasRefCerrar[i].numero_factura,
				fnc_get_links_acciones_partida_manual(i),
				sPartidasRefCerrar[i].numero_remesa,
				sPartidasRefCerrar[i].nombre_proveedor,
				sPartidasRefCerrar[i].consecutivo_partida,
				sPartidasRefCerrar[i].fraccion,
				sPartidasRefCerrar[i].descripcion,
				sPartidasRefCerrar[i].permiso,
				sPartidasRefCerrar[i].cantidad,
				sPartidasRefCerrar[i].valor
			];
			oReturn.push(oPush);
		}
		return oReturn
	}

	function fnc_get_links_acciones_partida_manual(nItem){
		var sConsFact = sPartidasRefCerrar[nItem].consecutivo_factura;
		var sConsPar = sPartidasRefCerrar[nItem].consecutivo_partida;
		var sHTML = '';
		if(sPartidasRefCerrar[nItem].tlcs != ''){
			//Regla y TLC's Aplicado ERROR
			sHTML += '<span class="label label-danger">';
			sHTML += '	<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error R8va Y TLC\'s Aplicados.';
			sHTML += '</span>';
		}else{
			sHTML = '<a href="javascript:void(0);" onclick="fnc_agregar_partida_sistema_web('+sConsFact+','+sConsPar+');return false;" style="padding-left:.5em" title="Agregar Informacion Al Sistem Web" class="btn btn-xs btn-primary"><i class="fa fa-floppy-o" aria-hidden="true"></i> Agregar Informaci&oacute;n</a>&nbsp;';
		}
		
		return sHTML;
	}

	function fnc_agregar_partida_sistema_web(sConsFact, sConsPar){
		sConsFactCRef = sConsFact;
		sConsPartCRef = sConsPar;
		$("#lbl_titulo_info_partida_web").html('<i class="fa fa-globe" aria-hidden="true"></i> Agregar informacion de la partida al sistema WEB');
		//$('#itxt_mdl_partida_info_fraccion_original').val('');
		$('#modal_infopartida').modal({show: true});
		//setTimeout(function (){$("#itxt_mdl_partida_info_fraccion_original").focus();},500);
	}

	function fnc_deshabilitar_controles_cerrar_referencia(bOpt){
		$('#itxt_mdl_cerrar_ref_referencia').prop('disabled',!bOpt);
		$('#ibtn_mdl_cerrar_ref_buscar_ref').prop('disabled',!bOpt);

		$('#ibtn_mdl_cerrar_ref_cancelat').prop('disabled',bOpt);
	}

	function fnc_limpiar_controles_cerrar_referencia_mdl(){
		$('#itxt_mdl_cerrar_ref_referencia').val('');

		sPartidasRefCerrar = Array();
		fnc_carga_grid_partidas_8va_manual_referencia();
		fnc_deshabilitar_controles_cerrar_referencia(true);

		$('#itxt_mdl_cerrar_ref_referencia').focus();
	}

	function fnc_limpia_controles_info_partida_manual(){
		$('#sel_partida_info_permiso').val('');
		$('#sel_partida_info_fraccion').html('');
		$('#sel_partida_info_descripcion').html('');
	}

	//function fnc_limpiar_controles_cerrar_referencia_mdl()	

	/* -----------------------------------------
			AJAX
	----------------------------------------- */
	/* Buscar referencia a procesar con la regla 8va */
	function fnc_ajax_buscar_referencia_cerrar(){
		try{
			//var DivDisplay = 'idiv_menseje_mdl_buscar_proref';
			if($('#itxt_mdl_cerrar_ref_referencia').val().trim() == ''){
				show_custom_function_error('Es necesario agregar el n&uacute;mero de referencia que se desea procesar.', sidDiv_BusRef_Cerrar);
				return false;
			}
			//show_custom_function_error('',sidDiv_BusRef);
			sNumReferenciaCerrar = $('#itxt_mdl_cerrar_ref_referencia').val().toUpperCase().trim();
			var oData = {referencia: sNumReferenciaCerrar};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_get_consultar_referencia_cerrar.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_custom_function_loading('Consultando informacion, espere un momento por favor...', sidDiv_BusRef_Cerrar);
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_custom_function_loading('', sidDiv_BusRef_Cerrar);					
						if (respuesta.Codigo == '1'){
							if(respuesta.aPartidas.length > 0){
								sPartidasRefCerrar = respuesta.aPartidas;
								fnc_carga_grid_partidas_8va_manual_referencia();
								fnc_deshabilitar_controles_cerrar_referencia(false);
								//Cargar permisos disponibles de aplicacion
								var sHtmlSelRem = '';
								sHtmlSelRem += '<option value="" selected>[SELECCIONAR NÚMERO DE PERMISO]</option>';
								for(i=0; i<respuesta.aPermisos.length; i++){
									sHtmlSelRem += '<option value="'+respuesta.aPermisos[i]+'">'+respuesta.aPermisos[i]+'</option>';
								}
								$('#sel_partida_info_permiso').html(sHtmlSelRem);
								fnc_limpia_controles_info_partida_manual();
							}else{
								show_modal_ok(respuesta.Mensaje);
								$('#modal_referencia').modal('hide');
								fcn_cargar_grid_referencias();
							}	
						}else{
							show_custom_function_error(respuesta.Mensaje+'['+respuesta.Error+']', sidDiv_BusRef_Cerrar);
						}
					}else{
						show_custom_function_error(strSessionMessage, sidDiv_BusRef_Cerrar);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_custom_function_error(a.status+' [' + a.statusText + ']', sidDiv_BusRef_Cerrar);
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_get_consulta_editar_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function fnc_ajax_agregar_partida_8va_sistema_web(){
		try{
			if($('#sel_partida_info_permiso').val().trim() == ''){
				show_modal_error('Es necesario seleccionar el permiso aplicado.');
				return false;
			}else{
				if($('#sel_partida_info_fraccion').val().trim() == ''){
					show_modal_error('Es necesario seleccionar la fraccion original.');
					return false;
				}else{
					if($('#sel_partida_info_descripcion').val().trim() == ''){
						show_modal_error('Es necesario seleccionar la descripcion de la fraccion.');
						return false;
					}
				}
			}
			var oData  = {
				referencia: sNumReferenciaCerrar,
				cons_fact: sConsFactCRef,
				cons_par: sConsPartCRef,
				id_fraccion: $('#sel_partida_info_descripcion').val()
			};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_set_agregar_partida_sistema_web.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Consultando informaci&oacute;n de la partida, espere un momento por favor...');
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);					
						if (respuesta.Codigo == '1'){
							show_modal_ok(respuesta.Mensaje);
							fnc_ajax_buscar_referencia_cerrar();
							$('#modal_infopartida').modal('hide');
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'fnc_ajax_aplicar_r8va_partida() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}

	function fnc_ajax_consultar_fracciones_info_partida(sSelect){
		try{
			if(sSelect == 'permiso'){
				$('#sel_partida_info_descripcion').html('');
				$('#sel_partida_info_fraccion').html('');
				if($('#sel_partida_info_permiso').val().trim() == ''){
					return false;
				}
			}
			if(sSelect == 'fracciones'){
				$('#sel_partida_info_descripcion').html('');
				if($('#sel_partida_info_fraccion').val().trim() == ''){
					return false;
				}
			}
			
			var oData = {
				permiso: $('#sel_partida_info_permiso').val().trim(),
				fraccion:($('#sel_partida_info_fraccion').val() == null || $('#sel_partida_info_fraccion').val() == undefined ? '': $('#sel_partida_info_fraccion').val()),
				descripcion:($('#sel_partida_info_descripcion').val() == null || $('#sel_partida_info_descripcion').val() == undefined ? '': $('#sel_partida_info_descripcion').val()),
			};
			$.ajax({
				type: "POST",
				url: 'ajax/regla8va/ajax_get_consultar_fracciones_disponibles_sel_cerrar.php',
				data: oData,
				timeout: 120000,
				beforeSend: function (dataMessage) {
					show_load_config(true, 'Consultando informacion, espere un momento por favor...', sidDiv_BusRef_Cerrar);
				},
				success:  function (response) {
					if (response != '500'){
						var respuesta = JSON.parse(response);
						show_load_config(false);				
						if (respuesta.Codigo == '1'){
							if($('#sel_partida_info_fraccion').val() == null || $('#sel_partida_info_fraccion').val() == undefined){
								var sHtmlSelRem = '';
								sHtmlSelRem += '<option value="" selected>[SELECCIONAR FRACCIÓN]</option>';
								for(i=0; i<respuesta.aFracciones.length; i++){
									sHtmlSelRem += '<option value="'+respuesta.aFracciones[i]+'">'+respuesta.aFracciones[i]+'</option>';
								}
								$('#sel_partida_info_fraccion').html(sHtmlSelRem);
							}else{
								var sHtmlSelRem = '';
								sHtmlSelRem += '<option value="" selected>[SELECCIONAR DESCRIPCIÓN]</option>';
								for(i=0; i<respuesta.aDescripciones.length; i++){
									sHtmlSelRem += '<option value="'+respuesta.aDescripciones[i].id_fraccion+'">'+respuesta.aDescripciones[i].descripcion+'</option>';
								}
								$('#sel_partida_info_descripcion').html(sHtmlSelRem);
							}
						}else{
							show_modal_error(respuesta.Mensaje+'['+respuesta.Error+']');
						}
					}else{
						show_modal_error(strSessionMessage);
						setTimeout(function () {window.location.replace('../logout.php');},3000);
					}				
				},
				error: function(a,b){
					show_modal_error(a.status+' [' + a.statusText + ']');
				}
			});
		} catch (err) {
			var strMensaje = 'ajax_get_consulta_editar_fraccion() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	/*********************************************************************************************************************************
	** GRID FUNCTIONS                                                                                                            **
	*********************************************************************************************************************************/
	/* ..:: Obtenemos los datos del row ::.. */
	function fcn_get_row_data($this, oGrid) {
		var current_row = $this.parents('tr');//Get the current row
		if (current_row.hasClass('child')) {//Check if the current row is a child row
			current_row = current_row.prev();//If it is, then point to the row before it (its 'parent')
		}
		var oData = oGrid.DataTable().row(current_row).data();
		return oData;
	}

	/* ..:: Capturamos los errores ::.. */
	function handleAjaxError( xhr, textStatus, error ) {
		if ( textStatus === 'timeout' ) {
			show_modal_error('El servidor tardó demasiado en enviar los datos');
		} else {
			show_modal_error('Se ha producido un error en el servidor, error: [' + error + ']. Por favor espera.');
		}
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
			if(!$('#modalloadconfig').is(":visible")){
				$('#modalloadconfig').modal({ show: true, backdrop: 'static', keyboard: false });
			}
			$("#modalloadconfig_mensaje").html(sGifLoader + '&nbsp;' + sMensaje);
		} else {
			$('#modalloadconfig').modal('hide');
		}
	}
	/* ..:: Funcion que muestra el mensaje de ok ::.. */
	function show_modal_ok(sMensaje) {
		if (sMensaje == null || sMensaje == undefined) {
			sMensaje = '';
		}
		
		$('#modalmessagebox_ok_titulo').html(appName);
		$('#modalmessagebox_ok_mensaje').html('<i class="fa fa-check"></i> ' + sMensaje);						
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
		
		$('#modalmessagebox_error_span').html("ERROR :: " + appName);
		$('#modalmessagebox_error_mensaje').html('<i class="fa fa-ban"></i> ' + sMensaje);
		setTimeout(function () {
			$('#modalmessagebox_error').modal({ show: true });
		},500);
	}
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
	/* ..:: Funcion muestra mensajes de consultar en div personalizado ::.. */
	function show_custom_function_loading(sMensaje, oDivDisplay) {
		sMensaje = ((sMensaje == null || sMensaje == undefined)? '': sMensaje);
		oDivDisplay = ((oDivDisplay == null || oDivDisplay == undefined)? '': oDivDisplay);
		//sStyle = ((sStyle == null || sStyle == undefined)? '': sStyle);
		if (oDivDisplay != '') {
			if (sMensaje != '') {
				var sHtml = '<div class="alert alert-info">';
				sHtml +=		sGifLoader + sMensaje;
				sHtml +=    '</div>';	
				
				$('#' + oDivDisplay).html(sHtml).show();
			} else {
				$('#' + oDivDisplay).hide();
			}
		} else {		
			show_modal_error('No se proporciono contenedor para el mensaje!');
		}
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

	/*********************************************************************************************************************************
	** MODALS FUNCTIONS                                                                                                            **
	*********************************************************************************************************************************/

	function fnc_show_modal_fraccion_add_edit(){
		switch(sAccion){
			case 'Nuevo':
				$("#lbl_titulo_agregar_fraccion").html('<i class="fa fa-plus" aria-hidden="true"></i> Nueva Fracci&oacute;n');
				fnc_limpiar_controles_fraccion_mdl();
				break;
			case 'Editar':
				$("#lbl_titulo_agregar_fraccion").html('<i class="fa fa-pencil" aria-hidden="true"></i> Editar Fracci&oacute;n');
				break;
			default:
				$("#lbl_titulo_agregar_fraccion").html('Fracci&oacute;n');
		}
		$('#modal_fraccion').modal({show: true});
		setTimeout(function (){$("#itxt_mdl_fraccion_descripcion").focus();},500);
	}

	function fnc_show_modal_procesar_referencia_r8va(){
		$("#lbl_titulo_procesar_referencia").html('<i class="fa fa-cogs" aria-hidden="true"></i> Aplicar Regla 8va a Referencia');
		fnc_limpiar_controles_procesar_referencia_mdl();
		$('#modal_procesar_ref').modal({show: true});
		setTimeout(function (){$("#itxt_mdl_proref_referencia").focus();},500);
	}

	function fnc_show_modal_cerrar_referencia(){
		$("#lbl_titulo_cerrar_referencia").html('<i class="fa fa-list-alt" aria-hidden="true"></i> Cerrar Referencia');
		fnc_limpiar_controles_cerrar_referencia_mdl();
		$('#modal_referencia').modal({show: true});
		setTimeout(function (){$("#itxt_mdl_cerrar_ref_referencia").focus();},500);
	}

	function fnc_show_modal_fracciones_disponibles(){
		$("#lbl_titulo_aplicar_fraccion_partida").html('<i class="fa fa-list" aria-hidden="true"></i> Aplicar Regla 8va a la Partida de Fracciones Disponibles');
		//fnc_limpiar_controles_cerrar_referencia_mdl();
		$('#modal_fraccion_apli_par').modal({show: true});
		//setTimeout(function (){$("#itxt_mdl_cerrar_ref_referencia").focus();},500);
	}

	function fnc_show_modal_fraccion_original_eliminar_r8va(){
		$("#itxt_mdl_fraccion_original").val('');
		$('#modalfracciori').modal({show: true});
		setTimeout(function (){$("#itxt_mdl_fraccion_original").focus();},500);
	}
	
	function fnc_show_modal_aplicar_r8va_fraccion_pedimento(){
		$('#modalr8va_fracci').modal({show: true});
	}
	
	function fnc_show_modal_editar_parfac_fraccion(){
		if(sActParFacFracci == 'Editar'){
			$('#lbl_titulo_mdl_fraccion_parfac').html('Editar Partida Factura');
		}
		if(sActParFacFracci == 'Nuevo'){
			$('#lbl_titulo_mdl_fraccion_parfac').html('Agregar Partida Factura');
		}
		$('#modalr8va_fracci_parfac_editar').modal({show: true});
	}








