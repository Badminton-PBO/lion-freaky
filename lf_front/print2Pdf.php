<?php

$output = json_decode($_POST["data"]);
$findNewLine = array("\r\n", "\n", "\r");
$replaceNewLine = '<br />';

$html = '<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;border-color:#bbb;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:bold;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#bbb}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#bbb}
</style>';
$html .= '<h1> Ploeguitwisselingsformulier:  ' . $output->chosenMeeting->hTeam . '-' . $output->chosenMeeting->oTeam .' </h1>';
$html .= '<table>
			<tr>
				<td>Type/Afdeling/Reeks:' . $output->chosenTeam->event . ' ' . $output->chosenTeam->devision .$output->chosenTeam->series. '</td>
				<td>Tijdstip: ' . $output->chosenMeeting->dateLayout . ' ' . $output->chosenMeeting->hourLayout . '</td>
				<td>Plaats: '. $output->chosenMeeting->locationName.'</td>
			</tr>
		</table>';
$html .= '
	<br/><br/>
<table class="tg">
  <thead>
	  <tr>
		<th width="10%" style="font-size:9px">Discipline</th>
		<th width="20%" style="font-size:9px;">Naam</th>
		<th width="20%" style="font-size:9px">Voornaam</th>
		<th width="14%" style="font-size:9px">Lidnummer</th>
		<th width="14%" style="font-size:9px; ">Klassement</th>
		<th width="22%" style="font-size:9px; text-align: center">Uitslagen</th>
	  </tr>
  </thead>
  <tbody>';

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
			$html .='<td width="10%" rowspan="'.$game->involvedNumberOfPlayers.'">'.$game->id.'</td>';
		}
		$html .='<td width="20%">'.$player->lastName.'</td>
				<td width="20%">'.$player->firstName.'</td>
				<td width="14%">'.$player->vblId.'</td>							
				<td width="14%" style="text-align: center">'.$klassement.'</td>';
		if ($playerCount==1) {
			$html .='<td width="22%" rowspan="'.$game->involvedNumberOfPlayers.'">&nbsp;</td>';
		}
		$html .= '</tr>';
	}
}
$html .='<tr>
    <td colspan="2">invallers<br>commentaar</td>
    <td colspan="4">'.str_replace($findNewLine,$replaceNewLine,$output->chosenMeeting->comment).'</td>
</tr>
';
$html .='</tbody></table><br/><br/>';
$html .='Ploegkapitein '. $output->chosenTeam->teamName.': '. $output->chosenTeam->captainName.'<br/>';
$html .='Handtekening, <br>';
$html .='<table border="1px" width="200px"><tr><td height="50px">&nbsp;</td></tr></table>';



// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT,true, 'UTF-8', false);

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

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
