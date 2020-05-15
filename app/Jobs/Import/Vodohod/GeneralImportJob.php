<?php

namespace App\Jobs\Import\Vodohod;

use Facades\App\Components\Vendor\Vodohod\Client;
use App\Jobs\Import\ImportJob;

class GeneralImportJob extends ImportJob
{
    /**
     * @inheritdoc
     */
    protected $configPath = 'import.vendors.vodohod';

    /**
     **/
    protected $loggerName = 'Vodohod:General';


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

        } catch (\Exception $e) {
            $this->error("Exception: ".$e->getMessage()."(file: ".$e->getFile()."; line: ".$e->getLine().")");
        }
    }
}
