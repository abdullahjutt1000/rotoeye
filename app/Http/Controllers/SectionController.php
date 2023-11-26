<?php namespace App\Http\Controllers;

use App\Models\Department;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Machine;
use App\Models\Section;
use App\Models\User;
use App\Models\Users;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller {

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
		$data['sections'] = Section::all();
		$data['user'] = Users::find(Session::get('user_name'));
        $data['machine'] = Machine::find(Crypt::decrypt($id));
		return view('roto.sections', $data);
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
        $data['departments'] = Department::all();
        $data['sections'] = Section::all();
        $data['user'] = Users::find(Session::get('user_name'));
        $data['machine'] = Machine::find(Crypt::decrypt($id));
        return view('roto.add-section', $data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request, $id)
	{
        $sections=array(
            "name"=>"Section Name",
            "department"=>"Department Name",
        );
        $validator=Validator::make($request->all(),
            [
                "name"=>"required",
                "department"=>"required",
            ]);
        $validator->setAttributeNames($sections);
        if($validator->fails())
        {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        else {
            try {
                $section = new Section();
                $section->name = $request->input('name');
                $section->department_id = $request->input('department');
                $section->save();

                Session::flash('success', 'A new section has been added.');
                return Redirect('sections'.'/'.$id);
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
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
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
