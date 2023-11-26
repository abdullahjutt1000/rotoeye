<?php namespace App\Http\Controllers;


use App\Models\Categories;
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

class CategoriesController extends Controller {

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
       
		$data['categories'] = Categories::all();
        $data['user'] = Users::find(Session::get('user_name'));
		$data['machine'] = Machine::find(Crypt::decrypt($id));
		return view('roto.categories', $data);
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
		$data['categoriesCount'] = Categories::all()->count();
		$data['errorCodes'] = Error::all();
		
		$data['user'] = Users::find(Session::get('user_name'));
		$data['machine'] = Machine::find(Crypt::decrypt($id));
        
		return view('roto.add-category', $data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request, $id)
	{
		$category=array(
			"name"=>"Category Name",
			
		);
		$validator=Validator::make($request->all(),
			[
				"name"=>"required",
				
			]);
		$validator->setAttributeNames($category);
		if($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}
		else {
			try { //dd($request ->all());
				if(count($request->input('errorCodes')) > 0){
					$category = new Categories();
					$category->name = $request->input('name');
					
					$category->save();
					$category->errorcatCodes()->attach($request->input('errorCodes'));
				}
				else{
					Session::flash('error', 'Please select at least one error code.');
					return Redirect::back()->withInput();
				}

				Session::flash('success', 'A new Category has been added.');
				return Redirect('categories'.'/'.$id);
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
		$data['categories'] = Categories::find($id);
		$data['categoriesCount'] = Categories::all()->count();
		
		$data['errorCodes'] = Error::all();
		$data['machine'] = Machine::find(Crypt::decrypt($machineID));
		$data['user'] = Users::find(Session::get('user_name'));
        
		return view('roto.update-category', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id, $machineID)
	{
		$category = Categories::find($id);
		$category->name = $request->input('name');
		

		$category->errorcatCodes()->detach();
		$category->errorcatCodes()->attach($request->input('errorCodes'));

		$category->save();

		Session::flash('success','Category has been updated successfuly');
		return redirect('categories'.'/'.$machineID);
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
