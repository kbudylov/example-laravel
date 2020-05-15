<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 17.03.17
 * Time: 19:56
 */

namespace App\Observers;

use App\Model\Booking\Cabin;

/**
 * Class BookingCabinsObserver
 * @package App\Observers
 */
class BookingCabinsObserver
{
    public function created(Cabin $item)
    {
        //$item->cabin->setBooked();
    }

    public function deleting(Cabin $bookingCabin)
    {
        //Restoring BookingPlacesReserve records for cabin
        //foreach ($bookingCabin->placesReserve as $bookingPlaceReserve) {
            /** @var BookingPlacesReserve $bookingPlacesReserve */
            //$bookingPlaceReserve->forceDelete();
        //}

        //Restoring BookingPlaces records for cabin
        //foreach ($bookingCabin->places as $bookingPlace){
            /** @var BookingPlaces $bookingPlace */
            //$bookingPlace->forceDelete();
        //}
    }

    public function restored(Cabin $bookingCabin)
    {
        //Restoring BookingPlaces records for cabin
        //foreach ($bookingCabin->places as $bookingPlace){
            /** @var BookingPlaces $bookingPlace */
        //    $bookingPlace->restore();
        //}

        //Restoring BookingPlacesReserve records for cabin
        //foreach ($bookingCabin->placesReserve as $bookingPlaceReserve) {
            /** @var BookingPlacesReserve $bookingPlacesReserve */
        //    if($bookingPlaceReserve->trashed()){
        //        $bookingPlaceReserve->restore();
        //    }
        //}
    }
}