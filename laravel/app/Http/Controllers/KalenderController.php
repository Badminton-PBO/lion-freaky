<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

use Illuminate\Http\Request;

/**
 * TO DO: BusinessLogic scheiden van controller
 * Voorlopig alle Google logica in de controller...
 */
/**
 * Class KalenderController
 * @package App\Http\Controllers
 */
class KalenderController extends Controller {

    private $calendar_service;

    public function __construct()
    {
        $this->calendar_service = createCalendarService("<InsertNaam>");
    }

    public function sync(){
$query = <<<EOD
SELECT  homeTeamName, outTeamName, date, locationName
FROM `lf_match`
EOD;
        $matches = DB::select($query);

        foreach($matches as $match){
            $calendarName = $this->getCalendarName($match->homeTeamName);
            $calendarId = $this->getCalendarId($match->homeTeamName);

        }
    }

    private function createCalendarService($appName){
        $client = new Google_Client();
        $client->setApplicationName($appName);
        $service = new Google_Service_Calendar($client);
        if (isset($_SESSION['service_token'])) {
            $client->setAccessToken($_SESSION['service_token']);
        }
        $key = file_get_contents($key_file_location);
        $cred = new Google_Auth_AssertionCredentials(
            $service_account_name,
            array('https://www.googleapis.com/auth/calendar'),
            $key
        );
        $client->setAssertionCredentials($cred);
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion($cred);
        }
        $_SESSION['service_token'] = $client->getAccessToken();

        return $service;
    }

    private function addEventIfNeeded($match, $calendarId){
        $eventList = $this->calendar_service->events->listEvents($calendarId);
        $newEvent = $this->createEvent($match);
        $isExistingEvent = $this->findMatchingEvent($newEvent, $eventList->getItems());
        if(!$isExistingEvent){
            //insert...
        }
    }

    private function getCalendarId($teamName)
    {
        $needToAdd = true;
        $calendarList = $this->calendar_client->calendarList->listCalendarList();
        $calendarItems = $calendarList->getItems();
        $calendarId = "";
        if(!empty($calendarItems))
        {
            foreach($calendarItems as $calendar)
            {
                if($calendar->getSummary() === $teamName)
                {
                    $calendarId = $calendar->getId();
                    $needToAdd = false;
                    break;
                }
            }
        }

        if($needToAdd)
        {
            $newCalendar = new Google_Service_Calendar_Calendar();
            $newCalendar->setSummary($teamName);
            $createdCalendar = $this->calendar_service->calendars->insert($newCalendar);
            $calendarId = $createdCalendar->getId();
        }
        return $calendarId;
    }

    private function getCalendarName($teamName){
        return $teamName + " competitie";
    }

    private function createEvent($match){
        $newEvent = new Google_Service_Calendar_Event();

        $newEvent->setSummary($match->homeTeamName + " - " + $match->outTeamName);
        $newEvent->setLocation($match->locationName);

        //TO DO: controle van dit ...
        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime('2012-10-31T10:00:00.000-05:00');
        $newEvent->setStart($start);
        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime('2012-10-31T10:25:00.000-05:00');
        $newEvent->setEnd($end);
        return $newEvent;
    }

    private function findMatchingEvent($newEvent, $events) {
        $matchingEvent = null;
        if(!empty($events)){
            foreach($events as $event){
                $eventSummary = $event->getSummary();
                if(!empty($eventSummary) && $event->getSummary() == $newEvent->getSummary()){
                    $matchingEvent = $event;
                    break;
                }
            }
        }
        if(!is_null($matchingEvent))
        {


        }

    }
}