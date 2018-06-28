<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="expires" content="0">
	
    <title>Scaner Demo</title>

    <!-- Bootstrap Core CSS -->
    <link href="../bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- DataTables CSS -->
    <link href="../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../bower_components/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
	<link href="../bower_components/datatables.net-select-bs/css/select.bootstrap.min.css" rel="stylesheet">
	<link href="../editor/css/editor.bootstrap.min.css" rel="stylesheet">
	<link href="../editor/css/editor.selectize.css" rel="stylesheet">
	
	<!--link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet"/-->
	
	<!-- Custom styles for this template -->
    <link href="../bootstrap/css/navbar.css" rel="stylesheet">
	
	<!-- FileInput -->
	<link href="../bower_components/bootstrap-fileinput/css/fileinput.min.css" rel="stylesheet"/>
	
	<!--style>
		body.modal-open {
			position: fixed;
		}
	</style-->

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	<style>
		/***************************************/
		/* Para DataTables */
		/***************************************/
		.def_app_right{
		  text-align: right;
		}

		.def_app_left{
		  text-align: left;
		}

		.def_app_center{
		  text-align: center !important;
		}
		
		/******************************************************/
		/* Clases para modales */
		/******************************************************/
		.modal-success .modal-header,
		.modal-success .modal-footer {
			border-color: #00733e;
		}
		.modal-danger .modal-header,
		.modal-danger .modal-footer {
			border-color: #c23321;
		}
		
		.modal-primary .modal-body,
		.modal-primary .modal-header,
		.modal-primary .modal-footer,
		.modal-warning .modal-body,
		.modal-warning .modal-header,
		.modal-warning .modal-footer,
		.modal-info .modal-body,
		.modal-info .modal-header,
		.modal-info .modal-footer,
		.modal-success .modal-body,
		.modal-success .modal-header,
		.modal-success .modal-footer,
		.modal-danger .modal-body,
		.modal-danger .modal-header,
		.modal-danger .modal-footer {
			color: #fff !important;
		}
		
		.bg-green,
		.callout.callout-success,
		.alert-success,
		.label-success,
		.modal-success .modal-body {
			background-color: #00a65a !important;
		}
		
		.bg-red,
		.callout.callout-danger,
		.alert-danger,
		.alert-error,
		.label-danger,
		.modal-danger .modal-body {
			background-color: #dd4b39 !important;
		}
		
		.bg-green-active,
		.modal-success .modal-header,
		.modal-success .modal-footer {
			background-color: #008d4c !important;
		}
		
		.bg-red-active,
		.modal-danger .modal-header,
		.modal-danger .modal-footer {
			background-color: #d33724 !important;
		}
	</style>
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php');?>
		
		<!-- MODAL INFO -->
		<div id="modal_info" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alta" aria-hidden="true" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-body">	
						<div class="row">
							<div class="col-md-12">
								<div id="idiv_modal_info_mensaje"></div>
							</div>
							<div class="col-md-12">
								<div id="idiv_modal_info_mensaje_registros"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	
		<!-- MODAL LOAD CONFIGURACION -->
		<div id="modalloadconfig" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-body">
						<div id="modalloadconfig_mensaje"></div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MESSAGE BOX OK -->
		<div id="modalmessagebox_ok" class="modal fade modal-success" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><span id ="modalmessagebox_ok_titulo"> </span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-3 text-center">
								<i class="fa fa-check fa-3x"></i>
							</div>
							<div class="col-xs-9" style="color:#FFF;">
								<div id="modalmessagebox_ok_mensaje"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-info" data-dismiss="modal" style="width:150px">OK</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- MESSAGE BOX ERROR-->
		<div id="modalmessagebox_error" class="modal fade modal-danger" style="z-index:9999;">
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal">&times;</button>
		                <h4 class="modal-title"><span id ="modalmessagebox_error_span"> </span></h4>
		            </div>
		            <div class="modal-body">
		                <div class="row">
		                    <div class="col-xs-3 text-center">
		                        <i class="fa fa-ban fa-3x"></i>
		                    </div>
		                    <div class="col-xs-9" style="color:#FFF;">
		                        <div id="modalmessagebox_error_mensaje"></div>
		                    </div>
		                </div>
		            </div>
		            <div class="modal-footer">
		                <button type="button" class="btn btn-info" data-dismiss="modal" style="width:150px">OK</button>
		            </div>
		        </div>
		    </div>
		</div>
		
		<!-- MODAL CONFIRM -->
		<div id="modalconfirm" class="modal fade" style="z-index:9999;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<!--button type="button" class="close" data-dismiss="modal">&times;</button-->
						<h4 class="modal-title"><span id ="modalconfirm_title"> </span></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-2 text-center">
								<i class="fa fa-exclamation-triangle fa-3x"></i>
							</div>
							<div class="col-xs-10">
								<div id="modalconfirm_mensaje"></div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button id="modalconfirm_btn_cancel" type="button" class="btn btn-danger pull-left"><i class="fa fa-ban"></i> Cancelar</button>
						<button id="modalconfirm_btn_ok" type="button" class="btn btn-success"><i class="fa fa-check"></i> Aceptar</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Scaner Demo</strong> <small></small>
				</div>
				<div class="panel-body">
					<div class="row" style="margin-bottom: 5px;">
						<div class="col-xs-12">	
							<div id="idiv_panel_principal_mensaje"></div>
						</div>
					</div>
					<div class="row" style="margin-bottom: 5px;">
						<div class="col-xs-12">
							<button type="button" onclick="scanToPdfWithThumbnails();">Scan</button>
							<div id="images"></div>

							<form id="form1" action="https://asprise.com/scan/applet/upload.php?action=dump" method="POST" enctype="multipart/form-data" target="_blank" >
								<input type="text" id="sample-field" name="sample-field" value="Test scan"/>
								<input type="button" value="Submit" onclick="submitFormWithScannedImages();">
							</form>

							<div id="server_response"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
    </div> <!-- /container -->

	<script src="../bower_components/json3/lib/json3.min.js"></script>

	<!--[if lt IE 9]>
		<script src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js' type='text/javascript'/>
	<![endif]-->

	<!--[if (gte IE 9) | (!IE)]><!-->
		<script src="../bower_components/jquery/dist/jquery.min.js"></script>
	<!--<![endif]-->

    <!-- Bootstrap Core JavaScript -->
    <script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- DataTables JavaScript -->
    <script src="../bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
	<script src="../bower_components/datatables.net-responsive/js/dataTables.responsive.js"></script>
	<script src="../bower_components/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
	<script src="../bower_components/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../bower_components/datatables.net-buttons/js/buttons.colVis.min.js"></script>
    <script src="../bower_components/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="../bower_components/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script src="../bower_components/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
	<script src="../bower_components/datatables.net-select/js/dataTables.select.min.js"></script>
	<script src="../bower_components/datatables.net-checkboxes/js/dataTables.checkboxes.min.js"></script>
	<script src="../editor/js/dataTables.editor.min.js"></script>
	<script src="../editor/js/editor.bootstrap.min.js"></script>
	<script src="../editor/js/editor.selectize.js"></script>
	
    <script type="text/javascript" language="javascript" src="../bower_components/jszip/dist/jszip.min.js"></script>
	<!--[if (gte IE 9) | (!IE)]><!-->
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
		<script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
	<!--<![endif]-->
	
	<!-- Fileinput JS -->
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput/js/fileinput.min.js"></script>
	<script type="text/javascript" language="javascript" src="../bower_components/bootstrap-fileinput/js/fileinput_locale_es.js"></script>
	
	<script type="text/javascript" src="//asprise.azureedge.net/scannerjs/scanner.js"></script>
	
	<script>
        //
        // Please read scanner.js developer's guide at: http://asprise.com/document-scan-upload-image-browser/ie-chrome-firefox-scanner-docs.html
        //
        /** Scan: output PDF original and JPG thumbnails */
        function scanToPdfWithThumbnails() {
            scanner.scan(displayImagesOnPage,
                    {
                        "output_settings": [
                            {
                                "type": "return-base64",
                                "format": "pdf",
                                "pdf_text_line": "By ${USERNAME} on ${DATETIME}"
                            },
                            {
                                "type": "return-base64-thumbnail",
                                "format": "jpg",
                                "thumbnail_height": 200
                            }
                        ]
                    }
            );
        }
        /** Processes the scan result */
        function displayImagesOnPage(successful, mesg, response) {
            if(!successful) { // On error
                console.error('Failed: ' + mesg);
                return;
            }
            if(successful && mesg != null && mesg.toLowerCase().indexOf('user cancel') >= 0) { // User cancelled.
                console.info('User cancelled');
                return;
            }
            var scannedImages = scanner.getScannedImages(response, true, false); // returns an array of ScannedImage
            for(var i = 0; (scannedImages instanceof Array) && i < scannedImages.length; i++) {
                var scannedImage = scannedImages[i];
                processOriginal(scannedImage);
            }
            var thumbnails = scanner.getScannedImages(response, false, true); // returns an array of ScannedImage
            for(var i = 0; (thumbnails instanceof Array) && i < thumbnails.length; i++) {
                var thumbnail = thumbnails[i];
                processThumbnail(thumbnail);
            }
        }
        /** Images scanned so far. */
        var imagesScanned = [];
        /** Processes an original */
        function processOriginal(scannedImage) {
            imagesScanned.push(scannedImage);
        }
        /** Processes a thumbnail */
        function processThumbnail(scannedImage) {
            var elementImg = scanner.createDomElementFromModel( {
                'name': 'img',
                'attributes': {
                    'class': 'scanned',
                    'src': scannedImage.src
                }
            });
            document.getElementById('images').appendChild(elementImg);
        }
        /** Upload scanned images by submitting the form */
        function submitFormWithScannedImages() {
            if (scanner.submitFormWithImages('form1', imagesScanned, function (xhr) {
                if (xhr.readyState == 4) { // 4: request finished and response is ready
                    document.getElementById('server_response').innerHTML = "<h2>Response from the server: </h2>" + xhr.responseText;
                    document.getElementById('images').innerHTML = ''; // clear images
                    imagesScanned = [];
                }
            })) {
                document.getElementById('server_response').innerHTML = "Submitting, please stand by ...";
            } else {
                document.getElementById('server_response').innerHTML = "Form submission cancelled. Please scan first.";
            }
        }
    </script>
	
	<!--script src="../js/sterisCatMatPrimas.js?v=2017.02.10.1600"></script-->
</body>

</html>
