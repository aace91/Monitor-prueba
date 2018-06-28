<?php
include_once('./../checklogin.php');
include('./../connect_dbsql.php');
include('../phpmailer/PHPMailerAutoload.php');

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
$id_cliente= empty($_POST['id_cliente']) ? '' : $_POST['id_cliente'];
$referencias= empty($_POST['referencias']) ? '' : $_POST['referencias'];
// a flag to see if everything is ok
$success = null;
 
// file paths to store
$paths= [];
 
// get file names
$filenames = $documento['name'];

$aref = json_decode($referencias,true);
$ref='';
if (count($aref)<1){
	echo json_encode(['error'=>'Error al grabar el documento ya que no se ha seleccionado ninguna referencia']);
	return;
}
 
// loop and process files
for($i=0; $i < count($filenames); $i++){
	mysqli_query($cmysqli, "BEGIN");
	$consulta = mysqli_query($cmysqli, "INSERT INTO docs (id_tpo,fecha,usuario_id) values('".$tipo."','".date("Y-m-d H:i:s")."','".$ejecutivo."')");
	if (!$consulta) {
		echo json_encode(['error'=>'Error al grabar el documento: '.mysqli_error($cmysqli)]);
		mysqli_query($cmysqli, "ROLLBACK");
		return;
	}
	$iddoc=mysqli_insert_id($cmysqli);
	$doccon=base64_encode(file_get_contents($documento['tmp_name'][$i]));
	$docnom=$documento['name'][$i];
	$consulta = mysqli_query($cmysqli, "INSERT INTO docs_contenido (id_doc,contenido,nombre) values('".$iddoc."','".$doccon."','".$docnom."')");
	if (!$consulta) {
		echo json_encode(['error'=>'Error al grabar el contenido del documento: '.mysqli_error($cmysqli)]);
		mysqli_query($cmysqli, "ROLLBACK");
		return;
	}
	//mysqli_query($cmysqli, "ROLLBACK");
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
foreach ($aref as $key) {
	$ref.=$key['referencia'].',';
	$consulta = mysqli_query($cmysqli, "INSERT INTO docs_refe (id_doc,referencia,id_tpo) values('".$iddoc."','".$key['referencia']."','".$tipo."')");
	if (!$consulta) {
		echo json_encode(['error'=>'Error al grabar las referencias del documento: '.mysqli_error($cmysqli)]);
		mysqli_query($cmysqli, "ROLLBACK");
		return;
	}
}
mysqli_query($cmysqli, "COMMIT");
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
$consulta = mysqli_query($cmysqli, "SELECT descripcion from docs_tipos where id_tpo=$tipo");
if (!$consulta) {
	echo json_encode(['error'=>'Error al consultar los tipos de documentos: '.mysqli_error($cmysqli)]);
	exit();
}
while($row = mysqli_fetch_array($consulta)){
	$tipodoc = $row['descripcion'];
}
//if($tipo=='2'){
	$r_mail=notifica_doc($ref,$iddoc,$id_cliente,$docnom,$tipodoc);
	if($r_mail["codigo"]!=1){
		$output = ['error'=>'El documento se almaceno pero fue imposible enviar la notificacion por correo: '.$r_mail['mensaje']];
		echo json_encode($output);
		exit;
	}
//}
// return a json encoded response for plugin to process successfully
echo json_encode($output);

function conn1(){
	include('../db.php');
	$cmysqli = mysqli_connect($mysqlserver,$mysqluser,$mysqlpass,$mysqldb);
	if ($cmysqli->connect_error) {
		$mensaje= "Error al conectarse a la base de datos de bodega: ".$cmysqli->connect_error;
		$response['codigo'] = -1;
		$response['mensaje'] = $mensaje;
		return $response;
	}
	$response['codigo'] = 1;
	$response['mensaje'] = 'Conexión exitosa';
	$response['conexion'] = $cmysqli;
	return $response;
}

function notifica_doc($referencias,$id_doc,$id_cliente,$docnom,$tipodoc){
	$to=array();
	$bcc=array();
	$conecta=conn1();
	if($conecta['codigo']!=1){
		$response['codigo'] = -1;
		$response['mensaje'] = $conecta['mensaje'];
		return $response;
	}
	$cmysqli=$conecta['conexion'];
	//array_push($bcc,'abisaicruz@delbravo.com');
	$consulta="SELECT
		g.nombre,
		g.cc1,
		g.cc2,
		g.cc3,
		g.cc4,
		g.cc5,
		g.cc6,
		g.cc7,
		g.cc8,
		g.cc9,
		g.cc10
	FROM
		geocel_clientes AS g 
	WHERE
		g.f_numcli = $id_cliente";
	$query = mysqli_query($cmysqli, $consulta);
	if (!$query) {
		$error=mysqli_error($cmysqli);
		$respuesta['codigo']=-1;
		$respuesta['mensaje']='Error en consulta de correos: ' .$error ;
		return $respuesta;
	}
	while($row = $query->fetch_object()){ 
		for ($i = 1; $i <= 10; $i++) {
			$row2= get_object_vars($row);
			$cc=$row2['cc'.$i];
			if($cc!='' or $cc!=NULL){
				array_push($to,$cc);
			}
		}
		$cliente=$row->nombre;
	}
	if(count($to)<=0){
		$respuesta['codigo']=1;
		$respuesta['mensaje']='No se envio el correo por que no hay ningun remitente registrado';
		return $respuesta;
	}
	mysqli_close($cmysqli);
	$asunto="Notificacion de $tipodoc, Cliente: $cliente, Referencias: $referencias";
	$mensaje='<img src="cid:logo.png" alt="Logo Del Bravo" width="103" height="100" /><br>';
	$mensaje.='<p>Se le notifica la recepcion de '.$tipodoc.':</p>';
	$mensaje.='<strong>Referencias: </strong>'.$referencias.'<br>';
	$mensaje.='<strong>Cliente: </strong>'.$cliente.'<br>';
	$mensaje.='<strong>'.$tipodoc.': </strong><a href="http://delbravoweb.com/siipruebas/admin/descargadoc.php?iddoc='.$id_doc.'">'.$docnom.'</a><br>';

	$mensaje.='<p>Este correo se ha generado de forma automatica, favor de no responder sobre el.</p>';
	$correo=enviamail($asunto,$mensaje,$to,$bcc,'mail.delbravo.com','25','avisosautomaticos@delbravo.com','aviaut01','../images/logo.png');
	if ($correo['codigo']==-1){
		$respuesta['codigo']=-1;
		$respuesta['mensaje']='Error al enviar el correo de notificacion de entrada: '.$correo['mensaje'].count($to);
	}else{
		$respuesta['codigo']=1;
		$respuesta['mensaje']=$correo['mensaje'];
	}
	return $respuesta;
}

function enviamail($asunto,$mensaje,$to,$bcc,$mailserver,$portmailserver,$sender,$pass,$ruta_logo){
	$mail = new PHPMailer();
	//Luego tenemos que iniciar la validación por SMTP:
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->Host = $mailserver; // SMTP a utilizar. Por ej. smtp.elserver.com
	$mail->Username = $sender; // Correo completo a utilizar
	$mail->Password = $pass; // Contraseña
	$mail->Port = $portmailserver; // Puerto a utilizar
	//Con estas pocas líneas iniciamos una conexión con el SMTP. Lo que ahora deberíamos hacer, es configurar el mensaje a enviar, el //From, etc.
	$mail->From = $sender; // Desde donde enviamos (Para mostrar)
	$mail->FromName = $sender;
	if($ruta_logo!=''){
		$mail->AddAttachment($ruta_logo, 'logo.png'); 
	}
	//Estas dos líneas, cumplirían la función de encabezado (En mail() usado de esta forma: “From: Nombre <correo@dominio.com>”) de //correo.
	if (count($to)>0){
		foreach($to as $t){
			// Esta es la dirección a donde enviamos
			$mail->AddAddress($t);
		}
	}
	if (count($bcc)>0){
		foreach($bcc as $b){
			// Esta es la dirección a donde enviamos
			$mail->AddBcc($b);
		}
	}
	$mail->IsHTML(true); // El correo se envía como HTML
	$mail->Subject = $asunto; // Este es el titulo del email.
	$mail->Body = $mensaje; // Mensaje a enviar
	$exito = $mail->Send(); // Envía el correo.

	//También podríamos agregar simples verificaciones para saber si se envió:
	if($exito){
		$respuesta['codigo']=1;
		$respuesta['mensaje']='El correo fue enviado correctamente.';
	}else{
		$respuesta['codigo']=-1;
		$respuesta['mensaje']=$mail->ErrorInfo;
	}
	return $respuesta;
}
?>