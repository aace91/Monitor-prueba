	/* 
	* Application: Monitor Del Bravo
	* 
	* 
	* 
	* Copyright (c) 2017 DEL BRAVO. - all right reserved
	*/

	/*********************************************************************************************************************************
	** GLOBALS DEFINITION SECTION                                                                                                   **
	*********************************************************************************************************************************/

	/* ..:: App Vars ::.. */
	var appName = 'Monitor';
	var strSessionMessage = 'La sesión del usuario ha caducado, por favor acceda de nuevo.';
	var sGifLoader = '<img src="../images/cargando.gif" height="16" width="16"/>';

	var oPrincipalGeneralGrid = null;
	
	var initEditor = null;
	var initEditorProv = null;
	
	var sPatente = '';
	
	/*********************************************************************************************************************************
	** BEGIN APPLICATION                                                                                                            **
	*********************************************************************************************************************************/

	$(document).ready(function () {
		//Esta funcion se dispara cuando se terminan de cargar todos los elementos de la pagina { .js, .css, images, etc. }
		application_load();
				
	});


	/*********************************************************************************************************************************
	** EXTERNAL APPLICATIONS                                                                                                        **
	*********************************************************************************************************************************/

	/*********************************************************************************************************************************
	** WINDOW RESIZE                                                                                                                **
	*********************************************************************************************************************************/

	function onWinResize() {
		if(initEditor != null){
			$('#edit_inicial').sceditor('instance').width($('#contanier_jum').width()- 40);
		}
		if(initEditorProv != null){
			$('#edit_prov').sceditor('instance').width($('#contanier_jum').width()- 40);
		}
	}

	/*********************************************************************************************************************************
	** Application LOAD { Load Preliminar Data by Ajax }                                                                            **
	*********************************************************************************************************************************/

	function application_load() {
		application_form();
		application_run();
	}

	/*********************************************************************************************************************************
	** APPLICATION FORM { Generate HTML Content }                                                                                   **
	*********************************************************************************************************************************/

	function application_form() {
		$(window).resize(function() { onWinResize(); }); onWinResize();
	}

	/*********************************************************************************************************************************
	** APPLICATION RUN                                                                                                              **
	*********************************************************************************************************************************/

	function application_run() {
		try {
			initEditor = $('#edit_inicial').sceditor({
						plugins: 'xhtml',
						toolbar: "bold,italic,underline,strike|left,center,right,justify"
					});
					
			initEditorProv = $('#edit_prov').sceditor({
						plugins: 'xhtml',
						toolbar: "bold,italic,underline,strike|left,center,right,justify"
					});
					
			texto_encabezado_documento_texto();
			
		} catch (err) {		
			var strMensaje = 'application_run() :: ' + err.message;
			show_modal_error(strMensaje);
		}
	}
	
	function ajax_descargar_carta_protesta(){
		
		var oForm = document.createElement("form");
		oForm.target = 'data';
		oForm.method = 'POST'; // or "post" if appropriate
		oForm.action = 'ajax/carta_protesta/ajax_descargar_carta_protesta.php';

		var oInput = document.createElement("input");
		oInput.type = "text"; oInput.name = "fecha"; 
		oInput.value = $('#lbl_fecha_documento').html().trim(); oForm.appendChild(oInput);
		
		var oInput = document.createElement("input");
		oInput.type = "text"; oInput.name = "aduana"; 
		oInput.value = $('#lbl_aduana').html().trim(); oForm.appendChild(oInput);
		//
		var oInput = document.createElement("input");
		oInput.type = "text"; oInput.name = "patente"; 
		oInput.value = sPatente; oForm.appendChild(oInput);
		
		var oInput = document.createElement("input");
		oInput.type = "text"; oInput.name = "texto_encabezado"; 
		oInput.value = $('#edit_inicial').sceditor('instance').val(); oForm.appendChild(oInput);

		var oInput = document.createElement("input");
		oInput.type = "text"; oInput.name = "gastos"; 
		oInput.value = $('#txt_gastos').val().trim(); oForm.appendChild(oInput);

		var oInput = document.createElement("input");
		oInput.type = "text"; oInput.name = "fletes"; 
		oInput.value = $('#txt_fletes').val().trim(); oForm.appendChild(oInput);

		var oInput = document.createElement("input");
		oInput.type = "text"; oInput.name = "seguros"; 
		oInput.value = $('#txt_seguros').val().trim(); oForm.appendChild(oInput);

		var oInput = document.createElement("input");
		oInput.type = "text"; oInput.name = "otros"; 
		oInput.value = $('#txt_otros').val().trim(); oForm.appendChild(oInput);

		var oInput = document.createElement("input");
		oInput.type = "text"; oInput.name = "texto_proveedores"; 
		oInput.value = $('#edit_prov').sceditor('instance').val(); oForm.appendChild(oInput);


		document.body.appendChild(oForm);
		oForm.submit();
		$(oForm).remove();
	}
	
	function ajax_buscar_referencia_carta(){
		
		if($('#txt_referenica_cartaprotesta').val().trim() == ''){
			show_modal_error('Es necesario especificar el número de la referencia.');
			return;
		}
		
		limpiar_controles_carta();
		
		var oData = new FormData();	
		oData.append('referencia',$('#txt_referenica_cartaprotesta').val().trim().toUpperCase());
			
		$.ajax({
			type: "POST",
			url: 'ajax/carta_protesta/ajax_consultar_referencia_carta.php',
			data: oData,
			contentType: false,
			cache: false,
			processData:false,
			timeout: 0,
			xhr: function() {
				$('#modal_info').modal({show: true,backdrop: 'static',keyboard: false});
				
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt){
				  if (evt.lengthComputable) {
					var percent = evt.loaded / evt.total * 100;
					if(percent > 89) 
						percent = 90;
					var sMen = '<div class="progress progress-striped active">';
					sMen += '		<div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
					sMen += '			<span>'+percent+'% Completado, Consultando informaci&oacute;n, espere un momento por favor...</span>';
					sMen += '		</div>';
					sMen += '	</div>';
					$('#idiv_modal_info_mensaje').html(sMen);
				  }
				}, false);
				return xhr;
			},
			beforeSend: function (dataMessage) {
				
			},
			success:  function (response) {
				$('#modal_info').modal('hide');		

				if (response != '500'){
					var respuesta = JSON.parse(response);
					
					if (respuesta.Codigo == '1'){
						$('#modal_nvo_permiso').modal('hide');
						
						var Carga = eval(respuesta.CARGA);
						var Fletes = eval(respuesta.FLETES);
						var Seguros = eval(respuesta.SEGUROS);
						var Otros = (eval(respuesta.EMBALAJES) + eval(respuesta.REVERSI) + eval(respuesta.REGALIAS) + eval(respuesta.COMI) + eval(respuesta.MATERIAL) + eval(respuesta.TECNO));
						
						$('#txt_gastos').val(Carga.toFixed(2));
						$('#txt_fletes').val(Fletes.toFixed(2));
						$('#txt_seguros').val(Seguros.toFixed(2));
						$('#txt_otros').val(Otros.toFixed(2));
						
						var nomAAduanal = '';
						
						switch(respuesta.Patente){
							case '3483':
								nomAAduanal = 'MANUEL JOSE ESTANDIA FERNANDEZ';
								break;
							case '1664':
								nomAAduanal = 'HUGO NISHIYAMA DE LA GARZA';
								break;
						}
						
						switch(respuesta.Aduana){
							case '240':
								$('#lbl_aduana').html('NUEVO LAREDO, TAMPS.');								
								break;
							case '800':
								$('#lbl_aduana').html('COLOMBIA, NL.');
								break;
						}
						
						var sHtml = '<div style="text-align: justify;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>'+nomAAduanal+':</b> CON MI CARACTER DE AGENTE ADUANAL ENCARGADO DEL DESPACHO ADUANERO DE LAS MERCANCÍAS QUE SE DESCRIBEN EN LA FACTURA COMERCIAL QUE SE CITA AL RUBRO, Y EN CUMPLIMIENTO A LO DISPUESTO POR EL TERCER PÁRRAFO DE LA REGLA <b>3.1.7</b>. DE LAS REGLAS GENERALES DE COMERCIO EXTERIOR PARA <b>2018</b>, PUBLICADAS EN EL DIARIO OFICIAL DE LA FEDERACIÓN EL DÍA <b>18 DE DICIEMBRE DE 2017</b>., Y EN CORRELACIÓN CON LO PRECEPTUADO EN LOS ARTÍCULOS <b>36-A</b>, FRACCIÓN <b>I</b>, INCISO <b>A</b>) Y <b>41</b> DE LA LEY ADUANERA., DECLARO BAJO PROTESTA DE DECIR VERDAD, QUE LAS ENMENDADURAS O ANOTACIONES QUE ALTERAN LOS DATOS ORIGINALES, ASÍ COMO LOS DATOS Y REQUISITOS QUE FALTAN EN LA FACTURA COMERCIAL ANTES CITADA Y QUE SE DECLARAN A CONTINUACIÓN, SON VERDADEROS Y CORRECTOS:</div><justify></justify>';
						$('#edit_inicial').sceditor('instance').val(sHtml);
						
						var sHtml_Prov = '<div style="text-align: left;">\
												<p>\
													<b>DATOS DEL IMPORTADOR:</b>\
												</p>\
												<p style="margin-left:70.8pt;text-indent:35.4pt;mso-outline-level:1">\
													<b>'+respuesta.nom_cli+'</b>\
												</p>\
												<p style="margin-left:70.8pt;text-indent:35.4pt;mso-outline-level:1">\
													<b>'+respuesta.dir_cli+'</b>\
												</p>\
												<p style="margin-left:70.8pt;text-indent:35.4pt;mso-outline-level:1">\
													<b>'+respuesta.cd_cli+'</b>\
												</p>\
												<p style="margin-left:70.8pt;text-indent:35.4pt;mso-outline-level:1">\
													<b>'+respuesta.rfc_cli+'</b>\
												</p>';
						for(i=0; i < respuesta.aProveedores.length; i++){
							sHtml_Prov += ' 	<p>\
													<b>DATOS DEL PROVEEDOR: </b>\
												</p>\
												<p style="margin-left:70.8pt;text-indent:35.4pt;mso-outline-level:1">\
													<b>'+respuesta.aProveedores[i][0]+'</b>\
												</p>\
												<p style="margin-left:70.8pt;text-indent:35.4pt;mso-outline-level:1">\
													<b>'+respuesta.aProveedores[i][1]+'</b>\
												</p>\
												<p style="margin-left:70.8pt;text-indent:35.4pt;mso-outline-level:1">\
													<b>'+respuesta.aProveedores[i][2]+'</b>\
												</p>\
												<p style="margin-left:70.8pt;text-indent:35.4pt;mso-outline-level:1">\
													<b>'+respuesta.aProveedores[i][3]+'</b>\
												</p>\
												<p style="margin-left:20.8pt;text-indent:15.4pt;mso-outline-level:1">\
													<b>FACTURAS:&nbsp;'+respuesta.aProveedores[i][4]+'</b>\
												</p>';
						}
						sHtml_Prov += ' 		<p>\
													<b>INCOTERM: ';
						for(i=0; i < respuesta.aIcoterms.length; i++){
							if(i == 0){
								sHtml_Prov += respuesta.aIcoterms[i][0];
							}else{
								sHtml_Prov += ',' + respuesta.aIcoterms[i][0];
							}
						}
						sHtml_Prov += ' 			</b>\
												</p>\
											</div>';
		
						$('#edit_prov').sceditor('instance').val(sHtml_Prov);
						
						sPatente = respuesta.Patente;
						switch(respuesta.Patente){
							case '3483':
								var sHtml = '\
									<div class="row">\
										<div class="col-xs-12 text-center">\
											<img src="../images/firma_estandia.png" height="150" width="150">\
										</div>\
									</div>\
									<div class="row">\
										<div class="col-xs-12 text-center">\
											<p style="font-family:arial; font-size:12px;"><strong>_________________________________</strong></p>\
										</div>\
									</div>\
									<div class="row">\
										<div class="col-xs-12 text-center">\
											<p style="font-family:arial; font-size:12px;"><strong>AGENTE ADUANAL</strong></p>\
										</div>\
									</div>\
									<div class="row">\
										<div class="col-xs-12 text-center">\
											<p style="font-family:arial; font-size:12px;"><strong>Manuel Jos&eacute; Estandia Fern&aacute;ndez</strong></p>\
										</div>\
									</div>\
									<div class="row">\
										<div class="col-xs-12 text-center">\
											<p style="font-family:arial; font-size:12px;"><strong>EAFM620803BVA</strong></p>\
										</div>\
									</div>';
								$('#div_agente_aduanal').html(sHtml);
								break;
							case '1664':
								var sHtml = '\
									<div class="row">\
										<div class="col-xs-12 text-center">\
											<img src="../images/firma_hugo.png" height="111" width="246">\
										</div>\
									</div>';
								$('#div_agente_aduanal').html(sHtml);
								break;
						}
						
					} else {
						$('#modal_info').modal('hide');							
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
				
				$('#modal_info').modal('hide');	
			}
		});
	}

	function texto_encabezado_documento_texto(){
		var dt = new Date();
		var syear = dt.getFullYear();
		var options = {year: 'numeric', month: 'long', day: 'numeric' };
		var fecha = dt.toLocaleDateString("es-ES", options).toString();		
		$('#lbl_fecha_documento').html(fecha);
		
		$('#edit_inicial').sceditor('instance').val("");		
		$('#edit_prov').sceditor('instance').val("");
	}
	
	function limpiar_controles_carta(){
		$('#edit_prov').sceditor('instance').val("");
		$('#txt_gastos').val('');
		$('#txt_fletes').val('');
		$('#txt_seguros').val('');
		$('#txt_otros').val('');
	}
	
	
/* ..:: Capturamos los errores ::.. */
function handleAjaxError( xhr, textStatus, error ) {
	if ( textStatus === 'timeout' ) {
		show_message_error('El servidor tardó demasiado en enviar los datos');
	} else {
		show_message_error('Se ha producido un error en el servidor. Por favor espera.');
		
		setTimeout(function(){ hide_message(); }, 5000);
	}
}

/* ..:: Capturamos los errores ::.. */
function on_grid_error(e, settings, techNote, message) {
	var bExist = message.includes("Code [500]");
	if(bExist) {
		show_message_error(strSessionMessage);					
		setTimeout(function () {window.location.replace('../logout.php');},4000);
	} else {
		show_message_error('Ha ocurrido un error: ' + message);
		setTimeout(function(){ hide_message(); }, 5000);
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
		
		$('#modalloadconfig').modal({ show: true, backdrop: 'static', keyboard: false });
		$("#modalloadconfig_mensaje").html(sGifLoader + ' ' + sMensaje);
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

/********/

function fcn_get_message_error(sMensaje) {
	var sHtml = '<div class="alert alert-danger" style="margin-bottom:0px;">';
	sHtml +=	'	<strong>Error!</strong> ' + sMensaje;
	sHtml +=    '</div>';

	return sHtml;
}

/********/
 
 /* ..:: Ocultamos mensajes ::.. */
 function hide_message() {
	$('#idiv_message').hide();
 }
 
/* ..:: Funcion que muestra el mensaje de informacion ::.. */
function show_message_info(sMensaje) {
	if (sMensaje == null || sMensaje == undefined || sMensaje == '') {
		$('#idiv_message').hide();
	} else {
		var sHtml = '<div class="alert alert-info" style="margin-top: 8px; margin-bottom: 8px;">';
		sHtml +=	'	<strong>Info!</strong> ' + sMensaje;
		sHtml +=    '</div>';
		
		$('#idiv_message').html(sHtml);
		$('#idiv_message').show();
	}
}

/* ..:: Funcion que muestra el mensaje de ok ::.. */
function show_message_ok(sMensaje) {
	if (sMensaje == null || sMensaje == undefined || sMensaje == '') {
		$('#idiv_message').hide();
	} else {
		var sHtml = '<div class="alert alert-success" style="margin-top: 8px; margin-bottom: 8px;">';
		sHtml +=	'	<strong>Exito!</strong> ' + sMensaje;
		sHtml +=    '</div>';
		
		$('#idiv_message').html(sHtml);
		$('#idiv_message').show();
	}
}

/* ..:: Funcion que muestra el mensaje de error ::.. */
function show_message_error(sMensaje) {
	if (sMensaje == null || sMensaje == undefined || sMensaje == '') {
		$('#idiv_message').hide();
	} else {
		var sHtml = '<div class="alert alert-danger" style="margin-top: 8px; margin-bottom: 8px;">';
		sHtml +=	'	<strong>Error!</strong> ' + sMensaje;
		sHtml +=    '</div>';
		
		$('#idiv_message').html(sHtml);
		$('#idiv_message').show();
	}
}