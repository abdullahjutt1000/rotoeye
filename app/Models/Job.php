<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Job extends Model {

    public $incrementing = false;
    protected $keyType = 'string';

	public function machines(){
        return $this->belongsToMany(Machine::class, 'records');
    }

    public function errors(){
        return $this->belongsToMany(Error::class, 'records');
    }

    public function users(){
        return $this->belongsToMany(Users::class, 'records', 'id', 'user_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,"product_id");
    }

    public function loginRecord(){
        return $this->hasOne(LoginRecord::class);
    }
}
