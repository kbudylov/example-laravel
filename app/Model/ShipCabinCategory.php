<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ShipCabinCategory extends Model
{
    /**
     * @var string
     */
    protected $table = 'ShipCabinCategory';

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
        'title','vendorId','shipId','description'
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
     * @param $shipId
     * @return mixed
     */
    public static function findAllByShipId($shipId)
    {
        return static::where([
            'shipId' => $shipId
        ])->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cabins()
    {
        return $this->hasMany(ShipCabin::class,'categoryId','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function photos()
    {
        return $this->hasMany(ShipCabinCategoryPhoto::class, 'categoryId','id');
    }
}
