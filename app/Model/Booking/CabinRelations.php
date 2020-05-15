<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 01.04.17
 * Time: 1:58
 */

namespace App\Model\Booking;

use App\Model\Client;
use App\Model\Cruise;
use App\Model\CruiseCabin;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Relations
 * @package App\Model\Booking\Cabin
 * @mixin Cabin
 * @property Collection $places
 * @property Collection $placesReserve
 * @property CruiseCabin $cabin
 * @property Cruise $cruise
 * @property Collection $passengers
 */
trait CabinRelations
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function client()
    {
        return $this->hasOne(Client::class, 'id', 'clientId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    //public function places()
    //{
    //    return $this->hasMany(Places::class, 'bookingId', 'id');
    //}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cabin()
    {
        return $this->belongsTo(CruiseCabin::class, 'cabinId', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cruise()
    {
        return $this->belongsTo(Cruise::class, 'cruiseId', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function passengers()
    {
        return $this->hasMany(Passenger::class, 'cabinId','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    //public function placesReserve()
    //{
    //    return $this->hasMany(PlacesReserve::class,'bookingCabinId','id');
    //}
}