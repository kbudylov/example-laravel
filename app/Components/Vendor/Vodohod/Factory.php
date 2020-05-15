<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.03.2018
 * Time: 23:29
 */

namespace App\Components\Vendor\Vodohod;

use App\Components\Vendor\FactoryAbstract;
use App\Model\Booking\Order;

/**
 * Class Factory
 * @package App\Components\Vendor\Vodohod
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
        //todo: before create order
    }

    /**
     * @param Order $order
     * @return void
     */
    public function beforeDeleteBookingOrder(Order $order)
    {
        //todo: before delete order
    }
}