<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GeoRiver extends Model
{
    /**
     * @var string
     */
    protected $table = 'GEORiver';

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
        'title'
    ];
}
