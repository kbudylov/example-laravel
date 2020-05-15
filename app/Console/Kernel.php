<?php

namespace App\Console;

use App\Console\Commands\Booking\DeleteOrderCommand;
use App\Console\Commands\Booking\SyncBookingToDealsCommand;
use App\Console\Commands\Booking\SyncCabinStatusesCommand;
use App\Console\Commands\Booking\SyncDealsToOrdersCommand;
use App\Console\Commands\Booking\CreateDealCommand;
use App\Console\Commands\ImportAll;
use App\Console\Commands\Sync\Infoflot as InfoflotSync;
use \App\Console\Commands\Sync\Volgaline as VolgalineSync;
use App\Console\Commands\Sync\Vodohod as VodohodSync;

use App\Console\Commands\TestCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel
 * @package App\Console
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        VolgalineSync::class,
        InfoflotSync::class,
        VodohodSync::class,
        ImportAll::class,
        CreateDealCommand::class,
        SyncBookingToDealsCommand::class,
        SyncDealsToOrdersCommand::class,
        SyncCabinStatusesCommand::class,
        DeleteOrderCommand::class,
        TestCommand::class
	  ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('sync:volgaline')->everyFiveMinutes();
	    //$schedule->command('sync:infoflot')->everyFiveMinutes();
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
