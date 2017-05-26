<?php namespace App\Http\Controllers;

use App\Http\Requests;
use Auth;
use DB;
use Request;
use Response;

class BasisPloegenController extends Controller {


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("basisploegen");
    }


    public function clubPlayers() {
        $query = <<<EOD
select c.clubName,p.playerId,p.firstName,p.lastName,p.gender,p.type, rF.singles fSingles,rF.doubles fDoubles,rF.mixed fMixed, rV.Singles vSingles,rV.doubles vDoubles,rV.mixed vMixed from lf_club c
join lf_player p on p.club_clubId = c.clubId
join lf_ranking rF on rF.player_playerId = p.playerId
join lf_ranking rV on rV.player_playerId = p.playerId
where c.clubId = :clubId
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

        $queryClub = <<<EOD
        SELECT clubId,clubName,teamNamePrefix FROM lf_club WHERE clubId =:clubId
EOD;

        $players = DB::select($query, array('clubId' =>Auth::user()->club_id));
        $club = DB::select($queryClub, array('clubId' =>Auth::user()->club_id));
        $result = array('players'=>array());
        $result['club'] = $club[0];

        //Add clubplayer data
        foreach($players as $key => $player) {
            array_push($result["players"],array('firstName' => $player->firstName ,'lastName' => $player->lastName, 'vblId' => $player->playerId, 'gender' => $player->gender, 'type' => $player->type, 'fixedRanking' => array($player->fSingles, $player->fDoubles,$player->fMixed), 'ranking' => array($player->vSingles, $player->vDoubles,$player->vMixed)));
        }

        $response = Response::json($result);
        $response->header("Cache-Control","no-cache, no-store, must-revalidate"); // HTTP 1.1.
        $response->header("Pragma","no-cache");// HTTP 1.0.
        $response->header("Expires","0");// Proxies.
        return $response;
    }

    public function searchPlayer($vblId) {
        $query = <<<EOD
SELECT clubName,playerId,firstName,lastName,gender,playerLevelSingle fSingles,playerLevelDouble fDoubles,playerLevelMixed fMixed FROM lf_tmpdbload_15mei
WHERE playerId=:playerId
and playerId not in
(
select p.playerId from lf_club c
join lf_player p on p.club_clubId = c.clubId
and c.clubId=:clubId)
EOD;
        $players = DB::select($query, array('playerId' =>$vblId,'clubId'=>Auth::user()->club_id));
        $result = array('players'=>array());

        //Add clubplayer data
        foreach($players as $key => $player) {
            $correctGender = $player->gender == 'V'? "F": $player->gender;
            array_push($result["players"],array('firstName' => $player->firstName ,'lastName' => $player->lastName, 'vblId' => $player->playerId, 'gender' => $correctGender, 'fixedRanking' => array($player->fSingles, $player->fDoubles,$player->fMixed)));
        }

        $response = Response::json($result);
        $response->header("Cache-Control","no-cache, no-store, must-revalidate"); // HTTP 1.1.
        $response->header("Pragma","no-cache");// HTTP 1.0.
        $response->header("Expires","0");// Proxies.
        return $response;
    }


    public function currentTeams() {
        $clubId = Auth::user()->club_id;

        //The ordering is very important as the teamNames will actually be calculated in the front
        $queryTeamAndPlayers = <<<EOD
SELECT t.teamName,lf_dbload_eventcode(t.teamName) as teamType,lf_dbload_teamSequenceNumber(t.teamName) as teamSequence,p.playerId,tp.role,p.firstName,p.lastName,p.gender,p.singles,p.doubles,p.mixed FROM `lf_bp_team` t
left outer join lf_bp_player_has_team tp on tp.team_teamName = t.teamName
left outer join lf_bp_player p on p.playerId = tp.player_playerId
where t.club_clubId=:clubId
order by lf_dbload_eventcode(t.teamName),convert(lf_dbload_teamSequenceNumber(t.teamName),UNSIGNED INTEGER) asc
EOD;

        $teamPlayers = DB::select($queryTeamAndPlayers, array('clubId' =>$clubId));

        $result = array('teams'=>array());
        $team = array('teamName'=>'DUMMY');
        foreach($teamPlayers as $key => $teamPlayer) {
            //Create new team if previous teamName was different
            if ($teamPlayer->teamName != $team["teamName"]) {
                if ($team["teamName"]!='DUMMY') {
                    array_push($result["teams"],$team);
                }
                $team = array('teamName'=>$teamPlayer->teamName,'teamType'=>$teamPlayer->teamType,'teamSequence' => $teamPlayer->teamSequence,"players"=>array());
            }

            //Add player to team
            if (!empty($teamPlayer->playerId)) {
                array_push($team["players"],array(
                   "vblId" => $teamPlayer->playerId,
                   "firstName" => $teamPlayer->firstName,
                    "lastName" => $teamPlayer->lastName,
                    "gender" => $teamPlayer->gender,
                    "role" => $teamPlayer->role,
                    'fixedRanking' => array($teamPlayer->singles, $teamPlayer->doubles,$teamPlayer->mixed)
                ));
            }
        }
        if (sizeof($teamPlayers) > 0) {
            //Pushing last team
            array_push($result["teams"],$team);
        }

        $response = Response::json($result);
        $response->header("Cache-Control","no-cache, no-store, must-revalidate"); // HTTP 1.1.
        $response->header("Pragma","no-cache");// HTTP 1.0.
        $response->header("Expires","0");// Proxies.
        return $response;
    }

    public function saveTeams() {
        $teams = Request::input("teams");
        $clubId = Auth::user()->club_id;
        //print_r($teams);

        $deleteLinkPlayerTeam = <<<'EOD'
delete from lf_bp_player_has_team where team_teamName in (select teamName from lf_bp_team t where t.club_clubId =:clubId);
EOD;

        $deletePlayers = <<<'EOD'
delete from lf_bp_player where club_clubId =:clubId;
EOD;

        $deleteTeam = <<<'EOD'
delete from lf_bp_team where club_clubId =:clubId;
EOD;

        DB::delete($deleteLinkPlayerTeam,
            array(
                'clubId' => $clubId
            )
        );

        DB::delete($deletePlayers,
            array(
                'clubId' => $clubId
            )
        );
        DB::delete($deleteTeam,
            array(
                'clubId' => $clubId
            )
        );

        $insertTeam = <<<'EOD'
insert into lf_bp_team (teamName, sequenceNumber, club_clubId)
values(:teamName,lf_dbload_teamSequenceNumber(:teamName2),:clubId);
EOD;

        $insertPlayer = <<<'EOD'
insert into lf_bp_player (playerId, club_clubId, firstName,lastName,gender,singles,doubles,mixed)
values(:playerId,:clubId,:firstName,:lastName,:gender,:singles,:doubles,:mixed);
EOD;

        $insertPlayerInTeam = <<<'EOD'
insert into lf_bp_player_has_team(player_playerId,team_teamName,role)
values (:playerId,:teamName,:role);
EOD;

        $insertedPlayerIds=[];
        foreach ($teams as $key => $team) {
            //print($team["teamName"]);
            DB::insert($insertTeam,
                array(
                    ':teamName' => $team["teamName"],
                    ':teamName2' => $team["teamName"],
                    ':clubId' => $clubId
                )
            );
            foreach ($team["playersInTeam"] as $key => $player) {
                if (!(in_array($player["vblId"], $insertedPlayerIds))) {
                    DB::insert($insertPlayer,
                        array(
                            ':playerId' => $player["vblId"],
                            ':clubId' => $clubId,
                            ':firstName' => $player["firstName"],
                            ':lastName' => $player["lastName"],
                            ':gender' => $player["gender"],
                            ':singles' => $player["fixedRankingSingle"],
                            ':doubles' => $player["fixedRankingDouble"],
                            ':mixed' => $player["fixedRankingMix"]
                        )
                    );
                }
                array_push($insertedPlayerIds, $player["vblId"]);

                DB::insert($insertPlayerInTeam,
                    array(
                        ':playerId' => $player["vblId"],
                        ':teamName' => $team["teamName"],
                        ':role' => 'P',
                    )
                );
            }

            foreach ($team["realPlayersInTeam"] as $key => $player) {
                if (!(in_array($player["vblId"], $insertedPlayerIds))) {
                    DB::insert($insertPlayer,
                        array(
                            ':playerId' => $player["vblId"],
                            ':clubId' => $clubId,
                            ':firstName' => $player["firstName"],
                            ':lastName' => $player["lastName"],
                            ':gender' => $player["gender"],
                            ':singles' => $player["fixedRankingSingle"],
                            ':doubles' => $player["fixedRankingDouble"],
                            ':mixed' => $player["fixedRankingMix"]
                        )
                    );
                }
                array_push($insertedPlayerIds, $player["vblId"]);

                DB::insert($insertPlayerInTeam,
                    array(
                        ':playerId' => $player["vblId"],
                        ':teamName' => $team["teamName"],
                        ':role' => 'R',
                    )
                );
            }


        }
        $teams["processedSuccessfull"]= "true";

        $response = Response::json($teams);
        $response->header("Cache-Control","no-cache, no-store, must-revalidate"); // HTTP 1.1.
        $response->header("Pragma","no-cache"); // HTTP 1.0.
        $response->header("Expires","0"); // Proxies.

        return $response;
    }

}
