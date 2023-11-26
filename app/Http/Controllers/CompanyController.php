<?php namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Machine;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($id)
	{
	    $user_id = Session::get('user_id');
	    if($user_id){
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

            $data['companies'] = Company::all();
            $data['machine'] = Machine::find(Crypt::decrypt($id));
            $data['user'] = Users::find(Session::get('user_name'));
            return view('roto.companies', $data);
        }
	    else{
	        Session::flash('error', 'Please login again to continue.');
	        return \redirect('login');
        }
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
		$data['companiesCount'] = Company::all()->count();
		$data['machine'] = Machine::find(Crypt::decrypt($id));
		$data['user'] = Users::find(Session::get('user_name'));
		return view('roto.add-company', $data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request, $id)
	{
		$company=array(
			"name"=>"Company Name",
			"address"=>"Address",
			"city"=>"City",
			"country"=>"Country",
		);
		$validator=Validator::make($request->all(),
			[
				"name"=>"required",
				"address"=>"required",
				"city"=>"required",
				"country"=>"required",
			]);
		$validator->setAttributeNames($company);
		if($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}
		else {
			try {
				$company = new Company();
				$company->name = $request->input('name');
				$company->address = $request->input('address');
				$company->city = $request->input('city');
				$company->country = $request->input('country');
				$company->save();

				Session::flash('success', 'A new company has been added.');
				return Redirect('companies'.'/'.$id);
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
		$data['company'] = Company::find($id);
        $data['machine'] = Machine::find(Crypt::decrypt($machineID));
		$data['companiesCount'] = Company::all()->count();
        $data['user'] = Users::find(Session::get('user_name'));
		return view('roto.update-company', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id, $machineID)
	{
		$company = Company::find($id);
		$company->name = $request->input('name');
		$company->address = $request->input('address');
		$company->city = $request->input('city');
		$company->country = $request->input('country');
		$company->save();

		Session::flash('success','Company has been updated successfuly');
		return redirect('companies'.'/'.$machineID);
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
