<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Shift extends Model {

	public function company(){
        return $this->belongsTo(Company::class);
    }

    // updated by Abdullah 17/11/23 start

        public function businessunit(){
            return $this->belongsTo(Businessunit::class);
        }


    // updated by Abdullah 17/11/23 end


    public static function find_shift($machine,$from,$to){

        // Updated by Abdullah 22-11-23 start
	    // $shifts = $machine->section->department->businessUnit->company->shifts->sortByDesc('shift_number');
	    $shifts = $machine->section->department->businessUnit->shifts->sortByDesc('shift_number');
        // Updated by Abdullah 22-11-23 end


	    $get_shifts=[];

	    foreach ($shifts as $shift)
        {
            if(strtotime($from) >= strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d',strtotime($from)).' + '.$shift->min_started.' minutes'))))
            {
                array_push($get_shifts,$shift->shift_number);
                break;
            }
        }
        // Updated by Abdullah 22-11-23 start
        // $shifts = $machine->section->department->businessUnit->company->shifts->sortByDesc('shift_number');
        $shifts = $machine->section->department->businessUnit->shifts->sortByDesc('shift_number');
        // Updated by Abdullah 22-11-23 end

        foreach ($shifts as $shift)
        {
            if(strtotime($to) >= strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($from)).' + '.$shift->min_started.' minutes')))
                &&
                strtotime($to) <= strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($from)).' + '.$shift->min_ended.' minutes'))))
            {
                array_push($get_shifts,$shift->shift_number);
                break;
            }
        }
        if(empty(array_unique($get_shifts)))
        {
            array_push($get_shifts,$shifts[2]->shift_number);
        }
        // $shifts = $machine->section->department->businessUnit->company->shifts->sortBy('shift_number');
        // foreach ($shifts as $shift)
        // {
        //     if(strtotime($to) <= strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d',strtotime($to)).' + '.$shift->min_ended.' minutes'))))
        //     {
        //         array_push($get_shifts,$shift->shift_number);
        //         break;
        //     }
        // }
        return array_unique($get_shifts);
    }

    public static function which_shift($date,$shifts)
    {
        $time = explode(':', date('H:i:s', strtotime($date)));
        $minutes = (($time[0] * 60) + ($time[1]) + ($time[2] / 60));
        if ($shifts[0]->min_started > $minutes && $shifts[count($shifts)-1]->min_ended > $minutes) {
            $minutes = $minutes + 1440;
            $date = date('Y-m-d',strtotime($date.'-1 day'));
        }
        foreach ($shifts as $shift) {
            if($minutes>=$shift->min_started && $minutes<=$shift->min_ended)
            {
                $shift->date = $date;
                return $shift;
            }
        }
        return -1;
    }
}