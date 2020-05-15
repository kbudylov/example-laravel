<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 03.12.17
 * Time: 1:35
 */

namespace App\Components\Vendor\Volgaline;

use App\Components\Vendor\FactoryAbstract;
use App\Model\Booking\Order;
use App\Model\Cruise;
use App\Model\CruiseCabin;

/**
 * Class Factory
 * @package App\Components\Vendor\Volgaline
 */
class Factory extends FactoryAbstract
{
    /**
     * @inheritdoc
     */
    public function getApiClient()
    {
        return new Client();
    }

    /**
     * @inheritdoc
     */
    public function getBokingOrderCalculator()
    {
        return new Calculator();
    }

    /**
     * @inheritdoc
     */
    public function beforeCreateBookingOrder(Order $order)
    {
        parent::beforeCreateBookingOrder($order);

        $client = $this->getApiClient();

        $cabins = $order->getCabins();
        $passengers = $order->getPassengers();

        $params = [
            "cabins" => [],
            "client" => $order->getClientCredentials()
        ];

        /**
         * @var int $cabinId
         * @var CruiseCabin $cabin
         */
        foreach ($cabins as $cabinId => $cabin) {
            $cabinParams = [
                "cruiseCabin" => $cabin->vendorId,
                "passengers" => []
            ];

            if(empty($passengers[$cabinId])){
                $passengers[$cabinId] = $this->getEmulatedPassengersList($cabin);
            }

            foreach ($passengers[$cabinId] as $passenger) {
                $passegerData = [
                    "categoryId" => $passenger['credentials']->categoryId
                ];

                $passegerData["firstName"] = !empty($passenger['credentials']->firstName) ? $passenger['credentials']->firstName : 'UNDEFINED';
                $passegerData["lastName"] = !empty($passenger['credentials']->lastName) ? $passenger['credentials']->lastName : 'UNDEFINED';
                $passegerData["middleName"] = !empty($passenger['credentials']->middleName) ? $passenger['credentials']->middleName : 'UNDEFINED';
                $passegerData["gender"] = isset($passenger['credentials']->gender) ? $passenger['credentials']->gender : 1;
                $passegerData["birthDate"] = !empty($passenger['credentials']->birthDate) ? $passenger['credentials']->birthDate : null;
                $passegerData["documentSeries"] = !empty($passenger['credentials']->documentSeries) ? $passenger['credentials']->documentSeries : 'UNDEFINED';
                $passegerData["documentNumber"] = !empty($passenger['credentials']->documentNumber) ? $passenger['credentials']->documentNumber : 'UNDEFINED';
                $passegerData["phone"] = !empty($passenger['credentials']->phone) ? $passenger['credentials']->phone : [];

                $cabinParams['passengers'][] = $passegerData;
            }
            $params['cabins'][] = $cabinParams;
        }
        $response = $client -> sendBookingRequest(Cruise::findById($order->cruiseId)->vendorId, $params);

        if($response->status == 200){
            $order->vendorId = $response->id;
        } else {
            throw new \RuntimeException("Remote server error: ".$response->message);
        }
    }

    /**
     * @param CruiseCabin $cabin
     * @return array
     */
    protected function getEmulatedPassengersList(CruiseCabin $cabin)
    {
        $passengers = [];

        for($i = 0; $i < $cabin->countPlaces; $i++){
            $passengerCredentials = new \StdClass();
            $passengerCredentials->categoryId = 1;
            $passengerCredentials->firstName = 'UNDEFINED';
            $passengerCredentials->lastName = 'UNDEFINED';
            $passengerCredentials->middleName = 'UNDEFINED';
            $passengerCredentials->gender = 1;
            $passengerCredentials->birthDate = null;
            $passengerCredentials->documentSeries = 'UNDEFINED';
            $passengerCredentials->documentNumber = 'UNDEFINED';
            $passengerCredentials->phone = [];

            $passengers[] = [
                'credentials' => $passengerCredentials
            ];
        }

        return $passengers;
    }

    /**
     * @param Order $order
     * @return void
     */
    public function beforeDeleteBookingOrder(Order $order)
    {
        try {
            /** @var Client $client */
            $client = $this->getApiClient();
            $client -> sendBookingDeleteRequest($order->vendorId);
        } catch (\Exception $e) {

        }
    }
}