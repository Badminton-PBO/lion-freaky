<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Request;
use Response;
use Mail;

class VerplaatsingsController extends Controller
{


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
    public function index()
    {


        //$results = DB::select('select * from lf_club');
        //var_dump($results);
        //var_dump(Auth::user()->username);
        //return "Welcome " . Auth::user()->username . " you are in mylord. <a href='auth/logout'>logout</a>";
        return view('verplaatsing');
    }

    public function clubAndTeams()
    {
        $queryTeams = <<<EOD
select c.clubName,t.teamName from lf_club c
join lf_team t on c.clubId = t.club_clubId
where c.clubId=:clubId
order by c.clubName,t.teamName
EOD;

        $queryDBLoad = <<<EOD
SELECT  date_format(max(`when`),'%Y%m%d%H%i%S') date FROM `lf_event`
where eventType='DBLOAD'
EOD;

        $queryMeetingsWithActionForThisClub = <<<EOD
select me.hTeamName,me.oTeamName,m.date,me.actionFor from lf_match_extra me
join lf_match m on m.homeTeamName = me.hTeamName and m.outTeamName = me.oTeamName
where me.actionFor in (
select t.teamName from lf_team t
join lf_club c on c.clubId = t.club_clubId
where c.clubId=:clubId
)
order by me.matchIdExtra asc
EOD;

        $queryMeetingsWithActionsForOthersInvolvingThisClub = <<<EOD
select me.hTeamName,me.oTeamName,m.date,me.actionFor from lf_match_extra me
join lf_match m on m.homeTeamName = me.hTeamName and m.outTeamName = me.oTeamName
where
(
me.hTeamName in (select t.teamName from lf_team t
join lf_club c on c.clubId = t.club_clubId
where c.clubId=:clubId1
)
or me.oTeamName in (select t.teamName from lf_team t
join lf_club c on c.clubId = t.club_clubId
where c.clubId=:clubId2
)
)
and me.actionFor not in (
select t.teamName from lf_team t
join lf_club c on c.clubId = t.club_clubId
where c.clubId=:clubId3
)
and me.status in ('IN AANVRAAG','OVEREENKOMST')
order by me.matchIdExtra asc
EOD;



        $dbteams = DB::select($queryTeams, array('clubId' => Auth::user()->club_id));
        $dbQueryMeetingsWithActionForThisClub = DB::select($queryMeetingsWithActionForThisClub, array('clubId' => Auth::user()->club_id));
        $dbQueryMeetingsWithActionsForOthersInvolvingThisClub = DB::select($queryMeetingsWithActionsForOthersInvolvingThisClub, array('clubId1' => Auth::user()->club_id,'clubId2' => Auth::user()->club_id, 'clubId3' => Auth::user()->club_id));

        $dbloads = DB::select($queryDBLoad);


        if (sizeof($dbteams) > 0) {
            $result["clubs"][0] = array("clubName" => $dbteams[0]->clubName, "teams" => array(), "openRequests" => array(),"openRequestsForOthers" => array());
            $teamCounter = -1;
            foreach($dbteams as $key => $team) {
                $teamCounter++;
                $result["clubs"][0]["teams"][$teamCounter] = array('teamName' => $team->teamName);
            }

            $openRequestCounter = -1;
            foreach($dbQueryMeetingsWithActionForThisClub as $key => $openRequest) {
                $openRequestCounter++;
                $result["clubs"][0]["openRequests"][$openRequestCounter] = array('hTeamName' => $openRequest->hTeamName,'oTeamName' => $openRequest->oTeamName,'date' => $openRequest->date,'actionFor' => $openRequest->actionFor);
            }
            $openRequestForOthersCounter = -1;
            foreach($dbQueryMeetingsWithActionsForOthersInvolvingThisClub as $key => $openRequest) {
                $openRequestForOthersCounter++;
                $result["clubs"][0]["openRequestsForOthers"][$openRequestForOthersCounter] = array('hTeamName' => $openRequest->hTeamName,'oTeamName' => $openRequest->oTeamName,'date' => $openRequest->date,'actionFor' => $openRequest->actionFor);
            }


        }
        $result['DBLOAD'] = $dbloads;

        header("Content-Disposition: attachment; filename=json.data");
        header("Pragma: no-cache");
        header("Expires: 0");

        return response()->json($result);
    }


    public function  meetingAndMeetingChangeRequest($clubName,$teamName) {
        $queryEventPerTeam = <<<EOD
select m.homeTeamName,m.outTeamName, date_format(m.date,'%Y%m%d%H%i%S') date,m.locationName,e.matchIdExtra,e.status,e.actionFor,e.hTeamComment,e.oTeamComment from lf_match m
join lf_match_extra e on e.hTeamName = m.homeTeamName and e.oTeamName = m.outTeamName
where (m.homeTeamName = :team1 or m.outTeamName = :team2)
and m.date >= DATE_SUB(now(),INTERVAL :startDate DAY)
order by m.date asc;
EOD;
//and m.date >= now()


        $queryMatchCRPerTeam = <<<EOD
select e.matchIdExtra,cr.acceptedState, cr.finallyChosen, e.hTeamName,e.oTeamName,cr.matchCRId,date_format(cr.proposedDate,'%Y%m%d%H%i') proposedDate,cr.requestedByTeam,date_format(cr.requestedOn,'%Y%m%d%H%i%S') requestedOn  from lf_match_cr cr
join lf_match_extra e on e.matchIdExtra = cr.match_matchIdExtra
where (e.hTeamName = :team1 or e.oTeamName = :team2)
order by cr.proposedDate asc;
EOD;

        $queryEventPerClub = <<<EOD
select m.homeTeamName,m.outTeamName, date_format(m.date,'%Y%m%d%H%i%S') date,m.locationName,e.matchIdExtra,e.status,e.actionFor,,e.hTeamComment,e.oTeamComment from lf_match m
join lf_match_extra e on e.hTeamName = m.homeTeamName and e.oTeamName = m.outTeamName
join lf_team t on t.teamName = m.homeTeamName or t.teamName = m.outTeamName
join lf_club c on c.clubId = t.club_clubId
where c.clubName= :club
and m.date >=DATE_SUB(now(),INTERVAL :startDate DAY)
order by m.date asc;
EOD;
//and m.date >= now()
        $queryMatchCRPerClub = <<<EOD
select e.matchIdExtra,cr.acceptedState, cr.finallyChosen, e.hTeamName,e.oTeamName,cr.matchCRId,date_format(cr.proposedDate,'%Y%m%d%H%i') proposedDate,cr.requestedByTeam,date_format(cr.requestedOn,'%Y%m%d%H%i%S') requestedOn  from lf_match_cr cr
join lf_match_extra e on e.matchIdExtra = cr.match_matchIdExtra
join lf_match m on e.hTeamName = m.homeTeamName and e.oTeamName = m.outTeamName
join lf_team t on t.teamName = m.homeTeamName or t.teamName = m.outTeamName
join lf_club c on c.clubId = t.club_clubId
where c.clubName= :club
order by cr.proposedDate asc;
EOD;

        $result = array('meetings'=>array());

        //Add match data
        $matches = DB::select($queryEventPerTeam, array('team1' =>$teamName,'team2' =>$teamName,'startDate' => env('SHOW_MEETINGS_STARTING_FROM_NOW_MINUS_DAYS', 0)));
        $matchesCRs = DB::select($queryMatchCRPerTeam, array('team1' =>$teamName,'team2' =>$teamName));


        foreach($matches as $key => $match) {
            $matchCRs = array();
            foreach($matchesCRs as $key => $matchesCR) {
                if ($match->homeTeamName == $matchesCR->hTeamName and $match->outTeamName == $matchesCR->oTeamName) {
                    //Matching matchCR, adding to result
                    array_push($matchCRs,
                        array(
                            'acceptedState' => $matchesCR->acceptedState,
                            'finallyChosen' => $matchesCR->finallyChosen,
                            'hTeamName' => $matchesCR->hTeamName,
                            'oTeamName' => $matchesCR->oTeamName,
                            'matchCRId' => $matchesCR->matchCRId,
                            'proposedDate' => $matchesCR->proposedDate,
                            'requestedByTeam' => $matchesCR->requestedByTeam,
                            'requestedOn' => $matchesCR->requestedOn,
                            'matchIdExtra' => $matchesCR->matchIdExtra
                        )
                    );
                }
            }

            array_push($result["meetings"],
                array('hTeam' => $match->homeTeamName,
                    'oTeam' => $match->outTeamName,
                    'dateTime' => $match->date,
                    'locationName' => $match->locationName,
                    'matchIdExtra' => $match->matchIdExtra,
                    'status' => $match->status,
                    'actionFor' => $match->actionFor,
                    'hTeamComment' => $match->hTeamComment,
                    'oTeamComment' => $match->oTeamComment,
                    'CRs' => $matchCRs));

        }


        $response = Response::json($result);
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');
        return $response;

    }



    public function saveMeetingChangeRequest()
    {
        $deleteExistingMatchCR = <<<'EOD'
delete from lf_match_cr where match_matchIdExtra=:matchIdExtra
EOD;

        $insertMatchCR = <<<'EOD'
INSERT INTO lf_match_cr(proposedDate,requestedByTeam,requestedOn,acceptedState,finallyChosen,match_matchIdExtra)
VALUES(STR_TO_DATE(:proposedDate,'%Y%m%d%H%i'),:requestedByTeam,STR_TO_DATE(:requestedOn,'%Y%m%d%H%i'),:acceptedState,:finallyChosen,:matchIdExtra)
EOD;

        $updateMatchExtra = <<<'EOD'
update lf_match_extra e set status = :status, actionFor = :actionFor, hTeamComment = :hTeamComment, oTeamComment = :oTeamComment
where matchIdExtra = :matchIdExtra;
EOD;


        $processedSuccessfull=true;
            $chosenMeeting = Request::input("chosenMeeting");
        $sendMail = Request::input("sendMail");

        DB::delete($deleteExistingMatchCR,
            array(
                'matchIdExtra' => $chosenMeeting['matchIdExtra']
            )
        );

        foreach ($chosenMeeting['proposedChanges'] as $key => $proposedChange) {
            DB::insert($insertMatchCR,
                array(
                    ':proposedDate' => $proposedChange['proposedDateTime'],
                    ':requestedByTeam' => $proposedChange['requestedByTeam'],
                    ':requestedOn' => $proposedChange['requestedOn'],
                    ':acceptedState' => $proposedChange['acceptedState'],
                    ':finallyChosen' => ($proposedChange['finallyChosen'] == false ? 0 : 1),
                    ':matchIdExtra' => $chosenMeeting['matchIdExtra']
                )
            );
        }

        DB::update($updateMatchExtra,
            array(
                ':status' => $chosenMeeting['status'],
                ':actionFor' => $chosenMeeting['actionFor'],
                ':matchIdExtra' => $chosenMeeting['matchIdExtra'],
                ':hTeamComment' => $chosenMeeting['hTeamComment'],
                ':oTeamComment' => $chosenMeeting['oTeamComment']
            )
        );

        //Create event for this request
        EventController::logEvent('v-saved',$chosenMeeting['chosenTeamName']);

        if ($sendMail == 'true') {
            $mailTo = $this->sendMail($chosenMeeting);
            $chosenMeeting["mailTo"]= $mailTo;
            $processedSuccessfull = $processedSuccessfull && !(empty($mailTo));
        }

        $chosenMeeting["processedSuccessfull"]= $processedSuccessfull;

        $response = Response::json($chosenMeeting);
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');
        return $response;

    }

    function sendMail($chosenMeeting)
    {
        $receiverTeam = "";
        $requesterTeam = "";
        if ($chosenMeeting['chosenTeamName'] == $chosenMeeting['hTeam']) {
            $receiverTeam = $chosenMeeting['oTeam'];
            $requesterTeam = $chosenMeeting['hTeam'];
        } else {
            $receiverTeam = $chosenMeeting['hTeam'];
            $requesterTeam = $chosenMeeting['oTeam'];
        }
        //Retrieve team email address
        $queryTeam = <<<'EOD'
select t.email from lf_team t
where t.teamName = :team;
EOD;
        $dbTeam = DB::select($queryTeam, array('team' => $requesterTeam));
        $requesterTeamEmail = $dbTeam[0]->email;

        $dbTeam = DB::select($queryTeam, array('team' => $receiverTeam));
        $receiverTeamEmail = $dbTeam[0]->email;


        $link = env('SITE_ROOT', '') . '/verplaatsing?hteam=' . rawurlencode($chosenMeeting['hTeam']) . '&oTeam=' . rawurlencode($chosenMeeting['oTeam']);
        $subject = 'Verplaatsingsaanvraag ' . $chosenMeeting['hTeam'] . ' - ' . $chosenMeeting['oTeam'];

        $data = array('mailToReceiver' => $receiverTeamEmail,
            'mailToReceiverText' => $receiverTeam,
            'mailToRequester' => $requesterTeamEmail,
            'mailToRequesterText' => $requesterTeam,
            'subject' => $subject,
            'link' => $link,
            'hTeam' => $chosenMeeting['hTeam'],
            'oTeam' => $chosenMeeting['oTeam'],
            'dateTimeLayout' => $chosenMeeting['dateLayout'] . ',' . $chosenMeeting['hourLayout'],
            'requester' => $requesterTeam);


        if ($chosenMeeting['actionFor'] != 'PBO') {
            Mail::send('emails.verplaatsing-receiver', $data, function ($message) use ($data) {
                $message->to(VerplaatsingsController::giveFinalMailto($data['mailToReceiver']), $data['mailToReceiverText'])->subject($data['subject']);
            });
            Mail::send('emails.verplaatsing-requester', $data, function ($message) use ($data) {
                $message->to(VerplaatsingsController::giveFinalMailto($data['mailToRequester']), $data['mailToRequesterText'])->subject($data['subject']);
            });
            return "$receiverTeam <".$receiverTeamEmail.">";
        } else {
            foreach ($chosenMeeting['proposedChanges'] as $key => $proposedChange) {
                if ($proposedChange['finallyChosen'] == 'true') {
                    $data['proposedDateTimeLayout']= $proposedChange['proposedDateTimeLayout'];
                }
            }
            Mail::send('emails.verplaatsing-agreement', $data, function ($message) use ($data) {
                $message->to(VerplaatsingsController::giveFinalMailto($data['mailToReceiver']), $data['mailToReceiverText'])
                    ->to(VerplaatsingsController::giveFinalMailto($data['mailToRequester']), $data['mailToRequesterText'])
                    ->to(VerplaatsingsController::giveFinalMailto(env('VERPLAATSING_MAIL_PBO','')))
                    ->subject($data['subject']);
            });
            return env('VERPLAATSING_MAIL_PBO','').','."$receiverTeam <".$receiverTeamEmail.">".','."$requesterTeam <".$requesterTeamEmail.">";
        }

    }

    public function testMailGun() {

        $data = array('mailToReceiver' => 'thomas.dekeyser@gmail.com',
            'mailToReceiverText' => 'Thomas',
            'subject' => 'TESTTDE',
            'link' => 'TESTTDE',
            'hTeam' => 'TESTTDE',
            'oTeam' => 'TESTTDE',
            'dateTimeLayout' => 'TESTTDE',
            'requester' => 'TESTTDE');

        Mail::send('emails.verplaatsing-receiver', $data, function ($message) use ($data)
        {
            $message->to(VerplaatsingsController::giveFinalMailto($data['mailToReceiver']), $data['mailToReceiverText'])->subject($data['subject']);
        });

/*        Mail::send('emails.testmailgun', $data, function ($message) use ($data)
        {
            $message->to($data['mailToReceiver'], $data['mailToReceiverText'])->subject($data['subject']);
        })*/;

    }

    //Support testing where we have to use a fixed mailto address
    public static function giveFinalMailto($mailto) {
        $myFixedMailTo = env('VERPLAATSING_FIXED_MAILTO_ADDRESS','');
        return empty($myFixedMailTo) ? $mailto : $myFixedMailTo;
    }
}