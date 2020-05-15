<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CruiseRoutePoint extends Model
{
    /**
     * @var string
     */
    protected $table = 'CruiseRoutePoint';

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
        'cityId', 'title', 'description'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function city()
    {
        return $this->hasOne(GeoCity::class, 'id', 'cityId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function river()
    {
        return $this->hasOne(GeoRiver::class, 'id', 'riverId');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public static function findAllByCitiesIds($ids)
    {
        return static::whereIn('cityId',$ids)->get();
    }
}
