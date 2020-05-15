<?php

namespace App\Components\Vendor\Infoflot;

use App\Components\Vendor\ClientAbstract;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class Infoflot
 * @package App\Providers\Api\Client\Source
 */
class Client extends ClientAbstract
{
	/**
	 * @var string
	 */
	protected $configPath = 'import.vendors.infoflot';

    /**
     * @see https://api.infoflot.com/JSON/Help/Tours
     * @param $shipId
     *
     * @return mixed
     * @throws \Exception
     */
	public function getCruiseListByShipId($shipId)
	{
		return $this->executeRequest('Tours/'.$shipId.'/');
	}

    /**
     * @see https://api.infoflot.com/JSON/Help/CabinsStatus
     * @param $shipId
     * @param $cruiseId
     *
     * @return mixed
     * @throws \Exception
     */
	public function getCruiseCabinsStatusList($shipId, $cruiseId)
	{
			return $this->executeRequest('CabinsStatus/'.$shipId.'/'.$cruiseId);
	}

    /**
     * @see http://api.infoflot.com/JSON/Help/Excursions
     * @param $shipId
     * @param $cruiseId
     *
     * @return mixed
     * @throws \Exception
     */
	public function getCruiseRouteList($shipId, $cruiseId)
	{
		return $this->executeRequest('Excursions/'.$shipId.'/'.$cruiseId);
	}

    /**
     * @see https://api.infoflot.com/JSON/Help/ShipsSchemes
     * @return array
     * @throws \Exception
     */
	public function getShipSchemes()
	{
		$imagesList = [];
		//http://api.infoflot.com/JSON/a04a83e5ccb19b661c4c0873d3234287982fb5d3/ShipsSchemes/
		$result = $this->executeRequest('ShipsSchemes/');
		foreach ($result as $id => $url) {
			$imagesList[$id] = $url;
		}
		return $imagesList;
	}

    /**
     * @see https://api.infoflot.com/JSON/Help/Ships
     * @return array
     * @throws \Exception
     */
	public function getShipList()
	{
		$result = $this->executeRequest('Ships/');
		$shipList = [];
		if (is_object($result)) {
			foreach ($result as $id => $title) {
				$shipList[] = (object)[
					'id' => $id,
					'title' => $title
				];
			}
		}
		return $shipList;
	}

    /**
     * @see https://api.infoflot.com/JSON/Help/CabinsPhoto
     * @param $shipId
     * @param $cabinName
     *
     * @return mixed
     * @throws \Exception
     */
	public function getShipCabinPhotoList($shipId, $cabinName)
	{
		//http://api.infoflot.com/JSON/a04a83e5ccb19b661c4c0873d3234287982fb5d3/CabinsPhoto/4/207/
		return $this->executeRequest('CabinsPhoto/'.$shipId.'/'.$cabinName.'/');
	}

    /**
     * @see https://api.infoflot.com/JSON/Help/ShipsImages
     * @return array
     * @throws \Exception
     */
	public function getShipTitleImages()
	{
		$imagesList = [];
		//http://api.infoflot.com/JSON/a04a83e5ccb19b661c4c0873d3234287982fb5d3/ShipsImages/
		$result = $this->executeRequest('ShipsImages/');
		foreach ($result as $id => $url) {
			$imagesList[$id] = $url;
		}
		return $imagesList;
	}

    /**
     * @see https://api.infoflot.com/JSON/Help/ShipsPhoto
     * @param $shipId
     *
     * @return mixed
     * @throws \Exception
     */
	public function getShipPhotos($shipId)
	{
		//http://api.infoflot.com/JSON/a04a83e5ccb19b661c4c0873d3234287982fb5d3/ShipsPhoto/4/
		return $this->executeRequest('ShipsPhoto/'.$shipId);
	}

    /**
     * @see https://api.infoflot.com/JSON/Help/ShipsDescription
     * @param $shipId
     *
     * @return mixed
     * @throws \Exception
     */
	public function getShipInfo($shipId)
	{
		//http://api.infoflot.com/JSON/a04a83e5ccb19b661c4c0873d3234287982fb5d3/ShipsDescription/
		return $this->executeRequest('ShipsDescription/'.$shipId);
	}

    /**
     * @see https://api.infoflot.com/JSON/Help/Cabins
     * @param $shipId
     *
     * @return mixed
     * @throws \Exception
     */
	public function getShipCabinList($shipId)
	{
		//http://api.infoflot.com/JSON/a04a83e5ccb19b661c4c0873d3234287982fb5d3/Cabins/4/
		return $this->executeRequest('Cabins/'.$shipId);
	}

    /**
     * @see https://api.infoflot.com/JSON/Help/Cabins
     * @param $shipId
     *
     * @return mixed
     * @throws \Exception
     */
	public function getShipPriceInclude($shipId)
    {
        //http://api.infoflot.com/JSON/a04a83e5ccb19b661c4c0873d3234287982fb5d3/ShipsPriceInclude/4/
        return $this->executeRequest('ShipsPriceInclude/'.$shipId);
    }

    /**
     * @param $url
     * @param $params
     * @return null
     */
	public function sendBookingRequest($url, ParameterBag $params)
    {
        return null;
    }
}
