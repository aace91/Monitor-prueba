<?php
include_once('./../checklogin.php');
include('./../connect_dbsql.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Abisai Cruz">
    <!--link rel="icon" href="../../favicon.ico"-->

    <title>Subir archivos</title>

	<!-- Bootstrap core CSS -->
    <link href="./../bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="./../bootstrap/css/bootstrap-select.min.css" rel="stylesheet"/>
	<link href="./../bootstrap/css/dataTables.bootstrap.css" rel="stylesheet"/>
	<link href="./../bootstrap/css/dataTables.responsive.css" rel="stylesheet"/>
	<link href="./../bootstrap/css/dataTables.tableTools.min.css" rel="stylesheet"/>
	<!--link href="./datatablestools.css" rel="stylesheet"/-->
	<link href="./../bootstrap/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
    <!-- Custom styles for this template -->
    <link href="./../bootstrap/css/navbar.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <!--script src="./bootstrap/js/ie-emulation-modes-warning.js"></script-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	<script type="text/javascript" src="./../bootstrap/js/jquery.js"></script>
	<script src="./../bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/bootstrap-select.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/dataTables.bootstrap.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/dataTables.responsive.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/dataTables.tableTools.min.js"></script>
	<script type="text/javascript" language="javascript" src="./../bootstrap/js/fnFilterOnReturn.js"></script>
	<script src="./../bootstrap/js/fileinput.min.js" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" class="init" src="tableinv.js?1.1"></script>
	<script type="text/javascript" language="javascript">
		$('.selectpicker').selectpicker();
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
			$('.selectpicker').selectpicker('mobile');
		}
		/*function cambiacliente(){
			var myselect = document.getElementById("selcliente");
			cliente=myselect.options[myselect.selectedIndex].value;
			$.ajax({
				url:   'setcliente.php',
				type:  'post',
				data:	{cliente: cliente},
				success:  function (response) {
						$("#Detalle").html(response);
						var table = $('#inventario').DataTable();
						table.ajax.reload();
				}
			});
		}*/
		function cambiacliente(){
			var myselect = document.getElementById("selcliente");
			var table = $('#inventario').DataTable();
			if (myselect.options[myselect.selectedIndex].value!="")
			{
				table.columns( 7 ).search( myselect.options[myselect.selectedIndex].value ).draw();
			}else{
				table.search('').columns().search('-').draw();
			}
		}
	</script>
	<script type="text/javascript" language="javascript">
		$(document).on("ready", function() {
			$("#documento").fileinput({
				allowedFileExtensions: ["pdf"],
				elErrorContainer: "#error",
				browseLabel: "Examinar",
				removeLabel: "Remover",
				uploadLabel: "Guardar",
				msgInvalidFileExtension: "Extension invalida para el archivo {name}. Solo archivos {extensions} son soportados.",
				msgValidationError: "<span class='text-danger'><i class='glyphicon glyphicon-exclamation-sign'></i> Error al seleccionar el archivo</span>",
				msgLoading: "Guardando &hellip;",
				uploadUrl: "savedoc.php", // your upload server url
				uploadExtraData: function() {
					var oTable = $('#inventario').dataTable();
					var oTT = TableTools.fnGetInstance( 'inventario' );
					var aData = oTT.fnGetSelectedData();
					return {
						ejecutivo: $("#idejecutivo").val(),
						tipo: $("#seltpo").val(),
						id_cliente: $("#selcliente").val(),
						referencias: JSON.stringify(aData, null, 2)
					};
				}
			});
			$('#documento').on('filebatchuploadcomplete', function(event, files, extra) {
				var table = $('#inventario').DataTable();
				table.ajax.reload();
			});
		});
	</script>
</head>
<body>
	<div class="container">
 <?php include('nav.php');?>
      <!-- Main component for a primary marketing message or call to action -->
      <div class="jumbotron"> 
		<div class="panel panel-default">
		<div class="panel-heading"><strong>Subir documento</strong> <small></small></div>
		<div class="panel-body">
				<table width="100%">
				<tr>
				<td>
				<label>Tipo de documento</label>
				</td>
				<td width="3%">&nbsp;</td>
				<td>
				<select class="selectpicker" data-style="btn-primary" id="seltpo" data-width="auto">
					<?php
						$consulta="SELECT id_tpo,descripcion FROM `docs_tipos` order by descripcion";
						$query = mysqli_query($cmysqli,$consulta);
						$number = mysqli_num_rows($query);
						if($number >= 1){
							while($row = mysqli_fetch_array($query)){
								if($row['id_tpo']==1){
									echo '<option value="'.$row['id_tpo'].'" selected>'.$row['descripcion'].'</option>';
								}else{
									echo '<option value="'.$row['id_tpo'].'">'.$row['descripcion'].'</option>';
								}
							}
						}
					?>
				</select>
				</td>
				<td width="15%">&nbsp;</td>
				<td>
				<label>Cliente</label>
				</td>
				<td width="3%">&nbsp;</td>
				<td>
				<select class="selectpicker" data-style="btn-primary" id="selcliente" data-width="auto" onchange="cambiacliente()" data-live-search="true">
					<?php
						$consulta="SELECT Nom,Cliente_id FROM `clientes` order by Nom";
						$query = mysqli_query($cmysqli,$consulta);
						$number = mysqli_num_rows($query);
						if($number >= 1){
							while($row = mysqli_fetch_array($query)){
								echo '<option value="'.$row['Cliente_id'].'">'.$row['Nom'].'</option>';
							}
						}
					?>
				</select>
				</td>
				</tr>
				<tr>
				<td>&nbsp;</td>
				</tr>
				<tr>
				<td colspan="4">
					<div class="checkbox" id="divchkfac">
						<label><input type="checkbox" value="" id="showconfac"><strong>Mostrar referencias que llegaron con factura</strong></label>
					</div>
				</td>
				<td colspan="3" >
				<label>Seleccione las referencias que ampara el documento</label>
				</td>
				</tr>
				<tr>
				<td colspan="7" >
				<table id="inventario" class="table table-bordered dt-responsive display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>Referencia</th>
							<th>Fecha Entrada</th>
							<th>Hora Entrada</th>
							<th>Proveedor</th>
							<th>Descripcion</th>
							<th>Fotos</th>
							<th>Documentación</th>
							<th>Cliente</th>
						</tr>
					</thead>
				</table>
				</td>
				</tr>
				<tr>
				<td colspan="7">
				<label>Haga clic en examinar para seleccionar el archivo o arrastrelo al recuadro</label>
				<br>
				<input id="idejecutivo" type="hidden" value="<?php echo $id; ?>" >
				<input id="documento" name="documento[]" type="file">
				<div id="error"></div>
				</td>
				</tr>
				</table>
		</div>
      </div>

    </div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!--script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="./bootstrap/js/bootstrap.min.js"></script-->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--script src="../../assets/js/ie10-viewport-bug-workaround.js"></script-->
	<?php include('foot.php');?>
</body>
</html>