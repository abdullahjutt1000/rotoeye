<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class LoginRecord extends Model {

    public function machine(){
        return $this->belongsTo(Machine::class);
    }

    public function job(){
        return $this->belongsTo(Job::class);
    }

    public function user(){
        return $this->belongsTo(Users::class);
    }

}
