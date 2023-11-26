<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Error extends Model {

	public function machines(){
        return $this->belongsToMany(Machine::class, 'records');
    }

    public function users(){
        return $this->belongsToMany(Users::class, 'records');
    }

    public function jobs(){
        return $this->belongsToMany(obs::class, 'records');

    }

    public function departments(){
        return $this->belongsToMany(Department::class);
    }

    public function categories(){
        return $this->belongsToMany(Categories::class);
    }

    public function downtimes(){
        return $this->hasMany(Downtime::class,'error_id');
    }
}
