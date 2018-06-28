<?php
include_once('./../checklogin.php');
if($loggedIn == false){ header("Location: ./../login.php"); }
// upload.php
// 'images' refers to your file input name attribute
if (empty($_FILES['documento'])) {
    echo json_encode(['error'=>'Error 1: No se ha seleccionado ningun documento o supera el tamaño maximo permitido']);
    // or you can throw an exception
    return; // terminate
}
 
// get the files posted
$documento = $_FILES['documento'];
 
$ejecutivo= empty($_POST['ejecutivo']) ? '' : $_POST['ejecutivo'];
$tipo= empty($_POST['tipo']) ? '' : $_POST['tipo'];
$referencias= empty($_POST['referencias']) ? '' : $_POST['referencias'];
// a flag to see if everything is ok
$success = null;
 
// file paths to store
$paths= [];
 
// get file names
$filenames = $documento['name'];
 
// loop and process files
for($i=0; $i < count($filenames); $i++){
	mysql_query("BEGIN");
	$consulta = mysql_query("INSERT INTO docs (id_tpo,fecha,usuario_id) values('".$tipo."','".date("Y-m-d H:i:s")."','".$ejecutivo."')");
	if (!$consulta) {
		echo json_encode(['error'=>'Error al grabar el documento: '.mysql_error()]);
		mysql_query("ROLLBACK");
		return;
	}
	$iddoc=mysql_insert_id();
	$doccon=base64_encode(file_get_contents($documento['tmp_name'][$i]));
	$docnom=$documento['name'][$i];
	$consulta = mysql_query("INSERT INTO docs_contenido (id_doc,contenido,nombre) values('".$iddoc."','".$doccon."','".$docnom."')");
	if (!$consulta) {
		echo json_encode(['error'=>'Error al grabar el contenido del documento: '.mysql_error()]);
		mysql_query("ROLLBACK");
		return;
	}
	//mysql_query("ROLLBACK");
   /* $ext = explode('.', basename($filenames[$i]));
    $target = "uploads" . DIRECTORY_SEPARATOR . md5(uniqid()) . "." . array_pop($ext);
    if(move_uploaded_file($documento['tmp_name'][$i], $target)) {
        $success = true;
        $paths[] = $target;
    } else {
        $success = false;
        break;
    }*/
}
$aref = json_decode($referencias,true);
$ref='';
if (count($aref)<1){
	echo json_encode(['error'=>'Error al grabar el documento ya que no se ha seleccionado ninguna referencia']);
	mysql_query("ROLLBACK");
	return;
}
foreach ($aref as $key) {
	$ref.=$key['referencia'].',';
	$consulta = mysql_query("INSERT INTO docs_refe (id_doc,referencia,id_tpo) values('".$iddoc."','".$key['referencia']."','".$tipo."')");
	if (!$consulta) {
		echo json_encode(['error'=>'Error al grabar las referencias del documento: '.mysql_error()]);
		mysql_query("ROLLBACK");
		return;
	}
}
mysql_query("COMMIT");
$success = true;
 
// check and process based on successful status
if ($success === true) {
    // call the function to save all data to database
    // code for the following function `save_data` is not
    // mentioned in this example
    //save_data($userid, $username, $paths);
 
    // store a successful response (default at least an empty array). You
    // could return any additional response info you need to the plugin for
    // advanced implementations.
    $output = ['uploaded'=>'El documento se almaceno con exito','ejecutivo'=>$ejecutivo,'referencias'=>$ref,'tipo'=>$tipo];
    // for example you can get the list of files uploaded this way
    // $output = ['uploaded' => $paths];
} elseif ($success === false) {
    $output = ['error'=>'Error al almacenar el documento, contacte al administrador'];
    // delete any uploaded files
    foreach ($paths as $file) {
        unlink($file);
    }
} else {
    $output = ['error'=>'Error 2: No se ha seleccionado ningun documento'];
}
 
// return a json encoded response for plugin to process successfully
echo json_encode($output);
?>