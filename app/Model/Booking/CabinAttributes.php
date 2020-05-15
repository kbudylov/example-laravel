<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 01.04.17
 * Time: 1:59
 */

namespace App\Model\Booking;

use App\Model\Booking\Cabin;

/**
 * Class Attributes
 * @package App\Model\Booking
 * @mixin Cabin
 * @property string $status
 * @property string $number
 */
trait CabinAttributes
{
    /**
     * @return string|null
     */
    public function getStatusAttribute()
    {
        return isset(self::$statuses[$this->statusId]) ? self::$statuses[$this->statusId] : null;
    }

    /**
     * @return mixed
     */
    public function getNumberAttribute()
    {
        return $this->cabin->number;
    }
}