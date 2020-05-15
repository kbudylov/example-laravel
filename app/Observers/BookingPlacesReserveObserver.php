<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 22.04.17
 * Time: 19:07
 */

namespace App\Observers;

use App\Model\Booking\BookingPlacesReserve;

class BookingPlacesReserveObserver
{
    /**
     * @param BookingPlacesReserve $reserve
     */
    public function created(BookingPlacesReserve $reserve)
    {
        $reserve->place->setBooked();
    }

    /**
     * @param BookingPlacesReserve $reserve
     */
    public function deleting(BookingPlacesReserve $reserve)
    {
        $reserve->place->setAvailable();
    }

    /**
     * @param BookingPlacesReserve $reserve
     */
    public function restored(BookingPlacesReserve $reserve)
    {
        if(!$reserve->bookingOrder->isPayed()){
            $reserve->place->setBooked();
        } else {
            $reserve->place->setSold();
        }
    }
}