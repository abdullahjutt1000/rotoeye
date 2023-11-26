<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class MaterialCombination extends Model {

    protected $table = 'material_combination';
	public function products(){
        return $this->hasMany(Product::class);
    }

    public function process(){
        return $this->belongsToMany(Process::class, 'process_structure');
    }

}
