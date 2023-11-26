<?php

use App\Http\Controllers\ProductivityController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('Num/{num_id}/LDT/{ldt}/Mtr/{mtr}/Rpm/{rpm}/Sd/{sd}',[RecordController::class,'live_compressed']);


Route::get('rotoeye/Num/{num_id}/LDT/{ldt}/Mtr/{mtr}/Rpm/{rpm}/Sd/{sd}',[RecordController::class,'live_compressed_LAN']);

//Productivity api
Route::prefix('productivity')->group(function () {
    Route::get('group_productivity',[ProductivityController::class,'get_records']);
});

////
Route::get('SDjson/{str}',[DashboardController::class,'jsnquery']);

// Updated by Abdullah 16-11-23 start

    Route::get('machine/{sap_code}',[Apicontroller::class,'GetMachineData']);

// Updated by Abdullah 16-11-23 end
