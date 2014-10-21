<?php
mb_internal_encoding("UTF-8");
include_once './libs/epiphany-20130912/Epi.php';
include_once './config.php';
Epi::setPath('base', './libs/epiphany-20130912');
Epi::init('route','database');
EpiDatabase::employ('mysql',constant('DB_NAME'),constant('DB_HOST'),constant('DB_USER'),constant('DB_PASSWORD')); // type = mysql, database = mysql, host = localhost, user = root, password = [empty]
Epi::setSetting('exceptions', false);



//Epi::init('base','cache','session');
// Epi::init('base','cache-apc','session-apc');
// Epi::init('base','cache-memcached','session-apc');

/*
 * This is a sample page whch uses EpiCode.
 * There is a .htaccess file which uses mod_rewrite to redirect all requests to index.php while preserving GET parameters.
 * The $_['routes'] array defines all uris which are handled by EpiCode.
 * EpiCode traverses back along the path until it finds a matching page.
 *  i.e. If the uri is /foo/bar and only 'foo' is defined then it will execute that route's action.
 * It is highly recommended to define a default route of '' for the home page or root of the site (yoursite.com/).
 * 
  */
getRoute()->get('/clubsAndTeams','clubsAndTeams');
getRoute()->get('/teamAndClubPlayers/([\w\s]+)','teamAndClubPlayers');
getRoute()->get('/', 'usage');
getRoute()->run(); 

/*
 * ******************************************************************************************
 * Define functions and classes which are executed by EpiCode based on the $_['routes'] array
 * ******************************************************************************************
 */
  
function usage() {
	echo "API method";
	echo "Usage:<br>";
	echo "HTTP GET /clubsAndTeams <br>";
	echo "HTTP GET /teamAndClubPlayers/Gentse%203G <br>";
	echo "HTTP POST-PARAM playerName=%name%";
}	


function  teamAndClubPlayers($teamName) {
$query = <<<EOD
select c.clubName,p.playerId,p.firstName,p.lastName,p.gender,rF.singles fSingles,rF.doubles fDoubles,rF.mixed fMixed, rV.Singles vSingles,rV.doubles vDoubles,rV.mixed vMixed from lf_club c
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
where (m.homeTeamName = :team or m.outTeamName = :team)
and m.date >= now()
order by m.date asc;
EOD;


	$players = getDatabase()->all($query, array(':team' =>$teamName));
	$result = array('clubName' => $players[0]['clubName'], 'teamName' => $teamName, 'meetings'=>array(),'players'=>array());
	
	//Add match data		
	$matches = getDatabase()->all($queryEvent, array(':team' =>$teamName));
	foreach($matches as $key => $match) {
		array_push($result["meetings"],array('hTeam' => $match['homeTeamName'], 'oTeam' => $match['outTeamName'], 'dateTime' => $match['date'], 'locationName' => $match['locationName']));		
	}	
	
	//Add clubplayer data
	foreach($players as $key => $player) {	
		array_push($result["players"],array('firstName' => $player['firstName'] ,'lastName' => $player['lastName'], 'vblId' => $player['playerId'], 'gender' => $player['gender'], 'fixedRanking' => array($player['fSingles'], $player['fDoubles'],$player['fMixed']), 'ranking' => array($player['vSingles'], $player['vDoubles'],$player['vMixed'])));
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
 
function  clubsAndTeams() {
$query = <<<EOD
select c.clubName,t.teamName,g.event,g.devision,g.series,p.playerId from lf_club c 
join lf_team t on c.clubId = t.club_clubId 
join lf_group g on g.groupId = t.group_groupId 
join lf_player_has_team pt on pt.team_teamName = t.teamName 
join lf_player p on p.playerId = pt.player_playerId order by c.clubName,t.teamName
EOD;
	
	$players = getDatabase()->all($query);
	
	$currentClubName="";
	$currentTeam="";
	$clubCounter=-1;
	$teamCounter=-1;
	foreach($players as $key => $player) {	
		if  ($currentClubName != $player['clubName']) {			
			$clubCounter++;
			$teamCounter=-1;
			$currentTeamName="";
			$result[$clubCounter]= array("clubName" => $player['clubName'], "teams" =>array());
			$currentClubName = $player['clubName'];
		}
		if ($currentTeamName != $player['teamName']) {
			$teamCounter++;
			$result[$clubCounter]["teams"][$teamCounter] = array('teamName' => $player['teamName'], 'event' => $player['event'], 'devision' => $player['devision'],'series'=> $player['series'], 'baseTeam' => array());
			$currentTeamName = $player['teamName'];
		}
		array_push($result[$clubCounter]["teams"][$teamCounter]["baseTeam"],$player['playerId']);		
	}	
	header("Content-type: application/json");
	//header("Content-type: text/html");
	header("Content-Disposition: attachment; filename=json.data");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo json_encode($result);	
} 
