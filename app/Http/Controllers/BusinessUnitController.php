<?php namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Company;
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

class BusinessUnitController extends Controller {

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
		$data['businessUnits'] = BusinessUnit::all();
		$data['user'] = Users::find(Session::get('user_name'));
		$data['machine'] = Machine::find(Crypt::decrypt($id));
		return view('roto.business-units', $data);
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
		$data['businessUnitsCount'] = BusinessUnit::all()->count();
		$data['companies'] = Company::all();
		$data['machine'] = Machine::find(Crypt::decrypt($id));
		$data['user'] = Users::find(Session::get('user_name'));
		return view('roto.add-business-unit', $data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request, $id)
	{
		$businessUnit=array(
			"name"=>"Business Unit Name",
			"company"=>"Company Name",
		);
		$validator=Validator::make($request->all(),
			[
				"name"=>"required",
				"company"=>"required",
			]);
		$validator->setAttributeNames($businessUnit);
		if($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}
		else {
			try {
				$businessUnit = new BusinessUnit();
				$businessUnit->business_unit_name = $request->input('name');
				$businessUnit->company_id = $request->input('company');
				$businessUnit->save();

				Session::flash('success', 'A new business unit has been added.');
				return Redirect('business-units'.'/'.$id);
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
		$data['businessUnit'] = BusinessUnit::find($id);
		$data['businessUnitCount'] = BusinessUnit::all()->count();
		$data['companies'] = Company::all();
		$data['machine'] = Machine::find(Crypt::decrypt($machineID));
		$data['user'] = Users::find(Session::get('user_name'));
		return view('roto.update-business-unit', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id, $machineId)
	{
		$businessUnit = BusinessUnit::find($id);

		$businessUnit->business_unit_name = $request->input('name');
		$businessUnit->company_id = $request->input('company');
		$businessUnit->save();

		Session::flash('success','Business unit has been updated successfuly');
		return redirect('business-units'.'/'.$machineId);
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
