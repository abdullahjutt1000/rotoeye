<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Error;
use App\Models\Machine;
use App\Models\Machine_User;
use App\Models\Record;
use App\Models\Section;
use App\Models\User;
use App\Models\Users;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
// use App\Http\Controllers\File;
use Illuminate\Support\Facades\File;

use Mail;

class MachineController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($id)
    {
        $data['path'] = Route::getFacadeRoot()->current()->uri();
        if (Session::get('rights') == 0) {
            $data['layout'] = 'web-layout';
        } elseif (Session::get('rights') == 1) {
            $data['layout'] = 'admin-layout';
        } elseif (Session::get('rights') == 2) {
            $data['layout'] = 'power-user-layout';
        }
        // Added new rights for view all machines start
        elseif (Session::get('rights') == 4) {
            $data['layout'] = 'simple-layout';
        }
        // Added new rights for view all machines end

        $data['machines'] = Machine::all();
        $data['machine'] = Machine::find(Crypt::decrypt($id));
        $data['user'] = Users::find(Session::get('user_name'));
        return view('roto.machines', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($id)
    {
        $data['path'] = Route::getFacadeRoot()->current()->uri();
        if (Session::get('rights') == 0) {
            $data['layout'] = 'web-layout';
        } elseif (Session::get('rights') == 1) {
            $data['layout'] = 'admin-layout';
        } elseif (Session::get('rights') == 2) {
            $data['layout'] = 'power-user-layout';
        }
        // Adding new machine for creating new machine start
        elseif (Session::get('rights') == 4) {
            $data['layout'] = 'simple-layout';
        }
        // Adding new machine for creating new machine end

        $data['machine'] = Machine::find(Crypt::decrypt($id));
        $data['machinesCount'] = Machine::all()->count();
        $data['sections'] = Section::all();
        $data['user'] = Users::find(Session::get('user_name'));
        //haseeb 6/14/2021
        $data['error_codes'] = Error::all();
        //haseeb 6/14/2021
        return view('roto.add-machine', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request, $id)
    {
        $machine = array(
            "code" => "id",
            "name" => "Machine Name",
            "ip" => "IP Address",
            "hw_type" => "Hardware Type",
            "max_speed" => "Max Speed",
            "waste_speed" => "Waste Speed",
            "auto_downtime" => "Auto Downtime",
            "time_uom" => "Time UOM",
            "qty_uom" => "Quantity UOM",
            "section" => "Section",
            "graph_span" => "Graph Span",
            "roller_circumference" => "Roller Circumference",
            "downtime_error" => "Downtime Error",
            "bin1" => "Bin 1",
            "bin2" => "Bin 2",
        );
        $validator = Validator::make(
            $request->all(),
            [
                "code" => "required",
                "name" => "required",
                "ip" => "required",
                "max_speed" => "required",
                "waste_speed" => "required",
                "auto_downtime" => "required",
                "time_uom" => "required",
                "qty_uom" => "required",
                "section" => "required",
                "graph_span" => "required",
                "roller_circumference" => "required",
                "downtime_error" => "required",
            ]
        );
        $validator->setAttributeNames($machine);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try {
                $machine = new Machine();
                $machine->name = $request->input('name');
                $machine->sap_code = $request->input('code');
                $machine->hw_type = $request->input('hw_type');
                $machine->max_speed = $request->input('max_speed');
                $machine->ip = $request->input('ip');
                $machine->time_uom = $request->input('time_uom');
                $machine->qty_uom = $request->input('qty_uom');
                $machine->waste_speed = $request->input('waste_speed');
                $machine->auto_downtime = $request->input('auto_downtime');
                $machine->graph_span = $request->input('graph_span');
                $machine->roller_circumference = $request->input('roller_circumference');
                $machine->section_id = $request->input('section');
                $machine->rh_logger_ip = $request->input('rh_ip');
                $machine->downtime_error = $request->input('downtime_error');
                // Code added by Abdullah 13/11/2023
                $file1 = $request->file('bin_file1');
                $file2 = $request->file('bin_file2');


                if ($file1) {
                    $originalName=$file1->getClientOriginalName();
                    $ReplaceName = str_replace(' ', '-', $originalName);
                    $binaryData1 = uniqid() . '-' . $ReplaceName;
                    $file1->move(public_path('machines/' . $request->input('code')), $binaryData1);
                    $machine->bin1 = $binaryData1;

                    // $binaryData1 = uniqid() . '-' . $file1->getClientOriginalName();
                    // $file1->move(public_path('machines/' . $request->input('code')), $binaryData1);
                    // $machine->bin1 = $binaryData1;

                }
                if ($file2) {
                    $originalName=$file2->getClientOriginalName();
                    $ReplaceName = str_replace(' ', '-', $originalName);
                    $binaryData2 = uniqid() . '-' . $ReplaceName;
                    $file2->move(public_path('machines/' . $request->input('code')), $binaryData2);
                    $machine->bin2 = $binaryData2;

                    // $binaryData2 = uniqid() . '-' . $file2->getClientOriginalName();
                    // $file2->move(public_path('machines/' . $request->input('code')), $binaryData2);
                    // $machine->bin2 = $binaryData2;
                }
                // Code added by Abdullah 13/11/2023

                $machine->save();

                Session::flash('success', 'A new machine has been added.');
                return Redirect('machines' . '/' . $id);
            } catch (QueryException $e) {
                Session::flash('error', 'Please contact System Administrator with code ' . '<strong>' . $e->getCode() . '</strong>');
                return Redirect::back()->withInput();
            }
        }
    }

    // function made by Abdullah 13/11/2023
    public function download($sap_code, $bin1)
    {
        $filePath = public_path('machines/' . $sap_code . '/' . $bin1);

        return Response::download($filePath, $bin1);
    }

    // function made by Abdullah 13/11/2023

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data['path'] = Route::getFacadeRoot()->current()->uri();
        if (Session::get('rights') == 0) {
            $data['layout'] = 'web-layout';
        } elseif (Session::get('rights') == 1) {
            $data['layout'] = 'admin-layout';
        } elseif (Session::get('rights') == 2) {
            $data['layout'] = 'power-user-layout';
        } elseif (Session::get('rights') == 4) {
            $data['layout'] = 'simple-layout';
        }
        $data['machines'] = Machine::all();
        $data['sections'] = Section::all();
        $data['machine'] = Machine::find($id);
        $data['user'] = Users::find(Session::get('user_name'));
        $data['error_codes'] = Error::all();
        return view('roto.update-machine', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id, $machineID)
    {
        $machine = array(
            "code" => "id",
            "name" => "Machine Name",
            "ip" => "IP Address",
            "hw_type" => "Hardware Type",
            "max_speed" => "Max Speed",
            "waste_speed" => "Waste Speed",
            "auto_downtime" => "Auto Downtime",
            "time_uom" => "Time UOM",
            "qty_uom" => "Quantity UOM",
            "section" => "Section",
            "graph_span" => "Graph Span",
            "roller_circumference" => "Roller Circumference",
            "downtime_error" => "Downtime Error",
            "bin1" => "Bin1",
            "bin2" => "Bin2",
        );
        $validator = Validator::make(
            $request->all(),
            [
                "code" => "required",
                "name" => "required",
                "ip" => "required",
                "max_speed" => "required",
                "waste_speed" => "required",
                "auto_downtime" => "required",
                "time_uom" => "required",
                "qty_uom" => "required",
                "section" => "required",
                "graph_span" => "required",
                "roller_circumference" => "required",
                "downtime_error" => "required",
            ]
        );
        $validator->setAttributeNames($machine);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try {
                $machine = Machine::find($id);
                $machine->name = $request->input('name');
                $machine->sap_code = $request->input('code');
                $machine->hw_type = $request->input('hw_type');
                $machine->max_speed = $request->input('max_speed');
                $machine->ip = $request->input('ip');
                $machine->time_uom = $request->input('time_uom');
                $machine->qty_uom = $request->input('qty_uom');
                $machine->waste_speed = $request->input('waste_speed');
                $machine->auto_downtime = $request->input('auto_downtime');
                $machine->graph_span = $request->input('graph_span');
                $machine->roller_circumference = $request->input('roller_circumference');
                $machine->section_id = $request->input('section');
                $machine->rh_logger_ip = $request->input('rh_ip');
                $machine->downtime_error = $request->input('downtime_error');

                // update bin file code start by Abdullah
                $file1 = $request->file('bin_file1');
                $file2 = $request->file('bin_file2');
                $binfile1 = $machine->bin1;
                $binfile2 = $machine->bin2;

                // for Bin file 1
                if ($file1) {
                    $oldFilePath1 = public_path('machines/' . $machine->sap_code . '/' . $machine->bin1);
                    if ($binfile1 !== '' && File::isFile($oldFilePath1) && File::exists($oldFilePath1)) {
                        unlink($oldFilePath1);
                    }
                    $originalName=$file1->getClientOriginalName();
                    $ReplaceName = str_replace(' ', '-', $originalName);
                    $binaryData1 = uniqid() . '-' . $ReplaceName;
                    // $binaryData1 = uniqid() . '-' . $file1->getClientOriginalName();
                    $file1->move(public_path('machines/' . $machine->sap_code), $binaryData1);
                    $machine->bin1 = $binaryData1;
                }

                // for Bin file 2
                if ($file2) {
                    $oldFilePath2 = public_path('machines/' . $machine->sap_code . '/' . $machine->bin2);
                    if ($binfile2 !== '' && File::isFile($oldFilePath2) && File::exists($oldFilePath2)) {
                        unlink($oldFilePath2);
                    }
                    $originalName=$file2->getClientOriginalName();
                    $ReplaceName = str_replace(' ', '-', $originalName);
                    $binaryData2 = uniqid() . '-' . $ReplaceName;
                    // $binaryData2 = uniqid() . '-' . $file2->getClientOriginalName();
                    $file2->move(public_path('machines/' . $machine->sap_code), $binaryData2);
                    $machine->bin2 = $binaryData2;
                }

                // update bin file code end by Abdullah



                $machine->save();

                Session::flash('success', 'Machine has been updated.');
                return Redirect('machines' . '/' . $machineID);
            } catch (QueryException $e) {
                dd($e);
                Session::flash('error', 'Please contact System Administrator with code ' . '<strong>' . $e->getCode() . '</strong>');
                return Redirect::back()->withInput();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        // Redirect Back after deleting a record start
        try {
            Machine::destroy($id);
            Session::flash('success', 'Machine deleted Successfully');
            // return redirect(URL::to('machines'));
            return Redirect()->back();
            // Redirect Back after deleting a record end

        } catch (QueryException $e) {
            Session::flash('error', 'Please contact System Administrator with code ' . '<strong>' . $e->getCode() . '</strong>');
            return Redirect::back();
        }
    }

    public function selectMachine()
    {
        $data['user'] = Users::find(Session::get('user_name'));
        $data['machines'] = Machine::all();
        return view('roto.select-machine', $data);
    }

    public function submitMachine(Request $request)
    {
        $machine_id = Crypt::decrypt($request->input('machine'));
        if ($machine_id) {
            $machine = Machine::find($machine_id);
            Session::put('machine', $machine);
            return \redirect('dashboard' . '/' . Crypt::encrypt($machine->id));
        } else {
            Session::flash('error', 'Machine is not valid. Please contact System Administrator.');
            return Redirect::back();
        }
    }

    public function allocateMachines($id)
    {
        $data['path'] = Route::getFacadeRoot()->current()->uri();
        if (Session::get('rights') == 0) {
            $data['layout'] = 'web-layout';
        } elseif (Session::get('rights') == 1) {
            $data['layout'] = 'admin-layout';
        } elseif (Session::get('rights') == 2) {
            $data['layout'] = 'power-user-layout';
        } elseif (Session::get('rights') == 4) {
            $data['layout'] = 'simple-layout';
        }
        $data['user'] = Users::find(Session::get('user_name'));
        $data['employee'] = Users::find($id);
        $data['machines'] = Machine::whereNotIn('id', function ($query) use ($data) {
            $query->select('machine_id')
                ->from('machine_users')->where('user_id', '=', $data['employee']->id);
        })->get();

        // 		return view('roto.allocate-machines', $data);
        //haseeb 7/14/2021 3:25
        if (count($data['machines']) > 0) {
            return view('roto.allocate-machines', $data);
        } else {
            Session::flash('error', 'No machine left to allocate');
            return Redirect::back();
        }
        //haseeb 7/14/2021 3:25

    }

    public function addMoreMachines($id)
    {
        $data['employee'] = Users::find($id);
        $data['machines'] = Machine::whereNotIn('id', function ($query) use ($data) {
            $query->select('machine_id')
                ->from('machine_users')->where('user_id', '=', $data['employee']->id);
        })->get();
        $view = View::make('partials.allocate-machine', $data)->render();
        $data['row'] = $view;
        return response(json_encode($data), 200);

    }

    public function storeAllocateMachines(Request $request, $id)
    {
        $data['employee'] = Users::find($id);
        $machines = $request->input('machines');
        foreach ($machines as $machine) {
            $alreadyExist = Machine_User::where('machine_id', '=', $machine)->where('user_id', '=', $data['employee']->id)->get();
            if (count($alreadyExist) == 0) {
                $machine_user = new Machine_User();
                $machine_user->machine_id = $machine;
                $machine_user->user_id = $data['employee']->id;
                $machine_user->save();
            }
        }
        Session::flash('success', 'Allocated Successfuly');
        // 		return redirect('users');
        //haseeb 7/14/2021 3:25
        return Redirect('user/update/' . $id . '/' . Crypt::encrypt(($machines[0])));
        //haseeb 7/14/2021 3:25
    }

    public function fetchLocalRecords()
    {
        $machines = Machine::all();
        $dateTime = date('Y-m-d H:i:s');
        $data['machines'] = [];
        foreach ($machines as $machine) {
            $record = Record::where('machine_id', '=', $machine->id)->latest('run_date_time')->limit(1)->get();
            if (count($record) > 0) {
                $minutesDiff = date_diff(date_create($record[0]->run_date_time), date_create($dateTime))->format("%i");
                if ($minutesDiff > 10) {
                    $lastDataReceived = date_diff(date_create($record[0]->run_date_time), date_create($dateTime))->format("%y Year %m Month %d Day %h Hr %i Min %s Sec");
                    array_push($data['machines'], [
                        "machine_id" => $machine->sap_code,
                        "machine_name" => $machine->name,
                        "last_run_date_time" => $record[0]->run_date_time,
                        "last_received" => $lastDataReceived
                    ]);
                }
            }
        }
        Mail::send('emails.not-responding-circuits', $data, function ($message) use ($data) {
            $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
            $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                ->cc('nauman.abid@packages.com.pk', 'M Nauman Abid')
                ->subject("RotoEye Cloud - Not Responding Circuits");
        });
    }

    public function getDateWiseMachineJobs(Request $request)
    {
        $startDateTime = date('Y-m-d H:i:s', strtotime($request->date . ' + 390 minutes'));
        $endDateTime = date('Y-m-d H:i:s', strtotime($request->date . ' + 1830 minutes'));
        $machine_id = $request->input('machine_id');
        $jobs = Record::with('job.product')->where('machine_id', '=', $machine_id)
            ->where('run_date_time', '>=', $startDateTime)
            ->where('run_date_time', '<=', $endDateTime)
            ->select('job_id')
            ->distinct()
            ->get();
        return json_encode($jobs);
    }

    public function updateStatus(Request $request, $id)
    {       //dd($id);

        try {
            $machine = Machine::find($id);
            // dd($machine);
            if ($machine->is_disabled == 1) {
                $machine->is_disabled = NULL;
            } else {
                $machine->is_disabled = 1;
            }
            $machine->save();

            Session::flash('success', 'Machine has been updated.');
            return Redirect()->back();
        } catch (QueryException $e) {
            dd($e);
            Session::flash('error', 'Please contact System Administrator with code ' . '<strong>' . $e->getCode() . '</strong>');
            return Redirect::back()->withInput();
        }

    }
}
