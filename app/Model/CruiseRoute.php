<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CruiseRoute
 * @package App\Model
 */
class CruiseRoute extends Model
{
    /**
     * @var string
     */
    protected $table = 'CruiseRoute';

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
        'cruiseId','pointId','index','isStart','isEnd','arrivalDateTime','departureDateTime','description'
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
    public function point()
    {
        return $this->hasOne(CruiseRoutePoint::class, 'id','pointId');
    }

    /**
     * @param $cruiseId
     * @return mixed
     */
    public static function findAllByCruiseId($cruiseId)
    {
        return static::where(['cruiseId' => $cruiseId])->get();
    }
}
