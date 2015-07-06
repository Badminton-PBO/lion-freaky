<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lf_match';

    public function getMatches($teamName){
        return Match::where('homeTeamName', '=', $teamName)->orWhere('outTeamName', '=', $teamName)->get();
    }
}
