<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShipDeck
 * @package App\Model
 */
class ShipDeck extends Model
{
    /**
     * @var string
     */
    protected $table = 'ShipDeck';

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
        'title','vendorId','shipId','schemeUrl','index'
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
        return $this->hasMany(ShipCabin::class, 'deckId','id');
    }
}
