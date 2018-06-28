/* 
* Copyright (c) 2017 DEL BRAVO. - all right reserved
*/


	var appName = 'Monitor';
	var strSessionMessage = 'La sesión del usuario ha caducado, por favor acceda de nuevo.';

	var oTablePedimentos;
	var oTableRangos;
	
	var idPedimento = '';
	var sAccion = '';
	
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
			fcn_cargar_grid_librop();
			setTimeout(function () {fcn_cargar_grid_rangos();},3000);
			
			/*$('#modal_mostrar_pedimento').modal({
				 show: true
			 });*/
			
			$('#idt_pedi_mod_fecha').datepicker({
				todayHighlight:true,
				autoclose: true
				//clearBtn: true
			}).data('datepicker');
			
			$("#idt_pedi_mod_fecha").datepicker("setDate", new Date());
			
			
	
		} catch (err) {		
			var strMensaje = 'application_run() :: ' + err.message;
			show_label_error(strMensaje);
		}
	}
	
	function toUpper(control) {
		if (/[a-z]/.test(control.value)) {
			control.value = control.value.toUpperCase();
		}
	}
	
	function guardar_nuevo_pedimentos(){
		switch (sAccion){
			case 'Agregar' :
				ajax_set_nuevo_pedimentos();
				break;
			case 'Editar' :
				ajax_set_editar_pedimento();
				break;
		}
	}
	/* ********************************************************************************************************************************
	** RANGOS                                                                                                      **
	******************************************************************************************************************************* **/
	
	function fcn_cargar_grid_rangos(){
		oTableRangos = $('#dtrangos');
			
		oTableRangos.DataTable({
			//"order": [[2, 'desc']],
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "ajax/librop/post_carga_rangos.php",
				"type": "POST"
			},
			"columns": [ 
				{ "data": "id_rango"},		
				{ "data": "aduana"},				
				{ "data": "patente"},				
				{ "data": "anio"},
				{ "data": "pedimento_inicial"},
				{ "data": "pedimento_final"},
				{ "data": "fecha_registro"}
			],
			responsive: true,
			aLengthMenu: [
				[25, 50, 100, 200, -1],
				[25, 50, 100, 200, "All"]
			],
			iDisplayLength: 25,
			language: {
				sProcessing: '<img src="../images/cargando.gif" height="36" width="36"> Cargando, espera un momento por favor...',
				lengthMenu: "Mostrar _MENU_ registros por p&aacute;gina",
				search:         "Buscar:",
				info: "Mostrando p&aacute;gina _PAGE_ de _PAGES_ p&aacute;ginas de _TOTAL_ registros",
				zeroRecords:    "No se encontraron registros",
				infoEmpty:      "Mostrando 0 a 0 de 0 registros",
				infoFiltered:   "(filtrado de un total de _MAX_ registros)",
				paginate: {
					first:      "|< Primero",
					last:       "&Uacute;ltimo >|",
					next:       "Siguiente >",
					previous:   "< Anterior"
				}
			}
		}).columns.adjust().draw();
		
		setInterval( function () {
			ajax_get_grid_data_rangos();
		}, 600000);
	}
		
	function fcn_nuevo_rango_open(){
		limpiar_controles_modal_rangos();
		$('#modal_add_rango').modal({show: true});
	}

	function limpiar_controles_modal_rangos(){
		var sAnio = new Date().getFullYear();
		$('#isel_rango_mod_anio').val(sAnio.toString());
		//$('#isel_rango_mod_aduana').val('1');
		//$('#isel_rango_mod_patente').val('1664');
		
		$('#itxt_rango_mod_ped_ini').val('');
		$('#itxt_rango_mod_ped_fin').val('');
		$('#itxt_rango_mod_observaciones').val('');
		
		$('#idiv_rango_mod_mensaje').html('');
		
	}

	function valida_controles_modal_rangos(){
		if($('#itxt_rango_mod_ped_ini').val().trim().length < 7){
			$('#idiv_rango_mod_mensaje').html('<div class="alert alert-danger alert-dismissible" role="alert">Por favor, verifique que el número de pedimento inicial este correcto.</div>');
			$('#itxt_rango_mod_ped_ini').focus();
			return false;
		}else{
			if($('#itxt_rango_mod_ped_ini').val().trim().substring(0,1) != $('#isel_rango_mod_anio').val().substring(3,4)){
				$('#idiv_rango_mod_mensaje').html('<div class="alert alert-danger alert-dismissible" role="alert">El pedimento inicial debe iniciar con el último dígito del año seleccionado.</div>');
				$('#itxt_rango_mod_ped_ini').focus();
				return false;
			}
		}
		if($('#itxt_rango_mod_ped_fin').val().trim().length < 7){
			$('#idiv_rango_mod_mensaje').html('<div class="alert alert-danger alert-dismissible" role="alert">Por favor, verifique que el número de pedimento inicial este correcto.</div>');
			$('#itxt_rango_mod_ped_fin').focus();
			return false;
		}else{
			if($('#itxt_rango_mod_ped_fin').val().trim().substring(0,1) != $('#isel_rango_mod_anio').val().substring(3,4)){
				$('#idiv_rango_mod_mensaje').html('<div class="alert alert-danger alert-dismissible" role="alert">El pedimento final debe iniciar con el último dígito del año seleccionado.</div>');
				$('#itxt_rango_mod_ped_fin').focus();
				return false;
			}
		}
		if(eval($('#itxt_rango_mod_ped_ini').val().trim()) >= eval($('#itxt_rango_mod_ped_fin').val().trim())){
			$('#idiv_rango_mod_mensaje').html('<div class="alert alert-danger alert-dismissible" role="alert">El pedimento inicial debe ser menor al pedimento final.</div>');
			return false;
		}
		return true;
	}

	/* -----------------------------------------
			AJAX
	----------------------------------------- */
	
	function ajax_set_nuevo_rango(){
		if(!valida_controles_modal_rangos()){
			return false;
		}
		
		var oData = {
			anio: $('#isel_rango_mod_anio').val().trim(),
			id_aduana: $('#isel_rango_mod_aduana').val().trim(),
			patente: $('#isel_rango_mod_patente').val().trim(),
			pedimento_ini: $('#itxt_rango_mod_ped_ini').val().trim(),
			pedimento_fin: $('#itxt_rango_mod_ped_fin').val().trim(),
			observaciones: $('#itxt_rango_mod_observaciones').val().trim()
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/librop/ajax_set_nuevo_rango.php',
            data: oData,
            beforeSend: function (dataMessage) {
				$('#idiv_rango_mod_mensaje').html('<div class="alert alert-info alert-dismissible" role="alert"><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Generando rango, espere un momento por favor...</div>');
			},
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					$('#idiv_rango_mod_mensaje').html('');					
					if (respuesta.Codigo == '1'){
						ajax_get_grid_data_rangos();
						$('#modal_add_rango').modal('hide');						
					}else{
						$('#idiv_rango_mod_mensaje').html('<div class="alert alert-danger alert-dismissible" role="alert">'+respuesta.Mensaje+'['+respuesta.Error+']</div>');
					}
				}else{
					$('#idiv_rango_mod_mensaje').html('<div class="alert alert-danger alert-dismissible" role="alert">'+strSessionMessage+'</div>');					
					setTimeout(function () {window.location.replace('../logout.php');},3000);
				}				
			},
			error: function(a,b){
				$('#idiv_rango_mod_mensaje').html('<div class="alert alert-danger alert-dismissible" role="alert">' + a.status+' [' + a.statusText + ']</div>');
			}
        });
	}
	
	function ajax_get_grid_data_rangos() {
		var table = oTableRangos.DataTable();
		table.ajax.reload();
	}
	
	/* ********************************************************************************************************************************
	** PEDIMENTOS                                                                                                      **
	******************************************************************************************************************************* **/
	
	function fcn_cargar_grid_librop(){
		oTablePedimentos = $('#dtpedimentos');
			
		oTablePedimentos.DataTable({
			//"order": [[2, 'desc']],
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "ajax/librop/post_carga_librop.php",
				"type": "POST"
			},
			"columns": [ 
				{ "data": "id_librop"},
				{ "data": "id_librop",
					"mRender": function (data, type, row) {
						var sBtnAction = '';
						sBtnAction += '<a href="javascript:void(0);" onclick="editar_informacion_pedimento(\''+data+'\');return false;" style="padding-left:.5em;" title="Editar">[ <i class="fa fa-pencil" aria-hidden="true"></i> Editar ]</a>';
						return sBtnAction;
					}
				},					
				{ "data": "aduana"},
				{ "data": "patente"},
				{ "data": "pedimento"},
				{ "data": "referencia"},
				{ "data": "anio"},
				{ "data": "cliente"},
				{ "data": "tipo_operacion"},
				{ "data": "clave_pedimento"},
				{ "data": "descripcion_mercancia"},
				{ "data": "observaciones"}
			],
			responsive: true,
			aLengthMenu: [
				[25, 50, 100, 200, -1],
				[25, 50, 100, 200, "All"]
			],
			iDisplayLength: 25,
			language: {
				sProcessing: '<img src="../images/cargando.gif" height="36" width="36"> Cargando, espera un momento por favor...',
				lengthMenu: "Mostrar _MENU_ registros por p&aacute;gina",
				search:         "Buscar:",
				info: "Mostrando p&aacute;gina _PAGE_ de _PAGES_ p&aacute;ginas de _TOTAL_ registros",
				zeroRecords:    "No se encontraron registros",
				infoEmpty:      "Mostrando 0 a 0 de 0 registros",
				infoFiltered:   "(filtrado de un total de _MAX_ registros)",
				paginate: {
					first:      "|< Primero",
					last:       "&Uacute;ltimo >|",
					next:       "Siguiente >",
					previous:   "< Anterior"
				}
			}
		}).columns.adjust().draw();
		
		setInterval( function () {
			ajax_get_grid_data_pedimentos();
		}, 180000);
	}

	function fcn_nuevo_pedimento_open(){
		fcn_limpiar_controles_modal_pedimentos();
		$("#isel_pedi_mod_anio").prop('disabled', false);
		$("#isel_pedi_mod_aduana").prop('disabled', false);
		$("#isel_pedi_mod_patente").prop('disabled', false);
		sAccion = 'Agregar';		
		$('#lbl_modal_titulo_pedimento').html('Generar nuevo número de pedimento');
		$('#modal_add_pedimento').modal({show: true});
		setTimeout(function () {$('#isel_pedi_mod_aduana').focus();},500);
	}
	
	function fcn_limpiar_controles_modal_pedimentos(){
		var sAnio = new Date().getFullYear();
		$('#isel_pedi_mod_anio').val(sAnio.toString());
		//$('#isel_pedi_mod_aduana').val('1');
		//$('#isel_pedi_mod_patente').val('1664');
		$('#itxt_pedi_mod_referencia').val('');
		$("#idt_pedi_mod_fecha").datepicker("setDate", new Date());
		$('#isel_pedi_mod_cliente').val('0');
		$('#isel_pedi_mod_operacion').val('1');
		$('#itxt_pedi_mod_cve_pedimento').val('');
		$('#itxt_pedi_mod_desc_mercancia').val('');
		$('#itxt_pedi_mod_observaciones').val('');
	}

	function fcn_valida_controles_modal_pedimentos(){
		if($('#itxt_pedi_mod_referencia').val().trim() == ''){
			$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-danger alert-dismissible" role="alert">Por favor, verifique que el número de referencia.</div>');
			$('#itxt_pedi_mod_referencia').focus();
			return false;
		}
		if($('#fecha_pedimento').val().trim() == ''){
			$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-danger alert-dismissible" role="alert">Por favor, seleccione la fecha del pedimento.</div>');
			$('#fecha_pedimento').focus();
			return false;
		}
		if($('#isel_pedi_mod_cliente').val() == '0'){
			$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-danger alert-dismissible" role="alert">Es necesario seleccionar el cliente.</div>');
			$('#isel_pedi_mod_cliente').focus();
			return false;
		}
		if($('#itxt_pedi_mod_cve_pedimento').val().trim().length < 2){
			$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-danger alert-dismissible" role="alert">Por favor, verifique la clave del pedimento.</div>');
			$('#itxt_pedi_mod_cve_pedimento').focus();
			return false;
		}
		
		return true;
	}
	
	/* -----------------------------------------
			AJAX
	----------------------------------------- */
	function ajax_set_nuevo_pedimentos(){
		if(!fcn_valida_controles_modal_pedimentos()){
			return false;
		}
		
		var oData = {
			anio: $('#isel_pedi_mod_anio').val().trim(),
			id_aduana: $('#isel_pedi_mod_aduana').val().trim(),
			patente: $('#isel_pedi_mod_patente').val().trim(),
			referencia: $('#itxt_pedi_mod_referencia').val().trim(),
			fecha: $('#fecha_pedimento').val().trim(),
			id_cliente: $('#isel_pedi_mod_cliente').val().trim(),
			cliente: $('#isel_pedi_mod_cliente option:selected').text(),
			operacion: $('#isel_pedi_mod_operacion').val(),
			cve_pedimento: $('#itxt_pedi_mod_cve_pedimento').val().trim(),
			descripcion: $('#itxt_pedi_mod_desc_mercancia').val().trim(),
			observaciones: $('#itxt_pedi_mod_observaciones').val().trim()
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/librop/ajax_set_nuevo_pedimento.php',
            data: oData,
            beforeSend: function (dataMessage) {
				$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-info alert-dismissible" role="alert"><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Generando número de pedimento, espere un momento por favor...</div>');
			},
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					$('#idiv_modal_mensaje_pedimento').html('');					
					if (respuesta.Codigo == '1'){
						ajax_get_grid_data_pedimentos();
						$('#modal_add_pedimento').modal('hide');
						
						var sHTML = '';
						sHTML += '<div class="alert alert-success alert-dismissible" role="alert">';
						sHTML += '	<span id="lbl_aduana_mod_asig"><h3>PEDIMENTO: '+respuesta.Pedimento+'</h3></span>';
						sHTML += '	<span id="lbl_aduana_mod_asig"><h4>ADUANA: '+$("#isel_pedi_mod_aduana option:selected").text()+'</h4></span>';
						sHTML += '	<span id="lbl_aduana_mod_asig"><h4>PATENTE: '+$("#isel_pedi_mod_patente option:selected").text()+'</h4></span>';
						sHTML += '	<span id="lbl_aduana_mod_asig"><h4>REFERENCIA: '+$('#itxt_pedi_mod_referencia').val().trim()+'</h4></span>';
						sHTML += '</div>';
						
						$('#idiv_mensaje_pedimento_generado').html(sHTML);
						setTimeout(function () {$('#modal_mostrar_pedimento').modal({show: true});},500);
						
						
					}else{
						$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-danger alert-dismissible" role="alert">'+respuesta.Mensaje+'['+respuesta.Error+']</div>');
					}
				}else{
					$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-danger alert-dismissible" role="alert">'+strSessionMessage+'</div>');					
					setTimeout(function () {window.location.replace('../logout.php');},3000);
				}				
			},
			error: function(a,b){
				$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-danger alert-dismissible" role="alert">' + a.status+' [' + a.statusText + ']</div>');
			}
        });

	}
	
	function ajax_get_grid_data_pedimentos() {
		var table = oTablePedimentos.DataTable();
		table.ajax.reload();
	}
	
	function editar_informacion_pedimento(idPedi){
		idPedimento = idPedi;
		fcn_limpiar_controles_modal_pedimentos();
		$.ajax({
			url:   'ajax/librop/ajax_consultar_librop.php',
			type:  'post',
			data: {id_librop:idPedimento},
			beforeSend: function () { 
				$("#mensaje_librop_editar").html('<div class="success alert-success alert-dismissible" role="alert"><img src="../images/cargando.gif" height="16" width="16"/> Consultando información, espere un momento por favor...</div>');
			},
			success:  function (response) {
				if (response != '500'){
					respuesta = JSON.parse(response);
					$('#modalloadconfig').modal('hide');
					if (respuesta.Codigo == '1'){
						$('#mensaje_librop_editar').html('');
						
						$('#isel_pedi_mod_anio').val(respuesta.anio);
						$('#isel_pedi_mod_aduana').val(respuesta.id_aduana);
						$('#isel_pedi_mod_patente').val(respuesta.patente);
						$('#itxt_pedi_mod_referencia').val(respuesta.referencia);
						$("#idt_pedi_mod_fecha").datepicker("setDate", respuesta.fecha_pedimento);
						$('#isel_pedi_mod_cliente').val(respuesta.id_cliente);
						$('#isel_pedi_mod_operacion').val(respuesta.tipo_operacion);
						$('#itxt_pedi_mod_cve_pedimento').val(respuesta.clave_pedimento);
						$('#itxt_pedi_mod_desc_mercancia').val(respuesta.descripcion_mercancia);
						$('#itxt_pedi_mod_observaciones').val(respuesta.observaciones);
						
						$("#isel_pedi_mod_anio").prop('disabled', true);
						$("#isel_pedi_mod_aduana").prop('disabled', true);
						$("#isel_pedi_mod_patente").prop('disabled', true);
						
						sAccion = 'Editar';
						$('#lbl_modal_titulo_pedimento').html('Editar información del pedimento');
						$('#modal_add_pedimento').modal({show: true});
					}else{
						$('#mensaje_librop_editar').html('<div class="success alert-danger alert-dismissible" role="alert">Error al consultar la referencia.['+respuesta.Mensaje+']</div>');
					}
				}else{
					$('#mensaje_librop_editar').html('<div class="success alert-danger alert-dismissible" role="alert">La sesión del usuario ha terminado.</div>');
					setTimeout(function () {window.location.replace('logout.php');},4000);
				}
			},
			error: function(a,b){
				$("#mensaje_librop_editar").html('<div class="success alert-danger alert-dismissible" role="alert">'+a.status+' ['+a.statusText+']</div>');
			}
		});
	}

	function ajax_set_editar_pedimento(){
		if(!fcn_valida_controles_modal_pedimentos()){
			return false;
		}
		
		var oData = {
			id_librop: idPedimento,
			referencia: $('#itxt_pedi_mod_referencia').val().trim(),
			fecha: $('#fecha_pedimento').val().trim(),
			id_cliente: $('#isel_pedi_mod_cliente').val().trim(),
			cliente: $('#isel_pedi_mod_cliente option:selected').text().replace("'", " "),
			operacion: $('#isel_pedi_mod_operacion').val(),
			cve_pedimento: $('#itxt_pedi_mod_cve_pedimento').val().trim(),
			descripcion: $('#itxt_pedi_mod_desc_mercancia').val().trim(),
			observaciones: $('#itxt_pedi_mod_observaciones').val().trim()
		};
		
		$.ajax({
            type: "POST",
            url: 'ajax/librop/ajax_set_editar_pedimento.php',
            data: oData,
            beforeSend: function (dataMessage) {
				$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-info alert-dismissible" role="alert"><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Generando número de pedimento, espere un momento por favor...</div>');
			},
            success:  function (response) {
				if (response != '500'){
					var respuesta = JSON.parse(response);
					$('#idiv_modal_mensaje_pedimento').html('');					
					if (respuesta.Codigo == '1'){
						ajax_get_grid_data_pedimentos();
						$('#modal_add_pedimento').modal('hide');
						ajax_get_grid_data_pedimentos();
					}else{
						$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-danger alert-dismissible" role="alert">'+respuesta.Mensaje+'['+respuesta.Error+']</div>');
					}
				}else{
					$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-danger alert-dismissible" role="alert">'+strSessionMessage+'</div>');					
					setTimeout(function () {window.location.replace('../logout.php');},3000);
				}				
			},
			error: function(a,b){
				$('#idiv_modal_mensaje_pedimento').html('<div class="alert alert-danger alert-dismissible" role="alert">' + a.status+' [' + a.statusText + ']</div>');
			}
        });
	}










