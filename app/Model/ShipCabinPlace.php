<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShipCabinPlace
 * @package App\Model
 */
class ShipCabinPlace extends Model
{
    /**
     * @var string
     */
    protected $table = 'ShipCabinPlace';

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
        'title','cabinId','position'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cabin()
    {
        return $this->hasOne(ShipCabin::class, 'id', 'cabinId');
    }
}
