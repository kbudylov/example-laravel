<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 04.12.17
 * Time: 16:34
 */

namespace App\Components\Vendor\Volgaline;

use App\Components\Vendor\CalculatorAbstract;
use App\Components\Vendor\CalculatorResult;
use App\Model\Booking\Order;
use App\Model\Cruise;
use App\Model\CruiseCabin;
use GuzzleHttp\Exception\ClientException;

/**
 * Class Calculator
 * @package App\Components\Vendor\Volgaline
 */
class Calculator extends CalculatorAbstract
{
    /**
     * @param Order $order
     * @return \App\Components\Vendor\CalculatorResult|void
     */
    public function calculate(Order $order)
    {
        $cabins = $order->getCabins();
        $passengers = $order->getPassengers();
        $cruise = Cruise::findById($order->cruiseId);

        $requestParams = [];
        foreach ($passengers as $cabinId => $passengersList) {

            $cabin = CruiseCabin::findById($cabinId);

            $cabinRequestParams = [
                'cruiseCabin' => $cabin->vendorId,
                'priceCategory' => []
            ];

            $priceCategories = [];
            if(empty($passengersList)){
                $passengersList = $this->getEmulatedPassengersList($cabin);
            }
            foreach ($passengersList as $passenger) {
                if(!isset($priceCategories[$passenger['category']])){
                    $priceCategories[$passenger['category']] = 0;
                }
                $priceCategories[$passenger['category']]++;
            }
            foreach ($priceCategories as $category => $count){
                $cabinRequestParams['priceCategory'][] = [
                    'id' => $category,
                    'count' => $count
                ];
            }

            $requestParams[] = $cabinRequestParams;
        }

        $client = new Client();
        $response = $client->getBookingPrice(Cruise::findById($order->cruiseId)->vendorId, $requestParams);

        if(!empty($response->PriceTotal)){
            $calcResult = new CalculatorResult();
            $calcResult -> setTotalPrice($response->PriceTotal);

            foreach ($response->CruiseCabins as $cabinInfo) {

                $cabinLocalId = $cruise->cabins()->where([
                    'vendorId' => $cabinInfo->cruiseCabinId
                ])->first()->id;

                foreach ($cabinInfo->Prices as $passengersCategoryPrices) {
                    $calcResult->setCabinPassengerCategoryPriceForPlace(
                        $cabinLocalId,
                        $passengersCategoryPrices->categoryId,
                        $passengersCategoryPrices->priceForPlace
                    );
                    $calcResult->setCabinPassengerCategoryPriceTotal(
                        $cabinLocalId,
                        $passengersCategoryPrices->categoryId,
                        $passengersCategoryPrices->priceTotal
                    );
                }
            }
            return $calcResult;
        } else if(!empty($response->message)) {
            throw new \RuntimeException("Remote server error: $response->message");
        }
    }

    /**
     * @param CruiseCabin $cabin
     * @return array
     */
    protected function getEmulatedPassengersList(CruiseCabin $cabin)
    {
        $passengers = [];
        for ($i = 0; $i < $cabin->countPlaces; $i++) {
            $passengers[] = [
                'category' => Order::DEFAULT_PASSENGER_CATEGORY
            ];
        }
        return $passengers;
    }
}