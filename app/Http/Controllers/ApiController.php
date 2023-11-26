<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Machine;



class ApiController extends Controller
{
    //
    // Updated by Abdullah 16-11-23 start
    public function GetMachineData($sap_code){


        $machines= Machine::select('id','sap_code','bin1','bin2')->where('sap_code', '=', $sap_code)->get();
        $machines->each(function ($machine) use ($sap_code) {
            $machine->binfile_url_bin1 = $machine->bin1 ? url("/machines/{$sap_code}/{$machine->bin1}") : null;
            $machine->binfile_url_bin2 = $machine->bin2 ? url("/machines/{$sap_code}/{$machine->bin2}") : null;
        });

        if ($machines->isEmpty()) {
            return response()->json(['message' => 'Invalid sap_code']);
        }
        return response()->json($machines);


    }

    // Updated by Abdullah 16-11-23 end

}
