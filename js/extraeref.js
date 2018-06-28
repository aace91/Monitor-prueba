	var table;
	$(document).ready(function() {
		table = $('#dt_referencias').DataTable({
			"lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "./postextraeref.php",
				"type": "POST",
				"data": function ( d ) {
					var text_referencias = $('#text_referencias').val().trim();
					var sRefs = '';
					// split into rows
					ref_Rows = text_referencias.split("\n");
					for(i=0; i<ref_Rows.length; i++){
						//if(ref_Rows[i].trim() != ''){
						sRefs += (i == 0 ? '' : ',') + "'" + ref_Rows[i].trim() + "'";
						//}
					}
					d.referencias = sRefs;
				}
			},
			"columns": [
				{ "data": "referencia" },
				{ "data": "fechaentrada" },
				{ "data": "cliente" },
				{ "data": "revision" ,
					"mRender": function (data, type, row) {
						var sBtns = '';
						var aRevisiones = data.split(",");
						var aFacturas = row.facrev.split(",");
						for(i = 0; i < aRevisiones.length; i++){
							sBtns += '<a href="javascript:void(0);" onclick="javascript:window.open(\'showrevision.php?id='+aRevisiones[i]+'&referencia='+row.referencia+'&factura='+aFacturas[i]+'\',\'_blank\');">'+aRevisiones[i]+'</a>&nbsp;';
						}
						return sBtns;
					}
				},
				{ "data": "doc_entrada"  ,
					"mRender": function (data, type, row) {
						var sBtns = '';
						sBtns = '<a href="javascript:void(0);" onclick="javascript:window.open(\''+data+'\',\'_blank\');"><i class="fa fa-download" aria-hidden="true"></i> Entrada</a></center>';
						return sBtns;
					}
				}
			],
			/*select: {
				style:    'os',
			},*/
			responsive: true,
			"language": {
				"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36">Cargando...',
			}
		});
	});
	
	function procesar_referencias(){
		table.ajax.reload();
	}
	
	function limpiar_referencias(){
		$('#text_referencias').val('');
		procesar_referencias();
	}

	function generar_referencias(){
		var text_referencias = $('#text_referencias').val().trim();
		if(text_referencias == ''){
			$('#idiv_principal_message').html('<div class="alert alert-danger alert-dismissible" role="alert">Es necesario agregar Referencias.</div>');					
			$('#text_referencias').focus();
			return;
		}		
		var sRefs = '';
		// split into rows
		ref_Rows = text_referencias.split("\n");
		for(i=0; i<ref_Rows.length; i++){
			if(ref_Rows[i].trim() != ''){
				sRefs += (i == 0 ? '' : ',') + "'" + ref_Rows[i].trim() + "'";
			}
		}
		$.ajax({
            type: "POST",
            url: 'ajax/descargar_archivos/ajax_descargar_archivos_referencias_zip.php',
            data: {referencias: sRefs},
            beforeSend: function (dataMessage) {
				$('#idiv_principal_message').html('<div class="alert alert-info alert-dismissible" role="alert"><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Descargando, espere un momento por favor...</div>');
			},
            success:  function (response) {
				if (response != '500'){
					$('#idiv_principal_message').html('');
					var respuesta = JSON.parse(response);					
					if (respuesta.Codigo == '1'){
						var _location = document.location.toString();
						var applicationNameIndex = _location.indexOf('/', _location.indexOf('://') + 3);
						var applicationName = _location.substring(0, applicationNameIndex) + '/';
						var webFolderIndex = _location.indexOf('/', _location.indexOf(applicationName) + applicationName.length);
						var webFolderFullPath = _location.substring(0, webFolderIndex);
						var sURL = webFolderFullPath + '/panel/ajax/descargar_archivos/ajax_descargar_archivos_referencias_zip_descarga.php?link='+respuesta.Archivo;
						window.open(sURL);
					}else{
						$('#idiv_principal_message').html('<div class="alert alert-danger alert-dismissible" role="alert">'+respuesta.Mensaje+'['+respuesta.Error+']</div>');
					}
				}else{
					$('#idiv_principal_message').html('<div class="alert alert-danger alert-dismissible" role="alert">La sesión del usuario ha finalizado.</div>');					
					setTimeout(function () {window.location.replace('../logout.php');},3000);
				}				
			},
			error: function(a,b){
				$('#idiv_principal_message').html('<div class="alert alert-danger alert-dismissible" role="alert">' + a.status+' [' + a.statusText + ']</div>');
			}
        });
	}