<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Job;
use App\Models\LoginRecord;
use App\Models\Machine;
use App\Models\Machine_User;
use App\Models\Record;
use App\Models\User;
use App\Models\Users;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use PhpParser\Error;
use Psy\Exception\ErrorException;
use Route;
use Symfony\Component\Debug\Exception\FatalErrorException;
use URL;

class UserController extends Controller {

    public function login(){
        Session::forget("user_id");
        Session::forget("user_name");
        Session::forget("rights");
        Session::forget("name");
        Session::forget("user");

        $data['machines'] = Machine::all();
        return view('roto.login', $data);
    }

    public function doLogin(Request $request){
        $names = array(
            "username" => "User Name",
            "password" => "Password",
        );
        $validator = Validator::make($request->all(), [
            "username" => "required",
            "password" => "required",
        ]);
        $validator->setAttributeNames($names);
        if ($validator->fails()) {
            Session::flash("error", "Please fill in the valid information");
            return Redirect::back() ->withErrors($validator)->withInput();
        }
        try {
            $user = Users::where("login", "=", $request->input("username"))->where("password", "=", md5($request->input("password")))->get();
            $data['client'] = $user;
            if (count($user) == 1) {
                Log::info("------User Found------");
                Log::info("User ID: ".$user[0]->id);
                Log::info("Name: ".$user[0]->name);
                Log::info("IP Address: ".$request->input('ipAddress'));

                if($user->first()->wrong_attempts < 3){
                    $user[0]->wrong_attempts = NULL;
                    $user[0]->save();

                    Session::put("user_name", $user->first()->login);
                    Session::put("rights", $user->first()->rights);
                    Session::put("name", $user->first()->name);
                    Session::put("user_id", $user->first()->id);
                    Session::put("user", "logged in");

                    if($user->first()->rights == 0 ){
                        Log::info("Machine: ".$request->input('machine'));
                        Log::info("------End User------");

                        $data['machine'] = Machine::find($request->input('machine'));
                        $loginRecord = LoginRecord::where('machine_id', '=', $data['machine']->id)->get();
                        if(count($loginRecord) == 1){
                            $loginRecord[0]->user_id = $user[0]->id;
                            $loginRecord[0]->save();
                            return redirect('dashboard'.'/'.Crypt::encrypt($data['machine']->id));
                        }
                        else{
                            return redirect('select/job'.'/'.Crypt::encrypt($data['machine']->id));
                        }
                    }
                    else {

                        Log::info("-----------------------End User Found-----------------------------");

                        if(strtotime($user[0]->last_password_date) < strtotime(date('Y-m-d').'- 6 months')){
                            return redirect('password/expired');
                        }
                        else{
                            return redirect('production/dashboard');
                        }

                    }
                }
                else{
                    Session::flash('error','Your account has been locked due to wrong password attempts. Please contact System Administrator');
                    return Redirect::back()->withInput();
                }
            }
            else {
                $user = Users::where("login", "=", $request->input("username"))->get();
                if (count($user) == 1) {
                    if($user->first()->rights != 0){
                        if($user->first()->wrong_attempts < 3){
                            $user[0]->wrong_attempts = $user[0]->wrong_attempts+1;
                            $user[0]->save();
                            Session::flash("error", "Login Details are incorrect");
                            return Redirect::back()->withInput();
                        }
                        else{
                            Session::flash('error','Your account has been locked due to wrong password attempts. Please contact System Administrator');
                            return Redirect::back()->withInput();
                        }
                    }
                    else{
                        Session::flash("error", "Login Details are incorrect");
                        return Redirect::back()->withInput();
                    }
                }
                else{
                    Session::flash("error", "Login Details are incorrect");
                    return Redirect::back()->withInput();
                }
            }
        }
        catch (ModelNotFoundException $e)
        {
            Session::flash("error", "Login Details are incorrect");
            return Redirect::back()->withInput();
        }
    }

    public function changeUser(Request $request){
        $names = array(
            "username" => "User Name",
            "password" => "Password",
        );
        $validator = Validator::make($request->all(), [
            "username" => "required",
            "password" => "required",
        ]);
        $validator->setAttributeNames($names);
        if ($validator->fails()) {
            Session::flash("error", "Please fill in the valid information");
            return Redirect::back() ->withErrors($validator) ->withInput();
        }
        try {
            $user = Users::where("login", "=", $request->input("username"))->where("password", "=", md5($request->input("password")))->get();
            $data['client'] = $user;
            Log::info("User Found");
            if (count($user) == 1) {
                if($user[0]->rights == 0){
                    $machineUser = Machine_User::where('user_id', '=', $user[0]->id)->where('machine_id', '=', Session::get('machine_id'))->get();
                    if(count($machineUser) == 1){
                        $loginRecord = LoginRecord::where('machine_id', '=', Session::get('machine_id'))->get();
                        if(count($loginRecord) == 1){
                            $loginRecord[0]->user_id = $user[0]->id;
                            $loginRecord[0]->save();
                            Session::forget('user_name');
                            Session::forget('rights');
                            Session::forget('name');
                            Session::put("user_name", $user->first()->login);
                            Session::put("rights", $user->first()->rights);
                            Session::put("name", $user->first()->name);
                        }
                        return redirect('dashboard');
                    }
                    else{
                        Session::flash("error", "This machine is not Allowed");
                        return Redirect::back();
                    }
                }
                else{
                    Session::flash("error", "You cannot log in as operator");
                    return Redirect::back();
                }
            }
            else {
                Session::flash("error", "Login Details are incorrect");
                return Redirect::back();
            }
        }
        catch (ModelNotFoundException $e) {
            Session::flash("error", "Login Details are incorrect");
            return Redirect::back();
        }
    }

    public function checkUserAccess(Request $request){
        $data['rights'] = Users::select('id', 'rights')->where('login', '=', $request->input('userName'))->get();
        $id = $data['rights'][0]->id;
        $data['allowedMachines'] = Machine::with('allowedUsers')->whereHas('allowedUsers', function($query) use ($id){
            $query->where('user_id', '=', $id);
        })->get();
        return response(json_encode($data), 200);
    }

    public function index($id){
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
        elseif(Session::get('rights') ==4){
            $data['layout'] = 'simple-layout';

        }
        $data['machine'] = Machine::find(Crypt::decrypt($id));
        $data['users'] = Users::all();
        $data['user'] = Users::find(Session::get('user_name'));
        return view('roto.users', $data);
    }

    public function create($id){
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
        $data['operatorCount'] = Users::where('rights', '=', '0')->count();
        $data['powerUserCount'] = Users::where('rights', '=', '2')->count();
        $data['adminCount'] = Users::where('rights', '=', '1')->count();
        $data['reportUserCount'] = Users::where('rights', '=', '3')->count();

        $data['user'] = Users::find(Session::get('user_name'));
        $data['users'] = Users::all();
        return view('roto.add-user', $data);
    }

    public function store(Request $request, $id){
        $user=array(
            "id"=>"Employee ID",
            "name"=>"Employee Name",
            "cnic"=>"CNIC",
            "password"=>"Password",
            "designation"=>"Designation",
            "rights"=>"Rights",
            "picture"=>"Picture"
        );
        $validator=Validator::make($request->all(),
            [
                "id"=>"required|unique:users,id",
                "name"=>"required",
                "cnic"=>"required|unique:users,cnic|digits:13",
                "password"=>"required",
                "designation"=>"required",
                "rights"=>"required",
            ]);
        $validator->setAttributeNames($user);
        if($validator->fails())
        {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        else{
            try{
                global $fileName;
                $user = new Users();
                $user->id = $request->input('id');
                $user->cnic = $request->input('cnic');
                $user->name = $request->input('name');
                $user->login = $request->input('id');
                $user->password = md5($request->input('password'));
                $user->designation = $request->input('designation');
                $user->rights = $request->input('rights');
                if($request->file('picture') == NULL){
                    $fileName = 'nophoto.jpg';
                }
                else{
                    $file = $request->file('picture');
                    if(isset($file)){
                        $extensions = array(
                            'jpg', 'png', 'JPG', 'PNG', 'JPEG', 'jpeg'
                        );
                        if ($request->file('picture')->isValid()){
                            if($request->file('picture')->getClientOriginalName()){
                                if (in_array($request->file('picture')->getClientOriginalExtension(), $extensions)) {
                                    $fileName = $request->file('picture')->getClientOriginalName();
                                    $request->file('picture')->move('assets/global/portraits', $fileName);
                                }
                                else {
                                    Session::flash('error', "File is not valid");
                                    echo 'Not Valid';
                                    return Redirect::back()
                                        ->withErrors($validator)
                                        ->withInput();
                                }
                            }
                        }
                    }
                }
                $user->photo = $fileName;
                $user->save();
                Session::flash('success','A new user has been added.');
                return Redirect('users'.'/'.$id);
            }
            catch(QueryException $e){
                Session::flash('error','Please contact System Administrator with code '.'<strong>'.$e->getCode().'</strong>');
                return Redirect::back()->withInput();
            }
            catch(FatalErrorException $fe){
                Session::flash('error','Please contact System Administrator with code '.'<strong>'.$fe->getCode().'</strong>');
                return Redirect::back()->withInput();
            }

        }
    }

    public function destroy($id){
        try{
            Users::destroy($id);
            Session::flash('success','User deleted Successfully');
            return redirect(URL::to('users'));
        }
        catch(QueryException $e){
            Session::flash('error','Please contact System Administrator with code '.'<strong>'.$e->getCode().'</strong>');
            return Redirect::back();
        }
    }

    public function edit($id, $machine){
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
        try{
            $data['machine'] = Machine::find(Crypt::decrypt($machine));
            $data['operatorCount'] = Users::where('rights', '=', '0')->count();
            $data['powerUserCount'] = Users::where('rights', '=', '2')->count();
            $data['adminCount'] = Users::where('rights', '=', '1')->count();
            $data['reportUserCount'] = Users::where('rights', '=', '3')->count();
            $data['employee'] = Users::find($id);
            $data['users'] = Users::all();
            $data['user'] = Users::find(Session::get('user_name'));
            return view('roto.update-user', $data);
        }
        catch(QueryException $e){
            Session::flash('error','Please contact System Administrator with code '.'<strong>'.$e->getCode().'</strong>');
            return Redirect::back();
        }
    }

    public function update(Request $request, $id, $machine_id){
        $user=array(
            "id"=>"Employee ID",
            "name"=>"Employee Name",
            "cnic"=>"CNIC",
            "designation"=>"Designation",
            "rights"=>"Rights",
            "picture"=>"Picture"
        );
        $validator=Validator::make($request->all(),
            [
                "id"=>"required|unique:users,id,$id",
                "name"=>"required",
                "cnic"=>"required",
                "designation"=>"required",
                "rights"=>"required",
            ]);
        $validator->setAttributeNames($user);
        if($validator->fails())
        {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        else{
            try{
                global $fileName;
                $user = Users::find($id);
                $user->id = $request->input('id');
                $user->name = $request->input('name');
                $user->login = $request->input('id');
                $user->cnic = $request->input('cnic');
                $user->designation = $request->input('designation');
                $user->rights = $request->input('rights');
                if($request->file('picture') == NULL){
                    $fileName = 'nophoto.jpg';
                }
                else{
                    $file = $request->file('picture');
                    if(isset($file)){
                        $extensions = array(
                            'jpg', 'png', 'JPG', 'PNG', 'JPEG', 'jpeg'
                        );
                        if ($request->file('picture')->isValid()){
                            if($request->file('picture')->getClientOriginalName()){
                                if (in_array($request->file('picture')->getClientOriginalExtension(), $extensions)) {
                                    $fileName = $request->file('picture')->getClientOriginalName();
                                    $request->file('picture')->move('assets/global/portraits', $fileName);
                                }
                                else {
                                    Session::flash('error', "File is not valid");
                                    echo 'Not Valid';
                                    return Redirect::back()
                                        ->withErrors($validator)
                                        ->withInput();
                                }
                            }
                        }
                    }
                }
                $user->photo = $fileName;
                $user->save();

                if($user->id != $id){
                    $records = Record::where('user_id', '=', $user->id)->get();
                    $loginRecord = LoginRecord::where('user_id', '=', $user->id)->first();
                    if(count($records) > 0){
                        foreach ($records as $record){
                            $record->user_id = $user->id;
                            $record->save();
                        }
                    }
                    if(isset($loginRecord)){
                        $loginRecord->user_id = $user->id;
                        $loginRecord->save();
                    }
                }

                Session::flash('success','A new user has been updated.');
                return Redirect('users'.'/'.$machine_id);

            }
            catch(QueryException $e){
                Session::flash('error','Please contact System Administrator with code '.'<strong>'.$e->getCode().'</strong>');
                return Redirect::back()->withInput();
            }
            catch(FatalErrorException $fe){
                Session::flash('error','Please contact System Administrator with code '.'<strong>'.$fe->getCode().'</strong>');
                return Redirect::back()->withInput();
            }

        }
    }

    public function changePassword($id){
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
        return view('roto.change-password', $data);
    }

    public function changePasswordUser($id){
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
        $data['user'] = Users::find(Session::get('user_name'));
        // $data['machine'] = Machine::find(Crypt::decrypt($machineID));
        $data['machine'] = Machine::find(Crypt::decrypt($id));
        $data['employee'] = Users::find($id);
        return view('roto.change-password-user', $data);
    }

    public function storeChangePassword(Request $request, $id, $machineID){
        if($id == 0){
            $user = Users::find(Session::get('user_name'));
            $oldPassword = $user->password;
            if($oldPassword == md5($request->input('old_password'))){
                $passwordData = array(
                    "old_password" => "Old Password",
                    "new_password" => "New Password",
                    "new_password_confirmation" => "Confirm Password",
                );
                $validator = Validator::make($request->all(),
                    [
                        "old_password" => "required",
                        "new_password" => "required|confirmed",
                        "new_password_confirmation" => "required",
                    ]);
                $validator->setAttributeNames($passwordData);

                if ($validator->fails()) {
                    return Redirect::back()
                        ->withErrors($validator)
                        ->withInput();
                }
                else{
                    $user->password = md5($request->input('new_password'));
                    $user->save();

                    Session::flash("success", "Password Changed Successfuly");
                    return Redirect('dashboard'.'/'.$machineID);
                }
            }
            else{
                Session::flash("error", "Old Password does not match. Please try again");
                return Redirect::back()->withInput();
            }
        }
        else{
            $user = Users::find($id);
            $passwordData = array(
                "new_password" => "New Password",
                "new_password_confirmation" => "Confirm Password",
            );
            $validator = Validator::make($request->all(),
                [
                    "new_password" => "required|confirmed",
                    "new_password_confirmation" => "required",
                ]);
            $validator->setAttributeNames($passwordData);
            if ($validator->fails()) {
                return Redirect::back()
                    ->withErrors($validator)
                    ->withInput();
            }
            else{
                $user->password = md5($request->input('new_password'));
                $user->save();

                Session::flash("success", "Password Changed Successfuly");
                return Redirect('dashboard'.'/'.$machineID);
            }
        }

    }

    public function passwordExpired(){
        $data['user'] = Users::find(Session::get('user_name'));
        return view('roto.password-expired', $data);
    }

    public function storeExpiredPassword(Request $request){
        $user = Users::find(Session::get('user_name'));
        $passwordData = array(
            "new_password" => "New Password",
            "new_password_confirmation" => "Confirm Password",
        );
        $validator = Validator::make($request->all(),
            [
                "new_password" => "required|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/",
                "new_password_confirmation" => "required",
            ]);
        $validator->setAttributeNames($passwordData);
        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput();
        }
        else{
            $user->password = md5($request->input('new_password'));
            $user->last_password_date= date('Y-m-d');
            $user->save();

            Session::flash("success", "Password Changed Successfuly");
            return Redirect('/');
        }
    }
}
