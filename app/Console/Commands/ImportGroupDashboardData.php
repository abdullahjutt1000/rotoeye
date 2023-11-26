<?php

namespace App\Console\Commands;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoggerController;
use App\Jobs\SendNotRespondingCircuitsEmail;
use App\Jobs\SendWrongDateTimeStringsEmail;
use App\Models\CircuitLog;
use App\Models\CircuitLogs;
use App\Models\Machine;
use App\Models\Record;
use App\Models\Shift;
use App\Models\Error;
use App\Models\LoginRecord;
use App\Models\Users;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\GroupProductionReport;
use Illuminate\Support\Facades\DB;

class ImportGroupDashboardData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:importgroupdashboarddata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            Log::info("======================Cron Job Started for groupdashboard(" . date('Y-m-d H:i:s') . ")========================");
            $machines = Machine::whereNull('is_disabled')->orderBy("sd_status","ASC")->get();
            $data['machines'] = [];
            $data['records'] = [];
            $data['wrongDateTimeStrings'] = [];

            foreach ($machines as $machine) {
                $machine_id = $machine->id;
                $logger = new LoggerController($machine);
                $logger->log('--------------------------------------------- CRON JOB START for  groupdashboard ---------------------------------------------', $machine);
               
                $count = 0;
                try {
                    $currentDate = date("Y-m-d");
                        $date = date("Y-m-d", strtotime($currentDate . "-1 day")); //date('Y-m-d');
                        $to_date = date("Y-m-d", strtotime($currentDate . "-1 day"));//date('Y-m-d');
                        //dd($dateRange);
                        $shiftSelection[] = 'All-Day';//$request->input('shiftSelection');
                        if($shiftSelection[0] == 'All-Day'){
                        //dd("sd");
                            $machine=Machine::find($machine_id);
                            $shifts_id=[];
                            foreach ($machine->section->department->businessUnit->company->shifts as $shift){
                                array_push($shifts_id,$shift->id);
                            }
                            //dd($shifts_id);
                            $minStarted = Shift::find($shifts_id[0])->min_started;
                            $minEnded = Shift::find($shifts_id[count($shifts_id)-1])->min_ended;
                            $from_date = $date;
                            $to_date = $to_date;
                        
                            $data['from'] = $from_date;
                            $data['to'] = $to_date;
                        // dd($data['from']);
                            $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
                            $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));
                        // dd($endDateTime);
                        }
                        else{
                            $minStarted = Shift::find($shiftSelection[0])->min_started;
                            $minEnded = Shift::find($shiftSelection[count($shiftSelection)-1])->min_ended;
                            $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                            $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
                        }

                        if(date('Y-m-d H:i:s') < $endDateTime){
                            $endDateTime = date('Y-m-d H:i:s');
                        }

                        $data['machine'] = Machine::find($machine_id);
                        $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

                        $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                        $runningCodes = Error::select('id')->where('category', '=', 'Running')->orWhere('category', '=', 'Waste')->get();
                        $wasteCodes = Error::select('id')->where('category', '=', 'Waste')->get();
                        $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

                        //dd($endDateTime);

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
                                'material_combination.name as material_combination','process_structure.color as process_structure_color', 'material_combination.nominal_speed as nominal_speed', 'records.user_id as user_id', 'users.name as user_name',
                                'processes.process_name as process_name')
                            ->where('machine_id', '=', $machine_id)
                            ->where('records.run_date_time', '>=', $startDateTime)
                            ->where('records.run_date_time', '<=', $endDateTime)
                            ->orderby('run_date_time', 'ASC')
                            ->get();
                            //dd(count($records));
                            if(count($records) > 0){
                                Log::info("Cron for  groupdashboard Started(" . date('Y-m-d H:i:s') . "): " . $machine->name);
                                $data['records'] = [];
                                $data['negativeRecords'] = [];
                                $startDate = $records[0]->run_date_time;
                                $oldLength = $records[0]->length;
                                $runTime = 0;
                                $idleTime = 0;
                                $jobWaitTime = 0;
                                $production = 0;
                                $waste = 0;
                                $actualSpeed = 0;
                                $jobProduction = 0;
                                $jobWaste = 0;
                                $jobRunTime = 0;
                                $totalLength =0;

                                $totalTime = (strtotime($endDateTime) - strtotime($startDateTime))/60;
                                if(count($records) > 1){
                                    for ($i=0; $i<count($records); $i++){

                                        $rntime=0;
                                        $idltime=0;
                                        $jbtime=0;
                                        if(isset($records[$i+1])){
                                            if($records[$i]->error_id != $records[$i+1]->error_id || $records[$i]->user_id != $records[$i+1]->user_id || $records[$i]->job_id != $records[$i+1]->job_id){
                                                $endDate = $records[$i]->run_date_time;
                                                $difference = date_diff(date_create(date('d-M-Y H:i:s',strtotime($startDate))), date_create(date('d-M-Y H:i:s',strtotime($endDate))));
                                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s/60;
                                                $totalLength += $records[$i]->length-$oldLength;

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
                                                        $rntime += $duration;
                                                        $jobRunTime += $duration;
                                                        $production += $records[$i]->length - $oldLength;
                                                        $jobProduction += $records[$i]->length - $oldLength;
                                                    }
                                                }
                                                //Waste Codes Added
                                                foreach($wasteCodes as $wasteCode) {
                                                    if ($wasteCode->id == $records[$i]->error_id) {
                                                        $waste += $records[$i]->length - $oldLength;
                                                        $jobWaste += $records[$i]->length - $oldLength;
                                                    }
                                                }
                                                foreach($idleErrors as $idleError){
                                                    if($idleError->id == $records[$i]->error_id){
                                                        $idleTime += $duration;
                                                        $idltime=$duration;
                                                    }
                                                }
                                                foreach($jobWaitingCodes as $jobWaitingCode){
                                                    if ($jobWaitingCode->id == $records[$i]->error_id) {
                                                        $jobWaitTime += $duration;
                                                        $jbtime =$duration;
                                                    }
                                                }

                                                if($records[$i]->length  - $oldLength < 0){
                                                    //
                                                }

                                                array_push($data['records'],[
                                                    "job_id"=>$records[$i]->job_id,
                                                    "job_name"=>$records[$i]->job_name,
                                                    "product_number"=>$records[$i]->product_number,
                                                    "material_combination"=>$records[$i]->material_combination,
                                                    "process_structure_color"=>$records[$i]->process_structure_color,
                                                    "nominal_speed"=>$records[$i]->nominal_speed,
                                                    "user_id"=>$records[$i]->user_id,
                                                    "user_name"=>$records[$i]->user_name,
                                                    "job_length"=>$records[$i]->job_length,
                                                    "error_id"=>$records[$i]->error_id,
                                                    "error_name"=>$records[$i]->error_name,
                                                    "comments"=>str_replace('#', 'no', $records[$i]->comments),
                                                    "length"=>$records[$i]->length-$oldLength,
                                                    "from"=>$startDate,
                                                    "to"=>$endDate,
                                                    "duration"=>$duration,
                                                    "instantSpeed"=>$instantSpeed,
                                                    "process_name"=>$records[$i]->process_name,
                                                    "run_time"=>$rntime,
                                                    "idleTime"=>$idltime,
                                                    "jobWaitingTime"=>$jbtime
                                                ]);

                                                $startDate = $endDate;
                                                if($records[$i]->job_id != $records[$i+1]->job_id){
                                                    $oldLength = $records[$i+1]->length;
                                                    if($jobRunTime == 0){
                                                        $jobPerformance = 0;
                                                        $jobAverageSpeed = 0;
                                                    }
                                                    else{
                                                        if($data['machine']->time_uom == 'Min'){
                                                            $jobPerformance = (($jobProduction/$jobRunTime)/$data['machine']->max_speed)*100;
                                                            $jobAverageSpeed = $jobProduction/$jobRunTime;
                                                        }
                                                        elseif($data['machine']->time_uom == 'Hr'){
                                                            $jobPerformance = (($jobProduction/$jobRunTime)*60/$data['machine']->max_speed)*100;
                                                            $jobAverageSpeed = ($jobProduction/$jobRunTime)*60;
                                                        }
                                                        elseif($data['machine']->time_uom == 'Sec'){
                                                            $jobPerformance = ((($jobProduction/$jobRunTime)/60)/$data['machine']->max_speed)*100;
                                                            $jobAverageSpeed = (($jobProduction/$jobRunTime)/60);
                                                        }
                                                    }
                                                    for($j=0; $j<count($data['records']); $j++){
                                                        array_push($data['records'][$j],[
                                                            "jobProduction"=>$jobProduction,
                                                            "jobPerformance"=>$jobPerformance,
                                                            "jobRuntime"=>$jobRunTime,
                                                            "jobAverageSpeed"=>$jobAverageSpeed,
                                                            "jobWaste"=>$jobWaste
                                                        ]);
                                                    }
                                                    $jobProduction = 0;
                                                    $jobRunTime = 0;
                                                    $jobWaste = 0;
                                                }
                                                else{
                                                    $oldLength = $records[$i]->length;
                                                }
                                            }
                                        }
                                        else{
                                            $endDate = $records[$i]->run_date_time;
                                            $difference = date_diff(date_create(date('d-M-Y H:i:s',strtotime($startDate))), date_create(date('d-M-Y H:i:s',strtotime($endDate))));
                                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s/60;
                                            $totalLength += $records[$i]->length-$oldLength;

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
                                                    $rntime +=$duration;
                                                    $production += $records[$i]->length - $oldLength;
                                                    $jobProduction += $records[$i]->length - $oldLength;
                                                }
                                            }

                                            foreach($wasteCodes as $wasteCode) {
                                                if ($wasteCode->id == $records[$i]->error_id) {
                                                    $waste += $records[$i]->length - $oldLength;
                                                    $jobWaste += $records[$i]->length - $oldLength;
                                                }
                                            }
                                            foreach($idleErrors as $idleError){
                                                if($idleError->id == $records[$i]->error_id){
                                                    $idleTime += $duration;
                                                    $idltime=$duration;
                                                }
                                            }
                                            foreach($jobWaitingCodes as $jobWaitingCode){
                                                if ($jobWaitingCode->id == $records[$i]->error_id) {
                                                    $jobWaitTime += $duration;
                                                    $jbtime =$duration;
                                                }
                                            }
                                            if($records[$i]->length  - $oldLength < 0){
                                                //
                                            }
                                            array_push($data['records'],[
                                                "job_id"=>$records[$i]->job_id,
                                                "job_name"=>$records[$i]->job_name,
                                                "product_number"=>$records[$i]->product_number,
                                                "material_combination"=>$records[$i]->material_combination,
                                                "process_structure_color"=>$records[$i]->process_structure_color,
                                                "nominal_speed"=>$records[$i]->nominal_speed,
                                                "user_name"=>$records[$i]->user_name,
                                                "user_id"=>$records[$i]->user_id,
                                                "job_length"=>$records[$i]->job_length,
                                                "error_id"=>$records[$i]->error_id,
                                                "error_name"=>$records[$i]->error_name,
                                                "comments"=>str_replace('#', 'no', $records[$i]->comments),
                                                "length"=>$records[$i]->length-$oldLength,
                                                "from"=>$startDate,
                                                "to"=>$endDate,
                                                "duration"=>$duration,
                                                "instantSpeed"=>$instantSpeed,
                                                "jobProduction"=>$production,
                                                "process_name"=>$records[$i]->process_name,
                                                "run_time"=>$rntime,
                                                "idleTime"=>$idltime,
                                                "jobWaitingTime"=>$jbtime
                                            ]);
                                            $startDate = $endDate;
                                            $oldLength = $records[$i]->length;
                                        }
                                    }


                                    for($k=0; $k<count($data['records']); $k++){
                                        if($jobRunTime == 0){
                                            $jobPerformance = 0;
                                            $jobAverageSpeed = 0;
                                        }
                                        else{
                                            if($data['machine']->time_uom == 'Min'){
                                                $jobPerformance = (($jobProduction/$jobRunTime)/$data['machine']->max_speed)*100;
                                                $jobAverageSpeed = $jobProduction/$jobRunTime;
                                            }
                                            elseif($data['machine']->time_uom == 'Hr'){
                                                $jobPerformance = (($jobProduction/$jobRunTime)*60/$data['machine']->max_speed)*100;
                                                $jobAverageSpeed = ($jobProduction/$jobRunTime)*60;
                                            }
                                            elseif($data['machine']->time_uom == 'Sec'){
                                                $jobPerformance = ((($jobProduction/$jobRunTime)/60)/$data['machine']->max_speed)*100;
                                                $jobAverageSpeed = (($jobProduction/$jobRunTime)/60);
                                            }
                                        }
                                        if(!isset($data['records'][$k][0]['jobProduction']) || !isset($data['records'][$k][0]['jobPerformance']) ){
                                            array_push($data['records'][$k],[
                                                "jobProduction"=>$jobProduction,
                                                "jobPerformance"=>$jobPerformance,
                                                "jobRuntime"=>$jobRunTime,
                                                "jobAverageSpeed"=>$jobAverageSpeed,
                                                "jobWaste"=>$jobWaste
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
                               // if(Session::get('rights') == 0){
                                    $data['user'] = Users::find($loginRecord[0]->user_id);
                               // }
                               // else{
                                  //  $data['user'] = Users::find(Session::get('user_name'));
                               // }

                                $data['produced'] = $production;
                                $data['waste'] = $waste;
                                $data['quality'] = ($production>0)?100-(($waste/$production)*100):0;
                                $data['oee'] = ($production>0)?($runTime/($totalTime - $idleTime))*($actualSpeed/$data['machine']->max_speed)*((100-(($waste/$production)*100))/100)*100:0;
                                $data['ee'] = ($production>0)?($runTime/($totalTime - $idleTime - $jobWaitTime))*($actualSpeed/$data['machine']->max_speed)*((100-(($waste/$production)*100))/100)*100:0;
                                $data['performance'] = ($actualSpeed/$data['machine']->max_speed)*100;
                                $data['availability_ee'] = ($runTime/($totalTime - $idleTime - $jobWaitTime))*100;
                                $data['availability'] = ($runTime/($totalTime - $idleTime))*100;
                                $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
                                $data['run_time'] = $runTime;
                                $data['budgetedTime'] = $totalTime - $idleTime;
                                $data['totalLength'] = $totalLength;
                                $data['shift'] = $shiftSelection[0];
                                $data['date'] = $date;
                                $data['current_time']=date('Y-m-d H:i:s');

                                //////////// my code 
                                $data['startDateTime'] = $startDateTime;
                                $data['endDateTime'] = $endDateTime;
                             // dd($data['endDateTime']);
                                $existingRecords = GroupProductionReport::where(function ($query) use ($data) {
                                $query->where(function ($query) use ($data) {
                                    $query->where('from_time', '>', $data['startDateTime']);
                                    $query->where('from_time', '<', $data['endDateTime']);
                                });
                                $query->orwhere(function ($query) use ($data) {
                                    $query->where('to_time', '>', $data['startDateTime']);
                                    $query->where('to_time', '<', $data['endDateTime']);
                                });
                                })->where('machine_id','=',$data['machine']->id)->get();    
                                
                        
                                    if(isset($existingRecords)){
                                        if(count($existingRecords) > 0 ){
                                            //if(count($existingRecords) < count($data['records'])){
                                            foreach($existingRecords as $r){
                                            $r->delete();
                                            }
                                        // }
                                        }
                                    }
                                
                                    $dateParts = explode('/', $data['date']);
                                    $month = $dateParts[0];
                                    $monthInt = (int)$month;
                                    $monthZeroPadded = sprintf("%02d", $monthInt);

                                    $dateStr = $data['date'];
                                    $timestamp = strtotime($dateStr);
                                    $formattedDate = $data['startDateTime'];//date("Y-m-d", $timestamp);
                                    //dd($formattedDate);
                                    foreach($data['records'] as $record){
                                    // dd($machine->section);
                                    $reportdata = new GroupProductionReport();
                                    $reportdata->job_no = $record['job_id'];
                                    $reportdata->job_name = $record['job_name'];
                                    $reportdata->machine_id = $data['machine']->id;
                                    $reportdata->machine_no =$data['machine']->sap_code;
                                    $reportdata->err_no = $record['error_id'];
                                    $reportdata->err_name = $record['error_name'];
                                    $reportdata->err_comments = $record['comments'];
                                    $reportdata->from_time = $record['from'];
                                    $reportdata->to_time = $record['to'];
                                    $reportdata->duration = $record['duration'];
                                    $reportdata->length = $record['length'];
                                    $reportdata->date = $record['from'];
                                    $reportdata->company_id =$machine->section->department->businessUnit->company->id;
                                    $reportdata->company_name =$machine->section->department->businessUnit->company->name;
                                    $reportdata->company_id =$machine->section->department->businessUnit->company->id;
                                    $reportdata->business_unit_id =$machine->section->department->businessUnit->id;
                                    $reportdata->business_unit_name =$machine->section->department->businessUnit->business_unit_name;
                                    $reportdata->department_id =$machine->section->department->id;
                                    $reportdata->department_name =$machine->section->department->name;
                                    $reportdata->section_id =$machine->section->id;
                                    $reportdata->section_name =$machine->section->name;
                                    $reportdata->month =$monthZeroPadded;
                                    $reportdata->operator_id =$record['user_id'];
                                    $reportdata->operator_name =$record['user_name'];
                                    $reportdata->product_number =$record['product_number'];
                                    $reportdata->material_combination =$record['material_combination'];
                                    $reportdata->nominal_speed =$record['nominal_speed'];
                                    $reportdata->run_time =$record['run_time'];
                                    $reportdata->idleTime=$record['idleTime'];
                                    $reportdata->job_wating_time=$record['jobWaitingTime'];
                                    $reportdata->save();
                                //  }
                                }
                                Log::info("Cron for  groupdashboard Started(" . date('Y-m-d H:i:s') . "): " . $machine->name);
                            }
                    
                } catch (\Exception $e) {
                    $logger->log('<<<< EXCEPTION >>>>', $machine);
                   // $logger->log('Exception while getting JSON from Circuit', $machine);
                  //  $logger->log('Got Exception: ' . $e->getMessage(), $machine);
                   // $logger->log('<<<< EXCEPTION >>>>', $machine);
                }
               
            }
        Log::info("======================Cron Job Ended(" . date('Y-m-d H:i:s') . ")==========================");
        } catch (\Exception $e) {
            Log::info("======================Fetch Local Records Cron Exception========================");
            Log::info("================================================================================");
            Log::info($e->getMessage());
            Log::info("================================================================================");
            Log::info("======================Fetch Local Records Cron Exception========================");

        }

    }

    public function calculateMinutes($fromTime, $toTime)
    {
        $diff = date_diff(date_create($toTime), date_create($fromTime));
        $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s / 60;
        if ($diff->invert) {
            return -1 * $total;
        } else {
            return $total;
        }
    }
}

