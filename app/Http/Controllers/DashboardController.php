<?php namespace App\Http\Controllers;



use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\CircuitRecords;
use App\Models\Error;
use App\Models\Job;
use App\Models\LoginRecord;

use App\Models\MaterialCombination;
use App\Models\Machine;
use App\Models\Patient;
use App\Models\Record;
use App\Models\Records_From_Circuit;
use App\Models\RhRecords;
use App\Models\Shift;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Users;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PhpSpec\Exception\Exception;use voku\helper\ASCII;
use App\Helper\Helper;
use Illuminate\Http\Reponse;


class DashboardController extends Controller {

    /**
     * Display a listing of the resource.
     *
     */


    public function index($id)
    {
        $user_id = Session::get('user_id');

        if($user_id){
            if(Session::get('rights') == 0){
                $data['layout'] = 'web-layout';
            }
            elseif(Session::get('rights') == 1){
                $data['layout'] = 'admin-layout';
            }
            elseif(Session::get('rights') == 2){
                $data['layout'] = 'power-user-layout';
            }
            elseif(Session::get('rights') == 3){
                $data['layout'] = 'reporting-user-layout';
            }
        // Addding new right in the controller start
            elseif(Session::get('rights') == 4){
                $data['layout'] = 'simple-layout';
            }
        // Addding new right in the controller start

            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $machine_id = Crypt::decrypt($id);
            $data['machine'] = Machine::find($machine_id);
            if($data['machine']){
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->first();
                if(count(array($loginRecord)) == 0){
                    Session::flash('error', 'No login record found against selected machine. Please login through operator rights first or select a job that is running.');
                    return redirect('select/job'.$id);
                }
                else{
                    if(Session::get('rights') == 0){
                        $data['user'] = Users::find($loginRecord->user_id);
                    }
                    else{
                        $data['user'] = Users::find(Session::get('user_id'));
                    }

                    $data['running_job'] = Job::find($loginRecord->job_id);

                    $lastDateTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - '.$loginRecord->machine->graph_span.' hours'));
                   // return $lastDateTime;
                    $data['record'] = Record::where('machine_id', '=', $machine_id)->latest('run_date_time')->first();
                    //return $data['record'];
                    if(!$data['record']){
                        $record = new Record();
                        $record->user_id = $loginRecord->user_id;
                        $record->error_id = 500;
                        $record->job_id = $loginRecord->job_id;
                        $record->machine_id = $loginRecord->machine_id;
                        $record->speed = 0;
                        $record->length = 0;
                        $record->run_date_time = date('Y-m-d H:i:s');
                        $record->process_id = $loginRecord->process_id;
                        $record->save();
                    }
                    $data['record'] = Record::where('machine_id', '=', $machine_id)->latest('run_date_time')->first();
                    //return $data['record'];

                    $data['graphRecords'] = Record::select('speed','length', 'run_date_time')->where('machine_id', '=', $machine_id)->where('run_date_time', '>=', $lastDateTime)->orderby('run_date_time', 'ASC')->get();

                    $data['rotoErrors'] = Error::whereNotIn('id', [2,500])->whereHas('departments', function($query) use ($data){
                        $query->where('department_id', '=', $data['machine']->section->department->id);
                    })->get();

                    return view('roto.dashboard', $data);
                }
            }
            else{
                Session::flash("error", "Machine is not valid. Please contact System Administrator.");
                return redirect('/');
            }
        }
        else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }

    public function productionDashboard(){
        $user_id = Session::get('user_id');
        if($user_id){
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $data['layout'] = 'production-dashboard-layout';
            $data['user'] = Users::find(Session::get('user_id'));
            return view('roto.production-dashboard', $data);
        }
        else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }

    /// mycode
    public function jsnquery($str, Request $request){
       //dd($str);
        $string1  = str_replace("'", "", $str);
        $string = preg_replace('/}{/', '},{', $string1);

            // Wrap the string in square brackets to create a JSON array
            $jsonString = '[' . $string . ']';

            // Decode the JSON string
            $jsonArray = json_decode($jsonString, true);
           // dd( $jsonArray);
           if($jsonArray){
                foreach($jsonArray as $value){


                    $num_id = $value['Num'];
                    $ldt = $value['LDT'];
                    $mtr = $value['Cp'];
                    $rpm = $value['Ppm'];
                    $sd = $value['Sd'];
                    $sts =$value['Sts'];
                    $rsi =$value['Rsi'];

                    //dd($sd);
                    $this->live_compressed($num_id, $ldt, $mtr, $rpm,$sd,$sts,$rsi ,$request);
                } // looop
                return  'Accepted';
            }
            else{
                return  response()->json('wrong json string',200);
            }

    }


    public function live_compressed($num_id, $ldt, $mtr, $rpm,$sd,$sts,$rsi, Request $request){
        try{
            $raw_string = 'Raw String: api/Num/'.$num_id.'/LDT/'.$ldt.'/Mtr/'.$mtr.'/Rpm/'.$rpm.'/Sd/'.$sd.'/Sts/'.$sts.'/Rsi/'.$rsi;
            //dd($raw_string);
            $ldt = substr($ldt,0,10) ." ".substr($ldt,11,8);
            $machine = Machine::where('sap_code', '=', $num_id)->first();

            if(!empty($machine)){
                $shifts = $machine->section->department->businessUnit->company->shifts;
                $logger = new LoggerController($machine);
                //Sd Update
                try {
                    Machine::where('sap_code','=',$machine->sap_code)->update(['sd_status'=>$sd]);
                }
                catch (\Exception $e)
                {
                    $logger->log('------------------ Exception While Changing Sd Status ------------------', $machine);
                    $logger->log($e->getMessage(), $machine);
                    $logger->log('------------------ Exception While Changing Sd Status ------------------', $machine);

                }
                //Sd Update

                //Change IP
                // if($machine->ip != $request->ip() && $machine->ip_change_on_off == 1)
                // {
                //     $logger->log('------------------ Start Changing Ip Address - Live Api ------------------', $machine);
                //     $data['previous_ip'] = $machine->ip;
                //     $data['changed_ip'] = $request->ip();
                //     $data['machine'] = $machine;
                //     Machine::where('sap_code','=',$machine->sap_code)->update(['ip'=>$request->ip()]);
                //     try {
                //         Mail::send('emails.ip-update', $data, function ($message) use ($data) {
                //             $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                //                 ->cc('haseeb.khan@packages.com.pk', 'Haseeb Khan')
                //                 ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                //                 ->subject("RotoEye Cloud - IP Changed");
                //         });
                //     }
                //     catch (\Exception $e)
                //     {
                //         $logger->log('------------------ Exception While Changing Ip Address - Live Api ------------------', $machine);
                //         $logger->log($e->getMessage(), $machine);
                //         $logger->log('------------------ Exception While Changing Ip Address - Live Api ------------------', $machine);
                //     }
                //     $logger->log('------------------ End Changing Ip Address - Live Api ------------------', $machine);
                // }
                //Change IP

                if(date('Y', strtotime($ldt)) == date('Y')){

                    $latest_record = Record::where('machine_id', '=', $machine->id)->orderby('run_date_time', 'DESC')->limit(1)->get();
                    $loginRecord = LoginRecord::where('machine_id', '=', $machine->id)->first();

                    if($machine->time_uom == 'Hr'){
                        $speed = $rpm*$machine->roller_circumference*60;
                    }
                    elseif($machine->time_uom == 'Sec'){
                        $speed = $rpm*$machine->roller_circumference/60;
                    }
                    else{
                        $speed = $rpm*$machine->roller_circumference;
                    }
                    $length = $mtr*$machine->roller_circumference;

                    if(!$latest_record->isEmpty())
                    {

                        $previous = CircuitRecords::save_record($machine->sap_code, $ldt, $length, $speed,$sd,$sts,$rsi,$raw_string);
                        $percentage_allow=2.6;
                        if(CircuitRecords::plus_minus_percentage($percentage_allow, $speed, $latest_record[0]->speed ) || ($latest_record[0]['user_id']!=$loginRecord["user_id"] || $latest_record[0]['job_id']!=$loginRecord["job_id"] || $latest_record[0]['process_id']!=$loginRecord["process_id"])||(Shift::which_shift($latest_record[0]->run_date_time,$shifts)->shift_number!=Shift::which_shift($ldt,$shifts)->shift_number ||  date('Y-m-d',strtotime(Shift::which_shift($latest_record[0]->run_date_time,$shifts)->date))!= date('Y-m-d',strtotime(Shift::which_shift($ldt,$shifts)->date))))

                        // if(CircuitRecords::plus_minus_percentage($percentage_allow, $speed, $latest_record[0]->speed ) || ($latest_record[0]['user_id']!=$loginRecord["user_id"] || $latest_record[0]['job_id']!=$loginRecord["job_id"] || $latest_record[0]['process_id']!=$loginRecord["process_id"]))
                        {
                            if($previous != null)
                            {
                                if($previous->LDT != $latest_record[0]["run_date_time"])
                                {

                                    if($this->liveNew($machine, $previous->LDT, $previous->Mtr , $previous->Rpm,$latest_record[0],$latest_record, $logger))
                                    {
                                        if($this->liveNew($machine, $ldt, $length, $speed,$loginRecord,$latest_record, $logger))
                                        {
                                            return response("Accepted", 404);
                                        }
                                        else
                                        {
                                            return response("Not Accepted", 500);
                                        }
                                    }
                                }
                                else
                                {
                                    if($this->liveNew($machine, $ldt, $length, $speed,$loginRecord,$latest_record, $logger))
                                    {
                                        return response("Accepted", 200);
                                    }
                                    else
                                    {
                                        return response("Not Accepted", 500);
                                    }
                                }
                            }
                            else
                            {
                                $logger->log('------------------ Previous Entry Null Exception ------------------', $machine);
                                $logger->log($raw_string, $machine);
                                $logger->log('------------------ Previous Entry Null Exception ------------------', $machine);
                            }
                        }
                        else
                        {
                            $logger->log('------------------ speed is more/less % ------------------', $machine);
                            $logger->log($raw_string, $machine);
                            $logger->log('------------------ speed is more/less % ------------------', $machine);
                            return response('speed is more/less '.$percentage_allow.'%',200);
                        }
                    }
                    else
                    {
                        CircuitRecords::save_record($machine->sap_code, $ldt, $length, $speed,$sd,$sts,$rsi,$raw_string);
                        if($this->liveNew($machine, $ldt, $length, $speed,$loginRecord,$latest_record, $loginRecord))
                        {
                            return response("Accepted",200);
                        }
                    }
                }
                else
                {
                    $logger->log('------------------ Circuit Date & Time Wrong Entry ------------------', $machine);
                    $logger->log($raw_string, $machine);
                    $logger->log('------------------ Circuit Date & Time Wrong Entry ------------------', $machine);
                    return 'Wrong Date And Time!';
                }

            }
            else {
                Log::info('------------------ Machine Not Found Exception ------------------');
                Log::info($raw_string.'/'.$sd);
                Log::info('------------------ Machine Not Found Exception ------------------');
                return 'Machine Not Found!';
            }
        }
        catch (\Exception $e)
        {
            Log::info('------------------ Live_Compressed Exception ------------------');
            Log::info($raw_string);
            Log::info($e->getMessage());
            Log::info('------------------ Live_Compressed Exception ------------------');
        }
    }
    public function liveNew($machine, $date_time, $length, $speed, $loginRecord, $latest_record,$logger){
        $record = Record::where('machine_id', '=', $machine->id)->where('run_date_time','=', $date_time)->get();
        if(count($record) == 0){
            if($speed < $machine->waste_speed){
                if(empty($latest_record[0]))
                {
                    $record = new Record();
                    $record->user_id = $loginRecord->user_id;
                    $record->error_id = 500;
                    $record->job_id = $loginRecord->job_id;
                    $record->machine_id = $loginRecord->machine_id;
                    $record->process_id =  $loginRecord->process_id;
                    $record->speed = $speed;
                    $record->length =$length;
                    $record->run_date_time = $date_time;
                    $record->save();
                }
                else
                {
                    $record = new Record();
                    $record->user_id = $latest_record[0]['user_id']!=$loginRecord["user_id"]? $loginRecord->user_id :  $latest_record[0]['user_id'] ;
                    $record->error_id = 500;
                    $record->job_id = $latest_record[0]['job_id']!=$loginRecord["job_id"] ? $loginRecord->job_id : $latest_record[0]['job_id'] ;
                    $record->machine_id = $loginRecord->machine_id;
                    $record->process_id = $latest_record[0]['process_id']!=$loginRecord["process_id"] ? $loginRecord->process_id :   $latest_record[0]['process_id'];
                    $record->speed = $speed;
                    $record->length =$length;
                    $record->run_date_time = $date_time;
                    $record->save();
                }

            }
            else{
                if(!empty($latest_record[0])){
                    $latest = Record::where('machine_id', '=', $machine->id)->orderby('run_date_time', 'DESC')->limit(1)->get();
                    if ($latest[0]->error_id == 500) {
                        $last_running = Record::where('machine_id', '=', $machine->id)->where('error_id', '=', '2')->orderby('run_date_time', 'DESC')->limit(1)->get();
                        if(count($last_running)>0){
                            $duration = (strtotime($latest[0]->run_date_time) - strtotime($last_running[0]->run_date_time));
                            $auto_downtime = null;

                            foreach ($machine->downtimes->sortByDesc('error_time') as $downtime) {
                                if ($duration <= ($downtime->error_time * 60)) {
                                    $auto_downtime = $downtime;
                                }
                            }
                            if ($auto_downtime) {
                                $logger->log('------------------ Start Live Update AutoDowntime ------------------', $machine);
                                Record::where('run_date_time', '>', $last_running[0]->run_date_time)
                                    ->where('run_date_time', '<=', $latest[0]->run_date_time)
                                    ->where('machine_id', '=', $machine->id)->update(['error_id'=>$auto_downtime->error_id, 'err_comments'=>$auto_downtime->err_comments]);
                                $logger->log('------------------ From: ' . $last_running[0]->run_date_time . ' To:' . $latest[0]->run_date_time . ' error_id: ' . $auto_downtime->error_id . ' ------------------', $machine);
                                $logger->log('------------------ End Live Update AutoDowntime ------------------', $machine);

                            }

                        }
                    }
                }
                $record = new Record();
                $record->user_id = $record->user_id = $latest_record[0]['user_id']!=$loginRecord["user_id"]?   $loginRecord->user_id : $latest_record[0]['user_id'];
                $record->error_id = 2;
                $record->job_id = $latest_record[0]['job_id']!=$loginRecord["job_id"] ? $loginRecord->job_id: $latest_record[0]['job_id'];
                $record->machine_id = $loginRecord->machine_id;
                $record->process_id = $latest_record[0]['process_id']!=$loginRecord["process_id"] ? $loginRecord->process_id : $latest_record[0]['process_id']  ;
                $record->speed = $speed;
                $record->length = $length;
                $record->run_date_time = $date_time;
                $record->save();
            }
            return true;
        }
        else{
            $logger->log('------------------ Duplicate Entry ------------------', $machine);
            $logger->log('Raw String: '.$machine->sap_code.'/'.$date_time.'/'.$length.'/'.$speed, $machine);
            $logger->log('------------------ Duplicate Entry ------------------', $machine);
            return true;
        }

        return false;
    }





    ///




    public function live_with_sd($num_id, $ldt, $mtr, $rpm,$sd, Request $request){
        $machine = Machine::where('sap_code', '=', $num_id)->first();

        $logger = new LoggerController($machine);
        $dateTime = $ldt;
        $date = substr($dateTime,0,10);
        $time = substr($dateTime,11,8);

        if(date('Y', strtotime($date)) == date('Y')){
            if($machine){
                $record = Record::where('machine_id', '=', $machine->id)->where('run_date_time','=', $date.' '.$time)->get();
                if(count($record) == 0){
                    if($machine->time_uom == 'Hr'){
                        $speed = $rpm*$machine->roller_circumference*60;
                    }
                    elseif($machine->time_uom == 'Sec'){
                        $speed = $rpm*$machine->roller_circumference/60;
                    }
                    else{
                        $speed = $rpm*$machine->roller_circumference;
                    }
                    $loginRecord = LoginRecord::where('machine_id', '=', $machine->id)->first();
                    if($speed < $machine->waste_speed){
                        $record = new Record();
                        $record->user_id = $loginRecord->user_id;
                        $record->error_id = 500;
                        $record->job_id = $loginRecord->job_id;
                        $record->machine_id = $loginRecord->machine_id;
                        $record->process_id = $loginRecord->process_id;
                        $record->speed = $speed;
                        $record->length = $mtr*$machine->roller_circumference;
                        $record->run_date_time = $date.' '.$time;
                        $record->save();
                    }
                    else{
                        $latest_record = Record::where('machine_id', '=', $machine->id)->orderby('run_date_time', 'DESC')->limit(1)->get();

                        if ($latest_record[0]->error_id == 500) {
                            $last_running = Record::where('machine_id', '=', $machine->id)->where('error_id', '=', '2')->orderby('run_date_time', 'DESC')->limit(1)->get();

                            if(count($last_running)>0){
                                $duration = (strtotime($latest_record[0]->run_date_time) - strtotime($last_running[0]->run_date_time));
                                $auto_downtime = null;

                                foreach ($machine->downtimes->sortByDesc('error_time') as $downtime) {
                                    if ($duration <= ($downtime->error_time * 60)) {
                                        $auto_downtime = $downtime;
                                    }
                                }
                                if ($auto_downtime) {
                                    $logger->log('------------------ Start Live Update AutoDowntime ------------------', $machine);
                                    Record::where('run_date_time', '>', $last_running[0]->run_date_time)
                                        ->where('run_date_time', '<=', $latest_record[0]->run_date_time)
                                        ->where('machine_id', '=', $machine->id)->update(['error_id'=>$auto_downtime->error_id, 'err_comments'=>$auto_downtime->err_comments]);
                                    $logger->log('------------------ From: ' . $last_running[0]->run_date_time . ' To:' . $latest_record[0]->run_date_time . ' error_id: ' . $auto_downtime->error_id . ' ------------------', $machine);
                                    $logger->log('------------------ End Live Update AutoDowntime ------------------', $machine);

                                }

                            }
                        }

                        $record = new Record();
                        $record->user_id = $loginRecord->user_id;
                        $record->error_id = 2;
                        $record->job_id = $loginRecord->job_id;
                        $record->machine_id = $loginRecord->machine_id;
                        $record->process_id = $loginRecord->process_id;
                        $record->speed = $speed;
                        $record->length = $mtr*$machine->roller_circumference;
                        $record->run_date_time = $date.' '.$time;
                        $record->save();
                    }

                    $record_from_circuit = new Records_From_Circuit();
                    $record_from_circuit->raw_string = 'Raw String: '.$num_id.'/'.$ldt.'/'.$mtr.'/'.$rpm.'/'.$sd;
                    $record_from_circuit->save();
                    try {
                        Machine::where('sap_code','=',$machine->sap_code)->update(['sd_status'=>$sd]);
                    }
                    catch (\Exception $e)
                    {
                        $logger->log('------------------ Exception While Changing Sd Status ------------------', $machine);
                        $logger->log($e->getMessage(), $machine);
                        $logger->log('------------------ Exception While Changing Sd Status ------------------', $machine);

                    }

                    if($machine->ip != $request->ip() && $machine->ip_change_on_off == '1')
                    {
                        $logger->log('------------------ Start Changing Ip Address - Live Api ------------------', $machine);
                        $data['previous_ip'] = $machine->ip;
                        $data['changed_ip'] = $request->ip();
                        $data['machine'] = $machine;
                        Machine::where('sap_code','=',$machine->sap_code)->update(['ip'=>$request->ip()]);

                        try {
                            Mail::send('emails.ip-update', $data, function ($message) use ($data) {
                                $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                                    ->cc('haseeb.khan@packages.com.pk', 'Haseeb Khan')
                                    ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                                    ->subject("RotoEye Cloud - IP Changed");
                            });
                        }
                        catch (\Exception $e)
                        {
                            $logger->log('------------------ Exception While Changing Ip Address - Live Api ------------------', $machine);
                            $logger->log($e->getMessage(), $machine);
                            $logger->log('------------------ Exception While Changing Ip Address - Live Api ------------------', $machine);
                        }
                        $logger->log('------------------ End Changing Ip Address - Live Api ------------------', $machine);
                    }

                    return 'Accepted';
                }
                else{
                    $logger->log('------------------ Duplicate Entry ------------------', $machine);
                    $logger->log('Raw String: '.$num_id.'/'.$ldt.'/'.$mtr.'/'.$rpm, $machine);
                    $logger->log('IP Address: '.$request->getClientIp(), $machine);
                    $logger->log('------------------ Duplicate Entry ------------------', $machine);
                }

            }
            else{
                return 'Not Found!';
            }
        }
        else{
            $logger->log('------------------ Circuit Date & Time Wrong Entry ------------------', $machine);
            $logger->log('Raw String: '.$num_id.'/'.$ldt.'/'.$mtr.'/'.$rpm, $machine);
            $logger->log('------------------ Circuit Date & Time Wrong Entry ------------------', $machine);
            return 'Not Found!';
        }
    }

    public function live($num_id, $ldt, $mtr, $rpm, Request $request){
        $machine = Machine::where('sap_code', '=', $num_id)->first();
        $logger = new LoggerController($machine);
        $dateTime = $ldt;
        $date = substr($dateTime,0,10);
        $time = substr($dateTime,11,8);

        if(date('Y', strtotime($date)) == date('Y')){
            if($machine){
                $record = Record::where('machine_id', '=', $machine->id)->where('run_date_time','=', $date.' '.$time)->get();
                if(count($record) == 0){
                    if($machine->time_uom == 'Hr'){
                        $speed = $rpm*$machine->roller_circumference*60;
                    }
                    elseif($machine->time_uom == 'Sec'){
                        $speed = $rpm*$machine->roller_circumference/60;
                    }
                    else{
                        $speed = $rpm*$machine->roller_circumference;
                    }
                    $loginRecord = LoginRecord::where('machine_id', '=', $machine->id)->first();
                    if($speed < $machine->waste_speed){
                        $record = new Record();
                        $record->user_id = $loginRecord->user_id;
                        $record->error_id = 500;
                        $record->job_id = $loginRecord->job_id;
                        $record->machine_id = $loginRecord->machine_id;
                        $record->process_id = $loginRecord->process_id;
                        $record->speed = $speed;
                        $record->length = $mtr*$machine->roller_circumference;
                        $record->run_date_time = $date.' '.$time;
                        $record->save();
                    }
                    else{
                        $latest_record = Record::where('machine_id', '=', $machine->id)->orderby('run_date_time', 'DESC')->limit(1)->get();
                        if ($latest_record[0]->error_id == 500) {
                            $last_running = Record::where('machine_id', '=', $machine->id)->where('error_id', '=', '2')->orderby('run_date_time', 'DESC')->limit(1)->get();
                            if(count($last_running)>0){
                                $duration = (strtotime($latest_record[0]->run_date_time) - strtotime($last_running[0]->run_date_time));
                                $auto_downtime = null;

                                foreach ($machine->downtimes->sortByDesc('error_time') as $downtime) {
                                    if ($duration <= ($downtime->error_time * 60)) {
                                        $auto_downtime = $downtime;
                                    }
                                }
                                if ($auto_downtime) {
                                    $logger->log('------------------ Start Live Update AutoDowntime ------------------', $machine);
                                    Record::where('run_date_time', '>', $last_running[0]->run_date_time)
                                        ->where('run_date_time', '<=', $latest_record[0]->run_date_time)
                                        ->where('machine_id', '=', $machine->id)->update(['error_id'=>$auto_downtime->error_id, 'err_comments'=>$auto_downtime->err_comments]);
                                    $logger->log('------------------ From: ' . $last_running[0]->run_date_time . ' To:' . $latest_record[0]->run_date_time . ' error_id: ' . $auto_downtime->error_id . ' ------------------', $machine);
                                    $logger->log('------------------ End Live Update AutoDowntime ------------------', $machine);

                                }

                            }
                        }


                        $record = new Record();
                        $record->user_id = $loginRecord->user_id;
                        $record->error_id = 2;
                        $record->job_id = $loginRecord->job_id;
                        $record->machine_id = $loginRecord->machine_id;
                        $record->process_id = $loginRecord->process_id;
                        $record->speed = $speed;
                        $record->length = $mtr*$machine->roller_circumference;
                        $record->run_date_time = $date.' '.$time;
                        $record->save();
                    }
                    $record_from_circuit = new Records_From_Circuit();
                    $record_from_circuit->raw_string = 'Raw String: '.$num_id.'/'.$ldt.'/'.$mtr.'/'.$rpm;
                    $record_from_circuit->save();

                    if($machine->ip != $request->ip() && $machine->ip_change_on_off == 0)
                    {
                        $logger->log('------------------ Start Changing Ip Address - Live Api ------------------', $machine);
                        $data['previous_ip'] = $machine->ip;
                        $data['changed_ip'] = $request->ip();
                        $data['machine'] = $machine;
                        Machine::where('sap_code','=',$machine->sap_code)->update(['ip'=>$request->ip()]);

                        try {
                            Mail::send('emails.ip-update', $data, function ($message) use ($data) {
                                $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                                    ->cc('haseeb.khan@packages.com.pk', 'Haseeb Khan')
                                    ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                                    ->subject("RotoEye Cloud - IP Changed");
                            });
                        }
                        catch (\Exception $e)
                        {
                            $logger->log('------------------ Exception While Changing Ip Address - Live Api ------------------', $machine);
                            $logger->log($e->getMessage(), $machine);
                            $logger->log('------------------ Exception While Changing Ip Address - Live Api ------------------', $machine);
                        }
                        $logger->log('------------------ End Changing Ip Address - Live Api ------------------', $machine);
                    }


                    return 'Accepted';
                }
                else{
                    $logger->log('------------------ Duplicate Entry ------------------', $machine);
                    $logger->log('Raw String: '.$num_id.'/'.$ldt.'/'.$mtr.'/'.$rpm, $machine);
                    $logger->log('IP Address: '.$request->getClientIp(), $machine);
                    $logger->log('------------------ Duplicate Entry ------------------', $machine);
                }
            }
            else{
                return 'Not Found!';
            }
        }
        else{
            $logger->log('------------------ Circuit Date & Time Wrong Entry ------------------', $machine);
            $logger->log('Raw String: '.$num_id.'/'.$ldt.'/'.$mtr.'/'.$rpm, $machine);
            $logger->log('------------------ Circuit Date & Time Wrong Entry ------------------', $machine);
            return 'Not Found!';
        }
    }

    public function liveRH($rh_num_id, $rdt, $rh_value, $temp_value, Request $request){
        $machine = Machine::where('sap_code', '=', $rh_num_id)->first();
        $dateTime = date('Y-m-d H:i:s', strtotime($rdt));
        if(isset($machine)){
            $rhRecord = new RhRecords();
            $rhRecord->machine_id = $machine->id;
            $rhRecord->date_time = $dateTime;
            $rhRecord->rh_value = $rh_value;
            $rhRecord->temp_value = $temp_value;
            $rhRecord->save();

            Log::info('------------------ RH LOGGER ------------------');
            Log::info('Raw String: '.$rh_num_id.'/'.$rdt.'/'.$rh_value.'/'.$temp_value);
            Log::info('IP Address: '.$request->getClientIp());
            Log::info('------------------ RH LOGGER ------------------');

            return 'NRCS:'.$rhRecord->id.'!';
        }
    }

   /* public function getRecord(Request $request){

        $user_id = Session::get('user_id');
        if($user_id){
            $data = [];
            $productionDashboardData = [];
            if($request->input('machine')){
                $machine_id = Crypt::decrypt($request->input('machine'));
                $data['record'] = Record::select('speed', 'length', 'run_date_time', 'user_id', 'job_id', 'process_id', 'error_id')
                    ->with('user', 'job.product', 'process', 'error')
                    ->where('machine_id', '=', $machine_id)
                    ->latest('run_date_time')
                    ->first();

                $mac = Machine::find($machine_id);

                $recent = CircuitRecords::where("num_id","=",$mac->sap_code)->latest("LDT")->limit(1)->get();
                if(!empty($recent[0]))
                {
                    $data["record"]->run_date_time = $recent[0]->LDT;
                    $data["record"]->speed = $recent[0]->Rpm;
                    $data["record"]->length =$recent[0]->Mtr;
                }

                $difference = $this->calculateMinutes(date("Y-m-d H:i:s"), $data['record']->run_date_time);
                if($difference > 15){
                    $data['status'] = 'Not Live';
                }
                else{
                    $data['status'] = 'Live';
                }

                $data['lastUpdatedDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                $data['lastUpdatedTime'] = date('H:i:s', strtotime($data['record']->run_date_time));

                if(strtotime($data['lastUpdatedDate']) < strtotime(date('Y-m-d').' - 1 day')){
                    $data['statusDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                    $data['statusTime'] = date('H:i', strtotime($data['record']->run_date_time));
                    $data['statusCode'] = $data['record']->error->name;
                }
                else{
                    $data['error'] = Record::select('run_date_time', 'error_id')
                        ->where('machine_id', '=', $machine_id)
                        ->whereRaw('error_id != '.$data['record']->error_id)
                        ->latest('run_date_time')->first();
                    if($data['error']){
                        $data['statusDate'] = date('d M, Y', strtotime($data['error']->run_date_time));
                        $data['statusTime'] = date('H:i', strtotime($data['error']->run_date_time));
                        $data['statusCode'] = $data['record']->error->name;
                    }
                }

                if($data['record']->process){

                    $data['substrate'] = $data['record']->process->materialCombination($data['record']->job->product->id)[0]->name;
                }
            }
            else{
                $machines = $request->input('machines_arr');


                foreach($machines as $machine){
                    $machine_id  = Crypt::decrypt($machine);
                    $data['machineID'] = $machine_id;

                    $data['record'] = Record::select('machine_id','speed', 'length', 'run_date_time', 'user_id', 'job_id', 'process_id', 'error_id')
                        ->with('user', 'job.product', 'process', 'error')
                        ->where('machine_id', '=', $machine_id)
                        ->latest('run_date_time')
                        ->first();
                    $mac = Machine::find($machine_id);
                    $recent = CircuitRecords::where("num_id","=",$mac->sap_code)->latest("LDT")->limit(1)->get();
                    if(!empty($recent[0]))
                    {
                        $data["record"]->run_date_time = $recent[0]->LDT;
                        $data["record"]->speed = $recent[0]->Rpm;
                        $data["record"]->length =$recent[0]->Mtr;
                    }

                    if($data['record']){
                        $difference = $this->calculateMinutes(date("Y-m-d H:i:s"), $data['record']->run_date_time);
                        if($difference > 15){
                            $data['status'] = 'Not Live';
                        }
                        else{
                            $data['status'] = 'Live';
                        }

                        $data['lastUpdatedDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                        $data['lastUpdatedTime'] = date('H:i:s', strtotime($data['record']->run_date_time));

                        if(strtotime($data['lastUpdatedDate']) < strtotime(date('Y-m-d').' - 1 day')){
                            $data['statusDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                            $data['statusTime'] = date('H:i', strtotime($data['record']->run_date_time));
                            $data['statusCode'] = $data['record']->error->name;
                        }
                        else{
                            $data['error'] = Record::select('run_date_time', 'error_id')
                                ->where('machine_id', '=', $machine_id)
                                ->whereRaw('error_id != '.$data['record']->error_id)
                                ->latest('run_date_time')->first();
                            if($data['error']){
                                $data['statusDate'] = date('d M, Y', strtotime($data['error']->run_date_time));
                                $data['statusTime'] = date('H:i', strtotime($data['error']->run_date_time));
                                $data['statusCode'] = $data['record']->error->name;
                            }
                        }
                        if($data['record']->process){
                            $data['substrate'] = $data['record']->process->materialCombination($data['record']->job->product->id)[0]->name;
                        }


                        array_push($productionDashboardData, $data);
                    }
                }
                return response(json_encode($productionDashboardData), 200);
            }


            return response(json_encode($data),200);
        }
        else{
            return response(json_encode("Session Out"), 505);
        }
    }*/

     public function getRecord(Request $request){

        $user_id = Session::get('user_id');
        if($user_id){
            $data = [];
            $productionDashboardData = [];
            if($request->input('machine')){
                $machine_id = Crypt::decrypt($request->input('machine'));
                $data['record'] = Record::select('speed', 'length', 'run_date_time', 'user_id', 'job_id', 'process_id', 'error_id')
                    ->with('user', 'job.product', 'process', 'error')
                    ->where('machine_id', '=', $machine_id)
                    ->latest('run_date_time')
                    ->first();

                $mac = Machine::find($machine_id);
                $recent = CircuitRecords::where("num_id","=",$mac->sap_code)->latest("LDT")->limit(1)->get();
                // old
                // if(!empty($recent[0]))
                // {
                //     $data["record"]->run_date_time = $recent[0]->LDT;
                //     $data["record"]->speed = $recent[0]->Rpm;
                //     $data["record"]->length =$recent[0]->Mtr;
                // }
                // old
                $difference = $this->calculateMinutes(date("Y-m-d H:i:s"), $data['record']->run_date_time);
                if($difference > 15){
                    //////mine
                    if(!empty($recent[0]))
                    {
                        $data["record"]->run_date_time = $recent[0]->LDT;
                        $data["record"]->speed = $recent[0]->Rpm;
                        $data["record"]->length =$recent[0]->Mtr;
                        $difference = $this->calculateMinutes(date("Y-m-d H:i:s"), $data['record']->run_date_time);
                        if($difference > 15){
                            $data['status'] = 'Not Live';

                        }else{
                            $data['status'] = 'Live';
                        }
                    }else{
                        $data['status'] = 'Not Live';

                    }
                    /////end mine
                   // $data['status'] = 'Not Live';  /// old
                }
                else{
                    $data['status'] = 'Live';
                }

                $data['lastUpdatedDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                $data['lastUpdatedTime'] = date('H:i:s', strtotime($data['record']->run_date_time));

                if(strtotime($data['lastUpdatedDate']) < strtotime(date('Y-m-d').' - 1 day')){
                    $data['statusDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                    $data['statusTime'] = date('H:i', strtotime($data['record']->run_date_time));
                    $data['statusCode'] = $data['record']->error->name;
                }
                else{
                    $data['error'] = Record::select('run_date_time', 'error_id')
                        ->where('machine_id', '=', $machine_id)
                        ->whereRaw('error_id != '.$data['record']->error_id)
                        ->latest('run_date_time')->first();
                    if($data['error']){
                        $data['statusDate'] = date('d M, Y', strtotime($data['error']->run_date_time));
                        $data['statusTime'] = date('H:i', strtotime($data['error']->run_date_time));
                        $data['statusCode'] = $data['record']->error->name;
                    }
                }

                if($data['record']->process){

                    $data['substrate'] = $data['record']->process->materialCombination($data['record']->job->product->id)[0]->name;
                }
            }
            else{
                $machines = $request->input('machines_arr');


                foreach($machines as $machine){
                    $machine_id  = Crypt::decrypt($machine);
                    $data['machineID'] = $machine_id;

                    $data['record'] = Record::select('machine_id','speed', 'length', 'run_date_time', 'user_id', 'job_id', 'process_id', 'error_id')
                        ->with('user', 'job.product', 'process', 'error')
                        ->where('machine_id', '=', $machine_id)
                        ->latest('run_date_time')
                        ->first();
                    $mac = Machine::find($machine_id);
                    $recent = CircuitRecords::where("num_id","=",$mac->sap_code)->latest("LDT")->limit(1)->get();
                    // if(!empty($recent[0]))
                    // {
                    //     $data["record"]->run_date_time = $recent[0]->LDT;
                    //     $data["record"]->speed = $recent[0]->Rpm;
                    //     $data["record"]->length =$recent[0]->Mtr;
                    // }

                    if($data['record']){
                        $difference = $this->calculateMinutes(date("Y-m-d H:i:s"), $data['record']->run_date_time);
                        if($difference > 15){
                             //////mine
                            if(!empty($recent[0]))
                            {
                                $data["record"]->run_date_time = $recent[0]->LDT;
                                $data["record"]->speed = $recent[0]->Rpm;
                                $data["record"]->length =$recent[0]->Mtr;
                                $difference = $this->calculateMinutes(date("Y-m-d H:i:s"), $data['record']->run_date_time);
                                if($difference > 15){
                                    $data['status'] = 'Not Live';

                                }else{
                                    $data['status'] = 'Live';
                                }
                            }else{
                                $data['status'] = 'Not Live';

                            }
                            /////end mine
                        // $data['status'] = 'Not Live';  /// old
                        }
                        else{
                            $data['status'] = 'Live';
                        }

                        $data['lastUpdatedDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                        $data['lastUpdatedTime'] = date('H:i:s', strtotime($data['record']->run_date_time));

                        if(strtotime($data['lastUpdatedDate']) < strtotime(date('Y-m-d').' - 1 day')){
                            $data['statusDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                            $data['statusTime'] = date('H:i', strtotime($data['record']->run_date_time));
                            $data['statusCode'] = $data['record']->error->name;
                        }
                        else{
                            $data['error'] = Record::select('run_date_time', 'error_id')
                                ->where('machine_id', '=', $machine_id)
                                ->whereRaw('error_id != '.$data['record']->error_id)
                                ->latest('run_date_time')->first();
                            if($data['error']){
                                $data['statusDate'] = date('d M, Y', strtotime($data['error']->run_date_time));
                                $data['statusTime'] = date('H:i', strtotime($data['error']->run_date_time));
                                $data['statusCode'] = $data['record']->error->name;
                            }
                        }
                        if($data['record']->process){
                            $data['substrate'] = $data['record']->process->materialCombination($data['record']->job->product->id)[0]->name;
                        }


                        array_push($productionDashboardData, $data);
                    }
                }
                //dd($productionDashboardData);
                return response(json_encode($productionDashboardData), 200);
            }


            return response(json_encode($data),200);
        }
        else{
            return response(json_encode("Session Out"), 505);
        }
    }
    public function recordsUpdate($id){
        $user_id = Session::get('user_id');
        if(isset($user_id)){
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            if(Session::get('rights') == 0){
                $data['layout'] = 'web-layout';
            }
            elseif(Session::get('rights') == 1){
                $data['layout'] = 'admin-layout';
            }
            elseif(Session::get('rights') == 2){
                $data['layout'] = 'power-user-layout';
            }
            $data['rotoErrors'] = Error::all();
            $data['machine'] = Machine::find(Crypt::decrypt($id));
            $data['user'] = Users::find(Session::get('user_name'));
            return view('roto.update-downtime', $data);
        }
        else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }

    public function getHistoricRecords(Request $request){
        $machine_id = $request->input('machine_id');
        $date = date('Y-m-d H:i:s', strtotime($request->input('date')));
        $shiftSelection = $request->input('shifts');

        $minStarted = Shift::find($shiftSelection[0])->min_started;
        $minEnded = Shift::find($shiftSelection[count($shiftSelection)-1])->min_ended;

        $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
        $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
        Log::info($machine_id);
//        $data['records'] = Record::select('speed', 'run_date_time')->where('run_date_time', '>=', $startDateTime)->where('run_date_time', '<=', $endDateTime)->where('machine_id', '=', $machine_id)->orderby('run_date_time', 'ASC')->get();
        $data['records'] = Record::select('speed', 'run_date_time','length')->where('run_date_time', '>=', $startDateTime)->where('run_date_time', '<=', $endDateTime)->where('machine_id', '=', $machine_id)->orderby('run_date_time', 'ASC')->get();

        return response(json_encode($data), 200);
    }

    public function allocateDowntime(Request $request){
        $machine_id = $request->input('machine_id');
        $records = Record::where('run_date_time', '>=', date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom'))))
            ->where('run_date_time', '<=', date('Y-m-d H:i:s', strtotime($request->input('downtimeTo'))))
            ->where('machine_id', '=', $machine_id)
            ->get();
        $machine = Machine::find($machine_id);
        $current_user = Users::find(Session::get('user_name'));
        $logger = new LoggerController($machine);
        $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>ALLOCATE DOWNTIME>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
        $logger->log($current_user['id'].' - '.$current_user['name'],$machine);
        $logger->log(date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom'))) .' - '. date('Y-m-d H:i:s', strtotime($request->input('downtimeTo'))) .' - '. $request->input('downtimeDescription') .' - '.$request->input('machine_id'),$machine);

        //haseeb
        if((round(abs(strtotime($request->input('downtimeTo'))-strtotime($request->input('downtimeFrom'))) / 60,2))>1439)
        {
            $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>ALLOCATE DOWNTIME EXCEPTION>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
            $logger->log($current_user['id'].' - '.$current_user['name'],$machine);
            $logger->log(date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom'))) .' - '. date('Y-m-d H:i:s', strtotime($request->input('downtimeTo'))) .' - '. $request->input('downtimeDescription') .' - '.$request->input('machine_id'),$machine);
            $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END ALLOCATE DOWNTIME EXCEPTION>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
            $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END ALLOCATE DOWNTIME>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
            return response("invalid form data",500);
        }
        //haseeb
        foreach($records as $record){
            $record->error_id = $request->input('downtimeID');
            $record->err_comments = $request->input('downtimeDescription');
            $record->save();
        }
        $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END ALLOCATE DOWNTIME>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
        return response(200);
    }

    public function getMultipleTime(Request $request){
        $data['times'] = Record::select('run_date_time as time')
            ->where('run_date_time', '>', date('y-m-d H:i:s', strtotime($request->input('downtimeFrom'))))
            ->where('run_date_time', '<=', date('y-m-d H:i:s', strtotime($request->input('downtimeTo'))))
            ->where('machine_id', '=', $request->input('machine_id'))
            ->get();
        return response(json_encode($data),200);
    }

    public function localRecordsLive($num_id, $dateTime, $mtr, $rpm, $job_id, $user_id, $process_id){
        try {
            $machine = Machine::where('sap_code', '=', $num_id)->get();
            $logger = new LoggerController($machine[0]);
            try{
                $record = Record::where('machine_id', '=', $machine[0]->id)->where('run_date_time','=', $dateTime)->get();
                if(count($record) == 0){
                    if(count($machine) > 0){
                        if($machine[0]->time_uom == 'Hr'){
                            $speed = $rpm*$machine[0]->roller_circumference*60;
                        }
                        elseif($machine[0]->time_uom == 'Sec'){
                            $speed = $rpm*$machine[0]->roller_circumference/60;
                        }
                        else{
                            $speed = $rpm*$machine[0]->roller_circumference;
                        }
                        if(count($machine) == 1){
                            if($speed < $machine[0]->waste_speed){
                                $record = new Record();
                                $record->user_id = $user_id;
                                $record->error_id = 500;
                                $record->job_id = $job_id;
                                $record->machine_id = $machine[0]->id;
                                $record->speed = $speed;
                                $record->length = $mtr*$machine[0]->roller_circumference;
                                $record->run_date_time = $dateTime;
                                $record->err_comments = '*';
                                $record->process_id = $process_id;
                                $upper = Record::where('machine_id', '=', $machine[0]->id)->where('run_date_time','>', $dateTime)->orderby('run_date_time', 'DESC')->limit(1)->get();
                                $lower = Record::where('machine_id', '=', $machine[0]->id)->where('run_date_time','<', $dateTime)->orderby('run_date_time', 'DESC')->limit(1)->get();

                                if(($upper[0]->error_id!=500)&&($upper[0]->error_id!=2))
                                {
                                    $record->error_id = $upper[0]->error_id;
                                    $record->err_comments = $upper[0]->err_comments.'*';
                                }
                                elseif (($lower[0]->error_id!=500)&&($lower[0]->error_id!=2))
                                {
                                    $record->error_id = $lower[0]->error_id;
                                    $record->err_comments = $lower[0]->err_comments.'*';
                                }

                                $record->save();
                            }
                            else{
                                $record = new Record();
                                $record->user_id = $user_id;
                                $record->error_id = 2;
                                $record->job_id = $job_id;
                                $record->machine_id = $machine[0]->id;
                                $record->speed = $speed;
                                $record->length = $mtr*$machine[0]->roller_circumference;
                                $record->run_date_time = $dateTime;
                                $record->err_comments = '*';
                                $record->process_id = $process_id;
                                $record->save();
                            }
                            $circuit_record = new CircuitRecords();
                            $circuit_record->num_id = $machine[0]->sap_code;
                            $circuit_record->LDT = $dateTime;
                            $circuit_record->Mtr = $mtr*$machine[0]->roller_circumference;
                            $circuit_record->Rpm = $speed;
                            $circuit_record->raw_string = "{'Num': '".$machine[0]->sap_code."','LDT': '".$dateTime."','Mtr': '".$mtr."','Rpm': '".$rpm."'}";
                            $circuit_record->save();
                            return $record;
                        }
                    }
                }
            }
            catch (\Exception $e)
            {
                $logger->log("======================localRecordsLive Exception========================",$machine);
                $logger->log("================================================================================",$machine);
                $logger->log($e->getMessage(),$machine);
                $logger->log("================================================================================",$machine);
                $logger->log("======================localRecordsLive Exception========================",$machine);
            }
        }
        catch (\Exception $e)
        {
            Log::info("======================localRecordsLive========================");
            Log::info("================================================================================");
            Log::info($e->getMessage());
            Log::info("================================================================================");
            Log::info("======================localRecordsLive========================");
        }



    }

    public function importLocalData($input){
//		$machine_id = Session::get('machine_id');
        $machine_id = $input;
        $machine = Machine::select('ip')->where('id', '=', $machine_id)->get();

        //haseeb 6/14/2021
        $mach = Machine::find($machine_id);
        $logger = new LoggerController($mach);
        $data['wrongDateTimeStrings'] = [];
        //haseeb 6/14/2021

//	dump($machine);
        try{
            //Calling circuit API to get the data
            //		dump($machine[0]->ip);
            $url = 'http://'.$machine[0]->ip.'/json';
            $ch = curl_init();
            //Setting options for API being called to circuit
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $result = curl_exec($ch);

            //closing the circuit API connection
            curl_close ($ch);

            //      dump($result);

            //Storing Records as JSON objects
            /// my code
            if($mach->sap_code=='22155'){
                $string1  = str_replace("'", "", $result);
                $string = preg_replace('/}{/', '},{', $string1);

                // Wrap the string in square brackets to create a JSON array
                $jsonString = '[' . $string . ']';

                // Decode the JSON string
                $localRecords = json_decode($jsonString);
                //dd($jsonArray);
           }else{
            $localRecords = json_decode($result);
           }

            /// end my code
           // $localRecords = json_decode($result);
            //		dump($localRecords);

            if(!empty($localRecords)){
                $last = count($localRecords)-1;
                $localStartedAt = substr($localRecords[0]->LDT,0,10).' '.substr($localRecords[0]->LDT,11,8);
                $localEndedAt = substr($localRecords[$last]->LDT,0,10).' '.substr($localRecords[$last]->LDT,11,8);
                //Making records live
                foreach($localRecords as $localRecord){

                    DB::beginTransaction();
                    $dateTime = $localRecord->LDT;
                    $date = substr($dateTime,0,10);
                    $time = substr($dateTime,11,8);
                    $dateTime = $date.' '.$time;

                    $record = Record::select('job_id', 'user_id', 'process_id')->where('run_date_time', '<', $dateTime)->where('machine_id', '=', $machine_id)->orderby('run_date_time', 'desc')->limit(1)->get();
//                    return $record[0]->process_id;
                    if(!$record->isEmpty()){
                        //haseeb 6/14/2021
                        if(date('Y', strtotime($dateTime)) == date('Y')) {
                            $this->localRecordsLive($localRecord->Num, $dateTime, $localRecord->Mtr, $localRecord->Rpm, $record[0]->job_id, $record[0]->user_id, $record[0]->process_id);
                        }
                        else{
                            array_push($data['wrongDateTimeStrings'], json_encode($localRecord));
                        }
                        //$this->localRecordsLive($localRecord->Num,$dateTime,$localRecord->Mtr,$localRecord->Rpm,$record[0]->job_id,$record[0]->user_id,$record[0]->process_id);
                        //haseeb 6/14/2021
                    }
                    DB::commit();
                }
                $url = 'http://'.$machine[0]->ip.'/DELog';
                $ch = curl_init();
                //Setting options for API being called to circuit
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $result = curl_exec($ch);
                //closing the circuit API connection
                curl_close ($ch);

                //haseeb 6/14/2021
                if(!empty($data['wrongDateTimeStrings'])){
                    try{
                        Mail::send('emails.wrong-date-time-strings', $data, function ($message) use ($data) {
                            $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                            $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                                ->to('a4ashraf23@gmail.com', 'Ashraf Wali')
                                ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                                ->cc('haseeb.khan@packages.com.pk', 'Haseeb Khan')
                                ->subject("RotoEye Cloud - Wrong Date & Time Strings");
                        });
                    }
                    catch (\Exception $e){
                        $logger->log('<<<< EXCEPTION >>>>', $machine);
                        $logger->log('Exception while sending email for wrong date and time strings.', $machine);
                        $logger->log('<<<< EXCEPTION >>>>', $machine);
                    }
                }
                //haseeb 6/14/2021

//				return 'i did it';
                Session::flash('success', 'Imported Successfuly');
                return redirect('production/dashboard');
            }
            else{
                Session::flash('error', 'No Local Records Found');
                return Redirect::back();
            }
        }
        catch(Exception $e){
            Session::flash('error', $e->getCode());
            return Redirect::back();
        }
    }

    public function getExcel(){
        $data['users'] = Users::all();
        $data['layout'] = 'admin-layout';
        $data['user'] = Users::find(Session::get('user_name'));
        $data['path'] = Route::getFacadeRoot()->current()->uri();
        Excel::create('thecodingstuff', function($excel) use ($data) {
            $excel->sheet('thecodingstuff', function($sheet) use ($data) {
                $sheet->loadView('excel.print-check', $data);
            });
        })->download('xls');
    }

    public function getTime(){
        return date('Y-m-d-H:i:s');
    }

    public function checkCron(){

    }

    public function getLocalRecords(){
        $url = 'http:///json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);

        $localRecords = json_decode($result);
        if(count($localRecords) > 0){
            $dashboardController = new DashboardController();
            foreach($localRecords as $localRecord){
                $dateTime = $localRecord->LDT;
                $date = substr($dateTime,0,10);
                $time = substr($dateTime,11,8);
                $dateTime = $date.' '.$time;

                $record = Record::select('job_id', 'user_id')->where('run_date_time', '<', $dateTime)->where('machine_id', '=', 0)->orderby('run_date_time', 'desc')->limit(1)->get();
                if(count($record)>0) {
                    $dashboardController->localRecordsLive($localRecord->Num,$dateTime,$localRecord->Mtr,$localRecord->Rpm,$record[0]->job_id,$record[0]->user_id);
                }
            }
        }
    }

    public function circuitLog(Request $request){
        return $request->file('log_file')->move('logs');
    }

    public function checkJSON(){
        /*$machines = Machine::whereNull('is_disabled')->get();
                    $data['machines'] = [];
                    $data['wrongDateTimeStrings'] = [];
                    $now = date('Y-m-d H:i:s');

                    foreach($machines as $machine){
                        $logger = new LoggerController($machine);
                        try{
                            $ip_address = $machine->ip;
                            dump($ip_address);
                            $url = 'http://'.$ip_address.'/json';
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            $result = curl_exec($ch);
                            curl_close($ch);

                            $localRecords = json_decode($result);

                            if(count($localRecords) > 0){
                                console.log($machine->ip);
                                console.log('Records Fetched');
                                $logger->log('--------------------------------------------- START LOCAL RECORDS ---------------------------------------------', $machine);
                                $logger->log('Local Records Count: '.count($localRecords), $machine);
                                $logger->log('Machine ID: '.$machine->id, $machine);
                                $logger->log('Machine IP: '.$machine->ip, $machine);

                                $dashboardController = new DashboardController();
                                foreach($localRecords as $localRecord){
                                    try{
                                        $logger->log('__________________ START STRING __________________', $machine);
                                        $logger->log('Record String: '.json_encode($localRecord), $machine);
                                        $logger->log('__________________ END STRING __________________', $machine);

                                        $dateTime = $localRecord->LDT;
                                        $date = substr($dateTime,0,10);
                                        $time = substr($dateTime,11,8);
                                        $dateTime = $date.' '.$time;

                                        $record = Record::select('job_id', 'user_id', 'process_id')->where('run_date_time', '<', $dateTime)->where('machine_id', '=', $machine->id)->orderby('run_date_time', 'desc')->limit(1)->get();
                                        if(count($record)>0) {
                                            if(date('Y', strtotime($dateTime)) == date('Y')){
                                                $dashboardController->localRecordsLive($localRecord->Num,$dateTime,$localRecord->Mtr,$localRecord->Rpm,$record[0]->job_id,$record[0]->user_id, $record[0]->process_id);
                                            }
                                            else{
                                                array_push($data['wrongDateTimeStrings'], json_encode($localRecord));
                                            }
                                        }
                                    }
                                    catch(\Exception $e){
                                        $logger->log('<<<< EXCEPTION >>>>', $machine);
                                        $logger->log('Record String: '.json_encode($localRecord), $machine);
                                        $logger->log('<<<< EXCEPTION >>>>', $machine);
                                    }
                                }
                                try{
                                    $url = 'http://'.$ip_address.'/DELog';
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, $url);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                    $result = curl_exec($ch);
                                    curl_close ($ch);
                                }
                                catch (\Exception $e){
                                    $logger->log('<<<< EXCEPTION >>>>', $machine);
                                    $logger->log('Exception while deleting JSON file from circuit', $machine);
                                    $logger->log('Got Exception: '.$e->getMessage(), $machine);
                                    $logger->log('<<<< EXCEPTION >>>>', $machine);
                                }
                                $logger->log('--------------------------------------------- END LOCAL RECORDS ---------------------------------------------', $machine);
                            }
                        }
                        catch(\Exception $e){
                            $logger->log('<<<< EXCEPTION >>>>', $machine);
                            $logger->log('Exception while getting JSON from Circuit', $machine);
                            $logger->log('Got Exception: '.$e->getMessage(), $machine);
                            $logger->log('<<<< EXCEPTION >>>>', $machine);
                        }

                        try{
                            $record = Record::where('machine_id', '=', $machine->id)->latest('run_date_time')->limit(1)->get();
                            if(count($record) > 0){
                                $minutesDiff = $this->calculateMinutes($now, $record[0]->run_date_time);
                                if($minutesDiff > 20){
                                    $lastDataReceived = date_diff(date_create($record[0]->run_date_time), date_create($now))->format("%y Year %m Month %d Day %h Hr %i Min %s Sec");
                                    array_push($data['machines'],[
                                        "machine_id"=>$machine->sap_code,
                                        "machine_name"=>$machine->name,
                                        "last_run_date_time"=>$record[0]->run_date_time,
                                        "last_received"=>$lastDataReceived,
                                        "ip_address"=>$machine->ip
                                    ]);
                                }
                            }
                        }
                        catch (\Exception $e){
                            $logger->log('<<<< EXCEPTION >>>>', $machine);
                            $logger->log('Exception while checking not responding circuits', $machine);
                            $logger->log('<<<< EXCEPTION >>>>', $machine);
                        }
                    }
                    if(count($data['machines']) > 0){
                        try{
                            Mail::send('emails.not-responding-circuits', $data, function ($message) use ($data) {
                                $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                                $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                                    ->cc('shaukat.hussain@packages.com.pk', 'Shaukat Hussain')
                                    ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                                    ->subject("RotoEye Cloud - Not Responding Circuits");
                            });
                        }
                        catch(\Exception $e){
                            $logger->log('<<<< EXCEPTION >>>>', $machine);
                            $logger->log('Exception while sending email for not responding circuits', $machine);
                            $logger->log('<<<< EXCEPTION >>>>', $machine);
                        }
                    }
                    if(count($data['wrongDateTimeStrings']) > 0){
                        try{
                            Mail::send('emails.wrong-date-time-strings', $data, function ($message) use ($data) {
                                $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                                $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                                    ->to('a4ashraf23@gmail.com', 'Ashraf Wali')
                                    ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                                    ->subject("RotoEye Cloud - Wrong Date & Time Strings");
                            });
                        }
                        catch (\Exception $e){
                            $logger->log('<<<< EXCEPTION >>>>', $machine);
                            $logger->log('Exception while sending email for wrong date and time strings.', $machine);
                            $logger->log('<<<< EXCEPTION >>>>', $machine);
                        }
                    }*/
    }

    public function calculateMinutes($fromTime, $toTime){
        $diff = date_diff( date_create($toTime), date_create($fromTime));
        $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
        if( $diff->invert){
            return -1 * $total;
        }
        else{
            return $total;
        }
    }

     public function getRecords(Request $request){

        $user_id = Session::get('user_id');
        /*if($user_id){
            $data = [];
            $productionDashboardData = [];

                $machine_id = Crypt::decrypt('eyJpdiI6InJ4dWxBTWRVUTlqckdkTWpPUmZCSXc9PSIsInZhbHVlIjoib2RJVlRXZDVONmwxdnQvTlVkSERwZz09IiwibWFjIjoiNWMxODMxOTczZjdiYTMzYzgyZGRmNDYyYmI5MWViMDE5YmFmZTI0ZTdjNjYxNmViMzBjMGIwYmQxZGYwNjRkYyIsInRhZyI6IiJ9');

                $data['record'] = Record::select('speed', 'length', 'run_date_time', 'user_id', 'job_id', 'process_id', 'error_id')
                    ->with('user','job', 'job.product', 'process', 'error')
                    ->where('machine_id', '=', $machine_id)
                    ->latest('run_date_time')
                    ->first();

                //return $data['record'];
                $mac = Machine::find($machine_id);

                $recent = CircuitRecords::where("num_id","=",$mac->sap_code)->latest("LDT")->limit(1)->get();

                if(!empty($recent[0]))
                {
                    $data["record"]->run_date_time = $recent[0]->LDT;
                    $data["record"]->speed = $recent[0]->Rpm;
                    $data["record"]->length =$recent[0]->Mtr;
                }

                $difference = $this->calculateMinutes(date("Y-m-d H:i:s"), $data['record']->run_date_time);

                if($difference > 15){
                    $data['status'] = 'Not Live';
                }
                else{
                    $data['status'] = 'Live';
                }

                $data['lastUpdatedDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                $data['lastUpdatedTime'] = date('H:i:s', strtotime($data['record']->run_date_time));

                if(strtotime($data['lastUpdatedDate']) < strtotime(date('Y-m-d').' - 1 day')){
                    $data['statusDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                    $data['statusTime'] = date('H:i', strtotime($data['record']->run_date_time));
                    $data['statusCode'] = $data['record']->error->name;
                }
                else{
                    $data['error'] = Record::select('run_date_time', 'error_id')
                        ->where('machine_id', '=', $machine_id)
                        ->whereRaw('error_id != '.$data['record']->error_id)
                        ->latest('run_date_time')->first();
                    if($data['error']){
                        $data['statusDate'] = date('d M, Y', strtotime($data['error']->run_date_time));
                        $data['statusTime'] = date('H:i', strtotime($data['error']->run_date_time));
                        $data['statusCode'] = $data['record']->error->name;
                    }
                }

                //return $data['record']->job->product->id;
                if($data['record']->process){
                    $data['substrate'] = $data['record']->process->materialCombination($data['record']->job->product->id)[0]->name;

                }
                else{
                    $data['substrate']="";
                }
            return response(json_encode($data),200);
        }
        else{
            return response(json_encode("Session Out"), 505);
        }*/

        if($user_id){
            $data = [];
            $productionDashboardData = [];

                $machine_id =  $machine_id = Crypt::decrypt('eyJpdiI6IitaQ3FHZjNUSWZyNDdJOGV4bFJWS1E9PSIsInZhbHVlIjoiM1djWkoxODNLV2l5d0d1VGFJZkZjUT09IiwibWFjIjoiOWQ3MjhiMGQzMTRhNWViZTlhYmYyZTA0ZTlmMzE3ZDE0NGRkODI1NmUyZTE5Mjg1ZTljYzZiMjQyYWM0ZmViYiIsInRhZyI6IiJ9');
                $data['record'] = Record::select('speed', 'length', 'run_date_time', 'user_id', 'job_id', 'process_id', 'error_id')
                    ->with('user', 'job.product', 'process', 'error')
                    ->where('machine_id', '=', $machine_id)
                    ->latest('run_date_time')
                    ->first();


                $mac = Machine::find($machine_id);
                $recent = CircuitRecords::where("num_id","=",$mac->sap_code)->latest("LDT")->limit(1)->toSql();
                return $recent;
                if(!empty($recent[0]))
                {
                    $data["record"]->run_date_time = $recent[0]->LDT;
                    $data["record"]->speed = $recent[0]->Rpm;
                    $data["record"]->length =$recent[0]->Mtr;
                }

                $difference = $this->calculateMinutes(date("Y-m-d H:i:s"), $data['record']->run_date_time);
                if($difference > 15){
                    $data['status'] = 'Not Live';
                }
                else{
                    $data['status'] = 'Live';
                }

                $data['lastUpdatedDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                $data['lastUpdatedTime'] = date('H:i:s', strtotime($data['record']->run_date_time));

                if(strtotime($data['lastUpdatedDate']) < strtotime(date('Y-m-d').' - 1 day')){
                    $data['statusDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                    $data['statusTime'] = date('H:i', strtotime($data['record']->run_date_time));
                    $data['statusCode'] = $data['record']->error->name;
                }
                else{
                    $data['error'] = Record::select('run_date_time', 'error_id')
                        ->where('machine_id', '=', $machine_id)
                        ->whereRaw('error_id != '.$data['record']->error_id)
                        ->latest('run_date_time')->first();
                    if($data['error']){
                        $data['statusDate'] = date('d M, Y', strtotime($data['error']->run_date_time));
                        $data['statusTime'] = date('H:i', strtotime($data['error']->run_date_time));
                        $data['statusCode'] = $data['record']->error->name;
                    }
                }

                if($data['record']->process){

                    $data['substrate'] = $data['record']->process->materialCombination($data['record']->job->product->id)[0]->name;
                }

           /* else{
                $machines = $request->input('machines_arr');


                foreach($machines as $machine){
                    $machine_id  = Crypt::decrypt($machine);
                    $data['machineID'] = $machine_id;

                    $data['record'] = Record::select('machine_id','speed', 'length', 'run_date_time', 'user_id', 'job_id', 'process_id', 'error_id')
                        ->with('user', 'job.product', 'process', 'error')
                        ->where('machine_id', '=', $machine_id)
                        ->latest('run_date_time')
                        ->first();
                    $mac = Machine::find($machine_id);
                    $recent = CircuitRecords::where("num_id","=",$mac->sap_code)->latest("LDT")->limit(1)->get();
                    if(!empty($recent[0]))
                    {
                        $data["record"]->run_date_time = $recent[0]->LDT;
                        $data["record"]->speed = $recent[0]->Rpm;
                        $data["record"]->length =$recent[0]->Mtr;
                    }

                    if($data['record']){
                        $difference = $this->calculateMinutes(date("Y-m-d H:i:s"), $data['record']->run_date_time);
                        if($difference > 15){
                            $data['status'] = 'Not Live';
                        }
                        else{
                            $data['status'] = 'Live';
                        }

                        $data['lastUpdatedDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                        $data['lastUpdatedTime'] = date('H:i:s', strtotime($data['record']->run_date_time));

                        if(strtotime($data['lastUpdatedDate']) < strtotime(date('Y-m-d').' - 1 day')){
                            $data['statusDate'] = date('d M, Y', strtotime($data['record']->run_date_time));
                            $data['statusTime'] = date('H:i', strtotime($data['record']->run_date_time));
                            $data['statusCode'] = $data['record']->error->name;
                        }
                        else{
                            $data['error'] = Record::select('run_date_time', 'error_id')
                                ->where('machine_id', '=', $machine_id)
                                ->whereRaw('error_id != '.$data['record']->error_id)
                                ->latest('run_date_time')->first();
                            if($data['error']){
                                $data['statusDate'] = date('d M, Y', strtotime($data['error']->run_date_time));
                                $data['statusTime'] = date('H:i', strtotime($data['error']->run_date_time));
                                $data['statusCode'] = $data['record']->error->name;
                            }
                        }
                        if($data['record']->process){
                            $data['substrate'] = $data['record']->process->materialCombination($data['record']->job->product->id)[0]->name;
                        }


                        array_push($productionDashboardData, $data);
                    }
                }
                return response(json_encode($productionDashboardData), 200);
            }*/


            return response(json_encode($data),200);
        }
        else{
            return response(json_encode("Session Out"), 505);
        }
    }


    public function manual_dashboard($id)
    {
        $user_id = Session::get('user_id');
        if($user_id) {
            if (Session::get('rights') == 0) {
                $data['layout'] = 'web-layout';
            } elseif (Session::get('rights') == 1) {
                $data['layout'] = 'admin-layout';
            } elseif (Session::get('rights') == 2) {
                $data['layout'] = 'power-user-layout';
            }

            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $machine_id = Crypt::decrypt($id);
            $data['machine'] = Machine::find($machine_id);
            if ($data['machine']) {
                if($data['machine']->app_type == 0){
                    $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->first();
                    if (count(array($loginRecord)) == 0) {
                        Session::flash('error', 'No login record found against selected machine. Please login through operator rights first or select a job that is running.');
                        return redirect('select/job' . $id);
                    } else {
                        if (Session::get('rights') == 0) {
                            $data['user'] = Users::find($loginRecord->user_id);
                        } else {
                            $data['user'] = Users::find(Session::get('user_id'));
                        }

                        $data['running_job'] = Job::find($loginRecord->job_id);
                        $lastDateTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - ' . $loginRecord->machine->graph_span . ' hours'));
                        $data['record'] = Record::where('machine_id', '=', $machine_id)->latest('run_date_time')->first();
                        if (!$data['record']) {
                            $record = new Record();
                            $record->user_id = $loginRecord->user_id;
                            $record->error_id = 500;
                            $record->job_id = $loginRecord->job_id;
                            $record->machine_id = $loginRecord->machine_id;
                            $record->speed = 0;
                            $record->length = 0;
                            $record->run_date_time = date('Y-m-d H:i:s');
                            $record->process_id = $loginRecord->process_id;
                            $record->save();
                        }
                        $data['record'] = Record::where('machine_id', '=', $machine_id)->latest('run_date_time')->first();
                        $data['graphRecords'] = Record::select('speed', 'length', 'run_date_time')->where('machine_id', '=', $machine_id)->where('run_date_time', '>=', $lastDateTime)->orderby('run_date_time', 'ASC')->get();
                        if ($data['machine']->app_type) {
                            $data['rotoErrors'] = Error::whereNotIn('id', [2, 500])->whereHas('departments', function ($query) use ($data) {
                                $query->where('department_id', '=', $data['machine']->section->department->id);
                            })->get();
                        } else {
                            $data['rotoErrors'] = Error::whereHas('departments', function ($query) use ($data) {
                                $query->where('department_id', '=', $data['machine']->section->department->id);
                            })->get();
                        }

                        return view('roto.dashboard-manual', $data);
                    }
                }
                else {
                    Session::flash("error", "Machine is not Manual. Please contact System Administrator.");
                    return redirect()->back();
                }
            }
            else{
                Session::flash("error", "Machine is not valid. Please contact System Administrator.");
                return redirect('/');
            }
        }
        else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }
    /**
     * @return \Illuminate\View\View
     */

}