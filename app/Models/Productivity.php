<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productivity extends Model
{
    use HasFactory;

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

}
