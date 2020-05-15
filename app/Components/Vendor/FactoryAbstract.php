<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 03.12.17
 * Time: 1:37
 */

namespace App\Components\Vendor;

use App\Model\Booking\Order;
use App\Model\Cruise;

/**
 * Class FactoryAbstract
 * @package App\Components\Vendor
 */
abstract class FactoryAbstract implements FactoryInterface
{
    /**
     * @var Cruise
     */
    protected $cruise;

    /**
     * FactoryAbstract constructor.
     * @param Cruise $cruise
     */
    public function __construct(Cruise $cruise)
    {
        $this->cruise = $cruise;
    }

    /**
     * @inheritdoc
     */
    public function createBookingOrder(array $attributes = [])
    {
        $order = new Order(collect($attributes)->merge([
            'cruiseId' => $this->cruise->id
        ])->toArray());
        $order -> setPriceCalculator($this->getBokingOrderCalculator());
        return $order;
    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function beforeCreateBookingOrder(Order $order)
    {

    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function afterCreateBookingOrder(Order $order)
    {

    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function beforeUpdateBookingOrder(Order $order)
    {

    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function afterUpdateBookingOrder(Order $order)
    {

    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function beforeDeleteBookingOrder(Order $order)
    {

    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function afterDeleteBookingOrder(Order $order)
    {

    }
}