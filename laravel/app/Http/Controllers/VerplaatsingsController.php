<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Auth;

use Illuminate\Http\Request;

class VerplaatsingsController extends Controller {


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application welcome screen to the user.
     *
     * @return Response
     */
    public function index(Request $request)
    {


        $results = DB::select('select * from lf_club');
        //var_dump($results);
        //var_dump(Auth::user()->username);
        //return "Welcome " . Auth::user()->username . " you are in mylord. <a href='auth/logout'>logout</a>";
        return view('verplaatsing');
    }

    public function clubAndTeams() {
        $query = <<<EOD
select c.clubName,t.teamName,t.captainName,g.event,g.`type`,g.devision,g.series,p.playerId from lf_club c
join lf_team t on c.clubId = t.club_clubId
join lf_group g on g.groupId = t.group_groupId
join lf_player_has_team pt on pt.team_teamName = t.teamName
join lf_player p on p.playerId = pt.player_playerId
where c.clubId=:clubId
order by c.clubName,t.teamName
EOD;

        $queryDBLoad = <<<EOD
SELECT  date_format(max(`when`),'%Y%m%d%H%i%S') date FROM `lf_event`
where eventType='DBLOAD'
EOD;

        $players = DB::select($query,array('clubId' => Auth::user()->club_id));
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


        header("Content-Disposition: attachment; filename=json.data");
        header("Pragma: no-cache");
        header("Expires: 0");

        return response()->json($result);
    }}
