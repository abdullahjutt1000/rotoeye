<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Process;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ProcessController extends Controller {

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
		$data['processes'] = Process::all();
		$data['user'] = User::find(Session::get('user_name'));
		return view('roto.processes', $data);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
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
		$data['sections'] = Section::all();
		$data['processesCount'] = Process::all()->count();
		$data['user'] = User::find(Session::get('user_name'));
		return view('roto.add-process', $data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$department=array(
			"name"=>"Department Name",
			"businessUnit"=>"Business Unit Name",
		);
		$validator=Validator::make($request->all(),
			[
				"name"=>"required",
				"businessUnit"=>"required",
			]);
		$validator->setAttributeNames($department);
		if($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}
		else {
			try {
				if(count($request->input('errorCodes')) > 0){
					$department = new Department();
					$department->name = $request->input('name');
					$department->business_unit_id = $request->input('businessUnit');
					$department->save();
					$department->errorCodes()->attach($request->input('errorCodes'));
				}
				else{
					Session::flash('error', 'Please select at least one error code.');
					return Redirect::back()->withInput();
				}

				Session::flash('success', 'A new department has been added.');
				return Redirect('departments');
			}
			catch (QueryException $e) {
				Session::flash('error', 'Please contact System Administrator with code ' . '<strong>' . $e->getCode() . '</strong>');
				return Redirect::back()->withInput();
			}
		}
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
		$data['process'] = Process::find($id);
		$data['sections'] = Section::all();
		$data['processesCount'] = Process::all()->count();
		$data['user'] = User::find(Session::get('user_name'));
		return view('roto.update-process', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		$process = Process::find($id);
		$process->name = $request->input('name');
		$process->section_id = $request->input('section');
		$process->save();

		Session::flash('success','Process has been updated successfuly');
		return redirect('processes');
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
