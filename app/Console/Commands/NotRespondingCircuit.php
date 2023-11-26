<?php

namespace App\Console\Commands;

use App\Models\CircuitLog;
use App\Models\CircuitRecords;
use App\Models\Machine;
use App\Models\Record;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

//// mine code 
use App\Models\Settings;
///

class NotRespondingCircuit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:notrespondingcircuit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info("======================Cron Job Not Responding Circuits Started(".date('Y-m-d H:i:s').")========================");
        try{
            $machines = Machine::whereNull('is_disabled')->get();
            $settings = Settings:: where("id",1)->first();
            $data['machines'] = [];
            $now = date('Y-m-d H:i:s');
            foreach($machines as $machine){
                $record = Record::where('machine_id', '=', $machine->id)->latest('run_date_time')->limit(1)->get();

                $records = Record::select('run_date_time as time')
                    ->where('machine_id', '=', $machine->id)
                    ->latest('run_date_time')
                    ->union(CircuitRecords::select('LDT as time')
                        ->where('num_id', '=', $machine->sap_code)
                        ->latest('LDT'))
                    ->orderby('time', 'DESC')
                    ->limit(1)
                    ->get();
                if(!$record->isEmpty()){
                    $minutesDiff = $this->calculateMinutes($now, $record[0]->run_date_time);
                    if($minutesDiff > 20){
                        $lastDataReceived = date_diff(date_create($record[0]->run_date_time), date_create($now))->format("%y Year %m Month %d Day %h Hr %i Min %s Sec");
                        /// mine code 
                        $lastDataReceivedInDays = date_diff(date_create($record[0]->run_date_time), date_create($now))->days;
                        ////
                        
                        $circuit_logs = new CircuitLog();
                        $circuit_logs->machine_id = $machine->id;
                        $circuit_logs->last_run_date_time = $record[0]->run_date_time;
                        $circuit_logs->not_responding_age = $lastDataReceived;
                        $circuit_logs->category="not_responding_circuits";
                        $circuit_logs->save();

                        array_push($data['machines'],[
                            "machine_id"=>$machine->sap_code,
                            "machine_name"=>$machine->name,
                            "machine_ip"=>$machine->ip,
                            "last_run_date_time"=>$record[0]->run_date_time,
                            "last_received"=>$lastDataReceived,
                            "last_received_days"=>$lastDataReceivedInDays,
                            "no_of_days"=>$settings->value
                        ]);
                    }
                }
            }
            if(!empty($data['machines'])){
                try{
                    /////mine code
                    usort($data['machines'], function($a, $b) {
                        return $a["last_received_days"] - $b["last_received_days"];
                    });
                   
                    ///
                    Mail::send('emails.not-responding-circuit', $data, function ($message) use ($data) {
                        $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                        $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                            //->cc('haseeb.khan@packages.com.pk', 'Haseeb Khan')
                            ->cc('shaukat.hussain@packages.com.pk', 'Shaukat Hussain')
                            ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                            ->bcc('faisal@websouls.com', 'Faisal')
                            ->subject("RotoEye Cloud  - Not Responding Circuits");
                        
                    });
                }
                catch(\Exception $e){
                    Log::info('<<<< EXCEPTION >>>>');
                    Log::info('Exception while sending email for not responding circuits');
                    Log::info($e->getMessage());
                    Log::info('<<<< EXCEPTION >>>>');
                }
            }


        }
        catch (\Exception $e){
            Log::info('<<<< EXCEPTION >>>>');
            Log::info($e->getMessage());
            Log::info('Exception while checking not responding circuits');
            Log::info('<<<< EXCEPTION >>>>');
        }
        Log::info("======================Cron Job Not Responding Circuits Ended(".date('Y-m-d H:i:s').")========================");
    }
    public function calculateMinutes($fromTime, $toTime){
        $diff = date_diff( date_create($toTime), date_create($fromTime));
        $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
        if( $diff->invert){
            return -1 * $total;
        }
        else{
            return $total;
        }
    }
}
