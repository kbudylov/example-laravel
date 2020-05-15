<?php

namespace App\Model\Booking;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Cabin
 * @package App\Model\Booking
 * @property int $statusId
 * @property int $id
 * @property int $cruiseId
 * @property int $clientId
 * @property int $cabinId
 * @property float $price
 * @property int $created_at
 * @property int $updated_at
 * @property int $orderId
 */
class Cabin extends Model
{
    use CabinRelations,
        CabinAttributes,
        SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'BookingCabins';

    /**
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [
        'id','cabinId','price','created_at','updated_at','orderId'
    ];
}
