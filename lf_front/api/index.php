<?php
mb_internal_encoding("UTF-8");
include_once './libs/epiphany-20130912/Epi.php';
include_once './config.php';
Epi::setPath('base', './libs/epiphany-20130912');
Epi::init('route','database');
EpiDatabase::employ('mysql',constant('DB_NAME'),constant('DB_HOST'),constant('DB_USER'),constant('DB_PASSWORD')); // type = mysql, database = mysql, host = localhost, user = root, password = [empty]
Epi::setSetting('exceptions', false);
ini_set('memory_limit', '256M');// Extra memory needed to load big CSV like fixedRankingMei15

//phpinfo();
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
getRoute()->get('/teamAndClubPlayers/([\w\s-]+)','teamAndClubPlayers');
getRoute()->get('/logEvent/([\w]+)/([\w\s-]+)','logEvent');
getRoute()->get('/dbload','dbload');
getRoute()->get('/dbload/(\w+)/(\w+)','dbload');
getRoute()->get('/statistic/([\w]+)','statistic');
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
		array_push($result["players"],array('firstName' => $player['firstName'] ,'lastName' => $player['lastName'], 'vblId' => $player['playerId'], 'gender' => $player['gender'], 'type' => $player['type'], 'fixedRanking' => array($player['fSingles'], $player['fDoubles'],$player['fMixed']), 'ranking' => array($player['vSingles'], $player['vDoubles'],$player['vMixed'])));
	}
	
	//Create event for this request
	logEvent('teamselect',$teamName);
	
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
	
	$players = getDatabase()->all($query);
	$dbloads = getDatabase()->all($queryDBLoad);
	
	$currentClubName="";
	$currentTeam="";
	$clubCounter=-1;
	$teamCounter=-1;
	foreach($players as $key => $player) {	
		if  ($currentClubName != $player['clubName']) {			
			$clubCounter++;
			$teamCounter=-1;
			$currentTeamName="";
			$result["clubs"][$clubCounter]= array("clubName" => $player['clubName'], "teams" =>array());
			$currentClubName = $player['clubName'];
		}
		if ($currentTeamName != $player['teamName']) {
			$teamCounter++;
			$result["clubs"][$clubCounter]["teams"][$teamCounter] = array('teamName' => $player['teamName'],'type' =>$player['type'], 'event' => $player['event'], 'devision' => $player['devision'],'series'=> $player['series'],'captainName'=> $player['captainName'], 'baseTeam' => array());
			$currentTeamName = $player['teamName'];
		}
		array_push($result["clubs"][$clubCounter]["teams"][$teamCounter]["baseTeam"],$player['playerId']);		
	}	
	$result['DBLOAD'] = $dbloads;
	
	
	header("Content-type: application/json");
	//header("Content-type: text/html");
	header("Content-Disposition: attachment; filename=json.data");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo json_encode($result);	
} 

function logEvent($eventType,$who) {
	getDatabase()->execute('INSERT INTO lf_event(eventType, `when`, who) VALUES(:eventType, now(), :who)',
					array(':eventType' => $eventType,
					':who' => $who)
					);		
}

function dbload($doLoad = 'true',$addTestClub = 'false') {
		$PB_COMPETITIE_ID=PB_COMPETITIE_ID;
		$PB_COMPETITIE_START_DAY=PB_COMPETITIE_START_DAY;
		$PB_COMPETITIE_END_DAY=PB_COMPETITIE_END_DAY;
		$PROV_ID=PROV_ID;
		$PROV_GID=PROV_GID;		
		$PROV_USERNAME=PROV_USERNAME;
		$PROV_PWD=PROV_PWD;	

		$USER_AGENT='Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.11) Gecko/20101012 Firefox/3.6.11';
		//Following was valid until 20141113
		//$LOGIN_STRING='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUJNjM0MDYzNDI3ZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAgU9Y3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kUmVtZW1iZXJNZQVDY3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kTG9naW5JbWFnZUJ1dHRvbg%3D%3D&__EVENTVALIDATION=%2FwEdAAk8ZxYRHnvYNT8dqfzKa%2FZYDc%2F5voaaGYfFlFBXi9EGFfyHSpCYj%2BAUNp9bXc20Z5f%2BOtme7httab8IViMP3HjzlRR%2BDpTMHdYiODpnuHxziR2B%2BiwIwJ5fF61AnAcX2%2BwvDdLMdOmJdT7lzlyuo8NCBjrAGg4uwJH4J35FqmwaB97lIlcv0kHWlCdwWozE4w4%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='.$PBO_USERNAME.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='.$PBO_PWD.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen';
		$LOGIN_STRING='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=UUiMxwEx9hHvimyioyqnDsPeGHVhftVJBPzvMyBm5nWccdiBywv3QDxgEVHimBhZLfFxnqMgLpiBa8CEKKa3x6Uhn0LDZHrEMQdVDfhSGLlzrzVwQnCCMgjIrrff1w%2Fns2ZbUOIqxYB%2BuyKbAcZX1yj1sTbXns%2FWneHaUeug74iw2Xhl%2BXeX%2BPsSZFtEDRn6g50dG%2FMSqWd69WRYyhOEgAy6Yit%2FHOph0ZJ%2BbAW%2FxlZjn370gsyD0w0sPQYsKLtSUQAddNs449CLeUxVmAoz62w3z6FS0Wo3SxN3IeVJ7CR7bdS0&__VIEWSTATEGENERATOR=625BA342&__EVENTVALIDATION=esjTYL6h5M6mOKWnx8EE9ZzO6FcwluwV6dQ6fO6I3XARrWQgvo3eJFvsbWtvibhiVdclIwNz85bH%2FmRomytK6rQZ%2F4eCGyogZvZIRGOi8SHThiDianeeT5xtK0p1F1Ohu%2FSzhOt6p11cJVZHV2qLM1c5iHs%2BYImLY2TMjUs%2FFGmUEreinSxoisZiGr5OgmYJOJOJrwDU8nPW4fEe%2FYsg%2B%2B2kT14i%2B56o4F7teXtsyQvIEBVrePxYhOncwTXw6XQf4962vg%3D%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='.$PROV_USERNAME.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='.$PROV_PWD.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen';
		$CLUBS_CSV_URL='https://toernooi.nl/organization/export/group_subgroups_export.aspx?id='.$PROV_ID.'&gid='.$PROV_GID.'&ft=1';
		$TEAMS_CSV_URL='https://toernooi.nl/sport/admin/exportteams.aspx?id='.$PB_COMPETITIE_ID.'&ft=1';
		$PLAYERS_CSV_URL='https://toernooi.nl/organization/export/export_memberperroletypepergroup.aspx?id='.$PROV_ID.'&gid='.$PROV_GID.'&ft=1&glid=1';		
		$MATCHES_CSV_URL='https://toernooi.nl/sport/admin/exportteammatches.aspx?id='.$PB_COMPETITIE_ID.'&ft=1&sd='.$PB_COMPETITIE_START_DAY.'000000&ed='.$PB_COMPETITIE_END_DAY.'000000';
		
		$BASETEAM_CSV_URL=SITE_ROOT.'/data/fixed/basisopstellingen.csv';
		$FIXED_RANKING_CSV_URL=SITE_ROOT.'/data/fixed/indexen_spelers_01052014.csv';
		$LIGA_BASETEAM_CSV_URL=SITE_ROOT.'/data/fixed/liga_basisopstelling_gemengd_20142015.csv';
		
        // create curl resource
        $ch = curl_init();        

        // set url
        curl_setopt($ch, CURLOPT_URL, "https://toernooi.nl/member/login.aspx");
        curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
        //curl_setopt($ch, CURLOPT_POST, TRUE);        
        if (PHP_VERSION_ID > 50500) {
			curl_setopt($ch, CURLOPT_SAFE_UPLOAD, TRUE);//PHP5.5 only option                    
		}	
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

		//Download BASISOPSTELLING CSV
        curl_setopt($ch, CURLOPT_URL, $BASETEAM_CSV_URL);
        $baseTeamCSV = curl_exec($ch);

		//Download Fixed Rankings (15 may) CSV
        curl_setopt($ch, CURLOPT_URL, $FIXED_RANKING_CSV_URL);
        $fixedRankingCSV = curl_exec($ch);

		//Download Liga BASISOPSTELLING CSV
        curl_setopt($ch, CURLOPT_URL, $LIGA_BASETEAM_CSV_URL);
        $ligaBaseTeamCSV = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);   

		if($addTestClub == 'true' ){		
			$clubCSV .= 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;40001;TESTCLUB BC;;;;;;;;;;;;;;;'."\n";

			
$testTeams = <<<'EOD'
113198;16;;;TESTCLUB 1H;Kap 1H;;;;;;;;;;;;;;;;40001;;Heren Competitie;;1e provinciale;
113198;16;;;TESTCLUB 2H;Kap 2H;;;;;;;;;;;;;;;;40001;;Heren Competitie;;2e provinciale A;
113198;16;;;TESTCLUB 3H;Kap 3H;;;;;;;;;;;;;;;;40001;;Heren Competitie;;2e provinciale B;
113198;16;;;TESTCLUB 2G;Kap 2G;;;;;;;;;;;;;;;;40001;;Gemengde Competitie;;1e provinciale;
113198;16;;;TESTCLUB 2D;Kap 2D;;;;;;;;;;;;;;;;40001;;Dames Competitie;;1e provinciale;
113198;16;;;TESTCLUB 3D;Kap 3D;;;;;;;;;;;;;;;;40001;;Dames Competitie;;2e provinciale;
EOD;
			$teamsCSV .=$testTeams."\n";

$testMatches = <<<'EOD'
113198;7000001;;;;;;;;TESTCLUB 1H;;TESTCLUB 1H;;10-5-2020 19:45:00;;666;Sporthal TestClub;0;0;0;0;0;0;0;0;;;;;;;false;false;false;false;false;false;false
113198;7000002;;;;;;;;TESTCLUB 2H;;TESTCLUB 2H;;10-5-2020 19:45:00;;666;Sporthal TestClub;0;0;0;0;0;0;0;0;;;;;;;false;false;false;false;false;false;false
113198;7000003;;;;;;;;TESTCLUB 3H;;TESTCLUB 3H;;10-5-2020 19:45:00;;666;Sporthal TestClub;0;0;0;0;0;0;0;0;;;;;;;false;false;false;false;false;false;false
113198;7000004;;;;;;;;TESTCLUB 2G;;TESTCLUB 2G;;10-5-2020 19:45:00;;666;Sporthal TestClub;0;0;0;0;0;0;0;0;;;;;;;false;false;false;false;false;false;false
113198;7000005;;;;;;;;TESTCLUB 2D;;TESTCLUB 2D;;10-5-2020 19:45:00;;666;Sporthal TestClub;0;0;0;0;0;0;0;0;;;;;;;false;false;false;false;false;false;false
113198;7000006;;;;;;;;TESTCLUB 3D;;TESTCLUB 3D;;10-5-2020 19:45:00;;666;Sporthal TestClub;0;0;0;0;0;0;0;0;;;;;;;false;false;false;false;false;false;false
EOD;
			$matchesCSV .=$testMatches."\n";
			
$testPlayers = <<<'EOD'
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000001;man1;;;man1;;;;;;;;M;;;;;;;;;;;;;A;A;A;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000002;man2;;;man2;;;;;;;;M;;;;;;;;;;;;;B1;B2;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000003;man3;;;man3;;;;;;;;M;;;;;;;;;;;;;B2;B2;B2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000004;man4;;;man4;;;;;;;;M;;;;;;;;;;;;;B1;B1;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000005;man5;;;man5;;;;;;;;M;;;;;;;;;;;;;B2;B2;B2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000006;man6;;;man6;;;;;;;;M;;;;;;;;;;;;;C1;C1;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000007;man7;;;man7;;;;;;;;M;;;;;;;;;;;;;C2;C1;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000008;man8;;;man8;;;;;;;;M;;;;;;;;;;;;;C2;C2;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000009;man9;;;man9;;;;;;;;M;;;;;;;;;;;;;D;C2;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000010;man10;;;man10;;;;;;;;M;;;;;;;;;;;;;C1;C1;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000011;man11;;;man11;;;;;;;;M;;;;;;;;;;;;;C2;D;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000012;man12;;;man12;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000013;man13;;;man13;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000014;man14;;;man14;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000015;man15;;;man15;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000016;man16;;;man16;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000017;man17;;;man17;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000018;man18;;;man18;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000019;vrouw1;;;vrouw1;;;;;;;;V;;;;;;;;;;;;;B1;B1;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000020;vrouw2;;;vrouw2;;;;;;;;V;;;;;;;;;;;;;B1;B1;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000021;vrouw3;;;vrouw3;;;;;;;;V;;;;;;;;;;;;;B1;B1;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000022;vrouw4;;;vrouw4;;;;;;;;V;;;;;;;;;;;;;B2;B2;B2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000023;vrouw5;;;vrouw5;;;;;;;;V;;;;;;;;;;;;;C1;C1;C1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000024;vrouw6;;;vrouw6;;;;;;;;V;;;;;;;;;;;;;C1;C1;C1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000025;vrouw7;;;vrouw7;;;;;;;;V;;;;;;;;;;;;;C2;B2;C1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000026;vrouw8;;;vrouw8;;;;;;;;V;;;;;;;;;;;;;C2;C2;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000027;vrouw9;;;vrouw9;;;;;;;;V;;;;;;;;;;;;;C2;D;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000028;vrouw10;;;vrouw10;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000029;vrouw11;;;vrouw11;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000030;vrouw12;;;vrouw12;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000031;vrouw13;;;vrouw13;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000032;vrouw14;;;vrouw14;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000033;vrouw15;;;vrouw15;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000034;vrouw16;;;vrouw16;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000035;vrouw 17;;;vrouw 17;;;;;;;;V;;;;;;;;;;;;;D;D;D;Recreant
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000036;man19;;;man19;;;;;;;;M;;;;;;;;;;;;;B2;B2;B2;Competitiespeler
EOD;
			$playersCSV .=$testPlayers."\n";


$testBaseTeams = <<<'EOD'
70000002,"TESTCLUB 1H"
70000003,"TESTCLUB 1H"
70000007,"TESTCLUB 1H"
70000009,"TESTCLUB 1H"
70000001,"TESTCLUB 2H"
70000012,"TESTCLUB 2H"
70000013,"TESTCLUB 2H"
70000014,"TESTCLUB 2H"
70000005,"TESTCLUB 3H"
70000006,"TESTCLUB 3H"
70000015,"TESTCLUB 3H"
70000016,"TESTCLUB 3H"
70000004,"TESTCLUB 2G"
70000005,"TESTCLUB 2G"
70000021,"TESTCLUB 2G"
70000022,"TESTCLUB 2G"
70000019,"TESTCLUB 2D"
70000022,"TESTCLUB 2D"
70000023,"TESTCLUB 2D"
70000028,"TESTCLUB 2D"
70000034,"TESTCLUB 3D"
70000029,"TESTCLUB 3D"
70000030,"TESTCLUB 3D"
70000031,"TESTCLUB 3D"
EOD;
			$baseTeamCSV .=$testBaseTeams."\n";


$testFixedRankings = <<<'EOD'
70000001;A;A;A
70000002;B1;B1;B1
70000003;B1;B1;B1
70000004;B1;B1;B1
70000005;B2;C1;B2
70000006;C1;C1;B1
70000007;C2;C2;C1
70000008;C1;C2;D
70000009;D;D;C1
70000010;C2;C2;C2
70000011;C2;D;C2
70000012;D;D;D
70000013;D;D;D
70000014;D;D;D
70000015;D;D;D
70000016;D;D;D
70000017;D;D;D
70000018;D;D;D
70000019;B1;B1;B1
70000020;B1;B1;B1
70000021;B1;B1;B1
70000022;B2;B2;B2
70000023;C1;C1;C1
70000024;C1;C1;C1
70000025;C2;B2;C1
70000026;C2;C2;C2
70000027;C2;D;C2
70000028;D;D;D
70000029;D;D;D
70000030;D;D;D
70000031;D;D;D
70000032;D;D;D
70000033;D;D;D
70000034;D;D;D
70000035;D;D;D
70000036;B2;B2;B2
EOD;
			$fixedRankingCSV .=$testFixedRankings."\n";

			
$testLigaBaseTeam = <<<'EOD'
TESTCLUB BC,Gemengd,TESTCLUB 1G,70000002,,,,,,,,
TESTCLUB BC,Gemengd,TESTCLUB 1G,70000003,,,,,,,,
TESTCLUB BC,Gemengd,TESTCLUB 1G,70000019,,,,,,,,
TESTCLUB BC,Gemengd,TESTCLUB 1G,70000023,,,,,,,,
TESTCLUB BC,Dames,TESTCLUB 1D,70000020,,,,,,,,
TESTCLUB BC,Dames,TESTCLUB 1D,70000021,,,,,,,,
TESTCLUB BC,Dames,TESTCLUB 1D,70000032,,,,,,,,
TESTCLUB BC,Dames,TESTCLUB 1D,70000033,,,,,,,,
EOD;
			$ligaBaseTeamCSV .=$testLigaBaseTeam."\n";

			//print($ligaBaseTeamCSV);
			
		}
		
        if($doLoad == 'true') {       
			if (!isValidCSV($clubCSV,"Code;Nummer;Naam;")
			    or !isValidCSV($teamsCSV,"clubcode;clubname;eventname")
			    or !isValidCSV($matchesCSV,"matchid;eventid;eventcode")
			    or !isValidCSV($playersCSV,"groupcode;groupname;code;memberid")
			    or !isValidCSV($baseTeamCSV,"player_playerId,team_teamName")
			    or !isValidCSV($fixedRankingCSV,"Lidnummer;Klassement enkel;Klassement dubbel;Klassement gemengd")
			    or !isValidCSV($ligaBaseTeamCSV,"Discipline,Teamnaam,Lidnummer,Voornaam,")) {
				print("NOK: invalid CSV detected");	
			} else {
				cleanDB();        
				getDatabase()->execute("set names latin1");//set to windows encoding
				loadCSV($clubCSV,'clubs');        
				loadCSV($teamsCSV,'teams');
				loadCSV($matchesCSV,'matches');
				loadCSV($playersCSV,'players');
				loadCSV($baseTeamCSV,'baseTeam');
				loadCSV($fixedRankingCSV,'fixedRanking');
				loadCSV($ligaBaseTeamCSV,'ligaBaseTeam');
				logEvent('DBLOAD','SYSTEM');
				print("OK");
			}
		}
}

function isValidCSV($CSV,$firstLineContent) {
	$csvFirstLine = strtok($CSV, "\n");
	if (strpos($csvFirstLine,$firstLineContent) === false) {
		return false;
	} else {
		return true;
	}
}

function cleanDB() {
	//print("Start cleaning");
	getDatabase()->execute('DELETE from lf_match');
	getDatabase()->execute('DELETE from lf_ranking');
	getDatabase()->execute('DELETE from lf_player_has_team');
	getDatabase()->execute('DELETE from lf_player');
	getDatabase()->execute('DELETE from lf_team');
	getDatabase()->execute('DELETE from lf_club');
	getDatabase()->execute('DELETE from lf_group');
	getDatabase()->execute('DELETE from lf_tmpdbload_teamscsv');
	getDatabase()->execute('DELETE from lf_tmpdbload_playersremoved');
	getDatabase()->execute('DELETE from lf_tmpdbload_playerscsv');
	getDatabase()->execute('DELETE FROM lf_tmpdbload_15mei');
	getDatabase()->execute('DELETE FROM lf_tmpdbload_basisopstellingliga');
}

function loadCSV($CSV,$type) {
	
	$delimiter=';';
	if ($type == 'baseTeam' or $type == 'ligaBaseTeam') {
		$delimiter=',';
	}		
	$parsedCsv = parse_csv($CSV,$delimiter,true,false);		
	//print("Handling CSV".$type);
	$headers = array_flip($parsedCsv[0]);
	
	switch($type) {
			case "clubs": 
				//Workaround nummer that is not a nummer
				for($i = 0, $size = count($parsedCsv)-1; $i < $size; ++$i) {
					if ($parsedCsv[$i][$headers['Nummer']] == '30099-OLD') {						
						array_splice($parsedCsv,$i,1);
						break;
					}					
				}
				buildAndExecQuery($parsedCsv,
					'INSERT INTO lf_club(clubId, clubName, clubCode) VALUES',
					 array('Nummer','Naam','?Code')
				);
				break;
			case "teams": 
				buildAndExecQuery($parsedCsv,
					'INSERT INTO lf_tmpdbload_teamscsv(name, clubCode, eventName, drawName, captainName ) VALUES ',
					 array('name','clubcode','eventname','DrawName','contact')
				);
				break;
			case "matches": 
				buildAndExecQuery($parsedCsv,
					'INSERT INTO lf_match(homeTeamName, outTeamName, locationId, locationName, matchId, date) VALUES ',
					 array('team1name','team2name','locationid','locationname','matchid','plannedtime'),
					 '(?, ?, ?, ?, ?, str_to_date(?, \'%e-%c-%Y %H:%i:%S\'))'
				);
				break;						
			case "players": 
				buildAndExecQuery($parsedCsv,
					'INSERT INTO lf_tmpdbload_playerscsv(memberId,firstName,lastName,gender,groupName,playerLevelSingle,playerLevelDouble,playerLevelMixed,typeName,role,groupCode) VALUES ',
					 array('memberid','firstname','lastname','gender','groupname','PlayerLevelSingle','PlayerLevelDouble','PlayerLevelMixed','TypeName','role','?groupcode')
				);
				break;					
			case "baseTeam": 
				buildAndExecQuery($parsedCsv,
					'INSERT INTO lf_player_has_team(player_playerId, team_teamName) VALUES ',
					 array('player_playerId','team_teamName')
				);
				break;			
			case "fixedRanking": 
				buildAndExecQuery($parsedCsv,
					'INSERT INTO lf_tmpdbload_15mei(playerId, playerLevelSingle, playerLevelDouble, playerLevelMixed) VALUES ',
					 array('Lidnummer','Klassement enkel','Klassement dubbel','Klassement gemengd')
				);
				break;								
			case "ligaBaseTeam": 
				buildAndExecQuery($parsedCsv,
					'INSERT INTO lf_tmpdbload_basisopstellingliga(playerId, teamName, discipline, clubName) VALUES',
					 array('Lidnummer','Teamnaam','Discipline','Club')
				);
				break;						
	}

$updateLfYear = <<<'EOD'
update lf_tmpdbload_teamscsv set year=2014;
EOD;
$insertLfGroup = <<<'EOD'
INSERT INTO lf_group (tournament,`type`,event,devision,series)
select `year`,'PROV',lf_dbload_eventcode(eventName),lf_dbload_devision(drawName),lf_dbload_serie(drawName) from lf_tmpdbload_teamscsv
group by `year`,lf_dbload_eventcode(eventName),lf_dbload_devision(drawName),lf_dbload_serie(drawName);
EOD;
$insertLfTeam = <<<'EOD'
INSERT INTO lf_team (teamName,sequenceNumber,club_clubId, group_groupId, captainName)
select name,lf_dbload_teamSequenceNumber(name),clubCode,(select groupId from lf_group lfg where lfg.tournament = t.`year` and lf_dbload_eventcode(t.eventName) = lfg.event and  lf_dbload_devision(t.drawName) = lfg.devision and lf_dbload_serie(t.drawName) = lfg.series),t.captainName  from lf_tmpdbload_teamscsv t;
EOD;
$deleteLfTmpdbloadPlayersWhenDuplicatePrefereSpelerAboveKYUSpeler = <<<'EOD'
delete n2 from lf_tmpdbload_playerscsv n1, lf_tmpdbload_playerscsv n2
where n1.memberId = n2.memberId
and n1.role='Speler' and n2.role like 'KYU%'	
EOD;
$deleteLfTmpdbloadPlayersWhenDuplicatePrefereCompetitieSpelerAboveJeugd = <<<'EOD'
delete n2 from lf_tmpdbload_playerscsv n1, lf_tmpdbload_playerscsv n2
where n1.memberId = n2.memberId
and n1.typeName='Competitiespeler' and n2.typeName = 'Jeugd'
EOD;
$deleteLfTmpdbloadPlayersWhenDuplicatePrefereUitgeleendeSpelerAboveSpeler = <<<'EOD'
delete n2 from lf_tmpdbload_playerscsv n1, lf_tmpdbload_playerscsv n2
where n1.memberId = n2.memberId
and n1.role='Uitgeleende speler' and n2.role = 'Speler'	
EOD;
$deleteLfTmpdbloadPlayersWhenDuplicateJustPickAndChoose = <<<'EOD'
delete n2 from lf_tmpdbload_playerscsv n1, lf_tmpdbload_playerscsv n2
where n1.memberId = n2.memberId
and n2.id > n1.id
EOD;
$insertLfRanking = <<<'EOD'
INSERT INTO lf_ranking (`date`,singles,doubles,mixed,player_playerId)
select SYSDATE(),t.playerLevelSingle,t.playerLevelDouble,t.playerLevelMixed,t.memberId from lf_tmpdbload_playerscsv t
join lf_club c on c.clubName=t.groupName;
EOD;
$insertLfPlayer = <<<'EOD'
INSERT INTO lf_player (playerId,firstName,lastName,gender,club_clubId,type)
select t.memberId,t.firstName,t.lastName, CASE when t.gender='V' then 'F' else t.gender END,c.clubId, case when t.typeName like 'Recreant%' then 'R' when t.typeName like 'Competitie%' then 'C' when t.typeName like 'Jeugd%' then 'J' END from lf_tmpdbload_playerscsv t
join lf_club c on c.clubCode=t.groupCode;			
EOD;
$insertLfRankingFixed = <<<'EOD'
insert into lf_ranking(date,singles,doubles,mixed,player_playerId)
select '2014-05-15',t.playerLevelSingle,t.playerLevelDouble,t.playerLevelMixed,t.playerId from lf_tmpdbload_15mei t
join lf_player p on t.playerId = p.playerId;
EOD;

$insertFakeLigaGroup = <<<'EOD'
INSERT INTO lf_group (tournament,`type`,event,devision) values ('2014','LIGA','MX',0),('2014','LIGA','M',0),('2014','LIGA','L',0);
EOD;
$insertLfTeamLiga = <<<'EOD'
INSERT INTO lf_team (teamName,sequenceNumber,club_clubId, group_groupId)
select t.teamName,lf_dbload_teamSequenceNumber(t.teamName),c.clubId,(select groupId from lf_group where `type`='LIGA' and event=lf_dbload_eventcode(t.Discipline)) from lf_tmpdbload_basisopstellingliga t
join lf_club c on c.clubName = t.clubName
group by t.teamName,c.clubId;
EOD;
$insertLfPlayerHasTeamLiga = <<<'EOD'
INSERT INTO lf_player_has_team(player_playerId,team_teamName)
select t.playerId, t.teamName from lf_tmpdbload_basisopstellingliga t
join lf_team lft on lft.teamName = t.teamName;
EOD;
$insertLfBaseTeamAddMissingPlayersStep1 = <<<'EOD'
INSERT INTO lf_tmpdbload_playersremoved(playerId,gender,club_clubid)
SELECT pht.player_playerId,
CASE
	when lf_dbload_teamType(t.teamName) = 'H' then 'M'
	when lf_dbload_teamType(t.teamName) = 'D' then 'F'
	when lf_dbload_genderCount(t.teamName,'F') < 2 and lf_dbload_genderCount(t.teamName,'M') = 2 then 'F'
	when lf_dbload_genderCount(t.teamName,'M') < 2 and lf_dbload_genderCount(t.teamName,'F') = 2 then 'M'
	else 'X'
END,
t.club_clubId FROM `lf_player_has_team` pht 
join lf_team t on t.teamName = pht.team_teamName
where pht.player_playerId not in (select playerId from lf_player);
EOD;
$insertLfBaseTeamAddMissingPlayersStep2 = <<<'EOD'
INSERT INTO lf_player(playerId,firstName,lastName,gender,club_clubid,type)
select removeplayer.playerId,
CASE
	when removeplayer.gender_best_attempt = 'X' then 'UNKNOWNGENDER'
	else 'UNKNOWN'
END,
'UNKNOWN',
CASE
	when removeplayer.gender_best_attempt = 'X' then 'F'
	else removeplayer.gender_best_attempt
END,
removeplayer.club_clubId,
'C'
 from (
select playerId,club_clubId,min(gender) gender_best_attempt from lf_tmpdbload_playersremoved
group by playerId,club_clubId
) as removeplayer
EOD;

	switch($type) {
		case "teams": 
			 getDatabase()->execute($updateLfYear);
			 getDatabase()->execute($insertLfGroup);
			 getDatabase()->execute($insertLfTeam);
			break;
		case "players":
			// When player is from O-Vl Club and rented to another O-Vl it will appear twice. However, we only want to keep the record with role='Speler'
			// Some tricks needed to avoid mysql limitation: In MySQL, you can't modify the same table which you use in the SELECT part
			// http://stackoverflow.com/questions/45494/mysql-error-1093-cant-specify-target-table-for-update-in-from-clause
			getDatabase()->execute($deleteLfTmpdbloadPlayersWhenDuplicatePrefereSpelerAboveKYUSpeler);
			getDatabase()->execute($deleteLfTmpdbloadPlayersWhenDuplicatePrefereCompetitieSpelerAboveJeugd);
			getDatabase()->execute($deleteLfTmpdbloadPlayersWhenDuplicatePrefereUitgeleendeSpelerAboveSpeler);
			getDatabase()->execute($deleteLfTmpdbloadPlayersWhenDuplicateJustPickAndChoose);

			//When must import players from type=Recreant too because they can be part of a baseTeam!
			getDatabase()->execute($insertLfPlayer);						
			
			getDatabase()->execute($insertLfRanking);			
			break;
		case "baseTeam":
			getDatabase()->execute($insertLfBaseTeamAddMissingPlayersStep1);			
			getDatabase()->execute($insertLfBaseTeamAddMissingPlayersStep2);
			break;		
		case "fixedRanking": 
			 getDatabase()->execute($insertLfRankingFixed);
			break;
		case "ligaBaseTeam": 
			 getDatabase()->execute($insertFakeLigaGroup);
			 getDatabase()->execute($insertLfTeamLiga);
			 getDatabase()->execute($insertLfPlayerHasTeamLiga);			 
			break;
	}	
	
	
		
	//print_r($parsedCsv);
}

function buildAndExecQuery($parsedCsv, $queryStart,$columnsToSelect,$qPreparedRecord = "") {
		//2014/12/04 For import performance reasons, its a lot faster to import using a single (big) query than one by one.		
		//$query = "INSERT INTO lf_tmpdbload_15mei(playerId, playerLevelSingle, playerLevelDouble, playerLevelMixed) VALUES "; //Prequery
		//$columnsToSelect = array('Lidnummer','Klassement enkel','Klassement dubbel','Klassement gemengd');
		
		$headers = array_flip($parsedCsv[0]);
		$query = $queryStart;
		//Build up all prepared values (?,?,?,?,...) , (?,?,?,?,...),...
		if (empty($qPreparedRecord)) {
			$qPreparedRecord = '(' . implode(",",array_fill(0, count($columnsToSelect), "?")) . ')';
		}
		$qPreparedRecords = array_fill(0, count($parsedCsv)-2, $qPreparedRecord);
		$query .=  implode(",",$qPreparedRecords);
		
		//Build up all bind parameters
		$bindParams = array();				
		for($i = 1, $size = count($parsedCsv)-1; $i < $size; ++$i) {
			for ($j=0, $numberOfColumns = count($columnsToSelect); $j < $numberOfColumns; ++$j) {
				$bindParams[] = $parsedCsv[$i][$headers[$columnsToSelect[$j]]];
			}
		}
		
		getDatabase()->execute($query,$bindParams);
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

function statistic($statType) {

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
			 $result = getDatabase()->all($queryTotalSelectAndPrintCmdPerTeam);
			break;
		case "totalSelectAndPrintCmdPerWeek": 			
			$result=array();
			$result1=getDatabase()->all($queryTotalSelectAndPrintCmdPerWeek);
			$result2=getDatabase()->all($queryTotalCombinedSelectAndPrintCmdPerWeek);
			$result3=getDatabase()->all($queryTotalPerWeek);
						
			foreach($result1 as $key => $totalSelect) {				
				$totalCombined='0';
				foreach($result2 as $key => $combinedPerWeek) {
					if ($totalSelect['week'] == $combinedPerWeek['week']) {
						$totalCombined = $combinedPerWeek['combined'];
					}
				}
				$totalSelect['combined']=$totalCombined;

				$totalMatches='0';
				foreach($result3 as $key => $totalPerWeek) {
					if ($totalSelect['week'] == $totalPerWeek['week']) {
						$totalMatches = $totalPerWeek['matches'];
					}
				}
				$totalSelect['totalMatchesX2']=$totalMatches;
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
