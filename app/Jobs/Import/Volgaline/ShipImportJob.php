<?php

namespace App\Jobs\Import\Volgaline;

use Facades\App\Components\Vendor\Volgaline\Client;
use App\Jobs\Import\ImportJob;
use App\Model\Ship;
use App\Model\ShipCabin;
use App\Model\ShipCabinCategory;
use App\Model\ShipCabinCategoryPhoto;
use App\Model\ShipCabinPlace;
use App\Model\ShipDeck;

/**
 * Class ShipImportJob
 * @package App\Jobs\Import\Volgaline
 */
class ShipImportJob extends ImportJob
{
	/**
	 * @inheritdoc
	 */
	protected $configPath = 'import.vendors.volgaline';

	/**
	 * @var \StdClass|null
	 */
	protected $shipInfo;

	/**
	 * ShipImportJob constructor.
	 *
	 * @param \StdClass $shipInfo
	 */
    public function __construct(\StdClass $shipInfo)
    {
	    parent::__construct();

	    $this->shipInfo = $shipInfo;
    }

	/**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    	$ship = $this->importShip($this->shipInfo);
	    if ($ship) {
		    $this->debug('Start importing cruise list for ship ['.$ship->id.']');
		    $cruiseList = Client::getCruiseListByShipId($ship->vendorId);
		    if(!empty($cruiseList)){
			    $cruiseVendorIds = [];
			    foreach ($cruiseList as $cruiseInfo) {
				    try {
					    dispatch((new CruiseImportJob($ship, $cruiseInfo))->onQueue($this->config('import.jobs.cruises.queue')));
						$cruiseVendorIds[] = $cruiseInfo->id;
				    } catch(\Exception $e) {
					    $this->error('Exception occurs whie importing cruise ['.$cruiseInfo->id.']: '.$e->getMessage().' (file: '.$e->getFile().', line: '.$e->getLine().')');
				    }
			    }
			    //delete missing cruises
			    if(!empty($cruiseVendorIds)){
				    $this->deleteOldCruises($ship, $cruiseVendorIds);
			    }
		    } else {
			    $this->warn('Ship [id'.$ship->id.'] cruise list is empty. Passing...');
		    }
		    $localShipIds[] = $ship->id;
	    } else {
		    $this->warn('Ship [vendorId'.$this->shipInfo->id.'] has not been imported.');
	    }
    }

	/**
	 * @param \StdClass $apiShipInfo
	 * @return Ship|null
	 */
	protected function importShip(\StdClass $apiShipInfo)
	{
		$apiShipCabinList = Client::getShipCabinListByShipId($apiShipInfo->id);
		if(!empty($apiShipCabinList)){
			//$this->info('Ship [vendorId:'.$apiShipInfo->id.'] '.count($apiShipCabinList).' cabins found. Processing');
			$ship = $this->shipFindOrNew($apiShipInfo);
			foreach ($apiShipCabinList as $shipCabinInfo){
				$this->importShipCabin($ship, $shipCabinInfo);
			}
			return $ship;
		} else {
			$this->warn('Ship [vendorId:'.$apiShipInfo->id.'] cabin list is empty. Nothing to import');
		}
		return null;
	}

	/**
	 * @param $cruiseVendorIds
	 */
	protected function deleteOldCruises($ship, $cruiseVendorIds)
	{
		dispatch((new DeleteOldCruisesJob($ship, $cruiseVendorIds))->onQueue($this->config('import.jobs.cruises.queue','default')));
	}

	/**
	 * @param \StdClass $apiShipInfo
	 * @return Ship
	 */
	protected function shipFindOrNew(\StdClass $apiShipInfo)
	{
		/** @var Ship $ship */
		$ship = Ship::where([
			//'vendorId' => $apiShipInfo->id,
			'vendor' => $this->getVendor()->id,
			'title' => $apiShipInfo->title
		])->first();

		if($ship){
			$ship->vendorId = $apiShipInfo->id;
			//$ship->title = $apiShipInfo->title;
			$ship->description = $apiShipInfo->description;
			$ship->photoUrl = $apiShipInfo->imageUrl;
			$ship->showPriority = $this->config('model.defaults.ship.showPriority',100);
			$ship->save();
		} else {
			$ship = Ship::create([
				'vendorId' => $apiShipInfo->id,
				'vendor' => $this->getVendor()->id,
				'title' => $apiShipInfo->title,
				'description' => $apiShipInfo->description,
				'photoUrl' => $apiShipInfo->imageUrl,
				'showPriority' => $this->config('model.defaults.ship.showPriority',100)
			]);
		}
		return $ship;
	}

	/**
	 * @param Ship $ship
	 * @param \StdClass $apiShipCabinInfo
	 */
	protected function importShipCabin(Ship $ship, \StdClass $apiShipCabinInfo)
	{
		$deck = $this->shipDeckFindOrNew($ship, $apiShipCabinInfo);
		$category = $this->shipCabinCategoryFindOrNew($ship, $apiShipCabinInfo);

		$shipCabin = ShipCabin::where([
			'shipId' => $ship->id,
			//'vendorId' => $apiShipCabinInfo->id
			'number' => $apiShipCabinInfo->cabinNumber
		])->first();

		if(!$shipCabin) {
			//$this->info('Cabin [#'.$apiShipCabinInfo->id.'] not found. Creating...');
			$shipCabin = ShipCabin::create([
				'shipId' => $ship->id,
				'vendorId' => $apiShipCabinInfo->id,
				'number' => $apiShipCabinInfo->cabinNumber,
				'deckId' => $deck->id,
				'categoryId' => $category->id,
				'type' => $apiShipCabinInfo->type,
				'seatsInCabin' => $apiShipCabinInfo->seatsInCabin
			]);
		} else {
			//$this->info('Cabin [#'.$apiShipCabinInfo->id.'] found. Updating info');

			$shipCabin->shipId = $ship->id;
			$shipCabin->number = $apiShipCabinInfo->cabinNumber;
			$shipCabin->deckId = $deck->id;
			$shipCabin->categoryId = $category->id;
			$shipCabin->type = $apiShipCabinInfo->type;
			$shipCabin->seatsInCabin = $apiShipCabinInfo->seatsInCabin;

			$shipCabin->save();
		}
		$this->importShipCabinPlaces($shipCabin, $apiShipCabinInfo);
	}

	/**
	 * @param ShipCabin $shipCabin
	 * @param \StdClass $apiShipCabinInfo
	 */
	protected function importShipCabinPlaces(ShipCabin $shipCabin, \StdClass $apiShipCabinInfo)
	{
		//$this->info('Importing ship cabin places for cabin #'.$shipCabin->id);
		$placesList = Client::getShipCabinPlacesByShipCabinId($apiShipCabinInfo->id);
		foreach($placesList as $k => $placeInfo){
			ShipCabinPlace::create([
				'cabinId' => $shipCabin->id,
				'vendorId' => $placeInfo->id,
				'title' => $placeInfo->title,
				'type' => $placeInfo->typeId,
				'position' => $placeInfo->positionId
			]);
		}
		//$this->info(($k+1).' ship cabin places imported');
	}

	/**
	 * @param Ship $ship
	 * @param \StdClass $apiShipCabinInfo
	 * @return ShipDeck
	 */
	protected function shipDeckFindOrNew(Ship $ship, \StdClass $apiShipCabinInfo)
	{
		$deck = ShipDeck::where([
			'shipId' => $ship->id,
			'vendorId'=> $apiShipCabinInfo->deckId
		])->first();
		$deckInfo = Client::getShipDeckById($apiShipCabinInfo->deckId);

		if(!$deck){
			$deck = ShipDeck::create([
				'shipId' => $ship->id,
				'vendorId' => $deckInfo->id,
				'title' => $deckInfo->title,
				'index' => $deckInfo->index,
				'schemeUrl' => $deckInfo->svg
			]);
		} else {
			$deck->title = $deckInfo->title;
			$deck->index = $deckInfo->index;
			$deck->schemeUrl = $deckInfo->svg;
			$deck->save();
		}

		return $deck;
	}

	/**
	 * @param $ship
	 * @param $apiShipCabinInfo
	 * @return ShipCabinCategory
	 */
	protected function shipCabinCategoryFindOrNew($ship, $apiShipCabinInfo)
	{
		$category = ShipCabinCategory::where([
			'shipId' => $ship->id,
			'vendorId'=> $apiShipCabinInfo->categoryId
		])->first();

		$categoryInfo = Client::getShipCabinCategoryById($apiShipCabinInfo->categoryId);

		if(!$category){
			$category = ShipCabinCategory::create([
				'shipId' => $ship->id,
				'vendorId' => $categoryInfo->id,
				'title' => $categoryInfo->title
			]);
		} else {
			$category->title = $categoryInfo->title;
		}

		if(!empty($categoryInfo->ShipCabinCategoryPhoto->url)){
			if(!ShipCabinCategoryPhoto::where([
				'categoryId' => $category->id,
				'url' => $categoryInfo->ShipCabinCategoryPhoto->url
			])->first()){
				ShipCabinCategoryPhoto::create([
					'categoryId' => $category->id,
					'url' => $categoryInfo->ShipCabinCategoryPhoto->url
				]);
			}
		}
		return $category;
	}
}
