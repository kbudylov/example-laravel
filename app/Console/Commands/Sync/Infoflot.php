<?php

namespace App\Console\Commands\Sync;

use Illuminate\Console\Command;
use App\Jobs\Sync\Infoflot\SyncCruiseCabinStatusesJob;
use App\Model\Cruise;
use App\Model\CruiseSource;

class Infoflot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:infoflot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncing cruise cabin statuses and prices';

    /**
     * @var CruiseSource
     */
    protected $vendor;


    /**
     * Volgaline constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->vendor = CruiseSource::findByPrefix('infoflot');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cruiseList = $this->vendor->cruises;
        /** @var Cruise $cruise */
        foreach ($cruiseList as $i => $cruise) {
            dispatch((new SyncCruiseCabinStatusesJob($cruise))->onQueue(config('import.vendors.infoflot.import.jobs.syncCabinStatus.queue','default')));
        }
        if (!empty($i)) {
            $this->info(($i + 1).' cruises processed');
        } else {
            $this->info('No cruises processed');
        }
    }
}
