<?php

namespace App\Jobs\Sync;

use App\Jobs\Import\ImportJob;
use App\Model\Cruise;

/**
*
**/
abstract class SyncJob extends ImportJob
{
    /**
    * @var Cruise
    */
    protected $cruise;

    /**
    *
    **/
    public function __construct(Cruise $cruise)
    {
        parent::__construct();
        $this->cruise = $cruise;
    }

    protected function getCruise()
    {
        return $this->cruise;
    }
}

?>
