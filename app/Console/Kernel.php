<?php

namespace App\Console;

use App\Http\Controllers\AuthController;
use App\Modules\ParserLogic;
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
        '\App\Console\Commands\StartParseQueue',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /* $schedule->call(function () {
             DB::table('failed_jobs')->delete();
         })->hourly();
         $schedule->call(function () {
             DB::table('failed_jobs')->delete();
         })->hourly();*/
        $schedule->command('queue:work --daemon --queue=parser --tries=1')
            ->withoutOverlapping()
            ->runInBackground()
            ->everyMinute();
        $schedule->command('StartParseQueue:startparsing')
            ->everyMinute()
            ->runInBackground()
            //->sendOutputTo(storage_path('app/public/timeImg') . '/log.txt')
            ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
