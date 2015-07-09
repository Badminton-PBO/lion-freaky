<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Match;
use App\Services\GoogleCalendar;
use App\Team;
use App\Batch;
use DB;

use Illuminate\Http\Request;


/**
 * Class KalenderController
 * @package App\Http\Controllers
 */
class KalenderController extends Controller {

    private $calendar;

    public function __construct() {
        $this->calendar = new GoogleCalendar();
    }

    public function authenticateUser(){
        $code = "";
        if(isset($_GET["code"])){
            $code = $_GET["code"];
        }
        $this->calendar->authenticateUser($code);
    }

    public function syncCalendars() {
        //Haal Batch op. Indien finished => einde
        $time_start = microtime(true);
        $batch = new Batch();
        $batch = $batch->getBatch("syncCalendarsGC", date('Y-m-d'));
        if($batch->finished == 1) {
            return;
        }
        $number = $batch->number;

        $team = new Team();
        $teams = $team->getAll();
        if($number >= count($teams)){
            $batch->setFinished();
        }
        for(;;){
            if($number >= count($teams)){
                $batch->setFinished();
                exit;
            }
            $team = $teams[$number];
            $this->calendar->createCalendar($team->teamName);
            $batch->incrementBatch();
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            echo "Process Time: {$time} <br/>";
            if($time > 35) {
                die("timeout nadert");
            }
            $number++;
        }

    }

    public function syncMatches(){
        //Haal Batch op. Indien finished => einde
        $time_start = microtime(true);
        $batch = new Batch();
        $batch = $batch->getBatch("syncGoogleCalendar", date('Y-m-d'));
        if($batch->finished == 1) {
            return;
        }
        $number = $batch->number;

        $team = new Team();
        $teams = $team->getAll();
        if($number >= count($teams)){
            $batch->setFinished();
        }
        for(;;){
            if($number >= count($teams)){
                $batch->setFinished();
                exit;
            }
            $team = $teams[$number];
            $match = new Match();
            $matches = $match->getMatches($team->teamName);


            $this->calendar->modifyCalendar($team->teamName, $matches);
            $batch->incrementBatch();
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            echo "Process Time: {$time} <br/>";
            if($time > 35) {
                die("timeout nadert");
            }
            $number++;
        }
    }
}