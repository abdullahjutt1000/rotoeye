<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Machine extends Model {

	public function section(){
        return $this->belongsTo(Section::class);
    }

    public function rotoValues(){
        return $this->hasMany(RotoValue::class);
    }

    public function monthOperator(){
        return $this->hasOne(MonthOperator::class);
    }

    public function jobs(){
        return $this->belongsToMany(Job::class, 'records');
    }

    public function errors(){
        return $this->belongsToMany(Error::class, 'records');
    }

    public function users(){
        return $this->belongsToMany(Users::class, 'records', 'user_id', 'id');
    }

    public function loginRecord(){
        return $this->hasOne(LoginRecord::class);
    }

    public function allowedUsers(){
        return $this->belongsToMany(Users::class, 'machine_users', 'machine_id', 'user_id');
    }

    public function downtimes(){
        return $this->hasMany(Downtime::class,'machine_id');
    }

    public function machinerecords(){
        return $this->hasMany(Re::class,'machine_id');
    }

    public function circuitlogs(){
        return $this->hasMany(CircuitLogs::class,'machine_id');
    }

    public function sleeves(){
        return $this->belongsToMany(Sleeve::class, )->withPivot('speed');
    }
}
