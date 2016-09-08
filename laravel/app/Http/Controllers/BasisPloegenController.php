<?php namespace App\Http\Controllers;

use App\Http\Requests;
use DB;
use Illuminate\Http\Request;

class BasisPloegenController extends Controller {

    public function index(Request $request)
    {
        return view("basisploegen");
    }


    public function clubPlayers($clubId) {
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

        $players = DB::select($query, array('clubId' =>$clubId));
        $result = array('players'=>array());

        //Add clubplayer data
        foreach($players as $key => $player) {
            array_push($result["players"],array('firstName' => $player->firstName ,'lastName' => $player->lastName, 'vblId' => $player->playerId, 'gender' => $player->gender, 'type' => $player->type, 'fixedRanking' => array($player->fSingles, $player->fDoubles,$player->fMixed), 'ranking' => array($player->vSingles, $player->vDoubles,$player->vMixed)));
        }

        header("Content-type: application/json");
        //header("Content-type: text/html");
        header("Content-Disposition: attachment; filename=json.data");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo json_encode($result);
        //echo json_encode($players);
        //echo "Reporting:".$teamName;
    }

    public function searchPlayer($vblId,$clubId) {
        $query = <<<EOD
SELECT clubName,playerId,firstName,lastName,gender,playerLevelSingle fSingles,playerLevelDouble fDoubles,playerLevelMixed fMixed FROM lf_tmpdbload_15mei
WHERE playerId=:playerId
and playerId not in
(
select p.playerId from lf_club c
join lf_player p on p.club_clubId = c.clubId
and c.clubId=:clubId)
EOD;
        $players = DB::select($query, array('playerId' =>$vblId,'clubId'=>$clubId));
        $result = array('players'=>array());

        //Add clubplayer data
        foreach($players as $key => $player) {
            $correctGender = $player->gender == 'V'? "F": $player->gender;
            array_push($result["players"],array('firstName' => $player->firstName ,'lastName' => $player->lastName, 'vblId' => $player->playerId, 'gender' => $correctGender, 'fixedRanking' => array($player->fSingles, $player->fDoubles,$player->fMixed)));
        }

        header("Content-Disposition: attachment; filename=json.data");
        header("Pragma: no-cache");
        header("Expires: 0");

        //echo json_encode($result);//Somehow the  Content-type was always set to text/html
        return response()->json($result);
        //echo json_encode($players);
        //echo "Reporting:".$teamName;
    }

}
