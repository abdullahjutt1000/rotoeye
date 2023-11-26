<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sleeve extends Model
{
    use HasFactory;

    public function machines(){
        return $this->belongsToMany(Machine::class, 'machine_sleeve', 'sleeve_id', 'machine_id')->withPivot('speed');;
    }

    public function products(){
        return $this->belongsToMany(Product::class, 'product_sleeve', 'sleeve_id', 'product_id');
    }
}
