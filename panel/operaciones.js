var pedimentog;

function guardacom(remesa,pedimento,remision){
	divap =  $("#mensajeaprov");
	if (document.getElementById("aprueba")){
		aprueba = document.getElementById("aprueba").checked;
	}else{
		aprueba = false;
	}
	comentario = document.getElementById("comentario").value;
	$.ajax({
		url:   'guardacom.php',
		type:  'post',
		data:	{remesa: remesa, pedimento: pedimento, remision: remision, comentario:comentario, aprueba:aprueba},
		beforeSend: function () {
				divap.attr('class','class="alert alert-success"');
				$("#mensajeaprov").html('<br><center><div class="alert alert-info" role="alert"><img src="../images/cargando.gif" height="36" width="36">Guardando, espere por favor...</div></center>');
		},
		success:  function (response) {
				$("#mensajeaprov").html(response);
				if(response.indexOf("enviada") > -1){
					$('#Aprobacion').remove();
					var table = $('#example').DataTable();
					table.ajax.reload();
				}
				if(response.indexOf("almacenado") > -1){
					$('#Aprobacion').remove();
					$('#divcomentario').remove();
					$('#divguardar').remove();
					$('#divsuspender').remove();
					$.ajax({
						url:   'cargacomentarios.php',
						type:  'post',
						data:	{remesa: remesa, pedimento: pedimento, remision: remision},
						beforeSend: function () {
								$("#divshowcomentarios").html('<br><center><p><img src="../images/cargando.gif" height="36" width="36">Consultando, espere por favor...</p></center>');
						},
						success:  function (response) {
								$("#divshowcomentarios").html(response);
						}
					});
				}
		}
	});
}

function suspenderemesa(remesa,pedimento,remision,cove){
	var obj;
	if (cove!=''){
		$("#mensajeaprov").html('<br><center><div class="alert alert-danger" role="alert">La remesa no se ha suspendido debido a que ya cuenta con COVE generado, cualquier duda contacte a su ejecutivo</div></center>');
		return;
	}
	$.ajax({
		url:   'suspenderemesa.php',
		type:  'post',
		data:	{remesa: remesa, pedimento: pedimento, remision: remision},
		beforeSend: function () {
				$("#mensajeaprov").html('<br><center><p><img src="../images/cargando.gif" height="36" width="36">Guardando, espere por favor...</p></center>');
		},
		success:  function (response) {
				$("#mensajeaprov").html(response);
				if(response.indexOf("suspendida") > -1){
					$('#divsuspender').remove();
					$('#Aprobacion').remove();
					var table = $('#example').DataTable();
					table.ajax.reload();
				}
		}
	});
}

function filtrafecha(){
	var table = $('#example').DataTable();
	table.ajax.reload();
}

function solicitadoc(referencia,tipodoc){
	if(tipodoc==1){
		doc="solicitanom";
		m1='Solicitada';
	}
	if(tipodoc==2){
		doc="solicitacerori";
		m1='Solicitado';
	}
	$.ajax({
		url:   'funciones.php',
		type:  'post',
		data:	{action:'solicitadoc' ,referencia: referencia,tipodoc: tipodoc},
		beforeSend: function () {
			$("#"+doc+referencia).html('<center><img src="../images/cargando.gif" height="16" width="16"></center>');
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			if (respuesta.codigo=='1'){
				$("#"+doc+referencia).html('<center>'+m1+'</center>');
			}else{
				$("#"+doc+referencia).html('<center>'+respuesta.mensaje+'</center>');
			}
		},
		error: function(data){
			$("#"+doc+referencia).html('<center>Error contacte al administrador</center>');
		}
	});
}

/***************************************************************************************************************************/
/* REMESA O PEDIMENTO DOCUMENTOS */
/***************************************************************************************************************************/

var __sRemesa;
var __sPedimento;
var __sRemision;
var __sReferencia;
var __sIdDoc; //variable global

/*-----------------------*/
/* PEDIMENTO */
/*-----------------------*/

function consultapedimento(pedimento){
	var obj;
	obj1 =  $("#tabpedimento");
	obj2 =  $("#tabremesa");
	obj2.removeAttr('class','active');
	obj1.attr("class","active");
	$('html, body').animate({
					scrollTop: $("#Detalle").offset().top
				}, 2000);
	if (pedimento=='') {
		$("#Detalle").html("<br><center><p>Debe seleccionar un pedimento</p></center>");
		return;
	}
	$.ajax({
		url:   'consultapedimento.php',
		type:  'post',
		data:	{pedimento: pedimento},
		beforeSend: function () {
				$("#Detalle").html('<br><center><p><img src="../images/cargando.gif" height="36" width="36">Consultando, espere por favor...</p></center>');
		},
		success:  function (response) {
				$("#Detalle").html(response);
				$('html, body').animate({
					scrollTop: $("#Detalle").offset().top
				}, 2000);
				$.ajax({
					url:   'pedxml.php',
					type:  'post',
					data:	{pedimento: pedimento},
					beforeSend: function () {
							$("#pedxml").html('<center><img src="../images/cargando.gif" height="24" width="24"></center>');
							$("#estatusped").html('<center><p><img src="../images/cargando.gif" height="36" width="36">Consultando, espere por favor...</p></center>');
					},
					success:  function (response) {
							$("#pedxml").html(response);
							if(response.indexOf("descargapedxml.php") > -1){
								$.ajax({
									url:   'estatus.php',
									type:  'post',
									data:	{pedimento: pedimento},
									beforeSend: function () {
											$("#estatusped").html('<center><p><img src="../images/cargando.gif" height="36" width="36">Consultando, espere por favor...</p></center>');
									},
									success:  function (response) {
											$("#estatusped").html(response);
									}
								});
							}else{
								$("#estatusped").html('<center><p>Error al obtener el pedimento xml de ventanilla unica, intentelo de nuevo</p></center>');
							}
					}
				});
				$.ajax({
					url:   'descargacovexml.php',
					type:  'post',
					data:	{pedimento: pedimento},
					beforeSend: function () {
							$("#covexml").html('<center><img src="../images/cargando.gif" height="24" width="24"></center>');
					},
					success:  function (response) {
							$("#covexml").html(response);
					}
				});
				
		}
	});
}

/*-----------------------*/
/* REMESA */
/*-----------------------*/

function consultaremesa(remesa, pedimento, remision, referencia){
	fcn_remped_docs_ops('browser');

	__sRemesa = remesa;
	__sPedimento = pedimento;
	__sRemision = remision;
	__sReferencia = referencia;

	$('#modal_remped_documentos').modal({ show: true, backdrop: 'static', keyboard: false });

	$.ajax({
		url:   'consultaremesa.php',
		type:  'post',
		data:	{
			action: 'consultaremesa',
			remesa: __sRemesa, 
			pedimento: __sPedimento, 
			remision: __sRemision
		},
		beforeSend: function () {
			$('#idiv_mdl_remped_docs_detalle').hide();
			$("#idiv_mdl_remped_docs_detalle .cls_remped_detalle").empty();
			$("#itable_mdl_docs tbody").empty();
			$("#idiv_remped_docs_mensaje").html(fcn_msj_loading('Consultando, espere por favor...'));
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			
			$("#idiv_remped_docs_mensaje").empty();
			if (respuesta.codigo=='1'){
				$("#idiv_mdl_remped_docs_detalle .cls_remped_detalle").html(respuesta.sHtmlResp);
				$("#itable_remped_docs tbody").html(respuesta.sDocumentos);

				$("#idiv_mdl_remped_docs_obs").hide();
				if (respuesta.sDocumentos == '') {
					$('#idiv_mdl_remped_docs_detalle').show();
					
					if (respuesta.fec_aprov != '') { 
						$("#idiv_remped_docs_mensaje").html(fcn_msj_ok('Remesa aprobada ' + respuesta.fec_aprov));
					} else if (respuesta.fec_rechazo != '') {
						$("#idiv_mdl_remped_docs_obs").show();
						$("#itxt_mdl_remped_docs_obs").val(respuesta.obs)
						$("#idiv_remped_docs_mensaje").html(fcn_msj_warning('Remesa rechazada ' + respuesta.fec_rechazo));
					}
				}
			}else{
				$("#idiv_remped_docs_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
			}
		}
	});
}

function fcn_remped_docs_subir(){
	var oData = new FormData();	

	var oDocs = $('#ifile_remped_docs').fileinput('getFileStack');
	if (oDocs.length == 0) {
		$("#idiv_remped_docs_mensaje").html(fcn_msj_error('Debe agregar por lo menos un archivo valido PDF.'));
		return;
	}

	var nDocumentosTotales = $('#idiv_mdl_remped_docs_upload .file-preview').find('div.file-preview-frame').length / 2;
	if (oDocs.length != nDocumentosTotales) {
		$("#idiv_remped_docs_mensaje").html(fcn_msj_error('Existen archivos con error, favor de eliminar.'));
		return;
	}

	$.each(oDocs, function(i, file) {
		oData.append('file-'+i, file);
	});

	oData.append('action', 'subir_documentos');
	oData.append('remesa', __sRemesa);
	oData.append('pedimento', __sPedimento);
	oData.append('remision', __sRemision);
	oData.append('referencia', __sReferencia);

	$.ajax({
		url:   'consultaremesa.php',
		type:  'post',
		data: oData,
		contentType: false,
		cache: false,
		processData:false,
		timeout: 30000,		
		beforeSend: function () {
			$("#ibtn_reped_docs_subir").prop( "disabled", true);
			$("#idiv_remped_docs_mensaje").html(fcn_msj_loading('Subiendo documentos, espere por favor...'));
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			
			$("#ibtn_reped_docs_subir").prop( "disabled", false);
			$("#idiv_remped_docs_mensaje").empty();
			if (respuesta.codigo=='1'){
				$('#example').DataTable().ajax.reload(null, true);
				
				fcn_remped_docs_ops('browser');
				$("#idiv_mdl_remped_docs_detalle .cls_remped_detalle").html(respuesta.sHtmlResp);
				$("#itable_remped_docs tbody").html(respuesta.sDocumentos);

				if (respuesta.sDocumentos == '') {
					$('#idiv_mdl_remped_docs_detalle').show();
				}

				$("#idiv_remped_docs_mensaje").html(fcn_msj_ok(respuesta.mensaje));
			}else{
				$("#idiv_remped_docs_mensaje").html(fcn_msj_error(respuesta.mensaje));
			}
		}
	});
}

function fcn_remped_docs_ver(id_doc, tipo, status, obs) {
	fcn_remped_docs_ops('ver');
	__sIdDoc = id_doc;
	
	$("#idiv_mdl_remped_docs_obs").hide();
	if (status == 'Rechazado') {
		$("#idiv_mdl_remped_docs_obs").show();
		$("#itxt_mdl_remped_docs_obs").val(obs);
	}

	$('#idiv_mdl_remped_docs_ver_documento span').html(tipo);
	
	$.ajax({
		url:   'mainfunc.php',
		type:  'post',
		data:	{action: 'consulta_documento_pdf', id_doc: __sIdDoc },
		beforeSend: function () {
			$('#pdfViewer_mdl_remped_docs_pdf_archivo').hide();
			$("#idiv_remped_docs_mensaje").html(fcn_msj_loading('Consultando, espere por favor...'));
		},
		success:  function (response) {
			respuesta = JSON.parse(response);
			
			$("#idiv_remped_docs_mensaje").empty();
			if (respuesta.codigo=='1'){
				var pdfjsframe_remped = document.getElementById('pdfViewer_mdl_remped_docs_pdf_archivo');
				
				var pdfData = base64ToUint8Array(respuesta.pdfbase64);
				pdfjsframe_remped.contentWindow.PDFViewerApplication.open(pdfData);
				$('#pdfViewer_mdl_remped_docs_pdf_archivo').show();

				/*var sHtml = '<iframe src="../bower_components/pdfjs/web/viewer.html?file=https://www.delbravoweb.com/archivos/monitor/' + respuesta.link + '" width="100%" height="700" frameborder="0" wmode="Opaque"  allowtransparency="yes" scrolling="no" style="position:relative;" ></iframe>';
				$('#iembed_mdl_remped_docs_pdf_archivo').html(sHtml);*/
			}else{
				$("#idiv_remped_docs_mensaje").html(fcn_msj_error(respuesta.mensaje));
			}
		},
		error: function(data){
			$("#idiv_remped_docs_mensaje").html(fcn_msj_error('Error contacte al administrador'));
		}
	});
}

function fcn_remped_docs_ops(sOpt) { 
	$('#idiv_remped_docs_mensaje').empty();

	switch(sOpt) {
		case 'browser':
			$('#idiv_mdl_remped_docs_detalle').hide();
			$('#idiv_mdl_remped_docs_ver_documento').hide();
			$('#idiv_mdl_remped_docs_table').show();
			$('#idiv_mdl_remped_docs_ver').hide();
			$('#idiv_mdl_remped_docs_upload').hide();
			break;
			
		case 'ver':
			$('#idiv_mdl_remped_docs_detalle').show();
			$('#idiv_mdl_remped_docs_ver_documento').show();
			$('#idiv_mdl_remped_docs_table').hide();
			$('#idiv_mdl_remped_docs_ver').show();
			$('#idiv_mdl_remped_docs_upload').hide();
			break;
			
		case 'subir_archivos':
			$('#idiv_mdl_remped_docs_detalle').hide();
			$('#idiv_mdl_remped_docs_table').hide();
			$('#idiv_mdl_remped_docs_ver').hide();
			$('#idiv_mdl_remped_docs_upload').show();

			$("#ibtn_reped_docs_subir").prop( "disabled", false);

			fcn_remped_docs_file_input();
			break;
	}
}

/* ..:: Creamos el objeto FileInput y lo llenamos de datos ::.. */
function fcn_remped_docs_file_input() {
	try {
		__nNumeroArchivos = 0;

		if (!$("#ifile_remped_docs").data('fileinput')) {
			$("#ifile_remped_docs").fileinput({
				language: "es",
				uploadUrl: "N/A",
				uploadAsync: false,
				showRemove: false,
				showUpload: false,
				minFileCount: 1,
				fileActionSettings: {
					showUpload: false
				},
				allowedFileExtensions: ["pdf"]
			});
		} else {
			$("#ifile_remped_docs").fileinput('clear');
		}
	} catch (err) {
		$("#idiv_remped_docs_mensaje").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+respuesta.mensaje+'</div>');
    }
}

/***************************************************************************************************************************/
/* FUNCIONES COMPARTIDAS */
/***************************************************************************************************************************/

function base64ToUint8Array(base64) {
	var raw = atob(base64);
	var uint8Array = new Uint8Array(raw.length);
	for (var i = 0; i < raw.length; i++) {
		uint8Array[i] = raw.charCodeAt(i);
	}
	return uint8Array;
}

/*-----------------------*/
/* MENSAJES */
/*-----------------------*/

function fcn_msj_error(mensaje) {
	var sMensaje = '' +
	'<br>' + 
	'<div class="alert alert-danger alert-dismissible" role="alert">' +
	'	<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
	'   	<span aria-hidden="true">&times;</span>' +
	'	</button>' + mensaje +
	'</div>';
	return sMensaje;
}

function fcn_msj_ok(mensaje) {
	var sMensaje = '' +
	'<br>' + 
	'<div class="alert alert-success alert-dismissible" role="alert">' +
	'	<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
	'   	<span aria-hidden="true">&times;</span>' +
	'	</button>' + mensaje +
	'</div>';
	return sMensaje;
}

function fcn_msj_warning(mensaje) {
	var sMensaje = '' +
	'<br>' + 
	'<div class="alert alert-warning alert-dismissible" role="alert">' +
	'	<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
	'   	<span aria-hidden="true">&times;</span>' +
	'	</button>' + mensaje +
	'</div>';
	return sMensaje;
}

function fcn_msj_loading(mensaje) {
	return '<br><center><p><img src="../images/cargando.gif" height="36" width="36">' + mensaje + '</p></center>';
}