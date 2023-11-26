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
use App\Models\GroupProductionReport;
use App\Models\Company;
use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Section;
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


class GrpDashboardController extends Controller {

    /**
     * Display a listing of the resource.
     *
     */


    public function index()
    {
        $user_id = Session::get('user_id');
        $data['user'] = Users::find(Session::get('user_id'));
        $data['months'] = array("01"=>"Jan","02"=>"Feb","03"=>"Mar","04"=>"Apr","05"=>"May","06"=>"Jun","07"=>"Jul","08"=>"Aug","09"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dec");
        if($user_id){
          //$data['layout'] = 'group-dashboard-layout';
        if(Session::get('rights') == 1){
            $data['layout'] = 'admin-layout';
        }
        elseif(Session::get('rights') == 2){
            $data['layout'] = 'power-user-layout';
        }
          
          $data['path'] = Route::getFacadeRoot()->current()->uri();
          $data['machines'] = Machine :: whereNull('is_disabled')->get();
          $data['companies'] = Company ::all();
          $data['bu'] = BusinessUnit ::all();
          $data['dept'] = Department ::all(); 
          $data['section'] = Section ::all();
        //   dd($data);
          return view('roto.generate-group-dashboard', $data);
        }
        else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }
    public function groupDashboardNew(Request $request){
        
        
        $user_id = Session::get('user_id');
        
        $data['companies'] = Company ::all();
        $argu = [];
        $grby ='companies.id';
        if(isset($request->grp)){
          //  $grby =$request->grp; business_units.id
          if($request->grp=='business_units_id'){
            $grby ="business_units.id";
          }
        }
      
        if(!empty($request->machine_id)){
           $argu["machine_id"]=$request->machine_id;  
        }
        if(!empty($request->company_id)){
            $argu["companies.id"]=$request->company_id;  
           // $grby ='company_id';
         }
        if(!empty($request->month)){
            $argu["month"]=$request->month;  
         }
         if(!empty($request->business_unit_id)){
            $argu["business_unit_id"]=$request->business_unit_id;  
         }
         if(!empty($request->department_id)){
            $argu["department_id"]=$request->department_id;  
         }
         if(!empty($request->section_id)){
            $argu["section_id"]=$request->section_id;  
         }
         if(!empty($request->operator_id)){
            $argu["operator_id"]=$request->operator_id;  
         }
        //dd($argu);
        $daterange = $request->daterange;
        $data['daterange'] = $daterange;
        $dateRange = explode(" - ",$daterange);
        $stdate = date('Y-m-d', strtotime($dateRange[0]));
        $endate = date('Y-m-d', strtotime($dateRange[1]));
        $days = $this->dateDiffernce($stdate,$endate);
        $total_time = 1440 * ($days+1) ; 
        //dd($total_time);
        $months = array("01"=>"Jan","02"=>"Feb","03"=>"Mar","04"=>"Apr","05"=>"May","06"=>"Jun","07"=>"Jul","08"=>"Aug","09"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dec");
        if($user_id){
            $data['path'] = Route::getFacadeRoot()->current()->uri();
            $data['layout'] = 'group-dashboard-layout';
            $data['user'] = Users::find(Session::get('user_id'));
             
            $startDateTime = $stdate;
            $endDateTime = date("Y-m-d", strtotime($endate . "+1 day")); 
            
            $data['records'] = GroupProductionReport::selectRaw("Sum(idleTime) as idle_time")
            ->selectRaw("Sum(run_time) as total_running_time")
            ->selectRaw("Sum(job_wating_time) as job_waiting")
            ->selectRaw("Sum(length) as total_production")
            ->selectRaw("Sum(length)/Sum(run_time) as actual_speed")
            ->selectRaw("machines.max_speed as designed_speed")
             
            ->selectRaw("($total_time)-Sum(idleTime) as budgeted_time")
            ->selectRaw("$total_time-Sum(idleTime)-Sum(job_wating_time) as budgeted_time_ee")
            ->selectRaw("machines.max_speed*$total_time as ideal_production") 
            ->selectRaw("(Sum(run_time)/($total_time-Sum(idleTime)-Sum(job_wating_time)))*100 as availability_ee")
            //->selectRow("Sum(run_time) as total_running_time")
            ->selectRaw("machines.sap_code as machine_no") 
            ->selectRaw("companies.name as company") 
            ->selectRaw("grp_dsb_production_report.date as date") 
            ->selectRaw("business_units.business_unit_name as bu") 
            ->selectRaw("departments.name as dept") 
            ->selectRaw("sections.name as section")
            ->selectRaw("Sum(length)/Sum(length)*100 as quality")
            ->selectRaw("((ifnull(SUM(length)/Sum(run_time),0))/machines.max_speed)*100 as performance")
            ->selectRaw("(ifnull(Sum(run_time),0)/($total_time-Sum(idleTime)))*100 as availability")
            ->selectRaw("ROUND((((ifnull(SUM(length)/Sum(run_time),0))/machines.max_speed)*(Sum(run_time)/($total_time-Sum(idleTime))))*100,2) as oee")
            ->leftJoin('machines', 'grp_dsb_production_report.machine_id', '=', 'machines.id')
                       // ->leftJoin('shifts', 'productivities.shift_id', '=', 'shifts.id')
            ->leftJoin('sections', 'machines.section_id', '=', 'sections.id')
            ->leftJoin('departments', 'sections.department_id', '=', 'departments.id')
            ->leftJoin('business_units', 'departments.business_unit_id', '=', 'business_units.id')
            ->leftJoin('companies', 'business_units.company_id', '=', 'companies.id')
            ->where($argu)
            ->where('err_no', '=', 2)
            ->where('date', '>=', $startDateTime)
            ->where('date', '<=', $endDateTime)
            //->groupBy($grby)
            //->groupBy("machines.id")
            ->groupBy('grp_dsb_production_report.company_id')
            
            //->orderBy("companies.id")
            ->get(); 
            $data['total_time'] =$total_time;
            dd($data['records']);
            // $data['records']=[];
            // if(count($results)>0){
               
            //     foreach($results as $result){
            //      $machine = Machine::where('sap_code',$result->machine_no)->first();
            //      $job_waiting = $result->total_job_waiting ; 
            //      $idle_time = $result->total_idleTime ; 
            //      $total_running_time  = $result->total_run_time;
            //      $budgeted_time = $total_time - $idle_time;
            //      $availability  = ($total_running_time / $budgeted_time)*100;
            //      $total_production = $result->total_production;
            //      $actual_speed =$total_production  / $total_running_time;
            //      $designed_speed = isset($machine->max_speed)?$machine->max_speed:0 ; // harde coded will get it from machine 
            //      $performance = ($designed_speed>0)?($actual_speed / $designed_speed) * 100:0;
            //      $actual_production = $result->total_production;
            //      $quality  = ($actual_production / $total_production) * 100;
            //      $budgeted_time_ee = $budgeted_time - $job_waiting;
            //      $ideal_production = $designed_speed * $total_time;//$designed_speed * $budgeted_time_ee;
            //      $oee = ($availability * $performance * $quality)/10000;
            //      $availability_ee  = ($total_running_time / $budgeted_time_ee)*100;
            //      //dd($result);   
                
            //     array_push($data['records'],[
            //         "machine_no"=>$result->machine_no,
            //         "company"=>$result->company_name,
            //        // "month"=>$months[$result->month],
            //         "date"=>$result->date,
            //         "bu"=>$result->business_unit_name,
            //         "dept"=>$result->department_name,
            //         "section"=>$result->section_name,
            //         "oee"=>$oee,
            //         "availability"=>$availability,
            //         "availability_ee"=>$availability_ee,
            //         "performance"=>$performance,
            //         "quality"=>$quality,
            //         "total_time"=>$total_time,
            //         "running_time"=>$total_running_time,
            //         "idle_time"=>$idle_time,
            //         "budgeted_time"=>$budgeted_time,
            //         "job_waiting"=>$job_waiting,
            //         "budgeted_time_ee"=>$budgeted_time_ee,
            //         "actual_speed"=>$actual_speed,
            //         "ideal_production"=>$ideal_production,
            //         "production"=>$total_production,
            //         "designed_speed"=>$designed_speed,
            //         "total_production"=>$total_production,
                    
                    
            //     ]);
            
            //     }
                //dd($data);
                return view('roto.groupdashboard', $data);
            // }else{
            //     //Session::flash("error", "No roecord found");
            //     return view('roto.groupdashboard', $data);
            //     //return redirect()->back(); 
            // }

                  
        }
        else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
    }





    public function groupDashboard(Request $request){
        
        
        $user_id = Session::get('user_id');
        
        $data['companies'] = Company ::all();
        $argu = [];
        $grby ='company_id';
        if(isset($request->grp)){
            $grby =$request->grp;
        }
      
        if(!empty($request->machine_id)){
           $argu["machine_id"]=$request->machine_id;  
        }
        if(!empty($request->company_id)){
            $argu["company_id"]=$request->company_id;  
            $grby ='company_id';
         }
        if(!empty($request->month)){
            $argu["month"]=$request->month;  
         }
         if(!empty($request->business_unit_id)){
            $argu["business_unit_id"]=$request->business_unit_id;  
         }
         if(!empty($request->department_id)){
            $argu["department_id"]=$request->department_id;  
         }
         if(!empty($request->section_id)){
           // $argu["section_id"]=$request->section_id;  
         }
         if(!empty($request->operator_id)){
            $argu["operator_id"]=$request->operator_id;  
         }
        //dd($argu);
        $daterange = $request->daterange;
        $data['daterange'] = $daterange;
        $dateRange = explode(" - ",$daterange);
        $stdate = date('Y-m-d', strtotime($dateRange[0]));
        $endate = date('Y-m-d', strtotime($dateRange[1]));
        $days = $this->dateDiffernce($stdate,$endate);
        //dd($days);
        //$idle_time = 0 ; 
        //$job_waiting = 0 ; 
        if($days==1){
        $total_time = 1440 * ($days) ; 
        }else{
        $total_time = 1440 * ($days+1) ; 
        }
        
        $months = array("01"=>"Jan","02"=>"Feb","03"=>"Mar","04"=>"Apr","05"=>"May","06"=>"Jun","07"=>"Jul","08"=>"Aug","09"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dec");
        if($user_id){
            $data['path'] = Route::getFacadeRoot()->current()->uri();
              //$data['layout'] = 'group-dashboard-layout';
        if(Session::get('rights') == 1){
            $data['layout'] = 'admin-layout';
        }
        elseif(Session::get('rights') == 2){
            $data['layout'] = 'power-user-layout';
        }
            $data['user'] = Users::find(Session::get('user_id'));
             
            $startDateTime = $stdate;
            $endDateTime = date("Y-m-d", strtotime($endate . "+1 day")); 
            if(!isset($request->machine_id)){
            $data['recordss'] = GroupProductionReport::selectRaw("Sum(idleTime) as idle_time")
            ->selectRaw("Sum(run_time) as total_running_time")
            ->selectRaw("Sum(job_wating_time) as job_waiting")
            ->selectRaw("Sum(length) as total_production")
            ->selectRaw("Sum(length)/Sum(run_time) as actual_speed")
            ->selectRaw("grp_dsb_production_report.company_id as company_id")
            ->selectRaw("$total_time*count(DISTINCT machines.id ) as total_time")
            ->selectRaw("machines.max_speed as designed_speed")
            ->selectRaw("count(DISTINCT machines.id ) as machinescount")
            ->selectRaw("($total_time*count(DISTINCT machines.id ))-Sum(idleTime) as budgeted_time")
            ->selectRaw("$total_time*count(DISTINCT machines.id )-Sum(idleTime)-Sum(job_wating_time) as budgeted_time_ee")
            ->selectRaw("(machines.max_speed)*$total_time*count(DISTINCT machines.id ) as ideal_production") 
            ->selectRaw("(Sum(run_time)/($total_time*count(DISTINCT machines.id )-Sum(idleTime)-Sum(job_wating_time)))*100 as availability_ee")
            ->selectRaw("Sum(run_time) as total_running_time")
            ->selectRaw("grp_dsb_production_report.machine_no as machine_no") 
            ->selectRaw("grp_dsb_production_report.machine_id as machine_id") 
            ->selectRaw("grp_dsb_production_report.operator_name as operator_name") 
            ->selectRaw("grp_dsb_production_report.operator_id as operator_id")
            ->selectRaw("grp_dsb_production_report.company_name as company") 
            ->selectRaw("grp_dsb_production_report.date as date") 
            ->selectRaw("grp_dsb_production_report.business_unit_name as bu") 
            ->selectRaw("grp_dsb_production_report.business_unit_id as business_unit_id")
            ->selectRaw("grp_dsb_production_report.department_id as department_id")
            ->selectRaw("grp_dsb_production_report.section_id as section_id")
            ->selectRaw("grp_dsb_production_report.department_name as dept") 
            ->selectRaw("grp_dsb_production_report.section_name as section")
            ->selectRaw("Sum(length)/Sum(length)*100 as quality")
            ->selectRaw("((ifnull(SUM(length)/Sum(run_time),0))/machines.max_speed)*100 as performance")
            ->selectRaw("(ifnull(Sum(run_time),0)/($total_time*count(DISTINCT machines.id )-Sum(idleTime)))*100 as availability")
            ->selectRaw("ROUND((((ifnull(SUM(length)/Sum(run_time),0))/machines.max_speed)*(Sum(run_time)/($total_time*count(DISTINCT machines.id )-Sum(idleTime))))*100,2) as oee")
            ->leftJoin('machines', 'grp_dsb_production_report.machine_id', '=', 'machines.id')
            ->where($argu)
            ->where('err_no', '=', 2)
            ->where('date', '>=', $startDateTime)
            ->where('date', '<=', $endDateTime)
            ->groupBy('machine_id')
            ->get(); 
            }else{  
                $data['recordss'] = GroupProductionReport::selectRaw("Sum(idleTime) as idle_time")
                ->selectRaw("Sum(run_time) as total_running_time")
                ->selectRaw("Sum(job_wating_time) as job_waiting")
                ->selectRaw("Sum(length) as total_production")
                ->selectRaw("Sum(length)/Sum(run_time) as actual_speed")
                ->selectRaw("grp_dsb_production_report.company_id as company_id")
                ->selectRaw("$total_time*count(DISTINCT machines.id ) as total_time")
                ->selectRaw("machines.max_speed as designed_speed")
                ->selectRaw("count(DISTINCT machines.id ) as machinescount")
                ->selectRaw("($total_time*count(DISTINCT machines.id ))-Sum(idleTime) as budgeted_time")
                ->selectRaw("$total_time*count(DISTINCT machines.id )-Sum(idleTime)-Sum(job_wating_time) as budgeted_time_ee")
                ->selectRaw("(machines.max_speed)*$total_time*count(DISTINCT machines.id ) as ideal_production") 
                ->selectRaw("(Sum(run_time)/($total_time*count(DISTINCT machines.id )-Sum(idleTime)-Sum(job_wating_time)))*100 as availability_ee")
                ->selectRaw("Sum(run_time) as total_running_time")
                ->selectRaw("grp_dsb_production_report.machine_no as machine_no") 
                ->selectRaw("grp_dsb_production_report.machine_id as machine_id") 
                ->selectRaw("grp_dsb_production_report.operator_name as operator_name") 
                ->selectRaw("grp_dsb_production_report.operator_id as operator_id")
                ->selectRaw("grp_dsb_production_report.company_name as company") 
                ->selectRaw("grp_dsb_production_report.date as date") 
                ->selectRaw("grp_dsb_production_report.business_unit_name as bu") 
                ->selectRaw("grp_dsb_production_report.business_unit_id as business_unit_id")
                ->selectRaw("grp_dsb_production_report.department_id as department_id")
                ->selectRaw("grp_dsb_production_report.section_id as section_id")
                ->selectRaw("grp_dsb_production_report.department_name as dept") 
                ->selectRaw("grp_dsb_production_report.section_name as section")
                ->selectRaw("Sum(length)/Sum(length)*100 as quality")
                ->selectRaw("((ifnull(SUM(length)/Sum(run_time),0))/machines.max_speed)*100 as performance")
                ->selectRaw("(ifnull(Sum(run_time),0)/($total_time*count(DISTINCT machines.id )-Sum(idleTime)))*100 as availability")
                ->selectRaw("ROUND((((ifnull(SUM(length)/Sum(run_time),0))/machines.max_speed)*(Sum(run_time)/($total_time*count(DISTINCT machines.id )-Sum(idleTime))))*100,2) as oee")
                ->leftJoin('machines', 'grp_dsb_production_report.machine_id', '=', 'machines.id')
                ->where($argu)
                ->where('err_no', '=', 2)
                ->where('date', '>=', $startDateTime)
                ->where('date', '<=', $endDateTime)
                ->groupBy('operator_id')
                ->get(); 
            }
            $data['total_time'] =$total_time;
            //dd($data['recordss']);
            $data['records']=[];
            $hp = new Helper();  
            if(!isset($request->company_id)){
                foreach( $data['companies'] as $comp){
                    $colective_oee =0;
                    $colective_running_time=0;
                    $colective_budgeted_time=0;
                    $colective_actual_speed=0;
                    $colective_designed_speed=0;
                    $colective_actual_speed=0;
                    $colective_total_production=0;
                    $colective_ideal_production=0;
                    $colective_total_time=0;
                    $colective_prodcution=0;
                    $colective_budgeted_time_ee=0;
                    $colective_idle_time=0;
                    $colective_job_waiting=0;
                        foreach($data['recordss'] as $result){
                        // dd($result);
                            if($comp->id == $result->company_id){
                                $colective_total_time = $colective_total_time + $result['total_time'];
                                $colective_running_time = $colective_running_time + $result['total_running_time'];
                                $colective_budgeted_time = $colective_budgeted_time + $result['budgeted_time'];
                                $colective_total_production = $colective_total_production + $result['total_production'];
                                $colective_designed_speed = $colective_designed_speed + $result['designed_speed'];
                                $colective_ideal_production = $colective_ideal_production + $result['ideal_production'];
                                $colective_prodcution = $colective_prodcution + $result['total_production'];
                                $colective_budgeted_time_ee = $colective_budgeted_time_ee + $result['budgeted_time_ee'];
                                $colective_idle_time = $colective_idle_time + $result['idle_time'];
                                $colective_job_waiting = $colective_job_waiting + $result['job_waiting']; 
                            }
                        }
                                $collective_availibilty = ($colective_budgeted_time>0)?$colective_running_time / $colective_budgeted_time:0;
                                $colective_actual_speed = ($colective_running_time>0)?$colective_total_production / $colective_running_time:0;
                                $colective_designed_speed =  ($colective_total_time>0)?$colective_ideal_production / $colective_total_time:0;
                                $colective_performace = ($colective_designed_speed>0)?$colective_actual_speed / $colective_designed_speed:0;
                                $colective_quality = ($colective_total_production>0)?$colective_total_production / $colective_total_production:0;
                                $colective_oee = ($colective_performace * $collective_availibilty * $colective_quality);
                                $collective_availibilty_ee =($colective_budgeted_time_ee>0)? ($colective_running_time / $colective_budgeted_time_ee):0 ; 





                                array_push($data['records'],[
                                    "machine_no"=>'',
                                    "operator"=>'',
                                    "company"=>$comp->name,
                                // "month"=>$months[$result->month],
                                    "date"=>$result->date,
                                    "bu"=>"",
                                    "dept"=>"",
                                    "section"=>"",
                                    "oee"=>$colective_oee,
                                    "availability"=>$collective_availibilty,
                                    "availability_ee"=>$collective_availibilty_ee,
                                    "performance"=>$colective_performace,
                                    "quality"=>$colective_quality,
                                    "total_time"=>$colective_total_time,
                                    "running_time"=>$colective_running_time,
                                    "idle_time"=>$colective_idle_time,
                                    "budgeted_time"=>$colective_budgeted_time,
                                    "job_waiting"=>$colective_job_waiting,
                                    "budgeted_time_ee"=>$colective_budgeted_time_ee,
                                    "actual_speed"=>$colective_actual_speed,
                                    "ideal_production"=>$colective_ideal_production,
                                    "production"=>$colective_prodcution,
                                    "designed_speed"=>$colective_designed_speed,
                                    "total_production"=>$colective_total_production,
                                    "total_running_time"=>$colective_running_time,
                                    
                                    
                                ]);
                
                }
            }else{
                $compp = Company ::find($request->company_id);
                /// BU checks
                if(isset($request->company_id)  && !isset($request->business_unit_id) && !isset($request->department_id) && !isset($request->section_id) && !isset($request->machine_id)){
               
                    foreach( $compp->businessUnits as $comp){
                        $colective_oee =0;
                        $colective_running_time=0;
                        $colective_budgeted_time=0;
                        $colective_actual_speed=0;
                        $colective_designed_speed=0;
                        $colective_actual_speed=0;
                        $colective_total_production=0;
                        $colective_ideal_production=0;
                        $colective_total_time=0;
                        $colective_prodcution=0;
                        $colective_budgeted_time_ee=0;
                        $colective_idle_time=0;
                        $colective_job_waiting=0;
                            foreach($data['recordss'] as $result){
                            //dd($result);
                                if($comp->id == $result->business_unit_id){
                                    $colective_total_time = $colective_total_time + $result['total_time'];
                                    $colective_running_time = $colective_running_time + $result['total_running_time'];
                                    $colective_budgeted_time = $colective_budgeted_time + $result['budgeted_time'];
                                    $colective_total_production = $colective_total_production + $result['total_production'];
                                    $colective_designed_speed = $colective_designed_speed + $result['designed_speed'];
                                    $colective_ideal_production = $colective_ideal_production + $result['ideal_production'];
                                    $colective_prodcution = $colective_prodcution + $result['total_production'];
                                    $colective_budgeted_time_ee = $colective_budgeted_time_ee + $result['budgeted_time_ee'];
                                    $colective_idle_time = $colective_idle_time + $result['idle_time'];
                                    $colective_job_waiting = $colective_job_waiting + $result['job_waiting']; 
                                }
                            }
                                    $collective_availibilty = ($colective_budgeted_time>0)?$colective_running_time / $colective_budgeted_time:0;
                                    $colective_actual_speed = ($colective_running_time>0)?$colective_total_production / $colective_running_time:0;
                                    $colective_designed_speed =  ($colective_total_time>0)?$colective_ideal_production / $colective_total_time:0;
                                    $colective_performace = ($colective_designed_speed>0)?$colective_actual_speed / $colective_designed_speed:0;
                                    $colective_quality = ($colective_total_production>0)?$colective_total_production / $colective_total_production:0;
                                    $colective_oee = ($colective_performace * $collective_availibilty * $colective_quality);
                                    $collective_availibilty_ee =($colective_budgeted_time_ee>0)? ($colective_running_time / $colective_budgeted_time_ee):0 ; 





                                    array_push($data['records'],[
                                        "machine_no"=>'',
                                        "operator"=>'',
                                        "company"=>$compp->name,
                                    // "month"=>$months[$result->month],
                                        "date"=>$result->date,
                                        "bu"=>"$comp->business_unit_name",
                                        "dept"=>"",
                                        "section"=>"",
                                        "oee"=>$colective_oee,
                                        "availability"=>$collective_availibilty,
                                        "availability_ee"=>$collective_availibilty_ee,
                                        "performance"=>$colective_performace,
                                        "quality"=>$colective_quality,
                                        "total_time"=>$colective_total_time,
                                        "running_time"=>$colective_running_time,
                                        "idle_time"=>$colective_idle_time,
                                        "budgeted_time"=>$colective_budgeted_time,
                                        "job_waiting"=>$colective_job_waiting,
                                        "budgeted_time_ee"=>$colective_budgeted_time_ee,
                                        "actual_speed"=>$colective_actual_speed,
                                        "ideal_production"=>$colective_ideal_production,
                                        "production"=>$colective_prodcution,
                                        "designed_speed"=>$colective_designed_speed,
                                        "total_production"=>$colective_total_production,
                                        "total_running_time"=>$colective_running_time,
                                        
                                        
                                    ]);
                    
                    } 
                }
                /// Dept check 
                if(isset($request->company_id)  && isset($request->business_unit_id) && !isset($request->department_id) && !isset($request->section_id) && !isset($request->machine_id)){
                
                    foreach( $compp->businessUnits as $compb){
                       if($compb->id == $request->business_unit_id){
                        foreach( $compb->departments as $comp){ 
                            $colective_oee =0;
                            $colective_running_time=0;
                            $colective_budgeted_time=0;
                            $colective_actual_speed=0;
                            $colective_designed_speed=0;
                            $colective_actual_speed=0;
                            $colective_total_production=0;
                            $colective_ideal_production=0;
                            $colective_total_time=0;
                            $colective_prodcution=0;
                            $colective_budgeted_time_ee=0;
                            $colective_idle_time=0;
                            $colective_job_waiting=0;
                                foreach($data['recordss'] as $result){
                                //dd($result);
                                    if($comp->id == $result->department_id){
                                        $colective_total_time = $colective_total_time + $result['total_time'];
                                        $colective_running_time = $colective_running_time + $result['total_running_time'];
                                        $colective_budgeted_time = $colective_budgeted_time + $result['budgeted_time'];
                                        $colective_total_production = $colective_total_production + $result['total_production'];
                                        $colective_designed_speed = $colective_designed_speed + $result['designed_speed'];
                                        $colective_ideal_production = $colective_ideal_production + $result['ideal_production'];
                                        $colective_prodcution = $colective_prodcution + $result['total_production'];
                                        $colective_budgeted_time_ee = $colective_budgeted_time_ee + $result['budgeted_time_ee'];
                                        $colective_idle_time = $colective_idle_time + $result['idle_time'];
                                        $colective_job_waiting = $colective_job_waiting + $result['job_waiting']; 
                                    }
                                }
                                        $collective_availibilty = ($colective_budgeted_time>0)?$colective_running_time / $colective_budgeted_time:0;
                                        $colective_actual_speed = ($colective_running_time>0)?$colective_total_production / $colective_running_time:0;
                                        $colective_designed_speed =  ($colective_total_time>0)?$colective_ideal_production / $colective_total_time:0;
                                        $colective_performace = ($colective_designed_speed>0)?$colective_actual_speed / $colective_designed_speed:0;
                                        $colective_quality = ($colective_total_production>0)?$colective_total_production / $colective_total_production:0;
                                        $colective_oee = ($colective_performace * $collective_availibilty * $colective_quality);
                                        $collective_availibilty_ee =($colective_budgeted_time_ee>0)? ($colective_running_time / $colective_budgeted_time_ee):0 ; 





                                        array_push($data['records'],[
                                            "machine_no"=>'',
                                            "operator"=>'',
                                            "company"=>$compp->name,
                                        // "month"=>$months[$result->month],
                                            "date"=>$result->date,
                                            "bu"=>"$result->bu",
                                            "dept"=>"$comp->name",
                                            "section"=>"",
                                            "oee"=>$colective_oee,
                                            "availability"=>$collective_availibilty,
                                            "availability_ee"=>$collective_availibilty_ee,
                                            "performance"=>$colective_performace,
                                            "quality"=>$colective_quality,
                                            "total_time"=>$colective_total_time,
                                            "running_time"=>$colective_running_time,
                                            "idle_time"=>$colective_idle_time,
                                            "budgeted_time"=>$colective_budgeted_time,
                                            "job_waiting"=>$colective_job_waiting,
                                            "budgeted_time_ee"=>$colective_budgeted_time_ee,
                                            "actual_speed"=>$colective_actual_speed,
                                            "ideal_production"=>$colective_ideal_production,
                                            "production"=>$colective_prodcution,
                                            "designed_speed"=>$colective_designed_speed,
                                            "total_production"=>$colective_total_production,
                                            "total_running_time"=>$colective_running_time,
                                            
                                            
                                        ]);
                                    }           
                        }
                        
                    } 
                }
                 /// sections check 
                 if(isset($request->company_id)  && isset($request->business_unit_id) && isset($request->department_id) && !isset($request->section_id) && !isset($request->machine_id)){
                
                    foreach( $compp->businessUnits as $compb){
                        foreach( $compb->departments as $compd){ 
                            if($compd->id == $request->department_id){
                            foreach( $compd->sections as $comp){ 
                               // dd($comp);
                                $colective_oee =0;
                                $colective_running_time=0;
                                $colective_budgeted_time=0;
                                $colective_actual_speed=0;
                                $colective_designed_speed=0;
                                $colective_actual_speed=0;
                                $colective_total_production=0;
                                $colective_ideal_production=0;
                                $colective_total_time=0;
                                $colective_prodcution=0;
                                $colective_budgeted_time_ee=0;
                                $colective_idle_time=0;
                                $colective_job_waiting=0;
                                    foreach($data['recordss'] as $result){
                                    //dd($result);
                                        if($comp->id == $result->section_id && $compd->id ==$result->department_id){
                                            $colective_total_time = $colective_total_time + $result['total_time'];
                                            $colective_running_time = $colective_running_time + $result['total_running_time'];
                                            $colective_budgeted_time = $colective_budgeted_time + $result['budgeted_time'];
                                            $colective_total_production = $colective_total_production + $result['total_production'];
                                            $colective_designed_speed = $colective_designed_speed + $result['designed_speed'];
                                            $colective_ideal_production = $colective_ideal_production + $result['ideal_production'];
                                            $colective_prodcution = $colective_prodcution + $result['total_production'];
                                            $colective_budgeted_time_ee = $colective_budgeted_time_ee + $result['budgeted_time_ee'];
                                            $colective_idle_time = $colective_idle_time + $result['idle_time'];
                                            $colective_job_waiting = $colective_job_waiting + $result['job_waiting']; 
                                        }
                                    }
                                            $collective_availibilty = ($colective_budgeted_time>0)?$colective_running_time / $colective_budgeted_time:0;
                                            $colective_actual_speed = ($colective_running_time>0)?$colective_total_production / $colective_running_time:0;
                                            $colective_designed_speed =  ($colective_total_time>0)?$colective_ideal_production / $colective_total_time:0;
                                            $colective_performace = ($colective_designed_speed>0)?$colective_actual_speed / $colective_designed_speed:0;
                                            $colective_quality = ($colective_total_production>0)?$colective_total_production / $colective_total_production:0;
                                            $colective_oee = ($colective_performace * $collective_availibilty * $colective_quality);
                                            $collective_availibilty_ee =($colective_budgeted_time_ee>0)? ($colective_running_time / $colective_budgeted_time_ee):0 ; 





                                            array_push($data['records'],[
                                                "machine_no"=>'',
                                                "operator"=>'',
                                                "company"=>$compp->name,
                                            // "month"=>$months[$result->month],
                                                "date"=>$result->date,
                                                "bu"=>"$result->bu",
                                                "dept"=>"$compd->name",
                                                "section"=>"$comp->name",
                                                "oee"=>$colective_oee,
                                                "availability"=>$collective_availibilty,
                                                "availability_ee"=>$collective_availibilty_ee,
                                                "performance"=>$colective_performace,
                                                "quality"=>$colective_quality,
                                                "total_time"=>$colective_total_time,
                                                "running_time"=>$colective_running_time,
                                                "idle_time"=>$colective_idle_time,
                                                "budgeted_time"=>$colective_budgeted_time,
                                                "job_waiting"=>$colective_job_waiting,
                                                "budgeted_time_ee"=>$colective_budgeted_time_ee,
                                                "actual_speed"=>$colective_actual_speed,
                                                "ideal_production"=>$colective_ideal_production,
                                                "production"=>$colective_prodcution,
                                                "designed_speed"=>$colective_designed_speed,
                                                "total_production"=>$colective_total_production,
                                                "total_running_time"=>$colective_running_time,
                                                
                                                
                                            ]);
                            }
                        }
                      }
                        
                    } 
                }
                /// machines check 
                if(isset($request->company_id)  && isset($request->business_unit_id) && isset($request->department_id) && isset($request->section_id) && !isset($request->machine_id)){
                
                    foreach( $compp->businessUnits as $compb){
                        foreach( $compb->departments as $compd){ 
                            if($compd->id == $request->department_id){
                                foreach( $compd->sections as $compss){ 
                                  if($compss->id == $request->section_id){
                                    foreach( $compss->machines as $comp){ 
                                        
                                            $colective_oee =0;
                                            $colective_running_time=0;
                                            $colective_budgeted_time=0;
                                            $colective_actual_speed=0;
                                            $colective_designed_speed=0;
                                            $colective_actual_speed=0;
                                            $colective_total_production=0;
                                            $colective_ideal_production=0;
                                            $colective_total_time=0;
                                            $colective_prodcution=0;
                                            $colective_budgeted_time_ee=0;
                                            $colective_idle_time=0;
                                            $colective_job_waiting=0;
                                                foreach($data['recordss'] as $result){
                                                //dd($result);
                                                    if($comp->id == $result->machine_id && $compss->id ==$result->section_id){
                                                        $colective_total_time = $colective_total_time + $result['total_time'];
                                                        $colective_running_time = $colective_running_time + $result['total_running_time'];
                                                        $colective_budgeted_time = $colective_budgeted_time + $result['budgeted_time'];
                                                        $colective_total_production = $colective_total_production + $result['total_production'];
                                                        $colective_designed_speed = $colective_designed_speed + $result['designed_speed'];
                                                        $colective_ideal_production = $colective_ideal_production + $result['ideal_production'];
                                                        $colective_prodcution = $colective_prodcution + $result['total_production'];
                                                        $colective_budgeted_time_ee = $colective_budgeted_time_ee + $result['budgeted_time_ee'];
                                                        $colective_idle_time = $colective_idle_time + $result['idle_time'];
                                                        $colective_job_waiting = $colective_job_waiting + $result['job_waiting']; 
                                                    }
                                                }
                                                        $collective_availibilty = ($colective_budgeted_time>0)?$colective_running_time / $colective_budgeted_time:0;
                                                        $colective_actual_speed = ($colective_running_time>0)?$colective_total_production / $colective_running_time:0;
                                                        $colective_designed_speed =  ($colective_total_time>0)?$colective_ideal_production / $colective_total_time:0;
                                                        $colective_performace = ($colective_designed_speed>0)?$colective_actual_speed / $colective_designed_speed:0;
                                                        $colective_quality = ($colective_total_production>0)?$colective_total_production / $colective_total_production:0;
                                                        $colective_oee = ($colective_performace * $collective_availibilty * $colective_quality);
                                                        $collective_availibilty_ee =($colective_budgeted_time_ee>0)? ($colective_running_time / $colective_budgeted_time_ee):0 ; 





                                                        array_push($data['records'],[
                                                            "machine_no"=>$comp->name,
                                                            "operator"=>'',
                                                            "company"=>$compp->name,
                                                        // "month"=>$months[$result->month],
                                                            "date"=>$result->date,
                                                            "bu"=>"$result->bu",
                                                            "dept"=>"$compd->name",
                                                            "section"=>"$compss->name",
                                                            "oee"=>$colective_oee,
                                                            "availability"=>$collective_availibilty,
                                                            "availability_ee"=>$collective_availibilty_ee,
                                                            "performance"=>$colective_performace,
                                                            "quality"=>$colective_quality,
                                                            "total_time"=>$colective_total_time,
                                                            "running_time"=>$colective_running_time,
                                                            "idle_time"=>$colective_idle_time,
                                                            "budgeted_time"=>$colective_budgeted_time,
                                                            "job_waiting"=>$colective_job_waiting,
                                                            "budgeted_time_ee"=>$colective_budgeted_time_ee,
                                                            "actual_speed"=>$colective_actual_speed,
                                                            "ideal_production"=>$colective_ideal_production,
                                                            "production"=>$colective_prodcution,
                                                            "designed_speed"=>$colective_designed_speed,
                                                            "total_production"=>$colective_total_production,
                                                            "total_running_time"=>$colective_running_time,
                                                            
                                                            
                                                        ]);
                                     }
                                    }
                                }
                        }
                      }
                        
                    } 
                }

                 /// Operator check 
                 if(isset($request->company_id)  && isset($request->business_unit_id) && isset($request->department_id) && isset($request->section_id) && isset($request->machine_id)){
                
                    foreach( $compp->businessUnits as $compb){
                        foreach( $compb->departments as $compd){ 
                            if($compd->id == $request->department_id){
                                foreach( $compd->sections as $compss){ 
                                  if($compss->id == $request->section_id){
                                    foreach( $compss->machines as $compm){ 
                                        if($compm->id == $request->machine_id){
                                                $uss = $hp->getMachineUsers($compm->id,$daterange);
                                                foreach($uss as $comp){
                                                    $colective_oee =0;
                                                    $colective_running_time=0;
                                                    $colective_budgeted_time=0;
                                                    $colective_actual_speed=0;
                                                    $colective_designed_speed=0;
                                                    $colective_actual_speed=0;
                                                    $colective_total_production=0;
                                                    $colective_ideal_production=0;
                                                    $colective_total_time=0;
                                                    $colective_prodcution=0;
                                                    $colective_budgeted_time_ee=0;
                                                    $colective_idle_time=0;
                                                    $colective_job_waiting=0;
                                                        foreach($data['recordss'] as $result){
                                                        //dd($result);
                                                            if($comp->operator_id == $result->operator_id && $compm->id ==$result->machine_id){
                                                                $colective_total_time = $colective_total_time + $result['total_time'];
                                                                $colective_running_time = $colective_running_time + $result['total_running_time'];
                                                                $colective_budgeted_time = $colective_budgeted_time + $result['budgeted_time'];
                                                                $colective_total_production = $colective_total_production + $result['total_production'];
                                                                $colective_designed_speed = $colective_designed_speed + $result['designed_speed'];
                                                                $colective_ideal_production = $colective_ideal_production + $result['ideal_production'];
                                                                $colective_prodcution = $colective_prodcution + $result['total_production'];
                                                                $colective_budgeted_time_ee = $colective_budgeted_time_ee + $result['budgeted_time_ee'];
                                                                $colective_idle_time = $colective_idle_time + $result['idle_time'];
                                                                $colective_job_waiting = $colective_job_waiting + $result['job_waiting']; 
                                                            }
                                                        }
                                                                $collective_availibilty = ($colective_budgeted_time>0)?$colective_running_time / $colective_budgeted_time:0;
                                                                $colective_actual_speed = ($colective_running_time>0)?$colective_total_production / $colective_running_time:0;
                                                                $colective_designed_speed =  ($colective_total_time>0)?$colective_ideal_production / $colective_total_time:0;
                                                                $colective_performace = ($colective_designed_speed>0)?$colective_actual_speed / $colective_designed_speed:0;
                                                                $colective_quality = ($colective_total_production>0)?$colective_total_production / $colective_total_production:0;
                                                                $colective_oee = ($colective_performace * $collective_availibilty * $colective_quality);
                                                                $collective_availibilty_ee =($colective_budgeted_time_ee>0)? ($colective_running_time / $colective_budgeted_time_ee):0 ; 





                                                                array_push($data['records'],[
                                                                    "machine_no"=>$compm->name,
                                                                    "operator"=>$comp->operator_name,
                                                                    "company"=>$compp->name,
                                                                // "month"=>$months[$result->month],
                                                                    "date"=>$result->date,
                                                                    "bu"=>"$result->bu",
                                                                    "dept"=>"$compd->name",
                                                                    "section"=>"$compss->name",
                                                                    "oee"=>$colective_oee,
                                                                    "availability"=>$collective_availibilty,
                                                                    "availability_ee"=>$collective_availibilty_ee,
                                                                    "performance"=>$colective_performace,
                                                                    "quality"=>$colective_quality,
                                                                    "total_time"=>$colective_total_time,
                                                                    "running_time"=>$colective_running_time,
                                                                    "idle_time"=>$colective_idle_time,
                                                                    "budgeted_time"=>$colective_budgeted_time,
                                                                    "job_waiting"=>$colective_job_waiting,
                                                                    "budgeted_time_ee"=>$colective_budgeted_time_ee,
                                                                    "actual_speed"=>$colective_actual_speed,
                                                                    "ideal_production"=>$colective_ideal_production,
                                                                    "production"=>$colective_prodcution,
                                                                    "designed_speed"=>$colective_designed_speed,
                                                                    "total_production"=>$colective_total_production,
                                                                    "total_running_time"=>$colective_running_time,
                                                                    
                                                                    
                                                                ]);
                                                 }
                                                }
                                     }
                                    }
                                }
                        }
                      }
                        
                    } 
                }
            }
          //dd($data);
            return view('roto.groupdashboard', $data);
               

                  
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
    public function dateDiffernce ($stdate , $endate){
        $date1 = strtotime($stdate);
        $date2 = strtotime($endate);
        $daysDifference = ($date2 - $date1) / (60 * 60 * 24);
        //dd($daysDifference);
         if($daysDifference==0){
            return 1;
         }else{
           return $daysDifference; 
         }
        // dd($daysDifference);
    }
    
    /**
     * @return \Illuminate\View\View
     */

}
