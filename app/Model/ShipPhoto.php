<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShipPhoto
 * @package App\Model
 */
class ShipPhoto extends Model
{
    /**
     * @var string
     */
    protected $table = 'ShipPhoto';

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
        'shipId','imageUrl','thumbUrl'
    ];

    /**
     * @param $id
     * @return static
     */
    public static function findById($id)
    {
        return static::find($id);
    }

    /**
     * @param $shipId
     * @return mixed
     */
    public static function findAllByShipId($shipId)
    {
        return static::where(['shipId' => $shipId])->get();
    }
}
