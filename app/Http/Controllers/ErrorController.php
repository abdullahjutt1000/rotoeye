<?php namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Department_Error;
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

class ErrorController extends Controller {

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
		$data['errorCodes'] = Error::all();
		$data['user'] = Users::find(Session::get('user_name'));
        $data['machine'] = Machine::find(Crypt::decrypt($id));
        return view('roto.error-codes', $data);
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
		$data['errorCodesCount'] = Error::all()->count();
		$data['departments'] = Department::all();
		$data['user'] = Users::find(Session::get('user_name'));
        $data['machine'] = Machine::find(Crypt::decrypt($id));
        $data['errorCodes'] = Error::all();
		$data['categories'] = Error::select('category')->distinct()->get();
		return view('roto.add-error-code', $data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request, $id)
	{
		$error=array(
			"id"=>"Error ID",
			"name"=>"Error Name",
			"category"=>"Error Category",
		);
		$validator=Validator::make($request->all(),
			[
				"id"=>"required|unique:errors",
				"name"=>"required|unique:errors",
			]);
		$validator->setAttributeNames($error);
		if($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}
		else {
			try {
				if(count($request->input('departments')) > 0){
					$error = new Error();
					$error->id = $request->input('id');
					$error->name = $request->input('name');
					$error->category = $request->input('category');
					$error->departments()->attach($request->input('departments'));
					$error->save();
				}
				else{
					Session::flash('error', 'Please select at least one department.');
					return Redirect::back()->withInput();
				}

				Session::flash('success', 'A new error has been added.');
				return Redirect('error-codes'.'/'.$id);
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
		$data['errorCode'] = Error::find($id);
		$data['errorCodesCount'] = Error::all()->count();
		$data['departments'] = Department::all();
		$data['user'] = User::find(Session::get('user_name'));
		return view('roto.update-error-code', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		$error = Error::find($id);
		$error->id = $request->input('id');
		$error->name = $request->input('name');
		$error->category = $request->input('category');
		$error->save();

		$error->departments()->detach();
		$error->departments()->attach($request->input('departments'));

		Session::flash('success','Error code has been updated successfuly');
		return redirect('error-codes');
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
