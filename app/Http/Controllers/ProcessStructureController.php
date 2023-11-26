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
use App\Models\User;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use PhpSpec\Exception\Exception;

class ProcessStructureController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
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
	    $user_id = Session::get('user_id');
	    if(isset($user_id)){
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
            $machine_id = Crypt::decrypt($id);
            if(isset($machine_id)){
                $data['machine'] = Machine::find($machine_id);
                $data['operator'] = Users::find(Session::get('user_name'));
                $data['jobs'] = Job::with('product')->get();
                $data['materialCombinations'] = MaterialCombination::all();
                return view('roto.update-process-structure', $data);
            }
        }
	    else{
            Session::flash("error", "Please login to continue");
            return redirect('/');
        }
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request)
	{
	    UpdateStructure:
        $machine = Machine::find($request->input('machine'));
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
                            $processStructure->material_combination_id = $material_combination->id;
                            $processStructure->save();
                        }
                        else{
                            Session::flash('error','Structure definition does not Exist.');
                            return Redirect::back();
                        }
                    }
                    else{
                        Session::flash('error','Please select a valid Process.');
                        return Redirect::back();
                    }
                }
                else{
                    $material_combination = new MaterialCombination();
                    $material_combination->name = $request->input('material_combination');
                    $material_combination->save();
                    goto UpdateStructure;
                }
            }
            else{
                Session::flash('error','Please select a valid Product.');
                return Redirect::back();
            }
        }
        else{
            Session::flash('error','Please select a valid Job.');
            return Redirect::back();
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
		//
	}

}
