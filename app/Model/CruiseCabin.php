<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CruiseCabin
 * @package App\Model
 * @property int $id
 * @property int $cruiseId
 * @property int $cabinId
 * @property ShipCabin $shipCabin
 * @property int $countPlaces
 * @property int $vendorId
 */
class CruiseCabin extends Model
{
    const SALE_STATUS_AVAILABLE = 1; //0
    const SALE_STATUS_BOOKED = 2; //1
    const SALE_STATUS_BOOKED_PARTIALLY = 4;//2
    const SALE_STATUS_SOLD = 3; //3

    /**
     * @var string
     */
    protected $table = 'CruiseCabin';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'cruiseId','vendorId','cabinId','isSeparate','isAvailable','saleStatusId'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cruise()
    {
        return $this->hasOne(Cruise::class,'id','cruiseId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ship()
    {
        return $this->hasOne(Ship::class,'id','shipId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prices()
    {
        return $this->hasMany(PriceVariant::class,'cabinId','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shipCabin()
    {
        return $this->hasOne(ShipCabin::class,'id','cabinId');
    }

    /**
     * @param $cruiseId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function findAllByCruiseId($cruiseId)
    {
        return static::where([
            'cruiseId' => $cruiseId
        ])->get();
    }

    /**
     * @param $id
     * @return static
     */
    public static function findById($id)
    {
        return static::findOrFail($id);
    }

    /**
     * @return int
     */
    public function getCountPlacesAttribute()
    {
        return $this->shipCabin->seatsInCabin;
    }

    /**
     * @return int
     */
    public function getNumberAttribute()
    {
        return $this->shipCabin->number;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return (bool)$this->isAvailable;
    }

    /**
     * @param $count
     * @return int|mixed
     */
    public function findPriceForCountPassengers($count)
    {
        $priceVariant = $this->prices()->where([
            'countPeople' => $count
        ])->first();

        if($priceVariant){
            return $priceVariant->price;
        }
        return 0;
    }
}
