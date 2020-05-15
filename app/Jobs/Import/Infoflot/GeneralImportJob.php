<?php

namespace App\Jobs\Import\Infoflot;

use Facades\App\Components\Vendor\Infoflot\Client;
use App\Jobs\Import\ImportJob;

/**
 * Initial Infoflot import job
 * @package App\Jobs\Import
 */
class GeneralImportJob extends ImportJob
{
	/**
	 * @inheritdoc
	 */
	protected $configPath = 'import.vendors.infoflot';

	/**
	 * @var array
	 */
	protected $importOnlyShipIds = [];

	/**
	 * @var null
	 */
	protected $importFromShipId = null;

	/**
	 * @var array
	 */
	protected $ignoreShipIds = [];

	/**
	**/
	protected $loggerName = 'Infoflot:General';

	/**
	 * @inheritdoc
	 */
	public function __construct()
	{
		parent::__construct();

		$this->importOnlyShipIds = $this->config('model.config.importOnlyShipIds',$this->importOnlyShipIds);
		$this->importFromShipId = $this->config('model.config.importFromShipIds',$this->importFromShipId);
		$this->ignoreShipIds = $this->config('model.config.ignoreShipIds',$this->ignoreShipIds);
	}

	/**
	 * @inheritdoc
	 */
    public function handle()
    {
    	try {
		    //todo: save images & schemes to redis and retreive nessessary in ShipImportJob
	    	$shipImages = Client::getShipTitleImages();
		    $shipSchemes = Client::getShipSchemes();

		    /** @var array  $shipList */
		    $shipList = Client::getShipList();

		    if(!empty($shipList)){

		    	$this->info('Found ['.count($shipList).'] ships');
			    $importedShipIds = [];
		    	foreach ($shipList as $shipInfo){
			    	if(!$this->shouldPassShip($shipInfo)) {
					    $shipPhotoUrl = !empty($shipImages[$shipInfo->id]) ? $shipImages[$shipInfo->id] : null;
					    $shipSchemeUrl = !empty($shipSchemes[$shipInfo->id]) ? $shipSchemes[$shipInfo->id] : null;
					    dispatch((new ShipImportJob($shipInfo, $shipPhotoUrl, $shipSchemeUrl))->onQueue($this->config('import.jobs.ships.queue','default')));
					    $importedShipIds[] = $shipInfo->id;
				    } else {
					    $this->info('Ship [vendorId: '.$shipInfo->id.']: passing due to configuration');
				    }
			    }
			    dispatch((new DeleteOldShipsJob($importedShipIds))->onQueue($this->config('import.jobs.ships.queue','default')));

		    } else {
			    $this->warn('Ships list is empty. Nothing to import.');
		    }
	    } catch (\Exception $e) {
		    $this->error("Exception: ".$e->getMessage()."(file: ".$e->getFile()."; line: ".$e->getLine().")");
	    }
    }

	/**
	 * @param \StdClass $shipInfo
	 *
	 * @return bool
	 */
    protected function shouldPassShip(\StdClass $shipInfo)
    {
    	if ( ($this->importFromShipId && $shipInfo->id < $this->importFromShipId)
	        || (!empty($this->importOnlyShipIds) && !in_array($shipInfo->id, $this->importOnlyShipIds))
		    || (!empty($this->ignoreShipIds) && in_array($shipInfo->id, $this->ignoreShipIds))
	    ) {
			return true;
	    }
	    return false;
    }
}
