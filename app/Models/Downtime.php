<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Downtime extends Model {


    public function machine(){
        return $this->belongsTo(Machine::class);
    }

    public function error(){
        return $this->belongsTo(Error::class);
    }

}
