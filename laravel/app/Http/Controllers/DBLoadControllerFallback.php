<?php namespace App\Http\Controllers;

use App\Http\Requests;
use DB;
use Mail;

class DBLoadControllerFallback extends Controller {

    public function dbload($doLoad = 'true',$addTestClub = 'false') {
        ini_set('memory_limit', '256M');
        set_time_limit(120);
        $PB_COMPETITIE_ID=env('PB_COMPETITIE_ID', '');
        $PB_COMPETITIE_START_DAY=env('PB_COMPETITIE_START_DAY','');
        $PB_COMPETITIE_END_DAY=env('PB_COMPETITIE_END_DAY','');
        $PROV_ID=env('PROV_ID','');
        $PROV_GID=env('PROV_GID','');
        $PROV_USERNAME=env('PROV_USERNAME','');
        $PROV_PWD=env('PROV_PWD','');

        $USER_AGENT='Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.11) Gecko/20101012 Firefox/3.6.11';
        $CLUBS_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/2015-2016/fallback/clubs.csv';
        $TEAMS_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/2015-2016/fallback/teams.csv';
        $PLAYERS_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/2015-2016/fallback/players.csv';
        $MATCHES_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/2015-2016/fallback/matches.csv';
        $LOCATIONS_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/2015-2016/fallback/locations.csv';

        $BASETEAM_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/2015-2016/basisopstellingen.csv';
        $FIXED_RANKING_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/2015-2016/indexen_spelers.csv';
        $LIGA_BASETEAM_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/2015-2016/liga_nationale_basisopstelling.csv';

        // create curl resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, "https://www.toernooi.nl/member/login.aspx");
        curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
        //curl_setopt($ch, CURLOPT_POST, TRUE);
        if (PHP_VERSION_ID > 50500) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, TRUE);//PHP5.5 only option
        };
        //return all http header and cookies
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);

        //Ready to download csv files
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, FALSE);

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

        //Download locations CSV
        curl_setopt($ch, CURLOPT_URL,$LOCATIONS_CSV_URL);
        $locationsCSV = curl_exec($ch);

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
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000001;man1;;;man1;;;;;;;;M;;;;;;;;;;;;Speler;A;A;A;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000002;man2;;;man2;;;;;;;;M;;;;;;;;;;;;Speler;B1;B2;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000003;man3;;;man3;;;;;;;;M;;;;;;;;;;;;Speler;B2;B2;B2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000004;man4;;;man4;;;;;;;;M;;;;;;;;;;;;Speler;B1;B1;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000005;man5;;;man5;;;;;;;;M;;;;;;;;;;;;Speler;B2;B2;B2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000006;man6;;;man6;;;;;;;;M;;;;;;;;;;;;Speler;C1;C1;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000007;man7;;;man7;;;;;;;;M;;;;;;;;;;;;Speler;C2;C1;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000008;man8;;;man8;;;;;;;;M;;;;;;;;;;;;Speler;C2;C2;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000009;man9;;;man9;;;;;;;;M;;;;;;;;;;;;Speler;D;C2;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000010;man10;;;man10;;;;;;;;M;;;;;;;;;;;;Speler;C1;C1;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000011;man11;;;man11;;;;;;;;M;;;;;;;;;;;;Speler;C2;D;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000012;man12;;;man12;;;;;;;;M;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000013;man13;;;man13;;;;;;;;M;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000014;man14;;;man14;;;;;;;;M;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000015;man15;;;man15;;;;;;;;M;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000016;man16;;;man16;;;;;;;;M;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000017;man17;;;man17;;;;;;;;M;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000018;man18;;;man18;;;;;;;;M;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000019;vrouw1;;;vrouw1;;;;;;;;V;;;;;;;;;;;;Speler;B1;B1;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000020;vrouw2;;;vrouw2;;;;;;;;V;;;;;;;;;;;;Speler;B1;B1;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000021;vrouw3;;;vrouw3;;;;;;;;V;;;;;;;;;;;;Speler;B1;B1;B1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000022;vrouw4;;;vrouw4;;;;;;;;V;;;;;;;;;;;;Speler;B2;B2;B2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000023;vrouw5;;;vrouw5;;;;;;;;V;;;;;;;;;;;;Speler;C1;C1;C1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000024;vrouw6;;;vrouw6;;;;;;;;V;;;;;;;;;;;;Speler;C1;C1;C1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000025;vrouw7;;;vrouw7;;;;;;;;V;;;;;;;;;;;;Speler;C2;B2;C1;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000026;vrouw8;;;vrouw8;;;;;;;;V;;;;;;;;;;;;Speler;C2;C2;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000027;vrouw9;;;vrouw9;;;;;;;;V;;;;;;;;;;;;Speler;C2;D;C2;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000028;vrouw10;;;vrouw10;;;;;;;;V;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000029;vrouw11;;;vrouw11;;;;;;;;V;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000030;vrouw12;;;vrouw12;;;;;;;;V;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000031;vrouw13;;;vrouw13;;;;;;;;V;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000032;vrouw14;;;vrouw14;;;;;;;;V;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000033;vrouw15;;;vrouw15;;;;;;;;V;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000034;vrouw16;;;vrouw16;;;;;;;;V;;;;;;;;;;;;Speler;D;D;D;Competitiespeler
XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX;TESTCLUB BC;;70000035;vrouw 17;;;vrouw 17;;;;;;;;V;;;;;;;;;;;;Speler;D;D;D;Recreant
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
            if (!DBLoadController::isValidCSV($clubCSV,"Code;Nummer;Naam;")
                or !DBLoadController::isValidCSV($teamsCSV,"clubcode;clubname;eventname")
                or !DBLoadController::isValidCSV($matchesCSV,"matchid;eventid;eventcode")
                or !DBLoadController::isValidCSV($playersCSV,"groupcode;groupname;code;memberid")
                or !DBLoadController::isValidCSV($baseTeamCSV,"player_playerId,team_teamName")
                or !DBLoadController::isValidCSV($fixedRankingCSV,"Lidnummer;Klassement enkel;Klassement dubbel;Klassement gemengd")
                or !DBLoadController::isValidCSV($locationsCSV,"code;name;number;contact;address;postalcode")
                or !DBLoadController::isValidCSV($ligaBaseTeamCSV,"player_playerId,team_teamName,club_clubName")) {

                print("NOK: invalid CSV detected");
            } else {
                DBLoadController::cleanDB();
                DB::statement("set names latin1");//set to windows encoding
                DBLoadController::loadCSV($clubCSV,'clubs');
                DBLoadController::loadCSV($teamsCSV,'teams');
                DBLoadController::loadCSV($matchesCSV,'matches');
                DBLoadController::loadCSV($playersCSV,'players');
                DBLoadController::loadCSV($baseTeamCSV,'baseTeam');
                DBLoadController::loadCSV($fixedRankingCSV,'fixedRanking');
                DBLoadController::loadCSV($ligaBaseTeamCSV,'ligaBaseTeam');
                DBLoadController::loadCSV($locationsCSV,'locations');
                EventController::logEvent('DBLOAD','SYSTEM');
                $this->updateMatchCRAccordingNewData();
                print("OK");
            }
        }
    }

    function updateMatchCRAccordingNewData() {
        $matchesInStatusOvereenkomstThatGotMovedQuery = <<<'EOD'
select e.matchIdExtra,
e.hTeamName,
(select t.email from lf_team t where t.teamName=e.hTeamName) hTeamEmail,
e.oTeamName,(select t.email from lf_team t where t.teamName=e.oTeamName) oTeamEmail,
date_format(m.date,'%W %e %M, %H:%i') dateTimeLayout
from lf_match_cr cr
join lf_match_extra e on e.matchIdExtra = cr.match_matchIdExtra
join lf_match m on m.homeTeamName = e.hTeamName and m.outTeamName = e.oTeamName
where e.status='OVEREENKOMST'
and cr.finallyChosen=1
and m.date = cr.proposedDate
EOD;

        $deleteMatchCR = <<<'EOD'
delete from lf_match_cr
where match_matchIdExtra = :matchIdExtra
EOD;

        $updateMatchExtra = <<<'EOD'
update lf_match_extra
set status=null,actionFor=null,hTeamComment=null,oTeamComment=null
where matchIdExtra = :matchIdExtra
EOD;
        DB::statement("SET lc_time_names = 'nl_NL';");//set NL language
        $matchesInStatusOvereenkomstThatGotMoved = DB::select($matchesInStatusOvereenkomstThatGotMovedQuery);

        foreach($matchesInStatusOvereenkomstThatGotMoved as $key => $match) {
            $matchIdExtra = (int)$match->matchIdExtra;

            //Delete lf_match_cr with this matchIdExtra
            DB::statement($deleteMatchCR,array('matchIdExtra'=>$matchIdExtra));

            //update lf_match_extra status=null, actionFor=null
            DB::update($updateMatchExtra,array('matchIdExtra'=>$matchIdExtra));


            $subject= "Verplaatsings aanvraag ".$match->hTeamName." - ".$match->oTeamName. " verwerkt";

            $data = array(
                'subject' => $subject,
                'hTeam' => $match->hTeamName,
                'oTeam' => $match->oTeamName,
                'hTeamEmail' => $match->hTeamEmail,
                'oTeamEmail' => $match->oTeamEmail,
                'dateTimeLayout' => $match->dateTimeLayout);

            Mail::send('emails.verplaatsing-moved', $data, function ($message) use ($data) {
                $message->to(VerplaatsingsController::giveFinalMailto($data['hTeamEmail']), $data['hTeam'])
                    ->to(VerplaatsingsController::giveFinalMailto($data['oTeamEmail']), $data['oTeam'])
                    ->subject($data['subject']);
            });
            EventController::logEvent('v-moved',$data['hTeam']);
            EventController::logEvent('v-moved',$data['oTeam']);
        }
    }

}
