<?php

namespace App\Jobs\Import\Infoflot;

use App\Jobs\Import\ImportJob;

/**
 * Class DeleteOldShipsJob
 * @package App\Jobs\Import\Infoflot
 */
class DeleteOldShipsJob extends ImportJob
{
	/**
	 * @var string
	 */
	protected $configPath = 'import.vendors.infoflot';

	/**
	 * @var array
	 */
	protected $importedShipIds = [];

	/**
	 * DeleteOldShipsJob constructor.
	 *
	 * @param array $importedShipIds
	 */
  public function __construct(array $importedShipIds)
  {
			parent::__construct();
      $this->importedShipIds = $importedShipIds;
  }

  /**
  * Execute the job.
  *
  * @return void
  */
  public function handle()
  {
      $this->getVendor()->ships()->whereNotIn('vendorId',$this->importedShipIds,'AND')->delete();
  }
}
