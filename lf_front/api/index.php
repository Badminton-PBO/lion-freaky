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
getRoute()->get('/dbload','dbload');
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
join lf_player p on p.playerId = pt.player_playerId 
where g.event != 'LI'
order by c.clubName,t.teamName
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


function dbload() {
		$PBO_COMPETITIE_ID='EF6D253B-4410-4B4F-883D-48A61DDA350D';
		$PBO_COMPETITIE_START_DAY='20140801';
		$PBO_COMPETITIE_END_DAY='20150731';
		$PBO_OVL_ID='638D0B55-C39B-43AB-8A9D-D50D62017FBE';
		$PBO_OVL_GID='3825E3C5-1371-4FF6-94AF-C4A3B152802A';		
		$PBO_USERNAME='g0ldh0rn';
		$PBO_PWD='Hgkxj8DgwE';	

		$USER_AGENT='Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.11) Gecko/20101012 Firefox/3.6.11';
		$LOGIN_STRING='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUJNjM0MDYzNDI3ZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAgU9Y3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kUmVtZW1iZXJNZQVDY3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kTG9naW5JbWFnZUJ1dHRvbg%3D%3D&__EVENTVALIDATION=%2FwEdAAk8ZxYRHnvYNT8dqfzKa%2FZYDc%2F5voaaGYfFlFBXi9EGFfyHSpCYj%2BAUNp9bXc20Z5f%2BOtme7httab8IViMP3HjzlRR%2BDpTMHdYiODpnuHxziR2B%2BiwIwJ5fF61AnAcX2%2BwvDdLMdOmJdT7lzlyuo8NCBjrAGg4uwJH4J35FqmwaB97lIlcv0kHWlCdwWozE4w4%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='.$PBO_USERNAME.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='.$PBO_PWD.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen';
		$CLUBS_CSV_URL='http://toernooi.nl/sport/admin/exportclubs.aspx?id='.$PBO_COMPETITIE_ID.'&ft=1';
		$TEAMS_CSV_URL='http://toernooi.nl/sport/admin/exportteams.aspx?id='.$PBO_COMPETITIE_ID.'&ft=1';
		$PLAYERS_CSV_URL='http://toernooi.nl/organization/export/export_memberperroletypepergroup.aspx?id='.$PBO_OVL_ID.'&gid='.$PBO_OVL_GID.'&ft=1&glid=1';		
		$MATCHES_CSV_URL='http://toernooi.nl/sport/admin/exportteammatches.aspx?id='.$PBO_COMPETITIE_ID.'&ft=1&sd='.$PBO_COMPETITIE_START_DAY.'000000&ed='.$PBO_COMPETITIE_END_DAY.'000000';
		
        // create curl resource
        $ch = curl_init();        

        // set url
        curl_setopt($ch, CURLOPT_URL, "http://toernooi.nl/member/login.aspx");
        curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
        //curl_setopt($ch, CURLOPT_POST, TRUE);        
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, TRUE);                       
        curl_setopt($ch, CURLOPT_POSTFIELDS, $LOGIN_STRING);
		
		
		//TDE 20141106: CURLOPT_COOKIEJAR, CURLOPT_COOKIEFILE not working on one.com
		// So we need to manually parse the cookie (ex. sessioncookie) from the header and put in the next request
        //curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie.txt');
        //curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie.txt');
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);        
        
        //return all http header and cookies
        curl_setopt($ch, CURLOPT_HEADER, 1);        
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $logonResponse = curl_exec($ch);       
        
        //Parse the cookies out of the r
        preg_match_all('|Set-Cookie: (.*);|U', $logonResponse, $results);
        $cookies = implode(';', $results[1]);
        
        //Ready to download csv files
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, FALSE);                
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);

        //Download CLUBS CSV
        curl_setopt($ch, CURLOPT_URL, $CLUBS_CSV_URL);       
        $clubCSV = curl_exec($ch);

        //Download TEAMS CSV
        curl_setopt($ch, CURLOPT_URL, $TEAMS_CSV_URL);
        $teamsCSV = curl_exec($ch);

        //Download PLAYERS CSV
        curl_setopt($ch, CURLOPT_URL, $PLAYERS_CSV_URL);
        $playersCSV = curl_exec($ch);

		//Download MATCHES CSV
        curl_setopt($ch, CURLOPT_URL, $MATCHES_CSV_URL);
        $matchesCSV = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);   
        
        cleanDB();
        
        loadCSV($clubCSV,'clubs');        
        loadCSV($teamsCSV,'teams');
        loadCSV($matchesCSV,'matches');
        loadCSV($playersCSV,'players');
        
        
        print("OK");
}

function cleanDB() {
	getDatabase()->execute('DELETE from lf_match');
	getDatabase()->execute('DELETE from lf_ranking');
	getDatabase()->execute('DELETE from lf_player_has_team');
	getDatabase()->execute('DELETE from lf_player');
	getDatabase()->execute('DELETE from lf_team');
	getDatabase()->execute('DELETE from lf_club');
	getDatabase()->execute('DELETE from lf_group');
	getDatabase()->execute('DELETE from lf_tmpdbload_teamscsv');
	getDatabase()->execute('DELETE from lf_tmpdbload_playerscsv');
	getDatabase()->execute('DELETE FROM lf_tmpdbload_15mei');
	getDatabase()->execute('DELETE FROM lf_tmpdbload_basisopstellingliga');
}

function loadCSV($CSV,$type) {

	$parsedCsv = parse_csv($CSV,';',true,false);	
	$headers = array_flip($parsedCsv[0]);
	
	for($i = 1, $size = count($parsedCsv)-1; $i < $size; ++$i) {
		switch($type) {
				case "clubs": getDatabase()->execute('INSERT INTO lf_club(clubId, clubName) VALUES(:clubId, :clubName)',
					array(':clubId' => $parsedCsv[$i][$headers['?code']],
					':clubName' => $parsedCsv[$i][$headers['name']])
					);
					break;
				case "teams": getDatabase()->execute('INSERT INTO lf_tmpdbload_teamscsv(name, clubCode, year, eventName, drawName ) VALUES(:name, :clubCode, :year, :eventName, :drawName)', 
					array(':name' => $parsedCsv[$i][$headers['name']],
					 ':clubCode' => $parsedCsv[$i][$headers['clubcode']], 
					 ':year' => 2014, 
					 ':eventName' => $parsedCsv[$i][$headers['eventname']], 
					 ':drawName' => $parsedCsv[$i][$headers['DrawName']])
					 );
					break;
				case "matches": getDatabase()->execute('INSERT INTO lf_match(homeTeamName, outTeamName, locationId, locationName, matchId, date) VALUES(:homeTeamName, :outTeamName, :locationId, :locationName, :matchId, str_to_date(:date, \'%e-%c-%Y %H:%i:%S\'))',
					array(':homeTeamName' => $parsedCsv[$i][$headers['team1name']],
					':outTeamName' => $parsedCsv[$i][$headers['team2name']],
					':locationId' => $parsedCsv[$i][$headers['locationid']],
					':locationName' => $parsedCsv[$i][$headers['locationname']],
					':matchId' => $parsedCsv[$i][$headers['matchid']],
					':date' => $parsedCsv[$i][$headers['plannedtime']])
					);
					break;	
				case "players": getDatabase()->execute('INSERT INTO lf_tmpdbload_playerscsv(memberId,firstName,lastName,gender,groupName,playerLevelSingle,playerLevelDouble,playerLevelMixed,typeName,role) VALUES(:memberId,:firstName,:lastName,:gender,:groupName,:playerLevelSingle,:playerLevelDouble,:playerLevelMixed,:typeName,:role)',
					array(':memberId' => $parsedCsv[$i][$headers['memberid']],
					':firstName' => $parsedCsv[$i][$headers['firstname']],
					':lastName' => $parsedCsv[$i][$headers['lastname']],
					':gender' => $parsedCsv[$i][$headers['gender']],
					':groupName' => $parsedCsv[$i][$headers['groupname']],
					':playerLevelSingle' => $parsedCsv[$i][$headers['PlayerLevelSingle']],
					':playerLevelDouble' => $parsedCsv[$i][$headers['PlayerLevelDouble']],
					':playerLevelMixed' => $parsedCsv[$i][$headers['PlayerLevelMixed']],
					':typeName' => $parsedCsv[$i][$headers['TypeName']],
					':role' => $parsedCsv[$i][$headers['role']])
					);
					break;					
		}			
	}	

$insertLfGroup = <<<'EOD'
INSERT INTO lf_group (tournament,event,devision,series)
select `year`,lf_dbload_eventcode(eventName),lf_dbload_devision(drawName),lf_dbload_serie(drawName) from lf_tmpdbload_teamscsv
group by `year`,lf_dbload_eventcode(eventName),lf_dbload_devision(drawName),lf_dbload_serie(drawName);
EOD;
$insertLfTeam = <<<'EOD'
INSERT INTO lf_team (teamName,sequenceNumber,club_clubId, group_groupId)
select name,lf_dbload_teamSequenceNumber(name),clubCode,(select groupId from lf_group lfg where lfg.tournament = t.`year` and lf_dbload_eventcode(t.eventName) = lfg.event and  lf_dbload_devision(t.drawName) = lfg.devision and lf_dbload_serie(t.drawName) = lfg.series)  from lf_tmpdbload_teamscsv t;
EOD;
$deleteLfTmpdbloadPlayers = <<<'EOD'
delete from lf_tmpdbload_playerscsv
where role = 'Speler'
and memberId in (
select c.memberId from (
select memberId from lf_tmpdbload_playerscsv t
group by memberId
having count(*) >1) as c
);		
EOD;
$insertLfRanking = <<<'EOD'
INSERT INTO lf_ranking (`date`,singles,doubles,mixed,player_playerId)
select SYSDATE(),t.playerLevelSingle,t.playerLevelDouble,t.playerLevelMixed,t.memberId from lf_tmpdbload_playerscsv t
join lf_club c on c.clubName=t.groupName;
EOD;
$insertLfPlayer = <<<'EOD'
INSERT INTO lf_player (playerId,firstName,lastName,gender,club_clubId,type)
select t.memberId,t.firstName,t.lastName, CASE when t.gender='V' then 'F' else t.gender END,c.clubId, case when t.typeName like 'Recreant%' then 'R' when t.typeName like 'Competitie%' then 'C' when t.typeName like 'Jeugd%' then 'J' END from lf_tmpdbload_playerscsv t
join lf_club c on c.clubName=t.groupName;			
EOD;


	
	switch($type) {
		case "teams": 
			 getDatabase()->execute($insertLfGroup);
			 getDatabase()->execute($insertLfTeam);
			break;
		case "players":
			// When player is from O-Vl Club and rented to another O-Vl it will appear twice. However, we only wan to keep the record with role='Uitgeleende speler
			// Some tricks needed to avoid mysql limitation: In MySQL, you can't modify the same table which you use in the SELECT part
			// http://stackoverflow.com/questions/45494/mysql-error-1093-cant-specify-target-table-for-update-in-from-clause
			getDatabase()->execute($deleteLfTmpdbloadPlayers);
			
			//When must import players from type=Recreant too because they can be part of a baseTeam!
			getDatabase()->execute($insertLfPlayer);						
			
			getDatabase()->execute($insertLfRanking);			
			break;
	}	
	
	
		
	//print_r($parsedCsv);
}


function parse_csv ($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
{
    return array_map(
        function ($line) use ($delimiter, $trim_fields) {
            return array_map(
                function ($field) {
                    return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
                },
                $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line)
            );
        },
        preg_split(
            $skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s',
            preg_replace_callback(
                '/"(.*?)"/s',
                function ($field) {
                    return urlencode(utf8_encode($field[1]));
                },
                $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string)
            )
        )
    );
}
