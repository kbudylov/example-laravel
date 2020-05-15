<?php

namespace App\Console\Commands\Sync;

use App\Jobs\Import\Vodohod\CruiseImportJob;
use App\Model\CruiseSource;
use Illuminate\Console\Command;
use Facades\App\Components\Vendor\Vodohod\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Vodohod
 * @package App\Console\Commands\Sync
 */
class Vodohod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:vodohod';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var CruiseSource
     */
    protected $vendor;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var int
     */
    protected $countCruises = 0;

    /**
     * Vodohod constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->vendor = CruiseSource::findByPrefix('vodohod');
        $this->logger = new Logger('Vodohod logger');
        $this->logger->pushHandler(new StreamHandler(__DIR__.'/../../../../storage/logs/vodohod.import.log', Logger::ERROR));
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cruiseList = Client::getCruiseList();
        foreach ($cruiseList as $cruiseId => $cruiseInfo) {
            dispatch((new CruiseImportJob($cruiseInfo, $cruiseId))->onQueue(config('import.vendors.vodohod.import.jobs.cruises.queue','default')));
        }
    }
}
