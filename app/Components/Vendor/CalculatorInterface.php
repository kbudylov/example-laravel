<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 04.12.17
 * Time: 16:27
 */

namespace App\Components\Vendor;

use App\Model\Booking\Order;

/**
 * Interface CalculatorInterface
 * @package App\Components\Vendor
 */
interface CalculatorInterface
{
    /**
     * @param Order $order
     * @return CalculatorResult
     */
    public function calculate(Order $order);
}