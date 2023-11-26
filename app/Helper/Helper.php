<?php

namespace App\Helper;
use App\Models\Record;
use App\Models\Users;
use App\Models\GroupProductionReport;
class Helper{

    public static function compare_last($request)
    {
        if(!is_file(base_path('machine_memory/memory.json'))){
            file_put_contents(base_path('machine_memory/memory.json'), json_encode(array("machines"=>[])));
        }
        $current = [
            "num_id"=>$request->route()->parameter('num_id'),
            "ldt"=>$request->route()->parameter('ldt'),
            "mtr"=>$request->route()->parameter('mtr'),
            "rpm"=>$request->route()->parameter('rpm')
        ];
        $previous=null;
        $jsonString = file_get_contents(base_path('machine_memory/memory.json'));
        $data = json_decode($jsonString, true);

        if(in_array($current["num_id"],array_column($data["machines"],"num_id")))
        {
            foreach ($data["machines"] as &$machine)
            {
                if($machine["num_id"] === $current["num_id"])
                {
                    $previous = $machine;
                    $machine = $current;
                }
            }
        }
        else
        {
            array_push($data["machines"],$current);
//            $previous=$current;
        }

        file_put_contents('machine_memory/memory.json', json_encode($data));
        return $previous;
    }
    public static function get_json_record($machine_id)
    {
        if(!is_file(base_path('machine_memory/memory.json'))){
            file_put_contents(base_path('machine_memory/memory.json'), json_encode(array("machines"=>[])));
        }

        $jsonString = file_get_contents(base_path('machine_memory/memory.json'));
        $data = json_decode($jsonString, true);
       foreach ($data['machines'] as $machine )
       {
           if($machine['num_id']==$machine_id)
           {
               return $machine;
           }
       }
       return null;
    }

    public static function plus_minus_percentage($percent,$cir_speed,$record_speed)
    {

        if(($record_speed-($record_speed*$percent)/100) <= $cir_speed && ($record_speed+($record_speed*$percent)/100) >= $cir_speed)
        {
            return false;
        }

        return true;
    }
    public static function getMachineUsers($machine_id,$date_range)
    {
        //dd($machine_id);
        $dateRange = explode(" - ",$date_range);
        $stdate = date('Y-m-d', strtotime($dateRange[0]));
        $endate = date('Y-m-d', strtotime($dateRange[1]));
       // $user  = Record ::with("user")->where ("machine_id",$machine_id)->groupBy("user_id")->pluck("user_id")->toArray();
       // $users = Users::whereIn("id",$user)->get();
       $startDateTime = $stdate;
      // $endDateTime = $endate;
       $endDateTime = date("Y-m-d", strtotime($endate . "+1 day")); 
       $results = GroupProductionReport::where('date', '>=', $startDateTime)
            ->where('date', '<=', $endDateTime)
            ->where('machine_id', '=', $machine_id)
            ->groupBy('operator_id')
            ->get(["operator_id","operator_name","machine_id"]); 
          //dd($results);
        return ($results ->count()>0)?$results :[];
    }

}
