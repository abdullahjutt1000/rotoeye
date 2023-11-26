<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Department extends Model {

	public function businessUnit(){
        return $this->belongsTo(BusinessUnit::class);
    }

    public function sections(){
        return $this->hasMany(Section::class);
    }

    public function errorCodes(){
        return $this->belongsToMany(Error::class);
    }

}
