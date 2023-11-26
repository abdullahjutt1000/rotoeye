<?php namespace App\Http\Controllers;

use App\Models\CircuitRecords;
use App\Models\Department_Error;


use App\Models\Error;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\LoginRecord;
use App\Models\Machine;
use App\Models\Record;
use App\Models\Shift;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
////// mine code 
use App\Models\Settings;
/////

use Route;
use Mail;
class DowntimeController extends Controller
{

    /// mine code 


    public function updatenumberdays($id)
    {   
       
        $user_id = Session::get('user_id');
        if (isset($user_id)) {

            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $machine_id = Crypt::decrypt($id);
            if (Session::get('rights') == 0) {
                $data['layout'] = 'web-layout';
            } elseif (Session::get('rights') == 1) {
                $data['layout'] = 'admin-layout';
            } elseif (Session::get('rights') == 2) {
                $data['layout'] = 'power-user-layout';
            }
            $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();
            $data['machine'] = Machine::find($machine_id);
            if (Session::get('rights') == 0) {
                $data['user'] = Users::find($loginRecord[0]->user_id);
            } else {
                $data['user'] = Users::find(Session::get('user_name'));
            }
            $data["no_of_days"] = Settings::where(["title"=>"not_responding_circuts_no_of_days"])->first();
            //dd($data);
            
           
            return view('settings.no_of_days', $data);
        } else {
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }

    public function numberdaysupdate(Request $request,$id,$machine_id)
    {   
       
        $user_id = Session::get('user_id');
        if (isset($user_id)) {
            $settings = Settings::where("id",$id)->first();

            $settings->value = $request->value;
            $settings->save();
            Session::flash("success", "Record has been updated");
            return redirect()->back();
           
        } else {
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }


    public function reportwitherrorsIds($id)
    {   
       
        $user_id = Session::get('user_id');
        if (isset($user_id)) {

            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $machine_id = Crypt::decrypt($id);
            if (Session::get('rights') == 0) {
                $data['layout'] = 'web-layout';
            } elseif (Session::get('rights') == 1) {
                $data['layout'] = 'admin-layout';
            } elseif (Session::get('rights') == 2) {
                $data['layout'] = 'power-user-layout';
            }
            $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();
            $data['machine'] = Machine::find($machine_id);
            if (Session::get('rights') == 0) {
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
           
            return view('downtime.get-downtime-errors-filter', $data);
        } else {
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }

    public function allocateDownTImeManuallywitherrorsIds(Request $request, $id)
    {
        
        $data['codes']=$request->errorsCodes;
        
        $machine = Machine::find(Crypt::decrypt($id));
         
        $data['record'] = Record::where('machine_id', '=', $machine->id)->latest('run_date_time')->first();
         
        $date = $request->input('date');
        $shiftSelection = $request->input('shiftSelection');
        if ($shiftSelection[0] == 'All-Day') {
            $shifts_id = [];
            foreach ($machine->section->department->businessUnit->company->shifts as $shift) {
                array_push($shifts_id, $shift->id);
            }

            $minStarted = Shift::find($shifts_id[0])->min_started;
            $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;


            $from_date = $request->input('date');
            $to_date = $request->input('to_date');

            $data['from'] = $from_date;
            $data['to'] = $to_date;

            $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'] . ' + ' . $minStarted . ' minutes'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'] . ' + ' . $minEnded . ' minutes'));

        } else {
            
            $minStarted = Shift::find($shiftSelection[0])->min_started;
            $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;

            $startDateTime = date('Y-m-d H:i:s', strtotime($date . ' + ' . $minStarted . ' minutes'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($date . ' + ' . $minEnded . ' minutes'));
        }
       

        if (date('Y-m-d H:i:s') < $endDateTime) {
            $endDateTime = date('Y-m-d H:i:s');
        }
        
        $from_date = $startDateTime;
        $to_date = $endDateTime;
        $rights = '';
        if (Session::get('rights') == 0) {
            $rights='24 Hours';
        } elseif (Session::get('rights') == 1) {
            $rights='1344 Hours';
           
        } elseif (Session::get('rights') == 2) {
            $rights='1344 Hours';
        }  
        // my if($from_date>date('Y-m-d H:i:s', strtotime( '-'.$rights, strtotime(date('Y-m-d H:i:s')))))
        //my {
            $data['machine'] = $machine;
            $data['user'] = Users::where('login','=',Session::get('user_name'))->first();
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $data['from'] = $startDateTime;
            $data['to'] = $endDateTime;
            $data['errors']=Department_Error::where('department_id','=',$machine->section->department->id)
                ->leftJoin('errors', 'department_error.error_id', '=', 'errors.id')->get();
           //$data['errors']=Error::all();

            $error_id = 2;
            
            $records = DB::table('records')
                ->join('errors', 'errors.id', '=', 'records.error_id')
                ->join('users', 'users.id', '=', 'records.user_id')
                ->join('jobs', 'jobs.id', '=', 'records.job_id')
                ->join('products', 'products.id', '=', 'jobs.product_id')
                ->select('errors.id as error_id', 'errors.name as error_name', 'records.run_date_time as run_date_time', 'records.err_comments as err_comments', 'products.name as product_name',
                    'products.id as product_id', 'users.name as user_name', 'users.id as user_id', 'jobs.id as job_id')
                ->where('machine_id', '=', $data['machine']->id)
                ->where('run_date_time', '>=', $from_date)
                ->where('run_date_time', '<=', $to_date)
                ->orderby('run_date_time', 'ASC')
                ->get();
            
            
            //dd(count($records));
            if (count($records) > 0) {
                $data['records'] = [];
                $startDate = $records[0]->run_date_time;
                for ($i = 0; $i < count($records); $i++) {
                    if (isset($records[$i + 1])) {
                        if($records[$i]->error_id != $records[$i+1]->error_id || $records[$i]->user_id != $records[$i+1]->user_id || $records[$i]->job_id != $records[$i+1]->job_id){

                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                            if ($records[$i]->error_id != $error_id) {
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
                         //  $startDate = $records[$i + 1]->run_date_time;
                            $startDate = $endDate;
                        }
                    } else {

                        $endDate = $records[$i]->run_date_time;
                        $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                        $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                        if ($records[$i]->error_id != $error_id) {

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
                if (Session::get('rights') == 0) {
                    $data['layout'] = 'web-layout';
                } elseif (Session::get('rights') == 1) {
                    $data['layout'] = 'admin-layout';
                } elseif (Session::get('rights') == 2) {
                    $data['layout'] = 'power-user-layout';
                }
                $data['error'] = Error::find($error_id);
                //return $data;
               
                return view('downtime.downtime-report-with-errors-filters', $data);
            }
            else {
                Session::flash("error", "No Record for the selected shift and date. Please try again.");
                return Redirect::back();
            }
        // my }
        // my else
        // my {
        // my     Session::flash("error", "Can not select more then ".$rights." from today.");
        // my     return Redirect::back();
        // my }

    }
    /// end of mine 
    
    public function report($id)
    {   
        $user_id = Session::get('user_id');
        if (isset($user_id)) {

            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $machine_id = Crypt::decrypt($id);
            if (Session::get('rights') == 0) {
                $data['layout'] = 'web-layout';
            } elseif (Session::get('rights') == 1) {
                $data['layout'] = 'admin-layout';
            } elseif (Session::get('rights') == 2) {
                $data['layout'] = 'power-user-layout';
            }
            $loginRecord = LoginRecord::where('machine_id', '=', $machine_id)->get();
            $data['machine'] = Machine::find($machine_id);
            if (Session::get('rights') == 0) {
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
            
            return view('downtime.get-downtime', $data);
        } else {
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }
    
    public function allocateDownTImeManually(Request $request, $id)
    {
       
        $machine = Machine::find(Crypt::decrypt($id));
        //dd(Crypt::decrypt($id));
        $data['record'] = Record::where('machine_id', '=', $machine->id)->latest('run_date_time')->first();
        //dd($data['record']);
        //dd($request->all());
        $date = $request->input('date');
        $shiftSelection = $request->input('shiftSelection');
        if ($shiftSelection[0] == 'All-Day') {
            $shifts_id = [];
            foreach ($machine->section->department->businessUnit->company->shifts as $shift) {
                array_push($shifts_id, $shift->id);
            }

            $minStarted = Shift::find($shifts_id[0])->min_started;
            $minEnded = Shift::find($shifts_id[count($shifts_id) - 1])->min_ended;


            $from_date = $request->input('date');
            $to_date = $request->input('to_date');

            $data['from'] = $from_date;
            $data['to'] = $to_date;

            $startDateTime = date('Y-m-d H:i:s', strtotime($data['from'] . ' + ' . $minStarted . ' minutes'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($data['to'] . ' + ' . $minEnded . ' minutes'));

        } else {
            
            $minStarted = Shift::find($shiftSelection[0])->min_started;
            $minEnded = Shift::find($shiftSelection[count($shiftSelection) - 1])->min_ended;

            $startDateTime = date('Y-m-d H:i:s', strtotime($date . ' + ' . $minStarted . ' minutes'));
            $endDateTime = date('Y-m-d H:i:s', strtotime($date . ' + ' . $minEnded . ' minutes'));
        }
        

        if (date('Y-m-d H:i:s') < $endDateTime) {
            $endDateTime = date('Y-m-d H:i:s');
        }
      
        $from_date = $startDateTime;
        $to_date = $endDateTime;
        $rights = '';
        if (Session::get('rights') == 0) {
            $rights='24 Hours';
        } elseif (Session::get('rights') == 1) {
            $rights='1344 Hours';
           
        } elseif (Session::get('rights') == 2) {
            $rights='1344 Hours';
        }
           if($from_date>date('Y-m-d H:i:s', strtotime( '-'.$rights, strtotime(date('Y-m-d H:i:s')))))
          {
            $data['machine'] = $machine;
            $data['user'] = Users::where('login','=',Session::get('user_name'))->first();
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $data['from'] = $startDateTime;
            $data['to'] = $endDateTime;
            $data['errors']=Department_Error::where('department_id','=',$machine->section->department->id)
                ->leftJoin('errors', 'department_error.error_id', '=', 'errors.id')->get();
           //$data['errors']=Error::all();

            $error_id = 2;
            
            $records = DB::table('records')
                ->join('errors', 'errors.id', '=', 'records.error_id')
                ->join('users', 'users.id', '=', 'records.user_id')
                ->join('jobs', 'jobs.id', '=', 'records.job_id')
                ->join('products', 'products.id', '=', 'jobs.product_id')
                ->select('errors.id as error_id', 'errors.name as error_name', 'records.run_date_time as run_date_time', 'records.err_comments as err_comments', 'products.name as product_name',
                    'products.id as product_id', 'users.name as user_name', 'users.id as user_id', 'jobs.id as job_id')
                ->where('machine_id', '=', $data['machine']->id)
                ->where('run_date_time', '>=', $from_date)
                ->where('run_date_time', '<=', $to_date)
                ->orderby('run_date_time', 'ASC')
                ->get();
            
            
           
            if (count($records) > 0) {
                $data['records'] = [];
                $startDate = $records[0]->run_date_time;
                for ($i = 0; $i < count($records); $i++) {
                    if (isset($records[$i + 1])) {
                        if($records[$i]->error_id != $records[$i+1]->error_id || $records[$i]->user_id != $records[$i+1]->user_id || $records[$i]->job_id != $records[$i+1]->job_id){

                            $endDate = $records[$i]->run_date_time;
                            $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                            $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                            if ($records[$i]->error_id != $error_id) {
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
                         //  $startDate = $records[$i + 1]->run_date_time;
                            $startDate = $endDate;
                        }
                    } else {

                        $endDate = $records[$i]->run_date_time;
                        $difference = date_diff(date_create(date('d-M-Y H:i:s', strtotime($startDate))), date_create(date('d-M-Y H:i:s', strtotime($endDate))));
                        $duration = (($difference->y * 365 + $difference->m * 30 + $difference->d) * 24 + $difference->h) * 60 + $difference->i + $difference->s / 60;
                        if ($records[$i]->error_id != $error_id) {

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
                if (Session::get('rights') == 0) {
                    $data['layout'] = 'web-layout';
                } elseif (Session::get('rights') == 1) {
                    $data['layout'] = 'admin-layout';
                } elseif (Session::get('rights') == 2) {
                    $data['layout'] = 'power-user-layout';
                }
                $data['error'] = Error::find($error_id);
                //return $data;

                return view('downtime.downtime-report', $data);
            }
            else {
                Session::flash("error", "No Record for the selected shift and date. Please try again.");
                return Redirect::back();
            }
         }
         else
          {
            Session::flash("error", "Can not select more then ".$rights." from today.");
              return Redirect::back();
          }

    }
    public function allocateDowntimeManual(Request $request){

        $machine = Machine::find($request->input('machine_id'));
        $startDateTime = date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom')));
        $endDateTime = date('Y-m-d H:i:s', strtotime($request->input('downtimeTo')));

        $current_user = Users::find(Session::get('user_name'));
        $logger = new LoggerController($machine);
        $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>ALLOCATE DOWNTIME>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
        $logger->log($current_user['id'].' - '.$current_user['name'],$machine);
        $logger->log(date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom'))) .' - '. date('Y-m-d H:i:s', strtotime($request->input('downtimeTo'))) .' - '. $request->input('downtimeDescription') .' - '.$request->input('machine_id'),$machine);

        //haseeb
      // if($current_user->rights = '0'){
       
       //old if((round(abs(strtotime($request->input('downtimeTo'))-strtotime($request->input('downtimeFrom'))) / 60,2))>1439)
       if((round(abs(strtotime($request->input('downtimeTo'))-strtotime($request->input('downtimeFrom'))) / 60,2))>2879) 
       {
            $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>ALLOCATE DOWNTIME EXCEPTION>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
            $logger->log($current_user['id'].' - '.$current_user['name'],$machine);
            $logger->log(date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom'))) .' - '. date('Y-m-d H:i:s', strtotime($request->input('downtimeTo'))) .' - '. $request->input('downtimeDescription') .' - '.$request->input('machine_id'),$machine);
            $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END ALLOCATE DOWNTIME EXCEPTION>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
            $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END ALLOCATE DOWNTIME>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
            return response("invalid form data",500);
        }
        //haseeb
        $records = Record::where('run_date_time', '>', $startDateTime)
            ->where('run_date_time', '<=', $endDateTime)
            ->where('machine_id', '=', $machine->id)
            ->orderby('run_date_time', 'ASC')
            ->get();

        foreach($records as $record){
            $record->error_id = $request->input('downtimeID');
            $record->err_comments = $request->input('downtimeDescription');
            $record->save();
        }
        $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END ALLOCATE DOWNTIME>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
        return response(200);
       //}
       
       /*else{
            
       if((round(abs(strtotime($request->input('downtimeTo'))-strtotime($request->input('downtimeFrom'))) / 60,2))>1439000)
        {
            $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>ALLOCATE DOWNTIME EXCEPTION>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
            $logger->log($current_user['id'].' - '.$current_user['name'],$machine);
            $logger->log(date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom'))) .' - '. date('Y-m-d H:i:s', strtotime($request->input('downtimeTo'))) .' - '. $request->input('downtimeDescription') .' - '.$request->input('machine_id'),$machine);
            $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END ALLOCATE DOWNTIME EXCEPTION>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
            $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END ALLOCATE DOWNTIME>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
            return response("invalid form data",500);
        }
        //haseeb
        $records = Record::where('run_date_time', '>', $startDateTime)
            ->where('run_date_time', '<=', $endDateTime)
            ->where('machine_id', '=', $machine->id)
            ->orderby('run_date_time', 'ASC')
            ->get();

        foreach($records as $record){
            $record->error_id = $request->input('downtimeID');
            $record->err_comments = $request->input('downtimeDescription');
            $record->save();
        }
        $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END ALLOCATE DOWNTIME>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
        return response(200);
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

    //Manual Generated Record
    /*public function getMultipleTime(Request $request){

        $records = Record::select('run_date_time as time')
            ->where('run_date_time', '>=', date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom'))))
            ->where('run_date_time', '<=', date('Y-m-d H:i:s', strtotime($request->input('downtimeTo'))))
            ->where('machine_id', '=', $request->input('machine_id'))
            ->get();

        $temp_record=[];
        array_push($temp_record,["time"=>date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom')))]);
        for($i=0; $i<count($records); $i++)
        {
            if(isset($records[$i+1]))
            {
                if(strtotime($records[$i]->time)<strtotime($records[$i+1]->time)){

                    while(strtotime("+20 sec",strtotime($temp_record[count($temp_record)-1]["time"]))<strtotime($records[$i+1]->time))
                    {
                        array_push($temp_record,["time"=>date('Y-m-d H:i:s',strtotime("+20 sec",strtotime($temp_record[count($temp_record)-1]["time"])))]);
                    }
                        array_push($temp_record,["time"=>$records[$i+1]->time]);
                }

            }
        }
        $data['times'] = $temp_record;

        return response(json_encode($data),200);
    }

    public function allocateDowntime(Request $request){

        $machine_id = $request->input('machine_id');

        $if_rec_exists = Record::where('run_date_time', '<=', date('Y-m-d H:i:s', strtotime($request->input('downtimeTo'))))
            ->where('machine_id', '=', $machine_id)
            ->orderBy('run_date_time', 'desc')
            ->limit(1)
            ->get();

        if($if_rec_exists[count($if_rec_exists)-1]->run_date_time != date('Y-m-d H:i:s', strtotime($request->input('downtimeTo'))))
        {
            $record = new Record();
            $record->user_id=$if_rec_exists[count($if_rec_exists)-1]->user_id;
            $record->error_id=$request->input('downtimeID');
            $record->job_id=$if_rec_exists[count($if_rec_exists)-1]->job_id;
            $record->machine_id=$if_rec_exists[count($if_rec_exists)-1]->machine_id;
            $record->err_comments=$request->input('downtimeDescription');
            $record->speed=$if_rec_exists[count($if_rec_exists)-1]->speed;
            $record->run_date_time= date('Y-m-d H:i:s', strtotime($request->input('downtimeTo')));
            $record->length= $record->speed==0 ? $if_rec_exists[count($if_rec_exists)-1]->length : $if_rec_exists[count($if_rec_exists)-1]->length + $record->speed*abs(strtotime($request->input('downtimeFrom'))-strtotime($record->run_date_time));
            $record->process_id=$if_rec_exists[count($if_rec_exists)-1]->process_id;
            $record->save();
        }

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
    }*/

    public function allocateDowntime(Request $request){

        $machine = Machine::find($request->input('machine_id'));
        $startDateTime = date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom')));
        $endDateTime = date('Y-m-d H:i:s', strtotime($request->input('downtimeTo')));
        $record = Record::where('run_date_time', '<=', date('Y-m-d H:i:s', strtotime($request->input('downtimeTo'))))
            ->where('machine_id', '=', $machine->id)
            ->orderBy('run_date_time', 'desc')
            ->limit(1)
            ->get();

        //from should be less then to
        if($record->last()->run_date_time < $endDateTime)
        {
            $circuit_record = CircuitRecords::select("LDT as run_date_time","Mtr as length","Rpm as speed")
                ->where("LDT","=",$endDateTime)
                ->where("num_id","=",$machine->sap_code)
                ->get();

            //if not exist
            if(!$circuit_record->isEmpty())
            {
                $new_record = new Record();
                $new_record->user_id=$record->last()->user_id;
                $new_record->error_id=$request->input('downtimeID');
                $new_record->err_comments = $request->input('downtimeDescription');
                $new_record->job_id=$record->last()->job_id;
                $new_record->machine_id=$record->last()->machine_id;
                $new_record->speed=$circuit_record[0]->speed;
                $new_record->run_date_time= $circuit_record[0]->run_date_time;
                $new_record->length= $circuit_record[0]->length;
                $new_record->process_id=$record->last()->process_id;
                $new_record->save();
            }
            else
            {
                return response()->json(["message"=>"Circuit Record Not Found\nPlease Ask administrator to add manual record"],200);
            }


        }
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
        $records = Record::where('run_date_time', '>=', $startDateTime)
            ->where('run_date_time', '<=', $endDateTime)
            ->where('machine_id', '=', $machine->id)
            ->orderby('run_date_time', 'ASC')
            ->get();

        foreach($records as $record){
            $record->error_id = $request->input('downtimeID');
            $record->err_comments = $request->input('downtimeDescription');
            $record->save();
        }
        $logger->log('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END ALLOCATE DOWNTIME>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',$machine);
        return response(200);
    }

    public function getMultipleTime(Request $request)
    {

        $startDateTime = date('Y-m-d H:i:s', strtotime($request->input('downtimeFrom')));
        $endDateTime = date('Y-m-d H:i:s', strtotime($request->input('downtimeTo')));
        $machine = Machine::find($request->input('machine_id'));
        $record = Record::select('speed', 'run_date_time as time', 'length')
            ->where('run_date_time', '<', $startDateTime)
            ->where('machine_id', '=', $machine->id)
            ->limit(1)
            ->orderby('time', 'DESC')
            ->union(CircuitRecords::select('Rpm as speed', 'LDT as time', 'Mtr as length')
                ->where('LDT', '<', $startDateTime)
                ->where('num_id', '=', $machine->sap_code)
                ->orderby('LDT', 'DESC')
                ->limit(1))
            ->orderby('time', 'DESC')
            ->limit(1)
            ->get();

        $records = Record::select('speed', 'run_date_time as time', 'length')
            ->where('run_date_time', '>=', $startDateTime)
            ->where('run_date_time', '<=', $endDateTime)
            ->where('machine_id', '=', $machine->id)
            ->union(CircuitRecords::select('Rpm as speed', 'LDT as time', 'Mtr as length')
                ->where('LDT', '>=', $startDateTime)
                ->where('LDT', '<=', $endDateTime)
                ->where('num_id', '=', $machine->sap_code))
            ->orderby('time', 'ASC')
            ->get();

        for ($i=0;$i<count($records);$i++)
        {
            $records[$i]->length = $records[$i]->length-$record[0]->length;
        }
//        $records[0]->length=0;
        $data['times'] = $records;
        return response(json_encode($data),200);
    }

}
