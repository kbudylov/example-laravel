<?php

namespace App\Jobs\Import\Infoflot;

use App\Jobs\Import\ImportJob;
use Facades\App\Components\Vendor\Infoflot\Client as Infoflot;
use App\Model\Cruise;
use App\Model\CruiseCabin;
use App\Model\CruiseDirection;
use App\Model\CruiseRoute;
use App\Model\CruiseRoutePoint;
use App\Model\CruiseSource;
use App\Model\GeoCity;
use App\Model\GeoRegion;
use App\Model\GeoRiver;
use App\Model\PriceVariant;
use App\Model\Ship;
use App\Model\ShipCabin;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class CruiseImportJob
 * @package App\Jobs\Import\Infoflot
 */
class CruiseImportJob extends ImportJob
{
    /**
     * @inheritdocs
     */
    protected $configPath = 'import.vendors.infoflot';

    /**
    * @inheritdoc
    **/
    protected $loggerName = 'Infoflot:Cruise';

		/** @var  CruiseSource */
    protected $vendor;

    /** @var  Ship */
    protected $ship;

    /** @var  \StdClass */
    protected $cruiseInfo;

    /** @var  int */
    protected $cruiseId;

	/**
	 * @var array
	 */
    protected $routeList = [];

	/**
	 * @var
	 */
    protected $river;

	/**
	 * @var
	 */
    protected $direction;

    /**
     * CruiseImportJob constructor.
     * @param Ship $ship
     * @param \StdClass $cruiseInfo
     * @param $cruiseId
     */
    public function __construct(Ship $ship, \StdClass $cruiseInfo, $cruiseId)
    {
		$this->loggerName .= ':'.$cruiseId;

        parent::__construct();
        $this->ship = $ship;
        $this->cruiseInfo = $cruiseInfo;
        $this->cruiseId = $cruiseId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->importCruise();
    }

    /**
     * @return Cruise
     */
    protected function importCruise()
    {
        $this->info('Parsing cruise [infoflot Id:' . $this->cruiseId . ']',[$this->cruiseInfo]);

        $this->routeList = $this->getCruiseRouteList($this->ship->vendorId, $this->cruiseId, $this->cruiseInfo);
        $this->info('Cruise [infiflot id: ]'.$this->cruiseId.' route list: ['.count($this->routeList).'] route points found.',[$this->routeList]);
        $this->river = $this->getRiver($this->cruiseInfo->river);
        $this->direction = $this->getDirection($this->cruiseInfo);

        $attributes = $this->getCruiseAttributes();
        $attributesForSearch = collect($attributes)->only([
            'vendor',
            'vendorId',
            //'title',
            //'shipId'
        ])->toArray();

        /** @var Cruise $cruise */
        $cruise = Cruise::where($attributesForSearch)->first();
        if (!$cruise) {
            $this->info('Cruise [' . $this->cruiseId . '] not found. Creating...',[
							'lookingFor' => $attributesForSearch,
							'attributes' => $attributes
						]);
            $cruise = Cruise::create($attributes);
            $this->info('Cruise ['.$cruise->id.'] created.', [$attributes]);
        } else {
            $this->info('Cruise [' . $this->cruiseId . '] found. Updating...',[$attributes]);
            if($cruise->update($attributes)){
                $this->info('Cruise ['.$cruise->id.'] updated',[$attributes]);
            } else {
                $this->error('Cruise ['.$cruise->id.'] update error');
            }
        }

        $this->importCruiseRoute($cruise);
        $this->getCruiseCabins($cruise);
        $this->getCruisePricesList($cruise);

        return $cruise;
    }

	/**
	 * @return array
	 */
    protected function getCruiseAttributes()
	{
		$pointDeparture = $this->routeList[0]['point'];
		$pointReturn = array_last($this->routeList)['point'];

		$attributes = [
			'title' => $this->cruiseInfo->route,
			'vendor' => $this->getVendor()->id,
			'vendorId' => $this->cruiseId,
			'departureDateTime' => Carbon::parse($this->cruiseInfo->date_start . ' ' . $this->cruiseInfo->time_start)->toDateTimeString(),
			'returnDateTime' => Carbon::parse($this->cruiseInfo->date_end . ' ' . $this->cruiseInfo->time_end)->toDateTimeString(),
			'departurePointId' => $pointDeparture->id,
			'returnPointId' => $pointReturn->id,
			'riverId' => $this->river->id,
			'shipId' => $this->ship->id,
			'directionId' => $this->direction->id,
			'isWeekend' => $this->cruiseInfo->weekend,
			'regionName' => $this->cruiseInfo->region,
			'bookingUrl' => env('APP_URL').'/Cruise/Booking',
			'bookingPriceUrl' => env('APP_URL').'/Cruise/BookingPrice',
		];

		return $attributes;
	}

	/**
	 * @param Cruise $cruise
	 *
	 * @return array
	 */
    protected function importCruiseRoute(Cruise $cruise)
    {
        $cruiseRouteOld = [];
				foreach (CruiseRoute::where([
            'cruiseId' => $cruise->id
        ])->get() as $_routePoint) {
					$cruiseRouteOld[] = $_routePoint->id;
				}

        $cruiseRoute = [];
        $total = count($this->routeList);

        $this->info('Found ['.$total.'] route points for cruise',[$this->routeList]);
        $this->info('Adding route points to database');

        if(!empty($this->routeList)){
            /** @var CruiseRoutePoint $routePoint */
            if(!empty($this->routeList)){
                foreach ($this->routeList as $k => $routePoint) {
                    if(!empty($routePoint['info']) && is_object($routePoint['info'])){
                        $pointArrivalDateTime = Carbon::parse($routePoint['info']->date_start.' '.$routePoint['info']->time_start)->toDateTimeString();
                        $pointDepartureDateTime = Carbon::parse($routePoint['info']->date_end.' '.$routePoint['info']->time_end)->toDateTimeString();
                        $pointDescription = $routePoint['info']->note ? $routePoint['info']->note : $routePoint['info']->description;
                    } else {
                        $pointArrivalDateTime = null;
                        $pointDepartureDateTime = null;
                        $pointDescription = null;
                    }
                    $cruiseRoute[] = CruiseRoute::create([
                        'cruiseId' => $cruise->id,
                        'pointId' => $routePoint['point']->id,
                        'index' => $k,
                        'isStart' => ($k < 1),
                        'isEnd' => $k >= $total - 1,
                        'departureDateTime' => (!$k) ? $cruise->departureDateTime : $pointDepartureDateTime,
                        'arrivalDateTime' => ($k >= $total - 1) ? $cruise->returnDateTime : $pointArrivalDateTime,
                        'description' => $pointDescription,
                    ]);
                }
                $this->info('Deleting old route points', $cruiseRouteOld);
                CruiseRoute::whereIn('id',$cruiseRouteOld)->delete();
            }
            $this->info(count($cruiseRoute).' points added');
        }
        return $cruiseRoute;
    }

    /**
     * @param Cruise $cruise
     */
    protected function getCruisePricesList(Cruise $cruise)
    {
        foreach ($this->cruiseInfo->prices as $cabinType => $cabinPricesList) {
            $price = !empty($cabinPricesList->price) ? $cabinPricesList->price : 0;
            if($price){
                /** @var Collection $shipCabinTypeList */
                $shipCabinTypeList = ShipCabin::where([
                    'shipId' => $cruise->shipId,
                    'type' => (int)$cabinType
                ])->get();
                if($shipCabinTypeList->count()){

                    $shipCabinIds = [];
                    /** @var ShipCabin $shipCabin */
                    foreach ($shipCabinTypeList as $shipCabin){
                        $shipCabinIds[] = $shipCabin->id;
                        $shipCabin->description = $cabinPricesList->name;
                        $shipCabin->save();
                    }

                    if(!empty($shipCabinIds)){
                        $cruiseCabinsList = CruiseCabin::where(['cruiseId' => $cruise->id])->whereIn('cabinId',$shipCabinIds)->get();

                        /** @var CruiseCabin $cruiseCabin */
                        foreach ($cruiseCabinsList as $cruiseCabin){
                            $cruiseCabin->prices()->delete();
                            $seatsInCabin = (int)$cruiseCabin->shipCabin->seatsInCabin;

                            for ($i = 1; $i <= $seatsInCabin; $i++){

                                $priceVariant = PriceVariant::where([
                                    'cabinId' => $cruiseCabin->id,
                                    'countPeople' => $i
                                ])->first();

                                if(!$priceVariant){
                                    $priceVariant = PriceVariant::create([
                                        'cabinId' => $cruiseCabin->id,
                                        'countPeople' => $i,
                                        'price' => $price
                                    ]);
                                }
                            }
                        }
                    } else {
                        //this type of ship cabin not found in the local ship cabin list. passing
                    }
                } else {

                }
            } else {

            }
        }
	    $cruise->minPrice = $this->getCruiseMinPrice($cruise->id);
        $cruise->save();
    }

    /**
     * @param $cruiseId
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
     * @param \StdClass $cruiseInfo
     */
    protected function getDirection(\StdClass $cruiseInfo)
    {
        //$this->info('Parsing cruise direction');
        $originalName = $cruiseInfo->route;
        $vendorId = !empty($cruiseInfo->route_name) ?  (is_array($cruiseInfo->route_name) ? $cruiseInfo->route_name[0] : $cruiseInfo->route_name)  : null;

        $directionTitleParts = preg_split('/ – /',$originalName,-1, PREG_SPLIT_NO_EMPTY);
        if(!empty($directionTitleParts)){
            foreach ($directionTitleParts as &$part) {
                $part = preg_replace('/ \([^)]+\)/', '', $part);
            }
            $title = implode(' – ',$directionTitleParts);
        } else {
            $title = $originalName;
        }

        $direction = CruiseDirection::where([
            'vendorId' => empty($vendorId) ? md5($originalName) : $vendorId,
            'originalName' => $originalName,
            'vendor' => $this->getVendor()->id
        ])->first();

        if($direction){
            //$this->info('Direction ['.$originalName.'] found. Setting title as ['.$title.']');
            $direction->title = $title;
            $direction->save();
        } else {
            //$this->info('Direction ['.$title.'] not found. Adding...');
            $attr = [
                'vendor' => $this->getVendor()->id,
                'vendorId' => empty($vendorId) ? md5($originalName) : $vendorId,
                'originalName' => $originalName,
                'title' => $title
            ];
            $direction = CruiseDirection::create($attr);
        }
        return $direction;
    }

    /**
     * @param $shipId
     * @param $cruiseId
     * @param \StdClass $cruiseInfo
     * @return array
     */
    protected function getCruiseRouteList($shipId, $cruiseId, \StdClass $cruiseInfo)
    {
        $this->info('Parsing cruise ['.$cruiseId.'] route list');
        $regionName = $cruiseInfo->region;
        $region = $this->getRegion($regionName);
        $cruiseRouteInfo = Infoflot::getCruiseRouteList($shipId, $cruiseId);

        if(!empty($cruiseRouteInfo)){
            $routeParts = $this->parseCruiseRouteList($cruiseRouteInfo);
        } else {
            $routeParts = $this->parseCruiseCitiesList($cruiseInfo->cities);
        }

        $this->info('Cruise region is ['.$region->title.']');
        $routeList = [];
        foreach ($routeParts as $k => $routePart) {
            $routeList[] = [
                'point' => $this->getRoutePoint($routePart, $region),
                'info' => $routePart
            ];
        }
        $this->info('Found ['.count($routeList).'] route points');
        return $routeList;
    }

    /**
     * @param $routeList
     * @return array
     */
    protected function parseCruiseRouteList($routeList)
    {
				//todo: apply custom patterns and blacklisted patterns here
        $_parsed = [];
        foreach ($routeList as $id => $info) {
            $info->city = $this->cleanupCityName($info->city);
            $_parsed[$id] = $info;
        }
        return $_parsed;
    }

    /**
     * @param $cities
     * @return array
     */
    protected function parseCruiseCitiesList($cities)
    {
        $_filtered = [];

        $cities = preg_replace("/\([^)]+\)/iu","",$cities);
        $_customPatterns = config('cities.custom',[]);
        $_customPatternsFound = [];
        foreach ($_customPatterns as $k => $pattern) {
            if (strpos($cities, $pattern) !== false) {
                //$_filtered[] = $pattern;
                $cities = preg_replace("/".$pattern."/i", '- KKK -', $cities);
                $_customPatternsFound[] = $pattern;
            }
        }

        $matchedCities = preg_split("/ ?(-|–) ?/iu", $cities, -1, PREG_SPLIT_NO_EMPTY);

        if(!empty($matchedCities)){
					$_i = 0;
          $_currentCustomPattern = !empty($_customPatternsFound) ? $_customPatternsFound[$_i] : null;
          foreach ($matchedCities as $key => $value) {
              if($value == 'KKK'){
                  if($_currentCustomPattern !== null) {
                      $_filtered[] = $this->cleanupCityName($_currentCustomPattern);
                      $_currentCustomPattern = isset($_customPatternsFound[$_i+1]) ? $_customPatternsFound[$_i+1] : null;
                      $_i++;
                  } else {
                      //probably error
                  }
              } else {
                if(!$this->isBlacklistedCityName($value)) {
                    $_filtered[] = $this->cleanupCityName($value);
                } else {
                    continue;
                }
              }
          }
          return $_filtered;
        } else {
            return [];
        }
    }

    /**
     * @param $city
     * @return bool
     */
    protected function isBlacklistedCityName($city)
    {
        $blacklist = config('cities.blacklist',[]); //todo
        if(empty(trim($city))){
            return true;
        }
        return in_array($city, $blacklist);
    }

    /**
     * @param $city
     * @return mixed|string
     */
    protected function cleanupCityName($city)
    {
        $city = trim($city);
        foreach(config('cities.replace',[]) as $rule => $replace){
            $replaced = preg_replace($rule, $replace, $city);
            $city = $replaced;
        }
        return $city;
    }

    /**
     * @param $routePart
     * @param $region
     * @return null
     */
    protected function getRoutePoint($routePart, $region)
    {
        $point = null;
        if(is_string($routePart)){
            $cityName = null;
            if(preg_match('/(.*) +\((.*)\)/',$routePart, $matches)){
                $cityName = $matches[1];
            } elseif(preg_match('/(.*)/',$routePart, $matches)) {
                $cityName = $matches[1];
            }
            if(!empty($cityName)){
                $city = $this->getCity($cityName, $region->id);
                $point = CruiseRoutePoint::where([
                    'cityId' => $city->id,
                    'title' => null,
                    'description' => null
                ])->first();
                if(!$point){
                    $point = CruiseRoutePoint::create([
                        'cityId' => $city->id
                    ]);
                }
            }
        } else {
            $city = $this->getCity($routePart->city, $region->id);
            $point = CruiseRoutePoint::where([
                'cityId' => $city->id,
                'title' => null,
                'description' => null
            ])->first();
            if(!$point){
                $point = CruiseRoutePoint::create([
                    'cityId' => $city->id
                ]);
            }
        }
        return $point;
    }

    /**
     * @param $regionName
     * @return GeoRegion
     */
    protected function getRegion($regionName)
    {
        $regionName = trim($regionName);
        if(empty($regionName)){
            $regionName = 'Россия';
        }
        $region = GeoRegion::where([
            'title' => $regionName
        ])->first();
        if(!$region){
            $region = GeoRegion::create([
                'title' => $regionName
            ]);
        }
        return $region;
    }

    /**
     * @param $cityName
     * @param $regionId
     * @return mixed
     */
    protected function getCity($cityName, $regionId)
    {
        $cityName = trim($cityName);
        $city = GeoCity::where([
            'title' => $cityName,
            //'regionId' => $regionId
        ])->first();

        if(!$city){
            $city = GeoCity::create([
                'title' => $cityName,
                'regionId' => $regionId
            ]);
        }
        return $city;
    }

    /**
     * @param $riverName
     * @return GeoRiver
     */
    protected function getRiver($riverName)
    {
        $this->info('Parsing cruise river');
        $river = GeoRiver::where(['title' => $riverName])->first();
        if (!$river) {
            $this->info('River ['.$riverName.'] is not found in database');
            $river = GeoRiver::create([
                'title' => $riverName
            ]);
        } else {
            $this->info('River ['.$riverName.'] found');
        }
        return $river;
    }

    /**
     * @param Cruise $cruise
     */
    public function getCruiseCabins(Cruise $cruise)
    {
        $_oldCabins = [];
				foreach ($cruise->cabins as $cruiseCabin) {
					$_oldCabins[] = $cruiseCabin->id;
				}
        $this->info('Adding cruise cabins to cruise');
        //$k = 0;
        if($cruise->ship->cabins->count()){
            /** @var ShipCabin $shipCabin */
            foreach ($cruise->ship->cabins as $k => $shipCabin){
                CruiseCabin::create([
                    'cruiseId' => $cruise->id,
                    'vendorId' => $shipCabin->vendorId,
                    'cabinId' => $shipCabin->id,
                    'isSeparate' => 0,
                    'isAvailable' => 1
                ]);
            }
            $this->info(($k+1).' cruise cabins added');
        }
        $cruise->cabins()->whereIn('id',$_oldCabins,'AND')->delete();

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
}
