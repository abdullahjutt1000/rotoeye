<?php namespace App\Http\Controllers;



use App\Helper\Helper;
use App\Models\CircuitRecords;
use App\Models\Error;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\temp_table;
use App\Models\LoginRecord;
use App\Models\Machine;
use App\Models\Record;
use App\Models\Records_From_Circuit;
use App\Models\Shift;
use App\Models\User;
use App\Models\Users;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class RecordController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public function index($id)
    {
        $user_id = Session::get('user_id');
        if(isset($user_id)){
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $data['machine'] = Machine::find(Crypt::decrypt($id));
            if(Session::get('rights') == 0){
                $data['layout'] = 'web-layout';
            }
            elseif(Session::get('rights') == 1){
                $data['layout'] = 'admin-layout';
            }
            elseif(Session::get('rights') == 2){
                $data['layout'] = 'power-user-layout';
            }
            $data['user'] = Users::find(Session::get('user_name'));
            return view('roto.update-records-filter', $data);
        }
        else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }

    public function live($machine, $date_time, $length, $speed, $loginRecord, $latest_record,$logger){
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

    public function live_compressed($num_id, $ldt, $mtr, $rpm,$sd, Request $request){
        try{
            $raw_string = 'Raw String: api/Num/'.$num_id.'/LDT/'.$ldt.'/Mtr/'.$mtr.'/Rpm/'.$rpm.'/Sd/'.$sd;

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
                if($machine->ip != $request->ip() && $machine->ip_change_on_off == 1)
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

                        $previous = CircuitRecords::save_record($machine->sap_code, $ldt, $length, $speed,$sd,$raw_string);
                        $percentage_allow=2.6;
                        if(CircuitRecords::plus_minus_percentage($percentage_allow, $speed, $latest_record[0]->speed ) || ($latest_record[0]['user_id']!=$loginRecord["user_id"] || $latest_record[0]['job_id']!=$loginRecord["job_id"] || $latest_record[0]['process_id']!=$loginRecord["process_id"])||(Shift::which_shift($latest_record[0]->run_date_time,$shifts)->shift_number!=Shift::which_shift($ldt,$shifts)->shift_number ||  date('Y-m-d',strtotime(Shift::which_shift($latest_record[0]->run_date_time,$shifts)->date))!= date('Y-m-d',strtotime(Shift::which_shift($ldt,$shifts)->date))))
                        
                        // if(CircuitRecords::plus_minus_percentage($percentage_allow, $speed, $latest_record[0]->speed ) || ($latest_record[0]['user_id']!=$loginRecord["user_id"] || $latest_record[0]['job_id']!=$loginRecord["job_id"] || $latest_record[0]['process_id']!=$loginRecord["process_id"]))
                        {
                            if($previous != null)
                            {
                                if($previous->LDT != $latest_record[0]["run_date_time"])
                                {

                                    if($this->live($machine, $previous->LDT, $previous->Mtr , $previous->Rpm,$latest_record[0],$latest_record, $logger))
                                    {
                                        if($this->live($machine, $ldt, $length, $speed,$loginRecord,$latest_record, $logger))
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
                                    if($this->live($machine, $ldt, $length, $speed,$loginRecord,$latest_record, $logger))
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
                        CircuitRecords::save_record($machine->sap_code, $ldt, $length, $speed,$sd,$raw_string);
                        if($this->live($machine, $ldt, $length, $speed,$loginRecord,$latest_record, $loginRecord))
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


    public function getDateWiseMachineRecords(Request $request){
        $date = date('Y-m-d', strtotime($request->input('date')));
        $machine = $request->input('machine');
        $job = $request->input('job');
        $data['job'] = Job::find($job);
        $data['machine'] = Machine::find($machine);
        $data['date'] = $date;
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

        $records = DB::table('records')
            ->leftJoin('errors', 'errors.id', '=', 'records.error_id')
            ->leftJoin('users', 'users.id', '=', 'records.user_id')
            ->leftJoin('jobs', 'jobs.id', '=', 'records.job_id')
            ->leftJoin('products', 'products.id', '=', 'jobs.product_id')
            ->leftJoin('process_structure', function($join){
                $join->on('process_structure.process_id', '=', 'records.process_id');
                $join->on('process_structure.product_id', '=', 'products.id');
            })
            ->leftJoin('material_combination', 'material_combination.id', '=', 'process_structure.material_combination_id')
            ->leftJoin('processes', 'processes.id', '=', 'process_structure.process_id')
            ->select('errors.name as error_name', 'records.run_date_time as run_date_time', 'records.error_id as error_id', 'records.length as length', 'records.err_comments as comments',
                'jobs.id as job_id', 'products.name as job_name', 'jobs.job_length as job_length', 'products.name as product_name', 'products.id as product_number',
                'material_combination.name as material_combination', 'material_combination.nominal_speed as nominal_speed', 'records.user_id as user_id', 'users.name as user_name',
                'processes.process_name as process_name')
            ->where('machine_id', '=', $machine)
            ->where('job_id', '=', $job)
            ->orderby('run_date_time', 'ASC')
            ->get();
        $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + 390 minutes'));
        $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + 1830 minutes'));
        $loginRecord = LoginRecord::where('machine_id', '=', $machine)->get();

        $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
        $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
        $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();
        if(count($records) > 0){
            $data['records'] = [];
            $data['negativeRecords'] = [];
            $startDate = $records[0]->run_date_time;
            $oldLength = $records[0]->length;
            $runTime = 0;
            $idleTime = 0;
            $jobWaitTime = 0;
            $production = 0;
            $actualSpeed = 0;
            $jobProduction = 0;

            $totalTimeDifference = date_diff(date_create($startDateTime), date_create($endDateTime));
            $totalTime = (($totalTimeDifference->y * 365 + $totalTimeDifference->m * 30 + $totalTimeDifference->d) * 24 + $totalTimeDifference->h) * 60 + $totalTimeDifference->i + $totalTimeDifference->s/60;
            if(count($records) > 1){
                for ($i=0; $i<count($records); $i++){
                    if(isset($records[$i+1])){
                        if($records[$i]->error_id != $records[$i+1]->error_id || $records[$i]->user_id != $records[$i+1]->user_id || $records[$i]->job_id != $records[$i+1]->job_id){
                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s',strtotime($startDate))), date_create(date('d-M-Y H:i:s',strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s/60;
                            if($data['machine']->time_uom == 'Hr'){
                                if($duration == 0){
                                    $instantSpeed = 0;
                                }
                                else{
                                    $instantSpeed = (($records[$i]->length-$oldLength)/$duration)*60;
                                }
                            }
                            elseif($data['machine']->time_uom == 'Min'){
                                if($duration == 0){
                                    $instantSpeed = 0;
                                }
                                else{
                                    $instantSpeed = ($records[$i]->length-$oldLength)/$duration;
                                }
                            }
                            else{
                                if($duration == 0){
                                    $instantSpeed = 0;
                                }
                                else{
                                    $instantSpeed = (($records[$i]->length-$oldLength)/$duration)/60;
                                }
                            }
                            foreach($runningCodes as $runningCode) {
                                if ($runningCode->id == $records[$i]->error_id) {
                                    $runTime += $duration;
                                    $production += $records[$i]->length - $oldLength;
                                    $jobProduction += $records[$i]->length - $oldLength;
                                }
                            }
                            foreach($idleErrors as $idleError){
                                if($idleError->id == $records[$i]->error_id){
                                    $idleTime += $duration;
                                }
                            }
                            foreach($jobWaitingCodes as $jobWaitingCode){
                                if ($jobWaitingCode->id == $records[$i]->error_id) {
                                    $jobWaitTime += $duration;
                                }
                            }
                            if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500){
                                array_push($data['records'],[
                                    "job_id"=>$records[$i]->job_id,
                                    "job_name"=>$records[$i]->job_name,
                                    "product_number"=>$records[$i]->product_number,
                                    "material_combination"=>$records[$i]->material_combination,
                                    "nominal_speed"=>$records[$i]->nominal_speed,
                                    "user_name"=>$records[$i]->user_name,
                                    "job_length"=>$records[$i]->job_length,
                                    "error_id"=>501,
                                    "error_name"=>'Auto Error',
                                    "comments"=>'Auto Minor Stop by Roto-eye',
                                    "length"=>$records[$i]->length-$oldLength,
                                    "from"=>$startDate,
                                    "to"=>$endDate,
                                    "duration"=>$duration,
                                    "instantSpeed"=>$instantSpeed,
                                    "process_name"=>$records[$i]->process_name
                                ]);
                                $startDate = $endDate;
                                if($records[$i]->job_id != $records[$i+1]->job_id){
                                    $oldLength = $records[$i+1]->length;
                                    $jobProduction = $production;
                                    foreach($data['records'] as $record){
                                        array_push($record,[
                                            "jobProduction"=>$jobProduction
                                        ]);
                                        $jobProduction = 0;
                                    }
                                }
                                else{
                                    $oldLength = $records[$i]->length;
                                }
                            }
                            else{
                                array_push($data['records'],[
                                    "job_id"=>$records[$i]->job_id,
                                    "job_name"=>$records[$i]->job_name,
                                    "product_number"=>$records[$i]->product_number,
                                    "material_combination"=>$records[$i]->material_combination,
                                    "nominal_speed"=>$records[$i]->nominal_speed,
                                    "user_name"=>$records[$i]->user_name,
                                    "job_length"=>$records[$i]->job_length,
                                    "error_id"=>$records[$i]->error_id,
                                    "error_name"=>$records[$i]->error_name,
                                    "comments"=>$records[$i]->comments,
                                    "length"=>$records[$i]->length-$oldLength,
                                    "from"=>$startDate,
                                    "to"=>$endDate,
                                    "duration"=>$duration,
                                    "instantSpeed"=>$instantSpeed,
                                    "process_name"=>$records[$i]->process_name
                                ]);
                                $startDate = $endDate;
                                if($records[$i]->job_id != $records[$i+1]->job_id){
                                    $oldLength = $records[$i+1]->length;
                                    for($j=0; $j<count($data['records']); $j++){
                                        array_push($data['records'][$j],[
                                            "jobProduction"=>$jobProduction
                                        ]);
                                    }
                                    $jobProduction = 0;
                                }
                                else{
                                    $oldLength = $records[$i]->length;
                                }
                            }
                        }
                    }
                    else{
                        $endDate = $records[$i]->run_date_time;
                        $difference = date_diff(date_create(date('d-M-Y H:i:s',strtotime($startDate))), date_create(date('d-M-Y H:i:s',strtotime($endDate))));
                        $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s/60;
                        if($data['machine']->time_uom == 'Hr'){
                            if($duration == 0){
                                $instantSpeed = 0;
                            }
                            else{
                                $instantSpeed = (($records[$i]->length-$oldLength)/$duration)*60;
                            }
                        }
                        elseif($data['machine']->time_uom == 'Min'){
                            if($duration == 0){
                                $instantSpeed = 0;
                            }
                            else{
                                $instantSpeed = ($records[$i]->length-$oldLength)/$duration;
                            }
                        }
                        else{
                            if($duration == 0){
                                $instantSpeed = 0;
                            }
                            else{
                                $instantSpeed = (($records[$i]->length-$oldLength)/$duration)/60;
                            }
                        }
                        foreach($runningCodes as $runningCode) {
                            if ($runningCode->id == $records[$i]->error_id) {
                                $runTime += $duration;
                                $production += $records[$i]->length - $oldLength;
                                $jobProduction += $records[$i]->length - $oldLength;
                            }
                        }
                        foreach($idleErrors as $idleError){
                            if($idleError->id == $records[$i]->error_id){
                                $idleTime += $duration;
                            }
                        }
                        foreach($jobWaitingCodes as $jobWaitingCode){
                            if ($jobWaitingCode->id == $records[$i]->error_id) {
                                $jobWaitTime += $duration;
                            }
                        }
                        if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500){
                            array_push($data['records'],[
                                "job_id"=>$records[$i]->job_id,
                                "job_name"=>$records[$i]->job_name,
                                "product_number"=>$records[$i]->product_number,
                                "material_combination"=>$records[$i]->material_combination,
                                "nominal_speed"=>$records[$i]->nominal_speed,
                                "user_name"=>$records[$i]->user_name,
                                "job_length"=>$records[$i]->job_length,
                                "error_id"=>501,
                                "error_name"=>'Reel Change Over',
                                "comments"=>'Auto Minor Stop by Roto-eye',
                                "length"=>$records[$i]->length-$oldLength,
                                "from"=>$startDate,
                                "to"=>$endDate,
                                "duration"=>$duration,
                                "instantSpeed"=>$instantSpeed,
                                "jobProduction"=>$production,
                                "process_name"=>$records[$i]->process_name
                            ]);
                            $startDate = $endDate;
                            $oldLength = $records[$i]->length;
                        }
                        else{
                            array_push($data['records'],[
                                "job_id"=>$records[$i]->job_id,
                                "job_name"=>$records[$i]->job_name,
                                "product_number"=>$records[$i]->product_number,
                                "material_combination"=>$records[$i]->material_combination,
                                "nominal_speed"=>$records[$i]->nominal_speed,
                                "user_name"=>$records[$i]->user_name,
                                "job_length"=>$records[$i]->job_length,
                                "error_id"=>$records[$i]->error_id,
                                "error_name"=>$records[$i]->error_name,
                                "comments"=>$records[$i]->comments,
                                "length"=>$records[$i]->length-$oldLength,
                                "from"=>$startDate,
                                "to"=>$endDate,
                                "duration"=>$duration,
                                "instantSpeed"=>$instantSpeed,
                                "jobProduction"=>$production,
                                "process_name"=>$records[$i]->process_name
                            ]);
                            $startDate = $endDate;
                            $oldLength = $records[$i]->length;
                        }
                    }
                }
                for($k=0; $k<count($data['records']); $k++){
                    if(!isset($data['records'][$k][0]['jobProduction'])){
                        array_push($data['records'][$k],[
                            "jobProduction"=>$jobProduction
                        ]);
                    }
                }
                if($runTime > 0){
                    if($data['machine']->time_uom == 'Hr'){
                        $actualSpeed = $production/$runTime*60;
                    }
                    elseif($data['machine']->time_uom == 'Min'){
                        $actualSpeed = $production/$runTime;
                    }
                    else{
                        $actualSpeed = $production/$runTime/60;
                    }
                }
                else{
                    $actualSpeed = 0;
                }
            }
            if(Session::get('rights') == 0){
                $data['user'] = Users::find($loginRecord[0]->user_id);
            }
            else{
                $data['user'] = Users::find(Session::get('user_name'));
            }
            $data['produced'] = $production;
            $data['oee'] = ($runTime/($totalTime - $idleTime))*($actualSpeed/$data['machine']->max_speed)*100;
            $data['ee'] = ($runTime/($totalTime - $idleTime - $jobWaitTime))*($actualSpeed/$data['machine']->max_speed)*100;
            $data['performance'] = ($actualSpeed/$data['machine']->max_speed)*100;
            $data['availability_ee'] = ($runTime/($totalTime - $idleTime - $jobWaitTime))*100;
            $data['availability'] = ($runTime/($totalTime - $idleTime))*100;
            $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
            $data['run_time'] = $runTime;
            $data['budgetedTime'] = $totalTime - $idleTime;
            $data['processes'] = $data['machine']->section->processes;
            return view('roto.update-records', $data);
        }
        else{
            Session::flash('error','No Record Found');
            return Redirect::back();
        }
    }

    public function updateDateWiseMachineRecords(Request $request){
        $request_from =  $_SERVER['HTTP_REFERER'];

        $records=array(
            "from"=>"From",
            "to"=>"To",
            "machine"=>"Machine",
            "job"=>"Job",
        );
        $validator=Validator::make($request->all(),
            [
                "from"=>"required|date",
                "to"=>"required|date|after:from",
                "machine"=>"required",
                "job"=>"required",
            ]);
        $validator->setAttributeNames($records);
        if($validator->fails())
        {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        else {
            $job = $request->input('job');
            $machine = $request->input('machine');
            $from = $request->input('from');
            $to = $request->input('to');

            $records = Record::where('run_date_time', '>=', $from)
                ->where('run_date_time', '<=', $to)
                ->where('machine_id', '=', $machine)
                ->where('job_id', '=', $job)
                ->get();
            foreach($records as $record){
                $record->process_id = $request->input('process');
                $record->save();
            }

            Session::flash('success', 'Records Updated Successfuly');
            return redirect($request_from);
        }
    }
    
    public function live_compressed_LAN($num_id, $ldt, $mtr, $rpm,$sd, Request $request){
        
        try{
            //return '1';
            $raw_string = 'Raw String: api/Num/'.$num_id.'/LDT/'.$ldt.'/Mtr/'.$mtr.'/Rpm/'.$rpm.'/Sd/'.$sd;

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

                        $previous = CircuitRecords::save_record($machine->sap_code, $ldt, $length, $speed,$sd,$raw_string);
                        $percentage_allow=2.6;
                        if(CircuitRecords::plus_minus_percentage($percentage_allow, $speed, $latest_record[0]->speed ) || ($latest_record[0]['user_id']!=$loginRecord["user_id"] || $latest_record[0]['job_id']!=$loginRecord["job_id"] || $latest_record[0]['process_id']!=$loginRecord["process_id"])||(Shift::which_shift($latest_record[0]->run_date_time,$shifts)->shift_number!=Shift::which_shift($ldt,$shifts)->shift_number ||  date('Y-m-d',strtotime(Shift::which_shift($latest_record[0]->run_date_time,$shifts)->date))!= date('Y-m-d',strtotime(Shift::which_shift($ldt,$shifts)->date))))
                        
//                        if(CircuitRecords::plus_minus_percentage($percentage_allow, $speed, $latest_record[0]->speed ) || ($latest_record[0]['user_id']!=$loginRecord["user_id"] || $latest_record[0]['job_id']!=$loginRecord["job_id"] || $latest_record[0]['process_id']!=$loginRecord["process_id"]))
                        {
                            if($previous != null)
                            {
                                if($previous->LDT != $latest_record[0]["run_date_time"])
                                {

                                    if($this->live($machine, $previous->LDT, $previous->Mtr , $previous->Rpm,$latest_record[0],$latest_record, $logger))
                                    {
                                        if($this->live($machine, $ldt, $length, $speed,$loginRecord,$latest_record, $logger))
                                        {
                                              if($latest_record[0]['job_id']!=$loginRecord["job_id"]){
                                          
                                            return response("Accepted", "Reset");
                                        }
                                        
                                        else{
                                            return response("Accepted",404);
                                        }
                                    }
                                        else
                                        {
                                            return response("Not Accepted", 500);
                                        }
                                    }
                                }
                                else
                                {
                                    if($this->live($machine, $ldt, $length, $speed,$loginRecord,$latest_record, $logger))
                                    {
                                       if($latest_record[0]['job_id']!=$loginRecord["job_id"]){
                                          
                                            return response("Accepted", "Reset");
                                        }
                                        
                                        else{
                                            return response("Accepted",404);
                                        }
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
                             return response("Accepted",404);

                        }
                    }
                    else
                    {
                        CircuitRecords::save_record($machine->sap_code, $ldt, $length, $speed,$sd,$raw_string);
                        if($this->live($machine, $ldt, $length, $speed,$loginRecord,$latest_record, $loginRecord))
                        {
                            if($latest_record[0]['job_id']!=$loginRecord["job_id"])
                                                                                {
                                            if($latest_record[0]['job_id']!=$loginRecord["job_id"])
                                            {
                                               return response("Accepted","Reset");
                                            }
                                            else{
                                                return response("Accepted",404);
                                           }
                                        }
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
    
    public function testing_data(){
       $data = temp_table::where('id','=','1')->get();
       return $data;
       dump($data);
       foreach($data as $d){
           dump($d);
            $string = 'api/Num/'.$d->machine_id.'/LDT/'.$d->ldt.'/Mtr/'.$d->meters.'/Rpm/'.$d->rpm.'/Sd/0';
            $url = 'http://rotoeye.packages.com.pk/'.$string;
            dump($url);
            $response = Http::get($url);
            dump(response);
            if($response == 'Accepted'){
                $d->adjusted = '1';
            }
        }
    }
    
    public function getLatestRecord($id)
    {
        return response()->json(['latest_record'=>Record::where('machine_id','=',$id)
            ->orderby('run_date_time', 'DESC')
            ->limit(1)
            ->get()],200);
    }

    public function manualSaveRecord(Request $request)
    {
        try {
            $manual_record=array(
                "downtimeTo"=>"To",
                "downtimeFrom"=>"From",
                "length"=>"Length",
                "downtimeID"=>"Downtime",
                "machine_id"=>"Machine",
            );
            $validator=Validator::make($request->all(),
                [
                    "downtimeTo"=>"required",
                    "downtimeFrom"=>"required",
                    "length"=>"required",
                    "downtimeID"=>"required",
                    "machine_id"=>"required",
                ]);
            $validator->setAttributeNames($manual_record);
            if($validator->fails())
            {
                return response()->json(["error"=>"Feilds Are Missing"],500);
            }
            $to = $request->input('downtimeTo');
            $from = $request->input('downtimeFrom');
            $length = $request->input('length');
            $downtimeId = $request->input('downtimeID');
            $downtimeDescription = $request->input('downtimeDescription');
            $machine = Machine::find($request->input('machine_id'));
            $logger = new LoggerController($machine);
            $loginRecord = LoginRecord::where('machine_id', '=', $machine->id)->first();
            $prev_record = Record::where('machine_id','=',$machine->id)
                ->orderby('run_date_time', 'DESC')
                ->limit(1)
                ->get();
            $difference = date_diff(date_create(date('d-M-Y H:i:s',strtotime($prev_record[0]->run_date_time))), date_create(date('d-M-Y H:i:s',strtotime($to))));
            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s/60;
            if($machine->time_uom == 'Hr'){
                if($duration == 0){
                    $instantSpeed = 0;
                }
                else{
                    $instantSpeed = ($length/$duration)*60;
                }
            }
            elseif($machine->time_uom == 'Min'){
                if($duration == 0){
                    $instantSpeed = 0;
                }
                else{
                    $instantSpeed = $length/$duration;
                }
            }
            else{
                if($duration == 0){
                    $instantSpeed = 0;
                }
                else{
                    $instantSpeed = ($length/$duration)/60;
                }
            }
            $save_prev = new Record();
            $save_prev->user_id = $prev_record[0]->user_id;
            $save_prev->error_id = $prev_record[0]->error_id;
            $save_prev->job_id = $prev_record[0]->job_id;
            $save_prev->machine_id = $machine->id;
            $save_prev->err_comments = $prev_record[0]->err_comments;
            $save_prev->speed = $instantSpeed;
            $save_prev->run_date_time = $prev_record[0]->run_date_time;
            $save_prev->length = $prev_record[0]->length;
            $save_prev->process_id = $prev_record[0]->process_id;
            $save_prev->save();
            $raw_string = $request->getClientIp().'->Raw String: /manual/allocate/downtime/'.$machine->id.'/LDT/'.$prev_record[0]->run_date_time.'/Mtr/'.$prev_record[0]->length.'/Rpm/'.$instantSpeed.'/Sd/0';
            CircuitRecords::save_record($machine->sap_code, $prev_record[0]->run_date_time, $prev_record[0]->length, $instantSpeed,0,$raw_string);
            $record = new Record();
            $record->user_id = $loginRecord->user_id;
            $record->error_id = $downtimeId;
            $record->job_id = $loginRecord->job_id;
            $record->machine_id = $machine->id;
            $record->err_comments = $downtimeDescription;
            $record->speed = $instantSpeed;
            $record->run_date_time = $to;
            $record->length = $prev_record[0]->length + $length;
            $record->process_id = $loginRecord->process_id;
            $record->save();
            $raw_string = $request->getClientIp().'->Raw String: /manual/allocate/downtime/'.$machine->id.'/LDT/'.$to.'/Mtr/'.$record->length.'/Rpm/'.$instantSpeed.'/Sd/0';
            CircuitRecords::save_record($machine->sap_code, $to, $prev_record[0]->length + $length, $instantSpeed,0,$raw_string);

            return response()->json(['latest_record'=>$to],200);
        }
        catch (\Exception $e)
        {
            return response()->json(['error'=>$e->getMessage()],500);
        }
    }
}
