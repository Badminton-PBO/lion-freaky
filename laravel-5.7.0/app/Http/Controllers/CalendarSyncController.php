<?php namespace App\Http\Controllers;

use DB;
use Response;

class CalendarSyncController extends Controller {

    public function index() {
        return view('agenda');
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function pboTeamMatches()
	{
        $queryPboTeamMatches = <<<EOD
select m.matchId, t.teamName,concat(m.homeTeamName,' - ',m.outTeamName) subject,m.date,date_format(m.date,'%d/%m/%Y %T') startDateTime,date_format(date_add(m.date, INTERVAL 180 MINUTE),'%d/%m/%Y %T') endDateTime,concat(m.locationName,', ',l1.address,', ',l1.postalCode,', ',l1.city) locationName from lf_match m
join lf_team t on m.homeTeamName = t.teamName  or m.outTeamName =t.teamName
left join lf_location l1 on l1.locationId = m.locationId
where t.teamName in (select teamName from lf_team) and m.locationId is not null and m.locationId != ''
union
select m.matchId, t.teamName,concat(m.homeTeamName,' - ',m.outTeamName) subject,m.date,date_format(m.date,'%d/%m/%Y %T') startDateTime,date_format(date_add(m.date, INTERVAL 180 MINUTE),'%d/%m/%Y %T') endDateTime,concat(m.locationName,', ',l1.address,', ',l1.postalCode,', ',l1.city) locationName from lf_match m
join lf_team t on m.homeTeamName = t.teamName  or m.outTeamName =t.teamName
left join lf_location l1 on l1.locationName = m.locationName and m.locationId is null
where t.teamName in (select teamName from lf_team) and (m.locationId is null or m.locationId = '')
order by teamName asc, date
EOD;

        $matches = DB::select($queryPboTeamMatches);
        $currentTeamName="";
        $teamCounter=-1;
        $xmlRoot = new \SimpleXMLElement('<teams/>');
        $currentXmlTeam="";
        foreach($matches as $key => $match) {
            if ($currentTeamName != $match->teamName) {
                $teamCounter++;
                $currentTeamName = $match->teamName;
                $currentXmlTeam =  $xmlRoot->addChild('team');
                $currentXmlTeam->addAttribute('name',$match->teamName);
                //$result["teams"][$teamCounter] = array("teamName" => $match->teamName, "matches" =>array());
            }
            $currentEvent = $currentXmlTeam->addChild('event');
            $currentEvent->addChild('matchId',$match->matchId);
            $currentEvent->addChild('subject',$match->subject);
            $currentEvent->addChild('location',$match->locationName);
            $currentEvent->addChild('startDateTime',$match->startDateTime);
            $currentEvent->addChild('endDateTime',$match->endDateTime);

            //array_push($result["teams"][$teamCounter]["matches"],array('subject' => $match->subject ,'location' => $match->locationName,'startDate' => $match->startDate,'startTime' => $match->startTime,'endDate' => $match->endDate,'endTime' => $match->endTime));
        }


        header("Content-type: application/xml");
        header("Content-Disposition: attachment; filename=data.xml");
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
        header("Pragma: no-cache");// HTTP 1.0.
        header("Expires: 0");// Proxies.
        print $xmlRoot->asXML();
	}
}
