<?php namespace App\Http\Controllers;

use App\Models\Error;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Job;
use App\Models\LoginRecord;
use App\Models\Machine;
use App\Models\MaterialCombination;
use App\Models\Process;
use App\Models\Process_Structure;
use App\Models\Product;
use App\Models\Record;
use App\Models\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use PhpSpec\Exception\Exception;

class ManualRecordsController extends Controller {

    public function manual($id)
    {
        $user_id = Session::get('user_id');
        if (isset($user_id)) {
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            if (Session::get('rights') == 0) {
                $data['layout'] = 'web-layout';
            } elseif (Session::get('rights') == 1) {
                $data['layout'] = 'admin-layout';
            } elseif (Session::get('rights') == 2) {
                $data['layout'] = 'power-user-layout';
            }

            $data['machine'] = Machine::find(Crypt::decrypt($id));
            $data['user'] = Users::find(Session::get('user_name'));

            $data['error_id'] = Error::all();
            $data['process_id'] = Process::all();

            $data['user_id'] = Users::all();

            $data['jobs'] = Job::with('product', 'product.process')->whereHas('product.process', function($query) use ($data){
                $query->whereHas('section.department.businessUnit.company', function($query2) use ($data){
                    $query2->where('companies.id', '=', $data['machine']->section->department->businessUnit->company->id);
                });
            })->get();

            $data['materialCombinations'] = MaterialCombination::with('process')->whereHas('process', function($query) use ($data){
                $query->whereHas('section.department.businessUnit.company', function($query2) use ($data){
                    $query2->where('companies.id', '=', $data['machine']->section->department->businessUnit->company->id);
                });
            })->get();

            $data['products'] = Product::with('process')->whereHas('process', function($query) use ($data){
                $query->whereHas('section.department.businessUnit.company', function($query2) use ($data){
                    $query2->where('companies.id', '=', $data['machine']->section->department->businessUnit->company->id);
                });
            })->get();
            return view('manual.update-records-manual', $data);
        } else {
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }

    public function get_recent_record_manual(Request $request)
    {
        $user_id = Session::get('user_id');
        if (isset($user_id)) {
            $from = date("Y-m-d H:i:s", strtotime($request->from));
            $to = date("Y-m-d H:i:s", strtotime($request->to));
            $data['machine'] = $request->machine_id;
            if (count(Record::where("machine_id", "=", $request->machine_id)->whereBetween('run_date_time', [ date("Y-m-d H:i:s",strtotime("+20 sec", strtotime($from))),  date("Y-m-d H:i:s",strtotime("-20 Sec", strtotime($to)))])->get()) > 0) {
                return response("Record between chosen time exists", 500);
            } else {
                $data['from_db'] = Record::where("run_date_time", "<=", $from)->where("machine_id", "=", $request->machine_id)->orderby('run_date_time', 'desc')->limit(1)->get();
                $data['to_db'] = Record::where("run_date_time", ">=", $to)->where("machine_id", "=", $request->machine_id)->orderby('run_date_time', 'asc')->limit(1)->get();
                $from_diff = abs(strtotime($data['from_db'][0]->run_date_time)-strtotime($from));
                $to_diff = abs(strtotime($data['to_db'][0]->run_date_time)-strtotime($to));
                if($from_diff<60 && $to_diff<60){
                    $dt = (strtotime($to)-strtotime($from))/60;
                    $data['cal_speed']= round($request->mtr/$dt);
                    $data['record'] = Record::where("run_date_time", "<=", $from)->where("machine_id", "=", $request->machine_id)->orderby('run_date_time', 'desc')->limit(1)->get();
                    return response($data,200);

                } else {
                    return response("Please Correct Date and time", 500);
                }
            }
        }
        else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }

    public function manual_update(Request $request){

        $user_id = Session::get('user_id');
        if(isset($user_id)){
            $machine = Machine::find($request->input('machine_id'));
            $logger = new LoggerController($machine);
            $logger->log('--------------------------------------------- START MANUAL RECORDS ---------------------------------------------', $machine);
            $from= date("Y-m-d H:i:s", strtotime($request->from));
            $to = date("Y-m-d H:i:s", strtotime($request->to));

            if (count(Record::where("machine_id", "=", $request->machine_id)->whereBetween('run_date_time', [ date("Y-m-d H:i:s",strtotime("+20 sec", strtotime($from))),  date("Y-m-d H:i:s",strtotime("-20 Sec", strtotime($to)))])->get()) > 0) {
                return response("Record between chosen time exists", 500);
            } else {
                $from_db = Record::where("run_date_time", "<=", $from)->where("machine_id", "=", $request->machine_id)->orderby('run_date_time', 'desc')->limit(1)->get();
                $to_db= Record::where("run_date_time", ">=", $to)->where("machine_id", "=", $request->machine_id)->orderby('run_date_time', 'asc')->limit(1)->get();
                $from_diff = abs(strtotime($from_db[0]->run_date_time)-strtotime($from));
                $to_diff = abs(strtotime($to_db[0]->run_date_time)-strtotime($to));
                if($from_diff<60 && $to_diff<60){
                    $from = $from_db[0]->run_date_time;
                    $to = $to_db[0]->run_date_time;
                    $meter_count = $from_db[0]->length;
                    $dt = (strtotime($to)-strtotime($from))/60;
                    $speed = round($request->mtr/$dt);
                    $avg_meter = round($request->mtr/($dt*3));
                    $records=[];
//                    $meter_count=0;
                    do{
                        $record = new Record();
                        $record->user_id = $request->user_id;
                        $record->error_id = $request->error_id;
                        $record->job_id = $request->job_id;
                        $record->machine_id = $request->machine_id;
                        $record->err_comments = "Manual Entry";
                        $record->process_id = $request->process_id;
                        $record->speed = $speed;
                        $record->run_date_time = $from = date("Y-m-d H:i:s", strtotime("+20 Sec", strtotime($from)));
                        $record->length =  $meter_count+=$avg_meter;
                        $record->save();
                        array_push($records,$record);
                    }
                    while ($from <  date("Y-m-d H:i:s", strtotime("-20 Sec", strtotime($to))));

                    $records[count($records)-1]->error_id=500;
                    $records[count($records)-1]->save();
                    $to_db[0]->error_id=500;
//                    $to_db[0]->length=$from_db[0]->length;
                    $to_db[0]->save();

                } else {
                    return response("Please Correct Date and time", 500);
                }
            }


            $logger->log('--------------------------------------------- END MANUAL RECORDS ---------------------------------------------', $machine);
            return response("Manual Record Generated",200);
        }
        else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }

    }

    public function newProduction(Request $request){
        try {
            SubmitJob:
            $machine = Machine::find($request->input('machine'));
            if($machine){
                $job = Job::find($request->input('job'));
                if(isset($job)){
                    $product = Product::find($job->product_id);
                    if(isset($product)){
                        $material_combination = MaterialCombination::find($request->input('material_combination'));
                        if(!isset($material_combination)){
                            $material_combination = MaterialCombination::where('name','=',$request->input('material_combination'))->first();
                        }
                        if(isset($material_combination)){
                            $process = Process::find($request->input('process'));
                            if(!isset($process)){
                                $process = Process::where('process_name', '=', $request->input('process'))->first();
                            }
                            if(isset($process)){
                                $processStructure = Process_Structure::where('product_id', '=', $product->id)
                                    ->where('process_id', '=', $process->id)
                                    ->first();
                                if(isset($processStructure)){
                                    if($processStructure->material_combination_id == $material_combination->id){
                                        return response(["job_id"=>$job->id],200);
                                    }
                                    else{
                                        return response("Please select another process. Material Combination already exist against this Product and Process",500);
                                    }
                                }
                                else{
                                    $processStructure = new Process_Structure();
                                    $processStructure->material_combination_id = $material_combination->id;
                                    $processStructure->process_id = $process->id;
                                    $processStructure->product_id = $product->id;
                                    $processStructure->color = $request->input('color');
                                    $processStructure->adhesive = $request->input('adhesive');
                                    $processStructure->save();
                                    goto SubmitJob;
                                }
                            }
                            else{
                                $process = new Process();
                                $process->process_name = $request->input('process');
                                $process->section_id = $machine->section->id;
                                $process->save();
                                goto SubmitJob;
                            }
                        }
                        else{
                            $material_combination = new MaterialCombination();
                            $material_combination->name = $request->input('material_combination');
                            $material_combination->save();
                            goto SubmitJob;
                        }
                    }
                    else{
                        $product = new Product();
                        $product->id = $request->input('product_number');
                        $product->name = $request->input('product_name');
                        $product->save();
                        goto SubmitJob;
                    }
                }
                else{
                    $job = new Job();
                    $job->id = $request->input('job');
                    $job->job_length = $request->input('job_length');
                    $job->product_id = $request->input('product_number');
                    $job->save();
                    goto SubmitJob;
                }
            }
            else{
                return response("Machine is not valid.",500);
            }
        }
        catch (ModelNotFoundException $e)
        {
            return response("Error Submitting Production Order",500);
        }
    }

    public function submitJob(Request $request){
        SubmitJob:
        $machine = Machine::find($request->input('machine'));
        if($machine){
            $job = Job::find($request->input('job'));

            if(isset($job)){
                $product = Product::find($job->product_id);
                if(isset($product)){
                    $material_combination = MaterialCombination::find($request->input('material_combination'));
                    if(!isset($material_combination)){
                        $material_combination = MaterialCombination::where('name','=',$request->input('material_combination'))->first();
                    }
                    if(isset($material_combination)){
                        $process = Process::find($request->input('process'));
                        if(!isset($process)){
                            $process = Process::where('process_name', '=', $request->input('process'))->first();
                        }
                        if(isset($process)){
                            $processStructure = Process_Structure::where('product_id', '=', $product->id)
                                ->where('process_id', '=', $process->id)
                                ->first();
                            if(isset($processStructure)){
                                if($processStructure->material_combination_id == $material_combination->id){
                                    return response(["job_id"=>$job->id],200);
                                }
                                else{
                                    return response("Please select another process. Material Combination already exist against this Product and Process",500);

                                }
                            }
                            else{
                                $processStructure = new Process_Structure();
                                $processStructure->material_combination_id = $material_combination->id;
                                $processStructure->process_id = $process->id;
                                $processStructure->product_id = $product->id;
                                $processStructure->color = $request->input('color');
                                $processStructure->adhesive = $request->input('adhesive');
                                $processStructure->save();
                                goto SubmitJob;
                            }
                        }
                        else{
                            $process = new Process();
                            $process->process_name = $request->input('process');
                            $process->section_id = $machine->section->id;
                            $process->save();
                            goto SubmitJob;
                        }
                    }
                    else{
                        $material_combination = new MaterialCombination();
                        $material_combination->name = $request->input('material_combination');
                        $material_combination->save();
                        goto SubmitJob;
                    }
                }
                else{
                    return response("Please select a valid Product",500);

                }
            }
            else{
                return response("Please select a valid Job",500);

            }
        }
        else{
            return response("Machine is not valid.",500);

        }
    }
}
