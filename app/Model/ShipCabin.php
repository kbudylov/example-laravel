<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShipCabin
 * @package App\Model
 * @property ShipCabinCategory $category
 */
class ShipCabin extends Model
{
    /**
     * @var string
     */
    protected $table = 'ShipCabin';

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
        'shipId','vendorId','number','deckId','categoryId','type','seatsInCabin'
    ];

    /**
     * @param $id
     * @return mixed
     */
    public static function findById($id)
    {
        return static::findOrFail($id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function deck()
    {
        return $this->hasOne(ShipDeck::class,'id','deckId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category()
    {
        return $this->hasOne(ShipCabinCategory::class,'id','categoryId');
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
    public function places()
    {
        return $this->hasMany(ShipCabinPlace::class, 'cabinId', 'id');
    }
}
