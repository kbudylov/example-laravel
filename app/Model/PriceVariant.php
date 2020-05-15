<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PriceVariant extends Model
{
    /**
     * @var string
     */
    protected $table = 'PriceVariant';

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
        'cabinId','countPeople','price'
    ];
}
