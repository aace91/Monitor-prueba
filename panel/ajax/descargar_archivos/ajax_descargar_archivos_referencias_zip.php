<?php

ini_set ( 'max_execution_time', 300); 
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

include_once('./../../../checklogin.php');
if ($loggedIn == false){
	echo '500';
}else{
	if (isset($_POST['referencias']) && !empty($_POST['referencias'])) {
		require('./../../../connect_dbsql.php');
		$sRefs = $_POST['referencias'];
		$RefAnt = '';$aFiles = array();

		$consulta = "SELECT
				bod.bodReferencia AS referencia,
				rev.id_revision AS revision,
				rev.factura as facrev,
				rev.manual,
				bod.weblinkp as doc_entrada
			FROM
					tblbod AS bod
				LEFT JOIN revision_general AS rev ON bod.bodReferencia = rev.referencia
			WHERE  bod.bodReferencia in ($sRefs)
			ORDER BY bod.bodReferencia";

		$query = mysqli_query($cmysqli, $consulta);

		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo'] = -1;
			$respuesta['Mensaje'] = 'Error al realizar la consulta.';
			$respuesta['Error'] = $error;
		}else{
			if(mysqli_num_rows($query) > 0){

				while($row = mysqli_fetch_array($query)){

					$Referencia = $row['referencia'];
					$Revision_Id = $row['revision'];
					$FacturaRev = $row['facrev'];
					$Manual = $row['manual'];
					$DocEntrada = $row['doc_entrada'];
					//Remplaza link para bajar documentos por LaredoNet
					$DocEntrada = str_replace ( "delbravoapps.com" , "delbravoapps.tk" , $DocEntrada  );

					if($RefAnt != $Referencia){
						$DocEnt = $Referencia.'_'.date("YmdHis").'.pdf';
						if (!copy($DocEntrada , $DocEnt)) {
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = 'Error al copiar la documentacion de entrada $ruta en el nuevo directorio.';
							$respuesta['Error'] = '';
							eliminar_documentos_array($aFiles);
							exit(json_encode($respuesta));
							break;
						}else{
							$DocEnt_Zip = $Referencia.'.pdf';
							$aFile = array($DocEnt,$DocEnt_Zip);
							array_push($aFiles,$aFile);
						}
					}
					if($Manual == 'S'){
						$ruta = "\\\\192.168.2.33\\dbdata\\revman\\".$Referencia.".pdf";
						$ReviNom = $Referencia.'_'.$FacturaRev.'_'.date("YmdHis").'.pdf';
						if (!copy($ruta , $ReviNom)) {
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = "No se pudo obtener la revision de la referencia $Referencia";
							$respuesta['Error'] = "Error al copiar la revision $ruta en el nuevo directorio, compruebe que exista";
							eliminar_documentos_array($aFiles);
							exit(json_encode($respuesta));
							break;
						}else{
							$ReviNom_Zip = $Referencia.'_'.$FacturaRev.'.pdf';
							$aFile = array($ReviNom,$ReviNom_Zip);
							array_push($aFiles,$aFile);
						}
					}else{
						$selection_formula = "{revision_general.referencia} = '".$Referencia."' and {revision_general.factura} ='".$FacturaRev."'";
						$COM_Object = 'CrystalRuntime.Application';
						try {
							$crapp  = new COM ($COM_Object) or die("Unable to Create Object");
						} catch (com_exception $e) {
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = "Error al generar el formato de la revision [Ref:$Referencia][Id:$Revision_Id][Fac:$FacturaRev].";
							$respuesta['Error'] = $e->getMessage();
							eliminar_documentos_array($aFiles);
							break;
						}
						$my_report = "C:\\WebSites\\monitor\\panel\\revision.rpt";
						$my_pdf = ''.$Referencia.'_'.str_replace('/','_',$FacturaRev).'_'.date("YmdHis").'.pdf';
						try
						{
							$creport = $crapp->OpenReport($my_report, 1);
							$creport->Database->LogOnServer("p2sodbc.dll", "bodegamysql", $mysqldb, $mysqluser, $mysqlpass);
							$creport->EnableParameterPrompting = 0;
							$creport->FormulaSyntax = 0;
							$creport->RecordSelectionFormula=$selection_formula;
							$creport->DiscardSavedData;
							$creport->ReadRecords();
							$creport->ExportOptions->DiskFileName=$my_pdf;
							$creport->ExportOptions->FormatType=31;
							$creport->ExportOptions->DestinationType=1;
							$creport->Export(false);
							$creport = null;
							$crapp = null;
						}
						catch(com_exception $error){
							$respuesta['Codigo'] = -1;
							$respuesta['Mensaje'] = "Error al generar el formato de la revision. [Ref:$Referencia][Id:$Revision_Id][Fac:$FacturaRev].";
							$respuesta['Error'] = $error->getMessage();
							eliminar_documentos_array($aFiles);
							$afiles=array();
							break;
						}
						$my_pdf_zip = ''.$Referencia.'_'.$FacturaRev.'.pdf';
						$aFile = array($my_pdf,$my_pdf_zip);
						array_push($aFiles,$aFile);

						//array_push($aFiles,$my_pdf);
					}
					$RefAnt = $Referencia;
				}

				if(count($aFiles) > 0){
					$zipname = 'DocumentosBodega_'.date("YmdHis").'.zip';
					$zip = new ZipArchive;
					$zip->open($zipname, ZipArchive::CREATE);
					foreach ($aFiles as $file) {
					  $zip->addFile($file[0],$file[1]);
					}
					$zip->close();
					/*header('Content-Type: application/zip');
					header('Content-disposition: attachment; filename='.$zipname);
					header('Content-Length: ' . filesize($zipname));
					readfile($zipname);
					array_push($aFiles,$zipname);*/
					$respuesta['Codigo'] = 1;
					$respuesta['Archivo'] = $zipname;
					eliminar_documentos_array($aFiles);
				}else{
					$respuesta['Codigo'] = -1;
					$respuesta['Mensaje'] = 'Sin documentos diponibles para generar el archivo comprimido.';
					$respuesta['Error'] = '';
				}
			}else{
				$respuesta['Codigo'] = -1;
				$respuesta['Mensaje'] = 'Las referencias que desea consultar no existen.';
				$respuesta['Error'] = '';
			}
		}
	}else{
		$respuesta['Codigo'] = -1;
		$respuesta['Mensaje'] = "458 : Error al recibir los datos de la relacion.";
	}
	echo json_encode($respuesta);
}

function eliminar_documentos_array($aFiles){
	for($i=0; $i<count($aFiles); $i++){
		unlink($aFiles[$i][0]);
	}
}
