<?php namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class GoogleCalendar {

    protected $client;

    protected $service;

    protected $calendars;


    function __construct() {
        /* Get config variables */
        $client_id = Config::get('google.client_id');
        $service_account_name = Config::get('google.service_account_name');
        $key_file_location = base_path() . Config::get('google.key_file_location');

        $this->client = new \Google_Client();
        $this->client->setApplicationName("Your Application Name");
        $this->service = new \Google_Service_Calendar($this->client);

        /* If we have an access token */
        if (Cache::has('service_token')) {
            $this->client->setAccessToken(Cache::get('service_token'));
        }

        $key = file_get_contents($key_file_location);
        /* Add the scopes you need */
        $scopes = array('https://www.googleapis.com/auth/calendar');
        $cred = new \Google_Auth_AssertionCredentials(
            $service_account_name,
            $scopes,
            $key
        );

        $this->client->setAssertionCredentials($cred);
        if ($this->client->getAuth()->isAccessTokenExpired()) {
            $this->client->getAuth()->refreshTokenWithAssertion($cred);
        }
        Cache::forever('service_token', $this->client->getAccessToken());

        //Call one time
        $calendarList = $this->service->calendarList->listCalendarList();
        $this->calendars = $calendarList->getItems();
    }

    public function modifyCalendar($teamName, $matches)
    {
        $calendarName = $this->getCalendarName($teamName);
        $calendarId = $this->getCalendarId($calendarName);
        $eventList = $this->service->events->listEvents($calendarId);
        $events = $eventList->getItems();

        $this->client->setUseBatch(true);
        $googleBatch = new \Google_Http_Batch($this->client);
        foreach($matches as $match) {
            $this->addEventIfNeeded($match, $calendarId, $events, $googleBatch);
        }
        $googleBatch->execute();
    }

    private function getCalendarId($teamName)
    {
        $needToAdd = true;
        $calendarId = "";

        if(!empty($this->calendars))
        {
            foreach($this->calendars as $calendar)
            {
                if($calendar->getSummary() == $teamName)
                {
                    $calendarId = $calendar->getId();
                    $needToAdd = false;
                    echo $teamName . " is already added ...";
                    break;
                }
            }
        }
        if($needToAdd)
        {
            $this->client->setUseBatch(false);
            $newCalendar = new \Google_Service_Calendar_Calendar();
            $newCalendar->setSummary($teamName);
            $createdCalendar = $this->service->calendars->insert($newCalendar);
            $calendarId = $createdCalendar->getId();
            $this->setPublicAccess($calendarId);
            $this->setServiceAccountAccess($calendarId);
        }
        return $calendarId;
    }

    private function addEventIfNeeded($match, $calendarId,$events, &$batch){
        $newEvent = $this->createEvent($match);
        $matchingEvent = $this->findMatchingEvent($newEvent, $events, $calendarId, $batch);
        if(is_null($matchingEvent)){
            echo "geen match gevonden - inserteren";
            $req = $this->service->events->insert($calendarId,$newEvent);
            $batch->add($req);
        }
        else{
            echo "Match gevonden - ignore";
        }

    }
    private function getCalendarName($teamName){
        return $teamName . " Competitie";
    }

    private function createEvent($match){
        $newEvent = new \Google_Service_Calendar_Event();

        $newEvent->setSummary($match->homeTeamName . " - " . $match->outTeamName);
        $newEvent->setLocation($match->locationName);

        $effectiveDate = strtotime("+180 minutes", strtotime($match->date));
        $endTime =  date("Y-m-d\TH:i:s",$effectiveDate);

        $start = new \Google_Service_Calendar_EventDateTime();
        $start->setDateTime(date('Y-m-d\TH:i:s',strtotime($match->date)));
        $start->setTimeZone("Europe/Brussels");
        $newEvent->setStart($start);
        $end = new \Google_Service_Calendar_EventDateTime();
        $end->setDateTime($endTime);
        $end->setTimeZone("Europe/Brussels");
        $newEvent->setEnd($end);
        return $newEvent;
    }

    private function findMatchingEvent($newEvent, $events, $calendarId, &$batch) {
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
            /* @var $matchingEvent \Google_Service_Calendar_Event */
            if($matchingEvent->getStart()->getDateTime() == $newEvent->getStart()->getDateTime()
                && $matchingEvent->getEnd()->getDateTime() == $newEvent->getEnd()->getDateTime()
                && $matchingEvent->getLocation() == $newEvent->getLocation()
                && $matchingEvent->getDescription() == $newEvent->getDescription()){

                return null;
            }
            $req = $this->service->events->delete($calendarId,$matchingEvent->getId());
            $batch->add($req);
        }

        return $matchingEvent;

    }

    private function setPublicAccess($calendarId){
        $scope = new \Google_Service_Calendar_AclRuleScope();
        $scope->setType("default");

        $rule = new \Google_Service_Calendar_AclRule();
        $rule->setScope($scope);
        $rule->setRole("reader");

        $result = $this->service->acl->insert($calendarId, $rule);
    }

    private function  setServiceAccountAccess($calendarId){
        $owner_account_name = Config::get('google.owner_account_name');
        $scope = new \Google_Service_Calendar_AclRuleScope();
        $scope->setType("user");
        $scope->setValue($owner_account_name);

        $rule = new \Google_Service_Calendar_AclRule();
        $rule->setScope($scope);
        $rule->setRole("owner");

        $result = $this->service->acl->insert($calendarId, $rule);
    }
}