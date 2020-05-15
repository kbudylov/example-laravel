<?php

namespace App\Jobs\Import\Infoflot;

use Facades\App\Components\Vendor\Infoflot\Client;
use App\Jobs\Import\ImportJob;
use App\Model\CruiseSource;
use App\Model\Ship;
use App\Model\ShipCabin;
use App\Model\ShipCabinCategory;
use App\Model\ShipCabinCategoryPhoto;
use App\Model\ShipCabinPlace;
use App\Model\ShipDeck;
use App\Model\ShipPhoto;

/**
 * Class ShipImportJob
 * @package App\Jobs\Import\Infoflot
 */
class ShipImportJob extends ImportJob
{
    /**
    * @inheritdoc
    */
    protected $configPath = 'import.vendors.infoflot';

    /**
    * @inheritdoc
    **/
    protected $loggerName = 'Infoflot:Ship';

		/** @var  \StdClass */
    protected $shipInfo;

    /** @var  CruiseSource */
    protected $vendor;

    /** @var  string */
    protected $shipPhotoUrl;

    /** @var  string */
    protected $shipSchemeUrl;

	/**
	 * @var array
	 */
    protected $ignoreCruiseIds = [];

	/**
	 * @var array
	 */
    protected $importOnlyCruiseIds = [];

    /**
     * ShipImportJob constructor.
     * @param \StdClass $shipInfo
     */
    public function __construct(\StdClass $shipInfo, $photoUrl, $schemeUrl)
    {
        $this->loggerName .= ':'.$shipInfo->id;

        parent::__construct();

        $this->shipInfo = $shipInfo;
        $this->shipPhotoUrl = $photoUrl;
        $this->shipSchemeUrl = $schemeUrl;
        $this->ignoreCruiseIds = $this->config('model.config.ignoreCruiseIds',[]);
        $this->importOnlyCruiseIds = $this->config('model.config.importOnlyCruiseIds',[]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	    $this->info('Ship [vendorId: '.$this->shipInfo->id.']: processing...');
	    $ship = $this->importShip($this->shipInfo);
        if ($ship) {
            $shipCabins = $this->importShipCabins($ship);
	        if(!empty($shipCabins)) {
                $shipCabinsIds = [];
	            foreach ($shipCabins as $shipCabin){
                    $shipCabinsIds[] = $shipCabin->id;
                }
                //delete old cabins
                dispatch((new ShipOldCabinsDeleteJob($ship, $shipCabinsIds))->onQueue($this->config('import.jobs.cruises.queue','default')));
            }
	        $cruiseList = Client::getCruiseListByShipId($this->shipInfo->id);
			if(!empty($cruiseList)){
				$cruiseImportedIds = [];
				foreach ($cruiseList as $cruiseId => $cruiseInfo) {
					if(!$this->shouldPassCruise($cruiseId)){
						dispatch((new CruiseImportJob($ship, $cruiseInfo, $cruiseId))->onQueue($this->config('import.jobs.cruises.queue','default')));
						$cruiseImportedIds[] = $cruiseId;
					} else {
						$this->info('Cruise [vendorId: '.$cruiseId.']: passing due to configuration');
					}
				}
				if(!empty($cruiseImportedIds)){
					dispatch((new DeleteOldCruisesJob($ship, $cruiseImportedIds))->onQueue($this->config('import.jobs.cruises.queue','default')));
				}
			} else {
				$this->warn('Ship [vendorId:'.$this->shipInfo->id.'] cruise list is empty.');
			}
	        $this->info('Ship [vendorId:'.$this->shipInfo->id.'] processed.');
        } else {
            $this->warn('Ship [vendorId:'.$this->shipInfo->id.'] has not been imported.');
        }
    }

	/**
	 * @param $cruiseId
	 *
	 * @return bool
	 */
    protected function shouldPassCruise($cruiseId)
    {
		if(in_array($cruiseId,$this->ignoreCruiseIds)
			|| (!empty($this->importOnlyCruiseIds) && !in_array($cruiseId,$this->importOnlyCruiseIds))
		){
			return true;
		}
		return false;
    }

    /**
     * @return Ship
     * @throws \Exception
     */
    protected function importShip()
    {
	    $ship = Ship::where([
		    'title' => $this->shipInfo->title,
		    'vendor' => $this->getVendor()->id
	    ])->first();

	    if (!$ship) {
		    $this->debug('Ship [vendorId:'.$this->shipInfo->id.'] not found. Creating...');
		    $ship = Ship::create($this->getShipAttributes());
		    $this->debug('Ship [id:'.$ship->id.', vendorId:'.$ship->vendorId.'] created.');
	    } else {
		    $this->debug('Ship [vendorId:'.$this->shipInfo->id.'] found ('.$ship->id.').Updating...');
		    if($ship->update($this->getShipAttributes())){
			    $this->debug('Ship [id:'.$ship->id.', vendorId:'.$ship->vendorId.'] updated success.');
		    } else {
			    $this->error('Ship [vendorId: '.$ship->vendorId.'] not updated.');
		    }
	    }
        if(!$ship){
            throw new \Exception('Ship is null');
        }

        $this->importShipPriceInclude($ship);

		//todo
	    //$this->getShipPhotos($ship);
	    return $ship;
    }

    /**
     * @param Ship $ship
     */
    protected function importShipPriceInclude(Ship $ship)
    {
        $priceInclude = Client::getShipPriceInclude($ship->vendorId);
        if (!empty($priceInclude)) {

            $priceInclude = preg_replace("/([\n|\t][\n|\t]+)/","",$priceInclude);
            $priceInclude = strip_tags($priceInclude);

            $ship->priceInclude = $priceInclude;
            $ship->save();
        }
    }

	/**
	 * @return array
	 */
    protected function getShipAttributes()
    {
        return [
            'vendor' => $this->getVendor()->id,
            'vendorId' => $this->shipInfo->id,
            'title' => $this->shipInfo->title,
            'description' => $this->getShipDescription(),
            'photoUrl' => $this->getShipPhotoUrl(),
            'schemeUrl' => $this->getShipSchemeUrl()
        ];
    }

	/**
	 * @return string
	 */
    protected function getShipDescription()
    {
	    $shipDetail = Client::getShipInfo($this->shipInfo->id);
	    $shipDetail = $this->cleanText($shipDetail);
		return $shipDetail;
    }

	/**
	 * @return null|string
	 */
    protected function getShipPhotoUrl()
    {
	    return $this->validatePhotoUrl($this->shipPhotoUrl) ? $this->shipPhotoUrl : null;
    }

	/**
	 * @return null|string
	 */
    protected function getShipSchemeUrl()
    {
	    return $this->validatePhotoUrl($this->shipSchemeUrl) ? $this->shipSchemeUrl : null;
    }

    /**
     * @param Ship $ship
     */
    protected function getShipPhotos(Ship $ship)
    {
        $_photosIds = [];
        foreach ($ship->photos as $shipPhoto) {
            $_photosIds[] = $shipPhoto->id;
        }
        $shipPhotos = Client::getShipPhotos($ship->vendorId);
        foreach ($shipPhotos as $photo) {
            if(!empty($photo->full)){
                ShipPhoto::create([
                    'shipId' => $ship->id,
                    'imageUrl' => $photo->full,
                    'thumbUrl' => !empty($photo->thumbnail) ? $photo->thumbnail : null
                ]);
            }
        }
		$ship->photos()->whereIn('id',$_photosIds,'AND')->delete();
    }

    /**
     * @param $url
     * @return bool
     */
    protected function validatePhotoUrl($url)
    {
        if(preg_match('/\.(jpg|jpeg|png|gif)$/',$url)){
            return true;
        }
        return false;
    }

    /**
     * @param $text
     * @return mixed
     */
    protected function cleanText($text)
    {
        $text = preg_replace('/[a-z0-9]+\.?[a-z0-9]+ \{[\'#;A-Za-z\-: 0-9.]+\}/','',$text);
        $text = preg_replace('/[ ]+/',' ', $text);
        $text = preg_replace("/\\r\\n/","\n",$text);
        $text = preg_replace("/^[ ]?\n[ ]?$/",'',$text);
        $text = preg_replace("/[\\n]+/","\n",$text);
        $text = preg_replace('/&nbsp;/',' ',$text);
        return trim($text);
    }

    /**
     * @param Ship $ship
     * @return array
     */
    protected function importShipCabins(Ship $ship)
    {
        $shipCabins = [];
        $cabins = Client::getShipCabinList($ship->vendorId);

        if(!empty($cabins)){

            //$this->info(count($cabins).' ship cabins loaded');
            $countCabins = 0;
            foreach ($cabins as $vendorId => $cabinInfo) {
                $category = $this->getShipCabinCategory($ship, $cabinInfo);
                $deck = $this->getShipDeck($ship, $cabinInfo);

                $cabin = ShipCabin::where([
                    'shipId' => $ship->id,
                    'number' => $cabinInfo->name
                ])->first();

                if($cabin){
                    $cabin->update([
	                    'vendorId' => $vendorId,
	                    'categoryId' => $category ? $category->id : null,
	                    'deckId' => $deck ? $deck->id : null,
	                    'type' => $cabinInfo->typeID,
	                    'seatsInCabin' => !empty($cabinInfo->places) ? count($cabinInfo->places) : null,
	                    //TODO: 'amenities' => $this->getCabinAmenities()
	                ]);
                } else {
                    $cabin = ShipCabin::create([
	                    'shipId' => $ship->id,
	                    'vendorId' => $vendorId,
	                    'categoryId' => $category ? $category->id : null,
	                    'deckId' => $deck ? $deck->id : null,
	                    'type' => $cabinInfo->typeID,
	                    'number' => $cabinInfo->name,
	                    'seatsInCabin' => !empty($cabinInfo->places) ? count($cabinInfo->places) : null,
	                    //TODO: 'amenities' => $this->getCabinAmenities()
	                ]);
                }
                $this->addCabinPlaces($cabin, $cabinInfo);

                $shipCabins[] = $cabin;
                $countCabins++;
            }
            $this->info($countCabins.' ship cabins added');
        } else {
            $this->warn('Ship [vendorId: '.$ship->vendorId.'] cabin list is empty');
        }

        return $shipCabins;
    }

    /**
     * @param $shipId
     * @param $cabinName
     * @return array
     */
    protected function importShipCabinPhoto($shipId, $cabinName)
    {
        $photoList = Client::getShipCabinPhotoList($shipId, $cabinName);
        return $photoList;
    }

    /**
     * @param Ship $ship
     * @param \StdClass $cabinInfo
     * @return ShipCabinCategory
     */
    protected function getShipCabinCategory(Ship $ship, \StdClass $cabinInfo)
    {
        /** @var ShipCabinCategory $category */
        $category = ShipCabinCategory::where([
            'shipId' => $ship->id,
            'vendorId' => $cabinInfo->typeID
        ])->first();

        $cabinDetail = $this->importShipCabinPhoto($ship->vendorId, $cabinInfo->name);

        if($category){
            $this->info('Cabin category ['.$cabinInfo->typeID.'] found. Updating...');
            $category->title = $cabinInfo->type;
            $category->description = !empty($cabinDetail->description) ? strip_tags($cabinDetail->description) : null;
            if(!$category->save()){
                $this->error('Error occurs while save ship cabin category [vendorId: '.$cabinInfo->typeID.']');
            }
        } else {
            $this->info('Cabin category ['.$cabinInfo->typeID.'] not found. Creating...');
            $category = ShipCabinCategory::create([
                'shipId' => $ship->id,
                'vendorId' => $cabinInfo->typeID,
                'title' => $cabinInfo->type,
                'description' => !empty($cabinDetail->description) ? strip_tags($cabinDetail->description) : null
            ]);
        }

        if(!empty($cabinDetail->photos)){
            foreach ($cabinDetail->photos as $k => $url){
                if($this->validatePhotoUrl($url)){
                    $photo = ShipCabinCategoryPhoto::where([
                        'categoryId' => $category->id,
                        'url' => $url
                    ])->first();
                    if(!$photo){
                        ShipCabinCategoryPhoto::create([
                            'categoryId' => $category->id,
                            'url' => $url
                        ]);
                    }
                }
            }
            $this->info(($k+1).' photos added for category ['.$category->id.']');
        }
        return $category;
    }

    /**
     * @param Ship $ship
     * @param \StdClass $cabinInfo
     * @return ShipDeck
     */
    protected function getShipDeck(Ship $ship, \StdClass $cabinInfo)
    {
		$deckName = trim(preg_replace("/ палуба/i","",$cabinInfo->deck_name));
        /** @var ShipDeck $deck */
        $deck = ShipDeck::where([
            'shipId' => $ship->id,
            'title' => $deckName
        ])->first();
        if(!$deck){
                /** @var ShipDeck $deck */
                $deck = ShipDeck::where([
                        'shipId' => $ship->id,
                        'title' => $cabinInfo->deck_name
                ])->first();
        }
        if($deck){
            //$this->info('Ship deck ['.$cabinInfo->deckID.'] found. Updating...');
            $deck->title = $deckName;
            $deck->vendorId = $cabinInfo->deckID;
            //TODO:: $deck->description = $this->getDeckDescription($cabinInfo);
            $deck->save();
        } else {
						//$this->info('Ship deck ['.$cabinInfo->deckID.'] not found. Creating...');
            $deck = ShipDeck::create([
                'shipId' => $ship->id,
                'vendorId' => $cabinInfo->deckID,
                'title' => $deckName,
                //TODO:: $deck->description = $this->getDeckDescription($cabinInfo);
            ]);
        }
        return $deck;
    }

    /**
     * @param ShipCabin $cabin
     * @param \StdClass $cabinInfo
     */
    protected function addCabinPlaces(ShipCabin $cabin, \StdClass $cabinInfo)
    {
        if(!empty($cabinInfo->places)){
            $cabin->places()->delete();
            foreach ($cabinInfo->places as $placeInfo) {
                ShipCabinPlace::create([
                    'cabinId' => $cabin->id,
                    'title' => $placeInfo->name,
                    'type' => $placeInfo->type,
                    'position' => $placeInfo->position
                ]);
            }
        }
    }

    /**
     * @param ShipCabin $cabin
     * @param \StdClass $cabinInfo
     */
    protected function addShipCabinDetails(ShipCabin $cabin, \StdClass $cabinInfo)
    {
        //TODO: ass cabin detail description
    }
}
