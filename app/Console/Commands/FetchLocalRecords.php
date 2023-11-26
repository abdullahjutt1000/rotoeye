<?php

namespace App\Console\Commands;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoggerController;
use App\Jobs\SendNotRespondingCircuitsEmail;
use App\Jobs\SendWrongDateTimeStringsEmail;
use App\Models\CircuitLog;
use App\Models\CircuitLogs;
use App\Models\Machine;
use App\Models\Record;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FetchLocalRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:fetchlocalrecords';

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
        try {
            Log::info("======================Cron Job Started(" . date('Y-m-d H:i:s') . ")========================");
            $machines = Machine::whereNull('is_disabled')->where("sd_status", "!=", 'XXXX')->where("sd_status", "!=", '0')->orderBy("sd_status","ASC")->get();
            $data['machines'] = [];
            $data['records'] = [];
            $data['wrongDateTimeStrings'] = [];

            foreach ($machines as $machine) {
                $logger = new LoggerController($machine);
                $logger->log('--------------------------------------------- CRON JOB START LOCAL RECORDS ---------------------------------------------', $machine);
                Log::info("Cron Fetch Local Records Started(" . date('Y-m-d H:i:s') . "): " . $machine->name);
                $count = 0;
                try {
                    $ip_address = $machine->ip;
                    $url = 'http://' . $ip_address . '/json';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    $result = curl_exec($ch);
                    $curl_err = curl_error($ch);
                    curl_close($ch);
                    if ($result !== false) {
                        /// my code
                        if($machine->sap_code=='22155'){
                            $string1  = str_replace("'", "", $result);
                            $string = preg_replace('/}{/', '},{', $string1);

                            // Wrap the string in square brackets to create a JSON array
                            $jsonString = '[' . $string . ']';
                            
                            // Decode the JSON string
                            $localRecords = json_decode($jsonString);
                            //dd($jsonArray);
                            }else{
                                $localRecords = json_decode($result);
                            }

                        /// end my code
                    // $localRecords = json_decode($result);
                        if (!empty($localRecords)) {
                            $logger->log('Local Records Count: ' . count($localRecords), $machine);
                            $logger->log('Machine ID: ' . $machine->id, $machine);
                            $logger->log('Machine IP: ' . $machine->ip, $machine);

                            $dashboardController = new DashboardController();
                            foreach ($localRecords as $localRecord) {
                                //Insert Records
                                try {
                                    $logger->log('__________________ START STRING __________________', $machine);
                                    $logger->log('Record String: ' . json_encode($localRecord), $machine);
                                    $logger->log('__________________ END STRING __________________', $machine);

                                    $dateTime = $localRecord->LDT;
                                    $date = substr($dateTime, 0, 10);
                                    $time = substr($dateTime, 11, 8);
                                    $dateTime = $date . ' ' . $time;

                                    $record = Record::select('job_id', 'user_id', 'process_id')->where('run_date_time', '<', $dateTime)->where('machine_id', '=', $machine->id)->orderby('run_date_time', 'desc')->limit(1)->get();
                                    if (count(array($record)) > 0) {
                                        if (date('Y', strtotime($dateTime)) == date('Y')) {
                                            $dashboardController->localRecordsLive($localRecord->Num, $dateTime, $localRecord->Mtr, $localRecord->Rpm, $record[0]->job_id, $record[0]->user_id, $record[0]->process_id);
                                            $count++;
                                        } else {
                                            array_push($data['wrongDateTimeStrings'], json_encode($localRecord));
                                        }
                                    }
                                } catch (\Exception $e) {
                                    $logger->log('<<<< EXCEPTION >>>>', $machine);
                                    $logger->log('Record String: ' . json_encode($localRecord), $machine);
                                    $logger->log($e->getMessage(), $machine);
                                    $logger->log('<<<< EXCEPTION >>>>', $machine);
                                }
                            }
                            //call device to dell log
                            try {
                                $url = 'http://' . $ip_address . '/DELog';
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $url);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                                $result = curl_exec($ch);
                                curl_close($ch);
                            } catch (\Exception $e) {
                                $logger->log('<<<< EXCEPTION >>>>', $machine);
                                $logger->log('Exception while deleting JSON file from circuit', $machine);
                                $logger->log('Got Exception: ' . $e->getMessage(), $machine);
                                $logger->log('<<<< EXCEPTION >>>>', $machine);
                            }
                        } else {
                            $logger->log('__________________ Cron Job No records __________________', $machine);
                        }
                    } else {
                        $logger->log('__________________ Cron Job No records __________________', $machine);
                    }
                } catch (\Exception $e) {
                    $logger->log('<<<< EXCEPTION >>>>', $machine);
                    $logger->log('Exception while getting JSON from Circuit', $machine);
                    $logger->log('Got Exception: ' . $e->getMessage(), $machine);
                    $logger->log('<<<< EXCEPTION >>>>', $machine);
                }
                $logger->log('--------------------------------------------- CRON JOB END LOCAL RECORDS ---------------------------------------------', $machine);
                array_push($data['machines'], [
                    "machine_id" => $machine->sap_code,
                    "machine_name" => $machine->name,
                    "records" => $machine->sd_status,
                    "count" => $count,
                    "ip_address" => $machine->ip,
                    "curl_error" => $curl_err
                ]);
                Log::info("Cron Fetch Local Records Ended(" . date('Y-m-d H:i:s') . "): " . $machine->name);
                $circuit_log = new CircuitLog();
                $circuit_log->machine_id = $machine->id;
                $circuit_log->sd_records = $machine->sd_status;
                $circuit_log->sd_inserted_records = $count;
                $circuit_log->category = 'fetch_local_records';
                $circuit_log->save();
            }

            if(!empty($data['machines'])){
                try{
                     /////mine code
                     usort($data['machines'], function($a, $b) {
                        return $a["records"] - $b["records"];
                    });
                    ///
                    Mail::send('emails.fetch-local-records', $data, function ($message) use ($data) {
                        $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                        $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                            ->cc('shaukat.hussain@packages.com.pk', 'Shaukat Hussain')
                            ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                            ->bcc('faisal@websouls.com', 'Faisal')
                            ->bcc('waqas@websouls.com', 'Waqas Waheed')
                            ->subject("RotoEye Cloud  - Fetch Local Record Status");
                    });
                }
                catch(\Exception $e){
                    Log::info('<<<< EXCEPTION >>>>');
                    Log::info('Exception while sending email for not responding circuits');
                    Log::info($e->getMessage());
                    Log::info('<<<< EXCEPTION >>>>');
                }
            }

            if(!empty($data['wrongDateTimeStrings'])){
                try{
                    Mail::send('emails.wrong-date-time-strings', $data, function ($message) use ($data) {
                        $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
                        $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                            ->to('a4ashraf23@gmail.com', 'Ashraf Wali')
                            //->cc('haseeb.khan@packages.com.pk', 'Haseeb Khan')
                            ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                            ->subject("RotoEye Cloud - Wrong Date & Time Strings");
                    });
                }
                catch (\Exception $e){
                    $logger->log('<<<< EXCEPTION >>>>', $machine);
                    $logger->log('Exception while sending email for wrong date and time strings.', $machine);
                    $logger->log('<<<< EXCEPTION >>>>', $machine);
                }
            }
            Log::info("======================Cron Job Ended(" . date('Y-m-d H:i:s') . ")==========================");
        } catch (\Exception $e) {
            Log::info("======================Fetch Local Records Cron Exception========================");
            Log::info("================================================================================");
            Log::info($e->getMessage());
            Log::info("================================================================================");
            Log::info("======================Fetch Local Records Cron Exception========================");

        }

    }

    public function calculateMinutes($fromTime, $toTime)
    {
        $diff = date_diff(date_create($toTime), date_create($fromTime));
        $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s / 60;
        if ($diff->invert) {
            return -1 * $total;
        } else {
            return $total;
        }
    }
}

