<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 04.12.17
 * Time: 16:48
 */

namespace App\Model;

/**
 * Trait CruiseRelations
 * @package App\Model
 * @mixin Cruise
 */
trait CruiseRelations
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vendorRel()
    {
        return $this->hasOne(CruiseSource::class,'id','vendor');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ship()
    {
        return $this->hasOne(Ship::class, 'id', 'shipId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pointDeparture()
    {
        return $this->hasOne(CruiseRoutePoint::class, 'id','departurePointId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pointReturn()
    {
        return $this->hasOne(CruiseRoutePoint::class,'id','returnPointId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cabins()
    {
        return $this->hasMany(CruiseCabin::class, 'cruiseId','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function route()
    {
        return $this->hasMany(CruiseRoute::class,'cruiseId','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function direction()
    {
        return $this->hasOne(CruiseDirection::class, 'id', 'directionId');
    }
}