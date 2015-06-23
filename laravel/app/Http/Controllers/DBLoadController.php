<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Mail;

use Illuminate\Http\Request;

class DBLoadController extends Controller {

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

        $USER_AGENT='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 Chrome/41.0.2272.76 Safari/537.36';
        //Following was valid until 20141113
        //$LOGIN_STRING='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUJNjM0MDYzNDI3ZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAgU9Y3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kUmVtZW1iZXJNZQVDY3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kTG9naW5JbWFnZUJ1dHRvbg%3D%3D&__EVENTVALIDATION=%2FwEdAAk8ZxYRHnvYNT8dqfzKa%2FZYDc%2F5voaaGYfFlFBXi9EGFfyHSpCYj%2BAUNp9bXc20Z5f%2BOtme7httab8IViMP3HjzlRR%2BDpTMHdYiODpnuHxziR2B%2BiwIwJ5fF61AnAcX2%2BwvDdLMdOmJdT7lzlyuo8NCBjrAGg4uwJH4J35FqmwaB97lIlcv0kHWlCdwWozE4w4%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='.$PBO_USERNAME.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='.$PBO_PWD.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen';
        //Following was valid unti 20150309
        //$LOGIN_STRING='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUJNjM0MDYzNDI3ZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAgU9Y3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kUmVtZW1iZXJNZQVDY3RsMDAkY3RsMDAkY3RsMDAkY3BoUGFnZSRjcGhQYWdlJGNwaFBhZ2UkcG5sTG9naW4kTG9naW5JbWFnZUJ1dHRvbkDFcxzmupMNoFNI2833VjIpspSb&__VIEWSTATEGENERATOR=625BA342&__EVENTVALIDATION=%2FwEdAAkkrxhVeFemLbIU82wv5PSCDc%2F5voaaGYfFlFBXi9EGFfyHSpCYj%2BAUNp9bXc20Z5f%2BOtme7httab8IViMP3HjzlRR%2BDpTMHdYiODpnuHxziR2B%2BiwIwJ5fF61AnAcX2%2BwvDdLMdOmJdT7lzlyuo8NCBjrAGg4uwJH4J35FqmwaB97lIlcv0kHWlCdwWozE4w6e5YuDNp%2F7v5Hoe%2Fq7l8Xai2IOSg%3D%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='.$PROV_USERNAME.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='.$PROV_PWD.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen';
        //$LOGIN_STRING='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=UUiMxwEx9hHvimyioyqnDsPeGHVhftVJBPzvMyBm5nWccdiBywv3QDxgEVHimBhZLfFxnqMgLpiBa8CEKKa3x6Uhn0LDZHrEMQdVDfhSGLlzrzVwQnCCMgjIrrff1w%2Fns2ZbUOIqxYB%2BuyKbAcZX1yj1sTbXns%2FWneHaUeug74iw2Xhl%2BXeX%2BPsSZFtEDRn6g50dG%2FMSqWd69WRYyhOEgAy6Yit%2FHOph0ZJ%2BbAW%2FxlZjn370gsyD0w0sPQYsKLtSUQAddNs449CLeUxVmAoz62w3z6FS0Wo3SxN3IeVJ7CR7bdS0&__VIEWSTATEGENERATOR=625BA342&__EVENTVALIDATION=esjTYL6h5M6mOKWnx8EE9ZzO6FcwluwV6dQ6fO6I3XARrWQgvo3eJFvsbWtvibhiVdclIwNz85bH%2FmRomytK6rQZ%2F4eCGyogZvZIRGOi8SHThiDianeeT5xtK0p1F1Ohu%2FSzhOt6p11cJVZHV2qLM1c5iHs%2BYImLY2TMjUs%2FFGmUEreinSxoisZiGr5OgmYJOJOJrwDU8nPW4fEe%2FYsg%2B%2B2kT14i%2B56o4F7teXtsyQvIEBVrePxYhOncwTXw6XQf4962vg%3D%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='.$PROV_USERNAME.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='.$PROV_PWD.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen';
        $LOGIN_STRING='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=bVJyNakpPV0f4usZzwuN0RFVXjxNPOBoGtK6FEtcdTPSG444DYCeMxjPP%2BiPDCaICHQ%2F1dZqZeZzeXeywJGGihN0%2Fp8gYzn6OTPl87P4FkPqL58X95uuuiVhxEQX4%2FJWFtdYbgx2D9OTVR0bC5jkB%2FVU116v4UcjRXIqUnmyLwgbSmnnBKWyv6ozI718LKmcj7rg3HPgGbq8Yikllj2288lkzCDxVAJuS9MP%2BbWpLQere1BO8qdTyI10Kh8xT%2FbYdW1DJ2PIKaKG08Hho%2F8ynCX1tyfPnrXkp%2BSY96qgh5749ag3&__VIEWSTATEGENERATOR=625BA342&__EVENTVALIDATION=J835wQEgbd7lzA11VgzazffOVk1r%2BXIF%2Bmh7%2FskUlhLI18g6BjbRZrR39RELNs8I4ZS%2BV3Pg7rIB9%2FyiS%2FhudynPEygQu74u0hjhQja2FjJ5FoZPp6ItBWcVl4t4UY%2B88JU4j%2BXvrzja8NzwUIa%2BeUPiAsWG9G3pbdtKmEDVoxQnULUdmeGnRZeirmYN%2Fl7zAQ1jzO%2B73Z47Y%2BdZhP15McNE99cMoyoas8wCWpypwsyB%2F3nIghBD2sZQKVk5hKQUcR7PwQ%3D%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='.$PROV_USERNAME.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='.$PROV_PWD.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen';
        $CLUBS_CSV_URL='https://toernooi.nl/organization/export/group_subgroups_export.aspx?id='.$PROV_ID.'&gid='.$PROV_GID.'&ft=1';
        $TEAMS_CSV_URL='https://toernooi.nl/sport/admin/exportteams.aspx?id='.$PB_COMPETITIE_ID.'&ft=1';
        $PLAYERS_CSV_URL='https://toernooi.nl/organization/export/export_memberperroletypepergroup.aspx?id='.$PROV_ID.'&gid='.$PROV_GID.'&ft=1&glid=1';
        $MATCHES_CSV_URL='https://toernooi.nl/sport/admin/exportteammatches.aspx?id='.$PB_COMPETITIE_ID.'&ft=1&sd='.$PB_COMPETITIE_START_DAY.'000000&ed='.$PB_COMPETITIE_END_DAY.'000000';

        $BASETEAM_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/basisopstellingen.csv';
        $FIXED_RANKING_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/indexen_spelers_01052014.csv';
        $LIGA_BASETEAM_CSV_URL=env('SITE_ROOT','http://localhost/pbo').'/data/fixed/liga_basisopstelling_gemengd_20142015.csv';

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

        //set cookie to bypass "do you accept cookies" warning
        curl_setopt($ch, CURLOPT_COOKIE, "st=c=1");
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
        curl_setopt($ch, CURLOPT_COOKIE, "st=c=1; ".$cookies);

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
                or !DBLoadController::isValidCSV($ligaBaseTeamCSV,"Discipline,Teamnaam,Lidnummer,Voornaam,")) {
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
                EventController::logEvent('DBLOAD','SYSTEM');
                $this->updateMatchCRAccordingNewData();
                print("OK");
            }
        }
    }

    static function isValidCSV($CSV,$firstLineContent) {
        $csvFirstLine = strtok($CSV, "\n");
        if (strpos($csvFirstLine,$firstLineContent) === false) {
            return false;
        } else {
            return true;
        }
    }

    static function cleanDB() {
        //print("Start cleaning");
        DB::statement('DELETE from lf_match');
        DB::statement('DELETE from lf_ranking');
        DB::statement('DELETE from lf_player_has_team');
        DB::statement('DELETE from lf_player');
        DB::statement('DELETE from lf_team');
        DB::statement('DELETE from lf_club');
        DB::statement('DELETE from lf_group');
        DB::statement('DELETE from lf_tmpdbload_teamscsv');
        DB::statement('DELETE from lf_tmpdbload_playersremoved');
        DB::statement('DELETE from lf_tmpdbload_playerscsv');
        DB::statement('DELETE FROM lf_tmpdbload_15mei');
        DB::statement('DELETE FROM lf_tmpdbload_basisopstellingliga');
    }

    static function loadCSV($CSV,$type) {

        $delimiter=';';
        if ($type == 'baseTeam' or $type == 'ligaBaseTeam') {
            $delimiter=',';
        }
        $parsedCsv = DBLoadController::parse_csv($CSV,$delimiter,true,false);
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
                DBLoadController::buildAndExecQuery($parsedCsv,
                    'INSERT INTO lf_club(clubId, clubName, clubCode) VALUES',
                    array('Nummer','Naam','?Code')
                );
                break;
            case "teams":
                DBLoadController::buildAndExecQuery($parsedCsv,
                    'INSERT INTO lf_tmpdbload_teamscsv(name, clubCode, eventName, drawName, captainName,email ) VALUES ',
                    array('name','clubcode','eventname','DrawName','contact','email')
                );
                break;
            case "matches":
                DBLoadController::buildAndExecQuery($parsedCsv,
                    'INSERT INTO lf_match(homeTeamName, outTeamName, locationId, locationName, matchId, date) VALUES ',
                    array('team1name','team2name','locationid','locationname','matchid','plannedtime'),
                    '(?, ?, ?, ?, ?, str_to_date(?, \'%e-%c-%Y %H:%i:%S\'))'
                );
                break;
            case "players":
                DBLoadController::buildAndExecQuery($parsedCsv,
                    'INSERT INTO lf_tmpdbload_playerscsv(memberId,firstName,lastName,gender,groupName,playerLevelSingle,playerLevelDouble,playerLevelMixed,typeName,role,groupCode) VALUES ',
                    array('memberid','firstname','lastname','gender','groupname','PlayerLevelSingle','PlayerLevelDouble','PlayerLevelMixed','TypeName','role','?groupcode')
                );
                break;
            case "baseTeam":
                DBLoadController::buildAndExecQuery($parsedCsv,
                    'INSERT INTO lf_player_has_team(player_playerId, team_teamName) VALUES ',
                    array('player_playerId','team_teamName')
                );
                break;
            case "fixedRanking":
                DBLoadController::buildAndExecQuery($parsedCsv,
                    'INSERT INTO lf_tmpdbload_15mei(playerId, playerLevelSingle, playerLevelDouble, playerLevelMixed) VALUES ',
                    array('Lidnummer','Klassement enkel','Klassement dubbel','Klassement gemengd')
                );
                break;
            case "ligaBaseTeam":
                DBLoadController::buildAndExecQuery($parsedCsv,
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
INSERT INTO lf_team (teamName,sequenceNumber,club_clubId, group_groupId, captainName,email)
select name,lf_dbload_teamSequenceNumber(name),clubCode,(select groupId from lf_group lfg where lfg.tournament = t.`year` and lf_dbload_eventcode(t.eventName) = lfg.event and  lf_dbload_devision(t.drawName) = lfg.devision and lf_dbload_serie(t.drawName) = lfg.series),t.captainName,t.email  from lf_tmpdbload_teamscsv t;
EOD;
        $deleteLfTmpdbloadPlayers = <<<'EOD'
delete from lf_tmpdbload_playerscsv
where role != 'Uitgeleende speler'
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
        $insertLfMatchExtra = <<<'EOD'
insert into lf_match_extra(oTeamName,hTeamName)
SELECT m.outTeamName,m.homeTeamName FROM lf_match m
left join lf_match_extra e on e.oTeamName =m.outTeamName and e.hTeamName = m.homeTeamName
where e.matchIdExtra is null
EOD;
        switch($type) {
            case "teams":
                DB::update($updateLfYear);
                DB::insert($insertLfGroup);
                DB::insert($insertLfTeam);
                break;
            case "players":
                // When player is from O-Vl Club and rented to another O-Vl it will appear twice. However, we only want to keep the record with role='Speler'
                // Some tricks needed to avoid mysql limitation: In MySQL, you can't modify the same table which you use in the SELECT part
                // http://stackoverflow.com/questions/45494/mysql-error-1093-cant-specify-target-table-for-update-in-from-clause
                DB::delete($deleteLfTmpdbloadPlayers);

                //When must import players from type=Recreant too because they can be part of a baseTeam!
                DB::insert($insertLfPlayer);

                DB::insert($insertLfRanking);
                break;
            case "baseTeam":
                DB::insert($insertLfBaseTeamAddMissingPlayersStep1);
                DB::insert($insertLfBaseTeamAddMissingPlayersStep2);
                break;
            case "fixedRanking":
                DB::insert($insertLfRankingFixed);
                break;
            case "ligaBaseTeam":
                DB::insert($insertFakeLigaGroup);
                DB::insert($insertLfTeamLiga);
                DB::insert($insertLfPlayerHasTeamLiga);
                break;
            case "matches":
                DB::insert($insertLfMatchExtra);
                break;
        }



        //print_r($parsedCsv);
    }

    static function buildAndExecQuery($parsedCsv, $queryStart,$columnsToSelect,$qPreparedRecord = "") {
        //2014/12/04 For import performance reasons, its a lot faster to import using a big insert querie(s) than one by one.
        //$query = "INSERT INTO lf_tmpdbload_15mei(playerId, playerLevelSingle, playerLevelDouble, playerLevelMixed) VALUES "; //Prequery
        //$columnsToSelect = array('Lidnummer','Klassement enkel','Klassement dubbel','Klassement gemengd');

        $headers = array_flip($parsedCsv[0]);

        //Build up all prepared values (?,?,?,?,...) , (?,?,?,?,...),...
        if (empty($qPreparedRecord)) {
            $qPreparedRecord = '(' . implode(",",array_fill(0, count($columnsToSelect), "?")) . ')';
        }
        $startRecord=1;
        $maxRecordsAtOnce=1000;
        while ($startRecord<=count($parsedCsv)-2) {
            $query = $queryStart;
            $endRecord = min($startRecord+$maxRecordsAtOnce-1,count($parsedCsv)-2);
            $numberOfRecords = min($endRecord-$startRecord+1,$maxRecordsAtOnce);

            $qPreparedRecords = array_fill(0, $numberOfRecords, $qPreparedRecord);
            $query .=  implode(",",$qPreparedRecords);

            //Build up all bind parameters
            $bindParams = array();
            for($i = $startRecord; $i <= $endRecord; $i++) {
                for ($j=0, $numberOfColumns = count($columnsToSelect); $j < $numberOfColumns; ++$j) {
                    $bindParams[] = $parsedCsv[$i][$headers[$columnsToSelect[$j]]];
                }
            }

            DB::insert($query,$bindParams);

            $startRecord=$endRecord+1;
        }
    }



    static function parse_csv ($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
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
set status=null,actionFor=null
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


            $subject= "Verplaatsings aanvraag ".$match->hTeamName." - ".$match->oTeamName. " bevestigd";

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
        }
    }
}
