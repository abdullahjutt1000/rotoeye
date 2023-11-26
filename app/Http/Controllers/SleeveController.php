<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Sleeve;
use App\Models\Users;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SleeveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
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
        $data['sleeves'] = Sleeve::all();
        $data['user'] = Users::find(Session::get('user_name'));
        $data['machine'] = Machine::find(Crypt::decrypt($id));
        return view('roto.sleeves', $data);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($id)
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
        $data['sleeves'] = DB::table('machine_sleeve')->whereIn('machine_id',$data['user']->allowedMachines->pluck('id'))->get();
        $data['machine'] = Machine::find(Crypt::decrypt($id));
        return view('roto.add-sleeve', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request, $id)
    {
        $sleeves=array(
            "circumference"=>"Sleeve Circumference",
            "speed"=>"Sleeve Speed",
            "machine_id"=>"Machine",
        );
        $validator=Validator::make($request->all(),
            [
                "circumference"=>"required",
                "speed"=>"required",
                "machine_id"=>"required",
            ]);
        $validator->setAttributeNames($sleeves);
        if($validator->fails())
        {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        else {
            try {
                $sleeve = new Sleeve();
                $sleeve->circumference = $request->input('circumference');
                $sleeve->save();
                $sleeve->machines()->attach($request->input('machine_id'),['speed'=>$request->input('speed')]);
                Session::flash('success', 'A new sleeve has been added.');
                return Redirect('sleeves'.'/'.$id);
            }
            catch (QueryException $e) {
                dd($e->getMessage());
                Session::flash('error', 'Please contact System Administrator with code ' . '<strong>' . $e->getCode() . '</strong>');
                return Redirect::back()->withInput();
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sleeve  $sleeve
     * @return \Illuminate\Http\Response
     */
    public function show(Sleeve $sleeve)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sleeve  $sleeve
     * @return \Illuminate\Http\Response
     */
    public function edit($sleeve_id,$machine_id,$id)
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
        $data['machine'] = Machine::find($machine_id);
        $data['user'] = Users::find(Session::get('user_name'));
        $data['sleeve'] = DB::table('sleeves')->select('sleeves.id as SleeveId','machines.id as machineId','machines.sap_code as machineSapCode','machines.name as machineName','circumference','sleeves.id as sleeveId','machine_sleeve.speed as sleeveSpeed','machine_sleeve.machine_id as machineId')
            ->leftJoin('machine_sleeve', 'machine_sleeve.sleeve_id', '=', 'sleeves.id')
            ->leftJoin('machines', 'machines.id', '=', 'machine_sleeve.machine_id')
            ->where('machine_sleeve.sleeve_id','=',$sleeve_id)
            ->where('machine_sleeve.machine_id','=',$machine_id)
        ->get();
        return view('roto.update-sleeve', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sleeve  $sleeve
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $sleeve_id,$machine_id, $id)
    {
        $request->input('circumference');

        $sleeves=array(
            "circumference"=>"Sleeve Circumference",
            "speed"=>"Sleeve Speed",
        );
        $validator=Validator::make($request->all(),
            [
                "circumference"=>"required",
                "speed"=>"required",
            ]);
        $validator->setAttributeNames($sleeves);
        if($validator->fails())
        {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        else {
            try {
                $data['machine'] = Machine::find($machine_id);
                $data['user'] = Users::find(Session::get('user_name'));
                $logger = new LoggerController($data['machine']);
                $logger->log('------------------ Start Update Sleeve ------------------', $data['machine']);
                $logger->log('sleeve Id: '.$sleeve_id, $data['machine']);
                $logger->log('Machine Id: '.$machine_id, $data['machine']);
                $logger->log('User ID: '.$data['user']->id.'-'.$data['user']->name, $data['machine']);
                DB::table('sleeves')
                    ->leftJoin('machine_sleeve', 'machine_sleeve.sleeve_id', '=', 'sleeves.id')
                    ->where('machine_sleeve.sleeve_id','=',$sleeve_id)
                    ->where('machine_sleeve.machine_id','=',$machine_id)
                    ->update([ 'machine_sleeve.speed' => $request->input('speed'),'sleeves.circumference' => $request->input('circumference') ]);
                $logger->log('------------------ Start Update Sleeve ------------------', $data['machine']);
                Session::flash('success', 'A new sleeve has been added.');
                return Redirect('sleeves'.'/'.$id);
            }
            catch (QueryException $e) {
                Session::flash('error', 'Please contact System Administrator with code ' . '<strong>' . $e->getCode() . '</strong>');
                return Redirect::back()->withInput();
            }
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sleeve  $sleeve
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sleeve $sleeve)
    {
        //
    }
}
