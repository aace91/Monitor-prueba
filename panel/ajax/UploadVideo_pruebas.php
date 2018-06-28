<?php
	$directorio_videos = "\\\\192.168.1.107\\gabdata\\jcdelacruz\\videos\\";

	//foreach(array('video', 'audio') as $type) {
	    if (isset($_FILES["file"])) {

	        //echo 'uploads/';

	        $fileName = md5(uniqid()).'.mp4';
	        $uploadDirectory = $directorio_videos.$fileName;

	        if (!move_uploaded_file($_FILES["file"]["tmp_name"], $uploadDirectory)) {
	            echo($uploadDirectory." problem moving uploaded file");
	        }

	        echo($fileName);
	    }
	//}

	//echo "ok";