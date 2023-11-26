<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\CircuitRecords;
use App\Models\Error;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\LoginRecord;
use App\Models\Machine;
use App\Models\Record;
use App\Models\Records_From_Circuit;
use App\Models\Shift;
use App\Models\User;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TestController extends Controller
{
    public function data(){
        return '1';
        $data = App\Models\temp_table::all();
        return $data;

    }
}
