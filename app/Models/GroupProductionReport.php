<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupProductionReport extends Model
{
    use HasFactory;
    
    protected $table = 'grp_dsb_production_report';
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
