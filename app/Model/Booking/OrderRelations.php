<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 03.03.17
 * Time: 17:34
 */

namespace App\Model\Booking;

use App\Model\Client;
use App\Model\Cruise;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Relations
 * @package App\Model\Booking
 * @mixin Order
 * @property Cruise $cruise
 * @property Collection $cabins
 * @property Client $client
 */
trait OrderRelations
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cabins()
    {
        return $this->hasMany(Cabin::class, 'orderId', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function client()
    {
        return $this->hasOne(Client::class, 'id', 'clientId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cruise()
    {
        return $this->belongsTo(Cruise::class, 'cruiseId', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function passengers()
    {
        return $this->hasMany(Passenger::class,'orderId','id');
    }
}