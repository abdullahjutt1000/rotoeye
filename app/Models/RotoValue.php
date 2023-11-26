<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class RotoValue extends Model {

	public function machine(){
        return $this->belongsTo(Machine::class);
    }

}
