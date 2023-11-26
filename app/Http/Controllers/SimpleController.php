<?php

namespace App\Http\Controllers;

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

class SimpleController extends Controller
{
    //

    public function index(){
        $machine=machine::all();
        return view('roto.simple',compact('machine'));
    }

    public function Login(Request $request){
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
        else{
            $user = Users::where("login", "=", $request->input("username"))->where("password", "=", md5($request->input("password")))->get();
            $data['client'] = $user;
            $id= $user->id;
            if($user->first()->rights==4){
            // return redirect ('simple');
            return view('roto.simple');


            }
        }


        //     $user = Users::where("login", "=", $request->input("username"))->where("password", "=", md5($request->input("password")))->get();
        //     $data['client'] = $user;
        //     if (count($user) == 1) {
        //         Log::info("------User Found------");
        //         Log::info("User ID: ".$user[0]->id);
        //         Log::info("Name: ".$user[0]->name);
        //         Log::info("IP Address: ".$request->input('ipAddress'));

        //         if($user->first()->wrong_attempts < 3){
        //             $user[0]->wrong_attempts = NULL;
        //             $user[0]->save();

        //             Session::put("user_name", $user->first()->login);
        //             Session::put("rights", $user->first()->rights);
        //             Session::put("name", $user->first()->name);
        //             Session::put("user_id", $user->first()->id);
        //             Session::put("user", "logged in");

        //             if($user->first()->rights == 0 ){
        //                 Log::info("Machine: ".$request->input('machine'));
        //                 Log::info("------End User------");

        //                 $data['machine'] = Machine::find($request->input('machine'));
        //                 $loginRecord = LoginRecord::where('machine_id', '=', $data['machine']->id)->get();
        //                 if(count($loginRecord) == 1){
        //                     $loginRecord[0]->user_id = $user[0]->id;
        //                     $loginRecord[0]->save();
        //                     return redirect('dashboard'.'/'.Crypt::encrypt($data['machine']->id));
        //                 }
        //                 else{
        //                     return redirect('select/job'.'/'.Crypt::encrypt($data['machine']->id));
        //                 }
        //             }
        //             else {

        //                 Log::info("-----------------------End User Found-----------------------------");

        //                 if(strtotime($user[0]->last_password_date) < strtotime(date('Y-m-d').'- 6 months')){
        //                     return redirect('password/expired');
        //                 }
        //                 else{
        //                     return redirect('production/dashboard');
        //                 }

        //             }
        //         }
        //         else{
        //             Session::flash('error','Your account has been locked due to wrong password attempts. Please contact System Administrator');
        //             return Redirect::back()->withInput();
        //         }
        //     }
        //     else {
        //         $user = Users::where("login", "=", $request->input("username"))->get();
        //         if (count($user) == 1) {
        //             if($user->first()->rights != 0){
        //                 if($user->first()->wrong_attempts < 3){
        //                     $user[0]->wrong_attempts = $user[0]->wrong_attempts+1;
        //                     $user[0]->save();
        //                     Session::flash("error", "Login Details are incorrect");
        //                     return Redirect::back()->withInput();
        //                 }
        //                 else{
        //                     Session::flash('error','Your account has been locked due to wrong password attempts. Please contact System Administrator');
        //                     return Redirect::back()->withInput();
        //                 }
        //             }
        //             else{
        //                 Session::flash("error", "Login Details are incorrect");
        //                 return Redirect::back()->withInput();
        //             }
        //         }
        //         else{
        //             Session::flash("error", "Login Details are incorrect");
        //             return Redirect::back()->withInput();
        //         }
        //     }
        // }
        // catch (ModelNotFoundException $e)
        // {
        //     Session::flash("error", "Login Details are incorrect");
        //     return Redirect::back()->withInput();
        // }
    }
}
