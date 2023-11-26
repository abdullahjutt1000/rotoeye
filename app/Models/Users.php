<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Users extends Model {

	protected $table = 'users';

    public function machines(){
        return $this->belongsToMany(Machine::class, 'records');
    }

    public function jobs(){
        return $this->belongsToMany(Job::class, 'records');
    }

    public function errors(){
        return $this->belongsToMany(Error::class);
    }

    public function allowedMachines(){
        return $this->belongsToMany(Machine::class, 'machine_users', 'user_id')->whereNull('machines.is_disabled')->orderby('machines.sap_code', 'ASC');
    }
}
