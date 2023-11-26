<?php

namespace App\Http\Middleware;

use App\Models\Machine;
use App\Models\Record;
use App\Models\Records_From_Circuit;
use Closure;
use Illuminate\Http\Request;

class StoreDeviceString
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(date('Y', strtotime(substr($request->route()->parameter('ldt'),0,10) ." ".substr($request->route()->parameter('ldt'),11,8))) == date('Y')){
            $machine = Machine::where('sap_code', '=', $request->route()->parameter('num_id'))->first();
            if($machine){
                $latest_record = Record::where('machine_id', '=', $machine->id)->orderby('run_date_time', 'DESC')->limit(1)->get();
                $percentage_diff = abs((($latest_record[0]->speed / $machine->max_speed)*100)-(($request->route()->parameter('rpm') / $machine->max_speed)*100));
                $percentage_allow=2;
                if($percentage_diff>$percentage_allow)
                {
                    return $next($request);
                }
                else
                {
                    return response("speed is more/less %".$percentage_allow,406);
                }
            }
            else {
                return response("machine not found",400);
            }
        }
        else
        {
            return response("wrong date/time",400);
        }

        return response("Internal Server Error",500);
    }
}
