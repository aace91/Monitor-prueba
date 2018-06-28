<?php
class CCorreo { 
    public $bError = false; 
	public $sError = '';
	public $sMensaje = '';
	public $to = array();
	public $bcc = array();
	
    function GenerarEmails($sNumCli, $sIdLogistica, $cmysqli) { 
		$consulta = "SELECT *
					 FROM bodega.geocel_clientes_expo
					 WHERE f_numcli='".$sNumCli."'";
						   
		$query = mysqli_query($cmysqli,$consulta);
		if (!$query) {
			$this->bError = true;
			
			$error=mysqli_error($cmysqli);
			$this->sError = $error;
			$this->sMensaje = 'Error al consultar correos electronicos, Por favor contacte al administrador del sistema';
		} else {
			while($row = mysqli_fetch_array($query)){
				$this->to = get_email($row['to1'], $this->to);
				$this->to = get_email($row['to2'], $this->to);
				$this->to = get_email($row['to3'], $this->to);
				$this->to = get_email($row['to4'], $this->to);
				$this->to = get_email($row['to5'], $this->to);
				$this->to = get_email($row['to6'], $this->to);
				$this->to = get_email($row['to7'], $this->to);
				$this->to = get_email($row['to8'], $this->to);
				$this->to = get_email($row['to9'], $this->to);
				$this->to = get_email($row['to10'], $this->to);
				$this->bcc = get_email($row['cc1'], $this->bcc);
				$this->bcc = get_email($row['cc2'], $this->bcc);
				$this->bcc = get_email($row['cc3'], $this->bcc);
				$this->bcc = get_email($row['cc4'], $this->bcc);
				$this->bcc = get_email($row['cc5'], $this->bcc);
				$this->bcc = get_email($row['cc6'], $this->bcc);
				$this->bcc = get_email($row['cc7'], $this->bcc);
				$this->bcc = get_email($row['cc8'], $this->bcc);
				$this->bcc = get_email($row['cc9'], $this->bcc);
				$this->bcc = get_email($row['cc10'], $this->bcc);
			}
		}

		if ($this->bError == false) {
			$consulta = "SELECT correos_notificacion
						 FROM bodega.expos_salidas_logisticas
						 WHERE logistica=".$sIdLogistica;
							   
			$query = mysqli_query($cmysqli,$consulta);
			if (!$query) {
				$this->bError = true;
				
				$error=mysqli_error($cmysqli);
				$this->sError = $error;
				$this->sMensaje = 'Error al consultar correos electronicos de logistica, Por favor contacte al administrador del sistema';
			} else { 
				while($row = mysqli_fetch_array($query)){ 
					$sEmails = $row['correos_notificacion'];
					if (!is_null($sEmails) && !empty($sEmails)) {
						$aEmails = explode(",", $sEmails);
						foreach ($aEmails as $email) {
							$this->to = get_email($email, $this->to);
						}
					}
				}
			}
		}
    } 
}

function get_email($sEmail, $to) {
	if (is_null($sEmail) == false) {
		array_push($to,$sEmail);	
	} 
	return $to;	
} 