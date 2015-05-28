<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

use Illuminate\Http\Request;

class EventController extends Controller {

    public static function logEvent($eventType,$who)
    {
        DB::insert('INSERT INTO lf_event(eventType, `when`, who) VALUES(:eventType, now(), :who)',
            array('eventType' =>$eventType,'who' => $who)
        );
    }

}
