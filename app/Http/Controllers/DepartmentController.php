<?php namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Error;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Machine;
use App\Models\User;
use App\Models\Users;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
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
		$data['departments'] = Department::all();
		$data['user'] = Users::find(Session::get('user_name'));
		$data['machine'] = Machine::find(Crypt::decrypt($id));
		return view('roto.departments', $data);
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
		$data['departmentsCount'] = Department::all()->count();
		$data['errorCodes'] = Error::all();
		$data['businessUnits'] = BusinessUnit::all();
		$data['user'] = Users::find(Session::get('user_name'));
		$data['machine'] = Machine::find(Crypt::decrypt($id));
		return view('roto.add-department', $data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request, $id)
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
				return Redirect('departments'.'/'.$id);
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
	public function edit($id, $machineID)
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
		$data['department'] = Department::find($id);
		$data['departmentsCount'] = Department::all()->count();
		$data['businessUnits'] = BusinessUnit::all();
		$data['errorCodes'] = Error::all();
		$data['machine'] = Machine::find(Crypt::decrypt($machineID));
		$data['user'] = Users::find(Session::get('user_name'));
		return view('roto.update-department', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id, $machineID)
	{
		$department = Department::find($id);
		$department->name = $request->input('name');
		$department->business_unit_id = $request->input('businessUnit');

		$department->errorCodes()->detach();
		$department->errorCodes()->attach($request->input('errorCodes'));

		$department->save();

		Session::flash('success','Department has been updated successfuly');
		return redirect('departments'.'/'.$machineID);
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
