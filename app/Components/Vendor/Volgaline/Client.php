<?php

namespace App\Components\Vendor\Volgaline;

use App\Components\Vendor\ClientAbstract;
use App\Model\CruiseCabin;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class Volgaline
 * @package App\Vendor\Volgaline
 */
class Client extends ClientAbstract
{
	/**
	 * @var string
	 */
	protected $configPath = 'import.vendors.volgaline';

    /**
     * @return mixed
     * @throws \Exception
     */
	public function getCruiseList()
	{
		return $this->executeRequest('Cruise');
	}

	/**
	 * @param $shipId
	 * @return array
	 */
	public function getCruiseListByShipId($shipId)
	{
		return $this->executeRequest('Cruise/shipId/'.$shipId);
	}

	/**
	 * @param $cruiseId
	 * @return mixed|null
	 */
	public function getCruiseById($cruiseId)
	{
		return $this->executeRequest('Cruise/id/'.$cruiseId);
	}

    /**
     * @param $cruiseId
     * @return mixed
     * @throws \Exception
     */
	public function getCruiseCabinListByCruiseId($cruiseId)
	{
		return $this->executeRequest('CruiseCabin/cruiseId/'.$cruiseId);
	}

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
	public function getShipCabinPlacesByShipCabinId($id)
	{
		return $this->executeRequest('ShipCabinPlace/shipCabinId/'.$id);
	}

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
	public function getCruiseCabinPricesByCabinId($id)
	{
		return $this->executeRequest('PriceVariant/cruiseCabinId/'.$id);
	}

    /**
     * @param $cruiseId
     * @return mixed
     * @throws \Exception
     */
	public function getCruiseRouteListByCruiseId($cruiseId)
	{
		return $this->executeRequest('CruiseRoute/cruiseId/'.$cruiseId);
	}

    /**
     * @return mixed
     * @throws \Exception
     */
	public function getShipList()
	{
		return $this->executeRequest('Ship');
	}

    /**
     * @param $shipId
     * @return mixed
     * @throws \Exception
     */
    public function getShipById($shipId)
    {
        return $this->executeRequest('Ship/id/'.$shipId);
    }

	/**
	 * @inheritdoc
	 */
	public function getShipCabinListByShipId($shipId)
	{
		return $this->executeRequest('ShipCabin/shipId/'.$shipId);
	}

	/**
	 * @param $deckId
	 * @return array|mixed
	 */
	public function getShipDeckById($deckId)
	{
		return $this->executeRequest('ShipDeck/id/'.$deckId);
	}

    /**
     * @param $categoryId
     * @return array|mixed
     */
	public function getShipCabinCategoryById($categoryId)
	{
		try {
			return $this->executeRequest('ShipCabinCategory/id/'.$categoryId);
		} catch (\Exception $e) {
			//todo: report error
			return [];
		}
	}

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
	public function getCityById($id)
	{
		return $this->executeRequest('Locality/id/'.$id);
	}

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getRiverById($id)
    {
        return $this->executeRequest('River/id/'.$id);
    }

    /**
     * @param $cruiseId
     * @param $params
     * @return mixed
     * @throws \Exception
     */
	public function sendBookingRequest($cruiseId, $params)
    {
        $client = new \GuzzleHttp\Client();
        $url = $this->urlTo('Cruise/'.$cruiseId.'/booking');
        try {
            $response = $client->post($url, [
                'body' => \GuzzleHttp\json_encode($params)
            ])->getBody()->getContents();
        } catch (\Exception $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }
        try {
            return \GuzzleHttp\json_decode($response);
        } catch (\Exception $e) {
            throw new \RuntimeException("Invalid response json");
        }
    }

    /**
     * @param $cruiseId
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function getBookingPrice($cruiseId, $params)
    {
        $url = $this->urlTo('Cruise/'.$cruiseId.'/bookingPrice');
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->post($url, [
                'body' => \GuzzleHttp\json_encode($params)
            ])->getBody()->getContents();
        } catch (ClientException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }
        try {
            return \GuzzleHttp\json_decode($response);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param $id
     * @return array|\Psr\Http\Message\ResponseInterface|string
     * @throws \Exception
     */
    public function sendBookingDeleteRequest($id)
    {
        $url = $this->urlTo('Booking/'.$id.'/Delete');
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->post($url,[]);
        } catch (ClientException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        } catch (\Exception $e) {
            return [];
        }
        return $response;
    }
}
