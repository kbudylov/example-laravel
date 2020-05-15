<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 21.03.17
 * Time: 14:52
 */

namespace App\Observers;

use App\Model\Booking\BookingPlaces;
use App\Model\Booking\BookingPlacesReserve;

/**
 * Class BookingPlacesObserver
 * @package App\Observers
 */
class BookingPlacesObserver
{
    /**
     * @param BookingPlaces $place
     */
    public function created(BookingPlaces $place)
    {
        //reserve CruiseCabinPlace
        BookingPlacesReserve::create([
            'bookingOrderId' => $place->bookingCabin->orderId,
            'bookingCabinId' => $place->bookingId,
            'bookingPlaceId' => $place->id,
            'cruiseCabinPlaceId' => $place->placeId
        ]);
    }

    /**
     * @param BookingPlaces $place
     */
    public function deleting(BookingPlaces $place)
    {
        //clear CruiseCabinPlace reservation
        if($place->isForceDeleting()){
            if($place->reserve){
                $place->reserve->forceDelete();
            }
        } else {
            if($place->reserve){
                $place->reserve->delete();
            }
        }
    }

    /**
     * @param BookingPlaces $place
     */
    public function restored(BookingPlaces $place)
    {
        if($place->reserve && $place->reserve->trashed()){
            $place->reserve->restore();
        }
    }
}