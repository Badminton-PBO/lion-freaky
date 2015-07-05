<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Response;

use Illuminate\Http\Request;

class CalendarSyncController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function pboTeamMatches()
	{
        $queryPboTeamMatches = <<<EOD
select t.teamName,concat(m.homeTeamName,' - ',m.outTeamName) subject,date_format(m.date,'%d/%m/%Y') startDate,date_format(m.date,'%T') startTime,date_format(date_add(m.date, INTERVAL 3 HOUR),'%d/%m/%Y') endDate,date_format(date_add(m.date, INTERVAL 3 HOUR),'%T') endTime,m.locationName from lf_match m
join lf_team t on m.homeTeamName = t.teamName  or m.outTeamName =t.teamName
where t.teamName in (select teamName from lf_team)
order by t.teamName, m.date
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
            $currentEvent->addChild('subject',$match->subject);
            $currentEvent->addChild('location',$match->locationName);
            $currentEvent->addChild('startDate',$match->startDate);
            $currentEvent->addChild('startTime',$match->startTime);
            $currentEvent->addChild('endDate',$match->endDate);
            $currentEvent->addChild('endTime',$match->endTime);

            //array_push($result["teams"][$teamCounter]["matches"],array('subject' => $match->subject ,'location' => $match->locationName,'startDate' => $match->startDate,'startTime' => $match->startTime,'endDate' => $match->endDate,'endTime' => $match->endTime));
        }


        header("Content-type: application/xml");
        header("Content-Disposition: attachment; filename=data.xml");
        header("Pragma: no-cache");
        header("Expires: 0");
        print $xmlRoot->asXML();
	}
}
