<?php

namespace App\Jobs\Import\Volgaline;

use Facades\App\Components\Vendor\Volgaline\Client;
use App\Jobs\Import\ImportJob;

/**
 * Initial Volgaline import job
 * @package App\Jobs\Import
 */
class GeneralImportJob extends ImportJob
{
	/**
	 * @inheritdoc
	 */
	protected $configPath = 'import.vendors.volgaline';

	/**
	 * Handle job
	 */
	public function handle()
	{
		$volgalineShipList = Client::getShipList();
		if(!empty($volgalineShipList)){
			$shipVendorIds = [];
			foreach ($volgalineShipList as $shipInfo){
				$shipVendorIds[] = $shipInfo->id;
				dispatch((new ShipImportJob($shipInfo))->onQueue($this->config('import.jobs.ships.queue','default')));
			}
			$this->deleteOldShips($shipVendorIds);
		} else {
			$this->warn('Ships list is empty. Nothing to import.');
		}
	}

	/**
	 * @param $shipVendorIds
	 */
	protected function deleteOldShips($shipVendorIds)
	{
		$class = $this->config('import.jobs.deleteOldShips.class');
		$queue = $this->config('import.jobs.deleteOldShips.queue');

		dispatch((new $class($shipVendorIds))->onQueue($queue));
	}
}
