<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 04.12.17
 * Time: 16:47
 */

namespace App\Model;

use Carbon\Carbon;

/**
 * Trait CruiseAttributes
 * @package App\Model
 * @mixin Cruise
 */
trait CruiseAttributes
{
    /**
     * @return int
     */
    public function getCountDaysAttribute()
    {
        return Carbon::parse($this->returnDateTime)->diffInDays(Carbon::parse($this->departureDateTime)) + 1;
    }

    /**
     * @return string
     */
    public function getVendorTitleAttribute()
    {
        return $this->vendorRel->prefix;
    }

    /**
     * @return mixed
     */
    public function getDeparturePointTitleAttribute()
    {
        return $this->pointDeparture->city->title;
    }

    /**
     * @return mixed
     */
    public function getReturnPointTitleAttribute()
    {
        return $this->pointReturn->city->title;
    }

    /**
     * @param \StdClass $cruiseInfo
     */
    public function setInfoAttribute( \StdClass $cruiseInfo )
    {
        $this->attributes['info'] = \GuzzleHttp\json_encode($cruiseInfo);
        $this->_infoDecoded = $cruiseInfo;
    }

    /**
     * @return null|\StdClass
     */
    public function getInfoAttribute()
    {
        if(!$this->_infoDecoded){
            try {
                /** @var \StdClass $_raw */
                $_raw = \GuzzleHttp\json_decode($this->attributes['info']);
                if($_raw){
                    $this->_infoDecoded = $_raw;
                } else {
                    $this->_infoDecoded = null;
                }
            } catch(\Exception $e) {
                $this->_infoDecoded = null;
            }
        }
        return $this->_infoDecoded;
    }

    /**
     * @return string
     */
    public function getDepartureDateAttribute()
    {
        return Carbon::parse($this->departureDateTime)->format('d.m.Y');
    }

    /**
     * @return string
     */
    public function getReturnDateAttribute()
    {
        return Carbon::parse($this->returnDateTime)->format('d.m.Y');
    }

    /**
     * @return string
     */
    public function getDepartureTimeAttribute()
    {
        return Carbon::parse($this->departureDateTime)->format('H:i');
    }

    /**
     * @return string
     */
    public function getReturnTimeAttribute()
    {
        return Carbon::parse($this->returnDateTime)->format('H:i');
    }
}