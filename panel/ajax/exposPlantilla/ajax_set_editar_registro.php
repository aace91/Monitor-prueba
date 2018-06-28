<?php
include_once('./../../../checklogin.php');
require('./../../../connect_dbsql.php');

if($loggedIn == false){
	echo '500';
} else {	
	if (isset($_POST['sIdDetalle']) && !empty($_POST['sIdDetalle'])) { 
		$respuesta['Codigo'] = 1;	
		
		//***********************************************************//
		
		$sIdDetalle = $_POST['sIdDetalle'];
		$sCveProv = $_POST['sCveProv'];
		$sFactura = $_POST['sFactura'];
		$sFecha = $_POST['sFecha'];
		$sMonto = $_POST['sMonto'];
		$sMoneda = $_POST['sMoneda'];
		$sIncoterm = $_POST['sIncoterm'];
		$sSubdivision = $_POST['sSubdivision'];
		$sCertificado = $_POST['sCertificado'];
		$sNoParte = $_POST['sNoParte'];
		$sOrigen = $_POST['sOrigen'];
		$sVendedor = $_POST['sVendedor'];
		$sFraccion = $_POST['sFraccion'];
		$sDescripcion = $_POST['sDescripcion'];
		$sPrecioPartida = $_POST['sPrecioPartida'];
		$sUMC = $_POST['sUMC'];
		$sCantUMC = $_POST['sCantUMC'];
		$sCantUMT = $_POST['sCantUMT'];
		$sPreferencia = $_POST['sPreferencia'];
		$sMarca = $_POST['sMarca'];
		$sModelo = $_POST['sModelo'];
		$sSubmodelo = $_POST['sSubmodelo'];
		$sSerie = $_POST['sSerie'];
		$sDescripcionCove = $_POST['sDescripcionCove'];

		//***********************************************************//

		$fecha_registro =  date("Y-m-d H:i:s");

		if ($sFecha != '') {
			$time = strtotime(str_replace('/', '-', $sFecha));
			$sFecha = date('Y-m-d',$time);
		}

		//***********************************************************//
		
		$consulta = "UPDATE expos_plantilla_detalle
					 SET id_proveedor='".$sCveProv."'
						,no_factura='".$sFactura."'
						,fecha_factura=".(($sFecha == '')? NULL : "'".$sFecha."'")."
						,monto_factura=".$sMonto."
						,moneda='".$sMoneda."'
						,incoterm='".$sIncoterm."'
						,subdivision='".$sSubdivision."'
						,certificado='".$sCertificado."'
						,no_parte='".$sNoParte."'
						,origen='".$sOrigen."'
						,vendedor='".$sVendedor."'
						,fraccion='".$sFraccion."'
						,descripcion='".$sDescripcion."'
						,precio_partida=".$sPrecioPartida."
						,umc='".$sUMC."'
						,cantidad_umc=".$sCantUMC."
						,cantidad_umt=".$sCantUMT."
						,preferencia='".$sPreferencia."'
						,marca='".$sMarca."'
						,modelo='".$sModelo."'
						,submodelo='".$sSubmodelo."'
						,serie='".$sSerie."'
						,descripcion_cove='".$sDescripcionCove."'
					 WHERE id_detalle=".$sIdDetalle;
		$query = mysqli_query($cmysqli, $consulta);
		if (!$query) {
			$error=mysqli_error($cmysqli);
			$respuesta['Codigo']=-1;
			$respuesta['Mensaje']='Error al actualizar detalle. Por favor contacte al administrador del sistema.'; 
			$respuesta['Error'] = ' ['.$error.']';
		} else { 
			$respuesta['Mensaje']='Registro actualizado correctamente.';
		}
	} else {
		$respuesta['Codigo']=-1;
		$respuesta['Mensaje']='No se recibieron datos';
	}

	echo json_encode($respuesta);
}

function get_column_value($oValue, $sType = '') {
	if(is_null($oValue)) { 
		return 'null';
	} else {
		switch ($sType) {
			case 'string':
				return "'".$oValue."'";
				break;
				
			case 'numeric':
				return $oValue;
				break;   
				
			default:
				return "'".$oValue."'";
		}
	}
}
