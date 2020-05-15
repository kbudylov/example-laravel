<?php

namespace App\Jobs\Import\Vodohod;

use App\Jobs\Import\ImportJob;
use App\Model\Cruise;
use App\Model\CruiseCabin;
use App\Model\CruiseDirection;
use App\Model\CruiseRoute;
use App\Model\CruiseRoutePoint;
use App\Model\CruiseSource;
use App\Model\GeoCity;
use App\Model\PriceVariant;
use App\Model\Ship;
use App\Model\ShipCabin;
use App\Model\ShipCabinCategory;
use App\Model\ShipDeck;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Facades\App\Components\Vendor\Vodohod\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CruiseImportJob
 * @package App\Jobs\Import\Vodohod
 */
class CruiseImportJob extends ImportJob
{
    const CRUISE_HTTP_URI = 'https://www.rech-agent.ru/cruise/';

    /**
     * @inheritdocs
     */
    protected $configPath = 'import.vendors.vodohod';

    /**
     * @inheritdoc
     **/
    protected $loggerName = 'Vodohod:Cruise';

    /**
     * @var  CruiseSource
     */
    protected $vendor;

    /**
     * @var \StdClass
     */
    protected $cruiseInfo;

    /**
     * @var int
     */
    protected $cruiseVendorId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\StdClass $cruiseInfo, $cruiseVendorId)
    {
        $this->loggerName .= ':'.$cruiseVendorId;
        parent::__construct();

        $this->cruiseInfo = $cruiseInfo;
        $this->cruiseVendorId = $cruiseVendorId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->parseCruise($this->cruiseVendorId, $this->cruiseInfo);
    }

    /**
     * @param $cruiseId
     * @param $cruiseInfo
     */
    protected function parseCruise($cruiseId, $cruiseInfo)
    {
        $this->info("Parsing cruise [$cruiseId]");
        try {
            $cruiseDetail   = Client::getCruiseInfo( $cruiseId );
            $cruiseSiteInfo = $this->parseCruiseSiteInfo($cruiseId);
            $ship           = $this->shipFindOrNew( $cruiseInfo );
            $this->updateShipDetail( $ship, $cruiseDetail );
            $cruise = $this->parseCruiseDetail( $ship, $cruiseId, $cruiseDetail, $cruiseInfo->is_special, $cruiseSiteInfo );
            $this->parseCruiseCabins( $cruise, $cruiseDetail );
        } catch (RequestException $e) {
            $this->error('Request exception: '.$e->getCode());
            $this->logger->error("Cruise [$cruiseId] import error: ".'Request exception: '.$e->getCode());
        } catch (\Exception $e) {
            $this->error($e->getMessage()."; FILE: ".$e->getFile()."; LINE: ".$e->getLine());
        }
    }

    /**
     * @param $cruiseId
     *
     * @return \StdClass
     */
    protected function parseCruiseSiteInfo($cruiseId): \StdClass
    {
        $cruiseHttpInfo = new \StdClass();
        $client = new \GuzzleHttp\Client();
        $response = $client->get(static::CRUISE_HTTP_URI.$cruiseId);
        $contents = $response->getBody()->getContents();
        if ($contents) {
            preg_match_all('/\<tr\>\<td\>(([0-9]{2}.[0-9]{2}.[0-9]{4})\<\/td\>\<td\>([0-9]{2}:[0-9]{2}) - ([0-9]{2}:[0-9]{2}))\<\/td\>\<td\>/iu', $contents, $matches3, PREG_SET_ORDER);
            $departure = array_shift($matches3);
            $arrival = array_pop($matches3);
            $cruiseHttpInfo->dateStart = $departure[2].' '.$departure[4];
            $cruiseHttpInfo->dateEnd = $arrival[2].' '.$arrival[3];
            $cruiseHttpInfo->routes = $this->parseCruiseHttpRoutes($contents);
        }
        return $cruiseHttpInfo;
    }

    /**
     * @param $contents
     *
     * @return array
     */
    protected function parseCruiseHttpRoutes($contents): array
    {
        $crawler = new Crawler($contents);
        $table = $crawler->filter('table');

        $routes = [];
        foreach ($table->children() as $i => $child) {
            if($child->nodeName === 'tr') {
                if (!$i) {
                    continue;
                }
                if(null !== $child->childNodes) {
                    if($child->childNodes->length !== 4) {
                        throw new \RuntimeException('Count cells in table is ['.$child->childNodes->length.']');
                    }

                    /**
                     * @var $td \DOMNode
                     */
                    $dateTimeArrival = $dateTimeDeparture = $description = null;
                    $i = 0;
                    foreach ($child->childNodes as $td) {
                        switch ($i) {
                            case 0: //date
                                $dateTimeArrival = $dateTimeDeparture = trim($td->textContent);
                                break;
                            case 1: //time
                                $times = preg_match('/(\d{2}:\d{2}) - (\d{2}:\d{2})/', $td->textContent, $matches);
                                $dateTimeArrival .= ' '.$matches[1];
                                $dateTimeDeparture .= ' '.$matches[2];
                                break;
                            case 3: //description
                                $description = $td->textContent;
                                break;
                            default:
                        }
                        $i++;
                    }
                    $routes[] = (object)['arrival' => $dateTimeArrival, 'departure' => $dateTimeDeparture, 'description' => $description];
                }
            }
        }
        return $routes;
    }


    /**
     * @param Cruise $cruise
     * @param \StdClass $cruiseDetail
     */
    protected function parseCruiseCabins(Cruise $cruise, \StdClass $cruiseDetail)
    {
        $cruiseCabinsCountAvailable = 0;
        $this->info("Parsing cruise $cruise->id ($cruise->vendorId) prices");
        foreach ($cruiseDetail->prices as $cabinNumber => $priceVariants) {
            $this->info("Parsing price variants for cabin [$cabinNumber]");
            foreach ($priceVariants as $countPlaces => $tariffs) {
                foreach ($tariffs as $tariffId => $tarifDetail) {
                    $shipCabin = $this->shipCabinFindOrNew($cruise->shipId, $cabinNumber, $tarifDetail);
                    $this->info("Ship cabin id [$shipCabin->id] is used");
                    $isAvailable = (bool)$tarifDetail->available_rooms;
                    $saleStatus = $isAvailable ? CruiseCabin::SALE_STATUS_AVAILABLE : CruiseCabin::SALE_STATUS_BOOKED;
                    $cruiseCabin = $this->cruiseCabinFindOrNew($cruise, $shipCabin, $isAvailable, $saleStatus);
                    $this->info("Cruise cabin id [$cruiseCabin->id] is used");
                    if ($isAvailable) {
                        $cruiseCabinsCountAvailable++;
                    }

                    $tariffName = $tarifDetail->tariff_name;
                    if(false !== strstr($tariffName, 'Взрослый')) {
                        $priceVariant = PriceVariant::where([
                            'cabinId' => $cruiseCabin->id,
                            'countPeople' => $countPlaces,
                        ])->first();
                        if (!$priceVariant) {
                            $priceVariant = PriceVariant::create([
                                'cabinId' => $cruiseCabin->id,
                                'countPeople' => $countPlaces,
                                'price' => $tarifDetail->price
                            ]);
                            $this->info("Price variant for [$countPlaces] created; Price is: $tarifDetail->price");
                        } else {
                            $this->info("Price variant for [$countPlaces] found ($priceVariant->id); Price is: $tarifDetail->price");
                            $priceVariant->update([
                                'price' => $tarifDetail->price
                            ]);
                        }
                    } else {

                    }
                }

            }
        }
        $cruise->minPrice = $this->getCruiseMinPrice($cruise->id);
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
     * @param Cruise $cruise
     * @param ShipCabin $shipCabin
     *
     * @return CruiseCabin
     */
    protected function cruiseCabinFindOrNew(Cruise $cruise, ShipCabin $shipCabin, $isAvailable, $saleStatusId)
    {
        $cruiseCabin = CruiseCabin::where([
            'cruiseId' => $cruise->id,
            'cabinId' => $shipCabin->id
        ])->first();
        if (!$cruiseCabin) {
            $cruiseCabin = CruiseCabin::create([
                'cruiseId' => $cruise->id,
                'vendorId' => $shipCabin->number,
                'cabinId' => $shipCabin->id,
                'isSeparate' => 0,
                'isAvailable' => $isAvailable,
                'saleStatusId' => $saleStatusId
            ]);
        } else {
            $cruiseCabin->update([
                'isAvailable' => $isAvailable,
                'saleStatusId' => $saleStatusId
            ]);
        }
        return $cruiseCabin;
    }

    /**
     * @param $shipId
     * @param $cabinNumber
     * @param $cabinInfo
     *
     * @return ShipCabin
     */
    protected function shipCabinFindOrNew($shipId, $cabinNumber, $cabinInfo)
    {
        $shipCabin = ShipCabin::where([
            'shipId' => $shipId,
            'number' => $cabinNumber
        ])->first();
        if (!$shipCabin) {

            $deck = ShipDeck::where([
                'shipId' => $shipId,
                'vendorId' => md5($cabinInfo->deck)
            ])->first();
            if (!$deck) {
                $deck = ShipDeck::create([
                    'shipId' => $shipId,
                    'vendorId' => md5($cabinInfo->deck),
                    'title' => $cabinInfo->deck
                ]);
            }

            $category = ShipCabinCategory::where([
                'shipId' => $shipId,
                'vendorId' => md5($cabinInfo->room_type)
            ])->first();
            if(!$category) {
                $category = ShipCabinCategory::create([
                    'shipId'	=> $shipId,
                    'vendorId' => md5($cabinInfo->room_type),
                    'title' => $cabinInfo->room_type
                ]);
            }

            $shipCabin = ShipCabin::create([
                'shipId' => $shipId,
                'vendorId' => $cabinNumber,
                'number' => $cabinNumber,
                'seatsInCabin' => $cabinInfo->count_place,
                'deckId' => $deck->id,
                'categoryId' => $category->id
            ]);
        }
        return $shipCabin;
    }

    /**
     * @param $cruiseInfo
     * @return Ship
     */
    protected function shipFindOrNew($cruiseInfo)
    {
        $title = mb_convert_case(trim($cruiseInfo->ship), MB_CASE_TITLE, "UTF-8");
        $ship = Ship::where([
            'vendorId' => $cruiseInfo->ship_id,
            'title' => $title
        ])->first();

        if (!$ship) {
            $ship = Ship::create([
                'vendor' => $this->vendor->id,
                'vendorId' => $cruiseInfo->ship_id,
                'title' => $title
            ]);
        }

        $shipPhotoUrl = trim($cruiseInfo->ship_photo_main);
        if (!empty($shipPhotoUrl)) {
            if (!$ship->photoUrl) {
                $ship->photoUrl = $shipPhotoUrl;
                $ship->save();
            }
        }
        return $ship;
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

    protected function updateShipDetail(Ship $ship, $cruiseDetail)
    {
        $ship->photoUrl = $cruiseDetail->cruise->ship_img;
        $ship->save();
    }

    /**
     * @param Ship $ship
     * @param $cruiseId
     * @param $cruiseDetail
     * @param int $isSpecial
     * @param \StdClass $cruiseSiteInfo
     *
     * @return Cruise
     */
    protected function parseCruiseDetail(Ship $ship, $cruiseId, $cruiseDetail, $isSpecial = 0, \StdClass $cruiseSiteInfo): Cruise
    {
        $cruiseDirection = $this->cruiseDirectionFindOrNew($cruiseDetail);
        $cruiseRouteInfo = $this->parseCruiseRouteInfo($cruiseDetail);

        $departurePoint = $cruiseRouteInfo[0]['point'];
        $returnPoint = $cruiseRouteInfo[\count($cruiseRouteInfo)-1]['point'];

        if (empty($cruiseSiteInfo->dateStart)) {
            $cruiseDateStart = Carbon::createFromTimestamp($cruiseDetail->cruise->date_start)->toDateTimeString();
        } else {
            $cruiseDateStart = Carbon::parse($cruiseSiteInfo->dateStart)->toDateTimeString();
        }

        if (empty($cruiseSiteInfo->dateEnd)) {
            $cruiseDateEnd = Carbon::createFromTimestamp($cruiseDetail->cruise->date_stop)->toDateTimeString();
        } else {
            $cruiseDateEnd = Carbon::parse($cruiseSiteInfo->dateEnd)->toDateTimeString();
        }

        $cruiseData = [
            'shipId' => $ship->id,
            'vendor' => $this->vendor->id,
            'vendorId' => $cruiseId,
            'title' => trim($cruiseDetail->cruise->route),
            'regionName' => 'Россия',
            'directionId' => $cruiseDirection->id,
            'departureDateTime' => $cruiseDateStart,
            'returnDateTime' => $cruiseDateEnd,
            'departurePointId' => $departurePoint->id,
            'returnPointId' => $returnPoint->id,
            'riverId' => 4,
            'isWeekend' => 0,
            'specialOffer' => $isSpecial
        ];

        $cruise = Cruise::where([
            'vendor' => $this->vendor->id,
            'vendorId' => $cruiseData['vendorId']
        ])->first();
        if (!$cruise) {
            $cruise = Cruise::create($cruiseData);
        } else {
            $cruise->update($cruiseData);
        }
        $this->createCruiseRoute($cruise, $cruiseRouteInfo, $cruiseSiteInfo->routes);
        return $cruise;
    }

    protected function createCruiseRoute(Cruise $cruise, $routeList, array $siteRoutes = [])
    {
        $cruise->route()->delete();
        $cnt = \count($routeList);
        foreach ($routeList as $idx => $routePointInfo) {
            if(isset($siteRoutes[$idx])) {
                $arrivalDatetime = Carbon::parse($siteRoutes[$idx]->arrival)->toDateTimeString();
                $departureDateTime = Carbon::parse($siteRoutes[$idx]->departure)->toDateTimeString();
                $description = $siteRoutes[$idx]->description;
            } else {
                $arrivalDatetime = Carbon::createFromTimestamp($routePointInfo['info']->date_start)->toDateTimeString();
                $departureDateTime = Carbon::createFromTimestamp($routePointInfo['info']->date_stop)->toDateTimeString();
                $description = $routePointInfo['info']->description;
            }
            CruiseRoute::create([
                'cruiseId' => $cruise->id,
                'pointId' => $routePointInfo['point']->id,
                'index' => $idx,
                'arrivalDateTime' => $arrivalDatetime,
                'departureDateTime' => $departureDateTime,
                'isStart' => ($idx < 1),
                'isEnd' => !($idx < ($cnt - 1)),
                'description' => $routePointInfo['info']->description
            ]);
        }
    }

    /**
     * @param $cruiseDetail
     *
     * @return array
     */
    protected function parseCruiseRouteInfo($cruiseDetail)
    {
        $routePoints = [];
        foreach ($cruiseDetail->programm as $pointInfo) {
            $point = $this->getRoutePoint($pointInfo);
            $routePoints[] = [
                'point' => $point,
                'info' => $pointInfo
            ];
        }
        return $routePoints;
    }

    /**
     * @param $title
     *
     * @return string
     */
    protected function cleanupCityName($title)
    {
        $title = trim(preg_replace('/\([^\)]+\)/','', trim($title)));
        return $title;
    }

    /**
     * @param $pointInfo
     *
     * @return CruiseRoutePoint
     */
    protected function getRoutePoint($pointInfo)
    {
        $cityName = $this->cleanupCityName($pointInfo->place);
        $city = GeoCity::where([
            'regionId' => 1,
            'title' => $cityName
        ])->first();
        if (!$city) {
            $city = GeoCity::create([
                'regionId' => 1,
                'title' => $cityName
            ]);
        }

        $point = CruiseRoutePoint::where([
            'cityId' => $city->id
        ])->first();
        if (!$point) {
            $point = CruiseRoutePoint::create([
                'cityId' => $city->id
            ]);
        }
        return $point;
    }

    /**
     * @param $cruiseDetail
     *
     * @return CruiseDirection
     */
    protected function cruiseDirectionFindOrNew($cruiseDetail)
    {
        $directionParts = [];
        foreach ($cruiseDetail->programm as $pointInfo) {
            $directionParts[] = mb_convert_case(trim($pointInfo->place), MB_CASE_TITLE, "UTF-8");
        }
        if (!empty($directionParts)) {
            $title = implode(" - ",$directionParts);
        } else {
            $title = $cruiseDetail->cruise->route;
        }
        $direction = CruiseDirection::where([
            'vendor' => $this->vendor->id,
            'vendorId' => md5($cruiseDetail->cruise->route)
        ])->first();
        if (!$direction) {
            $direction = CruiseDirection::create([
                'vendor' => $this->vendor->id,
                'vendorId' => md5($cruiseDetail->cruise->route),
                'title' => $title,
                'originalName' => $cruiseDetail->cruise->route
            ]);
        }
        return $direction;
    }
}
