<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 03.12.17
 * Time: 1:30
 */

namespace App\Components\Vendor;

use App\Model\Booking\Order;
use App\Model\Cruise;

/**
 * Interface FactoryInterface
 * @package App\Components\Vendor
 */
interface FactoryInterface
{
    /**
     * FactoryInterface constructor.
     * @param Cruise $cruise
     */
    public function __construct(Cruise $cruise);

    /**
     * @param array $attributes
     * @return Order
     */
    public function createBookingOrder(array $attributes = []);

    /**
     * @return CalculatorInterface
     */
    public function getBokingOrderCalculator();

    /**
     * @return ClientInterface
     */
    public function getApiClient();

    /**
     * @param Order $order
     * @return mixed
     */
    public function beforeCreateBookingOrder(Order $order);

    /**
     * @param Order $order
     * @return mixed
     */
    public function afterCreateBookingOrder(Order $order);

    /**
     * @param Order $order
     * @return mixed
     */
    public function beforeUpdateBookingOrder(Order $order);

    /**
     * @param Order $order
     * @return mixed
     */
    public function afterUpdateBookingOrder(Order $order);

    /**
     * @param Order $order
     * @return mixed
     */
    public function beforeDeleteBookingOrder(Order $order);

    /**
     * @param Order $order
     * @return mixed
     */
    public function afterDeleteBookingOrder(Order $order);
}