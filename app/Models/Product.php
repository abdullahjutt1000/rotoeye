<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    public $incrementing = false;
    protected $keyType = 'string';

	public function jobs(){
        return $this->hasMany(Job::class,);
    }

    public function materialCombination(){
        return $this->belongsTo(MaterialCombination::class);
    }

    public function process(){
        return $this->belongsToMany(Process::class, 'process_structure');
    }

    public function structure(){
        return $this->belongsToMany(MaterialCombination::class, 'process_structure');
    }

    public function sleeves(){
        return $this->belongsToMany(Sleeve::class);
    }
}
