<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Machine;
use App\Models\MaterialCombination;
use App\Models\User;
use App\Models\Users;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use PhpSpec\Exception\Exception;
use URL;

class MaterialsController extends Controller {

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
        $data['machine'] = Machine::find(Crypt::decrypt($id));
		$data['user'] = Users::find(Session::get('user_name'));
		$data['materials'] = MaterialCombination::all();
		return view('roto.materials', $data);
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
        $data['machine'] = Machine::find(Crypt::decrypt($id));
		$data['user'] = Users::find(Session::get('user_name'));
		$data['materials'] = MaterialCombination::all();
		return view('roto.add-material', $data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request,$id)
	{

		$material=array(
			"material_name"=>"Material Name",
			"nominal_speed"=>"Nominal Speed",
		);
		$validator=Validator::make($request->all(),
			[
				"material_name"=>"required|unique:material_combination,name",
				"nominal_speed"=>"required",
			]);
		$validator->setAttributeNames($material);

		if($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}
		else{
			try{
				$material = new MaterialCombination();
				$material->name = $request->input('material_name');
				$material->nominal_speed = $request->input('nominal_speed');
				$material->save();
				Session::flash('success','A new material has been added.');
				return Redirect('material/add/'.$id);
			}
			catch(QueryException $e){
                dd($e);
				Session::flash('error','Please contact System Administrator with code '.'<strong>'.$e->getCode().'</strong>');
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
	public function edit($machine_id,$material_id)
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
        $data['machine'] = Machine::find(Crypt::decrypt($machine_id));
		try{
			$data['material'] = MaterialCombination::find($material_id);
			$data['user'] = Users::find(Session::get('user_name'));
			return view('roto.update-material', $data);
		}
		catch(QueryException $e){
			Session::flash('error','Please contact System Administrator with code '.'<strong>'.$e->getCode().'</strong>');
			return Redirect::back();
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{

		$material=array(
			"material_name"=>"Material Name",
			"nominal_speed"=>"Nominal Speed",
		);
		$validator=Validator::make($request->all(),
			[
				"material_name"=>"required",
				"nominal_speed"=>"required",
			]);
		$validator->setAttributeNames($material);
		if($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}
		else{
			try{
				$material = MaterialCombination::find($id);
				$material->name = $request->input('material_name');
				$material->nominal_speed = $request->input('nominal_speed');
				$material->save();

				Session::flash('success','A new material has been Updated.');
                return Redirect::back()->withInput();
			}
			catch(QueryException $e){
				Session::flash('error','Please contact System Administrator with code '.'<strong>'.$e->getCode().'</strong>');
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
		try{
			MaterialCombination::destroy($id);
			Session::flash('success','Material deleted Successfully');
            return Redirect::back();
		}
		catch(QueryException $e){
			Session::flash('error','Please contact System Administrator with code '.'<strong>'.$e->getMessage().'</strong>');
			return Redirect::back();
		}
	}

}
