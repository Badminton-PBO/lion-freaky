<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\GoogleCalendar;
use DB;

use Illuminate\Http\Request;


/**
 * Class KalenderController
 * @package App\Http\Controllers
 */
class KalenderController extends Controller {

    private $calendar;

    public function __construct()
    {
        $this->calendar = new GoogleCalendar();
    }

    public function sync(){
$query = <<<EOD
SELECT  homeTeamName, outTeamName, date, locationName
FROM `lf_match`
EOD;
        $matches = DB::select($query);
        foreach($matches as $match){
            $calendarName = $this->calendar->getCalendarName($match->homeTeamName);
            $calendarId = $this->calendar->getCalendarId($calendarName);
            $this->calendar->addEventIfNeeded($match,$calendarId);
            //Still Testing...
            die;

        }
    }







}