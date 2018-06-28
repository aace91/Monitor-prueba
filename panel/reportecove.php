<?php
include_once('./../checklogin.php');
require('fpdf.php');
class PDF extends FPDF
{
// Page header
function Header()
{
	// Logo
	$this->Image('../images/logo.png',10,7,25);
	// Arial bold 15
	$this->SetFont('Arial','B',15);
	// Move to the right
	$this->Cell(80);
	// Title
	$this->Cell(30,10,"Registro de COVE's Validados",0,0,'C');
	// Line break
	$this->Ln(18);
}

// Page footer
function Footer()
{
	// Position at 1.5 cm from bottom
	$this->SetY(-15);
	// Arial italic 8
	$this->SetFont('Arial','I',8);
	// Page number
	$this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
}
}

$num_refe=$_GET['ref'];
$sql="SELECT a.NUM_REFE,a.NUM_PEDI,a.ADU_DESP,a.PAT_AGEN,a.IMP_EXPO,a.CVE_PEDI, b.NOM_IMP,b.DIR_IMP,b.NOE_IMP,b.NOI_IMP,b.POB_IMP,b.PAI_IMP,b.CP_IMP,b.RFC_IMP
		FROM SAAIO_PEDIME a LEFT JOIN CTRAC_CLIENT b ON a.CVE_IMPO=b.CVE_IMP
		WHERE a.NUM_REFE='".$num_refe."'";
$result=odbc_exec($odbccasa,$sql);
$error=odbc_errormsg(); 
if ($error!=''){
	echo $error;
	exit;
}
if (odbc_result($result,"num_refe")==""){
	echo "Sin resultados";
}
else
{
// Instanciation of inherited class
	$pdf = new PDF();
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(0,1,'Pat: '.odbc_result($result,"PAT_AGEN")."                   "."Adu: ".odbc_result($result,"ADU_DESP"),0,1,'C');
	$pdf->SetFont('Arial','',7);
	$pdf->Ln(7);
	$pdf->Text(155,37,"Referencia: ");
	$pdf->SetFont('Arial','B',7);
	$pdf->Text(175,37,odbc_result($result,"NUM_REFE"));
	$pdf->SetFont('Arial','',7);
	$pdf->Ln(3);
	$pdf->Cell(0,1,odbc_result($result,"NOM_IMP"),0,1,'');
	$pdf->Text(155,41,"Pedimento:     ");
	$pdf->SetFont('Arial','B',7);
	$pdf->Text(175,41,odbc_result($result,"NUM_PEDI"));
	$pdf->SetFont('Arial','',7);
	$pdf->Ln(3);
	$pdf->Cell(0,1,odbc_result($result,"DIR_IMP").', '.'No. Ext. '.odbc_result($result,"NOE_IMP").', '.'No. Int. '.odbc_result($result,"NOI_IMP").','.odbc_result($result,"POB_IMP").', '.odbc_result($result,"PAI_IMP"),0,1,'');
	$pdf->Text(155,44,"Operacion:");
	$pdf->SetFont('Arial','B',7);
	if (odbc_result($result,"IMP_EXPO")=='1')
		$pdf->Text(175,44,"IMPORTACION");
	else
		$pdf->Text(175,44,"EXPORTACION");
	$pdf->SetFont('Arial','',7);
	$pdf->Ln(3);
	$pdf->Cell(0,1,'C.P.  '.odbc_result($result,"CP_IMP").'          '.'R.F.C.   '.odbc_result($result,"RFC_IMP"),0,1,'');
	$pdf->Text(155,47,"Clave Ped:");
	$pdf->SetFont('Arial','B',7);
	$pdf->Text(175,47,odbc_result($result,"CVE_PEDI"));
	$pdf->SetFont('Arial','',7);
	$pdf->Line(10,51,200,51);
	$pdf->Ln(4);
	$pdf->SetFont('Arial','B',7);
	$pdf->Cell(0,1,"Proveedor/Destinatario",0,1,'');
	$pdf->SetFont('Arial','',7);
	$pdf->Ln(3);
	if (odbc_result($result,"IMP_EXPO")=='1')
		$sql2="SELECT c.NOM_PRO,c.DIR_PRO,c.NOE_PRO,c.NOI_PRO,c.ZIP_PRO,c.POB_PRO,c.PAI_PRO,c.TAX_PRO,a.NUM_FACT2,extract(day from a.FEC_FACT)||'/'||extract(month from a.FEC_FACT)||'/'||extract(year from a.FEC_FACT) as FEC_FACT1,d.FOL_COVE,d.E_DOCUMENT,extract(day from d.FEC_ENV)||'/'||extract(month from d.FEC_ENV)||'/'||extract(year from d.FEC_ENV) as FEC_ENV1,d.NUM_CER,d.FIR_DIGIT,a.OBS_COVE,a.CONS_FACT FROM SAAIO_FACTUR a LEFT JOIN SAAIO_PEDIME b ON a.NUM_REFE=b.NUM_REFE LEFT JOIN CTRAC_PROVED c ON a.CVE_PROV=c.CVE_PRO LEFT JOIN SAAIO_COVE d ON a.NUM_REFE=d.NUM_REFE and a.CONS_FACT=d.CONS_FACT WHERE a.NUM_REFE='".odbc_result($result,"NUM_REFE")."' ORDER BY a.CONS_FACT";
	else
		$sql2="SELECT c.NOM_PRO,c.DIR_PRO,c.NOE_PRO,c.NOI_PRO,c.ZIP_PRO,c.POB_PRO,c.PAI_PRO,c.TAX_PRO,a.NUM_FACT2,extract(day from a.FEC_FACT)||'/'||extract(month from a.FEC_FACT)||'/'||extract(year from a.FEC_FACT) as FEC_FACT1,d.FOL_COVE,d.E_DOCUMENT,extract(day from d.FEC_ENV)||'/'||extract(month from d.FEC_ENV)||'/'||extract(year from d.FEC_ENV) as FEC_ENV1,d.NUM_CER,d.FIR_DIGIT,a.OBS_COVE,a.CONS_FACT FROM SAAIO_FACTUR a LEFT JOIN SAAIO_PEDIME b ON a.NUM_REFE=b.NUM_REFE LEFT JOIN CTRAC_DESTIN c ON a.CVE_PROV=c.CVE_PRO LEFT JOIN SAAIO_COVE d ON a.NUM_REFE=d.NUM_REFE and a.CONS_FACT=d.CONS_FACT WHERE a.NUM_REFE='".odbc_result($result,"NUM_REFE")."' ORDER BY a.CONS_FACT";
	$result2=odbc_exec($odbccasa,$sql2);
	$error2=odbc_errormsg(); 
	if ($error2!=''){
		echo $error2;
		exit;
	}
	$pdf->Cell(0,1,odbc_result($result2,"NOM_PRO"),0,1,'');
	$pdf->Ln(3);
	$pdf->Cell(0,1,odbc_result($result2,"DIR_PRO").', No. Ext. '.odbc_result($result,"NOE_PRO").', No. Int. '.odbc_result($result,"NOI_PRO").', '.odbc_result($result2,"POB_PRO").', '.odbc_result($result2,"PAI_PRO"),0,1,'');
	$pdf->Ln(3);
	$pdf->Cell(0,1,'C.P.  '.odbc_result($result2,"ZIP_PRO"),0,1,'');
	$pdf->Ln(3);
	$pdf->Cell(0,1,'TAX ID  '.odbc_result($result2,"TAX_PRO"),0,1,'');
	$pdf->Ln(1);
	do{
		$pdf->Line(10,$pdf->gety(),200,$pdf->gety());
		$pdf->Ln(4);
		$pdf->Text(11,$pdf->gety(),'Factura/Fecha:');
		$pdf->SetFont('Arial','B',7);
		$pdf->Text(29,$pdf->gety(),odbc_result($result2,"NUM_FACT2"));
		$pdf->Text(85,$pdf->gety(),odbc_result($result2,"FEC_FACT1"));
		$pdf->SetFont('Arial','',7);
		$pdf->Text(100,$pdf->gety(),'No COVE:');
		$pdf->SetFont('Arial','B',7);
		$pdf->Text(130,$pdf->gety(),odbc_result($result2,"E_DOCUMENT"));
		$pdf->SetFont('Arial','',7);
		$pdf->Ln(4);
		$pdf->Text(11,$pdf->gety(),'Op. VUCEM:');
		$pdf->SetFont('Arial','B',7);
		$pdf->Text(35,$pdf->gety(),odbc_result($result2,"FOL_COVE"));
		$pdf->SetFont('Arial','',7);
		$pdf->Text(100,$pdf->gety(),'Fec. de Recepcion:');
		$pdf->SetFont('Arial','B',7);
		$pdf->Text(130,$pdf->gety(),odbc_result($result2,"FEC_ENV1"));
		$pdf->SetFont('Arial','',7);
		$pdf->Ln(4);
		$pdf->Text(100,$pdf->gety(),'No. Certificado:');
		$pdf->SetFont('Arial','B',7);
		$pdf->Text(130,$pdf->gety(),odbc_result($result2,"NUM_CER"));
		$pdf->SetFont('Arial','',7);
		$pdf->Ln(4);
		$pdf->Text(11,$pdf->gety(),'Sello: ');
		$pdf->SetFont('Arial','',5);
		$pdf->Text(20,$pdf->gety(),odbc_result($result2,"FIR_DIGIT"));
		$pdf->ln(2);
		$pdf->SetFont('Arial','',7);
		$pdf->SetFillColor(192,192,192);
		$pdf->Cell(10,3,'Cons',0,1,'',true);
		$pdf->ln(-3);
		$pdf->Cell(10);
		$pdf->Cell(75,3,'Descripcion',0,1,'C',true);
		$pdf->ln(-3);
		$pdf->Cell(85);
		$pdf->Cell(25,3,'Cant. UMC',0,1,'C',true);
		$pdf->ln(-3);
		$pdf->Cell(110);
		$pdf->Cell(10,3,'UMC',0,1,'C',true);
		$pdf->ln(-3);
		$pdf->Cell(120);
		$pdf->Cell(20,3,'Precio Unit.',0,1,'C',true);
		$pdf->ln(-3);
		$pdf->Cell(140);
		$pdf->Cell(25,3,'Valor ME USD',0,1,'C',true);
		$pdf->ln(-3);
		$pdf->Cell(165);
		$pdf->Cell(25,3,'Valor USD',0,1,'C',true);
		$pdf->Cell(10);
		$pdf->Cell(45,3,'Marca',0,1,'C',true);
		$pdf->ln(-3);
		$pdf->Cell(55);
		$pdf->Cell(60,3,'Modelo',0,1,'C',true);
		$pdf->ln(-3);
		$pdf->Cell(115);
		$pdf->Cell(35,3,'Submodelo',0,1,'C',true);
		$pdf->ln(-3);
		$pdf->Cell(150);
		$pdf->Cell(40,3,'No. Serie / No. de Parte',0,1,'C',true);
		$sql3="SELECT e.CONS_PART,e.DESC_COVE,e.CANT_COVE,e.UNI_COVE,e.VAL_UNIT,e.MON_FACT,f.MAR_MERC,f.NUM_PART,f.NUM_SERI FROM SAAIO_FACPAR e LEFT JOIN SAAIO_COVESER f ON e.NUM_REFE=f.NUM_REFE and e.CONS_FACT=f.CONS_FACT and e.CONS_PART=f.CONS_PART WHERE e.NUM_REFE='".odbc_result($result,"NUM_REFE")."' and e.CONS_FACT=".odbc_result($result2,"CONS_FACT");
		$result3=odbc_exec($odbccasa,$sql3);
		$error3=odbc_errormsg(); 
		if ($error3!=''){
			echo $error3;
			exit;
		}
		$totalfac=0;
		while(odbc_fetch_row($result3)){
			$pdf->Cell(10,4,odbc_result($result3,"CONS_PART"),0,1,'C',false);
			$pdf->ln(-4);
			$pdf->Cell(10);
			$pdf->Cell(75,4,odbc_result($result3,"DESC_COVE"),0,1,'',false);
			$pdf->ln(-4);
			$pdf->Cell(85);
			$pdf->Cell(25,4,odbc_result($result3,"CANT_COVE"),0,1,'C',false);
			$pdf->ln(-4);
			$pdf->Cell(110);
			$pdf->Cell(10,4,odbc_result($result3,"UNI_COVE"),0,1,'C',false);
			$pdf->ln(-4);
			$pdf->Cell(120);
			$pdf->Cell(20,4,odbc_result($result3,"VAL_UNIT"),0,1,'C',false);
			$pdf->ln(-4);
			$pdf->Cell(140);
			$pdf->Cell(25,4,odbc_result($result3,"MON_FACT"),0,1,'C',false);
			$pdf->ln(-4);
			$pdf->Cell(165);
			$pdf->Cell(25,4,odbc_result($result3,"MON_FACT"),0,1,'C',false);
			$totalfac=$totalfac+odbc_result($result3,"MON_FACT");
			if (odbc_result($result3,"MAR_MERC")!=NULL){
				$pdf->Cell(10);
				$pdf->Cell(45,4,odbc_result($result3,"MAR_MERC"),0,1,'C',false);
				$pdf->ln(-4);
				$pdf->Cell(55);
				$pdf->Cell(60,4,odbc_result($result3,"NUM_PART"),0,1,'C',false);
				$pdf->ln(-4);
				$pdf->Cell(115);
				$pdf->Cell(35,4,' ',0,1,'C',false);
				$pdf->ln(-4);
				$pdf->Cell(150);
				$pdf->Cell(40,4,odbc_result($result3,"NUM_SERI"),0,1,'C',false);
			}
		};
		$pdf->Cell(140);
		$pdf->Cell(25,4,$totalfac,0,1,'C',false);
		$pdf->ln(-4);
		$pdf->Cell(165);
		$pdf->Cell(25,4,$totalfac,0,1,'C',false);
		$pdf->SetFont('Arial','B',7);
		$pdf->Cell(190,3,'Observaciones del COVE conforme a la regla 1.9.15 de las RCGMCE',0,1,'C',true);
		$pdf->Cell(190,3,odbc_result($result2,"OBS_COVE"),0,1,'',false);
	}while(odbc_fetch_row($result2));
	$pdf->Output();
}
?>
