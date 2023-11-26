<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Section extends Model {

	public function department(){
        return $this->belongsTo(Department::class);
    }

    public function machines(){
        return $this->hasMany(Machine::class);
    }

    public function processes(){
        return $this->hasMany(Process::class);
    }
}
