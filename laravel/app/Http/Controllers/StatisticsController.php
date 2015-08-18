<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Response;

use Illuminate\Http\Request;

class StatisticsController extends Controller {

    function opstelling() {
        return view("stats-opstelling");
    }

    function verplaatsing() {
        return view("stats-verplaatsing");
    }

    function statisticsOpstelling($statType) {

        $queryTotalSelectAndPrintCmdPerTeam = <<<'EOD'
select c.clubName,
t.teamName,
(select count(*) from lf_event e where e.who = t.teamName and e.eventType='teamselect') as 'select',
(select count(*) from lf_event e where e.who = t.teamName and e.eventType in ('print','print2pdf')) as 'print'
from lf_club c
join lf_team t on t.club_clubId = c.clubId
join lf_group g on g.groupId = t.group_groupId
where g.type='PROV'
order by c.clubName,t.teamName
EOD;


        $queryTotalSelectAndPrintCmdPerWeek = <<<'EOD'
select q1.week,
 COALESCE(max(case when q1.eventType='teamSelect' then q1.count end),0) 'select',
 COALESCE(max(case when q1.eventType='print' then q1.count end),0) print
 from (
	select
		week(e.when,5) week,
		case when eventType in ('print','print2pdf') then 'print'
		else eventType end eventType,
		count(*) count
	from lf_event e
		where e.when >= DATE(NOW()) - INTERVAL 51 WEEK
		and e.eventType in ('teamSelect','print','print2pdf')
	group by week(e.when,5),
		  case when eventType in ('print','print2pdf') then 'print'
		  else eventType end
) q1
group by q1.week
EOD;

        $queryTotalCombinedSelectAndPrintCmdPerWeek = <<<'EOD'
select week(STR_TO_DATE(q.date,'%Y%m%d'),5) 'week',count(*) 'combined' from (
select e.who, date_format(e.when,'%Y%m%d') date,max(e.when) ,max(ep.when) from lf_event e
join lf_event ep on ep.eventType in ('print2pdf','print') and e.who = ep.who and ep.when > e.when and ep.when < date_add(e.when, INTERVAL 30 MINUTE)
where e.eventType='teamselect'
and e.when >= DATE(NOW()) - INTERVAL 51 WEEK
group by e.who, date_format(e.when,'%Y%m%d')
order by date_format(e.when,'%Y%m%d') desc
) q
group by week(STR_TO_DATE(q.date,'%Y%m%d'),5)
EOD;

        $queryTotalPerWeek = <<<'EOD'
select week(m.date,5) week,count(*)*2 'matches' from lf_match m
where m.date >= DATE(NOW()) - INTERVAL 51 WEEK
group by week(m.date,5)
EOD;


        $query='';

        switch($statType) {
            case "totalSelectAndPrintCmdPerTeam":
                $result = DB::select($queryTotalSelectAndPrintCmdPerTeam);
                break;
            case "totalSelectAndPrintCmdPerWeek":
                $result=array();
                $result1=DB::select($queryTotalSelectAndPrintCmdPerWeek);
                $result2=DB::select($queryTotalCombinedSelectAndPrintCmdPerWeek);
                $result3=DB::select($queryTotalPerWeek);

                foreach($result1 as $key => $totalSelect) {
                    $totalCombined='0';
                    foreach($result2 as $key => $combinedPerWeek) {
                        if ($totalSelect->week == $combinedPerWeek->week) {
                            $totalCombined = $combinedPerWeek->combined;
                        }
                    }
                    $totalSelect->combined=$totalCombined;

                    $totalMatches='0';
                    foreach($result3 as $key => $totalPerWeek) {
                        if ($totalSelect->week == $totalPerWeek->week) {
                            $totalMatches = $totalPerWeek->matches;
                        }
                    }
                    $totalSelect->totalMatchesX2=$totalMatches;
                    array_push($result,$totalSelect);

                }
                break;
        }

        header("Content-type: application/json");
        //header("Content-type: text/html");
        header("Content-Disposition: attachment; filename=json.data");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo json_encode($result);
    }

    function statisticsVerplaatsing($statType) {
        $queryMeetingsWithActionForPBO= <<<'EOD'
select me.hTeamName,me.oTeamName,date_format(m.date,'%Y%m%d%H%i%S') as date,date_format(cr.proposedDate,'%Y%m%d%H%i%S') as proposedDate from lf_match_extra me
join lf_match m on m.homeTeamName = me.hTeamName and m.outTeamName = me.oTeamName
join lf_match_cr cr on cr.match_matchIdExtra = me.matchIdExtra and cr.finallyChosen=1
where me.actionFor='PBO'
order by me.hTeamName asc
EOD;

        $meetingWithOpenRequest= <<<'EOD'
select me.hTeamName,me.oTeamName,date_format(m.date,'%Y%m%d%H%i%S') as date,me.actionFor,date_format((select max(cr.requestedOn) from lf_match_cr cr where cr.match_matchIdExtra = me.matchIdExtra),'%Y%m%d%H%i%S') as max_requested_on from lf_match_extra me
join lf_match m on m.homeTeamName = me.hTeamName and m.outTeamName = me.oTeamName
where me.status='IN AANVRAAG'
order by me.matchIdExtra asc
EOD;
        $meetingWithOpenRequestPerClub = <<<'EOD'
select c.clubId as clubId ,max(c.clubName) as clubName,count(*) as count from lf_match_extra me
join lf_match m on m.homeTeamName = me.hTeamName and m.outTeamName = me.oTeamName
join lf_team t on t.teamName = me.actionFor
join lf_club c on c.clubId = t.club_clubId
where me.status='IN AANVRAAG'
group by c.clubId
order by c.clubId
EOD;




        switch($statType) {
            case "meetingsWithActionForPBO":
                $result = DB::select($queryMeetingsWithActionForPBO);
                break;
            case "meetingWithOpenRequest":
                $result = DB::select($meetingWithOpenRequest);
                break;
            case "meetingWithOpenRequestPerClub":
                $result = DB::select($meetingWithOpenRequestPerClub);
                break;
        }
        $response = Response::json($result);
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');
        return $response;
    }
}
