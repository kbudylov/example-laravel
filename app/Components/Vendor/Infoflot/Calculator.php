<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 04.12.17
 * Time: 16:36
 */

namespace App\Components\Vendor\Infoflot;

use App\Components\Vendor\CalculatorAbstract;
use App\Components\Vendor\CalculatorResult;
use App\Model\Booking\Order;
use App\Model\Cruise;
use App\Model\CruiseCabin;

/**
 * Class Calculator
 * @package App\Components\Vendor\Infoflot
 */
class Calculator extends CalculatorAbstract
{
    /**
     * @return \App\Components\Vendor\CalculatorResult
     */
    public function calculate(Order $order)
    {
        $cabins = $order->getCabins();
        $passengers = $order->getPassengers();
        $cruise = Cruise::findById($order->cruiseId);
        $calcResult = new CalculatorResult();
        $totalPrice = 0;

        /** @var CruiseCabin $cabin */
        foreach ($cabins as $cabinId => $cabin) {
            $countPassengers = count($passengers[$cabinId]);
            $cabinPrice = $cabin->findPriceForCountPassengers($countPassengers);

            $categoriesList = [];
            foreach ($passengers[$cabinId] as $passenger) {
                if(!isset($categoriesList[$passenger['category']])){
                    $categoriesList[$passenger['category']] = 0;
                }
                $categoriesList[$passenger['category']]++;

                $calcResult->setCabinPassengerCategoryPriceForPlace(
                    $cabinId,
                    $passenger['category'],
                    $cabinPrice
                );
                $calcResult->setCabinPassengerCategoryPriceTotal(
                    $cabinId,
                    $passenger['category'],
                    $cabinPrice
                );
            }

            $totalPrice += $cabinPrice;
        }

        $calcResult -> setTotalPrice($totalPrice);

        return $calcResult;
    }


}