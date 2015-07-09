<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OAuth extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lf_oauth';

    public $timestamps = false;

    public function getCredential($type){
        return OAuth::where('type', $type)->first();
    }
    public function insertCredential($type, $credentials){
        $oauth = new OAuth;
        $oauth->type = $type;
        $oauth->credentials = $credentials;
        $oauth->save();
    }
}
