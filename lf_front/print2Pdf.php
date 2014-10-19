<?php

$output = json_decode($_POST["data"]);

$html = '<h1> Ploeguitwisselingsformulier </h1>';
$html .= '<h3> Wedstrijd: ' . $output->chosenMeeting->hTeam . '-' . $output->chosenMeeting->oTeam .'</h3>';
$html .= '<p> Type/Afdeling/Reeks: ' . $output->chosenTeam->event . ' ' . $output->chosenTeam->devision .$output->chosenTeam->series. '</p>';
$html .= '<p> Tijdstip: ' . $output->chosenMeeting->dateLayout . ' ' . $output->chosenMeeting->hourLayout . '</p>';
$html .= '<p> Plaats: '.'TODO'.'</p>';

$html .= '<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;border-color:#bbb;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#bbb;color:#594F4F;background-color:#E0FFEB;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#bbb;color:#493F3F;background-color:#9DE0AD;}
.tg .tg-s6z2{text-align:center}
</style>
<table class="tg">
  <tr>
    <th class="tg-031e">discipline</th>
    <th class="tg-031e">Voornaam</th>
    <th class="tg-031e">Achternaam</th>
    <th class="tg-s6z2">VBL Lidnummer</th>
    <th class="tg-031e">Klassement</th>
  </tr>';

foreach($output->games as $key => $game) {	
	$playerCount=0;	
	foreach($game->playersInGame as $key => $player) {		
		$playerCount++;		
		$klassement="";
		switch($game->gameType) {
			case "HD": $klassement = $player->rankingDouble;break;
			case "DD": $klassement = $player->rankingDouble;break;
			case "GD": $klassement = $player->rankingMix;break;
			case "HE": $klassement = $player->rankingSingle;break;
			case "DE": $klassement = $player->rankingSingle;break;				
		}
		
		$html .= '<tr>';
		if ($playerCount==1) {
			$html .='<td rowspan="'.$game->involvedNumberOfPlayers.'">'.$game->id.'</td>';
		}
		$html .='<td>'.$player->lastName.'</td>
				<td>'.$player->firstName.'</td>
				<td>'.$player->vblId.'</td>							
				<td>'.$klassement.'</td>
				</tr>';
	}
}  
$html .='</table>';
$html .= 'Handtekening Ploegkapitein,';


// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT,true, 'UTF-8', false);

// set document information
//$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($output->Home_Team);
$pdf->SetTitle('Wedstrijd: ' . $output->HomeTeam . ' - ' . $output->OutTeam);
$pdf->SetSubject('Ploeguitwisselingsformulier');
$pdf->SetKeywords('PBO,competitie,uitwisselingsformulier');

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
//$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', 10, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// set text shadow effect
//$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));


// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('uitwisselingsformulier' . date('Y-m-d') . '.pdf', 'D');

//============================================================+
// END OF FILE
//============================================================+

?>
