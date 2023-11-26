<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Process extends Model {


	public function section(){
        return $this->belongsTo(Section::class);
    }

    public function materialCombination($productNumber){
        return $this->belongsToMany(MaterialCombination::class, 'process_structure')->withPivot('product_id', 'color', 'adhesive')->wherePivot('product_id', '=', $productNumber)->get();
    }
}
