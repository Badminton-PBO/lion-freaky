<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

use Illuminate\Http\Request;

class OpstellingsController extends Controller {


    public function index(Request $request)
    {
        return view("opstelling");
    }


    public function clubAndTeams() {
$query = <<<EOD
select c.clubName,t.teamName,t.captainName,g.event,g.`type`,g.devision,g.series,p.playerId from lf_club c
join lf_team t on c.clubId = t.club_clubId
join lf_group g on g.groupId = t.group_groupId
join lf_player_has_team pt on pt.team_teamName = t.teamName
join lf_player p on p.playerId = pt.player_playerId
order by c.clubName,t.teamName
EOD;

$queryDBLoad = <<<EOD
SELECT  date_format(max(`when`),'%Y%m%d%H%i%S') date FROM `lf_event`
where eventType='DBLOAD'
EOD;

        $players = DB::select($query);
        $dbloads = DB::select($queryDBLoad);

        $currentClubName="";
        $currentTeam="";
        $clubCounter=-1;
        $teamCounter=-1;
        foreach($players as $key => $player) {
            if  ($currentClubName != $player->clubName) {
                $clubCounter++;
                $teamCounter=-1;
                $currentTeamName="";
                $result["clubs"][$clubCounter]= array("clubName" => $player->clubName, "teams" =>array());
                $currentClubName = $player->clubName;
            }
            if ($currentTeamName != $player->teamName) {
                $teamCounter++;
                $result["clubs"][$clubCounter]["teams"][$teamCounter] = array('teamName' => $player->teamName,'type' =>$player->type, 'event' => $player->event, 'devision' => $player->devision,'series'=> $player->series,'captainName'=> $player->captainName, 'baseTeam' => array());
                $currentTeamName = $player->teamName;
            }
            array_push($result["clubs"][$clubCounter]["teams"][$teamCounter]["baseTeam"],$player->playerId);
        }
        $result['DBLOAD'] = $dbloads;


        header("Content-type: application/json");
        //header("Content-type: text/html");
        header("Content-Disposition: attachment; filename=json.data");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo json_encode($result);
    }

    public function  teamAndClubPlayers($teamName) {
    $query = <<<EOD
select c.clubName,p.playerId,p.firstName,p.lastName,p.gender,p.type, rF.singles fSingles,rF.doubles fDoubles,rF.mixed fMixed, rV.Singles vSingles,rV.doubles vDoubles,rV.mixed vMixed from lf_club c
join lf_player p on p.club_clubId = c.clubId
join lf_ranking rF on rF.player_playerId = p.playerId
join lf_ranking rV on rV.player_playerId = p.playerId
where c.clubName = (
    select c.clubName from lf_club c
    join lf_team t on c.clubId = t.club_clubId
    where t.teamName=:team
)
and rV.date = (
select max(rr.date) from lf_player pp
join lf_ranking rr on rr.player_playerId = pp.playerId
where pp.playerId = p.playerId
group by p.playerId
)
and rF.date = (
select min(rr.date) from lf_player pp
join lf_ranking rr on rr.player_playerId = pp.playerId
where pp.playerId = p.playerId
group by p.playerId)
EOD;

        $queryEvent = <<<EOD
select m.homeTeamName,m.outTeamName, date_format(m.date,'%Y%m%d%H%i%S') date,m.locationName from lf_match m
where (m.homeTeamName = :team1 or m.outTeamName = :team2)
and m.date >= now()
order by m.date asc;
EOD;

        $queryEventAll = <<<EOD
select m.homeTeamName,m.outTeamName, date_format(m.date,'%Y%m%d%H%i%S') date,m.locationName from lf_match m
where (m.homeTeamName = :team1 or m.outTeamName = :team2)
order by m.date asc;
EOD;

        $players = DB::select($query, array('team' =>$teamName));
        $result = array('clubName' => $players[0]->clubName, 'teamName' => $teamName, 'meetings'=>array(),'players'=>array());

        $finaleQueryEvent = env('SHOW_ALL_MEETINGS', false) ? $queryEventAll : $queryEvent;
        //Add match data
        $matches = DB::select($finaleQueryEvent, array('team1' =>$teamName,'team2' =>$teamName));
        foreach($matches as $key => $match) {
            array_push($result["meetings"],array('hTeam' => $match->homeTeamName, 'oTeam' => $match->outTeamName, 'dateTime' => $match->date, 'locationName' => $match->locationName));
        }

        //Add clubplayer data
        foreach($players as $key => $player) {
            array_push($result["players"],array('firstName' => $player->firstName ,'lastName' => $player->lastName, 'vblId' => $player->playerId, 'gender' => $player->gender, 'type' => $player->type, 'fixedRanking' => array($player->fSingles, $player->fDoubles,$player->fMixed), 'ranking' => array($player->vSingles, $player->vDoubles,$player->vMixed)));
        }

        //Create event for this request
        EventController::logEvent('teamselect',$teamName);

        header("Content-type: application/json");
        //header("Content-type: text/html");
        header("Content-Disposition: attachment; filename=json.data");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo json_encode($result);
        //echo json_encode($players);
        //echo "Reporting:".$teamName;
    }
}
