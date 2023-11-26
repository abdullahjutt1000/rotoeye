<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Record extends Model {

	public function job(){
        return $this->belongsTo(Job::class);
    }

    public function machine(){
        return $this->belongsTo(Machine::class);
    }

    public function user(){
        return $this->belongsTo(Users::class);
    }

    public function error(){
        return $this->belongsTo(Error::class);
    }

    public function process(){
        return $this->belongsTo(Process::class);
    }

}
