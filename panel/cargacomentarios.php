<?php
include_once('./../checklogin.php');
include('./../connect_dbsql.php');

if($loggedIn == false){ header("Location: ./../login.php"); }
$remesa=$_POST['remesa'];
$pedimentopat=$_POST['pedimento'];
$remision=$_POST['remision'];
$numpedi=substr($pedimentopat,-7);
$patente=substr($pedimentopat,0,4);
if ($remesa==0){
	echo '<center><div class="alert alert-warning" role="alert">El numero de remesa es invalido</div></center>';
	exit;
}
if (rtrim($numpedi)==''||rtrim($patente)==''){
	echo '<center><div class="alert alert-warning" role="alert">El numero de pedimento es invalido</div></center>';
	exit;
}
$querym="SELECT fecha,comentario FROM remecom WHERE `remision` = '$remision' && `pedimento` = '$pedimentopat' && `partida` = '$remesa'";
$consultam= mysqli_query($cmysqli, $querym) or die("<br><center><p>Error al consultar los comentarios de la remesa ".$querym."<p></center>");
$nrows = mysqli_num_rows($consultam);
if($nrows > 0){
	$respuesta.= '<table class="table"><tr><th>Fecha</th><th>Comentario</th></tr>';
	while($row = mysqli_fetch_array($consultam)){
		$respuesta.='<tr>';
		$respuesta.='<td>'.$row['fecha'].'</td>';
		$respuesta.='<td>'.$row['comentario'].'</td>';
		$respuesta.='</tr>';
	}
	$respuesta.='</table>';
}else{
	$respuesta.= '<br><center><p>No existe ningun comentario<p></center>';
}
echo $respuesta;
?>