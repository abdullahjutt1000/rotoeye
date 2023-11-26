<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\FetchLocalRecords',
        'App\Console\Commands\SendSdStatus',
        'App\Console\Commands\NotRespondingCircuit',
        'App\Console\Commands\CalculateOEE',
        'App\Console\Commands\ImportGroupDashboardData',
        'App\Console\Commands\ImportGroupDashboardDataInChunks',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cron:fetchlocalrecords')->everyThirtyMinutes()->withoutOverlapping(60);
        $schedule->command('cron:sendsdstatus')->hourly();
        $schedule->command('cron:notrespondingcircuit')->hourly();
        $schedule->command('cron:calculateoee')->everyFourHours();
        $schedule->command('cron:importgroupdashboarddata')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
