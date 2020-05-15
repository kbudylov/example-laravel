<?php

namespace App\Jobs\Import\Volgaline;

use App\Model\CruiseCabin;
use App\Model\CruiseDirection;
use App\Model\CruiseRoute;
use App\Model\CruiseRoutePoint;
use App\Model\GeoCity;
use App\Model\GeoRegion;
use App\Model\GeoRiver;
use App\Model\PriceVariant;
use App\Model\ShipCabin;
use Carbon\Carbon;
use Facades\App\Components\Vendor\Volgaline\Client;
use App\Jobs\Import\ImportJob;
use App\Model\Cruise;
use App\Model\Ship;

/**
 * Class CruiseImportJob
 * @package App\Jobs\Import\Volgaline
 */
class CruiseImportJob extends ImportJob
{
	/**
	 * @inheritdocs
	 */
	protected $configPath = 'import.vendors.volgaline';

	/**
	 * @var \StdClass
	 */
	protected $cruiseInfo;

	/**
	 * @var Ship
	 */
	protected $ship;

	/**
	 * CruiseImportJob constructor.
	 *
	 * @param Ship      $ship
	 * @param \StdClass $cruiseInfo
	 */
    public function __construct(Ship $ship, \StdClass $cruiseInfo)
    {
        parent::__construct();

        $this->cruiseInfo = $cruiseInfo;
        $this->ship = $ship;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
	    /** @var Cruise $cruise */
	    $cruise = Cruise::where([
		    'vendor' => $this->vendor->id,
		    'vendorId' => $this->cruiseInfo->id
	    ])->first();

	    $cruiseAttributes = $this->getCruiseAttributes($this->ship, $this->cruiseInfo);

	    if ($cruise) {
		    foreach ($cruiseAttributes as $key => $value) {
			    $cruise->setAttribute($key, $value);
		    }
		    if(!$cruise->save()){
		        throw new \Exception('Cruise ['.$this->cruiseInfo->id.'] not saved!');
            }
	    } else {
		    $cruise = Cruise::create($cruiseAttributes);
	    }

	    $this->importCruiseRoute($cruise);
	    $this->importCruiseCabins($cruise);

	    $cruise->minPrice = $this->getCruiseMinPrice($cruise->id);
	    $cruise->save();
    }

	/**
	 * @param Cruise $cruise
	 */
	protected function importCruiseRoute(Cruise $cruise)
	{
		$cruise->route()->delete();

		$routePoints = Client::getCruiseRouteListByCruiseId($cruise->vendorId);

		if($routePoints){

			$index = 0;

			CruiseRoute::create([
				'cruiseId' => $cruise->id,
				'pointId' => $cruise->pointDeparture->id,
				'index' => $index,
				'departureDateTime' => $cruise->departureDateTime,
				'isStart' => 1
			]);

			foreach ($routePoints as $routePoint) {
				/**
				+"arrivalDate": "2017-04-30"
				+"arrivalTime": "08:30:00"
				+"departureDate": "2017-04-30"
				+"departureTime": "12:00:00"
				+"description": ""
				 */
				$routePointInfo = Client::getCityById($routePoint->localityId);
				if ($routePointInfo) {
					$point = $this->routePointFindOrCreateNew($routePointInfo);
					CruiseRoute::create([
						'cruiseId' => $cruise->id,
						'pointId' => $point->id,
						'index' => ++$index,
						'departureDateTime' => Carbon::parse($routePoint->departureDate.' '.$routePoint->departureTime),
						'arrivalDateTime' => Carbon::parse($routePoint->arrivalDate.' '.$routePoint->arrivalTime),
						'description' => $routePoint->description
					]);
				} else {
					$this->error('Error occurs while loading locality ['.$routePoint->localityId.']');
				}
			}

			CruiseRoute::create([
				'cruiseId' => $cruise->id,
				'pointId' => $cruise->pointReturn->id,
				'index' => ++$index,
				'arrivalDateTime' => $cruise->returnDateTime,
				'isEnd' => 1
			]);
		}
	}

	/**
	 * @param Cruise $cruise
	 */
	protected function importCruiseCabins(Cruise $cruise)
	{
		$cruiseCabinsList = Client::getCruiseCabinListByCruiseId($cruise->vendorId);

		$this->info("[".count($cruiseCabinsList)."] cruise cabins found");
		if(!empty($cruiseCabinsList)){
			foreach ($cruiseCabinsList as $cruiseCabinInfo) {

				$shipCabin = ShipCabin::where([
					'shipId' => $cruise->shipId,
					'vendorId' => $cruiseCabinInfo->cabinId
				])->first();

				if($shipCabin){

					$cruiseCabin = CruiseCabin::where([
						'cruiseId' => $cruise->id,
						'vendorId' => $cruiseCabinInfo->id
					])->first();

					if(!$cruiseCabin){

						$this->info('Cruise cabin ['.$cruiseCabinInfo->id.'] not found... Creating.');
						$this->info('Ship cabin id is ['.$shipCabin->id.'] (number: '.$shipCabin->number.')');

						$cruiseCabin = CruiseCabin::create([
							'cruiseId' => $cruise->id,
							'vendorId' => $cruiseCabinInfo->id,
							'cabinId' => $shipCabin->id,
							'isSeparate' => ($cruiseCabinInfo->separateId == 2),
							'isAvailable' => $cruiseCabinInfo->isAvailable,
							'saleStatusId' => $cruiseCabinInfo->saleStatusId
						]);

						$this->info('Cruise cabin created: ['.$cruiseCabin->id.']');

					} else {

						$cruiseCabin->cabinId = $shipCabin->id;
						$cruiseCabin->isSeparate = ($cruiseCabinInfo->separateId == 2);
						$cruiseCabin->isAvailable = $cruiseCabinInfo->isAvailable;
						$cruiseCabin->saleStatusId = $cruiseCabinInfo->saleStatusId;
						//$this->info('Cruise cabin found (cruiseId: '.$cruise->id.'), cabinId: ['.$cruiseCabin->id.'] cabinVendorId: '.$cruiseCabin->vendorId.'');
						if($cruiseCabin->save()){
							//$this->info('Cruise cabin ['.$cruiseCabin->id.'] updated');
						} else {
							$this->error('Error occurs while save cruise cabin ['.$cruiseCabin->id.']');
						}
					}
					$this->importCruiseCabinPrices($cruiseCabin);
				} else {
					$this->error('Ship cabin [vendorId: '.$cruiseCabinInfo->cabinId.'] not found');
				}
			}
		} else {
			$this->warn('Cruise ['.$cruise->vendorId.'] cabins list is empty');
		}

        $cruise->countAvailable = $this->getCountAvailableCabins($cruise->id);
        $cruise->save();
	}

    /**
     * @param $cruiseId
     *
     * @return int
     */
    protected function getCountAvailableCabins($cruiseId)
    {
        $result = \DB::selectOne(' SELECT COUNT(*) as count
                                    FROM CruiseCabin C
                                    WHERE C.cruiseId = ? AND isAvailable = 1',[$cruiseId]);

        if(!empty($result->count)){
            return $result->count;
        }
        return 0;
    }

	/**
	 * @param CruiseCabin $cabin
	 */
	protected function importCruiseCabinPrices(CruiseCabin $cabin)
	{
		$cabin->prices()->delete();
		//$this->info('Importing cruise prices list');
		$prices = Client::getCruiseCabinPricesByCabinId($cabin->vendorId);
		$k = 0;
		if(is_array($prices)){
			foreach ($prices as $priceInfo) {
				$k++;
				PriceVariant::create([
					'cabinId' => $cabin->id,
					'countPeople' => $priceInfo->countPeople,
					'price' => $priceInfo->priceForPlace
				]);
			}
			$this->info('['.($k).'] price variants imported');
		}
	}

	/**
	 * @param int $cruiseId
	 * @return int
	 */
	protected function getCruiseMinPrice($cruiseId)
	{
		$result = \DB::selectOne(' SELECT MIN(P.price) as price
                                    FROM CruiseCabin C
                                    JOIN PriceVariant P ON P.cabinId = C.id
                                    WHERE C.cruiseId = ?',[$cruiseId]);

		if(!empty($result->price)){
			return $result->price;
		}
		return 0;
	}

	/**
	 * @param \StdClass $cityInfo
	 * @return mixed
	 */
	protected function routePointFindOrCreateNew(\StdClass $cityInfo)
	{
		$region = GeoRegion::where(['title' => 'Россия'])->first();
		if(!$region){
			$region = GeoRegion::create([
				'title' => 'Россия'
			]);
		}
		$city = GeoCity::where([
			'title' => $cityInfo->title,
			'regionId' => $region->id
		])->first();

		if(!$city){
			$city = GeoCity::create([
				'title' => $cityInfo->title,
				'regionId' => $region->id
			]);
		}

		/** @var CruiseRoutePoint $routePoint */
		$routePoint = CruiseRoutePoint::where(['cityId' => $city->id])->first();
		if(!$routePoint){
			$routePoint = CruiseRoutePoint::create([
				'cityId' => $city->id
			]);
		}
		return $routePoint;
	}

	/**
	 * @param \StdClass $riverInfo
	 * @return GeoRiver
	 */
	protected function riverFindOrCreateNew(\StdClass $riverInfo)
	{
		/** @var GeoRiver $river */
		$river = GeoRiver::where(['title' => $riverInfo->title])->first();
		if (!$river) {
			//$this->info('River ['.$riverInfo->title.'] not found. Creating...');
			$river = GeoRiver::create([
				'title' => $riverInfo -> title
			]);
		} else {
			$this->info('River ['.$riverInfo->title.'] found.');
		}
		return $river;
	}

	/**
	 * @param \StdClass $cruiseInfo
	 *
	 * @return CruiseDirection
	 */
	protected function cruiseDirectionFindOrCreateNew(\StdClass $cruiseInfo)
	{
		$this->info('Parsing cruise direction');
		$originalName = $cruiseInfo->directionTitle;
		$photoUrl = $cruiseInfo->directionPhotoUrl;
		$vendorId = $cruiseInfo->directionId;
		$title = $originalName;

		$direction = CruiseDirection::where([
			'vendorId' => empty($vendorId) ? md5($originalName) : $vendorId,
			'vendor' => $this->getVendor()->id
		])->first();

		if($direction){
			$this->info('Direction ['.$originalName.'] found. Setting title as ['.$title.']');
			$direction->title = $title;
			$direction->originalName = $originalName;
			$direction->photoUrl = $photoUrl;
			$direction->save();
		} else {
			$this->info('Direction ['.$title.'] not found. Adding...');
			$direction = CruiseDirection::create([
				'vendor' => $this->getVendor()->id,
				'vendorId' => empty($vendorId) ? md5($originalName) : $vendorId,
				'originalName' => $originalName,
				'title' => $title,
				'photoUrl' => $photoUrl
			]);
		}
		return $direction;
	}



	/**
	 * @param Ship $ship
	 * @param \StdClass $cruiseInfo
	 * @return array
	 */
	protected function getCruiseAttributes(Ship $ship, \StdClass $cruiseInfo)
	{
		$pointDepartureInfo = Client::getCityById($cruiseInfo->pointDepartureId);
		$pointReturnInfo = Client::getCityById($cruiseInfo->pointReturnId);
		$riverInfo = Client::getRiverById($cruiseInfo->riverId);

		$pointDeparture = $this->routePointFindOrCreateNew($pointDepartureInfo);
		$pointReturn = $this->routePointFindOrCreateNew($pointReturnInfo);
		$river = $this->riverFindOrCreateNew($riverInfo);
		$direction = $this->cruiseDirectionFindOrCreateNew($cruiseInfo);

		$attributes = [
			'title' => $cruiseInfo->title,
			'vendor' => $this->vendor->id,
			'vendorId' => $cruiseInfo->id,
			'directionId' => $direction->id,
			'departureDateTime' => Carbon::parse($cruiseInfo->departureDate.' '.$cruiseInfo->departureTime)->toDateTimeString(),
			'returnDateTime' => Carbon::parse($cruiseInfo->returnDate.' '.$cruiseInfo->returnTime)->toDateTimeString(),
			'departurePointId' => $pointDeparture->id,
			'returnPointId' => $pointReturn->id,
			'riverId' => $river->id,
			'shipId' => $ship->id,
			'isWeekend' => $cruiseInfo->weekend,
			'specialOffer' => $cruiseInfo->specialOffer,
			'priceInclude' => $cruiseInfo->priceInclude,
			'priceNotInclude' => $cruiseInfo->priceNotInclude,
			'description' => $cruiseInfo->description,
			'info' => $cruiseInfo,
			'regionName' => "Россия",
			'bookingPriceUrl' => $cruiseInfo->BookingPrice->_meta->loadURI,
			'bookingUrl' => $cruiseInfo->Booking->_meta->loadURI
		];
		return $attributes;
	}
}
