<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GeoRegion extends Model
{
    /**
     * @var string
     */
    protected $table = 'GEORegion';

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
        'id','title'
    ];
}
