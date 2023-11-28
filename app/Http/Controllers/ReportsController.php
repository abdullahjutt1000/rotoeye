<?php

namespace App\Http\Controllers;

use App\Models\Productivity;
use Illuminate\Http\Request;

use App\Models\Job;
use App\Models\Error;
use App\Models\LoginRecord;
use App\Models\Machine;
use App\Models\Record;
use App\Models\Shift;
use App\Models\User;
use App\Models\ProductionReport;
use App\Models\Users;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
//// mine code
use App\Models\Categories;
use App\Models\GroupProductionReport;
///
use Route;
use Mail;
use DateTime;
use Carbon\Carbon;

class ReportsController extends Controller {

    public function reports($id) {
        $user_id = Session::get('user_id');
        if(isset($user_id)) {
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $machine_id = Crypt::decrypt($id);
            if(Session::get('rights') == 0) {
                $data['layout'] = 'web-layout';
            } elseif(Session::get('rights') == 1) {
                $data['layout'] = 'admin-layout';
            } elseif(Session::get('rights') == 2) {
                $data['layout'] = 'power-user-layout';
            } elseif(Session::get('rights') == 3) {
                $data['layout'] = 'reporting-user-layout';
            }
            $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();
            $data['machine'] = Machine::find($machine_id);
            if(Session::get('rights') == 0) {
                $data['user'] = Users::find($loginRecord[0]->user_id);
            } else {
                $data['user'] = Users::find(Session::get('user_name'));
            }
            $data['record'] = Record::where('machine_id', '=', $machine_id)->latest('run_date_time')->first();
            $data['operators'] = Users::with('allowedMachines')->whereHas('allowedMachines', function ($query) use ($machine_id) {
                $query->where('machine_id', '=', $machine_id);
            })->where('rights', '=', 0)->get();
            $data['errorCodes'] = $data['machine']->section->department->errorCodes;
            $data['productionOrders'] = Job::all();
            /// mine code
            $data['errorCategories'] = Categories::all();
            /// mine code
            //dd($data['errorCategories']);
            return view('reports.generate-reports', $data);
        } else {
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }
    public function reportsExport(Request $request, $id) {

        //dd($request->all());
        $user_id = Session::get('user_id');
        if(isset($user_id)) {
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $machine_id = Crypt::decrypt($id);
            $date = $request->date;
            if(Session::get('rights') == 0) {
                $data['layout'] = 'web-layout';
            } elseif(Session::get('rights') == 1) {
                $data['layout'] = 'admin-layout';
            } elseif(Session::get('rights') == 2) {
                $data['layout'] = 'power-user-layout';
            } elseif(Session::get('rights') == 3) {
                $data['layout'] = 'reporting-user-layout';
            }
            $data['machine'] = Machine::find($machine_id);
            $data['record'] = Record::where('machine_id', '=', $machine_id)->latest('run_date_time')->first();
            $shiftSelection = json_decode(urldecode($request->shiftSelection), true);
            if($shiftSelection[0] == 'All-Day') {
                $from_date = $request->date;
                $to_date = $request->to_date;

                $data['from'] = $from_date;
                $data['to'] = $to_date;

                $startDateTime = date('Y-m-d H:i:s', strtotime($from_date.' + 390 minutes'));
                $endDateTime = date('Y-m-d H:i:s', strtotime($to_date.' + 1830 minutes'));
            } else {

                $minStarted = Shift::find($shiftSelection[0])->min_started;
                $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;

                $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
            }

            $data['machine'] = Machine::find($machine_id);
            $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

            $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
            $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
            $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

            if(date('Y-m-d H:i:s') < $endDateTime) {
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
                ->select(
                    'errors.name as error_name',
                    'records.run_date_time as run_date_time',
                    'records.error_id as error_id',
                    'records.length as length',
                    'records.err_comments as comments',
                    'jobs.id as job_id',
                    'products.name as job_name',
                    'jobs.job_length as job_length',
                    'products.name as product_name',
                    'products.id as product_number',
                    'material_combination.name as material_combination',
                    'material_combination.nominal_speed as nominal_speed',
                    'records.user_id as user_id',
                    'users.name as user_name',
                    'processes.process_name as process_name'
                )
                ->where('machine_id', '=', $machine_id)
                ->where('records.run_date_time', '>=', $startDateTime)
                ->where('records.run_date_time', '<=', $endDateTime)
                ->orderby('run_date_time', 'ASC')
                ->get();
            if(count($records) > 0) {
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

                $totalTimeDifference = date_diff(date_create($startDateTime), date_create($endDateTime));
                $totalTime = (($totalTimeDifference->y * 365 + $totalTimeDifference->m * 30 + $totalTimeDifference->d) * 24 + $totalTimeDifference->h) * 60 + $totalTimeDifference->i + $totalTimeDifference->s / 60;
                if(count($records) > 1) {
                    for($i = 0; $i < count($records); $i++) {
                        if(isset($records[$i + 1])) {
                            if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                if($data['machine']->time_uom == 'Hr') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                    }
                                } elseif($data['machine']->time_uom == 'Min') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                    }
                                } else {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                    }
                                }
                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $jobRunTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        $jobProduction += $records[$i]->length - $oldLength;
                                    }
                                }
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }
                                if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500) {
                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => 501,
                                        "error_name" => 'Auto Error',
                                        "comments" => 'Auto Minor Stop by Roto-eye',
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                        $oldLength = $records[$i + 1]->length;
                                        $jobProduction = $production;
                                        if($jobRunTime == 0) {
                                            $jobPerformance = 0;
                                        } else {
                                            $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                        }
                                        foreach($data['records'] as $record) {
                                            array_push($record, [
                                                "jobProduction" => $jobProduction,
                                                "jobPerformance" => $jobPerformance,
                                                "jobRuntime" => $jobRunTime
                                            ]);
                                            $jobProduction = 0;
                                            $jobRunTime = 0;
                                        }
                                    } else {
                                        $oldLength = $records[$i]->length;
                                    }
                                } else {
                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "comments" => str_replace('#', 'no', $records[$i]->comments),
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                        $oldLength = $records[$i + 1]->length;
                                        if($jobRunTime == 0) {
                                            $jobPerformance = 0;
                                            $jobAverageSpeed = 0;
                                        } else {
                                            if($data['machine']->time_uom == 'Min') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = $jobProduction / $jobRunTime;
                                            } elseif($data['machine']->time_uom == 'Hr') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                            } elseif($data['machine']->time_uom == 'Sec') {
                                                $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                            }
                                        }
                                        for($j = 0; $j < count($data['records']); $j++) {
                                            array_push($data['records'][$j], [
                                                "jobProduction" => $jobProduction,
                                                "jobPerformance" => $jobPerformance,
                                                "jobRuntime" => $jobRunTime,
                                                "jobAverageSpeed" => $jobAverageSpeed
                                            ]);
                                        }
                                        $jobProduction = 0;
                                        $jobRunTime = 0;
                                    } else {
                                        $oldLength = $records[$i]->length;
                                    }
                                }
                            }
                        } else {
                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                            if($data['machine']->time_uom == 'Hr') {
                                if($duration == 0) {
                                    $instantSpeed = 0;
                                } else {
                                    $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                }
                            } elseif($data['machine']->time_uom == 'Min') {
                                if($duration == 0) {
                                    $instantSpeed = 0;
                                } else {
                                    $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                }
                            } else {
                                if($duration == 0) {
                                    $instantSpeed = 0;
                                } else {
                                    $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                }
                            }
                            foreach($runningCodes as $runningCode) {
                                if($runningCode->id == $records[$i]->error_id) {
                                    $runTime += $duration;
                                    $production += $records[$i]->length - $oldLength;
                                    $jobProduction += $records[$i]->length - $oldLength;
                                }
                            }
                            foreach($idleErrors as $idleError) {
                                if($idleError->id == $records[$i]->error_id) {
                                    $idleTime += $duration;
                                }
                            }
                            foreach($jobWaitingCodes as $jobWaitingCode) {
                                if($jobWaitingCode->id == $records[$i]->error_id) {
                                    $jobWaitTime += $duration;
                                }
                            }
                            if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500) {
                                if($records[$i]->length - $oldLength < 0) {
                                    array_push($data['negativeRecords'], [
                                        "startDate" => $startDate,
                                        "endDate" => $endDate,
                                        "machine_id" => $machine_id,
                                        "sap_code" => $data['machine']->sap_code,
                                        "machine_name" => $data['machine']->name
                                    ]);
                                    $this->resolveNegatives($startDate, $endDate, $machine_id);
                                }
                                array_push($data['records'], [
                                    "job_id" => $records[$i]->job_id,
                                    "job_name" => $records[$i]->job_name,
                                    "product_number" => $records[$i]->product_number,
                                    "material_combination" => $records[$i]->material_combination,
                                    "nominal_speed" => $records[$i]->nominal_speed,
                                    "user_id" => $records[$i]->user_id,
                                    "user_name" => $records[$i]->user_name,
                                    "job_length" => $records[$i]->job_length,
                                    "error_id" => 501,
                                    "error_name" => 'Reel Change Over',
                                    "comments" => 'Auto Minor Stop by Roto-eye',
                                    "length" => $records[$i]->length - $oldLength,
                                    "from" => $startDate,
                                    "to" => $endDate,
                                    "duration" => $duration,
                                    "instantSpeed" => $instantSpeed,
                                    "jobProduction" => $production,
                                    "process_name" => $records[$i]->process_name,
                                ]);
                                $startDate = $endDate;
                                $oldLength = $records[$i]->length;
                            } else {
                                if($records[$i]->length - $oldLength < 0) {
                                    array_push($data['negativeRecords'], [
                                        "startDate" => $startDate,
                                        "endDate" => $endDate,
                                        "machine_id" => $machine_id,
                                        "sap_code" => $data['machine']->sap_code,
                                        "machine_name" => $data['machine']->name
                                    ]);
                                    $this->resolveNegatives($startDate, $endDate, $machine_id);
                                }
                                array_push($data['records'], [
                                    "job_id" => $records[$i]->job_id,
                                    "job_name" => $records[$i]->job_name,
                                    "product_number" => $records[$i]->product_number,
                                    "material_combination" => $records[$i]->material_combination,
                                    "nominal_speed" => $records[$i]->nominal_speed,
                                    "user_name" => $records[$i]->user_name,
                                    "user_id" => $records[$i]->user_id,
                                    "job_length" => $records[$i]->job_length,
                                    "error_id" => $records[$i]->error_id,
                                    "error_name" => $records[$i]->error_name,
                                    "comments" => str_replace('#', 'no', $records[$i]->comments),
                                    "length" => $records[$i]->length - $oldLength,
                                    "from" => $startDate,
                                    "to" => $endDate,
                                    "duration" => $duration,
                                    "instantSpeed" => $instantSpeed,
                                    "jobProduction" => $production,
                                    "process_name" => $records[$i]->process_name,
                                ]);
                                $startDate = $endDate;
                                $oldLength = $records[$i]->length;
                            }
                        }
                    }
                    for($k = 0; $k < count($data['records']); $k++) {
                        if($jobRunTime == 0) {
                            $jobPerformance = 0;
                            $jobAverageSpeed = 0;
                        } else {
                            if($data['machine']->time_uom == 'Min') {
                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                $jobAverageSpeed = $jobProduction / $jobRunTime;
                            } elseif($data['machine']->time_uom == 'Hr') {
                                $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                            } elseif($data['machine']->time_uom == 'Sec') {
                                $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                            }
                        }
                        if(!isset($data['records'][$k][0]['jobProduction']) || !isset($data['records'][$k][0]['jobPerformance'])) {
                            array_push($data['records'][$k], [
                                "jobProduction" => $jobProduction,
                                "jobPerformance" => $jobPerformance,
                                "jobRuntime" => $jobRunTime,
                                "jobAverageSpeed" => $jobAverageSpeed
                            ]);
                        }
                    }
                    if($runTime > 0) {
                        if($data['machine']->time_uom == 'Hr') {
                            $actualSpeed = $production / $runTime * 60;
                        } elseif($data['machine']->time_uom == 'Min') {
                            $actualSpeed = $production / $runTime;
                        } else {
                            $actualSpeed = $production / $runTime / 60;
                        }
                    } else {
                        $actualSpeed = 0;
                    }
                }
                //echo '<pre>';
                //print_r($data['records']);
                if(Session::get('rights') == 0) {
                    $data['user'] = Users::find($loginRecord[0]->user_id);
                } else {
                    $data['user'] = Users::find(Session::get('user_name'));
                }
                $data['produced'] = $production;
                $data['oee'] = ($runTime / ($totalTime - $idleTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                $data['ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                $data['performance'] = ($actualSpeed / $data['machine']->max_speed) * 100;
                $data['availability_ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * 100;
                $data['availability'] = ($runTime / ($totalTime - $idleTime)) * 100;
                $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
                $data['run_time'] = $runTime;
                $data['budgetedTime'] = $totalTime - $idleTime;
                $data['shift'] = $request->input('shiftSelection');
                $data['date'] = $date;

                if(count($data['negativeRecords']) > 0) {

                    // Mail::send('emails.negative-meters', $data, function ($message) use ($data) {
                    //     $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                    //     $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                    //         ->cc('nauman.abid@packages.com.pk', 'M Nauman Abid')
                    //         ->cc('haroon.naseer@packages.com.pk', 'M Haroon Naseer')
                    //         ->subject("RotoEye Cloud - Negative Meters");
                    // });

                    Session::flash('success', 'We have resolved some negative meters during this production period. Please run the report again.');
                    $row['view'] = View::make('reports.shift-production-report-export-version1', $data)->render();
                    return view('roto.reports', $row);
                    //       return Redirect::back();
                } else {
                    //        return $data['records'];
                    $row['view'] = View::make('reports.shift-production-report-export-version', $data)->render();
                    return view('reports.shift-production-report-export-version1', $data);
                    //      return $data['records'];
                    //         return view('roto.reports', $row);
                }
            } else {
                Session::flash("error", "No Record for the selected shift and date. Please try again.");
                return Redirect::back();
            }
        }
    }
    public function productionReports(Request $request, $id) {

        $shiftSelection = $request->input('shiftSelection');
        $data["shft"] = $shiftSelection;
        $user_id = Session::get('user_id');
        if(isset($user_id)) {
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $machine_id = Crypt::decrypt($id);
            $date = $request->input('date');
            if(Session::get('rights') == 0) {
                $data['layout'] = 'web-layout';
            } elseif(Session::get('rights') == 1) {
                $data['layout'] = 'admin-layout';
            } elseif(Session::get('rights') == 2) {
                $data['layout'] = 'power-user-layout';
            } elseif(Session::get('rights') == 3) {
                $data['layout'] = 'reporting-user-layout';
            }
            $data['machine'] = Machine::find($machine_id);
            $data['record'] = Record::where('machine_id', '=', $machine_id)->latest('run_date_time')->first();

            //Shift Production Report (Shift Wise and Duration Wise)
            if($request->input('reportType') == 'shift-production-report') {
                $shiftSelection = $request->input('shiftSelection');
                $data['shiftSelection'] = $shiftSelection;
                if($shiftSelection[0] == 'All-Day') {
                    //haseeb 6/3/2021
                    $machine = Machine::find($machine_id);
                    $shifts_id = [];
                    foreach($machine->section->department->businessUnit->company->shifts as $shift) {
                        array_push($shifts_id, $shift->id);
                    }

                    $minStarted = Shift::find($shifts_id[0])->min_started;
                    $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;
                    //haseeb 6/3/2021

                    $from_date = $request->input('date');
                    $to_date = $request->input('to_date');

                    $data['from'] = $from_date;
                    $data['to'] = $to_date;

                    //haseeb 6/3/2021
                    $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));

                    //$startDateTime = date('Y-m-d H:i:s', strtotime($from_date.' + 390 minutes'));
                    //$endDateTime = date('Y-m-d H:i:s', strtotime($to_date.' + 1830 minutes'));
                    //haseeb 6/3/2021
                } else {

                    $minStarted = Shift::find($shiftSelection[0])->min_started;
                    $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
                }

                $data['machine'] = Machine::find($machine_id);
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

                if(date('Y-m-d H:i:s') < $endDateTime) {
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
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'process_structure.color as process_structure_color',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('machine_id', '=', $machine_id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();
                if(count($records) > 0) {
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

                    $totalTime = (strtotime($endDateTime) - strtotime($startDateTime)) / 60;
                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {
                            if(isset($records[$i + 1])) {
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    if($data['machine']->time_uom == 'Hr') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                        }
                                    } elseif($data['machine']->time_uom == 'Min') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                        }
                                    } else {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                        }
                                    }
                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $jobRunTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            $jobProduction += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                        }
                                    }


                                    if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500 && false) {
                                        if($records[$i]->length - $oldLength < 0) {
                                            array_push($data['negativeRecords'], [
                                                "startDate" => $startDate,
                                                "endDate" => $endDate,
                                                "machine_id" => $machine_id,
                                                "sap_code" => $data['machine']->sap_code,
                                                "machine_name" => $data['machine']->name
                                            ]);
                                            $this->resolveNegatives($startDate, $endDate, $machine_id);
                                        }

                                        array_push($data['records'], [
                                            "job_id" => $records[$i]->job_id,
                                            "job_name" => $records[$i]->job_name,
                                            "product_number" => $records[$i]->product_number,
                                            "material_combination" => $records[$i]->material_combination,
                                            "process_structure_color" => $records[$i]->process_structure_color,
                                            "nominal_speed" => $records[$i]->nominal_speed,
                                            "user_id" => $records[$i]->user_id,
                                            "user_name" => $records[$i]->user_name,
                                            "job_length" => $records[$i]->job_length,
                                            "error_id" => 501,
                                            "error_name" => 'Auto Error',
                                            "comments" => 'Auto Minor Stop by Roto-eye',

                                            "length" => $records[$i]->length - $oldLength,
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "duration" => $duration,
                                            "instantSpeed" => $instantSpeed,
                                            "process_name" => $records[$i]->process_name,
                                        ]);
                                        $startDate = $endDate;
                                        if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                            $oldLength = $records[$i + 1]->length;
                                            $jobProduction = $production;
                                            if($jobRunTime == 0) {
                                                $jobPerformance = 0;
                                            } else {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                            }
                                            foreach($data['records'] as $record) {
                                                array_push($record, [
                                                    "jobProduction" => $jobProduction,
                                                    "jobPerformance" => $jobPerformance,
                                                    "jobRuntime" => $jobRunTime
                                                ]);
                                                $jobProduction = 0;
                                                $jobRunTime = 0;
                                            }
                                        } else {
                                            $oldLength = $records[$i]->length;
                                        }
                                    } else {
                                        if($records[$i]->length - $oldLength < 0) {

                                            array_push($data['negativeRecords'], [
                                                "startDate" => $startDate,
                                                "endDate" => $endDate,
                                                "machine_id" => $machine_id,
                                                "sap_code" => $data['machine']->sap_code,
                                                "machine_name" => $data['machine']->name
                                            ]);


                                            $this->resolveNegatives($startDate, $endDate, $machine_id);
                                        }
                                        array_push($data['records'], [
                                            "job_id" => $records[$i]->job_id,
                                            "job_name" => $records[$i]->job_name,
                                            "product_number" => $records[$i]->product_number,
                                            "material_combination" => $records[$i]->material_combination,
                                            "process_structure_color" => $records[$i]->process_structure_color,
                                            "nominal_speed" => $records[$i]->nominal_speed,
                                            "user_id" => $records[$i]->user_id,
                                            "user_name" => $records[$i]->user_name,
                                            "job_length" => $records[$i]->job_length,
                                            "error_id" => $records[$i]->error_id,
                                            "error_name" => $records[$i]->error_name,
                                            "comments" => str_replace('#', 'no', $records[$i]->comments),
                                            "length" => $records[$i]->length - $oldLength,
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "duration" => $duration,
                                            "instantSpeed" => $instantSpeed,
                                            "process_name" => $records[$i]->process_name,
                                        ]);
                                        $startDate = $endDate;
                                        if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                            $oldLength = $records[$i + 1]->length;
                                            if($jobRunTime == 0) {
                                                $jobPerformance = 0;
                                                $jobAverageSpeed = 0;
                                            } else {
                                                if($data['machine']->time_uom == 'Min') {
                                                    $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                                    $jobAverageSpeed = $jobProduction / $jobRunTime;
                                                } elseif($data['machine']->time_uom == 'Hr') {
                                                    $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                                    $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                                } elseif($data['machine']->time_uom == 'Sec') {
                                                    $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                                    $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                                }
                                            }
                                            for($j = 0; $j < count($data['records']); $j++) {
                                                array_push($data['records'][$j], [
                                                    "jobProduction" => $jobProduction,
                                                    "jobPerformance" => $jobPerformance,
                                                    "jobRuntime" => $jobRunTime,
                                                    "jobAverageSpeed" => $jobAverageSpeed
                                                ]);
                                            }
                                            $jobProduction = 0;
                                            $jobRunTime = 0;
                                        } else {
                                            $oldLength = $records[$i]->length;
                                        }
                                    }
                                }
                            } else {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                if($data['machine']->time_uom == 'Hr') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                    }
                                } elseif($data['machine']->time_uom == 'Min') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                    }
                                } else {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                    }
                                }
                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        $jobProduction += $records[$i]->length - $oldLength;
                                    }
                                }
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }
                                if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500 && false) {
                                    if($records[$i]->length - $oldLength < 0) {

                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "process_structure_color" => $records[$i]->process_structure_color,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => 501,
                                        "error_name" => 'Reel Change Over',
                                        "comments" => 'Auto Minor Stop by Roto-eye',
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "jobProduction" => $production,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    $oldLength = $records[$i]->length;
                                } else {
                                    if($records[$i]->length - $oldLength < 0) {

                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "process_structure_color" => $records[$i]->process_structure_color,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_name" => $records[$i]->user_name,
                                        "user_id" => $records[$i]->user_id,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "comments" => str_replace('#', 'no', $records[$i]->comments),
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "jobProduction" => $production,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    $oldLength = $records[$i]->length;
                                }

                                if($request->input('shiftSelection')[0] != 'All-Day') {
                                    if(count($request->input('shiftSelection')) == 1) {
                                        Productivity::updateOrInsert(
                                            [
                                                'machine_id' => $data['machine']->id,
                                                'date' => date('Y-m-d', strtotime($date)),
                                                'shift_id' => $request->input('shiftSelection')[0]
                                            ],
                                            [
                                                'total_running_time' => round($runTime, 0),
                                                'total_production' => round($production, 0),
                                                'total_job_waiting_time' => round($jobWaitTime, 0),
                                                'total_idle_time' => round($idleTime, 0)
                                            ]
                                        );
                                    }
                                }
                            }
                        }
                        for($k = 0; $k < count($data['records']); $k++) {
                            if($jobRunTime == 0) {
                                $jobPerformance = 0;
                                $jobAverageSpeed = 0;
                            } else {
                                if($data['machine']->time_uom == 'Min') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = $jobProduction / $jobRunTime;
                                } elseif($data['machine']->time_uom == 'Hr') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                } elseif($data['machine']->time_uom == 'Sec') {
                                    $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                }
                            }
                            if(!isset($data['records'][$k][0]['jobProduction']) || !isset($data['records'][$k][0]['jobPerformance'])) {
                                array_push($data['records'][$k], [
                                    "jobProduction" => $jobProduction,
                                    "jobPerformance" => $jobPerformance,
                                    "jobRuntime" => $jobRunTime,
                                    "jobAverageSpeed" => $jobAverageSpeed
                                ]);
                            }
                        }
                        if($runTime > 0) {
                            if($data['machine']->time_uom == 'Hr') {
                                $actualSpeed = $production / $runTime * 60;
                            } elseif($data['machine']->time_uom == 'Min') {
                                $actualSpeed = $production / $runTime;
                            } else {
                                $actualSpeed = $production / $runTime / 60;
                            }
                        } else {
                            $actualSpeed = 0;
                        }
                    }
                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    //     dd($runTime,$totalTime,$idleTime,$actualSpeed);
                    $data['produced'] = $production;
                    $data['oee'] = ($runTime / ($totalTime - $idleTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['performance'] = ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['availability_ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * 100;
                    $data['availability'] = ($runTime / ($totalTime - $idleTime)) * 100;
                    $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
                    $data['run_time'] = $runTime;
                    $data['budgetedTime'] = $totalTime - $idleTime;
                    $data['shift'] = $request->input('shiftSelection');
                    $data['date'] = $date;
                    $data['current_time'] = date('Y-m-d H:i:s');

                    $data['startDateTime'] = $startDateTime;
                    $data['endDateTime'] = $endDateTime;


                    $existingRecords = ProductionReport::where(function ($query) use ($data) {
                        $query->where(function ($query) use ($data) {
                            $query->where('from_time', '>', $data['startDateTime']);
                            $query->where('from_time', '<', $data['endDateTime']);
                        });
                        $query->orwhere(function ($query) use ($data) {
                            $query->where('to_time', '>', $data['startDateTime']);
                            $query->where('to_time', '<', $data['endDateTime']);
                        });
                    })->where('machine_id', '=', $data['machine']->id)->get();


                    if(isset($existingRecords)) {
                        if(count($existingRecords) > 0) {
                            //if(count($existingRecords) < count($data['records'])){
                            foreach($existingRecords as $r) {
                                $r->delete();
                            }
                            // }
                        }
                    }

                    // if(count($existingRecords) < count($data['records'])){

                    foreach($data['records'] as $record) {

                        $reportdata = new ProductionReport();
                        $reportdata->job_no = $record['job_id'];
                        $reportdata->job_name = $record['job_name'];
                        $reportdata->machine_id = $data['machine']->id;
                        $reportdata->machine_no = $data['machine']->sap_code;
                        $reportdata->err_no = $record['error_id'];
                        $reportdata->err_name = $record['error_name'];
                        $reportdata->err_comments = $record['comments'];
                        $reportdata->from_time = $record['from'];
                        $reportdata->to_time = $record['to'];
                        $reportdata->duration = $record['duration'];
                        $reportdata->save();
                        //  }
                    }


                    //dd($data['negativeRecords']);

                    if(count($data['negativeRecords']) > 0) {

                        Mail::send('emails.negative-meters', $data, function ($message) use ($data) {
                            $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                            $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                                ->cc('haroon.naseer@packages.com.pk', 'M Haroon Naseer')
                                ->subject("RotoEye Cloud - Negative Meters");
                        });
                        //  return $data['negativeRecords'];
                        Session::flash('success', 'We have resolved some negative meters during this production period. Please run the report again.');
                        $row['view'] = View::make('reports.shift-production-report', $data)->render();

                        return Redirect::back();
                    } else {
                        //         return $data['records'];
                        $row['view'] = View::make('reports.shift-production-report', $data)->render();
                        return view('reports.shift-production-report', $data);
                        //    return $data['records'];
                        //     return view('roto.reports', $row);
                    }
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }


            } elseif($request->input('reportType') == 'shift-production-report-job') {

                $shiftSelection = $request->input('shiftSelection');
                $machine = Machine::find($machine_id);
                if($shiftSelection[0] == 'All-Day') {
                    $shifts_id = [];
                    foreach($machine->section->department->businessUnit->company->shifts as $shift) {
                        array_push($shifts_id, $shift->id);
                    }
                    $minStarted = Shift::find($shifts_id[0])->min_started;
                    $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;
                    $from_date = $request->input('date');
                    $to_date = $request->input('to_date');
                    $data['from'] = $from_date;
                    $data['to'] = $to_date;
                    $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));

                } else {
                    $minStarted = Shift::find($shiftSelection[0])->min_started;
                    $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;
                    $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
                }

                $data['machine'] = Machine::find($machine_id);
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();
                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

                if(date('Y-m-d H:i:s') < $endDateTime) {
                    $endDateTime = date('Y-m-d H:i:s');
                }

                $records = DB::table('records')
                    ->leftJoin('errors', 'errors.id', '=', 'records.error_id')
                    ->leftJoin('users', 'users.id', '=', 'records.user_id')
                    ->leftJoin('jobs', 'jobs.id', '=', 'records.job_id')
                    ->leftJoin('products', 'products.id', '=', 'jobs.product_id')
                    ->leftJoin('product_sleeve', 'product_sleeve.product_id', '=', 'products.id')
                    ->leftJoin('sleeves', 'sleeves.id', '=', 'product_sleeve.sleeve_id')
                    ->leftJoin('process_structure', function ($join) {
                        $join->on('process_structure.process_id', '=', 'records.process_id');
                        $join->on('process_structure.product_id', '=', 'products.id');
                    })
                    ->leftJoin('machine_sleeve', function ($join) {
                        $join->on('machine_sleeve.sleeve_id', '=', 'sleeves.id');
                        $join->on('machine_sleeve.machine_id', '=', 'records.machine_id');
                    })
                    ->leftJoin('material_combination', 'material_combination.id', '=', 'process_structure.material_combination_id')
                    ->leftJoin('processes', 'processes.id', '=', 'process_structure.process_id')
                    ->select(
                        'errors.name as error_name',
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'records.err_comments as comments',
                        'records.reelwidth as job_reelwidth',
                        'records.gsm as job_gsm',
                        'records.thickness as job_thickness',
                        'records.trim_width as job_trimwidth',
                        'records.density as job_density',
                        'records.ups as job_ups',
                        'products.name as product_name',
                        'products.id as product_number',
                        'products.ups as ups',
                        'products.col as col',
                        'products.slitted_reel_width as slitted_reel_width',
                        'products.trim_width as trim_width',
                        'products.gsm as gsm',
                        'products.thickness as thickness',
                        'products.density as density',
                        'machine_sleeve.speed as sleeve_speed',
                        'sleeves.circumference as sleeve_circumference',
                        'material_combination.name as material_combination',
                        'process_structure.color as process_structure_color',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('records.machine_id', '=', $machine_id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();

                //return $records;

                if(count($records) > 0) {
                    $data['records'] = [];
                    $data['negativeRecords'] = [];
                    $startDate = $records[0]->run_date_time;
                    $oldLength = $records[0]->length;
                    $runTime = 0;
                    $idleTime = 0;
                    $jobWaitTime = 0;
                    $production = 0;
                    $targetHour = 0;
                    $jobTargetHour = 0;
                    $productionArea = 0;
                    $jobProductionArea = 0;
                    $rotoeyeNextProduction = 0;
                    $gsm = 0;
                    $jobGsm = 0;
                    $ea = 0;
                    $jobEa = 0;
                    $jobProduction = 0;
                    $jobRunTime = 0;
                    $job_GSM = 0;

                    $totalTime = (strtotime($endDateTime) - strtotime($startDateTime)) / 60;
                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {

                            $running_speed = isset($records[$i]->sleeve_speed) ? $records[$i]->sleeve_speed : $data['machine']->max_speed;
                            if(isset($records[$i + 1])) {
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    if($data['machine']->time_uom == 'Hr') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                        }
                                    } elseif($data['machine']->time_uom == 'Min') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                        }
                                    } else {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                        }
                                    }
                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $jobRunTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            if($data['machine']->time_uom == 'Min') {
                                                $targetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                                $jobTargetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                            } elseif($data['machine']->time_uom == 'Hr') {
                                                $targetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;
                                                $jobTargetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;

                                            } elseif($data['machine']->time_uom == 'Sec') {
                                                $targetHour += ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                                $jobTargetHour += ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                            }
                                            if(isset($records[$i]->job_trimwidth) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($machine->machine_width)) {
                                                $rotoeyeNextProduction += $records[$i]->length - $oldLength;
                                                $productionArea += ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength);
                                                $jobProductionArea += ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength);
                                                if(isset($records[$i]->job_gsm)) {
                                                    $gsm += $records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength);
                                                    $jobGsm += $records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength);
                                                }

                                            }
                                            if(isset($records[$i]->col) && isset($records[$i]->job_ups) && ($records[$i]->job_ups > 0) && ($records[$i]->col > 0)) {
                                                $ea += ($records[$i]->length - $oldLength) / $records[$i]->col * $records[$i]->job_ups;
                                                $jobEa += ($records[$i]->length - $oldLength) / $records[$i]->col * $records[$i]->job_ups;
                                            }

                                            $jobProduction += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                        }
                                    }

                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }

                                    ////   dump($records[$i]);
                                    // dump(($records[$i]->length-$oldLength)/($records[$i]->col*100*$records[$i]->ups));


                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "product_ups" => $records[$i]->ups,
                                        "product_col" => $records[$i]->col,
                                        "product_slitted_reel_width" => $records[$i]->slitted_reel_width,
                                        "product_trim_width" => $records[$i]->trim_width,
                                        "product_gsm" => $records[$i]->gsm,
                                        "product_thickness" => $records[$i]->thickness,
                                        "product_density" => $records[$i]->density,
                                        "job_reelwidth" => $records[$i]->job_reelwidth,
                                        "job_gsm" => $records[$i]->job_gsm,
                                        "job_ups" => $records[$i]->job_ups,
                                        "job_thickness" => $records[$i]->job_thickness,
                                        "job_density" => $records[$i]->job_density,
                                        "job_trimwidth" => $records[$i]->job_trimwidth,
                                        "product_sleeve_speed" => $records[$i]->sleeve_speed,
                                        "product_sleeve_circumference" => $records[$i]->sleeve_circumference,
                                        "material_combination" => $records[$i]->material_combination,
                                        "process_structure_color" => $records[$i]->process_structure_color,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "comments" => str_replace('#', 'no', $records[$i]->comments),
                                        "length" => $records[$i]->length - $oldLength,
                                        "gsm" => (isset($records[$i]->job_gsm) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($records[$i]->job_trimwidth)) ?
                                            number_format($records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength), 1) : '-',
                                        "pope_production" => (isset($records[$i]->gsm) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($records[$i]->job_trimwidth)) ?
                                            $duration > 0 ? number_format((($records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength) * 60) / 1000) / $duration, 2) : 0 : '-',
                                        "ea" => isset($records[$i]->job_ups) && isset($records[$i]->col) && ($records[$i]->job_ups > 0) && ($records[$i]->col > 0) ? number_format(($records[$i]->length - $oldLength) / ($records[$i]->col * $records[$i]->job_ups), 2) : '-',
                                        "pope_production_kgs" => (isset($records[$i]->gsm) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($records[$i]->job_trimwidth)) ?
                                            $duration > 0 ? number_format((($records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength) * 60)) / $duration, 2) : 0 : '-',
                                        "ea" => isset($records[$i]->job_ups) && isset($records[$i]->col) && ($records[$i]->job_ups > 0) && ($records[$i]->col > 0) ? number_format(($records[$i]->length - $oldLength) / ($records[$i]->col * $records[$i]->job_ups), 2) : '-',
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "process_name" => $records[$i]->process_name,
                                    ]);

                                    $startDate = $endDate;
                                    if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                        $oldLength = $records[$i + 1]->length;
                                        if($jobRunTime == 0) {
                                            $jobPerformance = 0;
                                            $jobAverageSpeed = 0;
                                        } else {

                                            if($data['machine']->time_uom == 'Min') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $running_speed) * 100;
                                                $jobAverageSpeed = $jobProduction / $jobRunTime;
                                            } elseif($data['machine']->time_uom == 'Hr') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $running_speed) * 100;
                                                $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                            } elseif($data['machine']->time_uom == 'Sec') {
                                                $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $running_speed) * 100;
                                                $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                            }
                                        }

                                        for($j = 0; $j < count($data['records']); $j++) {
                                            $jobUtilization = 0;
                                            if(isset($records[$i]->job_trimwidth) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($machine->machine_width) && $jobProduction > 0) {
                                                $jobUtilization = $jobProductionArea / ($jobProduction * $machine->machine_width) * 100;
                                            }
                                            array_push($data['records'][$j], [
                                                "jobProduction" => $jobProduction,
                                                "jobTargetHour" => $jobTargetHour,
                                                "jobUtilization" => $jobUtilization,
                                                "jobGsm" => $jobGsm,
                                                "jobEa" => $jobEa,
                                                "jobPerformance" => $jobPerformance,
                                                "jobRuntime" => $jobRunTime,
                                                "jobAverageSpeed" => $jobAverageSpeed
                                            ]);
                                        }

                                        $jobProduction = 0;
                                        $jobRunTime = 0;
                                        $jobTargetHour = 0;
                                        $jobGsm = 0;
                                        $jobEa = 0;
                                        $jobProductionArea = 0;
                                    } else {
                                        $oldLength = $records[$i]->length;
                                    }

                                }

                            } else {

                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                if($data['machine']->time_uom == 'Hr') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                    }
                                } elseif($data['machine']->time_uom == 'Min') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                    }
                                } else {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                    }
                                }
                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        if($data['machine']->time_uom == 'Min') {
                                            $targetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                            $jobTargetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                        } elseif($data['machine']->time_uom == 'Hr') {
                                            $targetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;
                                            $jobTargetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;

                                        } elseif($data['machine']->time_uom == 'Sec') {
                                            $targetHour = ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                            $jobTargetHour = ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                        }

                                        if(isset($records[$i]->job_trimwidth) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($machine->machine_width)) {

                                            $rotoeyeNextProduction += $records[$i]->length - $oldLength;
                                            $productionArea += ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength);
                                            $jobProductionArea += ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength);
                                            if(isset($records[$i]->job_gsm)) {
                                                $gsm += $records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength);
                                                $jobGsm += $records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength);

                                            }

                                        }
                                        if(isset($records[$i]->col) && isset($records[$i]->job_ups) && ($records[$i]->job_ups > 0) && ($records[$i]->col > 0)) {
                                            $ea += ($records[$i]->length - $oldLength) * $records[$i]->col * $records[$i]->job_ups;
                                            $jobEa += ($records[$i]->length - $oldLength) * $records[$i]->col * $records[$i]->job_ups;
                                        }
                                        $jobProduction += $records[$i]->length - $oldLength;

                                    }
                                }
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }
                                if($records[$i]->length - $oldLength < 0) {
                                    array_push($data['negativeRecords'], [
                                        "startDate" => $startDate,
                                        "endDate" => $endDate,
                                        "machine_id" => $machine_id,
                                        "sap_code" => $data['machine']->sap_code,
                                        "machine_name" => $data['machine']->name
                                    ]);
                                    $this->resolveNegatives($startDate, $endDate, $machine_id);
                                }

                                array_push($data['records'], [
                                    "job_id" => $records[$i]->job_id,
                                    "job_name" => $records[$i]->job_name,
                                    "job_reelwidth" => $records[$i]->job_reelwidth,
                                    "job_gsm" => $records[$i]->job_gsm,
                                    "job_ups" => $records[$i]->job_ups,
                                    "job_thickness" => $records[$i]->job_thickness,
                                    "job_density" => $records[$i]->job_density,
                                    "job_trimwidth" => $records[$i]->job_trimwidth,
                                    "product_number" => $records[$i]->product_number,
                                    "product_ups" => $records[$i]->ups,
                                    "product_col" => $records[$i]->col,
                                    "product_slitted_reel_width" => $records[$i]->slitted_reel_width,
                                    "product_trim_width" => $records[$i]->trim_width,
                                    "product_gsm" => $records[$i]->gsm,
                                    "product_thickness" => $records[$i]->thickness,
                                    "product_density" => $records[$i]->density,
                                    "product_sleeve_speed" => $records[$i]->sleeve_speed,
                                    "product_sleeve_circumference" => $records[$i]->sleeve_circumference,
                                    "material_combination" => $records[$i]->material_combination,
                                    "process_structure_color" => $records[$i]->process_structure_color,
                                    "nominal_speed" => $records[$i]->nominal_speed,
                                    "user_id" => $records[$i]->user_id,
                                    "user_name" => $records[$i]->user_name,
                                    "job_length" => $records[$i]->job_length,
                                    "error_id" => $records[$i]->error_id,
                                    "error_name" => $records[$i]->error_name,
                                    "comments" => str_replace('#', 'no', $records[$i]->comments),
                                    "length" => $records[$i]->length - $oldLength,
                                    "gsm" => (isset($records[$i]->job_gsm) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($records[$i]->job_trimwidth)) ?
                                        number_format($records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength), 2) : '-',
                                    "ea" => isset($records[$i]->job_ups) && isset($records[$i]->col) && ($records[$i]->col > 0) && ($records[$i]->job_ups > 0) ? number_format(($records[$i]->length - $oldLength) / ($records[$i]->col * $records[$i]->job_ups), 2) : '-',
                                    "pope_production" => (isset($records[$i]->job_gsm) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($records[$i]->job_trimwidth)) ?
                                        $duration > 0 ? number_format((($records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength) * 60) / 1000) / $duration, 2) : 0 : '-',
                                    "pope_production_kgs" => (isset($records[$i]->gsm) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($records[$i]->job_trimwidth)) ?
                                        $duration > 0 ? number_format((($records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength) * 60)) / $duration, 2) : 0 : '-',
                                    "from" => $startDate,
                                    "to" => $endDate,
                                    "duration" => $duration,
                                    "instantSpeed" => $instantSpeed,
                                    "process_name" => $records[$i]->process_name,
                                ]);

                                if($jobRunTime == 0) {
                                    $jobPerformance = 0;
                                    $jobAverageSpeed = 0;
                                } else {

                                    if($data['machine']->time_uom == 'Min') {
                                        $jobPerformance = (($jobProduction / $jobRunTime) / $running_speed) * 100;
                                        $jobAverageSpeed = $jobProduction / $jobRunTime;
                                    } elseif($data['machine']->time_uom == 'Hr') {
                                        $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $running_speed) * 100;
                                        $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                    } elseif($data['machine']->time_uom == 'Sec') {
                                        $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $running_speed) * 100;
                                        $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                    }
                                }
                                for($j = 0; $j < count($data['records']); $j++) {
                                    $jobUtilization = 0;
                                    if(isset($records[$i]->job_trimwidth) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($machine->machine_width) && $jobProduction > 0) {
                                        $jobUtilization = $jobProductionArea / ($jobProduction * $machine->machine_width) * 100;
                                    }

                                    array_push($data['records'][$j], [
                                        "jobProduction" => $jobProduction,
                                        "jobTargetHour" => $jobTargetHour,
                                        "jobUtilization" => $jobUtilization,
                                        "jobGsm" => $jobGsm,
                                        "jobEa" => $jobEa,
                                        "jobPerformance" => $jobPerformance,
                                        "jobRuntime" => $jobRunTime,
                                        "jobAverageSpeed" => $jobAverageSpeed,

                                    ]);
                                }
                                $jobProduction = 0;
                                $jobRunTime = 0;
                                $jobProductionArea = 0;
                                $jobTargetHour = 0;
                                $jobGsm = 0;
                                $jobEa = 0;
                                $startDate = $endDate;
                                $oldLength = $records[$i]->length;

                            }
                        }
                    }
                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    $data['produced'] = $production;
                    $data['oee'] = ($runTime / ($totalTime - $idleTime)) * ($runTime > 0 ? ($targetHour / $runTime) : 0) * 100;
                    $data['ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($runTime > 0 ? ($targetHour / $runTime) : 0) * 100;
                    $data['performance'] = ($runTime > 0 ? ($targetHour / $runTime) : 0) * 100;
                    $data['availability_ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * 100;
                    $data['availability'] = ($runTime / ($totalTime - $idleTime)) * 100;
                    $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
                    $data['run_time'] = $runTime;
                    $data['targetHours'] = $targetHour;
                    $data['utilization'] = isset($data['machine']->machine_width) ? (($rotoeyeNextProduction * $data['machine']->machine_width) > 0 ? ($productionArea / ($rotoeyeNextProduction * $data['machine']->machine_width)) : 0) * 100 : 0;
                    $data['budgetedTime'] = $totalTime - $idleTime;
                    $data['shift'] = $request->input('shiftSelection');
                    $data['date'] = $date;
                    $data['current_time'] = date('Y-m-d H:i:s');
                    //return $data['records'];

                    if(count($data['negativeRecords']) > 0) {

                        //      Mail::send('emails.negative-meters', $data, function ($message) use ($data) {
                        //     $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                        //       $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                        //             ->cc('haroon.naseer@packages.com.pk', 'M Haroon Naseer')
                        //              ->subject("RotoEye Cloud - Negative Meters");
                        //           });

                        Session::flash('success', 'We have resolved some negative meters during this production period. Please run the report again.');
                        $row['view'] = View::make('reports.job-shift-production-report', $data)->render();
                        //return($data);
                        return Redirect::back();
                    } else {
                        $row['view'] = View::make('reports.shift-production-report-next', $data)->render();
                        //return($data);
                        return view('reports.job-shift-production-report', $data);
                    }
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            } elseif($request->input('reportType') == 'shift-production-report-quality') {
                $shiftSelection = $request->input('shiftSelection');
                $machine = Machine::find($machine_id);
                //    dump($shiftSelection[0]);
                if($shiftSelection[0] == 'All-Day') {
                    // $machine=Machine::find($machine_id);

                    $shifts_id = [];

                    // Updated by Abdullah 22-11-23 start

                    // foreach ($machine->section->department->businessUnit->company->shifts as $shift){
                    //     array_push($shifts_id,$shift->id);
                    // }
                    foreach($machine->section->department->businessUnit->shifts as $shift) {
                        array_push($shifts_id, $shift->id);
                    }

                    // Updated by Abdullah 22-11-23 end


                    $minStarted = Shift::find($shifts_id[0])->min_started;
                    $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;
                    $from_date = $request->input('date');
                    $to_date = $request->input('to_date');

                    $data['from'] = $from_date;
                    $data['to'] = $to_date;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));

                } else {

                    $minStarted = Shift::find($shiftSelection[0])->min_started;
                    $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;
                    $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));

                    // dd($minStarted, $minEnded);
                }

                if(date('Y-m-d H:i:s') < $endDateTime) {
                    $endDateTime = date('Y-m-d H:i:s');
                }

                $data['machine'] = Machine::find($machine_id);
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $runningCodes = Error::select('id')->where('category', '=', 'Running')->orWhere('category', '=', 'Waste')->get();
                $wasteCodes = Error::select('id')->where('category', '=', 'Waste')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

                $strategiclossesCodes = Error::select('id')->where('toe_category', '=', 'Strategic Losses/Supply Chain Losses')->get();
                $plannedlossesCodes = Error::select('id')->where('toe_category', '=', 'Planned Losses/Planning or Management Losses')->get();
                $operationallossesCodes = Error::select('id')->where('toe_category', '=', 'Operational Losses/Shop Floor Losses')->get();
                //dump($operationallossesCodes);


                // updated by Abdullah 22-11-23 start
                // $shifts = $data['machine']->section->department->businessUnit->company->shifts;
                $shifts = $data['machine']->section->department->businessUnit->shifts;
                // updated by Abdullah 22-11-23 end


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
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'process_structure.color as process_structure_color',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('machine_id', '=', $machine_id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();




                //  dump($endDateTime);
                //dump(count($records));
                if(count($records) > 0) {
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
                    $totalLength = 0;

                    $totalStrategiclosses = 0;
                    $totalPlannedlosses = 0;
                    $totalOperationallosses = 0;

                    $totalTime = (strtotime($endDateTime) - strtotime($startDateTime)) / 60;
                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {
                            $rntime = 0;
                            $idltime = 0;
                            $jbtime = 0;
                            $pd = 0;
                            $stloss = 0;
                            $plloss = 0;
                            $oploss = 0;

                            if(isset($records[$i + 1])) {
                                //if($records[$i]->error_id != $records[$i+1]->error_id || $records[$i]->user_id != $records[$i+1]->user_id || $records[$i]->job_id != $records[$i+1]->job_id || ($records[$i]->job_id == $records[$i+1]->job_id  && $records[$i]->shift_number==3  ) ){
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    $totalLength += $records[$i]->length - $oldLength;

                                    if($data['machine']->time_uom == 'Hr') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                        }
                                    } elseif($data['machine']->time_uom == 'Min') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                        }
                                    } else {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                        }
                                    }

                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $rntime += $duration;
                                            $jobRunTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            $pd += $records[$i]->length - $oldLength;
                                            $jobProduction += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    //Waste Codes Added
                                    foreach($wasteCodes as $wasteCode) {
                                        if($wasteCode->id == $records[$i]->error_id) {
                                            $waste += $records[$i]->length - $oldLength;
                                            $jobWaste += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                            $idltime = $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                            $jbtime = $duration;
                                        }
                                    }

                                    if($records[$i]->length - $oldLength < 0) {
                                        //
                                    }

                                    foreach($strategiclossesCodes as $strategiclossesCode) {
                                        if($strategiclossesCode->id == $records[$i]->error_id) {
                                            $stloss += $duration;
                                        }
                                    }
                                    foreach($plannedlossesCodes as $plannedlossesCode) {
                                        if($plannedlossesCode->id == $records[$i]->error_id) {
                                            $plloss += $duration;
                                        }
                                    }
                                    foreach($operationallossesCodes as $operationallossesCode) {
                                        if($operationallossesCode->id == $records[$i]->error_id) {
                                            $oploss += $duration;
                                        }
                                    }
                                    // dd($shifts);
                                    //dump(Shift::which_shift($startDate,$shifts));
                                    $shiftss = Shift::find_shift($data['machine'], $startDate, $endDate);
                                    $shiftNumber = Shift::which_shift($startDate, $shifts);
                                    //  dump($shiftNumber->date);
                                    array_push($data['records'], [
                                        "shiftid" => $shiftNumber->id,
                                        "shiftNumber" => $shiftNumber->shift_number,
                                        'shifts' => $shiftss,
                                        'shiftsdate' => $shiftNumber->date,
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "process_structure_color" => $records[$i]->process_structure_color,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "comments" => str_replace('#', 'no', $records[$i]->comments),
                                        "length" => $pd,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "process_name" => $records[$i]->process_name,
                                        "run_time" => $rntime,
                                        "idleTime" => $idltime,
                                        "jobWaitingTime" => $jbtime,
                                        "totalStrategiclosses" => $stloss,
                                        "totalPlannedlosses" => $plloss,
                                        "totalOperationallosses" => $oploss,

                                    ]);

                                    $startDate = $endDate;
                                    if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                        $oldLength = $records[$i + 1]->length;
                                        if($jobRunTime == 0) {
                                            $jobPerformance = 0;
                                            $jobAverageSpeed = 0;
                                        } else {
                                            if($data['machine']->time_uom == 'Min') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = $jobProduction / $jobRunTime;
                                            } elseif($data['machine']->time_uom == 'Hr') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                            } elseif($data['machine']->time_uom == 'Sec') {
                                                $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                            }
                                        }
                                        for($j = 0; $j < count($data['records']); $j++) {
                                            array_push($data['records'][$j], [
                                                "jobProduction" => $jobProduction,
                                                "jobPerformance" => $jobPerformance,
                                                "jobRuntime" => $jobRunTime,
                                                "jobAverageSpeed" => $jobAverageSpeed,
                                                "jobWaste" => $jobWaste
                                            ]);
                                        }
                                        $jobProduction = 0;
                                        $jobRunTime = 0;
                                        $jobWaste = 0;
                                    } else {
                                        $oldLength = $records[$i]->length;
                                    }
                                }
                            } else {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                $totalLength += $records[$i]->length - $oldLength;

                                if($data['machine']->time_uom == 'Hr') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                    }
                                } elseif($data['machine']->time_uom == 'Min') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                    }
                                } else {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                    }
                                }

                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $rntime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        $pd += $records[$i]->length - $oldLength;
                                        $jobProduction += $records[$i]->length - $oldLength;
                                    }
                                }

                                foreach($wasteCodes as $wasteCode) {
                                    if($wasteCode->id == $records[$i]->error_id) {
                                        $waste += $records[$i]->length - $oldLength;
                                        $jobWaste += $records[$i]->length - $oldLength;
                                    }
                                }
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                        $idltime = $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                        $jbtime = $duration;
                                    }
                                }
                                if($records[$i]->length - $oldLength < 0) {
                                    //
                                }
                                foreach($strategiclossesCodes as $strategiclossesCode) {
                                    if($strategiclossesCode->id == $records[$i]->error_id) {
                                        $stloss += $duration;
                                    }
                                }
                                foreach($plannedlossesCodes as $plannedlossesCode) {
                                    if($plannedlossesCode->id == $records[$i]->error_id) {
                                        $plloss += $duration;
                                    }
                                }
                                foreach($operationallossesCodes as $operationallossesCode) {
                                    if($operationallossesCode->id == $records[$i]->error_id) {
                                        $oploss += $duration;
                                    }
                                }


                                $shiftNumber = Shift::which_shift($startDate, $shifts);
                                $shiftss = Shift::find_shift($data['machine'], $startDate, $endDate);
                                array_push($data['records'], [
                                    "shiftid" => $shiftNumber->id,
                                    "shiftNumber" => $shiftNumber->shift_number,
                                    "shiftss" => $shiftss,
                                    'shiftsdate' => $shiftNumber->date,
                                    "job_id" => $records[$i]->job_id,
                                    "job_name" => $records[$i]->job_name,
                                    "product_number" => $records[$i]->product_number,
                                    "material_combination" => $records[$i]->material_combination,
                                    "process_structure_color" => $records[$i]->process_structure_color,
                                    "nominal_speed" => $records[$i]->nominal_speed,
                                    "user_name" => $records[$i]->user_name,
                                    "user_id" => $records[$i]->user_id,
                                    "job_length" => $records[$i]->job_length,
                                    "error_id" => $records[$i]->error_id,
                                    "error_name" => $records[$i]->error_name,
                                    "comments" => str_replace('#', 'no', $records[$i]->comments),
                                    "length" => $pd,
                                    "from" => $startDate,
                                    "to" => $endDate,
                                    "duration" => $duration,
                                    "instantSpeed" => $instantSpeed,
                                    "jobProduction" => $production,
                                    "process_name" => $records[$i]->process_name,
                                    "run_time" => $rntime,
                                    "idleTime" => $idltime,
                                    "jobWaitingTime" => $jbtime,
                                    "totalStrategiclosses" => $stloss,
                                    "totalPlannedlosses" => $plloss,
                                    "totalOperationallosses" => $oploss,
                                ]);
                                $startDate = $endDate;
                                $oldLength = $records[$i]->length;
                            }
                        }

                        // dd($data['records']);
                        for($k = 0; $k < count($data['records']); $k++) {
                            if($jobRunTime == 0) {
                                $jobPerformance = 0;
                                $jobAverageSpeed = 0;
                            } else {
                                if($data['machine']->time_uom == 'Min') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = $jobProduction / $jobRunTime;
                                } elseif($data['machine']->time_uom == 'Hr') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                } elseif($data['machine']->time_uom == 'Sec') {
                                    $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                }
                            }
                            if(!isset($data['records'][$k][0]['jobProduction']) || !isset($data['records'][$k][0]['jobPerformance'])) {
                                array_push($data['records'][$k], [
                                    "jobProduction" => $jobProduction,
                                    "jobPerformance" => $jobPerformance,
                                    "jobRuntime" => $jobRunTime,
                                    "jobAverageSpeed" => $jobAverageSpeed,
                                    "jobWaste" => $jobWaste
                                ]);
                            }
                        }

                        if($runTime > 0) {
                            if($data['machine']->time_uom == 'Hr') {
                                $actualSpeed = $production / $runTime * 60;
                            } elseif($data['machine']->time_uom == 'Min') {
                                $actualSpeed = $production / $runTime;
                            } else {
                                $actualSpeed = $production / $runTime / 60;
                            }
                        } else {
                            $actualSpeed = 0;
                        }
                    }
                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }

                    $data['produced'] = $production;
                    $data['waste'] = $waste;
                    $data['quality'] = ($production > 0) ? 100 - (($waste / $production) * 100) : 0;

                    $data['oee'] = ($production > 0) ? ($runTime / ($totalTime - $idleTime)) * ($actualSpeed / $data['machine']->max_speed) * ((100 - (($waste / $production) * 100)) / 100) * 100 : 0;
                    $data['ee'] = ($production > 0) ? ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($actualSpeed / $data['machine']->max_speed) * ((100 - (($waste / $production) * 100)) / 100) * 100 : 0;
                    $data['performance'] = ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['availability_ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * 100;
                    $data['availability'] = ($runTime / ($totalTime - $idleTime)) * 100;
                    $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
                    $data['run_time'] = $runTime;
                    $data['budgetedTime'] = $totalTime - $idleTime;
                    $data['totalLength'] = $totalLength;
                    $data['shift'] = $request->input('shiftSelection');
                    $data['date'] = $date;
                    $data['current_time'] = date('Y-m-d H:i:s');
                    //dd($data);
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
                    })->where('machine_id', '=', $data['machine']->id)->get();


                    if(isset($existingRecords)) {
                        if(count($existingRecords) > 0) {
                            //if(count($existingRecords) < count($data['records'])){
                            foreach($existingRecords as $r) {
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
                    $formattedDate = $data['startDateTime']; //date("Y-m-d", $timestamp);
                    $machinLastshifts = $machine->section->department->businessUnit->company->shifts[2];




                    $rcords = $data['records'];
                    $chk = [1, 2, 3, 4];
                    for($f = 0; $f < count($rcords); $f++) {
                        if(isset($rcords[$f + 1])) {
                            $endd = date("Y-m-d H:i:s", strtotime($endDateTime.' -1 day'));
                            //  dump($endd);

                            $exp = explode(' ', $endd);
                            $pshday = date("Y-m-d", strtotime($rcords[$f]['shiftsdate']));

                            $nshday = date("Y-m-d", strtotime($rcords[$f + 1]['shiftsdate']));

                            $pshdayexp = explode(' ', $nshday);
                            $newendd = $pshdayexp[0].' '.$exp[1];
                            if($rcords[$f]['job_id'] == $rcords[$f + 1]['job_id'] && $pshday != $nshday) {
                                // dump($newendd);
                                $data['records'][$f]['insterted'] = 'inserted';

                                $this->splittingRecords($rcords[$f], $rcords[$f + 1], $newendd, $data['machine']);


                                //     foreach($chk as $chk){
                                //     if($rcords[$f+1+$chk]['job_id'] == $rcords[$f]['job_id']){

                                //     }
                                //   }

                            }
                        }

                    }

                    // dd("the end");

                    // dump($data['records']);

                    foreach($data['records'] as $record) {
                        // dd($machine->section);
                        if(isset($record['insterted'])) {
                            continue;
                        }
                        $reportdata = new GroupProductionReport();
                        $reportdata->job_no = $record['job_id'];
                        $reportdata->job_name = $record['job_name'];
                        $reportdata->machine_id = $data['machine']->id;
                        $reportdata->machine_no = $data['machine']->sap_code;
                        $reportdata->err_no = $record['error_id'];
                        $reportdata->err_name = $record['error_name'];
                        $reportdata->err_comments = $record['comments'];
                        $reportdata->from_time = $record['from'];
                        $reportdata->to_time = $record['to'];
                        $reportdata->duration = $record['duration'];
                        $reportdata->length = $record['length'];
                        $reportdata->date = $record['from'];
                        $reportdata->company_id = $machine->section->department->businessUnit->company->id;
                        $reportdata->company_name = $machine->section->department->businessUnit->company->name;
                        $reportdata->company_id = $machine->section->department->businessUnit->company->id;
                        $reportdata->business_unit_id = $machine->section->department->businessUnit->id;
                        $reportdata->business_unit_name = $machine->section->department->businessUnit->business_unit_name;
                        $reportdata->department_id = $machine->section->department->id;
                        $reportdata->department_name = $machine->section->department->name;
                        $reportdata->section_id = $machine->section->id;
                        $reportdata->section_name = $machine->section->name;
                        $reportdata->month = $monthZeroPadded;
                        $reportdata->operator_id = $record['user_id'];
                        $reportdata->operator_name = $record['user_name'];
                        $reportdata->product_number = $record['product_number'];
                        $reportdata->material_combination = $record['material_combination'];
                        $reportdata->nominal_speed = $record['nominal_speed'];
                        $reportdata->run_time = $record['run_time'];
                        $reportdata->idleTime = $record['idleTime'];
                        $reportdata->job_wating_time = $record['jobWaitingTime'];
                        $reportdata->totalStrategiclosses = $record['totalStrategiclosses'];
                        $reportdata->totalPlannedlosses = $record['totalPlannedlosses'];
                        $reportdata->totalOperationallosses = $record['totalOperationallosses'];
                        $reportdata->save();
                        //  }
                    }


                    if(count($data['negativeRecords']) > 0) {
                        Session::flash('success', 'We have resolved some negative meters during this production period. Please run the report again.');
                        $row['view'] = View::make('reports.shift-production-report-quality', $data)->render();
                        return Redirect::back();
                    } else {
                        // dump("reached");
                        $row['view'] = View::make('reports.shift-production-report-quality', $data)->render();
                        return view('reports.shift-production-report-quality', $data);
                    }
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }

            ////// mine
            elseif($request->input('reportType') == 'shift-production-report-quality-toe') {
                // dump($request->all());
                $shiftSelection = $request->input('shiftSelection');
                $machine = Machine::find($machine_id);
                // dump($shiftSelection[0]);
                if($shiftSelection[0] == 'All-Day') {
                    // $machine=Machine::find($machine_id);

                    $shifts_id = [];
                    //    Updated by Abdullah 22-11-23 start

                    // foreach ($machine->section->department->businessUnit->company->shifts as $shift){
                    //     array_push($shifts_id,$shift->id);
                    // }

                    foreach($machine->section->department->businessUnit->shifts as $shift) {
                        array_push($shifts_id, $shift->id);
                    }
                    //    Updated by Abdullah 22-11-23 end

                    $minStarted = Shift::find($shifts_id[0])->min_started;
                    $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;
                    $from_date = $request->input('date');
                    $to_date = $request->input('to_date');

                    $data['from'] = $from_date;
                    $data['to'] = $to_date;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));

                } else {
                    $minStarted = Shift::find($shiftSelection[0])->min_started;
                    $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;
                    $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));

                    // dd($minStarted, $minEnded);
                }

                if(date('Y-m-d H:i:s') < $endDateTime) {
                    $endDateTime = date('Y-m-d H:i:s');
                }

                $data['machine'] = Machine::find($machine_id);
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $runningCodes = Error::select('id')->where('toe_category', '=', 'Running')->orWhere('category', '=', 'Waste')->get();
                $wasteCodes = Error::select('id')->where('category', '=', 'Waste')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();


                $strategiclossesCodes = Error::select('id')->where('toe_category', '=', 'Strategic Losses/Supply Chain Losses')->get();
                $plannedlossesCodes = Error::select('id')->where('toe_category', '=', 'Planned Losses/Planning or Management Losses')->get();
                $operationallossesCodes = Error::select('id')->where('toe_category', '=', 'Operational Losses/Shop Floor Losses')->get();
                //dump($operationallossesCodes);


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
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'process_structure.color as process_structure_color',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('machine_id', '=', $machine_id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();

                if(count($records) > 0) {
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
                    $totalLength = 0;
                    $totalStrategiclosses = 0;
                    $totalPlannedlosses = 0;
                    $totalOperationallosses = 0;

                    $totalTime = (strtotime($endDateTime) - strtotime($startDateTime)) / 60;
                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {
                            if(isset($records[$i + 1])) {
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    $totalLength += $records[$i]->length - $oldLength;

                                    if($data['machine']->time_uom == 'Hr') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                        }
                                    } elseif($data['machine']->time_uom == 'Min') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                        }
                                    } else {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                        }
                                    }

                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $jobRunTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            $jobProduction += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    //Waste Codes Added
                                    foreach($wasteCodes as $wasteCode) {
                                        if($wasteCode->id == $records[$i]->error_id) {
                                            $waste += $records[$i]->length - $oldLength;
                                            $jobWaste += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                        }
                                    }

                                    foreach($strategiclossesCodes as $strategiclossesCode) {
                                        if($strategiclossesCode->id == $records[$i]->error_id) {
                                            $totalStrategiclosses += $duration;
                                        }
                                    }
                                    foreach($plannedlossesCodes as $plannedlossesCode) {
                                        if($plannedlossesCode->id == $records[$i]->error_id) {
                                            $totalPlannedlosses += $duration;
                                        }
                                    }
                                    foreach($operationallossesCodes as $operationallossesCode) {
                                        if($operationallossesCode->id == $records[$i]->error_id) {
                                            $totalOperationallosses += $duration;
                                        }
                                    }




                                    if($records[$i]->length - $oldLength < 0) {
                                        //
                                    }

                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "process_structure_color" => $records[$i]->process_structure_color,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "comments" => str_replace('#', 'no', $records[$i]->comments),
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "process_name" => $records[$i]->process_name,
                                        "run_time" => $runTime,
                                        "idleTime" => $idleTime,
                                        "jobWaitingTime" => $jobWaitTime
                                    ]);

                                    $startDate = $endDate;
                                    if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                        $oldLength = $records[$i + 1]->length;
                                        if($jobRunTime == 0) {
                                            $jobPerformance = 0;
                                            $jobAverageSpeed = 0;
                                        } else {
                                            if($data['machine']->time_uom == 'Min') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = $jobProduction / $jobRunTime;
                                            } elseif($data['machine']->time_uom == 'Hr') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                            } elseif($data['machine']->time_uom == 'Sec') {
                                                $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                            }
                                        }
                                        for($j = 0; $j < count($data['records']); $j++) {
                                            array_push($data['records'][$j], [
                                                "jobProduction" => $jobProduction,
                                                "jobPerformance" => $jobPerformance,
                                                "jobRuntime" => $jobRunTime,
                                                "jobAverageSpeed" => $jobAverageSpeed,
                                                "jobWaste" => $jobWaste
                                            ]);
                                        }
                                        $jobProduction = 0;
                                        $jobRunTime = 0;
                                        $jobWaste = 0;
                                    } else {
                                        $oldLength = $records[$i]->length;
                                    }
                                }
                            } else {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                $totalLength += $records[$i]->length - $oldLength;

                                if($data['machine']->time_uom == 'Hr') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                    }
                                } elseif($data['machine']->time_uom == 'Min') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                    }
                                } else {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                    }
                                }

                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        $jobProduction += $records[$i]->length - $oldLength;
                                    }
                                }

                                foreach($wasteCodes as $wasteCode) {
                                    if($wasteCode->id == $records[$i]->error_id) {
                                        $waste += $records[$i]->length - $oldLength;
                                        $jobWaste += $records[$i]->length - $oldLength;
                                    }
                                }
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }


                                foreach($strategiclossesCodes as $strategiclossesCode) {
                                    if($strategiclossesCode->id == $records[$i]->error_id) {
                                        $totalStrategiclosses += $duration;
                                    }
                                }
                                foreach($plannedlossesCodes as $plannedlossesCode) {
                                    if($plannedlossesCode->id == $records[$i]->error_id) {
                                        $totalPlannedlosses += $duration;
                                    }
                                }
                                foreach($operationallossesCodes as $operationallossesCode) {
                                    if($operationallossesCode->id == $records[$i]->error_id) {
                                        $totalOperationallosses += $duration;
                                    }
                                }
                                if($records[$i]->length - $oldLength < 0) {
                                    //
                                }
                                array_push($data['records'], [
                                    "job_id" => $records[$i]->job_id,
                                    "job_name" => $records[$i]->job_name,
                                    "product_number" => $records[$i]->product_number,
                                    "material_combination" => $records[$i]->material_combination,
                                    "process_structure_color" => $records[$i]->process_structure_color,
                                    "nominal_speed" => $records[$i]->nominal_speed,
                                    "user_name" => $records[$i]->user_name,
                                    "user_id" => $records[$i]->user_id,
                                    "job_length" => $records[$i]->job_length,
                                    "error_id" => $records[$i]->error_id,
                                    "error_name" => $records[$i]->error_name,
                                    "comments" => str_replace('#', 'no', $records[$i]->comments),
                                    "length" => $records[$i]->length - $oldLength,
                                    "from" => $startDate,
                                    "to" => $endDate,
                                    "duration" => $duration,
                                    "instantSpeed" => $instantSpeed,
                                    "jobProduction" => $production,
                                    "process_name" => $records[$i]->process_name,
                                    "run_time" => $runTime,
                                    "idleTime" => $idleTime,
                                    "jobWaitingTime" => $jobWaitTime
                                ]);
                                $startDate = $endDate;
                                $oldLength = $records[$i]->length;
                            }
                        }

                        // dd($data['records']);
                        for($k = 0; $k < count($data['records']); $k++) {
                            if($jobRunTime == 0) {
                                $jobPerformance = 0;
                                $jobAverageSpeed = 0;
                            } else {
                                if($data['machine']->time_uom == 'Min') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = $jobProduction / $jobRunTime;
                                } elseif($data['machine']->time_uom == 'Hr') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                } elseif($data['machine']->time_uom == 'Sec') {
                                    $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                }
                            }
                            if(!isset($data['records'][$k][0]['jobProduction']) || !isset($data['records'][$k][0]['jobPerformance'])) {
                                array_push($data['records'][$k], [
                                    "jobProduction" => $jobProduction,
                                    "jobPerformance" => $jobPerformance,
                                    "jobRuntime" => $jobRunTime,
                                    "jobAverageSpeed" => $jobAverageSpeed,
                                    "jobWaste" => $jobWaste
                                ]);
                            }
                        }

                        if($runTime > 0) {
                            if($data['machine']->time_uom == 'Hr') {
                                $actualSpeed = $production / $runTime * 60;
                            } elseif($data['machine']->time_uom == 'Min') {
                                $actualSpeed = $production / $runTime;
                            } else {
                                $actualSpeed = $production / $runTime / 60;
                            }
                        } else {
                            $actualSpeed = 0;
                        }
                    }
                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }

                    $data['totalStrategiclosses'] = $totalStrategiclosses;
                    $data['totalPlannedlosses'] = $totalPlannedlosses;
                    $data['totalOperationallosses'] = $totalOperationallosses;
                    $data['produced'] = $production;
                    $data['waste'] = $waste;
                    $data['quality'] = ($production > 0) ? (100 - (($waste / $production) * 100)) : 100;
                    //$data['oee'] = ($runTime/($totalTime - $idleTime))*($actualSpeed/$data['machine']->max_speed)*((100-(($waste/$production)*100))/100)*100;
                    $data['oee'] = ($runTime / ($totalTime - $totalStrategiclosses)) * ($actualSpeed / $data['machine']->max_speed) * ($data['quality'] / 100) * 100;
                    //$data['ee'] = ($runTime/($totalTime - $idleTime - $jobWaitTime))*($actualSpeed/$data['machine']->max_speed)*((100-(($waste/$production)*100))/100)*100;
                    $data['ee'] = ($runTime / ($totalTime - $totalStrategiclosses - $totalPlannedlosses)) * ($actualSpeed / $data['machine']->max_speed) * ($data['quality'] / 100) * 100;
                    $data['tee'] = ($runTime / ($totalTime)) * ($actualSpeed / $data['machine']->max_speed) * ($data['quality'] / 100) * 100;

                    $data['performance'] = ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['availability_ee'] = ($runTime / ($totalTime - $totalStrategiclosses - $totalPlannedlosses)) * 100;
                    $data['availability_for_oee'] = ($runTime / ($totalTime - $totalStrategiclosses)) * 100;
                    $data['availability_for_ee'] = ($runTime / ($totalTime - $totalStrategiclosses - $totalPlannedlosses)) * 100;
                    $data['availability_for_tee'] = ($runTime / ($totalTime)) * 100;


                    $data['totalDowntime'] = ($totalTime - $totalStrategiclosses) - $runTime;
                    $data['run_time'] = $runTime;
                    $data['budgetedTime'] = $totalTime - $totalStrategiclosses;
                    $data['totalLength'] = $totalLength;
                    $data['shift'] = $request->input('shiftSelection');
                    $data['date'] = $date;
                    $data['current_time'] = date('Y-m-d H:i:s');

                    // Updated by Abdullah 20-11-23 start
                    $data['budgetedTime_for_ee'] = $totalTime - $totalStrategiclosses - $totalPlannedlosses;
                    $data['budgetedTime_for_tee'] = $totalTime;
                    // Updated by Abdullah 20-11-23 end
                    // dump($data);

                    if(count($data['negativeRecords']) > 0) {
                        Session::flash('success', 'We have resolved some negative meters during this production period. Please run the report again.');
                        $row['view'] = View::make('reports.shift-production-report-quality-toe', $data)->render();
                        return Redirect::back();
                    } else {
                        $row['view'] = View::make('reports.shift-production-report-quality-toe', $data)->render();
                        return view('reports.shift-production-report-quality-toe', $data);
                    }
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }
            /// mine
            elseif($request->input('reportType') == 'shift-production-report-raw') {
                $shiftSelection = $request->input('shiftSelection');
                if($shiftSelection[0] == 'All-Day') {
                    $from_date = $request->input('date');
                    $to_date = $request->input('to_date');

                    $data['from'] = $from_date;
                    $data['to'] = $to_date;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($from_date.' + 390 minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($to_date.' + 1830 minutes'));
                } else {

                    $minStarted = Shift::find($shiftSelection[0])->min_started;
                    $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
                }

                $data['machine'] = Machine::find($machine_id);
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

                if(date('Y-m-d H:i:s') < $endDateTime) {
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
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('machine_id', '=', $machine_id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();
                if(count($records) > 0) {
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

                    $totalTimeDifference = date_diff(date_create($startDateTime), date_create($endDateTime));
                    $totalTime = (($totalTimeDifference->y * 365 + $totalTimeDifference->m * 30 + $totalTimeDifference->d) * 24 + $totalTimeDifference->h) * 60 + $totalTimeDifference->i + $totalTimeDifference->s / 60;
                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {
                            if(isset($records[$i + 1])) {
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    if($data['machine']->time_uom == 'Hr') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                        }
                                    } elseif($data['machine']->time_uom == 'Min') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                        }
                                    } else {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                        }
                                    }
                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $jobRunTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            $jobProduction += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                        }
                                    }
                                    if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500) {
                                        if($records[$i]->length - $oldLength < 0) {
                                            array_push($data['negativeRecords'], [
                                                "startDate" => $startDate,
                                                "endDate" => $endDate,
                                                "machine_id" => $machine_id,
                                                "sap_code" => $data['machine']->sap_code,
                                                "machine_name" => $data['machine']->name
                                            ]);
                                            $this->resolveNegatives($startDate, $endDate, $machine_id);
                                        }
                                        array_push($data['records'], [
                                            "job_id" => $records[$i]->job_id,
                                            "job_name" => $records[$i]->job_name,
                                            "product_number" => $records[$i]->product_number,
                                            "material_combination" => $records[$i]->material_combination,
                                            "nominal_speed" => $records[$i]->nominal_speed,
                                            "user_id" => $records[$i]->user_id,
                                            "user_name" => $records[$i]->user_name,
                                            "job_length" => $records[$i]->job_length,
                                            "error_id" => 501,
                                            "error_name" => 'Auto Error',
                                            "comments" => 'Auto Minor Stop by Roto-eye',
                                            "length" => $records[$i]->length - $oldLength,
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "duration" => $duration,
                                            "instantSpeed" => $instantSpeed,
                                            "process_name" => $records[$i]->process_name,
                                        ]);
                                        $startDate = $endDate;
                                        if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                            $oldLength = $records[$i + 1]->length;
                                            $jobProduction = $production;
                                            if($jobRunTime == 0) {
                                                $jobPerformance = 0;
                                            } else {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                            }
                                            foreach($data['records'] as $record) {
                                                array_push($record, [
                                                    "jobProduction" => $jobProduction,
                                                    "jobPerformance" => $jobPerformance,
                                                    "jobRuntime" => $jobRunTime
                                                ]);
                                                $jobProduction = 0;
                                                $jobRunTime = 0;
                                            }
                                        } else {
                                            $oldLength = $records[$i]->length;
                                        }
                                    } else {
                                        if($records[$i]->length - $oldLength < 0) {
                                            array_push($data['negativeRecords'], [
                                                "startDate" => $startDate,
                                                "endDate" => $endDate,
                                                "machine_id" => $machine_id,
                                                "sap_code" => $data['machine']->sap_code,
                                                "machine_name" => $data['machine']->name
                                            ]);
                                            $this->resolveNegatives($startDate, $endDate, $machine_id);
                                        }
                                        array_push($data['records'], [
                                            "job_id" => $records[$i]->job_id,
                                            "job_name" => $records[$i]->job_name,
                                            "product_number" => $records[$i]->product_number,
                                            "material_combination" => $records[$i]->material_combination,
                                            "nominal_speed" => $records[$i]->nominal_speed,
                                            "user_id" => $records[$i]->user_id,
                                            "user_name" => $records[$i]->user_name,
                                            "job_length" => $records[$i]->job_length,
                                            "error_id" => $records[$i]->error_id,
                                            "error_name" => $records[$i]->error_name,
                                            "comments" => str_replace('#', 'no', $records[$i]->comments),
                                            "length" => $records[$i]->length - $oldLength,
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "duration" => $duration,
                                            "instantSpeed" => $instantSpeed,
                                            "process_name" => $records[$i]->process_name,
                                        ]);
                                        $startDate = $endDate;
                                        if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                            $oldLength = $records[$i + 1]->length;
                                            if($jobRunTime == 0) {
                                                $jobPerformance = 0;
                                                $jobAverageSpeed = 0;
                                            } else {
                                                if($data['machine']->time_uom == 'Min') {
                                                    $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                                    $jobAverageSpeed = $jobProduction / $jobRunTime;
                                                } elseif($data['machine']->time_uom == 'Hr') {
                                                    $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                                    $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                                } elseif($data['machine']->time_uom == 'Sec') {
                                                    $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                                    $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                                }
                                            }
                                            for($j = 0; $j < count($data['records']); $j++) {
                                                array_push($data['records'][$j], [
                                                    "jobProduction" => $jobProduction,
                                                    "jobPerformance" => $jobPerformance,
                                                    "jobRuntime" => $jobRunTime,
                                                    "jobAverageSpeed" => $jobAverageSpeed
                                                ]);
                                            }
                                            $jobProduction = 0;
                                            $jobRunTime = 0;
                                        } else {
                                            $oldLength = $records[$i]->length;
                                        }
                                    }
                                }
                            } else {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                if($data['machine']->time_uom == 'Hr') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                    }
                                } elseif($data['machine']->time_uom == 'Min') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                    }
                                } else {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                    }
                                }
                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        $jobProduction += $records[$i]->length - $oldLength;
                                    }
                                }
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }
                                if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500) {
                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => 501,
                                        "error_name" => 'Reel Change Over',
                                        "comments" => 'Auto Minor Stop by Roto-eye',
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "jobProduction" => $production,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    $oldLength = $records[$i]->length;
                                } else {
                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_name" => $records[$i]->user_name,
                                        "user_id" => $records[$i]->user_id,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "comments" => str_replace('#', 'no', $records[$i]->comments),
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "jobProduction" => $production,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    $oldLength = $records[$i]->length;
                                }
                            }
                        }
                        for($k = 0; $k < count($data['records']); $k++) {
                            if($jobRunTime == 0) {
                                $jobPerformance = 0;
                                $jobAverageSpeed = 0;
                            } else {
                                if($data['machine']->time_uom == 'Min') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = $jobProduction / $jobRunTime;
                                } elseif($data['machine']->time_uom == 'Hr') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                } elseif($data['machine']->time_uom == 'Sec') {
                                    $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                }
                            }
                            if(!isset($data['records'][$k][0]['jobProduction']) || !isset($data['records'][$k][0]['jobPerformance'])) {
                                array_push($data['records'][$k], [
                                    "jobProduction" => $jobProduction,
                                    "jobPerformance" => $jobPerformance,
                                    "jobRuntime" => $jobRunTime,
                                    "jobAverageSpeed" => $jobAverageSpeed
                                ]);
                            }
                        }
                        if($runTime > 0) {
                            if($data['machine']->time_uom == 'Hr') {
                                $actualSpeed = $production / $runTime * 60;
                            } elseif($data['machine']->time_uom == 'Min') {
                                $actualSpeed = $production / $runTime;
                            } else {
                                $actualSpeed = $production / $runTime / 60;
                            }
                        } else {
                            $actualSpeed = 0;
                        }
                    }
                    //echo '<pre>';
                    //print_r($data['records']);
                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    $data['produced'] = $production;
                    $data['oee'] = ($runTime / ($totalTime - $idleTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['performance'] = ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['availability_ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * 100;
                    $data['availability'] = ($runTime / ($totalTime - $idleTime)) * 100;
                    $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
                    $data['run_time'] = $runTime;
                    $data['budgetedTime'] = $totalTime - $idleTime;
                    $data['shift'] = $request->input('shiftSelection');
                    $data['date'] = $date;

                    if(count($data['negativeRecords']) > 0) {

                        Mail::send('emails.negative-meters', $data, function ($message) use ($data) {
                            $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                            $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                                ->cc('nauman.abid@packages.com.pk', 'M Nauman Abid')
                                ->cc('haroon.naseer@packages.com.pk', 'M Haroon Naseer')
                                ->subject("RotoEye Cloud - Negative Meters");
                        });

                        Session::flash('success', 'We have resolved some negative meters during this production period. Please run the report again.');
                        $row['view'] = View::make('reports.shift-production-report-export-version', $data)->render();
                        return view('roto.reports', $row);
                        //       return Redirect::back();
                    } else {
                        //        return $data['records'];
                        $row['view'] = View::make('reports.shift-production-report-export-version', $data)->render();
                        return view('reports.shift-production-report-export-version', $data);
                        //      return $data['records'];
                        //         return view('roto.reports', $row);
                    }
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }

            } elseif($request->input('reportType') == 'shift-production-report-raw-old') {
                $shiftSelection = $request->input('shiftSelection');
                if($shiftSelection[0] == 'All-Day') {
                    $from_date = $request->input('date');
                    $to_date = $request->input('to_date');

                    $data['from'] = $from_date;
                    $data['to'] = $to_date;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($from_date.' + 390 minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($to_date.' + 1830 minutes'));
                } else {

                    $minStarted = Shift::find($shiftSelection[0])->min_started;
                    $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
                }

                $data['machine'] = Machine::find($machine_id);
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

                if(date('Y-m-d H:i:s') < $endDateTime) {
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
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('machine_id', '=', $machine_id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();

                if(count($records) > 0) {
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

                    $totalTimeDifference = date_diff(date_create($startDateTime), date_create($endDateTime));
                    $totalTime = (($totalTimeDifference->y * 365 + $totalTimeDifference->m * 30 + $totalTimeDifference->d) * 24 + $totalTimeDifference->h) * 60 + $totalTimeDifference->i + $totalTimeDifference->s / 60;
                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {
                            if(isset($records[$i + 1])) {
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    if($data['machine']->time_uom == 'Hr') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                        }
                                    } elseif($data['machine']->time_uom == 'Min') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                        }
                                    } else {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                        }
                                    }
                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $jobRunTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            $jobProduction += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                        }
                                    }
                                    if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500) {
                                        if($records[$i]->length - $oldLength < 0) {
                                            array_push($data['negativeRecords'], [
                                                "startDate" => $startDate,
                                                "endDate" => $endDate,
                                                "machine_id" => $machine_id,
                                                "sap_code" => $data['machine']->sap_code,
                                                "machine_name" => $data['machine']->name
                                            ]);
                                            $this->resolveNegatives($startDate, $endDate, $machine_id);
                                        }
                                        array_push($data['records'], [
                                            "job_id" => $records[$i]->job_id,
                                            "job_name" => $records[$i]->job_name,
                                            "product_number" => $records[$i]->product_number,
                                            "material_combination" => $records[$i]->material_combination,
                                            "nominal_speed" => $records[$i]->nominal_speed,
                                            "user_name" => $records[$i]->user_name,
                                            "job_length" => $records[$i]->job_length,
                                            "error_id" => 501,
                                            "error_name" => 'Auto Error',
                                            "comments" => 'Auto Minor Stop by Roto-eye',
                                            "length" => $records[$i]->length - $oldLength,
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "duration" => $duration,
                                            "instantSpeed" => $instantSpeed,
                                            "process_name" => $records[$i]->process_name,
                                        ]);
                                        $startDate = $endDate;
                                        if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                            $oldLength = $records[$i + 1]->length;
                                            $jobProduction = $production;
                                            if($jobRunTime == 0) {
                                                $jobPerformance = 0;
                                            } else {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                            }
                                            foreach($data['records'] as $record) {
                                                array_push($record, [
                                                    "jobProduction" => $jobProduction,
                                                    "jobPerformance" => $jobPerformance,
                                                    "jobRuntime" => $jobRunTime
                                                ]);
                                                $jobProduction = 0;
                                                $jobRunTime = 0;
                                            }
                                        } else {
                                            $oldLength = $records[$i]->length;
                                        }
                                    } else {
                                        if($records[$i]->length - $oldLength < 0) {
                                            array_push($data['negativeRecords'], [
                                                "startDate" => $startDate,
                                                "endDate" => $endDate,
                                                "machine_id" => $machine_id,
                                                "sap_code" => $data['machine']->sap_code,
                                                "machine_name" => $data['machine']->name
                                            ]);
                                            $this->resolveNegatives($startDate, $endDate, $machine_id);
                                        }
                                        array_push($data['records'], [
                                            "job_id" => $records[$i]->job_id,
                                            "job_name" => $records[$i]->job_name,
                                            "product_number" => $records[$i]->product_number,
                                            "material_combination" => $records[$i]->material_combination,
                                            "nominal_speed" => $records[$i]->nominal_speed,
                                            "user_name" => $records[$i]->user_name,
                                            "job_length" => $records[$i]->job_length,
                                            "error_id" => $records[$i]->error_id,
                                            "error_name" => $records[$i]->error_name,
                                            "comments" => str_replace('#', 'no', $records[$i]->comments),
                                            "length" => $records[$i]->length - $oldLength,
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "duration" => $duration,
                                            "instantSpeed" => $instantSpeed,
                                            "process_name" => $records[$i]->process_name,
                                        ]);
                                        $startDate = $endDate;
                                        if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                            $oldLength = $records[$i + 1]->length;
                                            if($jobRunTime == 0) {
                                                $jobPerformance = 0;
                                                $jobAverageSpeed = 0;
                                            } else {
                                                if($data['machine']->time_uom == 'Min') {
                                                    $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                                    $jobAverageSpeed = $jobProduction / $jobRunTime;
                                                } elseif($data['machine']->time_uom == 'Hr') {
                                                    $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                                    $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                                } elseif($data['machine']->time_uom == 'Sec') {
                                                    $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                                    $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                                }
                                            }
                                            for($j = 0; $j < count($data['records']); $j++) {
                                                array_push($data['records'][$j], [
                                                    "jobProduction" => $jobProduction,
                                                    "jobPerformance" => $jobPerformance,
                                                    "jobRuntime" => $jobRunTime,
                                                    "jobAverageSpeed" => $jobAverageSpeed
                                                ]);
                                            }
                                            $jobProduction = 0;
                                            $jobRunTime = 0;
                                        } else {
                                            $oldLength = $records[$i]->length;
                                        }
                                    }
                                }
                            } else {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                if($data['machine']->time_uom == 'Hr') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                    }
                                } elseif($data['machine']->time_uom == 'Min') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                    }
                                } else {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                    }
                                }
                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        $jobProduction += $records[$i]->length - $oldLength;
                                    }
                                }
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }
                                if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500) {
                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => 501,
                                        "error_name" => 'Reel Change Over',
                                        "comments" => 'Auto Minor Stop by Roto-eye',
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "jobProduction" => $production,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    $oldLength = $records[$i]->length;
                                } else {
                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "comments" => str_replace('#', 'no', $records[$i]->comments),
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "jobProduction" => $production,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    $oldLength = $records[$i]->length;
                                }
                            }
                        }
                        for($k = 0; $k < count($data['records']); $k++) {
                            if($jobRunTime == 0) {
                                $jobPerformance = 0;
                                $jobAverageSpeed = 0;
                            } else {
                                if($data['machine']->time_uom == 'Min') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = $jobProduction / $jobRunTime;
                                } elseif($data['machine']->time_uom == 'Hr') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                } elseif($data['machine']->time_uom == 'Sec') {
                                    $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                }
                            }
                            if(!isset($data['records'][$k][0]['jobProduction']) || !isset($data['records'][$k][0]['jobPerformance'])) {
                                array_push($data['records'][$k], [
                                    "jobProduction" => $jobProduction,
                                    "jobPerformance" => $jobPerformance,
                                    "jobRuntime" => $jobRunTime,
                                    "jobAverageSpeed" => $jobAverageSpeed
                                ]);
                            }
                        }
                        if($runTime > 0) {
                            if($data['machine']->time_uom == 'Hr') {
                                $actualSpeed = $production / $runTime * 60;
                            } elseif($data['machine']->time_uom == 'Min') {
                                $actualSpeed = $production / $runTime;
                            } else {
                                $actualSpeed = $production / $runTime / 60;
                            }
                        } else {
                            $actualSpeed = 0;
                        }
                    }
                    //echo '<pre>';
                    //print_r($data['records']);
                    if(Session::get('rights') == 0 || Session::get('rights') == 3) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    $data['produced'] = $production;
                    $data['oee'] = ($runTime / ($totalTime - $idleTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['performance'] = ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['availability_ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * 100;
                    $data['availability'] = ($runTime / ($totalTime - $idleTime)) * 100;
                    $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
                    $data['run_time'] = $runTime;
                    $data['budgetedTime'] = $totalTime - $idleTime;
                    $data['shift'] = $request->input('shiftSelection');
                    $data['date'] = $date;

                    if(count($data['negativeRecords']) > 0) {

                        Mail::send('emails.negative-meters', $data, function ($message) use ($data) {
                            $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                            $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                                ->cc('nauman.abid@packages.com.pk', 'M Nauman Abid')
                                ->cc('haroon.naseer@packages.com.pk', 'M Haroon Naseer')
                                ->subject("RotoEye Cloud - Negative Meters");
                        });

                        Session::flash('success', 'We have resolved some negative meters during this production period. Please run the report again.');
                        $row['view'] = View::make('reports.shift-production-report-export-version', $data)->render();
                        return view('roto.reports', $row);
                        //     return Redirect::back();
                    } else {

                        $row['view'] = View::make('reports.shift-production-report-export-version', $data)->render();
                        //    return $data['records'];
                        return view('roto.reports', $row);
                    }
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }
            //Shift Production Report Summarized
            elseif($request->input('reportType') == 'shift-production-report-summarized') {
                $shiftSelection = $request->input('shiftSelection');

                //dd($data);
                if($shiftSelection[0] == 'All-Day') {
                    //haseeb 6/3/2021
                    $machine = Machine::find($machine_id);
                    $shifts_id = [];
                    foreach($machine->section->department->businessUnit->company->shifts as $shift) {
                        array_push($shifts_id, $shift->id);
                    }

                    $minStarted = Shift::find($shifts_id[0])->min_started;
                    $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;
                    //haseeb 6/3/2021

                    $from_date = $request->input('date');
                    $to_date = $request->input('to_date');

                    $data['from'] = $from_date;
                    $data['to'] = $to_date;

                    //haseeb 6/3/2021
                    $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));

                    //$startDateTime = date('Y-m-d H:i:s', strtotime($from_date.' + 390 minutes'));
                    //$endDateTime = date('Y-m-d H:i:s', strtotime($to_date.' + 1830 minutes'));
                    //haseeb 6/3/2021
                } else {

                    $minStarted = Shift::find($shiftSelection[0])->min_started;
                    $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
                    $data['from'] = $date;
                    $data['to'] = $date;
                }

                $data['machine'] = Machine::find($machine_id);
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

                if(date('Y-m-d H:i:s') < $endDateTime) {
                    $endDateTime = date('Y-m-d H:i:s');
                }
                /*$records = DB::table('records')
                    ->leftJoin('errors', 'errors.id', '=', 'records.error_id')
                    ->leftJoin('users', 'users.id', '=', 'records.user_id')
                    ->leftJoin('jobs', 'jobs.id', '=', 'records.job_id')
                    ->leftJoin('products', 'products.id', '=', 'jobs.product_id')
                    ->leftJoin('material_combination', 'material_combination.id', '=', 'products.material_combination_id')
                    ->select('errors.name as error_name', 'records.run_date_time as run_date_time', 'records.error_id as error_id', 'records.length as length', 'records.err_comments as comments',
                        'jobs.id as job_id', 'products.name as job_name', 'jobs.job_length as job_length', 'products.name as product_name', 'products.id as product_number',
                        'material_combination.name as material_combination', 'material_combination.nominal_speed as nominal_speed', 'records.user_id as user_id', 'users.name as user_name')
                    ->where('machine_id', '=', $machine_id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();*/

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
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('machine_id', '=', $machine_id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();
                if(count($records) > 0) {
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

                    $totalTime = (strtotime($endDateTime) - strtotime($startDateTime)) / 60;

                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {
                            if(isset($records[$i + 1])) {
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    if($data['machine']->time_uom == 'Hr') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                        }
                                    } elseif($data['machine']->time_uom == 'Min') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                        }
                                    } else {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                        }
                                    }
                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $jobRunTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            $jobProduction += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                        }
                                    }
                                    if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500 && false) {
                                        if($records[$i]->length - $oldLength < 0) {
                                            array_push($data['negativeRecords'], [
                                                "startDate" => $startDate,
                                                "endDate" => $endDate,
                                                "machine_id" => $machine_id,
                                                "sap_code" => $data['machine']->sap_code,
                                                "machine_name" => $data['machine']->name
                                            ]);
                                            //$this->resolveNegatives($startDate, $endDate, $machine_id);
                                        }
                                        array_push($data['records'], [
                                            "job_id" => $records[$i]->job_id,
                                            "job_name" => $records[$i]->job_name,
                                            "product_number" => $records[$i]->product_number,
                                            "material_combination" => $records[$i]->material_combination,
                                            "nominal_speed" => $records[$i]->nominal_speed,
                                            "user_id" => $records[$i]->user_id,
                                            "user_name" => $records[$i]->user_name,
                                            "job_length" => $records[$i]->job_length,
                                            "error_id" => 501,
                                            "error_name" => 'Auto Error',
                                            "comments" => 'Auto Minor Stop by Roto-eye',
                                            "length" => $records[$i]->length - $oldLength,
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "duration" => $duration,
                                            "instantSpeed" => $instantSpeed,
                                            "process_name" => $records[$i]->process_name,
                                        ]);
                                        $startDate = $endDate;
                                        if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                            $oldLength = $records[$i + 1]->length;
                                            $jobProduction = $production;
                                            if($jobRunTime == 0) {
                                                $jobPerformance = 0;
                                            } else {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                            }
                                            foreach($data['records'] as $record) {
                                                array_push($record, [
                                                    "jobProduction" => $jobProduction,
                                                    "jobPerformance" => $jobPerformance,
                                                    "jobRuntime" => $jobRunTime
                                                ]);
                                                $jobProduction = 0;
                                                $jobRunTime = 0;
                                            }
                                        } else {
                                            $oldLength = $records[$i]->length;
                                        }
                                    } else {
                                        if($records[$i]->length - $oldLength < 0) {
                                            array_push($data['negativeRecords'], [
                                                "startDate" => $startDate,
                                                "endDate" => $endDate,
                                                "machine_id" => $machine_id,
                                                "sap_code" => $data['machine']->sap_code,
                                                "machine_name" => $data['machine']->name
                                            ]);
                                            //$this->resolveNegatives($startDate, $endDate, $machine_id);
                                        }
                                        array_push($data['records'], [
                                            "job_id" => $records[$i]->job_id,
                                            "job_name" => $records[$i]->job_name,
                                            "product_number" => $records[$i]->product_number,
                                            "material_combination" => $records[$i]->material_combination,
                                            "nominal_speed" => $records[$i]->nominal_speed,
                                            "user_id" => $records[$i]->user_id,
                                            "user_name" => $records[$i]->user_name,
                                            "job_length" => $records[$i]->job_length,
                                            "error_id" => $records[$i]->error_id,
                                            "error_name" => $records[$i]->error_name,
                                            "comments" => str_replace('#', 'no', $records[$i]->comments),
                                            "length" => $records[$i]->length - $oldLength,
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "duration" => $duration,
                                            "instantSpeed" => $instantSpeed,
                                            "process_name" => $records[$i]->process_name,
                                        ]);
                                        $startDate = $endDate;
                                        if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                            $oldLength = $records[$i + 1]->length;
                                            if($jobRunTime == 0) {
                                                $jobPerformance = 0;
                                            } else {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                            }
                                            for($j = 0; $j < count($data['records']); $j++) {
                                                array_push($data['records'][$j], [
                                                    "jobProduction" => $jobProduction,
                                                    "jobPerformance" => $jobPerformance,
                                                    "jobRuntime" => $jobRunTime
                                                ]);
                                            }
                                            $jobProduction = 0;
                                            $jobRunTime = 0;
                                        } else {
                                            $oldLength = $records[$i]->length;
                                        }
                                    }
                                }
                            } else {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                if($data['machine']->time_uom == 'Hr') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                    }
                                } elseif($data['machine']->time_uom == 'Min') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                    }
                                } else {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                    }
                                }
                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        $jobProduction += $records[$i]->length - $oldLength;
                                    }
                                }
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }
                                if($duration <= $loginRecord[0]->machine->auto_downtime && $records[$i]->error_id == 500) {
                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        //$this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => 501,
                                        "error_name" => 'Reel Change Over',
                                        "comments" => 'Auto Minor Stop by Roto-eye',
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "jobProduction" => $production,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    $oldLength = $records[$i]->length;
                                } else {
                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        //$this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_name" => $records[$i]->user_name,
                                        "user_id" => $records[$i]->user_id,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "comments" => str_replace('#', 'no', $records[$i]->comments),
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "jobProduction" => $production,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    $oldLength = $records[$i]->length;
                                }
                            }
                        }
                        for($k = 0; $k < count($data['records']); $k++) {
                            if($jobRunTime == 0) {
                                $jobPerformance = 0;
                            } else {
                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                            }
                            if(!isset($data['records'][$k][0]['jobProduction']) || !isset($data['records'][$k][0]['jobPerformance'])) {
                                array_push($data['records'][$k], [
                                    "jobProduction" => $jobProduction,
                                    "jobPerformance" => $jobPerformance,
                                    "jobRuntime" => $jobRunTime
                                ]);
                            }
                        }
                        if($runTime > 0) {
                            if($data['machine']->time_uom == 'Hr') {
                                $actualSpeed = $production / $runTime * 60;
                            } elseif($data['machine']->time_uom == 'Min') {
                                $actualSpeed = $production / $runTime;
                            } else {
                                $actualSpeed = $production / $runTime / 60;
                            }
                        } else {
                            $actualSpeed = 0;
                        }
                    }
                    //echo '<pre>';
                    //print_r($data['records']);
                    if(Session::get('rights') == 0 || Session::get('rights') == 3) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }

                    array_multisort(
                        array_column($data['records'], 'job_id'),
                        SORT_ASC,
                        array_column($data['records'], 'error_id'),
                        SORT_ASC,
                        $data['records']
                    );

                    $data['produced'] = $production;
                    $data['oee'] = ($runTime / ($totalTime - $idleTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['performance'] = ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['availability_ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * 100;
                    $data['availability'] = ($runTime / ($totalTime - $idleTime)) * 100;
                    $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
                    $data['run_time'] = $runTime;
                    $data['budgetedTime'] = $totalTime - $idleTime;
                    $data['shift'] = $request->input('shiftSelection');
                    $data['date'] = $date;
                    $data['current_time'] = date('Y-m-d H:i:s');
                    if(count($data['negativeRecords']) > 0) {
                        /* Mail::send('emails.negative-meters', $data, function ($message) use ($data) {
                             $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                             $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                                 ->cc('nauman.abid@packages.com.pk', 'M Nauman Abid')
                                 ->cc('haroon.naseer@packages.com.pk', 'M Haroon Naseer')
                                 ->subject("RotoEye Cloud - Negative Meters");
                         });*/

                        Session::flash('success', 'We have resolved some negative meters during this production period. Please run the report again.');
                        $row['view'] = View::make('reports.shift-production-report-summarized', $data)->render();
                        return view('roto.reports', $row);
                        //       return Redirect::back();
                    } else {
                        $row['view'] = View::make('reports.shift-production-report-summarized', $data)->render();
                        //    return $data['records'];
                        return view('roto.reports', $row);
                    }
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }
            //Operator Wise OEE Report (Shift Wise and Duration Wise)
            elseif($request->input('reportType') == 'operator-wise-oee') {
                $shiftSelection = $request->input('shiftSelection');
                $operatorID = $request->input('operator');
                $data['budgetedTime'] = 0;

                if($shiftSelection[0] == 'All-Day') {
                    //haseeb 6/3/2021
                    $machine = Machine::find($machine_id);
                    $shifts_id = [];
                    foreach($machine->section->department->businessUnit->company->shifts as $shift) {
                        array_push($shifts_id, $shift->id);
                    }

                    $minStarted = Shift::find($shifts_id[0])->min_started;
                    $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;
                    //haseeb 6/3/2021

                    $from_date = $request->input('date');
                    $to_date = $request->input('to_date');

                    $data['from'] = $from_date;
                    $data['to'] = $to_date;

                    //haseeb 6/3/2021
                    $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));

                    //$startDateTime = date('Y-m-d H:i:s', strtotime($from_date.' + 390 minutes'));
                    //$endDateTime = date('Y-m-d H:i:s', strtotime($to_date.' + 1830 minutes'));
                    //haseeb 6/3/2021
                } else {

                    $minStarted = Shift::find($shiftSelection[0])->min_started;
                    $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
                }

                $data['machine'] = Machine::find($machine_id);
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

                if(date('Y-m-d H:i:s') < $endDateTime) {
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
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name'
                    )
                    ->where('machine_id', '=', $machine_id)
                    ->where('run_date_time', '>=', $startDateTime)
                    ->where('run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();
                ;
                if(count($records) > 0) {
                    if(in_array($operatorID, array_column($records->toArray(), 'user_id'))) {
                        $data['records'] = [];
                        $startDate = $records[0]->run_date_time;
                        $oldLength = $records[0]->length;
                        $runTime = 0;
                        $idleTime = 0;
                        $jobWaitTime = 0;
                        $production = 0;
                        $actualSpeed = 0;
                        $jobProduction = 0;
                        $totalTimeDifference = date_diff(date_create($startDateTime), date_create($endDateTime));
                        $totalTime = (($totalTimeDifference->y * 365 + $totalTimeDifference->m * 30 + $totalTimeDifference->d) * 24 + $totalTimeDifference->h) * 60 + $totalTimeDifference->i + $totalTimeDifference->s / 60;
                        if(count($records) > 1) {
                            for($i = 0; $i < count($records); $i++) {
                                if(isset($records[$i + 1])) {
                                    if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id || date('Y-m-d', strtotime($records[$i]->run_date_time)) != date('Y-m-d', strtotime($records[$i + 1]->run_date_time))) {
                                        $endDate = $records[$i]->run_date_time;
                                        $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                        $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;

                                        if($data['machine']->time_uom == 'Hr') {
                                            if($duration == 0) {
                                                $instantSpeed = 0;
                                            } else {
                                                $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                            }
                                        } elseif($data['machine']->time_uom == 'Min') {
                                            if($duration == 0) {
                                                $instantSpeed = 0;
                                            } else {
                                                $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                            }
                                        } else {
                                            if($duration == 0) {
                                                $instantSpeed = 0;
                                            } else {
                                                $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                            }
                                        }
                                        if($records[$i]->user_id == $operatorID) {

                                            $data['budgetedTime'] += $duration;
                                            foreach($runningCodes as $runningCode) {
                                                if($runningCode->id == $records[$i]->error_id) {
                                                    $runTime += $duration;
                                                    $production += $records[$i]->length - $oldLength;
                                                    $jobProduction += $records[$i]->length - $oldLength;
                                                }
                                            }
                                            foreach($idleErrors as $idleError) {
                                                if($idleError->id == $records[$i]->error_id) {
                                                    $idleTime += $duration;
                                                }
                                            }
                                            foreach($jobWaitingCodes as $jobWaitingCode) {
                                                if($jobWaitingCode->id == $records[$i]->error_id) {
                                                    $jobWaitTime += $duration;
                                                }
                                            }

                                            array_push($data['records'], [
                                                "job_id" => $records[$i]->job_id,
                                                "job_name" => $records[$i]->job_name,
                                                "product_number" => $records[$i]->product_number,
                                                "material_combination" => $records[$i]->material_combination,
                                                "nominal_speed" => $records[$i]->nominal_speed,
                                                "user_name" => $records[$i]->user_name,
                                                "job_length" => $records[$i]->job_length,
                                                "error_id" => $records[$i]->error_id,
                                                "error_name" => $records[$i]->error_name,
                                                "comments" => str_replace('#', 'no', $records[$i]->comments),
                                                "length" => $records[$i]->length - $oldLength,
                                                "from" => $startDate,
                                                "to" => $endDate,
                                                "duration" => $duration,
                                                "instantSpeed" => $instantSpeed,
                                            ]);
                                        }
                                        if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                            $oldLength = $records[$i + 1]->length;
                                            for($j = 0; $j < count($data['records']); $j++) {
                                                array_push($data['records'][$j], [
                                                    "jobProduction" => $jobProduction
                                                ]);
                                            }
                                            $jobProduction = 0;
                                        } else {
                                            $oldLength = $records[$i]->length;
                                        }

                                        $startDate = $endDate;
                                    }
                                } else {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;

                                    if($data['machine']->time_uom == 'Hr') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                        }
                                    } elseif($data['machine']->time_uom == 'Min') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                        }
                                    } else {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                        }
                                    }
                                    if($records[$i]->user_id == $operatorID) {
                                        $data['budgetedTime'] += $duration;
                                        foreach($runningCodes as $runningCode) {
                                            if($runningCode->id == $records[$i]->error_id) {
                                                $runTime += $duration;
                                                $production += $records[$i]->length - $oldLength;
                                                $jobProduction += $records[$i]->length - $oldLength;
                                            }
                                        }
                                        foreach($idleErrors as $idleError) {
                                            if($idleError->id == $records[$i]->error_id) {
                                                $idleTime += $duration;
                                            }
                                        }
                                        foreach($jobWaitingCodes as $jobWaitingCode) {
                                            if($jobWaitingCode->id == $records[$i]->error_id) {
                                                $jobWaitTime += $duration;
                                            }
                                        }

                                        array_push($data['records'], [
                                            "job_id" => $records[$i]->job_id,
                                            "job_name" => $records[$i]->job_name,
                                            "product_number" => $records[$i]->product_number,
                                            "material_combination" => $records[$i]->material_combination,
                                            "nominal_speed" => $records[$i]->nominal_speed,
                                            "user_name" => $records[$i]->user_name,
                                            "job_length" => $records[$i]->job_length,
                                            "error_id" => $records[$i]->error_id,
                                            "error_name" => $records[$i]->error_name,
                                            "comments" => str_replace('#', 'no', $records[$i]->comments),
                                            "length" => $records[$i]->length - $oldLength,
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "duration" => $duration,
                                            "instantSpeed" => $instantSpeed,
                                            "jobProduction" => $production
                                        ]);
                                    }
                                    $startDate = $endDate;
                                    $oldLength = $records[$i]->length;

                                }
                            }
                            for($k = 0; $k < count($data['records']); $k++) {
                                if(!isset($data['records'][$k][0]['jobProduction'])) {
                                    array_push($data['records'][$k], [
                                        "jobProduction" => $jobProduction
                                    ]);
                                }
                            }
                            if($runTime > 0) {
                                if($data['machine']->time_uom == 'Hr') {
                                    $actualSpeed = $production / $runTime * 60;
                                } elseif($data['machine']->time_uom == 'Min') {
                                    $actualSpeed = $production / $runTime;
                                } else {
                                    $actualSpeed = $production / $runTime / 60;
                                }
                            } else {
                                $actualSpeed = 0;
                            }
                        }
                        //echo '<pre>';
                        //print_r($data['records']);
                        if(Session::get('rights') == 0) {
                            $data['user'] = Users::find($loginRecord[0]->user_id);
                        } else {
                            $data['user'] = Users::find(Session::get('user_name'));
                        }
                        $data['produced'] = $production;
                        $data['oee'] = ($runTime / ($data['budgetedTime'] - $idleTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                        $data['ee'] = ($runTime / ($data['budgetedTime'] - $idleTime - $jobWaitTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                        $data['performance'] = ($actualSpeed / $data['machine']->max_speed) * 100;
                        $data['availability_ee'] = ($runTime / ($data['budgetedTime'] - $idleTime - $jobWaitTime)) * 100;
                        $data['availability'] = ($runTime / ($data['budgetedTime'] - $idleTime)) * 100;
                        $data['run_time'] = $runTime;
                        $data['shift'] = $request->input('shiftSelection');
                        $data['date'] = $date;
                        $data['totalDowntime'] = ($data['budgetedTime'] - $idleTime) - $runTime;
                        $data['run_time'] = $runTime;
                        return view('reports.operator-wise-oee', $data);
                    } else {
                        Session::flash("error", "No Record for the selected shift and date. Please try again.");
                        return Redirect::back();
                    }
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            } elseif($request->input('reportType') == 'job-wise-performance') {

            } elseif($request->input('reportType') == 'production-report') {
                $shiftSelection = $request->input('shiftSelection');
                if($shiftSelection[0] == 'All-Day') {
                    //haseeb 6/3/2021
                    $machine = Machine::find($machine_id);

                    $shifts_id = [];
                    foreach($machine->section->department->businessUnit->company->shifts as $shift) {
                        array_push($shifts_id, $shift->id);
                    }

                    $minStarted = Shift::find($shifts_id[0])->min_started;
                    $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;

                    $from_date = $request->input('date');
                    $to_date = $request->input('to_date');

                    $data['from'] = $from_date;
                    $data['to'] = $to_date;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));
                } else {
                    $minStarted = Shift::find($shiftSelection[0])->min_started;
                    $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;

                    $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
                }

                $data['machine'] = Machine::find($machine_id);
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

                if(date('Y-m-d H:i:s') < $endDateTime) {
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
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('machine_id', '=', $machine_id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();
                if(count($records) > 0) {
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

                    $totalTimeDifference = date_diff(date_create($startDateTime), date_create($endDateTime));
                    $totalTime = (($totalTimeDifference->y * 365 + $totalTimeDifference->m * 30 + $totalTimeDifference->d) * 24 + $totalTimeDifference->h) * 60 + $totalTimeDifference->i + $totalTimeDifference->s / 60;
                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {
                            if(isset($records[$i + 1])) {
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    if($data['machine']->time_uom == 'Hr') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                        }
                                    } elseif($data['machine']->time_uom == 'Min') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                        }
                                    } else {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                        }
                                    }
                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $jobRunTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            $jobProduction += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                        }
                                    }

                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "comments" => str_replace('#', 'no', $records[$i]->comments),
                                        "length" => $records[$i]->length - $oldLength,
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "process_name" => $records[$i]->process_name,
                                    ]);
                                    $startDate = $endDate;
                                    if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                        $oldLength = $records[$i + 1]->length;
                                        if($jobRunTime == 0) {
                                            $jobPerformance = 0;
                                            $jobAverageSpeed = 0;
                                        } else {
                                            if($data['machine']->time_uom == 'Min') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = $jobProduction / $jobRunTime;
                                            } elseif($data['machine']->time_uom == 'Hr') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                            } elseif($data['machine']->time_uom == 'Sec') {
                                                $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                                $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                            }
                                        }
                                        for($j = 0; $j < count($data['records']); $j++) {
                                            array_push($data['records'][$j], [
                                                "jobProduction" => $jobProduction,
                                                "jobPerformance" => $jobPerformance,
                                                "jobRuntime" => $jobRunTime,
                                                "jobAverageSpeed" => $jobAverageSpeed
                                            ]);
                                        }
                                        $jobProduction = 0;
                                        $jobRunTime = 0;
                                    } else {
                                        $oldLength = $records[$i]->length;
                                    }

                                }
                            } else {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                if($data['machine']->time_uom == 'Hr') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                    }
                                } elseif($data['machine']->time_uom == 'Min') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                    }
                                } else {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                    }
                                }
                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        $jobProduction += $records[$i]->length - $oldLength;
                                    }
                                }
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }
                                if($records[$i]->length - $oldLength < 0) {
                                    array_push($data['negativeRecords'], [
                                        "startDate" => $startDate,
                                        "endDate" => $endDate,
                                        "machine_id" => $machine_id,
                                        "sap_code" => $data['machine']->sap_code,
                                        "machine_name" => $data['machine']->name
                                    ]);
                                    $this->resolveNegatives($startDate, $endDate, $machine_id);
                                }
                                array_push($data['records'], [
                                    "job_id" => $records[$i]->job_id,
                                    "job_name" => $records[$i]->job_name,
                                    "product_number" => $records[$i]->product_number,
                                    "material_combination" => $records[$i]->material_combination,
                                    "nominal_speed" => $records[$i]->nominal_speed,
                                    "user_name" => $records[$i]->user_name,
                                    "user_id" => $records[$i]->user_id,
                                    "job_length" => $records[$i]->job_length,
                                    "error_id" => $records[$i]->error_id,
                                    "error_name" => $records[$i]->error_name,
                                    "comments" => str_replace('#', 'no', $records[$i]->comments),
                                    "length" => $records[$i]->length - $oldLength,
                                    "from" => $startDate,
                                    "to" => $endDate,
                                    "duration" => $duration,
                                    "instantSpeed" => $instantSpeed,
                                    "jobProduction" => $production,
                                    "process_name" => $records[$i]->process_name,
                                ]);
                                $startDate = $endDate;
                                $oldLength = $records[$i]->length;

                            }
                        }
                        for($k = 0; $k < count($data['records']); $k++) {
                            if($jobRunTime == 0) {
                                $jobPerformance = 0;
                                $jobAverageSpeed = 0;
                            } else {
                                if($data['machine']->time_uom == 'Min') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = $jobProduction / $jobRunTime;
                                } elseif($data['machine']->time_uom == 'Hr') {
                                    $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                } elseif($data['machine']->time_uom == 'Sec') {
                                    $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                    $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                }
                            }
                            if(!isset($data['records'][$k][0]['jobProduction']) || !isset($data['records'][$k][0]['jobPerformance'])) {
                                array_push($data['records'][$k], [
                                    "jobProduction" => $jobProduction,
                                    "jobPerformance" => $jobPerformance,
                                    "jobRuntime" => $jobRunTime,
                                    "jobAverageSpeed" => $jobAverageSpeed
                                ]);
                            }
                        }
                        if($runTime > 0) {
                            if($data['machine']->time_uom == 'Hr') {
                                $actualSpeed = $production / $runTime * 60;
                            } elseif($data['machine']->time_uom == 'Min') {
                                $actualSpeed = $production / $runTime;
                            } else {
                                $actualSpeed = $production / $runTime / 60;
                            }
                        } else {
                            $actualSpeed = 0;
                        }
                    }
                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    $data['produced'] = $production;
                    $data['oee'] = ($runTime / ($totalTime - $idleTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['performance'] = ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['availability_ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * 100;
                    $data['availability'] = ($runTime / ($totalTime - $idleTime)) * 100;
                    $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
                    $data['run_time'] = $runTime;
                    $data['budgetedTime'] = $totalTime - $idleTime;
                    $data['shift'] = $request->input('shiftSelection');
                    $data['date'] = $date;

                    if(count($data['negativeRecords']) > 0) {

                        Mail::send('emails.negative-meters', $data, function ($message) use ($data) {
                            $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                            $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                                ->cc('haroon.naseer@packages.com.pk', 'M Haroon Naseer')
                                ->subject("RotoEye Cloud - Negative Meters");
                        });

                        Session::flash('success', 'We have resolved some negative meters during this production period. Please run the report again.');
                        $row['view'] = View::make('reports.production-report', $data)->render();
                        return view('roto.reports', $row);
                    } else {
                        $row['view'] = View::make('reports.production-report', $data)->render();
                        return view('reports.production-report', $data);
                    }
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            } elseif($request->input('reportType') == 'shift-production-report-next') {
                $shiftSelection = $request->input('shiftSelection');
                $machine = Machine::find($machine_id);
                if($shiftSelection[0] == 'All-Day') {
                    $shifts_id = [];
                    foreach($machine->section->department->businessUnit->company->shifts as $shift) {
                        array_push($shifts_id, $shift->id);
                    }
                    $minStarted = Shift::find($shifts_id[0])->min_started;
                    $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;
                    $from_date = $request->input('date');
                    $to_date = $request->input('to_date');
                    $data['from'] = $from_date;
                    $data['to'] = $to_date;
                    $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));

                } else {
                    $minStarted = Shift::find($shiftSelection[0])->min_started;
                    $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;
                    $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
                    $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
                }

                $data['machine'] = Machine::find($machine_id);
                $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();
                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

                if(date('Y-m-d H:i:s') < $endDateTime) {
                    $endDateTime = date('Y-m-d H:i:s');
                }

                $records = DB::table('records')
                    ->leftJoin('errors', 'errors.id', '=', 'records.error_id')
                    ->leftJoin('users', 'users.id', '=', 'records.user_id')
                    ->leftJoin('jobs', 'jobs.id', '=', 'records.job_id')
                    ->leftJoin('products', 'products.id', '=', 'jobs.product_id')
                    ->leftJoin('product_sleeve', 'product_sleeve.product_id', '=', 'products.id')
                    ->leftJoin('sleeves', 'sleeves.id', '=', 'product_sleeve.sleeve_id')
                    ->leftJoin('process_structure', function ($join) {
                        $join->on('process_structure.process_id', '=', 'records.process_id');
                        $join->on('process_structure.product_id', '=', 'products.id');
                    })
                    ->leftJoin('machine_sleeve', function ($join) {
                        $join->on('machine_sleeve.sleeve_id', '=', 'sleeves.id');
                        $join->on('machine_sleeve.machine_id', '=', 'records.machine_id');
                    })
                    ->leftJoin('material_combination', 'material_combination.id', '=', 'process_structure.material_combination_id')
                    ->leftJoin('processes', 'processes.id', '=', 'process_structure.process_id')
                    ->select(
                        'errors.name as error_name',
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'products.ups as ups',
                        'products.col as col',
                        'products.slitted_reel_width as slitted_reel_width',
                        'products.trim_width as trim_width',
                        'products.gsm as gsm',
                        'products.thickness as thickness',
                        'products.density as density',
                        'machine_sleeve.speed as sleeve_speed',
                        'sleeves.circumference as sleeve_circumference',
                        'material_combination.name as material_combination',
                        'process_structure.color as process_structure_color',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('records.machine_id', '=', $machine_id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();

                if(count($records) > 0) {
                    $data['records'] = [];
                    $data['negativeRecords'] = [];
                    $startDate = $records[0]->run_date_time;
                    $oldLength = $records[0]->length;
                    $runTime = 0;
                    $idleTime = 0;
                    $jobWaitTime = 0;
                    $production = 0;
                    $targetHour = 0;
                    $jobTargetHour = 0;
                    $productionArea = 0;
                    $jobProductionArea = 0;
                    $rotoeyeNextProduction = 0;
                    $gsm = 0;
                    $jobGsm = 0;
                    $ea = 0;
                    $jobEa = 0;
                    $jobProduction = 0;
                    $jobRunTime = 0;

                    $totalTime = (strtotime($endDateTime) - strtotime($startDateTime)) / 60;
                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {

                            $running_speed = isset($records[$i]->sleeve_speed) ? $records[$i]->sleeve_speed : $data['machine']->max_speed;
                            if(isset($records[$i + 1])) {
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    if($data['machine']->time_uom == 'Hr') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                        }
                                    } elseif($data['machine']->time_uom == 'Min') {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                        }
                                    } else {
                                        if($duration == 0) {
                                            $instantSpeed = 0;
                                        } else {
                                            $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                        }
                                    }
                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $jobRunTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            if($data['machine']->time_uom == 'Min') {
                                                $targetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                                $jobTargetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                            } elseif($data['machine']->time_uom == 'Hr') {
                                                $targetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;
                                                $jobTargetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;

                                            } elseif($data['machine']->time_uom == 'Sec') {
                                                $targetHour += ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                                $jobTargetHour += ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                            }
                                            if(isset($records[$i]->trim_width) && isset($records[$i]->slitted_reel_width) && isset($records[$i]->ups) && isset($machine->machine_width)) {
                                                $rotoeyeNextProduction += $records[$i]->length - $oldLength;
                                                $productionArea += ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength);
                                                $jobProductionArea += ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength);
                                                if(isset($records[$i]->gsm)) {
                                                    $gsm += $records[$i]->gsm * ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength);
                                                    $jobGsm += $records[$i]->gsm * ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength);
                                                }

                                            }
                                            if(isset($records[$i]->col) && isset($records[$i]->ups) && ($records[$i]->ups > 0) && ($records[$i]->col > 0)) {
                                                $ea += ($records[$i]->length - $oldLength) / $records[$i]->col * $records[$i]->ups;
                                                $jobEa += ($records[$i]->length - $oldLength) / $records[$i]->col * $records[$i]->ups;
                                            }

                                            $jobProduction += $records[$i]->length - $oldLength;
                                        }
                                    }
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                        }
                                    }

                                    if($records[$i]->length - $oldLength < 0) {
                                        array_push($data['negativeRecords'], [
                                            "startDate" => $startDate,
                                            "endDate" => $endDate,
                                            "machine_id" => $machine_id,
                                            "sap_code" => $data['machine']->sap_code,
                                            "machine_name" => $data['machine']->name
                                        ]);
                                        $this->resolveNegatives($startDate, $endDate, $machine_id);
                                    }

                                    ////   dump($records[$i]);
                                    // dump(($records[$i]->length-$oldLength)/($records[$i]->col*100*$records[$i]->ups));
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "product_ups" => $records[$i]->ups,
                                        "product_col" => $records[$i]->col,
                                        "product_slitted_reel_width" => $records[$i]->slitted_reel_width,
                                        "product_trim_width" => $records[$i]->trim_width,
                                        "product_gsm" => $records[$i]->gsm,
                                        "product_thickness" => $records[$i]->thickness,
                                        "product_density" => $records[$i]->density,
                                        "product_sleeve_speed" => $records[$i]->sleeve_speed,
                                        "product_sleeve_circumference" => $records[$i]->sleeve_circumference,
                                        "material_combination" => $records[$i]->material_combination,
                                        "process_structure_color" => $records[$i]->process_structure_color,
                                        "nominal_speed" => $records[$i]->nominal_speed,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "job_length" => $records[$i]->job_length,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "comments" => str_replace('#', 'no', $records[$i]->comments),
                                        "length" => $records[$i]->length - $oldLength,
                                        "gsm" => (isset($records[$i]->gsm) && isset($records[$i]->slitted_reel_width) && isset($records[$i]->ups) && isset($records[$i]->trim_width)) ?
                                            number_format($records[$i]->gsm * ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength), 1) : '-',
                                        "pope_production" => (isset($records[$i]->gsm) && isset($records[$i]->slitted_reel_width) && isset($records[$i]->ups) && isset($records[$i]->trim_width)) ?
                                            $duration > 0 ? number_format((($records[$i]->gsm * ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength) * 60) / 1000) / $duration, 2) : 0 : '-',
                                        "pope_production_kgs" => (isset($records[$i]->gsm) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($records[$i]->job_trimwidth)) ?
                                            $duration > 0 ? number_format((($records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength) * 60)) / $duration, 2) : 0 : '-',
                                        "ea" => isset($records[$i]->ups) && isset($records[$i]->col) && ($records[$i]->ups > 0) && ($records[$i]->col > 0) ? number_format(($records[$i]->length - $oldLength) / ($records[$i]->col * $records[$i]->ups), 2) : '-',
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "duration" => $duration,
                                        "instantSpeed" => $instantSpeed,
                                        "process_name" => $records[$i]->process_name,
                                    ]);

                                    $startDate = $endDate;
                                    if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                        $oldLength = $records[$i + 1]->length;
                                        if($jobRunTime == 0) {
                                            $jobPerformance = 0;
                                            $jobAverageSpeed = 0;
                                        } else {

                                            if($data['machine']->time_uom == 'Min') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) / $running_speed) * 100;
                                                $jobAverageSpeed = $jobProduction / $jobRunTime;
                                            } elseif($data['machine']->time_uom == 'Hr') {
                                                $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $running_speed) * 100;
                                                $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                            } elseif($data['machine']->time_uom == 'Sec') {
                                                $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $running_speed) * 100;
                                                $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                            }
                                        }

                                        for($j = 0; $j < count($data['records']); $j++) {
                                            $jobUtilization = 0;
                                            if(isset($records[$i]->trim_width) && isset($records[$i]->slitted_reel_width) && isset($records[$i]->ups) && isset($machine->machine_width) && $jobProduction > 0) {
                                                $jobUtilization = $jobProductionArea / ($jobProduction * $machine->machine_width) * 100;
                                            }
                                            array_push($data['records'][$j], [
                                                "jobProduction" => $jobProduction,
                                                "jobTargetHour" => $jobTargetHour,
                                                "jobUtilization" => $jobUtilization,
                                                "jobGsm" => $jobGsm,
                                                "jobEa" => $jobEa,
                                                "jobPerformance" => $jobPerformance,
                                                "jobRuntime" => $jobRunTime,
                                                "jobAverageSpeed" => $jobAverageSpeed
                                            ]);
                                        }

                                        $jobProduction = 0;
                                        $jobRunTime = 0;
                                        $jobTargetHour = 0;
                                        $jobGsm = 0;
                                        $jobEa = 0;
                                        $jobProductionArea = 0;
                                    } else {
                                        $oldLength = $records[$i]->length;
                                    }

                                }

                            } else {

                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                if($data['machine']->time_uom == 'Hr') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                    }
                                } elseif($data['machine']->time_uom == 'Min') {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                    }
                                } else {
                                    if($duration == 0) {
                                        $instantSpeed = 0;
                                    } else {
                                        $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                    }
                                }
                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        if($data['machine']->time_uom == 'Min') {
                                            $targetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                            $jobTargetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                        } elseif($data['machine']->time_uom == 'Hr') {
                                            $targetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;
                                            $jobTargetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;

                                        } elseif($data['machine']->time_uom == 'Sec') {
                                            $targetHour = ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                            $jobTargetHour = ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                        }

                                        if(isset($records[$i]->trim_width) && isset($records[$i]->slitted_reel_width) && isset($records[$i]->ups) && isset($machine->machine_width)) {

                                            $rotoeyeNextProduction += $records[$i]->length - $oldLength;
                                            $productionArea += ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength);
                                            $jobProductionArea += ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength);
                                            if(isset($records[$i]->gsm)) {
                                                $gsm += $records[$i]->gsm * ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength);
                                                $jobGsm += $records[$i]->gsm * ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength);

                                            }

                                        }
                                        if(isset($records[$i]->col) && isset($records[$i]->ups) && ($records[$i]->ups > 0) && ($records[$i]->col > 0)) {
                                            $ea += ($records[$i]->length - $oldLength) * $records[$i]->col * $records[$i]->ups;
                                            $jobEa += ($records[$i]->length - $oldLength) * $records[$i]->col * $records[$i]->ups;
                                        }
                                        $jobProduction += $records[$i]->length - $oldLength;

                                    }
                                }
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }
                                if($records[$i]->length - $oldLength < 0) {
                                    array_push($data['negativeRecords'], [
                                        "startDate" => $startDate,
                                        "endDate" => $endDate,
                                        "machine_id" => $machine_id,
                                        "sap_code" => $data['machine']->sap_code,
                                        "machine_name" => $data['machine']->name
                                    ]);
                                    $this->resolveNegatives($startDate, $endDate, $machine_id);
                                }

                                array_push($data['records'], [
                                    "job_id" => $records[$i]->job_id,
                                    "job_name" => $records[$i]->job_name,
                                    "product_number" => $records[$i]->product_number,
                                    "product_ups" => $records[$i]->ups,
                                    "product_col" => $records[$i]->col,
                                    "product_slitted_reel_width" => $records[$i]->slitted_reel_width,
                                    "product_trim_width" => $records[$i]->trim_width,
                                    "product_gsm" => $records[$i]->gsm,
                                    "product_thickness" => $records[$i]->thickness,
                                    "product_density" => $records[$i]->density,
                                    "product_sleeve_speed" => $records[$i]->sleeve_speed,
                                    "product_sleeve_circumference" => $records[$i]->sleeve_circumference,
                                    "material_combination" => $records[$i]->material_combination,
                                    "process_structure_color" => $records[$i]->process_structure_color,
                                    "nominal_speed" => $records[$i]->nominal_speed,
                                    "user_id" => $records[$i]->user_id,
                                    "user_name" => $records[$i]->user_name,
                                    "job_length" => $records[$i]->job_length,
                                    "error_id" => $records[$i]->error_id,
                                    "error_name" => $records[$i]->error_name,
                                    "comments" => str_replace('#', 'no', $records[$i]->comments),
                                    "length" => $records[$i]->length - $oldLength,
                                    "gsm" => (isset($records[$i]->gsm) && isset($records[$i]->slitted_reel_width) && isset($records[$i]->ups) && isset($records[$i]->trim_width)) ?
                                        number_format($records[$i]->gsm * ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength), 2) : '-',
                                    "ea" => isset($records[$i]->ups) && isset($records[$i]->col) && ($records[$i]->col > 0) && ($records[$i]->ups > 0) ? number_format(($records[$i]->length - $oldLength) / ($records[$i]->col * $records[$i]->ups), 2) : '-',
                                    "pope_production" => (isset($records[$i]->gsm) && isset($records[$i]->slitted_reel_width) && isset($records[$i]->ups) && isset($records[$i]->trim_width)) ?
                                        $duration > 0 ? number_format((($records[$i]->gsm * ($records[$i]->trim_width + ($records[$i]->slitted_reel_width * $records[$i]->ups)) * ($records[$i]->length - $oldLength) * 60) / 1000) / $duration, 2) : 0 : '-',
                                    "pope_production_kgs" => (isset($records[$i]->gsm) && isset($records[$i]->job_reelwidth) && isset($records[$i]->job_ups) && isset($records[$i]->job_trimwidth)) ?
                                        $duration > 0 ? number_format((($records[$i]->job_gsm * ($records[$i]->job_trimwidth + ($records[$i]->job_reelwidth * $records[$i]->job_ups)) * ($records[$i]->length - $oldLength) * 60)) / $duration, 2) : 0 : '-',
                                    "from" => $startDate,
                                    "to" => $endDate,
                                    "duration" => $duration,
                                    "instantSpeed" => $instantSpeed,
                                    "process_name" => $records[$i]->process_name,
                                ]);

                                if($jobRunTime == 0) {
                                    $jobPerformance = 0;
                                    $jobAverageSpeed = 0;
                                } else {

                                    if($data['machine']->time_uom == 'Min') {
                                        $jobPerformance = (($jobProduction / $jobRunTime) / $running_speed) * 100;
                                        $jobAverageSpeed = $jobProduction / $jobRunTime;
                                    } elseif($data['machine']->time_uom == 'Hr') {
                                        $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $running_speed) * 100;
                                        $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                    } elseif($data['machine']->time_uom == 'Sec') {
                                        $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $running_speed) * 100;
                                        $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                    }
                                }
                                for($j = 0; $j < count($data['records']); $j++) {
                                    $jobUtilization = 0;
                                    if(isset($records[$i]->trim_width) && isset($records[$i]->slitted_reel_width) && isset($records[$i]->ups) && isset($machine->machine_width) && $jobProduction > 0) {
                                        $jobUtilization = $jobProductionArea / ($jobProduction * $machine->machine_width) * 100;
                                    }

                                    array_push($data['records'][$j], [
                                        "jobProduction" => $jobProduction,
                                        "jobTargetHour" => $jobTargetHour,
                                        "jobUtilization" => $jobUtilization,
                                        "jobGsm" => $jobGsm,
                                        "jobEa" => $jobEa,
                                        "jobPerformance" => $jobPerformance,
                                        "jobRuntime" => $jobRunTime,
                                        "jobAverageSpeed" => $jobAverageSpeed,

                                    ]);
                                }
                                $jobProduction = 0;
                                $jobRunTime = 0;
                                $jobProductionArea = 0;
                                $jobTargetHour = 0;
                                $jobGsm = 0;
                                $jobEa = 0;
                                $startDate = $endDate;
                                $oldLength = $records[$i]->length;

                            }
                        }
                    }
                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    $data['produced'] = $production;
                    $data['oee'] = ($runTime / ($totalTime - $idleTime)) * ($runTime > 0 ? ($targetHour / $runTime) : 0) * 100;
                    $data['ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($runTime > 0 ? ($targetHour / $runTime) : 0) * 100;
                    $data['performance'] = ($runTime > 0 ? ($targetHour / $runTime) : 0) * 100;
                    $data['availability_ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * 100;
                    $data['availability'] = ($runTime / ($totalTime - $idleTime)) * 100;
                    $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
                    $data['run_time'] = $runTime;
                    $data['targetHours'] = $targetHour;
                    $data['utilization'] = isset($data['machine']->machine_width) ? (($rotoeyeNextProduction * $data['machine']->machine_width) > 0 ? ($productionArea / ($rotoeyeNextProduction * $data['machine']->machine_width)) : 0) * 100 : 0;
                    $data['budgetedTime'] = $totalTime - $idleTime;
                    $data['shift'] = $request->input('shiftSelection');
                    $data['date'] = $date;
                    $data['current_time'] = date('Y-m-d H:i:s');
                    //return $data['records'];

                    if(count($data['negativeRecords']) > 0) {

                        //        Mail::send('emails.negative-meters', $data, function ($message) use ($data) {
                        //        $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                        //    $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                        //  ->cc('haroon.naseer@packages.com.pk', 'M Haroon Naseer')
                        //  ->subject("RotoEye Cloud - Negative Meters");
                        //  });

                        Session::flash('success', 'We have resolved some negative meters during this production period. Please run the report again.');
                        $row['view'] = View::make('reports.shift-production-report-next', $data)->render();

                        return Redirect::back();
                    } else {
                        $row['view'] = View::make('reports.shift-production-report-next', $data)->render();
                        return view('reports.shift-production-report-next', $data);
                    }
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }

        } else {
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }

    public function lossesReports(Request $request, $id) {
        //dd($request->all());
        $data['path'] = Route::getFacadeRoot()->current()->uri();
        $user_id = Session::get('user_id');
        if(isset($user_id)) {
            if(Session::get('rights') == 0) {
                $data['layout'] = 'web-layout';
            } elseif(Session::get('rights') == 1) {
                $data['layout'] = 'admin-layout';
            } elseif(Session::get('rights') == 2) {
                $data['layout'] = 'power-user-layout';
            } elseif(Session::get('rights') == 3) {
                $data['layout'] = 'reporting-user-layout';
            }
            $data['machine'] = Machine::find(Crypt::decrypt($id));
            //    mine code
            //dd($request->all());
            $data['errorCodes'] = $data['machine']->section->department->errorCodes;
            $data['errorCategories'] = Categories::all();
            $data['req_losses_from_date'] = $request->losses_from_date;
            $data['req_lossesShiftSelection'] = $request->lossesShiftSelection;
            $data['req_losses_to_date'] = $request->losses_to_date;
            $data['req_lossesReportType'] = $request->lossesReportType;
            $data['req_errorcat'] = $request->errorcat;
            $data['req_error'] = $request->error;
            // end mine code



            // Updated by Abdullah 22-11-23 start
            $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
            // Updated by Abdullah 22-11-23 end


            $loginRecord = LoginRecord::where('machine_id', '=', $data['machine']->id)->get();
            $data['record'] = Record::where('machine_id', '=', $data['machine']->id)->latest('run_date_time')->first();
            $data['from'] = $from_date = date('Y-m-d H:i:s', strtotime($request->input('losses_from_date')));
            $data['to'] = $to_date = date('Y-m-d H:i:s', strtotime($request->input('losses_to_date')));
            $data['shift'] = $shiftSelection = $request->input('lossesShiftSelection');

            if($shiftSelection[0] == 'All-Day') {
                $machine = Machine::find($data['machine']->id);
                $shifts_id = [];
                foreach($machine->section->department->businessUnit->company->shifts as $shift) {
                    array_push($shifts_id, $shift->id);
                }

                $minStarted = Shift::find($shifts_id[0])->min_started;
                $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;

                $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
                $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));
            } else {

                $minStarted = Shift::find($shiftSelection[0])->min_started;
                $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;
                $startDateTime = date('Y-m-d H:i:s', strtotime($from_date.' + '.$minStarted.' minutes'));
                $endDateTime = date('Y-m-d H:i:s', strtotime($from_date.' + '.$minEnded.' minutes'));
            }

            if(date('Y-m-d H:i:s') < $endDateTime) {
                $endDateTime = date('Y-m-d H:i:s');
            }
            //Job Wise Setting Report
            if($request->input('lossesReportType') == 'job-wise-setting') {

                $totalSettingMeters = 0;
                $totalSettingMinutes = 0;
                $totalColors = 0;

                $records = DB::table('records')
                    ->join('errors', 'errors.id', '=', 'records.error_id')
                    ->join('users', 'users.id', '=', 'records.user_id')
                    ->join('jobs', 'jobs.id', '=', 'records.job_id')
                    ->join('products', 'products.id', '=', 'jobs.product_id')
                    ->join('process_structure', function ($join) {
                        $join->on('process_structure.process_id', '=', 'records.process_id');
                        $join->on('process_structure.product_id', '=', 'products.id');
                    })
                    ->join('material_combination', 'material_combination.id', '=', 'process_structure.material_combination_id')
                    ->join('processes', 'processes.id', '=', 'process_structure.process_id')
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'process_structure.color as colors',
                        'process_structure.adhesive as adhesive',
                        'processes.process_name as process_name'
                    )
                    ->where('machine_id', '=', $data['machine']->id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();

                if(count($records) > 0) {
                    $data['records'] = [];
                    $startDate = $records[0]->run_date_time;
                    $oldLength = $records[0]->length;
                    for($i = 0; $i < count($records); $i++) {
                        if(isset($records[$i + 1])) {
                            if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                if($records[$i]->error_name == 'Setting') {
                                    array_push($data['records'], [
                                        "job_id" => $records[$i]->job_id,
                                        "job_name" => $records[$i]->job_name,
                                        "product_number" => $records[$i]->product_number,
                                        "material_combination" => $records[$i]->material_combination,
                                        "user_name" => $records[$i]->user_name,
                                        "length" => $records[$i]->length - $oldLength,
                                        "duration" => $duration,
                                        "colors" => $records[$i]->colors,
                                        "estimated_time" => $startDate,
                                        "process_name" => $records[$i]->process_name,
                                        "adhesive" => $records[$i]->adhesive,
                                    ]);
                                    $totalSettingMinutes += $duration;
                                    $totalSettingMeters += ($records[$i]->length - $oldLength);
                                    $totalColors += $records[$i]->colors;
                                }
                                $startDate = $endDate;
                                $oldLength = $records[$i + 1]->length;
                            }
                        } else {
                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                            if($records[$i]->error_name == 'Setting') {
                                array_push($data['records'], [
                                    "job_id" => $records[$i]->job_id,
                                    "job_name" => $records[$i]->job_name,
                                    "product_number" => $records[$i]->product_number,
                                    "material_combination" => $records[$i]->material_combination,
                                    "user_name" => $records[$i]->user_name,
                                    "length" => $records[$i]->length - $oldLength,
                                    "duration" => $duration,
                                    "colors" => $records[$i]->colors,
                                    "estimated_time" => $startDate,
                                    "process_name" => $records[$i]->process_name,
                                    "adhesive" => $records[$i]->adhesive,

                                ]);
                                $totalSettingMinutes += $duration;
                                $totalSettingMeters += ($records[$i]->length - $oldLength);
                                $totalColors += $records[$i]->colors;
                            }
                            $startDate = $endDate;
                            $oldLength = $records[count($records) - 1]->length;
                        }
                    }
                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    $data['totalSettingMeters'] = $totalSettingMeters;
                    $data['totalSettingMinutes'] = $totalSettingMinutes;
                    $data['totalColors'] = $totalColors;
                    return view('reports.job-wise-setting-report', $data);
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }
            //Performance Loss Report
            elseif($request->input('lossesReportType') == 'performance=loss') {
                $materialProduction = 0;
                $productProduction = 0;
                $materialRunTime = 0;
                $productRunTime = 0;
                $materialLastCounter = 0;
                $productLastCounter = 0;
                $totalProduction = 0;
                $totalRunTime = 0;
                $idleTime = 0;
                $jobWaitTime = 0;

                $data['from'] = $request->input('losses_from_date');
                $data['to'] = $request->input('losses_to_date');

                $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();
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
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'material_combination.id as id',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('machine_id', '=', $data['machine']->id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();
                if(count($records) > 0) {
                    $data['records'] = [];
                    $data['secondRecords'] = [];
                    $startDate = $records[0]->run_date_time;
                    $oldLength = $records[0]->length;
                    $runTime = 0;
                    $production = 0;
                    $jobProduction = 0;
                    $jobRunTime = 0;
                    $totalTime = (strtotime($endDateTime) - strtotime($startDateTime)) / 60;
                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {
                            if(isset($records[$i + 1])) {
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                        }
                                    }
                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            $jobProduction += $records[$i]->length - $oldLength;
                                            $jobRunTime += $duration;
                                            if(!in_array($records[$i]->job_id, array_column($data['records'], 'job_id'))) {
                                                array_push($data['records'], [
                                                    "id" => $records[$i]->id,
                                                    "job_id" => $records[$i]->job_id,
                                                    "job_name" => $records[$i]->job_name,
                                                    "product_number" => $records[$i]->product_number,
                                                    "material_combination" => $records[$i]->material_combination,
                                                    "nominal_speed" => $records[$i]->nominal_speed,
                                                    "user_name" => $records[$i]->user_name,
                                                    "error_id" => $records[$i]->error_id,
                                                    "error_name" => $records[$i]->error_name,
                                                    "comments" => str_replace('#', 'no', $records[$i]->comments),
                                                    "length" => $records[$i]->length - $oldLength,
                                                    "duration" => $duration,
                                                ]);
                                            }
                                        }
                                    }
                                    $startDate = $endDate;
                                    if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                        $oldLength = $records[$i + 1]->length;

                                        for($j = 0; $j < count($data['records']); $j++) {
                                            if(isset($data['records'][$j]['jobProduction'])) {
                                                if($data['records'][$j]['job_id'] == $records[$i]->job_id) {
                                                    $prev_jobProduction = $data['records'][$j]['jobProduction'];
                                                    $prev_jobRunTime = $data['records'][$j]['jobRunTime'];
                                                    Arr::set($data['records'][$j], 'jobProduction', $prev_jobProduction + $jobProduction);
                                                    Arr::set($data['records'][$j], 'jobRunTime', $prev_jobRunTime + $jobRunTime);
                                                }
                                            } else {
                                                Arr::set($data['records'][$j], 'jobProduction', $jobProduction);
                                                Arr::set($data['records'][$j], 'jobRunTime', $jobRunTime);
                                            }
                                        }
                                        $jobProduction = 0;
                                        $jobRunTime = 0;
                                    } else {
                                        $oldLength = $records[$i]->length;
                                    }
                                }
                            } else {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }
                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        $jobProduction += $records[$i]->length - $oldLength;
                                        $jobRunTime += $duration;
                                        if(!in_array($records[$i]->job_id, array_column($data['records'], 'job_id'))) {
                                            array_push($data['records'], [
                                                "id" => $records[$i]->id,
                                                "job_id" => $records[$i]->job_id,
                                                "job_name" => $records[$i]->job_name,
                                                "product_number" => $records[$i]->product_number,
                                                "material_combination" => $records[$i]->material_combination,
                                                "nominal_speed" => $records[$i]->nominal_speed,
                                                "user_name" => $records[$i]->user_name,
                                                "error_id" => $records[$i]->error_id,
                                                "error_name" => $records[$i]->error_name,
                                                "comments" => str_replace('#', 'no', $records[$i]->comments),
                                                "length" => $records[$i]->length - $oldLength,
                                                "duration" => $duration,
                                            ]);
                                        }
                                    }
                                }
                                $startDate = $endDate;
                            }
                        }
                        for($k = 0; $k < count($data['records']); $k++) {
                            if(!isset($data['records'][$k]['jobProduction'])) {
                                Arr::set($data['records'][$k], 'jobProduction', $jobProduction);
                                Arr::set($data['records'][$k], 'jobRunTime', $jobRunTime);
                            }
                        }
                        //dd($data['records']);
                        array_multisort(
                            array_column($data['records'], 'id'),
                            SORT_ASC,
                            array_column($data['records'], 'product_number'),
                            SORT_ASC,
                            array_column($data['records'], 'job_id'),
                            SORT_ASC,
                            $data['records']
                        );

                        for($i = 0; $i < count($data['records']); $i++) {
                            $totalProduction += $data['records'][$i]['jobProduction'];
                            $totalRunTime += $data['records'][$i]['jobRunTime'];
                            if(isset($data['records'][$i + 1])) {
                                $materialProduction += $data['records'][$i]['jobProduction'];
                                $materialRunTime += $data['records'][$i]['jobRunTime'];
                                $productProduction += $data['records'][$i]['jobProduction'];
                                $productRunTime += $data['records'][$i]['jobRunTime'];
                                if($data['records'][$i]['id'] != $data['records'][$i + 1]['id']) {
                                    for($j = $i; $j >= $materialLastCounter; $j--) {
                                        if($data['records'][$j]['id'] == $data['records'][$i]['id']) {
                                            Arr::set($data['records'][$j], 'materialProduction', $materialProduction);
                                            Arr::set($data['records'][$j], 'materialRunTime', $materialRunTime);
                                        }
                                    }
                                    $materialLastCounter = $i;
                                    $materialProduction = 0;
                                    $materialRunTime = 0;
                                }
                                if($data['records'][$i]['product_number'] != $data['records'][$i + 1]['product_number']) {
                                    for($j = $i; $j >= $productLastCounter; $j--) {
                                        if($data['records'][$j]['product_number'] == $data['records'][$i]['product_number']) {
                                            Arr::set($data['records'][$j], 'productProduction', $productProduction);
                                            Arr::set($data['records'][$j], 'productRunTime', $productRunTime);
                                        }
                                    }
                                    $productLastCounter = $i;
                                    $productProduction = 0;
                                    $productRunTime = 0;
                                }
                            } else {
                                $materialProduction += $data['records'][$i]['jobProduction'];
                                $materialRunTime += $data['records'][$i]['jobRunTime'];
                                $productProduction += $data['records'][$i]['jobProduction'];
                                $productRunTime += $data['records'][$i]['jobRunTime'];
                                for($j = $i; $j >= $productLastCounter; $j--) {
                                    if($data['records'][$j]['product_number'] == $data['records'][$i]['product_number']) {
                                        Arr::set($data['records'][$j], 'productProduction', $productProduction);
                                        Arr::set($data['records'][$j], 'productRunTime', $productRunTime);
                                    }
                                }
                                for($j = $i; $j >= $materialLastCounter; $j--) {
                                    if($data['records'][$j]['id'] == $data['records'][$i]['id']) {
                                        Arr::set($data['records'][$j], 'materialProduction', $materialProduction);
                                        Arr::set($data['records'][$j], 'materialRunTime', $materialRunTime);
                                    }
                                }
                            }
                        }

                        if($runTime > 0) {
                            if($data['machine']->time_uom == 'Hr') {
                                $actualSpeed = $production / $runTime * 60;
                            } elseif($data['machine']->time_uom == 'Min') {
                                $actualSpeed = $production / $runTime;
                            } else {
                                $actualSpeed = $production / $runTime / 60;
                            }
                        } else {
                            $actualSpeed = 0;
                        }
                    }

                    //  dd($runTime,$totalTime,$idleTime,$actualSpeed);

                    $data['totalProduction'] = $production;
                    $data['totalRunTime'] = $runTime;
                    $data['jobWaitTime'] = $jobWaitTime;
                    $data['idleTime'] = $idleTime;
                    $data['oee'] = ($runTime / ($totalTime - $idleTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;
                    $data['ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($actualSpeed / $data['machine']->max_speed) * 100;

                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    return view('reports.performance-loss-analysis', $data);
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }
            //Availability Losses Report
            elseif($request->input('lossesReportType') == 'availability-losses') {
                // dd($request->all());
                $data['report_type'] = $request->report_type;
                $cat = Categories::find($request->errorcat);
                $errorIds = isset($cat) ? $cat->errorcatCodes->pluck('id')->toArray() : null;


                $records = DB::table('records')
                    ->join('errors', 'errors.id', '=', 'records.error_id')
                    // ->join('machines', 'machines.downtime_error', '!=', 'records.error_id')
                    ->select('errors.id as error_id', 'errors.name as error_name', 'records.run_date_time as run_date_time', 'records.job_id as job_id', 'records.user_id as user_id')
                    ->where('machine_id', '=', $data['machine']->id)
                    ->where('run_date_time', '>=', $startDateTime)
                    ->where('run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();


                if(count($records) > 0) {
                    $data['records'] = [];
                    $data['graphRecords'] = [];
                    $startDate = $records[0]->run_date_time;
                    for($i = 0; $i < count($records); $i++) {
                        if(isset($records[$i + 1])) {
                            if($records[$i]->error_id != $records[$i + 1]->error_id) {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = ((($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60);

                                if($records[$i]->error_id != 2) {
                                    if(isset($errorIds)) {
                                        if(in_array($records[$i]->error_id, $errorIds)) {
                                            // if($data['machine']->downtime_error != $records[$i]->error_id){
                                            array_push($data['records'], [
                                                "from" => $startDate,
                                                "to" => $endDate,
                                                "error_id" => $records[$i]->error_id,
                                                "error_name" => $records[$i]->error_name,
                                                "duration" => $duration,
                                            ]);
                                            // }
                                        }

                                    } else {
                                        // if($data['machine']->downtime_error != $records[$i]->error_id){
                                        array_push($data['records'], [
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "error_id" => $records[$i]->error_id,
                                            "error_name" => $records[$i]->error_name,
                                            "duration" => $duration,
                                        ]);
                                        // }
                                    }

                                }
                                $startDate = $endDate;
                            }
                        } else {

                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = ((($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60);
                            if($records[$i]->error_id != 2) {
                                if(isset($errorIds)) {
                                    if(in_array($records[$i]->error_id, $errorIds)) {
                                        //if($data['machine']->downtime_error != $records[$i]->error_id){
                                        array_push($data['records'], [
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "error_id" => $records[$i]->error_id,
                                            "error_name" => $records[$i]->error_name,
                                            "duration" => $duration,
                                        ]);
                                        //}
                                    }

                                } else {
                                    //if($data['machine']->downtime_error != $records[$i]->error_id){
                                    array_push($data['records'], [
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "duration" => $duration,
                                    ]);
                                    //}
                                }
                            }
                            $startDate = $endDate;
                        }
                    }

                    $count = 0;
                    $alreadyDone = [];
                    $duration = 0;
                    for($i = 0; $i < count($data['records']); $i++) {
                        if(!in_array($data['records'][$i]['error_id'], $alreadyDone)) {

                            for($j = 0; $j < count($data['records']); $j++) {
                                if($data['records'][$i]['error_id'] == $data['records'][$j]['error_id']) {
                                    $count++;
                                    $duration += $data['records'][$j]['duration'];
                                }
                            }
                            array_push($data['graphRecords'], [
                                "error_id" => $data['records'][$i]['error_id'],
                                "error_name" => $data['records'][$i]['error_name'],
                                "errDuration" => $duration,
                                "frequency" => $count
                            ]);
                            array_push($alreadyDone, $data['records'][$i]['error_id']);
                            $count = 0;
                            $duration = 0;

                        }
                    }

                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    array_multisort(array_column($data['graphRecords'], 'errDuration'), SORT_DESC, $data['graphRecords']);
                    return view('reports.availability-loss-report', $data);
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }
            //temp for bsp dashboard
            elseif($request->input('lossesReportType') == 'availability-losses-2') {
                $records = DB::table('records')
                    ->join('errors', 'errors.id', '=', 'records.error_id')
                    ->select('errors.id as error_id', 'errors.name as error_name', 'records.run_date_time as run_date_time', 'records.job_id as job_id', 'records.user_id as user_id', 'records.length as length')
                    ->where('machine_id', '=', $data['machine']->id)
                    ->where('run_date_time', '>=', $startDateTime)
                    ->where('run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();
                if(count($records) > 0) {
                    $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                    $data['records'] = [];
                    $data['graphRecords'] = [];
                    $startDate = $records[0]->run_date_time;
                    $oldLength = $records[0]->length;
                    $runTime = 0;
                    $production = 0;
                    for($i = 0; $i < count($records); $i++) {
                        if(isset($records[$i + 1])) {
                            if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;

                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                    }
                                }

                                array_push($data['records'], [
                                    "from" => $startDate,
                                    "to" => $endDate,
                                    "error_id" => $records[$i]->error_id,
                                    "error_name" => $records[$i]->error_name,
                                    "duration" => $duration,
                                    "length" => $records[$i]->length - $oldLength,
                                    "job_id" => $records[$i]->job_id

                                ]);
                                $startDate = $endDate;
                                if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $oldLength = $records[$i + 1]->length;
                                } else {
                                    $oldLength = $records[$i]->length;
                                }

                            }
                        } else {
                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;

                            foreach($runningCodes as $runningCode) {
                                if($runningCode->id == $records[$i]->error_id) {
                                    $runTime += $duration;
                                    $production += $records[$i]->length - $oldLength;
                                }
                            }

                            array_push($data['records'], [
                                "from" => $startDate,
                                "to" => $endDate,
                                "error_id" => $records[$i]->error_id,
                                "error_name" => $records[$i]->error_name,
                                "duration" => $duration,
                                "length" => $records[$i]->length - $oldLength,
                                "job_id" => $records[$i]->job_id,
                            ]);
                            $startDate = $endDate;
                            $oldLength = $records[$i]->length;
                        }
                    }


                    $count = 0;
                    $alreadyDone = [];
                    $duration = 0;
                    $production = 0;
                    for($i = 0; $i < count($data['records']); $i++) {
                        if(!in_array($data['records'][$i]['error_id'], $alreadyDone)) {
                            for($j = 0; $j < count($data['records']); $j++) {
                                if($data['records'][$i]['error_id'] == $data['records'][$j]['error_id']) {
                                    if($data['records'][$i]['job_id'] == $data['records'][$j]['job_id']) {
                                        $count++;
                                    }

                                    $duration += $data['records'][$j]['duration'];
                                    $production += $data['records'][$j]['length'];
                                }
                            }
                            array_push($data['graphRecords'], [
                                "error_id" => $data['records'][$i]['error_id'],
                                "error_name" => $data['records'][$i]['error_name'],
                                "errDuration" => $duration,
                                "errProduction" => $production,
                                "frequency" => $count
                            ]);
                            array_push($alreadyDone, $data['records'][$i]['error_id']);
                            $count = 0;
                            $duration = 0;
                            $production = 0;
                        }
                    }

                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    array_multisort(array_column($data['graphRecords'], 'errDuration'), SORT_DESC, $data['graphRecords']);
                    return view('reports.availability-loss-report-2', $data);
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }
            //Error History Report
            elseif($request->input('lossesReportType') == 'error-history') {
                $error_id = $request->input('error');

                $records = DB::table('records')
                    ->join('errors', 'errors.id', '=', 'records.error_id')
                    ->join('users', 'users.id', '=', 'records.user_id')
                    ->join('jobs', 'jobs.id', '=', 'records.job_id')
                    ->join('products', 'products.id', '=', 'jobs.product_id')
                    ->select(
                        'errors.id as error_id',
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.err_comments as err_comments',
                        'products.name as product_name',
                        'products.id as product_id',
                        'users.name as user_name',
                        'users.id as user_id',
                        'jobs.id as job_id'
                    )
                    ->where('machine_id', '=', $data['machine']->id)
                    ->where('run_date_time', '>=', $startDateTime)
                    ->where('run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();


                if(count($records) > 0) {
                    $data['records'] = [];
                    $startDate = $records[0]->run_date_time;
                    for($i = 0; $i < count($records); $i++) {
                        if(isset($records[$i + 1])) {
                            if($records[$i]->error_id != $records[$i + 1]->error_id) {

                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                if($records[$i]->error_id == $error_id) {
                                    array_push($data['records'], [
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "error_id" => $records[$i]->error_id,
                                        "job_id" => $records[$i]->job_id,
                                        "product_id" => $records[$i]->product_id,
                                        "product_name" => $records[$i]->product_name,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "error_name" => $records[$i]->error_name,
                                        "duration" => $duration,
                                        "err_comments" => str_replace('#', 'no', $records[$i]->err_comments),
                                    ]);
                                }
                                // $startDate = $records[$i + 1]->run_date_time;
                                $startDate = $endDate;
                            }
                        } else {

                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                            if($records[$i]->error_id == $error_id) {

                                array_push($data['records'], [
                                    "from" => $startDate,
                                    "to" => $endDate,
                                    "error_id" => $records[$i]->error_id,
                                    "job_id" => $records[$i]->job_id,
                                    "product_id" => $records[$i]->product_id,
                                    "product_name" => $records[$i]->product_name,
                                    "user_id" => $records[$i]->user_id,
                                    "user_name" => $records[$i]->user_name,
                                    "error_name" => $records[$i]->error_name,
                                    "duration" => $duration,
                                    "err_comments" => $records[$i]->err_comments
                                ]);
                            }
                            $startDate = $endDate;
                        }
                    }
                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    $data['error'] = Error::find($request->input('error'));
                    return view('reports.error-history-report', $data);
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }
            //Error History Report Detailed
            // Commented from the front end instructed by Haroon Sab due to error in report and urgence issue
            elseif($request->input('lossesReportType') == 'detailed-error-history') {
                $records = DB::table('records')
                    ->join('errors', 'errors.id', '=', 'records.error_id')
                    ->join('users', 'users.id', '=', 'records.user_id')
                    ->join('jobs', 'jobs.id', '=', 'records.job_id')
                    ->join('products', 'products.id', '=', 'jobs.product_id')
                    ->select(
                        'errors.id as error_id',
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.length as length',
                        'records.err_comments as err_comments',
                        'products.name as product_name',
                        'products.id as product_id',
                        'users.name as user_name',
                        'users.id as user_id',
                        'jobs.id as job_id'
                    )
                    ->where('machine_id', '=', $data['machine']->id)
                    ->where('run_date_time', '>=', $startDateTime)
                    ->where('run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();
                if(count($records) > 0) {
                    $data['records'] = [];
                    $startDate = $records[0]->run_date_time;
                    $oldLength = $records[0]->length;
                    for($i = 0; $i < count($records); $i++) {
                        if(isset($records[$i + 1])) {
                            if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;

                                //   if($records[$i]->length  - $oldLength < 0){
                                //
                                //                                    }
                                if($records[$i]->error_id == $request->input('error')) {
                                    array_push($data['records'], [
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "error_id" => $records[$i]->error_id,
                                        "job_id" => $records[$i]->job_id,
                                        "product_id" => $records[$i]->product_id,
                                        "product_name" => $records[$i]->product_name,
                                        "user_id" => $records[$i]->user_id,
                                        "user_name" => $records[$i]->user_name,
                                        "error_name" => $records[$i]->error_name,
                                        "length" => $records[$i]->length - $oldLength,
                                        "duration" => $duration,
                                        "err_comments" => str_replace('#', 'no', $records[$i]->err_comments),
                                        "machine" => $data['machine']
                                    ]);
                                }
                                $startDate = $endDate;
                                if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $oldLength = $records[$i + 1]->length;
                                } else {
                                    $oldLength = $records[$i]->length;
                                }

                            }
                        } else {
                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;

                            //    if($records[$i]->length  - $oldLength < 0){
                            //
                            //                                }

                            if($records[$i]->error_id == $request->input('error')) {
                                array_push($data['records'], [
                                    "from" => $startDate,
                                    "to" => $endDate,
                                    "error_id" => $records[$i]->error_id,
                                    "job_id" => $records[$i]->job_id,
                                    "product_id" => $records[$i]->product_id,
                                    "product_name" => $records[$i]->product_name,
                                    "user_id" => $records[$i]->user_id,
                                    "user_name" => $records[$i]->user_name,
                                    "error_name" => $records[$i]->error_name,
                                    "length" => $records[$i]->length - $oldLength,
                                    "duration" => $duration,
                                    "err_comments" => str_replace('#', 'no', $records[$i]->err_comments),
                                    "machine" => $data['machine']
                                ]);
                            }
                            $startDate = $endDate;
                            $oldLength = $records[$i]->length;
                        }
                    }

                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    $data['error'] = Error::find($request->input('error'));
                    //dd($data);
                    return view('reports.detailed-error-history-report', $data);
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }
            //Performance Loss Next Report
            elseif($request->input('lossesReportType') == 'performance-loss-next') {
                $materialProduction = 0;
                $productProduction = 0;
                $materialRunTime = 0;
                $materialTargetHour = 0;
                $productRunTime = 0;
                $materialLastCounter = 0;
                $productLastCounter = 0;
                $totalProduction = 0;
                $totalTargetHour = 0;
                $totalRunTime = 0;
                $idleTime = 0;
                $jobWaitTime = 0;

                $data['from'] = $request->input('losses_from_date');
                $data['to'] = $request->input('losses_to_date');

                $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
                $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();
                $records = DB::table('records')
                    ->leftJoin('errors', 'errors.id', '=', 'records.error_id')
                    ->leftJoin('users', 'users.id', '=', 'records.user_id')
                    ->leftJoin('jobs', 'jobs.id', '=', 'records.job_id')
                    ->leftJoin('products', 'products.id', '=', 'jobs.product_id')
                    ->leftJoin('product_sleeve', 'product_sleeve.product_id', '=', 'products.id')
                    ->leftJoin('sleeves', 'sleeves.id', '=', 'product_sleeve.sleeve_id')
                    ->leftJoin('process_structure', function ($join) {
                        $join->on('process_structure.process_id', '=', 'records.process_id');
                        $join->on('process_structure.product_id', '=', 'products.id');
                    })
                    ->leftJoin('machine_sleeve', function ($join) {
                        $join->on('machine_sleeve.sleeve_id', '=', 'sleeves.id');
                        $join->on('machine_sleeve.machine_id', '=', 'records.machine_id');
                    })
                    ->leftJoin('material_combination', 'material_combination.id', '=', 'process_structure.material_combination_id')
                    ->leftJoin('processes', 'processes.id', '=', 'process_structure.process_id')
                    ->select(
                        'errors.name as error_name',
                        'records.run_date_time as run_date_time',
                        'records.error_id as error_id',
                        'records.length as length',
                        'records.err_comments as comments',
                        'machine_sleeve.speed as sleeve_speed',
                        'sleeves.circumference as sleeve_circumference',
                        'jobs.id as job_id',
                        'products.name as job_name',
                        'jobs.job_length as job_length',
                        'products.name as product_name',
                        'products.id as product_number',
                        'material_combination.name as material_combination',
                        'material_combination.id as id',
                        'material_combination.nominal_speed as nominal_speed',
                        'records.user_id as user_id',
                        'users.name as user_name',
                        'processes.process_name as process_name'
                    )
                    ->where('records.machine_id', '=', $data['machine']->id)
                    ->where('records.run_date_time', '>=', $startDateTime)
                    ->where('records.run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();


                if(count($records) > 0) {
                    $data['records'] = [];
                    $data['secondRecords'] = [];
                    $startDate = $records[0]->run_date_time;
                    $oldLength = $records[0]->length;
                    $runTime = 0;
                    $production = 0;
                    $jobProduction = 0;
                    $jobRunTime = 0;
                    $targetHour = 0;
                    $jobTargetHour = 0;
                    $totalTime = (strtotime($endDateTime) - strtotime($startDateTime)) / 60;
                    if(count($records) > 1) {
                        for($i = 0; $i < count($records); $i++) {
                            $running_speed = isset($records[$i]->sleeve_speed) ? $records[$i]->sleeve_speed : $data['machine']->max_speed;
                            if(isset($records[$i + 1])) {
                                if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                                    $endDate = $records[$i]->run_date_time;
                                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                    foreach($idleErrors as $idleError) {
                                        if($idleError->id == $records[$i]->error_id) {
                                            $idleTime += $duration;
                                        }
                                    }
                                    foreach($jobWaitingCodes as $jobWaitingCode) {
                                        if($jobWaitingCode->id == $records[$i]->error_id) {
                                            $jobWaitTime += $duration;
                                        }
                                    }
                                    foreach($runningCodes as $runningCode) {
                                        if($runningCode->id == $records[$i]->error_id) {
                                            $runTime += $duration;
                                            $production += $records[$i]->length - $oldLength;
                                            $jobProduction += $records[$i]->length - $oldLength;
                                            $jobRunTime += $duration;
                                            if($data['machine']->time_uom == 'Min') {
                                                $targetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                                $jobTargetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                            } elseif($data['machine']->time_uom == 'Hr') {
                                                $targetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;
                                                $jobTargetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;

                                            } elseif($data['machine']->time_uom == 'Sec') {
                                                $targetHour += ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                                $jobTargetHour += ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                            }

                                            if(!in_array($records[$i]->job_id, array_column($data['records'], 'job_id'))) {
                                                array_push($data['records'], [
                                                    "id" => $records[$i]->id,
                                                    "job_id" => $records[$i]->job_id,
                                                    "job_name" => $records[$i]->job_name,
                                                    "product_number" => $records[$i]->product_number,
                                                    "material_combination" => $records[$i]->material_combination,
                                                    "nominal_speed" => $records[$i]->nominal_speed,
                                                    "user_name" => $records[$i]->user_name,
                                                    "error_id" => $records[$i]->error_id,
                                                    "error_name" => $records[$i]->error_name,
                                                    "comments" => str_replace('#', 'no', $records[$i]->comments),
                                                    "length" => $records[$i]->length - $oldLength,
                                                    "duration" => $duration,
                                                    "product_sleeve_speed" => $records[$i]->sleeve_speed,
                                                    "product_sleeve_circumference" => $records[$i]->sleeve_circumference,
                                                ]);
                                            }
                                        }
                                    }
                                    $startDate = $endDate;
                                    if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                        $oldLength = $records[$i + 1]->length;

                                        for($j = 0; $j < count($data['records']); $j++) {
                                            if(isset($data['records'][$j]['jobProduction'])) {
                                                if($data['records'][$j]['job_id'] == $records[$i]->job_id) {
                                                    $prev_jobProduction = $data['records'][$j]['jobProduction'];
                                                    $prev_jobRunTime = $data['records'][$j]['jobRunTime'];
                                                    $prev_jobTargetHour = $data['records'][$j]['jobTargetHour'];
                                                    Arr::set($data['records'][$j], 'jobProduction', $prev_jobProduction + $jobProduction);
                                                    Arr::set($data['records'][$j], 'jobRunTime', $prev_jobRunTime + $jobRunTime);
                                                    Arr::set($data['records'][$j], 'jobTargetHour', $prev_jobTargetHour + $jobTargetHour);
                                                }
                                            } else {
                                                Arr::set($data['records'][$j], 'jobProduction', $jobProduction);
                                                Arr::set($data['records'][$j], 'jobRunTime', $jobRunTime);
                                                Arr::set($data['records'][$j], 'jobTargetHour', $jobTargetHour);
                                            }
                                        }
                                        $jobProduction = 0;
                                        $jobRunTime = 0;
                                        $jobTargetHour = 0;
                                    } else {
                                        $oldLength = $records[$i]->length;
                                    }
                                }
                            } else {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                    }
                                }
                                foreach($jobWaitingCodes as $jobWaitingCode) {
                                    if($jobWaitingCode->id == $records[$i]->error_id) {
                                        $jobWaitTime += $duration;
                                    }
                                }
                                foreach($runningCodes as $runningCode) {
                                    if($runningCode->id == $records[$i]->error_id) {
                                        $runTime += $duration;
                                        $production += $records[$i]->length - $oldLength;
                                        $jobProduction += $records[$i]->length - $oldLength;
                                        $jobRunTime += $duration;
                                        if($data['machine']->time_uom == 'Min') {
                                            $targetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                            $jobTargetHour += ($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0;
                                        } elseif($data['machine']->time_uom == 'Hr') {
                                            $targetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;
                                            $jobTargetHour += (($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) * 60;

                                        } elseif($data['machine']->time_uom == 'Sec') {
                                            $targetHour += ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                            $jobTargetHour += ((($records[$i]->length - $oldLength) != 0 ? ($records[$i]->length - $oldLength) / $running_speed : 0) / 60);
                                        }
                                        if(!in_array($records[$i]->job_id, array_column($data['records'], 'job_id'))) {
                                            array_push($data['records'], [
                                                "id" => $records[$i]->id,
                                                "job_id" => $records[$i]->job_id,
                                                "job_name" => $records[$i]->job_name,
                                                "product_number" => $records[$i]->product_number,
                                                "material_combination" => $records[$i]->material_combination,
                                                "nominal_speed" => $records[$i]->nominal_speed,
                                                "user_name" => $records[$i]->user_name,
                                                "error_id" => $records[$i]->error_id,
                                                "error_name" => $records[$i]->error_name,
                                                "comments" => str_replace('#', 'no', $records[$i]->comments),
                                                "length" => $records[$i]->length - $oldLength,
                                                "duration" => $duration,
                                                "product_sleeve_speed" => $records[$i]->sleeve_speed,
                                                "product_sleeve_circumference" => $records[$i]->sleeve_circumference,
                                            ]);
                                        }
                                    }
                                }
                                $startDate = $endDate;
                            }
                        }
                        for($k = 0; $k < count($data['records']); $k++) {
                            if(!isset($data['records'][$k]['jobProduction'])) {
                                Arr::set($data['records'][$k], 'jobProduction', $jobProduction);
                                Arr::set($data['records'][$k], 'jobRunTime', $jobRunTime);
                                Arr::set($data['records'][$k], 'jobTargetHour', $jobTargetHour);
                            }
                        }
                        array_multisort(
                            array_column($data['records'], 'id'),
                            SORT_ASC,
                            array_column($data['records'], 'product_number'),
                            SORT_ASC,
                            array_column($data['records'], 'job_id'),
                            SORT_ASC,
                            $data['records']
                        );

                        for($i = 0; $i < count($data['records']); $i++) {
                            $totalProduction += $data['records'][$i]['jobProduction'];
                            $totalRunTime += $data['records'][$i]['jobRunTime'];
                            $totalTargetHour += $data['records'][$i]['jobTargetHour'];
                            if(isset($data['records'][$i + 1])) {
                                $materialProduction += $data['records'][$i]['jobProduction'];
                                $materialRunTime += $data['records'][$i]['jobRunTime'];
                                $materialTargetHour += $data['records'][$i]['jobTargetHour'];
                                $productProduction += $data['records'][$i]['jobProduction'];
                                $productRunTime += $data['records'][$i]['jobRunTime'];
                                if($data['records'][$i]['id'] != $data['records'][$i + 1]['id']) {
                                    for($j = $i; $j >= $materialLastCounter; $j--) {
                                        if($data['records'][$j]['id'] == $data['records'][$i]['id']) {
                                            Arr::set($data['records'][$j], 'materialProduction', $materialProduction);
                                            Arr::set($data['records'][$j], 'materialRunTime', $materialRunTime);
                                            Arr::set($data['records'][$j], 'materialTargetHour', $materialTargetHour);
                                        }
                                    }
                                    $materialLastCounter = $i;
                                    $materialProduction = 0;
                                    $materialRunTime = 0;
                                    $materialTargetHour = 0;
                                }
                                if($data['records'][$i]['product_number'] != $data['records'][$i + 1]['product_number']) {
                                    for($j = $i; $j >= $productLastCounter; $j--) {
                                        if($data['records'][$j]['product_number'] == $data['records'][$i]['product_number']) {
                                            Arr::set($data['records'][$j], 'productProduction', $productProduction);
                                            Arr::set($data['records'][$j], 'productRunTime', $productRunTime);
                                        }
                                    }
                                    $productLastCounter = $i;
                                    $productProduction = 0;
                                    $productRunTime = 0;
                                }
                            } else {
                                $materialProduction += $data['records'][$i]['jobProduction'];
                                $materialRunTime += $data['records'][$i]['jobRunTime'];
                                $materialTargetHour += $data['records'][$i]['jobTargetHour'];
                                $productProduction += $data['records'][$i]['jobProduction'];
                                $productRunTime += $data['records'][$i]['jobRunTime'];
                                for($j = $i; $j >= $productLastCounter; $j--) {
                                    if($data['records'][$j]['product_number'] == $data['records'][$i]['product_number']) {
                                        Arr::set($data['records'][$j], 'productProduction', $productProduction);
                                        Arr::set($data['records'][$j], 'productRunTime', $productRunTime);
                                    }
                                }
                                for($j = $i; $j >= $materialLastCounter; $j--) {
                                    if($data['records'][$j]['id'] == $data['records'][$i]['id']) {
                                        Arr::set($data['records'][$j], 'materialProduction', $materialProduction);
                                        Arr::set($data['records'][$j], 'materialRunTime', $materialRunTime);
                                        Arr::set($data['records'][$j], 'materialTargetHour', $materialTargetHour);
                                    }
                                }
                            }
                        }

                        if($runTime > 0) {
                            if($data['machine']->time_uom == 'Hr') {
                                $actualSpeed = $production / $runTime * 60;
                            } elseif($data['machine']->time_uom == 'Min') {
                                $actualSpeed = $production / $runTime;
                            } else {
                                $actualSpeed = $production / $runTime / 60;
                            }
                        } else {
                            $actualSpeed = 0;
                        }
                    }


                    $data['totalProduction'] = $production;
                    $data['totalRunTime'] = $runTime;
                    $data['jobWaitTime'] = $jobWaitTime;
                    $data['idleTime'] = $idleTime;
                    $data['totalTargetHour'] = $targetHour;
                    //    $data['oee'] = ($runTime/($totalTime - $idleTime))*($actualSpeed/$data['machine']->max_speed)*100;
                    //       $data['ee'] = ($runTime/($totalTime - $idleTime - $jobWaitTime))*($actualSpeed/$data['machine']->max_speed)*100;
                    $data['oee'] = ($runTime / ($totalTime - $idleTime)) * ($runTime > 0 ? ($targetHour / $runTime) : 0) * 100;
                    $data['ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($runTime > 0 ? ($targetHour / $runTime) : 0) * 100;

                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }
                    return view('reports.performance-loss-analysis-next', $data);
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }
            }

            // Updated by Abdullah 21-11-23 start
            elseif($request->input('lossesReportType') == 'availability-losses-toe') {
                // dd($request->all());
                $data['report_type'] = $request->report_type;
                $cat = Categories::find($request->errorcat);
                $errorIds = isset($cat) ? $cat->errorcatCodes->pluck('id')->toArray() : null;

                $records = DB::table('records')
                    ->join('errors', 'errors.id', '=', 'records.error_id')
                    ->select('errors.id as error_id', 'errors.name as error_name', 'errors.toe_category as error_toe_category', 'records.run_date_time as run_date_time', 'records.user_id as user_id')
                    ->where('machine_id', '=', $data['machine']->id)
                    ->where('run_date_time', '>=', $startDateTime)
                    ->where('run_date_time', '<=', $endDateTime)
                    ->orderby('run_date_time', 'ASC')
                    ->get();

                // dd($records);

                if(count($records) > 0) {
                    $data['records'] = [];
                    $data['graphRecords'] = [];
                    $startDate = $records[0]->run_date_time;

                    // Updated by Abdullah 23-11-23 start
                    $totalStrategiclosses = 0;
                    $totalPlannedlosses = 0;
                    $totalOperationallosses = 0;
                    $idleTime = 0;
                    $totalTime = (strtotime($endDateTime) - strtotime($startDateTime)) / 60;
                    $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
                    // Updated by Abdullah 23-11-23 end


                    for($i = 0; $i < count($records); $i++) {
                        if(isset($records[$i + 1])) {

                            if($records[$i]->error_id != $records[$i + 1]->error_id) {
                                $endDate = $records[$i]->run_date_time;
                                $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                                $duration = ((($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60);

                                //   updated by Abdullah 23-11-23 start
                                foreach($idleErrors as $idleError) {
                                    if($idleError->id == $records[$i]->error_id) {
                                        $idleTime += $duration;
                                        // $idltime = $duration;
                                    }
                                }
                                //   updated by Abdullah 23-11-23 end .
                                if($records[$i]->error_id != 2) {
                                    if(isset($errorIds)) {
                                        if(in_array($records[$i]->error_id, $errorIds)) {
                                            array_push($data['records'], [
                                                "from" => $startDate,
                                                "to" => $endDate,
                                                "error_id" => $records[$i]->error_id,
                                                "error_name" => $records[$i]->error_name,
                                                "error_toe_category" => $records[$i]->error_toe_category,
                                                "duration" => $duration,
                                            ]);
                                        }
                                    } else {
                                        array_push($data['records'], [
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "error_id" => $records[$i]->error_id,
                                            "error_name" => $records[$i]->error_name,
                                            "error_toe_category" => $records[$i]->error_toe_category,
                                            "duration" => $duration,

                                        ]);
                                    }
                                }
                                $startDate = $endDate;
                            }
                        } else {

                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = ((($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60);

                            //   Updated by Abdullah 23-11-23 start
                            foreach($idleErrors as $idleError) {
                                if($idleError->id == $records[$i]->error_id) {
                                    $idleTime += $duration;
                                    // $idltime = $duration;
                                }
                            }

                            //   Updated by Abdullah 23-11-23 end
                            if($records[$i]->error_id != 2) {
                                if(isset($errorIds)) {
                                    if(in_array($records[$i]->error_id, $errorIds)) {

                                        array_push($data['records'], [
                                            "from" => $startDate,
                                            "to" => $endDate,
                                            "error_id" => $records[$i]->error_id,
                                            "error_name" => $records[$i]->error_name,
                                            "error_toe_category" => $records[$i]->error_toe_category,
                                            "duration" => $duration,
                                        ]);
                                    }

                                } else {
                                    array_push($data['records'], [
                                        "from" => $startDate,
                                        "to" => $endDate,
                                        "error_id" => $records[$i]->error_id,
                                        "error_name" => $records[$i]->error_name,
                                        "error_toe_category" => $records[$i]->error_toe_category,
                                        "duration" => $duration,
                                    ]);
                                }
                            }
                            $startDate = $endDate;
                        }
                    }
                    $count = 0;
                    $alreadyDone = [];
                    $duration = 0;
                    for($i = 0; $i < count($data['records']); $i++) {
                        if(!in_array($data['records'][$i]['error_id'], $alreadyDone)) {

                            for($j = 0; $j < count($data['records']); $j++) {
                                if($data['records'][$i]['error_id'] == $data['records'][$j]['error_id']) {
                                    $count++;
                                    $duration += $data['records'][$j]['duration'];
                                }
                            }
                            array_push($data['graphRecords'], [
                                "error_id" => $data['records'][$i]['error_id'],
                                "error_name" => $data['records'][$i]['error_name'],
                                // "error_name" => $data['records'][$i]['error_name'],
                                "error_toe_category" => $data['records'][$i]['error_toe_category'],
                                "errDuration" => $duration,
                                "frequency" => $count,
                            ]);
                            array_push($alreadyDone, $data['records'][$i]['error_id']);
                            $count = 0;
                            $duration = 0;
                        }
                    }
                    //   Updated by Abdullah 23-11-23 start
                    // $data['idleTime'] = $idleTime;
                    $data['budgetedTime'] = $totalTime - $idleTime;
                    // dd($data['budgetedTime']);
                    //   Updated by Abdullah 23-11-23 end

                    if(Session::get('rights') == 0) {
                        $data['user'] = Users::find($loginRecord[0]->user_id);
                    } else {
                        $data['user'] = Users::find(Session::get('user_name'));
                    }

                    array_multisort(array_column($data['graphRecords'], 'errDuration'), SORT_DESC, $data['graphRecords']);
                    return view('reports.availability-loss-report-toe', $data);
                } else {
                    Session::flash("error", "No Record for the selected shift and date. Please try again.");
                    return Redirect::back();
                }

            }
            // Updated by Abdullah 21-11-23 end
        } else {
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }

    public function jobPerformance($machine_id, $from, $to, $job_id, $shift) {
        $data['from'] = $from;
        $data['to'] = $to;
        $data['shift'] = $shiftSelection = $shift = unserialize($shift);
        if($shiftSelection[0] == 'All-Day') {
            $machine = Machine::find($data['machine']->id);
            $shifts_id = [];
            foreach($machine->section->department->businessUnit->company->shifts as $shift) {
                array_push($shifts_id, $shift->id);
            }

            $minStarted = Shift::find($shifts_id[0])->min_started;
            $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;

            $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));

        } else {

            $minStarted = Shift::find($shiftSelection[0])->min_started;
            $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;
            $startDateTime = date('Y-m-d H:i:s', strtotime($from.' + '.$minStarted.' minutes'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($from.' + '.$minEnded.' minutes'));
        }
        $records = DB::table('records')
            ->join('errors', 'errors.id', '=', 'records.error_id')
            ->join('users', 'users.id', '=', 'records.user_id')
            ->join('jobs', 'jobs.id', '=', 'records.job_id')
            ->join('products', 'products.id', '=', 'jobs.product_id')
            ->join('process_structure', function ($join) {
                $join->on('process_structure.process_id', '=', 'records.process_id');
                $join->on('process_structure.product_id', '=', 'products.id');
            })
            ->join('material_combination', 'material_combination.id', '=', 'process_structure.material_combination_id')
            ->join('processes', 'processes.id', '=', 'process_structure.process_id')
            ->select(
                'errors.name as error_name',
                'records.run_date_time as run_date_time',
                'records.error_id as error_id',
                'records.length as length',
                'records.err_comments as comments',
                'jobs.id as job_id',
                'products.name as job_name',
                'jobs.job_length as job_length',
                'products.name as product_name',
                'products.id as product_number',
                'material_combination.name as material_combination',
                'material_combination.nominal_speed as nominal_speed',
                'records.user_id as user_id',
                'users.name as user_name'
            )
            ->where('run_date_time', '>=', $startDateTime)
            ->where('run_date_time', '<=', $endDateTime)
            ->where('machine_id', '=', $machine_id)
            ->where('job_id', '=', $job_id)
            ->orderby('run_date_time', 'ASC')
            ->get();

        $data['machine'] = Machine::find($machine_id);

        $idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();
        $runningCodes = Error::select('id')->where('category', '=', 'Running')->get();
        $jobWaitingCodes = Error::select('id')->where('category', '=', 'Job Waiting')->get();

        if(count($records) > 0) {
            $data['records'] = [];
            $data['negativeRecords'] = [];
            $startDate = $records[0]->run_date_time;
            $oldLength = $records[0]->length;
            $runTime = 0;
            $idleTime = 0;
            $totalTime = 0;
            $jobWaitTime = 0;
            $production = 0;
            $actualSpeed = 0;
            $jobProduction = 0;
            if(count($records) > 1) {
                for($i = 0; $i < count($records); $i++) {
                    if(isset($records[$i + 1])) {
                        if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id) {
                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                            foreach($runningCodes as $runningCode) {
                                if($runningCode->id == $records[$i]->error_id) {
                                    $runTime += $duration;
                                    $production += $records[$i]->length - $oldLength;
                                    $jobProduction += $records[$i]->length - $oldLength;
                                }
                            }
                            foreach($idleErrors as $idleError) {
                                if($idleError->id == $records[$i]->error_id) {
                                    $idleTime += $duration;
                                }
                            }
                            foreach($jobWaitingCodes as $jobWaitingCode) {
                                if($jobWaitingCode->id == $records[$i]->error_id) {
                                    $jobWaitTime += $duration;
                                }
                            }
                            $totalTime += $duration;
                            array_push($data['records'], [
                                "job_id" => $records[$i]->job_id,
                                "job_name" => $records[$i]->job_name,
                                "product_number" => $records[$i]->product_number,
                                "material_combination" => $records[$i]->material_combination,
                                "nominal_speed" => $records[$i]->nominal_speed,
                                "user_name" => $records[$i]->user_name,
                                "job_length" => $records[$i]->job_length,
                                "error_id" => $records[$i]->error_id,
                                "error_name" => $records[$i]->error_name,
                                "comments" => str_replace('#', 'no', $records[$i]->comments),
                                "length" => $records[$i]->length - $oldLength,
                                "from" => $startDate,
                                "to" => $endDate,
                                "duration" => $duration,
                            ]);
                            $startDate = $records[$i + 1]->run_date_time;
                            $oldLength = $records[$i]->length;
                        }
                    } else {
                        $endDate = $records[$i]->run_date_time;
                        $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                        $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                        foreach($runningCodes as $runningCode) {
                            if($runningCode->id == $records[$i]->error_id) {
                                $runTime += $duration;
                                $production += $records[$i]->length - $oldLength;
                                $jobProduction += $records[$i]->length - $oldLength;
                            }
                        }
                        foreach($idleErrors as $idleError) {
                            if($idleError->id == $records[$i]->error_id) {
                                $idleTime += $duration;
                            }
                        }
                        foreach($jobWaitingCodes as $jobWaitingCode) {
                            if($jobWaitingCode->id == $records[$i]->error_id) {
                                $jobWaitTime += $duration;
                            }
                        }
                        $totalTime += $duration;
                        array_push($data['records'], [
                            "job_id" => $records[$i]->job_id,
                            "job_name" => $records[$i]->job_name,
                            "product_number" => $records[$i]->product_number,
                            "material_combination" => $records[$i]->material_combination,
                            "nominal_speed" => $records[$i]->nominal_speed,
                            "user_name" => $records[$i]->user_name,
                            "job_length" => $records[$i]->job_length,
                            "error_id" => $records[$i]->error_id,
                            "error_name" => $records[$i]->error_name,
                            "comments" => str_replace('#', 'no', $records[$i]->comments),
                            "length" => $records[$i]->length - $oldLength,
                            "from" => $startDate,
                            "to" => $endDate,
                            "duration" => $duration,
                            "jobProduction" => $production
                        ]);
                        $startDate = $endDate;
                        $oldLength = $records[$i]->length;

                    }

                }
                for($k = 0; $k < count($data['records']); $k++) {
                    if(!isset($data['records'][$k][0]['jobProduction'])) {
                        array_push($data['records'][$k], [
                            "jobProduction" => $jobProduction
                        ]);
                    }
                }
            }
            $data['user'] = Users::find(Session::get('user_name'));
            $data['runningTime'] = $runTime;
            $data['unPlannedDowntime'] = $totalTime - $runTime;
        }
        if(Session::get('rights') == 0) {
            $data['layout'] = 'web-layout';
        } elseif(Session::get('rights') == 1) {
            $data['layout'] = 'admin-layout';
        } elseif(Session::get('rights') == 2) {
            $data['layout'] = 'power-user-layout';
        }
        $data['path'] = Route::getFacadeRoot()->current()->uri();
        //return $data['records'];
        $row['view'] = View::make('reports.job-wise-performance-report', $data)->render();
        return view('roto.reports', $row);
    }

    public function errorHistory($machine_id, $from, $to, $error_id, $shift) {
        $data['machine'] = Machine::find($machine_id);
        //$data['user'] = Users::find(Session::get('user_name'));
        //dd(Session::get('user_name'));
        if(Session::get('rights') == 0) {
            $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();
            $data['user'] = Users::find($loginRecord[0]->user_id);
        } else {
            $data['user'] = Users::find(Session::get('user_name'));
        }
        $data['path'] = Route::getFacadeRoot()->current()->uri();
        $data['from'] = $from;
        $data['to'] = $to;
        $data['shift'] = $shiftSelection = $shift = unserialize($shift);
        $machine = $data['machine'];
        if($shiftSelection[0] == 'All-Day') {
            $machine = Machine::find($data['machine']->id);
            $shifts_id = [];
            foreach($machine->section->department->businessUnit->company->shifts as $shift) {
                array_push($shifts_id, $shift->id);
            }

            $minStarted = Shift::find($shifts_id[0])->min_started;
            $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;

            $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));

        } else {

            $minStarted = Shift::find($shiftSelection[0])->min_started;
            $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;
            $startDateTime = date('Y-m-d H:i:s', strtotime($from.' + '.$minStarted.' minutes'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($from.' + '.$minEnded.' minutes'));
        }

        if(date('Y-m-d H:i:s') < $endDateTime) {
            $endDateTime = date('Y-m-d H:i:s');
        }

        $data['record'] = Record::where('machine_id', '=', $machine_id)->latest('run_date_time')->first();
        $records = DB::table('records')
            ->join('errors', 'errors.id', '=', 'records.error_id')
            ->join('users', 'users.id', '=', 'records.user_id')
            ->join('jobs', 'jobs.id', '=', 'records.job_id')
            ->join('products', 'products.id', '=', 'jobs.product_id')
            ->select(
                'errors.id as error_id',
                'errors.name as error_name',
                'records.run_date_time as run_date_time',
                'records.err_comments as err_comments',
                'products.name as product_name',
                'products.id as product_id',
                'users.name as user_name',
                'users.id as user_id',
                'jobs.id as job_id'
            )
            ->where('machine_id', '=', $data['machine']->id)
            ->where('run_date_time', '>=', $startDateTime)
            ->where('run_date_time', '<=', $endDateTime)
            ->orderby('run_date_time', 'ASC')
            ->get();
        if(count($records) > 0) {
            $data['records'] = [];
            $startDate = $records[0]->run_date_time;
            for($i = 0; $i < count($records); $i++) {
                if(isset($records[$i + 1])) {
                    if($records[$i]->error_id != $records[$i + 1]->error_id) {

                        $endDate = $records[$i]->run_date_time;
                        $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                        $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                        if($records[$i]->error_id == $error_id) {
                            array_push($data['records'], [
                                "from" => $startDate,
                                "to" => $endDate,
                                "error_id" => $records[$i]->error_id,
                                "job_id" => $records[$i]->job_id,
                                "product_id" => $records[$i]->product_id,
                                "product_name" => $records[$i]->product_name,
                                "user_id" => $records[$i]->user_id,
                                "user_name" => $records[$i]->user_name,
                                "error_name" => $records[$i]->error_name,
                                "duration" => $duration,
                                "err_comments" => str_replace('#', 'no', $records[$i]->err_comments),
                            ]);
                        }
                        $startDate = $endDate;
                    }
                } else {

                    $endDate = $records[$i]->run_date_time;
                    $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                    $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                    if($records[$i]->error_id == $error_id) {

                        array_push($data['records'], [
                            "from" => $startDate,
                            "to" => $endDate,
                            "error_id" => $records[$i]->error_id,
                            "job_id" => $records[$i]->job_id,
                            "product_id" => $records[$i]->product_id,
                            "product_name" => $records[$i]->product_name,
                            "user_id" => $records[$i]->user_id,
                            "user_name" => $records[$i]->user_name,
                            "error_name" => $records[$i]->error_name,
                            "duration" => $duration,
                            "err_comments" => $records[$i]->err_comments
                        ]);
                    }
                    $startDate = $endDate;
                }
            }

            if(Session::get('rights') == 0) {
                $data['layout'] = 'web-layout';
            } elseif(Session::get('rights') == 1) {
                $data['layout'] = 'admin-layout';
            } elseif(Session::get('rights') == 2) {
                $data['layout'] = 'power-user-layout';
            } elseif(Session::get('rights') == 3) {
                $data['layout'] = 'reporting-user-layout';
            }
            $data['error'] = Error::find($error_id);

            return view('reports.error-history-report', $data);
        } else {
            Session::flash("error", "No Record for the selected shift and date. Please try again.");
            return Redirect::back();
        }
    }

    public function resolveNegatives($startDateTime, $endDateTime, $machine_id) {
        $negativeRecords = Record::where('run_date_time', '>=', $startDateTime)->where('run_date_time', '<=', $endDateTime)->where('machine_id', '=', $machine_id)->orderby('run_date_time', 'ASC')->get();

        DB::beginTransaction();
        for($i = 0; $i < count($negativeRecords); $i++) {
            $run_date_time = date_create($negativeRecords[$i]->run_date_time);
            $created_at = date_create($negativeRecords[$i]->created_at);
            $diff = $created_at->diff($run_date_time);
            $minutes = $diff->days * 24 * 60;
            $minutes += $diff->h * 60;
            $minutes += $diff->i;
            //            if($minutes >= 2 && $negativeRecords[$i]->err_comments != '*' && $negativeRecords[$i]->err_comments != 'Manual Entry'){
//            // if($minutes >= 2 && $negativeRecords[$i]->comments != '*'){ Haseeb
//                $negativeRecords[$i]->run_date_time = $negativeRecords[$i]->created_at;
//                $negativeRecords[$i]->save();
//            }
//            else{
            if(isset($negativeRecords[$i + 1])) {
                //dd($negativeRecords[$i]->run_date_time);
                if($negativeRecords[$i]->error_id == $negativeRecords[$i + 1]->error_id && $negativeRecords[$i]->job_id == $negativeRecords[$i + 1]->job_id && $negativeRecords[$i]->user_id == $negativeRecords[$i + 1]->user_id && $negativeRecords[$i]->machine_id == $negativeRecords[$i + 1]->machine_id) {
                    //dd($negativeRecords[$i]->run_date_time);
                    if($negativeRecords[$i + 1]->length < $negativeRecords[$i]->length) {
                        $job = Job::where('product_id', '=', $negativeRecords[$i + 1]->job->product_id)->where('id', '!=', $negativeRecords[$i + 1]->job_id)->first();
                        if(!empty($job)) {
                            $negativeRecords[$i + 1]->job_id = $job->id;
                            $negativeRecords[$i + 1]->save();
                        } else {
                            $negativeRecords[$i + 1]->job_id = 'Negative Meters';
                            $negativeRecords[$i + 1]->save();
                        }
                    }
                } elseif($negativeRecords[$i]->error_id != $negativeRecords[$i + 1]->error_id && $negativeRecords[$i]->job_id == $negativeRecords[$i + 1]->job_id && $negativeRecords[$i]->user_id == $negativeRecords[$i + 1]->user_id && $negativeRecords[$i]->machine_id == $negativeRecords[$i + 1]->machine_id) {
                    //dd($negativeRecords[$i]->run_date_time);
                    if($negativeRecords[$i + 1]->length < $negativeRecords[$i]->length) {
                        $job = Job::where('product_id', '=', $negativeRecords[$i + 1]->job->product_id)->where('id', '!=', $negativeRecords[$i + 1]->job_id)->first();
                        if(!empty($job)) {
                            $negativeRecords[$i + 1]->job_id = $job->id;
                            $negativeRecords[$i + 1]->save();
                        } else {
                            $negativeRecords[$i + 1]->job_id = 'Negative Meters';
                            $negativeRecords[$i + 1]->save();
                        }
                    }
                }
                /*else if ($negativeRecords[$i]->error_id != $negativeRecords[$i+1]->error_id && $negativeRecords[$i]->job_id == $negativeRecords[$i+1]->job_id && $negativeRecords[$i]->user_id == $negativeRecords[$i+1]->user_id && $negativeRecords[$i]->machine_id == $negativeRecords[$i+1]->machine_id) {
                    //echo $negativeRecords[$i+1]->length;echo '<br>';
                    //$negativeRecords[$i+1]->error_id = $negativeRecords[$i]->error_id;
                    //$negativeRecords[$i+1]->save();
                }*/
            }
            //            }
        }
        DB::commit();
    }

    public function calculateMinutes($fromTime, $toTime) {
        $diff = date_diff(date_create($toTime), date_create($fromTime));
        $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s / 60;
        if($diff->invert) {
            return -1 * $total;
        } else {
            return $total;
        }
    }

    public function checkReport() {
        /*$records = DB::table('records')
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
            ->where('machine_id', '=', 6)
            ->where('records.run_date_time', '>=', '2020-04-31 06:30:00')
            ->where('records.run_date_time', '<=', '2020-06-01 06:30:00')
            ->orderby('run_date_time', 'ASC')
            ->get();*/
    }

    public function exportPDF(Request $request) {
        $data['view'] = $request->input('view');
        $view = View::make('layouts.export-layout', $data)->render();

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
        if(@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }
        $pdf::AddPage();
        $pdf::writeHTML($view, true, false, true, false, '');
        $pdf::lastPage();
        ob_end_clean();
        $filepath = 'checking.pdf';
        $pdf::Output('C:\\xampp\\htdocs\\rotoeye\\exports\\'.$filepath, 'F');
    }

    public function importgroupDashboard(Request $request) {
        $user_id = Session::get('user_id');
        if(isset($user_id)) {
            //  dd("asd");
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            // $machine_id = Crypt::decrypt($id);
            $data['layout'] = 'import-data-layout';

            // $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();
            // $data['machine'] = Machine::find($machine_id);
            if(Session::get('rights') == 0) {
                $data['user'] = Users::find($loginRecord[0]->user_id);
            } else {
                $data['user'] = Users::find(Session::get('user_name'));
            }

            $data['errorCategories'] = Categories::all();
            $data['machines'] = Machine::whereNull('is_disabled')->orderBy("sd_status", "ASC")->get();
            /// mine code
            //dd($data['errorCategories']);
            return view('reports.import-generate-reports', $data);
        }

    }
    public function postimportgroupDashboard(Request $request) {
        //dd($request->all());
        $machine_id = $request->machine_id;
        $dateRange = $request->daterange;
        $daterange = explode(" - ", $dateRange);
        $date = date('Y-m-d', strtotime($daterange[0]));
        $to_date = date('Y-m-d', strtotime($daterange[1]));
        //dd($dateRange);
        $shiftSelection[] = 'All-Day'; //$request->input('shiftSelection');
        if($shiftSelection[0] == 'All-Day') {
            //dd("sd");
            $machine = Machine::find($machine_id);
            $shifts_id = [];
            foreach($machine->section->department->businessUnit->company->shifts as $shift) {
                array_push($shifts_id, $shift->id);
            }
            //dd($shifts_id);
            $minStarted = Shift::find($shifts_id[0])->min_started;
            $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;
            $from_date = $date;
            $to_date = $to_date;

            $data['from'] = $from_date;
            $data['to'] = $to_date;
            // dd($data['from']);
            $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'].' + '.$minStarted.' minutes'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'].' + '.$minEnded.' minutes'));
            // dd($endDateTime);
        } else {
            $minStarted = Shift::find($shiftSelection[0])->min_started;
            $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;
            $startDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minStarted.' minutes'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($date.' + '.$minEnded.' minutes'));
        }

        if(date('Y-m-d H:i:s') < $endDateTime) {
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
            ->leftJoin('process_structure', function ($join) {
                $join->on('process_structure.process_id', '=', 'records.process_id');
                $join->on('process_structure.product_id', '=', 'products.id');
            })
            ->leftJoin('material_combination', 'material_combination.id', '=', 'process_structure.material_combination_id')
            ->leftJoin('processes', 'processes.id', '=', 'process_structure.process_id')
            ->select(
                'errors.name as error_name',
                'records.run_date_time as run_date_time',
                'records.error_id as error_id',
                'records.length as length',
                'records.err_comments as comments',
                'jobs.id as job_id',
                'products.name as job_name',
                'jobs.job_length as job_length',
                'products.name as product_name',
                'products.id as product_number',
                'material_combination.name as material_combination',
                'process_structure.color as process_structure_color',
                'material_combination.nominal_speed as nominal_speed',
                'records.user_id as user_id',
                'users.name as user_name',
                'processes.process_name as process_name'
            )
            ->where('machine_id', '=', $machine_id)
            ->where('records.run_date_time', '>=', $startDateTime)
            ->where('records.run_date_time', '<=', $endDateTime)
            ->orderby('run_date_time', 'ASC')
            ->get();
        //dd(count($records));
        if(count($records) > 0) {
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
            $totalLength = 0;

            $totalTime = (strtotime($endDateTime) - strtotime($startDateTime)) / 60;
            if(count($records) > 1) {
                for($i = 0; $i < count($records); $i++) {
                    if(isset($records[$i + 1])) {
                        if($records[$i]->error_id != $records[$i + 1]->error_id || $records[$i]->user_id != $records[$i + 1]->user_id || $records[$i]->job_id != $records[$i + 1]->job_id) {
                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                            $totalLength += $records[$i]->length - $oldLength;

                            if($data['machine']->time_uom == 'Hr') {
                                if($duration == 0) {
                                    $instantSpeed = 0;
                                } else {
                                    $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                                }
                            } elseif($data['machine']->time_uom == 'Min') {
                                if($duration == 0) {
                                    $instantSpeed = 0;
                                } else {
                                    $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                                }
                            } else {
                                if($duration == 0) {
                                    $instantSpeed = 0;
                                } else {
                                    $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                                }
                            }

                            foreach($runningCodes as $runningCode) {
                                if($runningCode->id == $records[$i]->error_id) {
                                    $runTime += $duration;
                                    $jobRunTime += $duration;
                                    $production += $records[$i]->length - $oldLength;
                                    $jobProduction += $records[$i]->length - $oldLength;
                                }
                            }
                            //Waste Codes Added
                            foreach($wasteCodes as $wasteCode) {
                                if($wasteCode->id == $records[$i]->error_id) {
                                    $waste += $records[$i]->length - $oldLength;
                                    $jobWaste += $records[$i]->length - $oldLength;
                                }
                            }
                            foreach($idleErrors as $idleError) {
                                if($idleError->id == $records[$i]->error_id) {
                                    $idleTime += $duration;
                                }
                            }
                            foreach($jobWaitingCodes as $jobWaitingCode) {
                                if($jobWaitingCode->id == $records[$i]->error_id) {
                                    $jobWaitTime += $duration;
                                }
                            }

                            if($records[$i]->length - $oldLength < 0) {
                                //
                            }

                            array_push($data['records'], [
                                "job_id" => $records[$i]->job_id,
                                "job_name" => $records[$i]->job_name,
                                "product_number" => $records[$i]->product_number,
                                "material_combination" => $records[$i]->material_combination,
                                "process_structure_color" => $records[$i]->process_structure_color,
                                "nominal_speed" => $records[$i]->nominal_speed,
                                "user_id" => $records[$i]->user_id,
                                "user_name" => $records[$i]->user_name,
                                "job_length" => $records[$i]->job_length,
                                "error_id" => $records[$i]->error_id,
                                "error_name" => $records[$i]->error_name,
                                "comments" => str_replace('#', 'no', $records[$i]->comments),
                                "length" => $records[$i]->length - $oldLength,
                                "from" => $startDate,
                                "to" => $endDate,
                                "duration" => $duration,
                                "instantSpeed" => $instantSpeed,
                                "process_name" => $records[$i]->process_name,
                            ]);

                            $startDate = $endDate;
                            if($records[$i]->job_id != $records[$i + 1]->job_id) {
                                $oldLength = $records[$i + 1]->length;
                                if($jobRunTime == 0) {
                                    $jobPerformance = 0;
                                    $jobAverageSpeed = 0;
                                } else {
                                    if($data['machine']->time_uom == 'Min') {
                                        $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                                        $jobAverageSpeed = $jobProduction / $jobRunTime;
                                    } elseif($data['machine']->time_uom == 'Hr') {
                                        $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                                        $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                                    } elseif($data['machine']->time_uom == 'Sec') {
                                        $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                                        $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                                    }
                                }
                                for($j = 0; $j < count($data['records']); $j++) {
                                    array_push($data['records'][$j], [
                                        "jobProduction" => $jobProduction,
                                        "jobPerformance" => $jobPerformance,
                                        "jobRuntime" => $jobRunTime,
                                        "jobAverageSpeed" => $jobAverageSpeed,
                                        "jobWaste" => $jobWaste
                                    ]);
                                }
                                $jobProduction = 0;
                                $jobRunTime = 0;
                                $jobWaste = 0;
                            } else {
                                $oldLength = $records[$i]->length;
                            }
                        }
                    } else {
                        $endDate = $records[$i]->run_date_time;
                        $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                        $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                        $totalLength += $records[$i]->length - $oldLength;

                        if($data['machine']->time_uom == 'Hr') {
                            if($duration == 0) {
                                $instantSpeed = 0;
                            } else {
                                $instantSpeed = (($records[$i]->length - $oldLength) / $duration) * 60;
                            }
                        } elseif($data['machine']->time_uom == 'Min') {
                            if($duration == 0) {
                                $instantSpeed = 0;
                            } else {
                                $instantSpeed = ($records[$i]->length - $oldLength) / $duration;
                            }
                        } else {
                            if($duration == 0) {
                                $instantSpeed = 0;
                            } else {
                                $instantSpeed = (($records[$i]->length - $oldLength) / $duration) / 60;
                            }
                        }

                        foreach($runningCodes as $runningCode) {
                            if($runningCode->id == $records[$i]->error_id) {
                                $runTime += $duration;
                                $production += $records[$i]->length - $oldLength;
                                $jobProduction += $records[$i]->length - $oldLength;
                            }
                        }

                        foreach($wasteCodes as $wasteCode) {
                            if($wasteCode->id == $records[$i]->error_id) {
                                $waste += $records[$i]->length - $oldLength;
                                $jobWaste += $records[$i]->length - $oldLength;
                            }
                        }
                        foreach($idleErrors as $idleError) {
                            if($idleError->id == $records[$i]->error_id) {
                                $idleTime += $duration;
                            }
                        }
                        foreach($jobWaitingCodes as $jobWaitingCode) {
                            if($jobWaitingCode->id == $records[$i]->error_id) {
                                $jobWaitTime += $duration;
                            }
                        }
                        if($records[$i]->length - $oldLength < 0) {
                            //
                        }
                        array_push($data['records'], [
                            "job_id" => $records[$i]->job_id,
                            "job_name" => $records[$i]->job_name,
                            "product_number" => $records[$i]->product_number,
                            "material_combination" => $records[$i]->material_combination,
                            "process_structure_color" => $records[$i]->process_structure_color,
                            "nominal_speed" => $records[$i]->nominal_speed,
                            "user_name" => $records[$i]->user_name,
                            "user_id" => $records[$i]->user_id,
                            "job_length" => $records[$i]->job_length,
                            "error_id" => $records[$i]->error_id,
                            "error_name" => $records[$i]->error_name,
                            "comments" => str_replace('#', 'no', $records[$i]->comments),
                            "length" => $records[$i]->length - $oldLength,
                            "from" => $startDate,
                            "to" => $endDate,
                            "duration" => $duration,
                            "instantSpeed" => $instantSpeed,
                            "jobProduction" => $production,
                            "process_name" => $records[$i]->process_name,
                        ]);
                        $startDate = $endDate;
                        $oldLength = $records[$i]->length;
                    }
                }


                for($k = 0; $k < count($data['records']); $k++) {
                    if($jobRunTime == 0) {
                        $jobPerformance = 0;
                        $jobAverageSpeed = 0;
                    } else {
                        if($data['machine']->time_uom == 'Min') {
                            $jobPerformance = (($jobProduction / $jobRunTime) / $data['machine']->max_speed) * 100;
                            $jobAverageSpeed = $jobProduction / $jobRunTime;
                        } elseif($data['machine']->time_uom == 'Hr') {
                            $jobPerformance = (($jobProduction / $jobRunTime) * 60 / $data['machine']->max_speed) * 100;
                            $jobAverageSpeed = ($jobProduction / $jobRunTime) * 60;
                        } elseif($data['machine']->time_uom == 'Sec') {
                            $jobPerformance = ((($jobProduction / $jobRunTime) / 60) / $data['machine']->max_speed) * 100;
                            $jobAverageSpeed = (($jobProduction / $jobRunTime) / 60);
                        }
                    }
                    if(!isset($data['records'][$k][0]['jobProduction']) || !isset($data['records'][$k][0]['jobPerformance'])) {
                        array_push($data['records'][$k], [
                            "jobProduction" => $jobProduction,
                            "jobPerformance" => $jobPerformance,
                            "jobRuntime" => $jobRunTime,
                            "jobAverageSpeed" => $jobAverageSpeed,
                            "jobWaste" => $jobWaste
                        ]);
                    }
                }

                if($runTime > 0) {
                    if($data['machine']->time_uom == 'Hr') {
                        $actualSpeed = $production / $runTime * 60;
                    } elseif($data['machine']->time_uom == 'Min') {
                        $actualSpeed = $production / $runTime;
                    } else {
                        $actualSpeed = $production / $runTime / 60;
                    }
                } else {
                    $actualSpeed = 0;
                }
            }
            if(Session::get('rights') == 0) {
                $data['user'] = Users::find($loginRecord[0]->user_id);
            } else {
                $data['user'] = Users::find(Session::get('user_name'));
            }

            $data['produced'] = $production;
            $data['waste'] = $waste;
            $data['quality'] = ($production > 0) ? 100 - (($waste / $production) * 100) : 0;
            $data['oee'] = ($production > 0) ? ($runTime / ($totalTime - $idleTime)) * ($actualSpeed / $data['machine']->max_speed) * ((100 - (($waste / $production) * 100)) / 100) * 100 : 0;
            $data['ee'] = ($production > 0) ? ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * ($actualSpeed / $data['machine']->max_speed) * ((100 - (($waste / $production) * 100)) / 100) * 100 : 0;
            $data['performance'] = ($actualSpeed / $data['machine']->max_speed) * 100;
            $data['availability_ee'] = ($runTime / ($totalTime - $idleTime - $jobWaitTime)) * 100;
            $data['availability'] = ($runTime / ($totalTime - $idleTime)) * 100;
            $data['totalDowntime'] = ($totalTime - $idleTime) - $runTime;
            $data['run_time'] = $runTime;
            $data['budgetedTime'] = $totalTime - $idleTime;
            $data['totalLength'] = $totalLength;
            $data['shift'] = $shiftSelection[0];
            $data['date'] = $date;
            $data['current_time'] = date('Y-m-d H:i:s');

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
            })->where('machine_id', '=', $data['machine']->id)->get();


            if(isset($existingRecords)) {
                if(count($existingRecords) > 0) {
                    //if(count($existingRecords) < count($data['records'])){
                    foreach($existingRecords as $r) {
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
            $formattedDate = $data['startDateTime']; //date("Y-m-d", $timestamp);
            //dd($formattedDate);
            foreach($data['records'] as $record) {
                // dd($machine->section);
                $reportdata = new GroupProductionReport();
                $reportdata->job_no = $record['job_id'];
                $reportdata->job_name = $record['job_name'];
                $reportdata->machine_id = $data['machine']->id;
                $reportdata->machine_no = $data['machine']->sap_code;
                $reportdata->err_no = $record['error_id'];
                $reportdata->err_name = $record['error_name'];
                $reportdata->err_comments = $record['comments'];
                $reportdata->from_time = $record['from'];
                $reportdata->to_time = $record['to'];
                $reportdata->duration = $record['duration'];
                $reportdata->length = $record['length'];
                $reportdata->date = $record['from'];
                $reportdata->company_id = $machine->section->department->businessUnit->company->id;
                $reportdata->company_name = $machine->section->department->businessUnit->company->name;
                $reportdata->company_id = $machine->section->department->businessUnit->company->id;
                $reportdata->business_unit_id = $machine->section->department->businessUnit->id;
                $reportdata->business_unit_name = $machine->section->department->businessUnit->business_unit_name;
                $reportdata->department_id = $machine->section->department->id;
                $reportdata->department_name = $machine->section->department->name;
                $reportdata->section_id = $machine->section->id;
                $reportdata->section_name = $machine->section->name;
                $reportdata->month = $monthZeroPadded;
                $reportdata->operator_id = $record['user_id'];
                $reportdata->operator_name = $record['user_name'];
                $reportdata->product_number = $record['product_number'];
                $reportdata->material_combination = $record['material_combination'];
                $reportdata->nominal_speed = $record['nominal_speed'];
                $reportdata->save();
                //  }
            }

            //////////
            Session::flash("success", "Entered");
            return Redirect::back();
        } else {
            Session::flash("error", "no record");
            return Redirect::back();
        }

    }
    public function splittingRecords($record, $nextrecord, $endd, $data) {
        //dump($data);
        // dump($record);

        $date1 = $endd;
        $date2 = $record['to'];
        $datetime1 = new \DateTime($date1);
        $datetime2 = new \DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        $final_diff = $datetime2->sub($interval);
        $sub_data = $final_diff->format('Y-m-d H:i:s');
        //dump($sub_data);



        $difference = date_diff(date_create($sub_data), date_create($record['from']));
        $hours = $difference->h;    // Hours
        $minutes = $difference->i;  // Minutes
        $seconds = $difference->s;  // Seconds
        $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
        $machine = $data;

        $reportdata = new GroupProductionReport();
        $reportdata->job_no = $record['job_id'];
        $reportdata->job_name = $record['job_name'];
        $reportdata->machine_id = $machine['id'];
        $reportdata->machine_no = $machine['sap_code'];
        $reportdata->err_no = $record['error_id'];
        $reportdata->err_name = $record['error_name'];
        $reportdata->err_comments = $record['comments'];
        $reportdata->from_time = $record['from'];
        $reportdata->to_time = $sub_data;
        $reportdata->duration = $duration;
        $reportdata->divided = 1;
        $reportdata->length = ($record['length'] > 0) ? 1 : 0;
        $reportdata->date = $record['from'];
        $reportdata->company_id = $machine->section->department->businessUnit->company->id;
        $reportdata->company_name = $machine->section->department->businessUnit->company->name;
        $reportdata->company_id = $machine->section->department->businessUnit->company->id;
        $reportdata->business_unit_id = $machine->section->department->businessUnit->id;
        $reportdata->business_unit_name = $machine->section->department->businessUnit->business_unit_name;
        $reportdata->department_id = $machine->section->department->id;
        $reportdata->department_name = $machine->section->department->name;
        $reportdata->section_id = $machine->section->id;
        $reportdata->section_name = $machine->section->name;

        $reportdata->operator_id = $record['user_id'];
        $reportdata->operator_name = $record['user_name'];
        $reportdata->product_number = $record['product_number'];
        $reportdata->material_combination = $record['material_combination'];
        $reportdata->nominal_speed = $record['nominal_speed'];
        $reportdata->run_time = ($record['run_time'] > 0) ? 1 : 0;
        $reportdata->idleTime = ($record['idleTime'] > 0) ? 1 : 0;
        $reportdata->job_wating_time = ($record['jobWaitingTime'] > 0) ? 1 : 0;
        $reportdata->totalStrategiclosses = ($record['totalStrategiclosses'] > 0) ? 1 : 0;
        $reportdata->totalPlannedlosses = ($record['totalPlannedlosses'] > 0) ? 1 : 0;
        $reportdata->totalOperationallosses = ($record['totalOperationallosses'] > 0) ? 1 : 0;
        $reportdata->save();

        //// slpiting record
        $difference = date_diff(date_create($record['to']), date_create($sub_data));
        $hours = $difference->h;    // Hours
        $minutes = $difference->i;  // Minutes
        $seconds = $difference->s;  // Seconds
        $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
        $machine = $data;

        $reportdata = new GroupProductionReport();
        $reportdata->job_no = $record['job_id'];
        $reportdata->job_name = $record['job_name'];
        $reportdata->machine_id = $machine['id'];
        $reportdata->machine_no = $machine['sap_code'];
        $reportdata->err_no = $record['error_id'];
        $reportdata->err_name = $record['error_name'];
        $reportdata->err_comments = $record['comments'];
        $reportdata->from_time = $sub_data;
        $reportdata->to_time = $record['to'];
        $reportdata->duration = $duration;
        $reportdata->divided = 1;
        $reportdata->length = ($record['length'] > 0) ? $record['length'] - 1 : 0;
        $reportdata->date = $record['from'];
        $reportdata->company_id = $machine->section->department->businessUnit->company->id;
        $reportdata->company_name = $machine->section->department->businessUnit->company->name;
        $reportdata->company_id = $machine->section->department->businessUnit->company->id;
        $reportdata->business_unit_id = $machine->section->department->businessUnit->id;
        $reportdata->business_unit_name = $machine->section->department->businessUnit->business_unit_name;
        $reportdata->department_id = $machine->section->department->id;
        $reportdata->department_name = $machine->section->department->name;
        $reportdata->section_id = $machine->section->id;
        $reportdata->section_name = $machine->section->name;

        $reportdata->operator_id = $record['user_id'];
        $reportdata->operator_name = $record['user_name'];
        $reportdata->product_number = $record['product_number'];
        $reportdata->material_combination = $record['material_combination'];
        $reportdata->nominal_speed = $record['nominal_speed'];
        $reportdata->run_time = ($record['run_time'] > 0) ? $record['run_time'] - 1 : 0;
        $reportdata->idleTime = ($record['idleTime'] > 0) ? $record['idleTime'] - 1 : 0;
        $reportdata->job_wating_time = ($record['jobWaitingTime'] > 0) ? $record['jobWaitingTime'] - 1 : 0;
        $reportdata->totalStrategiclosses = ($record['totalStrategiclosses'] > 0) ? $record['totalStrategiclosses'] - 1 : 0;
        $reportdata->totalPlannedlosses = ($record['totalPlannedlosses'] > 0) ? $record['totalPlannedlosses'] - 1 : 0;
        $reportdata->totalOperationallosses = ($record['totalOperationallosses'] > 0) ? $record['totalOperationallosses'] - 1 : 0;



        $reportdata->save();

    }
}
