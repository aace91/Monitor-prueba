<?php
include_once "./../checklogin.php";
?>
<div class="navbar navbar-default" role="navigation">
    <div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Menu</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Monitor de referencias</a>
        </div>
        <div class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Bodega <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'index.php') !== false) {echo 'class="active"';} ?> ><a href="index.php">Inventario</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'subearchivos.php') !== false) {echo 'class="active"';} ?> ><a href="subearchivos.php">Subir archivos</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'historial.php') !== false) {echo 'class="active"';} ?> ><a href="historial.php">Historico</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'equipov.php') !== false) {echo 'class="active"';} ?> ><a href="equipov.php">Equipo vacío</a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Utiler&iacute;as <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'servicios.php') !== false) {echo 'class="active"';} ?> ><a href="servicios.php">Imprimir Relaci&oacute;n de Servicios</a></li>
						<li class="dropdown-submenu">
							<a class="dropdown-submenu-gab" tabindex="-1" href="#">Exportaciones</a>
							<ul class="dropdown-menu">
								<li <?php if (stripos($_SERVER['REQUEST_URI'],'entradasExpo.php') !== false) {echo 'class="active"';} ?> ><a href="entradasExpo.php">Entradas</a></li>
								<li <?php if (stripos($_SERVER['REQUEST_URI'],'cruces_exportacion.php') !== false) {echo 'class="active"';} ?> ><a href="cruces_exportacion.php">Cruces</a></li>
								<li <?php if (stripos($_SERVER['REQUEST_URI'],'salidasExpo.php') !== false) {echo 'class="active"';} ?> ><a href="salidasExpo.php">Salidas</a></li>
								<li class="dropdown-submenu">
									<a class="dropdown-submenu-gab" tabindex="-1" href="#">Catalogos</a>
									<ul class="dropdown-menu">
										<li <?php if (stripos($_SERVER['REQUEST_URI'],'lineasExpo.php') !== false) {echo 'class="active"';} ?> ><a href="lineasExpo.php">Lineas</a></li>
										<li <?php if (stripos($_SERVER['REQUEST_URI'],'aaaExpo.php') !== false) {echo 'class="active"';} ?> ><a href="aaaExpo.php">AAA</a></li>
										<li <?php if (stripos($_SERVER['REQUEST_URI'],'transfersExpo.php') !== false) {echo 'class="active"';} ?> ><a href="transfersExpo.php">Transfers</a></li>
										<li <?php if (stripos($_SERVER['REQUEST_URI'],'entregasExpo.php') !== false) {echo 'class="active"';} ?> ><a href="entregasExpo.php">Entregas</a></li>
										<li <?php if (stripos($_SERVER['REQUEST_URI'],'clientesExpo.php') !== false) {echo 'class="active"';} ?> ><a href="clientesExpo.php">Clientes</a></li>
									</ul>
								</li>
							</ul>
						</li>
						<li class="dropdown-submenu">
							<a class="dropdown-submenu-gab" tabindex="-1" href="#">Accesos SII</a>
							<ul class="dropdown-menu">
								<li <?php if (stripos($_SERVER['REQUEST_URI'],'accesos_sii.php') !== false) {echo 'class="active"';} ?> ><a href="accesos_sii.php">Clientes</a></li>
								<li <?php if (stripos($_SERVER['REQUEST_URI'],'accesos_transfer.php') !== false) {echo 'class="active"';} ?> ><a href="accesos_transfer.php">Transfers</a></li>
							</ul>
						</li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'clasificaBodega.php') !== false) {echo 'class="active"';} ?> ><a href="clasificaBodega.php">Clasificaciones Bodega</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'solicitudSellos.php') !== false) {echo 'class="active"';} ?> ><a href="solicitudSellos.php">Solicitud de Sellos</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'librop.php') !== false) {echo 'class="active"';} ?> ><a href="librop.php">Libro de Pedimentos</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'sterisCatMatPrimas.php') !== false) {echo 'class="active"';} ?> ><a href="sterisCatMatPrimas.php">Cat&aacute;logo Materias Primas (Steris)</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'ctrlGlosa.php') !== false) {echo 'class="active"';} ?> ><a href="ctrlGlosa.php">Control de Glosa</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'exposPlantilla.php') !== false) {echo 'class="active"';} ?> ><a href="exposPlantilla.php">Exportaci&oacute;n Plantilla</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'extraeref.php') !== false) {echo 'class="active"';} ?> ><a href="extraeref.php">Extracci&oacute;n Continua de Referencias</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'avisos_adhesion.php') !== false) {echo 'class="active"';} ?> ><a href="avisos_adhesion.php">Avisos de Adhesi&oacute;n</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'permisos_pedimentos.php') !== false) {echo 'class="active"';} ?> ><a href="permisos_pedimentos.php">Permisos Exportaci&oacute;n</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'permisos_pedimentos_impo.php') !== false) {echo 'class="active"';} ?> ><a href="permisos_pedimentos_impo.php">Permisos Importaci&oacute;n</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'carta_protesta.php') !== false) {echo 'class="active"';} ?> ><a href="carta_protesta.php">Carta Protesta</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'certificados_origen.php') !== false) {echo 'class="active"';} ?> ><a href="certificados_origen.php">Certificados de Origen</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'soiaEstatus.php') !== false) {echo 'class="active"';} ?> ><a href="soiaEstatus.php">Consulta SOIA</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'KIA_documentos.php') !== false) {echo 'class="active"';} ?> ><a href="KIA_documentos.php">Documentaci&oacute;n KIA</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'tiempos_extra.php') !== false) {echo 'class="active"';} ?> ><a href="tiempos_extra.php">Solicitud Servicio Prioritario</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'fracciones_regla8va.php') !== false) {echo 'class="active"';} ?> ><a href="fracciones_regla8va.php">Aplicar Regla 8va a Partidas</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'frwd_incrementables.php') !== false) {echo 'class="active"';} ?> ><a href="frwd_incrementables.php">Incrementables Bodega</a></li>
						<li <?php if (stripos($_SERVER['REQUEST_URI'],'ordenes_b&g.php') !== false) {echo 'class="active"';} ?> ><a href="ordenes_b&g.php">Ordenes B&amp;G</a></li>
						<?php 
							if($usunivel == 'A') {
								echo '<li'.(stripos($_SERVER['REQUEST_URI'],'circulares.php') !== false ? ' class="active"':'').'><a href="circulares.php">Circulares</a></li>';
							}
						?>
					</ul>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reportes <span class="caret"></span></a>
					<ul class="dropdown-menu">
					<li <?php if (stripos($_SERVER['REQUEST_URI'],'rpt_tmxop.php') !== false) {echo 'class="active"';} ?> ><a href="rpt_tmxop.php">Telmex Operación</a></li>
					<li <?php if (stripos($_SERVER['REQUEST_URI'],'rpt_plantilla_avanzada') !== false) {echo 'class="active"';} ?> ><a href="rpt_plantilla_avanzada.php">Plantilla General Avanzada 5</a></li>
					<li <?php if (stripos($_SERVER['REQUEST_URI'],'facmexame') !== false) {echo 'class="active"';} ?> ><a href="facmexame.php">Facturacion Mexicana y Americana con facturas de pedimento</a></li>
					<li <?php if (stripos($_SERVER['REQUEST_URI'],'facame.php') !== false) {echo 'class="active"';} ?> ><a href="facame.php">Consulta de cuentas americanas por pedimento</a></li>
					<li <?php if (stripos($_SERVER['REQUEST_URI'],'rpt_incrementablesxpedime.php') !== false) {echo 'class="active"';} ?> ><a href="rpt_incrementablesxpedime.php">Reporte Incrementables Por Pedimento</a></li>
					</ul>
				</li>
				
				<?php 
					if($usunivel == 'A' || $usunivel == 'F') {
						echo ' 
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Contabilidad <span class="caret"></span></a>
								<ul class="dropdown-menu">
									<li role="presentation" class="dropdown-header">GAB</li>
									<li'.(stripos($_SERVER['REQUEST_URI'],'listctamex.php') !== false ? ' class="active"':'').'><a href="listctamex.php">Listado Cuentas</a></li>
									<li'.(stripos($_SERVER['REQUEST_URI'],'listremmex.php') !== false ? ' class="active"':'').'><a href="listremmex.php">Remisiones</a></li>
									<li'.(stripos($_SERVER['REQUEST_URI'],'listcorreos.php') !== false ? ' class="active"':'').'><a href="listcorreos.php">Correos envio cuentas GAB</a></li>
									<li'.(stripos($_SERVER['REQUEST_URI'],'estadodecuentamex.php') !== false ? ' class="active"':'').'><a href="estadodecuentamex.php">Estado de Cuenta</a></li>
									<li role="presentation" class="dropdown-header">DEL BRAVO FWD</li>
									<li'.(stripos($_SERVER['REQUEST_URI'],'listctaame.php') !== false ? ' class="active"':'').'><a href="listctaame.php">Listado Cuentas</a></li>
								</ul>
							</li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Expedientes <span class="caret"></span></a>
								<ul class="dropdown-menu">
								<li '.(stripos($_SERVER['REQUEST_URI'],'expRecepcion.php') !== false ? 'class="active"':'').'><a href="expRecepcion.php">Recepci&oacute;n</a></li>
								<li '.(stripos($_SERVER['REQUEST_URI'],'expCxC.php') !== false ? 'class="active"':'').'><a href="expCxC.php">Cuentas por Cobrar</a></li>
								<li '.(stripos($_SERVER['REQUEST_URI'],'expCxP.php') !== false ? 'class="active"':'').'><a href="expCxP.php">Cuentas por Pagar</a></li>
								<li '.(stripos($_SERVER['REQUEST_URI'],'expArchivo.php') !== false ? 'class="active"': '').'><a href="expArchivo.php">Archivo</a></li>
								<li '.(stripos($_SERVER['REQUEST_URI'],'expSeguimiento.php') !== false ? 'class="active"':'').'><a href="expSeguimiento.php">Seguimiento</a></li>
								<li '.(stripos($_SERVER['REQUEST_URI'],'expPapeleriaFirmada.php') !== false ? 'class="active"':'').'><a href="expPapeleriaFirmada.php">Papeleria Firmada</a></li>
								</ul>
							</li>
						';
					}
				?>
				
				<li><a href="./../logout.php">Salir</a></li>
            </ul>
            <p class="navbar-text navbar-right"><?php echo $username;?></p>
        </div><!--/.nav-collapse -->
    </div><!--/.container-fluid -->
</div>

<script>
	var classname = document.getElementsByClassName("dropdown-submenu-gab");

	for (var i = 0; i < classname.length; i++) {
		classname[i].addEventListener("click", function(e) {
			$(this).next('ul').toggle();
			e.stopPropagation();
			e.preventDefault();
			return false;
		});
	}
</script>