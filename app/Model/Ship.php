<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Cruise;

/**
 * Class Ship
 * @package App\Model
 * @property int $showPriority
 * @property bool $selected
 */
class Ship extends Model
{
    /**
     * @var string
     */
    protected $table = 'Ship';

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
        'title','vendor','vendorId','description','photoUrl','schemeUrl','showPriority'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vendorRel()
    {
        return $this->hasOne(CruiseSource::class,'id','vendor');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function photos()
    {
        return $this->hasMany(ShipPhoto::class,'shipId','id');
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function findById($id)
    {
        return static::findOrFail($id);
    }

    /**
     * @param $vendorId
     * @return Ship
     */
    public static function findByVendorId($vendor, $vendorId)
    {
        return static::where([
            'vendor' => $vendor,
            'vendorId' => $vendorId
        ])->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cabins()
    {
        return $this->hasMany(ShipCabin::class, 'shipId','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function decks()
    {
        return $this->hasMany(ShipDeck::class,'shipId','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cruises()
    {
        return $this->hasMany(Cruise::class,'shipId','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cabinCategories()
    {
        return $this->hasMany(ShipCabinCategory::class,'shipId','id');
    }

	/**
	 * @return bool
	 */
    public function getSelectedAttribute()
    {
    	return $this->showPriority > 0;
    }
}
