<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GeoCity extends Model
{
    /**
     * @var string
     */
    protected $table = 'GEOCity';

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
        'title','regionId'
    ];
}
