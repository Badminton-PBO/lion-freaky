<?php
mb_internal_encoding("UTF-8");
include_once './libs/epiphany-20130912/Epi.php';
include_once './config.php';
Epi::setPath('base', './libs/epiphany-20130912');
Epi::init('route','database');
EpiDatabase::employ('mysql',constant('DB_NAME'),constant('DB_HOST'),constant('DB_USER'),constant('DB_PASSWORD')); // type = mysql, database = mysql, host = localhost, user = root, password = [empty]
Epi::setSetting('exceptions', false);

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
getRoute()->get('/teamAndClubPlayers/([\w\s]+)','teamAndClubPlayers');
getRoute()->get('/dbload','dbload');
getRoute()->get('/dbload/(\w+)/(\w+)','dbload');
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
and (
	p.type='C'
	OR
	p.playerId in (select player_playerId from lf_player_has_team)
)
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
	
	//Create event for this request
	getDatabase()->execute('INSERT INTO lf_event(eventType, `when`, who) VALUES(:eventType, now(), :who)',
					array(':eventType' => 'teamselect',
					':who' => $teamName)
					);	
	
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
select c.clubName,t.teamName,g.event,g.`type`,g.devision,g.series,p.playerId from lf_club c 
join lf_team t on c.clubId = t.club_clubId 
join lf_group g on g.groupId = t.group_groupId 
join lf_player_has_team pt on pt.team_teamName = t.teamName 
join lf_player p on p.playerId = pt.player_playerId 
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
			$result[$clubCounter]["teams"][$teamCounter] = array('teamName' => $player['teamName'],'type' =>$player['type'], 'event' => $player['event'], 'devision' => $player['devision'],'series'=> $player['series'], 'baseTeam' => array());
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

function dbload($doLoad = 'true',$addTestClub = 'false') {
		$PBO_COMPETITIE_ID='EF6D253B-4410-4B4F-883D-48A61DDA350D';
		$PBO_COMPETITIE_START_DAY='20140801';
		$PBO_COMPETITIE_END_DAY='20150731';
		$PBO_OVL_ID='638D0B55-C39B-43AB-8A9D-D50D62017FBE';
		$PBO_OVL_GID='3825E3C5-1371-4FF6-94AF-C4A3B152802A';		
		$PBO_USERNAME='g0ldh0rn';
		$PBO_PWD='Hgkxj8DgwE';	

		$USER_AGENT='Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.11) Gecko/20101012 Firefox/3.6.11';
		//Following was valid until 20141113
		//$LOGIN_STRING='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUJNjM0MDYzNDI3ZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAgU9Y3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kUmVtZW1iZXJNZQVDY3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kTG9naW5JbWFnZUJ1dHRvbg%3D%3D&__EVENTVALIDATION=%2FwEdAAk8ZxYRHnvYNT8dqfzKa%2FZYDc%2F5voaaGYfFlFBXi9EGFfyHSpCYj%2BAUNp9bXc20Z5f%2BOtme7httab8IViMP3HjzlRR%2BDpTMHdYiODpnuHxziR2B%2BiwIwJ5fF61AnAcX2%2BwvDdLMdOmJdT7lzlyuo8NCBjrAGg4uwJH4J35FqmwaB97lIlcv0kHWlCdwWozE4w4%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='.$PBO_USERNAME.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='.$PBO_PWD.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen';
		$LOGIN_STRING='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUJNjM0MDYzNDI3ZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAgU9Y3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kUmVtZW1iZXJNZQVDY3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kTG9naW5JbWFnZUJ1dHRvbkDFcxzmupMNoFNI2833VjIpspSb&__VIEWSTATEGENERATOR=625BA342&__EVENTVALIDATION=%2FwEdAAkkrxhVeFemLbIU82wv5PSCDc%2F5voaaGYfFlFBXi9EGFfyHSpCYj%2BAUNp9bXc20Z5f%2BOtme7httab8IViMP3HjzlRR%2BDpTMHdYiODpnuHxziR2B%2BiwIwJ5fF61AnAcX2%2BwvDdLMdOmJdT7lzlyuo8NCBjrAGg4uwJH4J35FqmwaB97lIlcv0kHWlCdwWozE4w6e5YuDNp%2F7v5Hoe%2Fq7l8Xai2IOSg%3D%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='.$PBO_USERNAME.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='.$PBO_PWD.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen';
		$CLUBS_CSV_URL='http://toernooi.nl/sport/admin/exportclubs.aspx?id='.$PBO_COMPETITIE_ID.'&ft=1';
		$TEAMS_CSV_URL='http://toernooi.nl/sport/admin/exportteams.aspx?id='.$PBO_COMPETITIE_ID.'&ft=1';
		$PLAYERS_CSV_URL='http://toernooi.nl/organization/export/export_memberperroletypepergroup.aspx?id='.$PBO_OVL_ID.'&gid='.$PBO_OVL_GID.'&ft=1&glid=1';		
		$MATCHES_CSV_URL='http://toernooi.nl/sport/admin/exportteammatches.aspx?id='.$PBO_COMPETITIE_ID.'&ft=1&sd='.$PBO_COMPETITIE_START_DAY.'000000&ed='.$PBO_COMPETITIE_END_DAY.'000000';
		
		$BASETEAM_CSV_URL=SITE_ROOT.'/data/fixed/basisopstellingen.csv';
		$FIXED_RANKING_CSV_URL=SITE_ROOT.'/data/fixed/indexen_spelers_01052014_OVL.csv';
		$LIGA_BASETEAM_CSV_URL=SITE_ROOT.'/data/fixed/liga_basisopstelling_gemengd_20142015.csv';
		
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
			$clubCSV .= '40001;TESTCLUB BC;;;;;;;;;;;;;;;;'."\n";
			
			
$testTeams = <<<'EOD'
113198;16;;;TESTCLUB 1H;;;;;;;;;;;;;;;;;40001;;Heren Competitie;;1e provinciale;
113198;16;;;TESTCLUB 2H;;;;;;;;;;;;;;;;;40001;;Heren Competitie;;2e provinciale A;
113198;16;;;TESTCLUB 3H;;;;;;;;;;;;;;;;;40001;;Heren Competitie;;2e provinciale B;
113198;16;;;TESTCLUB 2G;;;;;;;;;;;;;;;;;40001;;Gemengde Competitie;;1e provinciale;
113198;16;;;TESTCLUB 2D;;;;;;;;;;;;;;;;;40001;;Dames Competitie;;1e provinciale;
113198;16;;;TESTCLUB 3D;;;;;;;;;;;;;;;;;40001;;Dames Competitie;;2e provinciale;
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
x;TESTCLUB BC;;70000001;man1;;;man1;;;;;;;;M;;;;;;;;;;;;;A;A;A;Competitiespeler
x;TESTCLUB BC;;70000002;man2;;;man2;;;;;;;;M;;;;;;;;;;;;;B1;B2;B1;Competitiespeler
x;TESTCLUB BC;;70000003;man3;;;man3;;;;;;;;M;;;;;;;;;;;;;B2;B2;B2;Competitiespeler
x;TESTCLUB BC;;70000004;man4;;;man4;;;;;;;;M;;;;;;;;;;;;;B1;B1;B1;Competitiespeler
x;TESTCLUB BC;;70000005;man5;;;man5;;;;;;;;M;;;;;;;;;;;;;B2;B2;B2;Competitiespeler
x;TESTCLUB BC;;70000006;man6;;;man6;;;;;;;;M;;;;;;;;;;;;;C1;C1;B1;Competitiespeler
x;TESTCLUB BC;;70000007;man7;;;man7;;;;;;;;M;;;;;;;;;;;;;C2;C1;C2;Competitiespeler
x;TESTCLUB BC;;70000008;man8;;;man8;;;;;;;;M;;;;;;;;;;;;;C2;C2;C2;Competitiespeler
x;TESTCLUB BC;;70000009;man9;;;man9;;;;;;;;M;;;;;;;;;;;;;D;C2;C2;Competitiespeler
x;TESTCLUB BC;;70000010;man10;;;man10;;;;;;;;M;;;;;;;;;;;;;C1;C1;D;Competitiespeler
x;TESTCLUB BC;;70000011;man11;;;man11;;;;;;;;M;;;;;;;;;;;;;C2;D;C2;Competitiespeler
x;TESTCLUB BC;;70000012;man12;;;man12;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000013;man13;;;man13;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000014;man14;;;man14;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000015;man15;;;man15;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000016;man16;;;man16;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000017;man17;;;man17;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000018;man18;;;man18;;;;;;;;M;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000019;vrouw1;;;vrouw1;;;;;;;;V;;;;;;;;;;;;;B1;B1;B1;Competitiespeler
x;TESTCLUB BC;;70000020;vrouw2;;;vrouw2;;;;;;;;V;;;;;;;;;;;;;B1;B1;B1;Competitiespeler
x;TESTCLUB BC;;70000021;vrouw3;;;vrouw3;;;;;;;;V;;;;;;;;;;;;;B1;B1;B1;Competitiespeler
x;TESTCLUB BC;;70000022;vrouw4;;;vrouw4;;;;;;;;V;;;;;;;;;;;;;B2;B2;B2;Competitiespeler
x;TESTCLUB BC;;70000023;vrouw5;;;vrouw5;;;;;;;;V;;;;;;;;;;;;;C1;C1;C1;Competitiespeler
x;TESTCLUB BC;;70000024;vrouw6;;;vrouw6;;;;;;;;V;;;;;;;;;;;;;C1;C1;C1;Competitiespeler
x;TESTCLUB BC;;70000025;vrouw7;;;vrouw7;;;;;;;;V;;;;;;;;;;;;;C2;B2;C1;Competitiespeler
x;TESTCLUB BC;;70000026;vrouw8;;;vrouw8;;;;;;;;V;;;;;;;;;;;;;C2;C2;C2;Competitiespeler
x;TESTCLUB BC;;70000027;vrouw9;;;vrouw9;;;;;;;;V;;;;;;;;;;;;;C2;D;C2;Competitiespeler
x;TESTCLUB BC;;70000028;vrouw10;;;vrouw10;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000029;vrouw11;;;vrouw11;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000030;vrouw12;;;vrouw12;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000031;vrouw13;;;vrouw13;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000032;vrouw14;;;vrouw14;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000033;vrouw15;;;vrouw15;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000034;vrouw16;;;vrouw16;;;;;;;;V;;;;;;;;;;;;;D;D;D;Competitiespeler
x;TESTCLUB BC;;70000035;vrouw 17;;;vrouw 17;;;;;;;;V;;;;;;;;;;;;;D;D;D;Recreant
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
70000005;B2;B2;B2
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
			cleanDB();        
			getDatabase()->execute("set names latin1");//set to windows encoding
			loadCSV($clubCSV,'clubs');        
			loadCSV($teamsCSV,'teams');
			loadCSV($matchesCSV,'matches');
			loadCSV($playersCSV,'players');
			loadCSV($baseTeamCSV,'baseTeam');
			loadCSV($fixedRankingCSV,'fixedRanking');
			loadCSV($ligaBaseTeamCSV,'ligaBaseTeam');
		}
		
        print("OK");
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
				case "baseTeam": getDatabase()->execute('INSERT INTO lf_player_has_team(player_playerId, team_teamName) VALUES(:playerId, :teamName)',
					array(':playerId' => $parsedCsv[$i][$headers['player_playerId']],
					':teamName' => $parsedCsv[$i][$headers['team_teamName']])
					);
					break;
				case "fixedRanking": getDatabase()->execute('INSERT INTO lf_tmpdbload_15mei(playerId, playerLevelSingle, playerLevelDouble, playerLevelMixed) VALUES(:playerId, :playerLevelSingle, :playerLevelDouble, :playerLevelMixed)', 
					array(':playerId' => $parsedCsv[$i][$headers['Lidnummer']],
					 ':playerLevelSingle' => $parsedCsv[$i][$headers['Klassement enkel']], 
					 ':playerLevelDouble' => $parsedCsv[$i][$headers['Klassement dubbel']], 
					 ':playerLevelMixed' => $parsedCsv[$i][$headers['Klassement gemengd']])
					 );
					break;										
				case "ligaBaseTeam": getDatabase()->execute('INSERT INTO lf_tmpdbload_basisopstellingliga(playerId, teamName, discipline, clubName) VALUES(:playerId, :teamName,:discipline, :clubName)', 
					array(':playerId' => $parsedCsv[$i][$headers['Lidnummer']],
					 ':teamName' => $parsedCsv[$i][$headers['Teamnaam']], 
					 ':discipline' => $parsedCsv[$i][$headers['Discipline']], 
					 ':clubName' => $parsedCsv[$i][$headers['Club']])
					 );
					break;												
		}			
	}	

$insertLfGroup = <<<'EOD'
INSERT INTO lf_group (tournament,`type`,event,devision,series)
select `year`,'PROV',lf_dbload_eventcode(eventName),lf_dbload_devision(drawName),lf_dbload_serie(drawName) from lf_tmpdbload_teamscsv
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
