<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Categories extends Model {

	public function errorcatCodes(){
        return $this->belongsToMany(Error::class);
    }

}
