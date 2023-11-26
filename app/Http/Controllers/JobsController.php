<?php namespace App\Http\Controllers;

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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use PhpSpec\Exception\Exception;

class JobsController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
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
		$data['user'] = Users::find(Session::get('user_name'));
		$data['productionOrders'] = Job::all();
		return view('roto.production-orders', $data);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
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
		$data['user'] = Users::find(Session::get('user_name'));
		$data['productionOrder'] = Job::find($id);
		foreach($data['productionOrder']->users as $item){
			echo $item;
		}
		//return view('roto.production-order-details', $data);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	public function selectJob($id){
        $user_id = Session::get('user_id');
        if(isset($user_id)){
            $data['lastRunningJob'] = LoginRecord::select('job_id')->where('machine_id', '=', Session::get('machine_id'))->get();
            $data['machine'] = Machine::find(Crypt::decrypt($id));
            $data['jobs'] = Job::with('product', 'product.process')->whereHas('product.process', function($query) use ($data){
                $query->whereHas('section.department.businessUnit.company', function($query2) use ($data){
                   $query2->where('companies.id', '=', $data['machine']->section->department->businessUnit->company->id);
                });
            })->get();
            $data['operator'] = Users::where('login','=',Session::get('user_name'))->first();
               
            $data['materialCombinations'] = MaterialCombination::with('process')->whereHas('process', function($query) use ($data){
                $query->whereHas('section.department.businessUnit.company', function($query2) use ($data){
                    $query2->where('companies.id', '=', $data['machine']->section->department->businessUnit->company->id);
                });
            })->get();
            $data['processes'] = Process::all();
            $data['products'] = Product::with('process')->whereHas('process', function($query) use ($data){
                $query->whereHas('section.department.businessUnit.company', function($query2) use ($data){
                    $query2->where('companies.id', '=', $data['machine']->section->department->businessUnit->company->id);
                });
            })->with('sleeves')->get();
//            dd($data['products'][0]);
           return view('roto.select-job', $data);
        }
        else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
	}

	public function submitJob(Request $request){
		SubmitJob:
		$machine = Machine::find($request->input('machine'));
		
		if($machine){
            $job = Job::find($request->input('job'));
           // return $job;
            $loginRecord = LoginRecord::where('machine_id', '=', $machine->id)->first();
            if($loginRecord && $job){
                if($loginRecord->job_id == $job->id){
                    Session::flash('error','Selected job is already running on this machine.');
                    return redirect('dashboard'.'/'.Crypt::encrypt($machine->id));
                }
            }
            if(isset($job)){
                $product = Product::find($job->product_id);
               // dump($product);
                if(isset($product)){
                    $material_combination = MaterialCombination::find($request->input('material_combination'));
                    if(!isset($material_combination)){
                        $material_combination = MaterialCombination::where('name','=',$request->input('material_combination'))->first();
                    }
                    if(isset($material_combination)){
                        //dump($material_combination);
                        $process = Process::find($request->input('process'));
                        if(!isset($process)){
                            $process = Process::where('process_name', '=', $request->input('process'))->first();
                        }
                        if(isset($process)){
                           // dump($process);
                            $processStructure = Process_Structure::where('product_id', '=', $product->id)
                                ->where('process_id', '=', $process->id)
                                ->first();
                            if(isset($processStructure)){
                                //dump($processStructure);
                               // dump($processStructure->material_combination_id);
                                 //dump($material_combination->id);
                                if($processStructure->material_combination_id == $material_combination->id){
                                    //dump($material_combination->id);
                                    /*$lastCounter = Record::select('length')->where('job_id', '=', $request->input('job_id'))->latest('run_date_time')->first();*/
                                    //dump($lastCounter);
                                    $lastCounter = Record::select('length')->where('job_id', '=', $request->input('job_id'))->orderby('run_date_time', 'DESC')->get();
                                    //dump($lastCounter);
                                    if(isset($lastCounter) && $machine->roller_circumference > 0){
                                        $length = $lastCounter->length/$machine->roller_circumference;
                                    }
                                    else{
                                        $length = 0;
                                    }
                                    try{
                                        $logger = new LoggerController($machine);

                                        $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> STARTING JOB CHANGE >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>', $machine);
                                       
                                            $url = 'http://' . $machine->ip . '/cnt/' . $length;
                                            $logger->log('Machine: ' . json_encode($machine), $machine);
                                            $logger->log('Login Record: ' . json_encode($loginRecord), $machine);
                                            $logger->log('Calling URL: ' . $url, $machine);

                                            $changeJob = curl_init();
                                            curl_setopt($changeJob, CURLOPT_URL, $url);
                                            curl_setopt($changeJob, CURLOPT_CONNECTTIMEOUT, 0);
                                            curl_setopt($changeJob, CURLOPT_TIMEOUT, 10);
                                            curl_exec($changeJob);
                                            if ($error = curl_error($changeJob)) {
                                                $logger->log('-------------ERROR EXCEPTION WHILE CHANGING CIRCUIT COUNTER-------------', $machine);
                                                $logger->log('Machine SAP Code: ' . $machine->sap_code, $machine);
                                                $logger->log('IP Address: ' . $machine->ip, $machine);
                                                $logger->log('Error Description: ' . $error, $machine);
                                                $logger->log('-------------ERROR EXCEPTION WHILE CHANGING CIRCUIT COUNTER-------------', $machine);
                                                try {
                                                    $data['machine'] = $machine;
                                                    $data['error'] = $error;
                                                    $data['operationTried'] = 'Counter reset while changing/submission of a Job';
                                                    //haseeb
                                                    //$mail = new MailController();
                                                    //$mail->send('curl-error','Communication Error with '.$machine->sap_code.' Circuit',$data);
                                                    //haseeb
                                                } catch (Exception $e) {
                                                    $logger->log('-------------ERROR EXCEPTION WHILE SENDING EMAIL-------------', $machine);
                                                    $logger->log('Error Code: ' . $e->getCode(), $machine);
                                                    $logger->log('Error Description: ' . $e->getMessage(), $machine);
                                                    $logger->log('-------------ERROR EXCEPTION WHILE SENDING EMAIL-------------', $machine);
                                                }
                                            }
                                            $logger->log('Closing Connection.', $machine);
                                            curl_close($changeJob);
                                        
                            
                                    
                                    }
                                    catch(Exception $e){
                                        $logger->log('Closing Connection.', $machine);
                                        curl_close($changeJob);
                                        $logger->log('-------------ERROR EXCEPTION WHILE CHANGING CIRCUIT COUNTER-------------', $machine);
                                        $logger->log('Error Code: '.$e->getCode(), $machine);
                                        $logger->log('Error Description: '.$e->getMessage(), $machine);
                                        $logger->log('Other Possibility: '.'Circuit not reachable', $machine);
                                        $logger->log('-------------ERROR EXCEPTION WHILE CHANGING CIRCUIT COUNTER-------------', $machine);
                                        try{
                                            $data['machine'] = $machine;
                                            $data['error'] = $e->getMessage();
                                            $data['operationTried'] = 'Counter reset while changing/submission of a Job';
                                            //haseeb
                                            //$mail = new MailController();
                                            //$mail->send('curl-error','Communication Error / Circuit Unreachable with '.$machine->sap_code.' Circuit',$data);
                                        }
                                        catch(Exception $e){
                                            $logger->log('-------------ERROR EXCEPTION WHILE SENDING EMAIL-------------', $machine);
                                            $logger->log('Error Code: '.$e->getCode(), $machine);
                                            $logger->log('Error Description: '.$e->getMessage(), $machine);
                                            $logger->log('-------------ERROR EXCEPTION WHILE SENDING EMAIL-------------', $machine);
                                        }
                                    }
                                    if(isset($loginRecord)){
                                        $logger->log('Updating Login Record with Job '.json_encode($job), $machine);
                                        $logger->log('Updating Login Record with Process '.json_encode($process), $machine);
                                        $loginRecord->job_id = $job->id;
                                        $loginRecord->process_id = $process->id;
                                        $loginRecord->save();
                                    }
                                    else{
                                        $logger->log('Making New Login Record with Job '.json_encode($job), $machine);
                                        $logger->log('Making New Login Record with Process '.json_encode($process), $machine);
                                        $logger->log('Making New Login Record with User ID '.json_encode($request->input('user')), $machine);

                                        $loginRecord = new LoginRecord();
                                        $loginRecord->machine_id = $machine->id;
                                        $loginRecord->job_id = $job->id;
                                        $loginRecord->user_id = $request->input('user');
                                        $loginRecord->process_id = $process->id;
                                        $loginRecord->save();
                                    }
                                    Session::put("job_id", $request->input('job'));
                                    /*$logger->log('Creating New Job Changer Record.', $machine);

                                    $record = new Record();
                                    $record->user_id = $loginRecord->user_id;
                                    $record->error_id = 500;
                                    $record->job_id = $loginRecord->job_id;
                                    $record->machine_id = $loginRecord->machine_id;
                                    $record->speed = 0;
                                    $record->length = 0;
                                    $record->run_date_time = date('Y-m-d H:i:s');
                                    $record->process_id = $loginRecord->process_id;
                                    $record->save();*/
                                    $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> END JOB CHANGE >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>', $machine);
                                    return redirect('dashboard'.'/'.Crypt::encrypt($machine->id));
                                }
                                else{
                                    $material = MaterialCombination::where('id','=',$processStructure->material_combination_id)->first();
                                    if(isset($material)){
                                    Session::flash('error','Please select another process. Material Combination '.$material->name.' already exists against this Product and Process');
                                    }
                                    else{
                                    Session::flash('error','Material Combination selected does not exist. Please ask the power user to add it.');
                                    }
                                    return Redirect::back();
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
                    Session::flash('error','Please select a valid Product');
                    return Redirect::back();
                }
            }
            else{
                Session::flash('error','Please select a valid Job');
                return Redirect::back();
            }
        }
		else{
            Session::flash('error','Machine is not valid.');
            return Redirect::back();
        }
	}

	public function changeJob(){
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
		$data['user'] = Users::find(Session::get('user_name'));
		$data['record'] = Record::where('machine_id', '=', Session::get('machine_id'))->latest('run_date_time')->first();
		$data['products'] = Product::all();
		$data['processes'] = Process::all();
		$data['machine'] = Machine::find(Session::get('machine_id'));
		$data['materialCombinations'] = MaterialCombination::all();
		return view('roto.change-job', $data);
	}

	public function storeChangeJob(Request $request){
		$data = '';
		$lastCounter = Record::select('length')->where('job_id', '=', $request->input('job_id'))->orderby('run_date_time', 'DESC')->limit(1)->get();
		$machine = Machine::select('ip', 'roller_circumference')->where('id', '=', Session::get('machine_id'))->get();
            $url = 'http://' . $machine[0]->ip . '/cnt/' . ($lastCounter[0]->length) / $machine[0]->roller_circumference;
            $old_data = curl_init($url);
            curl_exec($old_data);
            $loginRecord = LoginRecord::where('machine_id', '=', Session::get('machine_id'))->get();
            if (count($loginRecord) == 1) {
                $loginRecord[0]->job_id = $request->input('job_id');
                $loginRecord[0]->process_id = $request->input('process_id');
                $loginRecord[0]->save();
                Session::forget('job_id');
                Session::put("job_id", $request->input('job_id'));
                $data['loginRecord'] = $loginRecord;
                Session::flash('success', 'Job Changed Successfuly');
                return redirect('dashboard');
            } else {
                Session::flash('error', 'Job Not Changed');
                return redirect('dashboard');
            }
        
	}

	public function newProduction(Request $request){
        $names = array(
            "product_number" => "Product Number",
            "product_name" => "Product Name",
            "job_card_number" => "Job Card Number",
            "job_length" => "Job Length",
            "slitted_reel_width" => "Slitted reel width",
            "col" => "Cut of length",
            "gsm" => "Grammage",
            "trim_width" => "Trim Width",
            "density" => "Product Density",
            "thickness" => "Product Thickness",
            "sleeve_id" => "Sleeve Speed",
            "material_combination"=>"Material Combination"
        );

        $validator = Validator::make($request->all(), [
            "product_name" => "required",
            "product_number" => "required",
            "job" => "required",
            "job_length" => "required",
            "material_combination" => "required",
            "thickness" => "required_with_all:density",
        ]);

		$validator->setAttributeNames($names);
		if ($validator->fails()) {
			Session::flash("error", "Please fill in the valid information");
			return Redirect::back() ->withErrors($validator) ->withInput();
		}
		try {
            SubmitJob:
            $machine = Machine::find($request->input('machine'));
            //dump($machine);
			if($machine){
                $job = Job::find($request->input('job'));
                $loginRecord = LoginRecord::where('machine_id', '=', $machine->id)->first();
               // dump($job);
            //    dump($loginRecord);
                if($loginRecord && $job){
                    if($loginRecord->job_id == $job->id){
                        Session::flash('error','Selected job is already running on this machine.');
                        return redirect('dashboard'.'/'.Crypt::encrypt($machine->id));
                    }
                }
                if(isset($job)){
                    $product = Product::find($job->product_id);
              //      dump($product);
                    if(isset($product)){
                        $material_combination = MaterialCombination::find($request->input('material_combination'));
                //        dump($material_combination);
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
                              //      dump($processStructure);
                                if(isset($processStructure)){
                                    if($processStructure->material_combination_id == $material_combination->id){
                                        $lastCounter = Record::select('length')->where('job_id', '=', $request->input('job_id'))->orderby('run_date_time', 'DESC')->first();
                                        if(isset($lastCounter) && $machine->roller_circumference > 0){
                                            $length = $lastCounter->length/$machine->roller_circumference;
                                        }
                                        else{
                                            $length = 0;
                                        }
                                        try{
                                            $logger = new LoggerController($machine);

                                            $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> STARTING JOB CHANGE >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>', $machine);
                                            $url = 'http://'.$machine->ip.'/cnt/'.$length;
                                            $logger->log('Machine: '. json_encode($machine), $machine);
                                            $logger->log('Login Record: '. json_encode($loginRecord), $machine);
                                            $logger->log('Calling URL: '. $url, $machine);

                                            $changeJob = curl_init();
                                            curl_setopt($changeJob, CURLOPT_URL, $url);
                                            curl_setopt($changeJob, CURLOPT_CONNECTTIMEOUT, 0);
                                            curl_setopt($changeJob,CURLOPT_TIMEOUT,10);
                                            curl_exec($changeJob);
                                            if ($error = curl_error($changeJob)) {
                                                $logger->log('-------------ERROR EXCEPTION WHILE CHANGING CIRCUIT COUNTER-------------', $machine);
                                                $logger->log('Machine SAP Code: '.$machine->sap_code, $machine);
                                                $logger->log('IP Address: '.$machine->ip, $machine);
                                                $logger->log('Error Description: '.$error, $machine);
                                                $logger->log('-------------ERROR EXCEPTION WHILE CHANGING CIRCUIT COUNTER-------------', $machine);
                                                try{
                                                    $data['machine'] = $machine;
                                                    $data['error'] = $error;
                                                    $data['operationTried'] = 'Counter reset while changing/submission of a Job';
                                                    //haseeb
                                                    //$mail = new MailController();
                                                    //$mail->send('curl-error','Communication Error with '.$machine->sap_code.' Circuit',$data);
                                                }
                                                catch(Exception $e){
                                                    $logger->log('-------------ERROR EXCEPTION WHILE SENDING EMAIL-------------', $machine);
                                                    $logger->log('Error Code: '.$e->getCode(), $machine);
                                                    $logger->log('Error Description: '.$e->getMessage(), $machine);
                                                    $logger->log('-------------ERROR EXCEPTION WHILE SENDING EMAIL-------------', $machine);
                                                }

                                            }
                                            $logger->log('Closing Connection.', $machine);
                                            curl_close($changeJob);
                                        }
                                        catch(Exception $e){
                                            $logger->log('Closing Connection.', $machine);
                                            curl_close($changeJob);
                                            $logger->log('-------------ERROR EXCEPTION WHILE CHANGING CIRCUIT COUNTER-------------', $machine);
                                            $logger->log('Error Code: '.$e->getCode(), $machine);
                                            $logger->log('Error Description: '.$e->getMessage(), $machine);
                                            $logger->log('Other Possibility: '.'Circuit not reachable', $machine);
                                            $logger->log('-------------ERROR EXCEPTION WHILE CHANGING CIRCUIT COUNTER-------------', $machine);
                                            try{
                                                $data['machine'] = $machine;
                                                $data['error'] = $e->getMessage();
                                                $data['operationTried'] = 'Counter reset while changing/submission of a Job';
                                                //haseeb
                                                //$mail = new MailController();
                                                //$mail->send('curl-error','Communication Error / Circuit Unreachable with '.$machine->sap_code.' Circuit',$data);
                                            }
                                            catch(Exception $e){
                                                $logger->log('-------------ERROR EXCEPTION WHILE SENDING EMAIL-------------', $machine);
                                                $logger->log('Error Code: '.$e->getCode(), $machine);
                                                $logger->log('Error Description: '.$e->getMessage(), $machine);
                                                $logger->log('-------------ERROR EXCEPTION WHILE SENDING EMAIL-------------', $machine);
                                            }
                                        }
                                        Session::put("job_id", $request->input('job'));
                                        if(isset($loginRecord)){
                                            $logger->log('Updating Login Record with Job '.json_encode($job), $machine);
                                            $logger->log('Updating Login Record with Process '.json_encode($process), $machine);
                                            $loginRecord->job_id = $job->id;
                                            $loginRecord->process_id = $process->id;
                                            $loginRecord->save();
                                        }
                                        else{
                                            $logger->log('Making New Login Record with Job '.json_encode($job), $machine);
                                            $logger->log('Making New Login Record with Process '.json_encode($process), $machine);
                                            $logger->log('Making New Login Record with User ID '.json_encode($request->input('user')), $machine);

                                            $loginRecord = new LoginRecord();
                                            $loginRecord->machine_id = $machine->id;
                                            $loginRecord->job_id = $job->id;
                                            $loginRecord->user_id = $request->input('user');
                                            $loginRecord->process_id = $process->id;
                                            $loginRecord->save();
                                        }
                                        /*$logger->log('Creating New Job Changer Record.', $machine);
                                        $record = new Record();
                                        $record->user_id = $loginRecord->user_id;
                                        $record->error_id = 500;
                                        $record->job_id = $loginRecord->job_id;
                                        $record->machine_id = $loginRecord->machine_id;
                                        $record->speed = 0;
                                        $record->length = 0;
                                        $record->run_date_time = date('Y-m-d H:i:s');
                                        $record->process_id = $loginRecord->process_id;
                                        $record->save();*/
                                        $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> END JOB CHANGE >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>', $machine);
                                        return redirect('dashboard'.'/'.Crypt::encrypt($machine->id));
                                    }
                                    else{
                                        Session::flash('error','Please select another process. Material Combination '.$material_combination->name.' already exists against this Product and Process');
                                        return Redirect::back();
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
                        $product->slitted_reel_width = $request->input('slitted_reel_width')/1000;
                        $product->col = $request->input('col')/1000;
                        $product->gsm = $request->input('gsm')/1000;
                        $product->trim_width = $request->input('trim_width')/1000;
                        $product->thickness = $request->input('thickness')/1000000;
                        $product->density = $request->input('density');
                        $product->ups = $request->input('ups');
                        $product->save();
                        if($request->input('sleeve_id')!=null&&$request->input('sleeve_id')!='')
                        {
                            $product->sleeves()->attach($request->input('sleeve_id'));
                        }
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
                Session::flash('error','Machine is not valid.');
                return Redirect::back();
            }
		}
		catch (ModelNotFoundException $e)
		{
			Session::flash("error", "Error Submitting Production Order");
			return Redirect::back();
		}
	}

	public function OldNewProduction(Request $request){
		$names = array(
			"product_number" => "Product Number",
			"product_name" => "Product Name",
			"color_adhesive" => "Colors/Adhesive",
			"materialCombination" => "Material Combination",
			"job_card_number" => "Job Card Number",
			"job_length" => "Job Length",
		);
		$validator = Validator::make($request->all(), [
			"product_name" => "required",
			"product_number" => "required",
			"color_adhesive" => "required",
			"materialCombination" => "required",
			"job_card_number" => "required",
			"job_length" => "required",
		]);
		$validator->setAttributeNames($names);
		if ($validator->fails()) {
			Session::flash("error", "Please fill in the valid information");
			return Redirect::back() ->withErrors($validator) ->withInput();
		}
		try {
			$product = Product::find($request->product_number);
			$machine_id = Session::get('machine_id');
			if(count($product) == 0){
				$product = new Product();
				$product->id = $request->input('product_number');
				$product->name = $request->input('product_name');
				$product->color_adh = $request->input('color_adhesive');
				$product->material_combination_id = $request->input('materialCombination');
				$product->save();

				$job = new Job();
				$job->id = $request->input('job_card_number');
				$job->job_length = $request->input('job_length');
				$job->product_id = $request->input('product_number');
				$job->save();

				$machine = Machine::select('ip')->where('id', '=', Session::get('machine_id'))->get();
				$url = 'http://'.$machine[0]->ip.'/cnt/0';
				$old_data = curl_init($url);
				curl_exec($old_data);
				$loginRecord = LoginRecord::where('machine_id', '=', Session::get('machine_id'))->get();
				if(count($loginRecord) == 1){
					$loginRecord[0]->job_id = $request->input('job_card_number');
					$loginRecord[0]->save();
					Session::forget('job_id');
					Session::put("job_id", $request->input('job_id'));
				}
				else{
					$loginRecord = new LoginRecord();
					$loginRecord->job_id = $request->input('job_card_number');
					$loginRecord->machine_id = Session::get('machine_id');
					$loginRecord->user_id = Session::get('user_name');
					$loginRecord->save();
					Session::forget('job_id');
					Session::put("job_id", $request->input('job_id'));
				}
			}
			else{
				$job = Job::find($request->input('job_card_number'));
				if(count($job) == 0){
					$job = new Job();
					$job->id = $request->input('job_card_number');
					$job->job_length = $request->input('job_length');
					$job->product_id = $product->id;
					$job->save();

					$machine = Machine::select('ip')->where('id', '=', Session::get('machine_id'))->get();
					$url = 'http://'.$machine[0]->ip.'/cnt/0';
					$old_data = curl_init($url);
					curl_exec($old_data);
					$loginRecord = LoginRecord::where('machine_id', '=', Session::get('machine_id'))->get();
					if(count($loginRecord) == 1){
						$loginRecord[0]->job_id = $request->input('job_card_number');
						$loginRecord[0]->save();
						Session::forget('job_id');
						Session::put("job_id", $request->input('job_id'));
					}
					else{
						$loginRecord = new LoginRecord();
						$loginRecord->job_id = $request->input('job_card_number');
						$loginRecord->machine_id = Session::get('machine_id');
						$loginRecord->user_id = Session::get('user_name');
						$loginRecord->save();
						Session::forget('job_id');
						Session::put("job_id", $request->input('job_id'));
					}
				}
				else{
					$lastCounter = Record::select('length')->where('job_id', '=', $job->id)->where('machine_id', '=', $machine_id)->orderby('run_date_time', 'DESC')->limit(1)->get();
					$machine = Machine::select('ip', 'roller_circumference')->where('id', '=', Session::get('machine_id'))->get();
					if(count($lastCounter) > 0){
						$url = 'http://'.$machine[0]->ip.'/cnt/'.($lastCounter[0]->length)/$machine[0]->roller_circumference;
					}
					else{
						$url = 'http://'.$machine[0]->ip.'/cnt/0';
					}
					$old_data = curl_init($url);
					curl_exec($old_data);
					$loginRecord = LoginRecord::where('machine_id', '=', Session::get('machine_id'))->get();
					if(count($loginRecord) == 1){
						$loginRecord[0]->job_id = $request->input('job_card_number');
						$loginRecord[0]->save();
						Session::forget('job_id');
						Session::put("job_id", $request->input('job_id'));
					}
					else{
						$loginRecord = new LoginRecord();
						$loginRecord->job_id = $request->input('job_card_number');
						$loginRecord->machine_id = Session::get('machine_id');
						$loginRecord->user_id = Session::get('user_name');
						$loginRecord->save();
						Session::forget('job_id');
						Session::put("job_id", $request->input('job_id'));
					}
				}
			}
			return redirect('dashboard');
		}
		catch (ModelNotFoundException $e)
		{
			Session::flash("error", "Error Submitting Production Order");
			return Redirect::back();
		}
	}

	public function checkProductProcess(Request $request){
		$product = Product::where('id','=',$request->input('product_id'))->first();
		if(isset($product)){
			$process_id = $request->input('process_id');
			$product_id = $product->id;
			$process_structure = Process_Structure::where('process_id', '=', $process_id)
				->where('product_id', '=', $product_id)
				->first();
			if(isset($process_structure)){
				return response(json_encode($process_structure, 200));
			}
			else{
				return response(json_encode('Structure Not Found'), 200);
			}
		}
		else{
			return json_encode('Product Not Found'. $product, 200);
		}
	}

public function job_details($id){

        $data['user'] = Users::find(Session::get('user_name'));

       

$data['machine'] = Machine::with('loginRecord.job')->where('id','=',(Crypt::decrypt($id)))->first();

$data['path'] = Route::getFacadeRoot()->current()->uri();

$machine_id = Crypt::decrypt($id);

if(Session::get('rights') == 0){

$data['layout'] = 'web-layout';

}

elseif(Session::get('rights') == 1){

$data['layout'] = 'admin-layout';

}

elseif(Session::get('rights') == 2){

$data['layout'] = 'power-user-layout';

}

$loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();

//$data['machine'] = Machine::find($machine_id);

if(Session::get('rights') == 0){

$data['user'] = Users::find($loginRecord[0]->user_id);

}

else{

$data['user'] = Users::find(Session::get('user_name'));

}

$data['record'] = Record::where('machine_id', '=', $machine_id)->latest('run_date_time')->first();

$data['operators'] = Users::with('allowedMachines')->whereHas('allowedMachines', function($query) use ($machine_id){

$query->where('machine_id', '=', $machine_id);

})->where('rights', '=', 0)->get();

$data['errorCodes'] = $data['machine']->section->department->errorCodes;

$data['productionOrders'] = Job::all();

return view('roto.job',$data);

}



public function update_job(Request $request,$id){





                    $job=array(

                       "jobID"=>"id",

                       "ups"=>"UPS",

                       "reel_width"=>"Reel Width",

                       "trimWidth"=>"Trim Width",

                       "gsm"=>"GSM",

                       "thickness"=>"Thickness",

                       "density"=>"Density",

        );

        $validator=Validator::make($request->all(),

                       [

                                      "jobID"=>"required",

                            "ups"=>"required",

                           "reel_width"=>"required",

                           "trimWidth"=>"required",

                           "gsm"=>"required",

                           "thickness"=>"required",

                           "density"=>"required",

                       ]);

                      

        $validator->setAttributeNames($job);

        if($validator->fails())

        {

                       return Redirect::back()->withErrors($validator)->withInput();

        }

        else{

            try{

            $jobs = Job::find($request->input('jobID'));

            $jobs->ups = $request->input('ups');

            $jobs->gsm = $request->input('gsm')/1000;

            $jobs->reel_width = $request->input('reel_width');

            $jobs->trim_width = $request->input('trimWidth')/1000;

            $jobs->thickness = $request->input('thickness')/1000000;

            $jobs->density = $request->input('density');

            $jobs->save();

       

        Session::flash('success', 'The job has been updated.');

        return Redirect::back();

        }                            

        catch (QueryException $e) {

                                      Session::flash('error', 'Please contact System Administrator with code ' . '<strong>' . $e->getMessage() . '</strong>');

                                      return Redirect::back()->withInput();


                       }

        }

        }


}
