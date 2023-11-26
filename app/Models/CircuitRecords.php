<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircuitRecords extends Model
{
    protected $table = 'circuit_records';

    public static function save_record($num_id,$ldt,$mtr,$rpm,$sd,$sts=null,$rsi=null,$raw_string)
    {

        $previous=CircuitRecords::where("num_id","=",$num_id)->latest("LDT")->limit(1)->get();
        $record = new CircuitRecords();
        $record->num_id=$num_id;
        $record->LDT = $ldt;
        $record->Mtr=$mtr;
        $record->Rpm=$rpm;
        $record->status_log=$sts;
        $record->rsi=$rsi;
        $record->raw_string =$raw_string;
        $record->save();

        if(empty($previous[0]))
        {
           return $record;
        }
        return $previous[0];
    }
    public static function get_json_record($machine_id)
    {
        $previous=CircuitRecords::where("num_id","=",$machine_id)->latest("LDT")->limit(1)->get();

    }
    public static function plus_minus_percentage($percent,$cir_speed,$record_speed)
    {
        if(($record_speed-($record_speed*$percent)/100) <= $cir_speed && ($record_speed+($record_speed*$percent)/100) >= $cir_speed)
        {
            return false;
        }
        return true;
    }
}
