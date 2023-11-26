<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BusinessUnit extends Model {

	public function company(){
        return $this->belongsTo(Company::class);
    }

    public function departments(){
        return $this->hasMany(Department::class);
    }

    // Updatd by Abdullah 22-11-23 start

    public function shifts(){
        return $this->hasMany(Shift::class);
    }
    // Updatd by Abdullah 22-11-23 end

}
