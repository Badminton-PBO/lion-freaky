<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lf_batch';

    public function getBatch($type, $date){
        $batch = Batch::where('type', $type)->where("date", $date)->first();
        if (is_null($batch)) {
            // batch doesn't exist
            $batch = new Batch;
            $batch->type = $type;
            $batch->date = $date;
            $batch->finished = 0;
            $batch->number = 0;

            $batch->save();
        }

        return $batch;
    }
    public function incrementBatch(){
        $this->increment('number');
    }
    public function setFinished(){
        $this->update(['finished' => 1]);
    }
}
