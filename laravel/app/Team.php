<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lf_team';

    public function getAll(){
        return Team::orderBy("teamName","desc")->get();
    }
}
