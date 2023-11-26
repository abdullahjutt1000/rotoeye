<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_sleeve extends Model
{
    protected $table = 'product_sleeve';
    protected $fillable = ['product_id','sleeve_id'];

    use HasFactory;
}
