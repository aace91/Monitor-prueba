<!-- Custom Fonts -->
<link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	
<!-- MODAL CATALOGO DE PROBLEMAS -->
<div id="modal_contactos_expo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><span class="glyphicon glyphicon glyphicon-send" aria-hidden="true"></span> Lista de Contactos</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon">Correo</div>
								<input id="itxt_mdl_contactos_expo_correo" type="text" maxlength="100" class="form-control text-lowercase">
							</div>
						</div>
					</div>
					<div class="col-xs-12">
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon">Nombre</div>
								<input id="itxt_mdl_contactos_expo_nombre" type="text" maxlength="100" class="form-control">
								<span id="ibtn_mdl_contactos_expo_add" class="input-group-btn">
									<button class="btn btn-success" type="button" onClick="mdl_contactos_expo_func('add');" style="border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;"><i class="fa fa-plus" aria-hidden="true"></i></button>
								</span>
								<span id="ibtn_mdl_contactos_expo_save" class="input-group-btn">
									<button class="btn btn-success" type="button" onClick="mdl_contactos_expo_func('save');" style="border-radius: 0px !important;"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
								</span>

								<span id="ibtn_mdl_contactos_expo_cancel" class="input-group-btn">
									<button class="btn btn-danger" type="button" onClick="mdl_contactos_expo_func('cancel');"><i class="fa fa-ban" aria-hidden="true"></i></button>
								</span>
							</div>
						</div>
					</div>
					<div id="idiv_mdl_contactos_expo_tipo" class="col-xs-12" style="display:none;">
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon">Tipo</div>
								<select id="isel_mdl_contactos_expo_tipo" class="form-control" onchange="cargar_grid_contactos_expo();">
									<option value="CLI">Cliente</option>
									<option value="EJE">Ejecutivo</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div id="idiv_mdl_contactos_expo_msj" class="col-xs-12"></div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<div class="dataTable_wrapper">
							<div class="table-responsive" style="overflow:hidden;">
								<table id="dtcontactos_expo" class="table table-striped table-bordered" width="100%">
									<thead>
										<tr>
											<th class="text-center">Correo</th>
											<th class="text-center">nombre</th>
											<th class="text-center"></th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th class="text-center">Correo</th>
											<th class="text-center">nombre</th>
											<th class="text-center"></th>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--div class="modal-footer">
				<button id="ibtn_modal_cuenta_gastos_detalles_cancel" type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-ban"></i> Salir</button>	
			</div-->
		</div>
	</div>
</div>