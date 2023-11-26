<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircuitLog extends Model
{
    use HasFactory;

    public function machines(){
        return $this->belongsTo(Machine::class,'machine_id');
    }

}
