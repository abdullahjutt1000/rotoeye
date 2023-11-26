<?php

namespace App\Console\Commands;

use App\Http\Controllers\LoggerController;
use App\Http\Controllers\ProductivityController;
use App\Models\Machine;
use App\Models\Productivity;
use App\Models\Shift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateOEE extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:calculateoee';

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
        Log::info("======================Cron OEE Started(".date('Y-m-d H:i:s').")========================");
        try {

            $date = date('Y-m-d',strtotime('-1 day', strtotime(date('Y-m-d H:i:s'))));
            $oee = new ProductivityController();
            $response = $oee->oee_dashboard($date,$date);
            foreach ($response as $res)
            {
                Log::info('Date: '.$res['date'].' shift_number: '.$res['shift'].' machine_id: '.$res['machine_id'].' response: '.$res['response']);
            }

        }
        catch (\Exception $e)
        {   Log::info("======================Cron OEE Exception(".date('Y-m-d H:i:s').")========================");
            Log::info($e);
            Log::info("======================Cron OEE Exception(".date('Y-m-d H:i:s').")========================");
            Log::info("======================Cron OEE Ended(".date('Y-m-d H:i:s').")========================");
        }
        Log::info("======================Cron OEE Ended(".date('Y-m-d H:i:s').")========================");
    }
}
