<?php

$output = json_decode($_POST["data"]);
$games = $output->games;
$meeting = $output->chosenMeeting;
$team = $output->chosenTeam;

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
class MYPDF extends TCPDF
{

	public function playerLine($naam='', $voornaam='',$klas='',$lidnr='',$somindex='',$hoogsteKlas='',$isPloegIndex=false,$isBasisSpeler=false)
	{
		$this->MultiCell(44, 5, $naam, 1, 'C', 1, 0, '75', '', true,0,false,true,5,'M');
		$this->MultiCell(39, 5, $voornaam, 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(24, 5, $klas, 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(21, 5, $lidnr, 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(17, 5, $somindex, 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(19, 5, $hoogsteKlas, 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(19, 5, ($isPloegIndex ? 'X':''), 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(17, 5, ($isBasisSpeler ? 'X':''), 1, 'C', 1, 1, '', '', true,0,false,true,5,'M');
	}

	public function reserverPlayerLine() {
		$this->MultiCell(44, 5, '', 1, 'C', 1, 0, '75', '', true,0,false,true,5,'M');
		$this->MultiCell(39, 5, '', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(8, 5, '', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(8, 5, '', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(8, 5, '', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(21, 5, '', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(17, 5, '', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(19, 5, '', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(19, 5, '', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
		$this->MultiCell(17, 5, '', 1, 'C', 1, 1, '', '', true,0,false,true,5,'M');

	}
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT,true, 'UTF-8', false);

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set document information
//$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($output->Home_Team);
$pdf->SetTitle('Ontmoeting: ' . $meeting->hTeam . ' - ' . $meeting->oTeam);
$pdf->SetSubject('Ploegopstellingsformulier');
$pdf->SetKeywords('Badminton vlaanderen PBO competitie ploegopstellingsformulier');

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', 'B', 12, '', true);
$pdf->MultiCell(213, 0, 'Ploegopstellingsformulier - Interclubcompetitie', 1, 'C', false, 2, 42, '', true, 0);


$pdf->SetFont('dejavusans', '', 10, '', true);
$pdf->SetXY(0,$pdf->GetY()+2);

$startYLiga = $pdf->GetY();

// Serie
$pdf->writeHTMLCell(29, 5, 25, '', '<div style="font-weight:bold">Liga</div>', 1, 0, '0', true, 'L');
$pdf->MultiCell(12, 5, '', 1, '', 0, 1, '', '', true);
$pdf->writeHTMLCell(29, 5, 25, '', '<div style="font-weight:bold">Provinciaal</div>', 1, 0, '0', true, 'L');
$pdf->MultiCell(12, 5, 'X', 1, 'C', 0, 1, '', '', true);

$pdf->SetXY(0,$startYLiga);
$pdf->writeHTMLCell(29, 5, 80, '', '<div style="font-weight:bold">Afdeling</div>', 1, 0, '0', true, 'L');
$pdf->writeHTMLCell(10, 5, '', '', '<div style="font-weight:bold">1</div>', 1, 0, '0', true, 'C');
$pdf->writeHTMLCell(10, 5, '', '', '<div style="font-weight:bold">2</div>', 1, 0, '0', true, 'C');
$pdf->writeHTMLCell(10, 5, '', '', '<div style="font-weight:bold">3</div>', 1, 0, '0', true, 'C');
$pdf->writeHTMLCell(10, 5, '', '', '<div style="font-weight:bold">4</div>', 1, 0, '0', true, 'C');
$pdf->writeHTMLCell(10, 5, '', '', '<div style="font-weight:bold">5</div>', 1, 1, '0', true, 'C');

$pdf->MultiCell(29, 5, 'Gemengd', 1, 'L', 0, 0, '80', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='G' and $team->devision==1) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='G' and $team->devision==2) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='G' and $team->devision==3) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='G' and $team->devision==4) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='G' and $team->devision==5) ? 'X': ''), 1, 'C', 0, 1, '', '', true);

$pdf->MultiCell(29, 5, 'Heren', 1, 'L', 0, 0, '80', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='H' and $team->devision==1) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='H' and $team->devision==2) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='H' and $team->devision==3) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='H' and $team->devision==4) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='H' and $team->devision==5) ? 'X': ''), 1, 'C', 0, 1, '', '', true);

$pdf->MultiCell(29, 5, 'Dames', 1, 'L', 0, 0, '80', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='D' and $team->devision==1) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='D' and $team->devision==2) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='D' and $team->devision==3) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='D' and $team->devision==4) ? 'X': ''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, (($team->teamType=='D' and $team->devision==5) ? 'X': ''), 1, 'C', 0, 1, '', '', true);

$pdf->SetXY(0,$startYLiga);
$pdf->writeHTMLCell(19, 5, 173, '', '<div style="font-weight:bold">Reeks</div>', 1, 0, '0', true, 'L');
$pdf->MultiCell(24, 5, $team->series, 1, 'C', 0, 1, '', '', true);
$pdf->SetY($pdf->GetY()+5);
$pdf->writeHTMLCell(19, 5, 173, '', '<div style="font-weight:bold">Datum</div>', 1, 0, '0', true, 'L');
$pdf->MultiCell(24, 5, $meeting->dateLayout, 1, 'C', 0, 1, '', '', true,'');
$pdf->writeHTMLCell(19, 5, 173, '', '<div style="font-weight:bold">Startuur</div>', 1, 0, '0', true, 'L');
$pdf->MultiCell(24, 5, $meeting->hourLayout, 1, 'C', 0, 1, '', '', true);

$pdf->SetXY(0,$startYLiga);
$pdf->writeHTMLCell(43, 5, 229, '', '<div style="font-weight:bold">Ploegindex</div>', 1, 1, '0', true, 'L');
//$pdf->MultiCell(43, 5, 'Ploegindex', 1, 'L', 0, 1, '229', '', true);
$pdf->MultiCell(29, 5, 'Basisspelers', 1, 'L', 0, 0, '229', '', true);
$pdf->MultiCell(14, 5, $team->baseTeamIndex, 1, 'C', 0, 1, '', '', true);
$pdf->MultiCell(29, 5, 'Titularissen', 1, 'L', 0, 0, '229', '', true);
$pdf->MultiCell(14, 5, $team->effectiveTeamIndex, 1, 'C', 0, 1, '', '', true);

$startYThuisploeg = $startYLiga+22;
$pdf->SetXY(0,$startYThuisploeg);
$pdf->MultiCell(25, 5, 'Thuisploeg', 1, 'C', 0, 0, '40', '', true);
$pdf->MultiCell(10, 5, ($meeting->hTeam == $team->teamName ? 'X' :''), 1, '', 0, 0, '', '', true);
$pdf->MultiCell(44, 5, $output->chosenMeeting->hTeam, 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(29, 5, 'Bezoekers', 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(10, 5, ($meeting->oTeam == $team->teamName ? 'X' :''), 1, 'C', 0, 0, '', '', true);
$pdf->MultiCell(45, 5, $output->chosenMeeting->oTeam, 1, 'C', 0, 1, '', '', true);

$startYBox = $pdf->GetY();
$pdf->SetFillColor(165, 165, 165);
$pdf->MultiCell(250, 116, '', 0, 'L', 1, 0, 25, '', true);

$pdf->SetY($startYBox);
$pdf->SetFont('dejavusans', 'B', 10, '', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->MultiCell(44, 16, 'Naam', 1, 'C', 1, 0, '75', '', true);
$pdf->MultiCell(39, 16, 'Voornaam', 1, 'C', 1, 0, '', '', true);
$pdf->writeHTMLCell(24, 16, '', '', 'Klas.<div style="font-size: x-small">(huidig)</div>', 1, 0, '1', true, 'C');
$pdf->MultiCell(21, 16, 'Lidnr. *', 1, 'C', 1, 0, '', '', true);
$pdf->writeHTMLCell(17, 16, '', '', 'Som index*<div style="font-size: x-small">(mei vorig seizoen)</div>', 1, 0, '1', true, 'C');
$pdf->writeHTMLCell(19, 16, '', '', 'Hoogste klas.*<div style="font-size: x-small">(mei vorig seizoen)</div>', 1, 0, '1', true, 'C');
$pdf->writeHTMLCell(19, 16, '', '', 'Ploeg Index*<div style="font-size: x-small">(titularis telt mee)</div>', 1, 0, '1', true, 'C');
$pdf->writeHTMLCell(17, 16, '', '', 'Basis Speler*<div style="font-size: x-small">(mei vorig seizoen)</div>', 1, 1, '1', true, 'C');
$pdf->SetFont('dejavusans', '', 10, '', true);

$pdf->SetXY(0,$startYBox+18);
$pdf->writeHTMLCell(44, 5, 25, '', '<div style="font-weight:bold">Ploegkapitein</div>', 1, 0, '1', true, 'C');
$pdf->writeHTMLCell(83, 5, 75, '', '<div>'.$team->captainName.'</div>', 1, 0, '1', true, 'C');

$pdf->SetXY(0,$startYBox+25);
$pdf->MultiCell(14, 10, 'HD', 1, 'C', 1, 0, '25', '', true,0,false,true,10,'M');
$pdf->MultiCell(15, 10, 'HD 1', 1, 'C', 1, 0, '', '', true,0,false,true,10,'M');
$pdf->MultiCell(15, 10, 'DD 1', 1, 'C', 1, 1, '', '', true,0,false,true,10,'M');
$pdf->MultiCell(14, 10, 'DD', 1, 'C', 1, 0, '25', '', true,0,false,true,10,'M');
$pdf->MultiCell(15, 10, 'HD 2', 1, 'C', 1, 0, '', '', true,0,false,true,10,'M');
$pdf->MultiCell(15, 10, 'DD 2', 1, 'C', 1, 1, '', '', true,0,false,true,10,'M');
$pdf->MultiCell(14, 10, 'GD 1', 1, 'C', 1, 0, '25', '', true,0,false,true,10,'M');
$pdf->MultiCell(15, 10, 'HD 3', 1, 'C', 1, 0, '', '', true,0,false,true,10,'M');
$pdf->MultiCell(15, 10, 'DD 3', 1, 'C', 1, 1, '', '', true,0,false,true,10,'M');
$pdf->MultiCell(14, 10, 'GD 2', 1, 'C', 1, 0, '25', '', true,0,false,true,10,'M');
$pdf->MultiCell(15, 10, 'HD 4', 1, 'C', 1, 0, '', '', true,0,false,true,10,'M');
$pdf->MultiCell(15, 10, 'DD 4', 1, 'C', 1, 1, '', '', true,0,false,true,10,'M');
$pdf->SetY($pdf->GetY()+2);
$pdf->MultiCell(14, 5, 'HE 1', 1, 'C', 1, 0, '25', '', true,0,false,true,5,'M');
$pdf->MultiCell(15, 5, 'HE 1', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
$pdf->MultiCell(15, 5, 'DE 1', 1, 'C', 1, 1, '', '', true,0,false,true,5,'M');
$pdf->MultiCell(14, 5, 'HE 2', 1, 'C', 1, 0, '25', '', true,0,false,true,5,'M');
$pdf->MultiCell(15, 5, 'HE 2', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
$pdf->MultiCell(15, 5, 'DE 2', 1, 'C', 1, 1, '', '', true,0,false,true,5,'M');
$pdf->MultiCell(14, 5, 'DE 1', 1, 'C', 1, 0, '25', '', true,0,false,true,5,'M');
$pdf->MultiCell(15, 5, 'HE 3', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
$pdf->MultiCell(15, 5, 'DE 3', 1, 'C', 1, 1, '', '', true,0,false,true,5,'M');
$pdf->MultiCell(14, 5, 'DE 2', 1, 'C', 1, 0, '25', '', true,0,false,true,5,'M');
$pdf->MultiCell(15, 5, 'HE 4', 1, 'C', 1, 0, '', '', true,0,false,true,5,'M');
$pdf->MultiCell(15, 5, 'DE 4', 1, 'C', 1, 1, '', '', true,0,false,true,5,'M');

$pdf->SetXY(0,$startYBox+25);

$effectivePlayersVblIds = array();
foreach($team->effectivePlayersInTeam as $key => $effectivePlayer) {
	array_push($effectivePlayersVblIds,$effectivePlayer->vblId);
}

$reportedPlayersVblIds = array(); // to keep track of the players that are already mentioned once in the form

$gameCount=0; // to keep track of the number of games already reported
foreach($output->games as $key => $game) {
	$gameCount++;
	$playerCount=0;
	foreach($game->playersInGame as $key => $player) {
		$playerCount++;
		$rankingR="";

		switch($game->gameType) {
			case "HD": $rankingR = $player->rankingRDouble;break;
			case "DD": $rankingR = $player->rankingRDouble;break;
			case "GD": $rankingR = $player->rankingRMix;break;
			case "HE": $rankingR = $player->rankingRSingle;break;
			case "DE": $rankingR = $player->rankingRSingle;break;
		}

		if (!in_array($player->vblId, $reportedPlayersVblIds)) {
			// Only need to report the players full details once
			$pdf->playerLine($player->lastName,
				$player->firstName, $rankingR,
				$player->vblId,
				$player->fixedIndexInsideTeamValue,
				$player->strongestFixedIndexInsideTeamValue,
				in_array($player->vblId, $effectivePlayersVblIds),
				in_array($player->vblId, $team->baseTeamVblIds)
			);
		} else {
			$pdf->playerLine($player->lastName,
				$player->firstName, $rankingR,
				$player->vblId
			);
		}

		array_push($reportedPlayersVblIds, $player->vblId);
	}
	if($gameCount ==4) {
		$pdf->SetY($pdf->GetY()+2);
	}
}




$pdf->SetXY(0,$startYBox+25);
$pdf->MultiCell(6, 5, 'T', 0, 'C', 0, 1, '69', '', true);
$pdf->MultiCell(6, 5, 'I', 0, 'C', 0, 1, '69', '', true);
$pdf->MultiCell(6, 5, 'T', 0, 'C', 0, 1, '69', '', true);
$pdf->MultiCell(6, 5, 'U', 0, 'C', 0, 1, '69', '', true);
$pdf->MultiCell(6, 5, 'L', 0, 'C', 0, 1, '69', '', true);
$pdf->MultiCell(6, 5, 'A', 0, 'C', 0, 1, '69', '', true);
$pdf->MultiCell(6, 5, 'R', 0, 'C', 0, 1, '69', '', true);
$pdf->MultiCell(6, 5, 'I', 0, 'C', 0, 1, '69', '', true);
$pdf->SetY($pdf->GetY()+3);
$pdf->MultiCell(6, 5, 'S', 0, 'C', 0, 1, '69', '', true);
$pdf->MultiCell(6, 5, 'S', 0, 'C', 0, 1, '69', '', true);
$pdf->MultiCell(6, 5, 'E', 0, 'C', 0, 1, '69', '', true);
$pdf->MultiCell(6, 5, 'N', 0, 'C', 0, 1, '69', '', true);


$pdf->SetY($pdf->GetY()+3);
$pdf->SetFont('dejavusans', 'B', 10, '', true);
$pdf->MultiCell(8, 5, 'E', 1, 'C', 1, 0, '158', '', true,0,false,true,5,'B');
$pdf->MultiCell(8, 5, 'D', 1, 'C', 1, 0, '', '', true,0,false,true,5,'B');
$pdf->MultiCell(8, 5, 'G', 1, 'C', 1, 1, '', '', true,0,false,true,5,'B');

$startInvallers = $pdf->GetY();
$pdf->MultiCell(44, 20, 'Invallers', 1, 'C', 1, 1, '25', '', true,0,false,true,20,'M');
$pdf->SetY($startInvallers);
$pdf->reserverPlayerLine();
$pdf->reserverPlayerLine();
$pdf->reserverPlayerLine();
$pdf->reserverPlayerLine();

$pdf->SetY($pdf->GetY()+0.5);
$pdf->SetFont('dejavusans', '', 8, '', true);
$pdf->MultiCell(45, 5, 'Handtekening ploegkapitein,', 0, 'C', 1, 0, '151', '', true,0,false,true,5,'M');
$pdf->MultiCell(61, 5, 'Handtekening ploegkapitein tegenpartij,', 0, 'C', 1, 0, '212', '', true,0,false,true,5,'M');

$pdf->setJPEGQuality(100);
$imgdata = base64_decode('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');
$pdf->Image('@'.$imgdata, 25, $startInvallers+25,0,0, 'JPEG');
$pdf->writeHTMLCell(50, 10, 70, $startInvallers+25, '<div>Badminton Vlaanderen vzw<br>info@badmintonvlaanderen.be<br>www.badmintonvlaanderen.be</div>', 'L', 0, '1', true, 'L');

$pdf->SetFont('dejavusans', '', 6, '', true);
$pdf->MultiCell(55, 5, '* slechts eenmaal in te vullen per speler', 0, 'C', 1, 1, 210, $startInvallers+40, true,0,false,true,5,'B');

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$filename = preg_replace("[^A-Za-z0-9_-]",'','ploegopstellingsformulier_'.$meeting->hTeam.'_'.$meeting->oTeam.'_' . date('Y-m-d') );
$pdf->Output($filename. '.pdf', 'D');

//============================================================+
// END OF FILE
//============================================================+

?>
