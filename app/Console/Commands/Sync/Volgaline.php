<?php

namespace App\Console\Commands\Sync;

use App\Jobs\Sync\Volgaline\SyncCruiseCabinStatusesJob;
use App\Model\Cruise;
use Illuminate\Console\Command;
use App\Model\CruiseSource;

/**
 * Class Volgaline
 * @package App\Console\Commands\Sync
 */
class Volgaline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:volgaline';

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
        $this->vendor = CruiseSource::findByPrefix('volgaline');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cruiseList = Cruise::where(['vendor' => $this->vendor->id])->get();
        /** @var Cruise $cruise */
        foreach ($cruiseList as $i => $cruise) {
            dispatch((new SyncCruiseCabinStatusesJob($cruise))->onQueue(config('import.vendors.volgaline.import.jobs.syncCabinStatus.queue','default')));
        }
        if (!empty($i)) {
            $this->info(($i + 1).' cruises processed');
        } else {
            $this->info('No cruises processed');
        }
    }
}
