<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\Error;
use App\Models\Job;
use App\Models\LoginRecord;
use App\Models\Machine;
use App\Models\Machine_User;
use App\Models\Productivity;
use App\Models\OEEView;
use App\Models\Record;
use App\Models\Shift;
use App\Models\User;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Route;

class ProductivityController extends Controller
{

    public function cal_oee_dashboard($date,$shift,$machine_id){

        $minStarted = $shift->min_started;
        $minEnded = $shift->min_ended;
        $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
        $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
        $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
        $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
        $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

        if (date('Y-m-d H:i:s') < $endDateTime) {
            $endDateTime = date('Y-m-d H:i:s');
        }

        $records = DB::table('records')
            ->leftJoin('errors', 'errors.id', '=', 'records.error_id')
            ->leftJoin('users', 'users.id', '=', 'records.user_id')
            ->leftJoin('jobs', 'jobs.id', '=', 'records.job_id')
            ->leftJoin('products', 'products.id', '=', 'jobs.product_id')
            ->leftJoin('process_structure', function ($join) {
                $join->on('process_structure.process_id', '=', 'records.process_id');
                $join->on('process_structure.product_id', '=', 'products.id');
            })
            ->leftJoin('material_combination', 'material_combination.id', '=', 'process_structure.material_combination_id')
            ->leftJoin('processes', 'processes.id', '=', 'process_structure.process_id')
            ->select('errors.name as error_name', 'records.run_date_time as run_date_time', 'records.error_id as error_id', 'records.length as length', 'records.err_comments as comments',
                'jobs.id as job_id', 'products.name as job_name', 'jobs.job_length as job_length', 'products.name as product_name', 'products.id as product_number',
                'material_combination.name as material_combination', 'material_combination.nominal_speed as nominal_speed', 'records.user_id as user_id', 'users.name as user_name',
                'processes.process_name as process_name')
            ->where('machine_id', '=', $machine_id)
            ->where('records.run_date_time', '>=', $startDateTime)
            ->where('records.run_date_time', '<=', $endDateTime)
            ->orderby('run_date_time', 'ASC')
            ->get();

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
            $jobRunTime = 0;

            if(count($records) > 1){
                for ($i=0; $i<count($records); $i++){
                    if(isset($records[$i+1])){
                        if($records[$i]->error_id != $records[$i+1]->error_id || $records[$i]->user_id != $records[$i+1]->user_id || $records[$i]->job_id != $records[$i+1]->job_id){
                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s',strtotime($startDate))), date_create(date('d-M-Y H:i:s',strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s/60;
                            foreach($runningCodes as $runningCode) {
                                if ($runningCode->id == $records[$i]->error_id) {
                                    $runTime += $duration;
                                    $jobRunTime += $duration;
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


                            if($records[$i]->length  - $oldLength < 0){
                                $this->resolveNegatives($startDate, $endDate, $machine_id);
                            }

                            $startDate = $endDate;
                            if($records[$i]->job_id != $records[$i+1]->job_id){
                                $oldLength = $records[$i+1]->length;
                                $jobProduction = 0;
                                $jobRunTime = 0;
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
                        if($records[$i]->length  - $oldLength < 0){

                            $this->resolveNegatives($startDate, $endDate, $machine_id);
                        }

                        $startDate = $endDate;
                        $oldLength = $records[$i]->length;


                        Productivity::updateOrInsert(['machine_id' => $machine_id,
                            'date' => date('Y-m-d',strtotime($date)),
                            'shift_id' => $shift->id],
                            ['total_running_time' => round($runTime, 0),
                                'total_production' => round($production, 0),
                                'total_job_waiting_time' => round($jobWaitTime, 0),
                                'total_idle_time' => round($idleTime, 0)]);
                        return "success, Record Successfully updated.";
                    }
                }

            }
        } else {
            return "error, No Record for the selected shift and date. Please try again.";
        }
    }

    public function resolveNegatives($startDateTime, $endDateTime, $machine_id){
        $negativeRecords = Record::where('run_date_time', '>=', $startDateTime)->where('run_date_time','<=',$endDateTime)->where('machine_id','=',$machine_id)->orderby('run_date_time', 'ASC')->get();
        DB::beginTransaction();
        for($i=0; $i<count($negativeRecords); $i++){
            $run_date_time = date_create($negativeRecords[$i]->run_date_time);
            $created_at = date_create($negativeRecords[$i]->created_at);
            $diff = $created_at->diff($run_date_time);
            $minutes = $diff->days * 24 * 60;
            $minutes += $diff->h * 60;
            if(isset($negativeRecords[$i+1])){
                if($negativeRecords[$i]->error_id == $negativeRecords[$i+1]->error_id && $negativeRecords[$i]->job_id == $negativeRecords[$i+1]->job_id && $negativeRecords[$i]->user_id == $negativeRecords[$i+1]->user_id && $negativeRecords[$i]->machine_id == $negativeRecords[$i+1]->machine_id){
                    if($negativeRecords[$i+1]->length < $negativeRecords[$i]->length){
                        $job = Job::where('product_id', '=', $negativeRecords[$i+1]->job->product_id)->where('id', '!=', $negativeRecords[$i+1]->job_id)->first();
                        if(!empty($job)){
                            $negativeRecords[$i+1]->job_id = $job->id;
                            $negativeRecords[$i+1]->save();
                        }
                        else{
                            $negativeRecords[$i+1]->job_id = 'Negative Meters';
                            $negativeRecords[$i+1]->save();
                        }
                    }
                }
                elseif($negativeRecords[$i]->error_id != $negativeRecords[$i+1]->error_id && $negativeRecords[$i]->job_id == $negativeRecords[$i+1]->job_id && $negativeRecords[$i]->user_id == $negativeRecords[$i+1]->user_id && $negativeRecords[$i]->machine_id == $negativeRecords[$i+1]->machine_id){
                    if($negativeRecords[$i+1]->length < $negativeRecords[$i]->length){
                        $job = Job::where('product_id', '=', $negativeRecords[$i+1]->job->product_id)->where('id', '!=', $negativeRecords[$i+1]->job_id)->first();
                        if(!empty($job)){
                            $negativeRecords[$i+1]->job_id = $job->id;
                            $negativeRecords[$i+1]->save();
                        }
                        else{
                            $negativeRecords[$i+1]->job_id = 'Negative Meters';
                            $negativeRecords[$i+1]->save();
                        }
                    }
                }
            }
        }
        DB::commit();
    }

    public function oee_dashboard($from,$to)
    {
        $machines  = Machine::whereNull('is_disabled')->get();
        $data=[];
        foreach ($machines as $machine)
        {
            $date = $from;
            $shfts = $machine->section->department->businessUnit->company->shifts ;
            while ($date<=$to){

                foreach ($shfts as $shift)
                {
                    $response = $this->cal_oee_dashboard($date,$shift,$machine->id);
                    array_push($data,['date'=>$date,'shift'=>$shift->shift_number,'machine_id'=>$machine->id,'response'=>$response]);
                }
                $date=date('Y-m-d',strtotime($date.'+1 day'));
            }
        }

        return $data;
    }

    public function index($id)
    {
        $user_id = Session::get('user_id');
        if($user_id){
            $data['path'] = \Illuminate\Support\Facades\Route::getFacadeRoot()->current()->uri();
            if(Session::get('rights') == 0){
                $data['layout'] = 'web-layout';
            }
            elseif(Session::get('rights') == 1){
                $data['layout'] = 'admin-layout';
            }
            elseif(Session::get('rights') == 2){
                $data['layout'] = 'power-user-layout';
            }
            $data['companies'] = Company::all();
            $data['machine'] = Machine::find(Crypt::decrypt($id));
            $data['user'] = Users::find(Session::get('user_name'));
            $data['allowed_machines'] = Users::find(Session::get('user_name'))->allowedMachines;
            return view('roto.productivity',$data);
        }
        else{
            Session::flash('error', 'Please login again to continue.');
            return \redirect('login');
        }

    }

    public function get_records(Request $request)
    {
        $data=[];
        if($request->rights== 1){
            if($request->month==null)
            {
                if($request->company_id==null) {
                    $data['records'] = DB::table("productivities")
                        ->selectRaw("productivities.date as date")
                        ->selectRaw("((ifnull(SUM(total_production)/SUM(total_running_time),0))/machines.max_speed)*100 as performance")
                        ->selectRaw("(ifnull(SUM(total_running_time),0)/(SUM(shifts.shift_duration)-SUM(total_idle_time)))*100 as availability")
                        ->selectRaw("(ifnull(SUM(total_running_time),0)/(SUM(shifts.shift_duration)-SUM(total_idle_time)))*100 as availability")
                        ->selectRaw("ROUND((((ifnull(SUM(total_production)/SUM(total_running_time),0))/machines.max_speed)*(SUM(total_running_time)/(SUM(shifts.shift_duration)-SUM(total_idle_time))))*100,2) as OEE")
                        ->leftJoin('machines', 'productivities.machine_id', '=', 'machines.id')
                        ->leftJoin('shifts', 'productivities.shift_id', '=', 'shifts.id')
                        ->leftJoin('sections', 'machines.section_id', '=', 'sections.id')
                        ->leftJoin('departments', 'sections.department_id', '=', 'departments.id')
                        ->leftJoin('business_units', 'departments.business_unit_id', '=', 'business_units.id')
                        ->leftJoin('companies', 'business_units.company_id', '=', 'companies.id')
                        ->groupByRaw('MONTH(productivities.date)')
                        ->whereYear('productivities.date', date('Y'))
                        ->orderBy('date')
                        ->get();
                }
                else
                {
                    $data['records'] = DB::table("productivities")
                        ->selectRaw("productivities.date as date")
                        ->selectRaw("((ifnull(SUM(total_production)/SUM(total_running_time),0))/machines.max_speed)*100 as performance")
                        ->selectRaw("(ifnull(SUM(total_running_time),0)/(SUM(shifts.shift_duration)-SUM(total_idle_time)))*100 as availability")
                        ->selectRaw("(ifnull(SUM(total_running_time),0)/(SUM(shifts.shift_duration)-SUM(total_idle_time)))*100 as availability")
                        ->selectRaw("ROUND((((ifnull(SUM(total_production)/SUM(total_running_time),0))/machines.max_speed)*(SUM(total_running_time)/(SUM(shifts.shift_duration)-SUM(total_idle_time))))*100,2) as OEE")
                        ->leftJoin('machines', 'productivities.machine_id', '=', 'machines.id')
                        ->leftJoin('shifts', 'productivities.shift_id', '=', 'shifts.id')
                        ->leftJoin('sections', 'machines.section_id', '=', 'sections.id')
                        ->leftJoin('departments', 'sections.department_id', '=', 'departments.id')
                        ->leftJoin('business_units', 'departments.business_unit_id', '=', 'business_units.id')
                        ->leftJoin('companies', 'business_units.company_id', '=', 'companies.id')
                        ->groupByRaw('MONTH(productivities.date)')
                        ->whereYear('productivities.date', date('Y'))
                        ->where('companies.id','=',$request->company_id)
                        ->orderBy('date')
                        ->get();
                }
            }
            else
            {
                $data['records'] = DB::table("productivities")
                    ->selectRaw("companies.name as companies_name")
                    ->selectRaw("((ifnull(SUM(total_production)/SUM(total_running_time),0))/machines.max_speed)*100 as performance")
                    ->selectRaw("(ifnull(SUM(total_running_time),0)/(SUM(shifts.shift_duration)-SUM(total_idle_time)))*100 as availability")
                    ->selectRaw("ROUND((((ifnull(SUM(total_production)/SUM(total_running_time),0))/machines.max_speed)*(SUM(total_running_time)/(SUM(shifts.shift_duration)-SUM(total_idle_time))))*100,2) as OEE")
                    ->leftJoin('machines', 'productivities.machine_id', '=', 'machines.id')
                    ->leftJoin('shifts', 'productivities.shift_id', '=', 'shifts.id')
                    ->leftJoin('sections', 'machines.section_id', '=', 'sections.id')
                    ->leftJoin('departments', 'sections.department_id', '=', 'departments.id')
                    ->leftJoin('business_units', 'departments.business_unit_id', '=', 'business_units.id')
                    ->leftJoin('companies', 'business_units.company_id', '=', 'companies.id')
                    ->groupByRaw('companies.id')
                    ->whereYear('productivities.date',date('Y'))
                    ->whereMonth('productivities.date',$request->month)
                    ->orderBy('date')
                    ->get();
            }
            return response($data,200);
        }
        elseif($request->rights == 2){
//            dd($request->allowed_machines);
//            dd(array_column($request->allowed_machines,'id'));
            $data['records'] = DB::table("productivities")
                ->selectRaw("productivities.date as date")
                ->selectRaw("((ifnull(SUM(total_production)/SUM(total_running_time),0))/machines.max_speed)*100 as performance")
                ->selectRaw("(ifnull(SUM(total_running_time),0)/(SUM(shifts.shift_duration)-SUM(total_idle_time)))*100 as availability")
                ->selectRaw("(ifnull(SUM(total_running_time),0)/(SUM(shifts.shift_duration)-SUM(total_idle_time)))*100 as availability")
                ->selectRaw("ROUND((((ifnull(SUM(total_production)/SUM(total_running_time),0))/machines.max_speed)*(SUM(total_running_time)/(SUM(shifts.shift_duration)-SUM(total_idle_time))))*100,2) as OEE")
                ->leftJoin('machines', 'productivities.machine_id', '=', 'machines.id')
                ->leftJoin('shifts', 'productivities.shift_id', '=', 'shifts.id')
                ->leftJoin('sections', 'machines.section_id', '=', 'sections.id')
                ->leftJoin('departments', 'sections.department_id', '=', 'departments.id')
                ->leftJoin('business_units', 'departments.business_unit_id', '=', 'business_units.id')
                ->leftJoin('companies', 'business_units.company_id', '=', 'companies.id')
                ->groupByRaw('MONTH(productivities.date)')
                ->groupByRaw('productivities.machine_id')
                ->whereYear('productivities.date', date('Y'))
                ->whereIn('productivities.machine_id' ,array_column($request->allowed_machines,'id'))
                ->orderBy('date')
                ->get();

            return response($data,200);
        }
        else
        {
            return response("authentication error",500);
        }

    }

}
