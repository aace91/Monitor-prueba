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
	
    <title>Seguimiento</title>

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
	
	<!--TouchSpin-->
    <link rel="stylesheet" href="../plugins/touchspin/jquery.bootstrap-touchspin.css">
		
    <!-- Custom styles for this template -->
    <link href="../bootstrap/css/navbar.css" rel="stylesheet">
	
	<!-- selectize CSS-->
	<link href="../bower_components/selectize/dist/css/selectize.css" rel="stylesheet">
	<link href="../bower_components/selectize/dist/css/selectize.bootstrap3.css" rel="stylesheet">
	
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
		/*video {
    height: 232px;
    margin: 0 12px 20px 0;
    vertical-align: top;
    width: calc(20em - 10px);
}
		video#gumVideo {
  margin: 0 20px 20px 0;
}*/
	</style>
</head>

<body id="body">
	<div class="container">
		<?php include('nav.php');?>
		<div class="jumbotron" style="padding: 10px; margin: 0px;"> 
			<div id="idiv_panel_principal" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<strong> Video</strong> <small></small>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12">
							<button id="record" disabled>Start Recording</button>
						    <button id="play" disabled>Play</button>
						    <button id="download" disabled>Download</button>
						    <button id="upload" disabled>Subir</button>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 col-sm-6">
							<div id="idiv_video" align="center" class="embed-responsive embed-responsive-16by9">								
								<video id="gum" class="embed-responsive-item" autoplay muted></video>
							</div>
						</div>
						<div class="col-xs-12 col-sm-6">
							<div align="center" class="embed-responsive embed-responsive-16by9">								
								<video id="recorded" class="embed-responsive-item" autoplay loop></video>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div align="center" class="embed-responsive embed-responsive-16by9">		
								<video class="embed-responsive-item" controls="controls" preload="none">
									<source src="uploads/prueba.webm" type="video/webm">
									Su navegador no sporta reproducci&oacute;n de video de HTML5
								</video>
							</div>
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
	
	<!-- selectize JavaScript -->
	<script src="../bower_components/selectize/dist/js/standalone/selectize.js"></script>
	
	<!--TouchSpin-->
	<script  type="text/javascript" language="javascript" src="../plugins/touchspin/jquery.bootstrap-touchspin.js"></script>
		
	<!--MaskedInput-->
	<script  type="text/javascript" language="javascript" src="../plugins/maskedinput/jquery.maskedinput.min.js"></script>
	
	<!-- boostrapselect JavaScript -->
	<!--<script src="../bower_components/bootstrap-select/js/bootstrap-select.js"></script>-->
		
	<script language="javascript" type="text/javascript">
		/*
		*  Copyright (c) 2015 The WebRTC project authors. All Rights Reserved.
		*
		*  Use of this source code is governed by a BSD-style license
		*  that can be found in the LICENSE file in the root of the source
		*  tree.
		*/

		// This code is adapted from
		// https://rawgit.com/Miguelao/demos/master/mediarecorder.html

		'use strict';

		/* globals MediaRecorder */

		var mediaSource = new MediaSource();
		mediaSource.addEventListener('sourceopen', handleSourceOpen, false);
		var mediaRecorder;
		var recordedBlobs;
		var sourceBuffer;

		var gumVideo = document.querySelector('video#gum');
		var recordedVideo = document.querySelector('video#recorded');

		var recordButton = document.querySelector('button#record');
		var playButton = document.querySelector('button#play');
		var downloadButton = document.querySelector('button#download');
		var uploadButton = document.querySelector('button#upload');
		recordButton.onclick = toggleRecording;
		playButton.onclick = play;
		downloadButton.onclick = download;
		uploadButton.onclick = upload;

		// window.isSecureContext could be used for Chrome
		var isSecureOrigin = location.protocol === 'https:' ||
		location.hostname === 'localhost';
		if (!isSecureOrigin) {
		  alert('getUserMedia() must be run from a secure origin: HTTPS or localhost.' +
		    '\n\nChanging protocol to HTTPS');
		  location.protocol = 'HTTPS';
		}

		var constraints = {
		  audio: true,
		  video: true
		};

		function handleSuccess(stream) {
		  recordButton.disabled = false;
		  console.log('getUserMedia() got stream: ', stream);
		  window.stream = stream;
		  if (window.URL) {
		    gumVideo.src = window.URL.createObjectURL(stream);
		  } else {
		    gumVideo.src = stream;
		  }
		}

		function handleError(error) {
		  console.log('navigator.getUserMedia error: ', error);
		}

		navigator.mediaDevices.getUserMedia(constraints).then(handleSuccess).catch(handleError);

		function handleSourceOpen(event) {
		  console.log('MediaSource opened');
		  sourceBuffer = mediaSource.addSourceBuffer('video/webm; codecs="vp8"');
		  console.log('Source buffer: ', sourceBuffer);
		}

		recordedVideo.addEventListener('error', function(ev) {
		  console.error('MediaRecording.recordedMedia.error()');
		  alert('Your browser can not play\n\n' + recordedVideo.src
		    + '\n\n media clip. event: ' + JSON.stringify(ev));
		}, true);

		function handleDataAvailable(event) {
		  if (event.data && event.data.size > 0) {
		    recordedBlobs.push(event.data);
		  }
		}

		function handleStop(event) {
		  console.log('Recorder stopped: ', event);
		}

		function toggleRecording() {
		  if (recordButton.textContent === 'Start Recording') {
		    startRecording();
		  } else {
		    stopRecording();
		    recordButton.textContent = 'Start Recording';
		    playButton.disabled = false;
		    downloadButton.disabled = false;
		    uploadButton.disabled = false;
		  }
		}

		function startRecording() {
		  //navigator.mediaDevices.getUserMedia(constraints).then(handleSuccess).catch(handleError);
		  
		  recordedBlobs = [];
		  var options = {mimeType: 'video/webm;codecs=vp9'};
		  if (!MediaRecorder.isTypeSupported(options.mimeType)) {
		    console.log(options.mimeType + ' is not Supported');
		    options = {mimeType: 'video/webm;codecs=vp8'};
		    if (!MediaRecorder.isTypeSupported(options.mimeType)) {
		      console.log(options.mimeType + ' is not Supported');
		      options = {mimeType: 'video/webm'};
		      if (!MediaRecorder.isTypeSupported(options.mimeType)) {
		        console.log(options.mimeType + ' is not Supported');
		        options = {mimeType: ''};
		      }
		    }
		  }
		  try {
		    mediaRecorder = new MediaRecorder(window.stream, options);
		  } catch (e) {
		    console.error('Exception while creating MediaRecorder: ' + e);
		    alert('Exception while creating MediaRecorder: '
		      + e + '. mimeType: ' + options.mimeType);
		    return;
		  }
		  console.log('Created MediaRecorder', mediaRecorder, 'with options', options);
		  recordButton.textContent = 'Stop Recording';
		  playButton.disabled = true;
		  downloadButton.disabled = true;
		  uploadButton.disabled = true;
		  mediaRecorder.onstop = handleStop;
		  mediaRecorder.ondataavailable = handleDataAvailable;
		  mediaRecorder.start(10); // collect 10ms of data
		  console.log('MediaRecorder started', mediaRecorder);
		}

		function stopRecording() {
		  mediaRecorder.stop();
		  console.log('Recorded Blobs: ', recordedBlobs);
		  recordedVideo.controls = true;
		  
		  window.stream.getVideoTracks().forEach(function (track) {
			track.stop();
		});

		  gumVideo.pause();
		  gumVideo.src = "";
		  gumVideo.mozSrcObject=null;
		  gumVideo.removeAttribute('src');
		  gumVideo.load();
		  
		  $('#idiv_video').hide();
		  /*$('#idiv_video').children().filter("video").each(function(){
				this.pause(); // can't hurt
				delete this; // @sparkey reports that this did the trick (even though it makes no sense!)
				$(this).remove(); // this is probably what actually does the trick
			});
			
			$('#idiv_video').empty();
			window.stream = null;*/
			
		  //gumVideo.pause();
		  //gumVideo.src = "";
		  //gumVideo.load();
		  //window.stream.stop();
		  //gumVideo.src = '';
		  //window.stream.getTracks()[0].stop();
		  //window.stream.getTracks()[1].stop();
		}

		function play() {
		  var superBuffer = new Blob(recordedBlobs, {type: 'video/webm'});
		  recordedVideo.src = window.URL.createObjectURL(superBuffer);
		}

		function download() {
		  var blob = new Blob(recordedBlobs, {type: 'video/webm'});
		  var url = window.URL.createObjectURL(blob);
		  var a = document.createElement('a');
		  a.style.display = 'none';
		  a.href = url;
		  a.download = 'test.webm';
		  document.body.appendChild(a);
		  a.click();
		  setTimeout(function() {
		    document.body.removeChild(a);
		    window.URL.revokeObjectURL(url);
		  }, 100);
		}

		function upload() {
		  var blob = new Blob(recordedBlobs, {type: 'video/webm'});
		  /*var url = window.URL.createObjectURL(blob);
		  var a = document.createElement('a');
		  a.style.display = 'none';
		  a.href = url;
		  a.download = 'test.webm';
		  document.body.appendChild(a);
		  a.click();
		  setTimeout(function() {
		    document.body.removeChild(a);
		    window.URL.revokeObjectURL(url);
		  }, 100);*/

		  /*var data = {};
		    data.video = blob;
		    data.metadata = 'test metadata';
		    data.action = "upload_video";
		    jQuery.post("ajax/UploadVideo_pruebas.php", data, onUploadSuccess);*/
		    var formData = new FormData();
		    formData.append('file', blob);
		    $.ajax({
		        url: "ajax/UploadVideo_pruebas.php",
		        type: "POST",
		        data: formData,
		        processData: false,
		        contentType: false,
		        success: function(response) {
		            alert('Successfully uploaded. ' + response);
		        },
		        error: function(jqXHR, textStatus, errorMessage) {
		            alert('Error:' + JSON.stringify(errorMessage));
		        }
		    });
		}

		function onUploadSuccess() {
		    alert ('video uploaded');
		}
	</script>
</body>

</html>
