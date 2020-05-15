<?php

namespace App\Jobs\Import\Infoflot;

use App\Jobs\Import\ImportJob;
use App\Model\Ship;

/**
 * Class DeleteOldCruisesJob
 * @package App\Jobs\Import\Infoflot
 */
class DeleteOldCruisesJob extends ImportJob
{
	/**
	 * @var string
	 */
	protected $configPath = 'import.vendors.infoflot';

	/**
	 * @var array
	 */
	protected $importedCruiseIds = [];

	/**
	 * @var Ship
	 */
	protected $ship;

	/**
	 * DeleteOldCruisesJob constructor.
	 *
	 * @param $importedCruiseIds
	 * @param $shipId
	 */
    public function __construct(Ship $ship, array $importedCruiseIds)
    {
        parent::__construct();
        $this->importedCruiseIds = $importedCruiseIds;
        $this->ship = $ship;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(empty($this->importedCruiseIds)){
        	return;
        }
        $this->ship->cruises()->whereNotIn('vendorId',$this->importedCruiseIds,'AND')->delete();
    }
}
