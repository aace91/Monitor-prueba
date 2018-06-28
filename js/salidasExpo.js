var table_salidasExpo;
$(document).ready(function() {
	
	table_salidasExpo =$('#dtsalidasExpo').DataTable({
		"processing": true,
		"serverSide": true,
		"dom": 'Bflrtip',
		"searchDelay": 2500,
		"order": [[ 0, 'desc' ]],
		aoColumnDefs: [
			{ bSearchable: false, aTargets: [ 1 ] }
		],
		aLengthMenu: [
				[10, 25, 50, 100, 200, -1],
				[10, 25, 50, 100, 200, "All"]
			],
		"ajax": {
			"url": "func/postsalidasExpo.php",
			"type": "POST",
			"data": function ( d ) {
				d.sel_status = $('#sel_status').val();
			}
		},
		"columns": [
			{ "data": "no_salida" },
			{ "data": "fecha_alta" },
			{ "data": "linea" },
			{ "data": "cajas" },
			{ "data": "referencias" },
			{ "data": "facturas" },
			{ "data": "ejecutivo" }
		],
		buttons:[ 
			{
				text: '<span class="glyphicon glyphicon-file" aria-hidden="true"></span> Nueva',
				action: function ( e, dt, node, config ) {
					window.open(encodeURI("salidaExpo.php"),'_blank');
				},
				className: 'btn btn-primary'
			},
			{ 
				extend: "edit",  
				text: '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Editar',
				action: function ( e, dt, node, config ) {
					var nSalidaNumero = dt.row({ selected: true }).data().no_salida;
					window.open(encodeURI("salidaExpo.php?id=" + nSalidaNumero),'_blank');
				},
				className: 'btn btn-primary'
			},
			{ 
				extend: 'edit',  
				text: '<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Imprimir Salida',
				action: function ( e, dt, node, config ) {
					var nSalidaNumero = dt.row({ selected: true }).data().no_salida;
					window.open(encodeURI("showCarta_Instrucciones.php?solicitud=" + nSalidaNumero),'_blank');
				},
				className: 'btn btn-primary'
			}
		],
		select: {
            style:    'os',
        },
		fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
			if (aData.estatus == 'pendiente') {
				$(nRow).css('background-color', '#c9302c');
				$(nRow).css('color', '#FFF');
			}
		},
		responsive: true,
		"language": {
			"sProcessing":     '<img src="../images/cargando.gif" height="36" width="36"> Cargando espere...',
		}
	});
	
	var sButton = fcn_create_button_datatable('dtsalidasExpo', '<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Recargar', 'onClick="javascript:fcn_sel_status_change(true);"');
	$("#dtsalidasExpo_length").append(sButton);
	
	$.fn.modal.Constructor.prototype.enforceFocus = function() {};
});

/* ..:: Creamos botones para el datatables ::.. */
function fcn_create_button_datatable(sAriaControls, sBtnTxt, oFunction = '') {
	var sHtml = '';
	
	sHtml += '<a class="btn btn-default buttons-selected-single pull-right"';
	sHtml += '    tabindex="0"';
	sHtml += '    aria-controls="' + sAriaControls + '"';
	sHtml += ' ' + oFunction;
	sHtml += '    >';
	sHtml += '    <span>'+ sBtnTxt +'</span>';
	sHtml += '</a>';
	
	return sHtml;
}


function fcn_sel_status_change() {
	table_salidasExpo.ajax.reload();
}

