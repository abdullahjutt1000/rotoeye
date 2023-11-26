<?php

namespace App\Console\Commands;

use App\Http\Controllers\LoggerController;
use App\Jobs\SendSdStatusEmail;
use App\Models\CircuitLog;
use App\Models\Machine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSdStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:sendsdstatus';

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
        Log::info('<<<< Start Cron Send SD Card Status >>>>');
        $machines = Machine::whereNull('is_disabled')->get();
        $data["machines"]=[];
        foreach ($machines as $machine)
        {

            $logger = new LoggerController($machine);
            if(!is_numeric($machine->sd_status))
            {
                $logger->log('------------------ Cron Start Sd Card Corrupted ------------------', $machine);
                array_push( $data["machines"],$machine);
                $circuit_log = new CircuitLog();
                $circuit_log->machine_id = $machine->id;
                $circuit_log->sd_records = $machine->sd_status;
                $circuit_log->category = "sd_records";
                $circuit_log->save();

                $logger->log('Sd Card Status: '. $machine->sd_status , $machine);
                $logger->log('------------------ Cron End Sd Card Corrupted ------------------', $machine);

            }
        }
        if(!empty( $data["machines"]))
        {
            try{

//                SendSdStatusEmail::dispatch($data);
                Mail::send('emails.alphabets-in-sd', $data, function ($message) use ($data) {
                    $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                    $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                        ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                        ->cc('shahid@websouls.com', 'Shahid Fareed')
                        ->bcc('waqas@websouls.com', 'Waqas Waheed')
                        ->bcc('faisal@websouls.com', 'Faisal Ejaz')
                        ->subject("RotoEye Cloud - SD Card's Corrupted");
                });
            }
            catch(\Exception $e){
                Log::info('<<<< EXCEPTION >>>>');
                Log::info('Exception while sending email for sd status');
                Log::info($e->getMessage());
                Log::info('<<<< EXCEPTION >>>>');
            }
        }

        Log::info('<<<< End Cron Send SD Card Status >>>>');
    }
}
